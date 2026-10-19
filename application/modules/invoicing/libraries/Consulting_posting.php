<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Invoice_consulting_service.php';

class Consulting_posting
{
    private $service;
    public function __construct($ci) { $this->service=new Invoice_consulting_service($ci); }

    private function ledger($owner, $tables)
    {
        $s=$this->service; $acc=$s->accounting($owner);
        $local=$s->one('SELECT @@server_id AS server_id,@@hostname AS host,@@port AS port');
        $remote=$acc->query('SELECT @@server_id AS server_id,@@hostname AS host,@@port AS port');
        if (!$remote || $remote->row_array()!==$local) throw new RuntimeException('Posting versi baru memerlukan finance dan accounting pada server transaksi yang sama.');
        $schema=$acc->database;
        if (!preg_match('/^[a-zA-Z0-9_]+$/D',$schema)) throw new RuntimeException('Schema accounting tidak valid.');
        foreach ($tables as $table) {
            $engine=$s->one('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?',array($schema,$table));
            if (!$engine || strtoupper($engine['ENGINE'])!=='INNODB') throw new RuntimeException('Tabel accounting '.$table.' harus InnoDB untuk posting atomik.');
        }
        return '`'.$schema.'`.';
    }

    public function invoice($journalId)
    {
        $s=$this->service;
        $r=$s->mutate('post_consulting_invoice',function()use($s,$journalId){
            if (!has_permission('Jurnal_Invoicing.Manage') && !has_permission('Jurnal_All.Manage')) throw new RuntimeException('Tidak memiliki izin posting invoice.');
            $s->transactional(array('tr_invoicing','tr_jurnal','tr_kartu_piutang'));
            $anchor=$s->one("SELECT * FROM tr_jurnal WHERE id=? AND jenis_transaksi='Invoicing' FOR UPDATE",array($journalId));
            if (!$anchor) throw new RuntimeException('Jurnal berubah; muat ulang daftar.');
            $invoice=$s->one('SELECT * FROM tr_invoicing WHERE id=? FOR UPDATE',array($anchor['no_transaksi']));
            $snapshot=Invoice_consulting::snapshot($invoice);$owner=$snapshot['owner'];
            $rows=$s->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id FOR UPDATE",array($invoice['id']));
            $expected=Invoice_consulting::journal(Invoice_consulting::totals($invoice['total_nominal'],$invoice['tax_treatment']),$snapshot['accounts'],$snapshot['sales_role'],$invoice['no_invoice'],$invoice['id_spk_penawaran']);
            if(count($rows)!==count($expected))throw new RuntimeException('Jurnal invoice tidak sesuai snapshot.');
            foreach($rows as $i=>$row){
                $line=$expected[$i];
                if(!in_array((string)$row['sts'],array('0','9'),true) || $row['coa']!==$line['coa'] || (string)$row['id_company']!==(string)$owner['id']
                    || Invoice_calculation::cents($row['debit'])!==$line['debit'] || Invoice_calculation::cents($row['kredit'])!==$line['credit'])throw new RuntimeException('Jurnal sudah diposting atau berbeda dari snapshot.');
            }
            $gl=$this->ledger($owner['id'],array('javh','jurnal','pastibisa_tb_cabang'));
            $branch=$s->one('SELECT subcab,nomorJC FROM '.$gl.'pastibisa_tb_cabang WHERE nocab=? FOR UPDATE',array('101'));
            if(!$branch)throw new RuntimeException('Cabang accounting 101 belum tersedia.');
            $number='101-'.$branch['subcab'].'JV'.date('y',strtotime($invoice['tanggal_invoice'])).str_pad((int)$branch['nomorJC']+1,5,'0',STR_PAD_LEFT);
            $s->checked($s->db->insert($gl.'javh',array('nomor'=>$number,'tgl'=>$invoice['tanggal_invoice'],'jml'=>Invoice_calculation::decimal(array_sum(array_column($expected,'debit'))),
                'koreksi_no'=>'-','kdcab'=>'101','jenis'=>'JV','keterangan'=>$invoice['no_invoice'],'bulan'=>substr($invoice['tanggal_invoice'],5,2),'tahun'=>substr($invoice['tanggal_invoice'],0,4),
                'user_id'=>$s->ci->auth->user_id(),'memo'=>'','tgl_jvkoreksi'=>$invoice['tanggal_invoice'],'ho_valid'=>'')));
            foreach($rows as $row)$s->checked($s->db->insert($gl.'jurnal',array('tipe'=>'JV','nomor'=>$number,'tanggal'=>$row['tgl_jurnal'],'no_perkiraan'=>$row['coa'],
                'keterangan'=>$row['keterangan'],'no_reff'=>$invoice['no_invoice'],'debet'=>$row['debit'],'kredit'=>$row['kredit'],'stspos'=>1)));
            $s->checked($s->db->query('UPDATE '.$gl.'pastibisa_tb_cabang SET nomorJC=nomorJC+1 WHERE nocab=?',array('101')));
            $s->checked($s->db->insert('tr_kartu_piutang',array('tipe'=>'JV','nomor'=>$number,'tanggal'=>$invoice['tanggal_invoice'],'no_perkiraan'=>$snapshot['accounts']['receivable']['code'],
                'keterangan'=>$invoice['no_invoice'].' - '.$invoice['nm_customer'],'no_reff'=>$invoice['id'],'debet'=>$invoice['total_akhir_jurnal'],'kredit'=>0,
                'id_supplier'=>$invoice['id_customer'],'nama_supplier'=>$invoice['nm_customer'],'id_company'=>$owner['id'],'nm_company'=>$owner['nm_company'])));
            $s->checked($s->db->update('tr_jurnal',array('sts'=>'1'),array('no_transaksi'=>$invoice['id'],'jenis_transaksi'=>'Invoicing')));
            return array('accounting_numbers'=>array(array('company'=>(string)$owner['id'],'number'=>$number)));
        },'jurnal_invoicing');
        return array('save'=>$r['status'])+$r;
    }

