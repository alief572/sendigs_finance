<?php
// Regresi: company petty cash 'SUSTAIN'/'VUCA' (huruf besar di sumber) harus ter-resolve ke id + nama kanonik kons.
define('BASEPATH', __DIR__);
class BF_Model { public function __construct() {} public function __get($n) { return null; } }
require dirname(__DIR__) . '/models/Request_payment_model.php';

class Company_case_db {
    public $company;
    public function select($f) { return $this; }
    public function error() { return array("code" => 0, "message" => ""); }
    public function get_where($t, $w) { return new Company_case_res($t === 'tr_petty_cash_vuca_sustain' ? $this->company : null); }
}
class Company_case_res {
    private $c; public function __construct($c) { $this->c = $c; }
    public function row() { return $this->c === null ? null : (object) array('company' => $this->c); }
}
class Company_case_model extends Request_payment_model {
    public function __construct() { $this->db = new Company_case_db(); }
    public function get_company_names_lookup() { return array('3' => 'Sustain', '4' => 'Vuca', '7' => 'STM'); }
    public function resolve_consultant_company($r) { return array('company_id' => null, 'company_nama' => ''); }
}

$m = new Company_case_model();
foreach (array('SUSTAIN' => array('3', 'Sustain'), 'VUCA' => array('4', 'Vuca'), 'STM' => array('7', 'STM')) as $src => $exp) {
    $m->db->company = $src;
    $r = $m->resolve_row_company((object) array('no_dokumen' => 'PHP-1', 'kategori' => 'Petty Cash Hutang'));
    if ($r['company_id'] !== $exp[0] || $r['company_nama'] !== $exp[1]) {
        fwrite(STDERR, "FAIL $src: " . json_encode($r) . "\n");
        exit(1);
    }
}
echo "PASS Company case-insensitive resolve: 3 scenarios\n";
