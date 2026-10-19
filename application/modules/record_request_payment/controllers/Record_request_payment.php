<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Controller: Record Request Payment (histori final snapshot - FSD 2026-09)
 * index (list done) -> detail (read-only snapshot) -> export Excel.
 */

class Record_request_payment extends Admin_Controller
{
	// Permission (reuse Request_Payment.*)
	protected $viewPermission   = "Record_Request_Payment.View";

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
		$this->beginAudit(__FUNCTION__, array(), array());
		$this->auth->restrict($this->viewPermission);
		$this->template->title('Record Request Payment');
		try {
			$this->template->render('index');
		} catch (Throwable $e) {
			$this->completeAudit('Gagal merender halaman Record Request Payment', 0, array('reason' => 'render_exception'));
			throw $e;
		}
		$ok = $this->auditReadStatus() === 1;
		$this->completeAudit(
			$ok ? 'Membuka daftar Record Request Payment' : 'Gagal membuka daftar Record Request Payment',
			$ok ? 1 : 0,
			$ok ? array('outcome' => 'rendered') : array('reason' => 'query_failure')
		);
	}


	public function get_data_record_items()
	{
		$this->beginAudit(__FUNCTION__, $this->auditFilters(), ['row_count', 'records_total', 'records_filtered']);
		$draw = $this->input->get_post('draw');
		$response = ['draw' => is_scalar($draw) ? (int) $draw : 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];
		$ok = false;
		$reason = 'permission_denied';
		if (has_permission($this->viewPermission)) {
			$reason = 'query_failure';
			try {
				$summary = [];
				$response = $this->Record_request_payment_model->get_data_record_items($summary);
				$ok = !empty($summary['query_ok']) && $this->auditReadStatus() === 1;
				if ($ok) {
					$this->load->model('request_payment/Request_payment_model');
					foreach ($response['data'] as &$row) {
						$this->Request_payment_model->resetAuditQueriesOk();
						$source = (object) $row;
						$source->id = $row['id_dokumen'] ?? $row['no_dokumen'];
						$row['print_url'] = $this->Request_payment_model->build_print_url($source);
						$ok = $this->Request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1 && $ok;
					}
					unset($row);
				}
			} catch (Throwable $e) {
				$ok = false;
			}
		}
		if (!$ok) {
			$response = ['draw' => $response['draw'], 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [],
				'error' => $reason === 'permission_denied' ? 'Anda tidak memiliki hak untuk melihat data ini.' : 'Gagal memuat data. Silakan coba lagi.'];
		}
		$this->completeAudit($ok ? 'Membaca daftar dokumen Record_request_payment' : 'Gagal membaca daftar dokumen Record_request_payment', $ok ? 1 : 0, [
			'row_count' => count($response['data']),
			'records_total' => $response['recordsTotal'],
			'records_filtered' => $response['recordsFiltered'],
			$ok ? 'outcome' : 'reason' => $ok ? 'listed' : $reason,
		]);
		echo json_encode($response);
	}

	public function get_data_record()
	{
		$this->beginAudit(__FUNCTION__, $this->auditFilters(), array('row_count', 'records_total', 'records_filtered'));
		$summary = array();
		$this->Record_request_payment_model->get_data_record($summary);
		$ok = !empty($summary['query_ok']) && $this->auditReadStatus() === 1;
		$this->completeAudit(
			$ok ? 'Membaca daftar Record Request Payment' : 'Gagal membaca daftar Record Request Payment',
			$ok ? 1 : 0,
			array_merge(
				array(
					'row_count'        => (int) ($summary['row_count'] ?? 0),
					'records_total'    => (int) ($summary['records_total'] ?? 0),
					'records_filtered' => (int) ($summary['records_filtered'] ?? 0),
				),
				$ok ? array('outcome' => 'listed') : array('reason' => 'query_failure')
			)
		);
	}

	public function get_data_record_legacy()
	{
		$this->beginAudit(__FUNCTION__, $this->auditFilters(), array('row_count', 'records_total', 'records_filtered'));
		$summary = array();
		$this->Record_request_payment_model->get_data_record_legacy($summary);
		$ok = !empty($summary['query_ok']) && $this->auditReadStatus() === 1;
		$this->completeAudit(
			$ok ? 'Membaca daftar Record Request Payment legacy' : 'Gagal membaca daftar Record Request Payment legacy',
			$ok ? 1 : 0,
			array_merge(
				array(
					'row_count'        => (int) ($summary['row_count'] ?? 0),
					'records_total'    => (int) ($summary['records_total'] ?? 0),
					'records_filtered' => (int) ($summary['records_filtered'] ?? 0),
				),
				$ok ? array('outcome' => 'listed') : array('reason' => 'query_failure')
			)
		);
	}

	public function detail($id_pengajuan = null)
	{
		$id_pengajuan = (int) $id_pengajuan;
		$returnRoute = $this->input->get_post('from') === 'items'
			? 'record_request_payment/index?tab=items' : 'record_request_payment';
		$this->beginAudit(__FUNCTION__, array('id_pengajuan' => $id_pengajuan), array('id_pengajuan', 'no_pengajuan', 'row_count'));
		$this->auth->restrict($this->viewPermission);

		$header = $this->Record_request_payment_model->get_header($id_pengajuan);
		$headerQueryOk = $this->Record_request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1;

		if (!$headerQueryOk || empty($header)) {
			$this->completeAudit('Gagal membuka detail Record Request Payment', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'reason'       => !$headerQueryOk ? 'query_failure' : 'not_found',
			));
			$this->session->set_flashdata('message', '<div class="alert alert-warning">Record tidak ditemukan.</div>');
			redirect(site_url($returnRoute));
			return;
		}

		$records = $this->Record_request_payment_model->get_records($id_pengajuan);
		$recordsQueryOk = $this->Record_request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1;

		// Tambahkan URL print dokumen sumber per record (pakai helper dari modul request_payment)
		$this->load->model('request_payment/Request_payment_model');
		$printQueriesOk = true;
		foreach ($records as $r) {
			if (is_object($this->Request_payment_model) && method_exists($this->Request_payment_model, 'resetAuditQueriesOk')) {
				$this->Request_payment_model->resetAuditQueriesOk();
			}
			$r->print_url = $this->Request_payment_model->build_print_url($r);
			if ($this->auditReadStatus() !== 1) {
				$printQueriesOk = false;
			}
			if (is_object($this->Request_payment_model) && method_exists($this->Request_payment_model, 'auditQueriesOk') && !$this->Request_payment_model->auditQueriesOk()) {
				$printQueriesOk = false;
			}
		}

		$queriesOk = $recordsQueryOk && $printQueriesOk;

		$this->template->set('header', $header);
		$this->template->set('records', $records);
		$this->template->title('Detail Record - ' . $header->no_pengajuan);
		try {
			$this->template->render('detail');
		} catch (Throwable $e) {
			$this->completeAudit('Gagal merender detail Record Request Payment', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'no_pengajuan' => isset($header->no_pengajuan) ? (string) $header->no_pengajuan : '',
				'row_count'    => is_array($records) ? count($records) : 0,
				'reason'       => 'render_exception',
			));
			throw $e;
		}

		$this->completeAudit(
			$queriesOk ? 'Membuka detail Record Request Payment' : 'Gagal membuka detail Record Request Payment',
			$queriesOk ? 1 : 0,
			array_merge(
				array(
					'id_pengajuan' => $id_pengajuan,
					'no_pengajuan' => isset($header->no_pengajuan) ? (string) $header->no_pengajuan : '',
					'row_count'    => is_array($records) ? count($records) : 0,
				),
				$queriesOk ? array('outcome' => 'rendered') : array('reason' => 'query_failure')
			)
		);
	}

	/**
	 * Export Excel dari snapshot (tr_rp_record), bukan dari dokumen master.
	 * Filename: Record_Payment_{Company}_{NoPengajuan}.xlsx
	 */
	public function export_excel($id_pengajuan = null)
	{
		$id_pengajuan = (int) $id_pengajuan;
		$this->beginAudit(__FUNCTION__, array('id_pengajuan' => $id_pengajuan, 'format' => 'xlsx'), array('id_pengajuan', 'no_pengajuan', 'format', 'row_count'));
		$this->auth->restrict($this->viewPermission);

		set_time_limit(0);
		ini_set('memory_limit', '512M');

		$header  = $this->Record_request_payment_model->get_header($id_pengajuan);
		$headerQueryOk = $this->Record_request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1;
		$records = $this->Record_request_payment_model->get_records($id_pengajuan);
		$recordsQueryOk = $this->Record_request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1;

		if (empty($header) || empty($records)) {
			$queryOk = $headerQueryOk && $recordsQueryOk;
			$this->completeAudit('Gagal mengekspor Excel Record Request Payment', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'format'       => 'xlsx',
				'row_count'    => is_array($records) ? count($records) : 0,
				'reason'       => !$queryOk ? 'query_failure' : 'not_found',
			));
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
		$sheet->mergeCells('A1:N1');
		$sheet->mergeCells('A2:N2');
		$sheet->mergeCells('A3:N3');
		$sheet->mergeCells('A4:N4');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		// Kolom header tabel (baris 6)
		$headers = ['NO', 'NO. DOKUMEN', 'KATEGORI', 'REQUEST BY', 'KEPERLUAN', 'DPP', 'PPN', 'PPH23', 'PPH21', 'ADMIN', 'DIBAYARKAN', 'Nama', 'Bank', 'No. Rek'];
		$cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
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
		$sheet->getStyle('A6:N6')->applyFromArray($headerStyle);

		// Lebar kolom
		$widths = [
			'A' => 5, 'B' => 20, 'C' => 14, 'D' => 22, 'E' => 34,
			'F' => 15, 'G' => 12, 'H' => 12, 'I' => 12, 'J' => 10, 'K' => 16,
			'L' => 25, 'M' => 15, 'N' => 22
		];
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
			$sheet->setCellValue('L' . $row, !empty($r->bank_nama) ? $r->bank_nama : '-');
			$sheet->setCellValue('M' . $row, !empty($r->bank_name) ? $r->bank_name : '-');
			$sheet->setCellValueExplicit('N' . $row, !empty($r->bank_no_rek) ? (string) $r->bank_no_rek : '-', PHPExcel_Cell_DataType::TYPE_STRING);

			$sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray($bodyStyle);
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
		$sheet->getStyle('E' . $row . ':N' . $row)->getFont()->setBold(true);
		$sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');
		$sheet->getStyle('E' . $row . ':N' . $row)->applyFromArray($bodyStyle);

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

		try {
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		} catch (Throwable $e) {
			$this->completeAudit('Gagal mengekspor Excel Record Request Payment', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'no_pengajuan' => isset($header->no_pengajuan) ? (string) $header->no_pengajuan : '',
				'format'       => 'xlsx',
				'row_count'    => is_array($records) ? count($records) : 0,
				'reason'       => 'export_exception',
			));
			throw $e;
		}

		$queriesOk = $headerQueryOk && $recordsQueryOk;
		$this->completeAudit(
			$queriesOk ? 'Mengekspor Excel Record Request Payment' : 'Gagal mengekspor Excel Record Request Payment',
			$queriesOk ? 1 : 0,
			array_merge(
				array(
					'id_pengajuan' => $id_pengajuan,
					'no_pengajuan' => isset($header->no_pengajuan) ? (string) $header->no_pengajuan : '',
					'format'       => 'xlsx',
					'row_count'    => is_array($records) ? count($records) : 0,
				),
				$queriesOk ? array('outcome' => 'exported') : array('reason' => 'query_failure')
			)
		);
		$objWriter->save('php://output');
		exit;
	}

