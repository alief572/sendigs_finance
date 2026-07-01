<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jurnal Payment Petty Cash Model
 *
 * Handles posting jurnal petty cash dari staging table (tr_jurnal di DBERP)
 * ke database akuntansi final (jurnal dan japh di DBACC_STM/VUCA/SUSTAIN).
 *
 * @author  Sendigs Dev Team
 */

class Jurnal_payment_petty_cash_model extends BF_Model
{
    protected $table_name = 'tr_jurnal';

    protected $accounting_stm;
    protected $accounting_vuca;
    protected $accounting_sustain;

    public function __construct()
    {
        parent::__construct();

        $this->accounting_stm     = $this->load->database('accounting_stm', true);
        $this->accounting_vuca    = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
    }

    /**
     * Get server-side data untuk DataTables
     * Query tr_jurnal grouped by no_transaksi dengan filter Petty Cash
     *
     * @param array $params DataTables POST params
     * @return array (draw, recordsTotal, recordsFiltered, data)
     */
    public function get_server_side_data($params)
    {
        $draw   = isset($params['draw']) ? $params['draw'] : 0;
        $length = isset($params['length']) ? $params['length'] : 10;
        $start  = isset($params['start']) ? $params['start'] : 0;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $order  = isset($params['order']) ? $params['order'] : [];

        $company = isset($params['company']) ? $params['company'] : '';

        // 1. Count Total Records (tanpa search)
        $this->_build_base_query($company);
        $sql_total = $this->db->get_compiled_select();
        $recordsTotal = $this->db->query("SELECT COUNT(*) AS num FROM ({$sql_total}) AS temp")->row()->num;

        // 2. Count Filtered Records (dengan search)
        $this->_build_base_query($company, $search);
        $sql_filtered = $this->db->get_compiled_select();
        $recordsFiltered = $this->db->query("SELECT COUNT(*) AS num FROM ({$sql_filtered}) AS temp")->row()->num;

        // 3. Get Data (dengan search + order + limit)
        $this->_build_base_query($company, $search);

        // Define sortable columns mapping
        $sort_columns = [
            0 => 'id',
            1 => 'tgl_jurnal',
            2 => 'no_transaksi',
            3 => 'nm_company',
            4 => 'total_debit',
            5 => 'total_kredit'
        ];

        if (!empty($order) && isset($sort_columns[$order[0]['column']])) {
            $this->db->order_by($sort_columns[$order[0]['column']], $order[0]['dir']);
        } else {
            $this->db->order_by('id', 'desc');
        }

        if ($length != -1) {
            $this->db->limit($length, $start);
        }

        $get_data = $this->db->get()->result();

        // 4. Format Data
        $hasil = [];
        $no = $start;
        foreach ($get_data as $item) {
            $no++;

            // Build action button
            $action = '<button type="button" class="btn btn-sm btn-info" onclick="view_detail(' . $item->id . ')"><i class="fa fa-eye"></i> View</button>';

            $hasil[] = [
                'no'            => $no,
                'id'            => $item->id,
                'tanggal'       => date('d F Y', strtotime($item->tgl_jurnal)),
                'no_transaksi'  => $item->no_transaksi,
                'company'       => $item->nm_company,
                'coa'           => $item->coa,
                'nm_coa'        => $item->nm_coa,
                'keterangan'    => $item->keterangan,
                'debit'         => $item->total_debit,
                'kredit'        => $item->total_kredit,
                'action'        => $action,
            ];
        }

        return [
            'draw'            => intval($draw),
            'recordsTotal'    => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data'            => $hasil
        ];
    }

    /**
     * Build base query untuk DataTables server-side
     * Grouped by no_transaksi, jenis_transaksi dengan aggregasi SUM
     *
     * @param string $company Filter company (id_company)
     * @param string $search  Search keyword
     */
    private function _build_base_query($company = '', $search = '')
    {
        $this->db->select('MAX(a.id) as id, a.tgl_jurnal, a.no_transaksi, a.nm_company, a.id_company, GROUP_CONCAT(DISTINCT a.coa SEPARATOR ", ") as coa, GROUP_CONCAT(DISTINCT a.nm_coa SEPARATOR ", ") as nm_coa, SUBSTRING_INDEX(GROUP_CONCAT(a.keterangan SEPARATOR ", "), ",", 1) as keterangan, SUM(a.debit) as total_debit, SUM(a.kredit) as total_kredit', FALSE);
        $this->db->from('tr_jurnal a');
        $this->db->where('a.jenis_transaksi', 'Petty Cash');
        $this->db->where_in('a.sts', ['', '0']);

        // Filter company
        if (!empty($company)) {
            $this->db->where('a.id_company', $company);
        }

        // Search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.no_transaksi', $search, 'both');
            $this->db->or_like('a.nm_company', $search, 'both');
            $this->db->or_like('a.coa', $search, 'both');
            $this->db->or_like('a.nm_coa', $search, 'both');
            $this->db->or_like('a.keterangan', $search, 'both');
            $this->db->group_end();
        }

