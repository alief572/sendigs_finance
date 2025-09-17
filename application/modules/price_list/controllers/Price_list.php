<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Price_list extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Price_List.View';
    protected $addPermission  	= 'Price_List.Add';
    protected $managePermission = 'Price_List.Manage';
    protected $deletePermission = 'Price_List.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Price_list/price_list_model','Product_price/product_price_model'
                                ));
        date_default_timezone_set('Asia/Bangkok');
    }

    //========================================================BOM

    public function index(){
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');

		$product_price 	= $this->db->select('MAX(update_date) AS updated_date')->get('product_price')->result();
		$last_update 	= "Last Update: ".date('d-M-Y H:i:s',strtotime($product_price[0]->updated_date));
		$data = [
			'product_lv1' => array(),
			'last_update' => $last_update
		];
		
		history("View index price list");
		$this->template->title('Costing / Product & Price List');
		$this->template->render('index',$data);
    }

    public function data_side_product_price(){
      $this->price_list_model->get_json_product_price();
    }

	public function detail_costing(){
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->uri->segment(3);
		$product_price 		= $this->db->get_where('product_price',array('no_bom' => $no_bom,'deleted_date'=>NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate',array('deleted_date'=>NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'ukuran jadi'))->result_array();
		$product    		= $this->price_list_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product'));

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

	public function detail_material(){
		// $this->auth->restrict($this->viewPermission);
		$no_bom 			= $this->input->post('no_bom');
		
		$header 			= $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'ukuran jadi'))->result_array();
		$product    		= $this->price_list_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product'));

		$data = [
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
		$this->template->render('detail_bom_material', $data);
  	}

	public function update_product_price(){
		$session = $this->session->userdata('app_session');
		$dateTime 	= date('Y-m-d H:i:s');
		$id_user	= $session['id_user'];

		$SQL 	= "SELECT a.* FROM bom_header a WHERE a.deleted_date IS NULL AND a.category IN ('standard','grid standard','grid custom') AND a.id_product LIKE 'P%'";
		$result = $this->db->query($SQL)->result_array();

		$dateTime 	= date('Y-m-d H:i:s');
		$date 		= date('YmdHis');

		$GET_RATE_COSTING = get_rate_costing_rate();
		$GET_RATE_MAN_POWER = $this->db->order_by('id','desc')->get('rate_man_power')->result();
		$GET_LEVEL4 = get_inventory_lv4();
		$GET_PRICE_REF = get_price_ref();
		$GET_MACHINE_PRODUCT = get_machine_product();
		$GET_MOLD_PRODUCT = get_mold_product();
		$GET_MACHINE_RATE = get_rate_machine();
		$GET_MOLD_RATE = get_rate_mold();
		$GET_CYCLETIME = get_total_time_cycletime();

		$ArrHeader = [];
		$ArrDetailDefault = [];
		$ArrDetailAdditive = [];
		$ArrDetailAdditiveCustom = [];
		$ArrDetailTopping = [];
		$ArrDetailToppingCustom = [];
		foreach ($result as $key => $value) {
			$no_bom = $value['no_bom'];
			$kode 	= $date.'-'.$no_bom;

			$detail   			= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'default'))->result_array();
			$detail_additive   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'additive'))->result_array();
			$detail_topping   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'topping'))->result_array();

			$BERAT_MINUS = 0;
			if(!empty($detail_additive)){
				foreach($detail_additive AS $val => $valx){
					$detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'additive'))->result();
					$PENGURANGAN_BERAT = 0;
					foreach($detail_custom AS $valx2){
						$PENGURANGAN_BERAT += $valx2->weight * $valx2->persen /100;
					}
					$BERAT_MINUS += $PENGURANGAN_BERAT;
				}
			}

			$TOTAL_PRICE_ALL = 0;
			$TOTAL_BERAT_BERSIH = 0;
			//default
			if(!empty($detail)){
				foreach($detail AS $val => $valx){ $val++;
					$code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2']))?$GET_LEVEL4[$valx['code_material']]['code_lv2']:'-';
					$price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref']))?$GET_PRICE_REF[$valx['code_material']]['price_ref']:0;
					$nm_category = strtolower(get_name('new_inventory_2','nama','code_lv2',$code_lv2));
					$berat_pengurang_additive = ($nm_category == 'resin')?$BERAT_MINUS:0;

					$berat_bersih = $valx['weight'] - $berat_pengurang_additive;
					$total_price = $berat_bersih * $price_ref;
					$TOTAL_PRICE_ALL += $total_price;
					$TOTAL_BERAT_BERSIH += $berat_bersih;
					$UNIQ = $val.'-'.$key;
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

			//additive
			if(!empty($detail_additive)){
				foreach($detail_additive AS $val => $valx){ $val++;
					$UNIQ = $val.'-'.$key;
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

					$detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'additive'))->result_array();
					foreach($detail_custom AS $val2 => $valx2){
						$price_ref      = (!empty($GET_PRICE_REF[$valx2['code_material']]['price_ref']))?$GET_PRICE_REF[$valx2['code_material']]['price_ref']:0;
						$berat_bersih = $valx2['weight'];
						$total_price    = $berat_bersih * $price_ref;
						$TOTAL_PRICE_ALL += $total_price;
						$TOTAL_BERAT_BERSIH += $berat_bersih;
						$UNIQ = $val.'-'.$val2.'-'.$key;
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
			if(!empty($detail_topping)){
				foreach($detail_topping AS $val => $valx){
					$UNIQ = $val.'-'.$key;
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
					$ArrDetailTopping[$UNIQ]['qty'] 				=  $valx['qty'];
					$ArrDetailTopping[$UNIQ]['m2'] 				=  $valx['m2'];
					$ArrDetailTopping[$UNIQ]['file_upload'] 		=  $valx['file_upload'];
					$detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'topping'))->result_array();
					foreach($detail_custom AS $val2 => $valx2){
						$price_ref      = (!empty($GET_PRICE_REF[$valx2['code_material']]['price_ref']))?$GET_PRICE_REF[$valx2['code_material']]['price_ref']:0;
						$berat_bersih    = $valx2['weight'];
						$total_price    = $berat_bersih * $price_ref;
						$TOTAL_PRICE_ALL += $total_price;
						$TOTAL_BERAT_BERSIH += $berat_bersih;
						$UNIQ = $val.'-'.$val2.'-'.$key;
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
			$ArrHeader[$key]['code_lv1'] 			= $GET_LEVEL4[$code_level4]['code_lv1'];
			$ArrHeader[$key]['product_type'] 		= NULL;
			$ArrHeader[$key]['code_lv2'] 			= $GET_LEVEL4[$code_level4]['code_lv2'];
			$ArrHeader[$key]['product_category'] 	= NULL;
			$ArrHeader[$key]['code_lv3'] 			= $GET_LEVEL4[$code_level4]['code_lv3'];
			$ArrHeader[$key]['product_jenis'] 		= NULL;
			$ArrHeader[$key]['code_lv4'] 			= $code_level4;
			$ArrHeader[$key]['product_master'] 		= $GET_LEVEL4[$code_level4]['nama'];
			$ArrHeader[$key]['berat_material'] 		= $TOTAL_BERAT_BERSIH;
			
			$ArrHeader[$key]['update_by'] 			= $id_user;
			$ArrHeader[$key]['update_date'] 		= $dateTime;
			$ArrHeader[$key]['deleted_by'] 			= NULL;
			$ArrHeader[$key]['deleted_date'] 		= NULL;


			$cycletimeMaster 	= (!empty($GET_CYCLETIME[$code_level4]['ct_manpower']))?$GET_CYCLETIME[$code_level4]['ct_manpower']:0;
			$cycletimeMesin 	= (!empty($GET_CYCLETIME[$code_level4]['ct_machine']))?$GET_CYCLETIME[$code_level4]['ct_machine']:0;
			$rate_cycletime 	= 0;
			$rate_cycletime_mch 	= 0;
			if($cycletimeMaster > 0){
				$rate_cycletime 		= $cycletimeMaster / 60;
				$rate_cycletime_mch 	= $cycletimeMesin / 60;
			}
			$rate_manpower 		= $GET_RATE_MAN_POWER[0]->upah_per_jam_dollar;

			$kode_mesin = (!empty($GET_MACHINE_PRODUCT[$code_level4]))?$GET_MACHINE_PRODUCT[$code_level4]:0;
			$kode_mold = (!empty($GET_MOLD_PRODUCT[$code_level4]))?$GET_MOLD_PRODUCT[$code_level4]:0;
			
			$rate_depresiasi 	= (!empty($GET_MACHINE_RATE[$kode_mesin]['biaya_mesin']))?$GET_MACHINE_RATE[$kode_mesin]['biaya_mesin']:0;
			$rate_mould 		= (!empty($GET_MOLD_RATE[$kode_mold]['biaya_mesin']))?$GET_MOLD_RATE[$kode_mold]['biaya_mesin']:0;

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
			$packing 		= ($cost_material + $cost_man_power + $cost_mesin) * $persen_packing / 100;
			$transport		= 0;
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

			$ArrHeader[$key]['rate_cycletime'] 			= $rate_cycletime;
			$ArrHeader[$key]['rate_cycletime_machine'] 	= $rate_cycletime_mch;
			$ArrHeader[$key]['rate_man_power_usd'] 		= $rate_manpower;
			$ArrHeader[$key]['rate_man_power_idr'] 	= $GET_RATE_MAN_POWER[0]->upah_per_jam;
			$ArrHeader[$key]['rate_depresiasi'] 	= $rate_depresiasi;
			$ArrHeader[$key]['rate_mould'] 			= $rate_mould;
			$ArrHeader[$key]['cost_material'] 		= $cost_material;
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
			if(!empty($ArrHeader)){
				$this->db->update('product_price',$ArrUpdate);
				
				$this->db->insert_batch('product_price',$ArrHeader);
			}
			if(!empty($ArrDetailDefault)){
				$this->db->insert_batch('product_price_bom_detail',$ArrDetailDefault);
			}
			if(!empty($ArrDetailAdditive)){
				$this->db->insert_batch('product_price_bom_detail',$ArrDetailAdditive);
			}
			if(!empty($ArrDetailAdditiveCustom)){
				$this->db->insert_batch('product_price_bom_detail_custom',$ArrDetailAdditiveCustom);
			}
			if(!empty($ArrDetailTopping)){
				$this->db->insert_batch('product_price_bom_detail',$ArrDetailTopping);
			}
			if(!empty($ArrDetailToppingCustom)){
				$this->db->insert_batch('product_price_bom_detail_custom',$ArrDetailToppingCustom);
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
          history('Update product price');
        }
        echo json_encode($status);

		

	}

	public function detail_machine_mold(){
		$id_product = $this->input->post('id_product');
		$tanda 		= $this->input->post('tanda');
		$cost 		= $this->input->post('cost');
		$header = $this->db->get_where('cycletime_header',array('id_product' => $id_product, 'deleted_date'=>NULL))->result();
		// print_r($header);
		$title = ($tanda == 'machine')?'Machine':'Mold';
		$data = [
		  'id_product' => $id_product,
		  'header' => $header,
		  'tanda' => $tanda,
		  'title' => $title,
		  'cost' => $cost,
		];
		$this->template->render('detail_machine_mold', $data);
	}

	public function pengajuan_costing(){
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->uri->segment(3);
		$product_price 		= $this->db->get_where('product_price',array('no_bom' => $no_bom,'deleted_date'=>NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate',array('deleted_date'=>NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'ukuran jadi'))->result_array();
		$product    		= $this->price_list_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product'));

		$detail_ipp_ukuranjadi = [];
		if(!empty($product_price[0]['kode'])){
			$kode 		= (!empty($product_price[0]['kode']))?$product_price[0]['kode']:0;
			$detail_ipp_ukuranjadi  = $this->db->get_where('product_price_ukuran_jadi',array('kode' => $kode, 'deleted_date'=> null))->result_array();
		}

		// print_r($detail_ipp_ukuranjadi);
		// exit;

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
		  'detail_ipp_ukuranjadi' => $detail_ipp_ukuranjadi,
		  'GET_LEVEL4' => get_inventory_lv4(),
		  'GET_ACC' => get_accessories(),
		  'GET_PRICE_REF' => get_price_ref()
		];
		$this->template->title('Approval Price List');
		$this->template->render('pengajuan_costing', $data);
	}

	public function confirm_product_price(){
		$session = $this->session->userdata('app_session');
		$dateTime 	= date('Y-m-d H:i:s');
		$id_user	= $session['id_user'];

		$data 	= $this->input->post();
		$id 	= $data['id'];
		$status = $data['status'];
		$reason = $data['reason'];
		$price_list 	= str_replace(',','',$data['price_list']);
		$price_list_idr = str_replace(',','',$data['price_list_idr']);
		$kurs 			= str_replace(',','',$data['kurs']);
		$price_persen_orindo 	= str_replace(',','',$data['price_persen_orindo']);
		$price_list_idr_orindo 	= str_replace(',','',$data['price_list_idr_orindo']);

		$ArrUpdate = [
			'status' => $status,
			'reason' => $reason,
			'status_by' => $id_user,
			'status_date' => $dateTime
		];
		$ArrPrice = [];
		$ArrDetail = [];
		if($status == 'A'){
			$ArrPrice = [
				'kurs' => $kurs,
				'price_list' => $price_list,
				'price_list_idr' => $price_list_idr,
				'price_persen_orindo' => $price_persen_orindo,
				'price_list_idr_orindo' => $price_list_idr_orindo
			];

			$ukuran_jadi_price = (!empty($data['ukuran_jadi_price']))?$data['ukuran_jadi_price']:[];
			if(!empty($ukuran_jadi_price)){
				foreach ($ukuran_jadi_price as $key => $value) {
					$ArrDetail[$key]['id'] 		= $value['id'];
					$ArrDetail[$key]['app_price'] 			= str_replace(',','',$value['app_price']);
					$ArrDetail[$key]['persen_orindo'] 		= str_replace(',','',$value['persen_orindo']);
					$ArrDetail[$key]['harga_idr_orindo'] 	= str_replace(',','',$value['harga_idr_orindo']);
				}
			}
		}

		$ArrMerge = array_merge($ArrUpdate,$ArrPrice);

		$this->db->trans_start();
			$this->db->where('id',$id);
			$this->db->update('product_price',$ArrMerge);

			if(!empty($ArrDetail)){
				$this->db->update_batch('product_price_ukuran_jadi',$ArrDetail,'id');
			}
        $this->db->trans_complete();	

        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $ArrayBack	= array(
            'pesan'		=>'Failed process data!',
            'status'	=> 0
          );
        } else {
          $this->db->trans_commit();
          $ArrayBack	= array(
            'pesan'		=>'Success process data!',
            'status'	=> 1
          );
          history('Confirm price list costing id: '.$id.' / '.$status);
        }
        echo json_encode($ArrayBack);

		

	}

	public function detail_costing_std(){
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->uri->segment(3);
		$product_price 		= $this->db->get_where('product_price',array('no_bom' => $no_bom,'deleted_date'=>NULL))->result_array();
		$costing_rate = $this->db->get_where('costing_rate',array('deleted_date'=>NULL))->result_array();

		//Material
		$header 			= $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'ukuran jadi'))->result_array();
		$product    		= $this->price_list_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product'));

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

	public function detail_costing_ass(){
		// $this->auth->restrict($this->viewPermission);
		$no_bom 		= $this->uri->segment(3);
		$product_price 	= $this->db->get_where('product_price',array('no_bom' => $no_bom,'deleted_date'=>NULL))->result_array();
		$costing_rate 	= $this->db->get_where('costing_rate',array('deleted_date'=>NULL))->result_array();
		$kode 			= (!empty($product_price[0]['kode']))?$product_price[0]['kode']:0;
		$list_assembly 			= $this->db->not_like('category','addBO')->get_where('product_price_assembly',array('kode'=>$kode))->result_array(); 
		$list_assembly_product 	= $this->db->like('category','addBO')->get_where('product_price_assembly',array('kode'=>$kode))->result_array(); 
		$list_cutting_process 	= $this->db->like('category','addBO')->get_where('product_price_bom_detail',array('kode'=>$kode))->result_array(); 

		//Material
		$header 			= $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'default'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'accessories'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'ukuran jadi'))->result_array();
		$detail_others 		= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'others'))->result_array();
		$product    		= $this->product_price_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product'));

		$detail_ipp_ukuranjadi = [];
		if(!empty($product_price[0]['kode'])){
			$detail_ipp_ukuranjadi  = $this->db->get_where('product_price_ukuran_jadi',array('kode' => $product_price[0]['kode'], 'deleted_date'=> null))->result_array();
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

?>
