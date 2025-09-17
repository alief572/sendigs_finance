<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Standard_check_sheet extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Standard_Check_Sheet.View';
    protected $addPermission  	= 'Standard_Check_Sheet.Add';
    protected $managePermission = 'Standard_Check_Sheet.Manage';
    protected $deletePermission = 'Standard_Check_Sheet.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Standard_check_sheet/Standard_check_sheet_model'
        ));

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
    		
        $data = [
          'get_level_1' =>  $this->db->order_by('nama','asc')->get_where('new_inventory_1',array('category'=>'product','deleted_date'=>NULL))->result_array(),
          'get_level_2' =>  $this->db->order_by('nama','asc')->get_where('new_inventory_2',array('category'=>'product','deleted_date'=>NULL))->result_array(),
          'get_level_3' =>  $this->db->order_by('nama','asc')->get_where('new_inventory_3',array('category'=>'product','deleted_date'=>NULL))->result_array(),
        ];
        
        history("View index standard check sheet product");
        $this->template->set($data);
        $this->template->title('Standard Check Sheet');
        $this->template->render('index');
    }

    public function get_json_product_master(){
      $controller			= ucfirst(strtolower($this->uri->segment(1)));
      // $Arr_Akses			= getAcccesmenu($controller);
      $requestData		= $_REQUEST;
      $fetch					= $this->get_query_json_product_master(
        $requestData['level1'],
        $requestData['level2'],
        $requestData['level3'],
        $requestData['search']['value'],
        $requestData['order'][0]['column'],
        $requestData['order'][0]['dir'],
        $requestData['start'],
        $requestData['length']
      );
      $totalData			= $fetch['totalData'];
      $totalFiltered	= $fetch['totalFiltered'];
      $query					= $fetch['query'];

      $ENABLE_ADD     = has_permission('Standard_Check_Sheet.Add');
      $ENABLE_MANAGE  = has_permission('Standard_Check_Sheet.Manage');
      $ENABLE_VIEW    = has_permission('Standard_Check_Sheet.View');
      $ENABLE_DELETE  = has_permission('Standard_Check_Sheet.Delete');

      $get_level_1 = get_list_inventory_lv1('product');
      $get_level_2 = get_list_inventory_lv2('product');
      $get_level_3 = get_list_inventory_lv3('product');
  
      $data	= array();
      $urut1  = 1;
      $urut2  = 0;
      foreach($query->result_array() as $row){
        $total_data     = $totalData;
        $start_dari     = $requestData['start'];
        $asc_desc       = $requestData['order'][0]['dir'];
        if($asc_desc == 'asc'){
          $nomor = $urut1 + $start_dari;
        }
        if($asc_desc == 'desc'){
          $nomor = ($total_data - $start_dari) - $urut2;
        }

        $product_type 		  = (!empty($get_level_1[$row['code_lv1']]['nama']))?$get_level_1[$row['code_lv1']]['nama']:'';
				$product_category 	= (!empty($get_level_2[$row['code_lv1']][$row['code_lv2']]['nama']))?$get_level_2[$row['code_lv1']][$row['code_lv2']]['nama']:'';
				$product_jenis 		  = (!empty($get_level_3[$row['code_lv1']][$row['code_lv2']][$row['code_lv3']]['nama']))?$get_level_3[$row['code_lv1']][$row['code_lv2']][$row['code_lv3']]['nama']:'';
  
        $nestedData 	= array();
        $nestedData[]	= "<div align='left'>".$nomor."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($product_type)."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($product_category)."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($product_jenis)."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($row['nama'])."</div>";
        
        // if($row['status'] == '1'){
				// 	$Label = "<label class='label label-success'>Aktif</label>";
				// }else{
				// 	$Label = "<label class='label label-danger'>Non Aktif</label>";
				// }
        // $nestedData[]	= "<div align='left'>".$Label."</div>";
        
        $edit	= "";
        $delete	= "";
        $download	= "";
        if($ENABLE_MANAGE){
          $edit = "<a href='".base_url('standard_check_sheet/add2/'.$row['id_checksheet'])."' class='btn btn-primary btn-sm edit' title='Edit'><i class='fa fa-edit'></i></a>";
          // $download = "&nbsp;<a href='".base_url('standard_check_sheet/print_checksheet/'.$row['id_checksheet'])."' target='_blank' class='btn btn-default btn-sm edit' title='Print'><i class='fa fa-print'></i></a>";
        }
        if($ENABLE_DELETE){
          $delete = "&nbsp;<a class='btn btn-danger btn-sm delete' href='javascript:void(0)' title='Delete' data-id='".$row['id_checksheet']."'><i class='fa fa-trash'></i></a>";
        }
  
        $nestedData[]	= "<div align='center'>".$edit.$delete.$download."</div>";
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
  
    public function get_query_json_product_master($level1, $level2, $level3, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

      $WHERE_1 = "";
      if($level1 != '0'){
        $WHERE_1 = " AND a.code_lv1 = '".$level1."'";
      }

      $WHERE_2 = "";
      if($level2 != '0'){
        $WHERE_2 = " AND a.code_lv2 = '".$level2."'";
      }

      $WHERE_3 = "";
      if($level3 != '0'){
        $WHERE_3 = " AND a.code_lv3 = '".$level3."'";
      }

      $sql = "SELECT
                (@row:=@row+1) AS nomor,
                a.*,
                z.id AS id_checksheet
              FROM
                checksheet_header z
                LEFT JOIN new_inventory_4 a ON z.code_lv4 = a.code_lv4,
                (SELECT @row:=0) r
              WHERE 
                a.deleted_date IS NULL AND z.deleted_date IS NULL 
                AND a.category='product' ".$WHERE_1.$WHERE_2.$WHERE_3."
                AND (
                  a.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
                  OR a.trade_name LIKE '%".$this->db->escape_like_str($like_value)."%'
                  OR a.code LIKE '%".$this->db->escape_like_str($like_value)."%'
                )
      ";
      // echo $sql; exit;
  
      $data['totalData'] = $this->db->query($sql)->num_rows();
      $data['totalFiltered'] = $this->db->query($sql)->num_rows();
      $columns_order_by = array(
        0 => 'nomor',
        1 => 'code_lv1',
        2 => 'code_lv2',
        3 => 'code_lv3',
        4 => 'nama'
      );
  
      $sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
      $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";
  
      $data['query'] = $this->db->query($sql);
      return $data;
    }

    public function add($id=null){	
      if(empty($id)){
        $this->auth->restrict($this->addPermission);
      }
      else{
        $this->auth->restrict($this->managePermission);
      }		
      if($this->input->post()){
        $post = $this->input->post();

        $id             = $post['id'];
        $code_lv4     = $post['id_product'];
        $id_frequency   = $post['id_frequency'];
        $Detail         = (!empty($post['Detail']))?$post['Detail']:[];
        $DetailEdit         = (!empty($post['DetailEdit']))?$post['DetailEdit']:[];
       
        $last_by        = (!empty($id))?'updated_by':'created_by';
        $last_date      = (!empty($id))?'updated_date':'created_date';
        $label          = (!empty($id))?'Edit':'Add';

        $dataHeader = [
          'code_lv4'          => $code_lv4,
          'frequency_check'   => $id_frequency,
          $last_by	          => $this->id_user,
          $last_date	        => $this->datetime
        ];

        $ArrDetailEdit = [];
        $ArrID = [];
        if(!empty($DetailEdit)){
          foreach($DetailEdit AS $val => $valx){
            $ArrID[] = $valx['id'];
            $ArrDetailEdit[$val]['id'] 	      = $valx['id'];
            $ArrDetailEdit[$val]['code_lv4']  = $code_lv4;
            $ArrDetailEdit[$val]['items'] 	  = $valx['items'];
            $ArrDetailEdit[$val]['standard'] 	= $valx['standard'];
            $ArrDetailEdit[$val]['tipe'] 			= $valx['tipe'];
          }
        }

        $ArrDetail = [];
        if(!empty($Detail)){
          foreach($Detail AS $val => $valx){
            $ArrDetail[$val]['code_lv4']  = $code_lv4;
            $ArrDetail[$val]['items'] 	  = $valx['items'];
            $ArrDetail[$val]['standard'] 	= $valx['standard'];
            $ArrDetail[$val]['tipe'] 			= $valx['tipe'];
          }
        }

        // print_r($ArrDetail);
        // print_r($dataHeader);
        // exit;

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('checksheet_header',$dataHeader);
            if(!empty($ArrDetail)){
              $this->db->insert_batch('checksheet_detail',$ArrDetail);
            }
          }
          else{
            $this->db->where('id',$id);
            $this->db->update('checksheet_header',$dataHeader);

            if(!empty($ArrID)){
              $this->db->where_not_in('id',$ArrID);
              $this->db->delete('checksheet_detail');
            }

            if(!empty($ArrDetailEdit)){
              $this->db->update_batch('checksheet_detail',$ArrDetailEdit,'id');
            }

            if(!empty($ArrDetail)){
              $this->db->insert_batch('checksheet_detail',$ArrDetail);
            }
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
          history($label." checksheet: ".$code_lv4);
        }
        echo json_encode($status);
      }
      else{
        $ArrlistCT = $this->db->group_by('code_lv4')->get_where('checksheet_header',array('deleted_date'=>NULL))->result_array();
        $ArrProductCT = [];
        foreach ($ArrlistCT as $key => $value) {
          $ArrProductCT[] = $value['code_lv4'];
        }

        $header   = $this->db->get_where('checksheet_header',array('id'=>$id))->result_array();
        $code_lv4 = (!empty($header[0]['code_lv4']))?$header[0]['code_lv4']:'0';

        $detail   = $this->db->get_where('checksheet_detail',array('code_lv4'=>$code_lv4))->result_array();

        $data = [
          'ArrProductCT' => $ArrProductCT,
          'header' => $header,
          'detail' => $detail,
        ];

        $this->template->set($data);
        $this->template->title('Standard Check Sheet');
        $this->template->render('add');
      }
    }

	  public function delete(){
      $this->auth->restrict($this->deletePermission);
      
      $id = $this->input->post('id');
      $ArrUpdate = [
        'deleted_by' 	  => $this->id_user,
        'deleted_date' 	=> $this->datetime
      ];

      $this->db->trans_begin();
        $this->db->where('id',$id);
        $this->db->update("checksheet_header",$ArrUpdate);
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
        history("Delete standard checksheet : ".$id);
      }
      echo json_encode($status);
    }

    public function download_excel(){
      set_time_limit(0);
      ini_set('memory_limit','1024M');
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

      $Arr_Bulan  = array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
      $sheet      = $objPHPExcel->getActiveSheet();

      $dateX	= date('Y-m-d H:i:s');
      $Row        = 1;
      $NewRow     = $Row+1;
      $Col_Akhir  = $Cols = getColsChar(12);
      $sheet->setCellValue('A'.$Row, "PRODUCT MASTER");
      $sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
      $sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

      $NewRow = $NewRow +2;
      $NextRow= $NewRow;

      $sheet ->getColumnDimension("A")->setAutoSize(true);
      $sheet->setCellValue('A'.$NewRow, '#');
      $sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('A'.$NewRow.':A'.$NextRow);

      $sheet ->getColumnDimension("B")->setAutoSize(true);
      $sheet->setCellValue('B'.$NewRow, 'PRODUCT TYPE');
      $sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('B'.$NewRow.':B'.$NextRow);

      $sheet ->getColumnDimension("C")->setAutoSize(true);
      $sheet->setCellValue('C'.$NewRow, 'PRODUCT CATEGORY');
      $sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('C'.$NewRow.':C'.$NextRow);

      $sheet ->getColumnDimension("D")->setAutoSize(true);
      $sheet->setCellValue('D'.$NewRow, 'PRODUCT JENIS');
      $sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('D'.$NewRow.':D'.$NextRow);

      $sheet ->getColumnDimension("E")->setAutoSize(true);
      $sheet->setCellValue('E'.$NewRow, 'CODE PROGRAM');
      $sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('E'.$NewRow.':E'.$NextRow);

      $sheet ->getColumnDimension("F")->setAutoSize(true);
      $sheet->setCellValue('F'.$NewRow, 'PRODUCT MASTER');
      $sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('F'.$NewRow.':F'.$NextRow);

      $sheet ->getColumnDimension("G")->setAutoSize(true);
      $sheet->setCellValue('G'.$NewRow, 'PRODUCT CODE');
      $sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('G'.$NewRow.':G'.$NextRow);

      $sheet ->getColumnDimension("H")->setAutoSize(true);
      $sheet->setCellValue('H'.$NewRow, 'TRADE NAME');
      $sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('H'.$NewRow.':H'.$NextRow);

      $sheet ->getColumnDimension("I")->setAutoSize(true);
      $sheet->setCellValue('i'.$NewRow, 'PACKING UNIT');
      $sheet->getStyle('I'.$NewRow.':I'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('I'.$NewRow.':I'.$NextRow);

      $sheet ->getColumnDimension("J")->setAutoSize(true);
      $sheet->setCellValue('J'.$NewRow, 'KONVERSI');
      $sheet->getStyle('J'.$NewRow.':J'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('J'.$NewRow.':J'.$NextRow);

      $sheet ->getColumnDimension("K")->setAutoSize(true);
      $sheet->setCellValue('K'.$NewRow, 'UNIT MEASUREMENT');
      $sheet->getStyle('K'.$NewRow.':K'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('K'.$NewRow.':K'.$NextRow);

      $sheet ->getColumnDimension("L")->setAutoSize(true);
      $sheet->setCellValue('L'.$NewRow, 'MOQ');
      $sheet->getStyle('L'.$NewRow.':L'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('L'.$NewRow.':L'.$NextRow);

      $sheet ->getColumnDimension("M")->setAutoSize(true);
      $sheet->setCellValue('M'.$NewRow, 'MINIMUM STOK');
      $sheet->getStyle('M'.$NewRow.':M'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('M'.$NewRow.':M'.$NextRow);

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

      $dataResult   = $this->db->get_where('new_inventory_4',array('category'=>'product','deleted_date'=>NULL))->result_array();
      $GET_UNIT = get_list_satuan();
      $GET_LEVEL3 = get_inventory_lv3();
      $GET_LEVEL2 = get_inventory_lv2();
      $GET_LEVEL1 = get_list_inventory_lv1('product');
      if($dataResult){
        $awal_row   = $NextRow;
        $no = 0;
        foreach($dataResult as $key=>$vals){
          $no++;
          $awal_row++;
          $awal_col   = 0;

          $awal_col++;
          $no   = $no;
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $no);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $code_lv1   = (!empty($GET_LEVEL1[$vals['code_lv1']]['nama']))?$GET_LEVEL1[$vals['code_lv1']]['nama']:'';
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $code_lv1);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $code_lv2   = (!empty($GET_LEVEL2[$vals['code_lv2']]['nama']))?$GET_LEVEL2[$vals['code_lv2']]['nama']:'';
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $code_lv2);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $code_lv3   = (!empty($GET_LEVEL3[$vals['code_lv3']]['nama']))?$GET_LEVEL3[$vals['code_lv3']]['nama']:'';
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $code_lv3);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $code_lv4   = $vals['code_lv4'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $code_lv4);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $nama   = $vals['nama'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $nama);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $code   = $vals['code'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $code);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $trade_name   = $vals['trade_name'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $trade_name);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $id_unit_packing   = (!empty($GET_UNIT[$vals['id_unit_packing']]['code']))?$GET_UNIT[$vals['id_unit_packing']]['code']:'';
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $id_unit_packing);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $konversi   = $vals['konversi'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $konversi);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $id_unit   = (!empty($GET_UNIT[$vals['id_unit']]['code']))?$GET_UNIT[$vals['id_unit']]['code']:'';
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $id_unit);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $max_stok   = $vals['max_stok'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $max_stok);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $min_stok   = $vals['min_stok'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $min_stok);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        }
      }

      $sheet->setTitle('Product Master');
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
      header('Content-Disposition: attachment;filename="product-master.xls"');
      //unduh file
      $objWriter->save("php://output");
    }

    public function get_add_checksheet(){
      $id 	= $this->uri->segment(3);
      $no 	= 0;
      $d_Header = "";
      // $d_Header .= "<tr>";
        $d_Header .= "<tr class='headerchecksheet_".$id."'>";
          $d_Header .= "<td align='center' style='vertical-align:middle;'>".$id."</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
            $d_Header .= "<input type='text' name='Detail[".$id."][items]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Items'>";
          $d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
            $d_Header .= "<input type='text' name='Detail[".$id."][standard]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Standard'>";
          $d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
            $d_Header .= "<div class='form-group'>";
              $d_Header .= "<div class='radio'>";
                $d_Header .= "<label>";
                  $d_Header .= "<input type='radio' name='Detail[".$id."][tipe]' id='optionsRadios1' value='1' checked>";
                  $d_Header .= "Yes/No";
                $d_Header .= "</label>";
              $d_Header .= "</div>";
              $d_Header .= "<div class='radio'>";
                $d_Header .= "<label>";
                  $d_Header .= "<input type='radio' name='Detail[".$id."][tipe]' id='optionsRadios2' value='2'>";
                  $d_Header .= "Input Text";
                $d_Header .= "</label>";
              $d_Header .= "</div>";
            $d_Header .= "</div>";
          $d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
            $d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
          $d_Header .= "</td>";
        $d_Header .= "</tr>";
  
      //add part
      $d_Header .= "<tr id='addchecksheet_".$id."'>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
      $d_Header .= "</tr>";
  
       echo json_encode(array(
          'header'			=> $d_Header,
       ));
    }

    public function add2($id=null){	
      if(empty($id)){
        $this->auth->restrict($this->addPermission);
      }
      else{
        $this->auth->restrict($this->managePermission);
      }		
      if($this->input->post()){
        $post = $this->input->post();

        $id           = $post['id'];
        $code_lv4     = $post['id_product'];
        $id_mesin     = $post['id_mesin'];

        $DetailSurface    = (!empty($post['DetailSurface']))?$post['DetailSurface']:[];
        $DetailRooving    = (!empty($post['DetailRooving']))?$post['DetailRooving']:[];
        $DetailMatt       = (!empty($post['DetailMatt']))?$post['DetailMatt']:[];
        $DetailSuhuSpeed  = (!empty($post['DetailSuhuSpeed']))?$post['DetailSuhuSpeed']:[];
       
        $last_by        = (!empty($id))?'updated_by':'created_by';
        $last_date      = (!empty($id))?'updated_date':'created_date';
        $label          = (!empty($id))?'Edit':'Add';

        $dataHeader = [
          'code_lv4'          => $code_lv4,
          'id_mesin'          => $id_mesin,
          $last_by	          => $this->id_user,
          $last_date	        => $this->datetime
        ];

        $ArrDetailSurface = [];
        $ArrDetailRooving = [];
        $ArrDetailMatt = [];
        $ArrDetailSuhuSpeed = [];

        if(!empty($DetailSurface)){
          foreach($DetailSurface AS $val => $valx){
            $ArrDetailSurface[$val]['category']   = 'surface';
            $ArrDetailSurface[$val]['code_lv4']   = $code_lv4;
            $ArrDetailSurface[$val]['id_checksheet'] 	= $valx['id_checksheet'];
            $ArrDetailSurface[$val]['surface'] 	      = $valx['atas'];
            if(!empty($id)){
              $ArrDetailSurface[$val]['id'] 	      = $valx['id'];
            }
          }
        }

        if(!empty($DetailRooving)){
          foreach($DetailRooving AS $val => $valx){
            $ArrDetailRooving[$val]['category']   = 'rooving';
            $ArrDetailRooving[$val]['code_lv4']   = $code_lv4;
            $ArrDetailRooving[$val]['id_checksheet'] 	= $valx['id_checksheet'];
            $ArrDetailRooving[$val]['rooving'] 	      = $valx['pemakaian'];
            if(!empty($id)){
              $ArrDetailRooving[$val]['id'] 	      = $valx['id'];
            }
          }
        }

        if(!empty($DetailMatt)){
          foreach($DetailMatt AS $val => $valx){
            $ArrDetailMatt[$val]['category']   = 'matt';
            $ArrDetailMatt[$val]['code_lv4']   = $code_lv4;
            $ArrDetailMatt[$val]['id_checksheet'] 	= $valx['id_checksheet'];
            $ArrDetailMatt[$val]['matt_atas'] 	      = $valx['atas'];
            $ArrDetailMatt[$val]['matt_bawah'] 	      = $valx['bawah'];
            $ArrDetailMatt[$val]['matt_kiri'] 	      = $valx['kiri'];
            $ArrDetailMatt[$val]['matt_kanan'] 	      = $valx['kanan'];
            if(!empty($id)){
              $ArrDetailMatt[$val]['id'] 	      = $valx['id'];
            }
          }
        }

        if(!empty($DetailSuhuSpeed)){
          foreach($DetailSuhuSpeed AS $val => $valx){
            $ArrDetailSuhuSpeed[$val]['category']   = 'suhu speed';
            $ArrDetailSuhuSpeed[$val]['code_lv4']   = $code_lv4;
            $ArrDetailSuhuSpeed[$val]['id_checksheet'] 	= $valx['id_checksheet'];
            $ArrDetailSuhuSpeed[$val]['display1'] 	      = $valx['display1'];
            $ArrDetailSuhuSpeed[$val]['display2'] 	      = $valx['display2'];
            $ArrDetailSuhuSpeed[$val]['display3'] 	      = $valx['display3'];
            $ArrDetailSuhuSpeed[$val]['dies1'] 	      = $valx['dies1'];
            $ArrDetailSuhuSpeed[$val]['dies2'] 	      = $valx['dies2'];
            $ArrDetailSuhuSpeed[$val]['dies3'] 	      = $valx['dies3'];
            $ArrDetailSuhuSpeed[$val]['speed'] 	      = $valx['speed'];
            if(!empty($id)){
              $ArrDetailSuhuSpeed[$val]['id'] 	      = $valx['id'];
            }
          }
        }

        // print_r($ArrDetailSurface);
        // print_r($ArrDetailRooving);
        // print_r($ArrDetailMatt);
        // print_r($ArrDetailSuhuSpeed);
        // exit;

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('checksheet_header',$dataHeader);

            if(!empty($ArrDetailSurface)){
              $this->db->insert_batch('checksheet_detail_new',$ArrDetailSurface);
            }
            if(!empty($ArrDetailRooving)){
              $this->db->insert_batch('checksheet_detail_new',$ArrDetailRooving);
            }
            if(!empty($ArrDetailMatt)){
              $this->db->insert_batch('checksheet_detail_new',$ArrDetailMatt);
            }
            if(!empty($ArrDetailSuhuSpeed)){
              $this->db->insert_batch('checksheet_detail_new',$ArrDetailSuhuSpeed);
            }
          }
          else{
            $this->db->where('id',$id);
            $this->db->update('checksheet_header',$dataHeader);

            if(!empty($ArrDetailSurface)){
              $this->db->update_batch('checksheet_detail_new',$ArrDetailSurface,'id');
            }
            if(!empty($ArrDetailRooving)){
              $this->db->update_batch('checksheet_detail_new',$ArrDetailRooving,'id');
            }
            if(!empty($ArrDetailMatt)){
              $this->db->update_batch('checksheet_detail_new',$ArrDetailMatt,'id');
            }
            if(!empty($ArrDetailSuhuSpeed)){
              $this->db->update_batch('checksheet_detail_new',$ArrDetailSuhuSpeed,'id');
            }
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
          history($label." checksheet: ".$code_lv4);
        }
        echo json_encode($status);
      }
      else{
        $ArrlistCT = $this->db->group_by('code_lv4')->get_where('checksheet_header',array('deleted_date'=>NULL))->result_array();
        $ArrProductCT = [];
        foreach ($ArrlistCT as $key => $value) {
          $ArrProductCT[] = $value['code_lv4'];
        }

        $header   = $this->db->get_where('checksheet_header',array('id'=>$id))->result_array();
        $code_lv4 = (!empty($header[0]['code_lv4']))?$header[0]['code_lv4']:'0';

        $detail   = $this->db->get_where('checksheet_detail',array('code_lv4'=>$code_lv4))->result_array();

        $listMachine = $this->db->select('MIN(id) AS id, nm_asset')->group_by('nm_asset')->get_where('asset',array('deleted_date'=>NULL,'category'=>'4'))->result_array();
        
        $listSurface    = $this->db->get_where('temp_checksheet',array('category'=>'surface','status'=>'1'))->result_array();
        $listMatt       = $this->db->get_where('temp_checksheet',array('category'=>'matt','status'=>'1'))->result_array();
        $listRooving    = $this->db->get_where('temp_checksheet',array('category'=>'rooving','status'=>'1'))->result_array();
        $listSuhuSpeed  = $this->db->get_where('temp_checksheet',array('category'=>'suhu speed','status'=>'1'))->result_array();

        $data = [
          'ArrProductCT' => $ArrProductCT,
          'header' => $header,
          'detail' => $detail,
          'listMachine' => $listMachine,
          'listSurface' => $listSurface,
          'listMatt' => $listMatt,
          'listRooving' => $listRooving,
          'listSuhuSpeed' => $listSuhuSpeed,
        ];

        $this->template->set($data);
        $this->template->title('Standard Check Sheet');
        $this->template->render('add2');
      }
    }

    public function print_checksheet($id){
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$printby		= $session['id_user'];

  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

      $header   = $this->db->get_where('checksheet_header',array('id'=>$id))->result_array();
      $code_lv4 = (!empty($header[0]['code_lv4']))?$header[0]['code_lv4']:'0';

      $detail   = $this->db->get_where('checksheet_detail',array('code_lv4'=>$code_lv4))->result_array();

      $listMachine = $this->db->select('MIN(id) AS id, nm_asset')->group_by('nm_asset')->get_where('asset',array('deleted_date'=>NULL,'category'=>'4'))->result_array();
      
      $listSurface    = $this->db->get_where('temp_checksheet',array('category'=>'surface'))->result_array();
      $listMatt       = $this->db->get_where('temp_checksheet',array('category'=>'matt'))->result_array();
      $listRooving    = $this->db->get_where('temp_checksheet',array('category'=>'rooving'))->result_array();
      $listSuhuSpeed  = $this->db->get_where('temp_checksheet',array('category'=>'suhu speed'))->result_array();
      $GET_VALUE 	= getValueChecksheet($code_lv4);

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
        'header' => $header,
        'detail' => $detail,
        'listMachine' => $listMachine,
        'listSurface' => $listSurface,
        'listMatt' => $listMatt,
        'listRooving' => $listRooving,
        'listSuhuSpeed' => $listSuhuSpeed,
        'GET_VALUE' => $GET_VALUE,
  		);

  		$this->load->view('print_checksheet', $data);
  	}

}
