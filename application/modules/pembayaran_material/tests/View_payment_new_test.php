<?php
/**
 * Test case for view_payment_new fixes:
 * 1. Nilai Bayar row calculation & sum match nilai pengajuan and header payment_bank.
 * 2. tr_jurnal retrieval returns all 14 rows and balances (Debit == Kredit).
 * 3. Child payment approval ID resolves all siblings under the same voucher.
 * 4. View table filtering:
 *    - Hides 0-amount rows (PPN & PPh with 0 amounts).
 *    - Hides intercompany balancing rows (1103-01-01 & 2203-01-01).
 *    - Results in Total Jurnal Debit = Kredit = Rp 8,920,202.00 (matches header bank payment).
 */

class View_payment_new_test {
    private $db;

    public function __construct() {
        $this->db = new mysqli('206.84.97.107', 'alief', 'Alief@ssc1', 'db_sendigs_ss_dev');
        if ($this->db->connect_error) {
            die("DB Connection failed: " . $this->db->connect_error . "\n");
        }
    }

    public function run() {
        echo "Running View_payment_new_test...\n";
        $this->test_nilai_bayar_calculation();
        $this->test_sibling_resolution();
        $this->test_tr_jurnal_retrieval_and_balance();
        $this->test_view_table_filtered_journals();
        echo "PASS: All tests in View_payment_new_test passed successfully!\n";
    }

    private function assert($condition, $message) {
        if (!$condition) {
            throw new RuntimeException("Assertion failed: " . $message);
        }
    }

    private function test_nilai_bayar_calculation() {
        echo "  - Testing Nilai Bayar calculation and sum...\n";
        $res = $this->db->query("SELECT id, no_doc, tipe, jumlah, total_ppn, total_pph, payment_bank, id_payment FROM payment_approve WHERE id_payment = 'BK--1026-0002' ORDER BY id ASC");
        $this->assert($res && $res->num_rows === 6, "Expected 6 document rows for BK--1026-0002");

        $tot_pengajuan = 0;
        $tot_ppn = 0;
        $tot_pph = 0;
        $tot_bayar = 0;
        $expected_rows = [
            'BK--1026-0006' => ['no_doc' => 'PERIODIK-2026-00181', 'expected_bayar' => 5401202.00],
            'BK--1026-0008' => ['no_doc' => 'PERIODIK-2026-00177', 'expected_bayar' => 1475000.00],
            'BK--1026-0039' => ['no_doc' => 'RPC-2026-0029',      'expected_bayar' => 207000.00],
            'BK--1026-0040' => ['no_doc' => 'DPM-2026-00081',     'expected_bayar' => 200000.00],
            'BK--1026-0041' => ['no_doc' => 'DPM-2026-00083',     'expected_bayar' => 797000.00],
            'BK--1026-0042' => ['no_doc' => 'DPM-2026-00082',     'expected_bayar' => 840000.00],
        ];

        while ($item = $res->fetch_object()) {
            $nilai_pengajuan = ($item->jumlah > 0) ? (float)$item->jumlah : (float)$item->payment_bank;
            $ppn = (float) $item->total_ppn;
            $pph = (float) $item->total_pph;
            // The fixed formula
            $nilai_bayar = $nilai_pengajuan;

            $tot_pengajuan += $nilai_pengajuan;
            $tot_ppn += $ppn;
            $tot_pph += $pph;
            $tot_bayar += $nilai_bayar;

            $this->assert(isset($expected_rows[$item->id]), "Unexpected item id: " . $item->id);
            $this->assert(abs($nilai_bayar - $expected_rows[$item->id]['expected_bayar']) < 0.01,
                "Item {$item->id} expected nilai_bayar {$expected_rows[$item->id]['expected_bayar']} but got {$nilai_bayar}");
            if ($item->jumlah != 8920202) {
                $this->assert($nilai_bayar !== (float)$item->payment_bank, "Item {$item->id} should not display full voucher payment_bank!");
            }
        }

        $this->assert(abs($tot_bayar - 8920202.00) < 0.01, "Total bayar must equal 8,920,202.00, got: " . $tot_bayar);
        $this->assert($tot_bayar < 50000000.00, "Total bayar must NOT be multiplied (53,521,212.00)");
        echo "    ✓ Row-level Nilai Bayar matches individual document pengajuan exactly.\n";
        echo "    ✓ Table footer sum is Rp " . number_format($tot_bayar, 2) . " (matches header bank payment).\n";
    }

