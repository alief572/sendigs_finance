<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| Helper Pembayaran Periodik per item.
|
| Tiga jenis nomor:
|   BP-YYMM-NNN    nomor batch pengajuan (tr_pengajuan_rutin.no_doc), reset tiap bulan
|   PI-YYYY-NNNNN  nomor item (tr_pengajuan_rutin_detail.no_item), reset tiap tahun
|   BG-YYMM-NNN    nomor perubahan budget (tr_budget_bg.no_bg), reset tiap bulan
| Nomor batch lama berbentuk PERIODIK-YYYY-NNNNN dan detailnya tidak punya no_item.
| Invoice PO memakai PI-YYYY-MM-NNNNNN, bentuk yang berbeda dari nomor item.
*/

if (!function_exists('periodik_is_item_no')) {
	/** Nomor item periodik: PI-YYYY-NNNNN. Invoice PO (PI-YYYY-MM-NNNNNN) tidak cocok. */
	function periodik_is_item_no($no)
	{
		return is_string($no) && preg_match('/^PI-\d{4}-\d{5}$/', $no) === 1;
	}
}

if (!function_exists('periodik_format_number')) {
	/** Susun nomor dari kode, periode, dan urutan. BP/BG: 3 digit, PI: 5 digit. */
	function periodik_format_number($kode, $periode, $urut)
	{
		$kode = strtoupper((string) $kode);
		if (!in_array($kode, array('BP', 'BG', 'PI'), true)) {
			throw new InvalidArgumentException('Kode penomoran periodik tidak dikenal: ' . $kode);
		}
		return sprintf('%s-%s-%0' . ($kode === 'PI' ? 5 : 3) . 'd', $kode, $periode, (int) $urut);
	}
}

if (!function_exists('periodik_number_period')) {
	/** Periode counter: YYYY untuk PI, YYMM untuk BP/BG. */
	function periodik_number_period($kode, $timestamp = null)
	{
		$timestamp = $timestamp === null ? time() : $timestamp;
		return strtoupper((string) $kode) === 'PI' ? date('Y', $timestamp) : date('ym', $timestamp);
	}
}

if (!function_exists('periodik_next_number')) {
	/**
	 * Ambil nomor berikutnya secara atomik.
	 *
	 * Wajib dipanggil di dalam transaksi pemanggil (trans_begin/trans_start): baris counter
	 * terkunci oleh UPDATE sampai commit, sehingga dua penyimpanan bersamaan tidak mendapat
	 * nomor sama dan nomor tidak terpakai ikut batal bila transaksi di-rollback.
	 *
	 * @param object $db  koneksi CI (CI_DB) atau objek dengan query() yang setara
	 * @return string     nomor lengkap, atau '' bila counter tidak bisa dibaca
	 */
	function periodik_next_number($db, $kode, $timestamp = null)
	{
		$kode    = strtoupper((string) $kode);
		$periode = periodik_number_period($kode, $timestamp);

		$db->query('INSERT IGNORE INTO ms_periodik_counter (kode, periode, last_no) VALUES (?, ?, 0)', array($kode, $periode));
		$db->query('UPDATE ms_periodik_counter SET last_no = last_no + 1 WHERE kode = ? AND periode = ?', array($kode, $periode));
		$row = $db->query('SELECT last_no FROM ms_periodik_counter WHERE kode = ? AND periode = ?', array($kode, $periode))->row();

		if (!$row || (int) $row->last_no < 1) {
			return '';
		}
		return periodik_format_number($kode, $periode, $row->last_no);
	}
}

if (!function_exists('periodik_schedule_text')) {
	/**
	 * Jadwal pembayaran bacaan manusia dari ms_budget_rutin.tanggal.
	 * Bulanan: hari 1-31 -> "Tgl 25". Tahunan: tanggal penuh MM/DD/YYYY -> "15 Mar 2026" (format yang tersimpan di data).
	 * Nilai di luar format (data lama) ditampilkan apa adanya; kosong menjadi "-".
	 */
	function periodik_schedule_text($tipe, $tanggal)
	{
		$t = trim((string) $tanggal);
		if ($t === '') {
			return '-';
		}
		$bulan = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
		if ($tipe === 'tahun') {
			return preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $t, $m) === 1 && checkdate((int) $m[1], (int) $m[2], (int) $m[3])
				? (int) $m[2] . ' ' . $bulan[(int) $m[1] - 1] . ' ' . $m[3] : $t;
		}
		return ctype_digit($t) ? 'Tgl ' . (int) $t : $t;
	}
}

if (!function_exists('periodik_doc_key')) {
	/**
	 * Kunci pencarian tr_pengajuan_rutin_detail untuk sebuah no_dokumen.
	 * Nomor item PI-... mencari lewat no_item; nomor batch lama (PERIODIK-...) lewat no_doc.
	 *
	 * @return array ['col' => 'no_item'|'no_doc', 'val' => string]
	 */
	function periodik_doc_key($no_dokumen)
	{
		return periodik_is_item_no($no_dokumen)
			? array('col' => 'no_item', 'val' => $no_dokumen)
			: array('col' => 'no_doc', 'val' => $no_dokumen);
	}
}

if (!function_exists('periodik_batch_no')) {
	/** Nomor batch (tr_pengajuan_rutin.no_doc) untuk sebuah no_dokumen; '' bila tidak ditemukan. */
	function periodik_batch_no($db, $no_dokumen)
	{
		if (!periodik_is_item_no($no_dokumen)) {
			return (string) $no_dokumen;
		}
		$row = $db->select('no_doc')->get_where('tr_pengajuan_rutin_detail', array('no_item' => $no_dokumen))->row();
		return $row ? (string) $row->no_doc : '';
	}
}

if (!function_exists('periodik_batch_status')) {
	/**
	 * Status batch dari status item: W (Waiting Approval), A (Approved), R (Rejected),
	 * P (Partially Approved). Parameter: daftar item_status (0 menunggu, 1/2/9 sudah disetujui
	 * approver, 8 ditolak approver).
	 */
	function periodik_batch_status(array $itemStatuses)
	{
		if (!$itemStatuses) {
			return 'W';
		}
		$groups = array();
		foreach ($itemStatuses as $s) {
			$s = (int) $s;
			$groups[$s === 0 ? 'W' : ($s === 8 ? 'R' : 'A')] = true;
		}
		return count($groups) === 1 ? key($groups) : 'P';
	}
}

if (!function_exists('periodik_doc_url')) {
	/**
	 * URL dokumen lampiran pengajuan periodik. Selalu lewat Pengajuan_rutin::download_doc (cek login dan permission),
	 * jangan menautkan assets/bayar_rutin/ langsung.
	 *
	 * @param string $file     nilai tr_pengajuan_rutin_detail.doc_file
	 * @param bool   $download true memaksa unduh (attachment), false ditampilkan di browser (inline)
	 */
	function periodik_doc_url($file, $download = false)
	{
		return base_url('pengajuan_rutin/download_doc/' . rawurlencode((string) $file)) . ($download ? '?dl=1' : '');
	}
}
