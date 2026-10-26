<?php
// Test Suite for To-Be Consulting Receipt Engine & Accounting Journal
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
class Admin_Controller {}
class BF_Model {}
function has_permission($p) { return true; }
function int_to_roman($m) { return array(1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII')[(int)$m]; }
function log_message($level, $message) {}

require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_calculation.php';
require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_consulting.php';
require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_consulting_service.php';
require_once dirname(__DIR__) . '/libraries/Penerimaan_calculation.php';
require_once dirname(__DIR__) . '/libraries/Consulting_receipt.php';

function checkAssert($cond, $msg) {
    if (!$cond) throw new RuntimeException("ASSERTION FAILED: " . $msg);
}

class MockResult {
    private $rows;
    public function __construct($rows = array()) { $this->rows = $rows; }
    public function row_array() { return $this->rows ? $this->rows[0] : array(); }
    public function result_array() { return $this->rows; }
    public function row() { return $this->rows ? (object)$this->rows[0] : null; }
    public function num_rows() { return count($this->rows); }
}

class MockDb {
    public $invoices = array();
    public $alloc_splits = array();
    public $alloc_details = array();
    public $banks = array();
    public $coa_masters = array();
    public $receipts = array();
    public $receipt_details = array();
    public $journals = array();
    public $spk_rows = array();
    public $journalCounter = 0;
    public $db_debug = false;

    public function query($sql, $params = array()) {
        if (strpos($sql, 'SELECT GET_LOCK(') === 0) {
            return new MockResult(array(array('acquired' => 1)));
        }
        if (strpos($sql, 'SELECT RELEASE_LOCK(') === 0) {
            return new MockResult(array(array('released' => 1)));
        }
        if (strpos($sql, 'SELECT * FROM ms_bank WHERE id=?') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->banks, function($b) use ($id) { return $b['id'] == $id; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT * FROM tr_alokasi_split WHERE id=?') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->alloc_splits, function($s) use ($id) { return $s['id'] == $id; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT * FROM tr_alokasi_detail WHERE id=?') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->alloc_details, function($d) use ($id) { return $d['id'] == $id; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT COALESCE(SUM(nominal_penerimaan_bank),0) AS cash FROM tr_penerimaan_piutang WHERE id_alokasi=?') !== false) {
            $allocId = $params[0];
            $sum = 0;
            foreach ($this->receipts as $r) {
                if ($r['id_alokasi'] == $allocId) $sum += (float)$r['nominal_penerimaan_bank'];
            }
            return new MockResult(array(array('cash' => Invoice_calculation::decimal(Penerimaan_calculation::cents((string)$sum)))));
        }
        if (strpos($sql, 'SELECT COALESCE(SUM(h.nominal_penerimaan_bank),0) AS cash FROM tr_penerimaan_piutang h LEFT JOIN tr_alokasi_split sp') !== false) {
            $sum = 0;
            foreach ($this->receipts as $r) $sum += (float)$r['nominal_penerimaan_bank'];
            return new MockResult(array(array('cash' => Invoice_calculation::decimal(Penerimaan_calculation::cents((string)$sum)))));
        }
        if (strpos($sql, 'SELECT * FROM tr_invoicing WHERE id=?') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->invoices, function($i) use ($id) { return $i['id'] == $id; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT id FROM tr_invoicing WHERE id=?') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->invoices, function($i) use ($id) { return $i['id'] == $id; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT no_spk FROM') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->spk_rows, function($s) use ($id) { return $s['id_spk_penawaran'] == $id; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_jurnal WHERE no_jurnal LIKE') !== false || strpos($sql, 'max_no FROM tr_jurnal') !== false || strpos($sql, 'SELECT MAX(id) as max_id FROM tr_jurnal') !== false) {
            return new MockResult(array(array('max_no' => ++$this->journalCounter, 'max_id' => $this->journalCounter)));
        }
        if (strpos($sql, 'SELECT MAX(no_surat) as maxP FROM tr_penerimaan_piutang') !== false) {
            return new MockResult(array(array('maxP' => '00001/RKN-KRG/X/26')));
        }
        if (strpos($sql, 'SELECT * FROM tr_jurnal WHERE no_transaksi=?') !== false) {
            $num = $params[0];
            $found = array_values(array_filter($this->journals, function($j) use ($num) { return $j['no_transaksi'] == $num; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT id FROM tr_penerimaan_piutang WHERE no_surat=?') !== false) {
            $num = $params[0];
            $found = array_values(array_filter($this->receipts, function($r) use ($num) { return $r['no_surat'] == $num; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT * FROM tr_penerimaan_piutang WHERE id=?') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->receipts, function($r) use ($id) { return $r['id'] == $id; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=?') !== false) {
            $hdr = $params[0];
            $found = array_values(array_filter($this->receipt_details, function($d) use ($hdr) { return $d['id_header'] == $hdr; }));
            return new MockResult($found);
        }
        if (strpos($sql, 'SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv=? AND id>?') !== false) {
            return new MockResult(array());
        }
        if (strpos($sql, 'SELECT ENGINE FROM information_schema.TABLES') !== false) {
            return new MockResult(array(array('ENGINE' => 'InnoDB')));
        }
        return new MockResult(array());
    }

    public function field_exists($field, $table) { return true; }
    public function table_exists($table) { return true; }
    private $txSnapshot;
    public function trans_begin() {
        $this->txSnapshot = array(
            'invoices' => $this->invoices,
            'receipts' => $this->receipts,
            'receipt_details' => $this->receipt_details,
            'journals' => $this->journals,
            'alloc_details' => $this->alloc_details,
            'alloc_splits' => $this->alloc_splits
        );
        return true;
    }
    public function trans_status() { return true; }
    public function trans_commit() { $this->txSnapshot = null; return true; }
    public function trans_rollback() {
        if ($this->txSnapshot !== null) {
            $this->invoices = $this->txSnapshot['invoices'];
            $this->receipts = $this->txSnapshot['receipts'];
            $this->receipt_details = $this->txSnapshot['receipt_details'];
            $this->journals = $this->txSnapshot['journals'];
            $this->alloc_details = $this->txSnapshot['alloc_details'];
            $this->alloc_splits = $this->txSnapshot['alloc_splits'];
            $this->txSnapshot = null;
        }
        return true;
    }

    public function get_where($table, $where) {
        if ($table === 'tr_invoicing') {
            $id = $where['id'];
            $found = array_values(array_filter($this->invoices, function($i) use ($id) { return $i['id'] == $id; }));
            return new MockResult($found);
        }
        if ($table === 'ms_bank') {
            $id = $where['id'];
            $found = array_values(array_filter($this->banks, function($b) use ($id) { return $b['id'] == $id; }));
            return new MockResult($found);
        }
        return new MockResult(array());
    }

    public function insert($table, $row) {
        if ($table === 'tr_penerimaan_piutang') {
            $row['id'] = count($this->receipts) + 1;
            $this->receipts[] = $row;
            return true;
        }
        return true;
    }

    public function insert_batch($table, $rows) {
        if ($table === 'tr_penerimaan_piutang_detail') {
            foreach ($rows as $r) {
                $r['id'] = count($this->receipt_details) + 1;
                $this->receipt_details[] = $r;
            }
            return true;
        }
        if ($table === 'tr_jurnal') {
            foreach ($rows as $r) {
                $r['id'] = count($this->journals) + 1;
                $this->journals[] = $r;
            }
            return true;
        }
        return true;
    }

    public function update($table, $data, $where) {
        if ($table === 'tr_invoicing') {
            $id = $where['id'];
            foreach ($this->invoices as &$inv) {
                if ($inv['id'] == $id) {
                    $inv = array_merge($inv, $data);
                }
            }
            return true;
        }
        if ($table === 'tr_alokasi_detail') {
            $id = $where['id'];
            foreach ($this->alloc_details as &$d) {
                if ($d['id'] == $id) {
                    $d = array_merge($d, $data);
                }
            }
            return true;
        }
        if ($table === 'tr_alokasi_split') {
            $id = $where['id'];
            foreach ($this->alloc_splits as &$s) {
                if ($s['id'] == $id) {
                    $s = array_merge($s, $data);
                }
            }
            return true;
        }
        return true;
    }

    public function delete($table, $where) {
        if ($table === 'tr_jurnal') {
            $no = $where['no_transaksi'];
            $this->journals = array_values(array_filter($this->journals, function($j) use ($no) { return $j['no_transaksi'] !== $no; }));
            return true;
        }
        if ($table === 'tr_penerimaan_piutang_detail') {
            $hdr = $where['id_header'];
            $this->receipt_details = array_values(array_filter($this->receipt_details, function($d) use ($hdr) { return $d['id_header'] !== $hdr; }));
            return true;
        }
        if ($table === 'tr_penerimaan_piutang') {
            $id = $where['id'];
            $this->receipts = array_values(array_filter($this->receipts, function($r) use ($id) { return $r['id'] != $id; }));
            return true;
        }
        return true;
    }
}

class MockAuth { public function user_id() { return 'tester-user'; } }

class MockModel {
    public function generate_id() { return '00001/RKN-KRG/X/26'; }
}

class MockCi {
    public $db;
    public $auth;
    public $Penerimaan_uang_model;
    public function __construct($db) {
        $this->db = $db;
        $this->auth = new MockAuth();
        $this->Penerimaan_uang_model = new MockModel();
    }
}

// Setup common fixtures
function createFixtures() {
    $db = new MockDb();

    // Banks
    $db->banks = array(
        array('id' => 1, 'nama' => 'Bank Mandiri', 'coa_bank' => '1101-02-01')
    );

    // Allocation: Bank deposit Rp 500.000.000
    $db->alloc_details = array(
        array(
            'id' => 10,
            'bank' => 1,
            'tipe_bank' => 1,
            'tanggal_transaksi' => '2026-10-26',
            'nominal_debit' => '500000000.00',
            'nilai_terpakai' => '0.00',
            'saldo' => '500000000.00',
            'keterangan' => 'Terima Transfer'
        )
    );
    $db->alloc_splits = array(
        array(
            'id' => 100,
            'id_alokasi_detail' => 10,
            'nominal' => '500000000.00',
            'nilai_terpakai' => '0.00',
            'jenis_alokasi' => '1'
        )
    );

    // COA Master for Company 1
    $db->coa_masters = array(
        array('no_perkiraan' => '1101-02-01', 'nama' => 'Bank Mandiri Operasional'),
        array('no_perkiraan' => '1102-01-01', 'nama' => 'Piutang Dagang Usaha'),
        array('no_perkiraan' => '1106-01-02', 'nama' => 'PPh 23 Disetor'),
        array('no_perkiraan' => '7201-01-04', 'nama' => 'Biaya Administrasi Bank')
    );

    // SPK
    $db->spk_rows = array(
        array('id_spk_penawaran' => 'SPK-001', 'no_spk' => 'SPK/KONS/X/2026')
    );

    return $db;
}

function makeInvoice($id, $no, $taxTreatment, $sales, $dppLain, $ppn, $pph, $tagihan, $saldo) {
    $snapshot = array(
        'type' => $taxTreatment,
        'owner' => array('id' => '1', 'nm_company' => 'PT Konsultan Prima'),
        'accounts' => array(
            'receivable' => array('code' => '1102-01-01', 'name' => 'Piutang Dagang Usaha'),
            'pph' => array('code' => '1106-01-02', 'name' => 'PPh 23 Disetor')
        )
    );
    return array(
        'id' => $id,
        'no_invoice' => $no,
        'tanggal_invoice' => '2026-10-25',
        'id_customer' => 'CUST-001',
        'nm_customer' => 'PT Mitra Sejahtera',
        'id_spk_penawaran' => 'SPK-001',
        'consulting_version' => 3,
        'tax_treatment' => $taxTreatment,
        'total_nominal_jurnal' => Invoice_calculation::decimal($sales),
        'dpp_lain_lain_jurnal' => Invoice_calculation::decimal($dppLain),
        'ppn_jurnal' => Invoice_calculation::decimal($ppn),
        'pph_jurnal' => Invoice_calculation::decimal($pph),
        'tagihan_ppn_jurnal' => Invoice_calculation::decimal($tagihan),
        'total_akhir_jurnal' => Invoice_calculation::decimal($tagihan),
        'saldo_piutang' => Invoice_calculation::decimal($saldo),
        'consulting_snapshot' => json_encode($snapshot),
        'tipe_invoice' => '0',
        'non_kons' => '0'
    );
}

class CustomService extends Invoice_consulting_service {
    public function master($owner) {
        return array(
            array('no_perkiraan' => '1101-02-01', 'nama' => 'Bank Mandiri Operasional'),
            array('no_perkiraan' => '1102-01-01', 'nama' => 'Piutang Dagang Usaha'),
            array('no_perkiraan' => '1106-01-02', 'nama' => 'PPh 23 Disetor'),
            array('no_perkiraan' => '7201-01-04', 'nama' => 'Biaya Administrasi Bank')
        );
    }
}

echo "Starting Consulting Receipt To-Be Test Suite...\n\n";

// =========================================================================
// TEST 1: PKP Invoice Receipt (Full Payment with Biaya Admin & PPh 23)
// =========================================================================
echo "=== TEST 1: PKP Full Payment with Admin & PPh 23 ===\n";
$db = createFixtures();
// PKP: Sales 200jt, DPP Lain 183.333.333,33, PPN 22jt, PPh 3.666.666,67, Tagihan/Piutang 222jt
$inv1 = makeInvoice(1, 'INV-PKP-001', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv1);

$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);

$refS = new ReflectionProperty('Consulting_receipt', 's');
$refS->setAccessible(true);
$refS->setValue($engine, new CustomService($ci));

// 1a. Preview
$prevPost = array(
    'choose_inv' => array(1),
    'id_alokasi' => 100,
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y'
);
$prevRes = $engine->preview($prevPost);
checkAssert(count($prevRes['rows']) === 1, 'Preview returns 1 invoice row');
checkAssert($prevRes['rows'][0]['tax_treatment'] === 'PKP', 'Preview detects PKP');
checkAssert($prevRes['rows'][0]['default_pph'] === 366666667, 'Preview default PPh 23 matches 2%');

// 1b. Save
// Piutang: 222.000.000 (22200000000 cents), PPh: 3.666.666,67 (366666667 cents), Admin: 2.500 (250000 cents), Sisa: 0
// Bank Cash = 22200000000 - 366666667 - 250000 = 21833083333 cents = 218.330.833,33
// Kontrol: (22200000000 - 366666667 - 250000 - 0) - 21833083333 = 0.
$savePost = array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 1,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '2500.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '218330833.33'
);
$saveRes = $engine->save($savePost);
checkAssert($saveRes['status'] === 1 && $saveRes['save'] === 1, 'PKP Save success: ' . ($saveRes['msg'] ?? ''));

// Verify Jurnal Akuntansi
checkAssert(count($db->journals) === 4, '4 journal lines created (Bank, PPh, Admin, Piutang Gross)');
$totDebit = 0; $totKredit = 0;
foreach ($db->journals as $j) {
    $totDebit += Penerimaan_calculation::cents($j['debit']);
    $totKredit += Penerimaan_calculation::cents($j['kredit']);
    if ($j['coa'] === '1101-02-01') checkAssert(Penerimaan_calculation::cents($j['debit']) === 21833083333, 'Bank Debit correct');
    if ($j['coa'] === '1106-01-02') checkAssert(Penerimaan_calculation::cents($j['debit']) === 366666667, 'PPh Debit correct');
    if ($j['coa'] === '7201-01-04') checkAssert(Penerimaan_calculation::cents($j['debit']) === 250000, 'Admin Debit correct');
    if ($j['coa'] === '1102-01-01') checkAssert(Penerimaan_calculation::cents($j['kredit']) === 22200000000, 'Piutang Gross Kredit correct');
}
checkAssert($totDebit === $totKredit, 'Journal is 100% BALANCED');
checkAssert($totDebit === 22200000000, 'Journal total equals gross receivable');
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 0, 'Invoice saldo_piutang is 0 (lunas)');
echo "PASS: PKP Full Payment with Admin & PPh 23\n\n";

// =========================================================================
// TEST 2: WAPU Invoice Receipt (Tagihan = Sales, PPN = 0, PPh 23 Dipotong)
// =========================================================================
echo "=== TEST 2: WAPU Invoice Receipt ===\n";
$db = createFixtures();
// WAPU: Sales 300jt, DPP Lain 275jt, PPN 0, PPh 5.5jt, Tagihan/Piutang 300jt
$inv2 = makeInvoice(2, 'INV-WAPU-001', 'WAPU', 30000000000, 27500000000, 0, 550000000, 30000000000, 30000000000);
$db->invoices = array($inv2);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(2), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));
checkAssert($prevRes['rows'][0]['tax_treatment'] === 'WAPU', 'Preview detects WAPU');

// Bank Cash = 300jt - 5.5jt = 294.500.000
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 2,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '5500000.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '294500000.00'
));
checkAssert($saveRes['status'] === 1, 'WAPU Save success: ' . ($saveRes['msg'] ?? ''));
$totDebit = 0; $totKredit = 0;
foreach ($db->journals as $j) {
    $totDebit += Penerimaan_calculation::cents($j['debit']);
    $totKredit += Penerimaan_calculation::cents($j['kredit']);
}
checkAssert($totDebit === $totKredit && $totDebit === 30000000000, 'WAPU Journal 100% Balanced');
echo "PASS: WAPU Invoice Receipt\n\n";

// =========================================================================
// TEST 3: Kurang Bayar (Partial Payment with Sisa Piutang > 0)
// Resolving Decision 3: 2 baris piutang (Kredit Gross & Debit Sisa Piutang)
// =========================================================================
echo "=== TEST 3: Partial Payment (Underpayment) - 2 Receivable Journal Lines ===\n";
$db = createFixtures();
// Invoice Piutang 222jt, PPh 3.666.666,67.
$inv3 = makeInvoice(3, 'INV-PARTIAL-001', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv3);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(3), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));

