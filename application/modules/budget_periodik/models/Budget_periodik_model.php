<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
		$sql = "select a.departement,b.nama as nm_dept from " . $this->table_name . " a left join ms_department b on a.departement=b.id ";
		if ($key != '') $sql .= " where a.departement='" . $key . "' ";
		$sql .= " group by a.departement, b.nama order by b.nama ";
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
