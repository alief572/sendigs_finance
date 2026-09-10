<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Controller: Record Request Payment (histori final snapshot - FSD 2026-09)
 * index (list done) -> detail (read-only snapshot) -> export Excel.
 */

class Record_request_payment extends Admin_Controller
{
	// Permission (reuse Request_Payment.*)
	protected $viewPermission   = "Request_Payment.View";

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Record_request_payment/Record_request_payment_model'));
		$this->template->title('Record Request Payment');
		$this->template->page_icon('fa fa-history');
		date_default_timezone_set("Asia/Bangkok");
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->title('Record Request Payment');
		$this->template->render('index');
	}

	public function get_data_record()
	{
		$this->Record_request_payment_model->get_data_record();
	}

	public function detail($id_pengajuan = null)
	{
		$this->auth->restrict($this->viewPermission);

		$id_pengajuan = (int) $id_pengajuan;
		$header = $this->Record_request_payment_model->get_header($id_pengajuan);

		if (empty($header)) {
			$this->session->set_flashdata('message', '<div class="alert alert-warning">Record tidak ditemukan.</div>');
			redirect(site_url('record_request_payment'));
			return;
		}

		$records = $this->Record_request_payment_model->get_records($id_pengajuan);

		// Tambahkan URL print dokumen sumber per record (pakai helper dari modul request_payment)
		$this->load->model('request_payment/Request_payment_model');
		foreach ($records as $r) {
			$r->print_url = $this->Request_payment_model->build_print_url($r);
		}

		$this->template->set('header', $header);
		$this->template->set('records', $records);
		$this->template->title('Detail Record - ' . $header->no_pengajuan);
		$this->template->render('detail');
	}

	/**
	 * Export Excel dari snapshot (tr_rp_record), bukan dari dokumen master.
	 * Filename: Record_Payment_{Company}_{NoPengajuan}.xlsx
	 */
	public function export_excel($id_pengajuan = null)
	{
		$this->auth->restrict($this->viewPermission);

		set_time_limit(0);
		ini_set('memory_limit', '512M');

		$id_pengajuan = (int) $id_pengajuan;
		$header  = $this->Record_request_payment_model->get_header($id_pengajuan);
		$records = $this->Record_request_payment_model->get_records($id_pengajuan);

		if (empty($header) || empty($records)) {
			show_error('Data record tidak ditemukan.', 404);
			return;
		}

		$this->load->library('PHPExcel');

		$objPHPExcel = new PHPExcel();
		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->setTitle('Record Payment');

		$company = $header->company_nama ? $header->company_nama : 'General';

		// Header laporan
		$sheet->setCellValue('A1', 'RIWAYAT REQUEST PAYMENT');
		$sheet->setCellValue('A2', 'Company: ' . $company);
		$sheet->setCellValue('A3', 'No. Pengajuan: ' . $header->no_pengajuan);
		$sheet->setCellValue('A4', 'Dicetak pada: ' . date('d-m-Y H:i:s'));
		$sheet->mergeCells('A1:K1');
		$sheet->mergeCells('A2:K2');
		$sheet->mergeCells('A3:K3');
		$sheet->mergeCells('A4:K4');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		// Kolom header tabel (baris 6)
		$headers = ['NO', 'NO. DOKUMEN', 'KATEGORI', 'REQUEST BY', 'KEPERLUAN', 'DPP', 'PPN', 'PPH23', 'PPH21', 'ADMIN', 'DIBAYARKAN'];
		$cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
		$headRow = 6;
		foreach ($headers as $i => $h) {
			$sheet->setCellValue($cols[$i] . $headRow, $h);
		}
		$headerStyle = [
			'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
			'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
			'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
			'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
		];
		$sheet->getStyle('A6:K6')->applyFromArray($headerStyle);

		// Lebar kolom
		$widths = ['A' => 5, 'B' => 20, 'C' => 14, 'D' => 22, 'E' => 34, 'F' => 15, 'G' => 12, 'H' => 12, 'I' => 12, 'J' => 10, 'K' => 16];
		foreach ($widths as $c => $w) {
			$sheet->getColumnDimension($c)->setWidth($w);
		}

		$bodyStyle = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]]];

		$row = $headRow + 1;
		$no = 0;
		$totalNilai = 0;
		$totalDibayarkan = 0;
		foreach ($records as $r) {
			$no++;
			$ppn   = (int) $r->flag_ppn ? (float) $r->nilai_ppn : 0;
			$pph23 = (int) $r->flag_pph23 ? (float) $r->nilai_pph : 0;
			$pph21 = (int) $r->flag_pph21 ? (float) $r->nilai_pph : 0;
			$admin = (float) $r->admin;
			$dibayarkan = (float) $r->dibayarkan;

			$totalNilai += (float) $r->dpp;
			$totalDibayarkan += $dibayarkan;

			$sheet->setCellValue('A' . $row, $no);
			$sheet->setCellValue('B' . $row, $r->no_dokumen);
			$sheet->setCellValue('C' . $row, $r->kategori);
			$sheet->setCellValue('D' . $row, $r->request_by . ($r->company_nama ? ' - ' . $r->company_nama : ''));
			$sheet->setCellValue('E' . $row, $r->keperluan);
			$sheet->setCellValue('F' . $row, (float) $r->dpp);
			$sheet->setCellValue('G' . $row, $ppn);
			$sheet->setCellValue('H' . $row, $pph23);
			$sheet->setCellValue('I' . $row, $pph21);
			$sheet->setCellValue('J' . $row, $admin);
			$sheet->setCellValue('K' . $row, $dibayarkan);

			$sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray($bodyStyle);
			foreach (['F', 'G', 'H', 'I', 'J', 'K'] as $c) {
				$sheet->getStyle($c . $row)->getNumberFormat()->setFormatCode('#,##0');
				$sheet->getStyle($c . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			}
			$row++;
		}

		// Baris total
		$sheet->setCellValue('E' . $row, 'TOTAL');
		$sheet->setCellValue('F' . $row, $totalNilai);
		$sheet->setCellValue('K' . $row, $totalDibayarkan);
		$sheet->getStyle('E' . $row . ':K' . $row)->getFont()->setBold(true);
		$sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyle('E' . $row . ':K' . $row)->applyFromArray($bodyStyle);

		// Filename
		$safeCompany = preg_replace('/[^a-zA-Z0-9]+/', '_', $company);
		$safePgj     = preg_replace('/[^a-zA-Z0-9\-]+/', '_', $header->no_pengajuan);
		$filename = 'Record_Payment_' . $safeCompany . '_' . $safePgj . '.xlsx';

		if (ob_get_level()) {
			ob_end_clean();
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
}
