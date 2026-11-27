<?php
/**
 * Tes model jurnal petty cash (PC-R-02, PC-R-04): jurnal dibentuk saat Approve, COA Petty Cash dari master.
 * Fixture in-memory tanpa koneksi database. Jalankan: php Journal_approve_test.php
 */
if (!defined('BASEPATH')) { define('BASEPATH', __DIR__); }
foreach (array('DBACC' => 'acc', 'DBACC_STM' => 'acc_stm', 'DBACC_VUCA' => 'acc_vuca', 'DBACC_SUSTAIN' => 'acc_sustain') as $c => $v) {
    if (!defined($c)) { define($c, $v); }
}
if (!class_exists('BF_Model')) { class BF_Model {} }
if (!function_exists('log_message')) { function log_message($l, $m) {} }

require dirname(__DIR__) . '/models/Expense_petty_cash_model.php';

$GLOBALS['checks'] = 0;
function check($cond, $msg) {
    $GLOBALS['checks']++;
    if (!$cond) { throw new RuntimeException('GAGAL: ' . $msg); }
}

class JrResult {
    private $rows;
    public function __construct($rows) { $this->rows = array_values($rows); }
    public function num_rows() { return count($this->rows); }
    public function row() { return $this->rows ? (object) $this->rows[0] : null; }
    public function result() { return array_map(function ($r) { return (object) $r; }, $this->rows); }
}

/** DB in-memory minimal: where/select/get/insert/update/delete/count_all_results/query/field_exists. */
class JrDb {
    public $jurnal = array();        // baris tr_jurnal
    public $pencatatan = array();    // id => row
    public $master = array();        // id => row (ms_petty_cash)
    public $coa = array();           // kode => nama
    public $has_coa_column = true;
    private $where = array();

    public function field_exists($f, $t) { return $this->has_coa_column; }
    public function select($s) { return $this; }
    public function where($k, $v = null) { $this->where[$k] = $v; return $this; }
    private function match($row) {
        foreach ($this->where as $k => $v) {
            if (!array_key_exists($k, $row) || (string) $row[$k] !== (string) $v) { return false; }
        }
        return true;
    }
    public function get($table) {
        $src = $table === 'ms_petty_cash' ? $this->master : $this->pencatatan;
        $out = array_filter($src, function ($r) { return $this->match($r); });
        $this->where = array();
        return new JrResult($out);
    }
    public function count_all_results($table) {
        $n = count(array_filter($this->jurnal, function ($r) { return $this->match($r); }));
        $this->where = array();
        return $n;
    }
    public function insert($table, $row) { $this->jurnal[] = $row; return true; }
    public function update($table, $data) {
        foreach ($this->pencatatan as $id => &$r) { if ($this->match($r)) { $r = array_merge($r, $data); } }
        unset($r);
        $this->where = array();
        return true;
    }
    public function delete($table) {
        $this->jurnal = array_values(array_filter($this->jurnal, function ($r) { return !$this->match($r); }));
        $this->where = array();
        return true;
    }
    public function query($sql, $params = array()) {
        if (stripos($sql, 'FROM tr_jurnal WHERE no_jurnal LIKE') !== false) {
            $suffix = ltrim($params[0], '%');
            $rows = array();
            foreach (array_reverse($this->jurnal) as $j) {
                if (substr($j['no_jurnal'], -strlen($suffix)) === $suffix) { $rows[] = array('no_jurnal' => $j['no_jurnal']); break; }
            }
            return new JrResult($rows);
        }
        if (stripos($sql, '.coa_master') !== false) {
            $kode = $params[0];
            return new JrResult(isset($this->coa[$kode]) ? array(array('nama' => $this->coa[$kode])) : array());
        }
        throw new RuntimeException('Query tidak dikenal di fixture: ' . $sql);
    }
    public function __call($n, $a) { return $this; }
}

class JrAuth { public function user_id() { return 10; } }

/** Model nyata; hanya get_pencatatan (JOIN rumit) yang disediakan dari fixture. */
class JrModel extends Expense_petty_cash_model {
    public function get_pencatatan($id) {
        $row = $this->db->pencatatan[$id] ?? null;
        if (!$row) { return false; }
        $r = new stdClass();
        $r->header = (object) $row;
        $r->details = array_map(function ($d) { return (object) $d; }, $row['_details']);
        return $r;
    }
}

function jr_model($db) {
    $m = (new ReflectionClass('JrModel'))->newInstanceWithoutConstructor();
    $m->db = $db;
    $m->auth = new JrAuth();
    return $m;
}

