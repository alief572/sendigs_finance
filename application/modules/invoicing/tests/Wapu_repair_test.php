<?php
// Jalankan: WAPU_TEST_DB_HOST=mysql WAPU_TEST_DB_USER=root WAPU_TEST_DB_PASS=... php Wapu_repair_test.php
// Hanya memakai tiga schema acak sekali pakai (di-drop di akhir); tidak pernah memuat kredensial aplikasi.
if (PHP_SAPI !== 'cli') exit('CLI only');
foreach (array('WAPU_TEST_DB_HOST', 'WAPU_TEST_DB_USER', 'WAPU_TEST_DB_PASS') as $k) if (getenv($k) === false) { fwrite(STDERR, "Set $k\n"); exit(1); }
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/libraries/Invoice_wapu_repair.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$checks = 0;
function verify($ok, $m) { global $checks; $checks++; if (!$ok) throw new RuntimeException('GAGAL: ' . $m); }
function expectFail($fn, $needle, $m) {
    try { $fn(); } catch (Throwable $e) { verify($needle === '' || strpos($e->getMessage(), $needle) !== false, $m . ' (pesan: ' . $e->getMessage() . ')'); return; }
    verify(false, $m . ' (tidak ditolak)');
}

$host = getenv('WAPU_TEST_DB_HOST'); $user = getenv('WAPU_TEST_DB_USER'); $pass = getenv('WAPU_TEST_DB_PASS');
$suffix = bin2hex(random_bytes(4)); $F = "wapu_t_{$suffix}_fin"; $G = "wapu_t_{$suffix}_gl"; $C = "wapu_t_{$suffix}_cnl";
$admin = new mysqli($host, $user, $pass); $admin->set_charset('utf8');
foreach (array($F, $G, $C) as $d) $admin->query("CREATE DATABASE `$d` CHARACTER SET utf8");
$fin = new mysqli($host, $user, $pass, $F); $gl = new mysqli($host, $user, $pass, $F);
$q = function ($sql) use ($admin) { $admin->query($sql); };

$q("CREATE TABLE `$F`.tr_invoicing(id VARCHAR(100) PRIMARY KEY,id_actual_plan_tagih VARCHAR(100),id_detail_plan_tagih INT,id_spk_penawaran VARCHAR(100),nm_customer VARCHAR(255),tanggal_invoice DATE,no_invoice VARCHAR(100),
 total_nominal DECIMAL(20,2),dpp_nilai_lain DECIMAL(20,2),pajak DECIMAL(20,2),total_akhir DECIMAL(20,2),total_nominal_jurnal DECIMAL(20,2),dpp_lain_lain_jurnal DECIMAL(20,2),ppn_jurnal DECIMAL(20,2),tagihan_ppn_jurnal DECIMAL(20,2),
 pph_jurnal DECIMAL(20,2),total_akhir_jurnal DECIMAL(20,2),saldo_piutang DECIMAL(20,2),saldo_piutang_tanpa_ppn DECIMAL(15,2),saldo_piutang_tanpa_pph DECIMAL(20,2),tipe_invoice ENUM('0','1') DEFAULT '0',non_kons ENUM('0','1') DEFAULT '0',
 ppn_persen DECIMAL(10,2) DEFAULT 0,consulting_version TINYINT NULL,tax_treatment VARCHAR(8) NULL,consulting_company_id VARCHAR(20) NULL,consulting_snapshot LONGTEXT NULL,consulting_top_key VARCHAR(50) NULL,UNIQUE KEY(consulting_top_key)) ENGINE=InnoDB");
$q("CREATE TABLE `$F`.tr_jurnal(id INT AUTO_INCREMENT PRIMARY KEY,no_jurnal VARCHAR(255),tgl_jurnal DATE,coa VARCHAR(255),id_company VARCHAR(255),nm_company VARCHAR(255),nm_coa VARCHAR(255),debit DECIMAL(20,2),kredit DECIMAL(20,2),keterangan TEXT,
 sts ENUM('0','1'),tgl_posting DATETIME NULL,posted_by VARCHAR(255) NULL,no_transaksi VARCHAR(255),jenis_transaksi VARCHAR(255),id_divisi VARCHAR(255) NULL,nm_divisi TEXT NULL,created_by VARCHAR(255) NULL,created_date DATETIME NULL) ENGINE=InnoDB");
$q("CREATE TABLE `$F`.tr_kartu_piutang(id BIGINT AUTO_INCREMENT PRIMARY KEY,tipe CHAR(3),nomor VARCHAR(50),tanggal DATE,no_perkiraan VARCHAR(10),keterangan VARCHAR(150),jenis_trans VARCHAR(20) NULL,no_reff VARCHAR(25),debet DOUBLE,kredit DOUBLE,
 nocust VARCHAR(15) NULL,valid CHAR(1) NULL,waktu_valid DATETIME NULL,stspos CHAR(1) NULL,jenis_jurnal VARCHAR(255) NULL,id_supplier VARCHAR(255),nama_supplier VARCHAR(255),debet_usd DOUBLE NULL,kredit_usd DOUBLE NULL,id_company INT,nm_company TEXT) ENGINE=MyISAM");
