<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Invoice_calculation.php';

/** Caller must run this reader in a consistent READ ONLY transaction. */
class Invoice_audit
{
    private $db;
    public function __construct($db) { $this->db=$db; }
    private function cents($value) { return substr((string)$value,0,1)==='-' ? -Invoice_calculation::cents(substr($value,1)) : Invoice_calculation::cents($value); }
    public function report()
    {
        $result=array();
        foreach ($this->db->query('SELECT * FROM tr_invoicing ORDER BY id')->result_array() as $invoice) {
            $row=array('id'=>$invoice['id'],'no_invoice'=>$invoice['no_invoice'],'gross_stored'=>$invoice['tagihan_ppn_jurnal'],
                'net_stored'=>$invoice['total_akhir_jurnal'],'balance_stored'=>$invoice['saldo_piutang'],'issues'=>array(),'receipts'=>array());
            try {
                $dpp=$this->cents($invoice['total_nominal_jurnal']); $ppn=$this->cents($invoice['ppn_jurnal']); $pph=$this->cents($invoice['pph_jurnal']);
                $gross=$dpp+$ppn; $net=$gross-$pph; $balance=$this->cents($invoice['saldo_piutang']);
                if (min($dpp,$ppn,$pph,$net,$balance)<0) $row['issues'][]='negative_amount';
                if ($this->cents($invoice['tagihan_ppn_jurnal'])!==$gross) $row['issues'][]='gross_mismatch';
                if ($this->cents($invoice['total_akhir_jurnal'])!==$net) $row['issues'][]='net_mismatch';
                $journals=$this->db->query("SELECT * FROM tr_jurnal WHERE no_transaksi = ? AND jenis_transaksi = 'Invoicing' ORDER BY id",array($invoice['id']))->result_array();
                $debits=0; $credits=0; $opening=0; $receivableCount=0; $posted=false;
                foreach($journals as $j) {
                    $debits+=$this->cents($j['debit']); $credits+=$this->cents($j['kredit']); $posted=$posted || (string)$j['sts']==='1';
                    if ($j['coa']==='1102-01-01') { $opening+=$this->cents($j['debit'])-$this->cents($j['kredit']); $receivableCount++; }
                }
                $row['invoicing_posted']=$posted;
                if (!$journals || $debits!==$credits || $receivableCount!==1 || $opening!==$net) $row['issues'][]='invoicing_journal_mismatch';
                $history=$this->db->query('SELECT d.*, h.pph23_dipotong FROM tr_penerimaan_piutang_detail d LEFT JOIN tr_penerimaan_piutang h ON h.no_surat=d.id_header WHERE d.id_inv=? ORDER BY d.created_date,d.id',array($invoice['id']))->result_array();
                $running=$opening; $mapped=$receivableCount===1; $choices=array(); $totalPph=0;
                $journalHeaders=$this->db->query("SELECT DISTINCT no_transaksi FROM tr_jurnal WHERE jenis_transaksi='Penerimaan Piutang' AND keterangan=CONCAT(nm_coa,' - ',?)",array($invoice['no_invoice']))->result_array();
                $historyHeaders=array_column($history,'id_header');
                foreach($journalHeaders as $header) {
                    if (!in_array($header['no_transaksi'],$historyHeaders,true)) { $row['issues'][]='orphan_receipt_journal'; $mapped=false; }
                }
                foreach($history as $receipt) {
                    $dup=$this->db->query('SELECT COUNT(*) AS n FROM tr_penerimaan_piutang_detail d JOIN tr_invoicing i ON i.id=d.id_inv WHERE d.id_header=? AND i.no_invoice=?',array($receipt['id_header'],$invoice['no_invoice']))->row_array();
                    $js=$this->db->query("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang' ORDER BY id",array($receipt['id_header']))->result_array();
                    $reduction=0; $adjustment=0; $count=0; $pphCount=0; $postedReceipt=false; $invalid=false;
                    foreach($js as $j) {
                        if ($j['keterangan']!==$j['nm_coa'].' - '.$invoice['no_invoice']) continue;
                        $postedReceipt=$postedReceipt || (string)$j['sts']==='1';
                        if (in_array($j['coa'],array('1102-01-01','1106-01-02','1106-01-05'),true)) {
                            if ($this->cents($j['debit'])!==0 || $this->cents($j['kredit'])<0) $invalid=true;
                            if ($j['coa']==='1102-01-01') { $reduction+=$this->cents($j['kredit']); $count++; }
                            else { $adjustment+=$this->cents($j['kredit']); $pphCount++; }
                        }
                    }
                    $ok=(int)$dup['n']===1 && $count===1 && $pphCount<=1 && !$invalid;
                    if (!$ok) { $row['issues'][]='ambiguous_receipt_journal'; $mapped=false; }
                    if (!in_array($receipt['pph23_dipotong'],array('Y','N'),true)) $row['issues'][]='invalid_pph_choice';
                    if ($ok && $receipt['pph23_dipotong']==='Y' && $adjustment!==0) $row['issues'][]='pph_choice_journal_mismatch';
                    $choices[]=$receipt['pph23_dipotong']; $totalPph+=$adjustment;
                    $row['receipts'][]=array('number'=>$receipt['id_header'],'pph_choice'=>$receipt['pph23_dipotong'],'receipt'=>$receipt['penerimaan'],
                        'receivable_credit'=>$ok?Invoice_calculation::decimal($reduction):null,'pph_credit'=>$ok?Invoice_calculation::decimal($adjustment):null,
                        'posted'=>$postedReceipt,'balance_before'=>$mapped?Invoice_calculation::decimal($running):null,'balance_after'=>$mapped?Invoice_calculation::decimal($running-$reduction):null);
                    $running-=$reduction;
                }
                if (count(array_unique($choices))>1) $row['issues'][]='mixed_pph_choices';
                if ($totalPph>$pph) $row['issues'][]='repeated_pph_credit';
                $row['balance_reconstructed']=$mapped?Invoice_calculation::decimal($running):null;
                $row['balance_difference']=$mapped?Invoice_calculation::decimal($balance-$running):null;
                if ($mapped && $balance!==$running) $row['issues'][]='balance_mismatch';
                $row['status']=$row['issues']?'needs_reconciliation':'consistent';
            } catch (Throwable $e) {
                $row['issues'][]='unreadable_evidence'; $row['status']='needs_reconciliation'; $row['balance_reconstructed']=null; $row['balance_difference']=null;
            }
            $row['issues']=array_values(array_unique($row['issues'])); $result[]=$row;
        }
        return $result;
    }
}
