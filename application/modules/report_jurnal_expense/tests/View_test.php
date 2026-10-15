<?php
function has_permission($key){return $key==='Report_Jurnal_Expense.View';}
function base_url($path){return 'http://localhost/'.$path;}
function site_url($path){return 'http://localhost/'.$path;}
$checks=0;
function check($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
$path=dirname(__DIR__).'/views/';
if(($argv[1]??'')==='--render'){$list_no_transaksi=[['no_transaksi'=>'<img src=x>']];$list_company=[['id_company'=>'1','nm_company'=>'<svg>']];include $path.'index.php';exit;}
$old=file_get_contents(dirname(__DIR__,2).'/report_jurnal_payment/views/index.php');$new=file_get_contents($path.'index.php');
preg_match('/<style>(.*?)<\/style>/s',$old,$a);preg_match('/<style>(.*?)<\/style>/s',$new,$b);check($a[1]===$b[1],'list CSS identical');
$old=file_get_contents(dirname(__DIR__,2).'/report_jurnal_payment/views/view_jurnal.php');$new=file_get_contents($path.'view_jurnal.php');
preg_match('/<style>(.*?)<\/style>/s',$old,$a);preg_match('/<style>(.*?)<\/style>/s',$new,$b);check($a[1]===$b[1],'detail CSS identical');
$list_no_transaksi=[['no_transaksi'=>'<img src=x>']];$list_company=[['id_company'=>'1','nm_company'=>'<svg>']];ob_start();include $path.'index.php';$html=ob_get_clean();
check(strpos($html,'<img src=x>')===false&&strpos($html,'&lt;img src=x&gt;')!==false,'filter options escaped');
check(strpos($html,'View Report Jurnal Expense')!==false&&strpos($html,'save_posting_jurnal')===false,'report name/read-only modal');
preg_match('/<table id="table_jurnal_payment".*?<thead>(.*?)<\/thead>/s',$html,$heads);check(substr_count($heads[1],'<th ')===7,'seven report columns');
$data_jurnal=[(object)['tgl_jurnal'=>'2026-10-14','jenis_transaksi'=>'Expense Report Consultant','coa'=>'<img>','nm_coa'=>'<svg>','keterangan'=>'</textarea><script>bad</script> {REF:<iframe>}','no_transaksi'=>'ER-1','debit'=>'100.25','kredit'=>'100.25'],(object)['debit'=>0,'kredit'=>0]];
ob_start();include $path.'view_jurnal.php';$html=ob_get_clean();check(strpos($html,'<script>')===false&&strpos($html,'&lt;script&gt;')!==false&&strpos($html,'<iframe>')===false,'detail/reference escaped');
check(substr_count($html,'100.25')===4,'details and totals retain cents');
check(strpos($html,'readonly')!==false&&strpos($html,'name="')===false,'detail cannot submit edited journals');
$data_jurnal=[];ob_start();include $path.'view_jurnal.php';$html=ob_get_clean();check(strpos($html,'Tidak ada rincian ayat jurnal.')!==false,'empty details');
$list_jurnal=[['no_transaksi'=>'<script>bad</script>','no_pengajuan'=>'<svg>','kategori_payment'=>'<img>','tgl_jurnal'=>'2026-10-14','nm_company'=>'<iframe>']];
ob_start();include $path.'export_excel.php';$html=ob_get_clean();check(strpos($html,'<script>')===false&&strpos($html,'&lt;script&gt;')!==false,'export escaped');
check(strpos($html,'Report Jurnal Expense')!==false,'export name');
$list_jurnal=[];ob_start();include $path.'export_excel.php';$html=ob_get_clean();check(strpos($html,'Tidak ada data jurnal expense')!==false,'empty export');
echo "PASS Report Jurnal Expense views: {$checks} assertions\n";
