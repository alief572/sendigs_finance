<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Jurnal extends Admin_Controller
{
    protected $viewPermission     = 'Jurnal.View';
    protected $addPermission      = 'Jurnal.Add';
    protected $managePermission = 'Jurnal.Manage';
    protected $deletePermission = 'Jurnal.Delete';

    protected $consultant;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Jurnal/Jurnal_model',
            'Jurnal/Jurnal_nomor_model'
        ));
        $this->template->title('Jurnal');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
    }

    public function index()
    {
        $this->template->title('Jurnal');
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
                throw new Exception('Data Jurnal tidak ditemukan');
            }

            $id_company = $get_jurnal->id_company;

            if ($get_jurnal->jenis_transaksi == 'Invoicing') {
                $get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_jurnal->no_transaksi])->row();
                if (!$get_invoicing) {
                    throw new Exception('Data Invoicing tidak ditemukan');
                }

                $Nomor_JV = $this->Jurnal_nomor_model->get_Nomor_Jurnal_Sales('101', $get_jurnal->tgl_jurnal, $id_company);

                $Bln = substr($get_jurnal->tgl_jurnal, 5, 2);
                $Thn = substr($get_jurnal->tgl_jurnal, 0, 4);

                $dataJVhead = array(
                    'nomor'             => $Nomor_JV,
                    'tgl'               => $get_jurnal->tgl_jurnal,
                    'jml'               => $get_invoicing->total_akhir_jurnal,
                    'koreksi_no'        => '-',
                    'kdcab'             => '101',
                    'jenis'             => 'JV',
                    'keterangan'        => $get_jurnal->keterangan,
                    'bulan'             => $Bln,
                    'tahun'             => $Thn,
                    'user_id'           => $this->auth->user_id(),
                    'memo'              => '',
                    'tgl_jvkoreksi'     => $get_jurnal->tgl_jurnal,
                    'ho_valid'          => ''
                );

                if ($id_company == '1' || $id_company == '4') {
                    $insert_jurnal_header = $this->db->insert(DBACC_VUCA . '.javh', $dataJVhead);
                } else if ($id_company == '7') {
                    $insert_jurnal_header = $this->db->insert(DBACC_STM . '.javh', $dataJVhead);
                } else {
                    $insert_jurnal_header = $this->db->insert(DBACC_SUST . '.javh', $dataJVhead);
                }

                if (!$insert_jurnal_header) {
                    throw new Exception('Gagal insert jurnal header');
                }

                $get_jurnal_all = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_jurnal->no_transaksi, 'jenis_transaksi' => $get_jurnal->jenis_transaksi])->result();

                foreach ($get_jurnal_all as $item_jurnal_all) {
                    $datadetail = [
                        'tipe' => 'JV',
                        'nomor' => $Nomor_JV,
                        'tanggal' => $item_jurnal_all->tgl_jurnal,
                        'no_perkiraan' => $item_jurnal_all->coa,
                        'keterangan' => $item_jurnal_all->keterangan,
                        'no_reff' => $item_jurnal_all->no_transaksi,
                        'debet' => $item_jurnal_all->debit,
                        'kredit' => $item_jurnal_all->kredit
                    ];

                    if ($id_company == '1' || $id_company == '4') {
                        $insert_jurnal_detail = $this->db->insert(DBACC_VUCA . '.jurnal', $datadetail);
                        $jurnal_posting = $this->db->update(DBACC_VUCA . '.jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item_jurnal_all->no_transaksi]);
                    } else if ($id_company == '7') {
                        $insert_jurnal_detail = $this->db->insert(DBACC_STM . '.jurnal', $datadetail);
                        $jurnal_posting = $this->db->update(DBACC_STM . '.jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item_jurnal_all->no_transaksi]);
                    } else {
                        $insert_jurnal_detail = $this->db->insert(DBACC_SUST . '.jurnal', $datadetail);
                        $jurnal_posting = $this->db->update(DBACC_SUST . '.jurnal', ['stspos' => 1], ['tipe' => 'JV', 'nomor' => $Nomor_JV, 'no_reff' => $item_jurnal_all->no_transaksi]);
                    }

                    if (!$insert_jurnal_detail) {
                        throw new Exception('Gagal insert jurnal detail');
                    }
                    if (!$jurnal_posting) {
                        throw new Exception('Gagal update status posting jurnal');
                    }

                    $update_jurnal_awal = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item_jurnal_all->id]);
                    if (!$update_jurnal_awal) {
                        throw new Exception('Gagal update status tr_jurnal awal');
                    }
                }

                if ($id_company == '1' || $id_company == '4') {
                    $Qry_Update_Cabang_acc = $this->db->query("UPDATE " . DBACC_VUCA . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");
                } else if ($id_company == '7') {
                    $Qry_Update_Cabang_acc = $this->db->query("UPDATE " . DBACC_STM . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");
                } else {
                    $Qry_Update_Cabang_acc = $this->db->query("UPDATE " . DBACC_SUST . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");
                }

                if (!$Qry_Update_Cabang_acc) {
                    throw new Exception('Gagal update nomorJC cabang');
                }

                $datapiutang = array(
                    'tipe'            => 'JV',
                    'nomor'           => $Nomor_JV,
                    'tanggal'         => $get_jurnal->tgl_jurnal,
                    'no_perkiraan'    => '1104-01-01',
                    'keterangan'      => $get_jurnal->keterangan,
                    'no_reff'         => $get_invoicing->id,
                    'debet'           => $get_invoicing->total_akhir_jurnal,
                    'kredit'          => 0,
                    'id_supplier'     => $get_invoicing->id_customer,
                    'nama_supplier'   => $get_invoicing->nm_customer,
                );

                $insert_kartu_piutang = $this->db->insert('tr_kartu_piutang', $datapiutang);
                if (!$insert_kartu_piutang) {
                    throw new Exception('Gagal insert kartu piutang');
                }
            } else {
                $get_payment_approve = $this->db->get_where('payment_approve', ['id' => $get_jurnal->no_transaksi])->row();
                if (!$get_payment_approve) {
                    throw new Exception('Data Payment Approve tidak ditemukan');
                }

                $Nomor_JV = $this->Jurnal_nomor_model->get_no_buk('101', $id_company);
                $get_jurnal_all = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_jurnal->no_transaksi, 'jenis_transaksi' => $get_jurnal->jenis_transaksi])->result();

                foreach ($get_jurnal_all as $item_jurnal_all) {
                    $update_tr_jurnal = $this->db->update('tr_jurnal', ['sts' => '1'], ['id' => $item_jurnal_all->id]);
                    if (!$update_tr_jurnal) {
                        throw new Exception('Gagal update status tr_jurnal payment');
                    }

                    $datadetail = [
                        'tipe' => 'BUK',
                        'nomor' => $Nomor_JV,
                        'tanggal' => $item_jurnal_all->tgl_jurnal,
                        'no_reff' => $item_jurnal_all->no_transaksi,
                        'no_perkiraan' => $item_jurnal_all->coa,
                        'keterangan' => $item_jurnal_all->keterangan,
                        'debet' => $item_jurnal_all->debit,
                        'kredit' => $item_jurnal_all->kredit,
                    ];

                    if ($id_company == '4') {
                        $insert_jurnal_detail = $this->db->insert(DBACC_VUCA . '.jurnal', $datadetail);
                    } else if ($id_company == '7' || $id_company == '1') {
                        $insert_jurnal_detail = $this->db->insert(DBACC_STM . '.jurnal', $datadetail);
                    } else {
                        $insert_jurnal_detail = $this->db->insert(DBACC_SUST . '.jurnal', $datadetail);
                    }

                    if (!$insert_jurnal_detail) {
                        throw new Exception('Gagal insert jurnal detail payment');
                    }
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

                if ($id_company == '4') {
                    $insert_japh = $this->db->insert(DBACC_VUCA . '.japh', $dataJVheader);
                } else if ($id_company == '7' || $id_company == '1') {
                    $insert_japh = $this->db->insert(DBACC_STM . '.japh', $dataJVheader);
                } else {
                    $insert_japh = $this->db->insert(DBACC_SUST . '.japh', $dataJVheader);
                }

                if (!$insert_japh) {
                    throw new Exception('Gagal insert jurnal header payment');
                }

                if ($id_company == '4') {
                    $Qry_Update_Cabang_acc = $this->db->query("UPDATE " . DBACC_VUCA . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'");
                } else if ($id_company == '1' || $id_company == '7') {
                    $Qry_Update_Cabang_acc = $this->db->query("UPDATE " . DBACC_STM . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'");
                } else {
                    $Qry_Update_Cabang_acc = $this->db->query("UPDATE " . DBACC_SUST . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'");
                }

                if (!$Qry_Update_Cabang_acc) {
                    throw new Exception('Gagal update nobuk cabang');
                }
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }

            $this->db->trans_commit();
            $param = array(
                'save' => 1,
                'msg'  => "SUKSES, simpan data..!!!"
            );
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $param = array(
                'save' => 0,
                'msg'  => "GAGAL, simpan data..!!! (" . $e->getMessage() . ")"
            );
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
        $this->Jurnal_model->get_data_jurnal();
    }
}
