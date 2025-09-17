<?php

defined('BASEPATH') || exit('No direct script access allowed');

function get_supplier($id = false)
{
  $CI = &get_instance();
  $CI->db->where('id_supplier', $id);
  $result = $CI->db->get('supplier')->row();

  return $result->group_produk;
}

function get_invoice($id = false)
{
  $CI = &get_instance();
  $CI->db->where('no_po', $id);
  $result = $CI->db->get('trans_po_invoice')->row();

  return $result->no_invoice;
}

function check_class($item = '', $class_only = false)
{
  if (strtolower(get_instance()->router->class) == strtolower($item)) {
    return $class_only ? 'active' : 'class="active"';
  }

  return '';
}

function get_name($table, $field, $where, $value)
{
  $CI = &get_instance();
  $query = "SELECT " . $field . " FROM " . $table . " WHERE " . $where . "='" . $value . "' LIMIT 1";
  // echo $query; exit;
  $result = $CI->db->query($query)->result();
  // echo $result[0]->$field; exit;
  $hasil = (!empty($result)) ? $result[0]->$field : '';
  if (empty($result)) {
    $hasil = $value;
  }
  return $hasil;
}

function get_incoming_sum_material($kode_trans){
  $CI = &get_instance();

  $query = "SELECT IF(SUM(qty_order) IS NULL, 0, SUM(qty_order)) AS ttl_qty_order FROM tr_incoming_check_detail WHERE kode_trans = '".$kode_trans."'";

  $result = $CI->db->query($query)->row();

  $hasil = (!empty($result)) ? $result->ttl_qty_order : 0;

  return $hasil;
}

/**
 * A simple helper method for checking menu items against the current method
 * (controller action) (as far as the Router knows).
 *
 * @param string $item       The name of the method to check against. Can be an array of names.
 * @param bool   $class_only If true, will only return 'active'. If false, will return 'class="active"'.
 *
 * @return string either 'active'/'class="active"' or an empty string
 */
function check_method($item, $class_only = false)
{
  $items = is_array($item) ? $item : array($item);
  if (in_array(get_instance()->router->method, $items)) {
    return $class_only ? 'active' : 'class="active"';
  }

  return '';
}

/**
 * Check if the logged user has permission or not.
 *
 * @param string $permission_name
 *
 * @return bool True if has permission and false if not
 */
function has_permission($permission_name = '')
{
  $ci = &get_instance();

  $return = $ci->auth->has_permission($permission_name);

  return $return;
}

/**
 * @param string $kode_tambahan
 *
 * @return string generated code
 */
function gen_primary($kode_tambahan = '')
{
  $CI = &get_instance();

  $tahun = intval(date('Y'));
  $bulan = intval(date('m'));
  $hari = intval(date('d'));
  $jam = intval(date('H'));
  $menit = intval(date('i'));
  $detik = intval(date('s'));
  $temp_ip = ($CI->input->ip_address()) == '::1' ? '127.0.0.1' : $CI->input->ip_address();
  $temp_ip = explode('.', $temp_ip);
  $ipval = $temp_ip[0] + $temp_ip[1] + $temp_ip[2] + $temp_ip[3];

  $kode_rand = mt_rand(1, 1000) + $ipval;
  $letter1 = chr(mt_rand(65, 90));
  $letter2 = chr(mt_rand(65, 90));

  $kode_primary = $tahun . $bulan . $hari . $jam . $menit . $detik . $letter1 . $kode_rand . $letter2;

  return $kode_tambahan . $kode_primary;
}

if (!function_exists('gen_idcustomer')) {
  function gen_idcustomer($kode_tambahan = '')
  {
    $CI = &get_instance();
    $CI->load->model('Customer/Customer_model');

    $query = $CI->Customer_model->generate_id($kode_tambahan);
    if (empty($query)) {
      return 'Error';
    } else {
      return $query;
    }
  }
}

if (!function_exists('gen_id_toko')) {
  function gen_id_toko($kode_tambahan = '')
  {
    $CI = &get_instance();
    $CI->load->model('Customer/Toko_model');

    $query = $CI->Toko_model->generate_id($kode_tambahan);
    if (empty($query)) {
      return 'Error';
    } else {
      return $query;
    }
  }
}

if (!function_exists('get_id_pnghn')) {
  function get_id_pnghn($kode_tambahan = '')
  {
    $CI = &get_instance();
    $CI->load->model('Customer/Penagihan_model');

    $query = $CI->Penagihan_model->generate_id($kode_tambahan);
    if (empty($query)) {
      return 'Error';
    } else {
      return $query;
    }
  }
}

if (!function_exists('get_id_pmbyr')) {
  function get_id_pmbyr($kode_tambahan = '')
  {
    $CI = &get_instance();
    $CI->load->model('Customer/Pembayaran_model');

    $query = $CI->Pembayaran_model->generate_id($kode_tambahan);
    if (empty($query)) {
      return 'Error';
    } else {
      return $query;
    }
  }
}

if (!function_exists('get_id_pic')) {
  function get_id_pic($kode_tambahan = '')
  {
    $CI = &get_instance();
    $CI->load->model('Customer/Pic_model');

    $query = $CI->Pic_model->generate_id($kode_tambahan);
    if (empty($query)) {
      return 'Error';
    } else {
      return $query;
    }
  }
}