$q("CREATE TABLE `$F`.tr_penerimaan_piutang_detail(id INT AUTO_INCREMENT PRIMARY KEY,id_inv VARCHAR(100)) ENGINE=InnoDB");
$q("CREATE TABLE `$F`.tr_penerimaan_pph_23(id INT AUTO_INCREMENT PRIMARY KEY,id_inv VARCHAR(100)) ENGINE=InnoDB");
$q("CREATE TABLE `$F`.ms_invoice_coa_mapping(source_company_id VARCHAR(20) PRIMARY KEY,owner_company_id VARCHAR(20),accounts TEXT) ENGINE=InnoDB");
$q("CREATE TABLE `$F`.kons_tr_actual_plan_tagih(id VARCHAR(100) PRIMARY KEY,term_payment VARCHAR(100),persen_payment VARCHAR(20),desc_payment TEXT) ENGINE=InnoDB");
$q("CREATE TABLE `$C`.kons_tr_company(id VARCHAR(20) PRIMARY KEY,nm_company VARCHAR(100)) ENGINE=InnoDB");
$q("CREATE TABLE `$G`.javh(nomor VARCHAR(50),tgl DATE,jml DOUBLE,koreksi_no VARCHAR(50),kdcab VARCHAR(5),jenis VARCHAR(5),keterangan VARCHAR(255),bulan VARCHAR(2),tahun VARCHAR(4),user_id VARCHAR(20),memo VARCHAR(100),tgl_jvkoreksi DATE,ho_valid VARCHAR(5)) ENGINE=MyISAM");
$q("CREATE TABLE `$G`.jurnal(id BIGINT AUTO_INCREMENT PRIMARY KEY,tipe CHAR(3),nomor VARCHAR(50),tanggal DATE,no_perkiraan VARCHAR(10),keterangan VARCHAR(150),no_reff VARCHAR(25),debet BIGINT,kredit BIGINT,stspos CHAR(1)) ENGINE=MyISAM");
$q("CREATE TABLE `$G`.pastibisa_tb_cabang(nocab VARCHAR(5),subcab VARCHAR(5),nomorJC INT) ENGINE=MyISAM");
$q("CREATE TABLE `$G`.coa_master(no_perkiraan VARCHAR(20),nama VARCHAR(100)) ENGINE=MyISAM");
$q("INSERT INTO `$C`.kons_tr_company VALUES('7','STM')");

$accounts = array('receivable' => '1102-01-01', 'vat_domestic' => '2104-01-07', 'vat_wapu_prepaid' => '1106-01-08', 'vat_wapu_payable' => '2104-01-09', 'pph' => '1106-01-02',
    'sales_consulting' => '4101-01-01', 'sales_inhouse' => '4101-01-02', 'sales_public' => '4101-01-03', 'sales_hse' => '4101-01-05');
$names = array('1102-01-01' => 'Piutang Dagang Usaha', '2104-01-07' => 'Hutang PPN DN', '1106-01-08' => 'Uang Muka PPN WAPU', '2104-01-09' => 'Hutang PPN WAPU', '1106-01-02' => 'PPh 23 Disetor',
    '4101-01-01' => 'Penjualan Konsultasi', '4101-01-02' => 'Penjualan In House', '4101-01-03' => 'Penjualan Publik', '4101-01-05' => 'Penjualan HSE');
// Nilai nyata dari data live (DB live hanya dibaca untuk menyusun fixture ini).
$cases = array(
    array('id' => '00215-INV-VII-26', 'no' => '146/STM/2026', 'date' => '2026-07-08', 'S' => 26600000, 'P' => 2926000, 'H' => 532000, 'src' => '1', 'nmc' => 'STM-Vuca', 'jv' => '101-AJV2603630', 'plan' => 1201, 'act' => '57153/ACT-TGH/VII/26', 'stspos' => '1'),
    array('id' => '00220-INV-VII-26', 'no' => '151/STM/2026', 'date' => '2026-07-16', 'S' => 339500000, 'P' => 37345000, 'H' => 6790000, 'src' => '6', 'nmc' => 'STM-Sustain', 'jv' => '101-AJV2603677', 'plan' => 1194, 'act' => '57157/ACT-TGH/VII/26', 'stspos' => '0'),
    array('id' => '00287-INV-VIII-26', 'no' => '197/STM/2026', 'date' => '2026-09-01', 'S' => 27500000, 'P' => 3025000, 'H' => 550000, 'src' => '6', 'nmc' => 'STM-Sustain', 'jv' => '101-AJV2603700', 'plan' => 1240, 'act' => '57229/ACT-TGH/VIII/26', 'stspos' => '0'),
);

