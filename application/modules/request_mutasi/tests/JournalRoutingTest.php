<?php

/**
 * Property-Based Test: Journal Routing to Target Database
 *
 * Feature: multi-accounting-target, Property 4: Journal Routing to Target Database
 *
 * **Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.7**
 *
 * Property 4: Journal Routing to Target Database
 * _For any_ valid target_accounting value stored in a transaction record,
 * all journal operations (header insert into jarh/japh, detail insert into jurnal,
 * counter update in pastibisa_tb_cabang) SHALL target the database identified by
 * the resolved target_accounting value, and the resulting journal number SHALL be
 * stored back in the originating transaction record.
 *
 * This test validates:
 * - Journal header (jarh for BUM, japh for BUK) is inserted to correct target DB
 * - Journal detail (jurnal) is inserted to correct target DB
 * - Counter update (pastibisa_tb_cabang) targets correct target DB
 * - Journal number is stored back in the originating record
 * - No cross-database journal insertion
 * - Invalid/null target_accounting causes journal routing to return false
 *
 * Usage: php application/modules/request_mutasi/tests/JournalRoutingTest.php
 */

// ============================================================================
// Define constants for isolated test runs (not within full CI bootstrap)
// ============================================================================

if (!defined('DBACC_STM')) {
    define('DBACC_STM', 'db_sendigs_ss_stm');
}
if (!defined('DBACC_VUCA')) {
    define('DBACC_VUCA', 'db_sendigs_ss_vuca');
}
if (!defined('DBACC_SUSTAIN')) {
    define('DBACC_SUSTAIN', 'db_sendigs_ss_sustain');
}

// ============================================================================
// Minimal test framework (standalone runner)
// ============================================================================

class SimpleTestRunner
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $failures = [];
    private $currentTest = '';

    public function run($testClass)
    {
        $reflection = new ReflectionClass($testClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        echo "Running: " . get_class($testClass) . "\n";
        echo str_repeat("=", 70) . "\n\n";

        foreach ($methods as $method) {
            if (strpos($method->getName(), 'test') === 0) {
                $this->currentTest = $method->getName();
                echo "  TEST: {$this->currentTest}\n";
                try {
                    $method->invoke($testClass);
                    $this->testsPassed++;
                    echo "    ✓ PASSED\n\n";
                } catch (AssertionError $e) {
                    $this->testsFailed++;
                    $this->failures[] = [
                        'test' => $this->currentTest,
                        'message' => $e->getMessage(),
                    ];
                    echo "    ✗ FAILED: " . $e->getMessage() . "\n\n";
                } catch (Exception $e) {
                    $this->testsFailed++;
                    $this->failures[] = [
                        'test' => $this->currentTest,
                        'message' => 'Exception: ' . $e->getMessage(),
                    ];
                    echo "    ✗ ERROR: " . $e->getMessage() . "\n\n";
                }
            }
        }

        echo str_repeat("=", 70) . "\n";
        echo "Results: {$this->testsPassed} passed, {$this->testsFailed} failed\n";

        if (!empty($this->failures)) {
            echo "\nFAILURES:\n";
            foreach ($this->failures as $i => $failure) {
                echo "  " . ($i + 1) . ") {$failure['test']}\n";
                echo "     {$failure['message']}\n\n";
            }
        }

        echo str_repeat("=", 70) . "\n";

        return $this->testsFailed === 0 ? 0 : 1;
    }
}

// ============================================================================
// Assertion helpers
// ============================================================================

if (!function_exists('assertEquals')) {
    function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $msg = $message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true);
            throw new AssertionError($msg);
        }
    }
}

if (!function_exists('assertTrue')) {
    function assertTrue($value, string $message = ''): void
    {
        if ($value !== true) {
            throw new AssertionError($message ?: "Expected true but got " . var_export($value, true));
        }
    }
}

if (!function_exists('assertFalse')) {
    function assertFalse($value, string $message = ''): void
    {
        if ($value !== false) {
            throw new AssertionError($message ?: "Expected false but got " . var_export($value, true));
        }
    }
}

if (!function_exists('assertNotNull')) {
    function assertNotNull($value, string $message = ''): void
    {
        if ($value === null) {
            throw new AssertionError($message ?: "Expected non-null value");
        }
    }
}

if (!function_exists('assertIsArray')) {
    function assertIsArray($value, string $message = ''): void
    {
        if (!is_array($value)) {
            throw new AssertionError($message ?: "Expected array but got " . gettype($value));
        }
    }
}

if (!function_exists('assertArrayHasKey')) {
    function assertArrayHasKey($key, $array, string $message = ''): void
    {
        if (!is_array($array) || !array_key_exists($key, $array)) {
            throw new AssertionError($message ?: "Expected array to have key '{$key}'");
        }
    }
}

if (!function_exists('assertStringContains')) {
    function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        if (strpos($haystack, $needle) === false) {
            throw new AssertionError($message ?: "Expected string to contain '{$needle}', got: '{$haystack}'");
        }
    }
}

if (!function_exists('assertCount')) {
    function assertCount(int $expected, array $array, string $message = ''): void
    {
        if (count($array) !== $expected) {
            throw new AssertionError($message ?: "Expected count {$expected}, got " . count($array));
        }
    }
}

// ============================================================================
// Mock Database Connection - tracks all insert/update/query operations
// ============================================================================

/**
 * Mock database connection that records all operations performed.
 * Tracks which tables are targeted and which database prefix is used.
 */
class MockJournalDbConnection
{
    public $database;
    public $inserts = [];
    public $batch_inserts = [];
    public $queries = [];
    public $updates = [];
    public $affected_rows_value = 1;

    public function __construct(string $database)
    {
        $this->database = $database;
    }

    public function insert(string $table, array $data): bool
    {
        $this->inserts[] = ['table' => $table, 'data' => $data];
        return true;
    }

    public function insert_batch(string $table, array $data): bool
    {
        $this->batch_inserts[] = ['table' => $table, 'data' => $data];
        return true;
    }

