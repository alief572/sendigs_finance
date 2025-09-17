<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Master Warehouse
 */
$status=array();
class Warehouse extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Warehouse.View';
    protected $addPermission  	= 'Warehouse.Add';
    protected $managePermission = 'Warehouse.Manage';
    protected $deletePermission = 'Warehouse.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('Warehouse/Warehouse_model','All/All_model'));
        $this->template->title('Gudang');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
		$this->status=array("0"=>"Aktif","1"=>"Non Aktif");
    }

	// list
    public function index() {
		$data = $this->Warehouse_model->GetListWarehouse();
        $this->template->set('results', $data);
        $this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Gudang');
        $this->template->render('warehouse_list');
    }

	// create
	public function create(){
        $this->template->set('status', $this->status);
        $this->template->render('warehouse_form');
	}

	// edit
	public function edit($id){
		$data = $this->Warehouse_model->GetDataWarehouse($id);
        $this->template->set('status', $this->status);
        $this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
        $this->template->render('warehouse_form');
	}

	// save
	public function save(){
        $id             = $this->input->post("id");
        $wh_name	= $this->input->post("wh_name");
		$wh_code  	= $this->input->post("wh_code");
        $wh_status   = $this->input->post("wh_status");
        $wh_info	= $this->input->post("wh_info");
        if($id!="") {
			$data = array(
						array(
							'id'=>$id,
							'wh_name'=>$wh_name,
							'wh_code'=>$wh_code,
							'wh_status'=>$wh_status,
							'wh_info'=>$wh_info,
						)
					);
			$result = $this->Warehouse_model->update_batch($data,'id');
			$keterangan     = "SUKSES, Edit data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
            $data =  array(
						'wh_name'=>$wh_name,
						'wh_code'=>$wh_code,
						'wh_status'=>$wh_status,
						'wh_info'=>$wh_info,
					);
            $id = $this->Warehouse_model->insert($data);
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
        $result=$this->Warehouse_model->delete($id);
        $param = array( 'delete' => $result );
        echo json_encode($param);
	}

}
