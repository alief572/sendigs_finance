<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Inventory_5_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'ms_material';
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
	
	
    function generate_id($kode='') {
      $query = $this->db->query("SELECT MAX(id_material) as max_id FROM ms_material");
      $row = $query->row_array();
      $thn = date('y');
      $max_id = $row['max_id'];
      $max_id1 =(int) substr($max_id,3,5);
      $counter = $max_id1 +1;
      $idcust = "M".$thn.str_pad($counter, 5, "0", STR_PAD_LEFT);
      return $idcust;
	}
	
	function level_2($inventory_1)
    {
        $this->db->where('id_type', $inventory_1);
        $this->db->order_by('id_category1', 'ASC');
        return $this->db->from('ms_inventory_category1')
            ->get()
            ->result();
    }
    function level_3($id_inventory2)
    {
        $this->db->where('id_category1', $id_inventory2);
        $this->db->order_by('id_category2', 'ASC');
        return $this->db->from('ms_inventory_category2')
            ->get()
            ->result();
    }
	    function level_4($id_inventory3)
    {
        $this->db->where('id_category2', $id_inventory3);
        $this->db->order_by('id_category3', 'ASC');
        return $this->db->from('ms_inventory_category3')
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
	
    public function get_type($table,$where_field,$tanda,$where_value){
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where('id_type !=',$where_value);
		$this->db->where('deleted','0');
		$query = $this->db->get();	
		return $query->result();
	}
	
	 public function get_data_ms_material($type){
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2, e.nama as nama_category3');
		$this->db->from('ms_material a');
		$this->db->join('ms_inventory_type b','b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c','c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d','d.id_category2 =a.id_category2');
		$this->db->join('ms_inventory_category3 e','e.id_category3 =a.id_category3');
		$this->db->where('a.deleted','0');
		if($type!=''){
		$this->db->where('a.id_type',$type);
		}
		$query = $this->db->get();		
		return $query->result();
	}
	public function get_data_id_ms_material($id){
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2, e.nama as nama_category3');
		$this->db->from('ms_material a');
		$this->db->join('ms_inventory_type b','b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c','c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d','d.id_category2 =a.id_category2');
		$this->db->join('ms_inventory_category3 e','e.id_category3 =a.id_category3');
		$this->db->where('a.deleted','0');
		$this->db->where('a.id_material',$id);
		
		$query = $this->db->get();		
		return $query->result();
	}
	
	public function get_data_ms_nonmaterial($type){
		$this->db->select('a.*, b.nama as nama_type');
		$this->db->from('ms_material a');
		$this->db->join('ms_inventory_type b','b.id_type=a.id_type');
		$this->db->where('a.deleted','0');
		$this->db->where('a.id_type!=','I2000001');
		if($type!=''){
		$this->db->where('a.id_type',$type);
		}
		$query = $this->db->get();		
		return $query->result();
	}
	
	public function get_data_id_ms_nonmaterial($id){
		$this->db->select('a.*, b.nama as nama_type');
		$this->db->from('ms_material a');
		$this->db->join('ms_inventory_type b','b.id_type=a.id_type');
		$this->db->where('a.deleted','0');
		$this->db->where('a.id_material',$id);
		
		$query = $this->db->get();		
		return $query->result();
	}

     function getById($id)
    {
       return $this->db->get_where('ms_material',array('id_material' => $id))->row_array();
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

}
