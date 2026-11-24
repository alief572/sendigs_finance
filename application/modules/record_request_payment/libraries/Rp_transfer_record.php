<?php

require_once dirname(__DIR__, 2).'/pembayaran_material/libraries/Rp_transfer_payment.php';

final class Rp_transfer_record
{
    private const COMPANIES = ['7' => 'STM', '3' => 'Sustain', '4' => 'Vuca'];
    public const CATEGORIES = ['Kasbon', 'Direct Payment', 'Cash', 'Periodik', 'Transport', 'Expense', 'Petty Cash'];
    private const NOTE = 'Catatan: semua angka positif seperti di sistem. DIBAYARKAN = DPP + PPN - PPh per dokumen. ADMIN dihitung 1x per transfer, penanggungnya di kolom ADMIN DITANGGUNG. JUMLAH DIBAYARKAN BANK = uang keluar bank per transfer (= Payment Bank di Form Payment = kredit Bank di jurnal): total DIBAYARKAN dokumen dalam transfer itu, ditambah ADMIN bila ditanggung perusahaan.';

    public static function filters(array $input)
    {
        $filter = [
            'from' => $input['from'] ?? date('Y-m-01'), 'to' => $input['to'] ?? date('Y-m-d'),
            'status' => $input['status'] ?? 'unpaid', 'category' => $input['category'] ?? '',
        ];
        foreach (['from', 'to'] as $key) {
            $value = $filter[$key];
            $date = is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
            if (!$date || $value !== $date->format('Y-m-d')) {
                throw new DomainException('Periode tanggal diputuskan wajib diisi.');
            }
        }
        if ($filter['from'] > $filter['to'] || !in_array($filter['status'], ['', 'paid', 'unpaid'], true) ||
            !in_array($filter['category'], array_merge([''], self::CATEGORIES), true)) {
            throw new DomainException('Filter pengajuan tidak valid.');
        }
        return $filter;
    }

    public static function groups($db, $batch = null, array $filter = [])
    {
        if (!Rp_transfer::installed($db)) {
            return [];
        }
        $sql = "SELECT t.*, h.company_id, h.company_nama, h.no_pengajuan, h.decided_at,
            d.id AS detail_id, d.no_dokumen, d.kategori, d.request_by, d.keperluan, d.keterangan_finance, d.dpp,
            d.flag_pph23, d.flag_pph21, d.nilai_ppn, d.nilai_pph, d.dibayarkan,
            EXISTS(SELECT 1 FROM tr_rp_transfer_file f WHERE f.id_detail=d.id) AS has_files,
            paid.bank_charge, pa.tgl_bayar, pa.payment_bank, pa.keterangan_pembayaran
            FROM tr_rp_transfer t JOIN tr_rp_pengajuan_h h ON h.id=t.id_pengajuan
            JOIN tr_rp_pengajuan_d d ON d.id_transfer=t.id AND d.decision='approved'
            LEFT JOIN tr_payment_paid paid ON BINARY paid.id=BINARY t.id_payment
            LEFT JOIN payment_approve pa ON BINARY pa.id=BINARY d.payment_approval_id
            WHERE h.status='done' AND h.deleted=0 AND t.status IN ('approved','paid')";
        $values = [];
        if ($batch !== null) {
            $sql .= ' AND h.id=?';
            $values[] = (int) $batch;
        }
        if (isset($filter['from'], $filter['to'])) {
            $sql .= ' AND h.decided_at>=? AND h.decided_at<DATE_ADD(?, INTERVAL 1 DAY)';
            array_push($values, $filter['from'], $filter['to']);
        }
        if (($filter['status'] ?? '') !== '') {
            $sql .= ' AND t.status=?';
            $values[] = $filter['status'] === 'paid' ? 'paid' : 'approved';
        }
        $sql .= " ORDER BY FIELD(h.company_id, '7','3','4'), h.no_pengajuan, t.urutan, d.id";
        $rows = Rp_transfer::write($db->query($sql, $values))->result_array();
        $groups = [];
        foreach ($rows as $row) {
            $key = $row['id'];
            if (!isset($groups[$key])) {
                $groups[$key] = $row + ['docs' => []];
            }
            $groups[$key]['docs'][] = $row;
        }
        $category = $filter['category'] ?? '';
        if ($category !== '') {
            $groups = array_filter($groups, static function ($group) use ($category) {
                foreach ($group['docs'] as $doc) {
                    $name = strtolower(str_replace('_', ' ', $doc['kategori']));
                    if ($name === strtolower($category) || ($category === 'Transport' && $name === 'transportasi')
                        || ($category === 'Petty Cash' && Rp_transfer::groupType($name) === 'refill')) {
                        return true;
                    }
                }
                return false;
            });
        }
        return $groups;
    }

