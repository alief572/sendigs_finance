<?php

class Invoicing_model extends BF_Model
{
    protected $viewPermission     = 'Invoicing.View';
    protected $addPermission      = 'Invoicing.Add';
    protected $managePermission = 'Invoicing.Manage';
    protected $deletePermission = 'Invoicing.Delete';

    protected $consultant;

    public function __construct()
    {
        $this->consultant = $this->load->database('consultant', true);
    }

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM tr_invoicing WHERE id LIKE '%/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2++;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '-INV-' . int_to_roman(date('m')) . '-' . date('y');

        return $kode_trans;
    }

    public function get_data_spk()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.*, e.nm_company, c.nm_customer, d.nm_paket as nm_project, c.nm_project_leader, c.nm_sales');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = c.id_project');
        $this->db->join(DBCNL . '.kons_tr_company e', 'e.id = b.company', 'left');
        $this->db->where_in('a.tagih_mundur', [1, 2]);
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('c.nm_customer', $search['value'], 'both');
            $this->db->or_like('d.nm_paket', $search['value'], 'both');
            $this->db->or_like('c.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('c.nm_sales', $search['value'], 'both');
            $this->db->or_like('e.nm_company', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*, e.nm_company, c.nm_customer, d.nm_paket as nm_project, c.nm_project_leader, c.nm_sales');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = c.id_project');
        $this->db->join(DBCNL . '.kons_tr_company e', 'e.id = b.company', 'left');
        $this->db->where_in('a.tagih_mundur', [1, 2]);
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('c.nm_customer', $search['value'], 'both');
            $this->db->or_like('d.nm_paket', $search['value'], 'both');
            $this->db->or_like('c.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('c.nm_sales', $search['value'], 'both');
            $this->db->or_like('e.nm_company', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];
        $no = (0 + $start);

        foreach ($get_data->result() as $item) {
            $no++;

            $status = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
            if ($item->sts_invoice == 1) {
                $status = '<button type="button" class="btn btn-sm btn-success">Invoice Created</button>';
            }

            $option = '';
            if ($item->sts_invoice !== '1') {
                $option = '<a href="' . base_url('invoicing/add_invoice/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" title="Create Invoice"><i class="fa fa-pencil"></i></a>';
            } else {
                $get_invoicing = $this->db->get_where('tr_invoicing', ['id_actual_plan_tagih' => $item->id])->row();

                $option = '<a href="' . base_url('invoicing/view_invoicing/' . $get_invoicing->id) . '" class="btn btn-sm btn-info" title="View Invoice"><i class="fa fa-eye"></i></a>';

                $option .= ' <a href="' . base_url('invoicing/edit_invoicing/' . $get_invoicing->id) . '" class="btn btn-sm btn-success" title="Revisi Invoice"><i class="fa fa-pencil"></i></a>';
            }

            $hasil[] = [
                'no' => $no,
                'company' => $item->nm_company,
                'no_spk' => $item->id_spk_penawaran,
                'customer' => $item->nm_customer,
                'project' => $item->nm_project,
                'project_leader' => $item->nm_project_leader,
                'sales' => $item->nm_sales,
                'status' => $status,
                'option' => $option
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }
}
