<?php
define('BASEPATH', __DIR__);

require_once dirname(__DIR__, 2).'/record_request_payment/libraries/Rp_transfer_record.php';

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $GLOBALS['checks']++;
}

function rejected(callable $call)
{
    try {
        $call();
    } catch (DomainException $e) {
        return true;
    }
    return false;
}

$checks = 0;
$transport = (object) ['kategori' => 'Transport'];
$expense = (object) ['kategori' => 'Expense'];
$refill = (object) ['kategori' => 'Refill Petty Cash'];
$debt = (object) ['kategori' => 'Petty Cash Hutang'];
$group = ['docs' => ['RQ-1','EXP-1'], 'bank' => 'BCA', 'no_rek' => '001.234-5', 'nama' => 'Vendor', 'admin' => '3500.50', 'bearer' => 'company'];
check(Rp_transfer::plan([$group], ['RQ-1' => $transport, 'EXP-1' => $expense, 'PHP-1' => $debt])[0] === [
    'bank' => 'BCA', 'no_rek' => '001.234-5', 'nama' => 'Vendor', 'docs' => ['RQ-1','EXP-1'], 'bearer' => 'company', 'admin' => '3500.50',
], 'Groups across categories, preserves account formatting and excludes legacy debt');
check(rejected(fn() => Rp_transfer::plan([$group, $group], ['RQ-1' => $transport, 'EXP-1' => $expense])), 'Duplicate grouping rejected');
check(rejected(fn() => Rp_transfer::plan([], ['RQ-1' => $transport])), 'Missing membership rejected');
check(rejected(fn() => Rp_transfer::plan([$group], ['RQ-1' => $refill, 'EXP-1' => $expense])), 'Refill cannot join other docs');
check(rejected(fn() => Rp_transfer::account(['bank' => 'BCA', 'no_rek' => 'abc', 'nama' => 'Vendor'])), 'Non-number account rejected');
check(rejected(fn() => Rp_transfer::uploadFiles(['name' => 'file.pdf'])), 'Malformed upload rejected');
check(Rp_transfer::taxes('27010000.01', true, false, true) === [
    'nilai_ppn' => '2971101.00', 'nilai_pph' => '540201.00', 'admin' => 0, 'dibayarkan' => '29440900.01',
], 'PPN and PPh23 use the unrounded source DPP and ceil per document');
check(Rp_transfer::taxes('5214499.00', false, true, false)['nilai_pph'] === '130363.00', 'PPh 21 calculation matches fixture');
check(rejected(fn() => Rp_transfer::totals(2000, 2001, 'recipient')), 'Partial reject cannot leave negative payment');

$first = [
    'dpp' => '894000.00', 'nilai_ppn' => '0.00', 'nilai_pph' => '0.00', 'flag_pph23' => 0, 'flag_pph21' => 0,
    'dibayarkan' => '894000.00', 'no_dokumen' => 'RQ-1', 'kategori' => 'Transport', 'request_by' => 'Staff', 'keperluan' => 'Asli', 'keterangan_finance' => '=12345',
];
$second = array_replace($first, ['no_dokumen' => 'EXP-1', 'kategori' => 'Expense', 'dpp' => '185000.00', 'dibayarkan' => '185000.00']);
$transfer = array_replace($group, [
    'company_id' => '7', 'company_nama' => 'STM', 'no_pengajuan' => 'PGJ-0001', 'urutan' => 1,
    'docs' => [$first, $second], 'admin' => '6500.00', 'bearer' => 'recipient',
]);
$actual = Rp_transfer_payment::actual($transfer, []);
check($actual['bank'] === 107900000 && $actual['transfer'] === 107250000 && $actual['adjustment'] === 0 && !$actual['difference'], 'Recipient covers admin once');
$actual = Rp_transfer_payment::actual($transfer, ['actual_bearer' => 'free']);
check($actual['charge'] === 0 && $actual['transfer'] === 107900000 && $actual['difference'], 'Fee waiver matches mockup');
$actual = Rp_transfer_payment::actual($transfer, ['actual_bearer' => 'company', 'bank_charge' => '3000.00']);
check($actual['bank'] === 108200000 && $actual['adjustment'] === 300000, 'Company fee debited once');
$actual = Rp_transfer_payment::actual($transfer, ['bank_charge' => '2500.00', 'payment_bank' => '1075000.00']);
check($actual['adjustment'] === -400000 && $actual['difference'], 'Recipient fee difference becomes admin credit');
check(rejected(fn() => Rp_transfer_payment::actual($transfer, ['payment_bank' => '1.00'])), 'Control mismatch rejected');
check(rejected(fn() => Rp_transfer_record::filters(['from' => '2026-02-30'])), 'Malformed calendar date rejected');
$compare = Rp_transfer_record::comparison($transfer + ['bank_charge' => '0.00', 'payment_bank' => '1079000.00', 'actual_bearer' => 'free']);
check(count($compare['differences']) === 3 && $compare['actualBank'] === $actual['net'], 'History separates wire amount, fee amount and total bank');

