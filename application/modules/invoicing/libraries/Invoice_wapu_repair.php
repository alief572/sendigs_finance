<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/Invoice_consulting.php';

/**
 * Koreksi satu invoice lama yang tercatat PKP menjadi WAPU versi baru (consulting_version 3).
 * Jurnal asli tidak disentuh; ditambah satu JV koreksi netto bertanggal sama dengan invoice.
 * Tidak memuat CodeIgniter: pemanggil memberi dua koneksi mysqli ke server yang sama
 * ($fin = schema finance, $gl = koneksi terpisah untuk LOCK TABLES atas GL MyISAM).
 */
class Invoice_wapu_repair
{
    const MARKER = 'KOREKSI-WAPU';
    const OWNER_ID = '7'; // STM: pemilik GL db_sendigs_ss_stm tempat jurnal asli berada

    private $fin;
    private $gl;
    private $f;
    private $g;
    private $c;
    /** Hanya untuk tes: nama titik gagal yang disimulasikan, mis. 'after_gl'. */
    public $failAt = '';

    public function __construct($fin, $gl, $finDb, $glDb, $cnlDb)
    {
        foreach (array($finDb, $glDb, $cnlDb) as $name) {
            if (!preg_match('/^[A-Za-z0-9_]+$/D', (string)$name)) throw new InvalidArgumentException('Nama schema tidak valid.');
        }
        $this->fin = $fin; $this->gl = $gl;
        $this->f = '`' . $finDb . '`.'; $this->g = '`' . $glDb . '`.'; $this->c = '`' . $cnlDb . '`.';
    }

    private function run($conn, $sql, $params = array())
    {
        $st = $conn->prepare($sql);
        if (!$st) throw new RuntimeException('SQL tidak dapat disiapkan: ' . $conn->error);
        if ($params) {
            $values = array_map(function ($v) { return $v === null ? null : (string)$v; }, array_values($params));
            $st->bind_param(str_repeat('s', count($values)), ...$values);
        }
        if (!$st->execute()) { $error = $st->error; $st->close(); throw new RuntimeException('SQL gagal: ' . $error); }
        $result = $st->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
        $affected = $st->affected_rows;
        $st->close();
        return $result ? $rows : $affected;
    }

    private function rows($conn, $sql, $params = array()) { return $this->run($conn, $sql, $params); }
    private function one($conn, $sql, $params = array()) { $r = $this->run($conn, $sql, $params); return $r ? $r[0] : null; }

    private function insert($conn, $table, $row)
    {
        $affected = $this->run($conn, 'INSERT INTO ' . $table . ' (`' . implode('`,`', array_keys($row)) . '`) VALUES (' . implode(',', array_fill(0, count($row), '?')) . ')', array_values($row));
        if ($affected !== 1) throw new RuntimeException('Insert ke ' . $table . ' tidak menambah satu baris.');
    }

    private function money($value)
    {
        return Invoice_calculation::cents(sprintf('%.2f', $value));
    }