if (!function_exists('gen_idsupplier')) {
  function gen_idsupplier($kode_tambahan = '')
  {
    $CI = &get_instance();
    $CI->load->model('Supplier/Supplier_model');

    $query = $CI->Supplier_model->generate_id($kode_tambahan);
    if (empty($query)) {
      return 'Error';
    } else {
      return $query;
    }
  }
}

if (!function_exists('simpan_aktifitas')) {
  function simpan_aktifitas($nm_hak_akses = '', $kode_universal = '', $keterangan = '', $jumlah = 0, $sql = '', $status = null)
  {
    $CI = &get_instance();

    $CI->load->model('aktifitas/aktifitas_model');

    $result = $CI->aktifitas_model->simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    return $result;
  }
}

/*
* $date_from is the date with format dd/mm/yyyy H:i:s / dd/mm/yyyy
*/
if (!function_exists('date_ymd')) {
  function date_ymd($date_from)
  {
    $error = false;
    if (strlen($date_from) <= 10) {
      list($dd, $mm, $yyyy) = explode('/', $date_from);

      if (!checkdate(intval($mm), intval($dd), intval($yyyy))) {
        $error = true;
      }
    } else {
      list($dd, $mm, $yyyy) = explode('/', $date_from);
      list($yyyy, $hhii) = explode(' ', $yyyy);

      if (!checkdate($mm, $dd, $yyyy)) {
        $error = true;
      }
    }

    if ($error) {
      return false;
    }

    if (strlen($date_from) <= 10) {
      $date_from = DateTime::createFromFormat('d/m/Y', $date_from);
      $date_from = $date_from->format('Y-m-d');
    } else {
      $date_from = DateTime::createFromFormat('d/m/Y H:i', $date_from);
      $date_from = $date_from->format('Y-m-d H:i');
    }

    return $date_from;
  }
}

if (!function_exists('simpan_alurkas')) {
  function simpan_alurkas($kode_accountKas = null, $ket = '', $total = null, $status = null, $nm_hak_akses = '')
  {
    $CI = &get_instance();

    $CI->load->model('kas/kas_model');

    $result = $CI->kas_model->simpan_alurKas($kode_accountKas, $ket, $total, $status, $nm_hak_akses);

    return $result;
  }
}

if (!function_exists('buatrp')) {
  function buatrp($angka)
  {
    $jadi = 'Rp ' . number_format($angka, 0, ',', '.');

    return $jadi;
  }
}

if (!function_exists('formatnomor')) {
  function formatnomor($angka)
  {
    if ($angka) {
      $jadi = number_format($angka, 0, ',', '.');

      return $jadi;
    }
  }
}

if (!function_exists('separator')) {
  function separator($angka)
  {
    if ($angka) {
      $jadi = number_format($angka, 0, '.', '.');

      return $jadi;
    }
  }
}

if (!function_exists('ynz_terbilang_format')) {
  function ynz_terbilang_format($x)
  {
    $x = abs($x);
    $angka = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
    $temp = '';
    if ($x < 12) {
      $temp = ' ' . $angka[$x];
    } elseif ($x < 20) {
      $temp = ynz_terbilang_format($x - 10) . ' belas';
    } elseif ($x < 100) {
      $temp = ynz_terbilang_format($x / 10) . ' puluh' . ynz_terbilang_format($x % 10);
    } elseif ($x < 200) {
      $temp = ' seratus' . ynz_terbilang_format($x - 100);
    } elseif ($x < 1000) {
      $temp = ynz_terbilang_format($x / 100) . ' ratus' . ynz_terbilang_format($x % 100);
    } elseif ($x < 2000) {
      $temp = ' seribu' . ynz_terbilang_format($x - 1000);
    } elseif ($x < 1000000) {
      $temp = ynz_terbilang_format($x / 1000) . ' ribu' . ynz_terbilang_format($x % 1000);
    } elseif ($x < 1000000000) {
      $temp = ynz_terbilang_format($x / 1000000) . ' juta' . ynz_terbilang_format($x % 1000000);
    } elseif ($x < 1000000000000) {
      $temp = ynz_terbilang_format($x / 1000000000) . ' milyar' . ynz_terbilang_format(fmod($x, 1000000000));
    } elseif ($x < 1000000000000000) {
      $temp = ynz_terbilang_format($x / 1000000000000) . ' trilyun' . ynz_terbilang_format(fmod($x, 1000000000000));
    }

    return $temp;
  }
}

if (!function_exists('ynz_terbilang')) {
  function ynz_terbilang($x, $style = 1)
  {
    if ($x < 0) {
      $hasil = 'minus ' . trim(ynz_terbilang_format($x));
    } else {
      $hasil = trim(ynz_terbilang_format($x));
    }
    switch ($style) {
      case 1:
        $hasil = strtoupper($hasil);
        break;
      case 2:
        $hasil = strtolower($hasil);
        break;
      case 3:
        $hasil = ucwords($hasil);
        break;
      default:
        $hasil = ucfirst($hasil);
        break;
    }

    return $hasil;
  }
}

if (!function_exists('tipe_pengiriman')) {
  function tipe_pengiriman($ket = false)
  {
    $uu = array(
      'SENDIRI' => 'MILIK SENDIRI',
      'SEWA' => 'SEWA',
      'EKSPEDISI' => 'EKSPEDISI',
      'PELANGGAN' => 'PELANGGAN AMBIL SENDIRI',
    );
    if ($ket == true) {
      return $uu[$ket];
    } else {
      return $uu;
    }
  }
}

