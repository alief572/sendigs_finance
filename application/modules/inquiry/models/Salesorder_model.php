<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Salesorder_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'trans_so_header';
    protected $key        = 'no_so';

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
    function pilih_item($kdcab){
        $query="SELECT
                barang_stock.id_barang,
                barang_stock.nm_barang,
                barang_stock.harga,
                barang_stock.qty_stock,
                barang_stock.qty_avl
                FROM
                barang_stock WHERE kdcab='$kdcab'";
        return $this->db->query($query);
    }

    function get_item_barang($idbarang){
        $query="SELECT
                *
                FROM
                barang_stock LEFT JOIN barang_master
                ON `barang_stock`.`id_barang` = `barang_master`.`id_barang`
                WHERE barang_stock.id_barang='$idbarang'";
        return $this->db->query($query);
    }

    /*
    function generate_noso($kdcab){
        $query = "SELECT cabang.no_so
                  FROM
                  cabang WHERE cabang.kdcab='$kdcab'";
        $q = $this->db->query($query);
        $r = $q->row();
        $kode = (int)$r->no_so+1;
        $next_kode = str_pad($kode, 4, "0", STR_PAD_LEFT);
        return $kdcab.'-SO'.date('y').$next_kode;
    }
    */

    function generate_noso($kdcab){
        $query = "SELECT cabang.no_so
                  FROM
                  cabang WHERE cabang.kdcab='$kdcab'";
        $q = $this->db->query($query);
        $r = $q->row();
        $kode = (int)$r->no_so+1;
        $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);

        $arr_tgl = array(1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'F',
                         7=>'G',8=>'H',9=>'I',10=>'J',11=>'K',12=>'L'
                        );
        $bln_now = date('m');
        $kode_bln = '';
        foreach($arr_tgl as $k=>$v){
            if($k == $bln_now){
                $kode_bln = $v;
            }
        }
        return $kdcab.'-SO-'.date('y').$kode_bln.$next_kode;
    }

    function generate_no_pending($param){
        $data = $this->get_data('no_so LIKE "%'.$param.'%"','trans_so_header');
        $no_akhir = count($data);
        $next_nomor = $no_akhir+1;
        $next_kode = str_pad($next_nomor, 2, "0", STR_PAD_LEFT);
        return $param.'-'.$next_kode;
    }

    function generate_no_pl($kdcab){
        $query = "SELECT cabang.th_picking_list,cabang.no_picking_list
                  FROM
                  cabang WHERE cabang.kdcab='$kdcab'";
        $q = $this->db->query($query);
        $r = $q->row();

        $kode = 1;
        if($r->th_picking_list == date('y')){
          $kode = (int)$r->no_picking_list+1;
        }
        $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);

        return $kdcab.'-PL-'.date('y').$next_kode;
    }

    function pilih_marketing(){
        $query="SELECT
                karyawan.id_karyawan,
                karyawan.nama_karyawan
                FROM karyawan where divisi='3' AND deleted=0 ";
        return $this->db->query($query);
    }

    function get_marketing($idkaryawan){
        $query="SELECT
                karyawan.id_karyawan,
                karyawan.nama_karyawan
                FROM karyawan where id_karyawan='$idkaryawan'";
        return $this->db->query($query);
    }

    function get_customer($idcus){
        $query="SELECT
                customer.id_customer,
                customer.nm_customer,
                customer.diskon_toko
                FROM customer where id_customer='$idcus'";
        return $this->db->query($query);
    }

    function get_pic_customer($idcus){
        $query="SELECT
                customer_pic.id_pic,
                customer_pic.id_customer,
                customer_pic.nm_pic,
                customer_pic.divisi,
                customer_pic.jabatan
                FROM customer_pic where id_customer='$idcus'";
        return $this->db->query($query);
    }

    public function cek_data($kunci,$tabel) {
        $this->db->where($kunci);
        $query=$this->db->get($tabel);
        return $query->row();
    }

    public function get_data($kunci,$tabel) {
        $this->db->where($kunci);
        $query=$this->db->get($tabel);
        return $query->result();
    }

    public function get_pending_so(){
      $q = "SELECT
            a.no_so,a.nm_customer,a.tanggal,a.stsorder,
            SUM(b.qty_order) AS qty_order,
            SUM(b.qty_supply) AS qty_supply,
            SUM(b.qty_pending) AS qty_pending,
            SUM(b.qty_cancel) AS qty_cancel
            FROM
            trans_so_header a
            JOIN
            trans_so_detail b ON a.no_so = b.no_so
            WHERE qty_pending != 0
            GROUP BY a.no_so ORDER BY a.no_so DESC ";
      $r = $this->db->query($q);
      return $r->result();
    }

    public function get_salesorder_open($where=false){
      /*WHERE d.qty_supply > 0 AND h.total != 0  ".$where."*/
      $q = " SELECT
              h.no_so,
              h.id_customer,
              h.nm_customer,
              h.id_salesman,
              h.nm_salesman,
              h.tanggal,
              h.pic,
              h.no_picking,
              h.total,
              d.proses_do,
              SUM(d.qty_supply)
            FROM
              trans_so_header h
            JOIN
            trans_so_detail d ON h.no_so = d.no_so
            WHERE h.stsorder='OPEN' AND h.total != 0  ".$where."
            GROUP BY h.no_so ORDER BY h.no_so DESC
          ";
      $r = $this->db->query($q);
      return $r->result();
    }

    function rekap_data(){
        $query="SELECT * FROM
                trans_so_header h INNER JOIN trans_so_detail d ON h.no_so = d.no_so";
        return $this->db->query($query);
    }

}
