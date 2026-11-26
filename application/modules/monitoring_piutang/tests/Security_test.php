<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Security_test.php
// Batas data detail/surat, validasi parameter GET, dan akses file bukti terima. Tanpa database; file sementara di folder temp.
$tmp = str_replace('\\', '/', sys_get_temp_dir()) . '/mp_sec_' . getmypid() . '/';
define('BASEPATH', 1);
define('APPPATH', dirname(__DIR__, 3) . '/');
define('FCPATH', $tmp);
define('DBCNL', 'db_consultant_new');
class Admin_Controller {}
class BF_Model {}
class Berhenti extends Exception {}
function show_404() { throw new Berhenti('404'); }
function show_error($msg, $code) { throw new Berhenti((string) $code); }
function has_permission($p) { return in_array($p, $GLOBALS['izin'], true); }
require_once __DIR__ . '/../controllers/Monitoring_piutang.php';

$n = 0;
function ok($c, $m) { global $n; $n++; if (!$c) { fwrite(STDERR, "FAIL: $m\n"); exit(1); } }

class FakeModel {
    public $row;
    function get_row($id) { return $this->row; }
    function get_rows() { return []; }
    function get_receipts() { return []; }
}
class FakeInput { public $get = []; function get($k) { return $this->get[$k] ?? null; } }
class FakeOutput {
    public $code = 200; public $type; public $body; public $headers = [];
    function set_content_type($t) { $this->type = $t; return $this; }
    function set_status_header($c) { $this->code = $c; return $this; }
    function set_header($h) { $this->headers[] = $h; return $this; }
    function set_output($o) { $this->body = $o; return $this; }
}
class FakeAuth { function restrict($p) { if (!has_permission($p)) { throw new Berhenti('403'); } return true; } }

$c = (new ReflectionClass('Monitoring_piutang'))->newInstanceWithoutConstructor();
$c->Monitoring_piutang_model = new FakeModel();
$c->input = new FakeInput();
$c->auth = new FakeAuth();
function jalan($method, $args = []) {
    global $c;
    $c->output = new FakeOutput();
    try { $c->$method(...$args); } catch (Berhenti $e) { return $e->getMessage(); }
    return (string) $c->output->code;
}
function priv($method, $args) { global $c; $m = new ReflectionMethod($c, $method); $m->setAccessible(true); return $m->invokeArgs($c, $args); }
function inv($o = []) {
    return $o + ['id' => 'I1', 'no_invoice' => '1/STM/2026', 'tgl_invoice' => '2026-10-05', 'customer' => 'PT A', 'project' => 'P', 'company' => '7',
        'company_name' => 'STM', 'nilai' => 100, 'saldo' => 100, 'sts_close' => '0', 'tax_treatment' => null, 'tgl_kirim' => null, 'tgl_terima' => null,
        'plan_bayar' => null, 'extra_fu' => [], 'bukti_terima' => null, 'bukti_extra' => []];
}

// detail / surat: hanya invoice yang bisa muncul di daftar
$GLOBALS['izin'] = ['Monitoring_Piutang.View'];
$c->Monitoring_piutang_model->row = inv();
ok(jalan('detail', ['I1']) === '200', 'invoice dalam batas bisa dibuka');
foreach (['close' => ['sts_close' => '1'], 'deposit' => ['tax_treatment' => 'DEP'], 'kwitansi KD' => ['id' => 'KD-001'],
    'sebelum tanggal mulai' => ['tgl_invoice' => '2020-01-01']] as $nama => $ubah) {
    $c->Monitoring_piutang_model->row = inv($ubah);
    ok(jalan('detail', ['I1']) === '404', "detail $nama ditolak");
    ok(jalan('surat', ['I1']) === '404', "surat $nama ditolak");
}

// parameter GET
$c->input->get = ['cari' => ['x'], 'customer' => str_repeat('a', 300)];
$f = priv('filters', []);
ok($f['cari'] === '' && strlen($f['customer']) === 300, 'cari berbentuk array diabaikan');
$c->input->get = ['cari' => str_repeat('a', 300)];
ok(strlen(priv('filters', [])['cari']) === 100, 'cari dibatasi 100 karakter');
$rows = array_fill(0, 600, ['customer' => 'a']);
foreach (['-1' => 1, '100000' => 500, '0' => 10] as $length => $jumlah) {
    $c->input->get = ['length' => (string) $length, 'sort' => ['x']];
    $c->output = new FakeOutput();
    priv('dt_json', [$rows, ['customer']]);
    ok(count(json_decode($c->output->body, true)['data']) === $jumlah, "length $length = $jumlah baris");
}

// bukti terima
$dir = FCPATH . 'uploads/bukti_terima_invoice/';
mkdir($dir, 0775, true);
file_put_contents($dir . 'a.pdf', '%PDF-a');
file_put_contents($dir . 'b.png', 'png-b');
file_put_contents(FCPATH . 'rahasia.pdf', 'rahasia');
$c->Monitoring_piutang_model->row = inv(['sts_close' => '1', 'bukti_terima' => 'uploads/bukti_terima_invoice/a.pdf',
    'bukti_extra' => [['path' => 'uploads/bukti_terima_invoice/b.png', 'nama' => 'b'], ['path' => 'uploads/bukti_terima_invoice/../../rahasia.pdf', 'nama' => 'x']]]);
ok(jalan('bukti', ['I1', '0']) === '200' && $c->output->body === '%PDF-a' && $c->output->type === 'application/pdf', 'bukti pertama terkirim');
ok(in_array('X-Content-Type-Options: nosniff', $c->output->headers, true), 'nosniff');
ok(jalan('bukti', ['I1', '1']) === '200' && $c->output->type === 'image/png', 'bukti tambahan terkirim (invoice close boleh)');
foreach (['2' => 'path keluar folder', '9' => 'nomor tidak ada', '-1' => 'nomor negatif', 'x' => 'nomor bukan angka'] as $no => $nama) {
    ok(jalan('bukti', ['I1', (string) $no]) === '404', "$nama ditolak");
}
$GLOBALS['izin'] = ['Follow_Up_Piutang.View'];
ok(jalan('bukti', ['I1', '0']) === '200', 'izin Follow Up cukup');
$GLOBALS['izin'] = [];
ok(jalan('bukti', ['I1', '0']) === '403', 'tanpa izin ditolak');

ok(strpos(file_get_contents(APPPATH . '../.htaccess'), 'RewriteRule ^uploads/bukti_terima_invoice(/|$) - [F,L,NC]') !== false, 'akses langsung folder bukti ditolak');

array_map('unlink', [$dir . 'a.pdf', $dir . 'b.png', FCPATH . 'rahasia.pdf']);
rmdir($dir); rmdir(FCPATH . 'uploads'); rmdir(FCPATH);
echo "Security PASS ($n checks)\n";
