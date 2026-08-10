<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Report_jurnal_penerimaan_model extends BF_Model
{
    protected $viewPermission     = 'Report_Jurnal_Penerimaan.View';
    protected $addPermission      = 'Report_Jurnal_Penerimaan.Add';
    protected $managePermission = 'Report_Jurnal_Penerimaan.Manage';
    protected $deletePermission = 'Report_Jurnal_Penerimaan.Delete';

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

        // Lookup invoice melalui tr_penerimaan_piutang_detail berdasarkan no_surat (no_transaksi)
        $get_penerimaan_detail = $this->db->get_where('tr_penerimaan_piutang_detail', ['id_header' => $get_jurnal->no_transaksi])->row();
        $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_penerimaan_detail->id_inv])->row();

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
            // Lookup invoice melalui tr_penerimaan_piutang_detail berdasarkan no_surat (no_transaksi)
            $get_penerimaan_detail_item = $this->db->get_where('tr_penerimaan_piutang_detail', ['id_header' => $item->no_transaksi])->row();
            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_penerimaan_detail_item->id_inv])->row();

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
        // if ($item->jenis_transaksi == 'Penerimaan Piutang') {
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

    public function get_data_jurnal_penerimaan()
    {
        $post = $this->input->post();

        $draw = $post['draw'];
        $length = $post['length'];
        $start = $post['start'];
        $search = isset($post['search']['value']) ? $post['search']['value'] : '';

        $select_fields = 'a.no_transaksi, a.id, a.tgl_jurnal, a.coa, a.nm_coa, a.debit, a.kredit, a.no_transaksi, a.jenis_transaksi, b.nm_customer, b.nm_project, ppd1.all_invoices as no_invoice, b.id_spk_penawaran, b.non_kons, e.id_penawaran as id_penawaran_non_kons, e.keterangan_penawaran, COALESCE(COALESCE(d.id, j.id), f.id) as id_company, COALESCE(COALESCE(d.nm_company, j.nm_company), f.nm_company) as nm_company, COALESCE(c.id_divisi, e.id_divisi) as id_divisi, COALESCE(g.name, h.name) as nm_divisi, SUM(a.debit) as total_debit';

        // Query untuk menghitung count_all (total data tanpa filter pencarian)
        $db_clone = clone $this->db;
        $db_clone->select('a.id');
        $db_clone->from('tr_jurnal a');
        $db_clone->join('(SELECT ppd.id_header, MIN(ppd.id_inv) as id_inv, GROUP_CONCAT(b2.no_invoice SEPARATOR ", ") as all_invoices FROM tr_penerimaan_piutang_detail ppd LEFT JOIN tr_invoicing b2 ON b2.id = ppd.id_inv GROUP BY ppd.id_header) ppd1', 'ppd1.id_header = a.no_transaksi', 'left', FALSE);
        $db_clone->join('tr_invoicing b', 'b.id = ppd1.id_inv', 'left');
        $db_clone->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left');
        $db_clone->join(DBCNL . '.kons_tr_spk_penawaran i', 'i.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $db_clone->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left');
        $db_clone->join(DBCNL . '.kons_tr_penawaran_non_konsultasi e', 'e.id_penawaran = b.id_penawaran', 'left');
        $db_clone->join(DBCNL . '.kons_tr_company f', 'f.id = e.id_company', 'left');
        $db_clone->join(DBCNL . '.kons_tr_company j', 'j.id = i.id_company', 'left');
        $db_clone->join(DBHRIS . '.divisions g', 'g.id = c.id_divisi', 'left');
        $db_clone->join(DBHRIS . '.departments h', 'h.id = e.id_divisi', 'left');
        $db_clone->where('a.jenis_transaksi', 'Penerimaan Piutang');
        $db_clone->where('a.sts', '1');
        if (isset($post['tgl_from']) && !empty($post['tgl_from'])) {
            $db_clone->where('a.tgl_jurnal >=', $post['tgl_from']);
        }
        if (isset($post['tgl_to']) && !empty($post['tgl_to'])) {
            $db_clone->where('a.tgl_jurnal <=', $post['tgl_to']);
        }
        if (isset($post['client']) && !empty($post['client'])) {
            $db_clone->where('b.id_customer', $post['client']);
        }
        if (isset($post['company']) && !empty($post['company'])) {
            $db_clone->group_start();
            $db_clone->where('d.id', $post['company']);
            $db_clone->or_where('f.id', $post['company']);
            $db_clone->or_where('j.id', $post['company']);
            $db_clone->group_end();
        }
        if (isset($post['divisi']) && !empty($post['divisi'])) {
            $db_clone->group_start();
            $db_clone->where('g.id', $post['divisi']);
            $db_clone->or_where('h.id', $post['divisi']);
            $db_clone->group_end();
        }
        $db_clone->group_start();
        $db_clone->where('a.debit >', 0);
        $db_clone->or_where('a.kredit >', 0);
        $db_clone->group_end();

        $query = $db_clone->group_by('a.no_transaksi');
        $count_all = $query->count_all_results('', false);

        // Query untuk menghitung count_filter (total data yang sesuai dengan filter pencarian)
        $db_clone = clone $this->db;
        $db_clone->select('a.id');
        $db_clone->from('tr_jurnal a');
        $db_clone->join('(SELECT ppd.id_header, MIN(ppd.id_inv) as id_inv, GROUP_CONCAT(b2.no_invoice SEPARATOR ", ") as all_invoices FROM tr_penerimaan_piutang_detail ppd LEFT JOIN tr_invoicing b2 ON b2.id = ppd.id_inv GROUP BY ppd.id_header) ppd1', 'ppd1.id_header = a.no_transaksi', 'left', FALSE);
        $db_clone->join('tr_invoicing b', 'b.id = ppd1.id_inv', 'left');
        $db_clone->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left');
        $db_clone->join(DBCNL . '.kons_tr_spk_penawaran i', 'i.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $db_clone->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left');
        $db_clone->join(DBCNL . '.kons_tr_penawaran_non_konsultasi e', 'e.id_penawaran = b.id_penawaran', 'left');
        $db_clone->join(DBCNL . '.kons_tr_company f', 'f.id = e.id_company', 'left');
        $db_clone->join(DBCNL . '.kons_tr_company j', 'j.id = i.id_company', 'left');
        $db_clone->join(DBHRIS . '.divisions g', 'g.id = c.id_divisi', 'left');
        $db_clone->join(DBHRIS . '.departments h', 'h.id = e.id_divisi', 'left');
        $db_clone->where('a.jenis_transaksi', 'Penerimaan Piutang');
        $db_clone->where('a.sts', '1');
        if (isset($post['tgl_from']) && !empty($post['tgl_from'])) {
            $db_clone->where('a.tgl_jurnal >=', $post['tgl_from']);
        }
        if (isset($post['tgl_to']) && !empty($post['tgl_to'])) {
            $db_clone->where('a.tgl_jurnal <=', $post['tgl_to']);
        }
        if (isset($post['client']) && !empty($post['client'])) {
            $db_clone->where('b.id_customer', $post['client']);
        }
        if (isset($post['company']) && !empty($post['company'])) {
            $db_clone->group_start();
            $db_clone->where('d.id', $post['company']);
            $db_clone->or_where('f.id', $post['company']);
            $db_clone->or_where('j.id', $post['company']);
            $db_clone->group_end();
        }
        if (isset($post['divisi']) && !empty($post['divisi'])) {
            $db_clone->group_start();
            $db_clone->where('g.id', $post['divisi']);
            $db_clone->or_where('h.id', $post['divisi']);
            $db_clone->group_end();
        }
        $db_clone->group_start();
        $db_clone->where('a.debit >', 0);
        $db_clone->or_where('a.kredit >', 0);
        $db_clone->group_end();

        // Jika ada pencarian, tambahkan kondisi pencarian
        if (!empty($search)) {
            $db_clone->group_start();
            $db_clone->like('a.tgl_jurnal', $search, 'both');
            $db_clone->or_like('a.no_transaksi', $search, 'both');
            $db_clone->or_like('b.nm_customer', $search, 'both');
            $db_clone->or_like('b.nm_project', $search, 'both');
            $db_clone->or_like('ppd1.all_invoices', $search, 'both');
            $db_clone->or_like('d.nm_company', $search, 'both');
            $db_clone->or_like('f.nm_company', $search, 'both');
            $db_clone->or_like('j.nm_company', $search, 'both');
            $db_clone->or_like('g.name', $search, 'both');
            $db_clone->or_like('h.name', $search, 'both');
            $db_clone->or_like('a.coa', $search, 'both');
            $db_clone->or_like('a.nm_coa', $search, 'both');
            $db_clone->or_like('b.id_spk_penawaran', $search, 'both');
            $db_clone->or_like('e.id_penawaran', $search, 'both');
            $db_clone->or_like('a.debit', $search, 'both');
            $db_clone->or_like('a.kredit', $search, 'both');
            $db_clone->group_end();
        }

        $query = $db_clone->group_by('a.no_transaksi');
        $count_filter = $query->count_all_results('', false);

        // Query untuk mendapatkan data yang akan ditampilkan di tabel
        $this->db->select($select_fields, FALSE);
        $this->db->from('tr_jurnal a');
        $this->db->join('(SELECT ppd.id_header, MIN(ppd.id_inv) as id_inv, GROUP_CONCAT(b2.no_invoice SEPARATOR ", ") as all_invoices FROM tr_penerimaan_piutang_detail ppd LEFT JOIN tr_invoicing b2 ON b2.id = ppd.id_inv GROUP BY ppd.id_header) ppd1', 'ppd1.id_header = a.no_transaksi', 'left', FALSE);
        $this->db->join('tr_invoicing b', 'b.id = ppd1.id_inv', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran i', 'i.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran_non_konsultasi e', 'e.id_penawaran = b.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company f', 'f.id = e.id_company', 'left');
        $this->db->join(DBCNL . '.kons_tr_company j', 'j.id = i.id_company', 'left');
        $this->db->join(DBHRIS . '.divisions g', 'g.id = c.id_divisi', 'left');
        $this->db->join(DBHRIS . '.departments h', 'h.id = e.id_divisi', 'left');
        $this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
        $this->db->where('a.sts', '1');
        if (isset($post['tgl_from']) && !empty($post['tgl_from'])) {
            $this->db->where('a.tgl_jurnal >=', $post['tgl_from']);
        }
        if (isset($post['tgl_to']) && !empty($post['tgl_to'])) {
            $this->db->where('a.tgl_jurnal <=', $post['tgl_to']);
        }
        if (isset($post['client']) && !empty($post['client'])) {
            $this->db->where('b.id_customer', $post['client']);
        }
        if (isset($post['company']) && !empty($post['company'])) {
            $this->db->group_start();
            $this->db->where('d.id', $post['company']);
            $this->db->or_where('f.id', $post['company']);
            $this->db->or_where('j.id', $post['company']);
            $this->db->group_end();
        }
        if (isset($post['divisi']) && !empty($post['divisi'])) {
            $this->db->group_start();
            $this->db->where('g.id', $post['divisi']);
            $this->db->or_where('h.id', $post['divisi']);
            $this->db->group_end();
        }
        $this->db->group_start();
        $this->db->where('a.debit >', 0);
        $this->db->or_where('a.kredit >', 0);
        $this->db->group_end();

        // Jika ada pencarian, tambahkan kondisi pencarian
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.tgl_jurnal', $search, 'both');
            $this->db->or_like('a.no_transaksi', $search, 'both');
            $this->db->or_like('b.nm_customer', $search, 'both');
            $this->db->or_like('b.nm_project', $search, 'both');
            $this->db->or_like('ppd1.all_invoices', $search, 'both');
            $this->db->or_like('d.nm_company', $search, 'both');
            $this->db->or_like('f.nm_company', $search, 'both');
            $this->db->or_like('j.nm_company', $search, 'both');
            $this->db->or_like('g.name', $search, 'both');
            $this->db->or_like('h.name', $search, 'both');
            $this->db->or_like('a.coa', $search, 'both');
            $this->db->or_like('a.nm_coa', $search, 'both');
            $this->db->or_like('b.id_spk_penawaran', $search, 'both');
            $this->db->or_like('e.id_penawaran', $search, 'both');
            $this->db->or_like('a.debit', $search, 'both');
            $this->db->or_like('a.kredit', $search, 'both');
            $this->db->group_end();
        }

        // Limit data berdasarkan parameter
        $this->db->group_by('a.no_transaksi');
        $this->db->limit($length, $start);

        // Ambil data yang akan ditampilkan
        $get_data = $this->db->get()->result();

        $no = (0 + $start);
        $hasil = [];

        foreach ($get_data as $row) {
            $no++;

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

            if (empty($keterangan)) {
                $keterangan = $row->nm_project;
            }

            $keterangan_tagihan = (!empty($row->non_kons) && $row->non_kons == '1')
                ? ((!empty($row->keterangan_penawaran) ? $row->keterangan_penawaran : $row->nm_project) . ' - <span style="font-weight:bold;">' . $row->id_penawaran_non_kons . '</span>')
                : ($keterangan . ' - <span style="font-weight:bold;">' . $row->id_spk_penawaran . '</span>');

            $hasil[] = [
                'no' => $no,
                'tgl' => date('d F Y', strtotime($row->tgl_jurnal)),
                'klien' => $row->nm_customer,
                'no_invoice' => $row->no_invoice,
                'keterangan_tagihan' => $keterangan_tagihan,
                'company' => $row->nm_company,
                'nm_divisi' => $row->nm_divisi,
                'coa' => $row->coa,
                'perkiraan' => $row->nm_coa,
                'uraian' => $row->no_invoice,
                'original' => number_format($row->total_debit),
                'action' => $action
            ];
        }

        // Response JSON untuk DataTables
        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filter,
            'data' => $hasil
        ];

        echo json_encode($response);
    }

    public function get_jurnal_invoicing($tgl_from = null, $tgl_to = null, $client = null, $company = null, $divisi = null)
    {
        $select_fields = 'a.no_transaksi, a.id, a.tgl_jurnal, a.coa, a.nm_coa, a.debit, a.kredit, a.no_transaksi, a.jenis_transaksi, b.nm_customer, b.nm_project, ppd1.all_invoices as no_invoice, b.id_spk_penawaran, b.non_kons, e.id_penawaran as id_penawaran_non_kons, e.keterangan_penawaran, COALESCE(COALESCE(d.id, j.id), f.id) as id_company, COALESCE(COALESCE(d.nm_company, j.nm_company), f.nm_company) as nm_company, COALESCE(c.id_divisi, e.id_divisi) as id_divisi, COALESCE(g.name, h.name) as nm_divisi, SUM(a.debit) as total_debit';

        $this->db->select($select_fields, FALSE);
        $this->db->from('tr_jurnal a');
        $this->db->join('(SELECT ppd.id_header, MIN(ppd.id_inv) as id_inv, GROUP_CONCAT(b2.no_invoice SEPARATOR ", ") as all_invoices FROM tr_penerimaan_piutang_detail ppd LEFT JOIN tr_invoicing b2 ON b2.id = ppd.id_inv GROUP BY ppd.id_header) ppd1', 'ppd1.id_header = a.no_transaksi', 'left', FALSE);
        $this->db->join('tr_invoicing b', 'b.id = ppd1.id_inv', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran i', 'i.id_spk_penawaran = b.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = c.company', 'left');
        $this->db->join(DBCNL . '.kons_tr_penawaran_non_konsultasi e', 'e.id_penawaran = b.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company f', 'f.id = e.id_company', 'left');
        $this->db->join(DBCNL . '.kons_tr_company j', 'j.id = i.id_company', 'left');
        $this->db->join(DBHRIS . '.divisions g', 'g.id = c.id_divisi', 'left');
        $this->db->join(DBHRIS . '.departments h', 'h.id = e.id_divisi', 'left');
        $this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
        $this->db->where('a.sts', '1');
        if (isset($tgl_from) && !empty($tgl_from)) {
            $this->db->where('a.tgl_jurnal >=', $tgl_from);
        }
        if (isset($tgl_to) && !empty($tgl_to)) {
            $this->db->where('a.tgl_jurnal <=', $tgl_to);
        }
        if (isset($client) && !empty($client)) {
            $this->db->where('b.id_customer', $client);
        }
        if (isset($company) && !empty($company)) {
            $this->db->group_start();
            $this->db->where('d.id', $company);
            $this->db->or_where('f.id', $company);
            $this->db->or_where('j.id', $company);
            $this->db->group_end();
        }
        if (isset($divisi) && !empty($divisi)) {
            $this->db->group_start();
            $this->db->where('g.id', $divisi);
            $this->db->or_where('h.id', $divisi);
            $this->db->group_end();
        }
        $this->db->group_start();
        $this->db->where('a.debit >', 0);
        $this->db->or_where('a.kredit >', 0);
        $this->db->group_end();

        $this->db->group_by('a.no_transaksi');

        $get_data = $this->db->get()->result();

        return $get_data;
    }
}
