<?php
define('BASEPATH', dirname(__DIR__,4).'/system/');
define('FCPATH', dirname(__DIR__,4).'/');
require dirname(__DIR__).'/libraries/Invoice_consulting.php';
if (!function_exists('html_escape')) {
    function html_escape($var, $double_encode = TRUE) {
        if (empty($var)) return $var;
        if (is_array($var)) {
            foreach (array_keys($var) as $key) $var[$key] = html_escape($var[$key], $double_encode);
            return $var;
        }
        return htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $double_encode);
    }
}
function base_url($path='') { return '/'.$path; }
function has_permission($permission) { return !in_array($permission,$GLOBALS['denied']??[],true); }
function verifyUi($condition,$message) { $GLOBALS['checks']++; if(!$condition)throw new RuntimeException($message); }
class UiTemplate {
    public $auth;
    function __construct() { $this->auth=new class {public $admin=true;function is_admin(){return $this->admin;}}; }
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
    verifyUi($xpath->query('//script[contains(@src,"assets/dist/sweetalert2.min.js")]')->length===($mode==='view'?0:1),'Local SweetAlert2 only on editable forms');
    verifyUi($xpath->query('//script[contains(@src,"save-feedback.js")]')->length===($mode==='view'?0:1),'Save feedback wired on add/edit');
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
verifyUi(strpos($html,'InvoiceSaveFeedback.submit')!==false,'Mapping save uses shared feedback');
verifyUi($xpath->query('//script[contains(@src,"assets/dist/sweetalert2.min.js")]')->length===1,'Mapping uses local SweetAlert2');
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
verifyUi($xpath->query('//*[@id="table_penawaran"]//th')->length===7,'Consulting grouped into seven columns');
verifyUi($xpath->query('//*[@id="table_penawaran_non_konsultasi"]//th')->length===10,'Other columns preserved');
verifyUi(strpos($html,'invoicing/coa_settings')!==false,'Configure navigation visible with permission');
$renderer->auth->admin=false;
$nonAdminHtml=$renderer->render('index',['data_company'=>[]]);
verifyUi(strpos($nonAdminHtml,'invoicing/coa_settings')===false,'Non-admin cannot see COA settings even with Configure permission');
$renderer->auth->admin=true;
verifyUi(strpos($html,'invoicing/list_penawaran_non_kons')!==false,'Create visible with permission');
$GLOBALS['denied']=['Invoicing.Configure','Invoicing.Add'];$html=$renderer->render('index',['data_company'=>[]]);
verifyUi(strpos($html,'invoicing/coa_settings')===false,'Configure permission respected');
verifyUi(strpos($html,'invoicing/list_penawaran_non_kons')===false,'Create permission respected');
if(!function_exists('terbilang')){function terbilang($n){return 'seribu seratus sepuluh';}}
$kwitansiInvoice=(object)[
    'no_invoice'=>'001/STM/2026', 'no_po'=>'PO-KW-01', 'nm_customer'=>'Customer Kwitansi <script>alert(1)</script>',
    'total_nominal'=>'1000.55', 'dpp_nilai_lain'=>'917.17', 'pajak'=>'110.06',
    'print_keterangan'=>'Pembayaran TOP 1', 'consulting_version'=>3
];
$kwitansiHtml=$renderer->render('print_kwitansi',['data_invoice'=>$kwitansiInvoice,'id_company'=>'7']);
verifyUi(strpos($kwitansiHtml,'1,000.55')!==false,'Kwitansi v3 2-decimal nominal');
verifyUi(strpos($kwitansiHtml,'917.17')!==false,'Kwitansi v3 2-decimal DPP');
verifyUi(strpos($kwitansiHtml,'110.06')!==false,'Kwitansi v3 2-decimal PPN');
verifyUi(strpos($kwitansiHtml,'1,110.61')!==false,'Kwitansi v3 2-decimal total');
verifyUi(strpos($kwitansiHtml,'PT. Sentral Tehnologi Managemen')!==false,'Kwitansi STM company footer');
$kwitansiSustain=$renderer->render('print_kwitansi',['data_invoice'=>$kwitansiInvoice,'id_company'=>'3']);
verifyUi(strpos($kwitansiSustain,'PT. Sentral Sustainability Consulting')!==false,'Kwitansi Sustain company footer');
$kwitansiLegacy=(object)[
    'no_invoice'=>'002/STM/2025', 'no_po'=>'PO-LEGACY', 'nm_customer'=>'Customer Legacy',
    'total_nominal'=>'1000', 'dpp_nilai_lain'=>'917', 'pajak'=>'110',
    'print_keterangan'=>'Pembayaran Legacy', 'consulting_version'=>0
];
$kwitansiLegacyHtml=$renderer->render('print_kwitansi',['data_invoice'=>$kwitansiLegacy,'id_company'=>'7']);
verifyUi(strpos($kwitansiLegacyHtml,'>1,000<')!==false,'Kwitansi legacy 0-decimal nominal');
verifyUi(strpos($kwitansiLegacyHtml,'>1,110<')!==false,'Kwitansi legacy 0-decimal total');
verifyUi(strpos($kwitansiHtml,'<script>alert(1)</script>')===false,'Kwitansi customer escaping');
verifyUi(strpos($kwitansiHtml,'class="body-table""')===false,'Kwitansi valid table attribute syntax');
$kwitansiVucaInvoice=(object)[
    'no_invoice'=>'001/VUC/2026', 'no_po'=>'PO-VUCA-01', 'nm_customer'=>'Customer Vuca <script>alert(1)</script>',
    'total_nominal'=>'1000', 'dpp_nilai_lain'=>'917', 'pajak'=>'110',
    'print_keterangan'=>"Pembayaran VUCA TOP 1\n<script>alert('ket')</script>"
];
$kwitansiVucaHtml=$renderer->render('print_kwitansi_vuca',['data_invoice'=>$kwitansiVucaInvoice,'id_company'=>'4']);
verifyUi(strpos($kwitansiVucaHtml,'<script>alert(1)</script>')===false,'Kwitansi Vuca customer escaping');
verifyUi(strpos($kwitansiVucaHtml,'<script>alert(\'ket\')</script>')===false,'Kwitansi Vuca keterangan escaping');
verifyUi(strpos($kwitansiVucaHtml,'PT. Vuca Strategi Bisnis')!==false,'Kwitansi Vuca company header');
verifyUi(strpos($kwitansiVucaHtml,'001/VUC/2026')!==false,'Kwitansi Vuca invoice number');
verifyUi(strpos($kwitansiVucaHtml,'PO-VUCA-01')!==false,'Kwitansi Vuca PO number');
verifyUi(strpos($kwitansiVucaHtml,'Pembayaran VUCA TOP 1<br')!==false,'Kwitansi Vuca multiline keterangan converted');
$printInvoice = [
    'no_invoice'=>'001/STM/2026', 'id_spk_penawaran'=>'SPK-UI', 'tanggal_invoice'=>'2026-10-18',
    'no_po'=>'PO-UI', 'no_faktur'=>'FP-UI', 'tax_treatment'=>'KB',
    'nm_customer'=>'Hostile Customer <script>alert(1)</script>', 'address'=>"Jl. Uji <script>alert('addr')</script>\nBaris 2",
    'total_nominal'=>'1000.55', 'dpp_nilai_lain'=>'917.17', 'ppn_jurnal'=>'110.06', 'tagihan_ppn_jurnal'=>'1110.61'
];
$printHtml = $renderer->render('consulting_print', ['invoice'=>$printInvoice, 'snapshot'=>$snapshot, 'error'=>null]);
verifyUi(strpos($printHtml, '<script>alert(1)</script>')===false, 'Consulting print customer escaping');
verifyUi(strpos($printHtml, "<script>alert('addr')</script>")===false, 'Consulting print address escaping');
verifyUi(strpos($printHtml, '&lt;script&gt;')!==false, 'Consulting print customer HTML entities');
verifyUi(strpos($printHtml, 'STM')!==false, 'Consulting print owner header');
verifyUi(strpos($printHtml, 'Kawasan Berikat')!==false, 'Consulting print tax treatment label');
verifyUi(strpos($printHtml, '1,000.55')!==false, 'Consulting print 2-decimal nominal');
verifyUi(strpos($printHtml, '917.17')!==false, 'Consulting print 2-decimal DPP');
verifyUi(strpos($printHtml, '110.06')!==false, 'Consulting print 2-decimal PPN');
verifyUi(strpos($printHtml, '1,110.61')!==false, 'Consulting print 2-decimal gross total');
$printErrorHtml = $renderer->render('consulting_print', ['invoice'=>$printInvoice, 'snapshot'=>$snapshot, 'error'=>'Gagal memuat <script>alert(1)</script>']);
verifyUi(strpos($printErrorHtml, '<script>alert(1)</script>')===false && strpos($printErrorHtml, 'Gagal memuat')!==false, 'Consulting print error escaping');

$GLOBALS['denied'] = [];
$nonKonsItems = [
    (object)[
        'id_penawaran' => 'P-NON-KONS-001 <script>alert(1)</script>',
        'nm_company' => 'STM <script>alert(2)</script>',
        'grand_total' => 19975200,
        'pic' => 'Ali Sadiman <script>alert(3)</script>',
        'total_invoice' => 1
    ],
    (object)[
        'id_penawaran' => 'P-NON-KONS-002',
        'nm_company' => 'Vuca',
        'grand_total' => 3500000,
        'pic' => 'Risa',
        'total_invoice' => 0
    ]
];
$listHtml = $renderer->render('list_penawaran_non_kons', ['list_penawaran_non_kons' => $nonKonsItems]);
$listXpath = htmlQuery($listHtml);

verifyUi(strpos($listHtml, '<script>alert(1)</script>') === false, 'List non-kons penawaran escaping');
verifyUi(strpos($listHtml, '<script>alert(2)</script>') === false, 'List non-kons company escaping');
verifyUi(strpos($listHtml, '<script>alert(3)</script>') === false, 'List non-kons PIC escaping');
verifyUi($listXpath->query('//*[@id="invoicing-workspace"]')->length === 1, 'List non-kons workspace container');
verifyUi($listXpath->query('//*[@id="table_list_penawaran_non_kons"]')->length === 1, 'List non-kons data table');
verifyUi($listXpath->query('//*[@id="table_list_penawaran_non_kons"]//th')->length === 8, 'List non-kons eight table columns');
verifyUi(strpos($listHtml, '19,975,200') !== false, 'List non-kons grand total formatted');
verifyUi(strpos($listHtml, '3,500,000') !== false, 'List non-kons second row total formatted');
verifyUi($listXpath->query('//span[contains(@class, "bg-green") and contains(text(), "Invoice Created")]')->length === 1, 'List non-kons green status badge');
verifyUi($listXpath->query('//span[contains(@class, "bg-yellow") and contains(text(), "Draft")]')->length === 1, 'List non-kons yellow status badge');
verifyUi($listXpath->query('//button[contains(@class, "close_penawaran")]')->length === 1, 'List non-kons close penawaran action for invoiced row');
verifyUi($listXpath->query('//a[contains(@href, "create_invoice_non_konsultasi")]')->length === 2, 'List non-kons create action on both rows');

$GLOBALS['denied'] = ['Invoicing.Add'];
$noAddHtml = $renderer->render('list_penawaran_non_kons', ['list_penawaran_non_kons' => $nonKonsItems]);
$noAddXpath = htmlQuery($noAddHtml);
verifyUi($noAddXpath->query('//a[contains(@href, "create_invoice_non_konsultasi")]')->length === 0, 'Create invoice hidden without Add permission');

$GLOBALS['denied'] = ['Invoicing.Manage'];
$noManageHtml = $renderer->render('list_penawaran_non_kons', ['list_penawaran_non_kons' => $nonKonsItems]);
$noManageXpath = htmlQuery($noManageHtml);
verifyUi($noManageXpath->query('//button[contains(@class, "close_penawaran")]')->length === 0, 'Close penawaran hidden without Manage permission');
$GLOBALS['denied'] = [];

echo 'UI render PASS: '.$GLOBALS['checks']." checks\n";
