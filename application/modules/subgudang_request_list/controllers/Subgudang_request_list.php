<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Subgudang_request_list extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Subgudang_Request_List.View';
  protected $addPermission    = 'Subgudang_Request_List.Add';
  protected $managePermission = 'Subgudang_Request_List.Manage';
  protected $deletePermission = 'Subgudang_Request_List.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Subgudang_request_list/subgudang_request_list_model'
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

    history("View data subgudang request list");
    $this->template->title('Gudang Material / Subgudang / Outgoing');
    $this->template->render('index');
  }

  public function data_side_spk_material()
  {
    $this->subgudang_request_list_model->data_side_spk_material();
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

  public function add2($id = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id        = $data['id_uniq'];
      $Detail    = $data['detail'];

      // print_r($id);
      // exit;

      $valid_qty_stock = 1;

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
        $ArrInsert[$key]['gudang'] = 'subgudang';

        $ArrStock[$key]['id'] = $value['code_material'];
        $ArrStock[$key]['qty'] = str_replace(',', '', $value['berat_aktual']);

        if ($valid_qty_stock == 1) {
          if ($value['stock'] < $value['berat_aktual']) {
            $valid_qty_stock = 0;
          }
        }
      }

      $ArrUpdate = [
        'sts_subgudang' => 'Y',
        'subgudang_by' => $this->id_user,
        'subgudang_date' => $this->datetime
      ];

      $getData = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
      // print_r($getData);
      $kode_trans = $getData[0]['kode_det'];
      $id_gudang_dari = $getData[0]['id_gudang'];
      $id_costcenter  = $getData[0]['id_costcenter'];
      $nm_costcenter  = strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $getData[0]['id_costcenter']));
      $id_gudang_ke  = get_name('warehouse', 'id', 'kd_gudang', $getData[0]['id_costcenter']);

      // exit;

      $this->db->trans_start();

      $this->db->select('a.id_gudang as id_gudang_dari,a.id_costcenter as id_gudang_ke, c.code_material as id_material, IF(c.weight IS NULL, 0, c.weight) as qty');
      $this->db->from('so_internal_spk a');
      $this->db->join('so_internal_spk_mixing b', 'b.kode_det = a.kode_det', 'left');
      $this->db->join('so_internal_spk_mixing_material c', 'c.id_mixing = b.id', 'left');
      $this->db->group_by('c.code_material');
      $this->db->where('a.id', $id);
      $get_material_mixing = $this->db->get()->result();

      // print_r($get_material_mixing);
      // exit;

      foreach ($get_material_mixing as $item_mixing) {
        // $get_gudang_dari = $this->db->get_where('warehouse', ['id' => $item_mixing->id_gudang_dari])->row();
        $get_gudang_ke = $this->db->get_where('warehouse', ['kd_gudang' => $item_mixing->id_gudang_ke])->row();

        $get_stok_gudang = $this->db->get_where('warehouse_stock', ['id_gudang' => $item_mixing->id_gudang_dari, 'id_material' => $item_mixing->id_material])->row();

        $get_stok_costcenter = $this->db->get_where('warehouse_stock', ['id_gudang' => $get_gudang_ke->id, 'id_material' => $item_mixing->id_material])->row();


        $stock_gudang = 0;
        $stock_costcenter = 0;
        if (!empty($get_stok_gudang)) {
          $stock_gudang = $get_stok_gudang->qty_stock;
        }
        if (!empty($get_stok_costcenter)) {
          $stock_costcenter = $get_stok_costcenter->qty_stock;
        }

        $get_material = $this->db->get_where('new_inventory_4', ['code_lv4' => $item_mixing->id_material])->row();
      }


      if (!empty($ArrInsert)) {
        $this->db->insert_batch('so_internal_spk_material_pengeluaran', $ArrInsert);
      }

      $this->db->where('id', $id);
      $this->db->update('so_internal_spk', $ArrUpdate);

      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE || $valid_qty_stock < 1) {
        $this->db->trans_rollback();
        $msg = 'Save gagal disimpan ...';

        if ($valid_qty_stock < 1) {
          $msg = 'Maaf, stock anda kurang dari yang diminta !';
        }

        $Arr_Data  = array(
          'pesan'    => $msg,
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1
        );

        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Pengeluaran subgudang request list : " . $id);
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
      $getMaterialMixing  = $this->db->select('id, code_material, SUM(weight) AS berat')->group_by('code_material')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();

      $data = [
        'getData' => $getData,
        'kode' => $kode,
        'qty' => $qty,
        'getMaterialMixing' => $getMaterialMixing,
        'GET_STOK' => getStokMaterial($id_gudang),
        'GET_MATERIAL' => get_inventory_lv4()
      ];
      $this->template->title('Request Mixing');
      $this->template->render('add_old', $data);
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
      ->get_where('so_internal_spk_material a', array('a.type_name' => 'mixing', 'b.gudang' => 'subgudang'))->result_array();

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

  public function print_spk_confirm()
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
      ->select('a.id, b.id AS id2, b.code_material, b.weight AS berat_req, b.weight_aktual AS berat_act, b.weight_confirm AS berat_con')
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
    $this->load->view('print_spk_confirm', $data);
  }

  public function add_confirm($id = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id        = $data['id_uniq'];
      $Detail    = $data['detail'];

      $ArrInsert = [];
      $ArrStock = [];
      foreach ($Detail as $key => $value) {
        $ArrInsert[$key]['id'] = $value['id'];
        $ArrInsert[$key]['code_material_confirm'] = $value['code_material_aktual'];
        $ArrInsert[$key]['weight_confirm'] = str_replace(',', '', $value['berat_aktual']);
        $ArrInsert[$key]['confirm_by'] = $this->id_user;
        $ArrInsert[$key]['confirm_date'] = $this->datetime;

        $ArrStock[$key]['id'] = $value['code_material'];
        $ArrStock[$key]['qty'] = str_replace(',', '', $value['berat_aktual']);
      }

      $ArrUpdate = [
        'sts_produksi' => 'Y',
        'produksi_confirm_by' => $this->id_user,
        'produksi_confirm_date' => $this->datetime
      ];

      $getData = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
      // print_r($getData);
      $kode_trans = $getData[0]['kode_det'];
      $id_gudang_dari = $getData[0]['id_gudang'];
      $id_gudang_ke = 3;

      // exit;

      $this->db->trans_start();
      if (!empty($ArrInsert)) {
        $this->db->update_batch('so_internal_spk_material_pengeluaran', $ArrInsert, 'id');

        // $id_gudang_dari = 17;
        // $id_gudang_ke = $id_costcenter;

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
          $get_material = $this->db->select('a.nama as nm_material, a.code as kode_material')
            ->from('new_inventory_4 a')
            ->where('a.code_lv4', $item_detail->id_material)
            ->get()
            ->row();

          if (!empty($get_material)) {
            $nm_material = $get_material->nm_material;
            $kode_material = $get_material->kode_material;
          }

          $nm_gudang_dari = '';
          $get_gudang_dari = $this->db->select('a.nm_gudang as nm_gudang_dari')
            ->from('warehouse a')
            ->where('a.id', $id_gudang_dari)
            ->get()
            ->row();

          if (!empty($get_gudang_dari)) {
            $nm_gudang_dari = $get_gudang_dari->nm_gudang_dari;
          }

          $nm_gudang_ke = '';
          $get_gudang_ke = $this->db->select('a.nm_gudang as nm_gudang_ke')
            ->from('warehouse a')
            ->where('a.id', $id_gudang_ke)
            ->get()
            ->row();

          if (!empty($get_gudang_ke)) {
            $nm_gudang_ke = $get_gudang_ke->nm_gudang_ke;
          }

          $insert_costbook = $this->db->insert('tr_cost_book', [
            'id' => $id_costbook,
            'id_material' => $item_detail->id_material,
            'nm_material' => $nm_material,
            'kode_produk' => $kode_material,
            'tipe_material' => 'material',
            'id_gudang_dari' => $id_gudang_dari,
            'nm_gudang_dari' => $nm_gudang_dari,
            'id_gudang_ke' => $id_gudang_ke,
            'nm_gudang_ke' => $nm_gudang_ke,
            'tgl' => date('Y-m-d'),
            'no_transaksi' => $kode_trans,
            'jenis_transaksi' => 'Out ke Gd. Produksi',
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
            'nm_gudang_dari' => $nm_gudang_dari,
            'id_gudang_ke' => $id_gudang_ke,
            'nm_gudang_ke' => $nm_gudang_ke,
            'tgl' => date('Y-m-d'),
            'no_transaksi' => $kode_trans,
            'jenis_transaksi' => 'In dari ' . $nm_gudang_dari,
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
          $wgere = ['subgudang', 'stok', 'produksi'];

          $get_sub_prod_warehouse = $this->db->query("SELECT id, `desc` FROM warehouse WHERE `desc` IN ('subgudang', 'stok', 'produksi')")->result();
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
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Confirm produksi request : " . $id);
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
      $getMaterialMixing  = $this->db
        ->select('a.id, b.id AS id2, b.code_material, b.weight_aktual AS berat')
        ->group_by('a.code_material')->where('a.kode_det', $kode)
        ->join('so_internal_spk_material_pengeluaran b', 'a.id=b.id_det_spk')
        ->get_where('so_internal_spk_material a', array('a.type_name !=' => 'mixing', 'b.gudang' => 'produksi'))->result_array();

      $data = [
        'getData' => $getData,
        'kode' => $kode,
        'qty' => $qty,
        'getMaterialMixing' => $getMaterialMixing,
        'GET_STOK' => getStokMaterial($id_gudang),
        'GET_MATERIAL' => get_inventory_lv4()
      ];
      $this->template->title('Confirm Request List');
      $this->template->render('add_confirm', $data);
    }
  }

  public function data_side_request_material()
  {
    $this->subgudang_request_list_model->data_side_request_material();
  }

  public function modal_request_edit()
  {
    if ($this->input->post()) {
      $data       = $this->input->post();
      $data_session  = $this->session->userdata;

      $kode_trans  = $data['kode_trans'];
      $id_gudang_dari  = $data['id_gudang_dari'];
      $id_gudang_ke  = $data['id_gudang_ke'];
      // print_r($data);
      // exit;
      $GET_DETAIL_MAT = get_inventory_lv4();

      $ArrInsertDetail   = array();
      $ArrStock = [];
      $SUM_MAT = 0;
      $SUM_PACK = 0;

      $valid_qty_stock = 1;

      if (!empty($data['detail'])) {
        foreach ($data['detail'] as $val => $valx) {
          $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi'])) ? $GET_DETAIL_MAT[$valx['id_material']]['konversi'] : 0;
          $qty_packing   = str_replace(',', '', $valx['edit_qty']);
          if ($qty_packing > 0) {
            $qty_berat = $qty_packing * $konversi;

            $SUM_MAT  += $qty_berat;
            $SUM_PACK += $qty_packing;
            //detail adjustmeny
            $ArrInsertDetail[$val]['id']             = $valx['id'];
            $ArrInsertDetail[$val]['check_qty_oke']       = $qty_berat;
            $ArrInsertDetail[$val]['check_keterangan']     = strtolower($valx['keterangan']);

            $ArrStock[$val]['id'] = $valx['id_material'];
            $ArrStock[$val]['qty'] = $qty_berat;

            if ($valid_qty_stock == 1) {
              if ($valx['stok'] < $qty_berat) {
                $valid_qty_stock = 0;
              }
            }
          }
        }
      }

      $ArrInsert = array(
        'checked' => 'Y',
        'checked_by'       => $this->id_user,
        'checked_date'     => $this->datetime
      );

      // print_r($ArrInsert);
      // print_r($ArrInsertDetail);
      // print_r($ArrStock);
      // exit;

      // exit;
      $this->db->trans_start();
      $this->db->where('kode_trans', $kode_trans);
      $this->db->update('warehouse_adjustment', $ArrInsert);

      if (!empty($ArrInsertDetail)) {
        $this->db->update_batch('warehouse_adjustment_detail', $ArrInsertDetail, 'id');
      }
      $this->db->trans_complete();


      if ($this->db->trans_status() === FALSE || $valid_qty_stock < 1) {
        $this->db->trans_rollback();

        $msg = 'Save process failed. Please try again later ...';
        if ($valid_qty_stock < 1) {
          $msg = 'Sorry, your stock is less than the request !';
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
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Confirm request material (gudang produksi) : " . $kode_trans);
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
        'GET_MATERIAL' => get_inventory_lv4(),
        'GET_SATUAN' => get_list_satuan(),
        'kode' => $kode_trans,
        'GET_STOK' => getStokMaterial(2),
        'GET_STOK_TO' => getStokMaterial($getData[0]['id_gudang_ke']),
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
      'GET_MATERIAL' => get_inventory_lv4(),
      'GET_SATUAN' => get_list_satuan(),
      'kode' => $kode_trans
    );

    history('Print spk pengeluaran material ' . $kode_trans);
    $this->load->view('print_spk_request', $data);
  }

  public function getChangeMaterialMixing()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id        = $data['id'];
    $kode    = $data['kode'];

    $getMaterialMixing  = $this->db->select('*')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();
    $ArrayID = [];
    $ArrayIDData = [];
    foreach ($getMaterialMixing as $key => $value) {
      $ArrayID[] = $value['id'];

      $ArrayIDData[$key]['id_det_spk'] = $value['id'];
      $ArrayIDData[$key]['weight'] = $value['weight'];
    }

    $ArrInputMaterial = [];

    $getdata = $this->db->where_in('id_det_spk', $ArrayID)->get_where('so_internal_spk_material_pengeluaran', array('qty_ke' => $id))->result_array();
    foreach ($getdata as $key => $value) {
      $ArrInputMaterial[$key]['id_det_spk'] = $value['id_det_spk'];
      $ArrInputMaterial[$key]['qty_ke'] = $id;
      $ArrInputMaterial[$key]['weight_aktual'] = $value['weight_aktual'];
      $ArrInputMaterial[$key]['created_date'] = $value['created_date'];
      $ArrInputMaterial[$key]['close'] = (!empty($value['close_date'])) ? 'Y' : 'N';
    }


    $Arr_Data  = array(
      'arrayData'    => (!empty($ArrInputMaterial)) ? $ArrInputMaterial : 0,
      'ArrayIDData'    => $ArrayIDData,
    );
    echo json_encode($Arr_Data);
  }

  public function add($id = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id        = $data['id_uniq'];
      $qty_ke    = $data['qty_ke'];
      $Detail    = $data['detail'];
      $id        = $data['id'];
      $check_close        = (!empty($data['check_close'])) ? $data['check_close'] : 0;

      $ArrInsert = [];
      $ArrStock = [];
      $ArrStockBack = [];
      $ArrayID = [];
      foreach ($Detail as $key => $value) {
        $ArrayID[] = $value['id'];
        $ArrInsert[$key]['id_det_spk'] = $value['id'];
        $ArrInsert[$key]['qty_ke'] = $qty_ke;
        $ArrInsert[$key]['code_material'] = $value['code_material'];
        $ArrInsert[$key]['weight'] = str_replace(',', '', $value['berat']);
        $ArrInsert[$key]['code_material_aktual'] = $value['code_material_aktual'];
        $ArrInsert[$key]['weight_aktual'] = str_replace(',', '', $value['berat_aktual']);
        $ArrInsert[$key]['created_by'] = $this->id_user;
        $ArrInsert[$key]['created_date'] = $this->datetime;
        $ArrInsert[$key]['gudang'] = 'subgudang';

        $ArrStock[$key]['id'] = $value['code_material'];
        $ArrStock[$key]['qty'] = str_replace(',', '', $value['berat_aktual']);
      }

      //STockPlus
      if (!empty($ArrayID)) {
        $getdata = $this->db->where_in('id_det_spk', $ArrayID)->get_where('so_internal_spk_material_pengeluaran', array('qty_ke' => $qty_ke))->result_array();
        foreach ($getdata as $key => $value) {
          $ArrStockBack[$key]['id']   = $value['code_material_aktual'];
          $ArrStockBack[$key]['qty']  = str_replace(',', '', $value['weight_aktual']);
        }
      }

      $ArrUpdate = [
        'sts_subgudang' => ($check_close == '0') ? 'P' : 'Y',
        'subgudang_by' => $this->id_user,
        'subgudang_date' => $this->datetime
      ];

      $getData = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
      // print_r($getData);
      $kode_trans = $getData[0]['kode_det'] . '/' . $qty_ke;
      $id_gudang_dari = $getData[0]['id_gudang'];
      $id_costcenter  = $getData[0]['id_costcenter'];
      $nm_costcenter  = strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $getData[0]['id_costcenter']));
      $id_gudang_ke  = get_name('warehouse', 'id', 'kd_gudang', $getData[0]['id_costcenter']);

      // exit;

      $this->db->trans_start();
      if (!empty($ArrInsert)) {
        $this->db->where_in('id_det_spk', $ArrayID);
        $this->db->where('qty_ke', $qty_ke);
        $this->db->delete('so_internal_spk_material_pengeluaran');

        $this->db->insert_batch('so_internal_spk_material_pengeluaran', $ArrInsert);
      }

      $this->db->where('id', $id);
      $this->db->update('so_internal_spk', $ArrUpdate);

      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save gagal disimpan ...',
          'status'  => 0,
          'id' => $id,
          'check_close' => $check_close
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1,
          'id' => $id,
          'check_close' => $check_close
        );
        if (!empty($ArrStockBack)) {
          move_warehouse($ArrStockBack, $id_gudang_ke, $id_gudang_dari, $kode_trans, null);
        }
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Pengeluaran subgudang request list : " . $id);
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
      $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();

      $data = [
        'getData' => $getData,
        'kode' => $kode,
        'qty' => $qty,
        'id' => $id,
        'getMaterialMixing' => $getMaterialMixing,
        'GET_STOK' => getStokMaterial($id_gudang),
        'GET_MATERIAL' => get_inventory_lv4()
      ];
      $this->template->title('Input Aktual Produksi');
      $this->template->render('add', $data);
    }
  }

  public function data_side_request_material_ftackle()
  {
    $this->subgudang_request_list_model->data_side_request_material_ftackle();
  }

  public function modal_request_edit_ftackle()
  {
    if ($this->input->post()) {
      $data       = $this->input->post();
      $data_session  = $this->session->userdata;

      $kode_trans  = $data['kode_trans'];
      $id_gudang_dari  = $data['id_gudang_dari'];
      $id_gudang_ke  = $data['id_gudang_ke'];
      // print_r($data);
      // exit;
      $GET_DETAIL_MAT = get_inventory_lv4();

      $ArrInsertDetail   = array();
      $ArrUpdateRequest   = array();
      $ArrStock = [];
      $SUM_MAT = 0;
      $SUM_PACK = 0;

      $valid_qty_stock = 1;

      if (!empty($data['detail'])) {
        foreach ($data['detail'] as $val => $valx) {
          $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi'])) ? $GET_DETAIL_MAT[$valx['id_material']]['konversi'] : 0;
          $qty_packing   = str_replace(',', '', $valx['edit_qty']);
          if ($qty_packing > 0) {
            $qty_berat = $qty_packing;

            $SUM_MAT  += $qty_berat;
            $SUM_PACK += $qty_packing;
            //detail adjustmeny
            $ArrInsertDetail[$val]['id']                   = $valx['id'];
            $ArrInsertDetail[$val]['check_qty_oke']       = $qty_berat;
            $ArrInsertDetail[$val]['check_keterangan']     = strtolower($valx['keterangan']);

            $getRequest = $this->db->get_where('so_internal_spk_material', array('id' => $valx['no_ipp']))->result_array();
            $qtyReq     = (!empty($getRequest[0]['request'])) ? $getRequest[0]['request'] : 0;
            $ArrUpdateRequest[$val]['id']       = $valx['no_ipp'];
            $ArrUpdateRequest[$val]['request']   = $qtyReq - $valx['request'] + $qty_berat;

            $ArrStock[$val]['id'] = $valx['id_material'];
            $ArrStock[$val]['qty'] = $qty_berat;

            if ($valid_qty_stock == 1) {
              if ($valx['stok'] < $qty_berat) {
                $valid_qty_stock = 0;
              }
            }
          }
        }
      }

      $ArrInsert = array(
        'checked' => 'Y',
        'checked_by'       => $this->id_user,
        'checked_date'     => $this->datetime
      );

      // print_r($ArrInsert);
      // print_r($ArrInsertDetail);
      // print_r($ArrStock);
      // exit;

      // exit;
      $this->db->trans_start();
      $this->db->where('kode_trans', $kode_trans);
      $this->db->update('warehouse_adjustment', $ArrInsert);

      if (!empty($ArrInsertDetail)) {
        $this->db->update_batch('warehouse_adjustment_detail', $ArrInsertDetail, 'id');
      }

      if (!empty($ArrUpdateRequest)) {
        $this->db->update_batch('so_internal_spk_material', $ArrUpdateRequest, 'id');
      }
      $this->db->trans_complete();


      if ($this->db->trans_status() === FALSE || $valid_qty_stock < 1) {
        $this->db->trans_rollback();

        $msg = 'Save process failed. Please try again later ...';
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
          'pesan'    => 'Save process success. Thanks ...',
          'status'  => 1
        );
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Confirm request material (gudang produksi) : " . $kode_trans);
      }
      echo json_encode($Arr_Data);
    } else {
      $kode_trans = $this->uri->segment(3);
      $tanda      = $this->uri->segment(4);

      $getData        = $this->db->get_where('warehouse_adjustment a', array('a.kode_trans' => $kode_trans))->result_array();
      if ($getData[0]['checked'] == 'Y') {
        $tanda      = 'detail';
      }
      $getDataDetail  = $this->db->select('a.*')->join('new_inventory_4 b', 'a.id_material=b.code_lv4')->get_where('warehouse_adjustment_detail a', array('a.kode_trans' => $kode_trans, 'b.code_lv1 <>' => 'M123000003'))->result_array();
      $getDataDetailMix  = $this->db->select('a.*')->join('new_inventory_4 b', 'a.id_material=b.code_lv4')->get_where('warehouse_adjustment_detail a', array('a.kode_trans' => $kode_trans, 'b.code_lv1' => 'M123000003'))->result_array();

      $data = array(
        'tanda' => $tanda,
        'getData' => $getData,
        'getDataDetail' => $getDataDetail,
        'getDataDetailMix' => $getDataDetailMix,
        'GET_MATERIAL' => get_inventory_lv4(),
        'GET_SATUAN' => get_list_satuan(),
        'kode' => $kode_trans,
        'GET_STOK' => getStokMaterial($getData[0]['id_gudang_dari']),
        'GET_STOK_TO' => getStokMaterial($getData[0]['id_gudang_ke']),
      );

      $this->load->view('modal_request_edit_ftackle', $data);
    }
  }

  public function print_spk_request_ftackle()
  {
    $kode_trans  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = get_name('users', 'nm_lengkap', 'id_user', $session['id_user']);

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $getData = $this->db
      ->select('a.*, b.no_spk, d.nama AS nama_product')
      ->join('so_internal_spk b', 'a.no_ipp=b.id', 'left')
      ->join('so_internal c', 'b.id_so=c.id', 'left')
      ->join('new_inventory_4 d', 'c.code_lv4=d.code_lv4', 'left')
      ->get_where('warehouse_adjustment a', array(
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
      'GET_MATERIAL' => get_inventory_lv4(),
      'GET_SATUAN' => get_list_satuan(),
      'kode' => $kode_trans
    );

    history('Print spk pengeluaran material ftackle ' . $kode_trans);
    $this->load->view('print_spk_request_ftackle', $data);
  }

  //material cutting
  public function data_side_request_material_cutting()
  {
    $this->subgudang_request_list_model->data_side_request_material_cutting();
  }

  public function modal_request_edit_cutting()
  {
    if ($this->input->post()) {
      $data       = $this->input->post();
      $data_session  = $this->session->userdata;

      $kode_trans  = $data['kode_trans'];
      $id_gudang_dari  = $data['id_gudang_dari'];
      $id_gudang_ke  = $data['id_gudang_ke'];
      // print_r($data);
      // exit;
      $GET_DETAIL_MAT = get_inventory_lv4();

      $ArrInsertDetail   = array();
      $ArrUpdateRequest   = array();
      $ArrStock = [];
      $SUM_MAT = 0;
      $SUM_PACK = 0;
      if (!empty($data['detail'])) {
        foreach ($data['detail'] as $val => $valx) {
          $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi'])) ? $GET_DETAIL_MAT[$valx['id_material']]['konversi'] : 0;
          $qty_packing   = str_replace(',', '', $valx['edit_qty']);
          if ($qty_packing > 0) {
            $qty_berat = $qty_packing;

            $SUM_MAT  += $qty_berat;
            $SUM_PACK += $qty_packing;
            //detail adjustmeny
            $ArrInsertDetail[$val]['id']                   = $valx['id'];
            $ArrInsertDetail[$val]['check_qty_oke']       = $qty_berat;
            $ArrInsertDetail[$val]['check_keterangan']     = strtolower($valx['keterangan']);

            $getRequest = $this->db->get_where('so_spk_cutting_material_request', array('id' => $valx['no_ipp']))->result_array();
            $qtyReq     = (!empty($getRequest[0]['request'])) ? $getRequest[0]['request'] : 0;
            $ArrUpdateRequest[$val]['id']       = $valx['no_ipp'];
            $ArrUpdateRequest[$val]['request']   = $qtyReq - $valx['request'] + $qty_berat;

            $ArrStock[$val]['id'] = $valx['id_material'];
            $ArrStock[$val]['qty'] = $qty_berat;
          }
        }
      }

      $ArrInsert = array(
        'checked' => 'Y',
        'checked_by'       => $this->id_user,
        'checked_date'     => $this->datetime
      );

      // print_r($ArrInsert);
      // print_r($ArrInsertDetail);
      // print_r($ArrStock);
      // exit;

      // exit;
      $this->db->trans_start();
      $this->db->where('kode_trans', $kode_trans);
      $this->db->update('warehouse_adjustment', $ArrInsert);

      if (!empty($ArrInsertDetail)) {
        $this->db->update_batch('warehouse_adjustment_detail', $ArrInsertDetail, 'id');
      }

      if (!empty($ArrUpdateRequest)) {
        $this->db->update_batch('so_spk_cutting_material_request', $ArrUpdateRequest, 'id');
      }
      $this->db->trans_complete();


      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save process failed. Please try again later ...',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save process success. Thanks ...',
          'status'  => 1
        );
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Confirm request material cutting (gudang produksi) : " . $kode_trans);
      }
      echo json_encode($Arr_Data);
    } else {
      $kode_trans = $this->uri->segment(3);
      $tanda      = $this->uri->segment(4);

      $getData        = $this->db->get_where('warehouse_adjustment a', array('a.kode_trans' => $kode_trans))->result_array();
      if ($getData[0]['checked'] == 'Y') {
        $tanda      = 'detail';
      }
      $getDataDetail  = $this->db->select('a.*')->join('new_inventory_4 b', 'a.id_material=b.code_lv4')->get_where('warehouse_adjustment_detail a', array('a.kode_trans' => $kode_trans, 'b.code_lv1 <>' => 'M123000003'))->result_array();
      $getDataDetailMix  = $this->db->select('a.*')->join('new_inventory_4 b', 'a.id_material=b.code_lv4')->get_where('warehouse_adjustment_detail a', array('a.kode_trans' => $kode_trans, 'b.code_lv1' => 'M123000003'))->result_array();

      $data = array(
        'tanda' => $tanda,
        'getData' => $getData,
        'getDataDetail' => $getDataDetail,
        'getDataDetailMix' => $getDataDetailMix,
        'GET_MATERIAL' => get_inventory_lv4(),
        'GET_SATUAN' => get_list_satuan(),
        'kode' => $kode_trans,
        'GET_STOK' => getStokMaterial($getData[0]['id_gudang_dari']),
        'GET_STOK_TO' => getStokMaterial($getData[0]['id_gudang_ke'])
      );

      $this->load->view('modal_request_edit_cutting', $data);
    }
  }

  public function print_spk_request_cutting()
  {
    $kode_trans  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = get_name('users', 'nm_lengkap', 'id_user', $session['id_user']);

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $getData = $this->db
      ->select('a.*, b.no_spk, d.nama AS nama_product')
      ->join('so_spk_cutting_request b', 'a.no_ipp=b.id', 'left')
      ->join('so_spk_cutting c', 'b.id_so=c.id', 'left')
      ->join('new_inventory_4 d', 'c.code_lv4=d.code_lv4', 'left')
      ->get_where('warehouse_adjustment a', array(
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
      'GET_MATERIAL' => get_inventory_lv4(),
      'GET_SATUAN' => get_list_satuan(),
      'kode' => $kode_trans
    );

    history('Print spk pengeluaran material cutting ' . $kode_trans);
    $this->load->view('print_spk_request_cutting', $data);
  }

  //material assembly
  public function data_side_request_material_assembly()
  {
    $this->subgudang_request_list_model->data_side_request_material_assembly();
  }

  public function modal_request_edit_assembly()
  {
    if ($this->input->post()) {
      $data       = $this->input->post();
      $data_session  = $this->session->userdata;

      $kode_trans  = $data['kode_trans'];
      $id_gudang_dari  = $data['id_gudang_dari'];
      $id_gudang_ke  = $data['id_gudang_ke'];
      // print_r($data);
      // exit;
      $GET_DETAIL_MAT = get_inventory_lv4();

      $ArrInsertDetail   = array();
      $ArrUpdateRequest   = array();
      $ArrStock = [];
      $SUM_MAT = 0;
      $SUM_PACK = 0;
      if (!empty($data['detail'])) {
        foreach ($data['detail'] as $val => $valx) {
          $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi'])) ? $GET_DETAIL_MAT[$valx['id_material']]['konversi'] : 0;
          $qty_packing   = str_replace(',', '', $valx['edit_qty']);
          if ($qty_packing > 0) {
            $qty_berat = $qty_packing;

            $SUM_MAT  += $qty_berat;
            $SUM_PACK += $qty_packing;
            //detail adjustmeny
            $ArrInsertDetail[$val]['id']                   = $valx['id'];
            $ArrInsertDetail[$val]['check_qty_oke']       = $qty_berat;
            $ArrInsertDetail[$val]['check_keterangan']     = strtolower($valx['keterangan']);

            $getRequest = $this->db->get_where('so_spk_assembly_material', array('id' => $valx['no_ipp']))->result_array();
            $qtyReq     = (!empty($getRequest[0]['request'])) ? $getRequest[0]['request'] : 0;
            $ArrUpdateRequest[$val]['id']       = $valx['no_ipp'];
            $ArrUpdateRequest[$val]['request']   = $qtyReq - $valx['request'] + $qty_berat;

            $ArrStock[$val]['id'] = $valx['id_material'];
            $ArrStock[$val]['qty'] = $qty_berat;
          }
        }
      }

      $ArrInsert = array(
        'checked' => 'Y',
        'checked_by'       => $this->id_user,
        'checked_date'     => $this->datetime
      );

      // print_r($ArrInsert);
      // print_r($ArrInsertDetail);
      // print_r($ArrStock);
      // exit;

      // exit;
      $this->db->trans_start();
      $this->db->where('kode_trans', $kode_trans);
      $this->db->update('warehouse_adjustment', $ArrInsert);

      if (!empty($ArrInsertDetail)) {
        $this->db->update_batch('warehouse_adjustment_detail', $ArrInsertDetail, 'id');
      }

      if (!empty($ArrUpdateRequest)) {
        $this->db->update_batch('so_spk_assembly_material', $ArrUpdateRequest, 'id');
      }
      $this->db->trans_complete();


      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save process failed. Please try again later ...',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save process success. Thanks ...',
          'status'  => 1
        );
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, null);
        history("Confirm request material assembly (gudang produksi) : " . $kode_trans);
      }
      echo json_encode($Arr_Data);
    } else {
      $kode_trans = $this->uri->segment(3);
      $tanda      = $this->uri->segment(4);

      $getData        = $this->db->get_where('warehouse_adjustment a', array('a.kode_trans' => $kode_trans))->result_array();
      if ($getData[0]['checked'] == 'Y') {
        $tanda      = 'detail';
      }
      $getDataDetailMix  = $this->db->select('a.*')->get_where('warehouse_adjustment_detail a', array('a.kode_trans' => $kode_trans))->result_array();

      $data = array(
        'tanda' => $tanda,
        'getData' => $getData,
        'getDataDetailMix' => $getDataDetailMix,
        'GET_MATERIAL' => get_inventory_lv4(),
        'GET_SATUAN' => get_list_satuan(),
        'kode' => $kode_trans,
        'GET_STOK' => getStokMaterial($getData[0]['id_gudang_dari']),
        'GET_STOK_TO' => getStokMaterial($getData[0]['id_gudang_ke'])
      );

      $this->load->view('modal_request_edit_assembly', $data);
    }
  }

  public function print_spk_request_assembly()
  {
    $kode_trans  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = get_name('users', 'nm_lengkap', 'id_user', $session['id_user']);

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $getData = $this->db
      ->select('a.*, b.no_spk, d.nama AS nama_product')
      ->join('so_spk_assembly b', 'a.no_ipp=b.id', 'left')
      ->join('bom_header c', 'b.no_bom=c.no_bom', 'left')
      ->join('new_inventory_4 d', 'c.id_product=d.code_lv4', 'left')
      ->get_where('warehouse_adjustment a', array(
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
      'GET_MATERIAL' => get_inventory_lv4(),
      'GET_SATUAN' => get_list_satuan(),
      'kode' => $kode_trans
    );

    history('Print spk pengeluaran material assembly ' . $kode_trans);
    $this->load->view('print_spk_request_assembly', $data);
  }

}
