<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__, 3) . '/helpers/expense_upload_helper.php';
if (PHP_OS_FAMILY !== 'Linux' || posix_getegid() === posix_getgrnam('www-data')['gid'] ||
    in_array(posix_getgrnam('www-data')['gid'], posix_getgroups(), true)) {
    throw new RuntimeException('Run this test as an unprivileged Linux user outside www-data');
}
$root = sys_get_temp_dir() . '/expense-denied-' . uniqid();
mkdir($root);
$failed = false;
try { (new Expense_upload_files($root))->directory(); }
catch (Expense_upload_exception $e) {
    $failed = true;
    if (strpos($e->getMessage(), $root) !== false) { throw new RuntimeException('Internal path exposed'); }
}
if (!$failed || is_dir($root . '/assets/expense')) { throw new RuntimeException('Missing group privileges must fail'); }
if ((fileperms($root . '/assets') & 0002) !== 0) { throw new RuntimeException('Must not fall back to world writable mode'); }
rmdir($root . '/assets'); rmdir($root);
echo 'PASS Upload permission denial: controlled failure without www-data privileges' . PHP_EOL;
