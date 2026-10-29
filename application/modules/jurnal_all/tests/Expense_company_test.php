<?php
require __DIR__.'/Pengembalian_kasbon_test.php';
$detail=$model->detail($emptyCompany,false);
check(!$detail['can_post']&&strpos($detail['header']['company_error'],'Company')!==false,'empty company preview is readable but not postable');
$page=$model->listing(['tab'=>'EXP','q'=>'ER-RETURN-NOCOMPANY','year'=>2026]);
check($page['data'][0]['can_post']===false&&!empty($page['data'][0]['post_block_reason']),'pending list retains blocked row and reason');
$posted=false;$detail['header']['company_error']='<script>company</script>';
ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();
check(strpos($html,'&lt;script&gt;company&lt;/script&gt;')!==false&&strpos($html,'<script>company')===false,'company block reason escaped');
$detail['header']['company_error']='';$detail['header']['accounting_target']='accounting_vuca';
ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();
check(strpos($html,'Tujuan TRAS')!==false&&strpos($html,'VUCA')!==false,'preview exposes validated target');

class Expense_gl_loader {
    public $groups=[];
    public function database($group,$return=true){$this->groups[]=$group;return new Expense_gl_db();}
}
class Expense_gl_db {public $conn_id=true;public function table_exists($table){return false;}}
$companyRuntime=(object)['load'=>new Expense_gl_loader()];
function &get_instance(){global $companyRuntime;return $companyRuntime;}
$reader=new ReflectionMethod('Jurnal_all_model','resolveGlMetadata');$reader->setAccessible(true);
foreach(['1'=>'accounting_vuca','4'=>'accounting_vuca','3'=>'accounting_sustain','6'=>'accounting_sustain','7'=>'accounting_stm'] as $company=>$target){
    $m=new Jurnal_all_model();$m->db=$db;
    $rows=[['source'=>'EXP','no_transaksi'=>'ER-META','id_company'=>$company,'source_numbers'=>'']];
    $reader->invokeArgs($m,[&$rows]);
    check(end($companyRuntime->load->groups)===$target,'report fallback uses Expense target '.$company);
}
echo "PASS Jurnal All Expense company checks\n";
