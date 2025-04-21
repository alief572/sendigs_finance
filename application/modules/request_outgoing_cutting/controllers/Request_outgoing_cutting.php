<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Request_outgoing_cutting extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Request_Outgoing_Cutting.View';
    protected $addPermission  	= 'Request_Outgoing_Cutting.Add';
    protected $managePermission = 'Request_Outgoing_Cutting.Manage';
    protected $deletePermission = 'Request_Outgoing_Cutting.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array(
          'Request_outgoing_cutting/Request_outgoing_cutting_model',
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

      history("View index request outgoing cutting product");
      $this->template->title('Request Outgoing Cutting Product');
      $this->template->render('index');
    }

    public function data_side_request_produksi(){
  		$this->Request_outgoing_cutting_model->data_side_request_produksi();
  	}

    public function add($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

        $id		              = $data['id'];
        $no_delivery		    = $data['no_delivery'];
    		$no_surat_jalan		  = $data['no_surat_jalan'];
    		$so_number		      = $data['so_number'];
    		$qty_outgoing		    = str_replace(',','',$data['qty_outgoing']);
        $getDetail		      = $data['detail'];
        // $getDetail = $this->db->get_where('so_spk_cutting_request_outgoing_temp a',array('a.created_by'=>$this->id_user))->result_array();
        
        $ArrDetail = [];
        foreach ($getDetail as $key => $value) {
          $ArrDetail[$key]['id_spk']     = $value['id_spk'];
          $ArrDetail[$key]['kode_req']      = $no_delivery;
          $ArrDetail[$key]['no_so']        = $so_number;
          $ArrDetail[$key]['code_lv4']   = $value['code_lv4'];
          $ArrDetail[$key]['qty_spk']   = $value['qty_spk'];
          $ArrDetail[$key]['qty_outgoing']   = str_replace(',','',$data['qty_outgoing']);
          $ArrDetail[$key]['no_bom']   = $value['no_bom'];
          $ArrDetail[$key]['nm_product']   = $value['nm_product'];
          $ArrDetail[$key]['created_by']   = $this->id_user;
          $ArrDetail[$key]['created_date']   = $this->datetime;
        }

        $getLastReq   = $this->db->get_where('so_spk_assembly_request',array('id'=>$id))->result_array();
        $SudahRequest = (!empty($getLastReq[0]['qty_out']))?$getLastReq[0]['qty_out']:0;
        $ArrHeader = [
          'no_surat_jalan' => $no_surat_jalan,
          'qty_out' => $qty_outgoing + $SudahRequest,
          'sts_close' => 'P',
          'outgoing_by' => $this->id_user,
          'outgoing_date' => $this->datetime
        ];

        //pengurangan stock
        // $ArrUpdateStokNew = [];
        // foreach ($getDetail as $key => $value) {
        //   $ArrUpdateStokNew[$key]['code_lv4'] = $value['code_lv4'];
        //   $ArrUpdateStokNew[$key]['no_bom'] = $value['no_bom'];
        //   $ArrUpdateStokNew[$key]['stok_aktual'] = $value['qty_outgoing'];
        //   $ArrUpdateStokNew[$key]['stok_booking'] = 0;
        //   $ArrUpdateStokNew[$key]['stok_downgrade'] = 0;
        //   $ArrUpdateStokNew[$key]['qty'] = $value['qty_outgoing'];
        // }
        // exit;
        $this->db->trans_start();
          $this->db->where('id', $id);
          $this->db->update('so_spk_assembly_request', $ArrHeader);

          // $this->db->where('id_spk', $id);
          // $this->db->delete('so_spk_assembly_request_outgoing');

          if(!empty($ArrDetail)){
            $this->db->insert_batch('so_spk_assembly_request_outgoing', $ArrDetail);
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
          // $this->db->where('created_by',$this->id_user);
          // $this->db->delete('spk_delivery_detail_sj_temp');
          history("Create spk delivery : ".$no_delivery);

          // if(empty($data['check_waste'])){
          // history_product($ArrUpdateStokNew, 'minus', $no_delivery, 'pengurangan fg (outgoing for cutting)');
          // }
        }
        echo json_encode($Arr_Data);
      }
      else{

        $getData    = $this->db->get_where('so_spk_assembly_request',array('id'=>$id))->result_array();
        $getDataCut = $this->db->get_where('so_spk_assembly',array('id'=>$getData[0]['id_req']))->result_array();
        $getDataSPK = $this->db->get_where('so_spk_assembly_detail',array('id'=>$getData[0]['id_detail']))->result_array();
        $getDetail 	= $this->db->get_where('so_spk_assembly_request_outgoing', array('id_spk' => $getData[0]['id']))->result_array();

        $this->db->where('created_by',$this->id_user);
        $this->db->delete('so_spk_assembly_request_outgoing_temp');

        $ArrDetail = [];
        foreach ($getDetail as $key => $value) {
          $ArrDetail[$key]['id_spk']     = $value['id_spk'];
          $ArrDetail[$key]['kode_req']      = $value['kode_req'];
          $ArrDetail[$key]['no_so']        = $value['no_so'];
          $ArrDetail[$key]['code_lv4']   = $value['code_lv4'];
          $ArrDetail[$key]['qty_spk']     = $value['qty_spk'];
          $ArrDetail[$key]['qty_outgoing']   = $value['qty_outgoing'];
          $ArrDetail[$key]['created_by']   = $this->id_user;
          $ArrDetail[$key]['no_bom']      = $value['no_bom'];
          $ArrDetail[$key]['nm_product']   = $value['nm_product'];
        }

        if(!empty($ArrDetail)){
          $this->db->insert_batch('so_spk_assembly_request_outgoing_temp', $ArrDetail);
        }
      
        $data = [
          'getData' => $getData,
          'getDataSPK' => $getDataSPK,
          'getDataCut' => $getDataCut,
          'getDetail' => $getDetail,
        ];

        $this->template->title('Outgoing Cutting Product');
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

}