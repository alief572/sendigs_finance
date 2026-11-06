<?php
define('AUDIT_FIXTURE_ONLY', true);
define('PENGAJUAN_FIXTURE_ONLY', true);
require __DIR__ . '/Batch1_audit_test.php';

$child = $argv[1] ?? '';
if ($child === '--pengajuan2-abort') {
    pengajuan_run('save_decision', array('abort_query' => 1), array('id' => '1', 'decision' => array(1 => 'A')));
    exit;
}

$b2ScenarioCount = 0;

function b2_writes($ctrl, $op, $table) {
    return array_values(array_filter($ctrl->db->writes, function ($w) use ($op, $table) { return $w[0] === $op && ($w[1][0] ?? '') === $table; }));
}
function b2_run() {
    global $b2ScenarioCount;
    $b2ScenarioCount++;
    return call_user_func_array('pengajuan_run', func_get_args());
}

// ==========================================
// 1. close_item (Tidak diajukan ulang)
// ==========================================
$closePost = array('no_item' => 'PI-2026-00001', 'note' => 'Tagihan batal');

// A. Alasan wajib diisi
list($event, $output, $ctrl) = b2_run('close_item', array('is_admin' => true), array('no_item' => 'PI-2026-00001', 'note' => '   '));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'empty_note', 'close_item: blank reason rejected');
$decoded = json_decode($output, true);
check($decoded['save'] === 0 && $decoded['msg'] === 'Alasan wajib diisi untuk menutup item.', 'close_item: reason message');
check(!b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail'), 'close_item: nothing written without reason');

// B. Nomor item tidak valid
list($event) = b2_run('close_item', array('is_admin' => true), array('no_item' => 'PERIODIK-2026-00001', 'note' => 'x'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_found', 'close_item: old-style number is not an item');

// C. Departemen lain
list($event) = b2_run('close_item', array('is_admin' => false, 'dept_mismatch' => true), $closePost);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'unauthorized', 'close_item: other department rejected');

// D. Hanya item ditolak yang belum diajukan ulang / belum ditutup
foreach (array(array('db_item_status' => 0), array('db_item_status' => 1), array('resub' => true), array('closed' => true)) as $notClosable) {
    list($event, , $ctrl) = b2_run('close_item', array_merge(array('is_admin' => true), $notClosable), $closePost);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_closable' && !b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail'), 'close_item: not closable ' . json_encode($notClosable));
}

// E. Berhasil: alasan, penutup, dan waktu tercatat; status item tetap ditolak (8)
list($event, $output, $ctrl) = b2_run('close_item', array('is_admin' => true), $closePost);
$decoded = json_decode($output, true);
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'closed' && $decoded['save'] === 1 && $decoded['no_item'] === 'PI-2026-00001', 'close_item: closed');
$upd = b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail')[0][1];
check($upd[1]['closed_note'] === 'Tagihan batal' && $upd[1]['closed_by'] === 'tester' && !empty($upd[1]['closed_at']) && !isset($upd[1]['item_status']) && $upd[2] === array('no_item' => 'PI-2026-00001'), 'close_item: reason, closer, time recorded and item_status untouched');

// F. Gagal menulis
list($event) = b2_run('close_item', array('is_admin' => true, 'update_false' => true), $closePost);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed', 'close_item: update failure');
list($event) = b2_run('close_item', array('is_admin' => true, 'save_exception' => true), $closePost);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed', 'close_item: write exception');

// G. Writer failure immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event, $output) = b2_run('close_item', array('is_admin' => true, $writer => true), $closePost);
    check(json_decode($output, true)['save'] === 1, 'close_item: save 1 unaffected by writer failure');
}

// ==========================================
// 2. app_list
// ==========================================
list($event, , $ctrl) = b2_run('app_list', array('is_admin' => true));
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'rendered' && $ctrl->template->view === 'app_list', 'app_list: rendered');
check(isset($ctrl->template->data['batches']['BP-2611-001']) && $ctrl->template->data['can_decide'] === true, 'app_list: batches and decide flag passed to view');
list($event) = b2_run('app_list', array('query_error_detail' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'query_error', 'app_list: query error');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event) = b2_run('app_list', array($writer => true));
    check($event['status'] === 1, 'app_list: writer resilience');
}

