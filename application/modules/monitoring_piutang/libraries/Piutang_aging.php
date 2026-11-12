<?php
defined('BASEPATH') or define('BASEPATH', __DIR__);

/**
 * Satu-satunya tempat aturan umur, kategori, tahap, follow up, kunci step, dan filter
 * Monitoring Piutang / Follow Up Piutang / Dashboard Plan Bayar (MP-NFR-01).
 * Murni PHP tanpa CI/DB supaya layar dan Excel memakai hitungan yang sama dan bisa dites tanpa database.
 *
 * Baris invoice (array): id, no_invoice, tgl_invoice, customer, project, company, nilai, saldo, sts_close,
 * close_date, close_reason, tgl_kirim, kirim_via, ket_kirim, no_resi, tgl_terima, ket_terima, fu_pic_nama, fu_pic_telp,
 * fu{1..4}_plan|aktual|ket, extra_fu [[ke,plan,aktual,ket]], plan_bayar, ket_plan_bayar, plan_bayar_nominal,
 * status_from, status_changed_date.
 */
class Piutang_aging
{
    const VIA = ['JNE', 'Driver'];
    const BARU_HARI = 3;
    const FU_OFFSET = [1 => 3, 2 => 7, 3 => 14, 4 => 14]; // FU1 dari tgl terima, sisanya dari FU sebelumnya dilakukan
    const FU_EXTRA_OFFSET = 14;
    /** Batas tegas mulai Follow Up Piutang dan Dashboard Plan Bayar: tanggal invoice (Follow Up) atau bulan plan/bayar (Plan Bayar) >= ini. */
    const TANGGAL_MULAI_FOLLOW_UP = '2026-10-01';
    const TABS = ['all', '1', '2', '3', '4', 'late', 'close'];
    const FU_FILTERS = ['', 'none', '1', '2', '3', '4', 'tambahan', 'selesai'];
    // Filter company grup: id kons_tr_company (STM 7, STM-Sustain 6, STM-Vuca 1, Vuca 4, Sustain 3; sama dengan modul Request Payment/Jurnal).
    const COMPANY_GROUPS = ['STM' => ['7', '6', '1'], 'VUCA' => ['4'], 'SUSTAIN' => ['3']];

    public static function today()
    {
        $tz = new DateTimeZone('Asia/Bangkok');
        return (new DateTime('now', $tz))->format('Y-m-d');
    }

