<?php
define('BASEPATH', __DIR__);
class Admin_Controller {}
class CI_Model { public function __construct() {} }
require dirname(__DIR__) . '/models/Audit_trail_model.php';
require dirname(__DIR__) . '/controllers/Audit_trail.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function log_message($level, $message) {}
function base_url($path) { return '/test/' . $path; }
function site_url($path) { return '/test/' . $path; }
class ControllerAuth { public $allow = false; public function has_permission($permission) { return $this->allow && $permission === 'Audit_Trail.View'; } }
class ControllerInput { public $values = array(); public function get($key, $filter) { return $this->values; } }
class ControllerOutput {
    public $status = 200, $body;
    public function set_status_header($status) { $this->status = $status; return $this; }
    public function set_content_type($type, $charset) { return $this; }
    public function set_header($header) { return $this; }
    public function set_output($body) { $this->body = $body; return $this; }
}
class ControllerTemplate { public $rendered; public function render($view, $data) { $this->rendered = array($view, $data); } }
class ControllerDb {
    public $save_queries = true, $committed = false;
    public function query($sql) { return true; }
    public function trans_begin() { return true; }
    public function trans_commit() { $this->committed = true; return true; }
    public function trans_rollback() { return true; }
}
class ControllerModel extends Audit_trail_model {
    public $calls = 0;
    public function page($options) { $this->calls++; return array('draw' => $options['draw'], 'recordsTotal' => 1, 'recordsFiltered' => 1, 'data' => array()); }
    public function detail($id) { $this->calls++; return $id === 'ok' ? array('id' => 'ok', 'status' => null, 'message' => '<script>alert(1)</script>') : null; }
    public function modules() { $this->calls++; return array('AUTH', '<script>alert(1)</script>'); }
    public function export_rows($options, $batchSize = 500) {
        $this->calls++;
        yield array('id' => 'a', 'status' => '1', 'message' => "=FORMULA\nsecond line", 'data' => '{"id":7}');
        yield array('id' => 'b', 'status' => null, 'message' => 'Second activity');
    }
}
class TestController extends Audit_trail {
    public function __construct() {
        $this->auth = new ControllerAuth(); $this->input = new ControllerInput(); $this->output = new ControllerOutput();
        $this->template = new ControllerTemplate(); $this->Audit_trail_model = new ControllerModel(); $this->db = new ControllerDb();
    }
}
foreach (array('index', 'data', 'detail', 'export_csv') as $endpoint) {
    $controller = new TestController(); $controller->$endpoint();
    check($controller->output->status === 403 && $controller->Audit_trail_model->calls === 0, 'Denied endpoint read protected logs: ' . $endpoint);
}
$controller = new TestController(); $controller->auth->allow = true;
$controller->data();
check($controller->output->status === 200 && isset(json_decode($controller->output->body, true)['recordsTotal']), 'Authorized list missing');
$controller->detail('missing'); check($controller->output->status === 404, 'Missing ID must be 404');
$controller->detail('ok'); check(json_decode($controller->output->body, true)['data']['status_label'] === 'Tidak diketahui', 'Detail status wrong');
$controller->input->values = array('date_from' => 'invalid'); $controller->data();
check($controller->output->status === 422, 'Invalid list filter must be rejected');
$controller->export_csv(); check($controller->output->status === 422, 'Invalid export filter must be rejected');
$controller->input->values = array();
ob_start(); $controller->export_csv(); $csv = ob_get_clean();
check($controller->db->committed && $controller->db->save_queries === true, 'Export must finish its snapshot and restore query settings');
$stream = fopen('php://memory', 'r+'); fwrite($stream, substr($csv, 3)); rewind($stream);
$header = fgetcsv($stream); $first = fgetcsv($stream); $second = fgetcsv($stream); fclose($stream);
check(count($header) === 13 && $first[7] === "'=FORMULA\nsecond line" && $second[6] === 'Tidak diketahui', 'CSV roundtrip or full detail fields wrong');
$controller->index(); extract($controller->template->rendered[1]);
ob_start(); require dirname(__DIR__) . '/views/index.php'; $html = ob_get_clean();
check(strpos($html, '<script>alert(1)</script>') === false && strpos($html, '&lt;script&gt;alert(1)&lt;/script&gt;') !== false, 'Module names must be escaped in the rendered template');
echo "PASS: permissions on every endpoint, validation, missing detail, CSV roundtrip and template escaping\n";
