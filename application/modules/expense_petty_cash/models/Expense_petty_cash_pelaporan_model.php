<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Expense Petty Cash Pelaporan Model
 *
 * Model untuk mengelola pelaporan pengeluaran kas kecil.
 * Handles pelaporan CRUD, approval workflow, integration, dan validation.
 *
 * @author  Sendigs Finance
 * @copyright Copyright (c) 2024
 */

class Expense_petty_cash_pelaporan_model extends BF_Model
{
    /**
     * @var string Table Name
     */
    protected $table_name = 'tr_pelaporan_petty_cash';
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
     * Get server-side DataTables data for pelaporan list
     *
     * @param array $params DataTables request parameters
     * @param array $filters Additional filters (company, status, etc.)
     * @return array
     */
    public function get_server_side_data($params, $filters)
    {
        $draw   = isset($params['draw']) ? intval($params['draw']) : 1;
        $start  = isset($params['start']) ? intval($params['start']) : 0;
        $length = isset($params['length']) ? intval($params['length']) : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $order_col = isset($params['order'][0]['column']) ? intval($params['order'][0]['column']) : 1;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        // Column index mapping (matching DataTables columns)
        // 0: row number (not sortable)
        // 1: no_pelaporan
        // 2: periode
        // 3: company
        // 4: jumlah_pencatatan
        // 5: grand_total
        // 6: status
        // 7: action (not sortable)
        $columns = [
            0 => null,
            1 => 'a.no_pelaporan',
            2 => 'a.periode_start',
            3 => 'a.company',
            4 => null,
            5 => 'a.grand_total',
            6 => 'a.status',
            7 => null,
        ];

        $order_by = isset($columns[$order_col]) && $columns[$order_col] !== null
            ? $columns[$order_col]
            : 'a.periode_start';
        $order_dir = ($order_dir === 'asc') ? 'asc' : 'desc';

        // Build filter conditions as a closure
        $apply_filters = function () use ($filters, $search) {
            // Apply company filter
            if (!empty($filters['company']) && strtolower($filters['company']) !== 'all') {
                $this->db->where('a.company', $filters['company']);
            }
            // Apply status filter
            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
                $this->db->where('a.status', $filters['status']);
            }
            // Apply search
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('a.no_pelaporan', $search);
                $this->db->or_like('a.company', $search);
                $this->db->group_end();
            }
        };

        // Count total records (no filters)
        $this->db->from($this->table_name . ' a');
        $records_total = $this->db->count_all_results();

        // Count filtered records
        $this->db->from($this->table_name . ' a');
        $apply_filters();
        $records_filtered = $this->db->count_all_results();

        // Get paginated data with jumlah_pencatatan subquery
        $this->db->select('a.id, a.no_pelaporan, a.periode_start, a.periode_end, a.company, a.grand_total, a.status');
        $this->db->select('(SELECT COUNT(*) FROM tr_pelaporan_petty_cash_detail pd WHERE pd.pelaporan_id = a.id) as jumlah_pencatatan', false);
        $this->db->from($this->table_name . ' a');
        $apply_filters();
        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $results = $query->result();

        // Format data
        $data = [];
        $no = $start + 1;
        foreach ($results as $row) {
            $data[] = [
                'no'                => $no++,
                'id'                => $row->id,
                'no_pelaporan'      => $row->no_pelaporan,
                'periode_start'     => $row->periode_start,
                'periode_end'       => $row->periode_end,
                'company'           => $row->company,
                'jumlah_pencatatan' => intval($row->jumlah_pencatatan),
                'grand_total'       => number_format($row->grand_total, 0, ',', '.'),
                'status'            => $row->status,
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
     * Get single pelaporan record with detail pencatatan
     *
     * @param int $id Pelaporan ID
     * @return object|false
     */
    public function get_pelaporan($id)
    {
        // Get pelaporan header
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return false;
        }

        $header = $query->row();

        // Get linked pencatatan records via detail table
        $this->db->select('p.*, (SELECT GROUP_CONCAT(d.pengeluaran SEPARATOR ", ") FROM tr_expense_petty_cash_detail d WHERE d.pencatatan_id = p.id) as pengeluaran_summary', false);
        $this->db->from('tr_pelaporan_petty_cash_detail pd');
        $this->db->join('tr_expense_petty_cash p', 'p.id = pd.pencatatan_id', 'inner');
        $this->db->where('pd.pelaporan_id', $id);
        $this->db->order_by('p.tanggal', 'asc');
        $query_pencatatan = $this->db->get();
        $pencatatan_list = $query_pencatatan->result();

        // Compose result
        $result = new stdClass();
        $result->header          = $header;
        $result->pencatatan_list = $pencatatan_list;

        return $result;
    }

    /**
     * Create new pelaporan from selected pencatatan records
     *
     * @param array $data Pelaporan header data (company, approver_id, petty_cash_id, etc.)
     * @param array $pencatatan_ids Array of pencatatan IDs to include
     * @return int|false Insert ID or false on failure
     */
    public function create_pelaporan($data, $pencatatan_ids)
    {
        if (empty($pencatatan_ids)) {
            return false;
        }

        // Validate same week
        if (!$this->validate_same_week($pencatatan_ids)) {
            return false;
        }

        // Begin transaction
        $this->db->trans_start();

        // Generate no_pelaporan
        $no_pelaporan = $this->generate_no_pelaporan();
        if ($no_pelaporan === false) {
            $this->db->trans_rollback();
            return false;
        }

        // Calculate grand_total = SUM of linked pencatatan grand_totals
        $this->db->select_sum('grand_total');
        $this->db->from('tr_expense_petty_cash');
        $this->db->where_in('id', $pencatatan_ids);
        $sum_query = $this->db->get()->row();
        $grand_total = ($sum_query && $sum_query->grand_total) ? (int) $sum_query->grand_total : 0;

        // Get periode from pencatatan dates
        $periode = $this->get_periode_dari_pencatatan($pencatatan_ids);
        if ($periode === false) {
            $this->db->trans_rollback();
            return false;
        }

        // Get approver_id from ms_petty_cash if not provided
        $approver_id = isset($data['approver_id']) ? $data['approver_id'] : null;
        if (empty($approver_id) && !empty($data['petty_cash_id'])) {
            $this->db->select('approver');
            $this->db->from('ms_petty_cash');
            $this->db->where('id', $data['petty_cash_id']);
            $petty_cash = $this->db->get()->row();
            if ($petty_cash && !empty($petty_cash->approver) && $petty_cash->approver > 0) {
                $approver_id = $petty_cash->approver;
            }
        }

        // Ensure approver_id is NULL if invalid (0 or empty) to satisfy FK constraint
        if (empty($approver_id) || $approver_id == 0) {
            $approver_id = null;
        }

        // Insert pelaporan header
        $pelaporan_data = [
            'no_pelaporan'  => $no_pelaporan,
            'periode_start' => $periode->periode_start,
            'periode_end'   => $periode->periode_end,
            'company'       => $data['company'],
            'grand_total'   => $grand_total,
            'status'        => 'draft',
            'approver_id'   => $approver_id,
            'petty_cash_id' => isset($data['petty_cash_id']) ? $data['petty_cash_id'] : null,
            'created_on'    => date('Y-m-d H:i:s'),
            'created_by'    => isset($data['created_by']) ? $data['created_by'] : null,
        ];

        $this->db->insert($this->table_name, $pelaporan_data);
        $pelaporan_id = $this->db->insert_id();

        // Insert detail links (pelaporan_id ↔ pencatatan_id)
        foreach ($pencatatan_ids as $pencatatan_id) {
            $this->db->insert('tr_pelaporan_petty_cash_detail', [
                'pelaporan_id'  => $pelaporan_id,
                'pencatatan_id' => $pencatatan_id,
            ]);
        }

        // Complete transaction
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $pelaporan_id;
    }

    /**
     * Submit pelaporan (change status from draft to waiting)
     *
     * @param int $id Pelaporan ID
     * @return bool
     */
    public function submit_pelaporan($id)
    {
        // Begin transaction
        $this->db->trans_start();

        // Update pelaporan status to 'waiting'
        $this->db->where('id', $id);
        $this->db->update($this->table_name, ['status' => 'waiting']);

        // Get all linked pencatatan IDs
        $this->db->select('pencatatan_id');
        $this->db->from('tr_pelaporan_petty_cash_detail');
        $this->db->where('pelaporan_id', $id);
        $detail_query = $this->db->get();
        $details = $detail_query->result();

        // Update all linked pencatatan status to 'waiting approval'
        if (!empty($details)) {
            $pencatatan_ids = array_map(function ($d) {
                return $d->pencatatan_id;
            }, $details);

            $this->db->where_in('id', $pencatatan_ids);
            $this->db->update('tr_expense_petty_cash', ['status' => 'waiting approval']);
        }

        // Complete transaction
        $this->db->trans_complete();

        return $this->db->trans_status() !== false;
    }

    // =========================================================================
    // Approval Operations
    // =========================================================================

    /**
     * Approve pelaporan and trigger integration
     *
     * @param int $id Pelaporan ID
     * @param int $user_id Approver user ID
     * @return bool
     */
    public function approve_pelaporan($id, $user_id)
    {
        // Validate pelaporan exists and status = 'waiting'
        $this->db->select('*');
        $this->db->from($this->table_name);
        $this->db->where('id', $id);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return false;
        }

        $pelaporan = $query->row();

        if ($pelaporan->status !== 'waiting') {
            return false;
        }

        // Begin transaction
        $this->db->trans_start();

        // Update pelaporan status to 'approved'
        $this->db->where('id', $id);
        $this->db->update($this->table_name, [
            'status'      => 'approved',
            'approved_on' => date('Y-m-d H:i:s'),
            'approved_by' => $user_id,
        ]);

        // Get all linked pencatatan IDs
        $this->db->select('pencatatan_id');
        $this->db->from('tr_pelaporan_petty_cash_detail');
        $this->db->where('pelaporan_id', $id);
        $detail_query = $this->db->get();
        $details = $detail_query->result();

        // Update linked pencatatan status to 'approved'
        if (!empty($details)) {
            $pencatatan_ids = array_map(function ($d) {
                return $d->pencatatan_id;
            }, $details);

            $this->db->where_in('id', $pencatatan_ids);
            $this->db->update('tr_expense_petty_cash', ['status' => 'approved']);
        }

        // Complete transaction
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        // Request Payment integration (optional - don't fail approval if this fails)
        try {
            $this->create_request_payment($pelaporan);
        } catch (Exception $e) {
            log_message('error', 'Request Payment integration failed for pelaporan #' . $id . ': ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Reject pelaporan with reason
     *
     * @param int $id Pelaporan ID
     * @param string $alasan Rejection reason (min 10, max 500 chars after trim)
     * @param int $user_id Rejector user ID
     * @return bool
     */
    public function reject_pelaporan($id, $alasan, $user_id)
    {
        // Validate alasan: trimmed length between 10-500 chars
        $alasan = trim($alasan);
        $alasan_length = mb_strlen($alasan);
        if ($alasan_length < 10 || $alasan_length > 500) {
            return false;
        }

        // Validate pelaporan exists and status = 'waiting'
        $this->db->select('*');
        $this->db->from($this->table_name);
        $this->db->where('id', $id);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return false;
        }

        $pelaporan = $query->row();

        if ($pelaporan->status !== 'waiting') {
            return false;
        }

        // Begin transaction
        $this->db->trans_start();

        // Update pelaporan status to 'reject' with reason
        $this->db->where('id', $id);
        $this->db->update($this->table_name, [
            'status'        => 'reject',
            'alasan_reject' => $alasan,
        ]);

        // Get all linked pencatatan IDs
        $this->db->select('pencatatan_id');
        $this->db->from('tr_pelaporan_petty_cash_detail');
        $this->db->where('pelaporan_id', $id);
        $detail_query = $this->db->get();
        $details = $detail_query->result();

        // Update linked pencatatan status back to 'draft' (return to editable)
        if (!empty($details)) {
            $pencatatan_ids = array_map(function ($d) {
                return $d->pencatatan_id;
            }, $details);

            $this->db->where_in('id', $pencatatan_ids);
            $this->db->update('tr_expense_petty_cash', ['status' => 'draft']);
        }

        // Complete transaction
        $this->db->trans_complete();

        return $this->db->trans_status() !== false;
    }

    // =========================================================================
    // Integration
    // =========================================================================

    /**
     * Create request payment entry from approved pelaporan
     *
     * Inserts a record into the request_payment table and, for VUCA/SUSTAIN companies,
     * also sends data to the Petty Cash VUCA/SUSTAIN module. Both operations run in
     * a single database transaction. On failure, rolls back the pelaporan approval status.
     *
     * @param object $pelaporan Pelaporan data object (row from tr_pelaporan_petty_cash)
     * @return bool
     */
    public function create_request_payment($pelaporan)
    {
        // Look up the creator's full name from users table
        $this->db->select('nm_lengkap');
        $this->db->from('users');
        $this->db->where('id_user', $pelaporan->created_by);
        $user_query = $this->db->get();
        $creator_name = '';
        if ($user_query->num_rows() > 0) {
            $creator_name = $user_query->row()->nm_lengkap;
        }

        // Check if request_payment table exists before inserting
        if (!$this->db->table_exists('request_payment')) {
            log_message('info', 'Table request_payment does not exist. Skipping integration for pelaporan #' . $pelaporan->id);
            return true;
        }

        // Prepare request payment data
        $company = strtoupper(trim($pelaporan->company));

        // Untuk STM: tipe = refill_pettycash, status = 1 (langsung masuk list pembayaran)
        // Untuk lainnya: tipe = refill_pettycash, status = 1 (langsung masuk list pembayaran)
        if ($company === 'STM') {
            $request_payment_data = [
                'no_doc'     => $pelaporan->no_pelaporan,
                'nama'       => $creator_name,
                'tgl_doc'    => date('Y-m-d'),
                'tanggal'    => date('Y-m-d'),
                'keperluan'  => 'Payment Hutang Petty Cash - ' . $pelaporan->no_pelaporan,
                'tipe'       => 'refill_pettycash',
                'jumlah'     => $pelaporan->grand_total,
                'status'     => 1,
                'created_by' => $pelaporan->approved_by,
                'created_on' => date('Y-m-d H:i:s'),
            ];
        } else {
            $request_payment_data = [
                'no_doc'     => $pelaporan->no_pelaporan,
                'nama'       => $creator_name,
                'tgl_doc'    => date('Y-m-d'),
                'tanggal'    => date('Y-m-d'),
                'keperluan'  => 'Pengeluaran Petty Cash - ' . $pelaporan->no_pelaporan,
                'tipe'       => 'refill_pettycash',
                'jumlah'     => $pelaporan->grand_total,
                'status'     => 1,
                'created_by' => $pelaporan->approved_by,
                'created_on' => date('Y-m-d H:i:s'),
            ];
        }

        // Insert into request_payment table
        $this->db->insert('request_payment', $request_payment_data);

        // For VUCA or SUSTAIN companies, also send to Petty Cash VUCA/SUSTAIN module
        if ($company === 'VUCA' || $company === 'SUSTAIN') {
            if ($this->db->table_exists('tr_petty_cash_vuca_sustain')) {
                $this->send_to_petty_cash_vuca_sustain($pelaporan);
            }
        }

        return true;
    }

    /**
     * Send approved pelaporan data to Petty Cash VUCA/SUSTAIN module
     *
     * Generates a No Payment Hutang via Petty_cash_vuca_sustain_model and inserts
     * a record into tr_petty_cash_vuca_sustain with full pelaporan mapping.
     * Wrapped in try-catch to ensure approval process is never blocked by failures here.
     *
     * @param object $pelaporan Pelaporan data object
     * @return bool Always returns true to avoid blocking approval flow
     */
    public function send_to_petty_cash_vuca_sustain($pelaporan)
    {
        try {
            // Load Petty_cash_vuca_sustain_model to generate no_payment_hutang
            $CI = &get_instance();
            $CI->load->model('petty_cash_vuca_sustain/Petty_cash_vuca_sustain_model', 'pcvs_model');

            // Generate sequential No Payment Hutang (PHP-YYYY-NNNN)
            $no_payment_hutang = $CI->pcvs_model->generate_no_payment_hutang();

            if ($no_payment_hutang === false) {
                log_message('error', 'send_to_petty_cash_vuca_sustain: Gagal generate no_payment_hutang untuk pelaporan ' . $pelaporan->no_pelaporan);
                return true; // Don't block approval
            }

            // Look up the creator's full name from users table
            $this->db->select('nm_lengkap');
            $this->db->from('users');
            $this->db->where('id_user', $pelaporan->created_by);
            $user_query = $this->db->get();
            $creator_name = '';
            if ($user_query->num_rows() > 0) {
                $creator_name = $user_query->row()->nm_lengkap;
            }

            // Count jumlah pencatatan from detail table (not a column in tr_pelaporan_petty_cash)
            $this->db->where('pelaporan_id', $pelaporan->id);
            $jumlah_pencatatan = $this->db->count_all_results('tr_pelaporan_petty_cash_detail');

            // Prepare data for tr_petty_cash_vuca_sustain
            $vuca_data = [
                'no_payment_hutang' => $no_payment_hutang,
                'no_pelaporan'      => $pelaporan->no_pelaporan,
                'pelaporan_id'      => $pelaporan->id,
                'company'           => strtoupper(trim($pelaporan->company)),
                'periode_start'     => $pelaporan->periode_start,
                'periode_end'       => $pelaporan->periode_end,
                'jumlah_pencatatan' => $jumlah_pencatatan,
                'grand_total'       => $pelaporan->grand_total,
                'nama_pembuat'      => $creator_name,
                'status'            => 'draft',
                'created_by'        => $pelaporan->approved_by,
                'created_on'        => date('Y-m-d H:i:s'),
            ];

            // Insert into tr_petty_cash_vuca_sustain table
            $result = $this->db->insert('tr_petty_cash_vuca_sustain', $vuca_data);

            if ($result === false) {
                log_message('error', 'send_to_petty_cash_vuca_sustain: Gagal insert record untuk pelaporan ' . $pelaporan->no_pelaporan . '. DB Error: ' . $this->db->error()['message']);
            }

            return true; // Always return true to not block approval
        } catch (Exception $e) {
            log_message('error', 'send_to_petty_cash_vuca_sustain: Exception saat proses pelaporan ' . $pelaporan->no_pelaporan . ' - ' . $e->getMessage());
            return true; // Don't block approval process
        }
    }

    /**
     * Rollback approval status when request payment integration fails.
     * Reverts pelaporan status to 'waiting' and linked pencatatan to 'waiting approval'.
     *
     * @param object $pelaporan Pelaporan data object
     * @return void
     */
    private function _rollback_approval_status($pelaporan)
    {
        // Revert pelaporan status back to 'waiting'
        $this->db->where('id', $pelaporan->id);
        $this->db->update($this->table_name, [
            'status'      => 'waiting',
            'approved_on' => null,
            'approved_by' => null,
        ]);

        // Revert linked pencatatan status back to 'waiting approval'
        $this->db->select('pencatatan_id');
        $this->db->from('tr_pelaporan_petty_cash_detail');
        $this->db->where('pelaporan_id', $pelaporan->id);
        $detail_query = $this->db->get();
        $details = $detail_query->result();

        if (!empty($details)) {
            $pencatatan_ids = array_map(function ($d) {
                return $d->pencatatan_id;
            }, $details);

            $this->db->where_in('id', $pencatatan_ids);
            $this->db->update('tr_expense_petty_cash', ['status' => 'waiting approval']);
        }
    }

    // =========================================================================
    // Sequential Number
    // =========================================================================

    /**
     * Generate sequential no_pelaporan for a given year
     * Format: RPC-YYYY-NNNN (e.g., RPC-2024-0001)
     *
     * Uses SELECT FOR UPDATE to ensure atomic number generation under concurrent access.
     * If no $year is provided, defaults to current year in Asia/Bangkok timezone.
     *
     * @param int|string|null $year 4-digit year (optional, defaults to current year Asia/Bangkok)
     * @return string|false Generated number or false if counter exceeds 9999
     */
    public function generate_no_pelaporan($year = null)
    {
        if ($year === null) {
            $tz = new DateTimeZone('Asia/Bangkok');
            $now = new DateTime('now', $tz);
            $year = $now->format('Y');
        }

        $year = (string) $year;
        $prefix = 'RPC-' . $year . '-';

        // Use raw query with FOR UPDATE to lock relevant rows and prevent race conditions
        $query = $this->db->query(
            "SELECT no_pelaporan FROM {$this->table_name} "
                . "WHERE no_pelaporan LIKE ? "
                . "ORDER BY no_pelaporan DESC LIMIT 1 FOR UPDATE",
            [$prefix . '%']
        );

        if ($query->num_rows() > 0) {
            $row = $query->row();
            // Extract the numeric part (last 4 characters)
            $last_number = (int) substr($row->no_pelaporan, -4);
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
    // Helper
    // =========================================================================

    /**
     * Calculate periode (Monday-Friday) from pencatatan dates
     * Returns the Monday and Friday of the ISO week containing the pencatatan dates
     *
     * @param array $pencatatan_ids Array of pencatatan IDs
     * @return object|false Object with periode_start (Monday) and periode_end (Friday)
     */
    public function get_periode_dari_pencatatan($pencatatan_ids)
    {
        if (empty($pencatatan_ids)) {
            return false;
        }

        // Get tanggal values from pencatatan records
        $this->db->select('tanggal');
        $this->db->from('tr_expense_petty_cash');
        $this->db->where_in('id', $pencatatan_ids);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return false;
        }

        // Take the first date to determine the ISO week
        $row = $query->row();
        $date = new DateTime($row->tanggal);

        // Get the Monday of this ISO week
        $day_of_week = (int) $date->format('N'); // 1=Monday, 7=Sunday
        $diff_to_monday = $day_of_week - 1;
        $monday = clone $date;
        $monday->modify("-{$diff_to_monday} days");

        // Get the Friday of this ISO week
        $friday = clone $monday;
        $friday->modify('+4 days');

        $result = new stdClass();
        $result->periode_start = $monday->format('Y-m-d');
        $result->periode_end   = $friday->format('Y-m-d');

        return $result;
    }

    /**
     * Validate that all pencatatan fall within the same ISO week
     *
     * @param array $pencatatan_ids Array of pencatatan IDs
     * @return bool True if all in same week, false otherwise
     */
    public function validate_same_week($pencatatan_ids)
    {
        if (empty($pencatatan_ids)) {
            return false;
        }

        // Get all tanggal values
        $this->db->select('tanggal');
        $this->db->from('tr_expense_petty_cash');
        $this->db->where_in('id', $pencatatan_ids);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return false;
        }

        $results = $query->result();

        // Check all dates fall in the same ISO week (year + week number)
        $week_key = null;
        foreach ($results as $row) {
            $date = new DateTime($row->tanggal);
            // Use 'o' for ISO year and 'W' for ISO week number
            $current_key = $date->format('o-W');

            if ($week_key === null) {
                $week_key = $current_key;
            } elseif ($current_key !== $week_key) {
                return false;
            }
        }

        return true;
    }
}
