<?php
define('MATERIAL_FIXTURE_ONLY', true);
require __DIR__.'/Batch1_test.php';
require APPPATH.'helpers/json_helper.php';
require_once APPPATH.'helpers/periodik_helper.php';
set_error_handler(function ($severity, $message, $file, $line) {
    $GLOBALS['settlementFixtureError'] = $message.' at '.basename($file).':'.$line;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
$source = file_get_contents(dirname(__DIR__).'/models/Pembayaran_material_model.php');
preg_match_all('/->([a-zA-Z_]\w*)\b(?!\s*\()/', $source, $properties);
$GLOBALS['materialRowDefaults'] = array_merge(array_fill_keys($properties[1], 0), [
    'nama' => 'Bank', 'nama_bank' => 'Bank', 'rekening' => 'Test', 'coa_bank' => '1101-02-01',
    'nm_coa' => 'Account', 'no_coa' => '1101-02-01', 'name' => 'Finance', 'nm_company' => 'STM',
    'company_id' => 'COM003', 'department_id' => 5, 'currency' => 'IDR', 'max_id' => '', 'kode_bank' => 'BCA',
    'no_payment_hutang' => 'PHP-1'
]);
class SettlementRows {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result() { return array_map(function ($row) { return (object) $row; }, $this->rows); }
    public function row() { return $this->result()[0] ?? null; }
}
class SettlementDb extends MaterialDb {
    private $coas = [];
    public $sourceLocks = [];
    public function __call($name, $args) {
        global $scenario;
        if ($name === 'where_in' && $args[0] === 'no_perkiraan') { $this->coas = $args[1]; }
        $table = strtok($args[0] ?? $this->table, ' ');
        $result = parent::__call($name, $args);
        if ($name === 'get' && $table === 'coa_master' && $this->coas) {
            return new SettlementRows(array_map(function ($coa) { return ['no_coa' => $coa, 'nm_coa' => 'Account '.$coa]; }, $this->coas));
        }
        if (($name === 'get_where' && $table === 'tr_pengajuan_rutin_detail') ||
            ($name === 'query' && strpos($args[0], 'SELECT coa, nilai FROM tr_pengajuan_rutin_detail') === 0)) {
            if ($name === 'query') { $this->sourceLocks[] = $args; }
            if ($name === 'query' && isset($scenario['source_failure'])) {
                $this->queryError = 999;
                return $scenario['source_failure'] === 'false' ? false : new SettlementRows([]);
            }
            return $this->queryError ? false : new SettlementRows($scenario['source_by_document'][$args[1][0] ?? ''] ?? ($scenario['source_rows'] ?? []));
        }
        return $result;
    }
}
function settlement_preview($options, $post) {
    $options['db_fixture'] = new SettlementDb();
    list($event, $output) = material_run('set_jurnal', $options, $post);
    $preview = json_decode($output, true);
    check($event['status'] === 1 && $preview['preview_error'] === '', 'Settlement preview: '.$output);
    check(abs($preview['ttl_debit'] - $preview['ttl_kredit']) < 0.001, 'Settlement preview balance');
    preg_match_all('~<input type="hidden" name="jurnal_ls\[(\d+)\]\[([a-z_]+)\]" value="([^"]*)">~', $preview['hasil_jurnal'], $inputs, PREG_SET_ORDER);
    $rows = [];
    foreach ($inputs as $input) { $rows[$input[1]][$input[2]] = html_entity_decode($input[3], ENT_QUOTES, 'UTF-8'); }
    return array_replace($post, ['jurnal_ls' => array_values($rows)]);
}
function settlement_save($options, $post, $success, $beforeWrites = true) {
    unset($GLOBALS['settlementFixtureError']);
    $options['db_fixture'] = new SettlementDb();
    list($event, $output, $controller) = material_run('save_payment', $options, $post);
    check(json_decode($output, true)['status'] === ($success ? 1 : 0), 'Settlement save: '.$output.' '.($GLOBALS['settlementFixtureError'] ?? ''));
    if ($success) {
        check(in_array('trans_commit', $event['calls_at_write'], true), 'Success audit after commit');
    } else {
        check($event['status'] === 0, 'One failed audit');
        check(in_array('trans_rollback', $controller->db->calls, true), 'Settlement rollback');
        if ($beforeWrites) { check(!$controller->db->writes, 'Rejected before business writes'); }
    }
    return [$event, $controller];
}
$id = 'BK--1126-0005';
$options = ['row_overrides' => ['id' => $id, 'tipe' => 'periodik'], 'source_rows' => [
    ['coa' => '2104-01-03', 'nama' => 'PPh 23', 'nilai' => '20192299', 'no_doc' => 'PERIODIK-2026-00222']],
    'table_overrides' => ['payment_approve' => ['no_doc' => 'PERIODIK-2026-00222', 'jumlah' => '20192299'],
        'tr_choosed_payment' => ['id_payment' => $id]]];
$post = ['id_payment' => $id, 'bank' => '18', 'tgl_bayar' => '2026-11-11', 'payment_bank' => '20192299.00',
    'total_payment' => '20192299.00', 'bank_charge' => '0', 'admin_charge_bearer' => 'company',
    'item_ppn' => [$id => '0'], 'item_pph' => [$id => '0'], 'pph_data' => [$id => ''],
    'dt' => [['id_payment' => $id, 'tipe_pph' => '', 'nilai_ppn' => '0', 'nilai_pph' => '0']]];
$post = settlement_preview($options, $post);
list($event, $saved) = settlement_save($options, $post, true);
check(count($saved->db->sourceLocks) === 1 && strpos($saved->db->sourceLocks[0][0], 'WHERE no_doc = ? FOR UPDATE') !== false &&
    $saved->db->sourceLocks[0][1] === ['PERIODIK-2026-00222'], 'Lock exact server source batch');
$staging = $accounting = [];
$glPrincipal = 0;
foreach ($saved->db->writes as $write) {
    if ($write[1][0] === 'tr_jurnal') { $staging = $write[1][1]; }
    if ($write[1][0] === 'jurnaltras') { $accounting = $write[1][1]; }
    if ($write[1][0] === DBACC.'.jurnal' && $write[1][1]['no_perkiraan'] === '2104-01-03') {
        $glPrincipal++;
        check($write[1][1]['debet'] === '20192299.00' && $write[1][1]['kredit'] === '0.00', 'GL preserves principal debit');
    }
}
check($glPrincipal === 1, 'One tax settlement debit in GL');
check(count($staging) === count($accounting) && count($staging) === count($post['jurnal_ls']), 'Staging/accounting parity');
foreach ($staging as $i => $row) {
    check($row['coa'] === $accounting[$i]['no_perkiraan'] && $row['debit'] === $accounting[$i]['debet'] && $row['kredit'] === $accounting[$i]['kredit'], 'Same accounting COA and amounts');
}
$principal = array_search('2104-01-03', array_column($post['jurnal_ls'], 'coa'), true);
check($principal !== false && $post['jurnal_ls'][$principal]['debit'] === '20192299.00', 'Original tax debt stays debit');
echo "PASS original BK--1126-0005 preview/save\n";

foreach ([
    ['coa' => '2104-01-02'],
    ['no_doc' => 'PI-2026-00288'],
    ['source_rows' => [
        ['coa' => '2104-01-03', 'nama' => 'PPh 23 A', 'nilai' => '10000000'],
        ['coa' => '2104-01-03', 'nama' => 'PPh 23 B', 'nilai' => '10000000'],
        ['coa' => '2104-01-02', 'nama' => 'PPh 21', 'nilai' => '192299']]]
] as $case) {
    $variant = $options;
    if (isset($case['coa'])) { $variant['source_rows'][0]['coa'] = $case['coa']; }
    if (isset($case['no_doc'])) { $variant['table_overrides']['payment_approve']['no_doc'] = $case['no_doc']; }
    if (isset($case['source_rows'])) { $variant['source_rows'] = $case['source_rows']; }
    $variantPost = settlement_preview($variant, $post);
    list($event, $controller) = settlement_save($variant, $variantPost, true);
    if (isset($case['no_doc'])) {
        check(strpos($controller->db->sourceLocks[0][0], 'WHERE no_item = ? FOR UPDATE') !== false &&
            $controller->db->sourceLocks[0][1] === [$case['no_doc']], 'Item source uses exact no_item');
    }
}
foreach ([['23', '403846.00', '19788453.00'], ['21', '504808.00', '19687491.00']] as $withholding) {
    $taxPost = $post;
    $taxPost['pph_data'][$id] = $taxPost['dt'][0]['tipe_pph'] = $withholding[0];
    $taxPost['item_pph'][$id] = $taxPost['dt'][0]['nilai_pph'] = $withholding[1];
    $taxPost['payment_bank'] = $taxPost['total_payment'] = $withholding[2];
    $taxPost = settlement_preview($options, $taxPost);
    settlement_save($options, $taxPost, true);
    foreach ($taxPost['jurnal_ls'] as &$row) {
        if ($row['kredit'] === $withholding[1]) { $row['coa'] = '6207-00-00'; }
    }
    unset($row);
    settlement_save($options, $taxPost, false);
}
foreach (['company', 'recipient'] as $bearer) {
    $adminPost = array_replace($post, ['bank_charge' => '6500', 'admin_charge_bearer' => $bearer,
        'payment_bank' => $bearer === 'company' ? '20198799.00' : '20192299.00']);
    settlement_save($options, settlement_preview($options, $adminPost), true);
}

$invalids = [];
foreach (['coa' => '2104-01-02', 'id_payment_ref' => 'OTHER', 'debit' => '20192300.00'] as $field => $value) {
    $tampered = $post;
    $tampered['jurnal_ls'][$principal][$field] = $value;
    $invalids[] = $tampered;
}
$tampered = $post;
$tampered['jurnal_ls'][$principal]['debit'] = '20192298.00';
$tampered['jurnal_ls'][] = array_replace($post['jurnal_ls'][$principal], ['coa' => '6207-00-00', 'debit' => '1.00']);
$invalids[] = $tampered; // Balance/bank stay correct, but principal is short.
$tampered = $post;
$tampered['jurnal_ls'][] = $post['jurnal_ls'][$principal];
$tampered['jurnal_ls'][] = array_replace($post['jurnal_ls'][$principal], ['coa' => '6207-00-00', 'debit' => '0.00', 'kredit' => '20192299.00']);
$invalids[] = $tampered; // Balanced duplicate principal still exceeds source.
$tampered = $post;
$tampered['jurnal_ls'][$principal]['coa'] = '6207-00-00';
$tampered['dt'][0]['principal'] = ['2104-01-03' => 0];
$invalids[] = $tampered; // Client metadata cannot remove server source requirement.
$tampered = $post;
$tampered['jurnal_ls'][$principal]['debit'] = '0.00';
$tampered['jurnal_ls'][$principal]['kredit'] = '20192299.00';
$invalids[] = $tampered;
foreach ($invalids as $tampered) { settlement_save($options, $tampered, false); }
foreach ([[], [['coa' => '6207-00-00', 'nilai' => '20192299']],
    [['coa' => '2104-01-02', 'nilai' => '20192299']], [['coa' => '2104-01-03', 'nilai' => '20192298']],
    [['coa' => '2104-01-03', 'nilai' => '20192300']], [['coa' => '2104-01-03', 'nilai' => '-1']]] as $changedSource) {
    settlement_save(array_replace($options, ['source_rows' => $changedSource]), $post, false);
}
foreach (['false', 'error'] as $failure) { settlement_save($options + ['source_failure' => $failure], $post, false); }
$nonPeriodic = $options;
$nonPeriodic['row_overrides']['tipe'] = 'expense';
$forged = $post;
$forged['dt'][0]['principal'] = ['2104-01-03' => 2019229900];
settlement_save($nonPeriodic, $forged, false);

// Each new source query, write, transaction and audit-writer failure must keep existing save contract.
for ($i = 1; $i <= $saved->db->queryNumber; $i++) {
    settlement_save($options + ['fail_query' => $i], $post, false, false);
}
for ($i = 1; $i <= $saved->db->writeNumber; $i++) {
    settlement_save($options + ['fail_write' => $i], $post, false, false);
}
foreach (['trans_begin_failure', 'transaction_failure', 'trans_commit_failure'] as $failure) {
    settlement_save($options + [$failure => true], $post, false, false);
}
foreach (['writer_failure', 'writer_exception'] as $failure) { settlement_save($options + [$failure => true], $post, true); }

$payments = [];
$GLOBALS['scenario'] = ['source_by_document' => []];
foreach (['A' => '100', 'B' => '200'] as $key => $amount) {
    $payments[] = (object) ['id' => $key, 'no_doc' => 'PERIODIK-'.$key, 'tipe' => 'periodik', 'jumlah' => $amount];
    $GLOBALS['scenario']['source_by_document']['PERIODIK-'.$key] = [['coa' => '2104-01-03', 'nilai' => $amount]];
}
$model = new MaterialModel(new SettlementDb(), new MaterialInput());
$totals = $model->payment_totals($payments, [], '0', 'company');
$multi = [
    ['coa' => '2104-01-03', 'id_payment_ref' => 'A', 'debit' => '100', 'kredit' => '0'],
    ['coa' => '2104-01-03', 'id_payment_ref' => 'B', 'debit' => '200', 'kredit' => '0'],
    ['coa' => '1101-02-01', 'debit' => '0', 'kredit' => '300']];
$model->validate_payment_journal($multi, $totals, '1101-02-01', $payments);
$multi[0]['id_payment_ref'] = 'B';
$multi[1]['id_payment_ref'] = 'A';
try {
    $model->validate_payment_journal($multi, $totals, '1101-02-01', $payments);
    throw new RuntimeException('Cross-payment principal swap accepted');
} catch (Invalid_payment_journal $expected) {}
echo 'PASS periodic tax settlement: '.$scenarioCount." endpoint scenarios, source locks, tampering, accounting/GL, rollback/commit/audit, multi-payment isolation\n";
