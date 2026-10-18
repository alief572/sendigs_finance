<?php
define('BASEPATH', dirname(__DIR__,4).'/system/');
define('FCPATH', dirname(__DIR__,4).'/');
require dirname(__DIR__).'/libraries/Invoice_consulting.php';
function base_url($path='') { return '/'.$path; }
function has_permission($permission) { return !in_array($permission,$GLOBALS['denied']??[],true); }
function verifyUi($condition,$message) { $GLOBALS['checks']++; if(!$condition)throw new RuntimeException($message); }
class UiTemplate {
    function render($name,$data) { extract($data); ob_start(); include dirname(__DIR__).'/views/'.$name.'.php'; return ob_get_clean(); }
}
function htmlQuery($html) {
    $dom=new DOMDocument; @$dom->loadHTML('<?xml encoding="UTF-8">'.$html); return new DOMXPath($dom);
}
$GLOBALS['checks']=0;
$renderer=new UiTemplate;
$accounts=[];
foreach(Invoice_consulting::roles() as $role=>$name)$accounts[$role]=['code'=>'4101-01-01','name'=>$name];
$snapshot=['top'=>['term_payment'=>'TOP uji','persen_payment'=>30,'desc_payment'=>'Deskripsi uji <script>alert(1)</script>'],'sales_role'=>'sales_consulting','accounts'=>$accounts,'owner'=>['id'=>'7','nm_company'=>'STM']];
$invoice=['id'=>'TEST-UI','id_detail_plan_tagih'=>'TOP-UI','no_invoice'=>'001/STM/2026','id_spk_penawaran'=>'SPK-UI','tanggal_invoice'=>'2026-10-18','no_po'=>'PO-UI','no_faktur'=>'FP-UI','nm_customer'=>'Customer uji <script>alert(1)</script>','address'=>'Alamat uji','total_nominal'=>'1000.55','tax_treatment'=>'WAPU'];
$base=['invoice'=>$invoice,'snapshot'=>$snapshot,'mode'=>'add','journals'=>[],'source_fingerprint'=>'source-ui','invoice_fingerprint'=>'invoice-ui','error'=>null];
foreach(['add','edit','view'] as $mode)foreach(['PKP','WAPU','KB'] as $tax) {
    $data=$base;$data['mode']=$mode;$data['invoice']['tax_treatment']=$tax;
    $html=$renderer->render('consulting_invoice',$data);$xpath=htmlQuery($html);
    verifyUi(strpos($html,'<script>alert(1)</script>')===false,'Invoice escaping');
    verifyUi($xpath->query('//*[@id="consulting-form"]')->length===1,'Invoice form retained');
    foreach(['id_detail_plan_tagih','id_invoicing','source_fingerprint','invoice_fingerprint','nomor_po','nomor_faktur','tax_treatment','tanggal_invoice'] as $name)verifyUi($xpath->query('//*[@name="'.$name.'"]')->length===1,'Field retained '.$name);
    $totals=Invoice_consulting::totals('1000.55',$tax);
    foreach(['sales','other','ppn','gross','receivable'] as $key)verifyUi($xpath->query('//*[@data-total="'.$key.'"]')->item(0)->textContent===number_format($totals[$key]/100,2),'Saved summary '.$mode.' '.$tax.' '.$key);
    verifyUi($xpath->query('//*[@id="consulting-save"]')->length===($mode==='view'?0:1),'Read-only save unavailable');
    verifyUi($xpath->query('//script[contains(@src,"consulting-form.js")]')->length===($mode==='view'?0:1),'Client only on editable forms');
    verifyUi($xpath->query('//*[@id="consulting-tax"]/@disabled')->length===($mode==='view'?1:0),'Tax disabled on view');
    verifyUi($xpath->query('//*[@id="consulting-po"]/@readonly')->length===($mode==='view'?1:0),'PO read-only on view');
    verifyUi($xpath->query('//*[@id="consulting-journals"]//td[@colspan="7"]')->length===1,'Preview empty state spans columns');
}
$data=$base;$data['invoice']['tax_treatment']='';$data['snapshot']['sales_role']='';
$html=$renderer->render('consulting_invoice',$data);$xpath=htmlQuery($html);
verifyUi($xpath->query('//*[@data-total="sales"]')->item(0)->textContent==='1,000.55','Initial TOP visible without tax');
verifyUi($xpath->query('//*[@data-total="gross"]')->item(0)->textContent==='-','No gross before tax');
$settings=['companies'=>[['id'=>'7','nm_company'=>'STM <script>alert(1)</script>']],'source_id'=>'7','owner_id'=>'7','mapping'=>['accounts'=>json_encode(array_fill_keys(array_keys($accounts),'4101-01-01'))],'master'=>[['no_perkiraan'=>'4101-01-01','nama'=>'Akun uji <script>alert(1)</script>']],'roles'=>Invoice_consulting::roles(),'mapping_fingerprint'=>'mapping-ui','error'=>null];
$settings['companies'][]=['id'=>'6','nm_company'=>'STM-Sustain'];
$settings['journal_owners']=[['id'=>'7','nm_company'=>'STM'],['id'=>'3','nm_company'=>'Sustain'],['id'=>'4','nm_company'=>'Vuca']];
$html=$renderer->render('consulting_settings',$settings);$xpath=htmlQuery($html);
verifyUi(strpos($html,'<script>alert(1)</script>')===false,'Settings escaping');
verifyUi($xpath->query('//*[@id="consulting-coa-settings"]//select')->length===11,'Two companies and nine COA');
verifyUi($xpath->query('//*[@id="consulting-settings"]//select[@required]')->length===9,'Nine required mappings');
verifyUi($xpath->query('//*[@id="consulting-settings"]//select/option[@selected]')->length===9,'Saved mapping selection preserved');
verifyUi($xpath->query('//*[@id="consulting-settings"]//fieldset')->length===2,'Revenue and tax groups');
verifyUi($xpath->query('//*[@name="mapping_fingerprint"]')->item(0)->getAttribute('value')==='mapping-ui','Fingerprint preserved');
verifyUi(strpos($html,'minimumResultsForSearch: 0')!==false,'Search always enabled');
verifyUi($xpath->query('//label[@for="source_company_id"]')->item(0)->textContent==='Company SPK','Company SPK label');
verifyUi($xpath->query('//select[@id="source_company_id"]/option[@value="6"]')->length===1,'Combined SPK remains selectable');
verifyUi($xpath->query('//select[@id="owner_company_id"]/option[@value!=""]')->length===3,'Only three journal owners');
verifyUi($xpath->query('//select[@id="owner_company_id"]/option[@value="6"]')->length===0,'Combined owner not selectable');
$settings['owner_warning']='Pilih ulang pemilik <script>alert(1)</script>';
$warningHtml=$renderer->render('consulting_settings',$settings);
verifyUi(strpos($warningHtml,'<script>alert(1)</script>')===false && strpos($warningHtml,'Pilih ulang pemilik')!==false,'Legacy owner warning escaped');
$settings['master']=[];$html=$renderer->render('consulting_settings',$settings);$xpath=htmlQuery($html);
verifyUi($xpath->query('//*[@id="consulting-settings"]//button[@disabled]')->length===1,'Empty master save disabled');
$settings['source_id']='';$settings['owner_id']='';$html=$renderer->render('consulting_settings',$settings);$xpath=htmlQuery($html);
verifyUi($xpath->query('//*[@id="consulting-settings"]')->length===0,'Unloaded company no mapping submission');
verifyUi(strpos($html,'lalu tekan Muat COA')!==false,'Company empty state actionable');
foreach(['consulting_invoice','consulting_settings']as $view){$html=$renderer->render($view,['error'=>'Error <script>alert(1)</script>']);verifyUi(strpos($html,'role="alert"')!==false&&strpos($html,'<script>alert(1)</script>')===false,'Error escaped '.$view);}
$GLOBALS['denied']=[];$html=$renderer->render('index',['data_company'=>[]]);$xpath=htmlQuery($html);
verifyUi($xpath->query('//*[@role="tab"]')->length===2,'Two accessible tabs');
verifyUi($xpath->query('//*[@role="tabpanel"]')->length===2,'Two tab panels');
verifyUi($xpath->query('//*[@role="tab" and @aria-selected="true"]')->length===1,'One selected tab');
verifyUi($xpath->query('//*[@id="table_penawaran"]//th')->length===10,'Consulting columns preserved');
verifyUi($xpath->query('//*[@id="table_penawaran_non_konsultasi"]//th')->length===10,'Other columns preserved');
verifyUi(strpos($html,'invoicing/coa_settings')!==false,'Configure navigation visible with permission');
verifyUi(strpos($html,'invoicing/list_penawaran_non_kons')!==false,'Create visible with permission');
$GLOBALS['denied']=['Invoicing.Configure','Invoicing.Add'];$html=$renderer->render('index',['data_company'=>[]]);
verifyUi(strpos($html,'invoicing/coa_settings')===false,'Configure permission respected');
verifyUi(strpos($html,'invoicing/list_penawaran_non_kons')===false,'Create permission respected');
echo 'UI render PASS: '.$GLOBALS['checks']." checks\n";
