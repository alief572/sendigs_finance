<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Laporan Kunjungan Konsultan Controller
 *
 * Handles the visit report module for consultants.
 * Extends Admin_Controller and uses permission-based access control.
 */
class Laporan_kunjungan extends Admin_Controller
{
    // Permission properties
    protected $viewPermission   = 'Laporan_Kunjungan.View';
    protected $addPermission    = 'Laporan_Kunjungan.Add';
    protected $managePermission = 'Laporan_Kunjungan.Manage';
    protected $deletePermission = 'Laporan_Kunjungan.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('laporan_kunjungan/Laporan_kunjungan_model');
        $this->db_consultant = $this->load->database('consultant', TRUE);
        date_default_timezone_set('Asia/Bangkok');
    }

    /**
     * Helper to extract full SPK ID from URI segments.
     * Decodes base64url-encoded ID.
     *
     * @param int $method_segment The segment position of the method name (default 2)
     * @return string The full decoded SPK ID
     */
    private function _get_id_spk_from_uri($method_segment = 2)
    {
        $encoded = $this->uri->segment($method_segment + 1);
        if (empty($encoded)) {
            return '';
        }
        // Decode base64url
        $id_spk = base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded));
        return $id_spk;
    }

    /**
     * Helper to encode SPK ID for use in URLs (base64url).
     *
     * @param string $id_spk The raw SPK ID
     * @return string URL-safe encoded ID
     */
    private function _encode_id_spk($id_spk)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($id_spk));
    }

    /**
     * Index page - displays list of SPK projects assigned to the logged-in konsultan.
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Laporan Kunjungan Konsultan');
        $this->template->render('index');
    }

    /**
     * AJAX endpoint for DataTables server-side processing.
     * Returns JSON with SPK list filtered by the logged-in konsultan.
     */
    public function get_data_spk()
    {
        $this->auth->restrict($this->viewPermission);

        $draw   = $this->input->post('draw');
        $start  = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');

        // Get konsultan ID from session
        $session       = $this->session->userdata('app_session');
        $konsultan_id  = $session['id_user'];
        $is_admin      = $this->auth->is_admin();

        // Build query for paginated data
        $this->db_consultant->select('
            a.id_spk_budgeting,
            a.id_spk_penawaran,
            a.nm_customer,
            a.nm_project_leader,
            a.nm_project,
            a.id_project,
            a.nm_konsultan_1 as nama_konsultan,
            b.nm_sales,
            b.waktu_from,
            b.waktu_to
        ');
        $this->db_consultant->from('kons_tr_spk_budgeting a');
        $this->db_consultant->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        // Admin sees all SPK, konsultan sees only their own
        if (!$is_admin) {
            $this->db_consultant->group_start();
            $this->db_consultant->where('a.id_konsultan_1', $konsultan_id);
            $this->db_consultant->or_where('a.id_konsultan_2', $konsultan_id);
            $this->db_consultant->group_end();
        }
        $this->db_consultant->where('a.sts', 1);

        // Apply search filter
        if (!empty($search['value'])) {
            $this->db_consultant->group_start();
            $this->db_consultant->like('a.id_spk_budgeting', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_customer', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_project', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_project_leader', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_konsultan_1', $search['value'], 'both');
            $this->db_consultant->group_end();
        }

        $this->db_consultant->order_by('a.id_spk_budgeting', 'desc');
        $this->db_consultant->limit($length, $start);
        $get_data = $this->db_consultant->get();

        // Build query for total count (with same filters)
        $this->db_consultant->select('COUNT(*) as total');
        $this->db_consultant->from('kons_tr_spk_budgeting a');
        $this->db_consultant->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        if (!$is_admin) {
            $this->db_consultant->group_start();
            $this->db_consultant->where('a.id_konsultan_1', $konsultan_id);
            $this->db_consultant->or_where('a.id_konsultan_2', $konsultan_id);
            $this->db_consultant->group_end();
        }
        $this->db_consultant->where('a.sts', 1);

        if (!empty($search['value'])) {
            $this->db_consultant->group_start();
            $this->db_consultant->like('a.id_spk_budgeting', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_customer', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_project', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_project_leader', $search['value'], 'both');
            $this->db_consultant->or_like('a.nm_konsultan_1', $search['value'], 'both');
            $this->db_consultant->group_end();
        }

        $count_filtered = $this->db_consultant->get()->row()->total;

        // Total records (without search filter)
        $this->db_consultant->select('COUNT(*) as total');
        $this->db_consultant->from('kons_tr_spk_budgeting a');
        if (!$is_admin) {
            $this->db_consultant->group_start();
            $this->db_consultant->where('a.id_konsultan_1', $konsultan_id);
            $this->db_consultant->or_where('a.id_konsultan_2', $konsultan_id);
            $this->db_consultant->group_end();
        }
        $this->db_consultant->where('a.sts', 1);
        $count_all = $this->db_consultant->get()->row()->total;

        // Build result array
        $hasil = [];
        $no    = $start;

        foreach ($get_data->result() as $item) {
            $no++;

            // Format target selesai
            $target_selesai = '-';
            if (!empty($item->waktu_to)) {
                $target_selesai = date('d-m-Y', strtotime($item->waktu_to));
            }

            // Action buttons - encode id_spk with base64url for safe URLs
            $encoded_spk = $this->_encode_id_spk($item->id_spk_budgeting);
            $btn_view = '<a href="' . base_url('laporan_kunjungan/view/' . $encoded_spk) . '" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>';

            // Check if draft exists - show pencil (edit draft) or plus (new visit)
            $this->db_consultant->select('id');
            $this->db_consultant->from('lk_visit_header');
            $this->db_consultant->where('id_spk_budgeting', $item->id_spk_budgeting);
            $this->db_consultant->where('status', 'draft');
            $this->db_consultant->order_by('id', 'desc');
            $this->db_consultant->limit(1);
            $draft_query = $this->db_consultant->get();

            if ($draft_query->num_rows() > 0) {
                // Draft exists - show pencil icon linking to edit draft
                $draft_id = $draft_query->row()->id;
                $encoded_draft_id = $this->_encode_id_spk((string)$draft_id);
                $btn_visit_or_edit = '<a href="' . base_url('laporan_kunjungan/edit/' . $encoded_draft_id) . '" class="btn btn-sm btn-warning" title="Edit Draft" style="margin-left: 3px;"><i class="fa fa-pencil"></i></a>';
            } else {
                // No draft - show plus icon linking to new visit
                $btn_visit_or_edit = '<a href="' . base_url('laporan_kunjungan/visit/' . $encoded_spk) . '" class="btn btn-sm btn-success" title="Kunjungan Baru" style="margin-left: 3px;"><i class="fa fa-plus"></i></a>';
            }

            $btn_report = '<a href="' . base_url('laporan_kunjungan/report/' . $encoded_spk) . '" class="btn btn-sm btn-primary" title="Report" style="margin-left: 3px;"><i class="fa fa-file-text"></i></a>';

            $option = $btn_view . $btn_visit_or_edit . $btn_report;

            $hasil[] = [
                'no'                => $no,
                'id_spk_budgeting'  => $item->id_spk_budgeting,
                'nm_customer'       => $item->nm_customer,
                'nm_project'        => $item->nm_project,
                'nm_project_leader' => ucfirst($item->nm_project_leader),
                'nama_konsultan'    => ucfirst($item->nama_konsultan),
                'target_selesai'    => $target_selesai,
                'option'            => $option
            ];
        }

        echo json_encode([
            'draw'            => intval($draw),
            'recordsTotal'    => intval($count_all),
            'recordsFiltered' => intval($count_filtered),
            'data'            => $hasil
        ]);
    }

    /**
     * View SPK project detail (read-only).
     * Displays project info and mandays allocation/usage.
     *
     * @param string $id_spk SPK budgeting ID (pipe-encoded)
     */
    public function view()
    {
        $this->auth->restrict($this->viewPermission);

        // Get full id_spk from URI segments (handles slashes in ID)
        $id_spk = $this->_get_id_spk_from_uri(2);

        if (empty($id_spk)) {
            $this->session->set_flashdata('message', 'ID SPK tidak valid.');
            redirect('laporan_kunjungan');
            return;
        }

        // Load SPK detail
        $spk_detail = $this->Laporan_kunjungan_model->get_spk_detail($id_spk);

        if (!$spk_detail) {
            $this->session->set_flashdata('message', 'Data SPK tidak ditemukan.');
            redirect('laporan_kunjungan');
            return;
        }

        // Load mandays info
        $mandays_allocated = $this->Laporan_kunjungan_model->get_mandays_allocated($id_spk);
        $mandays_used      = $this->Laporan_kunjungan_model->get_mandays_used($id_spk);
        $mandays_remaining = $mandays_allocated - $mandays_used;

        // Set data for view
        $this->template->set('spk_detail', $spk_detail);
        $this->template->set('mandays_allocated', $mandays_allocated);
        $this->template->set('mandays_used', $mandays_used);
        $this->template->set('mandays_remaining', $mandays_remaining);
        $this->template->set('id_spk', $id_spk);

        $this->template->title('Detail Project SPK');
        $this->template->render('view');
    }

    /**
     * Visit session form - create a new visit report for a project.
     * Displays project info, kegiatan list, previous action plans, and mandays info.
     *
     * @param string $id_spk SPK budgeting ID (pipe-encoded)
     */
    public function visit()
    {
        $this->auth->restrict($this->addPermission);

        // Get full id_spk from URI segments (handles slashes in ID)
        $id_spk = $this->_get_id_spk_from_uri(2);

        // Check if active draft exists for this SPK - redirect to edit instead of showing empty form
        $existing_draft = $this->Laporan_kunjungan_model->get_active_draft($id_spk);
        if ($existing_draft) {
            $encoded_draft_id = $this->_encode_id_spk((string)$existing_draft->id);
            redirect('laporan_kunjungan/edit/' . $encoded_draft_id);
            return;
        }

        // Load SPK detail
        $spk_detail = $this->Laporan_kunjungan_model->get_spk_detail($id_spk);

        if (!$spk_detail) {
            $this->session->set_flashdata('message', 'Data SPK tidak ditemukan.');
            redirect('laporan_kunjungan');
            return;
        }

        // Load kegiatan list from SPK
        $kegiatan_list = $this->Laporan_kunjungan_model->get_kegiatan_spk($id_spk);

        // Load previous action plans for follow-up
        $previous_action_plans = $this->Laporan_kunjungan_model->get_previous_action_plans($id_spk);

        // Load mandays info
        $mandays_allocated = $this->Laporan_kunjungan_model->get_mandays_allocated($id_spk);
        $mandays_used      = $this->Laporan_kunjungan_model->get_mandays_used($id_spk);
        $mandays_remaining = $mandays_allocated - $mandays_used;

        // Set data for view
        $this->template->set('spk_detail', $spk_detail);
        $this->template->set('kegiatan_list', $kegiatan_list);
        $this->template->set('previous_action_plans', $previous_action_plans);
        $this->template->set('mandays_allocated', $mandays_allocated);
        $this->template->set('mandays_used', $mandays_used);
        $this->template->set('mandays_remaining', $mandays_remaining);
        $this->template->set('id_spk', $id_spk);

        $this->template->title('Kunjungan Baru');
        $this->template->render('visit');
    }

    /**
     * AJAX endpoint to get kegiatan list for a project.
     * Returns JSON array of kegiatan from SPK aktifitas.
     *
     * @param string $id_spk SPK budgeting ID (pipe-encoded)
     */
    public function get_kegiatan()
    {
        $this->auth->restrict($this->viewPermission);

        // Get full id_spk from URI segments
        $id_spk = $this->_get_id_spk_from_uri(2);

        $kegiatan = $this->Laporan_kunjungan_model->get_kegiatan_spk($id_spk);

        if ($kegiatan) {
            echo json_encode([
                'status' => true,
                'data'   => $kegiatan
            ]);
        } else {
            echo json_encode([
                'status'  => true,
                'data'    => [],
                'message' => 'Tidak ada kegiatan tersedia untuk project ini.'
            ]);
        }
    }

    /**
     * AJAX endpoint to record the start time of a visit session.
     * Records the server's current time and returns it as JSON.
     *
     * @return void Outputs JSON response
     */
    public function start_session()
    {
        $this->auth->restrict($this->addPermission);

        $start_datetime = date('Y-m-d H:i');

        echo json_encode([
            'status'     => true,
            'message'    => 'Sesi kunjungan dimulai.',
            'start_time' => $start_datetime
        ]);
    }

    /**
     * AJAX endpoint to record the finish time of a visit session.
     * Receives start_time (DATETIME format "YYYY-MM-DD HH:MM") from POST,
     * records current server datetime as finish_time,
     * validates finish > start, calculates duration_minutes and mandays_used.
     *
     * @return void Outputs JSON response
     */
    public function finish_session()
    {
        $this->auth->restrict($this->addPermission);

        $start_time = $this->input->post('start_time');

        // Validate start_time is provided and matches DATETIME format "YYYY-MM-DD HH:MM"
        if (!$start_time || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $start_time)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Format start time tidak valid.'
            ]);
            return;
        }

        $finish_datetime = date('Y-m-d H:i');

        $start_timestamp  = strtotime($start_time . ':00');
        $finish_timestamp = strtotime($finish_datetime . ':00');

        // Validate finish > start
        if ($finish_timestamp <= $start_timestamp) {
            echo json_encode([
                'status'  => false,
                'message' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ]);
            return;
        }

        // Calculate duration in minutes (supports multi-day)
        $duration_minutes = (int)(($finish_timestamp - $start_timestamp) / 60);

        // Calculate mandays_used: duration_minutes / 480 (8 hours per day), rounded to 2 decimal places
        $mandays_used = round($duration_minutes / 480, 2);

        echo json_encode([
            'status'           => true,
            'message'          => 'Sesi kunjungan selesai.',
            'finish_time'      => $finish_datetime,
            'duration_minutes' => $duration_minutes,
            'mandays_used'     => $mandays_used
        ]);
    }

    /**
     * AJAX endpoint to save visit report as draft.
     * No required field validation — saves whatever data is provided.
     * Uses transaction via model methods for atomicity.
     *
     * Expects JSON POST body with structure:
     * {
     *   "id_spk": "...",
     *   "start_time": "YYYY-MM-DD HH:MM",
     *   "finish_time": "YYYY-MM-DD HH:MM",
     *   "duration_minutes": int,
     *   "mandays_used": float,
     *   "potensi_improvement": "...",
     *   "hasil_improvement": "...",
     *   "kegiatan": [...]
     * }
     *
     * @return void Outputs JSON response
     */
    public function save_draft()
    {
        $this->auth->restrict($this->addPermission);

        // Read JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data input tidak valid.'
            ]);
            return;
        }

        // Get konsultan info from user data
        $konsultan_id   = $this->auth->user_id();
        $userData        = $this->auth->userdata();
        $konsultan_name = isset($userData->nm_lengkap) ? $userData->nm_lengkap : (isset($userData->username) ? $userData->username : '');

        $now = date('Y-m-d H:i:s');

        // Extract start_time and finish_time inputs (format "YYYY-MM-DD HH:MM")
        $start_time_input = isset($input['start_time']) && $input['start_time'] !== '' ? $input['start_time'] : null;
        $finish_time_input = isset($input['finish_time']) && $input['finish_time'] !== '' ? $input['finish_time'] : null;

        // Prepare visit header data
        $header_data = [
            'id_spk_budgeting'    => isset($input['id_spk']) ? $input['id_spk'] : '',
            'konsultan_id'        => $konsultan_id,
            'konsultan_name'      => $konsultan_name,
            'visit_date'          => $start_time_input ? substr($start_time_input, 0, 10) : date('Y-m-d'),
            'start_time'          => $start_time_input ? $start_time_input . ':00' : null,
            'finish_time'         => $finish_time_input ? $finish_time_input . ':00' : null,
            'duration_minutes'    => isset($input['duration_minutes']) ? (int) $input['duration_minutes'] : null,
            'mandays_used'        => isset($input['mandays_used']) ? (float) $input['mandays_used'] : null,
            'potensi_improvement' => isset($input['potensi_improvement']) ? $input['potensi_improvement'] : null,
            'hasil_improvement'   => isset($input['hasil_improvement']) ? $input['hasil_improvement'] : null,
            'status'              => 'draft',
            'created_at'          => $now,
            'created_by'          => $konsultan_id,
        ];

        // Create visit header via model (uses transaction)
        $visit_id = $this->Laporan_kunjungan_model->create_visit($header_data);

        if (!$visit_id) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan data kunjungan. Silakan coba lagi.'
            ]);
            return;
        }

        // Save kegiatan and action plans if provided
        if (!empty($input['kegiatan']) && is_array($input['kegiatan'])) {
            $kegiatan_ids = $this->Laporan_kunjungan_model->save_kegiatan($visit_id, $input['kegiatan']);

            if ($kegiatan_ids === false) {
                echo json_encode([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data kegiatan. Silakan coba lagi.'
                ]);
                return;
            }

            // Save action plans for each kegiatan
            foreach ($input['kegiatan'] as $index => $kegiatan) {
                if (!empty($kegiatan['action_plans']) && is_array($kegiatan['action_plans']) && isset($kegiatan_ids[$index])) {
                    $plans = [];
                    foreach ($kegiatan['action_plans'] as $plan) {
                        $plans[] = [
                            'visit_id'    => $visit_id,
                            'description' => isset($plan['description']) ? $plan['description'] : '',
                            'pic'         => isset($plan['pic']) ? $plan['pic'] : '',
                            'due_date'    => isset($plan['due_date']) ? $plan['due_date'] : null,
                            'status'      => isset($plan['status']) ? $plan['status'] : 'Progress',
                        ];
                    }

                    $result = $this->Laporan_kunjungan_model->save_action_plans($kegiatan_ids[$index], $plans);

                    if ($result === false) {
                        echo json_encode([
                            'status'  => false,
                            'message' => 'Gagal menyimpan action plan. Silakan coba lagi.'
                        ]);
                        return;
                    }
                }
            }
        }

        echo json_encode([
            'status'       => true,
            'message'      => 'Draft berhasil disimpan.',
            'visit_id'     => (int) $visit_id,
            'redirect_url' => base_url('laporan_kunjungan/edit/' . $this->_encode_id_spk((string)$visit_id))
        ]);
    }

    /**
     * AJAX endpoint to save visit report as final.
     * Validates all required fields before saving.
     * Uses transaction via model methods for atomicity.
     *
     * Server-side validation:
     * - start_time and finish_time required, start < finish
     * - visit_date required
     * - At least 1 kegiatan required
     * - At least 1 action plan per kegiatan
     * - Character limits: nama_kegiatan (500), description (500), pic (100), improvement fields (2000)
     * - due_date >= visit_date for each action plan
     *
     * @return void Outputs JSON response
     */
    public function save_final()
    {
        $this->auth->restrict($this->addPermission);

        // Read JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data input tidak valid.'
            ]);
            return;
        }

        // =====================================================================
        // Server-side validation
        // =====================================================================
        $errors = [];

        // Validate start_time and finish_time (format "YYYY-MM-DD HH:MM")
        $start_time  = isset($input['start_time']) ? trim($input['start_time']) : '';
        $finish_time = isset($input['finish_time']) ? trim($input['finish_time']) : '';

        if (empty($start_time)) {
            echo json_encode(['status' => false, 'message' => 'Start time wajib diisi.']);
            return;
        }

        if (empty($finish_time)) {
            echo json_encode(['status' => false, 'message' => 'Finish time wajib diisi.']);
            return;
        }

        // Validate start < finish
        if (strtotime($start_time . ':00') >= strtotime($finish_time . ':00')) {
            $errors[] = 'Start time harus lebih awal dari finish time.';
        }

        // Derive visit_date from start_time date component
        $visit_date = substr($start_time, 0, 10);

        // Validate potensi_improvement character limit (2000)
        if (isset($input['potensi_improvement']) && mb_strlen($input['potensi_improvement']) > 2000) {
            $errors[] = 'Potensi Improvement tidak boleh melebihi 2000 karakter.';
        }

        // Validate hasil_improvement character limit (2000)
        if (isset($input['hasil_improvement']) && mb_strlen($input['hasil_improvement']) > 2000) {
            $errors[] = 'Hasil Improvement tidak boleh melebihi 2000 karakter.';
        }

        // Validate kegiatan: at least 1 required
        $kegiatan_list = isset($input['kegiatan']) && is_array($input['kegiatan']) ? $input['kegiatan'] : [];

        if (empty($kegiatan_list)) {
            $errors[] = 'Minimal 1 kegiatan harus dipilih.';
        } else {
            foreach ($kegiatan_list as $keg_index => $kegiatan) {
                $keg_num = $keg_index + 1;

                // Validate nama_kegiatan (max 500 chars)
                $nama_kegiatan = isset($kegiatan['nama_kegiatan']) ? trim($kegiatan['nama_kegiatan']) : '';
                if (empty($nama_kegiatan)) {
                    $errors[] = "Kegiatan #{$keg_num}: Nama kegiatan wajib diisi.";
                } elseif (mb_strlen($nama_kegiatan) > 500) {
                    $errors[] = "Kegiatan #{$keg_num}: Nama kegiatan tidak boleh melebihi 500 karakter.";
                }

                // Validate action plans: at least 1 per kegiatan
                $action_plans = isset($kegiatan['action_plans']) && is_array($kegiatan['action_plans']) ? $kegiatan['action_plans'] : [];

                if (empty($action_plans)) {
                    $errors[] = "Kegiatan #{$keg_num}: Minimal 1 action plan harus diisi.";
                } else {
                    foreach ($action_plans as $ap_index => $plan) {
                        $ap_num = $ap_index + 1;

                        // Validate description (max 500 chars)
                        $description = isset($plan['description']) ? trim($plan['description']) : '';
                        if (empty($description)) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Deskripsi wajib diisi.";
                        } elseif (mb_strlen($description) > 500) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Deskripsi tidak boleh melebihi 500 karakter.";
                        }

                        // Validate PIC (max 100 chars)
                        $pic = isset($plan['pic']) ? trim($plan['pic']) : '';
                        if (empty($pic)) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: PIC wajib diisi.";
                        } elseif (mb_strlen($pic) > 100) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: PIC tidak boleh melebihi 100 karakter.";
                        }

                        // Validate due_date: required and must be >= visit_date
                        $due_date = isset($plan['due_date']) ? trim($plan['due_date']) : '';
                        if (empty($due_date)) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Due date wajib diisi.";
                        } elseif (!empty($visit_date) && $due_date < $visit_date) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Due date harus sama atau setelah tanggal kunjungan.";
                        }
                    }
                }
            }
        }

        // Return validation errors if any
        if (!empty($errors)) {
            echo json_encode([
                'status'  => false,
                'message' => implode("\n", $errors)
            ]);
            return;
        }

        // =====================================================================
        // Save data
        // =====================================================================

        // Get konsultan info from user data
        $konsultan_id   = $this->auth->user_id();
        $userData        = $this->auth->userdata();
        $konsultan_name = isset($userData->nm_lengkap) ? $userData->nm_lengkap : (isset($userData->username) ? $userData->username : '');

        $now = date('Y-m-d H:i:s');

        // Prepare visit header data
        $header_data = [
            'id_spk_budgeting'    => isset($input['id_spk']) ? $input['id_spk'] : '',
            'konsultan_id'        => $konsultan_id,
            'konsultan_name'      => $konsultan_name,
            'visit_date'          => $visit_date,
            'start_time'          => $start_time . ':00',
            'finish_time'         => $finish_time . ':00',
            'duration_minutes'    => isset($input['duration_minutes']) ? (int) $input['duration_minutes'] : null,
            'mandays_used'        => isset($input['mandays_used']) ? (float) $input['mandays_used'] : null,
            'potensi_improvement' => isset($input['potensi_improvement']) ? $input['potensi_improvement'] : null,
            'hasil_improvement'   => isset($input['hasil_improvement']) ? $input['hasil_improvement'] : null,
            'status'              => 'final',
            'created_at'          => $now,
            'created_by'          => $konsultan_id,
        ];

        // Create visit header via model (uses transaction)
        $visit_id = $this->Laporan_kunjungan_model->create_visit($header_data);

        if (!$visit_id) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan data kunjungan. Silakan coba lagi.'
            ]);
            return;
        }

        // Save kegiatan and action plans
        $kegiatan_ids = $this->Laporan_kunjungan_model->save_kegiatan($visit_id, $kegiatan_list);

        if ($kegiatan_ids === false) {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan data kegiatan. Silakan coba lagi.'
            ]);
            return;
        }

        // Save action plans for each kegiatan
        foreach ($kegiatan_list as $index => $kegiatan) {
            if (!empty($kegiatan['action_plans']) && is_array($kegiatan['action_plans']) && isset($kegiatan_ids[$index])) {
                $plans = [];
                foreach ($kegiatan['action_plans'] as $plan) {
                    $plans[] = [
                        'visit_id'    => $visit_id,
                        'description' => trim($plan['description']),
                        'pic'         => trim($plan['pic']),
                        'due_date'    => trim($plan['due_date']),
                        'status'      => isset($plan['status']) ? $plan['status'] : 'Progress',
                    ];
                }

                $result = $this->Laporan_kunjungan_model->save_action_plans($kegiatan_ids[$index], $plans);

                if ($result === false) {
                    echo json_encode([
                        'status'  => false,
                        'message' => 'Gagal menyimpan action plan. Silakan coba lagi.'
                    ]);
                    return;
                }
            }
        }

        echo json_encode([
            'status'   => true,
            'message'  => 'Laporan kunjungan berhasil disimpan.',
            'visit_id' => (int) $visit_id
        ]);
    }

    /**
     * Edit a draft visit report.
     * Loads existing visit data and renders the edit form.
     * Rejects if visit status is already 'final'.
     *
     * @param int $id_visit Visit header ID
     */
    public function edit($encoded_id = '')
    {
        $this->auth->restrict($this->managePermission);

        // Decode base64url visit ID
        $id_visit = base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded_id));

        if (empty($id_visit) || !is_numeric($id_visit)) {
            $this->session->set_flashdata('message', 'ID kunjungan tidak valid.');
            redirect('laporan_kunjungan');
            return;
        }

        // Load visit data with nested kegiatan and action plans
        $visit = $this->Laporan_kunjungan_model->get_visit($id_visit);

        if (!$visit) {
            $this->session->set_flashdata('message', 'Data kunjungan tidak ditemukan.');
            redirect('laporan_kunjungan');
            return;
        }

        // Verify ownership (only creator or admin can edit)
        if (!$this->auth->is_admin() && $visit->konsultan_id != $this->auth->user_id()) {
            $this->session->set_flashdata('message', 'Anda tidak memiliki akses untuk mengedit laporan ini.');
            redirect('laporan_kunjungan');
            return;
        }

        // Reject edit if status is already 'final'
        if ($visit->status === 'final') {
            $this->session->set_flashdata('message', 'Laporan dengan status Final tidak dapat diedit.');
            redirect('laporan_kunjungan');
            return;
        }

        $id_spk = $visit->id_spk_budgeting;

        // Load SPK detail
        $spk_detail = $this->Laporan_kunjungan_model->get_spk_detail($id_spk);

        if (!$spk_detail) {
            $this->session->set_flashdata('message', 'Data SPK tidak ditemukan.');
            redirect('laporan_kunjungan');
            return;
        }

        // Load kegiatan list from SPK
        $kegiatan_list = $this->Laporan_kunjungan_model->get_kegiatan_spk($id_spk);

        // Load previous action plans for follow-up
        $previous_action_plans = $this->Laporan_kunjungan_model->get_previous_action_plans($id_spk);

        // Load mandays info
        $mandays_allocated = $this->Laporan_kunjungan_model->get_mandays_allocated($id_spk);
        $mandays_used      = $this->Laporan_kunjungan_model->get_mandays_used($id_spk);
        $mandays_remaining = $mandays_allocated - $mandays_used;

        // Set data for view
        $this->template->set('visit', $visit);
        $this->template->set('spk_detail', $spk_detail);
        $this->template->set('kegiatan_list', $kegiatan_list);
        $this->template->set('previous_action_plans', $previous_action_plans);
        $this->template->set('mandays_allocated', $mandays_allocated);
        $this->template->set('mandays_used', $mandays_used);
        $this->template->set('mandays_remaining', $mandays_remaining);
        $this->template->set('id_spk', $id_spk);

        $this->template->title('Edit Laporan Kunjungan');
        $this->template->render('edit');
    }

    /**
     * AJAX endpoint to update a draft visit report.
     * No required field validation — updates whatever data is provided.
     * Uses transaction via model methods for atomicity.
     *
     * Expects JSON POST body with structure:
     * {
     *   "visit_id": int,
     *   "id_spk": "...",
     *   "start_time": "HH:mm",
     *   "finish_time": "HH:mm",
     *   "duration_minutes": int,
     *   "mandays_used": float,
     *   "visit_date": "YYYY-MM-DD",
     *   "potensi_improvement": "...",
     *   "hasil_improvement": "...",
     *   "kegiatan": [...]
     * }
     *
     * @return void Outputs JSON response
     */
    public function update_draft()
    {
        $this->auth->restrict($this->managePermission);

        // Read JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['visit_id'])) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data input tidak valid.'
            ]);
            return;
        }

        $visit_id = (int) $input['visit_id'];

        // Load existing visit to verify status
        $visit = $this->Laporan_kunjungan_model->get_visit($visit_id);

        if (!$visit) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data kunjungan tidak ditemukan.'
            ]);
            return;
        }

        // Reject update if status is already 'final'
        if ($visit->status === 'final') {
            echo json_encode([
                'status'  => false,
                'message' => 'Laporan dengan status Final tidak dapat diedit.'
            ]);
            return;
        }

        $now = date('Y-m-d H:i:s');

        // Extract start_time and finish_time inputs (format "YYYY-MM-DD HH:MM")
        $start_time_input = isset($input['start_time']) && $input['start_time'] !== '' ? $input['start_time'] : null;
        $finish_time_input = isset($input['finish_time']) && $input['finish_time'] !== '' ? $input['finish_time'] : null;

        // Prepare visit header update data
        $header_data = [
            'visit_date'          => $start_time_input ? substr($start_time_input, 0, 10) : $visit->visit_date,
            'start_time'          => $start_time_input ? $start_time_input . ':00' : $visit->start_time,
            'finish_time'         => $finish_time_input ? $finish_time_input . ':00' : $visit->finish_time,
            'duration_minutes'    => isset($input['duration_minutes']) ? (int) $input['duration_minutes'] : $visit->duration_minutes,
            'mandays_used'        => isset($input['mandays_used']) ? (float) $input['mandays_used'] : $visit->mandays_used,
            'potensi_improvement' => isset($input['potensi_improvement']) ? $input['potensi_improvement'] : $visit->potensi_improvement,
            'hasil_improvement'   => isset($input['hasil_improvement']) ? $input['hasil_improvement'] : $visit->hasil_improvement,
            'updated_at'          => $now,
        ];

        // Start transaction
        $this->db->trans_begin();

        // Update visit header
        $update_result = $this->Laporan_kunjungan_model->update_visit($visit_id, $header_data);

        if (!$update_result) {
            $this->db->trans_rollback();
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal mengupdate data kunjungan. Silakan coba lagi.'
            ]);
            return;
        }

        // Save kegiatan and action plans if provided
        if (isset($input['kegiatan']) && is_array($input['kegiatan'])) {
            $kegiatan_ids = $this->Laporan_kunjungan_model->save_kegiatan($visit_id, $input['kegiatan']);

            if ($kegiatan_ids === false) {
                $this->db->trans_rollback();
                echo json_encode([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data kegiatan. Silakan coba lagi.'
                ]);
                return;
            }

            // Save action plans for each kegiatan
            foreach ($input['kegiatan'] as $index => $kegiatan) {
                if (!empty($kegiatan['action_plans']) && is_array($kegiatan['action_plans']) && isset($kegiatan_ids[$index])) {
                    $plans = [];
                    foreach ($kegiatan['action_plans'] as $plan) {
                        $plans[] = [
                            'visit_id'    => $visit_id,
                            'description' => isset($plan['description']) ? $plan['description'] : '',
                            'pic'         => isset($plan['pic']) ? $plan['pic'] : '',
                            'due_date'    => isset($plan['due_date']) ? $plan['due_date'] : null,
                            'status'      => isset($plan['status']) ? $plan['status'] : 'Progress',
                        ];
                    }

                    $result = $this->Laporan_kunjungan_model->save_action_plans($kegiatan_ids[$index], $plans);

                    if ($result === false) {
                        $this->db->trans_rollback();
                        echo json_encode([
                            'status'  => false,
                            'message' => 'Gagal menyimpan action plan. Silakan coba lagi.'
                        ]);
                        return;
                    }
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan perubahan. Silakan coba lagi.'
            ]);
            return;
        }

        echo json_encode([
            'status'   => true,
            'message'  => 'Draft berhasil diupdate.',
            'visit_id' => $visit_id
        ]);
    }

    /**
     * AJAX endpoint to toggle action plan status (Progress ↔ Done).
     * Restricts to managePermission.
     *
     * Expects POST data: id (action plan ID), status (new status)
     *
     * @return void Outputs JSON response
     */
    public function update_action_plan_status()
    {
        $this->auth->restrict($this->managePermission);

        $id     = $this->input->post('id');
        $status = $this->input->post('status');

        // Validate input
        if (empty($id) || empty($status)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Parameter tidak lengkap.'
            ]);
            return;
        }

        // Validate status value
        if (!in_array($status, ['Progress', 'Done'])) {
            echo json_encode([
                'status'  => false,
                'message' => 'Status tidak valid.'
            ]);
            return;
        }

        $result = $this->Laporan_kunjungan_model->update_action_plan_status($id, $status);

        if ($result) {
            echo json_encode([
                'status'  => true,
                'message' => 'Status berhasil diubah.'
            ]);
        } else {
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal mengubah status. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Report page - displays cumulative visit report for a project.
     * Shows project header info and mandays summary.
     * Data is loaded via AJAX from get_report_data().
     *
     * @param string $id_spk SPK budgeting ID (pipe-encoded)
     */
    public function report()
    {
        $this->auth->restrict($this->viewPermission);

        // Get full id_spk from URI segments
        $id_spk = $this->_get_id_spk_from_uri(2);

        // Load SPK detail
        $spk_detail = $this->Laporan_kunjungan_model->get_spk_detail($id_spk);

        if (!$spk_detail) {
            $this->session->set_flashdata('message', 'Data SPK tidak ditemukan.');
            redirect('laporan_kunjungan');
            return;
        }

        // Load mandays info
        $mandays_allocated = $this->Laporan_kunjungan_model->get_mandays_allocated($id_spk);
        $mandays_used      = $this->Laporan_kunjungan_model->get_mandays_used($id_spk);
        $mandays_remaining = $mandays_allocated - $mandays_used;

        // Set data for view
        $this->template->set('spk_detail', $spk_detail);
        $this->template->set('mandays_allocated', $mandays_allocated);
        $this->template->set('mandays_used', $mandays_used);
        $this->template->set('mandays_remaining', $mandays_remaining);
        $this->template->set('id_spk', $id_spk);

        $this->template->title('Laporan Kunjungan - Report');
        $this->template->render('report');
    }

    /**
     * AJAX endpoint to get cumulative report data for a project.
     * Returns all finalized visits with kegiatan and action plans as JSON,
     * flattened so each action plan becomes a separate row.
     * Sorted by visit_date DESC.
     *
     * @param string $id_spk SPK budgeting ID (pipe-encoded)
     * @return void Outputs JSON response
     */
    public function get_report_data()
    {
        $this->auth->restrict($this->viewPermission);

        // Get full id_spk from URI segments
        $id_spk = $this->_get_id_spk_from_uri(2);

        // Get all final visits for this project
        $visits = $this->Laporan_kunjungan_model->get_visits_by_project($id_spk);

        if (!$visits) {
            echo json_encode([
                'status'  => true,
                'data'    => [],
                'message' => 'Belum ada laporan kunjungan yang sudah final untuk project ini.'
            ]);
            return;
        }

        // Flatten data: each action plan becomes a row with date, konsultan, kegiatan, action_plan, pic, due_date, status
        $flattened = [];

        foreach ($visits as $visit) {
            if (!empty($visit->kegiatan)) {
                foreach ($visit->kegiatan as $kegiatan) {
                    if (!empty($kegiatan->action_plans)) {
                        foreach ($kegiatan->action_plans as $plan) {
                            $flattened[] = [
                                'visit_id'       => $visit->id,
                                'date'           => $visit->visit_date,
                                'konsultan'      => $visit->konsultan_name,
                                'kegiatan'       => $kegiatan->nama_kegiatan,
                                'action_plan'    => $plan->description,
                                'pic'            => $plan->pic,
                                'due_date'       => $plan->due_date,
                                'status'         => $plan->status,
                                'action_plan_id' => $plan->id,
                            ];
                        }
                    } else {
                        // Kegiatan without action plans — still show the kegiatan row
                        $flattened[] = [
                            'visit_id'       => $visit->id,
                            'date'           => $visit->visit_date,
                            'konsultan'      => $visit->konsultan_name,
                            'kegiatan'       => $kegiatan->nama_kegiatan,
                            'action_plan'    => '-',
                            'pic'            => '-',
                            'due_date'       => '-',
                            'status'         => '-',
                            'action_plan_id' => null,
                        ];
                    }
                }
            }
        }

        echo json_encode([
            'status' => true,
            'data'   => $flattened
        ]);
    }

    /**
     * AJAX endpoint to finalize a draft visit report.
     * Validates all required fields before updating status to 'final'.
     * Uses transaction via model methods for atomicity.
     *
     * Server-side validation (same as save_final):
     * - start_time and finish_time required, start < finish
     * - visit_date required
     * - At least 1 kegiatan required
     * - At least 1 action plan per kegiatan
     * - Character limits: nama_kegiatan (500), description (500), pic (100), improvement fields (2000)
     * - due_date >= visit_date for each action plan
     *
     * @return void Outputs JSON response
     */
    public function update_final()
    {
        $this->auth->restrict($this->managePermission);

        // Read JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['visit_id'])) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data input tidak valid.'
            ]);
            return;
        }

        $visit_id = (int) $input['visit_id'];

        // Load existing visit to verify status
        $visit = $this->Laporan_kunjungan_model->get_visit($visit_id);

        if (!$visit) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data kunjungan tidak ditemukan.'
            ]);
            return;
        }

        // Reject update if status is already 'final'
        if ($visit->status === 'final') {
            echo json_encode([
                'status'  => false,
                'message' => 'Laporan dengan status Final tidak dapat diedit.'
            ]);
            return;
        }

        // =====================================================================
        // Server-side validation (same rules as save_final)
        // =====================================================================
        $errors = [];

        // Validate start_time and finish_time
        $start_time  = isset($input['start_time']) ? trim($input['start_time']) : '';
        $finish_time = isset($input['finish_time']) ? trim($input['finish_time']) : '';

        if (empty($start_time)) {
            $errors[] = 'Start time wajib diisi.';
        }

        if (empty($finish_time)) {
            $errors[] = 'Finish time wajib diisi.';
        }

        // Validate start < finish
        if (!empty($start_time) && !empty($finish_time)) {
            if (strtotime($start_time . ':00') >= strtotime($finish_time . ':00')) {
                $errors[] = 'Start time harus lebih awal dari finish time.';
            }
        }

        // Derive visit_date from start_time date component (consistent with save_draft/save_final)
        $visit_date = !empty($start_time) ? substr($start_time, 0, 10) : '';

        // Validate potensi_improvement character limit (2000)
        if (isset($input['potensi_improvement']) && mb_strlen($input['potensi_improvement']) > 2000) {
            $errors[] = 'Potensi Improvement tidak boleh melebihi 2000 karakter.';
        }

        // Validate hasil_improvement character limit (2000)
        if (isset($input['hasil_improvement']) && mb_strlen($input['hasil_improvement']) > 2000) {
            $errors[] = 'Hasil Improvement tidak boleh melebihi 2000 karakter.';
        }

        // Validate kegiatan: at least 1 required
        $kegiatan_list = isset($input['kegiatan']) && is_array($input['kegiatan']) ? $input['kegiatan'] : [];

        if (empty($kegiatan_list)) {
            $errors[] = 'Minimal 1 kegiatan harus dipilih.';
        } else {
            foreach ($kegiatan_list as $keg_index => $kegiatan) {
                $keg_num = $keg_index + 1;

                // Validate nama_kegiatan (max 500 chars)
                $nama_kegiatan = isset($kegiatan['nama_kegiatan']) ? trim($kegiatan['nama_kegiatan']) : '';
                if (empty($nama_kegiatan)) {
                    $errors[] = "Kegiatan #{$keg_num}: Nama kegiatan wajib diisi.";
                } elseif (mb_strlen($nama_kegiatan) > 500) {
                    $errors[] = "Kegiatan #{$keg_num}: Nama kegiatan tidak boleh melebihi 500 karakter.";
                }

                // Validate action plans: at least 1 per kegiatan
                $action_plans = isset($kegiatan['action_plans']) && is_array($kegiatan['action_plans']) ? $kegiatan['action_plans'] : [];

                if (empty($action_plans)) {
                    $errors[] = "Kegiatan #{$keg_num}: Minimal 1 action plan harus diisi.";
                } else {
                    foreach ($action_plans as $ap_index => $plan) {
                        $ap_num = $ap_index + 1;

                        // Validate description (max 500 chars)
                        $description = isset($plan['description']) ? trim($plan['description']) : '';
                        if (empty($description)) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Deskripsi wajib diisi.";
                        } elseif (mb_strlen($description) > 500) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Deskripsi tidak boleh melebihi 500 karakter.";
                        }

                        // Validate PIC (max 100 chars)
                        $pic = isset($plan['pic']) ? trim($plan['pic']) : '';
                        if (empty($pic)) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: PIC wajib diisi.";
                        } elseif (mb_strlen($pic) > 100) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: PIC tidak boleh melebihi 100 karakter.";
                        }

                        // Validate due_date: required and must be >= visit_date
                        $due_date = isset($plan['due_date']) ? trim($plan['due_date']) : '';
                        if (empty($due_date)) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Due date wajib diisi.";
                        } elseif (!empty($visit_date) && $due_date < $visit_date) {
                            $errors[] = "Kegiatan #{$keg_num}, Action Plan #{$ap_num}: Due date harus sama atau setelah tanggal kunjungan.";
                        }
                    }
                }
            }
        }

        // Return validation errors if any
        if (!empty($errors)) {
            echo json_encode([
                'status'  => false,
                'message' => implode("\n", $errors)
            ]);
            return;
        }

        // =====================================================================
        // Update data
        // =====================================================================

        $now = date('Y-m-d H:i:s');

        // Prepare visit header update data
        $header_data = [
            'visit_date'          => $visit_date,
            'start_time'          => $start_time . ':00',
            'finish_time'         => $finish_time . ':00',
            'duration_minutes'    => isset($input['duration_minutes']) ? (int) $input['duration_minutes'] : null,
            'mandays_used'        => isset($input['mandays_used']) ? (float) $input['mandays_used'] : null,
            'potensi_improvement' => isset($input['potensi_improvement']) ? $input['potensi_improvement'] : null,
            'hasil_improvement'   => isset($input['hasil_improvement']) ? $input['hasil_improvement'] : null,
            'status'              => 'final',
            'updated_at'          => $now,
        ];

        // Start transaction
        $this->db->trans_begin();

        // Update visit header
        $update_result = $this->Laporan_kunjungan_model->update_visit($visit_id, $header_data);

        if (!$update_result) {
            $this->db->trans_rollback();
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal mengupdate data kunjungan. Silakan coba lagi.'
            ]);
            return;
        }

        // Save kegiatan and action plans
        $kegiatan_ids = $this->Laporan_kunjungan_model->save_kegiatan($visit_id, $kegiatan_list);

        if ($kegiatan_ids === false) {
            $this->db->trans_rollback();
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan data kegiatan. Silakan coba lagi.'
            ]);
            return;
        }

        // Save action plans for each kegiatan
        foreach ($kegiatan_list as $index => $kegiatan) {
            if (!empty($kegiatan['action_plans']) && is_array($kegiatan['action_plans']) && isset($kegiatan_ids[$index])) {
                $plans = [];
                foreach ($kegiatan['action_plans'] as $plan) {
                    $plans[] = [
                        'visit_id'    => $visit_id,
                        'description' => trim($plan['description']),
                        'pic'         => trim($plan['pic']),
                        'due_date'    => trim($plan['due_date']),
                        'status'      => isset($plan['status']) ? $plan['status'] : 'Progress',
                    ];
                }

                $result = $this->Laporan_kunjungan_model->save_action_plans($kegiatan_ids[$index], $plans);

                if ($result === false) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        'status'  => false,
                        'message' => 'Gagal menyimpan action plan. Silakan coba lagi.'
                    ]);
                    return;
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal menyimpan perubahan. Silakan coba lagi.'
            ]);
            return;
        }

        echo json_encode([
            'status'   => true,
            'message'  => 'Laporan kunjungan berhasil disimpan.',
            'visit_id' => $visit_id
        ]);
    }

    /**
     * Generate and download PDF report for a project.
     * Contains cumulative visit report with all finalized visits.
     *
     * @param string $id_spk SPK budgeting ID (pipe-encoded)
     */
    public function download_pdf()
    {
        $this->auth->restrict($this->viewPermission);

        // Get full id_spk from URI segments
        $id_spk = $this->_get_id_spk_from_uri(2);

        // Load SPK detail for header info
        $spk_detail = $this->Laporan_kunjungan_model->get_spk_detail($id_spk);

        if (!$spk_detail) {
            $this->session->set_flashdata('message', 'Data SPK tidak ditemukan.');
            redirect('laporan_kunjungan');
            return;
        }

        // Load all final visits for project with kegiatan and action plans
        $visits = $this->Laporan_kunjungan_model->get_visits_by_project($id_spk);

        if (!$visits) {
            $visits = [];
        }

        // Prepare data for PDF view
        $data = [
            'spk_detail' => $spk_detail,
            'visits'     => $visits,
        ];

        // Render pdf_report view to HTML string
        $html = $this->load->view('pdf_report', $data, TRUE);

        // Generate PDF using mPDF
        try {
            $this->load->library(array('Mpdf'));
            $mpdf = new mPDF('', 'A4-L');
            $mpdf->WriteHTML($html);
            $mpdf->Output('Laporan_Kunjungan_' . date('Y-m-d') . '.pdf', 'D');
        } catch (Exception $e) {
            log_message('error', 'PDF generation failed: ' . $e->getMessage());
            $this->session->set_flashdata('message', 'Gagal membuat PDF. Silakan coba lagi.');
            redirect('laporan_kunjungan/report/' . $this->_encode_id_spk($id_spk));
        }
    }

    /**
     * AJAX endpoint to send the visit report PDF via email to the client.
     * Generates PDF, attaches it to an email, and sends to the client email
     * address associated with the project in the SPK database.
     *
     * @param string $id_spk SPK budgeting ID (pipe-encoded)
     * @return void Outputs JSON response
     */
    public function send_email()
    {
        $this->auth->restrict($this->managePermission);

        // Get full id_spk from URI segments
        $id_spk = $this->_get_id_spk_from_uri(2);

        // Get client email from SPK data
        $client_email = $this->Laporan_kunjungan_model->get_client_email($id_spk);

        if (!$client_email) {
            echo json_encode([
                'status'  => false,
                'message' => 'Email client tidak ditemukan untuk project ini.'
            ]);
            return;
        }

        // Load SPK detail for header info and project name
        $spk_detail = $this->Laporan_kunjungan_model->get_spk_detail($id_spk);

        if (!$spk_detail) {
            echo json_encode([
                'status'  => false,
                'message' => 'Data SPK tidak ditemukan.'
            ]);
            return;
        }

        // Load all final visits for project with kegiatan and action plans
        $visits = $this->Laporan_kunjungan_model->get_visits_by_project($id_spk);

        if (!$visits) {
            $visits = [];
        }

        // Prepare data for PDF view
        $data = [
            'spk_detail' => $spk_detail,
            'visits'     => $visits,
        ];

        // Render pdf_report view to HTML string
        $html = $this->load->view('pdf_report', $data, TRUE);

        // Generate PDF and save to temp file
        $temp_path = APPPATH . 'cache/laporan_kunjungan_' . time() . '.pdf';

        try {
            $this->load->library(array('Mpdf'));
            $mpdf = new mPDF('', 'A4-L');
            $mpdf->WriteHTML($html);
            $mpdf->Output($temp_path, 'F');
        } catch (Exception $e) {
            log_message('error', 'PDF generation for email failed: ' . $e->getMessage());
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal membuat PDF. Silakan coba lagi.'
            ]);
            return;
        }

        // Send email with PDF attachment
        $project_name = $spk_detail->nm_project;

        $this->load->library('email');
        $this->email->from($this->config->item('smtp_user') ?: 'noreply@sendigs.com', 'Sendigs Finance');
        $this->email->to($client_email);
        $this->email->subject('Laporan Kunjungan - ' . $project_name);
        $this->email->message('Terlampir laporan kunjungan konsultan untuk project ' . $project_name . '.');
        $this->email->attach($temp_path);

        $send_result = $this->email->send();

        // Clean up temp file
        if (file_exists($temp_path)) {
            unlink($temp_path);
        }

        if ($send_result) {
            echo json_encode([
                'status'  => true,
                'message' => 'Email berhasil dikirim ke ' . $client_email . '.'
            ]);
        } else {
            log_message('error', 'Email sending failed: ' . $this->email->print_debugger(array('headers')));
            echo json_encode([
                'status'  => false,
                'message' => 'Gagal mengirim email. Silakan coba lagi.'
            ]);
        }
    }
}
