<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/Invoice_calculation.php';

class Invoice_consulting
{
    const VERSION = 3;

    public static function revenues()
    {
        return array('sales_consulting'=>'Penjualan Konsultasi', 'sales_inhouse'=>'Penjualan In House Training',
            'sales_public'=>'Penjualan Public Training & Seminar', 'sales_hse'=>'Penjualan HSE Pengujian');
    }

    public static function roles()
    {
        return array('receivable'=>'Piutang Dagang Usaha', 'vat_domestic'=>'Hutang PPN DN',
            'vat_wapu_prepaid'=>'Uang Muka PPN WAPU', 'vat_wapu_payable'=>'Hutang PPN WAPU', 'pph'=>'PPh 23') + self::revenues();
    }

    public static function is_new($invoice)
    {
        $invoice = (array)$invoice;
        return (int)($invoice['consulting_version'] ?? 0) === self::VERSION
            && (string)($invoice['tipe_invoice'] ?? '0') !== '1' && (string)($invoice['non_kons'] ?? '0') !== '1';
    }

    public static function totals($amount, $type)
    {
        if (!is_string($type) || !in_array($type, array('PKP','WAPU','KB'), true)) throw new InvalidArgumentException('Perlakuan PPN wajib dipilih.');
        $sales = Invoice_calculation::cents($amount);
        if ($sales <= 0 || $sales > 999999999999999) throw new InvalidArgumentException('Nominal TOP tidak valid.');
        $other = Invoice_calculation::ratio($sales, 11, 12);
        $vat = Invoice_calculation::ratio($other, 12, 100);
        return array('sales'=>$sales, 'other'=>$other, 'ppn'=>$vat, 'gross'=>$sales+$vat,
            'receivable'=>$type === 'PKP' ? $sales+$vat : $sales, 'type'=>$type);
    }

    public static function fields($t)
    {
        $f = array('total_nominal'=>$t['sales'], 'total_nominal_jurnal'=>$t['sales'], 'dpp_nilai_lain'=>$t['other'],
            'dpp_lain_lain_jurnal'=>$t['other'], 'pajak'=>$t['ppn'], 'ppn_jurnal'=>$t['ppn'],
            'total_akhir'=>$t['gross'], 'tagihan_ppn_jurnal'=>$t['gross'], 'pph_jurnal'=>0,
            'total_akhir_jurnal'=>$t['receivable'], 'saldo_piutang'=>$t['receivable'],
            'saldo_piutang_tanpa_pph'=>$t['receivable'], 'saldo_piutang_tanpa_ppn'=>$t['sales']);
        return array_map(array('Invoice_calculation','decimal'), $f) + array('ppn_persen'=>12);
    }

    public static function journal($t, $accounts, $salesRole, $invoiceNo, $spkNo)
    {
        if (!is_string($salesRole) || !isset(self::revenues()[$salesRole])) throw new InvalidArgumentException('Pilihan COA penjualan tidak valid.');
        $lines = array();
        if ($t['type'] === 'WAPU') $lines[] = array('vat_wapu_prepaid', $t['ppn'], 0);
        $lines[] = array('receivable', $t['receivable'], 0);
        if ($t['type'] === 'PKP') $lines[] = array('vat_domestic', 0, $t['ppn']);
        if ($t['type'] === 'WAPU') $lines[] = array('vat_wapu_payable', 0, $t['ppn']);
        $lines[] = array($salesRole, 0, $t['sales']);
        $rows = array();
        foreach ($lines as $line) {
            if (empty($accounts[$line[0]]['code']) || empty($accounts[$line[0]]['name'])) throw new RuntimeException('Mapping COA belum lengkap.');
            $account = $accounts[$line[0]];
            $rows[] = array('coa'=>$account['code'], 'name'=>$account['name'], 'debit'=>$line[1], 'credit'=>$line[2],
                'description'=>$account['name'].' - '.$invoiceNo.' - '.$spkNo.($t['type']==='KB' ? ' - Kawasan Berikat' : ''));
        }
        return $rows;
    }

    public static function receipt($balance, $cash, $admin, $pph)
    {
        if (!is_numeric($balance) || !is_numeric($cash) || !is_numeric($admin) || !is_numeric($pph)
            || min($balance, $cash, $admin, $pph) < 0 || $cash+$admin+$pph <= 0 || $cash+$admin+$pph > $balance)
            throw new InvalidArgumentException('Penerimaan, admin, dan PPh harus nonnegatif dan tidak melebihi saldo piutang.');
        return array('reduction'=>$cash+$admin+$pph, 'remaining'=>$balance-$cash-$admin-$pph, 'adjustment'=>0);
    }

    public static function snapshot($invoice)
    {
        $invoice = (array)$invoice;
        $snapshot = json_decode($invoice['consulting_snapshot'] ?? '', true);
        if (!self::is_new($invoice) || !is_array($snapshot) || empty($snapshot['owner']['id']) || empty($snapshot['accounts']['receivable']['code']))
            throw new RuntimeException('Snapshot invoice tidak lengkap; perlu rekonsiliasi.');
        return $snapshot;
    }

    public static function receipt_journal($snapshot)
    {
        if(!is_array($snapshot) || empty($snapshot['owner']['id']) || empty($snapshot['owner']['nm_company']) || !isset($snapshot['cash'],$snapshot['admin'],$snapshot['pph'],$snapshot['adjustment'],$snapshot['reduction'],$snapshot['date'],$snapshot['invoice_no']))
            throw new RuntimeException('Snapshot jurnal penerimaan tidak lengkap.');
        $lines=array();
        foreach(array(array('bank',$snapshot['cash'],0),array('admin',$snapshot['admin'],0),array('pph',$snapshot['pph'],$snapshot['adjustment']),array('receivable',0,$snapshot['reduction']))as $line){
            if(!$line[1]&&!$line[2])continue;
            $account=$snapshot['accounts'][$line[0]]??null;
            if(!$account || empty($account['code']) || empty($account['name']) || min($line[1],$line[2])<0)throw new RuntimeException('Snapshot akun penerimaan tidak lengkap.');
            $lines[]=array('date'=>$snapshot['date'],'owner'=>(string)$snapshot['owner']['id'],'company'=>$snapshot['owner']['nm_company'],
                'coa'=>$account['code'],'name'=>$account['name'],'debit'=>$line[1],'credit'=>$line[2],'description'=>$account['name'].' - '.$snapshot['invoice_no']);
        }
        if(array_sum(array_column($lines,'debit'))!==array_sum(array_column($lines,'credit')))throw new RuntimeException('Snapshot jurnal penerimaan tidak seimbang.');
        return $lines;
    }
}
