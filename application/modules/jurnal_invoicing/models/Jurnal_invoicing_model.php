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

        $id_company = $get_jurnal->id_company;
        $Nomor_JV   = $this->Jurnal_invoicing_nomor_model->get_Nomor_Jurnal_Sales('101', $get_jurnal->tgl_jurnal, $id_company);
        $Bln        = substr($get_jurnal->tgl_jurnal, 5, 2);
        $Thn        = substr($get_jurnal->tgl_jurnal, 0, 4);

        // Pilih koneksi DB accounting berdasarkan id_company
        $acc_db = $this->_get_accounting_db($id_company);

        $this->db->trans_begin();

        try {
            // Insert jurnal header — sekali saja
            $acc_db->insert('javh', [
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

            // Insert jurnal detail — satu baris per item
            foreach ($get_jurnal_detail as $item) {
                $acc_db->insert('jurnal', [
                    'tipe'          => 'JV',
                    'nomor'         => $Nomor_JV,
                    'tanggal'       => $item->tgl_jurnal,
                    'no_perkiraan'  => $item->coa,
                    'keterangan'    => $item->keterangan,
                    'no_reff'       => $item->no_transaksi,
                    'debet'         => $item->debit,
                    'kredit'        => $item->kredit,
                ]);

                // Update status posting jurnal detail
                $acc_db->update('jurnal', ['stspos' => 1], [
                    'tipe'   => 'JV',
                    'nomor'  => $Nomor_JV,
                    'no_reff' => $item->no_transaksi,
                ]);

                // Tandai jurnal awal sudah diposting
                $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item->id]);
            }

            // Update nomor cabang — sekali saja setelah semua detail selesai
            $acc_db->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");

            // Insert kartu piutang — sekali saja per invoice
            $acc_db->insert('tr_kartu_piutang', [
                'tipe'           => 'JV',
                'nomor'          => $Nomor_JV,
                'tanggal'        => $get_jurnal->tgl_jurnal,
                'no_perkiraan'   => '1104-01-01',
                'keterangan'     => $get_invoicing->no_invoice . ' - ' . $get_invoicing->nm_customer,
                'no_reff'        => $get_invoicing->id,
                'debet'          => $get_invoicing->total_akhir_jurnal,
                'kredit'         => 0,
                'id_supplier'    => $get_invoicing->id_customer,
                'nama_supplier'  => $get_invoicing->nm_customer,
            ]);

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
            'a.id_company' => $company
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

            $hasil[] = [
                'no'                 => $no,
                'tgl'                => date('d F Y', strtotime($row->tgl_jurnal)),
                'klien'              => $row->nm_customer,
                'no_invoice'         => $row->no_invoice,
                'keterangan_tagihan' => $row->nm_project . ' - <span style="font-weight:bold;">' . $row->id_spk_penawaran . '</span>',
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
        $select_fields = 'a.no_transaksi, a.id, a.tgl_jurnal, a.coa, a.nm_coa, a.debit, a.kredit, a.jenis_transaksi, 
                      b.nm_customer, b.nm_project, b.no_invoice, b.id_spk_penawaran, d.id as id_company, 
                      d.nm_company, e.name as nm_divisi';

        $this->db->select($select_fields)
            ->from('tr_jurnal a')
            ->join('tr_invoicing b', 'b.id = a.no_transaksi', 'left')
            ->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left')
            ->join('hris_divisions e', 'e.id = c.id_divisi', 'left')
            ->where('a.jenis_transaksi', 'Invoicing')
            ->where('b.no_invoice <>', '')
            ->where('d.nm_company <>', '')
            ->where_in('a.sts', ['', '0'])
            ->where('(a.debit > 0 OR a.kredit > 0)'); // Pindahan dari HAVING

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if ($value !== '') {
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
            'e.name',
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
