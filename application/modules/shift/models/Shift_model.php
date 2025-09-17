<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Shift_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'customer';
    protected $key        = 'id_customer';

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
    protected $soft_deletes = true;

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
	function provinsi($id_negara)
    {
        $this->db->where('id_negara', $id_negara);
        $this->db->order_by('id_prov', 'ASC');
        return $this->db->from('provinsi')
            ->get()
            ->result();
    }

	function kota($id_prov)
    {
        $this->db->where('id_prov', $id_prov);
        $this->db->order_by('id_kota', 'ASC');
        return $this->db->from('kota')
            ->get()
            ->result();
    }
	
    function generate_id($kode='') {
      $query = $this->db->query("SELECT MAX(id_Shift) as max_id FROM ms_shift");
      $row = $query->row_array();
      $thn = date('y');
      $max_id = $row['max_id'];
      $max_id1 =(int) substr($max_id,3,5);
      $counter = $max_id1 +1;
      $idcust = "S".$thn.str_pad($counter, 5, "0", STR_PAD_LEFT);
      return $idcust;
	}
	
	function level_2($inventory_1)
    {
        $this->db->where('id_inventory1', $inventory_1);
        $this->db->order_by('id_inventory2', 'ASC');
        return $this->db->from('inven_lvl2')
            ->get()
            ->result();
    }
    function level_3($id_inventory2)
    {
        $this->db->where('id_inventory2', $id_inventory2);
        $this->db->order_by('id_inventory3', 'ASC');
        return $this->db->from('inven_lvl3')
            ->get()
            ->result();
    }

 	public function get_data($table,$where_field='',$where_value=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->get_where($table, array($where_field=>$where_value));
		}else{
			$query = $this->db->get($table);
		}
		
		return $query->result();
	}
	
    function getById($id)
    {
       return $this->db->get_where('ms_shift',array('id_shift' => $id))->row_array();
    }

    function get_prov($id){
        $query = $this->db->query("SELECT provinsi FROM customer WHERE id_customer='$id'");
        $row = $query->row_array();
        $provinsi     = $row['provinsi'];
        return $provinsi;
    }
	public function get_data_byid($id){
		$this->db->select('a.*, b.name_type_shift as name_type_shift, c.day_id as day_id');
		$this->db->from('ms_shift a');
		$this->db->join('ms_type_shift b','b.id_type_shift = a.type_shift');
		$this->db->join('ms_hari c','c.id_hari = a.id_day');
		$this->db->where('a.id_shift',$id);
		$this->db->order_by('a.id_day');
		$query = $this->db->get();		
		return $query->result();
	}

public function get_data_idx(){
		$this->db->select('a.*, b.name_type_shift as name_type_shift, c.day_id as day_id');
		$this->db->from('ms_shift a');
		$this->db->join('ms_type_shift b','b.id_type_shift = a.type_shift');
		$this->db->join('ms_hari c','c.id_hari = a.id_day');
		$this->db->where('a.deleted','0');
		$this->db->order_by('a.id_day');
		$query = $this->db->get();		
		return $query->result();
	}

}
