<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2021
 *
 * This is model class for table "Purchase Request Rutin"
 */

class Pr_rutin_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $table_name = 'material_planning_base_on_produksi';
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
	public function GetListPrRutin($where = '')
	{
		$this->db->select('a.*,b.nm_lengkap,c.status_app as status');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.created_by = b.id_user', 'left');
		$this->db->join('material_planning_base_on_produksi_detail c', 'c.so_number = a.so_number', 'left');
		$this->db->where('a.category', 'pr stok');
		$this->db->where('c.status_app', 'N');
		if ($where !== "") $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$this->db->group_by('a.so_number');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data
	public function GetDataPrRutin($id)
	{
		$this->db->select('a.*');
		$this->db->from($this->table_name . ' a');
		$this->db->where('a.so_number', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetDataPrRutinDetail($id)
	{
		$this->db->select('a.*, b.stock_name, c.code as satuan');
		$this->db->from('material_planning_base_on_produksi_detail a');
		$this->db->join('accessories b', 'b.id = a.id_material', 'left');
		$this->db->join('ms_satuan c', 'c.id = b.id_unit', 'left');

		$this->db->where('a.so_number =', $id);
		// 	
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDetailPrRutin($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_pr_rutin_detail a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudgetRutin()
	{
		$this->db->select('a.material_qty, a.id_barang, a.jenis_barang, a.satuan material_unit, b.nama nama_barang, b.spec1, b.id_material, c.nama nama_jenis, c.id_type, d.stock as material_stock, 0 as material_order, e.element_cost material_price_ref, e.element_kurs kurs');
		$this->db->from(" ms_material b ");
		$this->db->join('(select sum(kebutuhan_month) as material_qty, id_barang, jenis_barang, satuan from budget_rutin_detail group by id_barang,jenis_barang,satuan) a', 'a.id_barang=b.id_material');
		$this->db->join('ms_inventory_type c', 'a.jenis_barang=c.id_type');
		$this->db->join('(select sum(stock)as stock, id_material from ms_warehouse_stock group by id_material) d', 'a.id_barang=d.id_material', 'left');
		$this->db->join('ms_price_ref_others e', 'a.id_barang=e.element_id', 'left');
		$this->db->order_by('c.nama asc, b.nama asc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
}
