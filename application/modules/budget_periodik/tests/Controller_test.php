<?php
// Controller + view Budget_periodik di atas model sungguhan (SQLite). Perubahan budget harus menjadi BG pending dan
// tidak pernah mengubah master. Jalankan: scratch/runtime/php74/php.exe application/modules/budget_periodik/tests/Controller_test.php
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
function simpan_aktifitas($a, $b, $c, $d, $e, $status) { $GLOBALS['activity'][] = array($c, $status); }
class BF_Model { public function __construct() {} }
class Stub_Input { public $values = array(); public function post($k = null) { return $k === null ? $this->values : (isset($this->values[$k]) ? $this->values[$k] : null); } }
class Stub_Auth { public function restrict($p) { if (!empty($GLOBALS['deny'][$p])) { throw new RuntimeException('denied ' . $p); } } public function user_name() { return 'admin.dep1'; } public function user_id() { return 7; } }
class Stub_Out { public function set_content_type($t) { return $this; } }
class Stub_Template { public $data = array(); public $view = ''; public function set($k, $v = null) { $this->data[$k] = $v; } public function title($t = '') {} public function page_icon($t = '') {} public function render($v) { $this->view = $v; } }
class Stub_Loader { public function model($m) {} public function view($name, $data = array()) { $GLOBALS['viewCalled'] = array($name, $data); echo 'VIEW:' . $name; } }
class Admin_Controller { public $load, $template, $auth, $input, $output, $db; public function __construct() { $this->load = new Stub_Loader(); $this->template = new Stub_Template(); } }
class Stub_All { public function GetDeptCombo($k = '') { return array('' => '', 'DEP-1' => 'Finance - STM', 'DEP-2' => 'GA - STM', 'DEP-3' => 'Marketing - STM'); }
	public function GetCoaCombo() { return array('6101' => '6101 - Beban Sewa', '6102' => '6102 - Beban Langganan'); } }
require BASEPATH . 'database/DB.php';
require dirname(__DIR__, 3) . '/helpers/periodik_helper.php';
require APPPATH . 'modules/budget_periodik/models/Budget_periodik_model.php';
require APPPATH . 'modules/budget_periodik/controllers/Budget_periodik.php';

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
function master($real, $dept, $nama, $nilai, $extra = []) {
	$real->insert('ms_budget_rutin', array_merge(['departement' => $dept, 'coa' => '6101', 'nama' => $nama, 'tipe' => 'bulan', 'tanggal' => '25', 'nilai' => $nilai, 'keterangan' => 'baseline lama', 'kode_id' => '', 'masa_berlaku' => 0, 'masa_awal' => 0, 'aktif' => 1], $extra));
	return $real->insert_id();
}
$sewa = master($real, 'DEP-1', 'Sewa Kantor', 1500000);
$cicilan = master($real, 'DEP-1', 'Cicilan Laptop', 800000, ['masa_berlaku' => 5, 'masa_awal' => 1]);
$mati = master($real, 'DEP-1', 'Langganan Lama', 100000, ['aktif' => 0]);
$listrik = master($real, 'DEP-2', 'Listrik', 300000);
$real->insert('tr_pengajuan_rutin_detail', ['id_budget' => $cicilan, 'item_status' => 1]);

function ctrl() {
	global $db;
	$c = new Budget_periodik();
	$c->db = $db;
	$c->input = new Stub_Input();
	$c->auth = new Stub_Auth();
	$c->output = new Stub_Out();
	$c->All_model = new Stub_All();
	$c->Budget_periodik_model = (new ReflectionClass('Budget_periodik_model'))->newInstanceWithoutConstructor();
	$c->Budget_periodik_model->db = $db;
	$GLOBALS['activity'] = array();
	$GLOBALS['deny'] = array();
	return $c;
}
function save($c, $dept, $rows) { $c->input->values = ['departement' => $dept, 'rows' => is_string($rows) ? $rows : json_encode($rows)]; ob_start(); $c->save_data(); return json_decode(ob_get_clean(), true); }
function table_dump($real, $t) { return json_encode($real->get($t)->result_array()); }
function draft_of($real, $dept) { return array_map(function ($r) { return Budget_logic::normalize($r); }, $real->get_where('ms_budget_rutin', ['departement' => $dept])->result_array()); }