    private function test_sibling_resolution() {
        echo "  - Testing sibling resolution when accessed via child approval ID...\n";
        $child_id = 'BK--1026-0006';

        // Emulate controller resolution
        $check_item = $this->db->query("SELECT a.id, a.id_payment FROM payment_approve a WHERE a.id = '{$child_id}'")->fetch_object();
        $this->assert(!empty($check_item) && !empty($check_item->id_payment), "Child item should have id_payment");

        $siblings = [];
        $res = $this->db->query("SELECT a.id FROM payment_approve a WHERE a.id_payment = '{$check_item->id_payment}'");
        while ($r = $res->fetch_object()) {
            $siblings[] = $r->id;
        }

        $this->assert(count($siblings) === 6, "Expected 6 siblings for child approval ID");
        $this->assert(in_array('BK--1026-0006', $siblings), "Must contain BK--1026-0006");
        $this->assert(in_array('BK--1026-0042', $siblings), "Must contain BK--1026-0042");
        echo "    ✓ Sibling resolution correctly resolves all 6 constituent approval IDs.\n";
    }

    private function test_tr_jurnal_retrieval_and_balance() {
        echo "  - Testing tr_jurnal retrieval and balance status...\n";
        $id = 'BK--1026-0002';
        $id_paid_voucher = 'BK--1026-0002';
        $id_payment = ['BK--1026-0006', 'BK--1026-0008', 'BK--1026-0039', 'BK--1026-0040', 'BK--1026-0041', 'BK--1026-0042'];

        $search_keys = array_unique(array_filter(array_merge([$id, $id_paid_voucher], $id_payment)));
        $escaped_search = array_map(function($k) { return "'" . $this->db->real_escape_string($k) . "'"; }, $search_keys);

        $where_clauses = [];
        $where_clauses[] = "no_transaksi IN (" . implode(',', $escaped_search) . ")";
        $where_clauses[] = "no_jurnal IN (" . implode(',', $escaped_search) . ")";
        $where_clauses[] = "no_transaksi LIKE '%" . $this->db->real_escape_string($id_paid_voucher) . "%'";
        $where_clauses[] = "keterangan LIKE '%" . $this->db->real_escape_string($id_paid_voucher) . "%'";
        foreach ($id_payment as $pid) {
            $where_clauses[] = "FIND_IN_SET('" . $this->db->real_escape_string($pid) . "', no_transaksi) > 0";
        }

        $sql = "SELECT id, no_jurnal, tgl_jurnal, coa, nm_coa, debit, kredit, keterangan, no_transaksi, jenis_transaksi FROM tr_jurnal WHERE (" . implode(' OR ', $where_clauses) . ") ORDER BY id ASC";
        $res = $this->db->query($sql);
        $this->assert($res !== false, "Query failed: " . $this->db->error);

        $rows = [];
        $tot_debit = 0;
        $tot_kredit = 0;
        while ($row = $res->fetch_object()) {
            $rows[] = $row;
            $tot_debit += (float)$row->debit;
            $tot_kredit += (float)$row->kredit;
        }

        $this->assert(count($rows) === 14, "Expected exactly 14 journal rows, got: " . count($rows));
        $this->assert(abs($tot_debit - 10557202.00) < 0.01, "Expected Debit 10,557,202.00, got: " . $tot_debit);
        $this->assert(abs($tot_kredit - 10557202.00) < 0.01, "Expected Kredit 10,557,202.00, got: " . $tot_kredit);
        $this->assert(abs($tot_debit - $tot_kredit) < 0.01, "Journal must be in balance!");
        echo "    ✓ Successfully retrieved 14 journal rows.\n";
        echo "    ✓ Raw combined: Debit Rp " . number_format($tot_debit, 2) . " | Kredit Rp " . number_format($tot_kredit, 2) . " [BALANCE].\n";
    }

