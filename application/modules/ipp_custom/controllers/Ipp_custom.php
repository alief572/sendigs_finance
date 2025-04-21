<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ipp_custom extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'IPP_Custom.View';
	protected $addPermission  	= 'IPP_Custom.Add';
	protected $managePermission = 'IPP_Custom.Manage';
	protected $deletePermission = 'IPP_Custom.Delete';

	public function __construct()
	{
		parent::__construct();

		// $this->load->library(array('Mpdf'));
		$this->load->model(array(
			'Ipp_custom/ipp_custom_model'
		));

		date_default_timezone_set('Asia/Bangkok');
	}

	//========================================================BOM

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		//   $this->template->page_icon('fa fa-users');

		history("View index ipp custom");

		$this->template->title('IPP Custom/Assembly');
		$this->template->render('index');
	}

	public function get_json_ipp()
	{
		$this->ipp_custom_model->get_json_ipp();
	}

	public function add()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			// print_r($data);
			// exit;
			$session 	= $this->session->userdata('app_session');
			$Ym			= date('y');
			$id    				= $data['id'];
			$no_ipp    			= $data['no_ipp'];
			$id_customer    	= $data['id_customer'];
			$project    		= $data['project'];
			$referensi    		= $data['referensi'];
			$id_top    			= $data['id_top'];
			$keterangan    		= $data['keterangan'];
			$delivery_type    	= $data['delivery_type'];
			$id_country    		= $data['id_country'];
			$delivery_category	= $data['delivery_category'];
			$area_destinasi    	= $data['area_destinasi'];
			$delivery_address   = $data['delivery_address'];
			$shipping_method    = $data['shipping_method'];
			$packing    		= $data['packing'];
			$guarantee    		= $data['guarantee'];
			$delivery_date    	= (!empty($data['delivery_date'])) ? date('Y-m-d', strtotime($data['delivery_date'])) : NULL;
			$instalasi_option   = $data['instalasi_option'];

			$created_by   = 'updated_by';
			$created_date = 'updated_date';
			$tanda        = 'Insert ';

			if (empty($id)) {
				//pengurutan kode
				$srcMtr			= "SELECT MAX(no_ipp) as maxP FROM custom_ipp WHERE no_ipp LIKE 'IPP_CA" . $Ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 8, 4);
				$urutan2++;
				$urut2			= sprintf('%04s', $urutan2);
				$no_ipp	      	= "IPP_CA" . $Ym . $urut2;

				$created_by   = 'created_by';
				$created_date = 'created_date';
				$tanda        = 'Update ';

				$rev = 0;
			} else {
				$header   	= $this->db->get_where('custom_ipp', array('id' => $id))->result();
				$rev		= $header[0]->rev + 1;
			}

			// echo $no_ipp; 
			// exit;

			$ArrHeader		= array(
				'no_ipp'			=> $no_ipp,
				'id_customer'		=> $id_customer,
				'project'			=> $project,
				'referensi'			=> $referensi,
				'id_top'			=> $id_top,
				'keterangan'		=> $keterangan,
				'delivery_type'		=> $delivery_type,
				'id_country'		=> $id_country,
				'delivery_category'	=> $delivery_category,
				'area_destinasi'	=> $area_destinasi,
				'delivery_address'	=> $delivery_address,
				'shipping_method'	=> $shipping_method,
				'packing'			=> $packing,
				'guarantee'			=> $guarantee,
				'delivery_date'		=> $delivery_date,
				'instalasi_option'	=> $instalasi_option,
				'rev'				=> $rev,
				$created_by	    	=> $session['id_user'],
				$created_date	  	=> date('Y-m-d H:i:s')
			);


			$ArrDetail	= array();
			$ArrDetailProduct	= array();
			$ArrDetailAcc	= array();
			$ArrDetailJadi	= array();
			$ArrDetailSheet	= array();
			$ArrDetailEnd	= array();
			if (!empty($data['Detail'])) {
				$nomor = 0;
				foreach ($data['Detail'] as $val => $valx) {
					$nomor++;
					$ArrDetail[$val]['no_ipp'] 			= $no_ipp;
					$ArrDetail[$val]['no_ipp_code'] 	= $no_ipp . '-' . $nomor;
					$ArrDetail[$val]['platform'] 		= (!empty($valx['platform'])) ? $valx['platform'] : 'N';
					$ArrDetail[$val]['cover_drainage'] 	= (!empty($valx['cover_drainage'])) ? $valx['cover_drainage'] : 'N';
					$ArrDetail[$val]['facade'] 			= (!empty($valx['facade'])) ? $valx['facade'] : 'N';
					$ArrDetail[$val]['ceilling'] 		= (!empty($valx['ceilling'])) ? $valx['ceilling'] : 'N';
					$ArrDetail[$val]['partition'] 		= (!empty($valx['partition'])) ? $valx['partition'] : 'N';
					$ArrDetail[$val]['fence'] 			= (!empty($valx['fence'])) ? $valx['fence'] : 'N';
					$ArrDetail[$val]['app_others'] 		= $valx['app_others'];


					$ArrDetail[$val]['color_dark_green'] 	= (!empty($valx['color_dark_green'])) ? $valx['color_dark_green'] : 'N';
					$ArrDetail[$val]['color_dark_grey'] 	= (!empty($valx['color_dark_grey'])) ? $valx['color_dark_grey'] : 'N';
					$ArrDetail[$val]['color_light_grey'] 	= (!empty($valx['color_light_grey'])) ? $valx['color_light_grey'] : 'N';
					$ArrDetail[$val]['color_yellow'] 		= (!empty($valx['color_yellow'])) ? $valx['color_yellow'] : 'N';
					$ArrDetail[$val]['color'] 				= $valx['color'];

					$ArrDetail[$val]['food_grade'] 			= (!empty($valx['food_grade'])) ? $valx['food_grade'] : 'N';
					$ArrDetail[$val]['uv'] 					= (!empty($valx['uv'])) ? $valx['uv'] : 'N';
					$ArrDetail[$val]['fire_reterdant'] 		= (!empty($valx['fire_reterdant'])) ? $valx['fire_reterdant'] : 'N';
					$ArrDetail[$val]['industrial_type'] 	= (!empty($valx['industrial_type'])) ? $valx['industrial_type'] : 'N';
					$ArrDetail[$val]['commercial_type'] 	= (!empty($valx['commercial_type'])) ? $valx['commercial_type'] : 'N';
					$ArrDetail[$val]['superior_type'] 		= (!empty($valx['superior_type'])) ? $valx['superior_type'] : 'N';

					$ArrDetail[$val]['standard_astm'] 		= (!empty($valx['standard_astm'])) ? $valx['standard_astm'] : 'N';
					$ArrDetail[$val]['standard_bs'] 		= (!empty($valx['standard_bs'])) ? $valx['standard_bs'] : 'N';
					$ArrDetail[$val]['standard_dnv'] 		= (!empty($valx['standard_dnv'])) ? $valx['standard_dnv'] : 'N';
					$ArrDetail[$val]['file_pendukung_1'] 	= $valx['file_pendukung_1'];
					$ArrDetail[$val]['file_pendukung_2'] 	= $valx['file_pendukung_2'];

					$ArrDetail[$val]['surface_concave'] 		= (!empty($valx['surface_concave'])) ? $valx['surface_concave'] : 'N';
					$ArrDetail[$val]['surface_flat'] 			= (!empty($valx['surface_flat'])) ? $valx['surface_flat'] : 'N';
					$ArrDetail[$val]['surface_chequered_plate'] = (!empty($valx['surface_chequered_plate'])) ? $valx['surface_chequered_plate'] : 'N';
					$ArrDetail[$val]['surface_anti_skid'] 		= (!empty($valx['surface_anti_skid'])) ? $valx['surface_anti_skid'] : 'N';
					// $ArrDetail[$val]['surface_custom'] 			= $valx['surface_custom'];

					$ArrDetail[$val]['mesh_open'] 				= (!empty($valx['mesh_open'])) ? $valx['mesh_open'] : 'N';
					$ArrDetail[$val]['mesh_closed'] 			= (!empty($valx['mesh_closed'])) ? $valx['mesh_closed'] : 'N';

					$ArrDetail[$val]['type_product'] 	= $valx['type_product'];
					$ArrDetail[$val]['product_name'] 	= $valx['product_name'];
					$ArrDetail[$val]['accessories'] 	= $valx['accessories'];
					$ArrDetail[$val]['ket'] 	= $valx['ket'];

					if (!empty($_FILES['photo_' . $val]["tmp_name"])) {
						$target_dir     = "assets/files/";
						$target_dir_u   = get_root3() . "/assets/files/";
						$name_file      = 'ipp-' . $val . "-" . $no_ipp . '-' . $nomor . '-' . date('Ymdhis');
						$target_file    = $target_dir . basename($_FILES['photo_' . $val]["name"]);
						$name_file_ori  = basename($_FILES['photo_' . $val]["name"]);
						$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
						$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;

						// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){

						$terupload = move_uploaded_file($_FILES['photo_' . $val]["tmp_name"], $nama_upload);
						$link_url    	= $target_dir . $name_file . "." . $imageFileType;

						$ArrDetail[$val]['file_dokumen'] 		= $link_url;
						// }
					}

					if (!empty($valx['ukuran_jadi'])) {
						foreach ($valx['ukuran_jadi'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailJadi[$UNIQ]['category'] = 'ukuran jadi';
							$ArrDetailJadi[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailJadi[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailJadi[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailJadi[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailJadi[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}
				}
			}

			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('custom_ipp', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('custom_ipp', $ArrHeader);
			}

			$this->db->where('no_ipp', $no_ipp);
			$this->db->delete('custom_ipp_detail');

			$this->db->where('no_ipp', $no_ipp);
			$this->db->delete('custom_ipp_detail_lainnya');

			if (!empty($ArrDetail)) {
				$this->db->insert_batch('custom_ipp_detail', $ArrDetail);
			}

			if (!empty($ArrDetailJadi)) {
				$this->db->insert_batch('custom_ipp_detail_lainnya', $ArrDetailJadi);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=> 'Save gagal disimpan ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Save berhasil disimpan. Thanks ...',
					'status'	=> 1
				);
				history($tanda . " supplier " . $no_ipp);
			}

			echo json_encode($Arr_Data);
		} else {
			$id 			= $this->uri->segment(3);
			$tanda 			= $this->uri->segment(4);
			$header   		= $this->db->get_where('custom_ipp', array('id' => $id))->result();
			$detail = [];
			if (!empty($header)) {
				$no_ipp 		= (!empty($header[0]->no_ipp)) ? $header[0]->no_ipp : 0;
				$detail   		= $this->db->get_where('custom_ipp_detail', array('no_ipp' => $no_ipp))->result_array();
			}
			$customer   	= $this->db->order_by('nm_customer', 'asc')->get_where('customer', array('deleted_date' => NULL))->result_array();
			$deliv_category = $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'category'))->result_array();
			$top			= $this->db->order_by('id', 'asc')->get_where('list_help', array('group_by' => 'top invoice'))->result_array();
			$shipping		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'method'))->result_array();
			$packing		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'ipp', 'category' => 'packing type'))->result_array();
			$country 		= $this->db->order_by('a.name', 'asc')->get('country_all a')->result_array();

			$list_bom_topping = $this->db
				->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
				->order_by('a.id_product', 'asc')
				->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
				->join('new_inventory_3 c', 'a.id_product=c.code_lv3', 'left')
				->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result_array();
			// print_r($detail);
			// exit;
			$data = [
				'header' => $header,
				'detail' => $detail,
				'customer' => $customer,
				'top' => $top,
				'country' => $country,
				'deliv_category' => $deliv_category,
				'shipping' => $shipping,
				'packing_list' => $packing,
				'list_bom_topping' => $list_bom_topping,
				'tanda' => $tanda,
				'product_lv1' => get_list_inventory_lv1('product'),
			];

			$explodeURL = explode('/', base_url());

			$this->template->title('Add IPP Custom/Assembly');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add', $data);
		}
	}

	public function detail()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->input->post('no_bom');
		$header = $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail = $this->db->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();
		$product    = $this->ipp_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));
		// print_r($header);
		$data = [
			'header' => $header,
			'detail' => $detail,
			'product' => $product,
			'GET_LEVEL4' => get_inventory_lv4(),
		];
		$this->template->set('results', $data);
		$this->template->render('detail', $data);
	}

	public function hapus()
	{
		$data = $this->input->post();
		$session 		= $this->session->userdata('app_session');
		$id  = $data['id'];

		$ArrHeader		= array(
			'deleted_by'	  => $session['id_user'],
			'deleted_date'	=> date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('custom_ipp', $ArrHeader);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
			history("Delete data ipp: " . $id);
		}

		echo json_encode($Arr_Data);
	}

	public function excel_report_all()
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		$objPHPExcel	= new PHPExcel();

		$tableHeader 	= tableHeader();
		$mainTitle 		= mainTitle();
		$tableBodyCenter = tableBodyCenter();
		$tableBodyLeft 	= tableBodyLeft();
		$tableBodyRight = tableBodyRight();

		$sheet 		= $objPHPExcel->getActiveSheet();

		$product    = $this->db
			->select('a.*, b.name AS nm_country')
			->join('country_all b', 'a.id_country=b.iso3', 'left')
			->get_where('new_supplier a', array('a.deleted_date' => NULL))
			->result_array();

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(6);
		$sheet->setCellValue('A' . $Row, 'SUPPLIER');
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow + 1;

		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->setCellValue('B' . $NewRow, 'Supplier Name');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->setCellValue('C' . $NewRow, 'Country Name');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D' . $NewRow, 'Telephone');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'Fax');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setAutoSize(true);

		$sheet->setCellValue('F' . $NewRow, 'Email');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setAutoSize(true);

		if ($product) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($product as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$nomor	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nomor);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nama	= $row_Cek['nama'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nama);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nm_country	= $row_Cek['nm_country'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_country);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$telp	= $row_Cek['telp'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $telp);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$fax	= $row_Cek['fax'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $fax);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$email	= $row_Cek['email'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $email);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		$sheet->setTitle('Supplier');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="master-supplier.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function get_add()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$product_lv1 = get_list_inventory_lv1('product');
		$list_bom_topping = $this->db
			->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
			->order_by('a.id_product', 'asc')
			->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
			->join('new_inventory_3 c', 'a.id_product=c.code_lv3', 'left')
			->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result_array();

		$d_Header = "";
		$d_Header .= "<div id='header_" . $id . "'>";
		$d_Header .= "<h4 class='text-bold text-primary'>Permintaan " . $id . "&nbsp;&nbsp;<span class='text-red text-bold delPart' data-id='" . $id . "' style='cursor:pointer;' title='Delete Part'>Delete</span></h4>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<label>Aplikasi Kebutuhan</label>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][platform]' value='Y'>Platform</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][cover_drainage]' value='Y'>Cover Drainage</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][facade]' value='Y'>Facade</label></div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][ceilling]' value='Y'>Ceilling</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][partition]' value='Y'>Partition</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][fence]' value='Y'>Fence</label></div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'><label>Other</label>";
		$d_Header .= "<input type='text' name='Detail[" . $id . "][app_others]' class='form-control input-md' placeholder='Other' value=''>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";

		$d_Header .= "<hr>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Type Product</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-3'>";
		$d_Header .= "		<select name='Detail[" . $id . "][type_product]' class='form-control'>";
		foreach ($product_lv1 as $key => $value) {
			$d_Header .= "<option value='" . $value['code_lv1'] . "'>" . $value['nama'] . "</option>";
		}
		$d_Header .= "		</select>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Product Name</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-6'>";
		$d_Header .= "		<input type='text' name='Detail[" . $id . "][product_name]' class='form-control input-md' placeholder='Product Name' value=''>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		$d_Header .= "<hr>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Additional Spesification</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Additional</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][food_grade]' value='Y'>Food Grade</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][uv]' value='Y'>UV</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][fire_reterdant]' value='Y'>Fire Reterdant</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label></label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][industrial_type]' value='Y'>Industrial Type</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][commercial_type]' value='Y'>Commercial Type</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][superior_type]' value='Y'>Superior Type</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Standard Spec</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][standard_astm]' value='Y'>ASTM</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][standard_bs]' value='Y'>BS</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][standard_dnv]' value='Y'>GNV-GL</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "		<div class='form-group'><label>Dokumen Pendukung</label>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[" . $id . "][file_pendukung_1]' placeholder='Dokumen Pendukung 1' style='margin-bottom:5px;'>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[" . $id . "][file_pendukung_2]' placeholder='Dokumen Pendukung 2' style='margin-bottom:5px;'>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label></label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Color</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][color_dark_green]' value='Y'>Dark Green</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][color_dark_grey]' value='Y'>Dark Grey</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label></label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][color_light_grey]' value='Y'>Light Grey</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][color_yellow]' value='Y'>Yellow</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Color Other</label>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[" . $id . "][color]' placeholder='Color Other'>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		$d_Header .= "<div class='form-group row' hidden>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label></label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Surface</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_concave]' value='Y'>Concave</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_flat]' value='Y'>Flat</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label></label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_anti_skid]' value='Y'>Anti Skid</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_chequered_plate]' value='Y'>Chequered Plate</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Drawing Customer</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-5'><input type='file' name='photo_" . $id . "' id='photo_" . $id . "' ></div>";
		$d_Header .= "</div>";

		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Accessories</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-6'>";
		$d_Header .= "		<input type='text' name='Detail[" . $id . "][accessories]' class='form-control input-md' placeholder='Accessories' value=''>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		$d_Header .= "<div class='form-group row' hidden>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Mesh</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][mesh_open]' value='Y'>Open Mesh</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][mesh_closed]' value='Y'>Closed Mesh</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		//ukuran jadi
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Ukuran Jadi</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-6'>";
		$d_Header .= "	<table class='table table-striped table-bordered table-hover table-condensed'>";
		$d_Header .= "		<tr class='bg-blue'>";
		$d_Header .= "			<th class='text-center' width='30%'>Length</th>";
		$d_Header .= "			<th class='text-center' width='30%'>Width</th>";
		$d_Header .= "			<th class='text-center' width='30%'>Qty</th>";
		$d_Header .= "			<th class='text-center' width='10%'>#</th>";
		$d_Header .= "		</tr>";
		$new_number = 0;
		$d_Header .= "		<tr id='addjadi_" . $id . "_" . $new_number . "'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPartUkj' title='Add Ukuran Jadi'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";
		$d_Header .= "	</table>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "		<div class='form-group'><label>Keterangan</label>";
		$d_Header .= "		<textarea class='form-control' name='Detail[" . $id . "][ket]' placeholder='Keterangan' rows='2'></textarea>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		//penutup div delete
		$d_Header .= "<hr>";
		$d_Header .= "</div>";
		//add part
		$d_Header .= "<div id='add_" . $id . "'><button type='button' class='btn btn-sm btn-primary addPart' title='Add Permintaan'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Permintaan</button></td></div>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_product_lv4()
	{
		$post 			= $this->input->post();

		$id_head 		= $this->uri->segment(3);
		$id 			= $this->uri->segment(4);
		$type_product 	= $post['type_product'];

		$where = array('deleted_date' => NULL, 'category' => 'product');
		if ($type_product != '0') {
			$where = array('deleted_date' => NULL, 'category' => 'product', 'code_lv1' => $type_product);
		}

		$material    = $this->ipp_custom_model->get_data_where_array('new_inventory_4', $where);
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr id='header_" . $id_head . "_" . $id . "'>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='Detail[" . $id_head . "][product_master][" . $id . "][code_lv4]' class='chosen-select form-control input-sm '>";
		$d_Header .= "<option value='0'>Select Material Name</option>";
		foreach ($material as $valx) {
			$d_Header .= "<option value='" . $valx->code_lv4 . "'>" . strtoupper($valx->nama) . "</option>";
		}
		$d_Header .= 		"</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][product_master][" . $id . "][order]' class='form-control input-md text-center autoNumeric0 qty' placeholder='Order'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='center'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPartProduct4' title='Delete'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "		<tr id='addproduct4_" . $id_head . "_" . $id . "'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPartProduct4' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_accessories()
	{
		$post 			= $this->input->post();

		$id_head 		= $this->uri->segment(3);
		$id 			= $this->uri->segment(4);
		$type_product 	= $post['type_product'];

		$where = array('deleted_date' => NULL, 'id_category' => $type_product);
		$material    = $this->ipp_custom_model->get_data_where_array('accessories', $where);
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr id='headeracc_" . $id_head . "_" . $id . "'>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='Detail[" . $id_head . "][accessories][" . $id . "][code_lv4]' class='chosen-select form-control input-sm '>";
		$d_Header .= "<option value='0'>Select Accessories</option>";
		foreach ($material as $valx) {
			$d_Header .= "<option value='" . $valx->id . "'>" . strtoupper($valx->stock_name) . "</option>";
		}
		$d_Header .= 		"</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][accessories][" . $id . "][order]' class='form-control input-md text-center autoNumeric0 qty' placeholder='Order'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='center'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPartAcc' title='Delete'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "		<tr id='addacc_" . $id_head . "_" . $id . "'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPartAcc' title='Add Accessories'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Accessories</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_ukuran()
	{
		$post 			= $this->input->post();

		$id_head 		= $this->uri->segment(3);
		$id 			= $this->uri->segment(4);
		$NameSave 		= $post['NameSave'];
		$LabelAdd 		= $post['LabelAdd'];
		$LabelClass 	= $post['LabelClass'];
		$idClass 		= $post['idClass'];

		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr id='header" . $idClass . "_" . $id_head . "_" . $id . "'>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][" . $NameSave . "][" . $id . "][length]' class='form-control input-md text-center autoNumeric4'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][" . $NameSave . "][" . $id . "][width]' class='form-control input-md text-center autoNumeric4'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][" . $NameSave . "][" . $id . "][order]' class='form-control input-md text-center autoNumeric0'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='center'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart" . $LabelClass . "' title='Delete'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "		<tr id='add" . $idClass . "_" . $id_head . "_" . $id . "'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPart" . $LabelClass . "' title='Add " . $LabelAdd . "'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add " . $LabelAdd . "</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function detail_costing($no_ipp = null)
	{

		$costing_rate 	= $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

		$ipp_product   	= $this->db->get_where('ipp_detail_lainnya', array('no_ipp' => $no_ipp, 'category' => 'product'))->result_array();

		$ArrCosting[0]['rate_cycletime'] = 0;
		$ArrCosting[0]['rate_man_power_usd'] = 0;
		$ArrCosting[0]['rate_depresiasi'] = 0;
		$ArrCosting[0]['rate_mould'] = 0;

		$ArrCosting[0]['cost_persen_indirect'] = 0;
		$ArrCosting[0]['cost_persen_consumable'] = 0;
		$ArrCosting[0]['cost_persen_packing'] = 0;
		$ArrCosting[0]['cost_persen_enginnering'] = 0;
		$ArrCosting[0]['cost_persen_foh'] = 0;
		$ArrCosting[0]['cost_persen_fin_adm'] = 0;
		$ArrCosting[0]['cost_persen_mkt_sales'] = 0;
		$ArrCosting[0]['cost_persen_interest'] = 0;
		$ArrCosting[0]['cost_persen_profit'] = 0;
		$ArrCosting[0]['cost_factor_kompetitif'] = 0;
		$ArrCosting[0]['cost_nego_allowance'] = 0;

		$ArrCosting[0]['price_material'] = 0;
		$ArrCosting[0]['cost_direct_labout'] = 0;
		$ArrCosting[0]['cost_indirect'] = 0;
		$ArrCosting[0]['cost_machine'] = 0;
		$ArrCosting[0]['cost_mould'] = 0;
		$ArrCosting[0]['cost_consumable'] = 0;
		$ArrCosting[0]['cost_packing'] = 0;
		$ArrCosting[0]['cost_transport'] = 0;
		$ArrCosting[0]['cost_enginnering'] = 0;
		$ArrCosting[0]['cost_foh'] = 0;
		$ArrCosting[0]['cost_fin_adm'] = 0;
		$ArrCosting[0]['cost_mkt_sales'] = 0;
		$ArrCosting[0]['cost_interest'] = 0;
		$ArrCosting[0]['cost_profit'] = 0;
		$ArrCosting[0]['cost_bottom_price'] = 0;
		$ArrCosting[0]['cost_bottom_selling'] = 0;
		$ArrCosting[0]['cost_allowance'] = 0;
		$ArrCosting[0]['cost_price_final'] = 0;

		foreach ($ipp_product as $key => $value) {
			$get_bom_header = $this->db->get_where('product_price', array('code_lv4' => $value['code_lv4'], 'deleted_date' => NULL))->result_array();
			foreach ($get_bom_header as $key2 => $value2) {
				$product_price 		= $this->db->get_where('product_price', array('no_bom' => $value2['no_bom'], 'deleted_date' => NULL))->result_array();
				foreach ($product_price as $key3 => $value3) {
					$ArrCosting[0]['rate_cycletime'] = $value3['rate_cycletime'];
					$ArrCosting[0]['rate_man_power_usd'] = $value3['rate_man_power_usd'];
					$ArrCosting[0]['rate_depresiasi'] = $value3['rate_depresiasi'];
					$ArrCosting[0]['rate_mould'] = $value3['rate_mould'];

					$ArrCosting[0]['cost_persen_indirect'] = $value3['cost_persen_indirect'];
					$ArrCosting[0]['cost_persen_consumable'] = $value3['cost_persen_consumable'];
					$ArrCosting[0]['cost_persen_packing'] = $value3['cost_persen_packing'];
					$ArrCosting[0]['cost_persen_enginnering'] = $value3['cost_persen_enginnering'];
					$ArrCosting[0]['cost_persen_foh'] = $value3['cost_persen_foh'];
					$ArrCosting[0]['cost_persen_fin_adm'] = $value3['cost_persen_fin_adm'];
					$ArrCosting[0]['cost_persen_mkt_sales'] = $value3['cost_persen_mkt_sales'];
					$ArrCosting[0]['cost_persen_interest'] = $value3['cost_persen_interest'];
					$ArrCosting[0]['cost_persen_profit'] = $value3['cost_persen_profit'];
					$ArrCosting[0]['cost_factor_kompetitif'] = $value3['cost_factor_kompetitif'];
					$ArrCosting[0]['cost_nego_allowance'] = $value3['cost_nego_allowance'];

					$ArrCosting[0]['price_material'] += $value3['price_material'] * $value['order'];
					$ArrCosting[0]['cost_direct_labout'] += $value3['cost_direct_labout'] * $value['order'];
					$ArrCosting[0]['cost_indirect'] += $value3['cost_indirect'] * $value['order'];
					$ArrCosting[0]['cost_machine'] += $value3['cost_machine'] * $value['order'];
					$ArrCosting[0]['cost_mould'] += $value3['cost_mould'] * $value['order'];
					$ArrCosting[0]['cost_consumable'] += $value3['cost_consumable'] * $value['order'];
					$ArrCosting[0]['cost_packing'] += $value3['cost_packing'] * $value['order'];
					$ArrCosting[0]['cost_transport'] += $value3['cost_transport'] * $value['order'];
					$ArrCosting[0]['cost_enginnering'] += $value3['cost_enginnering'] * $value['order'];
					$ArrCosting[0]['cost_foh'] += $value3['cost_foh'] * $value['order'];
					$ArrCosting[0]['cost_fin_adm'] += $value3['cost_fin_adm'] * $value['order'];
					$ArrCosting[0]['cost_mkt_sales'] += $value3['cost_mkt_sales'] * $value['order'];
					$ArrCosting[0]['cost_interest'] += $value3['cost_interest'] * $value['order'];
					$ArrCosting[0]['cost_profit'] += $value3['cost_profit'] * $value['order'];
					$ArrCosting[0]['cost_bottom_price'] += $value3['cost_bottom_price'] * $value['order'];
					$ArrCosting[0]['cost_bottom_selling'] += $value3['cost_bottom_selling'] * $value['order'];
					$ArrCosting[0]['cost_allowance'] += $value3['cost_allowance'] * $value['order'];
					$ArrCosting[0]['cost_price_final'] += $value3['cost_price_final'] * $value['order'];
				}
			}
		}

		$data = [
			'no_bom' => 0,
			'dataList' => $costing_rate,
			'product_price' => $ArrCosting
		];
		$this->template->title('Costing Rate');
		$this->template->render('detail_costing', $data);
	}

	public function ajukan()
	{
		$data = $this->input->post();
		$session 		= $this->session->userdata('app_session');
		$id  = $data['id'];

		$ArrHeader		= array(
			'ajukan_sts'	  	=> 'Y',
			'ajukan_sts_by'	=> $session['id_user'],
			'ajukan_sts_date'	=> date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('custom_ipp', $ArrHeader);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save gagal diproses !',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save berhasil diproses !',
				'status'	=> 1
			);
			history("Delete data custom ipp: " . $id);
		}

		echo json_encode($Arr_Data);
	}
}
