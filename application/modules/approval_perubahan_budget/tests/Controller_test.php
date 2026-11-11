<?php
// Controller + view Approval Perubahan Budget di atas model sungguhan (SQLite).
// Jalankan: scratch/runtime/php74/php.exe application/modules/approval_perubahan_budget/tests/Controller_test.php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
$checks = 0;
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message . ' @' . basename($file) . ':' . $line, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
function base_url($p = '') { return '/base/' . $p; }
function has_permission($p = '') { return empty($GLOBALS['deny'][$p]); }
function redirect($uri = '') { $GLOBALS['redirectTarget'] = $uri; }
function simpan_aktifitas($a, $b, $c, $d, $e, $status) { $GLOBALS['activity'][] = array($c, $status); }
class BF_Model { public function __construct() {} }
class Stub_Input { public $values = array(); public function post($k = null) { return $k === null ? $this->values : (isset($this->values[$k]) ? $this->values[$k] : null); } }
class Stub_Auth { public function restrict($p) { if (!empty($GLOBALS['deny'][$p])) { throw new RuntimeException('denied ' . $p); } } public function user_name() { return 'direktur'; } }
class Stub_Out { public function set_content_type($t) { return $this; } }
class Stub_Template { public $data = array(); public $view = ''; public $messages = array(); public function set($k, $v = null) { $this->data[$k] = $v; } public function title($t = '') {} public function page_icon($t = '') {}
	public function render($v) { $this->view = $v; } public function set_message($m, $t = '') { $this->messages[] = $m; } }
class Stub_Loader { public function model($m) {} public function view($name, $data = array()) { $GLOBALS['viewCalled'] = array($name, $data); echo 'VIEW:' . $name; } }
class Admin_Controller { public $load, $template, $auth, $input, $output, $db; public function __construct() { $this->load = new Stub_Loader(); $this->template = new Stub_Template(); } }
class Stub_All { public function GetDeptCombo($k = '') { return array('' => '', 'DEP-1' => 'Finance - STM', 'DEP-2' => 'GA - STM'); } }
require BASEPATH . 'database/DB.php';
require dirname(__DIR__, 3) . '/helpers/periodik_helper.php';
require APPPATH . 'modules/budget_periodik/models/Budget_periodik_model.php';
require APPPATH . 'modules/approval_perubahan_budget/controllers/Approval_perubahan_budget.php';

class MysqlishDb {
	public $db;
	public function __construct($db) { $this->db = $db; }
	public function query($sql, $binds = false) { return $this->db->query(str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $sql), $binds); }
	public function __call($name, $args) { return call_user_func_array([$this->db, $name], $args); }
}
$real = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
$db = new MysqlishDb($real);
$real->query('CREATE TABLE ms_periodik_counter (kode TEXT, periode TEXT, last_no INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (kode, periode))');
$real->query('CREATE TABLE ms_budget_rutin (id INTEGER PRIMARY KEY AUTOINCREMENT, departement TEXT, coa TEXT, nama TEXT, tipe TEXT, tanggal TEXT, nilai REAL, keterangan TEXT, kode_id TEXT, masa_berlaku INTEGER DEFAULT 0, masa_awal INTEGER DEFAULT 0, aktif INTEGER DEFAULT 1)');
$real->query('CREATE TABLE tr_pengajuan_rutin_detail (id INTEGER PRIMARY KEY AUTOINCREMENT, id_budget INTEGER, item_status INTEGER)');
$real->query('CREATE TABLE tr_budget_bg (id INTEGER PRIMARY KEY AUTOINCREMENT, no_bg TEXT UNIQUE, departement TEXT, status TEXT, created_by TEXT, created_on TEXT, decided_on TEXT)');
$real->query('CREATE TABLE tr_budget_bg_line (id INTEGER PRIMARY KEY AUTOINCREMENT, no_bg TEXT, kind TEXT NOT NULL, id_budget INTEGER, before_json TEXT, after_json TEXT, fields TEXT, decision TEXT, note TEXT, decided_by TEXT, decided_at TEXT)');
$real->insert('ms_budget_rutin', ['departement' => 'DEP-1', 'coa' => '6101', 'nama' => 'Sewa <Kantor>', 'tipe' => 'bulan', 'tanggal' => '25', 'nilai' => 1500000, 'keterangan' => '', 'kode_id' => '', 'masa_berlaku' => 0, 'masa_awal' => 0, 'aktif' => 1]);
$sewa = $real->insert_id();