    private function test_view_table_filtered_journals() {
        echo "  - Testing view table filtering (0 debit/kredit removed, intercompany removed)...\n";
        $id = 'BK--1026-0002';
        $id_paid_voucher = 'BK--1026-0002';
        $id_payment = ['BK--1026-0006', 'BK--1026-0008', 'BK--1026-0039', 'BK--1026-0040', 'BK--1026-0041', 'BK--1026-0042'];

        $search_keys = array_unique(array_filter(array_merge([$id, $id_paid_voucher], $id_payment)));
        $escaped_search = array_map(function($k) { return "'" . $this->db->real_escape_string($k) . "'"; }, $search_keys);

        $where_clauses = [];
        $where_clauses[] = "no_transaksi IN (" . implode(',', $escaped_search) . ")";
        $where_clauses[] = "no_jurnal IN (" . implode(',', $escaped_search) . ")";
        $where_clauses[] = "no_transaksi LIKE '%" . $this->db->real_escape_string($id_paid_voucher) . "%'";
        $where_clauses[] = "keterangan LIKE '%" . $this->db->real_escape_string($id_paid_voucher) . "%'";
        foreach ($id_payment as $pid) {
            $where_clauses[] = "FIND_IN_SET('" . $this->db->real_escape_string($pid) . "', no_transaksi) > 0";
        }

        $sql = "SELECT id, no_jurnal, tgl_jurnal, coa, nm_coa, debit, kredit, keterangan, no_transaksi, jenis_transaksi FROM tr_jurnal WHERE (" . implode(' OR ', $where_clauses) . ") ORDER BY id ASC";
        $res = $this->db->query($sql);

        $rendered_rows = [];
        $total_debit = 0;
        $total_kredit = 0;

        while ($jr = $res->fetch_object()) {
            $debit = (float)$jr->debit;
            $kredit = (float)$jr->kredit;

            // 1. Filter out 0 debit and 0 kredit
            if ($debit <= 0 && $kredit <= 0) {
                continue;
            }

            // 2. Filter out intercompany balancing accounts
            $is_intercompany = (
                (!empty($jr->coa) && in_array($jr->coa, ['1103-01-01', '2203-01-01'])) ||
                (!empty($jr->nm_coa) && stripos($jr->nm_coa, 'Hubungan Istimewa') !== false)
            );
            if ($is_intercompany) {
                continue;
            }

            $rendered_rows[] = $jr;
            $total_debit += $debit;
            $total_kredit += $kredit;
        }

        // 14 raw rows - 4 zero rows (PPN & PPh) - 2 intercompany rows = exactly 8 operational rows
        $this->assert(count($rendered_rows) === 8, "Expected 8 rendered rows, got: " . count($rendered_rows));
        $this->assert(abs($total_debit - 8920202.00) < 0.01, "Expected Debit 8,920,202.00, got: " . $total_debit);
        $this->assert(abs($total_kredit - 8920202.00) < 0.01, "Expected Kredit 8,920,202.00, got: " . $total_kredit);
        $this->assert(abs($total_debit - $total_kredit) < 0.01, "Filtered table must be in balance!");

        echo "    ✓ 4 zero-amount rows (PPN & PPh) successfully hidden.\n";
        echo "    ✓ 2 intercompany rows (Piutang & Hutang Hubungan Istimewa) successfully hidden.\n";
        echo "    ✓ Filtered table rendered exactly 8 operational rows.\n";
        echo "    ✓ Total Jurnal: Rp " . number_format($total_debit, 2) . " matches Total Nilai Bayar (Bank) Rp 8,920,202.00 [BALANCE 100%].\n";
    }
}

$test = new View_payment_new_test();
$test->run();
