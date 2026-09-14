<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Model: Record Request Payment (histori final snapshot - FSD 2026-09)
 * Sumber: tr_rp_pengajuan_h (status=done) + tr_rp_record (snapshot approved)
 */

class Record_request_payment_model extends BF_Model
{
	protected $table_name = 'tr_rp_pengajuan_h';
	protected $key        = 'id';
	protected $soft_deletes = false;
	protected $set_created  = false;
	protected $set_modified = false;

	public function __construct()
	{
		parent::__construct();
	}

	// Server-side DataTables: batch done yang punya >=1 record snapshot
	public function get_data_record()
	{
		$post   = $this->input->post();
		$draw   = isset($post['draw']) ? intval($post['draw']) : 0;
		$length = isset($post['length']) ? intval($post['length']) : 25;
		$start  = isset($post['start']) ? intval($post['start']) : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$build = function () {
			$this->db->from('tr_rp_pengajuan_h h');
			$this->db->where('h.deleted', 0);
			$this->db->where('h.status', 'done');
			// harus punya minimal 1 record snapshot
			$this->db->where('(SELECT COUNT(*) FROM tr_rp_record r WHERE r.id_pengajuan = h.id) >', 0, false);
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

		$this->db->select('h.*, (SELECT COUNT(*) FROM tr_rp_record r WHERE r.id_pengajuan = h.id) as jumlah_approved');
		$this->db->order_by('h.decided_at', 'desc');
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result();

		$data = [];
		foreach ($rows as $r) {
			// total dibayarkan dari snapshot (bukan header, konsisten dgn snapshot)
			$tot = $this->db->query("SELECT IFNULL(SUM(dibayarkan),0) as t FROM tr_rp_record WHERE id_pengajuan = ?", [$r->id])->row();
			$total = $tot ? $tot->t : 0;

			$data[] = [
				'no_pengajuan'     => $r->no_pengajuan,
				'company_nama'     => $r->company_nama ? $r->company_nama : '-',
				'submitted_at'     => !empty($r->submitted_at) ? date('d-M-Y H:i', strtotime($r->submitted_at)) : '',
				'decided_at'       => !empty($r->decided_at) ? date('d-M-Y H:i', strtotime($r->decided_at)) : '',
				'total_dibayarkan' => number_format($total, 0, ',', '.'),
				'jumlah_approved'  => (int) $r->jumlah_approved,
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

	public function get_header($id_pengajuan)
	{
		$this->db->from('tr_rp_pengajuan_h');
		$this->db->where('id', (int) $id_pengajuan);
		$this->db->where('deleted', 0);
		$this->db->where('status', 'done');
		return $this->db->get()->row();
	}

	// Snapshot records (read-only) untuk 1 batch
	public function get_records($id_pengajuan)
	{
		$this->db->from('tr_rp_record');
		$this->db->where('id_pengajuan', (int) $id_pengajuan);
		$this->db->order_by('id', 'asc');
		return $this->db->get()->result();
	}
}