    private function roman($m)
    {
        $r = array(1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII');
        return $r[(int)$m];
    }

    /** Tidak boleh ada jejak penerimaan (dan, untuk koreksi pertama, tidak boleh ada marker koreksi). */
    private function assertNoReceipts($id, $no, $withMarker)
    {
        $f = $this->f;
        $checks = array(
            array('SELECT COUNT(*) n FROM ' . $f . 'tr_penerimaan_piutang_detail WHERE id_inv=?', array($id)),
            array('SELECT COUNT(*) n FROM ' . $f . 'tr_penerimaan_pph_23 WHERE id_inv=?', array($id)),
            array("SELECT COUNT(*) n FROM " . $f . "tr_jurnal WHERE jenis_transaksi='Penerimaan Piutang' AND keterangan LIKE ?", array('% - ' . $no)),
            array("SELECT COUNT(*) n FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi<>'Invoicing'", array($id)),
        );
        if ($withMarker) $checks[] = array("SELECT COUNT(*) n FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND keterangan LIKE ?", array($id, '%' . self::MARKER . '%'));
        foreach ($checks as $check) {
            if ((int)$this->one($this->fin, $check[0], $check[1])['n'] !== 0) throw new RuntimeException('Invoice sudah punya penerimaan atau koreksi; koreksi ditolak.');
        }
    }

    /**
     * Tahap lanjutan setelah koreksi: tr_jurnal invoice berisi 4 baris asli (PKP) + 5 baris koreksi, sehingga layar jurnal terbaca seperti jurnal ganda.
     * Ganti dengan 4 baris jurnal WAPU standar (Invoice_consulting::journal). Saldo bersih per akun harus identik; GL tidak disentuh.
     */
    public function normalizePlan($id)
    {
        $f = $this->f;
        $inv = $this->one($this->fin, 'SELECT * FROM ' . $f . 'tr_invoicing WHERE id=?', array($id));
        if (!$inv || $inv['tax_treatment'] !== 'WAPU' || (int)$inv['consulting_version'] !== 3) throw new RuntimeException('Invoice belum dikoreksi ke WAPU versi baru.');
        $snapshot = Invoice_consulting::snapshot($inv);
        $t = Invoice_consulting::totals($inv['total_nominal'], 'WAPU');
        $S = $t['sales']; $P = $t['ppn']; $H = Invoice_calculation::ratio($S, 2, 100);
        if (Invoice_calculation::cents($inv['saldo_piutang']) !== $S || Invoice_calculation::cents($inv['total_akhir_jurnal']) !== $S) throw new RuntimeException('Saldo invoice berubah; perlu rekonsiliasi.');
        $this->assertNoReceipts($id, $inv['no_invoice'], false);
        $rows = $this->rows($this->fin, "SELECT * FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id", array($id));
        $expected = array(array('1102-01-01', $S + $P - $H, 0, false), array('1106-01-02', $H, 0, false), array('2104-01-07', 0, $P, false), array('4101-01-01', 0, $S, false),
            array('2104-01-07', $P, 0, true), array($snapshot['accounts']['vat_wapu_prepaid']['code'], $P, 0, true), array($snapshot['accounts']['vat_wapu_payable']['code'], 0, $P, true),
            array('1102-01-01', 0, $H - $P < 0 ? $P - $H : 0, true), array('1106-01-02', 0, $H, true));
        if (count($rows) !== 9) throw new RuntimeException('tr_jurnal invoice tidak berisi sembilan baris (4 asli + 5 koreksi).');
        $net = array();
        foreach ($rows as $i => $row) {
            $e = $expected[$i];
            if ($row['coa'] !== $e[0] || $this->money($row['debit']) !== $e[1] || $this->money($row['kredit']) !== $e[2] || (strpos($row['keterangan'], self::MARKER) !== false) !== $e[3] || (string)$row['sts'] !== '1') {
                throw new RuntimeException('Baris jurnal ke-' . ($i + 1) . ' berbeda dari yang diharapkan.');
            }
            $net[$row['coa']] = ($net[$row['coa']] ?? 0) + $this->money($row['debit']) - $this->money($row['kredit']);
        }
        $glMarked = (int)$this->one($this->gl, 'SELECT COUNT(*) n FROM ' . $this->g . 'javh WHERE koreksi_no<>? AND keterangan LIKE ?', array('-', '%' . $inv['no_invoice'] . '%' . self::MARKER))['n'];
        if ($glMarked !== 1) throw new RuntimeException('JV koreksi GL untuk invoice ini tidak ditemukan tepat satu.');
        $target = Invoice_consulting::journal($t, $snapshot['accounts'], $snapshot['sales_role'], $inv['no_invoice'], $inv['id_spk_penawaran']);
        $targetNet = array();
        foreach ($target as $line) $targetNet[$line['coa']] = ($targetNet[$line['coa']] ?? 0) + $line['debit'] - $line['credit'];
        $net = array_filter($net); ksort($net); ksort($targetNet);
        if ($net !== $targetNet) throw new RuntimeException('Saldo bersih jurnal sekarang tidak sama dengan jurnal WAPU standar; dibatalkan.');
        return array('id' => $id, 'no_invoice' => $inv['no_invoice'], 'before' => $rows, 'target' => $target, 'net' => $net, 'hash' => hash('sha256', json_encode(array($inv, $rows))));
    }

    /** Ganti 9 baris menjadi 4 baris WAPU standar dalam satu transaksi; backup baris lama wajib ada lebih dulu. */
    public function normalize($id, $hash, $backupPath)
    {
        $plan = $this->normalizePlan($id);
        if (!hash_equals((string)$hash, $plan['hash'])) throw new RuntimeException('Hash tidak cocok dengan kondisi data saat ini; jalankan dry-run ulang.');
        if (file_exists($backupPath)) throw new RuntimeException('Berkas backup sudah ada; tidak ditimpa.');
        $f = $this->f; $fin = $this->fin; $locks = array();
        foreach (array('sendigs_receipt_v2', 'sendigs_invoice_write') as $name) {
            $lock = $this->one($fin, 'SELECT GET_LOCK(?,10) AS acquired', array($name));
            if (!$lock || (int)$lock['acquired'] !== 1) { $this->release($locks); throw new RuntimeException('Transaksi lain sedang berjalan; coba lagi.'); }
            $locks[] = $name;
        }
        try {
            $backup = json_encode(array('created_at' => date(DATE_ATOM), 'mode' => 'normalize', 'id' => $id, 'tr_jurnal_before' => $plan['before']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($backupPath, $backup, LOCK_EX) !== strlen($backup)) throw new RuntimeException('Backup gagal ditulis; tidak ada perubahan.');
            try {
                $fin->begin_transaction();
                $locked = $this->one($fin, 'SELECT * FROM ' . $f . 'tr_invoicing WHERE id=? FOR UPDATE', array($id));
                $rows = $this->rows($fin, "SELECT * FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id FOR UPDATE", array($id));
                if (!$locked || !hash_equals($plan['hash'], hash('sha256', json_encode(array($locked, $rows))))) throw new RuntimeException('Data berubah selama proses; dibatalkan.');
                $this->run($fin, "DELETE FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing'", array($id));
                foreach ($plan['target'] as $i => $line) {
                    $meta = $rows[$i]; // metadata (nomor jurnal, posting, pembuat, company) dipakai ulang dari baris asli ke-i
                    $this->insert($fin, $f . 'tr_jurnal', array('no_jurnal' => $meta['no_jurnal'], 'tgl_jurnal' => $meta['tgl_jurnal'], 'coa' => $line['coa'], 'id_company' => $meta['id_company'],
                        'nm_company' => $meta['nm_company'], 'nm_coa' => $line['name'], 'debit' => Invoice_calculation::decimal($line['debit']), 'kredit' => Invoice_calculation::decimal($line['credit']),
                        'keterangan' => $line['description'], 'sts' => '1', 'tgl_posting' => $meta['tgl_posting'], 'posted_by' => $meta['posted_by'], 'no_transaksi' => $id,
                        'jenis_transaksi' => 'Invoicing', 'created_by' => $meta['created_by'], 'created_date' => $meta['created_date']));
                }
                $after = $this->rows($fin, "SELECT coa,debit,kredit FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing'", array($id));
                $net = array(); $d = 0; $c = 0;
                foreach ($after as $row) { $net[$row['coa']] = ($net[$row['coa']] ?? 0) + $this->money($row['debit']) - $this->money($row['kredit']); $d += $this->money($row['debit']); $c += $this->money($row['kredit']); }
                $net = array_filter($net); ksort($net);
                if (count($after) !== 4 || $d !== $c || $net !== $plan['net']) throw new RuntimeException('Verifikasi akhir jurnal gagal.');
                if (!$fin->commit()) throw new RuntimeException('Commit gagal; periksa status sebelum mencoba lagi.');
            } catch (Throwable $e) {
                try { $fin->rollback(); } catch (Throwable $ignored) {}
                throw $e;
            }
        } finally {
            $this->release($locks);
        }
        return array('id' => $id, 'rows' => 4, 'backup' => $backupPath);
    }

    /**
     * Tahap akhir: GL masih berisi JV asli (PKP) + JV koreksi terpisah. Tulis ulang JV asli menjadi 4 baris WAPU standar, hapus JV koreksi
     * (javh + jurnal) dan baris koreksi kartu piutang. Saldo bersih per akun harus identik. Dipakai setelah normalize() (tr_jurnal sudah 4 baris).
     */
    public function normalizeGlPlan($id)
    {
        $f = $this->f; $g = $this->g;
        $inv = $this->one($this->fin, 'SELECT * FROM ' . $f . 'tr_invoicing WHERE id=?', array($id));
        if (!$inv || $inv['tax_treatment'] !== 'WAPU' || (int)$inv['consulting_version'] !== 3) throw new RuntimeException('Invoice belum dikoreksi ke WAPU versi baru.');
        $snapshot = Invoice_consulting::snapshot($inv); $acc = $snapshot['accounts'];
        $t = Invoice_consulting::totals($inv['total_nominal'], 'WAPU');
        $S = $t['sales']; $P = $t['ppn']; $H = Invoice_calculation::ratio($S, 2, 100);
        if ($H >= $P) throw new RuntimeException('Kasus PPh >= PPN tidak didukung.');
        $this->assertNoReceipts($id, $inv['no_invoice'], false);
        $tj = $this->rows($this->fin, "SELECT coa FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id", array($id));
        if (count($tj) !== 4) throw new RuntimeException('tr_jurnal invoice belum dinormalisasi menjadi empat baris.');
        $javhC = $this->rows($this->gl, 'SELECT * FROM ' . $g . 'javh WHERE koreksi_no<>? AND keterangan LIKE ?', array('-', '%' . $inv['no_invoice'] . '%' . self::MARKER));
        if (count($javhC) !== 1) throw new RuntimeException('JV koreksi GL tidak ditemukan tepat satu (mungkin sudah dinormalisasi).');
        $C = $javhC[0]['nomor']; $O = $javhC[0]['koreksi_no'];
        $javhO = $this->rows($this->gl, 'SELECT * FROM ' . $g . 'javh WHERE nomor=?', array($O));
        if (count($javhO) !== 1 || $this->money($javhO[0]['jml']) !== $S + $P - $H || $this->money($javhC[0]['jml']) !== 2 * $P) throw new RuntimeException('Header JV GL tidak sesuai.');
        $glO = $this->rows($this->gl, 'SELECT * FROM ' . $g . 'jurnal WHERE nomor=? ORDER BY id', array($O));
        $glC = $this->rows($this->gl, 'SELECT * FROM ' . $g . 'jurnal WHERE nomor=? ORDER BY id', array($C));
        $expO = array('1102-01-01' => array($S + $P - $H, 0), '1106-01-02' => array($H, 0), '2104-01-07' => array(0, $P), '4101-01-01' => array(0, $S));
        $expC = array(array($acc['vat_domestic']['code'], $P, 0), array($acc['vat_wapu_prepaid']['code'], $P, 0), array($acc['vat_wapu_payable']['code'], 0, $P), array($acc['receivable']['code'], 0, $P - $H), array($acc['pph']['code'], 0, $H));
        if (count($glO) !== 4 || count($glC) !== 5) throw new RuntimeException('Jumlah baris JV GL tidak sesuai (4 asli + 5 koreksi).');
        $net = array();
        foreach ($glO as $row) {
            if (!isset($expO[$row['no_perkiraan']]) || array($this->money($row['debet']), $this->money($row['kredit'])) !== $expO[$row['no_perkiraan']]) throw new RuntimeException('Baris JV GL asli berbeda: ' . $row['no_perkiraan']);
            unset($expO[$row['no_perkiraan']]);
            $net[$row['no_perkiraan']] = ($net[$row['no_perkiraan']] ?? 0) + $this->money($row['debet']) - $this->money($row['kredit']);
        }
        foreach ($glC as $i => $row) {
            if ($row['no_perkiraan'] !== $expC[$i][0] || $this->money($row['debet']) !== $expC[$i][1] || $this->money($row['kredit']) !== $expC[$i][2]) throw new RuntimeException('Baris JV GL koreksi berbeda: ' . $row['no_perkiraan']);
            $net[$row['no_perkiraan']] = ($net[$row['no_perkiraan']] ?? 0) + $this->money($row['debet']) - $this->money($row['kredit']);
        }
        $cards = $this->rows($this->gl, 'SELECT * FROM ' . $f . 'tr_kartu_piutang WHERE no_reff=? ORDER BY id', array($id));
        if (count($cards) !== 2 || $cards[0]['nomor'] !== $O || $cards[1]['nomor'] !== $C || $this->money($cards[0]['debet']) !== $S + $P - $H || $this->money($cards[0]['kredit']) !== 0
            || $this->money($cards[1]['debet']) !== 0 || $this->money($cards[1]['kredit']) !== $P - $H) throw new RuntimeException('Kartu piutang tidak sesuai (satu debit asli + satu kredit koreksi).');
        $target = Invoice_consulting::journal($t, $acc, $snapshot['sales_role'], $inv['no_invoice'], $inv['id_spk_penawaran']);
        $targetNet = array();
        foreach ($target as $line) $targetNet[$line['coa']] = ($targetNet[$line['coa']] ?? 0) + $line['debit'] - $line['credit'];
        $net = array_filter($net); ksort($net); ksort($targetNet);
        if ($net !== $targetNet) throw new RuntimeException('Saldo bersih GL sekarang tidak sama dengan jurnal WAPU standar; dibatalkan.');
        return array('id' => $id, 'no_invoice' => $inv['no_invoice'], 'original' => $O, 'correction' => $C, 'javh' => array($javhO[0], $javhC[0]), 'gl' => array_merge($glO, $glC),
            'cards' => $cards, 'target' => $target, 'net' => $net, 'gross' => $S + $P, 'sales' => $S, 'glO' => $glO,
            'hash' => hash('sha256', json_encode(array($inv, $javhO, $javhC, $glO, $glC, $cards))));
    }

    /** Tulis ulang GL dan kartu piutang; backup penuh ke berkas lebih dulu; pada kegagalan, kondisi semula dipulihkan dari snapshot. */
    public function normalizeGl($id, $hash, $backupPath)
    {
        $plan = $this->normalizeGlPlan($id);
        if (!hash_equals((string)$hash, $plan['hash'])) throw new RuntimeException('Hash tidak cocok dengan kondisi data saat ini; jalankan dry-run ulang.');
        if (file_exists($backupPath)) throw new RuntimeException('Berkas backup sudah ada; tidak ditimpa.');
        $f = $this->f; $g = $this->g; $gl = $this->gl; $locks = array();
        foreach (array('sendigs_receipt_v2', 'sendigs_invoice_write') as $name) {
            $lock = $this->one($this->fin, 'SELECT GET_LOCK(?,10) AS acquired', array($name));
            if (!$lock || (int)$lock['acquired'] !== 1) { $this->release($locks); throw new RuntimeException('Transaksi lain sedang berjalan; coba lagi.'); }
            $locks[] = $name;
        }
        try {
            $snap = array('javh' => $plan['javh'], 'jurnal' => $plan['gl'], 'kartu' => $plan['cards']);
            $backup = json_encode(array('created_at' => date(DATE_ATOM), 'mode' => 'normalize_gl', 'id' => $id, 'snapshot' => $snap), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($backupPath, $backup, LOCK_EX) !== strlen($backup)) throw new RuntimeException('Backup gagal ditulis; tidak ada perubahan.');
            $gl->query('LOCK TABLES ' . $g . 'javh WRITE, ' . $g . 'jurnal WRITE, ' . $f . 'tr_kartu_piutang WRITE');
            $written = false;
            try {
                $again = $this->normalizeGlPlan($id);
                if (!hash_equals($plan['hash'], $again['hash'])) throw new RuntimeException('Data berubah selama proses; dibatalkan.');
                $written = true;
                foreach ($plan['glO'] as $i => $row) {
                    $line = $plan['target'][$i];
                    $this->run($gl, 'UPDATE ' . $g . 'jurnal SET no_perkiraan=?,keterangan=?,debet=?,kredit=? WHERE id=?', array($line['coa'], $line['description'], $line['debit'] / 100, $line['credit'] / 100, $row['id']));
                }
                $this->run($gl, 'UPDATE ' . $g . 'javh SET jml=? WHERE nomor=?', array($plan['gross'] / 100, $plan['original']));
                $this->run($gl, 'DELETE FROM ' . $g . 'jurnal WHERE nomor=?', array($plan['correction']));
                $this->run($gl, 'DELETE FROM ' . $g . 'javh WHERE nomor=?', array($plan['correction']));
                $this->run($gl, 'UPDATE ' . $f . 'tr_kartu_piutang SET debet=? WHERE id=?', array($plan['sales'] / 100, $plan['cards'][0]['id']));
                $this->run($gl, 'DELETE FROM ' . $f . 'tr_kartu_piutang WHERE id=?', array($plan['cards'][1]['id']));
                $after = $this->rows($gl, 'SELECT no_perkiraan,debet,kredit FROM ' . $g . 'jurnal WHERE nomor=?', array($plan['original']));
                $net = array(); $d = 0; $c = 0;
                foreach ($after as $row) { $net[$row['no_perkiraan']] = ($net[$row['no_perkiraan']] ?? 0) + $this->money($row['debet']) - $this->money($row['kredit']); $d += $this->money($row['debet']); $c += $this->money($row['kredit']); }
                $net = array_filter($net); ksort($net);
                $cardNet = $this->one($gl, 'SELECT SUM(debet-kredit) n, COUNT(*) k FROM ' . $f . 'tr_kartu_piutang WHERE no_reff=?', array($id));
                if (count($after) !== 4 || $d !== $c || $net !== $plan['net'] || (int)$cardNet['k'] !== 1 || $this->money($cardNet['n']) !== $plan['sales']
                    || (int)$this->one($gl, 'SELECT COUNT(*) n FROM ' . $g . 'jurnal WHERE nomor=?', array($plan['correction']))['n'] !== 0) throw new RuntimeException('Verifikasi akhir GL gagal.');
                if ($this->failAt === 'gl_after_writes') throw new RuntimeException('Simulasi kegagalan setelah tulis GL.');
            } catch (Throwable $e) {
                if ($written) $this->restoreGl($plan, $snap);
                throw $e;
            } finally {
                $gl->query('UNLOCK TABLES');
            }
        } finally {
            $this->release($locks);
        }
        return array('id' => $id, 'original' => $plan['original'], 'removed' => $plan['correction'], 'backup' => $backupPath);
    }

    /** Pulihkan baris GL/kartu persis seperti snapshot (id ikut dikembalikan); dipanggil saat tabel terkunci. */
    private function restoreGl($plan, $snap)
    {
        $f = $this->f; $g = $this->g; $gl = $this->gl;
        foreach (array($plan['original'], $plan['correction']) as $nomor) {
            $this->run($gl, 'DELETE FROM ' . $g . 'jurnal WHERE nomor=?', array($nomor));
            $this->run($gl, 'DELETE FROM ' . $g . 'javh WHERE nomor=?', array($nomor));
        }
        foreach ($snap['kartu'] as $row) $this->run($gl, 'DELETE FROM ' . $f . 'tr_kartu_piutang WHERE id=?', array($row['id']));
        foreach ($snap['javh'] as $row) $this->insert($gl, $g . 'javh', $row);
        foreach ($snap['jurnal'] as $row) $this->insert($gl, $g . 'jurnal', $row);
        foreach ($snap['kartu'] as $row) $this->insert($gl, $f . 'tr_kartu_piutang', $row);
    }

    /** Pemeriksaan dan perhitungan tanpa menulis apa pun. */
    public function plan($id)
    {
        $f = $this->f; $g = $this->g; $fin = $this->fin; $gl = $this->gl;
        $inv = $this->one($fin, 'SELECT * FROM ' . $f . 'tr_invoicing WHERE id=?', array($id));
        if (!$inv) throw new RuntimeException('Invoice tidak ditemukan.');
        if (!empty($inv['tax_treatment']) || !empty($inv['consulting_version']) || !empty($inv['consulting_snapshot'])
            || (string)$inv['tipe_invoice'] !== '0' || (string)$inv['non_kons'] !== '0') {
            throw new RuntimeException('Invoice bukan invoice lama biasa yang belum dikonversi.');
        }
        $no = $inv['no_invoice'];

        $t = Invoice_consulting::totals($inv['total_nominal'], 'WAPU');
        $S = $t['sales']; $P = $t['ppn']; $H = Invoice_calculation::ratio($S, 2, 100);
        if ($S % 100 !== 0 || $P % 100 !== 0 || $H % 100 !== 0) throw new RuntimeException('Nominal bukan rupiah bulat; GL menyimpan bilangan bulat.');
        if (Invoice_calculation::cents($inv['ppn_jurnal']) !== $P || Invoice_calculation::cents($inv['pph_jurnal']) !== $H
            || Invoice_calculation::cents($inv['total_akhir_jurnal']) !== $S + $P - $H
            || Invoice_calculation::cents($inv['saldo_piutang']) !== $S + $P - $H || (float)$inv['ppn_persen'] !== 12.0) {
            throw new RuntimeException('Invoice tidak berbentuk PKP 12% dengan saldo penuh; perlu rekonsiliasi.');
        }

        $this->assertNoReceipts($id, $no, true);
        $cards =$this->rows($fin, 'SELECT * FROM ' . $f . 'tr_kartu_piutang WHERE no_reff=?', array($id));
        if (count($cards) !== 1 || $this->money($cards[0]['kredit']) !== 0 || $this->money($cards[0]['debet']) !== $S + $P - $H) {
            throw new RuntimeException('Kartu piutang tidak hanya berisi satu debit invoice; perlu rekonsiliasi.');
        }
        $card = $cards[0]; $origNumber = $card['nomor'];

        // Jurnal operasional asli: empat baris, terposting, satu company, tanggal = tanggal invoice.
        $jr = $this->rows($fin, "SELECT * FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id", array($id));
        $expected = array('1102-01-01' => array($S + $P - $H, 0), '1106-01-02' => array($H, 0), '2104-01-07' => array(0, $P), '4101-01-01' => array(0, $S));
        if (count($jr) !== 4) throw new RuntimeException('Jurnal invoice asli tidak berisi empat baris.');
        $companies = array();
        foreach ($jr as $row) {
            if (!isset($expected[$row['coa']]) || (string)$row['sts'] !== '1' || $row['tgl_jurnal'] !== $inv['tanggal_invoice']
                || array($this->money($row['debit']), $this->money($row['kredit'])) !== $expected[$row['coa']]) {
                throw new RuntimeException('Jurnal invoice asli berbeda dari yang diharapkan: ' . $row['coa']);
            }
            unset($expected[$row['coa']]);
            $companies[$row['id_company'] . '|' . $row['nm_company']] = true;
        }
        if ($expected || count($companies) !== 1) throw new RuntimeException('Jurnal invoice asli tidak lengkap atau company tidak seragam.');
        $source = $jr[0]['id_company'];

        // GL asli: satu javh dan empat baris jurnal yang cocok; belum pernah dikoreksi.
        $javh = $this->rows($gl, 'SELECT * FROM ' . $g . 'javh WHERE nomor=?', array($origNumber));
        $glRows = $this->rows($gl, 'SELECT * FROM ' . $g . 'jurnal WHERE nomor=? ORDER BY id', array($origNumber));
        if (count($javh) !== 1 || $this->money($javh[0]['jml']) !== $S + $P - $H || count($glRows) !== 4) throw new RuntimeException('JV GL asli tidak ditemukan atau tidak cocok.');
        $expectedGl = array('1102-01-01' => array($S + $P - $H, 0), '1106-01-02' => array($H, 0), '2104-01-07' => array(0, $P), '4101-01-01' => array(0, $S));
        $stspos = array();
        foreach ($glRows as $row) {
            if (!isset($expectedGl[$row['no_perkiraan']]) || array($this->money($row['debet']), $this->money($row['kredit'])) !== $expectedGl[$row['no_perkiraan']] || $row['tanggal'] !== $inv['tanggal_invoice']) {
                throw new RuntimeException('Baris GL asli berbeda dari yang diharapkan: ' . $row['no_perkiraan']);
            }
            unset($expectedGl[$row['no_perkiraan']]);
            $stspos[(string)$row['stspos']] = true;
        }
        if ($expectedGl || count($stspos) !== 1) throw new RuntimeException('Baris GL asli tidak lengkap atau stspos tidak seragam.');
        $already = (int)$this->one($gl, 'SELECT COUNT(*) n FROM ' . $g . 'javh WHERE koreksi_no=?', array($origNumber))['n'];
        if ($already !== 0) throw new RuntimeException('JV asli ' . $origNumber . ' sudah pernah dikoreksi.');
        $branch = $this->one($gl, "SELECT subcab,nomorJC FROM " . $g . "pastibisa_tb_cabang WHERE nocab='101'");
        if (!$branch) throw new RuntimeException('Cabang accounting 101 tidak ditemukan.');

        // Mapping COA: owner harus STM dan semua sembilan akun harus ada di master GL (nama unik).
        $map = $this->one($fin, 'SELECT * FROM ' . $f . 'ms_invoice_coa_mapping WHERE source_company_id=?', array($source));
        if (!$map || (string)$map['owner_company_id'] !== self::OWNER_ID) throw new RuntimeException('Mapping COA company ' . $source . ' tidak menunjuk pemilik STM.');
        $codes = json_decode($map['accounts'], true);
        if (!is_array($codes) || array_diff(array_keys(Invoice_consulting::roles()), array_keys($codes))) throw new RuntimeException('Mapping COA tidak lengkap.');
        $master = array();
        foreach ($this->rows($gl, 'SELECT DISTINCT no_perkiraan,nama FROM ' . $g . 'coa_master') as $row) $master[$row['no_perkiraan']][] = $row['nama'];
        $accounts = array();
        foreach (Invoice_consulting::roles() as $role => $label) {
            $code = $codes[$role];
            if (!is_string($code) || !isset($master[$code]) || count($master[$code]) !== 1 || trim($master[$code][0]) === '') throw new RuntimeException('Akun ' . $label . ' (' . (is_string($code) ? $code : '?') . ') tidak valid di master COA STM.');
            $accounts[$role] = array('code' => $code, 'name' => $master[$code][0]);
        }
        $owner = $this->one($fin, 'SELECT id,nm_company FROM ' . $this->c . 'kons_tr_company WHERE id=?', array(self::OWNER_ID));
        if (!$owner || trim($owner['nm_company']) === '') throw new RuntimeException('Company pemilik STM tidak ditemukan.');

        // Snapshot meniru invoice versi baru yang sudah live; TOP dari plan tagih aktual bila ada.
        $actual = $this->one($fin, 'SELECT term_payment,persen_payment,desc_payment FROM ' . $f . 'kons_tr_actual_plan_tagih WHERE id=?', array($inv['id_actual_plan_tagih']));
        $snapshot = array('source_company_id' => (string)$source, 'owner' => array('id' => (string)$owner['id'], 'nm_company' => $owner['nm_company']),
            'accounts' => $accounts, 'sales_role' => 'sales_consulting',
            'top' => array('term_payment' => $actual['term_payment'] ?? '-', 'persen_payment' => $actual['persen_payment'] ?? '0.00', 'desc_payment' => $actual['desc_payment'] ?? '-'));
        $topKey = (string)(int)$inv['id_detail_plan_tagih'];
        if ($topKey === '0' || (int)$this->one($fin, 'SELECT COUNT(*) n FROM ' . $f . 'tr_invoicing WHERE consulting_top_key=?', array($topKey))['n'] !== 0) {
            throw new RuntimeException('consulting_top_key tidak tersedia untuk invoice ini.');
        }

        // JV koreksi netto = target WAPU dikurangi kondisi sekarang.
        $delta = $H - $P; // perubahan debit piutang
        $lines = array(
            array('vat_domestic', $P, 0), array('vat_wapu_prepaid', $P, 0), array('vat_wapu_payable', 0, $P),
            array('receivable', max(0, $delta), max(0, -$delta)), array('pph', 0, $H));
        $journal = array();
        foreach ($lines as $line) {
            $journal[] = array('role' => $line[0], 'coa' => $accounts[$line[0]]['code'], 'name' => $accounts[$line[0]]['name'], 'debit' => $line[1], 'credit' => $line[2],
                'description' => $accounts[$line[0]]['name'] . ' - ' . $no . ' - ' . self::MARKER);
        }
        if (array_sum(array_column($journal, 'debit')) !== array_sum(array_column($journal, 'credit'))) throw new RuntimeException('Jurnal koreksi tidak seimbang.');

        $fields = Invoice_consulting::fields($t) + array('tax_treatment' => 'WAPU', 'consulting_version' => 3, 'consulting_company_id' => self::OWNER_ID,
            'consulting_snapshot' => json_encode($snapshot), 'consulting_top_key' => $topKey);
        // Counter JV sengaja tidak ikut hash: nomor dialokasikan di bawah LOCK TABLES saat apply, dan counter berubah oleh posting siapa pun.
        $hash = hash('sha256', json_encode(array($inv, $jr, $card, $javh, $glRows)));
        return array('id' => $id, 'no_invoice' => $no, 'date' => $inv['tanggal_invoice'], 'sales' => $S, 'ppn' => $P, 'pph' => $H,
            'original_number' => $origNumber, 'next_number' => '101-' . $branch['subcab'] . 'JV' . date('y', strtotime($inv['tanggal_invoice'])) . str_pad((int)$branch['nomorJC'] + 1, 5, '0', STR_PAD_LEFT),
            'journal' => $journal, 'fields' => $fields, 'hash' => $hash, 'card' => $card, 'javh' => $javh[0], 'stspos' => (string)array_keys($stspos)[0],
            'customer' => $inv['nm_customer'], 'source_company' => (string)$source, 'owner' => $snapshot['owner'], 'before' => array_intersect_key($inv, $fields));
    }

    private function checkReview($plan, $review)
    {
        $ok = is_array($review) && !empty($review['approved_by']) && !empty($review['approved_at']) && !empty($review['operator_id'])
            && ($review['period_closed_ack'] ?? null) === true && ($review['pph_reversed_ack'] ?? null) === true
            && isset($review['invoices'][$plan['id']]) && hash_equals((string)$review['invoices'][$plan['id']], $plan['hash']);
        if (!$ok) throw new RuntimeException('Berkas review tidak lengkap atau hash invoice berbeda dari kondisi data saat ini.');
    }

    /** Menulis koreksi satu invoice. Mengembalikan ringkasan; melempar exception bila gagal (GL sudah dikompensasi). */
    public function apply($id, $review, $backupPath)
    {
        $plan = $this->plan($id);
        $this->checkReview($plan, $review);
        $operator = (string)$review['operator_id'];
        if (file_exists($backupPath)) throw new RuntimeException('Berkas backup sudah ada; tidak ditimpa.');

        $f = $this->f; $g = $this->g; $fin = $this->fin; $gl = $this->gl;
        $locks = array();
        foreach (array('sendigs_receipt_v2', 'sendigs_invoice_write') as $name) {
            $lock = $this->one($fin, 'SELECT GET_LOCK(?,10) AS acquired', array($name));
            if (!$lock || (int)$lock['acquired'] !== 1) { $this->release($locks); throw new RuntimeException('Transaksi lain sedang berjalan; coba lagi.'); }
            $locks[] = $name;
        }
        $number = null; $originalCounter = null; $glWritten = false;
        try {
            // Backup baris asli wajib tersimpan sebelum tulisan pertama.
            $backup = json_encode(array('created_at' => date(DATE_ATOM), 'plan' => $plan), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($backupPath, $backup, LOCK_EX) !== strlen($backup)) throw new RuntimeException('Backup gagal ditulis; tidak ada perubahan.');
            // Tahap GL (MyISAM): tulis di bawah LOCK TABLES agar nomor JV tidak bentrok.
            $gl->query('LOCK TABLES ' . $g . 'pastibisa_tb_cabang WRITE, ' . $g . 'javh WRITE, ' . $g . 'jurnal WRITE, ' . $f . 'tr_kartu_piutang WRITE');
            try {
                $branch = $this->one($gl, "SELECT subcab,nomorJC FROM " . $g . "pastibisa_tb_cabang WHERE nocab='101'");
                if (!$branch) throw new RuntimeException('Cabang accounting 101 tidak ditemukan.');
                if ((int)$this->one($gl, 'SELECT COUNT(*) n FROM ' . $g . 'javh WHERE koreksi_no=?', array($plan['original_number']))['n'] !== 0) throw new RuntimeException('JV asli sudah pernah dikoreksi.');
                $originalCounter = (int)$branch['nomorJC'];
                $number = '101-' . $branch['subcab'] . 'JV' . date('y', strtotime($plan['date'])) . str_pad($originalCounter + 1, 5, '0', STR_PAD_LEFT);
                if ((int)$this->one($gl, 'SELECT COUNT(*) n FROM ' . $g . 'javh WHERE nomor=?', array($number))['n'] !== 0
                    || (int)$this->one($gl, 'SELECT COUNT(*) n FROM ' . $g . 'jurnal WHERE nomor=?', array($number))['n'] !== 0) throw new RuntimeException('Nomor JV ' . $number . ' sudah terpakai.');
                $glWritten = true;
                $this->insert($gl, $g . 'javh', array('nomor' => $number, 'tgl' => $plan['date'], 'jml' => array_sum(array_column($plan['journal'], 'debit')) / 100,
                    'koreksi_no' => $plan['original_number'], 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $plan['javh']['keterangan'] . ' - ' . self::MARKER,
                    'bulan' => substr($plan['date'], 5, 2), 'tahun' => substr($plan['date'], 0, 4), 'user_id' => $operator, 'memo' => '',
                    'tgl_jvkoreksi' => $plan['date'], 'ho_valid' => ''));
                foreach ($plan['journal'] as $line) {
                    $this->insert($gl, $g . 'jurnal', array('tipe' => 'JV', 'nomor' => $number, 'tanggal' => $plan['date'], 'no_perkiraan' => $line['coa'],
                        'keterangan' => $line['description'], 'no_reff' => $plan['no_invoice'], 'debet' => $line['debit'] / 100, 'kredit' => $line['credit'] / 100, 'stspos' => $plan['stspos']));
                }
                $this->run($gl, "UPDATE " . $g . "pastibisa_tb_cabang SET nomorJC=nomorJC+1 WHERE nocab='101'");
                $receivable = null;
                foreach ($plan['journal'] as $line) if ($line['role'] === 'receivable') $receivable = $line;
                $card = $plan['card'];
                $this->insert($gl, $f . 'tr_kartu_piutang', array('tipe' => 'JV', 'nomor' => $number, 'tanggal' => $plan['date'], 'no_perkiraan' => $receivable['coa'],
                    'keterangan' => $card['keterangan'], 'no_reff' => $card['no_reff'], 'debet' => $receivable['debit'] / 100, 'kredit' => $receivable['credit'] / 100,
                    'stspos' => $card['stspos'], 'id_supplier' => $card['id_supplier'], 'nama_supplier' => $card['nama_supplier'],
                    'id_company' => $card['id_company'], 'nm_company' => $card['nm_company']));
                if ($this->failAt === 'after_gl') throw new RuntimeException('Simulasi kegagalan setelah GL.');
            } catch (Throwable $e) {
                if ($glWritten) $this->compensate($number, $originalCounter, $plan);
                throw $e;
            } finally {
                $gl->query('UNLOCK TABLES');
            }

            // Tahap finance (InnoDB): atomik.
            try {
                $fin->begin_transaction();
                $row = $this->one($fin, 'SELECT * FROM ' . $f . 'tr_invoicing WHERE id=? FOR UPDATE', array($id));
                if (!$row || !empty($row['tax_treatment']) || !empty($row['consulting_version'])) throw new RuntimeException('Invoice berubah selama proses; dibatalkan.');
                $period = $this->roman(date('m')) . '-' . date('y');
                $max = $this->one($fin, "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(no_jurnal,'-',1) AS UNSIGNED)),0) AS max_no FROM " . $f . "tr_jurnal WHERE no_jurnal LIKE ?", array('%-AJV-' . $period));
                $original = $this->rows($fin, "SELECT nm_company,id_company FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' LIMIT 1", array($id))[0];
                foreach ($plan['journal'] as $i => $line) {
                    $this->insert($fin, $f . 'tr_jurnal', array('no_jurnal' => sprintf('%05d', (int)$max['max_no'] + $i + 1) . '-AJV-' . $period, 'tgl_jurnal' => $plan['date'],
                        'coa' => $line['coa'], 'id_company' => $original['id_company'], 'nm_company' => $original['nm_company'], 'nm_coa' => $line['name'],
                        'debit' => Invoice_calculation::decimal($line['debit']), 'kredit' => Invoice_calculation::decimal($line['credit']), 'keterangan' => $line['description'],
                        'sts' => '1', 'tgl_posting' => $plan['date'] . ' 00:00:00', 'posted_by' => $operator, 'no_transaksi' => $id, 'jenis_transaksi' => 'Invoicing',
                        'created_by' => $operator, 'created_date' => date('Y-m-d H:i:s')));
                }
                $set = array(); foreach (array_keys($plan['fields']) as $k) $set[] = '`' . $k . '`=?';
                $affected = $this->run($fin, 'UPDATE ' . $f . 'tr_invoicing SET ' . implode(',', $set) . ' WHERE id=?', array_merge(array_values($plan['fields']), array($id)));
                if ($affected !== 1) throw new RuntimeException('Update invoice tidak mengubah satu baris.');
                $sum = $this->one($fin, "SELECT SUM(debit) d,SUM(kredit) k, SUM(CASE WHEN coa='1102-01-01' THEN debit-kredit ELSE 0 END) piutang FROM " . $f . "tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing'", array($id));
                if (abs($sum['d'] - $sum['k']) > 0.001 || Invoice_calculation::cents($sum['piutang']) !== $plan['sales']) throw new RuntimeException('Verifikasi akhir jurnal gagal.');
                if ($this->failAt === 'before_commit') throw new RuntimeException('Simulasi kegagalan sebelum commit.');
                if (!$fin->commit()) throw new RuntimeException('Commit gagal; periksa status sebelum mencoba lagi.');
            } catch (Throwable $e) {
                try { $fin->rollback(); } catch (Throwable $ignored) {}
                $this->compensateLocked($number, $originalCounter, $plan);
                throw $e;
            }
        } finally {
            $this->release($locks);
        }
        return array('id' => $id, 'gl_number' => $number, 'backup' => $backupPath);
    }

    private function release($locks)
    {
        foreach ($locks as $name) { try { $this->run($this->fin, 'SELECT RELEASE_LOCK(?)', array($name)); } catch (Throwable $ignored) {} }
    }

    /** Hapus tulisan GL/kartu berdasarkan nomor JV; dipanggil saat tabel sudah terkunci. Counter hanya diturunkan bila belum diubah pihak lain. */
    private function compensate($number, $originalCounter, $plan)
    {
        $g = $this->g; $f = $this->f; $gl = $this->gl;
        $this->run($gl, 'DELETE FROM ' . $g . 'jurnal WHERE nomor=?', array($number));
        $this->run($gl, 'DELETE FROM ' . $g . 'javh WHERE nomor=?', array($number));
        $this->run($gl, 'DELETE FROM ' . $f . 'tr_kartu_piutang WHERE nomor=? AND no_reff=?', array($number, $plan['card']['no_reff']));
        $this->run($gl, "UPDATE " . $g . "pastibisa_tb_cabang SET nomorJC=? WHERE nocab='101' AND nomorJC=?", array($originalCounter, $originalCounter + 1));
    }

    private function compensateLocked($number, $originalCounter, $plan)
    {
        if ($number === null) return;
        $this->gl->query('LOCK TABLES ' . $this->g . 'pastibisa_tb_cabang WRITE, ' . $this->g . 'javh WRITE, ' . $this->g . 'jurnal WRITE, ' . $this->f . 'tr_kartu_piutang WRITE');
        try { $this->compensate($number, $originalCounter, $plan); } finally { $this->gl->query('UNLOCK TABLES'); }
    }
}
