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

        $client = $get['client'];
        $company = $get['company'];
        $tahun = $get['tahun'];

        $this->db->select('a.id_spk_penawaran');
        $this->db->from('view_report_actual_plan_tagih a');
        if (!empty($client)) {
            $this->db->where('a.id_customer', $client);
        }
        if (!empty($company)) {
            $this->db->where('a.id_company', $company);
        }
        $this->db->group_by('a.id_spk_penawaran');

        $count_all = $this->db->get()->num_rows();

        $this->db->select('a.id_spk_penawaran');
        $this->db->from('view_report_actual_plan_tagih a');
        $this->db->where('a.tahun', $tahun);
        if (!empty($client)) {
            $this->db->where('a.id_customer', $client);
        }
        if (!empty($company)) {
            $this->db->where('a.id_company', $company);
        }
        if (!empty($search)) {
            $this->db->like('a.nm_company', $search, 'both');
            $this->db->or_like('a.id_spk_penawaran', $search, 'both');
            $this->db->or_like('a.nm_customer', $search, 'both');
            $this->db->or_like('a.nm_paket', $search, 'both');
            $this->db->or_like('a.nilai_kontrak', $search, 'both');
            $this->db->or_like('a.total_invoice', $search, 'both');
            // $this->db->or_like('a.total_uninvoice', $search, 'both');
        }
        $this->db->group_by('a.id_spk_penawaran');

        $count_filter = $this->db->get()->num_rows();

        $this->db->select('a.*');
        $this->db->from('view_report_actual_plan_tagih a');
        $this->db->where('a.tahun', $tahun);
        if (!empty($client)) {
            $this->db->where('a.id_customer', $client);
        }
        if (!empty($company)) {
            $this->db->where('a.id_company', $company);
        }
        if (!empty($search)) {
            $this->db->like('a.nm_company', $search, 'both');
            $this->db->or_like('a.id_spk_penawaran', $search, 'both');
            $this->db->or_like('a.nm_customer', $search, 'both');
            $this->db->or_like('a.nm_paket', $search, 'both');
            $this->db->or_like('a.nilai_kontrak', $search, 'both');
            $this->db->or_like('a.total_invoice', $search, 'both');
            // $this->db->or_like('a.total_uninvoice', $search, 'both');
        }
        $this->db->group_by('a.id_spk_penawaran');
        $this->db->order_by('a.id_spk_penawaran', 'asc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $no = (0 + $start);

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

        $hasil = [];
        foreach ($get_data as $item) :
            $no++;

            $total_invoice = 0;

            $arr_bulan = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            $arr_noms = [];
            foreach ($arr_bulan as $item_bulan) :
                $this->db->select('COALESCE(SUM(a.nominal_bulanan), 0.00) as total_bulanan');
                $this->db->from('view_report_actual_plan_tagih a');
                $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
                $this->db->where('a.bulan', $item_bulan);
                $this->db->where('a.tahun', $tahun);
                $get_nominal_perbulan = $this->db->get()->row();

                $arr_noms[$item_bulan] = $get_nominal_perbulan->total_bulanan;
                $total_invoice += $get_nominal_perbulan->total_bulanan;
            endforeach;

            $this->db->select('a.*');
            $this->db->from('kons_tr_actual_plan_tagih a');
            $this->db->where('a.tagih_mundur', '3');
            $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            $this->db->group_start();
            $this->db->where('DATE_FORMAT(a.tanggal_actual_plan_tagih, "%Y") =', $tahun);
            $this->db->or_where('DATE_FORMAT(a.tgl_plan_tagih, "%Y") =', $tahun);
            $this->db->group_end();

            $get_tagihan_macet = $this->db->get()->result();
            // print_r($this->db->last_query());
            // exit;



            $macet = 0;
            foreach ($get_tagihan_macet as $item_macet) :
                $get_tagihan_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', ['id_detail_plan_tagih' => $item_macet->id_detail_plan_tagih, 'id_top' => $item_macet->id_top, 'tagih_mundur' => '1'])->result();

                if (empty($get_tagihan_tagih)) {
                    $macet += $item_macet->nominal_payment;
                }
            endforeach;

            $hasil[] = [
                'no' => $no,
                'company' => $item->nm_company,
                'no_spk' => $item->id_spk_penawaran,
                'customer' => $item->nm_customer,
                'project' => $item->nm_paket,
                'nominal_spk' => number_format($item->nilai_kontrak, 2),
                'nominal_invoice' => number_format($total_invoice, 2),
                'nominal_uninvoice' => number_format(($item->nilai_kontrak - $total_invoice), 2),
                'macet' => number_format($macet, 2),
                'jan' => number_format($arr_noms[1], 2),
                'feb' => number_format($arr_noms[2], 2),
                'mar' => number_format($arr_noms[3], 2),
                'apr' => number_format($arr_noms[4], 2),
                'may' => number_format($arr_noms[5], 2),
                'jun' => number_format($arr_noms[6], 2),
                'jul' => number_format($arr_noms[7], 2),
                'aug' => number_format($arr_noms[8], 2),
                'sep' => number_format($arr_noms[9], 2),
                'oct' => number_format($arr_noms[10], 2),
                'nov' => number_format($arr_noms[11], 2),
                'dec' => number_format($arr_noms[12], 2),
            ];

            $ttl_nominal_spk += $item->nilai_kontrak;
            $ttl_invoice += $total_invoice;
            $ttl_uninvoice += ($item->nilai_kontrak - $total_invoice);
            $ttl_macet += $macet;

            $total_jan += $arr_noms[1];
            $total_feb += $arr_noms[2];
            $total_mar += $arr_noms[3];
            $total_apr += $arr_noms[4];
            $total_may += $arr_noms[5];
            $total_jun += $arr_noms[6];
            $total_jul += $arr_noms[7];
            $total_aug += $arr_noms[8];
            $total_sep += $arr_noms[9];
            $total_oct += $arr_noms[10];
            $total_nov += $arr_noms[11];
            $total_dec += $arr_noms[12];
        endforeach;

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
