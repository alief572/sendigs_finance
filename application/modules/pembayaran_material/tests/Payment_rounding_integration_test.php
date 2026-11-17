<?php
define('MATERIAL_FIXTURE_ONLY', true);
require __DIR__.'/Batch1_test.php';
$child = null;
require APPPATH.'helpers/json_helper.php';
require_once APPPATH.'helpers/periodik_helper.php';
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
$source = file_get_contents(dirname(__DIR__).'/models/Pembayaran_material_model.php');
preg_match_all('/->([a-zA-Z_]\w*)\b(?!\s*\()/', $source, $properties);
$GLOBALS['materialRowDefaults'] = array_merge(array_fill_keys($properties[1], 0), [
    'nama' => 'Bank', 'nm_bank' => 'Bank', 'nama_bank' => 'Bank', 'rekening' => 'Test',
    'coa_bank' => '1101-01', 'nm_coa' => 'Kas', 'no_coa' => '1101-01', 'coa' => '1101-01',
    'requestor' => 'Test', 'request_by' => 'Test', 'keperluan' => 'Test', 'created_on' => '2026-10-05',
    'max_id' => '', 'kode_bank' => 'BNK', 'subcab' => '01', 'no_payment_hutang' => 'PHP-1',
    'department_id' => 5, 'company_id' => 'COM006', 'nm_company' => 'Sustain', 'name' => 'Department', 'currency' => 'IDR'
]);
$options = ['row_overrides' => ['tipe' => 'expense'], 'table_overrides' => [
    'payment_approve' => ['jumlah' => '1000028.0000', 'total_ppn' => '0.00', 'total_pph' => '999.4400', 'tipe_pph' => 'PPH 23'],
    'tr_expense' => ['id_kasbon' => 1]
]];
$post = ['bank' => '18', 'total_payment' => '980027.00', 'payment_bank' => '980027.00', 'bank_charge' => '0',
    'nilai_ppn' => '0', 'nilai_pph' => '20001.00', 'item_ppn' => ['PAY-1' => '0'], 'item_pph' => ['PAY-1' => '20001.00'],
    'pph_data' => ['PAY-1' => '23'],
    'dt' => [['id_payment' => 'PAY-1', 'nilai_ppn' => '0', 'nilai_pph' => '20001.00', 'tipe_pph' => '23', 'kurs_invoice' => 1]]];
