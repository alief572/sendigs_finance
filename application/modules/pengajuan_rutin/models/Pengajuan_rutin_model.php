<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once dirname(__DIR__) . '/libraries/Periodik_logic.php';
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Budget Rutin"
 */

class Pengajuan_rutin_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	const ITEM_COLS = 'h.id, h.no_doc, h.tanggal_doc, h.periode, h.departement, h.created_by, d.id as did, d.no_item, d.id_budget, d.nama, d.nilai, d.keterangan, d.doc_file, d.item_status, d.item_reject_ket, d.item_approved_by, d.item_approved_date, d.revisi_dari, d.diajukan_ulang_sebagai, d.closed_note, d.closed_at, d.tanggal, d.budget, d.coa, d.bank_id, d.accnumber, d.accname, mb.tipe as m_tipe';

	protected $table_name = 'tr_pengajuan_rutin';
	protected $key        = 'id';

	/**
	 * @var string Field name to use for the created time column in the DB table
	 * if $set_created is enabled.
	 */
	protected $created_field = 'created_on';

	/**
	 * @var string Field name to use for the modified time column in the DB
	 * table if $set_modified is enabled.
	 */
	protected $modified_field = 'modified_on';

	/**
	 * @var bool Set the created time automatically on a new record (if true)
	 */
	protected $set_created = true;

	/**
	 * @var bool Set the modified time automatically on editing a record (if true)
	 */
	protected $set_modified = true;

	/**
	 * @var string The type of date/time field used for $created_field and $modified_field.
	 * Valid values are 'int', 'datetime', 'date'.
	 */
	protected $date_format = 'datetime';

	/**
	 * @var bool If true, will log user id in $created_by_field, $modified_by_field,
	 * and $deleted_by_field.
	 */
	protected $log_user = true;

	/**
	 * Function construct used to load some library, do some actions, etc.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// list data
	public function GetPengajuanRutin($where = '')
	{
		$this->db->select('a.*');
		$this->db->from($this->table_name . ' a');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.no_doc', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDataPengajuanRutin($id = '')
	{
		$this->db->select('a.*');
		$this->db->from($this->table_name . ' a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetDataPengajuanRutinDetail($nodoc = '')
	{
		$this->db->select('a.*');
		$this->db->from('tr_pengajuan_rutin_detail' . ' a');
		$this->db->where('a.no_doc', $nodoc);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data
	public function GetDataBudgetRutin($dept, $tanggal = null, $idbudget = null, $exclude_nodoc = null)
	{
		$tanggal_doc = ($tanggal !== null && !empty($tanggal)) ? $tanggal : date('Y-m-d');
		$ym = date("Y-m", strtotime($tanggal_doc));
		$y  = date("Y", strtotime($tanggal_doc));
		$m  = date("m", strtotime($tanggal_doc));

		$exclude_sql = "";
		if (!empty($exclude_nodoc)) {
			$exclude_sql = " AND h.no_doc != " . $this->db->escape($exclude_nodoc);
		}

		$sql = "
			SELECT 
				b.*,
				sub.no_doc AS submitted_no_doc,
				sub.tanggal_doc AS submitted_tanggal_doc,
				IF(sub.no_doc IS NOT NULL, 1, 0) AS is_submitted
			FROM ms_budget_rutin b
			LEFT JOIN (
				SELECT 
					d.id_budget, 
					GROUP_CONCAT(DISTINCT h.no_doc ORDER BY h.no_doc SEPARATOR ', ') AS no_doc, 
					MAX(h.tanggal_doc) AS tanggal_doc,
					DATE_FORMAT(h.tanggal_doc, '%Y-%m') AS ym,
					DATE_FORMAT(h.tanggal_doc, '%Y') AS y
				FROM tr_pengajuan_rutin_detail d
				JOIN tr_pengajuan_rutin h ON h.no_doc = d.no_doc
				WHERE d.nilai > 0
				{$exclude_sql}
				GROUP BY d.id_budget, ym, y
			) sub ON sub.id_budget = b.id 
				 AND (
					 (b.tipe = 'bulan' AND sub.ym = " . $this->db->escape($ym) . ")
					 OR
					 (b.tipe = 'tahun' AND sub.y = " . $this->db->escape($y) . ")
				 )
			WHERE b.departement = " . $this->db->escape($dept);

		if ($idbudget !== null && is_array($idbudget) && count($idbudget) > 0) {
			$clean_ids = array_filter(array_map('intval', $idbudget));
			if (!empty($clean_ids)) {
				$sql .= " AND b.id NOT IN (" . implode(",", $clean_ids) . ")";
			}
		}
		if ($tanggal !== null && !empty($tanggal)) {
			$sql .= " AND (b.tipe = 'bulan' OR (b.tipe = 'tahun' AND LEFT(b.tanggal, 2) = " . $this->db->escape($m) . "))";
		}
		$sql .= " ORDER BY b.nama ASC";

		$query = $this->db->query($sql);
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function CheckSubmittedBudget($id_budget, $tanggal_doc, $exclude_nodoc = null)
	{
		$budget = $this->db->get_where('ms_budget_rutin', array('id' => $id_budget))->row();
		if (!$budget) {
			return false;
		}

		$ym = date('Y-m', strtotime($tanggal_doc));
		$y  = date('Y', strtotime($tanggal_doc));

		$this->db->select('d.id_budget, d.nama, h.no_doc, h.tanggal_doc');
		$this->db->from('tr_pengajuan_rutin_detail d');
		$this->db->join('tr_pengajuan_rutin h', 'h.no_doc = d.no_doc');
		$this->db->where('d.id_budget', $id_budget);
		$this->db->where('d.nilai >', 0);
		if (!empty($exclude_nodoc)) {
			$this->db->where('h.no_doc !=', $exclude_nodoc);
		}

		if ($budget->tipe == 'tahun') {
			$this->db->where("DATE_FORMAT(h.tanggal_doc, '%Y') =", $y);
		} else {
			$this->db->where("DATE_FORMAT(h.tanggal_doc, '%Y-%m') =", $ym);
		}

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$row->tipe = $budget->tipe;
			return $row;
		}
		return false;
	}

	/**
	 * Baris detail dalam bentuk yang dipakai Periodik_logic. Batch lama (no_item NULL) memakai nomor
	 * batch sebagai nomor item. $periode/$tgl dari header.
	 */
	private function ItemRow($r)
	{
		return array(
			'id'          => (int) $r->did,
			'no_item'     => !empty($r->no_item) ? $r->no_item : $r->no_doc,
			'no_batch'    => $r->no_doc,
			'id_budget'   => $r->id_budget,
			'desc'        => $r->nama,
			'status'      => Periodik_logic::letter($r->item_status),
			'item_status' => (int) $r->item_status,
			'nominal'     => (float) $r->nilai,
			'ket'         => (string) $r->keterangan,
			'doc_file'    => (string) $r->doc_file,
			'note'        => (string) $r->item_reject_ket,
			'by'          => (string) $r->item_approved_by,
			'at'          => (string) $r->item_approved_date,
			'ref'         => (string) $r->revisi_dari,
			'resub'       => (string) $r->diajukan_ulang_sebagai,
			'closed_note' => (string) $r->closed_note,
			'closed_at'   => $r->closed_at,
			'periode'     => $r->periode ?: substr((string) $r->tanggal_doc, 0, 7),
			'tgl_batch'   => $r->tanggal_doc,
			'tanggal'     => $r->tanggal,
			'budget'      => $r->budget,
			'coa'         => $r->coa,
			'm_tipe'      => isset($r->m_tipe) ? $r->m_tipe : 'bulan',
			'bank_id'     => $r->bank_id,
			'accnumber'   => $r->accnumber,
			'accname'     => $r->accname,
		);
	}

	/**
	 * Batch beserta itemnya, terbaru dulu. $dept mengunci ke satu departemen (null = semua).
	 * Return: array no_doc => array(header..., 'items' => array(...))
	 */
	public function GetBatchesWithItems($dept = null, $noDoc = null)
	{
		$this->db->select(self::ITEM_COLS);
		$this->db->from('tr_pengajuan_rutin h');
		$this->db->join('tr_pengajuan_rutin_detail d', 'd.no_doc = h.no_doc');
		$this->db->join('ms_budget_rutin mb', 'mb.id = d.id_budget', 'left');
		if ($dept !== null && $dept !== '') {
			$this->db->where('h.departement', $dept);
		}
		if (is_array($noDoc)) {
			$this->db->where_in('h.no_doc', $noDoc ?: array(''));
		} elseif ($noDoc !== null) {
			$this->db->where('h.no_doc', $noDoc);
		}
		$this->db->order_by('h.no_doc', 'desc');
		$this->db->order_by('d.id', 'asc');
		$q = $this->db->get();
		if (!$q) {
			return false;
		}
		$out = array();
		foreach ($q->result() as $r) {
			if (!isset($out[$r->no_doc])) {
				$out[$r->no_doc] = array('id' => (int) $r->id, 'no' => $r->no_doc, 'dept' => $r->departement, 'date' => $r->tanggal_doc,
					'periode' => $r->periode ?: substr((string) $r->tanggal_doc, 0, 7), 'created_by' => $r->created_by, 'items' => array());
			}
			$out[$r->no_doc]['items'][] = $this->ItemRow($r);
		}
		return $out;
	}

	/**
	 * Syarat daftar serverside. $view wait = batch yang masih punya item menunggu, rec = semua item sudah diputuskan.
	 * $narrow=false hanya departemen + view (untuk total tab). Mode item membutuhkan join detail d.
	 */
	private function listWhere($dept, $view, $mode, $filter, $search, $narrow)
	{
		$db = $this->db;
		if ($dept !== null && $dept !== '') {
			$db->where('h.departement', $dept);
		}
		$ex = function ($cond) { return "EXISTS (SELECT 1 FROM tr_pengajuan_rutin_detail f WHERE f.no_doc = h.no_doc AND $cond)"; };
		$db->where(($view === 'wait' ? '' : 'NOT ') . $ex('f.item_status = 0'), null, false);
		if (!$narrow) {
			return;
		}
		if ($view === 'rec' && in_array($filter, array('A', 'P', 'R'), true)) {
			if ($mode === 'item') {
				$db->where($filter === 'A' ? 'd.item_status NOT IN (0, 8)' : 'd.item_status = 8', null, false);
			} elseif ($filter === 'A') {
				$db->where('NOT ' . $ex('f.item_status = 8'), null, false);
			} elseif ($filter === 'R') {
				$db->where('NOT ' . $ex('f.item_status <> 8'), null, false);
			} else {
				$db->where($ex('f.item_status = 8') . ' AND ' . $ex('f.item_status <> 8'), null, false);
			}
		}
		if ($search !== '') {
			$like = $db->escape('%' . $db->escape_like_str($search) . '%') . " ESCAPE '!'";
			$cols = $mode === 'item' ? array('d.no_item', 'd.nama', 'd.keterangan') : array();
			$sub = $ex("(f.nama LIKE $like OR f.no_item LIKE $like OR f.keterangan LIKE $like)");
			$db->where('(h.no_doc LIKE ' . $like . ($cols ? ' OR ' . implode(" LIKE $like OR ", $cols) . " LIKE $like" : ' OR ' . $sub) . ')', null, false);
		}
	}

	/** Jumlah batch per tab: array(wait, rec). */
	public function CountBatchViews($dept = null)
	{
		$out = array();
		foreach (array('wait', 'rec') as $view) {
			$this->db->from('tr_pengajuan_rutin h');
			$this->listWhere($dept, $view, 'batch', 'all', '', false);
			$out[$view] = (int) $this->db->count_all_results();
		}
		return $out;
	}

	/**
	 * Satu halaman daftar untuk DataTables serverside. $mode batch|item.
	 * Return array(total, filtered, rows) atau false bila query gagal.
	 * rows batch: array(batch...); rows item: array(array('batch' => header, 'item' => item)).
	 */
	public function ListPage($dept, $view, $mode, $filter, $search, $start, $length)
	{
		$item = $mode === 'item';
		$from = function () use ($item) {
			$this->db->from('tr_pengajuan_rutin h');
			if ($item) {
				$this->db->join('tr_pengajuan_rutin_detail d', 'd.no_doc = h.no_doc');
			}
		};
		$from();
		$this->listWhere($dept, $view, $mode, 'all', '', false);
		$total = $this->db->count_all_results();
		$from();
		$this->listWhere($dept, $view, $mode, $filter, $search, true);
		$filtered = $this->db->count_all_results();
		$start = max(0, (int) $start);
		$length = min(100, max(1, (int) $length));
		if ($item) {
			$from();
			$this->db->select(self::ITEM_COLS)->join('ms_budget_rutin mb', 'mb.id = d.id_budget', 'left');
			$this->listWhere($dept, $view, $mode, $filter, $search, true);
			$q = $this->db->order_by('h.no_doc', 'desc')->order_by('d.id', 'asc')->limit($length, $start)->get();
			if (!$q) {
				return false;
			}
			$rows = array();
			foreach ($q->result() as $r) {
				$rows[] = array('batch' => array('id' => (int) $r->id, 'no' => $r->no_doc, 'dept' => $r->departement, 'date' => $r->tanggal_doc), 'item' => $this->ItemRow($r));
			}
			return array((int) $total, (int) $filtered, $rows);
		}
		$this->db->select('h.no_doc');
		$from();
		$this->listWhere($dept, $view, $mode, $filter, $search, true);
		$q = $this->db->order_by('h.no_doc', 'desc')->limit($length, $start)->get();
		if (!$q) {
			return false;
		}
		$nos = array_column($q->result_array(), 'no_doc');
		$batches = $nos ? $this->GetBatchesWithItems(null, $nos) : array();
		return $batches === false ? false : array((int) $total, (int) $filtered, array_values($batches));
	}

	/**
	 * Record approval (DataTables serverside): semua item yang sudah diputuskan, di batch mana pun (termasuk batch yang
	 * sebagian itemnya masih menunggu), keputusan terbaru dulu. Return array(total, filtered, rows) atau false bila query gagal;
	 * rows: array(array('batch' => header, 'item' => item)).
	 */
	public function ListDecidedItems($search, $start, $length)
	{
		$from = function ($search) {
			$this->db->from('tr_pengajuan_rutin h')->join('tr_pengajuan_rutin_detail d', 'd.no_doc = h.no_doc')->where('d.item_status <>', 0);
			if ($search !== '') {
				$like = $this->db->escape('%' . $this->db->escape_like_str($search) . '%') . " ESCAPE '!'";
				$this->db->where("(d.no_item LIKE $like OR h.no_doc LIKE $like OR d.nama LIKE $like OR d.item_reject_ket LIKE $like)", null, false);
			}
		};
		$from('');
		$total = $this->db->count_all_results();
		$from($search);
		$filtered = $this->db->count_all_results();
		$from($search);
		$q = $this->db->select(self::ITEM_COLS)->join('ms_budget_rutin mb', 'mb.id = d.id_budget', 'left')
			->order_by('d.item_approved_date', 'desc')->order_by('d.id', 'desc')
			->limit(min(100, max(1, (int) $length)), max(0, (int) $start))->get();
		if (!$q) {
			return false;
		}
		$rows = array();
		foreach ($q->result() as $r) {
			$rows[] = array('batch' => array('id' => (int) $r->id, 'no' => $r->no_doc, 'dept' => $r->departement, 'date' => $r->tanggal_doc), 'item' => $this->ItemRow($r));
		}
		return array((int) $total, (int) $filtered, $rows);
	}

	/**
	 * Status Request Payment per nomor item: state belum|pending|done, no pengajuan Finance, alasan reject.
	 * Sumber: tr_rp_pengajuan_d/h (batch pengajuan Finance) dan tr_rp_reject_log.
	 */
	public function GetRpStates(array $noItems)
	{
		$noItems = array_values(array_unique(array_filter($noItems)));
		$out = array();
		if (!$noItems || !$this->db->table_exists('tr_rp_pengajuan_d')) {
			return $out;
		}
		$q = $this->db->select('d.no_dokumen, d.decision, h.status, h.no_pengajuan')
			->from('tr_rp_pengajuan_d d')->join('tr_rp_pengajuan_h h', 'h.id = d.id_pengajuan')
			->where('h.deleted', 0)->where_in('d.no_dokumen', $noItems)->order_by('h.id', 'asc')->get();
		if ($q) {
			foreach ($q->result() as $r) {
				if ($r->status === 'pending') {
					$out[$r->no_dokumen] = array('state' => 'pending', 'no' => $r->no_pengajuan);
				} elseif ($r->decision === 'approved') {
					$out[$r->no_dokumen] = array('state' => 'done', 'no' => $r->no_pengajuan);
				} else {
					$out[$r->no_dokumen] = array('state' => 'belum', 'no' => $r->no_pengajuan);
				}
			}
		}
		$q = $this->db->select('no_dokumen, no_pengajuan, alasan')->from('tr_rp_reject_log')
			->where_in('no_dokumen', $noItems)->order_by('id', 'asc')->get();
		if ($q) {
			foreach ($q->result() as $r) {
				// alasan reject hanya relevan selama item masih menunggu diajukan ulang
				if (isset($out[$r->no_dokumen]) && $out[$r->no_dokumen]['state'] === 'belum') {
					$out[$r->no_dokumen]['reject'] = $r->alasan;
					$out[$r->no_dokumen]['no'] = $r->no_pengajuan;
				}
			}
		}
		return $out;
	}

	/**
	 * Master budget aktif satu departemen beserta riwayat itemnya di batch lain.
	 * Return: array of array(master..., 'items' => array(item...)). $excludeNoDoc = batch yang sedang diedit.
	 */
	public function GetMasterWithItems($dept, $excludeNoDoc = null)
	{
		$q = $this->db->get_where('ms_budget_rutin', array('departement' => $dept));
		if (!$q) {
			return false;
		}
		$masters = array();
		foreach ($q->result_array() as $m) {
			$m['items'] = array();
			$masters[$m['id']] = $m;
		}
		if (!$masters) {
			return array();
		}
		$this->db->select('h.no_doc, h.tanggal_doc, h.periode, h.created_by, d.id as did, d.no_item, d.id_budget, d.nama, d.nilai, d.keterangan, d.doc_file, d.item_status, d.item_reject_ket, d.item_approved_by, d.item_approved_date, d.revisi_dari, d.diajukan_ulang_sebagai, d.closed_note, d.closed_at, d.tanggal, d.budget, d.coa, d.bank_id, d.accnumber, d.accname');
		$this->db->from('tr_pengajuan_rutin_detail d');
		$this->db->join('tr_pengajuan_rutin h', 'h.no_doc = d.no_doc');
		$this->db->where_in('d.id_budget', array_keys($masters));
		if (!empty($excludeNoDoc)) {
			$this->db->where('h.no_doc !=', $excludeNoDoc);
		}
		$this->db->order_by('d.id', 'asc');
		$q = $this->db->get();
		if (!$q) {
			return false;
		}
		foreach ($q->result() as $r) {
			if (isset($masters[$r->id_budget])) {
				$masters[$r->id_budget]['items'][] = $this->ItemRow($r);
			}
		}
		return array_values($masters);
	}

	/** Bank terakhir yang dipakai untuk item budget ini (form pengajuan baru tidak punya input bank). */
	public function GetLastBank($idBudget)
	{
		$row = $this->db->select('bank_id, accnumber, accname')->from('tr_pengajuan_rutin_detail')
			->where('id_budget', $idBudget)->where("COALESCE(accnumber, '') <>", '')->order_by('id', 'desc')->limit(1)->get();
		return $row ? $row->row() : null;
	}

	public function GetDataPengajuanRutinAll($where = '')
	{
		$this->db->select('a.*, c.nilai, c.nama,c.tanggal');
		$this->db->from($this->table_name . ' a');
		$this->db->join('tr_pengajuan_rutin_detail c', 'a.no_doc=c.no_doc');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.no_doc', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
}
