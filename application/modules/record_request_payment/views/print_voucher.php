<?php
// Bukti Bank Keluar format specs/input/Voucher STM - Copy.xlsx sheet "BBK (2)" (RP-R-37/39).
$isStm = (string) $group['company_id'] === '7';
// shortcut: nama TTD STM dari contoh voucher; pindahkan ke config bila TTD berganti atau company lain punya TTD tetap.
$signers = $isStm ? ['Tati Rina', 'Fikri Darmawan A'] : ['(diisi user)', '(diisi user)'];
$months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$date = new DateTimeImmutable($group['tgl_bayar']);
$money = static function ($value) { return (float) $value ? number_format((float) $value, 2, ',', '.') : ''; };
$debit = array_sum(array_map('floatval', array_column($journal, 'debit')));
$credit = array_sum(array_map('floatval', array_column($journal, 'kredit')));
$rows = array_pad($journal, max(7, count($journal)), null);
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title>Voucher <?= html_escape($voucherNo) ?></title>
	<style>
		@page{size:A4 portrait;margin:12mm 10mm}*{box-sizing:border-box}
		body{font-family:Calibri,Carlito,Arial,sans-serif;font-size:11pt;color:#000;margin:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}
		.wrap{width:190mm;margin:0 auto}
		.hd{display:flex;justify-content:space-between;align-items:center;border-top:2px solid #000;border-bottom:2px solid #000;padding:2mm 0}
		/* Tanpa 'Segoe Print': bila Maiandra GD tidak tersedia (mis. diblokir proteksi font browser), jangan jadi tulisan tangan. */
		.ttl{font-family:'Maiandra GD',Candara,sans-serif;font-size:24pt}.hd img{height:12mm}.hd .co{font-weight:700;font-size:14pt}
		.meta{display:flex;justify-content:space-between;align-items:flex-end;margin:1mm 0 3mm}.meta td{padding:0 2mm 0 0}
		table.v{width:100%;border-collapse:collapse;border:2px solid #000}
		table.v th{font-weight:400;border:1px solid #000;border-bottom:2px solid #000;padding:1mm}
		table.v td{border-left:1px solid #000;border-right:1px solid #000;border-bottom:.5px solid #555;height:13mm;padding:.5mm 1mm;font-size:9pt;vertical-align:middle}
		.c{text-align:center}.r{text-align:right;white-space:nowrap}
		table.vt{width:100%;border-collapse:collapse;margin-top:1mm}table.vt td{padding:.5mm 1mm}
		table.vt td.tl{text-align:right;padding-right:4mm}table.vt td.tl,table.vt td.r{border-bottom:1.5px solid #000}table.vt b{font-size:14pt}
		.sig{display:flex;justify-content:flex-end;margin-top:8mm;font-family:'Maiandra GD',Candara,sans-serif}
		.sig div{width:62mm;text-align:center}.sig .nm{margin-top:22mm;min-height:6mm;border-bottom:1px solid transparent}
		.note{font-size:8pt;color:#666;margin-top:10mm}
		@media screen{body{background:#eee}.wrap{background:#fff;padding:10mm;margin:10mm auto;box-shadow:0 0 6px #aaa}}
	</style>
</head>
<body>
<div class="wrap">
	<div class="hd"><span class="ttl">BUKTI BANK KELUAR</span>
		<?php if ($isStm): ?><img src="<?= base_url('assets/images/logo_stm_voucher.png') ?>" alt="Logo <?= html_escape($group['company_nama']) ?>">
		<?php else: ?><span class="co"><?= html_escape($group['company_nama']) ?></span><?php endif; ?>
	</div>
	<div class="meta">
		<table><tr><td>Keterangan</td><td>: Pengeluaran</td></tr></table>
		<table><tr><td>No</td><td>:</td><td><?= html_escape($voucherNo) ?></td></tr>
			<tr><td>Tanggal</td><td>:</td><td><?= $date->format('j') . ' ' . $months[(int) $date->format('n') - 1] . ' ' . $date->format('Y') ?></td></tr></table>
	</div>
	<table class="v">
		<colgroup><col style="width:24mm"><col style="width:30mm"><col><col style="width:30mm"><col style="width:30mm"></colgroup>
		<thead><tr><th>No. Account</th><th>Nama Account</th><th>Keterangan</th><th>Debit</th><th>Kredit</th></tr></thead>
		<tbody>
			<?php foreach ($rows as $row): ?>
				<?php if ($row): ?>
					<tr><td class="c"><?= html_escape($row['coa']) ?></td><td><?= html_escape($row['nm_coa']) ?></td><td><?= html_escape($row['keterangan']) ?></td>
						<td class="r"><?= $money($row['debit']) ?></td><td class="r"><?= $money($row['kredit']) ?></td></tr>
				<?php else: ?>
					<tr><td></td><td></td><td></td><td></td><td></td></tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<table class="vt">
		<colgroup><col style="width:24mm"><col style="width:30mm"><col><col style="width:30mm"><col style="width:30mm"></colgroup>
		<tr><td></td><td></td><td class="tl"><b>Total</b> <b>:</b></td><td class="r"><?= number_format($debit, 2, ',', '.') ?></td><td class="r"><?= number_format($credit, 2, ',', '.') ?></td></tr>
	</table>
	<div class="sig">
		<div>Disetujui<div class="nm"><?= html_escape($signers[0]) ?></div></div>
		<div>Kasir<div class="nm"><?= html_escape($signers[1]) ?></div></div>
	</div>
	<div class="note">Payment <?= html_escape($group['id_payment']) ?> &middot; <?= html_escape($group['no_pengajuan']) ?> / Transfer <?= (int) $group['urutan'] ?>
		&middot; ke <?= html_escape($group['bank'] . ' ' . $group['no_rek'] . ' a/n ' . $group['nama']) ?></div>
</div>
<script>window.onload = () => setTimeout(() => window.print(), 300);</script>
</body>
</html>
