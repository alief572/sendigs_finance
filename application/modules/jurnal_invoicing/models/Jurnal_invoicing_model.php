<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Jurnal_invoicing_model extends BF_Model
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
        if ($get_jurnal->jenis_transaksi == 'Invoicing') {
            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_jurnal->no_transaksi])->row();

            $id_company = $get_jurnal->id_company;

            $this->db->trans_begin();


            $Nomor_JV  = $this->Jurnal_invoicing_nomor_model->get_Nomor_Jurnal_Sales('101', $get_jurnal->tgl_jurnal, $get_jurnal->id_company);


            $Bln             = substr($get_jurnal->tgl_jurnal, 5, 2);
            $Thn             = substr($get_jurnal->tgl_jurnal, 0, 4);


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
            } else {
                $insert_jurnal_header = $this->db->insert(DBACC_SUST . '.javh', $dataJVhead);
            }
            if (!$insert_jurnal_header) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }

            $tgl_inv = $get_jurnal->tgl_jurnal;
            $keterangan = $get_jurnal->keterangan;
            $type = $get_jurnal->jenis_transaksi;
            $reff = $get_jurnal->no_transaksi;
            $no_req = $get_jurnal->no_transaksi;
            $jenis = 'JV';
            $jenis_jurnal = 'jurnalinvoicing';
            $no_coa = $get_jurnal->coa;
            $debet = $get_jurnal->debit;
            $kredit = $get_jurnal->kredit;

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
            // AND  jenis_jurnal = 'jurnalinvoicing' AND no_reff  = '" . $get_jurnal->no_transaksi . "' ";
            //     $this->db->query($jurnal_posting);

            if ($id_company == '1' || $id_company == '4') {
                $jurnal_posting = $this->db->update(DBACC_VUCA . '.jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $get_jurnal->no_transaksi]);
            } else {
                $jurnal_posting = $this->db->update(DBACC_SUST . '.jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $get_jurnal->no_transaksi]);
            }
            if (!$jurnal_posting) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }

            $update_jurnal_awal = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $get_jurnal->id]);
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



            // $jurnal_inv     = "UPDATE tr_invoice SET status_jurnal='CLS' WHERE no_invoice = '" . $get_jurnal->no_transaksi . "' ";
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
        }
    }

    public function update_sts_revisi_jurnal()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';

        $update_sts = $this->db->update('tr_jurnal', ['sts' => '9', 'alasan_revisi' => $post['alasan_revisi']], ['id' => $post['id']]);
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
        $post = $this->input->post();

        $draw = $post['draw'];
        $length = $post['length'];
        $start = $post['start'];
        $search = $post['search'];

        $this->db->select('a.id, a.tgl_jurnal, a.no_transaksi, a.coa, a.nm_coa, a.debit, a.kredit, b.nm_customer, b.nm_project, b.no_invoice, b.id_spk_penawaran, d.id as id_company, d.nm_company, e.name as nm_divisi');
        $this->db->from('tr_jurnal a');
        $this->db->join('tr_invoicing b', 'b.id = a.no_transaksi');
        $this->db->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = c.company');
        $this->db->join(DBHRIS . '.divisions e', 'e.id = c.id_divisi');
        $this->db->where('a.jenis_transaksi', 'Invoicing');
        $this->db->where_in('a.sts', ['', '0']);
        $this->db->group_start();
        $this->db->where('a.debit >', 0);
        $this->db->or_where('a.kredit >', 0);
        $this->db->group_end();

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.tgl_jurnal', $search['value'], 'both');
            $this->db->or_like('b.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_project', $search['value'], 'both');
            $this->db->or_like('b.no_invoice', $search['value'], 'both');
            $this->db->or_like('d.nm_company', $search['value'], 'both');
            $this->db->or_like('e.name', $search['value'], 'both');
            $this->db->or_like('a.coa', $search['value'], 'both');
            $this->db->or_like('a.nm_coa', $search['value'], 'both');
            $this->db->or_like('b.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.debit', $search['value'], 'both');
            $this->db->or_like('a.kredit', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id');

        $db_clone = clone $this->db;
        $count_filtered = $db_clone->count_all_results();

        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $no = (0 + $start);
        $hasil = [];

        foreach ($get_data as $row) {
            $no++;

            $nilai = 0;
            if ($row->debit > 0) {
                $nilai = $row->debit;
            }
            if ($row->kredit > 0) {
                $nilai = $row->kredit;
            }

            $btn_post_jurnal = '<button type="button" class="btn btn-sm btn-primary posting_jurnal" title="Posting Jurnal" data-id="' . $row->id . '"><i class="fa fa-arrow-up"></i></button>';
            $action = $btn_post_jurnal;

            $hasil[] = [
                'no' => $no,
                'tgl' => date('d F Y', strtotime($row->tgl_jurnal)),
                'klien' => $row->nm_customer,
                'no_invoice' => $row->no_invoice,
                'keterangan_tagihan' => $row->nm_project . ' - <span style="font-weight: bold;">' . $row->id_spk_penawaran . '</span>',
                'company' => $row->nm_company,
                'nm_divisi' => $row->nm_divisi,
                'coa' => $row->coa,
                'perkiraan' => $row->nm_coa,
                'uraian' => $row->no_invoice,
                'original' => number_format($nilai),
                'action' => $action
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filtered,
            'data' => $hasil
        ];

        echo json_encode($response);
    }
}
