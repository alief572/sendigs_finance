<?php

class Employees_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function Simpan($table, $data)
	{
		return $this->db->insert($table, $data);
	}
	public function getData($table, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function getDataArray($table, $where_field = '', $where_value = '', $keyArr = '', $valArr = '', $where_field2 = '', $where_value2 = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value));
		}
		if ($where_field2 != '' && $where_value2 != '' && $where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value, $where_field2 => $where_value2));
		} else {
			$query = $this->db->get($table);
		}
		$dataArr	= $query->result_array();

		if (!empty($keyArr) && !empty($valArr)) {
			$Arr_Data	= array();
			foreach ($dataArr as $key => $val) {
				$nilai_id				= $val[$keyArr];
				if (empty($valArr)) {
					$Arr_Data[$nilai_id]	= $val;
				} else {
					$Arr_Data[$nilai_id]	= $nilai_id;
				}
			}

			return $Arr_Data;
		} else {
			return $dataArr;
		}
	}

	public function getUpdate($table, $data, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->where(array($where_field => $where_value));
		}
		$result	= $this->db->update($table, $data);
		//echo "<pre>";print_r(result()); 
		return $result;
	}
	public function getDelete($table, $where_field, $where_value)
	{
		$result	= $this->db->delete($table, array($where_field => $where_value));
		return $result;
	}

	public function getMenu($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('menus', $where);
		} else {
			$query = $this->db->get('menus');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}

	public function getDepartments($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.departments', $where);
		} else {
			$query = $this->db->get('hr_sentral.departments');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}


	public function getDivisions($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.divisions', $where);
		} else {
			$query = $this->db->get('hr_sentral.divisions');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}

	public function getDivisionsHead($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.divisions_head', $where);
		} else {
			$query = $this->db->get('hr_sentral.divisions_head');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}

	public function getCompanies($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.companies', $where);
		} else {
			$query = $this->db->get('hr_sentral.companies');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getEmployees($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('employees', $where);
		} else {
			$query = $this->db->get('employees');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getTitles($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.titles', $where);
		} else {
			$query = $this->db->get('hr_sentral.titles');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getPositions($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.positions', $where);
		} else {
			$query = $this->db->get('hr_sentral.positions');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getMarital($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.marital_status', $where);
		} else {
			$query = $this->db->get('hr_sentral.marital_status');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['code']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getContract($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.contracts', $where);
		} else {
			$query = $this->db->get('hr_sentral.contracts');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getIdfinger($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {

			$this->db->distinct();
			$query = $this->db->get_where('hr_sentral.fingerprint_data', $where);
		} else {

			$this->db->distinct();
			$query = $this->db->get('hr_sentral.fingerprint_data');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['name']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getPolicy($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.ms_policy', $where);
		} else {
			$query = $this->db->get('hr_sentral.ms_policy');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function optionDivisions($where = array())
	{
		$result = array();
		$query = $this->db->get_where('hr_sentral.divisions', $where);
		$results	= $query->result_array();
		if ($results) {

			foreach ($results as $key => $vals) {
				$option[0]	= 'pilih';
				$option[$vals['id']]	= $vals['name'];
			}
		}

		return $option;
	}
	public function tampil($where, $table)
	{
		return $this->db->get_where($table, $where);
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

	public function companies()
	{
		$aMenu		= array();

		$query = $this->db->get('companies');


		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getDataEmpl()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('hr_sentral.employees a');
		$this->db->join('hr_sentral.companies b', 'b.id=a.company_id', 'left');
		$this->db->join('hr_sentral.departments c', 'c.id=a.department_id', 'left');
		$this->db->join('hr_sentral.divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('hr_sentral.titles e', 'e.id=a.title_id', 'left');
		$this->db->join('hr_sentral.contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('hr_sentral.contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('hr_sentral.contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('hr_sentral.contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('hr_sentral.positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.flag_active' => 'Y', 'a.company_id' => 'COM003'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataEmplcalibrasi()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('hr_sentral.employees a');
		$this->db->join('hr_sentral.companies b', 'b.id=a.company_id', 'left');
		$this->db->join('hr_sentral.departments c', 'c.id=a.department_id', 'left');
		$this->db->join('hr_sentral.divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('hr_sentral.titles e', 'e.id=a.title_id', 'left');
		$this->db->join('hr_sentral.contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('hr_sentral.contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('hr_sentral.contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('hr_sentral.contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('hr_sentral.positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.flag_active' => 'Y', 'a.company_id' => 'COM004'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function code_otomatis($table, $pre)
	{
		$this->db->select('Right(id,4) as kode ', false);
		$this->db->order_by('id', 'desc');
		$this->db->limit(1);
		$query = $this->db->get($table);

		if ($query->num_rows() <> 0) {
			$data = $query->row();
			$kode = intval($data->kode) + 1;
		} else {
			$kode = 1;
		}
		$kodemax = str_pad($kode, 4, "0", STR_PAD_LEFT);
		$kodediv  = "$pre" . $kodemax;
		return $kodediv;
	}
	function code_otomatisNik($table, $pre, $mid)
	{
		$today = date('Ym');
		$this->db->select('Right(nik,3) as kode ', false);
		$this->db->like('nik', $today);
		$this->db->order_by('nik', 'desc');
		$this->db->limit(1);
		$query = $this->db->get($table);
		if ($query->num_rows() <> 0) {
			$data = $query->row();
			$kode = intval($data->kode) + 1;
		} else {
			$kode = 1;
		}
		$kodemax = str_pad($kode, 3, "0", STR_PAD_LEFT);
		$kodediv  = "$pre" . "$mid" . $kodemax;
		return $kodediv;
	}

	public function insert_history()
	{

		$data_session			= $this->session->userdata;
		$this->name 			= 'Ubah Status Karyawan';
		$this->employee_id 		= $_POST['id'];
		$this->change_desc		= $_POST['nik'];
		$this->contract_id 		= $_POST['contract_id'];
		$this->start_date 		= $_POST['startcontract_date'];
		$this->latest_date 		= $_POST['latestcontract_date'];
		$this->created_by		= $data_session['User']['username'];
		$this->created			= date('Y-m-d H:i:s');

		return  $this->db->insert('employees_histories', $this);
	}

	public function division_history()
	{

		$data_session			= $this->session->userdata;
		$this->name 			= 'Ubah divisi';
		$this->employee_id 		= $_POST['id'];
		$this->change_desc		= $_POST['nik'];
		$this->descr	 		= $_POST['descr'];
		$this->start_date 		= date('Y-m-d');
		$this->created_by		= $data_session['User']['username'];
		$this->created			= date('Y-m-d H:i:s');

		return  $this->db->insert('employees_histories', $this);
	}

	public function grade_history()
	{

		$data_session			= $this->session->userdata;
		$this->name 			= 'Ubah Grade' . $_POST['grade'];
		$this->employee_id 		= $_POST['id'];
		$this->change_desc		= $_POST['nik'];
		$this->descr	 		= $_POST['descr'];
		$this->start_date 		= $_POST['startcontract_date'];
		$this->created_by		= $data_session['User']['username'];
		$this->created			= date('Y-m-d H:i:s');

		return  $this->db->insert('employees_histories', $this);
	}
	public function insert_family()
	{

		$data_session			= $this->session->userdata;
		$this->employee_id 		= $_POST['id'];
		$this->employee_nik		= $_POST['nik'];
		$this->family			= $_POST['family'];
		$this->family_name 		= $_POST['family_name'];
		$this->created_by		= $data_session['User']['username'];
		$this->created			= date('Y-m-d H:i:s');

		return  $this->db->insert('family', $this);
	}

	public function getCountLeave()
	{

		$employeeid		= $_POST['employee_id'];
		$year			= $_POST['year'];
		$leave			= $_POST['leave_id'];


		$this->db->select('*');
		$this->db->from('employees_leave');
		$this->db->where(array('employee_id' => $employeeid, 'year' => $year, 'leave_id' => $leave, 'leave' >= 1));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function getRowLeave()
	{

		$employeeid		= $_POST['employee_id'];
		$year			= $_POST['year'];
		$leave			= $_POST['leave_id'];


		$this->db->select('*');
		$this->db->from('employees_leave');
		$this->db->where(array('employee_id' => $employeeid, 'year' => $year, 'leave_id' => $leave,));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}



	public function family()
	{

		$data_session			= $this->session->userdata;
		$this->employee_id 		= $_POST['employee_id'];
		$this->employee_nik		= $_POST['employee_nik'];


		return  $this->db->insert('family', $this);
	}

	public function getDataEmplhis()
	{
		$this->db->select('a.*,b.name as employee_name,c.name as contract_name');
		$this->db->from('employees_histories a');
		$this->db->join('employees b', 'b.id=a.employee_id', 'left');
		$this->db->join('contracts c', 'c.id=a.contract_id', 'left');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	public function getGrades($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('grades', $where);
		} else {
			$query = $this->db->get('grades');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['name']]	= $vals['name'];
			}
		}
		return $aMenu;
	}

	public function getEmpfamily()
	{
		$this->db->select('a.*,b.name as employee_name, b.nik as employee_nik, c.category as name_category ');
		$this->db->from('hr_sentral.family a');
		$this->db->join('hr_sentral.employees b', 'b.id=a.employee_id', 'left');
		$this->db->join('hr_sentral.family_category c', 'c.kode=a.category', 'left');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getUpdateEmp($table, $data, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->where(array($where_field => $where_value));
		}
		$result	= $this->db->update($table, $data);
		//echo "<pre>";print_r(result()); 
		return $result;
	}

	public function prismaempNumrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.company_id' => 'COM003', 'a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function prismatetapNumrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.company_id' => 'COM003', 'a.permanent_id' => 'CTR004', 'a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function prismakontrak1Numrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => 'COM003',
			'a.firstcontract_id' => 'CTR001',
			'a.secondcontract_id' => '0',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function prismakontrak2Numrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => 'COM003',
			'a.secondcontract_id' => 'CTR002',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function prismakontrak3Numrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => 'COM003',
			'a.thirdcontract_id' => 'CTR003',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));

		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}





	public function danestempNumrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.company_id' => 'COM004', 'a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function danesttetapNumrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.company_id' => 'COM004', 'a.permanent_id' => 'CTR004', 'a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function danestkontrak1Numrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => 'COM004',
			'a.firstcontract_id' => 'CTR001',
			'a.secondcontract_id' => '0',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function danestkontrak2Numrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => 'COM004',
			'a.secondcontract_id' => 'CTR002',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function danestkontrak3Numrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => 'COM004',
			'a.thirdcontract_id' => 'CTR003',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));

		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}



	//JUMLAH SEMUA KARYAWAN 

	public function empNumrows()
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function getDataCompEmpl($com)
	{

		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.company_id' => $com, 'a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataLatestContract1($com)
	{

		$this->db->select('a.*, b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');

		$this->db->where(array(
			'a.company_id' => $com,
			'a.firstcontract_id' => 'CTR001',
			'a.secondcontract_id' => '0',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$this->db->where("DATEDIFF(a.firstcontractlatest_date,now()) <= 90");
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataLatestContract2($com)
	{

		$this->db->select('a.*, b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => $com,
			'a.secondcontract_id' => 'CTR002',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$this->db->where("DATEDIFF(a.secondcontractlatest_date,now())  <= 90");
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataLatestContract3($com)
	{

		$this->db->select('a.*, b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');

		$this->db->where(array(
			'a.company_id' => $com,
			'a.thirdcontract_id' => 'CTR003',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$this->db->where("DATEDIFF(a.thirdcontractlatest_date,now()) <=90");
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataContractEmpl($com)
	{

		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');

		$this->db->where(array('a.company_id' => $com, 'a.permanent_id' => '0', 'a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataPermanentEmpl($com)
	{

		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.company_id' => $com, 'a.permanent_id' => 'CTR004', 'a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getnumDataLatestContract1($com)
	{

		$this->db->select('a.*, b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');

		$this->db->where(array(
			'a.company_id' => $com,
			'a.firstcontract_id' => 'CTR001',
			'a.secondcontract_id' => '0',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$this->db->where("DATEDIFF(a.firstcontractlatest_date,now()) <= 90");
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function getnumDataLatestContract2($com)
	{

		$this->db->select('a.*, b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => $com,
			'a.secondcontract_id' => 'CTR002',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$this->db->where("DATEDIFF(a.secondcontractlatest_date,now())  <= 90");
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function getnumDataLatestContract3($com)
	{

		$this->db->select('a.*, b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');

		$this->db->where(array(
			'a.company_id' => $com,
			'a.thirdcontract_id' => 'CTR003',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$this->db->where("DATEDIFF(a.thirdcontractlatest_date,now()) <=90");
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->num_rows();
		} else {
			return false;
		}
	}

	public function prismakontrak1($com)
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => $com,
			'a.firstcontract_id' => 'CTR001',
			'a.secondcontract_id' => '0',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function prismakontrak2($com)
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => $com,
			'a.secondcontract_id' => 'CTR002',
			'a.thirdcontract_id' => '0',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function prismakontrak3($com)
	{
		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('companies b', 'b.id=a.company_id', 'left');
		$this->db->join('departments c', 'c.id=a.department_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array(
			'a.company_id' => $com,
			'a.thirdcontract_id' => 'CTR003',
			'a.permanent_id' => '0',
			'a.flag_active' => 'Y'
		));

		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getLeaves($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('at_leaves', $where);
		} else {
			$query = $this->db->get('at_leaves');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getSalary($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('salary', $where);
		} else {
			$query = $this->db->get('salary');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getDataEmpleave()
	{
		$this->db->select('a.*, b.year, b.leave, b.leave_id, c.name as leave_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('employees_leave b', 'b.employee_id=a.id', 'left');
		$this->db->join('at_leaves c', 'c.id=b.leave_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	public function getDataTransleave()
	{
		$this->db->select('a.*, b.year, b.leave, b.leave_id,b.from,b.until,b.descr, b.id as tr_id, c.name as leave_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('employees a');
		$this->db->join('transaction_leave b', 'b.employee_id=a.id', 'left');
		$this->db->join('at_leaves c', 'c.id=b.leave_id', 'left');
		$this->db->join('divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('titles e', 'e.id=a.title_id', 'left');
		$this->db->join('contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function update_data($where, $jumlah, $table)
	{

		$this->db->select('leave');
		$this->db->where($where);
		$query = $this->db->get($table);
		$row = $query->row();
		$qty_leave = $row->leave;

		//I am assuming that $quantidade is the quantity of the order
		$new_leave = $qty_leave - $jumlah;

		//update products table
		$this->db->where($where);
		$this->db->update($table, array('leave' => $new_leave));
	}

	function getAbsensirpt($tgl1, $tgl2)
	{


		$query = $this->db->query("SELECT a.name,a.date,a.timetable,a.clock_in,a.clock_out,a.created,a.created_by, e.clock_in as clock_in2 , e.clock_out as clock_out2,
			TIME_TO_SEC (timediff(a.clock_in,e.clock_in))/60 as late, 
			TIME_TO_SEC (timediff(e.clock_out,a.clock_out))/60 as early
		    FROM fingerprint_data_temp a
			LEFT JOIN at_shifts e ON a.timetable = e.name");





		if ($query->num_rows()) {
			$data = $query->result_array();

			$results	= $query->result_array();

			foreach ($data as $row => $rows) {

				$name = $rows['name'];
				$date = jin_date_sql($rows['date']);
				$timetable = $rows['timetable'];
				$clockin = $rows['clock_in'];
				$clockout = $rows['clock_out'];
				$late = $rows['late'];
				$early = $rows['early'];
				$created = $rows['created'];
				$createdby = $rows['created_by'];

				$finger = array(
					'name' => $name,
					'date' => $date,
					'timetable' => $timetable,
					'clock_in' => $clockin,
					'clock_out' => $clockout,
					'late' => $late,
					'early' => $early,
					'created' => $created,
					'created_by' => $createdby
				);

				$this->db->insert("fingerprint_data", $finger);
				$this->db->truncate("fingerprint_data_temp");
			}
		}

		$this->db->select('finger_id');
		$carinama = $this->db->get('employees');

		if ($carinama->num_rows()) {

			$datanama = $carinama->result_array();


			foreach ($datanama as $rownama => $rowsnama) {

				$fingername = $rowsnama['finger_id'];
			}
		}

		$cariabsen = $this->db->query("SELECT a.name, COUNT( a.name )AS jumlah, 
			 b.id, b.position_id, e.clock_in as clock_in2 , e.clock_out as clock_out2,
			TIME_TO_SEC (timediff(e.clock_in,a.clock_in)) as late,
			
			(SELECT IF (b.position_id='POS008', 
			(SELECT COUNT( c.name)
			FROM fingerprint_data c
			WHERE a.name = c.name AND
			c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			)
			, 
			(SELECT COUNT( c.name))
			)
			FROM fingerprint_data c
			WHERE a.name = c.name AND
			c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			) AS absensi,
			
			(SELECT IF (b.position_id='POS008', 
			(SELECT COUNT( c.name)
			FROM fingerprint_data c
			WHERE a.name = c.name AND
			c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in ='00:00:00' 
			OR
			a.name = c.name AND
			c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_out ='00:00:00' 
			)
			, 
			(SELECT COUNT( c.name))
			)
			FROM fingerprint_data c
			WHERE a.name = c.name AND
			c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in ='00:00:00' 
			OR
			a.name = c.name AND
			c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_out ='00:00:00' 
			)  AS noabsen,
			
			(SELECT COUNT( d.employee_id )
			FROM transaction_leave d
			WHERE b.id = d.employee_id 
			AND d.from BETWEEN '$tgl1' AND '$tgl2' 
			AND d.until BETWEEN '$tgl1' AND '$tgl2'
			AND d.leave_id = 'LV001'
			AND a.clock_in ='00:00:00' 
			AND  a.clock_out ='00:00:00'
			) AS cutitahunan,

			(SELECT COUNT( d.employee_id )
			FROM transaction_leave d
			WHERE b.id = d.employee_id 
			AND d.from BETWEEN '$tgl1' AND '$tgl2' 
			AND d.until BETWEEN '$tgl1' AND '$tgl2'
			AND d.leave_id = 'LV003'
			AND a.clock_in ='00:00:00' 
			AND  a.clock_out ='00:00:00'
			) AS cutibesar,
			
			(SELECT COUNT( d.employee_id )
			FROM transaction_leave d
			WHERE b.id = d.employee_id 
			AND d.from BETWEEN '$tgl1' AND '$tgl2' 
			AND d.until BETWEEN '$tgl1' AND '$tgl2'
			AND d.leave_id = 'LV004'
			AND a.clock_in ='00:00:00' 
			AND  a.clock_out ='00:00:00'
			) AS cutihamil,
			
			(SELECT COUNT( d.employee_id )
			FROM transaction_leave d
			WHERE b.id = d.employee_id AND
			d.from BETWEEN '$tgl1' AND '$tgl2' 
			AND d.until BETWEEN '$tgl1' AND '$tgl2'
			AND d.leave_id = 'LV005'
			AND a.clock_in ='00:00:00' 
			AND  a.clock_out ='00:00:00'
			) AS cutilain,
			
			(SELECT COUNT( d.employee_id )
			FROM transaction_leave d
			WHERE b.id = d.employee_id 
			AND d.from BETWEEN '$tgl1' AND '$tgl2' 
			AND d.until BETWEEN '$tgl1' AND '$tgl2'
			AND d.leave_id = 'LV006'
			AND a.clock_in ='00:00:00' 
			AND a.clock_out ='00:00:00'
			) AS sakit,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE 
			(SELECT  d.employee_id 
			FROM transaction_leave d
			WHERE b.id = d.employee_id 
			AND d.from BETWEEN '$tgl1' AND '$tgl2' 
			AND d.until BETWEEN '$tgl1' AND '$tgl2'
			AND d.leave_id != 'LV001'
			AND d.leave_id != 'LV002'
			AND d.leave_id != 'LV003'
			AND d.leave_id != 'LV004'
			AND d.leave_id != 'LV005'
			AND d.leave_id != 'LV006'
			AND  a.clock_in ='00:00:00' 
			AND  a.clock_out ='00:00:00'
			HAVING count(*) <1
			)			
			) AS noketerangan,
			
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.late BETWEEN '5' AND '15'		
			)
			AS late5minute,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.late BETWEEN '16' AND '30'		
			)
			AS late15minute,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.late BETWEEN '31' AND '60'		
			)
			AS late30minute,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.late BETWEEN '61' AND '240'		
			)
			AS late60minute,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00' 
			AND c.late > '4'		
			)
			AS totallate,
			
			
			(SELECT SUM( c.late )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.late > '4'		
			)
			AS lateminute,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00' 
			AND c.late >= 240	
			)
			AS late220minute,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.late >= 240	
			)
			AS izinpagi240,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.late BETWEEN '61' AND '240'		
			)
			AS izinpagi60,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.early BETWEEN '61' AND '240'		
			)
			AS izinsore60,
			
			(SELECT COUNT( c.name )
			FROM fingerprint_data c
			WHERE b.finger_id = c.name
			AND c.date BETWEEN '$tgl1' AND '$tgl2' 
			AND c.clock_in !='00:00:00' AND  c.clock_out !='00:00:00'
			AND c.early >= 240		
			)
			AS izinsore240
			

			FROM fingerprint_data a
			INNER JOIN employees b ON a.name = b.finger_id
			INNER JOIN at_shifts e ON a.timetable = e.name
			WHERE a.date BETWEEN '$tgl1' AND '$tgl2' 
			GROUP BY a.name");

		if ($cariabsen->num_rows() != 0) {

			$Qry_Delete		= "DELETE FROM rpt_absensi WHERE date_from='" . $tgl1 . "' AND date_until='" . $tgl2 . "'";
			$Hasil_Del		= $this->db->query($Qry_Delete);

			$dataabsen = $cariabsen->result_array();

			foreach ($dataabsen as $row => $baris) {
				$empid = $baris['id'];
				$name = $baris['name'];
				$jumlah = $baris['jumlah'];
				$absensi = $baris['absensi'];
				$noabsen = $baris['noabsen'];
				$cutitahunan = $baris['cutitahunan'];
				$cutibesar = $baris['cutibesar'];
				$cutihamil = $baris['cutihamil'];
				$cutilain = $baris['cutilain'];
				$sakit = $baris['sakit'];
				$noketerangan = $baris['noketerangan'];
				$late5minute = $baris['late5minute'];
				$late15minute = $baris['late15minute'];
				$late30minute = $baris['late30minute'];
				$late60minute = $baris['late60minute'];
				$late220minute = $baris['late220minute'];
				$totallate = $baris['totallate'];
				$lateminute = $baris['lateminute'];
				$izinpagi60 = $baris['izinpagi60'];
				$izinpagi240 = $baris['izinpagi240'];
				$izinsore60 = $baris['izinsore60'];
				$izinsore240 = $baris['izinsore240'];




				$rptabsen = array(
					'month' => $tgl1,
					'date_from' => $tgl1,
					'date_until' => $tgl2,
					'employee_id' => $empid,
					'name' => $name,
					'jumlah' => $jumlah,
					'absensi' => $absensi,
					'noabsen' => $noabsen,
					'cutitahunan' => $cutitahunan,
					'cutibesar' => $cutibesar,
					'cutihamil' => $cutihamil,
					'cutilain' => $cutilain,
					'sakit' => $sakit,
					'noketerangan' => $noketerangan,
					'late5minute' => $late5minute,
					'late15minute' => $late15minute,
					'late30minute' => $late30minute,
					'late60minute' => $late60minute,
					'late220minute' => $late220minute,
					'totallate' => $totallate,
					'lateminute' => $lateminute,
					'izinpagi60' => $izinpagi60,
					'izinpagi240' => $izinpagi240,
					'izinsore60' => $izinsore60,
					'izinsore240' => $izinsore240
				);

				$this->db->insert("rpt_absensi", $rptabsen);
				//$this->db->truncate("fingerprint_data_temp");
			}

			//return $cariabsen->result();
		}




		$Ambil_Report   = "SELECT * FROM rpt_absensi WHERE date_from='" . $tgl1 . "' AND date_until='" . $tgl2 . "'";
		$Hasil_Report	= $this->db->query($Ambil_Report);

		if ($Hasil_Report->num_rows() != 0) {
			return $Hasil_Report->result();
		} else {
			return false;
		}
	}

	function getSalarycount($tgl1, $tgl2)
	{

		$tahun = tahun($tgl1);

		$this->db->select('*');
		$this->db->from('rpt_potongan');
		$querymaster = $this->db->get();

		if ($querymaster->num_rows()) {
			$new_rptpotongan = $querymaster->result_array();

			foreach ($new_rptpotongan as $row => $pot) {
				$this->db->insert("ms_rptpotongan", $pot);
			}
			$this->db->truncate("rpt_potongan");
		}


		$this->db->select('*');
		$this->db->from('rpt_salary');
		$querymaster = $this->db->get();

		if ($querymaster->num_rows()) {
			$new_rptpotongan = $querymaster->result_array();

			foreach ($new_rptpotongan as $row => $pot) {
				$this->db->insert("ms_rptsalary", $pot);
			}
			$this->db->truncate("rpt_salary");
		}


		$this->db->select('*');
		$this->db->from('rpt_tunjangan');
		$querymaster = $this->db->get();

		if ($querymaster->num_rows()) {
			$new_rptpotongan = $querymaster->result_array();

			foreach ($new_rptpotongan as $row => $pot) {
				$this->db->insert("ms_rpttunjangan", $pot);
			}
			$this->db->truncate("rpt_tunjangan");
		}




		$data_session			= $this->session->userdata;
		$createdby				= $data_session['User']['username'];
		$created				= date('Y-m-d H:i:s');

		$Ambil_Report3   = "SELECT a.*,b.*,c.*
			FROM rpt_absensi a 
			LEFT JOIN employees b ON a.employee_id = b.id
			INNER JOIN ms_policy c ON b.position_id = c.position_id
			WHERE date_from='" . $tgl1 . "' AND date_until='" . $tgl2 . "'";
		$Hasil_Report3	= $this->db->query($Ambil_Report3);

		if ($Hasil_Report3->num_rows()) {
			$data2 = $Hasil_Report3->result_array();
			foreach ($data2 as $row => $val) {

				$employee_id 	= $val['employee_id'];
				$late5minute 	= $val['late5minute'];
				$late15minute 	= $val['late15minute'];
				$late30minute 	= $val['late30minute'];
				$late60minute 	= $val['late60minute'];
				$late220minute 	= $val['late220minute'];
				$noabsen	 	= $val['noabsen'];
				$notabsen		= $val['notabsen'];
				$pot_5			= $val['late5'];
				$pot_15			= $val['late15'];
				$pot_30			= $val['late30'];
				$pot_60			= $val['late60'];
				$pot_220		= $val['late240'];

				if ($notabsen == 'Y') {
					$tidakabsen	= $noabsen * 15000;
				} else {
					$tidakabsen	= 0;
				}

				if ($pot_5 == 'Y') {
					$late5 		= $late5minute * 7500;
				} else {
					$late5	= 0;
				}

				if ($pot_15 == 'Y') {
					$late15 	= $late15minute * 15000;
				} else {
					$late15	= 0;
				}

				if ($pot_30 == 'Y') {
					$late30 	= $late15minute * 22500;
				} else {
					$late30	= 0;
				}

				if ($pot_60 == 'Y') {
					$late60		= $late60minute * 35500;
				} else {
					$late60	= 0;
				}

				if ($pot_220 == 'Y') {
					$late220	= $late220minute * 75000;
				} else {
					$late220	= 0;
				}


				$potongan = array(
					'month'	=> $tgl1,
					'date_from'	=> $tgl1,
					'date_until' => $tgl2,
					'employee_id' => $employee_id,
					'pot_noabsen' => $tidakabsen,
					'pot_late5' => $late5,
					'pot_late15' => $late15,
					'pot_late30' => $late30,
					'pot_late60' => $late60,
					'pot_late220' => $late220,
					'created' => $created,
					'created_by' => $createdby
				);


				$this->db->insert("rpt_potongan", $potongan);
			}
		}

		$Ambil_Transport   = "SELECT * FROM ms_allowance WHERE id='ALL0001'";
		$Hasil_Transport	= $this->db->query($Ambil_Transport);

		if ($Hasil_Transport->num_rows()) {
			$datatr = $Hasil_Transport->result_array();
			foreach ($datatr as $row => $tr) {

				$tr_allowance 	= $tr['jumlah'];
			}
		}

		$Ambil_Insentif   = "SELECT * FROM ms_allowance WHERE id='ALL0003'";
		$Hasil_Insentif	= $this->db->query($Ambil_Insentif);

		if ($Hasil_Insentif->num_rows()) {
			$datains = $Hasil_Insentif->result_array();
			foreach ($datains as $row => $ins) {

				$ins_allowance 	= $ins['jumlah'];
			}
		}



		$Ambil_absen   = "SELECT a.*, b.*, b.salary,  b.hiredate, c.transport, c.insentif
					FROM rpt_absensi a
					LEFT JOIN employees b ON a.employee_id = b.id
					INNER JOIN ms_policy c ON b.position_id = c.position_id
					WHERE a.date_from='$tgl1' AND a.date_until='$tgl2'";
		$Hasil_absen	= $this->db->query($Ambil_absen);

		if ($Hasil_absen->num_rows()) {
			$datatj = $Hasil_absen->result_array();
			foreach ($datatj as $row => $tj) {

				$hiredate	 	= $tj['hiredate'];
				$employee_id 	= $tj['employee_id'];
				$totallate	 	= $tj['totallate'];
				$kehadiran	 	= $tj['jumlah'];
				$absensi	 	= $tj['absensi'];
				$position	 	= $tj['position_id'];
				$tr_code	 	= $tj['transport'];
				$ins_code	 	= $tj['insentif'];

				$awal  = date_create($hiredate);
				$akhir = date_create(); // waktu sekarang
				$diff  = date_diff($awal, $akhir);

				$bulan = $diff->m;




				if ($totallate <= 3 and $tr_code == 'Y') {

					$transport_tj	= $tr_allowance;
				} else {
					$transport_tj	= 0;
				}

				if ($totallate <= 3 and $kehadiran == $absensi  and $bulan >= 3 and  $ins_code == 'Y') {

					$insentif	= $ins_allowance;
				} else {
					$insentif	= 0;
				}

				$tunjangan = array(
					'month'	=> $tgl1,
					'date_from'	=> $tgl1,
					'date_until' => $tgl2,
					'employee_id' => $employee_id,
					'insentif' => $insentif,
					'transport' => $transport_tj,
					'tr_code' => $ins_code,
					'created' => $created,
					'created_by' => $createdby
				);


				$this->db->insert("rpt_tunjangan", $tunjangan);
			}
		}







		$carigaji = $this->db->query("SELECT a.name as nama, b.*, b.salary, c.*, d.*
					FROM rpt_absensi a
					INNER JOIN employees b ON a.employee_id = b.id
					INNER JOIN rpt_potongan c ON a.employee_id = c.employee_id
					INNER JOIN rpt_tunjangan d ON b.id = d.employee_id
					WHERE a.date_from='$tgl1' AND a.date_until='$tgl2'");


		if ($carigaji->num_rows()) {
			$datagaji = $carigaji->result_array();


			foreach ($datagaji as $row => $gaji) {

				$employee_id 	= $gaji['employee_id'];
				$name 			= $gaji['nama'];
				$noabsen2 		= $gaji['pot_noabsen'];
				$potlate5 		= $gaji['pot_late5'];
				$potlate15 		= $gaji['pot_late15'];
				$potlate30 		= $gaji['pot_late30'];
				$potlate60 		= $gaji['pot_late60'];
				$potlate220 	= $gaji['pot_late220'];
				$salary		 	= $gaji['salary'];
				$transport2 	= $gaji['transport'];
				$insentif2 		= $gaji['insentif'];
				$totalpotongan	= $potlate5 + $potlate15 + $potlate30 + $potlate60 + $potlate220 + $noabsen2;


				$inputsalary = array(
					'month'	=> $tgl1,
					'date_from'	=> $tgl1,
					'date_until' => $tgl2,
					'name' => $name,
					'employee_id' => $employee_id,
					'transport' => $transport2,
					'insentif' => $insentif2,
					'pot_noabsen' => $noabsen2,
					'pot_late5' => $potlate5,
					'pot_late15' => $potlate15,
					'pot_late30' => $potlate30,
					'pot_late60' => $potlate60,
					'pot_late220' => $potlate220,
					'total_potongan' => $totalpotongan,
					'salary' => $salary,
					'created' => $created,
					'created_by' => $createdby
				);


				$this->db->insert("rpt_salary", $inputsalary);
			}

			$Ambil_gaji   = "SELECT * FROM rpt_salary WHERE date_from='" . $tgl1 . "' AND date_until='" . $tgl2 . "'";
			$Hasil_gaji	= $this->db->query($Ambil_gaji);

			return $Hasil_gaji->result();
		} else {
			return false;
		}
	}

	public function getMasterSalary()
	{
		$this->db->select('a.*,b.name as nama, b.nik');
		$this->db->from('salary a');
		$this->db->join('employees b', 'b.id=a.employee_id', 'left');
		$this->db->where(array('b.flag_active' => 'Y'));
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataAllowance()
	{
		$this->db->select('a.*');
		$this->db->from('ms_allowance a');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataPotongan()
	{
		$this->db->select('a.*');
		$this->db->from('ms_potongan a');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	public function getdataPolicy()
	{
		$this->db->select('a.*,b.name');
		$this->db->from('ms_policy a');
		$this->db->join('positions b', 'b.id=a.position_id', 'left');
		$query = $this->db->get();

		return $query->result();
	}

	public function getDataBpjs()
	{
		$this->db->select('a.*');
		$this->db->from('ms_bpjs a');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function get_data_employees()
	{
		$draw = $this->input->post('draw');
		$start = $this->input->post('start');
		$length = $this->input->post('length');
		$search = $this->input->post('search');

		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('hr_sentral.employees a');
		$this->db->join('hr_sentral.companies b', 'b.id=a.company_id', 'left');
		$this->db->join('hr_sentral.departments c', 'c.id=a.department_id', 'left');
		$this->db->join('hr_sentral.divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('hr_sentral.titles e', 'e.id=a.title_id', 'left');
		$this->db->join('hr_sentral.contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('hr_sentral.contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('hr_sentral.contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('hr_sentral.contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('hr_sentral.positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.flag_active' => 'Y', 'a.company_id' => 'COM003'));
		if(!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.id', $search['value'], 'both');
			$this->db->or_like('a.nik', $search['value'], 'both');
			$this->db->or_like('a.name', $search['value'], 'both');
			$this->db->or_like('a.hometown', $search['value'], 'both');
			$this->db->or_like('a.birthday', $search['value'], 'both');
			$this->db->or_like('a.birthday', $search['value'], 'both');
			$this->db->or_like('a.nationality', $search['value'], 'both');
			$this->db->or_like('a.flag_active', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->limit($length, $start);
		$query = $this->db->get();

		$this->db->select('a.*,b.name as company_name,c.name as department_name,
								d.name as division_name, e.name as title_name,
								f.name as firstcontract,g.name as secondcontract,
								h.name as thirdcontract,i.name as permanent, j.name as position_name');
		$this->db->from('hr_sentral.employees a');
		$this->db->join('hr_sentral.companies b', 'b.id=a.company_id', 'left');
		$this->db->join('hr_sentral.departments c', 'c.id=a.department_id', 'left');
		$this->db->join('hr_sentral.divisions d', 'd.id=a.division_id', 'left');
		$this->db->join('hr_sentral.titles e', 'e.id=a.title_id', 'left');
		$this->db->join('hr_sentral.contracts f', 'f.id=a.firstcontract_id', 'left');
		$this->db->join('hr_sentral.contracts g', 'g.id=a.secondcontract_id', 'left');
		$this->db->join('hr_sentral.contracts h', 'h.id=a.thirdcontract_id', 'left');
		$this->db->join('hr_sentral.contracts i', 'i.id=a.permanent_id', 'left');
		$this->db->join('hr_sentral.positions j', 'j.id=a.position_id', 'left');
		$this->db->where(array('a.flag_active' => 'Y', 'a.company_id' => 'COM003'));
		if(!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.id', $search['value'], 'both');
			$this->db->or_like('a.nik', $search['value'], 'both');
			$this->db->or_like('a.name', $search['value'], 'both');
			$this->db->or_like('a.hometown', $search['value'], 'both');
			$this->db->or_like('a.birthday', $search['value'], 'both');
			$this->db->or_like('a.birthday', $search['value'], 'both');
			$this->db->or_like('a.nationality', $search['value'], 'both');
			$this->db->or_like('a.flag_active', $search['value'], 'both');
			$this->db->group_end();
		}
		$query_all = $this->db->get();

		$hasil = [];

		$int	= (0 + $start);
		foreach ($query->result() as $datas) {
			$int++;

			$awal  = date_create($datas->hiredate);
			$akhir = date_create(); // waktu sekarang
			// $akhir = ; // waktu sekarang
			@$diff  = date_diff($awal, $akhir);

			$th = 'Th';
			$bl = 'Bln';

			@$tahun = $diff->y;
			@$bulan = $diff->m;
			@$hari  = $diff->d;

			$agama = $datas->relid;
			$jk    = $datas->genderid;

			$permanent    		= $datas->permanent_id;
			$firstcontract   	= $datas->firstcontract_id;
			$secondcontract	= $datas->secondcontract_id;
			$thirdcontract		= $datas->thirdcontract_id;


			if ($permanent == 'CTR004') {

				$status = 'Tetap';
			} elseif ($thirdcontract == 'CTR003') {

				$status = 'Kontrak Ketiga';
			} elseif ($secondcontract == 'CTR002') {
				$status = 'Kontrak Kedua';
			} elseif ($firstcontract == 'CTR001') {

				$status = 'Kontrak Pertama';
			} elseif ($firstcontract == '0' && $secondcontract == '0' && $thirdcontract == '0' && $permanent == '0') {
				$status = 'Belum Kontrak';
			}




			if ($jk === 'L') {
				$genderid = 'Laki-laki';
			} elseif ($jk === 'P') {
				$genderid = 'Perempuan';
			}
			
			if ($agama == '1') {
				$religi = 'Islam';
			} elseif ($agama == '2') {
				$religi = 'Katolik';
			} elseif ($agama == '3') {
				$religi = 'Kristen';
			} elseif ($agama == '4') {
				$religi = 'Hindu';
			} elseif ($agama == '5') {
				$religi = 'Budha';
			} elseif ($agama == '6') {
				$religi = 'Kong Hu Chu';
			}

			$button = "<a href='" . site_url('employees/view/' . $datas->id) . "' class='btn btn-sm btn-info' title='View Data' data-role='qtip'><i class='fa fa-eye'></i></a>";

			$hasil[] = [
				'no' => $int,
				'id' => $datas->id,
				'nik' => $datas->nik,
				'name' => $datas->name,
				'hometown' => $datas->hometown,
				'birthday' => $datas->birthday,
				'gender' => $genderid,
				'religion' => $religi,
				'nationality' => $datas->nationality,
				'employee_status' => $status,
				'status_aktif' => $datas->flag_active,
				'option' => $button
			];
		}

		echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $query_all->num_rows(),
            'recordsFiltered' => $query_all->num_rows(),
            'data' => $hasil
        ]);
	}
}
