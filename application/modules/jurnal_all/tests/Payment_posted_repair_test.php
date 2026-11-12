<?php
// Posted Payment repair (approval id -> id_payment) on tr_jurnal + accounting japh/jurnal. SQLite fixtures.
require __DIR__.'/Payment_reference_test.php';
require dirname(__DIR__).'/libraries/Payment_posted_repair.php';
$repairBase=$checks;

const OLD_STM='BK--0726-0040'; const NEW_STM='BK--0126-0001';
const OLD_VUCA='BK--0426-0045'; const NEW_VUCA='BK--0426-0008';

function repairFixture()
{
    $ci=fixture();
    $ci->db->query('DELETE FROM tr_jurnal');$ci->db->query('DELETE FROM payment_approve');$ci->db->query('DELETE FROM tr_payment_paid');
    $ci->db->query('CREATE TABLE tr_jurnal_payment_reference_aliases (run_id TEXT,journal_id INTEGER,original_no_transaksi TEXT,voucher TEXT,approval_ids TEXT,active INTEGER,created_at TEXT,PRIMARY KEY(run_id,journal_id))');
    // real accounting tables have an auto-increment id; the shared fixture does not
    foreach(['accounting_stm','accounting_vuca','accounting_sustain'] as $name){
        $acc=$ci->load->dbs[$name];
        // japh keeps the real shape: NO id column, header identified by nomor (this is what broke the first live dry-run)
        $acc->query('DROP TABLE japh');
        $acc->query('CREATE TABLE japh (nomor TEXT PRIMARY KEY,tgl TEXT,jml DECIMAL(20,2),kdcab TEXT,jenis_reff TEXT,no_reff TEXT,jenis_ap TEXT,note TEXT,user_id TEXT,ho_valid TEXT,batal TEXT)');
        $acc->query('DROP TABLE jurnal');
        $acc->query('CREATE TABLE jurnal (id INTEGER PRIMARY KEY AUTOINCREMENT,tipe TEXT,nomor TEXT,tanggal TEXT,no_reff TEXT,no_perkiraan TEXT,keterangan TEXT,debet DECIMAL(20,2),kredit DECIMAL(20,2))');
    }
    $n=100;
    foreach([[OLD_STM,NEW_STM,'accounting_stm','1','101BK2600001'],[OLD_VUCA,NEW_VUCA,'accounting_vuca','4','101BK2600002']] as $g){
        list($old,$new,$ledger,$company,$nomor)=$g;
        $ci->db->insert('payment_approve',['id'=>$old,'id_payment'=>$new,'no_doc'=>'DOC-'.$old,'tipe'=>'direct_payment']);
        $ci->db->insert('tr_payment_paid',['id'=>$new]);
        foreach([[100,0],[0,100]] as $i=>$money){
            $ci->db->insert('tr_jurnal',['id'=>++$n,'no_transaksi'=>$old,'jenis_transaksi'=>'Payment','sts'=>'1','id_company'=>$company,'nm_company'=>'C','tgl_jurnal'=>'2026-01-24','coa'=>'5101','nm_coa'=>'X','debit'=>$money[0],'kredit'=>$money[1],'keterangan'=>'Tiket - '.$old]);
            $ci->load->dbs[$ledger]->insert('jurnal',['tipe'=>'BUK','nomor'=>$nomor,'tanggal'=>'2026-01-24','no_reff'=>$old,'no_perkiraan'=>'5101','keterangan'=>'Tiket - '.$old,'debet'=>$money[0],'kredit'=>$money[1]]);
        }
        $ci->load->dbs[$ledger]->insert('japh',['nomor'=>$nomor,'tgl'=>'2026-01-24','jml'=>'100.00','kdcab'=>'101','jenis_reff'=>'BUK','no_reff'=>$old,'jenis_ap'=>'V','note'=>'Payment '.$old,'user_id'=>'finance','ho_valid'=>'','batal'=>'0']);
    }
    return $ci;
}
function snapshot($ci)
{
    $s=['tr'=>$ci->db->order_by('id')->get('tr_jurnal')->result_array(),'alias'=>$ci->db->get('tr_jurnal_payment_reference_aliases')->result_array()];
    foreach(['accounting_stm','accounting_vuca','accounting_sustain'] as $n){$s[$n.':japh']=$ci->load->dbs[$n]->order_by('nomor')->get('japh')->result_array();$s[$n.':jurnal']=$ci->load->dbs[$n]->order_by('id')->get('jurnal')->result_array();}
    return $s;
}
function repairFiles($path){foreach([$path,$path.'.backup.json',$path.'.journal'] as $f)if(is_file($f))unlink($f);}
function statusOf($manifest,$old){foreach($manifest['items'] as $i)if($i['old']===$old)return $i['status'];return null;}