    public function receipt($journalId)
    {
        $s=$this->service;
        return $s->mutate('post_consulting_receipt',function()use($s,$journalId){
            if(!has_permission('Jurnal_Penerimaan.Manage') && !has_permission('Jurnal_All.Manage'))throw new RuntimeException('Tidak memiliki izin posting penerimaan.');
            $s->transactional(array('tr_jurnal','tr_penerimaan_piutang','tr_penerimaan_piutang_detail'));
            $anchor=$s->one("SELECT * FROM tr_jurnal WHERE id=? AND jenis_transaksi='Penerimaan Piutang' FOR UPDATE",array($journalId));
            if(!$anchor)throw new RuntimeException('Jurnal penerimaan tidak ditemukan.');
            $header=$s->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=? AND consulting_batch=3 FOR UPDATE',array($anchor['no_transaksi']));
            if(!$header)throw new RuntimeException('Penerimaan bukan batch konsultasi baru.');
            $rows=$s->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang' ORDER BY id FOR UPDATE",array($header['no_surat']));
            $details=$s->rows('SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=? ORDER BY id FOR UPDATE',array($header['no_surat']));
            $expected=array();$actual=array();
            foreach($details as $detail){
                $snapshot=json_decode($detail['consulting_snapshot']??'',true);
                foreach(Invoice_consulting::receipt_journal($snapshot) as $line)$expected[]=json_encode(array($line['date'],$line['owner'],$line['company'],$line['coa'],$line['name'],$line['debit'],$line['credit'],$line['description']));
            }
            foreach($rows as $row)$actual[]=json_encode(array($row['tgl_jurnal'],(string)$row['id_company'],$row['nm_company'],$row['coa'],$row['nm_coa'],Invoice_calculation::cents($row['debit']),Invoice_calculation::cents($row['kredit']),$row['keterangan']));
            sort($expected,SORT_STRING);sort($actual,SORT_STRING);
            if(!$expected || $expected!==$actual)throw new RuntimeException('Jurnal penerimaan berbeda dari snapshot; perlu rekonsiliasi.');
            $groups=array();foreach($rows as $row){if(!in_array((string)$row['sts'],array('0','9'),true))throw new RuntimeException('Jurnal penerimaan sudah diposting.');$groups[$row['id_company']][]=$row;}
            if(!$groups)throw new RuntimeException('Jurnal penerimaan kosong.');
            $numbers=array();
            foreach($groups as $owner=>$lines){
                $debit=0;$credit=0;foreach($lines as $line){$debit+=Invoice_calculation::cents($line['debit']);$credit+=Invoice_calculation::cents($line['kredit']);}
                if($debit!==$credit)throw new RuntimeException('Jurnal per company tidak seimbang.');
                $gl=$this->ledger($owner,array('jarh','jurnal','pastibisa_tb_cabang'));
                $branch=$s->one('SELECT nobum FROM '.$gl.'pastibisa_tb_cabang WHERE nocab=? FOR UPDATE',array('101'));
                if(!$branch)throw new RuntimeException('Cabang accounting 101 belum tersedia.');
                $date=$lines[0]['tgl_jurnal'];$number='101BM'.date('y',strtotime($date)).str_pad((int)$branch['nobum']+1,5,'0',STR_PAD_LEFT);
                $s->checked($s->db->insert($gl.'jarh',array('nomor'=>$number,'tgl'=>$date,'jml'=>Invoice_calculation::decimal($debit),'kdcab'=>'101','jenis_reff'=>'BUM',
                    'no_reff'=>$header['no_surat'],'customer'=>$header['nm_customer'],'terima_dari'=>$s->ci->auth->user_name(),'jenis_ar'=>'BUM','note'=>$header['no_surat'],
                    'user_id'=>$s->ci->auth->user_id(),'tgl_invoice'=>$date)));
                foreach($lines as $line)$s->checked($s->db->insert($gl.'jurnal',array('tipe'=>'BUM','nomor'=>$number,'tanggal'=>$date,'no_perkiraan'=>$line['coa'],
                    'keterangan'=>$line['keterangan'],'no_reff'=>$header['no_surat'],'debet'=>$line['debit'],'kredit'=>$line['kredit'],'id_perusahaan'=>$owner,'nm_perusahaan'=>$line['nm_company'],'stspos'=>1)));
                $s->checked($s->db->query('UPDATE '.$gl.'pastibisa_tb_cabang SET nobum=nobum+1 WHERE nocab=?',array('101')));
                $numbers[]=array('company'=>(string)$owner,'number'=>$number);
            }
            $s->checked($s->db->update('tr_jurnal',array('sts'=>'1'),array('no_transaksi'=>$header['no_surat'],'jenis_transaksi'=>'Penerimaan Piutang')));
            return array('accounting_numbers'=>$numbers);
        },'jurnal_penerimaan');
    }
}
