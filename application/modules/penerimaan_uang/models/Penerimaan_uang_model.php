<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Penerimaan_uang_model extends BF_Model
{

    protected $ENABLE_ADD;
    protected $ENABLE_MANAGE;
    protected $ENABLE_VIEW;
    protected $ENABLE_DELETE;

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Penerimaan_Uang.Add');
        $this->ENABLE_MANAGE  = has_permission('Penerimaan_Uang.Manage');
        $this->ENABLE_VIEW    = has_permission('Penerimaan_Uang.View');
        $this->ENABLE_DELETE  = has_permission('Penerimaan_Uang.Delete');
    }

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(no_surat) as maxP FROM tr_penerimaan_piutang WHERE no_surat LIKE '%/" . int_to_roman(date('m')) . "/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2++;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '/RKN-KRG/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    public function generate_id_invoice_jurnal($nomor)
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2 = $urutan2 + $nomor;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');

        return $kode_trans;
    }

    public function get_alokasi_penerimaan()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.id, a.tanggal_transaksi, a.keterangan, a.nominal_debit, a.nominal_kredit, a.saldo, a.reference_no, a.nilai_terpakai, b.nama as nama_bank_acc, b.rekening, c.nama_bank as nm_bank');
        $this->db->from('tr_alokasi_detail a');
        $this->db->join('ms_bank b', 'b.id = a.tipe_bank', 'left');
        $this->db->join('list_bank c', 'c.id = a.jenis_bank', 'left');
        $this->db->where('a.sts', '1');
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('DATE_FORMAT(a.tanggal_transaksi,"%d-%M-%Y")', $search['value'], 'both');
            $this->db->or_like('a.reference_no', $search['value'], 'both');
            $this->db->or_like('b.nama', $search['value'], 'both');
            $this->db->or_like('b.rekening', $search['value'], 'both');
            $this->db->or_like('c.nama_bank', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.nilai_terpakai', '');


        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        $this->db->limit($length, $start);
        $get_data = $this->db->get()->result_array();

        $hasil = [];

        $no = (0 + $start);
        foreach ($get_data as $item) {
            $no++;

            $tgl_transaksi_bank = '';
            if ($item['tanggal_transaksi'] == '' || $item['tanggal_transaksi'] == '0000-00-00') {
                $tgl_transaksi_bank = 'PEND';
            } else {
                $tgl_transaksi_bank = date('d-F-Y', strtotime($item['tanggal_transaksi']));
            }

            $nominal = ($item['nominal_debit'] < 1) ? $item['nominal_kredit'] : $item['nominal_debit'];

            $status = '<span class="badge bg-yellow">Draft</span>';

            $action = '<a href="' . base_url('penerimaan_uang/add_penerimaan_uang/' . $item['id']) . '" class="btn btn-sm btn-primary" title="Alokasi Penerimaan Uang"><i class="fa fa-plus"></i></a>';

            if (
                ($item['nominal_debit'] > 0 && ($item['nominal_debit'] - $item['nilai_terpakai']) <= 0) ||
                ($item['nominal_kredit'] > 0 && ($item['nominal_kredit'] - $item['nilai_terpakai']) <= 0)
            ) {
                $status = '<span class="badge bg-green">Used</span>';
                $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang', ['id_alokasi' => $item['id']])->row_array();
                if (!empty($get_penerimaan)) {
                    $action = '<button type="button" class="btn btn-sm btn-info detail" title="View Penerimaan Piutang" data-id="' . $get_penerimaan['id'] . '"><i class="fa fa-eye"></i></button>';
                } else {
                    $action = '';
                }
            }

            $hasil[] = [
                'no' => $no,
                'tgl_transaksi_bank' => $tgl_transaksi_bank,
                'reference_no' => $item['reference_no'],
                'bank' => $item['nm_bank'] . ' - ' . $item['rekening'] . ' - ' . $item['nama_bank_acc'],
                'keterangan' => $item['keterangan'],
                'nominal' => number_format($nominal),
                'status' => $status,
                'action' => $action
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
}
