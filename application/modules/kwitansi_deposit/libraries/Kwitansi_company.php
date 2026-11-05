<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kwitansi_company
{
    public static function resolve($db, $spk, $lock = false)
    {
        $quoteId = $spk['id_penawaran'] ?? null;
        if (!is_scalar($quoteId) || trim((string)$quoteId) === '') {
            throw new RuntimeException('Relasi penawaran SPK belum lengkap.');
        }
        $query = $db->query('SELECT * FROM ' . DBCNL . '.kons_tr_penawaran WHERE id_quotation=?' . ($lock ? ' FOR UPDATE' : ''), array($quoteId));
        if (!$query || $query->num_rows() !== 1) throw new RuntimeException('Penawaran SPK tidak ditemukan atau ambigu.');
        $quote = $query->row_array();
        $companyId = $quote['company'] ?? null;
        if (!is_scalar($companyId) || trim((string)$companyId) === '') throw new RuntimeException('Company penawaran belum ditentukan.');
        $query = $db->query('SELECT id,nm_company FROM ' . DBCNL . '.kons_tr_company WHERE id=?' . ($lock ? ' FOR UPDATE' : ''), array($companyId));
        if (!$query || $query->num_rows() !== 1) throw new RuntimeException('Company penawaran tidak ditemukan atau ambigu.');
        $company = $query->row_array();
        if (trim((string)$company['nm_company']) === '') throw new RuntimeException('Nama company penawaran belum lengkap.');
        return array('quote' => $quote, 'company' => $company);
    }
}