    public static function category($value)
    {
        return $value === 'refill_pettycash' ? 'Refill Pettycash' : $value;
    }

    public static function bearer($value)
    {
        return ['company' => 'Perusahaan', 'recipient' => 'Penerima', 'free' => 'Gratis'][$value] ?? '-';
    }

    public static function comparison(array $group)
    {
        $planned = Rp_transfer_payment::planned($group);
        $admin = Rp_transfer::cents($group['admin']);
        $charge = Rp_transfer::cents($group['bank_charge'], true);
        $actualBank = Rp_transfer::cents($group['payment_bank']);
        $received = $actualBank - $charge;
        $plannedBearer = $admin ? self::bearer($group['bearer']) : 'Tanpa admin';
        $actualBearer = $group['actual_bearer'] === 'free' ? 'Gratis' : ($charge || $admin ? self::bearer($group['actual_bearer']) : 'Tanpa admin');
        $differences = [];
        if ($plannedBearer !== $actualBearer) {
            $differences[] = 'Penanggung admin: ' . $plannedBearer . ' → ' . $actualBearer;
        }
        foreach (['Bank Charge' => [$admin, $charge], 'Transfer diterima penerima' => [$planned['transfer'], $received], 'Total keluar bank' => [$planned['bank'], $actualBank]] as $label => $pair) {
            if ($pair[0] !== $pair[1]) {
                $differences[] = $label . ': ' . Rp_transfer::money($pair[0]) . ' → ' . Rp_transfer::money($pair[1]) . ' (' . Rp_transfer::money($pair[1] - $pair[0]) . ')';
            }
        }
        return compact('planned', 'admin', 'charge', 'actualBank', 'received', 'plannedBearer', 'actualBearer', 'differences');
    }

