<?php

date_default_timezone_set("Asia/Bangkok");
include_once 'function_connect.php';

//echo "<pre>";print_r(file_get_contents('function_connec.php'));exit;
//echo"masuk bro".$_SERVER['DOCUMENT_ROOT'];
$db1 				= new database_ORI();
$koneksi 			= $db1->connect();
//echo $db1;exit;
$date = date('y-m-d');
// $date = '2021-08-02';
// echo $date;

for($a=1;$a<=20;$a++){
  $urut	= sprintf('%02s',$a);
  $code = str_replace('-','/', $date).'/'.$urut;
  $sqlInsertStatus = "INSERT INTO daycode(`code`,`tanggal`,`urut`,`created_by`, `created_date`)
                      VALUE ('".$code."','".$date."','".$a."','system','".date('Y-m-d H:i:s')."')";
  $koneksi->query($sqlInsertStatus);
  echo $urut." Success.<br>";
}

for($a=91;$a<=99;$a++){
  $urut	= sprintf('%02s',$a);
  $code = str_replace('-','/', $date).'/'.$urut;
  $sqlInsertStatus = "INSERT INTO daycode(`code`,`tanggal`,`urut`,`created_by`, `created_date`)
                      VALUE ('".$code."','".$date."','".$a."','system','".date('Y-m-d H:i:s')."')";
  $koneksi->query($sqlInsertStatus);
  echo $urut." Success.<br>";
}

  // $sqlInsertStatus = "INSERT INTO laporan_status
  //                             (`date`,`status`,insert_by, insert_date)
  //                             VALUE
  //                             ('".$date."','EMPTY','system','".date('Y-m-d H:i:s')."')
  //                         ";
  // $koneksi->query($sqlInsertStatus);
?>
