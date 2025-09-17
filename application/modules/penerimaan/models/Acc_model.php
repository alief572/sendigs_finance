<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author harboens
 * @copyright Copyright (c) 2020, harboens
 *
 * This is model class for table "Acc Model"
 */

class Acc_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	function GetCoaComboCategory($tipe, $coa = '', $name = '')
	{
		$aMenu		= array();
		$this->db->select('a.coa, a.nama, b.nama as nama_perkiraan');
		$this->db->from('ms_coa_category a');
		$this->db->join(DBACC . '.coa_master b', 'a.coa=b.no_perkiraan');
		$this->db->where('a.tipe', $tipe);
		if ($coa != '') {
			$this->db->where('a.coa', $coa);
		}
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				if ($name != '') {
					$aMenu[$vals['coa']]	= $vals['coa'] . ' - ' . $vals['nama_perkiraan'];
				} else {
					$aMenu[$vals['coa']]	= $vals['coa'] . ' - ' . $vals['nama_perkiraan'];
					//					$aMenu[$vals['coa']]	= $vals['coa'].' - '.$vals['nama'];
				}
			}
		}
		return $aMenu;
	}

	function GetCoaCombo($level = '5')
	{
		$aMenu		= array();
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

	public function get_noperkiraan($level = '5')
	{

		$kode_cabang	= $this->session->userdata('kode_cabang');

		$query 	= "SELECT * FROM " . DBACC . ".coa_master WHERE level='5' 
            		ORDER BY no_perkiraan ASC";
		$query	= $this->db->query($query);
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return 0;
		}
	}

	function GetDivisiCombo($id_divisi = '')
	{
		$aMenu		= array();
		$this->db->select('a.id_divisi, a.nm_divisi');
		$this->db->from('divisi a');
		if ($id_divisi != '') {
			$this->db->where('a.id_divisi', $id_divisi);
		}
		$this->db->order_by('a.nm_divisi', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id_divisi']]	= $vals['nm_divisi'];
			}
		}
		return $aMenu;
	}

	function GetJurnalCombo()
	{
		$aMenu		= array();
		$this->db->select('a.kode_master_jurnal, a.nama_jurnal');
		$this->db->from(DBACC . '.master_oto_jurnal_header a');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['kode_master_jurnal']]	= $vals['kode_master_jurnal'] . ' - ' . $vals['nama_jurnal'];
			}
		}
		return $aMenu;
	}

	function GetTemplateJurnal($kodejurnal)
	{
		$this->db->select('a.*');
		$this->db->from(DBACC . '.master_oto_jurnal_detail a');
		$this->db->where('kode_master_jurnal', $kodejurnal);
		$this->db->order_by('a.posisi', 'asc');
		$this->db->order_by('a.parameter_no', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return 0;
		}
	}

	public function GetData($tabel, $field, $param, $value)
	{
		$query = $this->db->query("SELECT " . $field . " FROM " . $tabel . " WHERE $param='" . $value . "'")->result();
		/*
		$this->db->select($field);
		$this->db->from($tabel); 
		$this->db->where($param,$value);
		$query = $this->db->get(); 
		
		if($query->num_rows() != 0)
		{
			return $query->result();
		}		else		{
			return false;
		}
*/
		if ($query) {
			return $query;
		} else {
			return false;
		}
	}

	public function vendor_combo()
	{
		$aMenu		= array();
		$this->db->select('a.id_vendor, a.nama');
		$this->db->from('ms_vendor a');
		$this->db->order_by('a.nama ASC');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id_vendor']]	= $vals['id_vendor'] . ' - ' . $vals['nama'];
			}
		}
		return $aMenu;
	}

	public function combo_bank($where = '')
	{
		$aMenu		= array();
		$this->db->distinct();
		if ($where != '') $this->db->where($where);
		$this->db->order_by('bank_nama', 'asc');
		$query = $this->db->get('ms_bank');
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['id']]	= $vals['bank_nama'] . ' - ' . $vals['bank_ac'] . ' - ' . $vals['bank_cabang'];
			}
		}
		return $aMenu;
	}

	public function combo_pph_penjualan($level = 5)
	{
		$aMenu		= array();
		$this->db->select("a.no_perkiraan, a.nama");
		$this->db->from(DBACC . ".coa_master a");
		$this->db->where("a.level", $level);
		$this->db->where("a.no_perkiraan in (select info from ms_generate where tipe='pph_penjualan')", NULL, FALSE);
		$this->db->order_by("a.no_perkiraan", "asc");
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'] . ' - ' . $vals['nama'];
			}
		}
		return $aMenu;
	}

	public function combo_pph_pembelian($level = 5)
	{
		$aMenu		= array();
		$this->db->select("a.no_perkiraan, a.nama");
		$this->db->from(DBACC . ".coa_master a");
		$this->db->where("a.level", $level);
		$this->db->where("a.no_perkiraan in (select info from ms_generate where tipe='pph_pembelian')", NULL, FALSE);
		$this->db->order_by("a.no_perkiraan", "asc");
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'] . ' - ' . $vals['nama'];
			}
		}
		return $aMenu;
	}

	function GetCoaComboNonStock($level = '5', $id_divisi = '')
	{
		$aMenu		= array();
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->where('a.level', $level);
		if ($id_divisi != '') {
			$this->db->where("a.no_perkiraan in (select distinct(coa) coa from ms_coa_budget where (divisi='" . $id_divisi . "' or divisi='' or divisi IS NULL or divisi='0'))", NULL, FALSE);
		} else {
			$this->db->where("a.no_perkiraan in (select distinct(coa) coa from ms_coa_budget)", NULL, FALSE);
		}
		$this->db->order_by('a.no_perkiraan', 'asc');
		//		$str = $this->db->last_query();
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'] . ' - ' . $vals['nama'];
			}
		}
		return $aMenu;
	}

	function GetInfoUser($id_user)
	{
		$this->db->select('a.*');
		$this->db->from('users a');
		$this->db->where('id_user', $id_user);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetInfoCustomer($id_klien)
	{
		$this->db->select('a.*');
		$this->db->from('ms_customer a');
		$this->db->where('id_klien', $id_klien);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetInfoVendor($id_vendor)
	{
		$this->db->select('a.*');
		$this->db->from('ms_vendor a');
		$this->db->where('id_vendor', $id_vendor);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// function tipe_bayar(){
	// $aMenu		= array('0'=>'Cicilan','1'=>'DP','2'=>'Pelunasan');
	// return $aMenu;
	// }

	function tipe_bayar()
	{
		$aMenu		= array('0' => 'Hutang Usaha', '1' => 'Uang Muka');
		return $aMenu;
	}

	function GetTemplate()
	{
		$aMenu		= array();
		$this->db->select('a.kode_master_jurnal, a.keterangan_header');
		$this->db->from(DBACC . '.master_oto_jurnal_header a');
		$this->db->order_by('a.kode_master_jurnal', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if ($results) {
			foreach ($results as $key => $vals) {
				$aMenu[$vals['kode_master_jurnal']]	= $vals['kode_master_jurnal'] . ' - ' . $vals['keterangan_header'];
			}
		}
		return $aMenu;
	}
	function generate_jurnal_jv($kode, $tanggal, $bank = '')
	{
		$template_jurnal = $this->db->query("SELECT * from ms_generate where tipe='ms_generate_jurnal' and info='" . $kode . "'")->row();
		$tahun = substr($tanggal, 0, 2);
		$bulan = substr($tanggal, 3, 2);
		$angka = 1;
		$no_urut = $this->db->query("SELECT * from ms_generate_jurnal where tipe='" . $kode . "' and tahun='" . $tahun . "' and bulan='" . $bulan . "'")->row();
		if (!empty($no_urut)) {
			$angka = ($no_urut->angka + 1);
			$this->db->query("update ms_generate_jurnal set angka=(angka+1) where id='" . $no_urut->id . "'");
		} else {
			$this->db->query("insert into ms_generate_jurnal (tipe,tahun,bulan,angka) values ('" . $kode . "','" . $tahun . "','" . $bulan . "','1')");
		}
		$format = $template_jurnal->kode_2;
		$format = str_replace('info', $template_jurnal->info, $format);
		$format = str_replace('th', $tahun, $format);
		$format = str_replace('bl', $bulan, $format);
		$format = str_replace('xxx', sprintf('%0' . $template_jurnal->kode_1 . 'd', $angka), $format);
		if ($bank != '') {
			$kode_bank = $this->db->query("SELECT * from ms_generate where tipe='kode_bank' and info='" . $bank . "'")->row();
			$format = str_replace('bank', $kode_bank->kode_1, $format);
		}
		return $format;
	}
}