    public static function exportPlan($sheet, array $groups, $title, $subtitle, $single = false)
    {
        $last = 'S';
        self::row($sheet, 1, [$title]);
        self::row($sheet, 2, [$subtitle]);
        $sheet->mergeCells('A1:S1')->mergeCells('A2:S2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        self::row($sheet, 3, ['NO','NO. DOKUMEN','KATEGORI','REQUEST BY','COMPANY','KEPERLUAN','DPP','PPN','PPH23','PPH21','ADMIN',
            'DIBAYARKAN','JUMLAH DIBAYARKAN BANK','NAMA','NO. REK','NO. PENGAJUAN','TRANSFER','ADMIN DITANGGUNG','KETERANGAN PEMBAYARAN']);
        self::headline($sheet, 'A3:' . $last . '3');
        foreach ([5,19,16,25,12,42,14,12,12,12,10,15,28,26,24,16,14,22,65] as $index => $width) {
            $sheet->getColumnDimensionByColumn($index)->setWidth($width);
        }
        $row = 4;
        $number = 0;
        $byCompany = [];
        foreach ($groups as $group) {
            $byCompany[$group['company_id']][] = $group;
        }
        $companyTotals = [];
        $grand = array_fill(0, 7, 0);
        foreach ($byCompany as $id => $transfers) {
            $totals = array_fill(0, 7, 0);
            foreach ($transfers as $group) {
                $first = $row;
                $plan = Rp_transfer_payment::planned($group);
                $count = count($group['docs']);
                $note = 'Pengajuan ' . implode(' + ', array_unique(array_map([self::class, 'category'], array_column($group['docs'], 'kategori')))) . '. ' .
                    ($count === 1 ? '1 dokumen = 1 payment, transfer ke ' : 'Digabung jadi 1 payment: ' . $count . ' dokumen (' . implode(', ', array_column($group['docs'], 'no_dokumen')) . '), 1x transfer ke ') . $group['nama'];
                $admin = (float) $group['admin'];
                $bearer = !$admin ? 'Tanpa admin' : self::bearer($group['bearer']) . ' (Rp ' . number_format($admin, fmod($admin, 1) ? 2 : 0, ',', '.') .
                    ($group['bearer'] === 'recipient' ? ' dipotong dari transfer)' : ' dibayar terpisah)');
                foreach ($group['docs'] as $index => $doc) {
                    $values = [(float) $doc['dpp'], (float) $doc['nilai_ppn'], $doc['flag_pph23'] ? (float) $doc['nilai_pph'] : 0,
                        $doc['flag_pph21'] ? (float) $doc['nilai_pph'] : 0, $index ? 0 : (float) $group['admin'],
                        (float) $doc['dibayarkan'], $index ? 0 : $plan['bank'] / 100];
                    $totals = array_map(static function ($total, $value) { return $total + $value; }, $totals, $values);
                    if ($index) {
                        $values[4] = $values[6] = '';
                    }
                    $company = self::COMPANIES[$id] ?? $group['company_nama'];
                    self::row($sheet, $row++, array_merge([
                        ++$number, $doc['no_dokumen'], self::category($doc['kategori']), $doc['request_by'] . ' - ' . $company, $company,
                        $doc['keterangan_finance'] ?: $doc['keperluan'],
                    ], $values, [
                        $index ? '' : $group['nama'], $index ? '' : $group['bank'] . ' - ' . $group['no_rek'],
                        $group['no_pengajuan'], $index ? '' : 'Transfer ' . $group['urutan'],
                        $index ? '' : $bearer, $index ? '' : $note,
                    ]));
                }
                if ($row > $first + 1) {
                    foreach (['K','M','N','O','Q','R','S'] as $letter) {
                        $sheet->mergeCells($letter . $first . ':' . $letter . ($row - 1));
                    }
                }
            }
            $companyTotals[$id] = $totals[6];
            $grand = array_map(static function ($a, $b) { return $a + $b; }, $grand, $totals);
            self::row($sheet, $row++, array_merge(['','','','','',$single ? 'TOTAL' : 'TOTAL ' . strtoupper(self::COMPANIES[$id] ?? $id)], $totals));
            if (!$single) {
                $row++;
            }
        }
        if (!$single) {
            self::row($sheet, $row++, array_merge(['','','','','','GRAND TOTAL'], $grand));
        }
        $sheet->getStyle('G4:M' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;
        self::row($sheet, $row, [self::NOTE]);
        $sheet->mergeCells('A' . $row . ':S' . $row);
        $row += 2;
        self::row($sheet, $row++, ['', '', '', '', '', 'RINGKASAN PER COMPANY']);
        self::row($sheet, $row, ['', '', '', '', '', 'COMPANY', 'PENGAJUAN', 'SALDO (isi manual)', 'SISA SALDO']);
        self::headline($sheet, 'F' . $row . ':I' . $row);
        $start = ++$row;
        foreach (self::COMPANIES as $id => $name) {
            self::row($sheet, $row, ['', '', '', '', '', $name, $companyTotals[$id] ?? 0]);
            $sheet->setCellValue('I' . $row, '=IF(H' . $row . '="","",H' . $row . '-G' . $row . ')');
            $row++;
        }
        self::row($sheet, $row, ['', '', '', '', '', 'GRAND TOTAL']);
        foreach (['G','H','I'] as $column) {
            $sheet->setCellValue($column . $row, '=SUM(' . $column . $start . ':' . $column . ($row - 1) . ')');
        }
        $sheet->getStyle('G' . $start . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->freezePane('A4');
    }

    public static function row($sheet, $row, array $values)
    {
        foreach ($values as $i => $value) {
            $sheet->setCellValueExplicitByColumnAndRow($i, $row, $value ?? '',
                is_int($value) || is_float($value) ? PHPExcel_Cell_DataType::TYPE_NUMERIC : PHPExcel_Cell_DataType::TYPE_STRING);
        }
    }

    public static function headline($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '3C8DBC']]]);
    }
}
