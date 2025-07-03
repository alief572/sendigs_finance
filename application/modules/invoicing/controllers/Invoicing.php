<?php

class Invoicing extends Admin_Controller
{
    protected $viewPermission     = 'Invoicing.View';
    protected $addPermission      = 'Invoicing.Add';
    protected $managePermission = 'Invoicing.Manage';
    protected $deletePermission = 'Invoicing.Delete';

    protected $consultant;

    public function __construct()
    {
        parent::__construct();
        $this->consultant = $this->load->database('consultant', true);

        $this->load->models(array(
            'Invoicing/Invoicing_model'
        ));
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_company a');
        $get_company = $this->consultant->get()->result();

        $this->template->set('data_company', $get_company);
        $this->template->title('Invoicing');
        $this->template->render('index');
    }

    public function add_invoice($id_actual_plan_tagih)
    {
        $id_actual_plan_tagih = urldecode($id_actual_plan_tagih);
        $id_actual_plan_tagih = str_replace('|', '/', $id_actual_plan_tagih);

        $this->db->select('a.*, c.nm_customer, c.address');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran');
        $this->db->where('a.id', $id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $this->auth->restrict($this->viewPermission);

        $data = [
            'data_actual' => $get_actual_plan_tagih
        ];

        $this->template->set($data);
        $this->template->title('Add Invoicing');
        $this->template->render('add_invoice');
    }

    public function view_invoicing($id_invoicing)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->where('a.id', $get_invoicing->id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $this->auth->restrict($this->viewPermission);

        $data = [
            'data_invoice' => $get_invoicing,
            'data_actual_plan_tagih' => $get_actual_plan_tagih
        ];

        $this->template->title('View Invoice');
        $this->template->set($data);
        $this->template->render('view_invoice');
    }

    public function edit_invoicing($id_invoicing)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->where('a.id', $get_invoicing->id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $this->auth->restrict($this->viewPermission);

        $data = [
            'id_invoicing' => $id_invoicing,
            'data_invoice' => $get_invoicing,
            'data_actual_plan_tagih' => $get_actual_plan_tagih
        ];

        $this->template->title('Edit Invoice');
        $this->template->set($data);
        $this->template->render('edit_invoice');
    }

    public function print_invoicing($id_invoicing, $id_company = 1)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->where('a.id', $get_invoicing->id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $this->auth->restrict($this->viewPermission);

        $data = [
            'id_invoicing' => $id_invoicing,
            'data_invoice' => $get_invoicing,
            'data_actual_plan_tagih' => $get_actual_plan_tagih,
            'id_company' => $id_company
        ];

        // $this->template->title('Print Invoice');
        // $this->template->set($data);
        // $this->template->render('edit_invoice');

        $this->load->view('print_invoice', $data);
    }

    public function save_invoice()
    {
        $post = $this->input->post();

        $get_actual_plan_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', ['id' => $post['id']])->row();

        $get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_actual_plan_tagih->id_spk_penawaran])->row();
        $get_penawaran = $this->db->get_where(DBCNL . '.kons_tr_penawaran', ['id_quotation' => $get_actual_plan_tagih->id_penawaran])->row();
        $get_konsultasi = $this->db->get_where(DBCNL . '.kons_master_konsultasi_header', ['id_konsultasi_h' => $get_spk_penawaran->id_project])->row();

        $id = $this->Invoicing_model->generate_id();

        $arr_insert = [
            'id' => $id,
            'id_actual_plan_tagih' => $get_actual_plan_tagih->id,
            'id_detail_plan_tagih' => $get_actual_plan_tagih->id_detail_plan_tagih,
            'id_penawaran' => $get_actual_plan_tagih->id_penawaran,
            'id_spk_penawaran' => $get_actual_plan_tagih->id_spk_penawaran,
            'id_customer' => $get_spk_penawaran->id_customer,
            'nm_customer' => $get_spk_penawaran->nm_customer,
            'address' => $get_spk_penawaran->address,
            'id_project' => $get_spk_penawaran->id_project,
            'nm_project' => $get_konsultasi->nm_paket,
            'id_project_leader' => $get_spk_penawaran->id_project_leader,
            'nm_project_leader' => $get_spk_penawaran->nm_project_leader,
            'id_sales' => $get_spk_penawaran->id_sales,
            'nm_sales' => $get_spk_penawaran->nm_sales,
            'tanggal_invoice' => $post['tanggal_invoice'],
            'no_invoice' => $post['nomor_invoice'],
            'no_po' => $post['nomor_po'],
            'total_nominal' => $post['total_nominal'],
            'dpp_nilai_lain' => $post['dpp_nilai_lain'],
            'pajak' => $post['pajak'],
            'total_akhir' => $post['total_akhir'],
            'total_nominal_jurnal' => $post['total_nominal_jurnal'],
            'ppn_jurnal' => $post['ppn_jurnal'],
            'pph_jurnal' => $post['pph_jurnal'],
            'total_akhir_jurnal' => $post['total_akhir_jurnal'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';

        $insert_invoicing = $this->db->insert('tr_invoicing', $arr_insert);
        if (!$insert_invoicing) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;

            $valid = 0;
            $msg = $this->db->error($insert_invoicing)['message'];
        }

        if ($valid == 1) {
            $update_actual_plan_tagih = $this->db->update('kons_tr_actual_plan_tagih', ['sts_invoice' => 1], ['id' => $post['id']]);

            if (!$update_actual_plan_tagih) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;

                $valid = 0;
                $msg = $this->db->error($update_actual_plan_tagih)['message'];
            } else {
                if ($this->db->trans_status() ===  false) {
                    $this->db->trans_rollback();

                    $valid = 0;
                    $msg = 'Please try again later !';
                } else {
                    $this->db->trans_commit();

                    $valid = 1;
                    $msg = 'Data has been saved !';
                }
            }
        } else {
            $valid = 0;
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function update_invoice()
    {
        $post = $this->input->post();

        $arr_update = [
            'tanggal_invoice' => $post['tanggal_invoice'],
            'no_invoice' => $post['nomor_invoice'],
            'no_po' => $post['no_po']
        ];

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';
        $update_invoice = $this->db->update('tr_invoicing', $arr_update, ['id' => $post['id_invoicing']]);

        if (!$update_invoice) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error($update_invoice)['message'];
        } else {
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();

                $valid = 0;
                $msg = 'Please try again later !';
            } else {
                $this->db->trans_commit();

                $valid = 1;
                $msg = 'Data has been updated !';
            }
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function get_data_spk()
    {
        $this->Invoicing_model->get_data_spk();
    }
}
