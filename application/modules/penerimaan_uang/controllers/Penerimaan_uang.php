<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__DIR__) . '/libraries/Penerimaan_calculation.php';

require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_consulting.php';



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
        $list_years = $this->Penerimaan_uang_model->getDistinctYears();
        $list_companies = array('STM', 'VUCA', 'SUSTAIN');

        $data = [
            'list_bank' => $list_bank,
            'list_years' => $list_years,
            'list_companies' => $list_companies
        ];

        $this->template->set($data);
        $this->template->title('Penerimaan Piutang');
        $this->template->render('index');
    }

    public function get_report_penerimaan()
    {
        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length <= 0) $length = 10;

        $year = $this->input->post('year');
        $month = $this->input->post('month');
        $company = $this->input->post('company');

        $searchDt = $this->input->post('search');
        $searchVal = '';
        if (is_array($searchDt) && !empty($searchDt['value'])) {
            $searchVal = trim($searchDt['value']);
        } elseif ($this->input->post('custom_search') !== null) {
            $searchVal = trim((string)$this->input->post('custom_search'));
        } elseif ($this->input->post('search') !== null && is_string($this->input->post('search'))) {
            $searchVal = trim((string)$this->input->post('search'));
        }

        $order = $this->input->post('order');
        $orderCol = 1;
        $orderDir = 'desc';
        if (is_array($order) && isset($order[0]['column'])) {
            $orderCol = (int)$order[0]['column'];
            $orderDir = $order[0]['dir'] ?? 'desc';
        }

        $result = $this->Penerimaan_uang_model->get_report_penerimaan_serverside(
            $year, $month, $company, $searchVal, $start, $length, $orderCol, $orderDir
        );

        $dataRows = array();
        $no = $start;
        foreach ($result['receipts'] as $r) {
            $no++;
            $tgl = !empty($r['tanggal_transaksi']) ? date('d-M-Y', strtotime($r['tanggal_transaksi'])) : '-';
            $bankName = trim(($r['nm_bank'] ?? '') . ' ' . ($r['nama_bank_acc'] ?? 'Bank'));
            if (empty($bankName)) $bankName = 'Bank';

            $bankUpper = strtoupper($bankName);
            if (strpos($bankUpper, 'VUCA') !== false) $coName = 'VUCA';
            elseif (strpos($bankUpper, 'SUSTAIN') !== false) $coName = 'SUSTAIN';
            else $coName = 'STM';
            $coBadge = '<span class="label label-primary">' . htmlspecialchars($coName, ENT_QUOTES, 'UTF-8') . '</span>';

            $invHtmlArr = array();
            $taxHtmlArr = array();
            $pphText = ($r['pph23_dipotong'] === 'Y') ? 'Potong PPh 23' : 'Tidak potong PPh 23';

            if (!empty($r['details'])) {
                foreach ($r['details'] as $d) {
                    $snap = json_decode($d['consulting_snapshot'] ?? '', true);
                    $taxCode = !empty($d['tax_treatment']) ? $d['tax_treatment'] : ($snap['tax_treatment'] ?? ((float)$d['ppn_jurnal'] > 0 ? 'PKP' : 'WAPU'));
                    $invNo = $d['no_invoice'] ?: '-';

                    $invHtmlArr[] = '<div style="padding: 2px 0;">' . htmlspecialchars($invNo, ENT_QUOTES, 'UTF-8') . '</div>';
                    $taxBadge = '<span class="tax-badge tax-' . htmlspecialchars($taxCode, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($taxCode, ENT_QUOTES, 'UTF-8') . '</span>';
                    $taxHtmlArr[] = '<div style="padding: 2px 0;">' . $taxBadge . '<div style="font-size: 11px; color: #777; margin-top: 1px;">' . $pphText . '</div></div>';
                }
                $invHtml = implode('', $invHtmlArr);
                $taxHtml = implode('', $taxHtmlArr);
            } else {
                $invHtml = '-';
                $taxHtml = '-';
            }

            $action = '<button type="button" class="btn btn-xs btn-info detail" title="Lihat Detail" data-id="' . $r['id'] . '"><i class="fa fa-eye"></i> Lihat</button>';
            if (!$r['is_posted']) {
                $action .= ' <button type="button" class="btn btn-xs btn-danger rollback" title="Rollback Penerimaan" data-id="' . $r['id'] . '"><i class="fa fa-undo"></i> Rollback</button>';
            }

            $dataRows[] = array(
                'no' => $no,
                'tanggal' => $tgl,
                'bank' => htmlspecialchars($bankName, ENT_QUOTES, 'UTF-8'),
                'detail_bank' => htmlspecialchars($r['keterangan'] ?: '-', ENT_QUOTES, 'UTF-8'),
                'customer' => htmlspecialchars($r['nm_customer'], ENT_QUOTES, 'UTF-8'),
                'no_invoice' => $invHtml,
                'company' => $coBadge,
                'tax_pph' => $taxHtml,
                'penerimaan_bank' => 'Rp. ' . number_format((float)$r['nominal_penerimaan_bank'], 2),
                'action' => $action
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $dataRows,
            'total_nominal' => 'Rp. ' . number_format($result['total_nominal'], 2),
            'count_invoices' => $result['total_invoices']
        ));
    }

    public function export_excel()
    {
        $year = $this->input->get('year');
        $month = $this->input->get('month');
        $company = $this->input->get('company');
        $search = $this->input->get('search');

        $report_data = $this->Penerimaan_uang_model->get_report_data($year, $month, $company, $search);

        $months_id = array(1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember');
        $filtInfo = 'Tahun: ' . (!empty($year) ? $year : 'Semua') . ' | Bulan: ' . (!empty($month) ? ($months_id[(int)$month] ?? $month) : 'Semua') . ' | Company: ' . (!empty($company) ? $company : 'Semua');

        $data = array(
            'report_data' => $report_data,
            'filter_info' => $filtInfo
        );

        $filename = 'Report_Penerimaan_' . (!empty($year) ? $year : 'Semua') . '_' . (!empty($month) ? $month : 'Semua') . '_' . (!empty($company) ? $company : 'Semua') . '_' . date('YmdHis') . '.xls';

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Cache-Control: max-age=0");

        $this->load->view('export_excel', $data);
    }



    public function add_penerimaan_uang($id)

    {

        $this->auth->restrict($this->viewPermission);



        // Fetch split record joined to detail

        $this->db->select('s.*, a.tanggal_transaksi, a.keterangan, a.nominal_debit, a.nominal_kredit, a.saldo, a.reference_no, a.nilai_terpakai, a.tipe_bank, a.jenis_bank');

        $this->db->from('tr_alokasi_split s');

        $this->db->join('tr_alokasi_detail a', 'a.id = s.id_alokasi_detail');

        $this->db->where('s.id', $id);

        $result = $this->db->get()->row_array();



        if (empty($result)) {

            $this->session->set_flashdata('error', 'Data alokasi split tidak ditemukan.');

            redirect('penerimaan_uang');

            return;

        }



        $nominal_penerimaan_bank = $result['nominal'] - $result['nilai_terpakai'];



        // Get all customers from all invoices (konsultasi + non-konsultasi) grouped by nm_customer

        $this->db->select('a.id_customer, a.nm_customer');

        $this->db->from('tr_invoicing a');

        $this->db->where('a.nm_customer !=', '');

        $this->db->where('a.nm_customer IS NOT NULL', null, false);

        $this->db->group_by('a.nm_customer');

        $this->db->order_by('a.nm_customer', 'ASC');

        $get_customer = $this->db->get()->result_array();



        $data = [

            'id_alokasi' => $result['id'],

            'list_customer' => $get_customer,

            'nominal_penerimaan_bank' => $nominal_penerimaan_bank

        ];



        $this->template->set('results', $data);

        $this->template->title('Penerimaan Piutang');

        $this->template->render('add');

    }



    public function get_inv_by_cust()

    {

        $post = $this->input->post();

        $nm_customer = is_array($post) && isset($post['nm_customer']) ? $post['nm_customer'] : '';

        $pph23_dipotong = is_array($post) && isset($post['pph23_dipotong']) ? $post['pph23_dipotong'] : 'Y';



        $this->db->select('a.created_date, a.id as no_inv, a.nm_customer, a.total_nominal_jurnal, a.dpp_lain_lain_jurnal, a.ppn_jurnal, a.pph_jurnal, a.total_akhir_jurnal, a.tagihan_ppn_jurnal, a.saldo_piutang, a.no_invoice, a.non_kons, a.tipe_invoice, a.tax_treatment, a.consulting_version, a.consulting_snapshot');

        $this->db->from('tr_invoicing a');

        $this->db->where('a.nm_customer', $nm_customer);

        $this->db->where('a.saldo_piutang >', 0);

        $this->db->order_by('a.created_date', 'ASC');

        $get_invoice = $this->db->get()->result_array();



        $hasil = '';

        if (!empty($get_invoice)) {

            foreach ($get_invoice as $item) {

                $isNew = Invoice_consulting::is_new($item);

                $taxTreatment = !empty($item['tax_treatment']) ? $item['tax_treatment'] : '';

                if (empty($taxTreatment)) {

                    $snapshot = json_decode($item['consulting_snapshot'] ?? '', true);

                    $taxTreatment = !empty($snapshot['type']) ? $snapshot['type'] : ((float)$item['ppn_jurnal'] > 0 ? 'PKP' : 'WAPU');

                }

                if ($item['non_kons'] == '1') {

                    $taxBadge = '<span class="label label-warning">Non Kons</span>';

                } else {

                    $taxBadge = '<span class="tax-badge tax-' . htmlspecialchars($taxTreatment, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($taxTreatment, ENT_QUOTES, 'UTF-8') . '</span>';

                }



                $decimals = $isNew ? 2 : 0;

                $sales = (float)$item['total_nominal_jurnal'];

                $dppLain = (float)$item['dpp_lain_lain_jurnal'];

                if ($dppLain <= 0 && $sales > 0) {

                    $dppLain = round($sales * 11 / 12);

                }

                $ppn = (float)$item['ppn_jurnal'];

                if ($taxTreatment === 'WAPU' || $taxTreatment === 'KB') {

                    $ppn = 0;

                }



                $pphDefault = round($dppLain * 0.02);

                $pphVal = ($pph23_dipotong === 'Y') ? $pphDefault : 0;

                $tagihan = ($taxTreatment === 'PKP') ? ($sales + $ppn) : $sales;

                $tagihanMinPph = $tagihan - $pphVal;

                $tgl = !empty($item['created_date']) ? date('d-M-Y', strtotime($item['created_date'])) : '-';



                $hasil .= '<tr>';

                $hasil .= '<td class="text-center nowrap">' . $tgl . '</td>';

                $hasil .= '<td class="text-center font-bold">' . htmlspecialchars($item['no_invoice'], ENT_QUOTES, 'UTF-8') . '</td>';

                $hasil .= '<td class="text-center">' . $taxBadge . '</td>';

                $hasil .= '<td class="text-right">' . number_format($sales, $decimals) . '</td>';

                $hasil .= '<td class="text-right">' . number_format($dppLain, $decimals) . '</td>';

                $hasil .= '<td class="text-right">' . number_format($ppn, $decimals) . '</td>';

                $hasil .= '<td class="text-right">' . number_format($pphVal, $decimals) . '</td>';

                $hasil .= '<td class="text-right font-bold">' . number_format($tagihan, $decimals) . '</td>';

                $hasil .= '<td class="text-right">' . number_format($tagihanMinPph, $decimals) . '</td>';

                $hasil .= '<td class="text-right font-bold">' . number_format((float)$item['saldo_piutang'], $decimals) . '</td>';

                $hasil .= '<td class="text-center">';

                $hasil .= '<input type="checkbox" name="choose_inv[]" value="' . $item['no_inv'] . '" style="cursor:pointer; width:16px; height:16px;">';

                $hasil .= '</td>';

                $hasil .= '</tr>';

            }

        } else {

            $hasil = '<tr>';

            $hasil .= '<td colspan="11" class="text-center text-muted" style="padding: 16px;">No Data Found</td>';

            $hasil .= '</tr>';

        }



        echo $hasil;

    }



    public function process_alokasi()

    {

        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $consulting=new Consulting_receipt($this);

        if ($consulting->contains_new($this->input->post('choose_inv'))) {

            try {

                $data=$consulting->preview($this->input->post());

                $this->template->set($data);$this->template->render('consulting_process');

            } catch (Throwable $error) { echo '<div class="alert alert-danger" role="alert">'.htmlspecialchars($error instanceof RuntimeException || $error instanceof InvalidArgumentException?$error->getMessage():'Preview tidak dapat dimuat.',ENT_QUOTES,'UTF-8').'</div>'; }

            return;

        }



        $post = $this->input->post();



        $pph23_dipotong = $post['pph23_dipotong'];

        $ppn_dipotong = $post['ppn_dipotong'];



        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($post['id_alokasi']);

        $split = $resolved['split'];

        $detail = $resolved['detail'];



        $uang_masuk = $split['nominal'];







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

            try {

                $remaining_pph = $this->Penerimaan_uang_model->remaining_receipt_pph($get_inv, $pph23_dipotong);

            } catch (InvalidArgumentException $e) {

                echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';

                return;

            }



            if ($get_inv['non_kons'] == '1') {

                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran_non_konsultasi', ['id_penawaran' => $get_inv['id_penawaran']])->row_array();



                $id_company = '1';

                $nm_company = 'STM-Vuca';

            } else {

                $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();



                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_spk_penawaran['id_penawaran']])->row_array();



                $company = (!empty($get_penawaran['company'])) ? $get_penawaran['company'] : $get_spk_penawaran['id_company'];



                $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $company])->row_array();

                $id_company = (!empty($get_company)) ? $get_company['id'] : '';

                $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';

            }

            $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();



            $get_bank = $this->db->get_where('ms_bank', ['id' => $detail['tipe_bank']])->row_array();



            $this->accounting->select('a.nama as nm_coa');

            $this->accounting->from('coa_master a');

            $this->accounting->where('a.no_perkiraan', $get_bank['coa_bank']);

            $get_coa_bank = $this->accounting->get()->row_array();



            $coa_bank = (!empty($get_bank)) ? $get_bank['coa_bank'] : '';

            $nm_coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank['nm_coa'] : '';



            // Keep the actual outstanding balance, including previous installments.

            $saldo_piutang = Penerimaan_calculation::cents($get_inv['saldo_piutang']) / 100;

            // Old Vuca invoices stored zero gross despite having a valid DPP.

            $tagihan_ppn = Penerimaan_calculation::invoice_gross($get_inv) / 100;



            $hasil .= '<tr>';

            $hasil .= '<td class="text-center">';

            $hasil .= date('d-F-Y', strtotime($get_inv['tanggal_invoice']));

            $hasil .= '<input type="hidden" name="id_inv_' . $no . '" value="' . $item . '">';

            $hasil .= '<input type="hidden" name="remaining_pph_' . $no . '" value="' . Penerimaan_calculation::decimal($remaining_pph) . '">';

            $hasil .= '</td>';

            $hasil .= '<td class="text-center">' . $get_inv['no_invoice'] . '</td>';

            $hasil .= '<td class="text-right">' . number_format($get_inv['total_nominal_jurnal']) . '</td>';

            $hasil .= '<td class="text-right">' . number_format($get_inv['dpp_lain_lain_jurnal']) . '</td>';

            $hasil .= '<td class="text-right">' . number_format($get_inv['ppn_jurnal']) . '</td>';

            $hasil .= '<td class="text-right">' . number_format($get_inv['pph_jurnal']) . '</td>';

            $hasil .= '<td class="text-right">' . number_format($tagihan_ppn) . '</td>';

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



            $total_piutang += ($pph23_dipotong == 'N') ? round($tagihan_ppn) : round($get_inv['total_akhir_jurnal']);

            // $total_piutang += $get_inv['total_akhir_jurnal'];

            $total_piutang_dagang += round($saldo_piutang);



            if ($no == 1) {

                $hasil_jurnal .= '<tr>';



                $hasil_jurnal .= '<td class="text-center">';

                $hasil_jurnal .= date('d-F-Y', strtotime($detail['tanggal_transaksi']));

                $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_debit" value="' . $detail['tanggal_transaksi'] . '">';

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

                $hasil_jurnal .= $nm_coa_bank . ' - ' . $get_inv['no_invoice'];

                $hasil_jurnal .= '<input type="hidden" name="nm_coa_bank_debit" value="' . $nm_coa_bank . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '<td class="text-center td_debit_bank_debit">';

                $hasil_jurnal .= number_format($uang_masuk);

                $hasil_jurnal .= '<input type="hidden" name="debit_bank_debit" value="' . intval($uang_masuk) . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '<td class="text-center td_kredit_bank_debit">';

                $hasil_jurnal .= number_format(0);

                $hasil_jurnal .= '<input type="hidden" name="kredit_bank_debit" value="' . 0 . '">';

                $hasil_jurnal .= '</td>';



                $hasil_jurnal .= '</tr>';



                $total_debit += $uang_masuk;



                // Tentukan COA PPh berdasarkan tipe_invoice (VUCA = 1106-01-05, lainnya = 1106-01-02)

                $coa_pph = (!empty($get_inv['tipe_invoice']) && $get_inv['tipe_invoice'] == '1') ? '1106-01-05' : '1106-01-02';

                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', $coa_pph];



                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');

                $this->accounting->from('coa_master a');

                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);

                $get_coa_jurnal = $this->accounting->get()->result_array();



                foreach ($get_coa_jurnal as $item_coa_jurnal) {



                    $value_debit = 0;

                    $value_kredit = 0;



                    if ($post['pph23_dipotong'] == 'N' && $item_coa_jurnal['no_perkiraan'] == $coa_pph) {

                        $value_kredit = $remaining_pph / 100;

                    }



                    if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {

                        // $value_kredit = $get_inv['tagihan_ppn_jurnal'];

                        // if ($post['pph23_dipotong'] == 'N') {

                        $value_kredit = $saldo_piutang;

                        // }

                    }



                    $hasil_jurnal .= '<tr>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= date('d-F-Y', strtotime($detail['tanggal_transaksi']));

                    $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $detail['tanggal_transaksi'] . '">';

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

                    $hasil_jurnal .= $item_coa_jurnal['nm_coa'] . ' - ' . $get_inv['no_invoice'];

                    $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $item_coa_jurnal['nm_coa'] . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center td_debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_debit);

                    $hasil_jurnal .= '<input type="hidden" name="debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_debit) . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center td_kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_kredit);

                    $hasil_jurnal .= '<input type="hidden" name="kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_kredit) . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '</tr>';



                    $total_debit += $value_debit;

                    $total_kredit += $value_kredit;

                }

            } else {

                // Tentukan COA PPh berdasarkan tipe_invoice (VUCA = 1106-01-05, lainnya = 1106-01-02)

                $coa_pph = (!empty($get_inv['tipe_invoice']) && $get_inv['tipe_invoice'] == '1') ? '1106-01-05' : '1106-01-02';

                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', $coa_pph];



                $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');

                $this->accounting->from('coa_master a');

                $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);

                $get_coa_jurnal = $this->accounting->get()->result_array();



                foreach ($get_coa_jurnal as $item_coa_jurnal) {

                    $value_debit = 0;

                    $value_kredit = 0;



                    if ($post['pph23_dipotong'] == 'N' && $item_coa_jurnal['no_perkiraan'] == $coa_pph) {

                        $value_kredit = $remaining_pph / 100;

                    }

                    if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {

                        $value_kredit = $saldo_piutang;

                    }



                    $hasil_jurnal .= '<tr>';



                    $hasil_jurnal .= '<td class="text-center">';

                    $hasil_jurnal .= date('d-F-Y', strtotime($detail['tanggal_transaksi']));

                    $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $detail['tanggal_transaksi'] . '">';

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

                    $hasil_jurnal .= $item_coa_jurnal['nm_coa'] . ' - ' . $get_inv['no_invoice'];

                    $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . $item_coa_jurnal['nm_coa'] . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center td_debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_debit);

                    $hasil_jurnal .= '<input type="hidden" name="debit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_debit) . '">';

                    $hasil_jurnal .= '</td>';



                    $hasil_jurnal .= '<td class="text-center td_kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '">';

                    $hasil_jurnal .= number_format($value_kredit);

                    $hasil_jurnal .= '<input type="hidden" name="kredit_' . $item_coa_jurnal['no_perkiraan'] . '_' . $no . '" value="' . intval($value_kredit) . '">';

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

            'nm_customer' => $post['customer'],

            'ppn_dipotong' => $post['ppn_dipotong'],

            'pph23_dipotong' => $post['pph23_dipotong'],

            'nominal_penerimaan_bank' => str_replace(',', '', $post['nominal_penerimaan_bank'])

        ];

        $this->template->set($data);

        $this->template->render('process');

    }



    public function save_penerimaan_piutang()

    {

        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $consulting=new Consulting_receipt($this);$post=$this->input->post();$ids=array();

        $count=is_scalar($post['no_inv']??null)?(int)$post['no_inv']:0;

        if($count<1 || $count>100){echo json_encode(array('status'=>0,'msg'=>'Jumlah invoice tidak valid.','message'=>'Jumlah invoice tidak valid.'));return;}

        for($n=1;$n<=$count;$n++)$ids[]=$post['id_inv_'.$n]??'';

        if($consulting->contains_new($ids)){echo json_encode($consulting->save($post));return;}

        $service=new Invoice_consulting_service($this);

        return $service->serialized(function()use($consulting,$post){

            try { $consulting->guard_legacy_allocation($post['id_alokasi']??''); }

            catch(RuntimeException $error){echo json_encode(array('status'=>0,'msg'=>$error->getMessage(),'message'=>$error->getMessage()));return;}

            $this->save_legacy_penerimaan_piutang();

        });

    }



    private function save_legacy_penerimaan_piutang()

    {

        $post = $this->input->post();



        $id = $this->Penerimaan_uang_model->generate_id();



        $nm_customer = $post['nm_customer'];



        // Try to find id_customer from consultant database

        $this->consultant->select('a.id_customer');

        $this->consultant->from('customer a');

        $this->consultant->where('a.nm_customer', $nm_customer);

        $get_customer = $this->consultant->get()->row_array();

        $id_customer_save = (!empty($get_customer)) ? $get_customer['id_customer'] : '';



        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($post['id_alokasi']);

        $split = $resolved['split'];

        $detail = $resolved['detail'];

        $get_ms_bank = $this->db->get_where('ms_bank', ['id' => $detail['tipe_bank']])->row_array();



        $coa_bank = (!empty($get_ms_bank)) ? $get_ms_bank['coa_bank'] : '';



        $get_coa_bank = $this->accounting->get_where('coa_master', ['no_perkiraan' => $coa_bank])->row_array();

        $nm_coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank['nama'] : '';



        $arr_insert_header = [

            'no_surat' => $id,

            'id_alokasi' => $post['id_alokasi'],

            'id_customer' => $id_customer_save,

            'nm_customer' => $nm_customer,

            'ppn_dipotong' => $post['ppn_dipotong'],

            'pph23_dipotong' => $post['pph23_dipotong'],

            'nominal_penerimaan_bank' => str_replace(',', '.', str_replace('.', '', $post['nominal_penerimaan_bank'])),

            'created_by' => $this->session->userdata('id_user'),

            'created_date' => date('Y-m-d H:i:s')

        ];



        $arr_insert_detail = [];

        $arr_update_inv = [];

        $arr_insert_jurnal = [];



        $total_penerimaan = 0;

        $total_penerimaan_cents = 0;



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

                $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_inv['id_spk_penawaran']])->row_array();



                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_spk_penawaran['id_penawaran']])->row_array();

                

                $company = (!empty($get_penawaran['company'])) ? $get_penawaran['company'] : $get_spk_penawaran['id_company'];



                $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $company])->row_array();



                $id_company = (!empty($get_company)) ? $get_company['id'] : '';

                $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';

            }



            $dpp = (!empty($get_inv)) ? $get_inv['total_nominal_jurnal'] : 0;

            $dpp_lain = (!empty($get_inv)) ? $get_inv['dpp_lain_lain_jurnal'] : 0;

            $ppn = (!empty($get_inv)) ? $get_inv['ppn_jurnal'] : 0;

            $tagihan_ppn = (!empty($get_inv)) ? Penerimaan_calculation::decimal(Penerimaan_calculation::invoice_gross($get_inv)) : 0;

            $pph = (!empty($get_inv)) ? $get_inv['pph_jurnal'] : 0;

            if ($post['pph23_dipotong'] == 'N') {

                $pph = 0; // Force PPh to 0 if not deducted

            }



            $total = (!empty($get_inv)) ? $get_inv['total_akhir_jurnal'] : 0;

            $saldo_piutang = (!empty($get_inv)) ? $get_inv['saldo_piutang'] : 0;

            $tgl_inv = (!empty($get_inv)) ? date('Y-m-d', strtotime($get_inv['created_date'])) : '';



            try {

                $remaining_pph = $this->Penerimaan_uang_model->remaining_receipt_pph($get_inv, $post['pph23_dipotong']);

                $receipt_cents = Penerimaan_calculation::cents($post['penerimaan_' . $i], true);

                $admin_cents = Penerimaan_calculation::cents($post['biaya_admin_' . $i], true);

                $calculation = Penerimaan_calculation::calculate(Penerimaan_calculation::cents($saldo_piutang), $remaining_pph, $post['pph23_dipotong'], $receipt_cents, $admin_cents);

            } catch (InvalidArgumentException $e) {

                echo json_encode(array('status'=>0, 'msg'=>$e->getMessage(), 'message'=>$e->getMessage()));

                return;

            }

            $penerimaan = Penerimaan_calculation::decimal($receipt_cents);

            $biaya_admin = Penerimaan_calculation::decimal($admin_cents);

            $sisa_piutang = Penerimaan_calculation::decimal($calculation['remaining']);



            $arr_insert_detail[] = [

                'id_header' => $id,

                'id_alokasi' => $post['id_alokasi'],

                'id_inv' => $post['id_inv_' . $i],

                'tgl_inv' => $tgl_inv,

                'id_customer' => $id_customer_save,

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

                'saldo_piutang' => $sisa_piutang

            ];



            $total_penerimaan_cents += $receipt_cents;



            // Tentukan COA PPh berdasarkan tipe_invoice (VUCA = 1106-01-05, lainnya = 1106-01-02)

            $coa_pph = (!empty($get_inv['tipe_invoice']) && $get_inv['tipe_invoice'] == '1') ? '1106-01-05' : '1106-01-02';



            $arr_coa_jurnal = ['1102-01-01', '7201-01-04'];

            // if ($post['pph23_dipotong'] == 'Y') {

            $arr_coa_jurnal[] = $coa_pph;

            // }



            $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');

            $this->accounting->from('coa_master a');

            $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);

            $get_coa_jurnal = $this->accounting->get()->result_array();





            foreach ($get_coa_jurnal as $item_jurnal) {

                $no_jurnal++;

                $value_debit = $item_jurnal['no_perkiraan'] === '7201-01-04' ? $biaya_admin : '0.00';

                $value_kredit = $item_jurnal['no_perkiraan'] === '1102-01-01'

                    ? Penerimaan_calculation::decimal($calculation['reduction'])

                    : ($item_jurnal['no_perkiraan'] === $coa_pph ? Penerimaan_calculation::decimal($calculation['adjustment']) : '0.00');



                $get_no_invoice = $this->db->select('a.no_invoice')

                    ->from('tr_invoicing a')

                    ->where('a.id', $post['id_inv_' . $i])

                    ->get()

                    ->row();



                $no_invoice = $get_no_invoice->no_invoice ?? '';



                $get_no_invoice = $this->db->select('a.no_invoice')

                    ->from('tr_invoicing a')

                    ->where('a.id', $post['id_inv_' . $i])

                    ->get()

                    ->row();



                $no_invoice = $get_no_invoice->no_invoice ?? '';



                if ($i == 1 && $no_jurnal == 1) {

                    $arr_insert_jurnal[] = [

                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),

                        'tgl_jurnal' => $detail['tanggal_transaksi'],

                        'coa' => $coa_bank,

                        'id_company' => $id_company,

                        'nm_company' => $nm_company,

                        'nm_coa' => $nm_coa_bank,

                        'debit' => '0.00', // Set to the sum of receipts after the loop.

                        'kredit' => '0.00',

                        'keterangan' => $nm_coa_bank . ' - ' . $no_invoice,

                        'sts' => '0',

                        'no_transaksi' => $id,

                        'jenis_transaksi' => 'Penerimaan Piutang',

                        'created_by' => $this->auth->user_id(),

                        'created_date' => date('Y-m-d H:i:s')

                    ];



                    $no_jurnal++;

                    $arr_insert_jurnal[] = [

                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),

                        'tgl_jurnal' => $detail['tanggal_transaksi'],

                        'coa' => $item_jurnal['no_perkiraan'],

                        'id_company' => $id_company,

                        'nm_company' => $nm_company,

                        'nm_coa' => $item_jurnal['nm_coa'],

                        'debit' => $value_debit,

                        'kredit' => $value_kredit,

                        'keterangan' => $item_jurnal['nm_coa'] . ' - ' . $no_invoice,

                        'sts' => '0',

                        'no_transaksi' => $id,

                        'jenis_transaksi' => 'Penerimaan Piutang',

                        'created_by' => $this->auth->user_id(),

                        'created_date' => date('Y-m-d H:i:s')

                    ];

                } else {

                    $arr_insert_jurnal[] = [

                        'no_jurnal' => $this->Penerimaan_uang_model->generate_id_invoice_jurnal($no_jurnal),

                        'tgl_jurnal' => $detail['tanggal_transaksi'],

                        'coa' => $item_jurnal['no_perkiraan'],

                        'id_company' => $id_company,

                        'nm_company' => $nm_company,

                        'nm_coa' => $item_jurnal['nm_coa'],

                        'debit' => $value_debit,

                        'kredit' => $value_kredit,

                        'keterangan' => $item_jurnal['nm_coa'] . ' - ' . $no_invoice,

                        'sts' => '0',

                        'no_transaksi' => $id,

                        'jenis_transaksi' => 'Penerimaan Piutang',

                        'created_by' => $this->auth->user_id(),

                        'created_date' => date('Y-m-d H:i:s')

                    ];

                }

            }

        }



        if (!empty($arr_insert_jurnal)) {

            $arr_insert_jurnal[0]['debit'] = Penerimaan_calculation::decimal($total_penerimaan_cents);

        }

        $total_penerimaan = Penerimaan_calculation::decimal($total_penerimaan_cents);

        $arr_insert_header['nominal_penerimaan_bank'] = $total_penerimaan;



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



        $detail_id_for_update = $resolved['is_legacy'] ? $post['id_alokasi'] : $split['id_alokasi_detail'];

        $update_alokasi = $this->db->update('tr_alokasi_detail', ['nilai_terpakai' => $total_penerimaan], ['id' => $detail_id_for_update]);

        if (!$update_alokasi) {

            $this->db->trans_rollback();



            print_r($this->db->last_query());

            exit;

        }



        // Update nilai_terpakai di tr_alokasi_split agar status per-split akurat

        if (!$resolved['is_legacy'] && !empty($split)) {

            $update_split = $this->db->update('tr_alokasi_split', ['nilai_terpakai' => $total_penerimaan], ['id' => $post['id_alokasi']]);

            if (!$update_split) {

                $this->db->trans_rollback();



                print_r($this->db->last_query());

                exit;

            }

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

            'msg' => $msg,

            'message' => $msg

        ];



        echo json_encode($response);

    }



    public function detail()

    {

        $post = $this->input->post();



        $id = $post['id_alokasi'];



        $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang', ['id' => $id])->row_array();



        $this->db->select('a.*, b.no_invoice, b.tax_treatment, b.total_nominal_jurnal, b.dpp_lain_lain_jurnal, b.ppn_jurnal, b.tagihan_ppn_jurnal, b.pph_jurnal, b.total_akhir_jurnal, b.saldo_piutang, c.no_surat');

        $this->db->from('tr_penerimaan_piutang_detail a');

        $this->db->join('tr_invoicing b', 'b.id = a.id_inv', 'left');

        $this->db->join('tr_penerimaan_piutang c', 'c.no_surat = a.id_header');

        $this->db->where('a.id_header', $get_penerimaan['no_surat']);

        $get_penerimaan_detail = $this->db->get()->result_array();



        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($get_penerimaan['id_alokasi']);

        $get_alokasi = (!empty($resolved)) ? $resolved['detail'] : [];

        if (!empty($get_alokasi) && !empty($get_alokasi['bank'])) {
            $bank_acc = $this->db->select('b.nama as nama_bank_acc, b.rekening, c.nama_bank as nm_bank')
                ->from('tr_bank_account b')
                ->join('tr_bank c', 'c.id = b.bank', 'left')
                ->where('b.id', $get_alokasi['bank'])
                ->get()->row_array();
            if ($bank_acc) {
                $get_alokasi = array_merge($get_alokasi, $bank_acc);
            }
        }



        // Receipt journals are keyed by no_surat, including posted journals.

        $get_jurnal = $this->db->get_where('tr_jurnal', [

            'no_transaksi' => $get_penerimaan['no_surat'],

            'jenis_transaksi' => 'Penerimaan Piutang'

        ])->result_array();



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



    public function rollback_penerimaan()

    {

        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $service=new Invoice_consulting_service($this);

        return $service->serialized(function(){

            $id=$this->input->post('id_penerimaan');

            if(!is_scalar($id)){echo json_encode(array('status'=>0,'msg'=>'ID penerimaan tidak valid.'));return;}

            $header=$this->db->get_where('tr_penerimaan_piutang',array('id'=>$id))->row_array();

            $consulting=new Consulting_receipt($this);

            if((int)($header['consulting_batch']??0)!==3 && $header){

                try {

                    $consulting->guard_legacy_allocation($header['id_alokasi']);

                    if($this->db->field_exists('consulting_reduction','tr_penerimaan_piutang_detail')){

                        $details=$this->db->get_where('tr_penerimaan_piutang_detail',array('id_header'=>$header['no_surat']))->result_array();

                        foreach($details as $detail){

                            $later=$this->db->query('SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv=? AND id>? AND consulting_reduction IS NOT NULL LIMIT 1',array($detail['id_inv'],$detail['id']))->row_array();

                            if($later)throw new RuntimeException('Rollback penerimaan terbaru terlebih dahulu.');

                        }

                    }

                }catch(RuntimeException $error){echo json_encode(array('status'=>0,'msg'=>$error->getMessage()));return;}

            }

            return $this->rollback_legacy_penerimaan();

        });

    }



    private function rollback_legacy_penerimaan()

    {

        $this->auth->restrict($this->deletePermission);



        $id_penerimaan = $this->input->post('id_penerimaan');



        require_once __DIR__.'/../libraries/Consulting_receipt.php';

        $consultingHeader=is_scalar($id_penerimaan)?$this->db->get_where('tr_penerimaan_piutang',array('id'=>$id_penerimaan))->row_array():null;

        if ((int)($consultingHeader['consulting_batch']??0)===3) {

            $consulting=new Consulting_receipt($this);echo json_encode($consulting->rollback($id_penerimaan));return;

        }



        if (empty($id_penerimaan)) {

            echo json_encode(['status' => 0, 'msg' => 'ID Penerimaan tidak valid.']);

            return;

        }



        // Get header

        $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang', ['id' => $id_penerimaan])->row_array();

        if (empty($get_penerimaan)) {

            echo json_encode(['status' => 0, 'msg' => 'Data penerimaan tidak ditemukan.']);

            return;

        }



        $no_surat = $get_penerimaan['no_surat'];



        // Check if journal has been posted

        $check_posted = $this->db->get_where('tr_jurnal', ['no_transaksi' => $no_surat, 'sts' => '1'])->num_rows();

        if ($check_posted > 0) {

            echo json_encode(['status' => 0, 'msg' => 'Penerimaan tidak bisa di-rollback karena jurnal sudah di-posting.']);

            return;

        }



        $this->db->trans_begin();



        // Revert Saldo Piutang in tr_invoicing

        $get_details = $this->db->get_where('tr_penerimaan_piutang_detail', ['id_header' => $no_surat])->result_array();



        // Cek tanggal rilis perbaikan untuk backward compatibility

        $tgl_rilis_perbaikan = '2026-06-09 00:00:00';

        $is_old_data = (strtotime($get_penerimaan['created_date']) < strtotime($tgl_rilis_perbaikan));



        $total_penerimaan = 0;

        foreach ($get_details as $detail) {

            $id_inv = $detail['id_inv'];

            $penerimaan = $detail['penerimaan'];

            $biaya_admin = $detail['biaya_admin'];

            $pph23 = $detail['pph23'];

            $total_penerimaan += $penerimaan;



            if ($is_old_data) {

                // Logika lama: hanya menambahkan penerimaan

                $amount_to_add = $penerimaan;

            } else {

                // Logika baru: akurat berdasarkan perhitungan UI

                if ($get_penerimaan['pph23_dipotong'] == 'N') {

                    $amount_to_add = $penerimaan - $pph23 + $biaya_admin;

                } else {

                    $amount_to_add = $penerimaan + $biaya_admin;

                }

            }



            // Update saldo_piutang

            $this->db->set('saldo_piutang', 'saldo_piutang + ' . $amount_to_add, FALSE);

            $this->db->where('id', $id_inv);

            $this->db->update('tr_invoicing');

        }



        // Revert nilai_terpakai in tr_alokasi_detail, hapus split, dan kembalikan sts ke 0

        $resolved = $this->Penerimaan_uang_model->resolve_alokasi($get_penerimaan['id_alokasi']);

        if (!empty($resolved)) {

            $split = $resolved['split'];

            $alokasi_detail = $resolved['detail'];



            $detail_id_for_update = $resolved['is_legacy'] ? $get_penerimaan['id_alokasi'] : $split['id_alokasi_detail'];



            // Set nilai_terpakai = NULL dan sts = '0' agar kembali Open di modul Alokasi

            $this->db->update('tr_alokasi_detail', [

                'nilai_terpakai' => NULL,

                'sts' => '0'

            ], ['id' => $detail_id_for_update]);



            if (!$resolved['is_legacy']) {

                // Hapus seluruh data split pada tr_alokasi_split

                $this->db->delete('tr_alokasi_split', ['id_alokasi_detail' => $detail_id_for_update]);



                // Insert log ke log_alokasi_history

                $log_data = [

                    'id_alokasi_detail' => $detail_id_for_update,

                    'action' => 'ROLLBACK_PENERIMAAN',

                    'deskripsi_log' => 'Rollback penerimaan piutang (' . $no_surat . ') dan hapus split alokasi',

                    'created_by' => $this->session->userdata('id_user') ?: 'System',

                    'created_date' => date('Y-m-d H:i:s')

                ];

                $this->db->insert('log_alokasi_history', $log_data);

            }

        }



        // Delete Jurnal

        $this->db->delete('tr_jurnal', ['no_transaksi' => $no_surat, 'jenis_transaksi' => 'Penerimaan Piutang']);



        // Delete Details

        $this->db->delete('tr_penerimaan_piutang_detail', ['id_header' => $no_surat]);



        // Delete Header

        $this->db->delete('tr_penerimaan_piutang', ['no_surat' => $no_surat]);



        if ($this->db->trans_status() === FALSE) {

            $this->db->trans_rollback();

            $valid = 0;

            $msg = 'Gagal melakukan rollback, coba lagi.';

        } else {

            $this->db->trans_commit();

            $valid = 1;

            $msg = 'Rollback penerimaan berhasil dilakukan.';

        }



        echo json_encode([

            'status' => $valid,

            'msg' => $msg

        ]);

    }

}

