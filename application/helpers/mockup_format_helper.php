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
        $map = array('PKP' => 'PKP', 'WAPU' => 'WAPU', 'KB' => 'Kawasan Berikat', 'UMKM' => 'UMKM Non-PKP', 'DEP' => 'Deposit');
        $code = strtoupper(trim((string)$code));
        return isset($map[$code]) ? $map[$code] : $code;
    }
}

if (!function_exists('mk_tax_badge')) {
    /** Badge TAX outline (.tax mockup); gayanya ada di assets/css/mockup_ui.css (wrapper .mk). */
    function mk_tax_badge($code)
    {
        $code = strtoupper(trim((string)$code));
        if ($code === '') return '-';
        return '<span class="tax ' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(mk_tax_label($code), ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

if (!function_exists('mk_ui_assets')) {
    /** Tag CSS + JS bersama (gaya mockup, toast/confirmBox/pencarian/pager). Panggil sekali di awal view. */
    function mk_ui_assets()
    {
        return '<link rel="stylesheet" href="' . base_url('assets/css/mockup_ui.css') . '"><script src="' . base_url('assets/js/penerimaan_uang/mockup_ui.js') . '"></script>';
    }
}

if (!function_exists('mk_select2_assets')) {
    /** Select2 (dropdown bisa dicari) dari cdnjs; dimuat sekali dan hanya bila layout belum menyediakannya. */
    function mk_select2_assets()
    {
        return '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">'
            . "<script>window.jQuery && !jQuery.fn.select2 && document.write('<script src=\"https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js\"><\/script>');</script>";
    }
}

if (!function_exists('mk_tax_badge_css')) {
    /** Kompatibilitas: gaya badge sudah ada di mockup_ui.css. */
    function mk_tax_badge_css() { return ''; }
}
