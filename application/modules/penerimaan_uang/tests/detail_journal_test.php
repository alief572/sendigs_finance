<?php
if (!function_exists('has_permission')) { function has_permission($permission) { return true; } }
// Controller + rendered view regression; no database writes or browser.
define('BASEPATH', __DIR__);
class Admin_Controller {}
require dirname(__DIR__) . '/controllers/Penerimaan_uang.php';
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
function checkDetail($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class DetailResult {
    private $rows;
    function __construct($rows) { $this->rows=$rows; }
    function row_array() { return $this->rows[0] ?? array(); }
    function result_array() { return $this->rows; }
}
class DetailDb {
    public $journals=array(), $details=array(), $headers=array();
    private $table, $filters=array();
    function select($columns) { return $this; }
    function from($table) { $this->table=$table; return $this; }
    function join($table, $on, $type=null) { return $this; }
    function where($key, $value) { $this->filters[substr(strrchr('.'.$key,'.'),1)]=array($value); return $this; }
    function where_in($key, $values) { $this->filters[substr(strrchr('.'.$key,'.'),1)]=$values; return $this; }
    function get() {
        $rows=strpos($this->table,'tr_jurnal')===0 ? $this->journals : $this->details;
        foreach($this->filters as $key=>$values) $rows=array_filter($rows,function($row) use($key,$values) { return in_array($row[$key],$values,true); });
        $this->filters=array(); return new DetailResult(array_values($rows));
    }
    function get_where($table,$where) {
        $rows=$table==='tr_penerimaan_piutang' ? $this->headers : $this->journals;
        foreach($where as $key=>$value) $rows=array_filter($rows,function($row) use($key,$value) { return $row[$key]===$value; });
        return new DetailResult(array_values($rows));
    }
}
class DetailInput { public $id=1; function post() { return array('id_alokasi'=>$this->id); } }
class DetailModel { function resolve_alokasi($id) { return array('detail'=>array('nilai_terpakai'=>44100000)); } }
class DetailTemplate { public $data, $view; function set($data) { $this->data=$data; } function render($view) { $this->view=$view; } }
$db=new DetailDb;
$number='00046/RKN-KRG/VIII/26';
$db->headers=array(array('id'=>1,'no_surat'=>$number,'id_alokasi'=>10),array('id'=>2,'no_surat'=>'OTHER-RECEIPT','id_alokasi'=>11));
$detail=array('id_inv'=>'00104-INV-V-26','id_header'=>$number,'no_invoice'=>'110/STM/2026','tgl_inv'=>'2026-05-05','dpp'=>45000000,'dpp_lain'=>0,'ppn'=>0,'pph23'=>900000,'tagihan_ppn'=>45000000,'total_akhir_jurnal'=>44100000,'penerimaan'=>44100000,'biaya_admin'=>0,'sisa_piutang'=>0);
$detail['total']=44100000;
$db->details=array($detail,array_replace($detail,array('id_header'=>'OTHER-RECEIPT')));
foreach(array('1101-02-01'=>'Bank BCA','1102-01-01'=>'Piutang Dagang Usaha','1106-01-02'=>'PPh 23 Disetor','7201-01-04'=>'Administrasi Bank') as $coa=>$name) {
    $db->journals[]=array('no_transaksi'=>$number,'jenis_transaksi'=>'Penerimaan Piutang','tgl_jurnal'=>'2026-08-01','coa'=>$coa,'nm_company'=>'STM-Sustain','nm_coa'=>$name,'debit'=>$coa==='1101-02-01'?44100000:0,'kredit'=>$coa==='1102-01-01'?44100000:0,'sts'=>'1');
}
// Same invoice, separate receipt must not leak into the first detail modal.
$db->journals[]=array_replace($db->journals[0],array('no_transaksi'=>'OTHER-RECEIPT','nm_coa'=>'OTHER JOURNAL','debit'=>123));
$db->journals[]=array_replace($db->journals[0],array('jenis_transaksi'=>'Invoicing','nm_coa'=>'WRONG TYPE'));
$controller=(new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();
$controller->db=$db; $controller->input=new DetailInput;
$controller->Penerimaan_uang_model=new DetailModel; $controller->template=new DetailTemplate;
$controller->detail();
checkDetail($controller->template->view==='detail','Correct view rendered');
checkDetail(count($controller->template->data['list_jurnal'])===4,'View must find four journals by receipt number, not invoice ID');
extract($controller->template->data);
ob_start(); include dirname(__DIR__).'/views/detail.php'; $html=ob_get_clean();
checkDetail(strpos($html,'<td class="text-center">1101-02-01</td>')!==false,'COA appears as cell text');
checkDetail(strpos($html,'<td class="text-center">1102-01-01</td>')!==false,'Receivable COA appears');
checkDetail(strpos($html,'value="44100000"')!==false && $total_debit===44100000 && $total_kredit===44100000,'Journal totals reflect saved rows');
checkDetail(strpos($html,'OTHER JOURNAL')===false && strpos($html,'WRONG TYPE')===false,'Unrelated receipt/type excluded');
// Invoice lama: Piutang Dagang (44.100.000) sudah neto setelah PPh 23 (900.000), jadi PPh tidak boleh dikurangkan lagi di Kontrol.
checkDetail(preg_match('/name="kontrol"[^>]*value="0"/',$html)===1 && strpos($html,'Selisih')===false,'Legacy row: Kontrol is 0 and PPh is not subtracted twice');
$controller->input->id=2; $controller->detail();
checkDetail(count($controller->template->data['list_jurnal'])===1 && $controller->template->data['list_jurnal'][0]['nm_coa']==='OTHER JOURNAL','Second receipt isolates its own journal');
$db->journals=array(); $controller->input->id=1; $controller->detail();
checkDetail($controller->template->data['list_jurnal']===array(),'Absent journals remain absent, no fabricated entries');
function renderDetail($data) { extract($data); ob_start(); include dirname(__DIR__).'/views/detail.php'; return ob_get_clean(); }

// Penerimaan model baru (calculation_version 4): Piutang Dagang bruto 1.000.000, PPh 20.000, bank 980.000 -> PPh tetap ikut rumus Kontrol.
$v4Number='00099/RKN-KRG/X/26';
$cents=function($rupiah){return (int)round($rupiah*100);};
$snapshot=array('calculation_version'=>4,'date'=>'2026-10-01','invoice_no'=>'999/STM/2026','tax_treatment'=>'PKP','balance'=>$cents(1000000),'alok'=>$cents(980000),'admin'=>0,'pph'=>$cents(20000),'sisa'=>0,'sales'=>$cents(1000000),'dpp_lain'=>0,'ppn'=>0,'tagihan'=>$cents(1000000));
$db->headers[]=array('id'=>3,'no_surat'=>$v4Number,'id_alokasi'=>12,'nominal_penerimaan_bank'=>'980000.00');
$db->details[]=array('id_inv'=>'V4-INV','id_header'=>$v4Number,'no_invoice'=>'999/STM/2026','tgl_inv'=>'2026-10-01','consulting_version'=>3,'consulting_snapshot'=>json_encode($snapshot),'dpp'=>0,'dpp_lain'=>0,'ppn'=>0,'pph23'=>0,'tagihan_ppn'=>0,'total_akhir_jurnal'=>0,'penerimaan'=>0,'biaya_admin'=>0,'sisa_piutang'=>0,'total'=>0);
$controller->input->id=3; $controller->detail();
$v4Html=renderDetail($controller->template->data);
checkDetail(preg_match('/name="kontrol"[^>]*value="0"/',$v4Html)===1 && strpos($v4Html,'Selisih')===false,'Version 4 row: PPh stays in the Kontrol formula and Kontrol is 0');
// Jika bank kurang Rp 1.000 dari seharusnya, Kontrol harus menampakkan selisih (rumus tidak boleh selalu 0).
$db->headers[2]['nominal_penerimaan_bank']='979000.00'; $controller->detail();
checkDetail(strpos(renderDetail($controller->template->data),'Selisih')!==false,'Version 4 row: a real difference is still reported');

// ID penerimaan tidak ada: pesan jelas, tidak ada view, tidak ada notice.
$controller->template->view=null; $controller->input->id=999;
ob_start(); $controller->detail(); $notFound=ob_get_clean();
checkDetail($controller->template->view===null && strpos($notFound,'Penerimaan tidak ditemukan')!==false,'Unknown receipt id shows a clear message without rendering the view');

echo "PASS: receipt detail journal lookup, Kontrol legacy/version 4, unknown id, posted rows, COA rendering, totals, isolated receipts and empty history\n";
