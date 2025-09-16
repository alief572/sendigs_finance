<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Customer
 */

class Customer extends Admin_Controller {

    //Permission
    protected $viewPermission   = "Customer.View";
    protected $addPermission    = "Customer.Add";
    protected $managePermission = "Customer.Manage";
    protected $deletePermission = "Customer.Delete";

    public function __construct()
    {
        parent::__construct();

        // $this->load->library(array('upload','Image_lib'));
        $this->load->library(array('upload','Image_lib'));
        $this->load->model(array('Customer/Customer_model',
                                 'Aktifitas/aktifitas_model'
                                ));

        $this->template->title('Manage Data Customer');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
        //$data = $this->Customer_model->rekap_data($session['kdcab'])->result();
        $data = $this->Customer_model->find_all_by(array('deleted' => '0'));
        $this->template->set('results', $data);
        $this->template->title('Customer');
        $this->template->render('list');

    }

	public function addCUstomer(){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$prov = $this->Customer_model->get_data('provinsi');
		$kota = $this->Customer_model->get_data('kota');
		$data = [
			'prov' => $prov,
			'kota' => $kota
		];
        $this->template->set('results', $data);
        $this->template->title('Add Customer');
        $this->template->render('form_add_customer');

	}

	// PROSES SAVE CUSTOMER
	// -------------------------------------------------------------
	public function saveCustomer(){
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Customer_model->generate_id();
		$this->db->trans_begin();
		$data = [
			'id_customer'		=> $code,
			'nm_customer'		=> $post['nm_customer'],
			'telepon'			=> $post['tlp'],
			'fax'				=> $post['fax'],
			'email'				=> $post['email'],
			'alamat'			=> $post['address'],
			'provinsi'			=> $post['provinsi'],
			'kota'				=> $post['city'],
			'kode_pos'			=> $post['zipcode'],
			'note'				=> $post['note'],
			'bidang_usaha'		=> $post['business'],
			'produk'			=> $post['product'],
			'marketing'			=> $post['marketing'],
			'no_ktp'			=> $post['idnumber'],
			'alamat_ktp'		=> $post['nameid'],
			'website'			=> $post['website'],
			'npwp'				=> $post['npwp'],
			'alamat_npwp'		=> $post['npwpaddress'],
			'aktif'				=> $post['status'],
			'pic_name'			=> $post['pic_nm'],
			'pic_hp'			=> $post['pic_hp'],
			'pic_email'			=> $post['pic_email'],
			'pic_divisi'		=> $post['pic_divisi'],
			'pic_jabatan'		=> $post['pic_posisi'],
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id()
		];

		$insert = $this->db->insert("customer",$data);

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
	// FORM EDIT CUSTOMER
	//------------------------------------------------------------
	public function editCustomer($id){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$prov = $this->Customer_model->get_data('provinsi');
		$kota = $this->Customer_model->get_data('kota');
		$cust = $this->db->get_where('customer',array('id_customer' => $id))->result();
		$data = [
			'prov' => $prov,
			'kota' => $kota,
			'cust' => $cust
		];
        $this->template->set('results', $data);
        $this->template->title('Edit Customer');
        $this->template->render('form_edit_customer');

	}

	// PROSES EDIT CUSTOMER
	//-------------------------------------------------------------
	public function saveEditCustomer(){
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		// echo $post['id'];
		// echo "<pre>";
		// print_r($post);
		// echo "<pre>";
		// exit;
		$this->db->trans_begin();
		$data = [
			'nm_customer'		=> $post['nm_customer'],
			'telepon'			=> $post['tlp'],
			'fax'				=> $post['fax'],
			'email'				=> $post['email'],
			'alamat'			=> $post['address'],
			'provinsi'			=> $post['provinsi'],
			'kota'				=> $post['city'],
			'kode_pos'			=> $post['zipcode'],
			'note'				=> $post['note'],
			'bidang_usaha'		=> $post['business'],
			'produk'			=> $post['product'],
			'marketing'			=> $post['marketing'],
			'no_ktp'			=> $post['idnumber'],
			'alamat_ktp'		=> $post['alamtaktp'],
			'website'			=> $post['website'],
			'npwp'				=> $post['npwp'],
			'alamat_npwp'		=> $post['npwpaddress'],
			'aktif'				=> $post['status'],
			'pic_name'			=> $post['pic_nm'],
			'pic_hp'			=> $post['pic_hp'],
			'pic_email'			=> $post['pic_email'],
			'pic_divisi'		=> $post['pic_divisi'],
			'pic_jabatan'		=> $post['pic_posisi'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];

		$this->db->where('id_customer',$post['id_customer'])->update("customer",$data);

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

	// PROSES DELETE CUSTOMER
	//--------------------------------------------------------------
	public function deleteCustomer(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_customer',$id)->update("customer",$data);

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

	// VIEW Customer
	//----------------------------------------------------------------
	public function viewCustomer(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$cust 	= $this->Customer_model->getById($id);
			// echo "<pre>";
			// print_r($cust);
			// echo "<pre>";
        $this->template->set('result', $cust);
		$this->template->render('view_customer');
	}

    function print_request($id){
        $id_customer = $id;
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $cust_toko      =  $this->Toko_model->tampil_toko($id_customer)->result();
        //$cust_setpen    =  $this->Penagihan_model->tampil_tagih($id_customer)->result();
        //$cust_setpem    =  $this->Pembayaran_model->tampil_bayar($id_customer)->result();
        $cust_pic       =  $this->Pic_model->tampil_pic($id_customer)->result();
        $cust_data      =  $this->Customer_model->find_data('customer',$id_customer,'id_customer');
        $inisial        =  $this->Customer_model->find_data('data_reff',$id_customer,'id_customer');


        $this->template->set('cust_data', $cust_data);
        $this->template->set('inisial', $inisial);
        $this->template->set('cust_toko', $cust_toko);
        //$this->template->set('cust_setpen', $cust_setpen);
        //$this->template->set('cust_setpem', $cust_setpem);
        $this->template->set('cust_pic', $cust_pic);
        $show = $this->template->load_view('print_data',$data);

        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    function rekap_pdf(){
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();
        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];
        $data_cus = $this->Customer_model->rekap_data($kdcab)->result_array();
        $this->template->set('data_cus', $data_cus);

        $show = $this->template->load_view('print_rekap',$data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    function downloadExcel()
    {
        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];
        //$data_cus = $this->Customer_model->rekap_data($kdcab)->result_array();
        $data_cus = $this->db->get_where('customer',array('kdcab'=>$session['kdcab'],'deleted != 0'))->result_array();
        //print_r($data_cus);die();

        $objPHPExcel    = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);

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
                'name' => 'Verdana'
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle("A1:I2")
                ->applyFromArray($header)
                ->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:I2');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'REKAP DATA CUSTOMER')
            ->setCellValue('A3', 'No.')
            ->setCellValue('B3', 'ID CUSTOMER')
            ->setCellValue('C3', 'NAMA CUSTOMER')
            ->setCellValue('D3', 'BIDANG USAHA')
            ->setCellValue('E3', 'MARKETING')
            ->setCellValue('F3', 'KREDIBILITAS')
            ->setCellValue('G3', 'PRODUK')
            ->setCellValue('H3', 'ALAMAT')
            ->setCellValue('I3', 'KREDIT LIMIT');

        $ex = $objPHPExcel->setActiveSheetIndex(0);
        $no = 1;
        $counter = 4;
        foreach ($data_cus as $row):
            $ex->setCellValue('A'.$counter, $no++);
            $ex->setCellValue('B'.$counter, $row['id_customer']);
            $ex->setCellValue('C'.$counter, $row['nm_customer']);
            $ex->setCellValue('D'.$counter, $row['bidang_usaha']);
            $ex->setCellValue('E'.$counter, $row['nama_karyawan']);
            $ex->setCellValue('F'.$counter, $row['kredibilitas']);
            $ex->setCellValue('G'.$counter, $row['produk_jual']);
            $ex->setCellValue('H'.$counter, $row['alamat']);
            $ex->setCellValue('I'.$counter, $row['limit_piutang']);

        $counter = $counter+1;
        endforeach;

        $objPHPExcel->getProperties()->setCreator("Yunaz Fandy")
            ->setLastModifiedBy("Yunaz Fandy")
            ->setTitle("Export Rekap Data Customer")
            ->setSubject("Export Rekap Data Customer")
            ->setDescription("Rekap Data Customer for Office 2007 XLSX, generated by PHPExcel.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("PHPExcel");
        $objPHPExcel->getActiveSheet()->setTitle('Rekap Data Customer');
        ob_clean();
        $objWriter  = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header('Last-Modified:'. gmdate("D, d M Y H:i:s").'GMT');
        header('Chace-Control: no-store, no-cache, must-revalation');
        header('Chace-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ExportCustomer'. date('Ymd') .'.xls"');

        $objWriter->save('php://output');

    }

    function downloadExcel_2()
      {
        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];

        $data_cus = $this->db->get_where('customer',array('kdcab'=>$session['kdcab'],'deleted != 0'))->result();

        $data = array(
    			'title2'		     => 'Report',
  			'results'			=> $data_cus,
    		);
        /*$this->template->set('results', $data_so);
        $this->template->set('head', $sts);
        $this->template->title('Report SO');*/
        $this->load->view('view_report_2',$data);


      }
}

?>
