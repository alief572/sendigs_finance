<?php
/** Read-only audit using the application's configured default database. */
if (PHP_SAPI !== 'cli') exit('CLI only');
mysqli_report(MYSQLI_REPORT_OFF);
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('ENVIRONMENT', 'development');
$configPath = dirname(__DIR__, 3) . '/config/development/database.php';
require $configPath;
$settings = $db[$active_group];
$connection = mysqli_init();
$connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
if (!@$connection->real_connect($settings['hostname'], $settings['username'], $settings['password'], $settings['database'], isset($settings['port']) ? (int)$settings['port'] : 3306)) {
    fwrite(STDERR, "Unable to connect to configured database for read-only preview.\n");
    exit(1);
}
$connection->set_charset('utf8mb4');
$sql = "SELECT pc.id, pc.no_pelaporan, pc.no_payment_hutang, pc.company
    FROM tr_petty_cash_vuca_sustain pc
    WHERE pc.status = 'waiting payment'
      AND EXISTS (
        SELECT 1 FROM payment_approve pa
        INNER JOIN tr_payment_paid pp ON pp.id = pa.id_payment
        WHERE pc.no_payment_hutang = CONVERT(pa.no_doc USING utf8mb4) COLLATE utf8mb4_general_ci AND pa.status = 2
      ) ORDER BY pc.id";
$result = $connection->query($sql);
if ($result === false) {
    fwrite(STDERR, "Read-only preview failed: " . $connection->error . "\n");
    exit(1);
}
$audit = [
    'host' => $settings['hostname'], 'database' => $settings['database'],
    'mode' => 'read-only', 'candidates_count' => $result->num_rows,
    'candidates' => $result->fetch_all(MYSQLI_ASSOC)
];
$engines = $connection->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('tr_petty_cash_vuca_sustain', 'payment_approve', 'tr_payment_paid')");
if ($engines === false) {
    fwrite(STDERR, "Unable to inspect transaction table engines.\n");
    exit(1);
}
$audit['table_engines'] = $engines->fetch_all(MYSQLI_ASSOC);
echo json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
$connection->close();
