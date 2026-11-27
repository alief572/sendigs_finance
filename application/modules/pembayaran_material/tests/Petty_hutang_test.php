<?php
/**
 * Tes model Payment Hutang Petty Cash (PC-R-05, PC-R-07): COA Hutang ke STM / Piutang menurut company,
 * bank pembayar dan Bank STM penerima dipilih saat Payment, jurnal form divalidasi terhadap turunan server.
 * Fixture in-memory tanpa koneksi database. Jalankan: php Petty_hutang_test.php
 */
if (!defined('BASEPATH')) { define('BASEPATH', __DIR__); }
if (!class_exists('BF_Model')) { class BF_Model {} }
if (!class_exists('CI_Model')) { class CI_Model {} }
require dirname(__DIR__) . '/models/Pembayaran_material_model.php';

$GLOBALS['checks'] = 0;
function check($cond, $msg) {
    $GLOBALS['checks']++;
    if (!$cond) { throw new RuntimeException('GAGAL: ' . $msg); }
}
function expect_domain_error($fn, $contains, $msg) {
    try { $fn(); } catch (DomainException $e) {
        check(strpos($e->getMessage(), $contains) !== false, $msg . ' (pesan: ' . $e->getMessage() . ')');
        return;
    }
    check(false, $msg . ' (tidak melempar DomainException)');
}

class PhResult {
    private $row;
    public function __construct($row) { $this->row = $row; }
    public function row() { return $this->row ? (object) $this->row : null; }
}
class PhDb {
    public $tables = array();
    public function get_where($table, $cond) {
        foreach ($this->tables[$table] ?? array() as $r) {
            $ok = true;
            foreach ($cond as $k => $v) { if ((string) ($r[$k] ?? '') !== (string) $v) { $ok = false; } }
            if ($ok) { return new PhResult($r); }
        }
        return new PhResult(null);
    }
}
function ph_model($db) {
    $m = (new ReflectionClass('Pembayaran_material_model'))->newInstanceWithoutConstructor();
    $m->db = $db;
    return $m;
}
function ph_db() {
    $db = new PhDb();
    $db->tables['tr_petty_cash_vuca_sustain'] = array(
        array('no_payment_hutang' => 'PHP-1', 'company' => 'VUCA'),
        array('no_payment_hutang' => 'PHP-2', 'company' => 'vuca'),
        array('no_payment_hutang' => 'PHP-3', 'company' => 'SUSTAIN'),
        array('no_payment_hutang' => 'PHP-9', 'company' => 'XXX'),
    );
    $db->tables['ms_bank'] = array(
        array('id' => '1', 'deleted' => '0', 'coa_bank' => '1101-02-01'),
        array('id' => '2', 'deleted' => '0', 'coa_bank' => '1101-02-09'),
        array('id' => '3', 'deleted' => '1', 'coa_bank' => '1101-02-07'),
        array('id' => '4', 'deleted' => '0', 'coa_bank' => ''),
    );
    return $db;
}
function ph_payments(array $docs) { return array_map(function ($d) { return (object) array('no_doc' => $d); }, $docs); }

$m = ph_model(ph_db());

// 1. Company dari dokumen PHP
check($m->petty_hutang_company(ph_payments(array('PHP-1'))) === 'VUCA', 'company VUCA');
check($m->petty_hutang_company(ph_payments(array('PHP-1', 'PHP-2'))) === 'VUCA', 'dua dokumen VUCA (huruf kecil dinormalisasi)');
check($m->petty_hutang_company(ph_payments(array('PHP-3'))) === 'SUSTAIN', 'company SUSTAIN');
expect_domain_error(function () use ($m) { $m->petty_hutang_company(ph_payments(array('PHP-1', 'PHP-3'))); }, 'satu company', 'campur company ditolak');
expect_domain_error(function () use ($m) { $m->petty_hutang_company(ph_payments(array('PHP-404'))); }, 'tidak dapat ditentukan', 'dokumen tidak ada ditolak');
expect_domain_error(function () use ($m) { $m->petty_hutang_company(ph_payments(array('PHP-9'))); }, 'tidak dapat ditentukan', 'company tak dikenal ditolak');

// 2. Akun: COA hutang/piutang per company, bank dari ms_bank
$a = $m->petty_hutang_accounts('VUCA', '1', '2');
check($a === array('hutang' => '2103-01-01', 'piutang' => '1103-01-01', 'bank' => '1101-02-01', 'bank_stm' => '1101-02-09'), 'akun VUCA: Hutang ke STM 2103-01-01, Piutang 1103-01-01, bank dan bank STM dari ms_bank');
$a = $m->petty_hutang_accounts('sustain', 2, 1);
check($a['hutang'] === '2103-01-02' && $a['piutang'] === '1103-01-02' && $a['bank'] === '1101-02-09' && $a['bank_stm'] === '1101-02-01', 'akun SUSTAIN: 2103-01-02 / 1103-01-02');
check($a['hutang'] !== '2203-01-01', 'COA lama 2203-01-01 tidak dipakai lagi');
expect_domain_error(function () use ($m) { $m->petty_hutang_accounts('VUCA', '', '2'); }, 'Bank pembayar wajib', 'bank pembayar kosong ditolak');
expect_domain_error(function () use ($m) { $m->petty_hutang_accounts('VUCA', '1', ''); }, 'Bank STM penerima wajib', 'Bank STM penerima kosong ditolak (PC-N11)');
expect_domain_error(function () use ($m) { $m->petty_hutang_accounts('VUCA', '1', '404'); }, 'tidak ditemukan', 'bank STM id tidak ada ditolak');
expect_domain_error(function () use ($m) { $m->petty_hutang_accounts('VUCA', '3', '2'); }, 'tidak ditemukan', 'bank terhapus (deleted=1) ditolak');
expect_domain_error(function () use ($m) { $m->petty_hutang_accounts('VUCA', '4', '2'); }, 'tidak ditemukan', 'bank tanpa COA ditolak');
expect_domain_error(function () use ($m) { $m->petty_hutang_accounts('VUCA', array('1'), '2'); }, 'wajib dipilih', 'id bank bukan scalar ditolak');
expect_domain_error(function () use ($m) { $m->petty_hutang_accounts('XXX', '1', '2'); }, 'tidak valid', 'company tidak valid ditolak');

