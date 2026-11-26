<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Follow_up_targets_test.php
define('APPPATH', dirname(__DIR__, 3) . '/');
define('DBCNL', 'db_consultant_new');
#[\AllowDynamicProperties]
class Admin_Controller {}
#[\AllowDynamicProperties]
class BF_Model {}
function has_permission($permission) { return true; }
require_once __DIR__ . '/../libraries/Piutang_aging.php';
require_once __DIR__ . '/../models/Monitoring_piutang_model.php';
require_once APPPATH . 'modules/follow_up_piutang/controllers/Follow_up_piutang.php';
require_once APPPATH . 'modules/follow_up_piutang/controllers/Plan_bayar.php';

$n = 0;
function check($value, $message) { global $n; $n++; if (!$value) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }
function invoice($extra = []) {
    return $extra + ['id' => 'target', 'no_invoice' => '239/STM/2023', 'tgl_invoice' => '2023-07-05',
        'company' => '7', 'customer' => 'PT A', 'project' => 'P', 'nilai' => 100, 'saldo' => 100,
        'sts_close' => '0', 'tgl_kirim' => null, 'tgl_terima' => null, 'plan_bayar' => null, 'extra_fu' => []];
}
function invoke($object, $name, $args = []) {
    $method = new ReflectionMethod($object, $name); if (PHP_VERSION_ID < 80100) { $method->setAccessible(true); } return $method->invokeArgs($object, $args);
}
class TargetInput { public $values = []; function get($key, $xss = false) { return $this->values[$key] ?? null; } }
class TargetConfig { function load(...$args) {} function item(...$args) { return '2019-01-01'; } }
class TargetAuth { function restrict($permission) {} }
class TargetTemplate {
    public $values = [];
    function set($key, $value) { $this->values[$key] = $value; }
    function render($view) {}
}
class TargetRows {
    public $rows = [], $receipts = [];
    function get_rows($closed) { return $this->rows; }
    function get_realisasi($year) { return $this->receipts; }
    function get_bayar_sebelum_terima() { return []; }
    function get_dashboard_start_year() { return 2023; }
}
$expected = ['239/STM/2023', '020/VSB/2024', '003/VSB/2025', '005/VSB/2025', '008/VSB/2025',
    '011/VSB/2025', '013/VSB/2025', '050/SSC/2025', '010/VSB/2026', '011/VSB/2026', '127/STM/2026',
    '031/VSB/2026', '170/STM/2026', '228/STM/2026', '229/STM/2026', '230/STM/2026',
    '235/STM/2026', '204/STM/2026', '240/STM/2026', '018/VSB/2025'];
check(Piutang_aging::FOLLOW_UP_INVOICES === $expected && count(array_unique($expected)) === 20, 'exact 20 target numbers');
$today = '2026-11-19';
$model = new TargetRows();
foreach ($expected as $i => $no) {
    $r = invoice(['id' => "target$i", 'no_invoice' => $no]);
    check(Piutang_aging::eligible_invoice_date($r), "$no bypasses invoice cutoff");
    check(count(Piutang_aging::monitoring_base([$r], [], $today)) === 1, "$no in Monitoring base");
    $model->rows[] = $r;
}
$other = invoice(['id' => 'other', 'no_invoice' => '999/STM/2023']);
check(!Piutang_aging::eligible_invoice_date($other), 'unlisted old invoice excluded');
check(!Piutang_aging::eligible_invoice_date(invoice(['tgl_invoice' => '2023-02-30'])), 'target invalid date excluded');
check(!Piutang_aging::eligible_invoice_date(invoice(['tgl_invoice' => ''])), 'target empty date excluded');
check(!Piutang_aging::eligible_invoice_date(invoice(['no_invoice' => '239/STM/2023x'])), 'partial number excluded');
check(Piutang_aging::eligible_invoice_date(invoice(['no_invoice' => 'OTHER', 'tgl_invoice' => '2026-10-01'])), 'cutoff inclusive');
check(!Piutang_aging::eligible_invoice_date(invoice(['no_invoice' => 'OTHER', 'tgl_invoice' => '2026-09-30'])), 'day before cutoff excluded');
$r = invoice();
check(count(Piutang_aging::aged_list([$r], 'all', $today)) === 0, 'unreceived target stays out of aging list');
check(Piutang_aging::summary([$r], $today)['belum']['count'] === 1, 'unreceived target in summary');
check(count(Piutang_aging::aged_list([invoice(['tgl_terima' => '2026-11-01'])], 'all', $today)) === 1, 'received target in list');
check(count(Piutang_aging::monitoring_base([invoice(['sts_close' => '1'])], [], $today)) === 0, 'closed target stays excluded');
check(count(Piutang_aging::monitoring_base([$r], ['company' => 'VUCA'], $today)) === 0, 'company filter preserved');

