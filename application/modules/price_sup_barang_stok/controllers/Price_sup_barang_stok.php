<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Price_sup_barang_stok extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Price_Supplier_Barang_Stok.View';
    protected $addPermission  	= 'Price_Supplier_Barang_Stok.Add';
    protected $managePermission = 'Price_Supplier_Barang_Stok.Manage';
    protected $deletePermission = 'Price_Supplier_Barang_Stok.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Price_sup_barang_stok/Price_sup_barang_stok_model'
        ));
        $this->template->title('Manage Material Jenis');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');

    		$this->template->page_icon('fa fa-users');
    		
        $where = [
          'deleted_date' => NULL
        ];
        $listData = $this->Price_sup_barang_stok_model->get_data($where);

        $data = [
          'result' =>  $listData
        ];
        
        history("View index price from supplier barang stok");
        $this->template->set($data);
        $this->template->title('Price From Supplier >> Barang Stok');
        $this->template->render('index');
    }

    public function add($id=null){	
      if(empty($id)){
        $this->auth->restrict($this->addPermission);
      }
      else{
        $this->auth->restrict($this->managePermission);
      }		
      if($this->input->post()){
        $post = $this->input->post();

        $id                 = $post['id'];
        $price_ref_new      = str_replace(',','',$post['price_ref_new']);
        $price_ref_new_usd      = str_replace(',','',$post['price_ref_new_usd']);
        $price_ref_high_new     = str_replace(',','',$post['price_ref_high_new']);
        $price_ref_high_new_usd = str_replace(',','',$post['price_ref_high_new_usd']);
        $kurs = str_replace(',','',$post['kurs']);
        $price_ref_expired  = $post['price_ref_expired'];
        $note               = $post['note'];

        $dataProcess1 = [
          'price_ref_new'           => $price_ref_new,
          'price_ref_high_new'      => $price_ref_high_new,
          'price_ref_new_usd'       => $price_ref_new_usd,
          'price_ref_high_new_usd'  => $price_ref_high_new_usd,
          'price_ref_new_expired'  => $price_ref_expired,
          'kurs'  => $kurs,
          'price_ref_new_date'  => date('Y-m-d'),
          'note'  => $note,
          'status_app'  => 'Y',
          'app_by'	  => $this->id_user,
          'app_date'	=> $this->datetime
        ];

        //UPLOAD DOCUMENT
        $dataProcess2 = [];
        if(!empty($_FILES['photo']["tmp_name"])){
          $target_dir     = "assets/files/";
          $target_dir_u   = get_root3()."/assets/files/";
          $name_file      = 'evidence-stok-'.$id."-".date('Ymdhis');
          $target_file    = $target_dir . basename($_FILES['photo']["name"]);
          $name_file_ori  = basename($_FILES['photo']["name"]);
          $imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); 
          $nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
          
          // if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){
          
            $terupload = move_uploaded_file($_FILES['photo']["tmp_name"], $nama_upload);
            $link_url    	= $target_dir.$name_file.".".$imageFileType;

            $dataProcess2	= array('upload_file' => $link_url);
          // }
        }

        $dataProcess = array_merge($dataProcess1,$dataProcess2);

        // print_r($dataProcess);
        // exit;

        $this->db->trans_start();
            $this->db->where('id',$id);
            $this->db->update('accessories',$dataProcess);
        $this->db->trans_complete();	

        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $status	= array(
            'pesan'		=>'Failed process data!',
            'status'	=> 0
          );
        } else {
          $this->db->trans_commit();
          $status	= array(
            'pesan'		=>'Success process data!',
            'status'	=> 1
          );
          history("Update price supplier barang stok: ".$id);
        }
        echo json_encode($status);
      }
      else{
        $listData = $this->db->get_where('accessories',array('id' => $id))->result();

        $data = [
          'listData' => $listData,
        ];
        $this->template->set($data);
        $this->template->render('add');
      }
    }

    public function update_kurs(){
      $data 		= $this->input->post();
      $session 	= $this->session->userdata('app_session');
      $id  		  = $data['id'];
      $kurs   	= $this->db->order_by('id','desc')->limit(1)->get_where('master_kurs',array('deleted_date'=>NULL))->result();

      $ArrHeader = array(
        'id_kurs'		=> $kurs[0]->id,
        'kurs'	=> $kurs[0]->kurs
        // 'kurs_tanggal'=> $kurs[0]->tanggal,
        // 'kurs_by'	  	=> $session['id_user'],
        // 'kurs_date'	=> date('Y-m-d H:i:s')
      );

      $this->db->trans_start();
          $this->db->where('id', $id);
          $this->db->update('accessories', $ArrHeader);
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0,
          'kurs' => number_format($kurs[0]->kurs)
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Kurs berhasil di update ...',
          'status'	=> 1,
          'kurs' => number_format($kurs[0]->kurs)
        );
        history("Update Kurs di master stok");
      }

      echo json_encode($Arr_Data);
    }

}
