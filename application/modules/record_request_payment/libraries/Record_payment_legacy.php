<?php

require_once dirname(__DIR__, 2) . '/request_payment/libraries/Rp_transfer.php';

/**
 * Tab Payment Lama di Record Payment: payment yang diproses lewat form lama.
 * 1 baris = 1 No. Payment. Aturannya sama dengan menu Payment (non pembelian), dikurangi payment transfer.
 */
final class Record_payment_legacy
{
    public const PER_PAGE = 25;
    // Company diambil dari jurnal; STM-Sustain / STM-Vuca adalah biaya company tersebut.
    private const COMPANIES = ['7' => ['STM'], '3' => ['Sustain', 'STM-Sustain'], '4' => ['Vuca', 'STM-Vuca']];

    /** No. Payment yang cocok dengan filter, tanggal bayar terbaru dulu. */
    public static function numbers($db, $company = '', $search = '')
    {
        self::base($db)->select('id_payment, MAX(tgl_bayar) AS tgl', false)->group_by('id_payment');
        $dates = array_column(self::rows($db->get('payment_approve')), 'tgl', 'id_payment');
        if (Rp_transfer::installed($db)) {
            foreach (self::rows($db->select('id_payment')->where('id_payment IS NOT NULL', null, false)->get('tr_rp_transfer')) as $row) {
                unset($dates[$row['id_payment']]);
            }
        }
        $search = trim((string) $search);
        if ($search !== '' && $dates) {
            $documents = array_column(self::rows($db->select('no_dokumen')->like('no_pengajuan', $search)->get('tr_rp_record')), 'no_dokumen');
            self::base($db)->select('id_payment')->group_start()->like('id_payment', $search)->or_like('no_doc', $search)
                ->or_like('created_by', $search)->or_like('keterangan_pembayaran', $search);
            if ($documents) {
                $db->or_where_in('no_doc', $documents);
            }
            $db->group_end();
            $dates = array_intersect_key($dates, array_flip(array_column(self::rows($db->get('payment_approve')), 'id_payment')));
        }
        if (isset(self::COMPANIES[(string) $company]) && $dates) {
            $matched = self::rows($db->select('no_transaksi')->where_in('nm_company', self::COMPANIES[(string) $company])
                ->where_in('no_transaksi', array_map('strval', array_keys($dates)))->get('tr_jurnal'));
            $dates = array_intersect_key($dates, array_flip(array_column($matched, 'no_transaksi')));
        }
        uksort($dates, static function ($a, $b) use ($dates) {
            return [(string) $dates[$b], (string) $b] <=> [(string) $dates[$a], (string) $a];
        });
        return array_map('strval', array_keys($dates));
    }

    /** Rincian untuk No. Payment yang ditampilkan saja, urutannya mengikuti $numbers. */
    public static function details($db, array $numbers, $root = null)
    {
        if (!$numbers) {
            return [];
        }
        $root = $root === null ? FCPATH : $root;
        $payments = [];
        self::base($db)->where_in('id_payment', $numbers)->order_by('no_doc', 'ASC');
        foreach (self::rows($db->get('payment_approve')) as $row) {
            $payments[$row['id_payment']][] = $row;
        }
        $charges = array_column(self::rows($db->select('id, bank_charge')->where_in('id', $numbers)->get('tr_payment_paid')), 'bank_charge', 'id');
        $companies = [];
        foreach (self::rows($db->select('no_transaksi, nm_company')->where_in('no_transaksi', $numbers)->get('tr_jurnal')) as $row) {
            if ((string) $row['nm_company'] !== '') {
                $companies[$row['no_transaksi']][$row['nm_company']] = true;
            }
        }
        $documents = [];
        foreach ($payments as $rows) {
            $documents = array_merge($documents, array_column($rows, 'no_doc'));
        }
        $submissions = $documents ? array_column(self::rows($db->select('no_dokumen, no_pengajuan')->where_in('no_dokumen', $documents)
            ->order_by('id', 'ASC')->get('tr_rp_record')), 'no_pengajuan', 'no_dokumen') : [];

        $result = [];
        foreach ($numbers as $number) {
            $rows = $payments[$number] ?? [];
            if (!$rows) {
                continue;
            }
            $unique = static function ($column) use ($rows) {
                return implode(', ', array_unique(array_filter(array_map('strval', array_column($rows, $column)), 'strlen')));
            };
            // Sama dengan menu Payment: nilai bank satu kali per payment; 0 = jumlah dokumennya.
            $paid = max(array_map('floatval', array_column($rows, 'payment_bank')));
            $docs = array_column($rows, 'no_doc');
            $result[] = [
                'payment' => $number,
                'tgl' => max(array_map('strval', array_column($rows, 'tgl_bayar'))),
                'pengajuan' => implode(', ', array_unique(array_values(array_intersect_key($submissions, array_flip($docs))))),
                'company' => implode(', ', array_keys($companies[$number] ?? [])),
                'request_by' => $unique('created_by'),
                'docs' => $docs,
                'bank' => $unique('nm_coa_bank'),
                'paid' => $paid ?: array_sum(array_map('floatval', array_column($rows, 'jumlah'))),
                'charge' => (float) ($charges[$number] ?? 0),
                'note' => $unique('keterangan_pembayaran'),
                'proofs' => self::proofs(array_column($rows, 'link_doc'), $root),
            ];
        }
        return $result;
    }

    private static function proofs(array $values, $root)
    {
        $files = [];
        foreach ($values as $value) {
            $value = trim((string) $value);
            $names = json_decode($value, true);
            foreach (is_array($names) ? $names : explode(',', $value) as $name) {
                $name = is_string($name) ? trim($name) : '';
                if ($name !== '' && basename($name) === $name && is_file($root . '/assets/expense/' . $name)) {
                    $files[$name] = ['name' => $name, 'url' => base_url('assets/expense/' . rawurlencode($name))];
                }
            }
        }
        return array_values($files);
    }

    private static function base($db)
    {
        return $db->where('status', 2)->where('id_payment IS NOT NULL', null, false)->where('id_payment <>', '')
            ->where("no_doc NOT LIKE '%INV-%'", null, false)->where("no_doc NOT LIKE '%PI-____-__-%'", null, false);
    }

    private static function rows($result)
    {
        return Rp_transfer::write($result)->result_array();
    }
}
