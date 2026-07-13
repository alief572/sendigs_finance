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

    /**
     * Display index page with server-side DataTable shell
     * @return void
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->set('addPermission', has_permission($this->addPermission));
        $this->template->render('index');
    }

    /**
     * Load create form via AJAX into modal body
     * @return void
     */
    public function create()
    {
        $this->auth->restrict($this->addPermission);

        $data['coa_list']   = $this->Petty_cash_master_model->get_coa_list();
        $data['users_list'] = $this->Petty_cash_master_model->get_active_users();
        $data['mode']       = 'create';

        $this->load->view('form', $data);
    }

    /**
     * Load edit form with existing data via AJAX into modal body
     * @param int $id  Master petty cash ID
     * @return void
     */
    public function edit($id)
    {
        $this->auth->restrict($this->managePermission);

        $header = $this->Petty_cash_master_model->get_header($id);
        if (!$header) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'msg' => 'Data tidak ditemukan']));
            return;
        }

        $data['data']       = $header;
        $data['details']    = $this->Petty_cash_master_model->get_details($id);
        $data['coa_list']   = $this->Petty_cash_master_model->get_coa_list();
        $data['users_list'] = $this->Petty_cash_master_model->get_active_users();
        $data['mode']       = 'edit';

        $this->load->view('form', $data);
    }

    /**
     * Load view (read-only) form via AJAX into modal body
     * @param int $id  Master petty cash ID
     * @return void
     */
    public function view($id)
    {
        $this->auth->restrict($this->viewPermission);

        $header = $this->Petty_cash_master_model->get_header($id);
        if (!$header) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'msg' => 'Data tidak ditemukan']));
            return;
        }

        $data['data']       = $header;
        $data['details']    = $this->Petty_cash_master_model->get_details($id);
        $data['coa_list']   = $this->Petty_cash_master_model->get_coa_list();
        $data['users_list'] = $this->Petty_cash_master_model->get_active_users();
        $data['mode']       = 'view';

        $this->load->view('view', $data);
    }

    /**
     * Server-side DataTables endpoint (POST, AJAX)
     * @return void  Echoes JSON {draw, recordsTotal, recordsFiltered, data}
     */
    public function get_data()
    {
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
    }

    /**
     * Save or update master + details (POST, AJAX)
     * Validates required fields, detail rows, and nominals before saving.
     * @return void  Echoes JSON {status: 0|1, msg: string}
     */
    public function save()
    {
        // Get POST data
        $id         = $this->input->post('id');
        $nama       = trim($this->input->post('nama'));
        $keterangan = trim($this->input->post('keterangan'));
        $detail     = $this->input->post('detail');

        // Validate required fields
        if (empty($nama)) {
            echo json_encode(['status' => 0, 'msg' => 'Field nama harus diisi']);
            return;
        }
        if (empty($keterangan)) {
            echo json_encode(['status' => 0, 'msg' => 'Field keterangan harus diisi']);
            return;
        }

        // Validate detail rows exist
        if (empty($detail) || !is_array($detail) || count($detail) < 1) {
            echo json_encode(['status' => 0, 'msg' => 'Detail COA harus diisi minimal 1 baris']);
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
    }

    /**
     * Delete master + all details (POST, AJAX)
     * Checks deletePermission and verifies record exists before deleting.
     * @param int $id  Master petty cash ID
     * @return void  Echoes JSON {status: 0|1, msg: string}
     */
    public function delete($id)
    {
        $this->auth->restrict($this->deletePermission);

        // Check if record exists
        $record = $this->Petty_cash_master_model->get_header($id);
        if (!$record) {
            echo json_encode(['status' => 0, 'msg' => 'Data tidak ditemukan']);
            return;
        }

        // Call model to delete
        $result = $this->Petty_cash_master_model->delete_with_details($id);

        echo json_encode($result);
    }
}
