<?php
require __DIR__.'/Query_test.php';

// Stub koneksi accounting: hanya chain yang dipakai validate_invoice_coa().
class Coa_master_stub {
    public $db_debug=false; public $rows=[]; public $fail=false; private $codes=[];
    public function distinct(){return $this;}
    public function select($s){return $this;}
    public function where_in($f,$codes){$this->codes=$codes;return $this;}
    public function get($t){
        if ($this->fail) return false;
        $rows=array_values(array_filter($this->rows,function($r){return in_array($r['no_perkiraan'],$this->codes,true);}));
        return new class($rows){private $r;function __construct($r){$this->r=$r;}function result_array(){return $this->r;}};
    }
}
$stub=new Coa_master_stub();
$model=new Jurnal_all_model();$model->db=$db;
$glDbs=new ReflectionProperty('Jurnal_all_model','glDbs');$glDbs->setAccessible(true);
$glDbs->setValue($model,['accounting_stm'=>$stub]);

$kwId=journal('Kwitansi Deposit','0','KW-COA','7','500.00','500.00'); // coa 5101-01, owner STM
$stub->rows=[];
$detail=$model->detail($kwId,false);
check(!$detail['can_post']&&strpos($detail['header']['company_error'],'5101-01')!==false&&strpos($detail['header']['company_error'],'STM')!==false,'COA missing blocks Kwitansi Deposit posting with warning');

$stub->rows=[['no_perkiraan'=>'5101-01','nama'=>'Biaya khusus']];
check($model->detail($kwId,false)['can_post'],'COA present allows Kwitansi Deposit posting');

$stub->rows[]=['no_perkiraan'=>'5101-01','nama'=>'Nama lain'];
$detail=$model->detail($kwId,false);
check(!$detail['can_post']&&strpos($detail['header']['company_error'],'ganda')!==false,'ambiguous COA blocks posting');

$stub->rows=[];$stub->fail=true;
check(!$model->detail($kwId,false)['can_post'],'unreadable master fails closed');

$stub->fail=false;
$db->insert('tr_invoicing',['id'=>'INV-LEG','no_invoice'=>'INV/LEG/1','nm_customer'=>'PT Pelanggan','nm_project'=>'Proyek SPK','id_penawaran'=>'Q1','id_spk_penawaran'=>'SPK-1']);
$legacy=journal('Invoicing','0','INV-LEG','7','500.00','500.00'); // $inv dari Query_test sudah diubah/diposting; pakai jurnal pending baru
check($model->detail($legacy,false)['can_post'],'legacy Invoicing is not checked against coa_master');

echo "PASS Jurnal All invoice COA checks\n";