// ---- index
$c = ctrl();
$c->index();
check($c->template->view === 'list' && !array_key_exists('rows', $c->template->data) && $c->template->data['can_add'] === true, 'index: renders the shell only (rows come from get_data)');
$dd = json_decode($c->template->data['dd_items'], true);
check(count($dd) === 3 && array_column($dd, 'sub', 'id')['DEP-3'] === 'Belum ada budget' && strpos(array_column($dd, 'sub', 'id')['DEP-1'], 'Sudah ada 3 item') === 0, 'index: + New lists every department with its state');
$GLOBALS['deny'] = ['Pembayaran_Periodik.View' => true];
try { $c->index(); check(false, 'index requires View'); } catch (RuntimeException $e) { check(strpos($e->getMessage(), 'denied') === 0, 'index requires View'); }

// ---- get_data (DataTables server-side)
function dt($c, $post) { $c->input->values = $post + ['draw' => 3, 'start' => 0, 'length' => 10, 'search' => ['value' => ''], 'order' => [['column' => 1, 'dir' => 'asc']]]; ob_start(); $c->get_data(); return json_decode(ob_get_clean(), true); }
$c = ctrl();
$r = dt($c, []);
check($r['draw'] === 3 && $r['recordsTotal'] === 2 && $r['recordsFiltered'] === 2 && count($r['data']) === 2, 'get_data: only departments that have a budget, DataTables envelope');
$byDept = array_column($r['data'], null, 'department');
check(array_keys($byDept) === ['Finance - STM', 'GA - STM'] && $r['data'][0]['DT_RowAttr'] === ['data-dept' => 'DEP-1'] && $r['data'][0]['no'] === 1 && $r['data'][1]['no'] === 2, 'get_data: rows sorted by name with row numbers and data-dept attribute');
check($byDept['Finance - STM']['aktif'] === '2<span class="sub">dari 3 item</span>' && $byDept['Finance - STM']['bulan'] === '2.300.000' && $byDept['Finance - STM']['tahun'] === '0', 'get_data: summary counts active items only (inactive excluded), Indonesian number format');
check(strpos($byDept['Finance - STM']['status'], 'pill A') !== false && strpos($byDept['Finance - STM']['action'], 'data-view="DEP-1"') !== false && strpos($byDept['Finance - STM']['action'], 'href="/base/budget_periodik/edit/DEP-1"') !== false, 'get_data: status pill and Lihat/Edit actions');
$GLOBALS['deny'] = ['Pembayaran_Periodik.Add' => true];
$r2 = dt($c, []);
check(strpos($r2['data'][0]['action'], 'Edit') === false && strpos($r2['data'][0]['action'], 'Lihat') !== false, 'get_data: no Edit action without Add permission');
$GLOBALS['deny'] = [];
$r2 = dt($c, ['search' => ['value' => 'ga']]);
check($r2['recordsTotal'] === 2 && $r2['recordsFiltered'] === 1 && $r2['data'][0]['department'] === 'GA - STM', 'get_data: search filters by department name (total stays, filtered drops)');
$r2 = dt($c, ['search' => ['value' => "' OR 1=1 --"]]);
check($r2['recordsFiltered'] === 0 && $r2['data'] === [], 'get_data: search injection string just matches nothing');
$r2 = dt($c, ['order' => [['column' => 3, 'dir' => 'desc']]]);
check(array_column($r2['data'], 'department') === ['Finance - STM', 'GA - STM'], 'get_data: order by monthly budget descending');
$r2 = dt($c, ['order' => [['column' => 3, 'dir' => 'asc']]]);
check(array_column($r2['data'], 'department') === ['GA - STM', 'Finance - STM'], 'get_data: order by monthly budget ascending');
$r2 = dt($c, ['order' => [['column' => 99, 'dir' => 'asc']]]);
check(count($r2['data']) === 2, 'get_data: unknown order column falls back to name');
$r2 = dt($c, ['start' => 1, 'length' => 1]);
check($r2['recordsFiltered'] === 2 && count($r2['data']) === 1 && $r2['data'][0]['no'] === 2 && $r2['data'][0]['department'] === 'GA - STM', 'get_data: paging (start 1, length 1) keeps numbering');
$r2 = dt($c, ['length' => 100000]);
check(count($r2['data']) === 2, 'get_data: oversized page length is capped, not trusted');
$GLOBALS['deny'] = ['Pembayaran_Periodik.View' => true];
try { dt($c, []); check(false, 'get_data requires View'); } catch (RuntimeException $e) { check(strpos($e->getMessage(), 'denied') === 0, 'get_data requires View'); }
$GLOBALS['deny'] = [];

