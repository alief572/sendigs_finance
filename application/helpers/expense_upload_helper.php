<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_upload_exception extends RuntimeException {}

class Expense_upload_files
{
    private $root;
    private $pending = array();
    private $copyLock;

    public function __construct($root) { $this->root = rtrim($root, '/\\'); }

    public function validateIncoming($files)
    {
        foreach ($files as $file) {
            $this->validateEntry($file['name'], $file['tmp_name'], $file['error']);
        }
    }

    private function validateEntry($name, $temporary, $error)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) { $this->validateEntry($value, $temporary[$key], $error[$key]); }
        } elseif ($error != UPLOAD_ERR_NO_FILE && ($error != UPLOAD_ERR_OK || ($name !== '' && $temporary === ''))) {
            throw new Expense_upload_exception('Gagal menerima lampiran upload. Silakan ulangi.');
        }
    }

    public function directory($relative = 'assets/expense')
    {
        if (!in_array($relative, array('assets/expense', 'assets/expense_bukti_pengembalian'), true)) {
            throw new Expense_upload_exception('Folder upload tidak valid.');
        }
        $base = realpath($this->root);
        if ($base === false || !is_dir($base)) { $this->fail(); }
        $path = $base;
        $parts = explode('/', $relative);
        foreach ($parts as $index => $part) {
            $path .= DIRECTORY_SEPARATOR . $part;
            if (is_link($path)) { $this->fail(); }
            $created = !is_dir($path);
            if ($created && !@mkdir($path, 02775) && !is_dir($path)) { $this->fail(); }
            if ($created || $index === count($parts) - 1) { $this->permissions($path, 02775); }
        }
        if (!is_writable($path)) { $this->fail(); }
        return $path . DIRECTORY_SEPARATOR;
    }

    private function permissions($path, $mode)
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $group = function_exists('posix_getgrnam') ? posix_getgrnam('www-data') : false;
            clearstatcache(true, $path);
            if ((!$group || filegroup($path) !== $group['gid']) && !@chgrp($path, 'www-data')) { $this->fail(); }
            clearstatcache(true, $path);
            if ((fileperms($path) & 07777) !== $mode && !@chmod($path, $mode)) { $this->fail(); }
            clearstatcache(true, $path);
            if ((fileperms($path) & 07777) !== $mode || ($group && filegroup($path) !== $group['gid'])) { $this->fail(); }
        }
        if (!is_writable($path)) { $this->fail(); }
    }

    public function uploaded($path)
    {
        $this->uploadedBatch(array($path));
    }

    public function copyDirectory()
    {
        $directory = $this->directory();
        if ($this->copyLock === null) {
            if (is_link($directory . '.pr-attachments.lock')) { $this->fail(); }
            $this->copyLock = @fopen($directory . '.pr-attachments.lock', 'c+b');
            if ($this->copyLock === false) { $this->copyLock = null; $this->fail(); }
            $this->permissions($directory . '.pr-attachments.lock', 0664);
            // Hold through commit/cleanup: a second kasbon must not reuse a copy that may still be rolled back.
            if (!@flock($this->copyLock, LOCK_EX)) { $this->fail(); }
        }
        return $directory;
    }

    public function uploadedBatch($paths)
    {
        $base = realpath($this->root);
        foreach ($paths as $path) {
            $file = realpath($path);
            if ($base === false || $file === false || is_link($path) || !is_file($file) ||
                !in_array(dirname($file), array($base . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'expense',
                    $base . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'expense_bukti_pengembalian'), true)) {
                $this->fail();
            }
            $this->pending[$file] = true;
        }
        foreach ($paths as $path) { $this->permissions($path, 0664); }
    }

    public function move($temporary, $name, $relative)
    {
        $directory = $this->directory($relative);
        $name = preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($name));
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $name = bin2hex(random_bytes(16)) . '_' . substr(pathinfo($name, PATHINFO_FILENAME), 0, 180)
            . ($extension === '' ? '' : '.' . substr($extension, 0, 20));
        $destination = $directory . $name;
        if (file_exists($destination) || is_link($destination) || !@move_uploaded_file($temporary, $destination)) {
            throw new Expense_upload_exception('Gagal mengunggah lampiran. Silakan ulangi.');
        }
        $this->uploaded($destination);
        return $relative . '/' . $name;
    }

    public function committed() { $this->pending = array(); }

    public function cleanup()
    {
        foreach ($this->pending as $file => $unused) {
            if (is_file($file) && !@unlink($file) && function_exists('log_message')) {
                log_message('error', 'Expense: gagal membersihkan lampiran request yang dibatalkan.');
            }
        }
        $this->pending = array();
        if (is_resource($this->copyLock)) {
            flock($this->copyLock, LOCK_UN);
            fclose($this->copyLock);
            $this->copyLock = null;
        }
    }

    private function fail()
    {
        throw new Expense_upload_exception('Folder atau permission upload belum siap. Hubungi administrator.');
    }
}