// ==========================================
// 3. proses (form keputusan)
// ==========================================
list($event) = b2_run('proses', array('missing_record' => true), array(), array('999'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'pengajuan_rutin/app_list', 'proses: unknown batch');
list($event) = b2_run('proses', array('item_letter' => 'A'), array(), array('1'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'already_decided', 'proses: decided batch cannot be reopened');
list($event, , $ctrl) = b2_run('proses', array(), array(), array('1'));
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'rendered' && $ctrl->template->view === 'approval_form' && $ctrl->template->data['batch']['no'] === 'BP-2611-001', 'proses: rendered');
list($event, , $ctrl) = b2_run('proses', array('item_ref' => 'PI-2026-00000'), array(), array('1'));
check(isset($ctrl->template->data['chain']['PI-2026-00001']), 'proses: revision history passed to view');
list($event) = b2_run('proses', array('query_error_detail' => true), array(), array('1'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'query_error', 'proses: query error');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event) = b2_run('proses', array($writer => true), array(), array('1'));
    check($event['status'] === 1, 'proses: writer resilience');
}

// ==========================================
// 4. save_decision
// ==========================================
$A = array('id' => '1', 'decision' => array(1 => 'A'), 'note' => array(), 'general_note' => '');
$R = array('id' => '1', 'decision' => array(1 => 'R'), 'note' => array(1 => 'Nominal salah'), 'general_note' => '');

// A. Batch tidak ada / sudah diputuskan
list($event, $output) = b2_run('save_decision', array('missing_record' => true), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_found' && json_decode($output, true)['save'] === 0, 'save_decision: unknown batch');
list($event, $output, $ctrl) = b2_run('save_decision', array('item_letter' => 'A'), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'already_decided' && !b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail'), 'save_decision: decision is final');

// B. Reject tanpa alasan (per item maupun umum)
list($event, $output, $ctrl) = b2_run('save_decision', array(), array('id' => '1', 'decision' => array(1 => 'R'), 'note' => array(1 => ' '), 'general_note' => ''));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'missing_reason' && !$ctrl->db->writes, 'save_decision: reject without reason blocked, nothing written');
list($event, $output, $ctrl) = b2_run('save_decision', array(), array('id' => '1', 'decision' => array(1 => 'R'), 'note' => array(), 'general_note' => 'Dokumen tidak lengkap'));
check($event['status'] === 1, 'save_decision: general reason covers an item without its own reason');
$upd = b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail')[0][1][1];
check($upd['item_status'] === 8 && $upd['item_reject_ket'] === 'Dokumen tidak lengkap', 'save_decision: general reason stored on the item');

// C. Semua disetujui
list($event, $output, $ctrl) = b2_run('save_decision', array(), $A);
$decoded = json_decode($output, true);
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'decided' && $event['context']['data']['approved_count'] === 1 && $event['context']['data']['rejected_count'] === 0, 'save_decision (approve): audited');
check($decoded['save'] === 1 && $decoded['status'] === 'Approved' && $decoded['approved'] === 1 && $ctrl->db->committed === true, 'save_decision (approve): response and commit');
$upd = b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail')[0][1];
check($upd[1]['item_status'] === 1 && $upd[1]['item_approved_by'] === 'tester' && !empty($upd[1]['item_approved_date']) && $upd[1]['item_reject_ket'] === null, 'save_decision (approve): item approved with approver and time');
check($upd[2] === array('id' => 1, 'item_status' => 0), 'save_decision (approve): update guarded by still-waiting status');
check(!isset($upd[1]['no_item']), 'save_decision (approve): item number untouched for new-style item');
$hdr = b2_writes($ctrl, 'update', 'tr_pengajuan_rutin')[0][1];
check($hdr[1]['status'] === 1 && $hdr[1]['sts_reject'] === null && $hdr[1]['approved_by'] === '7' && $hdr[2] === array('no_doc' => 'BP-2611-001'), 'save_decision (approve): header moves to approved, approver stored as user id like existing rows');

// D. Semua ditolak: header ditandai ditolak, tidak disetujui
list($event, $output, $ctrl) = b2_run('save_decision', array(), $R);
$decoded = json_decode($output, true);
check($event['status'] === 1 && $decoded['status'] === 'Rejected' && $decoded['rejected'] === 1 && $decoded['approved'] === 0, 'save_decision (reject): batch becomes Rejected');
$hdr = b2_writes($ctrl, 'update', 'tr_pengajuan_rutin')[0][1];
check($hdr[1]['status'] === 0 && $hdr[1]['sts_reject'] === 1 && $hdr[1]['reject_by'] === '7', 'save_decision (reject): header marked rejected so dashboard counter drops it');

