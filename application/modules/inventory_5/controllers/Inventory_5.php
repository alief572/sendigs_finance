<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Inventory_5 extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Inventory_5.View';
    protected $addPermission  	= 'Inventory_5.Add';
    protected $managePermission = 'Inventory_5.Manage';
    protected $deletePermission = 'Inventory_5.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Inventory_5/Inventory_5_model',
                                 'Aktifitas/aktifitas_model',
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
       $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		
		if ($this->input->post()){
        $type  = $this->input->post("type");
		$this->template->page_icon('fa fa-gears');
		$lvl1 = $this->Inventory_5_model->get_data('ms_inventory_type','deleted','0');
		$deleted = '0';
		//$data = $this->Inventory_5_model->find_all_by(array('id_type' =>$type));
        $data = $this->Inventory_5_model->get_data_ms_material($type);
		$data1 = $this->Inventory_5_model->get_data_ms_nonmaterial($type);
		$data2 = [
			
			'inventory_1' => $lvl1,
			
		];
		
		if($type=='I2000001'){
		$this->template->set('results', $data);
	    $this->template->set('result', $data2);
        $this->template->title('Material');
        $this->template->render('index');
		}
		else
		{
		$this->template->set('results', $data1);
	    $this->template->set('result', $data2);
        $this->template->title('Non Material');
        $this->template->render('nonmaterial');
		}
		
		}
		else {
        $type ='';
		$this->template->page_icon('fa fa-gears');
		$lvl1 = $this->Inventory_5_model->get_data('ms_inventory_type','deleted','0');
		$deleted = '0';
		//$data = $this->Inventory_5_model->find_all_by(array('id_type' =>$type));
        $data = $this->Inventory_5_model->get_data_ms_material($type);
		$data1 = $this->Inventory_5_model->get_data_ms_nonmaterial($type);
		$data2 = [
			
			'inventory_1' => $lvl1,
			
		];
		$this->template->set('results', $data);
	    $this->template->set('result', $data2);
        $this->template->title('Material');
        $this->template->render('index');
		}
		
		
		
    }
	
	
	public function rutin()
    {
       $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		
		if ($this->input->post()){
        $type  = $this->input->post("type");
		$this->template->page_icon('fa fa-gears');
			$lvl1 = $this->Inventory_5_model->get_type('ms_inventory_type','id_type','!=','I2000001');
		$deleted = '0';
		//$data = $this->Inventory_5_model->find_all_by(array('id_type' =>$type));
        $data = $this->Inventory_5_model->get_data_ms_nonmaterial($type);
		$data1 = $this->Inventory_5_model->get_data_ms_nonmaterial($type);
		$data2 = [
			
			'inventory_1' => $lvl1,
			
		];
		
		if($type=='I2000001'){
		$this->template->set('results', $data);
	    $this->template->set('result', $data2);
        $this->template->title('Rutin');
        $this->template->render('rutin');
		}
		else
		{
		$this->template->set('results', $data1);
	    $this->template->set('result', $data2);
        $this->template->title('Rutin');
        $this->template->render('nonmaterial');
		}
		
		}
		else {
        $type ='';
		$this->template->page_icon('fa fa-gears');
		$lvl1 = $this->Inventory_5_model->get_type('ms_inventory_type','id_type','!=','I2000001');
		$deleted = '0';
		//$data = $this->Inventory_5_model->find_all_by(array('id_type' =>$type));
        $data = $this->Inventory_5_model->get_data_ms_nonmaterial($type);
		$data1 = $this->Inventory_5_model->get_data_ms_nonmaterial($type);
		$data2 = [
			
			'inventory_1' => $lvl1,
			
		];
		$this->template->set('results', $data);
	    $this->template->set('result', $data2);
        $this->template->title('Rutin');
        $this->template->render('rutin');
		}
		
		
		
    }
	
	 public function list_nonmaterial()
    {
       $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$type  = $this->uri->segment(3);
	
		
		$this->template->page_icon('fa fa-gears');
		$lvl1 = $this->Inventory_5_model->get_data('ms_inventory_type','deleted','0');
		$deleted = '0';
		//$data = $this->Inventory_5_model->find_all_by(array('id_type' =>$type));
        $data = $this->Inventory_5_model->get_data_ms_nonmaterial($type);
		$data2 = [
			
			'inventory_1' => $lvl1,
			
		];
		
		$this->template->set('results', $data);
	    $this->template->set('result', $data2);
        $this->template->title('Material');
        $this->template->render('nonmaterial');
    }
	
	public function editInventory($id){
		$type  = $this->uri->segment(4);		
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$inven = $this->db->get_where('ms_material',array('id_material' => $id))->result();
		$lvl1 = $this->Inventory_5_model->get_data('ms_inventory_type');
		$lvl2 = $this->Inventory_5_model->get_data('ms_inventory_category1');
		$lvl3 = $this->Inventory_5_model->get_data('ms_inventory_category2');
		$lvl4 = $this->Inventory_5_model->get_data('ms_inventory_category3');
		$satuan = $this->Inventory_5_model->get_data('ms_satuan');
		$mssupplier = $this->Inventory_5_model->get_data('master_supplier');
		$SatuanType  = $this->Inventory_5_model->getArray('ms_satuan',array(),'id','nama');
		$konversi = $this->db->get_where('ms_material_konversi',array('id_material' => $id))->result();
		$material = $this->db->get_where('ms_material_sejenis',array('id_material' => $id))->result();
		$supplier1 = $this->db->get_where('ms_material_supplier',array('id_material' => $id))->result();
		$Supplier  = $this->Inventory_5_model->getArray('master_supplier',array(),'id_supplier','name_supplier');
			
		$data = [
			'inven' => $inven,
			'lvl1' => $lvl1,
			'lvl2' => $lvl2,
			'lvl3' => $lvl3,
			'lvl4' => $lvl4,
			'satuan_type' => $SatuanType,
			'konversi' => $konversi,
			'material' => $material,
			'supplier1' => $supplier1,
			'mssupplier' => $mssupplier,
			'Supplier' => $Supplier,
			'satuan' => $satuan
		];
		
		$this->template->set('results', $data);
		if($type == 'I2000001'){
        $this->template->set('results', $data);
		$this->template->title('Edit Material');
        $this->template->render('edit_inventory');
		}
		else{
		$this->template->set('results', $data);
		$this->template->title('Edit Material');
        $this->template->render('edit_others');
		}
       
	}
	public function viewInventory(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
        $inven = $this->Inventory_5_model->get_data_id_ms_material($id);
		$konversi = $this->db->get_where('ms_material_konversi',array('id_material' => $id))->result();$material = $this->db->get_where('ms_material_sejenis',array('id_material' => $id))->result();
		$supplier = $this->db->get_where('ms_material_supplier',array('id_material' => $id))->result();
		$data = [
			'inven' => $inven,
			'konversi' => $konversi,
			'material' => $material,
			'supplier' => $supplier
			];
        $this->template->set('results', $data);
		$this->template->render('view_material');
	}
	public function viewNonmaterial(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
        $inven = $this->Inventory_5_model->get_data_id_ms_nonmaterial($id);
		$konversi = $this->db->get_where('ms_material_konversi',array('id_material' => $id))->result();
		$material = $this->db->get_where('ms_material_sejenis',array('id_material' => $id))->result();
		$supplier = $this->db->get_where('ms_material_supplier',array('id_material' => $id))->result();
		$data = [
			'inven' => $inven,
			'konversi' => $konversi,
			'material' => $material,
			'supplier' => $supplier
			];
        $this->template->set('results', $data);
		$this->template->render('view_nonmaterial');
	}
	public function saveEditInventory(){
		$session = $this->session->userdata('app_session');
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		// print_r($post);
		// exit;
		$this->db->trans_begin();
		$data = [
			'id_type'		    => $post['inventory_1'],
			'id_category1'		=> $post['inventory_2'],
			'id_category2'		=> $post['inventory_3'],
			'id_category3'		=> $post['inventory_4'],
			'nama'				=> $post['nama'],
			'aktif'				=> 'aktif',
			'spec1'				=> $post['spec1'],
			'spec2'				=> $post['spec2'],
			'spec3'				=> $post['spec3'],
			'spec4'				=> $post['spec4'],
			'spec5'				=> $post['spec5'],
			'spec6'				=> $post['spec6'],
			'spec7'				=> $post['spec7'],
			'spec8'				=> $post['spec8'],
			'spec9'				=> $post['spec9'],
			'spec10'			=> $post['spec10'],
			'spec11'		    => $post['spec11'],
			'spec12'			=> $post['spec12'],
            'spec13'			=> $post['spec13'],
            'spec14'			=> $post['spec14'],
            'spec15'			=> $post['spec15'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_on'		=> $this->auth->user_id(),
		];
	 
		$this->db->where('id_material',$post['id_material'])->update("ms_material",$data);
		
		
		
		$this->db->delete('ms_material_konversi', array('id_material' => $post['id_material']));
		
		
		$numb1 =0;
		foreach($_POST['data1'] as $d1){
		$numb1++;	
		        $tglbayar   = $d1[perkiraan_bayar];
		      	$timebayar = strtotime($tglbayar);
				$tglbyr = date('Y-m-d',$timebayar);
              $data1 =  array(
			                    'id_material'=>$post['id_material'],
								'nama_satuan'=>$d1[tipe_payment],
								'konversi'=>$d1[pembayaran],
								'satuan_konversi' =>$d1[satuan_kecil],
				                'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('ms_material_konversi',$data1);
			
		    }	
			
		$this->db->delete('ms_material_sejenis', array('id_material' => $post['id_material']));	
		$numb2 =0;
		foreach($_POST['data2'] as $d2){
		$numb2++;	
		        $tglbayar   = $d1[perkiraan_bayar];
		       
              $data2 =  array(
			                    'id_material'=>$post['id_material'], 
								'nama_material_sejenis'=>$d2[material],
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('ms_material_sejenis',$data2);
			
		    }		

        $this->db->delete('ms_material_supplier', array('id_material' => $post['id_material']));	
        $numb3 =0;
		foreach($_POST['data3'] as $d3){
		$numb3++;	
		       
		      
              $data3 =  array(
			                    'id_material'=>$post['id_material'],
								'id_supplier'=>$d3[suplier],
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('ms_material_supplier',$data3);
			
		    }					
		
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
		}
		
  		echo json_encode($status);
	
	}
	public function addInventory()
    {
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$type        = $this->uri->segment(3);
		$type1       = $this->Inventory_5_model->get_data('ms_inventory_type','id_type',$type);
		$inventory_1 = $this->Inventory_5_model->get_data('ms_inventory_type','id_type',$type);
		$inventory_2 = $this->Inventory_5_model->get_data('ms_inventory_category1','deleted','0');
		$inventory_3 = $this->Inventory_5_model->get_data('ms_inventory_category2','deleted','0');
		$inventory_4 = $this->Inventory_5_model->get_data('ms_inventory_category3','deleted','0');
		$SatuanType  = $this->Inventory_5_model->getArray('ms_satuan',array(),'id','nama');
		$Supplier  = $this->Inventory_5_model->getArray('master_supplier',array(),'id_supplier','nm_supplier_office');
		$data = [
			'inventory_1' => $inventory_1,
			'inventory_2' => $inventory_2,
			'satuan_type' => $SatuanType,
			'type_1'      => $type1,
			'Supplier'    => $Supplier
		];
		
        $this->template->set('results', $data);
		if($type == 'I2000001'){
        $this->template->title('Add Material');
        $this->template->render('add_inventory');
		}
		else{
		$this->template->title('Add Material');
        $this->template->render('add_others');
		}

    }
	public function deleteInventory(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];
		
		$this->db->trans_begin();
		$this->db->where('id_material',$id)->update("ms_material",$data);
		
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
		}
  		echo json_encode($status);
	}
	function get_inven2()
    {
        $inventory_1=$_GET['inventory_1'];
        $data=$this->Inventory_5_model->level_2($inventory_1);
		
        // print_r($data);
        // exit();
        echo "<select id='inventory_2' name='inventory_2' class='form-control input-sm select2'>";
        echo "<option value=''>--Pilih Category--</option>";
                foreach ($data as $key => $st) :
				      echo "<option value='$st->id_category1' set_select('inventory_2', $st->id_category1, isset($data->id_category1) && $data->id_category1 == $st->id_category1)>$st->nama
                    </option>";
                endforeach;
        echo "</select>";
    }
	
	function get_inven3()
    {
        $inventory_2=$_GET['inventory_2'];
        $data=$this->Inventory_5_model->level_3($inventory_2);
		
        // print_r($data);
        // exit();
        echo "<select id='inventory_3' name='inventory_3' class='form-control input-sm select2'>";
        echo "<option value=''>--Pilih Category--</option>";
                foreach ($data as $key => $st) :
				      echo "<option value='$st->id_category2' set_select('inventory_3', $st->id_category2, isset($data->id_category3) && $data->id_category2 == $st->id_category2)>$st->nama
                    </option>";
                endforeach;
        echo "</select>";
    }
	function get_inven4()
    {
        $inventory_3=$_GET['inventory_3'];
        $data=$this->Inventory_5_model->level_4($inventory_3);
		
        // print_r($data);
        // exit();
        echo "<select id='inventory_4' name='inventory_4' class='form-control input-sm select2'>";
        echo "<option value=''>--Pilih Category--</option>";
                foreach ($data as $key => $st) :
				      echo "<option value='$st->id_category3' set_select('inventory_4', $st->id_category3, isset($data->id_category4) && $data->id_category3 == $st->id_category3)>$st->nama
                    </option>";
                endforeach;
        echo "</select>";
    }
	public function saveNewinventory()
    {
        $this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		
		$post = $this->input->post();
		// print_r($post);
		// exit();
		$code = $this->Inventory_5_model->generate_id();
		$this->db->trans_begin();
		
		$config['upload_path'] = './assets/files/'; //path folder
	    $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip'; //type yang dapat diakses bisa anda sesuaikan
	    $config['encrypt_name'] = false; //Enkripsi nama yang terupload

	    $this->upload->initialize($config);
	   

	        if ($this->upload->do_upload('image')){
	            $gbr = $this->upload->data();
	            //Compress Image
	            $config['image_library']='gd2';
	            $config['source_image']='./assets/files/'.$gbr['file_name'];
	            $config['create_thumb']= FALSE;
				$config['overwrite']= TRUE;
	            $config['maintain_ratio']= FALSE;
	            $config['quality']= '50%';
	            $config['width']= 260;
	            $config['height']= 350;
	            $config['new_image']= './assets/files/'.$gbr['file_name'];
	            $this->load->library('image_lib', $config);
	            $this->image_lib->resize();

	            $gambar  =$gbr['file_name'];
				$type    =$gbr['file_type'];
				$ukuran  =$gbr['file_size'];
				$ext1    =explode('.', $gambar);
				$ext     =$ext1[1];
				$lokasi = './assets/files/'.$gbr['file_name'].'.'.$ext;
				
				
			}
			
			// print_r($this->upload->do_upload('image'));
			// exit();
			
		$data = [
			'id_material'		=> $code,
			'id_type'		    => $post['inventory_1'],
			'id_category1'		=> $post['inventory_2'],
			'id_category2'		=> $post['inventory_3'],
			'id_category3'		=> $post['inventory_4'],
			'nama'				=> $post['nama'],
			'aktif'				=> 'aktif',
			'spec1'				=> $post['spec1'],
			'spec2'				=> $post['spec2'],
			'spec3'				=> $post['spec3'],
			'spec4'				=> $post['spec4'],
			'spec5'				=> $post['spec5'],
			'spec6'				=> $post['spec6'],
			'spec7'				=> $post['spec7'],
			'spec8'				=> $post['spec8'],
			'spec9'				=> $post['spec9'],
			'spec10'			=> $post['spec10'],
			'spec11'		    => $post['spec11'],
			'spec12'			=> $post['spec12'],
            'spec13'			=> $post['spec13'],
            'spec14'			=> $post['spec14'],
            'spec15'			=> $post['spec15'],
			'spec16'	        => $gambar,
			'spec17'	        => $ukuran,
			'spec18'	        => $ext,
			'spec19'	        => $lokasi,
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];
		
		$insert = $this->db->insert("ms_material",$data);
		
		$numb1 =0;
		foreach($_POST['data1'] as $d1){
		$numb1++;	
		        $tglbayar   = $d1[perkiraan_bayar];
		      	$timebayar = strtotime($tglbayar);
				$tglbyr = date('Y-m-d',$timebayar);
              $data1 =  array(
			                    'id_material'=>$code,
								'nama_satuan'=>$d1[tipe_payment],
								'konversi'=>$d1[pembayaran],
								'satuan_konversi' =>$d1[satuan_kecil],
				                'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('ms_material_konversi',$data1);
			
		    }			
			
		$numb2 =0;
		foreach($_POST['data2'] as $d2){
		$numb2++;	
		        $tglbayar   = $d1[perkiraan_bayar];
		       
              $data2 =  array(
			                    'id_material'=>$code, 
								'nama_material_sejenis'=>$d2[material],
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('ms_material_sejenis',$data2);
			
		    }		


        $numb3 =0;
		foreach($_POST['data3'] as $d3){
		$numb3++;	
		       
		      
              $data3 =  array(
			                    'id_material'=>$code,
								'nama_alt_supplier'=>$d3[suplier],
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('ms_material_supplier',$data3);
			
		    }					
		
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
			  'status'	=> 1
			);			
		}
		
  		echo json_encode($status);

    }
	
}
