<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once dirname(__DIR__) . '/libraries/Budget_logic.php';
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Budget Rutin"
 */

class Budget_periodik_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $table_name = 'ms_budget_rutin';
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
	public function GetBudgetRutinGroup($key = '')
	{
		$sql = "select a.departement from " . $this->table_name . " a  ";
		if ($key != '') $sql .= " where a.departement='" . $key . "' ";
		$sql .= " group by a.departement ";
		$query = $this->db->query($sql);
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	public function GetBudgetRutin($key = '')
	{
		$this->db->select('a.*, b.nm_dept, c.nama as nama_perkiraan');
		$this->db->from($this->table_name . ' a');
		$this->db->join('department b', 'a.departement=b.id', 'left');
		$this->db->join(DBACC . '.coa_master  c', 'a.coa=c.no_perkiraan', 'left');
		if ($key != '') $this->db->where('a.departement', $key);
		$this->db->order_by('a.coa asc,a.nama');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	/**
	 * Master budget (ms_budget_rutin) beserta pemakaiannya: in_sys = pembayaran yang sudah disetujui
	 * (item_status 1, 2, 9), pend = item yang masih menunggu approval. rej_* = pengajuan terakhir item ini bila ditolak
	 * (item_status 8; pengajuan yang lebih baru, termasuk pengajuan ulang, menggantikannya). $dept null = semua departemen.
	 */
	public function GetMasters($dept = null)
	{
		$sql = "SELECT a.*, COALESCE(u.appr, 0) AS in_sys, COALESCE(u.pend, 0) AS pend,
				r.no_item AS rej_no, r.item_reject_ket AS rej_note, r.item_approved_date AS rej_at, r.closed_at AS rej_closed
			FROM ms_budget_rutin a
			LEFT JOIN (SELECT id_budget, SUM(CASE WHEN item_status IN (1, 2, 9) THEN 1 ELSE 0 END) AS appr, SUM(CASE WHEN item_status = 0 THEN 1 ELSE 0 END) AS pend
				FROM tr_pengajuan_rutin_detail GROUP BY id_budget) u ON u.id_budget = a.id
			LEFT JOIN (SELECT d.id_budget, COALESCE(d.no_item, d.no_doc) AS no_item, d.item_reject_ket, d.item_approved_date, d.closed_at
				FROM tr_pengajuan_rutin_detail d
				JOIN (SELECT id_budget, MAX(id) AS mid FROM tr_pengajuan_rutin_detail GROUP BY id_budget) l ON l.mid = d.id
				WHERE d.item_status = 8) r ON r.id_budget = a.id";
		$binds = array();
		if ($dept !== null && $dept !== '') {
			$sql .= ' WHERE a.departement = ?';
			$binds[] = $dept;
		}
		$sql .= ' ORDER BY a.coa ASC, a.nama ASC';
		$q = $this->db->query($sql, $binds);
		return $q ? $q->result_array() : false;
	}

	private function BgRow($h, array $lines)
	{
		return array('id' => (int) $h->id, 'no' => $h->no_bg, 'dept' => $h->departement, 'status' => $h->status, 'by' => $h->created_by,
			'at' => $h->created_on, 'decided_on' => $h->decided_on, 'lines' => $lines);
	}

	private function BgLine($l)
	{
		return array('id' => (int) $l->id, 'kind' => $l->kind, 'id_budget' => $l->id_budget === null ? null : (int) $l->id_budget,
			// JSON kehilangan tipe angka; dinormalkan lagi supaya float/int stabil di UI dan saat diterapkan
			'before' => $l->before_json ? Budget_logic::normalize(json_decode($l->before_json, true) ?: array()) : null,
			'after' => Budget_logic::normalize(json_decode($l->after_json, true) ?: array()),
			'fields' => $l->fields === null || $l->fields === '' ? array() : explode(',', $l->fields), 'decision' => $l->decision, 'note' => (string) $l->note,
			'decided_by' => $l->decided_by, 'decided_at' => $l->decided_at);
	}

	/**
	 * Perubahan budget (BG) beserta barisnya, terbaru dulu. Filter opsional: departemen, status (pending|done), nomor.
	 * Return: array no_bg => BG.
	 */
	public function GetBgs($dept = null, $status = null, $no = null)
	{
		$this->db->from('tr_budget_bg');
		if ($dept !== null && $dept !== '') { $this->db->where('departement', $dept); }
		if ($status !== null) { $this->db->where('status', $status); }
		if ($no !== null) { $this->db->where('no_bg', $no); }
		$this->db->order_by('id', 'desc');
		$q = $this->db->get();
		if (!$q) {
			return false;
		}
		$out = array();
		foreach ($q->result() as $h) {
			$out[$h->no_bg] = $this->BgRow($h, array());
		}
		if (!$out) {
			return $out;
		}
		$lq = $this->db->where_in('no_bg', array_keys($out))->order_by('id', 'asc')->get('tr_budget_bg_line');
		if (!$lq) {
			return false;
		}
		foreach ($lq->result() as $l) {
			$out[$l->no_bg]['lines'][] = $this->BgLine($l);
		}
		return $out;
	}

	public function GetPendingBg($dept)
	{
		$all = $this->GetBgs($dept, 'pending');
		return $all ? reset($all) : ($all === false ? false : null);
	}

	/**
	 * Buat satu BG pending berisi baris perubahan (hasil Budget_logic::diff). Nomor BG diambil lebih dulu:
	 * baris counter yang terkunci sampai commit membuat pembuatan BG berurutan, sehingga pemeriksaan
	 * "departemen masih punya BG pending" di bawah tidak bisa dilewati dua permintaan bersamaan.
	 * Return array(ok, no_bg, pesan).
	 */
	public function CreateBg($dept, array $lines, $by)
	{
		if (!$lines) {
			return array(false, '', 'Tidak ada perubahan untuk disimpan.');
		}
		if ($this->db->trans_begin() === false) {
			return array(false, '', 'Please try again later !');
		}
		$no = periodik_next_number($this->db, 'BG');
		$now = date('Y-m-d H:i:s');
		$ok = $no !== '';
		$msg = 'Please try again later !';
		if ($ok) {
			$pending = $this->db->get_where('tr_budget_bg', array('departement' => $dept, 'status' => 'pending'));
			$row = $pending ? $pending->row() : null;
			if (!$pending) {
				$ok = false;
			} elseif ($row) {
				$ok = false;
				$msg = 'Masih ada perubahan ' . $row->no_bg . ' yang menunggu Approval Perubahan Budget untuk departemen ini.';
			}
		}
		if ($ok) {
			$ok = (bool) $this->db->insert('tr_budget_bg', array('no_bg' => $no, 'departement' => $dept, 'status' => 'pending', 'created_by' => $by, 'created_on' => $now));
		}
		foreach ($lines as $l) {
			if (!$ok) { break; }
			$ok = (bool) $this->db->insert('tr_budget_bg_line', array('no_bg' => $no, 'kind' => $l['kind'], 'id_budget' => $l['id'],
				'before_json' => $l['before'] === null ? null : json_encode($l['before']), 'after_json' => json_encode($l['after']), 'fields' => implode(',', $l['fields'])));
		}
		if (!$ok || $this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return array(false, '', $msg);
		}
		if ($this->db->trans_commit() === false) {
			return array(false, '', 'Please try again later !');
		}
		return array(true, $no, '');
	}

	/**
	 * Putuskan satu BG pending. $decisions: id baris => array('A'|'R', alasan). Setiap baris wajib punya keputusan,
	 * reject wajib beralasan. Baris disetujui langsung diterapkan ke ms_budget_rutin berdasarkan id (id master
	 * tidak pernah diganti). Seluruhnya atomik; keputusan final: BG yang sudah done ditolak.
	 * Return array(ok, pesan, disetujui, ditolak).
	 */
	public function DecideBg($no, array $decisions, $by)
	{
		$bgs = $this->GetBgs(null, null, $no);
		if ($bgs === false || !isset($bgs[$no])) {
			return array(false, 'Perubahan budget tidak ditemukan.', 0, 0);
		}
		$bg = $bgs[$no];
		if ($bg['status'] !== 'pending') {
			return array(false, 'Keputusan perubahan ini sudah disimpan dan tidak bisa diubah.', 0, 0);
		}
		foreach ($bg['lines'] as $l) {
			$d = isset($decisions[$l['id']]) ? $decisions[$l['id']] : null;
			if (!$d || !in_array($d[0], array('A', 'R'), true)) {
				return array(false, 'Keputusan belum lengkap untuk semua perubahan.', 0, 0);
			}
			if ($d[0] === 'R' && trim((string) $d[1]) === '') {
				return array(false, 'Ada perubahan yang di-reject tanpa alasan.', 0, 0);
			}
		}
		if ($this->db->trans_begin() === false) {
			return array(false, 'Please try again later !', 0, 0);
		}
		$now = date('Y-m-d H:i:s');
		$ok = true;
		$msg = 'Please try again later !';
		$a = $r = 0;
		$used = array();
		foreach ($this->GetMasters($bg['dept']) ?: array() as $m) {
			$used[(int) $m['id']] = (int) $m['masa_awal'] + (int) $m['in_sys'];
		}
		foreach ($bg['lines'] as $l) {
			$d = $decisions[$l['id']];
			$newId = $l['id_budget'];
			if ($d[0] === 'A') {
				// apply_line memakai 'id' sebagai id master; di baris BG id itu ada di id_budget ('id' = id baris perubahan)
				$ap = Budget_logic::apply_line(array_merge($l, array('id' => $l['id_budget'])), $bg['dept']);
				if (isset($ap['data']['masa_berlaku']) && $ap['data']['masa_berlaku'] > 0 && $ap['data']['masa_berlaku'] < ($used[$ap['id'] ?? 0] ?? 0)) {
					$ok = false;
					$msg = 'Masa berlaku "' . $l['after']['nama'] . '" tidak boleh kurang dari ' . $used[$ap['id']] . ' kali (sudah terpakai).';
					break;
				}
				if ($ap['op'] === 'insert') {
					$ok = (bool) $this->db->insert('ms_budget_rutin', $ap['data']);
					$newId = $this->db->insert_id();
				} else {
					$ok = (bool) $this->db->update('ms_budget_rutin', $ap['data'], array('id' => $ap['id'], 'departement' => $bg['dept']));
				}
				$a++;
			} else {
				$r++;
			}
			$ok = $ok && (bool) $this->db->update('tr_budget_bg_line', array('decision' => $d[0], 'note' => $d[0] === 'R' ? trim($d[1]) : null,
				'decided_by' => $by, 'decided_at' => $now, 'id_budget' => $newId), array('id' => $l['id'], 'decision' => null));
			if (!$ok) { break; }
		}
		$ok = $ok && (bool) $this->db->update('tr_budget_bg', array('status' => 'done', 'decided_on' => $now), array('no_bg' => $no, 'status' => 'pending'));
		if (!$ok || $this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return array(false, $msg, 0, 0);
		}
		if ($this->db->trans_commit() === false) {
			return array(false, 'Please try again later !', 0, 0);
		}
		return array(true, '', $a, $r);
	}

	public function GetNewBudgetRutin($divisi)
	{
		$this->db->select('a.*, b.nm_dept, c.nama as nama_perkiraan');
		$this->db->from($this->table_name . ' a');
		$this->db->join('department b', 'a.departement=b.id', 'left');
		$this->db->join(DBACC . '.coa_master c', 'a.coa=c.no_perkiraan', 'left');
		if ($divisi != '') $this->db->where('a.departement', $divisi);
		$this->db->order_by('a.coa', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function updatebudget($data)
	{
		if ($data['tipe'] == 'bulan') {
			$this->db->query("update ms_budget set bulan_1=(bulan_1+" . $data['nilai'] . "),bulan_2=(bulan_2+" . $data['nilai'] . "), bulan_3=(bulan_3+" . $data['nilai'] . "), bulan_4=(bulan_4+" . $data['nilai'] . "), bulan_5=(bulan_5+" . $data['nilai'] . "), bulan_6=(bulan_6+" . $data['nilai'] . "), bulan_7=(bulan_7+" . $data['nilai'] . "), bulan_8=(bulan_8+" . $data['nilai'] . "), bulan_9=(bulan_9+" . $data['nilai'] . "), bulan_10=(bulan_10+" . $data['nilai'] . "), bulan_11=(bulan_11+" . $data['nilai'] . "), bulan_12=(bulan_12+" . $data['nilai'] . "), total=(" . ($data['nilai'] * 12) . "), created_by_dept='" . $this->auth->user_id() . "', created_on_dept='" . date('Y-m-d H:i:s') . "' WHERE tahun='" . $data['tahun'] . "' and coa='" . $data['coa'] . "' and kategori='PEMBAYARAN PERIODIK'"); //and divisi='".$data['departement']."'
		}
		if ($data['tipe'] == 'tahun') {
			$bulan = date("n", strtotime('2021-' . $data['tanggal']));
			$this->db->query("update ms_budget set bulan_" . $bulan . "=(bulan_" . $bulan . "+" . $data['nilai'] . "), total=(total+" . $data['nilai'] . "), created_by_dept='" . $this->auth->user_id() . "', created_on_dept='" . date('Y-m-d H:i:s') . "' WHERE tahun='" . $data['tahun'] . "' and coa='" . $data['coa'] . "' and kategori='PEMBAYARAN PERIODIK' "); //and divisi='".$data['departement']."'
		}
	}
	// get data
	public function GetDataBudgetRutin($id)
	{
		$this->db->select('a.*');
		$this->db->from($this->table_name . ' a');
		$this->db->where('a.id', $id);
		$this->db->order_by('a.department', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}
}
