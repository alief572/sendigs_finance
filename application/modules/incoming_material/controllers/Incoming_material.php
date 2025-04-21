<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Incoming_material extends Admin_Controller
{

    protected $viewPermission     = 'Incoming.View';
    protected $addPermission      = 'Incoming.Add';
    protected $managePermission = 'Incoming.Manage';
    protected $deletePermission = 'Incoming.Delete';


    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model(array(
            'Incoming_material/Incoming_material_model',
            'Incoming_material/Master_model',
        ));

        $this->template->title('Incoming Material');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }


    //MATERIAL ADJUSTMENT
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');
        $data_Group    = $this->db->query('SELECT * FROM groups')->result_array();
        $pusat        = $this->db->query("SELECT * FROM warehouse WHERE 'desc'='pusat' ORDER BY urut ASC")->result_array();
        $no_po        = $this->db->query("
										SELECT a.no_po, a.no_surat, a.status, 'PO' as ket_,b.nama AS nm_supplier FROM tr_purchase_order a LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier WHERE a.status = '2' AND a.tipe IS NULL AND (SELECT IF(SUM(aa.qty_oke + aa.qty_ng) IS NULL, 0, SUM(aa.qty_oke + aa.qty_ng)) FROM tr_checked_incoming_detail aa WHERE aa.no_ipp = a.no_po) < (SELECT SUM(ab.qty) FROM dt_trans_po ab WHERE ab.no_po = a.no_po) AND (SELECT COUNT(ac.id) FROM dt_trans_po ac JOIN new_inventory_4 ca ON ca.code_lv4 = ac.idmaterial WHERE ac.no_po = a.no_po AND ac.idmaterial <> '') > 0 ORDER BY a.no_po ASC
										")->result_array();
        $list_po    = $this->db->group_by('no_ipp')->get_where('warehouse_adjustment', array('category' => 'incoming material'))->result_array();
        $data_gudang = $this->db->group_by('id_gudang_ke')->get_where('warehouse_adjustment', array('category' => 'incoming material'))->result_array();

        $list_supplier = $this->db->select('kode_supplier, nama')->get_where('new_supplier', ['deleted_by' => null])->result();

        $data = array(
            'title'            => 'Incoming Material',
            'action'        => 'index',
            'row_group'        => $data_Group,
            'list_po'        => $list_po,
            'data_gudang'    => $data_gudang,
            'pusat'            => $pusat,
            'no_po'            => $no_po,
            'list_supplier' => $list_supplier
        );
        // history('View Incoming Material');
        $this->template->set($data);
        $this->template->title('Incoming Material');
        $this->template->render('index');
    }

    public function server_side_incoming_material()
    {
        $this->Incoming_material_model->get_data_json_incoming_material();
    }

    public function modal_detail_adjustment()
    {
        $this->Incoming_material_model->modal_detail_adjustment();
    }

    public function modal_incoming_material()
    {
        $this->Incoming_material_model->modal_incoming_material();
    }

    public function process_in_material()
    {
        $this->Incoming_material_model->process_in_material();
    }

    public function incoming_list_po()
    {
        $kode_supplier = $this->input->post('kode_supplier');

        $no_po = $this->db->query("
            SELECT a.no_po, a.no_surat, a.status, 'PO' as ket_,b.nama AS nm_supplier FROM tr_purchase_order a LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier WHERE a.status = '2' AND a.tipe IS NULL AND a.id_suplier = '".$kode_supplier."' AND (SELECT IF(SUM(aa.qty_oke + aa.qty_ng) IS NULL, 0, SUM(aa.qty_oke + aa.qty_ng)) FROM tr_checked_incoming_detail aa WHERE aa.no_ipp = a.no_po) < (SELECT SUM(ab.qty) FROM dt_trans_po ab WHERE ab.no_po = a.no_po) AND (SELECT COUNT(ac.id) FROM dt_trans_po ac JOIN new_inventory_4 ca ON ca.code_lv4 = ac.idmaterial WHERE ac.no_po = a.no_po AND ac.idmaterial <> '') > 0 AND a.close_po IS NULL ORDER BY a.no_po ASC
            ")
            ->result();
        
        $hasil = '';
        foreach($no_po as $item) {

            $no_pr = [];
            $get_no_pr = $this->db->query("
                SELECT
                    d.no_pr as no_pr
                FROM
                    dt_trans_po a
                    JOIN tr_purchase_order b ON b.no_po = a.no_po
                    JOIN material_planning_base_on_produksi_detail c ON c.id = a.idpr
                    JOIN material_planning_base_on_produksi d ON d.so_number = c.so_number
                WHERE
                    b.no_surat = '".$item->no_surat."' AND
                    (a.tipe IS NULL OR a.tipe = '')
                GROUP BY d.no_pr

                UNION ALL

                SELECT
                    c.no_pr as no_pr
                FROM
                    dt_trans_po a
                    JOIN tr_purchase_order b ON b.no_po = a.no_po
                    JOIN rutin_non_planning_detail c ON c.id = a.idpr
                WHERE
                    b.no_surat = '".$item->no_surat."' AND
                    a.tipe = 'pr depart'
                GROUP BY c.no_pr

            ")->result();
            foreach($get_no_pr as $item_no_pr) {
                $no_pr[] = $item_no_pr->no_pr;
            }

            if(!empty($no_pr)) {
                $no_pr = implode(', ', $no_pr);
            }else{
                $no_pr = '';
            }

            $hasil .= '<tr>';
            $hasil .= '<td class="text-center">'.$item->no_surat.'</td>'; 
            $hasil .= '<td class="text-center">'.$no_pr.'</td>';
            $hasil .= '<td class="text-center"><input type="checkbox" name="check_po[]" class="check_po" value="'.$item->no_po.'"></td>';
            $hasil .= '</tr>';
        }

        echo $hasil;
    }
}
