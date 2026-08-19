<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Audit_jurnal_payment_model extends BF_Model
{
    protected $viewPermission   = 'Audit_Jurnal_Payment.View';
    protected $managePermission = 'Audit_Jurnal_Payment.Manage';

    protected $accounting;
    protected $accounting_vuca;
    protected $accounting_sustain;
    protected $consultant;
    protected $hris;

    public function __construct()
    {
        parent::__construct();
        $this->accounting       = $this->load->database('accounting', true);
        $this->accounting_vuca  = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
        $this->consultant       = $this->load->database('consultant', true);
        $this->hris             = $this->load->database('hris', true);
    }

    /**
     * Mengambil dan mengaudit seluruh data payment yang sudah diproses bayar dan belum diposting.
     */
    public function get_audit_data($filter = [])
    {
        // 1. Ambil data payment_approve yang sudah diproses payment
        $this->db->select('a.*');
        $this->db->from('payment_approve a');
        $this->db->group_start();
        $this->db->where('a.status', 2);
        $this->db->or_where('a.status', '2');
        $this->db->or_where('a.tgl_bayar IS NOT NULL', null, false);
        $this->db->group_end();

        // Kecualikan transaksi RPC dan PHP
        $this->db->group_start();
        $this->db->where("a.no_doc NOT LIKE '%RPC%'", null, false);
        $this->db->where("a.no_doc NOT LIKE '%PHP%'", null, false);
        $this->db->where_not_in('a.tipe', ['refill_pettycash', 'petty_cash_hutang']);
        $this->db->group_end();

        if (!empty($filter['tgl_from']) && !empty($filter['tgl_to'])) {
            $this->db->where('a.tgl_bayar >=', $filter['tgl_from']);
            $this->db->where('a.tgl_bayar <=', $filter['tgl_to']);
        }

        if (!empty($filter['tipe'])) {
            $this->db->where('a.tipe', $filter['tipe']);
        }

        if (!empty($filter['search'])) {
            $this->db->group_start();
            $this->db->like('a.no_doc', $filter['search'], 'both');
            $this->db->or_like('a.id', $filter['search'], 'both');
            $this->db->or_like('a.id_payment', $filter['search'], 'both');
            $this->db->or_like('a.keperluan', $filter['search'], 'both');
            $this->db->or_like('a.tipe', $filter['search'], 'both');
            $this->db->group_end();
        }

        $this->db->order_by('a.tgl_bayar', 'DESC');
        $this->db->order_by('a.id', 'DESC');

        $get_payments = $this->db->get()->result();

        $audit_results = [];
        $summary = [
            'total_audited'      => 0,
            'total_issues'       => 0,
            'total_unbalanced'   => 0,
            'total_missing'      => 0,
            'total_suffix_issue' => 0,
            'total_ok'           => 0
        ];

        foreach ($get_payments as $payment) {
            $target_id = $payment->id;
            $id_payment_ref = $payment->no_doc ?? $payment->id;

            // Cek data existing tr_jurnal berdasarkan no_transaksi
            $this->db->select('j.*');
            $this->db->from('tr_jurnal j');
            $this->db->group_start();
            $this->db->where('j.no_transaksi', (string)$target_id);
            $this->db->or_where("FIND_IN_SET('{$target_id}', REPLACE(j.no_transaksi, ' ', '')) > 0", null, false);
            if (!empty($payment->id_payment)) {
                $this->db->or_where('j.no_transaksi', (string)$payment->id_payment);
                $this->db->or_where("FIND_IN_SET('{$payment->id_payment}', REPLACE(j.no_transaksi, ' ', '')) > 0", null, false);
            }
            $this->db->group_end();
            $existing_jurnal = $this->db->get()->result();

            // Cek apakah jurnal SUDAH diposting semua
            $is_all_posted = true;
            if (empty($existing_jurnal)) {
                $is_all_posted = false;
            } else {
                foreach ($existing_jurnal as $ej) {
                    if ($ej->sts != '1') {
                        $is_all_posted = false;
                        break;
                    }
                }
            }

            // Jika sudah posted semua, skip dari audit unposted
            if ($is_all_posted && !empty($existing_jurnal)) {
                continue;
            }

            // Rekonstruksi data jurnal yang seharusnya
            $expected_jurnal = $this->reconstruct_expected_jurnal($payment, $existing_jurnal);

            // Komparasi & Identifikasi Masalah
            $comparison = $this->compare_jurnal($existing_jurnal, $expected_jurnal, $payment);

            $summary['total_audited']++;
            if ($comparison['has_issue']) {
                $summary['total_issues']++;
                if (in_array('Unbalanced', $comparison['issue_types'])) {
                    $summary['total_unbalanced']++;
                }
                if (in_array('No Journal', $comparison['issue_types'])) {
                    $summary['total_missing']++;
                }
                if (in_array('Missing Suffix', $comparison['issue_types'])) {
                    $summary['total_suffix_issue']++;
                }
            } else {
                $summary['total_ok']++;
            }

            // Filter status anomali jika dipilih di filter
            if (!empty($filter['status_issue'])) {
                if ($filter['status_issue'] == 'issue_only' && !$comparison['has_issue']) {
                    continue;
                }
                if ($filter['status_issue'] == 'ok_only' && $comparison['has_issue']) {
                    continue;
                }
                if ($filter['status_issue'] == 'unbalanced' && !in_array('Unbalanced', $comparison['issue_types'])) {
                    continue;
                }
                if ($filter['status_issue'] == 'no_journal' && !in_array('No Journal', $comparison['issue_types'])) {
                    continue;
                }
                if ($filter['status_issue'] == 'missing_suffix' && !in_array('Missing Suffix', $comparison['issue_types'])) {
                    continue;
                }
            }

            $audit_results[] = [
                'payment'         => $payment,
                'existing_jurnal' => $existing_jurnal,
                'expected_jurnal' => $expected_jurnal,
                'comparison'      => $comparison
            ];
        }

        return [
            'summary' => $summary,
            'data'    => $audit_results
        ];
    }

    /**
     * Resolusi COA Bank dan nama Bank dari payment_approve atau fallback ms_bank / existing_jurnal.
     */
    public function resolve_bank_info($item_payment, $existing_jurnal = [])
    {
        $coa_bank = '';
        $nm_coa_bank = '';
        $nm_bank = '';

        $bank_val = !empty($item_payment->coa_bank) ? $item_payment->coa_bank : (!empty($item_payment->bank) ? $item_payment->bank : '');

        if (!empty($bank_val)) {
            if (is_numeric($bank_val)) {
                $this->db->select('a.id, a.rekening, a.nama, a.coa_bank, b.nama_bank');
                $this->db->from('ms_bank a');
                $this->db->join('list_bank b', 'b.id = a.bank', 'left');
                $this->db->where('a.id', $bank_val);
                $get_bank = $this->db->get()->row();
                if (!empty($get_bank)) {
                    $coa_bank = $get_bank->coa_bank;
                    $nm_coa_bank = $get_bank->nama_bank ?: $get_bank->nama;
                    $nm_bank = $get_bank->rekening . ' - ' . $get_bank->nama_bank . ' - ' . $get_bank->nama;
                }
            } else {
                $coa_bank = $bank_val;
            }
        }

        // Cek dari existing jurnal jika coa_bank masih kosong atau masih numeric
        if (empty($coa_bank) || is_numeric($coa_bank)) {
            if (!empty($existing_jurnal)) {
                foreach ($existing_jurnal as $ej) {
                    if (strpos($ej->coa, '1101-02-') === 0 || ($ej->kredit > 0 && !in_array($ej->coa, ['2104-01-02', '2104-01-03', '1106-01-01']))) {
                        $coa_bank = $ej->coa;
                        $nm_coa_bank = $ej->nm_coa;
                        $nm_bank = $ej->keterangan;
                        break;
                    }
                }
            }
        }

        // Ambil nama resmi COA dari accounting
        if (!empty($coa_bank) && !is_numeric($coa_bank)) {
            $get_coa = $this->accounting->select('nama')->from('coa_master')->where('no_perkiraan', $coa_bank)->get()->row();
            if (!empty($get_coa)) {
                $nm_coa_bank = $get_coa->nama;
            }
        }

        if (empty($coa_bank) || is_numeric($coa_bank)) {
            $coa_bank = '1101-02-01';
            $nm_coa_bank = 'Bank';
        }

        if (empty($nm_bank)) {
            $nm_bank = $nm_coa_bank;
        }

        return [
            'coa_bank'    => $coa_bank,
            'nm_coa_bank' => $nm_coa_bank,
            'nm_bank'     => $nm_bank
        ];
    }

    /**
     * Rekonstruksi susunan ayat jurnal yang seharusnya berdasarkan business rules set_jurnal() dari modul pembayaran_material.
     * Urutan standar persis seperti form pembayaran:
     * 1. Akun Pengeluaran (Kasbon / Transport / Expense / Beban) - Debit
     * 2. PPN (1106-01-06) - Debit
     * 3. PPh (2104-01-02 / 2104-01-03 / 1106-01-01) - Kredit
     * 4. Admin Charge (7201-01-04) - Debit
     * 5. Bank - Kredit Pokok
     * 6. Bank - Kredit Admin (jika bank_charge > 0)
     */
    public function reconstruct_expected_jurnal($item_payment, $existing_jurnal = [])
    {
        // Prioritaskan id_payment seperti pada set_jurnal() modul payment
        if (!empty($item_payment->no_doc) && (strpos($item_payment->no_doc, 'RPC') !== false || strpos($item_payment->no_doc, 'PHP') !== false)) {
            $primary_ref = $item_payment->no_doc;
        } elseif (!empty($item_payment->id_payment)) {
            $primary_ref = $item_payment->id_payment;
        } elseif (!empty($item_payment->id)) {
            $primary_ref = $item_payment->id;
        } else {
            $primary_ref = $item_payment->no_doc ?? '';
        }

        $tgl_bayar = !empty($item_payment->tgl_bayar) ? $item_payment->tgl_bayar : date('Y-m-d');
        $item_ref_id = $primary_ref;

        // Resolusi Bank
        $bank_info = $this->resolve_bank_info($item_payment, $existing_jurnal);
        $coa_bank = $bank_info['coa_bank'];
        $nm_coa_bank = $bank_info['nm_coa_bank'];
        $nm_bank = $bank_info['nm_bank'];

        $id_company = '';
        $nm_company = '';
        $id_divisi  = '';
        $nm_divisi  = '';

        $nilai_ppn = floatval($item_payment->total_ppn ?? 0);
        $nilai_pph = floatval($item_payment->total_pph ?? 0);
        $coa_pph = ($item_payment->tipe_pph == 'PPH 23' || $item_payment->tipe_pph == '23' || $item_payment->tipe_pph == 1) ? '2104-01-03' : '2104-01-02';

        $total_payment = floatval($item_payment->jumlah ?? $item_payment->total_payment ?? 0);
        
        // Resolusi Bank Charge dari payment_approve atau existing_jurnal
        $bank_charge = 0;
        if (isset($item_payment->bank_charge) && floatval($item_payment->bank_charge) > 0) {
            $bank_charge = floatval($item_payment->bank_charge);
        } elseif (isset($item_payment->selisih) && abs(floatval($item_payment->selisih)) > 0) {
            $bank_charge = abs(floatval($item_payment->selisih));
        }

        if ($bank_charge == 0 && !empty($existing_jurnal)) {
            $bank_credits = [];
            foreach ($existing_jurnal as $ej) {
                if ($ej->coa == '7201-01-04' && floatval($ej->debit) > 0) {
                    $bank_charge = floatval($ej->debit);
                }
                if ((strpos($ej->coa, '1101-02-') === 0 || strpos($ej->coa, '1101-01-') === 0) && floatval($ej->kredit) > 0) {
                    $bank_credits[] = floatval($ej->kredit);
                }
            }
            if ($bank_charge == 0 && count($bank_credits) > 1) {
                $bank_charge = min($bank_credits);
            }
        }

        // Standar bearer admin charge pada sistem adalah 'company'
        $admin_charge_bearer = 'company';
        if (isset($item_payment->admin_charge_bearer) && in_array($item_payment->admin_charge_bearer, ['company', 'recipient'])) {
            $admin_charge_bearer = $item_payment->admin_charge_bearer;
        }

        $debit_admin = ($admin_charge_bearer === 'recipient') ? 0 : $bank_charge;
        $nominal_bank_utama = ($admin_charge_bearer === 'recipient') ? ($total_payment + $nilai_ppn - $nilai_pph - $bank_charge) : ($total_payment + $nilai_ppn - $nilai_pph);

        // Helper nama COA
        $get_coa_name = function ($no_coa, $default_name) {
            $get_c = $this->accounting->select('nama')->from('coa_master')->where('no_perkiraan', $no_coa)->get()->row();
            return (!empty($get_c)) ? $get_c->nama : $default_name;
        };

        $expected = [];

        if ($item_payment->tipe == 'kasbon') {
            $get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();

            if (!empty($get_kasbon->no_kasbon_consultant)) {
                $this->consultant->select('a.created_by, b.employee_id');
                $this->consultant->from('kons_tr_kasbon_project_header a');
                $this->consultant->join('users b', 'b.id_user = a.created_by', 'left');
                $this->consultant->where('a.id', $get_kasbon->no_kasbon_consultant);
                $get_pengajuan_konsultan = $this->consultant->get()->row();

                if (!empty($get_pengajuan_konsultan)) {
                    $this->hris->select('a.*, b.id as id_department, b.name as nm_department');
                    $this->hris->from('employees a');
                    $this->hris->join('departments b', 'b.id = a.department_id', 'left');
                    $this->hris->where('a.id', $get_pengajuan_konsultan->employee_id);
                    $get_department = $this->hris->get()->row();

                    $id_divisi = $get_department->id_department ?? '';
                    $nm_divisi = $get_department->nm_department ?? '';
                }

                $this->consultant->select('a.id as id_company, a.nm_company');
                $this->consultant->from('kons_tr_company a');
                $this->consultant->join('kons_tr_penawaran b', 'b.company = a.id', 'left');
                $this->consultant->join('kons_tr_kasbon_project_header c', 'c.id_penawaran = b.id_quotation', 'left');
                $this->consultant->where('c.id', $get_kasbon->no_kasbon_consultant);
                $get_company = $this->consultant->get()->row();

                $id_company = $get_company->id_company ?? '';
                $nm_company = $get_company->nm_company ?? '';
            } else {
                if (!empty($get_kasbon)) {
                    $this->db->select('a.department_id');
                    $this->db->from('users a');
                    $this->db->where('a.username', $get_kasbon->created_by);
                    $get_user = $this->db->get()->row();

                    if (!empty($get_user)) {
                        $this->hris->select('a.id as id_department, a.name as nm_department');
                        $this->hris->from('departments a');
                        $this->hris->where('a.id', $get_user->department_id);
                        $get_department = $this->hris->get()->row();

                        $id_divisi = $get_department->id_department ?? '';
                        $nm_divisi = $get_department->nm_department ?? '';
                    }
                }
            }

            // 1. Piutang Lain-lain Konsultan
            $expected[] = [
                'coa'         => '1103-01-14',
                'nm_coa'      => $get_coa_name('1103-01-14', 'Piutang Lain-lain Konsultan'),
                'keterangan'  => 'Piutang Lain-lain Konsultan - ' . $item_ref_id,
                'debit'       => $total_payment,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 2. PPN
            $expected[] = [
                'coa'         => '1106-01-06',
                'nm_coa'      => $get_coa_name('1106-01-06', 'PPN DN Disetor'),
                'keterangan'  => 'PPN - ' . $item_ref_id,
                'debit'       => $nilai_ppn,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 3. PPh
            $expected[] = [
                'coa'         => $coa_pph,
                'nm_coa'      => $get_coa_name($coa_pph, ($coa_pph == '2104-01-03') ? 'Hutang PPh 23' : 'Hutang PPh 21'),
                'keterangan'  => (($coa_pph == '2104-01-03') ? 'PPh 23' : 'PPh 21') . ' - ' . $item_ref_id,
                'debit'       => 0,
                'kredit'      => $nilai_pph,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 4. Admin Charge
            $expected[] = [
                'coa'         => '7201-01-04',
                'nm_coa'      => $get_coa_name('7201-01-04', 'Admin Charge'),
                'keterangan'  => 'Admin Charge',
                'debit'       => $debit_admin,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 5. Bank Pokok
            $expected[] = [
                'coa'         => $coa_bank,
                'nm_coa'      => $nm_coa_bank,
                'keterangan'  => $nm_bank,
                'debit'       => 0,
                'kredit'      => $nominal_bank_utama,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 6. Bank Admin (jika ada)
            if ($bank_charge > 0) {
                $expected[] = [
                    'coa'         => $coa_bank,
                    'nm_coa'      => $nm_coa_bank,
                    'keterangan'  => $nm_bank,
                    'debit'       => 0,
                    'kredit'      => $bank_charge,
                    'id_company'  => $id_company,
                    'nm_company'  => $nm_company,
                    'id_divisi'   => $id_divisi,
                    'nm_divisi'   => $nm_divisi,
                    'tgl_jurnal'  => $tgl_bayar,
                    'jenis'       => 'Payment'
                ];
            }
        } elseif ($item_payment->tipe == 'transport' || $item_payment->tipe == 'transportasi') {
            $this->db->select('a.no_coa, a.nm_coa, a.created_by');
            $this->db->from('tr_transport a');
            $this->db->join('tr_transport_req b', 'b.no_doc = a.no_req');
            $this->db->where('b.no_doc', $item_payment->no_doc);
            $get_coa_transport = $this->db->get()->row();

            $coa_transport = (!empty($get_coa_transport->no_coa)) ? $get_coa_transport->no_coa : '5103-01-01';
            $nm_coa_transport = (!empty($get_coa_transport->nm_coa)) ? $get_coa_transport->nm_coa : $get_coa_name($coa_transport, 'Biaya Transportasi');

            if (!empty($get_coa_transport)) {
                $get_users = $this->db->get_where('users', ['username' => $get_coa_transport->created_by])->row();
                if (!empty($get_users)) {
                    $get_department = $this->hris->get_where('departments', ['id' => $get_users->department_id])->row();
                    $id_divisi = $get_department->id ?? '';
                    $nm_divisi = $get_department->name ?? '';
                }
            }

            // 1. Debit Transportasi
            $expected[] = [
                'coa'         => $coa_transport,
                'nm_coa'      => $nm_coa_transport,
                'keterangan'  => 'Transportasi - ' . $item_ref_id,
                'debit'       => $total_payment,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 2. PPN
            $expected[] = [
                'coa'         => '1106-01-06',
                'nm_coa'      => $get_coa_name('1106-01-06', 'PPN DN Disetor'),
                'keterangan'  => 'PPN - ' . $item_ref_id,
                'debit'       => $nilai_ppn,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 3. PPh 21 Disetor (1106-01-01) atau PPh
            $coa_pph_trans = !empty($nilai_pph) ? $coa_pph : '1106-01-01';
            $expected[] = [
                'coa'         => $coa_pph_trans,
                'nm_coa'      => $get_coa_name($coa_pph_trans, 'PPh 21 Disetor'),
                'keterangan'  => $get_coa_name($coa_pph_trans, 'PPh 21 Disetor') . ' - ' . $item_ref_id,
                'debit'       => 0,
                'kredit'      => $nilai_pph,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 4. Admin Charge
            $expected[] = [
                'coa'         => '7201-01-04',
                'nm_coa'      => $get_coa_name('7201-01-04', 'Admin Charge'),
                'keterangan'  => 'Admin Charge',
                'debit'       => $debit_admin,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 5. Bank Pokok
            $expected[] = [
                'coa'         => $coa_bank,
                'nm_coa'      => $nm_coa_bank,
                'keterangan'  => $nm_bank,
                'debit'       => 0,
                'kredit'      => $nominal_bank_utama,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 6. Bank Admin
            if ($bank_charge > 0) {
                $expected[] = [
                    'coa'         => $coa_bank,
                    'nm_coa'      => $nm_coa_bank,
                    'keterangan'  => $nm_bank,
                    'debit'       => 0,
                    'kredit'      => $bank_charge,
                    'id_company'  => $id_company,
                    'nm_company'  => $nm_company,
                    'id_divisi'   => $id_divisi,
                    'nm_divisi'   => $nm_divisi,
                    'tgl_jurnal'  => $tgl_bayar,
                    'jenis'       => 'Payment'
                ];
            }
        } elseif ($item_payment->tipe == 'expense') {
            $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_payment->no_doc])->row();

            $nm_paket_biaya = 'Biaya Operasional';
            if (!empty($get_expense->no_expense_consultant)) {
                $get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_header', ['id' => $get_expense->id_kasbon])->row();
                $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_kasbon->id_spk_penawaran])->row();
                if (!empty($get_spk_penawaran)) {
                    $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_spk_penawaran->id_company])->row();
                    $id_company = $get_company->id ?? '';
                    $nm_company = $get_company->nm_company ?? '';

                    $get_department = $this->hris->get_where('divisions', ['id' => $get_spk_penawaran->id_divisi])->row();
                    $id_divisi = $get_department->id ?? '';
                    $nm_divisi = $get_department->name ?? '';
                }
            }

            // 1. Debit Expense
            $expected[] = [
                'coa'         => '9999-99-99',
                'nm_coa'      => $get_coa_name('9999-99-99', 'Biaya Operasional'),
                'keterangan'  => $nm_paket_biaya . ' - ' . $item_ref_id,
                'debit'       => $total_payment,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 2. PPN
            $expected[] = [
                'coa'         => '1106-01-06',
                'nm_coa'      => $get_coa_name('1106-01-06', 'PPN DN Disetor'),
                'keterangan'  => 'PPN - ' . $item_ref_id,
                'debit'       => $nilai_ppn,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 3. PPh
            $expected[] = [
                'coa'         => $coa_pph,
                'nm_coa'      => $get_coa_name($coa_pph, ($coa_pph == '2104-01-03') ? 'Hutang PPh 23' : 'Hutang PPh 21'),
                'keterangan'  => (($coa_pph == '2104-01-03') ? 'PPh 23' : 'PPh 21') . ' - ' . $item_ref_id,
                'debit'       => 0,
                'kredit'      => $nilai_pph,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 4. Admin Charge
            $expected[] = [
                'coa'         => '7201-01-04',
                'nm_coa'      => $get_coa_name('7201-01-04', 'Admin Charge'),
                'keterangan'  => 'Admin Charge',
                'debit'       => $debit_admin,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 5. Bank Pokok
            $expected[] = [
                'coa'         => $coa_bank,
                'nm_coa'      => $nm_coa_bank,
                'keterangan'  => $nm_bank,
                'debit'       => 0,
                'kredit'      => $nominal_bank_utama,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            // 6. Bank Admin
            if ($bank_charge > 0) {
                $expected[] = [
                    'coa'         => $coa_bank,
                    'nm_coa'      => $nm_coa_bank,
                    'keterangan'  => $nm_bank,
                    'debit'       => 0,
                    'kredit'      => $bank_charge,
                    'id_company'  => $id_company,
                    'nm_company'  => $nm_company,
                    'id_divisi'   => $id_divisi,
                    'nm_divisi'   => $nm_divisi,
                    'tgl_jurnal'  => $tgl_bayar,
                    'jenis'       => 'Payment'
                ];
            }
        } else {
            // General direct payment / Non PO fallback
            $expected[] = [
                'coa'         => '5101-01-03',
                'nm_coa'      => $get_coa_name('5101-01-03', 'Biaya Pengeluaran Lainnya'),
                'keterangan'  => ($item_payment->keperluan ?? 'Payment') . ' - ' . $item_ref_id,
                'debit'       => $total_payment,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            $expected[] = [
                'coa'         => '1106-01-06',
                'nm_coa'      => $get_coa_name('1106-01-06', 'PPN DN Disetor'),
                'keterangan'  => 'PPN - ' . $item_ref_id,
                'debit'       => $nilai_ppn,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            $expected[] = [
                'coa'         => $coa_pph,
                'nm_coa'      => $get_coa_name($coa_pph, ($coa_pph == '2104-01-03') ? 'Hutang PPh 23' : 'Hutang PPh 21'),
                'keterangan'  => (($coa_pph == '2104-01-03') ? 'PPh 23' : 'PPh 21') . ' - ' . $item_ref_id,
                'debit'       => 0,
                'kredit'      => $nilai_pph,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            $expected[] = [
                'coa'         => '7201-01-04',
                'nm_coa'      => $get_coa_name('7201-01-04', 'Admin Charge'),
                'keterangan'  => 'Admin Charge',
                'debit'       => $debit_admin,
                'kredit'      => 0,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            $expected[] = [
                'coa'         => $coa_bank,
                'nm_coa'      => $nm_coa_bank,
                'keterangan'  => $nm_bank,
                'debit'       => 0,
                'kredit'      => $nominal_bank_utama,
                'id_company'  => $id_company,
                'nm_company'  => $nm_company,
                'id_divisi'   => $id_divisi,
                'nm_divisi'   => $nm_divisi,
                'tgl_jurnal'  => $tgl_bayar,
                'jenis'       => 'Payment'
            ];

            if ($bank_charge > 0) {
                $expected[] = [
                    'coa'         => $coa_bank,
                    'nm_coa'      => $nm_coa_bank,
                    'keterangan'  => $nm_bank,
                    'debit'       => 0,
                    'kredit'      => $bank_charge,
                    'id_company'  => $id_company,
                    'nm_company'  => $nm_company,
                    'id_divisi'   => $id_divisi,
                    'nm_divisi'   => $nm_divisi,
                    'tgl_jurnal'  => $tgl_bayar,
                    'jenis'       => 'Payment'
                ];
            }
        }

        return $expected;
    }

    /**
     * Membandingkan data jurnal eksisting vs data jurnal yang seharusnya terbentuk.
     */
    public function compare_jurnal($existing, $expected, $payment)
    {
        $issue_types = [];
        $issue_details = [];

        // 1. Cek ketiadaan jurnal
        if (empty($existing)) {
            $issue_types[] = 'No Journal';
            $issue_details[] = 'Jurnal belum terbentuk di tabel tr_jurnal.';
            return [
                'has_issue'        => true,
                'issue_types'      => $issue_types,
                'issue_details'    => $issue_details,
                'existing_ttl_deb' => 0,
                'existing_ttl_krd' => 0,
                'expected_ttl_deb' => array_sum(array_column($expected, 'debit')),
                'expected_ttl_krd' => array_sum(array_column($expected, 'kredit')),
                'is_balanced'      => false
            ];
        }

        $existing_ttl_deb = 0;
        $existing_ttl_krd = 0;
        $has_missing_suffix = false;
        
        $ref_candidates = array_unique(array_filter([
            (string)($payment->id_payment ?? ''),
            (string)($payment->id ?? ''),
            (string)($payment->no_doc ?? '')
        ]));

        $primary_ref = !empty($payment->id_payment) ? $payment->id_payment : (!empty($payment->no_doc) ? $payment->no_doc : $payment->id);

        foreach ($existing as $row) {
            $existing_ttl_deb += floatval($row->debit);
            $existing_ttl_krd += floatval($row->kredit);

            // Cek suffix referensi pada akun selain bank dan admin charge
            $is_bank = in_array($row->coa, ['1101-02-01', '1101-02-09', $payment->coa_bank ?? '']) || strpos($row->coa, '1101-02-') === 0 || strpos($row->coa, '1101-01-') === 0;
            $is_admin = ($row->coa === '7201-01-04');

            if (!$is_bank && !$is_admin) {
                $has_suffix_on_row = false;
                foreach ($ref_candidates as $ref) {
                    if (!empty($ref) && strpos($row->keterangan, $ref) !== false) {
                        $has_suffix_on_row = true;
                        break;
                    }
                }
                if (!$has_suffix_on_row && preg_match('/ - [A-Za-z0-9\-\/]+$/', trim($row->keterangan))) {
                    $has_suffix_on_row = true;
                }

                if (!$has_suffix_on_row) {
                    $has_missing_suffix = true;
                }
            }
        }

        $expected_ttl_deb = array_sum(array_column($expected, 'debit'));
        $expected_ttl_krd = array_sum(array_column($expected, 'kredit'));

        // 2. Cek Balance
        $is_balanced = (round($existing_ttl_deb, 2) === round($existing_ttl_krd, 2));
        if (!$is_balanced) {
            $issue_types[] = 'Unbalanced';
            $issue_details[] = 'Debit (Rp ' . number_format($existing_ttl_deb) . ') ≠ Kredit (Rp ' . number_format($existing_ttl_krd) . ')';
        }

        // 3. Cek Suffix
        if ($has_missing_suffix) {
            $issue_types[] = 'Missing Suffix';
            $issue_details[] = 'Suffix referensi ID (' . $primary_ref . ') belum tercantum di keterangan ayat jurnal.';
        }

        // 4. Cek Selisih Nominal dengan Seharusnya
        if (round($existing_ttl_deb, 2) !== round($expected_ttl_deb, 2)) {
            $issue_types[] = 'Nominal Mismatch';
            $issue_details[] = 'Nominal eksisting (Rp ' . number_format($existing_ttl_deb) . ') berbeda dengan seharusnya (Rp ' . number_format($expected_ttl_deb) . ')';
        }

        // 5. Cek Jumlah Baris Jurnal
        if (count($existing) !== count($expected)) {
            $issue_types[] = 'Row Count Mismatch';
            $issue_details[] = 'Jumlah baris jurnal eksisting (' . count($existing) . ') berbeda dengan susunan standar (' . count($expected) . ')';
        }

        return [
            'has_issue'        => !empty($issue_types),
            'issue_types'      => $issue_types,
            'issue_details'    => $issue_details,
            'existing_ttl_deb' => $existing_ttl_deb,
            'existing_ttl_krd' => $existing_ttl_krd,
            'expected_ttl_deb' => $expected_ttl_deb,
            'expected_ttl_krd' => $expected_ttl_krd,
            'is_balanced'      => $is_balanced
        ];
    }

    /**
     * Mengeksekusi perbaikan ayat jurnal untuk satu transaksi pembayaran tertentu.
     */
    public function fix_jurnal_transaction($payment_id)
    {
        $this->db->where('id', $payment_id);
        $payment = $this->db->get('payment_approve')->row();

        if (empty($payment)) {
            throw new Exception("Data Payment ID #{$payment_id} tidak ditemukan.");
        }

        $target_id = $payment->id;
        $no_transaksi = $payment->id;

        // Ambil existing jurnal terlebih dahulu untuk mendeteksi COA bank yang sebelumnya digunakan
        $this->db->select('j.*');
        $this->db->from('tr_jurnal j');
        $this->db->group_start();
        $this->db->where('j.no_transaksi', (string)$target_id);
        $this->db->or_where("FIND_IN_SET('{$target_id}', REPLACE(j.no_transaksi, ' ', '')) > 0", null, false);
        if (!empty($payment->id_payment)) {
            $this->db->or_where('j.no_transaksi', (string)$payment->id_payment);
            $this->db->or_where("FIND_IN_SET('{$payment->id_payment}', REPLACE(j.no_transaksi, ' ', '')) > 0", null, false);
        }
        $this->db->group_end();
        $existing_jurnal = $this->db->get()->result();

        // 1. Rekonstruksi data jurnal yang benar
        $expected_jurnal = $this->reconstruct_expected_jurnal($payment, $existing_jurnal);

        if (empty($expected_jurnal)) {
            throw new Exception("Gagal merekonstruksi ayat jurnal untuk Payment ID #{$payment_id}.");
        }

        // 2. Hapus baris tr_jurnal lama yang unposted (sts <> '1') untuk transaksi ini
        $this->db->group_start();
        $this->db->where('no_transaksi', (string)$target_id);
        $this->db->or_where("FIND_IN_SET('{$target_id}', REPLACE(no_transaksi, ' ', '')) > 0", null, false);
        if (!empty($payment->id_payment)) {
            $this->db->or_where('no_transaksi', (string)$payment->id_payment);
            $this->db->or_where("FIND_IN_SET('{$payment->id_payment}', REPLACE(no_transaksi, ' ', '')) > 0", null, false);
        }
        $this->db->group_end();
        $this->db->where('sts <>', '1');
        $this->db->delete('tr_jurnal');

        // 3. Insert baris jurnal baru yang sudah terverifikasi
        $arr_insert = [];
        $no_urut = 1;
        $user_id = $this->auth->user_id() ?? 'system_audit';

        foreach ($expected_jurnal as $ej) {
            $no_jurnal = $this->generate_id_invoice_jurnal($no_urut++);

            $arr_insert[] = [
                'no_jurnal'       => $no_jurnal,
                'tgl_jurnal'      => $ej['tgl_jurnal'],
                'coa'             => $ej['coa'],
                'id_company'      => $ej['id_company'] ?: 1,
                'nm_company'      => $ej['nm_company'] ?: 'STM',
                'nm_coa'          => $ej['nm_coa'],
                'debit'           => $ej['debit'],
                'kredit'          => $ej['kredit'],
                'keterangan'      => $ej['keterangan'],
                'no_transaksi'    => (string)$payment->id,
                'jenis_transaksi' => 'Payment',
                'id_divisi'       => $ej['id_divisi'] ?? '',
                'nm_divisi'       => $ej['nm_divisi'] ?? '',
                'sts'             => '0',
                'created_by'      => $user_id,
                'created_date'    => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($arr_insert)) {
            if (!$this->db->insert_batch('tr_jurnal', $arr_insert)) {
                $db_err = $this->db->error();
                throw new Exception("Gagal insert data jurnal: " . ($db_err['message'] ?? ''));
            }
        }

        return true;
    }

    public function generate_id_invoice_jurnal($nomor = 1)
    {
        $srcMtr     = "SELECT MAX(no_jurnal) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
        $resultMtr  = $this->db->query($srcMtr)->result_array();
        $angkaUrut2 = isset($resultMtr[0]['maxP']) ? $resultMtr[0]['maxP'] : '00000';
        $urutan2    = (int)substr($angkaUrut2, 0, 5);
        $urutan2    = $urutan2 + $nomor;
        $urut2      = sprintf('%05s', $urutan2);
        $kode_trans = $urut2 . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');

        return $kode_trans;
    }
}