// Customer pays partial: Bank Cash Rp 100.000.000
// PPh 23: Rp 3.666.666,67
// Admin: Rp 0
// Sisa Piutang = 22200000000 - 366666667 - 10000000000 = 11833333333 cents = 118.333.333,33
// Kontrol = (22200000000 - 366666667 - 0 - 11833333333) - 10000000000 = 0.
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 3,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '118333333.33',
    'penerimaan_1' => '100000000.00'
));
checkAssert($saveRes['status'] === 1, 'Partial payment save success: ' . ($saveRes['msg'] ?? ''));

// Verify Decision 3 Accounting Journal:
// 1. Bank Debit: 100.000.000
// 2. PPh 23 Debit: 3.666.666,67
// 3. Piutang Usaha Kredit (Gross): 222.000.000
// 4. Piutang Usaha Debit (Kurang Bayar / Sisa Piutang): 118.333.333,33
checkAssert(count($db->journals) === 4, '4 journal rows created including 2 receivable rows');
$debitBank = 0; $debitPph = 0; $debitPiutangSisa = 0; $kreditPiutangGross = 0;
foreach ($db->journals as $j) {
    if ($j['coa'] === '1101-02-01') $debitBank += Penerimaan_calculation::cents($j['debit']);
    if ($j['coa'] === '1106-01-02') $debitPph += Penerimaan_calculation::cents($j['debit']);
    if ($j['coa'] === '1102-01-01') {
        if (Penerimaan_calculation::cents($j['debit']) > 0) $debitPiutangSisa += Penerimaan_calculation::cents($j['debit']);
        if (Penerimaan_calculation::cents($j['kredit']) > 0) $kreditPiutangGross += Penerimaan_calculation::cents($j['kredit']);
    }
}
checkAssert($debitBank === 10000000000, 'Bank Debit is 100jt');
checkAssert($debitPph === 366666667, 'PPh Debit is 3.666.666,67');
checkAssert($debitPiutangSisa === 11833333333, 'Receivable Debit (Sisa Piutang) is 118.333.333,33');
checkAssert($kreditPiutangGross === 22200000000, 'Receivable Gross Kredit is 222.000.000');

