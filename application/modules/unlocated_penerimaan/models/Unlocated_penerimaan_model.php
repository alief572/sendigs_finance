<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Unlocated_penerimaan_model extends BF_Model
{

    protected $ENABLE_ADD;
    protected $ENABLE_MANAGE;
    protected $ENABLE_VIEW;
    protected $ENABLE_DELETE;

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Unlocated Penerimaan.Add');
        $this->ENABLE_MANAGE  = has_permission('Unlocated Penerimaan.Manage');
        $this->ENABLE_VIEW    = has_permission('Unlocated Penerimaan.View');
        $this->ENABLE_DELETE  = has_permission('Unlocated Penerimaan.Delete');
    }

    public function get_unlocated_penerimaan()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
        $bank = $this->input->post('bank');

        $this->db->select('s.id, a.keterangan, a.tanggal_transaksi, a.nominal_debit, a.nominal_kredit, a.saldo, b.nama_bank, c.rekening, c.nama');
        $this->db->from('tr_alokasi_split s');
        $this->db->join('tr_alokasi_detail a', 'a.id = s.id_alokasi_detail');
        $this->db->join('list_bank b', 'b.id = a.jenis_bank', 'left');
        $this->db->join('ms_bank c', 'c.id = a.tipe_bank', 'left');
        $this->db->where('s.jenis_alokasi', 2);
        if (!empty($startDate)) {
            $this->db->where('a.tanggal_transaksi >=', $startDate);
        }
        if (!empty($endDate)) {
            $this->db->where('a.tanggal_transaksi <=', $endDate);
        }
        if (!empty($bank)) {
            $this->db->where('a.tipe_bank', $bank);
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.tanggal_transaksi', $search['value'], 'both');
            $this->db->or_like('b.nama_bank', $search['value'], 'both');
            $this->db->or_like('c.rekening', $search['value'], 'both');
            $this->db->or_like('c.nama', $search['value'], 'both');
            $this->db->or_like('a.nominal_debit', $search['value'], 'both');
            $this->db->or_like('a.nominal_kredit', $search['value'], 'both');
            $this->db->or_like('a.saldo', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('s.id');

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        $this->db->limit($length, $start);
        $get_data = $this->db->get()->result_array();

        $hasil = [];

        $no = ($start + 0);
        foreach ($get_data as $item) {
            $no++;

            $status = '<span class="badge bg-green">Unlocated Penerimaan</span>';

            $tanggal_transaksi = date('d-F-Y', strtotime($item['tanggal_transaksi']));
            if ($item['tanggal_transaksi'] == '0000-00-00') {
                $tanggal_transaksi = 'PEND';
            }

            $hasil[] = [
                'no' => $no,
                'tanggal_transaksi' => $tanggal_transaksi,
                'bank' => $item['nama_bank'] . ' - ' . $item['rekening'] . ' - ' . $item['nama'],
                'keterangan' => $item['keterangan'],
                'debit' => number_format($item['nominal_debit'], 2),
                'kredit' => number_format($item['nominal_kredit'], 2),
                'saldo' => number_format($item['saldo'], 2),
                'status_alokasi' => $status,
                'action' => '<div class="text-center"><input type="checkbox" class="check_item" data-id="' . $item['id'] . '"></div>'
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_all,
            'data' => $hasil
        ];

        echo json_encode($response);
    }

    public function update_alokasi_header($id)
    {
        $this->db->select('a.id');
        $this->db->from('tr_alokasi_detail a');
        $this->db->where('a.id_header', $id);
        $this->db->where('a.sts', '0');
        $check_detail = $this->db->get()->result_array();

        if (count($check_detail) < 1) {
            $this->db->update('tr_alokasi', ['status_alokasi' => 2], ['id' => $id]);
        }
    }

    public function rollback_data($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }

        $this->db->trans_start();

        foreach ($ids as $id) {
            // Get tr_alokasi_split using the ID passed (which is actually tr_alokasi_split.id)
            $split = $this->db->get_where('tr_alokasi_split', ['id' => $id])->row_array();
            
            if (!$split) {
                // If it's already deleted or not found, we could continue or rollback, but let's just continue
                continue;
            }

            $id_alokasi_detail = $split['id_alokasi_detail'];

            // Get id_header from tr_alokasi_detail
            $detail = $this->db->get_where('tr_alokasi_detail', ['id' => $id_alokasi_detail])->row_array();
            if (!$detail) {
                $this->db->trans_rollback();
                return false;
            }
            $id_header = $detail['id_header'];

            // Get all split details to be deleted for logging
            $splits_to_rollback = $this->db->get_where('tr_alokasi_split', ['id_alokasi_detail' => $id_alokasi_detail])->result_array();
            $jenis_labels = [
                '1' => 'Penerimaan Piutang',
                '2' => 'Unlocated Penerimaan',
                '3' => 'Pengembalian Kasbon',
                '4' => 'Mutasi',
                '5' => 'Transaksi Bank',
                '6' => 'Pembayaran',
                '7' => 'Alokasi Kalibrasi'
            ];
            $split_details = [];
            foreach ($splits_to_rollback as $split_item) {
                $label = isset($jenis_labels[$split_item['jenis_alokasi']]) ? $jenis_labels[$split_item['jenis_alokasi']] : 'Unknown';
                $split_details[] = $label . ' (Rp. ' . number_format($split_item['nominal'], 2) . ')';
            }
            $log_desc = 'Rollback alokasi transaksi. Detail alokasi yang dibatalkan: ' . implode(', ', $split_details) . '. Transaksi dikembalikan ke status Open.';
            
            $log_data = [
                'id_alokasi_detail' => $id_alokasi_detail,
                'action' => 'ROLLBACK_UNLOCATED',
                'deskripsi_log' => $log_desc,
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('log_alokasi_history', $log_data);

            // FULL ROLLBACK: Delete ALL split records for this transaction
            $this->db->delete('tr_alokasi_split', ['id_alokasi_detail' => $id_alokasi_detail]);

            // Update tr_alokasi_detail sts to 0 (Open)
            $this->db->update('tr_alokasi_detail', ['sts' => '0'], ['id' => $id_alokasi_detail]);

            // Check if we need to open the header again
            $this->db->select('a.id');
            $this->db->from('tr_alokasi_detail a');
            $this->db->where('a.id_header', $id_header);
            $this->db->where('a.sts', '0');
            $check_detail = $this->db->get()->result_array();

            if (count($check_detail) > 0) {
                // If there's at least one open detail, the header must be 1 (Open)
                $this->db->update('tr_alokasi', ['status_alokasi' => 1], ['id' => $id_header]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return true;
    }
}
