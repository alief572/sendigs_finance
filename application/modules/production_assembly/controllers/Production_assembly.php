<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Production_assembly extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Production_Assembly.View';
    protected $addPermission  	= 'Production_Assembly.Add';
    protected $managePermission = 'Production_Assembly.Manage';
    protected $deletePermission = 'Production_Assembly.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array(
          'Production_assembly/Production_assembly_model',
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

      history("View index production assembly");
      $this->template->title('Production Assembly');
      $this->template->render('index');
    }

    public function data_side_request_produksi(){
  		$this->Production_assembly_model->data_side_request_produksi();
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
                      ->select('a.*, b.nama AS nm_product, c.nm_customer')
                      ->join('new_inventory_4 b','a.code_lv4=b.code_lv4','left')
                      ->join('customer c','a.id_customer=c.id_customer','left')
                      ->get_where('so_spk_assembly a',array(
                          'a.id'=>$id
                        ))
                      ->result_array();
      
      $listMaterialJoint        = $this->db->select('id, id_material, qty')->get_where('so_spk_assembly_detail',array('kode_hub'=>$getData[0]['kode_hub'],'category'=>'mat joint'))->result_array();
      $listMaterialFlat         = $this->db->select('id, code_material AS id_material, weight AS qty')->like('kode_hub',$getData[0]['kode_hub'])->get_where('so_spk_assembly_material',array('category'=>'material flat sheet'))->result_array();
      $listMaterialEnd          = $this->db->select('id, code_material AS id_material, weight AS qty')->like('kode_hub',$getData[0]['kode_hub'])->get_where('so_spk_assembly_material',array('category'=>'material end plate'))->result_array();
      $listMaterialChequered    = $this->db->select('id, code_material AS id_material, weight AS qty')->like('kode_hub',$getData[0]['kode_hub'])->get_where('so_spk_assembly_material',array('category'=>'material ukuran jadi'))->result_array();
      $listMaterialOthers       = $this->db->select('id, code_material AS id_material, weight AS qty')->like('kode_hub',$getData[0]['kode_hub'])->get_where('so_spk_assembly_material',array('category'=>'material others'))->result_array();
      
      // echo print_r($listMaterialJoint);
      // exit;
      $data = [
        'id' => $id,
        'getData' => $getData,
        'listMaterialJoint' => $listMaterialJoint,
        'listMaterialFlat' => $listMaterialFlat,
        'listMaterialEnd' => $listMaterialEnd,
        'listMaterialChequered' => $listMaterialChequered,
        'listMaterialOthers' => $listMaterialOthers,
        'GET_MATERIAL' => get_list_inventory_lv4('material'),
      ];
      $this->template->title('Input Aktual Produksi Assembly');
      $this->template->render('add', $data);
  	}

    public function process_input_produksi_assembly(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id		        = $data['id'];
     
      $Arr_detailJoint  = [];
      $Arr_detailFlat   = [];
      $Arr_detailEnd    = [];
      $Arr_detailCheq   = [];
      $Arr_detailOthers = [];
      $ArrStock         = [];
      $nomor            = 0;

      if(!empty($data['detailJoint'])){
        foreach ($data['detailJoint'] as $key => $value) { $nomor++;
          $KEYUNIQ = $nomor;
          $Arr_detailJoint[$KEYUNIQ]['id_spk_assembly']  = $id;
          $Arr_detailJoint[$KEYUNIQ]['category']         = 'joint';
          $Arr_detailJoint[$KEYUNIQ]['id_key']           = $value['id'];
          $Arr_detailJoint[$KEYUNIQ]['est_id_material']  = $value['code_material'];
          $Arr_detailJoint[$KEYUNIQ]['act_id_material']  = $value['code_material_aktual'];
          $Arr_detailJoint[$KEYUNIQ]['est_berat']        = str_replace(',','',$value['berat']);
          $Arr_detailJoint[$KEYUNIQ]['act_berat']        = str_replace(',','',$value['berat_aktual']);
          $Arr_detailJoint[$KEYUNIQ]['created_by']       = $this->id_user;
          $Arr_detailJoint[$KEYUNIQ]['created_date']     = $this->datetime;

          $ArrStock[$KEYUNIQ]['id']   = $value['code_material_aktual'];
          $ArrStock[$KEYUNIQ]['qty']  = str_replace(',','',$value['berat_aktual']);
        }
      }
      if(!empty($data['detailFlat'])){
        foreach ($data['detailFlat'] as $key => $value) { $nomor++;
          $KEYUNIQ = $nomor;
          $Arr_detailFlat[$KEYUNIQ]['id_spk_assembly']  = $id;
          $Arr_detailFlat[$KEYUNIQ]['category']         = 'flat sheet';
          $Arr_detailFlat[$KEYUNIQ]['id_key']           = $value['id'];
          $Arr_detailFlat[$KEYUNIQ]['est_id_material']  = $value['code_material'];
          $Arr_detailFlat[$KEYUNIQ]['act_id_material']  = $value['code_material_aktual'];
          $Arr_detailFlat[$KEYUNIQ]['est_berat']        = str_replace(',','',$value['berat']);
          $Arr_detailFlat[$KEYUNIQ]['act_berat']        = str_replace(',','',$value['berat_aktual']);
          $Arr_detailFlat[$KEYUNIQ]['created_by']       = $this->id_user;
          $Arr_detailFlat[$KEYUNIQ]['created_date']     = $this->datetime;

          $ArrStock[$KEYUNIQ]['id']   = $value['code_material_aktual'];
          $ArrStock[$KEYUNIQ]['qty']  = str_replace(',','',$value['berat_aktual']);
        }
      }
      if(!empty($data['detailEnd'])){
        foreach ($data['detailEnd'] as $key => $value) { $nomor++;
          $KEYUNIQ = $nomor;
          $Arr_detailEnd[$KEYUNIQ]['id_spk_assembly']  = $id;
          $Arr_detailEnd[$KEYUNIQ]['category']         = 'end plate';
          $Arr_detailEnd[$KEYUNIQ]['id_key']           = $value['id'];
          $Arr_detailEnd[$KEYUNIQ]['est_id_material']  = $value['code_material'];
          $Arr_detailEnd[$KEYUNIQ]['act_id_material']  = $value['code_material_aktual'];
          $Arr_detailEnd[$KEYUNIQ]['est_berat']        = str_replace(',','',$value['berat']);
          $Arr_detailEnd[$KEYUNIQ]['act_berat']        = str_replace(',','',$value['berat_aktual']);
          $Arr_detailEnd[$KEYUNIQ]['created_by']       = $this->id_user;
          $Arr_detailEnd[$KEYUNIQ]['created_date']     = $this->datetime;

          $ArrStock[$KEYUNIQ]['id']   = $value['code_material_aktual'];
          $ArrStock[$KEYUNIQ]['qty']  = str_replace(',','',$value['berat_aktual']);
        }
      }
      if(!empty($data['detailCheq'])){
        foreach ($data['detailCheq'] as $key => $value) { $nomor++;
          $KEYUNIQ = $nomor;
          $Arr_detailCheq[$KEYUNIQ]['id_spk_assembly']  = $id;
          $Arr_detailCheq[$KEYUNIQ]['category']         = 'chequered';
          $Arr_detailCheq[$KEYUNIQ]['id_key']           = $value['id'];
          $Arr_detailCheq[$KEYUNIQ]['est_id_material']  = $value['code_material'];
          $Arr_detailCheq[$KEYUNIQ]['act_id_material']  = $value['code_material_aktual'];
          $Arr_detailCheq[$KEYUNIQ]['est_berat']        = str_replace(',','',$value['berat']);
          $Arr_detailCheq[$KEYUNIQ]['act_berat']        = str_replace(',','',$value['berat_aktual']);
          $Arr_detailCheq[$KEYUNIQ]['created_by']       = $this->id_user;
          $Arr_detailCheq[$KEYUNIQ]['created_date']     = $this->datetime;

          $ArrStock[$KEYUNIQ]['id']   = $value['code_material_aktual'];
          $ArrStock[$KEYUNIQ]['qty']  = str_replace(',','',$value['berat_aktual']);
        }
      }
      if(!empty($data['detailOthers'])){
        foreach ($data['detailOthers'] as $key => $value) { $nomor++;
          $KEYUNIQ = $nomor;
          $Arr_detailOthers[$KEYUNIQ]['id_spk_assembly']  = $id;
          $Arr_detailOthers[$KEYUNIQ]['category']         = 'others';
          $Arr_detailOthers[$KEYUNIQ]['id_key']           = $value['id'];
          $Arr_detailOthers[$KEYUNIQ]['est_id_material']  = $value['code_material'];
          $Arr_detailOthers[$KEYUNIQ]['act_id_material']  = $value['code_material_aktual'];
          $Arr_detailOthers[$KEYUNIQ]['est_berat']        = str_replace(',','',$value['berat']);
          $Arr_detailOthers[$KEYUNIQ]['act_berat']        = str_replace(',','',$value['berat_aktual']);
          $Arr_detailOthers[$KEYUNIQ]['created_by']       = $this->id_user;
          $Arr_detailOthers[$KEYUNIQ]['created_date']     = $this->datetime;

          $ArrStock[$KEYUNIQ]['id']   = $value['code_material_aktual'];
          $ArrStock[$KEYUNIQ]['qty']  = str_replace(',','',$value['berat_aktual']);
        }
      }

      $ArrUpdate = [
        'sts_close_material' => 'Y',
        'close_material_by' => $this->id_user,
        'close_material_date' => $this->datetime
      ];

      // echo "<pre>";
      // print_r($Arr_detailJoint);
      // print_r($Arr_detailFlat);
      // print_r($Arr_detailEnd);
      // print_r($Arr_detailCheq);
      // print_r($Arr_detailOthers);
      // exit;

      $this->db->trans_start();
        if(!empty($Arr_detailJoint)){
          $this->db->insert_batch('so_spk_assembly_material_pengeluaran', $Arr_detailJoint);
        }
        if(!empty($Arr_detailFlat)){
          $this->db->insert_batch('so_spk_assembly_material_pengeluaran', $Arr_detailFlat);
        }
        if(!empty($Arr_detailEnd)){
          $this->db->insert_batch('so_spk_assembly_material_pengeluaran', $Arr_detailEnd);
        }
        if(!empty($Arr_detailCheq)){
          $this->db->insert_batch('so_spk_assembly_material_pengeluaran', $Arr_detailCheq);
        }
        if(!empty($Arr_detailOthers)){
          $this->db->insert_batch('so_spk_assembly_material_pengeluaran', $Arr_detailOthers);
        }

        $this->db->where('id',$id);
        $this->db->update('so_spk_assembly',$ArrUpdate);

      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0,
          'id'      => $id
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil disimpan. Thanks ...',
          'status'	=> 1,
          'id'      => $id
        );
        
        // move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, $nm_costcenter);
        history("Input aktual produksi assembly : ".$id);
      }
      echo json_encode($Arr_Data);
  }

}