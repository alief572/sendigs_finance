<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Expense Petty Cash Model
 *
 * Model untuk mengelola pencatatan pengeluaran kas kecil.
 * Handles CRUD operations, journal creation, sequential numbering, dan budget tracking.
 *
 * @author  Sendigs Finance
 * @copyright Copyright (c) 2024
 */

class Expense_petty_cash_model extends BF_Model
{
    // =========================================================================
    // Inter-Company COA Constants
    // =========================================================================
    const COA_KAS_KECIL          = '1101-01-02';
    const COA_HUTANG_STM_VUCA    = '2103-01-01';  // Hutang VUCA ke STM
    const COA_HUTANG_STM_SUSTAIN = '2103-01-02';  // Hutang SUSTAIN ke STM
    const COA_PIUTANG_VUCA       = '1103-01-01';  // Piutang STM ke VUCA
    const COA_PIUTANG_SUSTAIN    = '1103-01-02';  // Piutang STM ke SUSTAIN

    /**
     * @var string Table Name
     */
    protected $table_name = 'tr_expense_petty_cash';
    protected $key        = 'id';

    /**
     * @var string Field name to use for the created time column in the DB table
     */
    protected $created_field = 'created_on';

    /**
     * @var string Field name to use for the modified time column in the DB table
     */
    protected $modified_field = 'modified_on';

    /**
     * @var bool Set the created time automatically on a new record
     */
    protected $set_created = true;

    /**
     * @var bool Set the modified time automatically on editing a record
     */
    protected $set_modified = true;

    /**
     * @var string The type of date/time field used for created/modified fields
     */
    protected $date_format = 'datetime';

    /**
     * @var bool If true, will log user id in created_by/modified_by fields
     */
    protected $log_user = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // CRUD Operations
    // =========================================================================

    /**
     * Get server-side DataTables data for pencatatan list
     *
     * @param array $params DataTables request parameters
     * @return array
     */
    public function get_server_side_data($params)
    {
        $draw   = isset($params['draw']) ? intval($params['draw']) : 1;
        $start  = isset($params['start']) ? intval($params['start']) : 0;
        $length = isset($params['length']) ? intval($params['length']) : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $order_col = isset($params['order'][0]['column']) ? intval($params['order'][0]['column']) : 3;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        // Column index mapping (matching DataTables columns)
        // 0: checkbox (not sortable)
        // 1: row number (not sortable)
        // 2: no_pencatatan
        // 3: tanggal
        // 4: company
        // 5: request_by
        // 6: keterangan
        // 7: grand_total
        // 8: status
        // 9: action (not sortable)
        $columns = [
            0 => null,
            1 => null,
            2 => 'a.no_pencatatan',
            3 => 'a.tanggal',
            4 => 'a.company',
            5 => 'a.request_by',
            6 => 'a.keterangan',
            7 => 'a.grand_total',
            8 => 'a.status',
            9 => null,
        ];

        $order_by = isset($columns[$order_col]) && $columns[$order_col] !== null
            ? $columns[$order_col]
            : 'a.tanggal';
        $order_dir = ($order_dir === 'asc') ? 'asc' : 'desc';

        // Count total records
        $this->db->from($this->table_name . ' a');
        $records_total = $this->db->count_all_results();

        // Build search condition
        // Count filtered records
        $this->db->from($this->table_name . ' a');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.no_pencatatan', $search);
            $this->db->or_like('a.company', $search);
            $this->db->or_like('a.request_by', $search);
            $this->db->or_like('a.keterangan', $search);
            $this->db->group_end();
        }
        $records_filtered = $this->db->count_all_results();

