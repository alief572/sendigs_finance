<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Parser rekening koran per jenis bank (7 = format "No. rekening : ...", 22 = format "PERIOD : ...").
 * Dipakai bersama oleh preview dan simpan upload agar keduanya membaca file dengan cara yang sama.
 */
class Rekening_koran_parser
{
    /** @return array header fields + 'arr_detail' */
    public static function parse(array $csvData, array $post, $id_header, $userId)
    {
        $account_no = '';
        $periode_from = '';
        $periode_to = '';
        $saldo_awal = 0;
        $total_debit = 0;
        $total_kredit = 0;
        $total_akhir = 0;

        $arr_detail = [];

        $no = 0;
        foreach ($csvData as $row => $item) {

            // $insertData = array_combine($header, $item);

            if ($post['jenis_bank'] == '7') {
                $no++;
                if (isset($item[0]) && strpos($item[0], 'No. rekening : ') !== false && $account_no == '') {
                    $account_no = str_replace('No. rekening : ', '', $item[0]);
                }
                if (isset($item[0]) && strpos($item[0], 'Periode : ') !== false && $periode_from == '' && $periode_to == '') {
                    $periode_arr = str_replace('Periode : ', '', $item[0]);
                    $split_periode = explode(' - ', $periode_arr);

                    $periode_from = date('Y-m-d', strtotime(str_replace('/', '-', $split_periode[0])));
                    $periode_to = date('Y-m-d', strtotime(str_replace('/', '-', $split_periode[1])));
                }
                if (isset($item[0]) && strpos($item[0], 'Saldo Awal : ') !== false && $saldo_awal == '') {
                    $saldo_awal = str_replace('Saldo Awal : ', '', $item[0]);
                    $saldo_awal = str_replace(',', '', $saldo_awal);
                }
                if (isset($item[0]) && strpos($item[0], 'Mutasi Debet : ') !== false && $total_debit == '') {
                    $total_debit = str_replace('Mutasi Debet : ', '', $item[0]);
                    $total_debit = str_replace(',', '', $total_debit);
                }
                if (isset($item[0]) && strpos($item[0], 'Mutasi Kredit : ') !== false && $total_kredit == '') {
                    $total_kredit = str_replace('Mutasi Kredit : ', '', $item[0]);
                    $total_kredit = str_replace(',', '', $total_kredit);
                }
                if (isset($item[0]) && strpos($item[0], 'Saldo Akhir : ') !== false && $total_akhir == '') {
                    $total_akhir = str_replace('Saldo Akhir : ', '', $item[0]);
                    $total_akhir = str_replace(',', '', $total_akhir);
                }

                if ($no >= 8) {
                    if (strtotime(str_replace('/', '-', $item[0])) === false) {
                        $tanggal_transaksi = $item[0];
                    } else {
                        $tanggal_transaksi = date('Y-m-d', strtotime(str_replace('/', '-', $item[0])));
                    }

                    $debit = 0;
                    $kredit = 0;

                    if (isset($item[3]) && strpos($item[3], 'CR') !== false) {
                        $debit = str_replace('CR', '', $item[3]);
                        $debit = str_replace(',', '', $item[3]);
                        $debit = floatval($debit);
                    } else {
                        if (isset($item[3])) {
                            $kredit = str_replace('DB', '', $item[3]);
                            $kredit = str_replace(',', '', $item[3]);
                            $kredit = floatval($kredit);
                        }
                    }

                    $saldo = 0;

                    if (isset($item[4])) {
                        $saldo = str_replace(',', '', $item[4]);
                        $saldo = floatval($saldo);
                    }

                    if ($debit > 0 || $kredit > 0) {
                        $arr_detail[] = [
                            'id_header' => $id_header,
                            'tipe_bank' => $post['bank'],
                            'jenis_bank' => $post['jenis_bank'],
                            'tanggal_transaksi' => $tanggal_transaksi,
                            'keterangan' => $item[1],
                            'cabang' => $item[2],
                            'nominal_debit' => $debit,
                            'nominal_kredit' => $kredit,
                            'saldo' => $saldo,
                            'reference_no' => '',
                            'cheque_no' => '',
                            'status_approval' => 'pending',
                            'created_by' => $userId,
                            'created_date' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                // print_r($account_no);
            }

            if ($post['jenis_bank'] == '22') {
                $no++;

                // print_r($no . '<br>');
                if ($no == '2') {
                    $saldo_awal = str_replace(',', '', $item[7]);
                    $saldo_awal = floatval($saldo_awal);
                }
                if (strpos($item[0], 'PERIOD :') !== false && $periode_from == '') {
                    $split_periode = str_replace('/', '-', $item[1]);
                    $split_periode = explode(' - ', $split_periode);

                    $periode_from = date('Y-m-d', strtotime($split_periode[0]));
                    $periode_to = date('Y-m-d', strtotime($split_periode[1]));
                }
                if (strpos($item[0], 'ACCOUNT NO :') !== false && $account_no == '') {
                    $account_no = $item[1];
                }
                if (strpos($item[0], 'TOTAL DEBIT :') !== false && $total_kredit == 0) {
                    $total_kredit = str_replace(',', '', $item[2]);
                    $total_kredit = floatval($total_kredit);
                }
                if (strpos($item[0], 'TOTAL CREDIT :') !== false && $total_debit == 0) {
                    $total_debit = str_replace(',', '', $item[2]);
                    $total_debit = floatval($total_debit);
                }
                if (isset($item[6]) && strpos($item[6], 'CLOSING BALANCE :') !== false && $total_akhir == 0) {
                    $total_akhir = str_replace(',', '', $item[7]);
                    $total_akhir = floatval($total_akhir);
                }

                if ($no >= 11) {
                    $tanggal_transaksi = str_replace('/', '-', $item[0]);
                    $tanggal_transaksi = date('Y-m-d', strtotime($tanggal_transaksi));

                    $arr_detail[] = [
                        'id_header' => $id_header,
                        'tipe_bank' => $post['bank'],
                        'jenis_bank' => $post['jenis_bank'],
                        'tanggal_transaksi' => $tanggal_transaksi,
                        'keterangan' => $item[4],
                        'cabang' => '',
                        'nominal_debit' => str_replace(',', '', $item[6]),
                        'nominal_kredit' => str_replace(',', '', $item[5]),
                        'saldo' => str_replace(',', '', $item[7]),
                        'reference_no' => str_replace("'", "", $item[2]),
                        'cheque_no' => str_replace('', '', $item[3]),
                        'status_approval' => 'pending',
                        'created_by' => $userId,
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }

            // Example insert (adjust for your table and field names)
            // $this->db->insert('your_table', $insertData);
        }

        return [
            'account_no' => $account_no,
            'periode_from' => $periode_from,
            'periode_to' => $periode_to,
            'saldo_awal' => $saldo_awal,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit,
            'total_akhir' => $total_akhir,
            'arr_detail' => $arr_detail,
        ];
    }

    /**
     * Baris alokasi manual (admin) dari input form. Memakai validate() yang sama dengan upload.
     * @param array $bank baris ms_bank (id, bank = id jenis bank); jenis_bank selalu dari master, bukan dari POST.
     * @return array [row, errors]; errors kosong = valid
     */
    public static function manual_row(array $post, array $bank, $id_header, $userId)
    {
        $num = function ($v) { $v = str_replace(',', '', trim((string)$v)); return $v === '' ? 0 : $v; };
        $row = [
            'id_header' => $id_header,
            'tipe_bank' => $bank['id'],
            'jenis_bank' => $bank['bank'],
            'tanggal_transaksi' => trim((string)($post['tanggal_transaksi'] ?? '')),
            'keterangan' => trim((string)($post['keterangan'] ?? '')),
            'cabang' => '',
            'nominal_debit' => $num($post['nominal_debit'] ?? ''),
            'nominal_kredit' => $num($post['nominal_kredit'] ?? ''),
            'saldo' => $num($post['saldo'] ?? ''),
            'reference_no' => trim((string)($post['reference_no'] ?? '')),
            'cheque_no' => trim((string)($post['cheque_no'] ?? '')),
            'status_approval' => 'approved',
            'created_by' => $userId,
            'created_date' => date('Y-m-d H:i:s')
        ];
        $errors = [];
        // 'PEND' hanya untuk hasil upload mutasi BCA; input manual harus tanggal nyata.
        if ($row['tanggal_transaksi'] === 'PEND') $errors[] = 'Tanggal tidak valid (PEND)';
        foreach (self::validate([$row]) as $msg) $errors[] = $msg;
        if ($row['keterangan'] === '') $errors[] = 'Keterangan wajib diisi';
        if (!is_numeric($row['saldo'])) $errors[] = 'Saldo bukan angka';
        return [$row, $errors];
    }

    /** Validasi baris hasil parse; kembalikan [nomor baris (1-based) => pesan]. */
    public static function validate(array $rows)
    {
        $errors = [];
        foreach (array_values($rows) as $i => $r) {
            $msgs = [];
            $d = (string)$r['tanggal_transaksi'];
            $ok = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m) && checkdate((int)$m[2], (int)$m[3], (int)$m[1]) && (int)$m[1] > 2000;
            // 'PEND' = transaksi pending pada mutasi BCA; sistem menampilkannya sebagai PEND (tanggal 0000-00-00).
            if (!$ok && $d !== 'PEND') $msgs[] = 'Tanggal tidak valid (' . $d . ')';
            foreach (['nominal_debit' => 'Debit', 'nominal_kredit' => 'Kredit'] as $k => $label) {
                if (!is_numeric($r[$k])) $msgs[] = $label . ' bukan angka';
                elseif ((float)$r[$k] < 0) $msgs[] = $label . ' negatif';
            }
            if (is_numeric($r['nominal_debit']) && is_numeric($r['nominal_kredit']) && (float)$r['nominal_debit'] == 0 && (float)$r['nominal_kredit'] == 0) {
                $msgs[] = 'Debit dan Kredit sama-sama 0';
            }
            if ($msgs) $errors[$i + 1] = implode('; ', $msgs);
        }
        return $errors;
    }
}
