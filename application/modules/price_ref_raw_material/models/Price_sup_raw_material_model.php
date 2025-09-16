<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Price_ref_raw_material_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'new_inventory_4';
        $this->key        = 'id';
        $this->code       = 'code_lv4';
    }
	
    function generate_id() {
        $kode 		    = 'M4'.date('y');
        $Query			= "SELECT MAX(".$this->code.") as maxP FROM ".$this->table_name." WHERE ".$this->code." LIKE '".$kode."%' ";
        $resultIPP		= $this->db->query($Query)->result_array();
        $angkaUrut2		= $resultIPP[0]['maxP'];
        $urutan2		= (int)substr($angkaUrut2, 4, 6);
        $urutan2++;
        $urut2			= sprintf('%06s',$urutan2);
        $kode_id	    = $kode.$urut2;
        return $kode_id;
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
       return $this->db->get_where($this->table_name,array($code => $id))->row_array();
    }

}
