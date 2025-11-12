<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Approval_non_po_model extends BF_Model
{

  protected $viewPermission   = 'Approval_Non_PO.View';
  protected $addPermission    = 'Approval_Non_PO.Add';
  protected $managePermission = 'Approval_Non_PO.Manage';
  protected $deletePermission = 'Approval_Non_PO.Delete';

  public function __construct()
  {
    parent::__construct();
  }

  public function get_data_non_po()
  {
    $post = $this->input->post();

    $draw = intval($post['draw']);
    $start = intval($post['start']);
    $length = intval($post['length']);
    $search = $post['search']['value'];
    $order_column = $post['order'][0]['column'];
    $order_dir = $post['order'][0]['dir'];

    $this->db->select('a.*');
    $this->db->from('tr_pr_non_po a');
    $this->db->where('a.sts', '0');

    $count_all = $this->db->count_all_results('', false);

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('a.no_non_po', $search, 'both');
      $this->db->or_like('a.no_pr', $search, 'both');
      $this->db->or_like('a.jenis_pr', $search, 'both');
      $this->db->group_end();
    }

    $count_filtered = $this->db->count_all_results('', false);

    $this->db->order_by('a.id', 'DESC');
    $this->db->limit($length, $start);

    $get_data = $this->db->get()->result();

    $no = (0 + $start);
    $hasil = array();
    foreach ($get_data as $row) {
      $no++;

      $btn_approve = '';
      $btn_reject = '';
      $btn_view = '';
      if (has_permission($this->managePermission)) {
        $btn_approve = '<button type="button" class="btn btn-success btn-sm btn-approve" data-id="' . $row->id . '" title="Approval Non PO"><i class="fa fa-check"></i></button>';

        $btn_reject = '<button type="button" class="btn btn-danger btn-sm btn-reject" data-id="' . $row->id . '" title="Reject Non PO"><i class="fa fa-times"></i></button>';

        if ($row->jenis_pr == 'pr stok') {
          $get_pr_header = $this->db->get_where('material_planning_base_on_produksi', array('no_pr' => $row->no_pr))->row();

          $btn_view = '<a href="' . base_url('request_pr_stok/detail_planning/' . $get_pr_header->so_number) . '" class="btn btn-sm btn-info" target="_blank" title="View PR"><i class="fa fa-eye"></i></a>';
        }
        if ($row->jenis_pr == 'pr departemen') {
          $get_pr_header = $this->db->get_where('rutin_non_planning_header', array('no_pr' => $row->no_pr))->row();

          $btn_view = '<a href="' . base_url('non_rutin/add/' . $get_pr_header->no_pengajuan . '/view') . '" class="btn btn-sm btn-info" target="_blank" title="View PR"><i class="fa fa-eye"></i></a>';
        }
        if ($row->jenis_pr == 'pr asset') {
          $get_pr_header = $this->db->get_where('tran_pr_header', array('no_pr' => $row->no_pr))->row();

          $btn_view = '<a href="' . base_url('pr_asset/view/' . $get_pr_header->id) . '" class="btn btn-sm btn-info" target="_blank" title="View PR"><i class="fa fa-eye"></i></a>';
        }
      }
      $action = $btn_approve . ' ' . $btn_reject . ' ' . $btn_view;

      $jenis_pr = '';
      if ($row->jenis_pr == 'pr stok') {
        $jenis_pr = '<span class="badge bg-primary">PR Stok</span>';
      }
      if ($row->jenis_pr == 'pr departemen') {
        $jenis_pr = '<span class="badge bg-green">PR Departemen</span>';
      }
      if ($row->jenis_pr == 'pr asset') {
        $jenis_pr = '<span class="badge bg-red">PR Asset</span>';
      }

      $nestedData = array();

      $nestedData['no'] = $no;
      $nestedData['no_non_po'] = $row->no_non_po;
      $nestedData['no_pr'] = $row->no_pr;
      $nestedData['tipe_pr'] = $jenis_pr;
      $nestedData['total_pr'] = number_format($row->total_pr, 2);
      $nestedData['action'] = $action;

      $hasil[] = $nestedData;
    }

    $response = array(
      'draw' => $draw,
      'recordsTotal' => $count_all,
      'recordsFiltered' => $count_filtered,
      'data' => $hasil,
    );

    echo json_encode($response);
  }
}
