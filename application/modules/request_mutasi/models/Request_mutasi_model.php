<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is Model for Request mutasi
 */

class Request_mutasi_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'request_mutasi';
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

    public function GetListDataRequest()
    {
        $query = $this->db->query("SELECT a.*
        FROM tr_request_mutasi a WHERE a.status='0'
        ORDER BY a.id DESC")->result();

        return $query;
    }

    public function GetListDataMutasi()
    {
        $query = $this->db->query("SELECT a.*
        FROM tr_request_mutasi_aktual a
        ORDER BY a.id DESC")->result();

        return $query;
    }

    public function GetListApproval()
    {
        $query =  $this->db->query("SELECT a.*
        FROM tr_request_mutasi a WHERE a.status_approve='0'
        ORDER BY a.id DESC")->result();

        return $query;
    }

    public function GetDataApprove($id)
    {
        $query =  $this->db->query("SELECT a.*
          FROM tr_request_mutasi a WHERE kd_mutasi='$id'
          ORDER BY a.id DESC")->result();

        return $query;
    }

    public function GetListDataTransaksi()
    {
        $query =  $this->db->query("SELECT a.*
          FROM tr_request_mutasi_admin a
          ORDER BY a.id DESC")->result();

        return $query;
    }

    //buat bikin kode request
    public function generate_nopn($tgl)
    {
        $arr_tgl = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H',
            9 => 'I',
            10 => 'J',
            11 => 'K',
            12 => 'L'
        );
        $bln_now = date('m', strtotime($tgl));
        $kode_bln = '';
        foreach ($arr_tgl as $k => $v) {
            if ($k == $bln_now) {
                $kode_bln = $v;
            }
        }
        $cek = 'MTS-' . date('ym');
        $this->db->select("MAX(kd_mutasi) as max_id");
        $this->db->like('kd_mutasi', $cek);
        $this->db->from('tr_request_mutasi');
        $query_cek = $this->db->count_all_results();

        if ($query_cek == 0) {
            $kode = 1;
            $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $fin = 'MTS-' . date('ym') . $next_kode;
        } else {
            $query = "SELECT MAX(kd_mutasi) as max_id
        FROM
        tr_request_mutasi WHERE kd_mutasi LIKE '%$cek%'";
            $q = $this->db->query($query);
            $r = $q->row();


            $query = $this->db->query("SELECT MAX(kd_mutasi) as max_id
        FROM
        tr_request_mutasi WHERE kd_mutasi LIKE '%$cek%'");
            $row = $query->row_array();
            $thn = date('T');
            $max_id = $row['max_id'];
            $max_id1 = (int) substr($max_id, -5);
            $kode = $max_id1 + 1;

            $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $fin = 'MTS-' . date('ym') . $next_kode;
        }
        return $fin;
    }

    public function generate_notr($tgl)
    {
        $arr_tgl = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H',
            9 => 'I',
            10 => 'J',
            11 => 'K',
            12 => 'L'
        );
        $bln_now = date('m', strtotime($tgl));
        $kode_bln = '';
        foreach ($arr_tgl as $k => $v) {
            if ($k == $bln_now) {
                $kode_bln = $v;
            }
        }
        $cek = 'KK-' . date('ym');
        $this->db->select("MAX(kd_mutasi) as max_id");
        $this->db->like('kd_mutasi', $cek);
        $this->db->from('tr_request_mutasi_admin');
        $query_cek = $this->db->count_all_results();

        if ($query_cek == 0) {
            $kode = 1;
            $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $fin = 'KK-' . date('ym') . $next_kode;
        } else {
            $query = "SELECT MAX(kd_mutasi) as max_id
        FROM
        tr_request_mutasi_admin WHERE kd_mutasi LIKE '%$cek%'";
            $q = $this->db->query($query);
            $r = $q->row();


            $query = $this->db->query("SELECT MAX(kd_mutasi) as max_id
        FROM
        tr_request_mutasi_admin WHERE kd_mutasi LIKE '%$cek%'");
            $row = $query->row_array();
            $thn = date('T');
            $max_id = $row['max_id'];
            $max_id1 = (int) substr($max_id, -5);
            $kode = $max_id1 + 1;

            $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $fin = 'KK-' . date('ym') . $next_kode;
        }
        return $fin;
    }

    public function generate_nokm($tgl)
    {
        $arr_tgl = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H',
            9 => 'I',
            10 => 'J',
            11 => 'K',
            12 => 'L'
        );
        $bln_now = date('m', strtotime($tgl));
        $kode_bln = '';
        foreach ($arr_tgl as $k => $v) {
            if ($k == $bln_now) {
                $kode_bln = $v;
            }
        }
        $cek = 'KM-' . date('ym');
        /*$query_cek = $this->db->query("SELECT MAX(no_so) as max_id FROM trans_so_header
      WHERE no_so LIKE '%$cek%'")->num_rows();*/
        $this->db->select("MAX(kd_mutasi) as max_id");
        $this->db->like('kd_mutasi', $cek);
        $this->db->from('tr_request_mutasi_admin');
        $query_cek = $this->db->count_all_results();

        if ($query_cek == 0) {
            $kode = 1;
            $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $fin = 'KM-' . date('ym') . $next_kode;
        } else {
            $query = "SELECT MAX(kd_mutasi) as max_id
        FROM
        tr_request_mutasi_admin WHERE kd_mutasi LIKE '%$cek%'";
            $q = $this->db->query($query);
            $r = $q->row();


            $query = $this->db->query("SELECT MAX(kd_mutasi) as max_id
        FROM
        tr_request_mutasi_admin WHERE kd_mutasi LIKE '%$cek%'");
            $row = $query->row_array();
            $thn = date('T');
            $max_id = $row['max_id'];
            $max_id1 = (int) substr($max_id, -5);
            $kode = $max_id1 + 1;

            $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $fin = 'KM-' . date('ym') . $next_kode;
        }
        return $fin;
    }
}
