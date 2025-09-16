<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author harboens
 * @copyright Copyright (c) 2020, harboens
 *
 * This is model class for table "Acc Model"
 */

class Acc_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	function GetcoaComboCategory($tipe,$coa='',$name=''){
		$aMenu		= array();
		$this->db->select('a.coa, a.nama, b.nama as nama_perkiraan');
		$this->db->from('ms_coa_category a');
		$this->db->join(DBACC.'.coa_master b','a.coa=b.no_perkiraan');
		$this->db->where('a.tipe',$tipe);
		if($coa!=''){
			$this->db->where('a.coa',$coa);
		}
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				if($name!=''){
					$aMenu[$vals['coa']]	= $vals['coa'].' - '.$vals['nama_perkiraan'];
				}else{
					$aMenu[$vals['coa']]	= $vals['coa'].' - '.$vals['nama_perkiraan'];
//					$aMenu[$vals['coa']]	= $vals['coa'].' - '.$vals['nama'];
				}
			}
		}
		return $aMenu;
	}

	function GetcoaCombo($level='5'){
		$aMenu		= array();
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC.'.coa_master a');
		$this->db->where('a.level',$level);
		$this->db->order_by('a.no_perkiraan', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'].' - '.$vals['nama'];
			}
		}
		return $aMenu;
	}
	
	public function get_noperkiraan($level='5')
	{

		$kode_cabang	= $this->session->userdata('kode_cabang');

		$query 	= "SELECT * FROM ".DBACC.".coa_master WHERE level='5' 
            		ORDER BY no_perkiraan ASC";
		$query	= $this->db->query($query);
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return 0;
		}
	}

	function GetDepartemenCombo($id_divisi=''){
		$aMenu		= array();
		$this->db->select('a.id_divisi, a.nm_divisi');
		$this->db->from('divisi a');
		if($id_divisi!=''){
			$this->db->where('a.id_divisi',$id_divisi);
		}
		$this->db->order_by('a.nm_divisi', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id_divisi']]	= $vals['nm_divisi'];
			}
		}
		return $aMenu;
	}
	
	function GetDivisiCombo($id_divisi=''){
		$aMenu		= array();
		$this->db->select('a.id_divisi, a.nm_divisi');
		$this->db->from('divisi a');
		if($id_divisi!=''){
			$this->db->where('a.id_divisi',$id_divisi);
		}
		$this->db->order_by('a.nm_divisi', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id_divisi']]	= $vals['nm_divisi'];
			}
		}
		return $aMenu;
	}
	
	function GetJurnalCombo(){
		$aMenu		= array();
		$this->db->select('a.kode_master_jurnal, a.nama_jurnal');
		$this->db->from(DBACC.'.master_oto_jurnal_header a');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['kode_master_jurnal']]	= $vals['kode_master_jurnal'].' - '.$vals['nama_jurnal'];
			}
		}
		return $aMenu;
	}
	
	function GetTemplateJurnal($kodejurnal){
		$this->db->select('a.*');
		$this->db->from(DBACC.'.master_oto_jurnal_detail a');
		$this->db->where('kode_master_jurnal',$kodejurnal);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return 0;
		}
	}
    
	public function GetData($tabel,$field,$param,$value){
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
	}
	
	public function vendor_combo(){
		$aMenu		= array();
		$this->db->select('a.id_supplier as id_vendor, a.nm_supplier_office as nama');
		$this->db->from('master_supplier a');
		$this->db->order_by('a.nm_supplier_office ASC');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id_vendor']]	= $vals['id_vendor'].' - '.$vals['nama'];
			}
		}
		return $aMenu;
	}

	public function combo_bank($where=''){
		$aMenu		= array();
		$this->db->distinct();
		if($where!='') $this->db->where($where);
		$this->db->order_by('bank_nama', 'asc');
		$query = $this->db->get('ms_bank');
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']]	= $vals['bank_nama'].' - '.$vals['bank_ac'].' - '.$vals['bank_cabang'];
			}
		}
		return $aMenu;
	}	

	public function combo_pph_penjualan($level=5){
		$aMenu		= array();
		$this->db->select("a.no_perkiraan, a.nama");
		$this->db->from(DBACC.".coa_master a");
		$this->db->where("a.level",$level);
		$this->db->where("a.no_perkiraan in (select info from ms_generate where tipe='pph_penjualan')",NULL,FALSE);
		$this->db->order_by("a.no_perkiraan", "asc");
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'].' - '.$vals['nama'];
			}
		}
		return $aMenu;
	}

	public function combo_pph_pembelian($level=5){
		$aMenu		= array();
		$this->db->select("a.no_perkiraan, a.nama");
		$this->db->from(DBACC.".coa_master a");
		$this->db->where("a.level",$level);
		$this->db->where("a.no_perkiraan in (select info from ms_generate where tipe='pph_pembelian')",NULL,FALSE);
		$this->db->order_by("a.no_perkiraan", "asc");
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'].' - '.$vals['nama'];
			}
		}
		return $aMenu;
	}

	function GetcoaComboNonStock($level='5',$id_divisi=''){
		$aMenu		= array();
		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC.'.coa_master a');
		$this->db->where('a.level',$level);
		if($id_divisi!=''){
			$this->db->where("a.no_perkiraan in (select distinct(coa) coa from ms_coa_budget where (divisi='".$id_divisi."' or divisi='' or divisi IS NULL or divisi='0'))",NULL,FALSE);
		}else{
			$this->db->where("a.no_perkiraan in (select distinct(coa) coa from ms_coa_budget)",NULL,FALSE);
		}
		$this->db->order_by('a.no_perkiraan', 'asc');
//		$str = $this->db->last_query();
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['no_perkiraan']]	= $vals['no_perkiraan'].' - '.$vals['nama'];
			}
		}
		return $aMenu;
	}

	function GetInfoUser($id_user){
		$this->db->select('a.*');
		$this->db->from('users a');
		$this->db->where('id_user',$id_user);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetInfoCustomer($id_klien){
		$this->db->select('a.*');
		$this->db->from('ms_customer a');
		$this->db->where('id_klien',$id_klien);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetInfoVendor($id_vendor){
		$this->db->select('a.*');
		$this->db->from('ms_vendor a');
		$this->db->where('id_vendor',$id_vendor);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}
	
	// function tipe_bayar(){
		// $aMenu		= array('0'=>'Cicilan','1'=>'DP','2'=>'Pelunasan');
		// return $aMenu;
	// }
	
	function tipe_bayar(){
		$aMenu		= array('0'=>'Hutang Usaha','1'=>'Uang Muka');
		return $aMenu;
	}
	
	function GetTemplate(){
		$aMenu		= array();
		$this->db->select('a.kode_master_jurnal, a.keterangan_header');
		$this->db->from(DBACC.'.master_oto_jurnal_header a');
		$this->db->order_by('a.kode_master_jurnal', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['kode_master_jurnal']]	= $vals['kode_master_jurnal'].' - '.$vals['keterangan_header'];
			}
		}
		return $aMenu;
	}

	function GetCostcenterCombo($id_costcenter=''){
		$aMenu		= array();
		$this->db->select('a.id, a.nm_costcenter');
		$this->db->from('costcenter a');
		if($id_costcenter!=''){
			$this->db->where('a.id',$id_costcenter);
		}
		$this->db->order_by('a.nm_costcenter', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']]	= $vals['nm_costcenter'];
			}
		}
		return $aMenu;
	}
}