// E. Sebagian: status batch Partially Approved, alasan per item
list($event, $output, $ctrl) = b2_run('save_decision', array('two_items' => true), array(
    'id' => '1', 'decision' => array(1 => 'A', 2 => 'R'),
    'note' => array(2 => 'Biaya terlalu tinggi'), 'general_note' => ''));
$decoded = json_decode($output, true);
check($event['status'] === 1 && $decoded['status'] === 'Partially Approved' && $decoded['approved'] === 1 && $decoded['rejected'] === 1, 'save_decision (partial): Partially Approved');
$ups = b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail');
check(count($ups) === 2 && $ups[0][1][1]['item_status'] === 1 && $ups[1][1][1]['item_status'] === 8 && $ups[1][1][1]['item_reject_ket'] === 'Biaya terlalu tinggi', 'save_decision (partial): each item gets its own decision');
check(b2_writes($ctrl, 'update', 'tr_pengajuan_rutin')[0][1][1]['status'] === 1, 'save_decision (partial): header approved when at least one item approved');

// F. Item tanpa keputusan di POST default disetujui
list($event, $output) = b2_run('save_decision', array(), array('id' => '1', 'decision' => array(), 'note' => array(), 'general_note' => ''));
check($event['status'] === 1 && json_decode($output, true)['status'] === 'Approved', 'save_decision: missing decision defaults to approve');

// G. Batch lama: nomor item dibuat saat diputuskan
list($event, , $ctrl) = b2_run('save_decision', array('legacy_item' => true), array('id' => '1', 'decision' => array(1 => 'A'), 'note' => array(), 'general_note' => ''));
$upd = b2_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail')[0][1][1];
check($event['status'] === 1 && preg_match('/^PI-\d{4}-00001$/', $upd['no_item']) === 1, 'save_decision (legacy batch): item number assigned at decision');

// H. Kegagalan penulisan dibatalkan seluruhnya
list($event, $output, $ctrl) = b2_run('save_decision', array('trans_begin_failure' => true), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'trans_begin_failure', 'save_decision: trans_begin failure');
list($event, $output, $ctrl) = b2_run('save_decision', array('affected_zero' => true), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed' && !$ctrl->db->committed && !b2_writes($ctrl, 'update', 'tr_pengajuan_rutin'), 'save_decision: item already decided elsewhere (0 rows) aborts the whole save');
list($event, , $ctrl) = b2_run('save_decision', array('update_false' => true), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed' && !$ctrl->db->committed, 'save_decision: update failure rolls back');
list($event, , $ctrl) = b2_run('save_decision', array('save_exception' => true), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'exception' && !$ctrl->db->committed, 'save_decision: exception rolls back');
list($event, , $ctrl) = b2_run('save_decision', array('transaction_failure' => true), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed' && !$ctrl->db->committed, 'save_decision: transaction status failure rolls back');
list($event, , $ctrl) = b2_run('save_decision', array('trans_commit_failure' => true), $A);
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'commit_failed', 'save_decision: commit failure');

// I. Writer failure immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event, $output) = b2_run('save_decision', array($writer => true), $A);
    check(json_decode($output, true)['save'] === 1, 'save_decision: business save unaffected by writer failure');
}

// ==========================================
// 5. Subprocess Shutdown Test
// ==========================================
$lines = array();
$exitCode = 0;
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --pengajuan2-abort', $lines, $exitCode);
$auditLines = array_values(array_filter($lines, function ($line) { return strpos($line, 'AUDIT:') === 0; }));
check($exitCode === 0, 'Subprocess exit code 0');
check(count($auditLines) === 1, 'Subprocess exactly 1 audit event on query abort');
$shutdownEvent = json_decode(substr($auditLines[0], 6), true);
check($shutdownEvent['context']['methode'] === 'save_decision', 'Shutdown event method attribution');
check($shutdownEvent['status'] === 0, 'Shutdown event status 0');
check($shutdownEvent['context']['data']['outcome'] === 'interrupted', 'Shutdown event outcome interrupted');

echo "PASS Pengajuan Rutin batch 2: {$b2ScenarioCount} fixture scenarios + 1 shutdown subprocess\n";
