<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Report_actual_plan_tagih extends Admin_Controller
{
    protected $viewPermission     = 'Report_Jurnal_Penerimaan.View';
    protected $addPermission      = 'Report_Jurnal_Penerimaan.Add';
    protected $managePermission = 'Report_Jurnal_Penerimaan.Manage';
    protected $deletePermission = 'Report_Jurnal_Penerimaan.Delete';

    protected $consultant;
    protected $hris;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_actual_plan_tagih/Report_actual_plan_tagih_model',
        ));
        $this->template->title('Jurnal');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
        $this->hris = $this->load->database('hris', true);
    }


    public function index()
    {
        $list_customer = $this->Report_actual_plan_tagih_model->list_customer();
        $list_company = $this->Report_actual_plan_tagih_model->list_company();

        $data = [
            'list_customer' => $list_customer,
            'list_company' => $list_company
        ];

        $this->template->title('Report Actual Plan Tagih');
        $this->template->set($data);
        $this->template->render('index');
    }

    public function download_excel()
    {
        $get = $this->input->get();

        $data = $this->Report_actual_plan_tagih_model->list_report_filterable($get['client'], $get['company'], $get['tahun']);

        $nm_client = '';
        if (!empty($get['client'])) {
            $get_client = $this->db->get_where('view_report_actual_plan_tagih', ['id_customer' => $get['client']])->row();

            $nm_client = (!empty($get_client->nm_customer)) ? $get_client->nm_customer : '';
        }

        $nm_company = '';
        if (!empty($get['company'])) {
            $get_company = $this->db->get_where('view_report_actual_plan_tagih', ['id_company' => $get['company']])->row();

            $nm_company = (!empty($get_company->nm_company)) ? $get_company->nm_company : '';
        }

        $this->load->view('export_excel', ['list_report' => $data, 'nm_client' => $nm_client, 'nm_company' => $nm_company, 'tahun' => $get['tahun']]);
    }

    public function get_data_report_apt()
    {
        $get = $this->input->get();

        $draw = intval($get['draw']);
        $length = $get['length'];
        $start = $get['start'];
        $search = $get['search']['value'];
        $order_col = (!empty($get['order'][0]['column'])) ? $get['order'][0]['column'] : ''; // Index kolom
        $order_dir = (!empty($get['order'][0]['dir'])) ? $get['order'][0]['dir'] : '';    // 'asc' atau 'desc'
        $columns = $get['columns'];              // Data kolom dari client-side

        $client = $get['client'];
        $company = $get['company'];
        $tahun = $get['tahun'];

        $order_map = [
            1 => 'c.nm_company',
            2 => 'a.id_spk_penawaran',
            3 => 'a.nm_customer',
            4 => 'd.nm_paket',
            5 => 'a.nilai_kontrak'
        ];

        $this->db->select('a.id_spk_penawaran, a.id_customer, a.nm_customer, a.nilai_kontrak, c.id as id_company, c.nm_company, d.nm_paket');
        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company c', 'c.id = b.company', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.sts_spk', '1');
        if (!empty($client)) {
            $this->db->where('a.id_customer', $client);
        }
        if (!empty($company)) {
            $this->db->where('b.company', $company);
        }
        $this->db->group_by('a.id_spk_penawaran');

        $count_all = $this->db->count_all_results();

        $this->db->select('a.id_spk_penawaran, a.id_customer, a.nm_customer, a.nilai_kontrak, c.id as id_company, c.nm_company, d.nm_paket');
        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company c', 'c.id = b.company', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.sts_spk', '1');
        if (!empty($client)) {
            $this->db->where('a.id_customer', $client);
        }
        if (!empty($company)) {
            $this->db->where('b.company', $company);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('c.nm_company', $search, 'both');
            $this->db->or_like('a.id_spk_penawaran', $search, 'both');
            $this->db->or_like('a.nm_customer', $search, 'both');
            $this->db->or_like('d.nm_paket', $search, 'both');
            $this->db->group_end();
        }

        $this->db->group_by('a.id_spk_penawaran');

        $count_filter = $this->db->count_all_results();

        $this->db->select('a.id_spk_penawaran, a.id_customer, a.nm_customer, a.nilai_kontrak, c.id as id_company, c.nm_company, d.nm_paket');
        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company c', 'c.id = b.company', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.sts_spk', '1');
        if (!empty($client)) {
            $this->db->where('a.id_customer', $client);
        }
        if (!empty($company)) {
            $this->db->where('b.company', $company);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('c.nm_company', $search, 'both');
            $this->db->or_like('a.id_spk_penawaran', $search, 'both');
            $this->db->or_like('a.nm_customer', $search, 'both');
            $this->db->or_like('d.nm_paket', $search, 'both');
            $this->db->group_end();
        }

        $this->db->group_by('a.id_spk_penawaran');
        if (!empty($order_map[$order_col])) {
            $this->db->order_by($order_map[$order_col], $order_dir);
        } else {
            // Default sorting kalo user belum klik apa-apa
            $this->db->order_by('a.id_spk_penawaran', 'DESC');
        }
        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $no = (0 + $start);
        $hasil = [];

        $total_jan = 0;
        $total_feb = 0;
        $total_mar = 0;
        $total_apr = 0;
        $total_may = 0;
        $total_jun = 0;
        $total_jul = 0;
        $total_aug = 0;
        $total_sep = 0;
        $total_oct = 0;
        $total_nov = 0;
        $total_dec = 0;

        $ttl_nominal_spk = 0;
        $ttl_invoice = 0;
        $ttl_uninvoice = 0;
        $ttl_macet = 0;

        foreach ($get_data as $item) {
            $no++;

            $this->db->select('COALESCE(SUM(a.total_nominal), 0) as total_invoice');
            $this->db->from('tr_invoicing a');
            $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            $get_invoicing = $this->db->get()->row();

            $total_invoice = (!empty($get_invoicing->total_invoice)) ? $get_invoicing->total_invoice : 0;

            $total_uninvoiced = ($item->nilai_kontrak - $total_invoice);

            $total_macet = 0;

            $this->db->select('a.*,b.nominal_payment');
            $this->db->from('kons_tr_actual_plan_tagih a');
            $this->db->join('kons_tr_plan_tagih_detail b', 'b.id = a.id_detail_plan_tagih');
            $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            // $this->db->where('YEAR(a.tanggal_actual_plan_tagih)', $tahun);
            // $this->db->where('MONTH(a.tanggal_actual_plan_tagih)', $item_bulan);
            $this->db->group_start();
            $this->db->where('a.tagih_mundur', '3');
            $this->db->or_where('a.macet', '1');
            $this->db->group_end();
            $this->db->group_by('a.id_detail_plan_tagih');
            $get_nilai_macet = $this->db->get()->result();

            foreach ($get_nilai_macet as $item_nilai_macet) {
                $this->db->select('a.id');
                $this->db->from('kons_tr_actual_plan_tagih a');
                $this->db->where('a.id_detail_plan_tagih', $item_nilai_macet->id_detail_plan_tagih);
                $this->db->where('a.id_spk_penawaran', $item_nilai_macet->id_spk_penawaran);
                // $this->db->group_start();
                $this->db->where_in('a.tagih_mundur', ['1', '2']);
                $this->db->where('a.created_date >', $item_nilai_macet->created_date);
                $this->db->group_start();
                $this->db->where('a.macet IS NULL');
                $this->db->or_where('a.macet', '');
                $this->db->group_end();
                // $this->db->group_end();
                $get_check_tagih_mundur_balik = $this->db->get()->num_rows();

                if ($get_check_tagih_mundur_balik < 1) {
                    $total_macet += $item_nilai_macet->nominal_payment;
                }
            }

            // $this->db->select('COALESCE(a.nominal_payment, 0) as total_macet');
            // $this->db->from('kons_tr_actual_plan_tagih a');
            // $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            // $this->db->group_start();
            // $this->db->where('a.tagih_mundur', '3');
            // $this->db->or_where('a.macet', '1');
            // $this->db->group_end();
            // $this->db->group_by('a.id_detail_plan_tagih');
            // $get_tagihan_macet = $this->db->get()->result();

            // foreach ($get_tagihan_macet as $item_macet) {

            //     $this->db->select('a.id');
            //     $this->db->from('kons_tr_actual_plan_tagih a');
            //     $this->db->where('a.id_detail_plan_tagih', $item_macet->id_detail_plan_tagih);
            //     $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            //     $this->db->where('a.tagih_mundur', '1');
            //     $this->db->where('a.created_date >', $item->created_date);
            //     $get_check_tagih_mundur_balik = $this->db->get()->num_rows();

            //     if ($get_check_tagih_mundur_balik < 1) {
            //         $total_macet += $item_macet->total_macet;
            //     }
            // }

            $arr_bulan = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            $arr_noms = [];
            foreach ($arr_bulan as $item_bulan) :

                $this->db->select('b.nominal_payment');
                $this->db->from('kons_tr_actual_plan_tagih a');
                $this->db->join('kons_tr_plan_tagih_detail b', 'b.id = a.id_detail_plan_tagih');
                $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
                $this->db->where('YEAR(a.tanggal_actual_plan_tagih)', $tahun);
                $this->db->where('MONTH(a.tanggal_actual_plan_tagih)', $item_bulan);
                $this->db->where('a.tagih_mundur', '1');
                $this->db->group_by('a.id_detail_plan_tagih');
                $get_nilai_per_bulan = $this->db->get()->result();

                $total_perbulan = 0;
                foreach ($get_nilai_per_bulan as $item_nilai_perbulan) {
                    $total_perbulan += $item_nilai_perbulan->nominal_payment;
                }



                // $this->db->select('
                //     COALESCE(b.nominal_payment, a.nominal_payment) as nilai_perbulan, 
                //     b.tagih_mundur, 
                //     b.id_detail_plan_tagih, 
                //     b.tanggal_actual_plan_tagih
                // ', FALSE); // FALSE supaya CI tidak otomatis nambahin backtick (`) yang bikin error di subquery

                // $this->db->from('kons_tr_plan_tagih_detail a');

                // // Join dengan subquery yang lebih bersih
                // $join_subquery = '(SELECT dd.id_detail_plan_tagih, dd.nominal_payment, dd.tagih_mundur, dd.tanggal_actual_plan_tagih 
                //    FROM kons_tr_actual_plan_tagih dd 
                //    WHERE dd.tanggal_actual_plan_tagih IS NOT NULL 
                //    ORDER BY dd.created_date DESC LIMIT 1) b';

                // $this->db->join($join_subquery, 'b.id_detail_plan_tagih = a.id', 'left');

                // $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);

                // // Gunakan pembanding tanggal yang lebih efisien (Sargable)
                // $target_date = $tahun . '-' . str_pad($item_bulan, 2, '0', STR_PAD_LEFT);
                // // $this->db->where("DATE_FORMAT(COALESCE(b.tanggal_actual_plan_tagih, a.tgl_plan_tagih), '%Y-%m') =", $target_date);
                // $this->db->where("DATE_FORMAT(b.tanggal_actual_plan_tagih, '%Y-%m') =", $target_date);

                // $this->db->group_by('a.id');
                // $get_nilai_perbulan = $this->db->get()->result();

                // $total_perbulan = 0;

                // foreach ($get_nilai_perbulan as $item_nilai_perbulan) {
                //     if (!empty($item_nilai_perbulan->tagih_mundur)) {
                //         if ($item_nilai_perbulan->tagih_mundur == '2') {
                //             $this->db->select('a.id');
                //             $this->db->from('kons_tr_actual_plan_tagih a');
                //             $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
                //             $this->db->where('a.id_detail_plan_tagih', $item_nilai_perbulan->id_detail_plan_tagih);
                //             $this->db->where('a.tanggal_actual_plan_tagih >', $item_nilai_perbulan->tanggal_actual_plan_tagih);
                //             $this->db->where('a.tanggal_actual_plan_tagih IS NOT NULL');
                //             if (!empty($item_nilai_perbulan->tanggal_actual_plan_tagih)) {
                //             }
                //             $get_check_lastest = $this->db->get()->num_rows();

                //             // print_r($this->db->last_query());
                //             // exit;

                //             if ($get_check_lastest < 1) {
                //                 $total_perbulan += $item_nilai_perbulan->nilai_perbulan;
                //             }
                //         } else {
                //             $total_perbulan += $item_nilai_perbulan->nilai_perbulan;
                //         }
                //     } else {
                //         $total_perbulan += $item_nilai_perbulan->nilai_perbulan;
                //     }
                // }
                $arr_noms[$item_bulan] = $total_perbulan;
            endforeach;

            if ($arr_noms[1] > 0 || $arr_noms[2] > 0 || $arr_noms[3] > 0 || $arr_noms[4] > 0 || $arr_noms[5] > 0 || $arr_noms[6] > 0 || $arr_noms[7] > 0 || $arr_noms[8] > 0 || $arr_noms[9] > 0 || $arr_noms[10] > 0 || $arr_noms[11] > 0 || $arr_noms[12] > 0) {
            }

            $hasil[] = [
                'no' => $no,
                'company' => $item->nm_company,
                'no_spk' => $item->id_spk_penawaran,
                'customer' => $item->nm_customer,
                'project' => $item->nm_paket,
                'nominal_spk' => number_format($item->nilai_kontrak),
                'nominal_invoice' => number_format($total_invoice),
                'nominal_uninvoice' => number_format($total_uninvoiced),
                'macet' => number_format($total_macet),
                'jan' => number_format((!empty($arr_noms[1])) ? $arr_noms[1] : 0),
                'feb' => number_format((!empty($arr_noms[2])) ? $arr_noms[2] : 0),
                'mar' => number_format((!empty($arr_noms[3])) ? $arr_noms[3] : 0),
                'apr' => number_format((!empty($arr_noms[4])) ? $arr_noms[4] : 0),
                'may' => number_format((!empty($arr_noms[5])) ? $arr_noms[5] : 0),
                'jun' => number_format((!empty($arr_noms[6])) ? $arr_noms[6] : 0),
                'jul' => number_format((!empty($arr_noms[7])) ? $arr_noms[7] : 0),
                'aug' => number_format((!empty($arr_noms[8])) ? $arr_noms[8] : 0),
                'sep' => number_format((!empty($arr_noms[9])) ? $arr_noms[9] : 0),
                'oct' => number_format((!empty($arr_noms[10])) ? $arr_noms[10] : 0),
                'nov' => number_format((!empty($arr_noms[11])) ? $arr_noms[11] : 0),
                'dec' => number_format((!empty($arr_noms[12])) ? $arr_noms[12] : 0)
            ];

            $total_jan += (!empty($arr_noms[1])) ? $arr_noms[1] : 0;
            $total_feb += (!empty($arr_noms[2])) ? $arr_noms[2] : 0;
            $total_mar += (!empty($arr_noms[3])) ? $arr_noms[3] : 0;
            $total_apr += (!empty($arr_noms[4])) ? $arr_noms[4] : 0;
            $total_may += (!empty($arr_noms[5])) ? $arr_noms[5] : 0;
            $total_jun += (!empty($arr_noms[6])) ? $arr_noms[6] : 0;
            $total_jul += (!empty($arr_noms[7])) ? $arr_noms[7] : 0;
            $total_aug += (!empty($arr_noms[8])) ? $arr_noms[8] : 0;
            $total_sep += (!empty($arr_noms[9])) ? $arr_noms[9] : 0;
            $total_oct += (!empty($arr_noms[10])) ? $arr_noms[10] : 0;
            $total_nov += (!empty($arr_noms[11])) ? $arr_noms[11] : 0;
            $total_dec += (!empty($arr_noms[12])) ? $arr_noms[12] : 0;

            $ttl_nominal_spk += $item->nilai_kontrak;
            $ttl_invoice += $total_invoice;
            $ttl_uninvoice += $total_uninvoiced;
            $ttl_macet += $total_macet;
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filter,
            'data' => $hasil,
            'total_nominal_spk' => $ttl_nominal_spk,
            'total_invoice' => $ttl_invoice,
            'total_uninvoice' => $ttl_uninvoice,
            'total_macet' => $ttl_macet,
            'total_jan' => $total_jan,
            'total_feb' => $total_feb,
            'total_mar' => $total_mar,
            'total_apr' => $total_apr,
            'total_may' => $total_may,
            'total_jun' => $total_jun,
            'total_jul' => $total_jul,
            'total_aug' => $total_aug,
            'total_sep' => $total_sep,
            'total_oct' => $total_oct,
            'total_nov' => $total_nov,
            'total_dec' => $total_dec
        ];

        echo json_encode($response);
    }
}
