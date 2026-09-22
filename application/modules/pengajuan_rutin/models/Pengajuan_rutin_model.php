<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
