<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Product
 */

class Product extends Admin_Controller
{
    /**
     * Load the models, library, etc.
     */
    //Permission
    protected $viewPermission = 'Product.View';
    protected $addPermission = 'Product.Add';
    protected $managePermission = 'Product.Manage';
    protected $deletePermission = 'Product.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('Mpdf'));
        $this->load->model(array(
            'Product/Product_model', 'Aktifitas/aktifitas_model',
        ));

        date_default_timezone_set('Asia/Bangkok');

        $this->template->title('Master Produk');
        $this->template->page_icon('fa fa-cube');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $data = $this->db->query('SELECT * FROM product where deleted = "0"')->result();
        $this->template->set('result', $data);
        $this->template->title('Product');
        $this->template->render('list');
    }

    public function getData()
    {
        $this->auth->restrict($this->viewPermission);
        $data = $this->db->query('SELECT * FROM product')->result();
        $this->template->set('result', $data);
        $this->template->render('data-product');
    }

    // FORM INSERT PRODUCT
    // -----------------------------------------------------------------------------
    public function addProduct()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Add New Product');
		$data['kategori'] = $this->db->query('SELECT * FROM kategori')->result();
        $this->template->page_icon('fa fa-pencil');
		$this->template->set($data);
        $this->template->render('form_add_product');
    }

    // PROSES INSERT PRODUCT
    // -----------------------------------------------------------------------------
    public function saveProduct()
    {
        $this->auth->restrict($this->addPermission);
        $post  = $this->input->post();

        $code = $this->Product_model->generate_id();
        $this->db->trans_begin();

        $data = [
            'id_product'    => $code,
            'product_name'  => $post['nm_product'],
            'surface'       => $post['surface'],
            'hardnes'       => $post['hardnes'],
            'thicknes'      => $post['thicknes'],
            'width'         => $post['width'],
            'form'          => $post['form'],
            'unit_coil'     => $post['uCoil'],
            'length'        => $post['length'],
            'weight'        => $post['weight'],
            'price'         => $post['price'],
            'status'        => $post['status'],
            'created_by'   => $this->auth->user_id(),
            'created_on'    => date('Y-m-d H:i:s')

        ];

        $insert = $this->db->insert("product", $data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status    = array(
                'pesan'    => 'Failed Save Item. Thanks ...',
                'status'    => 0
            );
        } else {
            $this->db->trans_commit();
            $status    = array(
                'pesan'    => 'Success Save Item. Thanks ...',
                'status'    => 1
            );
        }

        echo json_encode($status);
    }

    // FORM EDIT PRODUCT
    // ----------------------------------------------------------
    public function editProduct($id)
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Edit Product');
        $product = $this->db->get_where('product', array('id_product' => $id))->row_array();
        $hardnes = $this->db->get('hardnes')->result_array();
        $data = [
            'product' => $product,
            'hardnes' => $hardnes
        ];
        $this->template->set('result', $data);
        $this->template->page_icon('fa fa-edit');
        $this->template->render('form_edit_product');
    }


    // PROSES EDIT PRODUCT
    // ----------------------------------------------------------
    public function saveEditProduct($id)
    {
        $this->auth->restrict($this->editPermission);
        $post = $this->input->post();
        $this->db->trans_begin();
        $data = [
            'product_name'      => $post['nm_product'],
            'hardnes'           => $post['hardnes'],
            'surface'           => $post['surface'],
            'thicknes'          => $post['thicknes'],
            'width'             => $post['width'],
            'form'              => $post['form'],
            'unit_coil'         => $post['uCoil'],
            'length'            => $post['length'],
            'weight'            => $post['weight'],
            'price'             => str_replace(',', '', $post['price']),
            'status'            => $post['status'],
            'modified_by'       => $this->auth->user_id(),
            'modified_on'       => date('Y-m-d H:i:s')
        ];

        $this->db->where('id_product', $id)->update('product', $data);

        if ($this->db->trans_status() === FALSE) :
            $this->db->trans_rollback();
            $status = [
                'pesan'  => "Data tidak bisa du update..",
                'status' => 0
            ];
        else :
            $this->db->trans_commit();
            $status = [
                'pesan'  => "Data berhasil ditambahkan",
                'status' => 1
            ];
        endif;

        echo json_encode($status);
    }


    // PROSES DELETE PRODUCT
    // ---------------------------------------------------------
    public function deleteProduct($id)
    {
        $this->auth->restrict($this->deletePermission);
        $this->db->trans_begin();
        $data = [
            'deleted'    => '1',
            'deleted_by' => $this->auth->user_id(),
            'deleted_on' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id_product', $id)->update('product', $data);

        if ($this->db->trans_status() === FALSE) :
            $this->db->trans_rollback();
            $status = [
                'pesan'  => "Data tidak bisa dihapus..",
                'status' => 0
            ];
        else :
            $this->db->trans_commit();
            $status = [
                'pesan'  => "Data berhasil dihapus",
                'status' => 1
            ];
        endif;

        echo json_encode($status);
    }

    // VIEW PRODUCT
    //--------------------------------------------------------------------
    public function viewProduct()
    {
        $this->auth->restrict($this->viewPermission);
        $id = $this->input->post('id');
        $data = $this->db->get_where('product', array('id_product' => $id))->row_array();
        $this->template->set('result', $data);
        $this->template->render('view-product');
    }
	
	// GET SUB CATEGORY
	//--------------------------------------------------------------------
	
	public function getOpt(){
      $id_selected     = ($this->input->post('id_selected'))?$this->input->post('id_selected'):'';
      $column          = ($this->input->post('column'))?$this->input->post('column'):'';
      $column_fill     = ($this->input->post('column_fill'))?$this->input->post('column_fill'):'';
      $idkey           = ($this->input->post('key'))?$this->input->post('key'):'';
      $column_name     = ($this->input->post('column_name'))?$this->input->post('column_name'):'';
      $table_name      = ($this->input->post('table_name'))?$this->input->post('table_name'):'';
      $act             = ($this->input->post('act'))?$this->input->post('act'):'';

      $where_col = $column." = '".$column_fill."'";
      $queryTable = "Select * FROM $table_name WHERE 1=1";
      if (!empty($column_fill)) {
        $queryTable .= " AND ".$where_col;
      }
	  
      $getTable = $this->db->query($queryTable)->result_array();
      if ($act == 'free') {

        if (count($getTable) == 0) {
          $queryTable = "Select * FROM $table_name WHERE 1=1 AND ".$column." IS NULL OR ".$column." = ''";
          $getTable = $this->db->query($queryTable)->result_array();
        }

      }
      $html = '<option value="">Choose An Option</option>';
      if ($id_selected == 'multiple') {
        $html = '';
      }
      foreach ($getTable as $key => $vc) {
        $id_key = $vc[$idkey];//${'vc'.$key};
        $name = $vc[$column_name];//${'vc'.$column_name};
        if (!empty($id_selected)) {
          if ($id_key == $id_selected) {
            $active = 'selected';
          }else {
            $active = '';
          }
        }
        $html .= '<option value="'.$id_key.'" '.$active.'>'.$name.'</option>';
      }
      $Arr_Kembali	= array(
        'html'		=>$html
      );
      echo json_encode($Arr_Kembali);
    }






    public function print_rekap()
    {
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $brg_data = $this->Barang_model->tampil_produk()->result_array();
        $kol_data = $this->Barang_koli_model->tampil_dkoli()->result_array();
        $kom_data = $this->Barang_komponen_model->tampil_dkomponen()->result_array();
        $summary = $this->Barang_model->tampil_summary();

        $this->template->set('brg_data', $brg_data);
        $this->template->set('kol_data', $kol_data);
        $this->template->set('kom_data', $kom_data);
        $this->template->set('summary', $summary);

        $show = $this->template->load_view('print_rekap', $data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    public function print_request($id)
    {
        $id_barang = $id;
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $brg_data = $this->Barang_model->find_data('barang_master', $id_barang, 'id_barang');
        $kol_data = $this->Barang_koli_model->tampil_koli($id_barang)->result_array();
        $kom_data = $this->Barang_komponen_model->tampil_komponen($id_barang)->result_array();
        //$summary       =  $this->Barang_model->tampil_summary_barang();

        $this->template->set('brg_data', $brg_data);
        $this->template->set('kol_data', $kol_data);
        $this->template->set('kom_data', $kom_data);
        //$this->template->set('summary', $summary);
        $show = $this->template->load_view('print_data', $data);

        $this->mpdf->AddPage('P');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    public function downloadExcel()
    {
        //$brg_data = $this->Barang_model->tampil_produk()->result_array();
        $data = $this->Barang_model->select('barang_master.id_barang,
                                            barang_jenis.nm_jenis,
                                            barang_group.nm_group,
                                            barang_master.nm_barang,
                                            barang_master.satuan AS setpcs,
                                            barang_master.netto_weight,
                                            barang_master.cbm_each,
                                            barang_master.gross_weight,
                                            barang_master.spesifikasi,
                                            barang_master.sts_aktif,
                                            barang_master.qty as qty')
            ->join('barang_group', 'barang_group.id_group = barang_master.id_group', 'left')
            ->join('barang_jenis', 'barang_master.jenis = barang_jenis.id_jenis', 'left')
            ->group_by('barang_master.id_barang')
            ->where('barang_master.deleted', 0)
            ->order_by('barang_group.nm_group', 'ASC')->find_all();
        //print_r($brg_data);die();
        $kol_data = $this->Barang_koli_model->tampil_dkoli()->result_array();
        $kom_data = $this->Barang_komponen_model->tampil_dkomponen()->result_array();

        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];
        $filter = $this->input->get('filter');
        $param = $this->input->get('param');
        $where = '';
        if ($this->uri->segment(4) == "All") {
            $per = $this->uri->segment(5) . "-";
        } else {
            $per = $this->uri->segment(5) . "-" . $this->uri->segment(4);
        }




        $data = array(
            'title2'             => 'Report',
            'brg_data'           => $data
        );
        /*$this->template->set('results', $data_so);
        $this->template->set('head', $sts);
        $this->template->title('Report SO');*/
        $this->load->view('view_report', $data);
    }

    public function downloadExcel_old()
    {
        $brg_data = $this->Barang_model->tampil_produk()->result_array();
        //print_r($brg_data);die();
        $kol_data = $this->Barang_koli_model->tampil_dkoli()->result_array();
        $kom_data = $this->Barang_komponen_model->tampil_dkomponen()->result_array();

        $objPHPExcel = new PHPExcel();
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
                'name' => 'Verdana',
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:O2')
            ->applyFromArray($header)
            ->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:O2');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Rekap Data Produk')
            ->setCellValue('A3', 'No.')
            ->setCellValue('B3', 'ID Produk')
            ->setCellValue('C3', 'Jenis Produk')
            ->setCellValue('D3', 'Group Produk')
            ->setCellValue('E3', 'Nama Set')
            ->setCellValue('F3', 'Satuan')
            ->setCellValue('G3', 'ID Colly')
            ->setCellValue('H3', 'Nama Colly')
            ->setCellValue('I3', 'Qty')
            ->setCellValue('J3', 'Satuan')
            ->setCellValue('K3', 'ID Komponen')
            ->setCellValue('L3', 'Nama Komponen')
            ->setCellValue('M3', 'Qty')
            ->setCellValue('N3', 'Satuan')
            ->setCellValue('O3', '');

        $ex = $objPHPExcel->setActiveSheetIndex(0);
        $no = 1;
        $NN = 0;
        $counter = 4;
        foreach ($brg_data as $row) :
            $ex->setCellValue('A' . $counter, $no++);
            $ex->setCellValue('B' . $counter, strtoupper($row['id_barang']));
            $ex->setCellValue('C' . $counter, strtoupper($row['nm_jenis']));
            $ex->setCellValue('D' . $counter, strtoupper($row['nm_group']));
            $ex->setCellValue('E' . $counter, $row['nm_barang']);
            $ex->setCellValue('F' . $counter, $row['satuan']);
            $nco = $counter;

            foreach ($kol_data as $key => $y) {
                //$counter
                if ($row['id_barang'] == $y['id_barang']) {
                    $ex->setCellValue('G' . $counter, strtoupper($y['id_koli']));
                    $ex->setCellValue('H' . $counter, $y['nm_koli']);
                    $ex->setCellValue('I' . $counter, $y['qty']);
                    $ex->setCellValue('J' . $counter, $y['satuan']);
                    foreach ($kom_data as $key => $xy) {
                        if ($y['id_koli'] == $xy['id_koli'] && $row['id_barang'] == $y['id_barang']) {
                            $ex->setCellValue('K' . $counter, strtoupper($xy['id_komponen']));
                            $ex->setCellValue('L' . $counter, strtoupper($xy['nm_komponen']));
                            $ex->setCellValue('M' . $counter, $xy['qty']);
                            $ex->setCellValue('N' . $counter, $xy['satuan']);
                            //$ex->setCellValue('O'.$counter, $row['sts_aktif']);
                            $counter = $counter + 1;
                            $NN = 1;
                        } else {
                            $counter = $counter;
                        }
                    }
                    $counter = $counter + 1;
                } else {
                    $ex->setCellValue('G' . $counter, '');
                    $ex->setCellValue('H' . $counter, '');
                    $ex->setCellValue('I' . $counter, '');
                    $ex->setCellValue('J' . $counter, '');
                    $ex->setCellValue('K' . $counter, '');
                    $ex->setCellValue('L' . $counter, '');
                    $ex->setCellValue('M' . $counter, '');
                    $ex->setCellValue('N' . $counter, '');
                    //$ex->setCellValue('O'.$counter, $row['sts_aktif']);
                    $counter = $counter;
                }
            }

            $ex->setCellValue('O' . $nco, $row['sts_aktif']);

            $counter = $counter + 1;
        endforeach;

        $objPHPExcel->getProperties()->setCreator('Yunaz Fandy')
            ->setLastModifiedBy('Yunaz Fandy')
            ->setTitle('Export Rekap Data Produk')
            ->setSubject('Export Rekap Data Produk')
            ->setDescription('Rekap Data Produk for Office 2007 XLSX, generated by PHPExcel.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('PHPExcel');
        $objPHPExcel->getActiveSheet()->setTitle('Rekap Data Produk');
        ob_end_clean();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Chace-Control: no-store, no-cache, must-revalation');
        header('Chace-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ExportRekapProduk' . date('Ymd') . '.xls"');

        $objWriter->save('php://output');
    }
}
