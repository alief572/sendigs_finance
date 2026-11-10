<?php defined('BASEPATH') || exit('No direct script access allowed');

class Audit_trail_model extends CI_Model
{
    private $columns = array(1 => 'created_at', 2 => 'username', 3 => 'module', 4 => 'methode', 5 => 'status', 6 => 'message');

    public function options($input)
    {
        if (!is_array($input)) { throw new InvalidArgumentException('Filter tidak valid.'); }
        $today = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        $options = array();
        foreach (array('date_from' => $today->format('Y-m-01'), 'date_to' => $today->format('Y-m-d'), 'module' => '', 'status' => '') as $key => $default) {
            $value = array_key_exists($key, $input) ? $input[$key] : $default;
            if (!is_string($value) || strlen($value) > 200) { throw new InvalidArgumentException('Filter tidak valid.'); }
            $options[$key] = trim($value);
        }
        foreach (array('date_from', 'date_to') as $key) {
            if ($options[$key] !== '') {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $options[$key], new DateTimeZone('Asia/Jakarta'));
                if (!$date || $date->format('Y-m-d') !== $options[$key] || substr($options[$key], 0, 4) < '1000' || substr($options[$key], 0, 4) > '9998') {
                    throw new InvalidArgumentException('Tanggal harus valid dengan format YYYY-MM-DD.');
                }
            }
        }
        if ($options['date_from'] !== '' && $options['date_to'] !== '' && $options['date_from'] > $options['date_to']) {
            throw new InvalidArgumentException('Tanggal Dari tidak boleh melewati Sampai.');
        }
        if (!in_array($options['status'], array('', '0', '1', 'unknown'), true)) { throw new InvalidArgumentException('Status tidak valid.'); }
        $search = isset($input['search']) ? $input['search'] : '';
        if (is_array($search) && array_key_exists('value', $search)) { $search = $search['value']; }
        if (!is_string($search) || strlen($search) > 500) { throw new InvalidArgumentException('Pencarian tidak valid.'); }
        $options['search'] = trim($search);
        foreach (array('draw' => 0, 'start' => 0, 'length' => 25) as $key => $default) {
            $value = isset($input[$key]) ? $input[$key] : $default;
            if (filter_var($value, FILTER_VALIDATE_INT) === false) { throw new InvalidArgumentException('Pagination tidak valid.'); }
            $options[$key] = max(0, (int) $value);
        }
        $options['length'] = max(1, min(100, $options['length']));
        $options['sort'] = 'created_at'; $options['direction'] = 'desc';
        if (isset($input['order'][0]) && is_array($input['order'][0])) {
            $order = $input['order'][0];
            $column = isset($order['column']) ? filter_var($order['column'], FILTER_VALIDATE_INT) : false;
            if ($column !== false && isset($this->columns[$column]) && isset($order['dir']) && in_array($order['dir'], array('asc', 'desc'), true)) {
                $options['sort'] = $this->columns[$column]; $options['direction'] = $order['dir'];
            }
        }
        return $options;
    }

    private function filtered($options)
    {
        $this->db->from('logs');
        if ($options['date_from'] !== '') { $this->db->where('created_at >=', $options['date_from'] . ' 00:00:00'); }
        if ($options['date_to'] !== '') {
            $end = new DateTimeImmutable($options['date_to'], new DateTimeZone('Asia/Jakarta'));
            $this->db->where('created_at <', $end->modify('+1 day')->format('Y-m-d') . ' 00:00:00');
        }
        if ($options['module'] !== '') { $this->db->where('module', $options['module']); }
        if ($options['status'] === 'unknown') {
            $this->db->group_start()->where_not_in('status', array('0', '1'))->or_where('status', null)->group_end();
        } elseif ($options['status'] !== '') { $this->db->where('status', $options['status']); }
        if ($options['search'] !== '') {
            $this->db->group_start();
            foreach (array('message', 'username', 'module', 'methode', 'ip_address', 'sql') as $index => $column) {
                if ($index === 0) { $this->db->like($column, $options['search']); }
                else { $this->db->or_like($column, $options['search']); }
            }
            $this->db->group_end();
        }
    }

    public function page($options)
    {
        $total = (int) $this->db->count_all('logs');
        $this->filtered($options);
        $filtered = (int) $this->db->count_all_results();
        $this->filtered($options);
        $this->db->select('id, created_at, username, module, methode, status, message');
        $rows = $this->ordered($options)->limit($options['length'], $options['start'])->get();
        if (!$rows) { throw new RuntimeException('Logs unavailable'); }
        return array('draw' => $options['draw'], 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $rows->result_array());
    }

    private function ordered($options)
    {
        return $this->db->order_by($options['sort'], $options['direction'])->order_by('id', 'desc');
    }

    public function modules()
    {
        $result = $this->db->distinct()->select('module')->where('module IS NOT NULL', null, false)->where('module !=', '')->order_by('module', 'asc')->get('logs');
        if (!$result) { throw new RuntimeException('Logs unavailable'); }
        return array_column($result->result_array(), 'module');
    }

    public function detail($id)
    {
        if (!is_string($id) || $id === '' || strlen($id) > 50) { return null; }
        $query = $this->db->get_where('logs', array('id' => $id));
        if (!$query) { throw new RuntimeException('Logs unavailable'); }
        return $query->row_array() ?: null;
    }

    public function export_rows($options, $batchSize = 500)
    {
        $offset = 0;
        do {
            $this->filtered($options);
            $query = $this->ordered($options)->limit($batchSize, $offset)->get();
            if (!$query) { throw new RuntimeException('Logs unavailable'); }
            $rows = $query->result_array();
            foreach ($rows as $row) { yield $row; }
            $offset += count($rows);
        } while (count($rows) === $batchSize);
    }

    public static function status_label($status)
    {
        return (string) $status === '1' ? 'Sukses' : ((string) $status === '0' ? 'Gagal' : 'Tidak diketahui');
    }

    public static function csv_cell($value)
    {
        $value = $value === null ? '' : (string) $value;
        return preg_match('/^[\s\x00-\x20]*[=+@-]|^[\t\r\n]/', $value) ? "'" . $value : $value;
    }
}