function jr_db() {
    $db = new JrDb();
    $db->master = array(4 => array('id' => 4, 'coa_petty_cash' => '1101-01-05'), 5 => array('id' => 5, 'coa_petty_cash' => ''));
    $db->coa = array('6101-03-01' => 'Biaya ATK', '1101-01-05' => 'Kas Kecil Pusat', '1101-01-02' => 'Kas Kecil',
                     '2103-01-01' => 'Hutang VUCA ke STM', '1103-01-01' => 'Piutang VUCA');
    return $db;
}

function jr_pencatatan($id, $no, $tgl, $company, $pc_id, $total, $coa = '6101-03-01') {
    return array('id' => $id, 'no_pencatatan' => $no, 'tanggal' => $tgl, 'company' => $company, 'petty_cash_id' => $pc_id,
                 'keterangan' => 'Beli ATK', 'journal_status' => 'pending', 'status' => 'approved',
                 '_details' => array(array('coa_code' => $coa, 'pengeluaran' => 'ATK', 'total' => $total)));
}

function jr_sum($rows, $field) { return array_sum(array_map(function ($r) use ($field) { return (int) $r[$field]; }, $rows)); }
function jr_by_jurnal($rows) { $g = array(); foreach ($rows as $r) { $g[$r['no_jurnal']][] = $r; } return $g; }

// 1. STM: kredit memakai COA Petty Cash dari master, tanggal = tanggal pencatatan, seimbang
$db = jr_db();
$db->pencatatan[1] = jr_pencatatan(1, 'PCP-2026-0101', '2026-11-17', 'STM', 4, 350000);
$r = jr_model($db)->create_journals_for_approval(array(1));
check($r['success'] && $r['created'] === 1 && $r['skipped'] === 0, 'STM: 1 jurnal dibuat');
$kredit = array_values(array_filter($db->jurnal, function ($j) { return $j['kredit'] > 0; }));
check(count($kredit) === 1 && $kredit[0]['coa'] === '1101-01-05' && $kredit[0]['nm_coa'] === 'Kas Kecil Pusat', 'STM: kredit = COA Petty Cash master (1101-01-05), bukan 1101-01-02');
check(jr_sum($db->jurnal, 'debit') === 350000 && jr_sum($db->jurnal, 'kredit') === 350000, 'STM: debit = kredit = 350.000');
check(count(array_unique(array_column($db->jurnal, 'tgl_jurnal'))) === 1 && $db->jurnal[0]['tgl_jurnal'] === '2026-11-17', 'STM: tanggal jurnal = tanggal pencatatan');
check($db->jurnal[0]['no_transaksi'] === 'PCP-2026-0101' && $db->jurnal[0]['jenis_transaksi'] === 'Petty Cash' && $db->jurnal[0]['sts'] === '0', 'STM: no transaksi, jenis, status staging');
check($db->pencatatan[1]['journal_status'] === 'success', 'STM: journal_status success');

// 2. Master tanpa COA (data lama / kosong) -> fallback 1101-01-02 Kas Kecil
$db = jr_db();
$db->pencatatan[2] = jr_pencatatan(2, 'PCP-2026-0102', '2026-11-18', 'STM', 5, 450000);
check(jr_model($db)->create_journals_for_approval(array(2))['success'], 'fallback: sukses');
$kredit = array_values(array_filter($db->jurnal, function ($j) { return $j['kredit'] > 0; }));
check($kredit[0]['coa'] === '1101-01-02' && $kredit[0]['nm_coa'] === 'Kas Kecil', 'fallback: COA kosong -> 1101-01-02 Kas Kecil');
// kolom coa_petty_cash belum dimigrasi -> fallback juga
$db = jr_db(); $db->has_coa_column = false;
check(jr_model($db)->get_coa_petty_cash(4) === '1101-01-02', 'fallback: kolom belum dimigrasi -> 1101-01-02');

// 3. VUCA intercompany: dua jurnal seimbang, sisi STM memakai COA Petty Cash master
$db = jr_db();
$db->pencatatan[3] = jr_pencatatan(3, 'PCP-2026-0103', '2026-11-19', 'VUCA', 4, 500000);
check(jr_model($db)->create_journals_for_approval(array(3))['success'], 'VUCA: sukses');
$groups = jr_by_jurnal($db->jurnal);
check(count($groups) === 2, 'VUCA: dua nomor jurnal (buku VUCA dan buku STM)');
foreach ($groups as $no => $rows) {
    check(jr_sum($rows, 'debit') === 500000 && jr_sum($rows, 'kredit') === 500000, "VUCA: jurnal $no seimbang 500.000");
}
$coas = array_column($db->jurnal, 'coa');
check(in_array('2103-01-01', $coas, true) && in_array('1103-01-01', $coas, true) && in_array('1101-01-05', $coas, true) && !in_array('1101-01-02', $coas, true), 'VUCA: Hutang ke STM 2103-01-01, Piutang 1103-01-01, kas = COA master');
$stm_side = array_values(array_filter($db->jurnal, function ($j) { return $j['nm_company'] === 'STM'; }));
check(count($stm_side) === 2 && in_array('1101-01-05', array_column($stm_side, 'coa'), true), 'VUCA: kredit kas ada di sisi STM');

