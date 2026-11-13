<?php
// Fase 5 / penerimaan_uang batch 2 (8 endpoint, jalur tulis invoice lama + pembaca): controller asli + recorder asli.
// Fixture dipakai ulang dari Batch1_audit_test.php. Jalur sukses konsultasi v3 (Consulting_receipt::save/rollback) tidak dijalankan penuh di sini
// (butuh MariaDB: Consulting_integration_test.php); yang diuji: penolakan, dan invarian satu event untuk Invoice_consulting_service::mutate() asli.
define('AUDIT_FIXTURE_ONLY', true);
require __DIR__ . '/Batch1_audit_test.php';

if ($child === '--exit' || $child === '--denied') {
    pu_make(json_decode(base64_decode($argv[3]), true) ?: array(), json_decode(base64_decode($argv[4]), true) ?: array());
    register_shutdown_function(function () { global $ci; echo 'CALLS:' . json_encode($ci->db->calls) . "\n"; });
    $m = $argv[2]; $ci->$m(); exit;
}

function pb_child($mode, $method, $options, $post = array()) {
    global $scenarioCount; $scenarioCount++;
    $raw = (string) shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" ' . $mode . ' ' . $method . ' ' . base64_encode(json_encode($options)) . ' ' . base64_encode(json_encode($post)) . ' 2>&1');
    check(substr_count($raw, 'AUDIT:') === 1, $method . " ($mode): tepat satu event, output: " . $raw);
    $line = strtok(substr($raw, strpos($raw, 'AUDIT:') + 6), "\n");
    return array(json_decode(trim($line), true), $raw);
}
function pb_json($out) { $d = json_decode($out, true); check(is_array($d), 'keluaran bukan JSON: ' . $out); return $d; }

$savePost = array('nm_customer' => 'Customer', 'id_alokasi' => 5102, 'ppn_dipotong' => 'Y', 'pph23_dipotong' => 'N', 'nominal_penerimaan_bank' => '2.574.990,00',
    'no_inv' => 1, 'id_inv_1' => '189', 'penerimaan_1' => '2.574.990,00', 'biaya_admin_1' => '0', 'sisa_piutang_1' => '0');
$G = array('real_get' => true);

