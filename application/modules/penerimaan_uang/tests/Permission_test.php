<?php
// Setiap endpoint Penerimaan Uang harus menolak pengguna tanpa izin SEBELUM menyentuh model/database. Tanpa database, tanpa browser.
define('BASEPATH', __DIR__);
class Admin_Controller {}
require dirname(__DIR__) . '/controllers/Penerimaan_uang.php';
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
$GLOBALS['perms'] = array();
function has_permission($permission) { return in_array($permission, $GLOBALS['perms'], true); }
function permCheck($ok, $message) { if (!$ok) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } }

// Model/db yang dipanggil = pelanggaran: penolakan harus terjadi sebelum akses data.
class TouchGuard { public $touched = 0; function __call($n, $a) { $this->touched++; throw new RuntimeException("data diakses: $n"); } function __get($n) { $this->touched++; throw new RuntimeException("data diakses: $n"); } }
class PermInput { function post($key = null) { return $key === null ? array() : null; } function get($key = null) { return null; } }
class PermAuth { public $restricted = array(); function restrict($p) { $this->restricted[] = $p; return true; } }

function makeController() {
    $c = (new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();
    $c->db = new TouchGuard; $c->Penerimaan_uang_model = new TouchGuard; $c->input = new PermInput; $c->auth = new PermAuth;
    return $c;
}
function callEndpoint($c, $method) {
    ob_start();
    try { $c->$method(); } catch (Throwable $e) { /* jalur diizinkan boleh gagal karena tidak ada data; yang diuji hanya penjaganya */ }
    return ob_get_clean();
}

// method => [izin, jawaban JSON?]
$endpoints = array(
    'get_report_penerimaan' => array('Penerimaan_Uang.View', true),
    'get_alokasi_penerimaan' => array('Penerimaan_Uang.View', true),
    'detail' => array('Penerimaan_Uang.View', false),
    'process_alokasi' => array('Penerimaan_Uang.Add', false),
    'process_kwitansi_deposit' => array('Penerimaan_Uang.Add', false),
    'save_kwitansi_deposit' => array('Penerimaan_Uang.Add', true),
    'save_penerimaan_piutang' => array('Penerimaan_Uang.Add', true),
);
foreach ($endpoints as $method => $def) {
    list($permission, $json) = $def;

    // 1. Tanpa izin: ditolak, model/db tidak tersentuh.
    $GLOBALS['perms'] = array();
    $c = makeController();
    $out = callEndpoint($c, $method);
    permCheck(strpos($out, 'Tidak memiliki izin') !== false, "$method menolak tanpa izin");
    permCheck($c->db->touched === 0 && $c->Penerimaan_uang_model->touched === 0, "$method tidak menyentuh data saat ditolak");
    if ($json) {
        $decoded = json_decode($out, true);
        permCheck(is_array($decoded) && $decoded['status'] === 0, "$method menjawab JSON status 0");
    }

    // 2. Izin yang salah (hanya izin lain) tetap ditolak.
    $GLOBALS['perms'] = array($permission === 'Penerimaan_Uang.View' ? 'Penerimaan_Uang.Add' : 'Penerimaan_Uang.View');
    $out = callEndpoint(makeController(), $method);
    permCheck(strpos($out, 'Tidak memiliki izin') !== false, "$method menolak izin yang tidak sesuai");

    // 3. Dengan izin yang benar: penjaga tidak menolak.
    $GLOBALS['perms'] = array($permission);
    $out = callEndpoint(makeController(), $method);
    permCheck(strpos($out, 'Tidak memiliki izin') === false, "$method tidak menolak pengguna yang berizin");
}

// export_excel adalah unduhan browser: memakai auth->restrict(View).
$c = makeController();
callEndpoint($c, 'export_excel');
permCheck($c->auth->restricted === array('Penerimaan_Uang.View'), 'export_excel meminta izin View lewat auth->restrict');

echo "PASS: 7 endpoint AJAX menolak tanpa/izin salah sebelum akses data dan lolos dengan izin yang benar; export_excel meminta izin View\n";
