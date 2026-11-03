<?php
require dirname(__DIR__).'/libraries/Payment_coa_completion.php';
$checks=0;function coaCheck($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
$source=[];foreach(Payment_coa_completion::ACCOUNTS as $code=>$definition)$source[]=['id'=>'10','no_perkiraan'=>$code,'nama'=>$definition[0],'level'=>$definition[1],'tipe'=>'A','faktor'=>'1','kdcab'=>'101-A','bln'=>'3','thn'=>'2026','saldoawal'=>'999','debet'=>'55','kredit'=>'44','mata_uang'=>'USD'];
$plan=Payment_coa_completion::plan($source,[]);
coaCheck(count($plan)===4,'two requested accounts and two required parents');
foreach($plan as $row){coaCheck($row['saldoawal']==='0'&&$row['debet']==='0'&&$row['kredit']==='0','no balances or movements copied');coaCheck(!isset($row['id'])&&!isset($row['mata_uang']),'target IDs and unsupported currency not copied');}
coaCheck($plan[3]['level']==='3','preserve global level without creating fictitious leaf account');
coaCheck(Payment_coa_completion::plan($source,$plan)===[],'rerun never duplicates matching master');
$partial=Payment_coa_completion::plan($source,[$plan[0]]);coaCheck(count($partial)===3,'resume partial master completion only missing rows');
foreach(['nama','level','tipe'] as $field){$bad=$source;$bad[0][$field]='Changed';try{Payment_coa_completion::plan($bad,[]);coaCheck(false,'changed source');}catch(DomainException $e){coaCheck(true,'changed source '.$field.' rejected');}}
$bad=$plan;$bad[0]['nama']='Other';try{Payment_coa_completion::plan($source,$bad);coaCheck(false,'conflicting target');}catch(DomainException $e){coaCheck(true,'existing target never overwritten');}
try{Payment_coa_completion::plan(array_slice($source,1),[]);coaCheck(false,'missing source');}catch(DomainException $e){coaCheck(true,'missing source rejected');}
echo "PASS COA completion: {$checks} assertions\n";
