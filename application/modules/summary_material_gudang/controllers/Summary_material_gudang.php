<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Summary_material_gudang extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'SummaryMaterialGudang.View';
    protected $addPermission  	= 'SummaryMaterialGudang.Add';
    protected $managePermission = 'SummaryMaterialGudang.Manage';
    protected $deletePermission = 'SummaryMaterialGudang.Delete';

	public function __construct()
    {
        parent::__construct();
        // $this->load->model(
		// 	array('Material/material_model')
		// );
        $this->template->title('Summary Material Gudang');

        date_default_timezone_set('Asia/Bangkok');
    }

	public function index(){
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		
		$warehouse		= $this->db->order_by('id','asc')->get_where('warehouse',array('sts_1'=>'Y'))->result_array();

        $data = array(
			'warehouse'		=> $warehouse
		);

		history("View data summary material gudang");
		$this->template->title('Summary Material Gudang');
		$this->template->render('index', $data);
	}

    public function show_history_summary_gudang(){
		$data       = $this->input->post();
		$warehouse  = $data['warehouse'];
		$tgl_awal   = date('Y-m-d',strtotime($data['tgl_awal']));
		$tgl_akhir  = date('Y-m-d',strtotime($data['tgl_akhir']));

		$result		= $this->db->group_by('a.id_material')->order_by('a.nm_material')->select('a.*')->get_where('warehouse_history a', array('a.id_gudang'=>$warehouse, 'DATE(a.update_date) >='=>$tgl_awal, 'DATE(a.update_date) <='=>$tgl_akhir))->result_array();
		// TOTAL TRANSAKSI
		$result_in	= $this->db
							->select('SUM(a.jumlah_mat) AS jumlah_material, id_material')
							->from('warehouse_history a')
							->where('a.id_gudang',$warehouse)
							->where('DATE(a.update_date) >=',$tgl_awal)
							->where('DATE(a.update_date) <=',$tgl_akhir)
							->where('a.kd_gudang_dari <>','BOOKING')
							->group_start()
							->like('a.ket', 'penambahan')
							->or_like('a.ket', 'incoming')
							->group_end()
							->group_by('a.id_material')
							->get()
							->result_array();
		$ArrSumMaterial_IN = [];
		foreach ($result_in as $key => $value) {
			$ArrSumMaterial_IN[$value['id_material']] = $value['jumlah_material'];
		}
		$result_out	= $this->db
							->select('SUM(a.jumlah_mat) AS jumlah_material, id_material')
							->from('warehouse_history a')
							->where('a.id_gudang',$warehouse)
							->where('DATE(a.update_date) >=',$tgl_awal)
							->where('DATE(a.update_date) <=',$tgl_akhir)
							->where('a.kd_gudang_dari <>','BOOKING')
							->like('a.ket', 'pengurangan')
							->group_by('a.id_material')
							->get()
							->result_array();
		$ArrSumMaterial_OUT = [];
		foreach ($result_out as $key => $value) {
			$ArrSumMaterial_OUT[$value['id_material']] = $value['jumlah_material'];
		}

		$dataArr = [
			'result' 			=> $result,
			'get_in_material' 	=> $ArrSumMaterial_IN,
			'get_out_material' 	=> $ArrSumMaterial_OUT
		];

		$data_html = $this->load->view('show_history_summary_gudang', $dataArr, TRUE);

		$Arr_Kembali	= array(
			'status'	=> 1,
			'data_html'	=> $data_html
		);
		echo json_encode($Arr_Kembali);
	}

    public function show_history_summary_gudang_detail(){
		$data       = $this->input->post();

		$tanda  	= $data['tanda'];
		$warehouse  = $data['warehouse'];
		$material  	= $data['material'];
		$tgl_awal   = date('Y-m-d',strtotime($data['tgl_awal']));
		$tgl_akhir  = date('Y-m-d',strtotime($data['tgl_akhir']));

		$like1 = 'penambahan';
		$like2 = 'incoming';
		if($tanda == 'out'){
			$like1 = 'pengurangan';
			$like2 = 'pengurangan';
			$transaksi	= $this->db
							->select('SUM(a.jumlah_mat) AS jumlah_material, no_ipp AS kode_trans, id_material, nm_material, update_by, update_date, ket')
							->from('warehouse_history a')
							->where('a.id_gudang',$warehouse)
							->where('a.id_material',$material)
							->where('DATE(a.update_date) >=',$tgl_awal)
							->where('DATE(a.update_date) <=',$tgl_akhir)
							->where('a.kd_gudang_dari <>','BOOKING')
							->like('a.ket', $like1)
							->group_by('a.id_material')
							->group_by('a.no_ipp')
							->get()
							->result_array();
		}
		else{
			$transaksi	= $this->db
							->select('SUM(a.jumlah_mat) AS jumlah_material, no_ipp AS kode_trans, id_material, nm_material, update_by, update_date, ket')
							->from('warehouse_history a')
							->where('a.id_gudang',$warehouse)
							->where('a.id_material',$material)
							->where('DATE(a.update_date) >=',$tgl_awal)
							->where('DATE(a.update_date) <=',$tgl_akhir)
							->where('a.kd_gudang_dari <>','BOOKING')
							->group_start()
							->like('a.ket', $like1)
							->or_like('a.ket', $like2)
							->group_end()
							->group_by('a.id_material')
							->group_by('a.no_ipp')
							->get()
							->result_array();
		}

		$ArrTrans_IN = [];
		foreach ($transaksi as $key => $value) {
			$ArrTrans_IN[$value['id_material']][] = $value;
		}
		$dataArr = [
			'get_in_trans' 	=> $ArrTrans_IN,
			'material' 		=> $material
		];

		$data_html = $this->load->view('show_history_summary_gudang_detail', $dataArr, TRUE);
		// print_r($ArrTrans_IN);
		// echo $data_html;
		// exit;
		$Arr_Kembali	= array(
			'status'	=> 1,
			'data_html'	=> $data_html
		);
		echo json_encode($Arr_Kembali);
	}

    public function download_excel_summary_gudang(){
		//membuat objek PHPExcel
		set_time_limit(0);
		ini_set('memory_limit','1024M');
		$warehouse	= $this->uri->segment(3);
		$tgl_awal	= date('Y-m-d',strtotime($this->uri->segment(4)));
		$tgl_akhir	= date('Y-m-d',strtotime($this->uri->segment(5)));

		$this->load->library("PHPExcel");
		$objPHPExcel	= new PHPExcel();

		$whiteCenterBold    = whiteCenterBold();
		$whiteRightBold    	= whiteRightBold();
		$whiteCenter    	= whiteCenter();
		$mainTitle    		= mainTitle();
		$tableHeader    	= tableHeader();
		$tableBodyCenter    = tableBodyCenter();
		$tableBodyLeft    	= tableBodyLeft();
		$tableBodyRight    	= tableBodyRight();

		$Arr_Bulan	= array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		$sheet 		= $objPHPExcel->getActiveSheet();

		// echo '<pre>';
		// print_r($result);
		// exit;

		$Row		= 1;
		$NewRow		= $Row+1;
		$Col_Akhir	= $Cols	= getColsChar(12);
		$sheet->setCellValue('A'.$Row, 'REPORT SUMMARY MATERIAL');
		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

		$NewRow	= $NewRow +2;
		$NextRow= $NewRow;

		$sheet->setCellValue('A'.$NewRow, '#');
		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
		$sheet->getColumnDimension('A')->setWidth(10);

		$sheet->setCellValue('B'.$NewRow, 'NM MATERIAL');
		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
		$sheet->getColumnDimension('B')->setWidth(20);

		$sheet->setCellValue('C'.$NewRow, 'IN');
		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D'.$NewRow, 'OUT');
		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E'.$NewRow, 'Type');
		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
		$sheet->getColumnDimension('E')->setAutoSize(true);

		$sheet->setCellValue('F'.$NewRow, 'Kode Transaksi');
		$sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
		$sheet->getColumnDimension('F')->setAutoSize(true);

		$sheet->setCellValue('G'.$NewRow, 'Qty');
		$sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
		$sheet->getColumnDimension('G')->setAutoSize(true);

		$sheet->setCellValue('H'.$NewRow, 'Keterangan');
		$sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('H'.$NewRow.':H'.$NextRow);
		$sheet->getColumnDimension('H')->setAutoSize(true);

		$sheet->setCellValue('I'.$NewRow, 'Incoming From');
		$sheet->getStyle('I'.$NewRow.':I'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('I'.$NewRow.':I'.$NextRow);
		$sheet->getColumnDimension('I')->setAutoSize(true);

		$sheet->setCellValue('J'.$NewRow, 'Outgoing To');
		$sheet->getStyle('J'.$NewRow.':J'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('J'.$NewRow.':J'.$NextRow);
		$sheet->getColumnDimension('J')->setAutoSize(true);

		$sheet->setCellValue('K'.$NewRow, 'By');
		$sheet->getStyle('K'.$NewRow.':K'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('K'.$NewRow.':K'.$NextRow);
		$sheet->getColumnDimension('K')->setAutoSize(true);

		$sheet->setCellValue('L'.$NewRow, 'Date');
		$sheet->getStyle('L'.$NewRow.':L'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('L'.$NewRow.':L'.$NextRow);
		$sheet->getColumnDimension('L')->setAutoSize(true);

		$sheet->setCellValue('M'.$NewRow, 'No SPK');
		$sheet->getStyle('M'.$NewRow.':M'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('M'.$NewRow.':M'.$NextRow);
		$sheet->getColumnDimension('M')->setAutoSize(true);

		$sheet->setCellValue('N'.$NewRow, 'Product');
		$sheet->getStyle('N'.$NewRow.':N'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('N'.$NewRow.':N'.$NextRow);
		$sheet->getColumnDimension('N')->setAutoSize(true);

		// $sheet->setCellValue('O'.$NewRow, 'Product');
		// $sheet->getStyle('O'.$NewRow.':O'.$NextRow)->applyFromArray($tableHeader);
		// $sheet->mergeCells('O'.$NewRow.':O'.$NextRow);
		// $sheet->getColumnDimension('O')->setAutoSize(true);

		// $sheet->setCellValue('P'.$NewRow, 'Spec');
		// $sheet->getStyle('P'.$NewRow.':P'.$NextRow)->applyFromArray($tableHeader);
		// $sheet->mergeCells('P'.$NewRow.':P'.$NextRow);
		// $sheet->getColumnDimension('P')->setAutoSize(true);

		$result		= $this->db->group_by('a.id_material')->order_by('a.nm_material')->select('a.*')->get_where('warehouse_history a', array('a.id_gudang'=>$warehouse, 'DATE(a.update_date) >='=>$tgl_awal, 'DATE(a.update_date) <='=>$tgl_akhir))->result_array();
		// TOTAL TRANSAKSI
		$result_in	= $this->db
							->select('SUM(a.jumlah_mat) AS jumlah_material, id_material')
							->from('warehouse_history a')
							->where('a.id_gudang',$warehouse)
							->where('a.jumlah_mat > ',0)
							->where('DATE(a.update_date) >=',$tgl_awal)
							->where('DATE(a.update_date) <=',$tgl_akhir)
							->where('a.kd_gudang_dari <>','BOOKING')
							->group_start()
								->like('a.ket', 'penambahan')
								->or_like('a.ket', 'incoming')
							->group_end()
							->group_by('a.id_material')
							->get()
							->result_array();
		$ArrSumMaterial_IN = [];
		foreach ($result_in as $key => $value) {
			$ArrSumMaterial_IN[$value['id_material']] = $value['jumlah_material'];
		}
		$result_out	= $this->db
							->select('SUM(a.jumlah_mat) AS jumlah_material, id_material')
							->from('warehouse_history a')
							->where('a.id_gudang',$warehouse)
							->where('a.jumlah_mat > ',0)
							->where('DATE(a.update_date) >=',$tgl_awal)
							->where('DATE(a.update_date) <=',$tgl_akhir)
							->where('a.kd_gudang_dari <>','BOOKING')
							->like('a.ket', 'pengurangan')
							->group_by('a.id_material')
							->get()
							->result_array();
		$ArrSumMaterial_OUT = [];
		foreach ($result_out as $key => $value) {
			$ArrSumMaterial_OUT[$value['id_material']] = $value['jumlah_material'];
		}

		$GET_IN_MATERIAL = $ArrSumMaterial_IN;
		$GET_OUT_MATERIAL = $ArrSumMaterial_OUT;
		$GET_WAREHOUSE = get_detail_warehouse();
		$GET_MATERIAL = get_inventory_lv4();
        $GET_USER = get_list_user();

		if($result){
			$awal_row	= $NextRow;
			$no=0;
			foreach($result as $key => $value){
				$NM_MATERIAL   = (!empty($GET_MATERIAL[$value['id_material']]['nama']))?$GET_MATERIAL[$value['id_material']]['nama']:'-';
                if($NM_MATERIAL != '-'){
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$IN_MATERIAL    = (!empty($GET_IN_MATERIAL[$value['id_material']]))?$GET_IN_MATERIAL[$value['id_material']]:'-';
                $OUT_MATERIAL   = (!empty($GET_OUT_MATERIAL[$value['id_material']]))?$GET_OUT_MATERIAL[$value['id_material']]:'-';

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $detail_name);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $NM_MATERIAL);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $IN_MATERIAL);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $OUT_MATERIAL);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				// for ($i=0; $i < 6; $i++) { 
				// 	$awal_col++;
				// 	$Cols			= getColsChar($awal_col);
				// 	$sheet->setCellValue($Cols.$awal_row, '');
				// 	$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);
				// }
				
				$material = $value['id_material'];
				$transaksi_out	= $this->db
								->select('SUM(a.jumlah_mat) AS jumlah_material, a.no_ipp AS kode_trans, a.id_material, a.nm_material, a.update_by, a.update_date, a.ket, "OUT" AS type, a.id_gudang_dari, a.kd_gudang_dari, a.id_gudang_ke, a.kd_gudang_ke, y.no_spk, z.nama_product')
								->from('warehouse_history a')
								->join('so_internal_spk y','a.no_ipp=y.kode_det','left')
								->join('so_internal z','y.id_so=z.id','left')
								->where('a.id_gudang',$warehouse)
								->where('a.id_material',$material)
								->where('a.jumlah_mat > ',0)
								->where('DATE(a.update_date) >=',$tgl_awal)
								->where('DATE(a.update_date) <=',$tgl_akhir)
								->where('a.kd_gudang_dari <>','BOOKING')
								->like('a.ket', 'pengurangan')
								->group_by('a.id_material')
								->group_by('a.no_ipp')
								->get()
								->result_array();
		
				$transaksi_in	= $this->db
								->select('SUM(a.jumlah_mat) AS jumlah_material, a.no_ipp AS kode_trans, a.id_material, a.nm_material, a.update_by, a.update_date, a.ket, "IN" AS type, a.id_gudang_dari, a.kd_gudang_dari, a.id_gudang_ke, a.kd_gudang_ke, y.no_spk, z.nama_product')
								->from('warehouse_history a')
								->join('so_internal_spk y','a.no_ipp=y.kode_det','left')
								->join('so_internal z','y.id_so=z.id','left')
								->where('a.id_gudang',$warehouse)
								->where('a.id_material',$material)
								->where('a.jumlah_mat > ',0)
								->where('DATE(a.update_date) >=',$tgl_awal)
								->where('DATE(a.update_date) <=',$tgl_akhir)
								->where('a.kd_gudang_dari <>','BOOKING')
								->group_start()
								->like('a.ket', 'penambahan')
								->or_like('a.ket', 'incoming')
								->group_end()
								->group_by('a.id_material')
								->group_by('a.no_ipp')
								->get()
								->result_array();

				$transaksi = array_merge($transaksi_out,$transaksi_in);

				foreach ($transaksi as $key2 => $value2) {
					$NM_MATERIAL   = (!empty($GET_MATERIAL[$value2['id_material']]['nama']))?$GET_MATERIAL[$value2['id_material']]['nama']:'-';
                    if($NM_MATERIAL != '-'){
                        $awal_row++;
                        $awal_col	= 0;

                        $awal_col++;
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, '');
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);


                        $awal_col++;
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $NM_MATERIAL);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $awal_col++;
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, '');
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $awal_col++;
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, '');
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $awal_col++;
                        $type	= strtoupper($value2['type']);
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $type);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $awal_col++;
                        $kode_trans	= strtoupper($value2['kode_trans']);
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $kode_trans);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $awal_col++;
                        $jumlah_material	= $value2['jumlah_material'];
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $jumlah_material);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

                        $awal_col++;
                        $ket	= $value2['ket'];
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $ket);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $KD_GUDANG_DARI = $value2['kd_gudang_dari'];
                        if(!empty($value2['id_gudang_dari'])){
                            $KD_GUDANG_DARI = (!empty($GET_WAREHOUSE[$value2['id_gudang_dari']]['nm_gudang']))?$GET_WAREHOUSE[$value2['id_gudang_dari']]['nm_gudang']:'';
                        }

                        $KD_GUDANG_KE = $value2['kd_gudang_ke'];
                        if(!empty($value2['id_gudang_ke'])){
                            $KD_GUDANG_KE = (!empty($GET_WAREHOUSE[$value2['id_gudang_ke']]['nm_gudang']))?$GET_WAREHOUSE[$value2['id_gudang_ke']]['nm_gudang']:'';
                        }

                        $awal_col++;
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, strtoupper($KD_GUDANG_DARI));
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $awal_col++;
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, strtoupper($KD_GUDANG_KE));
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $NM_USER   = (!empty($GET_USER[$value2['update_by']]['nama']))?$GET_USER[$value2['update_by']]['nama']:'-';

                        $awal_col++;
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $NM_USER);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

                        $awal_col++;
                        $update_date	= $value2['update_date'];
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $update_date);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

						$awal_col++;
                        $no_spk	= $value2['no_spk'];
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $no_spk);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

						$awal_col++;
                        $nama_product	= $value2['nama_product'];
                        $Cols			= getColsChar($awal_col);
                        $sheet->setCellValue($Cols.$awal_row, $nama_product);
                        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);
                    }
                    }
                }
				
			

			}
		}


		$sheet->setTitle('SUMMARY');
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
		header('Content-Disposition: attachment;filename="summary-material.xls"');
		//unduh file
		$objWriter->save("php://output");
	}
}

?>