$path=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    // 1. dry-run only reads
    $ci=repairFixture();$before=snapshot($ci);
    $m=(new Payment_posted_repair($ci))->prepare($path);
    check(statusOf($m,OLD_STM)==='eligible'&&statusOf($m,OLD_VUCA)==='eligible','both fixture groups are eligible');
    check(statusOf($m,'BK--0626-0019')==='skipped'&&statusOf($m,'BK--0826-0075')==='skipped','whitelisted groups absent from the data are skipped, not invented');
    check($before===snapshot($ci),'dry-run changes no journal or accounting row');
    check(is_file($path.'.backup.json'),'dry-run writes the before-image backup');
    // SQLite silently treats a missing "id" as a string literal while MySQL raises 1054, so assert on the SQL itself:
    // japh has no id column and must never be ordered or filtered by it.
    $prop=new ReflectionProperty('PaymentFixtureDb','db');$prop->setAccessible(true);$sent=0;
    foreach(['accounting_stm','accounting_vuca','accounting_sustain'] as $n){
        foreach($prop->getValue($ci->load->dbs[$n])->queries as $sql){
            if(stripos($sql,'FROM "japh"')===false&&stripos($sql,'FROM `japh`')===false)continue;
            $sent++;
            check(!preg_match('/ORDER BY\s+["`]?id["`]?(\s|$)/i',$sql),'japh has no id column; query must not order by it: '.$sql);
        }
    }
    check($sent>0,'the japh queries were actually inspected');
    rejects(function()use($ci,$path){(new Payment_posted_repair($ci))->prepare($path);},'sudah ada');

    // 2. apply
    $r=new Payment_posted_repair($ci);$r->run($path);
    $db=$ci->db;
    check(count($db->get_where('tr_jurnal',['no_transaksi'=>NEW_STM])->result_array())===2&&count($db->get_where('tr_jurnal',['no_transaksi'=>NEW_VUCA])->result_array())===2,'tr_jurnal rows now use id_payment');
    check(!$db->get_where('tr_jurnal',['no_transaksi'=>OLD_STM])->result_array(),'no tr_jurnal row keeps the old approval id');
    $stm=$ci->load->dbs['accounting_stm'];$vuca=$ci->load->dbs['accounting_vuca'];
    $h=$stm->get('japh')->result_array();
    check(count($h)===1&&$h[0]['no_reff']===NEW_STM&&$h[0]['note']==='Payment '.NEW_STM,'japh header and its note follow the voucher in the right ledger');
    check(count($stm->get_where('jurnal',['no_reff'=>NEW_STM])->result_array())===2&&count($vuca->get_where('jurnal',['no_reff'=>NEW_VUCA])->result_array())===2,'jurnal lines follow the voucher in their own ledger');
    check($vuca->get('japh')->row_array()['no_reff']===NEW_VUCA,'Vuca group stays in the Vuca ledger');
    $rows=$db->order_by('id')->get('tr_jurnal')->result_array();
    check($rows[0]['sts']==='1'&&$rows[0]['keterangan']==='Tiket - '.OLD_STM&&(float)$rows[0]['debit']===100.0,'status, amounts and keterangan are untouched');
    check(count($db->where('active',1)->get('tr_jurnal_payment_reference_aliases')->result_array())===4,'every changed journal row has an active alias');
    $v=$r->verify($path);
    check($v['groups']===2&&$v['tr_jurnal']===4&&$v['jurnal']===4&&$v['japh']===2,'verify matches the backup except the reference columns');

    // 3. idempotent re-run
    $mid=snapshot($ci);(new Payment_posted_repair($ci))->run($path);
    check($mid===snapshot($ci),'apply again is a no-op');

    // 4. rollback restores the before-image
    (new Payment_posted_repair($ci))->rollback($path);
    $after=snapshot($ci);
    check($after['tr']===$before['tr']&&$after['accounting_stm:japh']===$before['accounting_stm:japh']&&$after['accounting_stm:jurnal']===$before['accounting_stm:jurnal']&&$after['accounting_vuca:japh']===$before['accounting_vuca:japh']&&$after['accounting_vuca:jurnal']===$before['accounting_vuca:jurnal'],'rollback restores journals and accounting byte for byte');
    check(count($db->where('active',1)->get('tr_jurnal_payment_reference_aliases')->result_array())===0,'rollback deactivates the aliases of the run');
}finally{repairFiles($path);}

// 5. failures in the middle never leave accounting orphaned
foreach(['after_japh','after_jurnal','before_commit'] as $point){
    $p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
    try{
        $ci=repairFixture();$before=snapshot($ci);
        (new Payment_posted_repair($ci))->prepare($p);
        rejects(function()use($ci,$p,$point){(new Payment_posted_repair($ci,$point))->run($p);},'Simulated failure');
        check($before===snapshot($ci),'failure at '.$point.' leaves journals, aliases and accounting exactly as before');
        check(count($ci->db->query('SELECT 1')->result_array())===1,'database still usable after '.$point);
    }finally{repairFiles($p);}
}

// 6. guards in the dry-run
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();$ci->db->insert('tr_jurnal',['id'=>900,'no_transaksi'=>NEW_STM,'jenis_transaksi'=>'Payment','sts'=>'0']);
    $m=(new Payment_posted_repair($ci))->prepare($p);
    check(statusOf($m,OLD_STM)==='skipped'&&statusOf($m,OLD_VUCA)==='eligible','voucher already used in tr_jurnal blocks only that group');
}finally{repairFiles($p);}
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();$ci->load->dbs['accounting_vuca']->insert('japh',['nomor'=>'X','no_reff'=>OLD_STM,'jml'=>'100.00']);
    check(statusOf((new Payment_posted_repair($ci))->prepare($p),OLD_STM)==='skipped','header found in two ledgers is ambiguous and skipped');
}finally{repairFiles($p);}
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();$ci->load->dbs['accounting_stm']->query("UPDATE japh SET no_reff='ELSEWHERE'");
    check(statusOf((new Payment_posted_repair($ci))->prepare($p),OLD_STM)==='skipped','no header in any ledger is skipped');
}finally{repairFiles($p);}
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();$ci->db->query("UPDATE tr_jurnal SET sts='0' WHERE no_transaksi='".OLD_STM."'");
    check(statusOf((new Payment_posted_repair($ci))->prepare($p),OLD_STM)==='skipped','a group that is not posted is left to the pending normalization');
}finally{repairFiles($p);}
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();$ci->db->insert('payment_approve',['id'=>'OTHER','id_payment'=>NEW_STM,'no_doc'=>'X','tipe'=>'kasbon']);
    check(statusOf((new Payment_posted_repair($ci))->prepare($p),OLD_STM)==='skipped','voucher shared by two approvals is skipped');
}finally{repairFiles($p);}

