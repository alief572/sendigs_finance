<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Faktur_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'faktur_header';
    protected $key        = 'kode_req';

    /**
     * @var string Field name to use for the created time column in the DB table
     * if $set_created is enabled.
     */
    protected $created_field = 'create_on';

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

    function generate_kode(){
		$Urut		= 1;
        $query = "SELECT SUBSTRING_INDEX(kode_req,'-',-1) AS kode_urut 
                  FROM 
                  faktur_header ORDER by kode_req DESC LIMIT 1"; 
        $q = $this->db->query($query);		
        $num_rows = $q->num_rows();
		if($num_rows >  0){
			$det_data		= $q->result_array();
			$Urut			= (int) $det_data[0]['kode_urut'] + 1;
		}
        
        $next_kode = str_pad($Urut, 5, "0", STR_PAD_LEFT);

        $Kode_Req		= 'FAK-'.$next_kode;
        return $Kode_Req;
    }
	
	
	
	function getCount($table,$where_field=array()){
		if($where_field){
			$query = $this->db->get_where($table, $where_field);
		}else{
			$query = $this->db->get($table);
		}
		return $query->num_rows();
	}
	
	function getArray($table,$WHERE=array(),$keyArr='',$valArr=''){
		if($WHERE){
			$query = $this->db->get_where($table, $WHERE);
		}else{
			$query = $this->db->get($table);
		}
		$dataArr	= $query->result_array();
		
		if(!empty($keyArr) && !empty($valArr)){
			$Arr_Data	= array();
			foreach($dataArr as $key=>$val){
				$nilai_id				= $val[$keyArr];
				$nilai_val				= $val[$valArr];
				$Arr_Data[$nilai_id]	= $nilai_val;
			}
			
			return $Arr_Data;
		}else{
			return $dataArr;
		}
		
	}
}
