<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alokasi_penampung_model extends BF_Model
{
    public function banks()
    {
        return $this->rows('SELECT a.id, a.nama, a.rekening, b.nama_bank FROM ms_bank a
            LEFT JOIN list_bank b ON b.id = a.bank WHERE a.deleted = 0 ORDER BY a.nama, a.id');
    }

    public function listing($jenis, $post)
    {
        if (!in_array($jenis, array(3, 4, 5, 6), true) || !is_array($post)) {
            throw new InvalidArgumentException('Jenis alokasi tidak valid.');
        }
        $draw = $this->integer($post, 'draw', 0, 0, 2147483647);
        $start = $this->integer($post, 'start', 0, 0, 2147483647);
        $length = $this->integer($post, 'length', 10, 1, 100);
        $bank = $this->text($post, 'bank');
        if ($bank !== '' && (!ctype_digit($bank) || strlen($bank) > 10 || (int)$bank < 1)) {
            throw new InvalidArgumentException('Bank tidak valid.');
        }
        $startDate = $this->text($post, 'startDate');
        $endDate = $this->text($post, 'endDate');
        foreach (array($startDate, $endDate) as $date) {
            $parsed = DateTime::createFromFormat('!Y-m-d', $date);
            if ($date !== '' && (!$parsed || $parsed->format('Y-m-d') !== $date || substr($date, 0, 4) === '0000')) {
                throw new InvalidArgumentException('Tanggal tidak valid.');
            }
        }
        if ($startDate !== '' && $endDate !== '' && $startDate > $endDate) {
            throw new InvalidArgumentException('Tanggal awal melebihi tanggal akhir.');
        }
        if (isset($post['search']) && !is_array($post['search'])) {
            throw new InvalidArgumentException('Pencarian tidak valid.');
        }
        $search = $this->text(isset($post['search']) ? $post['search'] : array(), 'value');
        if (mb_strlen($search) > 200) throw new InvalidArgumentException('Pencarian maksimal 200 karakter.');

        $from = ' FROM tr_alokasi_split s JOIN tr_alokasi_detail a ON a.id = s.id_alokasi_detail
            LEFT JOIN list_bank b ON b.id = a.jenis_bank LEFT JOIN ms_bank c ON c.id = a.tipe_bank
            WHERE s.jenis_alokasi = ?';
        $params = array($jenis);
        $total = (int)$this->rows('SELECT COUNT(*) AS n' . $from, $params)[0]['n'];
        foreach (array('a.tipe_bank = ?'=>$bank, 'a.tanggal_transaksi >= ?'=>$startDate, 'a.tanggal_transaksi <= ?'=>$endDate) as $clause=>$value) {
            if ($value !== '') { $from .= ' AND ' . $clause; $params[] = $value; }
        }
        if ($search !== '') {
            $clauses = array();
            foreach (array('a.tanggal_transaksi', 'a.reference_no', 'a.keterangan', 'b.nama_bank', 'c.nama', 'c.rekening', 's.nominal') as $column) {
                $clauses[] = $column . ' LIKE ?';
                $params[] = '%' . $search . '%';
            }
            $from .= ' AND (' . implode(' OR ', $clauses) . ')';
        }
        $filtered = (int)$this->rows('SELECT COUNT(*) AS n' . $from, $params)[0]['n'];
        $params[] = $length; $params[] = $start;
        $rows = $this->rows('SELECT a.tanggal_transaksi, a.reference_no, a.keterangan, b.nama_bank, c.rekening, c.nama, s.nominal'
            . $from . ' ORDER BY a.tanggal_transaksi DESC, s.id DESC LIMIT ? OFFSET ?', $params);
        $data = array();
        foreach ($rows as $row) {
            $bankParts = array_filter(array($row['nama_bank'], $row['rekening'], $row['nama']), function ($part) { return $part !== null && $part !== ''; });
            $data[] = array(
                'no' => ++$start,
                'tanggal_transaksi' => $row['tanggal_transaksi'] && $row['tanggal_transaksi'] !== '0000-00-00' ? date('d-F-Y', strtotime($row['tanggal_transaksi'])) : 'PEND',
                'reference_no' => (string)$row['reference_no'],
                'bank' => $bankParts ? implode(' - ', $bankParts) : '-',
                'keterangan' => (string)$row['keterangan'],
                'nominal' => number_format((float)$row['nominal'], 2)
            );
        }
        return array('draw'=>$draw, 'recordsTotal'=>$total, 'recordsFiltered'=>$filtered, 'data'=>$data);
    }

    private function rows($sql, $params = array())
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            $result = $this->db->query($sql, $params);
        } finally {
            $this->db->db_debug = $debug;
        }
        if (!$result) throw new RuntimeException('Gagal membaca daftar alokasi.');
        return $result->result_array();
    }

    private function text($post, $key)
    {
        if (!isset($post[$key])) return '';
        if (!is_string($post[$key]) && !is_int($post[$key])) throw new InvalidArgumentException('Filter tidak valid.');
        return trim((string)$post[$key]);
    }

    private function integer($post, $key, $default, $min, $max)
    {
        if (!isset($post[$key])) return $default;
        $value = filter_var($post[$key], FILTER_VALIDATE_INT, array('options'=>array('min_range'=>$min, 'max_range'=>$max)));
        if ($value === false) throw new InvalidArgumentException('Parameter halaman tidak valid.');
        return $value;
    }
}
