<?php
define('BASEPATH',__DIR__);
require dirname(__DIR__).'/libraries/Invoice_calculation.php';
function check($ok,$msg) { if (!$ok) throw new RuntimeException($msg); }
$cases=array();
foreach(array(1,5,55,100055,1500000000,999999999999,999999999999999) as $dpp) foreach(array('0','12') as $rate) $cases[]=Invoice_calculation::totals($dpp,$rate);
$cases[]=Invoice_calculation::totals(1500000000,'0',true);
if (($argv[1]??'')==='--json') { echo json_encode($cases); exit; }
check($cases[count($cases)-1]['net']===1492500000,'Vuca net');
check(Invoice_calculation::items(array(array('nama'=>'Item','qty'=>'1.5','harga'=>'100.25')),'2.00')['dpp']===15238,'Half up item/shipping');
check(Invoice_calculation::cents('1,234.50',true)===123450,'Explicit US grouping');
foreach(array('-1','1.234,50','1,23','1.005',array(),null) as $bad) {
    try { Invoice_calculation::cents($bad,true); throw new RuntimeException('Bad input accepted'); } catch(InvalidArgumentException $expected) {}
}
foreach(array('0','-1','1e3','0.0000001') as $bad) {
    try { Invoice_calculation::items(array(array('nama'=>'Item','qty'=>$bad,'harga'=>'1.00')),'0'); throw new RuntimeException('Bad quantity accepted'); } catch(InvalidArgumentException $expected) {}
}
foreach($cases as $t) check($t['net']+$t['pph']===$t['dpp']+$t['ppn'],'Balanced journal');
echo "PASS: invoice cents, rounded tax, fractional items, shipping and invalid formats\n";
