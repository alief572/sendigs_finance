<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is controller for Master Pettycash
 */
class Pettycash extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Pettycash.View';
    protected $addPermission  	= 'Pettycash.Add';
    protected $managePermission = 'Pettycash.Manage';
    protected $deletePermission = 'Pettycash.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('Pettycash/Pettycash_model','All/All_model'));
        $this->template->title('Petty Cash');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
    }

	// list
    public function index() {
		$data = $this->Pettycash_model->GetList();
        $this->template->set('results', $data);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Master Petty Cash');
        $this->template->render('index');
    }

	// create
	public function create(){
		$data_coa = $this->All_model->GetCoaCombo();
		$data_approval = $this->All_model->GetOneTable('user_emp','','nama_karyawan');
        $this->template->set('datacoa', $data_coa);
        $this->template->set('data_approval', $data_approval);
        $this->template->render('form');
	}

	// edit
	public function edit($id){
		$data = $this->Pettycash_model->GetData($id);
		$data_coa = $this->All_model->GetCoaCombo();
		$data_approval = $this->All_model->GetOneTable('user_emp','','nama_karyawan');
        $this->template->set('datacoa', $data_coa);
        $this->template->set('data_approval', $data_approval);
        $this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
        $this->template->render('form');
	}

	// save
	public function save(){
        $id	= $this->input->post("id");
        $nama	= $this->input->post("nama");
		$pengelola	= $this->input->post("pengelola");
        $keterangan	= $this->input->post("keterangan");
		$coadata	= $this->input->post("coa");
		$coa=implode(';',$coadata);
		$budget	= $this->input->post("budget");
		$approval	= $this->input->post("approval");
        if($id!="") {
			$data = array(
						array(
							'id'=>$id,
							'nama'=>$nama,
							'pengelola'=>$pengelola,
							'keterangan'=>$keterangan,
							'coa'=>$coa,
							'budget'=>$budget,
							'approval'=>$approval,
						)
					);
			$result = $this->Pettycash_model->update_batch($data,'id');
			$keterangan     = "SUKSES, Edit data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
            $data =  array(
						'nama'=>$nama,
						'pengelola'=>$pengelola,
						'keterangan'=>$keterangan,
						'coa'=>$coa,
						'budget'=>$budget,
						'approval'=>$approval,
					);
            $id = $this->Pettycash_model->insert($data);
            if(is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data ".$id;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result         = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data".$id;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
	}

	// delete
	public function delete($id){
        $result=$this->Pettycash_model->delete($id);
        $param = array( 'delete' => $result );
        echo json_encode($param);
	}

}
