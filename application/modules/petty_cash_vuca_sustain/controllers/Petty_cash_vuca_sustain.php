<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Petty Cash VUCA & Sustain Controller
 *
 * Mengelola tracking pembayaran hutang inter-company
 * antara perusahaan VUCA/SUSTAIN terhadap STM.
 * Data masuk otomatis dari modul expense_petty_cash
 * saat pelaporan untuk company VUCA/SUSTAIN di-approve.
 *
 * @author Sendigs Finance
 * @copyright Copyright (c) 2025
 */
class Petty_cash_vuca_sustain extends Admin_Controller
{
    // Permission constants
    protected $viewPermission   = 'Petty_Cash_Vuca_Sustain.View';
    protected $managePermission = 'Petty_Cash_Vuca_Sustain.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('petty_cash_vuca_sustain/Petty_cash_vuca_sustain_model');
        $this->template->title('Petty Cash VUCA & Sustain');
        $this->template->page_icon('fa fa-exchange');
    }

    // =========================================================================
    // AUDIT TRAIL
    // =========================================================================

    private function beginAudit($method, $data = array(), $keys = array())
    {
        if (isset($this->load) && method_exists($this->load, 'library')) {
            $this->load->library('Module_audit_recorder');
        }
        if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'begin')) {
            return $this->module_audit_recorder->begin('petty_cash_vuca_sustain', $method,
                array('data' => $this->auditSafeData($data)),
                array_unique(array_merge(array_keys($data), $keys, array('reason', 'outcome'))));
        }
        return false;
    }

    private function completeAudit($message, $status, $data = array())
    {
        if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'complete')) {
            return $this->module_audit_recorder->complete($message, $status,
                array('data' => $this->auditSafeData($data), 'sql' => null));
        }
        return false;
    }

    private function auditSafeData($data)
    {
        $safe = array();
        foreach ($data as $key => $value) {
            if ($key === 'id' && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', (string) $value))) { continue; }
            if (is_scalar($value) || $value === null) {
                $safe[$key] = is_string($value) ? mb_substr($value, 0, 512, 'UTF-8') : $value;
            }
        }
        return $safe;
    }

    // Catat penolakan akses sebelum restrict() mengalihkan/menghentikan request.
    private function auditRestrict($permission)
    {
        if (!has_permission($permission)) {
            $this->completeAudit('Akses petty cash VUCA & Sustain ditolak', 0, array('reason' => 'permission_denied', 'outcome' => 'denied'));
        }
        $this->auth->restrict($permission);
    }

    // =========================================================================
    // INDEX & DATA
    // =========================================================================

    /**
     * Halaman index dengan DataTables server-side
     *
     * Menampilkan daftar payment hutang dengan filter Company dan Status.
     * Permission flags dikirim ke view untuk kontrol tombol aksi.
     *
     * @return void
     */
    public function index()
    {
        $this->beginAudit(__FUNCTION__, array(), array('outcome'));
        $this->auditRestrict($this->viewPermission);

        $data = [
            'has_manage' => has_permission($this->managePermission),
        ];

        $this->template->set($data);
        $this->template->render('index');
        $this->completeAudit('Membuka daftar payment hutang petty cash VUCA & Sustain', 1, array('outcome' => 'rendered'));
    }

    /**
     * Server-side DataTables endpoint (POST, AJAX)
     *
     * Menerima parameter DataTables (draw, start, length, search, order)
     * beserta filter tambahan (company, status) dan mengembalikan JSON.
     *
     * @return void Echoes JSON {draw, recordsTotal, recordsFiltered, data}
     */
    public function get_data()
    {
        $this->beginAudit(__FUNCTION__, array(), array('row_count', 'outcome'));
        $this->auditRestrict($this->viewPermission);

        $filters = [
            'company' => $this->input->post('company'),
            'status'  => $this->input->post('status'),
        ];

        $result = $this->Petty_cash_vuca_sustain_model->get_server_side_data(
            $this->input->post(),
            $filters
        );

        echo json_encode($result);
        $this->completeAudit('Data payment hutang petty cash berhasil diambil', 1, array('row_count' => isset($result['data']) && is_array($result['data']) ? count($result['data']) : 0, 'outcome' => 'rendered'));
    }

    // =========================================================================
    // VIEW & PRINT
    // =========================================================================

    /**
     * Halaman detail payment hutang
     *
     * @param int $id ID record tr_petty_cash_vuca_sustain
     * @return void
     */
    public function view($id)
    {
        $this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'outcome'));
        $this->auditRestrict($this->viewPermission);

        $data = $this->Petty_cash_vuca_sustain_model->get_payment_hutang($id);

        if (!$data) {
            $this->session->set_flashdata('message', 'Data tidak ditemukan.');
            $this->completeAudit('Lihat payment hutang: data tidak ditemukan', 0, array('id' => $id, 'outcome' => 'not_found'));
            redirect('petty_cash_vuca_sustain');
        }

        $this->template->set('record', $data);
        $this->template->title('Detail Payment Hutang');
        $this->template->render('view');
        $this->completeAudit('Melihat payment hutang petty cash', 1, array('id' => $id, 'outcome' => 'rendered'));
    }

    /**
     * Print dokumen payment hutang (HTML browser print)
     *
     * Load data payment hutang, render standalone HTML page,
     * auto-trigger window.print() di browser.
     *
     * @param int $id ID record tr_petty_cash_vuca_sustain
     * @return void
     */
    public function print_pdf($id)
    {
        $this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'outcome'));
        $this->auditRestrict($this->viewPermission);

        $record = $this->Petty_cash_vuca_sustain_model->get_payment_hutang($id);

        if (!$record) {
            Template::set_message('Data tidak ditemukan.', 'error');
            $this->completeAudit('Cetak payment hutang: data tidak ditemukan', 0, array('id' => $id, 'outcome' => 'not_found'));
            redirect('petty_cash_vuca_sustain');
            return;
        }

        // Prepare data for view
        $data = [
            'record' => $record,
        ];

        // Load view directly as standalone HTML page (browser print)
        $this->load->view('print', $data);
        $this->completeAudit('Mencetak payment hutang petty cash', 1, array('id' => $id, 'outcome' => 'rendered'));
    }

    // =========================================================================
    // PAYMENT HUTANG
    // =========================================================================

    /**
     * Halaman konfirmasi sebelum proses Payment Hutang
     *
     * Menampilkan detail lengkap payment hutang (pencatatan + jurnal preview)
     * sebelum user memproses.
     *
     * @param int $id ID record tr_petty_cash_vuca_sustain
     * @return void
     */
    public function confirm_payment($id)
    {
        $this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'current_status', 'reason', 'outcome'));
        $this->auditRestrict($this->managePermission);

        $record = $this->Petty_cash_vuca_sustain_model->get_payment_hutang($id);

        if (!$record) {
            $this->session->set_flashdata('message', 'Data tidak ditemukan.');
            $this->completeAudit('Konfirmasi payment hutang: data tidak ditemukan', 0, array('id' => $id, 'outcome' => 'not_found'));
            redirect('petty_cash_vuca_sustain');
            return;
        }

        // Only draft records can be processed
        if ($record->header->status !== 'draft') {
            $this->session->set_flashdata('message', 'Hanya record berstatus "draft" yang dapat diproses.');
            $this->completeAudit('Konfirmasi payment hutang ditolak: bukan draft', 0, array('id' => $id, 'current_status' => $record->header->status, 'reason' => 'not_draft', 'outcome' => 'redirected'));
            redirect('petty_cash_vuca_sustain');
            return;
        }

        $this->template->set('record', $record);
        $this->template->title('Konfirmasi Payment Hutang');
        $this->template->render('confirm_payment');
        $this->completeAudit('Membuka konfirmasi payment hutang petty cash', 1, array('id' => $id, 'outcome' => 'rendered'));
    }

    /**
     * Proses kirim ke request_payment (POST, AJAX)
     *
     * Memproses pembayaran hutang: insert ke tabel request_payment
     * dan update status record menjadi 'waiting payment'.
     *
     * @param int $id ID record tr_petty_cash_vuca_sustain
     * @return void Echoes JSON {status, message}
     */
    public function payment_hutang($id)
    {
        $this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'reason', 'outcome'));
        $this->auditRestrict($this->managePermission);

        // Validasi request harus POST/AJAX (Requirement 4.4)
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                'status'  => false,
                'message' => 'Invalid request method.',
            ]);
            $this->completeAudit('Payment hutang petty cash ditolak: bukan request AJAX', 0, array('id' => $id, 'reason' => 'not_ajax', 'outcome' => 'invalid_request'));
            return;
        }

        $user_id = $this->auth->user_id();
        $result  = $this->Petty_cash_vuca_sustain_model->process_payment_hutang($id, $user_id);

        $this->completeAudit($result ? 'Payment hutang petty cash diproses' : 'Payment hutang petty cash gagal diproses', $result ? 1 : 0, array('id' => $id, 'outcome' => $result ? 'processed' : 'process_failed'));
        if ($result) {
            echo json_encode([
                'status'  => true,
                'message' => 'Payment Hutang berhasil diproses.',
            ]);
        } else {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal memproses Payment Hutang. Pastikan status masih draft.',
            ]);
        }
    }
}
/* End of file Petty_cash_vuca_sustain.php */
