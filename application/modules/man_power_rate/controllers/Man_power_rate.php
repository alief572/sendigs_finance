<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Man_power_rate extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Man_Power_Rate.View';
    protected $addPermission  	= 'Man_Power_Rate.Add';
    protected $managePermission = 'Man_Power_Rate.Manage';
    protected $deletePermission = 'Man_Power_Rate.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array('Mpdf'));
        $this->load->model(array('Man_power_rate/Man_power_rate_model'
                                ));
        $this->template->title('Bill Of Material');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    //========================================================BOM

    public function index(){
      	$this->auth->restrict($this->viewPermission);
      	$session = $this->session->userdata('app_session');
      	$this->template->page_icon('fa fa-users');
      
		$header   	= $this->db->order_by('id','desc')->limit(1)->get('rate_man_power')->result();
		$kode		= (!empty($header[0]->kode))?$header[0]->kode:0;
		$detail_direct  = $this->db->get_where('rate_man_power_detail',array('kode' => $kode,'category'=>'direct'))->result_array();
		$detail_bpjs   	= $this->db->get_where('rate_man_power_detail',array('kode' => $kode,'category'=>'bpjs'))->result_array();
		$detail_lain   	= $this->db->get_where('rate_man_power_detail',array('kode' => $kode,'category'=>'lainnya'))->result_array();

		$data = [
			'header' => $header,
			'detail_direct' => $detail_direct,
			'detail_bpjs' => $detail_bpjs,
			'detail_lain' => $detail_lain,
		];


      	history("View index man power rate");
      	$this->template->set($data);
      	$this->template->title('Man Power Rate');
      	$this->template->render('detail');
    }

    public function data_side_bom(){
      $this->Man_power_rate_model->get_json_bom();
    }

    public function add(){
      if($this->input->post()){
        $Arr_Kembali	= array();
		$data			= $this->input->post();
        // print_r($data);
        // exit;
		$session 		= $this->session->userdata('app_session');

		$Detail1	= [];
		$Detail2	= [];
		$Detail3	= [];

		if(!empty($data['Detail'])){
        	$Detail1 	    = $data['Detail'];
		}
		if(!empty($data['Detail2'])){
        	$Detail2 	    = $data['Detail2'];
		}
		if(!empty($data['Detail3'])){
        	$Detail3 	    = $data['Detail3'];
		}
        $Ym				= date('ym');

		//pengurutan kode
		$srcMtr			  = "SELECT MAX(kode) as maxP FROM rate_man_power WHERE kode LIKE 'MPR".$Ym."%' ";
		$numrowMtr		= $this->db->query($srcMtr)->num_rows();
		$resultMtr		= $this->db->query($srcMtr)->result_array();
		$angkaUrut2		= $resultMtr[0]['maxP'];
		$urutan2		  = (int)substr($angkaUrut2, 7, 3);
		$urutan2++;
		$urut2			  = sprintf('%03s',$urutan2);
		$kode	      = "MPR".$Ym.$urut2;

		$created_by   = 'created_by';
		$created_date = 'created_date';
		$tanda        = 'Insert ';
          

          $ArrHeader			= array(
			'kode'				=> $kode,
            'tanggal'			=> date('Y-m-d'),
			'total_direct'	    	=> str_replace(',','',$data['total_direct']),
			'total_bpjs'	    	=> str_replace(',','',$data['total_bpjs']),
			'total_biaya_lain'	    => str_replace(',','',$data['total_biaya_lain']),
			
			'rate_dollar'	    	=> str_replace(',','',$data['rate_dollar']),
			'upah_per_bulan_dollar'	=> str_replace(',','',$data['upah_per_bulan_dollar']),
			'upah_per_jam_dollar'	=> str_replace(',','',$data['upah_per_jam_dollar']),
			'upah_per_bulan'	    => str_replace(',','',$data['upah_per_bulan']),
			'upah_per_jam'	    	=> str_replace(',','',$data['upah_per_jam']),
            $created_by	    	=> $session['id_user'],
            $created_date	  	=> date('Y-m-d H:i:s')
          );

          $ArrDetail1	= array();
          $ArrDetail2	= array();
          $ArrDetail3	= array();
          foreach($Detail1 AS $val => $valx){
            $ArrDetail1[$val]['kode'] 			= $kode;
            $ArrDetail1[$val]['category'] 		= 'direct';
            $ArrDetail1[$val]['nama'] 			= trim($valx['nama']);
            $ArrDetail1[$val]['nilai'] 	 		= str_replace(',','',$valx['nilai']);
			$ArrDetail1[$val]['keterangan'] 	= trim($valx['keterangan']);
          }

		  foreach($Detail2 AS $val => $valx){
            $ArrDetail2[$val]['kode'] 			= $kode;
            $ArrDetail2[$val]['category'] 		= 'bpjs';
            $ArrDetail2[$val]['nama'] 			= trim($valx['nama']);
            $ArrDetail2[$val]['nilai'] 	 		= str_replace(',','',$valx['nilai']);
			$ArrDetail2[$val]['keterangan'] 	= trim($valx['keterangan']);
          }

		  foreach($Detail3 AS $val => $valx){
            $ArrDetail3[$val]['kode'] 			= $kode;
            $ArrDetail3[$val]['category'] 		= 'lainnya';
            $ArrDetail3[$val]['nama'] 			= trim( $valx['nama']);
            $ArrDetail3[$val]['nilai'] 	 		= str_replace(',','',$valx['nilai']);
			$ArrDetail3[$val]['keterangan'] 	= trim($valx['keterangan']);
			$ArrDetail3[$val]['harga_per_pcs'] 	= str_replace(',','',$valx['harga_per_pcs']);
          }

          	// print_r($ArrHeader);
      		// print_r($ArrDetail1);
      		// print_r($ArrDetail2);
      		// print_r($ArrDetail3);
      		// exit;

      		$this->db->trans_start();
            	$this->db->insert('rate_man_power', $ArrHeader);

				if(!empty($ArrDetail1)){
					$this->db->insert_batch('rate_man_power_detail', $ArrDetail1);
				}
				if(!empty($ArrDetail2)){
					$this->db->insert_batch('rate_man_power_detail', $ArrDetail2);
				}
				if(!empty($ArrDetail3)){
					$this->db->insert_batch('rate_man_power_detail', $ArrDetail3);
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
            history($tanda." man power rate ".$kode);
      		}
    		echo json_encode($Arr_Data);
      }
      else{
		$header   	= $this->db->order_by('id','desc')->limit(1)->get('rate_man_power')->result();
		$kode		= (!empty($header[0]->kode))?$header[0]->kode:0;
		$detail_direct  = $this->db->get_where('rate_man_power_detail',array('kode' => $kode,'category'=>'direct'))->result_array();
		$detail_bpjs   	= $this->db->get_where('rate_man_power_detail',array('kode' => $kode,'category'=>'bpjs'))->result_array();
		$detail_lain   	= $this->db->get_where('rate_man_power_detail',array('kode' => $kode,'category'=>'lainnya'))->result_array();

		$data = [
			'header' => $header,
			'detail_direct' => $detail_direct,
			'detail_bpjs' => $detail_bpjs,
			'detail_lain' => $detail_lain,
		];

      	$this->template->set($data);
      	$this->template->title('Man Power Rate');
      	$this->template->render('add');
      }
    }

    public function detail(){
      // $this->auth->restrict($this->viewPermission);
      $no_bom 	= $this->input->post('no_bom');
      $header = $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
      $detail = $this->db->get_where('bom_detail',array('no_bom' => $no_bom))->result_array();
      // print_r($header);
      $data = [
        'header' => $header,
        'detail' => $detail
      ];
      $this->template->set('results', $data);
      $this->template->render('detail', $data);
    }

    public function get_add(){
  		$id 	= $this->uri->segment(3);
  		$no 	= 0;

  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='header_".$id."'>";
  				$d_Header .= "<td align='center'>".$id."</td>";
  				$d_Header .= "<td align='left'>";
					$d_Header .= "<input type='text' name='Detail[".$id."][nama]' class='form-control input-md' placeholder='Nama'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "<input type='text' name='Detail[".$id."][nilai]' class='form-control text-right input-md autoNumeric2 nilaiDirect summaryCal' placeholder='Nilai'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "<input type='text' name='Detail[".$id."][keterangan]' class='form-control input-md' placeholder='Keterangan'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='left'>";
  				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
  				$d_Header .= "</td>";
  			$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

	public function get_add2(){
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr class='header2_".$id."'>";
				$d_Header .= "<td align='center'>".$id."</td>";
				$d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='Detail2[".$id."][nama]' class='form-control input-md' placeholder='Nama'>";
			  $d_Header .= "</td>";
			  $d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='Detail2[".$id."][nilai]' class='form-control text-right input-md autoNumeric2 nilaiBPJS summaryCal' placeholder='Nilai'>";
			  $d_Header .= "</td>";
			  $d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='Detail2[".$id."][keterangan]' class='form-control input-md' placeholder='Keterangan'>";
			  $d_Header .= "</td>";
			  $d_Header .= "<td align='left'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='add2_".$id."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart2' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}

	public function get_add3(){
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr class='header3_".$id."'>";
				$d_Header .= "<td align='center'>".$id."</td>";
				$d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='Detail3[".$id."][nama]' class='form-control input-md' placeholder='Nama'>";
			  $d_Header .= "</td>";
			  $d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='Detail3[".$id."][nilai]' class='form-control text-right input-md autoNumeric2 nilaiLain summaryCal' placeholder='Nilai'>";
			  $d_Header .= "</td>";
			  $d_Header .= "<td align='left'>";
				  $d_Header .= "<input type='text' name='Detail3[".$id."][keterangan]' class='form-control input-md' placeholder='Keterangan'>";
			  $d_Header .= "</td>";
			  $d_Header .= "<td align='left'>";
				$d_Header .= "<input type='text' name='Detail3[".$id."][harga_per_pcs]' class='form-control text-right input-md autoNumeric2' placeholder='Harga /Pcs'>";
			$d_Header .= "</td>";
			  $d_Header .= "<td align='left'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='add3_".$id."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart3' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
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
          history("Delete data BOM ".$no_bom);
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
						->select('a.*, a.additive_name AS nm_product')
						->order_by('a.no_bom','desc')
						->get_where('bom_header a',array('a.deleted_date'=>NULL,'a.category'=>'additive'))
						->result_array();

		$Row		= 1;
		$NewRow		= $Row+1;
		$Col_Akhir	= $Cols	= getColsChar(4);
		$sheet->setCellValue('A'.$Row, 'BOM ADDITIVE');
		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

		$NewRow	= $NewRow +2;
		$NextRow= $NewRow +1;

		$sheet->setCellValue('A'.$NewRow, 'No');
		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->setCellValue('B'.$NewRow, 'Kegunaan Additive');
		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->setCellValue('C'.$NewRow, 'Waste Product (%)');
		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D'.$NewRow, 'Waste Setting/Cleaning (%)');
		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

  				$awal_col++;
  				$nm_product	= $row_Cek['nm_product'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nm_product);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
  				$waste_product	= $row_Cek['waste_product'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $waste_product);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
  				$waste_setting	= $row_Cek['waste_setting'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $waste_setting);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

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
  		header('Content-Disposition: attachment;filename="bom-additive.xls"');
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
          		b.persen,
				a.additive_name AS nm_product
  			FROM
  				bom_header a 
				LEFT JOIN bom_detail b ON a.no_bom = b.no_bom
  		    WHERE 
				a.no_bom = '".$kode_bom."' 
				AND b.no_bom = '".$kode_bom."'
				AND a.category = 'additive'
  			ORDER BY
  				b.id ASC
  		";
  		$product    = $this->db->query($sql)->result_array();

		$Row		= 1;
		$NewRow		= $Row+1;
		$Col_Akhir	= $Cols	= getColsChar(3);
		$sheet->setCellValue('A'.$Row, 'BOM ADDITIVE DETAIL');
		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

		$NewRow	= $NewRow + 2;

		$sheet->setCellValue('A'.$NewRow, $product[0]['nm_product']);
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

		$sheet->setCellValue('C'.$NewRow, 'Pengurangan Resin (%)');
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
				$status_date	= number_format($row_Cek['persen'],2);
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
  		header('Content-Disposition: attachment;filename="bom-additive-detail-'.$kode_bom.'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

	public function update_kurs(){
        $data 		= $this->input->post();
        $session 	= $this->session->userdata('app_session');
		$header   	= $this->db->order_by('id','desc')->limit(1)->get('rate_man_power')->result();
		$kurs   	= $this->db->order_by('id','desc')->limit(1)->get_where('master_kurs',array('deleted_date'=>NULL))->result();
        $id  		= $header[0]->id;

        $ArrHeader = array(
          'id_kurs'		=> $kurs[0]->id,
          'rate_dollar'	=> $kurs[0]->kurs,
          'kurs_tanggal'=> $kurs[0]->tanggal,
          'kurs_by'	  	=> $session['id_user'],
          'kurs_date'	=> date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
            $this->db->where('id', $id);
            $this->db->update('rate_man_power', $ArrHeader);
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
          history("Update Kurs di man power rate");
        }

        echo json_encode($Arr_Data);

    }

}

?>
