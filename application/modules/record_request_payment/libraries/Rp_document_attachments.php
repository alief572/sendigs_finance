<?php

require_once dirname(__DIR__, 3) . '/helpers/kasbon_attachment_helper.php';
require_once dirname(__DIR__, 3) . '/helpers/periodik_helper.php';
require_once dirname(__DIR__, 2) . '/request_payment/libraries/Rp_transfer.php';

/**
 * Lampiran awal sebuah dokumen, sumbernya sama dengan yang dimuat halaman cetak tiap kategori.
 * Hasil: list ['name' => string, 'url' => string|null]; url null = berkas tercatat tetapi tidak ada di server.
 */
final class Rp_document_attachments
{
    public static function initial($db, $category, $number, $root = null)
    {
        $root = $root === null ? FCPATH : $root;
        $type = str_replace([' ', '_', '-'], '', strtolower((string) $category));
        $number = (string) $number;

        if ($type === 'periodik' || strpos($number, 'PERIODIK-') === 0) {
            // Item PI- punya lampiran sendiri; nomor batch lama memuat semua barisnya.
            $column = strpos($number, 'PI-') === 0 ? 'no_item' : 'no_doc';
            $files = array();
            foreach (self::rows($db, 'tr_pengajuan_rutin_detail', [$column => $number]) as $row) {
                if (!empty($row['doc_file'])) {
                    $files[$row['doc_file']] = ['name' => $row['doc_file'], 'url' => periodik_doc_url($row['doc_file'])];
                }
            }
            return array_values($files);
        }
        if ($type === 'kasbon' || strpos($number, 'KS-') === 0) {
            $record = Rp_transfer::write($db->get_where('tr_kasbon', ['no_doc' => $number]))->row();
            if (!$record || !empty($record->no_kasbon_consultant)) {
                return [];
            }
            $files = array();
            foreach (['doc_file', 'doc_file_2'] as $field) {
                foreach (kasbon_attachment_items($record, $db, $field, $root) as $item) {
                    $files[$item['name']] = ['name' => $item['name'], 'url' => $item['path'] === null ? null : base_url($item['path'])];
                }
            }
            return array_values($files);
        }
        if ($type === 'transport' || $type === 'transportasi' || strpos($number, 'RQ-') === 0) {
            return self::files(array_column(self::rows($db, 'tr_transport', ['no_req' => $number]), 'doc_file'), 'assets/expense', $root);
        }
        if ($type === 'expense' || strpos($number, 'EXP-') === 0 || strpos($number, 'ER-') === 0) {
            $names = array_column(self::rows($db, 'tr_expense_detail_file', ['no_doc' => $number]), 'doc_file');
            foreach (self::rows($db, 'tr_expense_detail', ['no_doc' => $number]) as $row) {
                $names[] = $row['doc_file'] ?? '';
                $names[] = $row['doc_file_2'] ?? '';
            }
            foreach (self::rows($db, 'tr_expense', ['no_doc' => $number]) as $row) {
                array_push($names, $row['bon_bukti'] ?? '', $row['bukti_pengembalian'] ?? '', $row['transfer_file'] ?? '');
            }
            return self::files($names, 'assets/expense', $root);
        }
        if ($type === 'cash') {
            $names = array();
            foreach (self::rows($db, 'tr_pr_non_po', ['no_non_po' => $number]) as $cash) {
                $names = array_merge($names, array_column(self::rows($db, 'tran_pr_header', ['no_pr' => $cash['no_pr']]), 'dokumen_pendukung'));
            }
            return self::files($names, 'assets/pr', $root);
        }
        // Direct Payment dan Petty Cash: halaman cetaknya tidak memuat lampiran.
        return [];
    }

    private static function rows($db, $table, array $where)
    {
        return Rp_transfer::write($db->get_where($table, $where))->result_array();
    }

    private static function files(array $names, $directory, $root)
    {
        $files = array();
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '' || isset($files[$name])) {
                continue;
            }
            // basename: nama dari database tidak boleh keluar dari folder lampiran.
            $exists = basename($name) === $name && is_file($root . '/' . $directory . '/' . $name);
            $files[$name] = ['name' => $name, 'url' => $exists ? base_url($directory . '/' . rawurlencode($name)) : null];
        }
        return array_values($files);
    }
}
