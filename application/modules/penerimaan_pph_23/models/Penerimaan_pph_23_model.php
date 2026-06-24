<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Penerimaan_pph_23_model extends BF_Model
{

    protected $ENABLE_ADD;
    protected $ENABLE_MANAGE;
    protected $ENABLE_VIEW;
    protected $ENABLE_DELETE;

    protected $consultant;
    protected $accounting;

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Penerimaan_PPH_23.Add');
        $this->ENABLE_MANAGE  = has_permission('Penerimaan_PPH_23.Manage');
        $this->ENABLE_VIEW    = has_permission('Penerimaan_PPH_23.View');
        $this->ENABLE_DELETE  = has_permission('Penerimaan_PPH_23.Delete');

        $this->consultant = $this->load->database('consultant', true);
        $this->accounting = $this->load->database('accounting', true);
    }

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM tr_penerimaan_pph_23 WHERE id LIKE '%/" . int_to_roman(date('m')) . "/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2++;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '/PNR-PPH-23/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    public function generate_no_jurnal($nomor)
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(no_jurnal) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2 = $urutan2 + $nomor;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');

        return $kode_trans;
    }

    public function create_jurnal($id)
    {
        $get_penerimaan_pph23 = $this->db->get_where('tr_penerimaan_pph_23', ['id' => $id])->row();

        $id_invoice = (!empty($get_penerimaan_pph23)) ? $get_penerimaan_pph23->id_inv : '';
        $get_invoice = $this->db->get_where('tr_invoicing', ['id' => $id_invoice])->row();

        $tipe_invoice = (!empty($get_invoice)) ? $get_invoice->tipe_invoice : '';
        $coa_pph = ($tipe_invoice == '1') ? '1106-01-05' : '1106-01-02';
        $arr_coa_jurnal = [$coa_pph, '1102-01-01'];

        $id_penawaran = (!empty($get_invoice)) ? $get_invoice->id_penawaran : '';

        $this->consultant->select('a.id, a.nm_company');
        $this->consultant->from('kons_tr_company a');
        $this->consultant->join('kons_tr_penawaran b', 'b.company = a.id');
        $this->consultant->where('b.id_quotation', $id_penawaran);
        $get_company = $this->consultant->get()->row();

        $id_company = (!empty($get_company)) ? $get_company->id : '';
        $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';


        $arr_input_jurnal = [];
        $no = 0;
        foreach ($arr_coa_jurnal as $item) {

            $get_coa = $this->accounting->get_where('coa_master', ['no_perkiraan' => $item])->row();

            $nm_coa = (!empty($get_coa)) ? $get_coa->nama : '';

            $debit = 0;
            $kredit = 0;
            if ($item == $coa_pph) {
                $debit = $get_penerimaan_pph23->nilai_setor;
            } else {
                $kredit = $get_penerimaan_pph23->nilai_setor;
            }

            $no_jurnal = $this->generate_no_jurnal($no);
            $arr_input_jurnal[] = [
                'no_jurnal' => $no_jurnal,
                'tgl_jurnal' => date('Y-m-d'),
                'coa' => $item,
                'id_company' => $id_company,
                'nm_company' => $nm_company,
                'nm_coa' => $nm_coa,
                'debit' => $debit,
                'kredit' => $kredit,
                'keterangan' => 'Penerimaan Piutang PPh 23 - ' . $item . ' - ' . $id,
                'sts' => '',
                'no_transaksi' => $id,
                'jenis_transaksi' => 'Penerimaan Piutang PPH 23',
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
        }

        $this->db->trans_begin();

        $this->db->insert_batch('tr_jurnal', $arr_input_jurnal);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
    }

    public function get_alokasi_penerimaan_pph23()
    {
        $draw   = intval($this->input->post('draw'));
        $length = intval($this->input->post('length'));
        $start  = intval($this->input->post('start'));
        $search = $this->input->post('search');
        $search_value = isset($search['value']) ? trim($search['value']) : '';

        // Count total records (tanpa filter search)
        $records_total = $this->db->query("
            SELECT COUNT(DISTINCT a.id) as total
            FROM tr_penerimaan_piutang_detail a
            JOIN tr_invoicing b ON b.id = a.id_inv
            JOIN tr_penerimaan_piutang c ON c.no_surat = a.id_header
            WHERE c.pph23_dipotong = 'Y'
        ")->row()->total;

        // Count filtered records
        $this->db->select('COUNT(DISTINCT a.id) as total');
        $this->db->from('tr_penerimaan_piutang_detail a');
        $this->db->join('tr_invoicing b', 'b.id = a.id_inv');
        $this->db->join('tr_penerimaan_piutang c', 'c.no_surat = a.id_header');
        $this->db->where('c.pph23_dipotong', 'Y');

        if ($search_value !== '') {
            $this->db->group_start();
            $this->db->like('b.no_invoice', $search_value, 'both');
            $this->db->or_like('a.nm_customer', $search_value, 'both');
            $this->db->or_like('b.print_keterangan', $search_value, 'both');
            $this->db->or_like('b.nm_project', $search_value, 'both');
            $this->db->group_end();
        }

        $records_filtered = $this->db->get()->row()->total;

        // Main query with limit
        $this->db->select('a.id, a.id_inv, a.nm_customer, a.pph23, a.id_header, b.print_keterangan, b.nm_project, b.no_invoice');
        $this->db->from('tr_penerimaan_piutang_detail a');
        $this->db->join('tr_invoicing b', 'b.id = a.id_inv');
        $this->db->join('tr_penerimaan_piutang c', 'c.no_surat = a.id_header');
        $this->db->where('c.pph23_dipotong', 'Y');

        if ($search_value !== '') {
            $this->db->group_start();
            $this->db->like('b.no_invoice', $search_value, 'both');
            $this->db->or_like('a.nm_customer', $search_value, 'both');
            $this->db->or_like('b.print_keterangan', $search_value, 'both');
            $this->db->or_like('b.nm_project', $search_value, 'both');
            $this->db->group_end();
        }

        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'DESC');
        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result_array();

        // Batch fetch status penerimaan PPH 23 (menghindari N+1 query)
        $detail_ids = array_column($get_data, 'id');
        $pph23_map = [];
        if (!empty($detail_ids)) {
            $this->db->where_in('id_detail_penerimaan', $detail_ids);
            $pph23_rows = $this->db->get('tr_penerimaan_pph_23')->result_array();
            foreach ($pph23_rows as $row) {
                $pph23_map[$row['id_detail_penerimaan']] = $row;
            }
        }

        // Build response data
        $hasil = [];
        $no = $start;
        foreach ($get_data as $item) {
            $no++;

            $is_lunas = isset($pph23_map[$item['id']]);

            if ($is_lunas) {
                $pph23_data = $pph23_map[$item['id']];
                $status = '<span class="badge bg-green">Lunas</span>';
                $action = '<a href="' . base_url('uploads/penerimaan_pph_23/' . $pph23_data['upload_bukti_setor']) . '" target="_blank" class="btn btn-sm btn-info" title="Lihat Bukti Setor"><i class="fa fa-download"></i></a>';
            } else {
                $status = '<span class="badge bg-red">Belum Lunas</span>';
                $action = '<a href="' . base_url('penerimaan_pph_23/add/' . $item['id']) . '" class="btn btn-sm btn-primary" title="Setor PPH 23"><i class="fa fa-money"></i></a>';
            }

            $hasil[] = [
                'no'                 => $no,
                'no_invoice'         => htmlspecialchars($item['no_invoice'], ENT_QUOTES, 'UTF-8'),
                'nm_customer'        => htmlspecialchars($item['nm_customer'], ENT_QUOTES, 'UTF-8'),
                'nm_project'         => htmlspecialchars($item['nm_project'], ENT_QUOTES, 'UTF-8'),
                'keterangan_invoice' => htmlspecialchars($item['print_keterangan'], ENT_QUOTES, 'UTF-8'),
                'nilai_pph'          => number_format($item['pph23'], 0, ',', '.'),
                'status'             => $status,
                'action'             => $action
            ];
        }

        $response = [
            'draw'            => $draw,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
            'data'            => $hasil
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
