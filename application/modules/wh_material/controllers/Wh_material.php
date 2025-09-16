<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Trasaction In Out Material Warehouse
 */
$status=array();
class Wh_material extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Wh_material.View';
    protected $addPermission  	= 'Wh_material.Add';
    protected $managePermission = 'Wh_material.Manage';
    protected $deletePermission = 'Wh_material.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('Wh_material/Wh_material_model','All/All_model'));
        $this->template->title('Traksaksi In Out Material');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
		$this->status=array("0"=>"Proses","1"=>"Selesai");
    }

	// list
    public function in() {
		$data = $this->Wh_material_model->GetListMaterialIn();
        $this->template->set('results', $data);
        $this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Material In');
        $this->template->render('wh_material_in_list');
    }

	// create
	public function create_in(){
		$warehouse=$this->All_model->GetWhCombo();
		$data_po=$this->Wh_material_model->GetPOCombo(array('status'=>1));
        $this->template->set('warehouse', $warehouse);
        $this->template->set('data_po', $data_po);
        $this->template->set('status', $this->status);
        $this->template->render('wh_material_in_form');
	}

	// edit
	public function edit_in($id){
		$data = $this->Wh_material_model->GetDataWhMaterial($id);
		$data_po=$this->Wh_material_model->GetPOCombo(array('po_no'=>$data->reference_no));
		$data_material	= $this->Wh_material_model->GetDataWhMaterialDetail($data->doc_no);
		$warehouse=$this->All_model->GetWhCombo();
        $this->template->set('data_material', $data_material);
        $this->template->set('warehouse', $warehouse);
        $this->template->set('data_po', $data_po);
        $this->template->set('status', $this->status);
        $this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
        $this->template->render('wh_material_in_form');
	}
	// view
	public function view_in($id){
		$data = $this->Wh_material_model->GetDataWhMaterial($id);
		$data_po=$this->Wh_material_model->GetPOCombo(array('po_no'=>$data->reference_no));
		$data_material	= $this->Wh_material_model->GetDataWhMaterialDetail($data->doc_no);
		$warehouse=$this->All_model->GetWhCombo();
        $this->template->set('data_material', $data_material);
        $this->template->set('warehouse', $warehouse);
        $this->template->set('data_po', $data_po);
        $this->template->set('status', $this->status);
        $this->template->set('data', $data);
        $this->template->set('views', 'view');
		$this->template->page_icon('fa fa-list');
        $this->template->render('wh_material_in_form');
	}
	// approve
	public function approve_in($id=''){
		$result=false;
        if($id!="") {
			$this->db->trans_begin();
			$data = array(
						array(
							'id'=>$id,
							'status'=>1,
						)
					);
			$result = $this->Wh_material_model->update_batch($data,'id');

		//start cek apakah sudah komplit purchase order (receive sudah semua)
			$data = $this->Wh_material_model->GetDataWhMaterial($id);
			if($data!==false){
				$data_material=$this->Wh_material_model->GetListPOMaterial($data->reference_no);
				if($data_material===false) {
					$this->All_model->dataUpdate("tr_purchase_order",array('status'=>2),array('po_no'=>$data->reference_no));
				}
				$data_material	= $this->Wh_material_model->GetDataWhMaterialDetail($data->doc_no);
				if($data_material!==false) {
					foreach($data_material as $record){
						$datatosave['material_id']=$record->material_id;
						$datatosave['material_qty']=$record->material_qty;
						$datatosave['material_price']=$record->material_price;
						$datatosave['material_unit']=$record->material_unit;
						$datatosave['wh_code']=$data->wh_code;
						$datatosave['code']='PO';
						$datatosave['doc_no']=$data->doc_no;
						$datatosave['tanggal']=$data->trans_date;
						$datatosave['created_by']=$this->auth->user_id();
						$datatosave['created_on']=date("Y-m-d h:i:s");
						$this->All_model->update_stock_in($datatosave);
					}
				}
			}

		// end cek
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
			$keterangan     = "SUKSES, Approve data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result, 'id'=>$id
                );
        echo json_encode($param);
	}

	// save
	public function save_in(){
        $id             = $this->input->post("id");
        $doc_no			= $this->input->post("doc_no");
		$wh_code  		= $this->input->post("wh_code");
        $reference_no   = $this->input->post("reference_no");
        $info			= $this->input->post("info");
        $trans_date		= $this->input->post("trans_date");
        $pic			= $this->input->post("pic");
        $trans_type			= $this->input->post("trans_type");
		$detail_id		= $this->input->post("detail_id");
		$this->db->trans_begin();
        if($id!="") {
			$data = array(
						array(
							'id'=>$id,
							'doc_no'=>$doc_no,
							'reference_no'=>$reference_no,
							'wh_code'=>$wh_code,
							'trans_date'=>$trans_date,
							'pic'=>$pic,
							'status'=>0,
							'info'=>$info,
							'trans_type'=>$trans_type,
						)
					);
			$result = $this->Wh_material_model->update_batch($data,'id');
			$this->All_model->dataUpdate("tr_purchase_order_detail",array('material_receive'=>0),array(" id in (select id_po from tr_warehouse_detail where doc_no='".$doc_no."')"=>null));
			$this->All_model->dataDelete('tr_warehouse_detail',array('doc_no'=>$doc_no));
			if(!empty($detail_id)){
				foreach ($detail_id as $keys){
					$material_id		= $this->input->post("material_id_".$keys);
					$material_qty		= $this->input->post("material_qty_".$keys);
					$material_unit		= $this->input->post("material_unit_".$keys);
					$material_order		= $this->input->post("material_order_".$keys);
					$material_price		= $this->input->post("material_price_".$keys);
					$id_po				= $this->input->post("id_po_detail_".$keys);
					$data_detail =  array(
								'doc_no'=>$doc_no,
								'id_po'=>$id_po,
								'material_id'=>$material_id,
								'material_qty'=>$material_qty,
								'material_unit'=>$material_unit,
								'material_order'=>$material_order,
								'material_price'=>$material_price,
								'created_by'=> $this->auth->user_id(),
								'created_on'=>date("Y-m-d h:i:s")
							);
					$this->All_model->dataSave('tr_warehouse_detail',$data_detail);
					$this->All_model->dataUpdate("tr_purchase_order_detail",array('material_receive'=>$material_qty),array('id'=>$id_po));
				}
			}

			$keterangan     = "SUKSES, Edit data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
			$doc_no=$this->All_model->GetAutoGenerate('format_material_in');
            $data =  array(
						'doc_no'=>$doc_no,
						'reference_no'=>$reference_no,
						'wh_code'=>$wh_code,
						'trans_date'=>$trans_date,
						'pic'=>$pic,
						'status'=>0,
						'info'=>$info,
						'trans_type'=>$trans_type,
					);
            $id = $this->Wh_material_model->insert($data);
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
 			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
           simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result, 'id'=>$id
                );
        echo json_encode($param);
	}

	// delete
	public function delete($id){
		$data = $this->Wh_material_model->GetDataWhMaterial($id);
        $result=$this->All_model->dataDelete('tr_warehouse_detail',array('doc_no'=>$data->doc_no));
        $result=$this->Wh_material_model->delete($id);
        $param = array( 'delete' => $result );
        echo json_encode($param);
	}

	public function add_material_in($id){
		$data = $this->Wh_material_model->GetDataWhMaterial($id);
		if($data!==false){
			$data_material=$this->Wh_material_model->GetListPOMaterial($data->reference_no);
			if($data_material!==false) {
				$idx=5000;
				foreach($data_material as $record){
					$idx++;?>
					<tr>
						<td><input type="checkbox" name="detail_id[]" id="raw_id_<?=$idx?>" value="<?=$idx;?>" checked>
						<input type="hidden" name="material_id_<?=$idx;?>" id="material_id_<?=$idx;?>" value="<?=$record->material_id;?>">
						<input type="hidden" name="id_po_detail_<?=$idx;?>" id="id_po_detail_<?=$idx;?>" value="<?=$record->id;?>">
						<input type="hidden" name="material_price_<?=$idx;?>" id="material_price_<?=$idx;?>" value="<?=$record->material_price;?>">
						<td><?= $record->nama ?></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_order_<?=$idx;?>" id="material_order_<?=$idx;?>" value="<?=$record->material_qty;?>"></td>
						<td><input type="text" class="form-control divide" name="material_qty_<?=$idx;?>" id="material_qty_<?=$idx;?>" value="0"></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_unit_<?=$idx;?>" id="material_unit_<?=$idx;?>" value="<?=$record->material_unit;?>"></td>
					</tr>
					<?php
				}
			}
		}else{
			die();
		}
	}

}