function ctrl() {
	global $db;
	$c = new Approval_perubahan_budget();
	$c->db = $db;
	$c->input = new Stub_Input();
	$c->auth = new Stub_Auth();
	$c->output = new Stub_Out();
	$c->All_model = new Stub_All();
	$c->Budget_periodik_model = (new ReflectionClass('Budget_periodik_model'))->newInstanceWithoutConstructor();
	$c->Budget_periodik_model->db = $db;
	$GLOBALS['activity'] = array(); $GLOBALS['deny'] = array(); $GLOBALS['redirectTarget'] = null;
	return $c;
}
function decide($c, $post) { $c->input->values = $post; ob_start(); $c->save_decision(); return json_decode(ob_get_clean(), true); }
function render($view, $vars) { extract($vars); ob_start(); include APPPATH . 'modules/approval_perubahan_budget/views/' . $view . '.php'; return ob_get_clean(); }
function js_syntax($html, $label) {
	preg_match_all('#<script>(.*?)</script>#s', $html, $m);
	$file = tempnam(sys_get_temp_dir(), 'apv') . '.js';
	file_put_contents($file, implode("\n;\n", $m[1]));
	exec('node --check ' . escapeshellarg($file) . ' 2>&1', $out, $code);
	unlink($file);
	check($code === 0, "inline JS syntax ($label): " . implode(' ', $out));
}

// BG pending dari departemen: ubah nilai, tambah item baru
$model = ctrl()->Budget_periodik_model;
$master = $model->GetMasters('DEP-1');
$lines = Budget_logic::diff($master, [array_merge($master[0], ['nilai' => 1750000]),
	['id' => '', 'coa' => '6102', 'nama' => 'Canva', 'tipe' => 'tahun', 'tanggal' => '11/15/2026', 'nilai' => 450000, 'masa_berlaku' => 3, 'masa_awal' => 0, 'aktif' => 1]]);
list(, $no) = $model->CreateBg('DEP-1', $lines, 'admin.dep1');
$bg = $model->GetBgs('DEP-1', 'pending')[$no];
$lineIds = array_column($bg['lines'], 'id');

// ---- index (kerangka) dan get_data (DataTables server-side)
function dt($c, $tab, $post = []) { $c->input->values = $post + ['tab' => $tab, 'draw' => 5, 'start' => 0, 'length' => 10, 'search' => ['value' => ''], 'order' => []]; ob_start(); $c->get_data(); return json_decode(ob_get_clean(), true); }
$c = ctrl(); $c->index();
check($c->template->view === 'index' && !array_key_exists('pending', $c->template->data) && !array_key_exists('record', $c->template->data), 'index: renders the shell only (rows come from get_data)');
$c = ctrl();
$r = dt($c, 'wait');
check($r['draw'] === 5 && $r['recordsTotal'] === 1 && $r['recordsFiltered'] === 1 && $r['pending_total'] === 1 && count($r['data']) === 1, 'get_data wait: pending BG listed with the DataTables envelope');
$row = $r['data'][0];
check($row['DT_RowAttr'] === ['data-bg' => $no] && $row['no'] === 1 && $row['department'] === 'Finance - STM' && $row['nomor'] === $no, 'get_data wait: row attributes and columns');
check(strpos($row['perubahan'], 'pill W">Ubah') !== false && strpos($row['perubahan'], 'pill P">Item baru') !== false && strpos($row['perubahan'], 'Sewa &lt;Kantor&gt;') !== false && strpos($row['perubahan'], 'Canva') !== false, 'get_data wait: change kinds as pills, item names escaped');
check(strpos($row['diajukan'], 'admin.dep1') !== false && strpos($row['action'], 'data-view="' . $no . '"') !== false && strpos($row['action'], 'href="/base/approval_perubahan_budget/proses/' . $no . '"') !== false, 'get_data wait: submitter, Lihat and Proses approval actions');
$GLOBALS['deny'] = ['Approval_Perubahan_Budget.Manage' => true];
$r2 = dt($c, 'wait');
check(strpos($r2['data'][0]['action'], 'Proses approval') === false && strpos($r2['data'][0]['action'], 'Lihat') !== false, 'get_data wait: no Proses approval link without Manage');
$GLOBALS['deny'] = [];
$r2 = dt($c, 'rec');
check($r2['recordsTotal'] === 0 && $r2['data'] === [] && $r2['pending_total'] === 1, 'get_data rec: nothing decided yet, pending count still reported');
$r2 = dt($c, 'wait', ['search' => ['value' => 'canva']]);
check($r2['recordsFiltered'] === 1 && $r2['recordsTotal'] === 1, 'get_data wait: search matches item name');
$r2 = dt($c, 'wait', ['search' => ['value' => "' OR 1=1 --"]]);
check($r2['recordsFiltered'] === 0 && $r2['data'] === [], 'get_data wait: search injection string matches nothing');
$GLOBALS['deny'] = ['Approval_Perubahan_Budget.View' => true];
try { $c->index(); check(false, 'index requires View'); } catch (RuntimeException $e) { check(strpos($e->getMessage(), 'denied') === 0, 'index requires View'); }

