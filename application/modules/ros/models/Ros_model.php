<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ros_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $ENABLE_ADD     = has_permission('ROS.Add');
        $ENABLE_MANAGE  = has_permission('ROS.Manage');
        $ENABLE_VIEW    = has_permission('ROS.View');
        $ENABLE_DELETE  = has_permission('ROS.Delete');
    }

    function generate_no_ros($kode = '')
    {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM tr_ros WHERE id LIKE '%ROS-" . date('m-y') . "%'")->row();
        $kodeBarang = $generate_id->max_id;
        $urutan = (int) substr($kodeBarang, 10, 5);
        $urutan++;
        $tahun = date('m-y');
        $huruf = "ROS-";
        $kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

        return $kodecollect;
    }

    public function data_side_ros()
    {
        $ENABLE_ADD     = has_permission('ROS.Add');
        $ENABLE_MANAGE  = has_permission('ROS.Manage');
        $ENABLE_VIEW    = has_permission('ROS.View');
        $ENABLE_DELETE  = has_permission('ROS.Delete');

        $requestData    = $_REQUEST;
        $fetch          = $this->get_query_ros(
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

            $edit_btn = '<a href="ros/edit/' . $row['id'] . '" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>';
            if (!$ENABLE_MANAGE || $row['sts'] !== '0') {
                $edit_btn = '';
            }

            $del_btn = '<a href="javascript:void(0)" class="btn btn-sm btn-danger del_ros" style="margin-left: 0.5rem;" data-no_ros="' . $row['id'] . '"><i class="fa fa-trash"></i></a>';
            if (!$ENABLE_DELETE || $row['sts'] !== '0') {
                $del_btn = '';
            }

            $btn_req = '<button type="button" class="btn btn-sm btn-success req_payment" style="margin-left: 0.5rem;" title="Request Payment" data-no_ros="'.$row['id'].'"><i class="fa fa-arrow-up"></i></button>';
            if($row['sts'] !== '0'){
                $btn_req = '';
            }

            $view_btn = '<a href="'.base_url('ros/view/'.$row['id'].'').'" class="btn btn-sm btn-info" style="margin-left: 0.5rem;"><i class="fa fa-eye"></i></a>';

            $sts = '<div class="badge bg-yellow">Draft</div>';
            if ($row['sts'] == '1') {
                $sts = '<div class="badge bg-green">Request Payment</div>';
            }
            $get_paid = $this->db->get_where('payment_approve', ['no_doc' => $row['id'], 'status' => 2])->result();
            if(!empty($get_paid)) {
                $sts = '<div class="badge bg-light-blue">Paid</div>';
            }

            $other_cost = 0;
            $get_other_cost = $this->db->select('IF(SUM(a.nilai_cost) IS NULL, 0, SUM(a.nilai_cost)) AS ttl_other_cost')->get_where('tr_ros_custom_pib a', ['a.no_ros' => $row['id']])->row();
            if(!empty($get_other_cost)) {
                $other_cost = $get_other_cost->ttl_other_cost;
            }

            $nestedData   = array();
            $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
            $nestedData[]  = "<div align='left'>" . $row['id'] . "</div>";
            $nestedData[]  = "<div align='left'>" . $row['no_po'] . "</div>";
            $nestedData[]  = "<div align='left'>" . $row['nm_supplier'] . "</div>";
            $nestedData[]  = "<div align='center'>" . $row['no_pengajuan_pib'] . "</div>";
            $nestedData[]  = "<div align='center'>" . number_format($row['cost_bm'] + $row['cost_ppn'] + $row['cost_pph'] + $other_cost, 2) . "</div>";
            $nestedData[]  = "<div align='center'>" . $sts . "</div>";
            $nestedData[]  = "<div align='center'>" . $edit_btn . $del_btn . $view_btn . $btn_req . "</div>";

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

    public function get_query_ros($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {


        // $sql = "SELECT
        //       (@row:=@row+1) AS nomor,
        //       a.*,
        //       b.nm_customer
        //     FROM
        //       material_planning_base_on_produksi a
        //       LEFT JOIN customer b ON a.id_customer=b.id_customer,
        //       (SELECT @row:=0) r
        //     WHERE 1=1 AND a.category in ('pr material','base on production') AND a.booking_date IS NOT NULL AND (
        //       b.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
        //       OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
        //       OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
        //       OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
        //       OR a.no_rev LIKE '%" . $this->db->escape_like_str($like_value) . "%'
        //     )
        // ";
        $sql = "
            SELECT
                a.*
            FROM
                tr_ros a
            WHERE
                1=1 AND (
                    a.id LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                    a.no_po LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                    a.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
        ";
        // echo $sql; exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'id',
            2 => 'no_po',
            3 => 'nm_supplier'
        );

        $sql .= " ORDER BY a.created_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function list_po_no_ros()
    {
        $get_list_no_po = $this->db->query("
            SELECT
                a.no_surat as no_po
            FROM
                tr_purchase_order a
                LEFT JOIN tr_ros b ON b.no_po = a.no_surat
            WHERE
                b.no_po IS NULL
        ")->result_array();

        return $get_list_no_po;
    }

    public function list_custom_pib($no_ros = null)
    {
        $get_list_custom_pib = $this->db->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->result_array();

        return $get_list_custom_pib;
    }
}
