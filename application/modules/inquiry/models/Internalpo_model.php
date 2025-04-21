<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Internalpo_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'trans_internalpo_header';
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

    /*
    function generate_nodo($kdcab){
        $query = "SELECT cabang.no_suratjalan
                  FROM
                  cabang WHERE cabang.kdcab='$kdcab'";
        $q = $this->db->query($query);
        $r = $q->row();
        $kode = (int)$r->no_suratjalan+1;
        $next_kode = str_pad($kode, 4, "0", STR_PAD_LEFT);
        return $kdcab.'-DO'.date('y').$next_kode;
    }
    */
	
	 public function get_Coa_Kas_Bank($Cabang)
	{
		//TEST CABANG
		//$Cabang		= '102';
		$Arr_Coa	= $det_COA	= array();
		$Bulan_Now	= date('n');
		$Tahun_Now	= date('Y');
		$Bulan_Lalu	= 1;
		$Tahun_Lalu	= $Tahun_Now;
		if($Bulan_Now == 1){
		  $Bulan_Lalu	= 12;
		  $Tahun_Lalu	= $Tahun_Now - 1;
		}
		$Kode_Cab		= 'A';
		$Query_Cabang	= $this->db->get_where('pastibisa_tb_cabang',array('nocab'=>$Cabang))->result();
		if($Query_Cabang){
		  $Kode_Cab	= $Query_Cabang[0]->subcab;
		}
		$Cab_Kode		= $Cabang.'-'.$Kode_Cab;
		$Query_Coa	= "SELECT * FROM COA WHERE bln='$Bulan_Now' AND thn='$Tahun_Now' AND `level`='5' AND SUBSTRING(no_perkiraan,1,4) IN ('1101','1102') AND kdcab='$Cab_Kode'";
		$Pros_Coa		= $this->db->query($Query_Coa);
		$Num_Coa		= $Pros_Coa->num_rows();
		if($Num_Coa > 0){
		   $det_COA	= $Pros_Coa->result();
		}else{
		   $Query_Coa	= "SELECT * FROM COA WHERE bln='$Bulan_Lalu' AND thn='$Tahun_Lalu' AND `level`='5' AND SUBSTRING(no_perkiraan,1,4) IN ('1101','1102') AND kdcab='$Cab_Kode'";
		  $Pros_Coa		= $this->db->query($Query_Coa);
		  $Num_Coa		= $Pros_Coa->num_rows();
		  if($Num_Coa > 0){
			  $det_COA	= $Pros_Coa->result();
		  }
		}
		if($det_COA){
			foreach($det_COA as $key=>$vals){
				$Name_Coa		= $vals->no_perkiraan.' - '.$vals->nama;
				$Kode_Coa		= $vals->no_perkiraan;
				$Arr_Coa[$Kode_Coa]	= $Name_Coa;
			}
		}
		
		return $Arr_Coa;
	}

    function generate_no_mutasi_old($kdcab){
        $query = "SELECT cabang.no_mutasi
                  FROM
                  cabang WHERE cabang.kdcab='$kdcab'";
        $q = $this->db->query($query);
        $r = $q->row();
        $kode = (int)$r->no_mutasi+1;
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
        return $kdcab.'-MT-'.date('y').$kode_bln.$next_kode;
    }

    function generate_no_mutasi($kdcab){

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
        $cek = $kdcab.'-MT-'.date('y').$kode_bln;
        /*$query_cek = $this->db->query("SELECT MAX(no_so) as max_id FROM trans_so_header_internal
        WHERE no_so LIKE '%$cek%'")->num_rows();*/
        $query = "SELECT MAX(no_mutasi) as max_id
        FROM
        trans_internalpo_header WHERE LEFT(no_mutasi,3)='$kdcab' AND no_mutasi LIKE '%$cek%'";
        $q = $this->db->query($query);
        $query_cek = $q->num_rows();
        if ($query_cek == 0) {
          $kode = 1;
          $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
          $fin = $kdcab.'-MT-'.date('y').$kode_bln.$next_kode;
        }else {
          $query = "SELECT MAX(no_mutasi) as max_id
          FROM
          trans_internalpo_header WHERE LEFT(no_mutasi,3)='$kdcab' AND no_mutasi LIKE '%$cek%'";
          $q = $this->db->query($query);
          $r = $q->row();
          $kode = (int)substr($r->max_id,10)+1;
          $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
          $fin =  $kdcab.'-MT-'.date('y').$kode_bln.$next_kode;
        }


      return $fin;
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

    function pilih_driver($kdcab){
        $query="SELECT
                karyawan.id_karyawan,
                karyawan.nama_karyawan
                FROM karyawan where divisi='12' and kdcab='$kdcab'";
        return $this->db->query($query);
    }

    function pilih_kendaraan($kdcab){
        $query="SELECT
                kendaraan.id_kendaraan,
                kendaraan.nm_kendaraan
                FROM kendaraan WHERE kdcab='$kdcab'";
        return $this->db->query($query);
    }

    function get_cabang($kdcab){
        $query="SELECT
                cabang.kdcab,
                cabang.namacabang
                FROM cabang where kdcab='".$kdcab."' ";
        return $this->db->query($query)->row();
    }

	function get_customer($id){

        $this->db->where('id_customer',$id);
        $query=$this->db->get(customer);
        return $query->result();

        //LEFT JOIN barang_master ON `barang_stock`.`id_barang` = `barang_master`.`id_barang`
    }

	function generate_no_pl($kdcab,$noso){
      $urut = substr($noso,8,8);

      return $kdcab.'-PL-'.$urut;
    }

	function generate_noso($kdcab,$tgl){

        $arr_tgl = array(1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'F',
        7=>'G',8=>'H',9=>'I',10=>'J',11=>'K',12=>'L'
        );
        $bln_now = date('m',strtotime($tgl));
        $kode_bln = '';
        foreach($arr_tgl as $k=>$v){
          if($k == $bln_now){
            $kode_bln = $v;
          }
        }
        $cek = $kdcab.'-SOI-'.date('y').$kode_bln;
        /*$query_cek = $this->db->query("SELECT MAX(no_so) as max_id FROM trans_so_header_internal
        WHERE no_so LIKE '%$cek%'")->num_rows();*/
        $query = "SELECT MAX(no_so) as max_id
        FROM
        trans_so_header_internal WHERE LEFT(no_so,3)='$kdcab' AND stsorder != 'PENDING' AND no_so LIKE '%$cek%'";
        $q = $this->db->query($query);
        $query_cek = $q->num_rows();
        if ($query_cek == 0) {
          $kode = 1;
          $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
          $fin 		 = $kdcab.'-SOI-'.date('y').$kode_bln.$next_kode;
        }else {
          $query = "SELECT MAX(no_so) as max_id
          FROM
          trans_so_header_internal WHERE LEFT(no_so,3)='$kdcab' AND stsorder != 'PENDING' AND no_so LIKE '%$cek%'";
          $q = $this->db->query($query);
          $r = $q->row();
          $kode = (int)substr($r->max_id,11)+1;
          $next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
          $fin =  $kdcab.'-SOI-'.date('y').$kode_bln.$next_kode;
        }


      return $fin;
    }


	function update_stok_avl($barang,$kdcabtujuan,$qty){

	                $Ambil_barang  = "SELECT a.* FROM barang_stock a
					WHERE a.id_barang ='$barang' AND a.kdcab='$kdcabtujuan'";

					$Hasil_barang	= $this->db->query($Ambil_barang);


					$databarang = $Hasil_barang->result_array();
					foreach ($databarang as $row=>$brg) {

					$avl      = $brg['qty_avl'];
					$sisa_avl = $avl - $qty;

 					}


					$update_available  =  "UPDATE barang_stock  SET  qty_avl=$sisa_avl
					WHERE id_barang ='$barang' AND kdcab='$kdcabtujuan'";
					$this->db->query($update_available);

	}
}
