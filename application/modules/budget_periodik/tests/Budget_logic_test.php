<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/budget_periodik/tests/Budget_logic_test.php
define('BASEPATH', __DIR__);
require __DIR__ . '/../libraries/Budget_logic.php';

$failures = 0;
function check($cond, $msg) { global $failures; if (!$cond) { $failures++; fwrite(STDERR, "FAIL: $msg\n"); } }
function row($extra = array()) {
	return array_merge(array('id' => 1, 'coa' => '6101', 'nama' => 'Sewa Kantor', 'tipe' => 'bulan', 'tanggal' => '25', 'nilai' => '1500000.00',
		'masa_berlaku' => '0', 'masa_awal' => '0', 'aktif' => '1', 'departement' => 'DEP-1', 'kode_id' => null, 'keterangan' => 'baseline lama'), $extra);
}

// normalize: tipe data dari DB (string) dan input form disamakan
$n = Budget_logic::normalize(row());
check($n['id'] === 1 && $n['nilai'] === 1500000.0 && $n['masa_berlaku'] === 0 && $n['aktif'] === 1 && $n['tipe'] === 'bulan', 'normalize casts DB strings');
check(!array_key_exists('keterangan', $n) && !array_key_exists('kode_id', $n), 'normalize drops Baseline and allocation columns');
check(Budget_logic::normalize(array('tipe' => 'aneh'))['tipe'] === 'bulan', 'unknown tipe falls back to bulan');
check(Budget_logic::normalize(array('masa_berlaku' => -3))['masa_berlaku'] === 0, 'negative masa clamped');

// is_blank_new
check(Budget_logic::is_blank_new(array('id' => '', 'nama' => '  ', 'nilai' => 0)) === true, 'blank new row');
check(Budget_logic::is_blank_new(array('id' => '', 'nama' => 'x', 'nilai' => 0)) === false, 'named new row is not blank');
check(Budget_logic::is_blank_new(row(array('nama' => '', 'nilai' => 0))) === false, 'existing row is never blank');

// used, is_active, summary
$cicilan = row(array('id' => 2, 'masa_berlaku' => 3, 'masa_awal' => 1));
check(Budget_logic::used($cicilan, 1) === 2 && Budget_logic::used(array('id' => '', 'masa_awal' => 2), 9) === 2, 'used: awal + approved; new row ignores approved');
check(Budget_logic::is_active($cicilan, 1) === true && Budget_logic::is_active($cicilan, 2) === false, 'inactive once masa is used up');
check(Budget_logic::is_active(row(array('aktif' => 0)), 0) === false, 'inactive when switched off');
$sum = Budget_logic::summary(array(row(), row(array('id' => 3, 'tipe' => 'tahun', 'nilai' => 600000, 'tanggal' => '03/15/2026')), $cicilan, row(array('id' => 4, 'aktif' => 0))), array(2 => 2));
check($sum['n'] === 4 && $sum['act'] === 2 && $sum['bln'] === 1500000.0 && $sum['thn'] === 600000.0, 'summary counts only active items');

