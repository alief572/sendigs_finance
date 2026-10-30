<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kwitansi_deposit extends Admin_Controller
{
    protected $viewPermission = 'Kwitansi_Deposit.View';
    protected $addPermission = 'Kwitansi_Deposit.Add';
    protected $exportPermission = 'Kwitansi_Deposit.Export';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('kwitansi_deposit/Kwitansi_deposit_model');
        require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_calculation.php';
        require_once dirname(__DIR__, 2) . '/penerimaan_uang/libraries/Penerimaan_calculation.php';
    }

    public function index()
    {
        if (function_exists('has_permission') && !has_permission($this->viewPermission)) {
            if (isset($this->auth)) $this->auth->restrict($this->viewPermission);
        }

        $filters = array(
            'status' => $this->input->get('status'),
            'id_customer' => $this->input->get('id_customer'),
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
            'search' => $this->input->get('search')
        );

        $kwitansiList = $this->Kwitansi_deposit_model->get_kwitansi_list($filters);
        $customers = $this->Kwitansi_deposit_model->get_kwitansi_customer_list();

        $totMenunggu = 0;
        $totLunas = 0;
        $countMenunggu = 0;
        foreach ($kwitansiList as $row) {
            $val = (float)$row['nilai_kwitansi'];
            if ($row['status'] === 'MENUNGGU_PEMBAYARAN') {
                $totMenunggu += $val;
                $countMenunggu++;
            } elseif ($row['status'] === 'LUNAS') {
                $totLunas += $val;
            }
        }

        $data = array(
            'kwitansiList' => $kwitansiList,
            'customers' => $customers,
            'filters' => $filters,
            'summary' => array(
                'total_menunggu' => $totMenunggu,
                'total_lunas' => $totLunas,
                'total_all' => $totMenunggu + $totLunas,
                'count_all' => count($kwitansiList),
                'count_menunggu' => $countMenunggu
            )
        );

        $this->template->set('results', $data);
        $this->template->title('Kwitansi Deposit Customer');
        $this->template->render('index');
    }

    public function add()
    {
        if (function_exists('has_permission') && !has_permission($this->addPermission)) {
            if (isset($this->auth)) $this->auth->restrict($this->addPermission);
        }

        $customers = $this->Kwitansi_deposit_model->get_customer_list();
        $initialNoKwitansi = $this->Kwitansi_deposit_model->generate_no_kwitansi(null, date('Y-m-d'));

        $data = array(
            'customers' => $customers,
            'today' => date('Y-m-d'),
            'no_kwitansi_preview' => $initialNoKwitansi
        );

        $this->template->set('results', $data);
        $this->template->title('Buat Kwitansi Deposit Customer');
        $this->template->render('add');
    }

    public function preview_no_kwitansi()
    {
        $date = $this->input->post('tanggal_kwitansi') ?: date('Y-m-d');
        $noKwitansi = $this->Kwitansi_deposit_model->generate_no_kwitansi(null, $date);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 1, 'no_kwitansi' => $noKwitansi)));
    }

    public function get_spk_by_customer()
    {
        $this->auth->restrict($this->viewPermission);
        $customerId = $this->input->post('id_customer');
        $nmCustomer = $this->input->post('nm_customer');

        $spkList = $this->Kwitansi_deposit_model->get_active_spk_by_customer($customerId, $nmCustomer);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 1, 'data' => $spkList)));
    }

    public function save()
    {
        if (function_exists('has_permission') && !has_permission($this->addPermission)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 0, 'msg' => 'Tidak memiliki hak akses pembuatan kwitansi deposit.')));
            return;
        }

        $post = $this->input->post();
        if (empty($post)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 0, 'msg' => 'Data input kosong.')));
            return;
        }

        try {
            $rawNilai = trim((string)($post['nilai_kwitansi'] ?? '0'));
            if (strpos($rawNilai, ',') !== false && strpos($rawNilai, '.') !== false) {
                $rawNilai = strrpos($rawNilai, ',') > strrpos($rawNilai, '.') ? str_replace(',', '.', str_replace('.', '', $rawNilai)) : str_replace(',', '', $rawNilai);
            } elseif (strpos($rawNilai, ',') !== false) {
                $rawNilai = str_replace(',', '.', $rawNilai);
            }
            $cents = Penerimaan_calculation::cents($rawNilai);
            $data = array(
                'id_customer' => $post['id_customer'] ?? '',
                'id_spk_penawaran' => $post['id_spk_penawaran'] ?? '',
                'nm_customer' => $post['nm_customer'] ?? '',
                'tanggal_kwitansi' => !empty($post['tanggal_kwitansi']) ? $post['tanggal_kwitansi'] : date('Y-m-d'),
                'id_company' => !empty($post['id_company']) ? $post['id_company'] : '1',
                'po_customer' => isset($post['po_customer']) ? $post['po_customer'] : '',
                'nilai_kwitansi' => Invoice_calculation::decimal($cents),
                'keterangan' => $post['keterangan'] ?? '',
                'user_id' => !empty($this->auth) && method_exists($this->auth, 'user_id') && !empty($this->auth->user_id()) ? $this->auth->user_id() : 'SYSTEM'
            );

            require_once dirname(__DIR__) . '/libraries/Kwitansi_deposit_service.php';
            $result = (new Kwitansi_deposit_service($this))->create($data, $this->Kwitansi_deposit_model);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($result));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 0, 'msg' => $e->getMessage())));
        }
    }

    public function detail($id = null)
    {
        if (!$id) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            redirect('kwitansi_deposit');
            return;
        }

        $header = $this->Kwitansi_deposit_model->get_kwitansi_by_id($id);
        if (!$header) {
            $this->session->set_flashdata('error', 'Kwitansi deposit tidak ditemukan.');
            redirect('kwitansi_deposit');
            return;
        }

        $journals = $this->Kwitansi_deposit_model->get_kwitansi_journal($header['no_kwitansi']);

        $data = array(
            'header' => $header,
            'journals' => $journals
        );

        $this->template->set('results', $data);
        $this->template->title('Detail Kwitansi Deposit - ' . $header['no_kwitansi']);
        $this->template->render('detail');
    }

    public function journal($id = null)
    {
        if (function_exists('has_permission') && !has_permission($this->viewPermission)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 0, 'msg' => 'Tidak memiliki hak akses melihat kwitansi deposit.')));
            return;
        }

        $header = $id ? $this->Kwitansi_deposit_model->get_kwitansi_by_id($id) : null;
        if (!$header) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 0, 'msg' => 'Kwitansi deposit tidak ditemukan.')));
            return;
        }

        $company = !empty($header['nm_company'])
            ? $header['nm_company']
            : $this->Kwitansi_deposit_model->get_company_name($header['id_company'] ?? null);
        $journals = array();
        foreach ($this->Kwitansi_deposit_model->get_kwitansi_journal($header['no_kwitansi']) as $j) {
            $journals[] = array(
                'tgl_jurnal' => $j['tgl_jurnal'],
                'coa' => $j['coa'],
                'nm_company' => $j['nm_company'] ?? '',
                'nm_coa' => $j['nm_coa'],
                'keterangan' => $j['keterangan'],
                'debit' => (float)$j['debit'],
                'kredit' => (float)$j['kredit']
            );
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 1,
                'header' => array(
                    'no_kwitansi' => $header['no_kwitansi'],
                    'tanggal_kwitansi' => $header['tanggal_kwitansi'],
                    'nm_customer' => $header['nm_customer'],
                    'no_spk' => $header['no_spk'],
                    'nm_company' => $company,
                    'status' => $header['status'],
                    'no_penerimaan' => $header['no_penerimaan'] ?? null
                ),
                'journals' => $journals
            )));
    }

    public function export_excel()
    {
        if (function_exists('has_permission') && !has_permission($this->exportPermission)) {
            if (isset($this->auth)) $this->auth->restrict($this->exportPermission);
        }

        $filters = array(
            'status' => $this->input->get('status'),
            'id_customer' => $this->input->get('id_customer'),
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
            'search' => $this->input->get('search')
        );

        $kwitansiList = $this->Kwitansi_deposit_model->get_kwitansi_list($filters);

        $data = array(
            'kwitansiList' => $kwitansiList,
            'filters' => $filters
        );

        $this->load->view('kwitansi_deposit/export_excel', $data);
    }

    public function post_journal()
    {
        require_once dirname(__DIR__, 2).'/invoicing/libraries/Consulting_posting.php';
        $id=$this->input->post('id');
        echo json_encode((new Consulting_posting($this))->kwitansi(is_scalar($id)?$id:''));
    }
}
