<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Aturan perubahan Budget Pembayaran Periodik (mDiff, mSave, mSum di specs/input/Periodik.html), tanpa
 * ketergantungan CodeIgniter. Kolom master ms_budget_rutin: coa, nama, tipe (bulan|tahun), tanggal
 * (hari 1-31 untuk bulan, tanggal penuh "MM/DD/YYYY" untuk tahun), nilai, masa_berlaku (0 = Selalu), masa_awal, aktif.
 */
class Budget_logic
{
	const FIELDS = array('coa', 'nama', 'tipe', 'tanggal', 'nilai', 'masa_berlaku', 'masa_awal', 'aktif');
	const LABELS = array(
		'coa' => 'Post pengeluaran', 'nama' => 'Nama barang / jasa', 'tipe' => 'Bulanan / Tahunan', 'tanggal' => 'Jadwal pembayaran',
		'nilai' => 'Perkiraan biaya', 'masa_berlaku' => 'Masa berlaku', 'masa_awal' => 'Sudah dibayar sebelumnya', 'aktif' => 'Status',
	);
	const KINDS = array('new' => array('P', 'Item baru'), 'edit' => array('W', 'Ubah'), 'off' => array('R', 'Nonaktifkan'), 'on' => array('A', 'Aktifkan'));

	/** Bentuk baku satu baris supaya perbandingan master dan draf tidak terkecoh oleh tipe data. */
	public static function normalize(array $r)
	{
		return array(
			'id'           => isset($r['id']) && $r['id'] !== '' && $r['id'] !== null ? (int) $r['id'] : null,
			'coa'          => trim((string) ($r['coa'] ?? '')),
			'nama'         => trim((string) ($r['nama'] ?? '')),
			'tipe'         => (($r['tipe'] ?? 'bulan') === 'tahun') ? 'tahun' : 'bulan',
			'tanggal'      => trim((string) ($r['tanggal'] ?? '')),
			'nilai'        => (float) ($r['nilai'] ?? 0),
			'masa_berlaku' => max(0, (int) ($r['masa_berlaku'] ?? 0)),
			'masa_awal'    => max(0, (int) ($r['masa_awal'] ?? 0)),
			'aktif'        => !isset($r['aktif']) || (int) $r['aktif'] ? 1 : 0,
		);
	}

	/** Baris baru yang belum diisi tidak dihitung sebagai perubahan. */
	public static function is_blank_new(array $r)
	{
		$n = self::normalize($r);
		return $n['id'] === null && $n['nama'] === '' && $n['nilai'] <= 0;
	}

	/** Pembayaran yang sudah terpakai: sebelum sistem + disetujui di sistem. */
	public static function used(array $r, $inSys)
	{
		$n = self::normalize($r);
		return $n['masa_awal'] + ($n['id'] === null ? 0 : (int) $inSys);
	}

	/** Item masih berlaku: aktif dan masa berlakunya belum habis. */
	public static function is_active(array $r, $inSys)
	{
		$n = self::normalize($r);
		return $n['aktif'] === 1 && !($n['masa_berlaku'] > 0 && self::used($r, $inSys) >= $n['masa_berlaku']);
	}

	/** mSum: jumlah item, item berlaku, total per bulan dan per tahun (hanya yang berlaku). */
	public static function summary(array $rows, array $inSysById)
	{
		$out = array('n' => count($rows), 'act' => 0, 'bln' => 0.0, 'thn' => 0.0);
		foreach ($rows as $r) {
			$n = self::normalize($r);
			if (!self::is_active($r, $inSysById[$n['id']] ?? 0)) {
				continue;
			}
			$out['act']++;
			$out[$n['tipe'] === 'tahun' ? 'thn' : 'bln'] += $n['nilai'];
		}
		return $out;
	}

