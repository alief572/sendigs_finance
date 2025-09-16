<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plan_mixing extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Plan_Mixing.View';
    protected $addPermission  	= 'Plan_Mixing.Add';
    protected $managePermission = 'Plan_Mixing.Manage';
    protected $deletePermission = 'Plan_Mixing.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Plan_mixing/plan_mixing_model'
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

      $listSO = $this->db->get_where('so_internal',array('deleted_date'=>NULL))->result_array();
      $listType = $this->db->get_where('new_inventory_1',array('deleted_date'=>NULL,'category'=>'product','code_lv1 <>'=>'P123000009'))->result_array();
      $data = [
        'listSO' => $listSO,
        'listType' => $listType
      ];
      history("View data plan mixing model");
      $this->template->title('Planning Mixing');
      $this->template->render('index',$data);
    }

    public function data_side_spk_material(){
  		$this->plan_mixing_model->data_side_spk_material();
  	}

    public function request_to_subgudang(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id		        = $data['id'];
      $detail		    = $data['detail'];
      $tipe_mixing  = $data['tipe_mixing'];
      $mix1		  = str_replace(',','',$data['mix1']);
      $mix2		  = str_replace(',','',$data['mix2']);
      $mix3		  = str_replace(',','',$data['mix3']);
      $mix4		  = str_replace(',','',$data['mix4']);
      $mix5		  = str_replace(',','',$data['mix5']);
      $mix6		  = str_replace(',','',$data['mix6']);
      $mix7		  = str_replace(',','',$data['mix7']);
      $getdata = $this->db->get_where('so_internal_spk',array('id'=>$id))->result_array();

      $ArrUpdateMat = [];
      foreach ($detail as $key => $value) {
        $ArrUpdateMat[$key]['id'] = $value['id'];
        $ArrUpdateMat[$key]['mix1'] = (!empty($value['mix1']))?$value['mix1']:null;
        $ArrUpdateMat[$key]['mix2'] = (!empty($value['mix2']))?$value['mix2']:null;
        $ArrUpdateMat[$key]['mix3'] = (!empty($value['mix3']))?$value['mix3']:null;
        $ArrUpdateMat[$key]['mix4'] = (!empty($value['mix4']))?$value['mix4']:null;
        $ArrUpdateMat[$key]['mix5'] = (!empty($value['mix5']))?$value['mix5']:null;
        $ArrUpdateMat[$key]['mix6'] = (!empty($value['mix6']))?$value['mix6']:null;
        $ArrUpdateMat[$key]['mix7'] = (!empty($value['mix7']))?$value['mix7']:null;
      }

      $ArrUpdate = array(
                    'sts_request'=>'Y',
                    'tipe_mixing' => $tipe_mixing,
                    'mix1'=>$mix1,
                    'mix2'=>$mix2,
                    'mix3'=>$mix3,
                    'mix4'=>$mix4,
                    'mix5'=>$mix5,
                    'mix6'=>$mix6,
                    'mix7'=>$mix7,
                    'request_by'=>$this->id_user,
                    'request_date'=>$this->datetime
                  );
      
      if($getdata[0]['sts_request'] == 'N'){
        $this->db->where('id',$id);
        $this->db->update('so_internal_spk',$ArrUpdate);

        $this->db->update_batch('so_internal_spk_material',$ArrUpdateMat, 'id');
      }

      //New Mixing
      $ArrInsertMixing = [
        'kode' => $getdata[0]['kode'],
        'kode_det' => $getdata[0]['kode_det'],
        'no_spk' => $getdata[0]['no_spk'],
        'id_so' => $getdata[0]['id_so'],
        'tanggal' => $getdata[0]['tanggal'],
        'tanggal_est_finish' => $getdata[0]['tanggal_est_finish'],
        'qty' => $getdata[0]['qty'],
        'id_gudang' => $getdata[0]['id_gudang'],
        'id_costcenter' => $getdata[0]['id_costcenter'],
        'mix1'=>$mix1,
        'mix2'=>$mix2,
        'mix3'=>$mix3,
        'mix4'=>$mix4,
        'mix5'=>$mix5,
        'mix6'=>$mix6,
        'mix7'=>$mix7,
        'created_by' => $this->id_user,
        'created_date' => $this->datetime,
        'qty_mixing' => null,
        'tipe_mixing' => $tipe_mixing
      ];

      $getMaxMixing = $this->db->order_by('id','desc')->limit(1)->get('so_internal_spk_mixing')->result_array();
      $id_mixing = (!empty($getMaxMixing[0]['id']))?$getMaxMixing[0]['id'] + 1 : 1;

      $ArrInsertMat = [];
      foreach ($detail as $key => $value) {
        $ArrInsertMat[$key]['id_mixing']    = $id_mixing;
        $ArrInsertMat[$key]['id_spk_mat']   = $value['id'];
        $getDetMax = $this->db->get_where('so_internal_spk_material',array('id'=>$value['id']))->result_array();
      
        $ArrInsertMat[$key]['kode_det']             = (!empty($getDetMax[0]['kode_det']))?$getDetMax[0]['kode_det']:null;
        $ArrInsertMat[$key]['code_material']        = (!empty($getDetMax[0]['code_material']))?$getDetMax[0]['code_material']:null;
        if($tipe_mixing == '1'){
          $ArrInsertMat[$key]['weight']               = (!empty($getDetMax[0]['weight']))?$getDetMax[0]['weight']:null;
        }
        else{
          $ArrInsertMat[$key]['weight']               = (!empty($getDetMax[0]['weight']))?$getDetMax[0]['weight'] * $getdata[0]['qty']:null;
        }
        $ArrInsertMat[$key]['code_lv1']             = (!empty($getDetMax[0]['code_lv1']))?$getDetMax[0]['code_lv1']:null;
        $ArrInsertMat[$key]['type_name']            = (!empty($getDetMax[0]['type_name']))?$getDetMax[0]['type_name']:null;
        $ArrInsertMat[$key]['request']              = (!empty($getDetMax[0]['request']))?$getDetMax[0]['request']:null;
        $ArrInsertMat[$key]['code_material_aktual'] = (!empty($getDetMax[0]['code_material_aktual']))?$getDetMax[0]['code_material_aktual']:null;
        $ArrInsertMat[$key]['weight_aktual']        = (!empty($getDetMax[0]['weight_aktual']))?$getDetMax[0]['weight_aktual']:null;

        $ArrInsertMat[$key]['mix1'] = (!empty($value['mix1']))?$value['mix1']:null;
        $ArrInsertMat[$key]['mix2'] = (!empty($value['mix2']))?$value['mix2']:null;
        $ArrInsertMat[$key]['mix3'] = (!empty($value['mix3']))?$value['mix3']:null;
        $ArrInsertMat[$key]['mix4'] = (!empty($value['mix4']))?$value['mix4']:null;
        $ArrInsertMat[$key]['mix5'] = (!empty($value['mix5']))?$value['mix5']:null;
        $ArrInsertMat[$key]['mix6'] = (!empty($value['mix6']))?$value['mix6']:null;
        $ArrInsertMat[$key]['mix7'] = (!empty($value['mix7']))?$value['mix7']:null;
      }

      if(!empty($ArrInsertMixing)){
        $this->db->insert('so_internal_spk_mixing',$ArrInsertMixing);
      }
      if(!empty($ArrInsertMat)){
        $this->db->insert_batch('so_internal_spk_mixing_material',$ArrInsertMat);
      }

      $Arr_Data	= array(
          'status'		    => 1,
          'id'		        => $id,
          'kode_det'		  => $getdata[0]['kode_det'],
          'id_mixing'		  => $id_mixing,
          'tipe_mixing'		=> $tipe_mixing,
      );
      echo json_encode($Arr_Data);
  	}

    public function print_spk(){
  		$kode	= $this->uri->segment(3);
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$printby		= $session['id_user'];

  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

      $getMaterialMixing    = $this->db->select('code_material, weight AS berat, mix1, mix2, mix3, mix4, mix5, mix6, mix7')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name'=>'mixing'))->result_array();
      $getMaterialProduksi  = $this->db->select('code_material, SUM(weight) AS berat')->group_by('code_material')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name <>'=>'mixing'))->result_array();
      $getDataSPK = $this->db->get_where('so_internal_spk',array('kode_det'=>$kode))->result_array();

      $getData = $this->db
                ->select('
                    b.code_lv4,
                    b.nama_product,
                    a.qty AS qty_produksi,
                    b.so_number AS nomor_so,
                    a.no_spk,
                    a.tanggal AS tanggal,
                    b.due_date AS due_date,
                    b.no_bom
                ')
                ->join('so_internal b','a.id_so=b.id','left')
                ->get_where('so_internal_spk a',array(
                  'a.kode_det'=>$kode
                  ))
                ->result_array();
      
      $GET_DET_Lv4 = get_inventory_lv4();
      $codeLv4 = $getData[0]['code_lv4'];

      $codeLv1 = (!empty($GET_DET_Lv4[$codeLv4]['code_lv1']))?$GET_DET_Lv4[$codeLv4]['code_lv1']:'';

      $no_bom = $getData[0]['no_bom'];
      $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
      $NamaProduct 	      = (!empty($GetNamaBOMProduct[$no_bom]))?$GetNamaBOMProduct[$no_bom]:0;

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
        'getData' => $getData,
  			'getDataSPK' => $getDataSPK,
        'NamaProduct' => $NamaProduct,
  			'getMaterialMixing' => $getMaterialMixing,
  			'getMaterialProduksi' => $getMaterialProduksi,
        'GET_DET_Lv4' => get_inventory_lv4(),
  			'kode' => $kode
  		);

  		history('Print spk material '.$kode);
      if($codeLv1 == 'P123000009'){
        $this->load->view('print_spk_ftackle', $data);
      }
      else{
        $this->load->view('print_spk', $data);
      }
  	}

    public function plan_mixing_add($id){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      $getDataSPK = $this->db->get_where('so_internal_spk',array('id'=>$id))->result_array();
      $getData = $this->db->get_where('so_internal',array('id'=>$getDataSPK[0]['id_so']))->result_array();
      $getMaterialMixing    = $this->db->select('code_material, weight AS berat, id')->where('kode_det', $getDataSPK[0]['kode_det'])->get_where('so_internal_spk_material',array('type_name'=>'mixing'))->result_array();
      $code_lv4 = $getData[0]['code_lv4'];
      $no_bom = $getData[0]['no_bom'];
      $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
      $NamaProduct 	      = (!empty($GetNamaBOMProduct[$no_bom]))?$GetNamaBOMProduct[$no_bom]:0;

      $data = [
        'id' => $id,
        'code_lv4' => $code_lv4,
        'getDataSPK' => $getDataSPK,
        'tipe_mixing_set' => (!empty($getDataSPK[0]['tipe_mixing']))?$getDataSPK[0]['tipe_mixing']:null,
        'getData' => $getData,
        'NamaProduct' => $NamaProduct,
        'GET_DET_Lv4' => get_inventory_lv4(),
        'getMaterialMixing' => $getMaterialMixing,
      ];

      $this->template->title('Plan Mixing');
      $this->template->render('plan_mixing', $data);
    }

    //Re-Print SPK
    public function reprint_spk(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');

      $this->template->title('SPK Re-Print');
      $this->template->render('reprint_spk');
    }

    public function data_side_spk_reprint(){
  		$this->plan_mixing_model->data_side_spk_reprint();
  	}

    public function print_spk_new(){
  		$id_mixing	= $this->uri->segment(3);
  		$tipe_mixing	= $this->uri->segment(4);
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$printby		= $session['id_user'];

  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

      $getMaterialMixing    = $this->db->select('code_material, weight AS berat, mix1, mix2, mix3, mix4, mix5, mix6, mix7')->where('id_mixing', $id_mixing)->get_where('so_internal_spk_mixing_material',array('type_name'=>'mixing'))->result_array();
      $getMaterialProduksi  = $this->db->select('code_material, SUM(weight) AS berat')->group_by('code_material')->where('id_mixing', $id_mixing)->get_where('so_internal_spk_mixing_material',array('type_name <>'=>'mixing'))->result_array();
      $getDataSPK = $this->db->get_where('so_internal_spk_mixing',array('id'=>$id_mixing))->result_array();

      $getData = $this->db
                ->select('
                    b.code_lv4,
                    b.nama_product,
                    a.qty AS qty_produksi,
                    b.so_number AS nomor_so,
                    a.no_spk,
                    a.tanggal AS tanggal,
                    b.due_date AS due_date,
                    b.no_bom
                ')
                ->join('so_internal b','a.id_so=b.id','left')
                ->get_where('so_internal_spk_mixing a',array(
                  'a.id'=>$id_mixing
                  ))
                ->result_array();
      
      $GET_DET_Lv4 = get_inventory_lv4();
      $codeLv4 = $getData[0]['code_lv4'];

      $codeLv1 = (!empty($GET_DET_Lv4[$codeLv4]['code_lv1']))?$GET_DET_Lv4[$codeLv4]['code_lv1']:'';

      $no_bom = $getData[0]['no_bom'];
      $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
      $NamaProduct 	      = (!empty($GetNamaBOMProduct[$no_bom]))?$GetNamaBOMProduct[$no_bom]:0;

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
        'getData' => $getData,
  			'getDataSPK' => $getDataSPK,
        'NamaProduct' => $NamaProduct,
  			'getMaterialMixing' => $getMaterialMixing,
  			'getMaterialProduksi' => $getMaterialProduksi,
        'GET_DET_Lv4' => get_inventory_lv4(),
  			'kode' => $getDataSPK[0]['kode_det'],
        'tipe_mixing'		=> $tipe_mixing,
  		);

  		history('Print spk material '.$kode);

      if($tipe_mixing == 1){
        $this->load->view('print_spk_new', $data);
      }
      else{
        $this->load->view('print_spk_new_satuan', $data);
      }
  	}

    public function reprint_spk_new(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');

      $this->template->title('SPK Re-Print Parsial Mixing');
      $this->template->render('reprint_spk_new');
    }

    public function data_side_spk_reprint_new(){
  		$this->plan_mixing_model->data_side_spk_reprint_new();
  	}

}

?>
