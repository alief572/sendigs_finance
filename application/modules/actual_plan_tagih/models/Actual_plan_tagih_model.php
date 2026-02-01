<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

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

    public function get_actual_plan_tagih()
    {
        $draw   = $this->input->post('draw');
        $length = $this->input->post('length');
        $start  = $this->input->post('start');
        $search = $this->input->post('search');
        $bulan  = $this->input->post('bulan');
        $tahun  = $this->input->post('tahun');
        $status = $this->input->post('status');

        // Definisikan kolom tanggal efektif secara konsisten
        // Jika ada tanggal_actual_plan_tagih yang valid, pakai itu. Jika tidak, pakai tgl_plan_tagih.
        $effective_date = "(CASE 
            WHEN d.tanggal_actual_plan_tagih IS NULL OR YEAR(d.tanggal_actual_plan_tagih) = 0
            THEN a.tgl_plan_tagih 
            ELSE d.tanggal_actual_plan_tagih 
        END)";

        // --- QUERY UTAMA ---
        $this->db->select('a.*, b.nm_customer, b.nm_project, b.nm_project_leader, ' . $effective_date . ' as tanggal_aktual, d.created_date as crated_actual, d.tagih_mundur as status_tagih_mundur, c.nm_sales, c.nm_customer, c.nm_project_leader, COALESCE(c.nm_company, e.nm_company) as nm_company');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header', 'left');
        $this->db->join('kons_tr_actual_plan_tagih d', 'd.id_top = a.id_top', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran e', 'e.id_quotation = c.id_penawaran', 'left');

        // Filter Bulan & Tahun
        if ($bulan == 'macet') {
            $this->db->where('d.id IS NOT NULL');
            $this->db->where('d.tagih_mundur', '3');
        } else {
            $this->db->where("YEAR($effective_date) =", $tahun);
            $this->db->where("MONTH($effective_date) =", intval($bulan));
        }

        // Filter Status
        if (!empty($status)) {
            if ($status == '1' || $status == '2') {
                $this->db->where('d.tagih_mundur', $status);
            } else if ($status == '3') {
                $this->db->where('d.id', null);
            }
        } else if ($bulan != 'macet') {
            $this->db->group_start();
            $this->db->where_in('d.tagih_mundur', ['1', '2', '3']);
            $this->db->or_where('d.id', null);
            $this->db->group_end();
        }

        // Filter Search
        if (!empty($search['value'])) {
            $val = $search['value'];
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $val);
            $this->db->or_like('a.desc_payment', $val);
            $this->db->or_like('b.nm_customer', $val);
            $this->db->or_like('b.nm_project', $val);
            $this->db->or_like('c.nm_project_leader', $val);
            $this->db->or_like('c.nm_sales', $val);
            $this->db->or_like('e.nm_company', $val);
            $this->db->or_like('COALESCE(c.nm_company, e.nm_company)', $val);
            $this->db->group_end();
        }

        $this->db->group_by('a.id');

        // Simpan clone sebelum ditambahkan order_by dan limit
        $temp_db = clone $this->db;

        // Hitung total records menggunakan num_rows
        $query_total = $temp_db->get();
        $recordsTotal = ($query_total) ? $query_total->num_rows() : 0;

        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);
        $get_data = $this->db->get();

        // print_r($this->db->last_query());
        // exit;



        $hasil = [];
        $no = $start;

        foreach ($get_data->result() as $item) {
            $no++;

            // Logic Sales & Company (diambil dari penawaran)
            $this->consultant->select('a.*');
            $this->consultant->from('kons_tr_spk_penawaran a');
            $this->consultant->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            $get_spk_penawaran = $this->consultant->get()->row();

            $nm_sales = $get_spk_penawaran->nm_sales ?? '';
            $nm_customer = $get_spk_penawaran->nm_customer ?? '';
            $nm_project_leader = $get_spk_penawaran->nm_project_leader ?? '';
            $nm_company = $get_spk_penawaran->nm_company ?? $item->nm_company;

            // Tentukan Status Button
            $status_btn = '<button type="button" class="btn btn-sm btn-primary">Waiting Actual Plan Tagih</button>';
            if ($item->status_tagih_mundur == '3' || $bulan == 'macet') {
                $status_btn = '<button type="button" class="btn btn-sm btn-danger">Tagihan Macet</button>';
            } else if ($item->status_tagih_mundur == '1') {
                $status_btn = '<button type="button" class="btn btn-sm btn-success">Tagih</button>';
            }

            // Logic Validasi Tombol Edit (Tgl 25)
            $valid_btn = 0;
            $tgl_data = $item->tanggal_aktual;
            if ($tgl_data) {
                $bulan_data = date('Ym', strtotime($tgl_data));
                $cut_off_day = 25;
                $bulan_sekarang = (date('j') >= $cut_off_day) ? date('Ym', strtotime('+1 month')) : date('Ym');

                if ($bulan_data <= $bulan_sekarang) {
                    $valid_btn = 1;
                }
            }

            $option = '';
            if ($bulan == 'macet') {
                $option = '<button type="button" class="btn btn-sm btn-warning aktual_tagihan_macet" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';
            } else if ($valid_btn == 1 && $item->status_tagih_mundur != '1') {
                $option = '<button type="button" class="btn btn-sm btn-warning aktual_tagihan" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';
            }

            $hasil[] = [
                'no' => $no,
                'company' => $nm_company,
                'no_spk' => $item->id_spk_penawaran,
                'customer' => $nm_customer,
                'project' => $item->nm_project,
                'project_leader' => $nm_project_leader,
                'sales' => $nm_sales,
                'keterangan' => $item->desc_payment,
                'status' => $status_btn,
                'option' => $option
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $hasil
        ]);
    }

    public function dataDownloadExcel($tahun = null, $status = null)
    {
        $effective_date = "(CASE 
            WHEN d.tanggal_actual_plan_tagih IS NULL OR YEAR(d.tanggal_actual_plan_tagih) = 0
            THEN a.tgl_plan_tagih 
            ELSE d.tanggal_actual_plan_tagih 
        END)";

        // --- QUERY UTAMA ---
        $this->db->select('a.*, b.nm_customer, b.nm_project, b.nm_project_leader, ' . $effective_date . ' as tanggal_aktual, d.created_date as crated_actual, d.tagih_mundur as status_tagih_mundur, c.nm_sales, c.nm_customer, c.nm_project_leader, COALESCE(c.nm_company, e.nm_company) as nm_company');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header', 'left');
        $this->db->join('kons_tr_actual_plan_tagih d', 'd.id_top = a.id_top', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran e', 'e.id_quotation = c.id_penawaran', 'left');
        if (!empty($tahun)) {
            $this->db->where('YEAR(' . $effective_date . ') =', $tahun);
        }
        if (!empty($status)) {
            if ($status == '1' || $status == '2') {
                $this->db->where('d.tagih_mundur', $status);
            } else if ($status == '3') {
                $this->db->where('d.id', null);
            }
        } else {
            $this->db->group_start();
            $this->db->where_in('d.tagih_mundur', ['1', '2', '3']);
            $this->db->or_where('d.id', null);
            $this->db->group_end();
        }
        $this->db->order_by('a.id', 'desc');
        $this->db->group_by('a.id');

        $get_data = $this->db->get()->result_array();

        return $get_data;
    }
}
