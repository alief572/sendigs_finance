<?php
// Run: scratch/runtime/php74/php.exe application/modules/alokasi/tests/Penampung_test.php
define('BASEPATH', __DIR__);
class BF_Model { public $db; }
class Admin_Controller { public $auth, $input, $output, $template, $load, $Alokasi_penampung_model; }
require dirname(__DIR__) . '/models/Alokasi_penampung_model.php';
$checks = 0;
function checkListing($ok, $message) {
    global $checks; $checks++;
    if (!$ok) throw new RuntimeException($message);
}
class ListingResult {
    private $statement;
    public function __construct($statement) { $this->statement = $statement; }
    public function result_array() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
}
class ListingDb {
    public $pdo, $fail = false, $calls = 0, $db_debug = true;
    public function __construct() {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE tr_alokasi_detail(id INTEGER, tanggal_transaksi TEXT, reference_no TEXT, keterangan TEXT, tipe_bank INTEGER, jenis_bank INTEGER);
            CREATE TABLE tr_alokasi_split(id INTEGER, id_alokasi_detail INTEGER, jenis_alokasi INTEGER, nominal TEXT);
            CREATE TABLE ms_bank(id INTEGER, bank INTEGER, nama TEXT, rekening TEXT, deleted INTEGER);
            CREATE TABLE list_bank(id INTEGER, nama_bank TEXT);
            INSERT INTO list_bank VALUES(7,"BCA");
            INSERT INTO ms_bank VALUES(1,7,"STM","123",0),(2,7,"VUCA","456",0),(3,7,"Deleted","789",1);
            INSERT INTO tr_alokasi_detail VALUES(10,"2026-11-01","REF1","Mixed",1,7),(11,"2026-10-01","REF2","Other",2,7),(12,"0000-00-00",NULL,NULL,99,99);
            INSERT INTO tr_alokasi_split VALUES(1,10,3,"25.15"),(2,10,4,"50.25"),(3,10,5,"75.35"),(4,10,6,"100.45"),(5,10,1,"9999.99"),(6,10,2,"9999.99"),(7,10,7,"9999.99"),(8,10,8,"9999.99"),(9,11,3,"2000.50"),(10,12,3,"3.00");');
    }
    public function query($sql, $params = array()) {
        $this->calls++;
        if ($this->fail) return false;
        $stmt = $this->pdo->prepare($sql);
        foreach (array_values($params) as $i => $value) $stmt->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        $stmt->execute();
        return new ListingResult($stmt);
    }
}
$model = new Alokasi_penampung_model(); $model->db = new ListingDb();
foreach (array(3=>'25.15',4=>'50.25',5=>'75.35',6=>'100.45') as $kind=>$amount) {
    $result = $model->listing($kind, array('search'=>array('value'=>'REF1')));
    checkListing(count($result['data']) === 1 && $result['data'][0]['nominal'] === $amount, "Kind $kind uses its own split amount");
}
$result = $model->listing(3, array('draw'=>'7','start'=>'1','length'=>'1'));
checkListing($result['draw'] === 7 && $result['recordsTotal'] === 3 && $result['recordsFiltered'] === 3 && count($result['data']) === 1, 'Counts and pagination');
checkListing($result['data'][0]['no'] === 2 && $result['data'][0]['nominal'] === '2,000.50', 'Stable date ordering and two decimals');
$result = $model->listing(3, array('bank'=>'2','startDate'=>'2026-10-01','endDate'=>'2026-10-31'));
checkListing($result['recordsTotal'] === 3 && $result['recordsFiltered'] === 1, 'Bank and inclusive date filters');
foreach (array('BCA','STM','123','REF1','Mixed','25.15') as $term) {
    checkListing($model->listing(3, array('search'=>array('value'=>$term)))['recordsFiltered'] >= 1, "Search $term");
}
checkListing($model->listing(3, array('search'=>array('value'=>"' OR 1=1 --")))['recordsFiltered'] === 0, 'Search cannot inject SQL');
checkListing($model->listing(3, array('start'=>999))['data'] === array(), 'Empty page');
$pending = $model->listing(3, array('bank'=>99))['data'][0];
checkListing($pending['tanggal_transaksi'] === 'PEND' && $pending['reference_no'] === '' && $pending['bank'] === '-', 'Pending date, NULL fields and missing master bank');
foreach (array(array('length'=>-1),array('length'=>101),array('start'=>-1),array('draw'=>array()),array('bank'=>array()),array('bank'=>'1 OR 1'),array('startDate'=>'2026-02-30'),array('startDate'=>'2026-11-10','endDate'=>'2026-11-01'),array('search'=>array('value'=>array())),array('search'=>array('value'=>str_repeat('a',201)))) as $bad) {
    try { $model->listing(3, $bad); checkListing(false, 'Invalid input must be rejected'); }
    catch (InvalidArgumentException $e) { checkListing(true, 'Invalid input rejected'); }
}
try { $model->listing(1, array()); checkListing(false, 'Unsupported kind'); }
catch (InvalidArgumentException $e) { checkListing(true, 'Unsupported kind rejected'); }
checkListing(count($model->banks()) === 2, 'Bank filter excludes deleted masters');
$model->db->fail = true;
try { $model->listing(3, array()); checkListing(false, 'Query failure must not look like empty data'); }
catch (RuntimeException $e) { checkListing(true, 'Query failure detected'); }
checkListing($model->db->db_debug === true, 'Database debug restored after failure');
$model->db->fail = false;

