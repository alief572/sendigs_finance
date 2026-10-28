<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Payment_patch.php';

/** Number-only maintenance. Posting eligibility remains owned by Payment_reference::inspect(). */
class Payment_normalization
{
    private $ci;
    private $backup;
    private $failedEvidence = [];

    public function __construct($ci) { $this->ci=$ci; }
    private function read($q) { if(!$q)throw new RuntimeException('Pembacaan normalisasi Payment gagal.');return $q->result_array(); }
    private function environment() { $db=$this->ci->db;return hash('sha256',json_encode([(string)$db->hostname,(string)$db->port,(string)$db->database],JSON_THROW_ON_ERROR)); }
    public static function digest($rows)
    {
        $lines=[];foreach($rows as $row){ksort($row);foreach($row as &$value)if($value!==null)$value=(string)$value;unset($value);$lines[]=json_encode($row,JSON_THROW_ON_ERROR);}sort($lines,SORT_STRING);
        return hash('sha256',implode("\n",$lines));
    }
    private function aliases($ids)
    {
        $result=[];foreach(array_chunk($ids,200) as $chunk)$result=array_merge($result,$this->read($this->ci->db->where('active',1)->where_in('journal_id',$chunk)->get('tr_jurnal_payment_reference_aliases')));
        return $result;
    }
    private function ready()
    {
        $db=$this->ci->db;
        if(!$db->table_exists('tr_jurnal_payment_reference_aliases'))throw new RuntimeException('Jalankan migrasi 008_payment_reference_aliases.sql terlebih dahulu.');
        if(!$db->table_exists('tr_jurnal_postings'))throw new RuntimeException('Metadata posting belum tersedia.');
        foreach(['tgl_posting','posted_by'] as $field)if(!$db->field_exists($field,'tr_jurnal'))throw new RuntimeException('Kolom bukti posting belum tersedia.');
    }
    private function sourceGuard($rows,$keys,$voucher)
    {
        $db=$this->ci->db;
        foreach($rows as $row)if($row['jenis_transaksi']!=='Payment'||!isset($row['sts'])||!in_array((string)$row['sts'],['0',''],true)||!in_array($row['tgl_posting'] ?? null,[null,''],true)||!in_array($row['posted_by'] ?? null,[null,''],true)){
            throw new Payment_reference_issue('posted','Kelompok posted/status tidak aman; normalisasi diblokir.');
        }
        $aliases=$this->aliases(array_column($rows,'id'));
        $keys=array_merge($keys,array_column($aliases,'original_no_transaksi'));
        $keys=array_values(array_unique(array_merge($keys,[$voucher])));
        if($this->read($db->where('jenis_transaksi','Payment')->where_in('no_transaksi',$keys)->get('tr_jurnal_postings')))throw new Payment_reference_issue('posted','Metadata posting kelompok sudah ada.');
        if($db->table_exists('tr_jurnal_payment_attempts'))foreach($this->read($db->get_where('tr_jurnal_payment_attempts',['voucher'=>$voucher])) as $attempt)if($attempt['status']!=='failed_safe')throw new Payment_reference_issue('reconciliation_required','Percobaan posting sudah ada; normalisasi diblokir.');
    }
    private function amounts($rows)
    {
        $debit=0;$credit=0;foreach($rows as $row){$debit+=Payment_reference::cents($row['debit']);$credit+=Payment_reference::cents($row['kredit']);}
        if(!$debit||$debit!==$credit)throw new Payment_reference_issue('unbalanced','Kelompok asal tidak balance atau kosong.');
    }
    private function evidence($references,$target)
    {
        $result=['verified'=>true,'tables'=>[]];$db=$this->ci->db;
        try {
            $rows=$this->read($db->group_start()->where_in('no_reff',$references)->or_where_in('no_request',$references)->group_end()->get('jurnaltras'));
            $result['tables']['source:jurnaltras']=['count'=>count($rows),'digest'=>self::digest($rows)];
            foreach(array_unique([$target,'accounting']) as $name){
                if(isset($this->failedEvidence[$name]))throw new RuntimeException('Pemeriksaan accounting sebelumnya tidak tersedia.');
                try {
                    $acc=$this->ci->load->database($name,true);
                    if(!$acc)throw new RuntimeException('Accounting tidak tersedia.');
                    $acc->db_debug=false;
                    foreach(['jurnal','japh'] as $table){
                        $rows=$this->read($acc->where_in('no_reff',$references)->get($table));
                        $result['tables'][$name.':'.$table]=['count'=>count($rows),'digest'=>self::digest($rows)];
                    }
                } catch(Throwable $error){$this->failedEvidence[$name]=true;throw $error;}
            }
        }catch(Throwable $error){$result['verified']=false;}
        return $result;
    }