if (!function_exists('selisih_hari')) {
  function selisih_hari($tgl, $now)
  {
    $aw = new DateTime($tgl);
    $ak = new DateTime($now);
    $interval = $aw->diff($ak);

    return $interval->days;
  }
}

if (!function_exists('kategori_umur_piutang')) {
  function kategori_umur_piutang($ket = false)
  {
    $uu = array(
      '0|14' => '0-14',
      '15|29' => '15-29',
      '30|59' => '30-59',
      '60|89' => '60-89',
      '90' => '>90',
    );
    if ($ket == true) {
      return $uu[$ket];
    } else {
      return $uu;
    }
  }
}

if (!function_exists('the_bulan')) {
  function the_bulan($time = false)
  {
    $a = array(
      '1' => 'Januari',
      '2' => 'Februari',
      '3' => 'Maret',
      '4' => 'April',
      '5' => 'Mei',
      '6' => 'Juni',
      '7' => 'Juli',
      '8' => 'Agustus',
      '9' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember',
    );

    return $time == false ? $a : $a[$time];
  }
}

if (!function_exists('bulan')) {
  function bulan($time = false)
  {
    $a = array(
      '01' => 'Januari',
      '02' => 'Februari',
      '03' => 'Maret',
      '04' => 'April',
      '05' => 'Mei',
      '06' => 'Juni',
      '07' => 'Juli',
      '08' => 'Agustus',
      '09' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember',
    );

    return $time == false ? $a : $a[$time];
  }
}

if (!function_exists('is_jenis_bayar')) {
  function is_jenis_bayar($ket = false)
  {
    $uu = array(
      'CASH' => 'CASH',
      'TRANSFER' => 'TRANSFER',
      'BG' => 'GIRO',
    );
    if ($ket == true) {
      return $uu[$ket];
    } else {
      return $uu;
    }
  }
}

if (!function_exists('is_status_giro')) {
  function is_status_giro($ket = false)
  {
    $uu = array(
      'OPEN' => 'OPEN',
      'INV' => 'INVOICE',
      'CAIR' => 'CAIR',
      'TOLAK' => 'TOLAK',
    );
    if ($ket == true) {
      return $uu[$ket];
    } else {
      return $uu;
    }
  }
}

if (!function_exists('is_filter_report_jual')) {
  function is_filter_report_jual($ket = false)
  {
    $uu = array(
      'by_customer' => 'Per Customer',
      'by_sales' => 'Per Sales',
    );
    if ($ket == true) {
      return $uu[$ket];
    } else {
      return $uu;
    }
  }
}

if (!function_exists('is_filter_detail_jual')) {
  function is_filter_detail_jual($ket = false)
  {
    $uu = array(
      'by_produk' => 'Per Produk',
      'by_customer' => 'Per Customer',
      'by_sales' => 'Per Sales',
    );
    if ($ket == true) {
      return $uu[$ket];
    } else {
      return $uu;
    }
  }
}


//=========================================================================================================================
//==================================================BY ARWANT==============================================================
//=========================================================================================================================

function getColsChar($colums)
{
  // Palleng by jester

  if ($colums > 26) {
    $modCols = floor($colums / 26);
    $ExCols = $modCols * 26;
    $totCols = $colums - $ExCols;

    if ($totCols == 0) {
      $modCols = $modCols - 1;
      $totCols += 26;
    }

    $lets1 = getLetColsLetter($modCols);
    $lets2 = getLetColsLetter($totCols);
    return $letsi = $lets1 . $lets2;
  } else {
    $lets = getLetColsLetter($colums);
    return $letsi = $lets;
  }
}

function getLetColsLetter($numbs)
{
  // Palleng by jester
  switch ($numbs) {
    case 1:
      $Chars = 'A';
      break;
    case 2:
      $Chars = 'B';
      break;
    case 3:
      $Chars = 'C';
      break;
    case 4:
      $Chars = 'D';
      break;
    case 5:
      $Chars = 'E';
      break;
    case 6:
      $Chars = 'F';
      break;
    case 7:
      $Chars = 'G';
      break;
    case 8:
      $Chars = 'H';
      break;
    case 9:
      $Chars = 'I';
      break;
    case 10:
      $Chars = 'J';
      break;
    case 11:
      $Chars = 'K';
      break;
    case 12:
      $Chars = 'L';
      break;
    case 13:
      $Chars = 'M';
      break;
    case 14:
      $Chars = 'N';
      break;
    case 15:
      $Chars = 'O';
      break;
    case 16:
      $Chars = 'P';
      break;
    case 17:
      $Chars = 'Q';
      break;
    case 18:
      $Chars = 'R';
      break;
    case 19:
      $Chars = 'S';
      break;
    case 20:
      $Chars = 'T';
      break;
    case 21:
      $Chars = 'U';
      break;
    case 22:
      $Chars = 'V';
      break;
    case 23:
      $Chars = 'W';
      break;
    case 24:
      $Chars = 'X';
      break;
    case 25:
      $Chars = 'Y';
      break;
    case 26:
      $Chars = 'Z';
      break;
  }

  return $Chars;
}