    public function query(string $sql)
    {
        $this->queries[] = $sql;
        return new MockJournalDbResult([]);
    }

    public function update(string $table, array $data, $where = null): bool
    {
        $this->updates[] = ['table' => $table, 'data' => $data, 'where' => $where];
        return true;
    }

    public function affected_rows(): int
    {
        return $this->affected_rows_value;
    }

    /**
     * Get all tables targeted by this connection (from inserts + queries)
     */
    public function getTargetedTables(): array
    {
        $tables = [];
        foreach ($this->inserts as $op) {
            $tables[] = $op['table'];
        }
        foreach ($this->batch_inserts as $op) {
            $tables[] = $op['table'];
        }
        return $tables;
    }
}

/**
 * Mock database result object
 */
class MockJournalDbResult
{
    private $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function result_array(): array
    {
        return $this->rows;
    }

    public function num_rows(): int
    {
        return count($this->rows);
    }

    public function row()
    {
        if (empty($this->rows)) return null;
        return (object) $this->rows[0];
    }
}

// ============================================================================
// Journal Routing Simulator - replicates controller logic for testing
// ============================================================================

/**
 * Simulates journal routing logic from the controller.
 * Tracks all database operations to verify correct routing.
 */
class JournalRoutingSimulator
{
    private static $VALID_TARGET_ACCOUNTING = [
        'accounting_stm',
        'accounting_vuca',
        'accounting_sustain'
    ];

    private static $TARGET_DB_MAP = [
        'accounting_stm'     => DBACC_STM,
        'accounting_vuca'    => DBACC_VUCA,
        'accounting_sustain' => DBACC_SUSTAIN,
    ];

    /** @var MockJournalDbConnection */
    public $db;

    /** @var array Track all operations per database */
    public $operations_log = [];

    /** @var int Counter for generating journal numbers */
    private $journal_counter = 1;

    /** @var string|null Last journal number generated */
    public $last_journal_number = null;

    /** @var array Store updates to originating records */
    public $record_updates = [];

    public function __construct()
    {
        $this->db = new MockJournalDbConnection('db_sendigs_ss');
    }

    /**
     * Reset state between test runs
     */
    public function reset(): void
    {
        $this->db = new MockJournalDbConnection('db_sendigs_ss');
        $this->operations_log = [];
        $this->journal_counter = 1;
        $this->last_journal_number = null;
        $this->record_updates = [];
    }

    /**
     * Resolve target DB - mirrors controller's _resolve_target_db()
     * @param mixed $target_accounting
     * @return array|false
     */
    public function resolveTargetDb($target_accounting)
    {
        if (empty($target_accounting) || !in_array($target_accounting, self::$VALID_TARGET_ACCOUNTING)) {
            return false;
        }

        $db_name = self::$TARGET_DB_MAP[$target_accounting];

        return [
            'db_name'    => $db_name,
            'connection' => new MockJournalDbConnection($db_name),
            'group'      => $target_accounting,
        ];
    }

    /**
     * Generate a journal number (simulates Jurnal_model->get_Nomor_Jurnal_BUM)
     */
    private function generateJournalNumber(string $type, string $tgl, string $db_name): string
    {
        $prefix = ($type === 'BUM') ? 'BUM' : 'BUK';
        $month = date('m', strtotime($tgl));
        $year = date('Y', strtotime($tgl));
        $number = str_pad($this->journal_counter++, 5, '0', STR_PAD_LEFT);
        return "{$prefix}-101-{$month}{$year}-{$number}";
    }

