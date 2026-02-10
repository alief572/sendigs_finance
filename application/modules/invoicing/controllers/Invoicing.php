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
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = b.company', 'left');
        $this->db->where('a.id', $id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $id_company = (!empty($get_actual_plan_tagih->id_company)) ? $get_actual_plan_tagih->id_company : '1';
        $nm_company = (!empty($get_actual_plan_tagih->nm_company)) ? $get_actual_plan_tagih->nm_company : 'STM-Vuca';

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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

            if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);
                $debit = ($total_nominal + $ppn - $pph);
            }

            if ($item_coa_jurnal['no_perkiraan'] == '2104-01-07') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);

                $kredit = $ppn;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $pph = ($total_nominal * 2 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);

                $kredit = $total_nominal;
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
            $hasil_jurnal .= $nm_company;
            $hasil_jurnal .= '<input type="hidden" name="id_company_' . $no_coa_jurnal . '" value="' . $id_company . '">';
            $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $no_coa_jurnal . '" value="' . $nm_company . '">';
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

    public function add_invoice_non_konsultasi($id_penawaran)
    {
        $this->auth->restrict($this->addPermission);

        $get_penawaran = $this->Invoicing_model->get_penawaran_non_konsultasi($id_penawaran);

        $this->template->set('data_penawaran', $get_penawaran);
        $this->template->title('Add Invoicing Non Konsultasi');
        $this->template->render('add_invoice_non_konsultasi');
    }

    public function add_invoice_vuca($id_actual_plan_tagih)
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

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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

            if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);
                $debit = ($total_nominal - $pph);
            }

            // if ($item_coa_jurnal['no_perkiraan'] == '2104-01-07') {
            //     $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
            //     $dpp_lain_lain = ($total_nominal * 11 / 12);
            //     $ppn = ($dpp_lain_lain * 12 / 100);

            //     $kredit = $ppn;
            // }

            if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $pph = ($total_nominal * 2 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);

                $kredit = $total_nominal;
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
        $this->template->title('Add Invoicing Vuca');
        $this->template->render('add_invoice_vuca');
    }

    public function view_invoicing($id_invoicing)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $this->db->select('a.*, c.nm_customer, c.address, COALESCE(d.id, e.id) as id_company, COALESCE(d.nm_company, e.nm_company) as nm_company');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = b.company', 'left');
        $this->db->join(DBCNL . '.kons_tr_company e', 'e.id = c.id_company', 'left');
        $this->db->where('a.id', $get_invoicing->id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $this->auth->restrict($this->viewPermission);

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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

            if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);
                $debit = ($total_nominal + $ppn - $pph);
            }

            if ($item_coa_jurnal['no_perkiraan'] == '2104-01-07') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);

                $kredit = $ppn;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $pph = ($total_nominal * 2 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);

                $kredit = $total_nominal;
            }

            $hasil_jurnal .= '<tr>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= date('d-F-Y', strtotime($get_invoicing->created_date));
            $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $no_coa_jurnal . '" value="' . date('Y-m-d', strtotime($get_invoicing->created_date)) . '">';
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
            'data_invoice' => $get_invoicing,
            'data_actual_plan_tagih' => $get_actual_plan_tagih,
            'hasil_jurnal' => $hasil_jurnal,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
        ];

        $this->template->title('View Invoice');
        $this->template->set($data);
        $this->template->render('view_invoice');
    }

    public function view_invoicing_vuca($id_invoicing)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $this->db->select('a.*, c.nm_customer, c.address, d.id as id_company, d.nm_company');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = b.company', 'left');
        $this->db->where('a.id', $get_invoicing->id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $this->auth->restrict($this->viewPermission);

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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

            if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);
                $debit = ($total_nominal - $pph);
            }

            if ($item_coa_jurnal['no_perkiraan'] == '2104-01-07') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);

                $kredit = 0;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $pph = ($total_nominal * 2 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);

                $kredit = $total_nominal;
            }

            $hasil_jurnal .= '<tr>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= date('d-F-Y', strtotime($get_invoicing->created_date));
            $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $no_coa_jurnal . '" value="' . date('Y-m-d', strtotime($get_invoicing->created_date)) . '">';
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
            'data_invoice' => $get_invoicing,
            'data_actual_plan_tagih' => $get_actual_plan_tagih,
            'hasil_jurnal' => $hasil_jurnal,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
        ];

        $this->template->title('View Invoice Vuca');
        $this->template->set($data);
        $this->template->render('view_invoice_vuca');
    }

    public function edit_invoicing($id_invoicing)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $this->db->select('a.*, c.nm_customer, c.address, COALESCE(d.id, e.id) as id_company, COALESCE(d.nm_company, e.nm_company) as nm_company');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = b.company', 'left');
        $this->db->join(DBCNL . '.kons_tr_company e', 'e.id = c.id_company', 'left');
        $this->db->where('a.id', $get_invoicing->id_actual_plan_tagih);
        $get_actual_plan_tagih = $this->db->get()->row();

        $this->auth->restrict($this->viewPermission);

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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

            if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);
                $debit = ($total_nominal + $ppn - $pph);
            }

            if ($item_coa_jurnal['no_perkiraan'] == '2104-01-07') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);

                $kredit = $ppn;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $pph = ($total_nominal * 2 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);

                $kredit = $total_nominal;
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
            'id_invoicing' => $id_invoicing,
            'data_invoice' => $get_invoicing,
            'data_actual_plan_tagih' => $get_actual_plan_tagih,
            'hasil_jurnal' => $hasil_jurnal,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
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

    public function print_invoicing_vuca($id_invoicing, $id_company = 1)
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

        $this->load->view('print_invoice_vuca', $data);
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
            'no_faktur' => $post['nomor_faktur'],
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
            'saldo_piutang' => $post['total_akhir_jurnal'],
            'saldo_piutang_tanpa_pph' => $post['total_tagihan_ppn'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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

    public function save_invoice_vuca()
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
            'no_faktur' => $post['nomor_faktur'],
            'total_nominal' => $post['total_nominal'],
            'dpp_nilai_lain' => $post['dpp_nilai_lain'],
            'pajak' => $post['pajak'],
            'total_akhir' => $post['total_akhir'],
            'total_nominal_jurnal' => $post['total_nominal_jurnal'],
            'dpp_lain_lain_jurnal' => $post['dpp_lain_lain'],
            'pph_jurnal' => $post['pph_jurnal'],
            'total_akhir_jurnal' => $post['total_akhir_jurnal'],
            'saldo_piutang' => $post['total_akhir_jurnal'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s'),
            'tipe_invoice' => '1'
        ];

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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
        try {
            $insert_invoicing = $this->db->insert('tr_invoicing', $arr_insert);
            $insert_invoicing_jurnal = $this->db->insert_batch('tr_jurnal', $arr_insert_jurnal);
            $update_actual_plan_tagih = $this->db->update('kons_tr_actual_plan_tagih', ['sts_invoice' => 1], ['id' => $post['id']]);

            $this->db->trans_commit();

            $response = [
                'status' => 1,
                'msg' => 'Data has been saved !'
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            $response = [
                'status' => 0,
                'msg' => $e->getMessage()
            ];

            echo json_encode($response);
        }
    }

    public function update_invoice()
    {
        $post = $this->input->post();

        $id = $post['id_invoicing'];

        $arr_update = [
            'tanggal_invoice' => $post['tanggal_invoice'],
            'no_invoice' => $post['nomor_invoice'],
            'no_po' => $post['no_po'],
            'no_faktur' => $post['nomor_faktur']
        ];

        $arr_coa_jurnal = ['1102-01-01', '1106-01-02', '2104-01-07', '4101-01-01'];

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

        $this->db->delete('tr_jurnal', ['no_transaksi' => $id, 'jenis_transaksi' => 'Invoicing']);

        $valid = 1;
        $msg = '';
        $update_invoice = $this->db->update('tr_invoicing', $arr_update, ['id' => $post['id_invoicing']]);

        $insert_jurnal = $this->db->insert_batch('tr_jurnal', $arr_insert_jurnal);

        if (!$update_invoice) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error($update_invoice)['message'];
        } else if (!$insert_jurnal) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error($insert_jurnal)['message'];
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

        try {
            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $post['id']])->row();

            $get_jurnal = $this->db->get_where('tr_jurnal', ['jenis_transaksi' => 'Invoicing', 'no_transaksi' => $post['id']])->result();

            if (!empty($get_jurnal) && $get_jurnal[0]->sts == '0') {
                $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $post['company']])->row();

                $id_company = (!empty($get_company->id)) ? $get_company->id : '';
                $nm_company = (!empty($get_company->nm_company)) ? $get_company->nm_company : '';

                $update_jurnal_company = $this->db->update('tr_jurnal', ['id_company' => $id_company, 'nm_company' => $nm_company], ['jenis_transaksi' => 'Invoicing', 'no_transaksi' => $post['id']]);
            }

            $update_inv = $this->db->update('tr_invoicing', ['print_keterangan' => $post['keterangan_print']], ['id' => $post['id']]);


            // if ($this->db->trans_status() === false) {
            //     $this->db->trans_rollback();

            //     $valid = 0;
            //     $msg = 'Please try again later !';
            // } else {
            //     $this->db->trans_commit();

            //     $valid = 1;
            //     $msg = 'Data has been updated !';
            // }

            $this->db->trans_commit();

            http_response_code(200);

            echo json_encode([
                'status' => 1,
                'msg' => 'Data has been saved !'
            ]);
        } catch (Exception $e) {
            $this->db->trans_rollback();

            http_response_code(500);

            $response = [
                'status' => 0,
                'msg' => $e->getMessage()
            ];

            echo json_encode($response);
        }
    }

    public function save_keterangan_print_vuca()
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

    public function _render_status_invoice_non_konsultasi($id_penawaran)
    {
        $get_invoice_non_kons = $this->Invoicing_model->get_invoice_non_kons($id_penawaran);

        $status = '<span class="badge bg-yellow">Draft</span>';
        if (count($get_invoice_non_kons) > 0) {
            $status = '<span class="badge bg-green">Invoice Created</span>';
        }

        return $status;
    }

    public function _render_action_invoice_non_kons($id_penawaran)
    {
        $get_invoice_non_kons = $this->Invoicing_model->get_invoice_non_kons($id_penawaran);

        $action = '<a href="' . base_url('invoicing/create_invoice_non_konsultasi/' . $id_penawaran) . '" class="btn btn-sm btn-warning" title="Create Invoice"><i class="fa fa-edit"></i></a>';
        if (count($get_invoice_non_kons) > 0) {
            $action = '<a href="' . base_url('invoicing/edit_invoice_non_kons/' . $get_invoice_non_kons[0]->id) . '" class="btn btn-sm btn-primary" title="Edit Invoice"><i class="fa fa-edit"></i></a>';

            $action .= ' <a href="' . base_url('invoicing/view_invoicing_non_kons/' . $get_invoice_non_kons[0]->id) . '" class="btn btn-sm btn-info" title="View Invoice" target="_blank"><i class="fa fa-eye"></i></a>';
        }
        return $action;
    }


    public function get_data_quotation_non_konsultasi()
    {
        $draw = $this->input->post('draw', true);
        $length = $this->input->post('length', true);
        $start = $this->input->post('start', true);
        $search = $this->input->post('search', true);

        $this->consultant->select('a.id_penawaran, a.tgl_quotation, a.pic_penawaran, a.id_customer, a.nm_customer, a.grand_total, a.keterangan_penawaran, a.input_date');
        $this->consultant->from('kons_tr_penawaran_non_konsultasi a');
        $this->consultant->where('a.sts_quot', '1');
        $this->consultant->where('a.sts_deal', '1');

        $db_clone = clone $this->consultant;
        $count_all = $db_clone->count_all_results();

        if (!empty($search['value'])) {
            $this->consultant->group_start();
            $this->consultant->like('a.id_penawaran', $search['value'], 'both');
            $this->consultant->or_like('a.nm_customer', $search['value'], 'both');
            $this->consultant->or_like('a.pic_penawaran', $search['value'], 'both');
            $this->consultant->or_like('a.keterangan_penawaran', $search['value'], 'both');
            $this->consultant->group_end();
        }

        $db_clone = clone $this->consultant;
        $count_filtered = $db_clone->count_all_results();

        $this->consultant->order_by('a.input_date', 'DESC');
        $this->consultant->limit($length, $start);

        $get_data = $this->consultant->get()->result();

        $data = [];
        $no = (0 + $start);
        foreach ($get_data as $item) {
            $no++;

            // $status = '<span class="badge bg-yellow">Draft</span>';
            $status = $this->_render_status_invoice_non_konsultasi($item->id_penawaran);

            // $action = '<a href="' . base_url('invoicing/create_invoice_non_konsultasi/' . $item->id_penawaran) . '" class="btn btn-sm btn-warning" title="Create Invoice"><i class="fa fa-edit"></i></a>';

            $action = $this->_render_action_invoice_non_kons($item->id_penawaran);

            $data[] = [
                'no' => $no,
                'id_quotation' => $item->id_penawaran,
                'date' => date('d-F-Y', strtotime($item->tgl_quotation)),
                'admin_sales' => $item->pic_penawaran,
                'penawaran' => $item->keterangan_penawaran,
                'customer' => $item->nm_customer,
                'grand_total' => number_format($item->grand_total),
                'status' => $status,
                'action' => $action
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filtered,
            'data' => $data
        ];
        echo json_encode($response);
    }

    public function create_invoice_non_konsultasi($id_penawaran)
    {
        $this->auth->restrict($this->addPermission);

        $get_penawaran = $this->Invoicing_model->get_penawaran_non_konsultasi($id_penawaran);
        $get_penawaran_detail = $this->Invoicing_model->get_detail_penawaran_non_konsultasi($id_penawaran);

        $get_jurnal = $this->Invoicing_model->jurnal_invoicing_non_konsultasi($id_penawaran);

        $data = [
            'data_penawaran' => $get_penawaran,
            'data_penawaran_detail' => $get_penawaran_detail,
            'hasil_jurnal' => $get_jurnal['hasil_jurnal'],
            'total_debit' => $get_jurnal['total_debit'],
            'total_kredit' => $get_jurnal['total_kredit']
        ];

        if (empty($get_penawaran)) {
            redirect('invoicing');
        } else {
            $this->template->title('Create Invoice Non Konsultasi');
            $this->template->set($data);
            $this->template->render('add_invoice_non_konsultasi');
        }
    }

    public function edit_invoice_non_kons($id_invoicing)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $id_penawaran = $get_invoicing->id_penawaran ?? '';

        $get_penawaran = $this->Invoicing_model->get_penawaran_non_konsultasi($id_penawaran);
        $get_penawaran_detail = $this->Invoicing_model->get_detail_penawaran_non_konsultasi($id_penawaran);

        $get_jurnal = $this->Invoicing_model->jurnal_invoicing_non_konsultasi($id_penawaran);

        $data = [
            'data_invoicing' => $get_invoicing,
            'data_penawaran' => $get_penawaran,
            'data_penawaran_detail' => $get_penawaran_detail,
            'hasil_jurnal' => $get_jurnal['hasil_jurnal'],
            'total_debit' => $get_jurnal['total_debit'],
            'total_kredit' => $get_jurnal['total_kredit']
        ];

        if (empty($get_penawaran) || empty($get_invoicing)) {
            redirect('invoicing');
        } else {
            $this->template->title('Edit Invoice Non Konsultasi');
            $this->template->set($data);
            $this->template->render('edit_invoice_non_konsultasi');
        }
    }

    public function view_invoicing_non_kons($id_invoicing)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $id_penawaran = $get_invoicing->id_penawaran ?? '';

        $get_penawaran = $this->Invoicing_model->get_penawaran_non_konsultasi($id_penawaran);
        $get_penawaran_detail = $this->Invoicing_model->get_detail_penawaran_non_konsultasi($id_penawaran);

        $get_jurnal = $this->Invoicing_model->jurnal_invoicing_non_konsultasi($id_penawaran);

        $data = [
            'data_invoicing' => $get_invoicing,
            'data_penawaran' => $get_penawaran,
            'data_penawaran_detail' => $get_penawaran_detail,
            'hasil_jurnal' => $get_jurnal['hasil_jurnal'],
            'total_debit' => $get_jurnal['total_debit'],
            'total_kredit' => $get_jurnal['total_kredit']
        ];

        if (empty($get_penawaran) || empty($get_invoicing)) {
            redirect('invoicing');
        } else {
            $this->template->title('View Invoice Non Konsultasi');
            $this->template->set($data);
            $this->template->render('view_invoice_non_konsultasi');
        }
    }


    public function save_invoice_non_konsultasi()
    {
        $id_penawaran = $this->input->post('id_penawaran', true);

        $this->db->trans_begin();

        try {
            $get_penawaran = $this->Invoicing_model->get_penawaran_non_konsultasi($id_penawaran);

            $id = $this->Invoicing_model->generate_id();

            $arr_insert = [
                'id' => $id,
                'id_actual_plan_tagih' => '0',
                'id_detail_plan_tagih' => '0',
                'id_penawaran' => $get_penawaran->id_penawaran,
                'id_spk_penawaran' => '',
                'id_customer' => $get_penawaran->id_customer,
                'nm_customer' => $get_penawaran->nm_customer,
                'address' => $get_penawaran->address,
                'id_project' => '',
                'nm_project' => $get_penawaran->keterangan_penawaran,
                'id_project_leader' => '',
                'nm_project_leader' => '',
                'id_sales' => '',
                'nm_sales' => $get_penawaran->nm_pic_penawaran,
                'tanggal_invoice' => $this->input->post('tanggal_invoice', true),
                'no_invoice' => $this->input->post('nomor_invoice', true),
                'no_po' => $this->input->post('nomor_po', true),
                'no_faktur' => $this->input->post('nomor_faktur', true),
                'total_nominal' => $this->input->post('total_nominal', true),
                'dpp_nilai_lain' => $this->input->post('dpp_nilai_lain', true),
                'pajak' => $this->input->post('pajak', true),
                'total_akhir' => $this->input->post('total_akhir', true),
                'total_nominal_jurnal' => $this->input->post('total_nominal_jurnal', true),
                'dpp_lain_lain_jurnal' => $this->input->post('dpp_lain_lain', true),
                'ppn_jurnal' => $this->input->post('ppn_jurnal', true),
                'tagihan_ppn_jurnal' => $this->input->post('total_tagihan_ppn', true),
                'pph_jurnal' => $this->input->post('pph_jurnal', true),
                'total_akhir_jurnal' => $this->input->post('total_akhir_jurnal', true),
                'saldo_piutang' => $this->input->post('total_akhir_jurnal', true),
                'saldo_piutang_tanpa_pph' => $this->input->post('total_tagihan_ppn', true),
                'non_kons' => '1',
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];

            $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

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
                $tgl_jurnal = $this->input->post('tgl_jurnal_' . $no_coa_jurnal, true);
                $coa_jurnal = $this->input->post('coa_jurnal_' . $no_coa_jurnal, true);
                $id_company = $this->input->post('id_company_' . $no_coa_jurnal, true);
                $nm_company = $this->input->post('nm_company_' . $no_coa_jurnal, true);
                $nm_coa = $this->input->post('nm_coa_' . $no_coa_jurnal, true);
                $debit = $this->input->post('debit_' . $no_coa_jurnal, true);
                $kredit = $this->input->post('kredit_' . $no_coa_jurnal, true);

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

            $insert_invoicing = $this->db->insert('tr_invoicing', $arr_insert);

            // Cek jika query insert pertama gagal
            if (!$insert_invoicing) {
                $error = $this->db->error(); // Ambil detail error database
                throw new Exception('Gagal insert invoicing: ' . $error['message']);
            }

            $insert_invoicing_jurnal = $this->db->insert_batch('tr_jurnal', $arr_insert_jurnal);

            // Cek jika batch insert kedua gagal
            if (!$insert_invoicing_jurnal) {
                $error = $this->db->error();
                throw new Exception('Gagal insert batch jurnal: ' . $error['message']);
            }

            $this->db->trans_commit();
            $valid = 1;
            $msg = 'Data has been saved !';

            http_response_code(200);
            echo json_encode([
                'status' => $valid,
                'msg' => $msg
            ]);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $response = [
                'status' => 0,
                'msg' => $e->getMessage()
            ];

            http_response_code(500);
            echo json_encode($response);
        }
    }


    public function update_invoice_non_konsultasi()
    {
        $id = $this->input->post('id_invoicing', true);
        $id_penawaran = $this->input->post('id_penawaran', true);

        $this->db->trans_begin();

        try {
            $arr_update = [
                'tanggal_invoice' => $this->input->post('tanggal_invoice'),
                'no_invoice' => $this->input->post('nomor_invoice'),
                'no_po' => $this->input->post('nomor_po'),
                'no_faktur' => $this->input->post('nomor_faktur')
            ];

            $arr_coa_jurnal = ['1102-01-01', '1106-01-02', '2104-01-07', '4101-01-01'];

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
                $tgl_jurnal = $this->input->post('tgl_jurnal_' . $no_coa_jurnal, true);
                $coa_jurnal = $this->input->post('coa_jurnal_' . $no_coa_jurnal, true);
                $id_company = $this->input->post('id_company_' . $no_coa_jurnal, true);
                $nm_company = $this->input->post('nm_company_' . $no_coa_jurnal, true);
                $nm_coa = $this->input->post('nm_coa_' . $no_coa_jurnal, true);
                $debit = $this->input->post('debit_' . $no_coa_jurnal, true);
                $kredit = $this->input->post('kredit_' . $no_coa_jurnal, true);


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

            $this->db->delete('tr_jurnal', ['no_transaksi' => $id, 'jenis_transaksi' => 'Invoicing']);

            $update_invoice = $this->db->update('tr_invoicing', $arr_update, ['id' => $id]);

            $insert_jurnal = $this->db->insert_batch('tr_jurnal', $arr_insert_jurnal);

            if (!$update_invoice) {
                $error = $this->db->error();
                throw new Exception('Gagal update invoicing: ' . $error['message']);
            }
            if (!$insert_jurnal) {
                $error = $this->db->error();
                throw new Exception('Gagal insert batch jurnal: ' . $error['message']);
            }
            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaksi database gagal (status false).");
            }

            $this->db->trans_commit();
            $valid = 1;
            $msg = 'Data has been updated!';

            $response = [
                'status' => $valid,
                'msg' => $msg
            ];
            echo json_encode($response);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $response = [
                'status' => 0,
                'msg' => $e->getMessage()
            ];

            http_response_code(500);
            echo json_encode($response);
        }
    }
}
