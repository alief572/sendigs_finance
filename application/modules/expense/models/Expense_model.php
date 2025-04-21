<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Purchase Request"
 */

class Expense_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $table_name = 'tr_expense';
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

	// list data kasbon
	public function GetListDataKasbon($where = '')
	{
		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		// $this->db->join('employee c', 'b.employee_id = c.id');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data kasbon
	public function GetDataKasbon($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_kasbon a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// list data transport request
	public function GetListDataTransportRequest($id_user = '', $where = '')
	{
		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		if ($id_user != '') $this->db->where('a.created_by', $id_user);
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// list data transport request
	public function GetListDataTransportRequestAll($id_user = '', $where = '')
	{
		$this->db->select('a.*, b.name as nmuser,c.tgl_doc as tgl_trans,c.keperluan');
		$this->db->from('tr_transport_req a');
		$this->db->join('tr_transport c', 'a.no_doc=c.no_req', 'left');
		$this->db->join('(select users.username,employees.name from users join employees on users.employee_id=employees.id)b', 'a.created_by=b.username', 'left');
		if ($id_user != '') $this->db->where('a.created_by', $id_user);
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data transport req
	public function GetDataTransportReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport_req a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// get data transport req detail
	public function GetDataTransportInReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.no_req', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	// list data transport
	public function GetListDatatransport($id_user = '')
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		if ($id_user != '') $this->db->where('a.nama', $id_user);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data transport
	public function GetDataTransport($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// list data
	public function GetListData($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	public function GetListDataAll($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval,d.tanggal,d.deskripsi');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		$this->db->join('tr_expense_detail d', 'a.no_doc=d.no_doc', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data
	public function GetDataHeader($id)
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

	public function GetDataDetail($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.no_doc', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDetailPurchaseRequest($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudget($coa, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudgetDivisi($type, $divisi, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $type);
		$this->db->where('a.divisi', $divisi);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function Update_budget($coa, $tgl, $nilai, $divisi, $nilai_pr = 0)
	{
		$bulan = date("n", strtotime($tgl));
		$tahun = date("Y", strtotime($tgl));

		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$this->db->where('a.divisi', $divisi);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			$data = $query->row();
			$terpakai_bulan = $data->{"terpakai_bulan_" . $bulan};
			$terpakai = $data->terpakai;
			$sisa = $data->sisa;
			$idbudget = $data->id;
			$upd_terpakai_bulan = ($terpakai_bulan + $nilai - $nilai_pr);
			$upd_terpakai = ($terpakai + $nilai - $nilai_pr);
			$upd_sisa = ($sisa - $nilai + $nilai_pr);
			$this->db->query("update ms_coa_budget set terpakai_bulan_" . $bulan . "=" . $upd_terpakai_bulan . ", terpakai=" . $upd_terpakai . ", sisa=" . $upd_sisa . " where id=" . $idbudget . " and coa='" . $coa . "' and tahun='" . $tahun . "'");
			return true;
		} else {
			return false;
		}
	}

	public function getArray($table, $WHERE = array(), $keyArr = '', $valArr = '')
	{
		if ($WHERE) {
			$query = $this->db->get_where($table, $WHERE);
		} else {
			$query = $this->db->get($table);
		}
		$dataArr	= $query->result_array();

		if (!empty($keyArr)) {
			$Arr_Data	= array();
			foreach ($dataArr as $key => $val) {
				$nilai_id					= $val[$keyArr];
				if (!empty($valArr)) {
					$nilai_val				= $val[$valArr];
					$Arr_Data[$nilai_id]	= $nilai_val;
				} else {
					$Arr_Data[$nilai_id]	= $val;
				}
			}

			return $Arr_Data;
		} else {
			return $dataArr;
		}
	}
}