// ---- create/edit: state untuk form
$c = ctrl();
$c->edit('DEP-1');
$st = json_decode($c->template->data['state'], true);
check($c->template->view === 'input_form' && $st['dept'] === 'DEP-1' && count($st['rows']) === 3 && $st['pending'] === null && $c->template->data['is_new_dept'] === false, 'edit: state with the department rows');
$byName = array_column($st['rows'], null, 'nama');
check($byName['Cicilan Laptop']['in_sys'] === 1 && $byName['Cicilan Laptop']['masa_awal'] === 1 && $byName['Cicilan Laptop']['masa_berlaku'] === 5 && !array_key_exists('keterangan', $byName['Sewa Kantor']), 'edit: usage included, Baseline column not exposed');
check($st['coa']['6101'] === '6101 - Beban Sewa', 'edit: COA options');
$c = ctrl();
$c->create('DEP-3');
check($c->template->data['is_new_dept'] === true && json_decode($c->template->data['state'], true)['rows'] === [], 'create: department without budget starts empty');

// ---- save_data: validasi dan penolakan
$base = draft_of($real, 'DEP-1');
$masterBefore = table_dump($real, 'ms_budget_rutin');
$c = ctrl();
$r = save($c, 'DEP-1', 'bukan json');
check($r['save'] === 0 && strpos($r['msg'], 'Data tidak valid') === 0, 'save: invalid JSON refused');
$r = save($c, 'DEP-1', array_merge($base, [array_merge($base[0], ['id' => $listrik])]));
check($r['save'] === 0 && strpos($r['msg'], 'tidak sesuai dengan departemen') !== false, 'save: id from another department refused');
$r = save($c, '', $base);
check($r['save'] === 0, 'save: department required');
$r = save($c, 'DEP-1', [['id' => '', 'nama' => '', 'nilai' => 0]]);
check($r['save'] === 0 && $r['msg'] === 'Isi minimal satu item budget sebelum mengajukan.', 'save: nothing filled');
$bad = $base; $bad[0]['nilai'] = 0;
$r = save($c, 'DEP-1', $bad);
check($r['save'] === 0 && strpos($r['msg'], 'harus lebih dari 0') !== false, 'save: cost must be > 0');
$bad = $base; foreach ($bad as &$b) { if ($b['id'] === (int) $cicilan) { $b['masa_berlaku'] = 1; } } unset($b);
$r = save($c, 'DEP-1', $bad);
check($r['save'] === 0 && $r['msg'] === 'Masa berlaku "Cicilan Laptop" tidak boleh kurang dari 2 kali.', 'save: masa below usage (1 before system + 1 approved)');
$r = save($c, 'DEP-1', $base);
check($r['save'] === 0 && $r['msg'] === 'Tidak ada perubahan untuk disimpan.', 'save: unchanged draft');
check($GLOBALS['activity'] && $GLOBALS['activity'][0][1] === 0, 'save: failures are logged with status 0');
check(table_dump($real, 'tr_budget_bg') === '[]' && table_dump($real, 'ms_budget_rutin') === $masterBefore, 'save: nothing written by any refused attempt');

// ---- save_data: berhasil menjadi BG pending, master tidak berubah
$edit = $base; foreach ($edit as &$b) { if ($b['id'] === (int) $sewa) { $b['nilai'] = 1750000; } if ($b['id'] === (int) $mati) { $b['aktif'] = 1; } } unset($b);
$edit[] = ['id' => '', 'coa' => '6102', 'nama' => 'Canva', 'tipe' => 'tahun', 'tanggal' => '11/15/2026', 'nilai' => 450000, 'masa_berlaku' => 3, 'masa_awal' => 0, 'aktif' => 1];
$edit[] = ['id' => '', 'nama' => '', 'nilai' => 0]; // baris kosong diabaikan
$c = ctrl();
$r = save($c, 'DEP-1', $edit);
check($r['save'] === 1 && $r['count'] === 3 && preg_match('/^BG-\d{4}-001$/', $r['no_bg']) === 1, 'save: three change lines (edit, on, new) under BG-YYMM-001');
check(table_dump($real, 'ms_budget_rutin') === $masterBefore, 'save: master untouched until approval');
$bg = $c->Budget_periodik_model->GetPendingBg('DEP-1');
check($bg['by'] === 'admin.dep1' && array_column($bg['lines'], 'kind') === ['edit', 'on', 'new'], 'save: BG pending with kinds in form order');
check($GLOBALS['activity'][count($GLOBALS['activity']) - 1][1] === 1, 'save: success logged');
$r = save($c, 'DEP-1', $edit);
check($r['save'] === 0 && strpos($r['msg'], 'Masih ada perubahan BG-') === 0 && strpos($r['msg'], 'Approval Perubahan Budget') !== false, 'save: second submission blocked while BG is pending');