    public function inventory()
    {
        $this->ready();$db=$this->ci->db;
        $all=$this->read($db->order_by('id','asc')->get_where('tr_jurnal',['jenis_transaksi'=>'Payment']));
        $all=array_values(array_filter($all,function($r){return $r['jenis_transaksi']==='Payment';}));
        $groups=[];foreach($all as $row)$groups[$row['no_transaksi']][]=$row;
        $approvals=$this->read($db->select('id,id_payment,no_doc,tipe')->get('payment_approve'));
        $known=[];foreach($approvals as $approval)$known[$approval['id']]=$approval;
        $resolver=new Payment_reference($this->ci,true);$resolver->prime(array_keys($groups),$known);
        $run=bin2hex(random_bytes(16));$created=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
        $cohorts=[];$unmapped=[];
        foreach($groups as $key=>$rows){
            $entry=['key'=>(string)$key,'ids'=>array_map('intval',array_column($rows,'id'))];$possible=[];$resolved=null;
            try{$resolved=$resolver->resolve((string)$key,$rows);$possible=[$resolved['voucher']];}
            catch(Throwable $error){
                $entry['reason']=$error instanceof DomainException?$error->getMessage():'Relasi sumber belum dapat dipastikan.';$entry['category']=$error instanceof Payment_reference_issue?$error->category:'invalid';
                $tokens=array_map('trim',explode(',',(string)$key));
                foreach($approvals as $approval)if(in_array($approval['id'],$tokens,true)||in_array((string)$approval['id_payment'],$tokens,true))if(trim((string)$approval['id_payment'])!=='')$possible[]=$approval['id_payment'];
            }
            if(!$possible){$unmapped[]=$entry;continue;}
            $entry['approval_ids']=$resolved?array_map('strval',array_column($resolved['approvals'],'id')):[];
            foreach(array_unique($possible) as $voucher){
                if(!isset($cohorts[$voucher]))$cohorts[$voucher]=['voucher'=>(string)$voucher,'groups'=>[],'rows'=>[],'references'=>[(string)$voucher],'issues'=>[]];
                $cohorts[$voucher]['groups'][]=$entry;$cohorts[$voucher]['rows']=array_merge($cohorts[$voucher]['rows'],$rows);
                if($resolved)$cohorts[$voucher]['references']=array_merge($cohorts[$voucher]['references'],$resolved['references']);
                else $cohorts[$voucher]['issues'][]=$entry['reason'];
            }
        }
        $items=[];
        foreach($cohorts as $voucher=>$cohort){
            $rows=$cohort['rows'];$keys=array_column($cohort['groups'],'key');$ids=array_map('intval',array_column($rows,'id'));sort($ids,SORT_NUMERIC);
            $item=['voucher'=>(string)$voucher,'groups'=>$cohort['groups'],'ids'=>$ids,'status'=>'skipped'];
            try{
                if($cohort['issues'])throw new Payment_reference_issue('ambiguous',implode(' ',$cohort['issues']));
                if(isset($groups[$voucher])&&!in_array((string)$voucher,$keys,true))throw new Payment_reference_issue('collision','Target voucher dimiliki kelompok lain yang relasinya berbeda.');
                $this->sourceGuard($rows,$keys,(string)$voucher);
                foreach($keys as $key)$this->amounts($groups[$key]);
                $companies=array_values(array_unique(array_map('strval',array_column($rows,'id_company'))));
                $dates=array_values(array_unique(array_column($rows,'tgl_jurnal')));
                if(count($companies)!==1||count($dates)!==1)throw new Payment_reference_issue('collision','Company/tanggal kelompok yang akan tergabung berbeda.');
                $target=Payment_reference::target($companies[0]);
                if(!preg_match('/^[1-9]\d{3}-\d{2}-\d{2}$/D',(string)$dates[0]))throw new DomainException('Tanggal jurnal tidak valid.');
                $beforeAliases=$this->aliases($ids);$aliasedIds=array_map('intval',array_column($beforeAliases,'journal_id'));$new=[];
                foreach($cohort['groups'] as $group)foreach($group['ids'] as $id)if(!in_array($id,$aliasedIds,true))$new[]=['run_id'=>$run,'journal_id'=>$id,'original_no_transaksi'=>$group['key'],'voucher'=>(string)$voucher,'approval_ids'=>json_encode($group['approval_ids'],JSON_THROW_ON_ERROR),'active'=>1,'created_at'=>$created];
                $after=$rows;foreach($after as &$row)$row['no_transaksi']=(string)$voucher;unset($row);
                $references=array_values(array_unique($cohort['references']));
                foreach($rows as $row)if(preg_match_all('/\{REF:(.*?)\}/',(string)$row['keterangan'],$matches))$references=array_merge($references,$matches[1]);
                $item+=['company'=>$companies[0],'date'=>$dates[0],'target'=>$target,'before'=>Payment_reference::fingerprint($rows),'after'=>Payment_reference::fingerprint($after),'alias_before'=>$beforeAliases,'new_aliases'=>$new,'references'=>array_values(array_unique($references))];
                $item['status']=count($keys)===1&&$keys[0]===(string)$voucher?'consistent':'eligible';
                $item['category']=$item['status'];
                if($item['status']==='eligible')$item['accounting_at_review']=$this->evidence($item['references'],$target);
            }catch(Throwable $error){$item['reason']=$error instanceof DomainException?$error->getMessage():'Prasyarat normalisasi belum dapat dipastikan.';$item['category']=$error instanceof Payment_reference_issue?$error->category:'invalid';}
            $items[]=$item;
        }
        foreach($unmapped as $entry)$items[]=['voucher'=>null,'groups'=>[$entry],'ids'=>$entry['ids'],'status'=>'skipped','category'=>$entry['category'],'reason'=>$entry['reason']];
        $metadata=$this->read($db->get_where('tr_jurnal_postings',['jenis_transaksi'=>'Payment']));
        $this->backup=['payment_rows'=>$all,'posting_metadata'=>$metadata,'aliases'=>$this->read($db->get('tr_jurnal_payment_reference_aliases'))];
        return ['kind'=>'payment_number_normalization','version'=>1,'run_id'=>$run,'environment'=>$this->environment(),'created_at'=>$created,'items'=>$items];
    }

