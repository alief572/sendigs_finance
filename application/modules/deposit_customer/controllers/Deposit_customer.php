<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Deposit_customer extends Admin_Controller
{
    protected $viewPermission = 'Deposit_Customer.View';
    protected $exportPermission = 'Deposit_Customer.Export';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('deposit_customer/Deposit_customer_model');
        require_once dirname(__DIR__, 2) . '/penerimaan_uang/libraries/Penerimaan_calculation.php';
    }

    public function index()
    {
        if (function_exists('has_permission') && !has_permission($this->viewPermission)) {
            if (isset($this->auth)) $this->auth->restrict($this->viewPermission);
        }

        $data = array(
            'kpi' => $this->Deposit_customer_model->get_kpi_summary(),
            'customers' => $this->Deposit_customer_model->get_customer_summary()
        );

        $this->template->set('results', $data);
        $this->template->title('Monitoring Deposit');
        $this->template->render('index');
    }

    public function detail($id_customer = null)
    {
        if (function_exists('has_permission') && !has_permission($this->viewPermission)) {
            if (isset($this->auth)) $this->auth->restrict($this->viewPermission);
        }

        $data = $this->customer_ledger_data($id_customer);
        if ($data === null) {
            redirect('deposit_customer');
            return;
        }

        $this->template->set('results', $data);
        $this->template->title('Monitoring Deposit - ' . $data['nm_customer']);
        $this->template->render('detail');
    }

    public function export_excel($id_customer = null)
    {
        if (function_exists('has_permission') && !has_permission($this->exportPermission)) {
            if (isset($this->auth)) $this->auth->restrict($this->exportPermission);
        }

        if (!$id_customer) {
            $id_customer = $this->input->get('id_customer');
        }

        if (!empty($id_customer)) {
            $data = $this->customer_ledger_data($id_customer);
            if ($data === null) {
                redirect('deposit_customer');
                return;
            }
            $data['is_customer_ledger'] = true;
        } else {
            $data = array(
                'customers' => $this->Deposit_customer_model->get_customer_summary(),
                'is_customer_ledger' => false
            );
        }

        $this->load->view('export_excel', $data);
    }

    /** Ledger satu customer (semua company) + ringkasan 6 field mockup; null bila customer belum punya deposit. */
    private function customer_ledger_data($id_customer)
    {
        if (!$id_customer) $id_customer = $this->input->get('id_customer');
        if (!is_scalar($id_customer) || trim((string)$id_customer) === '') return null;

        $mutasi = $this->Deposit_customer_model->get_ledger_rows($id_customer);
        if (empty($mutasi)) return null;

        $masuk = 0; $keluar = 0; $kw = 0; $lebih = 0;
        foreach ($mutasi as $m) {
            $c = Penerimaan_calculation::cents($m['nominal']);
            if ($m['jenis_mutasi'] === 'IN') {
                $masuk += $c;
                if (($m['tipe_sumber'] ?? '') === Deposit_customer_model::SRC_LEBIH) $lebih += $c; else $kw += $c;
            } else {
                $keluar += $c;
            }
        }

        return array(
            'id_customer' => $id_customer,
            'nm_customer' => !empty($mutasi[0]['nm_customer']) ? $mutasi[0]['nm_customer'] : $id_customer,
            'summary' => array(
                'total_nilai' => Penerimaan_calculation::decimal($masuk),
                'dari_kwitansi' => Penerimaan_calculation::decimal($kw),
                'dari_lebih_bayar' => Penerimaan_calculation::decimal($lebih),
                'total_terpakai' => Penerimaan_calculation::decimal($keluar),
                'sisa_saldo' => Penerimaan_calculation::decimal($masuk - $keluar)
            ),
            'mutasi' => $mutasi
        );
    }
}
