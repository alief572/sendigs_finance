<?php
/** CLI only. Default: read-only audit. --apply: only the authorized 036/VSB/2026 gross correction. */
if (PHP_SAPI !== 'cli') { exit('CLI only'); }
define('BASEPATH', __DIR__);
define('ENVIRONMENT', 'development');
require dirname(__DIR__, 3) . '/config/development/database.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$config = $db['default'];
$connection = new mysqli($config['hostname'], $config['username'], $config['password'], $config['database'], $config['port'] ?? 3306);
$connection->set_charset('utf8mb4');
function rows($connection, $sql, $params = array()) {
    $statement = $connection->prepare($sql);
    if ($params) { $statement->bind_param(str_repeat('s', count($params)), ...$params); }
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    $statement->close(); return $rows;
}
$apply = in_array('--apply', $argv, true);
$locked = false;
try {
    if ($apply) {
        if ($config['database'] !== 'db_sendigs_ss') { throw new RuntimeException('Unexpected database'); }
        $locked = (int) rows($connection, "SELECT GET_LOCK('sendigs_receipt_v2', 10) AS acquired")[0]['acquired'] === 1;
        if (!$locked) { throw new RuntimeException('Receipt processing busy'); }
        $connection->begin_transaction();
    } else {
        $connection->query('START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY');
    }
    $suffix = $apply ? ' FOR UPDATE' : '';
    $invoices = rows($connection, 'SELECT * FROM tr_invoicing WHERE id = ? AND no_invoice = ?' . $suffix, array('00297-INV-IX-26', '036/VSB/2026'));
    if (count($invoices) !== 1) { throw new RuntimeException('Target invoice missing or ambiguous'); }
    $before = $invoices[0];
    $receipts = rows($connection, 'SELECT * FROM tr_penerimaan_piutang_detail WHERE id_inv = ?' . $suffix, array($before['id']));
    $journals = rows($connection, "SELECT * FROM tr_jurnal WHERE no_transaksi = ? OR (jenis_transaksi = 'Penerimaan Piutang' AND keterangan LIKE ?) ORDER BY id" . $suffix, array($before['id'], '% - ' . $before['no_invoice']));
    if ($apply) {
        foreach (array('tipe_invoice'=>'1','total_nominal_jurnal'=>'15000000.00','ppn_jurnal'=>'0.00','pph_jurnal'=>'75000.00','total_akhir_jurnal'=>'14925000.00','saldo_piutang'=>'14925000.00') as $key=>$expected) {
            if ((string) $before[$key] !== $expected) { throw new RuntimeException('Target state changed: ' . $key); }
        }
        if ($receipts) { throw new RuntimeException('Receipt already exists; correction refused'); }
        $debits = array(); $credits = array();
        foreach ($journals as $journal) {
            if ($journal['jenis_transaksi'] !== 'Invoicing' || $journal['no_transaksi'] !== $before['id'] || $journal['sts'] !== '1') {
                throw new RuntimeException('Unexpected or unposted journal');
            }
            $debits[$journal['coa']] = ($debits[$journal['coa']] ?? 0) + (int) round((float) $journal['debit'] * 100);
            $credits[$journal['coa']] = ($credits[$journal['coa']] ?? 0) + (int) round((float) $journal['kredit'] * 100);
        }
        if (count($journals) !== 4 || ($debits['1102-01-01'] ?? 0) !== 1492500000 || ($debits['1106-01-05'] ?? 0) !== 7500000 || ($credits['4101-01-01'] ?? 0) !== 1500000000 || array_sum($debits) !== 1500000000 || array_sum($credits) !== 1500000000) {
            throw new RuntimeException('Invoice journal evidence differs');
        }
        if (!in_array($before['tagihan_ppn_jurnal'], array('0.00','15000000.00'), true)) { throw new RuntimeException('Unexpected gross value'); }
        if ($before['tagihan_ppn_jurnal'] === '0.00') {
            rows($connection, "UPDATE tr_invoicing SET tagihan_ppn_jurnal = '15000000.00' WHERE id = ? AND tagihan_ppn_jurnal = 0", array($before['id']));
        }
    }
    $after = rows($connection, 'SELECT * FROM tr_invoicing WHERE id = ?', array($before['id']))[0];
    $expectedAfter = $before;
    if ($apply) { $expectedAfter['tagihan_ppn_jurnal'] = '15000000.00'; }
    if ($after !== $expectedAfter || $journals !== rows($connection, "SELECT * FROM tr_jurnal WHERE no_transaksi = ? OR (jenis_transaksi = 'Penerimaan Piutang' AND keterangan LIKE ?) ORDER BY id", array($before['id'], '% - ' . $before['no_invoice']))) {
        throw new RuntimeException('Unintended invoice/journal change');
    }
    $audit = rows($connection, "SELECT i.id, i.no_invoice, i.total_nominal_jurnal, i.ppn_jurnal, i.pph_jurnal, i.tagihan_ppn_jurnal, i.total_akhir_jurnal, i.saldo_piutang, (SELECT COUNT(*) FROM tr_penerimaan_piutang_detail d WHERE d.id_inv = i.id) AS receipt_count FROM tr_invoicing i WHERE i.tipe_invoice = '1' AND i.tagihan_ppn_jurnal = 0 AND i.total_nominal_jurnal > 0 ORDER BY i.id");
    $connection->commit();
    echo json_encode(array('mode'=>$apply?'targeted_correction':'read_only','invoice'=>$before['no_invoice'],'gross_before'=>$before['tagihan_ppn_jurnal'],'gross_after'=>$after['tagihan_ppn_jurnal'],'balance_unchanged'=>$after['saldo_piutang'],'receipt_count'=>count($receipts),'posted_journals_unchanged'=>count($journals),'saldo_piutang_tanpa_pph_unchanged'=>$after['saldo_piutang_tanpa_pph'],'other_legacy_vuca'=>$audit), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $error) {
    $connection->rollback();
    fwrite(STDERR, $error->getMessage() . PHP_EOL); exit(1);
} finally {
    if ($locked) { rows($connection, "SELECT RELEASE_LOCK('sendigs_receipt_v2')"); }
    $connection->close();
}
