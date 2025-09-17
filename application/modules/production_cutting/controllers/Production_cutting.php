<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Production_cutting extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Production_Cutting.View';
    protected $addPermission  	= 'Production_Cutting.Add';
    protected $managePermission = 'Production_Cutting.Manage';
    protected $deletePermission = 'Production_Cutting.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array(
          'Production_cutting/Production_cutting_model',
          'Stock_origa/Stock_origa_model',
          'Bom_hi_grid_custom/bom_hi_grid_custom_model'
        ));
        // $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View index production cutting");
      $this->template->title('Production Cutting');
      $this->template->render('index');
    }

    public function data_side_request_produksi(){
  		$this->Production_cutting_model->data_side_request_produksi();
  	}

    public function addx($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

        $no_delivery		    = $data['no_delivery'];
    		$no_surat_jalan		  = $data['no_surat_jalan'];

        $getDetail = $this->db->get_where('so_spk_cutting_request_outgoing_temp a',array('a.created_by'=>$this->id_user))->result_array();
        
        $ArrDetail = [];
        foreach ($getDetail as $key => $value) {
          $ArrDetail[$key]['id_spk']     = $value['id_spk'];
          $ArrDetail[$key]['kode_req']      = $value['kode_req'];
          $ArrDetail[$key]['no_so']        = $value['no_so'];
          $ArrDetail[$key]['code_lv4']   = $value['code_lv4'];
          $ArrDetail[$key]['qty_spk']   = $value['qty_spk'];
          $ArrDetail[$key]['qty_outgoing']   = $value['qty_outgoing'];
          $ArrDetail[$key]['no_bom']   = $value['no_bom'];
          $ArrDetail[$key]['nm_product']   = $value['nm_product'];
          $ArrDetail[$key]['created_by']   = $this->id_user;
        }

        $ArrHeader = [
          'no_surat_jalan' => $no_surat_jalan,
          'outgoing_by' => $this->id_user,
          'outgoing_date' => $this->datetime
        ];

        //pengurangan stock
        $ArrUpdateStokNew = [];
        foreach ($getDetail as $key => $value) {
          $ArrUpdateStokNew[$key]['code_lv4'] = $value['code_lv4'];
          $ArrUpdateStokNew[$key]['no_bom'] = $value['no_bom'];
          $ArrUpdateStokNew[$key]['stok_aktual'] = $value['qty_outgoing'];
          $ArrUpdateStokNew[$key]['stok_booking'] = 0;
          $ArrUpdateStokNew[$key]['stok_downgrade'] = 0;
          $ArrUpdateStokNew[$key]['qty'] = $value['qty_outgoing'];
        }

        $this->db->trans_start();
          $this->db->where('kode', $no_delivery);
          $this->db->update('so_spk_cutting_request', $ArrHeader);

          $this->db->where('kode_req', $no_delivery);
          $this->db->delete('so_spk_cutting_request_outgoing');

          if(!empty($ArrDetail)){
            $this->db->insert_batch('so_spk_cutting_request_outgoing', $ArrDetail);
          }
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
          $this->db->where('created_by',$this->id_user);
          $this->db->delete('spk_delivery_detail_sj_temp');
          history("Create spk delivery : ".$no_delivery);

          if(empty($data['check_waste'])){
          history_product($ArrUpdateStokNew, 'minus', $no_delivery, 'pengurangan fg (outgoing for cutting)');
          }
        }
        echo json_encode($Arr_Data);
      }
      else{

        $getData    = $this->db->get_where('so_spk_cutting_request',array('id'=>$id))->result_array();
        $getDataCut = $this->db->get_where('so_spk_cutting',array('id'=>$getData[0]['id_so']))->result_array();
        $getDataReq = $this->db->get_where('so_internal_request',array('id'=>$getDataCut[0]['id_request']))->result_array();
        $getDetail 	= $this->db->get_where('so_spk_cutting_request_outgoing', array('kode_req' => $getData[0]['kode']))->result_array();

        $this->db->where('created_by',$this->id_user);
        $this->db->delete('so_spk_cutting_request_outgoing_temp');

        $ArrDetail = [];
        foreach ($getDetail as $key => $value) {
          $ArrDetail[$key]['id_spk']     = $value['id_spk'];
          $ArrDetail[$key]['kode_req']      = $value['kode_req'];
          $ArrDetail[$key]['no_so']        = $value['no_so'];
          $ArrDetail[$key]['code_lv4']   = $value['code_lv4'];
          $ArrDetail[$key]['qty_spk']   = $value['qty_spk'];
          $ArrDetail[$key]['qty_outgoing']   = $value['qty_outgoing'];
          $ArrDetail[$key]['created_by']   = $this->id_user;
          $ArrDetail[$key]['no_bom']   = $value['no_bom'];
          $ArrDetail[$key]['nm_product']   = $value['nm_product'];
        }

        if(!empty($ArrDetail)){
          $this->db->insert_batch('so_spk_cutting_request_outgoing_temp', $ArrDetail);
        }
      
        $data = [
          'getData' => $getData,
          'getDataCut' => $getDataCut,
          'getDataReq' => $getDataReq,
          'getDetail' => $getDetail,
        ];

        $this->template->title('Outgoing Single Product');
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

    public function add($id=null){
      $getData = $this->db
                      ->select('b.*, a.*, a.id AS id_uniq, c.kode_hub')
                      ->join('so_spk_cutting_request b','a.id_spk=b.id','left')
                      ->join('so_spk_cutting c','b.id_so=c.id','left')
                      ->get_where('so_spk_cutting_request_outgoing a',array(
                          'a.id'=>$id
                        ))
                      ->result_array();
      // echo "<pre>";
      // print_r($getData);
      // exit;

      
      $id_gudang  = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);
      $kode       = $getData[0]['no_so'];
      $code_lv4   = $getData[0]['code_lv4'];
      $no_bom     = $getData[0]['no_bom'];
      $qty        = $getData[0]['qty_outgoing'];
      $kode_hub   = $getData[0]['kode_hub'];
      // $getMaterialMixing  = $this->db
      //                         ->select('a.id, a.code_material, a.weight AS berat, a.weight_aktual, b.weight_aktual AS berat_subgudang')
      //                         ->join('so_internal_spk_material_pengeluaran b', 'a.id=b.id_det_spk','left')
      //                         ->get_where('so_internal_spk_material a',array('a.type_name'=>'mixing','a.kode_det'=>$kode))
      //                         ->result_array();
      $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat')->where('so_number', $kode)->get_where('so_internal_material',array('type_name'=>'mixing'))->result_array();
      $getMaterialNonMixing  = $this->db->select('id, code_material, weight AS berat')->where('so_number', $kode)->get_where('so_internal_material',array('type_name <>'=>'mixing'))->result_array();
      
      // $cycletime    = $this->db->select('b.nm_process')->join('cycletime_detail_detail b','a.id_time=b.id_time','left')->get_where('cycletime_header a', array('a.deleted_date'=>NULL,'id_product'=>$code_lv4))->result_array();
      
      $cycletime = $this->db->select('*')->get_where('so_spk_cutting_plan',array('kode_hub'=>$kode_hub))->result_array();
      
      $getProcess = $this->db->group_by('nm_process')->get_where('bom_detail',array('no_bom'=>$no_bom))->result_array();
      $ArrProcess = [];
      foreach ($getProcess as $key => $value) {
        $ArrProcess[] = $value['nm_process'];
      }

      $no_bom = $getData[0]['no_bom'];
      $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
      $NamaProduct 	      = (!empty($GetNamaBOMProduct[$no_bom]))?$GetNamaBOMProduct[$no_bom]:0;

      $data = [
        'getData' => $getData,
        'id' => $id,
        'kode' => $kode,
        'NamaProduct' => $NamaProduct,
        'cycletime' => $cycletime,
        'ArrProcess' => $ArrProcess,
        'qty' => $qty,
        'getMaterialMixing' => $getMaterialMixing,
        'getMaterialNonMixing' => $getMaterialNonMixing,
        'GET_STOK' => getStokMaterial($id_gudang),
        'GET_MATERIAL' => get_inventory_lv4(),
        'checkInputMixing' => checkInputMixing($kode)
      ];
      $this->template->title('Input Aktual Produksi Cutting');
      $this->template->render('add', $data);
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
            'length' => $value['length'],
            'width' => $value['width'],
            'lari' => $value['lari'],
            'no_bom' => $value['no_bom'],
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