function rounding_preview_rows($preview) {
    preg_match_all('~<input type="hidden" name="jurnal_ls\[(\d+)\]\[([a-z_]+)\]" value="([^"]*)">~', $preview['hasil_jurnal'], $inputs, PREG_SET_ORDER);
    $rows = [];
    foreach ($inputs as $input) { $rows[$input[1]][$input[2]] = html_entity_decode($input[3], ENT_QUOTES, 'UTF-8'); }
    return array_values($rows);
}
list($event, $output) = material_run('set_jurnal', $options, $post);
$preview = json_decode($output, true);
check($event['status'] === 1 && $preview['preview_error'] === '', 'PPh preview needs no rounding COA');
check(!array_intersect(['rounding_amount', 'rounding_bank', 'rounding_error'], array_keys($preview)), 'Bank rounding response removed');
$rows = rounding_preview_rows($preview);
$post['jurnal_ls'] = array_values($rows);
check(count(array_filter($rows, function ($row) { return !empty($row['payment_rounding']); })) === 0, 'No bank rounding row in preview');
check(abs($preview['ttl_debit'] - $preview['ttl_kredit']) < 0.001, 'Preview balances with PPh');
list($event, $output, $controller) = material_run('save_payment', $options, $post);
check(json_decode($output, true)['status'] === 1 && $event['status'] === 1, 'Save real preview: '.$output);
check(!isset($event['context']['data']['rounding_amount']) && in_array('trans_commit', $event['calls_at_write'], true), 'One success audit after commit without bank rounding');
$staging = $accounting = [];
$glPPh = 0;
foreach ($controller->db->writes as $write) {
    if ($write[1][0] === 'tr_jurnal') { $staging = $write[1][1]; }
    if ($write[1][0] === 'jurnaltras') { $accounting = $write[1][1]; }
    if ($write[1][0] === DBACC.'.jurnal' && $write[1][1]['no_perkiraan'] === '2104-01-03') {
        $glPPh++;
        check($write[1][1]['kredit'] === '20001.00', 'GL uses rounded PPh');
    }
    if ($write[0] === 'update' && $write[1][0] === 'payment_approve' && isset($write[1][1]['payment_bank'])) {
        check($write[1][1]['coa_bank'] === '1101-01' && $write[1][1]['payment_bank'] === '980027.00', 'Bank ID resolves to COA and exact payment');
    }
}
check(count($staging) === count($accounting) && $glPPh === 1, 'Staging and accounting contain same rows with one PPh credit');
list($event, $output) = material_run('save_payment', $options, array_replace($post, ['bank_charge' => '']));
check(json_decode($output, true)['status'] === 1, 'Blank optional admin has same zero default as preview');
foreach ($staging as $i => $row) {
    check($row['coa'] === $accounting[$i]['no_perkiraan'], 'Accounting COA parity');
    check(Pembayaran_material_model::payment_cents($row['debit']) === Pembayaran_material_model::payment_cents($accounting[$i]['debet']), 'Accounting debit parity');
    check(Pembayaran_material_model::payment_cents($row['kredit']) === Pembayaran_material_model::payment_cents($accounting[$i]['kredit']), 'Accounting credit parity');
}
$invalids = [];
foreach (['980027.56', '980028.00', '-1', 'bad'] as $value) { $invalids[] = ['payment_bank' => $value]; }
$invalids[] = ['total_payment' => '1.00'];
$invalids[] = ['dt' => array_merge($post['dt'], $post['dt'])];
$invalids[] = ['dt' => [['id_payment' => 'OTHER']]];
$invalids[] = ['dt' => [['id_payment' => 'PAY-1', 'nilai_pph' => '-1']]];
$pphIndex = $bankIndex = null;
foreach ($post['jurnal_ls'] as $i => $row) {
    if ($row['coa'] === '2104-01-03') { $pphIndex = $i; }
    if ($row['coa'] === '1101-01') { $bankIndex = $i; }
}
check($pphIndex !== null && $bankIndex !== null, 'PPh and bank rows present');
$invalids[] = ['dt' => [array_replace($post['dt'][0], ['nilai_pph' => '20000.56'])]];
$invalids[] = ['dt' => [array_replace($post['dt'][0], ['tipe_pph' => '21'])]];
foreach (['coa' => '2104-01-02', 'id_payment_ref' => 'OTHER'] as $field => $value) {
    $tampered = $post['jurnal_ls']; $tampered[$pphIndex][$field] = $value;
    $invalids[] = ['jurnal_ls' => $tampered];
}
$tampered = $post['jurnal_ls'];
$tampered[$pphIndex]['kredit'] = '20000.00';
$tampered[$bankIndex]['kredit'] = '980028.00';
$invalids[] = ['jurnal_ls' => $tampered];
$tampered[$pphIndex]['coa'] = 'OTHER';
$tampered[$pphIndex]['kredit'] = '1.00';
$tampered[$bankIndex]['kredit'] = '980027.00';
$invalids[] = ['jurnal_ls' => $tampered];
$tampered = $post['jurnal_ls'];
$tampered[$pphIndex]['kredit'] = '20000.00';
$tampered[] = array_replace($post['jurnal_ls'][$pphIndex], ['coa' => 'OTHER', 'kredit' => '1.00']);
$invalids[] = ['jurnal_ls' => $tampered];
$invalids[] = ['jurnal_ls' => array_merge($post['jurnal_ls'], [$post['jurnal_ls'][$pphIndex]])];
foreach ($invalids as $invalid) {
    list($event, $output, $failed) = material_run('save_payment', $options, array_replace($post, $invalid));
    check(json_decode($output, true)['status'] === 0 && !$failed->db->writes, 'Tampering rejected before writes: '.json_encode($invalid));
}
$invalidCoa = $options;
$invalidCoa['table_overrides']['ms_bank']['coa_bank'] = '';
list($event, $output, $failed) = material_run('save_payment', $invalidCoa, $post);
check(json_decode($output, true)['status'] === 0 && !$failed->db->writes, 'Missing bank COA rejected');
foreach (['trans_commit_failure', 'transaction_failure', 'writer_failure', 'writer_exception'] as $failure) {
    list($event, $output) = material_run('save_payment', $options + [$failure => true], $post);
    $success = strpos($failure, 'writer_') === 0;
    check(json_decode($output, true)['status'] === ($success ? 1 : 0), 'Transaction and audit failure contract '.$failure);
}
for ($i = 1; $i <= $controller->db->writeNumber; $i++) {
    list($event, $output, $failed) = material_run('save_payment', $options + ['fail_write' => $i], $post);
    check(json_decode($output, true)['status'] === 0 && !in_array('trans_commit', $failed->db->calls, true), 'Rollback at business write '.$i);
}
for ($i = 1; $i <= $controller->db->queryNumber; $i++) {
    list($event, $output, $failed) = material_run('save_payment', $options + ['fail_query' => $i], $post);
    check(json_decode($output, true)['status'] === 0 && !in_array('trans_commit', $failed->db->calls, true), 'Rollback at query '.$i);
}
foreach (['company', 'recipient'] as $bearer) {
    $adminPost = array_replace($post, ['admin_charge_bearer' => $bearer, 'bank_charge' => '2500.25', 'payment_bank' => $bearer === 'company' ? '982527.25' : '980027.00']);
    list($event, $output) = material_run('set_jurnal', $options, $adminPost);
    $adminPreview = json_decode($output, true);
    check($adminPreview['preview_error'] === '', 'Fractional admin preview '.$bearer);
    check(abs($adminPreview['ttl_debit'] - $adminPreview['ttl_kredit']) < 0.001, 'Admin preview balances for '.$bearer);
    $adminPost['jurnal_ls'] = rounding_preview_rows($adminPreview);
    list($event, $output) = material_run('save_payment', $options, $adminPost);
    check(json_decode($output, true)['status'] === 1, 'Admin preview saves for '.$bearer.': '.$output);
}
foreach ([['21', '0', '25001.00', '975027.00'], ['', '0', '0.00', '1000028.00'],
    ['23', '110003.08', '20001.00', '1090030.08'], ['21', '110003.08', '25001.00', '1085030.08']] as $case) {
    $taxPost = $post;
    $taxPost['pph_data']['PAY-1'] = $taxPost['dt'][0]['tipe_pph'] = $case[0];
    $taxPost['item_ppn']['PAY-1'] = $taxPost['dt'][0]['nilai_ppn'] = $taxPost['nilai_ppn'] = $case[1];
    $taxPost['item_pph']['PAY-1'] = $taxPost['dt'][0]['nilai_pph'] = $taxPost['nilai_pph'] = $case[2];
    $taxPost['total_payment'] = $taxPost['payment_bank'] = $case[3];
    list($event, $output) = material_run('set_jurnal', $options, $taxPost);
    $taxPreview = json_decode($output, true);
    check($event['status'] === 1 && $taxPreview['preview_error'] === '' && abs($taxPreview['ttl_debit'] - $taxPreview['ttl_kredit']) < 0.001, 'Changed type/PPN preview balances');
    $taxPost['jurnal_ls'] = rounding_preview_rows($taxPreview);
    list($event, $output, $saved) = material_run('save_payment', $options, $taxPost);
    check(json_decode($output, true)['status'] === 1, 'Changed type/PPN saves: '.$output);
    $detailWritten = false;
    foreach ($saved->db->writes as $write) {
        if ($write[0] === 'update' && $write[1][0] === 'payment_approve' && isset($write[1][1]['total_pph'])) {
            $detailWritten = true;
            check($write[1][1]['total_pph'] === $case[2] && $write[1][1]['tipe_pph'] === ($case[0] === '' ? '' : 'PPH '.$case[0]), 'Persist computed PPh and type');
        }
    }
    check($detailWritten, 'Rounded PPh persisted');
}
foreach ([['item_pph' => ['PAY-1' => '20000.56']], ['pph_data' => ['PAY-1' => '21']],
    ['item_ppn' => ['PAY-1' => 'bad']], ['item_pph' => ['OTHER' => '20001']], ['item_ppn' => 'bad'],
    ['item_ppn' => ['PAY-1' => '110003.08'], 'item_pph' => ['PAY-1' => '22201.00']],
    ['pph_data' => ['PAY-1' => '21'], 'item_ppn' => ['PAY-1' => '110003.08'], 'item_pph' => ['PAY-1' => '27751.00']]] as $invalid) {
    list($event, $output) = material_run('set_jurnal', $options, array_replace($post, $invalid));
    $failedPreview = json_decode($output, true);
    check($event['status'] === 0 && $failedPreview['hasil_jurnal'] === '' && $failedPreview['preview_error'] !== '', 'Reject inconsistent preview details');
}

