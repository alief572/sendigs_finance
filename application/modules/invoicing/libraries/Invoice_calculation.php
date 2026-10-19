<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** All monetary results are integer cents, with explicit input formats. */
class Invoice_calculation
{
    public static function cents($value, $grouped = false)
    {
        if (!is_scalar($value) || $value === '') { throw new InvalidArgumentException('Nominal wajib diisi.'); }
        $value = trim((string) $value);
        if ($grouped && preg_match('/^\d{1,3}(?:,\d{3})+(?:\.\d{1,2})?$/', $value)) { $value = str_replace(',', '', $value); }
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) { throw new InvalidArgumentException('Format nominal tidak valid.'); }
        $parts = explode('.', $value);
        if (strlen($parts[0]) > 13) { throw new InvalidArgumentException('Nominal terlalu besar.'); }
        return (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
    }
    public static function decimal($value)
    {
        return ($value < 0 ? '-' : '') . intdiv(abs($value), 100) . '.' . str_pad(abs($value) % 100, 2, '0', STR_PAD_LEFT);
    }
    public static function ratio($value, $numerator, $denominator)
    {
        return intdiv($value, $denominator) * $numerator + intdiv(($value % $denominator) * $numerator + intdiv($denominator, 2), $denominator);
    }
    public static function totals($dpp, $rate, $vuca = false)
    {
        if ($dpp <= 0 || $dpp > 999999999999999 || !in_array((string) $rate, array('0','12'), true) || ($vuca && (string) $rate !== '0')) {
            throw new InvalidArgumentException('DPP atau pilihan PPN tidak valid.');
        }
        $other = self::ratio($dpp, 11, 12);
        $ppn = self::ratio($other, (int) $rate, 100);
        $pph = self::ratio($dpp, $vuca ? 5 : 20, 1000);
        return array('dpp'=>$dpp,'other'=>$other,'ppn'=>$ppn,'pph'=>$pph,'gross'=>$dpp+$ppn,'net'=>$dpp+$ppn-$pph,'rate'=>(int)$rate);
    }
    public static function items($items, $shipping)
    {
        if (!is_array($items) || !$items) { throw new InvalidArgumentException('Item invoice wajib diisi.'); }
        $dpp = self::cents($shipping, true); $details = array();
        foreach ($items as $item) {
            if (!is_array($item) || trim($item['nama'] ?? '') === '' || !isset($item['qty'], $item['harga'])) { throw new InvalidArgumentException('Item invoice tidak lengkap.'); }
            $qty = trim((string) $item['qty']);
            if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $qty)) { throw new InvalidArgumentException('Quantity harus positif dengan maksimal dua desimal.'); }
            $parts = explode('.', $qty); $scale = 10 ** strlen($parts[1] ?? '');
            if (strlen($parts[0]) > 6) { throw new InvalidArgumentException('Quantity terlalu besar.'); }
            $quantity = (int)$parts[0] * $scale + (int)($parts[1] ?? 0);
            if ($quantity <= 0) { throw new InvalidArgumentException('Quantity harus positif.'); }
            $price = self::cents($item['harga'], true);
            if ($price && $quantity > intdiv(9007199254740991, $price)) { throw new InvalidArgumentException('Total item terlalu besar.'); }
            $total = intdiv($quantity * $price + intdiv($scale, 2), $scale);
            $dpp += $total;
            $details[] = array('nm_item'=>trim($item['nama']),'qty'=>$qty,'harga'=>self::decimal($price),'total'=>self::decimal($total));
        }
        return array('dpp'=>$dpp,'details'=>$details);
    }
    public static function fields($t)
    {
        return array('total_nominal'=>self::decimal($t['dpp']),'total_nominal_jurnal'=>self::decimal($t['dpp']),
            'dpp_nilai_lain'=>self::decimal($t['other']),'dpp_lain_lain_jurnal'=>self::decimal($t['other']),
            'pajak'=>self::decimal($t['ppn']),'ppn_jurnal'=>self::decimal($t['ppn']),
            'total_akhir'=>self::decimal($t['gross']),'tagihan_ppn_jurnal'=>self::decimal($t['gross']),
            'total_akhir_jurnal'=>self::decimal($t['net']),'saldo_piutang'=>self::decimal($t['net']),
            'saldo_piutang_tanpa_pph'=>self::decimal($t['gross']),'saldo_piutang_tanpa_ppn'=>self::decimal($t['dpp']-$t['pph']),'ppn_persen'=>$t['rate']);
    }
    public static function fingerprint($invoice)
    {
        ksort($invoice); return hash('sha256', json_encode($invoice));
    }
    /** Both writers must hold sendigs_receipt_v2 while allocating journal numbers. */
    public static function journal_number($db, $offset)
    {
        $period=int_to_roman(date('m')).'-'.date('y');
        $max=$db->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(no_jurnal,'-',1) AS UNSIGNED)),0) AS max_no FROM tr_jurnal WHERE no_jurnal LIKE ?",array('%-AJV-'.$period))->row_array();
        return sprintf('%05d',(int)$max['max_no']+(int)$offset).'-AJV-'.$period;
    }
    public static function invoice_gross($invoice)
    {
        $gross = self::cents($invoice['tagihan_ppn_jurnal'] ?? '0');
        $dpp = self::cents($invoice['total_nominal_jurnal'] ?? '0');
        return (string)($invoice['tipe_invoice'] ?? '') === '1' && $gross === 0 && $dpp > 0 ? $dpp + self::cents($invoice['ppn_jurnal'] ?? '0') : $gross;
    }
    public static function vuca_totals($dpp, $pph)
    {
        $gross=self::cents($dpp); $tax=self::cents($pph);
        if ($tax > $gross) { throw new InvalidArgumentException('PPh melebihi tagihan.'); }
        return array('gross'=>self::decimal($gross),'net'=>self::decimal($gross-$tax),'pph'=>self::decimal($tax));
    }
}
