<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Format tampilan modul penerimaan_uang, kwitansi_deposit, deposit_customer (mengikuti mockup
 * "Penerimaan Piutang + Deposit Consultant.html": fdate, en, idn, TAX).
 * Muat dengan $this->load->helper('mockup_format').
 */

if (!function_exists('mk_fdate')) {
    /** "2026-08-05" atau datetime -> "05-August-2026"; kosong/invalid -> "". */
    function mk_fdate($value)
    {
        $value = trim((string)$value);
        if ($value === '' || strpos($value, '0000-00-00') === 0) return '';
        $ts = strtotime($value);
        return $ts === false ? '' : date('d-F-Y', $ts);
    }
}

if (!function_exists('mk_en')) {
    /** Angka tabel/jurnal: bulat, pemisah koma, tanpa "Rp" (en() mockup). */
    function mk_en($value)
    {
        return number_format((float)$value, 0, '.', ',');
    }
}

if (!function_exists('mk_idn')) {
    /** Angka kartu/header/pesan: 1.234.567,89 (idn() mockup, tanpa "Rp"). */
    function mk_idn($value)
    {
        return number_format((float)$value, 2, ',', '.');
    }
}

if (!function_exists('mk_tax_label')) {
    /** Label TAX mockup (TAX[t]); kode tak dikenal dikembalikan apa adanya. */
    function mk_tax_label($code)
    {
        $map = array('PKP' => 'PKP', 'WAPU' => 'WAPU', 'KB' => 'Kawasan Berikat', 'DEP' => 'Deposit');
        $code = strtoupper(trim((string)$code));
        return isset($map[$code]) ? $map[$code] : $code;
    }
}

if (!function_exists('mk_tax_badge')) {
    /** Badge TAX outline (.tax mockup); CSS ada di mk_tax_badge_css(). */
    function mk_tax_badge($code)
    {
        $code = strtoupper(trim((string)$code));
        if ($code === '') return '-';
        return '<span class="mk-tax mk-tax-' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(mk_tax_label($code), ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

if (!function_exists('mk_tax_badge_css')) {
    /** CSS badge TAX = .tax mockup (outline, 3px radius). */
    function mk_tax_badge_css()
    {
        return '<style>.mk-tax{display:inline-block;font-size:11px;font-weight:700;padding:1px 7px;border-radius:3px;border:1px solid;white-space:nowrap}'
            . '.mk-tax-PKP{color:#2e6da4;border-color:#2e6da4}.mk-tax-WAPU{color:#8a5a00;border-color:#c28a00}'
            . '.mk-tax-KB{color:#5b4a9e;border-color:#7d6cc0}.mk-tax-DEP{color:#2b7a4b;border-color:#3c9a62}</style>';
    }
}
