<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Permissions
 */

class Permissions extends Admin_Controller {

    //Permission 
    protected $viewPermission   = "Permissions.View";
    protected $addPermission    = "Permissions.Add";
    protected $managePermission = "Permissions.Manage";
    protected $deletePermission = "Permissions.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Permissions/Permissions_model',
                                 'Aktifitas/aktifitas_model'
                                ));
        $this->template->title('Manage Data Permissions');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = $this->Permissions_model->where()->order_by('id_permission','ASC')->find_all();

        $this->template->set('results', $data);
        $this->template->title('Permissions');
        $this->template->render('list');
    }

    //Create New Customer
    public function create()
    {
        $this->auth->restrict($this->addPermission);
        $datmenu    	 = $this->Permissions_model->pilih_menu_permission();
		$datgroupmenu    = $this->Permissions_model->pilih_menu_group();
		$permission      = $this->Permissions_model->pilih_permission();
		$parent_id       = $this->Permissions_model->pilih_parent();

        $this->template->set('datmenu',$datmenu);
		$this->template->set('datgroupmenu',$datgroupmenu);
		$this->template->set('permission',$permission);
		$this->template->set('parent',$parent_id);
        $this->template->title('Input Master Permissions');
        $this->template->render('permissions_form');
    }

    //Edit Permissions
    public function edit()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->uri->segment(3);
        $data  = $this->Permissions_model->find_by(array('id_permission' => $id));
        if(!$data)
        {
            $this->template->set_message("Invalid ID", 'error');
            redirect('Permissions');
        }

        $datmenu    	 = $this->Permissions_model->pilih_menu_permission();
		$datgroupmenu    = $this->Permissions_model->pilih_menu_group();
		$permission      = $this->Permissions_model->pilih_permission();
		$parent_id       = $this->Permissions_model->pilih_parent();

        $this->template->set('datmenu',$datmenu);
		$this->template->set('datgroupmenu',$datgroupmenu);
		$this->template->set('permission',$permission);
		$this->template->set('parent',$parent_id);
		$this->template->set('data', $data);
        $this->template->title('Edit Master Permission');
        $this->template->render('permissions_form');

       
    }

    //Save customer ajax
    public function save_data_permissions(){
		
		//echo"<pre>";print_r($this->input->post());exit;

        $type           = $this->input->post("type");
        $id             = $this->input->post("id_permission");
		$nama           = $this->input->post("nm_permission");    
		$ket            = $this->input->post("ket");
        $namapermission = $nama.'.'.$ket;  
               
		
        if($type=="edit")
        {
            $this->auth->restrict($this->managePermission);

            if($id!="")
            {
                $data = array(
                            array(
                                'id_permission'=>$id,
                                'nm_permission'=>$namapermission,
								'nm_menu'=>$nama,
                                'ket'=>$ket,
                                )
                        );

                //Update data
                $result = $this->Permissions_model->update_batch($data,'id_permission');

                $keterangan     = "SUKSES, Edit data Permissions ".$id.", atas Nama : ".$nama;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id;
                $jumlah         = 1;
                $sql            = $this->db->last_query();

            }
            else
            {
                $result = FALSE;

                $keterangan     = "GAGAL, Edit data Permissions ".$id.", atas Nama : ".$nama;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $kdcab;
                $jumlah         = 1;
                $sql            = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        }
        else //Add New
        {
            $this->auth->restrict($this->addPermission);

            $data = $data = array(
                                'id_permission'=>$id,
                                'nm_permission'=>$namapermission,
								'nm_menu'=>$nama,
                                'ket'=>$ket,
								);

            //Add Data
            $id = $this->Permissions_model->insert($data);

            if(is_numeric($id))
            {
                $keterangan     = "SUKSES, tambah data Permissions ".$id.", atas Nama : ".$nama;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result         = TRUE;
            }
            else
            {
                $keterangan     = "GAGAL, tambah data Permissions ".$id.", atas Nama : ".$nama;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        }

        $param = array(
                'save' => $result
                );

        echo json_encode($param);
    }

    function hapus_Permissions()
    {
        $this->auth->restrict($this->deletePermission);
        $id = $this->uri->segment(3);

        if($id!=''){

            $result = $this->Permissions_model->delete($id);

            $keterangan     = "SUKSES, Delete data Permissions ".$id;
            $status         = 1;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();

        }
        else
        {
            $result = 0;
            $keterangan     = "GAGAL, Delete data Permissions ".$id;
            $status         = 0;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();

        }

        //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
                'delete' => $result,
                'idx'=>$id
                );

        echo json_encode($param);
    }

    function print_request($id){
        $id_customer = $id;
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $cust_toko      =  $this->Toko_model->tampil_toko($id_customer)->result();
        $cust_setpen    =  $this->Penagihan_model->tampil_tagih($id_customer)->result();
        $cust_setpem    =  $this->Pembayaran_model->tampil_bayar($id_customer)->result();
        $cust_pic       =  $this->Pic_model->tampil_pic($id_customer)->result();
        $cust_data      =  $this->Customer_model->find_data('customer',$id_customer,'id_customer');
        $inisial        =  $this->Customer_model->find_data('data_reff',$id_customer,'id_customer');
        

        $this->template->set('cust_data', $cust_data);
        $this->template->set('inisial', $inisial);
        $this->template->set('cust_toko', $cust_toko);
        $this->template->set('cust_setpen', $cust_setpen);
        $this->template->set('cust_setpem', $cust_setpem);
        $this->template->set('cust_pic', $cust_pic);
        $show = $this->template->load_view('print_data',$data);

        $this->mpdf->WriteHTML($show);        
        $this->mpdf->Output();
    }

    function downloadExcel()
    {
        $data = $this->Customer_model->select("customer.id_customer,
        customer.nm_customer,
        customer.bidang_usaha,
        customer.produk_jual,
        customer.kredibilitas,
        customer.alamat,
        customer.provinsi,
        customer.kota,
        customer.kode_pos,
        customer.telpon,
        customer.fax,
        customer.npwp,
        customer.alamat_npwp,
        customer.id_marketing,
        customer.referensi,
        customer.website,
        customer.foto
        ")->order_by('customer','ASC')->find_all();

        $objPHPExcel    = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(17);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(17);
       //// $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(17);
        
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
        $objPHPExcel->getActiveSheet()->getStyle("A1:P2")
                ->applyFromArray($header)
                ->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:P2');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Daftar Mitra')
            ->setCellValue('A3', 'No.')
            ->setCellValue('B3', 'No Anggota')
            ->setCellValue('C3', 'Nama Mitra')
            ->setCellValue('D3', 'Tempat Lahir')
            ->setCellValue('E3', 'Tanggal Lahir')
            ->setCellValue('F3', 'Warga Negara')
            ->setCellValue('G3', 'Jenis Kelamin')
            ->setCellValue('H3', 'Agama')
            ->setCellValue('I3', 'Alamat')
            ->setCellValue('J3', 'No HP')
            ->setCellValue('K3', 'Nama Bank')
            ->setCellValue('L3', 'No Rekening')
            ->setCellValue('M3', 'No KTP')
            ->setCellValue('N3', 'Email')
            ->setCellValue('O3', 'No Polisi')
            ->setCellValue('P3', 'Status');
            //->setCellValue('Q3', 'Hutang');
            //->setCellValue('R3', 'Hutang');
        
        $ex = $objPHPExcel->setActiveSheetIndex(0);
        $no = 1;
        $counter = 4;
        foreach ($data as $row):
            $tanggallahir = date('d-m-Y',strtotime($row->tanggallahir));            
            $ex->setCellValue('A'.$counter, $no++);
            $ex->setCellValue('B'.$counter, strtoupper($row->nama_mitra));
            $ex->setCellValue('C'.$counter, $row->nim);
            $ex->setCellValue('D'.$counter, $row->tempatlahir);
            $ex->setCellValue('E'.$counter, $tanggallahir);
            $ex->setCellValue('F'.$counter, $row->warganegara);
            $ex->setCellValue('G'.$counter, $row->jeniskelamin);
            $ex->setCellValue('H'.$counter, $row->jeniskagamaelamin);
            $ex->setCellValue('I'.$counter, $row->alamataktif);
            $ex->setCellValue('J'.$counter, $row->nohp);
            $ex->setCellValue('K'.$counter, $row->norekeningbank);
            $ex->setCellValue('L'.$counter, $row->namabank);
            $ex->setCellValue('M'.$counter, $row->noktp);
            $ex->setCellValue('N'.$counter, $row->email);
            $ex->setCellValue('O'.$counter, $row->nopolisi);
            $ex->setCellValue('P'.$counter, $row->status_aktif);
            
            $counter = $counter+1;
        endforeach;
        
        $objPHPExcel->getProperties()->setCreator("Yunaz Fandy")
            ->setLastModifiedBy("Yunaz Fandy")
            ->setTitle("Export Daftar Mitra")
            ->setSubject("Export Daftar Mitra")
            ->setDescription("Daftar Invoice for Office 2007 XLSX, generated by PHPExcel.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("PHPExcel");
        $objPHPExcel->getActiveSheet()->setTitle('Data Mitra');
        
        $objWriter  = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        header('Last-Modified:'. gmdate("D, d M Y H:i:s").'GMT');
        header('Chace-Control: no-store, no-cache, must-revalation');
        header('Chace-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ExportDataMitra'. date('Ymd') .'.xlsx"');
        
        $objWriter->save('php://output');
 
    }
}

?>
