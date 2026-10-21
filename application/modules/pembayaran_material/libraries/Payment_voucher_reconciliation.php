<?php
require_once __DIR__.'/Payment_journal_validator.php';

class Payment_voucher_reconciliation
{
    const VOUCHER='BK--1026-0002';
    const NATIVE_KEY='BK--1026-0040,BK--1026-0006,BK--1026-0008,BK--1026-0039,BK--1026-0041,BK--1026-0042';
    const SOURCES=[
        'BK--1026-0006'=>['PERIODIK-2026-00181','periodik',540120200],
        'BK--1026-0008'=>['PERIODIK-2026-00177','periodik',147500000],
        'BK--1026-0039'=>['RPC-2026-0029','refill_pettycash',20700000],
        'BK--1026-0040'=>['DPM-2026-00081','cash',20000000],
        'BK--1026-0041'=>['DPM-2026-00083','cash',79700000],
        'BK--1026-0042'=>['DPM-2026-00082','cash',84000000]
    ];

    public static function tokens($value)
    {
        $tokens=preg_split('/[;,\s]+/',trim((string)$value),-1,PREG_SPLIT_NO_EMPTY);
        $tokens=array_values(array_unique($tokens));sort($tokens,SORT_STRING);return $tokens;
    }

    public static function matches($value,$keys)
    {
        foreach($keys as $key)if($key!=='' && preg_match('/(?<![A-Za-z0-9-])'.preg_quote($key,'/').'(?![A-Za-z0-9-])/D',(string)$value))return true;
        return false;
    }

    public static function fingerprint($rows)
    {
        $canonical=array_map(function($row){return array_map(function($value){return $value===null?null:(string)$value;},$row);},$rows);
        return hash('sha256',json_encode($canonical,JSON_THROW_ON_ERROR));
    }

    public static function assess($evidence)
    {
        $blockers=$evidence['collection_errors'];
        $approvals=$evidence['approvals'];$journal=$evidence['journal'];
        $seen=[];$sourceValid=count($approvals)===6;
        foreach($approvals as $p){
            $expected=self::SOURCES[$p['id']] ?? null;$seen[]=$p['id'];
            try{if(!$expected || $p['no_doc']!==$expected[0] || $p['tipe']!==$expected[1] || Payment_journal_validator::cents($p['jumlah'])!==$expected[2]
                || $p['id_payment']!==self::VOUCHER || $p['tgl_bayar']!=='2026-10-05' || (string)$p['status']!=='2'
                || Payment_journal_validator::cents($p['payment_bank'])!==892020200 || Payment_journal_validator::cents($p['total_payment'])!==892020200
                || Payment_journal_validator::cents($p['selisih'])!==0 || Payment_journal_validator::cents($p['kurs_payment'])!==100) $sourceValid=false;
            }catch(Invalid_payment_journal $e){$sourceValid=false;}
        }
        if(count(array_unique($seen))!==6)$sourceValid=false;
        if(!$sourceValid)$blockers[]='Sumber enam approval, nominal, tanggal, status, atau kurs berubah.';
        try{$paidValid=count($evidence['paid'])===1 && Payment_journal_validator::cents($evidence['paid'][0]['bank_charge'])===0;}
        catch(Invalid_payment_journal $e){$paidValid=false;}
        if(!$paidValid)$blockers[]='Header voucher/biaya admin tidak sesuai baseline.';
        $totals=[];$missingCompany=[];$companyIds=[];
        foreach($journal as $row){
            $type=$row['jenis_transaksi'];
            if(!isset($totals[$type]))$totals[$type]=['debit_cents'=>0,'credit_cents'=>0,'row_count'=>0];
            foreach(['debit'=>'debit_cents','kredit'=>'credit_cents'] as $field=>$target){
                try{$amount=Payment_journal_validator::cents($row[$field]);}
                catch(Invalid_payment_journal $e){$blockers[]='Nominal jurnal tidak valid pada baris '.$row['id'].'.';continue;}
                if($amount>PHP_INT_MAX-$totals[$type][$target])throw new RuntimeException('Total rekonsiliasi melampaui batas aman.');
                $totals[$type][$target]+=$amount;
            }
            $totals[$type]['row_count']++;
            if((string)$row['sts']!=='0' || !empty($row['tgl_posting']) || !empty($row['posted_by']))$blockers[]='Jurnal sudah diposting atau memiliki metadata posting.';
            if($row['no_transaksi']!==self::NATIVE_KEY)$blockers[]='Native key jurnal berubah.';
            if($type==='Payment' && ($row['debit']>0 || $row['kredit']>0)){
                if(trim((string)$row['id_company'])==='' || trim((string)$row['nm_company'])==='')$missingCompany[]=$row['id'];
                else $companyIds[(string)$row['id_company']]=true;
            }
        }
        if($missingCompany)$blockers[]='Identitas company kosong pada baris Payment: '.implode(', ',$missingCompany).'.';
        if(count($companyIds)!==1)$blockers[]='Company kelompok Payment tidak tunggal/terverifikasi.';
        if(($totals['Payment'] ?? null)!==['debit_cents'=>871320200,'credit_cents'=>0,'row_count'=>9]
            || ($totals['Refill Pettycash'] ?? null)!==['debit_cents'=>20700000,'credit_cents'=>20700000,'row_count'=>2]
            || count($journal)!==11 || count($totals)!==2)$blockers[]='Struktur/nominal jurnal berbeda dari kasus awal.';
        if(!$evidence['baseline_matches'])$blockers[]='Fingerprint jurnal berbeda dari snapshot awal.';
        if(!$evidence['snapshot_unchanged'])$blockers[]='Snapshot sumber berubah selama pemeriksaan.';
        if($evidence['postings'])$blockers[]='Metadata posting terkait ditemukan.';
        foreach($evidence['ledger'] as $name=>$entries)if($entries)$blockers[]='Catatan accounting/bank berpotensi bertumpang tindih: '.$name.'.';
        $bank=$evidence['bank'];
        if(!$bank || empty($bank['coa_verified']) || empty($bank['payment_source_consistent']))$blockers[]='Identitas bank sumber/COA belum konsisten dan terverifikasi.';
        // ms_bank has no authoritative company foreign key in this installation.
        $blockers[]='Owner company bank belum memiliki relasi master eksplisit; label STM agregat tidak digunakan sebagai bukti owner.';
        $blockers=array_values(array_unique($blockers));
        return [
            'voucher'=>self::VOUCHER,'mode'=>'READ ONLY; no apply mode','correction_status'=>'belum layak diterapkan','blockers'=>$blockers,
            'source_contract_matches'=>$sourceValid,'journal_totals'=>$totals,
            'candidate_credit_row'=>['no_transaksi'=>self::NATIVE_KEY,'jenis_transaksi'=>'Payment','tgl_jurnal'=>$sourceValid?'2026-10-05':null,
                'coa'=>!empty($bank['coa_verified'])?$bank['coa_bank']:null,'nm_coa'=>!empty($bank['coa_verified'])?$bank['coa_name']:null,'id_company'=>null,'nm_company'=>null,
                'debit'=>'0.00','kredit'=>'8713202.00','keterangan'=>'Usulan kredit bank pembayaran '.self::VOUCHER,
                'no_jurnal'=>null,'apply_ready'=>false],
            'evidence'=>$evidence
        ];
    }
}