function akses_server()
{
  $Arr_Balik  = array(
    'hostname'  => '103.228.117.98',
    'hostuser'  => 'root',
    'hostpass'  => 'sentral2022**',
    'hostdb'    => 'origa_live2'
  );
  return $Arr_Balik;
}

function history($desc = NULL)
{
  $CI             = &get_instance();
  $path            = $CI->uri->segment(1);
  $data_session   = $CI->session->userdata('app_session');
  $userID          = $data_session['username'];
  $Date            = date('Y-m-d H:i:s');
  $IP_Address      = $CI->input->ip_address();

  $DataHistory    = array();
  $DataHistory['user_id']      = $userID;
  $DataHistory['path']        = $path;
  $DataHistory['description']  = $desc;
  $DataHistory['ip_address']  = $IP_Address;
  $DataHistory['created']      = $Date;

  $CI->db->insert('histories', $DataHistory);
}

function get_sum_qty_so($no_so)
{
  $CI     = &get_instance();
  $qHeader  = "SELECT SUM(qty_order) AS qty_order FROM sales_order_detail WHERE no_so = '" . $no_so . "' ";
  $restHeader  = $CI->db->query($qHeader)->result_array();
  $hasil    = (!empty($restHeader[0]['qty_order'])) ? $restHeader[0]['qty_order'] : 0;
  return $hasil;
}

function get_sum_qty_so_propose($no_so)
{
  $CI     = &get_instance();
  $qHeader  = "SELECT SUM(qty_propose) AS qty_order FROM sales_order_detail WHERE no_so = '" . $no_so . "' ";
  $restHeader  = $CI->db->query($qHeader)->result_array();
  $hasil    = (!empty($restHeader[0]['qty_order'])) ? $restHeader[0]['qty_order'] : 0;
  return $hasil;
}

function get_sum_planning($id_product)
{
  $CI     = &get_instance();
  $qHeader  = "SELECT SUM(qty) AS qty FROM produksi_planning_data WHERE product = '" . $id_product . "' ";
  $restHeader  = $CI->db->query($qHeader)->result_array();
  $hasil    = (!empty($restHeader[0]['qty'])) ? $restHeader[0]['qty'] : 0;
  return $hasil;
}

function get_sum_planning_new($tanggal, $id_product)
{
  $CI     = &get_instance();
  $qHeader  = "SELECT SUM(qty) AS qty FROM produksi_planning_data WHERE product = '" . $id_product . "' AND `date` = '" . $tanggal . "' ";
  $restHeader  = $CI->db->query($qHeader)->result_array();
  $hasil    = (!empty($restHeader[0]['qty'])) ? $restHeader[0]['qty'] : 0;
  return $hasil;
}

function get_costcenter()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' ORDER BY urut2 ASC")->result_array();
  return $query;
}

function get_costcenter_input_produksi()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' AND sts_warehouse='Y' ORDER BY urut2 ASC")->result_array();
  return $query;
}

function get_costcenter_report()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' AND urut <> '0' ORDER BY urut ASC")->result_array();
  return $query;
}

function get_product()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM ms_inventory_category2 ORDER BY id_category2 ASC")->result_array();
  return $query;
}

function get_product_bom()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT
                            	a.id_category2,
                            	a.nama
                            FROM
                            	ms_inventory_category2 a
                            	LEFT JOIN bom_hth_header b ON a.id_category2 = b.id_product WHERE b.id_product IS NULL
                            ORDER BY
                            	a.id_category2 ASC")->result_array();
  return $query;
}

function get_project()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM ms_inventory_category1 WHERE deleted='0' ORDER BY id_category1 ASC")->result_array();
  return $query;
}

function get_daycode()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM daycode ORDER BY id ASC")->result_array();
  return $query;
}

function get_material()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM ms_material ORDER BY nm_material ASC")->result_array();
  return $query;
}

function get_type_material_price()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM type_material_price")->result_array();
  return $query;
}

function get_warehouse()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM warehouse")->result_array();
  return $query;
}

function get_container()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM container")->result_array();
  return $query;
}

function get_sales_order()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT a.no_so, a.delivery_date, b.name_customer FROM sales_order_header a LEFT JOIN master_customer b ON a.code_cust=b.id_customer ORDER BY a.delivery_date ASC, b.name_customer ASC")->result_array();
  return $query;
}

function get_data_planning()
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM produksi_planning WHERE costcenter='CC2000012' AND sts_plan='N' ORDER BY date_awal ASC")->result_array();
  return $query;
}

function get_costcenter_antrian_wip($product, $costcenter)
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND costcenter='" . $costcenter . "' LIMIT 1 ")->result();
  $antrial = (!empty($query[0]->urut) and $query[0]->costcenter != 'CC2000012') ? intval($query[0]->urut) : 0;
  return $antrial;
}

function get_antrian_wip($product, $costcenter)
{
  $CI       = &get_instance();
  // $query	  = $CI->db->query("SELECT * FROM cycletime_fast WHERE id_product='".$product."' AND costcenter='".$costcenter."' LIMIT 1 ")->result();
  // $antrialx  = (!empty($query[0]->urut) AND $query[0]->costcenter != 'CC2000012')?intval($query[0]->urut):0;
  // $urut     = sprintf('%02s',$antrialx - 1);
  //
  // $query2	  = $CI->db->query("SELECT * FROM cycletime_fast WHERE id_product='".$product."' AND urut='".$urut."' LIMIT 1 ")->result();
  // $antrial  = (!empty($query2[0]->costcenter))?$query2[0]->costcenter:0;
  $antrial = get_before_costcenter_warehouse($product, $costcenter);
  $sql = "
          SELECT
            a.qty_stock AS qty
          FROM
            warehouse_product a
          WHERE
            a.category = 'order'
            AND a.id_product <> '0'
            AND a.costcenter = '" . $antrial . "'
            AND a.id_product = '" . $product . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $antrianx = (!empty($sisa[0]->qty)) ? $sisa[0]->qty : 0;
  return $antrianx;
}

