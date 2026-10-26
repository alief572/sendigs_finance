<?php
function has_permission($permission) { return $permission !== 'Outgoing_Stok_ATK.View' || !in_array('--no-view', $GLOBALS['argv'], true); }
function base_url($path = '') { return '/fixture/' . $path; }
ob_start(); require dirname(__DIR__) . '/views/index.php'; $html = ob_get_clean();
if (($argv[1] ?? '') === '--render') { echo $html; exit; }
libxml_use_internal_errors(true);
$dom = new DOMDocument(); $dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$headers = $xpath->query('//table[@id="example2"]/thead/tr/th');
$checks = 0;
function check_view($condition, $message) { global $checks; $checks++; if (!$condition) { throw new RuntimeException($message); } }
check_view($headers->length === 10, 'Ten visible table headers');
$export = $xpath->query('//a[@id="export-excel"]');
check_view($export->length === (has_permission('Outgoing_Stok_ATK.View') ? 1 : 0), 'Export button follows View permission');
if ($export->length) {
    check_view($export->item(0)->getAttribute('href') === '/fixture/outgoing_stok_atk/export_excel', 'Export button has real endpoint');
}
check_view(trim($headers->item(3)->textContent) === 'Department' && trim($headers->item(4)->textContent) === 'Detail Barang', 'Detail follows department');
check_view(strpos($headers->item(4)->getAttribute('class'), 'no-sort') !== false, 'Detail is not sortable');
check_view(trim($headers->item(5)->textContent) === 'Qty Unit' && trim($headers->item(9)->textContent) === 'Option', 'Original columns follow shifted indices');
check_view($xpath->query('//div[contains(@class,"table-responsive")]/table[@id="example2"]')->length === 1, 'Table supports horizontal overflow');
check_view(strpos($html, '#example2 .outgoing-items') !== false && strpos($html, 'overflow-wrap: anywhere') !== false, 'Scoped wrapping styles present');
check_view(strpos($html, 'outgoing-item-quantity') !== false && strpos($html, 'white-space: nowrap') !== false, 'Qty and unit stay together');
echo 'PASS Outgoing index view: ' . $checks . " checks\n";
