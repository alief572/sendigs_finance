<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Model class for table "Budget Rutin" (Budget Stock)
 */
class Budget_rutin_model extends BF_Model
{
    protected $table_name = 'budget_rutin_header';
    protected $key        = 'id';

    protected $created_field = 'created_on';
    protected $modified_field = 'modified_on';

    protected $set_created = true;
    protected $set_modified = true;
    protected $soft_deletes = false;
    protected $date_format = 'datetime';
    protected $log_user = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function GetBudgetRutin()
    {
        $this->db->select('a.*, b.nm_gudang AS nm_dept, c.cost_center');
        $this->db->from('budget_rutin_header a');
        $this->db->join('warehouse b', 'a.department=b.id', 'left');
        $this->db->join('department_center c', 'a.department=c.id_dept', 'left');
        $this->db->group_by('a.code_budget');
        $this->db->order_by('a.department', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function GetBudgetRutinDetail($code_budget)
    {
        $this->db->select('a.*, b.stock_name nama_barang, b.spec spec1, c.nm_category nama_jenis, c.id AS id_type, b.id_unit AS id_satuan, z.code AS nm_satuan');
        $this->db->from("(select * from budget_rutin_detail where code_budget='" . $code_budget . "') a");
        $this->db->join('accessories b', 'a.id_barang=b.id', 'left');
        $this->db->join('accessories_category c', 'a.jenis_barang=c.id', 'right');
        $this->db->join('ms_satuan z', 'b.id_unit=z.id', 'left');
        $this->db->order_by('c.nm_category', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_category_budget_items($code_budget, $id_category)
    {
        $this->db->select('a.id as id_barang, a.id_category, a.id_stock, a.stock_name, a.spec, a.price_ref_use, a.id_unit, s.code as nm_satuan, COALESCE(d.kebutuhan_month, 0) as kebutuhan_month, COALESCE(d.total_price, 0) as total_price, d.keterangan');
        $this->db->from('accessories a');
        $this->db->join('ms_satuan s', 's.id = a.id_unit', 'left');
        $this->db->join('budget_rutin_detail d', "d.id_barang = a.id AND d.code_budget = " . $this->db->escape($code_budget) . " AND d.jenis_barang = " . intval($id_category), 'left');
        $this->db->where('a.id_category', $id_category);
        $this->db->where('a.deleted_date IS NULL');
        $this->db->order_by('a.stock_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_detail_budget($id_budget, $id_category = null)
    {
        $this->db->select('a.id, a.code_budget, a.jenis_barang, a.kebutuhan_month, a.keterangan, a.price_reference, a.total_price, b.stock_name, b.spec, c.code, cat.nm_category');
        $this->db->from('budget_rutin_detail a');
        $this->db->join('accessories b', 'b.id = a.id_barang');
        $this->db->join('accessories_category cat', 'cat.id = a.jenis_barang', 'left');
        $this->db->join('ms_satuan c', 'c.id = a.satuan', 'left');
        $this->db->where('a.code_budget', $id_budget);
        if (!empty($id_category)) {
            $this->db->where('a.jenis_barang', $id_category);
        }
        $this->db->order_by('cat.nm_category', 'ASC');
        $this->db->order_by('b.stock_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_data()
    {
        $draw   = $this->input->post('draw');
        $length = $this->input->post('length') ? intval($this->input->post('length')) : 10;
        $start  = $this->input->post('start') ? intval($this->input->post('start')) : 0;
        $search = $this->input->post('search');

        // Total count
        $this->db->from('warehouse w');
        $this->db->where("w.desc = 'stok' AND w.status = 'Y' AND w.nm_gudang != '-'");
        $total_records = $this->db->count_all_results();

        // Filtered count
        $this->db->from('warehouse w');
        $this->db->join('budget_rutin_header h', 'h.department = w.id', 'left');
        $this->db->where("w.desc = 'stok' AND w.status = 'Y' AND w.nm_gudang != '-'");
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('w.nm_gudang', $search['value']);
            $this->db->or_like('h.modified_on', $search['value']);
            $this->db->or_like('h.tanggal', $search['value']);
            $this->db->group_end();
        }
        $total_filtered = $this->db->count_all_results();

        // Data query
        $this->db->select('w.id AS id_warehouse, w.nm_gudang, h.code_budget, COALESCE(h.modified_on, h.created_on, h.tanggal) AS last_update_date');
        $this->db->from('warehouse w');
        $this->db->join('budget_rutin_header h', 'h.department = w.id', 'left');
        $this->db->where("w.desc = 'stok' AND w.status = 'Y' AND w.nm_gudang != '-'");
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('w.nm_gudang', $search['value']);
            $this->db->or_like('h.modified_on', $search['value']);
            $this->db->or_like('h.tanggal', $search['value']);
            $this->db->group_end();
        }

        $order = $this->input->post('order');
        if (!empty($order)) {
            $order_column = $order[0]['column'];
            $order_dir = $order[0]['dir'];
            $columns = [
                0 => 'w.id',
                1 => 'w.nm_gudang',
                2 => 'last_update_date'
            ];
            if (isset($columns[$order_column])) {
                $this->db->order_by($columns[$order_column], $order_dir);
            }
        } else {
            $this->db->order_by('w.nm_gudang', 'ASC');
        }

        $this->db->limit($length, $start);
        $query = $this->db->get();

        $data = [];
        $no = $start;
        foreach ($query->result() as $row) {
            $no++;
            $action = '';
            $code_budget = !empty($row->code_budget) ? $row->code_budget : 'BR-00002';

            if (has_permission('Budget_Rutin.Manage')) {
                $action .= '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Kelola / Edit Budget" onclick="edit_warehouse_budget(\'' . $code_budget . '\')"><i class="fa fa-edit"></i> Edit</a> ';
            }
            if (has_permission('Budget_Rutin.View')) {
                $action .= '<a class="btn btn-sm btn-success" href="' . base_url('budget_rutin/download_budget_stock/' . $code_budget) . '" title="Download Excel" target="_blank"><i class="fa fa-download"></i> Excel</a>';
            }

            $last_date_str = '-';
            if (!empty($row->last_update_date)) {
                $last_date_str = date('d-M-Y H:i', strtotime($row->last_update_date));
            }

            $data[] = [
                'no'          => $no,
                'warehouse'   => '<b>' . strtoupper($row->nm_gudang) . '</b>',
                'last_update' => $last_date_str,
                'action'      => $action
            ];
        }

        $output = [
            "draw"            => $draw,
            "recordsTotal"    => $total_records,
            "recordsFiltered" => $total_filtered,
            "data"            => $data,
        ];

        echo json_encode($output);
    }
}