    public function prepare($path)
    {
        Payment_reference::acquire($this->ci->db);
        try{
            if(file_exists($path)||file_exists($path.'.backup.json')||file_exists($path.'.journal'))throw new DomainException('Artefak batch sudah ada; gunakan nama baru.');
            $manifest=$this->inventory();Payment_patch::save($path.'.backup.json',$this->backup);
            $manifest['backup_sha256']=hash_file('sha256',$path.'.backup.json');Payment_patch::save($path,$manifest);return $manifest;
        }finally{Payment_reference::release($this->ci->db);}
    }

    private function checkMapping($item,$rows,$rollback=false,$alreadyDesired=false)
    {
        $db=$this->ci->db;$resolver=new Payment_reference($this->ci);
        $this->sourceGuard($rows,array_column($item['groups'],'key'),$item['voucher']);
        foreach($item['groups'] as $group){
            $q=$db->where_in('id',$group['approval_ids'])->get('payment_approve');$approvals=$this->read($q);
            if(count($approvals)!==count($group['approval_ids']))throw new DomainException('Approval sumber berubah.');
            foreach($approvals as $approval)if(trim((string)$approval['id_payment'])!==$item['voucher'])throw new DomainException('Voucher approval berubah sejak dry-run.');
        }
        if(count($this->read($db->get_where('tr_payment_paid',['id'=>$item['voucher']])))!==1)throw new DomainException('Pembayaran aktual tidak valid.');
        $related=$this->read($db->select('id')->get_where('payment_approve',['id_payment'=>$item['voucher']]));
        $relatedIds=array_map('strval',array_column($related,'id'));$keys=(!$rollback&&$alreadyDesired)?[]:array_column($item['groups'],'key');$keys[]=$item['voucher'];
        $q=$db->query("SELECT id,no_transaksi FROM tr_jurnal WHERE jenis_transaksi = 'Payment' ORDER BY id FOR UPDATE");
        $checked=[];
        foreach($this->read($q) as $row){
            if(in_array((int)$row['id'],$item['ids'],true))continue;
            $key=$row['no_transaksi'];
            if(in_array($key,$keys,true))throw new DomainException('Anggota/target kelompok berubah sejak dry-run.');
            if(!isset($checked[$key])&&array_intersect(array_map('trim',explode(',',$key)),$relatedIds)){
                try{$other=$resolver->resolve($key);}catch(Throwable $error){throw new DomainException('Relasi kelompok lain belum dapat dipastikan; normalisasi diblokir.');}
                if($other['voucher']===$item['voucher'])throw new DomainException('Anggota/target kelompok berubah sejak dry-run.');
                $checked[$key]=true;
            }
        }
    }

