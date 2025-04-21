<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Customer_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'ms_inventory_category1';
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


    function generate_id($kode = '')
    {
        $query = $this->db->query("SELECT MAX(id_customer) as max_id FROM master_customers");
        $row = $query->row_array();
        $thn = date('y');
        $max_id = $row['max_id'];
        $max_id1 = (int) substr($max_id, 4, 5);
        $counter = $max_id1 + 1;
        $idcust = "MC" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
        return $idcust;
    }
    function generate_Category($kode = '')
    {
        $query = $this->db->query("SELECT MAX(id_category_customer) as max_id FROM child_customer_category");
        $row = $query->row_array();
        $thn = date('y');
        $max_id = $row['max_id'];
        $max_id1 = (int) substr($max_id, 4, 5);
        $counter = $max_id1 + 1;
        $idcust = "CC" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
        return $idcust;
    }

    public function get_data($table, $where_field = '', $where_value = '')
    {
        if ($where_field != '' && $where_value != '') {
            $query = $this->db->get_where($table, array($where_field => $where_value));
        } else {
            $query = $this->db->get($table);
        }

        return $query->result();
    }

    public function getCustomer()
    {
        $this->db->select('a.*, c.nama_kota as nama_kota, d.nama_karyawan as nama_karyawan');
        $this->db->from('master_customers a');
        $this->db->join('kota c', 'c.id_kota=a.id_kota', 'left');
        $this->db->join('ms_karyawan d', 'd.id_karyawan=a.id_karyawan', 'left');
        $this->db->where('a.deleted', '0');
        $query = $this->db->get();
        return $query->result();
    }

    public function getinternational()
    {
        $search = "suplier_location='international' and deleted='0'";
        $this->db->select('a.*, b.name_category_supplier as name_category_supplier, c.nm_negara as nm_negara ');
        $this->db->from('master_supplier a');
        $this->db->join('child_supplier_category b', 'b.id_category_supplier=a.id_category_supplier');
        $this->db->join('negara c', 'c.id_negara=a.id_negara');
        $this->db->where($search);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_data_category1()
    {
        $this->db->select('a.*, b.nama as nama_type');
        $this->db->from('ms_inventory_category1 a');
        $this->db->join('ms_inventory_type b', 'b.id_type=a.id_type');
        $this->db->where('a.deleted', '0');
        $query = $this->db->get();
        return $query->result();
    }
    public function getDimensi($id)
    {
        $search = "deleted='0' and id_bentuk='$id'";
        $this->db->select('*');
        $this->db->from('ms_dimensi');
        $this->db->where($search);
        $query = $this->db->get();
        return $query->result();
    }

    function getById($id)
    {
        return $this->db->get_where('ms_inventory_category1', array('id_category1' => $id))->row_array();
    }

    function carikota($id_prov)
    {
        $this->db->where('id_prov', $id_prov);
        return $this->db->from('kota')
            ->get()
            ->result();
    }
}