<<<<<<< HEAD
=======
	public function export_items_excel()
	{
		$search = $this->input->get_post('search');
		$search = is_scalar($search) ? trim((string) $search) : '';
		$this->beginAudit(__FUNCTION__, ['format' => 'xlsx', 'search_present' => $search !== ''], ['row_count']);
		$this->auth->restrict($this->viewPermission);
		$records = [];
		$bufferLevel = ob_get_level();
		$reason = 'query_failure';
		try {
			$summary = [];
			$response = $this->Record_request_payment_model->get_data_record_items($summary, [
				'start' => 0, 'length' => -1, 'search' => ['value' => $search],
			]);
			if (empty($summary['query_ok']) || $this->auditReadStatus() !== 1) {
				throw new RuntimeException('Record item query failed');
			}
			$records = $response['data'];
			if (!$records) {
				$this->completeAudit('Tidak ada data untuk ekspor Excel per transaksi', 0, ['row_count' => 0, 'reason' => 'not_found']);
				show_error('Tidak ada transaksi approved yang cocok untuk dicetak.', 404);
				return;
			}
			$reason = 'export_exception';
			$this->load->library('PHPExcel');
			$excel = new PHPExcel();
			$sheet = $excel->getActiveSheet();
			$sheet->setTitle('Record per Item');
			$sheet->setCellValue('A1', 'RECORD REQUEST PAYMENT PER TRANSAKSI');
			$sheet->setCellValue('A2', 'Dicetak pada: ' . date('d-m-Y H:i:s'));
			$sheet->mergeCells('A1:P1');
			$sheet->mergeCells('A2:P2');
			$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
			$headers = ['NO', 'NO. PENGAJUAN', 'TGL DIAJUKAN', 'TGL DISETUJUI', 'APPROVER', 'NO. DOKUMEN', 'KATEGORI', 'REQUEST BY', 'COMPANY', 'KEPERLUAN', 'DPP', 'PPN', 'PPH23', 'PPH21', 'ADMIN', 'DIBAYARKAN'];
			$widths = [6, 18, 22, 22, 20, 24, 18, 24, 18, 40, 18, 18, 18, 18, 18, 18];
			foreach ($headers as $i => $label) {
				$col = PHPExcel_Cell::stringFromColumnIndex($i);
				$sheet->setCellValue($col . '4', $label);
				$sheet->getColumnDimension($col)->setWidth($widths[$i]);
			}
			$sheet->getStyle('A4:P4')->applyFromArray([
				'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
				'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '3C8DBC']],
			]);
			$row = 5;
			$totals = array_fill(0, 6, 0.0);
			foreach ($records as $index => $record) {
				$sheet->setCellValue('A' . $row, $index + 1);
				$keys = ['no_pengajuan', 'submitted_at', 'decided_at', 'approver', 'no_dokumen', 'kategori', 'request_by', 'company_nama', 'keperluan'];
				foreach ($keys as $i => $key) {
					$sheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($i + 1) . $row, (string) ($record[$key] ?? ''), PHPExcel_Cell_DataType::TYPE_STRING);
				}
				$amounts = [(float) $record['dpp'], (float) $record['nilai_ppn'],
					$record['flag_pph23'] ? (float) $record['nilai_pph'] : 0,
					$record['flag_pph21'] ? (float) $record['nilai_pph'] : 0,
					(float) $record['admin'], (float) $record['dibayarkan']];
				foreach ($amounts as $i => $amount) {
					$sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($i + 10) . $row, $amount);
					$totals[$i] += $amount;
				}
				$row++;
			}
			$sheet->setCellValue('J' . $row, 'TOTAL');
			foreach ($totals as $i => $amount) {
				$sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($i + 10) . $row, $amount);
			}
			$sheet->getStyle('J' . $row . ':P' . $row)->getFont()->setBold(true);
			$sheet->getStyle('K5:P' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
			$sheet->getStyle('A5:P' . $row)->getAlignment()->setWrapText(true);
			$sheet->freezePane('A5');
			// Generate fully before sending headers or recording export success.
			ob_start();
			PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save('php://output');
			$content = ob_get_clean();
		} catch (Throwable $e) {
			while (ob_get_level() > $bufferLevel) { ob_end_clean(); }
			$this->completeAudit('Gagal mengekspor Excel per transaksi', 0, ['row_count' => count($records), 'reason' => $reason]);
			show_error('Gagal mengekspor data. Silakan coba lagi.', 500);
			return;
		}
		$this->completeAudit('Mengekspor Excel per transaksi Record Request Payment', 1, ['row_count' => count($records), 'outcome' => 'exported']);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Record_Request_Payment_per_Item.xlsx"');
		header('Cache-Control: max-age=0');
		echo $content;
	}

