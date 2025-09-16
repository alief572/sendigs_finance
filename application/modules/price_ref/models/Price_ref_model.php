<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Price Reference"
 */

class Price_ref_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'ms_price_ref_mp';
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
	public function GetListMP(){
		$this->db->select('a.*');
		$this->db->from($this->table_name.' a');
		$this->db->order_by('a.id', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data
	public function GetDataMP($id){
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

	// list data
	public function GetListOthers($tipe=''){
		$this->db->select('a.*, b.nama, b.spec1, b.spec2, b.spec3, c.nama as nama_tipe');
		$this->db->from('ms_price_ref_others a');
		$this->db->join('ms_material b','a.element_id=b.id_material', 'left');
		$this->db->join('ms_inventory_type c','a.element_tipe=c.id_type', 'left');
		if($tipe!=''){
			$this->db->where('a.element_tipe',$tipe);
		}
		$this->db->order_by('a.id', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetComboInventoryTipe(){
		$aMenu		= array();
		$this->db->select('a.id_type, a.nama');
		$this->db->from('ms_inventory_type a');
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id_type']]	= $vals['id_type'].' - '.$vals['nama'];
			}
		}
		return $aMenu;
	}

	// get data
	public function GetDataOthers($id){
		$this->db->select('a.*, b.nama, b.spec1, b.spec2, b.spec3, c.nama as nama_tipe');
		$this->db->from('ms_price_ref_others a');
		$this->db->join('ms_material b','a.element_id=b.id_material', 'left');
		$this->db->join('ms_inventory_type c','a.element_tipe=c.id_type', 'left');
		$this->db->where('a.id',$id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// get data material new pricereference
	public function GetListMaterialNewPriceRef($tipe){
		$this->db->select('a.*, c.nama_satuan satuan');
		$this->db->from('ms_material a');
		$this->db->join('ms_material_konversi c','a.id_material=c.id_material');
		$this->db->where('a.id_type',$tipe);
		$this->db->where('a.id_material NOT IN (select element_id from ms_price_ref_others)',NULL,FALSE);
		$this->db->order_by('a.nama', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
}