function get_stock_wip($product, $costcenter)
{
  $CI       = &get_instance();
  $sql = "
          SELECT
            a.qty_stock AS qty
          FROM
            warehouse_product a
          WHERE
            a.category = 'order'
            AND a.id_product <> '0'
            AND a.costcenter = '" . $costcenter . "'
            AND a.id_product = '" . $product . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $antrianx = (!empty($sisa[0]->qty)) ? $sisa[0]->qty : 0;
  return $antrianx;
}

function get_total_order_selesai($product)
{
  $CI       = &get_instance();

  $sql = "
          SELECT
            COUNT( a.id_product ) AS qty
          FROM
            report_produksi_daily_detail a
            LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
          WHERE
            a.id_product <> '0'
            AND b.id_costcenter = 'CC2000001'
            AND a.id_product = '" . $product . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->qty)) ? $sisa[0]->qty : 0;
  return $total;
}

function get_total_order($product)
{
  $CI       = &get_instance();

  $sql = "
          SELECT
            a.qty_order AS qty
          FROM
            warehouse_product a
          WHERE
            a.id_product <> '0'
            AND a.category = 'product'
            AND a.id_product = '" . $product . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->qty)) ? $sisa[0]->qty : 0;
  return $total;
}

function get_stock($product)
{
  $CI       = &get_instance();

  $sql = "
          SELECT
            a.qty_stock AS qty
          FROM
            warehouse_product a
          WHERE
            a.id_product <> '0'
            AND a.category = 'product'
            AND a.id_product = '" . $product . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->qty)) ? $sisa[0]->qty : 0;
  return $total;
}

function get_stock_material($material, $gudang)
{
  $CI       = &get_instance();

  $sql = "
          SELECT
            a.qty_stock AS qty
          FROM
            warehouse_stock a
          WHERE
            a.id_material = '" . $material . "'
            AND a.id_gudang = '" . $gudang . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->qty)) ? $sisa[0]->qty : 0;
  return $total;
}

function get_stock_material_packing($material, $gudang)
{
  $CI       = &get_instance();
  $stock    = get_stock_material($material, $gudang);
  $konversi = get_konversi($material);
  $pack = 0;
  if (!empty($stock) and $stock > 0 and $konversi > 0) {
    $pack = $stock / $konversi;
  }
  return $pack;
}

function get_total_stock($product)
{
  $CI       = &get_instance();

  $sql = "
          SELECT
            COUNT( a.id_product ) AS qty
          FROM
            report_produksi_daily_detail a
            LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
          WHERE
            a.ket = 'good'
            AND a.id_product <> '0'
            AND b.id_costcenter = 'CC2000001'
            AND a.id_product = '" . $product . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->qty)) ? $sisa[0]->qty : 0;
  return $total;
}

function get_name_field($table, $flied_name, $flied_whare, $flied_value)
{
  $CI         = &get_instance();
  $qHeader    = "SELECT $flied_name FROM $table WHERE $flied_whare = '" . $flied_value . "' ";
  $restHeader  = $CI->db->query($qHeader)->result_array();
  $hasil      = (!empty($restHeader[0][$flied_name])) ? $restHeader[0][$flied_name] : 'not found';
  return $hasil;
}

function get_date_delivery()
{
  $CI       = &get_instance();

  $sql = "SELECT * FROM list_date_delivery WHERE qty_sisa > 0";
  $result  = $CI->db->query($sql)->result_array();
  return $result;
}

function get_24($value)
{
  $time = ($value == '00:00:00') ? '24:00:00' : $value;
  return $time;
}

function get_qty_oke($tanggal, $product, $costcenter)
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT
                              COUNT(*) AS qtyx
                            FROM
                              report_produksi_daily_detail a
                              LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                            WHERE
                              a.ket = 'good'
                              AND a.id_product = '" . $product . "'
                              AND b.id_costcenter = '" . $costcenter . "'
                            ")->result();
  $antrial = (!empty($query[0]->qtyx)) ? $query[0]->qtyx : 0;
  return $antrial;
}

function get_qty_oke_new($tanggal, $product, $costcenter)
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT
                              COUNT(*) AS qtyx
                            FROM
                              report_produksi_daily_detail a
                              LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                            WHERE
                              a.ket = 'good'
                              AND a.id_product = '" . $product . "'
                              AND b.id_costcenter = '" . $costcenter . "'
                              AND a.tanggal_produksi = '" . $tanggal . "'
                            ")->result();
  $antrial = (!empty($query[0]->qtyx)) ? $query[0]->qtyx : 0;
  return $antrial;
}

function get_qty_order($product)
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT * FROM warehouse_product WHERE id_product='" . $product . "' AND category='product' LIMIT 1 ")->result();
  $antrial = (!empty($query[0]->qty_order)) ? $query[0]->qty_order : 0;
  return $antrial;
}