>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
	private function beginAudit($method, $data = array(), $keys = array())
	{
		$this->load->library('Module_audit_recorder');
		return $this->module_audit_recorder->begin(
			'record_request_payment',
			$method,
			array('data' => $this->auditSafeData($data)),
			array_unique(array_merge(array_keys($data), $keys, array('reason', 'outcome')))
		);
	}

	private function completeAudit($message, $status, $data = array())
	{
		return $this->module_audit_recorder->complete($message, $status, array(
			'data' => $this->auditSafeData($data),
			'sql'  => null,
		));
	}

	private function auditReadStatus()
	{
		$error = $this->db->error();
		return empty($error['code']) ? 1 : 0;
	}

	private function auditSafeData($data)
	{
		$out = array();
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$out[$key] = array();
				foreach (array_slice($value, 0, 100) as $item) {
					if (is_scalar($item) || $item === null) {
						$out[$key][] = is_string($item) ? mb_strcut($item, 0, 120, 'UTF-8') : $item;
					}
				}
			} elseif (is_scalar($value) || $value === null) {
				$out[$key] = is_string($value) ? mb_substr($value, 0, 512, 'UTF-8') : $value;
			}
		}
		return $out;
	}

	private function auditFilters()
	{
		$data = array();
		foreach (array('draw', 'start', 'length') as $key) {
			$value = $this->input->get_post($key);
			if (is_scalar($value) && mb_strlen((string) $value, 'UTF-8') <= 50) {
				$data[$key] = $value;
			}
		}
		$search = $this->input->get_post('search');
		$data['search_present'] = !empty(is_array($search) ? ($search['value'] ?? '') : $search);
		return $data;
	}
}
