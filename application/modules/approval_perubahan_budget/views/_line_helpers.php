<?php
/* HTML sebelum/sesudah satu baris perubahan budget, dipakai modal Lihat dan form keputusan. Semua nilai di-escape. */
if (!function_exists('apb_line_html')) {
	/** $side = 'before' | 'after'. Item baru: nama + ringkasan; selain itu: hanya field yang berubah, sebelum merah / sesudah hijau. */
	function apb_line_html(array $l, $side)
	{
		$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
		$text = function ($field, array $row) {
			return $field === 'tanggal' ? periodik_schedule_text($row['tipe'], $row['tanggal']) : Budget_logic::value_text($field, $row[$field]);
		};
		$chip = function ($label, $value, $cls) use ($e) {
			return '<div class="f"><small>' . $e($label) . '</small><span class="v ' . $cls . '">' . $e($value) . '</span></div>';
		};
		if ($l['kind'] === 'new') {
			if ($side === 'before') {
				return '<span class="v none">-</span>';
			}
			$a = $l['after'];
			return '<b>' . $e($a['nama']) . '</b><div class="diff">'
				. $chip('Tipe', Budget_logic::value_text('tipe', $a['tipe']), 'new') . $chip('Jadwal', $text('tanggal', $a), 'new')
				. $chip('Biaya', Budget_logic::value_text('nilai', $a['nilai']), 'new') . $chip('Masa berlaku', Budget_logic::value_text('masa_berlaku', $a['masa_berlaku']), 'new') . '</div>';
		}
		$out = '';
		foreach ($l['fields'] as $f) {
			$out .= $chip(Budget_logic::LABELS[$f], $text($f, $l[$side]), $side === 'before' ? 'old' : 'new');
		}
		return '<div class="diff">' . $out . '</div>';
	}
}