        $this->db->group_by(['a.no_transaksi', 'a.jenis_transaksi']);
    }

    /**
     * Get all active rows for a transaction group
     * Filter hanya baris dengan debit > 0 OR kredit > 0
     *
     * @param string $no_transaksi
     * @param string $jenis_transaksi
     * @return array of objects
     */
    public function get_detail_by_transaksi($no_transaksi, $jenis_transaksi)
    {
        $this->db->select('id, tgl_jurnal, coa, nm_coa, keterangan, no_transaksi, id_company, nm_company, debit, kredit');
        $this->db->from('tr_jurnal');
        $this->db->where('no_transaksi', $no_transaksi);
        $this->db->where('jenis_transaksi', $jenis_transaksi);
        $this->db->where_in('sts', ['', '0']);
        $this->db->where('(debit > 0 OR kredit > 0)');
        $this->db->order_by('id', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Validate balance: check sum(debit) == sum(kredit)
     *
     * @param array $rows Array of jurnal rows
     * @return bool True jika balance
     */
    public function validate_balance($rows)
    {
        if (empty($rows) || !is_array($rows)) {
            return false;
        }

        $sum_debit  = 0;
        $sum_kredit = 0;

        foreach ($rows as $row) {
            if (is_object($row)) {
                $sum_debit  += (float) $row->debit;
                $sum_kredit += (float) $row->kredit;
            } else {
                $sum_debit  += (float) (isset($row['debit']) ? $row['debit'] : 0);
                $sum_kredit += (float) (isset($row['kredit']) ? $row['kredit'] : 0);
            }
        }

        // At least one side must be > 0 (reject empty journals)
        if ($sum_debit <= 0 && $sum_kredit <= 0) {
            return false;
        }

        // Use epsilon for floating point comparison
        return abs($sum_debit - $sum_kredit) < 0.01;
    }

    /**
     * Insert jurnal ke DBACC_STM (japh header + jurnal details)
     *
     * @param object $jurnal_header Header data (nomor, tgl, jml, etc.)
     * @param array  $jurnal_details Array of detail rows
     * @param string $nomor_buk Generated BUK number
     * @return bool
     */
    public function post_jurnal_stm($jurnal_header, $jurnal_details, $nomor_buk)
    {
        // 1. INSERT header into japh table in DBACC_STM
        $dataJVheader = [
            'nomor'      => $nomor_buk,
            'tgl'        => $jurnal_header->tgl,
            'jml'        => $jurnal_header->jml,
            'kdcab'      => '101',
            'jenis_reff' => 'BUK',
            'no_reff'    => $jurnal_header->no_reff,
            'user_id'    => $jurnal_header->user_id,
            'ho_valid'   => '',
            'batal'      => '0'
        ];

        $insert_header = $this->accounting_stm->insert('japh', $dataJVheader);

        if (!$insert_header) {
            return false;
        }

        // 2. Build detail rows, filter only rows with debit > 0 OR kredit > 0
        $batch_data = [];
        foreach ($jurnal_details as $row) {
            $debit  = (float) $row->debit;
            $kredit = (float) $row->kredit;

            if ($debit > 0 || $kredit > 0) {
                $batch_data[] = [
                    'tipe'         => 'BUK',
                    'nomor'        => $nomor_buk,
                    'tanggal'      => $row->tgl_jurnal,
                    'no_perkiraan' => $row->coa,
                    'keterangan'   => $row->keterangan,
                    'no_reff'      => $row->no_transaksi,
                    'debet'        => $debit,
                    'kredit'       => $kredit
                ];
            }
        }

        // 3. INSERT BATCH into jurnal table in DBACC_STM
        if (empty($batch_data)) {
            return false;
        }

        $insert_details = $this->accounting_stm->insert_batch('jurnal', $batch_data);

        if (!$insert_details) {
            return false;
        }

        return true;
    }

    /**
     * Insert jurnal inter-company ke DBACC_VUCA/SUSTAIN dan DBACC_STM
     *
     * @param string $company Company identifier (id_company)
     * @param object $jurnal_header Header data
     * @param array  $jurnal_details Array of detail rows
     * @param string $nomor_buk_company BUK number for company side
     * @param string $nomor_buk_stm BUK number for STM side
     * @return bool
     */
    public function post_jurnal_intercompany($company, $jurnal_header, $jurnal_details, $nomor_buk_company, $nomor_buk_stm)
    {
        // 1. Determine target database based on company
        if ($company == '4') {
            $target_db = $this->accounting_vuca;
        } elseif ($company == '6') {
            $target_db = $this->accounting_sustain;
        } else {
            return false;
        }

        // 2. Build batch detail rows and calculate total debit per company
        $batch_company = [];
        $batch_stm = [];
        $jml_company = 0;
        $jml_stm = 0;

        foreach ($jurnal_details as $row) {
            $debit  = (float) $row->debit;
            $kredit = (float) $row->kredit;

            if ($debit > 0 || $kredit > 0) {
                $detail = [
                    'tipe'         => 'BUK',
                    'tanggal'      => $row->tgl_jurnal,
                    'no_perkiraan' => $row->coa,
                    'keterangan'   => $row->keterangan,
                    'no_reff'      => $row->no_transaksi,
                    'debet'        => $debit,
                    'kredit'       => $kredit
                ];

                if ($row->id_company == $company) {
                    $detail['nomor'] = $nomor_buk_company;
                    $batch_company[] = $detail;
                    $jml_company += $debit;
                } elseif ($row->id_company == '5') {
                    $detail['nomor'] = $nomor_buk_stm;
                    $batch_stm[] = $detail;
                    $jml_stm += $debit;
                }
            }
        }

        if (empty($batch_company) && empty($batch_stm)) {
            return false;
        }

        // === Company side (VUCA or SUSTAIN) ===

        if (!empty($batch_company)) {
            // 3. INSERT japh header into target_db
            $dataJVheader_company = [
                'nomor'      => $nomor_buk_company,
                'tgl'        => $jurnal_header->tgl,
                'jml'        => $jml_company,
                'kdcab'      => '101',
                'jenis_reff' => 'BUK',
                'no_reff'    => $jurnal_header->no_reff,
                'user_id'    => $jurnal_header->user_id,
                'ho_valid'   => '',
                'batal'      => '0'
            ];

            $insert_header_company = $target_db->insert('japh', $dataJVheader_company);
            if (!$insert_header_company) return false;

            // 4. INSERT BATCH jurnal details into target_db
            $insert_details_company = $target_db->insert_batch('jurnal', $batch_company);
            if (!$insert_details_company) return false;
        }

        // === STM side ===

        if (!empty($batch_stm)) {
            // 5. INSERT japh header into DBACC_STM
            $dataJVheader_stm = [
                'nomor'      => $nomor_buk_stm,
                'tgl'        => $jurnal_header->tgl,
                'jml'        => $jml_stm,
                'kdcab'      => '101',
                'jenis_reff' => 'BUK',
                'no_reff'    => $jurnal_header->no_reff,
                'user_id'    => $jurnal_header->user_id,
                'ho_valid'   => '',
                'batal'      => '0'
            ];

            $insert_header_stm = $this->accounting_stm->insert('japh', $dataJVheader_stm);
            if (!$insert_header_stm) return false;

            // 6. INSERT BATCH jurnal details into DBACC_STM
            $insert_details_stm = $this->accounting_stm->insert_batch('jurnal', $batch_stm);
            if (!$insert_details_stm) return false;
        }

        return true;
    }

    /**
     * Insert refill entries ke target database
     *
     * @param object $jurnal_header Header data
     * @param array  $jurnal_details Array of detail rows
     * @param string $nomor_buk Generated BUK number
     * @param string $target_db Target database connection key
     * @return bool
     */
    public function post_jurnal_refill($jurnal_header, $jurnal_details, $nomor_buk, $target_db)
    {
        // 1. Resolve DB connection from $target_db string key
        switch ($target_db) {
            case 'accounting_stm':
                $db = $this->accounting_stm;
                break;
            case 'accounting_vuca':
                $db = $this->accounting_vuca;
                break;
            case 'accounting_sustain':
                $db = $this->accounting_sustain;
                break;
            default:
                return false;
        }

        // 2. INSERT japh header into resolved DB
        $dataJVheader = [
            'nomor'      => $nomor_buk,
            'tgl'        => $jurnal_header->tgl,
            'jml'        => $jurnal_header->jml,
            'kdcab'      => '101',
            'jenis_reff' => 'BUK',
            'no_reff'    => $jurnal_header->no_reff,
            'user_id'    => $jurnal_header->user_id,
            'ho_valid'   => '',
            'batal'      => '0'
        ];

        $insert_header = $db->insert('japh', $dataJVheader);

        if (!$insert_header) {
            return false;
        }

        // 3. Build detail rows, filter only rows with debit > 0 OR kredit > 0
        $batch_data = [];
        foreach ($jurnal_details as $row) {
            $debit  = (float) $row->debit;
            $kredit = (float) $row->kredit;

            if ($debit > 0 || $kredit > 0) {
                $batch_data[] = [
                    'tipe'         => 'BUK',
                    'nomor'        => $nomor_buk,
                    'tanggal'      => $row->tgl_jurnal,
                    'no_perkiraan' => $row->coa,
                    'keterangan'   => $row->keterangan,
                    'no_reff'      => $row->no_transaksi,
                    'debet'        => $debit,
                    'kredit'       => $kredit
                ];
            }
        }

        // 4. INSERT BATCH into jurnal table in resolved DB
        if (empty($batch_data)) {
            return false;
        }

        $insert_details = $db->insert_batch('jurnal', $batch_data);

        if (!$insert_details) {
            return false;
        }

        return true;
    }

    /**
     * Update status posted: set sts = '1' on staging table
     *
     * @param string $no_transaksi
     * @param string $jenis_transaksi
     * @return bool
     */
    public function update_status_posted($no_transaksi, $jenis_transaksi)
    {
        $this->db->where('no_transaksi', $no_transaksi);
        $this->db->where('jenis_transaksi', $jenis_transaksi);
        $this->db->update('tr_jurnal', ['sts' => '1']);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get posted transactions + saldo awal untuk laporan buku besar
     *
     * @param string $tgl_from Start date (Y-m-d)
     * @param string $tgl_to End date (Y-m-d)
     * @return array
     */
    public function get_buku_besar_data($tgl_from, $tgl_to)
    {
        $this->db->select('id, no_transaksi, tgl_jurnal, coa, nm_coa, id_company, nm_company, keterangan, debit, kredit, jenis_transaksi');
        $this->db->from('tr_jurnal');
        $this->db->where('jenis_transaksi', 'Petty Cash');
        $this->db->where('sts', '1');
        $this->db->where('tgl_jurnal >=', $tgl_from);
        $this->db->where('tgl_jurnal <=', $tgl_to);
        $this->db->order_by('tgl_jurnal', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Calculate opening balance (saldo awal) before a given date
     * SUM(debit) - SUM(kredit) dari tr_jurnal WHERE sts='1' AND tgl_jurnal < tgl_from
     *
     * @param string $tgl_from Start date (Y-m-d)
     * @return float
     */
    public function get_saldo_awal($tgl_from)
    {
        $this->db->select('COALESCE(SUM(debit), 0) - COALESCE(SUM(kredit), 0) as saldo_awal', FALSE);
        $this->db->from('tr_jurnal');
        $this->db->where('jenis_transaksi', 'Petty Cash');
        $this->db->where('sts', '1');
        $this->db->where('tgl_jurnal <', $tgl_from);
        $result = $this->db->get()->row();
        return $result ? (float) $result->saldo_awal : 0;
    }

    /**
     * Get distinct companies dari tr_jurnal untuk filter dropdown
     *
     * @return array
     */
    public function get_company_filter()
    {
        $this->db->distinct();
        $this->db->select('id_company, nm_company');
        $this->db->from('tr_jurnal');
        $this->db->where('jenis_transaksi', 'Petty Cash');
        $this->db->where_in('sts', ['', '0']);
        $this->db->order_by('nm_company', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Transaction Helpers for multi-database coordination
     */
    public function begin_transaction($db_name)
    {
        $db = $this->_get_db_by_name($db_name);
        if ($db) {
            $db->trans_begin();
        }
    }

    public function commit_transaction($db_name)
    {
        $db = $this->_get_db_by_name($db_name);
        if ($db) {
            $db->trans_commit();
        }
    }

    public function rollback_transaction($db_name)
    {
        $db = $this->_get_db_by_name($db_name);
        if ($db) {
            $db->trans_rollback();
        }
    }

    public function check_transaction_status($db_name)
    {
        $db = $this->_get_db_by_name($db_name);
        if ($db) {
            return $db->trans_status();
        }
        return false;
    }

    private function _get_db_by_name($db_name)
    {
        switch ($db_name) {
            case 'accounting_stm':
                return $this->accounting_stm;
            case 'accounting_vuca':
                return $this->accounting_vuca;
            case 'accounting_sustain':
                return $this->accounting_sustain;
            default:
                return null;
        }
    }
}
