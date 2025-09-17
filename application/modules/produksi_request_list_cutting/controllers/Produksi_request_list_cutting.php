<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produksi_request_list_cutting extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Request_List_Produksi_Cutting.View';
    protected $addPermission  	= 'Request_List_Produksi_Cutting.Add';
    protected $managePermission = 'Request_List_Produksi_Cutting.Manage';
    protected $deletePermission = 'Request_List_Produksi_Cutting.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Produksi_request_list_cutting/produksi_request_list_cutting_model'
                                ));
        // $this->template->title('Manage Data Supplier');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View data gudang produksi request cutting");
      $this->template->title('Gudang Material / Gudang Produksi / Request List Cutting');
      $this->template->render('index');
    }

    public function data_side_spk_material(){
  		$this->produksi_request_list_cutting_model->data_side_spk_material();
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
        $id_so			    = $data['id_so'];
        $id_spk			    = $data['id_spk'];
        $id_gudang			= $data['id_gudang'];
        $id_costcenter	= $data['id_costcenter'];
        $keterangan	    = $data['keterangan'];
        $tanggal  = date('Y-m-d');

    		if(!empty($data['detail'])){
          $detail			= $data['detail'];
        }

        $kode_trans = generateNoTransaksi();
        $GET_DETAIL_MAT = get_inventory_lv4();
        $GET_LEVEL1 = get_list_inventory_lv1('material');

        $ArrInsertDetail	 = array();
        $ArrUpdateRequest	 = array();
        $ArrInsertMaterial = array();
        $SUM_MAT = 0;
        $SUM_PACK = 0;
        if(!empty($data['detail'])){
          foreach($detail AS $val => $valx){
            $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi']))?$GET_DETAIL_MAT[$valx['id_material']]['konversi']:0;
            $qty_packing 	= str_replace(',','',$valx['qty']);
            if($qty_packing > 0){
              $qty_berat = $qty_packing;

              $SUM_MAT  += $qty_berat;
              $SUM_PACK += $qty_packing;
              //detail adjustmeny
              $ArrInsertDetail[$val]['kode_trans'] 		= $kode_trans;
              $ArrInsertDetail[$val]['no_ipp'] 	      = (!empty($valx['id']))?$valx['id']:null;
              $ArrInsertDetail[$val]['id_material'] 	= $valx['id_material'];
              $ArrInsertDetail[$val]['qty_order'] 		= $qty_berat;
              $ArrInsertDetail[$val]['qty_oke'] 			= $qty_berat;
              $ArrInsertDetail[$val]['keterangan'] 		= strtolower($valx['ket']);
              $ArrInsertDetail[$val]['update_by'] 		= $this->id_user;
              $ArrInsertDetail[$val]['update_date'] 	= $this->datetime;

              if(!empty($valx['id'])){
                $getRequest = $this->db->get_where('so_spk_cutting_material_request',array('id'=>$valx['id']))->result_array();
                $qtyReq = (!empty($getRequest[0]['request']))?$getRequest[0]['request']:0;

                $ArrUpdateRequest[$val]['id'] 	    = $valx['id'];
                $ArrUpdateRequest[$val]['request'] 	= $valx['qty'] + $qtyReq;
              }
              else{
                //insert ke so_internal_spk_material
                $getCodeSPK = $this->db->select('kode_det')->get_where('so_spk_cutting_request',array('id'=>$id_spk))->result_array();
                $kode_det = (!empty($getCodeSPK[0]['kode_det']))?$getCodeSPK[0]['kode_det']:null;

                $code_lv1     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['code_lv1']))?$GET_DETAIL_MAT[$valx['id_material']]['code_lv1']:0;
                $nama_lv1     = (!empty($GET_LEVEL1[$code_lv1]['nama']))?$GET_LEVEL1[$code_lv1]['nama']:0;

                $ArrInsertMaterial[$val]['kode_det'] 		    = $kode_det;
                $ArrInsertMaterial[$val]['code_material'] 	= $valx['id_material'];
                $ArrInsertMaterial[$val]['weight'] 	        = $qty_berat;
                $ArrInsertMaterial[$val]['code_lv1'] 		    = $code_lv1;
                $ArrInsertMaterial[$val]['type_name'] 			= $nama_lv1;
                $ArrInsertMaterial[$val]['add_material'] 		= 'add';

              }
            }
          }
        }

        $ArrInsert = array(
          'kode_trans' 		  => $kode_trans,
          'no_ipp' 		      => $id_spk,
          'category' 			  => 'request produksi cutting',
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
        // print_r($ArrInsertMaterial);
        // exit;

        $this->db->trans_start();
          if(!empty($ArrInsertDetail)){
            $this->db->insert('warehouse_adjustment', $ArrInsert);
            $this->db->insert_batch('warehouse_adjustment_detail', $ArrInsertDetail);
          }
          if(!empty($ArrUpdateRequest)){
            $this->db->update_batch('so_spk_cutting_material_request',$ArrUpdateRequest,'id');
          }
          if(!empty($ArrInsertMaterial)){
            $this->db->insert_batch('so_spk_cutting_material_request',$ArrInsertMaterial);
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

        $listSO         = $this->db->select('a.*')->join('new_inventory_4 b','a.code_lv4=b.code_lv4')->get_where('so_spk_cutting a')->result_array();
        $listGudang     = $this->db->where_in('desc',array('subgudang'))->get_where('warehouse')->result_array();
        $listCostcenter = $this->db->select('a.id AS id_costcenter, b.nama_costcenter')->join('ms_costcenter b','a.kd_gudang=b.id_costcenter')->get_where('warehouse a',array('a.desc'=>'costcenter'))->result_array();

        $data = [
          'listSO' => $listSO,
          'listGudang' => $listGudang,
          'listCostcenter' => $listCostcenter,
          'GET_MATERIAL' => get_inventory_lv4()
        ];
        $this->template->title('Request Material F-Tackle');
        $this->template->render('request', $data);
      }
  	}

    public function server_side_request_produksi(){
      $this->produksi_request_list_cutting_model->server_side_request_produksi();
    }

    public function data_side_request_material(){
  		$this->produksi_request_list_cutting_model->data_side_request_material();
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

      $getData = $this->db
                            ->select('a.*, b.no_spk, c.nama_product')
                            ->join('so_spk_cutting_request b','a.no_ipp=b.id','left')
                            ->join('so_spk_cutting c','c.id=b.id_so','left')
                            ->get_where('warehouse_adjustment a',array(
                                'a.kode_trans'=>$kode_trans
                              ))
                            ->result_array();
      // echo $this->db->last_query();
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
        $ArrUpdateRequest	 = array();
        $SUM_MAT = 0;
        $SUM_PACK = 0;
        if(!empty($data['detail'])){
          foreach($data['detail'] AS $val => $valx){
            $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi']))?$GET_DETAIL_MAT[$valx['id_material']]['konversi']:0;
            $qty_packing 	= str_replace(',','',$valx['edit_qty']);
            $edit_qty_bef 	= str_replace(',','',$valx['edit_qty_bef']);

            if($qty_packing > 0){
              $qty_berat = $qty_packing ;

              $SUM_MAT  += $qty_berat;
              $SUM_PACK += $qty_packing;
              //detail adjustmeny
              $ArrInsertDetail[$val]['id'] 	          = $valx['id'];
              $ArrInsertDetail[$val]['qty_order'] 		= $qty_berat;
              $ArrInsertDetail[$val]['qty_oke'] 			= $qty_berat;
              $ArrInsertDetail[$val]['keterangan'] 		= strtolower($valx['keterangan']);
              $ArrInsertDetail[$val]['update_by'] 		= $this->id_user;
              $ArrInsertDetail[$val]['update_date'] 	= $this->datetime;

              $getRequest = $this->db->get_where('so_internal_spk_material',array('id'=>$valx['id_so']))->result_array();
              $qtyReq = (!empty($getRequest[0]['request']))?$getRequest[0]['request']:0;

              $ArrUpdateRequest[$val]['id'] 	    = $valx['id_so'];
              $ArrUpdateRequest[$val]['request'] 	= $qtyReq - $edit_qty_bef + $valx['edit_qty'];
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
        // print_r($ArrUpdateRequest);
        // exit;
  
        // exit;
        $this->db->trans_start();
          $this->db->where('kode_trans', $kode_trans);
          $this->db->update('warehouse_adjustment', $ArrInsert);

          if(!empty($ArrInsertDetail)){
            $this->db->update_batch('warehouse_adjustment_detail',$ArrInsertDetail,'id');
          }
          if(!empty($ArrUpdateRequest)){
            $this->db->update_batch('so_internal_spk_material',$ArrUpdateRequest,'id');
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

    public function get_list_spk(){
      $id_so = $this->input->post('id_so');
      $result	= $this->db->where_not_in('Y')->get_where('so_spk_cutting_request',array('id_so'=>$id_so))->result_array();

      if(!empty($result)){
        $option	= "<option value='0'>Select No. SPK</option>";
        foreach($result AS $val => $valx){
          $option .= "<option value='".$valx['id']."' data-kode_det='".$valx['kode_det']."' data-qty='".$valx['qty']."' data-tanggal='".date('d-M-Y',strtotime($valx['tanggal']))."'>".strtoupper($valx['no_spk'])."</option>";
        }
      }
      else{
        $option	= "<option value='0'>List Not Found</option>";
      }
      
      $ArrJson	= array(
        'option' => $option
      );
      // exit;
      echo json_encode($ArrJson);
    }

    public function get_estimasi_material(){
      $kode_det = $this->input->post('kode_det');
      $qty = $this->input->post('qty');
      $result	= $this->db
                      ->select('a.*, b.nama AS nm_material, b.code AS kode_material, b.code_lv4')
                      ->join('new_inventory_4 b','a.code_material=b.code_lv4','left')
                      ->get_where('so_spk_cutting_material_request a',array('a.kode_det'=>$kode_det,'a.type_name !='=>'mixing'))->result_array();


      //Stok SPK
      $id = get_name('so_spk_cutting_request','id','kode_det',$kode_det);
      $QUERY = "SELECT
                  a.id_material,
                  sum( qty_oke ) AS qty_req, 
                  sum( check_qty_oke ) AS qty 
                FROM
                  warehouse_adjustment_detail a
                  LEFT JOIN warehouse_adjustment b ON a.kode_trans = b.kode_trans 
                WHERE
                  b.no_ipp = '$id' 
                  AND category = 'request produksi cutting' 
                  AND b.deleted_date IS NULL
                GROUP BY
                  a.id_material";
      $resultStokSPK = $this->db->query($QUERY)->result_array();
      $ArrStokSPK = [];
      $ArrStokSPKAct = [];
      foreach ($resultStokSPK as $key => $value) {
      $ArrStokSPK[$value['id_material']] = $value['qty_req'];
      $ArrStokSPKAct[$value['id_material']] = $value['qty'];
      }

      $option	= "";
      if(!empty($result)){
        foreach($result AS $val => $valx){ $val++;
          $estimasi_mat = $valx['weight'] * $qty;
          $id_material = $valx['code_material'];

          $request = (!empty($ArrStokSPK[$id_material]))?$ArrStokSPK[$id_material]:0;
          $aktual = (!empty($ArrStokSPKAct[$id_material]))?$ArrStokSPKAct[$id_material]:0;

          $total_req = $request - ($request - $aktual);

          $sisa = $estimasi_mat - $total_req;
          if($estimasi_mat - $total_req < 0){
            $sisa = '';
          }

          $option .= "<tr>";
            $option .= "<td align='center'>".$val."</td>";
            $option .= "<td>".$valx['kode_material']."</td>";
            $option .= "<td>".$valx['nm_material']."</td>";
            $option .= "<td align='right'>".number_format($estimasi_mat,4)."</td>";
            $option .= "<td align='right' class='text-success text-bold'>".number_format($estimasi_mat - $total_req,4)."</td>";
            $option .= "<td align='right' class='text-primary text-bold'>".number_format($total_req,4)."</td>";
            $option .= "<td align='right'>";
              $option .= "<input type='hidden' name='detail[".$val."][id]' value='".$valx['id']."'>";
              $option .= "<input type='hidden' name='detail[".$val."][id_material]' value='".$valx['code_lv4']."'>";
              $option .= "<input type='text' name='detail[".$val."][qty]' class='form-control input-sm text-center autoNumeric4' value='".$sisa."'>";
            $option .= "</td>";
            $option .= "<td align='right'><input type='text' name='detail[".$val."][ket]' class='form-control input-sm'></td>";
          $option .= "</tr>";
        }
      }

      //mixing
      $result2	= $this->db
                      ->select('a.*, b.nama AS nm_material, b.code AS kode_material, b.code_lv4')
                      ->join('new_inventory_4 b','a.code_material=b.code_lv4','left')
                      ->get_where('so_spk_cutting_material_request a',array('a.kode_det'=>$kode_det,'a.type_name'=>'mixing'))->result_array();

      $option2	= "";
      if(!empty($result2)){
        $nomor = 99;
        foreach($result2 AS $val => $valx){ 
          $val++;
          $nomor++;
          $estimasi_mat = $valx['weight'] * $qty;

          $id_material = $valx['code_material'];

          $request = (!empty($ArrStokSPK[$id_material]))?$ArrStokSPK[$id_material]:0;
          $aktual = (!empty($ArrStokSPKAct[$id_material]))?$ArrStokSPKAct[$id_material]:0;

          $total_req = $request - ($request - $aktual);

          $sisa = $estimasi_mat - $total_req;
          if($estimasi_mat - $total_req < 0){
            $sisa = '';
          }

          $option2 .= "<tr>";
            $option2 .= "<td align='center'>".$val."</td>";
            $option2 .= "<td>".$valx['kode_material']."</td>";
            $option2 .= "<td>".$valx['nm_material']."</td>";
            $option2 .= "<td align='right'>".number_format($estimasi_mat,4)."</td>";
            $option2 .= "<td align='right' class='text-success text-bold'>".number_format($estimasi_mat - $total_req,4)."</td>";
            $option2 .= "<td align='right' class='text-primary text-bold'>".number_format($total_req,4)."</td>";
            $option2 .= "<td align='right'>";
              $option2 .= "<input type='hidden' name='detail[".$nomor."][id]' value='".$valx['id']."'>";
              $option2 .= "<input type='hidden' name='detail[".$nomor."][id_material]' value='".$valx['code_lv4']."'>";
              $option2 .= "<input type='text' name='detail[".$nomor."][qty]' class='form-control input-sm text-center autoNumeric4' value='".$sisa."'>";
            $option2 .= "</td>";
            $option2 .= "<td align='right'><input type='text' name='detail[".$nomor."][ket]' class='form-control input-sm'></td>";
          $option2 .= "</tr>";
        }
      }
      
      $ArrJson	= array(
        'option' => $option,
        'option2' => $option2
      );
      // exit;
      echo json_encode($ArrJson);
    }

    public function get_add(){
  		$id 	= $this->uri->segment(3);
  		$tanda 	= $this->uri->segment(4);
  		$no 	= 0;

      $addLabel = 'add2';
      $addHeaderLabel = 'header2';
      $material    = $this->db->get_where('new_inventory_4',array('deleted_date'=>NULL,'category'=>'material','code_lv1'=>'M123000003'))->result();
      if($tanda == 'non'){
        $addLabel = 'add';
        $addHeaderLabel = 'header';
        $material    = $this->db->get_where('new_inventory_4',array('deleted_date'=>NULL,'category'=>'material','code_lv1 <>'=>'M123000003'))->result();
      }


  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='".$addHeaderLabel."_".$id."'>";
          $d_Header .= "<td align='center'>";
          $d_Header .= "<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
          $d_Header .= "</td>";
  				$d_Header .= "<td align='center'></td>";
  				$d_Header .= "<td align='left'>";
          $d_Header .= "<select name='detail[".$id."][id_material]' class='chosen_select form-control input-sm inline-blockd material'>";
          $d_Header .= "<option value='0'>Select Material Name</option>";
          foreach($material AS $valx){
            $d_Header .= "<option value='".$valx->code_lv4."'>".strtoupper($valx->nama)."</option>";
          }
          $d_Header .= 		"</select>";
  				$d_Header .= "</td>";
          $d_Header .= "<td colspan='3'></td>";
          $d_Header .= "<td align='left'>";
          $d_Header .= "<input type='text' name='detail[".$id."][qty]' class='form-control input-md autoNumeric4 qty text-center' placeholder='Weight'>";
  				$d_Header .= "</td>";
				  $d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='detail[".$id."][ket]' class='form-control input-md' placeholder='Keterangan'>";
						  $d_Header .= "</td>";
         
  			$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='".$addLabel."_".$id."'>";
  			$d_Header .= "<td align='center' colspan='2'></td>";
  			$d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart'  data-mixing='".$tanda."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
  			$d_Header .= "<td align='center' colspan='5'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

}

?>
