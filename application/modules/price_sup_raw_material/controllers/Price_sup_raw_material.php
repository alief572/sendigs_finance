<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Price_sup_raw_material extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Price_Supplier_Raw_Material.View';
    protected $addPermission  	= 'Price_Supplier_Raw_Material.Add';
    protected $managePermission = 'Price_Supplier_Raw_Material.Manage';
    protected $deletePermission = 'Price_Supplier_Raw_Material.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Price_sup_raw_material/Price_sup_raw_material_model'
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
        $listData = $this->Price_sup_raw_material_model->get_data($where);

        $data = [
          'result' =>  $listData
        ];
        
        history("View index price from supplier raw materials");
        $this->template->set($data);
        $this->template->title('Price From Supplier >> Raw Materials');
        $this->template->render('index');
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
        $generate_id = $this->Price_sup_raw_material_model->generate_id();

        $id                 = $post['id'];
        $code_lv4           = $post['code_lv4'];
        $price_ref_new      = str_replace(',','',$post['price_ref_new']);
        $price_ref_new_usd      = str_replace(',','',$post['price_ref_new_usd']);
        $price_ref_high_new     = str_replace(',','',$post['price_ref_high_new']);
        $price_ref_high_new_usd = str_replace(',','',$post['price_ref_high_new_usd']);
        $kurs = str_replace(',','',$post['kurs']);
        $price_ref_expired      = $post['price_ref_expired'];
        $note                   = $post['note'];

        $dataProcess1 = [
          'price_ref_new'           => $price_ref_new,
          'price_ref_high_new'      => $price_ref_high_new,
          'price_ref_new_usd'       => $price_ref_new_usd,
          'price_ref_high_new_usd'  => $price_ref_high_new_usd,
          'price_ref_new_expired'   => $price_ref_expired,
          'kurs'   => $kurs,
          'price_ref_new_date'      => date('Y-m-d'),
          'note'  => $note,
          'status_app'  => 'Y',
          'app_by'	  => $this->id_user,
          'app_date'	=> $this->datetime
        ];

        //UPLOAD DOCUMENT
        $dataProcess2 = [];
        if(!empty($_FILES['photo']["tmp_name"])){
          $target_dir     = "assets/files/";
          $target_dir_u   = get_root3()."/assets/files/";
          $name_file      = 'evidence-'.$code_lv4."-".date('Ymdhis');
          $target_file    = $target_dir . basename($_FILES['photo']["name"]);
          $name_file_ori  = basename($_FILES['photo']["name"]);
          $imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); 
          $nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
          
          // if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){
          
            $terupload = move_uploaded_file($_FILES['photo']["tmp_name"], $nama_upload);
            $link_url    	= $target_dir.$name_file.".".$imageFileType;

            $dataProcess2	= array('upload_file' => $link_url);
          // }
        }

        $dataProcess = array_merge($dataProcess1,$dataProcess2);

        // print_r($dataProcess);
        // exit;

        $this->db->trans_start();
            $this->db->where('id',$id);
            $this->db->update('new_inventory_4',$dataProcess);
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
          history("Update price supplier raw material: ".$code_lv4);
        }
        echo json_encode($status);
      }
      else{
        $listData = $this->db->get_where('new_inventory_4',array('id' => $id))->result();

        $data = [
          'listData' => $listData,
        ];
        $this->template->set($data);
        $this->template->render('add');
      }
    }

    public function update_kurs(){
      $data 		= $this->input->post();
      $session 	= $this->session->userdata('app_session');
      $id  		  = $data['id'];
      $kurs   	= $this->db->order_by('id','desc')->limit(1)->get_where('master_kurs',array('deleted_date'=>NULL))->result();

      $ArrHeader = array(
        'id_kurs'		=> $kurs[0]->id,
        'kurs'	=> $kurs[0]->kurs
        // 'kurs_tanggal'=> $kurs[0]->tanggal,
        // 'kurs_by'	  	=> $session['id_user'],
        // 'kurs_date'	=> date('Y-m-d H:i:s')
      );

      $this->db->trans_start();
          $this->db->where('id', $id);
          $this->db->update('new_inventory_4', $ArrHeader);
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal disimpan ...',
          'status'	=> 0,
          'kurs' => number_format($kurs[0]->kurs)
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Kurs berhasil di update ...',
          'status'	=> 1,
          'kurs' => number_format($kurs[0]->kurs)
        );
        history("Update Kurs di master material");
      }

      echo json_encode($Arr_Data);
    }

    public function excel_report(){
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

      $where = [
        'deleted_date' => NULL,
        'category' => 'material'
      ];
      $listData = $this->db->get_where('new_inventory_4',$where)->result();

      $Row		= 1;
      $NewRow		= $Row + 1;
      $Col_Akhir	= $Cols	= getColsChar(11);
      $sheet->setCellValue('A' . $Row, 'Price From Supplier - Raw Materials');
      $sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
      $sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

      $NewRow	= $NewRow + 2;
      $NextRow = $NewRow + 1;

      $sheet->setCellValue('A' . $NewRow, '#');
      $sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
      $sheet->getColumnDimension('A')->setAutoSize(true);

      $sheet->setCellValue('B' . $NewRow, 'Material Code');
      $sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
      $sheet->getColumnDimension('B')->setAutoSize(true);

      $sheet->setCellValue('C' . $NewRow, 'Material Name');
      $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
      $sheet->getColumnDimension('C')->setAutoSize(true);

      $sheet->setCellValue('D' . $NewRow, 'Lower Price Before');
      $sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
      $sheet->getColumnDimension('D')->setAutoSize(true);

      $sheet->setCellValue('E' . $NewRow, 'Lower Price After');
      $sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
      $sheet->getColumnDimension('E')->setAutoSize(true);

      $sheet->setCellValue('F' . $NewRow, 'Higher Price Before');
      $sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
      $sheet->getColumnDimension('F')->setAutoSize(true);

      $sheet->setCellValue('G' . $NewRow, 'Higher Price After');
      $sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
      $sheet->getColumnDimension('G')->setAutoSize(true);

      $sheet->setCellValue('H' . $NewRow, 'Expired Before');
      $sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
      $sheet->getColumnDimension('H')->setAutoSize(true);

      $sheet->setCellValue('I' . $NewRow, 'Expired After');
      $sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
      $sheet->getColumnDimension('I')->setAutoSize(true);

      $sheet->setCellValue('J' . $NewRow, 'Status');
      $sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);
      $sheet->getColumnDimension('J')->setAutoSize(true);

      $sheet->setCellValue('K' . $NewRow, 'Alasan Reject');
      $sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($tableHeader);
      $sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);
      $sheet->getColumnDimension('K')->setAutoSize(true);

      if ($listData) {
        $awal_row	= $NextRow;
        $no = 0;
        foreach ($listData as $record) {
          $no++;
          $awal_row++;
          $awal_col	= 0;

          $tgl_create 	= $record->price_ref_new_date;
          $max_exp 		= $record->price_ref_new_expired;
          $tgl_expired 	= date('Y-m-d', strtotime('+'.$max_exp.' month', strtotime($tgl_create)));
          $date_now		= date('Y-m-d');

          $status = 'Not Set';
          $status_ = 'yellow';
          $status2 = '';

          $expired = '-';
          $expired_new = '-';
          if(!empty($record->price_ref_date)){
            $price_ref_date 	= date('Y-m-d', strtotime('+'.$record->price_ref_expired.' month', strtotime($record->price_ref_date)));
            $expired = date('d-M-Y',strtotime($price_ref_date));
            if($date_now > $price_ref_date){
              $status = 'Expired';
              $status_ = 'red';
            }
            else{
              $status = 'Oke';
              $status_ = 'green';
            }
          }
          if($record->status_app == 'Y'){
            $expired_new = date('d-M-Y',strtotime($tgl_expired));
            $status2 = 'Waiting Approve';
            $status2_ = 'purple';
          }

          $awal_col++;
          $nomor	= $no;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $nomor);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $code	= $record->code;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $code);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $nama	= $record->nama;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $nama);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $price_ref	= $record->price_ref;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $price_ref);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

          $awal_col++;
          $price_ref_new	= $record->price_ref_new;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $price_ref_new);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

          $awal_col++;
          $price_ref_high	= $record->price_ref_high;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $price_ref_high);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

          $awal_col++;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $expired);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

          $awal_col++;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $expired_new);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $status2);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $status_reject	= $record->status_reject;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols . $awal_row, $status_reject);
          $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
        }
      }

      $sheet->setTitle('Price From Supplier');
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
      header('Content-Disposition: attachment;filename="price-from-supplier-material.xls"');
      //unduh file
      $objWriter->save("php://output");
    }

}
