<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Department_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'ms_department';
        $this->key        = 'id';
    }

 	public function get_data($array_where){
		if(!empty($array_where)){
			$query = $this->db->get_where($this->table_name, $array_where);
		}
        else{
			$query = $this->db->get($this->table_name);
		}
		
		return $query->result();
	}
	
    function getById($id)
    {
       return $this->db->get_where($this->table_name,array($this->key => $id))->row_array();
    }

}
