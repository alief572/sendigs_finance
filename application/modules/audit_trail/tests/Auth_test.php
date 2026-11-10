<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__, 2) . '/users/libraries/Auth.php';
require dirname(__DIR__, 2) . '/users/controllers/Users.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function &get_instance() { return $GLOBALS['ci']; }
function lang($key) { return $key; }
function audit_log($message, $status = null, $context = array()) {
    $GLOBALS['events'][] = array('message' => $message, 'status' => $status, 'context' => $context,
        'session' => $GLOBALS['ci']->session->data);
    return true;
}
function redirect($uri) { throw new AuthRedirect($uri); }
class AuthRedirect extends RuntimeException {}
class Front_Controller {}
class AuthLoader { public function library($name) {} public function model($name) {} }
class AuthLanguage { public function load($name) {} }
class AuthTemplate { public function set_message($message, $type) {} }
class AuthSession {
    public $data = array();
    public function userdata($key) { return isset($this->data[$key]) ? $this->data[$key] : null; }
    public function set_userdata($key, $value) { $this->data[$key] = $value; }
    public function set_flashdata($key, $value) {}
    public function sess_destroy() { $this->data = array(); }
}
class AuthInput {
    public $otp = '123456';
    public function ip_address() { return '127.0.0.1'; }
    public function post($key) { return $this->otp; }
}
class AuthUsersModel {
    public $user;
    public function find_by($where) { return $this->user; }
    public function find($id) { return $this->user; }
    public function update($id, $data) { return true; }
}
class AuthDb {
    public function get_where($table, $where) { return $this; }
    public function row() { return $GLOBALS['ci']->users_model->user; }
}
class PHPGangsta_GoogleAuthenticator {
    public function verifyCode($secret, $otp, $tolerance) { return $GLOBALS['otp_valid']; }
}
class TestUsers extends Users {
    public function __construct() {
        $ci = $GLOBALS['ci'];
        $this->input = $ci->input; $this->db = $ci->db; $this->session = $ci->session; $this->auth = $ci->auth;
    }
}
function fixture($twoFactor = false) {
    $model = new AuthUsersModel();
    $model->user = (object) array('id_user' => 7, 'username' => 'admin', 'password' => password_hash('correct', PASSWORD_DEFAULT),
        'deleted' => 0, 'st_aktif' => 1, 'ga_secret' => $twoFactor ? 'NEVER_LOG_SECRET' : null, 'nm_lengkap' => 'Admin', 'login_terakhir' => null);
    $GLOBALS['ci'] = (object) array('load' => new AuthLoader(), 'session' => new AuthSession(), 'lang' => new AuthLanguage(),
        'template' => new AuthTemplate(), 'input' => new AuthInput(), 'users_model' => $model, 'db' => new AuthDb());
    $GLOBALS['events'] = array();
    $auth = new Auth(); $GLOBALS['ci']->auth = $auth;
    return $auth;
}
function invoke_redirect($callback) { try { $callback(); } catch (AuthRedirect $redirect) {} }
$auth = fixture(); $GLOBALS['ci']->users_model->user = false;
check($auth->login('missing', 'wrong') === false && $GLOBALS['events'][0]['status'] === 0 && $GLOBALS['events'][0]['context']['created_by'] === null, 'Unknown account must be logged with explicit anonymous identity');
foreach (array('deleted', 'st_aktif') as $field) {
    $auth = fixture(); $GLOBALS['ci']->users_model->user->$field = $field === 'deleted' ? 1 : 0;
    check($auth->login('admin', 'correct') === false && count($GLOBALS['events']) === 1 && $GLOBALS['events'][0]['status'] === 0, 'Disabled account outcome missing');
}
$auth = fixture();
check($auth->login('admin', 'wrong') === false && $GLOBALS['events'][0]['status'] === 0, 'Wrong password outcome missing');
$auth = fixture(); invoke_redirect(function () use ($auth) { $auth->login('admin', 'correct'); });
check(count($GLOBALS['events']) === 1 && $GLOBALS['events'][0]['context']['methode'] === 'LOGIN', 'Non-2FA login must be recorded once');
$auth = fixture(true); invoke_redirect(function () use ($auth) { $auth->login('admin', 'correct'); });
check($GLOBALS['events'][0]['context']['methode'] === 'PASSWORD_ACCEPTED', '2FA must not be reported complete before OTP');
$GLOBALS['otp_valid'] = false;
invoke_redirect(function () { (new TestUsers())->check_otp(); });
check($GLOBALS['events'][1]['status'] === 0 && $GLOBALS['events'][1]['context']['methode'] === 'VERIFY_2FA', 'Invalid OTP outcome missing');
$GLOBALS['otp_valid'] = true;
invoke_redirect(function () { (new TestUsers())->check_otp(); });
check($GLOBALS['events'][2]['status'] === 1 && $GLOBALS['events'][2]['context']['methode'] === 'LOGIN', 'Successful OTP must complete login');
$GLOBALS['ci']->input->otp = '';
invoke_redirect(function () { (new TestUsers())->check_otp(); });
check($GLOBALS['events'][3]['status'] === 0, 'Missing OTP outcome missing');
invoke_redirect(function () use ($auth) { $auth->logout(); });
$last = end($GLOBALS['events']);
check($last['context']['methode'] === 'LOGOUT' && isset($last['session']['app_session']) && $GLOBALS['ci']->session->data === array(), 'Logout identity must be captured before destroying session');
foreach ($GLOBALS['events'] as $event) {
    check(strpos(json_encode(array($event['message'], $event['context'])), 'NEVER_LOG_SECRET') === false && !isset($event['context']['password']) && !isset($event['context']['otp']), 'Authentication secrets leaked');
}
$auth = fixture(true);
invoke_redirect(function () use ($auth) { $auth->login('admin', 'correct'); });
$GLOBALS['ci']->users_model->user->ga_secret = null;
invoke_redirect(function () { (new TestUsers())->check_otp(); });
check(count($GLOBALS['events']) === 2 && $GLOBALS['events'][1]['status'] === 0
    && $GLOBALS['events'][1]['context']['methode'] === 'VERIFY_2FA', 'Missing 2FA secret must record failure');
echo "PASS: real Auth and OTP flows record truthful outcomes once, without credentials\n";
