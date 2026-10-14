<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Jurnal_expense extends Admin_Controller
{
    protected $viewPermission = 'Jurnal_Expense.View';
    protected $managePermission = 'Jurnal_Expense.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->db->db_debug = false;
        $this->load->model(['jurnal_expense/Jurnal_expense_model', 'jurnal_expense/Jurnal_expense_posting_model', 'jurnal_payment/Jurnal_payment_nomor_model']);
        $this->template->title('Jurnal Expense');
        $this->template->page_icon('fa fa-building-o');
    }

    public function index()
    {
        $this->beginAudit(__FUNCTION__);
        $this->auth->restrict($this->viewPermission);
        try {
            $this->template->set(['list_no_transaksi'=>$this->Jurnal_expense_model->get_no_payment_jurnal(), 'list_company'=>$this->Jurnal_expense_model->get_company()]);
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
            try {
                $response = $this->Jurnal_expense_model->get_data_jurnal();
                $ok = true;
            } catch (Throwable $error) { $reason = 'query_failure'; }
        }
        if (!$ok) { $response['error'] = $reason === 'permission_denied' ? 'Anda tidak memiliki hak akses.' : 'Gagal memuat data jurnal.'; }
        $this->finishAudit($ok, ['row_count'=>count($response['data']), $ok ? 'outcome' : 'reason'=>$ok ? 'listed' : $reason]);
        echo json_encode($response);
    }

    public function add_jurnal()
    {
        $this->beginAudit(__FUNCTION__);
        if (!has_permission($this->managePermission) || $this->input->method(true) !== 'POST') {
            $this->output->set_status_header(403);
            $this->finishAudit(false, ['reason'=>'permission_or_method_denied']);
            return;
        }
        try {
            $rows = $this->Jurnal_expense_model->get_pending_group($this->journalId());
            $this->load->view('preview', ['rows'=>$rows, 'id'=>$this->journalId()]);
            $this->finishAudit(true, ['row_count'=>count($rows), 'outcome'=>'previewed']);
        } catch (Throwable $error) {
            $this->output->set_status_header(400);
            $this->finishAudit(false, ['reason'=>'invalid_or_unavailable_journal']);
        }
    }

    public function save_posting_jurnal()
    {
        $this->beginAudit(__FUNCTION__);
        if (!has_permission($this->managePermission) || $this->input->method(true) !== 'POST') {
            $response = ['save'=>0, 'msg'=>'Anda tidak memiliki hak untuk memposting jurnal.'];
            $this->finishAudit(false, ['reason'=>'permission_or_method_denied']);
        } else {
            $response = $this->Jurnal_expense_posting_model->post_journal($this->journalId(), $this->auth->user_id());
            $this->finishAudit($response['save'] === 1, ['row_count'=>$response['row_count'] ?? 0, 'cleanup_ok'=>$response['cleanup_ok'],
                $response['save'] === 1 ? 'outcome' : 'reason'=>$response['save'] === 1 ? 'posted' : 'posting_failed']);
        }
        unset($response['row_count'], $response['cleanup_ok']);
        echo json_encode($response);
    }

    public function export_jurnal()
    {
        $this->beginAudit(__FUNCTION__);
        if (!has_permission($this->viewPermission)) {
            $this->output->set_status_header(403);
            $this->finishAudit(false, ['reason'=>'permission_denied']);
            return;
        }
        try {
            $values = $this->input->get();
            $rows = $this->Jurnal_expense_model->get_list_jurnal($this->Jurnal_expense_model->filters(is_array($values) ? $values : []));
            $this->load->view('export_excel', ['list_jurnal'=>$rows]);
            $this->finishAudit(true, ['row_count'=>count($rows), 'outcome'=>'exported']);
        } catch (Throwable $error) {
            $this->output->set_status_header(400);
            $this->finishAudit(false, ['reason'=>'query_or_export_failure']);
            echo json_encode(['status'=>0, 'msg'=>'Gagal mengekspor jurnal.']);
        }
    }

    private function journalId()
    {
        $id = $this->input->post('id');
        return is_scalar($id) && preg_match('/^[1-9]\d*$/D', (string) $id) ? (int) $id : 0;
    }

    private function beginAudit($method)
    {
        $this->load->library('Module_audit_recorder');
        $this->module_audit_recorder->begin('jurnal_expense', $method, [], ['row_count','cleanup_ok','reason','outcome']);
    }
    private function finishAudit($ok, $data)
    {
        try { $this->module_audit_recorder->complete($ok ? 'Jurnal Expense berhasil' : 'Jurnal Expense gagal', $ok ? 1 : 0, ['data'=>$data, 'sql'=>null]); }
        catch (Throwable $error) { log_message('error', 'Jurnal Expense: audit writer tidak tersedia.'); }
    }
}
