<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Supplier extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_Supplier.View';
	protected $addPermission  	= 'Master_Supplier.Add';
	protected $managePermission = 'Master_Supplier.Manage';
	protected $deletePermission = 'Master_Supplier.Delete';

	public function __construct()
	{
		parent::__construct();

		// $this->load->library(array('Mpdf'));
		$this->load->model(array(
			'Supplier/supplier_model'
		));

		date_default_timezone_set('Asia/Bangkok');
	}

	//========================================================BOM

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');

		history("View index master supplier");

		$this->template->title('Supplier');
		$this->template->render('index');
	}

	public function get_json_supplier()
	{
		$this->supplier_model->get_json_supplier();
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
			$id     = $data['id'];
			$kode_supplier    = $data['kode_supplier'];
			$nama    = $data['nama'];
			$id_country    = $data['id_country'];
			$id_provinsi    = $data['id_provinsi'];
			$id_currency    = $data['id_currency'];
			$telp    = $data['telp'];
			$telp2    = $data['telp2'];
			$fax    = $data['fax'];
			$email    = $data['email'];
			$email2    = $data['email2'];
			$email3    = $data['email3'];
			$contact    = $data['contact'];
			$contact_person    = $data['contact_person'];
			$tax_number    = $data['tax_number'];
			$address    = $data['address'];
			$tax_address    = $data['tax_address'];
			$note    = $data['note'];
			$bank_account    = $data['bank_account'];

			$created_by   = 'updated_by';
			$created_date = 'updated_date';
			$tanda        = 'Insert ';

			if (empty($id)) {
				//pengurutan kode
				$srcMtr			= "SELECT MAX(kode_supplier) as maxP FROM new_supplier WHERE kode_supplier LIKE 'SUP" . $Ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 5, 5);
				$urutan2++;
				$urut2			= sprintf('%05s', $urutan2);
				$kode_supplier	      = "SUP" . $Ym . $urut2;

				$created_by   = 'created_by';
				$created_date = 'created_date';
				$tanda        = 'Update ';
			}

			$ArrHeader		= array(
				'id'			    => $id,
				'kode_supplier'		=> $kode_supplier,
				'nama'			    => $nama,
				'id_country'		=> $id_country,
				'id_provinsi'		=> $id_provinsi,
				'id_currency'		=> $id_currency,
				'telp'			    => $telp,
				'telp2'			    => $telp2,
				'fax'			    => $fax,
				'email'			    => $email,
				'email2'			=> $email2,
				'email3'			=> $email3,
				'contact'			=> $contact,
				'contact_person'	=> $contact_person,
				'tax_number'		=> $tax_number,
				'address'			=> $address,
				'tax_address'		=> $tax_address,
				'note'			    => $note,
				'bank_account'		=> $bank_account,
				$created_by	    	=> $session['id_user'],
				$created_date	  	=> date('Y-m-d H:i:s')
			);

			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('new_supplier', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('new_supplier', $ArrHeader);
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
				history($tanda . " supplier " . $kode_supplier);
			}

			echo json_encode($Arr_Data);
		} else {
			$id 		= $this->uri->segment(3);
			$header   	= $this->db->get_where('new_supplier', array('id' => $id))->result();
			$country    = $this->db->order_by('name', 'asc')->get_where('country_all', array('iso3 !=' => NULL))->result_array();
			$provinsi   = $this->db->order_by('urut', 'asc')->get('provinsi')->result_array();
			$currency   = $this->db->order_by('negara', 'asc')->get('mata_uang')->result_array();

			// print_r($header);
			// exit;
			$data = [
				'header' => $header,
				'country' => $country,
				'provinsi' => $provinsi,
				'currency' => $currency,
			];
			$this->template->title('Add Supplier');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add', $data);
		}
	}

	public function edit()
	{
		$id = $this->input->post('id');

		if (!$id) {
			show_error("ID tidak ditemukan", 400);
		}

		$header   	= $this->db->get_where('new_supplier', array('id' => $id))->result();
		$country    = $this->db->order_by('name', 'asc')->get_where('country_all', array('iso3 !=' => NULL))->result_array();
		$provinsi   = $this->db->order_by('urut', 'asc')->get('provinsi')->result_array();
		$currency   = $this->db->order_by('negara', 'asc')->get('mata_uang')->result_array();

		$data = [
			'header' => $header,
			'country' => $country,
			'provinsi' => $provinsi,
			'currency' => $currency,
		];

		$this->template->title('Edit Supplier');
		$this->template->page_icon('fa fa-edit');
		$this->template->render('add', $data);
	}

	public function detail()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->input->post('no_bom');
		$header = $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail = $this->db->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();
		$product    = $this->supplier_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));
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
		$this->db->update('new_supplier', $ArrHeader);
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
			history("Delete data supplier " . $id);
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
		$Col_Akhir	= $Cols	= getColsChar(12);
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

		$sheet->setCellValue('D' . $NewRow, 'Address');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'Telephone');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setAutoSize(true);

		$sheet->setCellValue('F' . $NewRow, 'Fax');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setAutoSize(true);

		$sheet->setCellValue('G' . $NewRow, 'Email');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
		$sheet->getColumnDimension('G')->setAutoSize(true);

		$sheet->setCellValue('H' . $NewRow, 'Tax Number');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
		$sheet->getColumnDimension('H')->setAutoSize(true);

		$sheet->setCellValue('I' . $NewRow, 'Tax Address');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
		$sheet->getColumnDimension('I')->setAutoSize(true);

		$sheet->setCellValue('J' . $NewRow, 'Bank Account');
		$sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);
		$sheet->getColumnDimension('J')->setAutoSize(true);

		$sheet->setCellValue('K' . $NewRow, 'Contact Person');
		$sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);
		$sheet->getColumnDimension('K')->setAutoSize(true);

		$sheet->setCellValue('L' . $NewRow, 'Contact');
		$sheet->getStyle('L' . $NewRow . ':L' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('L' . $NewRow . ':L' . $NextRow);
		$sheet->getColumnDimension('L')->setAutoSize(true);

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
				$telp	= $row_Cek['address'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $telp);
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

				$awal_col++;
				$email	= $row_Cek['tax_number'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $email);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$email	= $row_Cek['tax_address'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $email);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$email	= $row_Cek['bank_account'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $email);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$email	= $row_Cek['contact_person'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $email);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$email	= $row_Cek['contact'];
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
}
