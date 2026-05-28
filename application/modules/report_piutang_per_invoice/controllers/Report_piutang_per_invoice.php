<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_piutang_per_invoice extends Admin_Controller
{
    protected $viewPermission = 'Report_Piutang_Per_Invoice.View';

    protected $consultant;

    /**
     * Mapping company codes ke tab perusahaan.
     */
    const COMPANY_TAB_MAP = [
        'STM'     => [1, 6, 7],
        'VUCA'    => [4],
        'SUSTAIN' => [3],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Report_piutang_per_invoice/Report_piutang_per_invoice_model',
        ));
        $this->template->title('Report Piutang Per Invoice');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
    }

    public function index()
    {
        $this->auth->restrict('Report_Piutang_Per_Invoice.View');

        $this->template->title('Report Piutang Per Invoice');
        $this->template->render('index');
    }

    public function get_report_data()
    {
        // --- Input Validation ---
        $filter_date = $this->input->get('filter_date');
        $company_codes = $this->input->get('company_codes');

        // Validate filter_date: required
        if (empty($filter_date)) {
            echo json_encode(['status' => 'error', 'message' => 'Filter tanggal harus diisi']);
            return;
        }

        // Validate filter_date: format Y-m-d
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
            echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid, gunakan format Y-m-d']);
            return;
        }

        // Validate filter_date: must be a valid date
        $date_parts = explode('-', $filter_date);
        if (!checkdate((int) $date_parts[1], (int) $date_parts[2], (int) $date_parts[0])) {
            echo json_encode(['status' => 'error', 'message' => 'Tanggal tidak valid']);
            return;
        }

        // Validate filter_date: must not be in the future
        $today = date('Y-m-d');
        if ($filter_date > $today) {
            echo json_encode(['status' => 'error', 'message' => 'Tanggal tidak boleh lebih dari hari ini']);
            return;
        }

        // Validate company_codes: required and must be array
        if (empty($company_codes) || !is_array($company_codes)) {
            echo json_encode(['status' => 'error', 'message' => 'Company codes harus berupa array']);
            return;
        }

        // Validate company_codes: each element must be integer in valid mapping
        $valid_company_codes = [1, 3, 4, 6, 7];
        foreach ($company_codes as $code) {
            if (!in_array((int) $code, $valid_company_codes, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Company code tidak valid']);
                return;
            }
        }

        // --- End Validation ---

        // Fetch raw data from model
        $company_codes = array_map('intval', $company_codes);
        $raw_data = $this->Report_piutang_per_invoice_model->get_report_data($company_codes, $filter_date);

        // Process raw data into hierarchical structure
        $processed_data = $this->_process_report_data($raw_data);

        // Calculate summary totals from processed data
        $summary = $this->_calculate_summary($processed_data);

        $response = [
            'status' => 'success',
            'data' => $processed_data,
            'summary' => $summary,
        ];

        echo json_encode($response);
    }

    /**
     * Process raw query results into hierarchical structure.
     *
     * Transforms flat JOIN results into: Customer → SPK → TOP/Invoice → Payment
     * Handles deduplication of rows caused by multiple payments per invoice (JOIN expansion).
     *
     * The raw data is already sorted by: nm_customer ASC, id_spk_penawaran ASC,
     * tanggal_invoice ASC, tanggal_bayar ASC.
     *
     * Calculates formula fields:
     * - Piutang Per Invoice: max(0, nilai_invoice - SUM(nilai_bayar))
     * - Uninvoiced per SPK: nominal_project - SUM(nilai_invoice)
     * - Total Sisa Piutang Per SPK: SUM(piutang_per_invoice) for all invoices in SPK
     * - Invoice progress: total_top, invoiced_top, pending_top per SPK
     *
     * @param array $raw_data Flat array of rows from model query
     * @return array Hierarchical structure grouped by customer → SPK → details
     */
    private function _process_report_data($raw_data)
    {
        if (empty($raw_data)) {
            return [];
        }

        // Step 1: Group raw data by customer → SPK → TOP → payments
        $customers = [];

        foreach ($raw_data as $row) {
            $customer_name = $row['nm_customer'];
            $spk_id = $row['id_spk_penawaran'];
            $top_id = $row['id_detail_plan_tagih'];
            $invoice_id = $row['id_invoice'];
            $payment_id = $row['id_payment'];

            // Initialize customer group if not exists
            if (!isset($customers[$customer_name])) {
                $customers[$customer_name] = [];
            }

            // Initialize SPK group if not exists
            if (!isset($customers[$customer_name][$spk_id])) {
                $customers[$customer_name][$spk_id] = [
                    'no_spk' => $spk_id,
                    'nominal_project' => (float) $row['nominal_project'],
                    'tops' => [],
                ];
            }

            // Initialize TOP if not exists
            if (!isset($customers[$customer_name][$spk_id]['tops'][$top_id])) {
                $customers[$customer_name][$spk_id]['tops'][$top_id] = [
                    'top_number' => (int) $row['top_number'],
                    'rincian_top' => (float) $row['rincian_top'],
                    'invoice' => null,
                ];
            }

            // Set invoice data if exists (only initialize once per TOP)
            if (!empty($invoice_id)) {
                if ($customers[$customer_name][$spk_id]['tops'][$top_id]['invoice'] === null) {
                    $customers[$customer_name][$spk_id]['tops'][$top_id]['invoice'] = [
                        'tanggal_invoice' => $row['tanggal_invoice'],
                        'no_invoice' => $row['no_invoice'],
                        'nilai_invoice' => (float) $row['nilai_invoice'],
                        'payments' => [],
                    ];
                }

                // Add payment if exists and not already added (deduplication)
                if (!empty($payment_id)) {
                    $payments = &$customers[$customer_name][$spk_id]['tops'][$top_id]['invoice']['payments'];
                    $payment_exists = false;
                    foreach ($payments as $existing_payment) {
                        if ($existing_payment['id_payment'] == $payment_id) {
                            $payment_exists = true;
                            break;
                        }
                    }
                    if (!$payment_exists) {
                        $payments[] = [
                            'id_payment' => $payment_id,
                            'tanggal_bayar' => $row['tanggal_bayar'],
                            'nilai_bayar' => (float) $row['nilai_bayar'],
                        ];
                    }
                    unset($payments);
                }
            }
        }

        // Step 2: Transform into final output structure
        // Sort customers alphabetically
        ksort($customers);

        $result = [];

        foreach ($customers as $customer_name => $spk_list) {
            $customer_entry = [
                'customer' => $customer_name,
                'spk_list' => [],
            ];

            // Sort SPKs by nomor SPK
            ksort($spk_list);

            foreach ($spk_list as $spk_id => $spk_data) {
                // Count TOP stats for invoice progress
                $total_top = count($spk_data['tops']);
                $invoiced_top = 0;

                foreach ($spk_data['tops'] as $top) {
                    if ($top['invoice'] !== null) {
                        $invoiced_top++;
                    }
                }

                $pending_top = $total_top - $invoiced_top;

                // Build details array from TOPs (sorted by tanggal_invoice ASC via query order)
                // Also calculate formula fields per invoice and aggregate per SPK
                $details = [];
                $sum_rincian_top_invoiced = 0;
                $sum_piutang_per_invoice = 0;

                foreach ($spk_data['tops'] as $top_id => $top_data) {
                    $detail_entry = [
                        'top_number' => $top_data['top_number'],
                        'rincian_top' => $top_data['rincian_top'],
                        'invoice' => null,
                    ];

                    if ($top_data['invoice'] !== null) {
                        // Clean payments: remove internal id_payment, keep only output fields
                        $clean_payments = [];
                        $sum_nilai_bayar = 0;
                        foreach ($top_data['invoice']['payments'] as $payment) {
                            $has_date = !empty($payment['tanggal_bayar']);
                            $clean_payments[] = [
                                'tanggal_bayar' => $payment['tanggal_bayar'],
                                'nilai_bayar' => $has_date ? $payment['nilai_bayar'] : null,
                            ];
                            if ($has_date) {
                                $sum_nilai_bayar += $payment['nilai_bayar'];
                            }
                        }

                        // Calculate Piutang Per Invoice: max(0, nilai_invoice - SUM(nilai_bayar))
                        $nilai_invoice = $top_data['invoice']['nilai_invoice'];
                        $piutang_per_invoice = max(0, $nilai_invoice - $sum_nilai_bayar);

                        // Accumulate for SPK-level calculations
                        // Use rincian_top (nominal_payment) for uninvoiced calculation
                        $sum_rincian_top_invoiced += $top_data['rincian_top'];
                        $sum_piutang_per_invoice += $piutang_per_invoice;

                        $detail_entry['invoice'] = [
                            'tanggal_invoice' => $top_data['invoice']['tanggal_invoice'],
                            'no_invoice' => $top_data['invoice']['no_invoice'],
                            'nilai_invoice' => $nilai_invoice,
                            'piutang_per_invoice' => $piutang_per_invoice,
                            'payments' => $clean_payments,
                        ];
                    }

                    $details[] = $detail_entry;
                }

                // Calculate Uninvoiced per SPK: nominal_project - SUM(rincian_top yang sudah punya invoice)
                $uninvoiced = $spk_data['nominal_project'] - $sum_rincian_top_invoiced;

                // Calculate Total Sisa Piutang Per SPK: SUM(piutang_per_invoice)
                $total_sisa_piutang = $sum_piutang_per_invoice;

                $spk_entry = [
                    'no_spk' => $spk_data['no_spk'],
                    'nominal_project' => $spk_data['nominal_project'],
                    'total_top' => $total_top,
                    'invoiced_top' => $invoiced_top,
                    'pending_top' => $pending_top,
                    'uninvoiced' => $uninvoiced,
                    'total_sisa_piutang' => $total_sisa_piutang,
                    'details' => $details,
                ];

                $customer_entry['spk_list'][] = $spk_entry;
            }

            $result[] = $customer_entry;
        }

        return $result;
    }

    /**
     * Calculate summary totals from processed hierarchical data.
     *
     * Iterates through all customers → SPKs → invoices to sum up:
     * - total_piutang_per_invoice: SUM of all piutang_per_invoice across all invoices
     * - total_uninvoiced: SUM of all uninvoiced values across all SPKs
     * - total_sisa_piutang_per_spk: SUM of all total_sisa_piutang across all SPKs
     * - grand_total_piutang: total_piutang_per_invoice + total_uninvoiced
     *
     * @param array $processed_data Hierarchical data from _process_report_data()
     * @return array Summary with total_piutang_per_invoice, total_uninvoiced, total_sisa_piutang_per_spk, grand_total_piutang
     */
    private function _calculate_summary($processed_data)
    {
        $total_piutang_per_invoice = 0;
        $total_uninvoiced = 0;
        $total_sisa_piutang_per_spk = 0;

        if (!empty($processed_data)) {
            foreach ($processed_data as $customer) {
                foreach ($customer['spk_list'] as $spk) {
                    $total_uninvoiced += $spk['uninvoiced'];
                    $total_sisa_piutang_per_spk += $spk['total_sisa_piutang'];

                    foreach ($spk['details'] as $detail) {
                        if ($detail['invoice'] !== null) {
                            $total_piutang_per_invoice += $detail['invoice']['piutang_per_invoice'];
                        }
                    }
                }
            }
        }

        $grand_total_piutang = $total_piutang_per_invoice + $total_uninvoiced;

        return [
            'total_piutang_per_invoice' => $total_piutang_per_invoice,
            'total_uninvoiced' => $total_uninvoiced,
            'total_sisa_piutang_per_spk' => $total_sisa_piutang_per_spk,
            'grand_total_piutang' => $grand_total_piutang,
        ];
    }

    /**
     * Download report as Excel file.
     * URL: report_piutang_per_invoice/download_excel/{filter_date}/{tab_key}
     */
    public function download_excel($filter_date = '', $tab_key = 'stm')
    {
        $this->auth->restrict('Report_Piutang_Per_Invoice.View');

        // Validate filter_date
        if (empty($filter_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
            show_error('Parameter tanggal tidak valid.');
            return;
        }

        // Determine company codes from tab key
        $tab_key = strtoupper($tab_key);
        if (!isset(self::COMPANY_TAB_MAP[$tab_key])) {
            show_error('Tab tidak valid.');
            return;
        }
        $company_codes = self::COMPANY_TAB_MAP[$tab_key];

        // Fetch and process data
        $raw_data = $this->Report_piutang_per_invoice_model->get_report_data($company_codes, $filter_date);
        $processed_data = $this->_process_report_data($raw_data);
        $summary = $this->_calculate_summary($processed_data);

        // Generate Excel
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Piutang Per Invoice');

        // Styles
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '2980B9']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $customerStyle = [
            'font' => ['bold' => true],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F9F0D2']],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $bodyStyle = [
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $formulaStyle = [
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'D6EAF8']],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $summaryStyle = [
            'font' => ['bold' => true],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'D6EAF8']],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];

        // Title row
        $row = 1;
        $display_date = date('d-m-Y', strtotime($filter_date));
        $sheet->setCellValue('A' . $row, 'Report Piutang Per Invoice - ' . $tab_key . ' (Tanggal: ' . $display_date . ')');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);

        // Header row
        $row = 3;
        $headers = ['Customer', 'No SPK', 'Nominal Project', 'TOP', 'Rincian TOP', 'Tgl Invoice', 'No Invoice', 'Nilai Invoice', 'Tgl Bayar', 'Nilai Bayar', 'Piutang Per Invoice', 'Uninvoiced', 'Total Sisa Piutang'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];

        foreach ($headers as $i => $header) {
            $sheet->setCellValue($cols[$i] . $row, $header);
            $sheet->getColumnDimension($cols[$i])->setAutoSize(true);
        }
        $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($headerStyle);

        // Data rows
        $row = 4;

        if (empty($processed_data)) {
            $sheet->setCellValue('A' . $row, 'Tidak ada data piutang untuk ditampilkan.');
            $sheet->mergeCells('A' . $row . ':M' . $row);
        } else {
            foreach ($processed_data as $customer) {
                // Customer header row
                $sheet->setCellValue('A' . $row, $customer['customer']);
                $sheet->mergeCells('A' . $row . ':M' . $row);
                $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($customerStyle);
                $row++;

                foreach ($customer['spk_list'] as $spk) {
                    $is_first_spk_row = true;

                    foreach ($spk['details'] as $detail) {
                        $invoice = $detail['invoice'];
                        $payments = ($invoice && !empty($invoice['payments'])) ? $invoice['payments'] : [];

                        // Main row
                        $sheet->setCellValue('A' . $row, ''); // Customer empty (shown in header)

                        if ($is_first_spk_row) {
                            $sheet->setCellValue('B' . $row, $spk['no_spk']);
                            $sheet->setCellValue('C' . $row, $spk['nominal_project']);
                        }

                        $sheet->setCellValue('D' . $row, $detail['top_number']);
                        $sheet->setCellValue('E' . $row, $detail['rincian_top']);

                        if ($invoice) {
                            $sheet->setCellValue('F' . $row, $invoice['tanggal_invoice'] ? date('d-m-Y', strtotime($invoice['tanggal_invoice'])) : '-');
                            $sheet->setCellValue('G' . $row, $invoice['no_invoice']);
                            $sheet->setCellValue('H' . $row, $invoice['nilai_invoice']);

                            if (!empty($payments)) {
                                $sheet->setCellValue('I' . $row, $payments[0]['tanggal_bayar'] ? date('d-m-Y', strtotime($payments[0]['tanggal_bayar'])) : '-');
                                $sheet->setCellValue('J' . $row, $payments[0]['nilai_bayar'] !== null ? $payments[0]['nilai_bayar'] : '-');
                            }

                            $sheet->setCellValue('K' . $row, $invoice['piutang_per_invoice']);
                        } else {
                            $sheet->setCellValue('F' . $row, '-');
                            $sheet->setCellValue('G' . $row, '-');
                            $sheet->setCellValue('H' . $row, '-');
                            $sheet->setCellValue('I' . $row, '-');
                            $sheet->setCellValue('J' . $row, '-');
                            $sheet->setCellValue('K' . $row, '-');
                        }

                        if ($is_first_spk_row) {
                            $sheet->setCellValue('L' . $row, $spk['uninvoiced']);
                            $sheet->setCellValue('M' . $row, $spk['total_sisa_piutang']);
                        }

                        // Apply styles
                        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($bodyStyle);
                        $sheet->getStyle('K' . $row . ':M' . $row)->applyFromArray($formulaStyle);

                        // Number format for currency columns
                        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');

                        $row++;

                        // Additional payment rows (2nd payment onward)
                        for ($p = 1; $p < count($payments); $p++) {
                            $sheet->setCellValue('I' . $row, $payments[$p]['tanggal_bayar'] ? date('d-m-Y', strtotime($payments[$p]['tanggal_bayar'])) : '-');
                            $sheet->setCellValue('J' . $row, $payments[$p]['nilai_bayar'] !== null ? $payments[$p]['nilai_bayar'] : '-');

                            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($bodyStyle);
                            $sheet->getStyle('K' . $row . ':M' . $row)->applyFromArray($formulaStyle);
                            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
                            $row++;
                        }

                        $is_first_spk_row = false;
                    }
                }
            }

            // Summary section
            $row++;
            $sheet->setCellValue('A' . $row, 'Summary Piutang Per Invoice');
            $sheet->setCellValue('M' . $row, $summary['total_piutang_per_invoice']);
            $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($summaryStyle);
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;

            $sheet->setCellValue('A' . $row, 'Summary Uninvoiced');
            $sheet->setCellValue('M' . $row, $summary['total_uninvoiced']);
            $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($summaryStyle);
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;

            $sheet->setCellValue('A' . $row, 'Summary Sisa Piutang Per SPK');
            $sheet->setCellValue('M' . $row, $summary['total_sisa_piutang_per_spk']);
            $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($summaryStyle);
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;

            $sheet->setCellValue('A' . $row, 'Total Piutang');
            $sheet->setCellValue('M' . $row, $summary['grand_total_piutang']);
            $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($summaryStyle);
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $row . ':M' . $row)->getFont()->setBold(true);
        }

        // Output file
        $filename = 'Report_Piutang_Per_Invoice_' . $tab_key . '_' . $filter_date . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
