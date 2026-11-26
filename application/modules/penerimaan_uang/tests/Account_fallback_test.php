<?php
// Uji Consulting_receipt::account: COA opsional (Deposit Customer) jatuh ke nama bawaan, bank/admin tetap wajib, ambigu ditolak.
define('BASEPATH', __DIR__);
require_once dirname(__DIR__) . '/libraries/Consulting_receipt.php';

$stub = new class { public $rows = array(); public function master($owner) { return $this->rows; } };
$engine = (new ReflectionClass('Consulting_receipt'))->newInstanceWithoutConstructor();
$prop = new ReflectionProperty('Consulting_receipt', 's'); $prop->setAccessible(true); $prop->setValue($engine, $stub);
$call = new ReflectionMethod('Consulting_receipt', 'account'); $call->setAccessible(true);
$fails = function (array $args) use ($call, $engine) { try { $call->invokeArgs($engine, $args); return null; } catch (RuntimeException $e) { return $e->getMessage(); } };

$stub->rows = array(array('no_perkiraan' => '1101-02-01', 'nama' => 'Bank BCA'));
assert($call->invoke($engine, '7', '1101-02-01') === array('code' => '1101-02-01', 'name' => 'Bank BCA'));
assert($call->invoke($engine, '7', '2102-01-02', 'Deposit Customer') === array('code' => '2102-01-02', 'name' => 'Deposit Customer'));
assert($fails(array('7', '7201-01-04')) === 'COA 7201-01-04 tidak tersedia atau ambigu pada accounting tujuan.');

$stub->rows[] = array('no_perkiraan' => '2102-01-02', 'nama' => 'A'); $stub->rows[] = array('no_perkiraan' => '2102-01-02', 'nama' => 'B');
assert($fails(array('7', '2102-01-02', 'Deposit Customer')) !== null);
echo "PASS: account fallback\n";
