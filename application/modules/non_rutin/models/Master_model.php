<?php

class Master_model extends BF_Model
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

	public function getDataOrderBy($table, $where_field = '', $where_value = '', $field_order)
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->order_by($field_order, 'ASC')->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->order_by($field_order, 'ASC')->get($table);
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

	public function get_select($typex = NULL, $idm = NULL)
	{
		if ($typex == 'veil') {
			$type = 'VEIL';
			$kode = 'TYP-0003';
		} elseif ($typex == 'csm') {
			$type = 'CSM (MAT)';
			$kode = 'TYP-0004';
		} else {
			$type = 'woven rooving';
			$kode = 'TYP-0006';
		}
		$utype = strtoupper($type);
		$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category = '" . $kode . "'")->result_array();
		$sel = "

		<input type='hidden' name='glass[detail_name][]' value='GLASS'>
		<input type='hidden' name='glass[id_category][]' value='" . $kode . "'>
		<select name='glass[id_material][]' id='$typex' class='form-control input-sm chosen-select'>
			<option value='0'>Select $utype</option>";
		foreach ($ListMat as $val => $valx) {
			if ($valx['id_material'] == $idm) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			$sel .= "<option value='" . $valx['id_material'] . "' " . $selected . ">" . strtoupper(strtolower($valx['nm_material'])) . "</option>";
		}
		$sel .= "<option value='MTL-1903000'>NONE MATERIAL</option>";
		$sel .= "</select>";
		return $sel;
	}

	public function get_select_detail_OLD($type = NULL, $name = NULL)
	{
		$utype = strtoupper($type);
		$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category = '$type'")->result_array();
		if ($type == 'TYP-001519') {
			$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category IN ('TYP-0015','TYP-0019')")->result_array();
		}
		if ($type == 'WAX') {
			$ListMat	= $this->db->query("SELECT * FROM raw_materials WHERE id_category='TYP-0008' OR id_category='TYP-0019' ORDER BY nm_material ASC")->result_array();
		}
		$ListCat		= $this->db->query("SELECT * FROM raw_categories WHERE id_category = '$type'")->row();
		$sel = "
		<input type='hidden' name='resinnadd[detail_name][]' value='RESIN AND ADD'>
		<input type='hidden' name='resinnadd[id_category][]' value='" . $type . "'>
		<select name='$name' id='$type' class='form-control input-sm'>
			<option value='0'>Select " . $ListCat->category . "</option>";
		foreach ($ListMat as $val => $valx) {
			$sel .= "<option value='" . $valx['id_material'] . "'>" . strtoupper(strtolower($valx['nm_material'])) . "</option>";
		}
		$sel .= "<option value='MTL-1903000'>NONE MATERIAL</option>";
		$sel .= "</select>";
		return $sel;
	}

	public function get_select_detailOLD($type = NULL, $name = NULL, $cat = NULL)
	{
		$utype = strtoupper($type);
		$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category = '$type'")->result_array();
		if ($type == 'WAX') {
			$ListMat	= $this->db->query("SELECT * FROM raw_materials WHERE id_category='TYP-0008' OR id_category='TYP-0019' ORDER BY nm_material ASC")->result_array();
		}
		if ($type == 'TYP-001519') {
			$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category IN ('TYP-0015','TYP-0019')")->result_array();
		}
		$ListCat		= $this->db->query("SELECT * FROM raw_categories WHERE id_category = '$type'")->row();
		$sel = "
		<input type='hidden' name='resinnadd[detail_name][]' value='RESIN AND ADD'>
		<input type='hidden' name='resinnadd[nm_category][]' value='$cat'>
		<input type='hidden' name='resinnadd[id_category][]' value='" . $type . "'>
		<select name='$name' id='$type' class='form-control input-sm'>
			<option value='0'>Select " . $cat . "</option>";
		foreach ($ListMat as $val => $valx) {
			$sel .= "<option value='" . $valx['id_material'] . "'>" . strtoupper(strtolower($valx['nm_material'])) . "</option>";
		}
		$sel .= "<option value='MTL-1903000'>NONE MATERIAL</option>";
		$sel .= "</select>";
		return $sel;
	}

	public function get_select_detail($type = NULL, $name = NULL, $cat = NULL, $idm = NULL)
	{
		$utype = strtoupper($type);
		$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category = '$type'")->result_array();
		if ($type == 'WAX') {
			$ListMat	= $this->db->query("SELECT * FROM raw_materials WHERE id_category='TYP-0008' OR id_category='TYP-0019' ORDER BY nm_material ASC")->result_array();
		}
		if ($type == 'TYP-001519') {
			$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category IN ('TYP-0015','TYP-0019')")->result_array();
		}
		if ($cat == 'DEMPUL') {
			$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category IN ('TYP-0025','TYP-0019')")->result_array();
		}
		if ($cat == 'SOLUTION WAX') {
			$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category IN ('TYP-0019')")->result_array();
		}
		$ListCat		= $this->db->query("SELECT * FROM raw_categories WHERE id_category = '$type'")->row();
		$sel = "
		<input type='hidden' name='resinnadd[detail_name][]' value='RESIN AND ADD'>
		<input type='hidden' name='resinnadd[nm_category][]' value='$cat'>
		<input type='hidden' name='resinnadd[id_category][]' value='" . $type . "'>
		<select name='$name' id='$type' class='form-control input-sm chosen-select'>
			<option value='0'>Select " . $cat . "</option>";
		foreach ($ListMat as $val => $valx) {
			if ($valx['id_material'] == $idm) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			if ('MTL-1903000' == $idm) {
				$selected = 'selected';
			}
			$sel .= "<option value='" . $valx['id_material'] . "' " . $selected . ">" . strtoupper(strtolower($valx['nm_material'])) . "</option>";
		}
		$sel .= "<option value='MTL-1903000' " . $selected . ">NONE MATERIAL</option>";
		$sel .= "</select>";
		return $sel;
	}

	public function get_select_detailx($type = NULL, $name = NULL, $cat = NULL, $idm = NULL)
	{
		$utype = strtoupper($type);
		$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category = '$type'")->result_array();
		if ($type == 'WAX') {
			$ListMat	= $this->db->query("SELECT * FROM raw_materials WHERE id_category='TYP-0008' OR id_category='TYP-0019' ORDER BY nm_material ASC")->result_array();
		}
		if ($type == 'TYP-001519') {
			$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category IN ('TYP-0015','TYP-0019')")->result_array();
		}
		if ($cat == 'DEMPUL') {
			$ListMat		= $this->db->query("SELECT * FROM raw_materials WHERE id_category IN ('TYP-0025','TYP-0019')")->result_array();
		}
		$ListCat		= $this->db->query("SELECT * FROM raw_categories WHERE id_category = '$type'")->row();
		$sel = "
		<select name='$name' id='$type' class='form-control input-sm chosen-select'>
			<option value='0'>Select " . $cat . "</option>";
		foreach ($ListMat as $val => $valx) {
			if ($valx['id_material'] == $idm) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			if ('MTL-1903000' == $idm) {
				$selected = 'selected';
			}
			$sel .= "<option value='" . $valx['id_material'] . "' " . $selected . ">" . strtoupper(strtolower($valx['nm_material'])) . "</option>";
		}
		$sel .= "<option value='MTL-1903000' " . $selected . ">NONE MATERIAL</option>";
		$sel .= "</select>";
		return $sel;
	}

	function GetDeptCombo($id = '')
	{
		$aCombo		= array();
		$this->db->select('a.id, a.nm_dept');
		$this->db->from('department a');
		if ($id != '') {
			$this->db->where('a.id', $id);
		}
		$this->db->where('a.deleted', 'N');
		$this->db->order_by('a.nm_dept', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id']]	= $vals['nm_dept'];
			}
		}
		return $aCombo;
	}

	function GetCategory()
	{
		$category = array('' => '', 'EXPENSE' => 'EXPENSE', 'RUTIN' => 'RUTIN', 'NON RUTIN' => 'NON RUTIN', 'PEMBAYARAN PERIODIK' => 'PEMBAYARAN PERIODIK', 'UMUM' => 'UMUM');
		return $category;
	}

	public function dataDelete($table, $where)
	{
		$this->db->delete($table, $where);
		return $this->db->affected_rows();
	}

	public function dataUpdate($table, $data, $where)
	{
		$this->db->update($table, $data, $where);
		return $this->db->affected_rows();
	}

	function GetJenis()
	{
		$jenis = array('' => '', 'VARIABLE' => 'VARIABLE', 'FIX COST BULANAN' => 'FIX COST BULANAN', 'FIX COST TAHUNAN' => 'FIX COST TAHUNAN');
		return $jenis;
	}

	function GetCoaCombo($level = '5')
	{
		$aMenu	= array();
		$aMenu[0] = 'Select An Option';
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->where('a.level', $level);
		$this->db->order_by('a.no_perkiraan', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'] . ' - ' . $vals['nama'];
			}
		}
		return $aMenu;
	}

	function GetBudgetComboCategory($kategori = '', $tahun = '', $divisi = '')
	{
		$aMenu		= array();
		$this->db->select('a.coa, b.no_perkiraan , b.nama as nama_perkiraan, c.nm_dept');
		$this->db->from('ms_budget a');
		$this->db->join(DBACC . '.coa_master b', 'a.coa=b.no_perkiraan');
		$this->db->join('department c', 'a.divisi=c.id', 'left');
		if ($kategori != '') $this->db->where("a.kategori", $kategori);
		$this->db->where("a.tahun", $tahun);
		if ($divisi != '') $this->db->where("a.divisi", $divisi);
		$this->db->where("b.level='5'");
		$this->db->order_by('b.no_perkiraan', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['coa']]	= $vals['coa'] . ' - ' . $vals['nama_perkiraan'];
			}
		}
		return $aMenu;
	}

	function GetComboBudget($divisi = '', $kategori = '', $tahun = '')
	{
		$aMenu	= array();
		$aMenu[0] = 'Select An Option';
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		if ($divisi != '') $this->db->where('b.divisi', $divisi);
		if ($kategori != '') $this->db->where('b.kategori', $kategori);
		if ($tahun == '') $tahun = date("Y");
		$this->db->where('b.tahun', $tahun);
		$this->db->join('ms_budget b', 'a.no_perkiraan=b.coa');
		$this->db->order_by('a.no_perkiraan', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'] . ' - ' . $vals['nama'];
			}
		}
		return $aMenu;
	}

	public function dataSave($table, $data)
	{
		$this->db->insert($table, $data);
		$last_id = $this->db->insert_id();
		return $last_id;
	}

	public function GetOneData($table, $where)
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($where);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function get_coa_payment($modul = '', $no_doc = '')
	{
		$this->db->select('a.*');
		$this->db->from('tr_coa_payment a');
		$this->db->where('a.modul', $modul);
		$this->db->where('a.no_doc', $no_doc);
		$this->db->order_by('a.id', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
}