// ---- view (modal)
$c = ctrl(); ob_start(); $c->view($no); $out = ob_get_clean();
check($out === 'VIEW:view' && $GLOBALS['viewCalled'][1]['bg']['no'] === $no && $GLOBALS['viewCalled'][1]['can_decide'] === true && $GLOBALS['viewCalled'][1]['dept_name'] === 'Finance - STM', 'view: BG passed to the partial with the decide flag');
$GLOBALS['deny'] = ['Approval_Perubahan_Budget.Manage' => true];
ob_start(); $c->view($no); ob_end_clean();
check($GLOBALS['viewCalled'][1]['can_decide'] === false, 'view: no decide flag without Manage');
ob_start(); ctrl()->view('BG-TIDAK-ADA'); ob_end_clean();
check($GLOBALS['viewCalled'][1]['bg'] === null, 'view: unknown BG');

// ---- proses
$c = ctrl(); $c->proses('BG-TIDAK-ADA');
check($GLOBALS['redirectTarget'] === 'approval_perubahan_budget' && $c->template->messages === ['Invalid Data'], 'proses: unknown BG redirects back');
$GLOBALS['deny'] = ['Approval_Perubahan_Budget.Manage' => true];
try { $c->proses($no); check(false, 'proses requires Manage'); } catch (RuntimeException $e) { check(strpos($e->getMessage(), 'denied') === 0, 'proses requires Manage'); }
$c = ctrl(); $c->proses($no);
check($c->template->view === 'form' && $c->template->data['bg']['no'] === $no && $GLOBALS['redirectTarget'] === null, 'proses: pending BG renders the decision form');

// ---- save_decision: penolakan
$masterBefore = json_encode($real->get('ms_budget_rutin')->result_array());
$c = ctrl();
$r = decide($c, ['no_bg' => $no, 'decision' => [$lineIds[0] => 'A'], 'note' => []]);
check($r['save'] === 0 && $r['msg'] === 'Keputusan belum lengkap untuk semua perubahan.', 'decision: every line needs a decision');
$r = decide($c, ['no_bg' => $no, 'decision' => [$lineIds[0] => 'A', $lineIds[1] => 'R'], 'note' => [$lineIds[1] => '  ']]);
check($r['save'] === 0 && $r['msg'] === 'Ada perubahan yang di-reject tanpa alasan.', 'decision: reject needs a reason');
$r = decide($c, ['no_bg' => 'BG-TIDAK-ADA', 'decision' => [], 'note' => []]);
check($r['save'] === 0 && $r['msg'] === 'Perubahan budget tidak ditemukan.', 'decision: unknown BG');
check(json_encode($real->get('ms_budget_rutin')->result_array()) === $masterBefore && $GLOBALS['activity'][0][1] === 0, 'decision: refused attempts change nothing and are logged as failures');
$GLOBALS['deny'] = ['Approval_Perubahan_Budget.Manage' => true];
try { decide($c, ['no_bg' => $no]); check(false, 'decision requires Manage'); } catch (RuntimeException $e) { check(strpos($e->getMessage(), 'denied') === 0, 'decision requires Manage'); }
check(json_encode($real->get('ms_budget_rutin')->result_array()) === $masterBefore, 'decision without Manage permission changes nothing');

// ---- save_decision: berhasil (satu disetujui, satu ditolak)
$c = ctrl();
$r = decide($c, ['no_bg' => $no, 'decision' => [$lineIds[0] => 'A', $lineIds[1] => 'R'], 'note' => [$lineIds[1] => 'Belum ada kontrak']]);
check($r['save'] === 1 && $r['approved'] === 1 && $r['rejected'] === 1 && $r['no_bg'] === $no, 'decision: approved + rejected');
$m = $real->get('ms_budget_rutin')->result_array();
check(count($m) === 1 && (float) $m[0]['nilai'] === 1750000.0 && (int) $m[0]['id'] === (int) $sewa, 'decision: approved edit applied to the same master row; rejected new item not created');
check($GLOBALS['activity'][count($GLOBALS['activity']) - 1][1] === 1, 'decision: success logged');
$r = decide($c, ['no_bg' => $no, 'decision' => [$lineIds[0] => 'R', $lineIds[1] => 'R'], 'note' => [$lineIds[0] => 'x', $lineIds[1] => 'x']]);
check($r['save'] === 0 && strpos($r['msg'], 'tidak bisa diubah') !== false, 'decision: final, cannot be decided twice');
$c = ctrl(); $c->proses($no);
check($GLOBALS['redirectTarget'] === 'approval_perubahan_budget' && strpos($c->template->messages[0], 'sudah diputuskan') !== false, 'proses: decided BG cannot be reopened');

