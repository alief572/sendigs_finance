<?php
// CLI-only. Default dry-run (hanya membaca). Penulisan hanya dengan --apply + berkas review + --confirm-host.
// Contoh: php repair_wapu.php --id=00215-INV-VII-26
//         php repair_wapu.php --id=00215-INV-VII-26 --apply --review=docs/invoice-wapu-repair-review.json --backup=scratch/wapu-00215.json --confirm-host=206.84.97.107
// Koneksi mengikuti config/development/database.php; env WAPU_DB_HOST/USER/PASS/FIN/GL/CNL menimpa (untuk latihan di salinan lokal).
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH', __DIR__); define('ENVIRONMENT', 'development');
require dirname(__DIR__, 3) . '/config/development/database.php';
require dirname(__DIR__) . '/libraries/Invoice_wapu_repair.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$opt = getopt('', array('id:', 'apply', 'review:', 'backup:', 'confirm-host:', 'normalize', 'normalize-gl', 'hash:'));
if (empty($opt['id'])) { fwrite(STDERR, "Wajib: --id=<id invoice>\n"); exit(1); }
$env = function ($k, $default) { $v = getenv($k); return $v === false || $v === '' ? $default : $v; };
$host = $env('WAPU_DB_HOST', $db['default']['hostname']); $user = $env('WAPU_DB_USER', $db['default']['username']); $pass = $env('WAPU_DB_PASS', $db['default']['password']);
$finDb = $env('WAPU_DB_FIN', $db['default']['database']); $glDb = $env('WAPU_DB_GL', $db['accounting_stm']['database']); $cnlDb = $env('WAPU_DB_CNL', $db['consultant']['database']);
$connect = function () use ($host, $user, $pass, $finDb) { $c = new mysqli($host, $user, $pass, $finDb); $c->set_charset('utf8'); return $c; };
$fin = $connect(); $gl = $connect();
try {
    $repair = new Invoice_wapu_repair($fin, $gl, $finDb, $glDb, $cnlDb);
    if (isset($opt['normalize-gl'])) {
        // Tahap akhir: GL (JV asli + JV koreksi) -> satu JV asli berisi 4 baris WAPU; kartu piutang ikut dirapikan.
        $gp = $repair->normalizeGlPlan($opt['id']);
        $out = array('mode' => isset($opt['apply']) ? 'normalize_gl_apply' : 'normalize_gl_dry_run', 'target' => array('host' => $host, 'finance' => $finDb, 'gl' => $glDb), 'id' => $gp['id'], 'no_invoice' => $gp['no_invoice'],
            'jv_asli_ditulis_ulang' => $gp['original'], 'jv_koreksi_dihapus' => $gp['correction'],
            'gl_sekarang' => array_map(function ($r) { return array($r['nomor'], $r['no_perkiraan'], $r['debet'], $r['kredit'], $r['keterangan']); }, $gp['gl']),
            'gl_sesudah' => array_map(function ($l) use ($gp) { return array($gp['original'], $l['coa'], $l['debit'] / 100, $l['credit'] / 100, $l['description']); }, $gp['target']),
            'kartu_sekarang' => array_map(function ($r) { return array($r['nomor'], $r['debet'], $r['kredit']); }, $gp['cards']), 'kartu_sesudah_debet' => $gp['sales'] / 100,
            'saldo_bersih_per_akun_sen' => $gp['net'], 'hash' => $gp['hash']);
        if (!isset($opt['apply'])) { echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL; exit(0); }
        if (empty($opt['hash']) || empty($opt['backup']) || ($opt['confirm-host'] ?? '') !== $host) { fwrite(STDERR, "--apply wajib disertai --hash, --backup, dan --confirm-host=$host\n"); exit(1); }
        $result = $repair->normalizeGl($opt['id'], $opt['hash'], $opt['backup']);
        echo json_encode(array('mode' => 'normalize_gl_apply', 'result' => $result), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL; exit(0);
    }
    if (isset($opt['normalize'])) {
        // Tahap lanjutan: rapikan tr_jurnal (9 baris -> 4 baris jurnal WAPU standar). Apply wajib --hash dari dry-run, --backup, --confirm-host.
        $np = $repair->normalizePlan($opt['id']);
        $out = array('mode' => isset($opt['apply']) ? 'normalize_apply' : 'normalize_dry_run', 'target' => array('host' => $host, 'finance' => $finDb), 'id' => $np['id'], 'no_invoice' => $np['no_invoice'],
            'tr_jurnal_sekarang' => array_map(function ($r) { return array($r['no_jurnal'], $r['coa'], $r['nm_coa'], $r['debit'], $r['kredit'], $r['keterangan']); }, $np['before']),
            'tr_jurnal_sesudah' => array_map(function ($l) { return array($l['coa'], $l['name'], $l['debit'] / 100, $l['credit'] / 100, $l['description']); }, $np['target']),
            'saldo_bersih_per_akun_sen' => $np['net'], 'hash' => $np['hash']);
        if (!isset($opt['apply'])) { echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL; exit(0); }
        if (empty($opt['hash']) || empty($opt['backup']) || ($opt['confirm-host'] ?? '') !== $host) { fwrite(STDERR, "--apply wajib disertai --hash, --backup, dan --confirm-host=$host\n"); exit(1); }
        $result = $repair->normalize($opt['id'], $opt['hash'], $opt['backup']);
        echo json_encode(array('mode' => 'normalize_apply', 'result' => $result), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL; exit(0);
    }
    $plan = $repair->plan($opt['id']);
    $out = array('mode' => isset($opt['apply']) ? 'apply' : 'dry_run', 'target' => array('host' => $host, 'finance' => $finDb, 'gl' => $glDb), 'plan' => array(
        'id' => $plan['id'], 'no_invoice' => $plan['no_invoice'], 'customer' => $plan['customer'], 'journal_date' => $plan['date'], 'original_gl_number' => $plan['original_number'],
        'next_gl_number_estimate' => $plan['next_number'], 'source_company' => $plan['source_company'], 'owner' => $plan['owner'],
        'journal' => array_map(function ($l) { return array($l['coa'], $l['name'], $l['debit'] / 100, $l['credit'] / 100); }, $plan['journal']),
        'invoice_before' => $plan['before'], 'invoice_after' => array_diff_key($plan['fields'], array('consulting_snapshot' => 1)), 'hash' => $plan['hash']));
    if (!isset($opt['apply'])) { echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL; exit(0); }
    if (empty($opt['review']) || empty($opt['backup']) || ($opt['confirm-host'] ?? '') !== $host) {
        fwrite(STDERR, "--apply wajib disertai --review, --backup, dan --confirm-host=$host\n"); exit(1);
    }
    $review = json_decode((string)@file_get_contents($opt['review']), true);
    $result = $repair->apply($opt['id'], $review, $opt['backup']);
    echo json_encode($out + array('result' => $result), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'DITOLAK/GAGAL: ' . $e->getMessage() . PHP_EOL); exit(2);
} finally { $fin->close(); $gl->close(); }