$reset = function () use ($admin, $F, $G, $cases, $accounts, $names) {
    foreach (array("$F.tr_invoicing", "$F.tr_jurnal", "$F.tr_kartu_piutang", "$F.tr_penerimaan_piutang_detail", "$F.tr_penerimaan_pph_23", "$F.ms_invoice_coa_mapping", "$F.kons_tr_actual_plan_tagih", "$G.javh", "$G.jurnal", "$G.pastibisa_tb_cabang", "$G.coa_master") as $t) $admin->query("DELETE FROM `" . str_replace('.', '`.`', $t) . "`");
    $admin->query("DROP TRIGGER IF EXISTS `$F`.fail_jurnal");
    $admin->query("INSERT INTO `$G`.pastibisa_tb_cabang VALUES('101','A',3784)");
    foreach ($names as $code => $name) $admin->query("INSERT INTO `$G`.coa_master VALUES('$code','$name')");
    foreach (array('1', '6') as $src) $admin->query("INSERT INTO `$F`.ms_invoice_coa_mapping VALUES('$src','7','" . $admin->real_escape_string(json_encode($accounts)) . "')");
    foreach ($cases as $c) {
        $S = $c['S']; $P = $c['P']; $H = $c['H']; $net = $S + $P - $H;
        $admin->query("INSERT INTO `$F`.tr_invoicing(id,id_actual_plan_tagih,id_detail_plan_tagih,id_spk_penawaran,nm_customer,tanggal_invoice,no_invoice,total_nominal,dpp_nilai_lain,pajak,total_akhir,total_nominal_jurnal,dpp_lain_lain_jurnal,ppn_jurnal,tagihan_ppn_jurnal,pph_jurnal,total_akhir_jurnal,saldo_piutang,saldo_piutang_tanpa_ppn,saldo_piutang_tanpa_pph,ppn_persen)
            VALUES('{$c['id']}','{$c['act']}',{$c['plan']},'SPK','Cust {$c['no']}','{$c['date']}','{$c['no']}',$S,$S,$P,$S+$P,$S,$S,$P,$S+$P,$H,$net,$net,$S-$H,$S+$P,12)");
        $admin->query("INSERT INTO `$F`.kons_tr_actual_plan_tagih VALUES('{$c['act']}','Penagihan 1','100.00','Tahap pertama')");
        foreach (array(array('1102-01-01', 'Piutang Dagang Usaha', $net, 0), array('1106-01-02', 'PPh 23 Disetor', $H, 0), array('2104-01-07', 'Hutang PPN DN', 0, $P), array('4101-01-01', 'Penjualan Konsultasi', 0, $S)) as $i => $r) {
            $admin->query("INSERT INTO `$F`.tr_jurnal(no_jurnal,tgl_jurnal,coa,id_company,nm_company,nm_coa,debit,kredit,keterangan,sts,tgl_posting,posted_by,no_transaksi,jenis_transaksi,created_by,created_date)
                VALUES('0000" . ($i + 1) . "-AJV-X-26','{$c['date']}','{$r[0]}','{$c['src']}','{$c['nmc']}','{$r[1]}',{$r[2]},{$r[3]},'{$r[1]} - {$c['id']}','1','{$c['date']} 00:00:00','203','{$c['id']}','Invoicing','203','{$c['date']} 10:00:00')");
            $admin->query("INSERT INTO `$G`.jurnal(tipe,nomor,tanggal,no_perkiraan,keterangan,no_reff,debet,kredit,stspos) VALUES('JV','{$c['jv']}','{$c['date']}','{$r[0]}','{$r[1]} - {$c['no']}','{$c['no']}',{$r[2]},{$r[3]},'{$c['stspos']}')");
        }
        $admin->query("INSERT INTO `$G`.javh VALUES('{$c['jv']}','{$c['date']}',$net,'-','101','JV','{$c['no']} - Cust','" . substr($c['date'], 5, 2) . "','2026','203','','{$c['date']}','')");
        $admin->query("INSERT INTO `$F`.tr_kartu_piutang(tipe,nomor,tanggal,no_perkiraan,keterangan,no_reff,debet,kredit,stspos,id_supplier,nama_supplier,id_company,nm_company)
            VALUES('JV','{$c['jv']}','{$c['date']}','1102-01-01','{$c['no']} - Cust','{$c['id']}',$net,0,'0','CUST1','Cust {$c['no']}',{$c['src']},'{$c['nmc']}')");
    }
};
$repairFor = function () use ($fin, $gl, $F, $G, $C) { return new Invoice_wapu_repair($fin, $gl, $F, $G, $C); };
$one = function ($sql) use ($admin) { return $admin->query($sql)->fetch_assoc(); };
$review = function ($plan) { return array('approved_by' => 'Finance', 'approved_at' => '2026-11-09', 'operator_id' => '203', 'period_closed_ack' => true, 'pph_reversed_ack' => true, 'invoices' => array($plan['id'] => $plan['hash'])); };
$tmp = sys_get_temp_dir() . '/wapu_' . $suffix; @mkdir($tmp);

try {
    // 1. Plan: angka jurnal koreksi untuk tiga invoice nyata, seimbang, tanpa menulis.
    $reset();
    $before = $one("SELECT (SELECT COUNT(*) FROM `$F`.tr_jurnal) j,(SELECT COUNT(*) FROM `$G`.jurnal) g,(SELECT nomorJC FROM `$G`.pastibisa_tb_cabang) c");
    $expectedPiutang = array('00215-INV-VII-26' => 2394000, '00220-INV-VII-26' => 30555000, '00287-INV-VIII-26' => 2475000);
    foreach ($cases as $c) {
        $plan = $repairFor()->plan($c['id']);
        $by = array(); foreach ($plan['journal'] as $l) $by[$l['role']] = $l;
        verify($by['vat_domestic']['debit'] === $c['P'] * 100 && $by['vat_wapu_prepaid']['debit'] === $c['P'] * 100 && $by['vat_wapu_payable']['credit'] === $c['P'] * 100, 'PPN pindah ' . $c['no']);
        verify($by['receivable']['credit'] === $expectedPiutang[$c['id']] * 100 && $by['pph']['credit'] === $c['H'] * 100, 'Piutang dan PPh ' . $c['no']);
        verify(array_sum(array_column($plan['journal'], 'debit')) === array_sum(array_column($plan['journal'], 'credit')), 'Seimbang ' . $c['no']);
        verify($plan['next_number'] === '101-AJV26' . '03785' && $plan['date'] === $c['date'], 'Nomor dan tanggal ' . $c['no']);
        verify($plan['fields']['saldo_piutang'] === Invoice_calculation::decimal($c['S'] * 100) && $plan['fields']['tax_treatment'] === 'WAPU', 'Field baru ' . $c['no']);
    }
    $after = $one("SELECT (SELECT COUNT(*) FROM `$F`.tr_jurnal) j,(SELECT COUNT(*) FROM `$G`.jurnal) g,(SELECT nomorJC FROM `$G`.pastibisa_tb_cabang) c");
    verify($before == $after, 'Plan tidak menulis');

    // 2. Apply invoice 146 end-to-end.
    $c = $cases[0]; $plan = $repairFor()->plan($c['id']); $backup = "$tmp/146.json";
    $result = $repairFor()->apply($c['id'], $review($plan), $backup);
    verify($result['gl_number'] === '101-AJV2603785' && is_file($backup) && json_decode(file_get_contents($backup), true)['plan']['hash'] === $plan['hash'], 'Backup dan nomor JV');
    $inv = $one("SELECT * FROM `$F`.tr_invoicing WHERE id='{$c['id']}'");
    verify($inv['tax_treatment'] === 'WAPU' && (int)$inv['consulting_version'] === 3 && $inv['consulting_company_id'] === '7' && $inv['consulting_top_key'] === '1201', 'Header invoice');
    verify((float)$inv['saldo_piutang'] === (float)$c['S'] && (float)$inv['pph_jurnal'] === 0.0 && (float)$inv['total_akhir_jurnal'] === (float)$c['S'] && (float)$inv['ppn_jurnal'] === (float)$c['P'], 'Saldo WAPU');
    $net = function ($coa) use ($one, $F, $c) { return (float)$one("SELECT COALESCE(SUM(debit-kredit),0) n FROM `$F`.tr_jurnal WHERE no_transaksi='{$c['id']}' AND coa='$coa'")['n']; };
    verify($net('1102-01-01') == $c['S'] && $net('1106-01-02') == 0 && $net('2104-01-07') == 0 && $net('1106-01-08') == $c['P'] && $net('2104-01-09') == -$c['P'] && $net('4101-01-01') == -$c['S'], 'Saldo akun operasional');
    $glnet = function ($coa) use ($one, $G, $c) { return (float)$one("SELECT COALESCE(SUM(debet-kredit),0) n FROM `$G`.jurnal WHERE no_reff='{$c['no']}' AND no_perkiraan='$coa'")['n']; };
    verify($glnet('1102-01-01') == $c['S'] && $glnet('1106-01-02') == 0 && $glnet('2104-01-07') == 0 && $glnet('1106-01-08') == $c['P'] && $glnet('2104-01-09') == -$c['P'], 'Saldo akun GL');
    $javh = $one("SELECT * FROM `$G`.javh WHERE nomor='101-AJV2603785'");
    verify($javh['koreksi_no'] === $c['jv'] && (float)$javh['jml'] == 2 * $c['P'] && $javh['tgl'] === $c['date'] && $one("SELECT nomorJC n FROM `$G`.pastibisa_tb_cabang")['n'] == 3785, 'Header GL dan counter');
    verify($one("SELECT COUNT(*) n FROM `$G`.jurnal WHERE nomor='{$c['jv']}'")['n'] == 4, 'Jurnal asli utuh');
    $kartu = $one("SELECT COALESCE(SUM(debet-kredit),0) n FROM `$F`.tr_kartu_piutang WHERE no_reff='{$c['id']}'")['n'];
    verify((float)$kartu == (float)$inv['saldo_piutang'], 'Kartu piutang = saldo');
    verify(Invoice_consulting::is_new($inv) && Invoice_consulting::snapshot($inv)['owner']['id'] === '7', 'Dikenali sebagai invoice versi baru');
    // Idempotensi dan penolakan.
    expectFail(function () use ($repairFor, $c) { $repairFor()->plan($c['id']); }, 'bukan invoice lama', 'Koreksi ulang ditolak');

    // 3. Review salah / tidak lengkap menolak tanpa menulis.
    $reset(); $c = $cases[1]; $plan = $repairFor()->plan($c['id']); $bad = $review($plan); $bad['invoices'][$c['id']] = str_repeat('0', 64);
    expectFail(function () use ($repairFor, $c, $bad, $tmp) { $repairFor()->apply($c['id'], $bad, "$tmp/bad.json"); }, 'Berkas review', 'Hash salah ditolak');
    $noack = $review($plan); $noack['period_closed_ack'] = false;
    expectFail(function () use ($repairFor, $c, $noack, $tmp) { $repairFor()->apply($c['id'], $noack, "$tmp/noack.json"); }, 'Berkas review', 'Tanpa persetujuan periode ditolak');
    verify(!is_file("$tmp/bad.json") && !is_file("$tmp/noack.json") && $one("SELECT COUNT(*) n FROM `$G`.javh")['n'] == 3, 'Tidak ada tulisan pada penolakan review');

    // 4. Preflight: penerimaan, GL beda, akun hilang, rekonsiliasi.
    $reset(); $admin->query("INSERT INTO `$F`.tr_penerimaan_piutang_detail(id_inv) VALUES('{$cases[1]['id']}')");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[1]['id']); }, 'penerimaan', 'Penerimaan ditolak');
    $reset(); $admin->query("INSERT INTO `$F`.tr_penerimaan_pph_23(id_inv) VALUES('{$cases[1]['id']}')");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[1]['id']); }, 'penerimaan', 'PPh 23 diterima ditolak');
    $reset(); $admin->query("UPDATE `$G`.jurnal SET debet=debet+1 WHERE nomor='{$cases[2]['jv']}' AND no_perkiraan='1102-01-01'");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[2]['id']); }, 'GL asli', 'GL berbeda ditolak');
    $reset(); $admin->query("DELETE FROM `$G`.coa_master WHERE no_perkiraan='1106-01-08'");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[0]['id']); }, 'master COA STM', 'Akun WAPU hilang ditolak');
    $reset(); $admin->query("UPDATE `$F`.tr_invoicing SET saldo_piutang=saldo_piutang-1 WHERE id='{$cases[0]['id']}'");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[0]['id']); }, 'saldo penuh', 'Saldo sudah berubah ditolak');
    $reset(); $admin->query("UPDATE `$F`.tr_jurnal SET sts='0' WHERE no_transaksi='{$cases[0]['id']}' AND coa='4101-01-01'");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[0]['id']); }, 'berbeda', 'Jurnal belum terposting ditolak');
    $reset(); $admin->query("UPDATE `$F`.ms_invoice_coa_mapping SET owner_company_id='6' WHERE source_company_id='1'");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[0]['id']); }, 'pemilik STM', 'Owner bukan STM ditolak');
    $reset(); $admin->query("UPDATE `$F`.tr_invoicing SET consulting_top_key='1201' WHERE id='{$cases[1]['id']}'");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->plan($cases[0]['id']); }, 'consulting_top_key', 'Top key terpakai ditolak');

    // 5. Kegagalan di tengah: GL dikompensasi, counter kembali, invoice tidak berubah.
    foreach (array('after_gl', 'before_commit', 'trigger') as $mode) {
        $reset(); $c = $cases[2]; $r = $repairFor(); $plan = $r->plan($c['id']);
        if ($mode === 'trigger') $admin->query("CREATE TRIGGER `$F`.fail_jurnal BEFORE INSERT ON `$F`.tr_jurnal FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='uji gagal'");
        else $r->failAt = $mode;
        expectFail(function () use ($r, $c, $review, $plan, $tmp, $mode) { $r->apply($c['id'], $review($plan), "$tmp/fail_$mode.json"); }, '', "Kegagalan $mode");
        $state = $one("SELECT (SELECT COUNT(*) FROM `$G`.javh) h,(SELECT COUNT(*) FROM `$G`.jurnal) g,(SELECT nomorJC FROM `$G`.pastibisa_tb_cabang) c,(SELECT COUNT(*) FROM `$F`.tr_kartu_piutang) k,(SELECT COUNT(*) FROM `$F`.tr_jurnal) j,(SELECT COUNT(*) FROM `$F`.tr_invoicing WHERE tax_treatment IS NOT NULL) t");
        verify($state['h'] == 3 && $state['g'] == 12 && $state['c'] == 3784 && $state['k'] == 3 && $state['j'] == 12 && $state['t'] == 0, "Kompensasi lengkap pada $mode");
        $admin->query("DROP TRIGGER IF EXISTS `$F`.fail_jurnal");
        $result = $repairFor()->apply($c['id'], $review($repairFor()->plan($c['id'])), "$tmp/retry_$mode.json");
        verify($result['gl_number'] === '101-AJV2603785', "Ulang setelah $mode memakai nomor yang sama");
    }

    // 6. Tiga invoice berurutan: nomor JV naik, jurnal asli tetap, tiap saldo = kartu piutang.
    $reset();
    foreach ($cases as $i => $c) {
        $r = $repairFor()->apply($c['id'], $review($repairFor()->plan($c['id'])), "$tmp/all_$i.json");
        verify($r['gl_number'] === '101-AJV26' . sprintf('%05d', 3785 + $i), 'Nomor berurutan ' . $c['no']);
        $inv = $one("SELECT saldo_piutang s FROM `$F`.tr_invoicing WHERE id='{$c['id']}'");
        $k = $one("SELECT SUM(debet-kredit) n FROM `$F`.tr_kartu_piutang WHERE no_reff='{$c['id']}'");
        verify((float)$inv['s'] == (float)$c['S'] && (float)$k['n'] == (float)$c['S'], 'Saldo = kartu ' . $c['no']);
    }
    $tot = $one("SELECT SUM(CASE WHEN no_perkiraan='2104-01-07' THEN debet-kredit ELSE 0 END) ppn,SUM(CASE WHEN no_perkiraan='1106-01-02' THEN debet-kredit ELSE 0 END) pph,SUM(CASE WHEN no_perkiraan='1106-01-08' THEN debet-kredit ELSE 0 END) um FROM `$G`.jurnal");
    verify((float)$tot['ppn'] == 0.0 && (float)$tot['pph'] == 0.0 && (float)$tot['um'] == 43296000.0, 'Total GL: PPN DN dan PPh nol, Uang Muka WAPU 43.296.000');
    // 7. Normalisasi tr_jurnal: 9 baris -> 4 baris WAPU standar; saldo bersih identik; GL, kartu, dan header invoice tidak berubah.
    $glSnapshot =function () use ($one, $G, $F) { return $one("SELECT (SELECT COUNT(*) FROM `$G`.jurnal) g,(SELECT SUM(debet-kredit) FROM `$G`.jurnal) gs,(SELECT COUNT(*) FROM `$G`.javh) h,(SELECT SUM(debet-kredit) FROM `$F`.tr_kartu_piutang) k"); };
    $reset();
    expectFail(function () use ($repairFor, $cases) { $repairFor()->normalizePlan($cases[0]['id']); }, 'belum dikoreksi', 'Normalisasi invoice yang belum dikoreksi ditolak');
    foreach ($cases as $c) $repairFor()->apply($c['id'], $review($repairFor()->plan($c['id'])), "$tmp/n_apply_{$c['id']}.json");
    $netOf = function ($id) use ($admin, $F) { $r = $admin->query("SELECT coa,SUM(debit-kredit) n FROM `$F`.tr_jurnal WHERE no_transaksi='$id' AND jenis_transaksi='Invoicing' GROUP BY coa HAVING n<>0 ORDER BY coa")->fetch_all(MYSQLI_ASSOC); $o = array(); foreach ($r as $x) $o[$x['coa']] = (float)$x['n']; return $o; };
    $glBefore = $glSnapshot();
    foreach ($cases as $i => $c) {
        $np = $repairFor()->normalizePlan($c['id']);
        verify(count($np['before']) === 9 && count($np['target']) === 4, 'Plan normalisasi 9 -> 4 ' . $c['no']);
        $netBefore = $netOf($c['id']);
        expectFail(function () use ($repairFor, $c, $tmp) { $repairFor()->normalize($c['id'], str_repeat('0', 64), "$tmp/nbad.json"); }, 'Hash tidak cocok', 'Hash salah ditolak ' . $c['no']);
        verify(!is_file("$tmp/nbad.json") && $one("SELECT COUNT(*) n FROM `$F`.tr_jurnal WHERE no_transaksi='{$c['id']}'")['n'] == 9, 'Penolakan hash tidak menulis ' . $c['no']);
        $r = $repairFor()->normalize($c['id'], $np['hash'], "$tmp/norm_$i.json");
        $rows = $admin->query("SELECT * FROM `$F`.tr_jurnal WHERE no_transaksi='{$c['id']}' AND jenis_transaksi='Invoicing' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        verify(count($rows) === 4 && array_column($rows, 'coa') === array('1106-01-08', '1102-01-01', '2104-01-09', '4101-01-01'), 'Empat baris WAPU standar ' . $c['no']);
        verify(array_sum(array_column($rows, 'debit')) == array_sum(array_column($rows, 'kredit')) && array_unique(array_column($rows, 'sts')) === array('1'), 'Seimbang dan terposting ' . $c['no']);
        verify(strpos(implode('|', array_column($rows, 'keterangan')), 'KOREKSI') === false && strpos($rows[0]['keterangan'], $c['no']) !== false, 'Deskripsi bersih ' . $c['no']);
        verify($netOf($c['id']) == $netBefore, 'Saldo bersih per akun identik ' . $c['no']);
        verify(count(json_decode(file_get_contents("$tmp/norm_$i.json"), true)['tr_jurnal_before']) === 9, 'Backup berisi 9 baris lama ' . $c['no']);
        expectFail(function () use ($repairFor, $c) { $repairFor()->normalizePlan($c['id']); }, 'sembilan baris', 'Normalisasi ulang ditolak ' . $c['no']);
    }
    verify($glSnapshot() == $glBefore, 'GL, kartu piutang tidak berubah oleh normalisasi');
    // Penerimaan setelah konversi menolak normalisasi.
    $reset(); foreach ($cases as $c) $repairFor()->apply($c['id'], $review($repairFor()->plan($c['id'])), "$tmp/n2_apply_{$c['id']}.json");
    $admin->query("INSERT INTO `$F`.tr_penerimaan_piutang_detail(id_inv) VALUES('{$cases[0]['id']}')");
    expectFail(function () use ($repairFor, $cases) { $repairFor()->normalizePlan($cases[0]['id']); }, 'penerimaan', 'Normalisasi dengan penerimaan ditolak');
    // Kegagalan DB di tengah: transaksi di-rollback, tetap 9 baris, lalu bisa diulang.
    $admin->query("DELETE FROM `$F`.tr_penerimaan_piutang_detail");
    $np = $repairFor()->normalizePlan($cases[1]['id']);
    $admin->query("CREATE TRIGGER `$F`.fail_jurnal BEFORE INSERT ON `$F`.tr_jurnal FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='uji gagal'");
    expectFail(function () use ($repairFor, $cases, $np, $tmp) { $repairFor()->normalize($cases[1]['id'], $np['hash'], "$tmp/nfail.json"); }, '', 'Kegagalan insert saat normalisasi');
    verify($one("SELECT COUNT(*) n FROM `$F`.tr_jurnal WHERE no_transaksi='{$cases[1]['id']}'")['n'] == 9, 'Rollback: tetap 9 baris');
    $admin->query("DROP TRIGGER IF EXISTS `$F`.fail_jurnal");
    $np = $repairFor()->normalizePlan($cases[1]['id']);
    $repairFor()->normalize($cases[1]['id'], $np['hash'], "$tmp/nretry.json");
    verify($one("SELECT COUNT(*) n FROM `$F`.tr_jurnal WHERE no_transaksi='{$cases[1]['id']}'")['n'] == 4, 'Ulang setelah gagal berhasil');
    // 8. Normalisasi GL: JV asli ditulis ulang jadi 4 baris WAPU, JV koreksi dan kredit kartu dihapus; kegagalan dipulihkan persis.
    $reset();
    foreach ($cases as $c) {
        $repairFor()->apply($c['id'], $review($repairFor()->plan($c['id'])), "$tmp/g_apply_{$c['id']}.json");
        $repairFor()->normalize($c['id'], $repairFor()->normalizePlan($c['id'])['hash'], "$tmp/g_norm_{$c['id']}.json");
    }
    $glState = function () use ($admin, $F, $G) {
        return md5(json_encode(array($admin->query("SELECT * FROM `$G`.jurnal ORDER BY id")->fetch_all(MYSQLI_ASSOC), $admin->query("SELECT * FROM `$G`.javh ORDER BY nomor")->fetch_all(MYSQLI_ASSOC),
            $admin->query("SELECT * FROM `$F`.tr_kartu_piutang ORDER BY id")->fetch_all(MYSQLI_ASSOC))));
    };
    $c0 = $cases[0]; $stateBefore = $glState();
    expectFail(function () use ($repairFor, $c0, $tmp) { $repairFor()->normalizeGl($c0['id'], str_repeat('0', 64), "$tmp/gbad.json"); }, 'Hash tidak cocok', 'Hash GL salah ditolak');
    verify($glState() === $stateBefore && !is_file("$tmp/gbad.json"), 'Penolakan hash GL tidak menulis');
    $r = $repairFor(); $r->failAt = 'gl_after_writes'; $gp = $r->normalizeGlPlan($c0['id']);
    expectFail(function () use ($r, $c0, $gp, $tmp) { $r->normalizeGl($c0['id'], $gp['hash'], "$tmp/gfail.json"); }, 'Simulasi', 'Kegagalan setelah tulis GL');
    verify($glState() === $stateBefore, 'GL dan kartu dipulihkan persis setelah gagal');
    foreach ($cases as $i => $c) {
        $gp = $repairFor()->normalizeGlPlan($c['id']);
        verify(count($gp['gl']) === 9 && count($gp['target']) === 4 && $gp['original'] === $c['jv'], 'Plan GL 9 -> 4 ' . $c['no']);
        $result = $repairFor()->normalizeGl($c['id'], $gp['hash'], "$tmp/gl_$i.json");
        $rows = $admin->query("SELECT * FROM `$G`.jurnal WHERE nomor='{$c['jv']}' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        verify(array_column($rows, 'no_perkiraan') === array('1106-01-08', '1102-01-01', '2104-01-09', '4101-01-01'), 'GL empat baris WAPU ' . $c['no']);
        verify(array_sum(array_column($rows, 'debet')) == array_sum(array_column($rows, 'kredit')) && (int)$rows[0]['debet'] === $c['P'] && (int)$rows[1]['debet'] === $c['S'] && (int)$rows[3]['kredit'] === $c['S'], 'GL nominal dan seimbang ' . $c['no']);
        verify(count(array_unique(array_column($rows, 'stspos'))) === 1 && $rows[0]['stspos'] === $c['stspos'], 'stspos tetap ' . $c['no']);
        $javhRows = $admin->query("SELECT nomor,jml,koreksi_no FROM `$G`.javh WHERE keterangan LIKE '%{$c['no']}%'")->fetch_all(MYSQLI_ASSOC);
        verify(count($javhRows) === 1 && $javhRows[0]['nomor'] === $c['jv'] && (float)$javhRows[0]['jml'] == $c['S'] + $c['P'] && $javhRows[0]['koreksi_no'] === '-', 'Header GL satu dan jml benar ' . $c['no']);
        verify($one("SELECT COUNT(*) n FROM `$G`.jurnal WHERE nomor='{$result['removed']}'")['n'] == 0, 'JV koreksi GL terhapus ' . $c['no']);
        $card = $admin->query("SELECT * FROM `$F`.tr_kartu_piutang WHERE no_reff='{$c['id']}'")->fetch_all(MYSQLI_ASSOC);
        $saldo = $one("SELECT saldo_piutang s FROM `$F`.tr_invoicing WHERE id='{$c['id']}'")['s'];
        verify(count($card) === 1 && (float)$card[0]['debet'] == $c['S'] && (float)$card[0]['kredit'] == 0 && (float)$saldo == $c['S'], 'Kartu piutang satu baris = saldo ' . $c['no']);
        verify(count(json_decode(file_get_contents("$tmp/gl_$i.json"), true)['snapshot']['jurnal']) === 9, 'Backup GL berisi 9 baris ' . $c['no']);
        expectFail(function () use ($repairFor, $c) { $repairFor()->normalizeGlPlan($c['id']); }, 'tepat satu', 'Normalisasi GL ulang ditolak ' . $c['no']);
    }
    $gt = $one("SELECT SUM(CASE WHEN no_perkiraan='2104-01-07' THEN debet-kredit ELSE 0 END) ppn,SUM(CASE WHEN no_perkiraan='1106-01-02' THEN debet-kredit ELSE 0 END) pph,SUM(CASE WHEN no_perkiraan='1106-01-08' THEN debet-kredit ELSE 0 END) um,COUNT(*) n FROM `$G`.jurnal");
    verify((float)$gt['ppn'] == 0.0 && (float)$gt['pph'] == 0.0 && (float)$gt['um'] == 43296000.0 && $gt['n'] == 12, 'Total GL akhir: 12 baris, PPN DN/PPh nol, Uang Muka WAPU 43.296.000');
    verify($one("SELECT nomorJC n FROM `$G`.pastibisa_tb_cabang")['n'] == 3787, 'Counter tidak diubah normalisasi GL');
    echo "OK: $checks pemeriksaan lulus\n";
} finally {
    foreach (array($F, $G, $C) as $d) $admin->query("DROP DATABASE IF EXISTS `$d`");
    foreach (glob("$tmp/*") ?: array() as $file) @unlink($file);
    @rmdir($tmp);
}
