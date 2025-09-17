<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Karyawan_model"
 */

class Karyawan_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'karyawan';
    protected $key        = 'id_karyawan';

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

    function get_div($id)
    {
        $query="SELECT
                karyawan.divisi,
                karyawan.id_karyawan
                FROM
                karyawan where id_karyawan='$id'";
        return $this->db->query($query);
    }    

    function rekap_data(){
        $query="SELECT
            karyawan.id_karyawan,
            karyawan.nik,
            karyawan.nama_karyawan,
            karyawan.tempatlahir,
            karyawan.tanggallahir,
            karyawan.divisi,
            karyawan.jeniskelamin,
            karyawan.agama,
            karyawan.levelpendidikan,
            karyawan.alamataktif,
            karyawan.nohp,
            karyawan.noktp,
            karyawan.npwp,
            karyawan.photo,
            karyawan.email,
            karyawan.tgl_join,
            karyawan.tgl_end,
            karyawan.sts_karyawan,
            karyawan.norekening,
            karyawan.sts_aktif,
            karyawan.created_on,
            karyawan.modified_on,
            karyawan.created_by,
            karyawan.modified_by,
            karyawan.deleted,
            karyawan.deleted_by,
            divisi.nm_divisi
            FROM
            karyawan
            INNER JOIN divisi ON karyawan.divisi = divisi.id_divisi";
        return $this->db->query($query);
    }
}