function get_qty_rusak($tanggal, $product, $costcenter)
{
  $CI   = &get_instance();
  $query  = $CI->db->query("SELECT
                              COUNT(*) AS qtyx
                            FROM
                              report_produksi_daily_detail a
                              LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                            WHERE
                              a.ket = 'bad'
                              AND a.id_product = '" . $product . "'
                              AND b.id_costcenter = '" . $costcenter . "'
                            ")->result();
  $antrial = (!empty($query[0]->qtyx)) ? $query[0]->qtyx : 0;
  return $antrial;
}

function get_project_name($product)
{
  $CI     = &get_instance();
  $qHeader  = "SELECT b.nama FROM ms_inventory_category2 a LEFT JOIN ms_inventory_category1 b ON a.id_category1=b.id_category1 WHERE a.id_category2 = '" . $product . "' LIMIT 1";
  $restHeader  = $CI->db->query($qHeader)->result_array();
  $hasil    = (!empty($restHeader[0]['nama'])) ? $restHeader[0]['nama'] : 'get error';
  return $hasil;
}

function get_qty_order_so($product, $costcenter, $date_akhir, $date_awal)
{
  $CI   = &get_instance();

  $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));
  // $date_now   = date('Y-m-d', strtotime('2020-08-04'));

  $q_max      = "SELECT MAX(date_akhir) AS date_akhir FROM produksi_planning WHERE costcenter='" . $costcenter . "' LIMIT 1 ";
  $max_date   = $CI->db->query($q_max)->result();

  $datex      = (!empty($max_date[0]->date_akhir)) ? $max_date[0]->date_akhir : $date_now;
  $date       = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
  if (!empty($date_awal)) {
    $date       = date('Y-m-d', strtotime($date_awal));
  }

  $product    = $CI->db->query("SELECT product, SUM(qty_order) AS qty_order, delivery_date FROM sales_order_detail WHERE product='" . $product . "' AND delivery_date BETWEEN '" . $date . "' AND '" . $date_akhir . "' GROUP BY product LIMIT 1 ")->result();
  $qty_order = (!empty($product[0]->qty_order)) ? $product[0]->qty_order : 0;
  return $qty_order;
}

function get_sts_warehouse($costcenter)
{
  $CI           = &get_instance();
  $qHeader      = "SELECT a.sts_warehouse FROM ms_costcenter a WHERE a.id_costcenter = '" . $costcenter . "' LIMIT 1";
  $restHeader    = $CI->db->query($qHeader)->result_array();
  $hasil         = (!empty($restHeader[0]['sts_warehouse'])) ? $restHeader[0]['sts_warehouse'] : 'get error';
  return $hasil;
}

function get_before_costcenter_warehouse($product, $costcenter)
{
  $CI           = &get_instance();

  $qHeader      = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND costcenter='" . $costcenter . "' LIMIT 1 ";
  $query        = $CI->db->query($qHeader)->result();
  $antrialx     = (!empty($query[0]->urut) and $query[0]->costcenter != 'CC2000012') ? intval($query[0]->urut) : 0;
  $urut         = sprintf('%02s', $antrialx - 1);
  //filter pertama
  $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
  $query2           = $CI->db->query($sql2)->result();
  $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

  if (get_sts_warehouse($bef_costcenter) == 'N') {
    $urut         = sprintf('%02s', $antrialx - 2);
    //filter kedua
    $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
    $query2           = $CI->db->query($sql2)->result();
    $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

    if (get_sts_warehouse($bef_costcenter) == 'N') {
      $urut         = sprintf('%02s', $antrialx - 3);
      //filter ketiga
      $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
      $query2           = $CI->db->query($sql2)->result();
      $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

      if (get_sts_warehouse($bef_costcenter) == 'N') {
        $urut         = sprintf('%02s', $antrialx - 4);
        //filter keempat
        $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
        $query2           = $CI->db->query($sql2)->result();
        $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;
      }
    }
  }

  return $bef_costcenter;
}

function get_next_costcenter($product, $costcenter)
{
  $CI           = &get_instance();

  $qHeader      = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND costcenter='" . $costcenter . "' LIMIT 1 ";
  $query        = $CI->db->query($qHeader)->result();
  $antrialx     = (!empty($query[0]->urut)) ? intval($query[0]->urut) : 0;
  $urut         = sprintf('%02s', $antrialx + 1);
  //filter pertama
  $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
  $query2           = $CI->db->query($sql2)->result();
  $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

  if (get_sts_warehouse($bef_costcenter) == 'N') {
    $urut         = sprintf('%02s', $antrialx + 2);
    //filter kedua
    $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
    $query2           = $CI->db->query($sql2)->result();
    $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

    if (get_sts_warehouse($bef_costcenter) == 'N') {
      $urut         = sprintf('%02s', $antrialx + 3);
      //filter ketiga
      $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
      $query2           = $CI->db->query($sql2)->result();
      $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

      if (get_sts_warehouse($bef_costcenter) == 'N') {
        $urut         = sprintf('%02s', $antrialx + 4);
        //filter keempat
        $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
        $query2           = $CI->db->query($sql2)->result();
        $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;
      }
    }
  }

  return $bef_costcenter;
}