class JournalResult
{
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result_array() { return $this->rows; }
}
class JournalFixtureDb
{
    private $table;
    public function select($fields) { return $this; }
    public function where($field, $value)
    {
        if ($field === 'a.deleted') {
            check($value === '0', "ms_bank.deleted is ENUM('0','1'): integer 0 matches no row");
        }
        return $this;
    }
    public function from($table) { $this->table = $table; return $this; }
    public function join($table, $on, $kind) { return $this; }
    public function order_by($field) { return $this; }
    public function where_in($field, $values) { return $this; }
    public function get($table = null)
    {
        if (($table ?? $this->table) === 'ms_bank a') {
            return new JournalResult([['id' => '5', 'nama' => 'PT. SENTRAL TEHNOLOGI MANAGEMEN', 'rekening' => '001', 'coa_bank' => '1101-02-09', 'nama_bank' => 'BCA']]);
        }
        return new JournalResult(array_map(static function ($coa) { return ['no_perkiraan' => $coa, 'nama' => 'COA ' . $coa]; },
            ['1101-02-09', '7201-01-04', '6202-01-05']));
    }
}
$finance = (object) [
    'db' => new JournalFixtureDb(),
    'load' => new class {
        public function database($target, $connect)
        {
            check($target === 'accounting_stm' && $connect, 'Company-specific ledger COA resolved');
            return new JournalFixtureDb();
        }
    },
    'Pembayaran_material_model' => new class {
        public function set_jurnal(&$summary, $post)
        {
            check($post['id_payment'] === 'RPA-1,RPA-2' && $post['item_ppn'] === ['RPA-1' => '0', 'RPA-2' => '0'], 'Legacy source composer supplies principal only');
            $base = ['id_divisi' => '1', 'nm_divisi' => 'Finance', 'coa' => '6202-01-05', 'keterangan' => 'Biaya', 'kredit' => 0];
            return [$base + ['reference' => 'RPA-1', 'debit' => 894000], $base + ['reference' => 'RPA-2', 'debit' => 185000]];
        }
    },
];
$transfer['docs'][0] += ['payment_status' => '1', 'payment_approval_id' => 'RPA-1', 'voucher' => null];
$transfer['docs'][1] += ['payment_status' => '1', 'payment_approval_id' => 'RPA-2', 'voucher' => null];
$journal = Rp_transfer_payment::journal($finance, $transfer, ['tgl_bayar' => '2026-11-24', 'bank' => '5', 'bank_charge' => '2500.00']);
$byCoa = array_column($journal['rows'], null, 'coa');
check(count($journal['rows']) === 4 && $byCoa['1101-02-09']['kredit'] === '1075000.00', 'Combined transfer uses one bank row');
check($byCoa['7201-01-04']['kredit'] === '4000.00', 'Negative admin variance appears on the credit side');
check($journal['rows'][0]['keterangan'] === 'Biaya - RQ-1' && $journal['rows'][1]['keterangan'] === 'Biaya - EXP-1', 'Journal description uses real document numbers');
Payment_journal_validator::validate(array_map(static function ($row) { return $row + ['no_transaksi' => 'BK--1126-0001']; }, $journal['rows']));
$transfer['docs'][0]['payment_status'] = '2';
check(rejected(fn() => Rp_transfer_payment::journal($finance, $transfer, ['tgl_bayar' => '2026-11-24', 'bank' => '5'])), 'Paid document blocks retry');