// ---- form dengan BG pending: terkunci + info penolakan sebelumnya
$c = ctrl();
$c->edit('DEP-1');
$st = json_decode($c->template->data['state'], true);
check($st['pending']['no'] === $bg['no'] && count($st['pending']['lines']) === 3 && $st['pending']['lines'][2]['nama'] === 'Canva', 'edit: pending BG passed to the form (locks it)');
$ids = array_column($bg['lines'], 'id');
$c->Budget_periodik_model->DecideBg($bg['no'], [$ids[0] => ['R', 'Belum ada kontrak'], $ids[1] => ['A', ''], $ids[2] => ['A', '']], 'direktur');
$c = ctrl();
$c->edit('DEP-1');
$st = json_decode($c->template->data['state'], true);
check($st['pending'] === null && $st['rejected'][(string) $sewa]['note'] === 'Belum ada kontrak' && $st['rejected'][(string) $sewa]['no'] === $bg['no'], 'edit: last rejection shown on the item');
check(!isset($st['rejected'][(string) $mati]) && count($st['rows']) === 4, 'edit: approved changes applied (inactive item re-enabled, new item present)');

// ---- view (modal)
$c = ctrl();
ob_start(); $c->view('DEP-1'); $out = ob_get_clean();
check($out === 'VIEW:view' && $GLOBALS['viewCalled'][1]['sum']['n'] === 4 && count($GLOBALS['viewCalled'][1]['bgs']) === 1 && $GLOBALS['viewCalled'][1]['dept_name'] === 'Finance - STM', 'view: department rows and history passed to the partial');

