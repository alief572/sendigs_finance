<?php
define('MATERIAL_FIXTURE_ONLY', true);
require __DIR__ . '/Batch1_test.php';
require APPPATH . 'helpers/json_helper.php';
require_once APPPATH . 'helpers/periodik_helper.php';
set_error_handler(function ($severity, $message, $file, $line) {
    $GLOBALS['lastMaterialFixtureError'] = $message . ' at ' . basename($file) . ':' . $line;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
$modelSource = file_get_contents(dirname(__DIR__) . '/models/Pembayaran_material_model.php');
preg_match_all('/->([a-zA-Z_]\w*)\b(?!\s*\()/', $modelSource, $propertyMatches);
$GLOBALS['materialRowDefaults'] = array_fill_keys($propertyMatches[1], 0);
$GLOBALS['materialRowDefaults'] = array_merge($GLOBALS['materialRowDefaults'], array(
    'nama' => 'PRIVATE', 'nm_bank' => 'PRIVATE', 'nama_bank' => 'PRIVATE', 'rekening' => 'PRIVATE',
    'coa_bank' => '1101-01', 'nm_coa' => 'Kas', 'no_coa' => '1101-01', 'coa' => '1101-01',
    'requestor' => 'PRIVATE', 'request_by' => 'PRIVATE', 'keperluan' => 'PRIVATE', 'created_on' => '2026-10-13',
    'max_id' => '', 'kode_bank' => 'BNK', 'nocab' => '101', 'subcab' => '01', 'nobuk' => 1, 'no_payment_hutang' => 'PHP-1',
    'department_id' => 5, 'company_id' => 'COM006', 'nm_company' => 'Sustain', 'name' => 'Department',
    'currency' => 'IDR', 'tipe' => 'kasbon', 'nilai_pph' => 0, 'nilai_ppn' => 0
));
if ($child === '--material2-abort-write') { material_run('save_payment', array('abort_commit' => true)); exit; }
if ($child === '--material2-abort-read') { material_run('set_jurnal', array('abort_query' => 1)); exit; }
foreach (array('check_payment', 'clear_choosed_payment', 'used_choosed_payment') as $method) {
    foreach (array(0, 1) as $checked) {
        $selectionOptions = array('empty_selection' => $method === 'check_payment' && $checked === 1);
        list($event, $output) = material_run($method, $selectionOptions, array('checked' => $checked));
        check($event['status'] === 1 && in_array('trans_commit', $event['calls_at_write'], true), 'Selection success after commit');
        foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'fail_write') as $failure) {
            list($event) = material_run($method, $selectionOptions + array($failure => $failure === 'fail_write' ? 1 : true), array('checked' => $checked));
            check($event['status'] === 0, 'Selection failure ' . $failure);
        }
    }
}
foreach (array('proses_payment', 'get_list_req_payment', 'set_jurnal', 'set_jurnal_refill') as $method) {
    list($event, $output, $controller) = material_run($method, array('row_overrides' => array('tipe' => 'transportasi')));
    check($event['status'] === 1, $method . ': success');
    for ($i = 1; $i <= $controller->db->queryNumber; $i++) {
        list($event) = material_run($method, array('fail_query' => $i, 'row_overrides' => array('tipe' => 'transportasi')));
        check($event['status'] === 0, $method . ': missed query ' . $i);
    }
}
list($event, $output) = material_run('save_payment');
check($event['status'] === 1 && json_decode($output, true)['status'] === 1, 'Payment success');
list($event, $output, $canonicalController) = material_run('save_payment');
foreach ($canonicalController->db->writes as $write) {
    if ($write[0] === 'insert_batch' && $write[1][0] === 'tr_jurnal') foreach ($write[1][1] as $line) {
        if ($line['jenis_transaksi'] === 'Payment') check($line['no_transaksi'] === $event['context']['data']['payment_number'], 'New Payment staging uses server-generated actual voucher');
    }
    if ($write[0] === 'insert_batch' && $write[1][0] === 'jurnaltras') foreach ($write[1][1] as $line) check($line['no_reff'] === 'PAY-1', 'Automatic journal references preserve selected approval identity');
}
$journal = array('nama_account' => 'PRIVATE', 'coa' => '1101-01', 'tanggal_jurnal' => '2026-10-13', 'company' => 'STM',
    'debit' => 100, 'kredit' => 0, 'keterangan' => 'PRIVATE', 'id_company' => 7, 'nm_company' => 'STM',
    'nm_coa' => 'PRIVATE', 'id_divisi' => 5, 'nm_divisi' => 'PRIVATE');
