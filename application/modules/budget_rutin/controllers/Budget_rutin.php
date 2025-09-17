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
        $id_satuan = (!empty($getSatuan[0]['id_unit_gudang'])) ? $getSatuan[0]['id_unit_gudang'] : 0;
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

    public function edit($id)
    {
        $data  = $this->Budget_rutin_model->find_by(array('code_budget' => $id));
        if (!$data) {
            $this->template->set_message("Invalid Budget Stock", 'error');
            redirect('budget_rutin');
        }
        $data_detail  = $this->Budget_rutin_model->GetBudgetRutinDetail($data->code_budget);
        $datdepartemen  = $this->All_model->GetWarehouseStok();
        $datcostcenter  = [];
        $this->template->set('data', $data);
        $this->template->set('data_detail', $data_detail);
        $this->template->set('datcostcenter', $datcostcenter);
        $this->template->set('datdepartemen', $datdepartemen);
        $this->template->title('Edit Budget Stock');
        $this->template->render('budget_rutin_form');
    }

    public function save_data()
    {
        $type           = $this->input->post("type");
        $id             = $this->input->post("id");
        $rev             = $this->input->post("rev");
        $department        = $this->input->post("department");
        $costcenter     = $this->input->post("costcenter");
        $jenis_barang    = $this->input->post("jenis_barang");
        $id_barang        = $this->input->post("id_barang");
        $kebutuhan_month = $this->input->post("kebutuhan_month");
        $satuan           = $this->input->post("satuan");
        $this->db->trans_begin();
        if ($type == "edit") {
            $data = array(
                'department' => $department,
                'costcenter' => $costcenter,
                'rev' => ($rev + 1),
                'modified_by' => $this->auth->user_id(),
                'modified_on' => date("Y-m-d h:i:s")
            );
            $this->All_model->dataUpdate('budget_rutin_header', $data, array('code_budget' => $id));
            $keterangan = "SUKSES, Edit data " . $id;
        } else {
            $id = $this->All_model->GetAutoGenerate('format_budget_rutin');
            $data =  array(
                'code_budget' => $id,
                'department' => $department,
                'costcenter' => $costcenter,
                'tanggal' => date('Y-m-d'),
                'rev' => 0,
                'created_by' => $this->auth->user_id(),
                'created_on' => date("Y-m-d h:i:s")
            );
            $this->All_model->dataSave('budget_rutin_header', $data);
            $keterangan = "SUKSES, New data " . $id;
        }
        $sql = $this->db->last_query();
        if (!empty($id_barang)) {
            $this->All_model->dataDelete('budget_rutin_detail', array('code_budget' => $id));
            for ($i = 0; $i < count($id_barang); $i++) {
                if ($kebutuhan_month[$i] > 0) {
                    $data_detail =  array(
                        'code_budget' => $id,
                        'jenis_barang' => $jenis_barang[$i],
                        'id_barang' => $id_barang[$i],
                        'kebutuhan_month' => str_replace(',', '', $kebutuhan_month[$i]),
                        'satuan' => $satuan[$i],
                    );
                    $this->All_model->dataSave('budget_rutin_detail', $data_detail);
                }
            }
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $result = false;
        } else {
            $this->db->trans_commit();
            $result = true;
        }
        $nm_hak_akses   = $this->managePermission;
        $kode_universal   = $id;
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, 1, $sql, 1);
        $param = array(
            'save' => $result,
            'id' => $id
        );
        echo json_encode($param);
    }

    function hapus_data($id)
    {
        $this->auth->restrict($this->deletePermission);
        if ($id != '') {
            $result         = true;
            $this->All_model->dataDelete('budget_rutin_detail', array('code_budget' => $id));
            $this->All_model->dataDelete('budget_rutin_header', array('code_budget' => $id));
            $keterangan     = "SUKSES, Delete data Budget " . $id;
            $status         = 1;
            $nm_hak_akses   = $this->deletePermission;
            $kode_universal = $id;
            $jumlah = 1;
            $sql            = $this->db->last_query();
        } else {
            $result         = 0;
            $keterangan     = "GAGAL, Delete data Budget " . $id;
            $status         = 0;
            $nm_hak_akses   = $this->deletePermission;
            $kode_universal = $id;
            $jumlah = 1;
            $sql            = $this->db->last_query();
        }
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
            'delete' => $result,
            'idx' => $id
        );
        echo json_encode($param);
    }
}
