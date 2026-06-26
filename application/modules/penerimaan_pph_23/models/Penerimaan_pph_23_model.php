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
        $this->db->select('a.id, a.id_alokasi, a.id_inv, a.nm_customer, a.pph23, a.id_header, b.print_keterangan, b.nm_project, b.no_invoice, b.tipe_invoice, b.total_nominal, b.id_detail_plan_tagih, b.id_spk_penawaran, b.id_penawaran');
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

        // Batch fetch plan tagih detail
        $plan_tagih_ids = array_unique(array_filter(array_column($get_data, 'id_detail_plan_tagih')));
        $plan_tagih_map = [];
        if (!empty($plan_tagih_ids)) {
            $this->db->where_in('id', $plan_tagih_ids);
            $plan_tagih_rows = $this->db->get('kons_tr_plan_tagih_detail')->result_array();
            foreach ($plan_tagih_rows as $row) {
                $plan_tagih_map[$row['id']] = $row;
            }
        }

        // Collect IDs for consultant DB
        $spk_penawaran_ids = [];
        $penawaran_ids = [];
        foreach ($get_data as $item) {
            if (!empty($item['id_spk_penawaran'])) {
                $spk_penawaran_ids[] = $item['id_spk_penawaran'];
            }
            if (!empty($item['id_penawaran'])) {
                $penawaran_ids[] = $item['id_penawaran'];
            }

            if (empty($item['id_alokasi'])) {
                if (!empty($item['id_spk_penawaran'])) {
                    $id_plan = $item['id_detail_plan_tagih'];
                    if (isset($plan_tagih_map[$id_plan]) && !empty($plan_tagih_map[$id_plan]['id_spk_penawaran'])) {
                        $spk_penawaran_ids[] = $plan_tagih_map[$id_plan]['id_spk_penawaran'];
                    }
                }
            }
        }

        // Batch fetch spk penawaran from consultant DB
        $spk_penawaran_map = [];
        $spk_penawaran_ids = array_unique(array_filter($spk_penawaran_ids));
        if (!empty($spk_penawaran_ids)) {
            $spk_penawaran_rows = $this->consultant->select('a.*, b.nm_paket, c.nm_company')
                ->from('kons_tr_spk_penawaran a')
                ->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left')
                ->join('kons_tr_company c', 'c.id = a.id_company', 'left')
                ->where_in('a.id_spk_penawaran', $spk_penawaran_ids)
                ->get()
                ->result_array();
            foreach ($spk_penawaran_rows as $row) {
                $spk_penawaran_map[$row['id_spk_penawaran']] = $row;
            }
        }

        // Batch fetch penawaran dari consultant DB
        $penawaran_map = [];
        $penawaran_ids = array_unique(array_filter($penawaran_ids));
        if (!empty($penawaran_ids)) {
            $penawaran_rows = $this->consultant->select('a.*, c.nm_company')
                ->from('kons_tr_penawaran_non_konsultasi a')
                ->join('kons_tr_company c', 'c.id = a.id_company', 'left')
                ->where_in('a.id_penawaran', $penawaran_ids)
                ->get()
                ->result_array();
            foreach ($penawaran_rows as $row) {
                $penawaran_map[$row['id_penawaran']] = $row;
            }

            $penawaran_company_rows = $this->consultant->select('a.id_quotation, c.nm_company')
                ->from('kons_tr_penawaran a')
                ->join('kons_tr_company c', 'c.id = a.company', 'left')
                ->where_in('a.id_quotation', $penawaran_ids)
                ->get()
                ->result_array();
            foreach ($penawaran_company_rows as $row) {
                $penawaran_map['company_' . $row['id_quotation']] = $row;
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

            $nilai_pph = $item['pph23'];
            if ($nilai_pph == 0) {
                if ($item['tipe_invoice'] == '1') {
                    $nilai_pph = $item['total_nominal'] * 0.5 / 100;
                } else {
                    $nilai_pph = $item['total_nominal'] * 2 / 100;
                }
            }

            $nm_customer = $item['nm_customer'] ?? '-';
            $nm_project = $item['nm_project'] ?? '-';
            $print_keterangan = $item['print_keterangan'] ?? '-';
            $nm_company = '-';

            // Coba ambil company dari kons_tr_penawaran terlebih dahulu
            if (!empty($item['id_penawaran']) && isset($penawaran_map['company_' . $item['id_penawaran']])) {
                $nm_company = $penawaran_map['company_' . $item['id_penawaran']]['nm_company'] ?? '-';
            }

            // Jika tidak ada, coba ambil dari kons_tr_spk_penawaran
            if (($nm_company === '-' || empty($nm_company)) && !empty($item['id_spk_penawaran']) && isset($spk_penawaran_map[$item['id_spk_penawaran']])) {
                $nm_company = $spk_penawaran_map[$item['id_spk_penawaran']]['nm_company'] ?? '-';
            }
            
            // Jika tidak ada juga, fallback ke kons_tr_penawaran_non_konsultasi
            if (($nm_company === '-' || empty($nm_company)) && !empty($item['id_penawaran']) && isset($penawaran_map[$item['id_penawaran']])) {
                $nm_company = $penawaran_map[$item['id_penawaran']]['nm_company'] ?? '-';
            }

            if(empty($item['id_alokasi'])) {
                if(!empty($item['id_spk_penawaran'])) {
                    $id_plan = $item['id_detail_plan_tagih'];
                    $desc_payment = '-';
                    $spk_id = null;
                    if (isset($plan_tagih_map[$id_plan])) {
                        $desc_payment = $plan_tagih_map[$id_plan]['desc_payment'];
                        $spk_id = $plan_tagih_map[$id_plan]['id_spk_penawaran'];
                    }

                    if ($spk_id && isset($spk_penawaran_map[$spk_id])) {
                        $nm_customer = $spk_penawaran_map[$spk_id]['nm_customer'] ?? '-';
                        $nm_project = $spk_penawaran_map[$spk_id]['nm_paket'] ?? '-';
                    }
                    $print_keterangan = $desc_payment ?? '-';
                } else {
                    $id_pen = $item['id_penawaran'];
                    if ($id_pen && isset($penawaran_map[$id_pen])) {
                        $nm_customer = $penawaran_map[$id_pen]['nm_customer'] ?? '-';
                        $nm_project = $penawaran_map[$id_pen]['keterangan_penawaran'] ?? '-';
                        $print_keterangan = $penawaran_map[$id_pen]['keterangan_penawaran'] ?? '-';
                    }
                }
            }


            $hasil[] = [
                'no'                 => $no,
                'no_invoice'         => htmlspecialchars($item['no_invoice'], ENT_QUOTES, 'UTF-8'),
                'nm_customer'        => htmlspecialchars($nm_customer, ENT_QUOTES, 'UTF-8'),
                'nm_company'         => htmlspecialchars($nm_company, ENT_QUOTES, 'UTF-8'),
                'nm_project'         => htmlspecialchars($nm_project, ENT_QUOTES, 'UTF-8'),
                'keterangan_invoice' => htmlspecialchars($print_keterangan, ENT_QUOTES, 'UTF-8'),
                'nilai_pph'          => number_format($nilai_pph, 0, ',', '.'),
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