function get_last_costcenter_warehouse($product)
{
  $CI           = &get_instance();

  $qHeader      = "SELECT MAX(urut) AS urut FROM cycletime_fast WHERE id_product='" . $product . "' LIMIT 1 ";
  $query        = $CI->db->query($qHeader)->result();
  $antrialx     = (!empty($query[0]->urut)) ? intval($query[0]->urut) : 0;
  $urut         = sprintf('%02s', $antrialx - 1);
  //filter pertama
  $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
  $query2           = $CI->db->query($sql2)->result();
  $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

  if (get_sts_warehouse($bef_costcenter) == 'N') {
    $urut         = sprintf('%02s', $antrialx - 2);
    //filter kedua
    $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
    $query2           = $CI->db->query($sql2)->result();
    $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

    if (get_sts_warehouse($bef_costcenter) == 'N') {
      $urut         = sprintf('%02s', $antrialx - 3);
      //filter ketiga
      $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
      $query2           = $CI->db->query($sql2)->result();
      $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;

      if (get_sts_warehouse($bef_costcenter) == 'N') {
        $urut         = sprintf('%02s', $antrialx - 4);
        //filter keempat
        $sql2            = "SELECT * FROM cycletime_fast WHERE id_product='" . $product . "' AND urut='" . $urut . "' LIMIT 1 ";
        $query2           = $CI->db->query($sql2)->result();
        $bef_costcenter  = (!empty($query2[0]->costcenter)) ? $query2[0]->costcenter : 0;
      }
    }
  }

  return $bef_costcenter;
}

function get_total_time_ct($product)
{
  $CI       = &get_instance();
  $sql = "
          SELECT
            SUM(a.man_hours) AS total
          FROM
            cycletime_full a
          WHERE
            a.id_product = '" . $product . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->total)) ? $sisa[0]->total : 0;
  return $total;
}

function get_total_time_cycletime()
{
  $CI       = &get_instance();
  $sql = "SELECT
            b.id_time,
            b.id_product,
            b.no_bom,
            b.total_ct_setting,
            b.total_ct_produksi,
            b.moq,
            MAX(qty_mp) AS qty_mp,
            SUM(a.cycletime) AS ct_machine,
            SUM(a.cycletime * qty_mp) AS ct_manpower
          FROM
            cycletime_detail_detail a
            LEFT JOIN cycletime_header b ON a.id_time = b.id_time
          WHERE
            b.deleted_date IS NULL
          GROUP BY b.id_time
            ";
  $result  = $CI->db->query($sql)->result_array();

  $Arrback = [];
  foreach ($result as $key => $value) {
    $KEY_UNIQ = $value['id_product']."-".$value['no_bom'];

    $Arrback[$KEY_UNIQ]['id_product'] = $value['id_product'];
    $Arrback[$KEY_UNIQ]['ct_machine'] = $value['ct_machine'];
    $Arrback[$KEY_UNIQ]['ct_manpower'] = $value['ct_manpower'];
    $Arrback[$KEY_UNIQ]['total_ct_setting'] = $value['total_ct_setting'];
    $Arrback[$KEY_UNIQ]['total_ct_produksi'] = $value['total_ct_produksi'];
    $Arrback[$KEY_UNIQ]['moq'] = $value['moq'];
    $Arrback[$KEY_UNIQ]['qty_mp'] = $value['qty_mp'];
  }
  return $Arrback;
}

function get_total_time_cycletime_bom_std()
{
  $CI       = &get_instance();
  $sql = "SELECT
            b.id_time,
            b.id_product,
            b.no_bom,
            b.total_ct_setting,
            b.total_ct_produksi,
            b.moq,
            MAX(qty_mp) AS qty_mp,
            SUM(a.cycletime) AS ct_machine,
            SUM(a.cycletime * qty_mp) AS ct_manpower
          FROM
            cycletime_detail_detail a
            LEFT JOIN cycletime_header b ON a.id_time = b.id_time
          WHERE
            b.deleted_date IS NULL AND a.tipe = 'production'
          GROUP BY b.id_time
            ";
  $result  = $CI->db->query($sql)->result_array();

  $Arrback = [];
  foreach ($result as $key => $value) {
    $KEY_UNIQ = $value['id_product']."-".$value['no_bom'];

    $Arrback[$KEY_UNIQ]['id_product'] = $value['id_product'];
    $Arrback[$KEY_UNIQ]['ct_machine'] = $value['ct_machine'];
    $Arrback[$KEY_UNIQ]['ct_manpower'] = $value['ct_manpower'];
    $Arrback[$KEY_UNIQ]['total_ct_setting'] = $value['total_ct_setting'];
    $Arrback[$KEY_UNIQ]['total_ct_produksi'] = $value['total_ct_produksi'];
    $Arrback[$KEY_UNIQ]['moq'] = $value['moq'];
    $Arrback[$KEY_UNIQ]['qty_mp'] = $value['qty_mp'];
  }
  return $Arrback;
}

function get_balance($product, $cust)
{
  $CI       = &get_instance();
  $balance  = $CI->db->query("SELECT qty_kurang FROM search_balance_so WHERE product = '" . $product . "' AND code_cust='" . $cust . "' LIMIT 1")->result();
  $total = (!empty($balance[0]->qty_kurang)) ? $balance[0]->qty_kurang : 0;
  return $total;
}

