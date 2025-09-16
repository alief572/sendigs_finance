<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Product_category extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Product_Category.View';
    protected $addPermission  	= 'Product_Category.Add';
    protected $managePermission = 'Product_Category.Manage';
    protected $deletePermission = 'Product_Category.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Product_category/Product_category_model'
        ));
        $this->template->title('Manage Product Category');
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
          'category' => 'product'
        ];
        $listData = $this->Product_category_model->get_data($where);

        $data = [
          'result' =>  $listData,
          'get_level_1' =>  get_list_inventory_lv1('product')
        ];
        
        history("View index product category");
        $this->template->set($data);
        $this->template->title('Product Category');
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
        $generate_id = $this->Product_category_model->generate_id();

        $id         = $post['id'];
        $code_lv1   = $post['code_lv1'];
        $code       = (!empty($id))?$post['code']:$generate_id;
        $status     = (!empty($id))?$post['status']:1;
        $nama       = $post['nama'];
        $code_manual       = $post['code_manual'];

        $last_by    = (!empty($id))?'updated_by':'created_by';
        $last_date  = (!empty($id))?'updated_date':'created_date';
        $label      = (!empty($id))?'Edit':'Add';

        $dataProcess = [
          'category'  => 'product',
          'code_lv1'  => $code_lv1,
          'code_lv2'  => $code,
          'nama'		  => $nama,
          'code'		  => $code_manual,
          'status'		=> $status,
          $last_by	  => $this->id_user,
          $last_date	=> $this->datetime
        ];

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('new_inventory_2',$dataProcess);
          }
          else{
            $this->db->where('id',$id);
            $this->db->update('new_inventory_2',$dataProcess);
          }
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
          history($label." product category: ".$code);
        }
        echo json_encode($status);
      }
      else{
        $listData = $this->db->get_where('new_inventory_2',array('id' => $id))->result();

        $data = [
          'listData' => $listData,
          'listLevel1' => get_list_inventory_lv1('product')
        ];
        $this->template->set($data);
        $this->template->render('add');
      }
    }

	  public function delete(){
      $this->auth->restrict($this->deletePermission);
      
      $id = $this->input->post('id');
      $data = [
        'deleted_by' 	  => $this->id_user,
        'deleted_date' 	=> $this->datetime
      ];

      $this->db->trans_begin();
      $this->db->where('id',$id)->update("new_inventory_2",$data);

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
        history("Delete product category : ".$id);
      }
      echo json_encode($status);
    }

}
