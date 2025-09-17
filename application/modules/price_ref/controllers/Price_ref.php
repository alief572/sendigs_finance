<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Master Price Reference
 */

class Price_ref extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Price_ref.View';
    protected $addPermission  	= 'Price_ref.Add';
    protected $managePermission = 'Price_ref.Manage';
    protected $deletePermission = 'Price_ref.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('Price_ref/Price_ref_model','All/All_model'));
        $this->template->title('Price Reference');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
		$this->satuan=array("Tahun"=>2076,"Bulan"=>173,"Minggu"=>40,"Hari"=>7);
    }

    public function index() {
		$data_tipe = $this->Price_ref_model->GetComboInventoryTipe();
		$data = $this->Price_ref_model->GetListOthers();
        $this->template->set('data_tipe', $data_tipe);
        $this->template->set('results', $data);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Price Reference');
        $this->template->render('others_list');
    }

	// list
	public function mp(){
		$data = $this->Price_ref_model->GetListMP();
        $this->template->set('results', $data);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Price Reference Manpower');
        $this->template->render('mp_list');
	}

	// create
	public function mp_create(){
        $this->template->set('satuan', $this->satuan);
        $this->template->render('mp_form');
	}

	// save
	public function mp_save(){
        $id             = $this->input->post("id");
        $element_name	= $this->input->post("element_name");
		$element_cost  	= $this->input->post("element_cost");
        $element_unit   = $this->input->post("element_unit");
        $element_in_hour= $this->input->post("element_in_hour");
        $element_info	= $this->input->post("element_info");
        $element_use	= $this->input->post("element_use");
        if($id!="") {
			$data = array(
						array(
							'id'=>$id,
							'element_name'=>$element_name,
							'element_cost'=>$element_cost,
							'element_unit'=>$element_unit,
							'element_in_hour'=>$element_in_hour,
							'element_info'=>$element_info,
							'element_use'=>$element_use
						)
					);
			$result = $this->Price_ref_model->update_batch($data,'id');
			$keterangan     = "SUKSES, Edit data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
            $data =  array(
						'element_name'=>$element_name,
						'element_cost'=>$element_cost,
						'element_unit'=>$element_unit,
						'element_in_hour'=>$element_in_hour,
						'element_info'=>$element_info,
						'element_use'=>$element_use
					);
            $id = $this->Price_ref_model->insert($data);
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

	// edit
	public function mp_edit($id){
		$data = $this->Price_ref_model->GetDataMP($id);
        $this->template->set('satuan', $this->satuan);
        $this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
        $this->template->render('mp_form');
	}

	// delete
	public function mp_delete($id){
        $result=$this->Price_ref_model->delete($id);
        $param = array( 'delete' => $result );
        echo json_encode($param);
	}

	public function material_search($idmaterial){
		$data_material	= $this->All_model->GetSatuanMaterial($idmaterial);
		echo json_encode($data_material);
	}

	// create
	public function others_create($tipe=''){
		$data_material	= $this->Price_ref_model->GetListMaterialNewPriceRef($tipe);
		$data_kurs	= $this->All_model->GetKursCombo();
        $this->template->set('data_tipe', $tipe);
        $this->template->set('data_kurs', $data_kurs);
        $this->template->set('data_material', $data_material);
        $this->template->render('others_form_add');
	}

	// save
	public function others_save(){
        $id             = $this->input->post("id");
		$element_tipe= $this->input->post("element_tipe");
        if($id!="") {
			$element_id	= $this->input->post("element_id");
			$element_cost  	= $this->input->post("element_cost");
			$element_unit   = $this->input->post("element_unit");
			$element_kurs	= $this->input->post("element_kurs");
			$data = array(
						'element_id'=>$element_id,
						'element_cost'=>$element_cost,
						'element_unit'=>$element_unit,
						'element_tipe'=>$element_tipe,
						'element_kurs'=>$element_kurs,
						'modified_by'=> $this->auth->user_id(),
						'modified_on'=>date("Y-m-d h:i:s")
					);
			$result = $this->All_model->dataUpdate('ms_price_ref_others',$data,array('id'=>$id));
			$result=true;
			$keterangan     = "SUKSES, Edit data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
			$detail_id		= $this->input->post("detail_id");
			$this->db->trans_begin();
			if(!empty($detail_id)){
				foreach ($detail_id as $keys){
					$element_id			= $this->input->post("element_id_".$keys);
					$element_cost		= $this->input->post("element_cost_".$keys);
					$element_unit		= $this->input->post("element_unit_".$keys);
					$element_kurs		= $this->input->post("element_kurs_".$keys);
					$data =  array(
								'element_id'=>$element_id,
								'element_cost'=>$element_cost,
								'element_unit'=>$element_unit,
								'element_tipe'=>$element_tipe,
								'element_kurs'=>$element_kurs,
								'created_by'=> $this->auth->user_id(),
								'created_on'=>date("Y-m-d h:i:s")
							);
					$this->All_model->dataSave('ms_price_ref_others',$data);
				}
			}
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result=false;
			} else {
				$this->db->trans_commit();
				$result=true;
			}
			$nm_hak_akses   = $this->managePermission;
			$keterangan     = "SUKSES, simpan data ";
			simpan_aktifitas($nm_hak_akses, 'ms_price_ref_others', $keterangan, 1, 'ms_price_ref_others', 1);
        }
        $param = array( 'save' => $result );
        echo json_encode($param);
	}

	// edit
	public function others_edit($id){
		$data = $this->Price_ref_model->GetDataOthers($id);
		$data_material	= $this->All_model->GetListMaterial(array('id_material'=>$data->element_id));
		$data_satuan	= $this->All_model->GetSatuanMaterial($data->element_id);
		$data_kurs	= $this->All_model->GetKursCombo();
        $this->template->set('data_tipe', $data->element_tipe);
        $this->template->set('data', $data);
        $this->template->set('data_satuan', $data_satuan);
        $this->template->set('data_kurs', $data_kurs);
        $this->template->set('data_material', $data_material);
		$this->template->page_icon('fa fa-list');
        $this->template->render('others_form');
	}

	// delete
	public function others_delete($id){
		$this->All_model->dataDelete('ms_price_ref_others',array('id'=>$id));
        $result=true;
        $param = array( 'delete' => $result );
        echo json_encode($param);
	}


}
