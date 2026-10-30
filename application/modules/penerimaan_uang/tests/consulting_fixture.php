<?php
// Test Suite for To-Be Consulting Receipt Engine & Accounting Journal
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
class Admin_Controller {}
class BF_Model { public $db; public function __construct() {} }
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
    public $customer_deposits = array();
    public $deposit_mutasi = array();
    public $kwitansi_deposits = array();
    public $journalCounter = 0;
    public $last_insert_id = 0;
    public $db_debug = false;
    public $fail_commit = false;

    public function insert_id() { return $this->last_insert_id; }

    public function query($sql, $params = array()) {
        if (strpos($sql, 'SELECT m.*, d.id_customer AS deposit_customer') === 0) {
            $rows=array();foreach($this->deposit_mutasi as $mutation)if($mutation['no_transaksi']===$params[0]&&$mutation['jenis_mutasi']==='OUT')foreach($this->customer_deposits as $voucher)if($voucher['id']===$mutation['id_deposit'])$rows[]=$mutation+array('deposit_customer'=>$voucher['id_customer'],'deposit_company'=>$voucher['id_company']);
            return new MockResult($rows);
        }

        if (strpos($sql, 'SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?') === 0) return new MockResult(array_values(array_filter($this->receipts, function($r) use($params){return $r['no_surat']===$params[0];})));
        if (strpos($sql, 'SELECT * FROM tr_invoicing WHERE no_invoice=?') === 0) return new MockResult(array_values(array_filter($this->invoices, function($r) use($params){return $r['no_invoice']===$params[0] && $r['tax_treatment']==='DEP';})));
        if (strpos($sql, 'SELECT * FROM tr_customer_deposit_mutasi WHERE id_deposit=? ORDER BY id ASC') === 0) return new MockResult(array_values(array_filter($this->deposit_mutasi, function($r) use($params){return $r['id_deposit']===$params[0];})));

        if (strpos($sql, 'SELECT d.*, h.no_surat AS receipt_no') === 0) {
            $rows = array();
            foreach ($this->receipt_details as $detail) {
                if ((string)$detail['id_inv'] !== (string)$params[0]) continue;
                $detail['receipt_no'] = null;
                foreach ($this->receipts as $header) if ($header['no_surat'] === $detail['id_header']) $detail['receipt_no'] = $header['no_surat'];
                $rows[] = $detail;
            }
            return new MockResult($rows);
        }
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
            foreach ($this->receipts as $r) {
                foreach ($this->alloc_splits as $split) {
                    if ($split['id'] == $r['id_alokasi'] && $split['id_alokasi_detail'] == $params[0]) $sum += (float)$r['nominal_penerimaan_bank'];
                }
            }
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
            // kons_tr_spk_penawaran tidak punya kolom no_spk (nomor SPK = id_spk_penawaran); query ini pernah membuat preview kwitansi gagal.
            throw new RuntimeException("Unknown column 'no_spk' in 'field list'");
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
        if (strpos($sql, 'SELECT COALESCE(SUM(sisa_saldo), 0) AS total_saldo FROM tr_customer_deposit') !== false) {
            $custId = $params[0];
            $sum = 0;
            foreach ($this->customer_deposits as $cd) {
                if ($cd['id_customer'] == $custId && (!isset($params[1]) || (string)($cd['id_company'] ?? '1') === (string)$params[1]) && in_array($cd['status'], array('AKTIF', 'TERPAKAI_SEBAGIAN'), true)) {
                    $sum += Penerimaan_calculation::cents($cd['sisa_saldo']);
                }
            }
            return new MockResult(array(array('total_saldo' => Invoice_calculation::decimal($sum))));
        }
        if (strpos($sql, 'FROM tr_customer_deposit WHERE id_customer=?') !== false) {
            $custId = $params[0];
            $rows = array();
            foreach ($this->customer_deposits as $cd) {
                if ($cd['id_customer'] == $custId && (!isset($params[1]) || (string)($cd['id_company'] ?? '1') === (string)$params[1]) && (in_array($cd['status'], array('AKTIF', 'TERPAKAI_SEBAGIAN'), true) || ($cd['status'] === 'LUNAS_TERPAKAI' && Penerimaan_calculation::cents($cd['sisa_saldo']) !== 0))) {
                    $rows[] = $cd;
                }
            }
            usort($rows, function($a, $b) {
                $c = strcmp($a['tanggal_aktif'] ?? $a['tanggal'], $b['tanggal_aktif'] ?? $b['tanggal']);
                return $c !== 0 ? $c : ($a['id'] - $b['id']);
            });
            return new MockResult($rows);
        }
        if (strpos($sql, 'SELECT no_deposit FROM tr_customer_deposit') !== false) {
            $like = str_replace('%', '', $params[0]);
            $last = null;
            foreach ($this->customer_deposits as $cd) {
                if (isset($cd['no_deposit']) && strpos($cd['no_deposit'], $like) === 0) {
                    $last = $cd;
                }
            }
            return new MockResult($last ? array($last) : array());
        }
        if (strpos($sql, 'kwitansi_number_candidates') !== false) {
            $numbers = array();
            foreach (array(array($this->kwitansi_deposits,'no_kwitansi'),array($this->invoices,'no_invoice'),array($this->customer_deposits,'no_referensi'),array($this->journals,'no_transaksi')) as $source) {
                foreach ($source[0] as $row) if (isset($row[$source[1]])) $numbers[] = array('document_number'=>$row[$source[1]]);
            }
            return new MockResult($numbers);
        }
        if (strpos($sql, 'deposit_number_candidates') !== false) {
            return new MockResult(array_map(function($row){return array('document_number'=>$row['no_deposit']);},$this->customer_deposits));
        }
        if (strpos($sql, 'SELECT no_kwitansi FROM tr_kwitansi_deposit') !== false) {
            $like = str_replace('%', '', $params[0]);
            $last = null;
            foreach ($this->kwitansi_deposits as $kd) {
                if (isset($kd['no_kwitansi']) && strpos($kd['no_kwitansi'], $like) === 0) {
                    $last = $kd;
                }
            }
            return new MockResult($last ? array($last) : array());
        }
        if (strpos($sql, 'FROM tr_jurnal WHERE no_transaksi = ?') !== false || strpos($sql, 'FROM tr_jurnal WHERE no_transaksi=?') !== false) {
            $trx = $params[0];
            $found = array_values(array_filter($this->journals, function($j) use ($trx) {
                return ($j['no_transaksi'] ?? '') === $trx;
            }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_customer_deposit WHERE no_referensi=?') !== false) {
            $ref = $params[0];
            $found = array_values(array_filter($this->customer_deposits, function($cd) use ($ref) {
                return ($cd['no_referensi'] ?? '') === $ref;
            }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_customer_deposit WHERE id=?') !== false) {
            $id = $params[0];
            $found = array_values(array_filter($this->customer_deposits, function($cd) use ($id) {
                return $cd['id'] == $id;
            }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_customer_deposit_mutasi WHERE id_deposit=?') !== false) {
            $depId = $params[0];
            $found = array_values(array_filter($this->deposit_mutasi, function($m) use ($depId) {
                return $m['id_deposit'] == $depId && $m['jenis_mutasi'] === 'OUT';
            }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_customer_deposit_mutasi WHERE no_transaksi=?') !== false) {
            $noTrx = $params[0];
            $found = array_values(array_filter($this->deposit_mutasi, function($m) use ($noTrx) {
                return $m['no_transaksi'] == $noTrx && $m['jenis_mutasi'] === 'OUT';
            }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_kwitansi_deposit WHERE no_kwitansi=?') !== false) {
            $kwNo = $params[0];
            $found = array_values(array_filter($this->kwitansi_deposits, function($kd) use ($kwNo) {
                return $kd['no_kwitansi'] === $kwNo;
            }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_kwitansi_deposit WHERE no_penerimaan=?') !== false) {
            $noPnr = $params[0];
            $found = array_values(array_filter($this->kwitansi_deposits, function($kd) use ($noPnr) {
                return ($kd['no_penerimaan'] ?? '') === $noPnr;
            }));
            return new MockResult($found);
        }
        if (strpos($sql, 'FROM tr_penerimaan_pph_23 WHERE id_detail_penerimaan=?') !== false) {
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
            'alloc_splits' => $this->alloc_splits,
            'customer_deposits' => $this->customer_deposits,
            'deposit_mutasi' => $this->deposit_mutasi,
            'kwitansi_deposits' => $this->kwitansi_deposits
        );
        return true;
    }
    public function trans_status() { return true; }
    public function trans_commit() { if ($this->fail_commit) return false; $this->txSnapshot = null; return true; }
    public function trans_rollback() {
        if ($this->txSnapshot !== null) {
            $this->invoices = $this->txSnapshot['invoices'];
            $this->receipts = $this->txSnapshot['receipts'];
            $this->receipt_details = $this->txSnapshot['receipt_details'];
            $this->journals = $this->txSnapshot['journals'];
            $this->alloc_details = $this->txSnapshot['alloc_details'];
            $this->alloc_splits = $this->txSnapshot['alloc_splits'];
            $this->customer_deposits = $this->txSnapshot['customer_deposits'];
            $this->deposit_mutasi = $this->txSnapshot['deposit_mutasi'];
            $this->kwitansi_deposits = $this->txSnapshot['kwitansi_deposits'];
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
            $this->last_insert_id = $row['id'];
            return true;
        }
        if ($table === 'tr_customer_deposit') {
            $row['id'] = count($this->customer_deposits) + 1;
            $this->customer_deposits[] = $row;
            $this->last_insert_id = $row['id'];
            return true;
        }
        if ($table === 'tr_customer_deposit_mutasi') {
            $row['id'] = count($this->deposit_mutasi) + 1;
            $this->deposit_mutasi[] = $row;
            $this->last_insert_id = $row['id'];
            return true;
        }
        if ($table === 'tr_kwitansi_deposit') {
            $row['id'] = count($this->kwitansi_deposits) + 1;
            $this->kwitansi_deposits[] = $row;
            $this->last_insert_id = $row['id'];
            return true;
        }
        if ($table === 'tr_invoicing') {
            if (!isset($row['id'])) {
                $row['id'] = count($this->invoices) + 1;
            }
            $this->invoices[] = $row;
            $this->last_insert_id = $row['id'];
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
        if ($table === 'tr_customer_deposit') {
            $id = $where['id'];
            foreach ($this->customer_deposits as &$cd) {
                if ($cd['id'] == $id) {
                    $cd = array_merge($cd, $data);
                }
            }
            return true;
        }
        if ($table === 'tr_kwitansi_deposit') {
            $id = $where['id'] ?? null;
            $no = $where['no_kwitansi'] ?? null;
            foreach ($this->kwitansi_deposits as &$kd) {
                if (($id && $kd['id'] == $id) || ($no && $kd['no_kwitansi'] == $no)) {
                    $kd = array_merge($kd, $data);
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
        if ($table === 'tr_customer_deposit') {
            if (isset($where['id'])) {
                $id = $where['id'];
                $this->customer_deposits = array_values(array_filter($this->customer_deposits, function($c) use ($id) { return $c['id'] != $id; }));
            }
            return true;
        }
        if ($table === 'tr_customer_deposit_mutasi') {
            if (isset($where['id_deposit'])) {
                $depId = $where['id_deposit'];
                $this->deposit_mutasi = array_values(array_filter($this->deposit_mutasi, function($m) use ($depId) { return $m['id_deposit'] != $depId; }));
            }
            if (isset($where['id'])) {
                $id = $where['id'];
                $this->deposit_mutasi = array_values(array_filter($this->deposit_mutasi, function($m) use ($id) { return $m['id'] != $id; }));
            }
            return true;
        }
        return true;
    }
}

class MockAuth { public function user_id() { return 'tester-user'; } }

class MockModel {
    private static $next = 0;
    public function generate_id() { return sprintf('%05d', ++self::$next) . '/RKN-KRG/X/26'; }
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
        array('no_perkiraan' => '7201-01-04', 'nama' => 'Biaya Administrasi Bank'),
        array('no_perkiraan' => '2102-01-02', 'nama' => 'DEPOSIT CUSTOMER')
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
            'pph' => array('code' => '1106-01-02', 'name' => 'PPh 23 Disetor'),
            'deposit' => array('code' => '2102-01-02', 'name' => 'DEPOSIT CUSTOMER')
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
            array('no_perkiraan' => '7201-01-04', 'nama' => 'Biaya Administrasi Bank'),
            array('no_perkiraan' => '2102-01-02', 'nama' => 'DEPOSIT CUSTOMER')
        );
    }
    public function mutate($action, $callback, $module='invoicing') {
        try {
            $payload = $callback();
            return array('status'=>1,'msg'=>'Data berhasil disimpan.','message'=>'Data berhasil disimpan.') + (is_array($payload)?$payload:array());
        } catch (Throwable $error) {
            echo "MUTATE THROWABLE: " . get_class($error) . ": " . $error->getMessage() . " at " . $error->getFile() . ":" . $error->getLine() . "\n";
            $message = $error instanceof InvalidArgumentException || $error instanceof RuntimeException ? $error->getMessage() : 'Proses gagal; perubahan dibatalkan.';
            return array('status'=>0,'msg'=>$message,'message'=>$message);
        }
    }
}

echo "Starting Consulting Receipt To-Be Test Suite...\n\n";

// =========================================================================

function seedDepositEvidence($db) {
    foreach ($db->customer_deposits as &$voucher) {
        $voucher += array('id_company'=>'1', 'tipe_sumber'=>'LEBIH_BAYAR', 'no_referensi'=>'SOURCE-'.$voucher['id'], 'no_deposit'=>'DEP-'.$voucher['id']);
        $number='SOURCE-'.$voucher['id']; $amount=Penerimaan_calculation::cents($voucher['nominal_awal']);
        $snapshot=array('owner'=>array('id'=>$voucher['id_company']), 'lebih'=>$amount, 'reduction'=>$amount, 'balance'=>$amount, 'sisa'=>0, 'cash'=>2*$amount, 'admin'=>0, 'pph'=>0, 'dep'=>0, 'tax_treatment'=>'KB');
        $invoiceId='SOURCE-INV-'.$voucher['id'];
        if ($voucher['tipe_sumber']==='KWITANSI_DEPOSIT') {
            $invoice=makeInvoice($invoiceId,$voucher['no_referensi'],'DEP',$amount,0,0,0,$amount,0);
            $owner=json_decode($invoice['consulting_snapshot'],true);$owner['owner']['id']=$voucher['id_company'];$owner['nilai']=$voucher['nominal_awal'];$invoice['consulting_snapshot']=json_encode($owner);$db->invoices[]=$invoice;
            $db->kwitansi_deposits[]=array('no_kwitansi'=>$voucher['no_referensi'], 'no_penerimaan'=>$number,'id_customer'=>$voucher['id_customer'],'id_company'=>$voucher['id_company'],'status'=>'LUNAS','nilai_kwitansi'=>$voucher['nominal_awal']);
            $snapshot['reduction']=$amount;$snapshot['lebih']=0;$snapshot['cash']=$amount;$snapshot['tax_treatment']='DEP';
        } else $voucher['no_referensi']=$number;
        $db->receipts[]=array('id'=>9000+$voucher['id'],'no_surat'=>$number,'id_customer'=>$voucher['id_customer'],'id_alokasi'=>'SOURCE','nominal_penerimaan_bank'=>Invoice_calculation::decimal($snapshot['cash']));
        $db->receipt_details[]=array('id'=>9000+$voucher['id'],'id_inv'=>$invoiceId,'id_header'=>$number,'id_customer'=>$voucher['id_customer'],'penerimaan'=>$voucher['nominal_awal'],'biaya_admin'=>'0.00','pph23'=>'0.00','sisa_piutang'=>'0.00','consulting_snapshot'=>json_encode($snapshot));
        $db->deposit_mutasi[]=array('id'=>count($db->deposit_mutasi)+1,'id_deposit'=>$voucher['id'],'id_customer'=>$voucher['id_customer'],'no_transaksi'=>$number,'tanggal'=>$voucher['tanggal_aktif'],'jenis_mutasi'=>'IN','nominal'=>$voucher['nominal_awal'],'saldo_sebelum'=>'0.00','saldo_sesudah'=>$voucher['nominal_awal']);
    }
    unset($voucher);
}
function depositOuts($db) { return array_values(array_filter($db->deposit_mutasi,function($row){return $row['jenis_mutasi']==='OUT';})); }
