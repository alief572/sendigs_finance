<?php
if(PHP_SAPI!=='cli')exit(2);
define('BASEPATH',true);define('ENVIRONMENT','development');require dirname(__DIR__).'/application/config/development/database.php';
$docs=['ER-2026-00033','ER-2026-00040','ER-2026-00041','ER-2026-00042','ER-2026-00044'];
$ids=[11228,11229,11247,11248,11253,11254,11255,11256,11264,11265];
$conn=mysqli_init();$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT,5);
if(!@$conn->real_connect($db['default']['hostname'],$db['default']['username'],$db['default']['password'],$db['default']['database']))throw new RuntimeException('Read-only connection unavailable');
if(!$conn->query('START TRANSACTION READ ONLY'))throw new RuntimeException('Read-only transaction unavailable');
function evidence_rows($sql){global $conn;$r=$conn->query($sql);if(!$r)throw new RuntimeException('Evidence query failed');return $r->fetch_all(MYSQLI_ASSOC);}
function evidence_hash($rows){$lines=array_map(function($row){ksort($row);return json_encode($row,JSON_THROW_ON_ERROR);},$rows);sort($lines,SORT_STRING);return hash('sha256',json_encode($lines,JSON_THROW_ON_ERROR));}
try{
    $keys=implode(',',array_map(function($doc)use($conn){return "'".$conn->real_escape_string($doc)."'";},$docs));
    $returns=evidence_rows("SELECT * FROM tr_jurnal WHERE no_transaksi IN ($keys) AND jenis_transaksi='Pengembalian Kasbon' ORDER BY id");
    $actual=array_map('intval',array_column($returns,'id'));sort($actual);$expected=$ids;sort($expected);
    if($actual!==$expected)throw new RuntimeException('Return row scope changed');
    $companies=[];$invariants=[];
    foreach($returns as $row){$companies[]=['id'=>$row['id'],'id_company'=>$row['id_company'],'nm_company'=>$row['nm_company'],'sts'=>$row['sts']];unset($row['id_company'],$row['nm_company']);$invariants[]=$row;}
    $snapshot=['database'=>$db['default']['database'],'return_invariants'=>evidence_hash($invariants),
        'realization'=>evidence_hash(evidence_rows("SELECT * FROM tr_jurnal WHERE no_transaksi IN ($keys) AND jenis_transaksi='Expense Report'")),
        'expense_headers'=>evidence_hash(evidence_rows("SELECT * FROM tr_expense WHERE no_doc IN ($keys)")),
        'posting_metadata'=>evidence_hash(evidence_rows("SELECT * FROM tr_jurnal_postings WHERE no_transaksi IN ($keys)")),
        'bank'=>evidence_hash(evidence_rows('SELECT * FROM ms_bank WHERE id=18')),'accounting'=>[]];
    foreach(['accounting_vuca','accounting_sustain','accounting_stm'] as $target){
        $schema=$db[$target]['database'];if(!preg_match('/^[A-Za-z0-9_]+$/D',$schema))throw new RuntimeException('Schema invalid');
        $like=implode(' OR ',array_map(function($doc)use($conn){return "keterangan LIKE '%".$conn->real_escape_string($doc)."%'";},$docs));
        $lines=evidence_rows("SELECT * FROM $schema.jurnal WHERE no_reff IN ($keys) OR $like");
        $numbers=array_unique(array_column($lines,'nomor'));
        $numberKeys=$numbers?implode(',',array_map(function($number)use($conn){return "'".$conn->real_escape_string($number)."'";},$numbers)):"''";
        $snapshot['accounting'][$target]=['details'=>evidence_hash($lines),'headers'=>evidence_hash(evidence_rows("SELECT * FROM $schema.javh WHERE nomor IN ($numberKeys) OR $like"))];
    }
    $result=['snapshot'=>$snapshot,'companies'=>$companies];
    if(($argv[1]??'')==='after'){
        $before=json_decode(file_get_contents(dirname(__DIR__).'/docs/expense-company-bank-before-2026-10-29.json'),true,512,JSON_THROW_ON_ERROR);
        if($before['snapshot']!==$snapshot)throw new RuntimeException('Non-company/ledger snapshot changed');
        foreach($companies as $row)if($row['id_company']!=='7'||$row['nm_company']!=='STM'||$row['sts']!=='0')throw new RuntimeException('Unexpected repaired company/status');
        $result['verified']='10 company rows corrected; amounts/date/COA/status/realization/expense/bank/metadata/accounting unchanged';
    }else foreach($companies as $row)if($row['id_company']!==''||$row['nm_company']!==''||$row['sts']!=='0')throw new RuntimeException('Initial scope is not blank pending');
    $path=dirname(__DIR__).'/docs/expense-company-bank-'.(($argv[1]??'')==='after'?'after':'before').'-2026-10-29.json';
    if(file_put_contents($path,json_encode($result,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL)===false)throw new RuntimeException('Snapshot file unavailable');
    echo json_encode(['rows'=>count($companies),'result'=>$result['verified']??'before snapshot captured']),PHP_EOL;
}finally{$conn->query('ROLLBACK');$conn->close();}
