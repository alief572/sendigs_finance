<?php
define('BASEPATH',__DIR__); define('FCPATH',dirname(__DIR__,4).'/');
require dirname(__DIR__).'/libraries/Invoice_vuca.php';
function base_url($path=''){return '/'.$path;}
set_error_handler(function($severity,$message,$file,$line){throw new ErrorException($message,0,$severity,$file,$line);});
class VucaRender {
    function render($view,$data){extract($data);ob_start();include dirname(__DIR__).'/views/'.$view.'.php';return ob_get_clean();}
}
$checks=0;
function renderCheck($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
$accounts=array();$i=1;foreach(Invoice_consulting::revenues() as $role=>$label)$accounts[$role]=array('code'=>'4101-01-0'.$i++,'label'=>$label,'name'=>'<script>alert(1)</script>');
$actual=(object)array('id'=>'A1','nominal_payment'=>'1000000.00','nm_customer'=>'<script>alert(1)</script>','address'=>"A & B\nAlamat",'id_spk_penawaran'=>'SPK"1','term_payment'=>'TOP','persen_payment'=>'100','desc_payment'=>'Selesai <img src=x onerror=alert(1)>');
$journals=array();foreach(array('1102-01-01','2104-01-07','1106-01-05','') as $code)$journals[]=array('coa'=>$code,'nm_coa'=>'<script>alert(1)</script>','id_company'=>'4','nm_company'=>'Vuca','tgl_jurnal'=>'2026-10-21','debit'=>$code==='1102-01-01'?995000:($code==='1106-01-05'?5000:0),'kredit'=>$code===''?1000000:0);
$renderer=new VucaRender;
$html=$renderer->render('add_invoice_vuca',array('data_actual'=>$actual,'accounts'=>$accounts,'journals'=>$journals,'preview_no_invoice'=>'<test>','error'=>null));
libxml_use_internal_errors(true);
$dom=new DOMDocument;$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new DOMXPath($dom);
renderCheck(strpos($html,'<script>alert(1)</script>')===false && strpos($html,'<img src=x')===false,'All customer, TOP and COA content escaped');
renderCheck($xpath->query('//*[@id="vuca-form" and @action="/invoicing/save_invoice_vuca"]')->length===1,'Existing save endpoint');
renderCheck($xpath->query('//select[@name="sales_role" and @required]/option')->length===5,'Four categories and empty placeholder');
renderCheck($xpath->query('//select[@name="tax_treatment" or @name="ppn_persen"]')->length===0,'No PPN selection');
foreach(array('nomor_po','nomor_faktur') as $name)renderCheck($xpath->query('//input[@name="'.$name.'" and @required]')->length===1,'Required document '.$name);
foreach(array('tanggal_invoice','nomor_invoice') as $name)renderCheck($xpath->query('//input[@name="'.$name.'" and @readonly]')->length===1,'Readonly '.$name);
foreach(array('sales'=>'1,000,000.00','pph'=>'- 5,000.00','net'=>'995,000.00') as $key=>$expected)renderCheck($xpath->query('//*[@data-total="'.$key.'"]')->item(0)->textContent===$expected,'Unchanged Vuca '.$key);
foreach(array('pajak'=>110000,'total_akhir'=>1110000,'pph_jurnal'=>5000,'total_akhir_jurnal'=>995000,'dpp_nilai_lain'=>1000000*11/12) as $name=>$expected)renderCheck(abs((float)$xpath->query('//input[@name="'.$name.'"]')->item(0)->getAttribute('value')-$expected)<0.00001,'Legacy hidden field '.$name);
renderCheck($xpath->query('//*[@id="vuca-journals" and @hidden]')->length===1,'Journal hidden until a category is selected');
renderCheck($xpath->query('//*[@role="region" and @tabindex="0"]')->length===2,'Keyboard accessible table scroll regions');
foreach($xpath->query('//label[@for]') as $label)renderCheck($xpath->query('//*[@id="'.$label->getAttribute('for').'"]')->length===1,'Labels target existing controls');
renderCheck(strpos($html,'https://cdn')===false && strpos($html,'save-feedback.js')!==false,'Local assets and shared feedback');
$error=$renderer->render('add_invoice_vuca',array('error'=>'Mapping <bad> tidak valid'));
renderCheck(strpos($error,'Mapping &lt;bad&gt; tidak valid')!==false && strpos($error,'<form')===false,'Configuration error stops unusable form');
echo "PASS: $checks Vuca render checks\n";
