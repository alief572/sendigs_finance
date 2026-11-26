<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once dirname(__DIR__).'/libraries/Rp_transfer_record.php';
require_once dirname(__DIR__).'/libraries/Rp_document_attachments.php';

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
		$this->template->set('rp_transfer_available', Rp_transfer::installed($this->db));
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
		$transferGroups = Rp_transfer_record::groups($this->db, $id_pengajuan);
		$docTransfers = [];
		foreach ($transferGroups as $group) {
			foreach ($group['docs'] as $doc) {
				$docTransfers[$doc['no_dokumen']] = $doc + ['group' => $group];
			}
		}
		$this->template->set('rp_transfers', $transferGroups);
		$this->template->set('rp_doc_transfers', $docTransfers);
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
		$transfers = Rp_transfer_record::groups($this->db, $id_pengajuan);
		// Format transfer hanya bila lengkap; kalau sebagian, format lama supaya tidak ada dokumen yang hilang.
		if ($transfers && !Rp_transfer_record::hasDocumentsOutside($this->db, $id_pengajuan)) {
			$first = reset($transfers);
			$this->export_plan_cash_flow($transfers,
				'Pengajuan Pembayaran ' . $first['no_pengajuan'] . ' (' . tgl_indo(date('Y-m-d', strtotime($first['decided_at']))) . ') - Pengajuan',
				'Company: ' . $first['company_nama'] . ' | Diputuskan: ' . $first['decided_at'] . ' | Dicetak pada: ' . date('d-m-Y H:i:s'),
				'Record_Request_Payment_' . $first['no_pengajuan'] . '.xlsx', true);
			return;
		}

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

	public function export_transfer_batches()
	{
		$this->beginAudit(__FUNCTION__, [], ['row_count', 'format']);
		$this->auth->restrict($this->viewPermission);
		try {
			$filter = Rp_transfer_record::filters((array) $this->input->get());
			$groups = Rp_transfer_record::groups($this->db, null, $filter);
			if ($this->input->method() === 'post') {
				$selected = $this->input->post('batches');
				if (!is_array($selected) || !$selected || array_filter($selected, static function ($id) { return !is_string($id) || !ctype_digit($id); })) {
					throw new DomainException('Pilih minimal satu pengajuan.');
				}
				$groups = array_filter($groups, static function ($group) use ($selected) {
					return in_array((string) $group['id_pengajuan'], $selected, true);
				});
				if (!$groups) {
					throw new DomainException('Pengajuan yang dipilih sudah tidak cocok dengan filter.');
				}
				$status = ['unpaid' => 'Belum dibayar', 'paid' => 'Sudah dibayar', '' => 'Semua'][$filter['status']];
				$numbers = array_values(array_unique(array_column($groups, 'no_pengajuan')));
				$this->export_plan_cash_flow($groups, 'Pengajuan Pembayaran (' . $filter['from'] . ' s/d ' . $filter['to'] . ') - Pengajuan',
					'Tanggal diputuskan: ' . $filter['from'] . ' s/d ' . $filter['to'] . ' | Status pembayaran: ' . $status
					. ' | Kategori: ' . ($filter['category'] ?: 'Semua') . ' | No. Pengajuan: ' . implode(', ', $numbers) . ' | Dicetak pada: ' . date('d-m-Y H:i:s'),
					'Pengajuan_Pembayaran_' . $filter['from'] . '_sd_' . $filter['to'] . '.xlsx');
				return;
			}
			$batches = Rp_transfer_record::exportBatches($this->db, $filter, $groups);
			$this->output->set_content_type('application/json')->set_output(json_encode(['status' => 1, 'batches' => $batches]));
			$this->completeAudit('Membaca pilihan pengajuan untuk Excel', 1, ['row_count' => count($batches)]);
		} catch (Throwable $e) {
			$this->completeAudit('Ekspor pengajuan gagal', 0, ['reason' => 'invalid_transfer_export']);
			$message = $e instanceof DomainException ? $e->getMessage() : 'Gagal memuat ekspor pengajuan.';
			if ($this->input->method() === 'post') {
				show_error($message, 400);
				return;
			}
			$this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['status' => 0, 'msg' => $message]));
		}
	}

	private function export_plan_cash_flow(array $groups, $title, $subtitle, $filename, $single = false)
	{
		try {
			$this->load->library('PHPExcel');
			$workbook = new PHPExcel();
			$sheet = $workbook->getActiveSheet();
			$sheet->setTitle('Record Payment');
			Rp_transfer_record::exportPlan($sheet, $groups, $title, $subtitle, $single);
			$this->send_workbook($workbook, $filename, count($groups));
		} catch (Throwable $e) {
			$this->completeAudit('Gagal membuat Excel Plan Cash Flow', 0, ['reason' => 'export_exception']);
			show_error('Gagal membuat Excel Plan Cash Flow.', 500);
		}
	}

	public function record_payment()
	{
		$this->beginAudit(__FUNCTION__, [], ['row_count', 'format']);
		$this->auth->restrict($this->viewPermission);
		try {
			$company = $this->input->get('company');
			$search = $this->input->get('q');
			$tab = $this->input->get('tab');
			if (!in_array($company, [null, '', '7','3','4'], true) || ($search !== null && !is_string($search)) || !in_array($tab, [null,'','diff','same'], true)) {
				throw new DomainException('Filter Record Payment tidak valid.');
			}
			$entries = [];
			foreach (Rp_transfer_record::groups($this->db, null, ['status' => 'paid']) as $group) {
				if ($company && $company !== (string) $group['company_id']) {
					continue;
				}
				$compare = Rp_transfer_record::comparison($group);
				$haystack = implode(' ', array_merge([$group['id_payment'], $group['no_pengajuan'], 'Transfer ' . $group['urutan'],
					$group['company_nama'], $group['nama'], $group['bank'], $group['no_rek'], $group['keterangan_pembayaran']],
					array_column($group['docs'], 'no_dokumen'), $compare['differences']));
				if (!$search || stripos($haystack, trim($search)) !== false) {
					$entries[] = $group + $compare;
				}
			}
			usort($entries, static function ($a, $b) { return [$b['tgl_bayar'], $b['id']] <=> [$a['tgl_bayar'], $a['id']]; });
			$counts = ['all' => count($entries), 'diff' => count(array_filter($entries, static function ($row) { return !empty($row['differences']); }))];
			if ($tab) {
				$entries = array_filter($entries, static function ($row) use ($tab) { return (bool) $row['differences'] === ($tab === 'diff'); });
			}
			if ($this->input->get('export') === '1') {
				$this->load->library('PHPExcel');
				$workbook = new PHPExcel();
				$sheet = $workbook->getActiveSheet();
				$sheet->setTitle('Record Payment');
				Rp_transfer_record::row($sheet, 1, ['NO','NO. PAYMENT','TGL BAYAR','NO. PENGAJUAN','TRANSFER','COMPANY','PENERIMA','REKENING','DOKUMEN',
					'DIBAYARKAN','ADMIN (RP)','PENANGGUNG ADMIN (RP)','PENANGGUNG ADMIN (PAYMENT)','BANK CHARGE (PAYMENT)',
					'TRANSFER DITERIMA (RP)','TRANSFER DITERIMA (PAYMENT)','KELUAR BANK (RP)','KELUAR BANK (PAYMENT)','SELISIH KELUAR BANK','PERBEDAAN','KETERANGAN PEMBAYARAN']);
				Rp_transfer_record::headline($sheet, 'A1:U1');
				$line = 1;
				foreach ($entries as $row) {
					Rp_transfer_record::row($sheet, $line + 1, [$line++, $row['id_payment'], $row['tgl_bayar'], $row['no_pengajuan'], 'Transfer ' . $row['urutan'],
						$row['company_nama'], $row['nama'], $row['bank'] . ' - ' . $row['no_rek'], implode(', ', array_column($row['docs'], 'no_dokumen')),
						$row['planned']['net'] / 100, $row['admin'] / 100, $row['plannedBearer'], $row['actualBearer'], $row['charge'] / 100,
						$row['planned']['transfer'] / 100, $row['received'] / 100, $row['planned']['bank'] / 100, $row['actualBank'] / 100,
						($row['actualBank'] - $row['planned']['bank']) / 100, implode('; ', $row['differences']), $row['keterangan_pembayaran']]);
				}
				$this->send_workbook($workbook, 'Record_Payment.xlsx', count($entries));
				return;
			}
			$this->template->set(compact('entries', 'company', 'search', 'tab', 'counts'));
			$this->template->title('Record Payment');
			$this->template->page_icon('fa fa-table');
			$this->template->render('record_payment');
			$this->completeAudit('Membuka Record Payment', 1, ['row_count' => count($entries)]);
		} catch (Throwable $e) {
			$this->completeAudit('Record Payment gagal dimuat', 0, ['reason' => 'record_payment_failure']);
			show_error($e instanceof DomainException ? $e->getMessage() : 'Gagal memuat Record Payment.', 400);
		}
	}

	public function transfer_file($id)
	{
		$this->auth->restrict($this->viewPermission);
		$this->beginAudit(__FUNCTION__, [], ['row_count']);
		$file = Rp_transfer::installed($this->db) ? Rp_transfer::write($this->db->query(
			"SELECT f.* FROM tr_rp_transfer_file f JOIN tr_rp_transfer t ON t.id=f.id_transfer
			 JOIN tr_rp_pengajuan_h h ON h.id=t.id_pengajuan
			 LEFT JOIN tr_rp_pengajuan_d d ON d.id=f.id_detail AND d.id_transfer=t.id
			 WHERE f.id=? AND h.status='done' AND h.deleted=0
			   AND ((f.id_detail IS NULL AND t.status='paid') OR d.decision='approved')",
			[(int) $id]
		))->row_array() : null;
		$this->completeAudit('Membuka lampiran transfer', $file ? 1 : 0, ['row_count' => $file ? 1 : 0]);
		if (!$file) {
			show_404();
			return;
		}
		$this->output->set_content_type($file['mime'])
			->set_header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($file['nama']))
			->set_header('X-Content-Type-Options: nosniff')->set_header('Cache-Control: private, no-store')
			->set_output($file['content']);
	}

	public function payment_detail($id)
	{
		$this->auth->restrict($this->viewPermission);
		$this->beginAudit(__FUNCTION__, [], ['row_count']);
		try {
			$group = $this->paidGroup($id);
			$proof = array_filter(Rp_transfer::files($this->db, $group['id']), static function ($file) { return $file['id_detail'] === null; });
			$journal = Rp_transfer_record::journalRows($this->db, $group['id_payment']);
			$data = compact('group', 'proof', 'journal') + Rp_transfer_record::comparison($group);
			if ($this->input->get('modal') === '1') {
				$this->load->view('payment_detail', $data + ['modal' => true]);
			} else {
				$this->template->set($data);
				$this->template->title('Record Payment');
				$this->template->render('payment_detail');
			}
			$this->completeAudit('Membuka detail Record Payment', 1, ['row_count' => count($group['docs'])]);
		} catch (Throwable $e) {
			$this->completeAudit('Detail Record Payment tidak tersedia', 0, ['reason' => 'payment_detail_failure']);
			show_error($e instanceof DomainException ? $e->getMessage() : 'Gagal memuat detail Payment.', 400);
		}
	}

	/** Print Voucher / Bukti Bank Keluar format BBK (2) per payment (RP-R-37/39), jurnal lengkap Debit/Kredit. */
	public function print_voucher($id)
	{
		$this->auth->restrict($this->viewPermission);
		$this->beginAudit(__FUNCTION__, [], ['row_count']);
		try {
			$group = $this->paidGroup($id);
			$journal = Rp_transfer_record::journalRows($this->db, $group['id_payment']);
			if (!$journal) {
				throw new DomainException('Jurnal payment belum tersedia.');
			}
			$voucherNo = Rp_transfer_record::voucherNo($this->db, $group);
			$this->load->view('print_voucher', compact('group', 'journal', 'voucherNo'));
			$this->completeAudit('Mencetak voucher payment', 1, ['row_count' => count($journal)]);
		} catch (Throwable $e) {
			$this->completeAudit('Voucher payment tidak tersedia', 0, ['reason' => 'print_voucher_failure']);
			show_error($e instanceof DomainException ? $e->getMessage() : 'Gagal memuat voucher.', 400);
		}
	}

	private function paidGroup($id)
	{
		$transfer = Rp_transfer_payment::find($this->db, (int) $id);
		$group = Rp_transfer_record::groups($this->db, $transfer['id_pengajuan'], ['status' => 'paid'])[$transfer['id']] ?? null;
		if (!$group) {
			throw new DomainException('Pembayaran tidak ditemukan.');
		}
		return $group;
	}

	public function print_transfer_document($id)
	{
		$this->auth->restrict($this->viewPermission);
		$this->beginAudit(__FUNCTION__, [], ['row_count']);
		$doc = Rp_transfer::installed($this->db) ? Rp_transfer::write($this->db->query(
			"SELECT d.* FROM tr_rp_pengajuan_d d JOIN tr_rp_transfer t ON t.id=d.id_transfer
			 JOIN tr_rp_pengajuan_h h ON h.id=t.id_pengajuan
			 WHERE d.id=? AND h.status='done' AND h.deleted=0 AND d.decision='approved'", [(int) $id]
		))->row() : null;
		if (!$doc) {
			$this->completeAudit('Dokumen print tidak ditemukan', 0, ['reason' => 'not_found']);
			show_404();
			return;
		}
		$this->load->model('request_payment/Request_payment_model');
		$source = clone $doc;
		$source->id = $doc->id_dokumen ?: $doc->no_dokumen;
		$original = $this->Request_payment_model->build_print_url($source);
		$files = Rp_transfer::write($this->db->select('id,nama,mime')->order_by('id')->get_where('tr_rp_transfer_file', [
			'id_transfer' => $doc->id_transfer, 'id_detail' => $doc->id,
		]))->result_array();
		$this->load->view('print_transfer_document', compact('doc', 'files', 'original'));
		$this->completeAudit('Mencetak dokumen transfer', 1, ['row_count' => count($files)]);
	}

	/** Isi modal Cetak Dokumen: ringkasan, lampiran awal, lampiran tambahan dan alamat cetak. Hanya membaca. */
	public function document_info()
	{
		$this->beginAudit(__FUNCTION__, [], ['id_pengajuan', 'row_count']);
		$this->auth->restrict($this->viewPermission);
		$batch = (int) $this->input->get('id_pengajuan');
		$number = $this->input->get('no_dokumen');
		try {
			if ($batch < 1 || !is_string($number) || $number === '' || strlen($number) > 100) {
				throw new DomainException('Dokumen tidak valid.');
			}
			$doc = Rp_transfer::write($this->db->query(
				"SELECT d.*, h.company_nama FROM tr_rp_pengajuan_d d JOIN tr_rp_pengajuan_h h ON h.id=d.id_pengajuan
				 WHERE h.id=? AND d.no_dokumen=? AND h.status='done' AND h.deleted=0 AND d.decision='approved' ORDER BY d.id LIMIT 1", [$batch, $number]
			))->row();
			if (!$doc) {
				throw new DomainException('Dokumen tidak ditemukan.');
			}
			$additional = [];
			if (!empty($doc->id_transfer) && Rp_transfer::installed($this->db)) {
				foreach (Rp_transfer::write($this->db->select('id,nama')->order_by('id')->get_where('tr_rp_transfer_file', [
					'id_transfer' => $doc->id_transfer, 'id_detail' => $doc->id,
				]))->result_array() as $file) {
					$additional[] = ['name' => $file['nama'], 'url' => site_url('record_request_payment/transfer_file/' . $file['id'])];
				}
			}
			if ($additional) {
				$print = site_url('record_request_payment/print_transfer_document/' . $doc->id);
			} else {
				$this->load->model('request_payment/Request_payment_model');
				$source = clone $doc;
				$source->id = $doc->id_dokumen ?: $doc->no_dokumen;
				$print = $this->Request_payment_model->build_print_url($source);
			}
			echo json_encode(['status' => 1, 'document' => [
				'no_dokumen' => $doc->no_dokumen, 'kategori' => Rp_transfer_record::category($doc->kategori), 'request_by' => $doc->request_by,
				// Keterangan asli dokumen (as is), bukan yang dilengkapi finance.
				'company' => $doc->company_nama, 'keperluan' => $doc->keperluan, 'dibayarkan' => $doc->dibayarkan,
			], 'initial' => Rp_document_attachments::initial($this->db, $doc->kategori, $doc->no_dokumen), 'additional' => $additional, 'print_url' => $print]);
			$this->completeAudit('Membuka pratinjau cetak dokumen', 1, ['id_pengajuan' => $batch, 'row_count' => 1]);
		} catch (Throwable $e) {
			$this->completeAudit('Pratinjau cetak dokumen gagal', 0, ['reason' => 'document_unavailable']);
			echo json_encode(['status' => 0, 'msg' => $e instanceof DomainException ? $e->getMessage() : 'Gagal memuat dokumen.']);
		}
	}

	private function send_workbook($workbook, $filename, $count)
	{
		$path = tempnam(sys_get_temp_dir(), 'rp-xlsx-');
		if (!$path) {
			throw new RuntimeException('Tidak bisa menyediakan file Excel sementara.');
		}
		try {
			PHPExcel_IOFactory::createWriter($workbook, 'Excel2007')->save($path);
			$bytes = file_get_contents($path);
			if ($bytes === false || $bytes === '') {
				throw new RuntimeException('Isi Excel tidak tersedia.');
			}
			$this->completeAudit('Mengekspor Excel pembayaran', 1, ['row_count' => $count, 'format' => 'xlsx']);
			$this->output->set_content_type('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
				->set_header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $filename) . '"')
				->set_output($bytes);
		} finally {
			unlink($path);
		}
	}

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
