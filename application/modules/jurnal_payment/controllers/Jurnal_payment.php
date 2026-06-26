<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Jurnal_payment extends Admin_Controller
{
    protected $viewPermission     = 'Jurnal_Payment.View';
    protected $addPermission      = 'Jurnal_Payment.Add';
    protected $managePermission = 'Jurnal_Payment.Manage';
    protected $deletePermission = 'Jurnal_Payment.Delete';

    protected $consultant;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Jurnal_payment/Jurnal_payment_model',
            'Jurnal_payment/Jurnal_payment_nomor_model'
        ));
        $this->template->title('Jurnal_payment');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
    }

    public function index()
    {

        $get_no_payment_jurnal = $this->Jurnal_payment_model->get_no_payment_jurnal();
        $get_company = $this->Jurnal_payment_model->get_company();

        $data = [
            'list_no_transaksi' => $get_no_payment_jurnal,
            'list_company' => $get_company
        ];

        $this->template->set($data);
        $this->template->title('Jurnal Payment');
        $this->template->render('index');
    }

    public function add_jurnal()
    {
        $id = $this->input->post('id');

        $get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $id])->row();

        $this->db->select('a.*');
        $this->db->from('tr_jurnal a');
        $this->db->where('a.no_transaksi', $get_jurnal->no_transaksi);
        $this->db->where('a.jenis_transaksi', $get_jurnal->jenis_transaksi);
        $this->db->where('a.sts <>', '1');
        $get_all_jurnal = $this->db->get()->result();

        $hasil = '<input type="hidden" name="id" value="' . $id . '">';

        $hasil .= '<table class="table table-striped">';
        $hasil .= '<thead>';
        $hasil .= '<tr>';
        $hasil .= '<td class="text-center">Tanggal</td>';
        $hasil .= '<td class="text-center">Tipe</td>';
        $hasil .= '<td class="text-center">No. COA</td>';
        $hasil .= '<td class="text-center">Nama COA</td>';
        $hasil .= '<td class="text-center">Keterangan</td>';
        $hasil .= '<td class="text-center">No. Reff</td>';
        $hasil .= '<td class="text-center">Debit</td>';
        $hasil .= '<td class="text-center">Kredit</td>';
        $hasil .= '</tr>';
        $hasil .= '</thead>';
        $hasil .= '<tbody>';

        $no = 0;
        $ttl_debit = 0;
        $ttl_kredit = 0;
        foreach ($get_all_jurnal as $item) {
            // Skip baris yang debit dan kredit sama-sama 0
            if ($item->debit <= 0 && $item->kredit <= 0) {
                continue;
            }

            $no++;

            $hasil .= '<tr>';

            $hasil .= '<td class="text-center">';
            $hasil .= date('d F Y', strtotime($item->tgl_jurnal));
            $hasil .= '<input type="hidden" name="jurnal[' . $no . '][id]" value="' . $item->id . '">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">' . $item->jenis_transaksi . '</td>';
            $hasil .= '<td class="text-center">' . $item->coa . '</td>';
            $hasil .= '<td class="text-center">' . $item->nm_coa . '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= '<textarea class="form-control form-control-sm" name="jurnal[' . $no . '][keterangan]">' . $item->keterangan . '</textarea>';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= $item->no_transaksi;
            $hasil .= '<input type="hidden" name="jurnal[' . $no . '][no_transaksi]">';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= '<input type="input" class="form-control form-control-sm text-right" name="jurnal[' . $no . '][debit]" value="' . number_format($item->debit) . '" readonly>';
            $hasil .= '</td>';
            $hasil .= '<td class="text-center">';
            $hasil .= '<input type="input" class="form-control form-control-sm text-right" name="jurnal[' . $no . '][kredit]" value="' . number_format($item->kredit) . '" readonly>';
            $hasil .= '</td>';

            $hasil .= '</tr>';

            $ttl_debit += $item->debit;
            $ttl_kredit += $item->kredit;
        }

        $hasil .= '</tbody>';
        $hasil .= '<tfoot>';
        $hasil .= '<tr>';
        $hasil .= '<th class="text-right" colspan="6">Total</th>';
        $hasil .= '<td class="text-right">';
        $hasil .= '<input type="text" class="form-control form-control-sm text-right" name="ttl_debit" value="' . number_format($ttl_debit) . '" readonly>';
        $hasil .= '</td>';
        $hasil .= '<td class="text-right">';
        $hasil .= '<input type="text" class="form-control form-control-sm text-right" name="ttl_kredit" value="' . number_format($ttl_kredit) . '" readonly>';
        $hasil .= '</td>';
        $hasil .= '</tr>';
        $hasil .= '</tfoot>';
        $hasil .= '</table>';

        echo $hasil;
    }

    public function save_posting_jurnal()
    {
        $post = $this->input->post();

        try {
            $this->db->trans_begin();

            $get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $post['id']])->row();
            if (!$get_jurnal) {
                throw new Exception('Data Jurnal_payment tidak ditemukan');
            }

            $id_company = $get_jurnal->id_company;
            $acc = $this->_get_acc_db($id_company, $get_jurnal->jenis_transaksi);

            if ($get_jurnal->jenis_transaksi == 'Invoicing') {
                $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_jurnal->no_transaksi])->row();
                if (!$get_invoicing) {
                    throw new Exception('Data Invoicing tidak ditemukan');
                }

                $Nomor_JV = $this->Jurnal_payment_nomor_model->get_Nomor_Jurnal_payment_Sales('101', $get_jurnal->tgl_jurnal, $id_company);

                $Bln = substr($get_jurnal->tgl_jurnal, 5, 2);
                $Thn = substr($get_jurnal->tgl_jurnal, 0, 4);

                $dataJVhead = [
                    'nomor'         => $Nomor_JV,
                    'tgl'           => $get_jurnal->tgl_jurnal,
                    'jml'           => $get_invoicing->total_akhir_jurnal,
                    'koreksi_no'    => '-',
                    'kdcab'         => '101',
                    'jenis'         => 'JV',
                    'keterangan'    => $get_jurnal->keterangan,
                    'bulan'         => $Bln,
                    'tahun'         => $Thn,
                    'user_id'       => $this->auth->user_id(),
                    'memo'          => '',
                    'tgl_jvkoreksi' => $get_jurnal->tgl_jurnal,
                    'ho_valid'      => ''
                ];

                if (!$acc->db->insert('javh', $dataJVhead)) {
                    throw new Exception('Gagal insert jurnal header');
                }

                $get_jurnal_all = $this->db->get_where('tr_jurnal', [
                    'no_transaksi'    => $get_jurnal->no_transaksi,
                    'jenis_transaksi' => $get_jurnal->jenis_transaksi
                ])->result();

                $details = [];
                $ids = [];
                foreach ($get_jurnal_all as $item) {
                    // Hanya insert ke tras jika debit atau kredit > 0
                    if ($item->debit > 0 || $item->kredit > 0) {
                        $details[] = [
                            'tipe'         => 'JV',
                            'nomor'        => $Nomor_JV,
                            'tanggal'      => $item->tgl_jurnal,
                            'no_perkiraan' => $item->coa,
                            'keterangan'   => $item->keterangan,
                            'no_reff'      => $item->no_transaksi,
                            'debet'        => $item->debit,
                            'kredit'       => $item->kredit,
                            'stspos'       => 1
                        ];
                    }
                    // Semua baris tetap di-update sts = 1
                    $ids[] = $item->id;
                }

                if (!empty($details)) {
                    if (!$acc->db->insert_batch('jurnal', $details)) {
                        throw new Exception('Gagal insert jurnal detail');
                    }
                }
                if (!empty($ids)) {
                    $this->db->where_in('id', $ids)->update('tr_jurnal', ['sts' => '1']);
                }

                $acc->db->set('nomorJC', 'nomorJC + 1', FALSE)->where('nocab', '101')->update('pastibisa_tb_cabang');

                $datapiutang = [
                    'tipe'          => 'JV',
                    'nomor'         => $Nomor_JV,
                    'tanggal'       => $get_jurnal->tgl_jurnal,
                    'no_perkiraan'  => '1104-01-01',
                    'keterangan'    => $get_jurnal->keterangan,
                    'no_reff'       => $get_invoicing->id,
                    'debet'         => $get_invoicing->total_akhir_jurnal,
                    'kredit'        => 0,
                    'id_supplier'   => $get_invoicing->id_customer,
                    'nama_supplier' => $get_invoicing->nm_customer,
                ];

                if (!$this->db->insert('tr_kartu_piutang', $datapiutang)) {
                    throw new Exception('Gagal insert kartu piutang');
                }
            } else if ($get_jurnal->jenis_transaksi == 'Penerimaan Piutang PPH 23') {
                $this->load->model('Jurnal_payment_penerimaan/Jurnal_payment_penerimaan_nomor_model');

                $get_jurnal_detail = $this->db->get_where('tr_jurnal', [
                    'no_transaksi'    => $get_jurnal->no_transaksi,
                    'jenis_transaksi' => $get_jurnal->jenis_transaksi,
                ])->result();

                $get_pen_pph_23 = $this->db->get_where('tr_penerimaan_pph_23', ['id' => $get_jurnal->no_transaksi])->row();
                if (!$get_pen_pph_23) {
                    throw new Exception('Data Penerimaan PPH 23 tidak ditemukan');
                }

                $get_inv = $this->db->select('a.*, COALESCE(b.id_company, c.company) as id_company')
                    ->from('tr_invoicing a')
                    ->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left')
                    ->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = a.id_penawaran', 'left')
                    ->where('a.id', $get_pen_pph_23->id_inv)
                    ->get()
                    ->row();

                $Nomor_BUM = $this->Jurnal_payment_penerimaan_nomor_model->get_Nomor_Jurnal_payment_BUM('101', $get_inv->tanggal_invoice, $get_inv->id_company ?? '');
                $nilai     = ($get_jurnal->debit > 0) ? $get_jurnal->debit : $get_jurnal->kredit;

                $arr_insert_jarh = [
                    'nomor'       => $Nomor_BUM,
                    'tgl'         => $get_jurnal->tgl_jurnal,
                    'jml'         => $nilai,
                    'kdcab'       => '101',
                    'jenis_reff'  => 'BUM',
                    'no_reff'     => $get_inv->id,
                    'customer'    => $get_inv->nm_customer,
                    'terima_dari' => $this->auth->user_name(),
                    'jenis_ar'    => 'BUM',
                    'note'        => $get_jurnal->keterangan,
                    'user_id'     => $this->auth->user_id(),
                    'tgl_invoice' => $get_inv->tanggal_invoice,
                ];

                $arr_jurnal = [];
                foreach ($get_jurnal_detail as $item) {
                    // Hanya insert ke tras jika debit atau kredit > 0
                    if ($item->debit > 0 || $item->kredit > 0) {
                        $arr_jurnal[] = [
                            'tipe'          => 'BUM',
                            'nomor'         => $Nomor_BUM,
                            'tanggal'       => $item->tgl_jurnal,
                            'no_perkiraan'  => $item->coa,
                            'keterangan'    => $item->keterangan,
                            'no_reff'       => $get_inv->id,
                            'debet'         => $item->debit,
                            'kredit'        => $item->kredit,
                            'id_perusahaan' => $item->id_company,
                            'nm_perusahaan' => $item->nm_company,
                        ];
                    }
                }

                $acc->db->insert('jarh', $arr_insert_jarh);
                if (!empty($arr_jurnal)) {
                    $acc->db->insert_batch('jurnal', $arr_jurnal);
                }

                // Semua baris tetap di-update sts = 1
                $this->db->where([
                    'no_transaksi'    => $get_jurnal->no_transaksi,
                    'jenis_transaksi' => $get_jurnal->jenis_transaksi,
                ])->update('tr_jurnal', ['sts' => '1']);

                $acc->db->set('nobum', 'nobum + 1', FALSE)->where('nocab', '101')->update('pastibisa_tb_cabang');
            } else {
                $get_payment_approve = $this->db->get_where('payment_approve', ['id' => $get_jurnal->no_transaksi])->row();
                if (!$get_payment_approve) {
                    throw new Exception('Data Payment Approve tidak ditemukan');
                }

                $Nomor_JV = $this->Jurnal_payment_nomor_model->get_no_buk('101', $id_company);
                $get_jurnal_all = $this->db->get_where('tr_jurnal', [
                    'no_transaksi'    => $get_jurnal->no_transaksi,
                    'jenis_transaksi' => $get_jurnal->jenis_transaksi
                ])->result();

                $details = [];
                $ids = [];
                foreach ($get_jurnal_all as $item) {
                    // Hanya insert ke tras jika debit atau kredit > 0
                    if ($item->debit > 0 || $item->kredit > 0) {
                        $details[] = [
                            'tipe'         => 'BUK',
                            'nomor'        => $Nomor_JV,
                            'tanggal'      => $item->tgl_jurnal,
                            'no_reff'      => $item->no_transaksi,
                            'no_perkiraan' => $item->coa,
                            'keterangan'   => $item->keterangan,
                            'debet'        => $item->debit,
                            'kredit'       => $item->kredit,
                        ];
                    }
                    // Semua baris tetap di-update sts = 1
                    $ids[] = $item->id;
                }

                if (!empty($details)) {
                    if (!$acc->db->insert_batch('jurnal', $details)) {
                        throw new Exception('Gagal insert jurnal detail payment');
                    }
                }
                if (!empty($ids)) {
                    $this->db->where_in('id', $ids)->update('tr_jurnal', ['sts' => '1']);
                }

                $dataJVheader = [
                    'nomor'      => $Nomor_JV,
                    'tgl'        => $get_jurnal->tgl_jurnal,
                    'jml'        => $get_payment_approve->jumlah,
                    'kdcab'      => '101',
                    'jenis_reff' => 'BUK',
                    'no_reff'    => $get_jurnal->no_transaksi,
                    'jenis_ap'   => 'V',
                    'note'       => 'Payment ' . $get_jurnal->no_transaksi,
                    'user_id'    => $this->auth->user_name(),
                    'ho_valid'   => '',
                    'batal'      => '0'
                ];

                if (!$acc->db->insert('japh', $dataJVheader)) {
                    throw new Exception('Gagal insert jurnal header payment');
                }

                $acc->db->set('nobuk', 'nobuk + 1', FALSE)->where('nocab', '101')->update('pastibisa_tb_cabang');
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }

            $this->db->trans_commit();
            $param = ['save' => 1, 'msg' => "SUKSES, simpan data..!!!"];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $param = ['save' => 0, 'msg' => "GAGAL, simpan data..!!! (" . $e->getMessage() . ")"];
        }

        echo json_encode($param);
    }

    public function fix_company()

    {
        $get_jurnal = $this->db->get_where('tr_jurnal', ['jenis_transaksi' => 'Penerimaan Piutang'])->result();

        $arr_update_jurnal = [];
        foreach ($get_jurnal as $item_jurnal) {
            $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_jurnal->no_transaksi])->row();

            $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_invoicing->id_penawaran])->row();
            $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row();

            $id_company = (!empty($get_company)) ? $get_company->id : '';
            $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';


            $arr_update_jurnal[] = [
                'id' => $item_jurnal->id,
                'id_company' => $id_company,
                'nm_company' => $nm_company
            ];
        }

        $this->db->trans_begin();

        $update_jurnal = $this->db->update_batch('tr_jurnal', $arr_update_jurnal, 'id');
        if (!$update_jurnal) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            echo json_encode([
                'status' => 1,
                'msg' => 'Data has not been updated !'
            ]);
        } else {
            $this->db->trans_commit();

            echo json_encode([
                'status' => 1,
                'msg' => 'Data has been updated !'
            ]);
        }
    }

    public function get_data_jurnal()
    {
        $this->Jurnal_payment_model->get_data_jurnal();
    }
    /**
     * Helper to get accounting database connection and name
     */
    private function _get_acc_db($id_company, $jenis_transaksi)
    {
        $db_key = '';
        $db_name = '';


        if ($jenis_transaksi == 'Invoicing') {
            if ($id_company == '4') {
                $db_key = 'accounting_vuca';
                $db_name = DBACC_VUCA;
            } else if (in_array($id_company, ['1', '6', '7'])) {
                $db_key = 'accounting_stm';
                $db_name = DBACC_STM;
            } else {
                $db_key = 'accounting_sustain';
                $db_name = DBACC_SUST;
            }
        } else {
            // Mapping for other transactions (PPH 23, Payment, etc.)
            if ($id_company == '4') {
                $db_key = 'accounting_vuca';
                $db_name = DBACC_VUCA;
            } else if (in_array($id_company, ['1', '6', '7'])) {
                $db_key = 'accounting_stm';
                $db_name = DBACC_STM;
            } else {
                $db_key = 'accounting_sustain';
                $db_name = DBACC_SUST;
            }
        }

        return (object)[
            'db'   => $this->load->database($db_key, TRUE),
            'name' => $db_name
        ];
    }

    public function export_jurnal()
    {
        $tgl_jurnal = $this->input->get('tgl_jurnal');
        $tgl_from = '';
        $tgl_to = '';
        if (!empty($tgl_jurnal)) :
            $exp_tgl_jurnal = explode(' to ', $tgl_jurnal);
            $tgl_from = $exp_tgl_jurnal[0];
            $tgl_to = $exp_tgl_jurnal[1];
        endif;

        $no_transaksi = $this->input->get('no_transaksi');
        $company = $this->input->get('company');

        $filter = [
            'tgl_from' => $tgl_from,
            'tgl_to' => $tgl_to,
            'no_transaksi' => $no_transaksi,
            'company' => $company
        ];

        $get_data = $this->Jurnal_payment_model->get_list_jurnal($filter);

        if (empty($get_data)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Data tidak ditemukan !'
            ]);
            exit;
        }

        $data = [
            'list_jurnal' => $get_data
        ];

        $this->load->view('export_excel', $data);
    }
}
