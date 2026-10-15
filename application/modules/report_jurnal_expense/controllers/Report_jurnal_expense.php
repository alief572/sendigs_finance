<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_jurnal_expense extends Admin_Controller
{
    protected $viewPermission = 'Report_Jurnal_Expense.View';

    public function __construct()
    {
        parent::__construct();
        $this->db->db_debug = false;
        $this->load->model('report_jurnal_expense/Report_jurnal_expense_model');
        $this->template->title('Report Jurnal Expense');
        $this->template->page_icon('fa fa-book');
    }

    public function index()
    {
        $this->beginAudit(__FUNCTION__);
        $this->auth->restrict($this->viewPermission);
        try {
            $this->template->set(['list_no_transaksi'=>$this->Report_jurnal_expense_model->get_no_payment_jurnal(), 'list_company'=>$this->Report_jurnal_expense_model->get_company()]);
            $this->template->render('index');
            $this->finishAudit(true, ['outcome'=>'rendered']);
        } catch (Throwable $error) {
            $this->finishAudit(false, ['reason'=>'query_or_render_failure']);
            throw $error;
        }
    }

    public function get_data_jurnal()
    {
        $this->beginAudit(__FUNCTION__);
        $draw = $this->input->post('draw');
        $response = ['draw'=>is_scalar($draw) ? (int) $draw : 0, 'recordsTotal'=>0, 'recordsFiltered'=>0, 'data'=>[]];
        $ok = false;
        $reason = 'permission_denied';
        if (has_permission($this->viewPermission)) {
            try { $response = $this->Report_jurnal_expense_model->get_data_jurnal(); $ok = true; }
            catch (Throwable $error) { $reason = 'query_failure'; }
        }
        if (!$ok) { $response['error'] = $reason === 'permission_denied' ? 'Anda tidak memiliki hak akses.' : 'Gagal memuat data jurnal.'; }
        $this->finishAudit($ok, ['row_count'=>count($response['data']), $ok ? 'outcome' : 'reason'=>$ok ? 'listed' : $reason]);
        echo json_encode($response);
    }

    public function view_jurnal()
    {
        $this->beginAudit(__FUNCTION__);
        if (!has_permission($this->viewPermission)) {
            $this->output->set_status_header(403);
            $this->finishAudit(false, ['reason'=>'permission_denied']);
            return;
        }
        try {
            $id = $this->input->post('id');
            $id = is_scalar($id) && preg_match('/^[1-9]\d*$/D', (string) $id) ? (int) $id : 0;
            $rows = $this->Report_jurnal_expense_model->get_detail_jurnal($id);
            $html = $this->load->view('view_jurnal', ['data_jurnal'=>$rows], true);
            $this->finishAudit(true, ['row_count'=>count($rows), 'outcome'=>'viewed']);
            echo $html;
        } catch (Throwable $error) {
            $this->output->set_status_header(400);
            $this->finishAudit(false, ['reason'=>'invalid_or_unavailable_journal']);
            echo json_encode(['status'=>0, 'msg'=>'Gagal memuat rincian jurnal posted.']);
        }
    }

    public function export_excel() { $this->renderExport(__FUNCTION__); }
    public function export_jurnal() { $this->renderExport(__FUNCTION__); }

    private function renderExport($method)
    {
        $this->beginAudit($method);
        if (!has_permission($this->viewPermission)) {
            $this->output->set_status_header(403);
            $this->finishAudit(false, ['reason'=>'permission_denied']);
            return;
        }
        try {
            $values = $this->input->get();
            $filter = $this->Report_jurnal_expense_model->filters(is_array($values) ? $values : []);
            $rows = $this->Report_jurnal_expense_model->get_list_jurnal($filter);
            $html = $this->load->view('export_excel', ['list_jurnal'=>$rows], true);
            $this->finishAudit(true, ['row_count'=>count($rows), 'outcome'=>'exported']);
            echo $html;
        } catch (Throwable $error) {
            $this->output->set_status_header(400);
            $this->finishAudit(false, ['reason'=>'query_or_export_failure']);
            echo json_encode(['status'=>0, 'msg'=>'Gagal mengekspor jurnal.']);
        }
    }

    private function beginAudit($method)
    {
        $this->load->library('Module_audit_recorder');
        $this->module_audit_recorder->begin('report_jurnal_expense', $method, [], ['row_count','reason','outcome']);
    }
    private function finishAudit($ok, $data)
    {
        try { $this->module_audit_recorder->complete($ok ? 'Report Jurnal Expense berhasil' : 'Report Jurnal Expense gagal', $ok ? 1 : 0, ['data'=>$data, 'sql'=>null]); }
        catch (Throwable $error) { log_message('error', 'Report Jurnal Expense: audit writer tidak tersedia.'); }
    }
}