// validate: urutan dan teks mengikuti mSave
check(Budget_logic::validate(array(array('id' => '', 'nama' => '', 'nilai' => 0)), array()) === 'Isi minimal satu item budget sebelum mengajukan.', 'validate: nothing filled');
check(Budget_logic::validate(array(), array()) === 'Isi minimal satu item budget sebelum mengajukan.', 'validate: empty draft');
check(Budget_logic::validate(array(row(array('nama' => ' '))), array()) === 'Nama barang / jasa wajib diisi.', 'validate: name required');
check(Budget_logic::validate(array(row(array('nilai' => 0))), array()) === 'Perkiraan biaya "Sewa Kantor" harus lebih dari 0.', 'validate: cost > 0');
check(Budget_logic::validate(array(row(array('masa_berlaku' => 2, 'masa_awal' => 1))), array(1 => 2)) === 'Masa berlaku "Sewa Kantor" tidak boleh kurang dari 3 kali.', 'validate: masa below used');
check(Budget_logic::validate(array(row(array('masa_berlaku' => 3, 'masa_awal' => 1))), array(1 => 2)) === null, 'validate: masa equal to used is allowed');
check(Budget_logic::validate(array(row(array('coa' => ''))), array()) === 'Post pengeluaran "Sewa Kantor" wajib dipilih.', 'validate: coa required');
check(Budget_logic::validate(array(row(array('tanggal' => '32'))), array()) !== null && Budget_logic::validate(array(row(array('tanggal' => '0'))), array()) !== null, 'validate: day of month 1-31');
check(Budget_logic::validate(array(row(array('tipe' => 'tahun', 'tanggal' => '02/30/2026'))), array()) !== null && Budget_logic::validate(array(row(array('tipe' => 'tahun', 'tanggal' => '02/29/2028'))), array()) === null && Budget_logic::validate(array(row(array('tipe' => 'tahun', 'tanggal' => '02/29/2027'))), array()) !== null, 'validate: annual date is a real MM/DD/YYYY date (leap day only in leap years)');
check(Budget_logic::validate(array(row(array('tipe' => 'tahun', 'tanggal' => '03-15'))), array()) !== null && Budget_logic::validate(array(row(array('tipe' => 'tahun', 'tanggal' => '2026-03-15'))), array()) !== null, 'validate: other annual date formats are refused for new/changed schedules');
// data lama yang bentuknya tak lazim (kosong, "30") tidak menghalangi perubahan lain, tapi dicek bila jadwalnya disentuh
$legacy = array(row(array('id' => 7, 'tipe' => 'tahun', 'tanggal' => '30')), row(array('id' => 8, 'tipe' => 'tahun', 'tanggal' => '')));
$edited = array(array_merge($legacy[0], array('nilai' => 5)), $legacy[1]);
check(Budget_logic::validate($edited, array(), $legacy) === null, 'validate: odd legacy schedule is not checked while unchanged');
check(Budget_logic::validate($edited, array()) !== null, 'validate: without the master the schedule is checked (new row semantics)');
$touched = array(array_merge($legacy[0], array('tanggal' => '31')), $legacy[1]);
check(Budget_logic::validate($touched, array(), $legacy) !== null, 'validate: changing the schedule of a legacy row checks the new value');
$fixed = array(array_merge($legacy[0], array('tanggal' => '03/15/2026')), $legacy[1]);
check(Budget_logic::validate($fixed, array(), $legacy) === null, 'validate: legacy row can be corrected to a valid date');
$retype = array(array_merge($legacy[0], array('tipe' => 'bulan')), $legacy[1]);
check(Budget_logic::validate($retype, array(), $legacy) === null, 'validate: switching to monthly with value 30 is a valid day, so it passes');
check(Budget_logic::validate(array(row(), array('id' => '', 'nama' => '', 'nilai' => 0)), array()) === null, 'validate: blank extra row is ignored');
check(Budget_logic::validate(array(row(array('nama' => 'x')), row(array('id' => 2, 'nilai' => 0))), array()) !== null, 'validate: any invalid row fails the whole draft');

// diff
$master = array(row(), row(array('id' => 2, 'nama' => 'Cicilan', 'masa_berlaku' => 3, 'masa_awal' => 1)));
check(Budget_logic::diff($master, $master) === array(), 'diff: unchanged draft yields no lines');
$d = Budget_logic::diff($master, array(row(array('nilai' => '1750000')), $master[1]));
check(count($d) === 1 && $d[0]['kind'] === 'edit' && $d[0]['id'] === 1 && $d[0]['fields'] === array('nilai') && $d[0]['before']['nilai'] === 1500000.0 && $d[0]['after']['nilai'] === 1750000.0, 'diff: cost change is an edit with before/after');
$d = Budget_logic::diff($master, array(row(array('aktif' => 0))));
check($d[0]['kind'] === 'off' && $d[0]['fields'] === array('aktif'), 'diff: only aktif 1->0 is off');
$d = Budget_logic::diff(array(row(array('aktif' => 0))), array(row(array('aktif' => 1))));
check($d[0]['kind'] === 'on', 'diff: only aktif 0->1 is on');
$d = Budget_logic::diff($master, array(row(array('aktif' => 0, 'nilai' => 1))));
check($d[0]['kind'] === 'edit' && $d[0]['fields'] === array('nilai', 'aktif'), 'diff: aktif plus another field is an edit');
$d = Budget_logic::diff($master, array(row(array('id' => '', 'nama' => 'Langganan Canva', 'nilai' => 450000, 'tipe' => 'tahun', 'tanggal' => '11/15/2026', 'masa_berlaku' => 3))));
check(count($d) === 1 && $d[0]['kind'] === 'new' && $d[0]['id'] === null && $d[0]['before'] === null && $d[0]['after']['nama'] === 'Langganan Canva' && $d[0]['after']['id'] === null, 'diff: new item');
check(Budget_logic::diff($master, array(array('id' => '', 'nama' => '', 'nilai' => 0))) === array(), 'diff: blank new row ignored');
check(Budget_logic::diff($master, array(row(array('id' => 999, 'nilai' => 1)))) === array(), 'diff: unknown id ignored');
check(Budget_logic::diff($master, array(row(array('nilai' => '1500000.001')))) === array(), 'diff: sub-cent difference is not a change');
check(Budget_logic::diff($master, array(row(array('nama' => 'Sewa Kantor ')))) === array(), 'diff: trailing space is not a change');
check(Budget_logic::diff($master, array(row(array('keterangan' => 'diubah', 'departement' => 'DEP-9')))) === array(), 'diff: Baseline and department are not budget fields');

