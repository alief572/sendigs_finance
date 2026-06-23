<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Report_jurnal_invoicing_model extends BF_Model
{
    protected $viewPermission     = 'Jurnal_Invoicing.View';
    protected $addPermission      = 'Jurnal_Invoicing.Add';
    protected $managePermission = 'Jurnal_Invoicing.Manage';
    protected $deletePermission = 'Jurnal_Invoicing.Delete';

    protected $accounting;
    protected $accounting_vuca;
    protected $accounting_sustain;

    public function __construct()
    {
        $this->accounting = $this->load->database('accounting', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
    }

    public function save_posting_jurnal()
    {
        $post        = $this->input->post();
        $session = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;

        $get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $post['id']])->row();
        $get_jurnal_detail = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_jurnal->no_transaksi, 'jenis_transaksi' => $get_jurnal->jenis_transaksi])->result();
        $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_jurnal->no_transaksi])->row();

        $Nomor_JV  = $this->Jurnal_invoicing_nomor_model->get_Nomor_Jurnal_Sales('101', $get_jurnal->tgl_jurnal, $get_jurnal->id_company);


        $Bln             = substr($get_jurnal->tgl_jurnal, 5, 2);
        $Thn             = substr($get_jurnal->tgl_jurnal, 0, 4);

        $id_company = $get_jurnal->id_company;

        $this->db->trans_begin();

        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $get_jurnal->tgl_jurnal,
            'jml'                => $get_invoicing->total_akhir_jurnal,
            'koreksi_no'        => '-',
            'kdcab'                => '101',
            'jenis'                => 'JV',
            'keterangan'         => $get_jurnal->keterangan,
            'bulan'                => $Bln,
            'tahun'                => $Thn,
            'user_id'            => $this->auth->user_id(),
            'memo'                => '',
            'tgl_jvkoreksi'        => $get_jurnal->tgl_jurnal,
            'ho_valid'            => ''
        );

        if ($id_company == '1' || $id_company == '4') {
            $insert_jurnal_header = $this->db->insert(DBACC_VUCA . '.javh', $dataJVhead);
            if (!$insert_jurnal_header) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }
        } else {
            $insert_jurnal_header = $this->db->insert(DBACC_SUST . '.javh', $dataJVhead);
            if (!$insert_jurnal_header) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }
        }
        if (!$insert_jurnal_header) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }



        foreach ($get_jurnal_detail as $item) {
            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item->no_transaksi])->row();

            $id_company = $item->id_company;



            $tgl_inv = $item->tgl_jurnal;
            $keterangan = $item->keterangan;
            $type = $item->jenis_transaksi;
            $reff = $item->no_transaksi;
            $no_req = $item->no_transaksi;
            $jenis = 'JV';
            $jenis_jurnal = 'jurnalinvoicing';
            $no_coa = $item->coa;
            $debet = $item->debit;
            $kredit = $item->kredit;

            $datadetail = [
                'tipe' => 'JV',
                'nomor' => $Nomor_JV,
                'tanggal' => $tgl_inv,
                'no_perkiraan' => $no_coa,
                'keterangan' => $keterangan,
                'no_reff' => $reff,
                'debet' => $debet,
                'kredit' => $kredit
            ];

            if ($id_company == '1' || $id_company == '4') {
                $insert_jurnal_detail = $this->db->insert(DBACC_VUCA . '.jurnal', $datadetail);
            } else {
                $insert_jurnal_detail = $this->db->insert(DBACC_SUST . '.jurnal', $datadetail);
            }
            if (!$insert_jurnal_detail) {
                $this->db->trans_rollback();

                print($this->db->last_query());
                exit;
            }

            //     $jurnal_posting     = "UPDATE jurnal SET stspos=1 WHERE tipe = 'JV'
            // AND  jenis_jurnal = 'jurnalinvoicing' AND no_reff  = '" . $item->no_transaksi . "' ";
            //     $this->db->query($jurnal_posting);

            if ($id_company == '1' || $id_company == '4') {
                $jurnal_posting = $this->db->update(DBACC_VUCA . '.jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item->no_transaksi]);
            } else {
                $jurnal_posting = $this->db->update(DBACC_SUST . '.jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item->no_transaksi]);
            }
            if (!$jurnal_posting) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }

            $update_jurnal_awal = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item->id]);
            if (!$update_jurnal_awal) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }


            if ($id_company == '1' || $id_company == '4') {
                $Qry_Update_Cabang_acc     = $this->db->query("UPDATE " . DBACC_VUCA . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");
            } else {
                $Qry_Update_Cabang_acc     = $this->db->query("UPDATE " . DBACC_SUST . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");
            }
            // $this->db->query($Qry_Update_Cabang_acc);

            if (!$Qry_Update_Cabang_acc) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }



            // $jurnal_inv     = "UPDATE tr_invoice SET status_jurnal='CLS' WHERE no_invoice = '" . $item->no_transaksi . "' ";
            // $this->db->query($jurnal_inv);

            $id_cust   = $get_invoicing->id_customer;
            $nama   = $get_invoicing->nm_customer;
            $No_Inv  = $get_invoicing->id;


            $datapiutang = array(
                'tipe'            => 'JV',
                'nomor'            => $Nomor_JV,
                'tanggal'        => $tgl_inv,
                'no_perkiraan'  => '1104-01-01',
                'keterangan'    => $keterangan,
                'no_reff'       => $No_Inv,
                'debet'         => $get_invoicing->total_akhir_jurnal,
                'kredit'         =>  0,
                'id_supplier'     => $id_cust,
                'nama_supplier'   => $nama,
            );
            $insert_kartu_piutang = $this->db->insert('tr_kartu_piutang', $datapiutang);
            if (!$insert_kartu_piutang) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
        // if ($item->jenis_transaksi == 'Invoicing') {
        // }
    }

    public function update_sts_revisi_jurnal()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';

        $get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $post['id']])->row();

        $arr_update = [
            'sts' => '9',
            'alasan_revisi' => $post['alasan_revisi'],
        ];

        $where_update = [
            'no_transaksi' => $get_jurnal->no_transaksi,
            'jenis_transaksi' => $get_jurnal->jenis_transaksi
        ];

        $update_sts = $this->db->update('tr_jurnal', $arr_update, $where_update);
        if (!$update_sts) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error()['message'];
        }

        if ($this->db->trans_status() === false || $valid == 0) {
            if ($valid !== 0) {
                $this->db->trans_rollback();

                $valid = 0;
                $msg = 'Please try again later !';
            }
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'Status data berhasil di update !';
        }

        $response = [
            'status' => $valid,
            'msg' => $msg
        ];

        echo json_encode($response);
    }

    public function get_data_jurnal_invoicing()
    {
        $post   = $this->input->post();
        $draw   = isset($post['draw']) ? $post['draw'] : 0;
        $length = isset($post['length']) ? $post['length'] : 10;
        $start  = isset($post['start']) ? $post['start'] : 0;
        $search = isset($post['search']) ? $post['search']['value'] : '';

        $filter = [
            'tgl_from' => isset($post['tgl_from']) ? $post['tgl_from'] : '',
            'tgl_to'   => isset($post['tgl_to']) ? $post['tgl_to'] : '',
            'client'   => isset($post['client']) ? $post['client'] : '',
            'company'  => isset($post['company']) ? $post['company'] : '',
            'divisi'   => isset($post['divisi']) ? $post['divisi'] : '',
        ];

        // 1. Hitung Total Record (Tanpa Filter Search)
        $this->_base_query_report($filter);
        $this->db->group_by('a.no_transaksi');
        $sub_query_all = $this->db->get_compiled_select();
        $count_all_res = $this->db->query("SELECT COUNT(*) as total FROM ($sub_query_all) as temp")->row();
        $count_all     = $count_all_res ? (int)$count_all_res->total : 0;

        // 2. Hitung Filtered Record (Dengan Filter Search)
        $this->_base_query_report($filter);
        if (!empty($search)) {
            $this->_apply_search_report($search);
        }
        $this->db->group_by('a.no_transaksi');
        $sub_query_filtered = $this->db->get_compiled_select();
        $count_filter_res   = $this->db->query("SELECT COUNT(*) as total FROM ($sub_query_filtered) as temp")->row();
        $count_filtered     = $count_filter_res ? (int)$count_filter_res->total : 0;

        // 3. Ambil Data Aktual
        $this->_base_query_report($filter);
        if (!empty($search)) {
            $this->_apply_search_report($search);
        }
        $this->db->group_by('a.no_transaksi');
        $this->db->order_by('a.tgl_jurnal', 'DESC');
        $this->db->limit($length, $start);
        $rows = $this->db->get()->result();

        // 4. Build Response untuk Datatables
        $hasil = [];
        $no    = (int)$start;

        foreach ($rows as $row) {
            $no++;
            $nilai = ($row->debit > 0) ? $row->debit : $row->kredit;

            $btn_view_jurnal = '<button type="button" class="btn btn-sm btn-info view_jurnal" title="View Jurnal" data-no_transaksi="' . $row->no_transaksi . '" data-jenis_transaksi="' . $row->jenis_transaksi . '"><i class="fa fa-eye"></i></button>';

            $action = $btn_view_jurnal;

            $keterangan = $row->keterangan_penawaran;
            if (empty($keterangan)) {
                $get_keterangan_spk = $this->db->select('a.nm_project, b.nm_paket as keterangan')
                    ->from(DBCNL . '.kons_tr_spk_penawaran a')
                    ->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left')
                    ->where('a.id_spk_penawaran', $row->id_spk_penawaran)
                    ->get()
                    ->row();

                $keterangan = isset($get_keterangan_spk->nm_project) ? $get_keterangan_spk->nm_project : '';
            }

            $hasil[] = [
                'no'                 => $no,
                'tgl'                => date('d F Y', strtotime($row->tgl_jurnal)),
                'klien'              => $row->nm_customer,
                'no_invoice'         => $row->no_invoice,
                'keterangan_tagihan' => (!empty($row->non_kons) && $row->non_kons == '1')
                    ? $row->keterangan_penawaran . ' - <span style="font-weight:bold;">' . $row->id_penawaran_non_kons . '</span>'
                    : $keterangan . ' - <span style="font-weight:bold;">' . $row->id_spk_penawaran . '</span>',
                'company'            => $row->nm_company,
                'nm_divisi'          => $row->nm_divisi,
                'coa'                => $row->coa,
                'perkiraan'          => $row->nm_coa,
                'uraian'             => $row->no_invoice,
                'original'           => number_format($nilai),
                'action'             => $action,
            ];
        }

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => $count_all,
            'recordsFiltered' => $count_filtered,
            'data'            => $hasil,
        ]);
    }

    public function get_jurnal_invoicing($tgl_from = null, $tgl_to = null, $client = null, $company = null, $divisi = null)
    {
        $filter = [
            'tgl_from' => $tgl_from,
            'tgl_to'   => $tgl_to,
            'client'   => $client,
            'company'  => $company,
            'divisi'   => $divisi,
        ];

        $this->_base_query_report($filter);
        $this->db->group_by('a.no_transaksi');

        $get_data = $this->db->get()->result();

        return $get_data;
    }

    /**
     * Base Query - sama dengan jurnal_invoicing tapi filter sts = '1' (sudah posting)
     */
    private function _base_query_report($filter)
    {
        $select_fields = 'a.no_transaksi, a.id, a.tgl_jurnal, a.coa, a.nm_coa, a.debit, a.kredit, a.jenis_transaksi, b.nm_customer, b.nm_project, b.no_invoice, b.id_spk_penawaran, b.non_kons, e.id_penawaran as id_penawaran_non_kons, e.keterangan_penawaran, COALESCE(COALESCE(d.id, j.id), f.id) as id_company, COALESCE(COALESCE(d.nm_company, j.nm_company), f.nm_company) as nm_company, COALESCE(c.id_divisi, e.id_divisi) as id_divisi, COALESCE(g.name, h.name) as nm_divisi';

        $this->db->select($select_fields, FALSE)
            ->from('tr_jurnal a')
            ->join('tr_invoicing b', 'b.id = a.no_transaksi', 'left')
            ->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_spk_penawaran i', 'i.id_spk_penawaran = b.id_spk_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left')
            ->join(DBCNL . '.kons_tr_penawaran_non_konsultasi e', 'e.id_penawaran = b.id_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_company f', 'f.id = e.id_company', 'left')
            ->join(DBCNL . '.kons_tr_company j', 'j.id = i.id_company', 'left')
            ->join('hris_divisions g', 'g.id = c.id_divisi', 'left')
            ->join(DBHRIS . '.departments h', 'h.id = e.id_divisi', 'left')
            ->where('a.jenis_transaksi', 'Invoicing')
            ->where('b.no_invoice <>', '')
            ->where('a.sts', '1')
            ->where('(a.debit > 0 OR a.kredit > 0)')
            ->group_start()
            ->where('d.nm_company IS NOT NULL')
            ->or_where('f.nm_company IS NOT NULL')
            ->or_where('j.nm_company IS NOT NULL')
            ->group_end();

        // Apply filters
        if (!empty($filter['tgl_from'])) {
            $this->db->where('a.tgl_jurnal >=', $filter['tgl_from']);
        }
        if (!empty($filter['tgl_to'])) {
            $this->db->where('a.tgl_jurnal <=', $filter['tgl_to']);
        }
        if (!empty($filter['client'])) {
            $this->db->where('b.id_customer', $filter['client']);
        }
        if (!empty($filter['company'])) {
            $this->db->group_start();
            $this->db->where('d.id', $filter['company']);
            $this->db->or_where('f.id', $filter['company']);
            $this->db->or_where('j.id', $filter['company']);
            $this->db->group_end();
        }
        if (!empty($filter['divisi'])) {
            $this->db->group_start();
            $this->db->where('g.id', $filter['divisi']);
            $this->db->or_where('h.id', $filter['divisi']);
            $this->db->group_end();
        }
    }

    /**
     * Helper untuk Apply Search
     */
    private function _apply_search_report($search_value)
    {
        $search_terms = [
            'a.tgl_jurnal',
            'b.nm_customer',
            'b.nm_project',
            'b.no_invoice',
            'a.coa',
            'a.nm_coa',
            'b.id_spk_penawaran',
            'a.debit',
            'a.kredit'
        ];

        $this->db->group_start();
        foreach ($search_terms as $key => $field) {
            if ($key === 0) {
                $this->db->like($field, $search_value, 'both');
            } else {
                $this->db->or_like($field, $search_value, 'both');
            }
        }
        $this->db->group_end();
    }
}
