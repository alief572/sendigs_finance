<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2021, Harboens
 *
 * This is model class for table "Budget Rutin"
 */

class Budget_rutin_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'budget_rutin_header';
    protected $key        = 'id';

    /**
     * @var string Field name to use for the created time column in the DB table
     * if $set_created is enabled.
     */
    protected $created_field = 'created_on';

    /**
     * @var string Field name to use for the modified time column in the DB
     * table if $set_modified is enabled.
     */
    protected $modified_field = 'modified_on';

    /**
     * @var bool Set the created time automatically on a new record (if true)
     */
    protected $set_created = true;

    /**
     * @var bool Set the modified time automatically on editing a record (if true)
     */
    protected $set_modified = true;
    /**
     * @var string The type of date/time field used for $created_field and $modified_field.
     * Valid values are 'int', 'datetime', 'date'.
     */
    /**
     * @var bool Enable/Disable soft deletes.
     * If false, the delete() method will perform a delete of that row.
     * If true, the value in $deleted_field will be set to 1.
     */
    protected $soft_deletes = false;

    protected $date_format = 'datetime';

    /**
     * @var bool If true, will log user id in $created_by_field, $modified_by_field,
     * and $deleted_by_field.
     */
    protected $log_user = true;

    /**
     * Function construct used to load some library, do some actions, etc.
     */
    public function __construct()
    {
        parent::__construct();
    }

    function GetBudgetRutin()
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
    function GetBudgetRutinDetail($code_budget)
    {
        $this->db->select('a.*, b.stock_name nama_barang, b.spec spec1, c.nm_category nama_jenis, c.id AS id_type, b.id_unit AS id_satuan, z.code AS nm_satuan');
        $this->db->from("(select * from budget_rutin_detail where code_budget='" . $code_budget . "') a");
        $this->db->join('accessories b', 'a.id_barang=b.id', 'left');
        $this->db->join('accessories_category c', 'a.jenis_barang=c.id', 'right');
        $this->db->join('ms_satuan z', 'b.id_unit=z.id', 'left');
        // $this->db->where("c.id_type != 'I2000001'");
        $this->db->order_by('c.nm_category', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_detail_budget($id_budget)
    {
        $this->db->select('a.id, a.code_budget, a.kebutuhan_month, a.keterangan, a.price_reference, a.total_price , b.stock_name, b.spec, c.code');
        $this->db->from('budget_rutin_detail a');
        $this->db->join('accessories b', 'b.id = a.id_barang');
        $this->db->join('ms_satuan c', 'c.id = a.satuan');
        $this->db->where('a.code_budget', $id_budget);
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_data()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        // Total records before filtering
        $this->db->select('a.code_budget');
        $this->db->from('budget_rutin_header a');
        $this->db->join('warehouse b', 'a.department=b.id', 'left');
        $this->db->group_by('a.code_budget');
        $total_records = $this->db->get()->num_rows();

        // Total records after filtering
        $this->db->select('a.code_budget');
        $this->db->from('budget_rutin_header a');
        $this->db->join('warehouse b', 'a.department=b.id', 'left');
        
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.tanggal', $search['value']);
            $this->db->or_like('b.nm_gudang', $search['value']);
            $this->db->group_end();
        }

        $this->db->group_by('a.code_budget');
        $total_filtered = $this->db->get()->num_rows();

        // Data query
        $this->db->select('a.*, b.nm_gudang AS nm_dept');
        $this->db->from('budget_rutin_header a');
        $this->db->join('warehouse b', 'a.department=b.id', 'left');

        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.tanggal', $search['value']);
            $this->db->or_like('b.nm_gudang', $search['value']);
            $this->db->group_end();
        }

        $this->db->group_by('a.code_budget');
        
        $order = $this->input->post('order');
        if (!empty($order)) {
            $order_column = $order[0]['column'];
            $order_dir = $order[0]['dir'];
            $columns = array(
                0 => '',
                1 => 'a.tanggal',
                2 => 'b.nm_gudang',
                3 => 'a.rev'
            );
            if (isset($columns[$order_column])) {
                $this->db->order_by($columns[$order_column], $order_dir);
            }
        } else {
            $this->db->order_by('a.id', 'DESC');
        }

        $this->db->limit($length, $start);
        $query = $this->db->get();

        $data = array();
        $no = $start;
        foreach ($query->result() as $row) {
            $no++;
            $action = '';

            if (has_permission('Budget_Rutin.Manage')) {
                $action .= '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_data(\'' . $row->code_budget . '\')"><i class="fa fa-edit"></i></a> ';
            }
            if (has_permission('Budget_Rutin.Delete')) {
                $action .= '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_data(\'' . $row->code_budget . '\')"><i class="fa fa-trash"></i></a>';
            }

            $data[] = array(
                'no' => $no,
                'tanggal' => date('d-M-Y', strtotime($row->tanggal)),
                'warehouse' => $row->nm_dept,
                'rev' => $row->rev,
                'action' => $action
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total_records,
            "recordsFiltered" => $total_filtered,
            "data" => $data,
        );

        echo json_encode($output);
    }
}
