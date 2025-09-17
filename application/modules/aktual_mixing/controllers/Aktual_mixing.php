<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aktual_mixing extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Aktual_Mixing.View';
    protected $addPermission  	= 'Aktual_Mixing.Add';
    protected $managePermission = 'Aktual_Mixing.Manage';
    protected $deletePermission = 'Aktual_Mixing.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Aktual_mixing/aktual_mixing_model'
                                ));
        // $this->template->title('Manage Data Supplier');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View data aktual mixing");
      $this->template->title('Gudang Material / Aktual Mixing');
      $this->template->render('index');
    }

    public function data_side_spk_material(){
  		$this->aktual_mixing_model->data_side_spk_material();
  	}

    public function print_spk(){
  		$kode	= $this->uri->segment(3);
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$printby		= get_name('users','nm_lengkap','id_user',$session['id_user']);

  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

      $getData = $this->db
                        ->select('b.*, a.*, a.id AS id_uniq')
                        ->join('so_internal b','a.id_so=b.id','left')
                        ->get_where('so_internal_spk a',array(
                            'a.kode_det'=>$kode
                          ))
                        ->result_array();

      $getMaterialMixing  = $this->db
                              ->select('a.id, b.id AS id2, b.code_material, SUM(b.weight) AS berat_req, SUM(b.mix1+b.mix2+b.mix3+b.mix4+b.mix5+b.mix6+b.mix7) AS berat_act')
                              ->group_by('a.code_material')->where('a.kode_det', $kode)
                              ->join('so_internal_spk_material_pengeluaran b','a.id=b.id_det_spk')
                              ->get_where('so_internal_spk_material a',array('a.type_name'=>'mixing','b.gudang'=>'subgudang','b.qty_ke <>'=>NULL))->result_array();

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
  			'getData' => $getData,
  			'getMaterialMixing' => $getMaterialMixing,
        'GET_MATERIAL' => get_inventory_lv4(),
  			'kode' => $kode
  		);

  		history('Print spk material '.$kode);
  		$this->load->view('print_spk', $data);
  	}

    public function getChangeMaterialMixing(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id		              = $data['id'];
      $kode		            = $data['kode'];
      $tipe_mixing_set		= $data['tipe_mixing_set'];

      $getMaterialMixing  = $this->db->select('*')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name'=>'mixing'))->result_array();
      $ArrayID = [];
      $ArrayIDData = [];
      foreach ($getMaterialMixing as $key => $value) {
        $ArrayID[] = $value['id'];

        $ArrayIDData[$key]['id_det_spk'] = $value['id'];
        $ArrayIDData[$key]['weight'] = $value['weight'];
      }

      $ArrInputMaterial = [];
      if(!empty($ArrayID)){
        $getdata = $this->db->where_in('id_det_spk',$ArrayID)->get_where('so_internal_spk_material_pengeluaran',array('qty_ke'=>$id))->result_array();
        foreach ($getdata as $key => $value) {
          $ArrInputMaterial[$key]['id_det_spk'] = $value['id_det_spk'];
          $ArrInputMaterial[$key]['qty_ke'] = $id;
          $ArrInputMaterial[$key]['weight_aktual'] = $value['weight_aktual'];
          $ArrInputMaterial[$key]['mix1'] = (!empty($value['mix1']) AND $value['mix1'] > 0)?$value['mix1']:'';
          $ArrInputMaterial[$key]['mix2'] = (!empty($value['mix2']) AND $value['mix2'] > 0)?$value['mix2']:'';
          $ArrInputMaterial[$key]['mix3'] = (!empty($value['mix3']) AND $value['mix3'] > 0)?$value['mix3']:'';
          $ArrInputMaterial[$key]['mix4'] = (!empty($value['mix4']) AND $value['mix4'] > 0)?$value['mix4']:'';
          $ArrInputMaterial[$key]['mix5'] = (!empty($value['mix5']) AND $value['mix5'] > 0)?$value['mix5']:'';
          $ArrInputMaterial[$key]['mix6'] = (!empty($value['mix6']) AND $value['mix6'] > 0)?$value['mix6']:'';
          $ArrInputMaterial[$key]['mix7'] = (!empty($value['mix7']) AND $value['mix7'] > 0)?$value['mix7']:'';
          $ArrInputMaterial[$key]['created_date'] = $value['created_date'];
          $ArrInputMaterial[$key]['close'] = (!empty($value['close_date']))?'Y':'N';
        }
      }


      $Arr_Data	= array(
          'arrayData'		=> (!empty($ArrInputMaterial))?$ArrInputMaterial:0,
          'ArrayIDData'		=> $ArrayIDData,
      );
      echo json_encode($Arr_Data);
  	}

    public function add($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

    		$id		    = $data['id_uniq'];
    		$qty_ke		= $data['qty_ke'];
    		$Detail		= $data['detail'];
    		$id		    = $data['id'];
    		$check_close		    = (!empty($data['check_close']))?$data['check_close']:0;

        $ArrInsert = [];
        $ArrStock = [];
        $ArrStockBack = [];
        $ArrayID = [];
        foreach ($Detail as $key => $value) {
            $ArrayID[] = $value['id'];
            $ArrInsert[$key]['id_det_spk'] = $value['id'];
            $ArrInsert[$key]['qty_ke'] = $qty_ke;
            $ArrInsert[$key]['code_material'] = $value['code_material'];
            $ArrInsert[$key]['weight'] = str_replace(',','',$value['berat']);
            $ArrInsert[$key]['code_material_aktual'] = $value['code_material_aktual'];
            $ArrInsert[$key]['weight_aktual'] = str_replace(',','',$value['berat_aktual']);
            $ArrInsert[$key]['created_by'] = $this->id_user;
            $ArrInsert[$key]['created_date'] = $this->datetime;
            $ArrInsert[$key]['gudang'] = 'subgudang';

            $ArrStock[$key]['id'] = $value['code_material'];
            $ArrStock[$key]['qty'] = str_replace(',','',$value['berat_aktual']);
        }

        //STockPlus
        if(!empty($ArrayID)){
          $getdata = $this->db->where_in('id_det_spk',$ArrayID)->get_where('so_internal_spk_material_pengeluaran',array('qty_ke'=>$qty_ke))->result_array();
          foreach ($getdata as $key => $value) {
            $ArrStockBack[$key]['id']   = $value['code_material_aktual'];
            $ArrStockBack[$key]['qty']  = str_replace(',','',$value['weight_aktual']);
          }
        }

        $ArrUpdate = [
          'sts_mixing' => ($check_close == '0')?'P':'Y',
          'mixing_by' => $this->id_user,
          'mixing_date' => $this->datetime
        ];

        $getData = $this->db->get_where('so_internal_spk',array('id'=>$id))->result_array();
        // print_r($getData);
        $kode_trans = $getData[0]['kode_det'].'/'.$qty_ke;
        $id_gudang_dari = $getData[0]['id_gudang'];
        $id_costcenter  = $getData[0]['id_costcenter'];
        $nm_costcenter  = strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getData[0]['id_costcenter']));
        $id_gudang_dari  = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);

        $id_gudang_ke = 14;
        // exit;

        $this->db->trans_start();
          if(!empty($ArrInsert)){
              $this->db->where_in('id_det_spk',$ArrayID);
              $this->db->where('qty_ke',$qty_ke);
              $this->db->delete('so_internal_spk_material_pengeluaran');

              $this->db->insert_batch('so_internal_spk_material_pengeluaran', $ArrInsert);
          }

          $this->db->where('id',$id);
          $this->db->update('so_internal_spk',$ArrUpdate);

        $this->db->trans_complete();

        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $Arr_Data	= array(
            'pesan'		=>'Save gagal disimpan ...',
            'status'	=> 0,
            'id' => $id,
            'check_close' => $check_close
          );
        }
        else{
          $this->db->trans_commit();
          $Arr_Data	= array(
            'pesan'		=>'Save berhasil disimpan. Thanks ...',
            'status'	=> 1,
            'id' => $id,
            'check_close' => $check_close
          );
          if(!empty($ArrStockBack)){
            move_warehouse($ArrStockBack, $id_gudang_ke, $id_gudang_dari, $kode_trans, null);
          }
          move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
          history("Pengeluaran subgudang request list : ".$id);
        }
        echo json_encode($Arr_Data);
      }
      else{

        $getData = $this->db
                        ->select('b.*, a.*, a.id AS id_uniq')
                        ->join('so_internal b','a.id_so=b.id','left')
                        ->get_where('so_internal_spk a',array(
                            'a.id'=>$id
                          ))
                        ->result_array();

        
        $id_gudang = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);
        $kode  = $getData[0]['kode_det'];
        $qty   = $getData[0]['qty'];
        $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name'=>'mixing'))->result_array();
        
        $data = [
          'getData' => $getData,
          'kode' => $kode,
          'qty' => $qty,
          'id' => $id,
          'getMaterialMixing' => $getMaterialMixing,
          'GET_STOK' => getStokMaterial($id_gudang),
          'GET_MATERIAL' => get_inventory_lv4()
        ];
        $this->template->title('Input Aktual Mixing');
        $this->template->render('add', $data);
      }
  	}

    public function saveClose(){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

    		$id		        = $data['id'];
    		$check_close  = (!empty($data['check_close']))?$data['check_close']:0;

        $ArrUpdate = [
          'sts_mixing' => ($check_close == '0')?'P':'Y',
          'mixing_by' => $this->id_user,
          'mixing_date' => $this->datetime
        ];

        $this->db->trans_start();
          $this->db->where('id',$id);
          $this->db->update('so_internal_spk',$ArrUpdate);
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
          history("Close input aktual mixing : ".$id);
        }
        echo json_encode($Arr_Data);
      }
      else{

        $getData = $this->db
                        ->select('b.*, a.*, a.id AS id_uniq')
                        ->join('so_internal b','a.id_so=b.id','left')
                        ->get_where('so_internal_spk a',array(
                            'a.id'=>$id
                          ))
                        ->result_array();

        
        $id_gudang = 2;
        $kode  = $getData[0]['kode_det'];
        $qty   = $getData[0]['qty'];
        $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name'=>'mixing'))->result_array();
        
        $data = [
          'getData' => $getData,
          'kode' => $kode,
          'qty' => $qty,
          'id' => $id,
          'getMaterialMixing' => $getMaterialMixing,
          'GET_STOK' => getStokMaterial($id_gudang),
          'GET_MATERIAL' => get_inventory_lv4()
        ];
        $this->template->title('Input Aktual Produksi');
        $this->template->render('add', $data);
      }
  	}

    public function add_new($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

    		$id		    = $data['id_uniq'];
    		$qty_ke		= $data['qty_ke'];
    		$qty_mixing		= $data['qty_mixing'];
    		$qty_mixing_max		= $data['qty_mixing'] + $qty_ke - 1;
    		$Detail		= $data['detail'];
    		$id		    = $data['id'];
    		$qty_produksi		    = $data['qty_produksi'];
    		$check_close		    = (!empty($data['check_close']))?$data['check_close']:0;
        // echo $qty_ke.'-'.$qty_mixing;
        $ArrInsert = [];
        $ArrStock = [];
        $ArrStockBack = [];
        $ArrayID = [];
        $ArrQty = [];
        foreach ($Detail as $key => $value) {
            $ArrayID[] = $value['id'];
            $berat_aktual = str_replace(',','',$value['mix1'])
                            + str_replace(',','',$value['mix2'])
                            + str_replace(',','',$value['mix3'])
                            + str_replace(',','',$value['mix4'])
                            + str_replace(',','',$value['mix5'])
                            + str_replace(',','',$value['mix6'])
                            + str_replace(',','',$value['mix7']);
            for ($i=$qty_ke; $i <= $qty_mixing_max; $i++) { 
              $ArrQty[] = $i;
              $UNIQ = $key.'-'.$i;
              $ArrInsert[$UNIQ]['id_det_spk'] = $value['id'];
              $ArrInsert[$UNIQ]['qty_ke'] = $i;
              $ArrInsert[$UNIQ]['code_material'] = $value['code_material'];
              $ArrInsert[$UNIQ]['weight'] = str_replace(',','',$value['berat']);
              $ArrInsert[$UNIQ]['code_material_aktual'] = $value['code_material_aktual'];
              $ArrInsert[$UNIQ]['weight_aktual'] = $berat_aktual / $qty_mixing;
              $ArrInsert[$UNIQ]['mix1'] = (str_replace(',','',$value['mix1']) > 0)?str_replace(',','',$value['mix1']) / $qty_mixing : 0;
              $ArrInsert[$UNIQ]['mix2'] = (str_replace(',','',$value['mix2']) > 0)?str_replace(',','',$value['mix2']) / $qty_mixing : 0;
              $ArrInsert[$UNIQ]['mix3'] = (str_replace(',','',$value['mix3']) > 0)?str_replace(',','',$value['mix3']) / $qty_mixing : 0;
              $ArrInsert[$UNIQ]['mix4'] = (str_replace(',','',$value['mix4']) > 0)?str_replace(',','',$value['mix4']) / $qty_mixing : 0;
              $ArrInsert[$UNIQ]['mix5'] = (str_replace(',','',$value['mix5']) > 0)?str_replace(',','',$value['mix5']) / $qty_mixing : 0;
              $ArrInsert[$UNIQ]['mix6'] = (str_replace(',','',$value['mix6']) > 0)?str_replace(',','',$value['mix6']) / $qty_mixing : 0;
              $ArrInsert[$UNIQ]['mix7'] = (str_replace(',','',$value['mix7']) > 0)?str_replace(',','',$value['mix7']) / $qty_mixing : 0;
              $ArrInsert[$UNIQ]['created_by'] = $this->id_user;
              $ArrInsert[$UNIQ]['created_date'] = $this->datetime;
              $ArrInsert[$UNIQ]['gudang'] = 'subgudang';
              $ArrInsert[$UNIQ]['type'] = 'mixing';
            }

            $ArrStock[$key]['id'] = $value['code_material'];
            $ArrStock[$key]['qty'] = $berat_aktual;
        }

        //STockPlus
        if(!empty($ArrayID)){
          $getdata = $this->db->where_in('id_det_spk',$ArrayID)->get_where('so_internal_spk_material_pengeluaran',array('qty_ke'=>$qty_ke))->result_array();
          foreach ($getdata as $key => $value) {
            $ArrStockBack[$key]['id']   = $value['code_material_aktual'];
            $ArrStockBack[$key]['qty']  = str_replace(',','',$value['weight_aktual']);
          }
        }

        $ArrUpdate = [
          'sts_mixing' => ($check_close == '0')?'P':'Y',
          'mixing_by' => $this->id_user,
          'mixing_date' => $this->datetime
        ];

        $getData = $this->db->get_where('so_internal_spk',array('id'=>$id))->result_array();
        // print_r($getData);
        $kode_trans = $getData[0]['kode_det'].'/'.$qty_ke;
        $id_gudang_dari = $getData[0]['id_gudang'];
        $id_costcenter  = $getData[0]['id_costcenter'];
        $nm_costcenter  = strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getData[0]['id_costcenter']));
        $id_gudang_dari  = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);

        $id_gudang_ke = 14;
        // exit;

        // echo "<pre>";
        // print_r($ArrInsert);
        // exit;

        $this->db->trans_start();
          if(!empty($ArrInsert)){
              $this->db->where_in('id_det_spk',$ArrayID);
              $this->db->where_in('qty_ke',$ArrQty);
              $this->db->delete('so_internal_spk_material_pengeluaran');

              $this->db->insert_batch('so_internal_spk_material_pengeluaran', $ArrInsert);
          }

          $this->db->where('id',$id);
          $this->db->update('so_internal_spk',$ArrUpdate);

        $this->db->trans_complete();

        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $Arr_Data	= array(
            'pesan'		=>'Save gagal disimpan ...',
            'status'	=> 0,
            'id' => $id,
            'check_close' => $check_close
          );
        }
        else{
          $this->db->trans_commit();

          $checkCLose = (!empty(checkInputMixingQty($id)[$id]))?checkInputMixingQty($id)[$id]:0;
          $checkInputMixingQty = ($checkCLose == $qty_produksi)?1:0;
          // echo checkInputMixingQty($id)[$id].'<br>'; 
          // echo $qty_produksi.'<br>'; 
          // echo $checkInputMixingQty; 
          // exit;
          if($checkInputMixingQty == 1){
            $ArrUpdate = [
              'sts_mixing' => 'Y'
            ];

            $this->db->where('id',$id);
            $this->db->update('so_internal_spk',$ArrUpdate);
          }
          $Arr_Data	= array(
            'pesan'		=>'Save berhasil disimpan. Thanks ...',
            'status'	=> 1,
            'id' => $id,
            'check_close' => $checkInputMixingQty
          );
          if(!empty($ArrStockBack)){
            move_warehouse($ArrStockBack, $id_gudang_ke, $id_gudang_dari, $kode_trans, null);
          }
          move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
          history("Pengeluaran subgudang request list : ".$id);
        }
        echo json_encode($Arr_Data);
      }
      else{

        $getData = $this->db
                        ->select('b.*, a.*, a.id AS id_uniq')
                        ->join('so_internal b','a.id_so=b.id','left')
                        ->get_where('so_internal_spk a',array(
                            'a.id'=>$id
                          ))
                        ->result_array();

        
        $id_gudang = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);
        $kode  = $getData[0]['kode_det'];
        $qty   = $getData[0]['qty'];
        $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name'=>'mixing'))->result_array();
      
        $GET_DET_Lv4 = get_inventory_lv4();
        $codeLv4 = $getData[0]['code_lv4'];
        $codeLv1 = (!empty($GET_DET_Lv4[$codeLv4]['code_lv1']))?$GET_DET_Lv4[$codeLv4]['code_lv1']:'';

        $data = [
          'getData' => $getData,
          'tipe_mixing_set' => (!empty($getData[0]['tipe_mixing']))?$getData[0]['tipe_mixing']:null,
          'tipe_mixing_name' => ($getData[0]['tipe_mixing'] == '1')?'Mixing Per Product':'Mixing Per SPK',
          'kode' => $kode,
          'qty' => $qty,
          'id' => $id,
          'getMaterialMixing' => $getMaterialMixing,
          'GET_STOK' => getStokMaterial($id_gudang),
          'GET_MATERIAL' => get_inventory_lv4(),
          'checkInputMixing' => checkInputMixing($kode)
        ];
        $this->template->title('Input Aktual Mixing');
        if($codeLv1 == 'P123000009'){
          $this->template->render('add_new_ftackle', $data);
        }
        else{
          $this->template->render('add_new', $data);
        }
       
      }
  	}

    public function add_new_ftackle(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id		    = $data['id_uniq'];
      $qty_ke		= 1;
      $Detail		= $data['detail'];
      $id		    = $data['id'];

      $ArrInsert = [];
      $ArrStock = [];
      $ArrStockBack = [];
      $ArrayID = [];
      foreach ($Detail as $key => $value) {
          $ArrayID[] = $value['id'];
          $ArrInsert[$key]['id_det_spk'] = $value['id'];
          $ArrInsert[$key]['qty_ke'] = $qty_ke;
          $ArrInsert[$key]['code_material'] = $value['code_material'];
          $ArrInsert[$key]['weight'] = str_replace(',','',$value['berat']);
          $ArrInsert[$key]['code_material_aktual'] = $value['code_material_aktual'];
          $ArrInsert[$key]['weight_aktual'] = str_replace(',','',$value['berat_aktual']);
          $ArrInsert[$key]['mix1'] = str_replace(',','',$value['mix1']);
          $ArrInsert[$key]['mix2'] = str_replace(',','',$value['mix2']);
          $ArrInsert[$key]['mix3'] = str_replace(',','',$value['mix3']);
          $ArrInsert[$key]['mix4'] = str_replace(',','',$value['mix4']);
          $ArrInsert[$key]['mix5'] = str_replace(',','',$value['mix5']);
          $ArrInsert[$key]['mix6'] = str_replace(',','',$value['mix6']);
          $ArrInsert[$key]['mix7'] = str_replace(',','',$value['mix7']);
          $ArrInsert[$key]['created_by'] = $this->id_user;
          $ArrInsert[$key]['created_date'] = $this->datetime;
          $ArrInsert[$key]['gudang'] = 'subgudang';
          $ArrInsert[$key]['type'] = 'mixing';

          $ArrStock[$key]['id'] = $value['code_material'];
          $ArrStock[$key]['qty'] = str_replace(',','',$value['berat_aktual']);
      }

      //STockPlus
      if(!empty($ArrayID)){
        $getdata = $this->db->where_in('id_det_spk',$ArrayID)->get_where('so_internal_spk_material_pengeluaran',array('qty_ke'=>$qty_ke))->result_array();
        foreach ($getdata as $key => $value) {
          $ArrStockBack[$key]['id']   = $value['code_material_aktual'];
          $ArrStockBack[$key]['qty']  = str_replace(',','',$value['weight_aktual']);
        }
      }

      $ArrUpdate = [
        'sts_mixing' => 'Y',
        'mixing_by' => $this->id_user,
        'mixing_date' => $this->datetime
      ];

      $getData = $this->db->get_where('so_internal_spk',array('id'=>$id))->result_array();
      $kode_trans = $getData[0]['kode_det'].'/'.$qty_ke;
      $id_costcenter  = $getData[0]['id_costcenter'];
      $nm_costcenter  = strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getData[0]['id_costcenter']));
      $id_gudang_dari  = get_name('warehouse','id','kd_gudang',$getData[0]['id_costcenter']);
      $id_gudang_ke = 14;

      // print_r($ArrInsert);
      // exit;

      $this->db->trans_start();
        if(!empty($ArrInsert)){
            $this->db->where_in('id_det_spk',$ArrayID);
            $this->db->where('qty_ke',$qty_ke);
            $this->db->delete('so_internal_spk_material_pengeluaran');

            $this->db->insert_batch('so_internal_spk_material_pengeluaran', $ArrInsert);
        }

        $this->db->where('id',$id);
        $this->db->update('so_internal_spk',$ArrUpdate);

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
        if(!empty($ArrStockBack)){
          move_warehouse($ArrStockBack, $id_gudang_ke, $id_gudang_dari, $kode_trans, null);
        }
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Pengeluaran aktual produksi : ".$id);
      }
      echo json_encode($Arr_Data);
  	}

}

?>
