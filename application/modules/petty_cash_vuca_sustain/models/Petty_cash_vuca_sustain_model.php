<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Petty Cash VUCA & Sustain Model
 *
 * Model untuk mengelola payment hutang inter-company VUCA/SUSTAIN terhadap STM.
 * Handles DataTables server-side, payment hutang processing, dan status management.
 *
 * @author  Sendigs Finance
 * @copyright Copyright (c) 2024
 */

class Petty_cash_vuca_sustain_model extends BF_Model
{
    // =========================================================================
    // Status Constants
    // =========================================================================
    const STATUS_DRAFT           = 'draft';
    const STATUS_WAITING_PAYMENT = 'waiting payment';
    const STATUS_DONE_PAYMENT    = 'done payment';

    /**
     * @var string Table Name
     */
    protected $table_name = 'tr_petty_cash_vuca_sustain';

    /**
     * @var string Primary Key
     */
    protected $key = 'id';

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
    // DataTables Server-Side
    // =========================================================================

    /**
     * Get server-side DataTables data with pagination, sorting, search, and filters
     *
     * @param  array $params  POST data from DataTables (draw, start, length, search, order, columns)
     * @param  array $filters Additional filters ['company' => '...', 'status' => '...']
     * @return array DataTables formatted response
     */
    public function get_server_side_data($params, $filters)
    {
        $draw   = isset($params['draw']) ? intval($params['draw']) : 1;
        $start  = isset($params['start']) ? intval($params['start']) : 0;
        $length = isset($params['length']) ? intval($params['length']) : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $order_col = isset($params['order'][0]['column']) ? intval($params['order'][0]['column']) : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        // Column index mapping (matching DataTables columns)
        // 0: row number (not sortable)
        // 1: no_pelaporan
        // 2: no_payment_hutang
        // 3: periode (sorted by periode_start)
        // 4: company
        // 5: jumlah_pencatatan
        // 6: grand_total
        // 7: status
        // 8: action (not sortable)
        $columns = [
            0 => null,
            1 => 'a.no_pelaporan',
            2 => 'a.no_payment_hutang',
            3 => 'a.periode_start',
            4 => 'a.company',
            5 => 'a.jumlah_pencatatan',
            6 => 'a.grand_total',
            7 => 'a.status',
            8 => null,
        ];

        $order_by = isset($columns[$order_col]) && $columns[$order_col] !== null
            ? $columns[$order_col]
            : 'a.created_on';
        $order_dir = ($order_dir === 'asc') ? 'asc' : 'desc';

        // Build filter conditions as a closure
        $apply_filters = function () use ($filters, $search) {
            // Apply company filter
            if (!empty($filters['company']) && strtolower($filters['company']) !== 'semua') {
                $this->db->where('a.company', $filters['company']);
            }
            // Apply status filter
            if (!empty($filters['status']) && strtolower($filters['status']) !== 'semua') {
                $this->db->where('a.status', $filters['status']);
            }
            // Apply global search
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('a.no_pelaporan', $search);
                $this->db->or_like('a.no_payment_hutang', $search);
                $this->db->or_like('a.company', $search);
                $this->db->or_like('a.nama_pembuat', $search);
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

        // Get paginated data
        $this->db->select('a.id, a.no_pelaporan, a.no_payment_hutang, a.periode_start, a.periode_end, a.company, a.jumlah_pencatatan, a.grand_total, a.status');
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
                'no_payment_hutang' => $row->no_payment_hutang,
                'periode'           => $this->_format_periode($row->periode_start, $row->periode_end),
                'company'           => $row->company,
                'jumlah_pencatatan' => intval($row->jumlah_pencatatan),
                'grand_total'       => number_format($row->grand_total, 0, ',', '.'),
                'grand_total_raw'   => $row->grand_total,
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
     * Format periode date range as "dd Mon YYYY - dd Mon YYYY"
     *
     * @param  string $start Date string (Y-m-d)
     * @param  string $end   Date string (Y-m-d)
     * @return string Formatted period string
     */
    private function _format_periode($start, $end)
    {
        $months = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        ];

        $start_date = date('d', strtotime($start));
        $start_month = $months[date('m', strtotime($start))];
        $start_year = date('Y', strtotime($start));

        $end_date = date('d', strtotime($end));
        $end_month = $months[date('m', strtotime($end))];
        $end_year = date('Y', strtotime($end));

        return "{$start_date} {$start_month} {$start_year} - {$end_date} {$end_month} {$end_year}";
    }

    // =========================================================================
    // Payment Hutang Processing
    // =========================================================================

    /**
     * Process Payment Hutang: insert ke request_payment dan update status ke waiting payment
     *
     * Menggunakan transaction untuk memastikan atomicity:
     * - SELECT FOR UPDATE untuk lock record dan validasi status
     * - INSERT ke request_payment
     * - UPDATE status ke 'waiting payment'
     *
     * @param  int $id      ID record tr_petty_cash_vuca_sustain
     * @param  int $user_id ID user yang melakukan aksi
     * @return bool true jika berhasil, false jika gagal
     */
    public function process_payment_hutang($id, $user_id)
    {
        $this->db->trans_begin();

        try {
            // 1. SELECT record dengan status 'draft' FOR UPDATE (lock row)
            $query = $this->db->query(
                "SELECT id, no_payment_hutang, nama_pembuat, grand_total, status "
                    . "FROM {$this->table_name} "
                    . "WHERE id = ? AND status = ? FOR UPDATE",
                [$id, self::STATUS_DRAFT]
            );

            if ($query->num_rows() === 0) {
                $this->db->trans_rollback();
                log_message('warning', 'process_payment_hutang: Record id=' . $id . ' tidak ditemukan atau status bukan draft');
                return false;
            }

            $record = $query->row();
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');

            // 2. INSERT ke request_payment
            $insert_data = [
                'no_doc'     => $record->no_payment_hutang,
                'nama'       => $record->nama_pembuat,
                'tgl_doc'    => $today,
                'tanggal'    => $today,
                'keperluan'  => 'Payment Hutang Petty Cash - ' . $record->no_payment_hutang,
                'tipe'       => 'petty_cash_hutang',
                'jumlah'     => $record->grand_total,
                'status'     => 1, // status 1 = approved/ready for payment (no additional approval needed)
                'created_by' => $user_id,
                'created_on' => $now,
            ];

            $this->db->insert('request_payment', $insert_data);

            // 3. UPDATE status ke 'waiting payment'
            $this->db->where('id', $id);
            $this->db->update($this->table_name, [
                'status'      => self::STATUS_WAITING_PAYMENT,
                'modified_on' => $now,
                'modified_by' => $user_id,
            ]);

            // 4. Check transaction status
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                log_message('error', 'process_payment_hutang: Transaction failed for record id=' . $id);
                return false;
            }

            $this->db->trans_commit();
            log_message('info', 'process_payment_hutang: Successfully processed payment hutang id=' . $id . ' no_doc=' . $record->no_payment_hutang);
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'process_payment_hutang: Exception - ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // Number Generation
    // =========================================================================

    /**
     * Generate No Payment Hutang dengan format PHP-{YYYY}-{NNNN}
     *
     * Menggunakan SELECT FOR UPDATE untuk mencegah race condition pada akses bersamaan.
     * Nomor sequential per tahun, reset ke 0001 tiap awal tahun baru.
     *
     * @param  string|null $year Tahun (YYYY). Jika null, gunakan tahun berjalan (Asia/Bangkok)
     * @return string|false Format PHP-YYYY-NNNN atau false jika nomor sudah mencapai 9999
     */
    public function generate_no_payment_hutang($year = null)
    {
        if ($year === null) {
            $tz = new DateTimeZone('Asia/Bangkok');
            $now = new DateTime('now', $tz);
            $year = $now->format('Y');
        }

        $year = (string) $year;
        $prefix = 'PHP-' . $year . '-';

        // Use raw query with FOR UPDATE to lock relevant rows and prevent race conditions
        $query = $this->db->query(
            "SELECT no_payment_hutang FROM {$this->table_name} "
                . "WHERE no_payment_hutang LIKE ? "
                . "ORDER BY no_payment_hutang DESC LIMIT 1 FOR UPDATE",
            [$prefix . '%']
        );

        if ($query->num_rows() > 0) {
            $row = $query->row();
            // Extract the numeric part (last 4 characters)
            $last_number = (int) substr($row->no_payment_hutang, -4);
            $next_number = $last_number + 1;
        } else {
            $next_number = 1;
        }

        // Reject if counter exceeds 9999
        if ($next_number > 9999) {
            log_message('error', 'generate_no_payment_hutang: Nomor urut payment hutang telah mencapai batas maksimum (9999) untuk tahun ' . $year);
            return false;
        }

        return $prefix . str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // Detail & Print
    // =========================================================================

    /**
     * Get Payment Hutang detail lengkap: header + daftar pencatatan + detail item per pencatatan
     *
     * Mengambil data gabungan dari:
     * - tr_petty_cash_vuca_sustain (header)
     * - tr_pelaporan_petty_cash_detail (daftar pencatatan via pelaporan_id)
     * - tr_expense_petty_cash (data pencatatan: no_pencatatan, tanggal, request_by, keterangan, grand_total)
     * - tr_expense_petty_cash_detail (detail item: coa_code, pengeluaran, spesifikasi, jumlah, nominal, total)
     * - coa_master (nama COA)
     *
     * @param  int $id ID record tr_petty_cash_vuca_sustain
     * @return object|false Object dengan property header + pencatatan_list (each with nested items), atau false jika tidak ditemukan
     */
    public function get_payment_hutang($id)
    {
        // 1. Get main record from tr_petty_cash_vuca_sustain
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            return false;
        }

        $header = $query->row();

        // 2. Get pencatatan list from tr_pelaporan_petty_cash_detail
        //    joined with tr_expense_petty_cash for pencatatan details
        $this->db->select('p.id, p.no_pencatatan, p.tanggal, p.request_by, p.keterangan, p.grand_total as nominal');
        $this->db->from('tr_pelaporan_petty_cash_detail pd');
        $this->db->join('tr_expense_petty_cash p', 'p.id = pd.pencatatan_id', 'inner');
        $this->db->where('pd.pelaporan_id', $header->pelaporan_id);
        $this->db->order_by('p.tanggal', 'asc');
        $query_pencatatan = $this->db->get();
        $pencatatan_list = $query_pencatatan->result();

        // 3. For each pencatatan, get expense detail items from tr_expense_petty_cash_detail
        foreach ($pencatatan_list as &$pencatatan) {
            $this->db->select('d.id, d.coa_code, d.pengeluaran, d.spesifikasi, d.jumlah, d.nominal, d.total, c.nama as coa_nama');
            $this->db->from('tr_expense_petty_cash_detail d');
            $this->db->join(DBACC . '.coa_master c', 'c.no_perkiraan = d.coa_code', 'left');
            $this->db->where('d.pencatatan_id', $pencatatan->id);
            $this->db->order_by('d.sort_order', 'asc');
            $pencatatan->items = $this->db->get()->result();
        }
        unset($pencatatan);

        // 4. Compose result object
        $result = new stdClass();
        $result->header          = $header;
        $result->pencatatan_list = $pencatatan_list;

        return $result;
    }

    // =========================================================================
    // Status Update
    // =========================================================================

    /**
     * Update status record menjadi "done payment"
     *
     * Dipanggil oleh modul pembayaran_material ketika pembayaran dengan
     * tipe "petty_cash_hutang" selesai diproses.
     *
     * @param  string      $no_doc  No Payment Hutang (no_doc dari request_payment) atau ID record
     * @param  int|null    $user_id User ID yang melakukan update (modified_by)
     * @return bool        true jika berhasil update, false jika gagal
     */
    public function update_status_done($no_doc, $user_id = null)
    {
        // Find record by no_payment_hutang first, fallback to id
        $record = $this->db
            ->where('no_payment_hutang', $no_doc)
            ->get($this->table_name)
            ->row();

        if (!$record) {
            // Fallback: try finding by id (if $no_doc is numeric)
            if (is_numeric($no_doc)) {
                $record = $this->db
                    ->where('id', $no_doc)
                    ->get($this->table_name)
                    ->row();
            }
        }

        // Record not found
        if (!$record) {
            log_message('warning', 'update_status_done: Record not found for no_doc: ' . $no_doc);
            return false;
        }

        // Validate current status must be "waiting payment"
        if ($record->status !== self::STATUS_WAITING_PAYMENT) {
            log_message('warning', 'update_status_done: Invalid status transition for no_doc: ' . $no_doc . ', current status: ' . $record->status);
            return false;
        }

        // Update status to "done payment"
        $update_data = [
            'status'      => self::STATUS_DONE_PAYMENT,
            'modified_on' => date('Y-m-d H:i:s'),
            'modified_by' => $user_id,
        ];

        $this->db->where('id', $record->id);
        $this->db->update($this->table_name, $update_data);

        return true;
    }
}
