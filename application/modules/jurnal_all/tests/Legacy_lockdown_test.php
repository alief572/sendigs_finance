<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/jurnal_all/tests/Legacy_lockdown_test.php
// Endpoint modul jurnal lama harus tertutup, sementara method tulis yang dipanggil Jurnal All tetap ada. Tanpa database.
define('BASEPATH', 1);
define('APPPATH', dirname(__DIR__, 3) . '/');
class Admin_Controller { public function __construct() {} }
class Tertutup extends Exception {}
function show_404() { throw new Tertutup('404'); }

$n = 0;
function ok($c, $m) { global $n; $n++; if (!$c) { fwrite(STDERR, "FAIL: $m\n"); exit(1); } }
function muat($modul) {
    $class = ucfirst($modul);
    require_once APPPATH . "modules/$modul/controllers/$class.php";
    return $class;
}

// report lama: ditolak di constructor, jadi tidak ada method yang bisa dipanggil
foreach (['report_jurnal_invoicing', 'report_jurnal_penerimaan', 'report_jurnal_payment'] as $modul) {
    $class = muat($modul);
    try { new $class(); ok(false, "$class harus 404"); } catch (Tertutup $e) { ok(true, "$class 404"); }
}

// jurnal_* lama: method baca 404 sebelum menyentuh data (objek tanpa db/model akan error bila lolos)
$baca = [
    'jurnal_invoicing' => ['index', 'add_jurnal', 'modal_posting_jurnal', 'download_excel', 'get_data_jurnal_invoicing'],
    'jurnal_penerimaan' => ['index', 'add_jurnal', 'modal_posting_jurnal', 'download_excel', 'get_data_jurnal_invoicing'],
    'jurnal_payment' => ['index', 'add_jurnal', 'get_data_jurnal', 'export_jurnal'],
];
foreach ($baca as $modul => $methods) {
    $class = muat($modul);
    $c = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    foreach ($methods as $method) {
        try { $c->$method(); ok(false, "$class::$method harus 404"); } catch (Tertutup $e) { ok(true, "$class::$method 404"); }
    }
}

// method tulis yang dipanggil Jurnal_all_dispatcher tetap ada
require_once APPPATH . 'modules/jurnal_all/libraries/Jurnal_all_dispatcher.php';
foreach (Jurnal_all_dispatcher::ROUTES as $routes) {
    foreach ($routes as $route) {
        list($modul, $method) = explode('/', $route);
        if (isset($baca[$modul])) {
            ok(method_exists(ucfirst($modul), $method) && !in_array($method, $baca[$modul], true), "$route tetap tersedia");
        }
    }
}

// fix_company (mass update tanpa cek izin) sudah dihapus
foreach (['jurnal', 'jurnal_invoicing', 'jurnal_penerimaan', 'jurnal_payment'] as $modul) {
    $source = file_get_contents(APPPATH . "modules/$modul/controllers/" . ucfirst($modul) . '.php');
    ok(stripos($source, 'fix_company') === false, "$modul tanpa fix_company");
}
ok(strpos(file_get_contents(APPPATH . 'modules/jurnal/views/index.php'), 'fix_company') === false, 'view jurnal tidak memanggil fix_company');

echo "PASS legacy lockdown: $n assertions\n";
