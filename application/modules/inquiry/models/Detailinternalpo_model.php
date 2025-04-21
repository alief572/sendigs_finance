<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Detailinternalpo_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'trans_internalpo_detail';
    protected $key        = 'no_mutasi';

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
	
	public function getDetailPO($mutasi,$cabang)
	{
		$this->db->select('a.*, b.qty_avl as avl, b.landed_cost as hpp, b.nm_barang, b.kategori, 
		b.jenis, b.satuan');
		$this->db->from('trans_internalpo_detail a'); 
		$this->db->join('barang_stock  b', 'b.id_barang=a.id_barang', 'left');
		$this->db->where('a.no_mutasi',$mutasi);
		$this->db->where('b.kdcab',$cabang);
		$query = $this->db->get(); 
		//echo "<pre>";print_r($query->result());
	
		
	if($query->num_rows() != 0)
    {
        return $query->result();
    }
    else
    {
        return false;
    }
	}

}