$totalDebit = $debitBank + $debitPph + $debitPiutangSisa;
checkAssert($totalDebit === $kreditPiutangGross, 'Decision 3: Total Debit equals Total Kredit (100% Balanced)');

// Verify Net Mutation on Receivable
$netReceivableCredit = $kreditPiutangGross - $debitPiutangSisa;
checkAssert($netReceivableCredit === (10000000000 + 366666667), 'Net receivable reduction matches Bank + PPh');

// Verify updated saldo_piutang in invoice table
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 11833333333, 'Invoice saldo_piutang updated to 118.333.333,33');
echo "PASS: Partial Payment with 2 Receivable Journal Lines (Decision 3)\n\n";

// =========================================================================
// TEST 4: Editable PPh 23 per Invoice
// =========================================================================
echo "=== TEST 4: Editable PPh 23 per Invoice ===\n";
$db = createFixtures();
$inv4 = makeInvoice(4, 'INV-PPH-EDIT', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv4);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(4), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));

// Customer deducted rounded PPh 23 = Rp 3.666.000 (different by Rp 667)
// Bank Cash = 222.000.000 - 3.666.000 = 218.334.000
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 4,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666000.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '218334000.00'
));
checkAssert($saveRes['status'] === 1, 'Custom PPh 23 save success: ' . ($saveRes['msg'] ?? ''));
$savedPphDebit = 0;
foreach ($db->journals as $j) {
    if ($j['coa'] === '1106-01-02') $savedPphDebit += Penerimaan_calculation::cents($j['debit']);
}
checkAssert($savedPphDebit === 366600000, 'Custom PPh 23 of 3.666.000 reflected in journal');
echo "PASS: Editable PPh 23 per Invoice\n\n";

