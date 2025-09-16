<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Efaktur_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'faktur_e_logs';

    /**
     * @var string Field name to use for the created time column in the DB table
     * if $set_created is enabled.
     */
   

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