class PPhCoaResult {
    private $coas;
    public function __construct($coas) { $this->coas = $coas; }
    public function row() { return $this->result()[0]; }
    public function result() { return array_map(function ($coa) { return (object) ['no_coa' => $coa, 'nm_coa' => 'Account '.$coa]; }, $this->coas); }
}
class PPhCoaDb extends MaterialDb {
    private $coas = [];
    public function __call($name, $args) {
        if ($name === 'where_in' && $args[0] === 'no_perkiraan') { $this->coas = $args[1]; }
        $table = strtok($args[0] ?? $this->table, ' ');
        $result = parent::__call($name, $args);
        if ($name === 'get' && $table === 'coa_master' && $this->coas) { return new PPhCoaResult($this->coas); }
        return $result;
    }
}
foreach (['kasbon', 'transport', 'periodik', 'direct_payment'] as $sourceType) {
    $sourceOptions = $options;
    $sourceOptions['db_fixture'] = new PPhCoaDb();
    $sourceOptions['row_overrides']['tipe'] = $sourceType;
    $sourceOptions['table_overrides'] += [
        'tr_transport' => ['no_coa' => '6207-00-00'],
        'tr_pengajuan_rutin_detail' => ['coa' => '6207-00-00', 'nilai' => '1000028'],
        'kons_tr_kasbon_project_subcont_perusahaan' => ['no_coa' => '6207-00-00', 'total_pengajuan' => '1000028']
    ];
    list($event, $output) = material_run('set_jurnal', $sourceOptions, $post);
    $sourcePreview = json_decode($output, true);
    check($event['status'] === 1 && $sourcePreview['preview_error'] === '', 'Source PPh preview '.$sourceType.': '.$output);
    check(abs($sourcePreview['ttl_debit'] - $sourcePreview['ttl_kredit']) < 0.001, 'Source PPh balances '.$sourceType);
    $sourcePost = array_replace($post, ['jurnal_ls' => rounding_preview_rows($sourcePreview)]);
    $sourceOptions['db_fixture'] = new PPhCoaDb();
    list($event, $output) = material_run('save_payment', $sourceOptions, $sourcePost);
    check(json_decode($output, true)['status'] === 1, 'Source PPh saves '.$sourceType.': '.$output);
}

