<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Alokasi_kalibrasi_model extends BF_Model
{

    protected $ENABLE_ADD;
    protected $ENABLE_MANAGE;
    protected $ENABLE_VIEW;
    protected $ENABLE_DELETE;

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Alokasi.Add');
        $this->ENABLE_MANAGE  = has_permission('Alokasi.Manage');
        $this->ENABLE_VIEW    = has_permission('Alokasi.View');
        $this->ENABLE_DELETE  = has_permission('Alokasi.Delete');
    }

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM tr_alokasi WHERE id LIKE '%/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 3);
        $urutan2++;
        $urut2            = sprintf('%03s', $urutan2);
        $kode_trans        = $urut2 . '/RKN-KRG/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    public function get_alokasi()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
        $bank = $this->input->post('bank');

        $this->db->select('a.id, a.keterangan, a.tanggal_transaksi, a.nominal_debit, a.nominal_kredit, a.saldo, a.sts, b.nama_bank, c.rekening, c.nama');
        $this->db->from('tr_alokasi_detail a');
        $this->db->join('list_bank b', 'b.id = a.jenis_bank', 'left');
        $this->db->join('ms_bank c', 'c.id = a.tipe_bank', 'left');
        $this->db->where('a.sts', '7');
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
        $this->db->group_by('a.id');

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        $this->db->limit($length, $start);
        $get_data = $this->db->get()->result_array();

        $hasil = [];

        $no = ($start + 0);
        foreach ($get_data as $item) {
            $no++;

            $status = '<span class="badge bg-blue">Open</span>';
            if ($item['sts'] !== '0') {
                $txt = '';
                if ($item['sts'] == '1') {
                    $txt = 'Penerimaan Piutang';
                } else if ($item['sts'] == '2') {
                    $txt = 'Unlocated Penerimaan';
                } else if ($item['sts'] == '3') {
                    $txt = 'Pengembalian Kasbon';
                } else if ($item['sts'] == '4') {
                    $txt = 'Mutasi';
                } else if ($item['sts'] == '5') {
                    $txt = 'Transaksi Bank';
                } else if ($item['sts'] == '6') {
                    $txt = 'Pembayaran';
                } else if ($item['sts'] == '7') {
                    $txt = 'Alokasi Kalibrasi';
                }
                $status = '<span class="badge bg-green">' . $txt . '</span>';
            }

            // $btn_alokasi = '<button type="button" class="btn btn-sm btn-primary btn_alokasi" title="Alokasi" data-id="' . $item['id'] . '"><i class="fa fa-money"></i></button>';
            // if ($item['sts'] !== '0') {
            $btn_alokasi = '';
            // }

            $tanggal_transaksi = date('d-F-Y', strtotime($item['tanggal_transaksi']));
            if ($item['tanggal_transaksi'] == '0000-00-00') {
                $tanggal_transaksi = 'PEND';
            }

            $nominal = ($item['nominal_debit'] > 0) ? $item['nominal_debit'] : $item['nominal_kredit'];

            $hasil[] = [
                'no' => $no,
                'tanggal_transaksi' => $tanggal_transaksi,
                'bank' => $item['nama_bank'] . ' - ' . $item['rekening'] . ' - ' . $item['nama'],
                'keterangan' => $item['keterangan'],
                'nominal' => $nominal
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
}