foreach (array(false, true) as $petty) {
    foreach (array('company', 'recipient') as $bearer) {
        $balancedJournal = array(array_merge($journal,array('coa'=>'6207-00-00','debit'=>100)),
            array_merge($journal,array('coa'=>'1106-01-06','debit'=>10,'id_payment_ref'=>'PAY-1')),
            array_merge($journal,array('coa'=>'2104-01-03','debit'=>0,'kredit'=>3,'id_payment_ref'=>'PAY-1')),
            array_merge($journal,array('debit'=>0,'kredit'=>107)));
        $specialJournal = array(array_merge($journal,array('coa'=>'6207-00-00','debit'=>107)),array_merge($journal,array('debit'=>0,'kredit'=>107)));
        $post = array('admin_charge_bearer' => $bearer, 'jurnal_ls' => $balancedJournal, 'jurnal_refill_pettycash' => $specialJournal,
            'bank_charge' => 0, 'total_payment' => 107, 'payment_bank' => 107,
            'jurnal_hutang_1' => $specialJournal, 'jurnal_hutang_2' => $specialJournal,
            'dt' => array(array('id_payment' => 'PAY-1', 'tipe_pph' => 1, 'nilai_ppn' => 10, 'nilai_pph' => 3, 'kurs_invoice' => 1)));
        list($event, $output, $controller) = material_run('save_payment', array('all_petty' => $petty), $post);
        check($event['status'] === 1 && json_decode($output, true)['status'] === 1 && in_array('trans_commit', $event['calls_at_write'], true), 'Branch payment success: ' . ($GLOBALS['lastMaterialFixtureError'] ?? 'no PHP warning'));
        $refillLines = 0;
        foreach ($controller->db->writes as $write) {
            if ($write[0] === 'insert_batch' && $write[1][0] === 'tr_jurnal') foreach ($write[1][1] as $line) {
                if ($line['jenis_transaksi'] === 'Refill Pettycash') { $refillLines++; check($line['no_transaksi'] === $event['context']['data']['payment_number'] && $line['no_transaksi'] !== 'PAY-1', 'Refill Pettycash staging uses actual voucher (id_payment), not the approval id'); }
            }
        }
        check($refillLines === 2, 'Refill Pettycash staging rows are still written');
        $writes = $controller->db->writeNumber; $queries = $controller->db->queryNumber;
        for ($i = 1; $i <= $writes; $i++) {
            list($event, $output) = material_run('save_payment', array('all_petty' => $petty, 'fail_write' => $i), $post);
            check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Payment write failure ' . $i);
        }
        for ($i = 1; $i <= $queries; $i++) {
            list($event, $output) = material_run('save_payment', array('all_petty' => $petty, 'fail_query' => $i), $post);
            check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Payment query failure ' . $i);
        }
    }
}
foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'pcvs_failure') as $failure) {
    list($event, $output) = material_run('save_payment', array($failure => true));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Payment transaction/paid update failure');
}
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event, $output) = material_run('save_payment', array($writer => true));
    check($event['status'] === 1 && json_decode($output, true)['status'] === 1, 'Writer changes payment response');
}
foreach (array('bank', 'tgl_bayar', 'keterangan_pembayaran', 'payment_bank', 'total_payment', 'id_payment') as $required) {
    list($event, $output) = material_run('save_payment', array(), array($required => ''));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Required validation ' . $required);
}
list($event) = material_run('save_payment', array(), array('admin_charge_bearer' => 'bad')); check($event['status'] === 0, 'Admin validation');
list($event) = material_run('save_payment', array('missing_table' => 'payment_approve')); check($event['status'] === 0, 'Missing payment source');
foreach (array(false, true) as $multiple) {
    $file = array('name' => $multiple ? array('PRIVATE.pdf', 'PRIVATE2.pdf') : 'PRIVATE.pdf',
        'type' => $multiple ? array('application/pdf', 'application/pdf') : 'application/pdf',
        'tmp_name' => $multiple ? array('fixture', 'fixture') : 'fixture', 'error' => $multiple ? array(0, 0) : 0, 'size' => $multiple ? array(1, 1) : 1);
    foreach (array(false, true) as $failure) {
        list($event, $output) = material_run('save_payment', array('files' => array('upload_doc' => $file), 'upload_failure' => $failure));
        check(json_decode($output, true)['status'] === 1 && $event['status'] === ($failure ? 0 : 1), 'Upload response/status compatibility');
        if ($failure) { check($event['context']['data']['outcome'] === 'partial_upload_failure', 'Partial upload outcome'); }
    }
}
foreach (array('kasbon', 'transportasi', 'transport', 'expense', 'direct_payment', 'petty_cash_hutang', 'refill_pettycash', 'periodik') as $type) {
    foreach (array('DOC-1', 'PHP-1', 'RPC-1') as $document) {
        $options = array('row_overrides' => array('tipe' => $type, 'no_doc' => $document, 'title_id' => 'TIT009', 'company' => 'STM'));
        list($event, $output, $controller) = material_run('set_jurnal', $options);
        check($event['status'] === 1, 'Preview ' . $type . '/' . $document);
        for ($i = 1; $i <= $controller->db->queryNumber; $i++) {
            list($event) = material_run('set_jurnal', $options + array('fail_query' => $i));
            check($event['status'] === 0, 'Preview first query error ' . $i);
        }
    }
}
foreach (array('get_list_req_payment', 'set_jurnal', 'set_jurnal_refill') as $method) {
    list($event) = material_run($method, array('empty' => true));
    check($event['status'] === ($method === 'get_list_req_payment' ? 1 : 0), 'Empty list vs missing preview');
}
$branchOptions = array(
    array('row_overrides' => array('tipe' => 'expense', 'exp_inv_po' => 1), 'table_overrides' => array('tr_purchase_order' => array('tipe' => 'pr depart'))),
    array('row_overrides' => array('tipe' => 'expense', 'no_expense_consultant' => 'EXP-KNS-1')),
    array('row_overrides' => array('tipe' => 'direct_payment', 'id_penawaran' => 'PEN-1')),
    array('row_overrides' => array('tipe' => 'transportasi', 'title_id' => 'TIT009', 'no_doc' => 'RPC-1'))
);
foreach (range(1, 5) as $consultantType) {
    $branchOptions[] = array('row_overrides' => array('tipe' => 'kasbon', 'no_kasbon_consultant' => 'KNS-1'),
        'table_overrides' => array('kons_tr_kasbon_project_header' => array('tipe' => (string) $consultantType)));
}
foreach ($branchOptions as $options) {
    list($event, $output, $controller) = material_run('set_jurnal', $options);
    check($event['status'] === 1, 'Consultant/expense/title preview branch');
    for ($i = 1; $i <= $controller->db->queryNumber; $i++) {
        list($event) = material_run('set_jurnal', $options + array('fail_query' => $i));
        check($event['status'] === 0, 'Branch query failure ' . $i);
    }
}
list($event, $output, $controller) = material_run('proses_payment', array('no_admin_rows' => true));
check($event['status'] === 1 && $controller->db->queryNumber >= 4, 'Fallback admin lookup');
foreach (range(1, $controller->db->queryNumber) as $i) {
    list($event) = material_run('proses_payment', array('no_admin_rows' => true, 'fail_query' => $i)); check($event['status'] === 0, 'Fallback admin query error');
}
list($event) = material_run('set_jurnal_refill', array('row_overrides' => array('tipe' => 'transportasi', 'title_id' => 'TIT009')));
check($event['status'] === 1, 'Transport refill');
list($event) = material_run('get_list_req_payment', array(), array('jenis_payment' => 2, 'order' => array(array('column' => '3', 'dir' => 'DESC'))));
check($event['context']['data']['jenis_payment'] === 2 && $event['context']['data']['sort_column'] === 3 && $event['context']['data']['sort_direction'] === 'desc', 'Backend navigation metadata');
list($event) = material_run('check_payment', array(), array('id' => '')); check($event['status'] === 0, 'Empty selection ID');
foreach (array('kasbon' => 'tr_kasbon', 'expense' => 'tr_expense', 'direct_payment' => 'tr_direct_payment', 'periodik' => 'tr_pengajuan_rutin') as $type => $table) {
    list($event) = material_run('set_jurnal', array('row_overrides' => array('tipe' => $type), 'missing_table' => $table, 'expected_exception' => true));
    check($event['status'] === 0, 'Missing source preview ' . $type);
}
foreach (array('proses_payment', 'get_list_req_payment', 'set_jurnal', 'set_jurnal_refill') as $method) {
    list($event) = material_run($method, array('fail_query' => 1, 'query_returns_false' => true, 'expected_exception' => true));
    check($event['status'] === 0, 'Query false ' . $method);
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($event, $output) = material_run($method, array($writer => true));
        check($event['status'] === 1, 'Read writer failure ' . $method);
    }
}
foreach (array('--material2-abort-write', '--material2-abort-read') as $argument) {
    $lines = array(); $exitCode = 0;
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . escapeshellarg($argument), $lines, $exitCode);
    $audits = array_values(array_filter($lines, function ($line) { return strpos($line, 'AUDIT:') === 0; }));
    check($exitCode === 0 && count($audits) === 1, 'One shutdown event');
    $event = json_decode(substr($audits[0], 6), true);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', 'Shutdown outcome');
}
echo 'PASS Material batch 2: ' . $scenarioCount . " fixture scenarios\n";
