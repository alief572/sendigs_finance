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

	public function __construct()
	{
		parent::__construct();
		try {
			$this->consultant = $this->load->database('consultant', true);
		} catch (Exception $e) {
			$this->consultant = null;
		}
	}

	// Server-side DataTables: batch pending
	public function get_data_pending()
	{
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

		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('h.no_pengajuan', $search, 'both');
			$this->db->or_like('h.company_nama', $search, 'both');
			$this->db->group_end();
		}
		$recordsFiltered = $this->db->count_all_results('', false);

		$this->db->select('h.*');
		$this->db->order_by('h.submitted_at', 'desc');
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result();

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
		return $this->db->get()->row();
	}

	public function get_batch_items($id_pengajuan)
	{
		$this->db->from('tr_rp_pengajuan_d');
		$this->db->where('id_pengajuan', (int) $id_pengajuan);
		$this->db->order_by('id', 'asc');
		$items = $this->db->get()->result();

		foreach ($items as $it) {
			$kat = strtolower(trim((string)$it->kategori));
			if ($kat === 'direct payment' || $kat === 'direct_payment' || strpos($it->no_dokumen, 'DPM') === 0 || strpos($it->no_dokumen, 'DP-') === 0) {
				$is_cash = ($kat === 'cash') || ($this->db->get_where('tr_pr_non_po', ['no_non_po' => $it->no_dokumen])->num_rows() > 0);
				if (!$is_cash && $this->consultant) {
					$kons = $this->consultant->select('created_by')->get_where('kons_tr_kasbon_project_header', ['id' => $it->no_dokumen])->row();
					if (!empty($kons) && !empty($kons->created_by)) {
						$u = $this->consultant->query("SELECT nm_lengkap, username FROM users WHERE id_user = ? OR username = ?", [$kons->created_by, $kons->created_by])->row();
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
		$generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve WHERE id LIKE '%BK-" . $kode_bank . "-" . date('my-') . "%'")->row();
		$kodeBarang = $generate_id->max_id;
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
		$row = $this->db->select('tanggal')->from('v_request_payment')->where('no_dokumen', $no_dokumen)->get()->row();
		return (!empty($row) && !empty($row->tanggal)) ? $row->tanggal : null;
	}

	// idempotency guard: apakah no_doc sudah ada di payment_approve
	public function payment_exists($no_doc)
	{
		return $this->db->get_where('payment_approve', ['no_doc' => $no_doc])->num_rows() > 0;
	}
}
