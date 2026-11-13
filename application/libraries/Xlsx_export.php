<?php
// Shared .xlsx writer for the Penerimaan / Kwitansi / Deposit exports (bundled PHPExcel).
// Spec: sheet, title, subtitle (optional), head[], rows[][], total[] (optional), numfmt (default '#,##0').
// Cell values: int/float -> number, array('date'=>'Y-m-d') -> date, array('formula'=>'=SUM(A1:A2)') -> formula (hanya dari kode, bukan input user), anything else -> explicit string.
if (!class_exists('PHPExcel')) {
    require_once __DIR__ . '/PHPExcel.php';
}

class Xlsx_export
{
    const MONTHS_ID = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

    /** TAX[t] mockup. */
    public static function tax_label($code)
    {
        $map = array('KB' => 'Kawasan Berikat', 'UMKM' => 'UMKM Non-PKP', 'DEP' => 'Deposit');
        $code = strtoupper(trim((string)$code));
        return isset($map[$code]) ? $map[$code] : $code;
    }

    public static function build(array $spec)
    {
        $book = new PHPExcel();
        $ws = $book->getActiveSheet();
        $ws->setTitle(substr(preg_replace('/[\\\\\/?*\[\]:]/', ' ', $spec['sheet']), 0, 31));
        // plain = isi persis mockup (judul/header tanpa tebal, angka format Umum, tanggal teks ISO, lebar kolom tetap).
        $plain = !empty($spec['plain']);
        $bold = !$plain && empty($spec['nobold']);
        $numfmt = $plain ? 'General' : ($spec['numfmt'] ?? '#,##0');
        $r = 1;
        $ws->setCellValueExplicit('A' . $r, (string)$spec['title'], PHPExcel_Cell_DataType::TYPE_STRING);
        if ($bold) $ws->getStyle('A' . $r)->getFont()->setBold(true)->setSize(14);
        $r++;
        if (isset($spec['subtitle'])) {
            $ws->setCellValueExplicit('A' . $r, (string)$spec['subtitle'], PHPExcel_Cell_DataType::TYPE_STRING);
            $r++;
        }
        $r++; // blank row
        $ncol = count($spec['head']);
        $put = function ($row, array $cells) use ($ws, $numfmt, $plain) {
            foreach (array_values($cells) as $i => $v) {
                if ($v === null || $v === '') continue;
                $addr = PHPExcel_Cell::stringFromColumnIndex($i) . $row;
                if (is_int($v) || is_float($v)) {
                    $ws->setCellValueExplicit($addr, $v, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $ws->getStyle($addr)->getNumberFormat()->setFormatCode($numfmt);
                } elseif (is_array($v) && isset($v['formula'])) {
                    $ws->setCellValueExplicit($addr, (string)$v['formula'], PHPExcel_Cell_DataType::TYPE_FORMULA);
                    $ws->getStyle($addr)->getNumberFormat()->setFormatCode($numfmt);
                } elseif (is_array($v) && isset($v['date'])) {
                    if ($plain) { $ws->setCellValueExplicit($addr, substr((string)$v['date'], 0, 10), PHPExcel_Cell_DataType::TYPE_STRING); continue; }
                    $ts = strtotime((string)$v['date']);
                    if ($ts === false) { $ws->setCellValueExplicit($addr, (string)$v['date'], PHPExcel_Cell_DataType::TYPE_STRING); continue; }
                    $ws->setCellValueExplicit($addr, PHPExcel_Shared_Date::PHPToExcel($ts), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $ws->getStyle($addr)->getNumberFormat()->setFormatCode('dd-mmm-yyyy');
                } else {
                    $ws->setCellValueExplicit($addr, (string)$v, PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
        };
        $put($r, $spec['head']);
        if ($bold) $ws->getStyle('A' . $r . ':' . PHPExcel_Cell::stringFromColumnIndex($ncol - 1) . $r)->getFont()->setBold(true);
        $r++;
        foreach ($spec['rows'] as $row) { $put($r++, $row); }
        if (!empty($spec['total'])) {
            $put($r, $spec['total']);
            if ($bold) $ws->getStyle('A' . $r . ':' . PHPExcel_Cell::stringFromColumnIndex($ncol - 1) . $r)->getFont()->setBold(true);
        }
        for ($i = 0; $i < $ncol; $i++) {
            if (!empty($spec['widths'][$i])) { $ws->getColumnDimensionByColumn($i)->setWidth($spec['widths'][$i]); }
            else { $ws->getColumnDimensionByColumn($i)->setAutoSize(true); }
        }
        return $book;
    }

    public static function save(array $spec, $path)
    {
        PHPExcel_IOFactory::createWriter(self::build($spec), 'Excel2007')->save($path);
    }

    public static function download(array $spec, $filename)
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        PHPExcel_IOFactory::createWriter(self::build($spec), 'Excel2007')->save('php://output');
        exit;
    }

    /**
     * Ekspor tab Invoiced (UMKM-FR-07). $rows: array asosiatif per invoice (lihat Invoicing::invoiced_export_rows).
     * Judul r1, baris filter r2, header r4, data mulai r5; baris Total memakai rumus SUM atas kolom angka.
     * Uang dalam sen (int) supaya jumlah Excel = card (0 selisih); dibagi 100 hanya saat ditulis.
     */
    public static function invoiced_spec(array $rows, $filterText, $filename)
    {
        $head = array('No', 'No. Invoice', 'Tanggal Invoice', 'No. SPK', 'No. PO', 'No. Faktur', 'Customer', 'Project', 'Project Leader', 'Sales',
            'Company', 'TOP', 'Persen TOP', 'Perlakuan PPN', 'Penjualan', 'DPP Lain-lain', 'PPN', 'Total Tagihan', 'Saldo Piutang', 'Status');
        $moneyCols = array('sales', 'dpp', 'ppn', 'gross', 'saldo');
        $first = 5; $out = array(); $no = 0;
        foreach ($rows as $r) {
            $no++;
            $line = array($no, $r['no_invoice'], $r['tanggal'] === '' ? '' : array('date' => $r['tanggal']), $r['no_spk'], $r['no_po'], $r['no_faktur'],
                $r['customer'], $r['project'], $r['leader'], $r['sales_name'], $r['company'], $r['top'], $r['persen_top'], $r['tax_label']);
            foreach ($moneyCols as $k) $line[] = $r[$k] === null ? '' : $r[$k] / 100;
            $line[] = $r['status'];
            $out[] = $line;
        }
        $last = $first + count($rows) - 1;
        $total = array('Total');
        for ($i = 1; $i < 14; $i++) $total[] = '';
        foreach (array('O', 'P', 'Q', 'R', 'S') as $col) $total[] = array('formula' => '=SUM(' . $col . $first . ':' . $col . $last . ')');
        $total[] = '';
        return array('sheet' => 'Invoiced', 'title' => 'Daftar Invoice (Invoiced)', 'subtitle' => $filterText, 'head' => $head, 'rows' => $out,
            'total' => $total, 'numfmt' => '#,##0.00', 'filename' => $filename);
    }

    /** Report Penerimaan: one row per invoice/kwitansi, Penerimaan Bank only on the first row of each receipt. */
    public static function report_spec(array $reportData, $year, $month, $company)
    {
        $tUM = 0.0; $tAlok = 0.0; $rows = array(); $no = 0;
        foreach ($reportData as $g) {
            $no++;
            $date = !empty($g['tanggal_transaksi']) ? $g['tanggal_transaksi'] : $g['created_date'];
            $tUM += (float)$g['nominal_penerimaan_bank'];
            foreach (array_values($g['details']) as $j => $d) {
                $tAlok += (float)$d['penerimaan'];
                $dep = ($d['tax_treatment'] ?? '') === 'DEP';
                $rows[] = array($no, array('date' => $date), (string)$g['bank_name'], (string)$g['keterangan'], (string)$g['nm_customer'],
                    (string)$d['no_invoice'], (string)$d['company_name'], ($d['tax_treatment'] ?? '') !== '' ? self::tax_label($d['tax_treatment']) : '-',
                    $dep ? '-' : ($g['pph23_dipotong'] === 'Y' ? 'Potong' : 'Tidak potong'), (float)$d['pph23'],
                    $j === 0 ? (float)$g['nominal_penerimaan_bank'] : null, (float)$d['total_akhir_jurnal'], (float)$d['penerimaan'],
                    (float)($d['dep'] ?? 0), (float)$d['biaya_admin'], (float)($d['lebih'] ?? 0), (float)$d['sisa_piutang']);
            }
        }
        $m = !empty($month) ? (self::MONTHS_ID[(int)$month] ?? $month) : 'Semua';
        $y = !empty($year) ? $year : 'Semua';
        $c = !empty($company) ? $company : 'Semua';
        return array('sheet' => 'Report Penerimaan', 'title' => 'Report Penerimaan Piutang', 'nobold' => true,
            'widths' => array(5, 14, 40, 50, 32, 18, 10, 10, 15, 14, 16, 15, 18, 15, 12, 12, 12),
            'subtitle' => 'Tahun: ' . $y . ' | Bulan: ' . $m . ' | Company: ' . $c,
            'head' => array('No', 'Tanggal Penerimaan', 'Bank', 'Detail Penerimaan Bank', 'Customer', 'Nomor Invoice / Kwitansi', 'Company', 'TAX Code', 'PPh 23 Dipotong', 'Nominal PPh 23', 'Penerimaan Bank', 'Piutang Dagang', 'Alokasi Penerimaan', 'Deposit Dipakai', 'Biaya Admin', 'Lebih Bayar', 'Sisa Piutang'),
            'rows' => $rows, 'total' => array('Total', null, null, null, null, null, null, null, null, null, $tUM, null, $tAlok),
            'filename' => 'Report_Penerimaan_' . $y . '_' . $m . '_' . $c . '.xlsx');
    }

    public static function kwitansi_spec(array $list)
    {
        $rows = array(); $no = 0;
        foreach ($list as $k) {
            $rows[] = array(++$no, array('date' => $k['tanggal_kwitansi']), (string)$k['no_kwitansi'], (string)$k['nm_customer'],
                (string)($k['no_spk'] ?? $k['id_spk_penawaran'] ?? $k['po_customer'] ?? '-'), (string)($k['nm_company'] ?? '-'),
                (float)$k['nilai_kwitansi'], $k['status'] === 'LUNAS' ? 'Lunas' : 'Menunggu Pembayaran', (string)($k['no_penerimaan'] ?? ''));
        }
        return array('sheet' => 'Kwitansi Deposit', 'title' => 'Kwitansi Deposit', 'plain' => true, 'widths' => array(5, 12, 18, 32, 18, 14, 15, 20, 14),
            'head' => array('No', 'Tanggal', 'No Kwitansi', 'Customer', 'No SPK', 'Company', 'Nilai Kwitansi', 'Status', 'No Penerimaan'),
            'rows' => $rows, 'filename' => 'Kwitansi_Deposit.xlsx');
    }

    public static function deposit_index_spec(array $customers)
    {
        $rows = array(); $no = 0;
        foreach ($customers as $c) {
            $rows[] = array(++$no, (string)$c['nm_customer'], (int)$c['jumlah_deposit'], (float)$c['total_dari_kwitansi'], (float)$c['total_dari_lebih_bayar'],
                (float)$c['total_nominal_awal'], (float)$c['total_nominal_terpakai'], (float)$c['sisa_saldo'],
                !empty($c['last_deposit_date']) ? array('date' => $c['last_deposit_date']) : '-');
        }
        return array('sheet' => 'Index Deposit', 'title' => 'Monitoring Deposit', 'plain' => true, 'widths' => array(5, 32, 15, 20, 18, 20, 16, 18, 16),
            'head' => array('No', 'Customer', 'Jumlah Deposit', 'Dari Kwitansi Deposit', 'Dari Lebih Bayar', 'Total Nilai Deposit', 'Total Terpakai', 'Total Sisa Saldo', 'Deposit Terakhir'),
            'rows' => $rows, 'filename' => 'Index_Deposit.xlsx');
    }

    /** $sourceLabel: callable(row) => string (Deposit_customer_model::source_label). */
    public static function deposit_ledger_spec($nmCustomer, array $mutasi, $sourceLabel)
    {
        $rows = array(); $no = 0; $run = 0.0;
        foreach ($mutasi as $m) {
            $nom = (float)$m['nominal']; $in = $m['jenis_mutasi'] === 'IN';
            $run += $in ? $nom : -$nom;
            $rows[] = array(++$no, array('date' => $m['tanggal']), (string)$m['no_transaksi'], (string)call_user_func($sourceLabel, $m),
                (string)($m['keterangan'] ?? ''), $in ? $nom : 0.0, $in ? 0.0 : $nom, $run);
        }
        // Seperti mockup: sheet = nama customer (maks 31 karakter; karakter terlarang Excel diganti), file = Deposit_<nama dengan spasi jadi _>.
        $sheet = trim(mb_substr(preg_replace('/[\\/?*\[\]:]/', ' ', (string)$nmCustomer), 0, 31));
        return array('sheet' => $sheet !== '' ? $sheet : 'Mutasi Deposit', 'title' => 'Mutasi Deposit - ' . $nmCustomer, 'plain' => true, 'widths' => array(5, 12, 22, 34, 60, 14, 14, 14),
            'head' => array('No', 'Tanggal', 'No Penerimaan Piutang', 'Sumber', 'Keterangan', 'Masuk', 'Keluar', 'Saldo'),
            'rows' => $rows, 'filename' => 'Deposit_' . preg_replace('/\s+/', '_', trim((string)$nmCustomer)) . '.xlsx');
    }
}