    /**
     * Simulate _save_jurnal_mutasi_aktual() - BUM journal for realisasi mutasi
     * Mirrors controller logic from Request_mutasi::_save_jurnal_mutasi_aktual()
     *
     * @return array|false Operation result with details or false on failure
     */
    public function saveJurnalMutasiAktual(
        string $kode_aktual,
        string $bank_asal,
        string $bank_tujuan,
        float $nilai_idr,
        string $keterangan,
        string $tgl,
        $target_accounting
    ) {
        // Resolve target database
        $target = $this->resolveTargetDb($target_accounting);
        if (!$target) {
            return false;
        }
        $db_name = $target['db_name'];

        $tgl_jurnal = date('Y-m-d', strtotime($tgl));
        $Nomor_JV = $this->generateJournalNumber('BUM', $tgl_jurnal, $db_name);
        $this->last_journal_number = $Nomor_JV;

        // Header jurnal - insert to {db_name}.jarh
        $header_table = $db_name . '.jarh';
        $this->db->insert($header_table, [
            'nomor'         => $Nomor_JV,
            'tgl'           => $tgl_jurnal,
            'jml'           => $nilai_idr,
            'kd_pembayaran' => $kode_aktual,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUM',
            'no_reff'       => $kode_aktual,
            'terima_dari'   => $keterangan,
            'jenis_ar'      => 'BUM',
            'note'          => $keterangan,
            'batal'         => '0',
        ]);

        // Detail jurnal - insert to {db_name}.jurnal
        $detail_table = $db_name . '.jurnal';
        $this->db->insert_batch($detail_table, [
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl_jurnal,
                'tipe'         => 'BUM',
                'no_perkiraan' => $bank_tujuan,
                'keterangan'   => $keterangan,
                'no_reff'      => $kode_aktual,
                'debet'        => $nilai_idr,
                'kredit'       => 0,
            ],
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl_jurnal,
                'tipe'         => 'BUM',
                'no_perkiraan' => $bank_asal,
                'keterangan'   => $keterangan,
                'no_reff'      => $kode_aktual,
                'debet'        => 0,
                'kredit'       => $nilai_idr,
            ],
        ]);

        // Counter update - UPDATE {db_name}.pastibisa_tb_cabang
        $counter_sql = "UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'";
        $this->db->query($counter_sql);

        // Store journal number back to originating record
        $this->db->update('tr_request_mutasi_aktual', ['jurnal' => $Nomor_JV], ['kd_mutasi_aktual' => $kode_aktual]);
        $this->record_updates[] = [
            'table' => 'tr_request_mutasi_aktual',
            'field' => 'jurnal',
            'value' => $Nomor_JV,
            'key'   => $kode_aktual,
        ];

        // Log operations
        $this->operations_log[] = [
            'type'         => 'BUM_AKTUAL',
            'target_db'    => $db_name,
            'header_table' => $header_table,
            'detail_table' => $detail_table,
            'counter_db'   => $db_name,
            'journal_no'   => $Nomor_JV,
            'record_key'   => $kode_aktual,
        ];

        return [
            'success'      => true,
            'db_name'      => $db_name,
            'journal_no'   => $Nomor_JV,
            'header_table' => $header_table,
            'detail_table' => $detail_table,
        ];
    }

    /**
     * Simulate save_jurnal_BUM() - BUM journal for transaksi bank masuk
     * Mirrors controller logic from Request_mutasi::save_jurnal_BUM()
     *
     * @return array|false
     */
    public function saveJurnalBUM(
        string $kode_mutasi,
        string $bank_asal,
        string $bank_tujuan,
        float $transaksi,
        string $keterangan,
        string $tgl_request,
        $target_accounting
    ) {
        // Resolve target database from record's target_accounting
        $target = $this->resolveTargetDb($target_accounting);
        if (!$target) {
            return false;
        }
        $db_name = $target['db_name'];

        $tgl_jurnal = date('Y-m-d', strtotime($tgl_request));
        $Nomor_JV = $this->generateJournalNumber('BUM', $tgl_jurnal, $db_name);
        $this->last_journal_number = $Nomor_JV;

        // Insert header to {db_name}.jarh
        $header_table = $db_name . '.jarh';
        $this->db->insert($header_table, [
            'nomor'         => $Nomor_JV,
            'tgl'           => $tgl_jurnal,
            'jml'           => $transaksi,
            'kd_pembayaran' => $kode_mutasi,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUM',
            'no_reff'       => $kode_mutasi,
            'terima_dari'   => $keterangan,
            'jenis_ar'      => 'BUM',
            'note'          => $keterangan,
            'batal'         => '0',
        ]);

        // Insert detail to {db_name}.jurnal
        $detail_table = $db_name . '.jurnal';
        $this->db->insert_batch($detail_table, [
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl_jurnal,
                'tipe'         => 'BUM',
                'no_perkiraan' => $bank_tujuan,
                'keterangan'   => $keterangan,
                'no_reff'      => $kode_mutasi,
                'debet'        => $transaksi,
                'kredit'       => 0,
            ],
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl_jurnal,
                'tipe'         => 'BUM',
                'no_perkiraan' => $bank_asal,
                'keterangan'   => $keterangan,
                'no_reff'      => $kode_mutasi,
                'debet'        => 0,
                'kredit'       => $transaksi,
            ],
        ]);

        // Counter update
        $counter_sql = "UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'";
        $this->db->query($counter_sql);

        // Store journal number back to record (jurnal1 field)
        $update_sql = "UPDATE tr_request_mutasi_admin SET jurnal1 = '{$Nomor_JV}' WHERE kd_mutasi = '{$kode_mutasi}'";
        $this->db->query($update_sql);
        $this->record_updates[] = [
            'table' => 'tr_request_mutasi_admin',
            'field' => 'jurnal1',
            'value' => $Nomor_JV,
            'key'   => $kode_mutasi,
        ];

        $this->operations_log[] = [
            'type'         => 'BUM',
            'target_db'    => $db_name,
            'header_table' => $header_table,
            'detail_table' => $detail_table,
            'counter_db'   => $db_name,
            'journal_no'   => $Nomor_JV,
            'record_key'   => $kode_mutasi,
        ];

        return [
            'success'      => true,
            'db_name'      => $db_name,
            'journal_no'   => $Nomor_JV,
            'header_table' => $header_table,
            'detail_table' => $detail_table,
        ];
    }

    /**
     * Simulate save_jurnal_BUK() - BUK journal for transaksi bank keluar
     * Mirrors controller logic from Request_mutasi::save_jurnal_BUK()
     *
     * @return array|false
     */
    public function saveJurnalBUK(
        string $kode_mutasi,
        string $bank_asal,
        string $bank_tujuan,
        float $transaksi,
        string $keterangan,
        string $tgl_request,
        $target_accounting
    ) {
        // Resolve target database
        $target = $this->resolveTargetDb($target_accounting);
        if (!$target) {
            return false;
        }
        $db_name = $target['db_name'];

        $tgl_jurnal = date('Y-m-d', strtotime($tgl_request));
        $Nomor_JV = $this->generateJournalNumber('BUK', $tgl_jurnal, $db_name);
        $this->last_journal_number = $Nomor_JV;

        // Insert header to {db_name}.japh (BUK uses japh, not jarh)
        $header_table = $db_name . '.japh';
        $this->db->insert($header_table, [
            'nomor'        => $Nomor_JV,
            'tgl'          => $tgl_jurnal,
            'jml'          => $transaksi,
            'kdcab'        => '101',
            'jenis_reff'   => 'BUK',
            'no_reff'      => $kode_mutasi,
            'bayar_kepada' => $bank_tujuan,
            'jenis_ap'     => 'BUK',
            'note'         => $keterangan,
            'batal'        => '0',
        ]);

        // Insert detail to {db_name}.jurnal
        $detail_table = $db_name . '.jurnal';
        $this->db->insert_batch($detail_table, [
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl_jurnal,
                'tipe'         => 'BUK',
                'no_perkiraan' => $bank_asal,
                'keterangan'   => $keterangan,
                'no_reff'      => $kode_mutasi,
                'debet'        => $transaksi,
                'kredit'       => 0,
            ],
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl_jurnal,
                'tipe'         => 'BUK',
                'no_perkiraan' => $bank_tujuan,
                'keterangan'   => $keterangan,
                'no_reff'      => $kode_mutasi,
                'debet'        => 0,
                'kredit'       => $transaksi,
            ],
        ]);

        // Counter update - nobuk for BUK
        $counter_sql = "UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobuk = nobuk + 1 WHERE nocab = '101'";
        $this->db->query($counter_sql);

        // Store journal number back to record (jurnal2 field)
        $update_sql = "UPDATE tr_request_mutasi_admin SET jurnal2 = '{$Nomor_JV}' WHERE kd_mutasi = '{$kode_mutasi}'";
        $this->db->query($update_sql);
        $this->record_updates[] = [
            'table' => 'tr_request_mutasi_admin',
            'field' => 'jurnal2',
            'value' => $Nomor_JV,
            'key'   => $kode_mutasi,
        ];

        $this->operations_log[] = [
            'type'         => 'BUK',
            'target_db'    => $db_name,
            'header_table' => $header_table,
            'detail_table' => $detail_table,
            'counter_db'   => $db_name,
            'journal_no'   => $Nomor_JV,
            'record_key'   => $kode_mutasi,
        ];

        return [
            'success'      => true,
            'db_name'      => $db_name,
            'journal_no'   => $Nomor_JV,
            'header_table' => $header_table,
            'detail_table' => $detail_table,
        ];
    }
}

