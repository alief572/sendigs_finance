<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("Asia/Bangkok");

function get_root3()
{
	$explodeURL = explode('/', base_url());
	return $_SERVER['DOCUMENT_ROOT'] . '/' . $explodeURL[3];
}

function get_root3LinkLive()
{
	return 'https://sentral.dutastudy.com/origa_live/';
}

function whiteCenterBold()
{
	$styleArray = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
		),
		'font' => array(
			'bold' => true,
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	);
	return $styleArray;
}

function whiteRightBold()
{
	$styleArray = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
		),
		'font' => array(
			'bold' => true,
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	);
	return $styleArray;
}

function whiteCenter()
{
	$styleArray = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	);
	return $styleArray;
}

function mainTitle()
{
	$styleArray = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => 'e0e0e0'),
		),
		'font' => array(
			'bold' => true,
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	);
	return $styleArray;
}

function tableHeader()
{
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		),
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => 'e0e0e0'),
		),
		'font' => array(
			'bold' => true,
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	);
	return $styleArray;
}

function tableBodyCenter()
{
	$styleArray = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	);
	return $styleArray;
}

function tableBodyLeft()
{
	$styleArray = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	);
	return $styleArray;
}

function tableBodyRight()
{
	$styleArray = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	);
	return $styleArray;
}

//NEW
function get_list_inventory_lv1($category)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('new_inventory_1', array('category' => $category, 'deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv1']]['code_lv1'] 	= $value['code_lv1'];
		$ArrGetCategory[$value['code_lv1']]['nama'] 		= $value['nama'];
		$ArrGetCategory[$value['code_lv1']]['code'] 		= $value['code'];
	}
	return $ArrGetCategory;
}

function get_list_inventory_lv2($category)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('new_inventory_2', array('category' => $category, 'deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']]['code_lv1'] 	= $value['code_lv1'];
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']]['code_lv2'] 	= $value['code_lv2'];
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']]['nama'] 		= $value['nama'];
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']]['code'] 		= $value['code'];
	}
	return $ArrGetCategory;
}

function get_list_inventory_lv3($category)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('new_inventory_3', array('category' => $category, 'deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']][$value['code_lv3']]['code_lv1'] 	= $value['code_lv1'];
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']][$value['code_lv3']]['code_lv2'] 	= $value['code_lv2'];
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']][$value['code_lv3']]['code_lv3'] 	= $value['code_lv3'];
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']][$value['code_lv3']]['nama'] 		= $value['nama'];
		$ArrGetCategory[$value['code_lv1']][$value['code_lv2']][$value['code_lv3']]['code'] 		= $value['code'];
	}
	return $ArrGetCategory;
}

function get_list_inventory_lv4($category)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('new_inventory_4', array('category' => $category, 'deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv4']]['code_lv1'] 	= $value['code_lv1'];
		$ArrGetCategory[$value['code_lv4']]['code_lv2'] 	= $value['code_lv2'];
		$ArrGetCategory[$value['code_lv4']]['code_lv3'] 	= $value['code_lv3'];
		$ArrGetCategory[$value['code_lv4']]['code_lv4'] 	= $value['code_lv4'];
		$ArrGetCategory[$value['code_lv4']]['nama'] 		= $value['nama'];
		$ArrGetCategory[$value['code_lv4']]['code'] 		= $value['code'];
		$ArrGetCategory[$value['code_lv4']]['min_stock'] 	= $value['min_stok'];
		$ArrGetCategory[$value['code_lv4']]['moq'] 			= $value['max_stok'];
	}
	return $ArrGetCategory;
}

function get_inventory_lv4()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('new_inventory_4', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv4']]['code_lv1'] 	= $value['code_lv1'];
		$ArrGetCategory[$value['code_lv4']]['code_lv2'] 	= $value['code_lv2'];
		$ArrGetCategory[$value['code_lv4']]['code_lv3'] 	= $value['code_lv3'];
		$ArrGetCategory[$value['code_lv4']]['code_lv4'] 	= $value['code_lv4'];
		$ArrGetCategory[$value['code_lv4']]['nama'] 		= $value['nama'];
		$ArrGetCategory[$value['code_lv4']]['code'] 		= $value['code'];
		$ArrGetCategory[$value['code_lv4']]['min_stock'] 	= $value['min_stok'];
		$ArrGetCategory[$value['code_lv4']]['moq'] 			= $value['max_stok'];
		$ArrGetCategory[$value['code_lv4']]['konversi'] 	= $value['konversi'];
		$ArrGetCategory[$value['code_lv4']]['id_packing'] 	= $value['id_unit_packing'];
		$ArrGetCategory[$value['code_lv4']]['id_unit'] 		= $value['id_unit'];
	}
	return $ArrGetCategory;
}

function get_inventory_lv2()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('new_inventory_2')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv2']]['code_lv2'] 	= $value['code_lv2'];
		$ArrGetCategory[$value['code_lv2']]['nama'] 		= $value['nama'];
	}
	return $ArrGetCategory;
}

function get_inventory_lv3()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('new_inventory_3')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv3']]['code_lv3'] 	= $value['code_lv3'];
		$ArrGetCategory[$value['code_lv3']]['nama'] 		= $value['nama'];
	}
	return $ArrGetCategory;
}

function get_persen_additive()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('bom_detail')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$key = $value['no_bom'] . '-' . $value['code_material'];
		$ArrGetCategory[$key]['persen']	= $value['persen'];
	}
	return $ArrGetCategory;
}

function get_accessories()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('accessories')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id']]['nama_full'] = $value['stock_name'] . ' ' . $value['brand'] . ' ' . $value['spec'];
		$ArrGetCategory[$value['id']]['konversi'] = $value['konversi'];
		$ArrGetCategory[$value['id']]['nama'] = $value['stock_name'];
		$ArrGetCategory[$value['id']]['code'] = $value['id_stock'];
		$ArrGetCategory[$value['id']]['id_unit'] = $value['id_unit'];
		$ArrGetCategory[$value['id']]['id_packing'] = $value['id_unit_gudang'];
	}
	return $ArrGetCategory;
}

function get_list_machine()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->group_by('kd_asset')->get_where('asset', array('deleted_date' => NULL, 'category' => '4'))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['kd_asset']]['kd_asset'] 	= $value['kd_asset'];
		$ArrGetCategory[$value['kd_asset']]['nm_asset'] 	= $value['nm_asset'];
		$ArrGetCategory[$value['kd_asset']]['nilai'] 		= $value['nilai_asset'];
		$ArrGetCategory[$value['kd_asset']]['dept_year'] 	= $value['depresiasi'];
		$ArrGetCategory[$value['kd_asset']]['dept_value'] 	= $value['value'];
	}
	return $ArrGetCategory;
}

function get_list_mould()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->group_by('kd_asset')->get_where('asset', array('deleted_date' => NULL, 'category' => '7'))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['kd_asset']]['kd_asset'] 	= $value['kd_asset'];
		$ArrGetCategory[$value['kd_asset']]['nm_asset'] 	= $value['nm_asset'];
		$ArrGetCategory[$value['kd_asset']]['nilai'] 		= $value['nilai_asset'];
		$ArrGetCategory[$value['kd_asset']]['dept_year'] 	= $value['depresiasi'];
		$ArrGetCategory[$value['kd_asset']]['dept_value'] 	= $value['value'];
	}
	return $ArrGetCategory;
}

function get_list_satuan()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('ms_satuan', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id']]['code'] 	= $value['code'];
	}
	return $ArrGetCategory;
}

function get_list_country()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->order_by('country_name', 'asc')->get('country')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['country_code']]['nama'] 	= $value['country_name'];
	}
	return $ArrGetCategory;
}

function get_list_country_all()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('country_all')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['iso3']]['nama'] 	= $value['name'];
	}
	return $ArrGetCategory;
}

function get_inventory_lv4_lv3()
{
	$CI = &get_instance();
	$listGetCategory4 = $CI->db->select('code_lv4 AS code, nama, "LEVEL 4" AS tanda')->get('new_inventory_4')->result_array();
	$listGetCategory3 = $CI->db->select('code_lv3 AS code, nama, "LEVEL 3" AS tanda')->get('new_inventory_3')->result_array();
	$listGetCategory	= array_merge($listGetCategory4, $listGetCategory3);

	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code']]['tanda'] 	= $value['tanda'];
		$ArrGetCategory[$value['code']]['code'] 	= $value['code'];
		$ArrGetCategory[$value['code']]['nama'] 	= $value['nama'];
	}
	return $ArrGetCategory;
}

function get_list_supplier()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('new_supplier', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id']]['nama'] 	= $value['nama'];
	}
	return $ArrGetCategory;
}

function get_list_user()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('users')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id_user']]['username'] 	= $value['username'];
		$ArrGetCategory[$value['id_user']]['nama'] 		= $value['nm_lengkap'];
	}
	return $ArrGetCategory;
}

function get_price_ref()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('new_inventory_4', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv4']]['price_ref'] 	= $value['price_ref_use_usd'];
	}
	return $ArrGetCategory;
}

function get_rate_costing_rate()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code']] 	= $value['rate'];
	}
	return $ArrGetCategory;
}

function get_stock_product()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db
		->select('
								(SELECT actual_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_akhir,
								a.code_lv4
								')
		->group_by('a.code_lv4')
		->get('stock_product a')
		->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv4']] 	= $value['stock_akhir'];
	}
	return $ArrGetCategory;
}

function get_stock_product_New()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('stock_product')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$keyNew = $value['code_lv4'] . '-' . $value['no_bom'];
		$ArrGetCategory[$keyNew]['stock'] 	= $value['actual_stock'];
		$ArrGetCategory[$keyNew]['booking'] 	= $value['booking_stock'];
		$ArrGetCategory[$keyNew]['downgrade'] 	= $value['ng_stock'];
	}
	return $ArrGetCategory;
}

function get_machine_product()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db
		->select('a.id_product,b.machine')
		->group_by('a.id_product')
		->join('cycletime_detail_detail b', 'a.id_time=b.id_time', 'join')
		->get_where('cycletime_header a', array('a.deleted_date' => NULL, 'b.machine !=' => NULL, 'b.machine !=' => '0'))
		->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id_product']] 	= $value['machine'];
	}
	return $ArrGetCategory;
}

function get_mold_product()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db
		->select('a.id_product,b.mould')
		->group_by('a.id_product')
		->join('cycletime_detail_detail b', 'a.id_time=b.id_time', 'join')
		->get_where('cycletime_header a', array('a.deleted_date' => NULL, 'b.mould !=' => NULL, 'b.mould !=' => '0'))
		->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id_product']] 	= $value['mould'];
	}
	return $ArrGetCategory;
}

function get_rate_machine()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('rate_machine', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['kd_mesin']]['biaya_mesin'] 	= $value['biaya_mesin'];
	}
	return $ArrGetCategory;
}

function get_rate_mold()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('rate_mold', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['kd_mesin']]['biaya_mesin'] 	= $value['biaya_mesin'];
	}
	return $ArrGetCategory;
}

function getTotalBeratSPKSOInternal()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('kode_det, SUM(weight) AS weight')->group_by('kode_det')->get('so_internal_spk_material')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['kode_det']] 	= $value['weight'];
	}
	return $ArrGetCategory;
}

function getTotalBeratSPKSOInternalMixing()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('kode_det, SUM(weight) AS weight')->group_by('kode_det')->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['kode_det']] 	= $value['weight'];
	}
	return $ArrGetCategory;
}

function getStokMaterial($id_gudang)
{
	$CI = &get_instance();
	$listGetCategory =	 $CI->db
		->select('
											a.id_material,
											a.qty_stock,
											a.qty_booking,
											b.konversi
										')
		->join('new_inventory_4 b', 'a.id_material=b.code_lv4')
		->get_where('warehouse_stock a', array('a.id_gudang' => $id_gudang))
		->result_array();
	$ArrGetCategory 	= [];
	foreach ($listGetCategory as $key => $value) {
		$stok_packing = 0;
		if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
			$stok_packing = $value['qty_stock'] / $value['konversi'];
		}
		$ArrGetCategory[$value['id_material']]['stok'] 	= $value['qty_stock'];
		$ArrGetCategory[$value['id_material']]['booking'] 	= $value['qty_booking'];
		$ArrGetCategory[$value['id_material']]['stok_packing'] 	= $stok_packing;
		$ArrGetCategory[$value['id_material']]['konversi'] 	= $value['konversi'];
	}
	return $ArrGetCategory;
}

function getStokBarang($id_gudang)
{
	$CI = &get_instance();
	$listGetCategory =	 $CI->db
		->select('
											a.id_material,
											a.qty_stock,
											a.qty_booking,
											b.konversi
										')
		->join('accessories b', 'a.id_material=b.id')
		->get_where('warehouse_stock a', array('a.id_gudang' => $id_gudang))->result_array();
	$ArrGetCategory 	= [];
	foreach ($listGetCategory as $key => $value) {
		$stok_packing = 0;
		if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
			$stok_packing = $value['qty_stock'] / $value['konversi'];
		}

		$id_material = $value['id_material'];
		$ArrGetCategory[$id_material]['stok'] 	= $value['qty_stock'];
		$ArrGetCategory[$id_material]['stok_packing'] 	= $stok_packing;
		$ArrGetCategory[$id_material]['konversi'] 	= $value['konversi'];
	}
	return $ArrGetCategory;
}

function getStokBarangAll()
{
	$CI = &get_instance();
	$listGetCategory =	 $CI->db
		->select('
											a.id_material,
											SUM(a.qty_stock) AS qty_stock,
											b.konversi
										')
		->group_by('a.id_material')
		->join('accessories b', 'a.id_material=b.id')
		->get('warehouse_stock a')
		->result_array();
	$ArrGetCategory 	= [];
	foreach ($listGetCategory as $key => $value) {
		$stok_packing = 0;
		if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
			$stok_packing = $value['qty_stock'] / $value['konversi'];
		}
		$ArrGetCategory[$value['id_material']]['stok'] 	= $value['qty_stock'];
		$ArrGetCategory[$value['id_material']]['stok_packing'] 	= $stok_packing;
		$ArrGetCategory[$value['id_material']]['konversi'] 	= $value['konversi'];
	}
	return $ArrGetCategory;
}

function getStokMaterialHistory($id_gudang, $date_filter)
{
	$CI = &get_instance();
	$listGetCategory =	 $CI->db
		->select('
											a.id_material,
											MAX(a.qty_stock) as qty_stock,
											MAX(a.qty_booking) as qty_booking,
											b.konversi
										')
		->join('new_inventory_4 b', 'a.id_material=b.code_lv4')
		->get_where('warehouse_stock_per_day a', array('a.id_gudang' => $id_gudang, 'DATE(a.hist_date)' => $date_filter))
		->order_by('a.hist_date', 'desc')
		->result_array();
	$ArrGetCategory 	= [];
	foreach ($listGetCategory as $key => $value) {
		$stok_packing = 0;
		if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
			$stok_packing = $value['qty_stock'] / $value['konversi'];
		}
		$ArrGetCategory[$value['id_material']]['stok'] 	= $value['qty_stock'];
		$ArrGetCategory[$value['id_material']]['booking'] 	= $value['qty_booking'];
		$ArrGetCategory[$value['id_material']]['stok_packing'] 	= $stok_packing;
		$ArrGetCategory[$value['id_material']]['konversi'] 	= $value['konversi'];
	}
	return $ArrGetCategory;
}

