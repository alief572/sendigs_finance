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
    const FU_BERMASALAH = 'INVOICE BERMASALAH';
    /** Status wajib per FU saat tanggal dilakukan diisi (MP-FR-17); FU 3 dan seterusnya memakai daftar yang sama. */
    const FU_STATUS = [
        1 => ['SUDAH DI TERIMA DI KEUANGAN', 'BELUM DI TERIMA DI KEUANGAN'],
        2 => ['SEDANG DALAM PROSES', 'INVOICE BERMASALAH'],
        3 => ['BELUM PLAN BAYAR', 'MASIH MENUNGGU JAWABAN', 'INVOICE BERMASALAH'],
    ];
    /** Cadangan bila config/piutang.php tidak terbaca; nilai sebenarnya dari tanggal_mulai(). */
    const TANGGAL_MULAI_DEFAULT = '2026-10-01';
    private static $tanggalMulai = null;
    // ponytail: pengecualian tetap untuk 20 invoice; gunakan master terkelola bila cakupan menjadi dinamis.
    const FOLLOW_UP_INVOICES = [
        '239/STM/2023', '020/VSB/2024', '003/VSB/2025', '005/VSB/2025',
        '008/VSB/2025', '011/VSB/2025', '013/VSB/2025', '050/SSC/2025',
        '010/VSB/2026', '011/VSB/2026', '127/STM/2026', '031/VSB/2026',
        '170/STM/2026', '228/STM/2026', '229/STM/2026', '230/STM/2026',
        '235/STM/2026', '204/STM/2026', '240/STM/2026', '018/VSB/2025',
    ];

    public static function is_follow_up_target($r)
    {
        return in_array($r['no_invoice'] ?? '', self::FOLLOW_UP_INVOICES, true);
    }

    /**
     * Batas umum Follow Up/Monitoring dan periode Plan Bayar (satu sumber: config/piutang.php `tanggal_mulai_daftar`);
     * FOLLOW_UP_INVOICES dikecualikan. Dibaca langsung dari file config supaya controller, CLI, dan tes memakai nilai yang sama.
     */
    public static function tanggal_mulai()
    {
        if (self::$tanggalMulai === null) {
            $config = [];
            $file = __DIR__ . '/../../../config/piutang.php';
            if (is_file($file)) {
                include $file;
            }
            $v = (string) ($config['tanggal_mulai_daftar'] ?? '');
            self::$tanggalMulai = self::is_date($v) ? $v : self::TANGGAL_MULAI_DEFAULT;
        }
        return self::$tanggalMulai;
    }

    public static function eligible_invoice_date($r)
    {
        $tgl = substr((string) ($r['tgl_invoice'] ?? ''), 0, 10);
        return self::is_date($tgl) && ($tgl >= self::tanggal_mulai() || self::is_follow_up_target($r));
    }
    const TABS = ['all', '1', '2', '3', '4', 'late', 'bermasalah', 'close'];
    const FU_FILTERS = ['', 'none', '1', '2', '3', '4', 'tambahan', 'selesai', 'bermasalah'];
    // Filter company grup: id kons_tr_company (STM 7, STM-Sustain 6, STM-Vuca 1, Vuca 4, Sustain 3; sama dengan modul Request Payment/Jurnal).
    const COMPANY_GROUPS = ['STM' => ['7', '6', '1'], 'VUCA' => ['4'], 'SUSTAIN' => ['3']];
    const CARI_MAKS = 100;

    /** Parameter request sebagai teks: bentuk array (?x[]=) diabaikan; $max > 0 memotong panjangnya. */
    public static function param($v, $max = 0)
    {
        $v = is_scalar($v) ? trim((string) $v) : '';
        return $max > 0 ? mb_substr($v, 0, $max) : $v;
    }

    /** Invoice masuk cakupan modul piutang: bukan deposit (DEP / KD-) dan tanggalnya memenuhi. Status close dinilai pemanggil. */
    public static function in_scope($r)
    {
        return strtoupper((string) ($r['tax_treatment'] ?? '')) !== 'DEP' && stripos((string) ($r['id'] ?? ''), 'KD-') !== 0
            && self::eligible_invoice_date($r);
    }

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

    /**
     * Dropdown company memakai kunci grup STM/VUCA/SUSTAIN, tetapi ATURAN pemetaan ke id mengikuti aturan asli tiap modul:
     * - 'alias' (jurnal_expense, jurnal_payment, report_jurnal_expense, request_payment): STM=7, VUCA=4+1 (STM-Vuca), SUSTAIN=3+6 (STM-Sustain)
     *   plus alias nm_company untuk jurnal lama.
     * - 'exact' (modul yang dulu cocok id persis): STM=7, VUCA=4, SUSTAIN=3.
     * Follow Up/Monitoring Piutang dan Invoicing tetap memakai COMPANY_GROUPS sendiri.
     */
    const ALIAS_GROUPS = ['STM' => ['7'], 'VUCA' => ['4', '1'], 'SUSTAIN' => ['3', '6']];
    const EXACT_GROUPS = ['STM' => ['7'], 'VUCA' => ['4'], 'SUSTAIN' => ['3']];
    const COMPANY_NAMES = ['STM' => ['STM'], 'VUCA' => ['Vuca', 'STM-Vuca'], 'SUSTAIN' => ['Sustain', 'STM-Sustain', 'SENTRAL SUSTAINABILITY CONSULTING']];
    /** Id lama dari dropdown versi sebelumnya (7/4/3) tetap dibaca sebagai grupnya. */
    const LEGACY_GROUP_IDS = ['7' => 'STM', '4' => 'VUCA', '3' => 'SUSTAIN'];

    /** Pecah nilai filter company (string "STM,VUCA" atau array dari Select2 multiple) jadi daftar unik, tanpa kosong; maks 10. */
    public static function company_list($value)
    {
        $items = is_array($value) ? $value : explode(',', (string) $value);
        $out = [];
        foreach ($items as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $out, true)) {
                $out[] = $item;
            }
        }
        return array_slice($out, 0, 10);
    }

    /** Kunci grup dari satu nilai (kunci grup, atau id lama 7/4/3); null bila bukan grup. */
    public static function jurnal_group($value)
    {
        $value = trim((string) $value);
        if (isset(self::ALIAS_GROUPS[$value])) {
            return $value;
        }
        return self::LEGACY_GROUP_IDS[$value] ?? null;
    }

    /** Gabungan id semua grup dalam $value menurut $rule ('alias' | 'exact'); item bukan grup diabaikan. */
    public static function company_ids($value, $rule = 'alias')
    {
        $ids = [];
        foreach (self::company_list($value) as $item) {
            $g = self::jurnal_group($item);
            if ($g) {
                $ids = array_merge($ids, $rule === 'exact' ? self::EXACT_GROUPS[$g] : self::ALIAS_GROUPS[$g]);
            }
        }
        return array_values(array_unique($ids));
    }

    /** Seperti company_ids, tetapi item bukan grup ikut sebagai id persis (kompatibel mundur). */
    public static function company_values($value, $rule = 'alias')
    {
        $ids = [];
        foreach (self::company_list($value) as $item) {
            $g = self::jurnal_group($item);
            $ids = array_merge($ids, $g ? ($rule === 'exact' ? self::EXACT_GROUPS[$g] : self::ALIAS_GROUPS[$g]) : [$item]);
        }
        return array_values(array_unique($ids));
    }

    /**
     * Terapkan filter company (satu atau banyak, digabung ATAU) ke query builder CI. Grup -> id IN (...) menurut $rule
     * [OR nm IN (...) bila $nameCol diisi dan rule alias]; item lain -> id persis. Beberapa kolom id digabung OR.
     * Kosong = tanpa filter. Dibungkus group_start/end.
     */
    public static function apply_company_filter($db, $idCol, $value, $nameCol = null, $rule = 'alias')
    {
        $list = self::company_list($value);
        if (!$list) {
            return;
        }
        $ids = self::company_values($list, $rule);
        $single = count($ids) === 1 && !self::company_ids($list, $rule);
        $names = [];
        foreach ($list as $item) {
            $g = self::jurnal_group($item);
            if ($g && $nameCol && $rule !== 'exact') {
                $names = array_merge($names, self::COMPANY_NAMES[$g]);
            }
        }
        $db->group_start();
        foreach ((array) $idCol as $i => $col) {
            $f = ($i === 0 ? '' : 'or_') . ($single ? 'where' : 'where_in');
            $db->$f($col, $single ? $ids[0] : $ids);
        }
        if ($names) {
            $db->or_where_in($nameCol, array_values(array_unique($names)));
        }
        $db->group_end();
    }

    /** Cocok bila $wanted kosong, atau $actual cocok ke salah satu item (kunci grup COMPANY_GROUPS atau id persis). */
    public static function company_match($actual, $wanted)
    {
        $list = self::company_list($wanted);
        if (!$list) {
            return true;
        }
        foreach ($list as $item) {
            if (isset(self::COMPANY_GROUPS[$item])) {
                if (in_array((string) $actual, self::COMPANY_GROUPS[$item], true)) {
                    return true;
                }
            } elseif ((string) $actual === $item) {
                return true;
            }
        }
        return false;
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
        // total = card Total Piutang = sudah diterima (Lancar+Potensi+Macet, mockup renderCards); persen L/P/M dari total ini.
        // semua = diterima + belum diterima + lunas belum close (jumlah invoice sama dengan tab Semua Follow Up); lunas tidak menambah nominal.
        $out = ['total' => ['cents' => 0, 'count' => 0, 'oldest' => null, 'customers' => 0], 'belum' => ['cents' => 0, 'count' => 0],
            'semua' => ['cents' => 0, 'count' => 0, 'oldest' => null], 'lunas' => ['count' => 0]];
        foreach (['lancar', 'potensi', 'macet'] as $k) {
            $out[$k] = ['cents' => 0, 'count' => 0, 'oldest' => null, 'pct' => 0.0];
        }
        $cust = [];
        foreach ($rows as $r) {
            if (self::is_aged($r)) {
                $cust[(string) ($r['customer'] ?? '')] = true;
                $u = self::umur($r['tgl_terima'], $today);
                $k = self::kategori($u);
                $c = self::cents($r['saldo']);
                foreach ([$k, 'total', 'semua'] as $key) {
                    $out[$key]['cents'] += $c;
                    $out[$key]['count']++;
                    $out[$key]['oldest'] = max((int) $out[$key]['oldest'], $u);
                }
            } elseif (self::is_unreceived($r)) {
                $out['belum']['cents'] += self::cents($r['saldo']);
                $out['belum']['count']++;
                $out['semua']['cents'] += self::cents($r['saldo']);
                $out['semua']['count']++;
            } elseif (!self::is_closed($r)) {
                $out['lunas']['count']++;
                $out['semua']['count']++;
            }
        }
        foreach (['lancar', 'potensi', 'macet'] as $k) {
            $out[$k]['pct'] = $out['total']['cents'] > 0 ? round($out[$k]['cents'] * 100 / $out['total']['cents'], 1) : 0.0;
        }
        $out['total']['customers'] = count($cust);
        $out['grand_cents'] = $out['total']['cents'] + $out['belum']['cents'];
        return $out;
    }

    // ---- Filter periode Tahun / Bulan / Tanggal dari-sampai (MP-FR-21) ----

    /** Validasi kunci periode: tahun, bulan, dari, sampai. Tidak valid melempar InvalidArgumentException. */
    public static function period_check($f)
    {
        foreach (['tahun' => [2000, 2100], 'bulan' => [1, 12]] as $k => $range) {
            if (self::filled($f[$k] ?? null) && (!ctype_digit((string) $f[$k]) || $f[$k] < $range[0] || $f[$k] > $range[1])) {
                throw new InvalidArgumentException("Filter periode $k tidak valid.");
            }
        }
        foreach (['dari', 'sampai'] as $k) {
            if (self::filled($f[$k] ?? null) && !self::is_date($f[$k])) {
                throw new InvalidArgumentException("Filter periode $k tidak valid.");
            }
        }
        if (self::filled($f['dari'] ?? null) && self::filled($f['sampai'] ?? null) && $f['dari'] > $f['sampai']) {
            throw new InvalidArgumentException('Periode dari tidak boleh setelah sampai.');
        }
    }

    /** Tanggal $tgl (Y-m-d...) masuk periode $f (tahun, bulan, dari, sampai)? Periode kosong = semua. Tanggal kosong ditolak bila ada filter. */
    public static function in_period($tgl, $f)
    {
        $has = self::filled($f['tahun'] ?? null) || self::filled($f['bulan'] ?? null) || self::filled($f['dari'] ?? null) || self::filled($f['sampai'] ?? null);
        if (!$has) {
            return true;
        }
        $d = substr((string) $tgl, 0, 10);
        if (!self::is_date($d)) {
            return false;
        }
        if (self::filled($f['tahun'] ?? null) && substr($d, 0, 4) !== (string) $f['tahun']) {
            return false;
        }
        if (self::filled($f['bulan'] ?? null) && (int) substr($d, 5, 2) !== (int) $f['bulan']) {
            return false;
        }
        if (self::filled($f['dari'] ?? null) && $d < $f['dari']) {
            return false;
        }
        return !(self::filled($f['sampai'] ?? null) && $d > $f['sampai']);
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

    /** Daftar FU berurutan: FU1-4 lalu tambahan. Tiap item: ke, plan, aktual, ket, status. */
    public static function fu_list($r)
    {
        $out = [];
        for ($i = 1; $i <= 4; $i++) {
            $out[] = ['ke' => $i, 'plan' => $r["fu{$i}_plan"] ?? null, 'aktual' => $r["fu{$i}_aktual"] ?? null, 'ket' => $r["fu{$i}_ket"] ?? null, 'status' => $r["fu{$i}_status"] ?? null];
        }
        $extra = $r['extra_fu'] ?? [];
        usort($extra, function ($a, $b) {
            return (int) $a['ke'] <=> (int) $b['ke'];
        });
        foreach ($extra as $e) {
            $out[] = ['ke' => (int) $e['ke'], 'plan' => $e['plan'] ?? null, 'aktual' => $e['aktual'] ?? null, 'ket' => $e['ket'] ?? null, 'status' => $e['status'] ?? null];
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

    /** Opsi status untuk FU ke-$ke. */
    public static function fu_status_options($ke)
    {
        return self::FU_STATUS[min((int) $ke, 3)] ?? self::FU_STATUS[3];
    }

    /** Ada FU dilakukan dengan status INVOICE BERMASALAH: follow up berhenti, plan bayar dan close ditolak (MP-FR-17). */
    public static function is_bermasalah($r)
    {
        foreach (self::fu_list($r) as $f) {
            if (self::filled($f['aktual']) && ($f['status'] ?? null) === self::FU_BERMASALAH) {
                return true;
            }
        }
        return false;
    }

    /** Keterangan FU yang menandai INVOICE BERMASALAH ('' bila tidak ada). */
    public static function ket_bermasalah($r)
    {
        foreach (self::fu_list($r) as $f) {
            if (self::filled($f['aktual']) && ($f['status'] ?? null) === self::FU_BERMASALAH) {
                return (string) ($f['ket'] ?? '');
            }
        }
        return '';
    }

    /** FU yang sedang berjalan (pertama yang belum dilakukan), atau null. FU 1-4 selalu ada; tambahan hanya bila sudah dibuat. */
    public static function fu_current($r)
    {
        if (!self::filled($r['tgl_terima'] ?? null) || self::filled($r['plan_bayar'] ?? null) || self::is_closed($r) || self::is_bermasalah($r)) {
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
        if (self::is_bermasalah($r)) {
            return 'bermasalah';
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
        if (self::is_closed($r) || !self::filled($r['status_changed_date'] ?? null)) {
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

    /**
     * Nilai plan (MP-R-24): sisa saat plan bayar disimpan (plan_bayar_nominal), tidak berkurang setelah invoice dibayar.
     * Tanpa nilai tersimpan: nilai invoice dikurangi penerimaan sebelum tanggal terima ($r['bayar_sebelum_terima'], diisi model),
     * bukan sisa sekarang karena bisa 0 untuk invoice yang sudah lunas.
     */
    public static function plan_nominal($r)
    {
        if (self::filled($r['plan_bayar_nominal'] ?? null)) {
            return (float) $r['plan_bayar_nominal'];
        }
        return max(0.0, (float) ($r['nilai'] ?? 0) - (float) ($r['bayar_sebelum_terima'] ?? 0));
    }

    /** Alasan invoice belum boleh di-close, atau null bila boleh. Lunas = sisa 0 persis. */
    public static function close_block_reason($r)
    {
        if (self::is_closed($r)) {
            return 'Invoice sudah close.';
        }
        if (self::is_bermasalah($r)) {
            return 'Invoice bermasalah belum bisa di-close.';
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
        $oldDone = [];
        foreach (self::fu_list($old) as $x) {
            $oldDone[$x['ke']] = self::filled($x['aktual']);
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
                if (!self::filled($f['status'] ?? null)) {
                    // FU lama yang sudah dilakukan sebelum ada status tidak memblokir simpan ulang
                    if (empty($oldDone[$f['ke']])) {
                        $e[] = "FU {$f['ke']}: status wajib diisi bila tanggal dilakukan diisi.";
                    }
                } elseif (!in_array($f['status'], self::fu_status_options($f['ke']), true)) {
                    $e[] = "FU {$f['ke']}: status tidak valid.";
                }
                $prevDone = true;
            } else {
                $prevDone = false;
            }
        }
        if (self::filled($r['plan_bayar'] ?? null) && self::is_bermasalah($r)) {
            $e[] = 'Invoice bermasalah tidak bisa diberi plan bayar.';
        }
        $faktur = trim((string) ($r['no_faktur'] ?? ''));
        if (mb_strlen($faktur) > 100 && $faktur !== trim((string) ($old['no_faktur'] ?? ''))) {
            $e[] = 'No faktur maksimal 100 karakter.';
        }
        // No faktur (MP-FR-18):wajib saat tanggal terima pertama kali disimpan; data lama tanpa faktur tidak diblokir saat simpan ulang.
        if (self::filled($r['tgl_terima'] ?? null) && !self::filled($r['no_faktur'] ?? null) && (!self::filled($old['tgl_terima'] ?? null) || self::filled($old['no_faktur'] ?? null))) {
            $e[] = 'No faktur wajib diisi.';
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

        // 1. Pengiriman terkunci begitu tersimpan (MP-R-19); yang masih diisi di tahap terima hanya no resi
        if (self::filled($old['tgl_kirim'] ?? null)) {
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
        // No faktur ikut terkunci setelah FU 1 dilakukan; sebelumnya boleh dikoreksi tapi tidak dikosongkan (MP-FR-18)
        if ($fu1Done) {
            $keep(['no_faktur']);
        } else {
            $noEmpty(['no_faktur']);
        }
        // PIC opsional dan boleh dikosongkan (MP-R-19)

        // 3. FU terkunci begitu tanggal dilakukan tersimpan (tidak bisa dikoreksi maupun dikosongkan)
        for ($ke = 1; $ke <= 4; $ke++) {
            if (isset($oldFu[$ke]) && self::filled($oldFu[$ke]['aktual'])) {
                $keep(["fu{$ke}_plan", "fu{$ke}_aktual", "fu{$ke}_ket", "fu{$ke}_status"]);
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
            $r['ket_plan_bayar'] ?? '', self::status_label(self::ar_status($r)), $r['no_faktur'] ?? '', $r['no_po'] ?? '', $r['konsultan'] ?? '',
            $r['close_reason'] ?? ''];
        if (self::filled($r['top_ke'] ?? null)) {
            $parts[] = 'TOP ' . $r['top_ke'];
        }
        if (self::is_bermasalah($r)) {
            $parts[] = 'Bermasalah';
        }
        foreach (self::fu_list($r) as $f) {
            $parts[] = $f['ket'];
            $parts[] = $f['status'];
        }
        $stage = self::fu_stage($r);
        if (ctype_digit($stage)) {
            $parts[] = 'FU ' . $stage;
        }
        if (isset($r['nilai'], $r['saldo'])) {
            $parts[] = number_format(self::cents($r['nilai']) / 100 - self::cents($r['saldo']) / 100, 0, ',', '.');
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
            $parts[] = $u . ' hari';
            $parts[] = (string) self::kategori($u);
            $parts[] = self::kategori_label(self::kategori($u));
        }
        return mb_strtolower(implode(' | ', array_map('strval', $parts)));
    }

    /** Cari banyak kata (mockup searchMatch): semua kata harus ada di haystack, urutan bebas. */
    private static function cari_match($r, $cari, $today)
    {
        if ($cari === '') {
            return true;
        }
        $h = self::haystack($r, $today);
        foreach (preg_split('/\s+/u', $cari, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            if (mb_strpos($h, $w) === false) {
                return false;
            }
        }
        return true;
    }

    /** Nama kategori untuk layar/Excel: Lancar / Potensi Macet / Macet ('' bila kosong). */
    public static function kategori_label($k)
    {
        $l = ['lancar' => 'Lancar', 'potensi' => 'Potensi Macet', 'macet' => 'Macet'];
        return $l[(string) $k] ?? '';
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
        self::period_check(['tahun' => $f['inv_tahun'] ?? '', 'bulan' => $f['inv_bulan'] ?? '', 'dari' => $f['inv_dari'] ?? '', 'sampai' => $f['inv_sampai'] ?? '']);
        $inv = ['tahun' => $f['inv_tahun'] ?? '', 'bulan' => $f['inv_bulan'] ?? '', 'dari' => $f['inv_dari'] ?? '', 'sampai' => $f['inv_sampai'] ?? ''];
        foreach (['plan_bulan' => [1, 12], 'plan_tahun' => [2000, 2100], 'close_tahun' => [2000, 2100]] as $k => $range) {
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
                // Invoice bermasalah pindah ke tab sendiri (MP-FR-17); tetap tampil di tab Semua.
                $bm = self::is_bermasalah($r);
                if ($tab === 'bermasalah' ? !$bm : ($bm && $tab !== 'all')) {
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
            // Periode tanggal invoice (Follow Up Piutang): berlaku di semua tab (MP-FR-21).
            if ($tab !== 'close' && !self::in_period($r['tgl_invoice'] ?? '', $inv)) {
                continue;
            }
            // 'none' = Belum ada FU (belum diterima), sama dengan mockup (fuTahap kosong).
            if ($fu !== '' && self::fu_stage($r) !== ($fu === 'none' ? '' : $fu)) {
                continue;
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
            if (!self::cari_match($r, $cari, $today)) {
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

    /**
     * Card ringkasan Follow Up (MP-FR-22): jumlah invoice + nominal per kartu. Ikut filter non-tab (company, customer, periode, cari);
     * filter fu, tab, dan bulan/tahun plan diabaikan. Nominal = sisa piutang; Closed = nilai invoice yang di-close pada periode
     * filter (tanggal close; mockup), bukan Tahun Close. Hasil: kunci => [count, cents].
     */
    public static function ar_cards($rows, $f, $today, $golive = '')
    {
        $base = array_merge($f, ['fu' => '', 'plan_bulan' => '', 'plan_tahun' => '']);
        $period = ['tahun' => $f['inv_tahun'] ?? '', 'bulan' => $f['inv_bulan'] ?? '', 'dari' => $f['inv_dari'] ?? '', 'sampai' => $f['inv_sampai'] ?? ''];
        $def = ['belum_kirim' => ['1', ''], 'belum_diterima' => ['2', ''], 'belum_fu' => ['3', '1'], 'tunggu_bayar' => ['4', ''],
            'terlambat' => ['late', ''], 'bermasalah' => ['bermasalah', ''], 'closed' => ['close', '']];
        $out = [];
        foreach ($def as $k => $d) {
            $cents = 0;
            $list = self::filter_ar_rows($rows, array_merge($base, ['tab' => $d[0], 'fu' => $d[1]], $k === 'closed' ? ['close_tahun' => ''] : []), $today, $golive);
            if ($k === 'closed') {
                $list = array_filter($list, function ($r) use ($period) {
                    return self::in_period($r['close_date'] ?? '', $period);
                });
            }
            foreach ($list as $r) {
                $cents += self::cents($k === 'closed' ? ($r['nilai'] ?? 0) : ($r['saldo'] ?? 0));
            }
            $out[$k] = ['count' => count($list), 'cents' => $cents];
        }
        return $out;
    }

    // ---- Monitoring Piutang (MP-FR-01, 06, 07, 12) ----

    const KATEGORI = ['all', 'lancar', 'potensi', 'macet'];

    /**
     * Basis Monitoring: invoice belum close (termasuk yang sudah lunas, agar jumlah card sama dengan Follow Up Piutang) dan
     * tanggal invoice memenuhi batas mulai atau termasuk target, disaring Company, Customer (persis), Cari. List, rekap, dan nominal
     * hanya memakai yang bersisa (is_aged / is_unreceived).
     * Card, list, rekap, dan Excel semuanya mulai dari basis ini. Filter tidak valid melempar InvalidArgumentException.
     */
    public static function monitoring_base($rows, $f, $today)
    {
        $kat = (string) ($f['kategori'] ?? 'all');
        if (!in_array($kat, self::KATEGORI, true)) {
            throw new InvalidArgumentException('Kategori tidak valid.');
        }
        $cari = mb_strtolower(trim((string) ($f['cari'] ?? '')));
        self::period_check($f);
        $out = [];
        foreach ($rows as $r) {
            if (self::is_closed($r)) {
                continue;
            }
            if (!self::eligible_invoice_date($r)) {
                continue;
            }
            if (!self::in_period($r['tgl_invoice'] ?? '', $f)) {
                continue;
            }
            if (!self::company_match($r['company'] ?? null, $f['company'] ?? null)) {
                continue;
            }
            if (self::filled($f['customer'] ?? null) && (string) ($r['customer'] ?? '') !== (string) $f['customer']) {
                continue;
            }
            if (!self::cari_match($r, $cari, $today)) {
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

    const PAGE_SORT_KEYS = ['no_invoice', 'customer', 'nilai', 'terbayar', 'saldo', 'umur'];
    const REKAP_SORT_KEYS = ['customer', 'count', 'lancar', 'potensi', 'macet', 'total', 'oldest'];

    /**
     * Satu halaman DataTables server-side dari baris list yang sudah jadi. $key di luar PAGE_SORT_KEYS = urutan bawaan
     * (tidak diurutkan ulang). Urutan stabil. $length < 0 = semua baris.
     */
    public static function page_rows(array $rows, $key, $dir, $start, $length, array $allowed = self::PAGE_SORT_KEYS)
    {
        if (in_array($key, $allowed, true)) {
            $mul = $dir === 'desc' ? -1 : 1;
            $text = in_array($key, ['no_invoice', 'customer'], true);
            $pos = [];
            foreach (array_values($rows) as $i => $r) {
                $pos[] = [$i, $r];
            }
            usort($pos, function ($a, $b) use ($key, $mul, $text) {
                $c = $text ? strnatcasecmp((string) $a[1][$key], (string) $b[1][$key]) : (float) $a[1][$key] <=> (float) $b[1][$key];
                return $c * $mul ?: $a[0] <=> $b[0];
            });
            $rows = array_column($pos, 1);
        }
        $length = (int) $length;
        return array_slice(array_values($rows), max(0, (int) $start), $length < 0 ? null : $length);
    }

    /**
     * Sisa piutang ber-umur per customer per kategori (dalam sen), terbesar dulu. $kategori selain 'all' = hanya invoice
     * kategori itu (ikut card yang dipilih, mockup). company = nama company (digabung bila lebih dari satu), oldest = umur tertua.
     */
    public static function rekap_customer($base, $today, $kategori = 'all')
    {
        $by = [];
        foreach ($base as $r) {
            if (!self::is_aged($r)) {
                continue;
            }
            $u = self::umur($r['tgl_terima'], $today);
            $k = self::kategori($u);
            if ($kategori !== 'all' && $k !== $kategori) {
                continue;
            }
            $c = (string) ($r['customer'] ?? '');
            $by[$c] = $by[$c] ?? ['customer' => $c, 'company' => [], 'lancar' => 0, 'potensi' => 0, 'macet' => 0, 'total' => 0, 'count' => 0, 'oldest' => 0];
            $by[$c][$k] += self::cents($r['saldo']);
            $by[$c]['total'] += self::cents($r['saldo']);
            $by[$c]['count']++;
            $by[$c]['oldest'] = max($by[$c]['oldest'], $u);
            $by[$c]['company'][(string) ($r['company_name'] ?? '')] = true;
        }
        foreach ($by as &$x) {
            $x['company'] = implode(', ', array_filter(array_keys($x['company']), 'strlen'));
        }
        unset($x);
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

    /** Tanggal bayar - plan bayar: 0 sesuai plan, negatif lebih cepat, positif telat; null bila tanpa plan. */
    public static function ketepatan($tgl_bayar, $plan)
    {
        if (!self::filled($plan) || !self::filled($tgl_bayar)) {
            return ['days' => null, 'label' => '–'];
        }
        $d = self::days_between(substr($plan, 0, 10), substr($tgl_bayar, 0, 10));
        return ['days' => $d, 'label' => $d === 0 ? 'Sesuai plan' : ($d < 0 ? 'Lebih cepat ' . abs($d) . ' hari' : 'Telat ' . $d . ' hari')];
    }

    /** Invoice (termasuk yang sudah close) yang plan bayarnya jatuh di $year, urut tanggal plan. Filter $company opsional. */
    public static function plan_rows($rows, $year, $company, $today, $range = [])
    {
        $out = [];
        foreach ($rows as $r) {
            if (!self::filled($r['plan_bayar'] ?? null) || (int) substr($r['plan_bayar'], 0, 4) !== (int) $year) {
                continue;
            }
            if (!self::in_period($r['plan_bayar'], $range)) {
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
    public static function realisasi_rows($receipts, $invById, $year, $company, $today, $range = [])
    {
        $out = [];
        foreach ($receipts as $p) {
            $inv = $invById[$p['id_inv']] ?? null;
            $tgl = substr((string) ($p['tanggal'] ?? ''), 0, 10);
            if ($inv === null || !self::is_date($tgl) || (int) substr($tgl, 0, 4) !== (int) $year || !self::in_period($tgl, $range)) {
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

    /** Periode memenuhi batas umum atau berasal dari invoice target. */
    public static function dari_mulai($year, $bulan, $r = [])
    {
        $mulai = self::tanggal_mulai();
        return self::is_follow_up_target($r) || (int) $year * 100 + (int) $bulan >= (int) substr($mulai, 0, 4) * 100 + (int) substr($mulai, 5, 2);
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
        $last = null;
        foreach (self::fu_list($r) as $f) {
            if (self::filled($f['aktual'])) {
                $last = $f;
            }
            if ($f['ke'] >= 5) {
                // mockup: "FU n: tanggal · STATUS · ket" bila dilakukan, "FU n: jadwal tanggal" bila belum
                $dmy = function ($d) { return self::is_date(substr((string) $d, 0, 10)) ? date('d-m-Y', strtotime(substr($d, 0, 10))) : '-'; };
                $extra[] = "FU{$f['ke']}: " . (self::filled($f['aktual']) ? $dmy($f['aktual']) . (self::filled($f['status']) ? ' · ' . $f['status'] : '') . (self::filled($f['ket']) ? ' · ' . $f['ket'] : '')
                    : 'jadwal ' . $dmy($f['plan']));
            }
        }
        $cur = $status === 'diterima' ? self::fu_current($r) : null;
        return $r + [
            'status' => $status,
            'status_label' => self::status_label($status),
            'umur' => $u,
            'kategori' => self::kategori($u),
            // nama kategori untuk Excel; hanya bila masih bersisa (mockup: kategori kosong untuk invoice lunas)
            'kategori_label' => self::cents($r['saldo'] ?? 0) > 0 ? self::kategori_label(self::kategori($u)) : '',
            'fu_stage' => self::fu_stage($r),
            'fu_next_plan' => $status === 'diterima' ? self::fu_next_plan($r) : null,
            'fu_next_ke' => $cur ? (int) $cur['ke'] : null,
            // FU terakhir yang dilakukan + statusnya (pill "status FU n" di daftar, mockup lastStTag)
            'fu_last_ke' => $last ? (int) $last['ke'] : null,
            'fu_last_status' => $last ? (string) ($last['status'] ?? '') : '',
            'fu_late' => $status === 'diterima' && self::fu_late($r, $today),
            'baru' => self::is_baru($r, $today),
            'baru_from' => self::filled($r['status_from'] ?? null) ? self::status_label($r['status_from']) : '',
            'fu_extra_text' => implode('; ', $extra),
            'close_block' => self::close_block_reason($r),
            'bermasalah' => self::is_bermasalah($r),
            'ket_bermasalah' => self::ket_bermasalah($r),
            'terbayar' => self::cents($r['nilai'] ?? 0) / 100 - self::cents($r['saldo'] ?? 0) / 100,
            'bukti_text' => implode(', ', array_map(function ($b) { return (string) ($b['nama'] ?: basename((string) $b['path'])); }, self::bukti_list($r))),
            'bukti_jumlah' => count(self::bukti_list($r)),
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
