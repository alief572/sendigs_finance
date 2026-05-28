<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */
// aaaa

class Actual_plan_tagih_model extends BF_Model
{
    protected $viewPermission     = 'Actual_Plan_Tagih.View';
    protected $addPermission      = 'Actual_Plan_Tagih.Add';
    protected $managePermission = 'Actual_Plan_Tagih.Manage';
    protected $deletePermission = 'Actual_Plan_Tagih.Delete';

    protected $consultant;

    public function __construct()
    {
        $this->consultant = $this->load->database('consultant', true);
    }

    public function generate_id($no = null)
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM kons_tr_actual_plan_tagih WHERE id LIKE '%/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        if (!empty($no)) {
            $urutan2        = (int)substr($angkaUrut2, 0, 5);
            $urutan2 = ($urutan2 + $no);
        } else {
            $urutan2        = (int)substr($angkaUrut2, 0, 5);
            $urutan2++;
        }
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '/ACT-TGH/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    /**
     * Re-check a record's status before processing (race condition guard).
     * Returns null if record not found or if status_terakhir is '1' (Tagih) or '3' (Macet).
     *
     * @param string $id
     * @return object|null
     */
    public function get_record_for_processing($id)
    {
        $this->db->select('*');
        $this->db->from('kons_tr_plan_tagih_detail');
        $this->db->where('id', $id);
        $query = $this->db->get();

        if ($query->num_rows() === 0) {
            return null;
        }

        $record = $query->row();

        // Race condition guard: skip if status has changed to Tagih or Macet
        if ($record->status_terakhir === '1' || $record->status_terakhir === '3') {
            return null;
        }

        return $record;
    }

    /**
     * Retrieve all Plan_Tagih_Detail records qualifying for batch processing.
     * Filters: status_terakhir NOT IN ('1', '3'), effective year in [$start_year, $end_year].
     *
     * @param int $start_year
     * @param int $end_year
     * @return array
     */
    public function get_batch_records($start_year, $end_year)
    {
        $this->db->select('a.id, a.id_header, a.id_spk_penawaran, a.id_penawaran, a.id_top, a.term_payment, a.persen_payment, a.nominal_payment, a.desc_payment, a.tgl_plan_tagih, a.tgl_aktual_plan_tagih, a.urutan, a.status_terakhir');
        $this->db->select('c.id_customer, c.nm_customer, c.address');
        $this->db->select('c.id_project, c.id_project_leader, c.nm_project_leader, c.id_sales, c.nm_sales');
        $this->db->select('e.nm_paket as nm_project');
        $this->db->select('COALESCE(d.nm_company, c.nm_company) as nm_company', false);
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran d', 'd.id_quotation = c.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header e', 'e.id_konsultasi_h = c.id_project', 'left');
        $this->db->where_not_in('a.status_terakhir', ['1', '3']);
        $this->db->where("YEAR(COALESCE(a.tgl_aktual_plan_tagih, a.tgl_plan_tagih)) >=", $start_year, false);
        $this->db->where("YEAR(COALESCE(a.tgl_aktual_plan_tagih, a.tgl_plan_tagih)) <=", $end_year, false);
        $this->db->group_by('a.id');

        return $this->db->get()->result();
    }

    public function get_actual_plan_tagih()
    {
        $draw   = $this->input->post('draw');
        $length = $this->input->post('length');
        $start  = $this->input->post('start');

        $this->_build_datatables_query();

        // Clone untuk count total data sesudah filter
        $temp_db      = clone $this->db;
        $query_total  = $temp_db->get();
        $recordsTotal = ($query_total) ? $query_total->num_rows() : 0;

        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);
        $get_data = $this->db->get();

        $hasil = [];
        $no    = $start;

        // Logic validasi cut-off tanggal 25 digenerate satu kali di luar loop agar lebih efisien
        $cut_off_day    = 25;
        $bulan_sekarang = (date('j') >= $cut_off_day) ? date('Ym', strtotime('+1 month')) : date('Ym');

        foreach ($get_data->result() as $item) {
            $no++;
            $hasil[] = [
                'no'             => $no,
                'company'        => $item->nm_company,
                'no_spk'         => $item->id_spk_penawaran,
                'customer'       => $item->nm_customer,
                'project'        => $item->nm_project,
                'project_leader' => $item->nm_project_leader,
                'sales'          => $item->nm_sales,
                'keterangan'     => $item->desc_payment,
                'nominal'        => number_format($item->nominal_payment),
                'status'         => $this->_get_status_button($item->status_terakhir),
                'option'         => $this->_get_option_button($item, $bulan_sekarang)
            ];
        }

        echo json_encode([
            'draw'            => intval($draw),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data'            => $hasil
        ]);
    }

    private function _build_datatables_query()
    {
        $bulan  = $this->input->post('bulan');
        $tahun  = $this->input->post('tahun');
        $status = $this->input->post('status');
        $search = $this->input->post('search');

        /**
         * LOGIKA EFFECTIVE DATE:
         * Mengambil tanggal terbaru dari tabel actual (alias d). 
         * Jika tidak ada, baru ambil dari tgl_plan_tagih (tabel a).
         */
        $this->db->select('a.*, b.id_customer, b.nm_customer, c.nm_project_leader, c.nm_sales, COALESCE(d.nm_company, c.nm_company) as nm_company, e.nm_paket as nm_project');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran d', 'd.id_quotation = c.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header e', 'e.id_konsultasi_h = c.id_project', 'left');

        if ($bulan == 'macet') {
            $this->db->where('a.status_terakhir', '3');
        } else {
            $this->db->where('YEAR(COALESCE(a.tgl_aktual_plan_tagih, a.tgl_plan_tagih)) =', $tahun);
            $this->db->where('MONTH(COALESCE(a.tgl_aktual_plan_tagih, a.tgl_plan_tagih)) =', $bulan);

            if (!empty($status)) {
                $this->db->where('a.status_terakhir', $status);
            } else {
                $this->db->where('a.status_terakhir <>', '3');
            }
        }

        // Filter Search
        if (!empty($search['value'])) {
            $val = $search['value'];
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $val, 'both');
            $this->db->or_like('a.desc_payment', $val, 'both');
            $this->db->or_like('b.nm_customer', $val, 'both');
            $this->db->or_like('b.nm_project', $val, 'both');
            $this->db->or_like('c.nm_project_leader', $val, 'both');
            $this->db->or_like('c.nm_sales', $val, 'both');
            $this->db->or_like('a.nominal_payment', $val, 'both');
            $this->db->group_end();
        }

        $this->db->group_by('a.id');
    }

    private function _get_status_button($status_terakhir)
    {
        switch ($status_terakhir) {
            case '1':
                return '<button type="button" class="btn btn-sm btn-success">Tagih</button>';
            case '3':
                return '<button type="button" class="btn btn-sm btn-danger">Tagihan Macet</button>';
            default:
                return '<button type="button" class="btn btn-sm btn-primary">Waiting Actual Plan Tagih</button>';
        }
    }

    private function _get_option_button($item, $bulan_sekarang)
    {
        if ($item->status_terakhir == '3') {
            return '<button type="button" class="btn btn-sm btn-warning aktual_tagihan_macet" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';
        }

        if ($item->status_terakhir != '1') {
            $tgl_data = (!empty($item->tgl_aktual_plan_tagih)) ? $item->tgl_aktual_plan_tagih : $item->tgl_plan_tagih;
            if ($tgl_data) {
                $bulan_data = date('Ym', strtotime($tgl_data));
                if ($bulan_data <= $bulan_sekarang) {
                    return '<button type="button" class="btn btn-sm btn-warning aktual_tagihan" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';
                }
            }
        }

        return '';
    }

    public function dataDownloadExcel($tahun = null, $status = null)
    {
        // --- QUERY UTAMA ---
        $this->db->select('a.*, b.nm_customer, b.nm_project, b.nm_project_leader, a.tgl_aktual_plan_tagih as tanggal_aktual, a.created_date as crated_actual, a.status_terakhir as status_tagih_mundur, c.nm_sales, c.nm_customer, c.nm_project_leader, COALESCE(c.nm_company, e.nm_company) as nm_company');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran e', 'e.id_quotation = c.id_penawaran', 'left');

        // Filter tahun hanya berlaku jika status_terakhir bukan 3 (Tagihan Macet).
        if (!empty($tahun)) {
            $this->db->group_start();
            $this->db->where('YEAR(COALESCE(a.tgl_aktual_plan_tagih, a.tgl_plan_tagih)) =', $tahun);
            $this->db->or_where('a.status_terakhir', '3');
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('a.status_terakhir', $status);
        }

        $this->db->order_by('a.id', 'desc');
        $this->db->group_by('a.id');

        $get_data = $this->db->get()->result_array();

        return $get_data;
    }
}
