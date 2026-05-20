<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_piutang extends Admin_Controller
{
    protected $viewPermission   = 'Report_Piutang.View';
    protected $addPermission    = 'Report_Piutang.Add';
    protected $managePermission = 'Report_Piutang.Manage';
    protected $deletePermission = 'Report_Piutang.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_piutang_model');
        $this->template->title('Report Piutang');
        $this->template->page_icon('fa fa-file-text-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    /**
     * Render halaman utama report piutang dengan form filter tanggal.
     */
    public function index()
    {
        $data = [
            'tgl_dari'   => date('01/m/Y'),
            'tgl_sampai' => date('d/m/Y')
        ];

        $this->template->title('Report Piutang');
        $this->template->set($data);
        $this->template->render('index');
    }

    /**
     * AJAX endpoint untuk mengambil data report piutang.
     * Menerima POST parameters tgl_dari dan tgl_sampai (format dd/mm/yyyy).
     * Mengembalikan JSON response dengan data grouped dan total piutang.
     */
    public function get_data()
    {
        $tgl_dari   = $this->input->post('tgl_dari');
        $tgl_sampai = $this->input->post('tgl_sampai');

        // Trim whitespace
        $tgl_dari   = $tgl_dari ? trim($tgl_dari) : '';
        $tgl_sampai = $tgl_sampai ? trim($tgl_sampai) : '';

        // If both dates are empty, no filter applied
        if ($tgl_dari === '' && $tgl_sampai === '') {
            $data          = $this->report_piutang_model->get_report_piutang(null, null);
            $total_piutang = $this->report_piutang_model->get_total_piutang(null, null);

            echo json_encode([
                'status'        => true,
                'data'          => $data,
                'total_piutang' => $total_piutang
            ]);
            return;
        }

        // If only one date is filled, return error
        if (($tgl_dari === '' && $tgl_sampai !== '') || ($tgl_dari !== '' && $tgl_sampai === '')) {
            echo json_encode([
                'status'  => false,
                'message' => 'Tanggal dari dan tanggal sampai harus diisi keduanya'
            ]);
            return;
        }

        // Validate date format (dd/mm/yyyy)
        if (!$this->_validate_date_format($tgl_dari) || !$this->_validate_date_format($tgl_sampai)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Format tanggal tidak valid (gunakan dd/mm/yyyy)'
            ]);
            return;
        }

        // Convert dd/mm/yyyy to yyyy-mm-dd
        $tgl_dari_db   = $this->_convert_date_to_db($tgl_dari);
        $tgl_sampai_db = $this->_convert_date_to_db($tgl_sampai);

        // Validate tgl_sampai >= tgl_dari
        if ($tgl_sampai_db < $tgl_dari_db) {
            echo json_encode([
                'status'  => false,
                'message' => 'Tanggal sampai harus lebih besar atau sama dengan tanggal dari'
            ]);
            return;
        }

        // Get data from model
        $data          = $this->report_piutang_model->get_report_piutang($tgl_dari_db, $tgl_sampai_db);
        $total_piutang = $this->report_piutang_model->get_total_piutang($tgl_dari_db, $tgl_sampai_db);

        echo json_encode([
            'status'        => true,
            'data'          => $data,
            'total_piutang' => $total_piutang
        ]);
    }

    /**
     * Validate date format dd/mm/yyyy.
     *
     * @param string $date Date string to validate
     * @return bool True if valid format
     */
    private function _validate_date_format($date)
    {
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return false;
        }

        $parts = explode('/', $date);
        $day   = (int) $parts[0];
        $month = (int) $parts[1];
        $year  = (int) $parts[2];

        return checkdate($month, $day, $year);
    }

    /**
     * Convert date from dd/mm/yyyy to yyyy-mm-dd format.
     *
     * @param string $date Date in dd/mm/yyyy format
     * @return string Date in yyyy-mm-dd format
     */
    private function _convert_date_to_db($date)
    {
        $parts = explode('/', $date);
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }

    /**
     * Generate dan download PDF report piutang.
     * Menerima parameter tanggal dari GET query string (tgl_dari, tgl_sampai) dalam format dd/mm/yyyy.
     */
    public function print_pdf()
    {
        // Receive date range from GET parameters
        $tgl_dari   = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        // Convert dates from dd/mm/yyyy to yyyy-mm-dd for database query
        $tgl_dari_db   = null;
        $tgl_sampai_db = null;

        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            if ($this->_validate_date_format($tgl_dari) && $this->_validate_date_format($tgl_sampai)) {
                $tgl_dari_db   = $this->_convert_date_to_db($tgl_dari);
                $tgl_sampai_db = $this->_convert_date_to_db($tgl_sampai);
            }
        }

        // Get report data from model
        $report_data   = $this->report_piutang_model->get_report_piutang($tgl_dari_db, $tgl_sampai_db);
        $total_piutang = $this->report_piutang_model->get_total_piutang($tgl_dari_db, $tgl_sampai_db);

        // Prepare data for PDF view
        $data = [
            'report_data'   => $report_data,
            'total_piutang' => $total_piutang,
            'tgl_dari'      => $tgl_dari,
            'tgl_sampai'    => $tgl_sampai,
            'tgl_cetak'     => date('d/m/Y H:i:s')
        ];

        // Render print_pdf view to HTML string
        $html = $this->load->view('print_pdf', $data, TRUE);

        // Generate PDF using mPDF with landscape orientation
        try {
            $this->load->library(array('Mpdf'));
            $mpdf = new mPDF('', 'A4-L');
            $mpdf->WriteHTML($html);
            $mpdf->Output('Report_Piutang_' . date('Y-m-d') . '.pdf', 'D');
        } catch (Exception $e) {
            log_message('error', 'PDF generation failed: ' . $e->getMessage());
            $this->session->set_flashdata('message', 'Print gagal. Silakan coba lagi.');
            redirect('report_piutang');
        }
    }

    /**
     * Generate dan download Excel report piutang (.xlsx).
     * Menerima parameter tanggal dari GET query string (tgl_dari, tgl_sampai) dalam format dd/mm/yyyy.
     */
    public function export_excel()
    {
        // Receive date range from GET parameters
        $tgl_dari   = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        // Convert dates from dd/mm/yyyy to yyyy-mm-dd for database query
        $tgl_dari_db   = null;
        $tgl_sampai_db = null;

        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            if ($this->_validate_date_format($tgl_dari) && $this->_validate_date_format($tgl_sampai)) {
                $tgl_dari_db   = $this->_convert_date_to_db($tgl_dari);
                $tgl_sampai_db = $this->_convert_date_to_db($tgl_sampai);
            }
        }

        // Get report data from model
        $report_data   = $this->report_piutang_model->get_report_piutang($tgl_dari_db, $tgl_sampai_db);
        $total_piutang = $this->report_piutang_model->get_total_piutang($tgl_dari_db, $tgl_sampai_db);

        // Check if data is empty; redirect back with notification
        if (empty($report_data)) {
            $this->session->set_flashdata('message', 'Tidak ada data untuk di-export');
            redirect('report_piutang');
            return;
        }

        // Set filename with current date
        $filename = 'Report_Piutang_' . date('Y-m-d') . '.xlsx';

        // Prepare data for Excel view
        $data = [
            'report_data'   => $report_data,
            'total_piutang' => $total_piutang,
            'tgl_dari'      => $tgl_dari,
            'tgl_sampai'    => $tgl_sampai,
            'filename'      => $filename
        ];

        $this->load->view('export_excel', $data);
    }
}
