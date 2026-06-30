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
        $this->auth->restrict($this->viewPermission);

        $data = [
            'has_manage' => has_permission($this->managePermission),
        ];

        $this->template->set($data);
        $this->template->render('index');
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
        $this->auth->restrict($this->viewPermission);

        $filters = [
            'company' => $this->input->post('company'),
            'status'  => $this->input->post('status'),
        ];

        $result = $this->Petty_cash_vuca_sustain_model->get_server_side_data(
            $this->input->post(),
            $filters
        );

        echo json_encode($result);
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
        $this->auth->restrict($this->viewPermission);

        $data = $this->Petty_cash_vuca_sustain_model->get_payment_hutang($id);

        if (!$data) {
            $this->session->set_flashdata('message', 'Data tidak ditemukan.');
            redirect('petty_cash_vuca_sustain');
        }

        $this->template->set('record', $data);
        $this->template->title('Detail Payment Hutang');
        $this->template->render('view');
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
        $this->auth->restrict($this->viewPermission);

        $record = $this->Petty_cash_vuca_sustain_model->get_payment_hutang($id);

        if (!$record) {
            Template::set_message('Data tidak ditemukan.', 'error');
            redirect('petty_cash_vuca_sustain');
            return;
        }

        // Prepare data for view
        $data = [
            'record' => $record,
        ];

        // Load view directly as standalone HTML page (browser print)
        $this->load->view('print', $data);
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
        $this->auth->restrict($this->managePermission);

        $record = $this->Petty_cash_vuca_sustain_model->get_payment_hutang($id);

        if (!$record) {
            $this->session->set_flashdata('message', 'Data tidak ditemukan.');
            redirect('petty_cash_vuca_sustain');
            return;
        }

        // Only draft records can be processed
        if ($record->header->status !== 'draft') {
            $this->session->set_flashdata('message', 'Hanya record berstatus "draft" yang dapat diproses.');
            redirect('petty_cash_vuca_sustain');
            return;
        }

        $this->template->set('record', $record);
        $this->template->title('Konfirmasi Payment Hutang');
        $this->template->render('confirm_payment');
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
        $this->auth->restrict($this->managePermission);

        // Validasi request harus POST/AJAX (Requirement 4.4)
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                'status'  => false,
                'message' => 'Invalid request method.',
            ]);
            return;
        }

        $user_id = $this->auth->user_id();
        $result  = $this->Petty_cash_vuca_sustain_model->process_payment_hutang($id, $user_id);

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
