<?php
$checks=0;
function check($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
function renderPreview(array $rows,$source='PAY',$posted=false){
    $detail=['header'=>['source'=>$source,'jenis_transaksi'=>'Transport','no_transaksi'=>'BK-1','no_referensi'=>'RQ-1','nm_company'=>'STM','nm_divisi'=>'Finance','tgl_jurnal'=>'2026-07-20','debit'=>'150.00','kredit'=>'150.00','source_numbers'=>'JV-1'],
        'rows'=>$rows,'is_balance'=>true];
    ob_start();require dirname(__DIR__).'/views/preview.php';return ob_get_clean();
}
function row($id,$coa,$name,$debit,$credit){return ['id'=>$id,'coa'=>$coa,'nm_coa'=>$name,'debit'=>$debit,'kredit'=>$credit,'tgl_jurnal'=>'2026-07-20','jenis_transaksi'=>'Transport','no_transaksi'=>'BK-1','keterangan'=>'Description '.$id];}
function orderOf($html){preg_match_all('/name="jurnal\[(\d+)\]\[keterangan\]"/',$html,$matches);return array_map('intval',$matches[1]);}
$rows=[
    row(1,'1101-02-03','Bank OCBC NISP','0.00','90.00'),
    row(2,'2104-01-02','PPh 23','0.00','10.00'),
    row(3,'5103-01-02','Transportasi','100.00','0.00'),
    row(4,'7201-01-01','Biaya administrasi bank','10.00','0.00'),
    row(5,'2104-01-03','PPh 21','0.00','50.00'),
    row(6,'1101-02-09','Bank lain','40.00','0.00'),
    row(7,'5104-01-01','Baris nol','0.00','0.00'),
];
$original=$rows;$html=renderPreview($rows);
check(orderOf($html)===[3,4,2,5,1,6],'debit then credit then all bank rows; stable order within each group');
check($rows===$original,'source rows unchanged');
check(strpos($html,'Description 4</textarea>')!==false,'description remains attached to original row ID');
check(strpos($html,'Baris nol')===false,'zero lines remain hidden');
check(substr_count($html,'150.00')===2,'header totals preserved');
check(orderOf(renderPreview(array_reverse($rows)))===[4,3,5,2,6,1],'group order preserved with different input order');
check(orderOf(renderPreview([$rows[1],$rows[2],$rows[4],$rows[3]]))===[3,4,2,5],'debit and credit sort without bank');
check(orderOf(renderPreview([$rows[0],$rows[5]]))===[1,6],'bank-only rows retain original order');
check(orderOf(renderPreview([]))===[],'empty details render');
foreach(['EXP','INV','RCV'] as $source){
    $other=renderPreview($rows,$source);
    check(strpos($other,'Bank OCBC NISP')<strpos($other,'Transportasi'),$source.' preview order unchanged');
}
$posted=renderPreview($rows,'PAY',true);
check(strpos($posted,'Bank OCBC NISP')<strpos($posted,'Transportasi'),'posted report order unchanged');
check(strpos($posted,'<textarea')===false,'posted report stays read-only');
echo "PASS preview order: {$checks} checks\n";
