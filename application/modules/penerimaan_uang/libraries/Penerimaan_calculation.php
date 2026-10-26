<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_calculation.php';

/** Money is represented as integer cents throughout the receipt calculation. */
class Penerimaan_calculation
{
    const MAX_ADMIN_CENTS = 1000000; // Rp 10.000,00

    public static function cents($value, $indonesian = false)
    {
        if (!is_scalar($value) && $value !== null) {
            throw new InvalidArgumentException('Nominal tidak valid.');
        }
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }
        $pattern = $indonesian ? '/^-?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{1,2})?$/' : '/^-?\d+(?:\.\d{1,2})?$/';
        if (!preg_match($pattern, $value)) {
            throw new InvalidArgumentException('Format nominal tidak valid.');
        }
        if ($indonesian) {
            $value = str_replace(',', '.', str_replace('.', '', $value));
        }
        $negative = substr($value, 0, 1) === '-';
        $parts = explode('.', ltrim($value, '-'));
        // Bound values to the safe integer range shared by PHP and JavaScript.
        if (strlen($parts[0]) > 13) {
            throw new InvalidArgumentException('Nominal terlalu besar.');
        }
        $result = ((int) $parts[0]) * 100 + (int) str_pad(isset($parts[1]) ? $parts[1] : '', 2, '0');
        return $negative ? -$result : $result;
    }

    public static function decimal($cents)
    {
        return ($cents < 0 ? '-' : '') . intdiv(abs($cents), 100) . '.' . str_pad(abs($cents) % 100, 2, '0', STR_PAD_LEFT);
    }

    /** Compatibility wrappers for existing receipt callers. */
    public static function invoice_gross($invoice)
    {
        return Invoice_calculation::invoice_gross($invoice);
    }
    public static function vuca_totals($dpp, $pph)
    {
        return Invoice_calculation::vuca_totals($dpp, $pph);
    }

    public static function calculate($balance, $remainingPph, $choice, $receipt, $admin)
    {
        if (!in_array($choice, array('Y', 'N'), true) || min($balance, $remainingPph, $receipt, $admin) < 0) {
            throw new InvalidArgumentException('Pilihan PPh atau nominal tidak valid.');
        }
        $adjustment = $choice === 'N' ? min($remainingPph, $receipt) : 0;
        $reduction = $receipt + $admin - $adjustment;
        if ($reduction > $balance) {
            throw new InvalidArgumentException('Penerimaan dan biaya admin melebihi saldo piutang.');
        }
        return array('adjustment' => $adjustment, 'reduction' => $reduction, 'remaining' => $balance - $reduction);
    }

    /**
     * Calculate tax breakdown for consulting invoices based on tax treatment.
     * Tax treatments: 'PKP', 'WAPU', 'KB' (Kawasan Berikat).
     */
    public static function tax_calc($salesCents, $taxTreatment)
    {
        if (!in_array($taxTreatment, array('PKP', 'WAPU', 'KB'), true)) {
            throw new InvalidArgumentException('Perlakuan pajak tidak valid: ' . $taxTreatment);
        }
        if ($salesCents < 0) {
            throw new InvalidArgumentException('Penjualan tidak boleh negatif.');
        }
        $dppLain = Invoice_calculation::ratio($salesCents, 11, 12);
        $ppn = $taxTreatment === 'PKP' ? Invoice_calculation::ratio($dppLain, 12, 100) : 0;
        $pphDefault = Invoice_calculation::ratio($dppLain, 2, 100);
        $tagihan = $taxTreatment === 'PKP' ? $salesCents + $ppn : $salesCents;
        $piutang = $tagihan;

        return array(
            'sales' => $salesCents,
            'dpp_lain' => $dppLain,
            'ppn' => $ppn,
            'pph_default' => $pphDefault,
            'tagihan' => $tagihan,
            'piutang' => $piutang,
            'tax_treatment' => $taxTreatment
        );
    }

    /**
     * Kontrol validation formula:
     * ((Total Piutang - Total PPh 23 - Total Biaya Admin - Total Sisa Piutang) - Uang Masuk)
     * Must be 0 for a balanced allocation.
     */
    public static function kontrol($totalPiutang, $totalPph, $totalAdmin, $totalSisa, $uangMasuk)
    {
        return ($totalPiutang - $totalPph - $totalAdmin - $totalSisa) - $uangMasuk;
    }

    /**
     * Validate an allocation batch against business rules.
     */
    public static function validate_batch($rows, $uangMasuk, $choice)
    {
        if (!is_array($rows) || empty($rows)) {
            throw new InvalidArgumentException('Daftar alokasi invoice tidak boleh kosong.');
        }
        if (!in_array($choice, array('Y', 'N'), true)) {
            throw new InvalidArgumentException('Pilihan PPh 23 wajib Dipotong (Y) atau Tidak Dipotong (N).');
        }
        $totPiutang = 0;
        $totPph = 0;
        $totAdmin = 0;
        $totSisa = 0;
        $errors = array();

        foreach ($rows as $idx => $r) {
            $invNo = isset($r['no_invoice']) ? $r['no_invoice'] : ('Baris ' . ($idx + 1));
            $piutang = isset($r['piutang']) ? (int)$r['piutang'] : 0;
            $pph = isset($r['pph']) ? (int)$r['pph'] : 0;
            $admin = isset($r['admin']) ? (int)$r['admin'] : 0;
            $sisa = isset($r['sisa']) ? (int)$r['sisa'] : 0;

            if ($piutang < 0 || $pph < 0 || $admin < 0 || $sisa < 0) {
                $errors[] = 'Nilai tidak boleh negatif pada ' . $invNo . '.';
            }
            if ($pph > $piutang) {
                $errors[] = 'PPh 23 melebihi piutang dagang invoice pada ' . $invNo . '.';
            }
            if ($sisa > $piutang) {
                $errors[] = 'Sisa piutang melebihi piutang dagang invoice pada ' . $invNo . '.';
            }

            $totPiutang += $piutang;
            $totPph += $pph;
            $totAdmin += $admin;
            $totSisa += $sisa;
        }

        if ($totAdmin > self::MAX_ADMIN_CENTS) {
            $errors[] = 'Total biaya admin melebihi batas maksimal Rp 10.000.';
        }

        $k = self::kontrol($totPiutang, $totPph, $totAdmin, $totSisa, $uangMasuk);
        if ($k !== 0) {
            $errors[] = 'Kontrol belum 0 (selisih ' . self::decimal($k) . ').';
        }

        return array(
            'valid' => empty($errors),
            'errors' => $errors,
            'kontrol' => $k,
            'totals' => array(
                'piutang' => $totPiutang,
                'pph' => $totPph,
                'admin' => $totAdmin,
                'sisa' => $totSisa,
                'uang_masuk' => $uangMasuk
            )
        );
    }
}