    private function validateItem($manifest,$item)
    {
        $ids=[];$keys=[];$expectedAliases=[];$aliasedIds=array_map('intval',array_column($item['alias_before'],'journal_id'));
        foreach($item['groups'] as $group){
            if(!is_string($group['key'])||$group['key']===''||in_array($group['key'],$keys,true)||!$group['ids']||!$group['approval_ids'])throw new DomainException('Kelompok manifest tidak valid.');
            $keys[]=$group['key'];foreach($group['approval_ids'] as $approvalId)if(!is_string($approvalId)||$approvalId==='')throw new DomainException('Approval manifest tidak valid.');
            foreach($group['ids'] as $id){
                if(!is_int($id)||$id<1||in_array($id,$ids,true))throw new DomainException('Partisi ID manifest tidak valid.');$ids[]=$id;
                if(!in_array($id,$aliasedIds,true))$expectedAliases[]=['run_id'=>$manifest['run_id'],'journal_id'=>$id,'original_no_transaksi'=>$group['key'],'voucher'=>$item['voucher'],'approval_ids'=>json_encode($group['approval_ids'],JSON_THROW_ON_ERROR),'active'=>1,'created_at'=>$manifest['created_at']];
            }
        }
        sort($ids,SORT_NUMERIC);$declared=$item['ids'];sort($declared,SORT_NUMERIC);
        if($ids!==$declared||self::digest($expectedAliases)!==self::digest($item['new_aliases']))throw new DomainException('Alias/anggota manifest di luar kelompok yang diizinkan.');
    }