// 3. Validasi jurnal form terhadap akun turunan server
$acc = array('hutang' => '2103-01-01', 'piutang' => '1103-01-01', 'bank' => '1101-02-01', 'bank_stm' => '1101-02-09');
$r1 = array(1 => array('coa' => '2103-01-01', 'debit' => '350000', 'kredit' => '0'), 2 => array('coa' => '1101-02-01', 'debit' => '0', 'kredit' => '350000'));
$r2 = array(1 => array('coa' => '1101-02-09', 'debit' => '350000', 'kredit' => '0'), 2 => array('coa' => '1103-01-01', 'debit' => '0', 'kredit' => '350000'));
$m->validate_petty_hutang_journal($r1, $r2, $acc, 350000);
check(true, 'jurnal benar lolos');
$m->validate_petty_hutang_journal($r1, $r2, $acc, '350,000.00');
check(true, 'total dokumen berformat ribuan tetap cocok');

$mut = function ($rows, $i, $k, $v) { $rows[$i][$k] = $v; return $rows; };
expect_domain_error(function () use ($m, $r1, $r2, $acc, $mut) { $m->validate_petty_hutang_journal($mut($r1, 1, 'coa', '2203-01-01'), $r2, $acc, 350000); }, 'tidak sesuai', 'COA hutang lama 2203-01-01 ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc, $mut) { $m->validate_petty_hutang_journal($mut($r1, 2, 'coa', '1101-02-09'), $r2, $acc, 350000); }, 'tidak sesuai', 'bank company bukan yang dipilih ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc, $mut) { $m->validate_petty_hutang_journal($mut($r1, 2, 'coa', ''), $r2, $acc, 350000); }, 'tidak sesuai', 'bank company kosong (belum dipilih di form) ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc, $mut) { $m->validate_petty_hutang_journal($r1, $mut($r2, 1, 'coa', '1101-02-01'), $acc, 350000); }, 'tidak sesuai', 'bank STM bukan yang dipilih ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc, $mut) { $m->validate_petty_hutang_journal($r1, $mut($r2, 2, 'coa', '1103-01-02'), $acc, 350000); }, 'tidak sesuai', 'piutang company lain ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc, $mut) { $m->validate_petty_hutang_journal($mut($r1, 1, 'debit', '999'), $r2, $acc, 350000); }, 'Nominal', 'nominal debit tidak sama total ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc, $mut) { $m->validate_petty_hutang_journal($r1, $mut($r2, 2, 'kredit', '1'), $acc, 350000); }, 'Nominal', 'nominal kredit STM tidak sama total ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc) { $m->validate_petty_hutang_journal($r1, $r2, $acc, 400000); }, 'Nominal', 'total dokumen berbeda dari jurnal ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc) { $m->validate_petty_hutang_journal(null, $r2, $acc, 350000); }, 'tidak lengkap', 'jurnal company tidak dikirim ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc) { $m->validate_petty_hutang_journal($r1, array(1 => $r2[1]), $acc, 350000); }, 'tidak lengkap', 'jurnal STM hanya satu baris ditolak');
expect_domain_error(function () use ($m, $r1, $r2, $acc) { $m->validate_petty_hutang_journal($r1, array(1 => $r2[1], 2 => array('coa' => '1103-01-01')), $acc, 350000); }, 'tidak valid', 'baris tanpa debit/kredit ditolak');
$both_debit = array(1 => $r1[1], 2 => array('coa' => '1101-02-01', 'debit' => '350000', 'kredit' => '0'));
expect_domain_error(function () use ($m, $both_debit, $r2, $acc) { $m->validate_petty_hutang_journal($both_debit, $r2, $acc, 350000); }, 'satu debit dan satu kredit', 'dua baris debit ditolak');

// 4. SUSTAIN memakai pasangan COA yang berbeda: jurnal VUCA ditolak untuk dokumen SUSTAIN
$acc_s = $m->petty_hutang_accounts('SUSTAIN', '1', '2');
expect_domain_error(function () use ($m, $r1, $r2, $acc_s) { $m->validate_petty_hutang_journal($r1, $r2, $acc_s, 350000); }, 'tidak sesuai', 'jurnal berpasangan COA VUCA ditolak untuk SUSTAIN');

echo "PASS Petty hutang (PC-R-05/07): {$GLOBALS['checks']} pemeriksaan" . PHP_EOL;
