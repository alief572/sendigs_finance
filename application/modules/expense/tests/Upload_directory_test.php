<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__, 3) . '/helpers/expense_upload_helper.php';
$checks = 0;
function uploadCheck($value, $message) {
    global $checks; $checks++;
    if (!$value) { throw new RuntimeException($message); }
}
function uploadFails($callback) {
    try { $callback(); } catch (Expense_upload_exception $e) {
        uploadCheck(strpos($e->getMessage(), sys_get_temp_dir()) === false, 'No internal path exposed');
        return;
    }
    throw new RuntimeException('Expected controlled upload failure');
}
$root = sys_get_temp_dir() . '/expense-directory-' . uniqid();
mkdir($root);
$files = new Expense_upload_files($root);
$oldUmask = umask(0077);
try {
    $files->validateIncoming(array());
    $files->validateIncoming(array(array('name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE)));
    uploadFails(function () use ($files) { $files->validateIncoming(array(array('name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_INI_SIZE))); });
    uploadFails(function () use ($files) { $files->validateIncoming(array(array('name' => 'receipt.pdf', 'tmp_name' => '', 'error' => UPLOAD_ERR_OK))); });
    uploadFails(function () use ($files) { $files->validateIncoming(array(array('name' => array('good.pdf', 'partial.pdf'), 'tmp_name' => array('/tmp/one', ''), 'error' => array(UPLOAD_ERR_OK, UPLOAD_ERR_PARTIAL)))); });
    foreach (array('assets/expense', 'assets/expense_bukti_pengembalian') as $relative) {
        $directory = $files->directory($relative);
        uploadCheck(is_dir($directory) && is_writable($directory), 'Create writable directory');
        uploadCheck($files->directory($relative) === $directory, 'Existing directory supported');
        if (PHP_OS_FAMILY === 'Linux') {
            uploadCheck((fileperms($directory) & 07777) === 02775, 'Directory mode independent of umask');
            uploadCheck(filegroup($directory) === posix_getgrnam('www-data')['gid'], 'www-data directory group');
            chmod($directory, 0700);
            $files->directory($relative);
            clearstatcache(true, $directory);
            uploadCheck((fileperms($directory) & 07777) === 02775, 'Repair existing directory permissions');
        }
        $file = $directory . 'new.pdf';
        file_put_contents($file, 'new');
        $files->uploaded($file);
        if (PHP_OS_FAMILY === 'Linux') {
            uploadCheck((fileperms($file) & 07777) === 0664, 'Uploaded file mode');
            uploadCheck(filegroup($file) === posix_getgrnam('www-data')['gid'], 'www-data file group');
        }
        $files->cleanup();
        uploadCheck(!file_exists($file), 'Failed request removes new file');
        file_put_contents($file, 'committed');
        $files->uploaded($file);
        $files->committed(); $files->cleanup();
        uploadCheck(file_get_contents($file) === 'committed', 'Committed file survives cleanup');
        unlink($file);
    }
    $files->copyDirectory();
    $lockPath = $root . '/assets/expense/.pr-attachments.lock';
    $secondLock = fopen($lockPath, 'c+b');
    uploadCheck(!flock($secondLock, LOCK_EX | LOCK_NB), 'PR copies stay locked until request cleanup');
    $files->committed();
    uploadCheck(!flock($secondLock, LOCK_EX | LOCK_NB), 'Commit keeps lock until cleanup');
    $files->cleanup();
    uploadCheck(flock($secondLock, LOCK_EX | LOCK_NB), 'Cleanup releases PR lock');
    flock($secondLock, LOCK_UN); fclose($secondLock); unlink($lockPath);
    uploadFails(function () use ($files) { $files->directory('../outside'); });
    uploadFails(function () use ($files) { $files->uploaded(__FILE__); });
    uploadFails(function () use ($root) { (new Expense_upload_files($root . '/missing'))->directory(); });
    uploadFails(function () use ($files) { $files->move(__FILE__, 'test.pdf', 'assets/expense'); });
    rmdir($root . '/assets/expense');
    file_put_contents($root . '/assets/expense', 'blocked');
    uploadFails(function () use ($files) { $files->directory(); });
    unlink($root . '/assets/expense');
    if (PHP_OS_FAMILY === 'Linux') {
        symlink($root . '/assets/expense_bukti_pengembalian', $root . '/assets/expense');
        uploadFails(function () use ($files) { $files->directory(); });
        unlink($root . '/assets/expense');
    }
} finally {
    umask($oldUmask);
    $files->cleanup();
    rmdir($root . '/assets/expense_bukti_pengembalian');
    rmdir($root . '/assets'); rmdir($root);
}
echo 'PASS Upload directories: ' . $checks . ' checks' . PHP_EOL;
