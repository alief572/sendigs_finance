<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Assembly extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Assembly.View';
    protected $addPermission  	= 'Assembly.Add';
    protected $managePermission = 'Assembly.Manage';
    protected $deletePermission = 'Assembly.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array(
          'Assembly/Assembly_model',
          'Stock_origa/Stock_origa_model',
          'Bom_hi_grid_custom/bom_hi_grid_custom_model'
        ));

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View index assembly");
      $this->template->title('Assembly');
      $this->template->render('index');
    }

    public function data_side_request_produksi(){
  		$this->Assembly_model->data_side_request_produksi();
  	}

    public function add($id = null, $uniq = null)
    {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id_uniq     = $data['id_uniq'];
      $detail      = $data['detail'];
      
      $ArrAssembly = [];
      foreach ($detail as $key => $value) {
        $ArrAssembly[$key]['id_req'] = $id_uniq;
        $ArrAssembly[$key]['id_master']   = (!empty($value['id_master']))?$value['id_master']:null;
        $ArrAssembly[$key]['key_hub']     = (!empty($value['key_hub']))?$value['key_hub']:null;
        $ArrAssembly[$key]['category']    = (!empty($value['category']))?$value['category']:null;
        $ArrAssembly[$key]['kode_barang'] = (!empty($value['kode_barang']))?$value['kode_barang']:null;
        $ArrAssembly[$key]['nama_barang'] = (!empty($value['nama_barang']))?$value['nama_barang']:null;
        $ArrAssembly[$key]['est']         = (!empty($value['est']))?$value['est']:null;
        $ArrAssembly[$key]['length']      = (!empty($value['length']))?$value['length']:null;
        $ArrAssembly[$key]['width']       = (!empty($value['width']))?$value['width']:null;
        $ArrAssembly[$key]['ket']         = (!empty($value['ket']))?$value['ket']:null;
        $ArrAssembly[$key]['layer']       = (!empty($value['layer']))?$value['layer']:null;
        $ArrAssembly[$key]['qty']         = (!empty($value['qty']))?$value['qty']:null;
        $ArrAssembly[$key]['created_by']  = $this->id_user;
        $ArrAssembly[$key]['created_date']= $this->datetime;
      }

      // echo "<pre>";
      // print_r($ArrAssembly);
      // exit;

      $ArrUpdateRequest = array(
        'ass_by'     => $this->id_user,
        'ass_date'   => $this->datetime,
        'sts_ass'    => 'P'
      );

      $this->db->trans_start();
        $this->db->where('id_req', $id_uniq);
        $this->db->delete('so_internal_assembly');

        $this->db->where('id', $id_uniq);
        $this->db->update('so_internal_request', $ArrUpdateRequest);
      
        if (!empty($ArrAssembly)) {
          $this->db->insert_batch('so_internal_assembly', $ArrAssembly);
        }
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save gagal disimpan ...',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1
        );
        history("Create custom assembly : " . $id_uniq);
      }
      echo json_encode($Arr_Data);
    } else {
      $getData = $this->db->get_where('new_inventory_4', array('code_lv4' => $id))->result_array();
      $getHeader = $this->db->get_where('so_internal_request', array('id' => $uniq))->result_array();

      $WhereIN = array('grid standard', 'standard', 'ftackel');


      //New
      $no_bom = $getHeader[0]['no_bom_planning'];
      $header         = $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
      $detail         = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
      $detail_hi_grid       = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'hi grid std'))->result_array();
      $detail_hi_grid_cut   = $this->db->select('a.*, b.length, b.width, b.qty, b.lari')->join('bom_detail_custom b','a.no_bom_detail=b.no_bom_detail','inner')->get_where('bom_detail a', array('a.no_bom' => $no_bom, 'a.category' => 'hi grid std','b.category'=>'ukuran jadi'))->result_array();
      $detail_additive      = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
      $detail_topping       = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
      $detail_accessories   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
      $detail_mat_joint     = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'))->result_array();
      $detail_flat_sheet    = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
      $detail_end_plate     = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
      $detail_ukuran_jadi   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
      $detail_others        = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'others'))->result_array();
      $product        = $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));
      $material      = $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'material'));
      $accessories    = $this->bom_hi_grid_custom_model->get_data_where_array('accessories', array('deleted_date' => NULL));
      $bom_additive      = $this->bom_hi_grid_custom_model->get_data_where_array('bom_header', array('deleted_date' => NULL, 'category' => 'additive'));
      $bom_topping      = $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_3 b', 'a.id_product=b.code_lv3', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result();
      $bom_higridstd1      = $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'grid standard'))->result();
      $bom_higridstd2      = $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'standard'))->result();
      $bom_higridstd     = array_merge($bom_higridstd1, $bom_higridstd2);
      $satuan        = $this->bom_hi_grid_custom_model->get_data_where_array('ms_satuan', array('deleted_date' => NULL, 'category' => 'unit'));

      $data = [
        'getData' => $getData,
        'getHeader' => $getHeader,
        'WhereIN' => $WhereIN,
        'getStockProduct' => get_stock_product_New(),
        'getProductLv4' => get_inventory_lv4(),
        'getNameBOMProduct' => get_name_product_by_bom_all(),
        'header' => $header,
        'detail' => $detail,
        'satuan' => $satuan,
        'detail_hi_grid' => $detail_hi_grid,
        'detail_hi_grid_cut' => $detail_hi_grid_cut,
        'detail_additive' => $detail_additive,
        'detail_topping' => $detail_topping,
        'detail_accessories' => $detail_accessories,
        'detail_mat_joint' => $detail_mat_joint,
        'detail_flat_sheet' => $detail_flat_sheet,
        'detail_end_plate' => $detail_end_plate,
        'detail_ukuran_jadi' => $detail_ukuran_jadi,
        'detail_others' => $detail_others,
        'product' => $product,
        'material' => $material,
        'accessories' => $accessories,
        'bom_additive' => $bom_additive,
        'bom_topping' => $bom_topping,
        'bom_higridstd' => $bom_higridstd,
      ];


      $this->template->title('Assembly');
      $this->template->render('add', $data);
    }
  }

    public function get_add(){
      $id 	= $this->uri->segment(3);
      $no 	= 0;
  
      $costcenter	= $this->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' ORDER BY nama_costcenter ASC ")->result_array();
      $d_Header = "";
      // $d_Header .= "<tr>";
        $d_Header .= "<tr class='header_".$id."'>";
          $d_Header .= "<td align='left'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][tanggal]' class='form-control input-md text-center datepicker' placeholder='Plan Date' readonly>";
          $d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][tanggal_est_finish]' class='form-control input-md text-center datepicker2' placeholder='Est Finish' readonly>";
          $d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][qty]' class='form-control input-md text-center autoNumeric0 qty_spk' placeholder='Qty SPK'>";
          $d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
          $d_Header .= "<select name='Detail[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
          $d_Header .= "<option value='0'>Select Costcenter</option>";
          foreach($costcenter AS $val => $valx){
            $d_Header .= "<option value='".$valx['id_costcenter']."'>".strtoupper($valx['nama_costcenter'])."</option>";
          }
          $d_Header .= 		"</select>";
          $d_Header .= "</td>";
          $d_Header .= "<td align='center'>";
          $d_Header .= "<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
          $d_Header .= "</td>";
        $d_Header .= "</tr>";
  
      //add part
      $d_Header .= "<tr id='add_".$id."'>";
        $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
       $d_Header .= "</tr>";
  
      echo json_encode(array(
          'header'			=> $d_Header,
      ));
    }

    public function save_detail_delivery(){
      $post 			  = $this->input->post();
      $no_delivery	= $post['no_delivery'];
      $username		  = $this->id_user;
      $datetime 		= $this->datetime;
      $qr_code_     = explode(", ",$post['qr_code']);
      $qr_code      = $qr_code_[0];
      $no_bom       = $qr_code_[1];
      $ArrDetail    = [];

      $GET_NAME = get_name_product_by_bom($no_bom);
      $nm_product = (!empty($GET_NAME[$no_bom]))?strtoupper($GET_NAME[$no_bom]):'';


      $getListSPK     = $this->db->get_where('so_spk_cutting_request',array('kode'=>$no_delivery))->result_array();
      $getListHeader  = $this->db->get_where('so_spk_cutting',array('id'=>$getListSPK[0]['id_so']))->result_array();
      if(!empty($getListSPK)){
        $ArrDetail    = [
          'id_spk' => $getListSPK[0]['id'],
          'kode_req' => $getListSPK[0]['kode'],
          'no_so' => $getListHeader[0]['so_number'],
          'code_lv4' => $getListHeader[0]['code_lv4'],
          'qty_spk' => $getListSPK[0]['qty'],
          'qty_outgoing' => NULL,
          'created_by' => $username,
          'no_bom' => $no_bom,
          'nm_product' => $nm_product,
        ];
      }

      $check = $this->db->get_where('so_spk_cutting_request_outgoing_temp',array('kode_req'=>$no_delivery,'code_lv4'=>$qr_code))->result_array();
      // exit;
      
      $this->db->trans_start();
        if(!empty($ArrDetail) AND empty($check)){
          $this->db->insert('so_spk_cutting_request_outgoing_temp', $ArrDetail);
        }
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0,
          'no_delivery' => $no_delivery
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil disimpan. Thanks ...',
          'status'	=> 1,
          'no_delivery' => $no_delivery
        );
      }
      echo json_encode($Arr_Data);
    }

    public function loadDataSS($no_delivery){

      $result 	= $this->db->get_where('so_spk_cutting_request_outgoing_temp', array('kode_req' => $no_delivery))->result_array();
      
      $data = array(
        'result'			=> $result,
        'GET_DET_Lv4' => get_inventory_lv4(),
      );
      $this->template->render('temp_product', $data);
    }

    public function changeDeliveryTemp(){
      $post 			  = $this->input->post();
      $username		  = $this->id_user;
      $datetime 		= $this->datetime;
      $id_spk	      = $post['id_spk'];
      $qty_delivery = $post['qty_delivery'];

      $ArrUpdate = [
        'qty_outgoing' => $qty_delivery
      ];
      
      $this->db->trans_start();
          $this->db->where('id_spk', $id_spk);
          $this->db->where('created_by', $username);
          $this->db->update('so_spk_cutting_request_outgoing_temp', $ArrUpdate);
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil disimpan. Thanks ...',
          'status'	=> 1
        );
      }
      echo json_encode($Arr_Data);
    }

    public function deleteDeliveryTemp(){
      $post 			  = $this->input->post();
      $username		  = $this->id_user;
      $datetime 		= $this->datetime;
      $id_spk	      = $post['id_spk'];
      
      $this->db->trans_start();
          $this->db->where('id_spk', $id_spk);
          $this->db->where('created_by', $username);
          $this->db->delete('so_spk_cutting_request_outgoing_temp');
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil disimpan. Thanks ...',
          'status'	=> 1
        );
      }
      echo json_encode($Arr_Data);
    }
    public function process_input_produksi_cutting(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $Detail = $data['detailCutting'];
      $DetailMaterial = $data['detailCuttingMaterial'];
      // echo "<pre>";
      // print_r($Detail);
      // exit;

      $id		        = $data['id'];
      $qty_produksi = $data['qty_produksi'];
      $check_close  = 0;

      $getData        = $this->db->get_where('so_spk_cutting_request_outgoing',array('id'=>$id))->result_array();
      $getDataReq     = $this->db->get_where('so_spk_cutting_request',array('id'=>$getData[0]['id_spk']))->result_array();
      $no_spk         = $getDataReq[0]['no_spk'];
      $kode           = $getData[0]['kode_req'];
      $qty            = $getData[0]['qty_outgoing'];
      $kode_trans     = $getData[0]['kode_req'];
      $id_costcenter  = $getDataReq[0]['id_costcenter'];
      $id_gudang_dari = get_name('warehouse','id','kd_gudang',$id_costcenter);
      $nm_costcenter  = 'AKTUAL CUTTING - '.strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$id_costcenter));
      $nm_costcenter2 =  strtolower(get_name('ms_costcenter','nama_costcenter','id_costcenter',$id_costcenter));

      $id_gudang_ke = 14;

      $ArrInsert = [];
      $ArrStock = [];
      $ArrStockBack = [];
      $ArrayID = [];
      foreach ($DetailMaterial as $key => $value) {
          // $ArrayID[] = $value['id'];

          if(!empty($value['detail'])){
            foreach ($value['detail'] as $key2 => $value2) {
              $KEYUNIQ = $key.'-'.$key2;
              $ArrInsert[$KEYUNIQ]['qty_produksi']  = $qty_produksi;
              $ArrInsert[$KEYUNIQ]['id_so_spk']     = $id;
              $ArrInsert[$KEYUNIQ]['qty']           = NULL;
              $ArrInsert[$KEYUNIQ]['tanggal']       = date('Y-m-d');

              $ArrInsert[$KEYUNIQ]['id_det_spk']            = $value2['id'];
              $ArrInsert[$KEYUNIQ]['code_material']         = $value2['code_material'];
              $ArrInsert[$KEYUNIQ]['weight']                = str_replace(',','',$value2['berat']);
              $ArrInsert[$KEYUNIQ]['code_material_aktual']  = $value2['code_material_aktual'];
              $ArrInsert[$KEYUNIQ]['weight_aktual']         = str_replace(',','',$value2['berat_aktual']);
              $ArrInsert[$KEYUNIQ]['created_by']            = $this->id_user;
              $ArrInsert[$KEYUNIQ]['created_date']          = $this->datetime;

              $ArrStock[$KEYUNIQ]['id'] = $value2['code_material'];
              $ArrStock[$KEYUNIQ]['qty'] = str_replace(',','',$value2['berat_aktual']);
            }
          }
          else{
              $KEYUNIQ = $key.'-9999';
              $ArrInsert[$KEYUNIQ]['qty_produksi']  = $qty_produksi;
              $ArrInsert[$KEYUNIQ]['id_so_spk']     = $id;
              $ArrInsert[$KEYUNIQ]['qty']           = str_replace(',','',$value['qty']);
              $ArrInsert[$KEYUNIQ]['tanggal']       = date('Y-m-d');

              $ArrInsert[$KEYUNIQ]['id_det_spk']            = NULL;
              $ArrInsert[$KEYUNIQ]['code_material']         = NULL;
              $ArrInsert[$KEYUNIQ]['weight']                = NULL;
              $ArrInsert[$KEYUNIQ]['code_material_aktual']  = NULL;
              $ArrInsert[$KEYUNIQ]['weight_aktual']         = NULL;
              $ArrInsert[$KEYUNIQ]['created_by']            = $this->id_user;
              $ArrInsert[$KEYUNIQ]['created_date']          = $this->datetime;
          }
      }

      $ArrUpdate = [
        'sts_close' => ($check_close == 0)?'P':'Y'
      ];

      //Insert Product
      foreach ($Detail as $key => $value) {
        $getMaxProduct  = $this->db->select('COUNT(product_ke) AS productMax')->get_where('so_spk_cutting_product',array('id_key_spk'=>$value['id']))->result_array();
        $qtyMaxOri      = (!empty($getMaxProduct[0]['productMax']))?$getMaxProduct[0]['productMax']:0;
        $qtyMax         = (!empty($getMaxProduct[0]['productMax']))?$getMaxProduct[0]['productMax'] + 1:1;
        $qtyFinishing   = str_replace(',','',$value['qty']) + $qtyMaxOri;
        for ($i=$qtyMax; $i <= $qtyFinishing; $i++) { 
          $InsertQC[] = [
            'id_key_spk' => $value['id'],
            'kode' => $kode,
            'kode_det' => $id,
            'no_spk' => $no_spk,
            'qty' => $qty,
            'product_ke' => $i,
            'close_produksi' => date('Y-m-d',strtotime($value['tanggal'])),
            'close_by' => $this->id_user,
            'close_date' => $this->datetime
          ];
        }
      }

      // echo "<pre>";
      // print_r($ArrInsert);
      // print_r($InsertQC);
      // exit;

      $this->db->trans_start();
        if(!empty($ArrInsert)){
          $this->db->insert_batch('so_spk_cutting_pengeluaran', $ArrInsert);
        }

        $this->db->where('id',$id);
        $this->db->update('so_spk_cutting_request_outgoing',$ArrUpdate);

        if(!empty($InsertQC)){
          $this->db->insert_batch('so_spk_cutting_product',$InsertQC);
        }

      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0,
          'id'      => $id,
          'close'   => $check_close
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil disimpan. Thanks ...',
          'status'	=> 1,
          'id'      => $id,
          'close'   => $check_close
        );
        
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, $nm_costcenter);
        history("Input aktual produksi cutting : ".$id);
      }
      echo json_encode($Arr_Data);
  }

}