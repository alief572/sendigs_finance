<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__) . '/libraries/Kwitansi_company.php';

class Kwitansi_deposit_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function consultant_table_exists($table)
    {
        if (!defined('DBCNL')) return false;
        $query = $this->db->query(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            array(DBCNL, $table)
        );
        return $query && $query->num_rows() > 0;
    }

    public function get_roman_month($month)
    {
        $romanMonths = array(
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        );
        $m = (int)$month;
        if (function_exists('int_to_roman')) {
            return int_to_roman($m);
        }
        return isset($romanMonths[$m]) ? $romanMonths[$m] : 'I';
    }

    public function get_company_code($companyId)
    {
        if ($this->consultant_table_exists('kons_tr_company')) {
            $comp = $this->db->query("SELECT nm_company FROM " . DBCNL . ".kons_tr_company WHERE id = ?", array($companyId))->row_array();
            if ($comp && !empty($comp['nm_company'])) {
                $words = preg_split('/\s+/', trim($comp['nm_company']));
                $acronym = '';
                foreach ($words as $w) {
                    if (strtoupper($w) !== 'PT' && strtoupper($w) !== 'CV') {
                        $acronym .= strtoupper(substr($w, 0, 1));
                    }
                }
                return $acronym;
            }
        }
        return '';
    }

    public function get_company_name($companyId)
    {
        if ($this->consultant_table_exists('kons_tr_company')) {
            $comp = $this->db->query("SELECT nm_company FROM " . DBCNL . ".kons_tr_company WHERE id = ?", array($companyId))->row_array();
            if ($comp && !empty($comp['nm_company'])) {
                return $comp['nm_company'];
            }
        }
        return '';
    }

    public function generate_no_kwitansi($companyId = null, $date = null)
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $prefix = "KD/{$year}/{$month}/";

        $query = $this->db->query(
            "/* kwitansi_number_candidates */ SELECT no_kwitansi AS document_number FROM tr_kwitansi_deposit WHERE no_kwitansi LIKE ?
             UNION ALL SELECT no_invoice FROM tr_invoicing WHERE no_invoice LIKE ?
             UNION ALL SELECT no_referensi FROM tr_customer_deposit WHERE no_referensi LIKE ?
             UNION ALL SELECT no_transaksi FROM tr_jurnal WHERE no_transaksi LIKE ?",
            array($prefix . '%', $prefix . '%', $prefix . '%', $prefix . '%')
        );
        return $this->next_document_number($query, $prefix);
    }

    public function generate_deposit_number($date)
    {
        $prefix = 'DEP/' . date('Y/m', strtotime($date)) . '/';
        $query = $this->db->query(
            '/* deposit_number_candidates */ SELECT no_deposit AS document_number FROM tr_customer_deposit WHERE no_deposit LIKE ?',
            array($prefix . '%')
        );
        return $this->next_document_number($query, $prefix);
    }

    private function next_document_number($query, $prefix)
    {
        if (!$query) throw new RuntimeException('Nomor dokumen tidak dapat diperiksa. Coba kembali setelah pemeriksaan database.');
        $maximum = 0;
        foreach ($query->result_array() as $row) {
            if (!preg_match('/^' . preg_quote($prefix, '/') . '([0-9]+)$/D', $row['document_number'], $match)) continue;
            $number = filter_var($match[1], FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)));
            // Leading zeroes are part of the document format.
            if ($number === false) $number = filter_var(ltrim($match[1], '0') ?: '0', FILTER_VALIDATE_INT);
            if ($number === false || $number < 0 || $number === PHP_INT_MAX) throw new RuntimeException('Urutan nomor dokumen tidak valid.');
            $maximum = max($maximum, $number);
        }
        return $prefix . sprintf('%04d', $maximum + 1);
    }

    private function checked_write($result, $stage)
    {
        if ($result === false || $this->db->trans_status() === false) {
            throw new RuntimeException('Gagal menyimpan ' . $stage . '. Seluruh perubahan dibatalkan.');
        }
    }

    public function get_customer_list()
    {
        $customers = array();
        if ($this->consultant_table_exists('kons_tr_spk_penawaran')) {
            $q = $this->db->query("SELECT DISTINCT id_customer, nm_customer FROM " . DBCNL . ".kons_tr_spk_penawaran WHERE nm_customer IS NOT NULL AND nm_customer != '' ORDER BY nm_customer ASC");
            if ($q && $q->num_rows() > 0) {
                $customers = $q->result_array();
            }
        }
        if (empty($customers) && $this->db->table_exists('tr_invoicing')) {
            $q = $this->db->query("SELECT DISTINCT id_customer, nm_customer FROM tr_invoicing WHERE nm_customer IS NOT NULL AND nm_customer != '' ORDER BY nm_customer ASC");
            if ($q && $q->num_rows() > 0) {
                $customers = $q->result_array();
            }
        }
        return $customers;
    }

    public function get_kwitansi_customer_list()
    {
        return $this->db->query("SELECT DISTINCT id_customer, nm_customer FROM tr_kwitansi_deposit WHERE nm_customer IS NOT NULL AND nm_customer != '' ORDER BY nm_customer ASC")->result_array();
    }

    public function get_active_spk_by_customer($customerId, $nmCustomer = '')
    {
        if (!$this->consultant_table_exists('kons_tr_spk_penawaran')) {
            return array();
        }

        $this->db->select('a.id_spk_penawaran, a.id_penawaran, a.id_project, a.id_customer, a.nm_customer, a.id_company');
        if ($this->db->field_exists('no_spk', DBCNL . '.kons_tr_spk_penawaran')) {
            $this->db->select('a.no_spk');
        }
        if ($this->db->field_exists('no_po', DBCNL . '.kons_tr_spk_penawaran')) {
            $this->db->select('a.no_po');
        }
        if ($this->db->field_exists('nm_project', DBCNL . '.kons_tr_spk_penawaran')) {
            $this->db->select('a.nm_project');
        }
        if ($this->db->field_exists('nilai_kontrak_bersih', DBCNL . '.kons_tr_spk_penawaran')) {
            $this->db->select('a.nilai_kontrak_bersih');
        }

        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        if (!is_scalar($customerId) || trim((string)$customerId) === '') return array();
        $this->db->where('a.id_customer', $customerId);
        $this->db->order_by('a.id_spk_penawaran', 'DESC');
        $spks = $this->db->get()->result_array();

        foreach ($spks as &$spk) {
            $spk['spk_company_id'] = $spk['id_company'];
            $spk['id_company'] = null;
            $spk['nm_company'] = '';
            try {
                $source = Kwitansi_company::resolve($this->db, $spk);
                $spk['id_company'] = $source['company']['id'];
                $spk['nm_company'] = $source['company']['nm_company'];
            } catch (RuntimeException $error) {
                $spk['company_error'] = $error->getMessage();
            }
        }
        unset($spk);

        return $spks;
    }

    public static function spk_number($row)
    {
        $snapshot = json_decode($row['spk_snapshot'] ?? '', true);
        $number = is_array($snapshot) ? ($snapshot['spk']['no_spk'] ?? '') : '';
        if (is_scalar($number) && trim((string)$number) !== '') return (string)$number;
        if (!empty($row['id_spk_penawaran'])) return (string)$row['id_spk_penawaran'];
        return !empty($row['po_customer']) ? (string)$row['po_customer'] : '-';
    }

    public function get_kwitansi_list($filters = array())
    {
        $this->db->select('k.*, p.nm_customer AS cust_name, p.tanggal_aktif AS tanggal_bayar, i.saldo_piutang, i.no_invoice');
        if ($this->db->field_exists('no_kwitansi', 'tr_alokasi_split')) {
            // Draft penerimaan: split Deposit yang menunjuk kwitansi ini dan belum diproses (kriteria sama dengan tab draft Penerimaan Uang)
            $this->db->select("(SELECT s.id FROM tr_alokasi_split s WHERE s.jenis_alokasi = 8 AND s.no_kwitansi = k.no_kwitansi AND NOT EXISTS (SELECT 1 FROM tr_penerimaan_piutang pp WHERE pp.id_alokasi = s.id) ORDER BY s.id LIMIT 1) AS draft_split_id", false);
        }
        $this->db->from('tr_kwitansi_deposit k');
        $this->db->join('tr_customer_deposit p', 'p.no_referensi = k.no_kwitansi', 'left');
        $this->db->join('tr_invoicing i', 'i.no_invoice = k.no_kwitansi AND i.tax_treatment = "DEP"', 'left');

        if (!empty($filters['status'])) {
            $this->db->where('k.status', $filters['status']);
        }
        if (!empty($filters['id_customer'])) {
            $this->db->where('k.id_customer', $filters['id_customer']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('k.tanggal_kwitansi >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('k.tanggal_kwitansi <=', $filters['end_date']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('k.no_kwitansi', $filters['search']);
            $this->db->or_like('k.nm_customer', $filters['search']);
            $this->db->or_like('k.po_customer', $filters['search']);
            $this->db->or_like('k.id_spk_penawaran', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('k.id', 'DESC');
        $list = $this->db->get()->result_array();
        foreach ($list as &$row) {
            $row['no_spk'] = self::spk_number($row);
            if (!isset($row['saldo_piutang']) || $row['saldo_piutang'] === null) {
                $row['saldo_piutang'] = ($row['status'] === 'MENUNGGU_PEMBAYARAN') ? $row['nilai_kwitansi'] : '0.00';
            }
            if (empty($row['nm_company'])) {
                $row['nm_company'] = $this->get_company_name($row['id_company'] ?? null);
            }
        }
        unset($row);
        return $list;
    }

    public function get_kwitansi_by_id($id)
    {
        $this->db->select('k.*, d.no_deposit, d.sisa_saldo, d.status AS deposit_status');
        $this->db->from('tr_kwitansi_deposit k');
        $this->db->join('tr_customer_deposit d', "d.no_referensi = k.no_kwitansi AND d.tipe_sumber = 'KWITANSI_DEPOSIT'", 'left');
        if (is_numeric($id)) {
            $this->db->where('k.id', $id);
        } else {
            $this->db->where('k.no_kwitansi', $id);
        }
        $row = $this->db->get()->row_array();
        if ($row) $row['no_spk'] = self::spk_number($row);
        return $row;
    }

    public function get_kwitansi_journal($noKwitansi)
    {
        return $this->db->query(
            "SELECT * FROM tr_jurnal WHERE no_transaksi = ? AND jenis_transaksi = 'Kwitansi Deposit' ORDER BY id ASC",
            array($noKwitansi)
        )->result_array();
    }

    public function save_kwitansi($data, $managedTransaction = false)
    {
        if (empty($data['id_customer']) || empty($data['nm_customer'])) {
            throw new InvalidArgumentException('Customer wajib dipilih.');
        }

        $rawNominal = isset($data['nilai_kwitansi']) ? $data['nilai_kwitansi'] : 0;
        $cents = Penerimaan_calculation::cents((string)$rawNominal);
        if ($cents <= 0) {
            throw new InvalidArgumentException('Nilai kwitansi deposit harus lebih besar dari 0.');
        }

        $tanggal = !empty($data['tanggal_kwitansi']) ? $data['tanggal_kwitansi'] : date('Y-m-d');
        $companyId = $data['id_company'] ?? null;
        if (!is_scalar($companyId) || trim((string)$companyId) === '') throw new InvalidArgumentException('Company kwitansi wajib ditentukan dari penawaran.');
        $companyName = isset($data['nm_company']) ? $data['nm_company'] : $this->get_company_name($companyId);
        $userId = !empty($data['user_id']) ? $data['user_id'] : (isset($this->auth) ? $this->auth->user_id() : 'SYSTEM');
        $poCustomer = isset($data['po_customer']) ? (is_array($data['po_customer']) ? implode(', ', $data['po_customer']) : $data['po_customer']) : '';
        $keterangan = !empty($data['keterangan']) ? $data['keterangan'] : ('Kwitansi Deposit PO: ' . $poCustomer);

        if (!$managedTransaction && $this->db->trans_begin() === false) {
            throw new RuntimeException('Gagal memulai transaksi kwitansi deposit.');
        }
        try {
            $noKwitansi = $this->generate_no_kwitansi($companyId, $tanggal);
            $noDeposit = $this->generate_deposit_number($tanggal);
            $decVal = Invoice_calculation::decimal($cents);

            // 1. Insert tr_kwitansi_deposit
            $kwData = array(
                'no_kwitansi' => $noKwitansi,
                'tanggal_kwitansi' => $tanggal,
                'id_customer' => $data['id_customer'],
                'nm_customer' => $data['nm_customer'],
                'id_company' => $companyId,
                'po_customer' => $poCustomer,
                'nilai_kwitansi' => $decVal,
                'status' => 'MENUNGGU_PEMBAYARAN',
                'no_penerimaan' => null,
                'keterangan' => $keterangan,
                'created_by' => $userId,
                'created_date' => date('Y-m-d H:i:s')
            );
            if (isset($data['id_spk_penawaran'])) {
                $kwData['id_spk_penawaran'] = $data['id_spk_penawaran'];
                $kwData['spk_snapshot'] = $data['spk_snapshot'];
            }
            $this->checked_write($this->db->insert('tr_kwitansi_deposit', $kwData), 'header kwitansi deposit');
            $kwId = $this->db->insert_id();

            // 2. Insert tr_customer_deposit (status MENUNGGU_PEMBAYARAN)
            $depData = array(
                'no_deposit' => $noDeposit,
                'id_customer' => $data['id_customer'],
                'nm_customer' => $data['nm_customer'],
                'id_company' => $companyId,
                'tipe_sumber' => 'KWITANSI_DEPOSIT',
                'no_referensi' => $noKwitansi,
                'po_customer' => $poCustomer,
                'tanggal' => $tanggal,
                'nominal_awal' => $decVal,
                'nominal_terpakai' => '0.00',
                'sisa_saldo' => $decVal,
                'status' => 'MENUNGGU_PEMBAYARAN',
                'keterangan' => 'Kwitansi Deposit: ' . $noKwitansi,
                'created_by' => $userId,
                'created_date' => date('Y-m-d H:i:s')
            );
            $this->checked_write($this->db->insert('tr_customer_deposit', $depData), 'voucher deposit');

            // 3. Insert tr_invoicing (tax_treatment = 'DEP', saldo_piutang = nilai_kwitansi)
            $snapshot = array(
                'type' => 'DEP',
                'version' => 3,
                'owner' => array(
                    'id' => (string)$companyId,
                    'nm_company' => $companyName
                ),
                'accounts' => array(
                    'receivable' => array('code' => '1102-01-01', 'name' => 'Piutang Dagang Usaha'),
                    'pph' => array('code' => '1106-01-02', 'name' => 'PPh 23 Disetor'),
                    'deposit' => array('code' => '2102-01-02', 'name' => 'DEPOSIT CUSTOMER')
                ),
                'nilai' => $decVal,
                'date' => $tanggal,
                'po_customer' => $poCustomer
            );
            $invData = array(
                'id' => 'KD-' . hash('sha256', $noKwitansi),
                'id_actual_plan_tagih' => 'KD-' . hash('sha256', $noKwitansi),
                'id_detail_plan_tagih' => 0,
                'no_invoice' => $noKwitansi,
                'tanggal_invoice' => $tanggal,
                'id_customer' => $data['id_customer'],
                'nm_customer' => $data['nm_customer'],
                'total_nominal_jurnal' => $decVal,
                'dpp_lain_lain_jurnal' => '0.00',
                'ppn_jurnal' => '0.00',
                'pph_jurnal' => '0.00',
                'total_akhir_jurnal' => $decVal,
                'tagihan_ppn_jurnal' => $decVal,
                'saldo_piutang' => $decVal,
                'tax_treatment' => 'DEP',
                'consulting_version' => 3,
                'consulting_snapshot' => json_encode($snapshot),
                'tipe_invoice' => '0',
                'non_kons' => '0',
                'created_by' => $userId,
                'created_date' => date('Y-m-d H:i:s')
            );
            if (isset($data['accounts'])) {
                $snapshot['accounts'] = $data['accounts'];
                $snapshot['spk'] = $data['no_spk'];
                $snapshot['id_spk_penawaran'] = $data['id_spk_penawaran'];
                $invData['id_spk_penawaran'] = $data['id_spk_penawaran'];
                $invData['consulting_company_id'] = $companyId;
                $invData['consulting_snapshot'] = json_encode($snapshot);
            }
            $this->checked_write($this->db->insert('tr_invoicing', $invData), 'piutang kwitansi deposit');

            // 4. Accrual Journal in tr_jurnal (Debit Piutang 1102-01-01, Kredit Deposit 2102-01-02)
            $jNo1 = Invoice_calculation::journal_number($this->db, 1);
            $jNo2 = Invoice_calculation::journal_number($this->db, 2);

            $journalRows = array(
                array(
                    'no_jurnal' => $jNo1,
                    'tgl_jurnal' => $tanggal,
                    'coa' => '1102-01-01',
                    'nm_coa' => 'Piutang Dagang Usaha',
                    'id_company' => $companyId,
                    'nm_company' => $companyName,
                    'debit' => $decVal,
                    'kredit' => '0.00',
                    'keterangan' => 'Piutang Kwitansi Deposit ' . $noKwitansi . ' - ' . $data['nm_customer'],
                    'no_transaksi' => $noKwitansi,
                    'jenis_transaksi' => 'Kwitansi Deposit',
                    'sts' => '0',
                    'created_by' => $userId,
                    'created_date' => date('Y-m-d H:i:s')
                ),
                array(
                    'no_jurnal' => $jNo2,
                    'tgl_jurnal' => $tanggal,
                    'coa' => '2102-01-02',
                    'nm_coa' => 'Deposit Customer',
                    'id_company' => $companyId,
                    'nm_company' => $companyName,
                    'debit' => '0.00',
                    'kredit' => $decVal,
                    'keterangan' => 'Deposit Customer ' . $noKwitansi . ' - ' . $data['nm_customer'],
                    'no_transaksi' => $noKwitansi,
                    'jenis_transaksi' => 'Kwitansi Deposit',
                    'sts' => '0',
                    'created_by' => $userId,
                    'created_date' => date('Y-m-d H:i:s')
                )
            );
            if (isset($data['accounts'])) {
                foreach (array('receivable', 'deposit') as $index=>$role) {
                    $journalRows[$index]['coa'] = $data['accounts'][$role]['code'];
                    $journalRows[$index]['nm_coa'] = $data['accounts'][$role]['name'];
                    $journalRows[$index]['keterangan'] = $data['accounts'][$role]['name'] . ' - ' . $noKwitansi . ' - ' . $data['no_spk'];
                }
            }
            $this->checked_write($this->db->insert_batch('tr_jurnal', $journalRows), 'jurnal kwitansi deposit');

            if ($this->db->trans_status() === false) {
                throw new RuntimeException('Gagal menyimpan transaksi kwitansi deposit.');
            }

            if (!$managedTransaction && $this->db->trans_commit() === false) {
                throw new RuntimeException('Commit transaksi kwitansi deposit gagal; perlu pemeriksaan sebelum mencoba kembali.');
            }
            return array(
                'status' => 1,
                'save' => 1,
                'id' => $kwId,
                'no_kwitansi' => $noKwitansi,
                'no_deposit' => $noDeposit,
                'msg' => 'Kwitansi deposit ' . $noKwitansi . ' berhasil diterbitkan.'
            );
        } catch (Throwable $e) {
            if (!$managedTransaction) $this->db->trans_rollback();
            throw $e;
        }
    }
}
