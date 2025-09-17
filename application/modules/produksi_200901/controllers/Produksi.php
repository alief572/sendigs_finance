<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

class Produksi extends Admin_Controller{

    protected $viewPermission = 'Barang.View';
    protected $addPermission = 'Barang.Add';
    protected $managePermission = 'Barang.Manage';
    protected $deletePermission = 'Barang.Delete';

    public function __construct(){
        parent::__construct();

        // $this->load->library(array('Mpdf'));
        $this->load->model(array(
			'Produksi/produksi_model','Cycletime/Cycletime_model'
			));

        date_default_timezone_set('Asia/Bangkok');
        $this->template->page_icon('fa fa-table');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data	= $this->db->query("SELECT
                                  a.id,
                                	a.id_produksi,
                                	a.id_produksi_h,
                                	a.tanggal_produksi,
                                	b.id_costcenter,
                                	c.nama_costcenter,
                                	a.id_product,
                                	e.nama AS nm_product,
                                	f.nama AS nm_project,
                                	a.id_process,
                                	d.nm_process,
                                	a.`code`,
                                	a.ket,
                                  a.remarks,
                                  b.created_by,
                                  b.created_date
                                FROM
                                	report_produksi_daily_detail a
                                	LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                                	LEFT JOIN ms_costcenter c ON b.id_costcenter = c.id_costcenter
                                	LEFT JOIN ms_process d ON a.id_process = d.id
                                	LEFT JOIN ms_inventory_category2 e ON a.id_product = e.id_category2
                                	LEFT JOIN ms_inventory_category1 f ON e.id_category1 = f.id_category1
                                ORDER BY
                                  a.id DESC,
                                	a.tanggal_produksi DESC,
                                	b.id_costcenter ASC,
                                	a.id_product ASC,
                                	a.id_process ASC
                                  ")->result();
      history("View index report produksi (input produksi)");
      $this->template->set('results', $data);
      $this->template->title('Report Produksi');
      $this->template->render('index');
    }

    public function report(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data	= $this->db->query("SELECT
                                	a.id_produksi,
                                	a.id_produksi_h,
                                	a.tanggal_produksi,
                                	b.id_costcenter,
                                	a.id_product,
                                  d.id_category1
                                FROM
                                	report_produksi_daily_detail a
                                	LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                                  LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2
                                WHERE
                                  a.ket <> 'not yet'
                                GROUP BY
                                  a.tanggal_produksi,
                                	b.id_costcenter,
                                	a.id_product
                                ORDER BY
                                	a.tanggal_produksi DESC,
                                  b.id_costcenter ASC,
                                  d.id_category1 ASC,
                                  a.id_product ASC
                                  ")->result();
      history("View index report produksi (report wip)");
      $this->template->set('results', $data);
      $this->template->title('Report Produksi');
      $this->template->render('report');
    }

    public function add(){

    	$session = $this->session->userdata('app_session');

			$customer    = $this->Cycletime_model->get_data('master_customer');
			$supplier    = $this->Cycletime_model->get_data('master_supplier');
			$material    = $this->Cycletime_model->get_data('ms_inventory_category2');
			$mesin      = $this->Cycletime_model->get_data_group('asset','category','4','nm_asset');
      $mould      = $this->Cycletime_model->get_data_group('asset','category','5','nm_asset');
			$costcenter  = $this->Cycletime_model->get_data('ms_costcenter','deleted','0');
			$data = [
			'customer' => $customer,
			'supplier' => $supplier,
			'material' => $material,
			'mesin' => $mesin,
      'mould' => $mould,
			'costcenter' => $costcenter,
			];
			$this->template->set('results', $data);
      $this->template->title('Add Produksi');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add');
    }

    public function edit(){

    	$session = $this->session->userdata('app_session');
      $id_time = $this->uri->segment(3);
      $header	= $this->db->query("SELECT * FROM report_produksi_daily_header WHERE id_produksi_h='".$id_time."' LIMIT 1 ")->result_array();
      $detail	= $this->db->query("SELECT * FROM report_produksi_daily_detail WHERE id_produksi_h='".$id_time."' ")->result_array();
      $costcenter	= $this->db->query("SELECT a.costcenter AS id, b.nama_costcenter AS nama FROM cycletime_detail_header a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter GROUP BY a.costcenter ORDER BY b.nama_costcenter ASC")->result_array();
      $daycode	= $this->db->query("SELECT * FROM daycode ORDER BY code ASC ")->result_array();

			$data = [
			'header' => $header,
			'detail' => $detail,
      'costcenter' => $costcenter,
      'daycode' => $daycode
			];
			$this->template->set('results', $data);
      $this->template->page_icon('fa fa-edit');
      $this->template->title('Edit Produksi');
      $this->template->render('edit', $data);
    }

    public function change_daycode(){
      if($this->input->post()){
        $data			= $this->input->post();
    		$session 		= $this->session->userdata('app_session');
        $id 	          = $data['id'];
        $daycode_before = $data['daycode_before'];
        $daycode_new 	  = $data['daycode_new'];

        $ArrUbah = array(
          'code' => $daycode_new
        );

        $this->db->trans_start();
          $this->db->where('id',$id);
    			$this->db->update('report_produksi_daily_detail', $ArrUbah);
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
          history("Change daycode produksi ".$id.", ".$daycode_before." to ".$daycode_new);
    		}

    		echo json_encode($Arr_Data);
      }
      else{
    		$id 	= $this->uri->segment(3);
        $header	= $this->db->query("SELECT a.*, d.id_category1 FROM report_produksi_daily_detail a LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2 WHERE a.id_produksi_h='".$id."'")->result_array();
        // print_r($header);exit;
    		$data = [
    			'header' => $header,
          'id' => $id
    			];
        $this->template->set('results', $data);
    		$this->template->render('change_daycode', $data);
      }
  	}

  	public function view(){
  		$this->auth->restrict($this->viewPermission);
  		$id 	= $this->input->post('id');
      $header	= $this->db->query("SELECT a.*, d.id_category1 FROM report_produksi_daily_detail a LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2 WHERE a.id_produksi_h='".$id."'")->result_array();
      // print_r($header);exit;
  		$data = [
  			'header' => $header
  			];
      $this->template->set('results', $data);
  		$this->template->render('view', $data);
  	}

  public function get_add(){
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$costcenter	= $this->db->query("SELECT a.costcenter AS id, b.nama_costcenter AS nama FROM cycletime_detail_header a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter GROUP BY a.costcenter ORDER BY b.nama_costcenter ASC")->result_array();
    $machine	= $this->db->query("SELECT * FROM asset WHERE category='4' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
    $mould	= $this->db->query("SELECT * FROM asset WHERE category='5' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
		// echo $qListResin; exit;
		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr class='header_".$id."'>";
				$d_Header .= "<td align='center'>".$id."</td>";
				$d_Header .= "<td align='left'>";
        $d_Header .= "<select name='Detail[".$id."][id_costcenter]' id='cost_".$id."'data-id='".$id."' class='chosen_select form-control input-sm inline-blockd costcenter'>";
        $d_Header .= "<option value='0'>Select Costcenter</option>";
        foreach($costcenter AS $val => $valx){
          $d_Header .= "<option value='".$valx['id']."'>".strtoupper($valx['nama'])."</option>";
        }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        $d_Header .= "<td align='left'>";

				$d_Header .= "</td>";
				// $d_Header .= "<td align='left'></td>";
        // $d_Header .= "<td align='left'></td>";
        $d_Header .= "<td align='left'></td>";
				$d_Header .= "<td align='center'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

  		//add nya
  		$d_Header .= "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id='".$id."' class='btn btn-sm btn-primary addSubPart' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
  			// $d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  			// $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>";
  			$d_Header .= "<td align='center'></td>";
  			// $d_Header .= "<td align='center'></td>";
  			// $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
        	$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
	}

  public function get_add_sub(){
		$id 	= $this->uri->segment(3);
    $no 	= $this->uri->segment(4);
    $cost 	= $this->uri->segment(5);

    $product	= $this->db->query("SELECT
                                  	a.costcenter AS id,
                                  	b.id_product AS id_product,
                                  	d.nama AS nama,
                                  	c.nama AS nama2
                                  FROM
                                  	cycletime_detail_header a
                                  	LEFT JOIN cycletime_header b ON a.id_time = b.id_time
                                  	LEFT JOIN ms_inventory_category2 d ON b.id_product = d.id_category2
                                  	LEFT JOIN ms_inventory_category1 c ON d.id_category1 = c.id_category1
                                  WHERE
                                    a.costcenter = '".$cost."'
                                  GROUP BY
                                  	a.costcenter,
                                  	b.id_product
                                  ORDER BY
                                  	d.id_category2")->result_array();
    $daycode	= $this->db->query("SELECT * FROM daycode ORDER BY code ASC ")->result_array();
		// echo $qListResin; exit;
		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr class='header_".$id."'>";
				$d_Header .= "<td align='center'></td>";
				$d_Header .= "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
        $d_Header .= "<select name='Detail[".$id."][detail][".$no."][id_product]' id='product_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd id_product cost_".$id."'>";
        $d_Header .= "<option value='0'>Select Product Name</option>";
        foreach($product AS $val => $valx){
          $d_Header .= "<option value='".$valx['id_product']."'>".strtoupper($valx['nama'])." [".strtoupper($valx['nama2'])."]</option>";
        }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        // $d_Header .= "<td align='left'>";
        // $d_Header .= "<select name='Detail[".$id."][detail][".$no."][id_process]' id='process_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd id_process pro_".$id."'>";
        // $d_Header .= "<option value='0'>List Empty</option>";
        // $d_Header .= "</select>";
				// $d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<select name='Detail[".$id."][detail][".$no."][code]' id='code_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd code code_".$id."'>";
        $d_Header .= "<option value='0'>List Empty</option>";
        // $d_Header .= "<option value='0'>Select Daycode</option>";
        // foreach($daycode AS $val => $valx){
        //   $d_Header .= "<option value='".$valx['code']."'>".strtoupper($valx['code'])."</option>";
        // }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        // $d_Header .= "<td align='left'>";
        // $d_Header .= "<select name='Detail[".$id."][detail][".$no."][ket]' class='chosen_select form-control input-sm inline-blockd ket'>";
        // $d_Header .= "<option value='0'>Select Status</option>";
        // $d_Header .= "<option value='good'>GOOD</option>";
        // $d_Header .= "<option value='bad'>NOT GOOD</option>";
        // $d_Header .= 	"</select>";
				// $d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= 	"<input type='text' name='Detail[".$id."][detail][".$no."][remarks]' class='form-control input-md' placeholder='Remarks'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='center'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

		//add nya
		$d_Header .= "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id='".$id."' class='btn btn-sm btn-primary addSubPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
			$d_Header .= "<td align='center'></td>";
			// $d_Header .= "<td align='center'></td>";
			// $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
      	$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}


  public function save_process(){

  	$Arr_Kembali	= array();
		$data			= $this->input->post();
    // print_r($data);
    // exit;
		$session 		= $this->session->userdata('app_session');
    $Detail 	= $data['Detail'];
    $tanggal_pro 	= date('Y-m-d', strtotime($data['tanggal_produksi']));
    $Ym						= date('ym');
    //pengurutan kode
    $srcMtr			= "SELECT MAX(id_produksi) as maxP FROM report_produksi_daily_code WHERE id_produksi LIKE 'PR-".$Ym."%' ";
    $numrowMtr		= $this->db->query($srcMtr)->num_rows();
    $resultMtr		= $this->db->query($srcMtr)->result_array();
    $angkaUrut2		= $resultMtr[0]['maxP'];
    $urutan2		= (int)substr($angkaUrut2, 7, 3);
    $urutan2++;
    $urut2			= sprintf('%03s',$urutan2);
    $id_material	= "PR-".$Ym.$urut2;

    $ArrHeader		= array(
      'id_produksi'			=> $id_material,
      'tanggal_produksi' 	=> $tanggal_pro,
      'created_by'	=> $session['id_user'],
      'created_date'	=> date('Y-m-d H:i:s')
    );



    $ArrDetail	= array();
    $ArrDetail2	= array();
    foreach($Detail AS $val => $valx){
      $urut				= sprintf('%02s',$val);
      $ArrDetail[$val]['id_produksi'] 			= $id_material;
      $ArrDetail[$val]['id_produksi_h']     = $id_material."-".$urut;
      $ArrDetail[$val]['tanggal_produksi'] 	= $tanggal_pro;
      $ArrDetail[$val]['id_costcenter'] 		= $valx['id_costcenter'];
      $ArrDetail[$val]['created_by'] 			  = $session['id_user'];
      $ArrDetail[$val]['created_date'] 			= date('Y-m-d H:i:s');
      foreach($valx['detail'] AS $val2 => $valx2){
        $ArrDetail2[$val2.$val]['id_produksi'] 			= $id_material;
        $ArrDetail2[$val2.$val]['id_produksi_h']    = $id_material."-".$urut;
        $ArrDetail2[$val2.$val]['tanggal_produksi'] 	= $tanggal_pro;
        $ArrDetail2[$val2.$val]['id_product'] 	    = $valx2['id_product'];
        // $ArrDetail2[$val2.$val]['id_process'] 		  = $valx2['id_process'];
        $ArrDetail2[$val2.$val]['code'] 			      = $valx2['code'];
        // $ArrDetail2[$val2.$val]['ket'] 			      = $valx2['ket'];
        $ArrDetail2[$val2.$val]['remarks'] 			      = $valx2['remarks'];
      }
    }

    // print_r($ArrHeader);
		// print_r($ArrDetail);
		// print_r($ArrDetail2);
		// exit;

		$this->db->trans_start();
			$this->db->insert('report_produksi_daily_code', $ArrHeader);
      if(!empty($ArrDetail)){
  			$this->db->insert_batch('report_produksi_daily_header', $ArrDetail);
      }
      if(!empty($ArrDetail)){
        $this->db->insert_batch('report_produksi_daily_detail', $ArrDetail2);
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
      history("Insert report produksi ".$id_material);
		}

		echo json_encode($Arr_Data);
	}

	public function list_center(){
		$id = $this->uri->segment(3);
		$query	 	= "SELECT * FROM ms_costcenter WHERE id_dept='".$id."' ORDER BY nama_costcenter ASC";
		$Q_result	= $this->db->query($query)->result();
		$option 	= "<option value='0'>Select an Option</option>";
		foreach($Q_result as $row)	{
		$option .= "<option value='".$row->nama_costcenter."'>".strtoupper($row->nama_costcenter)."</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

  public function edit_process(){

  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
    // print_r($data);
    // exit;
		$session 		   = $this->session->userdata('app_session');
    $Detail 	     = $data['Detail'];


    $ArrDetail	= array();
    foreach($Detail AS $val => $valx){
      $ArrDetail[$val]['id'] 		= $valx['id'];
      $ArrDetail[$val]['code'] 	= $valx['code'];
      $ArrDetail[$val]['ket'] 	= $valx['ket'];
    }

    // print_r($ArrHeader);
		// print_r($ArrDetail);
		// print_r($ArrDetail2);
		// exit;

		$this->db->trans_start();
			$this->db->update_batch('report_produksi_daily_detail', $ArrDetail,'id');
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
      history("Update report produksi ".$valx['id']);
		}

		echo json_encode($Arr_Data);
	}

  public function delete_cycletime(){

  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
    // print_r($data);
    // exit;
		$session 		   = $this->session->userdata('app_session');
    $id_material	 = $data['id'];

    $ArrHeader		  = array(
      'deleted'			=> "Y",
      'deleted_by'	=> $session['id_user'],
      'deleted_date'	=> date('Y-m-d H:i:s')
    );

		$this->db->trans_start();
      $this->db->where('id_time', $id_material);
			$this->db->update('cycletime_header', $ArrHeader);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Delete gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
      history("Delete report produksi ".$id_material);
		}

		echo json_encode($Arr_Data);
	}

  public function get_list_product(){
		$cost = $this->uri->segment(3);
    $product	= $this->db->query("SELECT
                                  	a.costcenter AS id,
                                  	b.id_product AS id_product,
                                  	d.nama AS nama,
                                  	c.nama AS nama2
                                  FROM
                                  	cycletime_detail_header a
                                  	LEFT JOIN cycletime_header b ON a.id_time = b.id_time
                                  	LEFT JOIN ms_inventory_category2 d ON b.id_product = d.id_category2
                                  	LEFT JOIN ms_inventory_category1 c ON d.id_category1 = c.id_category1
                                  WHERE
                                    a.costcenter = '".$cost."'
                                  GROUP BY
                                  	a.costcenter,
                                  	b.id_product
                                  ORDER BY
                                  	d.nama")->result();
		$option 	= "<option value='0'>Select Product Name</option>";
		foreach($product as $row)	{
		$option .= "<option value='".$row->id_product."'>".strtoupper($row->nama)." (".strtoupper($row->nama2).")</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

  public function get_list_process(){
		$id_product = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);

    $daycode = "<option value='0'>Select Daycode</option>";
    if($costcenter == 'CC2000012'){
      $filter = $this->db->query("SELECT * FROM filter_daycode WHERE id_costcenter='CC2000012' AND id_product='".$id_product."'")->result_array();
      $dtListArray = array();
      foreach($filter AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= "('".implode("','", $dtListArray)."')";

      $filter_daycode = $this->db->query("SELECT * FROM daycode WHERE code NOT IN ".$dtImplode." ORDER BY code ASC ")->result();
      foreach($filter_daycode as $row)	{
  		    $daycode .= "<option value='".$row->code."'>".strtoupper($row->code)."</option>";
  		}

    }

    if($costcenter != 'CC2000012'){
      $filter = $this->db->query("SELECT * FROM filter_daycode WHERE id_costcenter='".$costcenter."' AND id_product='".$id_product."'")->result_array();
      $dtListArray = array();
      foreach($filter AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= "('".implode("','", $dtListArray)."')";

      $filter_daycode = $this->db->query("SELECT * FROM daycode WHERE code NOT IN ".$dtImplode." ORDER BY code ASC ")->result();

      // $filter_daycode = $this->db->query("SELECT * FROM filter_daycode WHERE code NOT IN ".$dtImplode." AND id_costcenter='CC2000012' AND id_product='".$id_product."' ORDER BY code ASC ")->result();
      foreach($filter_daycode as $row)	{
  		    $daycode .= "<option value='".$row->code."'>".strtoupper($row->code)."</option>";
  		}
    }



    $product	= $this->db->query("SELECT
                                  	a.id_product,
                                  	d.costcenter,
                                  	b.id_process AS id_process,
                                  	c.nm_process AS nama
                                  FROM
                                  	cycletime_header a
                                  	LEFT JOIN cycletime_detail_detail b ON a.id_time = b.id_time
                                  	LEFT JOIN cycletime_detail_header d ON b.id_costcenter = d.id_costcenter
                                  	LEFT JOIN ms_process c ON c.id = b.id_process
                                  WHERE
                                    d.costcenter = '".$costcenter."'
                                    AND a.id_product = '".$id_product."'
                                  GROUP BY
                                  	b.id_process
                                  ORDER BY
                                  	a.id_product,
                                  	c.nm_process")->result();
		$option 	= "<option value='0'>Select Product Name</option>";
		foreach($product as $row)	{
		$option .= "<option value='".$row->id_process."'>".strtoupper($row->nama)."</option>";
		}
		echo json_encode(array(
			'option' => $option,
      'daycode' => $daycode
		));
	}

  public function download_excel(){
      //$brg_data = $this->Barang_model->tampil_produk()->result_array();
      $data	= $this->db->query("SELECT
                                	a.id_produksi,
                                	a.id_produksi_h,
                                	a.tanggal_produksi,
                                	b.id_costcenter,
                                	a.id_product,
                                	COUNT( a.id_product ) AS qty,
                                  d.id_category1
                                FROM
                                	report_produksi_daily_detail a
                                	LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                                  LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2
                                WHERE
                                  a.ket <> 'not yet'
                                GROUP BY
                                  a.tanggal_produksi,
                                	b.id_costcenter,
                                	a.id_product
                                ORDER BY
                                	a.tanggal_produksi DESC,
                                  b.id_costcenter ASC,
                                  d.id_category1 ASC,
                                  a.id_product ASC
                                  ")->result();

      $data = array(
  			'title2'		  => 'Report',
  			'results'	  => $data
  		);

      $this->load->view('excel_produksi',$data);


  }

  //Production Planning
  public function production_planning(){
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    history("View index BOM");
    $this->template->title('Production Planning');
    $this->template->render('production_planning');
  }

  public function data_side_plan(){
    $this->produksi_model->get_json_plan();
  }

  public function detail_production_planning(){
    // $this->auth->restrict($this->viewPermission);
    $no_plan 	= $this->input->post('no_plan');

    $data_num = $this->db->query("SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY `date` ORDER BY `date`")->num_rows();
    $data = $this->db->query("SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY `date` ORDER BY `date`")->result_array();

    $product = $this->db->query("SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY product ORDER BY product")->result_array();

    $header = $this->db->query("SELECT * FROM produksi_planning WHERE no_plan='".$no_plan."'")->result();

    // print_r($header);
    $data = [
      'data_num' => $data_num,
      'data' => $data,
      'product' => $product,
      'header'=> $header
    ];
    $this->template->set('results', $data);
    $this->template->render('detail_production_planning', $data);
  }

  public function add_production_planning(){
    if($this->input->post()){
      $Arr_Kembali	= array();
      $data			= $this->input->post();

      $session 		  = $this->session->userdata('app_session');
      $detail 	    = $data['detail'];
      $footer 	    = $data['footer'];
      $Ym					  = date('y');
      $no_plan      = $data['no_plan'];
      $no_planx     = $data['no_plan'];
      $costcenter   = $data['costcenter'];
      $date_akhir   = date('Y-m-d', strtotime($data['date_produksi_plan']));
      $date_awal    = $data['date_awal'];
      // print_r($detail);
      // exit;
      $created_by   = 'updated_by';
      $created_date = 'updated_date';
      $tanda        = 'Update ';
      if(empty($no_planx)){

        $srcMtr			  = "SELECT MAX(no_plan) as maxP FROM produksi_planning WHERE no_plan LIKE 'PR".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 4, 4);
        $urutan2++;
        $urut2			  = sprintf('%04s',$urutan2);
        $no_plan	      = "PR".$Ym.$urut2;

        $created_by   = 'created_by';
        $created_date = 'created_date';
        $tanda        = 'Insert ';
      }

      $ArrHeader		= array(
        'no_plan'		    => $no_plan,
        'costcenter'	  => $costcenter,
        'date_awal'	    => $date_awal,
        'date_akhir'	  => $date_akhir,
        $created_by	    => $session['id_user'],
        $created_date	  => date('Y-m-d H:i:s')
      );



      $ArrDetail	= array();
      $ArrDetail2	= array();
      foreach($detail AS $val => $valx){
        $urut				= sprintf('%03s',$val);
        foreach($valx['data'] AS $val2 => $valx2){
          // echo $val."-".$val2."=".$valx2['product']."<br>";
          $ArrDetail[$val.$val2]['no_plan'] 	      = $no_plan;
          $ArrDetail[$val.$val2]['no_plan_detail']  = $no_plan."-".$urut;
          $ArrDetail[$val.$val2]['date']          = $valx['date'];
          $ArrDetail[$val.$val2]['product']       = $valx2['product'];
          $ArrDetail[$val.$val2]['qty_order']     = $valx2['qty_order'];
          $ArrDetail[$val.$val2]['stock']         = $valx2['stock'];
          $ArrDetail[$val.$val2]['shortages']     = $valx2['shortages'];
          $ArrDetail[$val.$val2]['queue']         = $valx2['queue'];
          $ArrDetail[$val.$val2]['qty']           = $valx2['qty'];
          $ArrDetail[$val.$val2]['man_power']     = $valx2['man_power'];
          $ArrDetail[$val.$val2]['cycletime']     = $valx2['cycletime'];
          $ArrDetail[$val.$val2]['mp_ct']         = $valx2['mp_ct'];
        }
      }

      // print_r($ArrHeader);
      // print_r($ArrDetail);
      // print_r($data);
      // exit;

      foreach($footer AS $val => $valx){
        $urut				= sprintf('%03s',$val);
        foreach($valx['man_minutes'] AS $val2 => $valx2){
          $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          $ArrDetail2[$val.$val2]['date']           = $valx['date'];
          $ArrDetail2[$val.$val2]['category']       = $valx2['category'];
          $ArrDetail2[$val.$val2]['value']          = str_replace(',','',$valx2['value']);
        }
        foreach($valx['availability'] AS $val3 => $valx3){ $val2++;
          $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          $ArrDetail2[$val.$val2]['date']           = $valx['date'];
          $ArrDetail2[$val.$val2]['category']       = $valx3['category'];
          $ArrDetail2[$val.$val2]['value']          = str_replace(',','',$valx3['value']);
        }
      }

      // print_r($ArrHeader);
      // print_r($ArrDetail);
      // print_r($ArrDetail2);
      // exit;

      $this->db->trans_start();
        if(empty($no_planx)){
          $this->db->delete('produksi_planning', array('no_plan' => $no_plan));
          $this->db->insert('produksi_planning', $ArrHeader);
        }
        if(!empty($no_planx)){
          $this->db->where('no_plan', $no_plan);
          $this->db->update('produksi_planning', $ArrHeader);
        }

        if(!empty($ArrDetail)){
          $this->db->delete('produksi_planning_data', array('no_plan' => $no_plan));
          $this->db->insert_batch('produksi_planning_data', $ArrDetail);
        }
        if(!empty($ArrDetail2)){
          $this->db->delete('produksi_planning_footer', array('no_plan' => $no_plan));
          $this->db->insert_batch('produksi_planning_footer', $ArrDetail2);
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
        history($tanda." Production Planning ".$no_plan);
      }

      echo json_encode($Arr_Data);
    }
    else{
      $session  = $this->session->userdata('app_session');
      $no_plan 	  = $this->uri->segment(3);
      $header   = $this->db->get_where('produksi_planning',array('no_plan' => $no_plan))->result();
      $plan    = $this->produksi_model->get_data('ms_costcenter');
      // print_r($plan);
      // exit;
      $data = [
        'header' => $header,
        'plan' => $plan
      ];
      $this->template->set('results', $data);
      $this->template->title('Add Production Planning');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add_production_planning',$data);
    }
  }

  public function get_planning(){
    $date_akhir = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);
    $date_awal 	= $this->uri->segment(5);
    $no_plan 	  = $this->uri->segment(6);

    // $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));
    $date_now   = date('Y-m-d', strtotime('2020-08-27'));

    $q_max      = "SELECT MAx(date_akhir) AS date_akhir FROM produksi_planning WHERE costcenter='".$costcenter."' LIMIT 1 ";
    $max_date   = $this->db->query($q_max)->result();

    $datex      = (!empty($max_date[0]->date_akhir))?$max_date[0]->date_akhir:$date_now;
    $date       = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
    if(!empty($date_awal)){
      $date       = date('Y-m-d', strtotime($date_awal));
    }

    $akhir      = new DateTime($date_akhir);
    $awal       = new DateTime($date);
    // echo $date; exit;
    $perbedaan  = $akhir->diff($awal);
    $colspan    = $perbedaan->d + 1;
    // echo $perbedaan->d;
    $product    = $this->db->query("SELECT product, SUM(qty_order) AS qty_order, delivery_date, qty_propose FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY product ORDER BY product ")->result_array();

    $product_date    = $this->db->query("SELECT delivery_date FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY delivery_date ORDER BY delivery_date ")->result_array();
    $product_date_num    = $this->db->query("SELECT delivery_date FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY delivery_date ORDER BY delivery_date ")->num_rows();

    $d_Header = "<div class='box box-primary'>";
        $d_Header .= "<div class='box-body'>";
        $d_Header .= "<div class='tableFixHead' style='height:500px;'>";
        $d_Header .= "<table class='table table-bordered table-striped'>";
        $d_Header .= "<thead class='thead'>";
        $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle; width:300px !important;' rowspan='3'>Product</th>";
          foreach ($product_date as $key2x => $value2x) {
              $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Delivery<br>".date('d M Y', strtotime($value2x['delivery_date']))."</th>";
          }
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Total Propose</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Stock</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Shortages to Fulfill Orders</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Queue</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' colspan='".$colspan."'>Production Planning Date</th>";
        $d_Header .= "</tr>";
        $siz = 65/$colspan;
        $cols_empty = $colspan + 5;
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle;'>".$loop_date."</th>";
          }
        $d_Header .= "</tr>";
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("d-m-y", strtotime("+".$a." day", strtotime($date)));
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle; width:300px !important;'>".$loop_date."
                            <input type='hidden' name='detail[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            <input type='hidden' name='footer[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            </th>";
          }
        $d_Header .= "</tr>";

        $d_Header .= "</thead>";
        $d_Header .= "<tbody>";
          foreach ($product as $key => $value) { $key++;
              $key = $key - 1;
              $q_data_stock = "SELECT b.* FROM warehouse_product b WHERE b.costcenter='".$costcenter."' AND b.id_product='".$value['product']."' AND b.category='order' LIMIT 1 ";
              $r_data_stock = $this->db->query($q_data_stock)->result();
              $stock = (!empty($r_data_stock[0]->qty_stock))?$r_data_stock[0]->qty_stock:0;

              $q_data = "SELECT b.* FROM cycletime_fast b WHERE b.costcenter='".$costcenter."' AND b.id_product='".$value['product']."' LIMIT 1 ";
              $r_data = $this->db->query($q_data)->result();

              $mp = (!empty($r_data))?$r_data[0]->mp:0;
              $ct = (!empty($r_data))?$r_data[0]->cycletime:0;

              $d_Header .= "<tr class='header_".$key."'>";
              $d_Header .= "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
              foreach ($product_date as $key2x => $value2x) {
                  $queryx = "SELECT qty_order FROM sales_order_detail WHERE delivery_date = '".$value2x['delivery_date']."' AND product = '".$value['product']."' LIMIT 1 ";
                  $qty_order = $this->db->query($queryx)->result();
                  $qty_ = (!empty($qty_order[0]->qty_order))?$qty_order[0]->qty_order:0;
                  $d_Header .= "<td class='text-center'>".$qty_."</td>";
              }
              $d_Header .= "<td class='text-center'>".$value['qty_propose']."</td>";
              $sisa = $value['qty_order'] - $stock;
              $d_Header .= "<td class='text-center'>".$stock."</td>";


              $d_Header .= "<td class='text-center'>".$sisa."</td>";
              $d_Header .= "<td class='text-center'>".get_antrian_wip($value['product'], $costcenter)."</td>";
              for ($a=0; $a<$colspan; $a++) {
                $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
                $query  = "SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' AND `date`='".$loop_date2."' AND product='".$value['product']."' LIMIT 1";
                // echo $query;
                $rest_d = $this->db->query($query)->result();
                $qty    = (!empty($rest_d[0]->qty))?$rest_d[0]->qty:'';
                $mpCT    = (!empty($rest_d[0]->mp_ct))?$rest_d[0]->mp_ct:0;
                $d_Header .= "<td class='text-center'>";
                $d_Header .= "<input type='text' id='qtyp_".$key."_".$a."' name='detail[".$a."][data][".$key."][qty]' class='form-control text-center input-md maskM get_tot_ct' value='".$qty."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='mp_".$key."_".$a."' name='detail[".$a."][data][".$key."][man_power]' class='form-control text-left input-md maskM' value='".$mp."' placeholder='CT' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][cycletime]' class='form-control text-left input-md maskM' value='".$ct."' placeholder='MP' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='tot_ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][mp_ct]' class='form-control text-left input-md maskM tot_ct_".$a."' value='".$mpCT."' placeholder='CT*MP*QTY' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                              <input type='hidden' name='detail[".$a."][data][".$key."][product]' class='form-control text-center input-md maskM' readonly value='".$value['product']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][qty_order]' class='form-control text-center input-md maskM' readonly value='".$value['qty_order']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][stock]' class='form-control text-center input-md maskM' readonly value='".$stock."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][shortages]' class='form-control text-center input-md maskM' readonly value='".$sisa."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][queue]' class='form-control text-center input-md maskM' readonly value='".get_antrian_wip($value['product'], $costcenter)."'>
                              </td>";
              }
              $d_Header .= "</tr>";
          }
          $col = $product_date_num + 4;
          // $d_Header .= "<tr id='add_".$key."'>";
          //   $d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-colspan2='".$product_date_num."' data-colspan='".$colspan."' data-tanggal='".$date_akhir."' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
          //   $d_Header .= "<td colspan='".$col."'></td>";
          //   $d_Header .= "<td colspan='".$colspan."'></td>";
          // $d_Header .= "</tr>";

          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>TOTAL MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $query  = "SELECT * FROM produksi_planning_footer WHERE no_plan='".$no_plan."' AND `date`='".$loop_date2."' AND category='man minutes' LIMIT 1";
              // echo $query;
              $rest_d = $this->db->query($query)->result();
              $value    = (!empty($rest_d[0]->value))?number_format($rest_d[0]->value):'';

              $d_Header .= "<td class='text-center'>";
              $d_Header .= "<input type='text' id='tot_ct_".$a."' name='footer[".$a."][man_minutes][".$a."][value]' value='".$value."' class='form-control text-right input-md maskM' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                            <input type='hidden' name='footer[".$a."][man_minutes][".$a."][category]' value='man minutes' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>AVAILABILITY MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));
              $get_mp   = $this->db->query("SELECT b.* FROM ms_costcenter b WHERE b.id_costcenter='".$costcenter."' LIMIT 1 ")->result();;
              $mpx1 = $get_mp[0]->mp_1;
              $mpx2 = $get_mp[0]->mp_2;
              $mpx3 = $get_mp[0]->mp_3;
              $shx1 = $get_mp[0]->shift1;
              $shx2 = $get_mp[0]->shift2;
              $shx3 = $get_mp[0]->shift3;

              $get_time1 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='1' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $get_time2 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='2' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $get_time3 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='3' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $day = $get_time1[0]->id_day;
              // $tm1 = (!empty($get_time1))?($get_time1[0]->start_break1 - $get_time1[0]->start_work) + ($get_time1[0]->done_work - $get_time1[0]->done_break1):0;
              if($shx1 == 'N'){
                $tm1 = 0;
              }else{
                $tm1 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $sb1  = date_create(get_24($get_time1[0]->start_break1));
                    $tm1_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $db1  = date_create(get_24($get_time1[0]->done_break1));
                    $tm1_2= date_diff($dw, $db1);

                    $tm1 = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $tm1_1= date_diff($sw, $dw);

                    $tm1 = (($tm1_1->h)+(($tm1_1->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }
                }
              }

              if($shx2 == 'N'){
                $tm2 = 0;
              }else{
                $tm2 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $sb1  = date_create(get_24($get_time2[0]->start_break1));
                    $tm2_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $db1  = date_create(get_24($get_time2[0]->done_break1));
                    $tm2_2= date_diff($dw, $db1);

                    $tm2 = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $tm2_2= date_diff($sw, $dw);

                    $tm2 = (($tm2_2->h)+(($tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }
                }
              }

              if($shx3 == 'N'){
                $tm3 = 0;
                $tm3x = "<br>";
              }else{
                $tm3 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $sb1  = date_create($get_time3[0]->start_break1);
                    $tm3_1= date_diff($sw, $sb1);

                    $dw   = date_create($get_time3[0]->done_work);
                    $db1  = date_create($get_time3[0]->done_break1);
                    $tm3_2= date_diff($dw, $db1);

                    $tm3 = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $dw   = date_create($get_time3[0]->done_work);
                    $tm3_2= date_diff($sw, $dw);

                    $tm3 = (($tm3_2->h)+(($tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }
                }
              }

              $m_shift = floatval($tm1) + floatval($tm2) + floatval($tm3);
              $pengali = $m_shift * 60 * (93/100);
              $tanda = "";
              // $tanda = $tm1."_".$tm2."_".$tm3."<br>".$m_shift."<br>".$pengali;

              $tanda2 = "";
              // $tanda2 = $tm1x.$tm2x.$tm3x;
              $d_Header .= "<td class='text-center'>".$tanda2.$tanda;
              $d_Header .= "<input type='text' id='man_ct_".$a."' name='footer[".$a."][availability][".$a."][value]' value='".number_format($pengali)."' class='form-control text-right input-md maskM' readonly>
                            <input type='hidden' name='footer[".$a."][availability][".$a."][category]' value='availability' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          if(empty($product)){
            $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left' colspan='".$cols_empty."'>Not Found Data ...</td>";
            $d_Header .= "</tr>";
          }
        $d_Header .= "</tbody>";
        $d_Header .= "</table>";
        $d_Header .= "</div>";
        $d_Header .= "</div>";
    $d_Header .= "</div>";


     echo json_encode(array(
        'header'			=> $d_Header,
        'total' => $colspan
     ));
  }

  public function get_planning_edit(){
    $date_akhir = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);
    $date_awal 	= $this->uri->segment(5);
    $no_plan 	  = $this->uri->segment(6);

    $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));
    // $date_now   = date('Y-m-d', strtotime('2020-08-12'));

    $q_max      = "SELECT MAx(date_akhir) AS date_akhir FROM produksi_planning WHERE costcenter='".$costcenter."' LIMIT 1 ";
    $max_date   = $this->db->query($q_max)->result();

    $datex      = (!empty($max_date[0]->date_akhir))?$max_date[0]->date_akhir:$date_now;
    $date       = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
    if(!empty($date_awal)){
      $date       = date('Y-m-d', strtotime($date_awal));
    }

    $akhir      = new DateTime($date_akhir);
    $awal       = new DateTime($date);
    // echo $date; exit;
    $perbedaan  = $akhir->diff($awal);
    $colspan    = $perbedaan->d + 1;
    // echo $perbedaan->d;
    $product    = $this->db->query("SELECT product, SUM(qty_order) AS qty_order FROM produksi_planning_data WHERE no_plan = '".$no_plan."' GROUP BY product ORDER BY product ")->result_array();

    $product_date    = $this->db->query("SELECT delivery_date FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY delivery_date ORDER BY delivery_date ")->result_array();
    $product_date_num    = $this->db->query("SELECT delivery_date FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY delivery_date ORDER BY delivery_date ")->num_rows();

    $d_Header = "<div class='box box-primary'>";
        $d_Header .= "<div class='box-body'>";
        $d_Header .= "<div class='tableFixHead' style='height:500px;'>";
        $d_Header .= "<table class='table table-bordered table-striped'>";
        $d_Header .= "<thead class='thead'>";
        $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle; width:300px !important;' rowspan='3'>Product</th>";
          foreach ($product_date as $key2x => $value2x) {
              $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Delivery<br>".date('d M Y', strtotime($value2x['delivery_date']))."</th>";
          }
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Total Propose</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Stock</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Shortages to Fulfill Orders</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Queue</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' colspan='".$colspan."'>Production Planning Date</th>";
        $d_Header .= "</tr>";
        $siz = 65/$colspan;
        $cols_empty = $colspan + 5;
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle;'>".$loop_date."</th>";
          }
        $d_Header .= "</tr>";
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("d-m-y", strtotime("+".$a." day", strtotime($date)));
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle; width:300px !important;'>".$loop_date."
                            <input type='hidden' name='detail[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            <input type='hidden' name='footer[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            </th>";
          }
        $d_Header .= "</tr>";

        $d_Header .= "</thead>";
        $d_Header .= "<tbody>";
          foreach ($product as $key => $value) { $key++;
              $key = $key - 1;
              $q_data_stock = "SELECT b.* FROM warehouse_product b WHERE b.costcenter='".$costcenter."' AND b.id_product='".$value['product']."' AND b.category='order' LIMIT 1 ";
              $r_data_stock = $this->db->query($q_data_stock)->result();
              $stock = (!empty($r_data_stock[0]->qty_stock))?$r_data_stock[0]->qty_stock:0;

              $q_data = "SELECT b.* FROM cycletime_fast b WHERE b.costcenter='".$costcenter."' AND b.id_product='".$value['product']."' LIMIT 1 ";
              $r_data = $this->db->query($q_data)->result();

              $mp = (!empty($r_data))?$r_data[0]->mp:0;
              $ct = (!empty($r_data))?$r_data[0]->cycletime:0;

              $d_Header .= "<tr class='header_".$key."'>";
              $d_Header .= "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
              foreach ($product_date as $key2x => $value2x) {
                  $queryx = "SELECT qty_order FROM sales_order_detail WHERE delivery_date = '".$value2x['delivery_date']."' AND product = '".$value['product']."' LIMIT 1 ";
                  $qty_order = $this->db->query($queryx)->result();
                  $qty_ = (!empty($qty_order[0]->qty_order))?$qty_order[0]->qty_order:0;
                  $d_Header .= "<td class='text-center'>".$qty_."</td>";
              }
              $d_Header .= "<td class='text-center'>".$value['qty_order']."</td>";
              $sisa = $value['qty_order'] - $stock;
              $d_Header .= "<td class='text-center'>".$stock."</td>";


              $d_Header .= "<td class='text-center'>".$sisa."</td>";
              $d_Header .= "<td class='text-center'>".get_antrian_wip($value['product'], $costcenter)."</td>";
              for ($a=0; $a<$colspan; $a++) {
                $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
                $query  = "SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' AND `date`='".$loop_date2."' AND product='".$value['product']."' LIMIT 1";
                // echo $query;
                $rest_d = $this->db->query($query)->result();
                $qty    = (!empty($rest_d[0]->qty))?$rest_d[0]->qty:'';
                $mpCT    = (!empty($rest_d[0]->mp_ct))?$rest_d[0]->mp_ct:0;
                $d_Header .= "<td class='text-center'>";
                $d_Header .= "<input type='text' id='qtyp_".$key."_".$a."' name='detail[".$a."][data][".$key."][qty]' class='form-control text-center input-md maskM get_tot_ct' value='".$qty."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='mp_".$key."_".$a."' name='detail[".$a."][data][".$key."][man_power]' class='form-control text-left input-md maskM' value='".$mp."' placeholder='CT' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][cycletime]' class='form-control text-left input-md maskM' value='".$ct."' placeholder='MP' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='tot_ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][mp_ct]' class='form-control text-left input-md maskM tot_ct_".$a."' value='".$mpCT."' placeholder='CT*MP*QTY' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                              <input type='hidden' name='detail[".$a."][data][".$key."][product]' class='form-control text-center input-md maskM' readonly value='".$value['product']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][qty_order]' class='form-control text-center input-md maskM' readonly value='".$value['qty_order']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][stock]' class='form-control text-center input-md maskM' readonly value='".$stock."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][shortages]' class='form-control text-center input-md maskM' readonly value='".$sisa."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][queue]' class='form-control text-center input-md maskM' readonly value='".get_antrian_wip($value['product'], $costcenter)."'>
                              </td>";
              }
              $d_Header .= "</tr>";
          }
          $col = $product_date_num + 4;
          // $d_Header .= "<tr id='add_".$key."'>";
          //   $d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-colspan2='".$product_date_num."' data-colspan='".$colspan."' data-tanggal='".$date_akhir."' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
          //   $d_Header .= "<td colspan='".$col."'></td>";
          //   $d_Header .= "<td colspan='".$colspan."'></td>";
          // $d_Header .= "</tr>";

          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>TOTAL MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $query  = "SELECT * FROM produksi_planning_footer WHERE no_plan='".$no_plan."' AND `date`='".$loop_date2."' AND category='man minutes' LIMIT 1";
              // echo $query;
              $rest_d = $this->db->query($query)->result();
              $value    = (!empty($rest_d[0]->value))?number_format($rest_d[0]->value):'';

              $d_Header .= "<td class='text-center'>";
              $d_Header .= "<input type='text' id='tot_ct_".$a."' name='footer[".$a."][man_minutes][".$a."][value]' value='".$value."' class='form-control text-right input-md maskM' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                            <input type='hidden' name='footer[".$a."][man_minutes][".$a."][category]' value='man minutes' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>AVAILABILITY MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));
              $get_mp   = $this->db->query("SELECT b.* FROM ms_costcenter b WHERE b.id_costcenter='".$costcenter."' LIMIT 1 ")->result();;
              $mpx1 = $get_mp[0]->mp_1;
              $mpx2 = $get_mp[0]->mp_2;
              $mpx3 = $get_mp[0]->mp_3;
              $shx1 = $get_mp[0]->shift1;
              $shx2 = $get_mp[0]->shift2;
              $shx3 = $get_mp[0]->shift3;

              $get_time1 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='1' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $get_time2 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='2' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $get_time3 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='3' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $day = $get_time1[0]->id_day;
              // $tm1 = (!empty($get_time1))?($get_time1[0]->start_break1 - $get_time1[0]->start_work) + ($get_time1[0]->done_work - $get_time1[0]->done_break1):0;
              if($shx1 == 'N'){
                $tm1 = 0;
              }else{
                $tm1 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $sb1  = date_create(get_24($get_time1[0]->start_break1));
                    $tm1_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $db1  = date_create(get_24($get_time1[0]->done_break1));
                    $tm1_2= date_diff($dw, $db1);

                    $tm1 = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $tm1_1= date_diff($sw, $dw);

                    $tm1 = (($tm1_1->h)+(($tm1_1->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }
                }
              }

              if($shx2 == 'N'){
                $tm2 = 0;
              }else{
                $tm2 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $sb1  = date_create(get_24($get_time2[0]->start_break1));
                    $tm2_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $db1  = date_create(get_24($get_time2[0]->done_break1));
                    $tm2_2= date_diff($dw, $db1);

                    $tm2 = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $tm2_2= date_diff($sw, $dw);

                    $tm2 = (($tm2_2->h)+(($tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }
                }
              }

              if($shx3 == 'N'){
                $tm3 = 0;
                $tm3x = "<br>";
              }else{
                $tm3 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $sb1  = date_create($get_time3[0]->start_break1);
                    $tm3_1= date_diff($sw, $sb1);

                    $dw   = date_create($get_time3[0]->done_work);
                    $db1  = date_create($get_time3[0]->done_break1);
                    $tm3_2= date_diff($dw, $db1);

                    $tm3 = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $dw   = date_create($get_time3[0]->done_work);
                    $tm3_2= date_diff($sw, $dw);

                    $tm3 = (($tm3_2->h)+(($tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }
                }
              }

              $m_shift = floatval($tm1) + floatval($tm2) + floatval($tm3);
              $pengali = $m_shift * 60 * (93/100);
              $tanda = "";
              // $tanda = $tm1."_".$tm2."_".$tm3."<br>".$m_shift."<br>".$pengali;

              $tanda2 = "";
              // $tanda2 = $tm1x.$tm2x.$tm3x;
              $d_Header .= "<td class='text-center'>".$tanda2.$tanda;
              $d_Header .= "<input type='text' id='man_ct_".$a."' name='footer[".$a."][availability][".$a."][value]' value='".number_format($pengali)."' class='form-control text-right input-md maskM' readonly>
                            <input type='hidden' name='footer[".$a."][availability][".$a."][category]' value='availability' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          if(empty($product)){
            $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left' colspan='".$cols_empty."'>Not Found Data ...</td>";
            $d_Header .= "</tr>";
          }
        $d_Header .= "</tbody>";
        $d_Header .= "</table>";
        $d_Header .= "</div>";
        $d_Header .= "</div>";
    $d_Header .= "</div>";


     echo json_encode(array(
        'header'			=> $d_Header,
        'total' => $colspan
     ));
  }

  public function get_product(){
		$id 	     = $this->uri->segment(3);
    $colspan 	 = $this->uri->segment(4);
    $date 	   = $this->uri->segment(5);
    $colspan2 = $this->uri->segment(6);

		$d_Header = "";
    $d_Header .= "<tr class='header_".$id."'>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<select class='chosen_select form-control input-sm inline-blockd product' data-no='".$id."' data-tgl_akhir='".$date."'>";
      $d_Header .= "<option value='0'>Select Product</option>";
      foreach(get_product() AS $val => $valx){
        $d_Header .= "<option value='".$valx['id_category2']."'>".strtoupper($valx['nama'])."</option>";
      }
      $d_Header .= 		"</select>";
    $d_Header .= "</td>";
    $d_Header .= "<td class='text-center'  colspan='".$colspan2."'></td>";
    $d_Header .= "<td class='text-center'><div id='html_qty_order_".$id."'></div></td>";
    $d_Header .= "<td class='text-center'><div id='html_stock_".$id."'></div></td>";
    $d_Header .= "<td class='text-center'><div id='html_shortages_".$id."'></div></td>";
    $d_Header .= "<td class='text-center'><div id='html_queue_".$id."'></div></td>";
      for ($a=0; $a<$colspan; $a++) {
        $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));

        $d_Header .= "<td class='text-center'>";
        $d_Header .= "<input type='text' id='qtyp_".$id."_".$a."' name='detail[".$a."][data][".$id."][qty]' class='form-control text-center input-md maskM get_tot_ct' value='' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
        $d_Header .= "<input type='hidden' id='mp_".$id."_".$a."' name='detail[".$a."][data][".$id."][man_power]' class='mp_".$id." form-control text-left input-md maskM' value='man_power' placeholder='CT' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
        $d_Header .= "<input type='hidden' id='ct_".$id."_".$a."' name='detail[".$a."][data][".$id."][cycletime]' class='ct_".$id." form-control text-left input-md maskM' value='cycletime' placeholder='MP' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
        $d_Header .= "<input type='hidden' id='tot_ct_".$id."_".$a."' name='detail[".$a."][data][".$id."][mp_ct]' class='form-control text-left input-md maskM tot_ct_".$a."' value='' placeholder='CT*MP*QTY' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                      <input type='hidden' name='detail[".$a."][data][".$id."][product]' class='product_".$id." form-control text-center input-md maskM' readonly value='product'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][qty_order]' class='qty_order_".$id." form-control text-center input-md maskM' readonly value='qty_order'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][stock]' class='stock_".$id." form-control text-center input-md maskM' readonly value='stock'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][shortages]' class='shortages_".$id." form-control text-center input-md maskM' readonly value='sisa'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][queue]' class='queue_".$id." form-control text-center input-md maskM' readonly value='get_antrian_wip'>
                      </td>";
      }
    $d_Header .= "</tr>";



		//add part
		$d_Header .= "<tr id='add_".$id."'>";
      $d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-colspan2='".$colspan2."' data-colspan='".$colspan."' data-tanggal='".$date."' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
      $d_Header .= "<td colspan='4'></td>";
      $d_Header .= "<td colspan='".$colspan."'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}

  public function get_product_data(){
		$no 	        = $this->uri->segment(3);
    $product 	  	= $this->uri->segment(4);
    $costcenter   = $this->uri->segment(5);
    $date_awal    = $this->uri->segment(6);
    $date_akhir   = $this->uri->segment(7);

    $q_data_stock = "SELECT b.* FROM warehouse_product b WHERE b.costcenter='".$costcenter."' AND b.id_product='$product' AND b.category='order' LIMIT 1 ";
    $r_data_stock = $this->db->query($q_data_stock)->result();
    $stock = (!empty($r_data_stock[0]->qty_stock))?$r_data_stock[0]->qty_stock:0;


    $q_data = "SELECT b.* FROM cycletime_fast b WHERE b.costcenter='".$costcenter."' AND b.id_product='$product' LIMIT 1 ";
    $r_data = $this->db->query($q_data)->result();

    $mp = (!empty($r_data))?$r_data[0]->mp:0;
    $ct = (!empty($r_data))?$r_data[0]->cycletime:0;

		 echo json_encode(array(
				'no'			  => $no,
        'product'	  => $product,
        'stock'	    => $stock,
        'mp'	    => $mp,
        'ct'	    => $ct,
        'qty_order' => get_qty_order_so($product, $costcenter, $date_akhir, $date_awal),
        'shortages' => get_qty_order_so($product, $costcenter, $date_akhir, $date_awal) - $stock,
        'queue'	    => get_antrian_wip($product, $costcenter)
		 ));
	}

  public function get_maxdate(){
    $costcenter = $this->uri->segment(3);
    $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));
    $q_max      = "SELECT MAx(date_akhir) AS date_akhir FROM produksi_planning WHERE costcenter='".$costcenter."' LIMIT 1 ";
    $max_date   = $this->db->query($q_max)->result();
    $datex = (!empty($max_date[0]->date_akhir))?$max_date[0]->date_akhir:$date_now;
    $date = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
    // $date = date('Y-m-d', strtotime('2020-08-12'));
    $dateMax = date('Y-m-d', strtotime('+31 days', strtotime($datex)));

     echo json_encode(array(
        'min_date'	=> $date,
        'max_date' => $dateMax
     ));
  }

  public function print_plan(){
		$no_plan		= $this->uri->segment(3);

		$session 		= $this->session->userdata('app_session');
		$printby		= $session['id_user'];
		$koneksi		= akses_server();

		include 'plusPrint.php';
		$data_url		= base_url();
		$Split_Beda	= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda	= $Split_Beda[$Jum_Beda - 2];
		// $okeH  			= $this->session->userdata("ses_username");

		history('Print produksi_planning '.$no_plan);

		print_planning_produksi($Nama_Beda, $no_plan, $koneksi, $printby);
	}

  public function print_plan_custom(){
		$costcenter		= $this->uri->segment(3);
    $tgl_awal	  	= $this->uri->segment(4);
    $tgl_akhir		= $this->uri->segment(5);

		$session 		= $this->session->userdata('app_session');
		$printby		= $session['id_user'];
		$koneksi		= akses_server();

		include 'plusPrint.php';
		$data_url		= base_url();
		$Split_Beda	= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda	= $Split_Beda[$Jum_Beda - 2];
		// $okeH  			= $this->session->userdata("ses_username");

		history('Print produksi_planning '.$costcenter.' date '.$tgl_awal.' to '.$tgl_akhir);

		print_planning_produksi_custom($Nama_Beda, $costcenter, $koneksi, $printby, $tgl_awal, $tgl_akhir);
	}

  //========================================================================================================
  //============================================SPK=========================================================
  //========================================================================================================

  public function spk(){
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    history("View index SPK Produksi");
    $this->template->title('SPK Produksi');
    $this->template->render('spk');
  }
}
?>
