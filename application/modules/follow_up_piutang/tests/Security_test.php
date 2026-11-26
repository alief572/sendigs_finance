<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/follow_up_piutang/tests/Security_test.php
// Batas data form/history/save/close, penolakan request non-AJAX (CSRF), dan parameter berbentuk array. Tanpa database.
define('BASEPATH', 1);
define('APPPATH', dirname(__DIR__, 3) . '/');
define('FCPATH', sys_get_temp_dir() . '/fu_sec_tidak_dipakai/');
define('DBCNL', 'db_consultant_new');
class Admin_Controller {}
class BF_Model {}
function has_permission($p) { return true; }
set_error_handler(function ($no, $msg) { fwrite(STDERR, "FAIL: warning PHP: $msg\n"); exit(1); });
require_once APPPATH . 'modules/follow_up_piutang/controllers/Follow_up_piutang.php';
require_once APPPATH . 'modules/follow_up_piutang/controllers/Plan_bayar.php';

$n = 0;
function ok($c, $m) { global $n; $n++; if (!$c) { fwrite(STDERR, "FAIL: $m\n"); exit(1); } }

class FakeModel {
    public $row, $tulis = [];
    function get_row($id) { return $this->row; }
    function get_rows() { return []; }
    function get_log($id) { return []; }
    function get_realisasi() { return []; }
    function get_bayar_sebelum_terima() { return []; }
    function save_ar($id, $input, $user) { $this->tulis[] = 'save'; return ['ok' => false, 'errors' => ['x']]; }
    function close_invoice($id, $reason, $user) { $this->tulis[] = 'close'; return ['ok' => true, 'errors' => []]; }
}
class FakeInput {
    public $get = [], $post = [], $ajax = true;
    function get($k) { return $this->get[$k] ?? null; }
    function post($k) { return $k === null ? $this->post : ($this->post[$k] ?? null); }
    function is_ajax_request() { return $this->ajax; }
}
class FakeOutput {
    public $code = 200, $body;
    function set_content_type($t) { return $this; }
    function set_status_header($c) { $this->code = $c; return $this; }
    function set_output($o) { $this->body = $o; return $this; }
}
class FakeAuth { function restrict($p) { return true; } function user_id() { return 7; } }

function buat($class) {
    $c = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $c->Monitoring_piutang_model = new FakeModel();
    $c->input = new FakeInput();
    $c->output = new FakeOutput();
    $c->auth = new FakeAuth();
    return $c;
}
function jalan($c, $method, $args = []) { $c->output = new FakeOutput(); $c->$method(...$args); return $c->output->code; }
function priv($c, $method, $args = []) { $m = new ReflectionMethod($c, $method); $m->setAccessible(true); return $m->invokeArgs($c, $args); }
function inv($o = []) {
    return $o + ['id' => 'I1', 'no_invoice' => '1/STM/2026', 'tgl_invoice' => '2026-10-05', 'customer' => 'PT A', 'project' => 'P', 'company' => '7',
        'nilai' => 100, 'saldo' => 100, 'sts_close' => '0', 'tax_treatment' => null, 'tgl_kirim' => null, 'tgl_terima' => null, 'plan_bayar' => null,
        'extra_fu' => [], 'bukti_extra' => []];
}

$c = buat('Follow_up_piutang');
$m = $c->Monitoring_piutang_model;
$c->input->post = ['id' => 'I1', 'reason' => 'lunas'];

// di luar cakupan: ditolak, model tulis tidak dipanggil
foreach (['deposit' => ['tax_treatment' => 'DEP'], 'kwitansi KD' => ['id' => 'KD-001'], 'invoice lama' => ['tgl_invoice' => '2020-01-01']] as $nama => $ubah) {
    $m->row = inv($ubah);
    $m->tulis = [];
    foreach (['form' => ['I1'], 'history' => ['I1'], 'save' => [], 'close' => []] as $method => $args) {
        ok(jalan($c, $method, $args) === 404, "$method $nama ditolak");
    }
    ok($m->tulis === [], "tidak ada tulis untuk $nama");
}
$m->row = null;
ok(jalan($c, 'save') === 404 && jalan($c, 'history', ['X']) === 404, 'invoice tidak ada ditolak');

// dalam cakupan: normal, pengecualian tanggal lama, dan close (tetap bisa dibaca)
foreach (['normal' => [], 'pengecualian' => ['no_invoice' => '239/STM/2023', 'tgl_invoice' => '2023-07-05'], 'close' => ['sts_close' => '1']] as $nama => $ubah) {
    $m->row = inv($ubah);
    ok(jalan($c, 'form', ['I1']) === 200, "form $nama terbuka");
    ok(jalan($c, 'history', ['I1']) === 200, "history $nama terbuka");
}
$m->row = inv();
$m->tulis = [];
jalan($c, 'save');
jalan($c, 'close');
ok($m->tulis === ['save', 'close'], 'simpan dan close invoice normal sampai ke model');

// CSRF: request biasa (bukan AJAX) ditolak sebelum menyentuh data
$c->input->ajax = false;
$m->tulis = [];
ok(jalan($c, 'save') === 403 && jalan($c, 'close') === 403 && $m->tulis === [], 'non-AJAX ditolak');
$c->input->ajax = true;

// parameter berbentuk array atau terlalu panjang
$c->input->post = ['id' => ['x'], 'reason' => ['y']];
ok(jalan($c, 'save') === 400, 'id berbentuk array ditolak');
$c->input->get = ['tab' => ['x'], 'company' => ['y'], 'search' => ['value' => ['z']]];
$f = priv($c, 'filters');
ok($f['tab'] === 'all' && $f['company'] === '' && $f['cari'] === '', 'filter array diabaikan');
$c->input->get = ['search' => ['value' => str_repeat('a', 300)]];
ok(strlen(priv($c, 'filters')['cari']) === 100, 'cari dibatasi 100 karakter');

$pb = buat('Plan_bayar');
$pb->input->get = ['tahun' => ['x'], 'company' => ['y'], 'dari' => ['z'], 'sampai' => ['z'], 'search' => ['value' => ['w']], 'draw' => '1'];
ok(jalan($pb, 'plan_data') === 200, 'Dashboard menerima parameter array tanpa warning');

echo "Security PASS ($n checks)\n";
