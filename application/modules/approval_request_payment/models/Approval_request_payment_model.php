<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!defined('DBCNL')) define('DBCNL', 'db_consultant_new');

/*
 * Model: Approval Request Payment (alur batch baru - FSD 2026-09)
 */

class Approval_request_payment_model extends BF_Model
{
	protected $table_name = 'tr_rp_pengajuan_h';
	protected $key        = 'id';
	protected $soft_deletes = false;
	protected $set_created  = false;
	protected $set_modified = false;
	protected $consultant   = null;
	private $auditQueriesOk = true;

	public function __construct()
	{
		parent::__construct();
		try {
			$this->consultant = $this->load->database('consultant', true);
		} catch (Exception $e) {
			$this->consultant = null;
		}
	}

	public function auditQueriesOk()
	{
		return $this->auditQueriesOk;
	}

	public function resetAuditQueriesOk()
	{
		$this->auditQueriesOk = true;
	}

	private function trackAuditDbError($connection = null)
	{
		$conn = $connection ?: $this->db;
		if (is_object($conn)) {
			$err = $conn->error();
			if (!empty($err['code'])) {
				$this->auditQueriesOk = false;
			}
		}
	}

	// Daftar per dokumen tetap memakai status header sebagai tahap approval.

	public function get_data_pending_items(&$auditSummary = null)
	{
		$this->resetAuditQueriesOk();
		$post = $this->input->post();
		$post = is_array($post) ? $post : [];
		$draw = isset($post['draw']) && is_scalar($post['draw']) ? (int) $post['draw'] : 0;
		$start = isset($post['start']) && is_scalar($post['start']) ? max(0, (int) $post['start']) : 0;
		$length = isset($post['length']) && is_scalar($post['length']) ? (int) $post['length'] : 25;
		$length = $length === -1 ? -1 : min(100, max(1, $length));
		$search = $post['search']['value'] ?? '';
		$search = is_scalar($search) ? trim((string) $search) : '';

		$this->db->from('tr_rp_pengajuan_d d');
		$this->db->join('tr_rp_pengajuan_h h', 'h.id = d.id_pengajuan');
		$this->db->where('h.deleted', 0);
		$this->db->where('h.status', 'pending');
		$total = (int) $this->db->count_all_results('', false);
		$this->trackAuditDbError();
		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('d.no_dokumen', $search);
			foreach (['h.no_pengajuan', 'd.request_by', 'd.keperluan', 'd.kategori', 'h.company_nama'] as $field) {
				$this->db->or_like($field, $search);
			}
			$this->db->group_end();
		}
		$filtered = (int) $this->db->count_all_results('', false);
		$this->trackAuditDbError();
		$this->db->select('d.*, h.no_pengajuan, h.company_nama, h.submitted_at', false);
		$this->db->order_by('h.submitted_at', 'desc');
		$this->db->order_by('d.id', 'desc');
		if ($length !== -1) {
			$this->db->limit($length, $start);
		}
		$query = $this->db->get();
		$this->trackAuditDbError();
		if (!is_object($query)) {
			$this->auditQueriesOk = false;
		}
		$rows = is_object($query) ? $query->result() : [];
		$data = [];
		foreach ($rows as $row) {
			$item = (array) $row;
			foreach (['id', 'id_pengajuan', 'flag_ppn', 'flag_pph23', 'flag_pph21', 'admin'] as $key) {
				$item[$key] = (int) $item[$key];
			}
			foreach (['dpp', 'nilai_ppn', 'nilai_pph', 'dibayarkan'] as $key) {
				$item[$key] = (float) $item[$key];
			}
			foreach (['submitted_at'] as $key) {
				$item[$key] = !empty($item[$key]) ? date('d-M-Y H:i', strtotime($item[$key])) : '';
			}
			$data[] = $item;
		}
		$auditSummary = [
			'query_ok' => $this->auditQueriesOk(),
			'row_count' => count($data),
			'records_total' => $total,
			'records_filtered' => $filtered,
		];
		return ['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data];
	}

	// Server-side DataTables: batch pending
	public function get_data_pending(&$auditSummary = null)
	{
		$this->auditQueriesOk = true;
		$auditSummary = array(
			'queries_ok'       => true,
			'row_count'        => 0,
			'records_total'    => 0,
			'records_filtered' => 0,
		);
		$post   = $this->input->post();
		$draw   = isset($post['draw']) ? intval($post['draw']) : 0;
		$length = isset($post['length']) ? intval($post['length']) : 25;
		$start  = isset($post['start']) ? intval($post['start']) : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$build = function () {
			$this->db->from('tr_rp_pengajuan_h h');
			$this->db->where('h.deleted', 0);
			$this->db->where('h.status', 'pending');
		};

		// susun from/where SEKALI (count_all_results tanpa reset -> jangan append ulang)
		$build();
		$recordsTotal = $this->db->count_all_results('', false);
		$this->trackAuditDbError();

		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('h.no_pengajuan', $search, 'both');
			$this->db->or_like('h.company_nama', $search, 'both');
			$this->db->group_end();
		}
		$recordsFiltered = $this->db->count_all_results('', false);
		$this->trackAuditDbError();

		$this->db->select('h.*');
		$this->db->order_by('h.submitted_at', 'desc');
		$this->db->limit($length, $start);
		$query = $this->db->get();
		$this->trackAuditDbError();
		$rows = $query ? $query->result() : [];

