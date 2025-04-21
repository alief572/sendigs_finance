<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Request_production_so extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Request_Production_SO.View';
  protected $addPermission    = 'Request_Production_SO.Add';
  protected $managePermission = 'Request_Production_SO.Manage';
  protected $deletePermission = 'Request_Production_SO.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Request_production_so/Request_production_so_model',
      'Stock_origa/Stock_origa_model',
      'Bom_hi_grid_custom/bom_hi_grid_custom_model'
    ));
    // $this->template->title('Manage Data Supplier');
    $this->template->page_icon('fa fa-building-o');

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');

    history("View index request produksi from so");
    $this->template->title('Request Produksi From SO');
    $this->template->render('index');
  }

  public function data_side_request_produksi()
  {
    $this->Request_production_so_model->data_side_request_produksi();
  }

  public function spk_stok($id = null, $uniq = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $code_lv4    = $data['code_lv4'];
      $nama_product  = $data['nama_product'];
      $no_bom        = $data['no_bom'];
      $due_date      = date('Y-m-d', strtotime($data['due_date']));
      $propose    = str_replace(',', '', $data['propose']);
      $id_customer    = $data['id_customer'];
      $project    = $data['project'];
      $so_customer  = $data['so_customer'];
      $id_uniq      = $data['id_uniq'];

      $Y = date('y');
      $SQL      = "SELECT MAX(so_number) as maxP FROM so_internal WHERE so_number LIKE 'SOI" . $Y . "%' ";
      $result    = $this->db->query($SQL)->result_array();
      $angkaUrut2    = $result[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 5, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $so_number    = "SOI" . $Y . $urut2;

      $ArrHeader = array(
        'so_number'       => $so_number,
        'so_customer'     => $so_customer,
        'id_customer'     => $id_customer,
        'project'         => $project,
        'code_lv4'        => $code_lv4,
        'no_bom'          => $no_bom,
        'nama_product'    => strtolower($nama_product),
        'due_date'        => $due_date,
        'propose'         => $propose,
        'created_by'      => $this->id_user,
        'created_date'    => $this->datetime
      );

      //BOM material
      $GET_LEVEL4 = get_inventory_lv4();
      $GET_LEVEL1 = get_list_inventory_lv1('material');
      $dataBOM = $this->db->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();
      $ArrBOMDetail = [];
      $ArrPlanningDetail = [];
      $SUM_PLANNING = 0;
      if (!empty($dataBOM)) {
        foreach ($dataBOM as $key => $value) {
          $ArrBOMDetail[$key]['so_number'] = $so_number;
          $ArrBOMDetail[$key]['code_material'] = $value['code_material'];
          $ArrBOMDetail[$key]['weight'] = $value['weight'];

          $code_lv4   = (!empty($GET_LEVEL4[$value['code_material']]['code_lv1'])) ? $GET_LEVEL4[$value['code_material']]['code_lv1'] : 0;
          $type_name  = (!empty($GET_LEVEL1[$code_lv4]['nama'])) ? $GET_LEVEL1[$code_lv4]['nama'] : 0;

          $CHECK_FTACKEL = substr($no_bom, 0, 3);
          if ($CHECK_FTACKEL == 'BFT') {
            $type_name  = $value['spk'];
          }

          $ArrBOMDetail[$key]['code_lv1'] = $code_lv4;
          $ArrBOMDetail[$key]['type_name'] = $type_name;

          //Planning
          $qty_plan = $value['weight'] * $propose;
          $ArrPlanningDetail[$key]['so_number'] = $so_number;
          $ArrPlanningDetail[$key]['id_material'] = $value['code_material'];
          $ArrPlanningDetail[$key]['qty_order'] = $qty_plan;
          $SUM_PLANNING += $qty_plan;
        }
      }

      //SO Produksi
      $ArrPlanning = array(
        'so_number' => $so_number,
        'no_pr'   => generateNoPR(),
        'tgl_so' => date('Y-m-d'),
        'id_customer' => $id_customer,
        'project' => $project,
        'qty_order' => $SUM_PLANNING,
        'created_by' => $this->id_user,
        'created_date' => $this->datetime
      );

      //   print_r($ArrBOMDetail);

      $ArrUpdateRequest = array(
        'so_number'     => $so_number,
        'no_bom'        => $no_bom,
        'status'        => 'Y'
      );
      //   exit;

      $this->db->trans_start();
      $this->db->insert('so_internal', $ArrHeader);
      $this->db->insert('material_planning_base_on_produksi', $ArrPlanning);
      if (!empty($ArrBOMDetail)) {
        $this->db->insert_batch('so_internal_material', $ArrBOMDetail);
      }
      if (!empty($ArrPlanningDetail)) {
        $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrPlanningDetail);
      }

      $this->db->where('id', $id_uniq);
      $this->db->update('so_internal_request', $ArrUpdateRequest);
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save gagal disimpan ...',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1
        );
        history("Create so internal : " . $so_number);
      }
      echo json_encode($Arr_Data);
    } else {
      $getData = $this->db->get_where('new_inventory_4', array('code_lv4' => $id))->result_array();
      $getHeader = $this->db->get_where('so_internal_request', array('id' => $uniq))->result_array();

      $WhereIN = array('grid standard', 'standard', 'ftackel');
      $getDataBOM = $this->db
        ->select('a.*,b.nama AS nm_product')
        ->where_in('a.category', $WhereIN)
        ->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
        ->get_where('bom_header a', array('a.id_product' => $id, 'a.deleted_date' => NULL))->result_array();

      $data = [
        'getData' => $getData,
        'getHeader' => $getHeader,
        'getDataBOM' => $getDataBOM,
        'getStockProduct' => get_stock_product_New(),
        'getProductLv4' => get_inventory_lv4(),
        'getNameBOMProduct' => get_name_product_by_bom_all()
      ];


      $this->template->title('SPK Stock');
      $this->template->render('spk_stok_new', $data);
    }
  }

  public function spk_stok_custom($id = null, $uniq = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');
      $GET_LEVEL4 = get_inventory_lv4();
      $GET_LEVEL1 = get_list_inventory_lv1('material');
      $id_uniq      = $data['id_uniq'];
      $id_customer  = $data['id_customer'];
      $project      = $data['project'];
      $so_customer    = $data['so_customer'];
      $no_bom_header  = $data['no_bom'];
      $single_product  = (!empty($data['single_product'])) ? $data['single_product'] : [];
      $cutting_plan    = (!empty($data['cutting_plan'])) ? $data['cutting_plan'] : [];

      $Y            = date('y');
      $SQL          = "SELECT MAX(so_number) as maxP FROM so_internal WHERE so_number LIKE 'SOI" . $Y . "%' ";
      $result        = $this->db->query($SQL)->result_array();
      $angkaUrut2    = $result[0]['maxP'];
      $urutan21      = (int)substr($angkaUrut2, 5, 4);
      $urutan22      = (int)substr($angkaUrut2, 5, 4);
      $urutan23      = (int)substr($angkaUrut2, 5, 4);

      //single product
      $ArrBOMDetail = [];
      $ArrPlanningDetail = [];
      foreach ($single_product as $keyH => $value) {
        $code_lv4      = $value['code_lv4'];
        $nama_product  = $value['nama_product'];
        $no_bom        = $value['no_bom'];
        $due_date      = (!empty($value['due_date'])) ? date('Y-m-d', strtotime($value['due_date'])) : NULL;
        $propose      = str_replace(',', '', $value['propose']);

        $urutan21++;
        $urut2        = sprintf('%04s', $urutan21);
        $so_number    = "SOI" . $Y . $urut2 . "-" . $id_uniq;

        $ArrHeader[] = array(
          'so_number'       => $so_number,
          'so_customer'     => $so_customer,
          'id_customer'     => $id_customer,
          'project'         => $project,
          'code_lv4'        => $code_lv4,
          'no_bom'          => $no_bom,
          'nama_product'    => strtolower($nama_product),
          'due_date'        => $due_date,
          'propose'         => $propose,
          'created_by'      => $this->id_user,
          'created_date'    => $this->datetime
        );

        //BOM material
        $dataBOM = $this->db->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();

        $SUM_PLANNING = 0;
        if (!empty($dataBOM)) {
          foreach ($dataBOM as $key => $value) {
            $UNIQ = $keyH . '-' . $key;
            $ArrBOMDetail[$UNIQ]['so_number'] = $so_number;
            $ArrBOMDetail[$UNIQ]['code_material'] = $value['code_material'];
            $ArrBOMDetail[$UNIQ]['weight'] = $value['weight'];

            $code_lv4   = (!empty($GET_LEVEL4[$value['code_material']]['code_lv1'])) ? $GET_LEVEL4[$value['code_material']]['code_lv1'] : 0;
            $type_name  = (!empty($GET_LEVEL1[$code_lv4]['nama'])) ? $GET_LEVEL1[$code_lv4]['nama'] : 0;

            $CHECK_FTACKEL = substr($no_bom, 0, 3);
            if ($CHECK_FTACKEL == 'BFT') {
              $type_name  = $value['spk'];
            }

            $ArrBOMDetail[$UNIQ]['code_lv1'] = $code_lv4;
            $ArrBOMDetail[$UNIQ]['type_name'] = $type_name;

            //Planning
            $qty_plan = $value['weight'] * $propose;
            $ArrPlanningDetail[$UNIQ]['so_number'] = $so_number;
            $ArrPlanningDetail[$UNIQ]['id_material'] = $value['code_material'];
            $ArrPlanningDetail[$UNIQ]['qty_order'] = $qty_plan;
            $SUM_PLANNING += $qty_plan;
          }
        }

        //SO Produksi
        $ArrPlanning[] = array(
          'so_number' => $so_number,
          'no_pr'   => generateNoPR(),
          'tgl_so' => date('Y-m-d'),
          'id_customer' => $id_customer,
          'project' => $project,
          'qty_order' => $SUM_PLANNING,
          'created_by' => $this->id_user,
          'created_date' => $this->datetime
        );
      }

      //cutting plan
      $ArrCuttingUkuran = [];
      $ArrCuttingMaterial = [];
      $nomorCutting = 0;
      foreach ($cutting_plan as $keyH => $value) {
        $nomorCutting++;
        $code_lv4      = $value['code_lv4'];
        $nama_product  = $value['nama_product'];
        $no_bom        = $value['no_bom'];
        $due_date      = (!empty($value['due_date'])) ? date('Y-m-d', strtotime($value['due_date'])) : NULL;
        $propose      = $value['qty'];

        $urutan22++;
        $urut2        = sprintf('%04s', $urutan22);
        $so_number    = "SOI" . $Y . $urut2 . "-" . $id_uniq;

        $kode_hub     = $so_number . '-' . $nomorCutting;
        $ArrHeaderCutting[] = array(
          'id_request'        => $id_uniq,
          'kode_hub'        => $kode_hub,
          'so_number'       => $so_number,
          'so_customer'     => $so_customer,
          'id_customer'     => $id_customer,
          'project'         => $project,
          'code_lv4'        => $code_lv4,
          'no_bom'          => $no_bom,
          'nama_product'    => strtolower($nama_product),
          'due_date'        => $due_date,
          'propose'         => $propose,
          'created_by'      => $this->id_user,
          'created_date'    => $this->datetime
        );

        //BOM material
        if (!empty($value['ukuran_jadi'])) {
          foreach ($value['ukuran_jadi'] as $val2 => $valx2) {
            $key = $keyH . '-' . $val2;
            $ArrCuttingUkuran[$key]['kode_hub']       = $kode_hub;
            $ArrCuttingUkuran[$key]['id_master']     = $valx2['id'];
            $ArrCuttingUkuran[$key]['length']         = str_replace(',', '', $valx2['length']);
            $ArrCuttingUkuran[$key]['width']         = str_replace(',', '', $valx2['width']);
            $ArrCuttingUkuran[$key]['qty']           = str_replace(',', '', $valx2['qty']);
            $ArrCuttingUkuran[$key]['lari']           = str_replace(',', '', $valx2['lari']);
          }
        }
        if (!empty($value['cutting'])) {
          foreach ($value['cutting'] as $val2 => $valx2) {
            $key = $keyH . '-' . $val2;
            $ArrCuttingMaterial[$key]['kode_hub']       = $kode_hub;
            $ArrCuttingMaterial[$key]['id_master']     = $valx2['id'];
            $ArrCuttingMaterial[$key]['code_lv4']      = $valx2['id_material'];
            $ArrCuttingMaterial[$key]['weight']         = str_replace(',', '', $valx2['weight']);
          }
        }
      }

      //assembly
      $urutan23++;
      $urut2        = sprintf('%04s', $urutan23);
      $so_number    = "SOI" . $Y . $urut2 . "-" . $id_uniq;
      $kode_hub_ass = $so_number;

      $Y          = date('y');
      $SQL			  = "SELECT MAX(no_spk) as maxP FROM so_spk_assembly WHERE no_spk LIKE 'ASS.".$Y.".%' ";
      // echo $SQL; exit;
      $result		  = $this->db->query($SQL)->result_array();
      $angkaUrut2		= $result[0]['maxP'];
      $urutan2		  = (int)substr($angkaUrut2, 7, 4);
      $urutan2++;
      $urut2			  = sprintf('%04s',$urutan2);
      $no_spk_Ass		      = "ASS.".$Y.'.'.$urut2;

      $ArrHeaderAssembly = array(
        'kode_hub'        => $kode_hub_ass,
        'so_number'       => $so_number,
        'so_customer'     => $so_customer,
        'id_customer'     => $id_customer,
        'project'         => $project,
        'code_lv4'        => $code_lv4,
        'no_spk'          => $no_spk_Ass,
        'no_bom'          => $no_bom_header,
        'nama_product'    => strtolower($nama_product),
        'due_date'        => $due_date,
        'propose'         => $propose,
        'created_by'      => $this->id_user,
        'created_date'    => $this->datetime
      );

      $ArrSingleAssembly = [];
      $ArrCuttingUkuranAssembly = [];

      foreach ($single_product as $keyH => $value) {
        $propose      = str_replace(',', '', $value['propose']);

        $ArrSingleAssembly[] = array(
          'kode_hub'        => $kode_hub_ass,
          'category'        => 'single product',
          'id_material'     => $value['code_lv4'],
          'layer'           => $value['no_bom'],
          'qty'             => $propose
        );
      }

      foreach ($cutting_plan as $keyH => $value) {
        if (!empty($value['ukuran_jadi'])) {
          foreach ($value['ukuran_jadi'] as $val2 => $valx2) {
            $key = $keyH . '-' . $val2;

            $ArrCuttingUkuranAssembly[$key]['kode_hub']      = $kode_hub_ass;
            $ArrCuttingUkuranAssembly[$key]['category']      = 'cutting product';
            $ArrCuttingUkuranAssembly[$key]['id_master']     = $valx2['id'];
            $ArrCuttingUkuranAssembly[$key]['id_material']   = $value['code_lv4'];
            $ArrCuttingUkuranAssembly[$key]['layer']         = $value['no_bom'];
            $ArrCuttingUkuranAssembly[$key]['length']        = str_replace(',', '', $valx2['length']);
            $ArrCuttingUkuranAssembly[$key]['width']         = str_replace(',', '', $valx2['width']);
            $ArrCuttingUkuranAssembly[$key]['qty']           = str_replace(',', '', $valx2['qty']);
            $ArrCuttingUkuranAssembly[$key]['m2']            = str_replace(',', '', $valx2['lari']);
          }
        }
      }

      $ArrAssAccessories = [];
      if (!empty($data['DetailAcc'])) {
        foreach ($data['DetailAcc'] as $val => $valx) {
          $urut = sprintf('%03s', $val);
          $ArrAssAccessories[$val]['category']     = 'accessories';
          $ArrAssAccessories[$val]['kode_hub']     = $kode_hub_ass;
          $ArrAssAccessories[$val]['id_master']     = $valx['id'];
          $ArrAssAccessories[$val]['id_material']   = $valx['code_material'];
          $ArrAssAccessories[$val]['ket']             = $valx['ket'];
          $ArrAssAccessories[$val]['qty']          = str_replace(',', '', $valx['weight']);
        }
      }

      $ArrAssMatJoint = [];
      if (!empty($data['DetailMatJoint'])) {
        foreach ($data['DetailMatJoint'] as $val => $valx) {
          $urut = sprintf('%03s', $val);
          $ArrAssMatJoint[$val]['category']     = 'mat joint';
          $ArrAssMatJoint[$val]['kode_hub']     = $kode_hub_ass;
          $ArrAssMatJoint[$val]['id_material'] = $valx['code_material'];
          $ArrAssMatJoint[$val]['id_master']     = $valx['id'];
          $ArrAssMatJoint[$val]['ket']           = $valx['ket'];
          $ArrAssMatJoint[$val]['layer']         = $valx['layer'];
          $ArrAssMatJoint[$val]['qty']        = str_replace(',', '', $valx['weight']);
        }
      }

      $ArrAssFlat = [];
      $ArrAssFlatMat = [];
      if (!empty($data['DetailFlat'])) {
        foreach ($data['DetailFlat'] as $val => $valx) {
          $ArrAssFlat[$val]['category']   = 'flat sheet';
          $ArrAssFlat[$val]['kode_hub']       = $kode_hub_ass;
          $ArrAssFlat[$val]['kode_hub_mat']   = $kode_hub_ass . "-" . $val;
          $ArrAssFlat[$val]['id_master']   = $valx['id'];
          $ArrAssFlat[$val]['length']      = str_replace(',', '', $valx['length']);
          $ArrAssFlat[$val]['width']        = str_replace(',', '', $valx['width']);
          $ArrAssFlat[$val]['qty']          = str_replace(',', '', $valx['qty']);
          $ArrAssFlat[$val]['m2']          = str_replace(',', '', $valx['m2']);

          if (!empty($valx['material'])) {
            foreach ($valx['material'] as $val2 => $valx2) {
              $key = $val . '-' . $val2;
              $ArrAssFlatMat[$key]['category']       = 'material flat sheet';
              $ArrAssFlatMat[$key]['kode_hub']       = $kode_hub_ass . "-" . $val;
              $ArrAssFlatMat[$key]['id_master']     = $valx['id'];
              $ArrAssFlatMat[$key]['code_material']  = $valx2['id_material'];
              $ArrAssFlatMat[$key]['weight']         = str_replace(',', '', $valx2['weight']);
            }
          }
        }
      }

      $ArrAssEnd = [];
      $ArrAssEndMat = [];
      if (!empty($data['DetailEnd'])) {
        foreach ($data['DetailEnd'] as $val => $valx) {
          $ArrAssEnd[$val]['category']     = 'end plate';
          $ArrAssEnd[$val]['kode_hub']       = $kode_hub_ass;
          $ArrAssEnd[$val]['kode_hub_mat']   = $kode_hub_ass . "-" . $val;
          $ArrAssEnd[$val]['id_master']   = $valx['id'];
          $ArrAssEnd[$val]['length']      = str_replace(',', '', $valx['length']);
          $ArrAssEnd[$val]['width']        = str_replace(',', '', $valx['width']);
          $ArrAssEnd[$val]['qty']          = str_replace(',', '', $valx['qty']);
          $ArrAssEnd[$val]['m2']          = str_replace(',', '', $valx['m2']);

          if (!empty($valx['material'])) {
            foreach ($valx['material'] as $val2 => $valx2) {
              $key = $val . '-' . $val2;
              $ArrAssEndMat[$key]['category']     = 'material end plate';
              $ArrAssEndMat[$key]['kode_hub']       = $kode_hub_ass . "-" . $val;
              $ArrAssEndMat[$key]['id_master']     = $valx['id'];
              $ArrAssEndMat[$key]['code_material']  = $valx2['id_material'];
              $ArrAssEndMat[$key]['weight']         = str_replace(',', '', $valx2['weight']);
            }
          }
        }
      }

      $ArrAssJadi = [];
      $ArrAssJadiMat = [];
      if (!empty($data['DetailJadi'])) {
        foreach ($data['DetailJadi'] as $val => $valx) {
          $ArrAssJadi[$val]['category']     = 'ukuran jadi';
          $ArrAssJadi[$val]['kode_hub']       = $kode_hub_ass;
          $ArrAssJadi[$val]['kode_hub_mat']   = $kode_hub_ass . "-" . $val;
          $ArrAssJadi[$val]['id_master']   = $valx['id'];
          $ArrAssJadi[$val]['length']      = str_replace(',', '', $valx['length']);
          $ArrAssJadi[$val]['width']        = str_replace(',', '', $valx['width']);
          $ArrAssJadi[$val]['qty']          = str_replace(',', '', $valx['qty']);
          $ArrAssJadi[$val]['m2']          = str_replace(',', '', $valx['m2']);

          if (!empty($valx['material'])) {
            foreach ($valx['material'] as $val2 => $valx2) {
              $key = $val . '-' . $val2;
              $ArrAssJadiMat[$key]['category']       = 'material ukuran jadi';
              $ArrAssJadiMat[$key]['kode_hub']       = $kode_hub_ass . "-" . $val;
              $ArrAssJadiMat[$key]['id_master']       = $valx['id'];
              $ArrAssJadiMat[$key]['code_material']  = $valx2['id_material'];
              $ArrAssJadiMat[$key]['weight']         = str_replace(',', '', $valx2['weight']);
            }
          }
        }
      }

      $ArrAssOthers = [];
      $ArrAssOthersMat = [];
      if (!empty($data['DetailOthers'])) {
        foreach ($data['DetailOthers'] as $val => $valx) {
          $ArrAssOthers[$val]['category']       = 'others';
          $ArrAssOthers[$val]['kode_hub']       = $kode_hub_ass;
          $ArrAssOthers[$val]['kode_hub_mat']   = $kode_hub_ass . "-" . $val;
          $ArrAssOthers[$val]['id_master']   = $valx['id'];
          $ArrAssOthers[$val]['length']      = str_replace(',', '', $valx['length']);
          $ArrAssOthers[$val]['width']        = str_replace(',', '', $valx['width']);
          $ArrAssOthers[$val]['qty']          = str_replace(',', '', $valx['qty']);
          $ArrAssOthers[$val]['m2']          = str_replace(',', '', $valx['m2']);

          if (!empty($valx['material'])) {
            foreach ($valx['material'] as $val2 => $valx2) {
              $key = $val . '-' . $val2;
              $ArrAssOthersMat[$key]['category']       = 'material others';
              $ArrAssOthersMat[$key]['kode_hub']       = $kode_hub_ass . "-" . $val;
              $ArrAssOthersMat[$key]['id_master']     = $valx['id'];
              $ArrAssOthersMat[$key]['code_material']  = $valx2['id_material'];
              $ArrAssOthersMat[$key]['weight']         = str_replace(',', '', $valx2['weight']);
            }
          }
        }
      }

      //single product
      // print_r($ArrHeader);
      // print_r($ArrPlanning);
      // print_r($ArrBOMDetail);
      // print_r($ArrPlanningDetail);
      //cutting plan
      // print_r($ArrHeaderCutting);
      // print_r($ArrCuttingUkuran);
      // print_r($ArrCuttingMaterial);
      //assembly
      // print_r($ArrHeaderAssembly);
      // print_r($ArrAssAccessories);
      // print_r($ArrAssMatJoint);
      // print_r($ArrAssFlat);
      // print_r($ArrAssFlatMat);
      // print_r($ArrAssEnd);
      // print_r($ArrAssEndMat);
      // print_r($ArrAssJadi);
      // print_r($ArrAssJadiMat);
      // print_r($ArrAssOthers);
      // print_r($ArrAssOthersMat);

      // print_r($ArrCuttingUkuranAssembly);
      // print_r($ArrSingleAssembly);
      // exit;

      $ArrUpdateRequest = array(
        'so_number'     => $so_number,
        'no_bom'        => $no_bom_header,
        'status'        => 'Y'
      );

      $this->db->trans_start();
      //single product
      if (!empty($ArrHeader)) {
        $this->db->insert_batch('so_internal', $ArrHeader);
      }
      if (!empty($ArrPlanning)) {
        $this->db->insert_batch('material_planning_base_on_produksi', $ArrPlanning);
      }
      if (!empty($ArrBOMDetail)) {
        $this->db->insert_batch('so_internal_material', $ArrBOMDetail);
      }
      if (!empty($ArrPlanningDetail)) {
        $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrPlanningDetail);
      }

      //planning cutting
      if (!empty($ArrHeaderCutting)) {
        $this->db->insert_batch('so_spk_cutting', $ArrHeaderCutting);
      }
      if (!empty($ArrCuttingUkuran)) {
        $this->db->insert_batch('so_spk_cutting_plan', $ArrCuttingUkuran);
      }
      if (!empty($ArrCuttingMaterial)) {
        $this->db->insert_batch('so_spk_cutting_material', $ArrCuttingMaterial);
      }

      //assembly
      $this->db->insert('so_spk_assembly', $ArrHeaderAssembly);

      if (!empty($ArrAssAccessories)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrAssAccessories);
      }
      if (!empty($ArrAssMatJoint)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrAssMatJoint);
      }
      if (!empty($ArrAssFlat)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrAssFlat);
      }
      if (!empty($ArrAssEnd)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrAssEnd);
      }
      if (!empty($ArrAssJadi)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrAssJadi);
      }
      if (!empty($ArrAssOthers)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrAssOthers);
      }
      if (!empty($ArrCuttingUkuranAssembly)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrCuttingUkuranAssembly);
      }
      if (!empty($ArrSingleAssembly)) {
        $this->db->insert_batch('so_spk_assembly_detail', $ArrSingleAssembly);
      }


      if (!empty($ArrAssFlatMat)) {
        $this->db->insert_batch('so_spk_assembly_material', $ArrAssFlatMat);
      }
      if (!empty($ArrAssEndMat)) {
        $this->db->insert_batch('so_spk_assembly_material', $ArrAssEndMat);
      }
      if (!empty($ArrAssJadiMat)) {
        $this->db->insert_batch('so_spk_assembly_material', $ArrAssJadiMat);
      }
      if (!empty($ArrAssOthersMat)) {
        $this->db->insert_batch('so_spk_assembly_material', $ArrAssOthersMat);
      }

      $this->db->where('id', $id_uniq);
      $this->db->update('so_internal_request', $ArrUpdateRequest);
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save gagal disimpan ...',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1
        );
        history("Create so internal custom assembly : " . $id_uniq);
      }
      echo json_encode($Arr_Data);
    } else {
      $getData = $this->db->get_where('new_inventory_4', array('code_lv4' => $id))->result_array();
      $getHeader = $this->db->get_where('so_internal_request', array('id' => $uniq))->result_array();

      $WhereIN = array('grid standard', 'standard', 'ftackel');


      //New
      $no_bom = $getHeader[0]['no_bom_planning'];
      $header         = $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
      $detail         = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
      $detail_hi_grid     = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'hi grid std'))->result_array();
      $detail_additive     = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
      $detail_topping     = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
      $detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
      $detail_mat_joint   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'))->result_array();
      $detail_flat_sheet   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
      $detail_end_plate   = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
      $detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
      $detail_others     = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'others'))->result_array();
      $product      = $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));
      $material      = $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'material'));
      $accessories    = $this->bom_hi_grid_custom_model->get_data_where_array('accessories', array('deleted_date' => NULL));
      $bom_additive      = $this->bom_hi_grid_custom_model->get_data_where_array('bom_header', array('deleted_date' => NULL, 'category' => 'additive'));
      $bom_topping      = $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_3 b', 'a.id_product=b.code_lv3', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result();
      $bom_higridstd1      = $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'grid standard'))->result();
      $bom_higridstd2      = $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'standard'))->result();
      $bom_higridstd     = array_merge($bom_higridstd1, $bom_higridstd2);
      $satuan        = $this->bom_hi_grid_custom_model->get_data_where_array('ms_satuan', array('deleted_date' => NULL, 'category' => 'unit'));

      $data = [
        'getData' => $getData,
        'getHeader' => $getHeader,
        'WhereIN' => $WhereIN,
        'getStockProduct' => get_stock_product_New(),
        'getProductLv4' => get_inventory_lv4(),
        'getNameBOMProduct' => get_name_product_by_bom_all(),
        'header' => $header,
        'detail' => $detail,
        'satuan' => $satuan,
        'detail_hi_grid' => $detail_hi_grid,
        'detail_additive' => $detail_additive,
        'detail_topping' => $detail_topping,
        'detail_accessories' => $detail_accessories,
        'detail_mat_joint' => $detail_mat_joint,
        'detail_flat_sheet' => $detail_flat_sheet,
        'detail_end_plate' => $detail_end_plate,
        'detail_ukuran_jadi' => $detail_ukuran_jadi,
        'detail_others' => $detail_others,
        'product' => $product,
        'material' => $material,
        'accessories' => $accessories,
        'bom_additive' => $bom_additive,
        'bom_topping' => $bom_topping,
        'bom_higridstd' => $bom_higridstd,
      ];


      $this->template->title('SPK Stock');
      $this->template->render('spk_stok_custom', $data);
    }
  }
}
