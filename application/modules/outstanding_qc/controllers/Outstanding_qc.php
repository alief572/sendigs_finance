<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Outstanding_qc extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Outstanding_QC.View';
  protected $addPermission    = 'Outstanding_QC.Add';
  protected $managePermission = 'Outstanding_QC.Manage';
  protected $deletePermission = 'Outstanding_QC.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Outstanding_qc/Outstanding_qc_model'
    ));

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    history("View index outstanding QC");
    $this->template->title('Quality Control / Outstanding QC');
    $this->template->render('index');
  }

  public function data_side_outstanding_qc()
  {
    $this->Outstanding_qc_model->data_side_outstanding_qc();
  }

  public function qc($id = null, $tanda = null)
  {
    if (empty($id)) {
      $this->auth->restrict($this->addPermission);
    } else {
      $this->auth->restrict($this->managePermission);
    }
    if ($this->input->post()) {
      $post = $this->input->post();

      $id             = $post['id'];
      $kode           = $post['kode'];
      $kode_det       = $post['kode_det'];
      $no_spk         = $post['no_spk'];
      $qty            = $post['qty'];
      $code_lv4       = $post['code_lv4'];
      $no_bom         = $post['no_bom'];
      $detail         = $post['check'];
      $detail_data    = $post['detail'];
      $tanda          = $post['tanda'];
      $datetimeNm     = date('Ymdhis');

      $UpdateData = [];
      $ArrHistFG = [];
      $SUM_OKE = 0;
      $SUM_NG = 0;
      foreach ($detail as $key => $value) {
        $product_ke = $detail_data[$key]['id'];
        $status = $detail_data[$key]['status'];
        if ($status == 'OKE' or $status == 'NG') {
          $UpdateData[$key]['id_key_spk'] = $id;
          $UpdateData[$key]['kode'] = $kode;
          $UpdateData[$key]['kode_det'] = $kode_det;
          $UpdateData[$key]['no_spk'] = $no_spk;
          $UpdateData[$key]['qty'] = $qty;
          $UpdateData[$key]['product_ke'] = $product_ke;
          $UpdateData[$key]['status'] = $status;
          $UpdateData[$key]['daycode'] = $detail_data[$key]['daycode'];
          $UpdateData[$key]['qc_pass'] = (!empty($detail_data[$key]['qc_pass'])) ? date('Y-m-d', strtotime($detail_data[$key]['qc_pass'])) : NULL;
          $UpdateData[$key]['note'] = $detail_data[$key]['note'];
          $UpdateData[$key]['qc_by'] = $this->id_user;
          $UpdateData[$key]['qc_date'] = $this->datetime;

          if ($status == 'OKE') {
            $SUM_OKE += 1;
          }
          if ($status == 'NG') {
            $SUM_NG += 1;
          }

          //UPLOAD DOCUMENT
          // if (!empty($_FILES["inspeksi_" . $product_ke]["name"])) {
          //   $target_dir     = "assets/files/";
          //   $target_dir_u   = $_SERVER['DOCUMENT_ROOT'] . "assets/files/";
          //   $name_file      = 'inspeksi_qc_' . $product_ke . '_' . $datetimeNm;
          //   $target_file    = $target_dir . basename($_FILES["inspeksi_" . $product_ke]["name"]);
          //   $name_file_ori  = basename($_FILES["inspeksi_" . $product_ke]["name"]);
          //   $imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
          //   $nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;
          //   $file_name    	= $name_file . "." . $imageFileType;

          //   if (!empty($_FILES["inspeksi_" . $product_ke]["tmp_name"])) {
          //     move_uploaded_file($_FILES["inspeksi_" . $product_ke]["tmp_name"], $nama_upload);
          //     $UpdateData[$key]['doc_inspeksi'] = $file_name;
          //   }
          // }
        }
      }

      $getStock     = $this->db->get_where('stock_product', array('code_lv4' => $code_lv4, 'no_bom' => $no_bom))->result_array();
      $actual_stock = (!empty($getStock[0]['actual_stock'])) ? $getStock[0]['actual_stock'] : 0;
      $ng_stock     = (!empty($getStock[0]['ng_stock'])) ? $getStock[0]['ng_stock'] : 0;

      $ArrUpdateStok = [
        'actual_stock' => $actual_stock + $SUM_OKE,
        'ng_stock' => $ng_stock + $SUM_NG
      ];

      // echo "<pre>";
      // print_r($UpdateData);
      // exit;

      $this->db->trans_start();
      if (!empty($UpdateData)) {
        $this->db->insert_batch('so_internal_product', $UpdateData);

        $this->db->where('code_lv4', $code_lv4);
        $this->db->where('no_bom', $no_bom);
        $this->db->update('stock_product', $ArrUpdateStok);
      }
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $status  = array(
          'pesan'    => 'Failed process data!',
          'status'  => 0,
          'id'  => $id,
          'tanda' => $tanda
        );
      } else {
        $this->db->trans_commit();
        $status  = array(
          'pesan'    => 'Success process data!',
          'status'  => 1,
          'id'  => $id,
          'tanda' => $tanda
        );
        history("Proses qc : " . $id);
      }
      echo json_encode($status);
    } else {
      if ($tanda == 'old') {
        $listData = $this->db
          ->select('a.no_spk, a.qty, b.nama_product, c.variant_product, a.id, a.kode, a.kode_det, b.code_lv4, b.no_bom')
          ->join('so_internal b', 'a.id_so=b.id', 'left')
          ->join('bom_header c', 'b.no_bom=c.no_bom', 'left')
          ->get_where('so_internal_spk a', array('a.id' => $id))
          ->result();

          $no_bom = $listData[0]->no_bom;
          $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
          $NamaProduct 	      = (!empty($GetNamaBOMProduct[$no_bom]))?$GetNamaBOMProduct[$no_bom]:0;

        $data = [
          'tanda' => $tanda,
          'listData' => $listData,
          'NamaProduct' => $NamaProduct,
          'GET_QC' => get_quality_control()
        ];
        $this->template->set($data);
        $this->template->render('detail');
      } else {
        $listData = $this->db
          ->select('a.no_spk, a.qty, b.nama_product, c.variant_product, a.id, a.kode, a.kode_det, b.code_lv4, b.no_bom, z.product_ke, z.id AS id_qc')
          ->join('so_internal_spk a', 'a.id=z.id_key_spk', 'left')
          ->join('so_internal b', 'a.id_so=b.id', 'left')
          ->join('bom_header c', 'b.no_bom=c.no_bom', 'left')
          ->get_where('so_internal_product z', array('z.id_key_spk' => $id))
          ->result_array();

          $no_bom = $listData[0]['no_bom'];
          $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
          $NamaProduct 	      = (!empty($GetNamaBOMProduct[$no_bom]))?$GetNamaBOMProduct[$no_bom]:0;

        $data = [
          'tanda' => $tanda,
          'listData' => $listData,
          'NamaProduct' => $NamaProduct,
          'GET_QC' => get_quality_control()
        ];
        $this->template->set($data);
        $this->template->render('detail_new');
      }
    }
  }

  public function qcNew()
  {
    $post = $this->input->post();

    $id             = $post['id'];
    $kode           = $post['kode'];
    $kode_det       = $post['kode_det'];
    $no_spk         = $post['no_spk'];
    $qty            = $post['qty'];
    $code_lv4       = $post['code_lv4'];
    $no_bom         = $post['no_bom'];
    $detail         = $post['check'];
    $detail_data    = $post['detail'];
    $tanda          = $post['tanda'];
    $datetimeNm     = date('Ymdhis');

    $UpdateData = [];
    $ArrHistFG = [];
    $SUM_OKE = 0;
    $SUM_NG = 0;
    foreach ($detail as $key => $value) {
      $product_ke = $detail_data[$key]['id'];
      $status = $detail_data[$key]['status'];
      if ($status == 'OKE' or $status == 'NG') {
        $UpdateData[$key]['id'] = $product_ke;
        $UpdateData[$key]['status'] = $status;
        $UpdateData[$key]['daycode'] = $detail_data[$key]['daycode'];
        $UpdateData[$key]['inspektor'] = $detail_data[$key]['inspektor'];
        $UpdateData[$key]['qc_pass'] = (!empty($detail_data[$key]['qc_pass'])) ? date('Y-m-d', strtotime($detail_data[$key]['qc_pass'])) : NULL;
        $UpdateData[$key]['note'] = $detail_data[$key]['note'];
        $UpdateData[$key]['qc_by'] = $this->id_user;
        $UpdateData[$key]['qc_date'] = $this->datetime;

        if ($status == 'OKE') {
          $SUM_OKE += 1;
        }
        if ($status == 'NG') {
          $SUM_NG += 1;
        }

        //UPLOAD DOCUMENT
        // if (!empty($_FILES["inspeksi_" . $product_ke]["name"])) {
        //   $target_dir     = "assets/files/";
        //   $target_dir_u   = $_SERVER['DOCUMENT_ROOT'] . "assets/files/";
        //   $name_file      = 'inspeksi_qc_' . $product_ke . '_' . $datetimeNm;
        //   $target_file    = $target_dir . basename($_FILES["inspeksi_" . $product_ke]["name"]);
        //   $name_file_ori  = basename($_FILES["inspeksi_" . $product_ke]["name"]);
        //   $imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        //   $nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;
        //   $file_name    	= $name_file . "." . $imageFileType;

        //   if (!empty($_FILES["inspeksi_" . $product_ke]["tmp_name"])) {
        //     move_uploaded_file($_FILES["inspeksi_" . $product_ke]["tmp_name"], $nama_upload);
        //     $UpdateData[$key]['doc_inspeksi'] = $file_name;
        //   }
        // }
      }
    }

    $getStock     = $this->db->get_where('stock_product', array('code_lv4' => $code_lv4, 'no_bom' => $no_bom))->result_array();
    $actual_stock = (!empty($getStock[0]['actual_stock'])) ? $getStock[0]['actual_stock'] : 0;
    $ng_stock     = (!empty($getStock[0]['ng_stock'])) ? $getStock[0]['ng_stock'] : 0;

    $ArrUpdateStok = [
      'actual_stock' => $actual_stock + $SUM_OKE,
      'ng_stock' => $ng_stock + $SUM_NG
    ];

    $ArrUpdateStokNew[0]['code_lv4'] = $code_lv4;
    $ArrUpdateStokNew[0]['no_bom'] = $no_bom;
    $ArrUpdateStokNew[0]['stok_aktual'] = $SUM_OKE;
    $ArrUpdateStokNew[0]['stok_booking'] = 0;
    $ArrUpdateStokNew[0]['stok_downgrade'] = 0;
    $ArrUpdateStokNew[0]['qty'] = $SUM_OKE;

    $ArrUpdateStokNew[1]['code_lv4'] = $code_lv4;
    $ArrUpdateStokNew[1]['no_bom'] = $no_bom;
    $ArrUpdateStokNew[1]['stok_aktual'] = 0;
    $ArrUpdateStokNew[1]['stok_booking'] = 0;
    $ArrUpdateStokNew[1]['stok_downgrade'] = $SUM_NG;
    $ArrUpdateStokNew[1]['qty'] = $SUM_NG;

    // echo "<pre>";
    // print_r($UpdateData);
    // exit;

    $this->db->trans_start();
    if (!empty($UpdateData)) {
      $this->db->update_batch('so_internal_product', $UpdateData, 'id');

      // $this->db->where('code_lv4',$code_lv4);
      // $this->db->where('no_bom',$no_bom);
      // $this->db->update('stock_product',$ArrUpdateStok);
    }
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $status  = array(
        'pesan'    => 'Failed process data!',
        'status'  => 0,
        'id'  => $id,
        'tanda' => $tanda
      );
    } else {
      $this->db->trans_commit();
      $status  = array(
        'pesan'    => 'Success process data!',
        'status'  => 1,
        'id'  => $id,
        'tanda' => $tanda
      );
      history_product($ArrUpdateStokNew, 'plus', $kode_det . '/' . $no_spk, 'penambahan finish good');
      history("Proses qc : " . $id);
    }
    echo json_encode($status);
  }
}