// 7. data changed after the review is refused
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();(new Payment_posted_repair($ci))->prepare($p);
    $ci->db->query("UPDATE tr_jurnal SET keterangan='edited' WHERE no_transaksi='".OLD_STM."'");$before=snapshot($ci);
    rejects(function()use($ci,$p){(new Payment_posted_repair($ci))->run($p);},'berubah sejak dry-run');
    check($before===snapshot($ci),'a stale manifest changes nothing');
}finally{repairFiles($p);}

// 8. tampered backup is refused
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();(new Payment_posted_repair($ci))->prepare($p);
    file_put_contents($p.'.backup.json',str_replace('Tiket','Hacked',file_get_contents($p.'.backup.json')));
    rejects(function()use($ci,$p){(new Payment_posted_repair($ci))->run($p);},'Backup tidak cocok');
}finally{repairFiles($p);}

// ===== Refill batches (refill_posted: tr_jurnal + accounting keyed by the id list; refill_pending: tr_jurnal only) =====
function addRefill($ci,$key,$voucher,$sts,$nomor=null,$accRef=null)
{
    static $n=300;
    $db=$ci->db;$stm=$ci->load->dbs['accounting_stm'];
    foreach(array_map('trim',explode(',',$key)) as $id)$db->insert('payment_approve',['id'=>$id,'id_payment'=>$voucher,'no_doc'=>'RPC-'.$id,'tipe'=>'refill_pettycash']);
    $db->insert('tr_payment_paid',['id'=>$voucher]);
    foreach([[100,0],[0,100]] as $m){
        $db->insert('tr_jurnal',['id'=>++$n,'no_transaksi'=>$key,'jenis_transaksi'=>'Refill Pettycash','sts'=>$sts,'id_company'=>'1','nm_company'=>'C','tgl_jurnal'=>'2026-09-25','coa'=>'1101','nm_coa'=>'Kas','debit'=>$m[0],'kredit'=>$m[1],'keterangan'=>'Refill Kas Kecil']);
        if($nomor)$stm->insert('jurnal',['tipe'=>'BUK','nomor'=>$nomor,'tanggal'=>'2026-09-25','no_reff'=>$accRef,'no_perkiraan'=>'1101','keterangan'=>'Refill Kas Kecil','debet'=>$m[0],'kredit'=>$m[1]]);
    }
    if($nomor)$stm->insert('japh',['nomor'=>$nomor,'tgl'=>'2026-09-25','jml'=>'100.00','kdcab'=>'101','jenis_reff'=>'BUK','no_reff'=>$accRef,'jenis_ap'=>'','note'=>null,'user_id'=>'203','ho_valid'=>'','batal'=>'0']);
}
function refillFixture($sts)
{
    $ci=repairFixture();
    if($sts==='1'){
        addRefill($ci,'BK--0926-0104,BK--0926-0105','BK--0926-0018','1','101BKA2600015','BK--0926-0104,BK--0926-01');
        addRefill($ci,'BK--0926-0128','BK--0926-0016','1','101BKA2600014','RPC-2026-0019');
        addRefill($ci,'BK--1026-0074','BK--1026-0003','1','101BKA2600007','RPC-2026-0033');
    }else{
        addRefill($ci,'BK--0826-0046','BK--0826-0014','0');
        addRefill($ci,'BK--0826-0050','BK--0826-0029','0');
        addRefill($ci,'BK--0826-0048,BK--0926-0099,BK--0926-0098,BK--0926-0138,BK--0826-0047,BK--0826-0014','BK--0826-0030','0');
        addRefill($ci,'BK--0826-0043,BK--0826-0044,BK--0826-0045','BK--0826-0031','0');
        addRefill($ci,'BK--0826-0042,BK--0826-0049,BK--0926-0106','BK--0826-0032','0');
    }
    return $ci;
}
function reasonOf($manifest,$old){foreach($manifest['items'] as $i)if($i['old']===$old)return (string)($i['reason'] ?? '');return null;}
function assertNoJaphOrderById($ci)
{
    $prop=new ReflectionProperty('PaymentFixtureDb','db');$prop->setAccessible(true);$sent=0;
    foreach(['accounting_stm','accounting_vuca','accounting_sustain'] as $n){
        foreach($prop->getValue($ci->load->dbs[$n])->queries as $sql){
            if(stripos($sql,'FROM "japh"')===false&&stripos($sql,'FROM `japh`')===false)continue;
            $sent++;
            check(!preg_match('/ORDER BY\s+["`]?id["`]?(\s|$)/i',$sql),'japh has no id column; query must not order by it: '.$sql);
        }
    }
    check($sent>0,'the japh queries were actually inspected');
}
$RK1='BK--0926-0104,BK--0926-0105';

