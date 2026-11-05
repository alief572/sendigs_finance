<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/Invoice_calculation.php';

/** Shared transaction boundary for all six financial save/update endpoints. */
class Invoice_writer
{
    private $db, $accounting, $model, $auth, $schema;
    public function __construct($db, $accounting, $model, $auth, $schema)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $schema)) { throw new RuntimeException('Schema tidak valid.'); }
        $this->db=$db; $this->accounting=$accounting; $this->model=$model; $this->auth=$auth; $this->schema='`'.$schema.'`.';
    }
    private function one($sql, $params = array()) { return $this->db->query($sql, $params)->row_array(); }
    private function required($post, $field)
    {
        if (!isset($post[$field]) || !is_scalar($post[$field]) || trim((string)$post[$field]) === '') { throw new InvalidArgumentException('Field '.$field.' wajib diisi.'); }
        return trim((string)$post[$field]);
    }
    private function checked($result)
    {
        if (!$result || !$this->db->trans_status()) { throw new RuntimeException('Penyimpanan invoice gagal; semua perubahan dibatalkan.'); }
    }
    private function require_transactional($non)
    {
        $tables=array('tr_invoicing','tr_jurnal','tr_penerimaan_piutang_detail','ms_invoice_sequence');
        $tables=array_merge($tables,$non?array('tr_invoice_detail_non_kons'):array('kons_tr_actual_plan_tagih','kons_tr_plan_tagih_detail'));
        foreach ($tables as $table) {
            $engine=$this->one('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',array($table));
            if (!$engine && $table==='ms_invoice_sequence') continue;
            if (!$engine || strtoupper($engine['ENGINE'])!=='INNODB') { throw new RuntimeException('Tabel '.$table.' harus mendukung transaksi InnoDB.'); }
        }
        if ($non) {
            $engine=$this->one('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?',array(trim($this->schema,'`.'),'kons_tr_penawaran_non_konsultasi'));
            if (!$engine || strtoupper($engine['ENGINE'])!=='INNODB') { throw new RuntimeException('Tabel penawaran harus mendukung locking InnoDB.'); }
        }
    }
    private function journals($id, $company, $t, $vuca, $revenue, $date)
    {
        if (!preg_match($vuca?'/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D':'/^4101-01-\d{2}$/', $revenue)) { throw new InvalidArgumentException('COA pendapatan tidak valid.'); }
        $taxCoa=$vuca?'1106-01-05':'1106-01-02';
        $codes=array('1102-01-01',$taxCoa,'2104-01-07',$revenue);
        $this->accounting->select('no_perkiraan, nama AS nm_coa');
        $this->accounting->from('coa_master'); $this->accounting->where_in('no_perkiraan',$codes);
        $master=$this->accounting->get()->result_array(); $map=array();
        foreach ($master as $row) { $map[$row['no_perkiraan']]=$row['nm_coa']; }
        if (count($master)!==4 || count($map)!==4 || array_diff($codes,array_keys($map))) { throw new RuntimeException('Master COA invoice tidak lengkap.'); }
        $rows=array(); $debitTotal=0; $creditTotal=0;
        foreach ($codes as $n=>$code) {
            $debit=$code==='1102-01-01'?$t['net']:($code===$taxCoa?$t['pph']:0);
            $credit=$code==='2104-01-07'?$t['ppn']:($code===$revenue?$t['dpp']:0);
            $debitTotal+=$debit; $creditTotal+=$credit;
            $rows[]=array('no_jurnal'=>$this->model->generate_id_invoice_jurnal($n+1),'tgl_jurnal'=>$date,'coa'=>$code,
                'id_company'=>$company['id'],'nm_company'=>$company['nm_company'],'nm_coa'=>$map[$code],
                'debit'=>Invoice_calculation::decimal($debit),'kredit'=>Invoice_calculation::decimal($credit),
                'keterangan'=>$map[$code].' - '.$id,'sts'=>'0','no_transaksi'=>$id,'jenis_transaksi'=>'Invoicing',
                'created_by'=>$this->auth->user_id(),'created_date'=>date('Y-m-d H:i:s'));
        }
        if ($debitTotal!==$creditTotal) { throw new RuntimeException('Jurnal invoice tidak seimbang.'); }
        return $rows;
    }
    public function save($kind, $edit, $post)
    {
        $locks=array(); $started=false; $oldDebug=$this->db->db_debug; $this->db->db_debug=false;
        try {
            $date=$this->required($post,'tanggal_invoice');
            $parsed=DateTime::createFromFormat('!Y-m-d',$date);
            if (!$parsed || $parsed->format('Y-m-d')!==$date) { throw new InvalidArgumentException('Tanggal invoice tidak valid.'); }
            // Receipt and invoice numbering both use MAX-based legacy generators.
            foreach (array('sendigs_receipt_v2','sendigs_invoice_write') as $name) {
                $lock=$this->one('SELECT GET_LOCK(?, 10) AS acquired',array($name));
                if ((int)$lock['acquired']!==1) { throw new RuntimeException('Transaksi lain sedang berjalan; coba kembali.'); }
                $locks[]=$name;
            }
            $this->checked($this->db->trans_begin()); $started=true;
            $vuca=$kind==='vuca'; $non=$kind==='non'; $existing=null;
            $this->require_transactional($non);
            if ($edit) {
                $id=$this->required($post,$non?'id':'id_invoicing');
                $existing=$this->one('SELECT * FROM tr_invoicing WHERE id = ? FOR UPDATE',array($id));
                if (!$existing || ($non !== ((string)$existing['non_kons']==='1')) || (!$non && $vuca!==((string)$existing['tipe_invoice']==='1'))) { throw new RuntimeException('Jenis invoice tidak sesuai.'); }
                if (!hash_equals(Invoice_calculation::fingerprint($existing),$this->required($post,'invoice_fingerprint'))) { throw new RuntimeException('Invoice berubah sejak form dibuka. Buka ulang form.'); }
                $history=$this->db->query('SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv = ? FOR UPDATE',array($id))->result_array();
                $oldJournals=$this->db->query("SELECT * FROM tr_jurnal WHERE no_transaksi = ? AND jenis_transaksi = 'Invoicing' FOR UPDATE",array($id))->result_array();
                if ($history) { throw new RuntimeException('Invoice sudah memiliki penerimaan; saldo tidak boleh direset.'); }
                foreach ($oldJournals as $j) { if ((string)$j['sts']==='1') { throw new RuntimeException('Invoice tidak dapat diupdate karena jurnal sudah diposting!'); } }
                // Missing detail records are not proof that no receipt occurred.
                $orphans=$this->db->query("SELECT id FROM tr_jurnal WHERE jenis_transaksi = 'Penerimaan Piutang' AND keterangan LIKE ? FOR UPDATE",array('% - '.$existing['no_invoice']))->result_array();
                if ($orphans) { throw new RuntimeException('Ada jurnal penerimaan terkait; invoice perlu rekonsiliasi.'); }
                if (Invoice_calculation::cents($existing['saldo_piutang'])!==Invoice_calculation::cents($existing['total_akhir_jurnal'])) { throw new RuntimeException('Saldo invoice berbeda dari saldo awal; perlu rekonsiliasi.'); }
            }
            if ($non) {
                $quoteId=$edit?$existing['id_penawaran']:$this->required($post,'id_penawaran');
                if ($edit && isset($post['id_penawaran']) && (string)$post['id_penawaran']!==(string)$quoteId) { throw new RuntimeException('Penawaran invoice tidak boleh berubah.'); }
                $quote=$this->one('SELECT * FROM '.$this->schema.'kons_tr_penawaran_non_konsultasi WHERE id_penawaran = ? FOR UPDATE',array($quoteId));
                if (!$quote) { throw new RuntimeException('Penawaran tidak ditemukan.'); }
                $shipping=$edit?$existing['biaya_kirim']:$this->required($post,'biaya_kirim');
                $itemData=Invoice_calculation::items($post['item']??null,$shipping);
                $t=Invoice_calculation::totals($itemData['dpp'],$this->required($post,'ppn_persen'));
                $issued=$this->one('SELECT COALESCE(SUM(total_nominal),0) AS total FROM tr_invoicing WHERE id_penawaran = ? AND id <> ?',array($quoteId,$edit?$id:''));
                if ($t['dpp']>Invoice_calculation::cents($quote['grand_total'])-Invoice_calculation::cents($issued['total'])) { throw new RuntimeException('Total DPP melebihi outstanding invoice.'); }
                $companyId=$quote['id_company'];
                $fields=array('id_penawaran'=>$quoteId,'nm_customer'=>$this->required($post,'nm_customer'),'address'=>$post['address']??'',
                    'nm_project'=>$quote['keterangan_penawaran'],'nm_sales'=>$quote['nm_pic_penawaran'],'biaya_kirim'=>Invoice_calculation::decimal(Invoice_calculation::cents($shipping,true)), 'non_kons'=>'1');
                if (!$edit) { $fields+=array('id_actual_plan_tagih'=>'0','id_detail_plan_tagih'=>0,'id_spk_penawaran'=>''); }
            } else {
                $actualId=$edit?$existing['id_actual_plan_tagih']:$this->required($post,'id');
                $actual=$this->one('SELECT * FROM kons_tr_actual_plan_tagih WHERE id = ? FOR UPDATE',array($actualId));
                if (!$actual) { throw new RuntimeException('Actual plan tagih tidak ditemukan.'); }
                $plan=$this->one('SELECT '.($vuca?'id, nominal_payment':'id').' FROM kons_tr_plan_tagih_detail WHERE id = ? FOR UPDATE',array($actual['id_detail_plan_tagih']));
                if (!$plan) { throw new RuntimeException('Detail plan tagih tidak ditemukan.'); }
                if (!$edit) {
                    $issued=$this->db->query('SELECT id FROM tr_invoicing WHERE id_actual_plan_tagih = ? FOR UPDATE',array($actualId))->result_array();
                    if ($issued || (string)$actual['sts_invoice']==='1') { throw new RuntimeException('Plan tagih sudah dibuatkan invoice.'); }
                }
                $spk=$this->one('SELECT * FROM '.$this->schema.'kons_tr_spk_penawaran WHERE id_spk_penawaran = ?',array($actual['id_spk_penawaran']));
                $quote=$this->one('SELECT * FROM '.$this->schema.'kons_tr_penawaran WHERE id_quotation = ?',array($actual['id_penawaran']));
                if (!$spk || !$quote) { throw new RuntimeException('SPK atau penawaran tidak ditemukan.'); }
                $companyId=!empty($quote['company'])?$quote['company']:($spk['id_company']??null);
                $dpp=Invoice_calculation::cents($edit?$existing['total_nominal_jurnal']:($vuca?$plan['nominal_payment']:$actual['nominal_payment']));
                $t=Invoice_calculation::totals($dpp,$vuca?'0':$this->required($post,'ppn_persen'),$vuca);
                $fields=array();
                if (!$edit) {
                    $project=$this->one('SELECT nm_paket FROM '.$this->schema.'kons_master_konsultasi_header WHERE id_konsultasi_h = ?',array($spk['id_project']));
                    if (!$project) { throw new RuntimeException('Project invoice tidak ditemukan.'); }
                    $fields=array('id_actual_plan_tagih'=>$actualId,'id_detail_plan_tagih'=>$actual['id_detail_plan_tagih'],'id_penawaran'=>$actual['id_penawaran'],
                        'id_spk_penawaran'=>$actual['id_spk_penawaran'],'nm_project'=>$project['nm_paket'],'tipe_invoice'=>$vuca?'1':'0','non_kons'=>'0');
                    foreach (array('id_customer','nm_customer','address','id_project','id_project_leader','nm_project_leader','id_sales','nm_sales') as $key) { $fields[$key]=$spk[$key]??''; }
                }
            }
            $company=$this->one('SELECT id, nm_company FROM '.$this->schema.'kons_tr_company WHERE id = ?',array($companyId));
            if (!$company || trim($company['nm_company'])==='') { throw new RuntimeException('Company invoice tidak valid.'); }
            $number=$edit?$this->required($post,'nomor_invoice'):$this->model->generate_no_invoice($company['id'],$vuca?'1':'0');
            if (!$edit) { $id=$this->model->generate_id(); }
            if ($edit && $number!==$existing['no_invoice']) {
                $duplicate=$this->one('SELECT id FROM tr_invoicing WHERE no_invoice = ? AND id <> ?',array($number,$id));
                if ($duplicate) { throw new RuntimeException('Nomor invoice sudah digunakan.'); }
            }
            $revenue='4101-01-01';
            if ($vuca) {
                require_once __DIR__.'/Invoice_vuca.php';
                if ($edit) {
                    $revenue=Invoice_vuca::saved_revenue($oldJournals)['coa'];
                } else {
                    $accounts=Invoice_vuca::sales_accounts($this->db,$this->accounting,$companyId,true);
                    $role=$this->required($post,'sales_role');
                    if (!isset($accounts[$role])) throw new InvalidArgumentException('Pilih COA penjualan Vuca yang valid.');
                    $revenue=$accounts[$role]['code'];
                }
                if (!$edit) {
                    $this->required($post,'nomor_po');
                    $this->required($post,'nomor_faktur');
                }
            }
            if ($non) {
                $revenue=!empty($post['jurnal_invoice_no_coa'])?$post['jurnal_invoice_no_coa']:'4101-01-01';
                if ($edit && empty($post['jurnal_invoice_no_coa'])) {
                    $oldRevenue=array_values(array_filter($oldJournals,function($j){return preg_match('/^4101-01-\d{2}$/',$j['coa']);}));
                    if (count($oldRevenue)!==1) { throw new RuntimeException('COA pendapatan historis ambigu.'); }
                    $revenue=$oldRevenue[0]['coa'];
                }
            }
            $journals=$this->journals($id,$company,$t,$vuca,$revenue,$date);
            $fields=array_merge($fields,Invoice_calculation::fields($t),array('tanggal_invoice'=>$date,'no_invoice'=>$number,
                'no_po'=>$post[$edit && !$non?'no_po':'nomor_po']??'','no_faktur'=>$post['nomor_faktur']??''));
            if ($vuca) $fields['pph_jurnal']=Invoice_calculation::decimal($t['pph']);
            if ($vuca && $edit) {
                foreach (array('dpp_nilai_lain','pajak','total_akhir') as $key) $fields[$key]=$existing[$key];
            }
            if ($vuca && !$edit) {
                // These legacy document fields differ from the zero-VAT journal fields.
                $legacyAmount=(float)Invoice_calculation::decimal($t['dpp']);
                $fields['dpp_nilai_lain']=$legacyAmount*11/12;
                $fields['pajak']=$fields['dpp_nilai_lain']*12/100;
                $fields['total_akhir']=$legacyAmount+$fields['pajak'];
            }
            if ($edit) {
                $this->checked($this->db->delete('tr_jurnal',array('no_transaksi'=>$id,'jenis_transaksi'=>'Invoicing')));
                $this->checked($this->db->update('tr_invoicing',$fields,array('id'=>$id)));
                if ($non) { $this->checked($this->db->delete('tr_invoice_detail_non_kons',array('id_header'=>$id))); }
            } else {
                $fields+=array('id'=>$id,'created_by'=>$this->auth->user_id(),'created_date'=>date('Y-m-d H:i:s'));
                $this->checked($this->db->insert('tr_invoicing',$fields));
            }
            if ($non) {
                $details=array();
                foreach ($itemData['details'] as $detail) { $details[]=array_merge($detail,array('id_header'=>$id,'input_by'=>$this->auth->user_id(),'input_at'=>date('Y-m-d H:i:s'))); }
                $this->checked($this->db->insert_batch('tr_invoice_detail_non_kons',$details));
            } elseif (!$edit) {
                $this->checked($this->db->update('kons_tr_actual_plan_tagih',array('sts_invoice'=>'1'),array('id'=>$actualId)));
                $this->checked($this->db->update('kons_tr_plan_tagih_detail',array('sts_invoice'=>'1','tgl_invoice'=>$date),array('id'=>$actual['id_detail_plan_tagih'])));
            }
            $this->checked($this->db->insert_batch('tr_jurnal',$journals));
            $this->checked($this->db->trans_commit()); $started=false;
            return array('status'=>1,'msg'=>$edit?'Data has been updated !':'Data has been saved !');
        } catch (Throwable $error) {
            if ($started) { $this->db->trans_rollback(); }
            return array('status'=>0,'msg'=>$error instanceof InvalidArgumentException || $error instanceof RuntimeException ? $error->getMessage() : 'Proses invoice gagal; perubahan dibatalkan.');
        } finally {
            foreach (array_reverse($locks) as $name) { $this->one('SELECT RELEASE_LOCK(?)',array($name)); }
            $this->db->db_debug=$oldDebug;
        }
    }
}