// ---- setelah keputusan: tab Record approval
$c = ctrl();
$r = dt($c, 'wait');
check($r['recordsTotal'] === 0 && $r['data'] === [] && $r['pending_total'] === 0, 'get_data wait: decided BG leaves the waiting tab');
$r = dt($c, 'rec');
check($r['recordsTotal'] === 2 && $r['pending_total'] === 0 && count($r['data']) === 2, 'get_data rec: decided lines move to Record approval');
$byItem = array_column($r['data'], null, 'item');
check(isset($byItem['Sewa &lt;Kantor&gt;']) && isset($byItem['Canva']), 'get_data rec: item names escaped');
$sewaRow = $byItem['Sewa &lt;Kantor&gt;']; $canvaRow = $byItem['Canva'];
check(strpos($sewaRow['perubahan'], 'Perkiraan biaya:') !== false && strpos($sewaRow['perubahan'], '1.500.000 &rarr; 1.750.000') !== false && strpos($sewaRow['keputusan'], 'pill A">Approved') !== false && $sewaRow['note'] === '', 'get_data rec: approved edit shows before -> after');
check(strpos($canvaRow['keputusan'], 'pill R">Rejected') !== false && $canvaRow['note'] === '<span class="note">Belum ada kontrak</span>' && strpos($canvaRow['perubahan'], 'pill P">Item baru') !== false && strpos($canvaRow['diputuskan'], 'direktur') !== false, 'get_data rec: rejected new item shows reason and approver');
$r2 = dt($c, 'rec', ['search' => ['value' => 'belum ada kontrak']]);
check($r2['recordsFiltered'] === 1 && $r2['recordsTotal'] === 2 && strpos($r2['data'][0]['item'], 'Canva') !== false, 'get_data rec: search matches the reject reason');
$r2 = dt($c, 'rec', ['search' => ['value' => 'approved']]);
check($r2['recordsFiltered'] === 1 && strpos($r2['data'][0]['keputusan'], 'Approved') !== false, 'get_data rec: search matches the decision');
$r2 = dt($c, 'rec', ['order' => [['column' => 3, 'dir' => 'asc']]]);
check(array_column($r2['data'], 'item') === ['Canva', 'Sewa &lt;Kantor&gt;'], 'get_data rec: order by item ascending');
$r2 = dt($c, 'rec', ['order' => [['column' => 3, 'dir' => 'desc']]]);
check(array_column($r2['data'], 'item') === ['Sewa &lt;Kantor&gt;', 'Canva'], 'get_data rec: order by item descending');
$r2 = dt($c, 'rec', ['start' => 1, 'length' => 1]);
check($r2['recordsFiltered'] === 2 && count($r2['data']) === 1, 'get_data rec: paging');
$r2 = dt($c, 'rec', ['order' => [['column' => 99, 'dir' => 'asc']]]);
check(count($r2['data']) === 2, 'get_data rec: unknown order column falls back to newest first');
$r2 = dt($c, 'bukan-tab');
check($r2['recordsTotal'] === 0 && $r2['pending_total'] === 0, 'get_data: unknown tab behaves as the waiting tab');
$GLOBALS['deny'] = ['Approval_Perubahan_Budget.View' => true];
try { dt($c, 'wait'); check(false, 'get_data requires View'); } catch (RuntimeException $e) { check(strpos($e->getMessage(), 'denied') === 0, 'get_data requires View'); }
$GLOBALS['deny'] = [];
$html = render('index', []);
check(strpos($html, 'id="pjWait"') !== false && strpos($html, 'id="pjRec"') !== false && substr_count($html, '<tbody></tbody>') === 2, 'index view: two empty table skeletons');
check(substr_count($html, 'serverSide: true') === 2 && strpos($html, "'approval_perubahan_budget/get_data'") !== false && strpos($html, 'jquery.dataTables.min.js') !== false && strpos($html, 'pending_total') !== false, 'index view: both tables are DataTables server-side, tab title takes the pending count');
foreach (['Departement', 'No. Perubahan', 'Perubahan', 'Diajukan', 'Action', 'Diputuskan', 'Keputusan', 'Keterangan reject'] as $col) { check(strpos($html, '<th>' . $col . '</th>') !== false, "index view: column $col"); }
js_syntax($html, 'index');

