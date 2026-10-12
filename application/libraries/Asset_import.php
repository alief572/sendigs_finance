<?php
/** Pure import rules, shared by preview and confirmation. No database writes. */
class Asset_import
{
    const HEADERS = ['Nama Asset *', 'Kategori *', 'Company *', 'Department', 'Tanggal Perolehan *', 'Qty', 'Nilai Perolehan per Unit (Rp) *', 'Masa Manfaat (Tahun) *'];

    public function masters(array $groups)
    {
        $result = [];
        foreach ($groups as $key => $items) {
            foreach ($items as &$item) { $item['name']=trim($item['name']); }
            unset($item);
            $counts = array_count_values(array_column($items, 'name'));
            foreach ($items as $item) {
                $label = trim($item['name']);
                if ($counts[$item['name']] > 1) { $label .= ' [ID ' . $item['id'] . ']'; }
                if (isset($result[$key][$label])) { throw new RuntimeException('Label master ambigu. Hubungi administrator.'); }
                $result[$key][$label] = ['id' => (string)$item['id'], 'name' => $item['name']];
            }
            if (!isset($result[$key])) { $result[$key] = []; }
        }
        return $result;
    }

    public function validate(array $raw, array $masters)
    {
        $errors = []; $rows = []; $units = 0; $total = 0; $count = 0;
        foreach ($raw as $line => $cells) {
            $cells = array_pad($cells, 8, '');
            if (count(array_filter($cells, function ($v) { return trim((string)$v) !== ''; })) === 0) { continue; }
            $count++;
            $before = count($errors); $row = ['line' => (int)$line];
            $name = trim((string)$cells[0]);
            if ($name === '' || strlen($name) > 255 || preg_match('/[\x00-\x1f]/', $name)) {
                $this->error($errors, $line, 0, 'Isi nama asset (maksimal 255 byte, tanpa karakter kontrol).');
            }
            $row['nm_asset'] = $name;
            foreach ([1 => 'category', 2 => 'company', 3 => 'department'] as $col => $key) {
                $label = trim((string)$cells[$col]);
                if ($label === '' && $col >= 3) { $row[$key] = ''; $row[$key . '_name'] = ''; continue; }
                if ($label === '' || !isset($masters[$key][$label])) { $this->error($errors, $line, $col, 'Pilih nama dari dropdown master yang masih tersedia; download ulang format bila master berubah.'); }
                $row[$key] = isset($masters[$key][$label]) ? $masters[$key][$label]['id'] : '';
                $row[$key . '_name'] = $label;
            }
            if ($row['category'] !== '' && ((int)$row['category']<1 || (int)$row['category']>99)) { $this->error($errors,$line,1,'Kategori di luar rentang kode asset existing (ID 1–99). Hubungi administrator.'); }
            $date = $this->date($cells[4]);
            if (!$date) { $this->error($errors, $line, 4, 'Gunakan tanggal Excel, dd/mm/yyyy atau yyyy-mm-dd (tahun 1900–9999).'); }
            $row['date'] = $date;
            foreach ([5 => ['qty', 99], 6 => ['amount', 999999999999], 7 => ['years', 20]] as $col => $rule) {
                $value = trim((string)$cells[$col]);
                if ($col === 5 && $value === '') { $value = '1'; }
                if (!is_numeric($value) || !is_finite((float)$value) || (float)$value != floor((float)$value) || (float)$value < 1 || (float)$value > $rule[1]) {
                    $this->error($errors, $line, $col, 'Isi angka bulat positif 1–' . $rule[1] . ', tanpa Rp atau pemisah ribuan.');
                    $value = 0;
                }
                $row[$rule[0]] = (int)$value;
            }
            // Excel formula cells are rejected by the reader; reject formula-like text too.
            foreach ($cells as $col => $value) {
                if (preg_match('/^\s*=/', (string)$value)) { $this->error($errors, $line, min($col, 7), 'Formula tidak diperbolehkan. Isi nilai langsung.'); }
            }
            $units += $row['qty']; $total += $row['amount'] * $row['qty'];
            $row['monthly'] = $row['years'] ? (int)round($row['amount'] / ($row['years'] * 12), 0, PHP_ROUND_HALF_UP) : 0;
            $row['valid'] = count($errors) === $before;
            $rows[] = $row;
        }
        if ($count === 0) { $errors[] = ['baris' => 0, 'kolom' => 'File', 'pesan' => 'Tidak ada data asset untuk diimpor.']; }
        if ($count > 500 || $units > 1000) { $errors[] = ['baris' => 0, 'kolom' => 'File', 'pesan' => 'Maksimal 500 baris data dan 1.000 unit asset per upload.']; }
        return ['rows' => $rows, 'errors' => $errors, 'summary' => ['baris' => $count, 'unit' => $units, 'total' => $total]];
    }

    public function revalidate(array $rows, array $masters)
    {
        $errors = [];
        foreach ($rows as $row) {
            foreach ([1 => 'category', 2 => 'company', 3 => 'department'] as $col => $key) {
                if ($row[$key] === '' && $col >= 3) { continue; }
                $label = $row[$key . '_name'];
                if (!isset($masters[$key][$label]) || $masters[$key][$label]['id'] !== $row[$key]) {
                    $this->error($errors, $row['line'], $col, 'Master berubah setelah preview. Download format dan upload ulang.');
                }
            }
        }
        return $errors;
    }

    private function date($value)
    {
        if (is_numeric($value)) {
            if ((float)$value != floor((float)$value) || $value < 1 || $value > 2958465 || (int)$value === 60) { return null; }
            // Excel's fictional 29 February 1900 is not a valid date.
            return (new DateTimeImmutable('1899-12-31'))->modify('+' . ((int)$value - ($value > 60 ? 1 : 0)) . ' days')->format('Y-m-d');
        }
        foreach (['!d/m/Y', '!Y-m-d'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, trim((string)$value));
            $err = DateTimeImmutable::getLastErrors();
            if ($date && (!$err || (!$err['warning_count'] && !$err['error_count'])) && $date->format(substr($format, 1)) === trim((string)$value) && (int)$date->format('Y') >= 1900) { return $date->format('Y-m-d'); }
        }
        return null;
    }

    private function error(&$errors, $line, $col, $message)
    {
        $errors[] = ['baris' => (int)$line, 'kolom' => self::HEADERS[$col], 'pesan' => $message];
    }
}
