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
$denied = [];
$path = dirname(__DIR__) . '/views/rp_index.php';
if (($argv[1] ?? '') === '--render') {
    echo (new ViewHarness(['tab' => $argv[2] ?? 'request']))->render($path, ['companies' => []]);
    exit;
}
foreach (['request', 'approval', 'record', 'invalid', ['approval']] as $tab) {
    $html = (new ViewHarness(['tab' => $tab]))->render($path, ['companies' => []]);
    $expected = is_string($tab) && in_array($tab, ['approval', 'record'], true) ? $tab : 'request';
    check(strpos($html, 'id="rp_module_tabs"') !== false, 'module tabs present');
    check(strpos($html, 'class="tab-pane active" id="rp_tab_' . $expected . '"') !== false, 'active tab ' . $expected);
    check(substr_count($html, 'id="table_rp"') === 1, 'request table not duplicated');
    check(strpos($html, 'get_data_pending_items') !== false && strpos($html, 'get_data_record_items') !== false, 'new list endpoints');
}
foreach ([['Approval_Request_Payment.View'], ['Record_Request_Payment.View'], ['Approval_Request_Payment.View', 'Record_Request_Payment.View']] as $denied) {
    $html = (new ViewHarness(['tab' => 'approval']))->render($path, ['companies' => []]);
    foreach (['approval' => 'Approval_Request_Payment.View', 'record' => 'Record_Request_Payment.View'] as $tab => $permission) {
        check((strpos($html, 'id="rp_tab_' . $tab . '"') === false) === in_array($permission, $denied, true), 'hide denied tab ' . $tab);
    }
}
$denied = [];
$new = file_get_contents($path);
preg_match('/<style>(.*?)<\/style>/s', $new, $newStyle);
check(hash('sha256', str_replace("\r\n", "\n", $newStyle[1])) === '2fe9e8d5cbf653ec188cd171e51cd0a2bc8cd5465ef2df704ff60c59c6bf3002', 'original Request CSS unchanged');
preg_match('/<!-- Request Payment -->(.*?)<!-- \/Request Payment -->/s', $new, $newBody);
check(hash('sha256', trim(str_replace("\r\n", "\n", $newBody[1]))) === 'b4a1f529ed2f08e12b2a854487eacee9284a5c47438ece5856d11728c7b804c0', 'original Request markup unchanged');

$header = (object) ['id' => 10, 'no_pengajuan' => 'PGJ-10', 'company_nama' => 'STM', 'submitted_at' => '2026-10-14 08:00:00', 'decided_at' => '2026-10-14 10:00:00'];
foreach (['approval', 'record'] as $tab) {
    $file = APPPATH . 'modules/' . ($tab === 'approval' ? 'approval_request_payment' : 'record_request_payment') . '/views/detail.php';
    foreach (['request_payment', 'https://evil.invalid', ['request_payment'], null] as $from) {
        $html = (new ViewHarness(['from' => $from]))->render($file, ['header' => $header, 'items' => [], 'records' => []]);
        $target = $from === 'request_payment' ? 'request_payment/index?tab=' . $tab : ($tab === 'approval' ? 'approval_request_payment' : 'record_request_payment');
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
echo "PASS Request Payment tab views: {$checks} assertions\n";$new = file_get_contents($path);
