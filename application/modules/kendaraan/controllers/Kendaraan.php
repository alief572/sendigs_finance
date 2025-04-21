<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Customer
 */

class Kendaraan extends Admin_Controller {

  //Permission
  protected $viewPermission   = "Kendaraan.View";
  protected $addPermission    = "Kendaraan.Add";
  protected $managePermission = "Kendaraan.Manage";
  protected $deletePermission = "Kendaraan.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload','Image_lib'));
        $this->load->model(array('Kendaraan/Kendaraan_model',
                                 'Aktifitas/aktifitas_model'
                                ));
        $this->template->title('Manage Data Kendaraan');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
      $session = $this->session->userdata('app_session');
        $data = $this->Kendaraan_model->order_by('id_kendaraan','ASC')->find_all_by(array('kdcab'=>$session['kdcab']));

        $this->template->set('results', $data);
        $this->template->title('Kendaraan');
        $this->template->render('list');
    }

    public function create(){
    	$this->template->title('Add New Kendaraan');
        $this->template->render('kendaraan_form');
    }

    public function savekendaraan(){
      $session = $this->session->userdata('app_session');
    	$datainsert = array(
        'model'         => $this->input->post('model'),
    		'nm_kendaraan'  => $this->input->post('no_kendaraan'),
    		'no_rangka'     => $this->input->post('no_rangka'),
    		'stnk_expired'  => $this->input->post('stnk_expired'),
    		'keur_expired'  => $this->input->post('keur_expired'),
        'kdcab'         => $session['kdcab'],
    		);
    	$this->db->trans_begin();
    	if(!empty($this->input->post('id_kendaraan'))){
    		$this->db->where(array('id_kendaraan'=>$this->input->post('id_kendaraan')));
    		$this->db->update('kendaraan',$datainsert);
    	}else{
    		$datainsert['id_kendaraan'] = $this->Kendaraan_model->generate_id();
	        $this->db->insert('kendaraan',$datainsert);
	    }
	    if ($this->db->trans_status() === FALSE)
	    {
	        $this->db->trans_rollback();
	        $param = array(
	            'save' => 0,
	            'msg' => "GAGAL, tambah kendaraan..!!!"
	            );
	    }
	    else
	    {
	       $this->db->trans_commit();
	         $param = array(
	          'save' => 1,
	          'msg' => "SUKSES, simpan data kendaraan..!!!"
	          );
	    }

        echo json_encode($param);
    }

    public function edit(){
    	$id = $this->uri->segment(3);
    	$data = $this->Kendaraan_model->find_by(array('id_kendaraan'=>$id));
    	$this->template->set('detail', $data);
    	$this->template->title('Edit New Kendaraan');
        $this->template->render('kendaraan_form');
    }
}

?>
