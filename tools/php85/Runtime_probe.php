<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Runtime_probe extends MX_Controller
{
    public function index()
    {
        $this->load->library(['session', 'Mpdf', 'PHPExcel']);
        $counter = (int) $this->session->userdata('runtime_probe') + 1;
        $this->session->set_userdata('runtime_probe', $counter);
        session_write_close();

        $this->db->trans_start();
        $this->db->insert('probe_log', ['counter' => $counter]);
        $committed = $this->db->trans_complete();

        $this->db->trans_start();
        $this->db->insert('probe_log', ['counter' => $counter]);
        $rolledBack = !$this->db->trans_complete();

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'php' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'ci' => CI_VERSION,
            'document_loaders' => $this->mpdf instanceof \Mpdf\Mpdf
                && $this->phpexcel instanceof \PhpOffice\PhpSpreadsheet\Spreadsheet,
            'counter' => $counter,
            'committed' => $committed,
            'rolled_back' => $rolledBack,
            'rows' => $this->db->count_all('probe_log'),
        ], JSON_THROW_ON_ERROR));
    }
}