		$data = [];
		foreach ($rows as $r) {
			$data[] = [
				'no_pengajuan'     => $r->no_pengajuan,
				'company_nama'     => $r->company_nama ? $r->company_nama : '-',
				'submitted_at'     => !empty($r->submitted_at) ? date('d-M-Y H:i', strtotime($r->submitted_at)) : '',
				'jumlah_dokumen'   => (int) $r->jumlah_dokumen,
				'total_dibayarkan' => number_format($r->total_dibayarkan, 0, ',', '.'),
				'aksi'             => $r->id,
			];
		}

		$auditSummary['queries_ok']       = $this->auditQueriesOk;
		$auditSummary['row_count']        = count($data);
		$auditSummary['records_total']    = (int) $recordsTotal;
		$auditSummary['records_filtered'] = (int) $recordsFiltered;

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $recordsTotal,
			'recordsFiltered' => $recordsFiltered,
			'data'            => $data,
		]);
	}

	public function get_batch_header($id_pengajuan)
	{
		$this->db->from('tr_rp_pengajuan_h');
		$this->db->where('id', (int) $id_pengajuan);
		$this->db->where('deleted', 0);
		$q = $this->db->get();
		$this->trackAuditDbError();
		return $q ? $q->row() : null;
	}

	public function get_batch_items($id_pengajuan)
	{
		$this->db->from('tr_rp_pengajuan_d');
		$this->db->where('id_pengajuan', (int) $id_pengajuan);
		$this->db->order_by('id', 'asc');
		$q = $this->db->get();
		$this->trackAuditDbError();
		$items = $q ? $q->result() : [];

		foreach ($items as $it) {
			$kat = strtolower(trim((string)$it->kategori));
			if ($kat === 'direct payment' || $kat === 'direct_payment' || strpos($it->no_dokumen, 'DPM') === 0 || strpos($it->no_dokumen, 'DP-') === 0) {
				$is_cash = ($kat === 'cash');
				if (!$is_cash) {
					$cashQ = $this->db->get_where('tr_pr_non_po', ['no_non_po' => $it->no_dokumen]);
					$this->trackAuditDbError();
					$is_cash = $cashQ && ($cashQ->num_rows() > 0);
				}
				if (!$is_cash && $this->consultant) {
					$konsQ = $this->consultant->select('created_by')->get_where('kons_tr_kasbon_project_header', ['id' => $it->no_dokumen]);
					$this->trackAuditDbError($this->consultant);
					$kons = $konsQ ? $konsQ->row() : null;
					if (!empty($kons) && !empty($kons->created_by)) {
						$uQ = $this->consultant->query("SELECT nm_lengkap, username FROM users WHERE id_user = ? OR username = ?", [$kons->created_by, $kons->created_by]);
						$this->trackAuditDbError($this->consultant);
						$u = $uQ ? $uQ->row() : null;
						if (!empty($u) && !empty($u->nm_lengkap)) {
							$it->request_by = $u->nm_lengkap;
						} elseif (!empty($u) && !empty($u->username)) {
							$it->request_by = $u->username;
						}
					}
				}
			}
		}

		return $items;
	}

	/* =====================================================================
	 * INTEGRASI ke payment_approve (feed ke modul pembayaran_material).
	 * Saat dokumen di-approve, selain snapshot ke tr_rp_record, dibuatkan
	 * row payment_approve status=1 agar muncul di v_list_payment (status<>2).
	 * ===================================================================== */

	// Mapping kategori (v_request_payment) -> tipe (payment_approve)
	public function map_kategori_to_tipe($kategori)
	{
		$k = strtolower(trim((string) $kategori));
		$map = [
			'kasbon'            => 'kasbon',
			'transport'         => 'transportasi',
			'transportasi'      => 'transportasi',
			'expense'           => 'expense',
			'periodik'          => 'periodik',
			'cash'              => 'cash',
			'direct payment'    => 'direct_payment',
			'direct_payment'    => 'direct_payment',
			'petty cash hutang' => 'petty_cash_hutang',
			'petty_cash_hutang' => 'petty_cash_hutang',
			'petty cash'        => 'petty_cash',
			'petty_cash'        => 'petty_cash',
			'refill pettycash'  => 'refill_pettycash',
			'refill_pettycash'  => 'refill_pettycash',
			'non-po'            => 'nonpo',
			'nonpo'             => 'nonpo',
		];
		return isset($map[$k]) ? $map[$k] : $k;
	}

	// Generate id payment_approve (BK-{kode_bank}-{my}-{urut}); kode_bank kosong utk alur baru
	public function generate_id_payment($kode_bank = null)
	{
		$q = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve WHERE id LIKE '%BK-" . $kode_bank . "-" . date('my-') . "%'");
		$this->trackAuditDbError();
		$generate_id = $q ? $q->row() : null;
		$kodeBarang = $generate_id ? $generate_id->max_id : '';
		if ($kode_bank == null) {
			$urutan = (int) substr($kodeBarang, 9, 4);
		} else {
			$urutan = (int) substr($kodeBarang, 16, 4);
		}
		$urutan++;
		return "BK-" . $kode_bank . "-" . date('my-') . sprintf("%04s", $urutan);
	}

	// tgl dokumen asli dari v_request_payment (untuk tgl_doc)
	public function get_tgl_doc($no_dokumen)
	{
		$q = $this->db->select('tanggal')->from('v_request_payment')->where('no_dokumen', $no_dokumen)->get();
		$this->trackAuditDbError();
		$row = $q ? $q->row() : null;
		return (!empty($row) && !empty($row->tanggal)) ? $row->tanggal : null;
	}

	// idempotency guard: apakah no_doc sudah ada di payment_approve
	public function payment_exists($no_doc)
	{
		$q = $this->db->get_where('payment_approve', ['no_doc' => $no_doc]);
		$this->trackAuditDbError();
		return $q ? ($q->num_rows() > 0) : false;
	}
}
