<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Deposit_customer_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    const SRC_KWITANSI = 'KWITANSI_DEPOSIT';
    const SRC_LEBIH = 'LEBIH_BAYAR';

    /**
     * Sumber tunggal Monitoring Deposit: seluruh mutasi deposit (semua company) beserta sumber vouchernya.
     * Deposit yang masih MENUNGGU_PEMBAYARAN tidak menambah saldo.
     */
    public function get_ledger_rows($id_customer = null)
    {
        $this->db->select('m.*, d.nm_customer, d.no_deposit, d.no_referensi, d.tipe_sumber, d.id_company, pp.id AS penerimaan_id');
        $this->db->from('tr_customer_deposit_mutasi m');
        $this->db->join('tr_customer_deposit d', 'd.id = m.id_deposit', 'left');
        $this->db->join('tr_penerimaan_piutang pp', 'pp.no_surat = m.no_transaksi', 'left');
        $this->db->where('d.status !=', 'MENUNGGU_PEMBAYARAN');
        if (is_scalar($id_customer) && trim((string)$id_customer) !== '') {
            $this->db->where('m.id_customer', $id_customer);
        }
        $this->db->order_by('m.tanggal', 'ASC');
        $this->db->order_by('m.id', 'ASC');
        return $this->db->get()->result_array();
    }

    /** Semua customer yang punya voucher deposit (termasuk MENUNGGU_PEMBAYARAN): id => nama. */
    public function get_listed_customers()
    {
        $this->db->select('id_customer, MAX(nm_customer) AS nm_customer', false);
        $this->db->from('tr_customer_deposit');
        $this->db->group_by('id_customer');
        $out = array();
        foreach ($this->db->get()->result_array() as $r) {
            $out[(string)$r['id_customer']] = (string)$r['nm_customer'];
        }
        return $out;
    }

    /** Nama customer bila punya voucher deposit apa pun statusnya, selain itu null. */
    public function get_customer_name($id_customer)
    {
        $all = $this->get_listed_customers();
        return isset($all[(string)$id_customer]) ? $all[(string)$id_customer] : null;
    }

    /** Label sumber mutasi sesuai mockup Monitoring Deposit. */
    public static function source_label($row)
    {
        if (($row['jenis_mutasi'] ?? '') === 'OUT') return 'Deposit Dipakai (Penerimaan Invoice)';
        return ($row['tipe_sumber'] ?? '') === self::SRC_LEBIH ? 'Lebih Bayar (Penerimaan Invoice)' : 'Penerimaan Kwitansi Deposit';
    }

    /** Ringkasan per customer (semua company) = jumlah mutasi IN dikurangi OUT. */
    public function get_customer_summary($search = '')
    {
        $out = array();
        foreach ($this->get_ledger_rows() as $m) {
            $cid = (string)$m['id_customer'];
            if (!isset($out[$cid])) {
                $out[$cid] = array('id_customer' => $cid, 'nm_customer' => (string)($m['nm_customer'] ?? ''), 'jumlah_deposit' => 0, '_kw' => 0, '_lebih' => 0, '_masuk' => 0, '_keluar' => 0, 'last_deposit_date' => '');
            }
            $c = Penerimaan_calculation::cents($m['nominal']);
            if ($m['jenis_mutasi'] === 'IN') {
                $out[$cid]['jumlah_deposit']++;
                $out[$cid]['_masuk'] += $c;
                if (($m['tipe_sumber'] ?? '') === self::SRC_LEBIH) $out[$cid]['_lebih'] += $c; else $out[$cid]['_kw'] += $c;
                if ((string)$m['tanggal'] > $out[$cid]['last_deposit_date']) $out[$cid]['last_deposit_date'] = (string)$m['tanggal'];
            } else {
                $out[$cid]['_keluar'] += $c;
            }
        }
        // Seperti mockup (depCusts): customer yang baru punya kwitansi belum lunas tetap tampil dengan nilai nol.
        foreach ($this->get_listed_customers() as $cid => $name) {
            if (!isset($out[$cid])) {
                $out[$cid] = array('id_customer' => $cid, 'nm_customer' => $name, 'jumlah_deposit' => 0, '_kw' => 0, '_lebih' => 0, '_masuk' => 0, '_keluar' => 0, 'last_deposit_date' => '');
            }
        }
        $rows = array();
        foreach ($out as $r) {
            if ($search !== '' && stripos($r['nm_customer'], (string)$search) === false) continue;
            $rows[] = array(
                'id_customer' => $r['id_customer'], 'nm_customer' => $r['nm_customer'], 'jumlah_deposit' => $r['jumlah_deposit'],
                'total_dari_kwitansi' => Penerimaan_calculation::decimal($r['_kw']), 'total_dari_lebih_bayar' => Penerimaan_calculation::decimal($r['_lebih']),
                'total_nominal_awal' => Penerimaan_calculation::decimal($r['_masuk']), 'total_nominal_terpakai' => Penerimaan_calculation::decimal($r['_keluar']),
                'sisa_saldo' => Penerimaan_calculation::decimal($r['_masuk'] - $r['_keluar']), 'last_deposit_date' => $r['last_deposit_date']
            );
        }
        usort($rows, function ($a, $b) { return strcasecmp($a['nm_customer'], $b['nm_customer']); });
        return $rows;
    }

    /** KPI dihitung dari ringkasan yang sama dengan tabel, sehingga tile dan tabel selalu sejalan. */
    public function get_kpi_summary()
    {
        $masuk = 0; $keluar = 0; $jumlah = 0; $bersaldo = 0;
        foreach ($this->get_customer_summary() as $c) {
            $masuk += Penerimaan_calculation::cents($c['total_nominal_awal']);
            $keluar += Penerimaan_calculation::cents($c['total_nominal_terpakai']);
            $jumlah += (int)$c['jumlah_deposit'];
            if (Penerimaan_calculation::cents($c['sisa_saldo']) > 0) $bersaldo++;
        }
        return array(
            'total_saldo' => Penerimaan_calculation::decimal($masuk - $keluar),
            'total_customer_aktif' => $bersaldo,
            'cnt_masuk' => $jumlah,
            'total_masuk' => Penerimaan_calculation::decimal($masuk),
            'total_keluar' => Penerimaan_calculation::decimal($keluar)
        );
    }

    public function get_customer_active_deposit_total($id_customer, $id_company = null)
    {
        if (!is_scalar($id_customer) || !is_scalar($id_company) || trim((string)$id_customer) === '' || trim((string)$id_company) === '') return 0;
        require_once dirname(__DIR__, 2).'/penerimaan_uang/libraries/Consulting_receipt.php';
        $reader = new Consulting_receipt((object)array('db'=>$this->db));
        return (float)Penerimaan_calculation::decimal($reader->get_customer_active_deposit($id_customer, $id_company));
    }

    public function get_customer_vouchers($id_customer, $id_company = null)
    {
        if (!is_scalar($id_customer) || !is_scalar($id_company) || trim((string)$id_customer) === '' || trim((string)$id_company) === '') return array();
        $this->db->select('*');
        $this->db->from('tr_customer_deposit');
        $this->db->where('id_customer', $id_customer);
        if (!empty($id_company)) {
            $this->db->where('id_company', $id_company);
        }
        $this->db->order_by('tanggal', 'ASC');
        $this->db->order_by('id', 'ASC');
        $res = $this->db->get()->result_array();
        return $res;
    }

    /** Kartu deposit per customer lintas company (form Penerimaan Piutang tidak memilih company). */
    public function get_customer_mutasi_ledger_all($id_customer)
    {
        if (!is_scalar($id_customer) || trim((string)$id_customer) === '') return array();
        $this->db->select('m.*, d.no_deposit, d.no_referensi, d.tipe_sumber, d.id_company');
        $this->db->from('tr_customer_deposit_mutasi m');
        $this->db->join('tr_customer_deposit d', 'd.id = m.id_deposit', 'left');
        $this->db->where('m.id_customer', $id_customer);
        $this->db->where('d.id_customer', $id_customer);
        $this->db->order_by('m.tanggal', 'ASC');
        $this->db->order_by('m.id', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_customer_mutasi_ledger($id_customer, $id_company = null)
    {
        if (!is_scalar($id_customer) || !is_scalar($id_company) || trim((string)$id_customer) === '' || trim((string)$id_company) === '') return array();
        $this->db->select('m.*, d.no_deposit, d.no_referensi, d.tipe_sumber, d.id_company');
        $this->db->from('tr_customer_deposit_mutasi m');
        $this->db->join('tr_customer_deposit d', 'd.id = m.id_deposit', 'left');
        $this->db->where('m.id_customer', $id_customer);
        $this->db->where('d.id_customer', $id_customer);
        if (!empty($id_company)) {
            $this->db->where('d.id_company', $id_company);
        }
        $this->db->order_by('m.tanggal', 'ASC');
        $this->db->order_by('m.id', 'ASC');
        $res = $this->db->get()->result_array();
        return $res;
    }

    public function get_reconcile_report($startDate = null, $endDate = null)
    {
        $this->db->select('d.*, k.no_kwitansi, k.status as status_kwitansi');
        $this->db->from('tr_customer_deposit d');
        $this->db->join('tr_kwitansi_deposit k', 'k.no_kwitansi = d.no_referensi', 'left');
        if ($startDate) $this->db->where('d.tanggal >=', $startDate);
        if ($endDate) $this->db->where('d.tanggal <=', $endDate);
        $this->db->order_by('d.tanggal', 'DESC');
        return $this->db->get()->result_array();
    }
}
