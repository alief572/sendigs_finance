<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/jurnal_all/tests/Journal_write_permission_test.php
// Posting/revisi jurnal lama harus ditolak tanpa izin Manage, sebelum menyentuh data. Tanpa database.
define('BASEPATH', 1);
define('APPPATH', dirname(__DIR__, 3) . '/');
#[AllowDynamicProperties]
class Admin_Controller {}
function has_permission($p) { return in_array($p, $GLOBALS['diberi'], true); }

$n = 0;
function ok($c, $m) { global $n; $n++; if (!$c) { fwrite(STDERR, "FAIL: $m\n"); exit(1); } }
class FakeInput { public $method = 'POST'; function method($upper = false) { return $this->method; } function post() { throw new RuntimeException('lolos'); } }
class FakeOutput { public $code = 200; function set_status_header($c) { $this->code = $c; return $this; } }
class FakeModel { function __call($m, $a) { throw new RuntimeException('lolos'); } }

$kasus = [
    'jurnal_invoicing' => ['Jurnal_Invoicing.Manage', ['save_posting_jurnal', 'update_sts_revisi_jurnal']],
    'jurnal_penerimaan' => ['Jurnal_Penerimaan.Manage', ['save_posting_jurnal', 'update_sts_revisi_jurnal']],
    'jurnal_payment' => ['Jurnal_Payment.Manage', ['save_posting_jurnal']],
];
foreach ($kasus as $modul => list($izin, $methods)) {
    $class = ucfirst($modul);
    require_once APPPATH . "modules/$modul/controllers/$class.php";
    $c = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $c->input = new FakeInput();
    $c->{$class . '_model'} = new FakeModel();
    $c->db = new FakeModel();
    $jalan = function ($method) use ($c) {
        $c->output = new FakeOutput();
        ob_start();
        try { $c->$method(); $lolos = false; } catch (RuntimeException $e) { $lolos = true; }
        ob_end_clean();
        return $lolos ? 'lolos' : $c->output->code;
    };
    foreach ($methods as $method) {
        $GLOBALS['diberi'] = [];
        ok($jalan($method) === 403, "$class::$method tanpa izin ditolak");
        $GLOBALS['diberi'] = ["$class.View", "$class.Add"];
        ok($jalan($method) === 403, "$class::$method hanya View/Add ditolak");
        foreach ([$izin, 'Jurnal_All.Manage'] as $punya) {
            $GLOBALS['diberi'] = [$punya];
            $c->input->method = 'GET';
            ok($jalan($method) === 403, "$class::$method GET ditolak");
            $c->input->method = 'POST';
            // lolos pemeriksaan izin: tidak dijawab 403 (handler lanjut menyentuh input/model/lock tiruan)
            ok($jalan($method) !== 403, "$class::$method dengan $punya diteruskan");
        }
    }
}
echo "PASS journal write permission: $n assertions\n";
