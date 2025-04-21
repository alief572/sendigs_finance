<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2019, Harboens
 *
 * This is model class for table "Budget"
 */

class Po_aset_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'tr_pr_aset';
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
    /**
     * @var bool Enable/Disable soft deletes.
     * If false, the delete() method will perform a delete of that row.
     * If true, the value in $deleted_field will be set to 1.
     */
    protected $soft_deletes = false;
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

	function GetPrAset($status=''){
		$this->db->select('a.*');
		$this->db->from('tr_pr_aset a');
		if($status!=''){
			$this->db->where_in('a.status', $status);
		}
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function aset_combo($tahun='',$bulan='',$where=''){
		$aMenu = array();
		$this->db->select('a.*');
		$this->db->from('ms_coa_aset a');
		$this->db->order_by('a.nama_aset', 'desc');
		if($tahun!='') $this->db->where('tahun', $tahun);
		if($bulan!='') $this->db->where('bulan', $bulan);
		if($where!=''){
			$this->db->where($where);
		}
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']]	= $vals['coa'].' | '.$vals['nama_aset'];
			}
		}
		return $aMenu;
	}

	function GetDataAset(){
		$this->db->select('a.*');
		$this->db->from('ms_coa_aset a');
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	
	
	public function Update_budget($id,$nilai,$nilai_pr=0){
		$this->db->select('a.*');
		$this->db->from('ms_coa_aset a');
		$this->db->where('id', $id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			$data=$query->row();
			$budget=$data->budget;
			$terpakai=$data->terpakai;
			$sisa=$data->sisa;
			$idbudget=$data->id;
			$upd_terpakai=($terpakai+$nilai-$nilai_pr);
			$upd_sisa=($budget-$upd_terpakai);
			$this->db->query("update ms_coa_aset set terpakai=".$upd_terpakai.", sisa=".$upd_sisa." where id=".$idbudget."");
			return true;
		} else {
			return false;
		}
	}
	
// kasbon tidak dipakai
	function GetKasbonAset($status=false,$date=false){
		$this->db->select('a.*');
		$this->db->from('tr_pr_aset_kasbon a');
		if($status!==false){
			$this->db->where_in('a.status', $status);
		}
		if($date!==false){
			$this->db->where('tgl_kasbon >=', $date[0]);
			$this->db->where('tgl_kasbon <=', $date[1]);
		}
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function EditKasbon($id){
		$this->db->select('a.*');
		$this->db->from('tr_pr_aset_kasbon a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetPoAset($status=false){
		$this->db->select('a.*, b.nama');
		$this->db->from('tr_po_aset a');
		$this->db->join('ms_vendor b','a.vendor_id=b.id_vendor','left');
		$this->db->order_by('a.id', 'desc');
		if($status!==false){
			$this->db->where_in('a.status', $status);
		}
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function EditPo($id){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_po_aset a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function EditPoPayment($id){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_po_aset_request_payment a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetPoPaymentAsetList($where=''){
		$this->db->select('a.*, b.nama');
		$this->db->from('tr_po_aset_request_payment a');
		$this->db->join('ms_vendor b','a.vendor_id=b.id_vendor','left');
		$this->db->order_by('a.id', 'desc');
		if($where!=''){
			$this->db->where($where);
		}
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetPoPaymentAset($status=false,$date=false,$where=''){
		$this->db->select('a.*, b.nama');
		$this->db->from('tr_po_aset_request_payment a');
		$this->db->join('ms_vendor b','a.vendor_id=b.id_vendor','left');
		$this->db->order_by('a.id', 'desc');
		if($status!==false){
			$this->db->where_in('a.status', $status);
		}
		if($where!=''){
			$this->db->where($where);
		}
		if($date!==false){
			$this->db->where('tgl_periksa >=', $date[0]);
			$this->db->where('tgl_periksa <=', $date[1]);
		}
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function InfoPo($no_po){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_po_aset a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		$this->db->where('a.no_po', $no_po);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetPpAsetAp($where=''){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_pp_aset a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		if($where!=''){
			$this->db->where($where);
		}
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetPpAset($status=false,$date=false,$ap_cek='0'){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_pp_aset a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		if($status!==false){
			$this->db->where_in('a.status', $status);
		}
		if($date!==false){
			$this->db->where('tgl_pp >=', $date[0]);
			$this->db->where('tgl_pp <=', $date[1]);
		}
		$this->db->where('a.ap_cek', $ap_cek);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function EditPp($id){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_pp_aset a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function InfoPp($nopr){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_pr_aset a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		$this->db->where('a.no_pr', $nopr);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function user_approved($tipe,$iddata){
		$this->db->select('a.*');
		$this->db->from('users a');
		if($tipe=="PO"){
			$this->db->join('tr_aset_approval b', 'a.id_user=b.created_by');
		}
		$this->db->where('b.tipe', $tipe);
		$this->db->where('b.no_dokumen', $iddata);
		$this->db->order_by('b.id ASC');
		$query = $this->db->get();
		if($query->num_rows() != 0)		{
			return $query->result();
		}		else		{
			return false;
		}
	}

	public function GetDataNoCoa($id){
		$this->db->select('a.*');
		$this->db->from('ms_coa_aset a');
		$this->db->where('a.id',$id);
		$query = $this->db->get();
		if($query->num_rows() != 0)
		{
			return $query->result();
		}		else		{
			return false;
		}
	}

	public function GetDataNoPp($nopp){
		$this->db->select('a.*');
		$this->db->from('tr_pp_aset a');
		$this->db->where('a.no_pp',$nopp);
		$query = $this->db->get();
		if($query->num_rows() != 0)
		{
			return $query->result();
		}		else		{
			return false;
		}
	}

    public function GetDataNoPo($nopo){
		$this->db->select('a.*');
		$this->db->from('tr_po_aset a');
		$this->db->where('a.no_po',$nopo);
		$query = $this->db->get();
		if($query->num_rows() != 0)
		{
			return $query->result();
		}		else		{
			return false;
		}
	}

	function GetPoPaymentAsetFinance($where){
		$this->db->select('a.*, b.nama');
		$this->db->from('tr_po_aset_payment a');
		$this->db->join('ms_vendor b','a.vendor_id=b.id_vendor','left');
		$this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function EditPoPaymentAsetFinance($id){
		$this->db->select('a.*, b.nama');
		$this->db->from('tr_po_aset_payment a');
		$this->db->join('ms_vendor b','a.vendor_id=b.id_vendor','left');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetPpAsetPayment($where){
		$this->db->select('a.*');
		$this->db->from('tr_pp_aset_payment a');
		$this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function EditPpPayment($id){
		$this->db->select('a.*, b.nm_lengkap username');
		$this->db->from('tr_pp_aset_payment a');
		$this->db->join('users b', 'a.created_by=b.id_user','left');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}
	
	function GetPpAsetJurnal(){
		$this->db->select('a.*');
		$this->db->from('tr_pp_aset a');
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	
	function GetPoAsetPeyusutan($id=''){
		$this->db->select('a.*, b.nama');
		$this->db->from('tr_po_aset a');
		$this->db->join('ms_vendor b','a.vendor_id=b.id_vendor','left');
		$this->db->order_by('a.id', 'desc');
		$this->db->where('a.terima_barang', 1);
		$this->db->where('a.penyusutan', 0);
		if($id!=''){
			$this->db->where('a.id', $id);
		}
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	
	function divisi_combo($id=''){
		$aMenu = array();
		$this->db->select('a.*');
		$this->db->from('divisi a');
		$this->db->order_by('a.id', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']]	= $vals['nm_divisi'];
			}
		}
		return $aMenu;
	}
	
	public function Update_budget_aset($id,$nilai){
		$this->db->query("update ms_coa_aset set budgetpr=".$nilai." where id=".$id."");
		return true;
		
	}
	
	function GetPrAsetSelection(){
		$this->db->select('a.*');
		$this->db->from('tr_pr_aset a');
		$this->db->where('a.jenis_pembelian',NULL);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
}
