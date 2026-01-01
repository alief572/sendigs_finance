<?php
class Misc extends Admin_Controller
{

    protected $accounting;
    protected $consultant;
    protected $accounting_vuca;
    protected $accounting_sustain;
    protected $accounting_stm;

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Jurnal_invoicing/Jurnal_invoicing_model',
            'Jurnal_invoicing/Jurnal_invoicing_nomor_model'
        ));

        $this->accounting = $this->load->database('accounting', true);
        $this->consultant = $this->load->database('consultant', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
        $this->accounting_stm = $this->load->database('accounting_stm', true);
    }

    function get_Nomor_Jurnal_Sales($Cabang = '', $Tgl_Inv = '', $comp = '')
    {
        // $db2 = $this->load->database('accounting', TRUE);
        $nocab            = 'A';
        $bulan_Proses    = date('Y', strtotime($Tgl_Inv));
        $Urut            = 1;
        if ($comp == '4' || $comp == '5') {
            $Query_Cab        = "SELECT subcab,nomorJC FROM pastibisa_tb_cabang WHERE nocab='" . $Cabang . "'";
            $Pros_Cab        = $this->accounting_vuca->query($Query_Cab);
        } else if ($comp == '3') {
            $Query_Cab        = "SELECT subcab,nomorJC FROM pastibisa_tb_cabang WHERE nocab='" . $Cabang . "'";
            $Pros_Cab        = $this->accounting_sustain->query($Query_Cab);
        } else {
            $Query_Cab        = "SELECT subcab,nomorJC FROM pastibisa_tb_cabang WHERE nocab='" . $Cabang . "'";
            $Pros_Cab        = $this->accounting_stm->query($Query_Cab);
        }

        $det_Cab        = $Pros_Cab->result_array();
        if ($det_Cab) {
            $nocab        = $det_Cab[0]['subcab'];
            $Urut        = intval($det_Cab[0]['nomorJC']) + 1;
        }
        $Format            = $Cabang . '-' . $nocab . 'JV' . date('y', strtotime($Tgl_Inv));

        $Nomor_JS        = $Format . str_pad($Urut, 5, "0", STR_PAD_LEFT);

        return $Nomor_JS;
    }

    function get_Nomor_Jurnal_BUM($Cabang = '', $Tgl_Inv = '', $comp = '')
    {
        $nocab            = 'A';
        $bulan_Proses    = date('Y', strtotime($Tgl_Inv));
        $Urut            = 1;

        if ($comp == '1' || $comp == '6') {
            $Query_Cab        = "SELECT subcab,nobum FROM " . DBACC_STM . ".pastibisa_tb_cabang WHERE nocab='" . $Cabang . "'";
            $Pros_Cab        = $this->db->query($Query_Cab);
        } else if ($comp == '4' || $comp == '5') {
            $Query_Cab        = "SELECT subcab,nobum FROM " . DBACC_VUCA . ".pastibisa_tb_cabang WHERE nocab='" . $Cabang . "'";
            $Pros_Cab        = $this->db->query($Query_Cab);
        } else {
            $Query_Cab        = "SELECT subcab,nobum FROM " . DBACC_SUSTAIN . ".pastibisa_tb_cabang WHERE nocab='" . $Cabang . "'";
            $Pros_Cab        = $this->db->query($Query_Cab);
        }

        $det_Cab        = $Pros_Cab->result_array();
        if ($det_Cab) {
            $nocab        = $det_Cab[0]['subcab'];
            $Urut        = intval($det_Cab[0]['nobum']) + 1;
        }
        $Format            = $Cabang . 'BM' . date('y', strtotime($Tgl_Inv));

        $Nomor_BUM        = $Format . str_pad($Urut, 5, "0", STR_PAD_LEFT);

        return $Nomor_BUM;
    }

    public function buat_ulang_jurnal_invoicing()
    {
        $this->db->trans_begin();

        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        // $this->db->where_in('a.id', $arr_id_invoicing);
        $get_invoicing = $this->db->get()->result_array();

        $arr_rejurnal = [];

        foreach ($get_invoicing as $item) {

            $this->consultant->select('a.id, a.nm_company');
            $this->consultant->from('kons_tr_company a');
            $this->consultant->join('kons_tr_penawaran b', 'b.company = a.id');
            $this->consultant->where('b.id_quotation', $item['id_penawaran']);
            $get_company = $this->consultant->get()->row();

            $id_company = (!empty($get_company->id)) ? $get_company->id : '';
            $nm_company = (!empty($get_company->nm_company)) ? $get_company->nm_company : '';

            $arr_coa = ['1102-01-01', '1106-01-02', '2104-01-07', '4101-01-01'];

            $this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
            $this->accounting->from('coa_master a');
            $this->accounting->where_in('a.no_perkiraan', $arr_coa);
            $get_coa = $this->accounting->get()->result_array();

            foreach ($get_coa as $item_coa) {
                $debit = 0;
                $kredit = 0;
                if ($item_coa['no_coa'] == '1102-01-01') {
                    $debit = $item['total_akhir_jurnal'];
                }
                if ($item_coa['no_coa'] == '1106-01-02') {
                    $debit = $item['pph_jurnal'];
                }
                if ($item_coa['no_coa'] == '2104-01-07') {
                    $kredit = $item['ppn_jurnal'];
                }
                if ($item_coa['no_coa'] == '4101-01-01') {
                    $kredit = $item['total_nominal_jurnal'];
                }

                $arr_rejurnal[] = [
                    'tgl_jurnal' => $item['tanggal_invoice'],
                    'coa' => $item_coa['no_coa'],
                    'id_company' => $id_company,
                    'nm_company' => $nm_company,
                    'nm_coa' => $item_coa['nm_coa'],
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'keterangan' => $item['no_invoice'] . ' - ' . $item['nm_customer'],
                    'sts' => '0',
                    'no_transaksi' => $item['id'],
                    'jenis_transaksi' => 'Invoicing',
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        if (!empty($arr_rejurnal)) {
            // $this->db->trans_begin();

            try {
                $insert_rejurnal = $this->db->insert_batch('tr_jurnal', $arr_rejurnal);

                // print_r($this->db->last_query());
                // exit;

                $this->db->trans_commit();

                echo 'Berhasil !';
            } catch (Exception $e) {
                $this->db->trans_rollback();

                echo $e->getMessage();
            }
        }
    }

    public function rejurnal_penerimaan_piutang()
    {
        $arr_rejurnal = [];

        $get_all_penerimaan = $this->db->get('tr_penerimaan_piutang')->result_array();
        foreach ($get_all_penerimaan as $item) {
            $get_penerimaan_detail = $this->db->get_where('tr_penerimaan_piutang_detail', ['id_header' => $item['no_surat']])->result_array();

            foreach ($get_penerimaan_detail as $item_detail) {
                $get_penerimaan_header = $this->db->get_where('tr_penerimaan_piutang', ['no_surat' => $item_detail['id_header']])->row_array();
                $get_alokasi = $this->db->get_where('tr_alokasi_detail', ['id' => $item_detail['id_alokasi']])->row_array();
                $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_detail['id_inv']])->row_array();

                $this->consultant->select('a.id, a.nm_company');
                $this->consultant->from('kons_tr_company a');
                $this->consultant->join('kons_tr_penawaran b', 'b.company = a.id');
                $this->consultant->where('b.id_quotation', $get_invoicing['id_penawaran']);
                $get_company = $this->consultant->get()->row_array();

                $coa_bank = '';

                if (!empty($get_alokasi)) {
                    $get_bank = $this->db->get_where('ms_bank', ['id' => $get_alokasi['tipe_bank']])->row_array();

                    $coa_bank = (!empty($get_bank['coa_bank'])) ? $get_bank['coa_bank'] : '';
                }

                $arr_coa = ['1102-01-01', '1106-01-02', '7201-01-04'];
                if (!empty($coa_bank)) {
                    array_push($arr_coa, $coa_bank);
                }

                $this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
                $this->accounting->from('coa_master a');
                $this->accounting->where_in('a.no_perkiraan', $arr_coa);
                $get_coa = $this->accounting->get()->result_array();

                foreach ($get_coa as $item_coa) {
                    $debit = 0;
                    $kredit = 0;
                    $keterangan = '';

                    if ($item_coa['no_coa'] == $coa_bank) {
                        $debit = ($get_alokasi['nominal_debit']) ? $get_alokasi['nominal_debit'] : $get_alokasi['nominal_kredit'];
                        $keterangan = (!empty($get_alokasi['keterangan'])) ? $get_alokasi['keterangan'] : '';
                    }
                    if ($item_coa['no_coa'] == '1102-01-01') {
                        $kredit = $get_invoicing['total_akhir_jurnal'];
                        if ($get_penerimaan_header['pph23_dipotong'] !== 'Y') {
                            $kredit = $get_invoicing['tagihan_ppn_jurnal'];
                        }
                        $keterangan = $get_invoicing['no_invoice'] . ' - ' . $get_invoicing['nm_customer'];
                    }
                    if ($item_coa['no_coa'] == '1106-01-02') {
                        if ($get_penerimaan_header['pph23_dipotong'] !== 'Y') {
                            $kredit = $get_invoicing['pph_jurnal'];
                        }

                        $keterangan = $get_invoicing['no_invoice'] . ' - ' . $get_invoicing['nm_customer'];
                    }
                    if ($item_coa['no_coa'] == '7201-01-04') {
                        $debit = $item_detail['biaya_admin'];

                        $keterangan = $get_invoicing['no_invoice'] . ' - ' . $get_invoicing['nm_customer'];
                    }

                    $id_company = (!empty($get_company['id'])) ? $get_company['id'] : '';
                    $nm_company = (!empty($get_company['nm_company'])) ? $get_company['nm_company'] : '';

                    $arr_rejurnal[] = [
                        'tgl_jurnal' => date('Y-m-d', strtotime($get_penerimaan_header['created_date'])),
                        'coa' => $item_coa['no_coa'],
                        'id_company' => $id_company,
                        'nm_company' => $nm_company,
                        'nm_coa' => $item_coa['nm_coa'],
                        'debit' => $debit,
                        'kredit' => $kredit,
                        'keterangan' => $keterangan,
                        'sts' => '0',
                        'no_transaksi' => $get_invoicing['id'],
                        'jenis_transaksi' => 'Penerimaan Piutang',
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        if (!empty($arr_rejurnal)) {
            $this->db->trans_begin();

            try {
                $this->db->insert_batch('tr_jurnal', $arr_rejurnal);

                $this->db->trans_commit();

                echo 'Berhasil !';
            } catch (Exception $e) {
                $this->db->trans_rollback();

                echo $e->getMessage();
            }
        }
    }

    public function check_stock()
    {
        $this->db->select('a.*');
        $this->db->from('warehouse_stock a');
        $this->db->where('a.id_gudang', '1');
        $get_stock = $this->db->get()->result_array();

        $no = 0;
        foreach ($get_stock as $item_stock) {
            $this->db->select('a.qty_stock_akhir');
            $this->db->from('warehouse_history a');
            $this->db->where('a.id_material', $item_stock['id_material']);
            $this->db->where('a.id_gudang', '1');
            $this->db->order_by('a.update_date', 'desc');
            $this->db->limit(1);
            $get_history = $this->db->get()->row_array();

            $qty_stock = floatval($item_stock['qty_stock']);
            $qty_history = (!empty($get_history['qty_stock_akhir'])) ? floatval($get_history['qty_stock_akhir']) : '';

            if ($qty_stock !== $qty_history) {
                $no++;

                echo $no . '. ' . $item_stock['nm_material'] . ' - ' . $qty_stock . ' - ' . $qty_history . ' <br>';
            }
        }
    }


    public function check_posted_jurnal_invoicing()
    {
        $this->db->trans_begin();
        try {
            $this->db->select('a.*');
            $this->db->from('tr_jurnal a');
            $this->db->where('a.jenis_transaksi', 'Invoicing');
            $this->db->where('a.sts', '1');
            $this->db->group_start();
            $this->db->where('a.debit >', 0);
            $this->db->or_where('a.kredit >', 0);
            $this->db->group_end();
            $this->db->group_by('a.no_transaksi, a.jenis_transaksi');
            $get_data_jurnal = $this->db->get()->result();

            foreach ($get_data_jurnal as $item_jurnal) :
                if (!empty($item_jurnal->id_company)) {
                    if ($item_jurnal->id_company == '4' || $item_jurnal->id_company == '5') {
                        $this->accounting_vuca->select('a.id');
                        $this->accounting_vuca->from('jurnal a');
                        $this->accounting_vuca->where('a.no_reff', $item_jurnal->no_transaksi);
                        $this->accounting->where('a.tipe', 'JV');
                        $get_jurnal_tras = $this->accounting_vuca->get()->result();

                        if (count($get_jurnal_tras) < 1) {

                            echo $item_jurnal->no_transaksi . '<br>';

                            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_jurnal->no_transaksi])->row();

                            $Bln             = substr($item_jurnal->tgl_jurnal, 5, 2);
                            $Thn             = substr($item_jurnal->tgl_jurnal, 0, 4);

                            $Nomor_JV  = $this->get_Nomor_Jurnal_Sales('101', $item_jurnal->tgl_jurnal, $item_jurnal->id_company);

                            $dataJVhead = array(
                                'nomor'             => $Nomor_JV,
                                'tgl'                 => $item_jurnal->tgl_jurnal,
                                'jml'                => $get_invoicing->total_akhir_jurnal,
                                'koreksi_no'        => '-',
                                'kdcab'                => '101',
                                'jenis'                => 'JV',
                                'keterangan'         => $get_invoicing->no_invoice . ' - ' . $get_invoicing->nm_customer,
                                'bulan'                => $Bln,
                                'tahun'                => $Thn,
                                'user_id'            => $this->auth->user_id(),
                                'memo'                => '',
                                'tgl_jvkoreksi'        => $item_jurnal->tgl_jurnal,
                                'ho_valid'            => ''
                            );

                            $insert_jurnal_header = $this->accounting_vuca->insert('javh', $dataJVhead);

                            $get_jurnal_detail = $this->db->get_where('tr_jurnal', ['no_transaksi' => $item_jurnal->no_transaksi, 'jenis_transaksi' => $item_jurnal->jenis_transaksi])->result();

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
                                $insert_jurnal_detail = $this->accounting_vuca->insert('jurnal', $datadetail);

                                $jurnal_posting = $this->accounting_vuca->update('jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item->no_transaksi]);

                                // $update_jurnal_awal = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item->id]);

                                $Qry_Update_Cabang_acc = $this->accounting_vuca->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");


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
                            }
                        }
                    }
                    if ($item_jurnal->id_company == '3') {
                        $this->accounting_sustain->select('a.id');
                        $this->accounting_sustain->from('jurnal a');
                        $this->accounting_sustain->where('a.no_reff', $item_jurnal->no_transaksi);
                        $this->accounting_sustain->where('a.tipe', 'JV');
                        $get_jurnal_tras = $this->accounting_sustain->get()->result();

                        if (count($get_jurnal_tras) < 1) {

                            echo $item_jurnal->no_transaksi . '<br>';

                            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_jurnal->no_transaksi])->row();

                            $Bln             = substr($item_jurnal->tgl_jurnal, 5, 2);
                            $Thn             = substr($item_jurnal->tgl_jurnal, 0, 4);

                            $Nomor_JV  = $this->get_Nomor_Jurnal_Sales('101', $item_jurnal->tgl_jurnal, $item_jurnal->id_company);

                            $dataJVhead = array(
                                'nomor'             => $Nomor_JV,
                                'tgl'                 => $item_jurnal->tgl_jurnal,
                                'jml'                => $get_invoicing->total_akhir_jurnal,
                                'koreksi_no'        => '-',
                                'kdcab'                => '101',
                                'jenis'                => 'JV',
                                'keterangan'         => $get_invoicing->no_invoice . ' - ' . $get_invoicing->nm_customer,
                                'bulan'                => $Bln,
                                'tahun'                => $Thn,
                                'user_id'            => $this->auth->user_id(),
                                'memo'                => '',
                                'tgl_jvkoreksi'        => $item_jurnal->tgl_jurnal,
                                'ho_valid'            => ''
                            );

                            $insert_jurnal_header = $this->accounting_sustain->insert('javh', $dataJVhead);

                            $get_jurnal_detail = $this->db->get_where('tr_jurnal', ['no_transaksi' => $item_jurnal->no_transaksi, 'jenis_transaksi' => $item_jurnal->jenis_transaksi])->result();

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
                                $insert_jurnal_detail = $this->accounting_sustain->insert('jurnal', $datadetail);

                                $jurnal_posting = $this->accounting_sustain->update('jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item->no_transaksi]);

                                // $update_jurnal_awal = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item->id]);

                                $Qry_Update_Cabang_acc = $this->accounting_sustain->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");


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
                            }
                        }
                    }
                    if ($item_jurnal->id_company == '1' || $item_jurnal->id_company == '6') {
                        $this->accounting_stm->select('a.id');
                        $this->accounting_stm->from('jurnal a');
                        $this->accounting_stm->where('a.no_reff', $item_jurnal->no_transaksi);
                        $this->accounting_stm->where('a.tipe', 'JV');
                        $get_jurnal_tras = $this->accounting_stm->get()->result();

                        if (count($get_jurnal_tras) < 1) {

                            echo $item_jurnal->no_transaksi . '<br>';

                            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_jurnal->no_transaksi])->row();

                            $Bln             = substr($item_jurnal->tgl_jurnal, 5, 2);
                            $Thn             = substr($item_jurnal->tgl_jurnal, 0, 4);

                            $Nomor_JV  = $this->get_Nomor_Jurnal_Sales('101', $item_jurnal->tgl_jurnal, $item_jurnal->id_company);

                            $dataJVhead = array(
                                'nomor'             => $Nomor_JV,
                                'tgl'                 => $item_jurnal->tgl_jurnal,
                                'jml'                => $get_invoicing->total_akhir_jurnal,
                                'koreksi_no'        => '-',
                                'kdcab'                => '101',
                                'jenis'                => 'JV',
                                'keterangan'         => $get_invoicing->no_invoice . ' - ' . $get_invoicing->nm_customer,
                                'bulan'                => $Bln,
                                'tahun'                => $Thn,
                                'user_id'            => $this->auth->user_id(),
                                'memo'                => '',
                                'tgl_jvkoreksi'        => $item_jurnal->tgl_jurnal,
                                'ho_valid'            => ''
                            );

                            $insert_jurnal_header = $this->accounting_stm->insert('javh', $dataJVhead);

                            $get_jurnal_detail = $this->db->get_where('tr_jurnal', ['no_transaksi' => $item_jurnal->no_transaksi, 'jenis_transaksi' => $item_jurnal->jenis_transaksi])->result();

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
                                $insert_jurnal_detail = $this->accounting_stm->insert('jurnal', $datadetail);

                                $jurnal_posting = $this->accounting_stm->update('jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item->no_transaksi]);

                                // $update_jurnal_awal = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item->id]);

                                $Qry_Update_Cabang_acc = $this->accounting_stm->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");


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
                            }
                        }
                    }
                }
            endforeach;
            $this->db->trans_commit();


            echo "Success !";
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
    }

    public function check_posted_jurnal_penerimaan()
    {
        $this->db->trans_begin();
        try {
            $this->db->select('a.*');
            $this->db->from('tr_jurnal a');
            $this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
            $this->db->where('a.sts', '1');
            $this->db->group_start();
            $this->db->where('a.debit >', 0);
            $this->db->or_where('a.kredit >', 0);
            $this->db->group_end();
            $this->db->group_by('a.no_transaksi');
            $get_data_jurnal = $this->db->get()->result();

            foreach ($get_data_jurnal as $item_jurnal) {
                if (!empty($item_jurnal->id_company)) {
                    $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_jurnal->no_transaksi])->row();

                    $Nomor_BUM = $this->get_Nomor_Jurnal_BUM('101', $item_jurnal->tgl_jurnal, $item_jurnal->id_company);

                    if ($item_jurnal->id_company == '4' || $item_jurnal->id_company == '5') {
                        $this->db->select('a.id');
                        $this->db->from(DBACC_VUCA . '.jurnal a');
                        $this->db->where('a.no_reff', $item_jurnal->no_transaksi);
                        $this->db->where('a.tipe', 'BUM');
                        $get_jurnal_tras = $this->db->get()->result();
                    }
                    if ($item_jurnal->id_company == '3') {
                        $this->accounting_sustain->select('a.id');
                        $this->accounting_sustain->from(DBACC_SUSTAIN . '.jurnal a');
                        $this->accounting_sustain->where('a.no_reff', $item_jurnal->no_transaksi);
                        $this->accounting_sustain->where('a.tipe', 'BUM');
                        $get_jurnal_tras = $this->accounting_sustain->get()->result();
                    }
                    if ($item_jurnal->id_company == '1' || $item_jurnal->id_company == '6') {
                        $this->accounting_stm->select('a.id');
                        $this->accounting_stm->from(DBACC_STM . '.jurnal a');
                        $this->accounting_stm->where('a.no_reff', $item_jurnal->no_transaksi);
                        $this->accounting_stm->where('a.tipe', 'BUM');
                        $get_jurnal_tras = $this->accounting_stm->get()->result();
                    }

                    if (isset($get_jurnal_tras) && count($get_jurnal_tras) < 1) {
                        echo $item_jurnal->no_transaksi . '<br>';

                        $Bln             = substr($item_jurnal->tgl_jurnal, 5, 2);
                        $Thn             = substr($item_jurnal->tgl_jurnal, 0, 4);

                        $nilai = (!empty($item_jurnal->debit) && $item_jurnal->debit > 0) ? $item_jurnal->debit : $item_jurnal->kredit;

                        $arr_jarh = [
                            'nomor' => $Nomor_BUM,
                            'tgl' => $item_jurnal->tgl_jurnal,
                            'jml' => $nilai,
                            'kdcab' => '101',
                            'jenis_reff' => 'BUM',
                            'no_reff' => $get_invoicing->id,
                            'customer' => $get_invoicing->nm_customer,
                            'terima_dari' => $this->auth->user_name(),
                            'jenis_ar' => 'BUM',
                            'note' => $item_jurnal->keterangan,
                            'user_id' => $this->auth->user_id(),
                            'tgl_invoice' => $get_invoicing->tanggal_invoice
                        ];
                        if ($item_jurnal->id_company == '1' || $item_jurnal->id_company == '6') {
                            $insert_jarh = $this->db->insert(DBACC_STM . '.jarh', $arr_jarh);
                        } else if ($item_jurnal->id_company == '4' || $item_jurnal->id_company == '5') {
                            $insert_jarh = $this->db->insert(DBACC_VUCA . '.jarh', $arr_jarh);
                        } else {
                            $insert_jarh = $this->db->insert(DBACC_SUSTAIN . '.jarh', $arr_jarh);
                        }

                        $get_jurnal_detail = $this->db->get_where('tr_jurnal', ['no_transaksi' => $item_jurnal->no_transaksi, 'jenis_transaksi' => $item_jurnal->jenis_transaksi])->result();

                        $arr_jurnal = [];
                        foreach ($get_jurnal_detail as $item_jurnal_detail) {
                            $arr_jurnal[] = [
                                'tipe' => 'BUM',
                                'nomor' => $Nomor_BUM,
                                'tanggal' => $item_jurnal_detail->tgl_jurnal,
                                'no_perkiraan' => $item_jurnal_detail->coa,
                                'keterangan' => $item_jurnal_detail->keterangan,
                                'no_reff' => $get_invoicing->id,
                                'debet' => $item_jurnal_detail->debit,
                                'kredit' => $item_jurnal_detail->kredit,
                                'id_perusahaan' => $item_jurnal_detail->id_company,
                                'nm_perusahaan' => $item_jurnal_detail->nm_company
                            ];
                        }

                        if ($item_jurnal->id_company == '1' || $item_jurnal->id_company == '6') {
                            $insert_jurnal = $this->db->insert_batch(DBACC_STM . '.jurnal', $arr_jurnal);
                        } else if ($item_jurnal->id_company == '4' || $item_jurnal->id_company == '5') {
                            $insert_jurnal = $this->db->insert_batch(DBACC_VUCA . '.jurnal', $arr_jurnal);
                        } else {
                            $insert_jurnal = $this->db->insert_batch(DBACC_SUSTAIN . '.jurnal', $arr_jurnal);
                        }

                        if ($item_jurnal->id_company == '1' || $item_jurnal->id_company == '6') {
                            $update_cabang_acc = $this->db->query('UPDATE ' . DBACC_STM . '.pastibisa_tb_cabang SET nobum = nobum+1 WHERE nocab = "101"');
                        } else if ($item_jurnal->id_company == '4' || $item_jurnal->id_company == '5') {
                            $update_cabang_acc = $this->db->query('UPDATE ' . DBACC_VUCA . '.pastibisa_tb_cabang SET nobum = nobum+1 WHERE nocab = "101"');
                        } else {
                            $update_cabang_acc = $this->db->query('UPDATE ' . DBACC_SUSTAIN . '.pastibisa_tb_cabang SET nobum = nobum+1 WHERE nocab = "101"');
                        }

                        // print_r($arr_jurnal);
                        // exit;
                    }
                }
            }

            $this->db->trans_commit();

            echo "Success !";
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
    }
}
