<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Payment_reference.php';

class Payment_posting
{
    private $ci;
    public function __construct($ci) { $this->ci = $ci; }

    public function execute($id)
    {
        $ci = $this->ci; $db = $ci->db;
        if (!is_scalar($id) || !preg_match('/^[1-9]\d*$/D', (string)$id)) throw new DomainException('ID Payment tidak valid.');
        $locked = false; $sourceBegun = false; $targetBegun = false;
        $claimed = false; $commitAttempted = false; $acc = null; $voucher = null;
        $tablesLocked = false; $legacyWriteAttempted = false; $number = null;
        try {
            Payment_reference::acquire($db); $locked = true;
            $q = $db->get_where('tr_jurnal', ['id'=>(int)$id]);
            if (!$q || !($anchor = $q->row_array()) || $anchor['jenis_transaksi'] !== 'Payment') throw new DomainException('Anchor Payment tidak tersedia.');
            $resolver = new Payment_reference($ci);
            $context = $resolver->inspectForPosting($anchor['no_transaksi']);
            $voucher = $context['voucher'];
            $acc = $ci->load->database($context['target'], true);
            $acc->db_debug = false;
            $legacy = in_array('MYISAM', $context['target_engines'], true);
            $actor = (string)$ci->auth->user_id();
            if ($actor === '' || trim((string)$ci->auth->user_name()) === '') throw new DomainException('User posting tidak tersedia.');
            $now = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
            $attempt = ['source_key'=>$context['key'], 'fingerprint'=>$context['fingerprint'], 'status'=>'started', 'company'=>$context['company'], 'actor_id'=>$actor, 'updated_at'=>$now];
            if (!$db->trans_begin()) throw new RuntimeException('Transaksi registry Payment gagal.');
            $sourceBegun = true;
            $q = $db->get_where('tr_jurnal_payment_attempts', ['voucher'=>$voucher]);
            if (!$q) throw new RuntimeException('Registry Payment tidak dapat diperiksa.');
            $existing = $q->row_array();
            if ($existing && $existing['status'] !== 'failed_safe') throw new DomainException('Payment sudah dicoba; periksa rekonsiliasi.');
            $ok = $existing ? $db->where('voucher', $voucher)->update('tr_jurnal_payment_attempts', $attempt)
                : $db->insert('tr_jurnal_payment_attempts', array_merge($attempt, ['voucher'=>$voucher,'created_at'=>$now]));
            if (!$ok || !$db->trans_status() || !$db->trans_commit()) throw new RuntimeException('Commit registry Payment tidak dapat dipastikan; jangan ulangi tanpa pemeriksaan.');
            $sourceBegun = false; $claimed = true;
            if (!$db->trans_begin()) throw new RuntimeException('Transaksi staging Payment gagal.');
            $sourceBegun = true;
            $ids = array_column($context['rows'], 'id');
            $q = $db->query('SELECT * FROM tr_jurnal WHERE id IN ('.implode(',', array_fill(0, count($ids), '?')).') ORDER BY id FOR UPDATE', $ids);
            if (!$q || Payment_reference::fingerprint($q->result_array()) !== $context['fingerprint']) throw new DomainException('Jurnal Payment berubah; muat ulang sebelum posting.');
            if ($legacy) {
                if (!$acc->query('LOCK TABLES jurnal WRITE, japh WRITE, pastibisa_tb_cabang WRITE')) throw new RuntimeException('Lock accounting Payment gagal.');
                $tablesLocked = true;
            } else {
                if (!$acc->trans_begin()) throw new RuntimeException('Transaksi accounting Payment gagal.');
                $targetBegun = true;
            }
            $q = $acc->query('SELECT nocab,nobuk FROM pastibisa_tb_cabang WHERE nocab=?'.($legacy?'':' FOR UPDATE'), ['101']);
            if (!$q || $q->num_rows() !== 1 || !preg_match('/^\d+$/D',(string)$q->row()->nobuk)) throw new RuntimeException('Counter accounting Payment tidak valid.');
            $counter = (string)$q->row()->nobuk;
            // Use this connection: another connection cannot read a counter locked by LOCK TABLES.
            $number = $ci->Jurnal_payment_nomor_model->get_no_buk('101', $context['company'], $acc);
            if (!is_scalar($number) || trim((string)$number) === '') throw new RuntimeException('Nomor accounting Payment tidak tersedia.');
            foreach (['jurnal','japh'] as $table) {
                $references = $context['references'];
                foreach ($context['rows'] as $row) if (preg_match_all('/\{REF:(.*?)\}/',(string)$row['keterangan'],$matches)) $references=array_merge($references,$matches[1]);
                $q = $acc->where_in('no_reff',array_values(array_unique(array_filter(array_map('trim',$references),'strlen'))))->get($table);
                if (!$q || $q->num_rows()) throw new DomainException('Jurnal Payment sudah ada pada accounting company tujuan; posting ulang diblokir.');
                $q = $acc->get_where($table, ['nomor'=>$number]);
                if (!$q || $q->num_rows()) throw new RuntimeException('Nomor accounting Payment sudah digunakan.');
            }
            $details = []; $debit = 0;
            foreach ($context['rows'] as $row) {
                if (!Payment_reference::cents($row['debit']) && !Payment_reference::cents($row['kredit'])) continue;
                $description = trim(preg_replace('/\{REF:.*?\}/', '', (string)$row['keterangan']));
                $details[] = ['tipe'=>'BUK','nomor'=>$number,'tanggal'=>$row['tgl_jurnal'],'no_reff'=>$voucher,'no_perkiraan'=>$row['coa'],'keterangan'=>$description,'debet'=>$row['debit'],'kredit'=>$row['kredit']];
                $debit += Payment_reference::cents($row['debit']);
            }
            $header = ['nomor'=>$number,'tgl'=>$anchor['tgl_jurnal'],'jml'=>sprintf('%d.%02d', intdiv($debit, 100), $debit % 100), 'kdcab'=>'101','jenis_reff'=>'BUK','no_reff'=>$voucher,'jenis_ap'=>'V','note'=>'Payment '.$voucher,'user_id'=>$ci->auth->user_name(),'ho_valid'=>'','batal'=>'0'];
            $legacyWriteAttempted = $legacy;
            if (!$acc->insert_batch('jurnal', $details) || !$acc->insert('japh', $header)
                || !$acc->set('nobuk','nobuk + 1',false)->where('nocab','101')->where('nobuk',$counter)->update('pastibisa_tb_cabang')
                || $acc->affected_rows() !== 1 || !$acc->trans_status()) throw new RuntimeException('Penulisan accounting Payment gagal.');
            $this->verifyTarget($acc,$number,$details,$header,$counter);
            // The durable started claim blocks retries if either commit becomes uncertain.
            if (!$db->where('voucher',$voucher)->update('tr_jurnal_payment_attempts',['accounting_number'=>$number,'status'=>'committing','updated_at'=>$now])) throw new RuntimeException('Registry Payment gagal diperbarui.');
            $commitAttempted = true;
            if (!$legacy && !$acc->trans_commit()) throw new RuntimeException('Commit accounting Payment tidak dapat dipastikan.');
            $targetBegun = false;
            $update = ['sts'=>'1'];
            if ($db->field_exists('tgl_posting','tr_jurnal')) $update['tgl_posting'] = $now;
            if ($db->field_exists('posted_by','tr_jurnal')) $update['posted_by'] = $actor;
            if (!$db->where_in('id',$ids)->update('tr_jurnal',$update)
                || !$db->where('voucher',$voucher)->update('tr_jurnal_payment_attempts',['status'=>'committed','accounting_number'=>$number,'updated_at'=>$now])
                || !$db->trans_status() || !$db->trans_commit()) throw new RuntimeException('Accounting telah diproses, commit staging belum terkonfirmasi. Perlu rekonsiliasi; jangan posting ulang.');
            $sourceBegun = false;
            return ['save'=>1,'msg'=>'Jurnal Payment berhasil diposting.','accounting_numbers'=>[['company'=>$context['company'],'number'=>$number]]];
        } catch (Throwable $e) {
            $rolledBack = true;
            if ($targetBegun) $rolledBack = $acc->trans_rollback() !== false;
            if ($sourceBegun) $rolledBack = ($db->trans_rollback() !== false) && $rolledBack;
            if ($claimed) {
                $status = $commitAttempted || $legacyWriteAttempted || !$rolledBack ? 'reconcile' : 'failed_safe';
                try { if (!$db->where('voucher',$voucher)->update('tr_jurnal_payment_attempts',['status'=>$status,'accounting_number'=>$number])) throw new RuntimeException(); }
                catch (Throwable $ignore) { Payment_reference::log('Payment: status registry perlu rekonsiliasi.'); }
            }
            throw $e;
        } finally {
            if ($tablesLocked) {
                try { if (!$acc->query('UNLOCK TABLES')) throw new RuntimeException(); }
                catch (Throwable $ignore) { Payment_reference::log('Payment: lock accounting belum terkonfirmasi dilepas.'); }
            }
            if ($locked) Payment_reference::release($db);
        }
    }

