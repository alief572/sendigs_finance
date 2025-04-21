<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/* 
 * @author CokesHome
 * @copyright Copyright (c) 2016, CokesHome
 * 
 * This is model class for table "log_aktifitas"
 */

class Aktifitas_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'log_aktifitas';
    protected $key        = 'idlog';

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
    protected $set_modified = false;

    /**
     * @var string The type of date/time field used for $created_field and $modified_field.
     * Valid values are 'int', 'datetime', 'date'.
     */
    protected $date_format = 'datetime';
    //--------------------------------------------------------------------

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

        $this->load->model('aktifitas/permissions_model');
    }
    /**
    * Fungsi untuk menyimpan aktivitas pengguna
    * Variable yang wajib diisi adalah $nm_hak_akses, $kode_universal, dan $keterangan, $status
    * Return TRUE jika sukses, FALSE jika gagal
    */
    public function simpan_aktifitas($nm_hak_akses = "", $kode_universal = "", $keterangan ="", $jumlah = 0, $sql = "", $status = NULL)
    {
        if($nm_hak_akses == "" || $kode_universal == "" || $keterangan == "" || $status === NULL)
        {
            return FALSE;
        }

        $user_id         = $this->auth->user_id();
        $permission_data = $this->permissions_model->find_by(array('nm_permission' => $nm_hak_akses)); 
        $fasilitas       = ($permission_data) ? $permission_data->id_permission : 0;
        $id_log          = gen_primary();

        while ($this->find($id_log) !== FALSE) {
            $id_log = gen_primary();
        }

        $insert_data = array(
                        'idlog' => $id_log,
                        'kode_universal' => $kode_universal,
                        'fasilitas'     => $fasilitas,
                        'ket'       => $keterangan,
                        'jumlah'    => $jumlah,
                        'sql'       => $sql,
                        'status'    => $status
                        );
        $this->insert($insert_data);

        /*Cek apakah data berhasil disimpan ?, harus dicek manual karena 
        * type data pada field primary key-nya berupa string
        */
        $cek = $this->find($id_log);
        if($cek != FALSE)
        {
            $result = TRUE;
        }
        else
        {
            $result = FALSE;
        }

        return $result;
    }
}