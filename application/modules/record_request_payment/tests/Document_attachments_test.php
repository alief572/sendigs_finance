<?php
// Lampiran awal per kategori untuk modal Cetak Dokumen. SQLite in-memory + query builder CodeIgniter asli.
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
function base_url($path = '') { return 'http://fixture/' . $path; }
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/record_request_payment/libraries/Rp_document_attachments.php';

$root = sys_get_temp_dir() . '/rp-attach-' . getmypid() . '/';
foreach (['assets/expense', 'assets/pr'] as $dir) { mkdir($root . $dir, 0700, true); }
foreach (['assets/expense/a.pdf', 'assets/expense/b foto.jpg', 'assets/expense/h.png', 'assets/pr/pr.pdf', 'secret.txt'] as $file) { file_put_contents($root . $file, 'x'); }

$db = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
foreach ([
    'tr_pengajuan_rutin_detail (no_doc TEXT, no_item TEXT, doc_file TEXT)',
    'tr_kasbon (no_doc TEXT, no_kasbon_consultant TEXT, doc_file TEXT, doc_file_2 TEXT, tipe_pr TEXT, id_pr TEXT)',
    'tr_transport (no_req TEXT, doc_file TEXT)',
    'tr_expense (no_doc TEXT, bon_bukti TEXT, bukti_pengembalian TEXT, transfer_file TEXT)',
    'tr_expense_detail (no_doc TEXT, doc_file TEXT, doc_file_2 TEXT)',
    'tr_expense_detail_file (no_doc TEXT, doc_file TEXT)',
    'tr_pr_non_po (no_non_po TEXT, no_pr TEXT)',
    'tran_pr_header (no_pr TEXT, dokumen_pendukung TEXT)',
] as $table) { $db->query('CREATE TABLE ' . $table); }
$db->insert('tr_pengajuan_rutin_detail', ['no_doc' => 'PERIODIK-1', 'no_item' => 'PI-1', 'doc_file' => 'p1.pdf']);
$db->insert('tr_pengajuan_rutin_detail', ['no_doc' => 'PERIODIK-1', 'no_item' => 'PI-2', 'doc_file' => 'p2.pdf']);
$db->insert('tr_kasbon', ['no_doc' => 'KS-1', 'doc_file' => '["a.pdf","hilang.pdf"]', 'doc_file_2' => 'b foto.jpg']);
$db->insert('tr_kasbon', ['no_doc' => 'KS-2', 'no_kasbon_consultant' => 'C/1', 'doc_file' => 'a.pdf']);
$db->insert('tr_transport', ['no_req' => 'RQ-1', 'doc_file' => 'a.pdf']);
$db->insert('tr_transport', ['no_req' => 'RQ-1', 'doc_file' => 'a.pdf']);
$db->insert('tr_transport', ['no_req' => 'RQ-1', 'doc_file' => '../../secret.txt']);
$db->insert('tr_transport', ['no_req' => 'RQ-1', 'doc_file' => '']);
$db->insert('tr_expense_detail_file', ['no_doc' => 'ER-1', 'doc_file' => 'a.pdf']);
$db->insert('tr_expense_detail', ['no_doc' => 'ER-1', 'doc_file' => 'a.pdf', 'doc_file_2' => 'b foto.jpg']);
$db->insert('tr_expense', ['no_doc' => 'ER-1', 'bon_bukti' => 'h.png']);
$db->insert('tr_pr_non_po', ['no_non_po' => 'NP-1', 'no_pr' => 'PR-1']);
$db->insert('tran_pr_header', ['no_pr' => 'PR-1', 'dokumen_pendukung' => 'pr.pdf']);

$get = function ($category, $number) use ($db, $root) { return Rp_document_attachments::initial($db, $category, $number, $root); };
$names = function (array $files) { return implode(',', array_column($files, 'name')); };

check($names($get('Periodik', 'PI-2')) === 'p2.pdf', 'Periodic item shows only its own attachment');
check($names($get('Periodik', 'PERIODIK-1')) === 'p1.pdf,p2.pdf', 'Old periodic batch shows all rows');
check($get('Periodik', 'PI-2')[0]['url'] === 'http://fixture/pengajuan_rutin/download_doc/p2.pdf', 'Periodic uses the download endpoint');
$kasbon = $get('Kasbon', 'KS-1');
check($names($kasbon) === 'a.pdf,hilang.pdf,b foto.jpg' && $kasbon[1]['url'] === null && $kasbon[2]['url'] === 'http://fixture/assets/expense/b%20foto.jpg', 'Cash advance files, missing one has no link');
check($get('Kasbon', 'KS-2') === [], 'Consultant cash advance print has no attachment');
$transport = $get('Transport', 'RQ-1');
check($names($transport) === 'a.pdf,../../secret.txt' && $transport[1]['url'] === null, 'Duplicates removed and path traversal never linked');
check($names($get('Expense', 'ER-1')) === 'a.pdf,b foto.jpg,h.png', 'Expense files from detail and header');
check($get('Cash', 'NP-1') === [['name' => 'pr.pdf', 'url' => 'http://fixture/assets/pr/pr.pdf']], 'Cash PR supporting document');
foreach (['Direct Payment' => 'DPM-1', 'Refill Pettycash' => 'RPC-1', 'Petty Cash Hutang' => 'PHP-1', 'Transportasi' => 'RQ-9'] as $category => $number) {
    check($get($category, $number) === [], 'No attachment for ' . $category);
}

foreach (['assets/expense/a.pdf', 'assets/expense/b foto.jpg', 'assets/expense/h.png', 'assets/pr/pr.pdf', 'secret.txt'] as $file) { unlink($root . $file); }
foreach (['assets/expense', 'assets/pr', 'assets', ''] as $dir) { rmdir($root . $dir); }
echo "PASS Document attachments: $checks checks\n";
