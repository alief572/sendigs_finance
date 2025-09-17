<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Bom extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'BOM.View';
    protected $addPermission  	= 'BOM.Add';
    protected $managePermission = 'BOM.Manage';
    protected $deletePermission = 'BOM.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array('Mpdf'));
        $this->load->model(array('Bom/bom_model'
                                ));
        $this->template->title('BOM Standard Lainnya');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    //========================================================BOM

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->bom_model->get_data('bom_header','deleted','N');
      history("View index BOM standard lainnya");
      $this->template->set('results', $data);
      $this->template->title('BOM Standard Lainnya');
      $this->template->render('index');
    }

    public function data_side_bom(){
      $this->bom_model->get_json_bom();
    }

    public function add(){
      if($this->input->post()){
        $Arr_Kembali	= array();
    	  	$data			= $this->input->post();
        // print_r($data);
        // exit;
    		$session 		  = $this->session->userdata('app_session');
        $Detail 	    = $data['Detail'];
        $Ym					  = date('ym');
        $no_bom        = $data['no_bom'];
        $no_bomx        = $data['no_bom'];
        $check_p			  = "SELECT * FROM bom_header WHERE id_product ='".$data['id_product']."' ";
        $num_p		= $this->db->query($check_p)->num_rows();
        // if($num_p < 1){
          $created_by   = 'updated_by';
          $created_date = 'updated_date';
          $tanda        = 'Insert ';
          if(empty($no_bomx)){
            //pengurutan kode
            $srcMtr			  = "SELECT MAX(no_bom) as maxP FROM bom_header WHERE no_bom LIKE 'BOM".$Ym."%' ";
            $numrowMtr		= $this->db->query($srcMtr)->num_rows();
            $resultMtr		= $this->db->query($srcMtr)->result_array();
            $angkaUrut2		= $resultMtr[0]['maxP'];
            $urutan2		  = (int)substr($angkaUrut2, 7, 3);
            $urutan2++;
            $urut2			  = sprintf('%03s',$urutan2);
            $no_bom	      = "BOM".$Ym.$urut2;

            $created_by   = 'created_by';
            $created_date = 'created_date';
            $tanda        = 'Update ';
          }

          	$ArrHeader		= array(
				'no_bom'			    => $no_bom,
				'id_product'	    => $data['id_product'],
				'kode'	    => $data['kode'],
				'variant_product'	    => $data['variant_product'],
				'color'	    => $data['color_product'],
				'keterangan'	    => $data['keterangan'],
				'waste_product'	    => str_replace(',','',$data['waste_product']),
				'waste_setting'	    => str_replace(',','',$data['waste_setting']),
				'waste_setting_resin'	=> str_replace(',','',$data['waste_setting_resin']),
				'waste_setting_glass'	=> str_replace(',','',$data['waste_setting_glass']),
				'moq'	    			=> str_replace(',','',$data['moq']),
				'total_material'	    => str_replace(',','',$data['tot_material']),
				'waste_total'	    => str_replace(',','',$data['waste_total']),
				'width'	    => str_replace(',','',$data['width']),
				'length'	    => str_replace(',','',$data['length']),
				$created_by	    => $session['id_user'],
				$created_date	  => date('Y-m-d H:i:s')
          	);

          $ArrDetail	= array();
          $ArrDetail2	= array();
          foreach($Detail AS $val => $valx){
            $urut				= sprintf('%03s',$val);
            $ArrDetail[$val]['no_bom'] 			= $no_bom;
            $ArrDetail[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
            $ArrDetail[$val]['code_material'] 	= $valx['code_material'];
            $ArrDetail[$val]['weight'] 	 		= str_replace(',','',$valx['weight']);
            $ArrDetail[$val]['ket'] 		 	= $valx['ket'];
          }

          // print_r($ArrHeader);
      		// print_r($ArrDetail);
      		// exit;

      		$this->db->trans_start();
          if(empty($no_bomx)){
            $this->db->insert('bom_header', $ArrHeader);
          }
          if(!empty($no_bomx)){
            $this->db->where('no_bom', $no_bom);
            $this->db->update('bom_header', $ArrHeader);
          }

          if(!empty($ArrDetail)){
            $this->db->delete('bom_detail', array('no_bom' => $no_bom));
      			$this->db->insert_batch('bom_detail', $ArrDetail);
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
            history($tanda." BOM ".$no_bom);
      		}
        // }
        // else{
        //   $Arr_Data	= array(
        //     'pesan'		=>'Product sudah digunakan .',
        //     'status'	=> 0
        //   );
        // }

    		echo json_encode($Arr_Data);
      }
      else{
      	$session  = $this->session->userdata('app_session');
        $no_bom 	  = $this->uri->segment(3);
    		$header   = $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
        $detail   = $this->db->get_where('bom_detail',array('no_bom' => $no_bom))->result_array();
  			$product    = $this->bom_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product','code_lv1 <>'=>'P123000008'));
        $material    = $this->bom_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'material'));

        $variant_product  = $this->bom_model->get_data_where_array('list',array('menu'=>'bom std lainnya','category'=>'variant product'));
        $color_product    = $this->bom_model->get_data_where_array('list',array('menu'=>'bom std lainnya','category'=>'color'));

        // print_r($header);
        // exit;

		$bom_standard_list   = $this->db->select('a.*, b.nama')->join('new_inventory_4 b','a.id_product=b.code_lv4','left')->get_where('bom_header a',array('a.category' => 'standard', 'a.deleted_date'=>NULL))->result_array();

  			$data = [
          'header' => $header,
          'detail' => $detail,
          'product' => $product,
		  'bom_standard_list' => $bom_standard_list,
          'list_variant_product' => $variant_product,
          'list_color_product' => $color_product,
          'material' => $material
  			];
  			$this->template->set('results', $data);
        $this->template->title('Add BOM Standard Lainnya');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add',$data);
      }
    }

    public function detail(){
      // $this->auth->restrict($this->viewPermission);
      $no_bom 	= $this->input->post('no_bom');
      $header = $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
      $detail = $this->db->get_where('bom_detail',array('no_bom' => $no_bom))->result_array();
      $product    = $this->bom_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product'));
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

    public function get_add(){
  		$id 	= $this->uri->segment(3);
  		$no 	= 0;

      $material    = $this->bom_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'material'));
  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='header_".$id."'>";
  				$d_Header .= "<td align='center'>".$id."</td>";
  				$d_Header .= "<td align='left'>";
          $d_Header .= "<select name='Detail[".$id."][code_material]' class='chosen_select form-control input-sm inline-blockd material'>";
          $d_Header .= "<option value='0'>Select Material Name</option>";
          foreach($material AS $valx){
            $d_Header .= "<option value='".$valx->code_lv4."'>".strtoupper($valx->nama)."</option>";
          }
          $d_Header .= 		"</select>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][weight]' class='form-control input-md autoNumeric4 qty text-right' placeholder='Weight'>";
  				$d_Header .= "</td>";
				  $d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='Detail[".$id."][ket]' class='form-control input-md' placeholder='Keterangan'>";
						  $d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
  				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
  				$d_Header .= "</td>";
  			$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

    public function hapus(){
        $data = $this->input->post();
        $session 		= $this->session->userdata('app_session');
        $no_bom  = $data['id'];

        $ArrHeader		= array(
          'deleted'			  => "Y",
          'deleted_by'	  => $session['id_user'],
          'deleted_date'	=> date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
            $this->db->where('no_bom', $no_bom);
            $this->db->update('bom_header', $ArrHeader);
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
          history("Delete data BOM Standard Lainnya ".$no_bom);
        }

        echo json_encode($Arr_Data);

    }

    public function excel_report_all_bom(){
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$this->load->library("PHPExcel");
  		$objPHPExcel	= new PHPExcel();

  		$tableHeader 	= tableHeader();
		$mainTitle 		= mainTitle();
		$tableBodyCenter= tableBodyCenter();
		$tableBodyLeft 	= tableBodyLeft();  
		$tableBodyRight = tableBodyRight();

    	$sheet 		= $objPHPExcel->getActiveSheet();

  		$product    = $this->db
						->select('a.*, b.nama AS nm_product')
						->order_by('a.no_bom','desc')
						->join('new_inventory_4 b','a.id_product=b.code_lv4','left')
						->get_where('bom_header a',array('a.deleted_date'=>NULL,'a.category'=>'standard'))
						->result_array();

		$Row		= 1;
		$NewRow		= $Row+1;
		$Col_Akhir	= $Cols	= getColsChar(12);
		$sheet->setCellValue('A'.$Row, 'BOM STANDARD LAINNYA');
		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

		$NewRow	= $NewRow +2;
		$NextRow= $NewRow +1;

		$sheet->setCellValue('A'.$NewRow, '#');
		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->setCellValue('B'.$NewRow, 'Product Name');
		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->setCellValue('C'.$NewRow, 'Variant');
		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D'.$NewRow, 'Color');
		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E'.$NewRow, 'Keterangan');
		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
		$sheet->getColumnDimension('E')->setAutoSize(true);

		$sheet->setCellValue('F'.$NewRow, 'MOQ');
		$sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
		$sheet->getColumnDimension('F')->setAutoSize(true);

		$sheet->setCellValue('G'.$NewRow, 'Total Berat');
		$sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
		$sheet->getColumnDimension('G')->setAutoSize(true);

		$sheet->setCellValue('H'.$NewRow, 'Waste Product (Kg)');
		$sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('H'.$NewRow.':H'.$NextRow);
		$sheet->getColumnDimension('H')->setAutoSize(true);

		$sheet->setCellValue('I'.$NewRow, 'Waste Setting & Cleaning Resin (Kg)');
		$sheet->getStyle('I'.$NewRow.':I'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('I'.$NewRow.':I'.$NextRow);
		$sheet->getColumnDimension('I')->setAutoSize(true);

		$sheet->setCellValue('J'.$NewRow, 'Waste Setting & Cleaning Glass (Kg)');
		$sheet->getStyle('J'.$NewRow.':J'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('J'.$NewRow.':J'.$NextRow);
		$sheet->getColumnDimension('J')->setAutoSize(true);

		$sheet->setCellValue('K'.$NewRow, 'Width (m)');
		$sheet->getStyle('K'.$NewRow.':K'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('K'.$NewRow.':K'.$NextRow);
		$sheet->getColumnDimension('K')->setAutoSize(true);

		$sheet->setCellValue('L'.$NewRow, 'Length (m)');
		$sheet->getStyle('L'.$NewRow.':L'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('L'.$NewRow.':L'.$NextRow);
		$sheet->getColumnDimension('L')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor			= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

  				$awal_col++;
  				$nm_product		= $row_Cek['nm_product'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nm_product);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
  				$variant_product= $row_Cek['variant_product'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $variant_product);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
  				$color	= $row_Cek['color'];
  				$Cols	= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $color);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
  				$keterangan	= $row_Cek['keterangan'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $keterangan);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
  				$moq	= $row_Cek['moq'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $moq);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				$SUM_WEIGHT = $this->db->query("SELECT SUM(weight) AS berat FROM bom_detail WHERE no_bom = '".$row_Cek['no_bom']."' ")->result();
				$awal_col++;
				$status_date	= number_format($SUM_WEIGHT[0]->berat,4);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $status_date);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
  				$waste_product	= $row_Cek['waste_product'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $waste_product);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
  				$waste_setting_resin	= $row_Cek['waste_setting_resin'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $waste_setting_resin);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
  				$waste_setting_glass	= $row_Cek['waste_setting_glass'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $waste_setting_glass);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
  				$width	= $row_Cek['width'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $width);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
  				$length	= $row_Cek['length'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $length);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);

  			}
  		}

  		$sheet->setTitle('BOM');
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
  		header('Content-Disposition: attachment;filename="bom-standard-lainnya.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function excel_report_all_bom_detail(){
      	$kode_bom = $this->uri->segment(3);
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$this->load->library("PHPExcel");
  		$objPHPExcel	= new PHPExcel();

  		$tableHeader 	= tableHeader();
		$mainTitle 		= mainTitle();
		$tableBodyCenter= tableBodyCenter();
		$tableBodyLeft 	= tableBodyLeft();  
		$tableBodyRight = tableBodyRight();

    	$sheet 		= $objPHPExcel->getActiveSheet();

  		$sql = "
  			SELECT
  				a.id_product,
				a.variant_product,
          		b.code_material,
          		b.weight,
				c.nama AS nm_product
  			FROM
  				bom_header a 
				LEFT JOIN bom_detail b ON a.no_bom = b.no_bom
				LEFT JOIN new_inventory_4 c ON a.id_product = c.code_lv4
  		    WHERE 
				a.no_bom = '".$kode_bom."' 
				AND b.no_bom = '".$kode_bom."'
				AND a.category = 'standard'
  			ORDER BY
  				b.id ASC
  		";
  		$product    = $this->db->query($sql)->result_array();

		$Row		= 1;
		$NewRow		= $Row+1;
		$Col_Akhir	= $Cols	= getColsChar(3);
		$sheet->setCellValue('A'.$Row, 'BOM HEAD TO HEAD DETAIL');
		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

		$NewRow	= $NewRow + 2;

		$sheet->setCellValue('A'.$NewRow, $product[0]['nm_product']);
		$sheet->getStyle('A'.$NewRow.':C'.$NewRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('A'.$NewRow.':C'.$NewRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$NewRow	 = $NewRow + 1;
		$NextRow = $NewRow;

		$sheet->setCellValue('A'.$NewRow, $product[0]['variant_product']);
		$sheet->getStyle('A'.$NewRow.':C'.$NewRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('A'.$NewRow.':C'.$NewRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$NewRow	 = $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A'.$NewRow, 'No');
		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->setCellValue('B'.$NewRow, 'Material Name');
		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->setCellValue('C'.$NewRow, 'Total Weight');
		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $no);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$status_date	= strtoupper(get_name('new_inventory_4','nama','code_lv4', $row_Cek['code_material']));
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $status_date);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$status_date	= number_format($row_Cek['weight'],4);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $status_date);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);
  			}
  		}


  		$sheet->setTitle('List BOM DETAIL');
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
  		header('Content-Disposition: attachment;filename="bom-detail-'.$kode_bom.'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

	  public function get_add_copy(){
		$no_bom 	= $this->uri->segment(3);
		$no 	= 0;

		$material    = $this->bom_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'material'));
		$detail   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom))->result_array();
		$d_Header = "";
		// $d_Header .= "<tr>";
		$id = 0;
		foreach ($detail as $key => $value) { $id++;
			$d_Header .= "<tr class='header_".$id."'>";
				$d_Header .= "<td align='center'>".$id."</td>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "<select name='Detail[".$id."][code_material]' class='chosen_select form-control input-sm inline-blockd material'>";
					$d_Header .= "<option value='0'>Select Material Name</option>";
					foreach($material AS $valx){
						$sel2 = ($valx->code_lv4 == $value['code_material'])?'selected':'';
						$d_Header .= "<option value='".$valx->code_lv4."' ".$sel2.">".strtoupper($valx->nama)."</option>";
					}
					$d_Header .= "</select>";
					$d_Header .= "</td>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "<input type='text' name='Detail[".$id."][weight]' class='form-control input-md text-right autoNumeric4 qty' placeholder='Weight'  value='".$value['weight']."'>";
				$d_Header .= "</td>";
					$d_Header .= "<td align='left'>";
				$d_Header .= "<input type='text' name='Detail[".$id."][ket]' class='form-control input-md' placeholder='Keterangan'  value='".$value['ket']."'>";
						$d_Header .= "</td>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";
		}

		//add part
		$d_Header .= "<tr id='add_".$id."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header' => $d_Header,
		));
	}
}

?>
