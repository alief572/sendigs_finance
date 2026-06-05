<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Historical_alokasi_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_historical_alokasi()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
        $bank = $this->input->post('bank');

        // Count total records first
        $this->db->select('COUNT(DISTINCT a.id) as total');
        $this->db->from('tr_alokasi_detail a');
        $this->db->join('list_bank b', 'b.id = a.jenis_bank', 'left');
        $this->db->join('ms_bank c', 'c.id = a.tipe_bank', 'left');

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
            $this->db->or_like('a.keterangan', $search['value'], 'both');
            $this->db->group_end();
        }
        $count_result = $this->db->get()->row();
        $count_all = $count_result ? (int)$count_result->total : 0;

        // Fetch actual data
        $this->db->select('a.id, a.keterangan, a.tanggal_transaksi, a.nominal_debit, a.nominal_kredit, a.saldo, a.sts, b.nama_bank, c.rekening, c.nama');
        $this->db->from('tr_alokasi_detail a');
        $this->db->join('list_bank b', 'b.id = a.jenis_bank', 'left');
        $this->db->join('ms_bank c', 'c.id = a.tipe_bank', 'left');

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
            $this->db->or_like('a.keterangan', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'DESC');
        $this->db->limit($length, $start);
        $get_data = $this->db->get()->result_array();

        $hasil = [];
        $no = ($start + 0);

        $jenis_labels = [
            '1' => 'Penerimaan Piutang',
            '2' => 'Unlocated Penerimaan',
            '3' => 'Pengembalian Kasbon',
            '4' => 'Mutasi',
            '5' => 'Transaksi Bank',
            '6' => 'Pembayaran',
            '7' => 'Alokasi Kalibrasi'
        ];

        foreach ($get_data as $item) {
            $no++;

            // Format status badge
            $status = '<span class="badge bg-blue">Open</span>';

            // Check if it has splits
            $split_data = $this->db->get_where('tr_alokasi_split', ['id_alokasi_detail' => $item['id']])->result_array();
            if (!empty($split_data)) {
                $badges = '';
                foreach ($split_data as $split) {
                    $label = isset($jenis_labels[$split['jenis_alokasi']]) ? $jenis_labels[$split['jenis_alokasi']] : 'Split';
                    $badges .= '<span class="badge bg-purple" style="margin-right:2px;">' . $label . '</span>';
                }
                $status = $badges;
            } else if ($item['sts'] !== '0') {
                $txt = isset($jenis_labels[$item['sts']]) ? $jenis_labels[$item['sts']] : 'Processed';
                $status = '<span class="badge bg-green">' . $txt . '</span>';
            }

            $tanggal_transaksi = date('d-F-Y', strtotime($item['tanggal_transaksi']));
            if ($item['tanggal_transaksi'] == '0000-00-00') {
                $tanggal_transaksi = 'PEND';
            }

            $btn_history = '<button type="button" class="btn btn-sm btn-info btn_view_history" title="View History" data-id="' . $item['id'] . '"><i class="fa fa-history"></i> History</button>';

            $hasil[] = [
                'no' => $no,
                'tanggal_transaksi' => $tanggal_transaksi,
                'bank' => $item['nama_bank'] . ' - ' . $item['rekening'] . ' - ' . $item['nama'],
                'keterangan' => $item['keterangan'],
                'debit' => number_format($item['nominal_debit'], 2),
                'kredit' => number_format($item['nominal_kredit'], 2),
                'saldo' => number_format($item['saldo'], 2),
                'status_alokasi' => $status,
                'action' => '<div class="text-center">' . $btn_history . '</div>'
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

    public function get_timeline($id_detail)
    {
        // Get logs
        $this->db->select('h.*, u.nm_lengkap');
        $this->db->from('log_alokasi_history h');
        $this->db->join('users u', 'u.id_user = h.created_by', 'left');
        $this->db->where('h.id_alokasi_detail', $id_detail);
        $this->db->order_by('h.created_date', 'ASC');
        $logs = $this->db->get()->result_array();

        // If no log is found (e.g. legacy records), construct default UPLOAD log
        if (empty($logs)) {
            $this->db->select('a.*, u.nm_lengkap');
            $this->db->from('tr_alokasi_detail a');
            $this->db->join('users u', 'u.id_user = a.created_by', 'left');
            $this->db->where('a.id', $id_detail);
            $detail = $this->db->get()->row_array();

            if ($detail) {
                $log_desc = 'Upload rekening koran: ' . $detail['keterangan'] .
                    ' (Debit: Rp. ' . number_format($detail['nominal_debit'], 2) .
                    ', Kredit: Rp. ' . number_format($detail['nominal_kredit'], 2) . ')';

                $logs[] = [
                    'id' => 0,
                    'id_alokasi_detail' => $id_detail,
                    'action' => 'UPLOAD_REKENING',
                    'deskripsi_log' => $log_desc,
                    'created_by' => $detail['created_by'],
                    'created_date' => $detail['created_date'],
                    'nm_lengkap' => $detail['nm_lengkap'] ? $detail['nm_lengkap'] : 'System'
                ];
            }
        } else {
            // Fill nm_lengkap if null but created_by is text/sys
            foreach ($logs as &$log) {
                if (empty($log['nm_lengkap'])) {
                    $log['nm_lengkap'] = $log['created_by'] ? $log['created_by'] : 'System';
                }
            }
        }

        return $logs;
    }
}
