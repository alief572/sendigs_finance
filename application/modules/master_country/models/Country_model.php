<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Mohammad Ichsan
 * @copyright Copyright (c) 2019, Mohammad Ichsan
 *
 * This is model class for table "master_country"
 */

class Country_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'master_country';
    protected $key        = 'id_country';

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

    function generate_id() {
      $query = $this->db->query("SELECT MAX(id_country) as max_id FROM master_country");
      $row = $query->row_array();
      $max_id = $row['max_id'];
      $max_id1 =(int) substr($max_id,3,3);
      $counter = $max_id1 +1;
      $idsup = "CO".str_pad($counter, 5, "0", STR_PAD_LEFT);
      return $idsup;
    }

    function get_idtoko($kode=''){
        $query = $this->db->query("SELECT MAX(id_toko) as max_id FROM customer_toko");
        $row = $query->row_array();
        $max_id = $row['max_id'];
        $max_id1 =(int) substr($max_id,12,3);
        $counter = $max_id1 +1;
        $idcusttoko = $kode."-T".str_pad($counter, 3, "0", STR_PAD_LEFT);
        return $idcusttoko;
    }

    function pilih_provinsi()
    {
        $query="SELECT
                provinsi.id_prov,
                provinsi.nama
                FROM
                provinsi";
        return $this->db->query($query);
    }

    function get_prov($id){
        $query = $this->db->query("SELECT id_prov FROM supplier WHERE id_supplier='$id'");
        $row = $query->row_array();
        $provinsi     = $row['id_prov'];
        return $provinsi;
    }

    function pilih_kota($provinsi){
        $query="SELECT
                kabupaten.id_kab,
                kabupaten.nama
                FROM kabupaten where id_prov='$provinsi'";
        return $this->db->query($query);
    }

    function pilih_marketing(){
        $query="SELECT
                karyawan.id_karyawan,
                karyawan.nama_karyawan
                FROM karyawan where divisi='Marketing'";
        return $this->db->query($query);
    }

    function pilih_supplier(){
        $query="SELECT
                supplier.id_supplier,
                supplier.nm_supplier
                FROM supplier where sts_aktif='aktif'";
        return $this->db->query($query);
    }

    function tampil_foto($id_customer){
        $query="SELECT
                customer.foto
                FROM customer WHERE id_customer='$id_customer'";
        $data = $this->db->query($query);
        return $data->row();
    }

    function get_inisial($id){
        $query="SELECT
                negara.id_negara
                FROM negara WHERE id='$id'";
        $data = $this->db->query($query);
        return $data->row();
    }

    function rekap_data(){
        $query="SELECT
        supplier.id_supplier,
        supplier.nm_supplier,
        negara.nm_negara,
        provinsi.nama,
        kabupaten.nama,
        supplier.mata_uang,
        supplier.alamat,
        supplier.telpon,
        supplier.fax,
        supplier.email,
        supplier.cp,
        supplier.hp_cp,
        supplier.id_webchat,
        supplier.npwp,
        supplier.alamat_npwp,
        supplier.keterangan
        FROM
        supplier
        LEFT JOIN negara ON supplier.id_negara = negara.id_negara
        LEFT JOIN provinsi ON supplier.id_prov = provinsi.id_prov
        LEFT JOIN kabupaten ON supplier.id_kab = kabupaten.id_kab";
        return $this->db->query($query);
    }

    function print_data_supplier($id){
        $query="SELECT
        supplier.id_supplier,
        supplier.nm_supplier,
        negara.nm_negara,
        provinsi.nama as nm_prov,
        kabupaten.nama as nm_kab,
        supplier.mata_uang,
        supplier.alamat,
        supplier.telpon,
        supplier.fax,
        supplier.email,
        supplier.cp,
        supplier.hp_cp,
        supplier.id_webchat,
        supplier.npwp,
        supplier.alamat_npwp,
        supplier.keterangan
        FROM
        supplier
        LEFT JOIN negara ON supplier.id_negara = negara.id_negara
        LEFT JOIN provinsi ON supplier.id_prov = provinsi.id_prov
        LEFT JOIN kabupaten ON supplier.id_kab = kabupaten.id_kab
        WHERE supplier.id_supplier='$id'";
        $data = $this->db->query($query);
        return $data->row();
    }

    public function insert_supplier_cbm($data)
    {
        return $this->db->insert('supplier_cbm', $data);
    }

    public function update_supplier_cbm($id, $data)
    {
        $this->db->where('id_supplier_cbm', $id);
        return $this->db->update('supplier_cbm', $data);
    }

    public function delete_supplier_cbm($id)
    {
        $this->db->where('id_supplier_cbm', $id);
        return $this->db->delete('supplier_cbm');
    }

    public function insert_supplier_barang($data)
    {
        return $this->db->insert('supplier_barang', $data);
    }

    public function list_supplier_barang($id_supplier)
    {
        $this->db->select('supplier_barang.id_supplier_barang,barang_master.nm_barang ');
        $this->db->from('supplier_barang');
        $this->db->join('barang_master', 'barang_master.id_barang = supplier_barang.id_barang', 'left');
        $this->db->where('supplier_barang.id_supplier', $id_supplier);
        return $this->db->get();
    }

    public function delete_supplier_barang($id)
    {
        $this->db->where('id_supplier_barang', $id);
        return $this->db->delete('supplier_barang');
    }

}
