<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Material extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Master_Materials.View';
    protected $addPermission  	= 'Master_Materials.Add';
    protected $managePermission = 'Master_Materials.Manage';
    protected $deletePermission = 'Master_Materials.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Material/material_model'
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
      $data = $this->material_model->get_data('cycletime_header','deleted','N');
      history("View index masterial");
      $this->template->set('results', $data);
      $this->template->title('Materials');
      $this->template->render('index');
    }

    public function data_side_material(){
  		$this->material_model->get_json_material();
  	}

    public function add(){
      if($this->input->post()){
        $data = $this->input->post();
        // print_r($data); exit;

        $session 		= $this->session->userdata('app_session');
        $code_material  = $data['code_material'];
        $code_materialx  = $data['code_material'];
        $code_company   = trim(strtoupper($data['code_company']));
        $nm_material    = trim(strtolower($data['nm_material']));
        $satuan_packing = $data['satuan_packing'];
        $konversi       = str_replace(',','',$data['konversi']);
        $unit           = $data['unit'];
        $begin_balance  = str_replace(',','',$data['begin_balance']);
        // $incoming       = str_replace(',','',$data['incoming']);
        // $outgoing       = str_replace(',','',$data['outgoing']);
        // $ending_balance = str_replace(',','',$data['ending_balance']);
        // $unit_fisik     = str_replace(',','',$data['unit_fisik']);

        $created_by   = 'updated_by';
        $created_date = 'updated_date';
        $tanda        = 'Insert ';
        if(empty($code_materialx)){
          //pengurutan kode
          $srcMtr			    = "SELECT MAX(code_material) as maxP FROM ms_material WHERE code_material LIKE 'MTL-%' ";
          $numrowMtr		  = $this->db->query($srcMtr)->num_rows();
          $resultMtr		  = $this->db->query($srcMtr)->result_array();
          $angkaUrut2		  = $resultMtr[0]['maxP'];
          $urutan2		    = (int)substr($angkaUrut2, 4, 4);
          $urutan2++;
          $urut2			    = sprintf('%04s',$urutan2);
          $code_material  = "MTL-".$urut2;

          $created_by   = 'created_by';
          $created_date = 'created_date';
          $tanda        = 'Update ';
        }

        $ArrHeader		= array(
          'code_material'		=> $code_material,
          'code_company'		=> $code_company,
          'nm_material'			=> $nm_material,
          'satuan_packing'  => $satuan_packing,
          'konversi'			  => $konversi,
          'unit'			      => $unit,
          'begin_balance'	  => $begin_balance,
          // 'incoming'			  => $incoming,
          // 'outgoing'			  => $outgoing,
          // 'ending_balance'	=> $ending_balance,
          // 'unit_fisik'			=> $unit_fisik,
          $created_by	    => $session['id_user'],
          $created_date	  => date('Y-m-d H:i:s')
        );

        // print_r($ArrHeader);
        // exit;

        $this->db->trans_start();
          if(empty($code_materialx)){
            $this->db->insert('ms_material', $ArrHeader);
          }
          if(!empty($code_materialx)){
            $this->db->where('code_material', $code_material);
            $this->db->update('ms_material', $ArrHeader);
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
    				'status'	=> 1
    			);
          history($tanda." data material ".$code_material);
    		}

    		echo json_encode($Arr_Data);

      }
      else{
      	$session  = $this->session->userdata('app_session');
        $code_material 	= $this->uri->segment(3);
		$header   		= $this->db->get_where('ms_material',array('code_material' => $code_material))->result();
		
		$satuan 		= $this->db->get_where('ms_satuan',array('deleted_date'=>NULL,'category'=>'unit'))->result();
		$satuan_packing = $this->db->get_where('ms_satuan',array('deleted_date'=>NULL,'category'=>'packing'))->result();
		$kelompok 		= $this->material_model->get_data('list','category','kelompok');

		$data = [
			'header' => $header,
			'satuan' => $satuan,
			'satuan_packing' => $satuan_packing,
			'kelompok' => $kelompok
		];
  		
		$this->template->set('results', $data);
        $this->template->title('Add Material');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add',$data);
      }
    }



	public function detail(){
    $code_material 	  = $this->uri->segment(3);
    $header   = $this->db->get_where('ms_material',array('code_material' => $code_material))->result();
    $satuan = $this->material_model->get_data('ms_satuan','deleted','N');
    $data = [
      'header' => $header,
      'satuan' => $satuan
    ];
    $this->template->set('results', $data);
		$this->template->render('detail', $data);
	}


  public function hapus(){
      $data = $this->input->post();
      $session 		= $this->session->userdata('app_session');
      $code_material  = $data['id'];

      $ArrHeader		= array(
        'deleted'			  => "Y",
        'deleted_by'	  => $session['id_user'],
        'deleted_date'	=> date('Y-m-d H:i:s')
      );

      $this->db->trans_start();
          $this->db->where('code_material', $code_material);
          $this->db->update('ms_material', $ArrHeader);
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
        history("Delete data material ".$code_material);
      }

      echo json_encode($Arr_Data);

  }

  public function unit(){
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    $deleted = '0';
    $data = $this->material_model->get_data('ms_satuan','deleted','N');
    history("View index unit");
    $this->template->set('results', $data);
    $this->template->title('Unit');
    $this->template->render('unit');
  }

  public function data_side_unit(){
    $this->material_model->get_json_unit();
  }

  public function add_unit(){
    if($this->input->post()){
      $data = $this->input->post();
      // print_r($data); exit;

      $session 	= $this->session->userdata('app_session');
      $id       = $data['id'];
      $code     = trim(strtolower($data['code']));

      $created_by   = 'updated_by';
      $created_date = 'updated_date';
      $tanda        = 'Insert ';
      if(empty($code_materialx)){

        $created_by   = 'created_by';
        $created_date = 'created_date';
        $tanda        = 'Update ';
      }

      $ArrHeader		= array(
        'code'		=> $code,
        $created_by	    => $session['id_user'],
        $created_date	  => date('Y-m-d H:i:s')
      );

      // print_r($ArrHeader);
      // exit;

      $this->db->trans_start();
        if(empty($id)){
          $this->db->insert('ms_satuan', $ArrHeader);
        }
        if(!empty($id)){
          $this->db->where('id', $id);
          $this->db->update('ms_satuan', $ArrHeader);
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
          'status'	=> 1
        );
        history($tanda." data unit ".$id);
      }

      echo json_encode($Arr_Data);

    }
    else{
      $session  = $this->session->userdata('app_session');
      $id 	  = $this->uri->segment(3);
      $header   = $this->db->get_where('ms_satuan',array('id' => $id))->result();

      // print_r($header);
      // exit;
      $data = [
        'header' => $header,
      ];
      $this->template->set('results', $data);
      $this->template->title('Add Unit');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add_unit',$data);
    }
  }

  public function hapus_unit(){
      $data = $this->input->post();
      $session 		= $this->session->userdata('app_session');
      $code_material  = $data['id'];

      $ArrHeader		= array(
        'deleted'			  => "Y",
        'deleted_by'	  => $session['id_user'],
        'deleted_date'	=> date('Y-m-d H:i:s')
      );

      $this->db->trans_start();
          $this->db->where('id', $code_material);
          $this->db->update('ms_satuan', $ArrHeader);
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
        history("Delete data unit ".$code_material);
      }

      echo json_encode($Arr_Data);

  }

  public function get_unit(){
    $kelompok = $this->uri->segment(3);
    $konversi = '';
    if($kelompok == 'fitting'){
      $konversi = '1.00';
    }
		$query	 	= "SELECT * FROM ms_satuan WHERE deleted='N'";
		$Q_result	= $this->db->query($query)->result();
    $option = "<option value='0'>Select An Option</option>";
		foreach($Q_result as $row){
      $selected = ($row->code == 'pcs' AND $kelompok == 'fitting')?'selected':'';
		$option .= "<option value='".$row->code."' ".$selected.">".strtoupper($row->code)."</option>";
		}
		echo json_encode(array(
			'option' => $option,
      'konversi' => $konversi
		));
	}
}

?>
