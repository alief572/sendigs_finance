<?php
/**
 * Reset 8 penerimaan piutang (tanggal bank 30-Sep-2026) di db_sendigs_ss_dev supaya kembali menjadi Draft Penerimaan Piutang.
 * Atas permintaan pengguna, 2 November 2026. Hanya 8 ID eksplisit di bawah yang disentuh.
 *
 *   php reset_penerimaan_20260930.php            -> LATIHAN: semua langkah dijalankan, lalu ROLLBACK.
 *   php reset_penerimaan_20260930.php --apply    -> COMMIT bila semua guard lolos.
 *
 * Wajib: backup baris terkait (mysqldump --where --replace) dan reset_penerimaan_20260930_dryrun.sql lebih dulu.
 * Pulihkan: jalankan berkas backup (REPLACE INTO) lalu DELETE log_alokasi_history WHERE action='RESET_PENERIMAAN'.
 * Rumus saldo invoice = rollback aplikasi: + penerimaan + biaya_admin - (pph23 bila PPh tidak dipotong).
 * Kredensial dibaca dari application/config/development/database.php (tidak disalin ke sini).
 */
define('BASEPATH', __DIR__);
define('ENVIRONMENT', 'development');
require dirname(__DIR__, 3) . '/config/development/database.php';
$cfg = $db['default'];
if ($cfg['database'] !== 'db_sendigs_ss_dev') { fwrite(STDERR, "Database bukan db_sendigs_ss_dev: {$cfg['database']}\n"); exit(2); }

$apply = in_array('--apply', $argv, true);
$targets = array( // id header => [no_surat, id_alokasi (split), invoice id, saldo invoice setelah reset]
    668 => array('00003/RKN-KRG/X/26', 5251, '00295-INV-IX-26', '26160000.00'),
    680 => array('00015/RKN-KRG/X/26', 5185, '00273-INV-VIII-26', '30520000.00'),
    683 => array('00018/RKN-KRG/X/26', 5184, '00272-INV-VIII-26', '2616000.00'),
    684 => array('00019/RKN-KRG/X/26', 5188, '00262-INV-VIII-26', '19718100.00'),
    687 => array('00022/RKN-KRG/X/26', 5187, '00255-INV-VII-26', '41420000.00'),
    688 => array('00023/RKN-KRG/X/26', 5186, '00292-INV-IX-26', '15843150.00'),
    698 => array('00033/RKN-KRG/X/26', 5285, '00329-INV-IX-26', '14500000.00'),
    702 => array('00037/RKN-KRG/X/26', 5284, '00330-INV-IX-26', '14500000.00'),
);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
$m->set_charset('utf8mb4');

function guard($ok, $message) { if (!$ok) throw new RuntimeException('guard gagal: ' . $message); }
function rows($m, $sql) { return $m->query($sql)->fetch_all(MYSQLI_ASSOC); }
function one($m, $sql) { $r = rows($m, $sql); return $r ? array_values($r[0])[0] : null; }
function run($m, $sql) { $m->query($sql); return $m->affected_rows; }

$ids = implode(',', array_keys($targets));
$nos = "'" . implode("','", array_column($targets, 0)) . "'";
$splits = implode(',', array_column($targets, 1));

