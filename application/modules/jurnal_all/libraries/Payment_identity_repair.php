<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Payment_normalization.php';

/** One-off description repair; financial records and posting state are immutable. */
class Payment_identity_repair
{
    const PENDING = ['BK--0826-0016','BK--0826-0017','BK--0826-0020','BK--1126-0007','BK--1126-0008','BK--1126-0010','BK--1126-0018','BK--1126-0019','BK--1126-0020','BK--1126-0021'];
    const POSTED = ['BK--0926-0011','BK--0926-0014','BK--0926-0018','BK--1126-0011','BK--1126-0015','BK--1126-0012','BK--1126-0027','BK--1126-0028','BK--1126-0029','BK--1126-0030','BK--1126-0031','BK--1126-0025'];
    private $ci;
    private $specs;
    /** $specs null = kohort tetap (specs()); batch keterangan memberi daftar sendiri (lihat screen()). */
    public function __construct($ci,$specs=null) { $this->ci=$ci;$this->specs=$specs ?? self::specs(); }
    private function read($q) { if(!$q)throw new RuntimeException('Pemeriksaan identity repair gagal.');return $q->result_array(); }
    private function environment() { $d=$this->ci->db;return hash('sha256',json_encode([$d->hostname,$d->port ?? '',$d->database],JSON_THROW_ON_ERROR)); }
    public static function specs()
    {
        $out=[];
        foreach(array_merge(self::PENDING,self::POSTED) as $v){
            $pending=in_array($v,self::PENDING,true);
            $sustain=in_array($v,['BK--1126-0018','BK--1126-0019','BK--1126-0020','BK--1126-0021','BK--1126-0027','BK--1126-0028','BK--1126-0029','BK--1126-0030','BK--1126-0031','BK--1126-0025'],true);
            $out[$v]=['type'=>$v==='BK--0926-0018'?'Refill Pettycash':'Payment','status'=>$pending?'0':'1','target'=>$sustain?'accounting_sustain':'accounting_stm'];
        }
        return $out;
    }
    public static function replaceApproval($text,$ids,$voucher)
    {
        foreach($ids as $id){
            $suffix=' - '.$id;
            if($id!==$voucher && substr((string)$text,-strlen($suffix))===$suffix)return substr($text,0,-strlen($id)).$voucher;
        }
        return $text;
    }
    private static function equal($a,$b)
    {
        if(array_keys($a)!==array_keys($b))return false;
        foreach($a as $table=>$rows)if(Payment_normalization::digest($rows)!==Payment_normalization::digest($b[$table]))return false;
        return true;
    }
    /** $a: koneksi accounting yang sedang memegang LOCK TABLES; koneksi lain akan menunggu lock itu tanpa akhir. */
    private function collect($v,$spec,$a=null)
    {
        $d=$this->ci->db;$a=$a ?: $this->ci->load->database($spec['target'],true);
        $rows=$this->read($d->order_by('id')->get_where('tr_jurnal',['no_transaksi'=>$v,'jenis_transaksi'=>$spec['type']]));
        if(!$rows)throw new DomainException('Kelompok tidak ditemukan: '.$v);
        return [
            'source'=>$rows,
            'approvals'=>$this->read($d->order_by('id')->get_where('payment_approve',['id_payment'=>$v])),
            'paid'=>$this->read($d->get_where('tr_payment_paid',['id'=>$v])),
            'aliases'=>$this->read($d->where('active',1)->where_in('journal_id',array_column($rows,'id'))->get('tr_jurnal_payment_reference_aliases')),
            'postings'=>$this->read($d->get_where('tr_jurnal_postings',['no_transaksi'=>$v,'jenis_transaksi'=>$spec['type']])),
            'attempts'=>$this->read($d->get_where('tr_jurnal_payment_attempts',['voucher'=>$v])),
            'jurnal'=>$this->read($a->order_by('id')->get_where('jurnal',['no_reff'=>$v])),
            'japh'=>$this->read($a->order_by('nomor')->get_where('japh',['no_reff'=>$v])),
            'counter'=>$this->read($a->get_where('pastibisa_tb_cabang',['nocab'=>'101']))
        ];
    }
    private static function money($rows,$source)
    {
        $signatures=[];$total=0;$credit=0;
        foreach($rows as $r){$d=Payment_reference::cents($r[$source?'debit':'debet']);$k=Payment_reference::cents($r['kredit']);$total+=$d;$credit+=$k;
            if($d||$k)$signatures[]=json_encode([substr($r[$source?'tgl_jurnal':'tanggal'],0,10),$r[$source?'coa':'no_perkiraan'],$d,$k]);
        }
        if(!$total||$total!==$credit)throw new DomainException('Jurnal tidak balance.');
        sort($signatures,SORT_STRING);return [$signatures,$total];
    }
    private function guard($v,$spec,$b)
    {
        if(count($b['paid'])!==1||!$b['approvals']||count($b['counter'])!==1)throw new DomainException('Relasi pembayaran/counter tidak valid: '.$v);
        if(count(array_unique(array_column($b['source'],'id_company')))!==1||count(array_unique(array_column($b['source'],'tgl_jurnal')))!==1)throw new DomainException('Kelompok melintasi company/tanggal: '.$v);
        foreach($b['source'] as $r){
            if($r['no_transaksi']!==$v||$r['jenis_transaksi']!==$spec['type']||(string)$r['sts']!==$spec['status'])throw new DomainException('Identitas/status berubah: '.$v);
            if($spec['status']==='0'&&(!empty($r['tgl_posting'])||!empty($r['posted_by'])))throw new DomainException('Bukti posting pending tidak aman.');
        }
        $ids=array_map('strval',array_column($b['approvals'],'id'));sort($ids,SORT_STRING);
        if($spec['type']==='Payment'){
            $r=(new Payment_reference($this->ci))->resolve($v);$resolved=array_map('strval',array_column($r['approvals'],'id'));sort($resolved,SORT_STRING);
            if($r['voucher']!==$v||$resolved!==$ids)throw new DomainException('Approval kelompok ambigu: '.$v);
            if(Payment_reference::target($b['source'][0]['id_company'])!==$spec['target'])throw new DomainException('Company berubah.');
        } else foreach($b['approvals'] as $r)if($r['tipe']!=='refill_pettycash')throw new DomainException('Refill bercampur approval lain.');
        list($signatures,$total)=self::money($b['source'],true);
        if($spec['status']==='0'){
            if($b['jurnal']||$b['japh']||$b['postings'])throw new DomainException('Pending sudah memiliki accounting/metadata: '.$v);
            foreach($b['attempts'] as $r)if($r['status']!=='failed_safe')throw new DomainException('Registry pending perlu rekonsiliasi.');
        } else {
            if(count($b['japh'])!==1||self::money($b['jurnal'],false)!==[$signatures,$total]||Payment_reference::cents($b['japh'][0]['jml'])!==$total||substr($b['japh'][0]['tgl'],0,10)!==substr($b['source'][0]['tgl_jurnal'],0,10))throw new DomainException('Rincian accounting berbeda: '.$v);
        }
    }
    public static function project($v,$spec,$b,$run,$created)
    {
        $after=$b;$ids=array_map('strval',array_column($b['approvals'],'id'));sort($ids,SORT_STRING);$covered=[];
        foreach($b['aliases'] as $alias){
            $id=(string)$alias['journal_id'];
            if(isset($covered[$id])||$alias['voucher']!==$v)throw new DomainException('Alias bertentangan.');
            $members=json_decode($alias['approval_ids'],true,512,JSON_THROW_ON_ERROR);
            if(!is_array($members)||!$members||array_diff($members,$ids))throw new DomainException('Approval alias berubah.');
            $covered[$id]=true;
        }
        foreach($after['source'] as &$row){
            $row['no_transaksi']=$v;$row['keterangan']=self::replaceApproval($row['keterangan'],$ids,$v);
            if($spec['type']==='Payment'&&!isset($covered[(string)$row['id']]))$after['aliases'][]=['run_id'=>$run,'journal_id'=>$row['id'],'original_no_transaksi'=>$b['source'][0]['no_transaksi'],'voucher'=>$v,'approval_ids'=>json_encode($ids,JSON_THROW_ON_ERROR),'active'=>1,'created_at'=>$created];
        }unset($row);
        foreach($after['jurnal'] as &$row)$row['keterangan']=self::replaceApproval($row['keterangan'],$ids,$v);unset($row);
        return $after;
    }
    /** Read-only: pisahkan voucher yang lolos guard/project dari yang tidak (alasan dilaporkan, tidak dipaksa). */
    public function screen($specs)
    {
        $ok=[];$skipped=[];$run=str_repeat('0',32);
        foreach($specs as $v=>$spec){
            try{$b=$this->collect($v,$spec);$this->guard($v,$spec,$b);$after=self::project($v,$spec,$b,$run,'1970-01-01 00:00:00');
                $n=0;foreach($b['source'] as $i=>$r)if($r['keterangan']!==$after['source'][$i]['keterangan'])$n++;
                if(!$n)throw new DomainException('Tidak ada keterangan yang berubah.');$ok[$v]=$spec;
            }catch(Throwable $e){$skipped[$v]=$e->getMessage();}
        }
        return [$ok,$skipped];
    }
    public function prepare($path)
    {
        foreach([$path,$path.'.backup.json',$path.'.journal'] as $file)if(is_link($file)||file_exists($file))throw new DomainException('Manifest/backup sudah ada.');
        $d=$this->ci->db;Payment_reference::transactional($d,['tr_jurnal','tr_jurnal_payment_reference_aliases']);
        $m=['kind'=>'payment_identity_repair','version'=>1,'environment'=>$this->environment(),'run_id'=>bin2hex(random_bytes(16)),'created_at'=>(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s'),'items'=>[]];$backup=[];
        foreach($this->specs as $v=>$spec){$b=$this->collect($v,$spec);$this->guard($v,$spec,$b);$after=self::project($v,$spec,$b,$m['run_id'],$m['created_at']);$backup[$v]=$b;
            $counts=['source'=>0,'jurnal'=>0,'aliases'=>count($after['aliases'])-count($b['aliases'])];foreach(['source','jurnal'] as $table)foreach($b[$table] as $i=>$r)if($r['keterangan']!==$after[$table][$i]['keterangan'])$counts[$table]++;
            $m['items'][$v]=['status'=>'prepared','changes'=>$counts];
        }
        Payment_patch::save($path.'.backup.json',$backup);$m['backup_sha256']=hash_file('sha256',$path.'.backup.json');Payment_patch::save($path,$m);return $m;
    }
    private function load($path)
    {
        $m=Payment_patch::load($path);
        if(($m['kind'] ?? '')!=='payment_identity_repair'||($m['version'] ?? null)!==1||($m['environment'] ?? '')!==$this->environment()||!preg_match('/^[a-f0-9]{32}$/D',$m['run_id'] ?? '')||array_keys($m['items'])!==array_keys($this->specs))throw new DomainException('Manifest/environment tidak sesuai batch.');
        if(is_link($path)||is_link($path.'.backup.json')||!is_file($path.'.backup.json')||hash_file('sha256',$path.'.backup.json')!==$m['backup_sha256'])throw new DomainException('Backup berubah/tidak tersedia.');
        $backup=json_decode(file_get_contents($path.'.backup.json'),true,512,JSON_THROW_ON_ERROR);
        if(array_keys($backup)!==array_keys($this->specs))throw new DomainException('Cakupan backup berubah.');
        return [$m,$backup];
    }
    private function accounting($a,$from,$to)
    {
        foreach($from as $i=>$r)if($r['keterangan']!==$to[$i]['keterangan']){
            if(!$a->where('id',$r['id'])->where('nomor',$r['nomor'])->where('no_reff',$r['no_reff'])->where('keterangan',$r['keterangan'])->update('jurnal',['keterangan'=>$to[$i]['keterangan']])||$a->affected_rows()!==1)throw new RuntimeException('Update keterangan accounting gagal.');
        }
    }
    private function source($d,$from,$to,$run,$rollback)
    {
        foreach($from['source'] as $i=>$r){$next=$to['source'][$i];$set=[];foreach(['no_transaksi','keterangan'] as $field)if($r[$field]!==$next[$field])$set[$field]=$next[$field];
            if($set&&(!$d->where('id',$r['id'])->where('no_transaksi',$r['no_transaksi'])->where('keterangan',$r['keterangan'])->update('tr_jurnal',$set)||$d->affected_rows()!==1))throw new RuntimeException('Update staging gagal.');
        }
        if($rollback){
            $new=array_values(array_filter($from['aliases'],function($r)use($run){return $r['run_id']===$run;}));
            if($new&&(!$d->where('run_id',$run)->where('active',1)->where_in('journal_id',array_column($new,'journal_id'))->update('tr_jurnal_payment_reference_aliases',['active'=>0])||$d->affected_rows()!==count($new)))throw new RuntimeException('Rollback alias gagal.');
        } else {
            $new=array_values(array_filter($to['aliases'],function($r)use($run){return $r['run_id']===$run;}));
            if($new&&!$d->insert_batch('tr_jurnal_payment_reference_aliases',$new))throw new RuntimeException('Penyimpanan alias gagal.');
        }
    }
    private function restoreAccounting($a,$before,$after)
    {
        foreach($before as $i=>$r){$next=$after[$i];if($r['keterangan']===$next['keterangan'])continue;
            $now=$this->read($a->get_where('jurnal',['id'=>$r['id']]));
            if(Payment_normalization::digest($now)===Payment_normalization::digest([$r]))continue;
            if(Payment_normalization::digest($now)!==Payment_normalization::digest([$next]))throw new RuntimeException('Accounting berubah; kompensasi perlu rekonsiliasi.');
            $this->accounting($a,[$next],[$r]);
        }
    }
    public function run($path,$rollback=false)
    {
        $d=$this->ci->db;Payment_reference::acquire($d);
        try{
            Payment_reference::transactional($d,['tr_jurnal','tr_jurnal_payment_reference_aliases']);list($m,$backup)=$this->load($path);
            foreach($this->specs as $v=>$spec){
                $b=$backup[$v];$after=self::project($v,$spec,$b,$m['run_id'],$m['created_at']);$from=$rollback?$after:$b;$to=$rollback?$b:$after;
                $a=$this->ci->load->database($spec['target'],true);$begun=false;$locked=false;$committing=false;$writeAttempted=false;
                try{
                    if(!$d->trans_begin())throw new RuntimeException('Transaksi koreksi gagal dimulai.');$begun=true;
                    $ids=array_column($b['source'],'id');$this->read($d->query('SELECT id FROM tr_jurnal WHERE id IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY id FOR UPDATE',$ids));
                    $this->read($d->query('SELECT id FROM payment_approve WHERE id_payment=? ORDER BY id FOR UPDATE',[$v]));
                    // Pending tidak menulis accounting; LOCK TABLES di satu koneksi membuat collect() (koneksi lain) menunggu lock itu tanpa akhir.
                    if($spec['status']!=='0'){if(!$a->query('LOCK TABLES jurnal WRITE, japh READ, pastibisa_tb_cabang READ'))throw new RuntimeException('Lock accounting gagal.');$locked=true;}
                    $now=$this->collect($v,$spec,$a);
                    if(self::equal($now,$to)){$d->trans_rollback();$begun=false;$m['items'][$v]['status']=$rollback?'rolled_back':'applied';Payment_patch::save($path,$m);continue;}
                    if(!self::equal($now,$from)||(!$rollback&&($m['items'][$v]['status'] ?? '')==='reconcile'))throw new DomainException('Data/checkpoint berbeda; perlu rekonsiliasi: '.$v);
                    $this->guard($v,$spec,$now);$m['items'][$v]['status']=$rollback?'rolling_back':'applying';Payment_patch::save($path,$m);
                    $writeAttempted=true;$this->accounting($a,$from['jurnal'],$to['jurnal']);$this->source($d,$from,$to,$m['run_id'],$rollback);
                    if(!$d->trans_status()||!self::equal($this->collect($v,$spec,$a),$to))throw new RuntimeException('Verifikasi sebelum commit gagal.');
                    if($spec['type']==='Payment')$this->guard($v,$spec,$this->collect($v,$spec,$a));
                    $m['items'][$v]['status']='committing';Payment_patch::save($path,$m);$committing=true;
                    if(!$d->trans_commit())throw new RuntimeException('Commit tidak pasti; jangan ulangi tanpa rekonsiliasi.');$begun=false;
                    if(!self::equal($this->collect($v,$spec,$a),$to))throw new RuntimeException('Verifikasi sesudah commit gagal.');
                    $m['items'][$v]['status']=$rollback?'rolled_back':'applied';Payment_patch::save($path,$m);
                }catch(Throwable $e){
                    $safe=true;if($begun)$safe=$d->trans_rollback()!==false;
                    if(!$committing&&$writeAttempted){try{$this->restoreAccounting($a,$from['jurnal'],$to['jurnal']);}catch(Throwable $restore){$safe=false;}}
                    $m['items'][$v]['status']=$committing||!$safe?'reconcile':($rollback?'applied':'prepared');Payment_patch::save($path,$m);throw $e;
                }finally{if($locked&&!$a->query('UNLOCK TABLES'))throw new RuntimeException('Gagal melepas lock accounting; periksa koneksi.');}
            }
            return $m;
        }finally{Payment_reference::release($d);}
    }
    public function verify($path,$rollback=false)
    {
        list($m,$backup)=$this->load($path);$counts=['groups'=>0,'source'=>0,'jurnal'=>0,'aliases'=>0];
        foreach($this->specs as $v=>$spec){$expected=$rollback?$backup[$v]:self::project($v,$spec,$backup[$v],$m['run_id'],$m['created_at']);$now=$this->collect($v,$spec);
            if(!self::equal($now,$expected))throw new RuntimeException('Verifikasi exact gagal: '.$v);
            $this->guard($v,$spec,$now);$counts['groups']++;foreach(['source','jurnal','aliases'] as $key)$counts[$key]+=$m['items'][$v]['changes'][$key];
        }
        return $counts;
    }
}
