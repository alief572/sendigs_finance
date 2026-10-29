<?php
define('BASEPATH',true);define('APPPATH',dirname(__DIR__,3).'/');class Admin_Controller{}class BF_Model{}
$permissions=['Jurnal_All.View','Jurnal_All.Manage','Report_Jurnal_All.View'];
function has_permission($p){global $permissions;return in_array($p,$permissions,true);}
function log_message($level,$message){}
function redirect($url){global $redirected;$redirected=$url;}
require dirname(__DIR__).'/models/Jurnal_all_model.php';
require dirname(__DIR__).'/libraries/Jurnal_all_dispatcher.php';
require dirname(__DIR__).'/controllers/Jurnal_all.php';
require dirname(__DIR__,2).'/report_jurnal_all/controllers/Report_jurnal_all.php';
$checks=0;function check($ok,$msg){global $checks;$checks++;if(!$ok)throw new RuntimeException($msg);}
class Input {public $values=['id'=>1,'source'=>'PAY'];public $verb='POST';public function method($upper){return $this->verb;}public function post($key=null){return $key===null?$this->values:($this->values[$key]??null);}public function get($key=null){return $this->post($key);}}
class Output {public $status=200,$body='',$headers=[];public function set_status_header($value){$this->status=$value;return $this;}public function set_content_type($value){return $this;}public function set_output($value){$this->body=$value;return $this;}public function set_header($value){$this->headers[]=$value;return $this;}}
class Recorder {public $events=[],$fail=false;public function begin($module,$method,$initial,$keys){$this->events[]=['begin',$module,$method,$keys];}public function complete($msg,$status,$meta){$this->events[]=['complete',$status,$meta];if($this->fail)throw new RuntimeException('writer');}}
class Loader {public $views=[],$models=[];public function library($name){}public function model($name){$this->models[]=$name;}public function view($name,$data,$return=false){$this->views[]=[$name,$data];return '<p>detail</p>';}}
class Template {public $data=[];public function title($title){}public function page_icon($icon){}public function set($data){$this->data=$data;}public function render($view){}}
class Data extends Jurnal_all_model {
    public $calls=0,$fail=false,$source='PAY',$rows=[],$balanced=true,$canPost=true,$blockReason='';
    private function read(){$this->calls++;if($this->fail)throw new RuntimeException('query');}
    public function listing($values,$posted=false){$this->read();return ['draw'=>1,'data'=>[['id'=>1]],'recordsTotal'=>1,'recordsFiltered'=>1];}
    public function options($values,$posted=false){$this->read();return ['years'=>[2026],'companies'=>[],'categories'=>[]];}
    public function summary($filters){$this->read();return ['INV'=>['count'=>1]];}
    public function detail($id,$posted){$this->read();return ['header'=>['source'=>$this->source,'no_transaksi'=>'PC1','jenis_transaksi'=>'Petty Cash','payment_reference_error'=>$this->blockReason],'is_balance'=>$this->balanced,'can_post'=>$this->canPost,'rows'=>$this->rows];}
    public function export_rows($values,$posted=false){$this->read();return [];}
}
class Dispatcher {public $calls=0,$status=1;public function execute($action,$id,$source,$payload){$this->calls++;return ['status'=>$this->status,'msg'=>'outcome'];}}
class PettyPreview {public $calls=0,$rows=[];public function find_unclear_coa($rows){return [];}public function get_detail_by_transaksi($number,$type){$this->calls++;return $this->rows;}}
function fixture($class='Jurnal_all'){
    $c=(new ReflectionClass($class))->newInstanceWithoutConstructor();$c->input=new Input();$c->output=new Output();$c->module_audit_recorder=new Recorder();$c->load=new Loader();$c->template=new Template();$c->Jurnal_all_model=new Data();$c->jurnal_all_dispatcher=new Dispatcher();return $c;
}
$methods=['index','get_data_jurnal','get_filters','get_summary','preview','save_posting_jurnal','revisi_jurnal','export_excel'];
foreach($methods as $method){$c=fixture();$c->$method();check(count($c->module_audit_recorder->events)===2,'one audit '.$method);check($c->module_audit_recorder->events[1][1]===1,'success audit '.$method);}
$permissions=[];foreach($methods as $method){$c=fixture();$c->$method();check($c->output->status===403&&$c->Jurnal_all_model->calls===0&&$c->jurnal_all_dispatcher->calls===0,'permission before read/write '.$method);check($c->module_audit_recorder->events[1][1]===0,'denied audit '.$method);}
$permissions=['Jurnal_All.View'];foreach(['save_posting_jurnal','revisi_jurnal'] as $method){$c=fixture();$c->$method();check($c->output->status===403&&$c->jurnal_all_dispatcher->calls===0,'view cannot mutate');}
$c=fixture();$c->Jurnal_all_model->source='EXP';$c->input->values['source']='EXP';$c->preview();check($c->output->status===403,'expense preview manage contract');
$permissions=['Jurnal_All.View','Jurnal_All.Manage','Report_Jurnal_All.View'];
$c=fixture();$c->Jurnal_all_model->canPost=false;$c->Jurnal_all_model->blockReason='Hasil accounting sudah ada; posting ulang diblokir.';$c->preview();$blockedPayload=json_decode($c->output->body,true);
check($blockedPayload['show_post']===true&&$blockedPayload['can_post']===false&&strpos($blockedPayload['post_block_reason'],'accounting')!==false,'manager sees blocked posting and explicit reason without enabling it');
$c=fixture();$c->Jurnal_all_model->balanced=false;$c->preview();$blockedPayload=json_decode($c->output->body,true);check($blockedPayload['show_post']&&!$blockedPayload['can_post']&&strpos($blockedPayload['post_block_reason'],'balance')!==false,'unbalanced preview stays blocked with visible action');
$c=fixture('Report_jurnal_all');$c->preview();$reportPayload=json_decode($c->output->body,true);check(!$reportPayload['show_post']&&!$reportPayload['can_post'],'posted report never exposes posting action');
$c=fixture();$c->input->values['source']='INV';$c->preview();check($c->output->status===400,'source mismatch');
foreach(['save_posting_jurnal','revisi_jurnal'] as $method){$c=fixture();$c->input->verb='GET';$c->$method();check($c->output->status===403&&$c->jurnal_all_dispatcher->calls===0,'write POST only');}
foreach(['get_data_jurnal','get_filters','preview','export_excel'] as $method){$c=fixture();$c->Jurnal_all_model->fail=true;$c->$method();check($c->output->status===500&&$c->module_audit_recorder->events[1][1]===0,'read/export failure '.$method);check(strpos($c->output->body,'query')===false,'no raw error leak');}
$c=fixture();$c->jurnal_all_dispatcher->status=0;$c->save_posting_jurnal();check(json_decode($c->output->body,true)['status']===0&&$c->module_audit_recorder->events[1][1]===0,'commit failure audit');
$c=fixture();$c->module_audit_recorder->fail=true;$c->save_posting_jurnal();check(json_decode($c->output->body,true)['status']===1,'writer failure preserves committed result');
foreach(['index','get_data_jurnal','get_filters','preview','export_excel'] as $method){$c=fixture('Report_jurnal_all');$c->$method();check($c->module_audit_recorder->events[0][1]==='report_jurnal_all','report scope '.$method);}
foreach(['save_posting_jurnal','revisi_jurnal'] as $method){$c=fixture('Report_jurnal_all');$c->$method();check($c->output->status===403&&$c->jurnal_all_dispatcher->calls===0,'report read-only '.$method);}
$c=fixture();$c->Jurnal_all_model->source='PCS';$c->input->values['source']='PCS';$c->Jurnal_payment_petty_cash_model=new PettyPreview();$c->preview();check($c->Jurnal_payment_petty_cash_model->calls===1,'pending PCS preview uses native detail resolver');
$c=fixture('Report_jurnal_all');$c->Jurnal_all_model->source='PCS';$c->preview();check(!$c->load->models&&$c->output->status===200,'posted PCS preview does not load accounting/posting model');
$c=fixture();$c->legacy('PAY');check($redirected==='jurnal_all?tab=PAY','legacy redirect');
$c=fixture('Report_jurnal_all');$c->legacy('EXP');check($redirected==='report_jurnal_all?tab=EXP','report redirect');
include APPPATH.'config/routes.php';
foreach(['jurnal_invoicing'=>'INV','jurnal_penerimaan'=>'RCV','jurnal_payment'=>'PAY','jurnal_expense'=>'EXP','jurnal_payment_petty_cash'=>'PCS'] as $path=>$tab){check($route[$path]==='jurnal_all/legacy/'.$tab&&$route[$path.'/index']===$route[$path],'entry-only redirect '.$path);check(!isset($route[$path.'/save_posting_jurnal']),'posting route retained '.$path);}
check(!isset($route['jurnal'],$route['jurnal_payment_petty_cash/buku_besar']),'generic Jurnal and Buku Besar preserved');
$detail=['header'=>['source'=>'PAY','no_transaksi'=>'<script>','no_referensi'=>'<svg>','nm_company'=>'<img>','nm_divisi'=>'Finance','tgl_jurnal'=>'2026-10-14','source_numbers'=>'SOURCE','debit'=>'100.25','kredit'=>'100.25'], 'rows'=>[['id'=>1,'tgl_jurnal'=>'2026-10-14','jenis_transaksi'=>'Payment','coa'=>'<img>','nm_coa'=>'<svg>','keterangan'=>'</textarea><script>bad</script> {REF:REF1}','no_transaksi'=>'DOC1','debit'=>'100.25','kredit'=>'100.25']],'is_balance'=>true];
$posted=false;ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();check(strpos($html,'<script>')===false&&strpos($html,'&lt;script&gt;')!==false,'safe preview');check(strpos($html,'100.25')!==false,'preview cents');
$posted=true;ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();check(strpos($html,'<textarea')===false&&strpos($html,'No. Jurnal Accounting')!==false,'report detail read-only source labels');
$rows=[['header'=>$detail['header']+['tab'=>'PAY','category'=>'=HYPERLINK("x")'],'line'=>$detail['rows'][0]+['nm_company'=>'Company','nm_divisi'=>'Finance','no_jurnal'=>'SOURCE']]];
ob_start();include dirname(__DIR__).'/views/export_excel.php';$html=ob_get_clean();check(strpos($html,'<script>')===false&&strpos($html,'&#039;=HYPERLINK')!==false,'export escaping and formula injection');
$posted=false;
$detail['header']=array_merge($detail['header'],['source'=>'INV','no_transaksi'=>'INTERNAL-INV','display_no_transaksi'=>'INV/1','invoice_number'=>'INV/1','no_referensi'=>'SPK-1']);
$detail['rows'][0]['keterangan']='PPh - INTERNAL-INV';
ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();
check(strpos($html,'<dd>INV/1</dd>')!==false&&strpos($html,'<dd>SPK-1</dd>')!==false,'invoice preview metadata follows invoice/SPK display');
check(strpos($html,'PPh - INV/1')!==false&&strpos($html,'PPh - SPK-1')===false,'native invoice COA description keeps invoice reference rather than SPK');
$detail['header']=array_merge($detail['header'],['source'=>'PCS','jenis_transaksi'=>'Petty Cash','no_transaksi'=>'PCP-1','display_no_transaksi'=>'PCP-1','no_referensi'=>'RPC-1','nm_company'=>'STM / VUCA','debit'=>'887500.00','kredit'=>'887500.00']);
$detail['rows']=[];
foreach([['4','VUCA'],['7','STM']] as $company) foreach([['443750.00','0.00'],['0.00','443750.00']] as $amount) {
    $detail['rows'][]=['id'=>count($detail['rows'])+1,'tgl_jurnal'=>'2026-10-13','jenis_transaksi'=>'Petty Cash','coa'=>'5104','nm_coa'=>'Biaya','keterangan'=>'Detail '.$company[1],'no_transaksi'=>'PCP-1','id_company'=>$company[0],'nm_company'=>$company[1],'debit'=>$amount[0],'kredit'=>$amount[1]];
}
$detail['company_sections']=(new Jurnal_all_model())->company_sections($detail['rows']);
$posted=false;ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();
check(substr_count($html,'class="ja-company-section"')===2&&strpos($html,'Sisi VUCA')!==false&&strpos($html,'Sisi STM')!==false,'petty preview renders separate company sections');
check(substr_count($html,'TOTAL COMPANY')===2&&strpos($html,'887,500.00')===false,'company totals do not show combined amount as a company balance');
check(substr_count($html,'company ini seimbang')===2&&substr_count($html,'443,750.00')===8,'both company sections show exact own balance and line amounts');
$posted=true;ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();check(substr_count($html,'class="ja-company-section"')===2&&strpos($html,'<textarea')===false,'posted petty report shares company sections read-only');
$detail['company_sections'][0]['nm_company']='<img src=x onerror=bad>';
ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();check(strpos($html,'<img src=x')===false&&strpos($html,'&lt;img src=x')!==false,'company section labels escaped');
$c=fixture();$c->Jurnal_all_model->source='PCS';$c->input->values['source']='PCS';$c->Jurnal_payment_petty_cash_model=new PettyPreview();
$c->Jurnal_payment_petty_cash_model->rows=$detail['rows'];$c->preview();
$rendered=$c->load->views[0][1]['detail'];
check(count($rendered['company_sections'])===2&&$rendered['company_sections'][0]['debit']==='443750.00','controller builds sections after native pending detail resolver');
$c=fixture('Report_jurnal_all');$c->Jurnal_all_model->source='PCS';$c->Jurnal_all_model->rows=$detail['rows'];$c->preview();
check(count($c->load->views[0][1]['detail']['company_sections'])===2&&!json_decode($c->output->body,true)['can_post'],'posted controller prepares company sections with posting disabled');
$detail['header']['jenis_transaksi']='Refill Pettycash';
ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();check(strpos($html,'class="ja-company-section"')===false&&strpos($html,'GRAND TOTAL')!==false,'payment-side refill retains previous preview layout');
$posted=true;$detail['header']['source']='INV';$detail['header']['source_numbers']='FINAL-JV';$detail['header']['posted_at']='2026-10-15 15:50:00';$detail['header']['posted_by']='<img src=x>';
ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();check(strpos($html,'FINAL-JV')!==false&&strpos($html,'15:50:00 WIB')!==false&&strpos($html,'&lt;img src=x&gt;')!==false,'posted detail shows confirmed number/date/escaped actor');
$rows=[['header'=>array_merge($detail['header'],['tab'=>'INV','category'=>'Invoicing']),'line'=>array_merge($detail['rows'][0],['nm_divisi'=>'Finance','no_jurnal'=>'BAD-STAGING'])]];
ob_start();include dirname(__DIR__).'/views/export_excel.php';$html=ob_get_clean();check(strpos($html,'FINAL-JV')!==false&&strpos($html,'BAD-STAGING')===false&&strpos($html,'2026-10-15')!==false,'posted Excel uses actual accounting metadata rather than staging number');
echo "PASS Jurnal All controllers/routes/views: {$checks} assertions\n";