// ---- render view sungguhan
function render($view, $vars) { extract($vars); ob_start(); include APPPATH . 'modules/budget_periodik/views/' . $view . '.php'; return ob_get_clean(); }
function js_syntax($html, $label) {
	preg_match_all('#<script>(.*?)</script>#s', $html, $m);
	$file = tempnam(sys_get_temp_dir(), 'bpv') . '.js';
	file_put_contents($file, implode("\n;\n", $m[1]));
	exec('node --check ' . escapeshellarg($file) . ' 2>&1', $out, $code);
	unlink($file);
	check($code === 0, "inline JS syntax ($label): " . implode(' ', $out));
}
$c = ctrl(); $c->index();
$html = render('list', $c->template->data);
$r = dt($c, []);
check(strpos($r['data'][0]['status'], 'Terakhir diputuskan ' . $bg['no']) !== false && strpos($r['data'][0]['aktif'], 'dari') === false, 'get_data: last decision shown on the status cell (4 of 4 items active now)');
check(strpos($html, 'id="pjNew"') !== false && strpos($html, 'id="pjTable"') !== false && strpos($html, '<tbody></tbody>') !== false, 'list: empty table skeleton and + New');
check(strpos($html, 'serverSide: true') !== false && strpos($html, "'budget_periodik/get_data'") !== false && strpos($html, 'jquery.dataTables.min.js') !== false, 'list: DataTables configured for server-side processing');
foreach (['Item aktif', 'Budget / bulan', 'Budget / tahun', 'Status budget', 'Action'] as $col) { check(strpos($html, '>' . $col . '<') !== false, "list: column $col"); }
js_syntax($html, 'budget list');
$noAdd = render('list', array_merge($c->template->data, ['can_add' => false, 'can_manage' => false]));
check(strpos($noAdd, 'id="pjNew"') === false && strpos($noAdd, 'id="pjProses"') === false && strpos($noAdd, 'class="btn o sm"') === false, 'list: no New/Edit/Proses without permissions');
$detail = render('view', $GLOBALS['viewCalled'][1]);
check(strpos($detail, 'Budget Finance - STM') !== false && strpos($detail, 'Riwayat perubahan') !== false && strpos($detail, 'Selalu') !== false && strpos($detail, '2 / 5 kali') !== false, 'view: heading, history and masa berlaku usage (1 before system + 1 approved = 2 of 5)');
check(strpos($detail, 'Rejected') !== false && strpos($detail, 'Approved') !== false && strpos($detail, 'Item baru') !== false, 'view: history shows each line outcome');
$c = ctrl(); $c->edit('DEP-1');
$form = render('input_form', $c->template->data);
check(strpos($form, 'tag chip mod">Diubah, belum disimpan') !== false && strpos($form, 'tag chip new">Item baru, belum disimpan') !== false && strpos($form, 'tag back">') === false, 'form: row badges use the dedicated chip classes, not the colliding .back');
$css = file_get_contents(APPPATH . '../assets/css/periodik_ui.css');
check(strpos($css, '.pjx .tag.chip.mod') !== false && strpos($css, '.pjx .tag.chip.new') !== false && strpos($css, '.pjx .tag.back{display:table') !== false, 'css: chip styles and the .tag.back collision fix exist');
check(strpos($form, 'Edit budget') !== false && strpos($form, 'Baseline') === false && strpos($form, 'Masa berlaku') !== false && strpos($form, 'Ajukan perubahan') !== false, 'form: edit heading, Masa berlaku column, no Baseline column');
js_syntax($form, 'budget form');
$c = ctrl(); $c->create('DEP-3'); $new = render('input_form', $c->template->data);
check(strpos($new, 'Tambah budget') !== false && strpos($new, 'Departemen ini belum punya budget') !== false, 'form: new department hint');
$xss = $c->template->data; $xss['dept_name'] = '<script>alert(1)</script>';
check(strpos(render('input_form', $xss), '<script>alert(1)</script>') === false, 'form: department name escaped');

// ---- masking nominal (fungsi money/maskMoney diambil dari view, dijalankan di Node dengan elemen palsu)
preg_match('#// BEGIN money(.*?)// END money#s', file_get_contents(APPPATH . '../assets/js/periodik_ui.js'), $fn);
$script = "var PJ = {};\n" . $fn[1] . "\nvar money = PJ.money, maskMoney = PJ.maskMoney;\n" . <<<'JS'

function el(value, caret) { return {value: value, selectionStart: caret === undefined ? value.length : caret, setSelectionRange: function (a) { this.caret = a; }}; }
var out = [], e;
e = el('1234567'); var n = maskMoney(e); out.push([e.value, n, e.caret]);
e = el('12a'); n = maskMoney(e); out.push([e.value, n, e.caret]);
e = el('12.3450', 4); n = maskMoney(e); out.push([e.value, n, e.caret]);
e = el('0012'); n = maskMoney(e); out.push([e.value, n, e.caret]);
e = el(''); n = maskMoney(e); out.push([e.value, n, e.caret]);
e = el('abc'); n = maskMoney(e); out.push([e.value, n, e.caret]);
e = el('1.500.000'); n = maskMoney(e); out.push([e.value, n, e.caret]);
e = el('1.000.0001'); n = maskMoney(e); out.push([e.value, n, e.caret]);
out.push([money(1500000), money(0), money(999), money(1000), money(1234567.6)]);
console.log(JSON.stringify(out));
JS;
$file = tempnam(sys_get_temp_dir(), 'mm') . '.js';
file_put_contents($file, $script);
exec('node ' . escapeshellarg($file) . ' 2>&1', $o, $code);
unlink($file);
$res = json_decode(implode('', $o), true);
check($code === 0 && is_array($res), 'masking: script runs: ' . implode(' ', $o));
check($res[0] === ['1.234.567', 1234567, 9], 'masking: digits get thousand separators, caret stays at the end');
check($res[1] === ['12', 12, 2], 'masking: non-digits are dropped');
check($res[2] === ['123.450', 123450, 3], 'masking: caret stays after the same digit when separators shift (1.2|34 -> 123|.450)');
check($res[3] === ['12', 12, 2] && $res[4] === ['', 0, 0] && $res[5] === ['', 0, 0], 'masking: leading zeros trimmed, empty and junk give empty/0');
check($res[6] === ['1.500.000', 1500000, 9] && $res[7] === ['10.000.001', 10000001, 10], 'masking: re-masking an already formatted value is stable');
check($res[8] === ['1.500.000', '0', '999', '1.000', '1.234.568'], 'masking: money() formats whole rupiah');
// ---- konversi tanggal tahunan: tersimpan MM/DD/YYYY, input type=date memakai YYYY-MM-DD (fungsi diambil dari view)
preg_match('#(function usToIso.*?function validUs[^\n]*)#s', $form, $dfn);
$dscript = $dfn[1] . <<<'JS'

