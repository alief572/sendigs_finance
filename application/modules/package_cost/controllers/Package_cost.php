<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Package_cost extends Admin_Controller
{
    //Permission
    protected $viewPermission = 'Package_cost.View';
    protected $addPermission  = 'Package_cost.Add';
    protected $managePermission = 'Package_cost.Manage';
    protected $deletePermission = 'Package_cost.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Master_supplier/Supplier_model',
                                 'Aktifitas/aktifitas_model',
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Package Cost');
        $this->template->render('index');
    }

    public function getDataJSON(){
    		$requestData	= $_REQUEST;
    		$fetch			= $this->queryDataJSON(
			$requestData['activation'],
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
    				$detail = "";
    			$nestedData[]	= "<div align='center'>".$nomor."</div>";
    			$nestedData[]	= "<div align='left'>".strtoupper($row['id_supplier'])."</div>";
    			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_supplier'])."</div>";
    			$nestedData[]	= "<div align='center'>".strtoupper($row['name_country'])."</div>";
    			$nestedData[]	= "<div align='center'>".strtoupper($row['product_category'])."</div>";
    			if($this->auth->restrict($this->viewPermission) ) :
            $nestedData[]	= "<div style='text-align:center'>

            <!--<a class='btn btn-sm btn-primary' href='javascript:void(0)' title='Print' onclick='unpacking('".$row['id_barang']."','".$row['qty_avl']."')' style='min-width:30px'>
                <span class='glyphicon glyphicon-print'></span>
              </a>-->
              <a class='btn btn-sm btn-primary detail' href='javascript:void(0)' title='Detail' data-id_supplier='".$row['id_supplier']."' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-list'></span>
              </a>
              <a class='btn btn-sm btn-success edit' href='javascript:void(0)' title='Edit' data-id_supplier='".$row['id_supplier']."' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='detail btn btn-sm btn-danger delete' href='javascript:void(0)' title='Delete' data-id_supplier = '".$row['id_supplier']."'  style='width:30px; display:inline-block'>
                <i class='fa fa-trash'></i>
              </a>
              </div>
      		      ";
            endif;
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

  	public function queryDataJSON($activation, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
  		// echo $series."<br>";
  		// echo $group."<br>";
  		// echo $komponen."<br>";

      $where_activation = "";
  		if(!empty($activation)){
  			$where_activation = " AND a.activation = '".$activation."' ";
  		}

  		$sql = "
  			SELECT
  				a.*, b.name_country
  			FROM
  				master_supplier a
  				LEFT JOIN master_country b ON b.id_country = a.id_country
  			WHERE 1=1
          ".$where_activation."
  				AND a.deleted ='N' AND (
  				a.id_supplier LIKE '%".$this->db->escape_like_str($like_value)."%'
  				OR a.nm_supplier LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR b.name_country LIKE '%".$this->db->escape_like_str($like_value)."%'
  	        )
  		";

  		// echo $sql;

  		$data['totalData'] = $this->db->query($sql)->num_rows();
  		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
  		$columns_order_by = array(
  			0 => 'nomor',
  			1 => 'id_supplier',
  			2 => 'nm_supplier',
  			3 => 'name_country',
  			4 => 'product_category'
  		);

  		$sql .= " ORDER BY a.id_supplier ASC, ".$columns_order_by[$column_order]." ".$column_dir." ";
  		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

  		$data['query'] = $this->db->query($sql);
  		return $data;
  	}
	
	// FORM ADD SUPPLIER
	// -------------------------------------------------
	public function add_proses_cost(){
		$this->auth->restrict($this->viewPermission);
        $this->template->title('Add Proses Cost');
		$currency = $this->db->query("SELECT * from master_currency")->result();
		$country = $this->db->query("SELECT * from master_country")->result();
		$data = array(
			'dCountry' => $country, 
			'dCurrency' => $currency 
		);
        $this->template->set('result',$data);
        $this->template->render('form_add_proses_cost');
	}
	
	// PROSES ADD SUPPLIER
	// ------------------------------------------------
	public function saveProses_cost(){
		$this->auth->restrict($this->viewPermission);
		$post 		= $this->input->post();
		$code 		= $this->Supplier_model->generate_id();
		$this->db->trans_begin();
		$data =[
			'id_supplier' 		=> $code,
			'nm_supplier' 		=> $post['nm_supplier'],
			'telephone' 		=> $post['tlp'],
			'fax' 				=> $post['fax'],
			'email' 			=> $post['email'],
			'address_office' 	=> $post['address'],
			'id_country' 		=> $post['country'],
			'id_currency' 		=> $post['currency'],
			'product_category' 	=> $post['product'],
			'persion' 			=> $post['persion'],
			'website' 			=> $post['webchat'],
			'npwp' 				=> $post['npwp'],
			'npwp_address' 		=> $post['npwpaddress'],
			'activation'		=> $post['status'],
			'created_by' 		=> $this->auth->user_id(),
			'created_on' 		=> date('Y-m-d H:i:s'),
			'note' 				=> $post['note']
		];
		
		$insert = $this->db->insert("master_supplier",$data);
		
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);			
		}
		
  		echo json_encode($status);
	}
	
	// FORM EDIT SUPPLIER
	// ---------------------------------------------
	public function edit($id){
		$this->auth->restrict($this->viewPermission);
		$this->template->page_icon('fa fa-edit');
        $this->template->title('Edit Supplier');
		$supllier = $this->Supplier_model->getById($id);
		$currency = $this->db->query("SELECT * from master_currency")->result();
		$country = $this->db->query("SELECT * from master_country")->result();
		$data = array(
			'dCountry' 	=> $country, 
			'dCurrency' => $currency ,
			'supplier'	=> $supllier
		);
        $this->template->set('result',$data);
        $this->template->render('form_edit_supplier');
		
	} // END FORM EDIT SUPPLIER
	
	
	
	// PROSES EDIT SUPPLIER
	// ---------------------------------------------
	public function saveEditSupplier(){
		$this->auth->restrict($this->viewPermission);
		$post 		= $this->input->post();
		$this->db->trans_begin();
		$data =[
			// 'id_supplier' 		=> $post['id_supplier'],
			'nm_supplier' 		=> $post['nm_supplier'],
			'telephone' 		=> $post['tlp'],
			'fax' 				=> $post['fax'],
			'email' 			=> $post['email'],
			'address_office' 	=> $post['address'],
			'id_country' 		=> $post['country'],
			'id_currency' 		=> $post['currency'],
			'product_category' 	=> $post['product'],
			'persion' 			=> $post['persion'],
			'website' 			=> $post['webchat'],
			'npwp' 				=> $post['npwp'],
			'npwp_address' 		=> $post['npwpaddress'],
			'activation'		=> $post['status'],
			'modified_by' 		=> $this->auth->user_id(),
			'modified_on' 		=> date('Y-m-d H:i:s'),
			'note' 				=> $post['note']
		];
		
		$this->db->where('id_supplier',$post['id_supplier'])->update("master_supplier",$data);
		
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);			
		}
		
  		echo json_encode($status);
		
	} // END PROSES EDIT SUPPLIER
	
	
	
	// PROSES DELETE SUPPLIER
	// ---------------------------------------------
	public function delete($id){
		$this->auth->restrict($this->viewPermission);
		$this->db->trans_begin();
		// $this->db->where('id_supplier',$id)->delete("master_supplier");
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];
		
		$this->db->where('id_supplier',$id)->update("master_supplier",$data);
		
		
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);			
		}
		
  		echo json_encode($status);
		
		
		
		
	} // END PROSES DELETE SUPPLIER
	
	
	// VIEW DETAIL SUPPLIER
	// ---------------------------------------------------------------
	public function viewSupplier($id){
		
		$this->auth->restrict($this->viewPermission);
		$data = $this->Supplier_model->getByID($id);
		$this->template->set('result',$data);
        $this->template->render('detail-supplier');
	}
	
	
	
    public function saveProductCategory(){
  		$data				= $this->input->post();
      $counter = ((int) substr($this->db->query("select * From master_product_category ORDER BY id_category DESC LIMIT 1")->row()->id_category,-4))+1;

      $this->db->trans_begin();
      if ($data['type'] == 'edit') {
        $id_category = $data['id_category'];
        $insertData	= array(
          'name_category'	=> strtoupper($data['name_category']),
          'supplier_shipping'	=> strtoupper($data['supplier_shipping']),
          'modified_on'	=> date('Y-m-d H:i:s'),
          'modified_by'	=> $this->auth->user_id()
        );
        $this->db->where('id_category',$data['id_category'])->update('master_product_category',$insertData);
      }else {
        $id_category = "PCN".str_pad($counter, 4, "0", STR_PAD_LEFT);
        $insertData	= array(
          'id_category'    => $id_category,
          'name_category'	=> strtoupper($data['name_category']),
          'supplier_shipping'	=> strtoupper($data['supplier_shipping']),
          'activation'  => "active",
          'created_on'	=> date('Y-m-d H:i:s'),
          'created_by'	=> $this->auth->user_id()
        );
        $this->db->insert('master_product_category',$insertData);
      }
      //$this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Kembali	= array(
          'pesan'		=>'Failed Add Changes. Please try again later ...',
          'status'	=> 0
        );
        $keterangan = 'FAILED, '.$data['type'].' Supplier Type Data '.$id_brand;
        $status = 0;
        $nm_hak_akses = $this->addPermission;
        $kode_universal = $this->auth->user_id();
        $jumlah = 1;
        $sql = $this->db->last_query();
      }
      else{
        $this->db->trans_commit();
        $Arr_Kembali	= array(
          'pesan'		=>'Success Save Item. Thanks ...',
          'status'	=> 1
        );

        $keterangan = 'SUCCESS, '.$data['type'].' Supplier Type Data '.$id_brand;
        $status = 1;
        $nm_hak_akses = $this->addPermission;
        $kode_universal = $this->auth->user_id();
        $jumlah = 1;
        $sql = $this->db->last_query();
      }
      simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

  		echo json_encode($Arr_Kembali);
    }

    public function print_request($id)
    {
        $id_supplier = $id;
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $sup_data = $this->Supplier_model->print_data_supplier($id_supplier);

        $this->template->set('sup_data', $sup_data);
        $show = $this->template->load_view('print_data', $data);

        $this->mpdf->AddPage('P');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    public function print_rekap()
    {
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $rekap = $this->Supplier_model->rekap_data()->result_array();

        $this->template->set('rekap', $rekap);

        $show = $this->template->load_view('print_rekap', $data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    public function downloadExcel()
    {
        $rekap = $this->Supplier_model->rekap_data()->result_array();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);

        $objPHPExcel->getActiveSheet()->getStyle(1)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle(2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle(3)->getFont()->setBold(true);

        $header = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => '000000'),
                'name' => 'Verdana',
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:J2')
                ->applyFromArray($header)
                ->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:J2');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Rekap Data Supplier')
            ->setCellValue('A3', 'No.')
            ->setCellValue('B3', 'ID Supplier')
            ->setCellValue('C3', 'Nama Supplier')
            ->setCellValue('D3', 'Negara')
            ->setCellValue('E3', 'Alamat')
            ->setCellValue('F3', 'No Telpon /  Fax')
            ->setCellValue('G3', 'Kontak Person')
            ->setCellValue('H3', 'Hp Kontak Person / WeChat ID')
            ->setCellValue('I3', 'Email')
            ->setCellValue('J3', 'Status');

        $ex = $objPHPExcel->setActiveSheetIndex(0);
        $no = 1;
        $counter = 4;
        foreach ($rekap as $row):
            $ex->setCellValue('A'.$counter, $no++);
        $ex->setCellValue('B'.$counter, strtoupper($row['id_supplier']));
        $ex->setCellValue('C'.$counter, $row['nm_supplier']);
        $ex->setCellValue('D'.$counter, strtoupper($row['nm_negara']));
        $ex->setCellValue('E'.$counter, $row['alamat']);
        $ex->setCellValue('F'.$counter, $row['telpon'].' / '.$row['fax']);
        $ex->setCellValue('G'.$counter, $row['cp']);
        $ex->setCellValue('H'.$counter, $row['hp_cp'].' / '.$row['id_webchat']);
        $ex->setCellValue('I'.$counter, $row['email']);
        $ex->setCellValue('J'.$counter, $row['sts_aktif']);

        $counter = $counter + 1;
        endforeach;

        $objPHPExcel->getProperties()->setCreator('Yunaz Fandy')
            ->setLastModifiedBy('Yunaz Fandy')
            ->setTitle('Export Rekap Data Supplier')
            ->setSubject('Export Rekap Data Supplier')
            ->setDescription('Rekap Data Supplier for Office 2007 XLSX, generated by PHPExcel.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('PHPExcel');
        $objPHPExcel->getActiveSheet()->setTitle('Rekap Data Supplier');
        ob_end_clean();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
        header('Chace-Control: no-store, no-cache, must-revalation');
        header('Chace-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ExportRekapSupplier'.date('Ymd').'.xls"');

        $objWriter->save('php://output');
    }
}
