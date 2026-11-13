<?php
// Guard Consulting_posting::receipt: jurnal penerimaan dengan baris tanpa nomor COA (Beban/Hutang PPh Final UMKM) ditolak
// sebelum menyentuh accounting. Murni, tanpa DB.
// Jalankan: php application/modules/invoicing/tests/Posting_coa_guard_test.php
define('BASEPATH', __DIR__);
if (!function_exists('has_permission')) { function has_permission($p) { return true; } }
require dirname(__DIR__) . '/libraries/Consulting_posting.php';

class StubCi { public $db; }
class StubService extends Invoice_consulting_service {
    public $journalRows = array();
    public function __construct() {}
    public function transactional($tables) {}
    public function one($sql, $params = array()) {
        if (strpos($sql, 'FROM tr_penerimaan_piutang ') !== false) return array('no_surat' => 'RCV-1', 'nm_customer' => 'Cust');
        return $this->journalRows ? $this->journalRows[0] : null;
    }
    public function rows($sql, $params = array()) {
        return strpos($sql, 'FROM tr_jurnal') !== false ? $this->journalRows : array();
    }
    public function mutate($action, $callback, $module = 'invoicing') {
        try { $callback(); return array('status' => 1); }
        catch (RuntimeException $e) { return array('status' => 0, 'msg' => $e->getMessage()); }
    }
}
function guardRun($rows) {
    $posting = new Consulting_posting(new StubCi);
    $service = new StubService; $service->journalRows = $rows;
    $ref = new ReflectionProperty('Consulting_posting', 'service'); $ref->setAccessible(true); $ref->setValue($posting, $service);
    return $posting->receipt(1);
}
$line = function ($coa, $name) { return array('id' => 1, 'coa' => $coa, 'nm_coa' => $name, 'sts' => '0', 'debit' => '10.00', 'kredit' => '0.00', 'id_company' => 7); };
$r = guardRun(array($line('1101-02-01', 'Bank BCA'), $line('', 'Beban PPh Final')));
if ($r['status'] !== 0 || strpos($r['msg'], 'Akun Beban PPh Final belum memiliki nomor COA') === false) throw new RuntimeException('GAGAL: COA kosong harus ditolak, dapat: ' . json_encode($r));
$r = guardRun(array($line('1101-02-01', 'Bank BCA'), $line('   ', 'Hutang PPh Final')));
if ($r['status'] !== 0 || strpos($r['msg'], 'belum memiliki nomor COA') === false) throw new RuntimeException('GAGAL: COA spasi harus ditolak, dapat: ' . json_encode($r));
$r = guardRun(array($line('1101-02-01', 'Bank BCA')));
if (strpos($r['msg'] ?? '', 'nomor COA') !== false) throw new RuntimeException('GAGAL: COA lengkap tidak boleh ditolak oleh guard COA');
echo "PASS: 3 cek guard COA kosong pada posting penerimaan\n";
