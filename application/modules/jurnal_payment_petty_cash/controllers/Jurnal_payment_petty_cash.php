<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Jurnal_payment_petty_cash extends Admin_Controller
{
    protected $viewPermission   = 'Jurnal_Payment_Petty_Cash.View';
    protected $addPermission    = 'Jurnal_Payment_Petty_Cash.Add';
    protected $managePermission = 'Jurnal_Payment_Petty_Cash.Manage';

    protected $accounting_stm;
    protected $accounting_vuca;
    protected $accounting_sustain;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Jurnal_payment_petty_cash/Jurnal_payment_petty_cash_model',
            'Jurnal_payment_petty_cash/Jurnal_payment_petty_cash_nomor_model'
        ));
        $this->template->title('Jurnal Payment Petty Cash');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->accounting_stm     = $this->load->database('accounting_stm', true);
        $this->accounting_vuca    = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
    }

    /**
     * Halaman daftar jurnal petty cash (DataTables server-side)
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $companies = $this->Jurnal_payment_petty_cash_model->get_company_filter();

        $this->template->set('companies', $companies);
        $this->template->set('addPermission', has_permission($this->addPermission));
        $this->template->title('Jurnal Payment Petty Cash');
        $this->template->render('index');
    }

    /**
     * DataTables server-side endpoint untuk daftar jurnal
     */
    public function get_data_jurnal()
    {
        $this->auth->restrict($this->viewPermission);

        echo json_encode($this->Jurnal_payment_petty_cash_model->get_server_side_data($this->input->post()));
    }

    /**
     * Load detail jurnal untuk modal view
     * Accept POST (id atau no_transaksi), render partial view, return JSON
     */
    public function get_detail_jurnal()
    {
        $this->auth->restrict($this->viewPermission);

        $id            = $this->input->post('id');
        $no_transaksi  = $this->input->post('no_transaksi');
        $jenis_transaksi = '';

        // If only id is provided, look up the no_transaksi and jenis_transaksi from the record
        if (empty($no_transaksi) && !empty($id)) {
            $row = $this->db->select('no_transaksi, jenis_transaksi')
                ->from('tr_jurnal')
                ->where('id', $id)
                ->get()
                ->row();

            if ($row) {
                $no_transaksi = $row->no_transaksi;
                $jenis_transaksi = $row->jenis_transaksi;
            }
        }

        // If still no no_transaksi found, return empty response
        if (empty($no_transaksi)) {
            echo json_encode([
                'html'       => '<p class="text-center">Tidak ada data jurnal</p>',
                'is_balance' => false
            ]);
            return;
        }

        // Get detail rows for this transaction (use jenis_transaksi if known, else fetch all for this no_transaksi)
        $rows = $this->Jurnal_payment_petty_cash_model->get_detail_by_transaksi($no_transaksi, $jenis_transaksi ?: null);

        // Validate balance
        $is_balance = $this->Jurnal_payment_petty_cash_model->validate_balance($rows);

        // Calculate totals
        $total_debit  = 0;
        $total_kredit = 0;
        foreach ($rows as $row) {
            $total_debit  += (float) $row->debit;
            $total_kredit += (float) $row->kredit;
        }

        // Prepare data for the view
        $data = [
            'rows'             => $rows,
            'is_balance'       => $is_balance,
            'total_debit'      => $total_debit,
            'total_kredit'     => $total_kredit,
            'no_transaksi'     => $no_transaksi,
            'jenis_transaksi'  => $jenis_transaksi ?: (!empty($rows) ? $rows[0]->jenis_transaksi : 'Petty Cash')
        ];

        // Render the partial view as a string
        $html = $this->load->view('modal_detail', $data, TRUE);

        echo json_encode([
            'html'       => $html,
            'is_balance' => $is_balance
        ]);
    }

    /**
     * Posting jurnal ke database akuntansi
     * Mendukung tiga skenario berdasarkan id_company:
     * - id_company = '5' (STM): posting langsung ke DBACC_STM
     * - id_company = '4' (VUCA): inter-company posting ke DBACC_VUCA + DBACC_STM
     * - id_company = '6' (SUSTAIN): inter-company posting ke DBACC_SUSTAIN + DBACC_STM
     *
     * Skenario refill dideteksi via parameter jenis_posting = 'refill'
     * atau otomatis dari keterangan baris staging yang mengandung kata "Refill".
     */
    public function save_posting_jurnal()
    {
        if (!has_permission($this->addPermission)) {
            echo json_encode(['status' => 0, 'msg' => 'Anda tidak memiliki akses untuk posting jurnal']);
            return;
        }

        $no_transaksi = $this->input->post('no_transaksi');
        $jenis_transaksi = $this->input->post('jenis_transaksi') ?: 'Petty Cash';

        // Get all staging rows for this transaction
        $rows = $this->Jurnal_payment_petty_cash_model->get_detail_by_transaksi($no_transaksi, $jenis_transaksi);

        // Validate: data exists and status is unposted
        if (empty($rows)) {
            echo json_encode(['status' => 0, 'msg' => 'Data tidak valid atau sudah diposting']);
            return;
        }

        // Validate balance
        if (!$this->Jurnal_payment_petty_cash_model->validate_balance($rows)) {
            echo json_encode(['status' => 0, 'msg' => 'Jurnal tidak balance. Total Debit harus sama dengan Total Kredit']);
            return;
        }

        // Determine company from first row
        $id_company = $rows[0]->id_company;
        $nm_company = $rows[0]->nm_company;

        // Calculate totals
        $total_debit = 0;
        foreach ($rows as $row) {
            $total_debit += (float) $row->debit;
        }

        // Prepare header
        $jurnal_header = (object) [
            'tgl'      => $rows[0]->tgl_jurnal,
            'jml'      => $total_debit,
            'no_reff'  => $no_transaksi,
            'user_id'  => $this->auth->user_id()
        ];

        // --- Detect refill scenario ---
        // Explicit: POST parameter jenis_posting = 'refill'
        // Implicit: any row's keterangan contains "Refill" (case-insensitive)
        $jenis_posting = $this->input->post('jenis_posting');
        $is_refill = ($jenis_posting === 'refill');

        if (!$is_refill) {
            $is_refill = $this->_detect_refill_from_rows($rows);
        }

        if ($is_refill) {
            // === Refill Posting (single target DB based on company) ===
            $this->_post_refill($nm_company, $jurnal_header, $rows, $no_transaksi, $jenis_transaksi);
            return;
        }

        // Branch logic based on id_company (regular expense posting)
        if ($nm_company == 'STM') {
            // === STM Internal Posting ===
            $this->_post_stm($jurnal_header, $rows, $no_transaksi, $jenis_transaksi);
        } elseif ($nm_company == 'VUCA' || $nm_company == 'SUSTAIN') {
            // === Inter-Company Posting (VUCA or SUSTAIN) ===
            $this->_post_intercompany($id_company, $jurnal_header, $rows, $no_transaksi, $jenis_transaksi);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Company tidak dikenali untuk posting jurnal']);
            return;
        }
    }

    /**
     * Detect refill transaction from staging row data
     * Checks if any row's keterangan contains "Refill" (case-insensitive)
     *
     * @param array $rows Array of staging row objects
     * @return bool True if refill detected
     */
    private function _detect_refill_from_rows($rows)
    {
        foreach ($rows as $row) {
            if (isset($row->keterangan) && stripos($row->keterangan, 'Refill') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Internal: Posting jurnal refill (single target DB based on id_company)
     *
     * Refill scenarios:
     * - Refill STM (id_company='5') → target 'accounting_stm'
     * - Refill/Pembayaran hutang VUCA (id_company='4') → target 'accounting_vuca'
     * - Refill/Pembayaran hutang SUSTAIN (id_company='6') → target 'accounting_sustain'
     *
     * @param string $id_company Company identifier
     * @param object $jurnal_header Header data
     * @param array  $rows Detail rows from staging
     * @param string $no_transaksi Transaction number
     * @param string $jenis_transaksi Transaction type
     */
    private function _post_refill($nm_company, $jurnal_header, $rows, $no_transaksi, $jenis_transaksi)
    {
        // Determine target database based on nm_company
        switch ($nm_company) {
            case 'STM':
                $target_db = 'accounting_stm';
                $company_label = 'STM';
                break;
            case 'VUCA':
                $target_db = 'accounting_vuca';
                $company_label = 'VUCA';
                break;
            case 'SUSTAIN':
                $target_db = 'accounting_sustain';
                $company_label = 'SUSTAIN';
                break;
            default:
                echo json_encode(['status' => 0, 'msg' => 'Company tidak dikenali untuk posting refill']);
                return;
        }

        $this->db->trans_begin();
        $this->Jurnal_payment_petty_cash_model->begin_transaction($target_db);

        // Generate BUK number for target DB
        $nomor_buk = $this->Jurnal_payment_petty_cash_nomor_model->get_nomor_buk('101', $target_db);
        if (!$nomor_buk) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($target_db);
            echo json_encode(['status' => 0, 'msg' => 'Gagal generate nomor BUK untuk refill ' . $company_label]);
            return;
        }

        // Post refill jurnal to target database
        $post_result = $this->Jurnal_payment_petty_cash_model->post_jurnal_refill($jurnal_header, $rows, $nomor_buk, $target_db);
        if (!$post_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($target_db);
            echo json_encode(['status' => 0, 'msg' => 'Gagal insert jurnal refill ke database akuntansi ' . $company_label]);
            return;
        }

        // Increment BUK counter on target DB
        $increment_result = $this->Jurnal_payment_petty_cash_nomor_model->increment_nobuk('101', $target_db);
        if (!$increment_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($target_db);
            echo json_encode(['status' => 0, 'msg' => 'Gagal update counter BUK refill di ' . $company_label]);
            return;
        }

        // Update staging status to posted
        $update_result = $this->Jurnal_payment_petty_cash_model->update_status_posted($no_transaksi, $jenis_transaksi);
        if (!$update_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($target_db);
            echo json_encode(['status' => 0, 'msg' => 'Gagal update status jurnal refill']);
            return;
        }

        if ($this->db->trans_status() === FALSE || $this->Jurnal_payment_petty_cash_model->check_transaction_status($target_db) === FALSE) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($target_db);
            echo json_encode(['status' => 0, 'msg' => 'Transaksi refill gagal, data di-rollback']);
            return;
        }

        $this->db->trans_commit();
        $this->Jurnal_payment_petty_cash_model->commit_transaction($target_db);
        echo json_encode(['status' => 1, 'msg' => 'Jurnal refill berhasil diposting ke DBACC_' . $company_label]);
    }

    /**
     * Internal: Posting jurnal STM (id_company = '5')
     *
     * @param object $jurnal_header Header data
     * @param array  $rows Detail rows from staging
     * @param string $no_transaksi Transaction number
     * @param string $jenis_transaksi Transaction type
     */
    private function _post_stm($jurnal_header, $rows, $no_transaksi, $jenis_transaksi)
    {
        $this->db->trans_begin();
        $this->Jurnal_payment_petty_cash_model->begin_transaction('accounting_stm');

        // Generate BUK number for STM
        $nomor_buk = $this->Jurnal_payment_petty_cash_nomor_model->get_nomor_buk('101', 'accounting_stm');
        if (!$nomor_buk) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            echo json_encode(['status' => 0, 'msg' => 'Gagal generate nomor BUK untuk STM']);
            return;
        }

        // Post to DBACC_STM
        $post_result = $this->Jurnal_payment_petty_cash_model->post_jurnal_stm($jurnal_header, $rows, $nomor_buk);
        if (!$post_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            echo json_encode(['status' => 0, 'msg' => 'Gagal insert jurnal ke database akuntansi STM']);
            return;
        }

        // Increment BUK counter on STM
        $increment_result = $this->Jurnal_payment_petty_cash_nomor_model->increment_nobuk('101', 'accounting_stm');
        if (!$increment_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            echo json_encode(['status' => 0, 'msg' => 'Gagal update counter BUK di STM']);
            return;
        }

        // Update staging status
        $update_result = $this->Jurnal_payment_petty_cash_model->update_status_posted($no_transaksi, $jenis_transaksi);
        if (!$update_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            echo json_encode(['status' => 0, 'msg' => 'Gagal update status jurnal']);
            return;
        }

        if ($this->db->trans_status() === FALSE || $this->Jurnal_payment_petty_cash_model->check_transaction_status('accounting_stm') === FALSE) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            echo json_encode(['status' => 0, 'msg' => 'Transaksi gagal, data di-rollback']);
            return;
        }

        $this->db->trans_commit();
        $this->Jurnal_payment_petty_cash_model->commit_transaction('accounting_stm');
        echo json_encode(['status' => 1, 'msg' => 'Jurnal berhasil diposting ke DBACC_STM']);
    }

    /**
     * Internal: Posting jurnal inter-company (VUCA id_company='4' atau SUSTAIN id_company='6')
     * Generates 2 BUK numbers (company side + STM side), posts to both databases.
     *
     * @param string $id_company Company identifier ('4' = VUCA, '6' = SUSTAIN)
     * @param object $jurnal_header Header data
     * @param array  $rows Detail rows from staging
     * @param string $no_transaksi Transaction number
     * @param string $jenis_transaksi Transaction type
     */
    private function _post_intercompany($id_company, $jurnal_header, $rows, $no_transaksi, $jenis_transaksi)
    {
        // Determine target DB name for company side
        $db_company = ($id_company == '4') ? 'accounting_vuca' : 'accounting_sustain';
        $company_label = ($id_company == '4') ? 'VUCA' : 'SUSTAIN';

        $this->db->trans_begin();
        $this->Jurnal_payment_petty_cash_model->begin_transaction('accounting_stm');
        $this->Jurnal_payment_petty_cash_model->begin_transaction($db_company);

        // Generate BUK number for company side (VUCA or SUSTAIN)
        $nomor_buk_company = $this->Jurnal_payment_petty_cash_nomor_model->get_nomor_buk('101', $db_company);
        if (!$nomor_buk_company) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($db_company);
            echo json_encode(['status' => 0, 'msg' => 'Gagal generate nomor BUK untuk sisi ' . $company_label]);
            return;
        }

        // Generate BUK number for STM side
        $nomor_buk_stm = $this->Jurnal_payment_petty_cash_nomor_model->get_nomor_buk('101', 'accounting_stm');
        if (!$nomor_buk_stm) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($db_company);
            echo json_encode(['status' => 0, 'msg' => 'Gagal generate nomor BUK untuk sisi STM']);
            return;
        }

        // Post to both databases (company + STM)
        $post_result = $this->Jurnal_payment_petty_cash_model->post_jurnal_intercompany(
            $id_company,
            $jurnal_header,
            $rows,
            $nomor_buk_company,
            $nomor_buk_stm
        );
        if (!$post_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($db_company);
            echo json_encode(['status' => 0, 'msg' => 'Gagal insert jurnal inter-company. Posting ke sisi ' . $company_label . ' atau STM gagal, seluruh data di-rollback']);
            return;
        }

        // Increment BUK counter on company side
        $increment_company = $this->Jurnal_payment_petty_cash_nomor_model->increment_nobuk('101', $db_company);
        if (!$increment_company) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($db_company);
            echo json_encode(['status' => 0, 'msg' => 'Gagal update counter BUK di sisi ' . $company_label . ', seluruh data di-rollback']);
            return;
        }

        // Increment BUK counter on STM side
        $increment_stm = $this->Jurnal_payment_petty_cash_nomor_model->increment_nobuk('101', 'accounting_stm');
        if (!$increment_stm) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($db_company);
            echo json_encode(['status' => 0, 'msg' => 'Gagal update counter BUK di sisi STM, seluruh data di-rollback']);
            return;
        }

        // Update staging status
        $update_result = $this->Jurnal_payment_petty_cash_model->update_status_posted($no_transaksi, $jenis_transaksi);
        if (!$update_result) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($db_company);
            echo json_encode(['status' => 0, 'msg' => 'Gagal update status jurnal, seluruh data di-rollback']);
            return;
        }

        if ($this->db->trans_status() === FALSE || $this->Jurnal_payment_petty_cash_model->check_transaction_status('accounting_stm') === FALSE || $this->Jurnal_payment_petty_cash_model->check_transaction_status($db_company) === FALSE) {
            $this->db->trans_rollback();
            $this->Jurnal_payment_petty_cash_model->rollback_transaction('accounting_stm');
            $this->Jurnal_payment_petty_cash_model->rollback_transaction($db_company);
            echo json_encode(['status' => 0, 'msg' => 'Transaksi inter-company gagal, seluruh data di-rollback']);
            return;
        }

        $this->db->trans_commit();
        $this->Jurnal_payment_petty_cash_model->commit_transaction('accounting_stm');
        $this->Jurnal_payment_petty_cash_model->commit_transaction($db_company);
        echo json_encode(['status' => 1, 'msg' => 'Jurnal inter-company berhasil diposting ke DBACC_' . $company_label . ' dan DBACC_STM']);
    }

    /**
     * Halaman laporan buku besar kas kecil (running balance)
     */
    public function buku_besar()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Buku Besar Kas Kecil');
        $this->template->render('buku_besar');
    }

    /**
     * Load data laporan buku besar
     * Accept POST (tgl_from, tgl_to), calculate saldo_awal + running balance, return JSON
     */
    public function get_data_buku_besar()
    {
        $this->auth->restrict($this->viewPermission);

        $tgl_from = $this->input->post('tgl_from');
        $tgl_to   = $this->input->post('tgl_to');

        // Get opening balance (sum debit - sum kredit for all posted transactions before tgl_from)
        $saldo_awal = $this->Jurnal_payment_petty_cash_model->get_saldo_awal($tgl_from);

        // Get transactions within the selected period
        $transactions = $this->Jurnal_payment_petty_cash_model->get_buku_besar_data($tgl_from, $tgl_to);

        // Calculate running balance server-side
        $saldo = $saldo_awal;
        $result_data = [];
        foreach ($transactions as $row) {
            $saldo = $saldo + (float)$row->debit - (float)$row->kredit;
            $row->saldo = $saldo;
            // Determine jenis_jurnal: if keterangan contains "Refill" → "Refill", else → "Transaksi"
            $row->jenis_jurnal = (stripos($row->keterangan, 'Refill') !== false) ? 'Refill' : 'Transaksi';
            $result_data[] = $row;
        }

        echo json_encode([
            'saldo_awal'   => (float)$saldo_awal,
            'transactions' => $result_data,
            'has_data'     => !empty($result_data)
        ]);
    }

    /**
     * Export laporan buku besar ke Excel (.xlsx)
     * GET params: tgl_from, tgl_to
     */
    public function export_buku_besar()
    {
        $this->auth->restrict($this->viewPermission);

        $tgl_from = $this->input->get('tgl_from');
        $tgl_to   = $this->input->get('tgl_to');

        // Validate date parameters
        if (empty($tgl_from) || empty($tgl_to)) {
            show_error('Parameter tanggal tidak lengkap', 400);
            return;
        }

        // Load PHPExcel library
        $this->load->library('PHPExcel');

        // Get data from model
        $saldo_awal   = $this->Jurnal_payment_petty_cash_model->get_saldo_awal($tgl_from);
        $transactions = $this->Jurnal_payment_petty_cash_model->get_buku_besar_data($tgl_from, $tgl_to);

        // Create PHPExcel workbook
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Buku Besar Kas Kecil');

        // Set column headers (row 1)
        $headers = ['No', 'No Transaksi', 'Tanggal', 'COA', 'Company', 'Pengeluaran', 'Jenis Jurnal', 'Debit', 'Kredit', 'Saldo', 'Keterangan'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];

        foreach ($headers as $idx => $header) {
            $sheet->setCellValue($columns[$idx] . '1', $header);
            $sheet->getStyle($columns[$idx] . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($columns[$idx])->setAutoSize(true);
        }

        // Row 2: Saldo Awal
        $row = 2;
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'Saldo Awal');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, '');
        $sheet->setCellValue('G' . $row, '');
        $sheet->setCellValue('H' . $row, '');
        $sheet->setCellValue('I' . $row, '');
        $sheet->setCellValue('J' . $row, $saldo_awal);
        $sheet->setCellValue('K' . $row, '');
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);

        // Data rows
        $row = 3;
        $no = 1;
        $running_balance = $saldo_awal;

        foreach ($transactions as $trx) {
            $debit  = (float) $trx->debit;
            $kredit = (float) $trx->kredit;
            $running_balance = $running_balance + $debit - $kredit;

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $trx->no_transaksi);
            $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($trx->tgl_jurnal)));
            $sheet->setCellValue('D' . $row, $trx->coa . ' - ' . $trx->nm_coa);
            $sheet->setCellValue('E' . $row, $trx->nm_company);
            $sheet->setCellValue('F' . $row, $trx->keterangan);
            $sheet->setCellValue('G' . $row, $trx->jenis_transaksi);
            $sheet->setCellValue('H' . $row, $debit);
            $sheet->setCellValue('I' . $row, $kredit);
            $sheet->setCellValue('J' . $row, $running_balance);
            $sheet->setCellValue('K' . $row, $trx->keterangan);

            // Format number columns with thousand separators
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $row++;
            $no++;
        }

        // Set response headers for .xlsx download
        $filename = 'Buku_Besar_Kas_Kecil_' . $tgl_from . '_' . $tgl_to . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Revisi/update status jurnal
     */
    public function revisi_jurnal()
    {
        if (!has_permission($this->managePermission)) {
            echo json_encode(['status' => false, 'msg' => 'Anda tidak memiliki akses untuk revisi jurnal']);
            return;
        }

        $post = $this->input->post();
        echo json_encode($this->Jurnal_payment_petty_cash_model->revisi_jurnal($post));
    }
}
