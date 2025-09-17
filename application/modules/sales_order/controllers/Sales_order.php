<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Sales_order extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Sales_Order_New.View';
  protected $addPermission    = 'Sales_Order_New.Add';
  protected $managePermission = 'Sales_Order_New.Manage';
  protected $deletePermission = 'Sales_Order_New.Delete';

  public function __construct()
  {
    parent::__construct();

    // $this->load->library(array( 'upload', 'Image_lib'));
    $this->load->model(array(
      'Sales_order/Sales_order_model',
      'Stock_origa/Stock_origa_model'
    ));
    $this->template->title('Manage Data Supplier');
    $this->template->page_icon('fa fa-building-o');

    date_default_timezone_set('Asia/Bangkok');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    $deleted = '0';
    $data = $this->db->query("
      SELECT
          (@row:=@row+1) AS nomor,
          a.*,
          b.nm_customer,
          IF(a.modified_by != '' OR a.modified_by IS NOT NULL, e.nm_lengkap, d.nm_lengkap) AS update_by,
          c.no_so,
          f.req_app,
          f.approve
      FROM
      tr_penawaran a
        LEFT JOIN customer b ON b.id_customer = a.id_customer
        LEFT JOIN tr_sales_order c ON c.no_penawaran = a.no_penawaran
        LEFT JOIN users d ON d.id_user = a.created_by
        LEFT JOIN users e ON e.id_user = a.modified_by
        LEFT JOIN tr_sales_order f ON f.no_penawaran = a.no_penawaran
      WHERE 1=1 AND (a.status = '2' OR a.status = '3') 
      ORDER BY a.no_penawaran DESC
    ")->result();
    // history("View index sales order");
    $this->template->set('results', [
      'list_data' => $data
    ]);
    $this->template->title('Sales Order');
    $this->template->render('list_approval_so');
  }

  public function approval()
  {
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    $deleted = '0';
    $data = $this->db->query("
      SELECT
          (@row:=@row+1) AS nomor,
      a.*,
          b.nm_customer,
      IF(a.modified_by != '' OR a.modified_by IS NOT NULL, e.nm_lengkap, d.nm_lengkap) AS update_by,
      c.no_so,
      f.req_app,
      f.approve
      FROM
      tr_penawaran a
      LEFT JOIN customer b ON b.id_customer = a.id_customer
      LEFT JOIN tr_sales_order c ON c.no_penawaran = a.no_penawaran
      LEFT JOIN users d ON d.id_user = a.created_by
      LEFT JOIN users e ON e.id_user = a.modified_by
      LEFT JOIN tr_sales_order f ON f.no_penawaran = a.no_penawaran
      WHERE 1=1 AND (a.status = '2' OR a.status = '3') AND f.req_app = '1'
      ORDER BY a.no_penawaran DESC
    ")->result();
    // history("View index sales order");
    $this->template->set('results', [
      'list_data' => $data
    ]);
    $this->template->title('Approval Sales Order');
    $this->template->render('list_approval_so');
  }

  public function data_side_sales_order()
  {
    $this->Sales_order_model->get_json_sales_order();
  }
  public function data_side_approval_sales_order()
  {
    $this->Sales_order_model->get_json_approval_sales_order();
  }

  public function deal_so($no_penawaran = null)
  {

    $session  = $this->session->userdata('app_session');
    $no_so     = $this->uri->segment(3);
    $header   = $this->db->get_where('sales_order_header', array('no_so' => $no_so))->result();
    $detail   = $this->db->get_where('sales_order_detail', array('no_so' => $no_so))->result_array();
    $customer = $this->Sales_order_model->get_data('master_customer');
    $shipping = $this->Sales_order_model->get_data('list', 'category', 'shipping');
    $product    = $this->Sales_order_model->get_data('ms_inventory_category2');
    $top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

    $get_penawaran = $this->db->query('SELECT a.*, b.nm_customer, b.alamat, c.nm_pic, d.nm_lengkap FROM tr_penawaran a LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN customer_pic c ON c.id_pic = a.pic_customer LEFT JOIN users d ON d.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();

    $get_penawaran_detail = $this->db->query('
      SELECT 
        a.stok_tersedia as stok_tersedia,
        a.qty as qty,
        a.nama_produk as nama_produk,
        a.harga_satuan as harga_satuan,
        a.diskon_persen as diskon_persen,
        c.variant_product as variant_product,
        c.color as color,
        c.surface as surface,
        d.code as product_code,
        "non other" as tipe_data
      FROM
        tr_penawaran_detail a
        LEFT JOIN stock_product b ON b.code_lv4 = a.id_category3
        LEFT JOIN bom_header c ON c.no_bom = a.no_bom
        LEFT JOIN new_inventory_4 d ON d.code_lv4 = a.id_category3
      WHERE
        a.no_penawaran = "' . $no_penawaran . '"
      GROUP BY a.id_penawaran_detail
      UNION ALL
      SELECT
        0 as stok_tersedia,
        1 as qty,
        a.keterangan as nama_produk,
        a.total_nilai as harga_satuan,
        0 as diskon_persen,
        "" as variant_product,
        "" as color,
        "" as surface,
        "" as product_code,
        "other" as tipe_data
      FROM
        tr_penawaran_other_cost a
      WHERE
        a.id_penawaran = "' . $no_penawaran . '"
      GROUP BY a.id
      UNION ALL
      SELECT
        0 as stok_tersedia,
        a.qty as qty,
        a.nm_other as nama_produk,
        a.harga as harga_satuan,
        0 as diskon_persen,
        "" as variant_product,
        "" as color,
        "" as surface,
        "" as product_code,
        "other" as tipe_data
      FROM
        tr_penawaran_other_item a
      WHERE
        a.id_penawaran = "' . $no_penawaran . '"
      GROUP BY a.id
    ')->result();

    // print_r($header);
    // exit;
    $data = [
      'header' => $header,
      'detail' => $detail,
      'customer' => $customer,
      'shipping' => $shipping,
      'product' => $product,
      'data_penawaran' => $get_penawaran,
      'data_penawaran_detail' => $get_penawaran_detail,
      'list_top' => $top
    ];
    $this->template->set('results', $data);
    $this->template->title('Deal SO');
    $this->template->page_icon('fa fa-edit');
    $this->template->render('add_so', $data);
  }

  public function edit()
  {

    $session = $this->session->userdata('app_session');
    $id_time = $this->uri->segment(3);
    $customer    = $this->Sales_order_model->get_data('master_customer');
    $supplier    = $this->Sales_order_model->get_data('master_supplier');
    $material    = $this->Sales_order_model->get_data('ms_inventory_category2');
    // $machine      = $this->Sales_order_model->get_data_group('asset','category','4','nm_asset');
    // $mould      = $this->Sales_order_model->get_data_group('asset','category','5','nm_asset');
    // $costcenter  = $this->Sales_order_model->get_data('ms_costcenter','deleted','0');
    $header  = $this->db->query("SELECT * FROM cycletime_header WHERE id_time='" . $id_time . "' LIMIT 1 ")->result();
    $costcenter  = $this->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' ORDER BY nama_costcenter ASC ")->result_array();
    $machine  = $this->db->query("SELECT * FROM asset WHERE category='4' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
    $mould  = $this->db->query("SELECT * FROM asset WHERE category='5' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
    $data = [
      'customer' => $customer,
      'supplier' => $supplier,
      'material' => $material,
      'mesin' => $machine,
      'mould' => $mould,
      'costcenter' => $costcenter,
      'header' => $header
    ];
    $this->template->set('results', $data);
    $this->template->page_icon('fa fa-edit');
    $this->template->title('Edit Cycletime');
    $this->template->render('edit', $data);
  }


  public function detail_sales_order($no_so)
  {
    // $this->auth->restrict($this->viewPermission);
    $no_so   = $this->input->post('no_so');
    $header = $this->db->get_where('sales_order_header', array('no_so' => $no_so))->result();
    $detail = $this->db->get_where('sales_order_detail', array('no_so' => $no_so))->result_array();
    $customer    = $this->Sales_order_model->get_data('master_customer');
    $shipping  = $this->Sales_order_model->get_data('list', 'category', 'shipping');

    $sales_order = $this->db->query('SELECT a.*, b.nm_customer, b.alamat, c.nm_pic, d.nm_lengkap FROM tr_sales_order a LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN customer_pic c ON c.id_pic = a.pic_customer LEFT JOIN users d ON d.id_user = a.created_by WHERE a.no_so = "' . $no_so . '"')->row();

    $get_top = $this->db->get_where('list_help', ['id' => $sales_order->top])->row();
    $top = $get_top->name;

    $get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $sales_order->no_penawaran])->row();

    // $sales_order_detail = $this->db->get_where('tr_sales_order_detail', ['no_so' => $sales_order->no_so])->result();
    $sales_order_detail = $this->db->query('
      SELECT
        a.qty as qty,
        a.nama_produk as nama_produk,
        a.harga_satuan as harga_satuan,
        a.diskon_persen as diskon_persen,
        c.variant_product as variant_product,
        c.color as color,
        c.surface as surface,
        d.actual_stock as actual_stock,
        d.booking_stock as booking_stock,
        e.code as product_code
      FROM
        tr_sales_order_detail a
        LEFT JOIN bom_header c ON c.no_bom = a.no_bom
        LEFT JOIN stock_product d ON d.no_bom = a.no_bom AND d.code_lv4 = a.id_category3
        LEFT JOIN new_inventory_4 e ON e.code_lv4 = a.id_category3
      WHERE
        a.no_so = "' . $sales_order->no_so . '"

      UNION ALL 

      SELECT
        1 as qty,
        a.keterangan as nama_produk,
        a.total_nilai as harga_satuan,
        0 as diskon_persen,
        "" as variant_product,
        "" as color,
        "" as surface,
        0 as actual_stock,
        0 as booking_stock,
        "" as product_code
      FROM
        tr_penawaran_other_cost a
      WHERE
        a.id_penawaran = "' . $sales_order->no_penawaran . '"

      UNION ALL

      SELECT
        a.qty as qty,
        a.nm_other as nama_produk,
        a.harga as harga_satuan,
        0 as diskon_persen,
        "" as variant_product,
        "" as color,
        "" as surface,
        0 as actual_stock,
        0 as booking_stock,
        "" as product_code
      FROM
        tr_penawaran_other_item a
      WHERE
        a.id_penawaran = "' . $sales_order->no_penawaran . '"
    ')->result();
    // print_r($header);
    $data = [
      'sales_order' => $sales_order,
      'data_sales_order_detail' => $sales_order_detail,
      'data_penawaran' => $get_penawaran,
      'top_name' => $top
    ];
    $this->template->set('results', $data);
    $this->template->render('detail_sales_order', $data);
  }

  public function approval_modal()
  {
    $no_so   = $this->input->post('no_so');
    $header = $this->db->get_where('sales_order_header', array('no_so' => $no_so))->result();
    $detail = $this->db->get_where('sales_order_detail', array('no_so' => $no_so))->result_array();
    $customer    = $this->Sales_order_model->get_data('master_customer');
    $shipping  = $this->Sales_order_model->get_data('list', 'category', 'shipping');

    $sales_order = $this->db->query('SELECT a.*, b.nm_customer, b.alamat, c.nm_pic, d.nm_lengkap FROM tr_sales_order a LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN customer_pic c ON c.id_pic = a.pic_customer LEFT JOIN users d ON d.id_user = a.created_by WHERE a.no_so = "' . $no_so . '"')->row();

    $get_top = $this->db->get_where('list_help', ['id' => $sales_order->top])->row();
    $top = $get_top->name;

    $get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $sales_order->no_penawaran])->row();
    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $sales_order->no_penawaran])->row();

    // $sales_order_detail = $this->db->get_where('tr_sales_order_detail', ['no_so' => $sales_order->no_so])->result();
    $sales_order_detail = $this->db->query('
      SELECT
        a.stok_tersedia as stok_tersedia,
        a.qty as qty,
        a.nama_produk as nama_produk,
        a.harga_satuan as harga_satuan,
        a.diskon_persen as diskon_persen,
        c.variant_product as variant_product,
        d.actual_stock as actual_stock,
        d.booking_stock as booking_stock,
        c.color as color,
        c.surface as surface,
        e.code as product_code
      FROM
        tr_sales_order_detail a
        LEFT JOIN bom_header c ON c.no_bom = a.no_bom
        LEFT JOIN stock_product d ON d.no_bom = a.no_bom AND d.code_lv4 = a.id_category3
        LEFT JOIN new_inventory_4 e ON e.code_lv4 = a.id_category3
      WHERE
        a.no_so = "' . $sales_order->no_so . '"
      

      UNION ALL 

      SELECT
        1 as stok_tersedia,
        1 as qty,
        a.keterangan as nama_produk,
        a.nilai as harga_satuan,
        0 as diskon_persen,
        "" as variant_product,
        0 as actual_stock,
        0 as booking_stock,
        "" as color,
        "" as surface,
        "" as product_code
      FROM
        tr_penawaran_other_cost a
      WHERE
        a.id_penawaran = "' . $sales_order->no_penawaran . '"

        UNION ALL 

      SELECT
        1 as stok_tersedia,
        a.qty as qty,
        a.nm_other as nama_produk,
        a.harga as harga_satuan,
        0 as diskon_persen,
        "" as variant_product,
        0 as actual_stock,
        0 as booking_stock,
        "" as color,
        "" as surface,
        "" as product_code
      FROM
        tr_penawaran_other_item a
      WHERE
        a.id_penawaran = "' . $sales_order->no_penawaran . '"
    ')->result();
    // print_r($header);
    $data = [
      'sales_order' => $sales_order,
      'data_sales_order_detail' => $sales_order_detail,
      'data_penawaran' => $get_penawaran,
      'data_penawaran_detail' => $get_penawaran_detail,
      'top_name' => $top
    ];
    $this->template->set('results', $data);
    $this->template->render('approval_so');
  }

  public function get_add()
  {
    $id   = $this->uri->segment(3);
    $no   = 0;

    $product    = $this->Sales_order_model->get_data('ms_inventory_category2');
    $d_Header = "";
    // $d_Header .= "<tr>";
    $d_Header .= "<tr class='header_" . $id . "'>";
    $d_Header .= "<td align='center'>" . $id . "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<select name='Detail[" . $id . "][product]' data-no='" . $id . "' class='chosen_select form-control input-sm inline-blockd product'>";
    $d_Header .= "<option value='0'>Select Product Name</option>";
    foreach ($product as $valx) {
      $d_Header .= "<option value='" . $valx->id_category2 . "'>" . strtoupper($valx->nama) . "</option>";
    }
    $d_Header .=     "</select>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<input type='text' name='Detail[" . $id . "][qty_order]' class='form-control input-md maskM qty' placeholder='Qty Propose' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<input type='text' name='Detail[" . $id . "][qty_propose]' class='form-control input-md maskM qty' placeholder='Qty Order' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<input type='text' name='Detail[" . $id . "][qty_balance]' id='balance_" . $id . "' class='form-control text-center input-md' placeholder='Qty Balance' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
    $d_Header .= "</td>";
    $d_Header .= "</tr>";

    //add part
    $d_Header .= "<tr id='add_" . $id . "'>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "</tr>";

    echo json_encode(array(
      'header'      => $d_Header,
    ));
  }

  public function save_so()
  {

    $Arr_Kembali  = array();
    $data      = $this->input->post();

    $config['upload_path'] = './uploads/po'; //path folder
    $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
    $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
    $config['encrypt_name'] = FALSE; // Encrypt the uploaded file's name.
    $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

    $this->load->library('upload', $config);
    $this->upload->initialize($config);

    $upload_po = '';
    // for ($i = 0; $i < count($_FILES['upload_po']['name']); $i++) {
    //   if (!$this->upload->do_upload('upload_po')) {
    //   } else {
    //     $data_upload_po = $this->upload->data();
    //     $upload_po = $upload_po . '/uploads/po/' . $data_upload_po['file_name'][$i] . '|';

    //     print_r($_FILES['upload_po']['name']);
    //   }
    // }

    $files = $_FILES['upload_po'];
    $file_count = count($files['name']);
    for ($i = 0; $i < $file_count; $i++) {
      $_FILES['upload_po']['name'] = $files['name'][$i];
      $_FILES['upload_po']['type'] = $files['type'][$i];
      $_FILES['upload_po']['tmp_name'] = $files['tmp_name'][$i];
      $_FILES['upload_po']['error'] = $files['error'][$i];
      $_FILES['upload_po']['size'] = $files['size'][$i];

      if (!$this->upload->do_upload('upload_po')) {
        // If upload fails, display error
        $error = array('error' => $this->upload->display_errors());
        // print_r($error);
      } else {
        $data_upload_po = $this->upload->data();
        $upload_po = $upload_po . '|' . 'uploads/po/' . $data_upload_po['file_name'];
      }
    }


    // print_r($upload_po);
    // exit;

    // if (!$this->upload->do_upload('upload_penawaran_deal')) {
    //   $data_upload_penawaran_deal = 'Upload Error';
    // } else {
    //   $data_upload_penawaran_deal = $this->upload->data();
    //   $data_upload_penawaran_deal = '/uploads/po/' . $data_upload_penawaran_deal['file_name'];
    // }


    // print_r($data);
    // exit;
    $session       = $this->session->userdata('app_session');
    // $Detail       = $data['Detail'];
    $Ym            = date('y');
    $no_so        = '';
    // $no_sox        = $data['no_so'];

    $created_by   = 'updated_by';
    $created_date = 'updated_date';
    $tanda        = 'Insert ';
    if (empty($no_sox)) {
      //pengurutan kode
      $srcMtr        = "SELECT MAX(no_so) as maxP FROM tr_sales_order WHERE no_so LIKE 'SO" . $Ym . "%' ";
      $numrowMtr    = $this->db->query($srcMtr)->num_rows();
      $resultMtr    = $this->db->query($srcMtr)->result_array();
      $angkaUrut2    = $resultMtr[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 4, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $no_so        = "SO" . $Ym . $urut2;

      $created_by   = 'created_by';
      $created_date = 'created_date';
      $tanda        = 'Update ';
    }

    $get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $data['no_penawaran']])->row();
    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $data['no_penawaran']])->result();

    $get_nilai_so = $this->db->query('SELECT SUM(a.harga_satuan * a.qty) AS nilai_so FROM tr_penawaran_detail a WHERE a.no_penawaran = "' . $data['no_penawaran'] . '"')->row();

    $get_so_before = $this->db->get_where('tr_sales_order', ['no_penawaran' => $data['no_penawaran']])->row();
    if (count($get_so_before) > 0) {
      $no_so = $get_so_before->no_so;
    }

    $this->db->trans_begin();

    $this->db->delete('tr_sales_order', ['no_penawaran' => $data['no_penawaran']]);
    $this->db->delete('tr_sales_order_detail', ['no_penawaran' => $data['no_penawaran']]);

    $this->db->insert('tr_sales_order', [
      'no_so' => $no_so,
      'no_penawaran' => $data['no_penawaran'],
      'id_customer' => $get_penawaran->id_customer,
      'pic_customer' => $get_penawaran->pic_customer,
      'email_customer' => $get_penawaran->email_customer,
      'top' => $data['top'],
      'notes' => $data['notes'],
      'nilai_so' => $get_nilai_so->nilai_so,
      'order_status' => $get_penawaran->status,
      'id_sales' => $get_penawaran->id_sales,
      'nama_sales' => $get_penawaran->nama_sales,
      'status' => $get_penawaran->status,
      'revisi' => $get_penawaran->no_revisi,
      'project' => $get_penawaran->project,
      'created_by' => $get_penawaran->created_by,
      'created_on' => $get_penawaran->created_on,
      'modified_by' => $get_penawaran->modified_by,
      'modified_on' => $get_penawaran->modified_on,
      'revisi_by' => $get_penawaran->revisi_by,
      'revisi_on' => $get_penawaran->revisi_on,
      'ppn' => $get_penawaran->ppn,
      'nilai_ppn' => $get_penawaran->nilai_ppn,
      'grand_total' => $get_penawaran->grand_total,
      'upload_po' => $upload_po,
      'delivery_address' => $data['delivery_address'],
      'delivery_date' => $data['delivery_date'],
      'invoice_address' => $data['invoice_address'],
      'tgl_so' => $get_penawaran->tgl_penawaran,
      'req_app' => 1,
      'pengiriman' => $data['pengiriman'],
      'po_date' => $data['po_date'],
      'po_no' => $data['po_no'],
      'tipe_so' => $data['tipe_so']
    ]);

    foreach ($get_penawaran_detail as $penawaran_detail) :
      $this->db->insert('tr_sales_order_detail', [
        'id_penawaran_detail' => $penawaran_detail->id_penawaran_detail,
        'no_so' => $no_so,
        'no_penawaran' => $data['no_penawaran'],
        'id_category3' => $penawaran_detail->id_category3,
        'nama_produk' => $penawaran_detail->nama_produk,
        'qty_so' => $penawaran_detail->qty,
        'qty' => $penawaran_detail->qty,
        'harga_satuan' => $penawaran_detail->harga_satuan,
        'stok_tersedia' => $penawaran_detail->stok_tersedia,
        'diskon_persen' => $penawaran_detail->diskon_persen,
        'diskon_nilai' => $penawaran_detail->diskon_nilai,
        'total_harga' => $penawaran_detail->total_harga,
        'id_product_price' => $penawaran_detail->id_product_price,
        'no_bom' => $penawaran_detail->no_bom,
        'created_by' => $session['id_user'],
        'created_on' => date('Y-m-d H:i:s')
      ]);
    endforeach;

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
      // history($tanda . " sales order " . $no_so);
    }

    echo json_encode($Arr_Data);
  }

  public function list_center()
  {
    $id = $this->uri->segment(3);
    $query     = "SELECT * FROM ms_costcenter WHERE id_dept='" . $id . "' ORDER BY nama_costcenter ASC";
    $Q_result  = $this->db->query($query)->result();
    $option   = "<option value='0'>Select an Option</option>";
    foreach ($Q_result as $row) {
      $option .= "<option value='" . $row->nama_costcenter . "'>" . strtoupper($row->nama_costcenter) . "</option>";
    }
    echo json_encode(array(
      'option' => $option
    ));
  }

  public function delete_sales_order()
  {

    $Arr_Kembali  = array();
    $data          = $this->input->post();
    // print_r($data);
    // exit;
    $session       = $this->session->userdata('app_session');
    $no_so        = $this->uri->segment(3);

    $ArrHeader      = array(
      'deleted'      => "Y",
      'deleted_by'  => $session['id_user'],
      'deleted_date'  => date('Y-m-d H:i:s')
    );

    $this->db->trans_start();
    $this->db->where('no_so', $no_so);
    $this->db->update('sales_order_header', $ArrHeader);
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Data  = array(
        'pesan'    => 'Delete gagal disimpan ...',
        'status'  => 0
      );
    } else {
      $this->db->trans_commit();
      $Arr_Data  = array(
        'pesan'    => 'Delete berhasil disimpan. Thanks ...',
        'status'  => 1
      );
      history("Delete Sales Order " . $no_so);
    }

    echo json_encode($Arr_Data);
  }



  public function get_balance()
  {
    $product   = $this->uri->segment(3);
    $cust     = $this->uri->segment(4);

    $balance  = $this->db->query("SELECT qty_kurang FROM search_balance_so WHERE product = '" . $product . "' AND code_cust='" . $cust . "' LIMIT 1")->result();

    echo json_encode(array(
      'balance'      => (!empty($balance[0]->qty_kurang)) ? $balance[0]->qty_kurang : 0,
    ));
  }

  public function update_status()
  {
    $id_so = $this->input->post('id_so');

    $this->db->trans_begin();

    $this->db->update('tr_sales_order', [
      'req_app' => 1
    ], [
      'no_so' => $id_so
    ]);

    if ($this->db->trans_status() === FALSE) {
      $valid = 0;
      $pesan = 'Maaf, Update Status ke Request Approval gagal !';

      $this->db->trans_rollback();
    } else {
      $valid = 1;
      $pesan = 'Selamat, Update Status ke Request Approval berhasil !';

      $this->db->trans_commit();
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }

  public function save_approval()
  {
    $session        = $this->session->userdata('app_session');

    $no_so = $this->input->post('no_so');
    $action_type = $this->input->post('action_type');
    $keterangan_approve_reject = $this->input->post('keterangan_approve_reject');

    $this->db->trans_begin();

    if ($action_type == '1') {
      $this->db->update('tr_sales_order', [
        'status' => '3',
        'approve' => '1',
        'keterangan_approve' => $keterangan_approve_reject
      ], [
        'no_so' => $no_so
      ]);

      $ArrSODetail = $this->db->get_where('tr_sales_order_detail', ['no_so' => $no_so])->result();
      if (!empty($ArrSODetail)) {
        $this->Stock_origa_model->approveSO($ArrSODetail, $no_so);
      }
    } else {
      $this->db->update('tr_sales_order', [
        'status' => '2',
        'req_app' => '0',
        'keterangan_loss' => $keterangan_approve_reject
      ], [
        'no_so' => $no_so
      ]);
    }

    $status_action = 'Approve';
    if ($action_type !== '1') {
      $status_action = 'Reject';
    }

    if ($this->db->trans_status() === FALSE) {
      $valid = 0;
      $msg = 'Maaf, proses ' . $status_action . ' tidak berhasil';

      $this->db->trans_rollback();
    } {
      $valid = 1;
      $msg = 'Selamat, proses ' . $status_action . ' berhasil';

      $this->db->trans_commit();
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $msg
    ]);
  }

  public function print_sales_order($no_so, $show_disc = null)
  {
    $this->template->page_icon('fa fa-list');

    $get_penawaran = $this->db->query('SELECT a.*, a.notes as notes_so, b.nm_customer, b.alamat, b.fax, b.telpon, c.quote_by, c.subject, c.time_delivery, c.offer_period, c.delivery_term, c.warranty, c.currency, c.quote_by, c.notes, c.req_app1, c.req_app2, c.req_app3, d.name as nama_top, e.nm_pic, e.hp as pic_hp FROM tr_sales_order a LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN tr_penawaran c ON c.no_penawaran = a.no_penawaran LEFT JOIN list_help d ON d.id = c.top LEFT JOIN customer_pic e ON e.id_pic = a.pic_customer WHERE a.no_so = "' . $no_so . '"')->row();



    $get_penawaran_detail = $this->db->query('
      SELECT 
        a.id_so_detail as id_so_detail, 
        a.id_penawaran_detail as id_penawaran_detail, 
        a.no_so as no_so, 
        a.no_penawaran as no_penawaran, 
        a.id_category3 as id_category3, 
        a.nama_produk as nama_produk, 
        a.qty_so as qty_so, 
        a.harga_satuan as harga_satuan, 
        a.stok_tersedia as stok_tersedia, 
        a.diskon_persen as diskon_persen, 
        a.total_harga as total_harga, 
        a.tgl_delivery as tgl_delivery, 
        a.created_by as created_by, 
        a.created_on as created_on, 
        a.nilai_diskon as nilai_diskon, 
        b.nama, 
        b.code, 
        c.code as unit_packing, 
        d.code as unit_measure, 
        e.ukuran_potongan, 
        f.nama as product_category,
        g.variant_product as variant,
        g.color as color,
        g.surface as surface
      FROM 
        tr_sales_order_detail a 
        LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_category3 
        LEFT JOIN ms_satuan c ON c.id = b.id_unit_packing 
        LEFT JOIN ms_satuan d ON d.id = b.id_unit 
        LEFT JOIN tr_penawaran_detail e ON e.id_penawaran_detail = a.id_penawaran_detail 
        LEFT JOIN new_inventory_2 f ON f.code_lv2 = b.code_lv2 
        LEFT JOIN bom_header g ON g.no_bom = e.no_bom
      WHERE a.no_so = "' . $no_so . '" 
      

      UNION ALL

      SELECT
        a.id as id_so_detail, 
        "" as id_penawaran_detail, 
        "" as no_so, 
        a.id_penawaran as no_penawaran, 
        "" as id_category3, 
        a.keterangan as nama_produk, 
        1 as qty_so, 
        a.nilai as harga_satuan, 
        1 as stok_tersedia, 
        0 as diskon_persen, 
        a.nilai as total_harga, 
        "" as tgl_delivery, 
        a.dibuat_oleh as created_by, 
        a.dibuat_tgl as created_on, 
        0 as nilai_diskon,
        a.keterangan as nama, 
        "" as code, 
        "" as unit_packing, 
        "" as unit_measure, 
        "" as ukuran_potongan, 
        "" as product_category,
        "" as variant,
        "" as color,
        "" as surface
      FROM
        tr_penawaran_other_cost a
        LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
      WHERE
        b.no_so = "' . $no_so . '"

      UNION ALL

      SELECT
        a.id as id_so_detail, 
        "" as id_penawaran_detail, 
        "" as no_so, 
        a.id_penawaran as no_penawaran, 
        "" as id_category3, 
        a.nm_other as nama_produk, 
        a.qty as qty_so, 
        a.harga as harga_satuan, 
        1 as stok_tersedia, 
        0 as diskon_persen, 
        a.total as total_harga, 
        "" as tgl_delivery, 
        a.created_by as created_by, 
        a.created_on as created_on, 
        0 as nilai_diskon,
        a.nm_other as nama, 
        IF(c.code IS NULL, d.id_stock, c.code) as code, 
        IF(g.code IS NULL, h.code, e.code) as unit_packing, 
        IF(e.code IS NULL, f.code, e.code) as unit_measure, 
        "" as ukuran_potongan, 
        "" as product_category,
        "" as variant,
        "" as color,
        "" as surface
      FROM
        tr_penawaran_other_item a
        LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
        LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_other
        LEFT JOIN accessories d ON d.id = a.id_other
        LEFT JOIN ms_satuan e ON e.id = c.id_unit
        LEFT JOIN ms_satuan f ON f.id = d.id_unit
        LEFT JOIN ms_satuan g ON g.id = c.id_unit_packing
        LEFT JOIN ms_satuan h ON h.id = d.id_unit_gudang
      WHERE
        b.no_so = "' . $no_so . '"
    ')->result();
    if (!$get_penawaran_detail) {
      print_r($this->db->error($get_penawaran_detail));
      exit;
    }




    if ($get_penawaran->quote_by == "ORINDO") {
      $logo = '<img src="' . base_url('assets/images/orindo_logo.png') . '" width="300" alt="" srcset="" style="padding-top: 40px;">';
    } else {
      $logo = '<img src="' . base_url('assets/images/ori_logo2.png') . '" width="75" alt="" srcset="">';
    }

    $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $get_penawaran->no_penawaran])->result();

    if ($show_disc !== null) {
      $data = [
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'logo' => $logo,
        'list_other_cost' => $get_other_cost,
        'show_disc' => $show_disc
      ];
    } else {
      $data = [
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'logo' => $logo,
        'list_other_cost' => $get_other_cost
      ];
    }
    $this->load->view('print_sales_order', ['results' => $data]);
  }

  public function print_sales_order_non_ppn($no_so, $show_disc = null)
  {
    $this->template->page_icon('fa fa-list');

    $get_penawaran = $this->db->query('SELECT a.*, b.nm_customer, b.alamat, b.telpon, c.quote_by, c.subject, c.time_delivery, c.offer_period, c.delivery_term, c.warranty, c.currency, d.name as nama_top FROM tr_sales_order a LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN tr_penawaran c ON c.no_penawaran = a.no_penawaran LEFT JOIN list_help d ON d.id = c.top WHERE a.no_so = "' . $no_so . '"')->row();
    $get_penawaran_detail = $this->db->query('SELECT a.*, b.code, c.code as unit_packing, d.code as unit_measure, e.ukuran_potongan FROM tr_sales_order_detail a LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_category3 LEFT JOIN ms_satuan c ON c.id = b.id_unit_packing LEFT JOIN ms_satuan d ON d.id = b.id_unit LEFT JOIN tr_penawaran_detail e ON e.id_penawaran_detail = a.id_penawaran_detail WHERE a.no_so = "' . $no_so . '"  ORDER BY a.id_penawaran_detail ASC')->result();

    if ($get_penawaran->quote_by == "ORINDO") {
      $logo = '<img src="' . base_url('assets/images/orindo_logo.png') . '" width="300" alt="" srcset="" style="padding-top: 40px;">';
    } else {
      $logo = '<img src="' . base_url('assets/images/ori_logo2.png') . '" width="75" alt="" srcset="">';
    }

    $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $get_penawaran->no_penawaran])->result();

    if ($show_disc !== null) {
      $data = [
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'logo' => $logo,
        'list_other_cost' => $get_other_cost,
        'show_disc' => $show_disc
      ];
    } else {
      $data = [
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'logo' => $logo,
        'list_other_cost' => $get_other_cost
      ];
    }
    $this->load->view('print_sales_order_non_ppn', ['results' => $data]);
  }
}
