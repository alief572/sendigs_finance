<?php
defined('BASEPATH') or define('BASEPATH', __DIR__);

require_once __DIR__ . '/Piutang_aging.php';

/**
 * Surat Konfirmasi Status Tagihan (MP-FR-16). Format mengikuti contoh STM (mockup, Format Surat Konfirmasi.pdf).
 * Murni PHP tanpa CI/DB: aturan siapa yang boleh dicetak, isi surat, dan HTML cetak bisa dites langsung.
 */
class Surat_konfirmasi
{
    const ROMAWI = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    const URUTAN = ['Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh', 'Kedelapan', 'Kesembilan', 'Kesepuluh'];

    /** Profil kop per company, kunci = id company. Company tanpa profil memakai kop teks nama company. shortcut: hanya STM (id 7), tambah profil saat format company lain diterima. */
    public static function profiles()
    {
        return ['7' => ['kop' => 'stm', 'pt' => 'PT. Sentral Tehnologi Managemen', 'kode' => 'STM', 'kota' => 'Jakarta', 'ttd' => 'Tati Rina', 'jabatan' => 'Manager Accounting',
            'tagline' => 'QUALITY, ENVIRONMENT, HEALTH, SAFETY, PROCESS IMPROVEMENT, STRATEGIC BUSINESS PLAN',
            'footer' => ['MT. Haryono Square Lt. 3A/4 No. 2', 'Jl. MT. Haryono Kav. 10, Jakarta Timur', 'Telp. (021) 29067201-3, Fax . (021) 29067204',
                'Email Address : sspm@sentral-sistem.com  Website. www.sentral-sistem.com']]];
    }

    public static function profile_for($r)
    {
        $p = self::profiles();
        $id = (string) ($r['company'] ?? '');
        if (isset($p[$id])) {
            return $p[$id];
        }
        $name = (string) ($r['company_name'] ?? $id);
        return ['kop' => 'default', 'pt' => $name, 'kode' => $name, 'kota' => 'Jakarta', 'ttd' => '', 'jabatan' => '', 'tagline' => '', 'footer' => []];
    }

    /** Alasan surat tidak boleh dicetak, atau null. Hanya Potensi Macet / Macet, belum close, masih bersisa. */
    public static function can_print($r, $today)
    {
        if (Piutang_aging::is_closed($r)) {
            return 'Invoice sudah close.';
        }
        if (Piutang_aging::cents($r['saldo'] ?? 0) <= 0) {
            return 'Invoice sudah tidak bersisa.';
        }
        $kat = Piutang_aging::kategori(Piutang_aging::umur($r['tgl_terima'] ?? null, $today));
        if ($kat === null) {
            return 'Invoice belum diterima customer.';
        }
        return $kat === 'lancar' ? 'Surat konfirmasi hanya untuk invoice Potensi Macet atau Macet.' : null;
    }

    /** Lama penagihan sejak diterima: "lebih dari N bulan" / "N bulan" / "N hari". */
    public static function lama_tagih($terima, $today)
    {
        $a = new DateTime($terima, new DateTimeZone('UTC'));
        $b = new DateTime($today, new DateTimeZone('UTC'));
        $m = ((int) $b->format('Y') - (int) $a->format('Y')) * 12 + (int) $b->format('n') - (int) $a->format('n');
        if ((int) $b->format('j') - (int) $a->format('j') < 0) {
            $m--;
        }
        if ($m < 1) {
            return Piutang_aging::days_between($terima, $today) . ' hari';
        }
        $edge = (clone $a)->modify("+$m month");
        return ($edge < $b ? 'lebih dari ' : '') . $m . ' bulan';
    }

    public static function tagihan($top_ke, $project)
    {
        $top_ke = (int) $top_ke;
        $ord = $top_ke > 0 ? ' ' . (self::URUTAN[$top_ke - 1] ?? 'ke-' . $top_ke) : '';
        return 'Tagihan' . $ord . ' ' . $project;
    }

    public static function nomor($kode, $today)
    {
        return '…/' . $kode . '-Fnc/SK/' . self::ROMAWI[(int) substr($today, 5, 2) - 1] . '/' . substr($today, 2, 2);
    }

