<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Inquiry_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'tr_inquiry_hd';
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

    	
	 

    

   function generate_no_inquiry(){


		$today=date("ymd");
		$year=date("y");
		$month=date("m");
		$day=date("d");

        $cek = date('y').$kode_bln;
        $query = "SELECT MAX(RIGHT(no_inquiry,4)) as max_id from tr_inquiry_hd WHERE no_inquiry LIKE '%$today%'";
        $q = $this->db->query($query);
		$r = $q->row();
        $query_cek = $q->num_rows();
		$kode2 = $r->max_id;
		$kd_noreg = "";
		 
        if ($query_cek == 0) {
          $kd_noreg = 1;
          $reg = sprintf("%02d%02d%02d%04s", $year,$month,$day,$kode_noreg);
		  
        }else {
         		 	  
        // jk sudah ada maka
			$kd_new = $kode2+1; // kode sebelumnya ditambah 1.
			$reg = sprintf("%02d%02d%02d%04s", $year,$month,$day,$kd_new);
			
        }
		
		$tr ="IQ$reg";
		
         
          // print_r($tr);
		  // exit();

      return $tr;
    }


     
	
	
 	public function get_data($table,$where_field='',$where_value=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->get_where($table, array($where_field=>$where_value));
		}else{
			$query = $this->db->get($table);
		}
		
		return $query->result();
	}
	
	public function getArray($table,$WHERE=array(),$keyArr='',$valArr=''){
		if($WHERE){
			$query = $this->db->get_where($table, $WHERE);
		}else{
			$query = $this->db->get($table);
		}
		$dataArr	= $query->result_array();
		
		if(!empty($keyArr)){
			$Arr_Data	= array();
			foreach($dataArr as $key=>$val){
				$nilai_id					= $val[$keyArr];
				if(!empty($valArr)){
					$nilai_val				= $val[$valArr];
					$Arr_Data[$nilai_id]	= $nilai_val;
				}else{
					$Arr_Data[$nilai_id]	= $val;
				}
				
			}
			
			return $Arr_Data;
		}else{
			return $dataArr;
		}
		
	}
	
	public function get_data_form($noinquiry,$material){
		
		$this->db->select('a.*');
		$this->db->from('tr_inquiry_dt_form a');
		$this->db->where('a.no_inquiry',$noinquiry);
		$this->db->where('a.id_material',$material);
		$query = $this->db->get();
		
		if($query->num_rows() != 0)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

    public function cek_data($kunci,$tabel) {
        $this->db->where($kunci);
        $query=$this->db->get($tabel);
        return $query->row();
    }

    public function get_data1($kunci,$tabel) {
        $this->db->where($kunci);
        $query=$this->db->get($tabel);
        return $query->result();
    }

    function pilih_driver($kdcab){
        $query="SELECT
                karyawan.id_karyawan,
                karyawan.nama_karyawan
                FROM karyawan where divisi='12' and kdcab='$kdcab'";
        return $this->db->query($query);
    }

    function pilih_kendaraan($kdcab){
        $query="SELECT
                kendaraan.id_kendaraan,
                kendaraan.nm_kendaraan
                FROM kendaraan WHERE kdcab='$kdcab'";
        return $this->db->query($query);
    }

    function get_cabang($kdcab){
        $query="SELECT
                cabang.kdcab,
                cabang.namacabang
                FROM cabang where kdcab='".$kdcab."' ";
        return $this->db->query($query)->row();
    }

	function get_customer($id){

        $this->db->where('id_customer',$id);
        $query=$this->db->get(customer);
        return $query->result();

        //LEFT JOIN barang_master ON `barang_stock`.`id_barang` = `barang_master`.`id_barang`
    }

		
	
    public function tampil_detail($id){
        $query="SELECT
                tr_inquiry_dt.id_material,
                tr_inquiry_dt.nama_material              
                FROM
                tr_inquiry_dt
				where no_inquiry='$id'";
       
	   return $this->db->query($query);
    }
	
	
	
	public function get_data_inquiry()
	{	
	    $this->db->select('a.*, b.name_customer, c.nama_karyawan');
		$this->db->from('tr_inquiry_hd a');
		$this->db->join('master_customer  b', 'b.id_customer = a.id_customer');
		$this->db->join('ms_karyawan  c', 'c.id_karyawan = a.id_sales');
		$query = $this->db->get();
	
		
	if($query->num_rows() != 0)
    {
        return $query->result();
    }
    else
    {
        return false;
    }
	}
	
	
	public function viewInquiry($noinquiry)
	{	
	    $this->db->select('a.*, b.name_customer, c.nama_karyawan');
		$this->db->from('tr_inquiry_hd a');
		$this->db->join('master_customer  b', 'b.id_customer = a.id_customer');
		$this->db->join('ms_karyawan  c', 'c.id_karyawan = a.id_sales');
		$this->db->where('a.no_inquiry',$noinquiry);
		$query = $this->db->get();
	
		
	if($query->num_rows() != 0)
    {
        return $query->result();
    }
    else
    {
        return false;
    }
	}
	
	function pilih_combobox($table,$field,$where)
    {
        $query="SELECT * 
                FROM
                $table
				where $field ='$where'";
        return $this->db->query($query);
    }
	
	public function getUpdate($table,$data,$where_field='',$where_value=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->where(array($where_field=>$where_value));
		}
		$result	= $this->db->update($table,$data);
		return $result;
	}

    function hapus ($where,$table){
		
	$this->db->where($where);
	$this->db->delete($table);
    }


}
