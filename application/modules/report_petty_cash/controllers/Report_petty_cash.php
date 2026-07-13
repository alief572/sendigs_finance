<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_petty_cash extends Admin_Controller
{
    // Permission names
    protected $viewPermission   = 'Report_Petty_Cash.View';
    protected $downloadPermission = 'Report_Petty_Cash.Download';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Report_petty_cash/Report_petty_cash_model'));
        $this->template->title('Report Petty Cash');
        $this->template->page_icon('fa fa-building-o');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = [
            'has_download' => has_permission($this->downloadPermission)
        ];

        $this->template->set($data);
        $this->template->render('index');
    }

    public function get_data()
    {
        $this->auth->restrict($this->viewPermission);

        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        if (empty($start_date) && empty($end_date)) {
            echo json_encode([
                'draw' => intval($this->input->post('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
            return;
        }

        $saldo_awal = $this->Report_petty_cash_model->get_saldo_awal($start_date);
        $records = $this->Report_petty_cash_model->get_report_data($start_date, $end_date);

        $data = [];
        $running_balance = $saldo_awal;
        $no = 1;

        // Saldo Awal Row
        $data[] = [
            'no' => '',
            'no_transaksi' => '',
            'tanggal' => '',
            'coa' => ' ',
            'company' => '',
            'pengeluaran' => '',
            'jenis_jurnal' => '',
            'debit' => 'saldo awal >>',
            'kredit' => '',
            'saldo' => number_format($saldo_awal),
            'keterangan' => 'Saldo Awal Petty Cash'
        ];

        foreach ($records as $row) {
            $running_balance = $running_balance + $row->debit - $row->kredit;

            $data[] = [
                'no' => $no++,
                'no_transaksi' => $row->no_transaksi,
                'tanggal' => date('d/m/Y', strtotime($row->tanggal)),
                'coa' => ' ' . $row->coa,
                'company' => $row->company,
                'pengeluaran' => $row->pengeluaran,
                'jenis_jurnal' => $row->jenis_jurnal,
                'debit' => $row->debit > 0 ? 'Rp ' . number_format($row->debit) : '',
                'kredit' => $row->kredit > 0 ? 'Rp ' . number_format($row->kredit) : '',
                'saldo' => 'Rp ' . number_format($running_balance),
                'keterangan' => $row->keterangan
            ];
        }

        echo json_encode([
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }

    public function export_excel()
    {
        $this->auth->restrict($this->downloadPermission);

        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        if (empty($start_date) && empty($end_date)) {
            echo "<script>alert('Silakan pilih filter periode laporan terlebih dahulu!'); window.close();</script>";
            return;
        }

        $saldo_awal = $this->Report_petty_cash_model->get_saldo_awal($start_date);
        $records = $this->Report_petty_cash_model->get_report_data($start_date, $end_date);

        $data = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'saldo_awal' => $saldo_awal,
            'records' => $records
        ];

        $this->load->view('export_excel', $data);
    }
}