// apply_line
$new = Budget_logic::apply_line(array('kind' => 'new', 'id' => null, 'after' => Budget_logic::normalize(row(array('id' => '', 'nama' => 'Baru'))), 'fields' => array()), 'DEP-1');
check($new['op'] === 'insert' && $new['data']['departement'] === 'DEP-1' && $new['data']['nama'] === 'Baru' && !array_key_exists('id', $new['data']), 'apply: new inserts with department and no id');
$edit = Budget_logic::apply_line(array('kind' => 'edit', 'id' => 5, 'after' => Budget_logic::normalize(row(array('nilai' => 9, 'nama' => 'abaikan'))), 'fields' => array('nilai')), 'DEP-1');
check($edit['op'] === 'update' && $edit['id'] === 5 && $edit['data'] === array('nilai' => 9.0), 'apply: edit writes only changed fields');
$off = Budget_logic::apply_line(array('kind' => 'off', 'id' => 5, 'after' => Budget_logic::normalize(row(array('aktif' => 0))), 'fields' => array('aktif')), 'DEP-1');
check($off['data'] === array('aktif' => 0), 'apply: off sets aktif 0 only');
$bad = Budget_logic::apply_line(array('kind' => 'edit', 'id' => 5, 'after' => Budget_logic::normalize(row()), 'fields' => array('departement', 'id', 'nilai')), 'DEP-1');
check(array_keys($bad['data']) === array('nilai'), 'apply: fields outside the budget columns are never written');

// value_text
check(Budget_logic::value_text('nilai', 1500000) === '1.500.000' && Budget_logic::value_text('masa_berlaku', 0) === 'Selalu' && Budget_logic::value_text('masa_berlaku', 12) === '12 kali', 'value_text money/masa');
check(Budget_logic::value_text('tipe', 'tahun') === 'Tahunan' && Budget_logic::value_text('aktif', 0) === 'Nonaktif' && Budget_logic::value_text('nama', '') === '-', 'value_text tipe/aktif/empty');

// end_of
check(Budget_logic::end_of(row(), 0, '2026-11') === null, 'end_of: Selalu has no end');
check(Budget_logic::end_of(row(array('masa_berlaku' => 3)), 0, '2026-11') === 'Jan 2027 (sisa 3 kali)', 'end_of: monthly crosses the year');
check(Budget_logic::end_of(row(array('masa_berlaku' => 3)), 2, '2026-11') === 'Nov 2026 (sisa 1 kali)', 'end_of: one payment left ends this month');
check(Budget_logic::end_of(row(array('masa_berlaku' => 3)), 3, '2026-11') === 'Selesai', 'end_of: finished');
check(Budget_logic::end_of(row(array('tipe' => 'tahun', 'masa_berlaku' => 3)), 1, '2026-11') === 'Nov 2027 (sisa 2 kali)', 'end_of: annual advances by years');

if ($failures) { fwrite(STDERR, "$failures kegagalan\n"); exit(1); }
echo "PASS: Budget_logic (normalize, validate, diff, apply, summary, end_of)\n";
