<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Warehouse"
 */

class Wh_material_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'tr_warehouse';
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

	// list data
	public function GetListMaterialIn(){
		$this->db->select('a.*');
		$this->db->from($this->table_name.' a');
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data
	public function GetDataWhMaterial($id){
		$this->db->select('a.*');
		$this->db->from($this->table_name.' a');
		$this->db->where('a.id',$id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetDataWhMaterialDetail($id){
		$this->db->select('a.*, b.nama, b.spec1, b.spec2, b.spec3, b.spec4, b.spec5, b.spec6, b.spec7');
		$this->db->from('tr_warehouse_detail a');
		$this->db->join('ms_material b','a.material_id=b.id_material');
		$this->db->where('a.doc_no',$id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDetailWhMaterial($id){
		$this->db->select('a.*');
		$this->db->from('tr_warehouse_detail a');
		$this->db->where('a.id',$id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetPOCombo($where=''){
		$aCombo		= array();
		$this->db->select('a.po_no');
		$this->db->from('tr_purchase_order a');
		$this->db->order_by('a.po_no', 'desc');
		if($where!=''){
			$this->db->where($where);
		}
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aCombo[$vals['po_no']]	= $vals['po_no'];
			}
		}
		return $aCombo;
	}

	function GetListPOMaterial($id){
		$this->db->select('a.*, b.nama, b.spec1, b.spec2, b.spec3, b.spec4, b.spec5, b.spec6, b.spec7');
		$this->db->from('tr_purchase_order_detail a');
		$this->db->join('ms_material b','a.material_id=b.id_material');
		$this->db->where('a.doc_no',$id);
		$this->db->where('a.material_receive',0);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


}
