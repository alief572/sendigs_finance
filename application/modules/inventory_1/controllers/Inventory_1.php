<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Inventory_1 extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Inventory_Type.View';
    protected $addPermission  	= 'Inventory_Type.Add';
    protected $managePermission = 'Inventory_Type.Manage';
    protected $deletePermission = 'Inventory_Type.Delete';

    public function __construct()
    {
        parent::__construct();

        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Inventory_1/Inventory_1_model',
		                             'Crud/Crud_model',
                                 'Aktifitas/aktifitas_model',
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index(){
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
    		$this->template->page_icon('fa fa-users');
    		$deleted = '0';
        $data = $this->Inventory_1_model->get_data('ms_inventory_type','deleted',$deleted);
        history("View index inventory type");
        $this->template->set('results', $data);
        $this->template->title('Inventory');
        $this->template->render('index');
    }

  	public function editInventory($id){
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-edit');
        $inven = $this->db->get_where('ms_inventory_type',array('id_type' => $id))->result();
        $data = [
          'inven' => $inven
        ];
        $this->template->set('results', $data);
        $this->template->title('Inventory');
        $this->template->render('edit_inventory');

    }

    public function viewInventory(){
        $this->auth->restrict($this->viewPermission);
        $id 	= $this->input->post('id');
        $cust 	= $this->Inventory_1_model->getById($id);
        $this->template->set('result', $cust);
        $this->template->render('view_inventory');
    }

    public function saveEditInventory(){
      // $this->auth->restrict($this->editPermission);
      $post = $this->input->post();
      $this->db->trans_begin();

      $data = [
        'nama'		    => $post['nm_inventory'],
        'aktif'				=> $post['status'],
        'modified_on'	=> date('Y-m-d H:i:s'),
        'modified_by'	=> $this->auth->user_id()
      ];

      $this->db->where('id_type',$post['id_inventory'])->update("ms_inventory_type",$data);

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $status	= array(
          'pesan'		=>'Gagal Save Item. Thanks ...',
          'status'	=> 0
        );
      } else {
        $this->db->trans_commit();
        $status	= array(
          'pesan'		=>'Success Save Item. Thanks ...',
          'status'	=> 1
        );
        history("Update inventory type : ".$post['id_inventory']." / ".$post['nm_inventory']);
      }
      echo json_encode($status);
    }

	public function addInventory()  {
        $this->template->render('add_inventory');
    }

	public function deleteInventory(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_type',$id)->update("ms_inventory_type",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);
      history("Delete inventory type : ".$id);
		}

  		echo json_encode($status);
	}
	public function saveNewinventory()
    {
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Inventory_1_model->generate_id();
		$this->db->trans_begin();
		$data = [
			'id_type'		=> $code,
			'nama'		=> $post['nm_inventory'],
			'aktif'				=> 'aktif',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];

		$insert = $this->db->insert("ms_inventory_type",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);
      history("Insert inventory type : ".$code." / ".$post['nm_inventory']);
		}

  		echo json_encode($status);

    }

}
