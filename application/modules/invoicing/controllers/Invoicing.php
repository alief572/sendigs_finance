<?php

class Invoicing extends Admin_Controller
{
    protected $viewPermission     = 'Invoicing.View';
    protected $addPermission      = 'Invoicing.Add';
    protected $managePermission = 'Invoicing.Manage';
    protected $deletePermission = 'Invoicing.Delete';

    protected $consultant;
    protected $accounting;

    public function __construct()
    {
        parent::__construct();
        $this->consultant = $this->load->database('consultant', true);
        $this->accounting = $this->load->database('accounting', true);

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
        $this->auth->restrict($this->viewPermission);

        $id_actual_plan_tagih = urldecode($id_actual_plan_tagih);
        $id_actual_plan_tagih = str_replace('|', '/', $id_actual_plan_tagih);

        $this->db->select('a.*, c.nm_customer, c.address, d.id as id_company, d.nm_company');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = b.company', 'left');
        $this->db->where('a.id', $id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $arr_coa_jurnal = ['1030-10-1', '4010-10-1', '2010-30-6'];

        $hasil_jurnal = '';

        $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $get_coa_jurnal = $this->accounting->get()->result_array();

        $no_coa_jurnal = 0;

        $total_debit = 0;
        $total_kredit = 0;
        foreach ($get_coa_jurnal as $item_coa_jurnal) {
            $no_coa_jurnal++;

            $debit = 0;
            $kredit = 0;

            if ($item_coa_jurnal['no_perkiraan'] == '1030-10-1') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $debit = ($total_nominal + $ppn);
            }
            if ($item_coa_jurnal['no_perkiraan'] == '4010-10-1') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $kredit = $total_nominal;
            }
            if ($item_coa_jurnal['no_perkiraan'] == '2010-30-6') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);

                $kredit = $ppn;
            }

            $hasil_jurnal .= '<tr>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= date('d-F-Y');
            $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $no_coa_jurnal . '" value="' . date('Y-m-d') . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $item_coa_jurnal['no_perkiraan'];
            $hasil_jurnal .= '<input type="hidden" name="coa_jurnal_' . $no_coa_jurnal . '" value="' . $item_coa_jurnal['no_perkiraan'] . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $get_actual_plan_tagih->nm_company;
            $hasil_jurnal .= '<input type="hidden" name="id_company_' . $no_coa_jurnal . '" value="' . $get_actual_plan_tagih->id_company . '">';
            $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $no_coa_jurnal . '" value="' . $get_actual_plan_tagih->nm_company . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $item_coa_jurnal['nm_coa'];
            $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $no_coa_jurnal . '" value="' . $item_coa_jurnal['nm_coa'] . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-right">';
            $hasil_jurnal .= number_format($debit);
            $hasil_jurnal .= '<input type="hidden" name="debit_' . $no_coa_jurnal . '" value="' . $debit . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-right">';
            $hasil_jurnal .= number_format($kredit);
            $hasil_jurnal .= '<input type="hidden" name="kredit_' . $no_coa_jurnal . '" value="' . $kredit . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '</tr>';

            $total_debit += $debit;
            $total_kredit += $kredit;
        }

        $data = [
            'data_actual' => $get_actual_plan_tagih,
            'hasil_jurnal' => $hasil_jurnal,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
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
            'dpp_lain_lain_jurnal' => $post['dpp_lain_lain'],
            'ppn_jurnal' => $post['ppn_jurnal'],
            'tagihan_ppn_jurnal' => $post['total_tagihan_ppn'],
            'pph_jurnal' => $post['pph_jurnal'],
            'total_akhir_jurnal' => $post['total_akhir_jurnal'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $arr_coa_jurnal = ['1030-10-1', '4010-10-1', '2010-30-6'];

        $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $get_coa_jurnal = $this->accounting->get()->result_array();

        $arr_insert_jurnal = [];

        $no_coa_jurnal = 0;
        foreach ($get_coa_jurnal as $item) {
            $no_coa_jurnal++;

            $no_jurnal = $this->Invoicing_model->generate_id_invoice_jurnal($no_coa_jurnal);
            $keterangan = $item['nm_coa'] . ' - ' . $id;
            $tgl_jurnal = $post['tgl_jurnal_' . $no_coa_jurnal];
            $coa_jurnal = $post['coa_jurnal_' . $no_coa_jurnal];
            $id_company = $post['id_company_' . $no_coa_jurnal];
            $nm_company = $post['nm_company_' . $no_coa_jurnal];
            $nm_coa = $post['nm_coa_' . $no_coa_jurnal];
            $debit = $post['debit_' . $no_coa_jurnal];
            $kredit = $post['kredit_' . $no_coa_jurnal];


            $arr_insert_jurnal[] = [
                'no_jurnal' => $no_jurnal,
                'tgl_jurnal' => $tgl_jurnal,
                'coa' => $coa_jurnal,
                'id_company' => $id_company,
                'nm_company' => $nm_company,
                'nm_coa' => $nm_coa,
                'debit' => $debit,
                'kredit' => $kredit,
                'keterangan' => $keterangan,
                'sts' => 0,
                'no_transaksi' => $id,
                'jenis_transaksi' => 'Invoicing',
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
        }

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';

        $insert_invoicing = $this->db->insert('tr_invoicing', $arr_insert);
        if (!$insert_invoicing) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error($insert_invoicing)['message'];
        }

        $insert_invoicing_jurnal = $this->db->insert_batch('tr_jurnal', $arr_insert_jurnal);
        if (!$insert_invoicing_jurnal) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error($insert_invoicing_jurnal)['message'];
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

    public function save_keterangan_print()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $update_inv = $this->db->update('tr_invoicing', ['print_keterangan' => $post['keterangan_print']], ['id' => $post['id']]);
        if (!$update_inv) {
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

            $valid = 1;
            $msg = 'Data has been updated !';
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