$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=refillFixture('1');$before=snapshot($ci);
    $m=(new Payment_posted_repair($ci,null,'refill_posted'))->prepare($p);
    check(statusOf($m,$RK1)==='eligible'&&statusOf($m,'BK--0926-0128')==='eligible'&&statusOf($m,'BK--1026-0074')==='eligible','refill_posted: the three clean groups are eligible');
    check($before===snapshot($ci),'refill dry-run changes no journal or accounting row');
    assertNoJaphOrderById($ci);
    rejects(function()use($ci,$p){(new Payment_posted_repair($ci))->run($p);},'batch lain');
    $r=new Payment_posted_repair($ci,null,'refill_posted');$r->run($p);
    $db=$ci->db;$stm=$ci->load->dbs['accounting_stm'];
    check(count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0926-0018'])->result_array())===2&&count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0926-0016'])->result_array())===2&&count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0003'])->result_array())===2,'refill tr_jurnal rows now use id_payment');
    check(!$db->get_where('tr_jurnal',['no_transaksi'=>$RK1])->result_array()&&!$db->get_where('tr_jurnal',['no_transaksi'=>'BK--0926-0128'])->result_array(),'no refill row keeps the approval id');
    check($stm->get_where('japh',['nomor'=>'101BKA2600015'])->row_array()['no_reff']==='BK--0926-0018'&&count($stm->get_where('jurnal',['no_reff'=>'BK--0926-0018'])->result_array())===2,'accounting keyed by the cut-off id list follows the voucher');
    check($stm->get_where('japh',['nomor'=>'101BKA2600014'])->row_array()['no_reff']==='RPC-2026-0019'&&$stm->get_where('japh',['nomor'=>'101BKA2600007'])->row_array()['no_reff']==='RPC-2026-0033'&&count($stm->get_where('jurnal',['no_reff'=>'RPC-2026-0033'])->result_array())===2,'accounting keyed by the RPC number is left alone');
    check($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0926-0018'])->row_array()['sts']==='1'&&count($db->where('active',1)->get('tr_jurnal_payment_reference_aliases')->result_array())===6,'refill rows stay posted and every changed row has an active alias');
    $v=$r->verify($p);
    check($v['groups']===3&&$v['tr_jurnal']===6&&$v['japh']===1&&$v['jurnal']===2,'refill verify matches the backup except the reference columns');
    $mid=snapshot($ci);(new Payment_posted_repair($ci,null,'refill_posted'))->run($p);
    check($mid===snapshot($ci),'refill apply again is a no-op');
    (new Payment_posted_repair($ci,null,'refill_posted'))->rollback($p);
    $after=snapshot($ci);
    check($after['tr']===$before['tr']&&$after['accounting_stm:japh']===$before['accounting_stm:japh']&&$after['accounting_stm:jurnal']===$before['accounting_stm:jurnal']&&$after['accounting_vuca:japh']===$before['accounting_vuca:japh'],'refill rollback restores journals and accounting byte for byte');
}finally{repairFiles($p);}

foreach(['after_japh','after_jurnal','before_commit'] as $point){
    $p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
    try{
        $ci=refillFixture('1');$before=snapshot($ci);
        (new Payment_posted_repair($ci,null,'refill_posted'))->prepare($p);
        rejects(function()use($ci,$p,$point){(new Payment_posted_repair($ci,$point,'refill_posted'))->run($p);},'Simulated failure');
        check($before===snapshot($ci),'refill failure at '.$point.' leaves journals, aliases and accounting exactly as before');
    }finally{repairFiles($p);}
}

$guards=[
    'key shared with a Payment journal'=>function($ci){$ci->db->insert('tr_jurnal',['id'=>900,'no_transaksi'=>'BK--0926-0128','jenis_transaksi'=>'Payment','sts'=>'1']);},
    'accounting already references the id'=>function($ci){$ci->load->dbs['accounting_stm']->insert('japh',['nomor'=>'X1','no_reff'=>'BK--0926-0128','jml'=>'100.00']);},
    'approval is not a refill'=>function($ci){$ci->db->query("UPDATE payment_approve SET tipe='kasbon' WHERE id='BK--0926-0128'");},
    'group is not posted'=>function($ci){$ci->db->query("UPDATE tr_jurnal SET sts='0' WHERE no_transaksi='BK--0926-0128'");},
    'voucher equals the id of a refill approval'=>function($ci){$ci->db->insert('payment_approve',['id'=>'BK--0926-0016','id_payment'=>null,'no_doc'=>'Z','tipe'=>'refill_pettycash']);},
    'voucher has an extra approval'=>function($ci){$ci->db->insert('payment_approve',['id'=>'EXTRA','id_payment'=>'BK--0926-0016','no_doc'=>'Z','tipe'=>'refill_pettycash']);},
    'voucher already used by another journal'=>function($ci){$ci->db->insert('tr_jurnal',['id'=>901,'no_transaksi'=>'BK--0926-0016','jenis_transaksi'=>'Payment','sts'=>'0']);},
];
foreach($guards as $name=>$mutate){
    $p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
    try{
        $ci=refillFixture('1');$mutate($ci);
        $m=(new Payment_posted_repair($ci,null,'refill_posted'))->prepare($p);
        check(statusOf($m,'BK--0926-0128')==='skipped'&&statusOf($m,'BK--1026-0074')==='eligible','refill guard: '.$name.' blocks only that group');
    }finally{repairFiles($p);}
}

