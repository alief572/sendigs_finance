<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Pusat_request_list extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Request_List_Pusat.View';
  protected $addPermission    = 'Request_List_Pusat.Add';
  protected $managePermission = 'Request_List_Pusat.Manage';
  protected $deletePermission = 'Request_List_Pusat.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
    $this->load->model(array(
      'Pusat_request_list/pusat_request_list_model'
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

    history("View data gudang pusat request list");
    $this->template->title('Gudang Material / Gudang Pusat / Outgoing');
    $this->template->render('index');
  }

  public function data_side_request_material()
  {
    $this->pusat_request_list_model->data_side_request_material();
  }

  public function modal_request_edit()
  {
    if ($this->input->post()) {
      $data       = $this->input->post();
      $data_session  = $this->session->userdata;

      $kode_trans  = $data['kode_trans'];
      $id_gudang_dari  = $data['id_gudang_dari'];
      $id_gudang_ke  = $data['id_gudang_ke'];

      $this->db->trans_start();

      $get_nm_gudang_dari = $this->db->select('kd_gudang')->get_where('warehouse', ['id' => $id_gudang_dari])->row_array();
      $get_nm_gudang_ke = $this->db->select('kd_gudang')->get_where('warehouse', ['id' => $id_gudang_ke])->row_array();
      // print_r($data);
      // exit;
      $GET_DETAIL_MAT = get_inventory_lv4();

      $ArrInsertDetail   = array();
      $ArrStock = [];
      $SUM_MAT = 0;
      $SUM_PACK = 0;

      $valid_qty_lot = 1;
      $valid_qty_w_stok = 1;
      if (!empty($data['detail'])) {
        foreach ($data['detail'] as $val => $valx) {
          $konversi     = (!empty($GET_DETAIL_MAT[$valx['id_material']]['konversi'])) ? $GET_DETAIL_MAT[$valx['id_material']]['konversi'] : 0;
          $qty_packing   = str_replace(',', '', $valx['edit_qty']);
          if ($qty_packing > 0) {
            $qty_berat = $qty_packing * $konversi;

            if($valid_qty_w_stok == 1) {
              if($valx['stock'] < $qty_packing) {
                $valid_qty_w_stok = 0;
              }
            }

            $SUM_MAT  += $qty_berat;
            $SUM_PACK += $qty_packing;
            //detail adjustmeny
            $ArrInsertDetail[$val]['id']             = $valx['id'];
            $ArrInsertDetail[$val]['check_qty_oke']       = $qty_berat;
            $ArrInsertDetail[$val]['check_keterangan']     = strtolower($valx['keterangan']);
            $ArrInsertDetail[$val]['id_lot'] = $valx['id_lot'];

            $update_detail = $this->db->update('warehouse_adjustment_detail', [
              'check_qty_oke' => $qty_berat,
              'check_keterangan' => strtolower($valx['keterangan']),
              'id_lot' => $valx['id_lot']
            ], [
              'id' => $valx['id']
            ]);
            if (!$update_detail) {
              $this->db->error($update_detail);
              exit;
            }

            $ArrStock[$val]['id'] = $valx['id_material'];
            $ArrStock[$val]['qty'] = $qty_berat;


            $exp_id_lot = explode(',', $valx['id_lot']);
            foreach ($exp_id_lot as $lot_id) {
              $exp_lot_id = explode('-', $lot_id);

              $get_checked_incoming = $this->db->get_where('tr_checked_incoming_detail', ['id' => $exp_lot_id[0]])->row_array();
              $get_stock = $this->db->select('qty_stock')->get_where('warehouse_stock', [
                'id_material' => $valx['id_material'],
                'id_gudang' => $id_gudang_dari
              ])->row_array();

              if ($valid_qty_lot > 0 && ($get_checked_incoming['qty_oke'] - $get_checked_incoming['qty_used']) < ($exp_lot_id[1] * $konversi) && $valx['id_lot'] !== '') {
                $valid_qty_lot = 0;
              }
            }

            if ($valx['id_lot'] !== '') {
              if ($valid_qty_lot == '1') {
                $exp_id_lot = explode(',', $valx['id_lot']);
                foreach ($exp_id_lot as $lot_id) {
                  $exp_lot_id = explode('-', $lot_id);

                  $get_checked_incoming = $this->db->get_where('tr_checked_incoming_detail', ['id' => $exp_lot_id[0]])->row_array();
                  $get_stock = $this->db->select('qty_stock')->get_where('warehouse_stock', [
                    'id_material' => $valx['id_material'],
                    'id_gudang' => $id_gudang_dari
                  ])->row_array();

                  $this->db->update('tr_checked_incoming_detail', [
                    'qty_used' => ($get_checked_incoming['qty_used'] + ($exp_lot_id[1] * $konversi)),
                  ], [
                    'id' => $valx['id_lot']
                  ]);

                  $this->db->update('warehouse_stock', [
                    'qty_stock' => ($get_stock['qty_stock'] - ($exp_lot_id[1] * $konversi))
                  ], [
                    'id_material' => $valx['id_material'],
                    'id_gudang' => $id_gudang_dari
                  ]);

                  $get_data_material = $this->db->get_where('new_inventory_4', ['code_lv4' => $valx['id_material']])->row_array();
                  
                }
              }
            } else {
              $get_stock = $this->db->select('qty_stock')->get_where('warehouse_stock', [
                'id_material' => $valx['id_material'],
                'id_gudang' => $id_gudang_dari
              ])->row_array();

              if ($get_stock['qty_stock'] > ($qty_packing * $konversi)) {
                $get_data_material = $this->db->get_where('new_inventory_4', ['code_lv4' => $valx['id_material']])->row_array();
                

                $update_stock = $this->db->update('warehouse_stock', [
                  'qty_stock' => ($get_stock['qty_stock'] - ($exp_lot_id[1] * $konversi))
                ], [
                  'id_material' => $valx['id_material'],
                  'id_gudang' => $id_gudang_dari
                ]);

                if (!$update_stock) {
                  print_r($this->db->error($update_stock));
                  exit;
                }
              } else {
                $valid_qty_lot = 0;
              }
            }



            // $this->db->insert('warehouse_history', [
            //   'id_material' => $valx['id_material'],
            //   'nm_material' => $get_checked_incoming['nm_material'],
            //   'id_gudang' => $id_gudang_dari,
            //   'kd_gudang' => $get_nm_gudang_dari['kd_gudang'],
            //   'id_gudang_dari' => $id_gudang_dari,
            //   'kd_gudang_dari' => $get_nm_gudang_dari['kd_gudang'],
            //   'id_gudang_ke' => $id_gudang_ke,
            //   'kd_gudang_ke' => $get_nm_gudang_ke['kd_gudang'],
            //   'qty_stock_awal' => $get_stock['qty_stock'],
            //   'qty_stock_akhir' => ($get_stock['qty_stock'] - $qty_berat),
            //   'no_ipp' => $kode_trans,
            //   'ket' => 'pengurangan gudang'
            // ]);

            // $this->db->insert('warehouse_history', [
            //   'id_material' => $valx['id_material'],
            //   'nm_material' => $get_checked_incoming['nm_material'],
            //   'id_gudang' => $id_gudang_ke,
            //   'kd_gudang' => $get_nm_gudang_ke['kd_gudang'],
            //   'id_gudang_dari' => $id_gudang_ke,
            //   'kd_gudang_dari' => $get_nm_gudang_ke['kd_gudang'],
            //   'id_gudang_ke' => $id_gudang_dari,
            //   'kd_gudang_ke' => $get_nm_gudang_dari['kd_gudang'],
            //   'qty_stock_awal' => $get_stock['qty_stock'],
            //   'qty_stock_akhir' => ($get_stock['qty_stock'] - $qty_berat),
            //   'no_ipp' => $kode_trans,
            //   'ket' => 'pengurangan gudang'
            // ]);




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

      if ($valid_qty_lot > 0) {
        $update_warehouse_adjustment = $this->db->update('warehouse_adjustment', $ArrInsert, ['kode_trans' => $kode_trans]);
        if (!$update_warehouse_adjustment) {
          print_r($this->db->error($update_warehouse_adjustment));
          exit;
        }

        // if (!empty($ArrInsertDetail)) {
        //   $update_warehouse_adjustment_detail = $this->db->update_batch('warehouse_adjustment_detail', $ArrInsertDetail, 'id');
        //   if (!$update_warehouse_adjustment_detail) {
        //     print_r($this->db->error($update_warehouse_adjustment_detail));
        //     exit;
        //   }
        // }
      }

      $get_warehouse = $this->db->get_where('warehouse_adjustment', ['kode_trans' => $kode_trans])->row();
      $nm_gudang_ke = '';
      $get_nm_gudang_ke = $this->db->get_where('warehouse', ['id' => $get_warehouse->id_gudang_ke])->row();
      if (!empty($get_nm_gudang_ke)) {
        $nm_gudang_ke = $get_nm_gudang_ke->nm_gudang;
      }

      $get_warehouse_detail = $this->db->get_where('warehouse_adjustment_detail', ['kode_trans' => $kode_trans])->result();
      foreach ($get_warehouse_detail as $item_detail) {


        $stock_terakhir = 0;
        $get_stock_material = $this->db->get_where('warehouse_stock', ['id_material' => $item_detail->id_material, 'id_gudang' => 1])->row();
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
          ->where('a.id_gudang_ke', 1)
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
            (a.id_gudang_ke = '1' AND a.jenis_transaksi = 'In Pembelian' AND a.costbook > 0) OR 
            (a.jenis_transaksi = 'Costbook Awal' AND a.id_gudang_dari = '1' AND a.costbook > 0)
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

        $insert_costbook = $this->db->insert('tr_cost_book', [
          'id' => $id_costbook,
          'id_material' => $item_detail->id_material,
          'nm_material' => $nm_material,
          'kode_produk' => $kode_material,
          'tipe_material' => 'material',
          'id_gudang_dari' => 1,
          'nm_gudang_dari' => 'GUDANG PUSAT',
          'id_gudang_ke' => $get_warehouse->id_gudang_ke,
          'nm_gudang_ke' => $nm_gudang_ke,
          'tgl' => date('Y-m-d'),
          'no_transaksi' => $kode_trans,
          'jenis_transaksi' => 'Out ke sub',
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
          'tipe_material' => 'material',
          'id_gudang_dari' => 1,
          'nm_gudang_dari' => 'GUDANG PUSAT',
          'id_gudang_ke' => $get_warehouse->id_gudang_ke,
          'nm_gudang_ke' => $nm_gudang_ke,
          'tgl' => date('Y-m-d'),
          'no_transaksi' => $kode_trans,
          'jenis_transaksi' => 'In dari Gd. Pusat',
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

        $get_stock_sub = $this->db->get_where('warehouse_stock', ['id_material' => $item_detail->id_material, 'id_gudang' => $id_gudang_ke])->row_array();

        $get_data_material = $this->db->get_where('new_inventory_4', ['code_lv4' => $item_detail->id_material])->row_array();
        

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

        

        $insert_price_book = $this->db->insert('price_book_subgudang', [
          'id_material' => $item_detail->id_material,
          'pusat' => $ttl_qty_pusat,
          'subgudang' => ($ttl_qty_sub + $item_detail->qty_oke),
          'produksi' => $ttl_qty_prod,
          'price_book' => (($value_neraca2 + ($nilai_costbook * $item_detail->qty_oke)) / ($stock_terakhir1 + $item_detail->qty_oke)),
          'status' => 'Y',
          'kode_trans' => $item_detail->kode_trans,
          'updated_by' => $this->auth->user_id(),
          'updated_date' => date('Y-m-d H:i:s')
        ]);
      }
      $this->db->trans_complete();


      if ($this->db->trans_status() === FALSE || $valid_qty_lot < 1 || $valid_qty_w_stok < 0) {
        $this->db->trans_rollback();
        $msg = 'Save process failed. Please try again later ...';
        if ($valid_qty_lot < 1) {
          $msg = 'Sorry, there is an exceeding amount request qty toward Lot Qty or Warehouse stock !';
        }
        if($valid_qty_w_stok < 1) {
          $msg = 'Sorry, your stock is not enough !';
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
        history("Confirm request material (gudang pusat) : " . $kode_trans);
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
        'GET_STOK' => getStokMaterial(1)
      );

      $this->template->set($data);
      $this->template->render('modal_request_edit');
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

    $this->db->select('a.*, b.nm_lengkap as process_by, DATE_FORMAT(a.created_date, "%d %M %Y") as process_date');
    $this->db->from('warehouse_adjustment a');
    $this->db->join('users b', 'b.id_user = a.created_by', 'left');
    $this->db->where('a.kode_trans', $kode_trans);
    $getData = $this->db->get()->result_array();



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

    $this->load->library(array('Mpdf'));
    $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
    $mpdf->SetImportUse();
    $mpdf->RestartDocTemplate();
    $show = $this->template->load_view('print_spk_request', $data);
    $this->mpdf->AddPage('L', 'A4', 'en');
    $footer = 'Printed by : ' . ucfirst(strtolower($this->auth->user_name())) . ', ' . $today . ' / ' . $kode_trans . '';
    // $mpdf->SetWatermarkText('ORI Group');
    $mpdf->showWatermarkText = true;
    $mpdf->SetTitle($kode_trans . "/" . date('ymdhis'));
    $mpdf->AddPage();
    $mpdf->SetFooter($footer);
    $this->mpdf->WriteHTML($show);
    $this->mpdf->Output('Print Outgoing.pdf', 'D');
  }

  public function modal_scan_qr()
  {
    $id_material = $this->input->post('id_material');

    $hasil = '';

    $get_konversi = $this->db->select('konversi')->get_where('new_inventory_4', ['code_lv4' => $id_material])->row_array();

    $get_lot = $this->db
      ->select('a.*, b.nm_lengkap as nm_user')
      ->from('tr_checked_incoming_detail a')
      ->join('users b', 'b.id_user = a.created_by', 'left')
      ->where('a.id_material', $id_material)
      ->where('(a.qty_oke - a.qty_used) >', '0')
      ->get()
      ->result_array();

    // print_r($get_lot);
    // exit;


    $konversi = $get_konversi['konversi'];

    $no = 1;
    foreach ($get_lot as $lot) :
      $hasil .= '<tr>';
      $hasil .= '<td class="text-center">' . $no . '</td>';
      $hasil .= '<td class="text-center">' . date('d F Y', strtotime($lot['created_date'])) . '</td>';
      $hasil .= '<td class="text-center">' . $lot['nm_user'] . '</td>';
      $hasil .= '<td class="text-center">' . number_format($lot['qty_ng']) . '</td>';
      $hasil .= '<td class="text-center">' . number_format($lot['qty_oke'] - $lot['qty_used']) . '</td>';
      $hasil .= '<td class="text-center">' . number_format($konversi) . '</td>';
      $hasil .= '<td class="text-center">' . number_format(($lot['qty_oke'] - $lot['qty_used']) / $konversi, 2) . '</td>';
      $hasil .= '<td class="">' . $lot['lot_description'] . '</td>';
      $hasil .= '<td class="text-center">' . date('d F Y', strtotime($lot['expired_date'])) . '</td>';
      $hasil .= '<td class="text-center"><input type="checkbox" name="check_lot[]" class="check_lot check_lot_' . $lot['id'] . '" id="" value="' . $lot['id'] . '" data-id="' . $lot['id'] . '"></td>';
      $hasil .= '<td class="text-center"><input type="text" class="form-control qty_aktual_input qty_aktual_input_' . $lot['id'] . '" name="qty_aktual_input_' . $lot['id'] . '" id="" readonly></td>';
      $hasil .= '</tr>';

      $no++;
    endforeach;

    echo json_encode(['id_material' => $id_material, 'hasil' => $hasil]);
  }

  public function modal_scan_no_qr()
  {
    $id_material = $this->input->post('id_material');

    $hasil = '';

    $get_konversi = $this->db->select('konversi')->get_where('new_inventory_4', ['code_lv4' => $id_material])->row_array();

    $get_lot = $this->db
      ->select('a.*, b.nm_lengkap as nm_user')
      ->from('tr_checked_incoming_detail a')
      ->join('users b', 'b.id_user = a.created_by', 'left')
      ->where('a.id_material', $id_material)
      ->where('(a.qty_oke - a.qty_used) >', '0')
      ->get()
      ->result_array();

    // print_r($get_lot);
    // exit;


    $konversi = $get_konversi['konversi'];

    $no = 1;
    foreach ($get_lot as $lot) :
      $hasil .= '<tr>';
      $hasil .= '<td class="text-center">' . $no . '</td>';
      $hasil .= '<td class="text-center">' . date('d F Y', strtotime($lot['created_date'])) . '</td>';
      $hasil .= '<td class="text-center">' . $lot['nm_user'] . '</td>';
      $hasil .= '<td class="text-center">' . number_format($lot['qty_ng']) . '</td>';
      $hasil .= '<td class="text-center">' . number_format($lot['qty_oke'] - $lot['qty_used']) . '</td>';
      $hasil .= '<td class="text-center">' . number_format($konversi) . '</td>';
      $hasil .= '<td class="text-center">' . number_format(($lot['qty_oke'] - $lot['qty_used']) / $konversi, 2) . '</td>';
      $hasil .= '<td class="">' . $lot['lot_description'] . '</td>';
      $hasil .= '<td class="text-center">' . date('d F Y', strtotime($lot['expired_date'])) . '</td>';
      $hasil .= '<td class="text-center"><input type="checkbox" name="check_lot[]" class="check_lott check_lot_' . $lot['id'] . '" id="" value="' . $lot['id'] . '" data-id="' . $lot['id'] . '"></td>';
      $hasil .= '<td class="text-center"><input type="text" class="form-control qty_aktual_input qty_aktual_input_' . $lot['id'] . '" name="qty_aktual_input_' . $lot['id'] . '" id="" ></td>';
      $hasil .= '</tr>';

      $no++;
    endforeach;

    echo json_encode(['id_material' => $id_material, 'hasil' => $hasil]);
  }

  public function check_qr()
  {
    $qr_code = $this->input->post('qr_code');
    $id_material = $this->input->post('id_material');

    $explode_qr_code = explode(', ', $qr_code);

    $get_lot = $this->db->get_where('tr_checked_incoming_detail', ['CONCAT(kode_trans,"/",id)' => $explode_qr_code[0]])->row_array();
    $check_material = $this->db->get_where('new_inventory_4', ['code_lv4' => $explode_qr_code[1]])->row_array();
    $id_lot = '';
    $konversi = 1;
    if (!empty($get_lot)) {
      $fetch_id_material = $check_material['code_lv4'];
      if ($id_material !== $fetch_id_material) {
        $valid = 2;
      } else {
        $valid = 1;
      }
    } else {
      $valid = 0;
    }

    $hasil = '';
    if ($valid == 1) {
      $exp_qr_code_id = explode('/', $explode_qr_code[0]);

      $hasil = $exp_qr_code_id[1];
    }

    echo json_encode([
      'valid' => $valid,
      'hasil' => $hasil
    ]);
  }
}