// 4. Beberapa pencatatan, satu sudah berjurnal (data lama) -> dilewati, tidak ganda
$db = jr_db();
$db->pencatatan[1] = jr_pencatatan(1, 'PCP-2026-0101', '2026-11-17', 'STM', 4, 350000);
$db->pencatatan[2] = jr_pencatatan(2, 'PCP-2026-0102', '2026-11-18', 'STM', 4, 450000);
$db->pencatatan[3] = jr_pencatatan(3, 'PCP-2026-0103', '2026-11-19', 'STM', 4, 500000);
$db->jurnal[] = array('no_jurnal' => '00001-AJV-XI-26', 'tgl_jurnal' => '2026-11-18', 'coa' => '6101-03-01', 'debit' => 450000, 'kredit' => 0, 'no_transaksi' => 'PCP-2026-0102', 'jenis_transaksi' => 'Petty Cash', 'sts' => '0');
$before = count($db->jurnal);
$r = jr_model($db)->create_journals_for_approval(array(1, 2, 3));
check($r['success'] && $r['created'] === 2 && $r['skipped'] === 1, 'skip: 2 dibuat, 1 jurnal lama dilewati');
$per_no = array_count_values(array_column($db->jurnal, 'no_transaksi'));
check($per_no['PCP-2026-0102'] === 1 && $per_no['PCP-2026-0101'] === 2 && $per_no['PCP-2026-0103'] === 2, 'skip: pencatatan 0102 tetap 1 baris (tidak ganda)');
check(count($db->jurnal) === $before + 4, 'skip: hanya 4 baris baru (2 jurnal x 2 baris)');
$nos = array_unique(array_column($db->jurnal, 'no_jurnal'));
check(count($nos) === 3, 'skip: 3 nomor jurnal berbeda (1 lama + 2 baru), tidak bentrok');
// Jalankan lagi: idempoten
$r = jr_model($db)->create_journals_for_approval(array(1, 2, 3));
check($r['success'] && $r['created'] === 0 && $r['skipped'] === 3 && count($db->jurnal) === $before + 4, 'idempoten: approve ulang tidak menambah jurnal');

// 5. Gagal: company tidak valid -> success false + failed_id, berhenti di yang gagal
$db = jr_db();
$db->pencatatan[1] = jr_pencatatan(1, 'PCP-2026-0101', '2026-11-17', 'STM', 4, 350000);
$db->pencatatan[9] = jr_pencatatan(9, 'PCP-2026-0109', '2026-11-18', 'XXX', 4, 100000);
$db->pencatatan[10] = jr_pencatatan(10, 'PCP-2026-0110', '2026-11-19', 'STM', 4, 200000);
$r = jr_model($db)->create_journals_for_approval(array(1, 9, 10));
check($r['success'] === false && $r['failed_id'] === 9 && $r['created'] === 1, 'gagal: berhenti di pencatatan 9, failed_id tercatat');
check(!in_array('PCP-2026-0110', array_column($db->jurnal, 'no_transaksi'), true), 'gagal: pencatatan sesudahnya tidak diproses');
$r = jr_model(jr_db())->create_journals_for_approval(array(404));
check($r['success'] === false && $r['failed_id'] === 404, 'gagal: pencatatan tidak ditemukan');

