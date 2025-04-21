<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Outgoing_stok extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Outgoing_Stok.View';
  protected $addPermission    = 'Outgoing_Stok.Add';
  protected $managePermission = 'Outgoing_Stok.Manage';
  protected $deletePermission = 'Outgoing_Stok.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Outgoing_stok/outgoing_stok_model'
    ));
    // $this->template->title('Manage Data Supplier');

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    history("View data outgoing stok");
    $this->template->title('Gudang Stok / Outgoing Indirect');
    $this->template->render('index');
  }

  public function data_side_request_material()
  {
    $this->outgoing_stok_model->data_side_request_material();
  }

  public function request($id = null)
  {
    if ($this->input->post()) {
      $data           = $this->input->post();
      $session        = $this->session->userdata('app_session');
      $id_gudang      = 17;
      $id_costcenter  = $data['id_costcenter'];
      $id_dept        = $data['id_dept'];
      $pic            = $data['pic'];
      $keterangan      = $data['keterangan'];
      $tanggal        = date('Y-m-d', strtotime($data['tanggal']));

      if (!empty($data['detail'])) {
        $detail      = $data['detail'];
      }

      $kode_trans = generateNoTransaksiStok();
      $GET_DETAIL_MAT = get_accessories();

      $ArrInsertDetail   = array();
      $SUM_MAT = 0;
      $SUM_PACK = 0;
      $ArrStock = [];

      $valid_qty_stock = 1;

      if (!empty($data['detail'])) {
        foreach ($detail as $val => $valx) {
          $konversi     = (!empty($GET_DETAIL_MAT[$valx['id']]['konversi'])) ? $GET_DETAIL_MAT[$valx['id']]['konversi'] : 1;
          if($konversi <= 0) {
            $konversi = 1;
          }
          $qty_packing   = str_replace(',', '', $valx['sudah_request']);
          if ($qty_packing > 0) {
            $qty_berat = $qty_packing;

            $SUM_MAT  += ($qty_berat);
            // $SUM_PACK += $qty_packing;
            //detail adjustmeny

            $qty_stock = 0;
            $get_qty_stock = $this->db->get_where('warehouse_stock', ['id_material' => $valx['id'], 'id_gudang' => $id_gudang])->row();
            if (!empty($get_qty_stock)) {
              $qty_stock = $get_qty_stock->qty_stock;
            }

            $ArrInsertDetail[$val]['kode_trans']     = $kode_trans;
            $ArrInsertDetail[$val]['id_material']   = $valx['id'];
            $ArrInsertDetail[$val]['qty_order']     = $qty_berat;
            $ArrInsertDetail[$val]['qty_oke']       = $qty_berat;
            $ArrInsertDetail[$val]['keterangan']     = strtolower($valx['ket_request']);
            $ArrInsertDetail[$val]['update_by']     = $this->id_user;
            $ArrInsertDetail[$val]['update_date']   = $this->datetime;

            $ArrStock[$val]['id'] = $valx['id'];
            $ArrStock[$val]['qty'] = ($qty_berat );

            if ($valid_qty_stock == 1) {
              if ($qty_stock < ($qty_berat)) {
                $valid_qty_stock = 0;
              }
            }
          }
        }
      }

      $ArrInsert = array(
        'kode_trans'       => $kode_trans,
        'category'         => 'outgoing stok',
        'jumlah_mat'       => $SUM_MAT,
        // 'jumlah_mat_packing' 	=> $SUM_PACK,
        'tanggal'         => $tanggal,
        'pic'             => $pic,
        'id_dept'         => $id_dept,
        'note'             => $keterangan,
        'id_gudang_dari'   => $id_gudang,
        'id_gudang_ke'     => $id_costcenter,
        'kd_gudang_ke'     => strtoupper(get_name('warehouse', 'kd_gudang', 'id', $id_costcenter)),
        'created_by'       => $this->id_user,
        'created_date'     => $this->datetime
      );

      // print_r($ArrInsert);
      // print_r($ArrInsertDetail);
      // exit;

      $this->db->trans_begin();
      if (!empty($ArrInsertDetail)) {
        $this->db->insert('warehouse_adjustment', $ArrInsert);
        $this->db->insert_batch('warehouse_adjustment_detail', $ArrInsertDetail);
      }

      $id_gudang_dari = 17;
      $id_gudang_ke = $id_costcenter;

      $get_warehouse = $this->db->get_where('warehouse_adjustment', ['kode_trans' => $kode_trans])->row();
      $nm_gudang_ke = '';
      $get_nm_gudang_ke = $this->db->get_where('warehouse', ['id' => $get_warehouse->id_gudang_ke])->row();
      if (!empty($get_nm_gudang_ke)) {
        $nm_gudang_ke = strtoupper($get_nm_gudang_ke->nm_gudang);
      }

      $get_warehouse_detail = $this->db->get_where('warehouse_adjustment_detail', ['kode_trans' => $kode_trans])->result();
      foreach ($get_warehouse_detail as $item_detail) {


        $stock_terakhir = 0;
        $get_stock_material = $this->db->get_where('warehouse_stock', ['id_material' => $item_detail->id_material, 'id_gudang' => $id_gudang_dari])->row();
        if (!empty($get_stock_material)) {
          $stock_terakhir = ($get_stock_material->qty_stock);
        }

        $stock_terakhir1 = 0;
        $get_stock_material = $this->db->get_where('warehouse_stock', ['id_material' => $item_detail->id_material, 'id_gudang' => $id_gudang_ke])->row();
        if (!empty($get_stock_material)) {
          $stock_terakhir1 = ($get_stock_material->qty_stock);
        }

        $value_neraca = 0;
        $get_value_neraca = $this->db
          ->select('a.value_neraca, a.qty')
          ->from('tr_cost_book a')
          ->where('a.id_material', $item_detail->id_material)
          ->where('a.id_gudang_ke', $id_gudang_dari)
          ->order_by('a.tgl', 'DESC')
          ->get()
          ->row();
        if (!empty($get_value_neraca)) {
          $value_neraca = $get_value_neraca->value_neraca;
        }

        $value_neraca2 = 0;
        $get_value_neraca2 = $this->db
          ->select('a.value_neraca, a.qty')
          ->from('tr_cost_book a')
          ->where('a.id_material', $item_detail->id_material)
          ->where('a.id_gudang_ke', $id_gudang_ke)
          ->order_by('a.tgl', 'DESC')
          ->get()
          ->row();
        if (!empty($get_value_neraca2)) {
          $value_neraca2 = $get_value_neraca2->value_neraca;
        }

        $nilai_costbook = 0;
        $get_costbook = $this->db->query("
        SELECT 
          a.costbook 
        FROM 
          tr_cost_book a 
        WHERE 
          a.id_material = '" . $item_detail->id_material . "' AND 
          (
            (a.id_gudang_ke = '" . $id_gudang_dari . "' AND a.jenis_transaksi = 'In Pembelian' AND a.costbook > 0) OR 
            (a.jenis_transaksi = 'Costbook Awal' AND a.id_gudang_dari = '" . $id_gudang_dari . "' AND a.costbook > 0)
          ) 
            ORDER BY a.created_on DESC LIMIT 1 ")
          ->row();
        if (!empty($get_costbook)) {
          $nilai_costbook = $get_costbook->costbook;
        }



        $id_costbook = generate_no_costbook();
        $urutan = (int) substr($id_costbook, 13, 5);
        $urutan++;
        $tahun = date('Y-m-');
        $huruf = "CBO-";
        $id_costbook2 = $huruf . $tahun . sprintf("%06s", $urutan);

        $nm_material = '';
        $kode_material = '';
        $get_material = $this->db->select('a.stock_name as nm_material, a.id_stock as kode_material')
          ->from('accessories a')
          ->where('a.id', $item_detail->id_material)
          ->get()
          ->row();

        if (!empty($get_material)) {
          $nm_material = $get_material->nm_material;
          $kode_material = $get_material->kode_material;
        }

        $insert_costbook = $this->db->insert('tr_cost_book', [
          'id' => $id_costbook,
          'id_material' => $item_detail->id_material,
          'nm_material' => $nm_material,
          'kode_produk' => $kode_material,
          'tipe_material' => 'stok',
          'id_gudang_dari' => $id_gudang_dari,
          'nm_gudang_dari' => 'GUDANG INDIRECT',
          'id_gudang_ke' => $get_warehouse->id_gudang_ke,
          'nm_gudang_ke' => $nm_gudang_ke,
          'tgl' => date('Y-m-d'),
          'no_transaksi' => $kode_trans,
          'jenis_transaksi' => 'Out ke Cost Center',
          'qty_transaksi' => $item_detail->qty_oke,
          'qty' => ($stock_terakhir - $item_detail->qty_oke),
          'costbook' => $nilai_costbook,
          'value_transaksi' => ($nilai_costbook * $item_detail->qty_oke),
          'value_neraca' => ($value_neraca - ($nilai_costbook * $item_detail->qty_oke)),
          'created_by' => $this->auth->user_id(),
          'created_on' => date('Y-m-d H:i:s')
        ]);
        if (!$insert_costbook) {
          print_r($this->db->error($insert_costbook));
          exit;
        }

        $insert_costbook1 = $this->db->insert('tr_cost_book', [
          'id' => $id_costbook2,
          'id_material' => $item_detail->id_material,
          'nm_material' => $nm_material,
          'kode_produk' => $kode_material,
          'tipe_material' => 'stok',
          'id_gudang_dari' => $id_gudang_dari,
          'nm_gudang_dari' => 'GUDANG INDIRECT',
          'id_gudang_ke' => $get_warehouse->id_gudang_ke,
          'nm_gudang_ke' => $nm_gudang_ke,
          'tgl' => date('Y-m-d'),
          'no_transaksi' => $kode_trans,
          'jenis_transaksi' => 'In dari Gd. Indirect',
          'qty_transaksi' => $item_detail->qty_oke,
          'qty' => ($stock_terakhir1 + $item_detail->qty_oke),
          'nilai_beli' => $nilai_costbook,
          'costbook' => (($value_neraca2 + ($nilai_costbook * $item_detail->qty_oke)) / ($stock_terakhir1 + $item_detail->qty_oke)),
          'value_transaksi' => ((($value_neraca2 + ($nilai_costbook * $item_detail->qty_oke)) / ($stock_terakhir1 + $item_detail->qty_oke)) * $item_detail->qty_oke),
          'value_neraca' => ($value_neraca2 + ((($value_neraca2 + ($nilai_costbook * $item_detail->qty_oke)) / ($stock_terakhir1 + $item_detail->qty_oke)) * $item_detail->qty_oke)),
          'created_by' => $this->auth->user_id(),
          'created_on' => date('Y-m-d H:i:s')
        ]);
        if (!$insert_costbook1) {
          print_r($this->db->error($insert_costbook1));
          exit;
        }

        $arr_warehouse_sub = [];
        $arr_warehouse_prod = [];
        $wgere = ['subgudang', 'stok', 'costcenter'];

        $get_sub_prod_warehouse = $this->db->query("SELECT id, `desc` FROM warehouse WHERE `desc` IN ('subgudang', 'stok', 'costcenter')")->result();
        foreach ($get_sub_prod_warehouse as $item_ware) {
          if ($item_ware->desc == 'subgudang' || $item_ware->desc == 'stok') {
            $arr_warehouse_sub[] = $item_ware->id;
          } else {
            $arr_warehouse_prod[] = $item_ware->id;
          }
        }

        $ttl_qty_sub = 0;
        $ttl_qty_prod = 0;
        $ttl_qty_pusat = 0;

        $get_ttl_qty_sub = $this->db->query("SELECT SUM(qty_stock) as ttl_qty_sub FROM warehouse_stock WHERE id_material = '" . $item_detail->id_material . "' AND id_gudang IN ('" . str_replace(",", "','", implode(',', $arr_warehouse_sub)) . "')")->row();
        if (!empty($get_ttl_qty_sub)) {
          $ttl_qty_sub = $get_ttl_qty_sub->ttl_qty_sub;
        }

        $get_ttl_qty_prod = $this->db->query("SELECT SUM(qty_stock) as ttl_qty_prod FROM warehouse_stock WHERE id_material = '" . $item_detail->id_material . "' AND id_gudang IN ('" . str_replace(",", "','", implode(',', $arr_warehouse_prod)) . "')")->row();
        if (!empty($get_ttl_qty_prod)) {
          $ttl_qty_prod = $get_ttl_qty_prod->ttl_qty_prod;
        }

        $get_ttl_qty_pusat = $this->db->query("SELECT SUM(qty_stock) as ttl_qty_pusat FROM warehouse_stock WHERE id_material = '" . $item_detail->id_material . "'  AND id_gudang = '1'")->row();
        if (!empty($get_ttl_qty_pusat)) {
          $ttl_qty_pusat = $get_ttl_qty_pusat->ttl_qty_pusat;
        }

        $insert_price_book = $this->db->insert('price_book_produksi', [
          'id_material' => $item_detail->id_material,
          'pusat' => $ttl_qty_pusat,
          'subgudang' => $ttl_qty_sub,
          'produksi' => ($ttl_qty_prod + $item_detail->qty_oke),
          'price_book' => (($value_neraca2 + ($nilai_costbook * $item_detail->qty_oke)) / ($stock_terakhir1 + $item_detail->qty_oke)),
          'status' => 'Y',
          'kode_trans' => $item_detail->kode_trans,
          'updated_by' => $this->auth->user_id(),
          'updated_date' => date('Y-m-d H:i:s')
        ]);
      }
      // $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE || $valid_qty_stock < 1) {
        $this->db->trans_rollback();

        $msg = 'Save gagal disimpan ...';
        if ($valid_qty_stock < 1) {
          $msg = 'Sorry, the stock is less than the request !';
        }

        $Arr_Data  = array(
          'pesan'    => $msg,
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1,
        );
        move_warehouse_stok($ArrStock, $id_gudang, $id_costcenter, $kode_trans, null);
        history("Outgoing barang stok : " . $kode_trans);
      }
      echo json_encode($Arr_Data);
    } else {

      $listGudang     = $this->db->get_where('warehouse', array('desc' => 'stok', 'id' => '17'))->result_array();
      $listGudangKe   = $this->db->order_by('urut', 'ASC')->get_where('warehouse', array('desc' => 'costcenter'))->result_array();
      $listDepartment = $this->db->order_by('nama', 'ASC')->get_where('ms_department', array('deleted_date' => NULL))->result_array();

      $data = [
        'listGudang' => $listGudang,
        'listGudangKe' => $listGudangKe,
        'listDepartment' => $listDepartment,
        'GET_MATERIAL' => get_inventory_lv4()
      ];
      $this->template->title('Outgoing Indirect');
      $this->template->render('request', $data);
    }
  }

  public function server_side_request_produksi()
  {
    $this->outgoing_stok_model->server_side_request_produksi();
  }

  public function modal_request_edit()
  {
    if ($this->input->post()) {
      $data       = $this->input->post();
      $data_session  = $this->session->userdata;

      $kode_trans      = $data['kode_trans'];
      // print_r($data);
      // exit;
      $GET_DETAIL_MAT = get_inventory_lv4();

      $ArrInsertDetail   = array();
      $SUM_MAT = 0;
      $SUM_PACK = 0;

      $valid_qty_stok = 1;

      if (!empty($data['detail'])) {
        foreach ($data['detail'] as $val => $valx) {
          $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi'])) ? $GET_DETAIL_MAT[$valx['id_material']]['konversi'] : 0;
          if($konversi <= 0) {
            $konversi = 1;
          }
          $qty_packing   = str_replace(',', '', $valx['edit_qty']);
          if ($qty_packing > 0) {
            $qty_berat = $qty_packing * $konversi;

            $SUM_MAT  += $qty_berat;
            $SUM_PACK += $qty_packing;
            //detail adjustmeny
            $ArrInsertDetail[$val]['id']             = $valx['id'];
            $ArrInsertDetail[$val]['qty_order']     = $qty_berat;
            $ArrInsertDetail[$val]['qty_oke']       = $qty_berat;
            $ArrInsertDetail[$val]['keterangan']     = strtolower($valx['keterangan']);
            $ArrInsertDetail[$val]['update_by']     = $this->id_user;
            $ArrInsertDetail[$val]['update_date']   = $this->datetime;

            $qty_stock = 0;
            $get_qty_stock = $this->db->get_where('warehouse_stock', ['id_material' => $valx['id'], 'id_gudang' => 17])->row();
            if (!empty($get_qty_stock)) {
              $qty_stock = $get_qty_stock->qty_stock;
            }

            if($valid_qty_stok == 1) {
              if($qty_stock < $qty_berat) {
                $valid_qty_stok = 0;
              }
            }
          }
        }
      }

      $ArrInsert = array(
        'jumlah_mat'           => $SUM_MAT,
        'jumlah_mat_packing'   => $SUM_PACK,
        'updated_by'       => $this->id_user,
        'updated_date'     => $this->datetime
      );

      // print_r($ArrInsert);
      // print_r($ArrInsertDetail);
      // exit;

      // exit;
      $this->db->trans_start();
      $this->db->where('kode_trans', $kode_trans);
      $this->db->update('warehouse_adjustment', $ArrInsert);

      if (!empty($ArrInsertDetail)) {
        $this->db->update_batch('warehouse_adjustment_detail', $ArrInsertDetail, 'id');
      }
      $this->db->trans_complete();


      if ($this->db->trans_status() === FALSE || $valid_qty_stok < 1) {
        $this->db->trans_rollback();

        $msg = 'Save process failed. Please try again later ...';
        if($valid_qty_stok < 1) {
          $msg = 'Sorry, the stock is less than the request !';
        }

        $Arr_Data  = array(
          'pesan'    => $msg,
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save process success. Thanks ...',
          'status'  => 1
        );
        history("Update request material (subgudang) : " . $kode_trans);
      }
      echo json_encode($Arr_Data);
    } else {
      $kode_trans = $this->uri->segment(3);
      $tanda      = $this->uri->segment(4);

      $getData        = $this->db->get_where('warehouse_adjustment a', array('a.kode_trans' => $kode_trans))->result_array();
      if ($getData[0]['checked'] == 'Y') {
        $tanda      = 'detail';
      }
      $getDataDetail  = $this->db->get_where('warehouse_adjustment_detail a', array('a.kode_trans' => $kode_trans))->result_array();
      $data = array(
        'tanda' => $tanda,
        'getData' => $getData,
        'getDataDetail' => $getDataDetail,
        'GET_MATERIAL' => get_accessories(),
        'GET_SATUAN' => get_list_satuan(),
        'kode' => $kode_trans
      );

      $this->load->view('modal_request_edit', $data);
    }
  }

  public function print_spk_request()
  {
    $kode_trans  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = get_name('users', 'nm_lengkap', 'id_user', $session['id_user']);

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $getData = $this->db->get_where('warehouse_adjustment a', array(
      'a.kode_trans' => $kode_trans
    ))
      ->result_array();

    $getDataDetail  = $this->db->get_where('warehouse_adjustment_detail a', array(
      'a.kode_trans' => $kode_trans
    ))
      ->result_array();

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'getData' => $getData,
      'getDataDetail' => $getDataDetail,
      'GET_MATERIAL' => get_accessories(),
      'GET_SATUAN' => get_list_satuan(),
      'kode' => $kode_trans
    );

    history('Print spk request material ' . $kode_trans);
    $this->load->view('print_spk_request', $data);
  }










  public function data_side_spk_material()
  {
    $this->outgoing_stok_model->data_side_spk_material();
  }

  public function request_to_subgudang()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id        = $data['id'];
    $getdata = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();

    $this->db->where('id', $id);
    $this->db->update('so_internal_spk', array('sts_request' => 'Y', 'request_by' => $this->id_user, 'request_date' => $this->datetime));

    $Arr_Data  = array(
      'id'    => $id,
      'kode_det'    => $getdata[0]['kode_det'],
    );
    echo json_encode($Arr_Data);
  }

  public function add($id = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id        = $data['id_uniq'];
      $Detail    = $data['detail'];

      $ArrInsert = [];
      $ArrStock = [];
      foreach ($Detail as $key => $value) {
        $ArrInsert[$key]['id_det_spk'] = $value['id'];
        $ArrInsert[$key]['code_material'] = $value['code_material'];
        $ArrInsert[$key]['weight'] = str_replace(',', '', $value['berat']);
        $ArrInsert[$key]['code_material_aktual'] = $value['code_material_aktual'];
        $ArrInsert[$key]['weight_aktual'] = str_replace(',', '', $value['berat_aktual']);
        $ArrInsert[$key]['created_by'] = $this->id_user;
        $ArrInsert[$key]['created_date'] = $this->datetime;
        $ArrInsert[$key]['gudang'] = 'produksi';

        $ArrStock[$key]['id'] = $value['code_material'];
        $ArrStock[$key]['qty'] = str_replace(',', '', $value['berat_aktual']);
      }

      $ArrUpdate = [
        'sts_produksi' => 'P',
        'produksi_by' => $this->id_user,
        'produksi_date' => $this->datetime
      ];

      $getData = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
      // print_r($getData);
      $kode_trans = $getData[0]['kode_det'];
      $id_gudang_dari = $getData[0]['id_gudang'];
      $id_costcenter  = $getData[0]['id_costcenter'];
      $nm_costcenter  = strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $getData[0]['id_costcenter']));

      // exit;

      $this->db->trans_start();
      if (!empty($ArrInsert)) {
        $this->db->insert_batch('so_internal_spk_material_pengeluaran', $ArrInsert);
      }

      $this->db->where('id', $id);
      $this->db->update('so_internal_spk', $ArrUpdate);

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
          'status'  => 1,
        );
        // move_warehouse($ArrStock, $id_gudang_dari, null, $kode_trans, $nm_costcenter);
        history("Request produksi to subgudang request list : " . $id);
      }
      echo json_encode($Arr_Data);
    } else {

      $getData = $this->db
        ->select('b.*, a.*, a.id AS id_uniq')
        ->join('so_internal b', 'a.id_so=b.id', 'left')
        ->get_where('so_internal_spk a', array(
          'a.id' => $id
        ))
        ->result_array();


      $id_gudang = 2;
      $kode  = $getData[0]['kode_det'];
      $qty   = $getData[0]['qty'];
      $getMaterialMixing  = $this->db->select('id, code_material, SUM(weight) AS berat')->group_by('code_material')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name !=' => 'mixing'))->result_array();

      $data = [
        'getData' => $getData,
        'kode' => $kode,
        'qty' => $qty,
        'getMaterialMixing' => $getMaterialMixing,
        'GET_STOK' => getStokMaterial($id_gudang),
        'GET_MATERIAL' => get_inventory_lv4()
      ];
      $this->template->title('Request Material Non-Mixing');
      $this->template->render('add', $data);
    }
  }

  public function print_spk()
  {
    $kode  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = get_name('users', 'nm_lengkap', 'id_user', $session['id_user']);

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $getData = $this->db
      ->select('b.*, a.*, a.id AS id_uniq')
      ->join('so_internal b', 'a.id_so=b.id', 'left')
      ->get_where('so_internal_spk a', array(
        'a.kode_det' => $kode
      ))
      ->result_array();

    $getMaterialMixing  = $this->db
      ->select('a.id, b.id AS id2, b.code_material, b.weight AS berat_req, b.weight_aktual AS berat_act')
      ->group_by('a.code_material')->where('a.kode_det', $kode)
      ->join('so_internal_spk_material_pengeluaran b', 'a.id=b.id_det_spk')
      ->get_where('so_internal_spk_material a', array('a.type_name <>' => 'mixing', 'b.gudang' => 'produksi'))->result_array();

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'getData' => $getData,
      'getMaterialMixing' => $getMaterialMixing,
      'GET_MATERIAL' => get_inventory_lv4(),
      'kode' => $kode
    );

    history('Print spk material ' . $kode);
    $this->load->view('print_spk', $data);
  }

  //request material








}
