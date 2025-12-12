<?php
class Misc extends Admin_Controller
{

    protected $accounting;
    protected $consultant;

    public function __construct()
    {
        parent::__construct();

        $this->accounting = $this->load->database('accounting', true);
        $this->consultant = $this->load->database('consultant', true);
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
}
