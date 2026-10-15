<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_jurnal_expense_model extends BF_Model
{
    const TYPES = ['Expense Report', 'Expense Report Consultant'];

    public function get_data_jurnal()
    {
        $post = $this->input->post();
        $post = is_array($post) ? $post : [];
        $draw = (int) $this->scalar($post['draw'] ?? 0);
        $start = max(0, (int) $this->scalar($post['start'] ?? 0));
        $length = (int) $this->scalar($post['length'] ?? 10);
        $length = in_array($length, [10, 25, 50, 100], true) ? $length : max(1, min(100, $length));
        $filter = $this->filters($post);
        $search = $this->scalar(is_array($post['search'] ?? null) ? ($post['search']['value'] ?? '') : '');
        $this->baseQuery($filter);
        $total = count($this->result($this->db->get())->result_array());
        $this->baseQuery($filter, $search);
        $filtered = count($this->result($this->db->get())->result_array());
        $this->baseQuery($filter, $search);
        $columns = [1=>'a.no_transaksi', 2=>'a.no_transaksi', 3=>'a.jenis_transaksi', 4=>'tgl_jurnal', 5=>'nm_company'];
        $order = is_array($post['order'] ?? null) ? ($post['order'][0] ?? []) : [];
        $column = (int) $this->scalar($order['column'] ?? 4);
        $direction = strtolower($this->scalar($order['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $this->db->order_by($columns[$column] ?? 'tgl_jurnal', $direction)->order_by('id', 'desc')->limit($length, $start);
        $rows = $this->result($this->db->get())->result_array();
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'no'=>'<span class="text-muted" style="font-weight:600;">' . ++$start . '</span>',
                'no_transaksi'=>'<span style="font-weight: 700; color: #0073b7;">' . $this->escape($row['no_transaksi']) . '</span>',
                'no_pengajuan'=>'<span style="font-weight: 600; color: #444;">' . $this->escape($row['no_transaksi']) . '</span>',
                'kategori_payment'=>'<span class="label label-primary" style="font-size:11px; padding:4px 8px; border-radius:4px; font-weight:600;"><i class="fa fa-file-text-o"></i> ' . $this->escape($row['jenis_transaksi']) . '</span>',
                'tanggal_jurnal'=>date('d F Y', strtotime($row['tgl_jurnal'])),
                'company'=>'<span class="label" style="background-color: #3c8dbc; color: #fff; font-size:11px; padding:4px 8px; border-radius:4px; font-weight:600;">' . $this->escape($row['nm_company']) . '</span>',
                'action'=>'<button type="button" class="btn btn-sm btn-info view_jurnal" data-id="' . (int) $row['id'] . '" data-no_transaksi="' . $this->escape($row['no_transaksi']) . '" title="View Detail Jurnal" style="border-radius: 6px; padding: 4px 10px; font-weight: 600;"><i class="fa fa-eye"></i> View</button>'
            ];
        }
        return ['draw'=>$draw, 'recordsTotal'=>$total, 'recordsFiltered'=>$filtered, 'data'=>$data];
    }

    public function get_list_jurnal($filter = [])
    {
        $this->baseQuery(is_array($filter) ? $filter : []);
        $rows = $this->result($this->db->order_by('tgl_jurnal', 'desc')->order_by('id', 'desc')->get())->result_array();
        foreach ($rows as &$row) {
            $row['no_pengajuan'] = $row['no_transaksi'];
            $row['kategori_payment'] = $row['jenis_transaksi'];
        }
        unset($row);
        return $rows;
    }

    public function get_no_payment_jurnal()
    {
        $this->baseQuery([]);
        $rows = $this->result($this->db->order_by('a.no_transaksi', 'asc')->get())->result_array();
        $numbers = array_unique(array_column($rows, 'no_transaksi'));
        return array_map(function ($number) { return ['no_transaksi'=>$number]; }, array_values($numbers));
    }

    public function get_company()
    {
        return [['id_company'=>'7','nm_company'=>'STM'], ['id_company'=>'3','nm_company'=>'Sustain'], ['id_company'=>'4','nm_company'=>'Vuca']];
    }

    public function filters($values)
    {
        $range = explode(' to ', $this->scalar($values['tgl_jurnal'] ?? ''));
        $from = $range[0] ?? '';
        $to = $range[1] ?? $from;
        foreach ([$from, $to] as $date) {
            if ($date !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) || date('Y-m-d', strtotime($date)) !== $date)) {
                throw new InvalidArgumentException('Rentang tanggal jurnal tidak valid.');
            }
        }
        if ($from > $to) { throw new InvalidArgumentException('Rentang tanggal jurnal tidak valid.'); }
        return ['tgl_from'=>$from, 'tgl_to'=>$to, 'no_transaksi'=>$this->scalar($values['no_transaksi'] ?? ''), 'company'=>$this->scalar($values['company'] ?? '')];
    }

    public function get_detail_jurnal($id)
    {
        if ($id <= 0) { throw new InvalidArgumentException('ID jurnal tidak valid.'); }
        $anchor = $this->result($this->db->get_where('tr_jurnal', ['id'=>$id]))->row();
        if (!$anchor || !in_array($anchor->jenis_transaksi, self::TYPES, true) || (string) $anchor->sts !== '1') {
            throw new InvalidArgumentException('Jurnal posted tidak tersedia.');
        }
        $this->db->where(['no_transaksi'=>$anchor->no_transaksi, 'jenis_transaksi'=>$anchor->jenis_transaksi, 'id_company'=>$anchor->id_company, 'sts'=>'1']);
        $this->db->where('(debit > 0 OR kredit > 0)', null, false)->order_by('id', 'asc');
        return $this->result($this->db->get('tr_jurnal'))->result();
    }

    private function baseQuery($filter, $search = '')
    {
        $this->db->select('MIN(a.id) AS id, a.no_transaksi, a.jenis_transaksi, a.id_company, MAX(a.nm_company) AS nm_company, MAX(a.tgl_jurnal) AS tgl_jurnal', false)
            ->from('tr_jurnal a')->where_in('a.jenis_transaksi', self::TYPES)
            ->where('a.sts', '1');
        if (!empty($filter['tgl_from'])) { $this->db->where('a.tgl_jurnal >=', $filter['tgl_from']); }
        if (!empty($filter['tgl_to'])) { $this->db->where('a.tgl_jurnal <=', $filter['tgl_to']); }
        if (!empty($filter['no_transaksi'])) { $this->db->where('a.no_transaksi', $filter['no_transaksi']); }
        $company = $filter['company'] ?? '';
        $aliases = ['3'=>[['3','6'],['Sustain','STM-Sustain','SENTRAL SUSTAINABILITY CONSULTING']], '4'=>[['4','1'],['Vuca','STM-Vuca']], '7'=>[['7'],['STM']]];
        if (isset($aliases[$company])) {
            $this->db->group_start()->where_in('a.id_company', $aliases[$company][0])->or_where_in('a.nm_company', $aliases[$company][1])->group_end();
        } elseif ($company !== '') { $this->db->where('a.id_company', $company); }
        if ($search !== '') {
            $this->db->group_start()->like('a.no_transaksi', $search)->or_like('a.jenis_transaksi', $search)->or_like('a.nm_company', $search)->group_end();
        }
        $this->db->group_by(['a.no_transaksi', 'a.jenis_transaksi', 'a.id_company']);
    }

    private function result($query)
    {
        if ($query === false) { throw new RuntimeException('Gagal membaca data jurnal.'); }
        return $query;
    }
    private function scalar($value) { return is_scalar($value) ? mb_substr((string) $value, 0, 255, 'UTF-8') : ''; }
    private function escape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
}
