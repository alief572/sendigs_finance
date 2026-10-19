<?php
define('BASEPATH', __DIR__);
$helper = dirname(__DIR__, 3) . '/helpers/kasbon_attachment_helper.php';
if (!is_file($helper)) {
    throw new Exception('Multi-attachment helper belum tersedia');
}
require $helper;
function attachmentCheck($value, $message) {
    if (!$value) { throw new Exception($message); }
}
$root = sys_get_temp_dir() . '/kasbon-attachments-' . uniqid();
mkdir($root);
mkdir($root . '/assets');
mkdir($root . '/assets/pr');
mkdir($root . '/assets/expense');
define('FCPATH', $root . '/');
define('DBACC', 'test_accounting');
function base_url($path) { return 'https://test.local/' . $path; }
class Admin_Controller {}
require dirname(__DIR__) . '/controllers/Expense.php';
class AttachmentDbResult {
    private $record;
    public function __construct($record) { $this->record = $record; }
    public function row() { return $this->record; }
    public function row_array() { return $this->record ? (array)$this->record : array(); }
    public function result_array() { return array(); }
}
class AttachmentDb {
    public $document = '["first.png","second.pdf"]';
    public $existing = null;
    public $saved;
    public $rolledBack = false;
    public $committed = false;
    public $table;
    public $capacity = null;
    public function field_data($table) { return array((object)array('name' => 'doc_file', 'type' => $this->capacity === null ? 'text' : 'varchar', 'max_length' => $this->capacity)); }
    public function select($fields) { return $this; }
    public function from($table) { $this->table = $table; return $this; }
    public function where($key, $value) { return $this; }
    public function get() { return $this->get_where($this->table, array()); }
    public function get_where($table, $where) {
        $table = explode(' ', $table)[0];
        if ($table === 'rutin_non_planning_header') { return new AttachmentDbResult((object)array('document' => $this->document)); }
        return new AttachmentDbResult($table === 'tr_kasbon' ? $this->existing : null);
    }
    public function query($sql) { return new AttachmentDbResult(null); }
    public function trans_begin() {}
    public function trans_rollback() { $this->rolledBack = true; }
    public function trans_commit() { $this->committed = true; }
    public function trans_status() { return true; }
    public function insert($table, $data) { $this->saved = $data; return 10; }
    public function update($table, $data, $where) { if ($table === 'tr_kasbon') { $this->saved = $data; } return true; }
    public function delete($table, $where) { return true; }
    public function insert_batch($table, $data) { return true; }
    public function update_batch($table, $data, $field) { return true; }
}
class AttachmentInput {
    public $values;
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : ''); }
}
class AttachmentAuth { public function user_name() { return 'tester'; } }
class AttachmentNumber { public function GetAutoGenerate($type) { return 'KS-TEST'; } }
class AttachmentUpload {
    public function initialize($config) {}
    public function do_upload($field) { return true; }
    public function data() { return array('file_name' => 'manual.png'); }
}
function attachmentSave($db, $extra = array(), $upload = false) {
    $controller = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $controller->db = $db;
    $controller->input = new AttachmentInput();
    $controller->input->values = array_merge(array('id' => '', 'no_pr' => 'PR-TEST', 'tipe_pr' => 'pr departemen', 'tgl_doc' => '2026-10-13', 'jumlah_kasbon' => '100', 'file_name' => '["forged.png"]', 'doc_pr' => '../forged', 'to_doc_pr' => '../forged'), $extra);
    $controller->auth = new AttachmentAuth();
    $controller->All_model = new AttachmentNumber();
    $controller->upload = new AttachmentUpload();
    $_FILES = $upload ? array('doc_file' => array('name' => 'manual.png', 'type' => 'image/png', 'tmp_name' => '', 'error' => 0, 'size' => 1)) : array();
    ob_start();
    $controller->kasbon_save();
    return json_decode(ob_get_clean(), true);
}
try {
    file_put_contents($root . '/assets/pr/first.png', 'first');
    file_put_contents($root . '/assets/pr/second.pdf', 'second');
    $raw = '["first.png","second.pdf"]';
    $names = kasbon_attachment_names($raw);
    kasbon_copy_attachments($names, $root . '/assets/pr', $root . '/assets/expense');
    attachmentCheck(file_get_contents($root . '/assets/expense/second.pdf') === 'second', 'Semua file harus tersalin ketika file pertama tersedia');
    attachmentCheck(kasbon_attachment_names('old.png') === array('old.png'), 'Filename lama');
    attachmentCheck(kasbon_attachment_names('["first.png"]') === array('first.png'), 'JSON satu file');
    attachmentCheck(kasbon_attachment_names(null) === array(), 'Null');
    attachmentCheck(kasbon_attachment_names('[]') === array(), 'Array kosong');
    attachmentCheck(kasbon_attachment_names('["first.png","first.png"]') === array('first.png'), 'Deduplikasi');
    foreach (array('["truncated', '../file.png', 'a/b.png', 'a\\b.png', '{"file":"a.png"}', '[null]', '["../a.png"]') as $invalid) {
        $failed = false;
        try { kasbon_attachment_names($invalid); } catch (RuntimeException $e) { $failed = true; }
        attachmentCheck($failed, 'Tolak filename/JSON tidak valid: ' . $invalid);
    }
    $failed = false;
    try { kasbon_copy_attachments(array('first.png', 'missing.pdf'), $root . '/assets/pr', $root . '/assets/expense'); }
    catch (RuntimeException $e) { $failed = strpos($e->getMessage(), 'missing.pdf') !== false; }
    attachmentCheck($failed, 'File sumber hilang harus disebutkan');
    $failed = false;
    try { kasbon_copy_attachments($names, $root . '/assets/pr', $root . '/missing-destination'); }
    catch (RuntimeException $e) { $failed = true; }
    attachmentCheck($failed, 'Tujuan gagal harus membatalkan copy');
    mkdir($root . '/blocked');
    mkdir($root . '/blocked/second.pdf');
    $failed = false;
    try { kasbon_copy_attachments($names, $root . '/assets/pr', $root . '/blocked'); }
    catch (RuntimeException $e) { $failed = strpos($e->getMessage(), 'second.pdf') !== false; }
    attachmentCheck($failed && !is_file($root . '/blocked/first.png'), 'Destination collision rolls back files created earlier in this copy');
    rmdir($root . '/blocked/second.pdf');
    rmdir($root . '/blocked');
    $db = new AttachmentDb();
    $reply = attachmentSave($db);
    attachmentCheck($reply['save'] && $db->committed && $db->saved['doc_file'] === $raw, 'Controller saves every PR file from DB, ignoring forged hidden inputs');
    $db = new AttachmentDb();
    $db->capacity = 50;
    $db->document = json_encode(array(str_repeat('a', 32) . '.png', str_repeat('b', 32) . '.png'));
    $reply = attachmentSave($db);
    attachmentCheck(!$reply['save'] && $db->rolledBack && !$db->saved && strpos($reply['message'], 'migration') !== false, 'Unmigrated varchar cannot silently truncate JSON');
    $db = new AttachmentDb();
    $db->document = '["missing.pdf"]';
    $reply = attachmentSave($db);
    attachmentCheck(!$reply['save'] && $db->rolledBack && !$db->saved && strpos($reply['message'], 'missing.pdf') !== false, 'Controller rolls back missing source');
    $db = new AttachmentDb();
    $db->document = '["broken';
    $reply = attachmentSave($db);
    attachmentCheck(!$reply['save'] && $db->rolledBack && !$db->saved, 'Invalid PR document list cannot be persisted');
    file_put_contents($root . '/assets/pr/blocked.png', 'blocked');
    mkdir($root . '/assets/expense/blocked.png');
    try {
        $db = new AttachmentDb();
        $db->document = '["blocked.png"]';
        $reply = attachmentSave($db);
        attachmentCheck(!$reply['save'] && $db->rolledBack && !$db->saved && strpos($reply['message'], 'blocked.png') !== false, 'Controller rolls back destination copy failure');
    } finally {
        rmdir($root . '/assets/expense/blocked.png');
    }
    $db = new AttachmentDb();
    $db->existing = (object)array('id_pr' => 'PR-TEST', 'tipe_pr' => 'pr departemen', 'doc_file' => 'old.png', 'doc_file_2' => 'second.pdf');
    $db->document = '["changed.png"]';
    $reply = attachmentSave($db, array('id' => '615', 'no_doc' => 'KS-TEST'));
    attachmentCheck($reply['save'] && $db->saved['doc_file'] === 'old.png' && $db->saved['doc_file_2'] === 'second.pdf', 'Unchanged edit preserves stored snapshot and second document');
    $db = new AttachmentDb();
    $db->document = null;
    $reply = attachmentSave($db, array(), true);
    attachmentCheck($reply['save'] && $db->saved['doc_file'] === 'manual.png', 'PR without documents preserves manual upload');
    $db = new AttachmentDb();
    $db->document = 'first.png';
    $reply = attachmentSave($db);
    attachmentCheck($reply['save'] && kasbon_attachment_names($db->saved['doc_file']) === array('first.png'), 'Legacy PR single filename is imported');
    $db = new AttachmentDb();
    $record = (object)array('doc_file' => $raw, 'doc_file_2' => 'second.pdf', 'tipe_pr' => 'pr departemen', 'id_pr' => 'PR-TEST');
    $html = kasbon_attachment_html($record, $db, true);
    attachmentCheck(substr_count($html, '<img ') === 1 && substr_count($html, '<iframe ') === 1, 'Every image/PDF preview appears once including doc_file_2 deduplication');
    attachmentCheck(strpos($html, '%5B') === false, 'JSON is never used as filename in URLs');
    $controller = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $controller->db = $db;
    $controller->input = new AttachmentInput();
    $controller->input->values = array('no_pr' => 'PR-TEST');
    ob_start(); $controller->copy_pr_doc(); $response = json_decode(ob_get_clean(), true);
    attachmentCheck($response['file_name'] === $raw && $response['doc_file'] === 'assets/pr/first.png' && $response['to_doc_file'] === 'assets/expense/first.png' && count($response['attachments']) === 2, 'Endpoint preserves legacy fields and lists every attachment');
    unlink($root . '/assets/expense/second.pdf');
    $items = kasbon_attachment_items($record, $db);
    attachmentCheck($items[0]['path'] === 'assets/expense/first.png' && $items[1]['path'] === 'assets/pr/second.pdf', 'Prefer kasbon copy and fall back to missing PR file');
    $record->doc_file = '["truncated';
    attachmentCheck(count(kasbon_attachment_items($record, $db)) === 2, 'Truncated JSON recovers PR list without mutation');
    $record->doc_file = '["first.png","missing.pdf"]';
    attachmentCheck(strpos(kasbon_attachment_html($record, $db), 'Lampiran tidak tersedia: missing.pdf') !== false, 'Missing source is indicated');
    $db->document = null;
    $record->doc_file = '["broken';
    attachmentCheck(strpos(kasbon_attachment_html($record, $db), 'rusak atau terpotong') !== false, 'Corrupt data with no PR cannot disappear silently');
    file_put_contents($root . "/assets/expense/a'&b.txt", 'text');
    file_put_contents($root . '/assets/expense/second.pdf', 'second');
    $record->doc_file = "a'&b.txt";
    $html = kasbon_attachment_html($record, $db, true);
    attachmentCheck(strpos($html, 'a&#039;&amp;b.txt') !== false && strpos($html, '<b>') === false && strpos($html, '<iframe') !== false, 'Escape filename; unrelated second PDF still rendered');
    attachmentCheck(strpos($html, 'a%27%26b.txt') !== false && substr_count($html, '<img ') === 0, 'Other extensions get download links without image previews');
    foreach (array('kasbon_form.php', 'kasbon_form_detail.php', 'app_kasbon_manage.php', 'kasbon_print.php', 'kasbon_pr_dept_print.php') as $view) {
        $source = file_get_contents(dirname(__DIR__) . '/views/' . $view);
        attachmentCheck(strpos($source, 'kasbon_attachment_html(') !== false && strpos($source, "base_url('assets/expense/' . \$data->doc_file)") === false, 'View uses shared renderer: ' . $view);
    }
    echo "Kasbon attachment normalization, copy, save, edit, fallback and rendering tests passed\n";
} finally {
    foreach (array('assets/pr', 'assets/expense') as $dir) {
        foreach (glob($root . '/' . $dir . '/*') as $file) { unlink($file); }
        rmdir($root . '/' . $dir);
    }
    rmdir($root . '/assets');
    rmdir($root);
}
