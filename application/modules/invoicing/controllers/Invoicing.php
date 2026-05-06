<?php

class Invoicing extends Admin_Controller
{
    protected $viewPermission = 'Invoicing.View';
    protected $addPermission = 'Invoicing.Add';
    protected $managePermission = 'Invoicing.Manage';
    protected $deletePermission = 'Invoicing.Delete';
    protected $consultant;
    protected $accounting;
    protected $accounting_vuca;
    public function __construct()
    {
        parent::__construct();
        $this->consultant = $this->load->database('consultant', true);
        $this->accounting = $this->load->database('accounting', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);

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

        $this->accounting_vuca->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting_vuca->from('coa_master a');
        $this->accounting_vuca->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $get_coa_jurnal = $this->accounting_vuca->get()->result_array();

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
                $pph = ($total_nominal * 0.5 / 100);
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
                $pph = ($total_nominal * 0.5 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 0.5 / 100);

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

    public function edit_invoicing_vuca($id_invoicing)
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
                $pph = ($total_nominal * 0.5 / 100);
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
                $pph = ($total_nominal * 0.5 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $total_nominal = (!empty($get_actual_plan_tagih)) ? $get_actual_plan_tagih->nominal_payment : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 0.5 / 100);

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
        $this->template->render('edit_invoice_vuca');
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

    public function print_invoice_non_kons($id_invoicing, $id_company = 1)
    {
        $this->auth->restrict($this->viewPermission);

        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id_invoicing);
        $get_invoicing = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('tr_invoice_detail_non_kons a');
        $this->db->where('a.id_header', $id_invoicing);
        $get_invoice_detail = $this->db->get()->result();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_penawaran_non_konsultasi a');
        $this->db->from(DBCNL . '.kons_tr_penawaran_non_konsultasi a');
        $this->db->where('a.id_penawaran', $get_invoicing->id_penawaran);
        $get_penawaran = $this->db->get()->row();

        $data = [
            'id_invoicing' => $id_invoicing,
            'data_invoice' => $get_invoicing,
            'data_invoice_detail' => $get_invoice_detail,
            'data_penawaran' => $get_penawaran,
            'id_company' => $id_company
        ];


        // $this->template->title('Print Invoice');
        // $this->template->set($data);
        // $this->template->render('edit_invoice');

        $this->load->view('print_invoice_non_konsultasi', $data);
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
                if ($this->db->trans_status() === false) {
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
            'tagihan_ppn_jurnal' => $post['total_nominal'],
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

    public function save_keterangan_print_non_kons()
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

    public function get_data_spk()
    {
        $this->Invoicing_model->get_data_spk();
    }

    // public function _render_status_invoice_non_konsultasi($id_penawaran)
    // {
    //     $get_invoice_non_kons = $this->Invoicing_model->get_invoice_non_kons($id_penawaran);

    //     $status = '<span class="badge bg-yellow">Draft</span>';
    //     if (count($get_invoice_non_kons) > 0) {
    //         $status = '<span class="badge bg-green">Invoice Created</span>';
    //     }

    //     return $status;
    // }

    public function _render_status_invoice_non_kons($id)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id);
        $data_invoice = $this->db->get()->row();

        $status = '<div class="badge bg-green">Invoice Created</div>';
        if ($data_invoice->sts_close == '1') {
            $status = '<div class="badge bg-red">Closed</div>';
        }

        return $status;
    }