    private function verifyTarget($acc,$number,$details,$header,$counter)
    {
        $q=$acc->get_where('jurnal',['nomor'=>$number]);
        if(!$q||$q->num_rows()!==count($details))throw new RuntimeException('Verifikasi detail accounting Payment gagal; perlu rekonsiliasi.');
        $normalize=function($rows){
            $result=[];
            foreach($rows as $row){$item=[];foreach(['tipe','nomor','no_reff','no_perkiraan','keterangan'] as $key)$item[$key]=(string)$row[$key];$item['tanggal']=substr((string)$row['tanggal'],0,10);foreach(['debet','kredit'] as $key)$item[$key]=Payment_reference::cents($row[$key]);$result[]=json_encode($item);}
            sort($result,SORT_STRING);return $result;
        };
        if($normalize($q->result_array())!==$normalize($details))throw new RuntimeException('Verifikasi nilai detail accounting Payment gagal; perlu rekonsiliasi.');
        $q=$acc->get_where('japh',['nomor'=>$number]);
        if(!$q||$q->num_rows()!==1)throw new RuntimeException('Verifikasi header accounting Payment gagal; perlu rekonsiliasi.');
        $actual=$q->row_array();
        foreach(['nomor','no_reff','jenis_reff'] as $key)if((string)$actual[$key]!==$header[$key])throw new RuntimeException('Verifikasi referensi accounting Payment gagal; perlu rekonsiliasi.');
        if(substr((string)$actual['tgl'],0,10)!==substr($header['tgl'],0,10)||Payment_reference::cents($actual['jml'])!==Payment_reference::cents($header['jml']))throw new RuntimeException('Verifikasi nominal header accounting Payment gagal; perlu rekonsiliasi.');
        $q=$acc->get_where('pastibisa_tb_cabang',['nocab'=>'101']);
        if(!$q||$q->num_rows()!==1||(int)$q->row()->nobuk!==(int)$counter+1)throw new RuntimeException('Verifikasi counter accounting Payment gagal; perlu rekonsiliasi.');
    }
}