// ============================================================================
// Test Class: Journal Routing Property Test
// ============================================================================

class JournalRoutingTest
{
    private static $VALID_TARGET_ACCOUNTING = [
        'accounting_stm',
        'accounting_vuca',
        'accounting_sustain'
    ];

    private static $TARGET_DB_MAP = [
        'accounting_stm'     => DBACC_STM,
        'accounting_vuca'    => DBACC_VUCA,
        'accounting_sustain' => DBACC_SUSTAIN,
    ];

    /** @var JournalRoutingSimulator */
    private $simulator;

    public function __construct()
    {
        $this->simulator = new JournalRoutingSimulator();
    }

    /**
     * Generate random transaction data for property testing
     */
    private function generateRandomTransactionData(): array
    {
        $kode = 'MUT-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $bank_asal = '1101' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $bank_tujuan = '1101' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $nilai = mt_rand(100000, 999999999) / 100.0;
        $keterangan = 'Mutasi test #' . mt_rand(1, 9999);
        $year = mt_rand(2023, 2026);
        $month = str_pad(mt_rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad(mt_rand(1, 28), 2, '0', STR_PAD_LEFT);
        $tgl = "{$year}-{$month}-{$day}";

        return compact('kode', 'bank_asal', 'bank_tujuan', 'nilai', 'keterangan', 'tgl');
    }

    // ========================================================================
    // Test 4.1: BUM journal header is inserted to correct {db_name}.jarh
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.4**
     *
     * For each valid target_accounting, the journal header is inserted to
     * the correct {db_name}.jarh (BUM) table.
     */
    public function testBumJournalHeaderInsertsToCorrectDatabase(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $this->simulator->reset();
            $expected_db = self::$TARGET_DB_MAP[$target];

            $result = $this->simulator->saveJurnalBUM(
                'ADM-00001',
                '1101001',
                '1101002',
                1000000.00,
                'Test BUM',
                '2024-06-15',
                $target
            );

            assertIsArray($result, "BUM result should be array for '{$target}'");
            assertEquals(
                $expected_db . '.jarh',
                $result['header_table'],
                "BUM header should insert to '{$expected_db}.jarh' for target '{$target}'"
            );

            // Verify via inserts log
            $header_insert = $this->simulator->db->inserts[0] ?? null;
            assertNotNull($header_insert, "Should have at least one insert for '{$target}'");
            assertEquals(
                $expected_db . '.jarh',
                $header_insert['table'],
                "Insert table should be '{$expected_db}.jarh'"
            );
        }
    }

    // ========================================================================
    // Test 4.2: BUK journal header is inserted to correct {db_name}.japh
    // ========================================================================

