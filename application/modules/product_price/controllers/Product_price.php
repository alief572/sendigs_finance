<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_price extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Product_Price.View';
	protected $addPermission  	= 'Product_Price.Add';
	protected $managePermission = 'Product_Price.Manage';
	protected $deletePermission = 'Product_Price.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			'Product_price/product_price_model'
		));
		date_default_timezone_set('Asia/Bangkok');
	}

	//========================================================BOM

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');

		$product_price 	= $this->db->select('MAX(update_date) AS updated_date')->get('product_price')->result();
		$last_update 	= "Last Update: " . date('d-M-Y H:i:s', strtotime($product_price[0]->updated_date));
		$data = [
			'product_lv1' => array(),
			'last_update' => $last_update
		];

		history("View index product costing");
		$this->template->title('Costing / Product Costing');
		$this->template->render('index', $data);
	}

	public function data_side_product_price()
	{
		$this->product_price_model->get_json_product_price();
	}

	public function detail_costing()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->uri->segment(3);
		$product_price 		= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));

		$data = [
			'no_bom' => $no_bom,
			'dataList' => $costing_rate,
			'product_price' => $product_price,
			'header' => $header,
			'detail' => $detail,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'product' => $product,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories(),
			'GET_PRICE_REF' => get_price_ref()
		];
		$this->template->title('Costing Rate');
		$this->template->render('detail_costing', $data);
	}

	public function detail_material()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 			= $this->input->post('no_bom');

		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail1   			= $this->db->select('code_material, weight')->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail2   			= $this->db->select('a.code_material, (a.weight * b.qty) AS weight')->join('bom_detail b', 'a.no_bom_detail=b.no_bom_detail AND a.category=b.category', 'inner')->get_where('bom_detail_custom a', array('a.no_bom' => $no_bom, 'a.category' => 'hi grid std'))->result_array();
		$detail_assembly   	= $this->db->select('a.*, b.code_lv4')->join('product_price b', 'a.kode=b.kode', 'inner')->get_where('product_price_bom_detail a', array('a.no_bom' => $no_bom, 'a.category' => 'hi grid std', 'b.deleted_date' => NULL))->result_array();
		$detail   			= $detail1;
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$detail_mat_joint   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));

		$data = [
			'header' => $header,
			'detail' => $detail,
			'detail_mat_joint' => $detail_mat_joint,
			'detail_assembly' => $detail_assembly,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'product' => $product,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories(),
			'GET_PRICE_REF' => get_price_ref()
		];
		$this->template->render('detail_bom_material', $data);
	}

	public function update_product_price()
	{
		$session = $this->session->userdata('app_session');
		$dateTime 	= date('Y-m-d H:i:s');
		$id_user	= $session['id_user'];

		$SQL 	= "SELECT a.* FROM bom_header a WHERE a.deleted_date IS NULL AND a.category IN ('standard','grid standard','ftackel') AND a.id_product LIKE 'P%'";
		$result = $this->db->query($SQL)->result_array();

		$dateTime 	= date('Y-m-d H:i:s');
		$date 		= date('YmdHis');

		$GET_RATE_COSTING = get_rate_costing_rate();
		$GET_RATE_MAN_POWER = $this->db->order_by('id', 'desc')->get('rate_man_power')->result();
		$GET_LEVEL4 = get_inventory_lv4();
		$GET_PRICE_REF = get_price_ref();
		$GET_MACHINE_PRODUCT = get_machine_product();
		$GET_MOLD_PRODUCT = get_mold_product();
		$GET_MACHINE_RATE = get_rate_machine();
		$GET_MOLD_RATE = get_rate_mold();
		$GET_CYCLETIME = get_total_time_cycletime();
		$GET_CYCLETIME_BOM_STD = get_total_time_cycletime_bom_std();

		$ArrHeader = [];
		$ArrDetailDefault = [];
		$ArrDetailAdditive = [];
		$ArrDetailAdditiveCustom = [];
		$ArrDetailTopping = [];
		$ArrDetailToppingCustom = [];
		foreach ($result as $key => $value) {
			$no_bom = $value['no_bom'];
			$category_bom = $value['category'];
			$kode 	= $date . '-' . $no_bom;

			$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
			$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
			$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
			$detail_hi_grid_std = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'hi grid std'))->result_array();
			$detail_mat_joint   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'))->result_array();

			$BERAT_MINUS = 0;
			if (!empty($detail_additive)) {
				foreach ($detail_additive as $val => $valx) {
					$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx['no_bom_detail'], 'category' => 'additive'))->result();
					$PENGURANGAN_BERAT = 0;
					foreach ($detail_custom as $valx2) {
						$PENGURANGAN_BERAT += $valx2->weight * $valx2->persen / 100;
					}
					$BERAT_MINUS += $PENGURANGAN_BERAT;
				}
			}

			$TOTAL_PRICE_ALL = 0;
			$TOTAL_BERAT_BERSIH = 0;

			$PULTRUSION_PRICE = 0;
			$PULTRUSION_BERAT = 0;
			//default
			if (!empty($detail)) {
				foreach ($detail as $val => $valx) {
					$val++;
					$code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2'])) ? $GET_LEVEL4[$valx['code_material']]['code_lv2'] : '-';
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;
					$nm_category = strtolower(get_name('new_inventory_2', 'nama', 'code_lv2', $code_lv2));
					$berat_pengurang_additive = ($nm_category == 'resin') ? $BERAT_MINUS : 0;

					$berat_bersih = $valx['weight'] - $berat_pengurang_additive;
					$total_price = $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL += $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;

					$PULTRUSION_PRICE += $total_price;
					$PULTRUSION_BERAT += $berat_bersih;

					$UNIQ = $val . '-' . $key;
					$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailDefault[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailDefault[$UNIQ]['persen'] 			=  $valx['persen'];
					$ArrDetailDefault[$UNIQ]['persen_add'] 		=  $valx['persen_add'];
					$ArrDetailDefault[$UNIQ]['length'] 			=  $valx['length'];
					$ArrDetailDefault[$UNIQ]['width'] 			=  $valx['width'];
					$ArrDetailDefault[$UNIQ]['qty'] 				=  $valx['qty'];
					$ArrDetailDefault[$UNIQ]['m2'] 				=  $valx['m2'];
					$ArrDetailDefault[$UNIQ]['file_upload'] 		=  $valx['file_upload'];
					$ArrDetailDefault[$UNIQ]['pengurangan_additive'] = $berat_pengurang_additive;
					$ArrDetailDefault[$UNIQ]['berat_bersih'] 		= $berat_bersih;
					$ArrDetailDefault[$UNIQ]['price_ref'] 			= $price_ref;
					$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;
				}
			}

			$berat_per_kg = 0;
			if ($PULTRUSION_PRICE > 0 and $PULTRUSION_BERAT > 0) {
				$berat_per_kg = $PULTRUSION_PRICE / $PULTRUSION_BERAT;
			}

			//mat joint
			if (!empty($detail_mat_joint)) {
				foreach ($detail_mat_joint as $val => $valx) {
					$val++;
					$code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2'])) ? $GET_LEVEL4[$valx['code_material']]['code_lv2'] : '-';
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;
					$nm_category 	= strtolower(get_name('new_inventory_2', 'nama', 'code_lv2', $code_lv2));

					$berat_bersih = $valx['weight'];
					$total_price = $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL += $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;
					$UNIQ = $val . '-8888' . $key;
					$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailDefault[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailDefault[$UNIQ]['persen'] 			=  $valx['persen'];
					$ArrDetailDefault[$UNIQ]['persen_add'] 		=  $valx['persen_add'];
					$ArrDetailDefault[$UNIQ]['length'] 			=  $valx['length'];
					$ArrDetailDefault[$UNIQ]['width'] 			=  $valx['width'];
					$ArrDetailDefault[$UNIQ]['qty'] 			=  $valx['qty'];
					$ArrDetailDefault[$UNIQ]['m2'] 				=  $valx['m2'];
					$ArrDetailDefault[$UNIQ]['file_upload'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['pengurangan_additive'] 	= NULL;
					$ArrDetailDefault[$UNIQ]['berat_bersih'] 			= $berat_bersih;
					$ArrDetailDefault[$UNIQ]['price_ref'] 				= $price_ref;
					$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;
				}
			}

			//additive
			if (!empty($detail_additive)) {
				foreach ($detail_additive as $val => $valx) {
					$val++;
					$UNIQ = $val . '-' . $key;
					$ArrDetailAdditive[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailAdditive[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailAdditive[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailAdditive[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailAdditive[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailAdditive[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailAdditive[$UNIQ]['persen'] 			=  $valx['persen'];
					$ArrDetailAdditive[$UNIQ]['persen_add'] 		=  $valx['persen_add'];
					$ArrDetailAdditive[$UNIQ]['length'] 			=  $valx['length'];
					$ArrDetailAdditive[$UNIQ]['width'] 			=  $valx['width'];
					$ArrDetailAdditive[$UNIQ]['qty'] 				=  $valx['qty'];
					$ArrDetailAdditive[$UNIQ]['m2'] 				=  $valx['m2'];
					$ArrDetailAdditive[$UNIQ]['file_upload'] 		=  $valx['file_upload'];

					$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx['no_bom_detail'], 'category' => 'additive'))->result_array();
					foreach ($detail_custom as $val2 => $valx2) {
						$price_ref      = (!empty($GET_PRICE_REF[$valx2['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx2['code_material']]['price_ref'] : 0;
						$berat_bersih = $valx2['weight'];
						$total_price    = $berat_bersih * $price_ref;
						$TOTAL_PRICE_ALL += $total_price;
						$TOTAL_BERAT_BERSIH += $berat_bersih;
						$UNIQ = $val . '-' . $val2 . '-' . $key;
						$ArrDetailAdditiveCustom[$UNIQ]['kode'] 				=  $kode;
						$ArrDetailAdditiveCustom[$UNIQ]['category'] 			=  $valx2['category'];
						$ArrDetailAdditiveCustom[$UNIQ]['no_bom'] 				=  $valx2['no_bom'];
						$ArrDetailAdditiveCustom[$UNIQ]['no_bom_detail'] 		=  $valx2['no_bom_detail'];
						$ArrDetailAdditiveCustom[$UNIQ]['code_material'] 		=  $valx2['code_material'];
						$ArrDetailAdditiveCustom[$UNIQ]['nm_material'] 		=  $valx2['nm_material'];
						$ArrDetailAdditiveCustom[$UNIQ]['weight'] 				=  $valx2['weight'];
						$ArrDetailAdditiveCustom[$UNIQ]['persen'] 				=  $valx2['persen'];
						$ArrDetailAdditiveCustom[$UNIQ]['pengurangan_additive'] 	=  $BERAT_MINUS;
						$ArrDetailAdditiveCustom[$UNIQ]['berat_bersih'] 			=  $berat_bersih;
						$ArrDetailAdditiveCustom[$UNIQ]['price_ref'] 				=  $price_ref;
						$ArrDetailAdditiveCustom[$UNIQ]['total_price'] 			=  $total_price;
					}
				}
			}

			//topping
			if (!empty($detail_topping)) {
				foreach ($detail_topping as $val => $valx) {
					$UNIQ = $val . '-' . $key;
					$ArrDetailTopping[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailTopping[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailTopping[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailTopping[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailTopping[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailTopping[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailTopping[$UNIQ]['persen'] 			=  $valx['persen'];
					$ArrDetailTopping[$UNIQ]['persen_add'] 		=  $valx['persen_add'];
					$ArrDetailTopping[$UNIQ]['length'] 			=  $valx['length'];
					$ArrDetailTopping[$UNIQ]['width'] 			=  $valx['width'];
					$ArrDetailTopping[$UNIQ]['qty'] 			=  $valx['qty'];
					$ArrDetailTopping[$UNIQ]['m2'] 				=  $valx['m2'];
					$ArrDetailTopping[$UNIQ]['file_upload'] 	=  $valx['file_upload'];
					$ArrDetailTopping[$UNIQ]['price_ref'] 		=  0;
					$ArrDetailTopping[$UNIQ]['total_price'] 	=  0;
					$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx['no_bom_detail'], 'category' => 'topping'))->result_array();
					foreach ($detail_custom as $val2 => $valx2) {
						$price_ref      = (!empty($GET_PRICE_REF[$valx2['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx2['code_material']]['price_ref'] : 0;
						$berat_bersih    = $valx2['weight'] * $valx['qty'];
						$total_price    = $berat_bersih * $price_ref;
						$TOTAL_PRICE_ALL += $total_price;
						$TOTAL_BERAT_BERSIH += $berat_bersih;
						$UNIQ = $val . '-' . $val2 . '-' . $key;
						$ArrDetailToppingCustom[$UNIQ]['kode'] 			=  $kode;
						$ArrDetailToppingCustom[$UNIQ]['category'] 		=  $valx2['category'];
						$ArrDetailToppingCustom[$UNIQ]['no_bom'] 			=  $valx2['no_bom'];
						$ArrDetailToppingCustom[$UNIQ]['no_bom_detail'] 	=  $valx2['no_bom_detail'];
						$ArrDetailToppingCustom[$UNIQ]['code_material'] 	=  $valx2['code_material'];
						$ArrDetailToppingCustom[$UNIQ]['nm_material'] 	=  $valx2['nm_material'];
						$ArrDetailToppingCustom[$UNIQ]['weight'] 			=  $valx2['weight'];
						$ArrDetailToppingCustom[$UNIQ]['persen'] 			=  $valx2['persen'];
						$ArrDetailToppingCustom[$UNIQ]['pengurangan_additive'] = NULL;
						$ArrDetailToppingCustom[$UNIQ]['berat_bersih'] 		= $berat_bersih;
						$ArrDetailToppingCustom[$UNIQ]['price_ref'] 			= $price_ref;
						$ArrDetailToppingCustom[$UNIQ]['total_price'] 			= $total_price;
					}
				}
			}

			//hi grid standar assembly
			$GET_PRODUCT_PRICE = get_product_costing();
			$PRICE_ASSEMBLY_ALL = 0;
			if (!empty($detail_hi_grid_std)) {
				foreach ($detail_hi_grid_std as $val => $valx) {
					$UNIQ = $val . '-9999-' . $key;
					$ArrDetailTopping[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailTopping[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailTopping[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailTopping[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailTopping[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailTopping[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailTopping[$UNIQ]['persen'] 			=  $valx['persen'];
					$ArrDetailTopping[$UNIQ]['persen_add'] 		=  $valx['persen_add'];
					$ArrDetailTopping[$UNIQ]['length'] 			=  $valx['length'];
					$ArrDetailTopping[$UNIQ]['width'] 			=  $valx['width'];
					$ArrDetailTopping[$UNIQ]['qty'] 			=  $valx['qty'];
					$ArrDetailTopping[$UNIQ]['m2'] 				=  $valx['m2'];
					$ArrDetailTopping[$UNIQ]['file_upload'] 	=  $valx['file_upload'];

					$price_assembly = (!empty($GET_PRODUCT_PRICE[$valx['code_material']]['price_list'])) ? $GET_PRODUCT_PRICE[$valx['code_material']]['price_list'] : 0;
					$TOTAL_PRICE_ALL += $price_assembly * $valx['qty'];
					$PRICE_ASSEMBLY_ALL += $price_assembly * $valx['qty'];

					$ArrDetailTopping[$UNIQ]['price_ref'] 	=  $price_assembly;
					$ArrDetailTopping[$UNIQ]['total_price'] =  $price_assembly * $valx['qty'];

					$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx['no_bom_detail'], 'category' => 'hi grid std'))->result_array();
					foreach ($detail_custom as $val2 => $valx2) {
						$price_ref      = (!empty($GET_PRICE_REF[$valx2['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx2['code_material']]['price_ref'] : 0;
						$berat_bersih    = $valx2['weight'] * $valx['qty'];
						$total_price    = $berat_bersih * $price_ref;
						// $TOTAL_PRICE_ALL += $total_price;
						// $TOTAL_BERAT_BERSIH += $berat_bersih;
						$UNIQ = $val . '-' . $val2 . '-' . $key;
						$ArrDetailToppingCustom[$UNIQ]['kode'] 			=  $kode;
						$ArrDetailToppingCustom[$UNIQ]['category'] 		=  $valx2['category'];
						$ArrDetailToppingCustom[$UNIQ]['no_bom'] 			=  $valx2['no_bom'];
						$ArrDetailToppingCustom[$UNIQ]['no_bom_detail'] 	=  $valx2['no_bom_detail'];
						$ArrDetailToppingCustom[$UNIQ]['code_material'] 	=  $valx2['code_material'];
						$ArrDetailToppingCustom[$UNIQ]['nm_material'] 	=  $valx2['nm_material'];
						$ArrDetailToppingCustom[$UNIQ]['weight'] 			=  $valx2['weight'];
						$ArrDetailToppingCustom[$UNIQ]['persen'] 			=  $valx2['persen'];
						$ArrDetailToppingCustom[$UNIQ]['pengurangan_additive'] = NULL;
						$ArrDetailToppingCustom[$UNIQ]['berat_bersih'] 		= $berat_bersih;
						$ArrDetailToppingCustom[$UNIQ]['price_ref'] 			= $price_ref;
						$ArrDetailToppingCustom[$UNIQ]['total_price'] 			= $total_price;
					}
				}
			}

			$code_level4 = $value['id_product'];
			$ArrHeader[$key]['kode'] 				= $kode;
			$ArrHeader[$key]['no_bom'] 				= $no_bom;
			$ArrHeader[$key]['code_lv1'] 			= (!empty($GET_LEVEL4[$code_level4]['code_lv1'])) ? $GET_LEVEL4[$code_level4]['code_lv1'] : NULL;
			$ArrHeader[$key]['product_type'] 		= NULL;
			$ArrHeader[$key]['code_lv2'] 			= (!empty($GET_LEVEL4[$code_level4]['code_lv2'])) ? $GET_LEVEL4[$code_level4]['code_lv2'] : NULL;
			$ArrHeader[$key]['product_category'] 	= NULL;
			$ArrHeader[$key]['code_lv3'] 			= (!empty($GET_LEVEL4[$code_level4]['code_lv3'])) ? $GET_LEVEL4[$code_level4]['code_lv3'] : NULL;
			$ArrHeader[$key]['product_jenis'] 		= NULL;
			$ArrHeader[$key]['code_lv4'] 			= $code_level4;
			$ArrHeader[$key]['product_master'] 		= (!empty($GET_LEVEL4[$code_level4]['nama'])) ? $GET_LEVEL4[$code_level4]['nama'] : NULL;
			$ArrHeader[$key]['berat_material'] 		= $TOTAL_BERAT_BERSIH;

			$ArrHeader[$key]['update_by'] 			= $id_user;
			$ArrHeader[$key]['update_date'] 		= $dateTime;
			$ArrHeader[$key]['deleted_by'] 			= NULL;
			$ArrHeader[$key]['deleted_date'] 		= NULL;


			$qty_man_power 		= (!empty($GET_CYCLETIME[$code_level4 . "-" . $no_bom]['qty_mp'])) ? $GET_CYCLETIME[$code_level4 . "-" . $no_bom]['qty_mp'] : 0;
			$cycletimeMaster 	= (!empty($GET_CYCLETIME[$code_level4 . "-" . $no_bom]['ct_manpower'])) ? $GET_CYCLETIME[$code_level4 . "-" . $no_bom]['ct_manpower'] : 0;
			$cycletimeMP		= (!empty($GET_CYCLETIME[$code_level4 . "-" . $no_bom]['ct_manpower'])) ? $GET_CYCLETIME[$code_level4 . "-" . $no_bom]['ct_manpower'] : 0;
			$cycletimeMesin 	= (!empty($GET_CYCLETIME[$code_level4 . "-" . $no_bom]['ct_machine'])) ? $GET_CYCLETIME[$code_level4 . "-" . $no_bom]['ct_machine'] : 0;
			$rate_mp 		= 0;
			$rate_cycletime 		= 0;
			$rate_cycletime_mch 	= 0;
			if ($cycletimeMaster > 0) {
				$rate_cycletime 		= $cycletimeMaster / 60;
				$rate_cycletime_mch 	= $cycletimeMesin / 60;
			}
			if ($cycletimeMP > 0) {
				$rate_mp 		= $cycletimeMP / 60;
			}
			if ($category_bom == 'standard') {
				$cycletimeMaster 	= (!empty($GET_CYCLETIME_BOM_STD[$code_level4 . "-" . $no_bom]['ct_manpower'])) ? $GET_CYCLETIME_BOM_STD[$code_level4 . "-" . $no_bom]['ct_manpower'] : 0;
				$cycletimeMesin 	= (!empty($GET_CYCLETIME_BOM_STD[$code_level4 . "-" . $no_bom]['ct_machine'])) ? $GET_CYCLETIME_BOM_STD[$code_level4 . "-" . $no_bom]['ct_machine'] : 0;
				$rate_cycletime 		= 0;
				$rate_cycletime_mch 	= 0;
				if ($cycletimeMaster > 0) {
					$rate_cycletime 		= $cycletimeMaster / 60;
					$rate_cycletime_mch 	= $cycletimeMesin / 60;
				}
			}
			$rate_manpower 	= $GET_RATE_MAN_POWER[0]->upah_per_jam_dollar;

			$kode_mesin 	= (!empty($GET_MACHINE_PRODUCT[$code_level4])) ? $GET_MACHINE_PRODUCT[$code_level4] : 0;
			$kode_mold 		= (!empty($GET_MOLD_PRODUCT[$code_level4])) ? $GET_MOLD_PRODUCT[$code_level4] : 0;

			$rate_depresiasi 	= (!empty($GET_MACHINE_RATE[$kode_mesin]['biaya_mesin'])) ? $GET_MACHINE_RATE[$kode_mesin]['biaya_mesin'] : 0;
			$rate_mould 		= (!empty($GET_MOLD_RATE[$kode_mold]['biaya_mesin'])) ? $GET_MOLD_RATE[$kode_mold]['biaya_mesin'] : 0;

			//NEW========================
			$ct_setting 		= (!empty($GET_CYCLETIME[$code_level4 . "-" . $no_bom]['total_ct_setting'])) ? $GET_CYCLETIME[$code_level4 . "-" . $no_bom]['total_ct_setting'] : 0;
			$ct_produksi 		= (!empty($GET_CYCLETIME[$code_level4 . "-" . $no_bom]['total_ct_produksi'])) ? $GET_CYCLETIME[$code_level4 . "-" . $no_bom]['total_ct_produksi'] : 0;
			$ct_moq 			= (!empty($GET_CYCLETIME[$code_level4 . "-" . $no_bom]['moq'])) ? $GET_CYCLETIME[$code_level4 . "-" . $no_bom]['moq'] : 0;
			$berat_per_kg   	= $berat_per_kg;
			$waste_set_resin 	= $value['waste_setting_resin'];
			$waste_set_glass 	= $value['waste_setting_glass'];
			$bom_moq 			= $value['moq'];
			$qty_man_power 		= $qty_man_power;
			//END NEW====================

			// if('P423000121' == $code_level4){
			// 	echo $kode_mesin.'<br>';
			// 	echo $rate_depresiasi; exit;
			// }

			$persen_indirect 	= $GET_RATE_COSTING[3];
			$persen_consumable 	= $GET_RATE_COSTING[6];
			$persen_packing 	= $GET_RATE_COSTING[7];
			$persen_enginnering = $GET_RATE_COSTING[9];
			$persen_foh 		= $GET_RATE_COSTING[10];
			$persen_fin_adm 	= $GET_RATE_COSTING[11];
			$persen_mkt_sales 	= $GET_RATE_COSTING[12];
			$persen_interest 	= $GET_RATE_COSTING[13];
			$persen_profit 		= $GET_RATE_COSTING[14];
			$persen_allowance 	= $GET_RATE_COSTING[18];

			//1 material
			$cost_material 	= $TOTAL_PRICE_ALL;
			//# khusus purtution
			$biaya_setting_mp 		= ($rate_mp / 60) * $rate_manpower;
			$biaya_setting_mesin 	= ($rate_mp / 60) * $rate_depresiasi;
			$biaya_waste_set_mat	= ($waste_set_resin + $waste_set_glass) * $berat_per_kg;
			$biaya_total_setting	= $biaya_setting_mp + $biaya_setting_mesin + $biaya_waste_set_mat;
			$charge_setting_bom		= ($biaya_total_setting > 0 and $bom_moq > 0) ? $biaya_total_setting / $bom_moq : 0;
			$charge_setting_ct		= ($biaya_total_setting > 0 and $ct_moq > 0) ? $biaya_total_setting / $ct_moq : 0;
			//2 man power
			$direct_labour	= $rate_cycletime * $rate_manpower;
			$indirect 		= $direct_labour * $persen_indirect / 100;
			$cost_man_power = $direct_labour + $indirect;
			//3 machine mould consumable
			$machine 	= $rate_cycletime_mch * $rate_depresiasi;
			$mould 		= $rate_cycletime_mch * $rate_mould;
			$consumable = $cost_material * ($persen_consumable / 100);
			$cost_mesin	= $machine + $mould + $consumable;
			//4 logistik
			//getUpdate Shipping
			$newLogistik 		= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
			$stsUpdateLogistik 	= (!empty($newLogistik[0]['sts_logistik'])) ? $newLogistik[0]['sts_logistik'] : NULL;
			$stsUpdatePacking 	= (!empty($newLogistik[0]['cost_packing'])) ? $newLogistik[0]['cost_packing'] : 0;
			$stsUpdateTransport = (!empty($newLogistik[0]['cost_transport'])) ? $newLogistik[0]['cost_transport'] : 0;
			if ($stsUpdateLogistik == 'Y') {
				$packing 		= $stsUpdatePacking;
				$transport		= $stsUpdateTransport;
			} else {
				$packing 		= ($cost_material + $cost_man_power + $cost_mesin) * $persen_packing / 100;
				$transport		= 0;
			}
			$cost_logistik 	= $packing + $transport;

			$cost_enginnering 	= ($cost_material + $cost_man_power + $cost_mesin) * $persen_enginnering / 100;
			$cost_foh 			= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering) * $persen_foh / 100;
			$cost_fin_adm 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering) * $persen_fin_adm / 100;
			$cost_mkt_sales 	= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering) * $persen_mkt_sales / 100;
			$cost_interest 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales) * $persen_interest / 100;
			$cost_profit 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales + $cost_interest) * $persen_profit / 100;
			$bottom_price 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales + $cost_interest + $cost_profit);
			$factor_kompetitif	= 1;
			$bottom_selling		= $bottom_price * $factor_kompetitif;
			$nego_allowance		= $bottom_selling * ($persen_allowance / 100);
			$price_final		= $bottom_selling + $nego_allowance;

			$ArrHeader[$key]['ct_setting'] 				= $ct_setting;
			$ArrHeader[$key]['ct_produksi'] 			= $ct_produksi;
			$ArrHeader[$key]['ct_moq'] 					= $ct_moq;
			$ArrHeader[$key]['berat_per_kg'] 			= $berat_per_kg;
			$ArrHeader[$key]['waste_set_resin'] 		= $waste_set_resin;
			$ArrHeader[$key]['waste_set_glass'] 		= $waste_set_glass;
			$ArrHeader[$key]['bom_moq'] 				= $bom_moq;
			$ArrHeader[$key]['biaya_setting_mp'] 		= $biaya_setting_mp;
			$ArrHeader[$key]['biaya_setting_mesin'] 	= $biaya_setting_mesin;
			$ArrHeader[$key]['biaya_waste_set_mat'] 	= $biaya_waste_set_mat;
			$ArrHeader[$key]['biaya_total_setting'] 	= $biaya_total_setting;
			$ArrHeader[$key]['charge_setting_bom'] 		= $charge_setting_bom;
			$ArrHeader[$key]['charge_setting_ct'] 		= $charge_setting_ct;
			$ArrHeader[$key]['qty_man_power'] 			= $qty_man_power;

			$ArrHeader[$key]['rate_cycletime'] 			= $rate_cycletime;
			$ArrHeader[$key]['rate_cycletime_machine'] 	= $rate_cycletime_mch;
			$ArrHeader[$key]['rate_man_power_usd'] 		= $rate_manpower;
			$ArrHeader[$key]['rate_man_power_idr'] 	= $GET_RATE_MAN_POWER[0]->upah_per_jam;
			$ArrHeader[$key]['rate_depresiasi'] 	= $rate_depresiasi;
			$ArrHeader[$key]['rate_mould'] 			= $rate_mould;
			$ArrHeader[$key]['cost_material'] 		= $cost_material;
			$ArrHeader[$key]['cost_product_assembly'] 	= $PRICE_ASSEMBLY_ALL;
			$ArrHeader[$key]['cost_persen_indirect'] 	= $persen_indirect;
			$ArrHeader[$key]['cost_persen_consumable'] 	= $persen_consumable;
			$ArrHeader[$key]['cost_persen_packing'] 	= $persen_packing;
			$ArrHeader[$key]['cost_persen_enginnering']	= $persen_enginnering;
			$ArrHeader[$key]['cost_persen_foh'] 		= $persen_foh;
			$ArrHeader[$key]['cost_persen_fin_adm'] 	= $persen_fin_adm;
			$ArrHeader[$key]['cost_persen_mkt_sales'] 	= $persen_mkt_sales;
			$ArrHeader[$key]['cost_persen_interest'] 	= $persen_interest;
			$ArrHeader[$key]['cost_persen_profit'] 		= $persen_profit;
			$ArrHeader[$key]['cost_bottom_price'] 		= $bottom_price;
			$ArrHeader[$key]['cost_factor_kompetitif']	= $factor_kompetitif;
			$ArrHeader[$key]['cost_nego_allowance'] 	= $persen_allowance;
			$ArrHeader[$key]['cost_price_final'] 		= $price_final;

			$ArrHeader[$key]['price_material'] 			= $cost_material;
			$ArrHeader[$key]['price_man_power'] 		= $cost_man_power;
			$ArrHeader[$key]['price_machine'] 			= $cost_mesin;
			$ArrHeader[$key]['price_total'] 			= $price_final;
			$ArrHeader[$key]['cost_direct_labout'] 		= $direct_labour;
			$ArrHeader[$key]['cost_indirect'] 			= $indirect;
			$ArrHeader[$key]['cost_machine'] 			= $machine;
			$ArrHeader[$key]['cost_mould'] 				= $mould;
			$ArrHeader[$key]['cost_consumable'] 		= $consumable;
			$ArrHeader[$key]['cost_packing'] 			= $packing;
			$ArrHeader[$key]['cost_transport'] 			= $transport;
			$ArrHeader[$key]['cost_enginnering'] 		= $cost_enginnering;
			$ArrHeader[$key]['cost_foh'] 				= $cost_foh;
			$ArrHeader[$key]['cost_fin_adm'] 			= $cost_fin_adm;
			$ArrHeader[$key]['cost_mkt_sales'] 			= $cost_mkt_sales;
			$ArrHeader[$key]['cost_interest'] 			= $cost_interest;
			$ArrHeader[$key]['cost_profit'] 			= $cost_profit;
			$ArrHeader[$key]['cost_bottom_selling'] 	= $bottom_selling;
			$ArrHeader[$key]['cost_allowance'] 			= $nego_allowance;

			$GET_PRODUCT_COSTING = get_product_costing();

			$ArrHeader[$key]['pengajuan_price_list'] 	= (!empty($GET_PRODUCT_COSTING[$no_bom]['pengajuan_price_list'])) ? $GET_PRODUCT_COSTING[$no_bom]['pengajuan_price_list'] : NULL;
			$ArrHeader[$key]['price_list'] 				= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_list'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_list'] : NULL;
			$ArrHeader[$key]['price_list_idr'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_list_idr'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_list_idr'] : NULL;
			$ArrHeader[$key]['kurs'] 					= (!empty($GET_PRODUCT_COSTING[$no_bom]['kurs'])) ? $GET_PRODUCT_COSTING[$no_bom]['kurs'] : NULL;
			$ArrHeader[$key]['price_idr'] 				= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_idr'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_idr'] : NULL;
			$ArrHeader[$key]['price_persen_orindo'] 	= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_persen_orindo'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_persen_orindo'] : NULL;
			$ArrHeader[$key]['price_list_idr_orindo'] 	= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_list_idr_orindo'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_list_idr_orindo'] : NULL;
			$ArrHeader[$key]['status'] 					= (!empty($GET_PRODUCT_COSTING[$no_bom]['status'])) ? $GET_PRODUCT_COSTING[$no_bom]['status'] : 'N';
			$ArrHeader[$key]['status_by'] 				= (!empty($GET_PRODUCT_COSTING[$no_bom]['status_by'])) ? $GET_PRODUCT_COSTING[$no_bom]['status_by'] : NULL;
			$ArrHeader[$key]['status_date'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['status_date'])) ? $GET_PRODUCT_COSTING[$no_bom]['status_date'] : NULL;
			$ArrHeader[$key]['reason'] 					= (!empty($GET_PRODUCT_COSTING[$no_bom]['reason'])) ? $GET_PRODUCT_COSTING[$no_bom]['reason'] : NULL;
			$ArrHeader[$key]['sts_logistik'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['sts_logistik'])) ? $GET_PRODUCT_COSTING[$no_bom]['sts_logistik'] : NULL;
			$ArrHeader[$key]['logistik_by'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['logistik_by'])) ? $GET_PRODUCT_COSTING[$no_bom]['logistik_by'] : NULL;
			$ArrHeader[$key]['logistik_date'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['logistik_date'])) ? $GET_PRODUCT_COSTING[$no_bom]['logistik_date'] : NULL;
			$ArrHeader[$key]['total_price_uj'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['total_price_uj'])) ? $GET_PRODUCT_COSTING[$no_bom]['total_price_uj'] : NULL;
			$ArrHeader[$key]['total_idr_uj'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['total_idr_uj'])) ? $GET_PRODUCT_COSTING[$no_bom]['total_idr_uj'] : NULL;
			$ArrHeader[$key]['selisih_uj'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['selisih_uj'])) ? $GET_PRODUCT_COSTING[$no_bom]['selisih_uj'] : NULL;
		}


		// echo '<pre>';
		// print_r($ArrHeader);
		// print_r($ArrDetailDefault);
		// print_r($ArrDetailAdditive);
		// print_r($ArrDetailAdditiveCustom);
		// print_r($ArrDetailTopping);
		// exit;

		$ArrUpdate = [
			'deleted_by' => $id_user,
			'deleted_date' => $dateTime
		];

		$this->db->trans_start();
		if (!empty($ArrHeader)) {
			$this->db->where('deleted_date', NULL);
			$this->db->not_like('no_bom', 'BOC');
			$this->db->update('product_price', $ArrUpdate);

			$this->db->where('deleted_date', NULL);
			$this->db->update('product_price_assembly', $ArrUpdate);

			$this->db->insert_batch('product_price', $ArrHeader);
		}
		if (!empty($ArrDetailDefault)) {
			$this->db->insert_batch('product_price_bom_detail', $ArrDetailDefault);
		}
		if (!empty($ArrDetailAdditive)) {
			$this->db->insert_batch('product_price_bom_detail', $ArrDetailAdditive);
		}
		if (!empty($ArrDetailAdditiveCustom)) {
			$this->db->insert_batch('product_price_bom_detail_custom', $ArrDetailAdditiveCustom);
		}
		if (!empty($ArrDetailTopping)) {
			$this->db->insert_batch('product_price_bom_detail', $ArrDetailTopping);
		}
		if (!empty($ArrDetailToppingCustom)) {
			$this->db->insert_batch('product_price_bom_detail_custom', $ArrDetailToppingCustom);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Failed process data!',
				'status'	=> 0
			);
			echo json_encode($status);
		} else {
			$this->db->trans_commit();
			$this->update_product_price_assembly();
		}
	}

	public function update_product_price_assembly()
	{
		$session = $this->session->userdata('app_session');
		$dateTime 	= date('Y-m-d H:i:s');
		$id_user	= $session['id_user'];

		$SQL 	= "SELECT a.* FROM bom_header a WHERE a.deleted_date IS NULL AND a.category IN ('grid custom') AND a.id_product LIKE 'P%'";
		$result = $this->db->query($SQL)->result_array();

		$dateTime 	= date('Y-m-d H:i:s');
		$date 		= date('YmdHis');

		$GET_RATE_COSTING = get_rate_costing_rate();
		$GET_RATE_MAN_POWER = $this->db->order_by('id', 'desc')->get('rate_man_power')->result();
		$GET_LEVEL4 = get_inventory_lv4();
		$GET_PRICE_REF = get_price_ref();
		$GET_MACHINE_PRODUCT = get_machine_product();
		$GET_MOLD_PRODUCT = get_mold_product();
		$GET_MACHINE_RATE = get_rate_machine();
		$GET_MOLD_RATE = get_rate_mold();
		$GET_CYCLETIME = get_total_time_cycletime();
		$GET_CYCLETIME_BOM_STD = get_total_time_cycletime_bom_std();

		$GET_CYCLETIME_ASSEMBLY = get_total_time_cycletime_assembly();
		$GET_MACHINE_ASSEMBLY = get_machine_product_assembly();
		$GET_MOLD_ASSEMBLY = get_mold_product_assembly();

		$ArrHeader = [];
		$ArrDetailDefault = [];
		$ArrDetailAdditive = [];
		$ArrDetailAdditiveCustom = [];
		$ArrDetailTopping = [];
		$ArrDetailToppingCustom = [];
		$ArrDetailAssembly = [];
		$ArrDetail = [];
		$nomor = 0;
		$nox = 350;
		foreach ($result as $key => $value) {
			$no_bom 		= $value['no_bom'];
			$category_bom 	= $value['category'];
			$kode 			= $date . '-' . $no_bom;

			$detail_hi_grid_std = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'hi grid std'))->result_array();
			$detail_mat_joint   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'))->result_array();
			$detail_mat_flat_sheet  = $this->db->get_where('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material flat sheet'))->result_array();
			$detail_mat_end_plate   = $this->db->get_where('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material end plate'))->result_array();
			$detail_mat_chequered   = $this->db->get_where('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material ukuran jadi'))->result_array();
			$detail_mat_others   = $this->db->get_where('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material others'))->result_array();

			$TOTAL_PRICE_ALL = 0;
			$TOTAL_BERAT_BERSIH = 0;

			//=================== mat joint
			$FINAL_PRICE_MAT_JOINT = 0;
			$FINAL_WEIGHT_MAT_JOINT = 0;
			if (!empty($detail_mat_joint)) {
				foreach ($detail_mat_joint as $val => $valx) {
					$val++;
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;

					$berat_bersih 		= $valx['weight'];
					$total_price 		= $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL 	+= $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;

					$FINAL_PRICE_MAT_JOINT	+= $total_price;
					$FINAL_WEIGHT_MAT_JOINT += $berat_bersih;

					$UNIQ 				= $val . '-8888' . $key;
					$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailDefault[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailDefault[$UNIQ]['persen'] 			=  $valx['persen'];
					$ArrDetailDefault[$UNIQ]['persen_add'] 		=  $valx['persen_add'];
					$ArrDetailDefault[$UNIQ]['length'] 			=  $valx['length'];
					$ArrDetailDefault[$UNIQ]['width'] 			=  $valx['width'];
					$ArrDetailDefault[$UNIQ]['qty'] 			=  $valx['qty'];
					$ArrDetailDefault[$UNIQ]['m2'] 				=  $valx['m2'];
					$ArrDetailDefault[$UNIQ]['file_upload'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['pengurangan_additive'] 	= NULL;
					$ArrDetailDefault[$UNIQ]['berat_bersih'] 			= $berat_bersih;
					$ArrDetailDefault[$UNIQ]['price_ref'] 				= $price_ref;
					$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;
				}
			}
			//=================== end mat joint

			//=================== product stdandard
			$GET_PRODUCT_PRICE = get_product_costing();
			$FINAL_PRICE_PRODUCT_STD = 0;
			$FINAL_PRICE_BERAT_STD = 0;
			$FINAL_PRICE_PRODUCT_CUT = 0;
			$FINAL_PRICE_BERAT_CUT = 0;
			$ArrBOMCT = [];
			$ArrQtyBOM = [];
			if (!empty($detail_hi_grid_std)) {
				foreach ($detail_hi_grid_std as $val => $valx) {
					$no_bom_code 		= $valx['code_material'];
					$no_bom_detail 	= $valx['no_bom_detail'];
					$qty 			= $valx['qty'];
					$addUniq 		= 'add' . $no_bom_code;
					$ArrQtyBOM[$addUniq] = $qty;

					$ArrBOMCT[] 	= $addUniq;

					$harga_product = (!empty($GET_PRODUCT_PRICE[$no_bom_code]['price_list'])) ? $GET_PRODUCT_PRICE[$no_bom_code]['price_list'] : 0;
					$berat_product = (!empty($GET_PRODUCT_PRICE[$no_bom_code]['berat_material'])) ? $GET_PRODUCT_PRICE[$no_bom_code]['berat_material'] : 0;

					//price material cutting
					$detail_mat_cutting   = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $no_bom_detail, 'category' => 'material cutting'))->result_array();
					$SUM_PRICE_MAT_CUT = 0;
					$SUM_WEIGHT_MAT_CUT = 0;

					if (!empty($detail_mat_cutting)) {

						foreach ($detail_mat_cutting as $val => $valx) {
							$nox++;
							$id_material 		= $valx['code_material'];

							$price_ref      = (!empty($GET_PRICE_REF[$id_material]['price_ref'])) ? $GET_PRICE_REF[$id_material]['price_ref'] : 0;

							$berat_bersih 		= $valx['weight'];
							$total_price 		= $berat_bersih * $price_ref;

							$UNIQ 				= $val . $nox . $key;
							$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
							$ArrDetailDefault[$UNIQ]['category'] 		=  $addUniq;
							$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
							$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
							$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
							$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
							$ArrDetailDefault[$UNIQ]['persen'] 			= NULL;
							$ArrDetailDefault[$UNIQ]['persen_add'] 		= NULL;
							$ArrDetailDefault[$UNIQ]['length'] 			= NULL;
							$ArrDetailDefault[$UNIQ]['width'] 			= NULL;
							$ArrDetailDefault[$UNIQ]['qty'] 			= NULL;
							$ArrDetailDefault[$UNIQ]['m2'] 				= NULL;
							$ArrDetailDefault[$UNIQ]['file_upload'] 			= NULL;
							$ArrDetailDefault[$UNIQ]['pengurangan_additive'] 	= NULL;
							$ArrDetailDefault[$UNIQ]['berat_bersih'] 			= $berat_bersih;
							$ArrDetailDefault[$UNIQ]['price_ref'] 				= $price_ref;
							$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;

							$SUM_PRICE_MAT_CUT += $total_price;
							$SUM_WEIGHT_MAT_CUT += $berat_bersih;
						}
					}

					$price_plus = ($harga_product * $qty);
					$berat_plus = ($berat_product * $qty);

					$FINAL_PRICE_PRODUCT_CUT  += $SUM_PRICE_MAT_CUT;
					$FINAL_PRICE_BERAT_CUT += $SUM_WEIGHT_MAT_CUT;

					// $ArrUnPrice[$addUniq] 	+= $SUM_PRICE_MAT_CUT;
					// $ArrUnBerat[$addUniq] 	+= $SUM_WEIGHT_MAT_CUT;

					$TOTAL_PRICE_ALL 	+= $price_plus;
					$TOTAL_BERAT_BERSIH += $berat_plus;

					$FINAL_PRICE_PRODUCT_STD 	+= $price_plus;
					$FINAL_PRICE_BERAT_STD 		+= $berat_plus;
				}
			}
			//=================== end product stdandard

			//=================== plat sheet
			$FINAL_PRICE_FLAT_SHEET = 0;
			$FINAL_WEIGHT_FLAT_SHEET = 0;
			if (!empty($detail_mat_flat_sheet)) {
				foreach ($detail_mat_flat_sheet as $val => $valx) {
					$val++;
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;

					$berat_bersih 		= $valx['weight'];
					$total_price 		= $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL 	+= $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;

					$FINAL_PRICE_FLAT_SHEET	+= $total_price;
					$FINAL_WEIGHT_FLAT_SHEET += $berat_bersih;

					$UNIQ 				= $val . '-9999' . $key;
					$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailDefault[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailDefault[$UNIQ]['persen'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['persen_add'] 		= NULL;
					$ArrDetailDefault[$UNIQ]['length'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['width'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['qty'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['m2'] 				= NULL;
					$ArrDetailDefault[$UNIQ]['file_upload'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['pengurangan_additive'] 	= NULL;
					$ArrDetailDefault[$UNIQ]['berat_bersih'] 			= $berat_bersih;
					$ArrDetailDefault[$UNIQ]['price_ref'] 				= $price_ref;
					$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;
				}
			}
			//=================== end flats sheet

			//=================== end plate
			$FINAL_PRICE_END_PLATE = 0;
			$FINAL_WEIGHT_END_PLATE = 0;
			if (!empty($detail_mat_end_plate)) {
				foreach ($detail_mat_end_plate as $val => $valx) {
					$val++;
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;

					$berat_bersih 		= $valx['weight'];
					$total_price 		= $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL 	+= $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;

					$FINAL_PRICE_END_PLATE	+= $total_price;
					$FINAL_WEIGHT_END_PLATE += $berat_bersih;

					$UNIQ 				= $val . '-7777' . $key;
					$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailDefault[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailDefault[$UNIQ]['persen'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['persen_add'] 		= NULL;
					$ArrDetailDefault[$UNIQ]['length'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['width'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['qty'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['m2'] 				= NULL;
					$ArrDetailDefault[$UNIQ]['file_upload'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['pengurangan_additive'] 	= NULL;
					$ArrDetailDefault[$UNIQ]['berat_bersih'] 			= $berat_bersih;
					$ArrDetailDefault[$UNIQ]['price_ref'] 				= $price_ref;
					$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;
				}
			}
			//=================== end end plate

			//=================== chequered
			$FINAL_PRICE_CHE_SHEET = 0;
			$FINAL_WEIGHT_CHE_SHEET = 0;
			if (!empty($detail_mat_chequered)) {
				foreach ($detail_mat_chequered as $val => $valx) {
					$val++;
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;

					$berat_bersih 		= $valx['weight'];
					$total_price 		= $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL 	+= $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;

					$FINAL_PRICE_CHE_SHEET	+= $total_price;
					$FINAL_WEIGHT_CHE_SHEET += $berat_bersih;

					$UNIQ 				= $val . '-6666' . $key;
					$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailDefault[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailDefault[$UNIQ]['persen'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['persen_add'] 		= NULL;
					$ArrDetailDefault[$UNIQ]['length'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['width'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['qty'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['m2'] 				= NULL;
					$ArrDetailDefault[$UNIQ]['file_upload'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['pengurangan_additive'] 	= NULL;
					$ArrDetailDefault[$UNIQ]['berat_bersih'] 			= $berat_bersih;
					$ArrDetailDefault[$UNIQ]['price_ref'] 				= $price_ref;
					$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;
				}
			}
			//=================== end chequered

			//=================== others
			$FINAL_PRICE_OTHERS = 0;
			$FINAL_WEIGHT_OTHERS = 0;
			if (!empty($detail_mat_others)) {
				foreach ($detail_mat_others as $val => $valx) {
					$val++;
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;

					$berat_bersih 		= $valx['weight'];
					$total_price 		= $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL 	+= $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;

					$FINAL_PRICE_OTHERS	+= $total_price;
					$FINAL_WEIGHT_OTHERS += $berat_bersih;

					$UNIQ 				= $val . '-8989' . $key;
					$ArrDetailDefault[$UNIQ]['kode'] 			=  $kode;
					$ArrDetailDefault[$UNIQ]['category'] 		=  $valx['category'];
					$ArrDetailDefault[$UNIQ]['no_bom'] 			=  $valx['no_bom'];
					$ArrDetailDefault[$UNIQ]['no_bom_detail'] 	=  $valx['no_bom_detail'];
					$ArrDetailDefault[$UNIQ]['code_material'] 	=  $valx['code_material'];
					$ArrDetailDefault[$UNIQ]['weight'] 			=  $valx['weight'];
					$ArrDetailDefault[$UNIQ]['persen'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['persen_add'] 		= NULL;
					$ArrDetailDefault[$UNIQ]['length'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['width'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['qty'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['m2'] 				= NULL;
					$ArrDetailDefault[$UNIQ]['file_upload'] 			= NULL;
					$ArrDetailDefault[$UNIQ]['pengurangan_additive'] 	= NULL;
					$ArrDetailDefault[$UNIQ]['berat_bersih'] 			= $berat_bersih;
					$ArrDetailDefault[$UNIQ]['price_ref'] 				= $price_ref;
					$ArrDetailDefault[$UNIQ]['total_price'] 			= $total_price;
				}
			}
			//=================== end others


			$PRICE_TOTAL 	= $TOTAL_PRICE_ALL;
			$WEIGHT_TOTAL 	= $TOTAL_BERAT_BERSIH;


			$code_level4 = $value['id_product'];
			$ArrHeader[$key]['kode'] 				= $kode;
			$ArrHeader[$key]['no_bom'] 				= $no_bom;
			$ArrHeader[$key]['code_lv1'] 			= (!empty($GET_LEVEL4[$code_level4]['code_lv1'])) ? $GET_LEVEL4[$code_level4]['code_lv1'] : NULL;
			$ArrHeader[$key]['product_type'] 		= NULL;
			$ArrHeader[$key]['code_lv2'] 			= (!empty($GET_LEVEL4[$code_level4]['code_lv2'])) ? $GET_LEVEL4[$code_level4]['code_lv2'] : NULL;
			$ArrHeader[$key]['product_category'] 	= NULL;
			$ArrHeader[$key]['code_lv3'] 			= (!empty($GET_LEVEL4[$code_level4]['code_lv3'])) ? $GET_LEVEL4[$code_level4]['code_lv3'] : NULL;
			$ArrHeader[$key]['product_jenis'] 		= NULL;
			$ArrHeader[$key]['code_lv4'] 			= $code_level4;
			$ArrHeader[$key]['product_master'] 		= (!empty($GET_LEVEL4[$code_level4]['nama'])) ? $GET_LEVEL4[$code_level4]['nama'] : NULL;
			$ArrHeader[$key]['berat_material'] 		= $TOTAL_BERAT_BERSIH;

			$ArrHeader[$key]['ass_single_product'] 		= $FINAL_PRICE_PRODUCT_STD;
			$ArrHeader[$key]['ass_cutting'] 			= $FINAL_PRICE_PRODUCT_CUT;
			$ArrHeader[$key]['ass_mat_joint'] 			= $FINAL_PRICE_MAT_JOINT;
			$ArrHeader[$key]['ass_flat_sheet'] 			= $FINAL_PRICE_FLAT_SHEET;
			$ArrHeader[$key]['ass_end_plate'] 			= $FINAL_PRICE_END_PLATE;
			$ArrHeader[$key]['ass_chequered_plate'] 	= $FINAL_PRICE_CHE_SHEET;
			$ArrHeader[$key]['ass_others'] 				= $FINAL_PRICE_OTHERS;
			$ArrHeader[$key]['ass_mat_single_product'] 	= $FINAL_PRICE_BERAT_STD;
			$ArrHeader[$key]['ass_mat_cutting'] 		= $FINAL_PRICE_BERAT_CUT;
			$ArrHeader[$key]['ass_mat_mat_joint'] 		= $FINAL_WEIGHT_MAT_JOINT;
			$ArrHeader[$key]['ass_mat_flat_sheet'] 		= $FINAL_WEIGHT_FLAT_SHEET;
			$ArrHeader[$key]['ass_mat_end_plate'] 		= $FINAL_WEIGHT_END_PLATE;
			$ArrHeader[$key]['ass_mat_chequered_plate'] = $FINAL_WEIGHT_CHE_SHEET;
			$ArrHeader[$key]['ass_mat_others'] 			= $FINAL_WEIGHT_OTHERS;

			$ArrHeader[$key]['update_by'] 			= $id_user;
			$ArrHeader[$key]['update_date'] 		= $dateTime;
			$ArrHeader[$key]['deleted_by'] 			= NULL;
			$ArrHeader[$key]['deleted_date'] 		= NULL;

			$rate_manpower 	= $GET_RATE_MAN_POWER[0]->upah_per_jam_dollar;
			$persen_indirect 	= $GET_RATE_COSTING[3];
			$persen_consumable 	= $GET_RATE_COSTING[6];
			$persen_packing 	= $GET_RATE_COSTING[7];
			$persen_enginnering = $GET_RATE_COSTING[9];
			$persen_foh 		= $GET_RATE_COSTING[10];
			$persen_fin_adm 	= $GET_RATE_COSTING[11];
			$persen_mkt_sales 	= $GET_RATE_COSTING[12];
			$persen_interest 	= $GET_RATE_COSTING[13];
			$persen_profit 		= $GET_RATE_COSTING[14];
			$persen_allowance 	= $GET_RATE_COSTING[18];

			$ArrUnPrice['addJoint'] 			= $FINAL_PRICE_MAT_JOINT;
			$ArrUnPrice['addFlatSheet'] 		= $FINAL_PRICE_FLAT_SHEET;
			$ArrUnPrice['addEndPlate'] 			= $FINAL_PRICE_END_PLATE;
			$ArrUnPrice['addChequeredPlate'] 	= $FINAL_PRICE_CHE_SHEET;
			$ArrUnPrice['addOthers'] 			= $FINAL_PRICE_OTHERS;

			$ArrUnBerat['addJoint'] 			= $FINAL_WEIGHT_MAT_JOINT;
			$ArrUnBerat['addFlatSheet'] 		= $FINAL_WEIGHT_FLAT_SHEET;
			$ArrUnBerat['addEndPlate'] 			= $FINAL_WEIGHT_END_PLATE;
			$ArrUnBerat['addChequeredPlate'] 	= $FINAL_WEIGHT_CHE_SHEET;
			$ArrUnBerat['addOthers'] 			= $FINAL_WEIGHT_OTHERS;

			$ArrCycletime = array_merge($ArrBOMCT, ['addJoint', 'addFlatSheet', 'addEndPlate', 'addChequeredPlate', 'addOthers']);

			$TOT_CS_MAT = $FINAL_PRICE_PRODUCT_STD + $FINAL_PRICE_PRODUCT_CUT;
			$TOT_CS_MP = 0;
			$TOT_CS_MCH = 0;
			foreach ($ArrCycletime as $className) {
				$nomor++;
				# code...
				$keyCtAssembly = $code_level4 . "-" . $no_bom . '-' . $className;
				$keyMchAssembly = $code_level4 . "-" . $className;

				$qty_man_power 		= (!empty($GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['qty_mp'])) ? $GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['qty_mp'] : 0;
				$cycletimeMaster 	= (!empty($GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['ct_cycletime'])) ? $GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['ct_cycletime'] : 0;
				$cycletimeMP		= (!empty($GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['ct_manpower'])) ? $GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['ct_manpower'] : 0;

				$rate_mp 				= 0;
				$rate_cycletime 		= 0;
				if ($cycletimeMaster > 0) {
					$rate_cycletime 	= $cycletimeMaster / 60;
				}
				if ($cycletimeMP > 0) {
					$rate_mp 			= $cycletimeMP / 60;
				}

				$kode_mesin 	= (!empty($GET_MACHINE_ASSEMBLY[$keyMchAssembly])) ? $GET_MACHINE_ASSEMBLY[$keyMchAssembly] : 0;
				$kode_mold 		= (!empty($GET_MOLD_ASSEMBLY[$keyMchAssembly])) ? $GET_MOLD_ASSEMBLY[$keyMchAssembly] : 0;

				$rate_depresiasi 	= (!empty($GET_MACHINE_RATE[$kode_mesin]['biaya_mesin'])) ? $GET_MACHINE_RATE[$kode_mesin]['biaya_mesin'] : 0;
				$rate_mould 		= (!empty($GET_MOLD_RATE[$kode_mold]['biaya_mesin'])) ? $GET_MOLD_RATE[$kode_mold]['biaya_mesin'] : 0;

				//getCT Setting & Produksi
				$id_time		= (!empty($GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['id_time'])) ? $GET_CYCLETIME_ASSEMBLY[$keyCtAssembly]['id_time'] : NULL;

				$getCTProduksi = $this->db
					->select('SUM(a.cycletime) AS total_production')
					->join('cycletime_custom_detail_header b', 'a.id_costcenter=b.id_costcenter', 'left')
					->group_by('a.tipe')
					->get_where('cycletime_custom_detail_detail a', array('a.tipe' => 'production', 'a.id_time' => $id_time, 'b.category' => $className))
					->result_array();
				$getCTSetting = $this->db
					->select('SUM(a.cycletime) AS total_setting')
					->join('cycletime_custom_detail_header b', 'a.id_costcenter=b.id_costcenter', 'left')
					->group_by('a.tipe')
					->get_where('cycletime_custom_detail_detail a', array('a.tipe' => 'setting', 'a.id_time' => $id_time, 'b.category' => $className))
					->result_array();

				$ct_produksi 		= (!empty($getCTProduksi[0]['total_production'])) ? $getCTProduksi[0]['total_production'] : 0;
				$ct_setting 		= (!empty($getCTSetting[0]['total_setting'])) ? $getCTSetting[0]['total_setting'] : 0;
				$ct_moq				= 0;
				if ($ct_setting > 0 and $ct_produksi > 0) {
					$ct_moq 			= $ct_setting * 9 / $ct_produksi;
				}

				$waste_set_resin 	= 0;
				$waste_set_glass 	= 0;
				$bom_moq 			= 0;

				$berat_per_kg 	= (!empty($ArrUnBerat[$className])) ? $ArrUnBerat[$className] : 0;
				//1 material
				$cost_material 	= (!empty($ArrUnPrice[$className])) ? $ArrUnPrice[$className] : 0;
				//# khusus purtution
				$biaya_setting_mp 		= $rate_mp * $rate_manpower;
				$biaya_setting_mesin 	= $rate_mp * $rate_depresiasi;
				$biaya_waste_set_mat	= ($waste_set_resin + $waste_set_glass) * $berat_per_kg;
				$biaya_total_setting	= $biaya_setting_mp + $biaya_setting_mesin + $biaya_waste_set_mat;
				$charge_setting_bom		= ($biaya_total_setting > 0 and $bom_moq > 0) ? $biaya_total_setting / $bom_moq : 0;
				$charge_setting_ct		= ($biaya_total_setting > 0 and $ct_moq > 0) ? $biaya_total_setting / $ct_moq : 0;

				// $biaya_waste_set_mat	= 0;
				// $biaya_total_setting	= 0;
				// $charge_setting_bom		= 0;
				// $charge_setting_ct		= 0;
				//2 man power
				$direct_labour	= $rate_mp * $rate_manpower;
				$indirect 		= $direct_labour * $persen_indirect / 100;
				$cost_man_power = $direct_labour + $indirect;
				//3 machine mould consumable
				$machine 	= $rate_cycletime * $rate_depresiasi;
				$mould 		= $rate_cycletime * $rate_mould;
				$consumable = $cost_material * ($persen_consumable / 100);
				$cost_mesin	= $machine + $mould + $consumable;

				$TOT_CS_MAT += $cost_material;
				$TOT_CS_MP 	+= $cost_man_power;
				$TOT_CS_MCH += $cost_mesin;

				$no_bomReplace 	= str_replace('add', '', $className);
				$harga_product 	= (!empty($GET_PRODUCT_PRICE[$no_bomReplace]['price_list'])) ? $GET_PRODUCT_PRICE[$no_bomReplace]['price_list'] : 0;
				$qty_product 	= (!empty($ArrQtyBOM[$className])) ? $ArrQtyBOM[$className] : 0;

				$UNIQ2 = $nomor;
				$ArrDetailAssembly[$UNIQ2]['kode'] 					= $kode;
				$ArrDetailAssembly[$UNIQ2]['category'] 				= $className;
				$ArrDetailAssembly[$UNIQ2]['product_price'] 		= $harga_product;
				$ArrDetailAssembly[$UNIQ2]['product_qty'] 			= $qty_product;
				$ArrDetailAssembly[$UNIQ2]['ct_setting'] 			= $ct_setting;
				$ArrDetailAssembly[$UNIQ2]['ct_produksi'] 			= $ct_produksi;
				$ArrDetailAssembly[$UNIQ2]['ct_moq'] 				= $ct_moq;
				$ArrDetailAssembly[$UNIQ2]['berat_per_kg'] 			= $berat_per_kg;
				$ArrDetailAssembly[$UNIQ2]['waste_set_resin'] 		= $waste_set_resin;
				$ArrDetailAssembly[$UNIQ2]['waste_set_glass'] 		= $waste_set_glass;
				$ArrDetailAssembly[$UNIQ2]['bom_moq'] 				= $bom_moq;
				$ArrDetailAssembly[$UNIQ2]['biaya_setting_mp'] 		= $biaya_setting_mp;
				$ArrDetailAssembly[$UNIQ2]['biaya_setting_mesin'] 	= $biaya_setting_mesin;
				$ArrDetailAssembly[$UNIQ2]['biaya_waste_set_mat'] 	= $biaya_waste_set_mat;
				$ArrDetailAssembly[$UNIQ2]['biaya_total_setting'] 	= $biaya_total_setting;
				$ArrDetailAssembly[$UNIQ2]['charge_setting_bom'] 	= $charge_setting_bom;
				$ArrDetailAssembly[$UNIQ2]['charge_setting_ct'] 	= $charge_setting_ct;
				$ArrDetailAssembly[$UNIQ2]['qty_man_power'] 		= $qty_man_power;

				$ArrDetailAssembly[$UNIQ2]['rate_cycletime'] 		= $rate_mp;
				$ArrDetailAssembly[$UNIQ2]['rate_cycletime_machine'] = $rate_cycletime;
				$ArrDetailAssembly[$UNIQ2]['rate_man_power_usd'] 	= $rate_manpower;
				$ArrDetailAssembly[$UNIQ2]['rate_man_power_idr'] 	= $GET_RATE_MAN_POWER[0]->upah_per_jam;
				$ArrDetailAssembly[$UNIQ2]['rate_depresiasi'] 		= $rate_depresiasi;
				$ArrDetailAssembly[$UNIQ2]['rate_mould'] 			= $rate_mould;
				$ArrDetailAssembly[$UNIQ2]['cost_material'] 		= $cost_material;
				$ArrDetailAssembly[$UNIQ2]['price_material'] 		= $cost_material;
			}

			$TOTAL_COST_SUM = ($TOT_CS_MAT + $TOT_CS_MP + $TOT_CS_MCH);
			// echo "Tot:".$TOTAL_COST_SUM;
			// exit;
			//4 logistik
			//getUpdate Shipping
			$newLogistik 		= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
			$stsUpdateLogistik 	= (!empty($newLogistik[0]['sts_logistik'])) ? $newLogistik[0]['sts_logistik'] : NULL;
			$stsUpdatePacking 	= (!empty($newLogistik[0]['cost_packing'])) ? $newLogistik[0]['cost_packing'] : 0;
			$stsUpdateTransport = (!empty($newLogistik[0]['cost_transport'])) ? $newLogistik[0]['cost_transport'] : 0;
			if ($stsUpdateLogistik == 'Y') {
				$packing 		= $stsUpdatePacking;
				$transport		= $stsUpdateTransport;
			} else {
				$packing 		= $TOTAL_COST_SUM * $persen_packing / 100;
				$transport		= 0;
			}
			$cost_logistik 	= $packing + $transport;

			$cost_enginnering 	= $TOTAL_COST_SUM * $persen_enginnering / 100;
			$cost_foh 			= ($TOTAL_COST_SUM + $cost_logistik + $cost_enginnering) * $persen_foh / 100;
			$cost_fin_adm 		= ($TOTAL_COST_SUM + $cost_logistik + $cost_enginnering) * $persen_fin_adm / 100;
			$cost_mkt_sales 	= ($TOTAL_COST_SUM + $cost_logistik + $cost_enginnering) * $persen_mkt_sales / 100;
			$cost_interest 		= ($TOTAL_COST_SUM + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales) * $persen_interest / 100;
			$cost_profit 		= ($TOTAL_COST_SUM + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales + $cost_interest) * $persen_profit / 100;
			$bottom_price 		= ($TOTAL_COST_SUM + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales + $cost_interest + $cost_profit);
			$factor_kompetitif	= 1;
			$bottom_selling		= $bottom_price * $factor_kompetitif;
			$nego_allowance		= $bottom_selling * ($persen_allowance / 100);
			$price_final		= $bottom_selling + $nego_allowance;


			$ArrHeader[$key]['cost_product_assembly'] 	= $TOTAL_COST_SUM;
			$ArrHeader[$key]['cost_persen_indirect'] 	= $persen_indirect;
			$ArrHeader[$key]['cost_persen_consumable'] 	= $persen_consumable;
			$ArrHeader[$key]['cost_persen_packing'] 	= $persen_packing;
			$ArrHeader[$key]['cost_persen_enginnering']	= $persen_enginnering;
			$ArrHeader[$key]['cost_persen_foh'] 		= $persen_foh;
			$ArrHeader[$key]['cost_persen_fin_adm'] 	= $persen_fin_adm;
			$ArrHeader[$key]['cost_persen_mkt_sales'] 	= $persen_mkt_sales;
			$ArrHeader[$key]['cost_persen_interest'] 	= $persen_interest;
			$ArrHeader[$key]['cost_persen_profit'] 		= $persen_profit;
			$ArrHeader[$key]['cost_bottom_price'] 		= $bottom_price;
			$ArrHeader[$key]['cost_factor_kompetitif']	= $factor_kompetitif;
			$ArrHeader[$key]['cost_nego_allowance'] 	= $persen_allowance;
			$ArrHeader[$key]['cost_price_final'] 		= $price_final;

			$ArrHeader[$key]['price_material'] 			= $TOT_CS_MAT;
			$ArrHeader[$key]['price_man_power'] 		= $TOT_CS_MP;
			$ArrHeader[$key]['price_machine'] 			= $TOT_CS_MCH;
			$ArrHeader[$key]['price_total'] 			= $price_final;
			$ArrHeader[$key]['cost_direct_labout'] 		= $direct_labour;
			$ArrHeader[$key]['cost_indirect'] 			= $indirect;
			$ArrHeader[$key]['cost_machine'] 			= $machine;
			$ArrHeader[$key]['cost_mould'] 				= $mould;
			$ArrHeader[$key]['cost_consumable'] 		= $consumable;
			$ArrHeader[$key]['cost_packing'] 			= $packing;
			$ArrHeader[$key]['cost_transport'] 			= $transport;
			$ArrHeader[$key]['cost_enginnering'] 		= $cost_enginnering;
			$ArrHeader[$key]['cost_foh'] 				= $cost_foh;
			$ArrHeader[$key]['cost_fin_adm'] 			= $cost_fin_adm;
			$ArrHeader[$key]['cost_mkt_sales'] 			= $cost_mkt_sales;
			$ArrHeader[$key]['cost_interest'] 			= $cost_interest;
			$ArrHeader[$key]['cost_profit'] 			= $cost_profit;
			$ArrHeader[$key]['cost_bottom_selling'] 	= $bottom_selling;
			$ArrHeader[$key]['cost_allowance'] 			= $nego_allowance;

			$GET_PRODUCT_COSTING = get_product_costing();

			$ArrHeader[$key]['pengajuan_price_list'] 	= (!empty($GET_PRODUCT_COSTING[$no_bom]['pengajuan_price_list'])) ? $GET_PRODUCT_COSTING[$no_bom]['pengajuan_price_list'] : NULL;
			$ArrHeader[$key]['price_list'] 				= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_list'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_list'] : NULL;
			$ArrHeader[$key]['price_list_idr'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_list_idr'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_list_idr'] : NULL;
			$ArrHeader[$key]['kurs'] 					= (!empty($GET_PRODUCT_COSTING[$no_bom]['kurs'])) ? $GET_PRODUCT_COSTING[$no_bom]['kurs'] : NULL;
			$ArrHeader[$key]['price_idr'] 				= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_idr'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_idr'] : NULL;
			$ArrHeader[$key]['price_persen_orindo'] 	= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_persen_orindo'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_persen_orindo'] : NULL;
			$ArrHeader[$key]['price_list_idr_orindo'] 	= (!empty($GET_PRODUCT_COSTING[$no_bom]['price_list_idr_orindo'])) ? $GET_PRODUCT_COSTING[$no_bom]['price_list_idr_orindo'] : NULL;
			$ArrHeader[$key]['status'] 					= (!empty($GET_PRODUCT_COSTING[$no_bom]['status'])) ? $GET_PRODUCT_COSTING[$no_bom]['status'] : 'N';
			$ArrHeader[$key]['status_by'] 				= (!empty($GET_PRODUCT_COSTING[$no_bom]['status_by'])) ? $GET_PRODUCT_COSTING[$no_bom]['status_by'] : NULL;
			$ArrHeader[$key]['status_date'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['status_date'])) ? $GET_PRODUCT_COSTING[$no_bom]['status_date'] : NULL;
			$ArrHeader[$key]['reason'] 					= (!empty($GET_PRODUCT_COSTING[$no_bom]['reason'])) ? $GET_PRODUCT_COSTING[$no_bom]['reason'] : NULL;
			$ArrHeader[$key]['sts_logistik'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['sts_logistik'])) ? $GET_PRODUCT_COSTING[$no_bom]['sts_logistik'] : NULL;
			$ArrHeader[$key]['logistik_by'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['logistik_by'])) ? $GET_PRODUCT_COSTING[$no_bom]['logistik_by'] : NULL;
			$ArrHeader[$key]['logistik_date'] 			= (!empty($GET_PRODUCT_COSTING[$no_bom]['logistik_date'])) ? $GET_PRODUCT_COSTING[$no_bom]['logistik_date'] : NULL;

			$ukuran_jadi_price = $this->db->get_where('product_price_ukuran_jadi', array('no_bom' => $no_bom, 'deleted_date' => null))->result_array();
			if (!empty($ukuran_jadi_price)) {
				foreach ($ukuran_jadi_price as $ukj => $value) {
					$keyUkt = $key . '-' . $ukj;
					$ArrDetail[$keyUkt]['id_ukuran'] 		= $value['id'];
					$ArrDetail[$keyUkt]['no_bom'] 			= $no_bom;
					$ArrDetail[$keyUkt]['kode'] 			= $kode;
					$ArrDetail[$keyUkt]['created_by'] 		= $id_user;
					$ArrDetail[$keyUkt]['created_date'] 	= $dateTime;
					$ArrDetail[$keyUkt]['qty'] 				= str_replace(',', '', $value['qty']);
					$ArrDetail[$keyUkt]['width'] 			= str_replace(',', '', $value['width']);
					$ArrDetail[$keyUkt]['length'] 			= str_replace(',', '', $value['length']);
					$ArrDetail[$keyUkt]['price_unit'] 		= str_replace(',', '', $value['price_unit']);
					$ArrDetail[$keyUkt]['total_price'] 		= str_replace(',', '', $value['total_price']);
				}
			}
		}




		// echo '<pre>';
		// print_r($ArrHeader);
		// print_r($ArrDetailDefault);
		// print_r($ArrDetailAdditive);
		// print_r($ArrDetailAdditiveCustom);
		// print_r($ArrDetailAssembly);
		// exit;

		$ArrUpdate = [
			'deleted_by' => $id_user,
			'deleted_date' => $dateTime
		];

		$this->db->trans_start();
		if (!empty($ArrHeader)) {
			$this->db->where('deleted_date', NULL);
			$this->db->like('no_bom', 'BOC');
			$this->db->update('product_price', $ArrUpdate);

			$this->db->insert_batch('product_price', $ArrHeader);
		}
		if (!empty($ArrDetailDefault)) {
			$this->db->insert_batch('product_price_bom_detail', $ArrDetailDefault);
		}
		if (!empty($ArrDetailAssembly)) {
			$this->db->insert_batch('product_price_assembly', $ArrDetailAssembly);
		}

		if (!empty($ArrDetail)) {
			$this->db->where('deleted_date', NULL);
			$this->db->like('no_bom', 'BOC');
			$this->db->update('product_price_ukuran_jadi', $ArrUpdate);

			$this->db->insert_batch('product_price_ukuran_jadi', $ArrDetail);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Failed process data!',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success process data!',
				'status'	=> 1
			);
			history('Update product price');
		}
		echo json_encode($status);
	}

	public function detail_machine_mold()
	{
		$id_product = $this->input->post('id_product');
		$tanda 		= $this->input->post('tanda');
		$cost 		= $this->input->post('cost');
		$no_bom 		= $this->input->post('no_bom');
		$header = $this->db->get_where('cycletime_header', array('id_product' => $id_product, 'no_bom' => $no_bom, 'deleted_date' => NULL))->result();
		// print_r($header);
		$title = ($tanda == 'machine') ? 'Machine' : 'Mold';
		$data = [
			'id_product' => $id_product,
			'header' => $header,
			'tanda' => $tanda,
			'title' => $title,
			'cost' => $cost,
		];
		$this->template->render('detail_machine_mold', $data);
	}

	public function detail_machine_mold_ass()
	{
		$id_product = $this->input->post('id_product');
		$tanda 		= $this->input->post('tanda');
		$cost 		= $this->input->post('cost');
		$no_bom 	= $this->input->post('no_bom');
		$category 	= $this->input->post('category');
		$header = $this->db->get_where('cycletime_custom_header', array('id_product' => $id_product, 'no_bom' => $no_bom, 'deleted_date' => NULL))->result();
		// print_r($header);
		$title = ($tanda == 'machine') ? 'Machine' : 'Mold';
		$data = [
			'id_product' => $id_product,
			'header' => $header,
			'tanda' => $tanda,
			'title' => $title,
			'cost' => $cost,
			'category' => $category,
		];
		$this->template->render('detail_machine_mold_ass', $data);
	}

	public function pengajuan_costing()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->uri->segment(3);
		$product_price 		= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));

		$data = [
			'no_bom' => $no_bom,
			'dataList' => $costing_rate,
			'product_price' => $product_price,
			'header' => $header,
			'detail' => $detail,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'product' => $product,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories(),
			'GET_PRICE_REF' => get_price_ref()
		];
		$this->template->title('Pengajuan Price List Costing');
		$this->template->render('pengajuan_costing', $data);
	}

	public function ajukan_product_price()
	{
		$session = $this->session->userdata('app_session');
		$dateTime 	= date('Y-m-d H:i:s');
		$id_user	= $session['id_user'];

		$data 	= $this->input->post();
		$id 	= $data['id'];
		$no_bom 	= $data['no_bom'];
		$kode 	= $data['kode'];
		$pengajuan_price_list 	= str_replace(',', '', $data['pengajuan_price_list']);
		$kurs 	= str_replace(',', '', $data['kurs']);
		$price_idr 	= str_replace(',', '', $data['price_idr']);

		$total_price_uj 	= (!empty($data['total_price_uj'])) ? str_replace(',', '', $data['total_price_uj']) : 0;
		$total_idr_uj 		= (!empty($data['total_idr_uj'])) ? str_replace(',', '', $data['total_idr_uj']) : 0;
		$selisih_uj 		= (!empty($data['selisih_uj'])) ? str_replace(',', '', $data['selisih_uj']) : 0;

		$ArrUpdate = [
			'pengajuan_price_list' => $pengajuan_price_list,
			'kurs' => $kurs,
			'price_idr' => $price_idr,
			'status' => 'WA',
			'status_by' => $id_user,
			'status_date' => $dateTime,
			'total_price_uj' => $total_price_uj,
			'total_idr_uj' => $total_idr_uj,
			'selisih_uj' => $selisih_uj,
		];

		$ukuran_jadi_price = (!empty($data['ukuran_jadi_price'])) ? $data['ukuran_jadi_price'] : [];
		$ArrDetail = [];
		if (!empty($ukuran_jadi_price)) {
			foreach ($ukuran_jadi_price as $key => $value) {
				$ArrDetail[$key]['id_ukuran'] 		= $value['id'];
				$ArrDetail[$key]['no_bom'] 			= $no_bom;
				$ArrDetail[$key]['kode'] 			= $kode;
				$ArrDetail[$key]['created_by'] 			= $id_user;
				$ArrDetail[$key]['created_date'] 			= $dateTime;
				$ArrDetail[$key]['qty'] 			= str_replace(',', '', $value['qty']);
				$ArrDetail[$key]['width'] 			= str_replace(',', '', $value['width']);
				$ArrDetail[$key]['length'] 			= str_replace(',', '', $value['length']);
				$ArrDetail[$key]['price_unit'] 		= str_replace(',', '', $value['price_unit']);
				$ArrDetail[$key]['total_price'] 	= str_replace(',', '', $value['total_price']);
			}
		}

		$ArrDelete = [
			'deleted_by' => $id_user,
			'deleted_date' => $dateTime
		];

		// print_r($ArrDetail);
		// exit;
		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('product_price', $ArrUpdate);

		$this->db->where('no_bom', $no_bom);
		$this->db->update('product_price_ukuran_jadi', $ArrDelete);

		if (!empty($ArrDetail)) {
			$this->db->insert_batch('product_price_ukuran_jadi', $ArrDetail);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Failed process data!',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success process data!',
				'status'	=> 1
			);
			history('Mengajukan price list costing id: ' . $id);
		}
		echo json_encode($status);
	}

	public function download_excel()
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		$this->load->library("PHPExcel");

		$objPHPExcel    = new PHPExcel();

		$whiteCenterBold    = whiteCenterBold();
		$whiteRightBold    	= whiteRightBold();
		$whiteCenter    	  = whiteCenter();
		$mainTitle    		  = mainTitle();
		$tableHeader    	  = tableHeader();
		$tableBodyCenter    = tableBodyCenter();
		$tableBodyLeft    	= tableBodyLeft();
		$tableBodyRight    	= tableBodyRight();

		$Arr_Bulan  = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet      = $objPHPExcel->getActiveSheet();

		$dateX	= date('Y-m-d H:i:s');
		$Row        = 1;
		$NewRow     = $Row + 1;
		$Col_Akhir  = $Cols = getColsChar(3);
		$sheet->setCellValue('A' . $Row, "PRODUCT COSTING");
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow = $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->getColumnDimension("A")->setAutoSize(true);
		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

		$sheet->getColumnDimension("B")->setAutoSize(true);
		$sheet->setCellValue('B' . $NewRow, 'PRODUCT TYPE');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

		$sheet->getColumnDimension("C")->setAutoSize(true);
		$sheet->setCellValue('C' . $NewRow, 'PRODUCT MASTER');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

		$sheet->getColumnDimension("D")->setAutoSize(true);
		$sheet->setCellValue('D' . $NewRow, 'VARIANT');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

		$sheet->getColumnDimension("E")->setAutoSize(true);
		$sheet->setCellValue('E' . $NewRow, 'TOTAL WEIGHT');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

		$sheet->getColumnDimension("F")->setAutoSize(true);
		$sheet->setCellValue('F' . $NewRow, 'PRICE LIST (USD)');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);

		$sheet->getColumnDimension("G")->setAutoSize(true);
		$sheet->setCellValue('G' . $NewRow, 'PRICE LIST (IDR)');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);

		$sheet->getColumnDimension("H")->setAutoSize(true);
		$sheet->setCellValue('H' . $NewRow, 'PRICE DIAJUKAN (USD)');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);

		$sheet->getColumnDimension("I")->setAutoSize(true);
		$sheet->setCellValue('i' . $NewRow, 'PRICE DIAJUKAN (IDR)');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);

		$sheet->getColumnDimension("J")->setAutoSize(true);
		$sheet->setCellValue('J' . $NewRow, 'STATUS');
		$sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);

		$sheet->getColumnDimension("K")->setAutoSize(true);
		$sheet->setCellValue('K' . $NewRow, 'REASON');
		$sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);

		$sheet->getColumnDimension("L")->setAutoSize(true);
		$sheet->setCellValue('L' . $NewRow, 'NO BOM');
		$sheet->getStyle('L' . $NewRow . ':L' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('L' . $NewRow . ':L' . $NextRow);

		// $sheet ->getColumnDimension("M")->setAutoSize(true);
		// $sheet->setCellValue('M'.$NewRow, 'MINIMUM STOK');
		// $sheet->getStyle('M'.$NewRow.':M'.$NextRow)->applyFromArray($whiteCenterBold);
		// $sheet->mergeCells('M'.$NewRow.':M'.$NextRow);

		// $sheet ->getColumnDimension("N")->setAutoSize(true);
		// $sheet->setCellValue('N'.$NewRow, 'Qty');
		// $sheet->getStyle('N'.$NewRow.':N'.$NextRow)->applyFromArray($whiteCenterBold);
		// $sheet->mergeCells('N'.$NewRow.':N'.$NextRow);

		// $sheet ->getColumnDimension("O")->setAutoSize(true);
		// $sheet->setCellValue('O'.$NewRow, 'Qty');
		// $sheet->getStyle('O'.$NewRow.':O'.$NextRow)->applyFromArray($whiteCenterBold);
		// $sheet->mergeCells('O'.$NewRow.':O'.$NextRow);

		// $sheet ->getColumnDimension("P")->setAutoSize(true);
		// $sheet->setCellValue('P'.$NewRow, 'Qty');
		// $sheet->getStyle('P'.$NewRow.':P'.$NextRow)->applyFromArray($whiteCenterBold);
		// $sheet->mergeCells('P'.$NewRow.':P'.$NextRow);

		// $sheet ->getColumnDimension("Q")->setAutoSize(true);
		// $sheet->setCellValue('Q'.$NewRow, 'Qty');
		// $sheet->getStyle('Q'.$NewRow.':Q'.$NextRow)->applyFromArray($whiteCenterBold);
		// $sheet->mergeCells('Q'.$NewRow.':Q'.$NextRow);

		$SQL = "SELECT
					a.*,
					b.nama AS nama_level4,
					d.variant_product,
					c.nama AS nama_level1
				FROM
					product_price a 
					LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
					LEFT JOIN new_inventory_1 c ON b.code_lv1=c.code_lv1
					LEFT JOIN bom_header d ON a.no_bom=d.no_bom
				WHERE a.deleted_date IS NULL ";

		$dataResult   = $this->db->query($SQL)->result_array();

		if ($dataResult) {
			$awal_row   = $NextRow;
			$no = 0;
			foreach ($dataResult as $key => $vals) {
				$no++;
				$awal_row++;
				$awal_col   = 0;

				$awal_col++;
				$no   = $no;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $no);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nama_level1   = $vals['nama_level1'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nama_level1);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nama_level4   = $vals['nama_level4'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nama_level4);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$variant_product   = $vals['variant_product'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $variant_product);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$berat_material   = $vals['berat_material'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $berat_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$price_list   = $vals['price_list'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $price_list);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$price_list_idr   = $vals['price_list_idr'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $price_list_idr);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$pengajuan_price_list   = $vals['pengajuan_price_list'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $pengajuan_price_list);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$price_idr   = $vals['price_idr'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $price_idr);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$status = 'Waiting Submission';
				if ($vals['status'] == 'WA') {
					$status = 'Waiting Approval';
				}
				if ($vals['status'] == 'A') {
					$status = 'Approved';
				}
				if ($vals['status'] == 'R') {
					$status = 'Rejected';
				}

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $status);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);


				$awal_col++;
				$reason   = $vals['reason'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $reason);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$no_bom   = $vals['no_bom'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $no_bom);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		$sheet->setTitle('Product Costing');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter      = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="product-costing.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function download($no_bom)
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		$this->load->library("PHPExcel");

		$objPHPExcel    = new PHPExcel();

		$whiteCenterBold    = whiteCenterBold();
		$whiteRightBold    	= whiteRightBold();
		$whiteCenter    	  = whiteCenter();
		$mainTitle    		  = mainTitle();
		$tableHeader    	  = tableHeader();
		$tableBodyCenter    = tableBodyCenter();
		$tableBodyLeft    	= tableBodyLeft();
		$tableBodyRight    	= tableBodyRight();

		$Arr_Bulan  = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet      = $objPHPExcel->getActiveSheet();

		$product_price 		= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));

		$no_bom = $no_bom;
		$dataList = $costing_rate;
		$product_price = $product_price;
		$header = $header;
		$detail = $detail;
		$detail_additive = $detail_additive;
		$detail_topping = $detail_topping;
		$detail_accessories = $detail_accessories;
		$detail_flat_sheet = $detail_flat_sheet;
		$detail_end_plate = $detail_end_plate;
		$detail_ukuran_jadi = $detail_ukuran_jadi;
		$product = $product;
		$GET_LEVEL4 = get_inventory_lv4();
		$GET_LEVEL1 = get_list_inventory_lv1('product');
		$GET_ACC = get_accessories();
		$GET_PRICE_REF = get_price_ref();

		$nameLv4 = (!empty($GET_LEVEL4[$header[0]->id_product]['nama'])) ? $GET_LEVEL4[$header[0]->id_product]['nama'] : '';
		$codeLv1 = (!empty($GET_LEVEL4[$header[0]->id_product]['code_lv1'])) ? $GET_LEVEL4[$header[0]->id_product]['code_lv1'] : '';
		$nameLv1 = (!empty($GET_LEVEL1[$codeLv1]['nama'])) ? $GET_LEVEL1[$codeLv1]['nama'] : '';

		$nameLv4Label = strtolower(str_replace(' ', '-', $nameLv4));

		$dateX	= date('Y-m-d H:i:s');
		$Row        = 1;
		$NewRow     = $Row + 1;
		$Col_Akhir  = $Cols = getColsChar(5);
		$sheet->setCellValue('A' . $Row, "DETAIL COSTING");
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow = $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->getColumnDimension("A")->setAutoSize(true);
		$sheet->setCellValue('A' . $NewRow, 'Product Type');
		$sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

		$sheet->getColumnDimension("C")->setAutoSize(true);
		$sheet->setCellValue('C' . $NewRow, $nameLv1);
		$sheet->getStyle('C' . $NewRow . ':E' . $NextRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('C' . $NewRow . ':E' . $NextRow);

		$NewRow = $NewRow + 1;
		$NextRow = $NewRow;

		$sheet->getColumnDimension("A")->setAutoSize(true);
		$sheet->setCellValue('A' . $NewRow, 'Product Master');
		$sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

		$sheet->getColumnDimension("C")->setAutoSize(true);
		$sheet->setCellValue('C' . $NewRow, $nameLv4);
		$sheet->getStyle('C' . $NewRow . ':E' . $NextRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('C' . $NewRow . ':E' . $NextRow);

		$NewRow = $NewRow + 1;
		$NextRow = $NewRow;

		$sheet->getColumnDimension("A")->setAutoSize(true);
		$sheet->setCellValue('A' . $NewRow, 'Variant');
		$sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

		$sheet->getColumnDimension("C")->setAutoSize(true);
		$sheet->setCellValue('C' . $NewRow, $header[0]->variant_product);
		$sheet->getStyle('C' . $NewRow . ':E' . $NextRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('C' . $NewRow . ':E' . $NextRow);

		$NewRow = $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->getColumnDimension("A")->setAutoSize(true);
		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

		$sheet->getColumnDimension("B")->setAutoSize(true);
		$sheet->setCellValue('B' . $NewRow, 'ELEMENT COSTING');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

		$sheet->getColumnDimension("C")->setAutoSize(true);
		$sheet->setCellValue('C' . $NewRow, 'RATE');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

		$sheet->getColumnDimension("D")->setAutoSize(true);
		$sheet->setCellValue('D' . $NewRow, 'PRICE');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

		$sheet->getColumnDimension("E")->setAutoSize(true);
		$sheet->setCellValue('E' . $NewRow, 'KETERANGAN');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

		$awal_row   = $NextRow;
		$no = 0;
		foreach ($dataList as $key => $value) {
			if ($value['judul'] == 'Material') {
				$no++;
				$awal_row++;
				$awal_col   = 0;

				$awal_col++;
				$no   = 1;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $no);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$element_costing   = $value['element_costing'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $element_costing);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$none   = '';
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $none);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$price_material   = $product_price[0]['price_material'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $price_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$keterangan   = $value['keterangan'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $keterangan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		$no++;
		$awal_row++;
		$awal_col   = 0;

		$awal_col++;
		$no   = 2;
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $no);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$element_costing   = 'Man Power';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $element_costing);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		foreach ($dataList as $key => $value) {
			if ($value['judul'] == 'Manpower') {
				$no++;
				$awal_row++;
				$awal_col   = 0;

				if ($value['code'] == '2') {
					$rate 	    = number_format($product_price[0]['rate_cycletime'], 2) . ' x ' . number_format($product_price[0]['rate_man_power_usd'], 2);
					$man_power	= $product_price[0]['cost_direct_labout'];
					$detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='manpower' data-cost='" . $product_price[0]['rate_man_power_usd'] . "' data-id_product='" . $header[0]->id_product . "' >Detail</span>";
				}
				if ($value['code'] == '3') {
					$rate 	    = number_format($product_price[0]['cost_persen_indirect'], 2) . " %";
					$man_power 	= $product_price[0]['cost_indirect'];
					$detRate = "";
				}

				$awal_col++;
				$none   = '';
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $none);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$element_costing   = $value['element_costing'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $element_costing);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $rate);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $man_power);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$keterangan   = $value['keterangan'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $keterangan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		$no++;
		$awal_row++;
		$awal_col   = 0;

		$awal_col++;
		$no   = 3;
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $no);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$element_costing   = 'Mesin, cetakan, consumable';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $element_costing);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		foreach ($dataList as $key => $value) {
			if ($value['judul'] == 'Mesin, cetakan, consumable') {
				$no++;
				$awal_row++;
				$awal_col   = 0;

				if ($value['code'] == '4') {
					$rate 	    = number_format($product_price[0]['rate_cycletime_machine'], 2) . ' x ' . number_format($product_price[0]['rate_depresiasi'], 2);
					$cost_machine	= $product_price[0]['cost_machine'];
					$detRate = "<span class='text-primary btncursor detailRate' id='btnShowMachine' data-tanda='machine' data-cost='" . $product_price[0]['rate_depresiasi'] . "' data-id_product='" . $header[0]->id_product . "' >Detail</span>";
				}
				if ($value['code'] == '5') {
					$rate 	    = number_format($product_price[0]['rate_cycletime_machine'], 2) . ' x ' . number_format($product_price[0]['rate_mould'], 2);
					$cost_machine 	= $product_price[0]['cost_mould'];
					$detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='mold' data-cost='" . $product_price[0]['rate_mould'] . "' data-id_product='" . $header[0]->id_product . "' >Detail</span>";
				}
				if ($value['code'] == '6') {
					$rate 	    = number_format($product_price[0]['cost_persen_consumable'], 2) . " %";
					$cost_machine 	= $product_price[0]['cost_consumable'];
					$detRate = "";
				}

				$awal_col++;
				$none   = '';
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $none);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$element_costing   = $value['element_costing'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $element_costing);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $rate);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $cost_machine);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$keterangan   = $value['keterangan'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $keterangan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		$no++;
		$awal_row++;
		$awal_col   = 0;

		$awal_col++;
		$no   = 4;
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $no);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$element_costing   = 'Logistik';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $element_costing);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		$awal_col++;
		$none   = '';
		$Cols       = getColsChar($awal_col);
		$sheet->setCellValue($Cols . $awal_row, $none);
		$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

		foreach ($dataList as $key => $value) {
			if ($value['judul'] == 'Logistik') {
				$no++;
				$awal_row++;
				$awal_col   = 0;

				if ($value['code'] == '7') {
					$rate 	    = number_format($product_price[0]['cost_persen_packing'], 2) . " %";
					$cost_packing	= $product_price[0]['cost_packing'];
				}
				if ($value['code'] == '8') {
					$rate 	    = '';
					$cost_packing 	= $product_price[0]['cost_transport'];
				}

				$awal_col++;
				$none   = '';
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $none);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$element_costing   = $value['element_costing'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $element_costing);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $rate);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $cost_packing);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$keterangan   = $value['keterangan'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $keterangan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		$no = 4;
		foreach ($dataList as $key => $value) {
			if ($value['judul'] == 'Lainnya') {
				$no++;
				$awal_row++;
				$awal_col   = 0;

				if ($value['code'] == '9') {
					$rate 	    = number_format($product_price[0]['cost_persen_enginnering'], 2) . " %";
					$cost   	= $product_price[0]['cost_enginnering'];
				}
				if ($value['code'] == '10') {
					$rate 	    = number_format($product_price[0]['cost_persen_foh'], 2) . " %";
					$cost    	= $product_price[0]['cost_foh'];
				}
				if ($value['code'] == '11') {
					$rate 	    = number_format($product_price[0]['cost_persen_fin_adm'], 2) . " %";
					$cost   	= $product_price[0]['cost_fin_adm'];
				}
				if ($value['code'] == '12') {
					$rate 	    = number_format($product_price[0]['cost_persen_mkt_sales'], 2) . " %";
					$cost    	= $product_price[0]['cost_mkt_sales'];
				}
				if ($value['code'] == '13') {
					$rate 	    = number_format($product_price[0]['cost_persen_interest'], 2) . " %";
					$cost   	= $product_price[0]['cost_interest'];
				}
				if ($value['code'] == '14') {
					$rate 	    = number_format($product_price[0]['cost_persen_profit'], 2) . " %";
					$cost    	= $product_price[0]['cost_profit'];
				}
				if ($value['code'] == '15') {
					$rate 	    = '';
					$cost   	= $product_price[0]['cost_bottom_price'];
				}
				if ($value['code'] == '16') {
					$rate 	    = number_format($product_price[0]['cost_factor_kompetitif'], 2);
					$cost    	= 0;
				}
				if ($value['code'] == '17') {
					$rate 	    = '';
					$cost   	= $product_price[0]['cost_bottom_selling'];
				}
				if ($value['code'] == '18') {
					$rate 	    = number_format($product_price[0]['cost_nego_allowance'], 2) . " %";
					$cost    	= $product_price[0]['cost_allowance'];
				}
				if ($value['code'] == '19') {
					$rate 	    = '';
					$cost    	= $product_price[0]['cost_price_final'];
				}

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $no);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$element_costing   = $value['element_costing'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $element_costing);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $rate);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				if ($value['code'] == '16') {
					$awal_col++;
					$Cols       = getColsChar($awal_col);
					$sheet->setCellValue($Cols . $awal_row, '');
					$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);
				} else {
					$awal_col++;
					$Cols       = getColsChar($awal_col);
					$sheet->setCellValue($Cols . $awal_row, $cost);
					$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);
				}

				$awal_col++;
				$keterangan   = $value['keterangan'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $keterangan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		//detail material
		$NewRow = 30;
		$NewRow = $NewRow + 2;
		$NextRow = $NewRow;

		// echo $NextRow;

		$sheet->getColumnDimension("A")->setAutoSize(true);
		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

		$sheet->getColumnDimension("B")->setAutoSize(true);
		$sheet->setCellValue('B' . $NewRow, 'MATERIAL TYPE');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

		$sheet->getColumnDimension("C")->setAutoSize(true);
		$sheet->setCellValue('C' . $NewRow, 'MATERIAL CATEGORY');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

		$sheet->getColumnDimension("D")->setAutoSize(true);
		$sheet->setCellValue('D' . $NewRow, 'MATERIAL JENIS');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

		$sheet->getColumnDimension("E")->setAutoSize(true);
		$sheet->setCellValue('E' . $NewRow, 'MATERIAL NAME');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

		$sheet->getColumnDimension("F")->setAutoSize(true);
		$sheet->setCellValue('F' . $NewRow, 'BERAT');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);

		$sheet->getColumnDimension("G")->setAutoSize(true);
		$sheet->setCellValue('G' . $NewRow, 'BERAT BERSIH');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);

		$sheet->getColumnDimension("H")->setAutoSize(true);
		$sheet->setCellValue('H' . $NewRow, 'PRICE REF');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);

		$sheet->getColumnDimension("I")->setAutoSize(true);
		$sheet->setCellValue('I' . $NewRow, 'TOTAL PRICE');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($whiteCenterBold);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);

		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$GET_LEVEL4 		= get_inventory_lv4();
		$GET_ACC 			= get_accessories();
		$GET_PRICE_REF 		= get_price_ref();

		if ($detail) {
			$awal_row   = $NextRow;
			$no = 0;
			foreach ($detail as $key => $valx) {
				$no++;
				$awal_row++;
				$awal_col   = 0;

				$BERAT_MINUS = 0;
				if (!empty($detail_additive)) {
					foreach ($detail_additive as $val => $valx1) {
						$val++;
						$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx1['no_bom_detail'], 'category' => 'additive'))->result();
						$PENGURANGAN_BERAT = 0;
						foreach ($detail_custom as $valx2) {
							$PENGURANGAN_BERAT += $valx2->weight * $valx2->persen / 100;
						}
						$BERAT_MINUS += $PENGURANGAN_BERAT;
					}
				}

				$nm_material		= (!empty($GET_LEVEL4[$valx['code_material']]['nama'])) ? $GET_LEVEL4[$valx['code_material']]['nama'] : '-';
				$code_lv1		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv1'])) ? $GET_LEVEL4[$valx['code_material']]['code_lv1'] : '-';
				$code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2'])) ? $GET_LEVEL4[$valx['code_material']]['code_lv2'] : '-';
				$code_lv3		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv3'])) ? $GET_LEVEL4[$valx['code_material']]['code_lv3'] : '-';

				$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref'])) ? $GET_PRICE_REF[$valx['code_material']]['price_ref'] : 0;

				$nm_type 		= strtoupper(get_name('new_inventory_1', 'nama', 'code_lv1', $code_lv1));
				$nm_category 	= strtoupper(get_name('new_inventory_2', 'nama', 'code_lv2', $code_lv2));
				$nm_jenis		= strtoupper(get_name('new_inventory_3', 'nama', 'code_lv3', $code_lv3));

				$berat_pengurang_additive = ($nm_category == 'RESIN') ? $BERAT_MINUS : 0;
				$berat_bersih 	= $valx['weight'] - $berat_pengurang_additive;
				$total_price 	= $berat_bersih * $price_ref;

				$awal_col++;
				$no   = $no;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $no);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_type);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_category);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_jenis);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$weight 	= $valx['weight'];
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $weight);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $berat_bersih);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $price_ref);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$Cols       = getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $total_price);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);
			}
		}

		$sheet->setTitle('Detail Costing');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter      = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="detail-costing-' . $nameLv4Label . '.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function pengajuan_costing_std()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->uri->segment(3);
		$product_price 		= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));

		$data = [
			'no_bom' => $no_bom,
			'dataList' => $costing_rate,
			'product_price' => $product_price,
			'header' => $header,
			'detail' => $detail,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'product' => $product,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories(),
			'GET_PRICE_REF' => get_price_ref()
		];
		$this->template->title('Pengajuan Price List Costing Pultrution');
		$this->template->render('pengajuan_costing_std', $data);
	}

	public function detail_costing_std()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->uri->segment(3);
		$product_price 		= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));

		$data = [
			'no_bom' => $no_bom,
			'dataList' => $costing_rate,
			'product_price' => $product_price,
			'header' => $header,
			'detail' => $detail,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'product' => $product,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories(),
			'GET_PRICE_REF' => get_price_ref()
		];
		$this->template->title('Costing Rate');
		$this->template->render('detail_costing_std', $data);
	}

	public function update_product_price_satuan()
	{

		$no_bom 		= $this->input->post('no_bom');
		$new_packing 	= str_replace(',', '', $this->input->post('new_packing'));
		$new_shipping 	= str_replace(',', '', $this->input->post('new_shipping'));

		$session = $this->session->userdata('app_session');
		$dateTime 	= date('Y-m-d H:i:s');
		$id_user	= $session['id_user'];

		$result = $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		// echo $this->db->last_query();
		$dateTime 	= date('Y-m-d H:i:s');
		$date 		= date('YmdHis');
		$GET_RATE_COSTING = get_rate_costing_rate();
		$ArrHeader = [];
		foreach ($result as $key => $value) {
			$ArrHeader[$key]['id'] 					= $value['id'];
			$ArrHeader[$key]['update_by'] 			= $id_user;
			$ArrHeader[$key]['update_date'] 		= $dateTime;
			$ArrHeader[$key]['sts_logistik'] 		= 'Y';
			$ArrHeader[$key]['logistik_by'] 		= $id_user;
			$ArrHeader[$key]['logistik_date'] 		= $dateTime;

			$persen_indirect 	= $GET_RATE_COSTING[3];
			$persen_consumable 	= $GET_RATE_COSTING[6];
			$persen_packing 	= $GET_RATE_COSTING[7];
			$persen_enginnering = $GET_RATE_COSTING[9];
			$persen_foh 		= $GET_RATE_COSTING[10];
			$persen_fin_adm 	= $GET_RATE_COSTING[11];
			$persen_mkt_sales 	= $GET_RATE_COSTING[12];
			$persen_interest 	= $GET_RATE_COSTING[13];
			$persen_profit 		= $GET_RATE_COSTING[14];
			$persen_allowance 	= $GET_RATE_COSTING[18];

			$cost_material	= $value['price_material'];
			$cost_man_power	= $value['price_man_power'];
			$cost_mesin		= $value['price_machine'];

			// echo $cost_material; exit;
			//4 logistik
			$packing 		= $new_packing;
			$transport		= $new_shipping;
			$cost_logistik 	= $packing + $transport;

			$cost_enginnering 	= ($cost_material + $cost_man_power + $cost_mesin) * $persen_enginnering / 100;
			$cost_foh 			= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering) * $persen_foh / 100;
			$cost_fin_adm 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering) * $persen_fin_adm / 100;
			$cost_mkt_sales 	= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering) * $persen_mkt_sales / 100;
			$cost_interest 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales) * $persen_interest / 100;
			$cost_profit 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales + $cost_interest) * $persen_profit / 100;
			$bottom_price 		= ($cost_material + $cost_man_power + $cost_mesin + $cost_logistik + $cost_enginnering + $cost_foh + $cost_fin_adm + $cost_mkt_sales + $cost_interest + $cost_profit);
			$factor_kompetitif	= 1;
			$bottom_selling		= $bottom_price * $factor_kompetitif;
			$nego_allowance		= $bottom_selling * ($persen_allowance / 100);
			$price_final		= $bottom_selling + $nego_allowance;

			$ArrHeader[$key]['cost_bottom_price'] 		= $bottom_price;
			$ArrHeader[$key]['cost_factor_kompetitif']	= $factor_kompetitif;
			$ArrHeader[$key]['cost_price_final'] 		= $price_final;
			$ArrHeader[$key]['cost_packing'] 			= $packing;
			$ArrHeader[$key]['cost_transport'] 			= $transport;
			$ArrHeader[$key]['cost_enginnering'] 		= $cost_enginnering;
			$ArrHeader[$key]['cost_foh'] 				= $cost_foh;
			$ArrHeader[$key]['cost_fin_adm'] 			= $cost_fin_adm;
			$ArrHeader[$key]['cost_mkt_sales'] 			= $cost_mkt_sales;
			$ArrHeader[$key]['cost_interest'] 			= $cost_interest;
			$ArrHeader[$key]['cost_profit'] 			= $cost_profit;
			$ArrHeader[$key]['cost_bottom_selling'] 	= $bottom_selling;
			$ArrHeader[$key]['cost_allowance'] 			= $nego_allowance;
			$ArrHeader[$key]['price_total'] 			= $price_final;
		}
		// echo "<pre>";
		// print_r($ArrHeader);
		// exit;
		$this->db->trans_start();
		if (!empty($ArrHeader)) {
			$this->db->update_batch('product_price', $ArrHeader, 'id');
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Failed process data!',
				'status'	=> 0,
				'no_bom'	=> $no_bom
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success process data!',
				'status'	=> 1,
				'no_bom'	=> $no_bom
			);
			history('Update logistik product price ' . $no_bom);
		}
		echo json_encode($status);
	}

	public function pengajuan_costing_ass()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 		= $this->uri->segment(3);
		$product_price 	= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		$costing_rate 	= $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();
		$kode 			= (!empty($product_price[0]['kode'])) ? $product_price[0]['kode'] : 0;
		$list_assembly 			= $this->db->not_like('category', 'addBO')->get_where('product_price_assembly', array('kode' => $kode))->result_array();
		$list_assembly_product 	= $this->db->like('category', 'addBO')->get_where('product_price_assembly', array('kode' => $kode))->result_array();
		$list_cutting_process 	= $this->db->like('category', 'addBO')->get_where('product_price_bom_detail', array('kode' => $kode))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$detail_others 		= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'others'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));
		

		$detail_ipp_ukuranjadi = [];
		if (!empty($header[0]->id_ipp)) {
			$header_ipp  = $this->db->get_where('custom_ipp', array('id' => $header[0]->id_ipp))->result();
			if (!empty($header_ipp[0]->no_ipp)) {
				$no_ipp 		= (!empty($header_ipp[0]->no_ipp)) ? $header_ipp[0]->no_ipp : 0;
				$detail_ipp_ukuranjadi  = $this->db->get_where('custom_ipp_detail_lainnya', array('no_ipp' => $no_ipp, 'category' => 'ukuran jadi'))->result_array();
			}
		}

		
		// echo $this->db->last_query();
		// print_r($detail_ipp_ukuranjadi);
		// exit;

		$ttl_accessories = 0;
		foreach($detail_accessories as $item) {
			$get_accessories = $this->db->get_where('accessories', ['id' => $item['code_material']])->row();
			// if(!empty($get_accessories)) {
				$ttl_accessories += $get_accessories->price_ref_use_usd * $item['weight'];
			// }
		}

		$data = [
			'no_bom' => $no_bom,
			'kode' => $kode,
			'dataList' => $costing_rate,
			'list_assembly' => $list_assembly,
			'list_assembly_product' => $list_assembly_product,
			'list_cutting_process' => $list_cutting_process,
			'product_price' => $product_price,
			'header' => $header,
			'detail' => $detail,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'detail_others' => $detail_others,
			'product' => $product,
			'detail_ipp_ukuranjadi' => $detail_ipp_ukuranjadi,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories(),
			'GET_PRICE_REF' => get_price_ref(),
			'ttl_accessories' => $ttl_accessories
		];
		$this->template->title('Pengajuan Price List Costing Custom');
		$this->template->render('pengajuan_costing_ass', $data);
	}

	public function detail_single_product()
	{
		$no_bom 	= $this->input->post('no_bom');
		$detail_single 	= $this->db->like('category', 'addBO')->get_where('product_price_assembly', array('kode' => $no_bom))->result_array();

		$data = [
			'detail_single' => $detail_single
		];
		$this->template->render('detail_single_product', $data);
	}

	public function detail_mat_cutting_process()
	{
		$no_bom 	= $this->input->post('no_bom');
		$detail_single 	= $this->db->select('code_material,price_ref,SUM(berat_bersih) AS berat_bersih')->like('category', 'addBO')->group_by('code_material')->get_where('product_price_bom_detail', array('kode' => $no_bom))->result_array();

		$data = [
			'detail_single' => $detail_single,
			'GET_MATERIAL' => get_inventory_lv4()
		];
		$this->template->render('detail_mat_cutting_process', $data);
	}

	public function detail_mat_assembly()
	{
		$no_bom 	= $this->input->post('no_bom');
		$nm_class 	= $this->input->post('nm_class');
		$detail_single 	= $this->db->select('code_material,price_ref,SUM(berat_bersih) AS berat_bersih')->like('category', $nm_class)->group_by('code_material')->get_where('product_price_bom_detail', array('kode' => $no_bom))->result_array();

		$data = [
			'detail_single' => $detail_single,
			'GET_MATERIAL' => get_inventory_lv4()
		];
		$this->template->render('detail_mat_cutting_process', $data);
	}

	public function detail_costing_ass()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 		= $this->uri->segment(3);
		$product_price 	= $this->db->get_where('product_price', array('no_bom' => $no_bom, 'deleted_date' => NULL))->result_array();
		$costing_rate 	= $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();
		$kode 			= (!empty($product_price[0]['kode'])) ? $product_price[0]['kode'] : 0;
		$list_assembly 			= $this->db->not_like('category', 'addBO')->get_where('product_price_assembly', array('kode' => $kode))->result_array();
		$list_assembly_product 	= $this->db->like('category', 'addBO')->get_where('product_price_assembly', array('kode' => $kode))->result_array();
		$list_cutting_process 	= $this->db->like('category', 'addBO')->get_where('product_price_bom_detail', array('kode' => $kode))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$detail_others 		= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'others'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));

		$detail_ipp_ukuranjadi = [];
		if (!empty($product_price[0]['kode'])) {
			$detail_ipp_ukuranjadi  = $this->db->get_where('product_price_ukuran_jadi', array('kode' => $product_price[0]['kode'], 'deleted_date' => null))->result_array();
		}
		// echo $this->db->last_query();
		// echo $product_price[0]['kode'];
		// print_r($detail_ipp_ukuranjadi);
		// exit;

		$data = [
			'no_bom' => $no_bom,
			'kode' => $kode,
			'dataList' => $costing_rate,
			'list_assembly' => $list_assembly,
			'list_assembly_product' => $list_assembly_product,
			'list_cutting_process' => $list_cutting_process,
			'product_price' => $product_price,
			'header' => $header,
			'detail' => $detail,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'detail_others' => $detail_others,
			'product' => $product,
			'detail_ipp_ukuranjadi' => $detail_ipp_ukuranjadi,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories(),
			'GET_PRICE_REF' => get_price_ref()
		];
		$this->template->title('Detail Price List Costing Custom');
		$this->template->render('detail_costing_ass', $data);
	}
}
