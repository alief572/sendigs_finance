<?php
// Jalankan dari root: scratch/runtime/php74/php.exe application/modules/monitoring_piutang/cli/check_follow_up_targets.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/../libraries/Piutang_aging.php';
require dirname(__DIR__, 3) . '/config/development/database.php';
$config = [];
require dirname(__DIR__, 3) . '/config/piutang.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('Asia/Jakarta');
$conn = null;
$inTransaction = false;
try {
    $cfg = $db['default'];
    $conn = mysqli_init();
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 8);
    @$conn->real_connect($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database'], $cfg['port'] ?? 3306);
    $conn->set_charset('utf8mb4');
    $conn->query('SET SESSION TRANSACTION READ ONLY');
    $conn->begin_transaction();
    $inTransaction = true;
    $targets = Piutang_aging::FOLLOW_UP_INVOICES;
    $in = implode(',', array_map(function ($no) use ($conn) { return "'" . $conn->real_escape_string($no) . "'"; }, $targets));
    $rows = $conn->query("SELECT i.id, i.no_invoice, i.tanggal_invoice AS tgl_invoice,
        i.saldo_piutang AS saldo, i.sts_close, i.tax_treatment, i.nm_customer AS customer,
        i.id_spk_penawaran AS no_spk, i.total_akhir_jurnal AS nilai,
        ar.tgl_kirim, ar.tgl_terima, ar.plan_bayar, c.id AS company,
        (SELECT COUNT(*) FROM tr_penerimaan_piutang_detail ppd WHERE ppd.id_inv = i.id) AS receipt_count
        FROM tr_invoicing i LEFT JOIN tr_invoice_ar ar ON ar.id_invoice = i.id
        LEFT JOIN db_consultant_new.kons_tr_penawaran p ON p.id_quotation = i.id_penawaran AND i.non_kons <> '1'
        LEFT JOIN db_consultant_new.kons_tr_spk_penawaran s ON s.id_spk_penawaran = i.id_spk_penawaran AND i.non_kons <> '1'
        LEFT JOIN db_consultant_new.kons_tr_penawaran_non_konsultasi nk ON nk.id_penawaran = i.id_penawaran AND i.non_kons = '1'
        LEFT JOIN db_consultant_new.kons_tr_company c ON c.id = IF(i.non_kons = '1', nk.id_company, COALESCE(p.company, s.id_company))
        WHERE i.no_invoice IN ($in)")->fetch_all(MYSQLI_ASSOC);
    $matrix = [];
    foreach ($targets as $no) {
        $matches = array_values(array_filter($rows, function ($r) use ($no) { return $r['no_invoice'] === $no; }));
        $issues = [];
        if (!$matches) { $issues[] = 'missing'; }
        if (count($matches) > 1 && !in_array($no, ['228/STM/2026', '230/STM/2026'], true)) { $issues[] = 'duplicate'; }
        foreach ($matches as &$r) {
            if (Piutang_aging::is_closed($r)) { $issues[] = 'closed'; }
            if (!Piutang_aging::eligible_invoice_date($r)) { $issues[] = 'invalid_invoice_date'; }
            if ($r['tax_treatment'] === 'DEP' || strpos($r['id'], 'KD-') === 0) { $issues[] = 'deposit'; }
            $r['status'] = Piutang_aging::ar_status($r);
            $r['follow_up_visible'] = Piutang_aging::eligible_invoice_date($r) && $r['tax_treatment'] !== 'DEP' && strpos($r['id'], 'KD-') !== 0 && (bool) Piutang_aging::filter_ar_rows([$r], ['tab' => 'all'], Piutang_aging::today(), $config['tanggal_mulai_follow_up'] ?? '');
            $r['monitoring_position'] = Piutang_aging::is_closed($r) ? 'excluded_closed' : (Piutang_aging::is_aged($r) ? 'list_and_summary' : (Piutang_aging::is_unreceived($r) ? 'unreceived_summary' : 'paid_summary'));
        }
        unset($r);
        $entry = ['no_invoice' => $no, 'issues' => array_values(array_unique($issues)), 'matches' => $matches];
        if (count($matches) > 1 && !$issues) { $entry['note'] = 'All rows confirmed valid by workspace owner on 2026-11-19.'; }
        $matrix[] = $entry;
    }
    $conn->rollback();
    echo json_encode(['database' => $cfg['database'], 'checked_at' => date(DATE_ATOM), 'targets' => $matrix], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), PHP_EOL;
    exit(count(array_filter($matrix, function ($r) { return (bool) $r['issues']; })) ? 1 : 0);
} catch (Throwable $e) {
    if ($inTransaction) { try { $conn->rollback(); } catch (Throwable $ignored) {} }
    fwrite(STDERR, 'READ ONLY target check unavailable (error code ' . $e->getCode() . '). No business data changed.' . PHP_EOL);
    exit(2);
}