// =========================================================================== save_penerimaan_piutang (invoice lama)
list($e, $out, $c) = pu_run('save_penerimaan_piutang', $G, $savePost);
check($e['status'] === 1 && pd($e)['outcome'] === 'committed' && pd($e)['mode'] === 'legacy' && pd($e)['invoice_count'] === 1 && pd($e)['no_surat'] === 'NEW' && pb_json($out)['status'] === 1, 'save: sukses');
check(in_array('trans_commit', $e['calls_at_write'], true), 'save: audit sukses sesudah commit');
check(isset($c->db->batches['tr_jurnal']) && isset($c->db->batches['tr_penerimaan_piutang_detail']), 'save: payload bisnis tetap ditulis');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = pu_run('save_penerimaan_piutang', $G + array($w => true), $savePost);
    check($e['status'] === 1 && pb_json($out)['status'] === 1, "save: bisnis utuh saat $w");
}
list($e, $out, $c) = pu_run('save_penerimaan_piutang', $G + array('deny' => true), $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied' && pb_json($out)['status'] === 0 && empty($c->db->batches), 'save: denied, tidak menulis');
foreach (array(0, 101, 'x') as $bad) {
    list($e, $out) = pu_run('save_penerimaan_piutang', $G, array('no_inv' => $bad) + $savePost);
    check($e['status'] === 0 && pd($e)['reason'] === 'invalid_invoice_count' && pb_json($out)['status'] === 0, "save: jumlah invoice tidak valid ($bad)");
}
list($e, $out) = pu_run('save_penerimaan_piutang', $G + array('query_rows' => array(array('id' => 1, 'id_alokasi_detail' => 5))), $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'allocation_guard' && pb_json($out)['status'] === 0, 'save: guard alokasi konsultasi menolak');
list($e, $out, $c) = pu_run('save_penerimaan_piutang', $G + array('lock_fail' => true), $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'serialized_failed' && pb_json($out)['status'] === 0 && empty($c->db->batches), 'save: lock gagal -> gagal, tidak menulis');
list($e, $out) = pu_run('save_penerimaan_piutang', $G, array('penerimaan_1' => '2.574.991,00') + $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_input' && pb_json($out)['status'] === 0, 'save: validasi nominal ditolak');
list($e, $out) = pu_run('save_penerimaan_piutang', $G, array('deposit_choice' => 'Y', 'deposit_amount' => '0') + $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_deposit_amount', 'save: nominal deposit nol');
list($e, $out, $c) = pu_run('save_penerimaan_piutang', $G + array('status_fail' => true), $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'transaction_failed' && pb_json($out)['status'] === 0 && in_array('trans_rollback', $c->db->calls, true) && !in_array('trans_commit', $c->db->calls, true), 'save: trans_status false -> rollback, gagal');
list($e, $out, $c) = pu_run('save_penerimaan_piutang', $G + array('commit_fail' => true), $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'commit_failed' && pb_json($out)['status'] === 0 && in_array('trans_rollback', $c->db->calls, true), 'save: commit gagal TIDAK boleh sukses');
list($e, $out) = pu_run('save_penerimaan_piutang', $G + array('invoice_patch' => array('consulting_version' => 3)), $savePost);
check($e['status'] === 0 && pd($e)['mode'] === 'consulting' && pd($e)['reason'] === 'save_rejected' && pb_json($out)['status'] === 0, 'save: jalur konsultasi ditolak -> tercatat gagal, satu event');
// jalur exit lama (print_r + exit): hasil dicatat sebelum keluar, keluaran lama utuh.
foreach (array('tr_penerimaan_piutang' => 'insert_header_failed', 'tr_penerimaan_piutang_detail' => 'insert_detail_failed', 'tr_jurnal' => 'insert_journal_failed',
    'tr_invoicing' => 'update_invoice_failed', 'tr_alokasi_detail' => 'update_allocation_failed', 'tr_alokasi_split' => 'update_split_failed') as $table => $reason) {
    list($e, $raw) = pb_child('--exit', 'save_penerimaan_piutang', $G + array('fail_' . $table => true), $savePost);
    check($e['status'] === 0 && $e['context']['data']['reason'] === $reason && in_array('trans_rollback', $e['calls_at_write'], true) && strpos($raw, 'SQL') > strpos($raw, 'AUDIT:'), "save exit: $reason");
    check(strpos($raw, 'PRIVATE') === false, "save exit: $reason tanpa data privat");
}
// Lebih Bayar / deposit invoice lama
$dep = array('deposit' => true, 'legacy_schema' => true, 'company_id' => '7', 'strict_sql' => true, 'invoice_patch' => array('id_customer' => 'C-ID', 'nm_customer' => 'Customer'));
$lebihPost = array('penerimaan_1' => '2,574,990.00', 'lebih_bayar_1' => '12,425,010.00', 'nominal_penerimaan_bank' => '999') + $savePost;
list($e, $out, $c) = pu_run('save_penerimaan_piutang', $G + $dep, $lebihPost);
check($e['status'] === 1 && pd($e)['mode'] === 'legacy' && pd($e)['lebih_used'] === true && pd($e)['deposit_used'] === false && pb_json($out)['status'] === 1, 'save: Lebih Bayar sukses');
$noHistory = array('details' => array());
list($e, $out) = pu_run('save_penerimaan_piutang', $G + $dep + $noHistory, array('deposit_choice' => 'Y', 'deposit_amount' => '1,000.00', 'dep_1' => '500.00', 'lebih_bayar_1' => '', 'penerimaan_1' => '3,043,170.00') + $lebihPost);
check($e['status'] === 0 && pd($e)['reason'] === 'deposit_total_mismatch' && pb_json($out)['status'] === 0, 'save: total deposit tidak cocok');
list($e, $out) = pu_run('save_penerimaan_piutang', $G + $dep + $noHistory, array('deposit_choice' => 'Y', 'deposit_amount' => '500.00', 'dep_1' => '500.00', 'penerimaan_1' => '3,043,170.00') + $lebihPost);
check($e['status'] === 0 && pd($e)['reason'] === 'deposit_with_lebih', 'save: deposit dan Lebih Bayar bersamaan');

// =========================================================================== save_kwitansi_deposit
list($e, $out) = pu_run('save_kwitansi_deposit', $G + array('deny' => true), $savePost);
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied' && pb_json($out)['status'] === 0, 'kwitansi: denied');
list($e, $out) = pu_run('save_kwitansi_deposit', $G, $savePost);
check($e['status'] === 0 && pd($e)['receipt_kind'] === 'deposit' && pd($e)['reason'] === 'save_rejected' && pd($e)['outcome'] === 'rejected' && pb_json($out)['status'] === 0, 'kwitansi: ditolak -> gagal, satu event');
list($e, $out) = pu_run('save_kwitansi_deposit', $G + array('writer_exception' => true), $savePost);
check(pb_json($out)['status'] === 0, 'kwitansi: writer exception tidak mengubah respons');

// =========================================================================== rollback_penerimaan (invoice lama)
$rb = $G + array('no_journals' => true, 'details' => array(array('id' => 1, 'id_header' => 'NEW', 'id_inv' => '189', 'penerimaan' => '2574990.00', 'biaya_admin' => '0.00', 'pph23' => '0.00', 'consulting_snapshot' => '')));
$rbPost = array('id_penerimaan' => 'H1');
list($e, $out, $c) = pu_run('rollback_penerimaan', $rb, $rbPost);
check($e['status'] === 1 && pd($e)['outcome'] === 'rolled_back' && pd($e)['mode'] === 'legacy' && pd($e)['id_penerimaan'] === 'H1' && pb_json($out)['status'] === 1, 'rollback: sukses');
check(in_array('trans_commit', $e['calls_at_write'], true) && !empty($c->db->deletes['tr_penerimaan_piutang']), 'rollback: audit sesudah commit, payload bisnis tetap');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = pu_run('rollback_penerimaan', $rb + array($w => true), $rbPost);
    check($e['status'] === 1 && pb_json($out)['status'] === 1, "rollback: bisnis utuh saat $w");
}
list($e, $out) = pu_run('rollback_penerimaan', $rb, array('id_penerimaan' => array('x')));
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_id' && pb_json($out)['status'] === 0, 'rollback: id bukan scalar');
list($e, $out) = pu_run('rollback_penerimaan', $rb, array('id_penerimaan' => ''));
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_id', 'rollback: id kosong');
list($e, $out) = pu_run('rollback_penerimaan', $rb + array('rows' => array('tr_penerimaan_piutang' => array())), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'not_found' && pb_json($out)['status'] === 0, 'rollback: header tidak ada');
list($e, $out, $c) = pu_run('rollback_penerimaan', $rb + array('rows' => array('tr_jurnal' => array(array('no_transaksi' => 'NEW', 'sts' => '1', 'jenis_transaksi' => 'Penerimaan Piutang')))), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'journal_posted' && pb_json($out)['status'] === 0 && empty($c->db->deletes), 'rollback: jurnal sudah diposting ditolak, tidak menghapus');
list($e, $out) = pu_run('rollback_penerimaan', $rb + array('query_rows' => array(array('id' => 1, 'id_alokasi_detail' => 5)), 'query_match' => 'tr_alokasi_split'), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'allocation_guard', 'rollback: guard alokasi konsultasi');
list($e, $out) = pu_run('rollback_penerimaan', $rb + array('query_rows' => array(array('id' => 1)), 'query_match' => 'consulting_reduction'), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'later_receipt_exists', 'rollback: ada penerimaan lebih baru');
list($e, $out, $c) = pu_run('rollback_penerimaan', $rb + array('lock_fail' => true), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'serialized_failed' && pb_json($out)['status'] === 0 && empty($c->db->deletes), 'rollback: lock gagal');
list($e, $out) = pu_run('rollback_penerimaan', $rb + array('deposit' => true, 'legacy_schema' => true, 'strict_sql' => true, 'voucher' => array('id' => 9, 'status' => 'TERPAKAI', 'nominal_terpakai' => '5.00'), 'voucher_mutations' => array(array('jenis_mutasi' => 'IN'))), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'deposit_used' && pb_json($out)['status'] === 0, 'rollback: voucher lebih bayar sudah dipakai');
list($e, $out, $c) = pu_run('rollback_penerimaan', $rb + array('deposit' => true, 'voucher' => null, 'voucher_mutations' => array(array('jenis_mutasi' => 'OUT')), 'fail_tr_customer_deposit' => true,
    'query_match' => 'tr_customer_deposit', 'query_rows' => array(array('id' => 1, 'id_deposit' => 1, 'nominal' => '100.00', 'sisa_saldo' => '0.00', 'nominal_terpakai' => '100.00', 'nominal_awal' => '100.00'))), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'deposit_restore_failed' && pb_json($out)['status'] === 0 && in_array('trans_rollback', $c->db->calls, true) && empty($c->db->deletes), 'rollback: pengembalian deposit gagal -> rollback, tidak menghapus');
list($e, $out, $c) = pu_run('rollback_penerimaan', $rb + array('status_fail' => true), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'transaction_failed' && pb_json($out)['status'] === 0 && !in_array('trans_commit', $c->db->calls, true), 'rollback: trans_status false');
list($e, $out, $c) = pu_run('rollback_penerimaan', $rb + array('commit_fail' => true), $rbPost);
check($e['status'] === 0 && pd($e)['reason'] === 'commit_failed' && pb_json($out)['status'] === 0, 'rollback: commit gagal TIDAK boleh sukses');
list($e, $out) = pu_run('rollback_penerimaan', $rb + array('rows' => array('tr_penerimaan_piutang' => array(array('id' => 'H1', 'no_surat' => 'NEW', 'id_alokasi' => 5102, 'consulting_batch' => 3)))), $rbPost);
check($e['status'] === 0 && pd($e)['mode'] === 'consulting' && pd($e)['reason'] === 'rollback_rejected' && pb_json($out)['status'] === 0, 'rollback: jalur konsultasi ditolak -> tercatat gagal');
list($e, $raw) = pb_child('--denied', 'rollback_penerimaan', $rb + array('deny' => true), $rbPost);
check($e['status'] === 0 && $e['context']['data']['reason'] === 'permission_denied', 'rollback: denied sebelum restrict()');

// =========================================================================== mutate() asli: tidak boleh menulis event kedua
// Controller membuka scope lebih dulu, jadi begin() di mutate() ditolak (owns=false) dan hanya complete() controller yang menulis.
foreach (array('commit' => array(), 'commit_fail' => array('commit_fail' => true)) as $label => $opts) {
    pu_make(array('deposit' => true) + $opts, array());
    $GLOBALS['scenarioCount']++;
    check($ci->module_audit_recorder->begin('penerimaan_uang', 'save_penerimaan_piutang', array(), array('outcome', 'reason', 'no_surat')), "mutate $label: scope controller terbuka");
    $svc = new Invoice_consulting_service($ci);
    $result = $svc->mutate('save_consulting_receipt', function () { return array('no_surat' => 'NEW'); });
    check(count($GLOBALS['events']) === 0, "mutate $label: mutate() tidak menulis event sendiri saat scope sudah dibuka controller");
    check($result['status'] === ($label === 'commit' ? 1 : 0), "mutate $label: status mengikuti nilai balik commit");
    $ci->module_audit_recorder->complete('x', $result['status'], array('data' => array('outcome' => $result['status'] ? 'committed' : 'rejected')));
    check(count($GLOBALS['events']) === 1 && $GLOBALS['events'][0]['context']['module'] === 'penerimaan_uang' && $GLOBALS['events'][0]['status'] === $result['status'], "mutate $label: tepat satu event atas nama controller");
}

// =========================================================================== detail
$header = array(array('id' => 'H1', 'no_surat' => 'NEW', 'id_alokasi' => 5102, 'pph23_dipotong' => 'N', 'consulting_batch' => 0));
list($e, $out) = pu_run('detail', array(), array('id_alokasi' => 'H1'));
check($e['status'] === 1 && pd($e)['outcome'] === 'rendered' && pd($e)['id_penerimaan'] === 'H1' && strpos($out, 'rendered detail') !== false, 'detail: rendered');
list($e, $out) = pu_run('detail', array('rows' => array('tr_penerimaan_piutang' => array())), array('id_alokasi' => 'H1'));
check($e['status'] === 0 && pd($e)['outcome'] === 'not_found' && strpos($out, 'Penerimaan tidak ditemukan') !== false, 'detail: tidak ditemukan');
list($e, $out) = pu_run('detail', array('deny' => true), array('id_alokasi' => 'H1'));
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied' && strpos($out, 'Tidak memiliki izin') !== false, 'detail: denied');
list($e) = pu_run('detail', array('model_exception' => true), array('id_alokasi' => 'H1'));
check($e['status'] === 0 && pd($e)['outcome'] === 'interrupted', 'detail: abort -> interrupted');

// =========================================================================== view
list($e, $out) = pu_run('view', array(), array(), array('H1'));
check($e['status'] === 1 && pd($e)['outcome'] === 'rendered' && strpos($out, 'rendered detail_page') !== false, 'view: rendered');
list($e, $out) = pu_run('view', array('rows' => array('tr_penerimaan_piutang' => array())), array(), array('H1'));
check($e['status'] === 0 && pd($e)['outcome'] === 'not_found' && strpos($out, 'REDIRECT:penerimaan_uang') !== false, 'view: tidak ditemukan -> redirect, tercatat sebelum redirect');
list($e, $raw) = pb_child('--denied', 'view', array('deny' => true));
check($e['status'] === 0 && $e['context']['data']['reason'] === 'permission_denied', 'view: denied');

// =========================================================================== view_surat
list($e, $out) = pu_run('view_surat', array('rows' => array('tr_penerimaan_piutang' => array(array('id' => 7, 'no_surat' => 'X')))), array('get_no' => '00001/RKN/X/26'));
check($e['status'] === 1 && pd($e)['outcome'] === 'redirected' && strpos($out, 'REDIRECT:penerimaan_uang/view/7') !== false && strpos(json_encode($e), '00001/RKN') === false, 'view_surat: ditemukan, nomor surat tidak dicatat');
list($e, $out) = pu_run('view_surat', array('rows' => array('tr_penerimaan_piutang' => array())), array('get_no' => 'NOPE'));
check($e['status'] === 0 && pd($e)['outcome'] === 'not_found' && strpos($out, 'REDIRECT:penerimaan_uang#tab_report') !== false, 'view_surat: tidak ditemukan');
list($e, $out) = pu_run('view_surat');
check($e['status'] === 0 && pd($e)['outcome'] === 'not_found', 'view_surat: tanpa nomor');

// =========================================================================== draft_rows
list($e, $out) = pu_run('draft_rows', array('draft_rows' => array(array('x' => 1), array('x' => 2))), array('receipt_kind' => 'deposit', 'bank' => 'PRIVATE_BANK'));
check($e['status'] === 1 && pd($e)['row_count'] === 2 && pd($e)['receipt_kind'] === 'deposit' && pd($e)['outcome'] === 'listed' && pb_json($out)['recordsTotal'] === 2, 'draft_rows: 2 baris');
list($e) = pu_run('draft_rows');
check($e['status'] === 1 && pd($e)['row_count'] === 0 && pd($e)['receipt_kind'] === 'invoice', 'draft_rows: kosong');
list($e, $out) = pu_run('draft_rows', array('deny' => true));
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied' && pb_json($out)['status'] === 0, 'draft_rows: denied');
list($e) = pu_run('draft_rows', array('model_exception' => true));
check($e['status'] === 0 && pd($e)['outcome'] === 'interrupted', 'draft_rows: abort');

// =========================================================================== get_alokasi_penerimaan
list($e, $out) = pu_run('get_alokasi_penerimaan');
check($e['status'] === 1 && pd($e)['outcome'] === 'listed' && pb_json($out)['data'] === array(), 'get_alokasi_penerimaan: listed');
list($e, $out) = pu_run('get_alokasi_penerimaan', array('deny' => true));
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied' && pb_json($out)['status'] === 0, 'get_alokasi_penerimaan: denied');
list($e) = pu_run('get_alokasi_penerimaan', array('model_exception' => true));
check($e['status'] === 0 && pd($e)['outcome'] === 'interrupted', 'get_alokasi_penerimaan: abort');

echo 'PASS Penerimaan Uang batch 2: ' . $scenarioCount . " skenario fixture/subprocess, controller+recorder asli\n";