    public static function tanggal_id($iso)
    {
        if (!Piutang_aging::is_date((string) $iso)) {
            return '–';
        }
        return (int) substr($iso, 8, 2) . ' ' . Piutang_aging::BULAN[(int) substr($iso, 5, 2)] . ' ' . substr($iso, 0, 4);
    }

    public static function rp($n)
    {
        return 'Rp. ' . number_format(round((float) $n), 0, ',', '.');
    }

    public static function data($r, $today)
    {
        $pr = self::profile_for($r);
        $nilai = (float) ($r['nilai'] ?? 0);
        $sisa = (float) ($r['saldo'] ?? 0);
        return ['profile' => $pr, 'today' => $today, 'nomor' => self::nomor($pr['kode'], $today), 'customer' => (string) ($r['customer'] ?? ''),
            'tagihan' => self::tagihan($r['top_ke'] ?? 0, (string) ($r['project'] ?? '')), 'invoice' => (string) ($r['no_invoice'] ?? ''),
            'tgl_invoice' => (string) ($r['tgl_invoice'] ?? ''), 'nilai' => $nilai, 'terbayar' => max(0, $nilai - $sisa), 'sisa' => $sisa,
            'lama' => self::lama_tagih(substr((string) $r['tgl_terima'], 0, 10), $today)];
    }

