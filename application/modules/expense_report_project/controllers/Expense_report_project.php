<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Pengajuan Rutin
 */

$status = array();
class Expense_report_project extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Expense_Report_Project.View';
    protected $addPermission      = 'Expense_Report_Project.Add';
    protected $managePermission = 'Expense_Report_Project.Manage';
    protected $deletePermission = 'Expense_Report_Project.Delete';

    protected $accounting;
    protected $sendigs;

    public function __construct()
    {
        parent::__construct();
        $this->template->title('Expense Report Project');
        $this->template->page_icon('fa fa-cubes');
        $this->load->library('upload');
        $this->load->model(array('Expense_report_project/Expense_report_project_model'));
        date_default_timezone_set('Asia/Bangkok');

        $this->accounting = $this->load->database('gl_sendigs', true);
        $this->sendigs = $this->load->database('sendigs_finance', true);
    }

    // View Page Function

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Expense Report Project');
        $this->template->render('index');
    }

    public function add($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to, c.nm_paket as nama_project');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join('kons_master_konsultasi_header c', 'c.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $budget_subcont = 0;
        $this->db->select('a.mandays_subcont_final, a.mandays_rate_subcont_final');
        $this->db->from('kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont = $this->db->get()->result();

        foreach ($get_budget_subcont as $item) {
            $budget_subcont += ($item->mandays_rate_subcont_final * $item->mandays_subcont_final);
        }

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_subcont');
        $this->db->from('kons_tr_kasbon_req_ovb_subcont_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_subcont_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 1);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_subcont = $this->db->get()->row();
        $budget_subcont += $get_budget_ovb_subcont->total_ovb_subcont;

        $this->db->select('SUM(a.total_final) as budget_akomodasi');
        $this->db->from('kons_tr_spk_budgeting_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_akomodasi = $this->db->get()->row();
        $budget_akomodasi = $get_budget_akomodasi->budget_akomodasi;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_akomodasi');
        $this->db->from('kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_akomodasi_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 2);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_akomodasi = $this->db->get()->row();
        $budget_akomodasi += $get_budget_ovb_akomodasi->total_ovb_akomodasi;

        $this->db->select('SUM(a.total_final) as budget_others');
        $this->db->from('kons_tr_spk_budgeting_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_others = $this->db->get()->row();
        $budget_others = $get_budget_others->budget_others;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_others');
        $this->db->from('kons_tr_kasbon_req_ovb_others_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_others_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 3);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_others = $this->db->get()->row();
        $budget_others += $get_budget_ovb_others->total_ovb_others;

        $this->db->select('SUM(a.total_final) as budget_lab');
        $this->db->from('kons_tr_spk_budgeting_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_lab = $this->db->get()->row();
        $budget_lab = $get_budget_lab->budget_lab;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_lab');
        $this->db->from('kons_tr_kasbon_req_ovb_lab_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_lab_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 4);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_lab = $this->db->get()->row();
        $budget_lab += $get_budget_ovb_lab->total_ovb_lab;

        $this->db->select('SUM(a.total_final) as budget_subcont_tenaga_ahli');
        $this->db->from('kons_tr_spk_budgeting_subcont_tenaga_ahli a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont_tenaga_ahli = $this->db->get()->row();
        $budget_subcont_tenaga_ahli = $get_budget_subcont_tenaga_ahli->budget_subcont_tenaga_ahli;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_subcont_tenaga_ahli');
        $this->db->from('kons_tr_kasbon_req_ovb_subcont_tenaga_ahli_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_subcont_tenaga_ahli_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 5);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_subcont_tenaga_ahli = $this->db->get()->row();
        $budget_subcont_tenaga_ahli += $get_budget_ovb_subcont_tenaga_ahli->total_ovb_subcont_tenaga_ahli;

        $this->db->select('SUM(a.total_final) as budget_subcont_perusahaan');
        $this->db->from('kons_tr_spk_budgeting_subcont_perusahaan a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont_perusahaan = $this->db->get()->row();
        $budget_subcont_perusahaan = $get_budget_subcont_perusahaan->budget_subcont_perusahaan;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_subcont_perusahaan');
        $this->db->from('kons_tr_kasbon_req_ovb_subcont_perusahaan_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_subcont_perusahaan_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 6);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_subcont_perusahaan = $this->db->get()->row();
        $budget_subcont_perusahaan += $get_budget_ovb_subcont_perusahaan->total_ovb_subcont_perusahaan;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_subcont');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('b.deleted_at IS NULL');
        $this->db->where('a.tipe', 1);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_subcont = $this->db->get()->row();

        $nilai_kasbon_on_proses = $get_expense_subcont->ttl_expense_subcont;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_akomodasi');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('b.deleted_at IS NULL');
        $this->db->where('a.tipe', 2);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_akomodasi = $this->db->get()->row();

        $nilai_kasbon_on_proses_akomodasi = $get_expense_akomodasi->ttl_expense_akomodasi;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_others');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('b.deleted_at IS NULL');
        $this->db->where('a.tipe', 3);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_others = $this->db->get()->row();

        $nilai_kasbon_on_proses_others = $get_expense_others->ttl_expense_others;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_lab');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('b.deleted_at IS NULL');
        $this->db->where('a.tipe', 4);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_lab = $this->db->get()->row();

        $nilai_kasbon_on_proses_lab = $get_expense_lab->ttl_expense_lab;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_subcont_tenaga_ahli');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('b.deleted_at IS NULL');
        $this->db->where('a.tipe', 5);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_subcont_tenaga_ahli = $this->db->get()->row();

        $nilai_kasbon_on_proses_subcont_tenaga_ahli = $get_expense_subcont_tenaga_ahli->ttl_expense_subcont_tenaga_ahli;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_subcont_perusahaan');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('b.deleted_at IS NULL');
        $this->db->where('a.tipe', 6);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_subcont_perusahaan = $this->db->get()->row();

        $nilai_kasbon_on_proses_subcont_perusahaan = $get_expense_subcont_perusahaan->ttl_expense_subcont_perusahaan;

        $data = [
            'list_budgeting' => $get_budgeting,
            'budget_subcont' => $budget_subcont,
            'budget_akomodasi' => $budget_akomodasi,
            'budget_others' => $budget_others,
            'budget_lab' => $budget_lab,
            'budget_subcont_tenaga_ahli' => $budget_subcont_tenaga_ahli,
            'budget_subcont_perusahaan' => $budget_subcont_perusahaan,
            'nilai_kasbon_on_proses' => $nilai_kasbon_on_proses,
            'nilai_kasbon_on_proses_akomodasi' => $nilai_kasbon_on_proses_akomodasi,
            'nilai_kasbon_on_proses_others' => $nilai_kasbon_on_proses_others,
            'nilai_kasbon_on_proses_lab' => $nilai_kasbon_on_proses_lab,
            'nilai_kasbon_on_proses_subcont_tenaga_ahli' => $nilai_kasbon_on_proses_subcont_tenaga_ahli,
            'nilai_kasbon_on_proses_subcont_perusahaan' => $nilai_kasbon_on_proses_subcont_perusahaan
        ];

        $this->template->set($data);
        $this->template->render('add');
    }

    public function view($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $budget_subcont = 0;
        $this->db->select('a.mandays_subcont_final, a.mandays_rate_subcont_final');
        $this->db->from('kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont = $this->db->get()->result();

        foreach ($get_budget_subcont as $item) {
            $budget_subcont += ($item->mandays_rate_subcont_final * $item->mandays_subcont_final);
        }

        $this->db->select('SUM(a.total_final) as budget_akomodasi');
        $this->db->from('kons_tr_spk_budgeting_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_akomodasi = $this->db->get()->row();
        $budget_akomodasi = $get_budget_akomodasi->budget_akomodasi;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_akomodasi');
        $this->db->from('kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_akomodasi_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 2);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_akomodasi = $this->db->get()->row();
        $budget_akomodasi += $get_budget_ovb_akomodasi->total_ovb_akomodasi;

        $this->db->select('SUM(a.total_final) as budget_others');
        $this->db->from('kons_tr_spk_budgeting_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_others = $this->db->get()->row();
        $budget_others = $get_budget_others->budget_others;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_others');
        $this->db->from('kons_tr_kasbon_req_ovb_others_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_others_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 3);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_others = $this->db->get()->row();
        $budget_others += $get_budget_ovb_others->total_ovb_others;

        $this->db->select('SUM(a.total_final) as budget_lab');
        $this->db->from('kons_tr_spk_budgeting_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_lab = $this->db->get()->row();
        $budget_lab = $get_budget_lab->budget_lab;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_lab');
        $this->db->from('kons_tr_kasbon_req_ovb_lab_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_lab_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 4);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_lab = $this->db->get()->row();
        $budget_lab += $get_budget_ovb_lab->total_ovb_lab;

        $this->db->select('SUM(a.total_final) as budget_subcont_tenaga_ahli');
        $this->db->from('kons_tr_spk_budgeting_subcont_tenaga_ahli a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont_tenaga_ahli = $this->db->get()->row();
        $budget_subcont_tenaga_ahli = $get_budget_subcont_tenaga_ahli->budget_subcont_tenaga_ahli;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_subcont_tenaga_ahli');
        $this->db->from('kons_tr_kasbon_req_ovb_subcont_tenaga_ahli_header a');
        $this->db->join('kons_tr_kasbon_req_ovb_subcont_tenaga_ahli_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 5);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_subcont_tenaga_ahli = $this->db->get()->row();
        $budget_subcont_tenaga_ahli += $get_budget_ovb_subcont_tenaga_ahli->total_ovb_subcont_tenaga_ahli;

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_subcont = $this->db->get()->result();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_subcont as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->total_pengajuan;
            }
        }

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_akomodasi = $this->db->get()->result();

        $nilai_kasbon_on_proses_akomodasi = 0;
        foreach ($get_kasbon_akomodasi as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses_akomodasi += $item->total_pengajuan;
            }
        }

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_others = $this->db->get()->result();

        $nilai_kasbon_on_proses_others = 0;
        foreach ($get_kasbon_others as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses_others += $item->total_pengajuan;
            }
        }

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_lab = $this->db->get()->result();

        $nilai_kasbon_on_proses_lab = 0;
        foreach ($get_kasbon_lab as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses_lab += $item->total_pengajuan;
            }
        }

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_subcont_tenaga_ahli = $this->db->get()->result();

        $nilai_kasbon_on_proses_subcont_tenaga_ahli = 0;
        foreach ($get_kasbon_subcont_tenaga_ahli as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses_subcont_tenaga_ahli += $item->total_pengajuan;
            }
        }

        $data = [
            'list_budgeting' => $get_budgeting,
            'budget_subcont' => $budget_subcont,
            'budget_akomodasi' => $budget_akomodasi,
            'budget_others' => $budget_others,
            'budget_lab' => $budget_lab,
            'budget_subcont_tenaga_ahli' => $budget_subcont_tenaga_ahli,
            'nilai_kasbon_on_proses' => $nilai_kasbon_on_proses,
            'nilai_kasbon_on_proses_akomodasi' => $nilai_kasbon_on_proses_akomodasi,
            'nilai_kasbon_on_proses_others' => $nilai_kasbon_on_proses_others,
            'nilai_kasbon_on_proses_lab' => $nilai_kasbon_on_proses_lab,
            'nilai_kasbon_on_proses_subcont_tenaga_ahli' => $nilai_kasbon_on_proses_subcont_tenaga_ahli
        ];

        $this->template->set($data);
        $this->template->render('view');
    }

    public function add_expense_subcont($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $get_kasbon_header = $this->db->get_where('kons_tr_kasbon_project_header a', ['a.id' => $id_header])->row();
        $get_penawaran = $this->db->get_where('kons_tr_penawaran', ['id_quotation' => $get_kasbon_header->id_penawaran])->row();
        $get_company = $this->db->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row();

        $this->sendigs->select('a.id, a.rekening, a.nama, a.coa_bank, b.nama_bank');
        $this->sendigs->from('ms_bank a');
        $this->sendigs->join('list_bank b', 'b.id = a.bank');
        $this->sendigs->where('a.deleted', '0');
        $get_bank = $this->sendigs->get()->result();

        $id_company = (!empty($get_company)) ? $get_company->id : '';
        $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';

        $datalist_item = [];

        if ($get_kasbon_header->tipe == 1) {

            $this->db->select('a.*');
            $this->db->from('kons_tr_spk_budgeting_aktifitas a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_aktifitas', 'asc');
            $get_list_subcont = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_aktifitas', $item->id_aktifitas);
                $this->db->where('a.custom_subcont', 0);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_aktifitas,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_ovb_subcont a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_custom = $this->db->get()->result();

            foreach ($get_list_subcont_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_aktifitas', $item->id);
                $this->db->where('a.custom_subcont', 1);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }
        }

        if ($get_kasbon_header->tipe == 2) {
            $this->db->select('a.*, b.nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_akomodasi a');
            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_akomodasi = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_akomodasi a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_akomodasi_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_akomodasi as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_akomodasi a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_akomodasi', $item->id_akomodasi);
                $this->db->where('a.custom_akomodasi', 0);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_akomodasi_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_akomodasi a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_akomodasi', $item->id);
                $this->db->where('a.custom_akomodasi', 1);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }
        }

        if ($get_kasbon_header->tipe == 3) {
            $this->db->select('a.*, b.nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_others a');
            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_others = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_others a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_others_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_others as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_others a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_others', $item->id_others);
                $this->db->where('a.custom_others', 0);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_others_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_others a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_others', $item->id);
                $this->db->where('a.custom_others', 1);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }
        }

        if ($get_kasbon_header->tipe == 4) {
            $this->db->select('a.*, b.isu_lingkungan as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_lab a');
            $this->db->join('kons_master_lab b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_lab = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_lab a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_lab_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_lab as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_lab a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_lab', $item->id_lab);
                $this->db->where('a.custom_lab', 0);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_lab_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_lab a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_lab', $item->id);
                $this->db->where('a.custom_lab', 1);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }
        }

        if ($get_kasbon_header->tipe == 5) {
            $this->db->select('a.*, b.nm_biaya as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_subcont_tenaga_ahli a');
            $this->db->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_subcont_tenaga_ahli = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont_tenaga_ahli a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_tenaga_ahli_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont_tenaga_ahli as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id_subcont);
                $this->db->where('a.custom_subcont_tenaga_ahli', 0);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_subcont_tenaga_ahli_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id);
                $this->db->where('a.custom_subcont_tenaga_ahli', 1);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }
        }

        if ($get_kasbon_header->tipe == 6) {
            $this->db->select('a.*, b.nm_biaya as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_subcont_perusahaan a');
            $this->db->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_subcont_perusahaan = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont_perusahaan a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_perusahaan_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont_perusahaan as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_perusahaan a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id_subcont);
                $this->db->where('a.custom_subcont_perusahaan', 0);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_subcont_perusahaan_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_perusahaan a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id);
                $this->db->where('a.custom_subcont_perusahaan', 1);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }
        }

        $this->sendigs->select('a.id, a.rekening, a.nama, a.coa_bank, b.nama_bank');
        $this->sendigs->from('ms_bank a');
        $this->sendigs->join('list_bank b', 'b.id = a.bank', 'left');
        $this->sendigs->where('a.deleted', '0');
        $get_bank_acc = $this->sendigs->get()->result();

        $list_jurnal_pph21 = $this->Expense_report_project_model->list_jurnal_pph21($id_header);

        $data = [
            'datalist_item' => $datalist_item,
            'id_spk_budgeting' => $get_kasbon_header->id_spk_budgeting,
            'id_header' => $id_header,
            'id_spk_penawaran' => $get_kasbon_header->id_spk_penawaran,
            'id_penawaran' => $get_kasbon_header->id_penawaran,
            'tipe' => $get_kasbon_header->tipe,
            'data_bank' => $get_bank,
            'list_bank' => $get_bank_acc,
            'list_jurnal_pph21' => $list_jurnal_pph21
        ];

        $this->template->set($data);
        $this->template->render('add_expense_subcont');
    }

    public function edit_expense_subcont($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->where('a.id_header', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_expense_report_bukti_pengembalian a');
        $this->db->where('a.id_header_expense', $get_header->id);
        $get_bukti_pengembalian = $this->db->get()->result();

        $this->db->select('a.*');
        $this->db->from('kons_tr_bukti_penggunaan_expense a');
        $this->db->where('a.id_header_expense', $get_header->id);
        $get_bukti_penggunaan = $this->db->get()->result();

        $this->sendigs->select('a.id, a.rekening, a.nama, a.coa_bank, b.nama_bank');
        $this->sendigs->from('ms_bank a');
        $this->sendigs->join('list_bank b', 'b.id = a.bank');
        $this->sendigs->where('a.deleted', '0');
        $get_bank = $this->sendigs->get()->result();

        $get_kasbon_header = $this->db->get_where('kons_tr_kasbon_project_header a', ['a.id' => $id_header])->row();

        $datalist_item = [];
        $datalist_item_expense = [];

        if ($get_kasbon_header->tipe == 1) {
            $this->db->select('a.*');
            $this->db->from('kons_tr_spk_budgeting_aktifitas a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_aktifitas', 'asc');
            $get_list_subcont = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_aktifitas', $item->id_aktifitas);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_aktifitas,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_subcont_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_aktifitas', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 2) {
            $this->db->select('a.*, b.nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_akomodasi a');
            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_akomodasi = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_akomodasi a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_akomodasi_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_akomodasi as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_akomodasi a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_akomodasi', $item->id_akomodasi);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_akomodasi_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_akomodasi a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_akomodasi', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 3) {
            $this->db->select('a.*, b.nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_others a');
            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_others = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_others a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_others_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_others as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_others a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_others', $item->id_others);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_others_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_others a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_others', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 4) {
            $this->db->select('a.*, b.isu_lingkungan as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_lab a');
            $this->db->join('kons_master_lab b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_lab = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_lab a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_lab_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_lab as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_lab a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_lab', $item->id_lab);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_lab_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_lab a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_lab', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 5) {
            $this->db->select('a.*, b.nm_biaya as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_subcont_tenaga_ahli a');
            $this->db->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_subcont_tenaga_ahli = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont_tenaga_ahli a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_tenaga_ahli_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont_tenaga_ahli as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id_subcont);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_subcont_tenaga_ahli_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 6) {
            $this->db->select('a.*, b.nm_biaya as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_subcont_perusahaan a');
            $this->db->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_subcont_perusahaan = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont_perusahaan a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_perusahaan_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont_perusahaan as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_perusahaan a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        $list_jurnal_pph21 = $this->Expense_report_project_model->list_jurnal_pph21($id_header);

        $data = [
            'header' => $get_header,
            'list_bukti_pengembalian' => $get_bukti_pengembalian,
            'list_bukti_penggunaan' => $get_bukti_penggunaan,
            'datalist_item' => $datalist_item,
            'datalist_item_expense' => $datalist_item_expense,
            'id_spk_budgeting' => $get_kasbon_header->id_spk_budgeting,
            'id_header' => $id_header,
            'id_spk_penawaran' => $get_kasbon_header->id_spk_penawaran,
            'id_penawaran' => $get_kasbon_header->id_penawaran,
            'tipe' => $get_kasbon_header->tipe,
            'list_bank' => $get_bank,
            'list_jurnal_pph21' => $list_jurnal_pph21
        ];

        $this->template->set($data);
        $this->template->render('edit_expense_subcont');
    }

    public function view_expense_subcont($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->where('a.id_header', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_expense_report_bukti_pengembalian a');
        $this->db->where('a.id_header_expense', $get_header->id);
        $get_bukti_pengembalian = $this->db->get()->result();

        $this->db->select('a.*');
        $this->db->from('kons_tr_bukti_penggunaan_expense a');
        $this->db->where('a.id_header_expense', $get_header->id);
        $get_bukti_penggunaan = $this->db->get()->result();

        $get_kasbon_header = $this->db->get_where('kons_tr_kasbon_project_header a', ['a.id' => $id_header])->row();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to, c.nm_paket');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join('kons_master_konsultasi_header c', 'c.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
        $get_spk_budgeting = $this->db->get()->row();

        $datalist_item = [];
        $datalist_item_expense = [];

        if ($get_kasbon_header->tipe == 1) {
            $this->db->select('a.*');
            $this->db->from('kons_tr_spk_budgeting_aktifitas a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_aktifitas', 'asc');
            $get_list_subcont = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_aktifitas', $item->id_aktifitas);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_aktifitas,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_subcont_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_aktifitas', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 2) {
            $this->db->select('a.*, b.nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_akomodasi a');
            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_akomodasi = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_akomodasi a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_akomodasi_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_akomodasi as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_akomodasi a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_akomodasi', $item->id_akomodasi);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_akomodasi_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_akomodasi a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_akomodasi', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 3) {
            $this->db->select('a.*, b.nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_others a');
            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_others = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_others a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_others_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_others as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_others a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_others', $item->id_others);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_others_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_others a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_others', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 4) {
            $this->db->select('a.*, b.isu_lingkungan as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_lab a');
            $this->db->join('kons_master_lab b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_lab = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_lab a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_lab_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_lab as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_lab a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_lab', $item->id_lab);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_lab_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_lab a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_lab', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 5) {
            $this->db->select('a.*, b.nm_biaya as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_subcont_tenaga_ahli a');
            $this->db->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_subcont_tenaga_ahli = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont_tenaga_ahli a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_tenaga_ahli_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont_tenaga_ahli as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id_subcont);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_biaya,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            foreach ($get_list_subcont_tenaga_ahli_custom as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        if ($get_kasbon_header->tipe == 6) {
            $this->db->select('a.*, b.nm_biaya as nm_biaya');
            $this->db->from('kons_tr_spk_budgeting_subcont_perusahaan a');
            $this->db->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $this->db->order_by('a.id_item', 'asc');
            $get_list_subcont_perusahaan = $this->db->get()->result();

            $this->db->select('a.*');
            $this->db->from('kons_tr_kasbon_custom_subcont_perusahaan a');
            $this->db->where('a.id_spk_budgeting', $get_kasbon_header->id_spk_budgeting);
            $get_list_subcont_perusahaan_custom = $this->db->get()->result();

            $no = 0;
            foreach ($get_list_subcont_perusahaan as $item) {
                $no++;

                $qty_kasbon = 0;
                $nominal_kasbon = 0;
                $total_kasbon = 0;

                $this->db->select('a.*');
                $this->db->from('kons_tr_kasbon_project_subcont_perusahaan a');
                $this->db->where('a.id_header', $id_header);
                $this->db->where('a.id_subcont', $item->id);
                $get_kasbon = $this->db->get()->row();
                if (!empty($get_kasbon)) {
                    $qty_kasbon = $get_kasbon->qty_pengajuan;
                    $nominal_kasbon = $get_kasbon->nominal_pengajuan;
                    $total_kasbon = $get_kasbon->total_pengajuan;
                }

                $datalist_item[] = [
                    'no' => $no,
                    'id_detail_kasbon' => $item->id,
                    'nm_item' => $item->nm_item,
                    'qty_kasbon' => $qty_kasbon,
                    'nominal_kasbon' => $nominal_kasbon,
                    'total_kasbon' => $total_kasbon
                ];
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_detail a');
            $this->db->where('a.id_header_kasbon', $id_header);
            $get_expense_detail = $this->db->get()->result();

            $no = 1;
            foreach ($get_expense_detail as $item) {
                $qty_expense = ($item->qty_expense >= 1) ? $item->qty_expense : 1;
                $datalist_item_expense[$item->id_detail_kasbon] = [
                    'id' => $item->id,
                    'id_detail_kasbon' => $item->id_detail_kasbon,
                    'tipe' => $item->tipe,
                    'qty_expense' => $item->qty_expense,
                    'nominal_expense' => $item->nominal_expense,
                    'keterangan' => $item->keterangan,
                    'total_expense' => ($qty_expense * $item->nominal_expense)
                ];
            }
        }

        $this->sendigs->select('a.id, a.rekening, a.nama, a.coa_bank, b.nama_bank');
        $this->sendigs->from('ms_bank a');
        $this->sendigs->join('list_bank b', 'b.id = a.bank', 'left');
        $this->sendigs->where('a.deleted', '0');
        $get_bank_acc = $this->sendigs->get()->result();

        $list_jurnal_pph21 = $this->Expense_report_project_model->list_jurnal_pph21($id_header);

        $data = [
            'header' => $get_header,
            'list_bukti_pengembalian' => $get_bukti_pengembalian,
            'list_bukti_penggunaan' => $get_bukti_penggunaan,
            'datalist_item' => $datalist_item,
            'datalist_item_expense' => $datalist_item_expense,
            'id_spk_budgeting' => $get_kasbon_header->id_spk_budgeting,
            'id_header' => $id_header,
            'id_spk_penawaran' => $get_kasbon_header->id_spk_penawaran,
            'id_penawaran' => $get_kasbon_header->id_penawaran,
            'tipe' => $get_kasbon_header->tipe,
            'list_bank' => $get_bank_acc,
            'list_jurnal_pph21' => $list_jurnal_pph21,
            'list_budgeting' => $get_spk_budgeting
        ];

        $this->template->set($data);
        $this->template->render('view_expense_subcont');
    }


    // End Page Function    

    // Get Data Function    

    public function get_data_spk()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');


        $this->db->select('a.*, b.nm_sales, c.nm_paket as nama_project');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join('kons_master_konsultasi_header c', 'c.id_konsultasi_h = a.id_project', 'left');
        $this->db->join('kons_tr_kasbon_project_header d', 'd.id_spk_budgeting = a.id_spk_budgeting');
        $this->db->join('users e', 'e.id_user = d.created_by', 'left');
        $this->db->where('d.sts', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('c.nm_paket', $search['value'], 'both');
            $this->db->or_like('e.nm_lengkap', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_spk_penawaran');
        $this->db->order_by('a.create_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*, b.nm_sales, c.nm_paket as nama_project');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join('kons_master_konsultasi_header c', 'c.id_konsultasi_h = a.id_project', 'left');
        $this->db->join('kons_tr_kasbon_project_header d', 'd.id_spk_budgeting = a.id_spk_budgeting');
        $this->db->join('users e', 'e.id_user = d.created_by', 'left');
        $this->db->where('d.sts', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('c.nm_paket', $search['value'], 'both');
            $this->db->or_like('e.nm_lengkap', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_spk_penawaran');
        $this->db->order_by('a.create_date', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];

        $no = (0 + $start);
        foreach ($get_data->result() as $item) {

            $valid_show = 1;

            $count_ttl_kasbon_subcont = $this->db->select('SUM(a.total_pengajuan) as val')->get_where('kons_tr_kasbon_project_subcont a', array('a.id_spk_budgeting' => $item->id_spk_budgeting, 'a.sts' => 1))->row();

            $count_ttl_kasbon_akomodasi = $this->db->select('SUM(a.total_pengajuan) as val')->get_where('kons_tr_kasbon_project_akomodasi a', array('a.id_spk_budgeting' => $item->id_spk_budgeting, 'a.sts' => 1))->row();

            $count_ttl_kasbon_others = $this->db->select('SUM(a.total_pengajuan) as val')->get_where('kons_tr_kasbon_project_others a', array('a.id_spk_budgeting' => $item->id_spk_budgeting, 'a.sts' => 1))->row();

            $count_ttl_kasbon_lab = $this->db->select('SUM(a.total_pengajuan) as val')->get_where('kons_tr_kasbon_project_lab a', array('a.id_spk_budgeting' => $item->id_spk_budgeting, 'a.sts' => 1))->row();

            $count_ttl_kasbon_subcont_tenaga_ahli = $this->db->select('SUM(a.total_pengajuan) as val')->get_where('kons_tr_kasbon_project_subcont_tenaga_ahli a', array('a.id_spk_budgeting' => $item->id_spk_budgeting, 'a.sts' => 1))->row();

            $ttl_kasbon = ($count_ttl_kasbon_subcont->val + $count_ttl_kasbon_akomodasi->val + $count_ttl_kasbon_others->val + $count_ttl_kasbon_lab->val + $count_ttl_kasbon_subcont_tenaga_ahli->val);

            $ttl_expense = 0;

            $get_kasbon = $this->db->get_where('kons_tr_kasbon_project_header', array('id_spk_budgeting' => $item->id_spk_budgeting))->result();
            foreach ($get_kasbon as $item_kas) {
                $get_expense = $this->db->get_where('kons_tr_expense_report_project_header', array('id_header' => $item_kas->id, 'sts' => 1))->result();
                foreach ($get_expense as $item_exp) {
                    $ttl_expense += $item_exp->total_expense_report;
                }
            }

            if (($ttl_kasbon - $ttl_expense) <= 0 && $item->sts !== 1) {
                $valid_show = 0;
            }

            $this->db->select('a.*');
            $this->db->from('kons_tr_expense_report_project_header a');
            $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header', 'left');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('a.sts', 1);
            $this->db->group_by('a.id');
            $check_expense = $this->db->get()->result();

            if (count($check_expense) > 0) {
                $option = '<a href="' . base_url('expense_report_project/view/' . urlencode(str_replace('/', '|', $item->id_spk_budgeting))) . '" class="btn btn-sm btn-info" title="View Expense Report"><i class="fa fa-eye"></i></a>';
            }

            $this->db->select('a.id');
            $this->db->from('kons_tr_expense_report_project_header a');
            $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header', 'left');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('a.sts', null);
            $this->db->where('a.sts_req', null);
            $this->db->where('b.id IS NOT NULL');
            $check_expense_draft = $this->db->get()->num_rows();

            $count_kasbon = $this->db->get_where('kons_tr_kasbon_project_header', array('id_spk_budgeting' => $item->id_spk_budgeting, 'sts' => 1))->num_rows();

            $this->db->select('a.id as val');
            $this->db->from('kons_tr_expense_report_project_header a');
            $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('a.sts', 1);
            $count_expense = $this->db->get()->num_rows();

            if ($count_kasbon > $count_expense) {
                $valid_show = 1;
            } else {
                $valid_show = 0;
            }

            $option = '<a href="' . base_url('expense_report_project/add/' . urlencode(str_replace('/', '|', $item->id_spk_budgeting))) . '" class="btn btn-sm btn-primary" title="Add Expense Report"><i class="fa fa-arrow-up"></i></a>';

            if ($check_expense_draft > 0) {
                $option .= ' ' . '<button type="button" class="btn btn-sm btn-warning req_app" data-id_spk_budgeting="' . $item->id_spk_budgeting . '" title="Request Approval"><i class="fa fa-arrow-up"></i></button>';
            } else {
                $this->db->select('a.*');
                $this->db->from('kons_tr_expense_report_project_header a');
                $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header', 'left');
                $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
                $check_expense = $this->db->get()->result();

                if ($valid_show < 1) {
                    $option = '<a href="' . base_url('expense_report_project/view/' . urlencode(str_replace('/', '|', $item->id_spk_budgeting))) . '" class="btn btn-sm btn-info" title="View Expense Report"><i class="fa fa-eye"></i></a>';
                } else {
                    $option = '<a href="' . base_url('expense_report_project/add/' . urlencode(str_replace('/', '|', $item->id_spk_budgeting))) . '" class="btn btn-sm btn-primary" title="Add Expense Report"><i class="fa fa-arrow-up"></i></a>';
                }
            }



            $this->db->select('a.nm_lengkap');
            $this->db->from('users a');
            $this->db->join('kons_tr_kasbon_project_header b', 'b.created_by = a.id_user');
            $this->db->where('b.id_spk_penawaran', $item->id_spk_penawaran);
            $this->db->group_by('b.created_by');
            $get_pic_kasbon = $this->db->get()->result();

            $pic_kasbon = [];
            foreach ($get_pic_kasbon as $item_pic_kasbon) :
                $pic_kasbon[] = ucfirst($item_pic_kasbon->nm_lengkap);
            endforeach;

            $no++;
            $hasil[] = [
                'no' => $no,
                'id_spk_penawaran' => $item->id_spk_penawaran,
                'nm_customer' => $item->nm_customer,
                'pic_kasbon' => implode(', ', $pic_kasbon),
                'nm_project' => $item->nama_project,
                'option' => $option
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_subcont()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_subcont = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_subcont_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_subcont_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_subcont->result() as $item) {
            $check_expense = $this->db->select('a.*')->from('kons_tr_expense_report_project_header a')->where('a.id_header', $item->id)->get();

            $sts = '<button type="button" class="btn btn-sm btn-success">New</button>';
            if ($check_expense->num_rows() > 0) {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
                if ($check_expense->row()->sts_req == 1) {
                    $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
                } else {
                    if ($check_expense->row()->sts == 1) {
                        $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
                    }
                    if ($check_expense->row()->sts == 2 || ($check_expense->row()->reject_reason !== '' && $check_expense->row()->reject_reason !== null)) {
                        $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
                    }
                }
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            if ($check_expense->num_rows() > 0) {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/view_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

                if ($check_expense->row()->sts_req == null && $check_expense->row()->sts == null) {
                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="' . base_url('expense_report_project/edit_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-pencil"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Edit </span>
                        </div>
                    ';

                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="javascript:void();" class="btn btn-sm btn-danger del_expense" data-id_kasbon="' . $item->id . '" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-trash"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Delete </span>
                        </div>
                    ';
                }
            } else {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/add_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-success" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-plus"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Add Expense </span>
                    </div>
                ';
            }


            if ($item->sts !== '1' && $item->sts_req !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_kasbon_subcont" style="color: #000000" data-id="' . $item->id . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';

                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/edit_kasbon_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-pencil"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Edit </span>
                    </div>
                ';
            }



            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_aktifitas' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->tgl)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_subcont_all->num_rows(),
            'recordsFiltered' => $get_kasbon_subcont_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_akomodasi()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 2);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_akomodasi = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 2);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_akomodasi_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_akomodasi_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_akomodasi->result() as $item) {
            $check_expense = $this->db->select('a.*')->from('kons_tr_expense_report_project_header a')->where('a.id_header', $item->id)->get();

            $sts = '<button type="button" class="btn btn-sm btn-success">New</button>';
            if ($check_expense->num_rows() > 0) {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
                if ($check_expense->row()->sts_req == 1) {
                    $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
                } else {
                    if ($check_expense->row()->sts == 1) {
                        $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
                    }
                    if ($check_expense->row()->sts == 2 || ($check_expense->row()->reject_reason !== '' && $check_expense->row()->reject_reason !== null)) {
                        $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
                    }
                }
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            if ($check_expense->num_rows() > 0) {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/view_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

                if ($check_expense->row()->sts_req == null && $check_expense->row()->sts == null) {
                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="' . base_url('expense_report_project/edit_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-pencil"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Edit </span>
                        </div>
                    ';

                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="javascript:void();" class="btn btn-sm btn-danger del_expense" data-id_kasbon="' . $item->id . '" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-trash"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Delete </span>
                        </div>
                    ';
                }
            } else {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/add_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-success" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-plus"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Add Expense </span>
                    </div>
                ';
            }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->tgl)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_akomodasi_all->num_rows(),
            'recordsFiltered' => $get_kasbon_akomodasi_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_others()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 3);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_others = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 3);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_others_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_others_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_others->result() as $item) {
            $check_expense = $this->db->select('a.*')->from('kons_tr_expense_report_project_header a')->where('a.id_header', $item->id)->get();

            $sts = '<button type="button" class="btn btn-sm btn-success">New</button>';
            if ($check_expense->num_rows() > 0) {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
                if ($check_expense->row()->sts_req == 1) {
                    $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
                } else {
                    if ($check_expense->row()->sts == 1) {
                        $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
                    }
                    if ($check_expense->row()->sts == 2 || ($check_expense->row()->reject_reason !== '' && $check_expense->row()->reject_reason !== null)) {
                        $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
                    }
                }
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            if ($check_expense->num_rows() > 0) {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/view_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

                if ($check_expense->row()->sts_req == null && $check_expense->row()->sts == null) {
                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="' . base_url('expense_report_project/edit_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-pencil"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Edit </span>
                        </div>
                    ';

                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="javascript:void();" class="btn btn-sm btn-danger del_expense" data-id_kasbon="' . $item->id . '" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-trash"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Delete </span>
                        </div>
                    ';
                }
            } else {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/add_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-success" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-plus"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Add Expense </span>
                    </div>
                ';
            }




            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->created_date)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_others_all->num_rows(),
            'recordsFiltered' => $get_kasbon_others_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_lab()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 4);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_lab = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 4);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_lab_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_lab_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_lab->result() as $item) {
            $check_expense = $this->db->select('a.*')->from('kons_tr_expense_report_project_header a')->where('a.id_header', $item->id)->get();

            $sts = '<button type="button" class="btn btn-sm btn-success">New</button>';
            if ($check_expense->num_rows() > 0) {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
                if ($check_expense->row()->sts_req == 1) {
                    $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
                } else {
                    if ($check_expense->row()->sts == 1) {
                        $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
                    }
                    if ($check_expense->row()->sts == 2 || ($check_expense->row()->reject_reason !== '' && $check_expense->row()->reject_reason !== null)) {
                        $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
                    }
                }
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            if ($check_expense->num_rows() > 0) {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/view_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

                if ($check_expense->row()->sts_req == null && $check_expense->row()->sts == null) {
                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="' . base_url('expense_report_project/edit_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-pencil"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Edit </span>
                        </div>
                    ';

                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="javascript:void();" class="btn btn-sm btn-danger del_expense" data-id_kasbon="' . $item->id . '" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-trash"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Delete </span>
                        </div>
                    ';
                }
            } else {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/add_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-success" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-plus"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Add Expense </span>
                    </div>
                ';
            }




            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->created_date)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_lab_all->num_rows(),
            'recordsFiltered' => $get_kasbon_lab_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_subcont_tenaga_ahli()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 5);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_subcont_tenaga_ahli = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 5);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_subcont_tenaga_ahli_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_subcont_tenaga_ahli_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_subcont_tenaga_ahli->result() as $item) {
            $check_expense = $this->db->select('a.*')->from('kons_tr_expense_report_project_header a')->where('a.id_header', $item->id)->get();

            $sts = '<button type="button" class="btn btn-sm btn-success">New</button>';
            if ($check_expense->num_rows() > 0) {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
                if ($check_expense->row()->sts_req == 1) {
                    $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
                } else {
                    if ($check_expense->row()->sts == 1) {
                        $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
                    }
                    if ($check_expense->row()->sts == 2 || ($check_expense->row()->reject_reason !== '' && $check_expense->row()->reject_reason !== null)) {
                        $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
                    }
                }
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            if ($check_expense->num_rows() > 0) {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/view_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

                if ($check_expense->row()->sts_req == null && $check_expense->row()->sts == null) {
                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="' . base_url('expense_report_project/edit_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-pencil"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Edit </span>
                        </div>
                    ';

                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="javascript:void();" class="btn btn-sm btn-danger del_expense" data-id_kasbon="' . $item->id . '" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-trash"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Delete </span>
                        </div>
                    ';
                }
            } else {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/add_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-success" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-plus"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Add Expense </span>
                    </div>
                ';
            }




            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->created_date)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_subcont_tenaga_ahli_all->num_rows(),
            'recordsFiltered' => $get_kasbon_subcont_tenaga_ahli_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_subcont_perusahaan()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 6);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_subcont_perusahaan = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.deleted_at IS NULL');
        $this->db->where('a.tipe', 6);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_subcont_perusahaan_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_subcont_perusahaan_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_subcont_perusahaan->result() as $item) {
            $check_expense = $this->db->select('a.*')->from('kons_tr_expense_report_project_header a')->where('a.id_header', $item->id)->get();

            $sts = '<button type="button" class="btn btn-sm btn-success">New</button>';
            if ($check_expense->num_rows() > 0) {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
                if ($check_expense->row()->sts_req == 1) {
                    $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
                } else {
                    if ($check_expense->row()->sts == 1) {
                        $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
                    }
                    if ($check_expense->row()->sts == 2 || ($check_expense->row()->reject_reason !== '' && $check_expense->row()->reject_reason !== null)) {
                        $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
                    }
                }
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            if ($check_expense->num_rows() > 0) {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/view_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

                if ($check_expense->row()->sts_req == null && $check_expense->row()->sts == null) {
                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="' . base_url('expense_report_project/edit_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-pencil"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Edit </span>
                        </div>
                    ';

                    $option .= '
                        <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                            <a href="javascript:void();" class="btn btn-sm btn-danger del_expense" data-id_kasbon="' . $item->id . '" style="color: #000000">
                                <div class="col-12 dropdown-item">
                                <b>
                                    <i class="fa fa-trash"></i>
                                </b>
                                </div>
                            </a>
                            <span style="font-weight: 500"> Delete </span>
                        </div>
                    ';
                }
            } else {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('expense_report_project/add_expense_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-success" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-plus"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Add Expense </span>
                    </div>
                ';
            }




            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->created_date)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_subcont_perusahaan_all->num_rows(),
            'recordsFiltered' => $get_kasbon_subcont_perusahaan_all->num_rows(),
            'data' => $hasil
        ]);
    }

    // End Data Function

    // Update Data Function

    public function save_expense_report()
    {
        $post = $this->input->post();

        $this->db->trans_start();

        $id = $this->Expense_report_project_model->generate_id_expense_report_header('format_expense');

        $config['upload_path'] = './uploads/expense_report_project/'; //path folder
        $config['allowed_types'] = '*'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if ($this->upload->do_upload('kasbon_document')) {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/expense_report_project/' . $data_upload_po['file_name'];
        // }

        $data_bukti_penggunaan = [];
        $data_bukti_pengembalian = [];

        $files2 = $_FILES['bukti_pengembalian'];
        $file_count2 = count($files2['name']);

        $config2['upload_path'] = './uploads/bukti_pengembalian_expense_report/'; //path folder
        $config2['allowed_types'] = 'jpg|jpeg|png|pdf'; //type yang dapat diakses bisa anda sesuaikan
        $config2['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config2['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config2['remove_spaces'] = TRUE; // Remove spaces from the file name.

        // $this->load->library('upload', $config);
        $this->upload->initialize($config2);

        for ($i = 0; $i < $file_count2; $i++) {
            $_FILES['bukti_pengembalian']['name'] = $files2['name'][$i];
            $_FILES['bukti_pengembalian']['type'] = $files2['type'][$i];
            $_FILES['bukti_pengembalian']['tmp_name'] = $files2['tmp_name'][$i];
            $_FILES['bukti_pengembalian']['error'] = $files2['error'][$i];
            $_FILES['bukti_pengembalian']['size'] = $files2['size'][$i];

            // Reinitialize the upload class for each file
            if ($this->upload->do_upload('bukti_pengembalian')) {
                // Handle success (save file information or any other action)
                $data = $this->upload->data();

                $data_bukti_pengembalian[] = [
                    'id_header_expense' => $id,
                    'document_link' => 'uploads/bukti_pengembalian_expense_report/' . $data['file_name'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        $config3['upload_path'] = './uploads/bukti_penggunaan_expense/'; //path folder
        $config3['allowed_types'] = 'jpg|jpeg|png|pdf'; //type yang dapat diakses bisa anda sesuaikan
        $config3['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config3['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config3['remove_spaces'] = TRUE; // Remove spaces from the file name.

        // $this->load->library('upload', $config);
        $this->upload->initialize($config3);

        $files3 = $_FILES['bukti_penggunaan'];
        $file_count3 = count($files3['name']);

        for ($i = 0; $i < $file_count3; $i++) {
            $_FILES['bukti_penggunaan']['name'] = $files3['name'][$i];
            $_FILES['bukti_penggunaan']['type'] = $files3['type'][$i];
            $_FILES['bukti_penggunaan']['tmp_name'] = $files3['tmp_name'][$i];
            $_FILES['bukti_penggunaan']['error'] = $files3['error'][$i];
            $_FILES['bukti_penggunaan']['size'] = $files3['size'][$i];

            // Reinitialize the upload class for each file
            if ($this->upload->do_upload('bukti_penggunaan')) {
                // Handle success (save file information or any other action)
                $data2 = $this->upload->data();

                $data_bukti_penggunaan[] = [
                    'id_header_expense' => $id,
                    'upload_file' => 'uploads/bukti_penggunaan_expense/' . $data2['file_name'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        $data_insert_detail = [];

        $ttl_kasbon = 0;
        $ttl_expense_report = 0;

        if (isset($post['detail_subcont'])) {
            foreach ($post['detail_subcont'] as $item) {
                $qty_kasbon = str_replace(',', '', $item['qty_kasbon']);
                $nominal_kasbon = str_replace(',', '', $item['nominal_kasbon']);
                $total_kasbon = ($qty_kasbon * $nominal_kasbon);

                $qty_expense = str_replace(',', '', $item['qty_expense']);
                if ($qty_expense <= 0) {
                    $qty_expense = 0;
                }
                $nominal_expense = str_replace(',', '', $item['nominal_expense']);
                $total_expense = ($nominal_expense * $qty_expense);

                if ($qty_expense > 0 && $nominal_expense > 0) {
                    $data_insert_detail[] = [
                        'id_header_expense' => $id,
                        'id_header_kasbon' => $post['id_header'],
                        'id_spk_budgeting' => $post['id_spk_budgeting'],
                        'id_spk_penawaran' => $post['id_spk_penawaran'],
                        'id_penawaran' => $post['id_penawaran'],
                        'id_detail_kasbon' => $item['id_detail_kasbon'],
                        'tipe' => 1,
                        'qty_expense' => $qty_expense,
                        'nominal_expense' => $nominal_expense,
                        'keterangan' => $item['keterangan'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }

                $ttl_kasbon += ($total_kasbon);
                $ttl_expense_report += ($total_expense);
            }
        }

        // $data_insert_header = [
        //     'id' => $id,
        //     'id_header' => $post['id_header'],
        //     'total_expense_report' => $ttl_expense_report,
        //     'total_kasbon' => $ttl_kasbon,
        //     'selisih' => ($ttl_kasbon - $ttl_expense_report),
        //     'tipe' => $post['tipe'],
        //     'document_link' => $upload_po,
        //     'bank' => $post['kasbon_bank'],
        //     'bank_number' => $post['kasbon_bank_number'],
        //     'bank_account' => $post['kasbon_bank_account'],
        //     'created_by' => $this->auth->user_id(),
        //     'created_date' => date('Y-m-d H:i:s')
        // ];

        $selisih = ($ttl_kasbon - $ttl_expense_report);
        // if(($ttl_kasbon - $ttl_expense_report) <= 0) {
        //     $selisih = ($ttl_expense_report - $ttl_kasbon);
        // }

        $data_insert_header = [
            'id' => $id,
            'id_header' => $post['id_header'],
            'total_expense_report' => $ttl_expense_report,
            'total_kasbon' => $ttl_kasbon,
            'selisih' => $selisih,
            'tipe' => $post['tipe'],
            'document_link' => $upload_po,
            'id_bank' => $post['bank'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $insert_header = $this->db->insert('kons_tr_expense_report_project_header', $data_insert_header);
        if (!$insert_header) {
            $this->db->trans_rollback();

            print_r('error insert header : ' . $this->db->error($insert_header));
            exit;
        }

        $insert_detail = $this->db->insert_batch('kons_tr_expense_report_project_detail', $data_insert_detail);
        if (!$insert_detail) {
            $this->db->trans_rollback();

            print_r('error insert detail :' . $this->db->error($insert_detail));
            exit;
        }

        if (!empty($data_bukti_pengembalian)) {
            $insert_bukti_pengembalian = $this->db->insert_batch('kons_tr_expense_report_bukti_pengembalian', $data_bukti_pengembalian);
            if (!$insert_bukti_pengembalian) {
                $this->db->trans_rollback();

                print_r('error insert bukti pengembalian : ' . $this->db->error($insert_bukti_pengembalian));
                exit;
            }
        }

        // print_r($data_bukti_penggunaan);
        // exit;

        if (!empty($data_bukti_penggunaan)) {
            $insert_bukti_penggunaan = $this->db->insert_batch('kons_tr_bukti_penggunaan_expense', $data_bukti_penggunaan);
            if (!$insert_bukti_penggunaan) {
                $this->db->trans_rollback();

                print_r('error insert bukti penggunaan : ' . $this->db->last_query());
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Expense data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function update_expense_report_subcont()
    {
        $post = $this->input->post();

        $this->db->trans_start();

        $id = $post['id_expense'];

        $config['upload_path'] = './uploads/expense_report_project/'; //path folder
        $config['allowed_types'] = 'jpg|jpeg|png|pdf'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        $data_bukti_penggunaan = [];
        $data_bukti_pengembalian = [];

        $files2 = $_FILES['bukti_pengembalian'];
        $file_count2 = count($files2['name']);

        $config2['upload_path'] = './uploads/bukti_pengembalian_expense_report/'; //path folder
        $config2['allowed_types'] = 'jpg|jpeg|png|pdf'; //type yang dapat diakses bisa anda sesuaikan
        $config2['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config2['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config2['remove_spaces'] = TRUE; // Remove spaces from the file name.

        // $this->load->library('upload', $config);
        $this->upload->initialize($config2);

        for ($i = 0; $i < $file_count2; $i++) {
            $_FILES['bukti_pengembalian']['name'] = $files2['name'][$i];
            $_FILES['bukti_pengembalian']['type'] = $files2['type'][$i];
            $_FILES['bukti_pengembalian']['tmp_name'] = $files2['tmp_name'][$i];
            $_FILES['bukti_pengembalian']['error'] = $files2['error'][$i];
            $_FILES['bukti_pengembalian']['size'] = $files2['size'][$i];

            // Reinitialize the upload class for each file
            if ($this->upload->do_upload('bukti_pengembalian')) {
                // Handle success (save file information or any other action)
                $data = $this->upload->data();

                $data_bukti_pengembalian[] = [
                    'id_header_expense' => $id,
                    'document_link' => 'uploads/bukti_pengembalian_expense_report/' . $data['file_name'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        $files3 = $_FILES['bukti_penggunaan'];
        $file_count3 = count($files3['name']);

        $confi3['upload_path'] = './uploads/bukti_penggunaan_expense/'; //path folder
        $confi3['allowed_types'] = 'jpg|jpeg|png|pdf'; //type yang dapat diakses bisa anda sesuaikan
        $confi3['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $confi3['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $confi3['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $confi3);
        $this->upload->initialize($confi3);

        for ($i = 0; $i < $file_count3; $i++) {
            $_FILES['bukti_penggunaan']['name'] = $files3['name'][$i];
            $_FILES['bukti_penggunaan']['type'] = $files3['type'][$i];
            $_FILES['bukti_penggunaan']['tmp_name'] = $files3['tmp_name'][$i];
            $_FILES['bukti_penggunaan']['error'] = $files3['error'][$i];
            $_FILES['bukti_penggunaan']['size'] = $files3['size'][$i];

            // Reinitialize the upload class for each file
            if ($this->upload->do_upload('bukti_penggunaan')) {
                // Handle success (save file information or any other action)
                $data = $this->upload->data();

                $data_bukti_penggunaan[] = [
                    'id_header_expense' => $id,
                    'upload_file' => 'uploads/bukti_penggunaan_expense/' . $data['file_name'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        $data_insert_detail = [];

        $ttl_kasbon = 0;
        $ttl_expense_report = 0;

        if (isset($post['detail_subcont'])) {
            foreach ($post['detail_subcont'] as $item) {
                $qty_kasbon = str_replace(',', '', $item['qty_kasbon']);
                $nominal_kasbon = str_replace(',', '', $item['nominal_kasbon']);

                $qty_expense = str_replace(',', '', $item['qty_expense']);
                $nominal_expense = str_replace(',', '', $item['nominal_expense']);

                if ($qty_expense > 0 && $nominal_expense > 0) {
                    $data_insert_detail[] = [
                        'id_header_expense' => $id,
                        'id_header_kasbon' => $post['id_header'],
                        'id_spk_budgeting' => $post['id_spk_budgeting'],
                        'id_spk_penawaran' => $post['id_spk_penawaran'],
                        'id_penawaran' => $post['id_penawaran'],
                        'id_detail_kasbon' => $item['id_detail_kasbon'],
                        'tipe' => $post['tipe'],
                        'qty_expense' => $qty_expense,
                        'nominal_expense' => $nominal_expense,
                        'keterangan' => $item['keterangan'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }

                $ttl_kasbon += ($qty_kasbon * $nominal_kasbon);
                $ttl_expense_report += ($qty_expense * $nominal_expense);
            }
        }

        if (!empty($data_insert_detail)) {
            $this->db->delete('kons_tr_expense_report_project_detail', ['id_header_expense' => $post['id_expense']]);
        }
        // if (!empty($data_bukti_pengembalian)) {
        //     $this->db->delete('kons_tr_expense_report_bukti_pengembalian', ['id_header_expense' => $post['id_expense']]);
        // }

        $data_insert_header = [
            'total_expense_report' => $ttl_expense_report,
            'total_kasbon' => $ttl_kasbon,
            'selisih' => ($ttl_kasbon - $ttl_expense_report),
            'tipe' => $post['tipe'],
            'document_link' => $upload_po,
            'updated_by' => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s')
        ];
        if (empty($upload_po)) {
            // $data_insert_header = [
            //     'total_expense_report' => $ttl_expense_report,
            //     'total_kasbon' => $ttl_kasbon,
            //     'selisih' => ($ttl_kasbon - $ttl_expense_report),
            //     'tipe' => $post['tipe'],
            //     'bank' => $post['kasbon_bank'],
            //     'bank_number' => $post['kasbon_bank_number'],
            //     'bank_account' => $post['kasbon_bank_account'],
            //     'updated_by' => $this->auth->user_id(),
            //     'updated_date' => date('Y-m-d H:i:s')
            // ];

            $data_insert_header = [
                'total_expense_report' => $ttl_expense_report,
                'total_kasbon' => $ttl_kasbon,
                'selisih' => ($ttl_kasbon - $ttl_expense_report),
                'tipe' => $post['tipe'],
                'keterangan_kurang_bayar' => $post['keterangan_kurang_bayar'],
                'updated_by' => $this->auth->user_id(),
                'updated_date' => date('Y-m-d H:i:s')
            ];
        }

        $update_header = $this->db->update('kons_tr_expense_report_project_header', $data_insert_header, ['id' => $post['id_expense']]);
        if (!$update_header) {
            $this->db->trans_rollback();

            print_r('error update header : ' . $this->db->error($update_header));
            exit;
        }

        if (!empty($data_insert_detail)) {
            $insert_detail = $this->db->insert_batch('kons_tr_expense_report_project_detail', $data_insert_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r('error insert detail :' . $this->db->error($insert_detail));
                exit;
            }
        }

        if (!empty($data_bukti_pengembalian)) {
            $insert_bukti_pengembalian = $this->db->insert_batch('kons_tr_expense_report_bukti_pengembalian', $data_bukti_pengembalian);
            if (!$insert_bukti_pengembalian) {
                $this->db->trans_rollback();

                print_r('error insert bukti pengembalian : ' . $this->db->error($insert_bukti_pengembalian));
                exit;
            }
        }

        if (!empty($data_bukti_penggunaan)) {
            $insert_bukti_penggunaan = $this->db->insert_batch('kons_tr_bukti_penggunaan_expense', $data_bukti_penggunaan);
            if (!$insert_bukti_penggunaan) {
                $this->db->trans_rollback();

                print_r('error insert bukti penggunaan : ' . $this->db->error($insert_bukti_penggunaan));
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Expense data has been updated !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function req_approval()
    {
        $id = $this->input->post('id');

        $this->db->trans_begin();

        $update_req_approval = $this->db->update('kons_tr_expense_report_project_header', ['sts_req' => 1], ['id_header' => $id]);
        if (!$update_req_approval) {
            $this->db->trans_rollback();

            print_r($this->db->error($update_req_approval));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data status has been moved to requested approval !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function req_app()
    {
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');

        $this->db->trans_begin();

        $data_update = array();

        $this->db->select('a.id');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', null);
        $get_data_exp = $this->db->get()->result();

        foreach ($get_data_exp as $item) {
            $data_update[] = [
                'id' => $item->id,
                'sts_req' => 1
            ];
        }

        if (!empty($data_update)) {
            $update_sts = $this->db->update_batch('kons_tr_expense_report_project_header', $data_update, 'id');
            if (!$update_sts) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'Data has been changed to Request Approval !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $msg
        ]);
    }

    public function del_expense()
    {
        $id = $this->input->post('id');

        $this->db->trans_begin();

        $get_expense = $this->db->get_where('kons_tr_expense_report_project_header', array('id_header' => $id))->row();


        $this->db->delete('kons_tr_bukti_penggunaan_expense', array('id_header_expense' => $get_expense->id));
        $this->db->delete('kons_tr_expense_report_project_header', array('id_header' => $id));
        $this->db->delete('kons_tr_expense_report_project_detail', array('id_header_kasbon' => $id));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
        } else {
            $this->db->trans_commit();

            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function hitung_all_budget_on_process()
    {
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');

        $nilai_budget_subcont_on_process = 0;
        $nilai_budget_akomodasi_on_process = 0;
        $nilai_budget_others_on_process = 0;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_subcont');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('a.tipe', 1);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_subcont = $this->db->get()->row();

        $nilai_kasbon_on_proses = $get_expense_subcont->ttl_expense_subcont;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_akomodasi');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('a.tipe', 2);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_akomodasi = $this->db->get()->row();

        $nilai_kasbon_on_proses_akomodasi = $get_expense_akomodasi->ttl_expense_akomodasi;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_others');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('a.tipe', 3);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_others = $this->db->get()->row();

        $nilai_kasbon_on_proses_others = $get_expense_others->ttl_expense_others;

        $this->db->select('SUM(a.total_expense_report) as ttl_expense_lab');
        $this->db->from('kons_tr_expense_report_project_header a');
        $this->db->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
        $this->db->where('a.tipe', 4);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_start();
        $this->db->where('a.sts', null);
        $this->db->or_where('a.sts <>', 1);
        $this->db->group_end();
        $get_expense_lab = $this->db->get()->row();

        $nilai_kasbon_on_proses_lab = $get_expense_lab->ttl_expense_lab;

        echo json_encode([
            'nilai_budget_subcont' => $nilai_kasbon_on_proses,
            'nilai_budget_akomodasi' => $nilai_kasbon_on_proses_akomodasi,
            'nilai_budget_others' => $nilai_kasbon_on_proses_others,
            'nilai_budget_lab' => $nilai_kasbon_on_proses_lab
        ]);
    }

    public function del_bukti_penggunaan()
    {
        $id = $this->input->post('id');

        $this->db->trans_begin();

        $del_bukti_penggunaan = $this->db->delete('kons_tr_bukti_penggunaan_expense', array('id' => $id));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
        } else {
            $this->db->trans_commit();

            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function del_bukti_pengembalian()
    {
        $id = $this->input->post('id');

        $this->db->trans_begin();

        $del_bukti_penggunaan = $this->db->delete('kons_tr_expense_report_bukti_pengembalian', array('id' => $id));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
        } else {
            $this->db->trans_commit();

            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function set_jurnal_expense()
    {
        $this->Expense_report_project_model->set_jurnal_expense();
    }

    // End Update Data Function
}
