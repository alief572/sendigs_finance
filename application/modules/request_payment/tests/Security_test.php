<?php
// Regresi audit 25-Nov-2026: SQL injection dan upload. Model asli, database tiruan.
define('BASEPATH', __DIR__);
define('DBACC', 'fixture_accounting');
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
class BF_Model {}
require dirname(__DIR__) . '/models/Request_payment_model.php';

class CaptureSql extends Exception {}
class CaptureDb {
    public function escape($value) { return "'" . addslashes($value) . "'"; }
    public function escape_like_str($value) { return str_replace(array('!', '%', '_'), array('!!', '!%', '!_'), addslashes($value)); }
    public function query($sql) { throw new CaptureSql($sql); }
}
function captured_sql(callable $call) {
    try { $call(); } catch (CaptureSql $e) { return $e->getMessage(); }
    throw new RuntimeException('No query was run');
}
$model = (new ReflectionClass('Request_payment_model'))->newInstanceWithoutConstructor();
$model->db = new CaptureDb();

$evil = "x' OR '1'='1";
$safe = "x\\' OR \\'1\\'=\\'1";
foreach (array(null, 'transport', 'kasbon', 'expense', 'periodik') as $tab) {
    foreach (array(array($evil, '2026-01-01'), array('2026-01-01', $evil)) as $dates) {
        $sql = captured_sql(function () use ($model, $tab, $dates) { $model->GetListDataRequest($tab, $dates[0], $dates[1]); });
        check(strpos($sql, $evil) === false && strpos($sql, $safe) !== false, 'Request date must be escaped: ' . var_export($tab, true));
    }
}
foreach (array(array($evil, '', ''), array('', $evil, ''), array($evil, $evil, ''), array('', '', $evil)) as $args) {
    $sql = captured_sql(function () use ($model, $args) { $model->search_payment_list($args[0], $args[1], $args[2]); });
    check(strpos($sql, $evil) === false && strpos($sql, $safe) !== false, 'Payment search input must be escaped');
}
$sql = captured_sql(function () use ($model) { $model->search_payment_list('', '', '50%'); });
check(strpos($sql, "LIKE '%50!%%' ESCAPE '!'") !== false, 'Bank wildcard must be literal');
$sql = captured_sql(function () use ($model) { $model->search_payment_list('2026-01-01', '2026-01-31', ''); });
check(strpos($sql, "BETWEEN '2026-01-01' AND '2026-01-31'") !== false, 'Normal date range unchanged');

$controller = file_get_contents(dirname(__DIR__) . '/controllers/Request_payment.php');
check(!preg_match('/\$(no_doc|ids)\[\$keys\]\s*\./', $controller), 'save_payment input must not be concatenated into SQL');
check(substr_count($controller, 'array($no_doc[$keys])') === 1 && substr_count($controller, 'array($ids[$keys], $no_doc[$keys])') === 1, 'save_payment must bind parameters');

preg_match_all("/\['allowed_types'\]\s*=\s*'([^']*)'/", $controller, $uploads);
check(count($uploads[1]) === 2, 'Both upload configs found');
foreach ($uploads[1] as $types) {
    check($types !== '*' && !array_diff(explode('|', $types), array('pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx')), 'Upload types must be a document/image whitelist');
}
$root = dirname(__DIR__, 4);
check(strpos((string) @file_get_contents($root . '/assets/expense/.htaccess'), 'Require all denied') !== false, 'Upload folder must deny scripts');
check(strpos(file_get_contents(dirname(__DIR__, 2) . '/request_payment_backup/controllers/Request_payment.php'), 'show_404();') !== false, 'Backup module must stay blocked');
echo "Security_test: OK\n";