class ListingAuth {
    public $allowed = true, $seen;
    public function restrict($permission) { $this->seen = $permission; if (!$this->allowed) throw new RuntimeException('Forbidden'); }
}
class ListingInput { public $post = array(); public function post() { return $this->post; } }
class ListingOutput {
    public $status = 200, $body;
    public function set_status_header($status) { $this->status = $status; return $this; }
    public function set_content_type($type) { return $this; }
    public function set_output($body) { $this->body = json_decode($body, true); return $this; }
}
class ListingTemplate {
    public $view, $data;
    public function title($title) {}
    public function render($view, $data) { $this->view = $view; $this->data = $data; }
}
function log_message($level, $message) { checkListing(strpos($message, 'SECRET') === false, 'Error log excludes raw input'); }
function show_error($message, $status) { $GLOBALS['listingError'] = array($message, $status); }
foreach (array('pengembalian_kasbon'=>3,'mutasi'=>4,'transaksi_bank'=>5,'pembayaran'=>6) as $suffix=>$kind) {
    $class = 'Alokasi_' . $suffix;
    require dirname(__DIR__, 2) . '/alokasi_' . $suffix . '/controllers/' . $class . '.php';
    $controller = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $controller->auth = new ListingAuth(); $controller->input = new ListingInput();
    $controller->output = new ListingOutput(); $controller->template = new ListingTemplate();
    $controller->Alokasi_penampung_model = $model;
    $controller->get_alokasi();
    checkListing($controller->output->status === 200 && $controller->output->body['data'][0]['nominal'] === array(3=>'25.15',4=>'50.25',5=>'75.35',6=>'100.45')[$kind], "$class routing");
    $controller->index();
    checkListing($controller->template->view === 'alokasi/penampung' && count($controller->template->data['data_bank']) === 2, "$class shared view");
    $permission = 'Alokasi_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', $suffix))) . '.View';
    checkListing($controller->auth->seen === $permission, "$class permission");
    $controller->input->post = array('bank'=>array('SECRET'));
    $controller->get_alokasi();
    checkListing($controller->output->status === 400 && $controller->output->body['data'] === array(), "$class bad filter");
    $controller->input->post = array(); $model->db->fail = true;
    $controller->index();
    checkListing($GLOBALS['listingError'] === array('Gagal memuat daftar bank.', 500), "$class bank query failure");
    $controller->get_alokasi();
    checkListing($controller->output->status === 500 && $controller->output->body['data'] === array(), "$class query failure");
    $model->db->fail = false; $controller->auth->allowed = false;
    foreach (array('index','get_alokasi') as $method) {
        $before = $model->db->calls;
        try { $controller->$method(); checkListing(false, 'Missing permission must deny access'); }
        catch (RuntimeException $e) { checkListing($e->getMessage() === 'Forbidden' && $model->db->calls === $before, "$class $method denied before query"); }
    }
}
define('EXT', '.php');
class ListingConfig { public function item($key) { return array(dirname(__DIR__, 2) . '/' => '../modules/'); } }
$CFG = new ListingConfig();
require dirname(__DIR__, 3) . '/third_party/MX/Modules.php';
Modules::$locations = array(dirname(__DIR__, 2) . '/' => '../modules/');
foreach (array('alokasi_pengembalian_kasbon','alokasi_mutasi','alokasi_transaksi_bank','alokasi_pembayaran') as $module) {
    list($path, $file) = Modules::find('alokasi/penampung', $module, 'views/');
    checkListing($path !== false && is_file($path . $file . '.php'), 'HMVC resolves shared view from ' . $module);
    list($path, $file) = Modules::find('alokasi/Alokasi_penampung_model', $module, 'models/');
    checkListing($path !== false && is_file($path . $file . '.php'), 'HMVC resolves shared model from ' . $module);
}
function html_escape($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
function site_url($path) { return '/' . $path; }
function base_url($path) { return '/' . $path; }
$data_bank = array(array('id'=>'1" onmouseover="alert(1)', 'nama_bank'=>'<img src=x>', 'rekening'=>'123', 'nama'=>'STM'));
$listing_route = 'alokasi_mutasi';
ob_start(); require dirname(__DIR__) . '/views/penampung.php'; $html = ob_get_clean();
checkListing(strpos($html, '<img src=x>') === false && strpos($html, '&lt;img src=x&gt;') !== false, 'Rendered bank labels escape markup');
checkListing(strpos($html, 'value="1&quot; onmouseover=&quot;alert(1)"') !== false, 'Rendered option attribute escapes quotes');
checkListing(strpos($html, json_encode('/alokasi_mutasi/get_alokasi')) !== false, 'Rendered AJAX endpoint follows module route');
foreach (array('Dari tanggal', 'Sampai tanggal', 'Rekening bank', 'Terapkan filter', 'Daftar alokasi', 'scope="col"', 'aria-busy="true"', 'aria-live="polite"') as $text) {
    checkListing(strpos($html, $text) !== false, 'UI markup: ' . $text);
}
checkListing(is_file(dirname(__DIR__, 4) . '/assets/css/alokasi-penampung.css'), 'Scoped allocation stylesheet exists');
foreach (array('alokasi_pengembalian_kasbon','alokasi_mutasi','alokasi_transaksi_bank','alokasi_pembayaran') as $listing_route) {
    ob_start(); require dirname(__DIR__) . '/views/penampung.php'; $rendered = ob_get_clean();
    checkListing(strpos($rendered, json_encode('/' . $listing_route . '/get_alokasi')) !== false, 'UI endpoint renders for ' . $listing_route);
}
foreach (array('js/jquery.dataTables.js','js/dataTables.bootstrap.js','css/dataTables.bootstrap.css') as $asset) {
    checkListing(is_file(dirname(__DIR__, 4) . '/assets/datatables/' . $asset), 'DataTables asset exists: ' . $asset);
}
echo "PASS: $checks allocation listing checks\n";
