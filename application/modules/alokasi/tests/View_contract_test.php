<?php
// Kontrak teks/skrip view Alokasi (modal Split, Detail Alokasi, Upload) tanpa browser.
$src = file_get_contents(dirname(__DIR__) . '/views/index.php');
function vCheck($ok, $m) { if (!$ok) throw new RuntimeException($m); }
foreach (array('Total Debit', 'Total Credit', 'id="split_bank"', 'id="view_bank"', 'Jenis alokasi belum dipilih', 'Nominal belum sama dengan transaksi', '&nbsp;Detail Alokasi</h4>', 'Penerimaan Piutang</th>', 'receiptCell', "'penerimaan_uang/view/'", 'id="upload_preview"', 'tidak ada baris yang disimpan sebelum semuanya valid', 'id="btn-proses-upload" disabled') as $needle) {
    vCheck(strpos($src, $needle) !== false, "View contains: $needle");
}
vCheck(strpos($src, 'Tidak Sesuai (selisih') === false && strpos($src, 'Detail Alokasi Split') === false && strpos($src, "'review_upload/'") === false, 'Old split wording and review redirect are gone');
// Skrip inline harus valid secara sintaks.
preg_match_all('~<script(?![^>]*(?:src|type=))[^>]*>(.*?)</script>~s', $src, $m);
$js = preg_replace('~<\?.*?\?>~s', '0', implode("\n", $m[1]));
$tmp = tempnam(sys_get_temp_dir(), 'js') . '.js';
file_put_contents($tmp, $js);
exec('node --check ' . escapeshellarg($tmp) . ' 2>&1', $out, $code);
unlink($tmp);
vCheck($code === 0, 'Inline script syntax: ' . implode(' ', $out));
echo "PASS: Alokasi view contract (split columns/status, Detail Alokasi, upload preview) and inline script syntax\n";
