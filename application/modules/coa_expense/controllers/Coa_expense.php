<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is controller for Master Coa Expense
 */
class Coa_expense extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Coa_expense.View';
    protected $addPermission        = 'Coa_expense.Add';
    protected $managePermission     = 'Coa_expense.Manage';
    protected $deletePermission     = 'Coa_expense.Delete';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Coa_expense/Coa_expense_model', 'All/All_model'));
        $this->template->title('Coa Expense');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
    }

    // list
    public function index()
    {
        $data = $this->Coa_expense_model->GetList();
        $this->template->set('results', $data);
        $this->template->page_icon('fa fa-list');
        $this->template->title('Master Coa Expense');
        $this->template->render('index');
    }

    // create
    public function create()
    {
        $data_coa = $this->All_model->GetCoaCombo();
        $this->template->set('datacoa', $data_coa);
        $this->template->render('form');
    }

    // edit
    public function edit($id)
    {
        $data = $this->Coa_expense_model->GetData($id);
        $data_coa = $this->All_model->GetCoaCombo();
        $data_approval = $this->All_model->GetOneTable('user_emp', '', 'nama_karyawan');
        $this->template->set('datacoa', $data_coa);
        $this->template->set('data_approval', $data_approval);
        $this->template->set('data', $data);
        $this->template->page_icon('fa fa-list');
        $this->template->render('form');
    }

    // save
    public function save()
    {
        $id                     = $this->input->post("id");
        $jenis_pengeluaran      = $this->input->post("jenis_pengeluaran");
        $keterangan             = $this->input->post("keterangan");
        $coadata                = $this->input->post("coa");

        $coa_numbers    = [];
        $coa_string       = [];

        foreach ($coadata as $cod) {
            $parts = explode(' - ', $cod);

            $coa_numbers[]   = trim($parts[0]);
            $coa_string[]    = trim($parts[0]) . '-' . trim($parts[1]);
        }

        $coa          = implode(';', $coa_numbers);
        $coa_name     = implode(';', $coa_string);

        if ($id != "") {
            $data = array(
                array(
                    'id'                => $id,
                    'jenis_pengeluaran' => $jenis_pengeluaran,
                    'keterangan'        => $keterangan,
                    'coa'               => $coa,
                    // 'coa_name'          => $coa_name,
                )
            );
            $result         = $this->Coa_expense_model->update_batch($data, 'id');
            $keterangan     = "SUKSES, Edit data " . $id;
            $status         = 1;
            $nm_hak_akses   = $this->managePermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
            $data =  array(
                'jenis_pengeluaran' => $jenis_pengeluaran,
                'keterangan'        => $keterangan,
                'coa'               => $coa,
                // 'coa_name'          => $coa_name,
            );
            $id = $this->Coa_expense_model->insert($data);
            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data " . $id;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result         = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data" . $id;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result         = FALSE;
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
            'save' => $result
        );
        echo json_encode($param);
    }

    // delete
    public function delete($id)
    {
        $result = $this->Coa_expense_model->delete($id);
        $param = array('delete' => $result);
        echo json_encode($param);
    }
}
