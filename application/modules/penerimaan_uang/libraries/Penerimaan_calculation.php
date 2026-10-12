<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_calculation.php';

/** Money is represented as integer cents throughout the receipt calculation. */
class Penerimaan_calculation
{
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
}
