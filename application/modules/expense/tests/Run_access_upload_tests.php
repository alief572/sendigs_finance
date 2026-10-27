<?php
if (PHP_SAPI !== 'cli') { exit; }
$failed = false;
$lintFiles = array_merge(glob(__DIR__ . '/*.php'), array(dirname(__DIR__) . '/controllers/Expense.php',
    dirname(__DIR__, 3) . '/helpers/expense_upload_helper.php', dirname(__DIR__, 3) . '/helpers/kasbon_attachment_helper.php'));
foreach ($lintFiles as $file) {
    $output = array();
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1', $output, $status);
    if ($status !== 0) { $failed = true; echo implode(PHP_EOL, $output) . PHP_EOL; }
}
echo 'PHP lint: ' . count($lintFiles) . ' files checked' . PHP_EOL;
$tests = array('Visibility_test.php', 'Query_state_test.php', 'Upload_directory_test.php', 'Upload_requests_test.php',
    'Kasbon_attachments_test.php', 'Management_actions_test.php', 'Reject_test.php');
for ($batch = 1; $batch <= 7; $batch++) { $tests[] = 'Batch' . $batch . '_audit_test.php'; }
foreach ($tests as $test) {
    $output = array();
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/' . $test) . ' 2>&1', $output, $status);
    echo $test . ': ' . ($status === 0 ? 'PASS' : 'FAIL') . PHP_EOL . implode(PHP_EOL, $output) . PHP_EOL;
    if ($status !== 0) { $failed = true; }
}
exit($failed ? 1 : 0);