class RoundingMultiResult {
    private $row, $payment;
    public function __construct($row, $payment) { $this->row = $row; $this->payment = $payment; }
    public function row() { return $this->result()[0]; }
    public function result() {
        $rows = [];
        foreach ([1, 2] as $id) {
            $row = clone $this->row;
            $row->id = 'PAY-'.$id;
            $row->no_doc = 'DOC-'.$id;
            if ($this->payment) { $row->jumlah = '500014.00'; $row->total_pph = '499.72'; }
            $rows[] = $row;
        }
        return $rows;
    }
}
class RoundingMultiDb extends MaterialDb {
    public function __call($name, $args) {
        $table = strtok($args[0] ?? $this->table, ' ');
        $result = parent::__call($name, $args);
        if (in_array($name, ['get', 'get_where'], true) && in_array($table, ['payment_approve', 'tr_expense'], true)) {
            return new RoundingMultiResult($result->row(), $table === 'payment_approve');
        }
        return $result;
    }
}
$multiPost = $post;
$multiPost['id_payment'] = 'PAY-1,PAY-2';
$multiPost['item_ppn'] = ['PAY-1' => '0', 'PAY-2' => '0'];
$multiPost['item_pph'] = ['PAY-1' => '10001.00', 'PAY-2' => '10001.00'];
$multiPost['dt'] = [];
$multiPost['pph_data'] = ['PAY-1' => '23', 'PAY-2' => '23'];
$multiPost['total_payment'] = $multiPost['payment_bank'] = '980026.00';
foreach ([1, 2] as $id) { $multiPost['dt'][] = array_replace($post['dt'][0], ['id_payment' => 'PAY-'.$id, 'nilai_pph' => '10001.00']); }
list($event, $output) = material_run('set_jurnal', $options + ['db_fixture' => new RoundingMultiDb()], $multiPost);
$multiPreview = json_decode($output, true);
check($multiPreview['preview_error'] === '' && abs($multiPreview['ttl_debit'] - $multiPreview['ttl_kredit']) < 0.001, 'Two documents round PPh individually and preview balances');
$multiPost['jurnal_ls'] = rounding_preview_rows($multiPreview);
check(count(array_filter($multiPost['jurnal_ls'], function ($row) { return !empty($row['payment_rounding']); })) === 0, 'Multi-item has no rounding journal');
list($event, $output) = material_run('save_payment', $options + ['db_fixture' => new RoundingMultiDb()], $multiPost);
check(json_decode($output, true)['status'] === 1, 'Multi-item preview saves: '.$output);
echo 'PASS PPh rounding integration: '.$scenarioCount." fixture scenarios\n";