    public function _render_action_invoice_non_kons($id)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id);
        $data_invoice = $this->db->get()->row();

        $btn_view = '';
        if (has_permission($this->viewPermission)) {
            $btn_view = '<a href="' . base_url('invoicing/view_invoice_non_kons/' . $id) . '" class="btn btn-sm btn-info" title="View Invoice Non Konsultasi"><i class="fa fa-eye"></i></a>';
        }

        $btn_revisi = '';
        $btn_close = '';
        if (has_permission($this->managePermission)) {
            $btn_revisi = '<a href="' . base_url('invoicing/edit_invoice_non_kons/' . $id) . '" class="btn btn-sm btn-warning" title="Edit Invoice Non KOnsultasi"><i class="fa fa-pencil"></i></a>';

            $btn_close = '<button type="button" class="btn btn-sm btn-danger close_invoice_non_kons" data-id="' . $id . '" title="Close Invoice"><i class="fa fa-close"></i></button>';
        }

        $btn_print = '<a href="javascript:void(0);" class="btn btn-sm btn-primary pilih_print_inv_non_kons" data-id_inv="' . $id . '" title="Print Invoice Non Konsultasi" data-toggle="modal" data-target="#modal_print_non_kons"><i class="fa fa-print"></i></a>';
        if ($data_invoice->sts_close == '1') {
            $btn_print = '<a href="javascript:void(0);" class="btn btn-sm btn-primary pilih_print_inv_non_kons" data-id_inv="' . $id . '" title="Print Invoice Non Konsultasi" data-toggle="modal" data-target="#modal_print_non_kons"><i class="fa fa-print"></i></a>';

            $btn_view = '';
            $btn_revisi = '';
            $btn_close = '';
        }

        $buttons = $btn_view . ' ' . $btn_revisi . ' ' . $btn_print;

        return $buttons;
    }


    public function get_data_quotation_non_konsultasi()
    {
        $draw = $this->input->post('draw', true);
        $length = $this->input->post('length', true);
        $start = $this->input->post('start', true);
        $search = $this->input->post('search', true);

        $this->db->select('a.*, b.id_company, b.nm_company, b.keterangan_penawaran as penjualan, b.nm_pic_penawaran as pic');
        $this->db->from('tr_invoicing a');
        $this->db->join(DBCNL . '.kons_tr_penawaran_non_konsultasi b', 'b.id_penawaran = a.id_penawaran', 'left');
        $this->db->where('a.non_kons', '1');

        $count_all = $this->db->count_all_results('', false);

        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('b.nm_company', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('a.no_invoice', $search['value'], 'both');
            $this->db->or_like('a.id_penawaran', $search['value'], 'both');
            $this->db->or_like('b.keterangan_penawaran', $search['value'], 'both');
            $this->db->or_like('b.nm_pic_penawaran', $search['value'], 'both');
            $this->db->group_end();
        }

        $count_filtered = $this->db->count_all_results('', false);

        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $no = (0 + $start);
        $hasil = [];

        foreach ($get_data as $item) {
            $no++;

            $status = $this->_render_status_invoice_non_kons($item->id);

            $action = $this->_render_action_invoice_non_kons($item->id);

            $hasil[] = [
                'no' => $no,
                'no_invoice' => $item->no_invoice,
                'kepada' => $item->nm_customer,
                'company' => $item->nm_company,
                'no_penawaran' => $item->id_penawaran,
                'penjualan' => $item->penjualan,
                'pic' => $item->pic,
                'revisi' => $item->no_revisi,
                'status' => $status,
                'action' => $action
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filtered,
            'data' => $hasil
        ];

        echo json_encode($response);
    }

    public function create_invoice_non_konsultasi($id_penawaran)
    {
        $this->auth->restrict($this->addPermission);

        $get_penawaran = $this->Invoicing_model->get_penawaran_non_konsultasi($id_penawaran);
        $get_penawaran_detail = $this->Invoicing_model->get_detail_penawaran_non_konsultasi($id_penawaran);
        $total_invoiced = $this->Invoicing_model->total_invoiced_non_kons($id_penawaran);

        $get_jurnal = $this->Invoicing_model->jurnal_invoicing_non_konsultasi($id_penawaran);

        $data = [
            'data_penawaran' => $get_penawaran,
            'data_penawaran_detail' => $get_penawaran_detail,
            'total_invoiced' => $total_invoiced,
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

    public function edit_invoice_non_kons($id)
    {
        $data_invoicing = $this->Invoicing_model->get_invoice($id);
        $data_detail_invoice = $this->Invoicing_model->get_invoice_non_kons_detail($id);

        $data = [
            'data_invoicing' => $data_invoicing,
            'data_detail_invoice' => $data_detail_invoice
        ];

        $this->template->title('Edit Invoice Non Konsultasi');
        $this->template->set($data);
        $this->template->render('edit_invoice_non_konsultasi');
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
                'nm_customer' => $this->input->post('nm_customer', true),
                'address' => $this->input->post('address', true),
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
                'total_nominal' => str_replace(',', '', $this->input->post('dpp', true)),
                'dpp_nilai_lain' => str_replace(',', '', $this->input->post('dpp_lain_lain  ', true)),
                'pajak' => str_replace(',', '', $this->input->post('ppn', true)),
                'total_akhir' => str_replace(',', '', $this->input->post('total_tagihan_ppn', true)),
                'total_nominal_jurnal' => str_replace(',', '', $this->input->post('dpp', true)),
                'dpp_lain_lain_jurnal' => str_replace(',', '', $this->input->post('dpp_lain_lain', true)),
                'ppn_jurnal' => str_replace(',', '', $this->input->post('ppn', true)),
                'tagihan_ppn_jurnal' => str_replace(',', '', $this->input->post('total_tagihan_ppn', true)),
                'pph_jurnal' => str_replace(',', '', $this->input->post('pph', true)),
                'total_akhir_jurnal' => str_replace(',', '', $this->input->post('total_tagihan_all', true)),
                'saldo_piutang' => str_replace(',', '', $this->input->post('total_tagihan_all', true)),
                'saldo_piutang_tanpa_pph' => str_replace(',', '', $this->input->post('total_tagihan_ppn', true)),
                'non_kons' => '1',
                'biaya_kirim' => str_replace(',', '', $this->input->post('biaya_kirim', true)),
                'discount' => str_replace(',', '', $this->input->post('discount', true)),
                'ppn_consultant' => str_replace(',', '', $this->input->post('ppn_consultant', true)),
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];

            $item_all = $this->input->post('item', true);

            $arr_item_detail = [];
            foreach ($item_all as $item_detail) {
                if (!empty($item_detail['nama'])) {
                    $arr_item_detail[] = [
                        'id_header' => $id,
                        'nm_item' => $item_detail['nama'],
                        'qty' => $item_detail['qty'],
                        'harga' => str_replace(',', '', $item_detail['harga']),
                        'total' => str_replace(',', '', $item_detail['total']),
                        'input_by' => $this->auth->user_id(),
                        'input_at' => date('Y-m-d H:i:s')
                    ];
                }
            }

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
                if (!isset($coa_jurnal)) {
                    $coa_jurnal = $this->input->post('jurnal_invoice_no_coa', true);
                }
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

            $insert_detail_item = $this->db->insert_batch('tr_invoice_detail_non_kons', $arr_item_detail);
            if (!$insert_detail_item) {
                $error = $this->db->error(); // Ambil detail error database
                throw new Exception('Gagal insert detail item: ' . $error['message']);
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
        $id = $this->input->post('id', true);
        $id_penawaran = $this->input->post('id_penawaran', true);

        $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $id])->row();

        $this->db->trans_begin();

        try {
            $arr_update = [
                'nm_customer' => $this->input->post('nm_customer', true),
                'address' => $this->input->post('address', true),
                'tanggal_invoice' => $this->input->post('tanggal_invoice', true),
                'no_invoice' => $this->input->post('nomor_invoice', true),
                'no_po' => $this->input->post('nomor_po', true),
                'no_faktur' => $this->input->post('nomor_faktur', true),
                'no_revisi' => ($get_invoicing->no_revisi + 1),
                'total_nominal' => str_replace(',', '', $this->input->post('dpp', true)),
                'dpp_nilai_lain' => str_replace(',', '', $this->input->post('dpp_lain_lain  ', true)),
                'pajak' => str_replace(',', '', $this->input->post('ppn', true)),
                'total_akhir' => str_replace(',', '', $this->input->post('total_tagihan_ppn', true)),
                'total_nominal_jurnal' => str_replace(',', '', $this->input->post('dpp', true)),
                'dpp_lain_lain_jurnal' => str_replace(',', '', $this->input->post('dpp_lain_lain', true)),
                'ppn_jurnal' => str_replace(',', '', $this->input->post('ppn', true)),
                'tagihan_ppn_jurnal' => str_replace(',', '', $this->input->post('total_tagihan_ppn', true)),
                'pph_jurnal' => str_replace(',', '', $this->input->post('pph', true)),
                'total_akhir_jurnal' => str_replace(',', '', $this->input->post('total_tagihan_all', true)),
                'saldo_piutang' => str_replace(',', '', $this->input->post('total_tagihan_all', true)),
                'saldo_piutang_tanpa_pph' => str_replace(',', '', $this->input->post('total_tagihan_ppn', true))
            ];

            $arr_data_detail = [];
            $data_item = (isset($_POST['item'])) ? $this->input->post('item') : '';

            if (!empty($data_item)) {
                foreach ($data_item as $item) {
                    $arr_data_detail[] = [
                        'id_header' => $id,
                        'nm_item' => $item['nama'],
                        'qty' => $item['qty'],
                        'harga' => str_replace(',', '', $item['harga']),
                        'total' => str_replace(',', '', $item['total']),
                        'input_by' => $this->auth->user_id(),
                        'input_at' => date('Y-m-d H:i:s')
                    ];
                }
            }


            $arr_coa_jurnal = ['1102-01-01', '1106-01-02', '2104-01-07', $this->input->post('jurnal_invoice_no_coa')];

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
                if (!isset($coa_jurnal)) {
                    $coa_jurnal = $this->input->post('jurnal_invoice_no_coa', true);
                }
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
            $this->db->delete('tr_invoice_detail_non_kons', ['id_header' => $id]);

            $update_invoice = $this->db->update('tr_invoicing', $arr_update, ['id' => $id]);
            $insert_detail = $this->db->insert_batch('tr_invoice_detail_non_kons', $arr_data_detail);
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

    public function list_penawaran_non_kons()
    {
        $list_penawaran_non_kons = $this->Invoicing_model->get_list_penawaran_non_kons();
        $data = [
            'list_penawaran_non_kons' => $list_penawaran_non_kons
        ];

        $this->template->title('List Penawaran Non Konsultasi');
        $this->template->set($data);
        $this->template->render('list_penawaran_non_kons');
    }

    public function hitung_jurnal()
    {
        $get = $this->input->get();

        $id_penawaran = $get['id_penawaran'];
        $dpp = $get['dpp'];
        $dpp_lain_lain = $get['dpp_lain_lain'];
        $ppn = $get['ppn'];
        $pph = $get['pph'];
        $total_tagihan_all = $get['total_tagihan_all'];

        $id_invoice = (isset($get['id_invoice'])) ? $get['id_invoice'] : '';

        $get_penawaran_non_kons = $this->db->get_where(DBCNL . '.kons_tr_penawaran_non_konsultasi', ['id_penawaran' => $id_penawaran])->row();

        $id_company = (!empty($get_penawaran_non_kons)) ? $get_penawaran_non_kons->id_company : '';
        $nm_company = (!empty($get_penawaran_non_kons)) ? $get_penawaran_non_kons->nm_company : '';

        $arr_coa_jurnal = ['4101-01-03', '1102-01-01', '2104-01-07', '1106-01-02'];
        if (!empty($id_invoice)) {
            $this->db->select('a.coa');
            $this->db->from('tr_jurnal a');
            $this->db->where('a.no_transaksi', $id_invoice);
            $this->db->where('a.jenis_transaksi', 'Invoicing');
            $this->db->where_not_in('a.coa', ['1102-01-01', '2104-01-07', '1106-01-02']);
            $get_coa_other = $this->db->get()->row();

            $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', $get_coa_other->coa];
        }

        $hasil_jurnal = '';

        $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $this->accounting->order_by('a.no_perkiraan', 'asc');
        $get_coa_jurnal = $this->accounting->get()->result_array();

        $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', ['4101-01-03', '4101-01-07']);
        // $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $get_coa_jurnal_all = $this->accounting->get()->result_array();

        $no_coa_jurnal = 0;

        $total_debit = 0;
        $total_kredit = 0;

        foreach ($get_coa_jurnal as $item_coa_jurnal) {
            $no_coa_jurnal++;

            $debit = 0;
            $kredit = 0;

            if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                $debit = $total_tagihan_all;
            } else if ($item_coa_jurnal['no_perkiraan'] == '2104-01-07') {
                $kredit = $ppn;
            } else if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                $debit = $pph;
            } else {
                $kredit = $dpp;
            }

            $hasil_jurnal .= '<tr>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= date('d-F-Y');
            $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $no_coa_jurnal . '" value="' . date('Y-m-d') . '">';
            $hasil_jurnal .= '</td>';

            if (
                $item_coa_jurnal['no_perkiraan'] !== '1102-01-01' &&
                $item_coa_jurnal['no_perkiraan'] !== '2104-01-07' &&
                $item_coa_jurnal['no_perkiraan'] !== '1106-01-02'
            ) {
                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= '<select class="form-control form-control-sm select2" name="jurnal_invoice_no_coa" data-no="' . $no_coa_jurnal . '">';
                foreach ($get_coa_jurnal_all as $item_coa) {
                    $selected = '';
                    if (
                        $item_coa['no_perkiraan'] !== '1102-01-01' &&
                        $item_coa['no_perkiraan'] !== '2104-01-07' &&
                        $item_coa['no_perkiraan'] !== '1106-01-02' &&
                        $item_coa['no_perkiraan'] == $item_coa_jurnal['no_perkiraan']
                    ) {
                        $selected = 'selected';
                    }
                    $hasil_jurnal .= '<option value="' . $item_coa['no_perkiraan'] . '" ' . $selected . '>' . $item_coa['no_perkiraan'] . ' - ' . $item_coa['nm_coa'] . '</option>';
                }
                $hasil_jurnal .= '</select>';
                $hasil_jurnal .= '</td>';
            } else {
                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $item_coa_jurnal['no_perkiraan'];
                $hasil_jurnal .= '<input type="hidden" name="coa_jurnal_' . $no_coa_jurnal . '" value="' . $item_coa_jurnal['no_perkiraan'] . '">';
                $hasil_jurnal .= '</td>';
            }

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $nm_company;
            $hasil_jurnal .= '<input type="hidden" name="id_company_' . $no_coa_jurnal . '" value="' . $id_company . '">';
            $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $no_coa_jurnal . '" value="' . $nm_company . '">';
            $hasil_jurnal .= '</td>';

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                $hasil_jurnal .= '<td class="text-center colm_nm_coa">';
                $hasil_jurnal .= $item_coa_jurnal['nm_coa'];
                $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $no_coa_jurnal . '" value="' . $item_coa_jurnal['nm_coa'] . '">';
                $hasil_jurnal .= '</td>';
            } else {
                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $item_coa_jurnal['nm_coa'];
                $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $no_coa_jurnal . '" value="' . $item_coa_jurnal['nm_coa'] . '">';
                $hasil_jurnal .= '</td>';
            }

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

        $response = [
            'hasil_jurnal' => $hasil_jurnal,
            'total_debit' => number_format($total_debit),
            'total_kredit' => number_format($total_kredit)
        ];

        echo json_encode($response);
    }

    public function change_jurnal_invoice()
    {
        $no = $this->input->get('no', true);
        $no_coa = $this->input->get('no_coa', true);

        try {
            $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
            $this->accounting->from('coa_master a');
            $this->accounting->where('a.no_perkiraan', $no_coa);
            $this->accounting->order_by('a.no_perkiraan', 'asc');
            $get_coa_jurnal = $this->accounting->get()->row();

            if (!$get_coa_jurnal) {
                throw new Exception('Nomor COA tidak ditemukan !');
            }

            $nm_coa = (!empty($get_coa_jurnal->nm_coa)) ? $get_coa_jurnal->nm_coa : '';

            $hasil_jurnal = $nm_coa;
            $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $no . '" value="' . $nm_coa . '">';

            http_response_code(200);

            echo json_encode([
                'hasil' => $hasil_jurnal
            ]);
        } catch (Exception $e) {
            http_response_code(500);

            echo json_encode([
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function view_invoice_non_kons($id)
    {
        $data_invoice = $this->Invoicing_model->get_invoice($id);
        $data_item_invoice = $this->Invoicing_model->get_invoice_non_kons_detail($id);
        $data_jurnal_invoice = $this->Invoicing_model->get_view_jurnal_invoice_non_kons($id);

        $data = [
            'data_invoicing' => $data_invoice,
            'data_item_invoice' => $data_item_invoice,
            'list_jurnal_invoice' => (!empty($data_jurnal_invoice['hasil_jurnal'])) ? $data_jurnal_invoice['hasil_jurnal'] : '',
            'total_kredit' => (!empty($data_jurnal_invoice['total_kredit'])) ? $data_jurnal_invoice['total_debit'] : '',
            'total_debit' => (!empty($data_jurnal_invoice['total_debit'])) ? $data_jurnal_invoice['total_kredit'] : ''
        ];

        $this->template->title('View Invoice Non Konsultasi');
        $this->template->set($data);
        $this->template->render('view_invoice_non_konsultasi');
    }

    public function save_close_invoice()
    {
        $id_invoicing   = $this->input->post('id_invoicing', true);
        $alasan_closing = $this->input->post('alasan_closing', true);

        // Validasi input awal
        if (empty($id_invoicing)) {
            return $this->_send_response(400, 'Data invoice tidak ditemukan. Silakan muat ulang halaman.');
        }

        if (empty($alasan_closing)) {
            return $this->_send_response(400, 'Mohon isi alasan penutupan invoice terlebih dahulu.');
        }

        $this->db->trans_begin();

        try {
            $arr_update = [
                'sts_close'    => '1',
                'close_by'     => $this->auth->user_id(),
                'close_date'   => date('Y-m-d H:i:s'),
                'close_reason' => $alasan_closing
            ];

            $this->db->where('id', $id_invoicing);
            $this->db->update('tr_invoicing', $arr_update);

            // Cek apakah ada baris yang benar-benar terupdate
            if ($this->db->affected_rows() === 0) {
                throw new Exception('Gagal memperbarui status. Invoice mungkin sudah ditutup sebelumnya.');
            }

            $this->db->trans_commit();
            return $this->_send_response(200, 'Invoice telah berhasil ditutup dan tersimpan di sistem.');
        } catch (Exception $e) {
            $this->db->trans_rollback();
            // Ambil pesan dari exception atau pakai pesan default jika terjadi error database
            $msg = $e->getMessage() ?: 'Terjadi kendala teknis saat memproses permintaan Anda. Silakan coba beberapa saat lagi.';
            return $this->_send_response(500, $msg);
        }
    }



    public function close_penawaran_non_kons()
    {
        // 1. Ambil data input
        $id_penawaran = $this->input->post('id_penawaran', true);

        // Validasi awal: pastiin ID gak kosong
        if (empty($id_penawaran)) {
            return $this->_send_response(400, 'ID Penawaran tidak valid atau tidak terbaca.');
        }

        $this->db->trans_begin();

        try {
            // 2. Data yang mau diupdate
            $arr_update = [
                'sts_close'  => '1',
                'close_by'   => $this->auth->user_id(),
                'close_date' => date('Y-m-d H:i:s')
            ];

            // 3. Eksekusi Update
            $this->db->where('id_penawaran', $id_penawaran);
            $this->db->update(DBCNL . '.kons_tr_penawaran_non_konsultasi', $arr_update);

            // Cek apakah ada perubahan (biar gak update yang sudah diclose)
            if ($this->db->affected_rows() === 0) {
                // Bisa jadi karena ID salah atau status memang sudah '1'
                throw new Exception('Data tidak ditemukan atau penawaran ini sebenarnya sudah ditutup.');
            }

            // 4. Jika lancar, simpan permanen
            $this->db->trans_commit();
            return $this->_send_response(200, 'Penawaran berhasil ditutup dengan sukses.');
        } catch (Exception $e) {
            // 5. Jika ada error, batalin semua perubahan database
            $this->db->trans_rollback();
            $msg = $e->getMessage() ?: 'Terjadi kesalahan sistem saat menutup penawaran.';
            return $this->_send_response(500, $msg);
        }
    }

    private function _send_response($code, $message)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode([
                'status' => $code == 200 ? 'success' : 'error',
                'msg'    => $message
            ]));
    }
}
