<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Payment_reference.php';

class Payment_patch
{
    private $ci;
    public function __construct($ci) { $this->ci = $ci; }

    private function environment()
    {
        $db=$this->ci->db;
        return hash('sha256',json_encode([(string)$db->hostname,(string)$db->port,(string)$db->database],JSON_THROW_ON_ERROR));
    }

    public function inventory()
    {
        $db = $this->ci->db;
        $q = $db->select('no_transaksi')->where('jenis_transaksi','Payment')->group_by('no_transaksi')->order_by('no_transaksi','asc')->get('tr_jurnal');
        if (!$q) throw new RuntimeException('Inventory Payment gagal.');
        $resolver = new Payment_reference($this->ci);
        $items = [];
        foreach ($q->result_array() as $group) {
            $key = $group['no_transaksi'];
            $item = ['key'=>$key,'status'=>'skipped'];
            try {
                $context = $resolver->inspect($key);
                $after = $context['rows'];
                foreach ($after as &$row) $row['no_transaksi'] = $context['voucher']; unset($row);
                if ($resolver->resolve($context['voucher'],$after)['voucher']!==$context['voucher']) throw new Payment_reference_issue('ambiguous','Normalisasi nomor akan menghilangkan konteks relasi jurnal; kandidat dilewati.');
                $item += ['voucher'=>$context['voucher'],'company'=>$context['company'],'ids'=>array_map('intval',array_column($context['rows'],'id')),
                    'before'=> $context['fingerprint'],'after'=>Payment_reference::fingerprint($after)];
                $item['status'] = $key === $context['voucher'] ? 'consistent' : 'eligible';
                $item['category'] = $item['status'];
            } catch (Throwable $error) {
                $item['category'] = $error instanceof Payment_reference_issue ? $error->category : ($error instanceof DomainException ? 'invalid' : 'unverified');
                $item['reason'] = $error instanceof DomainException ? $error->getMessage() : 'Pemeriksaan schema/koneksi belum dapat dipastikan; dilewati.';
            }
            $items[] = $item;
        }
        // Two legacy groups cannot both be rewritten into one canonical group.
        $targets = [];
        foreach ($items as $i=>$item) if ($item['status']==='eligible') $targets[$item['voucher']][] = $i;
        foreach ($targets as $indices) if (count($indices)>1) foreach ($indices as $i) { $items[$i]['status']='skipped'; $items[$i]['category']='collision'; $items[$i]['reason']='Beberapa kelompok menarget voucher yang sama; perlu rekonsiliasi.'; }
        return ['version'=>1,'environment'=>$this->environment(),'created_at'=>gmdate('c'),'items'=>$items];
    }

