<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Jurnal_Invoicing extends Admin_Controller
{
    protected $viewPermission     = 'Jurnal_Invoicing.View';
    protected $addPermission      = 'Jurnal_Invoicing.Add';
    protected $managePermission = 'Jurnal_Invoicing.Manage';
    protected $deletePermission = 'Jurnal_Invoicing.Delete';

    protected $consultant;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Jurnal_invoicing/Jurnal_invoicing_model',
            'Jurnal_invoicing/Jurnal_invoicing_nomor_model'
        ));
        $this->template->title('Jurnal');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
    }

    public function index()
    {

        $get_cust_jurnal = $this->Jurnal_invoicing_model->get_cust_jurnal();
        $get_no_invoice_jurnal = $this->Jurnal_invoicing_model->get_no_invoice_jurnal();
        $get_company_jurnal = $this->Jurnal_invoicing_model->get_company_jurnal();

        $data = [
            'list_customer' => $get_cust_jurnal,
            'list_no_invoice' => $get_no_invoice_jurnal,
            'list_company' => $get_company_jurnal
        ];

        $this->template->set($data);
        $this->template->title('Jurnal Invoicing');
        $this->template->render('index');
    }

    public function add_jurnal()
    {
        $id = $this->input->post('id');

        $get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $id])->row();

        $this->db->select('a.*');
        $this->db->from('tr_jurnal a');
        $this->db->where('a.no_transaksi', $get_jurnal->no_transaksi);
        $this->db->where('a.jenis_transaksi', $get_jurnal->jenis_transaksi);
        $this->db->where('a.sts <>', '1');
        $get_all_jurnal = $this->db->get()->result();

        $hasil = '<input type="hidden" name="id" value="' . $id . '">';

        $hasil .= '<table class="table table-striped">';
        $hasil .= '<thead>';
        $hasil .= '<tr>';
        $hasil .= '<td class="text-center">Tanggal</td>';
        $hasil .= '<td class="text-center">Tipe</td>';
        $hasil .= '<td class="text-center">No. COA</td>';
        $hasil .= '<td class="text-center">Keterangan</td>';
        $hasil .= '<td class="text-center">No. Reff</td>';
        $hasil .= '<td class="text-center">Debit</td>';
        $hasil .= '<td class="text-center">Kredit</td>';
        $hasil .= '</tr>';
        $hasil .= '</thead>';
        $hasil .= '<tbody>';

        $no = 0;
        $ttl_debit = 0;
        $ttl_kredit = 0;
        foreach ($get_all_jurnal as $item) {
            $no++;

            $hasil .= '<tr>';

            $hasil .= '<td class="text-center">';
            $hasil .= date('d F Y', strtotime($item->tgl_jurnal));
            $hasil .= '<input type="hidden" name="jurnal[' . $no . '][id]" value="' . $item->id . '">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">' . $item->jenis_transaksi . '</td>';
            $hasil .= '<td class="text-center">' . $item->coa . '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= '<textarea class="form-control form-control-sm" name="jurnal[' . $no . '][keterangan]">' . $item->keterangan . '</textarea>';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= $item->no_transaksi;
            $hasil .= '<input type="hidden" name="jurnal[' . $no . '][no_transaksi]">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= '<input type="input" class="form-control form-control-sm text-right" name="jurnal[' . $no . '][debit]" value="' . number_format($item->debit) . '" readonly>';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= '<input type="input" class="form-control form-control-sm text-right" name="jurnal[' . $no . '][kredit]" value="' . number_format($item->kredit) . '" readonly>';
            $hasil .= '</td>';

            $hasil .= '</tr>';

            $ttl_debit += $item->debit;
            $ttl_kredit += $item->kredit;
        }

        $hasil .= '</tbody>';
        $hasil .= '<tfoot>';
        $hasil .= '<tr>';
        $hasil .= '<th class="text-right" colspan="6">Total</th>';
        $hasil .= '<td class="text-right">';
        $hasil .= '<input type="text" class="form-control form-control-sm text-right" name="ttl_debit" value="' . number_format($ttl_debit) . '" readonly>';
        $hasil .= '</td>';
        $hasil .= '<td class="text-right">';
        $hasil .= '<input type="text" class="form-control form-control-sm text-right" name="ttl_kredit" value="' . number_format($ttl_kredit) . '" readonly>';
        $hasil .= '</td>';
        $hasil .= '</tr>';
        $hasil .= '</tfoot>';
        $hasil .= '</table>';

        echo $hasil;
    }

    public function fix_company()
    {
        $get_jurnal = $this->db->get_where('tr_jurnal', ['jenis_transaksi' => 'Penerimaan Piutang'])->result();

        $arr_update_jurnal = [];
        foreach ($get_jurnal as $item_jurnal) {
            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_jurnal->no_transaksi])->row();

            $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_invoicing->id_penawaran])->row();
            $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row();

            $id_company = (!empty($get_company)) ? $get_company->id : '';
            $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';


            $arr_update_jurnal[] = [
                'id' => $item_jurnal->id,
                'id_company' => $id_company,
                'nm_company' => $nm_company
            ];
        }

        $this->db->trans_begin();

        $update_jurnal = $this->db->update_batch('tr_jurnal', $arr_update_jurnal, 'id');
        if (!$update_jurnal) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            echo json_encode([
                'status' => 1,
                'msg' => 'Data has not been updated !'
            ]);
        } else {
            $this->db->trans_commit();

            echo json_encode([
                'status' => 1,
                'msg' => 'Data has been updated !'
            ]);
        }
    }

    public function modal_posting_jurnal()
    {
        $id = $this->input->post('id');
        $no_transaksi = $this->input->post('no_transaksi');
        $jenis_transaksi = $this->input->post('jenis_transaksi');

        $get_jurnal = $this->db->get_where('tr_jurnal', ['no_transaksi' => $no_transaksi, 'jenis_transaksi' => $jenis_transaksi])->result();

        $data = [
            'id' => $id,
            'jurnal_header' => $get_jurnal
        ];

        $this->load->view('posting_jurnal', $data);
    }

    public function download_excel()
    {
        $klien = $this->input->get('klien');
        $no_invoice = $this->input->get('no_invoice');
        $company = $this->input->get('company');

        $this->db->select('a.no_transaksi, a.id, a.tgl_jurnal, a.coa, a.nm_coa, a.debit, a.kredit, a.no_transaksi, a.jenis_transaksi, a.sts, b.nm_customer, b.nm_project, b.no_invoice, b.id_spk_penawaran, d.id as id_company, a.nm_company, e.name as nm_divisi');
        $this->db->from('tr_jurnal a');
        $this->db->join('tr_invoicing b', 'b.id = a.no_transaksi', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left');
        $this->db->join(DBHRIS . '.divisions e', 'e.id = c.id_divisi', 'left');
        $this->db->where('a.jenis_transaksi', 'Invoicing');
        $this->db->group_start();
        $this->db->where('a.debit >', 0);
        $this->db->or_where('a.kredit >', 0);
        $this->db->group_end();

        if (!empty($klien)) {
            $this->db->where('b.id_customer', $klien);
        }

        if (!empty($no_invoice)) {
            $this->db->where('b.no_invoice', $no_invoice);
        }

        if (!empty($company)) {
            $this->db->where('a.id_company', $company);
        }

        $this->db->group_by('a.no_transaksi, a.jenis_transaksi');

        $get_data_jurnal = $this->db->get()->result();

        $this->load->view('download_excel', ['list_jurnal' => $get_data_jurnal]);
    }

    public function save_posting_jurnal()
    {
        $this->Jurnal_invoicing_model->save_posting_jurnal();
    }

    public function update_sts_revisi_jurnal()
    {
        $this->Jurnal_invoicing_model->update_sts_revisi_jurnal();
    }

    public function get_data_jurnal_invoicing()
    {
        $this->Jurnal_invoicing_model->get_data_jurnal_invoicing();
    }
}
