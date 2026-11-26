<?php
$checks = 0;
function requestCheck($ok, $message) {
    global $checks; $checks++;
    if (!$ok) { throw new RuntimeException($message); }
}
$root = sys_get_temp_dir() . '/expense-requests-' . uniqid();
mkdir($root);
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
$address = stream_socket_get_name($socket, false);
fclose($socket);
$command = escapeshellarg(PHP_BINARY) . ' -S ' . escapeshellarg($address) . ' ' . escapeshellarg(__DIR__ . '/Upload_request_fixture.php');
$process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('file', $root . '/server.log', 'a'),
    2 => array('file', $root . '/server.log', 'a')), $pipes, __DIR__, array_merge(getenv(), array('EXPENSE_UPLOAD_TEST_ROOT' => $root)));
if (!is_resource($process)) { throw new RuntimeException('Cannot start upload fixture server'); }
fclose($pipes[0]);
$caseNumber = 0;
function uploadRequest($endpoint, $uploads, $options = array(), $extra = array()) {
    global $address, $caseNumber;
    $caseNumber++;
    $boundary = 'ExpenseBoundary' . $caseNumber;
    $fields = array_merge(array('id' => '', 'no_doc' => 'EXP-TEST', 'id_expense' => '10',
        'informasi' => 'Pengeluaran fixture', 'departement' => 'FIN', 'nama' => 'alice', 'tgl_doc' => '2026-10-27',
        'total_expense' => 500, 'total_kasbon' => 0, 'jumlah_kasbon' => 500, 'tipe_pr' => 'umum',
        'jumlah_expense' => '500', 'bensin' => 500, 'tol' => 0, 'parkir' => 0, 'lainnya' => 0,
        'id_bank_pengembalian' => '1', 'transfer_tanggal' => '2026-10-27', 'transfer_jumlah' => '500'), $extra);
    if ($endpoint === 'transport_req_save') {
        foreach (array('bensin', 'tol', 'parkir', 'lainnya') as $field) {
            $fields[$field . '[0]'] = $fields[$field]; unset($fields[$field]);
        }
    }
    $body = '';
    foreach ($fields as $name => $value) {
        $body .= '--' . $boundary . "\r\nContent-Disposition: form-data; name=\"" . $name . "\"\r\n\r\n" . $value . "\r\n";
    }
    foreach ($uploads as $field) {
        $body .= '--' . $boundary . "\r\nContent-Disposition: form-data; name=\"" . $field . "\"; filename=\"receipt.pdf\"\r\nContent-Type: application/pdf\r\n\r\nfixture upload\r\n";
    }
    $body .= '--' . $boundary . "--\r\n";
    $context = stream_context_create(array('http' => array('method' => 'POST', 'timeout' => 10,
        'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary, 'content' => $body, 'ignore_errors' => true)));
    $raw = file_get_contents('http://' . $address . '/?' . http_build_query(array_merge(array('endpoint' => $endpoint, 'case' => 'case' . $caseNumber), $options)), false, $context);
    $reply = json_decode($raw, true);
    requestCheck(is_array($reply) && is_array($reply['response']), $endpoint . ': valid JSON ' . $raw);
    requestCheck(count($reply['events']) === 1, $endpoint . ': exactly one audit outcome');
    requestCheck(strpos(json_encode($reply['events']), 'fixture upload') === false && strpos(json_encode($reply['events']), 'receipt.pdf') === false, 'Audit excludes file contents and names');
    return $reply;
}
try {
    for ($attempt = 0; $attempt < 60; $attempt++) {
        $probe = @stream_socket_client('tcp://' . $address, $errno, $error, 0.05);
        if ($probe) { fclose($probe); break; }
        usleep(50000);
    }
    $cases = array(
        array('kasbon_save', array('doc_file', 'doc_file_2'), array()),
        array('transport_save', array('doc_file'), array()),
        array('transport_req_save', array('doc_file_1'), array('id_transport[0]' => '1')),
        array('save', array('doc_file[]', 'bukti_pengembalian[]'), array('pengembalian' => '2')),
        array('save_pengembalian_dana', array('bukti_transfer[]', 'bukti_transfer[]'), array()),
        array('save', array('doc_files_1[]', 'doc_files_1[]'), array('id' => '10', 'detail_id[0]' => '1',
            'id_detail[0]' => '10', 'id_kasbon[0]' => '', 'expense[0]' => '500', 'qty[0]' => '1', 'harga[0]' => '500',
            'tanggal[0]' => '2026-10-27', 'deskripsi[0]' => 'item', 'coa[0]' => '6001', 'kasbon[0]' => '0')),
        array('kasbon_save', array('doc_file', 'doc_file_2'), array('id' => '10')),
        array('transport_save', array('doc_file'), array('id' => '10')),
        array('transport_req_save', array('doc_file_1'), array('id' => '10', 'id_transport[0]' => '1')),
        array('save', array('bukti_pengembalian[]'), array('id' => '10', 'pengembalian' => '2')),
    );
    foreach ($cases as $case) {
        list($endpoint, $uploads, $fields) = $case;
        $reply = uploadRequest($endpoint, $uploads, array(), $fields);
        requestCheck(!empty($reply['response']['save']), $endpoint . ': upload saved ' . json_encode($reply));
        requestCheck(count($reply['files']) === count($uploads), $endpoint . ': every attachment saved');
        requestCheck($reply['commits'] === 1 && $reply['events'][0]['status'] === 1, $endpoint . ': success after commit');
        requestCheck($reply['events'][0]['commits_at_write'] === 1, 'Audit success follows commit');
        foreach ($reply['files'] as $path => $file) {
            requestCheck($file['mode'] === 0664 && $file['group'] === posix_getgrnam('www-data')['gid'], 'File writable by www-data');
            if (strpos($path, 'expense_bukti_pengembalian/') !== false) {
                requestCheck(strpos($path, 'assets/expense_bukti_pengembalian/') === 0, 'Return evidence in its own folder');
            }
        }
        if ($endpoint === 'save' && isset($fields['pengembalian'])) {
            $returnFiles = array_filter(array_keys($reply['files']), function ($path) { return strpos($path, 'assets/expense_bukti_pengembalian/') === 0; });
            requestCheck(count($returnFiles) === 1, 'Expense return evidence uses its dedicated folder');
        }
        foreach (array('commit_fail', 'transaction_fail') as $failure) {
            $reply = uploadRequest($endpoint, $uploads, array($failure => 1, 'existing' => 1), $fields);
            requestCheck(empty($reply['response']['save']) && $reply['events'][0]['status'] === 0, $endpoint . ': failed DB response');
            requestCheck($reply['rollbacks'] >= 1, $endpoint . ': rollback');
            requestCheck(count($reply['files']) === 1 && $reply['files']['assets/expense/existing.pdf']['content'] === 'existing attachment', $endpoint . ': remove only new files');
        }
        $reply = uploadRequest($endpoint, $uploads, array('blocked' => 1), $fields);
        if ($endpoint !== 'save_pengembalian_dana' && $uploads !== array('bukti_pengembalian[]')) {
            requestCheck(empty($reply['response']['save']) && $reply['commits'] === 0, $endpoint . ': blocked directory prevents commit');
        } else {
            requestCheck(!empty($reply['response']['save']), 'Return folder is independent of expense folder');
        }
        if ($endpoint !== 'save_pengembalian_dana' && !in_array('bukti_pengembalian[]', $uploads, true)) {
            $reply = uploadRequest($endpoint, $uploads, array('fail_at' => 1, 'existing' => 1), $fields);
            requestCheck(empty($reply['response']['save']) && $reply['commits'] === 0, 'Upload library failure prevents commit');
            requestCheck(count($reply['files']) === 1, 'Upload library failure preserves old attachments');
        }
    }
    foreach (array($cases[0], $cases[5]) as $case) {
        $reply = uploadRequest($case[0], $case[1], array('fail_at' => 2, 'existing' => 1), $case[2]);
        requestCheck(empty($reply['response']['save']) && $reply['commits'] === 0, 'Second upload failure prevents commit');
        requestCheck(count($reply['files']) === 1, 'First upload cleaned when second fails');
    }
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        foreach ($cases as $case) {
            $reply = uploadRequest($case[0], $case[1], array($writer => 1), $case[2]);
            requestCheck(!empty($reply['response']['save']) && count($reply['files']) === count($case[1]), 'Audit writer failure cannot invalidate committed attachments');
        }
    }
    $reply = uploadRequest('save', array('doc_file[]', 'bukti_pengembalian[]'), array('insert_fail' => 1, 'existing' => 1), array('pengembalian' => '2'));
    requestCheck(empty($reply['response']['save']) && $reply['commits'] === 0 && count($reply['files']) === 1, 'DB write failure removes uploads and preserves old file');
    echo 'PASS Upload requests: ' . $checks . ' checks, ' . $caseNumber . ' multipart requests' . PHP_EOL;
} finally {
    proc_terminate($process); proc_close($process);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $entry) { if ($entry->isDir()) { rmdir($entry->getPathname()); } else { unlink($entry->getPathname()); } }
    rmdir($root);
}