    public static function loadManifest($path)
    {
        $manifest=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
        if(($manifest['kind'] ?? '')!=='payment_number_normalization'||!is_array($manifest['items'] ?? null))throw new DomainException('Manifest normalisasi tidak valid.');
        if(is_link($path.'.journal'))throw new DomainException('Journal symlink tidak diizinkan.');
        if(is_file($path.'.journal')){
            $handle=fopen($path.'.journal','rb');if(!$handle)throw new RuntimeException('Journal normalisasi tidak dapat dibaca.');
            try{while(($line=fgets($handle))!==false){
                if(substr($line,-1)!=="\n")throw new RuntimeException('Checkpoint normalisasi tidak lengkap.');
                $delta=json_decode($line,true,512,JSON_THROW_ON_ERROR);$index=$delta['item_index'] ?? null;
                if(($delta['normalization_checkpoint'] ?? null)!==1||($delta['run_id'] ?? '')!==$manifest['run_id']||!is_int($index)||!isset($manifest['items'][$index])||!is_array($delta['state'] ?? null))throw new DomainException('Checkpoint normalisasi tidak valid.');
                foreach($delta['state'] as $key=>$value){if(!in_array($key,['status','error','accounting_at_apply'],true))throw new DomainException('Field checkpoint tidak valid.');if($key==='error'&&$value===null)unset($manifest['items'][$index][$key]);else $manifest['items'][$index][$key]=$value;}
            }if(!feof($handle))throw new RuntimeException('Journal normalisasi gagal dibaca.');}finally{fclose($handle);}
        }
        return $manifest;
    }

    private function checkpoint($path,$manifest,$index)
    {
        $item=$manifest['items'][$index];$state=['status'=>$item['status'],'error'=>$item['error'] ?? null];
        if(isset($item['accounting_at_apply']))$state['accounting_at_apply']=$item['accounting_at_apply'];
        Payment_patch::save($path,['normalization_checkpoint'=>1,'run_id'=>$manifest['run_id'],'item_index'=>$index,'state'=>$state]);
    }

