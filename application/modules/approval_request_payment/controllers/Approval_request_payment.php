<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Controller: Approval Request Payment (alur batch baru - FSD 2026-09)
 * Daftar pengajuan (batch) pending -> detail approve/reject per dokumen ->
 * Simpan Keputusan (snapshot Record + reject log + finalize batch).
 */

class Approval_request_payment extends Admin_Controller
{
	// Permission (reuse Request_Payment.*)
	protected $viewPermission   = "Request_Payment.View";
	protected $addPermission    = "Request_Payment.Add";
	protected $managePermission = "Request_Payment.Manage";
	protected $deletePermission = "Request_Payment.Delete";

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
		$this->auth->restrict($this->viewPermission);
		$this->template->title('Approval Request Payment');
		$this->template->render('index');
	}

	// Server-side DataTables: batch pending
	public function get_data_pending()
	{
		$this->Approval_request_payment_model->get_data_pending();
	}

	// Halaman detail 1 batch (approve/reject per dokumen)
	public function detail($id_pengajuan = null)
	{
		$this->auth->restrict($this->viewPermission);

		$id_pengajuan = (int) $id_pengajuan;
		$header = $this->Approval_request_payment_model->get_batch_header($id_pengajuan);

		if (empty($header) || $header->status !== 'pending') {
			$this->session->set_flashdata('message', '<div class="alert alert-warning">Pengajuan tidak ditemukan atau sudah diputuskan.</div>');
			redirect(site_url('approval_request_payment'));
			return;
		}

		$items = $this->Approval_request_payment_model->get_batch_items($id_pengajuan);

		$this->template->set('header', $header);
		$this->template->set('items', $items);
		$this->template->title('Detail Approval - ' . $header->no_pengajuan);
		$this->template->render('detail');
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
		if (!has_permission($this->managePermission)) {
			echo json_encode(['status' => 0, 'msg' => 'Anda tidak memiliki hak untuk memutuskan.']);
			return;
		}

		$id_pengajuan = (int) $this->input->post('id_pengajuan');
		$decisions    = $this->input->post('decisions'); // [{no_dokumen, decision(approved|rejected), alasan}]

		$header = $this->Approval_request_payment_model->get_batch_header($id_pengajuan);
		if (empty($header) || $header->status !== 'pending') {
			echo json_encode(['status' => 0, 'msg' => 'Pengajuan tidak ditemukan atau sudah diputuskan.']);
			return;
		}

		if (empty($decisions) || !is_array($decisions)) {
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

		$items = $this->Approval_request_payment_model->get_batch_items($id_pengajuan);
		if (empty($items)) {
			echo json_encode(['status' => 0, 'msg' => 'Batch tidak memiliki dokumen.']);
			return;
		}

		// Validasi alasan reject wajib
		foreach ($items as $it) {
			$dec = isset($dec_map[$it->no_dokumen]) ? $dec_map[$it->no_dokumen] : ['decision' => 'approved', 'alasan' => ''];
			if ($dec['decision'] === 'rejected' && $dec['alasan'] === '') {
				echo json_encode(['status' => 0, 'msg' => 'Alasan reject wajib diisi untuk dokumen ' . $it->no_dokumen . '.']);
				return;
			}
		}

		$now  = date('Y-m-d H:i:s');
		$user = $this->auth->user_name();

		$this->db->trans_begin();

		$record_rows = [];
		foreach ($items as $it) {
			$dec = isset($dec_map[$it->no_dokumen]) ? $dec_map[$it->no_dokumen] : ['decision' => 'approved', 'alasan' => ''];

			if ($dec['decision'] === 'approved') {
				$this->db->update('tr_rp_pengajuan_d', ['decision' => 'approved'], ['id' => $it->id]);

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

					$this->db->insert('payment_approve', [
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
					]);
				}
			} else {
				$this->db->update('tr_rp_pengajuan_d', ['decision' => 'rejected'], ['id' => $it->id]);

				// append-only reject log (tidak menimpa)
				$this->db->insert('tr_rp_reject_log', [
					'no_dokumen'   => $it->no_dokumen,
					'id_pengajuan' => $id_pengajuan,
					'no_pengajuan' => $header->no_pengajuan,
					'approver'     => $user,
					'rejected_at'  => $now,
					'alasan'       => $dec['alasan'],
				]);
			}
		}

		if (!empty($record_rows)) {
			$this->db->insert_batch('tr_rp_record', $record_rows);
		}

		// finalize batch
		$this->db->update('tr_rp_pengajuan_h', [
			'status'      => 'done',
			'decided_at'  => $now,
			'approver'    => $user,
			'modified_by' => $user,
			'modified_on' => $now,
		], ['id' => $id_pengajuan]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan keputusan. Silakan coba lagi.']);
			return;
		}

		$this->db->trans_commit();
		echo json_encode(['status' => 1, 'msg' => 'Keputusan berhasil disimpan.']);
	}
}
