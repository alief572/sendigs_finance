<?php
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__, 3) . '/');
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
function has_permission($permission) { global $denied; return !in_array($permission, $denied, true); }
function site_url($uri = '') { return 'http://localhost/' . ltrim($uri, '/'); }
function base_url($uri = '') { return site_url($uri); }
function html_escape($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
class ViewInput {
    public $values = [];
    public function get_post($key) { return $this->values[$key] ?? null; }
}
class ViewLoader {
    public function view($view, $values = []) { extract($values); include dirname(__DIR__) . '/views/' . $view . '.php'; }
}
class ViewHarness {
    public $input;
    public $load;
    public function __construct($values) { $this->input = new ViewInput(); $this->input->values = $values; $this->load = new ViewLoader(); }
    public function render($file, $values = []) { extract($values); ob_start(); include $file; return ob_get_clean(); }
}
$denied = ['Request_Payment.View', 'Request_Payment.Manage'];
$path = APPPATH . 'modules/record_request_payment/views/index.php';
if (($argv[1] ?? '') === '--render') {
    echo (new ViewHarness(['tab' => $argv[2] ?? 'batch']))->render($path);
    exit;
}
foreach (['batch', 'items', 'legacy', 'invalid', ['items']] as $tab) {
    $html = (new ViewHarness(['tab' => $tab]))->render($path);
    $expected = is_string($tab) && in_array($tab, ['items', 'legacy'], true) ? $tab : 'batch';
    $pane = ['batch' => 'tab_baru', 'items' => 'tab_items', 'legacy' => 'tab_lama'][$expected];
    check(strpos($html, 'class="tab-pane active" id="' . $pane . '"') !== false, 'Record active tab ' . $expected);
    check(strpos($html, 'Per Transaksi (Approved)') !== false, 'Record per-item tab present');
    check(strpos($html, 'get_data_record_items') !== false, 'Record item endpoint');
    check(strpos($html, 'Pengajuan Baru (Batch)') < strpos($html, 'Per Transaksi (Approved)') && strpos($html, 'Per Transaksi (Approved)') < strpos($html, 'Histori Lama (Pre-Cutoff)'), 'tab order');
}
$requestPath = APPPATH . 'modules/request_payment/views/rp_index.php';
$requestHtml = (new ViewHarness(['tab' => 'approval']))->render($requestPath, ['companies' => []]);
check(strpos($requestHtml, 'rp_module_tabs') === false, 'Request has no added tabs');
check(strpos($requestHtml, 'get_data_pending_items') === false && strpos($requestHtml, 'get_data_record_items') === false, 'Request has no extra endpoints');
$new = file_get_contents($requestPath);
preg_match('/<style>(.*?)<\/style>/s', $new, $newStyle);
check(hash('sha256', str_replace("\r\n", "\n", $newStyle[1])) === '2fe9e8d5cbf653ec188cd171e51cd0a2bc8cd5465ef2df704ff60c59c6bf3002', 'original Request CSS unchanged');
preg_match('/<div class="box">.*?(?=<!-- Modal Histori Reject -->)/s', $new, $newBody);
check(hash('sha256', trim(str_replace("\r\n", "\n", $newBody[0]))) === 'b4a1f529ed2f08e12b2a854487eacee9284a5c47438ece5856d11728c7b804c0', 'original Request markup unchanged');
$denied = [];

$header = (object) ['id' => 10, 'no_pengajuan' => 'PGJ-10', 'company_nama' => 'STM', 'submitted_at' => '2026-10-14 08:00:00', 'decided_at' => '2026-10-14 10:00:00'];
foreach (['approval', 'record'] as $tab) {
    $file = APPPATH . 'modules/' . ($tab === 'approval' ? 'approval_request_payment' : 'record_request_payment') . '/views/detail.php';
    foreach (['items', 'request_payment', 'https://evil.invalid', ['items'], null] as $from) {
        $html = (new ViewHarness(['from' => $from]))->render($file, ['header' => $header, 'items' => [], 'records' => []]);
        $target = $tab === 'record' && $from === 'items' ? 'record_request_payment/index?tab=items' : ($tab === 'approval' ? 'approval_request_payment' : 'record_request_payment');
        check(strpos($html, 'http://localhost/' . $target) !== false, 'fixed return target ' . $tab);
        check(strpos($html, 'evil.invalid') === false, 'no untrusted return URL');
    }
}
$denied = ['Approval_Request_Payment.Manage'];
$html = (new ViewHarness([]))->render(APPPATH . 'modules/approval_request_payment/views/detail.php', ['header' => $header, 'items' => []]);
check(strpos($html, 'onclick="apprSaveDecision();"') === false, 'canonical Manage denied hides save');
$denied = ['Request_Payment.Manage'];
$html = (new ViewHarness([]))->render(APPPATH . 'modules/approval_request_payment/views/detail.php', ['header' => $header, 'items' => []]);
check(strpos($html, 'onclick="apprSaveDecision();"') !== false, 'canonical Manage allowed shows save');
echo "PASS Record Request Payment tab views: {$checks} assertions\n";$new = file_get_contents($path);
