<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Jurnal_invoicing_model extends BF_Model
{
    protected $viewPermission     = 'Jurnal_Invoicing.View';
    protected $addPermission      = 'Jurnal_Invoicing.Add';
    protected $managePermission = 'Jurnal_Invoicing.Manage';
    protected $deletePermission = 'Jurnal_Invoicing.Delete';

    protected $accounting;
    protected $accounting_vuca;
    protected $accounting_sustain;
    protected $accounting_stm;
    protected $consultant;

    public function __construct()
    {
        $this->accounting = $this->load->database('accounting', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
        $this->accounting_stm = $this->load->database('accounting_stm', true);
        $this->consultant = $this->load->database('consultant', true);
    }

    public function save_posting_jurnal()
    {
        $post = $this->input->post();

        $get_jurnal        = $this->db->get_where('tr_jurnal', ['id' => $post['id']])->row();

        $get_jurnal_detail = $this->db->get_where('tr_jurnal', [
            'no_transaksi'    => $get_jurnal->no_transaksi,
            'jenis_transaksi' => $get_jurnal->jenis_transaksi,
        ])->result();
        $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_jurnal->no_transaksi])->row();

        $get_coa_piutang = $this->db->select('a.coa, a.nm_coa')
            ->from('tr_jurnal a')
            ->where('a.no_transaksi', $get_jurnal->no_transaksi)
            ->where('a.jenis_transaksi', $get_jurnal->jenis_transaksi)
            ->like('a.nm_coa', 'Piutang', 'both')
            ->get()
            ->row();

        $no_coa_piutang = $get_coa_piutang->coa ?? '';
        $nm_coa_piutang = $get_coa_piutang->nm_coa ?? '';

        // Cek penawaran konsultasi dulu
        $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_invoicing->id_penawaran])->row();
        $id_company = (!empty($get_penawaran->company)) ? $get_penawaran->company : '';



        // Jika tidak ditemukan di penawaran konsultasi, cek penawaran non-konsultasi
        if (empty($id_company)) {
            $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_invoicing->id_spk_penawaran])->row();

            $id_company = $get_spk_penawaran->id_company ?? '';
        }

        if (empty($id_company)) {
            $get_penawaran_non_kons = $this->consultant->get_where('kons_tr_penawaran_non_konsultasi', ['id_penawaran' => $get_invoicing->id_penawaran])->row();
            $id_company = (!empty($get_penawaran_non_kons->id_company)) ? $get_penawaran_non_kons->id_company : '';
        }

        $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $id_company])->row();

        $nm_company = $get_company->nm_company ?? '';

        $Nomor_JV   = $this->Jurnal_invoicing_nomor_model->get_Nomor_Jurnal_Sales('101', $get_jurnal->tgl_jurnal, $id_company);
        $Bln        = substr($get_jurnal->tgl_jurnal, 5, 2);
        $Thn        = substr($get_jurnal->tgl_jurnal, 0, 4);

        // Pilih koneksi DB accounting berdasarkan id_company
        $acc_db = $this->_get_accounting_db($id_company);

        $this->db->trans_begin();

        try {
            // Insert jurnal header — sekali saja
            $insert_javh = $acc_db->insert('javh', [
                'nomor'          => $Nomor_JV,
                'tgl'            => $get_jurnal->tgl_jurnal,
                'jml'            => $get_invoicing->total_akhir_jurnal,
                'koreksi_no'     => '-',
                'kdcab'          => '101',
                'jenis'          => 'JV',
                'keterangan'     => $get_invoicing->no_invoice . ' - ' . $get_invoicing->nm_customer,
                'bulan'          => $Bln,
                'tahun'          => $Thn,
                'user_id'        => $this->auth->user_id(),
                'memo'           => '',
                'tgl_jvkoreksi'  => $get_jurnal->tgl_jurnal,
                'ho_valid'       => '',
            ]);
            if (!$insert_javh) {
                throw new Exception('Gagal insert jurnal header (javh): ' . $acc_db->error()['message']);
            }

            // Insert jurnal detail — satu baris per item
            foreach ($get_jurnal_detail as $item) {
                $get_invoice = $this->db->get_where('tr_invoicing', array('id' => $item->no_transaksi))->row();
                $no_invoice = $get_invoice->no_invoice ?? $item->no_transaksi;

                $ket = explode(' - ', $item->keterangan);

                $keterangan = $ket[0].' - '.$no_invoice ?? $item->keterangan;

                $insert_jurnal = $acc_db->insert('jurnal', [
                    'tipe'          => 'JV',
                    'nomor'         => $Nomor_JV,
                    'tanggal'       => $item->tgl_jurnal,
                    'no_perkiraan'  => $item->coa,
                    'keterangan'    => $keterangan,
                    'no_reff'       => $no_invoice,
                    'debet'         => $item->debit,
                    'kredit'        => $item->kredit,
                ]);
                if (!$insert_jurnal) {
                    throw new Exception('Gagal insert jurnal detail: ' . $acc_db->error()['message']);
                }

                // Update status posting jurnal detail
                $update_stspos = $acc_db->update('jurnal', ['stspos' => 1], [
                    'tipe'   => 'JV',
                    'nomor'  => $Nomor_JV,
                    'no_reff' => $item->no_transaksi,
                ]);
                if (!$update_stspos) {
                    throw new Exception('Gagal update stspos jurnal: ' . $acc_db->error()['message']);
                }

                // Tandai jurnal awal sudah diposting
                $update_sts = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item->id]);
                if (!$update_sts) {
                    throw new Exception('Gagal update status tr_jurnal: ' . $this->db->error()['message']);
                }
            }

            // Update nomor cabang — sekali saja setelah semua detail selesai
            $update_cabang = $acc_db->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");
            if (!$update_cabang) {
                throw new Exception('Gagal update nomor cabang: ' . $acc_db->error()['message']);
            }

            // Insert kartu piutang — sekali saja per invoice
            $insert_kartu_piutang = $this->db->insert('tr_kartu_piutang', [
                'tipe'           => 'JV',
                'nomor'          => $Nomor_JV,
                'tanggal'        => $get_jurnal->tgl_jurnal,
                'no_perkiraan'   => $no_coa_piutang,
                'keterangan'     => $get_invoicing->no_invoice . ' - ' . $get_invoicing->nm_customer,
                'no_reff'        => $get_invoicing->id,
                'debet'          => $get_invoicing->total_akhir_jurnal,
                'kredit'         => 0,
                'id_supplier'    => $get_invoicing->id_customer,
                'nama_supplier'  => $get_invoicing->nm_customer,
                'id_company'     => $id_company,
                'nm_company'     => $nm_company
            ]);
            if (!$insert_kartu_piutang) {
                throw new Exception('Gagal insert kartu piutang: ' . $this->db->error()['message']);
            }

            $this->db->trans_commit();

            echo json_encode(['save' => 1, 'msg' => 'SUKSES, simpan data..!!!']);
        } catch (Exception $e) {
            $this->db->trans_rollback();

            echo json_encode(['save' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Kembalikan koneksi DB accounting yang sesuai berdasarkan id_company.
     */
    private function _get_accounting_db($id_company)
    {
        if ($id_company == '4') {
            return $this->accounting_vuca;
        } elseif ($id_company == '7' || $id_company == '1' || $id_company == '6') {
            return $this->accounting_stm;
        } else {
            return $this->accounting_sustain;
        }
    }

    public function update_sts_revisi_jurnal()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';

        $get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $post['id']])->row();

        $arr_update = [
            'sts' => '9',
            'alasan_revisi' => $post['alasan_revisi'],
        ];

        $where_update = [
            'no_transaksi' => $get_jurnal->no_transaksi,
            'jenis_transaksi' => $get_jurnal->jenis_transaksi
        ];

        $update_sts = $this->db->update('tr_jurnal', $arr_update, $where_update);
        if (!$update_sts) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error()['message'];
        }

        if ($this->db->trans_status() === false || $valid == 0) {
            if ($valid !== 0) {
                $this->db->trans_rollback();

                $valid = 0;
                $msg = 'Please try again later !';
            }
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'Status data berhasil di update !';
        }

        $response = [
            'status' => $valid,
            'msg' => $msg
        ];

        echo json_encode($response);
    }

    public function get_data_jurnal_invoicing()
    {
        $post   = $this->input->post();
        $draw   = isset($post['draw']) ? $post['draw'] : 0;
        $length = isset($post['length']) ? $post['length'] : 10;
        $start  = isset($post['start']) ? $post['start'] : 0;
        $search = isset($post['search']) ? $post['search']['value'] : '';

        $klien = isset($post['klien']) ? $post['klien'] : '';
        $no_invoice = isset($post['no_invoice']) ? $post['no_invoice'] : '';
        $company = isset($post['company']) ? $post['company'] : '';

        $filter = [
            'b.id_customer' => $klien,
            'b.no_invoice' => $no_invoice,
            'd.id' => $company
        ];

        // 1. Hitung Total Record (Tanpa Filter Search)
        $this->base_query_jurnal($filter);
        $this->db->group_by('a.no_transaksi');
        $sub_query_all = $this->db->get_compiled_select();
        $count_all_res = $this->db->query("SELECT COUNT(*) as total FROM ($sub_query_all) as temp")->row();
        $count_all     = $count_all_res ? (int)$count_all_res->total : 0;

        // 2. Hitung Filtered Record (Dengan Filter Search)
        $this->base_query_jurnal($filter);
        if (!empty($search)) {
            $this->apply_search_jurnal($search);
        }
        $this->db->group_by('a.no_transaksi');
        $sub_query_filtered = $this->db->get_compiled_select();
        $count_filter_res   = $this->db->query("SELECT COUNT(*) as total FROM ($sub_query_filtered) as temp")->row();
        $count_filtered     = $count_filter_res ? (int)$count_filter_res->total : 0;

        // 3. Ambil Data Aktual
        $this->base_query_jurnal($filter);
        if (!empty($search)) {
            $this->apply_search_jurnal($search);
        }
        $this->db->group_by('a.no_transaksi');
        $this->db->order_by('a.tgl_jurnal', 'DESC');
        $this->db->limit($length, $start);
        $rows = $this->db->get()->result();

        // 4. Build Response untuk Datatables
        $hasil = [];
        $no    = (int)$start;

        foreach ($rows as $row) {
            $no++;
            // Logika ambil nilai debit atau kredit (mana yang isi)
            $nilai = ($row->debit > 0) ? $row->debit : $row->kredit;

            $btn_post_jurnal = '<button type="button" class="btn btn-sm btn-primary posting_jurnal" title="Posting Jurnal"'
                . ' data-id="' . $row->id . '"'
                . ' data-no_transaksi="' . $row->no_transaksi . '"'
                . ' data-jenis_transaksi="' . $row->jenis_transaksi . '">'
                . '<i class="fa fa-arrow-up"></i></button>';

            $keterangan = $row->keterangan_penawaran;
            if (empty($keterangan)) {
                $get_keterangan_spk = $this->db->select('a.nm_project, b.nm_paket as keterangan')
                    ->from(DBCNL . '.kons_tr_spk_penawaran a')
                    ->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left')
                    ->where('a.id_spk_penawaran', $row->id_spk_penawaran)
                    ->get()
                    ->row();

                $keterangan = $row->nm_project ?? $get_keterangan_spk->nm_project;
            }

            $hasil[] = [
                'no'                 => $no,
                'tgl'                => date('d F Y', strtotime($row->tgl_jurnal)),
                'klien'              => $row->nm_customer,
                'no_invoice'         => $row->no_invoice,
                'keterangan_tagihan' => (!empty($row->non_kons) && $row->non_kons == '1')
                    ? $row->keterangan_penawaran . ' - <span style="font-weight:bold;">' . $row->id_penawaran_non_kons . '</span>'
                    : $keterangan . ' - <span style="font-weight:bold;">' . $row->id_spk_penawaran . '</span>',
                'company'            => $row->nm_company,
                'nm_divisi'          => $row->nm_divisi,
                'coa'                => $row->coa,
                'perkiraan'          => $row->nm_coa,
                'uraian'             => $row->no_invoice,
                'original'           => number_format($nilai),
                'action'             => $btn_post_jurnal,
            ];
        }

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => $count_all,
            'recordsFiltered' => $count_filtered,
            'data'            => $hasil,
        ]);
    }

    /**
     * Base Query untuk menghindari penulisan ulang JOIN dan WHERE dasar
     */
    private function base_query_jurnal($filter)
    {
        $select_fields = 'a.no_transaksi, a.id, a.tgl_jurnal, a.coa, a.nm_coa, a.debit, a.kredit, a.jenis_transaksi, b.nm_customer, b.nm_project, b.no_invoice, b.id_spk_penawaran, b.non_kons, e.id_penawaran as id_penawaran_non_kons, e.keterangan_penawaran, COALESCE(COALESCE(d.id, j.id), f.id) as id_company, COALESCE(COALESCE(d.nm_company, j.nm_company), f.nm_company) as nm_company, COALESCE(c.id_divisi, e.id_divisi) as id_divisi, COALESCE(g.name, h.name) as nm_divisi';

        $this->db->select($select_fields, FALSE)
            ->from('tr_jurnal a')
            ->join('tr_invoicing b', 'b.id = a.no_transaksi', 'left')
            ->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_spk_penawaran i', 'i.id_spk_penawaran = b.id_spk_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left')
            ->join(DBCNL . '.kons_tr_penawaran_non_konsultasi e', 'e.id_penawaran = b.id_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_company f', 'f.id = e.id_company', 'left')
            ->join(DBCNL . '.kons_tr_company j', 'j.id = i.id_company', 'left')
            ->join('hris_divisions g', 'g.id = c.id_divisi', 'left')
            ->join(DBHRIS . '.departments h', 'h.id = e.id_divisi', 'left')
            ->where('a.jenis_transaksi', 'Invoicing')
            ->where('b.no_invoice <>', '')
            ->where_in('a.sts', ['', '0'])
            ->where('(a.debit > 0 OR a.kredit > 0)') // Pindahan dari HAVING
            ->group_start()
            ->where('d.nm_company IS NOT NULL')
            ->or_where('f.nm_company IS NOT NULL')
            ->or_where('j.nm_company IS NOT NULL')
            ->group_end();

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if (!empty($value)) {
                    $this->db->where($key, $value);
                }
            }
        }
    }

    /**
     * Helper untuk Apply Search
     */
    private function apply_search_jurnal($search_value)
    {
        $search_terms = [
            'a.tgl_jurnal',
            'b.nm_customer',
            'b.nm_project',
            'b.no_invoice',
            'a.nm_company',
            'g.name',
            'h.name',
            'a.coa',
            'a.nm_coa',
            'b.id_spk_penawaran',
            'a.debit',
            'a.kredit'
        ];

        $this->db->group_start();
        foreach ($search_terms as $key => $field) {
            if ($key === 0) {
                $this->db->like($field, $search_value, 'both');
            } else {
                $this->db->or_like($field, $search_value, 'both');
            }
        }
        $this->db->group_end();
    }

    public function get_cust_jurnal()
    {
        $get_cust_jurnal = $this->consultant->select('a.id_customer, a.nm_customer')
            ->from('customer a')
            ->where('a.sts_aktif', 'Y')
            ->where('a.deleted', 'N')
            ->get()
            ->result_array();

        return $get_cust_jurnal;
    }

    public function get_no_invoice_jurnal()
    {
        $get_no_invoice_jurnal = $this->db->select('a.no_invoice, a.created_date')
            ->from('tr_invoicing a')
            ->join('tr_jurnal b', 'b.no_transaksi = a.id AND b.jenis_transaksi = "Invoicing"')
            ->where('b.sts <>', '1')
            ->where('b.jenis_transaksi', 'Invoicing')
            ->group_by('a.no_invoice')
            ->order_by('a.created_date', 'desc')
            ->get()
            ->result_array();

        return $get_no_invoice_jurnal;
    }

    public function get_company_jurnal()
    {
        $get_company_jurnal = $this->consultant->select('a.id as id_company, a.nm_company')
            ->from('kons_tr_company a')
            ->get()
            ->result_array();

        return $get_company_jurnal;
    }
}
