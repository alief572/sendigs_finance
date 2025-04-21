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

class Master_supplier_backup extends Admin_Controller
{
  //Permission
  protected $viewPermission = 'Master_supplier.View';
  protected $addPermission  = 'Master_supplier.Add';
  protected $managePermission = 'Master_supplier.Manage';
  protected $deletePermission = 'Master_supplier.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array( 'upload', 'Image_lib'));
    $this->load->model(array(
      'Master_supplier/Supplier_model',
      'Aktifitas/aktifitas_model',
    ));
    $this->template->title('Manage Data Supplier');
    $this->template->page_icon('fa fa-table');

    date_default_timezone_set('Asia/Bangkok');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $this->template->title('Supplier');
    $this->template->render('index');
  }

  public function getDataJSON()
  {
    $requestData  = $_REQUEST;
    $fetch      = $this->queryDataJSON(
      $requestData['activation'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData    = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query      = $fetch['query'];

    $data  = array();
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

      $nestedData   = array();
      $detail = "";
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['id_supplier']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_supplier_office']) . "</div>";
      $nestedData[]  = "<div>" . strtoupper($row['address_office']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['name_business']) . "</div>";
      if ($this->auth->restrict($this->viewPermission)) :
        $nestedData[]  = "<div style='text-align:center'>

            <!--<a class='btn btn-sm btn-primary' href='javascript:void(0)' title='Print' onclick='unpacking('" . $row['id_barang'] . "','" . $row['qty_avl'] . "')' style='min-width:30px'>
                <span class='glyphicon glyphicon-print'></span>
              </a>-->
              <a class='btn btn-sm btn-primary detail' href='javascript:void(0)' title='Detail' data-id_supplier='" . $row['id_supplier'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-list'></span>
              </a>
              <a class='btn btn-sm btn-success edit' href='javascript:void(0)' title='Edit' data-id_supplier='" . $row['id_supplier'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='btn btn-sm btn-danger delete' href='javascript:void(0)' title='Delete' data-id_supplier = '" . $row['id_supplier'] . "'  style='width:30px; display:inline-block'>
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
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
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
  				a.*,b.name_business
  			FROM
          master_supplier a
          LEFT JOIN child_supplier_business_category b on a.id_category = b.id_business
  			WHERE 1=1
          " . $where_activation . "
  				AND (
  				a.id_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR a.nm_supplier_office LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR a.address_office LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR b.name_business LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  	        )
  		";

    // echo $sql;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_supplier',
      2 => 'nm_supplier_office',
      3 => 'address_office',
      4 => 'name_business'
    );

    $sql .= " ORDER BY a.id_supplier ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function getDataPatternName()
  {
    $requestData  = $_REQUEST;
    $fetch      = $this->queryDataPatternName(
      $requestData['activation'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData    = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query      = $fetch['query'];

    $data  = array();
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

      $nestedData   = array();
      $detail = "";
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['id_pattern']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['name_pattern']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['name_supplier']) . "</div>";
      if ($this->auth->restrict($this->viewPermission)) :
        $nestedData[]  = "<div style='text-align:center'>

              <a class='btn btn-sm btn-primary detail_PatternName' href='javascript:void(0)' title='Detail' data-id_pattern='" . $row['id_pattern'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-list'></span>
              </a>
              <a class='btn btn-sm btn-success edit_PatternName' href='javascript:void(0)' title='Edit' data-id_pattern='" . $row['id_pattern'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='btn btn-sm btn-danger delete_PatternName' href='javascript:void(0)' title='Delete' data-id_pattern = '" . $row['id_pattern'] . "'  style='width:30px; display:inline-block'>
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
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function queryDataPatternName($activation, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
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
  				child_supplier_pattern a

  			WHERE 1=1
          " . $where_activation . "
  				AND (
  				a.id_pattern LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR a.name_pattern LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  	        )
  		";

    // echo $sql;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_pattern',
      2 => 'name_pattern',
      3 => 'name_supplier'
    );

    $sql .= " ORDER BY a.id_pattern ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function getDataSupplierType()
  {
    $requestData  = $_REQUEST;
    $fetch      = $this->queryDataSupplierType(
      $requestData['activation'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData    = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query      = $fetch['query'];

    $data  = array();
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

      $nestedData   = array();
      $detail = "";
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['id_type']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['name_type']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_supplier_office']) . "</div>";
      if ($this->auth->restrict($this->viewPermission)) :
        $nestedData[]  = "<div style='text-align:center'>

              <a class='btn btn-sm btn-primary detail' href='javascript:void(0)' title='Detail' data-id_type='" . $row['id_type'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-list'></span>
              </a>
              <a class='btn btn-sm btn-success edit_SupplierType' href='javascript:void(0)' title='Edit' data-id_type='" . $row['id_type'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='detail btn btn-sm btn-danger delete' href='javascript:void(0)' title='Delete' data-id_type = '" . $row['id_type'] . "'  style='width:30px; display:inline-block'>
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
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function queryDataSupplierType($activation, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
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
  				a.*, b.nm_supplier_office
  			FROM
  				child_supplier_type a
  				LEFT JOIN master_supplier b ON a.id_type = b.id_type
  			WHERE 1=1
          " . $where_activation . "
  				AND (
  				a.id_type LIKE '%" . $this->db->escape_like_str($like_value) . "%'
          OR b.id_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR a.name_type LIKE '%" . $this->db->escape_like_str($like_value) . "%'
          OR b.nm_supplier_office LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  	        )
  		";

    // echo $sql;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_type',
      2 => 'name_type',
      3 => 'nm_supplier_office'
    );

    $sql .= " ORDER BY a.id_type ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function getDataProductCategory()
  {
    $requestData  = $_REQUEST;
    $fetch      = $this->queryDataProductCategory(
      $requestData['activation'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData    = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query      = $fetch['query'];

    $data  = array();
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

      $nestedData   = array();
      $detail = "";
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['id_category']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['name_category']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['supplier_shipping']) . "</div>";
      if ($this->auth->restrict($this->viewPermission)) :
        $nestedData[]  = "<div style='text-align:center'>

              <a class='btn btn-sm btn-primary detail' href='javascript:void(0)' title='Detail' data-id_category='" . $row['id_category'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-list'></span>
              </a>
              <a class='btn btn-sm btn-success edit_ProductCategory' href='javascript:void(0)' title='Edit' data-id_category='" . $row['id_category'] . "' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='detail btn btn-sm btn-danger delete' href='javascript:void(0)' title='Delete' data-id_category = '" . $row['id_category'] . "'  style='width:30px; display:inline-block'>
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
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function queryDataProductCategory($activation, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
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
  				master_product_category a

  			WHERE 1=1
          " . $where_activation . "
  				AND (
  				a.supplier_shipping LIKE '%" . $this->db->escape_like_str($like_value) . "%'
          OR a.id_category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  				OR a.name_category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
  	        )
  		";

    // echo $sql;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_category',
      2 => 'name_category',
      3 => 'supplier_shipping'
    );

    $sql .= " ORDER BY a.id_category ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function getID()
  {
    $nm        = $this->input->post('nm');
    $first_letter = strtoupper(substr($nm, 0, 1));
    $getI   = $this->db->query("SELECT * FROM master_supplier WHERE LEFT(id_supplier,1) = '$first_letter' ORDER BY id_supplier DESC LIMIT 1")->row();
    //echo "$first_letter";
    //exit;
    $num = substr($getI->id_supplier, 1, 3) + 1;
    $id = $first_letter . str_pad($num, 3, "0", STR_PAD_LEFT);
    $Arr_Kembali  = array(
      'id'    => $id
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
      //echo count($getTable);
      //exit;
      if (count($getTable) == 0) {
        $queryTable = "Select * FROM $table_name WHERE 1=1 AND " . $column . " IS NULL OR " . $column . " = ''";
        $getTable = $this->db->query($queryTable)->result_array();
      }
      //echo count($getTable);
      //exit;
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

  public function getVal()
  {
    $id_selected     = ($this->input->post('id_selected')) ? $this->input->post('id_selected') : '';
    $column          = ($this->input->post('column')) ? $this->input->post('column') : '';
    $column_fill     = ($this->input->post('column_fill')) ? $this->input->post('column_fill') : '';
    $idkey           = ($this->input->post('key')) ? $this->input->post('key') : '';
    $column_name     = ($this->input->post('column_name')) ? $this->input->post('column_name') : '';
    $table_name      = ($this->input->post('table_name')) ? $this->input->post('table_name') : '';
    $act             = ($this->input->post('act')) ? $this->input->post('act') : '';

    $where_col = $column . " = '" . $column_fill . "'";
    $queryTable = "Select * FROM $table_name WHERE $idkey = '$id_selected' ";
    $getTable = $this->db->query($queryTable)->result_array();
    //echo $queryTable;
    //exit;
    $html = $getTable[0][$column];

    $Arr_Kembali  = array(
      'html'    => $html
    );
    echo json_encode($Arr_Kembali);
  }

  public function modal_Process($page = "", $action = "", $id = "")
  {
    $this->template->set('action', $action);
    $this->template->set('id', $id);
    if ($page == 'Supplier') {
      $this->template->render('modal_Process_Supplier');
    } elseif ($page == 'PatternName') {
      $this->template->render('modal_Process_PatternName');
    } elseif ($page == 'SupplierType') {
      $this->template->render('modal_Process_SupplierType');
    } elseif ($page == 'ProductCategory') {
      $this->template->render('modal_Process_ProductCategory');
    }
  }

  public function modal_Helper($action = "", $id_sup = "")
  {
    $this->template->set('action', $action);
    $this->template->set('id', $id_sup);
    $this->template->render('modal_Helper');
  }

  public function saveSupplier()
  {
    $data                   = $this->input->post();
    $type                   = $data['type'];
    $id_supplier            = $data['id_supplier'];
    $supplier_shipping      = $data['supplier_shipping'];
    $nm_supplier_office     = $data['nm_supplier_office'];
    $address_office         = $data['address_office'];
    $city_office            = $data['city_office'];
    $zip_code_office        = $data['zip_code_office'];
    $telephone_office_1     = $data['telephone_office_1'][0] . "-" . $data['telephone_office_1'][1];
    $telephone_office_2     = $data['telephone_office_2'][0] . "-" . $data['telephone_office_2'][1];
    $fax_office             = $data['fax_office'];
    $owner                  = $data['owner'];
    $nm_supplier_factory    = $data['nm_supplier_factory'];
    $address_factory        = $data['address_factory'];
    $city_factory           = $data['city_factory'];
    $zip_code_factory       = $data['zip_code_factory'];
    $telephone_factory_1    = $data['telephone_factory_1'][0] . "-" . $data['telephone_factory_1'][1];
    $telephone_factory_2    = $data['telephone_factory_2'][0] . "-" . $data['telephone_factory_2'][1];
    $fax_factory            = $data['fax_factory'];
    $owner_factory          = $data['owner_factory'];
    $nm_supplier_excompany  = $data['nm_supplier_excompany'];
    $address_excompany      = $data['address_excompany'];
    $city_excompany         = $data['city_excompany'];
    $zip_code_excompany     = $data['zip_code_excompany'];
    $telephone_excompany_1  = $data['telephone_excompany_1'][0] . "-" . $data['telephone_excompany_1'][1];
    $telephone_excompany_2  = $data['telephone_excompany_2'][0] . "-" . $data['telephone_excompany_2'][1];
    $fax_excompany          = $data['fax_excompany'];
    $owner_excompany        = $data['owner_excompany'];
    $remarks                = $data['remarks'];
    $agent_name             = $data['agent_name'];
    $id_type                = $data['id_type'];
    $id_business            = $data['id_business'];
    $id_capacity            = ($data['id_capacity']) ? implode(";", $data['id_capacity']) : '';
    $activation_factory     = $data['activation_factory'];
    $activation             = $data['activation'];
    $id_category            = $data['id_category'];
    $id_brand               = ($data['id_brand']) ? implode(";", $data['id_brand']) : '';
    $pic_name_office        = $data['pic_name']['office'];
    $pic_position_office    = $data['pic_position']['office'];
    $pic_phone_office       = $data['pic_phone']['office'];
    $pic_email_office       = $data['pic_email']['office'];
    $pic_wechat_office      = $data['pic_wechat']['office'];
    $pic_wa_office          = $data['pic_wa']['office'];
    $pic_web_office         = $data['pic_web']['office'];
    //$pic_card_office        = $data['pic_card']['office'];
    $pic_name_factory       = $data['pic_name']['factory'];
    $pic_position_factory   = $data['pic_position']['factory'];
    $pic_phone_factory      = $data['pic_phone']['factory'];
    $pic_email_factory      = $data['pic_email']['factory'];
    $pic_wechat_factory     = $data['pic_wechat']['factory'];
    $pic_wa_factory         = $data['pic_wa']['factory'];
    $pic_web_factory        = $data['pic_web']['factory'];
    //$pic_card_factory       = $data['pic_card']['factory'];
    $pic_name_excompany     = $data['pic_name']['excompany'];
    $pic_position_excompany = $data['pic_position']['excompany'];
    $pic_phone_excompany    = $data['pic_phone']['excompany'];
    $pic_email_excompany    = $data['pic_email']['excompany'];
    $pic_wechat_excompany   = $data['pic_wechat']['excompany'];
    $pic_wa_excompany       = $data['pic_wa']['excompany'];
    $pic_web_excompany      = $data['pic_web']['excompany'];
    //$pic_card_excompany     = $data['pic_card']['excompany'];

    $bank_beneficiary_number      = $data['bank_beneficiary_number'];
    $bank_beneficiary_bic         = $data['bank_beneficiary_bic'];
    $bank_beneficiary_swift_code  = $data['bank_beneficiary_swift_code'];
    $bank_beneficiary_currency    = $data['bank_beneficiary_currency'];
    $bank_beneficiary_iban        = $data['bank_beneficiary_iban'];
    $intermediary_bank_number     = $data['intermediary_bank_number'];
    $intermediary_bank_iban       = $data['intermediary_bank_iban'];
    $intermediary_bank_address    = $data['intermediary_bank_address'];
    $intermediary_bank_bic        = $data['intermediary_bank_bic'];
    $intermediary_bank_swift_code = $data['intermediary_bank_swift_code'];
    /*
      $id_supplier          = $data['id_supplier'];
      $nm_supplier          = $data['nm_supplier'];
      $id_country           = $data['id_country'];
      $city                 = $data['city'];
      $address_office       = $data['address_office'];
      $address_factory      = $data['address_factory'];
      $zip_code             = $data['zip_code'];
      $telephone            = $data['telephone'][0]."-".$data['telephone'][1];
      $fax                  = $data['fax'];
      $npwp                 = $data['npwp'];
      $npwp_address         = $data['npwp_address'];
      $owner                = $data['owner'];
      $remarks              = $data['remarks'];
      $agent_name           = $data['agent_name'];
      $vat_name             = $data['vat_name'];
      $email                = $data['email'];
      $activation           = ($data['activation'] == 'aktif')?'active':'inactive';
      $supplier_shipping    = $data['supplier_shipping'];
      $id_type              = $data['id_type'];
      $id_category          = $data['id_category'];
      $id_business          = $data['id_business'];
      $id_capacity          = ($data['id_capacity'])?implode(";",$data['id_capacity']):'';
      $website              = $data['website'];
      $input_date           = $data['input_date'];
      $purchase_limit       = $data['purchase_limit'];
      $payment_remark       = $data['payment_remark'];
      $id_brand             = ($data['id_brand'])?implode(";",$data['id_brand']):'';
      $id_toq               = $data['id_toq'];
      $level                = $data['level'];
      $payment_option       = $data['payment_option'];
      $shipping_term        = $data['shipping_term'];
      $note                 = $data['note'];
      $pic                  = $data['pic'];
      $pic_phone            = $data['pic_phone'];
      $pic_email            = $data['pic_email'];
      $pic_level            = $data['pic_level'];
      $bank_name            = $data['bank_name'];
      $bank_acc_no          = $data['bank_acc_no'];
      $bank_code            = $data['bank_code'];
      $bank_acc_name        = $data['bank_acc_name'];
      //$config['upload_path']=base_url('assets/img/master_supplier');
      //$config['allowed_types']='gif|jpg|png';
      */
    $filelama             =   $this->input->post('filelama');
    $dataPICOffice        = array();
    $dataPICFactory       = array();
    $dataPICExcompany     = array();
    $dataBANK             = array();

    $path           =   './assets/img/master_supplier/'; //path folder
    $files_tmp = $_FILES;
    //OFFICE
    foreach ($_FILES['pic_card'] as $key => $value) {
      foreach ($value as $kv => $v) {
        foreach ($v as $v_num => $v_val) {
          if ($kv == 'office') {

            //$pic_card_office[$v_num][$key] = $_FILES['pic_card'][][$kv][$v_num];
          } elseif ($kv == 'factory') {
            //$pic_card_factory[$v_num][$key] = $_FILES['pic_card'][][$kv][$v_num];
          } elseif ($kv == 'excompany') {
            //$pic_card_excompany[$v_num][$key] = $_FILES['pic_card'][][$kv][$v_num];
          }
        }
      }
    }
    if (!empty($pic_name_office)) {
      for ($i = 0; $i < count($pic_name_office); $i++) {
        $_FILES['pic_card']['name'] = $files_tmp['pic_card']['name']['office'][$i];
        $_FILES['pic_card']['type'] = $files_tmp['pic_card']['type']['office'][$i];
        $_FILES['pic_card']['tmp_name'] = $files_tmp['pic_card']['tmp_name']['office'][$i];
        $_FILES['pic_card']['error'] = $files_tmp['pic_card']['error']['office'][$i];
        $_FILES['pic_card']['size'] = $files_tmp['pic_card']['size']['office'][$i];
        $config = array(
          'upload_path' => './assets/img/master_supplier/PIC_Office/',
          'allowed_types' => 'gif|jpg|png|jpeg|JPG|PNG',
          'file_name' => 'name_card_' . $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT),
          'file_ext_tolower' => TRUE,
          'overwrite' => TRUE,
          'max_size' => 2048,
          'remove_spaces' => TRUE
        );

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('pic_card')) {
          $result = $this->upload->display_errors();
        } else {

          $data_foto  = array('upload_data' => $this->upload->data('pic_card'));
          $ext = end((explode(".", $_FILES['pic_card']['name'])));
          $name_card = 'name_card_' . $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT) . "." . $ext;
        }

        $dataPICOffice[$i]['id_pic']       = $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT);
        $dataPICOffice[$i]['id_supplier']  = $id_supplier;
        $dataPICOffice[$i]['pic_name']     = $pic_name_office[$i];
        $dataPICOffice[$i]['pic_position'] = $pic_position_office[$i];
        $dataPICOffice[$i]['pic_phone']    = $pic_phone_office[$i];
        $dataPICOffice[$i]['pic_email']    = $pic_email_office[$i];
        $dataPICOffice[$i]['pic_wechat']   = $pic_wechat_office[$i];
        $dataPICOffice[$i]['pic_wa']       = $pic_wa_office[$i];
        $dataPICOffice[$i]['pic_web']      = $pic_web_office[$i];
        $dataPICOffice[$i]['pic_card']     = $name_card;
      }
    }
    if (!empty($pic_name_factory)) {
      for ($i = 0; $i < count($pic_name_factory); $i++) {
        $_FILES['pic_card']['name'] = $files_tmp['pic_card']['name']['factory'][$i];
        $_FILES['pic_card']['type'] = $files_tmp['pic_card']['type']['factory'][$i];
        $_FILES['pic_card']['tmp_name'] = $files_tmp['pic_card']['tmp_name']['factory'][$i];
        $_FILES['pic_card']['error'] = $files_tmp['pic_card']['error']['factory'][$i];
        $_FILES['pic_card']['size'] = $files_tmp['pic_card']['size']['factory'][$i];
        $config = array(
          'upload_path' => './assets/img/master_supplier/PIC_Factory/',
          'allowed_types' => 'gif|jpg|png|jpeg|JPG|PNG',
          'file_name' => 'name_card_' . $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT),
          'file_ext_tolower' => TRUE,
          'overwrite' => TRUE,
          'max_size' => 2048,
          'remove_spaces' => TRUE
        );

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('pic_card')) {
          $result = $this->upload->display_errors();
        } else {

          $data_foto  = array('upload_data' => $this->upload->data('pic_card'));
          $ext = end((explode(".", $_FILES['pic_card']['name'])));
          $name_card = 'name_card_' . $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT) . "." . $ext;
        }

        $dataPICFactory[$i]['id_pic']       = $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT);
        $dataPICFactory[$i]['id_supplier']  = $id_supplier;
        $dataPICFactory[$i]['pic_name']     = $pic_name_factory[$i];
        $dataPICFactory[$i]['pic_position'] = $pic_position_factory[$i];
        $dataPICFactory[$i]['pic_phone']    = $pic_phone_factory[$i];
        $dataPICFactory[$i]['pic_email']    = $pic_email_factory[$i];
        $dataPICFactory[$i]['pic_wechat']   = $pic_wechat_factory[$i];
        $dataPICFactory[$i]['pic_wa']       = $pic_wa_factory[$i];
        $dataPICFactory[$i]['pic_web']      = $pic_web_factory[$i];
        $dataPICFactory[$i]['pic_card']     = $name_card;
      }
    }
    /*?>
      <pre>
        <?php print_r($pic_card_office)?>
        <?php print_r($dataPICFactory)?>
        <?php echo $result; ?>

      </pre>
      <?php
      exit;*/
    if (!empty($pic_name_excompany)) {
      for ($i = 0; $i < count($pic_name_excompany); $i++) {
        $_FILES['pic_card']['name'] = $files_tmp['pic_card']['name']['excompany'][$i];
        $_FILES['pic_card']['type'] = $files_tmp['pic_card']['type']['excompany'][$i];
        $_FILES['pic_card']['tmp_name'] = $files_tmp['pic_card']['tmp_name']['excompany'][$i];
        $_FILES['pic_card']['error'] = $files_tmp['pic_card']['error']['excompany'][$i];
        $_FILES['pic_card']['size'] = $files_tmp['pic_card']['size']['excompany'][$i];
        $config = array(
          'upload_path' => './assets/img/master_supplier/PIC_Office/',
          'allowed_types' => 'gif|jpg|png|jpeg|JPG|PNG',
          'file_name' => 'name_card_' . $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT),
          'file_ext_tolower' => TRUE,
          'overwrite' => TRUE,
          'max_size' => 2048,
          'remove_spaces' => TRUE
        );

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('pic_card')['excompany']) {
          $result = $this->upload->display_errors();
        } else {

          $data_foto  = array('upload_data' => $this->upload->data('pic_card[excompany][' . $i . ']'));
          $ext = end((explode(".", $data_foto['upload_data']['file_name'])));
          $name_card = 'name_card_' . $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT) . "." . $ext;
        }

        $dataPICExcompany[$i]['id_pic']       = $id_supplier . "-P" . str_pad($i + 1, 2, "0", STR_PAD_LEFT);
        $dataPICExcompany[$i]['id_supplier']  = $id_supplier;
        $dataPICExcompany[$i]['pic_name']     = $pic_name_excompany[$i];
        $dataPICExcompany[$i]['pic_position'] = $pic_position_excompany[$i];
        $dataPICExcompany[$i]['pic_phone']    = $pic_phone_excompany[$i];
        $dataPICExcompany[$i]['pic_email']    = $pic_email_excompany[$i];
        $dataPICExcompany[$i]['pic_wechat']   = $pic_wechat_excompany[$i];
        $dataPICExcompany[$i]['pic_wa']       = $pic_wa_excompany[$i];
        $dataPICExcompany[$i]['pic_web']      = $pic_web_excompany[$i];
        $dataPICExcompany[$i]['pic_card']     = $pic_card_excompany[$i];
      }
    }



    //Bank Acc
    if (!empty($bank_beneficiary_number)) {
      for ($i = 0; $i < count($bank_beneficiary_number); $i++) {
        $dataBANK[$i]['id_bank']                      = $id_supplier . "-B" . str_pad($i + 1, 2, "0", STR_PAD_LEFT);
        $dataBANK[$i]['id_supplier']                  = $id_supplier;
        $dataBANK[$i]['bank_beneficiary_number']      = $bank_beneficiary_number[$i];
        $dataBANK[$i]['bank_beneficiary_bic']         = $bank_beneficiary_bic[$i];
        $dataBANK[$i]['bank_beneficiary_swift_code']  = $bank_beneficiary_swift_code[$i];
        $dataBANK[$i]['bank_beneficiary_currency']    = $bank_beneficiary_currency[$i];
        $dataBANK[$i]['bank_beneficiary_iban']        = $bank_beneficiary_iban[$i];
        $dataBANK[$i]['intermediary_bank_number']     = $intermediary_bank_number[$i];
        $dataBANK[$i]['intermediary_bank_iban']       = $intermediary_bank_iban[$i];
        $dataBANK[$i]['intermediary_bank_address']    = $intermediary_bank_address[$i];
        $dataBANK[$i]['intermediary_bank_bic']        = $intermediary_bank_bic[$i];
        $dataBANK[$i]['intermediary_bank_swift_code'] = $intermediary_bank_swift_code[$i];
      }
    }
    $this->db->trans_begin();
    //PIC
    if ($data['type'] == 'edit') {
      $this->db->where('id_supplier', $data['id_supplier'])->delete('child_supplier_pic_office');
      $this->db->where('id_supplier', $data['id_supplier'])->delete('child_supplier_pic_factory');
      $this->db->where('id_supplier', $data['id_supplier'])->delete('child_supplier_pic_excompany');
    }
    if (!empty($pic_name_office)) {
      $this->db->insert_batch('child_supplier_pic_office', $dataPICOffice);
    }
    if (!empty($pic_name_factory)) {
      $this->db->insert_batch('child_supplier_pic_factory', $dataPICFactory);
    }
    if (!empty($pic_name_excompany)) {
      $this->db->insert_batch('child_supplier_pic_excompany', $dataPICExcompany);
    }
    //BANK
    if ($data['type'] == 'edit') {
      $this->db->where('id_supplier', $data['id_supplier'])->delete('child_supplier_bank');
    }
    if (!empty($bank_beneficiary_number)) {
      $this->db->insert_batch('child_supplier_bank', $dataBANK);
    }
    //SUPPLIER DATA
    if ($data['type'] == 'edit') {

      $insertData  = array(
        'supplier_shipping'         =>  $supplier_shipping,
        'nm_supplier_office'        =>  $nm_supplier_office,
        'address_office'            =>  $address_office,
        'city_office'               =>  $city_office,
        'zip_code_office'           =>  $zip_code_office,
        'telephone_office_1'        =>  $telephone_office_1,
        'telephone_office_2'        =>  $telephone_office_2,
        'fax_office'                =>  $fax_office,
        'owner'                     =>  $owner,
        'nm_supplier_factory'       =>  $nm_supplier_factory,
        'address_factory'           =>  $address_factory,
        'city_factory'              =>  $city_factory,
        'zip_code_factory'          =>  $zip_code_factory,
        'telephone_factory_1'       =>  $telephone_factory_1,
        'telephone_factory_2'       =>  $telephone_factory_2,
        'fax_factory'               =>  $fax_factory,
        'owner_factory'             =>  $owner_factory,
        'nm_supplier_excompany'     =>  $nm_supplier_excompany,
        'address_excompany'         =>  $address_excompany,
        'city_excompany'            =>  $city_excompany,
        'zip_code_excompany'        =>  $zip_code_excompany,
        'telephone_excompany_1'     =>  $telephone_excompany_1,
        'telephone_excompany_2'     =>  $telephone_excompany_2,
        'fax_excompany'             =>  $fax_excompany,
        'owner_excompany'           =>  $owner_excompany,
        'remarks'                   =>  $remarks,
        'agent_name'                =>  $agent_name,
        'id_type'                   =>  $id_type,
        'id_business'               =>  $id_business,
        'id_capacity'               =>  $id_capacity,
        'activation_factory'        =>  $activation_factory,
        'activation'                =>  $activation,
        'id_category'               =>  $id_category,
        'id_brand'                  =>  $id_brand,
        'modified_on'                =>  date('Y-m-d H:i:s'),
        'modified_by'                =>  $this->auth->user_id()
      );
      $this->db->where('id_supplier', $data['id_supplier'])->update('master_supplier', $insertData);
    } else {
      $numID = $this->db->get_where('master_supplier', array('id_supplier' => $id_supplier))->num_rows();
      if ($numID > 0) {
        $nm        = $nm_supplier;
        $first_letter = strtoupper(substr($nm, 0, 1));
        $getI   = $this->db->query("SELECT * FROM master_supplier WHERE LEFT(id_supplier,1) = '$first_letter' ORDER BY id_supplier DESC LIMIT 1")->row();
        //echo "$first_letter";
        //exit;
        $num = substr($getI->id_supplier, 1, 3) + 1;
        $id_supplier = $first_letter . str_pad($num, 3, "0", STR_PAD_LEFT);
      }

      $insertData  = array(
        'id_supplier'               =>  $id_supplier,
        'supplier_shipping'         =>  $supplier_shipping,
        'nm_supplier_office'        =>  $nm_supplier_office,
        'address_office'            =>  $address_office,
        'city_office'               =>  $city_office,
        'zip_code_office'           =>  $zip_code_office,
        'telephone_office_1'        =>  $telephone_office_1,
        'telephone_office_2'        =>  $telephone_office_2,
        'fax_office'                =>  $fax_office,
        'owner'                     =>  $owner,
        'nm_supplier_factory'       =>  $nm_supplier_factory,
        'address_factory'           =>  $address_factory,
        'city_factory'              =>  $city_factory,
        'zip_code_factory'          =>  $zip_code_factory,
        'telephone_factory_1'       =>  $telephone_factory_1,
        'telephone_factory_2'       =>  $telephone_factory_2,
        'fax_factory'               =>  $fax_factory,
        'owner_factory'             =>  $owner_factory,
        'nm_supplier_excompany'     =>  $nm_supplier_excompany,
        'address_excompany'         =>  $address_excompany,
        'city_excompany'            =>  $city_excompany,
        'zip_code_excompany'        =>  $zip_code_excompany,
        'telephone_excompany_1'     =>  $telephone_excompany_1,
        'telephone_excompany_2'     =>  $telephone_excompany_2,
        'fax_excompany'             =>  $fax_excompany,
        'owner_excompany'           =>  $owner_excompany,
        'remarks'                   =>  $remarks,
        'agent_name'                =>  $agent_name,
        'id_type'                   =>  $id_type,
        'id_business'               =>  $id_business,
        'id_capacity'               =>  $id_capacity,
        'activation_factory'        =>  $activation_factory,
        'activation'                =>  $activation,
        'id_category'               =>  $id_category,
        'id_brand'                  =>  $id_brand,
        'created_on'          =>  date('Y-m-d H:i:s'),
        'created_by'          =>  $this->auth->user_id()
      );
      $this->db->insert('master_supplier', $insertData);
    }
    //echo implode("<br />", $data);
    //echo implode("<br />", $dataPIC);
    //echo implode("<br />", $dataBANK);
    //echo implode("<br />", $insertData);
    //exit;
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Kembali  = array(
        'pesan'    => 'Failed Add Changes. Please try again later ...',
        'status'  => 0
      );
      $keterangan = 'FAILED, ' . $data['type'] . ' Supplier Data ' . $id_supplier . ' With Name ' . $nm_supplier;
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

      $keterangan = 'SUCCESS, ' . $data['type'] . ' Brand Data ' . $id_supplier . ' With Name ' . $nm_supplier;
      $status = 1;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    }
    simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    echo json_encode($Arr_Kembali);
  }

  public function deleteData()
  {
    $id_supplier        = $this->input->post('id_supplier');
    $this->db->trans_begin();
    $getCat   = $this->db->where('id_supplier', $id_supplier)->update('master_supplier', array('activation' => 'inactive'));
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Kembali  = array(
        'msg'    => 'Failed Add Changes. Please try again later ...',
        'status'  => 0
      );
      $keterangan = 'FAILED, Delete Customer Data ' . $id_supplier;
      $status = 0;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    } else {
      $this->db->trans_commit();
      $Arr_Kembali  = array(
        'msg'    => 'Success Delete Item. Thanks ...',
        'status'  => 1
      );

      $keterangan = 'SUCCESS, Delete Customer Data ' . $id_supplier;
      $status = 1;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    }
    simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    echo json_encode($Arr_Kembali);
  }

  public function deleteData_PatternName()
  {
    $id_pattern        = $this->input->post('id_pattern');
    $this->db->trans_begin();
    $getCat   = $this->db->where('id_pattern', $id_pattern)->update('child_supplier_pattern', array('activation' => 'inactive', 'id_pattern' => "D-" . $id_pattern));
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Kembali  = array(
        'msg'    => 'Failed Add Changes. Please try again later ...',
        'status'  => 0
      );
      $keterangan = 'FAILED, Delete Pattern Data ' . $id_pattern;
      $status = 0;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    } else {
      $this->db->trans_commit();
      $Arr_Kembali  = array(
        'msg'    => 'Success Delete Item. Thanks ...',
        'status'  => 1
      );

      $keterangan = 'SUCCESS, Delete Pattern Data ' . $id_pattern;
      $status = 1;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    }
    simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    echo json_encode($Arr_Kembali);
  }

  public function saveBrand()
  {
    $data        = $this->input->post();
    $counter = ($this->db->get('master_product_brand')->num_rows()) + 1;

    $this->db->trans_begin();
    if ($data['type'] == 'edit') {
      $id_supplier = $data['id_supplier'];
      $insertData  = array(
        'nm_supplier'  => strtoupper($data['nm_supplier']),
        'modified_on'  => date('Y-m-d H:i:s'),
        'modified_by'  => $this->auth->user_id()
      );
      $this->db->where('id_brand', $data['id_brand'])->update('master_product_brand', $insertData);
    } else {
      $id_brand = "MPB" . str_pad($counter, 3, "0", STR_PAD_LEFT);
      $insertData  = array(
        'id_brand'    => $id_brand,
        'name_brand'  => strtoupper($data['name_brand']),
        'activation'  => "active",
        'created_on'  => date('Y-m-d H:i:s'),
        'created_by'  => $this->auth->user_id()
      );
      $this->db->insert('master_product_brand', $insertData);
    }
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Kembali  = array(
        'pesan'    => 'Failed Add Changes. Please try again later ...',
        'status'  => 0
      );
      $keterangan = 'FAILED, ' . $data['type'] . ' Brand Data ' . $id_brand;
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

      $keterangan = 'SUCCESS, ' . $data['type'] . ' Brand Data ' . $id_brand;
      $status = 1;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    }
    simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    echo json_encode($Arr_Kembali);
  }

  public function saveSupplierType()
  {
    $data        = $this->input->post();
    $counter = ((int) substr($this->db->query("select * From child_supplier_type ORDER BY id_type DESC LIMIT 1")->row()->id_type, -5)) + 1;

    $this->db->trans_begin();
    if ($data['type'] == 'edit') {
      $id_type = $data['id_type'];
      $insertData  = array(
        'name_type'  => strtoupper($data['name_type']),
        //'activation'	=> strtoupper($data['activation']),
        'modified_on'  => date('Y-m-d H:i:s'),
        'modified_by'  => $this->auth->user_id()
      );
      $this->db->where('id_type', $data['id_type'])->update('child_supplier_type', $insertData);
    } else {
      $id_type = "ST" . str_pad($counter, 5, "0", STR_PAD_LEFT);
      $insertData  = array(
        'id_type'    => $id_type,
        'name_type'  => strtoupper($data['name_type']),
        'activation'  => "active",
        'created_on'  => date('Y-m-d H:i:s'),
        'created_by'  => $this->auth->user_id()
      );
      $this->db->insert('child_supplier_type', $insertData);
    }
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Kembali  = array(
        'pesan'    => 'Failed Add Changes. Please try again later ...',
        'status'  => 0
      );
      $keterangan = 'FAILED, ' . $data['type'] . ' Supplier Type Data ' . $id_brand;
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

      $keterangan = 'SUCCESS, ' . $data['type'] . ' Supplier Type Data ' . $id_brand;
      $status = 1;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    }
    simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    echo json_encode($Arr_Kembali);
  }

  public function savePatternName()
  {
    $data         = $this->input->post();
    $id_supplier  = $data['id_supplier'];
    $name_pattern  = $data['name_pattern'];
    $item_name  = $data['item_name'];
    $getS = $this->db->get_where('master_supplier', array('id_supplier' => $id_supplier))->row();
    $counter = $this->db->query("select * From child_supplier_pattern WHERE id_supplier = '$id_supplier' AND id_pattern NOT LIKE '%D-%' ORDER BY id_pattern DESC LIMIT 1")->row();


    $this->db->trans_begin();
    if ($data['type'] == 'edit') {
      $id_pattern = $data['id_pattern'];
      $insertData  = array(
        'name_pattern'  => strtoupper($data['name_pattern']),
        'item_name'      => strtoupper($data['item_name']),
        'id_supplier'      => strtoupper($data['id_supplier']),
        //'name_supplier'	=> strtoupper($getS->nm_supplier),
        'modified_on'  => date('Y-m-d H:i:s'),
        'modified_by'  => $this->auth->user_id()
      );
      $this->db->where('id_pattern', $data['id_pattern'])->update('child_supplier_pattern', $insertData);
    } else {
      //$id_pattern = $id_supplier."-".str_pad(substr($counter,-3), 3, "0", STR_PAD_LEFT);
      if (empty($counter)) {
        $id_pattern = $id_supplier . "-001";
      } else {
        $id_pattern = $id_supplier . "-" . str_pad(substr($counter->id_pattern, -3), 3, "0", STR_PAD_LEFT);
      }
      $insertData  = array(
        'id_pattern'    => $id_pattern,
        'name_pattern'  => strtoupper($data['name_pattern']),
        'item_name'      => strtoupper($data['item_name']),
        'id_supplier'    => strtoupper($getS->id_supplier),
        'name_supplier'  => strtoupper($getS->nm_supplier),
        'activation'    => "active",
        'created_on'    => date('Y-m-d H:i:s'),
        'created_by'    => $this->auth->user_id()
      );
      $this->db->insert('child_supplier_pattern', $insertData);
    }
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Kembali  = array(
        'pesan'    => 'Failed Add Changes. Please try again later ...',
        'status'  => 0
      );
      $keterangan = 'FAILED, ' . $data['type'] . ' Pattern Name Data ' . $id_pattern;
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

      $keterangan = 'SUCCESS, ' . $data['type'] . ' Pattern Name Data ' . $id_patter;
      $status = 1;
      $nm_hak_akses = $this->addPermission;
      $kode_universal = $this->auth->user_id();
      $jumlah = 1;
      $sql = $this->db->last_query();
    }
    simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    echo json_encode($Arr_Kembali);
  }

  public function saveProductCategory()
  {
    $data        = $this->input->post();
    $counter = ((int) substr($this->db->query("select * From master_product_category ORDER BY id_category DESC LIMIT 1")->row()->id_category, -4)) + 1;

    $this->db->trans_begin();
    if ($data['type'] == 'edit') {
      $id_category = $data['id_category'];
      $insertData  = array(
        'name_category'  => strtoupper($data['name_category']),
        'supplier_shipping'  => strtoupper($data['supplier_shipping']),
        'modified_on'  => date('Y-m-d H:i:s'),
        'modified_by'  => $this->auth->user_id()
      );
      $this->db->where('id_category', $data['id_category'])->update('master_product_category', $insertData);
    } else {
      $id_category = "PCN" . str_pad($counter, 4, "0", STR_PAD_LEFT);
      $insertData  = array(
        'id_category'    => $id_category,
        'name_category'  => strtoupper($data['name_category']),
        'supplier_shipping'  => strtoupper($data['supplier_shipping']),
        'activation'  => "active",
        'created_on'  => date('Y-m-d H:i:s'),
        'created_by'  => $this->auth->user_id()
      );
      $this->db->insert('master_product_category', $insertData);
    }
    //$this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Kembali  = array(
        'pesan'    => 'Failed Add Changes. Please try again later ...',
        'status'  => 0
      );
      $keterangan = 'FAILED, ' . $data['type'] . ' Supplier Type Data ' . $id_brand;
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

      $keterangan = 'SUCCESS, ' . $data['type'] . ' Supplier Type Data ' . $id_brand;
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
    foreach ($rekap as $row) :
      $ex->setCellValue('A' . $counter, $no++);
      $ex->setCellValue('B' . $counter, strtoupper($row['id_supplier']));
      $ex->setCellValue('C' . $counter, $row['nm_supplier']);
      $ex->setCellValue('D' . $counter, strtoupper($row['nm_negara']));
      $ex->setCellValue('E' . $counter, $row['alamat']);
      $ex->setCellValue('F' . $counter, $row['telpon'] . ' / ' . $row['fax']);
      $ex->setCellValue('G' . $counter, $row['cp']);
      $ex->setCellValue('H' . $counter, $row['hp_cp'] . ' / ' . $row['id_webchat']);
      $ex->setCellValue('I' . $counter, $row['email']);
      $ex->setCellValue('J' . $counter, $row['sts_aktif']);

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
    header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
    header('Chace-Control: no-store, no-cache, must-revalation');
    header('Chace-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="ExportRekapSupplier' . date('Ymd') . '.xls"');

    $objWriter->save('php://output');
  }
}
