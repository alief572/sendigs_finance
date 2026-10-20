<?php
function has_permission($permission) { return true; }
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
check_view(trim($headers->item(3)->textContent) === 'Department' && trim($headers->item(4)->textContent) === 'Detail Barang', 'Detail follows department');
check_view(strpos($headers->item(4)->getAttribute('class'), 'no-sort') !== false, 'Detail is not sortable');
check_view(trim($headers->item(5)->textContent) === 'Qty Unit' && trim($headers->item(9)->textContent) === 'Option', 'Original columns follow shifted indices');
check_view($xpath->query('//div[contains(@class,"table-responsive")]/table[@id="example2"]')->length === 1, 'Table supports horizontal overflow');
check_view(strpos($html, '#example2 .outgoing-items') !== false && strpos($html, 'overflow-wrap: anywhere') !== false, 'Scoped wrapping styles present');
check_view(strpos($html, 'outgoing-item-quantity') !== false && strpos($html, 'white-space: nowrap') !== false, 'Qty and unit stay together');
echo 'PASS Outgoing index view: ' . $checks . " checks\n";