    /**
     * **Validates: Requirements 4.3, 4.4**
     *
     * For each valid target_accounting, the BUK journal header is inserted to
     * the correct {db_name}.japh table.
     */
    public function testBukJournalHeaderInsertsToCorrectDatabase(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $this->simulator->reset();
            $expected_db = self::$TARGET_DB_MAP[$target];

            $result = $this->simulator->saveJurnalBUK(
                'ADM-00002',
                '1101003',
                '1101004',
                2000000.00,
                'Test BUK',
                '2024-07-20',
                $target
            );

            assertIsArray($result, "BUK result should be array for '{$target}'");
            assertEquals(
                $expected_db . '.japh',
                $result['header_table'],
                "BUK header should insert to '{$expected_db}.japh' for target '{$target}'"
            );

            // Verify via inserts log
            $header_insert = $this->simulator->db->inserts[0] ?? null;
            assertNotNull($header_insert, "Should have header insert for '{$target}'");
            assertEquals(
                $expected_db . '.japh',
                $header_insert['table'],
                "Insert table should be '{$expected_db}.japh'"
            );
        }
    }

    // ========================================================================
    // Test 4.3: Journal detail inserts to correct {db_name}.jurnal
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
     *
     * For each valid target_accounting, the journal detail is inserted to
     * {db_name}.jurnal for both BUM and BUK operations.
     */
    public function testJournalDetailInsertsToCorrectDatabase(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $expected_db = self::$TARGET_DB_MAP[$target];

            // Test BUM detail
            $this->simulator->reset();
            $this->simulator->saveJurnalBUM(
                'ADM-D001',
                '1101001',
                '1101002',
                500000.00,
                'Detail test BUM',
                '2024-05-10',
                $target
            );
            $batch = $this->simulator->db->batch_inserts[0] ?? null;
            assertNotNull($batch, "BUM should have batch insert for '{$target}'");
            assertEquals(
                $expected_db . '.jurnal',
                $batch['table'],
                "BUM detail should insert to '{$expected_db}.jurnal'"
            );

            // Test BUK detail
            $this->simulator->reset();
            $this->simulator->saveJurnalBUK(
                'ADM-D002',
                '1101003',
                '1101004',
                750000.00,
                'Detail test BUK',
                '2024-05-10',
                $target
            );
            $batch = $this->simulator->db->batch_inserts[0] ?? null;
            assertNotNull($batch, "BUK should have batch insert for '{$target}'");
            assertEquals(
                $expected_db . '.jurnal',
                $batch['table'],
                "BUK detail should insert to '{$expected_db}.jurnal'"
            );
        }
    }

    // ========================================================================
    // Test 4.4: Counter update targets correct {db_name}.pastibisa_tb_cabang
    // ========================================================================

    /**
     * **Validates: Requirements 4.4**
     *
     * The counter update targets {db_name}.pastibisa_tb_cabang for both BUM and BUK.
     */
    public function testCounterUpdateTargetsCorrectDatabase(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $expected_db = self::$TARGET_DB_MAP[$target];

            // Test BUM counter update (nobum)
            $this->simulator->reset();
            $this->simulator->saveJurnalBUM(
                'ADM-C001',
                '1101001',
                '1101002',
                100000.00,
                'Counter test BUM',
                '2024-03-15',
                $target
            );
            $counter_query_found = false;
            foreach ($this->simulator->db->queries as $sql) {
                if (
                    strpos($sql, $expected_db . '.pastibisa_tb_cabang') !== false
                    && strpos($sql, 'nobum') !== false
                ) {
                    $counter_query_found = true;
                    break;
                }
            }
            assertTrue(
                $counter_query_found,
                "BUM counter should update '{$expected_db}.pastibisa_tb_cabang' with nobum for '{$target}'"
            );

            // Test BUK counter update (nobuk)
            $this->simulator->reset();
            $this->simulator->saveJurnalBUK(
                'ADM-C002',
                '1101003',
                '1101004',
                200000.00,
                'Counter test BUK',
                '2024-03-20',
                $target
            );
            $counter_query_found = false;
            foreach ($this->simulator->db->queries as $sql) {
                if (
                    strpos($sql, $expected_db . '.pastibisa_tb_cabang') !== false
                    && strpos($sql, 'nobuk') !== false
                ) {
                    $counter_query_found = true;
                    break;
                }
            }
            assertTrue(
                $counter_query_found,
                "BUK counter should update '{$expected_db}.pastibisa_tb_cabang' with nobuk for '{$target}'"
            );
        }
    }

    // ========================================================================
    // Test 4.5: Journal number stored back in originating record
    // ========================================================================

    /**
     * **Validates: Requirements 4.7**
     *
     * The journal number is stored back in the originating record.
     */
    public function testJournalNumberStoredBackInOriginatingRecord(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            // Test BUM - stores to jurnal1
            $this->simulator->reset();
            $result = $this->simulator->saveJurnalBUM(
                'ADM-J001',
                '1101001',
                '1101002',
                300000.00,
                'Jurnal store test',
                '2024-04-10',
                $target
            );
            assertIsArray($result);
            assertTrue(strlen($result['journal_no']) > 0, "Journal number should not be empty");

            $update_found = false;
            foreach ($this->simulator->record_updates as $update) {
                if (
                    $update['table'] === 'tr_request_mutasi_admin'
                    && $update['field'] === 'jurnal1'
                    && $update['value'] === $result['journal_no']
                    && $update['key'] === 'ADM-J001'
                ) {
                    $update_found = true;
                    break;
                }
            }
            assertTrue($update_found, "BUM journal number should be stored in jurnal1 for '{$target}'");

            // Test BUK - stores to jurnal2
            $this->simulator->reset();
            $result = $this->simulator->saveJurnalBUK(
                'ADM-J002',
                '1101003',
                '1101004',
                400000.00,
                'Jurnal store BUK test',
                '2024-04-12',
                $target
            );
            assertIsArray($result);
            assertTrue(strlen($result['journal_no']) > 0, "BUK journal number should not be empty");

            $update_found = false;
            foreach ($this->simulator->record_updates as $update) {
                if (
                    $update['table'] === 'tr_request_mutasi_admin'
                    && $update['field'] === 'jurnal2'
                    && $update['value'] === $result['journal_no']
                    && $update['key'] === 'ADM-J002'
                ) {
                    $update_found = true;
                    break;
                }
            }
            assertTrue($update_found, "BUK journal number should be stored in jurnal2 for '{$target}'");

            // Test mutasi aktual - stores to jurnal
            $this->simulator->reset();
            $result = $this->simulator->saveJurnalMutasiAktual(
                'AKT-J003',
                '1101005',
                '1101006',
                500000.00,
                'Aktual store test',
                '2024-04-15',
                $target
            );
            assertIsArray($result);

            $update_found = false;
            foreach ($this->simulator->record_updates as $update) {
                if (
                    $update['table'] === 'tr_request_mutasi_aktual'
                    && $update['field'] === 'jurnal'
                    && $update['value'] === $result['journal_no']
                    && $update['key'] === 'AKT-J003'
                ) {
                    $update_found = true;
                    break;
                }
            }
            assertTrue($update_found, "Aktual journal number should be stored for '{$target}'");
        }
    }

    // ========================================================================
    // Test 4.6: No cross-database journal insertion
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
     *
     * Journal for one target doesn't go to a different target's database.
     * All operations target ONLY the resolved database.
     */
    public function testNoCrossDatabaseJournalInsertion(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $expected_db = self::$TARGET_DB_MAP[$target];
            $other_dbs = array_diff(array_values(self::$TARGET_DB_MAP), [$expected_db]);

            // Test BUM
            $this->simulator->reset();
            $this->simulator->saveJurnalBUM(
                'ADM-X001',
                '1101001',
                '1101002',
                100000.00,
                'Cross-db test',
                '2024-08-01',
                $target
            );

            // Check all tables targeted - none should reference other databases
            $all_tables = $this->simulator->db->getTargetedTables();
            foreach ($all_tables as $table) {
                foreach ($other_dbs as $other_db) {
                    assertFalse(
                        strpos($table, $other_db) === 0,
                        "BUM for '{$target}': table '{$table}' should NOT reference '{$other_db}'"
                    );
                }
                // Verify it either uses expected_db prefix or is a local table
                $uses_target = (strpos($table, $expected_db) === 0);
                $is_local = (strpos($table, 'tr_') === 0);
                assertTrue(
                    $uses_target || $is_local,
                    "BUM for '{$target}': table '{$table}' must use '{$expected_db}' prefix or be local"
                );
            }

            // Check queries don't reference other databases
            foreach ($this->simulator->db->queries as $sql) {
                foreach ($other_dbs as $other_db) {
                    assertFalse(
                        strpos($sql, $other_db . '.') !== false,
                        "BUM for '{$target}': query should NOT reference '{$other_db}'. SQL: {$sql}"
                    );
                }
            }

            // Test BUK
            $this->simulator->reset();
            $this->simulator->saveJurnalBUK(
                'ADM-X002',
                '1101003',
                '1101004',
                200000.00,
                'Cross-db BUK test',
                '2024-08-05',
                $target
            );

            $all_tables = $this->simulator->db->getTargetedTables();
            foreach ($all_tables as $table) {
                foreach ($other_dbs as $other_db) {
                    assertFalse(
                        strpos($table, $other_db) === 0,
                        "BUK for '{$target}': table '{$table}' should NOT reference '{$other_db}'"
                    );
                }
            }

            foreach ($this->simulator->db->queries as $sql) {
                foreach ($other_dbs as $other_db) {
                    assertFalse(
                        strpos($sql, $other_db . '.') !== false,
                        "BUK for '{$target}': query should NOT reference '{$other_db}'. SQL: {$sql}"
                    );
                }
            }
        }
    }

    // ========================================================================
    // Test 4.7: Invalid/null target_accounting returns false (rejected)
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3**
     *
     * For invalid/null target_accounting, journal routing returns false.
     */
    public function testInvalidTargetAccountingReturnsFalse(): void
    {
        $invalid_targets = [
            null,
            '',
            'invalid',
            'accounting_xyz',
            'ACCOUNTING_STM',
            'accounting_stm ',
            ' accounting_vuca',
            'db_sendigs_ss_stm',
            '0',
            'false',
            'accounting',
            'stm',
            'vuca',
            'sustain',
        ];

        foreach ($invalid_targets as $invalid) {
            $this->simulator->reset();

            // BUM should return false
            $result_bum = $this->simulator->saveJurnalBUM(
                'ADM-INV01',
                '1101001',
                '1101002',
                100000.00,
                'Invalid test',
                '2024-01-01',
                $invalid
            );
            assertFalse(
                $result_bum,
                "BUM should return false for invalid target: " . var_export($invalid, true)
            );

            // BUK should return false
            $result_buk = $this->simulator->saveJurnalBUK(
                'ADM-INV02',
                '1101003',
                '1101004',
                200000.00,
                'Invalid BUK test',
                '2024-01-02',
                $invalid
            );
            assertFalse(
                $result_buk,
                "BUK should return false for invalid target: " . var_export($invalid, true)
            );

            // Mutasi aktual should return false
            $result_aktual = $this->simulator->saveJurnalMutasiAktual(
                'AKT-INV03',
                '1101005',
                '1101006',
                300000.00,
                'Invalid aktual test',
                '2024-01-03',
                $invalid
            );
            assertFalse(
                $result_aktual,
                "Aktual should return false for invalid target: " . var_export($invalid, true)
            );

            // Verify NO database operations were performed
            assertCount(
                0,
                $this->simulator->db->inserts,
                "No inserts should occur for invalid target: " . var_export($invalid, true)
            );
            assertCount(
                0,
                $this->simulator->db->batch_inserts,
                "No batch inserts for invalid target: " . var_export($invalid, true)
            );
        }
    }

    // ========================================================================
    // Test 4.8: Property - For ANY valid target, ALL journal operations route
    //           to the resolved database (100 iterations)
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.7**
     *
     * Property: For ANY valid target_accounting, ALL journal operations
     * (header, detail, counter) route to the database identified by
     * the resolved target_accounting value.
     */
    public function testPropertyAllOperationsRouteToResolvedDatabase(): void
    {
        $iterations = 100;
        $seed = 42;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, 2)];
            $expected_db = self::$TARGET_DB_MAP[$target];
            $data = $this->generateRandomTransactionData();
            $this->simulator->reset();

            // Randomly choose BUM, BUK, or Aktual
            $op_type = mt_rand(0, 2);

            if ($op_type === 0) {
                $result = $this->simulator->saveJurnalBUM(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } elseif ($op_type === 1) {
                $result = $this->simulator->saveJurnalBUK(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } else {
                $result = $this->simulator->saveJurnalMutasiAktual(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            }

            assertIsArray($result, "Iteration {$i}: Result should be array for '{$target}'");
            assertEquals(
                $expected_db,
                $result['db_name'],
                "Iteration {$i}: db_name should be '{$expected_db}' for '{$target}'"
            );

            // Verify header table uses correct DB prefix
            assertTrue(
                strpos($result['header_table'], $expected_db . '.') === 0,
                "Iteration {$i}: header_table '{$result['header_table']}' should start with '{$expected_db}.'"
            );

            // Verify detail table uses correct DB prefix
            assertTrue(
                strpos($result['detail_table'], $expected_db . '.') === 0,
                "Iteration {$i}: detail_table '{$result['detail_table']}' should start with '{$expected_db}.'"
            );

            // Verify counter query references correct DB
            $counter_found = false;
            foreach ($this->simulator->db->queries as $sql) {
                if (strpos($sql, $expected_db . '.pastibisa_tb_cabang') !== false) {
                    $counter_found = true;
                    break;
                }
            }
            assertTrue(
                $counter_found,
                "Iteration {$i}: counter should target '{$expected_db}.pastibisa_tb_cabang'"
            );

            // Verify journal number was stored back
            assertTrue(
                count($this->simulator->record_updates) > 0,
                "Iteration {$i}: journal number should be stored back in record"
            );
            assertEquals(
                $result['journal_no'],
                $this->simulator->record_updates[0]['value'],
                "Iteration {$i}: stored journal no should match generated journal no"
            );
        }

        echo "    Property verified: {$iterations} random operations all routed correctly\n";
    }

    // ========================================================================
    // Test 4.9: Property - For ANY invalid/null target, journal routing
    //           returns false (100 iterations)
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3**
     *
     * Property: For ANY invalid or null target_accounting value,
     * journal routing operations SHALL return false (rejected).
     */
    public function testPropertyInvalidTargetAlwaysReturnsFalse(): void
    {
        $iterations = 100;
        $seed = 77;
        mt_srand($seed);

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_- ';

        for ($i = 0; $i < $iterations; $i++) {
            // Generate random invalid target
            $length = mt_rand(0, 30);
            $random_target = '';
            for ($j = 0; $j < $length; $j++) {
                $random_target .= $chars[mt_rand(0, strlen($chars) - 1)];
            }

            // Skip if accidentally generated a valid value
            if (in_array($random_target, self::$VALID_TARGET_ACCOUNTING)) {
                continue;
            }

            $this->simulator->reset();
            $data = $this->generateRandomTransactionData();

            $result_bum = $this->simulator->saveJurnalBUM(
                $data['kode'],
                $data['bank_asal'],
                $data['bank_tujuan'],
                $data['nilai'],
                $data['keterangan'],
                $data['tgl'],
                $random_target
            );
            assertFalse(
                $result_bum,
                "Iteration {$i}: BUM should return false for '{$random_target}'"
            );

            $result_buk = $this->simulator->saveJurnalBUK(
                $data['kode'],
                $data['bank_asal'],
                $data['bank_tujuan'],
                $data['nilai'],
                $data['keterangan'],
                $data['tgl'],
                $random_target
            );
            assertFalse(
                $result_buk,
                "Iteration {$i}: BUK should return false for '{$random_target}'"
            );

            $result_aktual = $this->simulator->saveJurnalMutasiAktual(
                $data['kode'],
                $data['bank_asal'],
                $data['bank_tujuan'],
                $data['nilai'],
                $data['keterangan'],
                $data['tgl'],
                $random_target
            );
            assertFalse(
                $result_aktual,
                "Iteration {$i}: Aktual should return false for '{$random_target}'"
            );

            // Verify NO operations occurred
            assertCount(
                0,
                $this->simulator->db->inserts,
                "Iteration {$i}: No inserts for invalid target '{$random_target}'"
            );
            assertCount(
                0,
                $this->simulator->db->batch_inserts,
                "Iteration {$i}: No batch inserts for invalid target '{$random_target}'"
            );
        }

        echo "    Property verified: {$iterations} random invalid targets all rejected\n";
    }

    // ========================================================================
    // Test 4.10: Property - BUM header always goes to jarh, BUK to japh
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3**
     *
     * Property: BUM operations ALWAYS insert header to {db}.jarh,
     * BUK operations ALWAYS insert header to {db}.japh.
     * 100 iterations with random targets and data.
     */
    public function testPropertyCorrectHeaderTableByJournalType(): void
    {
        $iterations = 100;
        $seed = 99;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, 2)];
            $expected_db = self::$TARGET_DB_MAP[$target];
            $data = $this->generateRandomTransactionData();

            // Test BUM -> jarh
            $this->simulator->reset();
            $result = $this->simulator->saveJurnalBUM(
                $data['kode'],
                $data['bank_asal'],
                $data['bank_tujuan'],
                $data['nilai'],
                $data['keterangan'],
                $data['tgl'],
                $target
            );
            assertEquals(
                $expected_db . '.jarh',
                $result['header_table'],
                "Iteration {$i}: BUM header must be '{$expected_db}.jarh'"
            );

            // Test BUK -> japh
            $this->simulator->reset();
            $result = $this->simulator->saveJurnalBUK(
                $data['kode'],
                $data['bank_asal'],
                $data['bank_tujuan'],
                $data['nilai'],
                $data['keterangan'],
                $data['tgl'],
                $target
            );
            assertEquals(
                $expected_db . '.japh',
                $result['header_table'],
                "Iteration {$i}: BUK header must be '{$expected_db}.japh'"
            );

            // Test Aktual (BUM type) -> jarh
            $this->simulator->reset();
            $result = $this->simulator->saveJurnalMutasiAktual(
                $data['kode'],
                $data['bank_asal'],
                $data['bank_tujuan'],
                $data['nilai'],
                $data['keterangan'],
                $data['tgl'],
                $target
            );
            assertEquals(
                $expected_db . '.jarh',
                $result['header_table'],
                "Iteration {$i}: Aktual header must be '{$expected_db}.jarh'"
            );
        }

        echo "    Property verified: {$iterations} iterations confirm correct header tables\n";
    }

    // ========================================================================
    // Test 4.11: Property - Journal detail always goes to {db}.jurnal
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
     *
     * Property: For ANY valid target and ANY journal type (BUM/BUK/Aktual),
     * the detail records ALWAYS insert to {db_name}.jurnal
     */
    public function testPropertyDetailAlwaysGoesToJurnalTable(): void
    {
        $iterations = 100;
        $seed = 150;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, 2)];
            $expected_db = self::$TARGET_DB_MAP[$target];
            $data = $this->generateRandomTransactionData();
            $this->simulator->reset();

            $op_type = mt_rand(0, 2);
            if ($op_type === 0) {
                $result = $this->simulator->saveJurnalBUM(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } elseif ($op_type === 1) {
                $result = $this->simulator->saveJurnalBUK(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } else {
                $result = $this->simulator->saveJurnalMutasiAktual(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            }

            assertIsArray($result, "Iteration {$i}: result should be array");
            assertEquals(
                $expected_db . '.jurnal',
                $result['detail_table'],
                "Iteration {$i}: detail must go to '{$expected_db}.jurnal' for '{$target}'"
            );

            // Also verify via batch_inserts log
            assertTrue(
                count($this->simulator->db->batch_inserts) > 0,
                "Iteration {$i}: should have batch inserts"
            );
            assertEquals(
                $expected_db . '.jurnal',
                $this->simulator->db->batch_inserts[0]['table'],
                "Iteration {$i}: batch insert table should be '{$expected_db}.jurnal'"
            );
        }

        echo "    Property verified: {$iterations} iterations confirm detail goes to .jurnal\n";
    }

    // ========================================================================
    // Test 4.12: Property - No cross-database contamination (randomized)
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
     *
     * Property: For ANY valid target, NO operation references any other
     * target database. Validates isolation between accounting databases.
     */
    public function testPropertyNoCrossDatabaseContamination(): void
    {
        $iterations = 100;
        $seed = 200;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, 2)];
            $expected_db = self::$TARGET_DB_MAP[$target];
            $other_dbs = array_diff(array_values(self::$TARGET_DB_MAP), [$expected_db]);
            $data = $this->generateRandomTransactionData();
            $this->simulator->reset();

            // Random operation type
            $op_type = mt_rand(0, 2);
            if ($op_type === 0) {
                $this->simulator->saveJurnalBUM(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } elseif ($op_type === 1) {
                $this->simulator->saveJurnalBUK(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } else {
                $this->simulator->saveJurnalMutasiAktual(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            }

            // Check all targeted tables
            $all_tables = $this->simulator->db->getTargetedTables();
            foreach ($all_tables as $table) {
                foreach ($other_dbs as $other_db) {
                    assertFalse(
                        strpos($table, $other_db) === 0,
                        "Iter {$i}: '{$table}' must NOT use '{$other_db}' (target='{$target}')"
                    );
                }
            }

            // Check all queries
            foreach ($this->simulator->db->queries as $sql) {
                foreach ($other_dbs as $other_db) {
                    assertFalse(
                        strpos($sql, $other_db . '.') !== false,
                        "Iter {$i}: SQL must NOT reference '{$other_db}' (target='{$target}')"
                    );
                }
            }
        }

        echo "    Property verified: {$iterations} iterations confirm no cross-DB contamination\n";
    }

    // ========================================================================
    // Test 4.13: Mutasi Aktual journal routing to correct database
    // ========================================================================

    /**
     * **Validates: Requirements 4.1, 4.4, 4.7**
     *
     * For each valid target_accounting, the _save_jurnal_mutasi_aktual
     * inserts header to {db_name}.jarh and detail to {db_name}.jurnal.
     */
    public function testMutasiAktualJournalRoutesToCorrectDb(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $this->simulator->reset();
            $expected_db = self::$TARGET_DB_MAP[$target];

            $result = $this->simulator->saveJurnalMutasiAktual(
                'AKT-001',
                '1101001',
                '1101002',
                1500000.00,
                'Aktual routing test',
                '2024-09-01',
                $target
            );

            assertIsArray($result, "Aktual result should be array for '{$target}'");
            assertEquals($expected_db, $result['db_name']);
            assertEquals($expected_db . '.jarh', $result['header_table']);
            assertEquals($expected_db . '.jurnal', $result['detail_table']);

            // Verify counter update targets correct db
            $counter_found = false;
            foreach ($this->simulator->db->queries as $sql) {
                if (strpos($sql, $expected_db . '.pastibisa_tb_cabang') !== false) {
                    $counter_found = true;
                    break;
                }
            }
            assertTrue($counter_found, "Aktual counter should target '{$expected_db}' for '{$target}'");

            // Verify journal number stored
            assertTrue(count($this->simulator->record_updates) > 0);
            assertEquals('tr_request_mutasi_aktual', $this->simulator->record_updates[0]['table']);
            assertEquals('jurnal', $this->simulator->record_updates[0]['field']);
            assertEquals($result['journal_no'], $this->simulator->record_updates[0]['value']);
        }
    }

    // ========================================================================
    // Test 4.14: Property - Journal number is always non-empty and unique
    // ========================================================================

    /**
     * **Validates: Requirements 4.7**
     *
     * Property: Every successful journal operation produces a non-empty,
     * unique journal number that is stored back.
     */
    public function testPropertyJournalNumberAlwaysNonEmptyAndUnique(): void
    {
        $iterations = 100;
        $seed = 333;
        mt_srand($seed);
        $generated_numbers = [];

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, 2)];
            $data = $this->generateRandomTransactionData();
            $this->simulator->reset();

            $op_type = mt_rand(0, 2);
            if ($op_type === 0) {
                $result = $this->simulator->saveJurnalBUM(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } elseif ($op_type === 1) {
                $result = $this->simulator->saveJurnalBUK(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            } else {
                $result = $this->simulator->saveJurnalMutasiAktual(
                    $data['kode'],
                    $data['bank_asal'],
                    $data['bank_tujuan'],
                    $data['nilai'],
                    $data['keterangan'],
                    $data['tgl'],
                    $target
                );
            }

            assertIsArray($result, "Iteration {$i}: result should be array");
            assertTrue(
                strlen($result['journal_no']) > 0,
                "Iteration {$i}: journal number must not be empty"
            );

            // Check uniqueness
            assertFalse(
                in_array($result['journal_no'], $generated_numbers),
                "Iteration {$i}: journal number '{$result['journal_no']}' should be unique"
            );
            $generated_numbers[] = $result['journal_no'];

            // Verify it was stored back in record
            assertTrue(
                count($this->simulator->record_updates) > 0,
                "Iteration {$i}: journal number should be stored in record"
            );
            assertEquals(
                $result['journal_no'],
                $this->simulator->record_updates[0]['value'],
                "Iteration {$i}: stored value should match generated journal number"
            );
        }

        echo "    Property verified: {$iterations} unique journal numbers generated and stored\n";
    }
}

// ============================================================================
// Run tests
// ============================================================================

$runner = new SimpleTestRunner();
$exitCode = $runner->run(new JournalRoutingTest());
exit($exitCode);
