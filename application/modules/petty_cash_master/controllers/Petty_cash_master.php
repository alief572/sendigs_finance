<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Kiro
 * @copyright Copyright (c) 2024
 *
 * Controller for Master Petty Cash (header-detail structure)
 */
class Petty_cash_master extends Admin_Controller
{
    // Permission
    protected $viewPermission   = 'Pettycash.View';
    protected $addPermission    = 'Pettycash.Add';
    protected $managePermission = 'Pettycash.Manage';
    protected $deletePermission = 'Pettycash.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('petty_cash_master/Petty_cash_master_model'));
        $this->template->title('Master Petty Cash');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
    }

    private function beginAudit($method, $data = array(), $keys = array())
    {
        if (isset($this->load) && method_exists($this->load, 'library')) {
            $this->load->library('Module_audit_recorder');
        }
        if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'begin')) {
            return $this->module_audit_recorder->begin('petty_cash_master', $method,
                array('data' => $this->auditSafeData($data)),
                array_unique(array_merge(array_keys($data), $keys, array('reason', 'outcome'))));
        }
        return false;
    }

    private function completeAudit($message, $status, $data = array())
    {
        if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'complete')) {
            return $this->module_audit_recorder->complete($message, $status,
                array('data' => $this->auditSafeData($data), 'sql' => null));
        }
        return false;
    }

    private function auditSafeData($data)
    {
        $safe = array();
        foreach ($data as $key => $value) {
            if ($key === 'id' && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', (string) $value))) { continue; }
            if (is_scalar($value) || $value === null) {
                $safe[$key] = is_string($value) ? mb_substr($value, 0, 512, 'UTF-8') : $value;
            }
        }
        return $safe;
    }

    // Catat penolakan akses sebelum restrict() mengalihkan/menghentikan request.
    private function auditRestrict($permission)
    {
        if (!has_permission($permission)) {
            $this->completeAudit('Akses master petty cash ditolak', 0, array('reason' => 'permission_denied', 'outcome' => 'denied'));
        }
        $this->auth->restrict($permission);
    }

    /**
     * Display index page with server-side DataTable shell
     * @return void
     */
    public function index()
    {
        $this->beginAudit(__FUNCTION__, array(), array('outcome'));
        $this->auditRestrict($this->viewPermission);

        $this->template->set('addPermission', has_permission($this->addPermission));
        $this->template->render('index');
        $this->completeAudit('Membuka daftar master petty cash', 1, array('outcome' => 'rendered'));
    }

    /**
     * Load create form via AJAX into modal body
     * @return void
     */
    public function create()
    {
        $this->beginAudit(__FUNCTION__, array(), array('outcome'));
        $this->auditRestrict($this->addPermission);

        $data['coa_list']   = $this->Petty_cash_master_model->get_coa_list();
        $data['users_list'] = $this->Petty_cash_master_model->get_active_users();
        $data['mode']       = 'create';

        $this->load->view('form', $data);
        $this->completeAudit('Membuka form tambah master petty cash', 1, array('outcome' => 'rendered'));
    }

    /**
     * Load edit form with existing data via AJAX into modal body
     * @param int $id  Master petty cash ID
     * @return void
     */
    public function edit($id)
    {
        $this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'outcome'));
        $this->auditRestrict($this->managePermission);

        $header = $this->Petty_cash_master_model->get_header($id);
        if (!$header) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'msg' => 'Data tidak ditemukan']));
            $this->completeAudit('Edit master petty cash: data tidak ditemukan', 0, array('id' => $id, 'outcome' => 'not_found'));
            return;
        }

        $data['data']       = $header;
        $data['details']    = $this->Petty_cash_master_model->get_details($id);
        $data['coa_list']   = $this->Petty_cash_master_model->get_coa_list();
        $data['users_list'] = $this->Petty_cash_master_model->get_active_users();
        $data['mode']       = 'edit';

        $this->load->view('form', $data);
        $this->completeAudit('Membuka form edit master petty cash', 1, array('id' => $id, 'outcome' => 'rendered'));
    }

    /**
     * Load view (read-only) form via AJAX into modal body
     * @param int $id  Master petty cash ID
     * @return void
     */
    public function view($id)
    {
        $this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'outcome'));
        $this->auditRestrict($this->viewPermission);

        $header = $this->Petty_cash_master_model->get_header($id);
        if (!$header) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'msg' => 'Data tidak ditemukan']));
            $this->completeAudit('Lihat master petty cash: data tidak ditemukan', 0, array('id' => $id, 'outcome' => 'not_found'));
            return;
        }

        $data['data']       = $header;
        $data['details']    = $this->Petty_cash_master_model->get_details($id);
        $data['coa_list']   = $this->Petty_cash_master_model->get_coa_list();
        $data['users_list'] = $this->Petty_cash_master_model->get_active_users();
        $data['mode']       = 'view';

        $this->load->view('view', $data);
        $this->completeAudit('Melihat master petty cash', 1, array('id' => $id, 'outcome' => 'rendered'));
    }

    /**
     * Server-side DataTables endpoint (POST, AJAX)
     * @return void  Echoes JSON {draw, recordsTotal, recordsFiltered, data}
     */
    public function get_data()
    {
        $this->beginAudit(__FUNCTION__, array(), array('row_count', 'outcome'));
        $dt_params = $this->input->post();
        $result    = $this->Petty_cash_master_model->get_server_side_data($dt_params);

        // Build action buttons based on permissions
        foreach ($result['data'] as &$row) {
            $btn_view   = '';
            $btn_edit   = '';
            $btn_delete = '';

            if (has_permission($this->viewPermission)) {
                $btn_view = '<a class="btn btn-info btn-sm" href="javascript:void(0)" title="View" onclick="data_view(' . $row['id'] . ')"><i class="fa fa-eye"></i></a>';
            }

            if (has_permission($this->managePermission)) {
                $btn_edit = '<a class="btn btn-warning btn-sm" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $row['id'] . ')"><i class="fa fa-pencil"></i></a>';
            }

            if (has_permission($this->deletePermission)) {
                $btn_delete = '<a class="btn btn-danger btn-sm" href="javascript:void(0)" title="Delete" onclick="data_delete(' . $row['id'] . ')"><i class="fa fa-trash"></i></a>';
            }

            $row['action'] = $btn_view . ' ' . $btn_edit . ' ' . $btn_delete;
            unset($row['id']);
        }

        echo json_encode($result);
        $this->completeAudit('Data master petty cash berhasil diambil', 1, array('row_count' => count($result['data']), 'outcome' => 'rendered'));
    }

    /**
     * Save or update master + details (POST, AJAX)
     * Validates required fields, detail rows, and nominals before saving.
     * @return void  Echoes JSON {status: 0|1, msg: string}
     */
    public function save()
    {
        $this->beginAudit(__FUNCTION__, array(), array('id', 'operation', 'detail_count', 'outcome'));
        // Get POST data
        $id         = $this->input->post('id');
        $nama       = trim($this->input->post('nama'));
        $keterangan = trim($this->input->post('keterangan'));
        $detail     = $this->input->post('detail');

        // Validate required fields
        if (empty($nama)) {
            echo json_encode(['status' => 0, 'msg' => 'Field nama harus diisi']);
            $this->completeAudit('Simpan master petty cash gagal validasi', 0, array('id' => $id, 'reason' => 'nama_required', 'outcome' => 'validation_failed'));
            return;
        }
        if (empty($keterangan)) {
            echo json_encode(['status' => 0, 'msg' => 'Field keterangan harus diisi']);
            $this->completeAudit('Simpan master petty cash gagal validasi', 0, array('id' => $id, 'reason' => 'keterangan_required', 'outcome' => 'validation_failed'));
            return;
        }

        // Validate detail rows exist
        if (empty($detail) || !is_array($detail) || count($detail) < 1) {
            echo json_encode(['status' => 0, 'msg' => 'Detail COA harus diisi minimal 1 baris']);
            $this->completeAudit('Simpan master petty cash gagal validasi', 0, array('id' => $id, 'reason' => 'detail_required', 'outcome' => 'validation_failed'));
            return;
        }

        // Validate each detail row
        $details = [];
        foreach ($detail as $row) {
            $coa_code          = isset($row['coa_code']) ? trim($row['coa_code']) : '';
            $jenis_pengeluaran = isset($row['jenis_pengeluaran']) ? trim($row['jenis_pengeluaran']) : '';
            $nominal_raw       = isset($row['nominal']) ? $row['nominal'] : '';

            // Strip dot formatting (e.g., "1.000.000" → "1000000")
            $nominal_clean = str_replace('.', '', $nominal_raw);
            $nominal_clean = str_replace(',', '', $nominal_clean);

            // Validate nominal is numeric and greater than 0
            if (!is_numeric($nominal_clean) || intval($nominal_clean) <= 0) {
                echo json_encode(['status' => 0, 'msg' => 'Nominal harus berupa angka lebih besar dari 0']);
                $this->completeAudit('Simpan master petty cash gagal validasi', 0, array('id' => $id, 'reason' => 'nominal_invalid', 'outcome' => 'validation_failed'));
                return;
            }

            $details[] = [
                'coa_code'          => $coa_code,
                'jenis_pengeluaran' => $jenis_pengeluaran,
                'nominal'           => intval($nominal_clean),
            ];
        }

        // Build header array
        $header = [
            'nama'       => $nama,
            'keterangan' => $keterangan
        ];

        // Call model to save
        $result = $this->Petty_cash_master_model->save_with_details($header, $details, $id);

        echo json_encode($result);
        $saved = !empty($result['status']);
        $this->completeAudit($saved ? 'Master petty cash berhasil disimpan' : 'Master petty cash gagal disimpan', $saved ? 1 : 0, array(
            'id' => $saved && isset($result['id']) ? $result['id'] : $id,
            'operation' => empty($id) ? 'create' : 'update',
            'detail_count' => count($details),
            'outcome' => $saved ? 'saved' : 'save_failed'
        ));
    }

    /**
     * Delete master + all details (POST, AJAX)
     * Checks deletePermission and verifies record exists before deleting.
     * @param int $id  Master petty cash ID
     * @return void  Echoes JSON {status: 0|1, msg: string}
     */
    public function delete($id)
    {
        $this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'outcome'));
        $this->auditRestrict($this->deletePermission);

        // Check if record exists
        $record = $this->Petty_cash_master_model->get_header($id);
        if (!$record) {
            echo json_encode(['status' => 0, 'msg' => 'Data tidak ditemukan']);
            $this->completeAudit('Hapus master petty cash: data tidak ditemukan', 0, array('id' => $id, 'outcome' => 'not_found'));
            return;
        }

        // Call model to delete
        $result = $this->Petty_cash_master_model->delete_with_details($id);

        echo json_encode($result);
        $deleted = !empty($result['status']);
        $this->completeAudit($deleted ? 'Master petty cash berhasil dihapus' : 'Master petty cash gagal dihapus', $deleted ? 1 : 0, array('id' => $id, 'outcome' => $deleted ? 'deleted' : 'delete_failed'));
    }
}
