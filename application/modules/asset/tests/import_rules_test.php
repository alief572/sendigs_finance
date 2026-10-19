<?php
require dirname(__DIR__, 3) . '/libraries/Asset_import.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$rules = new Asset_import();
$masters = $rules->masters(['category' => [['id'=>1,'name'=>'GEDUNG'],['id'=>2,'name'=>'ALAT']], 'company'=>[['id'=>1,'name'=>'PT A']], 'department'=>[]]);
$good = ['Laptop','ALAT','PT A','','12/10/2026',5,3000000,4];
$result = $rules->validate([2=>$good], $masters);
check(!$result['errors'] && $result['summary']['unit'] === 5 && $result['summary']['total'] === 15000000, 'qty/per-unit');
check($result['rows'][0]['monthly'] === 62500 && $result['rows'][0]['date'] === '2026-10-12', 'monthly/date');
$good[5] = ''; $good[4] = 46307;
check(!$rules->validate([2=>$good], $masters)['errors'], 'Excel date/default qty');
foreach ([[4,'31/02/2026'],[5,1.5],[6,'Rp3.000.000'],[7,21],[1,'missing'],[0,'=A1'],[6,0],[5,100],[6,-2],[4,60],[2,'']] as $case) {
    list($col,$bad)=$case;
    $row = $good; $row[$col]=$bad; check(count($rules->validate([2=>$row],$masters)['errors']) > 0, 'invalid column '.$col);
}
check(count($rules->validate(array_fill(2,501,$good),$masters)['errors']) > 0, 'row cap');
$good[5]=99; check(count($rules->validate(array_fill(2,11,$good),$masters)['errors']) > 0,'unit cap');
check(count($rules->validate([2=>array_fill(0,8,'')],$masters)['errors']) > 0, 'empty');
$changed=$masters; unset($changed['category']['ALAT']);
check(count($rules->revalidate($result['rows'],$changed))===1,'master recheck');
$duplicate=$rules->masters(['company'=>[['id'=>1,'name'=>'Same'],['id'=>2,'name'=>'Same']]]);
check(isset($duplicate['company']['Same [ID 1]'],$duplicate['company']['Same [ID 2]']),'duplicate labels');
echo "Import rules OK\n";
