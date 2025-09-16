<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_checksheet extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Report_Checksheet.View';
    protected $addPermission  	= 'Report_Checksheet.Add';
    protected $managePermission = 'Report_Checksheet.Manage';
    protected $deletePermission = 'Report_Checksheet.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Report_checksheet/report_checksheet_model'
                                ));
        // $this->template->title('Manage Data Supplier');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View data report checksheet");
      $this->template->title('Report Checksheet');
      $this->template->render('index');
    }

    public function data_side_report_checksheet(){
  		$this->report_checksheet_model->data_side_report_checksheet();
  	}

    public function add($id=null,$qty_ke=null,$tanda=null){
      $getData = $this->db
                      ->select('b.*, a.*, a.id AS id_uniq')
                      ->join('so_internal b','a.id_so=b.id','left')
                      ->get_where('so_internal_spk a',array(
                          'a.id'=>$id
                        ))
                      ->result_array();

      
      $id_gudang  = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);
      $kode       = $getData[0]['kode_det'];
      $code_lv4   = $getData[0]['code_lv4'];
      $no_bom     = $getData[0]['no_bom'];
      $qty        = $getData[0]['qty'];

      $checksheet_header = $this->db->get_where('checksheet_header',array('code_lv4'=>$code_lv4))->result_array();
      $checksheet_detail = $this->db->get_where('checksheet_detail',array('code_lv4'=>$code_lv4))->result_array();
      
      $data = [
        'checksheet_header' => $checksheet_header,
        'checksheet_detail' => $checksheet_detail,
        'GET_LOOPING' => get_checksheet_looping(),
        'GET_LOOPING_LABEL' => get_checksheet_looping_label(),
        'GET_DATA_SHEET' => get_checksheet_value($id),
        'getData' => $getData,
        'id' => $id,
        'kode' => $kode,
        'tanda' => $tanda,
        'qty_ke' => $qty_ke,
        'qty' => $qty
      ];
      $this->template->title('Input Checksheet');
      $this->template->render('add', $data);
  	}

    public function add_hourly($id=null,$qty_ke=null,$tanda=null){
      $getData = $this->db
                      ->select('b.*, a.*, a.id AS id_uniq')
                      ->join('so_internal b','a.id_so=b.id','left')
                      ->get_where('so_internal_spk a',array(
                          'a.id'=>$id
                        ))
                      ->result_array();

      
      $id_gudang  = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);
      $kode       = $getData[0]['kode_det'];
      $code_lv4   = $getData[0]['code_lv4'];
      $no_bom     = $getData[0]['no_bom'];
      $qty        = $getData[0]['qty'];

      $checksheet_header = $this->db->get_where('checksheet_header',array('code_lv4'=>$code_lv4))->result_array();
      $checksheet_detail = $this->db->get_where('checksheet_detail',array('code_lv4'=>$code_lv4))->result_array();
      
      $data = [
        'checksheet_header' => $checksheet_header,
        'checksheet_detail' => $checksheet_detail,
        'GET_LOOPING' => get_checksheet_looping(),
        'GET_LOOPING_LABEL' => get_checksheet_looping_label(),
        'GET_DATA_SHEET' => get_checksheet_value($id),
        'getData' => $getData,
        'id' => $id,
        'kode' => $kode,
        'tanda' => $tanda,
        'qty_ke' => $qty_ke,
        'qty' => $qty
      ];
      $this->template->title('Input Checksheet');
      $this->template->render('add_hourly', $data);
  	}

    public function process_input_checksheet(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      
      $id		        = $data['id'];
      $frequency		= $data['frequency'];
      $id_master		= $data['id_master'];
      $qty_ke		    = $data['qty_ke'];
      $datail       = (!empty($data['datail']))?$data['datail']:[];
      
      $ArrDetail = [];
      if(!empty($datail)){
        foreach ($datail as $key => $value) {
          if(!empty($value['ket'])){
            $ArrDetail[$key]['id_master'] = $id_master;
            $ArrDetail[$key]['frequency'] = $frequency;
            $ArrDetail[$key]['id_spk']    = $id;
            $ArrDetail[$key]['qty_ke']    = $qty_ke;
            $ArrDetail[$key]['id_detail'] = $value['id_detail'];
            $ArrDetail[$key]['id_kolom']  = $value['id_kolom'];
            $ArrDetail[$key]['ket']       = $value['ket'];
            $ArrDetail[$key]['reason']    = (!empty($value['reason']))?$value['reason']:null;
            $ArrDetail[$key]['text_kolom']    = (!empty($value['text_kolom']))?$value['text_kolom']:null;
          }
        }
      }
      // echo "<pre>";
      // print_r($ArrInsert);
      // print_r($InsertQC);
      // exit;

      $this->db->trans_start();
        if(!empty($ArrDetail)){
          $this->db->insert_batch('so_internal_checksheet', $ArrDetail);
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
        history("Input checksheet id_spk : ".$id);
      }
      echo json_encode($Arr_Data);
    }

    public function get_add(){
  		$id 	      = $this->uri->segment(3);
  		$tipe 	    = $this->uri->segment(4);
  		$id_detail 	= $this->uri->segment(5);
  		$id_uniq 	  = $this->uri->segment(6);
  		$key 	      = $this->uri->segment(7);
  		$no 	      = 0;

  		$d_Header = "";
      $d_Header .= "<tr class='header".$id_uniq."_".$id."'>";
        $d_Header .= "<td>";
          $d_Header .= "<input type='text' class='form-control input-sm' name='datail[".$key."-".$id."][text_kolom]' placeholder='Jam'>";
          $d_Header .= "<input type='hidden' name='datail[".$key."-".$id."][id_detail]' value='".$id_detail."'>";
          $d_Header .= "<input type='hidden' name='datail[".$key."-".$id."][id_kolom]' value='".$id."'>";
        $d_Header .= "</td>";
       
        if($tipe == '1'){
          $d_Header .= "<td>";
            $d_Header .= "<input type='radio' name='datail[".$key."-".$id."][ket]' value='Y'>";
            $d_Header .= "&nbsp;Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $d_Header .= "<input type='radio' name='datail[".$key."-".$id."][ket]' value='N'>";
            $d_Header .= "&nbsp;No";
          $d_Header .= "</td>";
        }
        else{
          $d_Header .= "<td>";
            $d_Header .= "<input type='text' name='datail[".$key."-".$id."][ket]' class='form-control input-sm' placeholder='Keterangan'>";
          $d_Header .= "</td>";
          
        }
        $d_Header .= "<td>";
          $d_Header .= "<input type='text' class='form-control input-sm' name='datail[".$key."-".$id."][reason]' placeholder='Reason'>";
        $d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
        $d_Header .= "</td>";
      $d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add".$id_uniq."_".$id."'>";
  			$d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-key='".$key."' data-uniq='".$id_uniq."' data-tipe='".$tipe."' data-id_detail='".$id_detail."' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

    public function add2($id=null,$qty_ke=null,$tanda=null){
      $getData = $this->db
                      ->select('b.*, a.*, a.id AS id_uniq')
                      ->join('so_internal b','a.id_so=b.id','left')
                      ->get_where('so_internal_spk a',array(
                          'a.id'=>$id
                        ))
                      ->result_array();

      
      $id_gudang  = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);
      $kode       = $getData[0]['kode_det'];
      $code_lv4   = $getData[0]['code_lv4'];
      $no_bom     = $getData[0]['no_bom'];
      $qty        = $getData[0]['qty'];

      $checksheet_header = $this->db->get_where('checksheet_header',array('code_lv4'=>$code_lv4,'deleted_date'=>null))->result_array();
      $checksheet_detail = $this->db->get_where('checksheet_detail',array('code_lv4'=>$code_lv4))->result_array();
      $nm_machine = get_name('asset','nm_asset','id',$checksheet_header[0]['id_mesin']);

      $listSurface    = $this->db->get_where('temp_checksheet',array('category'=>'surface'))->result_array();
      $listMatt       = $this->db->get_where('temp_checksheet',array('category'=>'matt'))->result_array();
      $listRooving    = $this->db->get_where('temp_checksheet',array('category'=>'rooving'))->result_array();
      $listSuhuSpeed  = $this->db->get_where('temp_checksheet',array('category'=>'suhu speed'))->result_array();
      $GET_VALUE 	= getValueChecksheetInputProduksi($id,$qty_ke);
      $GET_VALUE_MST 	= getValueChecksheet($code_lv4);

      $no_bom =$getData[0]['no_bom'];
      $GETNamaBOM = get_name_product_by_bom($no_bom);
      $nama_product = (!empty($GETNamaBOM[$no_bom]))?$GETNamaBOM[$no_bom]:'';
      
      $data = [
        'checksheet_header' => $checksheet_header,
        'checksheet_detail' => $checksheet_detail,
        'GET_LOOPING' => get_checksheet_looping(),
        'GET_LOOPING_LABEL' => get_checksheet_looping_label(),
        'GET_DATA_SHEET' => get_checksheet_value($id),
        'nm_machine' => $nm_machine,
        'nama_product' => $nama_product,
        'getData' => $getData,
        'id' => $id,
        'kode' => $kode,
        'tanda' => $tanda,
        'qty_ke' => $qty_ke,
        'qty' => $qty,
        'listSurface' => $listSurface,
        'listMatt' => $listMatt,
        'listRooving' => $listRooving,
        'listSuhuSpeed' => $listSuhuSpeed,
        'GET_VALUE' => $GET_VALUE,
        'GET_VALUE_MST' => $GET_VALUE_MST,
      ];
      $this->template->title('Input Checksheet');
      $this->template->render('add2', $data);
  	}

    public function print_checksheet($id_spk, $code_lv4){
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$printby		= $session['id_user'];

  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

      $detail   = $this->db->get_where('checksheet_detail',array('code_lv4'=>$code_lv4))->result_array();
      $header   = $this->db->get_where('checksheet_header',array('code_lv4'=>$code_lv4,'deleted_date'=>null))->result_array();
      $nm_machine = get_name('asset','nm_asset','id',$header[0]['id_mesin']);

      $listMachine = $this->db->select('MIN(id) AS id, nm_asset')->group_by('nm_asset')->get_where('asset',array('deleted_date'=>NULL,'category'=>'4'))->result_array();
      
      $listSurface    = $this->db->get_where('temp_checksheet',array('category'=>'surface'))->result_array();
      $listMatt       = $this->db->get_where('temp_checksheet',array('category'=>'matt'))->result_array();
      $listRooving    = $this->db->get_where('temp_checksheet',array('category'=>'rooving'))->result_array();
      $listSuhuSpeed  = $this->db->get_where('temp_checksheet',array('category'=>'suhu speed'))->result_array();

      $dataHeader = $this->db->select('a.no_spk, b.so_number, b.due_date, a.tanggal as plan_date, a.qty, b.no_bom')->join('so_internal b','a.id_so=b.id','inner')->get_where('so_internal_spk a',array('a.id'=>$id_spk))->result_array();
      // echo $this->db->last_query();
      $GET_VALUE 	= getValueChecksheet($code_lv4);

      $no_bom =$dataHeader[0]['no_bom'];
      $GETNamaBOM = get_name_product_by_bom($no_bom);
      $nama_product = (!empty($GETNamaBOM[$no_bom]))?$GETNamaBOM[$no_bom]:'';

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
        'nm_machine' => $nm_machine,
        'nama_product' => $nama_product,
        'dataHeader' => $dataHeader,
        'detail' => $detail,
        'listMachine' => $listMachine,
        'listSurface' => $listSurface,
        'listMatt' => $listMatt,
        'listRooving' => $listRooving,
        'listSuhuSpeed' => $listSuhuSpeed,
        'GET_VALUE' => $GET_VALUE,
  		);

  		$this->load->view('print_checksheet', $data);
  	}

    public function process_input_checksheet_new(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      
      $id		        = $data['id'];
      $id_master		= $data['id_master'];
      $qty_ke		    = $data['qty_ke'];
      $code_lv4		  = $data['code_lv4'];

      $DetailSurface    = (!empty($data['DetailSurface']))?$data['DetailSurface']:[];
      $DetailRooving    = (!empty($data['DetailRooving']))?$data['DetailRooving']:[];
      $DetailMatt       = (!empty($data['DetailMatt']))?$data['DetailMatt']:[];
      $DetailSuhuSpeed  = (!empty($data['DetailSuhuSpeed']))?$data['DetailSuhuSpeed']:[];

      $ArrDetailSurface = [];
      $ArrDetailRooving = [];
      $ArrDetailMatt = [];
      $ArrDetailSuhuSpeed = [];

      if(!empty($DetailSurface)){
        foreach($DetailSurface AS $val => $valx){
          $ArrDetailSurface[$val]['category']   = 'surface';
          $ArrDetailSurface[$val]['code_lv4']   = $code_lv4;
          $ArrDetailSurface[$val]['id_spk']   = $id;
          $ArrDetailSurface[$val]['qty_ke']   = $qty_ke;
          $ArrDetailSurface[$val]['id_master']   = $id_master;
          $ArrDetailSurface[$val]['id_checksheet'] 	= $valx['id_checksheet'];
          $ArrDetailSurface[$val]['surface'] 	      = $valx['atas'];
          // if(!empty($id)){
          //   $ArrDetailSurface[$val]['id'] 	      = $valx['id'];
          // }
        }
      }

      if(!empty($DetailRooving)){
        foreach($DetailRooving AS $val => $valx){
          $ArrDetailRooving[$val]['category']   = 'rooving';
          $ArrDetailRooving[$val]['code_lv4']   = $code_lv4;
          $ArrDetailRooving[$val]['id_spk']   = $id;
          $ArrDetailRooving[$val]['qty_ke']   = $qty_ke;
          $ArrDetailRooving[$val]['id_master']   = $id_master;
          $ArrDetailRooving[$val]['id_checksheet'] 	= $valx['id_checksheet'];
          $ArrDetailRooving[$val]['rooving'] 	      = $valx['pemakaian'];
          // if(!empty($id)){
          //   $ArrDetailRooving[$val]['id'] 	      = $valx['id'];
          // }
        }
      }

      if(!empty($DetailMatt)){
        foreach($DetailMatt AS $val => $valx){
          $ArrDetailMatt[$val]['category']   = 'matt';
          $ArrDetailMatt[$val]['code_lv4']   = $code_lv4;
          $ArrDetailMatt[$val]['id_spk']   = $id;
          $ArrDetailMatt[$val]['qty_ke']   = $qty_ke;
          $ArrDetailMatt[$val]['id_master']   = $id_master;
          $ArrDetailMatt[$val]['id_checksheet'] 	= $valx['id_checksheet'];
          $ArrDetailMatt[$val]['matt_atas'] 	      = $valx['atas'];
          $ArrDetailMatt[$val]['matt_bawah'] 	      = $valx['bawah'];
          $ArrDetailMatt[$val]['matt_kiri'] 	      = $valx['kiri'];
          $ArrDetailMatt[$val]['matt_kanan'] 	      = $valx['kanan'];
          // if(!empty($id)){
          //   $ArrDetailMatt[$val]['id'] 	      = $valx['id'];
          // }
        }
      }

      if(!empty($DetailSuhuSpeed)){
        foreach($DetailSuhuSpeed AS $val => $valx){
          $ArrDetailSuhuSpeed[$val]['category']   = 'suhu speed';
          $ArrDetailSuhuSpeed[$val]['code_lv4']   = $code_lv4;
          $ArrDetailSuhuSpeed[$val]['id_spk']   = $id;
          $ArrDetailSuhuSpeed[$val]['qty_ke']   = $qty_ke;
          $ArrDetailSuhuSpeed[$val]['id_master']   = $id_master;
          $ArrDetailSuhuSpeed[$val]['id_checksheet'] 	= $valx['id_checksheet'];
          $ArrDetailSuhuSpeed[$val]['display1'] 	      = $valx['display1'];
          $ArrDetailSuhuSpeed[$val]['display2'] 	      = $valx['display2'];
          $ArrDetailSuhuSpeed[$val]['display3'] 	      = $valx['display3'];
          $ArrDetailSuhuSpeed[$val]['dies1'] 	      = $valx['dies1'];
          $ArrDetailSuhuSpeed[$val]['dies2'] 	      = $valx['dies2'];
          $ArrDetailSuhuSpeed[$val]['dies3'] 	      = $valx['dies3'];
          $ArrDetailSuhuSpeed[$val]['speed'] 	      = $valx['speed'];
          // if(!empty($id)){
          //   $ArrDetailSuhuSpeed[$val]['id'] 	      = $valx['id'];
          // }
        }
      }
      //  print_r($ArrDetailSurface);
      //   print_r($ArrDetailRooving);
      //   print_r($ArrDetailMatt);
      //   print_r($ArrDetailSuhuSpeed);
      //   exit;

      $this->db->trans_start();
      $this->db->where('id_spk',$id);
      $this->db->where('qty_ke',$qty_ke);
      $this->db->delete('so_internal_checksheet_new');

        if(!empty($ArrDetailSurface)){
          $this->db->insert_batch('so_internal_checksheet_new',$ArrDetailSurface);
        }
        if(!empty($ArrDetailRooving)){
          $this->db->insert_batch('so_internal_checksheet_new',$ArrDetailRooving);
        }
        if(!empty($ArrDetailMatt)){
          $this->db->insert_batch('so_internal_checksheet_new',$ArrDetailMatt);
        }
        if(!empty($ArrDetailSuhuSpeed)){
          $this->db->insert_batch('so_internal_checksheet_new',$ArrDetailSuhuSpeed);
        }
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0,
          'id' => $id,
          'qty_ke' => $qty_ke,
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil disimpan. Thanks ...',
          'status'	=> 1,
          'id' => $id,
          'qty_ke' => $qty_ke,
        );
        history("Input checksheet id_spk : ".$id);
      }
      echo json_encode($Arr_Data);
    }
}

?>
