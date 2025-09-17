<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Produksi_request_list extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Request_List_Produksi.View';
    protected $addPermission  	= 'Request_List_Produksi.Add';
    protected $managePermission = 'Request_List_Produksi.Manage';
    protected $deletePermission = 'Request_List_Produksi.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Produksi_request_list/produksi_request_list_model'
                                ));
        // $this->template->title('Manage Data Supplier');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View data gudang produksi request");
      $this->template->title('Gudang Material / Gudang Produksi / Request List');
      $this->template->render('index');
    }

    public function data_side_spk_material(){
  		$this->produksi_request_list_model->data_side_spk_material();
  	}

    public function request_to_subgudang(){
      $data 			  = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id		    = $data['id'];
      $getdata = $this->db->get_where('so_internal_spk',array('id'=>$id))->result_array();

      $this->db->where('id',$id);
      $this->db->update('so_internal_spk',array('sts_request'=>'Y','request_by'=>$this->id_user,'request_date'=>$this->datetime));

      $Arr_Data	= array(
          'id'		=> $id,
          'kode_det'		=> $getdata[0]['kode_det'],
      );
      echo json_encode($Arr_Data);
  	}

    public function add($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

    		$id		    = $data['id_uniq'];
    		$Detail		= $data['detail'];

        $ArrInsert = [];
        $ArrStock = [];
        foreach ($Detail as $key => $value) {
            $ArrInsert[$key]['id_det_spk'] = $value['id'];
            $ArrInsert[$key]['code_material'] = $value['code_material'];
            $ArrInsert[$key]['weight'] = str_replace(',','',$value['berat']);
            $ArrInsert[$key]['code_material_aktual'] = $value['code_material_aktual'];
            $ArrInsert[$key]['weight_aktual'] = str_replace(',','',$value['berat_aktual']);
            $ArrInsert[$key]['created_by'] = $this->id_user;
            $ArrInsert[$key]['created_date'] = $this->datetime;
            $ArrInsert[$key]['gudang'] = 'produksi';

            $ArrStock[$key]['id'] = $value['code_material'];
            $ArrStock[$key]['qty'] = str_replace(',','',$value['berat_aktual']);
        }

        $ArrUpdate = [
          'sts_produksi' => 'P',
          'produksi_by' => $this->id_user,
          'produksi_date' => $this->datetime
        ];

        $getData = $this->db->get_where('so_internal_spk',array('id'=>$id))->result_array();
        // print_r($getData);
        $kode_trans = $getData[0]['kode_det'];
        $id_gudang_dari = $getData[0]['id_gudang'];
        $id_costcenter  = $getData[0]['id_costcenter'];
        $nm_costcenter  = strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getData[0]['id_costcenter']));

        // exit;

        $this->db->trans_start();
          if(!empty($ArrInsert)){
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
            'status'	=> 1,
          );
          // move_warehouse($ArrStock, $id_gudang_dari, null, $kode_trans, $nm_costcenter);
          history("Request produksi to subgudang request list : ".$id);
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
        $getMaterialMixing  = $this->db->select('id, code_material, SUM(weight) AS berat')->group_by('code_material')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name !='=>'mixing'))->result_array();
        
        $data = [
          'getData' => $getData,
          'kode' => $kode,
          'qty' => $qty,
          'getMaterialMixing' => $getMaterialMixing,
          'GET_STOK' => getStokMaterial($id_gudang),
          'GET_MATERIAL' => get_inventory_lv4()
        ];
        $this->template->title('Request Material Non-Mixing');
        $this->template->render('add', $data);
      }
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
                              ->select('a.id, b.id AS id2, b.code_material, b.weight AS berat_req, b.weight_aktual AS berat_act')
                              ->group_by('a.code_material')->where('a.kode_det', $kode)
                              ->join('so_internal_spk_material_pengeluaran b','a.id=b.id_det_spk')
                              ->get_where('so_internal_spk_material a',array('a.type_name <>'=>'mixing','b.gudang'=>'produksi'))->result_array();

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

    //request material
    public function request($id=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');
        $id_gudang			= $data['id_gudang'];
        $id_gudang_ke		= 3;
        $id_costcenter	= $data['id_costcenter'];
        $keterangan	    = $data['keterangan'];
        $tanggal  = date('Y-m-d');

    		if(!empty($data['detail'])){
          $detail			= $data['detail'];
        }

        $kode_trans = generateNoTransaksi();
        $GET_DETAIL_MAT = get_inventory_lv4();

        $ArrInsertDetail	 = array();
        $SUM_MAT = 0;
        $SUM_PACK = 0;
        if(!empty($data['detail'])){
          foreach($detail AS $val => $valx){
            $konversi     = (!empty($GET_DETAIL_MAT[$valx['id']]['konversi']))?$GET_DETAIL_MAT[$valx['id']]['konversi']:0;
            $qty_packing 	= str_replace(',','',$valx['sudah_request']);
            if($qty_packing > 0){
              $qty_berat = $qty_packing * $konversi;

              $SUM_MAT  += $qty_berat;
              $SUM_PACK += $qty_packing;
              //detail adjustmeny
              $ArrInsertDetail[$val]['kode_trans'] 		= $kode_trans;
              $ArrInsertDetail[$val]['id_material'] 	= $valx['id'];
              $ArrInsertDetail[$val]['qty_order'] 		= $qty_berat;
              $ArrInsertDetail[$val]['qty_oke'] 			= $qty_berat;
              $ArrInsertDetail[$val]['keterangan'] 		= strtolower($valx['ket_request']);
              $ArrInsertDetail[$val]['update_by'] 		= $this->id_user;
              $ArrInsertDetail[$val]['update_date'] 	= $this->datetime;
            }
          }
        }

        $ArrInsert = array(
          'kode_trans' 		  => $kode_trans,
          'category' 			  => 'request produksi',
          'jumlah_mat' 		      => $SUM_MAT,
          'jumlah_mat_packing' 	=> $SUM_PACK,
          'tanggal' 			  => $tanggal,
          'note' 		  => $keterangan,
          'id_gudang_dari' 	=> $id_gudang,
          'id_gudang_ke' 		=> $id_costcenter,
          'kd_gudang_ke' 		=> strtoupper(get_name('warehouse','kd_gudang','id',$id_costcenter)),
          'created_by' 		  => $this->id_user,
          'created_date' 		=> $this->datetime
        );

        // print_r($ArrInsert);
        // print_r($ArrInsertDetail);
        // exit;

        $this->db->trans_start();
        if(!empty($ArrInsertDetail)){
            $this->db->insert('warehouse_adjustment', $ArrInsert);
            $this->db->insert_batch('warehouse_adjustment_detail', $ArrInsertDetail);
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
          );
          history("Request produksi to subgudang : ".$kode_trans);
        }
        echo json_encode($Arr_Data);
      }
      else{

        $listGudang     = $this->db->where_in('desc',array('subgudang','pusat'))->get('warehouse')->result_array();
        $listCostcenter = $this->db->select('a.id AS id_costcenter, b.nama_costcenter')->join('ms_costcenter b','a.kd_gudang=b.id_costcenter')->get_where('warehouse a',array('a.desc'=>'costcenter'))->result_array();

        $data = [
          'listGudang' => $listGudang,
          'listCostcenter' => $listCostcenter,
          'GET_MATERIAL' => get_inventory_lv4()
        ];
        $this->template->title('Request Material');
        $this->template->render('request', $data);
      }
  	}

    public function server_side_request_produksi(){
      $this->produksi_request_list_model->server_side_request_produksi();
    }

    public function data_side_request_material(){
  		$this->produksi_request_list_model->data_side_request_material();
  	}

    public function print_spk_request(){
  		$kode_trans	= $this->uri->segment(3);
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$printby		= get_name('users','nm_lengkap','id_user',$session['id_user']);

  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

      $getData = $this->db->get_where('warehouse_adjustment a',array(
                                'a.kode_trans'=>$kode_trans
                              ))
                            ->result_array();

      $getDataDetail  = $this->db->get_where('warehouse_adjustment_detail a',array(
                                      'a.kode_trans'=>$kode_trans
                                    ))
                                  ->result_array();

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
  			'getData' => $getData,
  			'getDataDetail' => $getDataDetail,
        'GET_MATERIAL' => get_inventory_lv4(),
        'GET_SATUAN' => get_list_satuan(),
  			'kode' => $kode_trans
  		);

  		history('Print spk request material '.$kode_trans);
  		$this->load->view('print_spk_request', $data);
  	}

    public function modal_request_edit(){
      if($this->input->post()){
        $data 			= $this->input->post();
        $data_session	= $this->session->userdata;
  
			  $kode_trans	    = $data['kode_trans'];
			  $id_costcenter	= $data['id_costcenter'];
        // print_r($data);
        // exit;
        $GET_DETAIL_MAT = get_inventory_lv4();
  
        $ArrInsertDetail	 = array();
        $SUM_MAT = 0;
        $SUM_PACK = 0;
        if(!empty($data['detail'])){
          foreach($data['detail'] AS $val => $valx){
            $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi']))?$GET_DETAIL_MAT[$valx['id_material']]['konversi']:0;
            $qty_packing 	= str_replace(',','',$valx['edit_qty']);
            if($qty_packing > 0){
              $qty_berat = $qty_packing * $konversi;

              $SUM_MAT  += $qty_berat;
              $SUM_PACK += $qty_packing;
              //detail adjustmeny
              $ArrInsertDetail[$val]['id'] 	          = $valx['id'];
              $ArrInsertDetail[$val]['qty_order'] 		= $qty_berat;
              $ArrInsertDetail[$val]['qty_oke'] 			= $qty_berat;
              $ArrInsertDetail[$val]['keterangan'] 		= strtolower($valx['keterangan']);
              $ArrInsertDetail[$val]['update_by'] 		= $this->id_user;
              $ArrInsertDetail[$val]['update_date'] 	= $this->datetime;
            }
          }
        }
  
        $ArrInsert = array(
          'id_gudang_ke' 		=> $id_costcenter,
          'kd_gudang_ke' 		=> strtoupper(get_name('warehouse','kd_gudang','id',$id_costcenter)),
          'jumlah_mat' 		      => $SUM_MAT,
          'jumlah_mat_packing' 	=> $SUM_PACK,
          'updated_by' 		  => $this->id_user,
          'updated_date' 		=> $this->datetime
        );

        // print_r($ArrInsert);
        // print_r($ArrInsertDetail);
        // exit;
  
        // exit;
        $this->db->trans_start();
          $this->db->where('kode_trans', $kode_trans);
          $this->db->update('warehouse_adjustment', $ArrInsert);

          if(!empty($ArrInsertDetail)){
            $this->db->update_batch('warehouse_adjustment_detail',$ArrInsertDetail,'id');
          }
        $this->db->trans_complete();
  
  
        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $Arr_Data	= array(
            'pesan'		=>'Save process failed. Please try again later ...',
            'status'	=> 0
          );
        }
        else{
          $this->db->trans_commit();
          $Arr_Data	= array(
            'pesan'		=>'Save process success. Thanks ...',
            'status'	=> 1
          );
          history("Update request material (gudang produksi) : ".$kode_trans);
        }
        echo json_encode($Arr_Data);
      }
      else{
        $kode_trans = $this->uri->segment(3);
        $tanda      = $this->uri->segment(4);
  
        $getData        = $this->db->get_where('warehouse_adjustment a',array('a.kode_trans'=>$kode_trans))->result_array();
        if($getData[0]['checked'] == 'Y'){
          $tanda      = 'detail';
        }
        $getDataDetail  = $this->db->get_where('warehouse_adjustment_detail a',array('a.kode_trans'=>$kode_trans))->result_array();
        $listCostcenter     = $this->db->get_where('warehouse',array('desc'=>'costcenter'))->result_array();
        $data = array(
          'tanda' => $tanda,
          'listCostcenter' => $listCostcenter,
          'getData' => $getData,
          'getDataDetail' => $getDataDetail,
          'GET_MATERIAL' => get_inventory_lv4(),
          'GET_SATUAN' => get_list_satuan(),
          'kode' => $kode_trans,
          'costcenter' => strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getData[0]['kd_gudang_ke']))
        );
  
        $this->load->view('modal_request_edit', $data);
      }
    }

}

?>
