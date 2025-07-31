<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penerimaan_uang extends Admin_Controller
{
    protected $viewPermission = 'Penerimaan_Uang.View';
    protected $managePermission = 'Penerimaan_Uang.Manage';
    protected $addPermission = 'Penerimaan_Uang.Add';
    protected $deletePermission = 'Penerimaan_Uang.Delete';

    protected $consultant;
    protected $accounting;

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Penerimaan_uang/Penerimaan_uang_model'));

        $this->consultant = $this->load->database('consultant', true);
        $this->accounting = $this->load->database('accounting', true);
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Penerimaan Uang');
        $this->template->render('index');
    }

    public function add_penerimaan_uang($id)
    {
        $this->auth->restrict($this->viewPermission);

        $get_alokasi_detail = $this->db->get_where('tr_alokasi_detail', ['id' => $id])->row_array();

        $nominal_penerimaan_bank = ($get_alokasi_detail['nominal_debit'] < 1) ? $get_alokasi_detail['nominal_kredit'] : $get_alokasi_detail['nominal_debit'];

        $this->db->select('a.id_customer, a.nm_customer');
        $this->db->from('tr_invoicing a');
        $this->db->group_by('a.id_customer');
        $get_customer = $this->db->get()->result_array();

        $data = [
            'id_alokasi' => $id,
            'list_customer' => $get_customer,
            'nominal_penerimaan_bank' => $nominal_penerimaan_bank
        ];

        $this->template->set('results', $data);
        $this->template->title('Penerimaan Uang');
        $this->template->render('add');
    }

    public function get_inv_by_cust()
    {
        $id_customer = $this->input->post('id');

        $this->db->select('a.created_date, a.id as no_inv, a.nm_customer, a.total_nominal_jurnal, a.ppn_jurnal, a.pph_jurnal, a.total_akhir_jurnal');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id_customer', $id_customer);
        $get_invoice = $this->db->get()->result_array();

        $hasil = '';
        if (!empty($get_invoice)) {
            $no = 0;
            foreach ($get_invoice as $item) {
                $no++;

                $hasil .= '<tr>';

                $hasil .= '<td class="text-center">' . $no . '</td>';
                $hasil .= '<td class="text-center">' . $item['no_inv'] . '</td>';
                $hasil .= '<td class="text-left">' . $item['nm_customer'] . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['total_nominal_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['ppn_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['pph_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['total_akhir_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['total_akhir_jurnal']) . '</td>';
                $hasil .= '<td class="text-center">';
                $hasil .= '<input type="checkbox" name="choose_inv[]" value="' . $item['no_inv'] . '">';
                $hasil .= '</td>';

                $hasil .= '</tr>';
            }
        } else {
            $hasil = '<tr>';
            $hasil .= '<td colspan="9" class="text-center">No Data Found</td>';
            $hasil .= '</tr>';
        }

        echo $hasil;
    }

    public function process_alokasi()
    {
        $post = $this->input->post();

        $get_alokasi = $this->db->get_where('tr_alokasi_detail', ['id' => $post['id_alokasi']])->row_array();

        $uang_masuk = ($get_alokasi['nominal_debit'] < 0) ? $get_alokasi['nominal_kredit'] : $get_alokasi['nominal_debit'];



        $hasil = '';
        $hasil_jurnal = '';
        $total_piutang = 0;

        $total_debit = 0;
        $total_kredit = 0;

        $no = 0;
        foreach ($post['choose_inv'] as $item) {

            $no++;

            $get_inv = $this->db->get_where('tr_invoicing', ['id' => $item])->row_array();

            $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_inv['id_penawaran']])->row_array();
            $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();

            $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran['company']])->row_array();
            $id_company = (!empty($get_company)) ? $get_company['id'] : '';
            $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';

            $get_bank = $this->db->get_where('ms_bank', ['id' => $get_alokasi['tipe_bank']])->row_array();

            $this->accounting->select('a.nama as nm_coa');
            $this->accounting->from('coa_master a');
            $this->accounting->where('a.no_perkiraan', $get_bank['coa_bank']);
            $get_coa_bank = $this->accounting->get()->row_array();

            $coa_bank = (!empty($get_bank)) ? $get_bank['coa_bank'] : '';
            $nm_coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank['nm_coa'] : '';

            $hasil .= '<tr>';
            $hasil .= '<td class="text-center">';
            $hasil .= date('d-F-Y', strtotime($get_inv['created_date']));
            $hasil .= '<input type="hidden" name="id_inv_' . $no . '" value="' . $item . '">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">' . $get_inv['id'] . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['total_nominal_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['total_nominal_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['ppn_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['pph_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">';
            $hasil .= number_format($get_inv['total_akhir_jurnal']);
            $hasil .= '<input type="hidden" name="piutang_' . $no . '" value="' . $get_inv['total_akhir_jurnal'] . '">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-left">';
            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="penerimaan_' . $no . '" onkeyup="hitungAll()">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-left">';
            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="biaya_admin_' . $no . '" onkeyup="hitungAll()">';
            $hasil .= '</td>';
            $hasil .= '</tr>';

            $total_piutang += ($get_inv['total_akhir_jurnal']);

            if ($no == 1) {
                $hasil_jurnal .= '<tr>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= date('d-F-Y');
                $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_debit" value="' . date('Y-m-d') . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $coa_bank;
                $hasil_jurnal .= '<input type="hidden" name="coa_bank_debit" value="' . $coa_bank . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $nm_company;
                $hasil_jurnal .= '<input type="hidden" name="id_company_debit" value="' . $id_company . '">';
                $hasil_jurnal .= '<input type="hidden" name="nm_company_debit" value="' . $nm_company . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $nm_coa_bank;
                $hasil_jurnal .= '<input type="hidden" name="nm_coa_bank_debit" value="' . $nm_coa_bank . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center td_debit_bank_debit">';
                $hasil_jurnal .= number_format($uang_masuk);
                $hasil_jurnal .= '<input type="hidden" name="debit_bank_debit" value="' . $uang_masuk . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center td_kredit_bank_debit">';
                $hasil_jurnal .= number_format(0);
                $hasil_jurnal .= '<input type="hidden" name="kredit_bank_debit" value="' . 0 . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '</tr>';

                $total_debit += $uang_masuk;

                $arr_coa_jurnal = ['1030-10-1', '2010-30-2', '7010-20-5'];

                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
                $this->accounting->from('coa_master a');
                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
                $get_coa_jurnal = $this->accounting->get()->result_array();

                foreach ($get_coa_jurnal as $item_coa_jurnal) {

                    $value_debit = 0;
                    $value_kredit = 0;

                    if ($post['pph23_dipotong'] == '2' && $item_coa_jurnal['no_perkiraan'] == '2010-30-2') {
                        $this->db->select('a.pph_jurnal as ttl_kredit');
                        $this->db->from('tr_invoicing a');
                        $this->db->where('a.id', $item);
                        $get_kredit = $this->db->get()->row_array();

                        $value_kredit = $get_kredit['ttl_kredit'];
                    }

                    $hasil_jurnal .= '<tr>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= date('d-F-Y');
                    $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . date('Y-m-d') . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa_jurnal['no_perkiraan'];
                    $hasil_jurnal .= '<input type="hidden" name="coa_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $coa_bank . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $nm_company;
                    $hasil_jurnal .= '<input type="hidden" name="id_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $id_company . '">';
                    $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $nm_company . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa_jurnal['nm_coa'];
                    $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $item_coa_jurnal['nm_coa'] . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center td_debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';
                    $hasil_jurnal .= number_format($value_debit);
                    $hasil_jurnal .= '<input type="hidden" name="debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $value_debit . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center td_kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';
                    $hasil_jurnal .= number_format($value_kredit);
                    $hasil_jurnal .= '<input type="hidden" name="kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $value_kredit . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '</tr>';

                    $total_debit += $value_debit;
                    $total_kredit += $value_kredit;
                }
            } else {
                $arr_coa_jurnal = ['1030-10-1', '2010-30-2', '7010-20-5'];

                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
                $this->accounting->from('coa_master a');
                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
                $get_coa_jurnal = $this->accounting->get()->result_array();

                foreach ($get_coa_jurnal as $item_coa_jurnal) {
                    $value_debit = 0;
                    $value_kredit = 0;

                    if ($post['pph23_dipotong'] == '2' && $item_coa_jurnal['no_perkiraan'] == '2010-30-2') {
                        $this->db->select('a.pph_jurnal as ttl_kredit');
                        $this->db->from('tr_invoicing a');
                        $this->db->where('a.id', $item);
                        $get_kredit = $this->db->get()->row_array();

                        $value_kredit = $get_kredit['ttl_kredit'];
                    }

                    $hasil_jurnal .= '<tr>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= date('d-F-Y');
                    $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . date('Y-m-d') . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa_jurnal['no_perkiraan'];
                    $hasil_jurnal .= '<input type="hidden" name="coa_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $coa_bank . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $nm_company;
                    $hasil_jurnal .= '<input type="hidden" name="id_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $id_company . '">';
                    $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $nm_company . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa_jurnal['nm_coa'];
                    $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $item_coa_jurnal['nm_coa'] . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center td_debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';
                    $hasil_jurnal .= number_format($value_debit);
                    $hasil_jurnal .= '<input type="hidden" name="debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $value_debit . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center td_kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';
                    $hasil_jurnal .= number_format($value_kredit);
                    $hasil_jurnal .= '<input type="hidden" name="kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $value_kredit . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '</tr>';

                    $total_debit += $value_debit;
                    $total_kredit += $value_kredit;
                }
            }
        }



        $data = [
            'hasil' => $hasil,
            'hasil_jurnal' => $hasil_jurnal,
            'total_piutang' => $total_piutang,
            'uang_masuk' => $uang_masuk,
            'no_inv' => $no,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
        ];
        $this->template->set($data);
        $this->template->render('process');
    }

    public function get_alokasi_penerimaan()
    {
        $this->Penerimaan_uang_model->get_alokasi_penerimaan();
    }
}
