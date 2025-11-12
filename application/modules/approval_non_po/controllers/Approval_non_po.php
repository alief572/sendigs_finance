<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approval_non_po extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Approval_Non_PO.View';
  protected $addPermission    = 'Approval_Non_PO.Add';
  protected $managePermission = 'Approval_Non_PO.Manage';
  protected $deletePermission = 'Approval_Non_PO.Delete';

  protected $hris;

  public function __construct()
  {
    parent::__construct();

    $this->load->model(array('Approval_non_po/Approval_non_po_model'));
    date_default_timezone_set('Asia/Bangkok');

    // $this->id_user  = $this->auth->user_id();
    // $this->datetime = date('Y-m-d H:i:s');

    $this->hris = $this->load->database('hris', true);
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    history("View Approval Non PO");
    $this->template->title('Approval Non PO');
    $this->template->render('index');
  }

  public function approve_non_po()
  {
    $id = $this->input->post('id');

    $this->db->trans_begin();

    $arr_update_sts = [
      'sts' => '1'
    ];

    $valid = 1;

    $update_sts = $this->db->where('id', $id)->update('tr_pr_non_po', $arr_update_sts);
    if (!$update_sts) {
      $this->db->trans_rollback();

      $valid = 0;
      $msg = $this->db->error()['message'];
    }

    if ($this->db->trans_status() === false || $valid == '0') {
      if ($valid !== '0') {
        $this->db->trans_rollback();

        $valid = 0;
        $msg = 'Please try again later !';
      }
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $msg = 'Data has been approved !';
    }

    $response = [
      'status' => $valid,
      'msg' => $msg
    ];

    echo json_encode($response);
  }

  public function reject_modal()
  {
    $id = $this->input->post('id');

    $this->load->view('reject_reason', ['id' => $id]);
  }

  public function reject_non_po()
  {
    $id = $this->input->post('id');
    $reject_reason = $this->input->post('reject_reason');

    $this->db->trans_begin();

    $arr_update_sts = [
      'sts' => '2',
      'reject_reason' => $reject_reason
    ];

    $valid = 1;
    $msg = '';

    $update_sts = $this->db->where('id', $id)->update('tr_pr_non_po', $arr_update_sts);
    if (!$update_sts) {
      $this->db->trans_rollback();

      $valid = 0;
      $msg = $this->db->error()['message'];
    } else {
      $get_pr_non_po = $this->db->get_where('tr_pr_non_po', ['id' => $id])->row();

      if ($get_pr_non_po->jenis_pr == 'pr stok') {
        $arr_update_pr = [
          'metode_pembelian' => null,
          'app_1' => null,
          'app_2' => null,
          'app_3' => null,
          'sts_reject1' => 1,
          'sts_reject2' => 1,
          'sts_reject3' => 1,
          'app_1_by' => null,
          'app_2_by' => null,
          'app_3_by' => null,
          'app_1_date' => null,
          'app_2_date' => null,
          'app_3_date' => null,
          'sts_reject1_by' => $this->auth->user_id(),
          'sts_reject2_by' => $this->auth->user_id(),
          'sts_reject3_by' => $this->auth->user_id(),
          'reject_reason1' => $reject_reason,
          'reject_reason2' => $reject_reason,
          'reject_reason3' => $reject_reason,
          'app_post' => null,
          'rejected' => 1
        ];

        $this->db->where('no_pr', $get_pr_non_po->no_pr);
        $update_sts_pr = $this->db->update('material_planning_base_on_produksi', $arr_update_pr);

        if (!$update_sts_pr) {
          $this->db->trans_rollback();
          $valid = 0;
          $msg = $this->db->error()['message'];
        }
      }

      if ($get_pr_non_po->jenis_pr == 'pr departemen') {
        $arr_update_pr = [
          'metode_pembelian' => null,
          'app_1' => null,
          'app_2' => null,
          'app_3' => null,
          'sts_reject1' => 1,
          'sts_reject2' => 1,
          'sts_reject3' => 1,
          'app_1_by' => null,
          'app_2_by' => null,
          'app_3_by' => null,
          'app_1_date' => null,
          'app_2_date' => null,
          'app_3_date' => null,
          'sts_reject1_by' => $this->auth->user_id(),
          'sts_reject2_by' => $this->auth->user_id(),
          'sts_reject3_by' => $this->auth->user_id(),
          'reject_reason1' => $reject_reason,
          'reject_reason2' => $reject_reason,
          'reject_reason3' => $reject_reason,
          'app_post' => null,
          'rejected' => 1
        ];

        $this->db->where('no_pr', $get_pr_non_po->no_pr);
        $update_sts_pr = $this->db->update('rutin_non_planning_header', $arr_update_pr);
        if (!$update_sts_pr) {
          $this->db->trans_rollback();
          $valid = 0;
          $msg = $this->db->error()['message'];
        }
      }

      if ($get_pr_non_po->jenis_pr == 'pr asset') {
        $arr_update_pr = [
          'app_status_3' => 'D',
          'app_by_3' => $this->auth->user_id(),
          'app_date_3' => date('Y-m-d H:i:s'),
          'app_reason_3' => $reject_reason,
          'metode_pembelian' => null
        ];

        $this->db->where('no_pr', $get_pr_non_po->no_pr);
        $update_sts_pr = $this->db->update('tran_pr_header', $arr_update_pr);
        if (!$update_sts_pr) {
          $this->db->trans_rollback();
          $valid = 0;
          $msg = $this->db->error()['message'];
        }
      }
    }

    if ($this->db->trans_status() === false || $valid == '0') {
      if ($valid !== '0') {
        $this->db->trans_rollback();

        $valid = 0;
        $msg = 'Please try again later !';
      }
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $msg = 'Data has been rejected !';
    }

    $response = [
      'status' => $valid,
      'msg' => $msg
    ];

    echo json_encode($response);
  }

  public function get_data_non_po()
  {
    $this->auth->restrict($this->viewPermission);

    $this->Approval_non_po_model->get_data_non_po();
  }
}
