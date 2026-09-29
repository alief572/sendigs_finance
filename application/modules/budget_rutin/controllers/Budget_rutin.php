<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Budget_rutin extends Admin_Controller
{

    protected $viewPermission   = "Budget_Rutin.View";
    protected $addPermission    = "Budget_Rutin.Add";
    protected $managePermission = "Budget_Rutin.Manage";
    protected $deletePermission = "Budget_Rutin.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Budget_rutin/Budget_rutin_model',
            'All/All_model'
        ));
        $this->template->title('Manage Data Budget Stock');
        // $this->template->page_icon('fa fa-table');
        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        //        $this->auth->restrict($this->viewPermission);
        $data = $this->Budget_rutin_model->GetBudgetRutin();
        $this->template->set('results', $data);
        $this->template->title('Budget Stock');
        $this->template->render('list');
    }

    public function get_cost_center($department)
    {
        $data = $this->All_model->GetCostCenter($department);
        echo json_encode($data);
        die();
    }

    public function get_material($id_type)
    {
        $data = $this->All_model->GetOneTable('accessories', array('id_category' => $id_type, 'deleted_date' => NULL, 'status' => '1'), 'stock_name');
        echo json_encode($data);
        die();
    }
    public function get_satuan($id_material)
    {
        $getSatuan = $this->db->get_where('accessories', array('id' => $id_material))->result_array();
        $id_satuan = (!empty($getSatuan[0]['id_unit'])) ? $getSatuan[0]['id_unit'] : 0;
        $data = $this->All_model->GetOneTable('ms_satuan', array('id' => $id_satuan));
        echo json_encode($data);
        die();
    }
    public function create()
    {
        $datdepartemen  = $this->All_model->GetWarehouseStok();
        $jenisrutin     = $this->All_model->GetOneTable('accessories_category', "deleted_date IS NULL", 'nm_category');
        $this->template->set('datdepartemen', $datdepartemen);
        $this->template->set('jenisrutin', $jenisrutin);
        $this->template->title('Budget Stock');
        $this->template->render('budget_rutin_form');
    }

    public function kompilasi()
    {
        $group_header = $this->db->query("SELECT a.department, a.costcenter, b.nm_gudang AS nm_dept, c.cost_center FROM budget_rutin_header a join warehouse b on a.department=b.id left join department_center c on a.costcenter=c.id GROUP BY a.department,a.costcenter, b.nm_gudang, c.cost_center")->result_array();
        $group_barang = $this->db->query("SELECT a.id_barang, a.jenis_barang, z.code AS satuan, b.nm_category as jenisbarang, c.stock_name AS nama, c.spec AS spec1, '' AS spec2 FROM budget_rutin_detail a join accessories_category b on a.jenis_barang=b.id join accessories c on a.id_barang=c.id left join ms_satuan z ON a.satuan=z.id GROUP BY a.id_barang, a.jenis_barang, a.satuan, b.nm_category, c.stock_name, c.spec ORDER BY jenisbarang ASC, nama")->result_array();
        $this->template->set('group_header', $group_header);
        $this->template->set('group_barang', $group_barang);
        $this->template->title('Kompilasi Budget Stock');
        $this->template->render('kompilasi');
    }

    public function edit($id = null)
    {
        $data = $this->Budget_rutin_model->find_by(array('code_budget' => $id));
        if (!$data) {
            $data = $this->Budget_rutin_model->find_by(array('department' => 1));
        }

        $code_budget = $data ? $data->code_budget : ($id ? $id : 'BR-00002');
        $warehouse   = $this->db->get_where('warehouse', ['id' => $data ? $data->department : 1])->row();
        
        $this->db->where('deleted_date IS NULL');
        $this->db->order_by('nm_category', 'ASC');
        $categories  = $this->db->get('accessories_category')->result();

        $items_by_cat = [];
        foreach ($categories as $cat) {
            $items_by_cat[$cat->id] = $this->Budget_rutin_model->get_category_budget_items($code_budget, $cat->id);
        }

        $this->template->set('id', $code_budget);
        $this->template->set('code_budget', $code_budget);
        $this->template->set('categories', $categories);
        $this->template->set('items_by_cat', $items_by_cat);
        $this->template->set('data', $data);
        $this->template->set('warehouse', $warehouse);
        $this->template->title('Budget Stock >> ' . (!empty($warehouse) ? strtoupper($warehouse->nm_gudang) : 'SENTRAL SISTEM'));
        $this->template->render('budget_rutin_form');
    }

    public function save_data()
    {
        $code_budget = $this->input->post("id");
        $items       = $this->input->post("items");

        if (empty($code_budget)) {
            $code_budget = 'BR-00002';
        }

        $this->db->trans_begin();

        // 1. Delete all existing details for this code_budget
        $this->db->where('code_budget', $code_budget);
        $this->db->delete('budget_rutin_detail');

        // 2. Insert items that have kebutuhan_month > 0
        if (!empty($items)) {
            $insert_batch = [];
            foreach ($items as $id_barang => $row) {
                $qty    = floatval(str_replace(',', '', $row['kebutuhan_month'] ?? 0));
                $price  = floatval(str_replace(',', '', $row['price_reference'] ?? 0));
                $total  = $qty * $price;
                $cat_id = intval($row['jenis_barang'] ?? 0);

                if ($qty > 0) {
                    $insert_batch[] = [
                        'code_budget'     => $code_budget,
                        'jenis_barang'    => $cat_id,
                        'id_barang'       => $id_barang,
                        'kebutuhan_month' => $qty,
                        'satuan'          => $row['satuan'] ?? '',
                        'price_reference' => $price,
                        'total_price'     => $total,
                        'keterangan'      => $row['keterangan'] ?? ''
                    ];
                }
            }

            if (!empty($insert_batch)) {
                $this->db->insert_batch('budget_rutin_detail', $insert_batch);
            }
        }

        // 3. Update header audit
        $this->db->set('rev', 'rev+1', FALSE);
        $this->db->set('modified_by', $this->auth->user_id());
        $this->db->set('modified_on', date('Y-m-d H:i:s'));
        $this->db->where('code_budget', $code_budget);
        $this->db->update('budget_rutin_header');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                'status' => 0,
                'save'   => false,
                'pesan'  => 'Gagal menyimpan budget stock!'
            ]);
        } else {
            $this->db->trans_commit();
            history("Update Budget Stock on " . $code_budget);
            echo json_encode([
                'status' => 1,
                'save'   => true,
                'pesan'  => 'Seluruh data Budget Stock berhasil disimpan!'
            ]);
        }
    }

    public function download_budget_stock($id_budget)
    {
        $get_data_detail = $this->Budget_rutin_model->get_detail_budget($id_budget);
        $header = $this->db->get_where('budget_rutin_header', ['code_budget' => $id_budget])->row();
        $warehouse = $this->db->get_where('warehouse', ['id' => $header ? $header->department : 1])->row();

        $data = [
            'data_detail' => $get_data_detail,
            'warehouse'   => $warehouse
        ];

        $this->load->view('excel_list_budget_detail', $data);
    }

    public function get_data() {
        $this->Budget_rutin_model->get_data();
    }
}