function get_plan_mat($material, $tahun, $bulan)
{
  $CI       = &get_instance();
  $sql = "
          SELECT
            SUM(a.weight) AS weight
          FROM
            material_planning_footer a
            LEFT JOIN material_planning b ON a.no_plan=b.no_plan
          WHERE
            (b.tahun='" . $tahun . "' AND b.bulan='" . $bulan . "')
            AND a.category='sum'
            AND a.material='" . $material . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->weight)) ? $sisa[0]->weight : 0;
  return $total;
}

function get_edit_plan_mat($kode_req, $material)
{
  $CI       = &get_instance();
  $sql = "
          SELECT
            a.weight AS weight
          FROM
            material_request_detail a
          WHERE
            a.no_req='" . $kode_req . "'
            AND a.material='" . $material . "'
            ";
  $sisa  = $CI->db->query($sql)->result();
  $total = (!empty($sisa[0]->weight)) ? $sisa[0]->weight : 0;
  return $total;
}

function get_konversi($material)
{
  $CI       = &get_instance();
  $sql    = "SELECT a.konversi FROM ms_material a WHERE  a.code_material='" . $material . "'";
  $sisa    = $CI->db->query($sql)->result();
  $total  = (!empty($sisa[0]->konversi)) ? $sisa[0]->konversi : 1;
  return $total;
}

function get_count_daycode_double($daycode, $product, $costcenter)
{
  $CI       = &get_instance();
  $sql    = "SELECT a.jumlah_double FROM cek_count_double_daycode a WHERE  a.code='" . $daycode . "' AND  a.id_product='" . $product . "' AND  a.id_costcenter='" . $costcenter . "' LIMIT 1";
  $sisa    = $CI->db->query($sql)->result();
  $jumlah_double  = (!empty($sisa[0]->jumlah_double)) ? $sisa[0]->jumlah_double : 0;
  return $jumlah_double;
}

function get_dimensi($product)
{
  $CI       = &get_instance();
  $sql    = "SELECT a.* FROM ms_inventory_category2 a WHERE  a.id_category2='" . $product . "'";
  $sisa    = $CI->db->query($sql)->result();
  $total  = (!empty($sisa)) ? number_format($sisa[0]->length) . " x " . number_format($sisa[0]->high) . " x " . number_format($sisa[0]->wide) : '-';
  return $total;
}

function tgl_indo($tanggal)
{
  $bulan = array(
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
  );
  $pecahkan = explode('-', $tanggal);

  return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

function get_total_time_cycletime_assembly()
{
  $CI       = &get_instance();
  $sql = "SELECT
            b.id_time,
            b.id_product,
            b.no_bom,
            c.category,
            MAX(qty_mp) AS qty_mp,
            SUM(a.cycletime) AS ct_cycletime,
            SUM(a.cycletime * qty_mp) AS ct_manpower
          FROM
            cycletime_custom_detail_detail a
            LEFT JOIN cycletime_custom_header b ON a.id_time = b.id_time
            LEFT JOIN cycletime_custom_detail_header c ON a.id_costcenter = c.id_costcenter
          WHERE
            b.deleted_date IS NULL
          GROUP BY b.id_time, c.category
            ";
  $result  = $CI->db->query($sql)->result_array();

  $Arrback = [];
  foreach ($result as $key => $value) {
    $KEY_UNIQ = $value['id_product']."-".$value['no_bom']."-".$value['category'];

    $Arrback[$KEY_UNIQ]['id_time']      = $value['id_time'];
    $Arrback[$KEY_UNIQ]['id_product']   = $value['id_product'];
    $Arrback[$KEY_UNIQ]['ct_cycletime'] = $value['ct_cycletime'];
    $Arrback[$KEY_UNIQ]['ct_manpower']  = $value['ct_manpower'];
    $Arrback[$KEY_UNIQ]['qty_mp']       = $value['qty_mp'];
  }
  return $Arrback;
}

function get_machine_product_assembly(){
	$CI = &get_instance();
	$listGetCategory = $CI->db
		->select('a.id_product,b.machine,c.category')
		->group_by('a.id_product,c.category')
		->join('cycletime_custom_detail_header c', 'a.id_time=c.id_time', 'join')
		->join('cycletime_custom_detail_detail b', 'b.id_costcenter=c.id_costcenter', 'join')
		->get_where('cycletime_custom_header a', array('a.deleted_date' => NULL, 'b.machine !=' => NULL, 'b.machine !=' => '0'))
		->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
    $KEY_UNIQ = $value['id_product']."-".$value['category'];
		$ArrGetCategory[$KEY_UNIQ] 	= $value['machine'];
	}
	return $ArrGetCategory;
}

function get_mold_product_assembly(){
	$CI = &get_instance();
	$listGetCategory = $CI->db
		->select('a.id_product,b.mould,c.category')
		->group_by('a.id_product,c.category')
		->join('cycletime_custom_detail_header c', 'a.id_time=c.id_time', 'join')
		->join('cycletime_custom_detail_detail b', 'b.id_costcenter=c.id_costcenter', 'join')
		->get_where('cycletime_custom_header a', array('a.deleted_date' => NULL, 'b.mould !=' => NULL, 'b.mould !=' => '0'))
		->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
    $KEY_UNIQ = $value['id_product']."-".$value['category'];
		$ArrGetCategory[$KEY_UNIQ] 	= $value['mould'];
	}
	return $ArrGetCategory;
}