    public function run($path,$rollback=false)
    {
        $db=$this->ci->db;Payment_reference::acquire($db);
        try{
            $this->ready();Payment_reference::transactional($db,['tr_jurnal','tr_jurnal_payment_reference_aliases']);
            $manifest=self::loadManifest($path);
            if(($manifest['kind'] ?? '')!=='payment_number_normalization'||($manifest['version'] ?? null)!==1||($manifest['environment'] ?? '')!==$this->environment()||!preg_match('/^[a-f0-9]{32}$/D',$manifest['run_id'] ?? ''))throw new DomainException('Manifest normalisasi/environment tidak valid.');
            if(!is_file($path.'.backup.json')||hash_file('sha256',$path.'.backup.json')!==$manifest['backup_sha256'])throw new DomainException('Backup batch tidak sesuai.');
            foreach($manifest['items'] as $index=>&$item){
                if(!in_array($item['status'],['eligible','applied','rolled_back','commit_unknown'],true))continue;
                if(!$rollback&&$item['status']==='rolled_back')continue;
                $this->validateItem($manifest,$item);
                $begun=false;$commitAttempted=false;
                try{
                    if(!$db->trans_begin())throw new RuntimeException('Transaksi normalisasi gagal dimulai.');$begun=true;
                    $ids=$item['ids'];if(!$ids||count(array_unique($ids))!==count($ids)||min($ids)<1)throw new DomainException('ID manifest tidak valid.');
                    $rows=$this->read($db->query('SELECT * FROM tr_jurnal WHERE id IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY id FOR UPDATE',$ids));
                    $fingerprint=Payment_reference::fingerprint($rows);$expected=$rollback?$item['after']:$item['before'];$desired=$rollback?$item['before']:$item['after'];
                    if(count($rows)!==count($ids)||($fingerprint!==$expected&&$fingerprint!==$desired))throw new DomainException('Fingerprint jurnal berubah; tidak ada perubahan diterapkan.');
                    $this->checkMapping($item,$rows,$rollback,$fingerprint===$desired);
                    $active=$this->aliases($ids);$afterAliases=array_merge($item['alias_before'],$item['new_aliases']);
                    if($fingerprint===$desired){
                        $desiredAliases=$rollback?$item['alias_before']:$afterAliases;
                        if(self::digest($active)!==self::digest($desiredAliases))throw new DomainException('Alias no-op tidak sesuai manifest.');
                        if(!$db->trans_commit())throw new RuntimeException('Commit pemeriksaan no-op gagal.');$begun=false;
                        $item['status']=$rollback?'rolled_back':'applied';$this->checkpoint($path,$manifest,$index);continue;
                    }
                    if(self::digest($active)!==self::digest($rollback?$afterAliases:$item['alias_before']))throw new DomainException('Alias aktif berubah sejak pemeriksaan.');
                    if($rollback){
                        $proof=$item['accounting_at_apply'] ?? ['verified'=>false];$fresh=$this->evidence($item['references'],$item['target']);
                        if(!$proof['verified']||!$fresh['verified']||$proof!==$fresh)throw new DomainException('Bukti accounting berubah/tidak terverifikasi; rollback diblokir.');
                    }else{
                        $resolver=new Payment_reference($this->ci);
                        foreach($item['groups'] as $group){
                            $members=array_values(array_filter($rows,function($row)use($group){return in_array((int)$row['id'],$group['ids'],true);}));
                            $resolved=$resolver->resolve($group['key'],$members);
                            $a=$group['approval_ids'];$b=array_map('strval',array_column($resolved['approvals'],'id'));sort($a);sort($b);
                            if($resolved['voucher']!==$item['voucher']||$a!==$b)throw new DomainException('Mapping approval berubah sejak dry-run.');
                        }
                        $item['accounting_at_apply']=$this->evidence($item['references'],$item['target']);
                    }
                    $item['status']='commit_unknown';$this->checkpoint($path,$manifest,$index);
                    if(!$rollback&&$item['new_aliases']&&!$db->insert_batch('tr_jurnal_payment_reference_aliases',$item['new_aliases']))throw new RuntimeException('Penyimpanan alias gagal.');
                    foreach($item['groups'] as $group){
                        $from=$rollback?$item['voucher']:$group['key'];$to=$rollback?$group['key']:$item['voucher'];if($from===$to)continue;
                        if(!$db->where_in('id',$group['ids'])->where('jenis_transaksi','Payment')->where('no_transaksi',$from)->update('tr_jurnal',['no_transaksi'=>$to])||$db->affected_rows()!==count($group['ids']))throw new RuntimeException('Penulisan nomor Payment gagal.');
                    }
                    if($rollback&&$item['new_aliases']){
                        $newIds=array_column($item['new_aliases'],'journal_id');
                        if(!$db->where('run_id',$manifest['run_id'])->where('active',1)->where_in('journal_id',$newIds)->update('tr_jurnal_payment_reference_aliases',['active'=>0])||$db->affected_rows()!==count($newIds))throw new RuntimeException('Penonaktifan alias gagal.');
                    }
                    $actual=$this->read($db->where_in('id',$ids)->get('tr_jurnal'));
                    if(Payment_reference::fingerprint($actual)!==$desired)throw new RuntimeException('Verifikasi nomor hasil gagal.');
                    $resolver=new Payment_reference($this->ci);
                    $resultGroups=[];foreach($actual as $row)$resultGroups[$row['no_transaksi']][]=$row;
                    foreach($resultGroups as $key=>$members)if($resolver->resolve((string)$key,$members)['voucher']!==$item['voucher'])throw new DomainException('Relasi hasil normalisasi/rollback tidak dapat dipastikan.');
                    if(!$db->trans_status())throw new RuntimeException('Transaksi normalisasi tidak valid.');
                    $commitAttempted=true;if(!$db->trans_commit())throw new RuntimeException('Commit normalisasi tidak dapat dipastikan; periksa manifest sebelum rerun.');$begun=false;
                    $item['status']=$rollback?'rolled_back':'applied';unset($item['error']);$this->checkpoint($path,$manifest,$index);
                }catch(Throwable $error){
                    if($begun)$db->trans_rollback();if(!$commitAttempted)$item['status']=$rollback?'applied':'eligible';$item['error']=$error->getMessage();$this->checkpoint($path,$manifest,$index);throw $error;
                }
            }unset($item);return $manifest;
        }finally{Payment_reference::release($db);}
    }

