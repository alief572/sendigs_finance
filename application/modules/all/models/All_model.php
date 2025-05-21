<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "All Model"
 */

class All_model extends BF_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	// save data
	public function dataSave($table, $data)
	{
		$this->db->insert($table, $data);
		$last_id = $this->db->insert_id();
		return $last_id;
	}

	// update data
	public function dataUpdate($table, $data, $where)
	{
		$this->db->update($table, $data, $where);
		return $this->db->affected_rows();
	}

	// delete data
	public function dataDelete($table, $where)
	{
		$this->db->delete($table, $where);
		return $this->db->affected_rows();
	}

	// Get one data
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

	// Get data table
	public function GetOneTable($table, $where = '', $orderby = '')
	{
		$this->db->select('*');
		$this->db->from($table);
		if ($where !== '') $this->db->where($where);
		if ($orderby !== '') $this->db->order_by($orderby, 'asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// list data material
	public function GetListMaterial($where = '')
	{
		$this->db->select('a.*');
		$this->db->from('ms_material a');
		if ($where != '') {
			$this->db->where($where);
		}
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetKursCombo()
	{
		$aCombo		= array();
		$aCombo['IDR'] = 'IDR - Rupiah';
		$this->db->select('a.kode, a.mata_uang');
		$this->db->from('mata_uang a');
		$this->db->where('a.kode !=', 'IDR');
		$this->db->order_by('a.kode', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['kode']]	= $vals['kode'] . ' - ' . $vals['mata_uang'];
			}
		}
		return $aCombo;
	}

	function GetSatuanMaterial($idmaterial = "")
	{
		$this->db->select('a.nama');
		$this->db->from('ms_satuan a');
		if ($idmaterial != '') {
			$this->db->where("a.nama IN (select nama_satuan from ms_material_konversi where id_material='" . $idmaterial . "')", NULL, FALSE);
		}
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetWhCombo()
	{
		$aCombo		= array();
		$this->db->select('a.wh_code, a.wh_name');
		$this->db->from('ms_warehouse a');
		$this->db->order_by('a.wh_name', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['wh_code']]	= $vals['wh_code'] . ' - ' . $vals['wh_name'];
			}
		}
		return $aCombo;
	}

	function GetInventoryTypeCombo($where = '')
	{
		$aCombo = array();
		$this->db->select('a.id_type, a.nama');
		$this->db->from('ms_inventory_type a');
		if ($where != '') {
			$this->db->where($where);
		}
		$this->db->where('aktif', 'aktif', FALSE);
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id_type']]	= $vals['nama'];
			}
		}
		return $aCombo;
	}

	function GetAutoGenerate($tipe)
	{
		$newcode = '';
		$data = $this->GetOneData('ms_generate', array('tipe' => $tipe));
		if ($data !== false) {
			if (stripos($data->info, 'YEAR', 0) !== false) {
				if ($data->info3 != date("Y")) {
					$years = date("Y");
					$number = 1;
					$newnumber = sprintf('%0' . $data->info4 . 'd', $number);
				} else {
					$years = $data->info3;
					$number = ($data->info2 + 1);
					$newnumber = sprintf('%0' . $data->info4 . 'd', $number);
				}
				$newcode = str_ireplace('XXXX', $newnumber, $data->info);
				$newcode = str_ireplace('YEAR', $years, $newcode);
				$newdata = array('info2' => $number, 'info3' => $years);
			} else {
				$number = ($data->info2 + 1);
				$newnumber = sprintf('%0' . $data->info4 . 'd', $number);
				$newcode = str_ireplace('XXXX', $newnumber, $data->info);
				$newdata = array('info2' => $number);
			}
			$this->dataUpdate('ms_generate', $newdata, array('tipe' => $tipe));
			return $newcode;
		} else {
			return false;
		}
	}

	function GetSupplierCombo()
	{
		$aCombo		= array();
		$this->db->select('a.id_supplier, a.nm_supplier');
		$this->db->from('master_supplier_backup a');
		$this->db->order_by('a.nm_supplier', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id_supplier']]	= $vals['nm_supplier'];
			}
		}
		return $aCombo;
	}

	public function GetMaterialStockList($where)
	{
		$this->db->select('a.*, b.stock, (a.spec13-b.stock) as material_qty, c.nama_satuan satuan');
		$this->db->from('ms_material a');
		$this->db->join('ms_material_konversi c', 'a.id_material=c.id_material');
		$this->db->join('(select sum(stock)as stock ,id_material, satuan from ms_warehouse_stock group by id_material,satuan) b', 'a.id_material=b.id_material and b.satuan=c.nama_satuan', 'left');
		if ($where != '') {
			$this->db->where($where);
		}
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function update_stock_in($data)
	{
		$id_material = $data['material_id'];
		$qty_in = $data['material_qty'];
		$harga = $data['material_price'];
		$satuan = $data['material_unit'];
		$wh_code = $data['wh_code'];
		$code = $data['code'];
		$doc_no = $data['doc_no'];
		$tanggal = $data['tanggal'];
		$created_by = $data['created_by'];
		$created_on = $data['created_on'];
		$modified_by = $data['created_by'];
		$modified_on = $data['created_on'];

		//cek ms_warehouse_stock
		$where = array('id_material' => $id_material, 'wh_code' => $wh_code, 'satuan' => $satuan);
		$data_stok = $this->GetOneData('ms_warehouse_stock', $where);
		if ($data_stok !== false) {
			$harga_rata = ((($data_stok->stock * $data_stok->harga) + ($qty_in * $harga)) / ($data_stok->stock + $qty_in));
			$datatosave = array('stock' => ($data_stok->stock + $qty_in), 'harga' => $harga_rata, 'modified_by' => $modified_by, 'modified_on' => $modified_on);
			$this->dataUpdate('ms_warehouse_stock', $datatosave, $where);
		} else {
			$datatosave = array('stock' => $qty_in, 'harga' => $harga, 'id_material' => $id_material, 'wh_code' => $wh_code, 'satuan' => $satuan, 'created_by' => $created_by, 'created_on' => $created_on);
			$this->dataSave('ms_warehouse_stock', $datatosave);
		}
		//cek ms_warehouse_stock_log
		$this->db->select('*');
		$this->db->from('ms_warehouse_stock_log');
		$this->db->where($where);
		$this->db->order_by('id', 'desc');
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			$data_stok = $query->row();
			$datatosave = array('stock_awal' => $data_stok->stock_akhir, 'stock_out' => 0, 'stock_in' => $qty_in, 'stock_akhir' => ($data_stok->stock_akhir + $qty_in), 'harga' => $harga, 'id_material' => $id_material, 'wh_code' => $wh_code, 'satuan' => $satuan, 'tanggal' => $tanggal, 'doc_no' => $doc_no, 'code' => $code, 'created_by' => $created_by, 'created_on' => $created_on);
		} else {
			$datatosave = array('stock_awal' => 0, 'stock_out' => 0, 'stock_in' => $qty_in, 'stock_akhir' => $qty_in, 'harga' => $harga, 'id_material' => $id_material, 'wh_code' => $wh_code, 'satuan' => $satuan, 'tanggal' => $tanggal, 'doc_no' => $doc_no, 'code' => $code, 'created_by' => $created_by, 'created_on' => $created_on);
		}
		$this->dataSave('ms_warehouse_stock_log', $datatosave);
	}

	function GetComboBudget($where = '')
	{
		$aMenu	= array();
		$aMenu[0] = 'Select An Option';
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->join('ms_budget b', 'a.no_perkiraan=b.coa');
		if ($where != '') {
			$this->db->where($where);
		}
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

	function GetCoaCombo($level = '5', $custom_where = null)
	{
		$aMenu	= array();
		$aMenu[0] = 'Select An Option';
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		// $this->db->where('a.level', $level);
		if ($custom_where !== null) {
			$this->db->where($custom_where);
		}
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

	function GetListCoa($coa_numbers = [])
	{
		$aMenu	= array();
		$aMenu[0] = 'Select An Option';
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		if (!empty($coa_numbers)) {
			$this->db->where_in('a.no_perkiraan', $coa_numbers);
		}
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

	function GetTypePaymentCombo()
	{
		$aMenu	= array('' => 'Select An option', 'CASH' => 'CASH', 'GIRO' => 'GIRO', 'TRANSFER' => 'TRANSFER');
		return $aMenu;
	}

	function GetCoaComboCategory()
	{
		$aMenu		= array();
		$this->db->select('a.id_type, a.nama');
		$this->db->from('ms_inventory_type a');
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id_type']]	= $vals['nama'];
			}
		}
		return $aMenu;
	}
	function GetDivisiCombo()
	{
		$aCombo		= array();
		$this->db->select('a.id_divisi, a.nm_divisi');
		$this->db->from('divisi a');
		$this->db->order_by('a.nm_divisi', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		$aCombo[]	= '';
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id_divisi']]	= $vals['nm_divisi'];
			}
		}
		return $aCombo;
	}

	function GetDeptCombo($key = '')
	{
		$aCombo		= array();
		$this->db->select('a.id, UPPER(a.nama) AS nm_dept');
		$this->db->from('ms_department a');
		$this->db->where('a.deleted_by', null);
		if ($key != '') $this->db->where('a.id', $key);
		//		$this->db->where('a.company_id','COM003');
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($key == '') $aCombo[]	= '';
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id']]	= $vals['nm_dept'];
			}
		}
		return $aCombo;
	}

	function GetCostCenterCombo($dept = '')
	{
		$aCombo		= array();
		$this->db->select('a.id, a.cost_center');
		$this->db->from('department_center a');
		$this->db->where('a.company_id', 'COM003');
		if ($dept != '') $this->db->where('a.id_dept', $dept);
		$this->db->order_by('a.cost_center', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		$aCombo[]	= '';
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id']]	= $vals['cost_center'];
			}
		}
		return $aCombo;
	}

	function GetCostCenter($dept)
	{
		$this->db->select('a.id, a.cost_center');
		$this->db->from('department_center a');
		//		$this->db->where('a.company_id','COM003');
		$this->db->where('a.id_dept', $dept);
		$this->db->order_by('a.cost_center', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetWarehouseStok($key = '')
	{
		$aCombo		= array();
		$this->db->select('a.id, UPPER(a.nm_gudang) AS nm_dept');
		$this->db->from('warehouse a');
		$this->db->where('a.desc', 'stok');
		if ($key != '') $this->db->where('a.id', $key);
		//		$this->db->where('a.company_id','COM003');
		$this->db->order_by('a.urut', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($key == '') $aCombo[]	= '';
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id']]	= $vals['nm_dept'];
			}
		}
		return $aCombo;
	}

	function get_name($table, $field, $field_whare, $value)
	{
		$query	= $this->db->query("SELECT $field FROM $table WHERE $field_whare='" . $value . "' LIMIT 1")->result();
		$data 	= (!empty($query)) ? $query[0]->$field : '-';
		return $data;
	}

	function GetCategory()
	{
		$category = array('' => '', 'EXPENSE' => 'EXPENSE', 'RUTIN' => 'RUTIN', 'NON RUTIN' => 'NON RUTIN', 'PEMBAYARAN PERIODIK' => 'PEMBAYARAN PERIODIK', 'UMUM' => 'UMUM');
		return $category;
	}
	function GetJenis()
	{
		$jenis = array('' => '', 'VARIABLE' => 'VARIABLE', 'FIX COST BULANAN' => 'FIX COST BULANAN', 'FIX COST TAHUNAN' => 'FIX COST TAHUNAN');
		return $jenis;
	}

	function GetBudgetComboCategory($kategori, $tahun, $divisi)
	{
		$aMenu		= array();
		$this->db->select('a.coa, b.no_perkiraan , b.nama as nama_perkiraan, c.nm_dept');
		$this->db->from('ms_budget a');
		$this->db->join(DBACC . '.coa_master b', 'a.coa=b.no_perkiraan');
		$this->db->join('department c', 'a.divisi=c.id', 'left');
		$this->db->where("a.kategori", $kategori);
		$this->db->where("a.tahun", $tahun);
		$this->db->where("a.divisi", $divisi);
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
	function GetInfoUser($user_id)
	{
		$this->db->select('a.*,b.username');
		$this->db->from('employee a');
		$this->db->join('users b', 'a.id=b.employee_id');
		$this->db->where('b.id_user', $user_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return false;
		}
	}
	function GetPettyCashCombo()
	{
		$combos = array();
		$this->db->select('a.nama, a.pengelola');
		$this->db->from('ms_petty_cash a');
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$combos[$vals['nama']]	= $vals['nama'] . ' - ' . $vals['pengelola'];
			}
		}
		return $combos;
	}

	function GetUsersCombo()
	{
		$combos = array();
		$this->db->select('a.username,a.nama_karyawan');
		$this->db->from('user_emp a');
		$this->db->order_by('a.nama_karyawan', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$combos[$vals['username']]	= $vals['nama_karyawan'];
			}
		}
		return $combos;
	}

	function GetPettyCashComboCoa($tipe)
	{
		$datacoa = $this->db->query("select coa from ms_petty_cash where nama = '" . $tipe . "'")->row();
		$coabudget = str_ireplace(";", "','", $datacoa->coa);
		$combos = array();
		$results = $this->db->query("select * from " . DBACC . ".coa_master where no_perkiraan in ('" . $coabudget . "')")->result();
		if ($results) {
			foreach ($results as $key) {
				$combos[$key->no_perkiraan]	= $key->no_perkiraan . ' - ' . $key->nama;
			}
		}
		return $combos;
	}

	function GetUserCombo()
	{
		$aCombo		= array();
		$this->db->select('a.id_user, a.nm_lengkap');
		$this->db->from('users a');
		$this->db->order_by('a.nm_lengkap', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aCombo[$vals['id_user']]	= $vals['nm_lengkap'];
			}
		}
		return $aCombo;
	}

	public function generate_id_costbook()
	{
		$generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM tr_cost_book WHERE id LIKE '%CBO-" . date('Y-m-') . "%'")->row();
		$kodeBarang = $generate_id->max_id;
		$urutan = (int) substr($kodeBarang, 13, 5);
		$urutan++;
		$tahun = date('Y-m-');
		$huruf = "CBO-";
		$kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

		return $kodecollect;
	}
}
