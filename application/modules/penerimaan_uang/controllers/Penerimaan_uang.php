<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__DIR__) . '/libraries/Penerimaan_calculation.php';

require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_consulting.php';



class Penerimaan_uang extends Admin_Controller

{

    protected $viewPermission = 'Penerimaan_Uang.View';

    protected $addPermission = 'Penerimaan_Uang.Add';

    protected $deletePermission = 'Penerimaan_Uang.Delete';

    /**
     * Cek izin untuk endpoint AJAX. Mengembalikan true (dan sudah menjawab 403) bila ditolak.
     * auth->restrict() tidak dipakai di sini karena me-redirect ke halaman login, sedangkan AJAX membutuhkan jawaban yang jelas.
     */
    private function deny_without($permission, $json = true)
    {
        if (has_permission($permission)) return false;
        http_response_code(403);
        if ($json) {
            echo json_encode(array('status' => 0, 'msg' => 'Tidak memiliki izin.', 'message' => 'Tidak memiliki izin.'));
        } else {
            echo '<div class="alert alert-danger" role="alert">Tidak memiliki izin.</div>';
        }
        return true;
    }



    protected $consultant;

    protected $accounting;



    public function __construct()

    {

        parent::__construct();



        $this->load->helper('mockup_format');
        $this->load->model(array('Penerimaan_uang/Penerimaan_uang_model'));



        $this->consultant = $this->load->database('consultant', true);

        $this->accounting = $this->load->database('accounting', true);

    }



    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $list_bank = $this->Penerimaan_uang_model->getListBank();
        $list_years = $this->Penerimaan_uang_model->getDistinctYears();
        $list_companies = array('STM', 'VUCA', 'SUSTAIN');

        $data = [
            'tab_counts' => $this->Penerimaan_uang_model->get_tab_counts(),
            'list_bank' => $list_bank,
            'list_years' => $list_years,
            'list_companies' => $list_companies
        ];

