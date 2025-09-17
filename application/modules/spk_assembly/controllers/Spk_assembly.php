<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Spk_assembly extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'SPK_Assembly.View';
    protected $addPermission  	= 'SPK_Assembly.Add';
    protected $managePermission = 'SPK_Assembly.Manage';
    protected $deletePermission = 'SPK_Assembly.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array(
          'Spk_assembly/Spk_assembly_model',
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

      history("View index spk assembly");
      $this->template->title('SPK Assembly');
      $this->template->render('index');
    }

    public function data_side_request_produksi(){
  		$this->Spk_assembly_model->data_side_request_produksi();
  	}

    public function add($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

    		$id		        = $data['id'];
    		$listSingle   = $data['single'];
    		$listCutting	= $data['cutting'];

        $Ym = date('ym');
        $SQL			  = "SELECT MAX(kode) as maxP FROM so_spk_assembly_request WHERE kode LIKE 'REA".$Ym."%' ";
        $result		  = $this->db->query($SQL)->result_array();
        $angkaUrut2		= $result[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 7, 4);
        $urutan2++;
        $urut2			  = sprintf('%04s',$urutan2);
        $kode		      = "REA".$Ym.$urut2;

        $Y          = date('y');
        $SQL			  = "SELECT MAX(no_spk) as maxP FROM so_spk_assembly_request WHERE no_spk LIKE 'ASS.".$Y.".%' ";
        $result		  = $this->db->query($SQL)->result_array();
        $angkaUrut2		= $result[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 7, 4);
        
        
        $ArrUpdateSimple = [];
        $ArrReqSimple = [];
        if(!empty($data['single'])){
          foreach ($data['single'] as $key => $value) {
            $qty_req = str_replace(',','',$value['qty_req']);
            if($qty_req > 0){
              $getLastReq   = $this->db->get_where('so_spk_assembly_detail',array('id'=>$value['id']))->result_array();
              $SudahRequest = (!empty($getLastReq[0]['qty_req']))?$getLastReq[0]['qty_req']:0;

              $ArrUpdateSimple[$key]['id']      = $value['id'];
              $ArrUpdateSimple[$key]['qty_req'] = $SudahRequest + $qty_req;

              $urutan2++;
              $urut2		= sprintf('%04s',$urutan2);
              $no_spk		= "ASS.".$Y.'.'.$urut2;

              $ArrReqSimple[$key]['id_req'] = $id;
              $ArrReqSimple[$key]['id_detail'] = $value['id'];
              $ArrReqSimple[$key]['category'] = 'single';
              $ArrReqSimple[$key]['kode'] = $kode;
              $ArrReqSimple[$key]['kode_det'] = $kode.'-'.$key;
              $ArrReqSimple[$key]['no_spk'] = $no_spk;
              $ArrReqSimple[$key]['qty'] = $qty_req;
              $ArrReqSimple[$key]['created_by'] = $this->id_user;
              $ArrReqSimple[$key]['created_date'] = $this->datetime;
            }
          }
        }

        $ArrUpdateCutting = [];
        $ArrReqCutting = [];
        if(!empty($data['cutting'])){
          foreach ($data['cutting'] as $key => $value) {
            $qty_req = str_replace(',','',$value['qty_req']);
            if($qty_req > 0){
              $getLastReq   = $this->db->get_where('so_spk_assembly_detail',array('id'=>$value['id']))->result_array();
              $SudahRequest = (!empty($getLastReq[0]['qty_req']))?$getLastReq[0]['qty_req']:0;

              $ArrUpdateCutting[$key]['id']      = $value['id'];
              $ArrUpdateCutting[$key]['qty_req'] = $SudahRequest + $qty_req;

              $urutan2++;
              $urut2		= sprintf('%04s',$urutan2);
              $no_spk		= "ASS.".$Y.'.'.$urut2;

              $ArrReqCutting[$key]['id_req'] = $id;
              $ArrReqCutting[$key]['id_detail'] = $value['id'];
              $ArrReqCutting[$key]['category'] = 'cutting';
              $ArrReqCutting[$key]['kode'] = $kode;
              $ArrReqCutting[$key]['kode_det'] = $kode.'-'.$key;
              $ArrReqCutting[$key]['no_spk'] = $no_spk;
              $ArrReqCutting[$key]['qty'] = $qty_req;
              $ArrReqCutting[$key]['created_by'] = $this->id_user;
              $ArrReqCutting[$key]['created_date'] = $this->datetime;
            }
          }
        }

        // exit;
        $this->db->trans_start();
          if(!empty($ArrUpdateSimple)){
              $this->db->update_batch('so_spk_assembly_detail', $ArrUpdateSimple, 'id');
          }
          if(!empty($ArrUpdateCutting)){
            $this->db->update_batch('so_spk_assembly_detail', $ArrUpdateCutting, 'id');
          }

          if(!empty($ArrReqSimple)){
            $this->db->insert_batch('so_spk_assembly_request', $ArrReqSimple);
          }
          if(!empty($ArrReqCutting)){
            $this->db->insert_batch('so_spk_assembly_request', $ArrReqCutting);
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
            'status'	=> 1,
            'kode' => $kode
          );
          history("Create spk request assembly : ".$kode);
        }
        echo json_encode($Arr_Data);
      }
      else{

        $getData            = $this->db->get_where('so_spk_assembly',array('id'=>$id))->result_array();
        $getDataReq         = $this->db->get_where('so_internal_request',array('so_number'=>$getData[0]['so_number']))->result_array();
        $reqSingleProduct   = $this->db->get_where('so_spk_assembly_detail',array('kode_hub'=>$getData[0]['kode_hub'],'category'=>'single product'))->result_array();
        $reqCuttingProduct  = $this->db->get_where('so_spk_assembly_detail',array('kode_hub'=>$getData[0]['kode_hub'],'category'=>'cutting product'))->result_array();

        $data = [
          'getData' => $getData,
          'getDataReq' => $getDataReq,
          'reqSingleProduct' => $reqSingleProduct,
          'reqCuttingProduct' => $reqCuttingProduct
        ];

        $this->template->title('Add Request Assembly');
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

}