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
		$this->db->select('a.*, b.nama as nm_dept');
		$this->db->from($this->table_name . ' a');
		$this->db->join('ms_department b', 'a.departement=b.id');
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
	public function GetDataBudgetRutin($dept, $tanggal = null, $idbudget = null)
	{
		$sql = "select * from ms_budget_rutin where departement='" . $dept . "' ";
		if ($idbudget !== null) $sql .= " and id not in (" . implode(",", $idbudget) . ")";
		if ($tanggal !== null) $sql .= " and (tipe ='bulan' or (tipe='tahun' and left(tanggal,2)='" . date("m", strtotime($tanggal)) . "'))";
		$query = $this->db->query($sql);
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDataPengajuanRutinAll($where = '')
	{
		$this->db->select('a.*, c.nilai, b.nama as nm_dept,c.nama,c.tanggal');
		$this->db->from($this->table_name . ' a');
		$this->db->join('ms_department b', 'a.departement=b.id');
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
