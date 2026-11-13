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

    /**
     * Cetak memakai layout Vuca (print_invoicing_vuca): invoice Vuca (tipe_invoice 1), atau invoice lama milik company Vuca.
     * Invoice alur konsultasi v3 tetap memakai layout konsultasi dengan kop pemiliknya, termasuk bila pemiliknya Vuca.
     */
    public static function print_as_vuca($invoice, $companyIsVuca)
    {
        $invoice = (array)$invoice;
        return (string)($invoice['tipe_invoice'] ?? '0') === '1' || ($companyIsVuca && !self::is_new($invoice));
    }

    public static function tax_treatments()
    {
        return array('PKP'=>'PKP', 'WAPU'=>'WAPU', 'KB'=>'Kawasan Berikat', 'UMKM'=>'UMKM Non-PKP');
    }

    /** Akhiran deskripsi jurnal; hanya KB dan UMKM yang diberi penanda. */
    public static function tax_suffix($type)
    {
        return is_string($type) && in_array($type, array('KB','UMKM'), true) ? ' - ' . self::tax_treatments()[$type] : '';
    }

    public static function totals($amount, $type)
    {
        if (!is_string($type) || !isset(self::tax_treatments()[$type])) throw new InvalidArgumentException('Perlakuan PPN wajib dipilih.');
        $sales = Invoice_calculation::cents($amount);
        if ($sales <= 0 || $sales > 999999999999999) throw new InvalidArgumentException('Nominal TOP tidak valid.');
        $other = Invoice_calculation::ratio($sales, 11, 12);
        // UMKM Non-PKP tidak memungut PPN: DPP tetap dihitung, PPN 0, tagihan = piutang = penjualan.
        $vat = $type === 'UMKM' ? 0 : Invoice_calculation::ratio($other, 12, 100);
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
                'description'=>$account['name'].' - '.$invoiceNo.' - '.$spkNo.self::tax_suffix($t['type']));
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
        $concept = (int)($snapshot['calculation_version'] ?? 0) === 4;
        if(!is_array($snapshot) || empty($snapshot['owner']['id']) || empty($snapshot['owner']['nm_company']) || !isset($snapshot['cash'],$snapshot['admin'],$snapshot['pph'],$snapshot['reduction'],$snapshot['date'],$snapshot['invoice_no'])
            || (!$concept && !isset($snapshot['adjustment'])))
            throw new RuntimeException('Snapshot jurnal penerimaan tidak lengkap.');
        if ($concept) {
            $dep = (int)($snapshot['dep'] ?? 0);
            $lebih = (int)($snapshot['lebih'] ?? 0);
            if (!isset($snapshot['balance'], $snapshot['sisa']) || $snapshot['balance'] - $snapshot['sisa'] !== $snapshot['reduction']
                || $snapshot['cash'] + $snapshot['pph'] + $snapshot['admin'] + $dep !== $snapshot['reduction'] + $lebih) throw new RuntimeException('Snapshot pelunasan piutang tidak konsisten.');
            // UMKM Non-PKP: PPh Final 0,5% dipotong customer -> Beban PPh Final (D); setor sendiri -> Beban (D) / Hutang PPh Final (K).
            // Setor sendiri tidak mengurangi piutang, jadi tidak ikut kontrol cash+pph+admin+dep = reduction+lebih.
            $umkm = ($snapshot['tax_treatment'] ?? '') === 'UMKM';
            $self = $snapshot['pph_final_self'] ?? 0;
            if (!is_int($self) || $self < 0 || ($self > 0 && !$umkm) || ($self > 0 && $snapshot['pph'] > 0)) throw new RuntimeException('Snapshot PPh Final tidak valid.');
            $entries = array(
                array('bank', max(0, $snapshot['cash']), max(0, -$snapshot['cash'])),
                $umkm ? array('pph_final_expense', $snapshot['pph'] + $self, 0) : array('pph', $snapshot['pph'], 0),
                array('admin', $snapshot['admin'], 0),
                array('deposit', $dep, $lebih),
                array('receivable', 0, $snapshot['balance']),
                array('receivable', $snapshot['sisa'], 0),
                array('pph_final_payable', 0, $self)
            );
        } else {
            $entries = array(array('bank',$snapshot['cash'],0),array('admin',$snapshot['admin'],0),array('pph',$snapshot['pph'],$snapshot['adjustment']),array('receivable',0,$snapshot['reduction']));
        }
        $suffix = ' - ' . $snapshot['invoice_no'];
        if ($concept) $suffix .= (!empty($snapshot['spk']) ? ' - ' . $snapshot['spk'] : '') . self::tax_suffix($snapshot['tax_treatment'] ?? '');
        $lines=array();
        foreach($entries as $line){
            if(!$line[1]&&!$line[2])continue;
            $account=$snapshot['accounts'][$line[0]]??null;
            // Hanya dua akun PPh Final yang boleh tanpa nomor COA (belum ada di master); posting menolaknya.
            $codeOptional = in_array($line[0], array('pph_final_expense','pph_final_payable'), true);
            if(!$account || (empty($account['code']) && !$codeOptional) || empty($account['name']) || min($line[1],$line[2])<0)throw new RuntimeException('Snapshot akun penerimaan tidak lengkap.');
            $lines[]=array('date'=>$snapshot['date'],'owner'=>(string)$snapshot['owner']['id'],'company'=>$snapshot['owner']['nm_company'],
                'coa'=>(string)$account['code'],'name'=>$account['name'],'debit'=>$line[1],'credit'=>$line[2],'description'=>$account['name'].$suffix);
        }
        if(array_sum(array_column($lines,'debit'))!==array_sum(array_column($lines,'credit')))throw new RuntimeException('Snapshot jurnal penerimaan tidak seimbang.');
        return $lines;
    }
}
