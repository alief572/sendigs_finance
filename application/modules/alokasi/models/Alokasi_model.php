<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Alokasi_model extends BF_Model
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


        $this->db->select('a.id, a.tipe_bank, a.tanggal_transaksi_from, a.tanggal_transaksi_to, a.saldo_awal, a.total_debit, a.total_credit, a.saldo_akhir, a.status_alokasi');
        $this->db->from('tr_alokasi a');
        $this->db->where('a.deleted', '0');
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.tanggal_transaksi_from', $search['value'], 'both');
            $this->db->or_like('a.tanggal_transaksi_to', $search['value'], 'both');
            $this->db->or_like('a.total_debit', $search['value'], 'both');
            $this->db->or_like('a.total_credit', $search['value'], 'both');
            $this->db->or_like('a.saldo_akhir', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.id');
        $this->db->from('tr_alokasi a');
        $this->db->where('a.deleted', '0');
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.tanggal_transaksi_from', $search['value'], 'both');
            $this->db->or_like('a.tanggal_transaksi_to', $search['value'], 'both');
            $this->db->or_like('a.total_debit', $search['value'], 'both');
            $this->db->or_like('a.total_credit', $search['value'], 'both');
            $this->db->or_like('a.saldo_akhir', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_date', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];

        $no = (0 + $start);

        foreach ($get_data->result_array() as $item) {
            $no++;

            $status = '<button type="button" class="btn btn-sm btn-primary">Open</button>';
            $btn_alokasi = '<button type="button" class="btn btn-sm btn-primary btn_alokasi" title="Alokasi" data-id="' . $item['id'] . '"><i class="fa fa-money"></i></button>';
            if ($item['status_alokasi'] == '2') {
                $status = '<button type="button" class="btn btn-sm btn-success">Closed</button>';
                $btn_alokasi = '';
            }

            $nm_bank = ($item['tipe_bank'] == '1') ? "Bank BCA" : "Bank OCBC";

            $hasil[] = [
                'no' => $no,
                'tanggal_transaksi_bank' => date('d F Y', strtotime($item['tanggal_transaksi_from'])) . ' - ' . date('d F Y', strtotime($item['tanggal_transaksi_to'])),
                'bank' => $nm_bank,
                'total_debit' => 'Rp. ' . number_format($item['total_debit']),
                'total_kredit' => 'Rp. ' . number_format($item['total_credit']),
                'saldo_akhir' => 'Rp. ' . number_format($item['saldo_akhir']),
                'status_alokasi' => $status,
                'action' => $btn_alokasi
            ];
        }

        $json = [
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ];

        echo json_encode($json);
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