    public function verifyAccounting($items)
    {
        $unverified=0;$verified=0;
        foreach($items as $item)if($item['status']==='applied'){
            $proof=$item['accounting_at_apply'] ?? ['verified'=>false];$fresh=$this->evidence($item['references'],$item['target']);
            if(!$proof['verified']||!$fresh['verified'])$unverified++;
            elseif($proof!==$fresh)throw new DomainException('Snapshot accounting berubah selama normalisasi.');
            else $verified++;
        }
        return ['verified_cohorts'=>$verified,'unverified_cohorts'=>$unverified];
    }

    public function verify($path)
    {
        $manifest=self::loadManifest($path);$backup=json_decode(file_get_contents($path.'.backup.json'),true,512,JSON_THROW_ON_ERROR);
        if(hash_file('sha256',$path.'.backup.json')!==$manifest['backup_sha256'])throw new DomainException('Backup tidak sesuai.');
        $expected=$backup['payment_rows'];$targets=[];
        foreach($manifest['items'] as $item)if($item['status']==='applied')foreach($item['ids'] as $id)$targets[$id]=$item['voucher'];
        foreach($expected as &$row)if(isset($targets[$row['id']]))$row['no_transaksi']=$targets[$row['id']];unset($row);
        $actual=$this->read($this->ci->db->order_by('id','asc')->get_where('tr_jurnal',['jenis_transaksi'=>'Payment']));
        $actual=array_values(array_filter($actual,function($row){return $row['jenis_transaksi']==='Payment';}));
        if(Payment_reference::fingerprint($expected)!==Payment_reference::fingerprint($actual))throw new DomainException('Snapshot Payment berbeda dari perubahan nomor yang diizinkan.');
        $metadata=$this->read($this->ci->db->get_where('tr_jurnal_postings',['jenis_transaksi'=>'Payment']));
        if(self::digest($metadata)!==self::digest($backup['posting_metadata']))throw new DomainException('Metadata posting berubah; perlu pemeriksaan.');
        $resolver=new Payment_reference($this->ci,true);$vouchers=[];foreach($manifest['items'] as $item)if($item['status']==='applied')$vouchers[]=$item['voucher'];$resolver->prime($vouchers);
        $byKey=[];foreach($actual as $row)$byKey[$row['no_transaksi']][]=$row;
        foreach($manifest['items'] as $item)if($item['status']==='applied'){
            if(self::digest($this->aliases($item['ids']))!==self::digest(array_merge($item['alias_before'],$item['new_aliases'])))throw new DomainException('Alias hasil berbeda dari manifest.');
            if($resolver->resolve($item['voucher'],$byKey[$item['voucher']] ?? [])['voucher']!==$item['voucher'])throw new DomainException('Relasi voucher hasil tidak valid.');
        }
        $accounting=$this->verifyAccounting($manifest['items']);
        $changed=0;foreach($backup['payment_rows'] as $row)if(isset($targets[$row['id']])&&$targets[$row['id']]!==$row['no_transaksi'])$changed++;
        return ['payment_rows'=>count($actual),'normalized_rows'=>count($targets),'changed_rows'=>$changed,'posted_rows'=>count(array_filter($actual,function($row){return (string)$row['sts']==='1';})),'fields_and_posted_unchanged'=>true,'metadata_unchanged'=>true,'accounting_unchanged'=>$accounting['unverified_cohorts']===0,'accounting_unverified_cohorts'=>$accounting['unverified_cohorts']];
    }
}