    public static function is_date($v)
    {
        if (!is_string($v) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
            return false;
        }
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    public static function add_days($date, $n)
    {
        $d = new DateTime($date, new DateTimeZone('UTC'));
        $d->modify(($n >= 0 ? '+' : '') . $n . ' day');
        return $d->format('Y-m-d');
    }

    /** Selisih hari kalender (to - from), tidak terpengaruh jam/DST. */
    public static function days_between($from, $to)
    {
        $a = new DateTime($from, new DateTimeZone('UTC'));
        $b = new DateTime($to, new DateTimeZone('UTC'));
        return (int) $a->diff($b)->format('%r%a');
    }

    private static function filled($v)
    {
        return $v !== null && trim((string) $v) !== '';
    }

    /** Pilihan dropdown filter company: hanya STM, VUCA, SUSTAIN. */
    public static function company_options()
    {
        $out = [];
        foreach (array_keys(self::COMPANY_GROUPS) as $g) {
            $out[] = ['id' => $g, 'nm_company' => $g];
        }
        return $out;
    }

    /** Cocok bila $wanted kosong, kunci grup (STM/VUCA/SUSTAIN) berisi id $actual, atau id company sama persis (kompatibel mundur untuk pemakai lain). */
    public static function company_match($actual, $wanted)
    {
        if (!self::filled($wanted)) {
            return true;
        }
        $wanted = (string) $wanted;
        if (isset(self::COMPANY_GROUPS[$wanted])) {
            return in_array((string) $actual, self::COMPANY_GROUPS[$wanted], true);
        }
        return (string) $actual === $wanted;
    }

    public static function cents($v)
    {
        return (int) round(((float) $v) * 100);
    }

    public static function is_closed($r)
    {
        return (string) ($r['sts_close'] ?? '') === '1';
    }

    // ---- Umur dan kategori ----

    /** Hari sejak customer menerima invoice; null bila belum diterima; tanggal masa depan = 0. */
    public static function umur($tgl_terima, $today)
    {
        if (!self::filled($tgl_terima)) {
            return null;
        }
        return max(0, self::days_between($tgl_terima, $today));
    }

    public static function kategori($umur)
    {
        if ($umur === null) {
            return null;
        }
        return $umur <= 60 ? 'lancar' : ($umur <= 90 ? 'potensi' : 'macet');
    }

    /** Sisa > 0, belum close, sudah diterima customer. */
    public static function is_aged($r)
    {
        return self::cents($r['saldo'] ?? 0) > 0 && !self::is_closed($r) && self::filled($r['tgl_terima'] ?? null);
    }

    /** Sisa > 0, belum close, belum diterima customer (card Belum Diterima). */
    public static function is_unreceived($r)
    {
        return self::cents($r['saldo'] ?? 0) > 0 && !self::is_closed($r) && !self::filled($r['tgl_terima'] ?? null);
    }

    public static function summary($rows, $today)
    {
        $out = ['total' => ['cents' => 0, 'count' => 0, 'oldest' => null], 'belum' => ['cents' => 0, 'count' => 0]];
        foreach (['lancar', 'potensi', 'macet'] as $k) {
            $out[$k] = ['cents' => 0, 'count' => 0, 'oldest' => null, 'pct' => 0.0];
        }
        foreach ($rows as $r) {
            if (self::is_aged($r)) {
                $u = self::umur($r['tgl_terima'], $today);
                $k = self::kategori($u);
                $c = self::cents($r['saldo']);
                foreach ([$k, 'total'] as $key) {
                    $out[$key]['cents'] += $c;
                    $out[$key]['count']++;
                    $out[$key]['oldest'] = max((int) $out[$key]['oldest'], $u);
                }
            } elseif (self::is_unreceived($r)) {
                $out['belum']['cents'] += self::cents($r['saldo']);
                $out['belum']['count']++;
            }
        }
        foreach (['lancar', 'potensi', 'macet'] as $k) {
            $out[$k]['pct'] = $out['total']['cents'] > 0 ? round($out[$k]['cents'] * 100 / $out['total']['cents'], 1) : 0.0;
        }
        $out['grand_cents'] = $out['total']['cents'] + $out['belum']['cents'];
        return $out;
    }

    // ---- Tahap invoice dan follow up ----

    public static function ar_status($r)
    {
        if (self::is_closed($r)) {
            return 'close';
        }
        if (self::filled($r['plan_bayar'] ?? null)) {
            return 'plan_bayar';
        }
        if (self::filled($r['tgl_terima'] ?? null)) {
            return 'diterima';
        }
        if (self::filled($r['tgl_kirim'] ?? null)) {
            return 'dikirim';
        }
        return 'belum_kirim';
    }

    public static function status_label($status)
    {
        $l = ['belum_kirim' => 'Belum dikirim', 'dikirim' => 'Menunggu diterima', 'diterima' => 'Proses follow up',
            'plan_bayar' => 'Menunggu pembayaran', 'close' => 'Close'];
        return $l[$status] ?? $status;
    }

    /** Daftar FU berurutan: FU1-4 lalu tambahan. Tiap item: ke, plan, aktual, ket. */
    public static function fu_list($r)
    {
        $out = [];
        for ($i = 1; $i <= 4; $i++) {
            $out[] = ['ke' => $i, 'plan' => $r["fu{$i}_plan"] ?? null, 'aktual' => $r["fu{$i}_aktual"] ?? null, 'ket' => $r["fu{$i}_ket"] ?? null];
        }
        $extra = $r['extra_fu'] ?? [];
        usort($extra, function ($a, $b) {
            return (int) $a['ke'] <=> (int) $b['ke'];
        });
        foreach ($extra as $e) {
            $out[] = ['ke' => (int) $e['ke'], 'plan' => $e['plan'] ?? null, 'aktual' => $e['aktual'] ?? null, 'ket' => $e['ket'] ?? null];
        }
        return $out;
    }

    /**
     * Bukti terima: file pertama di kolom bukti_terima, file ke-2 dst di bukti_extra (tr_invoice_ar_bukti).
     * Tiap item: path, nama.
     */
    public static function bukti_list($r)
    {
        $out = [];
        if (self::filled($r['bukti_terima'] ?? null)) {
            $out[] = ['path' => $r['bukti_terima'], 'nama' => $r['bukti_terima_nama'] ?? null];
        }
        foreach (($r['bukti_extra'] ?? []) as $b) {
            $out[] = ['path' => $b['path'], 'nama' => $b['nama'] ?? null];
        }
        return $out;
    }

    /**
     * File bukti baru boleh ditambah selama tanggal terima belum tersimpan. Data lama yang sudah terima tapi belum punya
     * bukti boleh melengkapi (sama dengan lock_saved); begitu bukti ada, semua bukti terkunci.
     */
    public static function bukti_boleh_ditambah($old)
    {
        return !self::filled($old['tgl_terima'] ?? null) || !self::filled($old['bukti_terima'] ?? null);
    }

    /** FU yang sedang berjalan (pertama yang belum dilakukan), atau null. FU 1-4 selalu ada; tambahan hanya bila sudah dibuat. */
    public static function fu_current($r)
    {
        if (!self::filled($r['tgl_terima'] ?? null) || self::filled($r['plan_bayar'] ?? null) || self::is_closed($r)) {
            return null;
        }
        foreach (self::fu_list($r) as $f) {
            if (!self::filled($f['aktual'])) {
                return $f;
            }
        }
        return null;
    }

    /** '' belum diterima; '1'-'4'; 'tambahan'; 'selesai'. */
    public static function fu_stage($r)
    {
        if (!self::filled($r['tgl_terima'] ?? null)) {
            return '';
        }
        $c = self::fu_current($r);
        if ($c === null) {
            return 'selesai';
        }
        return $c['ke'] <= 4 ? (string) $c['ke'] : 'tambahan';
    }

    /** Usulan jadwal FU ke-$ke dari data terkini (FU sebelumnya dilakukan; FU1 dari tanggal terima). */
    public static function default_fu_plan($r, $ke)
    {
        if ($ke == 1) {
            return self::filled($r['tgl_terima'] ?? null) ? self::add_days($r['tgl_terima'], self::FU_OFFSET[1]) : null;
        }
        foreach (self::fu_list($r) as $f) {
            if ($f['ke'] == $ke - 1) {
                if (!self::filled($f['aktual'])) {
                    return null;
                }
                return self::add_days($f['aktual'], self::FU_OFFSET[$ke] ?? self::FU_EXTRA_OFFSET);
            }
        }
        return null;
    }

    /** Jadwal FU berjalan: yang tersimpan, atau usulan otomatis. */
    public static function fu_next_plan($r)
    {
        $c = self::fu_current($r);
        if ($c === null) {
            return null;
        }
        return self::filled($c['plan']) ? $c['plan'] : self::default_fu_plan($r, $c['ke']);
    }

    /** FU berjalan yang jadwalnya sudah lewat dan belum ada plan bayar. */
    public static function fu_late($r, $today)
    {
        $p = self::fu_next_plan($r);
        return $p !== null && $p < $today;
    }

    /** Pindah tahap dalam 3 hari terakhir (termasuk hari ini). */
    public static function is_baru($r, $today)
    {
        if (!self::filled($r['status_changed_date'] ?? null)) {
            return false;
        }
        $d = substr($r['status_changed_date'], 0, 10);
        return self::is_date($d) && self::days_between($d, $today) < self::BARU_HARI && self::days_between($d, $today) >= 0;
    }

    /** Invoice belum close tampil di Follow Up: ada sisa, sudah dikirim, atau tanggal invoice >= go-live. */
    public static function ar_visible($r, $golive)
    {
        if (self::is_closed($r)) {
            return false;
        }
        if (self::cents($r['saldo'] ?? 0) > 0 || self::filled($r['tgl_kirim'] ?? null)) {
            return true;
        }
        return self::filled($golive) && self::filled($r['tgl_invoice'] ?? null) && substr($r['tgl_invoice'], 0, 10) >= $golive;
    }

    /** Nilai plan: sisa saat plan disimpan; cadangan sisa sekarang. */
    public static function plan_nominal($r)
    {
        return self::filled($r['plan_bayar_nominal'] ?? null) ? (float) $r['plan_bayar_nominal'] : (float) ($r['saldo'] ?? 0);
    }

    /** Alasan invoice belum boleh di-close, atau null bila boleh. Lunas = sisa 0 persis. */
    public static function close_block_reason($r)
    {
        if (self::is_closed($r)) {
            return 'Invoice sudah close.';
        }
        if (!self::filled($r['plan_bayar'] ?? null)) {
            return 'Invoice belum punya plan bayar.';
        }
        if (self::cents($r['saldo'] ?? 0) !== 0) {
            return 'Invoice belum lunas. Selesaikan sisa piutang di Penerimaan Uang dulu.';
        }
        return null;
    }

    // ---- Validasi ----

    public static function valid_phone($v)
    {
        if (!preg_match('/^\+?[0-9 \-]+$/', $v)) {
            return false;
        }
        $n = strlen(preg_replace('/\D/', '', $v));
        return $n >= 8 && $n <= 20;
    }

    /**
     * Daftar pesan kesalahan untuk isi form $r. $old = data tersimpan (untuk via lama di luar pilihan).
     * Urutan dan isian wajib sama di form dan server (MP-FR-09, MP-FR-10, MP-FR-10a).
     */
    public static function validate_ar($r, $old = [])
    {
        $e = [];
        foreach (['tgl_kirim', 'tgl_terima', 'plan_bayar'] as $k) {
            if (self::filled($r[$k] ?? null) && !self::is_date($r[$k])) {
                $e[] = "Tanggal $k tidak valid.";
            }
        }
        $via = trim((string) ($r['kirim_via'] ?? ''));
        if ($via !== '' && !in_array($via, self::VIA, true) && $via !== trim((string) ($old['kirim_via'] ?? ''))) {
            $e[] = 'Dikirim via harus JNE atau Driver.';
        }
        if (self::filled($r['tgl_kirim'] ?? null) && $via === '') {
            $e[] = 'Dikirim via wajib diisi bila tanggal kirim diisi.';
        }
        // No resi diisi di tahap 2 (Konfirmasi Diterima): wajib untuk JNE saat tanggal terima pertama kali disimpan.
        // Data lama yang sudah diterima tanpa resi tidak diblokir saat disimpan ulang (FU, dll).
        if ($via === 'JNE' && self::filled($r['tgl_terima'] ?? null) && !self::filled($old['tgl_terima'] ?? null) && !self::filled($r['no_resi'] ?? null)) {
            $e[] = 'No resi wajib diisi bila dikirim via JNE.';
        }
        if (strlen((string) ($r['no_resi'] ?? '')) > 100) {
            $e[] = 'No resi maksimal 100 karakter.';
        }
        if (self::filled($r['tgl_terima'] ?? null)) {
            if (!self::filled($r['tgl_kirim'] ?? null)) {
                $e[] = 'Tanggal terima butuh tanggal kirim.';
            } elseif (self::is_date($r['tgl_terima']) && self::is_date($r['tgl_kirim']) && $r['tgl_terima'] < $r['tgl_kirim']) {
                $e[] = 'Tanggal terima tidak boleh sebelum tanggal kirim.';
            }
        }
        $terima = self::filled($r['tgl_terima'] ?? null);
        if ($terima && !self::filled($r['bukti_terima'] ?? null)) {
            $e[] = 'Bukti terima (file) wajib diunggah.';
        }
        $picNama = self::filled($r['fu_pic_nama'] ?? null);
        $picTelp = self::filled($r['fu_pic_telp'] ?? null);
        $fus = self::fu_list($r);
        $anyFu = false;
        foreach ($fus as $f) {
            if (self::filled($f['aktual']) || self::filled($f['plan']) || self::filled($f['ket'])) {
                $anyFu = true;
            }
        }
        if (($anyFu || $picNama || $picTelp || self::filled($r['plan_bayar'] ?? null)) && !$terima) {
            $e[] = 'Follow up, PIC, dan plan bayar butuh tanggal terima.';
        }
        if ($picTelp && !self::valid_phone(trim($r['fu_pic_telp']))) {
            $e[] = 'Nomor WA / telepon PIC harus 8-20 digit.';
        }
        $prevDone = true;
        foreach ($fus as $f) {
            foreach (['plan', 'aktual'] as $k) {
                if (self::filled($f[$k]) && !self::is_date($f[$k])) {
                    $e[] = "FU {$f['ke']}: tanggal $k tidak valid.";
                }
            }
            if (self::filled($f['aktual'])) {
                if (!$prevDone) {
                    $e[] = "FU {$f['ke']} tidak bisa dilakukan sebelum FU sebelumnya.";
                }
                if (!self::filled($f['ket'])) {
                    $e[] = "FU {$f['ke']}: keterangan wajib sebelum tanggal dilakukan diisi.";
                }
                $prevDone = true;
            } else {
                $prevDone = false;
            }
        }
        if (self::filled($r['plan_bayar'] ?? null) && !self::filled($r['ket_plan_bayar'] ?? null)) {
            $e[] = 'Keterangan plan bayar wajib diisi.';
        }
        return array_values(array_unique($e));
    }

    // ---- Kunci step (MP-FR-10b) ----

    /**
     * Gabungkan isi form $new dengan data tersimpan $old: bagian yang sudah terkunci diambil dari $old,
     * isian yang tidak boleh dikosongkan dipertahankan. Dipanggil di server sebelum validasi.
     */
    public static function lock_saved($new, $old)
    {
        if (self::filled($old['plan_bayar'] ?? null)) {
            return $old;
        }
        $r = $new;
        $keep = function (array $keys) use (&$r, $old) {
            foreach ($keys as $k) {
                $r[$k] = $old[$k] ?? null;
            }
        };
        $noEmpty = function (array $keys) use (&$r, $old) {
            foreach ($keys as $k) {
                if (self::filled($old[$k] ?? null) && !self::filled($r[$k] ?? null)) {
                    $r[$k] = $old[$k];
                }
            }
        };
        $oldFu = [];
        foreach (self::fu_list($old) as $f) {
            $oldFu[$f['ke']] = $f;
        }
        $fu1Done = isset($oldFu[1]) && self::filled($oldFu[1]['aktual']);

        // 1. Pengiriman terkunci setelah tanggal terima tersimpan
        if (self::filled($old['tgl_terima'] ?? null)) {
            $keep(['tgl_kirim', 'kirim_via', 'ket_kirim']);
        } else {
            $noEmpty(['tgl_kirim']);
        }
        // 2. Diterima customer terkunci begitu tanggal terima tersimpan (invoice masuk tahap Follow Up), tanpa menunggu FU1.
        //    Pengecualian: data lama tanpa bukti terima boleh melengkapi bukti sekali (validate_ar mewajibkannya); setelah terisi terkunci.
        //    No resi (bagian tahap 2) terkunci bila terisi dan tanggal terima tersimpan.
        if (self::filled($old['tgl_terima'] ?? null)) {
            $keep(['tgl_terima', 'ket_terima']);
            if (self::filled($old['bukti_terima'] ?? null)) {
                $keep(['bukti_terima', 'bukti_terima_nama']);
            } else {
                $noEmpty(['bukti_terima']);
                if (!self::filled($r['bukti_terima'] ?? null)) {
                    $r['bukti_terima_nama'] = $old['bukti_terima_nama'] ?? null;
                }
            }
        } else {
            $noEmpty(['tgl_terima', 'bukti_terima']);
            // nama asli mengikuti file: file baru membawa nama baru, file lama tetap dengan namanya
            if (!self::filled($r['bukti_terima'] ?? null) || $r['bukti_terima'] === ($old['bukti_terima'] ?? null)) {
                $r['bukti_terima_nama'] = $old['bukti_terima_nama'] ?? null;
            }
        }
        if (self::filled($old['tgl_terima'] ?? null) && self::filled($old['no_resi'] ?? null)) {
            $keep(['no_resi']);
        }
        // PIC bisa dikoreksi tapi tidak dikosongkan
        $noEmpty(['fu_pic_nama', 'fu_pic_telp']);

        // 3. FU terkunci begitu tanggal dilakukan tersimpan (tidak bisa dikoreksi maupun dikosongkan)
        for ($ke = 1; $ke <= 4; $ke++) {
            if (isset($oldFu[$ke]) && self::filled($oldFu[$ke]['aktual'])) {
                $keep(["fu{$ke}_plan", "fu{$ke}_aktual", "fu{$ke}_ket"]);
            }
        }
        // FU tambahan: yang sudah dilakukan terkunci dan tidak bisa dihapus
        $newExtra = [];
        foreach ($new['extra_fu'] ?? [] as $e) {
            $newExtra[(int) $e['ke']] = $e;
        }
        $merged = [];
        foreach ($oldFu as $ke => $f) {
            if ($ke < 5) {
                continue;
            }
            if (self::filled($f['aktual'])) {
                $merged[$ke] = $f;
            } elseif (isset($newExtra[$ke])) {
                $merged[$ke] = $newExtra[$ke]; // belum dilakukan: boleh diubah
            }
            // belum dilakukan dan tidak ada di form: dihapus
        }
        foreach ($newExtra as $ke => $e) {
            if (!isset($merged[$ke]) && !isset($oldFu[$ke])) {
                $merged[$ke] = $e; // FU tambahan baru
            }
        }
        ksort($merged);
        $r['extra_fu'] = array_values($merged);
        return $r;
    }

    // ---- Filter (layar dan Excel memakai fungsi yang sama) ----

    /** Format nominal dan tanggal yang ikut dicari. */
    private static function haystack($r, $today)
    {
        $parts = [$r['no_invoice'] ?? '', $r['customer'] ?? '', $r['project'] ?? '', $r['no_spk'] ?? '', $r['company_name'] ?? '', $r['kirim_via'] ?? '',
            $r['ket_kirim'] ?? '', $r['no_resi'] ?? '', $r['ket_terima'] ?? '', $r['fu_pic_nama'] ?? '', $r['fu_pic_telp'] ?? '',
            $r['ket_plan_bayar'] ?? '', self::status_label(self::ar_status($r))];
        foreach (self::fu_list($r) as $f) {
            $parts[] = $f['ket'];
        }
        foreach (['tgl_invoice', 'tgl_kirim', 'tgl_terima', 'plan_bayar'] as $k) {
            $v = isset($r[$k]) ? substr((string) $r[$k], 0, 10) : '';
            if (self::is_date($v)) {
                $parts[] = $v;
                $parts[] = date('d-m-Y', strtotime($v));
                $parts[] = date('d/m/Y', strtotime($v));
            }
        }
        foreach (['nilai', 'saldo'] as $k) {
            if (isset($r[$k])) {
                $parts[] = number_format((float) $r[$k], 2, '.', '');
                $parts[] = number_format((float) $r[$k], 0, ',', '.');
                $parts[] = number_format((float) $r[$k], 2, ',', '.');
            }
        }
        $u = self::umur($r['tgl_terima'] ?? null, $today);
        if ($u !== null) {
            $parts[] = (string) $u;
            $parts[] = (string) self::kategori($u);
        }
        return mb_strtolower(implode(' | ', array_map('strval', $parts)));
    }

    /**
     * Tab: all|1|2|3|4|late|close. Filter: company, customer, cari, fu, plan_bulan, plan_tahun, close_tahun.
     * Filter tidak valid melempar InvalidArgumentException.
     */
    public static function filter_ar_rows($rows, $f, $today, $golive = '')
    {
        $tab = (string) ($f['tab'] ?? 'all');
        if (!in_array($tab, self::TABS, true)) {
            throw new InvalidArgumentException('Tab tidak valid.');
        }
        $fu = (string) ($f['fu'] ?? '');
        if (!in_array($fu, self::FU_FILTERS, true)) {
            throw new InvalidArgumentException('Filter follow up tidak valid.');
        }
        foreach (['plan_bulan' => [1, 12], 'plan_tahun' => [2000, 2100], 'close_tahun' => [2000, 2100], 'inv_bulan' => [1, 12], 'inv_tahun' => [2000, 2100]] as $k => $range) {
            if (self::filled($f[$k] ?? null) && (!ctype_digit((string) $f[$k]) || $f[$k] < $range[0] || $f[$k] > $range[1])) {
                throw new InvalidArgumentException("Filter $k tidak valid.");
            }
        }
        $cari = mb_strtolower(trim((string) ($f['cari'] ?? '')));
        $map = ['1' => 'belum_kirim', '2' => 'dikirim', '3' => 'diterima', '4' => 'plan_bayar'];
        $out = [];
        foreach ($rows as $r) {
            $status = self::ar_status($r);
            if ($tab === 'close') {
                if ($status !== 'close') {
                    continue;
                }
                if (self::filled($f['close_tahun'] ?? null) && substr((string) ($r['close_date'] ?? ''), 0, 4) !== (string) $f['close_tahun']) {
                    continue;
                }
            } else {
                if ($status === 'close' || !self::ar_visible($r, $golive)) {
                    continue;
                }
                if (isset($map[$tab]) && $status !== $map[$tab]) {
                    continue;
                }
                if ($tab === 'late' && !($status === 'diterima' && self::fu_late($r, $today))) {
                    continue;
                }
            }
            if (!self::company_match($r['company'] ?? null, $f['company'] ?? null)) {
                continue;
            }
            if (self::filled($f['customer'] ?? null) && (string) ($r['customer'] ?? '') !== (string) $f['customer']) {
                continue;
            }
            // Tahun/Bulan invoice (Follow Up Piutang): menyaring tanggal invoice di semua tab.
            $tglInv = (string) ($r['tgl_invoice'] ?? '');
            if (self::filled($f['inv_tahun'] ?? null) && substr($tglInv, 0, 4) !== (string) $f['inv_tahun']) {
                continue;
            }
            if (self::filled($f['inv_bulan'] ?? null) && (int) substr($tglInv, 5, 2) !== (int) $f['inv_bulan']) {
                continue;
            }
            if ($fu !== '') {
                $stage = self::fu_stage($r);
                $anyDone = false;
                foreach (self::fu_list($r) as $x) {
                    $anyDone = $anyDone || self::filled($x['aktual']);
                }
                if ($fu === 'none' ? ($stage === '' || $anyDone) : $stage !== $fu) {
                    continue;
                }
            }
            if ($tab === '4') {
                $pb = (string) ($r['plan_bayar'] ?? '');
                if (self::filled($f['plan_tahun'] ?? null) && substr($pb, 0, 4) !== (string) $f['plan_tahun']) {
                    continue;
                }
                if (self::filled($f['plan_bulan'] ?? null) && (int) substr($pb, 5, 2) !== (int) $f['plan_bulan']) {
                    continue;
                }
            }
            if ($cari !== '' && mb_strpos(self::haystack($r, $today), $cari) === false) {
                continue;
            }
            $out[] = $r;
        }
        return $out;
    }

    /** Jumlah invoice per tab untuk filter non-tab yang sama. */
    public static function tab_counts($rows, $f, $today, $golive = '')
    {
        $c = [];
        foreach (self::TABS as $t) {
            $c[$t] = count(self::filter_ar_rows($rows, array_merge($f, ['tab' => $t]), $today, $golive));
        }
        return $c;
    }

    // ---- Monitoring Piutang (MP-FR-01, 06, 07, 12) ----

    const KATEGORI = ['all', 'lancar', 'potensi', 'macet'];

    /**
     * Basis Monitoring: invoice belum close dengan sisa > 0, disaring Company, Customer (persis), Cari.
     * Card, list, rekap, dan Excel semuanya mulai dari basis ini. Filter tidak valid melempar InvalidArgumentException.
     */
    public static function monitoring_base($rows, $f, $today)
    {
        $kat = (string) ($f['kategori'] ?? 'all');
        if (!in_array($kat, self::KATEGORI, true)) {
            throw new InvalidArgumentException('Kategori tidak valid.');
        }
        $cari = mb_strtolower(trim((string) ($f['cari'] ?? '')));
        $out = [];
        foreach ($rows as $r) {
            if (self::is_closed($r) || self::cents($r['saldo'] ?? 0) <= 0) {
                continue;
            }
            if (!self::company_match($r['company'] ?? null, $f['company'] ?? null)) {
                continue;
            }
            if (self::filled($f['customer'] ?? null) && (string) ($r['customer'] ?? '') !== (string) $f['customer']) {
                continue;
            }
            if ($cari !== '' && mb_strpos(self::haystack($r, $today), $cari) === false) {
                continue;
            }
            $out[] = $r;
        }
        return $out;
    }

    /** Daftar piutang ber-umur untuk satu kategori ('all' = semua), umur terlama dulu. */
    public static function aged_list($base, $kategori, $today)
    {
        $out = [];
        foreach ($base as $r) {
            if (!self::is_aged($r)) {
                continue;
            }
            $u = self::umur($r['tgl_terima'], $today);
            if ($kategori === 'all' || self::kategori($u) === $kategori) {
                $out[] = $r;
            }
        }
        usort($out, function ($a, $b) use ($today) {
            return self::umur($b['tgl_terima'], $today) <=> self::umur($a['tgl_terima'], $today);
        });
        return $out;
    }

    /** Sisa piutang ber-umur per customer per kategori (dalam sen), terbesar dulu. */
    public static function rekap_customer($base, $today)
    {
        $by = [];
        foreach ($base as $r) {
            if (!self::is_aged($r)) {
                continue;
            }
            $k = self::kategori(self::umur($r['tgl_terima'], $today));
            $c = (string) ($r['customer'] ?? '');
            $by[$c] = $by[$c] ?? ['customer' => $c, 'lancar' => 0, 'potensi' => 0, 'macet' => 0, 'total' => 0, 'count' => 0];
            $by[$c][$k] += self::cents($r['saldo']);
            $by[$c]['total'] += self::cents($r['saldo']);
            $by[$c]['count']++;
        }
        $by = array_values($by);
        usort($by, function ($a, $b) {
            return [$b['total'], $a['customer']] <=> [$a['total'], $b['customer']];
        });
        return $by;
    }

    // ---- Dashboard Plan Bayar (MP-FR-14, MP-FR-15) ----

    const BULAN = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli',
        8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

    /** Lunas & close / Lunas, belum close / Bayar sebagian (sisa tagihan sekarang). */
    public static function posisi($r)
    {
        if (self::is_closed($r)) {
            return 'Lunas & close';
        }
        return self::cents($r['saldo'] ?? 0) === 0 ? 'Lunas, belum close' : 'Bayar sebagian';
    }

    /** Tanggal bayar - plan bayar: 0 sesuai janji, negatif lebih cepat, positif telat; null bila tanpa plan. */
    public static function ketepatan($tgl_bayar, $plan)
    {
        if (!self::filled($plan) || !self::filled($tgl_bayar)) {
            return ['days' => null, 'label' => '–'];
        }
        $d = self::days_between(substr($plan, 0, 10), substr($tgl_bayar, 0, 10));
        return ['days' => $d, 'label' => $d === 0 ? 'Sesuai janji' : ($d < 0 ? 'Lebih cepat ' . abs($d) . ' hari' : 'Telat ' . $d . ' hari')];
    }

    /** Invoice (termasuk yang sudah close) yang plan bayarnya jatuh di $year, urut tanggal plan. Filter $company opsional. */
    public static function plan_rows($rows, $year, $company, $today)
    {
        $out = [];
        foreach ($rows as $r) {
            if (!self::filled($r['plan_bayar'] ?? null) || (int) substr($r['plan_bayar'], 0, 4) !== (int) $year) {
                continue;
            }
            if (!self::company_match($r['company'] ?? null, $company)) {
                continue;
            }
            $d = self::decorate($r, $today);
            $d['bulan'] = (int) substr($r['plan_bayar'], 5, 2);
            $d['nilai_plan'] = self::plan_nominal($r);
            $d['posisi'] = self::posisi($r);
            $out[] = $d;
        }
        usort($out, function ($a, $b) {
            return [$a['plan_bayar'], $a['no_invoice']] <=> [$b['plan_bayar'], $b['no_invoice']];
        });
        return $out;
    }

    /**
     * Semua penerimaan di $year (dari tanggal transaksi bank), termasuk invoice tanpa plan bayar dan yang sudah close.
     * $receipts: id_inv, no_penerimaan, tanggal, nominal. $invById: id => baris invoice.
     */
    public static function realisasi_rows($receipts, $invById, $year, $company, $today)
    {
        $out = [];
        foreach ($receipts as $p) {
            $inv = $invById[$p['id_inv']] ?? null;
            $tgl = substr((string) ($p['tanggal'] ?? ''), 0, 10);
            if ($inv === null || !self::is_date($tgl) || (int) substr($tgl, 0, 4) !== (int) $year) {
                continue;
            }
            if (!self::company_match($inv['company'] ?? null, $company)) {
                continue;
            }
            $k = self::ketepatan($tgl, $inv['plan_bayar'] ?? null);
            $out[] = $inv + ['tgl_bayar' => $tgl, 'no_penerimaan' => $p['no_penerimaan'], 'nominal' => (float) $p['nominal'], 'bulan' => (int) substr($tgl, 5, 2),
                'ketepatan_days' => $k['days'], 'ketepatan' => $k['label'], 'posisi' => self::posisi($inv)];
        }
        usort($out, function ($a, $b) {
            return [$a['tgl_bayar'], $a['no_penerimaan']] <=> [$b['tgl_bayar'], $b['no_penerimaan']];
        });
        return $out;
    }

    /** 12 bulan: plan (jumlah, sen), realisasi (jumlah, sen), penanda "bulan ini" dan "Lewat" (bulan lewat, masih ada invoice belum close). */
    public static function dashboard_summary($plan, $real, $year, $today)
    {
        $m = [];
        for ($i = 1; $i <= 12; $i++) {
            $m[$i] = ['bulan' => $i, 'nama' => self::BULAN[$i], 'plan_count' => 0, 'plan_cents' => 0, 'real_count' => 0, 'real_cents' => 0, 'open' => 0];
        }
        foreach ($plan as $r) {
            $m[$r['bulan']]['plan_count']++;
            $m[$r['bulan']]['plan_cents'] += self::cents($r['nilai_plan']);
            $m[$r['bulan']]['open'] += self::is_closed($r) ? 0 : 1;
        }
        foreach ($real as $r) {
            $m[$r['bulan']]['real_count']++;
            $m[$r['bulan']]['real_cents'] += self::cents($r['nominal']);
        }
        $ym = (int) $year * 100;
        $now = (int) substr($today, 0, 4) * 100 + (int) substr($today, 5, 2);
        foreach ($m as $i => &$x) {
            $x['current'] = $ym + $i === $now;
            $x['lewat'] = $ym + $i < $now && $x['open'] > 0;
        }
        return array_values($m);
    }

    /** Periode (tahun, bulan) jatuh pada atau setelah TANGGAL_MULAI_FOLLOW_UP. */
    public static function dari_mulai($year, $bulan)
    {
        return (int) $year * 100 + (int) $bulan >= (int) substr(self::TANGGAL_MULAI_FOLLOW_UP, 0, 4) * 100 + (int) substr(self::TANGGAL_MULAI_FOLLOW_UP, 5, 2);
    }

    /** Saring baris plan/realisasi ke satu bulan (1-12); selain itu semua bulan. */
    public static function by_month($rows, $bulan)
    {
        $b = (int) $bulan;
        return $b >= 1 && $b <= 12 ? array_values(array_filter($rows, function ($r) use ($b) {
            return $r['bulan'] === $b;
        })) : $rows;
    }

    /** Turunan per baris yang dipakai layar dan Excel (satu sumber). */
    public static function decorate($r, $today)
    {
        $status = self::ar_status($r);
        $u = self::umur($r['tgl_terima'] ?? null, $today);
        $extra = [];
        foreach (self::fu_list($r) as $f) {
            if ($f['ke'] >= 5) {
                $extra[] = "FU{$f['ke']}: " . ($f['aktual'] ?: ($f['plan'] ? 'jadwal ' . $f['plan'] : '-')) . ($f['ket'] ? " ({$f['ket']})" : '');
            }
        }
        return $r + [
            'status' => $status,
            'status_label' => self::status_label($status),
            'umur' => $u,
            'kategori' => self::kategori($u),
            'fu_stage' => self::fu_stage($r),
            'fu_next_plan' => $status === 'diterima' ? self::fu_next_plan($r) : null,
            'fu_late' => $status === 'diterima' && self::fu_late($r, $today),
            'baru' => self::is_baru($r, $today),
            'baru_from' => self::filled($r['status_from'] ?? null) ? self::status_label($r['status_from']) : '',
            'fu_extra_text' => implode('; ', $extra),
            'close_block' => self::close_block_reason($r),
        ];
    }

    /** Label "Baru" paling atas, lalu sisanya mengikuti urutan masuk. */
    public static function sort_baru($rows, $today)
    {
        usort($rows, function ($a, $b) use ($today) {
            $x = self::is_baru($a, $today) ? 0 : 1;
            $y = self::is_baru($b, $today) ? 0 : 1;
            if ($x !== $y) {
                return $x <=> $y;
            }
            return strcmp((string) ($b['status_changed_date'] ?? ''), (string) ($a['status_changed_date'] ?? ''));
        });
        return $rows;
    }
}
