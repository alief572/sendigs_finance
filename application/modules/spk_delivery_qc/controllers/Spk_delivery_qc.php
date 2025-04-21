<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spk_delivery_qc extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'QC_Delivery.View';
    protected $addPermission  	= 'QC_Delivery.Add';
    protected $managePermission = 'QC_Delivery.Manage';
    protected $deletePermission = 'QC_Delivery.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Spk_delivery_qc/spk_delivery_qc_model'
                                ));

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      $listSO = $this->db->get_where('tr_sales_order',array('approve'=>1))->result_array();
      $data = [
        'listSO' => $listSO
      ];
      history("View data qc delivery surat jalan");
      $this->template->title('Quality Control / SJ Ready To Delivery');
      $this->template->render('index',$data);
    }

    public function data_side_spk_material(){
  		$this->spk_delivery_qc_model->data_side_spk_material();
  	}

    public function add($no_delivery=null){
      $QUERY = "SELECT
                  a.no_so,
                  a.no_penawaran,
                  c.nm_customer,
                  a.project,
                  z.no_delivery,
                  z.no_surat_jalan,
                  z.created_date,
                  z.id,
                  z.status,
                  z.delivery_date,
                  z.delivery_address
                FROM
                  spk_delivery z
                  LEFT JOIN tr_sales_order a ON a.no_so = z.no_so
                  LEFT JOIN tr_penawaran b ON a.no_penawaran = b.no_penawaran
                  LEFT JOIN customer c ON b.id_customer = c.id_customer
                WHERE a.approve = '1' AND z.no_delivery = '".$no_delivery."' ";
      $getData = $this->db->query($QUERY)->result_array();

      $getDetail = $this->db
                      ->select('a.*')
                      ->get_where('spk_delivery_detail_sj a',array('a.no_delivery'=>$no_delivery))->result_array();
      
      $data = [
        'getData' => $getData,
        'GET_DET_Lv4' => get_inventory_lv4(),
        'getDetail' => $getDetail
      ];

      $this->db->where('created_by',$this->id_user);
      $this->db->delete('spk_delivery_detail_sj_temp');

      $ArrDetail = [];
      foreach ($getDetail as $key => $value) {
        $ArrDetail[$key]['id_spk']     = $value['id_spk'];
        $ArrDetail[$key]['no_delivery']      = $value['no_delivery'];
        $ArrDetail[$key]['no_so']        = $value['no_so'];
        $ArrDetail[$key]['code_lv4']   = $value['code_lv4'];
        $ArrDetail[$key]['qty_order']   = $value['qty_order'];
        $ArrDetail[$key]['qty_spk']   = $value['qty_spk'];
        $ArrDetail[$key]['qty_delivery']   = $value['qty_delivery'];
        $ArrDetail[$key]['created_by']   = $this->id_user;
      }

      if(!empty($ArrDetail)){
        $this->db->insert_batch('spk_delivery_detail_sj_temp', $ArrDetail);
      }

      $this->template->title('QC Surat Jalan');
      $this->template->render('add', $data);
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

      $getData        = $this->db->get_where('spk_delivery',array('no_delivery'=>$kode))->result_array();
      $getDataDetail  = $this->db->get_where('spk_delivery_detail_sj',array('no_delivery'=>$kode))->result_array();

      $QUERY = "SELECT
                    a.no_so,
                    a.no_penawaran,
                    c.nm_customer,
                    a.project,
                    z.no_delivery,
                    z.no_surat_jalan,
                    z.created_date,
                    z.id,
                    z.status,
                    z.delivery_date,
                    z.delivery_address
                  FROM
                    spk_delivery z
                    LEFT JOIN tr_sales_order a ON a.no_so = z.no_so
                    LEFT JOIN tr_penawaran b ON a.no_penawaran = b.no_penawaran
                    LEFT JOIN customer c ON b.id_customer = c.id_customer
                  WHERE a.approve = '1' AND z.no_delivery = '".$kode."' ";
        $getData2 = $this->db->query($QUERY)->result_array();

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
        'getData' => $getData,
        'getData2' => $getData2,
        'getDataDetail' => $getDataDetail,
        'GET_DET_Lv4' => get_inventory_lv4(),
  			'kode' => $kode,
  			'no_surat_jalan' => $getData[0]['no_surat_jalan'],
  		);

  		history('Print spk delivery '.$kode);
  		$this->load->view('print_spk', $data);
  	}

    public function save_detail_delivery(){
      $post 			  = $this->input->post();
      $no_delivery	= $post['no_delivery'];
      $username		  = $this->id_user;
      $datetime 		= $this->datetime;
      $qr_code      = $post['qr_code'];
      $ArrDetail    = [];

      $getListSPK = $this->db->get_where('spk_delivery_detail',array('no_delivery'=>$no_delivery,'code_lv4'=>$qr_code))->result_array();
      if(!empty($getListSPK)){
        $ArrDetail    = [
          'id_spk' => $getListSPK[0]['id'],
          'no_delivery' => $getListSPK[0]['no_delivery'],
          'no_so' => $getListSPK[0]['no_so'],
          'code_lv4' => $getListSPK[0]['code_lv4'],
          'qty_order' => $getListSPK[0]['qty_so'],
          'qty_spk' => $getListSPK[0]['qty_delivery'],
          'qty_delivery' => NULL,
          'created_by' => $username,
        ];
      }

      $check = $this->db->get_where('spk_delivery_detail_sj_temp',array('no_delivery'=>$no_delivery,'code_lv4'=>$qr_code))->result_array();
      // exit;
      
      $this->db->trans_start();
        if(!empty($ArrDetail) AND empty($check)){
          $this->db->insert('spk_delivery_detail_sj_temp', $ArrDetail);
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

      $result 	= $this->db->get_where('spk_delivery_detail_sj_temp', array('no_delivery' => $no_delivery))->result_array();
      
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
        'qty_delivery' => $qty_delivery
      ];
      
      $this->db->trans_start();
          $this->db->where('id_spk', $id_spk);
          $this->db->where('created_by', $username);
          $this->db->update('spk_delivery_detail_sj_temp', $ArrUpdate);
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
          $this->db->delete('spk_delivery_detail_sj_temp');
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

    public function deliver_to_customer(){
      $post 			  = $this->input->post();
      $username		  = $this->id_user;
      $datetime 		= $this->datetime;
      $no_delivery	      = $post['no_delivery'];
      $reject_reason	= $post['reject_reason'];

      $getProductSJ = $this->db
                          ->select('a.*, c.no_bom')
                          ->join('spk_delivery_detail b','a.no_delivery=b.no_delivery AND a.code_lv4=b.code_lv4','left')
                          ->join('tr_sales_order_detail c','b.id_so_det=c.id_so_detail','left')
                          ->get_where('spk_delivery_detail_sj a',array('a.no_delivery'=>$no_delivery))->result_array();
      $ArrUpdateStokNew = [];
      foreach ($getProductSJ as $key => $value) {
        $ArrUpdateStokNew[$key]['code_lv4'] = $value['code_lv4'];
        $ArrUpdateStokNew[$key]['no_bom'] = $value['no_bom'];
        $ArrUpdateStokNew[$key]['stok_aktual'] = $value['qty_delivery'];
        $ArrUpdateStokNew[$key]['stok_booking'] = 0;
        $ArrUpdateStokNew[$key]['stok_downgrade'] = $value['qty_delivery'];
        $ArrUpdateStokNew[$key]['qty'] = $value['qty_delivery'];
      }

      // print_r($ArrUpdateStokNew);
      // exit;
      
      $this->db->trans_start();
          $this->db->where('no_delivery', $no_delivery);
          $this->db->update('spk_delivery',array('status'=>'ON DELIVER','reject_reason'=>$reject_reason));
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
        history_product($ArrUpdateStokNew, 'minus', $no_delivery, 'pengurangan fg dan booking (delivery)');
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil disimpan. Thanks ...',
          'status'	=> 1
        );
      }
      echo json_encode($Arr_Data);
    }

    public function reject_delivery(){
      $post 			  = $this->input->post();
      $username		  = $this->id_user;
      $datetime 		= $this->datetime;
      $no_delivery	= $post['no_delivery'];
      $reject_reason	= $post['reject_reason'];
      
      $this->db->trans_start();
          $this->db->where('no_delivery', $no_delivery);
          $this->db->update('spk_delivery',array('status'=>'NOT YET DELIVER','reject_reason'=>$reject_reason));
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

?>