console.log(JSON.stringify([usToIso('02/18/2026'), usToIso('01/01/2026'), usToIso('30'), usToIso(''), isoToUs('2026-09-30'), isoToUs(''), isoToUs('garbage'),
	validUs('02/29/2028'), validUs('02/29/2027'), validUs('13/01/2026'), validUs('30'), validUs('09/30/2026')]));
JS;
$dfile = tempnam(sys_get_temp_dir(), 'dt') . '.js';
file_put_contents($dfile, $dscript);
exec('node ' . escapeshellarg($dfile) . ' 2>&1', $dout, $dcode);
unlink($dfile);
check($dcode === 0 && json_decode(implode('', $dout), true) === ['2026-02-18', '2026-01-01', '', '', '09/30/2026', '', '', true, false, false, false, true], 'date conversion: real stored values show in the date input and round-trip back to MM/DD/YYYY: ' . implode(' ', $dout));
// ---- ringkasan jadwal (fungsi diambil dari view)
preg_match('#(function whenText.*?\n\t}\n)#s', $form, $wfn);
$wscript = "var MON = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];\n" . $wfn[1] . <<<'JS'

console.log(JSON.stringify([
	whenText({tipe: 'tahun', tanggal: '02/18/2026'}), whenText({tipe: 'tahun', tanggal: '09/30/2026'}), whenText({tipe: 'tahun', tanggal: ''}), whenText({tipe: 'tahun', tanggal: '30'}),
	whenText({tipe: 'bulan', tanggal: '25'}), whenText({tipe: 'bulan', tanggal: '31'}), whenText({tipe: 'bulan', tanggal: '0'}), whenText({tipe: 'bulan', tanggal: '32'}), whenText({tipe: 'bulan', tanggal: ''})]));
JS;
$wfile = tempnam(sys_get_temp_dir(), 'wt') . '.js';
file_put_contents($wfile, $wscript);
exec('node ' . escapeshellarg($wfile) . ' 2>&1', $wout, $wcode);
unlink($wfile);
check($wcode === 0 && json_decode(implode('', $wout), true) === ['Tiap tahun · 18 Feb', 'Tiap tahun · 30 Sep', '', '', 'Tiap bulan · tanggal 25', 'Tiap bulan · tanggal 31', '', '', ''], 'schedule summary: plain-language text, empty for odd legacy values: ' . implode(' ', $wout));
check(strpos($form, 'class="sched"') !== false && strpos($form, 'data-mwhen') !== false && strpos($form, 'Jadwal belum diatur') !== false && strpos($form, 'Dibayar tiap tahun pada tanggal ini') === false, 'form: schedule cell uses the new component with a live summary and a warning for unset values');
$css2 = file_get_contents(APPPATH . '../assets/css/periodik_ui.css');
check(strpos($css2, '.pjx .sched input[type=date]') !== false && strpos($css2, '.pjx .sched .when.warn') !== false && strpos($css2, '.pjx .sched input:focus') !== false, 'css: schedule component styles (date input, summary chip, warning, focus ring)');
check(strpos($form, 'schedChanged') !== false && strpos($form, "'01/01/' + S.baseYm.slice(0, 4)") !== false, 'form: legacy schedule only validated when touched; switching to Tahunan defaults to a full date');
check(strpos($form, 'type="number" min="0" step="any" data-mf="nilai"') === false && strpos($form, 'inputmode="numeric"') !== false && strpos($form, 'var money = PJ.money') !== false, 'form: cost input is a masked text field using the shared PJ.money, not type=number');

echo "PASS Budget_periodik controller/views: {$checks} assertions\n";