$m->begin_transaction();
try {
    $before = (int)one($m, 'SELECT COUNT(*) FROM tr_penerimaan_piutang');
    $match = 0;
    foreach (rows($m, "SELECT id, no_surat, id_alokasi FROM tr_penerimaan_piutang WHERE id IN ($ids) FOR UPDATE") as $r) {
        $t = $targets[(int)$r['id']] ?? null;
        guard($t && $t[0] === $r['no_surat'] && (int)$t[1] === (int)$r['id_alokasi'], 'header ' . $r['id'] . ' tidak sesuai');
        $match++;
    }
    guard($match === 8, 'header bukan 8');
    guard((int)one($m, "SELECT COUNT(*) FROM tr_jurnal WHERE sts='1' AND no_transaksi IN ($nos)") === 0, 'ada jurnal yang sudah diposting');
    guard((int)one($m, "SELECT COUNT(*) FROM tr_penerimaan_piutang_detail WHERE id_header IN ($nos)") === 8, 'detail bukan 8');
    guard((int)one($m, "SELECT COUNT(*) FROM tr_penerimaan_pph_23 WHERE id_detail_penerimaan IN (SELECT id FROM tr_penerimaan_piutang_detail WHERE id_header IN ($nos))") === 0, 'ada bukti potong PPh 23');
    guard((int)one($m, "SELECT COUNT(*) FROM tr_customer_deposit WHERE no_referensi IN ($nos)") === 0, 'ada voucher deposit');
    guard((int)one($m, "SELECT COUNT(*) FROM tr_invoicing i JOIN tr_penerimaan_piutang_detail d ON d.id_inv=i.id WHERE d.id_header IN ($nos) AND i.saldo_piutang=0 AND (SELECT COUNT(*) FROM tr_penerimaan_piutang_detail d2 WHERE d2.id_inv=d.id_inv)=1") === 8, 'invoice tidak lunas atau punya penerimaan lain');
    guard((int)one($m, "SELECT COUNT(*) FROM tr_alokasi_split s JOIN tr_alokasi_detail a ON a.id=s.id_alokasi_detail WHERE s.id IN ($splits) AND a.sts='7' AND (SELECT COUNT(*) FROM tr_alokasi_split s2 WHERE s2.id_alokasi_detail=s.id_alokasi_detail)=1") === 8, 'alokasi tidak sesuai');

    // 1) saldo invoice
    guard(run($m, "UPDATE tr_invoicing i JOIN tr_penerimaan_piutang_detail d ON d.id_inv=i.id JOIN tr_penerimaan_piutang p ON p.no_surat=d.id_header
        SET i.saldo_piutang = i.saldo_piutang + d.penerimaan + d.biaya_admin - IF(p.pph23_dipotong='N', d.pph23, 0) WHERE p.id IN ($ids)") === 8, 'update invoice bukan 8');
    foreach ($targets as $t) {
        guard(bccomp(one($m, "SELECT saldo_piutang FROM tr_invoicing WHERE id='{$t[2]}'"), $t[3], 2) === 0, 'saldo invoice ' . $t[2] . ' tidak sesuai tabel');
    }
    // 2) alokasi kembali ke draft (split dipertahankan)
    guard(run($m, "UPDATE tr_alokasi_detail a JOIN tr_alokasi_split s ON s.id_alokasi_detail=a.id SET a.nilai_terpakai=NULL, a.sts='1' WHERE s.id IN ($splits)") === 8, 'update alokasi detail bukan 8');
    guard(run($m, "UPDATE tr_alokasi_split SET nilai_terpakai=NULL WHERE id IN ($splits)") === 8, 'update split bukan 8');
    guard(run($m, "INSERT INTO log_alokasi_history (id_alokasi_detail, action, deskripsi_log, created_by, created_date)
        SELECT s.id_alokasi_detail, 'RESET_PENERIMAAN', CONCAT('Reset penerimaan piutang (', p.no_surat, ') kembali ke Draft atas permintaan pengguna'), 'System', NOW()
        FROM tr_penerimaan_piutang p JOIN tr_alokasi_split s ON s.id=p.id_alokasi WHERE p.id IN ($ids)") === 8, 'log bukan 8');
    // 3) jurnal (belum diposting), detail, header
    guard(run($m, "DELETE FROM tr_jurnal WHERE jenis_transaksi='Penerimaan Piutang' AND sts='0' AND no_transaksi IN ($nos)") === 24, 'jurnal terhapus bukan 24');
    guard(run($m, "DELETE FROM tr_penerimaan_piutang_detail WHERE id_header IN ($nos)") === 8, 'detail terhapus bukan 8');
    guard(run($m, "DELETE FROM tr_penerimaan_piutang WHERE id IN ($ids)") === 8, 'header terhapus bukan 8');
    guard((int)one($m, 'SELECT COUNT(*) FROM tr_penerimaan_piutang') === $before - 8, 'total header tidak turun 8');
    guard((int)one($m, "SELECT COUNT(*) FROM tr_alokasi_split s JOIN tr_alokasi_detail a ON a.id=s.id_alokasi_detail WHERE s.id IN ($splits) AND s.nilai_terpakai IS NULL AND a.nilai_terpakai IS NULL AND a.sts='1' AND NOT EXISTS (SELECT 1 FROM tr_penerimaan_piutang p WHERE p.id_alokasi=s.id)") === 8, 'draft tidak muncul untuk 8 split');

    if ($apply) { $m->commit(); echo "COMMIT. Header $before -> " . ($before - 8) . ", 8 draft muncul, 24 baris jurnal dihapus, 8 saldo invoice dikembalikan.\n"; }
    else { $m->rollback(); echo "LATIHAN OK (semua guard lolos), di-ROLLBACK. Jalankan dengan --apply untuk menyimpan.\n"; }
} catch (Throwable $e) {
    $m->rollback();
    fwrite(STDERR, 'ROLLBACK: ' . $e->getMessage() . "\n");
    exit(1);
}