$fu = (new ReflectionClass('Follow_up_piutang'))->newInstanceWithoutConstructor();
$fu->Monitoring_piutang_model = $model; $fu->config = new TargetConfig();
$model->rows[] = $other;
$filters = ['tab' => 'all', 'close_tahun' => '2026'];
check(count(invoke($fu, 'filtered', [$filters])) === 20, 'actual Follow Up controller admits all targets only');
check(count(invoke($fu, 'filtered', [$filters + ['inv_tahun' => '2023', 'inv_bulan' => '7']])) === 20, 'old year/month filter available');
check(count(invoke($fu, 'filtered', [$filters + ['inv_bulan' => '8']])) === 0, 'invoice month filter preserved');
$model->rows = [invoice(['id' => 'split1', 'no_invoice' => '228/STM/2026']), invoice(['id' => 'split2', 'no_invoice' => '228/STM/2026'])];
check(array_column(invoke($fu, 'filtered', [$filters]), 'id') === ['split1', 'split2'], 'valid multiple rows remain separate follow up records');
check(Piutang_aging::summary(Piutang_aging::monitoring_base($model->rows, [], $today), $today)['semua']['count'] === 2, 'Monitoring counts valid rows without collapsing invoice numbers');
$fu->template = new TargetTemplate(); $fu->auth = new TargetAuth(); $fu->index();
check(in_array(2023, $fu->template->values['inv_years'], true), 'Follow Up offers 2023');

foreach (['1' => [], '2' => ['tgl_kirim' => '2026-11-01'], '3' => ['tgl_kirim' => '2026-11-01', 'tgl_terima' => '2026-11-02'],
    '4' => ['tgl_kirim' => '2026-11-01', 'tgl_terima' => '2026-11-02', 'plan_bayar' => '2026-12-01']] as $tab => $fields) {
    $model->rows = [invoice($fields)];
    check(count(invoke($fu, 'filtered', [['tab' => (string) $tab, 'close_tahun' => '2026']])) === 1, "target in tab $tab");
}
$model->rows = [invoice(['sts_close' => '1', 'close_date' => '2026-11-01'])];
check(count(invoke($fu, 'filtered', [$filters])) === 0, 'closed not reopened in Follow Up');
check(count(invoke($fu, 'filtered', [['tab' => 'close', 'close_tahun' => '2026']])) === 1, 'closed target remains in close tab');

$pb = (new ReflectionClass('Plan_bayar'))->newInstanceWithoutConstructor();
$pb->Monitoring_piutang_model = $model; $pb->input = new TargetInput();
$pb->template = new TargetTemplate(); $pb->auth = new TargetAuth(); $pb->index();
check(in_array(2023, $pb->template->values['years'], true), 'dashboard offers historical year');
$pb->input->values = ['tahun' => '2023'];
$model->rows = [invoice(['plan_bayar' => '2023-08-01']), invoice(['id' => 'other', 'no_invoice' => 'OTHER', 'plan_bayar' => '2023-08-01'])];
$model->receipts = [['id_inv' => 'target', 'tanggal' => '2023-08-02', 'no_penerimaan' => 'R1', 'nominal' => 10],
    ['id_inv' => 'other', 'tanggal' => '2023-08-02', 'no_penerimaan' => 'R2', 'nominal' => 20]];
list($plan, $real, $summary) = invoke($pb, 'load_data');
check(count($plan) === 1 && $plan[0]['id'] === 'target', 'historical plan only target');
check(count($real) === 1 && $real[0]['id'] === 'target', 'historical receipt only target');
check($summary[7]['plan_count'] === 1 && $summary[7]['real_count'] === 1, 'historical cards match rows');
$model->rows[0]['plan_bayar'] = null;
list($plan, $real) = invoke($pb, 'load_data');
check(count($plan) === 0 && count($real) === 1, 'no artificial plan; receipt still valid');
$pb->input->values['company'] = 'VUCA';
list($plan, $real) = invoke($pb, 'load_data');
check(!$plan && !$real, 'historical company filter retained');
check(!Piutang_aging::dari_mulai(2026, 9) && Piutang_aging::dari_mulai(2026, 10), 'default period cutoff unchanged');
$pb->input->values = ['tahun' => '2026'];
$model->rows = [invoice(['id' => 'old-target', 'plan_bayar' => '2026-09-01']), invoice(['id' => 'old-other', 'no_invoice' => 'OTHER', 'plan_bayar' => '2026-09-01']), invoice(['id' => 'normal', 'no_invoice' => 'NORMAL', 'plan_bayar' => '2026-10-01'])];
$model->receipts = [];
list($plan) = invoke($pb, 'load_data');
check(array_column($plan, 'id') === ['old-target', 'normal'], 'cutoff year admits old target and normal October plan only');

class TargetQueryResult { public $year; function row_array() { return ['tahun' => $this->year]; } }
class TargetDb {
    public $year, $bindings;
    function query($sql, $bindings) { $this->bindings = $bindings; $r = new TargetQueryResult(); $r->year = $this->year; return $r; }
}
$dbModel = (new ReflectionClass('Monitoring_piutang_model'))->newInstanceWithoutConstructor(); $dbModel->db = new TargetDb();
$dbModel->db->year = '2023';
check($dbModel->get_dashboard_start_year() === 2023, 'earliest historical year used');
check($dbModel->db->bindings === array_merge($expected, $expected), 'both date queries bind exactly target list');
$dbModel->db->year = null;
check($dbModel->get_dashboard_start_year() === min((int) date('Y'), 2026), 'empty history retains normal start year');
$dbModel->db->year = '2027';
check($dbModel->get_dashboard_start_year() === min((int) date('Y'), 2026), 'future history does not remove current year');
echo "PASS: $n assertions\n";
