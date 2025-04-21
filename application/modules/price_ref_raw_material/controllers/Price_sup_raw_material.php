<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Price_ref_raw_material extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Price_Ref_Raw_Material.View';
    protected $addPermission  	= 'Price_Ref_Raw_Material.Add';
    protected $managePermission = 'Price_Ref_Raw_Material.Manage';
    protected $deletePermission = 'Price_Ref_Raw_Material.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Price_ref_raw_material/Price_ref_raw_material_model'
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
          'deleted_date' => NULL,
          'category' => 'material'
        ];
        $listData = $this->Price_ref_raw_material_model->get_data($where);

        $data = [
          'result' =>  $listData
        ];
        
        history("View index price from supplier raw materials");
        $this->template->set($data);
        $this->template->title('Price From Supplier >> Raw Materials');
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
        $generate_id = $this->Price_ref_raw_material_model->generate_id();

        $id                 = $post['id'];
        $code_lv4           = $post['code_lv4'];
        $price_ref_new      = str_replace(',','',$post['price_ref_new']);
        $price_ref_expired  = $post['price_ref_expired'];
        $note               = $post['note'];

        $dataProcess1 = [
          'price_ref_new'  => $price_ref_new,
          'price_ref_new_expired'  => $price_ref_expired,
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
          $name_file      = 'evidence-'.$code_lv4."-".date('Ymdhis');
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
            $this->db->update('new_inventory_4',$dataProcess);
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
          history("Update price supplier raw material: ".$code_lv4);
        }
        echo json_encode($status);
      }
      else{
        $listData = $this->db->get_where('new_inventory_4',array('id' => $id))->result();

        $data = [
          'listData' => $listData,
        ];
        $this->template->set($data);
        $this->template->render('add');
      }
    }

}
