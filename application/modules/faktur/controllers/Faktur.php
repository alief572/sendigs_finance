<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Invoice
 */

class Faktur extends Admin_Controller {

    //Permission

    protected $viewPermission   = "Faktur.View";
    protected $addPermission    = "Faktur.Add";
    protected $managePermission = "Faktur.Manage";
    protected $deletePermission = "Faktur.Delete";

    public function __construct()
    {
        parent::__construct();
		$this->load->model('Faktur/Faktur_model');
		$this->load->database();
        $this->template->title('Faktur');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        //$this->auth->restrict($this->viewPermission);

        $data = $this->Faktur_model->order_by('kode_req','ASC')->find_all();
        $this->template->set('results', $data);
        $this->template->title('Faktur');
        $this->template->render('list');
    }


    public function add(){
       if($this->input->post()){
			//echo"<pre>";print_r($this->input->post());exit;
			$Kode_Generate		= $this->input->post('idgen');
			$Kode_Faktur		= $this->input->post('kode_faktur');
			$tanggal			= $this->input->post('datet');
			$Tahun_Faktur		= $this->input->post('tahun_faktur');
			$no_Awal			= $this->input->post('no_awal');
			$no_Akhir			= $this->input->post('no_akhir');
			$Tgl_Faktur			= date('Y-m-d',strtotime($tanggal));
			// cek data
			$Count_Cek			= $this->Faktur_model->getCount('faktur_header',array('idgen'=>$Kode_Generate));
			if($Count_Cek > 0){
				$Arr_Return		= array(
					'status'		=> 2,
					'pesan'			=> 'ID Generate Al Ready Exists. Please Input Different ID Generate'
			   );
			}else{
				$loop			=0;
				$Arr_Detail		= array();
				for($x=intval($no_Awal);$x<=intval($no_Akhir);$x++){
					$loop++;
					$no_faktur		= str_pad($x,8,"0",STR_PAD_LEFT);
					$faktur_id		= $Kode_Faktur.'-'.substr($Tahun_Faktur,-2).'.'.$no_faktur;

					$Arr_Detail[$loop]['fakturid']		= $faktur_id;
					$Arr_Detail[$loop]['kode']			= $Kode_Faktur;
					$Arr_Detail[$loop]['idgen']			= $Kode_Generate;
					$Arr_Detail[$loop]['idfaktur']		= $no_faktur;
					$Arr_Detail[$loop]['sts']			= "0";
					$Arr_Detail[$loop]['tahun']			= $Tahun_Faktur;
				}

				$Arr_Insert	= array(
					'kode_req'		=> $this->Faktur_model->generate_kode(),
					'idgen'			=> $Kode_Generate,
					'tanggal'		=> $Tgl_Faktur,
					'tahun'			=> $Tahun_Faktur,
					'noawal'		=> $no_Awal,
					'noakhir'		=> $no_Akhir,
					'kode'			=> $Kode_Faktur,
					'status'		=> '0',
					'created_on'	=> date('Y-m-d H:i:s'),
					'created_by'	=> $this->session->userdata['app_session']['username']
				);

				$this->db->trans_begin();
				$this->db->insert('faktur_header',$Arr_Insert);
				$this->db->insert_batch('faktur_detail',$Arr_Detail);
				if($this->db->trans_status() === FALSE){
					$this->db->trans_rollback();
					$Arr_Return		= array(
							'status'		=> 2,
							'pesan'			=> 'Save Process Failed. Please Try Again...'
					   );
				}else{
					$this->db->trans_commit();
					$Arr_Return		= array(
							'status'		=> 1,
							'pesan'			=> 'Save Process Success. Thank You & Have A Nice Day...'
					   );
				}
			}

	   }else{
		   $Arr_Return		= array(
				'status'		=> 3,
				'pesan'			=> 'No Record Was Found To Process. Please Try Again...'
		   );
	   }
	   echo json_encode($Arr_Return);
    }

	public function edit(){
       if($this->input->post()){
			//echo"<pre>";print_r($this->input->post());exit;
			$Kode_Generate		= $this->input->post('idgen_det');
			$Status				= $this->input->post('approval_all');
			$Arr_Update			= array();
			$WHERE				= array(
				'idgen'		=> $Kode_Generate
			);
			$Data_Insert	= array(
				'status'	=> ($Status=='Y')?'1':'0'
			);
			if($Status=='Y'){
				$Arr_Update		= array(
					'status'		=> '1'
				);
			}

			$this->db->trans_begin();
			if($Arr_Update){
				$Count_Cek			= $this->Faktur_model->getCount('faktur_header',array('status'=>'1'));
				if($Count_Cek > 0){
					$this->db->update('faktur_header',$Arr_Update,array('status'=>'1'));
				}
			}
			$this->db->update('faktur_header',$Data_Insert,$WHERE);
			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Return		= array(
						'status'		=> 2,
						'pesan'			=> 'Set Active Process Failed. Please Try Again...'
				   );
			}else{
				$this->db->trans_commit();
				$Arr_Return		= array(
						'status'		=> 1,
						'pesan'			=> 'Set Active Process Success. Thank You & Have A Nice Day...'
				   );
			}
	   }else{
		   $Arr_Return		= array(
				'status'		=> 3,
				'pesan'			=> 'No Record Was Found To Process. Please Try Again...'
		   );
	   }
	   echo json_encode($Arr_Return);
    }


    public function view($kode){
		$header				= $this->Faktur_model->getArray('faktur_header',array('kode_req'=>$kode));
		$details			= $this->Faktur_model->getArray('faktur_detail',array('idgen'=>$header[0]['idgen']));
		//echo"<pre>";print_r($details);
		$this->template->set('row_header', $header);
        //$this->template->set('customer', $customer);
        $this->template->set('row_detail', $details);

        $this->template->render('view_detail');


    }


}

?>
