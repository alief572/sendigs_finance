<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Sales extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Sales.View';
    protected $addPermission  	= 'Sales.Add';
    protected $managePermission = 'Sales.Manage';
    protected $deletePermission = 'Sales.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Sales/sales_model'
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function so()
    {
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->sales_model->get_data('cycletime_header','deleted','N');
      history("View index sales order");
      $this->template->set('results', $data);
      $this->template->title('Sales Order');
      $this->template->render('so');
    }

    public function data_side_sales_order(){
  		$this->sales_model->get_json_sales_order();
  	}

    public function add_so(){

    	$session  = $this->session->userdata('app_session');
      $no_so 	  = $this->uri->segment(3);
  		$header   = $this->db->get_where('sales_order_header',array('no_so' => $no_so))->result();
      $detail   = $this->db->get_where('sales_order_detail',array('no_so' => $no_so))->result_array();
			$customer = $this->sales_model->get_data('master_customer');
			$shipping = $this->sales_model->get_data('list','category','shipping');
      $product    = $this->sales_model->get_data('ms_inventory_category2');

      // print_r($header);
      // exit;
			$data = [
        'header' => $header,
        'detail' => $detail,
  			'customer' => $customer,
  			'shipping' => $shipping,
        'product' => $product
			];
			$this->template->set('results', $data);
      $this->template->title('Add Sales Order');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add_so',$data);
    }

    public function edit(){

    	$session = $this->session->userdata('app_session');
      $id_time = $this->uri->segment(3);
			$customer    = $this->sales_model->get_data('master_customer');
			$supplier    = $this->sales_model->get_data('master_supplier');
			$material    = $this->sales_model->get_data('ms_inventory_category2');
			// $machine      = $this->sales_model->get_data_group('asset','category','4','nm_asset');
      // $mould      = $this->sales_model->get_data_group('asset','category','5','nm_asset');
			// $costcenter  = $this->sales_model->get_data('ms_costcenter','deleted','0');
      $header	= $this->db->query("SELECT * FROM cycletime_header WHERE id_time='".$id_time."' LIMIT 1 ")->result();
      $costcenter	= $this->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' ORDER BY nama_costcenter ASC ")->result_array();
      $machine	= $this->db->query("SELECT * FROM asset WHERE category='4' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
      $mould	= $this->db->query("SELECT * FROM asset WHERE category='5' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
			$data = [
			'customer' => $customer,
			'supplier' => $supplier,
			'material' => $material,
			'mesin' => $machine,
      'mould' => $mould,
			'costcenter' => $costcenter,
      'header' => $header
			];
			$this->template->set('results', $data);
      $this->template->page_icon('fa fa-edit');
      $this->template->title('Edit Cycletime');
      $this->template->render('edit', $data);
    }


	public function detail_sales_order(){
		// $this->auth->restrict($this->viewPermission);
		$no_so 	= $this->input->post('no_so');
		$header = $this->db->get_where('sales_order_header',array('no_so' => $no_so))->result();
    $detail = $this->db->get_where('sales_order_detail',array('no_so' => $no_so))->result_array();
    $customer    = $this->sales_model->get_data('master_customer');
    $shipping  = $this->sales_model->get_data('list','category','shipping');
    // print_r($header);
		$data = [
			'header' => $header,
      'detail' => $detail,
      'customer' => $customer,
			'shipping' => $shipping
			];
    $this->template->set('results', $data);
		$this->template->render('detail_sales_order', $data);
	}

  public function get_add(){
		$id 	= $this->uri->segment(3);
		$no 	= 0;

    $product    = $this->sales_model->get_data('ms_inventory_category2');
		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr class='header_".$id."'>";
				$d_Header .= "<td align='center'>".$id."</td>";
				$d_Header .= "<td align='left'>";
        $d_Header .= "<select name='Detail[".$id."][product]' data-no='".$id."' class='chosen_select form-control input-sm inline-blockd product'>";
        $d_Header .= "<option value='0'>Select Product Name</option>";
        foreach($product AS $valx){
          $d_Header .= "<option value='".$valx->id_category2."'>".strtoupper($valx->nama)."</option>";
        }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<input type='text' name='Detail[".$id."][qty_order]' class='form-control input-md maskM qty' placeholder='Qty Propose' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
				$d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<input type='text' name='Detail[".$id."][qty_propose]' class='form-control input-md maskM qty' placeholder='Qty Order' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
				$d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<input type='text' name='Detail[".$id."][qty_balance]' id='balance_".$id."' class='form-control text-center input-md' placeholder='Qty Balance' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
        $d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='add_".$id."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
			$d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}

  public function save_so(){

  	$Arr_Kembali	= array();
		$data			= $this->input->post();
    // print_r($data);
    // exit;
		$session 		  = $this->session->userdata('app_session');
    $Detail 	    = $data['Detail'];
    $Ym					  = date('y');
    $no_so        = $data['no_so'];
    $no_sox        = $data['no_so'];

    $created_by   = 'updated_by';
    $created_date = 'updated_date';
    $tanda        = 'Insert ';
    if(empty($no_sox)){
      //pengurutan kode
      $srcMtr			  = "SELECT MAX(no_so) as maxP FROM sales_order_header WHERE no_so LIKE 'SO".$Ym."%' ";
      $numrowMtr		= $this->db->query($srcMtr)->num_rows();
      $resultMtr		= $this->db->query($srcMtr)->result_array();
      $angkaUrut2		= $resultMtr[0]['maxP'];
      $urutan2		  = (int)substr($angkaUrut2, 4, 4);
      $urutan2++;
      $urut2			  = sprintf('%04s',$urutan2);
      $no_so	      = "SO".$Ym.$urut2;

      $created_by   = 'created_by';
      $created_date = 'created_date';
      $tanda        = 'Update ';
    }

    $ArrHeader		= array(
      'no_so'			    => $no_so,
      'code_cust'	    => $data['code_cust'],
      'delivery_date'	=> date('Y-m-d', strtotime($data['delivery_date'])),
      'shipping'	    => $data['shipping'],
      'no_so_manual'	=> strtoupper($data['no_so_manual']),
      'shipment'	    => strtoupper($data['shipment']),
      'no_po'	        => strtoupper($data['no_po']),
      $created_by	    => $session['id_user'],
      $created_date	  => date('Y-m-d H:i:s')
    );

    $ArrDetail	= array();
    $ArrDetail2	= array();
    foreach($Detail AS $val => $valx){
      $urut				= sprintf('%03s',$val);
      $ArrDetail[$val]['no_so'] 			 = $no_so;
      $ArrDetail[$val]['no_so_detail'] = $no_so."-".$urut;
      $ArrDetail[$val]['product'] 		 = $valx['product'];
      $ArrDetail[$val]['code_cust'] 		= $data['code_cust'];
      $ArrDetail[$val]['delivery_date'] = date('Y-m-d', strtotime($data['delivery_date']));
      $ArrDetail[$val]['shipping'] 		 = $data['shipping'];
      $ArrDetail[$val]['qty_order'] 	 = str_replace(',','',$valx['qty_order']);
      $ArrDetail[$val]['qty_propose'] = str_replace(',','',$valx['qty_propose']);
    }

    // print_r($ArrHeader);
		// print_r($ArrDetail);
		// exit;

		$this->db->trans_start();
    if(empty($no_sox)){
      $this->db->insert('sales_order_header', $ArrHeader);
    }
    if(!empty($no_sox)){
      $this->db->where('no_so', $no_so);
      $this->db->update('sales_order_header', $ArrHeader);
    }

    if(!empty($ArrDetail)){
      $this->db->delete('sales_order_detail', array('no_so' => $no_so));
			$this->db->insert_batch('sales_order_detail', $ArrDetail);
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
      history($tanda." sales order ".$no_so);
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

  public function delete_sales_order(){

  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
    // print_r($data);
    // exit;
		$session 		  = $this->session->userdata('app_session');
    $no_so	      = $this->uri->segment(3);

    $ArrHeader		  = array(
      'deleted'			=> "Y",
      'deleted_by'	=> $session['id_user'],
      'deleted_date'	=> date('Y-m-d H:i:s')
    );

		$this->db->trans_start();
      $this->db->where('no_so', $no_so);
			$this->db->update('sales_order_header', $ArrHeader);
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
      history("Delete Sales Order ".$no_so);
		}

		echo json_encode($Arr_Data);
	}

  public function print_sales_order(){
		$id_bq	= $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$session 		   = $this->session->userdata('app_session');
		$koneksi		= akses_server();

		include 'plusPrint.php';
		$data_url		= base_url();
		$Split_Beda		= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		history('Print Hasil Sales Order '.$id_bq);

		PrintSalesOrder($Nama_Beda, $koneksi, $session['id_user'], $id_bq);
	}

  public function get_balance(){
		$product 	= $this->uri->segment(3);
    $cust 	  = $this->uri->segment(4);

    $balance  = $this->db->query("SELECT qty_kurang FROM search_balance_so WHERE product = '".$product."' AND code_cust='".$cust."' LIMIT 1")->result();

		 echo json_encode(array(
				'balance'			=> (!empty($balance[0]->qty_kurang))?$balance[0]->qty_kurang:0,
		 ));
	}

}

?>
