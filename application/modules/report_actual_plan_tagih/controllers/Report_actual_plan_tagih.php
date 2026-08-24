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
        // $list_report  = $this->Report_actual_plan_tagih_model->get_report_actual_plan_tagih();

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
        $tahun = $get['tahun'];

        $nm_client = '';
        if (!empty($get['client'])) {
            $get_client = $this->db->get_where('view_rekap_actual_plan_tagih_dev', ['id_customer' => $get['client']])->row();
            $nm_client = (!empty($get_client->nm_customer)) ? $get_client->nm_customer : '';
        }

        $nm_company = '';
        if (!empty($get['company'])) {
            $get_company = $this->db->get_where('view_rekap_actual_plan_tagih_dev', ['id_company' => $get['company']])->row();
            $nm_company = (!empty($get_company->nm_company)) ? $get_company->nm_company : '';
        }

        // Load PHPExcel
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Report APT');

        // Mapping bulan
        $list_bulan_map = [
            1 => 'jan',
            2 => 'feb',
            3 => 'mar',
            4 => 'apr',
            5 => 'may',
            6 => 'jun',
            7 => 'jul',
            8 => 'aug',
            9 => 'sep',
            10 => 'oct',
            11 => 'nov',
            12 => 'dec'
        ];

        // Title
        $row = 1;
        $sheet->setCellValue('A' . $row, 'Report Actual Plan Tagih (' . $tahun . ')');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;

        if (!empty($nm_client)) {
            $sheet->setCellValue('A' . $row, 'Client : ' . $nm_client);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }
        if (!empty($nm_company)) {
            $sheet->setCellValue('A' . $row, 'Company : ' . $nm_company);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }
        $row++;

        // Header row
        $headerRow = $row;
        $headers = ['No.', 'Company', 'No. SPK', 'Customer', 'Consultant', 'Sales', 'Project', 'Nominal SPK', 'Nominal Invoice', 'Nominal Un-Invoiced', 'Macet'];
        foreach ($list_bulan_map as $num => $key) {
            $headers[] = date('M', strtotime($tahun . '-' . sprintf('%02d', $num) . '-01'));
        }

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Style header
        $lastCol = chr(ord('A') + count($headers) - 1);
        // Karena kolom lebih dari Z, hitung pakai angka
        $lastColIndex = count($headers) - 1;
        $lastColLetter = PHPExcel_Cell::stringFromColumnIndex($lastColIndex);

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '3C8DBC']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A' . $headerRow . ':' . $lastColLetter . $headerRow)->applyFromArray($headerStyle);

        $row++;

        // Data rows
        $no = 0;
        $ttl_nominal_spk = 0;
        $ttl_invoice     = 0;
        $ttl_uninvoice   = 0;
        $ttl_macet       = 0;
        $total_per_bulan = array_fill(1, 12, 0);

        foreach ($data as $item) {
            $no++;
            $current_invoice   = $item->nominal_invoice ?? 0;
            $current_uninvoice = $item->nominal_uninvoice ?? 0;
            $current_macet     = $item->macet ?? 0;
            $is_same_year      = ($item->tahun_data == $tahun);

            // Consultant
            $arr_konsultan = [];
            if (!empty($item->nm_konsultan_1)) $arr_konsultan[] = $item->nm_konsultan_1;
            if (!empty($item->nm_konsultan_2)) $arr_konsultan[] = $item->nm_konsultan_2;
            $nm_consultant = implode(', ', $arr_konsultan);

            $colIdx = 0;
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $no);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $item->nm_company);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $item->id_spk_penawaran);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $item->nm_customer);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $nm_consultant);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $item->nm_sales ?? '');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $item->nm_paket);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $item->nilai_kontrak);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $current_invoice);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $current_uninvoice);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $current_macet);

            // Bulan
            foreach ($list_bulan_map as $num => $key) {
                $val_bulan = ($is_same_year) ? ($item->$key ?? 0) : 0;
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $val_bulan);
                $total_per_bulan[$num] += $val_bulan;
            }

            $row++;

            $ttl_nominal_spk += $item->nilai_kontrak;
            $ttl_invoice     += $current_invoice;
            $ttl_uninvoice   += $current_uninvoice;
            $ttl_macet       += $current_macet;
        }

        // Grand Total row
        $colIdx = 0;
        $sheet->setCellValueByColumnAndRow($colIdx, $row, 'Grand Total');
        $sheet->mergeCellsByColumnAndRow(0, $row, 6, $row);
        $colIdx = 7;
        $sheet->setCellValueByColumnAndRow($colIdx++, $row, $ttl_nominal_spk);
        $sheet->setCellValueByColumnAndRow($colIdx++, $row, $ttl_invoice);
        $sheet->setCellValueByColumnAndRow($colIdx++, $row, $ttl_uninvoice);
        $sheet->setCellValueByColumnAndRow($colIdx++, $row, $ttl_macet);

        foreach ($total_per_bulan as $total_bln) {
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $total_bln);
        }

        // Style grand total
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->applyFromArray($totalStyle);

        // Output
        $filename = 'Report Actual Plan Tagih (' . $tahun . ') - ' . $nm_client . ' - ' . $nm_company . '.xls';

        // Bersihkan output buffer jika ada
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function get_data_report_apt()
    {
        $get = $this->input->get();

        $draw     = intval($get['draw'] ?? 1);
        $length   = intval($get['length'] ?? 10);
        $start    = intval($get['start'] ?? 0);
        $search   = $get['search']['value'] ?? '';
        $tahun    = !empty($get['tahun']) ? $get['tahun'] : date('Y');
        $client   = $get['client'] ?? '';
        $company  = $get['company'] ?? '';

        // 1. Total records (without search keyword)
        $this->db->select('a.id_spk_penawaran');
        $this->db->from('view_rekap_actual_plan_tagih_dev a');
        $this->db->group_start();
        $this->db->where('a.tahun_data', $tahun);
        $this->db->or_where('a.macet >', 0);
        $this->db->group_end();
        $this->db->where('a.tahun_data >=', 2000);
        if (!empty($client))  $this->db->where('a.id_customer', $client);
        if (!empty($company)) $this->db->where('a.id_company', $company);
        $this->db->group_by('a.id_spk_penawaran');
        $count_all = $this->db->get()->num_rows();

        // 2. Summary query (with all active filters & search) for Grand Totals across entire dataset
        $this->db->select("
            a.id_spk_penawaran,
            a.nilai_kontrak,
            a.nominal_invoice,
            a.nominal_uninvoice,
            a.macet,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`jan` ELSE 0 END) AS `jan`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`feb` ELSE 0 END) AS `feb`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`mar` ELSE 0 END) AS `mar`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`apr` ELSE 0 END) AS `apr`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`may` ELSE 0 END) AS `may`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`jun` ELSE 0 END) AS `jun`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`jul` ELSE 0 END) AS `jul`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`aug` ELSE 0 END) AS `aug`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`sep` ELSE 0 END) AS `sep`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`oct` ELSE 0 END) AS `oct`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`nov` ELSE 0 END) AS `nov`,
            SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`dec` ELSE 0 END) AS `dec`
        ", FALSE);
        $this->db->from('view_rekap_actual_plan_tagih_dev a');
        $this->db->group_start();
        $this->db->where('a.tahun_data', $tahun);
        $this->db->or_where('a.macet >', 0);
        $this->db->group_end();
        $this->db->where('a.tahun_data >=', 2000);

        if (!empty($client))  $this->db->where('a.id_customer', $client);
        if (!empty($company)) $this->db->where('a.id_company', $company);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.nm_company', $search, 'both');
            $this->db->or_like('a.id_spk_penawaran', $search, 'both');
            $this->db->or_like('a.nm_customer', $search, 'both');
            $this->db->or_like('a.nm_paket', $search, 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_spk_penawaran');
        $subquery_sql = $this->db->get_compiled_select();

        $summary_sql = "
            SELECT 
                COUNT(*) AS total_count,
                COALESCE(SUM(sub.nilai_kontrak), 0) AS total_nominal_spk,
                COALESCE(SUM(sub.nominal_invoice), 0) AS total_invoice,
                COALESCE(SUM(sub.nominal_uninvoice), 0) AS total_uninvoice,
                COALESCE(SUM(sub.macet), 0) AS total_macet,
                COALESCE(SUM(sub.jan), 0) AS total_jan,
                COALESCE(SUM(sub.feb), 0) AS total_feb,
                COALESCE(SUM(sub.mar), 0) AS total_mar,
                COALESCE(SUM(sub.apr), 0) AS total_apr,
                COALESCE(SUM(sub.may), 0) AS total_may,
                COALESCE(SUM(sub.jun), 0) AS total_jun,
                COALESCE(SUM(sub.jul), 0) AS total_jul,
                COALESCE(SUM(sub.aug), 0) AS total_aug,
                COALESCE(SUM(sub.sep), 0) AS total_sep,
                COALESCE(SUM(sub.oct), 0) AS total_oct,
                COALESCE(SUM(sub.nov), 0) AS total_nov,
                COALESCE(SUM(sub.dec), 0) AS total_dec
            FROM ({$subquery_sql}) AS sub
        ";
        $summary = $this->db->query($summary_sql)->row();
        $count_filter = !empty($summary->total_count) ? intval($summary->total_count) : 0;

        // 3. Paginated Data
        $list_bulan_select = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        $this->db->select("a.id_spk_penawaran, a.id_customer, a.nm_customer, a.nm_konsultan_1, a.nm_konsultan_2, a.nm_sales, a.nilai_kontrak, a.id_company, a.nm_company, a.nm_paket, a.sts_spk, a.input_date, a.id_company_ref, a.nominal_invoice, a.nominal_uninvoice, a.macet, MAX(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.tahun_data ELSE a.tahun_data END) AS tahun_data", FALSE);

        foreach ($list_bulan_select as $bln) {
            $this->db->select("SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`$bln` ELSE 0 END) AS `$bln`", FALSE);
        }

        $this->db->from('view_rekap_actual_plan_tagih_dev a');

        $this->db->group_start();
        $this->db->where('a.tahun_data', $tahun);
        $this->db->or_where('a.macet >', 0);
        $this->db->group_end();

        $this->db->where('a.tahun_data >=', 2000);

        if (!empty($client))  $this->db->where('a.id_customer', $client);
        if (!empty($company)) $this->db->where('a.id_company', $company);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.nm_company', $search, 'both');
            $this->db->or_like('a.id_spk_penawaran', $search, 'both');
            $this->db->or_like('a.nm_customer', $search, 'both');
            $this->db->or_like('a.nm_paket', $search, 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_spk_penawaran');
        $this->db->order_by('a.id_spk_penawaran', 'DESC');
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        $get_data = $this->db->get()->result();

        // 4. Processing Data for Page
        $no = $start;
        $hasil = [];

        foreach ($get_data as $item) {
            $total_invoice   = $item->nominal_invoice ?? 0;
            $total_uninvoice = $item->nominal_uninvoice ?? 0;
            $total_macet     = $item->macet ?? 0;
            $is_same_year    = ($item->tahun_data == $tahun);

            $arr_nm_konsultan = [];
            if (!empty($item->nm_konsultan_1)) {
                $arr_nm_konsultan[] = $item->nm_konsultan_1;
            }
            if (!empty($item->nm_konsultan_2)) {
                $arr_nm_konsultan[] = $item->nm_konsultan_2;
            }

            $nm_consultant = implode(', ', $arr_nm_konsultan);
            $nm_sales = $item->nm_sales ?? '';

            $row = [
                'no'                => ++$no,
                'company'           => $item->nm_company ?? '',
                'customer'          => $item->nm_customer ?? '',
                'no_spk'            => $item->id_spk_penawaran ?? '',
                'consultant'        => $nm_consultant,
                'sales'             => $nm_sales,
                'project'           => $item->nm_paket ?? '',
                'nominal_spk'       => number_format($item->nilai_kontrak, 0, ',', '.'),
                'nominal_invoice'   => number_format($total_invoice, 0, ',', '.'),
                'nominal_uninvoice' => number_format($total_uninvoice, 0, ',', '.'),
                'macet'             => number_format($total_macet, 0, ',', '.')
            ];

            foreach ($list_bulan_select as $bln) {
                $val = ($is_same_year) ? ($item->$bln ?? 0) : 0;
                $row[$bln] = number_format($val, 0, ',', '.');
            }

            $hasil[] = $row;
        }

        // 5. Final Response with full dataset summary
        $response = [
            'draw'              => $draw,
            'recordsTotal'      => $count_all,
            'recordsFiltered'   => $count_filter,
            'data'              => $hasil,
            'total_nominal_spk' => $summary->total_nominal_spk ?? 0,
            'total_invoice'     => $summary->total_invoice ?? 0,
            'total_uninvoice'   => $summary->total_uninvoice ?? 0,
            'total_macet'       => $summary->total_macet ?? 0,
            'total_jan'         => $summary->total_jan ?? 0,
            'total_feb'         => $summary->total_feb ?? 0,
            'total_mar'         => $summary->total_mar ?? 0,
            'total_apr'         => $summary->total_apr ?? 0,
            'total_may'         => $summary->total_may ?? 0,
            'total_jun'         => $summary->total_jun ?? 0,
            'total_jul'         => $summary->total_jul ?? 0,
            'total_aug'         => $summary->total_aug ?? 0,
            'total_sep'         => $summary->total_sep ?? 0,
            'total_oct'         => $summary->total_oct ?? 0,
            'total_nov'         => $summary->total_nov ?? 0,
            'total_dec'         => $summary->total_dec ?? 0
        ];

        echo json_encode($response);
    }

    public function get_data_report_apt_backup()
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

        $db_clone = clone $this->db;
        $count_filter = $db_clone->count_all_results();

        // $count_filter = $this->db->count_all_results();

        $get_data_all = $this->db->get()->result();


        $no_all = 0;
        foreach ($get_data_all as $item) {
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

                $arr_noms[$item_bulan] = $total_perbulan;
            endforeach;

            $no_all++;

            // if ($arr_noms[1] > 0 || $arr_noms[2] > 0 || $arr_noms[3] > 0 || $arr_noms[4] > 0 || $arr_noms[5] > 0 || $arr_noms[6] > 0 || $arr_noms[7] > 0 || $arr_noms[8] > 0 || $arr_noms[9] > 0 || $arr_noms[10] > 0 || $arr_noms[11] > 0 || $arr_noms[12] > 0) {
            // }
        }

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
            $this->db->order_by('a.input_date', 'DESC');
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
            // $no++;

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

            $no++;
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

            // if ($arr_noms[1] > 0 || $arr_noms[2] > 0 || $arr_noms[3] > 0 || $arr_noms[4] > 0 || $arr_noms[5] > 0 || $arr_noms[6] > 0 || $arr_noms[7] > 0 || $arr_noms[8] > 0 || $arr_noms[9] > 0 || $arr_noms[10] > 0 || $arr_noms[11] > 0 || $arr_noms[12] > 0) {
            // }
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $no_all,
            'recordsFiltered' => $no_all,
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