// =========================================================================
// TEST 5: Rejection Validation (Kontrol != 0, Admin > 10.000, Exceeding Bank)
// =========================================================================
echo "=== TEST 5: Rejection Validations ===\n";
$db = createFixtures();
$inv5 = makeInvoice(5, 'INV-REJECT', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv5);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(5), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));

// 5a. Kontrol != 0
$resKontrol = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 5,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '200000000.00' // Unbalanced by ~18.333.333
));
checkAssert($resKontrol['status'] === 0 && strpos($resKontrol['msg'], 'Kontrol belum 0') !== false, 'Rejected non-zero kontrol');

// 5b. Biaya Admin > Rp 10.000
$resAdmin = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 5,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '15000.00', // Admin 15.000 > 10.000
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '218318333.33'
));
checkAssert($resAdmin['status'] === 0 && strpos($resAdmin['msg'], '10.000') !== false, 'Rejected admin > 10.000');
echo "PASS: Rejection Validations\n\n";

// =========================================================================
// TEST 6: Rollback Verification
// =========================================================================
echo "=== TEST 6: Rollback Verification ===\n";
$db = createFixtures();
$inv6 = makeInvoice(6, 'INV-ROLLBACK', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv6);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(6), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 6,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '2500.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '218330833.33'
));
checkAssert($saveRes['status'] === 1, 'Initial receipt saved for rollback');
$receiptId = $db->receipts[0]['id'];
checkAssert(count($db->receipts) === 1, 'Receipt exists before rollback');
checkAssert(count($db->journals) > 0, 'Journals exist before rollback');
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 0, 'Invoice paid before rollback');

// Execute rollback
$rbRes = $engine->rollback($receiptId);
checkAssert($rbRes['status'] === 1, 'Rollback returned status 1');
checkAssert(count($db->receipts) === 0, 'Header receipt deleted');
checkAssert(count($db->receipt_details) === 0, 'Detail receipts deleted');
checkAssert(count($db->journals) === 0, 'Accounting journals deleted');

// Invoice saldo_piutang restored
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 22200000000, 'Invoice saldo_piutang restored to 222.000.000');

// Allocation bank restored
checkAssert(Penerimaan_calculation::cents($db->alloc_details[0]['nilai_terpakai']) === 0, 'Bank allocation nilai_terpakai restored to 0');
checkAssert(Penerimaan_calculation::cents($db->alloc_splits[0]['nilai_terpakai']) === 0, 'Bank split nilai_terpakai restored to 0');
echo "PASS: Rollback Verification\n\n";

echo "ALL CONSULTING TO-BE INTEGRATION TESTS PASSED 100%!\n";
