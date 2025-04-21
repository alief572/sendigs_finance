<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2021, Harboens
 *
 * This is model class for table "Budget Rutin"
 */

class Budget_rutin_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'budget_rutin_header';
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

	function GetBudgetRutin(){
		$this->db->select('a.*, b.nm_gudang AS nm_dept, c.cost_center');
		$this->db->from('budget_rutin_header a');
		$this->db->join('warehouse b','a.department=b.id', 'left');
		$this->db->join('department_center c','a.department=c.id_dept','left');
		$this->db->group_by('a.code_budget');
		$this->db->order_by('a.department', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	function GetBudgetRutinDetail($code_budget){
		$this->db->select('a.*, b.stock_name nama_barang, b.spec spec1, c.nm_category nama_jenis, c.id AS id_type, b.id_unit_gudang AS id_satuan, z.code AS nm_satuan');
		$this->db->from("(select * from budget_rutin_detail where code_budget='".$code_budget."') a");
		$this->db->join('accessories b','a.id_barang=b.id','left');
		$this->db->join('accessories_category c','a.jenis_barang=c.id','right');
        $this->db->join('ms_satuan z','b.id_unit_gudang=z.id','left');
		// $this->db->where("c.id_type != 'I2000001'");
		$this->db->order_by('c.nm_category', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
}