    public static function save($path, $manifest)
    {
        if (file_exists($path)) {
            $journal=$path.'.journal';
            if (is_link($journal)) throw new RuntimeException('Journal manifest symlink tidak diizinkan.');
            $handle=fopen($journal,'ab');
            if (!$handle) throw new RuntimeException('Journal manifest tidak dapat dibuat.');
            try {
                $json=json_encode($manifest,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
                if (!flock($handle,LOCK_EX) || fwrite($handle,$json)!==strlen($json) || !fflush($handle)) throw new RuntimeException('Checkpoint manifest gagal disimpan.');
                if (function_exists('fsync') && !fsync($handle)) throw new RuntimeException('Checkpoint manifest gagal disinkronkan.');
            } finally { fclose($handle); }
            return;
        }
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $temporary = $path.'.'.bin2hex(random_bytes(6)).'.tmp';
        $handle = fopen($temporary,'xb');
        if (!$handle) throw new RuntimeException('Manifest tidak dapat dibuat.');
        try {
            if (fwrite($handle,$json)!==strlen($json) || !fflush($handle)) throw new RuntimeException('Manifest gagal disimpan.');
            if (function_exists('fsync') && !fsync($handle)) throw new RuntimeException('Manifest gagal disinkronkan.');
        } finally { fclose($handle); }
        if (!rename($temporary,$path)) { unlink($temporary); throw new RuntimeException('Manifest gagal dipublikasikan.'); }
    }

    public static function load($path)
    {
        $manifest=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
        $journal=$path.'.journal';
        if (is_link($journal)) throw new DomainException('Journal manifest symlink tidak diizinkan.');
        if (is_file($journal)) {
            $handle=fopen($journal,'rb');
            if (!$handle) throw new RuntimeException('Checkpoint manifest tidak dapat dibaca.');
            try {
                while (($line=fgets($handle))!==false) {
                    if (substr($line,-1)!=="\n") throw new RuntimeException('Checkpoint manifest tidak lengkap; perlu pemeriksaan manual.');
                    $manifest=json_decode($line,true,512,JSON_THROW_ON_ERROR);
                }
                if (!feof($handle)) throw new RuntimeException('Pembacaan checkpoint manifest gagal.');
            } finally { fclose($handle); }
        }
        return $manifest;
    }

    public function run($path, $rollback = false)
    {
        $db = $this->ci->db;
        Payment_reference::acquire($db);
        try {
            $manifest = self::load($path);
            if (($manifest['version'] ?? null)!==1 || !isset($manifest['items']) || !is_array($manifest['items'])) throw new DomainException('Format manifest tidak valid.');
            if (($manifest['environment'] ?? null)!==$this->environment()) throw new DomainException('Manifest berasal dari environment database yang berbeda.');
            Payment_reference::transactional($db,['tr_jurnal','tr_jurnal_payment_attempts']);
            foreach ($manifest['items'] as &$item) {
                if (!in_array($item['status'],['eligible','applied','commit_unknown','rolled_back'],true)) continue;
                if (!isset($item['key'],$item['voucher'],$item['ids'],$item['before'],$item['after']) || !is_array($item['ids']) || !$item['ids']) throw new DomainException('Kandidat manifest tidak lengkap.');
                $ids = array_map('intval',$item['ids']);
                if (min($ids)<1 || count($ids)!==count(array_unique($ids))) throw new DomainException('ID manifest tidak valid.');
                $begun = false; $commitAttempted = false;
                try {
                    if (!$db->trans_begin()) throw new RuntimeException('Transaksi patch gagal dimulai.');
                    $begun = true;
                    $q = $db->query('SELECT * FROM tr_jurnal WHERE id IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY id FOR UPDATE',$ids);
                    if (!$q) throw new RuntimeException('Baris patch tidak dapat dikunci.');
                    $current = $q->result_array();
                    $fingerprint = Payment_reference::fingerprint($current);
                    $from = $rollback ? $item['voucher'] : $item['key'];
                    $to = $rollback ? $item['key'] : $item['voucher'];
                    $expected = $rollback ? $item['after'] : $item['before'];
                    $desired = $rollback ? $item['before'] : $item['after'];
                    if ($fingerprint===$desired) {
                        $resolver = new Payment_reference($this->ci);
                        $context = $resolver->inspect($to);
                        if ($context['fingerprint']!==$desired || $context['voucher']!==$item['voucher']) throw new DomainException('Kelompok no-op berubah.');
                        if (!$db->trans_commit()) throw new RuntimeException('Commit pemeriksaan no-op gagal.');
                        $begun = false; $item['status']=$rollback?'rolled_back':'applied';
                        self::save($path,$manifest); continue;
                    }
                    if ($fingerprint!==$expected || count($current)!==count($ids)) throw new DomainException('Fingerprint berubah sejak dry-run; kandidat tidak diubah.');
                    $resolver = new Payment_reference($this->ci);
                    $context = $resolver->inspect($from);
                    if ($context['fingerprint']!==$expected || $context['voucher']!==$item['voucher'] || $context['company']!==$item['company']) throw new DomainException('Mapping/anggota Payment berubah.');
                    $projected=$current;
                    foreach ($projected as &$row) $row['no_transaksi']=$to; unset($row);
                    if ($resolver->resolve($to,$projected)['voucher']!==$item['voucher']) throw new Payment_reference_issue('ambiguous','Perubahan nomor akan menghilangkan konteks relasi jurnal.');
                    if ($rollback) {
                        if ($resolver->rows($to)) throw new DomainException('Nomor rollback bertabrakan dengan kelompok lain.');
                    }
                    $item['status']='commit_unknown';
                    self::save($path,$manifest);
                    if (!$db->where_in('id',$ids)->where('jenis_transaksi','Payment')->where('no_transaksi',$from)->update('tr_jurnal',['no_transaksi'=>$to])
                        || $db->affected_rows()!==count($ids) || !$db->trans_status()) throw new RuntimeException('Penulisan patch Payment gagal.');
                    $commitAttempted = true;
                    if (!$db->trans_commit()) throw new RuntimeException('Commit patch belum dapat dipastikan. Jalankan ulang pemeriksaan manifest.');
                    $begun = false;
                    $item['status']=$rollback?'rolled_back':'applied';
                    unset($item['error']);
                    self::save($path,$manifest);
                } catch (Throwable $error) {
                    if ($begun) $db->trans_rollback();
                    if (!$commitAttempted) $item['status']=$rollback?'applied':'eligible';
                    $item['error']=$error->getMessage();
                    self::save($path,$manifest);
                    // Stop after any failure; the manifest records exactly which groups committed.
                    throw $error;
                }
            }
            unset($item);
            return $manifest;
        } finally { Payment_reference::release($db); }
    }
}