        // Get paginated data with LEFT JOIN to check if pencatatan is linked in a pelaporan
        $this->db->select('a.id, a.no_pencatatan, a.tanggal, a.company, a.request_by, a.keterangan, a.grand_total, a.status, a.journal_status');
        $this->db->select('(SELECT COUNT(*) FROM tr_pelaporan_petty_cash_detail pd 
            JOIN tr_pelaporan_petty_cash p ON p.id = pd.pelaporan_id 
            WHERE pd.pencatatan_id = a.id 
            AND p.status IN ("draft","waiting","approved")
        ) as in_pelaporan', false);
        $this->db->from($this->table_name . ' a');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.no_pencatatan', $search);
            $this->db->or_like('a.company', $search);
            $this->db->or_like('a.request_by', $search);
            $this->db->or_like('a.keterangan', $search);
            $this->db->group_end();
        }
        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $results = $query->result();

        // Format data
        $data = [];
        $no = $start + 1;
        foreach ($results as $row) {
            $data[] = [
                'no'             => $no++,
                'id'             => $row->id,
                'no_pencatatan'  => $row->no_pencatatan,
                'tanggal'        => $row->tanggal,
                'company'        => $row->company,
                'request_by'     => $row->request_by,
                'keterangan'     => $row->keterangan,
                'grand_total'    => number_format($row->grand_total, 0, ',', '.'),
                'status'         => $row->status,
                'journal_status' => $row->journal_status,
                'in_pelaporan'   => intval($row->in_pelaporan) > 0,
            ];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
            'data'            => $data,
        ];
    }

    /**
     * Get single pencatatan record with details and evidences
     *
     * @param int $id Pencatatan ID
     * @return object|false
     */
    public function get_pencatatan($id)
    {
        // Get header
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return false;
        }

        $header = $query->row();

        // Get detail items ordered by sort_order
        $this->db->select('d.*');
        $this->db->from('tr_expense_petty_cash_detail d');
        $this->db->where('d.pencatatan_id', $id);
        $this->db->order_by('d.sort_order', 'asc');
        $query_details = $this->db->get();
        $details = $query_details->result();

        // Get evidences grouped by detail_id
        $this->db->select('e.*');
        $this->db->from('tr_expense_petty_cash_evidence e');
        $this->db->join('tr_expense_petty_cash_detail d', 'd.id = e.detail_id', 'inner');
        $this->db->where('d.pencatatan_id', $id);
        $this->db->order_by('e.id', 'asc');
        $query_evidences = $this->db->get();
        $evidences_raw = $query_evidences->result();

        // Group evidences by detail_id
        $evidences = [];
        foreach ($evidences_raw as $ev) {
            $evidences[$ev->detail_id][] = $ev;
        }

        // Compose result object
        $result = new stdClass();
        $result->header    = $header;
        $result->details   = $details;
        $result->evidences = $evidences;

        return $result;
    }

    /**
     * Save pencatatan (create or update) with details and evidences
     *
     * Handles both create (new draft) and update (existing record) in a single
     * database transaction. Calculates grand_total from detail items, inserts
     * header + details + evidences atomically. Rolls back on any failure.
     *
     * Note: Journal creation is handled separately (task 4.6) and is NOT part
     * of this transaction due to cross-database eventual consistency strategy.
     *
     * @param array $header Header data (tanggal, company, request_by, keterangan, petty_cash_id)
     * @param array $details Array of detail items, each with: coa_code, pengeluaran, spesifikasi, jumlah, nominal
     * @param array $evidences Array of evidence data per detail (keyed by row index),
     *                         each containing arrays with: original_name, encrypted_name, file_type, file_size
     * @param int|null $id If provided, update existing record; otherwise create new
     * @return int|false Insert ID (on create), true (on update), or false on failure
     */
    public function save_pencatatan($header, $details, $evidences, $id = null)
    {
        // Calculate grand_total = SUM(jumlah × nominal) for all detail items
        $grand_total = 0;
        foreach ($details as $item) {
            $jumlah  = (int) $item['jumlah'];
            $nominal = (int) $item['nominal'];
            $grand_total += ($jumlah * $nominal);
        }

        // Begin transaction
        $this->db->trans_start();

        if ($id === null) {
            // === CREATE NEW PENCATATAN ===
            $header['status']         = 'draft';
            $header['journal_status'] = 'pending';
            $header['grand_total']    = $grand_total;
            $header['created_on']     = date('Y-m-d H:i:s');
            $header['created_by']     = $this->auth->user_id();

            $this->db->insert($this->table_name, $header);
            $pencatatan_id = $this->db->insert_id();
        } else {
            // === UPDATE EXISTING PENCATATAN ===
            $pencatatan_id = $id;

            $header['grand_total']  = $grand_total;
            $header['modified_on']  = date('Y-m-d H:i:s');
            $header['modified_by']  = $this->auth->user_id();

            $this->db->where('id', $pencatatan_id);
            $this->db->update($this->table_name, $header);

            // Get existing detail IDs for evidence cleanup
            $existing_details = $this->db->select('id')
                ->from('tr_expense_petty_cash_detail')
                ->where('pencatatan_id', $pencatatan_id)
                ->get()
                ->result();

            $existing_detail_ids = array_map(function ($d) {
                return $d->id;
            }, $existing_details);

            // Delete existing evidence records for this pencatatan's details
            if (!empty($existing_detail_ids)) {
                $this->db->where_in('detail_id', $existing_detail_ids);
                $this->db->delete('tr_expense_petty_cash_evidence');
            }

            // Delete existing detail items
            $this->db->where('pencatatan_id', $pencatatan_id);
            $this->db->delete('tr_expense_petty_cash_detail');
        }

        // Insert all detail items with calculated total and sort_order
        $sort_order = 1;
        $detail_id_map = []; // Maps row index to inserted detail_id

        foreach ($details as $index => $item) {
            $jumlah  = (int) $item['jumlah'];
            $nominal = (int) $item['nominal'];
            $total   = $jumlah * $nominal;

            $detail_data = [
                'pencatatan_id' => $pencatatan_id,
                'coa_code'      => $item['coa_code'],
                'pengeluaran'   => $item['pengeluaran'],
                'spesifikasi'   => isset($item['spesifikasi']) ? $item['spesifikasi'] : null,
                'jumlah'        => $jumlah,
                'nominal'       => $nominal,
                'total'         => $total,
                'sort_order'    => $sort_order,
            ];

            $this->db->insert('tr_expense_petty_cash_detail', $detail_data);
            $detail_id_map[$index] = $this->db->insert_id();
            $sort_order++;
        }

        // Insert evidence records linked to detail_id
        foreach ($evidences as $row_index => $evidence_files) {
            if (!isset($detail_id_map[$row_index]) || empty($evidence_files)) {
                continue;
            }

            $detail_id = $detail_id_map[$row_index];

            foreach ($evidence_files as $file) {
                $evidence_data = [
                    'detail_id'      => $detail_id,
                    'original_name'  => $file['original_name'],
                    'encrypted_name' => $file['encrypted_name'],
                    'file_type'      => $file['file_type'],
                    'file_size'      => $file['file_size'],
                    'uploaded_on'    => date('Y-m-d H:i:s'),
                ];

                $this->db->insert('tr_expense_petty_cash_evidence', $evidence_data);
            }
        }

        // Complete transaction
        $this->db->trans_complete();

        // Check transaction status
        if ($this->db->trans_status() === false) {
            return false;
        }

        // Return insert_id for create, true for update
        return ($id === null) ? $pencatatan_id : true;
    }

    /**
     * Delete pencatatan and related details, evidences, and journal entries
     *
     * Validates status (must be draft/reject) and checks pencatatan is not linked
     * to any active pelaporan before proceeding with cascade delete.
     * Physical evidence files are removed AFTER successful DB transaction.
     *
     * @param int $id Pencatatan ID
     * @return array [status => 0|1, msg => string]
     */
    public function delete_pencatatan($id)
    {
        // Step 1: Get the pencatatan record
        $this->db->select('id, no_pencatatan, status');
        $this->db->from($this->table_name);
        $this->db->where('id', $id);
        $pencatatan = $this->db->get()->row();

        if (!$pencatatan) {
            return ['status' => 0, 'msg' => 'Data pencatatan tidak ditemukan.'];
        }

        // Step 2: Validate status must be 'draft' or 'reject'
        if (!in_array($pencatatan->status, ['draft', 'reject'])) {
            return ['status' => 0, 'msg' => 'Pencatatan dengan status "' . $pencatatan->status . '" tidak dapat dihapus.'];
        }

        // Step 3: Check if pencatatan is linked to any active pelaporan
        $this->db->select('p.no_pelaporan');
        $this->db->from('tr_pelaporan_petty_cash_detail pd');
        $this->db->join('tr_pelaporan_petty_cash p', 'p.id = pd.pelaporan_id', 'inner');
        $this->db->where('pd.pencatatan_id', $id);
        $this->db->where_in('p.status', ['draft', 'waiting', 'approved']);
        $pelaporan_check = $this->db->get()->row();

        if ($pelaporan_check) {
            return ['status' => 0, 'msg' => 'Pencatatan tidak dapat dihapus karena sudah masuk dalam pelaporan.'];
        }

        // Step 4: Collect evidence files before deletion (for physical file cleanup)
        $this->db->select('e.encrypted_name');
        $this->db->from('tr_expense_petty_cash_evidence e');
        $this->db->join('tr_expense_petty_cash_detail d', 'd.id = e.detail_id', 'inner');
        $this->db->where('d.pencatatan_id', $id);
        $evidence_files = $this->db->get()->result();

        // Step 5: Begin transaction for cascade delete
        $this->db->trans_begin();

        // Step 5a: Get detail IDs for this pencatatan
        $detail_ids = $this->db->select('id')
            ->from('tr_expense_petty_cash_detail')
            ->where('pencatatan_id', $id)
            ->get()
            ->result();

        $detail_id_array = array_map(function ($d) {
            return $d->id;
        }, $detail_ids);

        // Step 5b: Delete evidence records
        if (!empty($detail_id_array)) {
            $this->db->where_in('detail_id', $detail_id_array);
            $this->db->delete('tr_expense_petty_cash_evidence');
        }

        // Step 5c: Delete detail records
        $this->db->where('pencatatan_id', $id);
        $this->db->delete('tr_expense_petty_cash_detail');

        // Step 5d: Delete journal entries from DBACC
        $this->delete_journal($pencatatan->no_pencatatan);

        // Step 5e: Delete header record
        $this->db->where('id', $id);
        $this->db->delete($this->table_name);

        // Step 6: Check transaction status
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => 0, 'msg' => 'Gagal menghapus data. Silakan coba lagi.'];
        }

        $this->db->trans_commit();

        // Step 7: Delete physical evidence files AFTER successful DB commit
        if (!empty($evidence_files)) {
            $upload_path = FCPATH . 'assets/expense_petty_cash/';
            foreach ($evidence_files as $file) {
                $file_path = $upload_path . $file->encrypted_name;
                if (file_exists($file_path)) {
                    if (!@unlink($file_path)) {
                        log_message('error', 'Failed to delete evidence file: ' . $file_path);
                    }
                }
            }
        }

        return ['status' => 1, 'msg' => 'Data pencatatan berhasil dihapus.'];
    }

    // =========================================================================
    // Journal Operations
    // =========================================================================

    /**
     * Create journal entries for STM company pencatatan
     * Debit: COA items, Credit: COA 1101-01-02 (Kas Kecil)
     *
     * Logic:
     * 1. Calculate grand_total = SUM of all detail totals
     * 2. Prepare N debit entries (one per detail item)
     * 3. Prepare 1 credit entry on COA 1101-01-02
     * 4. Validate SUM(debit) = SUM(kredit) = grand_total
     * 5. Generate nomor jurnal from pastibisa_tb_cabang sequence
     * 6. Insert all entries into jurnal table in DBACC
     * 7. Update journal_status on pencatatan record
     *
     * @param int $pencatatan_id
     * @param array $header Pencatatan header data (no_pencatatan, tanggal, company, keterangan)
     * @param array $details Pencatatan detail items (each with: coa_code, pengeluaran, total)
     * @return bool
     */
    public function create_journal_stm($pencatatan_id, $header, $details)
    {
        // Step 1: Calculate grand_total from detail items
        $grand_total = 0;
        foreach ($details as $item) {
            $grand_total += (int) $item['total'];
        }

        // Guard: no entries to create if grand_total is zero
        if ($grand_total <= 0) {
            return false;
        }

        // Step 2: Validate balance
        $sum_debit = 0;
        foreach ($details as $item) {
            $sum_debit += (int) $item['total'];
        }
        if ($sum_debit !== $grand_total) {
            return false;
        }

        // Step 3: Generate no_jurnal (format: NNNNN-AJV-{MM}-{YY})
        $tanggal = $header['tanggal'];
        $month_roman = $this->_get_roman_month(date('n', strtotime($tanggal)));
        $year_short = date('y', strtotime($tanggal));

        // Get next sequence from existing tr_jurnal for this month/year
        $prefix_like = '%-AJV-' . $month_roman . '-' . $year_short;
        $query_last = $this->db->query(
            "SELECT no_jurnal FROM tr_jurnal WHERE no_jurnal LIKE ? ORDER BY id DESC LIMIT 1",
            ['%' . $prefix_like]
        );

        if ($query_last->num_rows() > 0) {
            $last_no = $query_last->row()->no_jurnal;
            $last_seq = (int) substr($last_no, 0, 5);
            $next_seq = $last_seq + 1;
        } else {
            $next_seq = 1;
        }
        $no_jurnal = str_pad($next_seq, 5, '0', STR_PAD_LEFT) . '-AJV-' . $month_roman . '-' . $year_short;

        // Step 4: Determine company info
        $id_company = '5'; // STM
        $nm_company = 'STM';

        // Step 5: Get COA names from coa_master
        $coa_names = [];
        foreach ($details as $item) {
            $coa_code = $item['coa_code'];
            if (!isset($coa_names[$coa_code])) {
                $coa_q = $this->db->query(
                    "SELECT nama FROM " . DBACC_STM . ".coa_master WHERE no_perkiraan = ?",
                    [$coa_code]
                );
                $coa_names[$coa_code] = ($coa_q->num_rows() > 0) ? $coa_q->row()->nama : $coa_code;
            }
        }
        // COA Kas Kecil name
        $coa_kas_kecil = '1101-01-02';
        $nm_kas_kecil = 'Kas Kecil';

        // Step 6: Insert debit entries into tr_jurnal (one per detail item)
        $created_by = $this->auth->user_id();
        $created_date = date('Y-m-d H:i:s');

        foreach ($details as $item) {
            $this->db->insert('tr_jurnal', [
                'no_jurnal'        => $no_jurnal,
                'tgl_jurnal'       => $tanggal,
                'coa'              => $item['coa_code'],
                'id_company'       => $id_company,
                'nm_company'       => $nm_company,
                'nm_coa'           => $coa_names[$item['coa_code']],
                'debit'            => (int) $item['total'],
                'kredit'           => 0,
                'keterangan'       => $item['pengeluaran'] ?: ($header['keterangan'] ?: 'Pengeluaran Kas Kecil'),
                'sts'              => '0', // belum diposting
                'no_transaksi'     => $header['no_pencatatan'],
                'jenis_transaksi'  => 'Petty Cash',
                'created_by'       => $created_by,
                'created_date'     => $created_date,
            ]);
        }

        // Step 7: Insert 1 credit entry (COA Kas Kecil)
        $this->db->insert('tr_jurnal', [
            'no_jurnal'        => $no_jurnal,
            'tgl_jurnal'       => $tanggal,
            'coa'              => $coa_kas_kecil,
            'id_company'       => $id_company,
            'nm_company'       => $nm_company,
            'nm_coa'           => $nm_kas_kecil,
            'debit'            => 0,
            'kredit'           => $grand_total,
            'keterangan'       => $header['keterangan'] ?: 'Pengeluaran Kas Kecil',
            'sts'              => '0',
            'no_transaksi'     => $header['no_pencatatan'],
            'jenis_transaksi'  => 'Petty Cash',
            'created_by'       => $created_by,
            'created_date'     => $created_date,
        ]);

        // Step 8: Update journal_status
        $this->_update_journal_status($pencatatan_id, 'success');
        return true;
    }

    /**
     * Get Roman numeral for month number
     * @param int $month 1-12
     * @return string Roman numeral
     */
    private function _get_roman_month($month)
    {
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return isset($romans[$month]) ? $romans[$month] : '';
    }

    /**
     * Update journal_status field on pencatatan record
     *
     * @param int $pencatatan_id
     * @param string $status 'pending', 'success', or 'failed'
     * @return void
     */
    private function _update_journal_status($pencatatan_id, $status)
    {
        $this->db->where('id', $pencatatan_id);
        $this->db->update($this->table_name, ['journal_status' => $status]);
    }

    /**
     * Create inter-company journal entries for VUCA/SUSTAIN pencatatan
     * Company-side: Debit COA items, Credit COA Hutang ke STM
     * STM-side: Debit COA Piutang, Credit COA 1101-01-02
     *
     * Generates two independent journal sets in a single DBACC transaction:
     * - Set 1 (Company side): N debit entries + 1 credit entry, balance = grand_total
     * - Set 2 (STM side): 1 debit entry + 1 credit entry, balance = grand_total
     * Each set has a different nomor jurnal.
     *
     * @param int $pencatatan_id
     * @param array $header Pencatatan header data (no_pencatatan, tanggal, company, keterangan)
     * @param array $details Pencatatan detail items, each with: coa_code, pengeluaran, total
     * @return bool
     */
    public function create_journal_intercompany($pencatatan_id, $header, $details)
    {
        // Extract header values
        $no_pencatatan = is_array($header) ? $header['no_pencatatan'] : $header->no_pencatatan;
        $tanggal       = is_array($header) ? $header['tanggal'] : $header->tanggal;
        $company       = is_array($header) ? $header['company'] : $header->company;
        $keterangan    = is_array($header) ? (isset($header['keterangan']) ? $header['keterangan'] : '') : (isset($header->keterangan) ? $header->keterangan : '');

        // Step 1: Calculate grand_total
        $grand_total = 0;
        foreach ($details as $item) {
            $total = is_array($item) ? (int) $item['total'] : (int) $item->total;
            $grand_total += $total;
        }

        if ($grand_total <= 0) {
            return false;
        }

        // Step 2: Determine company info and COA codes
        $company_upper = strtoupper($company);
        if ($company_upper === 'VUCA') {
            $coa_hutang_stm = self::COA_HUTANG_STM_VUCA;
            $coa_piutang    = self::COA_PIUTANG_VUCA;
            $id_company     = '4';
            $nm_company     = 'VUCA';
            $db_company     = DBACC_VUCA;
        } elseif ($company_upper === 'SUSTAIN') {
            $coa_hutang_stm = self::COA_HUTANG_STM_SUSTAIN;
            $coa_piutang    = self::COA_PIUTANG_SUSTAIN;
            $id_company     = '6';
            $nm_company     = 'SUSTAIN';
            $db_company     = DBACC_SUSTAIN;
        } else {
            return false;
        }

        // Step 3: Generate no_jurnal
        $month_roman = $this->_get_roman_month(date('n', strtotime($tanggal)));
        $year_short = date('y', strtotime($tanggal));
        $prefix_like = '%-AJV-' . $month_roman . '-' . $year_short;

        $query_last = $this->db->query(
            "SELECT no_jurnal FROM tr_jurnal WHERE no_jurnal LIKE ? ORDER BY id DESC LIMIT 1",
            ['%' . $prefix_like]
        );

        if ($query_last->num_rows() > 0) {
            $last_no = $query_last->row()->no_jurnal;
            $last_seq = (int) substr($last_no, 0, 5);
            $next_seq = $last_seq + 1;
        } else {
            $next_seq = 1;
        }
        $no_jurnal_company = str_pad($next_seq, 5, '0', STR_PAD_LEFT) . '-AJV-' . $month_roman . '-' . $year_short;
        $no_jurnal_stm = str_pad($next_seq + 1, 5, '0', STR_PAD_LEFT) . '-AJV-' . $month_roman . '-' . $year_short;

        // Step 4: Get COA names
        $coa_names = [];
        foreach ($details as $item) {
            $coa_code = is_array($item) ? $item['coa_code'] : $item->coa_code;
            if (!isset($coa_names[$coa_code])) {
                $coa_q = $this->db->query(
                    "SELECT nama FROM " . $db_company . ".coa_master WHERE no_perkiraan = ?",
                    [$coa_code]
                );
                $coa_names[$coa_code] = ($coa_q->num_rows() > 0) ? $coa_q->row()->nama : $coa_code;
            }
        }

        // Hutang COA name
        $hutang_q = $this->db->query("SELECT nama FROM " . $db_company . ".coa_master WHERE no_perkiraan = ?", [$coa_hutang_stm]);
        $nm_hutang = ($hutang_q->num_rows() > 0) ? $hutang_q->row()->nama : 'Hutang ke STM';

        // Piutang COA name
        $piutang_q = $this->db->query("SELECT nama FROM " . DBACC_STM . ".coa_master WHERE no_perkiraan = ?", [$coa_piutang]);
        $nm_piutang = ($piutang_q->num_rows() > 0) ? $piutang_q->row()->nama : 'Piutang ' . $nm_company;

        $created_by = $this->auth->user_id();
        $created_date = date('Y-m-d H:i:s');

        // =====================================================================
        // SET 1: Company side (VUCA/SUSTAIN) — N debit + 1 kredit Hutang ke STM
        // =====================================================================
        foreach ($details as $item) {
            $coa_code = is_array($item) ? $item['coa_code'] : $item->coa_code;
            $total    = is_array($item) ? (int) $item['total'] : (int) $item->total;

            $this->db->insert('tr_jurnal', [
                'no_jurnal'        => $no_jurnal_company,
                'tgl_jurnal'       => $tanggal,
                'coa'              => $coa_code,
                'id_company'       => $id_company,
                'nm_company'       => $nm_company,
                'nm_coa'           => $coa_names[$coa_code],
                'debit'            => $total,
                'kredit'           => 0,
                'keterangan'       => $keterangan ?: 'Pengeluaran Kas Kecil',
                'sts'              => '0',
                'no_transaksi'     => $no_pencatatan,
                'jenis_transaksi'  => 'Petty Cash',
                'created_by'       => $created_by,
                'created_date'     => $created_date,
            ]);
        }

        // Credit: Hutang ke STM
        $this->db->insert('tr_jurnal', [
            'no_jurnal'        => $no_jurnal_company,
            'tgl_jurnal'       => $tanggal,
            'coa'              => $coa_hutang_stm,
            'id_company'       => $id_company,
            'nm_company'       => $nm_company,
            'nm_coa'           => $nm_hutang,
            'debit'            => 0,
            'kredit'           => $grand_total,
            'keterangan'       => $keterangan ?: 'Pengeluaran Kas Kecil',
            'sts'              => '0',
            'no_transaksi'     => $no_pencatatan,
            'jenis_transaksi'  => 'Petty Cash',
            'created_by'       => $created_by,
            'created_date'     => $created_date,
        ]);

        // =====================================================================
        // SET 2: STM side — 1 debit Piutang + 1 kredit Kas Kecil
        // =====================================================================
        $this->db->insert('tr_jurnal', [
            'no_jurnal'        => $no_jurnal_stm,
            'tgl_jurnal'       => $tanggal,
            'coa'              => $coa_piutang,
            'id_company'       => '5',
            'nm_company'       => 'STM',
            'nm_coa'           => $nm_piutang,
            'debit'            => $grand_total,
            'kredit'           => 0,
            'keterangan'       => $keterangan ?: 'Piutang ' . $nm_company,
            'sts'              => '0',
            'no_transaksi'     => $no_pencatatan,
            'jenis_transaksi'  => 'Petty Cash',
            'created_by'       => $created_by,
            'created_date'     => $created_date,
        ]);

        $this->db->insert('tr_jurnal', [
            'no_jurnal'        => $no_jurnal_stm,
            'tgl_jurnal'       => $tanggal,
            'coa'              => self::COA_KAS_KECIL,
            'id_company'       => '5',
            'nm_company'       => 'STM',
            'nm_coa'           => 'Kas Kecil',
            'debit'            => 0,
            'kredit'           => $grand_total,
            'keterangan'       => $keterangan ?: 'Pengeluaran Kas Kecil - ' . $nm_company,
            'sts'              => '0',
            'no_transaksi'     => $no_pencatatan,
            'jenis_transaksi'  => 'Petty Cash',
            'created_by'       => $created_by,
            'created_date'     => $created_date,
        ]);

        return true;
    }

    /**
     * Generate nomor jurnal JV from pastibisa_tb_cabang sequence
     *
     * Format: {nocab}BK{subcab}{yy}{padded_counter}
     * Example: 101BKA2500001
     *
     * @param string $db_name Database name constant (DBACC_STM, DBACC_VUCA, DBACC_SUSTAIN)
     * @return string|false Generated nomor jurnal or false on failure
     */
    protected function generate_nomor_jurnal_jv($db_name)
    {
        $cabang = '101';
        $query = $this->db->query(
            "SELECT subcab, nomorJC FROM " . $db_name . ".pastibisa_tb_cabang WHERE nocab = ?",
            [$cabang]
        );

        if ($query->num_rows() === 0) {
            return false;
        }

        $row    = $query->row();
        $subcab = $row->subcab;
        $urut   = intval($row->nomorJC) + 1;

        $format = $cabang . '-' . $subcab . 'JV' . date('y');
        $nomor  = $format . str_pad($urut, 5, '0', STR_PAD_LEFT);

        return $nomor;
    }

    /**
     * Delete ALL journal entries by no_pencatatan (no_reff in jurnal table)
     *
     * Deletes from all relevant DBACC databases:
     * - DBACC_STM: Always (STM direct journals OR STM-side of inter-company)
     * - DBACC_VUCA: For VUCA inter-company journals
     * - DBACC_SUSTAIN: For SUSTAIN inter-company journals
     *
     * Since no_reff (No Pencatatan) uniquely identifies a pencatatan across all databases,
     * it is safe to delete from all three — only the relevant ones will have matching rows.
     *
     * Used during:
     * - Edit pencatatan (delete old journals, then create new ones)
     * - Delete pencatatan (remove all associated journals)
     *
     * @param string $no_pencatatan The No Pencatatan value used as no_reff in jurnal
     * @return bool True if delete operations completed without error
     */
    public function delete_journal($no_pencatatan)
    {
        // Delete from tr_jurnal (staging table) where no_transaksi matches
        $this->db->where('no_transaksi', $no_pencatatan);
        $this->db->where('jenis_transaksi', 'Petty Cash');
        $this->db->delete('tr_jurnal');

        return true;
    }

    /**
     * Create journal for a pencatatan record (eventual consistency Phase 2)
     *
     * This method is called AFTER save_pencatatan() has committed successfully.
     * It implements the eventual consistency pattern:
     * - Phase 1 (already done): Pencatatan saved with journal_status='pending'
     * - Phase 2 (this method): Create journal in DBACC, update status accordingly
     *
     * For edits ($is_edit = true): deletes old journal entries before creating new ones.
     * For company STM: calls create_journal_stm()
     * For company VUCA/SUSTAIN: calls create_journal_intercompany()
     *
     * @param int $pencatatan_id The pencatatan ID to create journal for
     * @param bool $is_edit If true, delete existing journal entries before creating new ones
     * @return array ['success' => bool, 'message' => string]
     */
    public function create_journal_for_pencatatan($pencatatan_id, $is_edit = false)
    {
        // Step 1: Get the pencatatan record with details
        $pencatatan = $this->get_pencatatan($pencatatan_id);

        if (!$pencatatan || !$pencatatan->header) {
            $this->_update_journal_status($pencatatan_id, 'failed');
            return ['success' => false, 'message' => 'Data pencatatan tidak ditemukan.'];
        }

        $header  = $pencatatan->header;
        $details = $pencatatan->details;

        // Step 2: If editing, delete old journal entries first
        if ($is_edit) {
            $this->delete_journal($header->no_pencatatan);
        }

        // Step 3: Set journal_status to 'pending' before attempting
        $this->_update_journal_status($pencatatan_id, 'pending');

        // Step 4: Prepare header and details as arrays for journal methods
        $header_data = [
            'no_pencatatan' => $header->no_pencatatan,
            'tanggal'       => $header->tanggal,
            'company'       => $header->company,
            'keterangan'    => $header->keterangan ?: 'Pengeluaran Kas Kecil',
        ];

        $details_data = [];
        foreach ($details as $item) {
            $details_data[] = [
                'coa_code'    => $item->coa_code,
                'pengeluaran' => $item->pengeluaran,
                'total'       => $item->total,
            ];
        }

        // Step 5: Dispatch to appropriate journal creation method based on company
        $company = strtoupper($header->company);
        $journal_result = false;

        try {
            if ($company === 'STM') {
                $journal_result = $this->create_journal_stm($pencatatan_id, $header_data, $details_data);
            } elseif ($company === 'VUCA' || $company === 'SUSTAIN') {
                $journal_result = $this->create_journal_intercompany($pencatatan_id, $header_data, $details_data);
                // Update journal_status for intercompany (create_journal_intercompany doesn't do it internally)
                if ($journal_result) {
                    $this->_update_journal_status($pencatatan_id, 'success');
                } else {
                    $this->_update_journal_status($pencatatan_id, 'failed');
                }
            } else {
                $this->_update_journal_status($pencatatan_id, 'failed');
                return ['success' => false, 'message' => 'Company tidak valid untuk pembuatan jurnal.'];
            }
        } catch (Exception $e) {
            $this->_update_journal_status($pencatatan_id, 'failed');
            log_message('error', 'Journal creation failed for pencatatan #' . $pencatatan_id . ': ' . $e->getMessage());
            return ['success' => false, 'message' => 'Pencatatan berhasil disimpan, namun jurnal gagal disinkronisasi. Dapat di-retry.'];
        }

        // Step 6: Return result
        if ($journal_result) {
            return ['success' => true, 'message' => 'Pencatatan dan jurnal berhasil disimpan.'];
        } else {
            return ['success' => false, 'message' => 'Pencatatan berhasil disimpan, namun jurnal gagal disinkronisasi. Dapat di-retry.'];
        }
    }

    /**
     * Retry journal creation for pencatatan with journal_status='failed'
     *
     * Steps:
     * 1. Get the pencatatan record by ID
     * 2. Validate journal_status is 'failed' (only retry failed ones)
     * 3. Get all detail items for this pencatatan
     * 4. Delete any partial journal entries that may exist
     * 5. Re-attempt journal creation based on company (STM vs VUCA/SUSTAIN)
     * 6. Journal methods already update journal_status to 'success' or 'failed'
     *
     * @param int $pencatatan_id
     * @return bool True if journal creation succeeded, false otherwise
     */
    public function retry_journal($pencatatan_id)
    {
        // Step 1: Get the pencatatan record
        $this->db->select('*');
        $this->db->from($this->table_name);
        $this->db->where('id', $pencatatan_id);
        $pencatatan = $this->db->get()->row();

        if (!$pencatatan) {
            return false;
        }

        // Step 2: Validate journal_status is 'failed'
        if ($pencatatan->journal_status !== 'failed') {
            return false;
        }

        // Step 3: Get all detail items for this pencatatan
        $this->db->select('*');
        $this->db->from('tr_expense_petty_cash_detail');
        $this->db->where('pencatatan_id', $pencatatan_id);
        $this->db->order_by('sort_order', 'asc');
        $details_result = $this->db->get()->result();

        if (empty($details_result)) {
            return false;
        }

        // Step 4: Delete any partial journal entries that may exist
        $this->delete_journal($pencatatan->no_pencatatan);

        // Step 5: Prepare header and details arrays for journal creation
        $header = [
            'no_pencatatan' => $pencatatan->no_pencatatan,
            'tanggal'       => $pencatatan->tanggal,
            'company'       => $pencatatan->company,
            'keterangan'    => $pencatatan->keterangan,
        ];

        $details = [];
        foreach ($details_result as $item) {
            $details[] = [
                'coa_code'    => $item->coa_code,
                'pengeluaran' => $item->pengeluaran,
                'total'       => $item->total,
            ];
        }

        // Step 6: Re-attempt journal creation based on company
        $company_upper = strtoupper($pencatatan->company);

        if ($company_upper === 'STM') {
            $result = $this->create_journal_stm($pencatatan_id, $header, $details);
        } elseif (in_array($company_upper, ['VUCA', 'SUSTAIN'])) {
            $result = $this->create_journal_intercompany($pencatatan_id, $header, $details);
            // Update journal_status since create_journal_intercompany doesn't do it internally
            if ($result) {
                $this->_update_journal_status($pencatatan_id, 'success');
            } else {
                $this->_update_journal_status($pencatatan_id, 'failed');
            }
        } else {
            // Unknown company
            return false;
        }

        return $result;
    }

    // =========================================================================
    // Sequential Number
    // =========================================================================

    /**
     * Generate sequential no_pencatatan for a given year
     * Format: PCP-YYYY-NNNN (e.g., PCP-2024-0001)
     *
     * Uses SELECT FOR UPDATE to ensure atomic number generation under concurrent access.
     * If no $year is provided, defaults to current year in Asia/Bangkok timezone.
     *
     * @param int|string|null $year 4-digit year (optional, defaults to current year Asia/Bangkok)
     * @return string|false Generated number or false if counter exceeds 9999
     */
    public function generate_no_pencatatan($year = null)
    {
        if ($year === null) {
            $tz = new DateTimeZone('Asia/Bangkok');
            $now = new DateTime('now', $tz);
            $year = $now->format('Y');
        }

        $year = (string) $year;
        $prefix = 'PCP-' . $year . '-';

        // Use raw query with FOR UPDATE to lock relevant rows and prevent race conditions
        $query = $this->db->query(
            "SELECT no_pencatatan FROM {$this->table_name} "
                . "WHERE no_pencatatan LIKE ? "
                . "ORDER BY no_pencatatan DESC LIMIT 1 FOR UPDATE",
            [$prefix . '%']
        );

        if ($query->num_rows() > 0) {
            $row = $query->row();
            // Extract the numeric part (last 4 characters)
            $last_number = (int) substr($row->no_pencatatan, -4);
            $next_number = $last_number + 1;
        } else {
            $next_number = 1;
        }

        // Reject if counter exceeds 9999
        if ($next_number > 9999) {
            return false;
        }

        return $prefix . str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // Budget
    // =========================================================================

    /**
     * Get total budget terpakai (used) for a petty cash master
     * SUM of grand_total where status != 'reject'
     *
     * @param int $petty_cash_id
     * @return float
     */
    public function get_budget_terpakai($petty_cash_id)
    {
        $this->db->select_sum('grand_total');
        $this->db->from('tr_expense_petty_cash');
        $this->db->where('petty_cash_id', $petty_cash_id);
        $this->db->where('status !=', 'reject');
        $result = $this->db->get()->row();

        return ($result && $result->grand_total) ? (float) $result->grand_total : 0;
    }

    /**
     * Get complete budget info (total budget, terpakai, sisa) for a petty cash master
     *
     * @param int $petty_cash_id
     * @return object|false
     */
    public function get_budget_info($petty_cash_id)
    {
        $this->db->select('total_budget');
        $this->db->from('ms_petty_cash');
        $this->db->where('id', $petty_cash_id);
        $master = $this->db->get()->row();

        if (!$master) {
            return false;
        }

        $budget = (float) $master->total_budget;
        $budget_terpakai = $this->get_budget_terpakai($petty_cash_id);
        $sisa_budget = $budget - $budget_terpakai;

        $info = new stdClass();
        $info->budget = $budget;
        $info->budget_terpakai = $budget_terpakai;
        $info->sisa_budget = $sisa_budget;

        return $info;
    }
}
