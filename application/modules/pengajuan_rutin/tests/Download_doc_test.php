<?php
// download_doc: lampiran hanya untuk user ber-permission, hanya file tercatat di DB dengan nama 32 hex, tanpa path traversal.
// Jalankan dari container: php application/modules/pengajuan_rutin/tests/Download_doc_test.php (cwd diatur ke root repo).
define('AUDIT_FIXTURE_ONLY', true);
define('PENGAJUAN_FIXTURE_ONLY', true);
chdir(dirname(__DIR__, 4));

function has_permission($perm = '') { return !empty($GLOBALS['allowPerm'][$perm]); }
function show_404() { throw new RuntimeException('404'); }
require __DIR__ . '/Batch1_audit_test.php';

class DownloadInput extends FixtureInput {
    public $dl = null;
    public function get($key = null) { return $key === 'dl' ? $this->dl : array(); }
}

$name = str_repeat('ab', 16) . '.pdf';
$path = './assets/bayar_rutin/' . $name;
$body = '%PDF-1.4 download_doc test';
$child = $argv[1] ?? '';

class DownloadTestController extends RutinTestController {
    public $sent = null;
    protected function sendDoc($path, array $headers) { $this->sent = array('body' => file_get_contents($path), 'headers' => $headers); }
}

if (file_exists($path)) { throw new RuntimeException('fixture file already exists: ' . $path); }
file_put_contents($path, $body);
register_shutdown_function(function () use ($path) { if (is_file($path)) { unlink($path); } });

$dlChecks = 0;
function dl_check($ok, $msg) { global $dlChecks; $dlChecks++; check($ok, $msg); }
function dl_run($file, $allow, $options = array()) {
    $GLOBALS['allowPerm'] = $allow;
    return pengajuan_run('download_doc', array_merge(array('expected_exception' => true), $options), array(), array($file));
}

$perms = array('Pengajuan_Pembayaran_Rutin.View', 'Approval_Pengajuan_Pembayaran_Rutin.View', 'Request_Payment.View',
    'Approval_Request_Payment.View', 'Record_Request_Payment.View', 'Expense.View', 'Pembayaran_Periodik.View');

// A. tanpa permission apa pun: ditolak, tidak menyentuh DB
list($event, , $ci) = dl_run($name, array());
dl_check($event['status'] === 0 && $event['context']['data']['outcome'] === 'unauthorized', 'download_doc: no permission -> unauthorized');
dl_check(!in_array('get_where', $ci->db->calls, true), 'download_doc: no DB lookup before the permission gate');

// B. tiap permission terkait cukup (lolos gerbang, berhenti di file yang tidak ada)
foreach ($perms as $perm) {
    list($event) = dl_run(str_repeat('cd', 16) . '.pdf', array($perm => true));
    dl_check($event['context']['data']['outcome'] === 'not_found', 'download_doc: ' . $perm . ' passes the gate');
}

// C. nama tidak valid / traversal / bukan dokumen
foreach (array('x.pdf', '0319fb823eef4ed46e824a0370fa967c.xlsx', rawurlencode('../../application/config/config.php'), rawurlencode('..\\..\\index.php'),
    $name . '%00.php', strtoupper($name), '') as $bad) {
    list($event, , $ci) = dl_run($bad, array('Expense.View' => true));
    dl_check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_found', 'download_doc: rejects ' . json_encode($bad));
    dl_check(!in_array('get_where', $ci->db->calls, true), 'download_doc: invalid name never reaches the DB ' . json_encode($bad));
}

// D. file ada di disk tetapi tidak tercatat di tr_pengajuan_rutin_detail
list($event) = dl_run($name, array('Expense.View' => true), array('empty' => true));
dl_check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_found', 'download_doc: file on disk but not registered in DB -> 404');

// E. berhasil: isi, tipe, inline vs attachment, header aman
foreach (array('inline' => null, 'attachment' => '1') as $mode => $flag) {
    $GLOBALS['allowPerm'] = array('Expense.View' => true);
    $GLOBALS['pengajuanQueryCount'] = 0;
    $scenario = array(); $events = array();
    $ci = new DownloadTestController();
    $db = new RutinDb('business'); $hris = new RutinDb('hris');
    $ci->setHris($hris); $ci->db = $db; $ci->load = new RutinLoader($db, $hris); $ci->auth = new RutinAuth();
    $ci->template = new RutinTemplate(); $ci->output = new RutinOutput(); $ci->All_model = new RutinAllModel($db);
    $ci->Pengajuan_rutin_model = new RutinModel($db);
    $ci->input = new DownloadInput();
    $ci->input->dl = $flag;
    ob_start(); $ci->download_doc($name); ob_end_clean();
    dl_check($ci->sent['body'] === $body, 'download_doc ' . $mode . ': file content is sent unchanged');
    $h = $ci->sent['headers'];
    dl_check(in_array('Content-Type: application/pdf', $h, true), 'download_doc ' . $mode . ': content type');
    dl_check(in_array('Content-Disposition: ' . $mode . '; filename="' . $name . '"', $h, true), 'download_doc ' . $mode . ': disposition');
    dl_check(in_array('X-Content-Type-Options: nosniff', $h, true) && in_array('Content-Length: ' . strlen($body), $h, true), 'download_doc ' . $mode . ': nosniff and length');
    dl_check(count($events) === 1 && $events[0]['status'] === 1 && $events[0]['context']['data']['outcome'] === 'sent'
        && strpos(json_encode($events), $body) === false, 'download_doc ' . $mode . ': one audit event, outcome sent, no file content');
}

echo "PASS pengajuan_rutin download_doc: $dlChecks assertions
";
