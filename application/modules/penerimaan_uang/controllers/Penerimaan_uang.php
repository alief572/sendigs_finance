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

        $list_bank = $this->Penerimaan_uang_model->getListBank();

        $data = [
            'list_bank' => $list_bank
        ];

        $this->template->set($data);
        $this->template->title('Penerimaan Piutang');
        $this->template->render('index');
    }

    public function add_penerimaan_uang($id)
    {
        $this->auth->restrict($this->viewPermission);

        $get_alokasi_detail = $this->db->get_where('tr_alokasi_detail', ['id' => $id])->row_array();

        $nominal_penerimaan_bank = ($get_alokasi_detail['nominal_debit'] < 1) ? ($get_alokasi_detail['nominal_kredit'] - $get_alokasi_detail['nilai_terpakai']) : ($get_alokasi_detail['nominal_debit'] - $get_alokasi_detail['nilai_terpakai']);

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
        $this->template->title('Penerimaan Piutang');
        $this->template->render('add');
    }

    public function get_inv_by_cust()
    {
        $id_customer = $this->input->post('id');

        $this->db->select('a.created_date, a.id as no_inv, a.nm_customer, a.total_nominal_jurnal, a.dpp_lain_lain_jurnal, a.ppn_jurnal, a.pph_jurnal, a.total_akhir_jurnal, a.tagihan_ppn_jurnal, a.saldo_piutang, a.no_invoice');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id_customer', $id_customer);
        $this->db->where('a.saldo_piutang >', 0);
        $get_invoice = $this->db->get()->result_array();

        $hasil = '';
        if (!empty($get_invoice)) {
            $no = 0;
            foreach ($get_invoice as $item) {
                $no++;

                $hasil .= '<tr>';

                $hasil .= '<td class="text-center">' . $no . '</td>';
                $hasil .= '<td class="text-center">' . $item['no_invoice'] . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['total_nominal_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['dpp_lain_lain_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['ppn_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['pph_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['tagihan_ppn_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['total_akhir_jurnal']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item['saldo_piutang']) . '</td>';
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

        $pph23_dipotong = $post['pph23_dipotong'];
        $ppn_dipotong = $post['ppn_dipotong'];

        $get_alokasi = $this->db->get_where('tr_alokasi_detail', ['id' => $post['id_alokasi']])->row_array();

        $uang_masuk = ($get_alokasi['nominal_debit'] < 0) ? $get_alokasi['nominal_kredit'] : $get_alokasi['nominal_debit'];



        $hasil = '';
        $hasil_jurnal = '';
        $total_piutang = 0;
        $total_piutang_dagang = 0;

        $total_debit = 0;
        $total_kredit = 0;

        $no = 0;
        foreach ($post['choose_inv'] as $item) {

            $no++;

            $get_inv = $this->db->get_where('tr_invoicing', ['id' => $item])->row_array();

            if ($get_inv['non_kons'] == '1') {
                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran_non_konsultasi', ['id_penawaran' => $get_inv['id_penawaran']])->row_array();

                $id_company = '1';
                $nm_company = 'STM-Vuca';
            } else {
                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_inv['id_penawaran']])->row_array();
                $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran['company']])->row_array();
                $id_company = (!empty($get_company)) ? $get_company['id'] : '';
                $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';
            }
            $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();

            $get_bank = $this->db->get_where('ms_bank', ['id' => $get_alokasi['tipe_bank']])->row_array();

            $this->accounting->select('a.nama as nm_coa');
            $this->accounting->from('coa_master a');
            $this->accounting->where('a.no_perkiraan', $get_bank['coa_bank']);
            $get_coa_bank = $this->accounting->get()->row_array();

            $coa_bank = (!empty($get_bank)) ? $get_bank['coa_bank'] : '';
            $nm_coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank['nm_coa'] : '';

            $saldo_piutang = $get_inv['saldo_piutang'];
            if ($pph23_dipotong == 'N') {
                $saldo_piutang = $get_inv['saldo_piutang_tanpa_pph'];
            }

            $hasil .= '<tr>';
            $hasil .= '<td class="text-center">';
            $hasil .= date('d-F-Y', strtotime($get_inv['tanggal_invoice']));
            $hasil .= '<input type="hidden" name="id_inv_' . $no . '" value="' . $item . '">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">' . $get_inv['no_invoice'] . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['total_nominal_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['dpp_lain_lain_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['ppn_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['pph_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['tagihan_ppn_jurnal']) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($get_inv['total_akhir_jurnal']) . '</td>';
            $hasil .= '<td>';
            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="piutang_dagang_' . $no . '" value="' . $saldo_piutang . '" onkeyup="hitungAll()" readonly>';
            $hasil .= '</td>';
            $hasil .= '<td class="text-left">';
            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="penerimaan_' . $no . '" onkeyup="hitungAll()">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-left">';
            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="biaya_admin_' . $no . '" onkeyup="hitungAll()">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-left">';
            $hasil .= '<input type="text" class="form-control form-control-sm text-right autonum" name="sisa_piutang_' . $no . '" onkeyup="hitungAll()" readonly>';
            $hasil .= '</td>';
            $hasil .= '</tr>';

            $total_piutang += ($get_inv['total_akhir_jurnal']);
            $total_piutang_dagang += $saldo_piutang;

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

                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', '1106-01-02'];

                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
                $this->accounting->from('coa_master a');
                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
                $get_coa_jurnal = $this->accounting->get()->result_array();

                foreach ($get_coa_jurnal as $item_coa_jurnal) {

                    $value_debit = 0;
                    $value_kredit = 0;

                    if ($post['pph23_dipotong'] == 'N' && $item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                        $this->db->select('a.pph_jurnal as ttl_kredit');
                        $this->db->from('tr_invoicing a');
                        $this->db->where('a.id', $item);
                        $get_kredit = $this->db->get()->row_array();

                        $value_kredit = $get_kredit['ttl_kredit'];
                    }

                    if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                        $value_kredit = $get_inv['total_akhir_jurnal'];
                        if ($post['pph23_dipotong'] == 'N') {
                            $value_kredit = $get_inv['tagihan_ppn_jurnal'];
                        }
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
                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', '1106-01-02'];

                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
                $this->accounting->from('coa_master a');
                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
                $get_coa_jurnal = $this->accounting->get()->result_array();

                foreach ($get_coa_jurnal as $item_coa_jurnal) {
                    $value_debit = 0;
                    $value_kredit = 0;

                    if ($post['pph23_dipotong'] == '2' && $item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                        $this->db->select('a.pph_jurnal as ttl_kredit');
                        $this->db->from('tr_invoicing a');
                        $this->db->where('a.id', $item);
                        $get_kredit = $this->db->get()->row_array();

                        $value_kredit = $get_kredit['ttl_kredit'];
                    }
                    if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                        $value_debit = $get_inv['total_akhir_jurnal'];
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
                    $hasil_jurnal .= $item_coa_jurnal['nm_coa'] . ' (' . $get_inv['id'] . ')';
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
            'total_piutang_dagang' => $total_piutang_dagang,
            'uang_masuk' => $uang_masuk,
            'id_alokasi' => $post['id_alokasi'],
            'no_inv' => $no,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit,
            'id_customer' => $post['customer'],
            'ppn_dipotong' => $post['ppn_dipotong'],
            'pph23_dipotong' => $post['pph23_dipotong'],
            'nominal_penerimaan_bank' => str_replace(',', '', $post['nominal_penerimaan_bank'])
        ];
        $this->template->set($data);
        $this->template->render('process');
    }

    public function save_penerimaan_piutang()
    {
        $post = $this->input->post();

        $id = $this->Penerimaan_uang_model->generate_id();

        // print_r($id);
        // exit;

        $this->consultant->select('a.id_customer, a.nm_customer');
        $this->consultant->from('customer a');
        $this->consultant->where('a.id_customer', $post['id_customer']);
        $get_customer = $this->consultant->get()->row_array();

        $get_alokasi = $this->db->get_where('tr_alokasi_detail', ['id' => $post['id_alokasi']])->row_array();
        $get_ms_bank = $this->db->get_where('ms_bank', ['id' => $get_alokasi['tipe_bank']])->row_array();

        $coa_bank = (!empty($get_ms_bank)) ? $get_ms_bank['coa_bank'] : '';

        $get_coa_bank = $this->accounting->get_where('coa_master', ['no_perkiraan' => $coa_bank])->row_array();
        $nm_coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank['nama'] : '';

        $nm_customer = (!empty($get_customer)) ? $get_customer['nm_customer'] : '';

        $arr_insert_header = [
            'no_surat' => $id,
            'id_alokasi' => $post['id_alokasi'],
            'id_customer' => $post['id_customer'],
            'nm_customer' => $nm_customer,
            'ppn_dipotong' => $post['ppn_dipotong'],
            'pph23_dipotong' => $post['pph23_dipotong'],
            'nominal_penerimaan_bank' => str_replace(',', '', $post['nominal_penerimaan_bank']),
            'created_by' => $this->session->userdata('id_user'),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $arr_insert_detail = [];
        $arr_update_inv = [];
        $arr_insert_jurnal = [];

        $total_penerimaan = 0;

        $no_jurnal = 0;
        for ($i = 1; $i <= $post['no_inv']; $i++) {

            $this->db->select('a.*');
            $this->db->from('tr_invoicing a');
            $this->db->where('a.id', $post['id_inv_' . $i]);
            $get_inv = $this->db->get()->row_array();

            if ($get_inv['non_kons'] == '1') {
                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran_non_konsultasi', ['id_penawaran' => $get_inv['id_penawaran']])->row_array();

                $id_company = '1';
                $nm_company = 'STM-Vuca';
            } else {
                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_inv['id_penawaran']])->row_array();
                $get_company = $this->consultant->get_where('kons_tr_company', $get_penawaran['company'])->row_array();

                $id_company = (!empty($get_company)) ? $get_company['id'] : '';
                $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';
            }

            $dpp = (!empty($get_inv)) ? $get_inv['total_nominal_jurnal'] : 0;
            $dpp_lain = (!empty($get_inv)) ? $get_inv['dpp_lain_lain_jurnal'] : 0;
            $ppn = (!empty($get_inv)) ? $get_inv['ppn_jurnal'] : 0;
            $tagihan_ppn = (!empty($get_inv)) ? $get_inv['tagihan_ppn_jurnal'] : 0;
            $pph = (!empty($get_inv)) ? $get_inv['pph_jurnal'] : 0;
            $total = (!empty($get_inv)) ? $get_inv['total_akhir_jurnal'] : 0;
            $saldo_piutang = (!empty($get_inv)) ? $get_inv['saldo_piutang'] : 0;
            $tgl_inv = (!empty($get_inv)) ? date('Y-m-d', strtotime($get_inv['created_date'])) : '';

            $penerimaan = str_replace(',', '', $post['penerimaan_' . $i]);
            $biaya_admin = str_replace(',', '', $post['biaya_admin_' . $i]);
            if ($biaya_admin == '') {
                $biaya_admin = 0;
            }

            $sisa_piutang = str_replace(',', '', $post['sisa_piutang_' . $i]);
            if ($sisa_piutang == '') {
                $sisa_piutang = 0;
            }

            $arr_insert_detail[] = [
                'id_header' => $id,
                'id_alokasi' => $post['id_alokasi'],
                'id_inv' => $post['id_inv_' . $i],
                'tgl_inv' => $tgl_inv,
                'id_customer' => $post['id_customer'],
                'nm_customer' => $nm_customer,
                'dpp' => $dpp,
                'dpp_lain' => $dpp_lain,
                'ppn' => $ppn,
                'tagihan_ppn' => $tagihan_ppn,
                'pph23' => $pph,
                'total' => $total,
                'penerimaan' => $penerimaan,
                'biaya_admin' => $biaya_admin,
                'sisa_piutang' => $sisa_piutang,
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];

            $arr_update_inv[] = [
                'id' => $post['id_inv_' . $i],
                'saldo_piutang' => ($saldo_piutang - $penerimaan)
            ];

            $total_penerimaan += $penerimaan;

            $arr_coa_jurnal = ['1102-01-01', '7201-01-04', '1106-01-02'];

            $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
            $this->accounting->from('coa_master a');
            $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
            $get_coa_jurnal = $this->accounting->get()->result_array();


            foreach ($get_coa_jurnal as $item_jurnal) {
                $no_jurnal++;

                if ($i == 1 && $no_jurnal == 1) {
                    $arr_insert_jurnal[] = [
                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),
                        'tgl_jurnal' => date('Y-m-d'),
                        'coa' => $coa_bank,
                        'id_company' => $id_company,
                        'nm_company' => $nm_company,
                        'nm_coa' => $nm_coa_bank,
                        'debit' => str_replace(',', '', $post['debit_bank_debit']),
                        'kredit' => str_replace(',', '', $post['kredit_bank_debit']),
                        'keterangan' => $nm_coa_bank . ' - ' . $post['id_inv_' . $i],
                        'sts' => '0',
                        'no_transaksi' => $post['id_inv_' . $i],
                        'jenis_transaksi' => 'Penerimaan Piutang',
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];

                    $no_jurnal++;
                    $arr_insert_jurnal[] = [
                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),
                        'tgl_jurnal' => date('Y-m-d'),
                        'coa' => $item_jurnal['no_perkiraan'],
                        'id_company' => $id_company,
                        'nm_company' => $nm_company,
                        'nm_coa' => $item_jurnal['nm_coa'],
                        'debit' => $post['debit_' . $item_jurnal['no_perkiraan'] . '_' . $i],
                        'kredit' => $post['kredit_' . $item_jurnal['no_perkiraan'] . '_' . $i],
                        'keterangan' => $item_jurnal['nm_coa'] . ' - ' . $post['id_inv_' . $i],
                        'sts' => '0',
                        'no_transaksi' => $post['id_inv_' . $i],
                        'jenis_transaksi' => 'Penerimaan Piutang',
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $arr_insert_jurnal[] = [
                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),
                        'tgl_jurnal' => date('Y-m-d'),
                        'coa' => $item_jurnal['no_perkiraan'],
                        'id_company' => $id_company,
                        'nm_company' => $nm_company,
                        'nm_coa' => $item_jurnal['nm_coa'],
                        'debit' => $post['debit_' . $item_jurnal['no_perkiraan'] . '_' . $i],
                        'kredit' => $post['kredit_' . $item_jurnal['no_perkiraan'] . '_' . $i],
                        'keterangan' => $item_jurnal['nm_coa'] . ' - ' . $post['id_inv_' . $i],
                        'sts' => '0',
                        'no_transaksi' => $post['id_inv_' . $i],
                        'jenis_transaksi' => 'Penerimaan Piutang',
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        $this->db->trans_begin();

        $insert_penerimaan_header = $this->db->insert('tr_penerimaan_piutang', $arr_insert_header);
        if (!$insert_penerimaan_header) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $insert_penerimaan_detail = $this->db->insert_batch('tr_penerimaan_piutang_detail', $arr_insert_detail);
        if (!$insert_penerimaan_detail) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $insert_jurnal = $this->db->insert_batch('tr_jurnal', $arr_insert_jurnal);
        if (!$insert_jurnal) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $update_inv = $this->db->update_batch('tr_invoicing', $arr_update_inv, 'id');
        if (!$update_inv) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $update_alokasi = $this->db->update('tr_alokasi_detail', ['nilai_terpakai' => $total_penerimaan], ['id' => $post['id_alokasi']]);
        if (!$update_alokasi) {
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
            $msg = 'Save penerimaan berhasil !';
        }

        $response = [
            'status' => $valid,
            'msg' => $msg
        ];

        echo json_encode($response);
    }

    public function detail()
    {
        $post = $this->input->post();

        $id = $post['id_alokasi'];

        $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang', ['id' => $id])->row_array();

        $this->db->select('a.*, b.no_invoice, b.total_nominal_jurnal, b.dpp_lain_lain_jurnal, b.ppn_jurnal, b.tagihan_ppn_jurnal, b.pph_jurnal, b.total_akhir_jurnal, b.saldo_piutang, c.no_surat');
        $this->db->from('tr_penerimaan_piutang_detail a');
        $this->db->join('tr_invoicing b', 'b.id = a.id_inv', 'left');
        $this->db->join('tr_penerimaan_piutang c', 'c.no_surat = a.id_header');
        $this->db->where('a.id_header', $get_penerimaan['no_surat']);
        $get_penerimaan_detail = $this->db->get()->result_array();

        $arr_id_inv = [];
        foreach ($get_penerimaan_detail as $item_detail) {
            $arr_id_inv[] = $item_detail['id_inv'];
        }

        $get_alokasi = $this->db->get_where('tr_alokasi_detail', ['id' => $get_penerimaan['id_alokasi']])->row_array();

        // $get_jurnal = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_penerimaan['id_inv'], 'jenis_transaksi' => 'Penerimaan Piutang'])->result_array();

        $this->db->select('a.*');
        $this->db->from('tr_jurnal a');
        $this->db->where_in('a.no_transaksi', $arr_id_inv);
        $this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
        $get_jurnal = $this->db->get()->result_array();

        $data = [
            'header' => $get_penerimaan,
            'detail' => $get_penerimaan_detail,
            'alokasi' => $get_alokasi,
            'list_jurnal' => $get_jurnal
        ];

        $this->template->set($data);
        $this->template->render('detail');
    }

    public function get_alokasi_penerimaan()
    {
        $this->Penerimaan_uang_model->get_alokasi_penerimaan();
    }
}
