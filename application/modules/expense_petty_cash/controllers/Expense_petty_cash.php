<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Expense Petty Cash Controller
 *
 * Mengelola siklus lengkap pengeluaran kas kecil:
 * - Pencatatan transaksi harian
 * - Pelaporan mingguan per company
 * - Approval oleh approver
 *
 * @author Sendigs Finance
 * @copyright Copyright (c) 2025
 */
class Expense_petty_cash extends Admin_Controller
{
    // Permission constants
    protected $viewPermission    = 'Expense_Petty_Cash.View';
    protected $addPermission     = 'Expense_Petty_Cash.Add';
    protected $managePermission  = 'Expense_Petty_Cash.Manage';
    protected $deletePermission  = 'Expense_Petty_Cash.Delete';
    protected $approvePermission = 'Expense_Petty_Cash.Approve';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'expense_petty_cash/Expense_petty_cash_model',
            'expense_petty_cash/Expense_petty_cash_pelaporan_model'
        ));
        $this->template->title('Expense Petty Cash');
        $this->template->page_icon('fa fa-money');
    }

    // =========================================================================
    // PENCATATAN
    // =========================================================================

    /**
     * Halaman daftar pencatatan petty cash
     *
     * Menampilkan index view dengan DataTables server-side.
     * Permission flags dikirim ke view untuk kontrol tombol aksi.
     *
     * @return void
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = [
            'has_add'    => has_permission($this->addPermission),
            'has_manage' => has_permission($this->managePermission),
            'has_delete' => has_permission($this->deletePermission),
        ];

        $this->template->set($data);
        $this->template->title('Pencatatan Petty Cash');
        $this->template->render('pencatatan/index');
    }

    /**
     * Server-side DataTables endpoint untuk pencatatan (POST, AJAX)
     *
     * Menerima parameter DataTables (draw, start, length, search, order)
     * dan mengembalikan JSON response untuk rendering tabel.
     *
     * @return void Echoes JSON {draw, recordsTotal, recordsFiltered, data}
     */
    public function get_data_pencatatan()
    {
        $this->auth->restrict($this->viewPermission);

        $result = $this->Expense_petty_cash_model->get_server_side_data($this->input->post());

        echo json_encode($result);
    }

    /**
     * Form tambah pencatatan baru
     *
     * Load form kosong dengan:
     * - No Pencatatan preview (generated saat save, tampilkan placeholder)
     * - Budget info (Budget, Budget Terpakai, Sisa Budget)
     * - COA list dari ms_petty_cash_detail
     * - petty_cash_id dari parameter URL (dipilih user via modal)
     *
     * @param int|null $petty_cash_id  ID master petty cash (dari URL)
     * @return void
     */
    public function create($petty_cash_id = null)
    {
        $this->auth->restrict($this->addPermission);

        // If no petty_cash_id provided, redirect back to index
        if (empty($petty_cash_id)) {
            redirect('expense_petty_cash');
            return;
        }

        $petty_cash_id = (int) $petty_cash_id;

        // Get budget info
        $budget_info = $this->Expense_petty_cash_model->get_budget_info($petty_cash_id);
        if (!$budget_info) {
            redirect('expense_petty_cash');
            return;
        }

        // Get COA list from ms_petty_cash_detail joined with coa_master
        $coa_list = $this->_get_coa_list($petty_cash_id);

        $data = [
            'mode'          => 'create',
            'petty_cash_id' => $petty_cash_id,
            'budget_info'   => $budget_info,
            'coa_list'      => $coa_list,
        ];

        $this->template->set($data);
        $this->template->title('Tambah Pencatatan Petty Cash');
        $this->template->render('pencatatan/form');
    }

    /**
     * Form edit pencatatan
     *
     * Load form terisi data pencatatan beserta detail dan evidence.
     * Hanya pencatatan berstatus 'draft' atau 'reject' yang dapat diedit.
     *
     * @param int $id Pencatatan ID
     * @return void
     */
    public function edit($id)
    {
        $this->auth->restrict($this->managePermission);

        // Get pencatatan data
        $pencatatan = $this->Expense_petty_cash_model->get_pencatatan($id);

        if (!$pencatatan) {
            Template::set_message('Data pencatatan tidak ditemukan.', 'error');
            redirect('expense_petty_cash');
            return;
        }

        // Validate status must be 'draft' or 'reject'
        if (!in_array($pencatatan->header->status, ['draft', 'reject'])) {
            Template::set_message('Pencatatan dengan status "' . $pencatatan->header->status . '" tidak dapat diedit.', 'error');
            redirect('expense_petty_cash');
            return;
        }

        // Get petty_cash_id from the pencatatan record
        $petty_cash_id = $pencatatan->header->petty_cash_id;

        // Get budget info
        $budget_info = false;
        if ($petty_cash_id) {
            $budget_info = $this->Expense_petty_cash_model->get_budget_info($petty_cash_id);
        }

        // Get COA list from ms_petty_cash_detail joined with coa_master
        $coa_list = $this->_get_coa_list($petty_cash_id);

        $data = [
            'mode'          => 'edit',
            'pencatatan'    => $pencatatan,
            'petty_cash_id' => $petty_cash_id,
            'budget_info'   => $budget_info,
            'coa_list'      => $coa_list,
        ];

        $this->template->set($data);
        $this->template->title('Edit Pencatatan Petty Cash');
        $this->template->render('pencatatan/form');
    }

    /**
     * View detail pencatatan (read-only)
     *
     * Menampilkan detail lengkap pencatatan beserta detail item dan evidence.
     *
     * @param int $id Pencatatan ID
     * @return void
     */
    public function view($id)
    {
        $this->auth->restrict($this->viewPermission);

        // Get pencatatan data
        $pencatatan = $this->Expense_petty_cash_model->get_pencatatan($id);

        if (!$pencatatan) {
            Template::set_message('Data pencatatan tidak ditemukan.', 'error');
            redirect('expense_petty_cash');
            return;
        }

        // Get petty_cash_id from the pencatatan record
        $petty_cash_id = $pencatatan->header->petty_cash_id;

        // Get budget info
        $budget_info = false;
        if ($petty_cash_id) {
            $budget_info = $this->Expense_petty_cash_model->get_budget_info($petty_cash_id);
        }

        // Get COA list for reference display
        $coa_list = $this->_get_coa_list($petty_cash_id);

        $data = [
            'mode'          => 'view',
            'pencatatan'    => $pencatatan,
            'petty_cash_id' => $petty_cash_id,
            'budget_info'   => $budget_info,
            'coa_list'      => $coa_list,
        ];

        $this->template->set($data);
        $this->template->title('Detail Pencatatan Petty Cash');
        $this->template->render('pencatatan/view');
    }

    // =========================================================================
    // PELAPORAN
    // =========================================================================

    /**
     * Halaman daftar pelaporan petty cash
     *
     * Menampilkan index view pelaporan dengan DataTables server-side.
     * Filter company dan status tersedia di atas tabel.
     * Permission flags dikirim ke view untuk kontrol tombol aksi.
     *
     * @return void
     */
    public function pelaporan()
    {
        $this->auth->restrict($this->viewPermission);

        $data = [
            'has_add'    => has_permission($this->addPermission),
            'has_manage' => has_permission($this->managePermission),
        ];

        $this->template->set($data);
        $this->template->title('Pelaporan Petty Cash');
        $this->template->render('pelaporan/index');
    }

    /**
     * Server-side DataTables endpoint untuk pelaporan (POST, AJAX)
     *
     * Menerima parameter DataTables (draw, start, length, search, order)
     * beserta filter company dan status, lalu mengembalikan JSON response.
     *
     * @return void Echoes JSON {draw, recordsTotal, recordsFiltered, data}
     */
    public function get_data_pelaporan()
    {
        $this->auth->restrict($this->viewPermission);

        $filters = [
            'company' => $this->input->post('company'),
            'status'  => $this->input->post('status'),
        ];

        $result = $this->Expense_petty_cash_pelaporan_model->get_server_side_data($this->input->post(), $filters);

        echo json_encode($result);
    }

    /**
     * Buat pelaporan dari pencatatan terpilih (POST, AJAX)
     *
     * Menerima array pencatatan_ids dari POST, lalu validasi:
     * - Minimal 1 pencatatan dipilih
     * - Semua pencatatan dari company yang sama
     * - Semua pencatatan berada di minggu yang sama (ISO week)
     * - Semua pencatatan berstatus 'draft' dan belum masuk pelaporan aktif
     *
     * Jika valid, buat record pelaporan dan return redirect URL ke view pelaporan.
     *
     * @return void Output JSON response
     */
    public function create_pelaporan()
    {
        // Check permission
        if (!has_permission($this->addPermission)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Anda tidak memiliki akses untuk membuat pelaporan'
            ]);
            return;
        }

        // Get pencatatan_ids from POST
        $pencatatan_ids = $this->input->post('pencatatan_ids');

        // Validate not empty
        if (empty($pencatatan_ids) || !is_array($pencatatan_ids)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Minimal satu pencatatan harus dipilih untuk membuat pelaporan'
            ]);
            return;
        }

        // Clean IDs (ensure integers)
        $pencatatan_ids = array_map('intval', $pencatatan_ids);
        $pencatatan_ids = array_filter($pencatatan_ids, function ($id) {
            return $id > 0;
        });

        if (empty($pencatatan_ids)) {
            echo json_encode([
                'status'  => false,
                'message' => 'ID pencatatan tidak valid'
            ]);
            return;
        }

        // =====================================================================
        // Validate: all pencatatan exist and are status 'draft'
        // =====================================================================
        $this->db->select('id, company, status, petty_cash_id');
        $this->db->from('tr_expense_petty_cash');
        $this->db->where_in('id', $pencatatan_ids);
        $query = $this->db->get();
        $pencatatan_records = $query->result();

        if (count($pencatatan_records) !== count($pencatatan_ids)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Beberapa pencatatan tidak ditemukan'
            ]);
            return;
        }

        // Check all are 'draft' status
        $non_draft = [];
        foreach ($pencatatan_records as $record) {
            if ($record->status !== 'draft') {
                $non_draft[] = $record->id;
            }
        }

        if (!empty($non_draft)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Pencatatan dengan status selain "draft" tidak dapat dimasukkan ke pelaporan'
            ]);
            return;
        }

        // =====================================================================
        // Validate: not already in active pelaporan (waiting/approved)
        // =====================================================================
        $this->db->select('pd.pencatatan_id');
        $this->db->from('tr_pelaporan_petty_cash_detail pd');
        $this->db->join('tr_pelaporan_petty_cash p', 'p.id = pd.pelaporan_id', 'inner');
        $this->db->where_in('pd.pencatatan_id', $pencatatan_ids);
        $this->db->where_in('p.status', ['draft', 'waiting', 'approved']);
        $existing_query = $this->db->get();

        if ($existing_query->num_rows() > 0) {
            $existing_ids = array_map(function ($row) {
                return $row->pencatatan_id;
            }, $existing_query->result());

            echo json_encode([
                'status'  => false,
                'message' => 'Pencatatan berikut sudah masuk dalam pelaporan aktif: ' . implode(', ', $existing_ids)
            ]);
            return;
        }

        // =====================================================================
        // Validate: same company
        // =====================================================================
        $companies = array_unique(array_map(function ($record) {
            return $record->company;
        }, $pencatatan_records));

        if (count($companies) > 1) {
            echo json_encode([
                'status'  => false,
                'message' => 'Pelaporan hanya dapat berisi pencatatan dari satu company yang sama'
            ]);
            return;
        }

        // =====================================================================
        // Validate: same week (ISO week)
        // =====================================================================
        $same_week = $this->Expense_petty_cash_pelaporan_model->validate_same_week($pencatatan_ids);

        if (!$same_week) {
            echo json_encode([
                'status'  => false,
                'message' => 'Pelaporan hanya dapat berisi pencatatan dari minggu yang sama'
            ]);
            return;
        }

        // =====================================================================
        // Get company and petty_cash_id from the first record
        // =====================================================================
        $company       = $pencatatan_records[0]->company;
        $petty_cash_id = $pencatatan_records[0]->petty_cash_id;

        // =====================================================================
        // Create pelaporan
        // =====================================================================
        $data = [
            'company'       => $company,
            'petty_cash_id' => $petty_cash_id,
            'created_by'    => $this->auth->user_id(),
        ];

        $pelaporan_id = $this->Expense_petty_cash_pelaporan_model->create_pelaporan($data, $pencatatan_ids);

        if ($pelaporan_id === false) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal membuat pelaporan. Silakan coba lagi.'
            ]);
            return;
        }

        // Return success with redirect URL to view pelaporan
        echo json_encode([
            'status'      => true,
            'message'     => 'Pelaporan berhasil dibuat',
            'redirect_url' => site_url('expense_petty_cash/view_pelaporan/' . $pelaporan_id),
        ]);
    }

    /**
     * View detail pelaporan (read-only)
     *
     * Menampilkan detail lengkap pelaporan beserta daftar pencatatan terkait.
     *
     * @param int $id Pelaporan ID
     * @return void
     */
    public function view_pelaporan($id)
    {
        $this->auth->restrict($this->viewPermission);

        // Get pelaporan data with linked pencatatan
        $pelaporan = $this->Expense_petty_cash_pelaporan_model->get_pelaporan($id);

        if (!$pelaporan) {
            Template::set_message('Data pelaporan tidak ditemukan.', 'error');
            redirect('expense_petty_cash/pelaporan');
            return;
        }

        // Get budget info
        $budget_info = false;
        $petty_cash_id = !empty($pelaporan->header->petty_cash_id) ? $pelaporan->header->petty_cash_id : null;
        if ($petty_cash_id) {
            $budget_info = $this->Expense_petty_cash_model->get_budget_info($petty_cash_id);
        }

        // Get detail items per pencatatan for full review
        $pencatatan_details = [];
        if (!empty($pelaporan->pencatatan_list)) {
            foreach ($pelaporan->pencatatan_list as $pencatatan) {
                $detail = $this->Expense_petty_cash_model->get_pencatatan($pencatatan->id);
                if ($detail) {
                    $pencatatan_details[$pencatatan->id] = $detail;
                }
            }
        }

        // Get COA list for display
        $coa_list = $petty_cash_id ? $this->_get_coa_list($petty_cash_id) : [];

        $data = [
            'pelaporan'          => $pelaporan,
            'budget_info'        => $budget_info,
            'pencatatan_details' => $pencatatan_details,
            'coa_list'           => $coa_list,
            'has_add'            => has_permission($this->addPermission),
            'has_manage'         => has_permission($this->managePermission),
        ];

        $this->template->set($data);
        $this->template->title('Detail Pelaporan Petty Cash');
        $this->template->render('pelaporan/view');
    }

    /**
     * Halaman konfirmasi sebelum ajukan pelaporan
     *
     * Menampilkan detail lengkap pelaporan (ringkasan, detail pencatatan,
     * detail item per pencatatan, dan preview jurnal) sebelum user mengajukan.
     *
     * @param int $id Pelaporan ID
     * @return void
     */
    public function confirm_pelaporan($id)
    {
        $this->auth->restrict($this->viewPermission);

        // Get pelaporan data with linked pencatatan
        $pelaporan = $this->Expense_petty_cash_pelaporan_model->get_pelaporan($id);

        if (!$pelaporan) {
            Template::set_message('Data pelaporan tidak ditemukan.', 'error');
            redirect('expense_petty_cash/pelaporan');
            return;
        }

        // Only draft pelaporan can be confirmed/submitted
        if ($pelaporan->header->status !== 'draft') {
            Template::set_message('Hanya pelaporan berstatus "draft" yang dapat diajukan.', 'error');
            redirect('expense_petty_cash/pelaporan');
            return;
        }

        // Get budget info
        $budget_info = false;
        if (!empty($pelaporan->header->petty_cash_id)) {
            $budget_info = $this->Expense_petty_cash_model->get_budget_info($pelaporan->header->petty_cash_id);
        }

        // Get detail items per pencatatan for full review
        $pencatatan_details = [];
        if (!empty($pelaporan->pencatatan_list)) {
            foreach ($pelaporan->pencatatan_list as $pencatatan) {
                $detail = $this->Expense_petty_cash_model->get_pencatatan($pencatatan->id);
                if ($detail) {
                    $pencatatan_details[$pencatatan->id] = $detail;
                }
            }
        }

        // Get COA list for display
        $coa_list = [];
        if (!empty($pelaporan->header->petty_cash_id)) {
            $coa_list = $this->_get_coa_list($pelaporan->header->petty_cash_id);
        }

        $data = [
            'pelaporan'          => $pelaporan,
            'budget_info'        => $budget_info,
            'pencatatan_details' => $pencatatan_details,
            'coa_list'           => $coa_list,
            'has_add'            => has_permission($this->addPermission),
            'has_manage'         => has_permission($this->managePermission),
        ];

        $this->template->set($data);
        $this->template->title('Konfirmasi Pelaporan Petty Cash');
        $this->template->render('pelaporan/confirm');
    }

    /**
     * Submit/ajukan pelaporan (draft → waiting) via AJAX
     *
     * Mengubah status pelaporan dari 'draft' menjadi 'waiting'
     * dan mengubah status semua pencatatan terkait menjadi 'waiting approval'.
     *
     * @param int $id Pelaporan ID
     * @return void Output JSON response
     */
    public function submit_pelaporan($id)
    {
        // Check permission (add or manage)
        if (!has_permission($this->addPermission) && !has_permission($this->managePermission)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Anda tidak memiliki akses untuk mengajukan pelaporan'
            ]);
            return;
        }

        // Validate ID
        if (empty($id)) {
            echo json_encode([
                'status'  => false,
                'message' => 'ID pelaporan tidak valid'
            ]);
            return;
        }

        // Check pelaporan exists and is in 'draft' status
        $this->db->select('id, status');
        $this->db->from('tr_pelaporan_petty_cash');
        $this->db->where('id', $id);
        $pelaporan = $this->db->get()->row();

        if (!$pelaporan) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data pelaporan tidak ditemukan'
            ]);
            return;
        }

        if ($pelaporan->status !== 'draft') {
            echo json_encode([
                'status'  => false,
                'message' => 'Hanya pelaporan berstatus "draft" yang dapat diajukan'
            ]);
            return;
        }

        // Submit pelaporan (draft → waiting)
        $result = $this->Expense_petty_cash_pelaporan_model->submit_pelaporan($id);

        if (!$result) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal mengajukan pelaporan. Silakan coba lagi.'
            ]);
            return;
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Pelaporan berhasil diajukan untuk approval'
        ]);
    }

    // =========================================================================
    // APPROVAL
    // =========================================================================

    /**
     * Halaman daftar approval pelaporan petty cash
     *
     * Menampilkan daftar pelaporan berstatus "waiting" yang ditujukan
     * kepada current user sebagai approver.
     *
     * @return void
     */
    public function approval()
    {
        $this->auth->restrict($this->approvePermission);

        $this->template->title('Approval Pelaporan Petty Cash');
        $this->template->render('approval/index');
    }

    /**
     * Server-side DataTables endpoint untuk approval list (POST, AJAX)
     *
     * Query pelaporan where status='waiting' AND approver_id = current user.
     * Diurutkan berdasarkan tanggal pelaporan terlama (asc) sebagai default.
     *
     * @return void Echoes JSON {draw, recordsTotal, recordsFiltered, data}
     */
    public function get_data_approval()
    {
        $this->auth->restrict($this->approvePermission);

        $current_user_id = $this->auth->user_id();

        $params = $this->input->post();
        $draw   = isset($params['draw']) ? intval($params['draw']) : 1;
        $start  = isset($params['start']) ? intval($params['start']) : 0;
        $length = isset($params['length']) ? intval($params['length']) : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';

        // Approver filter: show items assigned to current user OR unassigned (NULL)
        $approver_condition = "(approver_id = {$current_user_id} OR approver_id IS NULL)";

        // Count total records for this approver (waiting status)
        $this->db->from('tr_pelaporan_petty_cash');
        $this->db->where('status', 'waiting');
        $this->db->where($approver_condition, null, false);
        $records_total = $this->db->count_all_results();

        // Count filtered records
        $this->db->from('tr_pelaporan_petty_cash');
        $this->db->where('status', 'waiting');
        $this->db->where($approver_condition, null, false);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('no_pelaporan', $search);
            $this->db->or_like('company', $search);
            $this->db->group_end();
        }
        $records_filtered = $this->db->count_all_results();

        // Get paginated data
        $this->db->select('a.id, a.no_pelaporan, a.periode_start, a.periode_end, a.company, a.grand_total, a.status, a.created_on');
        $this->db->select('(SELECT COUNT(*) FROM tr_pelaporan_petty_cash_detail pd WHERE pd.pelaporan_id = a.id) as jumlah_pencatatan', false);
        $this->db->from('tr_pelaporan_petty_cash a');
        $this->db->where('a.status', 'waiting');
        $this->db->where("(a.approver_id = {$current_user_id} OR a.approver_id IS NULL)", null, false);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.no_pelaporan', $search);
            $this->db->or_like('a.company', $search);
            $this->db->group_end();
        }
        $this->db->order_by('a.created_on', 'asc'); // Terlama dulu
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

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
            'data'            => $data,
        ]);
    }

    /**
     * View detail pelaporan untuk approval
     *
     * Menampilkan detail pelaporan beserta daftar pencatatan, detail item,
     * dan evidence untuk review oleh approver.
     * Validasi bahwa current user adalah approver yang ditunjuk.
     *
     * @param int $id Pelaporan ID
     * @return void
     */
    public function view_approval($id)
    {
        $this->auth->restrict($this->approvePermission);

        $current_user_id = $this->auth->user_id();

        // Get pelaporan data
        $pelaporan = $this->Expense_petty_cash_pelaporan_model->get_pelaporan($id);

        if (!$pelaporan) {
            Template::set_message('Data pelaporan tidak ditemukan.', 'error');
            redirect('expense_petty_cash/approval');
            return;
        }

        // Validate current user is the designated approver (or approver is unassigned)
        if (!empty($pelaporan->header->approver_id) && (int) $pelaporan->header->approver_id !== (int) $current_user_id) {
            Template::set_message('Anda tidak memiliki akses untuk mereview pelaporan ini.', 'error');
            redirect('expense_petty_cash/approval');
            return;
        }

        // Get pencatatan detail items and evidence for each pencatatan
        $pencatatan_details = [];
        if (!empty($pelaporan->pencatatan_list)) {
            foreach ($pelaporan->pencatatan_list as $pencatatan) {
                // Get detail items
                $this->db->select('d.*, c.nama as coa_nama');
                $this->db->from('tr_expense_petty_cash_detail d');
                $this->db->join(DBACC . '.coa_master c', 'c.no_perkiraan = d.coa_code', 'left');
                $this->db->where('d.pencatatan_id', $pencatatan->id);
                $this->db->order_by('d.sort_order', 'asc');
                $detail_items = $this->db->get()->result();

                // Get evidence for each detail
                foreach ($detail_items as &$item) {
                    $this->db->select('*');
                    $this->db->from('tr_expense_petty_cash_evidence');
                    $this->db->where('detail_id', $item->id);
                    $item->evidences = $this->db->get()->result();
                }
                unset($item);

                $pencatatan_details[$pencatatan->id] = $detail_items;
            }
        }

        $data = [
            'pelaporan'          => $pelaporan,
            'pencatatan_details' => $pencatatan_details,
            'budget_info'        => (!empty($pelaporan->header->petty_cash_id))
                ? $this->Expense_petty_cash_model->get_budget_info($pelaporan->header->petty_cash_id)
                : false,
            'coa_list'           => (!empty($pelaporan->header->petty_cash_id))
                ? $this->_get_coa_list($pelaporan->header->petty_cash_id)
                : [],
        ];

        $this->template->set($data);
        $this->template->title('Review Approval Pelaporan');
        $this->template->render('approval/view');
    }

    /**
     * Proses approve pelaporan (POST, AJAX)
     *
     * Konfirmasi + proses approve pelaporan.
     * Mengubah status pelaporan → "approved", pencatatan → "approved",
     * dan trigger integrasi Request Payment.
     *
     * @param int $id Pelaporan ID
     * @return void Output JSON response
     */
    public function approve($id)
    {
        // Check permission
        if (!has_permission($this->approvePermission)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan approval'
            ]);
            return;
        }

        // Validate ID
        if (empty($id)) {
            echo json_encode([
                'status'  => false,
                'message' => 'ID pelaporan tidak valid'
            ]);
            return;
        }

        $current_user_id = $this->auth->user_id();

        // Call model to approve (handles status validation, approver check, and state transition)
        $result = $this->Expense_petty_cash_pelaporan_model->approve_pelaporan($id, $current_user_id);

        if (!$result) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal melakukan approval. Pastikan pelaporan berstatus waiting dan Anda adalah approver yang ditunjuk.'
            ]);
            return;
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Pelaporan berhasil diapprove'
        ]);
    }

    /**
     * Proses reject pelaporan (POST, AJAX)
     *
     * Validasi alasan reject (min 10 chars) dan proses reject pelaporan.
     * Mengubah status pelaporan → "reject", pencatatan → "draft".
     *
     * @param int $id Pelaporan ID
     * @return void Output JSON response
     */
    public function reject($id)
    {
        // Check permission
        if (!has_permission($this->approvePermission)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan reject'
            ]);
            return;
        }

        // Validate ID
        if (empty($id)) {
            echo json_encode([
                'status'  => false,
                'message' => 'ID pelaporan tidak valid'
            ]);
            return;
        }

        // Get alasan from POST
        $alasan = $this->input->post('alasan');

        // Validate alasan is provided and min 10 characters
        if (empty($alasan)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Alasan reject wajib diisi'
            ]);
            return;
        }

        $alasan = trim($alasan);
        $alasan_length = mb_strlen($alasan);

        if ($alasan_length < 10) {
            echo json_encode([
                'status'  => false,
                'message' => 'Alasan reject wajib diisi minimal 10 karakter'
            ]);
            return;
        }

        if ($alasan_length > 500) {
            echo json_encode([
                'status'  => false,
                'message' => 'Alasan reject maksimal 500 karakter'
            ]);
            return;
        }

        $current_user_id = $this->auth->user_id();

        // Call model to reject (handles status validation, approver check, and state transition)
        $result = $this->Expense_petty_cash_pelaporan_model->reject_pelaporan($id, $alasan, $current_user_id);

        if (!$result) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal melakukan reject. Pastikan pelaporan berstatus waiting dan Anda adalah approver yang ditunjuk.'
            ]);
            return;
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Pelaporan berhasil direject'
        ]);
    }

    /**
     * Generate PDF pelaporan dan tampilkan di browser (inline)
     *
     * Load data pelaporan beserta pencatatan list, get creator/approver names,
     * render HTML template menggunakan mPDF, output PDF A4 portrait inline.
     *
     * @param int $id Pelaporan ID
     * @return void
     */
    public function print_pelaporan($id)
    {
        $this->auth->restrict($this->viewPermission);

        // Get pelaporan data with linked pencatatan
        $pelaporan = $this->Expense_petty_cash_pelaporan_model->get_pelaporan($id);

        if (!$pelaporan) {
            Template::set_message('Data pelaporan tidak ditemukan.', 'error');
            redirect('expense_petty_cash/pelaporan');
            return;
        }

        // Get creator name from users table
        $creator_name = '';
        if (!empty($pelaporan->header->created_by)) {
            $this->db->select('nm_lengkap');
            $this->db->from('users');
            $this->db->where('id_user', $pelaporan->header->created_by);
            $user = $this->db->get()->row();
            if ($user) {
                $creator_name = $user->nm_lengkap;
            }
        }

        // Get approver name from users table
        $approver_name = '';
        if (!empty($pelaporan->header->approver_id)) {
            $this->db->select('nm_lengkap');
            $this->db->from('users');
            $this->db->where('id_user', $pelaporan->header->approver_id);
            $user = $this->db->get()->row();
            if ($user) {
                $approver_name = $user->nm_lengkap;
            }
        }

        // Prepare data for view — render as plain HTML (browser print)
        $data = [
            'pelaporan'     => $pelaporan,
            'creator_name'  => $creator_name,
            'approver_name' => $approver_name,
        ];

        // Load view directly without template wrapper (standalone HTML page)
        $this->load->view('pelaporan/print', $data);
    }

    // =========================================================================
    // AJAX HELPER ENDPOINTS
    // =========================================================================

    /**
     * AJAX: Get budget info real-time
     *
     * Return JSON {budget, budget_terpakai, sisa_budget} untuk real-time display
     * di form pencatatan. Dipanggil saat load form atau perubahan context.
     *
     * @return void Output JSON response
     */
    public function get_budget_info()
    {
        $this->auth->restrict($this->viewPermission);

        $petty_cash_id = $this->input->get_post('petty_cash_id');

        if (empty($petty_cash_id)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Parameter petty_cash_id diperlukan'
            ]);
            return;
        }

        $budget_info = $this->Expense_petty_cash_model->get_budget_info((int) $petty_cash_id);

        if (!$budget_info) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Data budget tidak ditemukan'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data'   => [
                'budget'         => $budget_info->budget,
                'budget_terpakai' => $budget_info->budget_terpakai,
                'sisa_budget'    => $budget_info->sisa_budget,
            ]
        ]);
    }

    /**
     * AJAX: Get COA list untuk Select2 dropdown
     *
     * Return JSON array COA items filtered dari ms_petty_cash_detail
     * berdasarkan petty_cash_id. Digunakan untuk populate Select2 di form pencatatan.
     *
     * @return void Output JSON response
     */
    public function get_coa_list()
    {
        $this->auth->restrict($this->viewPermission);

        $petty_cash_id = $this->input->get_post('petty_cash_id');

        if (empty($petty_cash_id)) {
            echo json_encode([]);
            return;
        }

        $coa_list = $this->_get_coa_list((int) $petty_cash_id);

        echo json_encode($coa_list);
    }

    /**
     * AJAX endpoint: Get list of all master petty cash for modal selection
     *
     * Returns JSON with id, nama, keterangan, total_budget (formatted).
     *
     * @return void Echoes JSON {status: 0|1, data: [...]}
     */
    public function get_petty_cash_list()
    {
        $this->auth->restrict($this->addPermission);

        $this->db->select('id, nama, keterangan, total_budget');
        $this->db->from('ms_petty_cash');
        $this->db->order_by('nama', 'asc');
        $query = $this->db->get();
        $results = $query->result();

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id'           => $row->id,
                'nama'         => $row->nama,
                'keterangan'   => $row->keterangan,
                'total_budget' => number_format($row->total_budget, 0, ',', '.'),
            ];
        }

        echo json_encode(['status' => 1, 'data' => $data]);
    }

    // =========================================================================
    // HELPER METHODS (Private)
    // =========================================================================

    /**
     * Get the petty_cash_id for current context
     *
     * Simplified approach: use first active record from ms_petty_cash.
     * In future iterations, this can be extended to use session/URL/user assignment.
     *
     * @return int|null
     */
    private function _get_petty_cash_id()
    {
        $this->db->select('id');
        $this->db->from('ms_petty_cash');
        $this->db->order_by('id', 'asc');
        $this->db->limit(1);
        $row = $this->db->get()->row();

        return $row ? (int) $row->id : null;
    }

    /**
     * Get COA list from ms_petty_cash_detail joined with DBACC coa_master
     *
     * Returns COA options filtered to only those registered in the master petty cash detail.
     * Format: array of objects with coa_code and display_name (code - nama).
     *
     * @param int|null $petty_cash_id
     * @return array
     */
    private function _get_coa_list($petty_cash_id)
    {
        if (empty($petty_cash_id)) {
            return [];
        }

        $this->db->select('d.coa_code, d.jenis_pengeluaran, c.nama as coa_nama');
        $this->db->from('ms_petty_cash_detail d');
        $this->db->join(DBACC . '.coa_master c', 'c.no_perkiraan = d.coa_code', 'left');
        $this->db->where('d.petty_cash_id', $petty_cash_id);
        $this->db->order_by('d.coa_code', 'asc');
        $query = $this->db->get();

        return $query->result();
    }

    /**
     * Upload file evidence via AJAX
     *
     * Validasi format file (png, jpg, pdf, xlsx, xls) dan ukuran (max 5 MB).
     * Generate encrypted filename menggunakan md5(uniqid + time).
     * Simpan file ke assets/expense_petty_cash/.
     *
     * @return void Output JSON response
     */
    public function upload_evidence()
    {
        // Check permission
        if (!has_permission($this->addPermission) && !has_permission($this->managePermission)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Anda tidak memiliki akses untuk upload file'
            ]);
            return;
        }

        // Validate file is uploaded
        if (empty($_FILES['evidence_file']['name'])) {
            echo json_encode([
                'status'  => false,
                'message' => 'Tidak ada file yang diupload'
            ]);
            return;
        }

        // Get original filename and extension
        $original_name = $_FILES['evidence_file']['name'];
        $file_size     = $_FILES['evidence_file']['size'];
        $extension     = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        // Validate extension
        $allowed_types = ['png', 'jpg', 'jpeg', 'pdf', 'xlsx', 'xls'];
        if (!in_array($extension, $allowed_types)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Format file tidak valid. Hanya diizinkan: png, jpg, pdf, xlsx, xls'
            ]);
            return;
        }

        // Validate file size (max 5 MB = 5,242,880 bytes)
        if ($file_size > 5242880) {
            echo json_encode([
                'status'  => false,
                'message' => 'Ukuran file melebihi batas maksimal 5 MB'
            ]);
            return;
        }

        // Generate encrypted filename
        $encrypted_name = md5(uniqid(mt_rand(), true) . time()) . '.' . $extension;

        // Set upload path and config
        $upload_path = FCPATH . 'assets/expense_petty_cash/';

        // Ensure directory exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'png|jpg|jpeg|pdf|xlsx|xls';
        $config['max_size']      = 5120; // KB
        $config['encrypt_name']  = false;
        $config['file_name']     = $encrypted_name;
        $config['overwrite']     = false;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('evidence_file')) {
            echo json_encode([
                'status'  => false,
                'message' => $this->upload->display_errors('', '')
            ]);
            return;
        }

        // Upload success
        echo json_encode([
            'status' => true,
            'data'   => [
                'original_name'  => $original_name,
                'encrypted_name' => $encrypted_name,
                'file_type'      => $extension,
                'file_size'      => $file_size
            ]
        ]);
    }

    /**
     * Hapus pencatatan via AJAX
     *
     * Validasi permission, lalu delegasikan ke model yang menangani
     * validasi status, constraint pelaporan, dan cascade delete.
     *
     * @param int|null $id Pencatatan ID
     * @return void Output JSON response {status: 0|1, msg: string}
     */
    public function delete($id = null)
    {
        // Check permission
        if (!has_permission($this->deletePermission)) {
            echo json_encode(['status' => 0, 'msg' => 'Anda tidak memiliki akses untuk menghapus data.']);
            return;
        }

        // Validate ID is provided
        if (empty($id)) {
            echo json_encode(['status' => 0, 'msg' => 'ID pencatatan tidak valid.']);
            return;
        }

        // Call model to handle deletion (status validation, pelaporan constraint, cascade delete)
        $result = $this->Expense_petty_cash_model->delete_pencatatan($id);

        echo json_encode($result);
    }

    /**
     * Simpan pencatatan (create atau update)
     *
     * Menerima POST data, validasi server-side, generate no_pencatatan (jika create),
     * simpan ke database, dan buat jurnal otomatis.
     * Return JSON response dengan SweetAlert2 notification data.
     *
     * @return void Output JSON response
     */
    public function save()
    {
        // Check permission
        if (!has_permission($this->addPermission) && !has_permission($this->managePermission)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Anda tidak memiliki akses untuk menyimpan data'
            ]);
            return;
        }

        // Get POST data
        $id            = $this->input->post('id') ?: null;
        $company       = $this->input->post('company');
        $tanggal       = date('Y-m-d'); // Server-enforced: selalu hari ini, tidak terima input user
        $request_by    = $this->input->post('request_by');
        $keterangan    = $this->input->post('keterangan');
        $petty_cash_id = $this->input->post('petty_cash_id');
        $details_post  = $this->input->post('details') ?: [];
        $evidences_post = $this->input->post('evidences') ?: [];

        // =====================================================================
        // Server-side Validation
        // =====================================================================
        $errors = [];

        // Validate company
        $valid_companies = ['STM', 'VUCA', 'SUSTAIN'];
        if (empty($company) || !in_array($company, $valid_companies)) {
            $errors['company'] = 'Company wajib dipilih (STM, VUCA, atau SUSTAIN)';
        }

        // Validate request_by
        if (empty(trim($request_by))) {
            $errors['request_by'] = 'Request By wajib diisi';
        } elseif (strlen($request_by) > 100) {
            $errors['request_by'] = 'Request By maksimal 100 karakter';
        }

        // Validate at least 1 detail row
        if (empty($details_post) || !is_array($details_post) || count($details_post) === 0) {
            $errors['details'] = 'Detail item harus diisi minimal 1 baris';
        } else {
            // Validate each detail row
            foreach ($details_post as $idx => $item) {
                $row_num = $idx + 1;
                $jumlah  = isset($item['jumlah']) ? (int) $item['jumlah'] : 0;
                $nominal = isset($item['nominal']) ? (int) $item['nominal'] : 0;

                if ($jumlah <= 0) {
                    $errors['details_' . $idx . '_jumlah'] = "Baris {$row_num}: Jumlah harus lebih besar dari 0";
                }
                if ($nominal <= 0) {
                    $errors['details_' . $idx . '_nominal'] = "Baris {$row_num}: Nominal harus lebih besar dari 0";
                }
            }
        }

        // Return validation errors if any
        if (!empty($errors)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Validasi gagal. Silakan periksa kembali data Anda.',
                'errors'  => $errors
            ]);
            return;
        }

        // =====================================================================
        // Generate No Pencatatan (for create only)
        // =====================================================================
        $no_pencatatan = null;
        if (empty($id)) {
            $no_pencatatan = $this->Expense_petty_cash_model->generate_no_pencatatan();

            if ($no_pencatatan === false) {
                echo json_encode([
                    'status'  => false,
                    'message' => 'Kapasitas nomor urut tahun ini telah habis (maks 9999). Tidak dapat menyimpan data baru.'
                ]);
                return;
            }
        }

        // =====================================================================
        // Prepare header data
        // =====================================================================
        $header = [
            'tanggal'       => $tanggal,
            'company'       => $company,
            'request_by'    => trim($request_by),
            'keterangan'    => $keterangan ?: null,
            'petty_cash_id' => $petty_cash_id,
        ];

        // Add no_pencatatan for new records
        if (!empty($no_pencatatan)) {
            $header['no_pencatatan'] = $no_pencatatan;
        }

        // =====================================================================
        // Prepare details array
        // =====================================================================
        $details = [];
        foreach ($details_post as $item) {
            $details[] = [
                'coa_code'    => isset($item['coa_code']) ? $item['coa_code'] : '',
                'pengeluaran' => isset($item['pengeluaran']) ? $item['pengeluaran'] : '',
                'spesifikasi' => isset($item['spesifikasi']) ? $item['spesifikasi'] : null,
                'jumlah'      => (int) $item['jumlah'],
                'nominal'     => (int) $item['nominal'],
            ];
        }

        // =====================================================================
        // Prepare evidences array (keyed by row index)
        // =====================================================================
        $evidences = [];
        if (!empty($evidences_post) && is_array($evidences_post)) {
            foreach ($evidences_post as $row_index => $files) {
                if (!empty($files) && is_array($files)) {
                    $evidences[$row_index] = [];
                    foreach ($files as $file) {
                        $evidences[$row_index][] = [
                            'original_name'  => isset($file['original_name']) ? $file['original_name'] : '',
                            'encrypted_name' => isset($file['encrypted_name']) ? $file['encrypted_name'] : '',
                            'file_type'      => isset($file['file_type']) ? $file['file_type'] : '',
                            'file_size'      => isset($file['file_size']) ? (int) $file['file_size'] : 0,
                        ];
                    }
                }
            }
        }

        // =====================================================================
        // Save pencatatan (create or update)
        // =====================================================================
        $save_id = empty($id) ? null : (int) $id;
        $result  = $this->Expense_petty_cash_model->save_pencatatan($header, $details, $evidences, $save_id);

        if ($result === false) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan data. Silakan coba lagi.'
            ]);
            return;
        }

        // Determine pencatatan_id for journal creation
        $pencatatan_id = empty($id) ? $result : (int) $id;
        $is_edit = !empty($id);

        // =====================================================================
        // Create journal (eventual consistency - Phase 2)
        // =====================================================================
        $journal_result = $this->Expense_petty_cash_model->create_journal_for_pencatatan($pencatatan_id, $is_edit);

        // =====================================================================
        // Return JSON response
        // =====================================================================
        $response = [
            'status'  => true,
            'message' => 'Data berhasil disimpan',
        ];

        // Add warning if journal failed
        if (!$journal_result['success']) {
            $response['warning'] = 'Jurnal gagal disinkronisasi. Dapat di-retry melalui tombol Retry Jurnal.';
        }

        echo json_encode($response);
    }

    /**
     * Retry jurnal yang gagal sinkronisasi (POST, AJAX)
     *
     * Memeriksa permission, validasi pencatatan ada dan journal_status='failed',
     * lalu memanggil model retry_journal untuk re-attempt pembuatan jurnal.
     *
     * @param int|null $id Pencatatan ID
     * @return void Output JSON response {status: '1'|'0', message: string}
     */
    public function retry_journal($id = null)
    {
        // Check permission
        if (!has_permission($this->managePermission)) {
            echo json_encode([
                'status'  => '0',
                'message' => 'Anda tidak memiliki akses untuk melakukan retry jurnal'
            ]);
            return;
        }

        // Validate ID
        if (empty($id)) {
            echo json_encode([
                'status'  => '0',
                'message' => 'ID pencatatan tidak valid'
            ]);
            return;
        }

        // Call model to retry journal
        $result = $this->Expense_petty_cash_model->retry_journal((int) $id);

        if ($result) {
            echo json_encode([
                'status'  => '1',
                'message' => 'Jurnal berhasil disinkronisasi'
            ]);
        } else {
            echo json_encode([
                'status'  => '0',
                'message' => 'Jurnal gagal disinkronisasi. Pastikan pencatatan memiliki status jurnal "failed" dan coba lagi nanti.'
            ]);
        }
    }

    /**
     * Hapus file evidence via AJAX
     *
     * Menghapus record dari tr_expense_petty_cash_evidence
     * dan menghapus file fisik dari folder server.
     *
     * @param int $id ID record evidence
     * @return void Output JSON response
     */
    public function delete_evidence($id)
    {
        // Check permission
        if (!has_permission($this->addPermission) && !has_permission($this->managePermission)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus file'
            ]);
            return;
        }

        // Get the evidence record by ID
        $evidence = $this->db->get_where('tr_expense_petty_cash_evidence', ['id' => $id])->row();

        if (!$evidence) {
            echo json_encode([
                'status'  => false,
                'message' => 'File tidak ditemukan'
            ]);
            return;
        }

        // Get encrypted_name for file deletion
        $encrypted_name = $evidence->encrypted_name;

        // Delete record from database
        $this->db->delete('tr_expense_petty_cash_evidence', ['id' => $id]);

        // Delete physical file from server
        $file_path = FCPATH . 'assets/expense_petty_cash/' . $encrypted_name;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        echo json_encode([
            'status'  => true,
            'message' => 'File berhasil dihapus'
        ]);
    }
}
/* End of file Expense_petty_cash.php */