	private static function validSchedule($tipe, $tanggal)
	{
		if ($tipe === 'tahun') {
			return preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $tanggal, $m) === 1 && checkdate((int) $m[1], (int) $m[2], (int) $m[3]);
		}
		return ctype_digit($tanggal) && (int) $tanggal >= 1 && (int) $tanggal <= 31;
	}

	/**
	 * Validasi draf seperti mSave. Mengembalikan pesan galat pertama, atau null bila lolos.
	 * $inSysById: id master => jumlah pembayaran disetujui di sistem.
	 */
	public static function validate(array $draft, array $inSysById, array $master = array())
	{
		$old = array();
		foreach ($master as $m) {
			$n = self::normalize($m);
			$old[$n['id']] = $n;
		}
		$rows = array_values(array_filter($draft, function ($r) { return !self::is_blank_new($r); }));
		if (!$rows) {
			return 'Isi minimal satu item budget sebelum mengajukan.';
		}
		foreach ($rows as $r) {
			$n = self::normalize($r);
			if ($n['nama'] === '') {
				return 'Nama barang / jasa wajib diisi.';
			}
			if (!($n['nilai'] > 0)) {
				return 'Perkiraan biaya "' . $n['nama'] . '" harus lebih dari 0.';
			}
			$used = self::used($r, $inSysById[$n['id']] ?? 0);
			if ($n['masa_berlaku'] > 0 && $n['masa_berlaku'] < $used) {
				return 'Masa berlaku "' . $n['nama'] . '" tidak boleh kurang dari ' . $used . ' kali.';
			}
			if ($n['coa'] === '') {
				return 'Post pengeluaran "' . $n['nama'] . '" wajib dipilih.';
			}
			// jadwal hanya diperiksa untuk item baru atau yang jadwalnya diubah: data lama yang bentuknya tak lazim tidak menghalangi perubahan lain
			$o = $n['id'] === null ? null : ($old[$n['id']] ?? null);
			$scheduleChanged = $o === null || $o['tipe'] !== $n['tipe'] || $o['tanggal'] !== $n['tanggal'];
			if ($scheduleChanged && !self::validSchedule($n['tipe'], $n['tanggal'])) {
				return 'Jadwal pembayaran "' . $n['nama'] . '" tidak valid.';
			}
		}
		return null;
	}

	/**
	 * mDiff: baris perubahan antara master (daftar baris dari DB) dan draf. Baris master yang tidak ada
	 * di draf dianggap tidak berubah. Return: list of array(kind, id, before, after, fields).
	 */
	public static function diff(array $master, array $draft)
	{
		$byId = array();
		foreach ($master as $m) {
			$n = self::normalize($m);
			$byId[$n['id']] = $n;
		}
		$out = array();
		foreach ($draft as $r) {
			if (self::is_blank_new($r)) {
				continue;
			}
			$a = self::normalize($r);
			if ($a['id'] === null) {
				$out[] = array('kind' => 'new', 'id' => null, 'before' => null, 'after' => $a, 'fields' => array());
				continue;
			}
			if (!isset($byId[$a['id']])) {
				continue; // id tidak dikenal: diabaikan, validasi kepemilikan ada di pemanggil
			}
			$o = $byId[$a['id']];
			$changed = array();
			foreach (self::FIELDS as $k) {
				$same = $k === 'nilai' ? abs($o[$k] - $a[$k]) < 0.005 : $o[$k] === $a[$k];
				if (!$same) {
					$changed[] = $k;
				}
			}
			if ($changed) {
				$kind = (count($changed) === 1 && $changed[0] === 'aktif') ? ($a['aktif'] ? 'on' : 'off') : 'edit';
				$out[] = array('kind' => $kind, 'id' => $a['id'], 'before' => $o, 'after' => $a, 'fields' => $changed);
			}
		}
		return $out;
	}

	/** Nilai yang akan ditulis ke ms_budget_rutin saat satu baris perubahan disetujui. */
	public static function apply_line(array $line, $dept)
	{
		$after = $line['after'];
		if ($line['kind'] === 'new') {
			$row = array_intersect_key($after, array_flip(self::FIELDS));
			$row['departement'] = $dept;
			return array('op' => 'insert', 'data' => $row);
		}
		$set = array();
		foreach ($line['fields'] as $k) {
			if (in_array($k, self::FIELDS, true)) {
				$set[$k] = $after[$k];
			}
		}
		return array('op' => 'update', 'id' => (int) $line['id'], 'data' => $set);
	}

	/** Teks nilai satu kolom untuk tampilan sebelum/sesudah (mfv di mockup). */
	public static function value_text($field, $v)
	{
		switch ($field) {
			case 'nilai':
				return number_format((float) $v, 0, ',', '.');
			case 'masa_berlaku':
				return (int) $v > 0 ? (int) $v . ' kali' : 'Selalu';
			case 'tipe':
				return $v === 'tahun' ? 'Tahunan' : 'Bulanan';
			case 'aktif':
				return (int) $v ? 'Aktif' : 'Nonaktif';
			default:
				return $v === '' || $v === null ? '-' : (string) $v;
		}
	}

	/** Perkiraan bulan berakhir untuk masa terbatas (endOf): "Des 2026 (sisa 3 kali)" atau "Selesai". */
	public static function end_of(array $r, $used, $baseYm)
	{
		$n = self::normalize($r);
		if ($n['masa_berlaku'] <= 0) {
			return null;
		}
		$rem = $n['masa_berlaku'] - (int) $used;
		if ($rem <= 0) {
			return 'Selesai';
		}
		$bulan = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
		$y = (int) substr($baseYm, 0, 4);
		$m = (int) substr($baseYm, 5, 2) - 1;
		if ($n['tipe'] === 'tahun') {
			$y += $rem - 1;
		} else {
			$t = $m + $rem - 1;
			$y += intdiv($t, 12);
			$m = $t % 12;
		}
		return $bulan[$m] . ' ' . $y . ' (sisa ' . $rem . ' kali)';
	}
}