// 6. Edit: hanya jurnal belum diposting (sts 0) yang dihapus; yang diposting tidak disentuh
$db = jr_db();
$db->pencatatan[1] = jr_pencatatan(1, 'PCP-2026-0101', '2026-11-17', 'STM', 4, 350000);
$db->pencatatan[1]['journal_status'] = 'success';
$db->jurnal[] = array('no_jurnal' => 'A', 'coa' => 'x', 'no_transaksi' => 'PCP-2026-0101', 'jenis_transaksi' => 'Petty Cash', 'sts' => '0', 'debit' => 1, 'kredit' => 0);
$db->jurnal[] = array('no_jurnal' => 'A', 'coa' => 'y', 'no_transaksi' => 'PCP-2026-0101', 'jenis_transaksi' => 'Petty Cash', 'sts' => '0', 'debit' => 0, 'kredit' => 1);
$db->jurnal[] = array('no_jurnal' => 'B', 'coa' => 'z', 'no_transaksi' => 'PCP-2026-0999', 'jenis_transaksi' => 'Petty Cash', 'sts' => '0', 'debit' => 1, 'kredit' => 0);
check(jr_model($db)->reset_journal_for_edit(1) === true, 'edit: reset sukses');
check(count($db->jurnal) === 1 && $db->jurnal[0]['no_transaksi'] === 'PCP-2026-0999', 'edit: jurnal belum diposting milik pencatatan ini dihapus, milik lain utuh');
check($db->pencatatan[1]['journal_status'] === 'pending', 'edit: journal_status kembali pending (belum ada jurnal)');
$db = jr_db();
$db->pencatatan[1] = jr_pencatatan(1, 'PCP-2026-0101', '2026-11-17', 'STM', 4, 350000);
$db->pencatatan[1]['journal_status'] = 'success';
$db->jurnal[] = array('no_jurnal' => 'A', 'coa' => 'x', 'no_transaksi' => 'PCP-2026-0101', 'jenis_transaksi' => 'Petty Cash', 'sts' => '1', 'debit' => 1, 'kredit' => 0);
jr_model($db)->reset_journal_for_edit(1);
check(count($db->jurnal) === 1 && $db->pencatatan[1]['journal_status'] === 'success', 'edit: jurnal yang sudah diposting tidak dihapus dan status tidak berubah');
check(jr_model(jr_db())->reset_journal_for_edit(404) === false, 'edit: pencatatan tidak ada -> false');

// 7. Paritas preview vs jurnal nyata: Preview di Approval harus sama dengan yang dibuat saat Approve
foreach (array('STM', 'VUCA', 'SUSTAIN') as $company) {
    $db = jr_db();
    $db->coa['2103-01-02'] = 'Hutang SUSTAIN ke STM';
    $db->coa['1103-01-02'] = 'Piutang SUSTAIN';
    $row = jr_pencatatan(1, 'PCP-2026-0201', '2026-11-20', $company, 4, 0);
    $row['_details'] = array(
        array('coa_code' => '6101-03-01', 'pengeluaran' => 'ATK', 'total' => 120000),
        array('coa_code' => '6101-03-01', 'pengeluaran' => 'Kertas', 'total' => 30000),
    );
    $db->pencatatan[1] = $row;
    $model = jr_model($db);
    check($model->create_journals_for_approval(array(1))['success'], "paritas $company: jurnal dibuat");

    $preview = Expense_petty_cash_model::build_journal_preview(
        $company, '2026-11-20',
        array(array('coa_code' => '6101-03-01', 'pengeluaran' => 'ATK', 'total' => 120000), array('coa_code' => '6101-03-01', 'pengeluaran' => 'Kertas', 'total' => 30000)),
        $model->get_coa_petty_cash_info(4),
        'Beli ATK'
    );
    $actual_books = array_values(jr_by_jurnal($db->jurnal));
    check(count($preview) === count($actual_books), "paritas $company: jumlah buku jurnal sama (" . count($preview) . ')');
    foreach ($preview as $i => $book) {
        $actual = array_map(function ($j) {
            return array($j['tgl_jurnal'], $j['coa'], $j['nm_company'], (int) $j['debit'], (int) $j['kredit'], $j['keterangan']);
        }, $actual_books[$i]);
        $expected = array_map(function ($r) {
            return array($r['tanggal'], $r['coa'], $r['company'], $r['debit'], $r['kredit'], $r['keterangan']);
        }, $book['rows']);
        check($actual === $expected, "paritas $company buku {$book['label']}: baris preview = baris jurnal nyata " . json_encode(array($expected, $actual)));
        check(array_sum(array_column($book['rows'], 'debit')) === array_sum(array_column($book['rows'], 'kredit')), "paritas $company buku {$book['label']}: preview seimbang");
    }
    $kas_rows = array_filter(array_merge(...array_column($preview, 'rows')), function ($r) { return $r['kredit'] > 0 && strpos($r['coa'], '1101-') === 0; });
    check(count($kas_rows) === 1 && array_values($kas_rows)[0]['coa'] === '1101-01-05' && array_values($kas_rows)[0]['nm_coa'] === 'Kas Kecil Pusat', "paritas $company: kas di preview = COA Petty Cash master");
}
check(Expense_petty_cash_model::build_journal_preview('XXX', '2026-11-20', array(array('coa_code' => 'a', 'total' => 5)), array('kode' => 'k', 'nama' => 'n')) === false, 'preview: company tidak valid -> false');
check(Expense_petty_cash_model::build_journal_preview('STM', '2026-11-20', array(), array('kode' => 'k', 'nama' => 'n')) === false, 'preview: tanpa detail -> false');

echo "PASS Journal approve (PC-R-02/04): {$GLOBALS['checks']} pemeriksaan" . PHP_EOL;
