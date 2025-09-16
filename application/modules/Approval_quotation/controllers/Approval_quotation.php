<?php
if (!defined('BASEPATH')) {
  exit('No direct script access allowed');
}

class Approval_quotation extends Admin_Controller
{
  //Permission
  protected $viewPermission1   = 'Approval_Quotation_1.View';
  protected $addPermission1    = 'Approval_Quotation_1.Add';
  protected $managePermission1 = 'Approval_Quotation_1.Manage';
  protected $deletePermission1 = 'Approval_Quotation_1.Delete';

  protected $viewPermission2   = 'Approval_Quotation_2.View';
  protected $addPermission2    = 'Approval_Quotation_2.Add';
  protected $managePermission2 = 'Approval_Quotation_2.Manage';
  protected $deletePermission2 = 'Approval_Quotation_2.Delete';

  protected $viewPermission3   = 'Approval_Quotation_3.View';
  protected $addPermission3    = 'Approval_Quotation_3.Add';
  protected $managePermission3 = 'Approval_Quotation_3.Manage';
  protected $deletePermission3 = 'Approval_Quotation_3.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Approval_quotation_model');
    $this->template->title('Manage Product Type');
    $this->template->page_icon('fa fa-building-o');

    date_default_timezone_set('Asia/Bangkok');
  }



  public function level1()
  {
    $this->auth->restrict($this->viewPermission1);
    $session = $this->session->userdata('app_session');

    $this->template->page_icon('fa fa-users');

    $get_penawaran = $this->db->query('SELECT a.*, b.nm_customer FROM tr_penawaran a LEFT JOIN customer b ON b.id_customer = a.id_customer WHERE a.req_app1 = "1" AND a.status = "1"  ORDER BY a.created_on DESC')->result();

    $this->template->set('results', $get_penawaran);
    $this->template->title('Approval Quotation Supervisor');
    $this->template->render('level1');
  }

  public function level2()
  {
    $this->auth->restrict($this->viewPermission2);
    $session = $this->session->userdata('app_session');

    $this->template->page_icon('fa fa-users');

    $get_penawaran = $this->db->query('SELECT a.*, b.nm_customer FROM tr_penawaran a LEFT JOIN customer b ON b.id_customer = a.id_customer WHERE a.req_app2 = "1" AND a.status = "1"  ORDER BY a.created_on DESC')->result();

    $this->template->set('results', $get_penawaran);
    $this->template->title('Approval Quotation Manager');
    $this->template->render('level2');
  }

  public function level3()
  {
    $this->auth->restrict($this->viewPermission3);
    $session = $this->session->userdata('app_session');

    $this->template->page_icon('fa fa-users');

    $get_penawaran = $this->db->query('SELECT a.*, b.nm_customer FROM tr_penawaran a LEFT JOIN customer b ON b.id_customer = a.id_customer WHERE a.req_app3 = "1" AND a.status = "1"  ORDER BY a.created_on DESC')->result();

    $this->template->set('results', $get_penawaran);
    $this->template->title('Approval Quotation Cost Control');
    $this->template->render('level3');
  }

  public function view_quotation1($no_penawaran)
  {
    $session = $this->session->userdata('app_session');

    $Cust = $this->db->query("SELECT a.* FROM customer a")->result();
    $User = $this->db->query("SELECT a.* FROM users a")->result();
    $pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

    $get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

    if ($no_penawaran !== null) {
      $get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
      $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

      $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

      $get_other_item = $this->db->query("
			SELECT
				a.code_lv4 as id_product,
				a.nama as nm_product,
				a.code as product_code
			FROM
				new_inventory_4 a
			WHERE
				a.category = 'material' AND
				a.deleted_by IS NULL
			
			UNION ALL

			SELECT
				a.id as id_product,
				a.stock_name as nm_product,
				a.id_stock as product_code
			FROM
				accessories a 
			WHERE
				a.deleted_by IS NULL
		")->result();

      $get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'nm_sales' => $session['nm_lengkap'],
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'list_top' => $get_top,
        'curr' => $get_penawaran->currency,
        'list_other_cost' => $get_other_cost,
        'list_other_item' => $get_other_item,
        'list_another_item' => $get_list_item_others
      ]);
    } else {
      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'list_penawaran_detail' => $get_penawaran_detail,
        'nm_sales' => $session['nm_lengkap'],
        'list_top' => $get_top
      ]);
    }
    // $this->template->set('results', $get_penawaran);
    $this->template->title('View Quotation');
    $this->template->render('view_quotation1');
  }

  public function view_quotation2($no_penawaran)
  {
    $session = $this->session->userdata('app_session');

    $Cust = $this->db->query("SELECT a.* FROM customer a")->result();
    $User = $this->db->query("SELECT a.* FROM users a")->result();
    $pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

    $get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

    if ($no_penawaran !== null) {
      $get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
      $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

      $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

      $get_other_item = $this->db->query("
			SELECT
				a.code_lv4 as id_product,
				a.nama as nm_product,
				a.code as product_code
			FROM
				new_inventory_4 a
			WHERE
				a.category = 'material' AND
				a.deleted_by IS NULL
			
			UNION ALL

			SELECT
				a.id as id_product,
				a.stock_name as nm_product,
				a.id_stock as product_code
			FROM
				accessories a 
			WHERE
				a.deleted_by IS NULL
		")->result();

      $get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'nm_sales' => $session['nm_lengkap'],
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'list_top' => $get_top,
        'curr' => $get_penawaran->currency,
        'list_other_cost' => $get_other_cost,
        'list_other_item' => $get_other_item,
        'list_another_item' => $get_list_item_others
      ]);
    } else {
      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'list_penawaran_detail' => $get_penawaran_detail,
        'nm_sales' => $session['nm_lengkap'],
        'list_top' => $get_top
      ]);
    }
    // $this->template->set('results', $get_penawaran);
    $this->template->title('View Quotation');
    $this->template->render('view_quotation1');
  }

  public function view_quotation3($no_penawaran)
  {
    $session = $this->session->userdata('app_session');

    $Cust = $this->db->query("SELECT a.* FROM customer a")->result();
    $User = $this->db->query("SELECT a.* FROM users a")->result();
    $pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

    $get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

    if ($no_penawaran !== null) {
      $get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
      $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

      $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

      $get_other_item = $this->db->query("
			SELECT
				a.code_lv4 as id_product,
				a.nama as nm_product,
				a.code as product_code
			FROM
				new_inventory_4 a
			WHERE
				a.category = 'material' AND
				a.deleted_by IS NULL
			
			UNION ALL

			SELECT
				a.id as id_product,
				a.stock_name as nm_product,
				a.id_stock as product_code
			FROM
				accessories a 
			WHERE
				a.deleted_by IS NULL
		")->result();

      $get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'nm_sales' => $session['nm_lengkap'],
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'list_top' => $get_top,
        'curr' => $get_penawaran->currency,
        'list_other_cost' => $get_other_cost,
        'list_other_item' => $get_other_item,
        'list_another_item' => $get_list_item_others
      ]);
    } else {
      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'list_penawaran_detail' => $get_penawaran_detail,
        'nm_sales' => $session['nm_lengkap'],
        'list_top' => $get_top
      ]);
    }
    // $this->template->set('results', $get_penawaran);
    $this->template->title('View Quotation');
    $this->template->render('view_quotation1');
  }

  public function approval_quotation_1($no_penawaran)
  {
    $session = $this->session->userdata('app_session');

    $Cust = $this->db->query("SELECT a.* FROM customer a")->result();
    $User = $this->db->query("SELECT a.* FROM users a")->result();
    $pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

    if ($no_penawaran !== null) {
      $get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
      $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

      $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

      $action_app = 'Approve';
      if ($get_penawaran->req_app2 == '1' || $get_penawaran->req_app3 == '1') {
        $action_app = 'Request Approval Manager';
      }

      $get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

      $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

      $get_other_item = $this->db->query("
        SELECT
          a.code_lv4 as id_product,
          a.nama as nm_product,
          a.code as product_code
        FROM
          new_inventory_4 a
        WHERE
          a.category = 'material' AND
          a.deleted_by IS NULL
        
        UNION ALL

        SELECT
          a.id as id_product,
          a.stock_name as nm_product,
          a.id_stock as product_code
        FROM
          accessories a 
        WHERE
          a.deleted_by IS NULL
      ")->result();

      $get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'nm_sales' => $session['nm_lengkap'],
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'app_quote' => 1,
        'action_app' => $action_app,
        'list_top' => $get_top,
        'curr' => $get_penawaran->currency,
        'list_other_cost' => $get_other_cost,
        'list_other_item' => $get_other_item,
        'list_another_item' => $get_list_item_others
      ]);
    } else {
      $get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();
      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'list_penawaran_detail' => $get_penawaran_detail,
        'nm_sales' => $session['nm_lengkap'],
        'list_top' => $get_top
      ]);
    }
    // $this->template->set('results', $get_penawaran);
    $this->template->title('Approval Supervisor');
    $this->template->render('approval_quotation');
  }

  public function approval_quotation_2($no_penawaran)
  {
    $session = $this->session->userdata('app_session');

    $Cust = $this->db->query("SELECT a.* FROM customer a")->result();
    $User = $this->db->query("SELECT a.* FROM users a")->result();
    $pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

    if ($no_penawaran !== null) {
      $get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
      $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

      $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

      $action_app = 'Approve';
      if ($get_penawaran->req_app3 == '1') {
        $action_app = 'Request Approval Cost Control';
      }

      $get_other_item = $this->db->query("
        SELECT
          a.code_lv4 as id_product,
          a.nama as nm_product,
          a.code as product_code
        FROM
          new_inventory_4 a
        WHERE
          a.category = 'material' AND
          a.deleted_by IS NULL
        
        UNION ALL

        SELECT
          a.id as id_product,
          a.stock_name as nm_product,
          a.id_stock as product_code
        FROM
          accessories a 
        WHERE
          a.deleted_by IS NULL
      ")->result();

      $get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'nm_sales' => $session['nm_lengkap'],
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'app_quote' => 1,
        'action_app' => $action_app,
        'curr' => $get_penawaran->currency,
        'list_other_cost' => $get_other_cost,
        'list_other_item' => $get_other_item,
        'list_another_item' => $get_list_item_others
      ]);
    } else {
      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'list_penawaran_detail' => $get_penawaran_detail,
        'nm_sales' => $session['nm_lengkap']
      ]);
    }
    // $this->template->set('results', $get_penawaran);
    $this->template->title('Approval Manager');
    $this->template->render('approval_quotation');
  }

  public function approval_quotation_3($no_penawaran)
  {
    $session = $this->session->userdata('app_session');

    $Cust = $this->db->query("SELECT a.* FROM customer a")->result();
    $User = $this->db->query("SELECT a.* FROM users a")->result();
    $pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

    $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

    if ($no_penawaran !== null) {
      $get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
      $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

      $get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

      $get_other_item = $this->db->query("
        SELECT
          a.code_lv4 as id_product,
          a.nama as nm_product,
          a.code as product_code
        FROM
          new_inventory_4 a
        WHERE
          a.category = 'material' AND
          a.deleted_by IS NULL
        
        UNION ALL

        SELECT
          a.id as id_product,
          a.stock_name as nm_product,
          a.id_stock as product_code
        FROM
          accessories a 
        WHERE
          a.deleted_by IS NULL
      ")->result();

      $get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

      $action_app = 'Approve';

      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'nm_sales' => $session['nm_lengkap'],
        'data_penawaran' => $get_penawaran,
        'data_penawaran_detail' => $get_penawaran_detail,
        'app_quote' => 1,
        'action_app' => $action_app,
        'curr' => $get_penawaran->currency,
        'list_other_cost' => $get_other_cost,
        'list_other_item' => $get_other_item,
        'list_another_item' => $get_list_item_others
      ]);
    } else {
      $this->template->set('results', [
        'customers' => $Cust,
        'user' => $User,
        'pic_cust' => $pic_cust,
        'list_penawaran_detail' => $get_penawaran_detail,
        'nm_sales' => $session['nm_lengkap']
      ]);
    }
    // $this->template->set('results', $get_penawaran);
    $this->template->title('Approval Cost Control');
    $this->template->render('approval_quotation');
  }

  public function save_approval()
  {
    $session = $this->session->userdata('app_session');

    $post = $this->input->post();

    $get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $post['no_surat']])->row();

    $this->db->trans_begin();

    $action = 'Approve';
    if ($post['pilih_action'] == '0') {
      $action = 'Reject';
    }

    $app_1 = '';
    $app_2 = '';
    $app_3 = '';
    $status = $get_penawaran->status;
    $keterangan_approve_reject = '';
    $keterangan_approve = '';
    if ($action == 'Reject') {
      $keterangan_approve = $post['keterangan_approve'];
    }

    $app_quote = 1;

    if ($get_penawaran->req_app1 == '1' && ($get_penawaran->app_1 == null || $get_penawaran->app_1 == '0')) {
      if ($action == 'Approve') {

        $app_1 = 1;
        if ($get_penawaran->req_app2 == '0' || $get_penawaran->req_app2 == null) {
          $status = 2;
        }
      } else {
        $app_1 = '';
        $status = '0';
      }

      $this->db->update('tr_penawaran', [
        'status' => $status,
        'app_1' => $app_1,
        'keterangan_app1' => $keterangan_approve,
        'approved_by' => $session['id_user'],
        'approved_on' => date('Y-m-d H:i:s')
      ], [
        'no_penawaran' => $post['no_surat']
      ]);
    }

    if ($get_penawaran->req_app2 == '1' && ($get_penawaran->app_2 == null || $get_penawaran->app_2 == '0') && $get_penawaran->app_1 == '1') {
      if ($action == 'Approve') {
        $app_2 = 1;
        if ($get_penawaran->req_app3 == '0' || $get_penawaran->req_app3 == null) {
          $status = 2;
        }
      } else {
        $app_2 = '';
        $status = '0';
      }

      $app_quote = 2;

      $this->db->update('tr_penawaran', [
        'status' => $status,
        'app_2' => $app_2,
        'keterangan_app2' => $keterangan_approve,
        'approved_by' => $session['id_user'],
        'approved_on' => date('Y-m-d H:i:s')
      ], [
        'no_penawaran' => $post['no_surat']
      ]);
    }

    if ($get_penawaran->req_app3 == '1' && ($get_penawaran->app_3 == null || $get_penawaran->app_3 == '0') && $get_penawaran->app_2 == '1') {
      if ($action == 'Approve') {
        $app_3 = 1;
        $status = 2;
      } else {
        $app_3 = '';
        $status = '0';
      }

      $app_quote = 3;

      $this->db->update('tr_penawaran', [
        'status' => $status,
        'app_3' => $app_3,
        'keterangan_app3' => $keterangan_approve,
        'approved_by' => $session['id_user'],
        'approved_on' => date('Y-m-d H:i:s')
      ], [
        'no_penawaran' => $post['no_surat']
      ]);
    }

    // print_r($app_1 . ' ' . $app_2 . ' ' . $app_3);
    // exit;

    if ($action == "Reject") {
      $this->db->update('tr_penawaran', [
        'status' => '0',
        'req_app1' => '',
        'req_app2' => '',
        'req_app3' => '',
        'app_1' => '',
        'app_2' => '',
        'app_3' => ''
      ], [
        'no_penawaran' => $post['no_surat']
      ]);
    }



    if ($this->db->trans_status() === FALSE) {
      $valid = 0;
      $msg = 'Maaf, penawaran gagal di ' . $action;
      $this->db->trans_rollback();
    } else {
      $valid = 1;
      $msg = 'Selamat, penawaran telah berhasil di ' . $action;
      $this->db->trans_commit();
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $msg,
      'app_quote' => $app_quote
    ]);
  }
}