// a voucher equal to the id of an approval of ANOTHER type is harmless: the refill lookup filters on tipe (this is the real data shape)
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=refillFixture('1');$ci->db->insert('payment_approve',['id'=>'BK--0926-0016','id_payment'=>'SOMETHING-ELSE','no_doc'=>'PERIODIK-X','tipe'=>'periodik']);
    $m=(new Payment_posted_repair($ci,null,'refill_posted'))->prepare($p);
    check(statusOf($m,'BK--0926-0128')==='eligible','voucher equal to a periodik approval id does not block the refill group');
    $r=new Payment_posted_repair($ci,null,'refill_posted');$r->run($p);
    check(count($ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--0926-0016','jenis_transaksi'=>'Refill Pettycash'])->result_array())===2,'and the refill rows are rewritten to that voucher');
}finally{repairFiles($p);}

$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=refillFixture('0');$before=snapshot($ci);
    $m=(new Payment_posted_repair($ci,null,'refill_pending'))->prepare($p);
    $elig=0;foreach($m['items'] as $i)if($i['status']==='eligible')$elig++;
    check($elig===4&&statusOf($m,'BK--0826-0046')==='skipped','refill_pending: four groups are eligible');
    check(strpos(reasonOf($m,'BK--0826-0046'),'sama dengan id sebuah approval Refill')!==false,'a voucher equal to a refill approval id is refused with that reason (BK--0826-0014 is also a refill approval id)');
    check($before===snapshot($ci),'refill_pending dry-run changes nothing');
    $r=new Payment_posted_repair($ci,null,'refill_pending');$r->run($p);
    $db=$ci->db;
    check(count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0826-0030'])->result_array())===2&&count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0826-0031'])->result_array())===2&&count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0826-0029'])->result_array())===2&&count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0826-0032'])->result_array())===2,'pending refill rows use id_payment');
    check(count($db->get_where('tr_jurnal',['no_transaksi'=>'BK--0826-0046'])->result_array())===2&&$db->get_where('tr_jurnal',['no_transaksi'=>'BK--0826-0046'])->row_array()['sts']==='0','the refused group keeps its key and stays pending');
    $now=snapshot($ci);
    check($now['accounting_stm:japh']===$before['accounting_stm:japh']&&$now['accounting_stm:jurnal']===$before['accounting_stm:jurnal']&&$now['accounting_vuca:japh']===$before['accounting_vuca:japh'],'pending refill never touches accounting');
    $v=$r->verify($p);check($v['groups']===4&&$v['tr_jurnal']===8&&$v['japh']===0&&$v['jurnal']===0,'pending verify matches the backup except no_transaksi');
    (new Payment_posted_repair($ci,null,'refill_pending'))->rollback($p);
    check(snapshot($ci)['tr']===$before['tr'],'pending rollback restores the journals');
}finally{repairFiles($p);}

$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=refillFixture('0');$ci->load->dbs['accounting_stm']->insert('japh',['nomor'=>'X2','no_reff'=>'RPC-BK--0826-0050','jml'=>'100.00']);
    $m=(new Payment_posted_repair($ci,null,'refill_pending'))->prepare($p);
    check(statusOf($m,'BK--0826-0050')==='skipped'&&statusOf($m,'BK--0826-0043,BK--0826-0044,BK--0826-0045')==='eligible','a pending refill that already has an accounting record is skipped, not forced');
    check(strpos(reasonOf($m,'BK--0826-0050'),'catatan accounting')!==false,'the skip names the accounting record as the reason');
}finally{repairFiles($p);}

// ===== shared batch: one key used by a Payment AND a Refill journal (BK--1026-0040,...), both posted =====
const SH_KEY='BK--1026-0040,BK--1026-0006,BK--1026-0008,BK--1026-0039,BK--1026-0041,BK--1026-0042';
const SH_VOUCHER='BK--1026-0002';
const SH_CUT='BK--1026-0040,BK--1026-00';
function sharedFixture()
{
    $ci=repairFixture();$db=$ci->db;$stm=$ci->load->dbs['accounting_stm'];
    foreach([['BK--1026-0040','cash'],['BK--1026-0006','periodik'],['BK--1026-0008','periodik'],['BK--1026-0039','refill_pettycash'],['BK--1026-0041','cash'],['BK--1026-0042','cash']] as $a)
        $db->insert('payment_approve',['id'=>$a[0],'id_payment'=>SH_VOUCHER,'no_doc'=>'DOC-'.$a[0],'tipe'=>$a[1]]);
    $db->insert('tr_payment_paid',['id'=>SH_VOUCHER]);
    $n=600;
    foreach([['Payment',[[300,0],[0,300]],'101BK2600013','V'],['Refill Pettycash',[[100,0],[0,100]],'101BKA2600008','']] as $g){
        list($jenis,$money,$nomor,$ap)=$g;
        foreach($money as $m){
            $db->insert('tr_jurnal',['id'=>++$n,'no_transaksi'=>SH_KEY,'jenis_transaksi'=>$jenis,'sts'=>'1','id_company'=>'7','nm_company'=>'C','tgl_jurnal'=>'2026-10-05','coa'=>'5101','nm_coa'=>'X','debit'=>$m[0],'kredit'=>$m[1],'keterangan'=>$jenis.' line']);
            $stm->insert('jurnal',['tipe'=>'BUK','nomor'=>$nomor,'tanggal'=>'2026-10-05','no_reff'=>SH_CUT,'no_perkiraan'=>'5101','keterangan'=>$jenis.' line','debet'=>$m[0],'kredit'=>$m[1]]);
        }
        $stm->insert('japh',['nomor'=>$nomor,'tgl'=>'2026-10-05','jml'=>$money[0][0].'.00','kdcab'=>'101','jenis_reff'=>'BUK','no_reff'=>SH_CUT,'jenis_ap'=>$ap,'note'=>$ap==='V'?'Payment '.SH_KEY:null,'user_id'=>$ap==='V'?'finance':'203','ho_valid'=>'','batal'=>'0']);
    }
    return $ci;
}

