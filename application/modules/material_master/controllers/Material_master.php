<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Material_master extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Material_Master.View';
    protected $addPermission  	= 'Material_Master.Add';
    protected $managePermission = 'Material_Master.Manage';
    protected $deletePermission = 'Material_Master.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Material_master/Material_master_model'
        ));
        $this->template->title('Manage Material Jenis');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');

    		$this->template->page_icon('fa fa-users');
    		
        $where = [
          'deleted_date' => NULL,
          'category' => 'material'
        ];
        $listData = $this->Material_master_model->get_data($where);

        $data = [
          'result' =>  $listData,
          'get_level_1' =>  $this->db->order_by('nama','asc')->get_where('new_inventory_1',array('category'=>'material','deleted_date'=>NULL))->result_array(),
          'get_level_2' =>  $this->db->order_by('nama','asc')->get_where('new_inventory_2',array('category'=>'material','deleted_date'=>NULL))->result_array(),
          'get_level_3' =>  $this->db->order_by('nama','asc')->get_where('new_inventory_3',array('category'=>'material','deleted_date'=>NULL))->result_array(),
        ];
        
        history("View index material master");
        $this->template->set($data);
        $this->template->title('Material Master');
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

      $ENABLE_ADD     = has_permission('Product_Master.Add');
      $ENABLE_MANAGE  = has_permission('Product_Master.Manage');
      $ENABLE_VIEW    = has_permission('Product_Master.View');
      $ENABLE_DELETE  = has_permission('Product_Master.Delete');

      $get_level_1 = get_list_inventory_lv1('material');
      $get_level_2 = get_list_inventory_lv2('material');
      $get_level_3 = get_list_inventory_lv3('material');
  
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
        
        if($row['status'] == '1'){
					$Label = "<label class='label label-success'>Aktif</label>";
				}else{
					$Label = "<label class='label label-danger'>Non Aktif</label>";
				}
        $nestedData[]	= "<div align='left'>".$Label."</div>";
        
        $edit	= "";
        $delete	= "";
        if($ENABLE_MANAGE){
          $edit = "<a class='btn btn-primary btn-sm edit' href='javascript:void(0)' title='Edit' data-id='".$row['id']."'><i class='fa fa-edit'></i></a>";
        }
        if($ENABLE_DELETE){
          $delete = "&nbsp;<a class='btn btn-danger btn-sm delete' href='javascript:void(0)' title='Delete' data-id='".$row['id']."'><i class='fa fa-trash'></i></a>";
        }
  
        $nestedData[]	= "<div align='center'>".$edit.$delete."</div>";
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
                a.*
              FROM
                new_inventory_4 a,
                (SELECT @row:=0) r
              WHERE 
                a.deleted_date IS NULL 
                AND a.category='material' ".$WHERE_1.$WHERE_2.$WHERE_3."
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
        $generate_id = $this->Material_master_model->generate_id();

        $id         = $post['id'];
        $code_lv1   = $post['code_lv1'];
        $code_lv2   = $post['code_lv2'];
        $code_lv3   = $post['code_lv3'];
        $code_lv4   = (!empty($id))?$post['code_lv4']:$generate_id;
        $status     = (!empty($id))?$post['status']:1;
        $nama       = $post['nama'];

        $code             = $post['code'];
        $trade_name       = $post['trade_name'];

        $id_unit_packing  = $post['id_unit_packing'];
        $id_unit_other    = $post['id_unit_other'];
        $id_unit          = $post['id_unit'];
        $id_supplier      = $post['id_supplier'];
        $konversi         = str_replace(',','',$post['konversi']);
        $konversi_other   = str_replace(',','',$post['konversi_other']);

        $max_stok     = str_replace(',','',$post['max_stok']);
        $min_stok     = str_replace(',','',$post['min_stok']);

        $length     = str_replace(',','',$post['length']);
        $wide       = str_replace(',','',$post['wide']);
        $high       = str_replace(',','',$post['high']);
        $cub        = str_replace(',','',$post['cub']);

        $last_by    = (!empty($id))?'updated_by':'created_by';
        $last_date  = (!empty($id))?'updated_date':'created_date';
        $label      = (!empty($id))?'Edit':'Add';

        $dataProcess1 = [
          'category'  => 'material',
          'code_lv1'  => $code_lv1,
          'code_lv2'  => $code_lv2,
          'code_lv3'  => $code_lv3,
          'code_lv4'  => $code_lv4,
          'nama'		  => $nama,
          'code'  => $code,
          'trade_name'  => $trade_name,
          'id_unit_packing'  => $id_unit_packing,
          'id_unit'  => $id_unit,
          'konversi'  => $konversi,
          'id_unit_other'  => $id_unit_other,
          'konversi_other'  => $konversi_other,
          'length'		  => $length,
          'max_stok'		=> $max_stok,
          'min_stok'		=> $min_stok,
          'wide'		  => $wide,
          'high'		  => $high,
          'cub'		  => $cub,
          'status'		=> $status,
          'id_supplier'		=> $id_supplier,
          $last_by	  => $this->id_user,
          $last_date	=> $this->datetime
        ];

        //UPLOAD DOCUMENT
        $dataProcess2 = [];
        if(!empty($_FILES['photo']["tmp_name"])){
          $target_dir     = "assets/files/";
          $target_dir_u   = get_root3()."/assets/files/";
          $name_file      = 'msds-'.$code_lv4."-".date('Ymdhis');
          $target_file    = $target_dir . basename($_FILES['photo']["name"]);
          $name_file_ori  = basename($_FILES['photo']["name"]);
          $imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); 
          $nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
          
          // if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){
          
            $terupload = move_uploaded_file($_FILES['photo']["tmp_name"], $nama_upload);
            $link_url    	= $target_dir.$name_file.".".$imageFileType;

            $dataProcess2	= array('file_msds' => $link_url);
          // }
        }

        $dataProcess = array_merge($dataProcess1,$dataProcess2);

        // print_r($dataProcess);
        // exit;

        $ArrGudangNew = [
          'id_material' => $code_lv4,
          'nm_material' => $nama,
          'id_gudang' => 1,
          'kd_gudang' => 'PUS',
          'update_by' => $this->id_user,
          'update_date' => $this->datetime
        ];

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('new_inventory_4',$dataProcess);
            $this->db->insert('warehouse_stock',$ArrGudangNew);
          }
          else{
            $this->db->where('id',$id);
            $this->db->update('new_inventory_4',$dataProcess);
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
          history($label." material master: ".$code_lv4);
        }
        echo json_encode($status);
      }
      else{
        $listData = $this->db->get_where('new_inventory_4',array('id' => $id))->result();
        $code_lv1 = (!empty($listData[0]->code_lv1))?$listData[0]->code_lv1:0;
        $code_lv2 = (!empty($listData[0]->code_lv2))?$listData[0]->code_lv2:0;

        $satuan 		= $this->db->get_where('ms_satuan',array('deleted_date'=>NULL,'category'=>'unit'))->result();
		    $satuan_packing = $this->db->get_where('ms_satuan',array('deleted_date'=>NULL,'category'=>'packing'))->result();
		    $supplier = $this->db->get_where('new_supplier',array('deleted_date'=>NULL))->result();

        $data = [
          'listData' => $listData,
          'listLevel1' => get_list_inventory_lv1('material'),
          'listLevel2' => (!empty(get_list_inventory_lv2('material')[$code_lv1]))?get_list_inventory_lv2('material')[$code_lv1]:array(),
          'listLevel3' => (!empty(get_list_inventory_lv3('material')[$code_lv1][$code_lv2]))?get_list_inventory_lv3('material')[$code_lv1][$code_lv2]:array(),
          'satuan' => $satuan,
          'satuan_packing' => $satuan_packing,
          'supplier' => $supplier,
        ];
        $this->template->set($data);
        $this->template->render('add');
      }
    }

	  public function delete(){
      $this->auth->restrict($this->deletePermission);
      
      $id = $this->input->post('id');
      $data = [
        'deleted_by' 	  => $this->id_user,
        'deleted_date' 	=> $this->datetime
      ];

      $this->db->trans_begin();
      $this->db->where('id',$id)->update("new_inventory_4",$data);

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
        history("Delete material master : ".$id);
      }
      echo json_encode($status);
    }

    public function get_list_level1($id=null){
      $code_lv1 = $this->input->post('code_lv1');
      $result	= get_list_inventory_lv2('material');

      if(!empty($result[$code_lv1])){
        $option	= "<option value='0'>Select Material Category</option>";
        foreach($result[$code_lv1] AS $val => $valx){
          $sel = ($id == $valx['code_lv2'])?'selected':'';
          $option .= "<option value='".$valx['code_lv2']."' ".$sel.">".strtoupper($valx['nama'])."</option>";
        }
      }
      else{
        $option	= "<option value='0'>List Not Found</option>";
      }
      
      $ArrJson	= array(
        'option' => $option
      );
      // exit;
      echo json_encode($ArrJson);
    }

    public function get_list_level3($id=null){
      $code_lv1 = $this->input->post('code_lv1');
      $code_lv2 = $this->input->post('code_lv2');
      $result	= get_list_inventory_lv3('material');

      if(!empty($result[$code_lv1][$code_lv2])){
        $option	= "<option value='0'>Select Material Jenis</option>";
        foreach($result[$code_lv1][$code_lv2] AS $val => $valx){
          $sel = ($id == $valx['code_lv3'])?'selected':'';
          $option .= "<option value='".$valx['code_lv3']."' ".$sel.">".strtoupper($valx['nama'])."</option>";
        }
      }
      else{
        $option	= "<option value='0'>List Not Found</option>";
      }
      
      $ArrJson	= array(
        'option' => $option
      );
      // exit;
      echo json_encode($ArrJson);
    }

    public function get_list_level4_name(){
      $code_lv1 = $this->input->post('code_lv1');
      $code_lv2 = $this->input->post('code_lv2');
      $code_lv3 = $this->input->post('code_lv3');

      $get_level_1 =  get_list_inventory_lv1('material');
      $get_level_2 =  get_list_inventory_lv2('material');
      $get_level_3 =  get_list_inventory_lv3('material');

      $material_type 		= (!empty($get_level_1[$code_lv1]['nama']))?$get_level_1[$code_lv1]['nama']:'';
      $material_category = (!empty($get_level_2[$code_lv1][$code_lv2]['nama']))?$get_level_2[$code_lv1][$code_lv2]['nama']:'';
      $material_jenis 		= (!empty($get_level_3[$code_lv1][$code_lv2][$code_lv3]['nama']))?$get_level_3[$code_lv1][$code_lv2][$code_lv3]['nama']:'';
     
      
      $ArrJson	= array(
        'nama' => strtoupper($material_type." ".$material_category."; ".$material_jenis)
      );
      // exit;
      echo json_encode($ArrJson);
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
      $Col_Akhir  = $Cols = getColsChar(14);
      $sheet->setCellValue('A'.$Row, "MATERIAL MASTER");
      $sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
      $sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

      $NewRow = $NewRow +2;
      $NextRow= $NewRow;

      $sheet ->getColumnDimension("A")->setAutoSize(true);
      $sheet->setCellValue('A'.$NewRow, '#');
      $sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('A'.$NewRow.':A'.$NextRow);

      $sheet ->getColumnDimension("B")->setAutoSize(true);
      $sheet->setCellValue('B'.$NewRow, 'MATERIAL TYPE');
      $sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('B'.$NewRow.':B'.$NextRow);

      $sheet ->getColumnDimension("C")->setAutoSize(true);
      $sheet->setCellValue('C'.$NewRow, 'MATERIAL CATEGORY');
      $sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('C'.$NewRow.':C'.$NextRow);

      $sheet ->getColumnDimension("D")->setAutoSize(true);
      $sheet->setCellValue('D'.$NewRow, 'MATERIAL JENIS');
      $sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('D'.$NewRow.':D'.$NextRow);

      $sheet ->getColumnDimension("E")->setAutoSize(true);
      $sheet->setCellValue('E'.$NewRow, 'CODE PROGRAM');
      $sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('E'.$NewRow.':E'.$NextRow);

      $sheet ->getColumnDimension("F")->setAutoSize(true);
      $sheet->setCellValue('F'.$NewRow, 'MATERIAL MASTER');
      $sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('F'.$NewRow.':F'.$NextRow);

      $sheet ->getColumnDimension("G")->setAutoSize(true);
      $sheet->setCellValue('G'.$NewRow, 'MATERIAL CODE');
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

      $sheet ->getColumnDimension("N")->setAutoSize(true);
      $sheet->setCellValue('N'.$NewRow, 'UNIT OTHERS');
      $sheet->getStyle('N'.$NewRow.':N'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('N'.$NewRow.':N'.$NextRow);

      $sheet ->getColumnDimension("O")->setAutoSize(true);
      $sheet->setCellValue('O'.$NewRow, 'KONVERSI OTHER');
      $sheet->getStyle('O'.$NewRow.':O'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('O'.$NewRow.':O'.$NextRow);

      // $sheet ->getColumnDimension("P")->setAutoSize(true);
      // $sheet->setCellValue('P'.$NewRow, 'Qty');
      // $sheet->getStyle('P'.$NewRow.':P'.$NextRow)->applyFromArray($whiteCenterBold);
      // $sheet->mergeCells('P'.$NewRow.':P'.$NextRow);

      // $sheet ->getColumnDimension("Q")->setAutoSize(true);
      // $sheet->setCellValue('Q'.$NewRow, 'Qty');
      // $sheet->getStyle('Q'.$NewRow.':Q'.$NextRow)->applyFromArray($whiteCenterBold);
      // $sheet->mergeCells('Q'.$NewRow.':Q'.$NextRow);

      $dataResult   = $this->db->get_where('new_inventory_4',array('category'=>'material','deleted_date'=>NULL))->result_array();
      $GET_UNIT = get_list_satuan();
      $GET_LEVEL3 = get_inventory_lv3();
      $GET_LEVEL2 = get_inventory_lv2();
      $GET_LEVEL1 = get_list_inventory_lv1('material');
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

          $awal_col++;
          $id_unit_other   = (!empty($GET_UNIT[$vals['id_unit_other']]['code']))?$GET_UNIT[$vals['id_unit_other']]['code']:'';
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $id_unit_other);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $konversi_other   = $vals['konversi_other'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $konversi_other);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        }
      }

      $sheet->setTitle('Material Master');
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
      header('Content-Disposition: attachment;filename="material-master.xls"');
      //unduh file
      $objWriter->save("php://output");
    }

}
