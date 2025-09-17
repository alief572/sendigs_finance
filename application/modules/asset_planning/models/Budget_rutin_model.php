<?php
class Budget_rutin_model extends BF_Model {

	public function __construct() {
		parent::__construct();
	}
	
	public function index_rutin(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)))."/index_rutin"; 
		$Arr_Akses			= getAcccesmenu($controller);
		if($Arr_Akses['read'] !='1'){
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$category = $this->db->query("SELECT * FROM con_nonmat_category_awal")->result_array();
		
		$data = array(
			'title'			=> 'Indeks Of Budget Rutin',
			'action'		=> 'index',
			'category'		=> $category,
			'akses_menu'	=> $Arr_Akses
		);
		history('View Data Budget Rutin');
		$this->load->view('Budget_rutin/index',$data);
	}
	
	public function kompilasi(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)))."/index_rutin"; 
		$Arr_Akses			= getAcccesmenu($controller);
		if($Arr_Akses['read'] !='1'){
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}
		$id_category= $this->uri->segment(3);
		$WHERE = "";
		$nm_category = '';
		if($id_category != '0'){
			$WHERE = " WHERE jenis_barang='".$id_category."'";
			$nm_category = ' ['.strtoupper(get_name('con_nonmat_category_awal','category','id',$id_category)).']';
		}
		
		$group_header = $this->db->query("SELECT department, costcenter FROM budget_rutin_header GROUP BY department, costcenter")->result_array();
		$group_barang = $this->db->query("SELECT id_barang, jenis_barang, satuan FROM budget_rutin_detail ".$WHERE." GROUP BY id_barang ORDER BY jenis_barang ASC, id_barang")->result_array();
		
		$data = array(
			'title'			=> 'Compile Budget'.$nm_category,
			'action'		=> 'index',
			'id_category'	=> $id_category,
			'akses_menu'	=> $Arr_Akses,
			'group_header' => $group_header,
			'group_barang' => $group_barang
		);
		history('View Data Budget Kompilasi');
		$this->load->view('Budget_rutin/kompilasi',$data);
	}
	
	public function detail_rutin(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)))."/index_rutin"; 
		$Arr_Akses			= getAcccesmenu($controller);
		if($Arr_Akses['read'] !='1'){
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}
		
		$code = $this->uri->segment(3);
		$header = $this->db->query("SELECT * FROM budget_rutin_header WHERE code_budget='".$code."' ")->result();
		
		$data = array(
			'title'			=> 'Add Budget Rutin',
			'action'		=> 'add',
			'akses_menu'	=> $Arr_Akses,
			'header'		=> $header,
			'code'			=> $code
		);
		$this->load->view('Budget_rutin/modal_detail',$data);
	}
	
	public function add_rutin(){
		if($this->input->post()){
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$dateTime		= date('Y-m-d H:i:s');
            // print_r($data); exit;
			//header
			$code_budgetx  	= $data['code_budget'];
			$code_budget  	= $data['code_budget'];
			$tanda        	= $data['tanda'];
			$department 	= $data['department'];
			$costcenter 	= $data['costcenter'];
			$detail 		= $data['detail'];
			$ym = date('ym');
			
			$get_rev 	= get_name('budget_rutin_header', 'rev', 'code_budget', $code_budget);
			$rev_		= (!empty($get_rev))?$get_rev:0;
			
			$ArrHeader		= array(
				'department'	=> $department,
				'costcenter'	=> $costcenter,
				'rev'			=> $rev_ + 1,
				'updated_by'	=> $data_session['ORI_User']['username'],
				'updated_date'	=> $dateTime
			);
			
			if(empty($code_budgetx)){
				//pengurutan kode
				$srcMtr			= "SELECT MAX(code_budget) as maxP FROM budget_rutin_header WHERE code_budget LIKE 'BUD".$ym."%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 7, 3);
				$urutan2++;
				$urut2			= sprintf('%03s',$urutan2);
				$code_budget	= "BUD".$ym.$urut2;
				
				$ArrHeader		= array(
					'code_budget'	=> $code_budget,
					'tanggal'		=> date('Y-m-d'),
					'department'	=> $department,
					'costcenter'	=> $costcenter,
					'rev'			=> 0,
					'created_by'	=> $data_session['ORI_User']['username'],
					'created_date'	=> $dateTime
				);
			}

			

			$ArrDetail	= array();
			foreach($detail AS $val => $valx){
				if($valx['id_barang'] <> '0'){
					$ArrDetail[$val]['code_budget'] 	= $code_budget;
					$ArrDetail[$val]['jenis_barang'] 	= $valx['jenis_barang'];
					$ArrDetail[$val]['id_barang'] 		= $valx['id_barang'];
					$ArrDetail[$val]['kebutuhan_month'] = str_replace(',','',$valx['kebutuhan_month']);
					$ArrDetail[$val]['satuan'] 			= $valx['satuan'];
				}
			}
			
			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// exit;
			
			$this->db->trans_start();
				if(!empty($code_budgetx)){
					$this->db->where(array('code_budget' => $code_budget));
					$this->db->update('budget_rutin_header', $ArrHeader);
				}
				if(empty($code_budgetx)){
					$this->db->insert('budget_rutin_header', $ArrHeader);
				}
				if(!empty($ArrDetail)){
					$this->db->delete('budget_rutin_detail', array('code_budget' => $code_budget));
					$this->db->insert_batch('budget_rutin_detail', $ArrDetail);
				}
			$this->db->trans_complete();


			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data failed. Please try again later ...',
					'status'	=> 0
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data success. Thanks ...',
					'status'	=> 1
				);
				history($tanda.' Budget Rutin '.$code_budget);
			}
			echo json_encode($Arr_Kembali);
		}
		else{
			$controller			= ucfirst(strtolower($this->uri->segment(1)))."/index_rutin"; 
			$Arr_Akses			= getAcccesmenu($controller);
			if($Arr_Akses['read'] !='1'){
				$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
				redirect(site_url('dashboard'));
			}
			
			$code = $this->uri->segment(3);
			$header = $this->db->query("SELECT * FROM budget_rutin_header WHERE code_budget='".$code."' ")->result();
			$jns_brg = (!empty($header[0]->jenis_barang))?$header[0]->jenis_barang:0;
			$satuan				= $this->db->query("SELECT * FROM raw_pieces WHERE flag_active = 'Y' AND `delete` = 'N' ORDER BY kode_satuan ASC")->result_array();
			
			$data = array(
				'title'			=> 'Add Budget Rutin',
				'action'		=> 'add',
				'akses_menu'	=> $Arr_Akses,
				'header'		=> $header,
				'satuan'		=> $satuan,
				'code'		=> $code
			);
			$this->load->view('Budget_rutin/add',$data);
		}
	}
	
	public function get_add(){
		$id 			= $this->uri->segment(3);
		$jenis_barangx 	= $this->uri->segment(4);
		$class_no 		= $this->uri->segment(5);

		// $jenis_barang		= $this->db->query("SELECT * FROM con_nonmat_new WHERE category_awal='".$jenis_barangx."' ORDER BY material_name ASC, spec ASC ")->result_array();
		$satuan				= $this->db->query("SELECT * FROM raw_pieces WHERE flag_active = 'Y' AND `delete` = 'N' ORDER BY kode_satuan ASC")->result_array();
		$jenis_barang		= $this->db->select('code_group,material_name,spec')->get_where('con_nonmat_new',array('category_awal'=>$jenis_barangx,'deleted'=>'N'))->result_array();

		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='header_".$id."'>";
			$d_Header .= "<td align='center'>".$class_no."</td>";
			$d_Header .= "<td align='left'>";
				$d_Header .= "<select name='detail[".$id."][id_barang]' data-no='".$id."' class='chosen_select form-control input-sm getSpec'>";
				$d_Header .= "<option value='0'>Select Barang</option>";
				foreach($jenis_barang AS $val => $valx){
				  $d_Header .= "<option value='".$valx['code_group']."'>".strtoupper($valx['material_name']." - ".$valx['spec'])."</option>";
				}
				$d_Header .= "</select>";
			$d_Header .= "</td>";
			$d_Header .= "<td align='left'>";
				$d_Header .= "<input type='hidden' name='detail[".$id."][jenis_barang]' class='form-control input-md' value='".$jenis_barangx."'>";
				$d_Header .= "<input name='detail[".$id."][spesifikasi]' id='spec_".$id."' class='form-control input-md' readonly placeholder='Spesifikasi'>";
			$d_Header .= "</td>";
			$d_Header .= "<td align='left'>";
				$d_Header .= "<input name='detail[".$id."][kebutuhan_month]' class='form-control text-center input-md maskM' placeholder='0' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
			$d_Header .= "</td>";
			$d_Header .= "<td align='left'>";
				$d_Header .= "<select name='detail[".$id."][satuan]' data-no='".$id."' id='satuan_".$id."' class='chosen_select form-control input-sm'>";
				$d_Header .= "<option value='0'>Select Satuan</option>";
				foreach($satuan AS $val => $valx){
				  $d_Header .= "<option value='".$valx['id_satuan']."'>".strtoupper($valx['kode_satuan'])."</option>";
				}
				$d_Header .= "</select>";
			$d_Header .= "</td>";
			$d_Header .= "<td align='center'>";
			$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
			$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='add_".$id."' class='".$class_no."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id_barang='".$jenis_barangx."' class='btn btn-sm btn-success addPart' title='Add Item'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Item</button></td>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}
	
	public function get_spec(){
		$id 		= $this->uri->segment(3);
		$spec		= $this->db->query("SELECT spec, satuan FROM con_nonmat_new WHERE code_group='".$id."' LIMIT 1")->result();
		$satuan		= $this->db->query("SELECT * FROM raw_pieces WHERE flag_active = 'Y' AND `delete` = 'N' ORDER BY kode_satuan ASC")->result_array();
		
		$option = '';
		foreach($satuan AS $val => $valx){
			$sel = ($valx['id_satuan'] == $spec[0]->satuan)?'selected':'';
			$option .= "<option value='".$valx['id_satuan']."' ".$sel.">".strtoupper($valx['kode_satuan'])."</option>";
		}
		echo json_encode(array(
			'spec'	=> strtoupper($spec[0]->spec),
			'option' => $option
		));
	}

	//==========================================================================================================================
	//======================================================SERVER SIDE=========================================================
	//==========================================================================================================================
    
	public function get_data_json_rutin(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)))."/index_rutin";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_rutin(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		$urut1  = 1;
        $urut2  = 0;
		foreach($query->result_array() as $row)
		{
			$total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if($asc_desc == 'asc')
            {
                $nomor = $urut1 + $start_dari;
            }
            if($asc_desc == 'desc')
            {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$tanggal = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
			$nestedData[]	= "<div align='center'>".date('d-M-Y', strtotime($tanggal))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_dept'])."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_costcenter'])."</div>";
			$nestedData[]	= "<div align='center'><span class='badge bg-blue'>".$row['rev']."</span></div>";
				$edit	= "";
				$delete	= "";
				$detail	= "";
				if($Arr_Akses['update'] =='1'){
					$edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_rutin/'.$row['code_budget']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
				}
				// if($Arr_Akses['update'] =='1'){
				// 	$detail	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/kompilasi_budget/'.$row['code_budget']."' class='btn btn-sm btn-success' title='Kompilasi Budget' data-role='qtip'><i class='fa fa-filter'></i></a>";
				// }
				if($Arr_Akses['delete'] =='1'){
					$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger hapus' data-id='".$row['code_budget']."' title='Hapus'><i class='fa fa-trash'></i></button>";
				}
			$nestedData[]	= "	<div align='center'>
                                    <button type='button' class='btn btn-sm btn-warning detail' data-code='".$row['code_budget']."' title='Detail'><i class='fa fa-eye'></i></button>
									".$edit."
									".$detail."
									".$delete."
								</div>";
			$data[] = $nestedData;
            $urut1++;
            $urut2++;
		}

		$json_data = array(
			"draw"            	=> intval( $requestData['draw'] ),
			"recordsTotal"    	=> intval( $totalData ),
			"recordsFiltered" 	=> intval( $totalFiltered ),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function query_data_json_rutin($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.nm_dept,
				c.nm_costcenter
			FROM
				budget_rutin_header a
				LEFT JOIN department b ON a.department = b.id
				LEFT JOIN costcenter c ON a.costcenter = c.id_costcenter,
				(SELECT @row:=0) r
		    WHERE  (
				a.code_budget LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.tanggal LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.department LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'code_budget',
			2 => 'tanggal',
			3 => 'department',
			4 => 'costcenter'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
	
	public function excel_kompilasi(){
		$id_category= $this->uri->segment(3);
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$this->load->library("PHPExcel");
  		// $this->load->library("PHPExcel/Writer/Excel2007");
  		$objPHPExcel	= new PHPExcel();

  		$style_header = array(
  			'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			),
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
  			),
  			'font' => array(
  				'bold' => true,
  			),
  			'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			)
  		);

  		$style_header2 = array(
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
  			),
  			'font' => array(
  				'bold' => true,
  			),
  			'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			)
  		);

  		$styleArray = array(
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
  			  ),
  			  'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			)
  		  );
  		$styleArray3 = array(
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
  			  ),
  			  'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			)
  		  );
  		 $styleArray4 = array(
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
  			  ),
  			  'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			)
  		  );
  	    $styleArray1 = array(
  			  'borders' => array(
  				  'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN
  				  )
  			  ),
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			  )
  		  );
  		$styleArray2 = array(
  			  'borders' => array(
  				  'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN
  				  )
  			  ),
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			  )
  		  );

    		$Arr_Bulan	= array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    		$sheet 		= $objPHPExcel->getActiveSheet();

			$WHERE = "";
			$nm_category = '';
			if($id_category != '0'){
				$WHERE = " WHERE jenis_barang='".$id_category."'";
				$nm_category = ' ['.strtoupper(get_name('con_nonmat_category_awal','category','id',$id_category)).']';
			}

			$group_header = $this->db->query("SELECT department, costcenter FROM budget_rutin_header GROUP BY department, costcenter")->result_array();
			$group_barang = $this->db->query("SELECT id_barang, jenis_barang, satuan FROM budget_rutin_detail ".$WHERE." GROUP BY id_barang ORDER BY jenis_barang ASC, id_barang")->result_array();
			

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(5+COUNT($group_header));
    		$sheet->setCellValue('A'.$Row, 'KOMPILASI BUDGET'.$nm_category);
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Kategori');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

			$sheet->setCellValue('C'.$NewRow, 'Nama Barang');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

			$sheet->setCellValue('D'.$NewRow, 'Spesifikasi');
    		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);
			
			$sheet->setCellValue('E'.$NewRow, 'Brand');
    		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
    		$sheet->getColumnDimension('E')->setAutoSize(true);
			
			$sheet->setCellValue('F'.$NewRow, 'Total');
    		$sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
    		$sheet->getColumnDimension('F')->setAutoSize(true);
			
			$sheet->setCellValue('G'.$NewRow, 'Total');
    		$sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
    		$sheet->getColumnDimension('G')->setAutoSize(true);
			
			$noexcel = 7;
			foreach($group_header AS $val => $valx){ $noexcel++;
				$cc = '';
				if($valx['costcenter'] <> '0'){
					$cc = strtoupper(get_name('costcenter', 'nm_costcenter', 'id_costcenter', $valx['costcenter']));
				}
				$row_name = getColsChar($noexcel);
				$sheet->setCellValue($row_name.$NewRow, strtoupper(get_name('department', 'nm_dept', 'id', $valx['department'])).", ".$cc);
				$sheet->getStyle($row_name.$NewRow.':'.$row_name.''.$NextRow)->applyFromArray($style_header);
				$sheet->mergeCells($row_name.$NewRow.':'.$row_name.''.$NextRow);
				$sheet->getColumnDimension($row_name)->setAutoSize(false);
			}

  		if($group_barang){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($group_barang as $key => $valx){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$id_produksi	= strtoupper(get_name('con_nonmat_category_awal', 'category', 'id', $valx['jenis_barang']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= strtoupper(get_name('con_nonmat_new', 'material_name', 'code_group', $valx['id_barang']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);
				
				$awal_col++;
  				$status_date	= strtoupper(get_name('con_nonmat_new', 'spec', 'code_group', $valx['id_barang']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);
				
				$awal_col++;
  				$status_date	= strtoupper(get_name('con_nonmat_new', 'brand', 'code_group', $valx['id_barang']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);
				
				$total_kebutuhan = 0;
				foreach($group_header AS $val2 => $valx2){
					$get_qty = $this->db->query("SELECT a.kebutuhan_month FROM budget_rutin_detail a LEFT JOIN budget_rutin_header b ON a.code_budget=b.code_budget WHERE a.id_barang='".$valx['id_barang']."' AND b.department='".$valx2['department']."' AND b.costcenter='".$valx2['costcenter']."' ")->result();
					$total_kebutuhan += (!empty($get_qty))?$get_qty[0]->kebutuhan_month:0;
					
				}
				
				$awal_col++;
  				$status_date	= $total_kebutuhan;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);
				
				$awal_col++;
  				$status_date	= strtoupper(get_name('raw_pieces','kode_satuan','id_satuan',$valx['satuan']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

				foreach($group_header AS $val2 => $valx2){
					$get_qty = $this->db->query("SELECT a.kebutuhan_month FROM budget_rutin_detail a LEFT JOIN budget_rutin_header b ON a.code_budget=b.code_budget WHERE a.id_barang='".$valx['id_barang']."' AND b.department='".$valx2['department']."' AND b.costcenter='".$valx2['costcenter']."' ")->result();
					$qty = (!empty($get_qty))?$get_qty[0]->kebutuhan_month:'-';
					
					$awal_col++;
					$status_date	= $qty;
					$Cols			= getColsChar($awal_col);
					$sheet->setCellValue($Cols.$awal_row, $status_date);
					$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);
				}

  			}
  		}


  		$sheet->setTitle('List Budget Barang Rutin');
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
  		header('Content-Disposition: attachment;filename="compile-budget'.strtolower(str_replace(' ','-',$nm_category)).'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}
	
}
