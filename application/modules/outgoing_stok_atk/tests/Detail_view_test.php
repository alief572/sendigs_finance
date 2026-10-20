<?php
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
function tgl_indo($date) { return $date; }
function render_detail($note, $itemNote = '', $includeNote = true) {
    $getData = array(array('kode_trans' => 'TRM-TEST', 'tanggal' => '2026-10-20', 'pic' => 'Lita'));
    if ($includeNote) { $getData[0]['note'] = $note; }
    $nm_dept = 'Sales'; $tanda = 'detail';
    $GET_MATERIAL = array(11 => array('nama' => 'Kertas', 'code' => 'A4', 'id_unit' => 1));
    $GET_SATUAN = array(1 => array('code' => 'RIM'));
    $getDataDetail = array(array('id_material' => 11, 'qty_order' => 2, 'keterangan' => $itemNote));
    ob_start(); require dirname(__DIR__) . '/views/modal_request_edit.php'; return ob_get_clean();
}
$checks = 0;
function detail_check($condition, $message) { global $checks; $checks++; if (!$condition) { throw new RuntimeException($message); } }
$html = render_detail("Keperluan meeting\nLantai 2", 'Untuk printer');
detail_check(strpos($html, 'Keperluan meeting') !== false && strpos($html, 'Lantai 2') !== false, 'Header transaction note visible');
detail_check(strpos($html, 'Keperluan meeting<br') !== false, 'Header note preserves newlines');
detail_check(strpos($html, 'Untuk printer') !== false, 'Item note remains separate and visible');
$html = render_detail('<script>alert("note")</script>', '<img src=x onerror=alert(1)>');
detail_check(strpos($html, '<script>alert') === false && strpos($html, '&lt;script&gt;') !== false, 'Transaction note HTML escaped');
detail_check(strpos($html, '<img src=x') === false && strpos($html, '&lt;img') !== false, 'Item note HTML escaped');
foreach (array('', '  ', null) as $empty) {
    $html = render_detail($empty);
    detail_check(preg_match('/class="outgoing-transaction-note"[^>]*>\s*-\s*<\/td>/', $html) === 1, 'Empty note fallback');
}
$html = render_detail(null, '', false);
detail_check(preg_match('/class="outgoing-transaction-note"[^>]*>\s*-\s*<\/td>/', $html) === 1, 'Historical missing field fallback');
$html = render_detail('0', "Baris 1\nBaris 2");
detail_check(preg_match('/class="outgoing-transaction-note"[^>]*>\s*0\s*<\/td>/', $html) === 1, 'Literal zero is not empty');
detail_check(strpos($html, 'Baris 1<br') !== false, 'Item note preserves newlines');
echo 'PASS Outgoing detail view: ' . $checks . " checks\n";
