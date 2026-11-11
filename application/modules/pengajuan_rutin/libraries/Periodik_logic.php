<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Aturan bisnis Pembayaran Periodik per item, tanpa ketergantungan CodeIgniter supaya bisa diuji.
 * Padanan fungsi mockup specs/input/Periodik.html: usage, mState, bStat, itemSteps.
 *
 * Bentuk item (array): no_item, no_batch, id_budget, status ('W' menunggu, 'A' disetujui,
 * 'R' ditolak), nominal, ket, note (alasan reject), ref (revisi dari), resub (diajukan ulang sebagai),
 * closed_note, closed_at, by, at (waktu keputusan), periode (YYYY-MM batch), tgl_batch.
 */
class Periodik_logic
{
	const BULAN = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');

	/** item_status database -> huruf status mockup. 0 menunggu, 8 ditolak approver, selain itu disetujui. */
	public static function letter($itemStatus)
	{
		$s = (int) $itemStatus;
		return $s === 0 ? 'W' : ($s === 8 ? 'R' : 'A');
	}

	public static function batch_status(array $letters)
	{
		$u = array_values(array_unique($letters));
		if (count($u) === 1) {
			$map = array('W' => 'Waiting Approval', 'A' => 'Approved', 'R' => 'Rejected');
			return array($u[0], $map[$u[0]]);
		}
		return array('P', 'Partially Approved');
	}

	/** Kunci periode: tahun untuk master Tahunan, YYYY-MM untuk Bulanan. */
	public static function period_key($tipe, $periode)
	{
		return $tipe === 'tahun' ? substr((string) $periode, 0, 4) : (string) $periode;
	}

	/**
	 * Pengajuan terakhir item budget ini (item terakhir di $items, urut id naik, semua periode) bila ditolak, selain itu null.
	 * Pengajuan yang lebih baru (menunggu, disetujui, atau pengajuan ulang) menggantikan penolakan lama.
	 */
	public static function last_reject(array $items)
	{
		$last = end($items);
		return $last && $last['status'] === 'R' ? $last : null;
	}

	/** used = sudah dibayar sebelum sistem + item disetujui; pend = item menunggu approval. */
	public static function usage(array $master, array $items)
	{
		$used = (int) ($master['masa_awal'] ?? 0);
		$pend = array();
		foreach ($items as $i) {
			if ($i['status'] === 'A') {
				$used++;
			} elseif ($i['status'] === 'W') {
				$pend[] = $i;
			}
		}
		return array('used' => $used, 'pend' => $pend);
	}

	/**
	 * Status baris budget pada form untuk satu periode.
	 * off (nonaktif), done (masa habis), lock (sudah diajukan di periode itu atau pembayaran terakhir
	 * menunggu), back (ditolak, boleh diajukan ulang), closed (ditolak lalu ditutup, tersembunyi), free.
	 * $items = semua item master ini di batch lain (batch yang sedang diedit sudah dikeluarkan).
	 */
	public static function state(array $master, array $items, $periode)
	{
		$u    = self::usage($master, $items);
		$masa = (int) ($master['masa_berlaku'] ?? 0);
		$ke   = $u['used'] + count($u['pend']) + 1;
		$out  = array('state' => 'free', 'used' => $u['used'], 'pend' => $u['pend'], 'ke' => $ke, 'masa' => $masa);

		if (isset($master['aktif']) && !(int) $master['aktif']) {
			$out['state'] = 'off';
			return $out;
		}
		if ($masa > 0 && $u['used'] >= $masa) {
			$out['state'] = 'done';
			return $out;
		}
		$key  = self::period_key($master['tipe'], $periode);
		$same = array();
		foreach ($items as $i) {
			if (self::period_key($master['tipe'], $i['periode']) === $key) {
				$same[] = $i;
			}
		}
		foreach ($same as $i) {
			if ($i['status'] !== 'R') {
				$out['state'] = 'lock';
				$out['lock'] = $i;
				return $out;
			}
		}
		if ($masa > 0 && $u['used'] + count($u['pend']) >= $masa) {
			$out['state'] = 'lock';
			$out['lock'] = $u['pend'][0] ?? null;
			$out['last'] = true;
			return $out;
		}
		// item ditolak terakhir di periode ini yang belum diajukan ulang
		$open = null;
		foreach ($same as $i) {
			if (empty($i['resub'])) {
				$open = $i;
			}
		}
		if ($open) {
			$out['state'] = !empty($open['closed_at']) ? 'closed' : 'back';
			$out['back'] = $open;
		}
		return $out;
	}

