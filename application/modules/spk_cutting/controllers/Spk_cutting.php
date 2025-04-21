<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Spk_cutting extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'SPK_Cutting.View';
    protected $addPermission  	= 'SPK_Cutting.Add';
    protected $managePermission = 'SPK_Cutting.Manage';
    protected $deletePermission = 'SPK_Cutting.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array(
          'Spk_cutting/Spk_cutting_model',
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
      $this->template->page_icon('fa fa-users');

      history("View index spk cutting");
      $this->template->title('SPK Cutting');
      $this->template->render('index');
    }

    public function data_side_request_produksi(){
  		$this->Spk_cutting_model->data_side_request_produksi();
  	}

    public function add($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

    		$id		    = $data['id'];
    		$so_number= $data['so_number'];
    		$Detail		= $data['Detail'];

        $Ym = date('ym');
        $SQL			  = "SELECT MAX(kode) as maxP FROM so_spk_cutting_request WHERE kode LIKE 'REQ".$Ym."%' ";
        $result		  = $this->db->query($SQL)->result_array();
        $angkaUrut2		= $result[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 7, 4);
        $urutan2++;
        $urut2			  = sprintf('%04s',$urutan2);
        $kode		      = "REQ".$Ym.$urut2;

        $Y          = date('y');
        $SQL			  = "SELECT MAX(no_spk) as maxP FROM so_spk_cutting_request WHERE no_spk LIKE 'CUT.".$Y.".%' ";
        // echo $SQL; exit;
        $result		  = $this->db->query($SQL)->result_array();
        $angkaUrut2		= $result[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 7, 4);
        $urutan2++;
        $urut2			  = sprintf('%04s',$urutan2);
        $no_spk		      = "CUT.".$Y.'.'.$urut2;

        $ArrInsert = [];
        $ArrInsertMaterial = [];
        $SUM_REQUEST = 0;
        foreach ($Detail as $key => $value) {
          $qty = str_replace(',','',$value['qty']);
          if($qty > 0){
            $ArrInsert[$key]['id_so'] = $id;
            $ArrInsert[$key]['kode'] = $kode;
            $ArrInsert[$key]['kode_det'] = $kode.'-'.$key;
            $ArrInsert[$key]['no_spk'] = $no_spk;
            $ArrInsert[$key]['tanggal'] = date('Y-m-d',strtotime($value['tanggal']));
            $ArrInsert[$key]['tanggal_est_finish'] = date('Y-m-d',strtotime($value['tanggal_est_finish']));
            $ArrInsert[$key]['qty'] = $qty;
            $ArrInsert[$key]['id_costcenter'] = $value['costcenter'];
            $ArrInsert[$key]['created_by'] = $this->id_user;
            $ArrInsert[$key]['created_date'] = $this->datetime;

            $SUM_REQUEST += $qty;

            $dataBOM = $this->db->get_where('so_internal_material',array('so_number'=>$so_number))->result_array();
            if(!empty($dataBOM)){
              foreach ($dataBOM as $key2 => $value2) {
                $UNIQ = $key.'-'.$key2;
                $ArrInsertMaterial[$UNIQ]['kode_det'] = $kode.'-'.$key;
                $ArrInsertMaterial[$UNIQ]['code_material'] = $value2['code_material'];
                $ArrInsertMaterial[$UNIQ]['weight'] = $value2['weight'];
                $ArrInsertMaterial[$UNIQ]['code_lv1'] = $value2['code_lv1'];
                $ArrInsertMaterial[$UNIQ]['type_name'] = $value2['type_name'];
              }
            }
          }
        }

        $getData = $this->db->get_where('so_spk_cutting',array('id'=>$id))->result_array();
        $ArrUpdate = [
          'qty_request' => $SUM_REQUEST + $getData[0]['qty_request']
        ];

        $this->db->trans_start();
          if(!empty($ArrInsert)){
              $this->db->insert_batch('so_spk_cutting_request', $ArrInsert);
          }

          $this->db->where('id',$id);
          $this->db->update('so_spk_cutting',$ArrUpdate);
          if(!empty($ArrInsertMaterial)){
            $this->db->insert_batch('so_spk_cutting_material_request', $ArrInsertMaterial);
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
          history("Create spk request cutting : ".$so_number);
        }
        echo json_encode($Arr_Data);
      }
      else{

        $getData = $this->db->get_where('so_spk_cutting',array('id'=>$id))->result_array();
        $getDataReq = $this->db->get_where('so_internal_request',array('id'=>$getData[0]['id_request']))->result_array();

        $tgl1 = date_create();
        $tgl2 = date_create($getData[0]['due_date']);
        $jarak = date_diff( $tgl1, $tgl2 );

        $maxDate = $jarak->days + 1;

        $GET_CYCLETIME = get_total_time_cycletime();
        $code_lv4 = $getData[0]['code_lv4'].'-'.$getData[0]['no_bom'];
        $no_bom = $getData[0]['no_bom'];

        $getDataProduct = $this->db->get_where('bom_header',array('no_bom'=>$getData[0]['no_bom']))->result_array();

        $cycletimeMesin 	= (!empty($GET_CYCLETIME[$code_lv4]['ct_machine']))?$GET_CYCLETIME[$code_lv4]['ct_machine']:0;

        $GetNamaBOMProduct  = get_inventory_lv4();
        $NamaProduct 	      = (!empty($GetNamaBOMProduct[$getData[0]['code_lv4']]['nama']))?$GetNamaBOMProduct[$getData[0]['code_lv4']]['nama']:0;
        $qty_sisa = $getData[0]['propose'] - $getData[0]['qty_request'];
        $data = [
          'getData' => $getData,
          'getDataReq' => $getDataReq,
          'getDataProduct' => $getDataProduct,
          'maxDate' => $maxDate,
          'qty' => $qty_sisa,
          'NamaProduct' => $NamaProduct,
          'cycletime' => ($cycletimeMesin > 0)?$cycletimeMesin/60:0,
        ];

        $this->template->title('Add SPK Cutting');
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