// BG baru untuk menguji tampilan tab menunggu dan form
list(, $no2) = $model->CreateBg('DEP-2', Budget_logic::diff([], [['id' => '', 'coa' => '6101', 'nama' => 'Listrik <b>', 'tipe' => 'bulan', 'tanggal' => '10', 'nilai' => 300000, 'masa_berlaku' => 0, 'masa_awal' => 0, 'aktif' => 1]]), 'admin.dep2');
$r = dt(ctrl(), 'wait');
check($r['pending_total'] === 1 && strpos($r['data'][0]['action'], 'href="/base/approval_perubahan_budget/proses/' . $no2 . '"') !== false && $r['data'][0]['department'] === 'GA - STM' && strpos($r['data'][0]['perubahan'], 'Listrik &lt;b&gt;') !== false, 'get_data wait: new BG with decide link, department name and escaped item name');
$c = ctrl(); ob_start(); $c->view($no2); ob_end_clean();
$detail = render('view', $GLOBALS['viewCalled'][1]);
check(strpos($detail, 'Proses approval') !== false && strpos($detail, 'Item baru') !== false && strpos($detail, 'Bulanan') !== false && strpos($detail, 'Tgl 10') !== false, 'view partial: new line summary and decide button');
$c = ctrl(); $c->proses($no2);
$form = render('form', $c->template->data);
check(strpos($form, 'Approval Perubahan Budget') !== false && substr_count($form, 'data-v="A"') === 1 && strpos($form, 'Simpan keputusan') !== false && strpos($form, 'Reject semua') !== false, 'form: approve/reject toggle per line and actions');
check(strpos($form, '<b>') === false || strpos($form, 'Listrik &lt;b&gt;') !== false, 'form: item name escaped');
js_syntax($form, 'form');
check(strpos(render('view', ['bg' => null, 'dept_name' => '', 'can_decide' => false]), 'Data tidak ditemukan') !== false, 'view partial: missing BG message');
// sebelum/sesudah per field di modal dan form keputusan
$c = ctrl(); ob_start(); $c->view($no); ob_end_clean();
$decidedModal = render('view', $GLOBALS['viewCalled'][1]);
check(strpos($decidedModal, '<span class="v old">1.500.000</span>') !== false && strpos($decidedModal, '<span class="v new">1.750.000</span>') !== false && strpos($decidedModal, '<small>Perkiraan biaya</small>') !== false, 'modal: before shown as old chip, after as new chip, with the field label');
check(strpos($decidedModal, 'pill A">Selesai') !== false, 'modal: decided BG status pill');
check(strpos($detail, '<span class="v none">-</span>') !== false && strpos($detail, '<small>Jadwal</small><span class="v new">Tgl 10</span>') !== false && strpos($detail, '<small>Masa berlaku</small><span class="v new">Selalu</span>') !== false, 'modal: new item before is a dash, after lists type/schedule/cost/masa chips');
$schedBg = $model->GetBgs('DEP-2', 'pending')[$no2];
check(strpos($form, '<span class="v new">') !== false && strpos($form, '<span class="v none">-</span>') !== false, 'form: same before/after chips');
$xssLine = ['kind' => 'new', 'id' => null, 'before' => null, 'fields' => [], 'after' => ['id' => null, 'coa' => '1', 'nama' => '<img src=x onerror=1>', 'tipe' => 'tahun', 'tanggal' => '<b>', 'nilai' => 1, 'masa_berlaku' => 0, 'masa_awal' => 0, 'aktif' => 1]];
$xssHtml = apb_line_html($xssLine, 'after');
check(strpos($xssHtml, '<img') === false && strpos($xssHtml, '&lt;img src=x onerror=1&gt;') !== false, 'modal: item name is escaped');
check(strpos($xssHtml, '<b><b>') === false && strpos($xssHtml, '&lt;b&gt;') !== false, 'modal: odd schedule text is escaped');
$cssText = file_get_contents(APPPATH . '../assets/css/periodik_ui.css');
check(strpos($cssText, '.pjx .pill.W{background:#fdf0d5;color:#8a5200') !== false && strpos($cssText, '.pjx .grid2 .pill{display:inline-flex') !== false && strpos($cssText, '.pjx .v.old') !== false, 'css: soft pills with readable contrast, pills inside .grid2 stay compact, diff chips');

echo "PASS Approval_perubahan_budget controller/views: {$checks} assertions\n";
