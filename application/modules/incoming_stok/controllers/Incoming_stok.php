<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Incoming_stok extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Incoming_Stok.View';
  protected $addPermission    = 'Incoming_Stok.Add';
  protected $managePermission = 'Incoming_Stok.Manage';
  protected $deletePermission = 'Incoming_Stok.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Incoming_stok/incoming_stok_model'
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

    history("View data incoming stok");
    $this->template->title('Gudang Stok / Incoming Stok');
    $this->template->render('index');
  }

  public function data_side_request_material()
  {
    $this->incoming_stok_model->data_side_request_material();
  }

  public function request_stok($id = null)
  {
    if ($this->input->post()) {
      $data           = $this->input->post();
      $session        = $this->session->userdata('app_session');
      $no_po          = $data['no_po'];
      $no_po = implode(',', $no_po);
      $id_gudang      = $data['id_gudang'];
      $pic            = $data['pic'];
      $keterangan      = $data['keterangan'];
      $tanggal        = date('Y-m-d', strtotime($data['tanggal']));

      if (!empty($data['Detail'])) {
        $detail = $data['Detail'];
      }
      // exit;
      $kode_trans = generateNoTransaksiLainnya();
      $GET_ACC    = get_accessories();

      $ArrInsertDetail  = array();
      $ArrStock         = [];
      $ArrUpdatePO      = [];
      $SUM_MAT          = 0;
      if (!empty($data['Detail'])) {
        foreach ($detail as $val => $valx) {
          $qty_incoming   = str_replace(',', '', $valx['qty_in']);
          if ($qty_incoming > 0) {
            $SUM_MAT  += $qty_incoming;
            //detail adjustment
            $ArrInsertDetail[$val]['kode_trans']     = $kode_trans;
            $ArrInsertDetail[$val]['no_ipp']         = $valx['id'];
            $ArrInsertDetail[$val]['id_material']   = $valx['id_barang'];
            $ArrInsertDetail[$val]['nm_material']   = $valx['nm_barang'];
            $ArrInsertDetail[$val]['qty_order']         = $valx['qty_po'];
            $ArrInsertDetail[$val]['qty_oke']       = $qty_incoming;
            $ArrInsertDetail[$val]['keterangan']     = $valx['ket'];
            $ArrInsertDetail[$val]['update_by']     = $this->id_user;
            $ArrInsertDetail[$val]['update_date']   = $this->datetime;

            $konversi = (!empty($GET_ACC[$valx['id_barang']]['konversi'])) ? $GET_ACC[$valx['id_barang']]['konversi'] : 1;
            if ($konversi <= 0) {
              $konversi = 1;
            }
            $ArrStock[$val]['id']   = $valx['id_barang'];
            $ArrStock[$val]['qty']  = $qty_incoming * $konversi;

            // print_r($qty_incoming.' - '.$konversi.'<br>');

            $getIncoming  = $this->db->get_where('dt_trans_po', array('id' => $valx['id']))->result_array();
            $qtyIn        = (!empty($getIncoming[0]['qty_in'])) ? $getIncoming[0]['qty_in'] : 0;

            $ArrUpdatePO[$val]['id']       = $valx['id'];
            $ArrUpdatePO[$val]['qty_in']   = $qtyIn + $qty_incoming;

            $value_neraca = 0;
            $get_value_neraca = $this->db->select('a.value_neraca')
              ->from('tr_cost_book a')
              ->where('a.id_material', $valx['id_barang'])
              ->where('a.id_gudang_ke', $id_gudang)
              ->order_by('a.tgl', 'DESC')
              ->get()
              ->row();
            if (!empty($get_value_neraca)) {
              $value_neraca = $get_value_neraca->value_neraca;
            }

            $id_costbook = generate_no_costbook();

            $konversi = 1;
            $get_konversi = $this->db->get_where('accessories', ['id' => $valx['id_barang']])->row();
            if (!empty($get_konversi) && $get_konversi->konversi > 0) {
              $konversi = $get_konversi->konversi;
            }

            $nm_gudang = '';
            $get_nm_gudang = $this->db->get_where('warehouse', ['id' => $id_gudang])->row();
            if (!empty($get_nm_gudang)) {
              $nm_gudang = $get_nm_gudang->nm_gudang;
            }

            $stock_terakhir = 0;
            $get_stock_terakhir = $this->db->get_where('warehouse_stock', ['id_material' => $valx['id_barang'], 'id_gudang' => $id_gudang])->row();
            if (!empty($get_stock_terakhir)) {
              $stock_terakhir = $get_stock_terakhir->qty_stock;
            }

            $nilai_beli = 0;
            $this->db->select('a.hargasatuan, a.persen_disc as item_disc, b.persen_disc as po_disc');
            $this->db->from('dt_trans_po a');
            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
            $this->db->where_in('a.no_po', explode(',', $no_po));
            $get_nilai_beli = $this->db->get()->result();
            foreach ($get_nilai_beli as $item_beli) {
              if ($item_beli->item_disc > 0) {
                $nilai_beli = ($item_beli->hargasatuan - ($item_beli->hargasatuan * $item_beli->item_disc));
              } else {
                $nilai_beli = ($item_beli->hargasatuan - ($item_beli->hargasatuan * $item_beli->po_disc));
              }
            }

            $value_neraca = 0;
            $this->db->select('a.value_neraca');
            $this->db->from('tr_cost_book a');
            $this->db->where('a.id_material', $valx['id_barang']);
            $this->db->where('a.id_gudang_ke', $id_gudang);
            $this->db->order_by('a.created_on', 'desc');
            $get_value_neraca = $this->db->get()->row();
            if (!empty($get_value_neraca)) {
              $value_neraca = $get_value_neraca->value_neraca;
            }

            $nm_stock = '';
            $kode_stock = '';
            $get_stock = $this->db->select('a.id_stock as kode_stock, a.stock_name as nm_stock')
              ->from('accessories a')
              ->where('a.id', $valx['id_barang'])
              ->get()
              ->row();

            if (!empty($get_stock)) {
              $nm_stock = $get_stock->nm_stock;
              $kode_stock = $get_stock->kode_stock;
            }

            $nilai_costbook = (($value_neraca + ($nilai_beli * $qty_incoming)) / (($stock_terakhir / $konversi) + $qty_incoming));

            $insert_costbook = $this->db->insert('tr_cost_book', [
              'id' => $id_costbook,
              'id_material' => $valx['id_barang'],
              'nm_material' => $nm_stock,
              'kode_produk' => $kode_stock,
              'tipe_material' => 'stok',
              'id_gudang_ke' => $id_gudang,
              'nm_gudang_ke' => $nm_gudang,
              'tgl' => date('Y-m-d'),
              'no_transaksi' => $kode_trans,
              'jenis_transaksi' => 'In pembelian',
              'qty_transaksi' => $qty_incoming,
              'qty' => (($stock_terakhir / $konversi) + $qty_incoming),
              'nilai_beli' => $nilai_beli,
              'costbook' => $nilai_costbook,
              'value_transaksi' => ($nilai_beli * $qty_incoming),
              'value_neraca' => ($value_neraca + ($nilai_beli * $qty_incoming)),
              'created_by' => $this->auth->user_id(),
              'created_on' => date('Y-m-d H:i:s')
            ]);
            if (!$insert_costbook) {
              print_r($this->db->error($insert_costbook));
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

            $get_ttl_qty_sub = $this->db->query("SELECT SUM(qty_stock) as ttl_qty_sub FROM warehouse_stock WHERE id_material = '" . $valx['id_barang'] . "' AND id_gudang IN ('" . str_replace(",", "','", implode(',', $arr_warehouse_sub)) . "')")->row();
            if (!empty($get_ttl_qty_sub)) {
              $ttl_qty_sub = $get_ttl_qty_sub->ttl_qty_sub;
            }

            $get_ttl_qty_prod = $this->db->query("SELECT SUM(qty_stock) as ttl_qty_prod FROM warehouse_stock WHERE id_material = '" . $valx['id_barang'] . "' AND id_gudang IN ('" . str_replace(",", "','", implode(',', $arr_warehouse_prod)) . "')")->row();
            if (!empty($get_ttl_qty_prod)) {
              $ttl_qty_prod = $get_ttl_qty_prod->ttl_qty_prod;
            }

            $get_ttl_qty_pusat = $this->db->query("SELECT SUM(qty_stock) as ttl_qty_pusat FROM warehouse_stock WHERE id_material = '" . $valx['id_barang'] . "'  AND id_gudang = '1'")->row();
            if (!empty($get_ttl_qty_pusat)) {
              $ttl_qty_pusat = $get_ttl_qty_pusat->ttl_qty_pusat;
            }

            $insert_price_book = $this->db->insert('price_book', [
              'id_material' => $valx['id_barang'],
              'pusat' => ($ttl_qty_pusat + $qty_incoming),
              'subgudang' => $ttl_qty_sub,
              'produksi' => $ttl_qty_prod,
              'price_book' => $nilai_costbook,
              'status' => 'Y',
              'kode_trans' => $kode_trans,
              'updated_by' => $this->auth->user_id(),
              'updated_date' => date('Y-m-d H:i:s')
            ]);

            $get_stock_barang = $this->db->get_where('warehouse_stock', ['id_material' => $valx['id_barang'], 'id_gudang' => $id_gudang])->row();
            $stock_barang = 0;
            if(!empty($get_stock_barang)) {
              $stock_barang = ($get_stock_barang->qty_stock);
            }
            
          }
        }
      }
      // exit;

      $ArrInsert = array(
        'kode_trans'       => $kode_trans,
        'tanggal'         => $tanggal,
        'no_ipp'           => $no_po,
        'category'         => 'incoming stok',
        'jumlah_mat'       => $SUM_MAT,
        'pic'             => $pic,
        'note'             => $keterangan,
        'kd_gudang_dari'   => 'PURCHASE',
        'id_gudang_ke'     => $id_gudang,
        'kd_gudang_ke'     => strtoupper(get_name('warehouse', 'kd_gudang', 'id', $id_gudang)),
        'created_by'       => $this->id_user,
        'created_date'     => $this->datetime
      );

      // print_r($ArrInsert);
      // print_r($ArrInsertDetail);
      // print_r($ArrUpdatePO);
      // exit;

      $this->db->trans_start();
      if (!empty($ArrInsertDetail)) {
        $this->db->insert('warehouse_adjustment', $ArrInsert);
        $this->db->insert_batch('warehouse_adjustment_detail', $ArrInsertDetail);
      }
      if (!empty($ArrUpdatePO)) {
        $this->db->update_batch('dt_trans_po', $ArrUpdatePO, 'id');
      }
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
        move_warehouse_stok($ArrStock, NULL, $id_gudang, $kode_trans, null);
        history("Incoming barang stok : " . $kode_trans);
      }
      echo json_encode($Arr_Data);
    } else {

      $listGudang     = $this->db->get_where('warehouse', array('desc' => 'stok'))->result_array();
      $listGudangKe   = $this->db->order_by('urut', 'ASC')->get_where('warehouse', array('desc' => 'costcenter'))->result_array();

      $countListNomorPO = $this->db
        ->select('b.no_surat, b.no_po')
        ->group_by('a.no_po')
        ->order_by('b.no_surat', 'ASC')
        ->join('tr_purchase_order b', 'a.no_po=b.no_po', 'left')
        ->where('a.qty_in < a.qty')
        ->get_where(
          'dt_trans_po a',
          array(
            'b.status' => '2',
            'a.idmaterial !=' => '',
            'SUBSTRING(a.idmaterial, 1, 1) !=' => 'M'
          )
        )
        ->num_rows();
      if ($countListNomorPO > 0) {
        $listNomorPO = $this->db
          ->select('b.no_surat, b.no_po')
          ->group_by('a.no_po')
          ->order_by('b.no_surat', 'ASC')
          ->join('tr_purchase_order b', 'a.no_po=b.no_po', 'left')
          ->where('a.qty_in < a.qty')
          ->get_where(
            'dt_trans_po a',
            array(
              'b.status' => '2',
              'a.idmaterial !=' => '',
              'SUBSTRING(a.idmaterial, 1, 1) !=' => 'M'
            )
          )
          ->result_array();
      } else {
        $listNomorPO = '';
      }
      // echo $this->db->last_query();
      // exit;

      $get_list_supplier = $this->db->select('kode_supplier, nama')->get_where('new_supplier', ['deleted_by' => null])->result();

      $data = [
        'listGudang' => $listGudang,
        'listGudangKe' => $listGudangKe,
        'listNomorPO' => $listNomorPO,
        'GET_MATERIAL' => get_inventory_lv4(),
        'listSupplier' => $get_list_supplier
      ];
      $this->template->title('Incoming Stok');
      $this->template->render('request', $data);
    }
  }

  public function print_incoming_stok()
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


    $no_po = [];
    $get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $getData[0]['no_ipp']) . "')")->result();
    foreach ($get_no_po as $item) {
      $no_po[] = $item->no_surat;
    }
    $no_po = implode(', ', $no_po);

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'getData' => $getData,
      'getDataDetail' => $getDataDetail,
      'GET_MATERIAL' => get_accessories(),
      'GET_SATUAN' => get_list_satuan(),
      'kode' => $kode_trans,
      'no_po' => $no_po
    );

    history('Print spk request material ' . $kode_trans);
    $this->load->view('print_incoming_stok', $data);
  }

  public function detail()
  {
    $kode_trans  = $this->uri->segment(3);

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

    $no_po = [];
    $get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $getData[0]['no_ipp']) . "')")->result();
    foreach ($get_no_po as $item) {
      $no_po[] = $item->no_surat;
    }
    $no_po = implode(', ', $no_po);

    $data = array(
      'getData' => $getData,
      'getDataDetail' => $getDataDetail,
      'GET_MATERIAL' => get_accessories(),
      'GET_SATUAN' => get_list_satuan(),
      'kode' => $kode_trans,
      'no_po' => $no_po
    );

    $this->load->view('detail', $data);
  }

  public function detail_purchasing_order()
  {
    $no_po       = $this->input->post('no_po');
    $no_po = implode(',', $no_po);
    $id_gudang   = $this->input->post('id_gudang');

    $categoryGudang = getPembedaAccessories($id_gudang);

    $detail = $this->db->query("
                  SELECT 
                    a.id,
                    a.idmaterial as idmaterial,
                    a.namamaterial as namamaterial,
                    a.qty as qty_po,
                    a.qty_in as qty_in,
                    b.id_stock,
                    d.code as satuan_packing
                  FROM
                    dt_trans_po a
                    LEFT JOIN accessories b ON a.idmaterial = b.id
                    LEFT JOIN accessories_category c ON b.id_category = c.id
                    LEFT JOIN ms_satuan d ON d.id = b.id_unit_gudang
                  WHERE
                    a.no_po IN ('" . str_replace(",", "','", $no_po) . "')
                    AND a.qty_in < a.qty
                    AND c.outgoing = '" . $categoryGudang . "'
                ")->result_array();
    // print_r($detail);
    // echo $this->db->last_query();
    $d_Header = "";
    // $d_Header .= "<tr>";
    $id = 0;
    if (!empty($detail)) {
      foreach ($detail as $key => $value) {
        $id++;
        $d_Header .= "<tr>";
        $d_Header .= "<td align='center'>" . $id . "</td>";
        $d_Header .= "<td align='center'>" . $value['idmaterial'] . "</td>";
        $d_Header .= "<td align='left'>" . $value['id_stock'] . "</td>";
        $d_Header .= "<td align='left'>" . $value['namamaterial'] . "</td>";
        $d_Header .= "<td align='center'>" . number_format($value['qty_po'], 2) . "</td>";
        $d_Header .= "<td align='center'>" . strtoupper($value['satuan_packing']) . "</td>";
        $d_Header .= "<td align='center'>" . number_format($value['qty_in'], 2) . "</td>";
        $d_Header .= "<td align='center' class='qty_max'>" . number_format($value['qty_po'] - $value['qty_in'], 2) . "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<input type='text' name='Detail[" . $id . "][qty_in]' class='form-control text-center input-md autoNumeric4 qty_in'>";
        $d_Header .= "<input type='hidden' name='Detail[" . $id . "][id]' value='" . $value['id'] . "'>";
        $d_Header .= "<input type='hidden' name='Detail[" . $id . "][qty_po]' value='" . $value['qty_po'] . "'>";
        $d_Header .= "<input type='hidden' name='Detail[" . $id . "][id_barang]' value='" . $value['idmaterial'] . "'>";
        $d_Header .= "<input type='hidden' name='Detail[" . $id . "][nm_barang]' value='" . $value['namamaterial'] . "'>";
        $d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<input type='text' name='Detail[" . $id . "][ket]' class='form-control input-md'>";
        $d_Header .= "</td>";
        $d_Header .= "</tr>";
      }
    } else {
      $d_Header .= "<tr>";
      $d_Header .= "<td colspan='8'><b>Data tidak ada atau <span class='text-red'>gudang yang dipilih tidak sesuai</span> !!!</b></td>";
      $d_Header .= "</tr>";
    }

    echo json_encode(array(
      'header' => $d_Header,
    ));
  }

  public function pilih_supplier()
  {
    $kode_supplier = $this->input->post('kode_supplier');

    $countListNomorPO = $this->db
      ->select('b.no_surat, b.no_po')
      ->group_by('a.no_po')
      ->order_by('b.no_surat', 'ASC')
      ->join('tr_purchase_order b', 'a.no_po=b.no_po', 'left')
      ->where('a.qty_in < a.qty')
      ->get_where(
        'dt_trans_po a',
        array(
          'b.status' => '2',
          'a.idmaterial !=' => '',
          'SUBSTRING(a.idmaterial, 1, 1) !=' => 'M',
          'b.id_suplier' => $kode_supplier,
          'b.close_po' => null
        )
      )
      ->num_rows();
    if ($countListNomorPO > 0) {
      $listNomorPO = $this->db
        ->select('b.no_surat, b.no_po')
        ->group_by('a.no_po')
        ->order_by('b.no_surat', 'ASC')
        ->join('tr_purchase_order b', 'a.no_po=b.no_po', 'left')
        ->where('a.qty_in < a.qty')
        ->get_where(
          'dt_trans_po a',
          array(
            'b.status' => '2',
            'a.idmaterial !=' => '',
            'SUBSTRING(a.idmaterial, 1, 1) !=' => 'M',
            'b.id_suplier' => $kode_supplier
          )
        )
        ->result();
    } else {
      $listNomorPO = '';
    }

    $hasil = '';
    if (!empty($listNomorPO)) {
      foreach ($listNomorPO as $item) {

        $no_pr = [];
        $get_no_pr = $this->db->query("
          SELECT
            b.no_pr
          FROM
            material_planning_base_on_produksi_detail a
            JOIN material_planning_base_on_produksi b ON b.so_number = a.so_number
          WHERE
            a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $item->no_po . "' AND (aa.tipe IS NULL OR aa.tipe = ''))
          GROUP BY b.no_pr

          UNION ALL

          SELECT
            b.no_pr
          FROM
            rutin_non_planning_detail a
            JOIN rutin_non_planning_header b ON b.no_pengajuan = a.no_pengajuan
          WHERE
            a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $item->no_po . "' AND aa.tipe = 'pr depart')
          GROUP BY b.no_pr
        ")->result();
        foreach ($get_no_pr as $item_pr) {
          $no_pr[] = $item_pr->no_pr;
        }

        $no_pr = implode(', ', $no_pr);

        $hasil .= '<tr>';
        $hasil .= '<td class="text-center">' . $item->no_surat . '</td>';
        $hasil .= '<td class="text-center">' . $no_pr . '</td>';
        $hasil .= '<td class="text-center"><input type="checkbox" name="no_po[]" class="check_po" value="' . $item->no_po . '"></td>';
        $hasil .= '</tr>';
      }
    }

    echo $hasil;
  }
}
