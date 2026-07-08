<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penerimaan_pph_23 extends Admin_Controller
{
    protected $viewPermission = 'Penerimaan_PPH_23.View';
    protected $managePermission = 'Penerimaan_PPH_23.Manage';
    protected $addPermission = 'Penerimaan_PPH_23.Add';
    protected $deletePermission = 'Penerimaan_PPH_23.Delete';

    protected $consultant;
    protected $accounting;

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Penerimaan_pph_23/Penerimaan_pph_23_model'));

        $this->consultant = $this->load->database('consultant', true);
        $this->accounting = $this->load->database('accounting', true);
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $list_company = $this->consultant->get('kons_tr_company')->result();
        $this->template->set('list_company', $list_company);

        $this->template->title('Penerimaan PPH 23');
        $this->template->render('index');
    }

    public function add($id_penerimaan_piutang)
    {
        $this->auth->restrict($this->viewPermission);

        $this->db->select('a.*, b.print_keterangan, b.nm_project, b.id_penawaran, b.tipe_invoice, b.total_nominal, b.id_detail_plan_tagih, b.id_spk_penawaran');
        $this->db->from('tr_penerimaan_piutang_detail a');
        $this->db->join('tr_invoicing b', 'b.id = a.id_inv');
        $this->db->join('tr_penerimaan_piutang c', 'c.no_surat = a.id_header');
        $this->db->where('c.pph23_dipotong', 'Y');
        $this->db->where('a.id', $id_penerimaan_piutang);
        $get_data_penerimaan = $this->db->get()->row_array();

        if (empty($get_data_penerimaan['id_alokasi'])) {
            if (!empty($get_data_penerimaan['id_spk_penawaran'])) {
                $get_plan_tagih_detail = $this->db->get_where('kons_tr_plan_tagih_detail', ['id' => $get_data_penerimaan['id_detail_plan_tagih']])->row();

                $get_spk_penawaran = $this->consultant->select('a.*, b.nm_paket')
                    ->from('kons_tr_spk_penawaran a')
                    ->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left')
                    ->where('a.id_spk_penawaran', $get_plan_tagih_detail->id_spk_penawaran ?? '')
                    ->get()
                    ->row();

                $get_data_penerimaan['nm_customer'] = $get_spk_penawaran->nm_customer ?? '-';
                $get_data_penerimaan['nm_project'] = $get_spk_penawaran->nm_paket ?? '-';
                $get_data_penerimaan['print_keterangan'] = $get_plan_tagih_detail->desc_payment ?? '-';
            } else {
                $get_penawaran_non_kons = $this->consultant->select('a.*')
                    ->from('kons_tr_penawaran_non_konsultasi a')
                    ->where('a.id_penawaran', $get_data_penerimaan['id_penawaran'])
                    ->get()
                    ->row();

                $get_data_penerimaan['nm_customer'] = $get_penawaran_non_kons->nm_customer ?? '-';
                $get_data_penerimaan['nm_project'] = $get_penawaran_non_kons->keterangan_penawaran ?? '-';
                $get_data_penerimaan['print_keterangan'] = $get_penawaran_non_kons->keterangan_penawaran ?? '-';
            }
        }

        if ($get_data_penerimaan['pph23'] == 0) {
            if ($get_data_penerimaan['tipe_invoice'] == '1') {
                $get_data_penerimaan['pph23'] = $get_data_penerimaan['total_nominal'] * 0.5 / 100;
            } else {
                $get_data_penerimaan['pph23'] = $get_data_penerimaan['total_nominal'] * 2 / 100;
            }
        }

        $tipe_invoice = (!empty($get_data_penerimaan['tipe_invoice'])) ? $get_data_penerimaan['tipe_invoice'] : '';
        $coa_pph = ($tipe_invoice == '1') ? '1106-01-05' : '1106-01-02';
        $arr_coa_jurnal = [$coa_pph, '1102-01-01'];

        $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $get_coa_jurnal = $this->accounting->get()->result_array();

        $this->consultant->select('a.id, a.nm_company');
        $this->consultant->from('kons_tr_company a');
        $this->consultant->join('kons_tr_penawaran b', 'b.company = a.id');
        $this->consultant->where('b.id_quotation', $get_data_penerimaan['id_penawaran']);
        $get_company = $this->consultant->get()->row_array();

        $id_company = (!empty($get_company)) ? $get_company['id'] : '';
        $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';

        $data = [
            'id_detail_penerimaan' => $id_penerimaan_piutang,
            'id_company' => $id_company,
            'nm_company' => $nm_company
        ];

        $this->template->set('data_penerimaan', $get_data_penerimaan);
        $this->template->set('data_coa_jurnal', $get_coa_jurnal);
        $this->template->set($data);
        $this->template->title('Penerimaan PPH 23');
        $this->template->render('add');
    }

    public function save_penerimaan_pph_23()
    {
        $post = $this->input->post();

        $id = $this->Penerimaan_pph_23_model->generate_id();

        $filename = '';

        $config['upload_path'] = './uploads/penerimaan_pph_23/';
        $config['allowed_types'] = '*';
        $config['remove_spaces'] = TRUE;
        $config['encrypt_name'] = TRUE;

        $filenames = '';
        $this->upload->initialize($config);
        if ($this->upload->do_upload('upload_bukti_setor')) {
            $uploadData = $this->upload->data();
            $filenames = $uploadData['file_name'];
        } else {
            print_r($this->upload->display_errors());
            exit;
        }

        $arr_insert = [
            'id' => $id,
            'id_detail_penerimaan' => $post['id_detail_penerimaan'],
            'id_inv' => $post['no_invoice'],
            'id_customer' => $post['id_customer'],
            'nm_customer' => $post['customer'],
            'nm_project' => $post['project'],
            'keterangan_invoice' => $post['keterangan_invoice'],
            'nilai_pph' => str_replace(',', '', $post['nilai_pph']),
            'nilai_setor' => str_replace(',', '', $post['nilai_setor']),
            'upload_bukti_setor' => $filenames,
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $arr_insert_jurnal = [];

        $get_invoice = $this->db->get_where('tr_invoicing', ['id' => $post['no_invoice']])->row();
        $tipe_invoice = (!empty($get_invoice)) ? $get_invoice->tipe_invoice : '';
        $coa_pph = ($tipe_invoice == '1') ? '1106-01-05' : '1106-01-02';
        $arr_coa_jurnal = [$coa_pph, '1102-01-01'];

        $this->db->trans_begin();

        $insert_pph_23 = $this->db->insert('tr_penerimaan_pph_23', $arr_insert);
        if (!$insert_pph_23) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $update_penerimaan_piutang = $this->db->update('tr_penerimaan_piutang_detail', ['sts_pph_23' => 1], ['id' => $post['id_detail_penerimaan']]);
        if (!$update_penerimaan_piutang) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $this->Penerimaan_pph_23_model->create_jurnal($id);

            $valid = 1;
            $msg = 'Data has been saved !';
        }

        $response = [
            'status' => $valid,
            'msg' => $msg
        ];

        echo json_encode($response);
    }

    public function get_alokasi_penerimaan_pph23()
    {
        $this->Penerimaan_pph_23_model->get_alokasi_penerimaan_pph23();
    }
}