require_once dirname(__DIR__, 3).'/libraries/PHPExcel.php';
$book = new PHPExcel();
$sheet = $book->getActiveSheet();
Rp_transfer_record::exportPlan($sheet, [$transfer], 'Pengajuan Pembayaran', 'STM');
check($sheet->getCell('M3')->getValue() === 'JUMLAH DIBAYARKAN BANK', 'Plan Cash Flow header');
check((float) $sheet->getCell('M4')->getValue() === 1079000.0 && isset($sheet->getMergeCells()['M4:M5']), 'Merged cash figure counted once');
check($sheet->getCell('F4')->getDataType() === PHPExcel_Cell_DataType::TYPE_STRING, 'User text cannot become a spreadsheet formula');
check($sheet->getCell('R4')->getValue() === 'Penerima (Rp 6.500 dipotong dari transfer)', 'Admin bearer shows amount like mockup');
check($sheet->getCell('S4')->getValue() === 'Pengajuan Transport + Expense. Digabung jadi 1 payment: 2 dokumen (RQ-1, EXP-1), 1x transfer ke Vendor', 'Merged transfer note matches mockup');
$notes = array_filter(range(1, $sheet->getHighestRow()), static function ($r) use ($sheet) { return strpos((string) $sheet->getCell('A' . $r)->getValue(), 'Catatan: semua angka positif') === 0; });
check(count($notes) === 1, 'Excel note row present once');
$refillSheet = (new PHPExcel())->getActiveSheet();
$refillTransfer = array_replace($transfer, ['admin' => '0.00', 'docs' => [array_replace($first, ['kategori' => 'refill_pettycash'])]]);
Rp_transfer_record::exportPlan($refillSheet, [$refillTransfer], 'Pengajuan Pembayaran', 'STM');
check($refillSheet->getCell('C4')->getValue() === 'Refill Pettycash' && strpos($refillSheet->getCell('S4')->getValue(), 'Pengajuan Refill Pettycash.') === 0, 'Refill category label matches mockup');
$last = $sheet->getHighestRow();
check($sheet->getCell('I' . ($last - 3))->getDataType() === PHPExcel_Cell_DataType::TYPE_FORMULA, 'Saldo formula exists');
$file = tempnam(sys_get_temp_dir(), 'rp-test-');
try {
    PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save($file);
    $xlsx = new ZipArchive();
    check($xlsx->open($file) === true && strpos($xlsx->getFromName('xl/worksheets/sheet1.xml'), 'M4:M5') !== false, 'Actual XLSX contains merged cells');
    $xlsx->close();
} finally {
    unlink($file);
}

$originalFlag = getenv('RP_TRANSFER_ENABLED');
putenv('RP_TRANSFER_ENABLED');
check(!function_exists('config_item') && !Rp_transfer::enabled(), 'Transfer mode off without env or CodeIgniter config');
putenv('RP_TRANSFER_ENABLED=1');
check(Rp_transfer::enabled(), 'Env flag enables transfer mode');
putenv('RP_TRANSFER_ENABLED');
$rpConfig = null;
if (!function_exists('config_item')) { // conditional so it is not hoisted before the no-config check above
    function config_item($item) { return $item === 'rp_transfer_enabled' ? $GLOBALS['rpConfig'] : null; }
}
foreach ([[null, false], [false, false], ['0', false], ['yes', false], [true, true], [1, true], ['1', true]] as [$value, $expected]) {
    $rpConfig = $value;
    check(Rp_transfer::enabled() === $expected, 'Server config rp_transfer_enabled=' . var_export($value, true));
}
$originalFlag === false ? putenv('RP_TRANSFER_ENABLED') : putenv('RP_TRANSFER_ENABLED=' . $originalFlag);

echo "PASS Request Payment transfer: {$checks} checks\n";