        $this->template->set($data);
        $this->template->title('Penerimaan Piutang');
        $this->template->render('index');
    }

    public function get_report_penerimaan()
    {
        if ($this->deny_without($this->viewPermission)) return;
        $searchDt = $this->input->post('search');
        $searchVal = is_array($searchDt) ? trim((string)($searchDt['value'] ?? '')) : '';
        $order = $this->input->post('order');
        $start = max(0, (int)$this->input->post('start'));
        $length = (int)$this->input->post('length');
        if ($length <= 0 || $length > 500) $length = 25;

        $result = $this->Penerimaan_uang_model->get_report_penerimaan_serverside(
            $this->input->post('year'), $this->input->post('month'), $this->input->post('company'), $searchVal,
            $start, $length, (int)($order[0]['column'] ?? 1), (string)($order[0]['dir'] ?? 'desc'),
            $this->input->post('receipt_kind'), $this->input->post('id_customer')
        );
        $e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
        $data = array();
        $no = $start;
        foreach ($result['receipts'] as $r) {
            $no++;
            $bankName = trim(($r['nm_bank'] ?? '') . ' ' . ($r['nama_bank_acc'] ?? 'Bank'));
            $bankShort = !empty($r['nm_bank']) ? $r['nm_bank'] : ($bankName !== '' ? $bankName : 'Bank');
            $bankUpper = strtoupper($bankName);
            if (strpos($bankUpper, 'VUCA') !== false) $coName = 'VUCA';
            elseif (strpos($bankUpper, 'SUSTAIN') !== false) $coName = 'SUSTAIN';
            else $coName = 'STM';
            if (!empty($r['company_main'])) $coName = $r['company_main'];
            $pphText = ($r['pph23_dipotong'] === 'Y') ? 'Potong PPh 23' : 'Tidak potong PPh 23';

            // DataTables tidak mendukung rowspan di body: satu baris per penerimaan, invoice ditumpuk dalam satu sel.
            $invCells = array();
            $taxCells = array();
            foreach (!empty($r['details']) ? $r['details'] : array() as $d) {
                $snap = json_decode($d['consulting_snapshot'] ?? '', true);
                // Hanya perlakuan PPN yang benar-benar tersimpan di Invoicing; invoice lama tanpa data itu tampil "-" (tidak ditebak dari nilai PPN).
                $taxCode = Penerimaan_calculation::stored_tax_code($snap, $d['tax_treatment'] ?? '');
                $spkNo = !empty($snap['spk']) ? $snap['spk'] : ($d['id_spk_penawaran'] ?? '');
                $invCells[] = '<div style="padding:2px 0;white-space:nowrap">' . $e($d['no_invoice'] ?: '-')
                    . ($spkNo !== '' ? '<div class="hint">SPK ' . $e($spkNo) . '</div>' : '') . '</div>';
                $taxCells[] = '<div style="padding:2px 0">' . ($taxCode === '' ? '-' : mk_tax_badge($taxCode))
                    . '<div style="font-size:12px;margin-top:2px;white-space:nowrap">' . $e($taxCode === 'DEP' ? 'Kwitansi Deposit' : $pphText) . '</div></div>';
            }
            $data[] = array(
                'no' => $no,
                'tanggal' => !empty($r['tanggal_transaksi']) ? $e(mk_fdate($r['tanggal_transaksi'])) : '-',
                'bank' => $e($bankShort),
                'detail_bank' => $e($r['keterangan'] ?: '-'),
                'customer' => $e($r['nm_customer']),
                'no_invoice' => $invCells ? implode('', $invCells) : '-',
                'company' => '<span class="lbl">' . $e($coName) . '</span>',
                'tax_pph' => $taxCells ? implode('', $taxCells) : '-',
                'penerimaan_bank' => mk_en($r['nominal_penerimaan_bank']),
                'action' => '<span style="display:inline-flex;gap:4px;white-space:nowrap"><a class="btn btn-info btn-xs" title="Lihat" data-toggle="tooltip" href="' . base_url('penerimaan_uang/view/' . (int)$r['id']) . '"><i class="fa fa-eye"></i></a>'
                    . (!$r['is_posted'] ? ' <button type="button" class="btn btn-danger btn-xs rollback" title="Rollback" data-toggle="tooltip" data-id="' . (int)$r['id'] . '"><i class="fa fa-undo"></i></button>' : '') . '</span>'
            );
        }
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(array(
            'draw' => (int)$this->input->post('draw'),
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $data,
            'count_invoices' => (int)$result['total_invoices'],
            'total_nominal' => mk_en($result['total_nominal']),
            'counts' => $this->Penerimaan_uang_model->get_tab_counts()
        ));
    }

    public function export_excel()
    {
        $this->auth->restrict($this->viewPermission);
        $year = $this->input->get('year');
        $month = $this->input->get('month');
        $company = $this->input->get('company');
        $search = $this->input->get('search');

        $report_data = $this->Penerimaan_uang_model->get_report_data($year, $month, $company, $search, $this->input->get('receipt_kind'), $this->input->get('id_customer'));

        require_once APPPATH . 'libraries/Xlsx_export.php';
        $spec = Xlsx_export::report_spec($report_data, $year, $month, $company);
        Xlsx_export::download($spec, $spec['filename']);
    }



    public function add_penerimaan_uang($id)

    {

        $this->auth->restrict($this->viewPermission);



        // Fetch split record joined to detail

        $this->db->select('s.*, a.tanggal_transaksi, a.keterangan, a.nominal_debit, a.nominal_kredit, a.saldo, a.reference_no, a.nilai_terpakai, a.tipe_bank, a.jenis_bank');

        $this->db->from('tr_alokasi_split s');

        $this->db->join('tr_alokasi_detail a', 'a.id = s.id_alokasi_detail');

        $this->db->where('s.id', $id);

        $result = $this->db->get()->row_array();



        if (empty($result)) {

            $this->session->set_flashdata('error', 'Data alokasi split tidak ditemukan.');

            redirect('penerimaan_uang');

            return;

        }



        $nominal_penerimaan_bank = $result['nominal'] - $result['nilai_terpakai'];



        $receiptKind = (string)$result['jenis_alokasi'] === '8' ? 'deposit' : 'invoice';

        // Get customers filtered by receipt kind
        $this->db->select('a.id_customer, MAX(a.nm_customer) AS nm_customer');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.nm_customer !=', '');
        $this->db->where('a.nm_customer IS NOT NULL', null, false);
        $this->db->where('a.saldo_piutang >', 0);
        if ($receiptKind === 'deposit') {
            $this->db->where('a.tax_treatment', 'DEP');
        } else {
            $this->db->where('(a.tax_treatment IS NULL OR a.tax_treatment <> "DEP")', null, false);
        }
        $this->db->group_by('a.id_customer');
        $this->db->order_by('a.nm_customer', 'ASC');
        $get_customer = $this->db->get()->result_array();

        // Saran kwitansi dari pilihan di Alokasi (opsional): preselect customer dan kwitansi di form
        $kwitansiHint = null;
        if ($receiptKind === 'deposit' && !empty($result['no_kwitansi'])) {
            $hintInvoice = $this->db->select('id, id_customer')->get_where('tr_invoicing', ['no_invoice' => $result['no_kwitansi'], 'tax_treatment' => 'DEP', 'saldo_piutang >' => 0])->row_array();
            if ($hintInvoice) {
                $kwitansiHint = ['no_kwitansi' => $result['no_kwitansi'], 'id_customer' => (string)$hintInvoice['id_customer'], 'invoice_id' => (string)$hintInvoice['id']];
            }
        }

        $data = [
            'id_alokasi' => $result['id'],
            'kwitansi_hint' => $kwitansiHint,
            'receipt_kind' => $receiptKind,
            'tanggal_bank' => $this->bank_label($result),
            'list_customer' => $get_customer,
            'nominal_penerimaan_bank' => $nominal_penerimaan_bank
        ];

        $this->template->set('results', $data);
        $this->template->title($receiptKind === 'deposit' ? 'Penerimaan Piutang Kwitansi Deposit' : 'Penerimaan Piutang Invoice');
        $this->template->render('add');
    }

    /** True bila request hanya membawa customer (tanpa company/invoice): saldo & kartu dilihat per customer. */
    private function customer_only_context()
    {
        $customer = $this->input->post('id_customer'); $company = $this->input->post('id_company'); $id = $this->input->post('id_invoice');
        foreach (array($customer, $company, $id) as $value) if ($value !== null && !is_scalar($value)) throw new InvalidArgumentException('Identitas deposit tidak valid.');
        return trim((string)$customer) !== '' && trim((string)$company) === '' && trim((string)$id) === '';
    }

    /** "31-Dec-2025 · Bank BCA" untuk kolom readonly Tanggal & Bank pada form Kwitansi Deposit (mockup). */
    private function bank_label($alokasi)
    {
        $tgl = !empty($alokasi['tanggal_transaksi']) ? date('d-M-Y', strtotime($alokasi['tanggal_transaksi'])) : '-';
        $bank = !empty($alokasi['tipe_bank']) ? $this->db->get_where('ms_bank', ['id' => $alokasi['tipe_bank']])->row_array() : null;
        $listBank = !empty($alokasi['jenis_bank']) ? $this->db->get_where('list_bank', ['id' => $alokasi['jenis_bank']])->row_array() : null;
        $nama = trim((string)($listBank['nama_bank'] ?? '') . ' ' . (string)($bank['nama'] ?? ''));
        return $nama !== '' ? $tgl . ' · ' . $nama : $tgl;
    }

    private function deposit_context()
    {
        require_once __DIR__.'/../libraries/Consulting_receipt.php';
        $customer = $this->input->post('id_customer'); $company = $this->input->post('id_company');
        $id = $this->input->post('id_invoice');
        foreach (array($customer, $company, $id) as $value) if ($value !== null && !is_scalar($value)) throw new InvalidArgumentException('Identitas deposit tidak valid.');
        if ($id !== null && $id !== '') {
            $invoice = $this->db->get_where('tr_invoicing', array('id'=>$id))->row_array();
            if (!$invoice || !Invoice_consulting::is_new($invoice)) throw new InvalidArgumentException('Invoice Consultant baru tidak ditemukan.');
            $snapshot = Invoice_consulting::snapshot($invoice);
            if (($customer !== null && $customer !== '' && (string)$customer !== (string)$invoice['id_customer'])
                || ($company !== null && $company !== '' && (string)$company !== (string)$snapshot['owner']['id'])) throw new InvalidArgumentException('Customer/company tidak sesuai invoice.');
            $customer = $invoice['id_customer']; $company = $snapshot['owner']['id'];
        }
        if (trim((string)$customer) === '' || trim((string)$company) === '') throw new InvalidArgumentException('Pilih customer dan company deposit.');
        if (!Invoice_consulting_service::is_journal_owner($company)) throw new InvalidArgumentException('Company deposit tidak valid.');
        return array((string)$customer, (string)$company, $id);
    }

    public function get_receipt_companies()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            $customer = $this->input->post('id_customer');
            if (!is_scalar($customer) || trim((string)$customer) === '') throw new InvalidArgumentException('Pilih customer.');
            $invoices = $this->db->get_where('tr_invoicing', array('id_customer'=>$customer, 'saldo_piutang >'=>0))->result_array();
            $companies = array(); $deposit = $this->input->post('receipt_kind') === 'deposit';
            foreach ($invoices as $invoice) {
                if ((($invoice['tax_treatment'] ?? '') === 'DEP') !== $deposit) continue;
                if (Invoice_consulting::is_new($invoice)) {
                    $snapshot = Invoice_consulting::snapshot($invoice);
                    $companies[(string)$snapshot['owner']['id']] = array('id'=>(string)$snapshot['owner']['id'], 'name'=>$snapshot['owner']['nm_company']);
                } elseif (!$deposit) $companies['legacy'] = array('id'=>'legacy', 'name'=>'Invoice lama / Vuca (alur existing)');
            }
            $result = array('status'=>1, 'companies'=>array_values($companies));
        } catch (Throwable $e) { $result = array('status'=>0, 'message'=>$e instanceof RuntimeException || $e instanceof InvalidArgumentException ? $e->getMessage() : 'Company tidak dapat dimuat.', 'companies'=>array()); }
        echo json_encode($result);
    }

    public function get_customer_deposit()
    {
        $this->auth->restrict($this->viewPermission);
        $result = array('status'=>0, 'saldo'=>null, 'formatted'=>'Belum tersedia', 'id_customer'=>null, 'id_company'=>null, 'id_invoice'=>null);
        try {
            if ($this->customer_only_context()) {
                // Form tidak memilih company: saldo ditampilkan per customer, dengan rincian per company
                // supaya jelas berapa yang bisa dipakai untuk invoice dari company tertentu.
                require_once __DIR__.'/../libraries/Consulting_receipt.php';
                $customer = (string)$this->input->post('id_customer');
                $consultingReceipt = new Consulting_receipt($this);
                $summary = $consultingReceipt->get_customer_active_deposit_by_company($customer);
                $summary['total'] = $consultingReceipt->get_customer_active_deposit($customer); // saldo pakai = per customer (mockup)
                $result = array('status'=>1, 'saldo'=>Penerimaan_calculation::decimal($summary['total']), 'formatted'=>'Rp '.number_format($summary['total']/100,2,',','.'),
                    'id_customer'=>$customer, 'id_company'=>null, 'id_invoice'=>null, 'error'=>null, 'by_company'=>array());
                foreach ($summary['by_company'] as $row) {
                    $result['by_company'][] = array('id'=>$row['id'], 'name'=>$row['name'], 'saldo'=>Penerimaan_calculation::decimal($row['saldo']), 'formatted'=>'Rp '.number_format($row['saldo']/100,2,',','.'));
                }
                header('Content-Type: application/json'); echo json_encode($result); return;
            }
            list($customer, $company, $invoiceId) = $this->deposit_context();
            require_once __DIR__.'/../libraries/Consulting_receipt.php';
            $cents = (new Consulting_receipt($this))->get_customer_active_deposit($customer);
            $result = array('status'=>1, 'saldo'=>Penerimaan_calculation::decimal($cents), 'formatted'=>'Rp '.number_format($cents/100,2,',','.'),
                'id_customer'=>$customer, 'id_company'=>$company, 'id_invoice'=>$invoiceId, 'error'=>null);
        } catch (Throwable $e) { $result['error'] = $e instanceof RuntimeException || $e instanceof InvalidArgumentException ? $e->getMessage() : 'Saldo deposit tidak dapat dimuat.'; $result['message'] = $result['error']; }
        header('Content-Type: application/json'); echo json_encode($result);
    }

    public function get_customer_deposit_ledger()
    {
        $this->auth->restrict($this->viewPermission);
        $customerOnly = false;
        try {
            $customerOnly = $this->customer_only_context();
            if ($customerOnly) { $customerId = (string)$this->input->post('id_customer'); $companyId = null; }
            else list($customerId, $companyId) = $this->deposit_context();
        }
        catch (Throwable $e) { echo json_encode(array('status'=>0, 'message'=>$e instanceof InvalidArgumentException ? $e->getMessage() : 'Konteks deposit tidak valid.')); return; }
        $this->load->model('deposit_customer/Deposit_customer_model');
        require_once __DIR__.'/../libraries/Consulting_receipt.php';
        $depositError = null;
        if ($customerOnly) {
            $ledger = $this->Deposit_customer_model->get_customer_mutasi_ledger_all($customerId);
            try { $available = (new Consulting_receipt($this))->get_customer_active_deposit($customerId); }
            catch (RuntimeException $e) { $available = null; $depositError = $e->getMessage(); }
        } else {
            $ledger = $this->Deposit_customer_model->get_customer_mutasi_ledger($customerId, $companyId);
            try { $available = (new Consulting_receipt($this))->get_customer_active_deposit($customerId); }
            catch (RuntimeException $e) { $available = null; $depositError = $e->getMessage(); }
        }

        $totMasuk = 0;
        $totKeluar = 0;
        $running = 0;
        $rows = array();
        foreach ($ledger as $m) {
            $in = ($m['jenis_mutasi'] === 'IN') ? Penerimaan_calculation::cents($m['nominal']) : 0;
            $out = ($m['jenis_mutasi'] === 'OUT') ? Penerimaan_calculation::cents($m['nominal']) : 0;
            $totMasuk += $in;
            $totKeluar += $out;
            $running += ($in - $out);

            $rows[] = array(
                // Kartu deposit mockup (depTable): Tanggal full month, Referensi, Keterangan, Masuk, Keluar, Saldo (en()).
                'tanggal' => mk_fdate($m['tanggal']),
                'tipe' => $m['jenis_mutasi'],
                'sumber' => $m['tipe_sumber'] ?? $m['jenis_mutasi'],
                'no_dokumen' => $m['no_deposit'] ?? '-',
                'no_transaksi' => $m['no_transaksi'] ?? '-',
                'referensi' => $m['no_transaksi'] ?? '-',
                'company' => $m['id_company'] ?? '-',
                'keterangan' => $m['keterangan'] ?? '-',
                'masuk' => mk_en($in/100),
                'keluar' => mk_en($out/100),
                'saldo' => mk_en($running/100)
            );
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 1,
            'total_masuk' => 'Rp ' . number_format($totMasuk/100, 2, ',', '.'),
            'total_keluar' => 'Rp ' . number_format($totKeluar/100, 2, ',', '.'),
            'saldo_aktif' => $available === null ? 'Perlu rekonsiliasi' : 'Rp ' . number_format($available/100, 2, ',', '.'),
            'error' => $depositError, 'id_customer'=>$customerId, 'id_company'=>$companyId,
            'ledger' => $rows
        ));
    }

    public function get_inv_by_cust()
    {
        $this->auth->restrict($this->viewPermission);
        $post = $this->input->post();

        $nm_customer = is_array($post) && isset($post['nm_customer']) ? $post['nm_customer'] : '';

        $pph23_dipotong = is_array($post) && isset($post['pph23_dipotong']) ? $post['pph23_dipotong'] : 'Y';



        $this->db->select('a.id_customer, a.tanggal_invoice, a.created_date, a.id as no_inv, a.nm_customer, a.total_nominal_jurnal, a.dpp_lain_lain_jurnal, a.ppn_jurnal, a.pph_jurnal, a.total_akhir_jurnal, a.tagihan_ppn_jurnal, a.saldo_piutang, a.no_invoice, a.non_kons, a.tipe_invoice, a.tax_treatment, a.consulting_version, a.consulting_snapshot');

        $this->db->from('tr_invoicing a');

        $customerId = $this->input->post('id_customer');
        if ($customerId !== null && !is_scalar($customerId)) { echo '<tr><td colspan="11">Identitas customer tidak valid.</td></tr>'; return; }
        if (is_scalar($customerId) && trim((string)$customerId) !== '') {
            $this->db->where('a.id_customer', $customerId);
        } else {
            $this->db->where('a.nm_customer', is_scalar($nm_customer) ? $nm_customer : '');
            $this->db->where('(a.consulting_version IS NULL OR a.consulting_version <> 3)', null, false);
        }

        $this->db->where('a.saldo_piutang >', 0);
        if ($this->input->post('receipt_kind') === 'deposit') $this->db->where('a.tax_treatment', 'DEP');
        else $this->db->where("(a.tax_treatment IS NULL OR a.tax_treatment <> 'DEP')", null, false);

        $this->db->order_by('a.tanggal_invoice', 'ASC');
        $this->db->order_by('a.id', 'ASC');

        $get_invoice = $this->db->get()->result_array();



        $hasil = '';

        if (!empty($get_invoice)) {

            foreach ($get_invoice as $item) {

                // Company tidak lagi dipilih di form: setiap baris membawa grupnya (id company, atau 'legacy')
                // supaya satu penerimaan hanya memuat invoice dari satu company.
                $isNew = Invoice_consulting::is_new($item);
                $group = 'legacy'; $groupName = 'Invoice lama';
                if ($isNew) {
                    try { $ownerSnapshot = Invoice_consulting::snapshot($item); }
                    catch (RuntimeException $e) { continue; }
                    $group = (string)$ownerSnapshot['owner']['id'];
                    $groupName = (string)($ownerSnapshot['owner']['nm_company'] ?? $group);
                }
                $groupAttr = ' data-group="' . htmlspecialchars($group, ENT_QUOTES, 'UTF-8') . '" data-group-name="' . htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') . '"';
                $groupLabel = '';


                // Label memakai kode pajak yang tersimpan; invoice lama tanpa data itu tampil "-".
                $taxStored = Penerimaan_calculation::stored_tax_code(json_decode($item['consulting_snapshot'] ?? '', true), $item['tax_treatment'] ?? '');
                // Hitungan angka invoice lama tetap memakai perkiraan dari PPN supaya Total Tagihan tidak berubah; hanya label yang tidak menebak.
                $taxTreatment = $taxStored !== '' ? $taxStored : ((float)$item['ppn_jurnal'] > 0 ? 'PKP' : 'WAPU');

                if ($taxStored === '') {
                    $taxBadge = '<span class="text-muted" title="Perlakuan PPN belum tercatat di Invoicing">-</span>';
                } else {
                    $taxBadge = mk_tax_badge($taxStored);
                }
                $decimals = 0;

                $sales = (float)$item['total_nominal_jurnal'];

                $dppLain = (float)$item['dpp_lain_lain_jurnal'];

                if ($dppLain <= 0 && $sales > 0) {

                    $dppLain = round($sales * 11 / 12);

                }

                $ppn = (float)$item['ppn_jurnal'];

                if ($taxTreatment === 'WAPU' || $taxTreatment === 'KB') {

                    $ppn = 0;

                }



                $pphDefault = round($sales * 0.02);
                $receiptBlocked = false;
                if ($isNew) {
                    require_once __DIR__ . '/../libraries/Consulting_receipt.php';
                    try {
                        $contextInvoice = $item; $contextInvoice['id'] = $item['no_inv'];
                        $tax = Penerimaan_calculation::tax_calc(Penerimaan_calculation::cents($item['total_nominal_jurnal']), $taxTreatment);
                        $pphDefault = (new Consulting_receipt($this))->available_pph($contextInvoice, $tax['pph_default']) / 100;
                        $dppLain = $tax['dpp_lain'] / 100;
                    } catch (RuntimeException | InvalidArgumentException $error) {
                        $receiptBlocked = true;
                        $pphDefault = 0;
                    }
                }

                $saldoShow = (float)$item['saldo_piutang'];
                if (!$isNew) {
                    $legacyInv = $item; $legacyInv['id'] = $item['no_inv'];
                    try {
                        $budgetCents = $pph23_dipotong === 'Y'
                            ? $this->Penerimaan_uang_model->deducted_pph_budget($legacyInv)
                            : $this->Penerimaan_uang_model->remaining_receipt_pph($legacyInv, $pph23_dipotong);
                        $pphDefault = $this->Penerimaan_uang_model->default_pph($legacyInv, $budgetCents) / 100;
                        if (!$this->Penerimaan_uang_model->legacy_gross_base($legacyInv)) $saldoShow += $budgetCents / 100;
                    } catch (InvalidArgumentException $error) {
                        $receiptBlocked = true;
                    }
                }
                $pphVal = ($pph23_dipotong === 'Y') ? $pphDefault : 0;
                $tagihan = ($taxTreatment === 'PKP') ? ($sales + $ppn) : $sales;
                $tagihanMinPph = $tagihan - $pphVal;

                $invoiceDate = $item['tanggal_invoice'] ?? '';
                $invoiceTimestamp = $invoiceDate !== '' && $invoiceDate !== '0000-00-00' ? strtotime($invoiceDate) : false;
                $tgl = $invoiceTimestamp !== false ? mk_fdate($invoiceDate) : '-';
                $isDepositKind = ($this->input->post('receipt_kind') === 'deposit');
                if ($isDepositKind) {
                    $snapshot = json_decode($item['consulting_snapshot'] ?? '', true);
                    $noSpk = $snapshot['spk'] ?? (!empty($item['id_spk_penawaran']) ? $item['id_spk_penawaran'] : '-');
                    $nmCompany = $snapshot['owner']['nm_company'] ?? 'STM';

                    $hasil .= '<tr>';
                    $hasil .= '<td class="nowrap">' . $tgl . '</td>';
                    $hasil .= '<td class="nowrap">' . htmlspecialchars($item['no_invoice'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $hasil .= '<td class="nowrap">' . htmlspecialchars($noSpk, ENT_QUOTES, 'UTF-8') . '</td>';
                    $hasil .= '<td>' . htmlspecialchars($nmCompany, ENT_QUOTES, 'UTF-8') . '</td>';
                    $hasil .= '<td class="r">' . mk_en($item['total_nominal_jurnal']) . '</td>';
                    $hasil .= '<td class="r">' . mk_en($item['saldo_piutang']) . '</td>';
                    $hasil .= '<td class="c">';
                    $hasil .= '<input type="checkbox" name="choose_inv[]" value="' . htmlspecialchars($item['no_inv'], ENT_QUOTES, 'UTF-8') . '"' . $groupAttr . '>';
                    $hasil .= '</td>';
                    $hasil .= '</tr>';
                } else {
                    $hasil .= '<tr>';
                    $hasil .= '<td class="nowrap">' . $tgl . '</td>';
                    $hasil .= '<td class="nowrap">' . htmlspecialchars($item['no_invoice'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $hasil .= '<td>' . $taxBadge . '</td>';
                    $hasil .= '<td class="r">' . mk_en($sales) . '</td>';
                    $hasil .= '<td class="r">' . mk_en($dppLain) . '</td>';
                    $hasil .= '<td class="r">' . mk_en($ppn) . '</td>';
                    $hasil .= '<td class="r">' . mk_en($pphVal) . '</td>';
                    $hasil .= '<td class="r">' . mk_en($tagihan) . '</td>';
                    $hasil .= '<td class="r">' . mk_en($tagihanMinPph) . '</td>';
                    $hasil .= '<td class="r">' . mk_en($saldoShow) . '</td>';
                    $hasil .= '<td class="c">';
                    $hasil .= '<input type="checkbox" name="choose_inv[]" value="' . htmlspecialchars($item['no_inv'], ENT_QUOTES, 'UTF-8') . '"' . $groupAttr . ($receiptBlocked ? ' disabled' : '') . '>';
                    if ($receiptBlocked) $hasil .= '<small class="text-danger">Riwayat PPh perlu rekonsiliasi.</small>';
                    $hasil .= '</td>';
                    $hasil .= '</tr>';
                }
            }
        } else {
            $isDepositKind = ($this->input->post('receipt_kind') === 'deposit');
            $colSpan = $isDepositKind ? '7' : '11';
            $msg = $isDepositKind ? 'Customer ini tidak punya Kwitansi Deposit yang masih terbuka.' : 'No Data Found';
            $hasil = '<tr><td colspan="' . $colSpan . '" class="empty">' . $msg . '</td></tr>';
        }



        if ($hasil === '') $hasil = '<tr><td colspan="11" class="empty">' . (($this->input->post('receipt_kind') === 'deposit') ? 'Customer ini tidak punya Kwitansi Deposit yang masih terbuka.' : 'No Data Found') . '</td></tr>';
        echo $hasil;

    }



    public function process_alokasi()

    {
        if ($this->deny_without($this->addPermission, false)) return;

        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $consulting=new Consulting_receipt($this);

        if ($consulting->contains_new($this->input->post('choose_inv'))) {

            try {

                $post=$this->input->post(); $post['receipt_kind']='invoice';
                $data=$consulting->preview($post);

                $this->template->set($data);$this->template->render('consulting_process');

            } catch (Throwable $error) { echo '<div class="alert alert-danger" role="alert">'.htmlspecialchars($error instanceof RuntimeException || $error instanceof InvalidArgumentException?$error->getMessage():'Preview tidak dapat dimuat.',ENT_QUOTES,'UTF-8').'</div>'; }

            return;

        }



        $post = $this->input->post();

        // Deposit invoice lama: hanya untuk invoice yang belum pernah diproses penerimaannya (dicek per invoice di bawah).
        $useDep = ($post['deposit_choice'] ?? 'N') === 'Y';
        $depCents = $useDep ? $this->legacy_cents($post['deposit_amount'] ?? '') : 0;
        if ($useDep && $depCents <= 0) {
            echo '<div class="alert alert-danger" role="alert">Nominal deposit dipakai harus lebih besar dari 0.</div>';
            return;
        }

        // Form hanya mengirim id_customer; alur invoice lama bekerja dengan nama customer, jadi ambil dari invoice yang dicentang.
        if (!isset($post['customer'])) {
            $firstInv = is_array($post['choose_inv'] ?? null) ? reset($post['choose_inv']) : '';
            $invRow = is_scalar($firstInv) ? $this->db->select('nm_customer')->get_where('tr_invoicing', array('id' => $firstInv))->row_array() : null;
            $post['customer'] = $invRow['nm_customer'] ?? '';
        }

        $pph23_dipotong = $post['pph23_dipotong'];

        $ppn_dipotong = ($post['ppn_dipotong'] ?? null);



        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($post['id_alokasi']);

        $split = $resolved['split'];

        $detail = $resolved['detail'];



        $uang_masuk = $split['nominal'];







        $hasil = '';

        $hasil_jurnal = '';

        $total_piutang = 0;

        $total_piutang_dagang = 0;



        $total_debit = 0;

        $total_kredit = 0;



        $no = 0;

        $lebihCompanies = array(); $lebihInvoiceNos = array();
        foreach ($post['choose_inv'] as $item) {



            $no++;



            $get_inv = $this->db->get_where('tr_invoicing', ['id' => $item])->row_array();

            if ($useDep && $this->db->where('id_inv', $item)->count_all_results('tr_penerimaan_piutang_detail') > 0) {
                echo '<div class="alert alert-danger" role="alert">Deposit hanya bisa dipakai untuk invoice lama yang belum pernah diproses penerimaannya (' . htmlspecialchars((string)$get_inv['no_invoice'], ENT_QUOTES, 'UTF-8') . ').</div>';
                return;
            }

            try {

                // Y: anggaran PPh yang bisa disesuaikan pengguna (kolom PPH 23 editable); N: sisa batas PPh seperti sebelumnya.
                $remaining_pph = $pph23_dipotong === 'Y'
                    ? $this->Penerimaan_uang_model->deducted_pph_budget($get_inv)
                    : $this->Penerimaan_uang_model->remaining_receipt_pph($get_inv, $pph23_dipotong);

            } catch (InvalidArgumentException $e) {

                echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';

                return;

            }



            if ($get_inv['non_kons'] == '1') {

                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran_non_konsultasi', ['id_penawaran' => $get_inv['id_penawaran']])->row_array();



                $id_company = '1';

                $nm_company = 'STM-Vuca';

            } else {

                $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();



                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_spk_penawaran['id_penawaran']])->row_array();



                $company = (!empty($get_penawaran['company'])) ? $get_penawaran['company'] : $get_spk_penawaran['id_company'];



                $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $company])->row_array();

                $id_company = (!empty($get_company)) ? $get_company['id'] : '';

                $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';

            }

            $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();



            $lebihCompanies[$this->legacy_owner_id($id_company)] = (string)$nm_company;
            $lebihInvoiceNos[] = (string)$get_inv['no_invoice'];
            $get_bank = $this->db->get_where('ms_bank', ['id' => $detail['tipe_bank']])->row_array();



            $this->accounting->select('a.nama as nm_coa');

            $this->accounting->from('coa_master a');

            $this->accounting->where('a.no_perkiraan', $get_bank['coa_bank']);

            $get_coa_bank = $this->accounting->get()->row_array();



            $coa_bank = (!empty($get_bank)) ? $get_bank['coa_bank'] : '';

            $nm_coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank['nm_coa'] : '';



            // Keep the actual outstanding balance, including previous installments.

            // Mockup: Piutang Dagang bruto = saldo (neto PPh, penerimaan pertama) + anggaran PPh lama; setelahnya saldo sudah bruto.
            $saldo_piutang = (Penerimaan_calculation::cents($get_inv['saldo_piutang']) + ($this->Penerimaan_uang_model->legacy_gross_base($get_inv) ? 0 : $remaining_pph)) / 100;

            // Old Vuca invoices stored zero gross despite having a valid DPP.

            $tagihan_ppn = Penerimaan_calculation::invoice_gross($get_inv) / 100;



            $hasil .= '<tr>';

            $legacyTax = Penerimaan_calculation::stored_tax_code(json_decode($get_inv['consulting_snapshot'] ?? '', true), $get_inv['tax_treatment'] ?? '');
            $hasil .= '<td class="text-center nowrap">' . htmlspecialchars((string)$get_inv['no_invoice'], ENT_QUOTES, 'UTF-8');
            $hasil .= '<input type="hidden" name="id_inv_' . $no . '" value="' . $item . '">';
            $hasil .= '<input type="hidden" name="remaining_pph_' . $no . '" value="' . Penerimaan_calculation::decimal($pph23_dipotong === 'Y' ? $remaining_pph : 0) . '">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">' . ($legacyTax !== '' ? '<span class="tax-badge tax-' . htmlspecialchars($legacyTax, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($legacyTax, ENT_QUOTES, 'UTF-8') . '</span>' : '-') . '</td>';

            $hasil .= '<td class="text-right">' . number_format($get_inv['total_nominal_jurnal']) . '</td>';

            $hasil .= '<td class="text-right">' . number_format($get_inv['dpp_lain_lain_jurnal']) . '</td>';

            $hasil .= '<td class="text-right">' . number_format($get_inv['ppn_jurnal']) . '</td>';

            if ($pph23_dipotong === 'Y') {
                $default_pph = $this->Penerimaan_uang_model->default_pph($get_inv, $remaining_pph);
                $pphDefault = Penerimaan_calculation::decimal($default_pph);
                $hasil .= '<td class="text-right"><input type="text" class="form-control form-control-sm text-right autonum" name="pph_actual_' . $no . '" data-default="' . $pphDefault . '" value="' . $pphDefault . '" onkeyup="pphEdited(' . $no . ')">'
                    . '<div class="reset-pph-link-' . $no . '" style="display:none; font-size: 11px; margin-top: 2px;"><a href="javascript:void(0)" onclick="resetPph(' . $no . ')">&#8634; Default (Rp ' . number_format($default_pph / 100) . ')</a></div></td>';
            } else {
                $hasil .= '<td class="text-right"><input type="text" class="form-control form-control-sm text-right" readonly value="0,00"></td>';
            }

            $hasil .= '<td class="text-right">' . number_format($tagihan_ppn) . '</td>';

            // Total Tagihan - PPH: dihitung ulang di browser saat PPh diubah; awalnya tagihan dikurangi anggaran PPh (Potong) atau tagihan penuh (Tidak potong).
            $hasil .= '<td class="text-right" id="tagihan_net_' . $no . '" data-tagihan="' . $tagihan_ppn . '">' . number_format($pph23_dipotong === 'Y' ? $tagihan_ppn - $default_pph / 100 : $tagihan_ppn) . '</td>';

            $hasil .= '<td>';

            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="piutang_dagang_' . $no . '" value="' . $saldo_piutang . '" onkeyup="hitungAll()" readonly>';

            $hasil .= '</td>';

            $hasil .= '<td class="text-left">';

            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="penerimaan_' . $no . '" value="' . $saldo_piutang . '" onkeyup="penEdited(' . $no . ')">';

            $hasil .= '</td>';

            if ($useDep) {
                $hasil .= '<td class="text-left"><input type="text" class="form-control form-control-sm text-right autonum" name="dep_' . $no . '" onkeyup="depEdited()"></td>';
            }
            $hasil .= '<td class="text-left">';

            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="biaya_admin_' . $no . '" onkeyup="hitungAll()">';

            $hasil .= '</td>';

            $hasil .= '<td class="text-left">';

            // Lebih Bayar: kelebihan uang masuk menjadi saldo deposit customer; dikunci di bawah bila company invoice tidak eligible.
            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="lebih_bayar_' . $no . '" data-lebih onkeyup="hitungAll()">';

            $hasil .= '</td>';

            $hasil .= '<td class="text-left">';

            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="sisa_piutang_' . $no . '" value="" onkeyup="sisaEdited(' . $no . ')">';

            $hasil .= '</td>';

            $hasil .= '</tr>';



            $total_piutang += ($pph23_dipotong == 'N') ? round($tagihan_ppn) : round($get_inv['total_akhir_jurnal']);

            // $total_piutang += $get_inv['total_akhir_jurnal'];

            $total_piutang_dagang += round($saldo_piutang);



            if ($no == 1) {

                $hasil_jurnal .= '<tr class="tr_jurnal_row">';



                $hasil_jurnal .= '<td class="text-center">';

                $hasil_jurnal .= date('d-F-Y', strtotime($detail['tanggal_transaksi']));

                $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_debit" value="' . $detail['tanggal_transaksi'] . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '<td class="text-center">';

                $hasil_jurnal .= $coa_bank;

                $hasil_jurnal .= '<input type="hidden" name="coa_bank_debit" value="' . $coa_bank . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '<td class="text-center">';

                $hasil_jurnal .= $nm_company;

                $hasil_jurnal .= '<input type="hidden" name="id_company_debit" value="' . $id_company . '">';

                $hasil_jurnal .= '<input type="hidden" name="nm_company_debit" value="' . $nm_company . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '<td class="text-center">';

                $hasil_jurnal .= $nm_coa_bank;

                $hasil_jurnal .= '<input type="hidden" name="nm_coa_bank_debit" value="' . $nm_coa_bank . '">';

                $hasil_jurnal .= '</td>';
                $hasil_jurnal .= '<td class="text-left">' . $nm_coa_bank . ' - ' . $get_inv['no_invoice'] . '</td>';



                $hasil_jurnal .= '<td class="text-center td_debit_bank_debit">';

                $hasil_jurnal .= number_format($uang_masuk);

                $hasil_jurnal .= '<input type="hidden" name="debit_bank_debit" value="' . intval($uang_masuk) . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '<td class="text-center td_kredit_bank_debit">';

                $hasil_jurnal .= number_format(0);

                $hasil_jurnal .= '<input type="hidden" name="kredit_bank_debit" value="' . 0 . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '</tr>';



                $total_debit += $uang_masuk;



                // Tentukan COA PPh berdasarkan tipe_invoice (VUCA = 1106-01-05, lainnya = 1106-01-02)

                $coa_pph = (!empty($get_inv['tipe_invoice']) && $get_inv['tipe_invoice'] == '1') ? '1106-01-05' : '1106-01-02';

                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', $coa_pph];



                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');

                $this->accounting->from('coa_master a');

                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);

                $get_coa_jurnal = $this->accounting->get()->result_array();



                foreach ($get_coa_jurnal as $item_coa_jurnal) {



                    $value_debit = 0;

                    $value_kredit = 0;





                    if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {

                        // $value_kredit = $get_inv['tagihan_ppn_jurnal'];

                        // if ($post['pph23_dipotong'] == 'N') {

                        $value_kredit = $saldo_piutang;

                        // }

                    }



                    $hasil_jurnal .= '<tr class="tr_jurnal_row">';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= date('d-F-Y', strtotime($detail['tanggal_transaksi']));

                    $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $detail['tanggal_transaksi'] . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= $item_coa_jurnal['no_perkiraan'];

                    $hasil_jurnal .= '<input type="hidden" name="coa_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $coa_bank . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= $nm_company;

                    $hasil_jurnal .= '<input type="hidden" name="id_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $id_company . '">';

                    $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $nm_company . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= $item_coa_jurnal['nm_coa'];

                    $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $item_coa_jurnal['nm_coa'] . '">';

                    $hasil_jurnal .= '</td>';
                    $hasil_jurnal .= '<td class="text-left">' . $item_coa_jurnal['nm_coa'] . ' - ' . $get_inv['no_invoice'] . '</td>';



                    $hasil_jurnal .= '<td class="text-center td_debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_debit);

                    $hasil_jurnal .= '<input type="hidden" name="debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_debit) . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center td_kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_kredit);

                    $hasil_jurnal .= '<input type="hidden" name="kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_kredit) . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '</tr>';



                    $total_debit += $value_debit;

                    $total_kredit += $value_kredit;

                }

            } else {

                // Tentukan COA PPh berdasarkan tipe_invoice (VUCA = 1106-01-05, lainnya = 1106-01-02)

                $coa_pph = (!empty($get_inv['tipe_invoice']) && $get_inv['tipe_invoice'] == '1') ? '1106-01-05' : '1106-01-02';

                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', $coa_pph];



                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');

                $this->accounting->from('coa_master a');

                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);

                $get_coa_jurnal = $this->accounting->get()->result_array();



                foreach ($get_coa_jurnal as $item_coa_jurnal) {

                    $value_debit = 0;

                    $value_kredit = 0;



                    if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {

                        $value_kredit = $saldo_piutang;

                    }



                    $hasil_jurnal .= '<tr class="tr_jurnal_row">';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= date('d-F-Y', strtotime($detail['tanggal_transaksi']));

                    $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $detail['tanggal_transaksi'] . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= $item_coa_jurnal['no_perkiraan'];

                    $hasil_jurnal .= '<input type="hidden" name="coa_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $coa_bank . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= $nm_company;

                    $hasil_jurnal .= '<input type="hidden" name="id_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $id_company . '">';

                    $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $nm_company . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= $item_coa_jurnal['nm_coa'];

                    $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $item_coa_jurnal['nm_coa'] . '">';

                    $hasil_jurnal .= '</td>';
                    $hasil_jurnal .= '<td class="text-left">' . $item_coa_jurnal['nm_coa'] . ' - ' . $get_inv['no_invoice'] . '</td>';



                    $hasil_jurnal .= '<td class="text-center td_debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_debit);

                    $hasil_jurnal .= '<input type="hidden" name="debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_debit) . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center td_kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_kredit);

                    $hasil_jurnal .= '<input type="hidden" name="kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_kredit) . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '</tr>';



                    $total_debit += $value_debit;

                    $total_kredit += $value_kredit;

                }

            }

        }







        // Lebih Bayar invoice lama: hanya bila semua invoice satu company pemilik jurnal (7/3/4), karena voucher deposit dipakai per company.
        $lebihEligible = count($lebihCompanies) === 1 && Invoice_consulting_service::is_journal_owner((string)key($lebihCompanies));
        if (!$lebihEligible) $hasil = str_replace(' data-lebih ', ' data-lebih readonly ', $hasil);
        $depSaldo = 0;
        if ($useDep) {
            $depCustomers = array_unique(array_map(function ($id) { return (string)($this->db->select('id_customer')->get_where('tr_invoicing', ['id' => $id])->row()->id_customer ?? ''); }, $post['choose_inv']));
            if (!$lebihEligible || count($depCustomers) !== 1 || $depCustomers[0] === '') {
                echo '<div class="alert alert-danger" role="alert">Deposit hanya untuk invoice satu customer dari satu company pemilik jurnal (STM, Sustain, atau Vuca).</div>';
                return;
            }
            try { $depSaldo = $consulting->get_customer_active_deposit($depCustomers[0]); }
            catch (Throwable $error) { echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>'; return; }
            if ($depCents > $depSaldo) {
                echo '<div class="alert alert-danger" role="alert">Deposit dipakai (' . number_format($depCents / 100, 2) . ') melebihi saldo deposit customer (' . number_format($depSaldo / 100, 2) . ').</div>';
                return;
            }
        }
        $coaDeposit = $this->accounting->get_where('coa_master', ['no_perkiraan' => '2102-01-02'])->row_array();
        $nmCoaDeposit = !empty($coaDeposit['nama']) ? $coaDeposit['nama'] : 'Deposit Customer';
        $hasil_jurnal .= '<tr class="tr_jurnal_lebih" style="display:none;">';
        $hasil_jurnal .= '<td class="text-center">' . date('d-F-Y', strtotime($detail['tanggal_transaksi'])) . '</td>';
        $hasil_jurnal .= '<td class="text-center">2102-01-02</td>';
        $hasil_jurnal .= '<td class="text-center">' . htmlspecialchars((string)reset($lebihCompanies), ENT_QUOTES, 'UTF-8') . '</td>';
        $hasil_jurnal .= '<td class="text-center">' . htmlspecialchars($nmCoaDeposit, ENT_QUOTES, 'UTF-8') . '</td>';
        $hasil_jurnal .= '<td class="text-left">' . htmlspecialchars($nmCoaDeposit, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars(implode(', ', $lebihInvoiceNos), ENT_QUOTES, 'UTF-8') . '</td>';
        $hasil_jurnal .= '<td class="text-center">0</td>';
        $hasil_jurnal .= '<td class="text-center td_kredit_lebih">0<input type="hidden" name="kredit_lebih" value="0"></td>';
        $hasil_jurnal .= '</tr>';
        if ($useDep) {
            // Deposit Customer sisi debit (deposit dipakai); diisi/ditampilkan oleh hitungAll().
            $hasil_jurnal .= '<tr class="tr_jurnal_dep" style="display:none;">';
            $hasil_jurnal .= '<td class="text-center">' . date('d-F-Y', strtotime($detail['tanggal_transaksi'])) . '</td>';
            $hasil_jurnal .= '<td class="text-center">2102-01-02</td>';
            $hasil_jurnal .= '<td class="text-center">' . htmlspecialchars((string)reset($lebihCompanies), ENT_QUOTES, 'UTF-8') . '</td>';
            $hasil_jurnal .= '<td class="text-center">' . htmlspecialchars($nmCoaDeposit, ENT_QUOTES, 'UTF-8') . '</td>';
            $hasil_jurnal .= '<td class="text-left">' . htmlspecialchars($nmCoaDeposit, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars(implode(', ', $lebihInvoiceNos), ENT_QUOTES, 'UTF-8') . '</td>';
            $hasil_jurnal .= '<td class="text-center td_debit_dep">0</td>';
            $hasil_jurnal .= '<td class="text-center">0</td>';
            $hasil_jurnal .= '</tr>';
        }

        $data = [

            'hasil' => $hasil,

            'hasil_jurnal' => $hasil_jurnal,

            'total_piutang' => $total_piutang,

            'total_piutang_dagang' => $total_piutang_dagang,

            'uang_masuk' => $uang_masuk,

            'id_alokasi' => $post['id_alokasi'],

            'no_inv' => $no,

            'total_debit' => $total_debit,

            'total_kredit' => $total_kredit,
            'lebih_eligible' => $lebihEligible,
            'use_dep' => $useDep,
            'dep_amount' => $depCents / 100,
            'dep_saldo' => $depSaldo / 100,

            'nm_customer' => $post['customer'],

            'ppn_dipotong' => ($post['ppn_dipotong'] ?? null),

            'pph23_dipotong' => $post['pph23_dipotong'],

            'nominal_penerimaan_bank' => str_replace(',', '', $post['nominal_penerimaan_bank'])

        ];

        $this->template->set($data);

        $this->template->render('process');

    }



    public function process_kwitansi_deposit()
    {
        if ($this->deny_without($this->addPermission, false)) return;
        require_once __DIR__.'/../libraries/Consulting_receipt.php';
        try {
            $post = $this->input->post(); $post['receipt_kind'] = 'deposit';
            $data = (new Consulting_receipt($this))->preview($post);
            $this->template->set($data); $this->template->render('consulting_process');
        } catch (Throwable $e) {
            log_message('error', 'process_kwitansi_deposit: ' . get_class($e) . ': ' . $e->getMessage());
            echo '<div class="alert alert-danger">'.htmlspecialchars($e instanceof RuntimeException || $e instanceof InvalidArgumentException ? $e->getMessage() : 'Preview gagal.', ENT_QUOTES, 'UTF-8').'</div>';
        }
    }

    public function save_kwitansi_deposit()
    {
        if ($this->deny_without($this->addPermission)) return;
        require_once __DIR__.'/../libraries/Consulting_receipt.php';
        $post = $this->input->post(); $post['receipt_kind'] = 'deposit';
        echo json_encode((new Consulting_receipt($this))->save($post));
    }

    public function save_penerimaan_piutang()

    {
        if ($this->deny_without($this->addPermission)) return;

        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $consulting=new Consulting_receipt($this);$post=$this->input->post();$ids=array();

        $count=is_scalar($post['no_inv']??null)?(int)$post['no_inv']:0;

        if($count<1 || $count>100){echo json_encode(array('status'=>0,'msg'=>'Jumlah invoice tidak valid.','message'=>'Jumlah invoice tidak valid.'));return;}

        for($n=1;$n<=$count;$n++)$ids[]=$post['id_inv_'.$n]??'';

        if($consulting->contains_new($ids)){$post['receipt_kind']='invoice';echo json_encode($consulting->save($post));return;}


        $service=new Invoice_consulting_service($this);

        return $service->serialized(function()use($consulting,$post){

            try { $consulting->guard_legacy_allocation($post['id_alokasi']??''); }

            catch(RuntimeException $error){echo json_encode(array('status'=>0,'msg'=>$error->getMessage(),'message'=>$error->getMessage()));return;}

            $this->save_legacy_penerimaan_piutang();

        });

    }



    /** Nominal modal invoice lama: menerima '140,610,000.00' (autoNumeric add.php) maupun '2.574.990,00' (format Indonesia); keduanya tidak ambigu. */
    private function legacy_cents($raw)
    {
        $v = trim((string)$raw);
        if ($v === '') return 0;
        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d{1,2})?$/', $v) || preg_match('/^-?\d+\.\d{1,2}$/', $v)) return Penerimaan_calculation::cents(str_replace(',', '', $v));
        return Penerimaan_calculation::cents($v, true);
    }

    /** id kons_tr_company -> owner jurnal/deposit (7 STM, 3 Sustain, 4 Vuca); STM-Sustain(6) dan STM-Vuca(1) mengikuti pemetaan modul lain. Selain itu '' (tidak eligible). */
    private function legacy_owner_id($companyId)
    {
        $map = array('1' => '4', '4' => '4', '6' => '3', '3' => '3', '7' => '7');
        return $map[(string)$companyId] ?? '';
    }

    private function legacy_fail($message)
    {
        echo json_encode(array('status' => 0, 'msg' => $message, 'message' => $message));
    }

    private function save_legacy_penerimaan_piutang()

    {

        $post = $this->input->post();
        $useDep = ($post['deposit_choice'] ?? 'N') === 'Y';
        $depCents = $useDep ? $this->legacy_cents($post['deposit_amount'] ?? '') : 0;
        if ($useDep && $depCents <= 0) { $this->legacy_fail('Nominal deposit dipakai harus lebih besar dari 0.'); return; }
        $id = $this->Penerimaan_uang_model->generate_id();
        $nm_customer = $post['nm_customer'];



        // Try to find id_customer from consultant database

        $this->consultant->select('a.id_customer');

        $this->consultant->from('customer a');

        $this->consultant->where('a.nm_customer', $nm_customer);

        $get_customer = $this->consultant->get()->row_array();

        $id_customer_save = (!empty($get_customer)) ? $get_customer['id_customer'] : '';



        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($post['id_alokasi']);

        $split = $resolved['split'];

        $detail = $resolved['detail'];

        $get_ms_bank = $this->db->get_where('ms_bank', ['id' => $detail['tipe_bank']])->row_array();



        $coa_bank = (!empty($get_ms_bank)) ? $get_ms_bank['coa_bank'] : '';



        $get_coa_bank = $this->accounting->get_where('coa_master', ['no_perkiraan' => $coa_bank])->row_array();

        $nm_coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank['nama'] : '';



        $arr_insert_header = [

            'no_surat' => $id,

            'id_alokasi' => $post['id_alokasi'],

            'id_customer' => $id_customer_save,

            'nm_customer' => $nm_customer,

            'ppn_dipotong' => ($post['ppn_dipotong'] ?? null),

            'pph23_dipotong' => $post['pph23_dipotong'],

            'nominal_penerimaan_bank' => str_replace(',', '.', str_replace('.', '', $post['nominal_penerimaan_bank'])),

            'created_by' => $this->session->userdata('id_user'),

            'created_date' => date('Y-m-d H:i:s')

        ];



        $arr_insert_detail = [];

        $arr_update_inv = [];

        $arr_insert_jurnal = [];



        $total_penerimaan = 0;

        $total_penerimaan_cents = 0;



        $no_jurnal = 0;
        $lebihRows = array();

        for ($i = 1; $i <= $post['no_inv']; $i++) {



            $this->db->select('a.*');

            $this->db->from('tr_invoicing a');

            $this->db->where('a.id', $post['id_inv_' . $i]);

            $get_inv = $this->db->get()->row_array();



            if ($get_inv['non_kons'] == '1') {

                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran_non_konsultasi', ['id_penawaran' => $get_inv['id_penawaran']])->row_array();



                $id_company = '1';

                $nm_company = 'STM-Vuca';

            } else {

                $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();



                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_spk_penawaran['id_penawaran']])->row_array();



                $company = (!empty($get_penawaran['company'])) ? $get_penawaran['company'] : $get_spk_penawaran['id_company'];



                $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $company])->row_array();



                $id_company = (!empty($get_company)) ? $get_company['id'] : '';

                $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';

            }



            $dpp = (!empty($get_inv)) ? $get_inv['total_nominal_jurnal'] : 0;

            $dpp_lain = (!empty($get_inv)) ? $get_inv['dpp_lain_lain_jurnal'] : 0;

            $ppn = (!empty($get_inv)) ? $get_inv['ppn_jurnal'] : 0;

            $tagihan_ppn = (!empty($get_inv)) ? Penerimaan_calculation::decimal(Penerimaan_calculation::invoice_gross($get_inv)) : 0;

            $pph = (!empty($get_inv)) ? $get_inv['pph_jurnal'] : 0;

            if ($post['pph23_dipotong'] == 'N') {

                $pph = 0; // Force PPh to 0 if not deducted

            }



            $total = (!empty($get_inv)) ? $get_inv['total_akhir_jurnal'] : 0;

            $saldo_piutang = (!empty($get_inv)) ? $get_inv['saldo_piutang'] : 0;

            $tgl_inv = (!empty($get_inv)) ? date('Y-m-d', strtotime($get_inv['created_date'])) : '';



            try {

                // Basis mockup (bruto): saldo tersimpan neto PPh pada penerimaan pertama => G = saldo + anggaran PPh lama; sesudahnya saldo sudah bruto.
                $remaining_pph = $post['pph23_dipotong'] === 'Y'
                    ? $this->Penerimaan_uang_model->deducted_pph_budget($get_inv)
                    : $this->Penerimaan_uang_model->remaining_receipt_pph($get_inv, $post['pph23_dipotong']);
                $gross_base = $this->Penerimaan_uang_model->legacy_gross_base($get_inv);
                $balance_cents = Penerimaan_calculation::cents($saldo_piutang) + ($gross_base ? 0 : $remaining_pph);
                $pph_budget = $post['pph23_dipotong'] === 'Y' ? $remaining_pph : 0;
                $pph_actual = $post['pph23_dipotong'] === 'Y' ? ((isset($post['pph_actual_' . $i]) && trim((string)$post['pph_actual_' . $i]) !== '') ? $this->legacy_cents($post['pph_actual_' . $i]) : $pph_budget) : 0;
                $receipt_cents = $this->legacy_cents($post['penerimaan_' . $i]);
                $admin_cents = $this->legacy_cents($post['biaya_admin_' . $i]);
                $sisa_cents = $this->legacy_cents($post['sisa_piutang_' . $i] ?? '');
                $dep_cents = $useDep ? $this->legacy_cents($post['dep_' . $i] ?? '') : 0;
                if ($dep_cents < 0) throw new InvalidArgumentException('Deposit dipakai tidak boleh negatif.');
                if ($useDep && $this->db->where('id_inv', $get_inv['id'])->count_all_results('tr_penerimaan_piutang_detail') > 0) {
                    throw new InvalidArgumentException('Deposit hanya bisa dipakai untuk invoice lama yang belum pernah diproses penerimaannya (' . ($get_inv['no_invoice'] ?? '') . ').');
                }
                $admin_total_cents = ($admin_total_cents ?? 0) + $admin_cents;
                if ($admin_total_cents > Penerimaan_calculation::MAX_ADMIN_CENTS) {
                    throw new InvalidArgumentException('Total biaya admin melebihi ' . number_format(Penerimaan_calculation::MAX_ADMIN_CENTS / 100) . '.');
                }
                $calculation = Penerimaan_calculation::settle($balance_cents, $pph_budget, $post['pph23_dipotong'], $pph_actual, $admin_cents, $sisa_cents, $dep_cents);
                if ($receipt_cents < 0 || $receipt_cents + $sisa_cents !== $balance_cents) {
                    throw new InvalidArgumentException('Alokasi penerimaan + sisa piutang harus sama dengan piutang dagang invoice ' . ($get_inv['no_invoice'] ?? '') . '.');
                }

            } catch (InvalidArgumentException $e) {

                echo json_encode(array('status'=>0, 'msg'=>$e->getMessage(), 'message'=>$e->getMessage()));

                return;

            }

            try {
                $lebih_cents = $this->legacy_cents($post['lebih_bayar_' . $i] ?? '');
                if ($lebih_cents < 0) throw new InvalidArgumentException('Lebih Bayar tidak boleh negatif.');
            } catch (InvalidArgumentException $e) {
                $this->legacy_fail($e->getMessage());
                return;
            }
            $cash_cents = $calculation['cash'];
            $penerimaan = Penerimaan_calculation::decimal($receipt_cents);
            $biaya_admin = Penerimaan_calculation::decimal($admin_cents);
            $sisa_piutang = Penerimaan_calculation::decimal($calculation['remaining']);
            $pph = Penerimaan_calculation::decimal($pph_actual);



            $arr_insert_detail[] = [

                'id_header' => $id,

                'id_alokasi' => $post['id_alokasi'],

                'id_inv' => $post['id_inv_' . $i],

                'tgl_inv' => $tgl_inv,

                'id_customer' => $id_customer_save,

                'nm_customer' => $nm_customer,

                'dpp' => $dpp,

                'dpp_lain' => $dpp_lain,

                'ppn' => $ppn,

                'tagihan_ppn' => $tagihan_ppn,

                'pph23' => $pph,

                'total' => $total,

                'penerimaan' => $penerimaan,

                'biaya_admin' => $biaya_admin,

                'sisa_piutang' => $sisa_piutang,

                'created_by' => $this->auth->user_id(),

                'created_date' => date('Y-m-d H:i:s')

            ];



            // Penanda basis bruto (dibaca legacy_gross_base(), deducted_pph_budget() dan rollback); tanpa kolom baru.
            $gross_mark = array('gross_basis' => 1, 'prev_saldo' => Penerimaan_calculation::cents($saldo_piutang), 'pph_used' => $pph_actual);
            $arr_insert_detail[count($arr_insert_detail) - 1]['consulting_snapshot'] = json_encode($gross_mark);
            $arr_update_inv[] = [

                'id' => $post['id_inv_' . $i],

                'saldo_piutang' => $sisa_piutang

            ];



            $lebihRows[] = array('detail' => count($arr_insert_detail) - 1, 'cash' => $cash_cents, 'admin' => $admin_cents, 'mark' => $gross_mark, 'dep' => $dep_cents,
                'pph' => $pph_actual, 'lebih' => $lebih_cents, 'balance' => $balance_cents,
                'sisa' => $calculation['remaining'], 'company' => $this->legacy_owner_id($id_company), 'legacy_company' => (string)$id_company, 'nm_company' => (string)$nm_company,
                'customer' => (string)($get_inv['id_customer'] ?? ''), 'nm_customer' => (string)($get_inv['nm_customer'] ?? ''), 'no_invoice' => (string)($get_inv['no_invoice'] ?? ''));
            $total_penerimaan_cents += $cash_cents;



            // Tentukan COA PPh berdasarkan tipe_invoice (VUCA = 1106-01-05, lainnya = 1106-01-02)

            $coa_pph = (!empty($get_inv['tipe_invoice']) && $get_inv['tipe_invoice'] == '1') ? '1106-01-05' : '1106-01-02';



            $arr_coa_jurnal = ['1102-01-01', '7201-01-04'];

            // if ($post['pph23_dipotong'] == 'Y') {

            $arr_coa_jurnal[] = $coa_pph;

            // }



            $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');

            $this->accounting->from('coa_master a');

            $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);

            $get_coa_jurnal = $this->accounting->get()->result_array();





            foreach ($get_coa_jurnal as $item_jurnal) {

                $no_jurnal++;

                // Jurnal mockup: Debit PPh 23 Disetor = PPh dipotong, Debit Admin, Kredit Piutang bruto, Debit Piutang = sisa; Bank = kas (di akhir).
                // Piutang Dagang Usaha dipecah: baris kredit (piutang bruto) dan baris debit (sisa piutang, bila ada).
                $value_debit = $item_jurnal['no_perkiraan'] === '7201-01-04' ? $biaya_admin
                    : ($item_jurnal['no_perkiraan'] === $coa_pph ? $pph : '0.00');
                $value_kredit = $item_jurnal['no_perkiraan'] === '1102-01-01' ? Penerimaan_calculation::decimal($balance_cents) : '0.00';
                $get_no_invoice = $this->db->select('a.no_invoice')

                    ->from('tr_invoicing a')

                    ->where('a.id', $post['id_inv_' . $i])

                    ->get()

                    ->row();



                $no_invoice = $get_no_invoice->no_invoice ?? '';



                $get_no_invoice = $this->db->select('a.no_invoice')

                    ->from('tr_invoicing a')

                    ->where('a.id', $post['id_inv_' . $i])

                    ->get()

                    ->row();



                $no_invoice = $get_no_invoice->no_invoice ?? '';



                if ($i == 1 && $no_jurnal == 1) {

                    $arr_insert_jurnal[] = [

                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),

                        'tgl_jurnal' => $detail['tanggal_transaksi'],

                        'coa' => $coa_bank,

                        'id_company' => $id_company,

                        'nm_company' => $nm_company,

                        'nm_coa' => $nm_coa_bank,

                        'debit' => '0.00', // Set to the sum of receipts after the loop.

                        'kredit' => '0.00',

                        'keterangan' => $nm_coa_bank . ' - ' . $no_invoice,

                        'sts' => '0',

                        'no_transaksi' => $id,

                        'jenis_transaksi' => 'Penerimaan Piutang',

                        'created_by' => $this->auth->user_id(),

                        'created_date' => date('Y-m-d H:i:s')

                    ];



                    $no_jurnal++;

                    $arr_insert_jurnal[] = [

                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),

                        'tgl_jurnal' => $detail['tanggal_transaksi'],

                        'coa' => $item_jurnal['no_perkiraan'],

                        'id_company' => $id_company,

                        'nm_company' => $nm_company,

                        'nm_coa' => $item_jurnal['nm_coa'],

                        'debit' => $value_debit,

                        'kredit' => $value_kredit,

                        'keterangan' => $item_jurnal['nm_coa'] . ' - ' . $no_invoice,

                        'sts' => '0',

                        'no_transaksi' => $id,

                        'jenis_transaksi' => 'Penerimaan Piutang',

                        'created_by' => $this->auth->user_id(),

                        'created_date' => date('Y-m-d H:i:s')

                    ];

                } else {

                    $arr_insert_jurnal[] = [

                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),

                        'tgl_jurnal' => $detail['tanggal_transaksi'],

                        'coa' => $item_jurnal['no_perkiraan'],

                        'id_company' => $id_company,

                        'nm_company' => $nm_company,

                        'nm_coa' => $item_jurnal['nm_coa'],

                        'debit' => $value_debit,

                        'kredit' => $value_kredit,

                        'keterangan' => $item_jurnal['nm_coa'] . ' - ' . $no_invoice,

                        'sts' => '0',

                        'no_transaksi' => $id,

                        'jenis_transaksi' => 'Penerimaan Piutang',

                        'created_by' => $this->auth->user_id(),

                        'created_date' => date('Y-m-d H:i:s')

                    ];

                }

            if ($item_jurnal['no_perkiraan'] === '1102-01-01' && $sisa_cents > 0) {
                    $no_jurnal++;
                    $sisaRow = end($arr_insert_jurnal);
                    $sisaRow['no_jurnal'] = $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal);
                    $sisaRow['debit'] = $sisa_piutang;
                    $sisaRow['kredit'] = '0.00';
                    $arr_insert_jurnal[] = $sisaRow;
                }
            }

        }



        // Lebih Bayar invoice lama: kelebihan uang bank menjadi voucher deposit customer (company pemilik jurnal 7/3/4).
        $total_lebih_cents = 0;
        $total_dep_cents = 0;
        foreach ($lebihRows as $lr) { $total_lebih_cents += $lr['lebih']; $total_dep_cents += $lr['dep']; }
        if ($useDep && $total_dep_cents !== $depCents) { $this->legacy_fail('Total Deposit Dipakai per invoice (' . Penerimaan_calculation::decimal($total_dep_cents) . ') harus sama dengan Nominal Deposit Dipakai di form (' . Penerimaan_calculation::decimal($depCents) . ').'); return; }
        if ($total_dep_cents > 0 && $total_lebih_cents > 0) { $this->legacy_fail('Deposit Dipakai dan Lebih Bayar tidak boleh terisi bersamaan.'); return; }
        if ($total_lebih_cents > 0 || $total_dep_cents > 0) {
            $lebihCompanyIds = array_unique(array_column($lebihRows, 'company'));
            $lebihCustomerIds = array_unique(array_column($lebihRows, 'customer'));
            $allocNominal = !empty($split) ? $split['nominal'] : ($detail['nominal_kredit'] ?? 0);
            if (count($lebihCompanyIds) !== 1 || !Invoice_consulting_service::is_journal_owner($lebihCompanyIds[0])) { $this->legacy_fail('Lebih Bayar/Deposit hanya untuk invoice dari satu company pemilik jurnal (STM, Sustain, atau Vuca).'); return; }
            if (count($lebihCustomerIds) !== 1 || trim($lebihCustomerIds[0]) === '') { $this->legacy_fail('Lebih Bayar/Deposit membutuhkan invoice dari satu customer.'); return; }
            if ($total_penerimaan_cents + $total_lebih_cents !== Penerimaan_calculation::cents($allocNominal)) { $this->legacy_fail('Penerimaan + Lebih Bayar harus sama dengan uang masuk alokasi bank.'); return; }
            if (!$this->db->field_exists('consulting_snapshot', 'tr_penerimaan_piutang_detail') || !$this->db->table_exists('tr_customer_deposit')) { $this->legacy_fail('Skema deposit belum tersedia; terapkan migration deposit.'); return; }
            // Snapshot ringkas (sen) agar Consulting_receipt::deposit_receipt dapat memverifikasi sumber voucher invoice lama.
            foreach ($lebihRows as $lr) {
                $arr_insert_detail[$lr['detail']]['id_customer'] = $lr['customer'];
                $arr_insert_detail[$lr['detail']]['consulting_snapshot'] = json_encode(array(
                    'legacy' => true, 'owner' => array('id' => $lr['company'], 'nm_company' => $lr['nm_company']),
                    'cash' => $lr['cash'], 'admin' => $lr['admin'], 'pph' => $lr['pph'], 'lebih' => $lr['lebih'], 'dep' => $lr['dep'],
                    'balance' => $lr['balance'], 'sisa' => $lr['sisa']
                ) + $lr['mark']);
            }
            $arr_insert_header['id_customer'] = $lebihCustomerIds[0];
            $no_jurnal++;
            $coaDepositRow = $this->accounting->get_where('coa_master', ['no_perkiraan' => '2102-01-02'])->row_array();
            $arr_insert_jurnal[] = [
                'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),
                'tgl_jurnal' => $detail['tanggal_transaksi'],
                'coa' => '2102-01-02',
                'id_company' => $lebihRows[0]['legacy_company'],
                'nm_company' => $lebihRows[0]['nm_company'],
                'nm_coa' => !empty($coaDepositRow['nama']) ? $coaDepositRow['nama'] : 'Deposit Customer',
                'debit' => Penerimaan_calculation::decimal($total_dep_cents),
                'kredit' => Penerimaan_calculation::decimal($total_lebih_cents),
                'keterangan' => (!empty($coaDepositRow['nama']) ? $coaDepositRow['nama'] : 'Deposit Customer') . ' - ' . implode(', ', array_column($lebihRows, 'no_invoice')),
                'sts' => '0',
                'no_transaksi' => $id,
                'jenis_transaksi' => 'Penerimaan Piutang',
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($arr_insert_jurnal)) {
            $arr_insert_jurnal[0]['debit'] = Penerimaan_calculation::decimal($total_penerimaan_cents + $total_lebih_cents);
        }
        $total_penerimaan = Penerimaan_calculation::decimal($total_penerimaan_cents + $total_lebih_cents);

        $arr_insert_header['nominal_penerimaan_bank'] = $total_penerimaan;



        $this->db->trans_begin();



        $insert_penerimaan_header = $this->db->insert('tr_penerimaan_piutang', $arr_insert_header);

        if (!$insert_penerimaan_header) {

            $this->db->trans_rollback();



            print_r($this->db->last_query());

            exit;

        }



        $insert_penerimaan_detail = $this->db->insert_batch('tr_penerimaan_piutang_detail', $arr_insert_detail);

        if (!$insert_penerimaan_detail) {

            $this->db->trans_rollback();



            print_r($this->db->last_query());

            exit;

        }



        $insert_jurnal = $this->db->insert_batch('tr_jurnal', $arr_insert_jurnal);

        if (!$insert_jurnal) {

            $this->db->trans_rollback();



            print_r($this->db->last_query());

            exit;

        }



        $update_inv = $this->db->update_batch('tr_invoicing', $arr_update_inv, 'id');

        if (!$update_inv) {

            $this->db->trans_rollback();



            print_r($this->db->last_query());

            exit;

        }



        $detail_id_for_update = $resolved['is_legacy'] ? $post['id_alokasi'] : $split['id_alokasi_detail'];

        $update_alokasi = $this->db->update('tr_alokasi_detail', ['nilai_terpakai' => $total_penerimaan], ['id' => $detail_id_for_update]);

        if (!$update_alokasi) {

            $this->db->trans_rollback();



            print_r($this->db->last_query());

            exit;

        }



        // Update nilai_terpakai di tr_alokasi_split agar status per-split akurat

        if (!$resolved['is_legacy'] && !empty($split)) {

            $update_split = $this->db->update('tr_alokasi_split', ['nilai_terpakai' => $total_penerimaan], ['id' => $post['id_alokasi']]);

            if (!$update_split) {

                $this->db->trans_rollback();



                print_r($this->db->last_query());

                exit;

            }

        }



        if ($total_dep_cents > 0) {
            try {
                (new Consulting_receipt($this))->consume_deposit($lebihRows[0]['customer'], $total_dep_cents, $id, $detail['tanggal_transaksi'], implode(', ', array_column($lebihRows, 'no_invoice')));
            } catch (Throwable $e) {
                $this->db->trans_rollback();
                $this->legacy_fail($e instanceof RuntimeException || $e instanceof InvalidArgumentException ? $e->getMessage() : 'Pemakaian deposit gagal; penerimaan dibatalkan.');
                return;
            }
        }
        if ($total_lebih_cents > 0) {
            try {
                (new Consulting_receipt($this))->create_lebih_bayar_voucher($id, $lebihRows[0]['customer'], $lebihRows[0]['nm_customer'], $lebihRows[0]['company'], '', $detail['tanggal_transaksi'], $total_lebih_cents, implode(', ', array_column($lebihRows, 'no_invoice')));
            } catch (Throwable $e) {
                $this->db->trans_rollback();
                $this->legacy_fail('Voucher deposit lebih bayar gagal dibuat; penerimaan dibatalkan.');
                return;
            }
        }
        if ($this->db->trans_status() === false) {

            $this->db->trans_rollback();



            $valid = 0;

            $msg = 'Please try again later !';

        } else {

            $this->db->trans_commit();



            $valid = 1;

            $msg = 'Save penerimaan berhasil !';

        }



        $response = [
            'status' => $valid,
            'msg' => $msg,
            'message' => $msg,
            'no_surat' => isset($id) ? (string)$id : ''
        ];



        echo json_encode($response);

    }



    public function detail()
    {
        if ($this->deny_without($this->viewPermission, false)) return;

        $post = $this->input->post();
        $id = $post['id_alokasi'] ?? '';
        $data = $this->build_detail_data($id);
        if ($data === null) {
            echo '<div class="alert alert-danger" role="alert">Penerimaan tidak ditemukan.</div>';
            return;
        }
        $this->template->set($data);
        $this->template->render('detail');
    }

    /** Halaman detail penerimaan (mockup: halaman penuh, bukan modal). */
    public function view($id = '')
    {
        $this->auth->restrict($this->viewPermission);
        $data = $this->build_detail_data(is_scalar($id) ? $id : '');
        if ($data === null) {
            $this->session->set_flashdata('error', 'Penerimaan tidak ditemukan.');
            redirect('penerimaan_uang');
            return;
        }
        $customerId = (string)($data['header']['id_customer'] ?? ($data['detail'][0]['id_customer'] ?? ''));
        $this->load->model('deposit_customer/Deposit_customer_model');
        $data['kartu_ledger'] = $customerId !== '' ? $this->Deposit_customer_model->get_customer_mutasi_ledger_all($customerId) : array();
        $this->template->set($data);
        $this->template->title('Penerimaan Piutang');
        $this->template->render('detail_page');
    }

    /** Tautan dari modul lain (nomor surat mengandung '/'): cari id penerimaan lalu buka halaman detail. */
    public function view_surat()
    {
        $this->auth->restrict($this->viewPermission);
        $no = $this->input->get('no');
        $row = is_scalar($no) && trim((string)$no) !== '' ? $this->db->select('id')->get_where('tr_penerimaan_piutang', ['no_surat' => $no])->row_array() : null;
        redirect($row ? 'penerimaan_uang/view/' . (int)$row['id'] : 'penerimaan_uang#tab_report');
    }

    private function build_detail_data($id)
    {
        $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang', ['id' => $id])->row_array();
        if (empty($get_penerimaan)) {
            return null;
        }



        $this->db->select('a.*, b.no_invoice, b.tax_treatment, b.total_nominal_jurnal, b.dpp_lain_lain_jurnal, b.ppn_jurnal, b.tagihan_ppn_jurnal, b.pph_jurnal, b.total_akhir_jurnal, b.saldo_piutang, b.id_spk_penawaran, c.no_surat');

        $this->db->from('tr_penerimaan_piutang_detail a');

        $this->db->join('tr_invoicing b', 'b.id = a.id_inv', 'left');

        $this->db->join('tr_penerimaan_piutang c', 'c.no_surat = a.id_header');

        $this->db->where('a.id_header', $get_penerimaan['no_surat']);

        $get_penerimaan_detail = array_map(array('Penerimaan_calculation', 'receipt_detail'), $this->db->get()->result_array());



        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($get_penerimaan['id_alokasi']);

        $get_alokasi = (!empty($resolved)) ? $resolved['detail'] : [];

        if (!empty($get_alokasi) && !empty($get_alokasi['bank'])) {
            $bank_acc = $this->db->select('b.nama as nama_bank_acc, b.rekening, c.nama_bank as nm_bank')
                ->from('tr_bank_account b')
                ->join('tr_bank c', 'c.id = b.bank', 'left')
                ->where('b.id', $get_alokasi['bank'])
                ->get()->row_array();
            if ($bank_acc) {
                $get_alokasi = array_merge($get_alokasi, $bank_acc);
            }
        }



        // Receipt journals are keyed by no_surat, including posted journals.

        $get_jurnal = $this->db->get_where('tr_jurnal', [

            'no_transaksi' => $get_penerimaan['no_surat'],

            'jenis_transaksi' => 'Penerimaan Piutang'

        ])->result_array();



        $data = [

            'header' => $get_penerimaan,

            'detail' => $get_penerimaan_detail,

            'alokasi' => $get_alokasi,

            'list_jurnal' => $get_jurnal

        ];
        return $data;
    }

    /** JSON daftar draft untuk tab Invoice / Kwitansi Deposit (render di browser seperti mockup). */
    public function draft_rows()
    {
        if ($this->deny_without($this->viewPermission)) return;
        $kind = $this->input->post('receipt_kind') === 'deposit' ? 'deposit' : 'invoice';
        $search = $this->input->post('search');
        $order = $this->input->post('order');
        $start = max(0, (int)$this->input->post('start'));
        $length = (int)$this->input->post('length');
        if ($length <= 0 || $length > 500) $length = 25;
        $result = $this->Penerimaan_uang_model->get_draft_serverside(
            $kind, (string)$this->input->post('bank'), is_array($search) ? trim((string)($search['value'] ?? '')) : '',
            $start, $length, (int)($order[0]['column'] ?? 1), (string)($order[0]['dir'] ?? 'asc')
        );
        foreach ($result['rows'] as $i => $row) $result['rows'][$i]['no'] = $start + $i + 1;
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(array(
            'draw' => (int)$this->input->post('draw'),
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['rows'],
            'counts' => $this->Penerimaan_uang_model->get_tab_counts()
        ));
    }

    public function get_alokasi_penerimaan()

    {
        if ($this->deny_without($this->viewPermission)) return;

        $this->Penerimaan_uang_model->get_alokasi_penerimaan();

    }



    public function rollback_penerimaan()

    {

        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $service=new Invoice_consulting_service($this);

        return $service->serialized(function(){

            $id=$this->input->post('id_penerimaan');

            if(!is_scalar($id)){echo json_encode(array('status'=>0,'msg'=>'ID penerimaan tidak valid.'));return;}

            $header=$this->db->get_where('tr_penerimaan_piutang',array('id'=>$id))->row_array();

            $consulting=new Consulting_receipt($this);

            if((int)($header['consulting_batch']??0)!==3 && $header){

                try {

                    $consulting->guard_legacy_allocation($header['id_alokasi']);

                    if($this->db->field_exists('consulting_reduction','tr_penerimaan_piutang_detail')){

                        $details=$this->db->get_where('tr_penerimaan_piutang_detail',array('id_header'=>$header['no_surat']))->result_array();

                        foreach($details as $detail){

                            $later=$this->db->query('SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv=? AND id>? AND consulting_reduction IS NOT NULL LIMIT 1',array($detail['id_inv'],$detail['id']))->row_array();

                            if($later)throw new RuntimeException('Rollback penerimaan terbaru terlebih dahulu.');

                        }

                    }

                }catch(RuntimeException $error){echo json_encode(array('status'=>0,'msg'=>$error->getMessage()));return;}

            }

            return $this->rollback_legacy_penerimaan();

        });

    }



    private function rollback_legacy_penerimaan()

    {

        $this->auth->restrict($this->deletePermission);



        $id_penerimaan = $this->input->post('id_penerimaan');



        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $consultingHeader=is_scalar($id_penerimaan)?$this->db->get_where('tr_penerimaan_piutang',array('id'=>$id_penerimaan))->row_array():null;

        if ((int)($consultingHeader['consulting_batch']??0)===3) {

            $consulting=new Consulting_receipt($this);echo json_encode($consulting->rollback($id_penerimaan));return;

        }



        if (empty($id_penerimaan)) {

            echo json_encode(['status' => 0, 'msg' => 'ID Penerimaan tidak valid.']);

            return;

        }



        // Get header

        $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang', ['id' => $id_penerimaan])->row_array();

        if (empty($get_penerimaan)) {

            echo json_encode(['status' => 0, 'msg' => 'Data penerimaan tidak ditemukan.']);

            return;

        }



        $no_surat = $get_penerimaan['no_surat'];



        // Check if journal has been posted

        $check_posted = $this->db->get_where('tr_jurnal', ['no_transaksi' => $no_surat, 'sts' => '1'])->num_rows();

        if ($check_posted > 0) {

            echo json_encode(['status' => 0, 'msg' => 'Penerimaan tidak bisa di-rollback karena jurnal sudah di-posting.']);

            return;

        }
        // Lebih Bayar invoice lama: voucher deposit hanya boleh dihapus bila belum terpakai sama sekali.
        $lebihVoucher = $this->db->table_exists('tr_customer_deposit')
            ? $this->db->get_where('tr_customer_deposit', ['no_referensi' => $no_surat, 'tipe_sumber' => 'LEBIH_BAYAR'])->row_array() : null;
        if (!empty($lebihVoucher)) {
            $voucherMutations = $this->db->get_where('tr_customer_deposit_mutasi', ['id_deposit' => $lebihVoucher['id']])->result_array();
            $voucherUsed = $lebihVoucher['status'] !== 'AKTIF' || Penerimaan_calculation::cents($lebihVoucher['nominal_terpakai']) !== 0
                || count($voucherMutations) !== 1 || $voucherMutations[0]['jenis_mutasi'] !== 'IN';
            if ($voucherUsed) {
                echo json_encode(['status' => 0, 'msg' => 'Deposit hasil lebih bayar sudah dipakai; rollback penerimaan yang memakainya terlebih dahulu.']);
                return;
            }
        }




        $this->db->trans_begin();



        // Deposit yang dipakai penerimaan ini (mutasi OUT) dikembalikan ke voucher sumbernya.
        $depUsed = $this->db->table_exists('tr_customer_deposit_mutasi')
            ? array_filter($this->db->get_where('tr_customer_deposit_mutasi', ['no_transaksi' => $no_surat, 'jenis_mutasi' => 'OUT'])->result_array(), function ($m) { return ($m['jenis_mutasi'] ?? '') === 'OUT'; }) : array();
        try { if (!empty($depUsed)) (new Consulting_receipt($this))->restore_consumed_deposit($no_surat); }
        catch (Throwable $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Pengembalian deposit gagal; rollback dibatalkan.']);
            return;
        }

        // Revert Saldo Piutang in tr_invoicing

        $get_details = $this->db->get_where('tr_penerimaan_piutang_detail', ['id_header' => $no_surat])->result_array();



        // Cek tanggal rilis perbaikan untuk backward compatibility

        $tgl_rilis_perbaikan = '2026-06-09 00:00:00';

        $is_old_data = (strtotime($get_penerimaan['created_date']) < strtotime($tgl_rilis_perbaikan));



        $total_penerimaan = 0;

        foreach ($get_details as $detail) {

            $id_inv = $detail['id_inv'];

            $penerimaan = $detail['penerimaan'];

            $biaya_admin = $detail['biaya_admin'];

            $pph23 = $detail['pph23'];

            $total_penerimaan += $penerimaan;



            if ($is_old_data) {

                // Logika lama: hanya menambahkan penerimaan

                $amount_to_add = $penerimaan;

            } else {

                // Logika baru: akurat berdasarkan perhitungan UI

                if ($get_penerimaan['pph23_dipotong'] == 'N') {

                    $amount_to_add = $penerimaan - $pph23 + $biaya_admin;


                } else {

                    $amount_to_add = $penerimaan + $biaya_admin;

                }

            }



            // Update saldo_piutang
            $snapRb = json_decode($detail['consulting_snapshot'] ?? '', true);
            if (is_array($snapRb) && isset($snapRb['prev_saldo'])) {
                // Penerimaan basis bruto: kembalikan saldo ke nilai sebelum penerimaan (sen -> rupiah).
                $this->db->set('saldo_piutang', Penerimaan_calculation::decimal((int)$snapRb['prev_saldo']));
            } else {
                $this->db->set('saldo_piutang', 'saldo_piutang + ' . $amount_to_add, FALSE);
            }

            $this->db->where('id', $id_inv);

            $this->db->update('tr_invoicing');

        }



        // Revert nilai_terpakai in tr_alokasi_detail, hapus split, dan kembalikan sts ke 0

        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($get_penerimaan['id_alokasi']);

        if (!empty($resolved)) {

            $split = $resolved['split'];

            $alokasi_detail = $resolved['detail'];



            $detail_id_for_update = $resolved['is_legacy'] ? $get_penerimaan['id_alokasi'] : $split['id_alokasi_detail'];



            // Set nilai_terpakai = NULL dan sts = '0' agar kembali Open di modul Alokasi

            $this->db->update('tr_alokasi_detail', [

                'nilai_terpakai' => NULL,

                'sts' => '0'

            ], ['id' => $detail_id_for_update]);



            if (!$resolved['is_legacy']) {

                // Hapus seluruh data split pada tr_alokasi_split

                $this->db->delete('tr_alokasi_split', ['id_alokasi_detail' => $detail_id_for_update]);



                // Insert log ke log_alokasi_history

                $log_data = [

                    'id_alokasi_detail' => $detail_id_for_update,

                    'action' => 'ROLLBACK_PENERIMAAN',

                    'deskripsi_log' => 'Rollback penerimaan piutang (' . $no_surat . ') dan hapus split alokasi',

                    'created_by' => $this->session->userdata('id_user') ?: 'System',

                    'created_date' => date('Y-m-d H:i:s')

                ];

                $this->db->insert('log_alokasi_history', $log_data);

            }

        }



        if (!empty($lebihVoucher)) {
            $this->db->delete('tr_customer_deposit_mutasi', ['id_deposit' => $lebihVoucher['id']]);
            $this->db->delete('tr_customer_deposit', ['id' => $lebihVoucher['id']]);
        }
        // Delete Jurnal

        $this->db->delete('tr_jurnal', ['no_transaksi' => $no_surat, 'jenis_transaksi' => 'Penerimaan Piutang']);



        // Delete Details

        $this->db->delete('tr_penerimaan_piutang_detail', ['id_header' => $no_surat]);



        // Delete Header

        $this->db->delete('tr_penerimaan_piutang', ['no_surat' => $no_surat]);



        if ($this->db->trans_status() === FALSE) {

            $this->db->trans_rollback();

            $valid = 0;

            $msg = 'Gagal melakukan rollback, coba lagi.';

        } else {

            $this->db->trans_commit();

            $valid = 1;

            $msg = 'Rollback penerimaan berhasil dilakukan.';

        }



        echo json_encode([

            'status' => $valid,

            'msg' => $msg

        ]);

    }

}

