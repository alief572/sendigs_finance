<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Product_model"
 */

class Product_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'product';
    protected $key        = 'id_product';

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

    public function generate_id() {
     
      $query = $this->db->query("SELECT MAX(id_product) as max_id FROM product");
      $row = $query->row_array();
      $max_id = $row['max_id'];
      $max_id1 =(int) substr($max_id,2,4);
      $kode = $max_id1 + 1;
      $newkode ='P01'.str_pad($kode, 4, "0", STR_PAD_LEFT);
      return $newkode;
    }

    public function pilih_barang(){
        $query="SELECT
                barang_master.id_barang,
                barang_master.nm_barang
                FROM
                barang_master where sts_aktif='aktif'";
        return $this->db->query($query);
    }

    public function get_barang($id_barang){
        $query=$this->db->query("SELECT
                barang_master.nm_barang
                FROM
                barang_master where id_barang='".$id_barang."'");
        $row = $query->row_array();
        $nm_barang = $row['nm_barang'];
        return $nm_barang;
    }

    public function tampil_produk(){
        $query="SELECT
        barang_master.id_barang,
        barang_jenis.nm_jenis,
        barang_group.nm_group,
        barang_master.nm_barang,
        barang_master.satuan,
        barang_master.sts_aktif,
        barang_master.qty
        FROM
        barang_master
        INNER JOIN barang_jenis ON barang_master.jenis = barang_jenis.id_jenis
        INNER JOIN barang_group ON barang_master.id_group = barang_group.id_group
        WHERE barang_master.deleted = '0'";
        return $this->db->query($query);
    }

    function tampil_summary(){
        $query="SELECT
        sum(barang_master.netto_weight) as netto_weight,
        sum(barang_master.cbm_each) as cbm_each,
        sum(barang_master.gross_weight) as gross_weight
        FROM
        barang_master";
        $data = $this->db->query($query);
        return $data->row();
    }

    function tampil_foto($id){
        $query="SELECT
                barang_master.foto_barang
                FROM barang_master WHERE id_barang='$id'";
        $data = $this->db->query($query);
        return $data->row();
    }
}
