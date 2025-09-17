<?php

class Master_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code

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

	public function getDatadiv()
	{
		$this->db->select('a.*,b.name as company_name');
		$this->db->from('hr_sentral.divisions a');
		$this->db->join('hr_sentral.companies b', 'b.id=a.company_id', 'left');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDatadept()
	{
		$this->db->select('a.*, b.name as division_name, c.name as company_name');
		$this->db->from('hr_sentral.departments a');
		$this->db->join('hr_sentral.divisions b', 'b.id=a.division_id', 'left');
		$this->db->join('hr_sentral.companies c', 'c.id=a.company_id', 'left');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getShifts()
	{
		$this->db->select('*');
		$this->db->from('hr_sentral.at_shifts');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getDataShifts($where = array())
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
				$aMenu[$vals['timetable']]	= $vals['timetable'];
			}
		}
		return $aMenu;
	}

	public function getDatatitle()
	{
		$this->db->select('a.*, b.name as department_name, c.name as division_name, d.name as company_name');
		$this->db->from('titles a');
		$this->db->join('hr_sentral.departments b', 'b.id=a.department_id', 'left');
		$this->db->join('hr_sentral.divisions c', 'c.id=a.division_id', 'left');
		$this->db->join('hr_sentral.companies d', 'd.id=a.company_id', 'left');
		$query = $this->db->get();
		//echo "<pre>";print_r($query->result());
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
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


	public function getCount($table, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}
		return $query->num_rows();
	}



	public function getUpdate($table, $data, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->where(array($where_field => $where_value));
		}
		$result	= $this->db->update($table, $data);
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
			$query = $this->db->get_where('hr_sentral.menus', $where);
		} else {
			$query = $this->db->get('hr_sentral.menus');
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
	public function getGrades($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.grades', $where);
		} else {
			$query = $this->db->get('hr_sentral.grades');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getLeaves($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('hr_sentral.at_leaves', $where);
		} else {
			$query = $this->db->get('hr_sentral.at_leaves');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getContracts($where = array())
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


	function code_otomatis($table, $pre)
	{
		$this->db->select('Right(id,3) as kode ', false);
		$this->db->order_by('id', 'desc');
		$this->db->limit(1);
		$query = $this->db->get($table);
		if ($query->num_rows() <> 0) {
			$data = $query->row();
			$kode = intval($data->kode) + 1;
		} else {
			$kode = 1;
		}
		$kodemax = str_pad($kode, 3, "0", STR_PAD_LEFT);
		$kodediv  = "$pre" . $kodemax;
		return $kodediv;
	}

	public function getPolicy($where = array())
	{
		$aMenu		= array();
		if (!empty($where)) {
			$query = $this->db->get_where('ms_policy', $where);
		} else {
			$query = $this->db->get('ms_policy');
		}

		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['id'];
			}
		}
		return $aMenu;
	}
}
