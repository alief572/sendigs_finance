<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Customer
 */

class Master_customer extends Admin_Controller
{

    //Permission
    protected $viewPermission   = "Master_customer.View";
    protected $addPermission    = "Master_customer.Add";
    protected $managePermission = "Master_customer.Manage";
    protected $deletePermission = "Master_customer.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array(
            'Master_customer/Customer_model',
            // 'Customer/Toko_model',
            // 'Customer/Penagihan_model',
            // 'Customer/Pembayaran_model',
            // 'Customer/Pic_model',
            // 'Customer/Syarat_tagih_model',
            // 'Aktifitas/aktifitas_model',
            // 'Bidus/Bidus_model',
            'Aktifitas/aktifitas_model',
        ));
        $this->template->title('Manage Data Customer');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Customer');
        $this->template->render('index');
    }

    public function getDataJSON()
    {
        $requestData    = $_REQUEST;
        $fetch            = $this->queryDataJSON(
            $requestData['activation'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData        = $fetch['totalData'];
        $totalFiltered    = $fetch['totalFiltered'];
        $query            = $fetch['query'];

        $data    = array();
        $urut1  = 1;
        $urut2  = 0;
        foreach ($query->result_array() as $row) {
            $total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            if ($row['aktif'] === 'active') {
                $badge = 'bg-blue';
            } else {
                $badge = 'bg-danger';
            }

            $nestedData     = array();
            $detail = "";
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['id_customer']) . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['nm_customer']) . "</div>";
            $nestedData[]    = "<div>" . strtoupper($row['alamat']) . "</div>";
            $nestedData[]    = "<div>" . strtoupper($row['telepon']) . "</div>";
            $nestedData[]    = "<div>" . strtoupper($row['name_category_customer']) . "</div>";
            $nestedData[]    = "<div><span class='badge " . $badge . "'>" . strtoupper($row['aktif']) . "</span></div>";
            if ($this->auth->restrict($this->viewPermission)) :
                $nestedData[]    = "<div style='text-align:center'>

              <a class='btn btn-sm btn-primary detail' href='javascript:void(0)' title='Detail' data-id_customer='" . $row['id_customer'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-list'></span>
              </a>
              <a class='btn btn-sm btn-success edit' href='javascript:void(0)' title='Edit' data-id_customer='" . $row['id_customer'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='btn btn-sm btn-danger delete' href='javascript:void(0)' title='Delete' data-id_customer = '" . $row['id_customer'] . "'  style='width:30px; display:inline-block'>
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
            "draw"                => intval($requestData['draw']),
            "recordsTotal"        => intval($totalData),
            "recordsFiltered"     => intval($totalFiltered),
            "data"                => $data
        );

        echo json_encode($json_data);
    }

    public function queryDataJSON($activation, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        // echo $series."<br>";
        // echo $group."<br>";
        // echo $komponen."<br>";

        $where_activation = "";
        if (!empty($activation)) {
            $where_activation = " AND a.activation = '" . $activation . "' ";
        }

        $sql = "
  			SELECT
  				*
  			FROM
                  master_customer
  			WHERE 1=1
          " . $where_activation . "
  				AND (
  				a.id_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR a.name_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.address_office LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.activation LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR b.name_category_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  	        )
  		";

        // echo $sql;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'id_customer',
            2 => 'name_customer',
            3 => 'address_office',
            4 => 'telephone',
            5 => 'name_category_customer',
            6 => 'activation'
        );

        $sql .= " ORDER BY a.id_customer ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    // DATA CUSTOMER CATEGORY

    public function getDataJSONCategory()
    {
        $requestData    = $_REQUEST;
        $fetch            = $this->queryDataJSONCategory(
            $requestData['activation'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData        = $fetch['totalData'];
        $totalFiltered    = $fetch['totalFiltered'];
        $query            = $fetch['query'];

        $data    = array();
        $urut1  = 1;
        $urut2  = 0;
        foreach ($query->result_array() as $row) {
            $total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            if ($row['activation'] === 'active') {
                $badge = 'bg-blue';
            } else {
                $badge = 'bg-danger';
            }

            $nestedData     = array();
            $detail = "";
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['id_category_customer']) . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['name_category_customer']) . "</div>";
            $nestedData[]    = "<div><span class='badge " . $badge . "'>" . strtoupper($row['activation']) . "</span></div>";
            if ($this->auth->restrict($this->viewPermission)) :
                $nestedData[]    = "<div style='text-align:center'>

              <a class='btn btn-sm btn-primary detail' href='javascript:void(0)' title='Detail' data-id_category_customer='" . $row['id_category_customer'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-list'></span>
              </a>
              <a class='btn btn-sm btn-success edit' href='javascript:void(0)' title='Edit' data-id_category_customer='" . $row['id_category_customer'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='btn btn-sm btn-danger delete' href='javascript:void(0)' title='Delete' data-id_category_customer = '" . $row['id_category_customer'] . "'  style='width:30px; display:inline-block'>
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
            "draw"                => intval($requestData['draw']),
            "recordsTotal"        => intval($totalData),
            "recordsFiltered"     => intval($totalFiltered),
            "data"                => $data
        );

        echo json_encode($json_data);
    }

    public function queryDataJSONCategory($activation, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        // echo $series."<br>";
        // echo $group."<br>";
        // echo $komponen."<br>";

        $where_activation = "";
        if (!empty($activation)) {
            $where_activation = " AND a.activation = '" . $activation . "' ";
        }

        $sql = "
  			SELECT
  				a.*
  			FROM
                  child_customer_category a
  			WHERE 1=1
          " . $where_activation . "
  				AND (
  				a.id_category_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR a.name_category_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.activation LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  	        )
  		";

        // echo $sql;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'id_category_customer',
            2 => 'name_category_customer',
            3 => 'activation'
        );

        $sql .= " ORDER BY a.id_category_customer ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function modal_Process($page = "", $action = "", $id = "")
    {
        $this->template->set('action', $action);
        $this->template->set('id', $id);
        if ($page == 'Customer') {
            $this->template->render('modal_Process_Customer');
        } elseif ($page == 'ProductCategory') {
            $this->template->render('modal_Process_Category');
        }
    }

    public function getCodeByCat()
    {
        $id         = $this->input->post('id_category_customer');
        $code       = $this->db->query("SELECT customer_code code FROM child_customer_category WHERE id_category_customer = '$id' LIMIT 1")->row();
        $codeId     = $code->code;
        $getId      = $this->db->query("SELECT MAX(SUBSTR(id_customer,3,4)) idQ FROM master_customer WHERE id_customer like '%$codeId%' ORDER BY id_customer DESC LIMIT 1")->row();

        $num = $getId->idQ + 1;
        $nomor = str_pad($num, 4, "0", STR_PAD_LEFT);
        $new_id = $codeId . $nomor;
        $Arr_Kembali  = array(
            'id'    => $new_id
        );
        echo json_encode($Arr_Kembali);
    }

    public function getOpt()
    {
        $id_selected     = ($this->input->post('id_selected')) ? $this->input->post('id_selected') : '';
        $column          = ($this->input->post('column')) ? $this->input->post('column') : '';
        $column_fill     = ($this->input->post('column_fill')) ? $this->input->post('column_fill') : '';
        $idkey           = ($this->input->post('key')) ? $this->input->post('key') : '';
        $column_name     = ($this->input->post('column_name')) ? $this->input->post('column_name') : '';
        $table_name      = ($this->input->post('table_name')) ? $this->input->post('table_name') : '';
        $act             = ($this->input->post('act')) ? $this->input->post('act') : '';

        $where_col = $column . " = '" . $column_fill . "'";
        $queryTable = "Select * FROM $table_name WHERE 1=1";
        if (!empty($column_fill)) {
            $queryTable .= " AND " . $where_col;
        }
        $getTable = $this->db->query($queryTable)->result_array();
        if ($act == 'free') {

            if (count($getTable) == 0) {
                $queryTable = "Select * FROM $table_name WHERE 1=1 AND " . $column . " IS NULL OR " . $column . " = ''";
                $getTable = $this->db->query($queryTable)->result_array();
            }
        }
        $html = '<option value="">Choose An Option</option>';
        if ($id_selected == 'multiple') {
            $html = '';
        }
        foreach ($getTable as $key => $vc) {
            $id_key = $vc[$idkey]; //${'vc'.$key};
            $name = $vc[$column_name]; //${'vc'.$column_name};
            if (!empty($id_selected)) {
                if ($id_key == $id_selected) {
                    $active = 'selected';
                } else {
                    $active = '';
                }
            }
            $html .= '<option value="' . $id_key . '" ' . $active . '>' . $name . '</option>';
        }
        $Arr_Kembali  = array(
            'html'    => $html
        );
        echo json_encode($Arr_Kembali);
    }


    //Create New Customer
    public function saveCustomer()
    {
        $data = $this->input->post();
        $ArrPic = array();
        for ($i = 0; $i < count($data['pic']); $i++) {

            $ArrPic[$i]['id_pic'] = $data['id_customer'] . '-P' . str_pad($i + 1, 2, "0", STR_PAD_LEFT);
            $ArrPic[$i]['name_pic'] = $data['pic'][$i];
            $ArrPic[$i]['id_customer'] = $data['id_customer'];
            $ArrPic[$i]['phone_pic'] = $data['pic_phone'][$i];
            $ArrPic[$i]['email_pic'] = $data['pic_email'][$i];
            $ArrPic[$i]['position_pic'] = $data['pic_position'][$i];
            $ArrPic[$i]['religion_pic'] = $data['pic_religion'][$i];
            // echo "<pre>";
            // print_r($ArrPic);
            // echo "</pre>";
        }


        // exit;
        $this->db->trans_begin();

        if ($data['type'] == 'edit') {
            $insertData  = array(
                // 'id_customer'           => $data['id_customer'],
                'name_customer'         => $data['name_customer'],
                'telephone'             => $data['telephone'][0] . "-" . $data['telephone'][1],
                'telephone_2'           => $data['telephone_2'][0] . "-" . $data['telephone_2'][1],
                'fax'                   => $data['fax'],
                'email'                 => $data['email'],
                'id_country'            => $data['id_country'],
                'id_prov'               => $data['id_prov'],
                'id_city'               => $data['city'],
                'address_office'        => $data['address_office'],
                'zip_code'              => $data['zip_code'],
                'longitude'             => $data['longitude'],
                'latitude'              => $data['latitude'],
                'id_category_customer'  => $data['id_category_customer'],
                'start_date'            => $data['start_date'],
                'receipt_time_1'        => $data['receipt_time_1'],
                'receipt_time_2'        => $data['receipt_time_2'],
                'credit_limit'          => $data['credit_limit'],
                'remarks'               => $data['remarks'],
                'npwp'                  => $data['npwp'],
                'npwp_address'          => $data['npwp_address'],
                'vat_name'              => $data['vat_name'],
                'pic_finance'           => $data['pic_finance'],
                'day_invoice_receive'   => ($data['day_invoice_receive']) ? implode(";", $data['day_invoice_receive']) : '',
                'invoice_address'       => $data['invoice_address'],
                'va_number'             => $data['va_number'],
                'payment_req'           => ($data['payment_req']) ? implode(";", $data['payment_req']) : '',
                'activation'            => $data['activation'],
                'modified_on'           => date('Y-m-d H:i:s'),
                'modified_by'           => $this->auth->user_id()
            );
            $this->db->where('id_customer', $data['id_customer'])->update('master_customer', $insertData);
        } else {
            $insertData  = array(
                'id_customer'            => $data['id_customer'],
                'name_customer'          => $data['name_customer'],
                'telephone'              => $data['telephone'][0] . "-" . $data['telephone'][1],
                'telephone_2'            => $data['telephone_2'][0] . "-" . $data['telephone_2'][1],
                'fax'                    => $data['fax'],
                'email'                  => $data['email'],
                'id_country'             => $data['id_country'],
                'id_prov'                => $data['id_prov'],
                'id_city'                => $data['city'],
                'address_office'         => $data['address_office'],
                'zip_code'               => $data['zip_code'],
                'longitude'              => $data['longitude'],
                'latitude'               => $data['latitude'],
                'id_category_customer'   => $data['id_category_customer'],
                'start_date'             => $data['start_date'],
                'receipt_time_1'         => $data['receipt_time_1'],
                'receipt_time_2'         => $data['receipt_time_2'],
                'credit_limit'           => $data['credit_limit'],
                'remarks'                => $data['remarks'],
                'npwp'                  => $data['npwp'],
                'npwp_address'          => $data['npwp_address'],
                'vat_name'              => $data['vat_name'],
                'pic_finance'           => $data['pic_finance'],
                'day_invoice_receive'   => ($data['day_invoice_receive']) ? implode(";", $data['day_invoice_receive']) : '',
                'invoice_address'       => $data['invoice_address'],
                'va_number'             => $data['va_number'],
                'payment_req'           => ($data['payment_req']) ? implode(";", $data['payment_req']) : '',
                'activation'            => $data['activation'],
                'created_on'            => date('Y-m-d H:i:s'),
                'created_by'            => $this->auth->user_id()
            );

            $this->db->insert('master_customer', $insertData);
            if (!empty($data['pic'])) {
                $this->db->insert_batch('child_customer_pic', $ArrPic);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Kembali  = array(
                'pesan'    => 'Failed Add Changes. Please try again later ...',
                'status'  => 0
            );
            $keterangan = 'FAILED, ';
            $status = 0;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $this->auth->user_id();
            $jumlah = 1;
            $sql = $this->db->last_query();
        } else {
            $this->db->trans_commit();
            $Arr_Kembali  = array(
                'pesan'    => 'Success Save Item. Thanks ...',
                'status'  => 1
            );
            $keterangan = 'SUCCESS, ';
            $status = 1;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $this->auth->user_id();
            $jumlah = 1;
            $sql = $this->db->last_query();
        }
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        echo json_encode($Arr_Kembali);
    }

    function print_request($id)
    {
        $id_customer = $id;
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $cust_toko      =  $this->Toko_model->tampil_toko($id_customer)->result();
        //$cust_setpen    =  $this->Penagihan_model->tampil_tagih($id_customer)->result();
        //$cust_setpem    =  $this->Pembayaran_model->tampil_bayar($id_customer)->result();
        $cust_pic       =  $this->Pic_model->tampil_pic($id_customer)->result();
        $cust_data      =  $this->Customer_model->find_data('customer', $id_customer, 'id_customer');
        $inisial        =  $this->Customer_model->find_data('data_reff', $id_customer, 'id_customer');


        $this->template->set('cust_data', $cust_data);
        $this->template->set('inisial', $inisial);
        $this->template->set('cust_toko', $cust_toko);
        //$this->template->set('cust_setpen', $cust_setpen);
        //$this->template->set('cust_setpem', $cust_setpem);
        $this->template->set('cust_pic', $cust_pic);
        $show = $this->template->load_view('print_data', $data);

        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    function rekap_pdf()
    {
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();
        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];
        $data_cus = $this->Customer_model->rekap_data($kdcab)->result_array();
        $this->template->set('data_cus', $data_cus);

        $show = $this->template->load_view('print_rekap', $data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    function downloadExcel()
    {
        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];
        //$data_cus = $this->Customer_model->rekap_data($kdcab)->result_array();
        $data_cus = $this->db->get_where('customer', array('kdcab' => $session['kdcab'], 'deleted != 0'))->result_array();
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
        foreach ($data_cus as $row) :
            $ex->setCellValue('A' . $counter, $no++);
            $ex->setCellValue('B' . $counter, $row['id_customer']);
            $ex->setCellValue('C' . $counter, $row['nm_customer']);
            $ex->setCellValue('D' . $counter, $row['bidang_usaha']);
            $ex->setCellValue('E' . $counter, $row['nama_karyawan']);
            $ex->setCellValue('F' . $counter, $row['kredibilitas']);
            $ex->setCellValue('G' . $counter, $row['produk_jual']);
            $ex->setCellValue('H' . $counter, $row['alamat']);
            $ex->setCellValue('I' . $counter, $row['limit_piutang']);

            $counter = $counter + 1;
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
        header('Last-Modified:' . gmdate("D, d M Y H:i:s") . 'GMT');
        header('Chace-Control: no-store, no-cache, must-revalation');
        header('Chace-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ExportCustomer' . date('Ymd') . '.xls"');

        $objWriter->save('php://output');
    }

    function downloadExcel_2()
    {
        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];

        $data_cus = $this->db->get_where('customer', array('kdcab' => $session['kdcab'], 'deleted != 0'))->result();

        $data = array(
            'title2'             => 'Report',
            'results'            => $data_cus,
        );
        /*$this->template->set('results', $data_so);
        $this->template->set('head', $sts);
        $this->template->title('Report SO');*/
        $this->load->view('view_report_2', $data);
    }
}