    /** HTML cetak (satu halaman A4). Semua nilai di-escape. $assetBase = URL folder gambar kop (berakhir dengan /). */
    public static function render(array $d, $assetBase)
    {
        $e = function ($s) {
            return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        };
        $pr = $d['profile'];
        $a = $e($assetBase);
        $kop = $pr['kop'] === 'stm'
            ? '<div class="kop-stm"><img class="logo" src="' . $a . 'stm_kop_logo.jpg" alt=""><img class="nama" src="' . $a . 'stm_kop_nama.jpg" alt="' . $e(strtoupper($pr['pt'])) . '"><img class="sert" src="' . $a . 'stm_kop_sertifikat.jpg" alt=""></div>'
              . '<div class="kop-tag">' . $e($pr['tagline']) . '</div><div class="kop-line"></div><div class="kop-line2"></div>'
            : '<div class="kop-default">' . $e($pr['pt']) . '</div>';
        $paid = $d['terbayar'] > 0
            ? '<tr><td>Sudah dibayar</td><td>: ' . $e(self::rp($d['terbayar'])) . '</td></tr><tr><td>Sisa tagihan</td><td>: <b>' . $e(self::rp($d['sisa'])) . '</b></td></tr>' : '';
        $footer = $pr['footer'] ? '<div class="footer">' . implode('<br>', array_map($e, $pr['footer'])) . '</div>' : '';
        $cust = $e($d['customer']);
        $tl = $e(self::tanggal_id($d['today']));
        $nomor = $e($d['nomor']);
        $kota = $e($pr['kota']);
        $ttd = $e($pr['ttd']);
        $jab = $e($pr['jabatan']);
        $pt = $e($pr['pt']);
        $title = $e('Surat Konfirmasi Tagihan ' . $d['invoice']);
        return <<<HTML
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>$title</title><style>
@page{size:A4;margin:12mm 25mm 12mm 27mm}
body{font-family:Verdana,Tahoma,Arial,sans-serif;font-size:10.5pt;color:#000;margin:0;background:#e9ecef}
.page{width:210mm;min-height:297mm;padding:12mm 25mm 30mm 27mm;box-sizing:border-box;margin:12px auto;background:#fff;position:relative;box-shadow:0 1px 6px rgba(0,0,0,.15)}
.kop-stm{display:flex;align-items:center;gap:10px}.kop-stm img{flex-shrink:0}.kop-stm .logo{height:42px}.kop-stm .sert{height:54px}
.kop-stm .nama{flex:1;min-width:0;height:50px;object-fit:contain}
.kop-tag{text-align:center;font-family:Arial,sans-serif;font-weight:700;font-size:7pt;margin:4px 0 3px}
.kop-line{height:3px;background:#f2b705}.kop-line2{height:6px;background:#1f3f99;margin-bottom:8px}
.kop-default{text-align:center;font-weight:700;font-size:16pt;border-bottom:2px solid #000;padding-bottom:6px;margin-bottom:10px}
.meta td{padding:0;vertical-align:top}.meta td:first-child{width:32px}
p{margin:0 0 12px;line-height:1.3}.just{text-align:justify}
.data{margin:0 0 12px 52px;border-collapse:collapse}.data td{padding:0 0 1px;vertical-align:top}.data td:first-child{width:130px}
.ttd{margin-top:44px}.ttd .ruang{height:62px}.ttd .nm{text-decoration:underline}
.footer{position:absolute;left:25mm;right:25mm;bottom:10mm;text-align:center;font-family:"Times New Roman",serif;font-size:7.5pt;line-height:1.25}
.toolbar{position:sticky;top:0;background:#fff;border-bottom:1px solid #ccd;padding:8px 14px;font-family:Arial,sans-serif;font-size:12px;display:flex;flex-wrap:wrap;gap:8px 14px;align-items:flex-end}
.toolbar label{display:flex;flex-direction:column;gap:2px;color:#555}.toolbar input{font-size:12px;padding:4px 6px;border:1px solid #bbb;border-radius:3px;min-width:150px}
.toolbar button{font-size:13px;padding:6px 16px;background:#3c8dbc;color:#fff;border:0;border-radius:3px;cursor:pointer}.toolbar .note{flex-basis:100%;color:#8a6d3b}
@media print{body{background:#fff}.toolbar{display:none}.page{margin:0;box-shadow:none;width:auto;min-height:0;padding:0 0 22mm;position:static}.footer{position:fixed;left:0;right:0;bottom:0}*{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style></head><body>
<div class="toolbar">
<label>No. surat<input data-bind="nomor" value="$nomor"></label><label>Ditujukan ke<input data-bind="tujuan" value="Finance Dept."></label>
<label>Kota<input data-bind="kota" value="$kota"></label><label>Penandatangan<input data-bind="ttd" value="$ttd"></label><label>Jabatan<input data-bind="jabatan" value="$jab"></label>
<button type="button" onclick="window.print()">Print</button>
<span class="note">Isian di atas hanya untuk surat ini (tidak disimpan) dan tidak ikut tercetak. Lengkapi nomor urut surat sebelum print.</span>
</div>
<div class="page">$kop
<table class="meta"><tr><td colspan="2"><span data-text="kota">$kota</span>, $tl</td></tr><tr><td>No.</td><td>: <span data-text="nomor">$nomor</span></td></tr><tr><td>Hal</td><td>: Konfirmasi Status Tagihan</td></tr></table><br>
<p>Kepada yth:<br><b data-text="tujuan">Finance Dept.</b><br><b style="font-size:11.5pt">$cust</b></p>
<p class="just">Dengan Hormat,<br>Sehubungan dengan adanya {$e($d['tagihan'])}, maka bersama ini kami bermaksud menanyakan status pembayaran yang sampai sekarang belum dipenuhi oleh $cust.</p>
<p>Adapun datanya sebagai berikut :</p>
<table class="data"><tr><td>Invoice</td><td>: {$e($d['invoice'])}</td></tr><tr><td>Tanggal Invoice</td><td>: {$e(self::tanggal_id($d['tgl_invoice']))}</td></tr><tr><td>Nilai</td><td>: {$e(self::rp($d['nilai']))}</td></tr>$paid</table>
<p>Mengingat :</p>
<p class="just">Penagihan <b>sudah {$e($d['lama'])}</b> dan hingga saat ini belum ada kepastian kapan tagihan tersebut akan dibayarkan.</p>
<p class="just">Maka kami mohon bantuan Bapak/Ibu untuk dapat memberikan informasi kepada kami mengenai status kekurangan bayar tersebut dengan tertulis, dan bila sudah melakukan pembayaran, maka surat ini diabaikan.</p>
<p class="just">Demikianlah surat ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
<div class="ttd">Hormat kami,<br>$pt<div class="ruang"></div><span class="nm" data-text="ttd">$ttd</span><br><span data-text="jabatan">$jab</span></div>
$footer
</div>
<script>document.querySelectorAll('[data-bind]').forEach(function(i){i.addEventListener('input',function(){document.querySelectorAll('[data-text="'+i.dataset.bind+'"]').forEach(function(el){el.textContent=i.value;});});});</script>
</body></html>
HTML;
    }
}
