<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Controller: Approval Request Payment (alur batch baru - FSD 2026-09)
 * Daftar pengajuan (batch) pending -> detail approve/reject per dokumen ->
 * Simpan Keputusan (snapshot Record + reject log + finalize batch).
 */

class Approval_request_payment extends Admin_Controller
{
	// Permission (reuse Request_Payment.*)
	protected $viewPermission   = "Approval_Request_Payment.View";
	protected $addPermission    = "Approval_Request_Payment.Add";
	protected $managePermission = "Approval_Request_Payment.Manage";
	protected $deletePermission = "Approval_Request_Payment.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Approval_request_payment/Approval_request_payment_model', 'All/All_model'));
		$this->template->title('Approval Request Payment');
		$this->template->page_icon('fa fa-check-square-o');
		date_default_timezone_set("Asia/Bangkok");
	}

	// Index: daftar batch pending
	public function index()
	{
		$this->beginAudit(__FUNCTION__, array(), array());
		$this->auth->restrict($this->viewPermission);
		$this->template->title('Approval Request Payment');
		try {
			$this->template->render('index');
		} catch (Throwable $e) {
			$this->completeAudit('Gagal merender halaman Approval Request Payment', 0, array('reason' => 'render_exception'));
			throw $e;
		}
		$ok = $this->auditReadStatus() === 1;
		$this->completeAudit(
			$ok ? 'Membuka daftar Approval Request Payment' : 'Gagal membuka daftar Approval Request Payment',
			$ok ? 1 : 0,
			$ok ? array('outcome' => 'rendered') : array('reason' => 'query_failure')
		);
	}

	// Server-side DataTables: batch pending
	public function get_data_pending()
	{
		$this->beginAudit(__FUNCTION__, $this->auditFilters(), array('row_count', 'records_total', 'records_filtered'));
		$auditSummary = array();
		$this->Approval_request_payment_model->get_data_pending($auditSummary);
		$ok = !empty($auditSummary['queries_ok']) && $this->auditReadStatus() === 1;
		$this->completeAudit(
			$ok ? 'Membaca daftar batch pending Approval Request Payment' : 'Gagal membaca daftar batch pending Approval Request Payment',
			$ok ? 1 : 0,
			array_merge(
				array(
					'row_count'        => (int) ($auditSummary['row_count'] ?? 0),
					'records_total'    => (int) ($auditSummary['records_total'] ?? 0),
					'records_filtered' => (int) ($auditSummary['records_filtered'] ?? 0),
				),
				$ok ? array('outcome' => 'listed') : array('reason' => 'query_failure')
			)
		);
	}

	// Halaman detail 1 batch (approve/reject per dokumen)
	public function detail($id_pengajuan = null)
	{
		$id_pengajuan = (int) $id_pengajuan;
		$this->beginAudit(__FUNCTION__, array('id_pengajuan' => $id_pengajuan), array('id_pengajuan', 'no_pengajuan', 'item_count'));
		$this->auth->restrict($this->viewPermission);

		$this->Approval_request_payment_model->resetAuditQueriesOk();
		$header = $this->Approval_request_payment_model->get_batch_header($id_pengajuan);
		$queriesOk = $this->Approval_request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1;

		if (empty($header) || $header->status !== 'pending') {
			$this->completeAudit('Gagal membuka detail Approval Request Payment', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'reason'       => !$queriesOk ? 'query_failure' : (empty($header) ? 'not_found' : 'already_decided'),
			));
			$this->session->set_flashdata('message', '<div class="alert alert-warning">Pengajuan tidak ditemukan atau sudah diputuskan.</div>');
			redirect(site_url('approval_request_payment'));
			return;
		}

		$items = $this->Approval_request_payment_model->get_batch_items($id_pengajuan);
		$queriesOk = $this->Approval_request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1 && $queriesOk;

		// Tambahkan URL print dokumen sumber per item (pakai helper dari modul request_payment)
		$this->load->model('request_payment/Request_payment_model');
		foreach ($items as $it) {
			if (is_object($this->Request_payment_model) && method_exists($this->Request_payment_model, 'resetAuditQueriesOk')) {
				$this->Request_payment_model->resetAuditQueriesOk();
			}
			$it->print_url = $this->Request_payment_model->build_print_url($it);
			$queriesOk = $this->auditReadStatus() === 1 && $queriesOk;
			if (is_object($this->Request_payment_model) && method_exists($this->Request_payment_model, 'auditQueriesOk')) {
				$queriesOk = $this->Request_payment_model->auditQueriesOk() && $queriesOk;
			}
		}

		$this->template->set('header', $header);
		$this->template->set('items', $items);
		$this->template->title('Detail Approval - ' . $header->no_pengajuan);
		try {
			$this->template->render('detail');
		} catch (Throwable $e) {
			$this->completeAudit('Gagal merender detail Approval Request Payment', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'no_pengajuan' => (string) $header->no_pengajuan,
				'item_count'   => count($items),
				'reason'       => 'render_exception',
			));
			throw $e;
		}
		$this->completeAudit(
			$queriesOk ? 'Membuka detail Approval Request Payment' : 'Gagal membuka detail Approval Request Payment',
			$queriesOk ? 1 : 0,
			array_merge(
				array(
					'id_pengajuan' => $id_pengajuan,
					'no_pengajuan' => (string) $header->no_pengajuan,
					'item_count'   => count($items),
				),
				$queriesOk ? array('outcome' => 'rendered') : array('reason' => 'query_failure')
			)
		);
	}

	/**
	 * Simpan Keputusan.
	 * - Validasi: setiap dokumen di-reject wajib punya alasan (non-kosong/whitespace).
	 * - approved -> insert snapshot ke tr_rp_record, set decision approved.
	 * - rejected -> set decision rejected + insert entry BARU ke tr_rp_reject_log (append-only).
	 * - batch -> status done, decided_at, approver.
	 */
	public function save_decision()
	{
		$id_pengajuan = (int) $this->input->post('id_pengajuan');
		$this->beginAudit(__FUNCTION__, array('id_pengajuan' => $id_pengajuan), array(
			'id_pengajuan', 'no_pengajuan', 'document_ids', 'document_count',
			'approved_count', 'rejected_count', 'payment_ids',
		));

		if (!has_permission($this->managePermission)) {
			$this->completeAudit('Keputusan approval ditolak karena tidak memiliki izin', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'reason'       => 'permission_denied',
			));
			echo json_encode(['status' => 0, 'msg' => 'Anda tidak memiliki hak untuk memutuskan.']);
			return;
		}

		$decisions = $this->input->post('decisions'); // [{no_dokumen, decision(approved|rejected), alasan}]

		$this->Approval_request_payment_model->resetAuditQueriesOk();
		$header = $this->Approval_request_payment_model->get_batch_header($id_pengajuan);
		if (!$this->Approval_request_payment_model->auditQueriesOk() || $this->auditReadStatus() === 0) {
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'reason'       => 'query_failure',
			));
			echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan keputusan. Silakan coba lagi.']);
			return;
		}
		if (empty($header) || $header->status !== 'pending') {
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'reason'       => empty($header) ? 'not_found' : 'already_decided',
			));
			echo json_encode(['status' => 0, 'msg' => 'Pengajuan tidak ditemukan atau sudah diputuskan.']);
			return;
		}

		if (empty($decisions) || !is_array($decisions)) {
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'no_pengajuan' => (string) $header->no_pengajuan,
				'reason'       => 'invalid_decisions',
			));
			echo json_encode(['status' => 0, 'msg' => 'Data keputusan tidak valid.']);
			return;
		}

		// index decision by no_dokumen
		$dec_map = [];
		foreach ($decisions as $d) {
			if (!empty($d['no_dokumen'])) {
				$dec_map[$d['no_dokumen']] = [
					'decision' => (isset($d['decision']) && $d['decision'] === 'rejected') ? 'rejected' : 'approved',
					'alasan'   => isset($d['alasan']) ? trim($d['alasan']) : '',
				];
			}
		}

		$this->Approval_request_payment_model->resetAuditQueriesOk();
		$items = $this->Approval_request_payment_model->get_batch_items($id_pengajuan);
		if (!$this->Approval_request_payment_model->auditQueriesOk() || $this->auditReadStatus() === 0) {
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'no_pengajuan' => (string) $header->no_pengajuan,
				'reason'       => 'query_failure',
			));
			echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan keputusan. Silakan coba lagi.']);
			return;
		}
		if (empty($items)) {
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'no_pengajuan' => (string) $header->no_pengajuan,
				'reason'       => 'empty_batch',
			));
			echo json_encode(['status' => 0, 'msg' => 'Batch tidak memiliki dokumen.']);
			return;
		}

		// Validasi alasan reject wajib
		foreach ($items as $it) {
			$dec = isset($dec_map[$it->no_dokumen]) ? $dec_map[$it->no_dokumen] : ['decision' => 'approved', 'alasan' => ''];
			if ($dec['decision'] === 'rejected' && $dec['alasan'] === '') {
				$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
					'id_pengajuan' => $id_pengajuan,
					'no_pengajuan' => (string) $header->no_pengajuan,
					'reason'       => 'missing_reject_reason',
				));
				echo json_encode(['status' => 0, 'msg' => 'Alasan reject wajib diisi untuk dokumen ' . $it->no_dokumen . '.']);
				return;
			}
		}

		$now  = date('Y-m-d H:i:s');
		$user = $this->auth->user_name();

		if ($this->db->trans_begin() === false) {
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan' => $id_pengajuan,
				'no_pengajuan' => (string) $header->no_pengajuan,
				'reason'       => 'trans_begin_failure',
			));
			echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan keputusan. Silakan coba lagi.']);
			return;
		}

		$this->Approval_request_payment_model->resetAuditQueriesOk();
		$writeOk       = true;
		$documentIds   = [];
		$paymentIds    = [];
		$approvedCount = 0;
		$rejectedCount = 0;
		$record_rows   = [];

		foreach ($items as $it) {
			$documentIds[] = (string) $it->no_dokumen;
			$dec = isset($dec_map[$it->no_dokumen]) ? $dec_map[$it->no_dokumen] : ['decision' => 'approved', 'alasan' => ''];

			if ($dec['decision'] === 'approved') {
				$approvedCount++;
				$writeOk = (bool) $this->db->update('tr_rp_pengajuan_d', ['decision' => 'approved'], ['id' => $it->id]) && $writeOk;

				$record_rows[] = [
					'id_pengajuan' => $id_pengajuan,
					'no_pengajuan' => $header->no_pengajuan,
					'no_dokumen'   => $it->no_dokumen,
					'kategori'     => $it->kategori,
					'request_by'   => $it->request_by,
					'company_nama' => $header->company_nama,
					'keperluan'    => $it->keperluan,
					'dpp'          => $it->dpp,
					'flag_ppn'     => $it->flag_ppn,
					'flag_pph23'   => $it->flag_pph23,
					'flag_pph21'   => $it->flag_pph21,
					'nilai_ppn'    => $it->nilai_ppn,
					'nilai_pph'    => $it->nilai_pph,
					'admin'        => $it->admin,
					'dibayarkan'   => $it->dibayarkan,
					'created_on'   => $now,
				];

				// ==== Integrasi ke payment_approve (feed modul pembayaran_material) ====
				// Idempotent: skip kalau no_doc sudah ada di payment_approve.
				if (!$this->Approval_request_payment_model->payment_exists($it->no_dokumen)) {
					$tipe     = $this->Approval_request_payment_model->map_kategori_to_tipe($it->kategori);
					$id_pay   = $this->Approval_request_payment_model->generate_id_payment(null);
					$tgl_doc  = $this->Approval_request_payment_model->get_tgl_doc($it->no_dokumen);
					$tipe_pph = $it->flag_pph21 ? '21' : ($it->flag_pph23 ? '23' : null);
					$paymentIds[] = (string) $id_pay;

					$writeOk = (bool) $this->db->insert('payment_approve', [
						'id'            => $id_pay,
						'no_doc'        => $it->no_dokumen,
						'nama'          => $it->request_by,
						'tgl_doc'       => $tgl_doc,
						'keperluan'     => $it->keperluan,
						'tipe'          => $tipe,
						'jumlah'        => $it->dpp,        // DPP murni (ppn/pph/admin dihitung terpisah di form pembayaran)
						'status'        => 1,               // agar lolos v_list_payment (status<>2)
						'tanggal'       => $now,
						'created_by'    => $user,
						'created_on'    => $now,
						'approved_by'   => $user,
						'approved_on'   => $now,
						'ids'           => $it->id_dokumen,
						'currency'      => 'IDR',
						'admin_bank'    => $it->admin,
						'total_pph'     => $it->nilai_pph,
						'tipe_pph'      => $tipe_pph,
						// kolom NOT NULL tanpa default -> isi 0 (nilai bayar aktual diisi di tahap pembayaran)
						'total_ppn'     => $it->nilai_ppn,
						'payment_bank'  => 0,
						'total_payment' => 0,
						'selisih'       => 0,
						'kurs_payment'  => 0,
					]) && $writeOk;
				}
			} else {
				$rejectedCount++;
				$writeOk = (bool) $this->db->update('tr_rp_pengajuan_d', ['decision' => 'rejected'], ['id' => $it->id]) && $writeOk;

				// append-only reject log (tidak menimpa)
				$writeOk = (bool) $this->db->insert('tr_rp_reject_log', [
					'no_dokumen'   => $it->no_dokumen,
					'id_pengajuan' => $id_pengajuan,
					'no_pengajuan' => $header->no_pengajuan,
					'approver'     => $user,
					'rejected_at'  => $now,
					'alasan'       => $dec['alasan'],
				]) && $writeOk;
			}
		}

		if (!empty($record_rows)) {
			$writeOk = (bool) $this->db->insert_batch('tr_rp_record', $record_rows) && $writeOk;
		}

		// finalize batch
		$writeOk = (bool) $this->db->update('tr_rp_pengajuan_h', [
			'status'      => 'done',
			'decided_at'  => $now,
			'approver'    => $user,
			'modified_by' => $user,
			'modified_on' => $now,
		], ['id' => $id_pengajuan]) && $writeOk;

		$queriesOk = $this->Approval_request_payment_model->auditQueriesOk() && $this->auditReadStatus() === 1;
		if (!$writeOk || !$queriesOk || $this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan'   => $id_pengajuan,
				'no_pengajuan'   => (string) $header->no_pengajuan,
				'document_ids'   => array_slice($documentIds, 0, 100),
				'document_count' => count($documentIds),
				'reason'         => !$writeOk ? 'write_failure' : (!$queriesOk ? 'query_failure' : 'transaction_failure'),
			));
			echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan keputusan. Silakan coba lagi.']);
			return;
		}

		if ($this->db->trans_commit() === false) {
			$this->db->trans_rollback();
			$this->completeAudit('Gagal menyimpan keputusan approval', 0, array(
				'id_pengajuan'   => $id_pengajuan,
				'no_pengajuan'   => (string) $header->no_pengajuan,
				'document_ids'   => array_slice($documentIds, 0, 100),
				'document_count' => count($documentIds),
				'reason'         => 'trans_commit_failure',
			));
			echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan keputusan. Silakan coba lagi.']);
			return;
		}

		$this->completeAudit('Menyimpan keputusan Approval Request Payment', 1, array(
			'outcome'        => 'saved',
			'id_pengajuan'   => $id_pengajuan,
			'no_pengajuan'   => (string) $header->no_pengajuan,
			'document_ids'   => array_slice($documentIds, 0, 100),
			'document_count' => count($documentIds),
			'approved_count' => $approvedCount,
			'rejected_count' => $rejectedCount,
			'payment_ids'    => array_slice($paymentIds, 0, 100),
		));
		echo json_encode(['status' => 1, 'msg' => 'Keputusan berhasil disimpan.']);
	}

	private function beginAudit($method, $data = array(), $keys = array())
	{
		$this->load->library('Module_audit_recorder');
		return $this->module_audit_recorder->begin(
			'approval_request_payment',
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
