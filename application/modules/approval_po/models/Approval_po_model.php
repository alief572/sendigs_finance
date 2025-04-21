<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Approval_po_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $ENABLE_ADD     = has_permission('Approval_PO.Add');
    $ENABLE_MANAGE  = has_permission('Approval_PO.Manage');
    $ENABLE_VIEW    = has_permission('Approval_PO.View');
    $ENABLE_DELETE  = has_permission('Approval_PO.Delete');
  }

  public function get_data($table, $where_field = '', $where_value = '')
  {
    if ($where_field != '' && $where_value != '') {
      $query = $this->db->get_where($table, array($where_field => $where_value));
    } else {
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
  {
    if ($where_field != '' && $where_value != '') {
      $query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
    } else {
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function data_side_approval_pr_material()
  {

    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_approval_pr_material(
      $requestData['product'],
      $requestData['costcenter'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData      = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query          = $fetch['query'];

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    $GET_USER = get_list_user();
    foreach ($query->result_array() as $row) {
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }
      if ($asc_desc == 'desc') {
        $nomor = $urut1 + $start_dari;
      }

      if ($row['status_po'] > 1) {
        $status = "<div class='badge bg-green'>Approved</div>";
      } else {
        if ($row['reject_reason'] !== '') {
          $status = "<td><span class='badge bg-red'>Reject</span></td>";
        } else {
          $status = "<td><span class='badge bg-blue'>Waiting</span></td>";
        }
      }

      $approval_btn = '';
      if (has_permission('Approval_PO.Manage')) {
        $approval_btn = "<a class='btn btn-success btn-sm Approve' href='javascript:void(0)' title='Approval PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-check'></i></a>";
      }

      // print_r($approval_btn);

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='center'>" . $row['no_surat'] . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d F Y', strtotime($row['tanggal'])) . "</div>";
      $nestedData[]  = "<div align='center'>" . $row['nm_lengkap'] . "</div>";
      $nestedData[]  = "<div align='center'>" . $row['dibuat_tgl'] . "</div>";
      // $nestedData[]  = "<div align='center'>
      //   <a href='approval_po/po_approval/" . $row['no_po'] . "' class='btn btn-sm btn-success'><i class='fa fa-check'></i></a>
      //   <button type='button' class='btn btn-sm btn-info view' data-no_pr='" . $row['so_number'] . "'><i class='fa fa-eye'></i></button>
      //   </td></div>";
      $nestedData[]  = "<div align='center'>
        <a href='approval_po/po_approval/" . $row['no_po'] . "' class='btn btn-sm btn-success'><i class='fa fa-check'></i></a>
        </td></div>";

      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function get_query_approval_pr_material($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $costcenter_where = "";
    // if($costcenter != '0'){
    // $costcenter_where = " AND a.costcenter = '".$costcenter."'";
    // }

    $product_where = "";
    // if($product != '0'){
    // $product_where = " AND b.code_lv1 = '".$product."'";
    // }

    $sql = "SELECT
              a.*,
              b.so_number,
              b.po_date,
              a.status as status_po,
              d.nm_lengkap,
              DATE_FORMAT(a.created_on, '%d %M %Y') as dibuat_tgl
            FROM
              tr_purchase_order a
              LEFT JOIN material_planning_base_on_produksi b ON b.po_number = a.no_po
              LEFT JOIN customer c ON c.id_customer = b.id_customer
              LEFT JOIN users d ON d.id_user = a.created_by
            WHERE 1=1 AND a.status = '1' AND (
              a.no_po LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
              a.no_surat LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
              b.po_date LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
              d.nm_lengkap LIKE '%".$this->db->escape_like_str($like_value)."%' OR
              DATE_FORMAT(a.created_on, '%d %M %Y') LIKE '%".$this->db->escape_like_str($like_value)."%'
            ) GROUP BY a.no_po ORDER  BY a.created_on DESC
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
    );

    // $sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