$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=sharedFixture();$before=snapshot($ci);
    $m=(new Payment_posted_repair($ci,null,'shared'))->prepare($p);
    check(statusOf($m,SH_KEY)==='eligible','shared: the Payment+Refill group is eligible');
    check($before===snapshot($ci),'shared dry-run changes nothing');
    assertNoJaphOrderById($ci);
    $r=new Payment_posted_repair($ci,null,'shared');$r->run($p);
    $db=$ci->db;$stm=$ci->load->dbs['accounting_stm'];
    check(count($db->get_where('tr_jurnal',['no_transaksi'=>SH_VOUCHER,'jenis_transaksi'=>'Payment'])->result_array())===2&&count($db->get_where('tr_jurnal',['no_transaksi'=>SH_VOUCHER,'jenis_transaksi'=>'Refill Pettycash'])->result_array())===2,'both the Payment and the Refill rows now use the shared voucher');
    check(!$db->get_where('tr_jurnal',['no_transaksi'=>SH_KEY])->result_array(),'no row keeps the id list');
    // the base fixture also holds an unrelated header (101BK2600001); only this group's two headers may change
    $h=$stm->where_in('nomor',['101BK2600013','101BKA2600008'])->order_by('nomor')->get('japh')->result_array();
    check(count($h)===2&&$h[0]['no_reff']===SH_VOUCHER&&$h[1]['no_reff']===SH_VOUCHER,'both japh headers follow the voucher');
    check($stm->get_where('japh',['nomor'=>'101BK2600001'])->row_array()['no_reff']===OLD_STM,'an unrelated header is not touched');
    check($h[0]['jenis_ap']==='V'&&$h[1]['jenis_ap']===''&&$h[0]['note']==='Payment '.SH_VOUCHER&&$h[1]['note']===null,'jenis_ap is untouched so the two headers stay distinguishable; only the Payment note follows');
    check(count($stm->get_where('jurnal',['no_reff'=>SH_VOUCHER,'nomor'=>'101BK2600013'])->result_array())===2&&count($stm->get_where('jurnal',['no_reff'=>SH_VOUCHER,'nomor'=>'101BKA2600008'])->result_array())===2,'jurnal lines of each header follow the voucher');
    // aliases: a Payment row resolves to the non-refill approvals only, the Refill row to the refill approval only
    $al=$db->get('tr_jurnal_payment_reference_aliases')->result_array();$byJenis=['Payment'=>[],'Refill Pettycash'=>[]];
    foreach($al as $a){$j=$db->get_where('tr_jurnal',['id'=>$a['journal_id']])->row_array()['jenis_transaksi'];$ids=json_decode($a['approval_ids'],true);sort($ids);$byJenis[$j][json_encode($ids)]=1;}
    check(array_keys($byJenis['Payment'])===[json_encode(['BK--1026-0006','BK--1026-0008','BK--1026-0040','BK--1026-0041','BK--1026-0042'])],'Payment aliases hold the five non-refill approvals');
    check(array_keys($byJenis['Refill Pettycash'])===[json_encode(['BK--1026-0039'])],'Refill aliases hold only the refill approval');
    check(count($al)===4,'every changed row has an alias');
    $v=$r->verify($p);
    check($v['groups']===1&&$v['tr_jurnal']===4&&$v['japh']===2&&$v['jurnal']===4,'shared verify matches the backup except the reference columns');
    $mid=snapshot($ci);(new Payment_posted_repair($ci,null,'shared'))->run($p);
    check($mid===snapshot($ci),'shared apply again is a no-op');
    (new Payment_posted_repair($ci,null,'shared'))->rollback($p);
    $after=snapshot($ci);
    check($after['tr']===$before['tr']&&$after['accounting_stm:japh']===$before['accounting_stm:japh']&&$after['accounting_stm:jurnal']===$before['accounting_stm:jurnal'],'shared rollback restores journals and both headers byte for byte');
}finally{repairFiles($p);}

foreach(['after_japh','after_jurnal','before_commit'] as $point){
    $p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
    try{
        $ci=sharedFixture();$before=snapshot($ci);
        (new Payment_posted_repair($ci,null,'shared'))->prepare($p);
        rejects(function()use($ci,$p,$point){(new Payment_posted_repair($ci,$point,'shared'))->run($p);},'Simulated failure');
        check($before===snapshot($ci),'shared failure at '.$point.' restores both headers and every journal exactly');
    }finally{repairFiles($p);}
}

$sharedGuards=[
    'a third journal kind uses the key'=>function($ci){$ci->db->insert('tr_jurnal',['id'=>990,'no_transaksi'=>SH_KEY,'jenis_transaksi'=>'Kasbon','sts'=>'1']);},
    'only the Payment journal exists'=>function($ci){$ci->db->query("DELETE FROM tr_jurnal WHERE jenis_transaksi='Refill Pettycash'");},
    'a row of the group is not posted'=>function($ci){$ci->db->query("UPDATE tr_jurnal SET sts='0' WHERE jenis_transaksi='Refill Pettycash'");},
    'the voucher holds a second refill approval'=>function($ci){$ci->db->query("UPDATE payment_approve SET tipe='refill_pettycash' WHERE id='BK--1026-0040'");},
    'the voucher holds no refill approval'=>function($ci){$ci->db->query("UPDATE payment_approve SET tipe='cash' WHERE id='BK--1026-0039'");},
    'an extra approval joined the voucher'=>function($ci){$ci->db->insert('payment_approve',['id'=>'EXTRA','id_payment'=>SH_VOUCHER,'no_doc'=>'Z','tipe'=>'cash']);},
    'the voucher is already used by a journal'=>function($ci){$ci->db->insert('tr_jurnal',['id'=>991,'no_transaksi'=>SH_VOUCHER,'jenis_transaksi'=>'Payment','sts'=>'0']);},
    'a japh header amount differs from its journal'=>function($ci){$ci->load->dbs['accounting_stm']->query("UPDATE japh SET jml='999.00' WHERE nomor='101BKA2600008'");},
    'both japh headers claim to be Payment'=>function($ci){$ci->load->dbs['accounting_stm']->query("UPDATE japh SET jenis_ap='V' WHERE nomor='101BKA2600008'");},
    'a japh header is missing'=>function($ci){$ci->load->dbs['accounting_stm']->query("DELETE FROM japh WHERE nomor='101BKA2600008'");},
    'a jurnal line uses a number outside the headers'=>function($ci){$ci->load->dbs['accounting_stm']->query("UPDATE jurnal SET nomor='OTHER' WHERE nomor='101BKA2600008'");},
    'the voucher is already used in accounting'=>function($ci){$ci->load->dbs['accounting_vuca']->insert('japh',['nomor'=>'X9','no_reff'=>SH_VOUCHER,'jml'=>'1.00']);},
];
foreach($sharedGuards as $name=>$mutate){
    $p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
    try{
        $ci=sharedFixture();$mutate($ci);
        $m=(new Payment_posted_repair($ci,null,'shared'))->prepare($p);
        check(statusOf($m,SH_KEY)==='skipped','shared guard: '.$name.' skips the group');
    }finally{repairFiles($p);}
}