	/** Label masa berlaku terbatas di form: ke-x dari n, "(terakhir)" bila pembayaran penutup, atau menunggu approval. */
	public static function masa_tag(array $st)
	{
		if ($st['masa'] <= 0) {
			return '';
		}
		if (!empty($st['last'])) {
			return 'Pembayaran terakhir sedang menunggu approval';
		}
		if (in_array($st['state'], array('free', 'back'), true)) {
			return 'Pembayaran ke-' . $st['ke'] . ' dari ' . $st['masa'] . ($st['ke'] === $st['masa'] ? ' (terakhir)' : '');
		}
		return '';
	}

	/** Awalan keterangan item yang disimpan: "Pembayaran ke-x dari n" (tanpa "(terakhir)"), kosong untuk masa Selalu. */
	public static function ket_prefix(array $st)
	{
		return $st['masa'] > 0 && in_array($st['state'], array('free', 'back'), true) ? 'Pembayaran ke-' . $st['ke'] . ' dari ' . $st['masa'] : '';
	}

	/** Keterangan buatan pengguna tanpa awalan otomatis ket_prefix. */
	public static function user_ket($ket)
	{
		return preg_replace('/^Pembayaran ke-\d+ dari \d+(?: - )?/', '', (string) $ket);
	}

	/** dd Mon yyyy dalam bahasa Indonesia dari 'YYYY-MM-DD...'. */
	public static function fdate($s)
	{
		$s = substr((string) $s, 0, 10);
		if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) {
			return '';
		}
		return $m[3] . ' ' . self::BULAN[(int) $m[2] - 1] . ' ' . $m[1];
	}

	private static function dt($s)
	{
		return $s ? self::fdate($s) . substr((string) $s, 10, 6) : '';
	}

	/**
	 * Status request payment satu item: state 'belum' | 'pending' | 'done' | null (belum pernah),
	 * no (no. pengajuan Finance), reject (alasan reject Finance terakhir).
	 */
	public static function rp_state($rp)
	{
		if (!$rp || empty($rp['state']) || $rp['state'] === 'belum') {
			$sub = !empty($rp['reject']) ? 'Ditolak di ' . ($rp['no'] ?? '') . ': ' . $rp['reject'] : '';
			return array('k' => 'W', 't' => 'Menunggu diajukan Finance', 'sub' => $sub);
		}
		if ($rp['state'] === 'pending') {
			return array('k' => 'W', 't' => 'Menunggu approval', 'sub' => (string) ($rp['no'] ?? ''));
		}
		return array('k' => 'A', 't' => 'Disetujui', 'sub' => (string) ($rp['no'] ?? ''));
	}

	/**
	 * Progres 4 langkah satu item: titik (dots), judul, badge, dan baris meta.
	 * Mengikuti itemSteps di mockup (Periodik.html:1497).
	 */
	public static function item_steps(array $i, $rp = null)
	{
		$st = array(
			array('t' => 'Diajukan', 'c' => 'done', 's' => self::fdate($i['tgl_batch'] ?? '')),
			array('t' => 'Approval pengajuan', 'c' => '', 's' => ''),
			array('t' => 'Diajukan Finance', 'c' => '', 's' => 'Request Payment'),
			array('t' => 'Approval Finance', 'c' => '', 's' => 'Approval Request Payment'),
		);
		$meta = 'Periodik · ' . $i['no_batch'] . ' · diajukan ' . self::fdate($i['tgl_batch'] ?? '');

		if ($i['status'] === 'W') {
			$st[1]['c'] = 'cur';
			$st[1]['s'] = 'Menunggu approval';
			$card = array('t' => 'Approval Pengajuan — Direktur', 'k' => 'cur', 'bd' => 'Menunggu Approval Pengajuan', 'm' => array($meta));
		} elseif ($i['status'] === 'R') {
			$st[1]['c'] = 'rej';
			$st[1]['s'] = 'Ditolak ' . self::fdate($i['at'] ?? '');
			$st[2]['c'] = $st[3]['c'] = 'skip';
			$fate = !empty($i['resub']) ? 'Diajukan ulang sebagai ' . $i['resub']
				: (!empty($i['closed_at']) ? 'Ditutup, tidak diajukan ulang (' . $i['closed_note'] . ')' : 'Belum diajukan ulang');
			$card = array('t' => 'Approval Pengajuan — ' . ($i['by'] ?? ''), 'k' => 'rej', 'bd' => 'Ditolak Approval Pengajuan',
				'm' => array('Alasan: ' . ($i['note'] ?? ''), $fate . ' · ditolak ' . self::dt($i['at'] ?? '')));
		} else {
			$st[1]['c'] = 'done';
			$st[1]['s'] = 'Disetujui ' . self::fdate($i['at'] ?? '');
			$r = self::rp_state($rp);
			if (strpos($r['t'], 'Menunggu diajukan') === 0) {
				$st[2]['c'] = 'cur';
				$st[2]['s'] = 'Menunggu diajukan';
				$m = array();
				if ($r['sub'] !== '') {
					$m[] = preg_replace('/^Ditolak/', 'Ditolak Finance', $r['sub']);
				}
				$m[] = 'Disetujui approver ' . self::dt($i['at'] ?? '');
				$card = array('t' => 'Request Payment — Finance', 'k' => 'cur',
					'bd' => $r['sub'] !== '' ? 'Menunggu Diajukan Ulang Finance' : 'Menunggu Diajukan Finance', 'm' => $m);
			} elseif ($r['k'] === 'W') {
				$st[2]['c'] = 'done';
				$st[2]['s'] = $r['sub'] !== '' ? $r['sub'] : 'Sudah diajukan';
				$st[3]['c'] = 'cur';
				$st[3]['s'] = 'Menunggu approval';
				$card = array('t' => 'Approval Request Payment', 'k' => 'cur', 'bd' => 'Menunggu Approval Request Payment',
					'm' => array('No. pengajuan Finance ' . $r['sub']));
			} else {
				$st[2]['c'] = 'done';
				$st[2]['s'] = $r['sub'] !== '' ? $r['sub'] : 'Sudah diajukan';
				$st[3]['c'] = 'done';
				$st[3]['s'] = 'Disetujui Finance';
				$card = array('t' => 'Approval Request Payment', 'k' => 'done', 'bd' => 'Disetujui Finance',
					'm' => array('No. pengajuan Finance ' . $r['sub']));
			}
		}
		return array('dots' => $st, 'card' => $card);
	}

	/** HTML stepper (kelas .pstp dari mockup). */
	public static function steps_html(array $i, $rp = null)
	{
		$e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
		$r   = self::item_steps($i, $rp);
		$sr  = array('done' => 'selesai', 'cur' => 'posisi sekarang', 'rej' => 'ditolak', 'skip' => 'tidak dilanjutkan');
		$tip = array();
		$done = 0;
		$track = '';
		foreach ($r['dots'] as $ix => $x) {
			$tip[] = ($ix + 1) . '. ' . $x['t'] . ': ' . ($x['s'] !== '' ? $x['s'] : '-') . ' (' . ($sr[$x['c']] ?? 'belum') . ')';
			$done += $x['c'] === 'done' ? 1 : 0;
			$reached = in_array($x['c'], array('done', 'cur', 'rej'), true);
			$track .= ($ix ? '<b class="' . ($reached ? 'on' : '') . '"></b>' : '') . '<i class="' . $e($x['c']) . '"></i>';
		}
		$tip = implode("\n", $tip);
		$meta = '';
		foreach ($r['card']['m'] as $m) {
			$meta .= '<span class="meta">' . $e($m) . '</span>';
		}
		return '<div class="pstp"><div class="track" title="' . $e($tip) . '" aria-hidden="true">' . $track . '</div>'
			. '<span class="sr">Progres ' . $done . ' dari 4 langkah. ' . $e($tip) . '</span>'
			. '<div class="ttl">' . $e($r['card']['t']) . '</div><span class="bdg ' . $e($r['card']['k']) . '">' . $e($r['card']['bd']) . '</span>' . $meta . '</div>';
	}
}