function getStokBarangHistory($id_gudang, $date_filter)
{
	$CI = &get_instance();
	$listGetCategory =	 $CI->db
		->select('
											a.id_material,
											a.qty_stock,
											a.qty_booking,
											b.konversi
										')
		->join('new_inventory_4 b', 'a.id_material=b.id')
		->get_where('warehouse_stock_per_day a', array('a.id_gudang' => $id_gudang, 'DATE(a.hist_date)' => $date_filter))->result_array();
	$ArrGetCategory 	= [];
	foreach ($listGetCategory as $key => $value) {
		$stok_packing = 0;
		if ($value['qty_stock'] > 0 and $value['konversi'] > 0) {
			$stok_packing = $value['qty_stock'] / $value['konversi'];
		}
		$ArrGetCategory[$value['id_material']]['stok'] 	= $value['qty_stock'];
		$ArrGetCategory[$value['id_material']]['stok_packing'] 	= $stok_packing;
		$ArrGetCategory[$value['id_material']]['konversi'] 	= $value['konversi'];
	}
	return $ArrGetCategory;
}

function move_warehouse($ArrUpdateStock = null, $id_gudang_dari = null, $id_gudang_ke = null, $kode_trans = null, $costcenter = null)
{
	$CI 	= &get_instance();
	$dateTime		= date('Y-m-d H:i:s');
	$UserName 		= $CI->auth->user_id();
	$kd_gudang_dari = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_dari));
	$kd_gudang_ke	= (!empty($id_gudang_ke)) ? $id_gudang_ke : $costcenter;
	if ($id_gudang_ke != null) {
		$kd_gudang_ke 	= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_ke));
	}
	//grouping sum
	$temp = [];
	foreach ($ArrUpdateStock as $value) {
		if (!array_key_exists($value['id'], $temp)) {
			$temp[$value['id']] = 0;
		}
		$temp[$value['id']] += $value['qty'];
	}

	$ArrStock = array();
	$ArrHist = array();
	$ArrStockInsert = array();
	$ArrHistInsert = array();

	$ArrStock2 = array();
	$ArrHist2 = array();
	$ArrStockInsert2 = array();
	$ArrHistInsert2 = array();

	foreach ($temp as $key => $value) {
		//PENGURANGAN GUDANG
		$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_dari, 'id_material' => $key))->result();

		if (!empty($rest_pusat)) {
			$ArrStock[$key]['id'] 			= $rest_pusat[0]->id;
			$ArrStock[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock - $value;
			$ArrStock[$key]['update_by'] 	= $UserName;
			$ArrStock[$key]['update_date'] 	= $dateTime;

			$ArrHist[$key]['id_material'] 	= $key;
			$ArrHist[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
			$ArrHist[$key]['id_gudang'] 		= $id_gudang_dari;
			$ArrHist[$key]['kd_gudang'] 		= $kd_gudang_dari;
			$ArrHist[$key]['id_gudang_dari'] 	= $id_gudang_dari;
			$ArrHist[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
			$ArrHist[$key]['id_gudang_ke'] 		= $id_gudang_ke;
			$ArrHist[$key]['kd_gudang_ke'] 		= $kd_gudang_ke;
			$ArrHist[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
			$ArrHist[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock - $value;
			$ArrHist[$key]['qty_booking_awal'] 	= $rest_pusat[0]->qty_booking;
			$ArrHist[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
			$ArrHist[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
			$ArrHist[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
			$ArrHist[$key]['no_ipp'] 			= $kode_trans;
			$ArrHist[$key]['jumlah_mat'] 		= $value;
			$ArrHist[$key]['ket'] 				= 'pengurangan gudang';
			$ArrHist[$key]['update_by'] 		= $UserName;
			$ArrHist[$key]['update_date'] 		= $dateTime;
		} else {
			$restMat	= $CI->db->get_where('new_inventory_4', array('code_lv4' => $key))->result();

			$ArrStockInsert[$key]['id_material'] 	= $key;
			$ArrStockInsert[$key]['nm_material'] 	= $restMat[0]->nama;
			$ArrStockInsert[$key]['id_gudang'] 		= $id_gudang_dari;
			$ArrStockInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
			$ArrStockInsert[$key]['qty_stock'] 		= 0 - $value;
			$ArrStockInsert[$key]['update_by'] 		= $UserName;
			$ArrStockInsert[$key]['update_date'] 	= $dateTime;

			$ArrHistInsert[$key]['id_material'] 	= $key;
			$ArrHistInsert[$key]['nm_material'] 	= $restMat[0]->nama;
			$ArrHistInsert[$key]['id_gudang'] 		= $id_gudang_dari;
			$ArrHistInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
			$ArrHistInsert[$key]['id_gudang_dari'] 	= $id_gudang_dari;
			$ArrHistInsert[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
			$ArrHistInsert[$key]['id_gudang_ke'] 	= $id_gudang_ke;
			$ArrHistInsert[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
			$ArrHistInsert[$key]['qty_stock_awal'] 	    = 0;
			$ArrHistInsert[$key]['qty_stock_akhir']     = 0 - $value;
			$ArrHistInsert[$key]['qty_booking_awal']    = 0;
			$ArrHistInsert[$key]['qty_booking_akhir']   = 0;
			$ArrHistInsert[$key]['qty_rusak_awal'] 	    = 0;
			$ArrHistInsert[$key]['qty_rusak_akhir'] 	= 0;
			$ArrHistInsert[$key]['no_ipp'] 			= $kode_trans;
			$ArrHistInsert[$key]['jumlah_mat'] 		= $value;
			$ArrHistInsert[$key]['ket'] 			= 'pengurangan gudang (insert new)';
			$ArrHistInsert[$key]['update_by'] 		= $UserName;
			$ArrHistInsert[$key]['update_date'] 	= $dateTime;
		}

		//PENAMBAHAN GUDANG
		if ($id_gudang_ke != null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_ke, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock2[$key]['id'] 			= $rest_pusat[0]->id;
				$ArrStock2[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock + $value;
				$ArrStock2[$key]['update_by'] 	=  $UserName;
				$ArrStock2[$key]['update_date'] 	= $dateTime;

				$ArrHist2[$key]['id_material'] 	= $key;
				$ArrHist2[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
				$ArrHist2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHist2[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist2[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHist2[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist2[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock + $value;
				$ArrHist2[$key]['qty_booking_awal'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist2[$key]['jumlah_mat'] 		= $value;
				$ArrHist2[$key]['ket'] 				= 'penambahan gudang';
				$ArrHist2[$key]['update_by'] 		= $UserName;
				$ArrHist2[$key]['update_date'] 		= $dateTime;
			} else {
				$restMat	= $CI->db->get_where('new_inventory_4', array('code_lv4' => $key))->result();

				$ArrStockInsert2[$key]['id_material'] 	= $key;
				$ArrStockInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrStockInsert2[$key]['id_gudang'] 	= $id_gudang_ke;
				$ArrStockInsert2[$key]['kd_gudang'] 	= $kd_gudang_ke;
				$ArrStockInsert2[$key]['qty_stock'] 	= $value;
				$ArrStockInsert2[$key]['update_by'] 	= $UserName;
				$ArrStockInsert2[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert2[$key]['id_material'] 	= $key;
				$ArrHistInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrHistInsert2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHistInsert2[$key]['id_gudang_dari'] = $id_gudang_dari;
				$ArrHistInsert2[$key]['kd_gudang_dari'] = $kd_gudang_dari;
				$ArrHistInsert2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert2[$key]['qty_stock_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_stock_akhir'] 	= $value;
				$ArrHistInsert2[$key]['qty_booking_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_booking_akhir']  = 0;
				$ArrHistInsert2[$key]['qty_rusak_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert2[$key]['jumlah_mat'] 		= $value;
				$ArrHistInsert2[$key]['ket'] 				= 'penambahan gudang (insert new)';
				$ArrHistInsert2[$key]['update_by'] 		    = $UserName;
				$ArrHistInsert2[$key]['update_date'] 		= $dateTime;
			}
		}
	}

	// print_r($ArrStock);
	// print_r($ArrHist);
	// print_r($ArrStockInsert);
	// print_r($ArrHistInsert);
	// print_r($ArrStock2);
	// print_r($ArrHist2);
	// print_r($ArrStockInsert2);
	// print_r($ArrHistInsert2);
	// exit;

	if (!empty($ArrStock)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock, 'id');
	}
	if (!empty($ArrHist)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist);
	}

	if (!empty($ArrStockInsert)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert);
	}
	if (!empty($ArrHistInsert)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert);
	}

	if (!empty($ArrStock2)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock2, 'id');
	}
	if (!empty($ArrHist2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist2);
	}

	if (!empty($ArrStockInsert2)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert2);
	}
	if (!empty($ArrHistInsert2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert2);
	}
}

function move_warehouse_stok($ArrUpdateStock = null, $id_gudang_dari = null, $id_gudang_ke = null, $kode_trans = null, $costcenter = null)
{
	$CI 	= &get_instance();
	$dateTime		= date('Y-m-d H:i:s');
	$UserName 		= $CI->auth->user_id();
	$kd_gudang_dari = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_dari));
	$kd_gudang_ke	= (!empty($id_gudang_ke)) ? $id_gudang_ke : $costcenter;
	if ($id_gudang_ke != null) {
		$kd_gudang_ke 	= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_ke));
	}
	//grouping sum
	$temp = [];
	foreach ($ArrUpdateStock as $value) {
		if (!array_key_exists($value['id'], $temp)) {
			$temp[$value['id']] = 0;
		}
		$temp[$value['id']] += $value['qty'];
	}

	$ArrStock = array();
	$ArrHist = array();
	$ArrStockInsert = array();
	$ArrHistInsert = array();

	$ArrStock2 = array();
	$ArrHist2 = array();
	$ArrStockInsert2 = array();
	$ArrHistInsert2 = array();

	$ArrHistPerDay = array();
	$ArrHistPerDay2 = array();

	foreach ($temp as $key => $value) {
		//PENGURANGAN GUDANG
		if ($id_gudang_dari != null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_dari, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock[$key]['id'] 			= $rest_pusat[0]->id;
				$ArrStock[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock - $value;
				$ArrStock[$key]['update_by'] 	= $UserName;
				$ArrStock[$key]['update_date'] 	= $dateTime;

				$ArrHist[$key]['id_material'] 	= $key;
				$ArrHist[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
				$ArrHist[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrHist[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrHist[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist[$key]['id_gudang_ke'] 		= $id_gudang_ke;
				$ArrHist[$key]['kd_gudang_ke'] 		= $kd_gudang_ke;
				$ArrHist[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock - $value;
				$ArrHist[$key]['qty_booking_awal'] 	= $rest_pusat[0]->qty_booking;
				$ArrHist[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist[$key]['jumlah_mat'] 		= $value;
				$ArrHist[$key]['ket'] 				= 'pengurangan gudang';
				$ArrHist[$key]['update_by'] 		= $UserName;
				$ArrHist[$key]['update_date'] 		= $dateTime;

				$ArrHistPerDay[$key]['id_material'] = $key;
				$ArrHistPerDay[$key]['nm_material'] = $rest_pusat[0]->nm_material;
				$ArrHistPerDay[$key]['id_gudang'] = $id_gudang_dari;
				$ArrHistPerDay[$key]['qty_stock'] = $rest_pusat[0]->qty_stock - $value;
				$ArrHistPerDay[$key]['qty_booking'] = 0;
				$ArrHistPerDay[$key]['qty_rusak'] = 0;
				$ArrHistPerDay[$key]['hist_date'] = date('Y-m-d H:i:s');
			} else {
				$restMat	= $CI->db->get_where('accessories', array('id' => $key))->result();

				$ArrStockInsert[$key]['id_material'] 	= $key;
				$ArrStockInsert[$key]['nm_material'] 	= $restMat[0]->stock_name;
				$ArrStockInsert[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrStockInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrStockInsert[$key]['qty_stock'] 		= 0 - $value;
				$ArrStockInsert[$key]['update_by'] 		= $UserName;
				$ArrStockInsert[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert[$key]['id_material'] 	= $key;
				$ArrHistInsert[$key]['nm_material'] 	= $restMat[0]->stock_name;
				$ArrHistInsert[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrHistInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrHistInsert[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHistInsert[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHistInsert[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert[$key]['qty_stock_awal'] 	    = 0;
				$ArrHistInsert[$key]['qty_stock_akhir']     = 0 - $value;
				$ArrHistInsert[$key]['qty_booking_awal']    = 0;
				$ArrHistInsert[$key]['qty_booking_akhir']   = 0;
				$ArrHistInsert[$key]['qty_rusak_awal'] 	    = 0;
				$ArrHistInsert[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert[$key]['jumlah_mat'] 		= $value;
				$ArrHistInsert[$key]['ket'] 			= 'pengeluaran gudang stok (insert new)';
				$ArrHistInsert[$key]['update_by'] 		= $UserName;
				$ArrHistInsert[$key]['update_date'] 	= $dateTime;


				$ArrHistPerDay[$key]['id_material'] = $key;
				$ArrHistPerDay[$key]['nm_material'] = $restMat[0]->stock_name;
				$ArrHistPerDay[$key]['id_gudang'] = $id_gudang_dari;
				$ArrHistPerDay[$key]['qty_stock'] = 0 - $value;
				$ArrHistPerDay[$key]['qty_booking'] = 0;
				$ArrHistPerDay[$key]['qty_rusak'] = 0;
				$ArrHistPerDay[$key]['hist_date'] = date('Y-m-d H:i:s');
			}
		}

		//PENAMBAHAN GUDANG
		if ($id_gudang_ke !== null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_ke, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock2[$key]['id'] 			= $rest_pusat[0]->id;
				$ArrStock2[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock + $value;
				$ArrStock2[$key]['update_by'] 	=  $UserName;
				$ArrStock2[$key]['update_date'] 	= $dateTime;

				$ArrHist2[$key]['id_material'] 	= $key;
				$ArrHist2[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
				$ArrHist2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHist2[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist2[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHist2[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist2[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock + $value;
				$ArrHist2[$key]['qty_booking_awal'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist2[$key]['jumlah_mat'] 		= $value;
				$ArrHist2[$key]['ket'] 				= 'penambahan gudang';
				$ArrHist2[$key]['update_by'] 		= $UserName;
				$ArrHist2[$key]['update_date'] 		= $dateTime;

				$ArrHistPerDay2[$key]['id_material'] = $key;
				$ArrHistPerDay2[$key]['nm_material'] = $rest_pusat[0]->nm_material;
				$ArrHistPerDay2[$key]['id_gudang'] = $id_gudang_ke;
				$ArrHistPerDay2[$key]['qty_stock'] = $rest_pusat[0]->qty_stock + $value;
				$ArrHistPerDay2[$key]['qty_booking'] = 0;
				$ArrHistPerDay2[$key]['qty_rusak'] = 0;
				$ArrHistPerDay2[$key]['hist_date'] = date('Y-m-d H:i:s');
			} else {
				$restMat	= $CI->db->get_where('accessories', array('id' => $key))->result();

				$ArrStockInsert2[$key]['id_material'] 	= $key;
				$ArrStockInsert2[$key]['nm_material'] 	= $restMat[0]->stock_name;
				$ArrStockInsert2[$key]['id_gudang'] 	= $id_gudang_ke;
				$ArrStockInsert2[$key]['kd_gudang'] 	= $kd_gudang_ke;
				$ArrStockInsert2[$key]['qty_stock'] 	= $value;
				$ArrStockInsert2[$key]['update_by'] 	= $UserName;
				$ArrStockInsert2[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert2[$key]['id_material'] 	= $key;
				$ArrHistInsert2[$key]['nm_material'] 	= $restMat[0]->stock_name;
				$ArrHistInsert2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHistInsert2[$key]['id_gudang_dari'] = $id_gudang_dari;
				$ArrHistInsert2[$key]['kd_gudang_dari'] = $kd_gudang_dari;
				$ArrHistInsert2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert2[$key]['qty_stock_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_stock_akhir'] 	= $value;
				$ArrHistInsert2[$key]['qty_booking_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_booking_akhir']  = 0;
				$ArrHistInsert2[$key]['qty_rusak_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert2[$key]['jumlah_mat'] 		= $value;
				$ArrHistInsert2[$key]['ket'] 				= 'penambahan gudang stok (insert new)';
				$ArrHistInsert2[$key]['update_by'] 		    = $UserName;
				$ArrHistInsert2[$key]['update_date'] 		= $dateTime;

				$ArrHistPerDay2[$key]['id_material'] = $key;
				$ArrHistPerDay2[$key]['nm_material'] = $restMat[0]->stock_name;
				$ArrHistPerDay2[$key]['id_gudang'] = $id_gudang_ke;
				$ArrHistPerDay2[$key]['qty_stock'] = $value;
				$ArrHistPerDay2[$key]['qty_booking'] = 0;
				$ArrHistPerDay2[$key]['qty_rusak'] = 0;
				$ArrHistPerDay2[$key]['hist_date'] = date('Y-m-d H:i:s');
				// print_r($ArrHistPerDay);
			}
		}
	}

	// print_r($ArrStock);
	// print_r($ArrHist);
	// print_r($ArrStockInsert);
	// print_r($ArrHistInsert);
	// print_r($ArrStock2);
	// print_r($ArrHist2);
	// print_r($ArrStockInsert2);
	// print_r($ArrHistInsert2);
	// print_r($ArrHistPerDay);
	// print_r($id_gudang_ke);
	// exit;

	if (!empty($ArrStock)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock, 'id');
	}
	if (!empty($ArrHist)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist);
	}

	if (!empty($ArrStockInsert)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert);
	}
	if (!empty($ArrHistInsert)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert);
	}

	if (!empty($ArrStock2)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock2, 'id');
	}
	if (!empty($ArrHist2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist2);
	}

	if (!empty($ArrStockInsert2)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert2);
	}
	if (!empty($ArrHistInsert2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert2);
	}

	if (!empty($ArrHistPerDay)) {
		$CI->db->insert_batch('warehouse_stock_per_day', $ArrHistPerDay);
	}
	if (!empty($ArrHistPerDay2)) {
		$CI->db->insert_batch('warehouse_stock_per_day', $ArrHistPerDay2);
	}
}

function move_warehouse_adjustment($ArrUpdateStock = null, $id_gudang_dari = null, $id_gudang_ke = null, $kode_trans = null, $keterangan = null)
{
	$CI 	= &get_instance();
	$dateTime		= date('Y-m-d H:i:s');
	$UserName 		= $CI->auth->user_id();

	$kd_gudang_ke 		= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_ke));
	$kd_gudang_dari 	= 'adjustment ' . $keterangan;

	if ($id_gudang_dari != null) {
		$kd_gudang_dari	= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_dari));
	}
	//grouping sum
	$temp = [];
	foreach ($ArrUpdateStock as $value) {
		if (!array_key_exists($value['id'], $temp)) {
			$temp[$value['id']] = 0;
		}
		$temp[$value['id']] += $value['qty'];
	}

	$ArrStock = array();
	$ArrHist = array();
	$ArrStockInsert = array();
	$ArrHistInsert = array();

	$ArrStock2 = array();
	$ArrHist2 = array();
	$ArrStockInsert2 = array();
	$ArrHistInsert2 = array();

	foreach ($temp as $key => $value) {
		//PENGURANGAN GUDANG
		if ($id_gudang_dari != null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_dari, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock[$key]['id'] 			= $rest_pusat[0]->id;
				$ArrStock[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock - $value;
				$ArrStock[$key]['update_by'] 	= $UserName;
				$ArrStock[$key]['update_date'] 	= $dateTime;

				$ArrHist[$key]['id_material'] 	= $key;
				$ArrHist[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
				$ArrHist[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrHist[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrHist[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist[$key]['id_gudang_ke'] 		= $id_gudang_ke;
				$ArrHist[$key]['kd_gudang_ke'] 		= $kd_gudang_ke;
				$ArrHist[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock - $value;
				$ArrHist[$key]['qty_booking_awal'] 	= $rest_pusat[0]->qty_booking;
				$ArrHist[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist[$key]['jumlah_mat'] 		= $value;
				$ArrHist[$key]['ket'] 				= 'pengurangan gudang ' . $keterangan;
				$ArrHist[$key]['update_by'] 		= $UserName;
				$ArrHist[$key]['update_date'] 		= $dateTime;
			} else {
				$restMat	= $CI->db->get_where('new_inventory_4', array('code_lv4' => $key))->result();

				$ArrStockInsert[$key]['id_material'] 	= $key;
				$ArrStockInsert[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrStockInsert[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrStockInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrStockInsert[$key]['qty_stock'] 		= 0 - $value;
				$ArrStockInsert[$key]['update_by'] 		= $UserName;
				$ArrStockInsert[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert[$key]['id_material'] 	= $key;
				$ArrHistInsert[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrHistInsert[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrHistInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrHistInsert[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHistInsert[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHistInsert[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert[$key]['qty_stock_awal'] 	    = 0;
				$ArrHistInsert[$key]['qty_stock_akhir']     = 0 - $value;
				$ArrHistInsert[$key]['qty_booking_awal']    = 0;
				$ArrHistInsert[$key]['qty_booking_akhir']   = 0;
				$ArrHistInsert[$key]['qty_rusak_awal'] 	    = 0;
				$ArrHistInsert[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert[$key]['jumlah_mat'] 		= $value;
				$ArrHistInsert[$key]['ket'] 			= 'pengurangan gudang (insert new) ' . $keterangan;
				$ArrHistInsert[$key]['update_by'] 		= $UserName;
				$ArrHistInsert[$key]['update_date'] 	= $dateTime;
			}
		}

		//PENAMBAHAN GUDANG
		if ($id_gudang_ke != null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_ke, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock2[$key]['id'] 			= $rest_pusat[0]->id;
				if ($keterangan == 'minus') {
					$ArrStock2[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock - $value;
				} else {
					$ArrStock2[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock + $value;
				}
				$ArrStock2[$key]['update_by'] 	=  $UserName;
				$ArrStock2[$key]['update_date'] 	= $dateTime;

				$ArrHist2[$key]['id_material'] 		= $key;
				$ArrHist2[$key]['nm_material'] 		= $rest_pusat[0]->nm_material;
				$ArrHist2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHist2[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist2[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHist2[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				if ($keterangan == 'minus') {
					$ArrHist2[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock - $value;
				} else {
					$ArrHist2[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock + $value;
				}

				$ArrHist2[$key]['qty_booking_awal'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist2[$key]['jumlah_mat'] 		= $value;
				if ($keterangan == 'minus') {
					$ArrHist2[$key]['ket'] 				= 'pengurangan gudang ' . $keterangan;
				} else {
					$ArrHist2[$key]['ket'] 				= 'penambahan gudang ' . $keterangan;
				}

				$ArrHist2[$key]['update_by'] 		= $UserName;
				$ArrHist2[$key]['update_date'] 		= $dateTime;
			} else {
				$restMat	= $CI->db->get_where('new_inventory_4', array('code_lv4' => $key))->result();

				$ArrStockInsert2[$key]['id_material'] 	= $key;
				$ArrStockInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrStockInsert2[$key]['id_gudang'] 	= $id_gudang_ke;
				$ArrStockInsert2[$key]['kd_gudang'] 	= $kd_gudang_ke;
				if ($keterangan == 'minus') {
					$ArrStockInsert2[$key]['qty_stock'] 	= $value * -1;
				} else {
					$ArrStockInsert2[$key]['qty_stock'] 	= $value;
				}

				$ArrStockInsert2[$key]['update_by'] 	= $UserName;
				$ArrStockInsert2[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert2[$key]['id_material'] 	= $key;
				$ArrHistInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrHistInsert2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHistInsert2[$key]['id_gudang_dari'] = $id_gudang_dari;
				$ArrHistInsert2[$key]['kd_gudang_dari'] = $kd_gudang_dari;
				$ArrHistInsert2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert2[$key]['qty_stock_awal'] 	= 0;
				if ($keterangan == 'minus') {
					$ArrHistInsert2[$key]['qty_stock_akhir'] 	= $value * -1;
				} else {
					$ArrHistInsert2[$key]['qty_stock_akhir'] 	= $value;
				}

				$ArrHistInsert2[$key]['qty_booking_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_booking_akhir']  = 0;
				$ArrHistInsert2[$key]['qty_rusak_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert2[$key]['jumlah_mat'] 		= $value;
				if ($keterangan == 'minus') {
					$ArrHistInsert2[$key]['ket'] 				= 'pengurangan gudang (insert new) ' . $keterangan;
				} else {
					$ArrHistInsert2[$key]['ket'] 				= 'penambahan gudang (insert new) ' . $keterangan;
				}

				$ArrHistInsert2[$key]['update_by'] 		    = $UserName;
				$ArrHistInsert2[$key]['update_date'] 		= $dateTime;
			}
		}
	}

	// print_r($ArrStock);
	// print_r($ArrHist);
	// print_r($ArrStockInsert);
	// print_r($ArrHistInsert);
	// print_r($ArrStock2);
	// print_r($ArrHist2);
	// print_r($ArrStockInsert2);
	// print_r($ArrHistInsert2);
	// exit;

	if (!empty($ArrStock)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock, 'id');
	}
	if (!empty($ArrHist)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist);
	}

	if (!empty($ArrStockInsert)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert);
	}
	if (!empty($ArrHistInsert)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert);
	}

	if (!empty($ArrStock2)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock2, 'id');
	}
	if (!empty($ArrHist2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist2);
	}

	if (!empty($ArrStockInsert2)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert2);
	}
	if (!empty($ArrHistInsert2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert2);
	}
}

function move_warehouse_stok_adjustment($ArrUpdateStock = null, $id_gudang_dari = null, $id_gudang_ke = null, $kode_trans = null, $keterangan = null)
{
	$CI 	= &get_instance();
	$dateTime		= date('Y-m-d H:i:s');
	$UserName 		= $CI->auth->user_id();

	$kd_gudang_ke 		= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_ke));
	$kd_gudang_dari 	= 'adjustment ' . $keterangan;

	if ($id_gudang_dari != null) {
		$kd_gudang_dari	= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_dari));
	}
	//grouping sum
	$temp = [];
	foreach ($ArrUpdateStock as $value) {
		if (!array_key_exists($value['id'], $temp)) {
			$temp[$value['id']] = 0;
		}
		$temp[$value['id']] += $value['qty'];
	}

	$ArrStock = array();
	$ArrHist = array();
	$ArrStockInsert = array();
	$ArrHistInsert = array();

	$ArrStock2 = array();
	$ArrHist2 = array();
	$ArrStockInsert2 = array();
	$ArrHistInsert2 = array();

	foreach ($temp as $key => $value) {
		//PENGURANGAN GUDANG
		if ($id_gudang_dari != null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_dari, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock[$key]['id'] 			= $rest_pusat[0]->id;
				$ArrStock[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock - $value;
				$ArrStock[$key]['update_by'] 	= $UserName;
				$ArrStock[$key]['update_date'] 	= $dateTime;

				$ArrHist[$key]['id_material'] 	= $key;
				$ArrHist[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
				$ArrHist[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrHist[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrHist[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist[$key]['id_gudang_ke'] 		= $id_gudang_ke;
				$ArrHist[$key]['kd_gudang_ke'] 		= $kd_gudang_ke;
				$ArrHist[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock - $value;
				$ArrHist[$key]['qty_booking_awal'] 	= $rest_pusat[0]->qty_booking;
				$ArrHist[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist[$key]['jumlah_mat'] 		= $value;
				$ArrHist[$key]['ket'] 				= 'pengurangan gudang ' . $keterangan;
				$ArrHist[$key]['update_by'] 		= $UserName;
				$ArrHist[$key]['update_date'] 		= $dateTime;
			} else {
				$restMat	= $CI->db->select('stock_name AS nama')->get_where('accessories', array('id' => $key))->result();

				$ArrStockInsert[$key]['id_material'] 	= $key;
				$ArrStockInsert[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrStockInsert[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrStockInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrStockInsert[$key]['qty_stock'] 		= 0 - $value;
				$ArrStockInsert[$key]['update_by'] 		= $UserName;
				$ArrStockInsert[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert[$key]['id_material'] 	= $key;
				$ArrHistInsert[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrHistInsert[$key]['id_gudang'] 		= $id_gudang_dari;
				$ArrHistInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
				$ArrHistInsert[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHistInsert[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHistInsert[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert[$key]['qty_stock_awal'] 	    = 0;
				$ArrHistInsert[$key]['qty_stock_akhir']     = 0 - $value;
				$ArrHistInsert[$key]['qty_booking_awal']    = 0;
				$ArrHistInsert[$key]['qty_booking_akhir']   = 0;
				$ArrHistInsert[$key]['qty_rusak_awal'] 	    = 0;
				$ArrHistInsert[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert[$key]['jumlah_mat'] 		= $value;
				$ArrHistInsert[$key]['ket'] 			= 'pengurangan gudang (insert new) ' . $keterangan;
				$ArrHistInsert[$key]['update_by'] 		= $UserName;
				$ArrHistInsert[$key]['update_date'] 	= $dateTime;
			}
		}

		//PENAMBAHAN GUDANG
		if ($id_gudang_ke != null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_ke, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock2[$key]['id'] 			= $rest_pusat[0]->id;
				if ($keterangan == 'minus') {
					$ArrStock2[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock - $value;
				} else {
					$ArrStock2[$key]['qty_stock'] 	= $rest_pusat[0]->qty_stock + $value;
				}
				$ArrStock2[$key]['update_by'] 	=  $UserName;
				$ArrStock2[$key]['update_date'] 	= $dateTime;

				$ArrHist2[$key]['id_material'] 		= $key;
				$ArrHist2[$key]['nm_material'] 		= $rest_pusat[0]->nm_material;
				$ArrHist2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHist2[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist2[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHist2[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				if ($keterangan == 'minus') {
					$ArrHist2[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock - $value;
				} else {
					$ArrHist2[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock + $value;
				}

				$ArrHist2[$key]['qty_booking_awal'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist2[$key]['jumlah_mat'] 		= $value;
				if ($keterangan == 'minus') {
					$ArrHist2[$key]['ket'] 				= 'pengurangan gudang ' . $keterangan;
				} else {
					$ArrHist2[$key]['ket'] 				= 'penambahan gudang ' . $keterangan;
				}

				$ArrHist2[$key]['update_by'] 		= $UserName;
				$ArrHist2[$key]['update_date'] 		= $dateTime;
			} else {
				$restMat	= $CI->db->select('stock_name AS nama')->get_where('accessories', array('id' => $key))->result();

				$ArrStockInsert2[$key]['id_material'] 	= $key;
				$ArrStockInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrStockInsert2[$key]['id_gudang'] 	= $id_gudang_ke;
				$ArrStockInsert2[$key]['kd_gudang'] 	= $kd_gudang_ke;
				if ($keterangan == 'minus') {
					$ArrStockInsert2[$key]['qty_stock'] 	= $value * -1;
				} else {
					$ArrStockInsert2[$key]['qty_stock'] 	= $value;
				}

				$ArrStockInsert2[$key]['update_by'] 	= $UserName;
				$ArrStockInsert2[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert2[$key]['id_material'] 	= $key;
				$ArrHistInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrHistInsert2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHistInsert2[$key]['id_gudang_dari'] = $id_gudang_dari;
				$ArrHistInsert2[$key]['kd_gudang_dari'] = $kd_gudang_dari;
				$ArrHistInsert2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert2[$key]['qty_stock_awal'] 	= 0;
				if ($keterangan == 'minus') {
					$ArrHistInsert2[$key]['qty_stock_akhir'] 	= $value * -1;
				} else {
					$ArrHistInsert2[$key]['qty_stock_akhir'] 	= $value;
				}

				$ArrHistInsert2[$key]['qty_booking_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_booking_akhir']  = 0;
				$ArrHistInsert2[$key]['qty_rusak_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert2[$key]['jumlah_mat'] 		= $value;
				if ($keterangan == 'minus') {
					$ArrHistInsert2[$key]['ket'] 				= 'pengurangan gudang (insert new) ' . $keterangan;
				} else {
					$ArrHistInsert2[$key]['ket'] 				= 'penambahan gudang (insert new) ' . $keterangan;
				}

				$ArrHistInsert2[$key]['update_by'] 		    = $UserName;
				$ArrHistInsert2[$key]['update_date'] 		= $dateTime;
			}
		}
	}

	// print_r($ArrStock);
	// print_r($ArrHist);
	// print_r($ArrStockInsert);
	// print_r($ArrHistInsert);
	// print_r($ArrStock2);
	// print_r($ArrHist2);
	// print_r($ArrStockInsert2);
	// print_r($ArrHistInsert2);
	// exit;

	if (!empty($ArrStock)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock, 'id');
	}
	if (!empty($ArrHist)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist);
	}

	if (!empty($ArrStockInsert)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert);
	}
	if (!empty($ArrHistInsert)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert);
	}

	if (!empty($ArrStock2)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock2, 'id');
	}
	if (!empty($ArrHist2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist2);
	}

	if (!empty($ArrStockInsert2)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert2);
	}
	if (!empty($ArrHistInsert2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert2);
	}
}

function generateNoTransaksi()
{
	$CI = &get_instance();
	$Ym 			= date('ym');
	$srcMtr			= "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRS" . $Ym . "%' ";
	$resultMtr		= $CI->db->query($srcMtr)->result_array();
	$angkaUrut2		= $resultMtr[0]['maxP'];
	$urutan2		= (int)substr($angkaUrut2, 7, 4);
	$urutan2++;
	$urut2			= sprintf('%04s', $urutan2);
	$kode_trans		= "TRS" . $Ym . $urut2;

	return $kode_trans;
}

function generate_no_costbook($lebih = null)
{
	$CI = &get_instance();
	$generate_id = $CI->db->query("SELECT MAX(id) AS max_id FROM tr_cost_book WHERE id LIKE '%CBO-" . date('Y-m-') . "%'")->row();
	$kodeBarang = $generate_id->max_id;
	$urutan = (int) substr($kodeBarang, 13, 5);
	$urutan++;
	if ($lebih !== null) {
		$urutan++;
	}
	$tahun = date('Y-m-');
	$huruf = "CBO-";
	$kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

	return $kodecollect;
}

function generate_no_costbook_in_dari()
{
	$CI = &get_instance();
	$generate_id = $CI->db->query("SELECT MAX(id) AS max_id FROM tr_cost_book WHERE id LIKE '%CID-" . date('Y-m-') . "%'")->row();
	$kodeBarang = $generate_id->max_id;
	$urutan = (int) substr($kodeBarang, 13, 5);
	$urutan++;
	$tahun = date('Y-m-');
	$huruf = "CID-";
	$kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

	return $kodecollect;
}

function generateNoTransaksiStok()
{
	$CI = &get_instance();
	$Ym 			= date('ym');
	$srcMtr			= "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRM" . $Ym . "%' ";
	$resultMtr		= $CI->db->query($srcMtr)->result_array();
	$angkaUrut2		= $resultMtr[0]['maxP'];
	$urutan2		= (int)substr($angkaUrut2, 7, 4);
	$urutan2++;
	$urut2			= sprintf('%04s', $urutan2);
	$kode_trans		= "TRM" . $Ym . $urut2;

	return $kode_trans;
}

function generateNoTransaksiLainnya()
{
	$CI = &get_instance();
	$Ym 			= date('ym');
	$srcMtr			= "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRA" . $Ym . "%' ";
	$resultMtr		= $CI->db->query($srcMtr)->result_array();
	$angkaUrut2		= $resultMtr[0]['maxP'];
	$urutan2		= (int)substr($angkaUrut2, 7, 4);
	$urutan2++;
	$urut2			= sprintf('%04s', $urutan2);
	$kode_trans		= "TRA" . $Ym . $urut2;

	return $kode_trans;
}

function get_product_costing()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('product_price', array('deleted_date' => NULL))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['no_bom']]['pengajuan_price_list'] 	= $value['pengajuan_price_list'];
		$ArrGetCategory[$value['no_bom']]['price_list'] 	= $value['price_list'];
		$ArrGetCategory[$value['no_bom']]['price_list_idr'] 	= $value['price_list_idr'];
		$ArrGetCategory[$value['no_bom']]['kurs'] 	= $value['kurs'];
		$ArrGetCategory[$value['no_bom']]['price_idr'] 	= $value['price_idr'];
		$ArrGetCategory[$value['no_bom']]['price_persen_orindo'] 	= $value['price_persen_orindo'];
		$ArrGetCategory[$value['no_bom']]['price_list_idr_orindo'] 	= $value['price_list_idr_orindo'];
		$ArrGetCategory[$value['no_bom']]['status'] 	= $value['status'];
		$ArrGetCategory[$value['no_bom']]['status_by'] 	= $value['status_by'];
		$ArrGetCategory[$value['no_bom']]['status_date'] 	= $value['status_date'];
		$ArrGetCategory[$value['no_bom']]['reason'] 	= $value['reason'];
		$ArrGetCategory[$value['no_bom']]['sts_logistik'] 	= $value['sts_logistik'];
		$ArrGetCategory[$value['no_bom']]['logistik_by'] 	= $value['logistik_by'];
		$ArrGetCategory[$value['no_bom']]['logistik_date'] 	= $value['logistik_date'];
		$ArrGetCategory[$value['no_bom']]['berat_material'] = $value['berat_material'];
		$ArrGetCategory[$value['no_bom']]['total_price_uj'] = $value['total_price_uj'];
		$ArrGetCategory[$value['no_bom']]['total_idr_uj'] = $value['total_idr_uj'];
		$ArrGetCategory[$value['no_bom']]['selisih_uj'] = $value['selisih_uj'];
	}
	return $ArrGetCategory;
}

function get_quality_control()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('so_internal_product')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$keyUniq = $value['id_key_spk'] . '-' . $value['product_ke'];
		$ArrGetCategory[$keyUniq]['id'] 			= $value['id'];
		$ArrGetCategory[$keyUniq]['status'] 		= $value['status'];
		$ArrGetCategory[$keyUniq]['daycode'] 		= $value['daycode'];
		$ArrGetCategory[$keyUniq]['qc_pass'] 		= $value['qc_pass'];
		$ArrGetCategory[$keyUniq]['note'] 			= $value['note'];
		$ArrGetCategory[$keyUniq]['doc_inspeksi'] 	= $value['doc_inspeksi'];
		$ArrGetCategory[$keyUniq]['sts_print_qr'] 	= $value['sts_print_qr'];
		$ArrGetCategory[$keyUniq]['inspektor'] 	= $value['inspektor'];
	}
	return $ArrGetCategory;
}

function get_quality_control_cutting()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('so_spk_cutting_product')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$keyUniq = $value['kode_det'] . '-' . $value['id_key_spk'] . '-' . $value['product_ke'];
		$ArrGetCategory[$keyUniq]['id'] 			= $value['id'];
		$ArrGetCategory[$keyUniq]['status'] 		= $value['status'];
		$ArrGetCategory[$keyUniq]['daycode'] 		= $value['daycode'];
		$ArrGetCategory[$keyUniq]['qc_pass'] 		= $value['qc_pass'];
		$ArrGetCategory[$keyUniq]['note'] 			= $value['note'];
		$ArrGetCategory[$keyUniq]['doc_inspeksi'] 	= $value['doc_inspeksi'];
		$ArrGetCategory[$keyUniq]['sts_print_qr'] 	= $value['sts_print_qr'];
		$ArrGetCategory[$keyUniq]['inspektor'] 	= $value['inspektor'];
	}
	return $ArrGetCategory;
}

function history_product($ArrUpdateStock = null, $operator = null, $no_dokumen = null, $keterangan = null)
{
	$CI 	= &get_instance();
	$dateTime		= date('Y-m-d H:i:s');
	$UserName 		= $CI->auth->user_id();

	foreach ($ArrUpdateStock as $key => $value) {
		# code...
		$code_lv4 		= $value['code_lv4'];
		$no_bom 		= $value['no_bom'];
		$stok_aktual 	= $value['stok_aktual'];
		$stok_booking 	= $value['stok_booking'];
		$stok_downgrade = $value['stok_downgrade'];
		$qty 			= $value['qty'];

		$checkStock 	= $CI->db->get_where('stock_product', array('code_lv4' => $code_lv4, 'no_bom' => $no_bom))->result_array();

		$ArrStock = array();
		$ArrUpdate = array();
		if (!empty($checkStock)) {
			if ($operator == 'plus') {
				$ArrStock = array(
					'id_key_price' => $checkStock[0]['id'],
					'code_lv4' => $code_lv4,
					'no_bom' => $no_bom,
					'stok_aktual_before' => $checkStock[0]['actual_stock'],
					'stok_aktual_after' => $checkStock[0]['actual_stock'] + $stok_aktual,
					'stok_booking_before' => $checkStock[0]['booking_stock'],
					'stok_booking_after' => $checkStock[0]['booking_stock'] + $stok_booking,
					'stok_downgrade_before' => $checkStock[0]['ng_stock'],
					'stok_downgrade_after' => $checkStock[0]['ng_stock'] + $stok_downgrade,
					'qty' => $qty,
					'no_dokumen' => $no_dokumen,
					'keterangan' => $operator . ' - ' . $keterangan,
					'created_by' => $UserName,
					'created_date' => $dateTime
				);

				$ArrUpdate = array(
					'actual_stock' => $checkStock[0]['actual_stock'] + $stok_aktual,
					'booking_stock' => $checkStock[0]['booking_stock'] + $stok_booking,
					'ng_stock' => $checkStock[0]['ng_stock'] + $stok_downgrade
				);
			} else {
				$ArrStock = array(
					'id_key_price' => $checkStock[0]['id'],
					'code_lv4' => $code_lv4,
					'no_bom' => $no_bom,
					'stok_aktual_before' => $checkStock[0]['actual_stock'],
					'stok_aktual_after' => $checkStock[0]['actual_stock'] - $stok_aktual,
					'stok_booking_before' => $checkStock[0]['booking_stock'],
					'stok_booking_after' => $checkStock[0]['booking_stock'] - $stok_booking,
					'stok_downgrade_before' => $checkStock[0]['ng_stock'],
					'stok_downgrade_after' => $checkStock[0]['ng_stock'] - $stok_downgrade,
					'qty' => $qty,
					'no_dokumen' => $no_dokumen,
					'keterangan' => $operator . ' - ' . $keterangan,
					'created_by' => $UserName,
					'created_date' => $dateTime
				);

				$ArrUpdate = array(
					'actual_stock' => $checkStock[0]['actual_stock'] - $stok_aktual,
					'booking_stock' => $checkStock[0]['booking_stock'] - $stok_booking,
					'ng_stock' => $checkStock[0]['ng_stock'] - $stok_downgrade
				);
			}
		}

		if (!empty($ArrStock)) {
			$CI->db->insert('stock_product_histroy', $ArrStock);
		}

		if (!empty($ArrUpdate)) {
			$CI->db->where('code_lv4', $code_lv4);
			$CI->db->where('no_bom', $no_bom);
			$CI->db->update('stock_product', $ArrUpdate);
		}
	}
}

function checkInputMixing($kode_det)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db
		->select('b.kode_det, a.qty_ke, a.id')
		->group_by('b.kode_det, a.qty_ke')
		->join('so_internal_spk_material b', "a.id_det_spk=b.id AND b.kode_det='" . $kode_det . "' AND b.type_name='mixing' ")
		->get_where('so_internal_spk_material_pengeluaran a')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$keyNew = $value['kode_det'] . '-' . $value['qty_ke'];
		$ArrGetCategory[$keyNew] 	= (!empty($value['id'])) ? TRUE : FALSE;
	}
	return $ArrGetCategory;
}

function get_pr_on_progress()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('id_material, SUM(qty_pr) AS qty_pr, SUM(qty_po) AS qty_po')->group_by('id_material')->get('pr_on_progress')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id_material']] 	= $value['qty_pr'] - $value['qty_po'];
	}
	return $ArrGetCategory;
}

function booking_warehouse($ArrUpdateStock = null, $id_gudang_dari = null, $id_gudang_ke = null, $kode_trans = null, $costcenter = null)
{
	$CI 	= &get_instance();
	$dateTime		= date('Y-m-d H:i:s');
	$UserName 		= $CI->auth->user_id();
	$kd_gudang_dari = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_dari));
	$kd_gudang_ke	= (!empty($id_gudang_ke)) ? $id_gudang_ke : $costcenter;
	if ($id_gudang_ke != null) {
		$kd_gudang_ke 	= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $id_gudang_ke));
	}
	//grouping sum
	$temp = [];
	foreach ($ArrUpdateStock as $value) {
		if (!array_key_exists($value['id'], $temp)) {
			$temp[$value['id']] = 0;
		}
		$temp[$value['id']] += $value['qty'];
	}

	$ArrStock = array();
	$ArrHist = array();
	$ArrStockInsert = array();
	$ArrHistInsert = array();

	$ArrStock2 = array();
	$ArrHist2 = array();
	$ArrStockInsert2 = array();
	$ArrHistInsert2 = array();

	foreach ($temp as $key => $value) {
		//PENGURANGAN GUDANG
		// $rest_pusat = $CI->db->get_where('warehouse_stock',array('id_gudang'=>$id_gudang_dari, 'id_material'=>$key))->result();

		// if(!empty($rest_pusat)){
		// 	$ArrStock[$key]['id'] 			= $rest_pusat[0]->id;
		// 	$ArrStock[$key]['qty_booking'] 	= $rest_pusat[0]->qty_stock - $value;
		// 	$ArrStock[$key]['update_by'] 	= $UserName;
		// 	$ArrStock[$key]['update_date'] 	= $dateTime;

		// 	$ArrHist[$key]['id_material'] 	= $key;
		// 	$ArrHist[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
		// 	$ArrHist[$key]['id_gudang'] 		= $id_gudang_dari;
		// 	$ArrHist[$key]['kd_gudang'] 		= $kd_gudang_dari;
		// 	$ArrHist[$key]['id_gudang_dari'] 	= $id_gudang_dari;
		// 	$ArrHist[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
		// 	$ArrHist[$key]['id_gudang_ke'] 		= $id_gudang_ke;
		// 	$ArrHist[$key]['kd_gudang_ke'] 		= $kd_gudang_ke;
		// 	$ArrHist[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
		// 	$ArrHist[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock;
		// 	$ArrHist[$key]['qty_booking_awal'] 	= $rest_pusat[0]->qty_booking;
		// 	$ArrHist[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking - $value;
		// 	$ArrHist[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
		// 	$ArrHist[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
		// 	$ArrHist[$key]['no_ipp'] 			= $kode_trans;
		// 	$ArrHist[$key]['jumlah_mat'] 		= $value;
		// 	$ArrHist[$key]['ket'] 				= 'pengurangan booking';
		// 	$ArrHist[$key]['update_by'] 		= $UserName;
		// 	$ArrHist[$key]['update_date'] 		= $dateTime;
		// }
		// else{
		// 	$restMat	= $CI->db->get_where('new_inventory_4',array('code_lv4'=>$key))->result();

		// 	$ArrStockInsert[$key]['id_material'] 	= $key;
		// 	$ArrStockInsert[$key]['nm_material'] 	= $restMat[0]->nama;
		// 	$ArrStockInsert[$key]['id_gudang'] 		= $id_gudang_dari;
		// 	$ArrStockInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
		// 	$ArrStockInsert[$key]['qty_booking'] 		= 0 - $value;
		// 	$ArrStockInsert[$key]['update_by'] 		= $UserName;
		// 	$ArrStockInsert[$key]['update_date'] 	= $dateTime;

		// 	$ArrHistInsert[$key]['id_material'] 	= $key;
		// 	$ArrHistInsert[$key]['nm_material'] 	= $restMat[0]->nama;
		// 	$ArrHistInsert[$key]['id_gudang'] 		= $id_gudang_dari;
		// 	$ArrHistInsert[$key]['kd_gudang'] 		= $kd_gudang_dari;
		// 	$ArrHistInsert[$key]['id_gudang_dari'] 	= $id_gudang_dari;
		// 	$ArrHistInsert[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
		// 	$ArrHistInsert[$key]['id_gudang_ke'] 	= $id_gudang_ke;
		// 	$ArrHistInsert[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
		// 	$ArrHistInsert[$key]['qty_stock_awal'] 	    = 0;
		// 	$ArrHistInsert[$key]['qty_stock_akhir']     = 0;
		// 	$ArrHistInsert[$key]['qty_booking_awal']    = 0;
		// 	$ArrHistInsert[$key]['qty_booking_akhir']   = 0 - $value;;
		// 	$ArrHistInsert[$key]['qty_rusak_awal'] 	    = 0;
		// 	$ArrHistInsert[$key]['qty_rusak_akhir'] 	= 0;
		// 	$ArrHistInsert[$key]['no_ipp'] 			= $kode_trans;
		// 	$ArrHistInsert[$key]['jumlah_mat'] 		= $value;
		// 	$ArrHistInsert[$key]['ket'] 			= 'pengurangan booking (insert new)';
		// 	$ArrHistInsert[$key]['update_by'] 		= $UserName;
		// 	$ArrHistInsert[$key]['update_date'] 	= $dateTime;
		// }

		//PENAMBAHAN GUDANG
		if ($id_gudang_ke != null) {
			$rest_pusat = $CI->db->get_where('warehouse_stock', array('id_gudang' => $id_gudang_ke, 'id_material' => $key))->result();

			if (!empty($rest_pusat)) {
				$ArrStock2[$key]['id'] 			= $rest_pusat[0]->id;
				$ArrStock2[$key]['qty_booking'] 	= $rest_pusat[0]->qty_stock + $value;
				$ArrStock2[$key]['update_by'] 	=  $UserName;
				$ArrStock2[$key]['update_date'] 	= $dateTime;

				$ArrHist2[$key]['id_material'] 	= $key;
				$ArrHist2[$key]['nm_material'] 	= $rest_pusat[0]->nm_material;
				$ArrHist2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHist2[$key]['id_gudang_dari'] 	= $id_gudang_dari;
				$ArrHist2[$key]['kd_gudang_dari'] 	= $kd_gudang_dari;
				$ArrHist2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHist2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHist2[$key]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist2[$key]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist2[$key]['qty_booking_awal'] = $rest_pusat[0]->qty_booking;
				$ArrHist2[$key]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking + $value;
				$ArrHist2[$key]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHist2[$key]['jumlah_mat'] 		= $value;
				$ArrHist2[$key]['ket'] 				= 'penambahan booking';
				$ArrHist2[$key]['update_by'] 		= $UserName;
				$ArrHist2[$key]['update_date'] 		= $dateTime;
			} else {
				$restMat	= $CI->db->get_where('new_inventory_4', array('code_lv4' => $key))->result();

				$ArrStockInsert2[$key]['id_material'] 	= $key;
				$ArrStockInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrStockInsert2[$key]['id_gudang'] 	= $id_gudang_ke;
				$ArrStockInsert2[$key]['kd_gudang'] 	= $kd_gudang_ke;
				$ArrStockInsert2[$key]['qty_booking'] 	= $value;
				$ArrStockInsert2[$key]['update_by'] 	= $UserName;
				$ArrStockInsert2[$key]['update_date'] 	= $dateTime;

				$ArrHistInsert2[$key]['id_material'] 	= $key;
				$ArrHistInsert2[$key]['nm_material'] 	= $restMat[0]->nama;
				$ArrHistInsert2[$key]['id_gudang'] 		= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang'] 		= $kd_gudang_ke;
				$ArrHistInsert2[$key]['id_gudang_dari'] = $id_gudang_dari;
				$ArrHistInsert2[$key]['kd_gudang_dari'] = $kd_gudang_dari;
				$ArrHistInsert2[$key]['id_gudang_ke'] 	= $id_gudang_ke;
				$ArrHistInsert2[$key]['kd_gudang_ke'] 	= $kd_gudang_ke;
				$ArrHistInsert2[$key]['qty_stock_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_stock_akhir'] 	= 0;
				$ArrHistInsert2[$key]['qty_booking_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_booking_akhir']  = $value;
				$ArrHistInsert2[$key]['qty_rusak_awal'] 	= 0;
				$ArrHistInsert2[$key]['qty_rusak_akhir'] 	= 0;
				$ArrHistInsert2[$key]['no_ipp'] 			= $kode_trans;
				$ArrHistInsert2[$key]['jumlah_mat'] 		= $value;
				$ArrHistInsert2[$key]['ket'] 				= 'penambahan booking (insert new)';
				$ArrHistInsert2[$key]['update_by'] 		    = $UserName;
				$ArrHistInsert2[$key]['update_date'] 		= $dateTime;
			}
		}
	}

	// print_r($ArrStock);
	// print_r($ArrHist);
	// print_r($ArrStockInsert);
	// print_r($ArrHistInsert);
	// print_r($ArrStock2);
	// print_r($ArrHist2);
	// print_r($ArrStockInsert2);
	// print_r($ArrHistInsert2);
	// exit;

	if (!empty($ArrStock)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock, 'id');
	}
	if (!empty($ArrHist)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist);
	}

	if (!empty($ArrStockInsert)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert);
	}
	if (!empty($ArrHistInsert)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert);
	}

	if (!empty($ArrStock2)) {
		$CI->db->update_batch('warehouse_stock', $ArrStock2, 'id');
	}
	if (!empty($ArrHist2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHist2);
	}

	if (!empty($ArrStockInsert2)) {
		$CI->db->insert_batch('warehouse_stock', $ArrStockInsert2);
	}
	if (!empty($ArrHistInsert2)) {
		$CI->db->insert_batch('warehouse_history', $ArrHistInsert2);
	}
}


function checkInputMixingQty($id)
{
	$CI = &get_instance();
	$SQL = "SELECT
						c.id,
						b.kode_det,
						a.qty_ke 
					FROM
						so_internal_spk_material_pengeluaran a
						LEFT JOIN so_internal_spk_material b ON b.id = a.id_det_spk
						LEFT JOIN so_internal_spk c ON b.kode_det = c.kode_det 
					WHERE
						a.type = 'mixing' AND c.id = '" . $id . "'
					GROUP BY
						a.qty_ke,
						c.id ORDER BY a.id ASC";

	$listCheck = $CI->db->query($SQL)->result_array();

	$temp = [];
	if (!empty($listCheck)) {
		foreach ($listCheck as $key => $value) {
			if (!array_key_exists($value['id'], $temp)) {
				$temp[$value['id']] = 0;
			}
			$temp[$value['id']] += 1;
		}
	}

	return $temp;
}

function checkInputProduksiQty($id)
{
	$CI = &get_instance();
	$SQL = "SELECT
						c.id,
						b.kode_det,
						a.qty_ke 
					FROM
						so_internal_spk_material_pengeluaran a
						LEFT JOIN so_internal_spk_material b ON b.id = a.id_det_spk
						LEFT JOIN so_internal_spk c ON b.kode_det = c.kode_det 
					WHERE
						a.type IS NULL 
						AND a.qty_ke IS NOT NULL 
						AND a.close_date IS NOT NULL
						AND c.id = '" . $id . "'
					GROUP BY
						a.qty_ke,
						c.id ORDER BY a.id ASC";

	$listCheck = $CI->db->query($SQL)->result_array();

	$temp = [];
	if (!empty($listCheck)) {
		foreach ($listCheck as $key => $value) {
			if (!array_key_exists($value['id'], $temp)) {
				$temp[$value['id']] = 0;
			}
			$temp[$value['id']] += 1;
		}
	}

	return $temp;
}

function get_detail_warehouse()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('warehouse')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id']]['nm_gudang'] = $value['nm_gudang'];
	}
	return $ArrGetCategory;
}

function generateNoPR()
{
	$CI = &get_instance();
	$Ym         = date('ym');
	$qIPP       = "SELECT MAX(no_pr) as maxP FROM material_planning_base_on_produksi WHERE no_pr LIKE 'PR" . $Ym . "%' ";
	$resultIPP  = $CI->db->query($qIPP)->result_array();
	$angkaUrut2 = $resultIPP[0]['maxP'];
	$urutan2    = (int)substr($angkaUrut2, 6, 5);
	$urutan2++;
	$urut2      = sprintf('%05s', $urutan2);
	$no_pr      = "PR" . $Ym . $urut2;
	return $no_pr;
}

function getActualFtackle($id)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('id_so_spk, process_name, qty_produksi, SUM(qty) AS qty')->group_by('id_so_spk, process_name')->get_where('so_internal_spk_material_pengeluaran_ftackle', array('id_so_spk' => $id))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$UNIQ = $value['id_so_spk'] . '-' . $value['process_name'];
		$ArrGetCategory[$UNIQ]['qty_produksi'] = $value['qty_produksi'];
		$ArrGetCategory[$UNIQ]['qty'] = $value['qty'];
	}
	return $ArrGetCategory;
}

function getActualFtackleMaterial($id)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('id_so_spk, id_det_spk, SUM(weight_aktual) AS berat')->group_by('id_so_spk, id_det_spk')->get_where('so_internal_spk_material_pengeluaran_ftackle', array('id_so_spk' => $id))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$UNIQ = $value['id_so_spk'] . '-' . $value['id_det_spk'];
		$ArrGetCategory[$UNIQ]['aktual'] = $value['berat'];
	}
	return $ArrGetCategory;
}

function getActualFtackleHistory($id)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db
		->select('
										a.id_so_spk, 
										a.process_name, 
										a.tanggal, 
										a.qty, 
										a.code_material_aktual as id_material, 
										b.nama AS nm_material,
										a.weight_aktual AS berat, 
										a.created_by, 
										a.created_date,
										c.nm_lengkap
										')
		->order_by('a.id', 'asc')
		->join('new_inventory_4 b', 'a.code_material_aktual=b.code_lv4', 'left')
		->join('users c', 'a.created_by=c.id_user', 'left')
		->get_where('so_internal_spk_material_pengeluaran_ftackle a', array('a.id_so_spk' => $id))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$UNIQ = $value['id_so_spk'] . '-' . $value['process_name'];
		$ArrGetCategory[$UNIQ][] = $value;
	}
	return $ArrGetCategory;
}

function get_checksheet()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('checksheet_header')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['code_lv4']] = TRUE;
	}
	return $ArrGetCategory;
}

function get_checksheet_looping()
{
	$ArrGetCategory['hourly'] = 24;
	$ArrGetCategory['day'] = 7;
	$ArrGetCategory['week'] = 5;
	$ArrGetCategory['month'] = 31;
	$ArrGetCategory['year'] = 12;

	return $ArrGetCategory;
}

function get_checksheet_looping_label()
{
	$ArrGetCategory['hourly'] = '';
	$ArrGetCategory['day'] = 'Hari Ke-';
	$ArrGetCategory['week'] = 'Week ';
	$ArrGetCategory['month'] = 'Tgl Ke-';
	$ArrGetCategory['year'] = 'Bln Ke-';

	return $ArrGetCategory;
}

function get_checksheet_value($id_spk)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('so_internal_checksheet', array('id_spk' => $id_spk))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$UNIQ = $value['id_detail'] . '-' . $value['id_kolom'] . '-' . $id_spk . '-' . $value['frequency'] . '-' . $value['qty_ke'];
		$ArrGetCategory[$UNIQ]['id'] = $value['id'];
		$ArrGetCategory[$UNIQ]['ket'] = $value['ket'];
		$ArrGetCategory[$UNIQ]['reason'] = $value['reason'];
		$ArrGetCategory[$UNIQ]['text_kolom'] = $value['text_kolom'];
	}
	return $ArrGetCategory;
}

function get_checksheet_input()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->group_by('id_spk')->get('so_internal_checksheet_new')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[$value['id_spk']] = TRUE;
	}
	return $ArrGetCategory;
}

function get_kebutuhanPerMonth()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('SUM(kebutuhan_month) AS sum_keb, id_barang')->group_by('id_barang')->get('budget_rutin_detail')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$KEY = $value['id_barang'];
		$ArrGetCategory[$KEY]['kebutuhan'] = (!empty($value['sum_keb'])) ? $value['sum_keb'] : 0;
	}
	return $ArrGetCategory;
}

function getPembedaAccessories($id_gudang)
{
	$Category = 0;
	if ($id_gudang == '17') {
		$Category = 'general';
	}
	if ($id_gudang == '19') {
		$Category = 'sparepart';
	}
	if ($id_gudang == '21') {
		$Category = 'atk';
	}
	return $Category;
}

function get_name_product_by_bom($no_bom)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('a.*, b.nama')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.no_bom' => $no_bom))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$variant_product 	= (!empty($value['variant_product'])) ? '; Variant ' . $value['variant_product'] : '';
		$color 				= (!empty($value['color'])) ? '; Color ' . $value['color'] : '';
		$surface 			= (!empty($value['surface'])) ? '; Surface ' . $value['surface'] : '';

		$ArrGetCategory[$value['no_bom']] = $value['nama'] . $variant_product . $color . $surface;
	}
	return $ArrGetCategory;
}

function get_name_product_by_bom_all()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->select('a.*, b.nama')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get('bom_header a')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$variant_product 	= (!empty($value['variant_product'])) ? '; Variant ' . $value['variant_product'] : '';
		$color 				= (!empty($value['color'])) ? '; Color ' . $value['color'] : '';
		$surface 			= (!empty($value['surface'])) ? '; Surface ' . $value['surface'] : '';

		$ArrGetCategory[$value['no_bom']] = $value['nama'] . $variant_product . $color . $surface;
	}
	return $ArrGetCategory;
}

function getValueChecksheet($code_lv4)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('checksheet_detail_new', array('code_lv4' => $code_lv4))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$keyUniq = $value['id_checksheet'];
		$ArrGetCategory[$keyUniq]['id'] 			= $value['id'];
		$ArrGetCategory[$keyUniq]['id_checksheet'] 	= $value['id_checksheet'];
		$ArrGetCategory[$keyUniq]['surface'] 		= $value['surface'];
		$ArrGetCategory[$keyUniq]['rooving'] 		= $value['rooving'];
		$ArrGetCategory[$keyUniq]['matt_atas'] 		= $value['matt_atas'];
		$ArrGetCategory[$keyUniq]['matt_bawah'] 	= $value['matt_bawah'];
		$ArrGetCategory[$keyUniq]['matt_kiri'] 		= $value['matt_kiri'];
		$ArrGetCategory[$keyUniq]['matt_kanan'] 	= $value['matt_kanan'];
		$ArrGetCategory[$keyUniq]['display1'] 	= $value['display1'];
		$ArrGetCategory[$keyUniq]['display2'] 	= $value['display2'];
		$ArrGetCategory[$keyUniq]['display3'] 	= $value['display3'];
		$ArrGetCategory[$keyUniq]['dies1'] 	= $value['dies1'];
		$ArrGetCategory[$keyUniq]['dies2'] 	= $value['dies2'];
		$ArrGetCategory[$keyUniq]['dies3'] 	= $value['dies3'];
		$ArrGetCategory[$keyUniq]['speed'] 	= $value['speed'];
	}
	return $ArrGetCategory;
}

function getValueChecksheetInputProduksi($id_spk, $qty_ke)
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get_where('so_internal_checksheet_new', array('id_spk' => $id_spk, 'qty_ke' => $qty_ke))->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$keyUniq = $value['id_checksheet'];
		$ArrGetCategory[$keyUniq]['id'] 			= $value['id'];
		$ArrGetCategory[$keyUniq]['id_checksheet'] 	= $value['id_checksheet'];
		$ArrGetCategory[$keyUniq]['surface'] 		= $value['surface'];
		$ArrGetCategory[$keyUniq]['rooving'] 		= $value['rooving'];
		$ArrGetCategory[$keyUniq]['matt_atas'] 		= $value['matt_atas'];
		$ArrGetCategory[$keyUniq]['matt_bawah'] 	= $value['matt_bawah'];
		$ArrGetCategory[$keyUniq]['matt_kiri'] 		= $value['matt_kiri'];
		$ArrGetCategory[$keyUniq]['matt_kanan'] 	= $value['matt_kanan'];
		$ArrGetCategory[$keyUniq]['display1'] 	= $value['display1'];
		$ArrGetCategory[$keyUniq]['display2'] 	= $value['display2'];
		$ArrGetCategory[$keyUniq]['display3'] 	= $value['display3'];
		$ArrGetCategory[$keyUniq]['dies1'] 	= $value['dies1'];
		$ArrGetCategory[$keyUniq]['dies2'] 	= $value['dies2'];
		$ArrGetCategory[$keyUniq]['dies3'] 	= $value['dies3'];
		$ArrGetCategory[$keyUniq]['speed'] 	= $value['speed'];
	}
	return $ArrGetCategory;
}

function insert_br_every_50_words($inputString)
{
	// Split the string into words
	$words = str_word_count($inputString, 1);

	// Initialize an empty result string
	$result = '';

	// Counter for words
	$wordCount = 0;

	// Iterate through each word
	foreach ($words as $word) {
		// Append the word to the result string
		$result .= $word . ' ';

		// Increment word count
		$wordCount++;

		// Check if 50 words reached
		if ($wordCount % 50 == 0) {
			// Append <br> tag to create a line break
			$result .= "<br>";
		}
	}

	// Return the modified string
	return $result;
}

function insert_stock_per_day($id_material, $nm_material, $id_gudang, $qty_stock = 0, $qty_booking = 0, $qty_rusak = 0)
{
	$CI 	= &get_instance();

	$CI->db->insert('warehouse_stock_per_day', [
		'id_material' => $id_material,
		'nm_material' => $nm_material,
		'id_gudang' => $id_gudang,
		'qty_stock' => $qty_stock,
		'qty_booking' => $qty_booking,
		'qty_rusak' => $qty_rusak,
		'hist_date' => date('Y-m-d H:i:s')
	]);
}

if (!function_exists('int_to_roman')) {
	function int_to_roman($integer)
	{
		$map = [
			'M' => 1000,
			'CM' => 900,
			'D' => 500,
			'CD' => 400,
			'C' => 100,
			'XC' => 90,
			'L' => 50,
			'XL' => 40,
			'X' => 10,
			'IX' => 9,
			'V' => 5,
			'IV' => 4,
			'I' => 1
		];

		$roman = '';
		while ($integer > 0) {
			foreach ($map as $roman_digit => $value) {
				if ($integer >= $value) {
					$integer -= $value;
					$roman .= $roman_digit;
					break;
				}
			}
		}

		return $roman;
	}
}

if (!function_exists('number_to_words')) {
	function number_to_words($number)
	{
		$units = [
			0 => 'nol',
			1 => 'satu',
			2 => 'dua',
			3 => 'tiga',
			4 => 'empat',
			5 => 'lima',
			6 => 'enam',
			7 => 'tujuh',
			8 => 'delapan',
			9 => 'sembilan'
		];

		$teens = [
			10 => 'sepuluh',
			11 => 'sebelas',
			12 => 'dua belas',
			13 => 'tiga belas',
			14 => 'empat belas',
			15 => 'lima belas',
			16 => 'enam belas',
			17 => 'tujuh belas',
			18 => 'delapan belas',
			19 => 'sembilan belas'
		];

		$tens = [
			20 => 'dua puluh',
			30 => 'tiga puluh',
			40 => 'empat puluh',
			50 => 'lima puluh',
			60 => 'enam puluh',
			70 => 'tujuh puluh',
			80 => 'delapan puluh',
			90 => 'sembilan puluh'
		];

		$thousands = [
			100 => 'seratus',
			1000 => 'seribu'
		];

		$large_numbers = [
			1000000 => 'juta',
			1000000000 => 'miliar',
			1000000000000 => 'triliun'
		];

		if ($number < 10) {
			return $units[$number];
		} elseif ($number < 20) {
			return $teens[$number];
		} elseif ($number < 100) {
			$unit = $number % 10;
			$ten = (int)($number / 10) * 10;
			return $tens[$ten] . ($unit ? ' ' . $units[$unit] : '');
		} elseif ($number < 1000) {
			$hundred = (int)($number / 100);
			$rest = $number % 100;
			return ($hundred > 1 ? $units[$hundred] . ' ratus' : 'seratus') . ($rest ? ' ' . number_to_words($rest) : '');
		} elseif ($number < 1000000) {
			$thousand = (int)($number / 1000);
			$rest = $number % 1000;
			return ($thousand > 1 ? number_to_words($thousand) . ' ribu' : 'seribu') . ($rest ? ' ' . number_to_words($rest) : '');
		} else {
			foreach ($large_numbers as $value => $word) {
				if ($number < $value * 1000) {
					$quotient = (int)($number / $value);
					$remainder = $number % $value;
					return number_to_words($quotient) . ' ' . $word . ($remainder ? ' ' . number_to_words($remainder) : '');
				}
			}
		}

		return 'angka terlalu besar';
	}
}

if (!function_exists('rupiah_to_words')) {
	function rupiah_to_words($amount)
	{
		$amount = number_format($amount, 2, '.', '');
		list($integer, $decimal) = explode('.', $amount);

		$words = number_to_words((int)$integer);
		$words .= ' ';
		if ($decimal > 0) {
			$words .= ' dan ' . number_to_words((int)$decimal) . ' sen';
		}

		return $words;
	}
}

function insert_jurnal_department($ArrData, $GudangFrom, $GudangTo, $kode_trans, $category, $ket_min, $ket_plus)
{
	$CI     = &get_instance();
	$UserName        = $CI->auth->user_name();
	$DateTime        = date('Y-m-d H:i:s');

	$getHeaderAdjust = $CI->db->get_where('warehouse_adjustment', array('kode_trans' => $kode_trans))->result();
	$DATE_JURNAL = (!empty($getHeaderAdjust[0]->tanggal)) ? $getHeaderAdjust[0]->tanggal : $getHeaderAdjust[0]->created_date;

	$SUM_PRICE = 0;
	$ArrDetail = [];
	$ArrDetailNew = [];
	foreach ($ArrData as $key => $value) {
		$PRICE     = $value['unit_price'];
		$QTY     = $value['qty'];
		$TOTAL     = $PRICE * $QTY;
		$SUM_PRICE += $TOTAL;

		$ArrDetail[$key]['kode_trans']         = $kode_trans;
		$ArrDetail[$key]['id_material']     = $value['id_barang'];
		$ArrDetail[$key]['price_book']         = $PRICE;
		$ArrDetail[$key]['berat']             = $QTY;
		$ArrDetail[$key]['amount']             = $TOTAL;
		$ArrDetail[$key]['updated_by']         = $UserName;
		$ArrDetail[$key]['updated_date']     = $DateTime;

		$ArrDetailNew[$key]['kode_trans']     = $kode_trans;
		$ArrDetailNew[$key]['no_ipp']         = $value['no_po'];
		$ArrDetailNew[$key]['category']     = $category;
		$ArrDetailNew[$key]['gudang_dari']     = $GudangFrom;
		$ArrDetailNew[$key]['gudang_ke']     = $GudangTo;
		$ArrDetailNew[$key]['tanggal']         = date('Y-m-d', strtotime($DATE_JURNAL));
		$ArrDetailNew[$key]['id_material']     = $value['id_barang'];
		$ArrDetailNew[$key]['nm_material']     = $value['nm_barang'];
		$ArrDetailNew[$key]['cost_book']     = $PRICE;
		$ArrDetailNew[$key]['qty']             = $QTY;
		$ArrDetailNew[$key]['total_nilai']     = $TOTAL;
		$ArrDetailNew[$key]['created_by']     = $UserName;
		$ArrDetailNew[$key]['created_date'] = $DateTime;
	}

	//DEBET
	$ArrJurnal[0]['category'] = $category;
	$ArrJurnal[0]['posisi'] = 'DEBIT';
	$ArrJurnal[0]['amount'] = $SUM_PRICE;
	$ArrJurnal[0]['gudang'] = $GudangTo;
	$ArrJurnal[0]['keterangan'] = $ket_plus;
	$ArrJurnal[0]['kode_trans'] = $kode_trans;
	$ArrJurnal[0]['updated_by'] = $UserName;
	$ArrJurnal[0]['updated_date'] = $DateTime;

	//KREDIT
	$ArrJurnal[1]['category'] = $category;
	$ArrJurnal[1]['posisi'] = 'KREDIT';
	$ArrJurnal[1]['amount'] = $SUM_PRICE;
	$ArrJurnal[1]['gudang'] = $GudangFrom;
	$ArrJurnal[1]['keterangan'] = $ket_min;
	$ArrJurnal[1]['kode_trans'] = $kode_trans;
	$ArrJurnal[1]['updated_by'] = $UserName;
	$ArrJurnal[1]['updated_date'] = $DateTime;

	$CI->db->insert_batch('jurnal_temp', $ArrJurnal);
	$CI->db->insert_batch('jurnal_temp_detail', $ArrDetail);
	$CI->db->insert_batch('jurnal', $ArrDetailNew);
	// if ($category == 'incoming department') {
	//     auto_jurnal_product($kode_trans, $category);
	// }
	// if ($category == 'incoming asset') {
	//     auto_jurnal_product($kode_trans, $category);
	// }
}

function get_detail_user()
{
	$CI = &get_instance();
	$listGetCategory = $CI->db->get('users')->result_array();
	$ArrGetCategory = [];
	foreach ($listGetCategory as $key => $value) {
		$ArrGetCategory[strtolower($value['username'])]['nm_lengkap'] = strtolower($value['nm_lengkap']);
	}
	return $ArrGetCategory;
}

function Dekripsi_base64($sData)
{
	$sBase64 = strtr($sData, '-_', '+/');
	return base64_decode($sBase64);
}

function Dekripsi($sData, $sKey = '200881173_limchaemoo')
{
	$sResult = '';
	$sData   = Dekripsi_base64($sData);
	for ($i = 0; $i < strlen($sData); $i++) {
		$sChar    = substr($sData, $i, 1);
		$sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
		$sChar    = chr(ord($sChar) - ord($sKeyChar));
		$sResult .= $sChar;
	}
	return $sResult;
}

// function auto_jurnal_product($ArrDetailProduct, $ket)
// {
//     $CI     = &get_instance();
//     $CI->load->model('Jurnal_model');
//     $CI->load->model('Acc_model');
//     $data_session    = $CI->session->userdata;
//     $UserName        = $data_session['ORI_User']['username'];
//     $DateTime        = date('Y-m-d H:i:s');


//     if ($ket == 'WIP - FINISH GOOD') {
//         $kodejurnal = 'JV005';
//         foreach ($ArrDetailProduct as $keys => $values) {
//             $id = $values['id_detail'];
//             $datajurnal = $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.category='quality control' and a.status_jurnal='0' and a.id_detail ='$id' limit 1")->row();
//             $id = $datajurnal->id;
//             $tgl_voucher = $datajurnal->tanggal;
//             $no_request = $id;

//             //$datasodetailheader = $CI->db->query("SELECT * FROM so_detail_header WHERE id ='".$datajurnal->id_milik."' limit 1" )->row();
//             $datasodetailheader = $CI->db->query("SELECT * FROM laporan_per_hari_action WHERE id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();

//             // print_r($datajurnal->id_milik);
//             // exit;


//             $kurs = 1;
//             $sqlkurs = "select * from ms_kurs where tanggal <='" . $datajurnal->tanggal . "' and mata_uang='USD' order by tanggal desc limit 1";
//             $dtkurs    = $CI->db->query($sqlkurs)->row();
//             if (!empty($dtkurs)) $kurs = $dtkurs->kurs;
//             $data_pro_det = $CI->db->query("SELECT * FROM production_detail WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();
//             $dataprodet = "";
//             if (!empty($data_pro_det)) {
//                 if ($data_pro_det->finish_good > 0) {
//                     $dataprodet = $data_pro_det->id;
//                     $wip_material = $data_pro_det->wip_material;
//                     $pe_direct_labour = $data_pro_det->wip_dl;
//                     $foh = $data_pro_det->wip_foh;
//                     $pe_indirect_labour = $data_pro_det->wip_il;
//                     $pe_consumable = $data_pro_det->wip_consumable;
//                     $finish_good = $data_pro_det->finish_good;
//                 }
//             }
//             if ($dataprodet == "") {
//                 $wip_material = $datajurnal->total_nilai;
//                 $pe_direct_labour = (($datasodetailheader->direct_labour * $datasodetailheader->man_hours) * $kurs);
//                 $pe_indirect_labour = (($datasodetailheader->indirect_labour * $datasodetailheader->man_hours) * $kurs);
//                 $foh = (($datasodetailheader->machine + $datasodetailheader->mould_mandrill + $datasodetailheader->foh_depresiasi + $datasodetailheader->biaya_rutin_bulanan + $datasodetailheader->foh_consumable) * $kurs);
//                 $pe_consumable = ($datasodetailheader->consumable * $kurs);
//                 $finish_good = ($wip_material + $pe_direct_labour + $foh + $pe_indirect_labour + $pe_consumable);

//                 $CI->db->query("update production_detail set wip_kurs='" . $kurs . "', wip_material='" . $wip_material . "' , wip_dl='" . $pe_direct_labour . "' , wip_foh='" . $foh . "', wip_il='" . $pe_indirect_labour . "', wip_consumable='" . $pe_consumable . "', finish_good='" . $finish_good . "' WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1");
//             }
//             $masterjurnal    = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//             $totaldebit = 0;
//             $totalkredit = 0;
//             $coa_cogm = '';
//             $no_spk = $datajurnal->id_spk;
//             $det_Jurnaltes = [];
//             foreach ($masterjurnal as $record) {
//                 $debit = 0;
//                 $kredit = 0;
//                 $nokir      = $record->no_perkiraan;
//                 $posisi     = $record->posisi;
//                 $parameter  = $record->parameter_no;
//                 $keterangan = $record->keterangan;
//                 if ($parameter == '1') {
//                     $debit = ($wip_material);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '2') {
//                     $debit = ($pe_direct_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '3') {
//                     $debit = ($pe_indirect_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '4') {
//                     $debit = ($pe_consumable);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => $debit,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '5') {
//                     $debit = ($foh);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => ($debit),
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '6') {
//                     $kredit = ($wip_material);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '7') {
//                     $kredit = ($pe_direct_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '8') {
//                     $kredit = ($pe_indirect_labour);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '9') {
//                     $kredit = ($pe_consumable);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '10') {
//                     $kredit = ($foh);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $datajurnal->coa,
//                         'keterangan'    => $keterangan . ' ' . $datajurnal->id_spk,
//                         'no_reff'       => $id,
//                         'debet'         => 0,
//                         'kredit'        => $kredit,
//                         'jenis_jurnal'  => 'wip finishgood',
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//                 if ($parameter == '11') {
//                     $coa_cogm = $nokir;
//                 }
//                 $totaldebit += $debit;
//                 $totalkredit += $kredit;
//             }
//             $Keterangan_INV = ($ket) . ' (' . $datajurnal->no_so . ' - ' . $datajurnal->product . ' - ' . $no_spk . ')';
//             $nilaibayar = $datajurnal->total_nilai;
//             $det_Jurnaltes[]  = array(
//                 'nomor'         => '',
//                 'tanggal'       => $tgl_voucher,
//                 'tipe'          => 'JV',
//                 'no_perkiraan'  => $coa_cogm,
//                 'keterangan'    => $Keterangan_INV,
//                 'no_reff'       => $id,
//                 'debet'         => 0,
//                 'kredit'        => $totalkredit,
//                 'jenis_jurnal'  => 'wip finishgood',
//                 'no_request'    => $no_request,
//                 'stspos'        => 1
//             );
//             $det_Jurnaltes[]  = array(
//                 'nomor'         => '',
//                 'tanggal'       => $tgl_voucher,
//                 'tipe'          => 'JV',
//                 'no_perkiraan'  => $datajurnal->coa_fg,
//                 'keterangan'    => $Keterangan_INV,
//                 'no_reff'       => $id,
//                 'debet'         => $totaldebit,
//                 'kredit'        => 0,
//                 'jenis_jurnal'  => 'wip finishgood',
//                 'no_request'    => $no_request,
//                 'stspos'        => 1
//             );
//             $CI->db->query("delete from jurnaltras WHERE jenis_jurnal='wip finishgood' and no_reff ='$id'");
//             $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//             $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//             $Bln    = substr($tgl_voucher, 5, 2);
//             $Thn    = substr($tgl_voucher, 0, 4);
//             $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalkredit, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV . '-' . $id, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//             $CI->db->insert(DBACC . '.javh', $dataJVhead);
//             $datadetail = array();
//             foreach ($det_Jurnaltes as $vals) {
//                 $datadetail = array(
//                     'tipe'            => 'JV',
//                     'nomor'            => $Nomor_JV,
//                     'tanggal'        => $tgl_voucher,
//                     'no_perkiraan'    => $vals['no_perkiraan'],
//                     'keterangan'    => $vals['keterangan'],
//                     'no_reff'        => $vals['no_reff'],
//                     'debet'            => $vals['debet'],
//                     'kredit'        => $vals['kredit'],
//                 );
//                 $CI->db->insert(DBACC . '.jurnal', $datadetail);
//             }
//             $CI->db->query("UPDATE jurnal_product SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE id ='$id'");
//             unset($det_Jurnaltes);
//             unset($datadetail);
//         }
//     }
//     if ($ket == 'FINISH GOOD - TRANSIT') {
//         $kodejurnal = 'JV006';
//         foreach ($ArrDetailProduct as $keys => $values) {
//             $id = $values['id_detail'];
//             $datajurnal = $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.category='delivery' and a.status_jurnal='0' and a.id_detail ='$id' limit 1")->row();
//             $id = $datajurnal->id;
//             $tgl_voucher = $datajurnal->tanggal;
//             $no_request = $id;

//             $dataproductiondetail = $CI->db->query("select * from production_detail where id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();
//             if ($dataproductiondetail->finish_good == 0) {
//                 //$datasodetailheader = $CI->db->query("SELECT * FROM so_detail_header WHERE id ='".$datajurnal->id_milik."' limit 1" )->row();
//                 $datasodetailheader = $CI->db->query("SELECT * FROM laporan_per_hari_action WHERE id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();


//                 $kurs = 1;
//                 $sqlkurs = "select * from ms_kurs where tanggal <='" . $datajurnal->tanggal . "' and mata_uang='USD' order by tanggal desc limit 1";
//                 $dtkurs    = $CI->db->query($sqlkurs)->row();
//                 if (!empty($dtkurs)) $kurs = $dtkurs->kurs;
//                 $wip_material = $datajurnal->total_nilai;
//                 $pe_direct_labour = (($datasodetailheader->direct_labour * $datasodetailheader->man_hours) * $kurs);
//                 $pe_indirect_labour = (($datasodetailheader->indirect_labour * $datasodetailheader->man_hours) * $kurs);
//                 $foh = (($datasodetailheader->machine + $datasodetailheader->mould_mandrill + $datasodetailheader->foh_depresiasi + $datasodetailheader->biaya_rutin_bulanan + $datasodetailheader->foh_consumable) * $kurs);
//                 $pe_consumable = ($datasodetailheader->consumable * $kurs);
//                 $finish_good = ($wip_material + $pe_direct_labour + $foh + $pe_indirect_labour + $pe_consumable);

//                 $CI->db->query("update production_detail set wip_kurs='" . $kurs . "', wip_material='" . $wip_material . "' , wip_dl='" . $pe_direct_labour . "' , wip_foh='" . $foh . "', wip_il='" . $pe_indirect_labour . "', wip_consumable='" . $pe_consumable . "', finish_good='" . $finish_good . "' WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1");

//                 $totalall = $finish_good;
//             } else {
//                 $totalall = $dataproductiondetail->finish_good;
//             }
//             $no_spk = $datajurnal->id_spk;
//             $Keterangan_INV = ($ket) . ' (' . $datajurnal->no_so . ' - ' . $datajurnal->product . ' - ' . $no_spk . ' - ' . $datajurnal->no_surat_jalan . ')';
//             $datajurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//             $det_Jurnaltes = [];
//             foreach ($datajurnal as $record) {
//                 $tabel  = $record->menu;
//                 $posisi = $record->posisi;
//                 $field  = $record->field;
//                 $nokir  = $record->no_perkiraan;

//                 $totalall2 = (!empty($totalall)) ? $totalall : 0;
//                 $param  = 'id';
//                 if ($posisi == 'D') {
//                     $value_param  = $id;
//                     $val = $CI->Acc_model->GetData($tabel, $field, $param, $value_param);
//                     $nilaibayar = $val[0]->$field;
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $Keterangan_INV,
//                         'no_reff'       => $no_request,
//                         'debet'         => $totalall2,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => 'finish good intransit',
//                         'no_request'    => $no_request,
//                         'stspos'        => 1
//                     );
//                 } elseif ($posisi == 'K') {
//                     $coa =     $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.id ='$id'")->result();
//                     $nokir = $coa[0]->coa_fg;
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $nokir,
//                         'keterangan'    => $Keterangan_INV,
//                         'no_reff'       => $no_request,
//                         'debet'         => 0,
//                         'kredit'        => $totalall2,
//                         'jenis_jurnal'  => 'finish good intransit',
//                         'no_request'    => $no_request,
//                         'stspos'        => 1
//                     );
//                 }
//             }
//             $CI->db->query("delete from jurnaltras WHERE jenis_jurnal='finish good intransit' and no_reff ='$id'");
//             $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//             $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//             $Bln    = substr($tgl_voucher, 5, 2);
//             $Thn    = substr($tgl_voucher, 0, 4);
//             $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall2, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV . '-' . $id, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//             $CI->db->insert(DBACC . '.javh', $dataJVhead);
//             $datadetail = array();
//             foreach ($det_Jurnaltes as $vals) {
//                 $datadetail = array(
//                     'tipe'            => 'JV',
//                     'nomor'            => $Nomor_JV,
//                     'tanggal'        => $tgl_voucher,
//                     'no_perkiraan'    => $vals['no_perkiraan'],
//                     'keterangan'    => $vals['keterangan'],
//                     'no_reff'        => $vals['no_reff'],
//                     'debet'            => $vals['debet'],
//                     'kredit'        => $vals['kredit'],
//                 );
//                 $CI->db->insert(DBACC . '.jurnal', $datadetail);
//             }
//             $CI->db->query("UPDATE jurnal_product SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE id ='$id'");
//             unset($det_Jurnaltes);
//             unset($datadetail);
//         }
//     }
//     if ($ket == 'TRANSIT - CUSTOMER') {
//         $kodejurnal = 'JV007';
//         foreach ($ArrDetailProduct as $keys => $values) {
//             $id = $values['id_detail'];

//             $datajurnal = $CI->db->query("SELECT a.*, b.coa,b.coa_fg FROM jurnal_product a join product_parent b on a.product=b.product_parent WHERE a.category='diterima customer' and a.status_jurnal='0' and a.id_detail ='$id' limit 1")->row();
//             $id = (!empty($datajurnal->id)) ? $datajurnal->id : 0;
//             $tgl_voucher = (!empty($datajurnal->tanggal)) ? $datajurnal->tanggal : date('Y-m-d');
//             $no_request = $id;

//             $id_detail = (!empty($datajurnal->id_detail)) ? $datajurnal->id_detail : 0;
//             $id_milik = (!empty($datajurnal->id_milik)) ? $datajurnal->id_milik : 0;
//             $total_nilai = (!empty($datajurnal->total_nilai)) ? $datajurnal->total_nilai : 0;
//             $id_spk = (!empty($datajurnal->id_spk)) ? $datajurnal->id_spk : 0;
//             $no_so = (!empty($datajurnal->no_so)) ? $datajurnal->no_so : 0;
//             $product = (!empty($datajurnal->product)) ? $datajurnal->product : 0;
//             $no_surat_jalan = (!empty($datajurnal->no_surat_jalan)) ? $datajurnal->no_surat_jalan : 0;

//             $dataproductiondetail = $CI->db->query("select * from production_detail where id='" . $id_detail . "' and id_milik ='" . $id_milik . "' limit 1")->row();


//             if (!empty($dataproductiondetail->finish_good)) {
//                 if ($dataproductiondetail->finish_good == 0) {
//                     $datasodetailheader = $CI->db->query("SELECT * FROM laporan_per_hari_action WHERE id_milik ='" . $datajurnal->id_milik . "' limit 1")->row();


//                     $kurs = 1;
//                     $sqlkurs = "select * from ms_kurs where tanggal <='" . $datajurnal->tanggal . "' and mata_uang='USD' order by tanggal desc limit 1";
//                     $dtkurs    = $CI->db->query($sqlkurs)->row();
//                     if (!empty($dtkurs)) $kurs = $dtkurs->kurs;
//                     $wip_material = $datajurnal->total_nilai;
//                     $pe_direct_labour = (($datasodetailheader->direct_labour * $datasodetailheader->man_hours) * $kurs);
//                     $pe_indirect_labour = (($datasodetailheader->indirect_labour * $datasodetailheader->man_hours) * $kurs);
//                     $foh = (($datasodetailheader->machine + $datasodetailheader->mould_mandrill + $datasodetailheader->foh_depresiasi + $datasodetailheader->biaya_rutin_bulanan + $datasodetailheader->foh_consumable) * $kurs);
//                     $pe_consumable = ($datasodetailheader->consumable * $kurs);
//                     $finish_good = ($wip_material + $pe_direct_labour + $foh + $pe_indirect_labour + $pe_consumable);

//                     $CI->db->query("update production_detail set wip_kurs='" . $kurs . "', wip_material='" . $wip_material . "' , wip_dl='" . $pe_direct_labour . "' , wip_foh='" . $foh . "', wip_il='" . $pe_indirect_labour . "', wip_consumable='" . $pe_consumable . "', finish_good='" . $finish_good . "' WHERE id='" . $datajurnal->id_detail . "' and id_milik ='" . $datajurnal->id_milik . "' limit 1");

//                     $totalall = $finish_good;
//                 } else {
//                     $totalall = (!empty($dataproductiondetail->finish_good)) ? $dataproductiondetail->finish_good : 0;
//                 }
//             }

//             // print_r($totalall);
//             // exit;

//             $no_spk = $id_spk;
//             $Keterangan_INV = ($ket) . ' (' . $no_so . ' - ' . $product . ' - ' . $no_spk . ' - ' . $no_surat_jalan . ')';
//             $datajurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//             $det_Jurnaltes = [];
//             if (!empty($datajurnal)) {
//                 foreach ($datajurnal as $record) {
//                     $tabel  = $record->menu;
//                     $posisi = $record->posisi;
//                     $field  = $record->field;
//                     $nokir  = $record->no_perkiraan;
//                     $totalall2 = (!empty($totalall)) ? $totalall : 0;
//                     $param  = 'id';
//                     if ($posisi == 'D') {
//                         $value_param  = $id;
//                         $val = $CI->Acc_model->GetData($tabel, $field, $param, $value_param);
//                         $nilaibayar = (!empty($val[0]->$field)) ? $val[0]->$field : 0;
//                         $det_Jurnaltes[]  = array(
//                             'nomor'         => '',
//                             'tanggal'       => $tgl_voucher,
//                             'tipe'          => 'JV',
//                             'no_perkiraan'  => $nokir,
//                             'keterangan'    => $Keterangan_INV,
//                             'no_reff'       => $no_request,
//                             'debet'         => $totalall2,
//                             'kredit'        => 0,
//                             'jenis_jurnal'  => 'intransit incustomer',
//                             'no_request'    => $no_request,
//                             'stspos'        => 1
//                         );
//                     } elseif ($posisi == 'K') {
//                         $det_Jurnaltes[]  = array(
//                             'nomor'         => '',
//                             'tanggal'       => $tgl_voucher,
//                             'tipe'          => 'JV',
//                             'no_perkiraan'  => $nokir,
//                             'keterangan'    => $Keterangan_INV,
//                             'no_reff'       => $no_request,
//                             'debet'         => 0,
//                             'kredit'        => $totalall2,
//                             'jenis_jurnal'  => 'intransit incustomer',
//                             'no_request'    => $no_request,
//                             'stspos'        => 1
//                         );
//                     }
//                 }
//             }
//             $CI->db->query("delete from jurnaltras WHERE jenis_jurnal='diterima customer' and no_reff ='$id'");
//             $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//             $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//             $Bln    = substr($tgl_voucher, 5, 2);
//             $Thn    = substr($tgl_voucher, 0, 4);
//             $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall2, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV . '-' . $id, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//             $CI->db->insert(DBACC . '.javh', $dataJVhead);
//             $datadetail = array();
//             foreach ($det_Jurnaltes as $vals) {
//                 $datadetail = array(
//                     'tipe'            => 'JV',
//                     'nomor'            => $Nomor_JV,
//                     'tanggal'        => $tgl_voucher,
//                     'no_perkiraan'    => $vals['no_perkiraan'],
//                     'keterangan'    => $vals['keterangan'],
//                     'no_reff'        => $vals['no_reff'],
//                     'debet'            => $vals['debet'],
//                     'kredit'        => $vals['kredit'],
//                 );
//                 $CI->db->insert(DBACC . '.jurnal', $datadetail);
//             }
//             $CI->db->query("UPDATE jurnal_product SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE id ='$id'");
//             unset($det_Jurnaltes);
//             unset($datadetail);
//         }
//     }
//     if ($ket == 'incoming stok') {
//         $kodejurnal = 'JV035';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING STOCK " . $id;
//         $datajurnal = $CI->db->query("select sum(total_nilai) as nilaibayar, tanggal, no_ipp from jurnal where kode_trans='" . $id . "' limit 1")->row();
//         $tgl_voucher = $datajurnal->tanggal;
//         $no_ipp = $datajurnal->no_ipp;
//         $no_request = $id;
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $masterjurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $det_Jurnaltes = [];
//         $unbill_coa = '';

//         // print_r($id);
//         // exit;

//         foreach ($masterjurnal as $record) {
//             $posisi = $record->posisi;
//             $nokir  = $record->no_perkiraan;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = $ket;
//             $totalall = $datajurnal->nilaibayar;
//             if ($posisi == 'D') {
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => $totalall,
//                     'kredit'        => 0,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalall,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             }
//         }
//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po in (select no_ipp from warehouse_adjustment where kode_trans='" . $id . "') limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-04';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $data_po->no_po,
//             'kredit'           => $datajurnal->nilaibayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//         unset($datahutang);
//     }
//     if ($ket == 'outgoing stok') {
//         $kodejurnal = 'JV039';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "OUTGOING STOCK " . $id;
//         $datajurnal = $CI->db->query("select sum(ROUND(total_nilai)) as nilaibayar, tanggal from jurnal where kode_trans='" . $id . "' limit 1")->row();
//         $tgl_voucher = $datajurnal->tanggal;
//         $no_request = $id;
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $sql = "SELECT * FROM warehouse_adjustment where kode_trans='" . $id . "'";
//         $wh = $CI->db->query($sql)->row();
//         $kode_gudang = $wh->id_gudang_ke;
//         $coa_deffered = '';
//         if ($kode_gudang == '17') {
//             $sql_deff = "select c.nm_customer, c.coa_deffered from so_number a 
//             left join table_sales_order b on a.id_bq=b.id_bq 
//             left join customer c on b.id_customer=c.id_customer
//             where a.so_number='" . $wh->no_so . "'";
//             $dt_coa = $CI->db->query($sql_deff)->row();
//             if (!empty($dt_coa)) {
//                 $coa_deffered = $dt_coa->coa_deffered;
//             }
//             if ($coa_deffered == "") {
//                 $sql_deff = "select coa_biaya from costcenter where id='" . $wh->id_gudang_ke . "'";
//                 $dt_coa = $CI->db->query($sql_deff)->row();
//                 $coa_deffered = $dt_coa->coa_biaya;
//             }
//         }
//         $masterjurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $det_Jurnaltes = [];
//         foreach ($masterjurnal as $record) {
//             $posisi = $record->posisi;
//             $nokir  = $record->no_perkiraan;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = $ket;
//             $totalall = $datajurnal->nilaibayar;
//             if ($posisi == 'D') {
//                 if ($kode_gudang == '17') {
//                     $val = $CI->db->query("select ROUND(a.total_nilai) total_nilai,a.id_material,a.nm_material, a.gudang_ke, '" . $coa_deffered . "' coa_biaya from jurnal a  where a.kode_trans='" . $id . "'")->result();
//                 } else {
//                     $val = $CI->db->query("select ROUND(a.total_nilai) total_nilai,a.id_material,a.nm_material, a.gudang_ke, b.category_awal, c.coa_biaya from jurnal a left join con_nonmat_new b on a.id_material=b.code_group left join con_nonmat_category_costcenter c on a.gudang_ke=c.costcenter and b.category_awal=c.category where a.kode_trans='" . $id . "'")->result();
//                 }
//                 foreach ($val as $rec) {
//                     $nilaibayar = $rec->total_nilai;
//                     $totalbayar = ($totalbayar + $nilaibayar);
//                     $dtcoa_biaya = $rec->coa_biaya;
//                     if ($dtcoa_biaya != "") {
//                     } else {
//                         $dtcoa_biaya = $nokir;
//                     }
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $dtcoa_biaya,
//                         'keterangan'    => $rec->nm_material . ' ' . $id,
//                         'no_reff'       => $id,
//                         'debet'         => $nilaibayar,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => $jenisjurnal,
//                         'no_request'    => $no_request,
//                         'stspos'          => 1
//                     );
//                 }
//             } elseif ($posisi == 'K') {
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalall,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             }
//         }
//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         unset($det_Jurnaltes);
//         unset($datadetail);
//     }
//     if ($ket == 'incoming department') {
//         $kodejurnal = 'JV036';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING DEPARTMENT " . $id;
//         $datajurnal    = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $unbill_coa = '';
//         $no_po = '';
//         foreach ($datajurnal as $record) {
//             $nokir1 = $record->no_perkiraan;
//             $tabel  = $record->menu;
//             $posisi = $record->posisi;
//             $field  = $record->field;
//             $nokir  = $record->no_perkiraan;
//             $kd_bayar = $id;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = 'incoming department';
//             if ($posisi == 'D') {
//                 $val = $CI->db->query("select a.no_ipp, a.tanggal, a.total_nilai,a.id_material,a.nm_material, c.coa from jurnal a left join rutin_non_planning_detail b on a.id_material=b.id left join rutin_non_planning_header c on b.no_pr=c.no_pr where a.kode_trans='" . $kd_bayar . "'")->result();
//                 foreach ($val as $rec) {
//                     $tgl_voucher = $rec->tanggal;
//                     $no_po = $rec->no_ipp;
//                     $nilaibayar = $rec->total_nilai;
//                     $totalbayar = ($totalbayar + $nilaibayar);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $rec->coa,
//                         'keterangan'    => $rec->nm_material . ' ' . $kd_bayar . ', ' . $no_po,
//                         'no_reff'       => $id,
//                         'debet'         => $nilaibayar,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => $jenisjurnal,
//                         'no_request'    => $id
//                     );
//                 }
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalbayar,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $id
//                 );
//             }
//         }

//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalbayar, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po='" . $no_po . "' limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-04';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $no_po,
//             'kredit'           => $totalbayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//         unset($datahutang);
//     }
//     if ($ket == 'incoming asset') {
//         $kodejurnal = 'JV038';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING ASSET " . $id;
//         $datajurnal    = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $unbill_coa = '';
//         $no_po = '';
//         foreach ($datajurnal as $record) {
//             $nokir1 = $record->no_perkiraan;
//             $tabel  = $record->menu;
//             $posisi = $record->posisi;
//             $field  = $record->field;
//             $nokir  = $record->no_perkiraan;
//             $kd_bayar = $id;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = 'incoming asset';
//             if ($posisi == 'D') {
//                 $val = $CI->db->query("select a.no_ipp, a.tanggal, a.total_nilai,a.id_material,a.nm_material, b.coa from jurnal a left join asset_planning b on a.id_material=b.code_plan where a.kode_trans='" . $kd_bayar . "'")->result();
//                 foreach ($val as $rec) {
//                     $tgl_voucher = $rec->tanggal;
//                     $no_po = $rec->no_ipp;
//                     $nilaibayar = $rec->total_nilai;
//                     $totalbayar = ($totalbayar + $nilaibayar);
//                     $det_Jurnaltes[]  = array(
//                         'nomor'         => '',
//                         'tanggal'       => $tgl_voucher,
//                         'tipe'          => 'JV',
//                         'no_perkiraan'  => $rec->coa,
//                         'keterangan'    => $rec->nm_material . ' ' . $kd_bayar . ', ' . $no_po,
//                         'no_reff'       => $id,
//                         'debet'         => $nilaibayar,
//                         'kredit'        => 0,
//                         'jenis_jurnal'  => $jenisjurnal,
//                         'no_request'    => $id
//                     );
//                 }
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalbayar,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $id
//                 );
//             }
//         }

//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalbayar, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po='" . $no_po . "' limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-05';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $no_po,
//             'kredit'           => $totalbayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//     }

//     if ($ket == 'incoming project') {
//         $kodejurnal = 'JV078';
//         $id = $ArrDetailProduct;
//         $Keterangan_INV = "INCOMING PROJECT " . $id;
//         $datajurnal = $CI->db->query("select sum(total_nilai) as nilaibayar, tanggal, no_ipp from jurnal where kode_trans='" . $id . "' limit 1")->row();
//         $tgl_voucher = $datajurnal->tanggal;
//         $no_ipp = $datajurnal->no_ipp;
//         $no_request = $id;
//         $nilaibayar    = 0;
//         $totalbayar    = 0;
//         $masterjurnal       = $CI->Acc_model->GetTemplateJurnal($kodejurnal);
//         $det_Jurnaltes = [];
//         $unbill_coa = '';



//         foreach ($masterjurnal as $record) {
//             $posisi = $record->posisi;
//             $nokir  = $record->no_perkiraan;
//             $param  = 'id';
//             $value_param  = $id;
//             $jenisjurnal = $ket;
//             $totalall = $datajurnal->nilaibayar;
//             if ($posisi == 'D') {
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => $totalall,
//                     'kredit'        => 0,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             } elseif ($posisi == 'K') {
//                 $unbill_coa = $nokir;
//                 $det_Jurnaltes[]  = array(
//                     'nomor'         => '',
//                     'tanggal'       => $tgl_voucher,
//                     'tipe'          => 'JV',
//                     'no_perkiraan'  => $nokir,
//                     'keterangan'    => $Keterangan_INV,
//                     'no_reff'       => $id,
//                     'debet'         => 0,
//                     'kredit'        => $totalall,
//                     'jenis_jurnal'  => $jenisjurnal,
//                     'no_request'    => $no_request,
//                     'stspos'          => 1
//                 );
//             }
//         }
//         $CI->db->query("UPDATE jurnal SET status_jurnal='1',approval_by='" . $UserName . "',approval_date='" . $DateTime . "' WHERE kode_trans ='$id' and category='" . $jenisjurnal . "'");
//         $CI->db->insert_batch('jurnaltras', $det_Jurnaltes);
//         $Nomor_JV = $CI->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_voucher);
//         $Bln    = substr($tgl_voucher, 5, 2);
//         $Thn    = substr($tgl_voucher, 0, 4);
//         $dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $tgl_voucher, 'jml' => $totalall, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => 'JV', 'keterangan' => $Keterangan_INV, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $id, 'tgl_jvkoreksi' => $tgl_voucher, 'ho_valid' => '');
//         $CI->db->insert(DBACC . '.javh', $dataJVhead);
//         $datadetail = array();
//         foreach ($det_Jurnaltes as $vals) {
//             $datadetail = array(
//                 'tipe'            => 'JV',
//                 'nomor'            => $Nomor_JV,
//                 'tanggal'        => $tgl_voucher,
//                 'no_perkiraan'    => $vals['no_perkiraan'],
//                 'keterangan'    => $vals['keterangan'],
//                 'no_reff'        => $vals['no_reff'],
//                 'debet'            => $vals['debet'],
//                 'kredit'        => $vals['kredit'],
//             );
//             $CI->db->insert(DBACC . '.jurnal', $datadetail);
//         }
//         $data_po = $CI->db->query("select * from tran_po_header where no_po in (select no_ipp from warehouse_adjustment where kode_trans='" . $id . "') limit 1")->row();
//         if ($data_po->mata_uang != 'IDR') $unbill_coa = '2101-01-04';
//         $datahutang = array(
//             'tipe'            => 'JV',
//             'nomor'            => $Nomor_JV,
//             'tanggal'        => $tgl_voucher,
//             'no_perkiraan'   => $unbill_coa,
//             'keterangan'     => $Keterangan_INV,
//             'no_reff'          => $data_po->no_po,
//             'kredit'           => $datajurnal->nilaibayar,
//             'debet'          => 0,
//             'id_supplier'    => $data_po->id_supplier,
//             'nama_supplier'  => $data_po->nm_supplier,
//             'no_request'     => $id,
//         );
//         $CI->db->insert('tr_kartu_hutang', $datahutang);
//         unset($det_Jurnaltes);
//         unset($datadetail);
//         unset($datahutang);
//     }


// }
