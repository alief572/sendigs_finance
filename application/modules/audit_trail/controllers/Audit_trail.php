<?php defined('BASEPATH') || exit('No direct script access allowed');

class Audit_trail extends Admin_Controller
{
    protected $viewPermission = 'Audit_Trail.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('audit_trail/Audit_trail_model');
        $this->template->title('Audit Trail');
        $this->template->page_icon('fa fa-history');
    }

    private function allowed()
    {
        if ($this->auth->has_permission($this->viewPermission)) { return true; }
        $this->json(array('error' => 'Anda tidak memiliki akses Audit Trail.'), 403);
        return false;
    }

    private function json($data, $status = 200)
    {
        $this->output->set_status_header($status)->set_content_type('application/json', 'utf-8')
            ->set_header('Cache-Control: no-store')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
    }

    public function index()
    {
        if (!$this->allowed()) { return; }
        $options = $this->Audit_trail_model->options(array());
        $data = array('date_from' => $options['date_from'], 'date_to' => $options['date_to'],
            'modules' => $this->Audit_trail_model->modules());
        $this->template->render('index', $data);
    }

    public function data()
    {
        if (!$this->allowed()) { return; }
        try {
            $options = $this->Audit_trail_model->options($this->input->get(null, false) ?: array());
            $this->json($this->Audit_trail_model->page($options));
        } catch (InvalidArgumentException $error) {
            $this->json(array('error' => $error->getMessage()), 422);
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: list unavailable.');
            $this->json(array('error' => 'Data Audit Trail belum dapat dimuat.'), 500);
        }
    }

    public function detail($id = null)
    {
        if (!$this->allowed()) { return; }
        try {
            $row = $this->Audit_trail_model->detail($id);
            if (!$row) { $this->json(array('error' => 'Log tidak ditemukan.'), 404); return; }
            $row['status_label'] = Audit_trail_model::status_label($row['status']);
            $this->json(array('data' => $row));
        } catch (Throwable $error) {
            log_message('error', 'Audit Trail: detail unavailable.');
            $this->json(array('error' => 'Detail Audit Trail belum dapat dimuat.'), 500);
        }
    }

    public function export_csv()
    {
        if (!$this->allowed()) { return; }
        try {
            $options = $this->Audit_trail_model->options($this->input->get(null, false) ?: array());
        } catch (InvalidArgumentException $error) {
            $this->json(array('error' => $error->getMessage()), 422); return;
        }
        // Keep batch pagination stable even while new activities are recorded.
        $previousSaveQueries = $this->db->save_queries;
        $this->db->save_queries = false;
        $output = null;
        try {
            if (!$this->db->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ') || !$this->db->trans_begin()) {
                throw new RuntimeException('Export transaction unavailable');
            }
            $rows = $this->Audit_trail_model->export_rows($options);
            // Start reading before sending download headers so initial DB errors stay JSON.
            $rows->rewind();
            $date = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="audit-trail-' . $date->format('Ymd-His') . '.csv"');
            header('Cache-Control: no-store');
            $output = fopen('php://output', 'wb');
            if (!$output) { throw new RuntimeException('Export output unavailable'); }
            fwrite($output, "\xEF\xBB\xBF");
            $this->write_csv($output, $rows);
            $this->db->trans_commit();
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Audit Trail: CSV export failed.');
            if (!headers_sent()) { $this->json(array('error' => 'Export Audit Trail gagal.'), 500); }
        } finally {
            if (is_resource($output)) { fclose($output); }
            $this->db->save_queries = $previousSaveQueries;
        }
    }

    protected function write_csv($output, $rows)
    {
        $fields = array('id', 'created_at', 'username', 'created_by', 'module', 'methode', 'status', 'message', 'data', 'sql', 'ip_address', 'agent', 'platform');
        fputcsv($output, array('ID', 'Waktu (WIB)', 'User', 'ID User', 'Modul', 'Aksi', 'Status', 'Pesan', 'Data', 'SQL', 'IP', 'Agent', 'Platform'), ',', '"', '');
        foreach ($rows as $row) {
            $values = array();
            foreach ($fields as $field) {
                $value = isset($row[$field]) ? $row[$field] : null;
                $values[] = Audit_trail_model::csv_cell($field === 'status' ? Audit_trail_model::status_label($value) : $value);
            }
            if (fputcsv($output, $values, ',', '"', '') === false) { throw new RuntimeException('Export output failed'); }
        }
    }
}