// the resolver after the repair: the Payment row keeps its five approvals and never picks up the refill approval
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=sharedFixture();
    (new Payment_posted_repair($ci,null,'shared'))->prepare($p);(new Payment_posted_repair($ci,null,'shared'))->run($p);
    $res=new Payment_reference($ci);
    $rows=$ci->db->order_by('id')->get_where('tr_jurnal',['jenis_transaksi'=>'Payment','no_transaksi'=>SH_VOUCHER])->result_array();
    $r=$res->resolve(SH_VOUCHER,$rows);
    $ids=array_map('strval',array_column($r['approvals'],'id'));sort($ids);
    check($r['voucher']===SH_VOUCHER&&$ids===['BK--1026-0006','BK--1026-0008','BK--1026-0040','BK--1026-0041','BK--1026-0042'],'resolver: the shared voucher resolves the Payment rows to the five non-refill approvals');
}finally{repairFiles($p);}

// ===== pending_payment batch: group B keyed by an approval id that is the voucher of group A (real shape: BK--0926-0017) =====
const PP_B_KEY='BK--0926-0017'; const PP_B_VOUCHER='BK--0826-0015';
const PP_A_KEY='BK--0926-0123,BK--0926-0122,BK--0926-0116,BK--0926-0117';
function pendingPaymentFixture()
{
    $ci=repairFixture();$db=$ci->db;
    // B: Cash approval BK--0926-0017 paid under voucher BK--0826-0015 (a transport approval carries the voucher's number as its id)
    $db->insert('payment_approve',['id'=>PP_B_KEY,'id_payment'=>PP_B_VOUCHER,'no_doc'=>'DPM-2026-00022','tipe'=>'Cash']);
    $db->insert('payment_approve',['id'=>PP_B_VOUCHER,'id_payment'=>null,'no_doc'=>'RQ-2026-00140','tipe'=>'transport']);
    $db->insert('tr_payment_paid',['id'=>PP_B_VOUCHER]);
    // A: two kasbon + two refill approvals paid under voucher BK--0926-0017 (the same number as B's approval id)
    foreach([['BK--0926-0123','refill_pettycash'],['BK--0926-0122','refill_pettycash'],['BK--0926-0116','kasbon'],['BK--0926-0117','kasbon']] as $a)
        $db->insert('payment_approve',['id'=>$a[0],'id_payment'=>PP_B_KEY,'no_doc'=>'DOC-'.$a[0],'tipe'=>$a[1]]);
    $db->insert('tr_payment_paid',['id'=>PP_B_KEY]);
    $n=700;
    foreach([[PP_B_KEY,'',[[8750,0],[0,218],[0,8532]]],[PP_A_KEY,'3',[[340,0],[0,340]]]] as $g){
        foreach($g[2] as $m)$db->insert('tr_jurnal',['id'=>++$n,'no_transaksi'=>$g[0],'jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>$g[1],'nm_company'=>$g[1]===''?'':'C','tgl_jurnal'=>'2026-09-24','coa'=>'5101','nm_coa'=>'X','debit'=>$m[0],'kredit'=>$m[1],'keterangan'=>'Payment line']);
    }
    return $ci;
}
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=pendingPaymentFixture();
    rejects(function()use($ci){(new Payment_reference($ci))->inspect(PP_A_KEY,true,true);},'bertabrakan');
    $before=snapshot($ci);
    $m=(new Payment_posted_repair($ci,null,'pending_payment'))->prepare($p);
    check(statusOf($m,PP_B_KEY)==='eligible','pending_payment: group B is eligible');
    check($before===snapshot($ci),'pending_payment dry-run changes nothing');
    $r=new Payment_posted_repair($ci,null,'pending_payment');$r->run($p);
    $db=$ci->db;
    check(count($db->get_where('tr_jurnal',['no_transaksi'=>PP_B_VOUCHER,'jenis_transaksi'=>'Payment'])->result_array())===3&&!$db->get_where('tr_jurnal',['no_transaksi'=>PP_B_KEY])->result_array(),'group B rows now use their real voucher and none keeps the colliding key');
    check($db->get_where('tr_jurnal',['no_transaksi'=>PP_B_VOUCHER])->row_array()['sts']==='0','group B stays pending');
    $alias=$db->where('active',1)->get('tr_jurnal_payment_reference_aliases')->result_array();
    check(count($alias)===3&&$alias[0]['original_no_transaksi']===PP_B_KEY&&$alias[0]['voucher']===PP_B_VOUCHER&&json_decode($alias[0]['approval_ids'],true)===[PP_B_KEY],'aliases point the voucher at the Cash approval, not at the transport approval with the same number');
    $resolved=(new Payment_reference($ci))->resolve(PP_B_VOUCHER);
    check($resolved['voucher']===PP_B_VOUCHER&&array_column($resolved['approvals'],'id')===[PP_B_KEY],'resolver maps the voucher to the Cash approval through the alias');
    $ctx=(new Payment_reference($ci))->inspect(PP_A_KEY,true,true);
    check($ctx['voucher']===PP_B_KEY&&$ctx['target']==='accounting_sustain','group A is no longer blocked by the collision and still targets its own voucher');
    $v=$r->verify($p);check($v['groups']===1&&$v['tr_jurnal']===3&&$v['japh']===0&&$v['jurnal']===0,'pending_payment verify matches the backup except no_transaksi');
    $mid=snapshot($ci);(new Payment_posted_repair($ci,null,'pending_payment'))->run($p);
    check($mid===snapshot($ci),'pending_payment apply again is a no-op');
    (new Payment_posted_repair($ci,null,'pending_payment'))->rollback($p);
    check(snapshot($ci)['tr']===$before['tr'],'pending_payment rollback restores the journals');
}finally{repairFiles($p);}

// multi-token key of mixed approval types (real shape: BK--0826-0018 = Cash + 3 periodik + kasbon), cross-company rows, one transport approval sharing the voucher's number
const PM_KEY='BK--0926-0015,BK--0926-0016,BK--0926-0022,BK--0926-0089,BK--0926-0020'; const PM_VOUCHER='BK--0826-0018';
$p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=repairFixture();$db=$ci->db;
    foreach([['BK--0926-0015','Cash'],['BK--0926-0016','periodik'],['BK--0926-0022','kasbon'],['BK--0926-0089','periodik'],['BK--0926-0020','periodik']] as $a)
        $db->insert('payment_approve',['id'=>$a[0],'id_payment'=>PM_VOUCHER,'no_doc'=>'DOC-'.$a[0],'tipe'=>$a[1]]);
    $db->insert('payment_approve',['id'=>PM_VOUCHER,'id_payment'=>null,'no_doc'=>'RQ-2026-00107','tipe'=>'transport']);
    $db->insert('tr_payment_paid',['id'=>PM_VOUCHER]);
    $n=800;foreach([['',100,0],['7',0,60],['4',0,40]] as $m)$db->insert('tr_jurnal',['id'=>++$n,'no_transaksi'=>PM_KEY,'jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>$m[0],'nm_company'=>$m[0]===''?'':'C','tgl_jurnal'=>'2026-09-25','coa'=>'5101','nm_coa'=>'X','debit'=>$m[1],'kredit'=>$m[2],'keterangan'=>'Payment line']);
    $m=(new Payment_posted_repair($ci,null,'pending_payment'))->prepare($p);
    check(statusOf($m,PM_KEY)==='eligible','pending_payment: a multi-token key of mixed approval types is eligible');
    (new Payment_posted_repair($ci,null,'pending_payment'))->run($p);
    check(count($db->get_where('tr_jurnal',['no_transaksi'=>PM_VOUCHER,'jenis_transaksi'=>'Payment'])->result_array())===3&&!$db->get_where('tr_jurnal',['no_transaksi'=>PM_KEY])->result_array(),'multi-token key rows now use the voucher');
    $alias=$db->where('active',1)->get('tr_jurnal_payment_reference_aliases')->result_array();
    $ids=json_decode($alias[0]['approval_ids'],true);sort($ids);
    check(count($alias)===3&&$ids===['BK--0926-0015','BK--0926-0016','BK--0926-0020','BK--0926-0022','BK--0926-0089'],'aliases carry the five approvals, not the transport approval that shares the voucher number');
    $r=(new Payment_reference($ci))->resolve(PM_VOUCHER);$got=array_column($r['approvals'],'id');sort($got);
    check($r['voucher']===PM_VOUCHER&&$got===['BK--0926-0015','BK--0926-0016','BK--0926-0020','BK--0926-0022','BK--0926-0089'],'resolver maps the voucher to the five approvals');
}finally{repairFiles($p);}

$ppGuards=[
    'voucher already used by another journal'=>function($ci){$ci->db->insert('tr_jurnal',['id'=>990,'no_transaksi'=>PP_B_VOUCHER,'jenis_transaksi'=>'Payment','sts'=>'0']);},
    'voucher holds an extra approval'=>function($ci){$ci->db->insert('payment_approve',['id'=>'EXTRA','id_payment'=>PP_B_VOUCHER,'no_doc'=>'Z','tipe'=>'Cash']);},
    'group is already posted'=>function($ci){$ci->db->query("UPDATE tr_jurnal SET sts='1' WHERE no_transaksi='".PP_B_KEY."'");},
    'accounting already references the key'=>function($ci){$ci->load->dbs['accounting_stm']->insert('japh',['nomor'=>'X9','no_reff'=>PP_B_KEY,'jml'=>'100.00']);},
    'payment record is missing'=>function($ci){$ci->db->delete('tr_payment_paid',['id'=>PP_B_VOUCHER]);},
];
foreach($ppGuards as $name=>$mutate){
    $p=dirname(__DIR__,4).'/scratch/posted-repair-'.bin2hex(random_bytes(6)).'.json';
    try{
        $ci=pendingPaymentFixture();$mutate($ci);
        $m=(new Payment_posted_repair($ci,null,'pending_payment'))->prepare($p);
        check(statusOf($m,PP_B_KEY)==='skipped','pending_payment guard: '.$name.' blocks the group');
    }finally{repairFiles($p);}
}

echo 'PASS Payment posted repair: '.($checks-$repairBase)." assertions\n";
