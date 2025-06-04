<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is controller for Request Payment
 */

class Request_mutasi extends Admin_Controller
{

    //Permission
    protected $viewPermission   = "Request_mutasi.View";
    protected $addPermission    = "Request_mutasi.Add";
    protected $managePermission = "Request_mutasi.Manage";
    protected $deletePermission = "Request_mutasi.Delete";

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Request_mutasi/Request_mutasi_model', 'All/All_model', 'Jurnal_nomor/Jurnal_model', 'Penerimaan/Acc_model'));
        $this->template->title('Manage Request Mutasi');
        $this->template->page_icon('fa fa-table');
        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $data = $this->Request_mutasi_model->GetListDataRequest();
        $this->template->set('data', $data);
        $this->template->title('Request Mutasi');
        $this->template->render('index');
    }

    public function create()
    {
        $getInv             = $this->db->query("SELECT * FROM tr_invoice_header")->row();
        $Cust               = $this->db->query("SELECT a.id_customer,b.nm_customer FROM tr_invoice_header a
											INNER JOIN customer b on a.id_customer=b.id_customer GROUP BY a.id_customer")->result();
        $bank1              = $this->Jurnal_model->get_Coa_Bank_Aja('101');
        $pphpenjualan       = $this->Acc_model->combo_pph_penjualan();
        $datacoa            = $this->Acc_model->GetCoaCombo();
        $template           = $this->Acc_model->GetTemplate();
        $data_coa_bank      = $this->All_model->GetCoaCombo('5', " a.kode_bank IS NOT NULL");
        $matauang           = $this->All_model->GetKursCombo();
        $data = [
            'result'        => $getInv,
            'customer'      => $Cust,
            'bank1'         => $bank1,
            'pphpenjualan'  => $pphpenjualan,
            'datacoa'       => $datacoa,
            'template'      => $template,
            'datbank'       => $data_coa_bank,
            'matauang'      => $matauang,
        ];

        $this->template->title('Form Request Mutasi');
        $this->template->render('form', $data);
    }

    public function save()
    {
        $post   = $this->input->post();

        $tgl    = $post['tgl_request'];
        $dari   = $post['dari'];
        $ke     = $post['ke'];

        $kode_mutasi   = $this->Request_mutasi_model->generate_nopn($tgl);
        $bank_asal     = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE no_perkiraan='$dari'")->row();
        $bank_tujuan   = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE no_perkiraan='$ke'")->row();

        $this->db->trans_begin();
        $data = array(
            'kd_mutasi'         => $kode_mutasi,
            'tgl_request'       => date('Y-m-d', strtotime($post['tgl_request'])),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'mata_uang'         => $post['matauang'],
            'nilai_request'     => str_replace(",", "", $post['nilai']),
            'keterangan'        => $post['keterangan'],
            'terbilang'         => $post['terbilang'],
            'nama_bank_asal'    => $bank_asal->nama,
            'nama_bank_tujuan'  => $bank_tujuan->nama,
            'created_by'        => $this->auth->user_name(),
            'created_on'        => date('Y-m-d H:i:s'),
        );
        $this->db->insert('tr_request_mutasi', $data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $return = array(
                'status' => 2,
                'pesan'  => 'Save Process Failed. Please Try Again...'
            );
        } else {
            $this->db->trans_commit();
            $return = array(
                'status' => 1,
                'nomor'  => $kode_mutasi,
                'pesan'  => 'Save Process Success. '
            );
        }
        echo json_encode($return);
    }

    public function mutasi()
    {
        $data = $this->Request_mutasi_model->GetListDataMutasi();
        $this->template->title('List Mutasi');
        $this->template->set('data', $data);
        $this->template->render('mutasi');
    }

    public function approval_mutasi()
    {
        $data = $this->Request_mutasi_model->GetListApproval();
        $this->template->set('data', $data);
        $this->template->page_icon('fa fa-list');
        $this->template->title('List Approval Mutasi');
        $this->template->render('index_approval');
    }

    public function add_mutasi($id)
    {
        $result             = $this->Request_mutasi_model->GetDataApprove($id);
        $bank1              = $this->Jurnal_model->get_Coa_Bank_Aja('101');
        $pphpenjualan       = $this->Acc_model->combo_pph_penjualan();
        $datacoa            = $this->Acc_model->GetCoaCombo();
        $template           = $this->Acc_model->GetTemplate();
        $data_coa_bank      = $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
        $matauang           = $this->All_model->GetKursCombo();
        $data = [
            'result'        => $result,
            'bank1'         => $bank1,
            'pphpenjualan'  => $pphpenjualan,
            'datacoa'       => $datacoa,
            'template'      => $template,
            'datbank'       => $data_coa_bank,
            'matauang'      => $matauang,
        ];
        $this->template->page_icon('fa fa-plus');
        $this->template->title('Add Mutasi');
        $this->template->render('add', $data);
    }

    public function save_mutasi()
    {
        $post           = $this->input->post();
        $kode_mutasi    = $post['no_request'];

        $this->db->trans_begin();
        $data = array(
            'kd_mutasi'             => $kode_mutasi,
            'tgl_request'           => $post['tgl_request'],
            'bank_asal'             => $post['dari'],
            'bank_tujuan'           => $post['ke'],
            // 'mata_uang'             => $this->input->post('matauang'),
            'nilai_request'         => str_replace(",", "", $post['nilai']),
            'keterangan'            => $post['keterangan'],
            'nama_bank_asal'        => $post['darinama'],
            'nama_bank_tujuan'      => $post['kenama'],
            'created_by'            => $this->auth->user_name(),
            'created_on'            => date('Y-m-d H:i:s'),
            'dari'                  => $post['dari_matauang'],
            'ke'                    => $post['ke_matauang'],
            'kurs'                  => $post['kurs'],
            'nilai_aktual'          => str_replace(",", "", $post['rupiah']),
            'terbilang'             => ynz_terbilang($post['rupiah']),
            'tgl_mutasi'            => $post['tgl'],
        );

        $update = array(
            'status'    => '1',
        );

        $this->db->insert('tr_request_mutasi_aktual', $data);
        $this->db->update('tr_request_mutasi', $update, ['kd_mutasi' => $kode_mutasi]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $return = array(
                'status' => 2,
                'pesan'  => 'Save Process Failed. Please Try Again...'
            );
        } else {
            $this->db->trans_commit();
            $return = array(
                'status' => 1,
                'nomor'  => $kode_mutasi,
                'pesan'  => 'Save Process Success. '
            );
        }
        echo json_encode($return);
    }

    public function approval($id)
    {
        $data = $this->Request_mutasi_model->GetDataApprove($id);
        $this->template->set('data', $data);
        $this->template->page_icon('fa fa-check');
        $this->template->title('Approval Mutasi');
        $this->template->render('approval');
    }

    public function save_approval()
    {
        $kd_mutasi = $this->input->post('no_request');

        $data = array(
            'status_approve'    => '1',
            'approved_by'       => $this->auth->user_name(),
            'approved_on'       => date('Y-m-d H:i:s'),
        );

        $this->db->trans_begin();
        $this->db->update('tr_request_mutasi', $data, ['kd_mutasi' => $kd_mutasi]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Return = array(
                'status'    => 2,
                'pesan'     => 'Approve Process Failed. Please Try Again...'
            );
        } else {
            $this->db->trans_commit();
            $Arr_Return = array(
                'status'    => 1,
                'nomor'     => $kd_mutasi,
                'pesan'     => 'Approve Process Success. '
            );
        }
        echo json_encode($Arr_Return);
    }

    public function reject_mutasi()
    {
        $kd_mutasi = $this->input->post('no_request');

        $data = array(
            'alasan'            => $this->input->post('alasan'),
            'status_approve'    => '2',
        );

        $this->db->trans_begin();
        $this->db->update('tr_request_mutasi', $data, ['kd_mutasi' => $kd_mutasi]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Return = array(
                'status'    => 2,
                'pesan'     => 'Reject Process Failed. Please Try Again...'
            );
        } else {
            $this->db->trans_commit();
            $Arr_Return = array(
                'status'    => 1,
                'nomor'     => $kd_mutasi,
                'pesan'     => 'Reject Process Success. '
            );
        }
        echo json_encode($Arr_Return);
    }

    public function index_transaksi()
    {
        $data = $this->Request_mutasi_model->GetListDataTransaksi();
        $this->template->set('data', $data);
        $this->template->title('Data Transaksi Bank');
        $this->template->render('index_transaksi');
    }

    public function create_transaksi()
    {
        $getInv             = $this->db->query("SELECT * FROM tr_invoice_header")->row();
        $Cust               = $this->db->query("SELECT a.id_customer,b.nm_customer FROM tr_invoice_header a
											INNER JOIN customer b on a.id_customer=b.id_customer GROUP BY a.id_customer")->result();
        $bank1              = $this->Jurnal_model->get_Coa_Bank_Aja('101');
        $pphpenjualan       = $this->Acc_model->combo_pph_penjualan();
        $datacoa            = $this->Acc_model->GetCoaCombo();
        $template           = $this->Acc_model->GetTemplate();
        $data_coa_bank      = $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
        $matauang           = $this->All_model->GetKursCombo();
        $data = [
            'result'        => $getInv,
            'customer'      => $Cust,
            'bank1'         => $bank1,
            'pphpenjualan'  => $pphpenjualan,
            'datacoa'       => $datacoa,
            'template'      => $template,
            'datbank'       => $data_coa_bank,
            'matauang'      => $matauang,
        ];

        $this->template->title('Form Transaksi Bank');
        $this->template->render('form_transaksi', $data);
    }

    public function save_transaksi()
    {
        $post   = $this->input->post();

        $tgl    = $post['tgl_request'];
        $dari   = $post['dari'];
        $ke     = $post['ke'];
        $jenis  = $post['jenis_transaksi'];

        if ($jenis == 'keluar') {
            $kode_mutasi    = $this->Request_mutasi_model->generate_notr($tgl);
        } else {
            $kode_mutasi    = $this->Request_mutasi_model->generate_nokm($tgl);
        }

        $bank_asal     = $this->db->query("SELECT * FROM gl_sendigs_manufaktur.coa_master WHERE no_perkiraan='$dari'")->row();
        $bank_tujuan   = $this->db->query("SELECT * FROM gl_sendigs_manufaktur.coa_master WHERE no_perkiraan='$ke'")->row();

        $this->db->trans_begin();
        $data = array(
            'kd_mutasi'         => $kode_mutasi,
            'tgl_request'       => date('Y-m-d', strtotime($post['tgl_request'])),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'mata_uang'         => $post['matauang'],
            'kurs'              => $post['kurs'],
            'nilai'             => str_replace(",", "", $post['nilai']),
            'transaksi'         => str_replace(",", "", $post['transaksi']),
            'keterangan'        => $post['keterangan'],
            'terbilang'         => $post['terbilang'],
            'nama_bank_asal'    => $bank_asal->nama,
            'nama_bank_tujuan'  => $bank_tujuan->nama,
            'created_by'        => $this->auth->user_name(),
            'created_on'        => date('Y-m-d H:i:s'),
            'jenis_transaksi'   => $jenis,
        );
        $this->db->insert('tr_request_mutasi_admin', $data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Return = array(
                'status'    => 2,
                'pesan'     => 'Save Process Failed. Please Try Again...'
            );
        } else {
            if ($jenis == 'keluar') {
                $jurnal_status = $this->save_jurnal_BUK($kode_mutasi);
            } elseif ($jenis == 'terima') {
                $jurnal_status = $this->save_jurnal_BUM($kode_mutasi);
            }

            if (!$jurnal_status) {
                $this->db->trans_rollback();
                $Arr_Return = array(
                    'status'    => 2,
                    'pesan'     => 'Jurnal Save Failed. Transaction has been rolled back.'
                );
            } else {
                $this->db->trans_commit();
                $Arr_Return = array(
                    'status'    => 1,
                    'nomor'     => $kode_mutasi,
                    'pesan'     => 'Save Process Success.'
                );
            }
        }
        echo json_encode($Arr_Return);
    }

    public function save_jurnal_BUM($kode_mutasi)
    {
        $jurnal     = $this->db->query("SELECT * FROM tr_request_mutasi_admin WHERE kd_mutasi='$kode_mutasi'")->row();
        if (!$jurnal) return;

        $Nomor_JV   = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $jurnal->tgl_request);

        // Insert ke tabel header jurnal
        $this->db->insert(DBACC . '.jarh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $jurnal->tgl_request,
            'jml'           => $jurnal->transaksi,
            'kd_pembayaran' => $kode_mutasi,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUM',
            'no_reff'       => $kode_mutasi,
            'terima_dari'   => $jurnal->keterangan,
            'jenis_ar'      => 'BUM',
            'note'          => $jurnal->keterangan,
            'batal'         => '0'
        ]);

        // Data untuk jurnal debit dan kredit
        $det_Jurnaltes = [
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUM',
                'no_perkiraan'          => $jurnal->bank_tujuan,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => $jurnal->transaksi,
                'kredit'                => 0,
                'nilai_valas_debet'     => $jurnal->nilai,
                'nilai_valas_kredit'    => 0,
                'created_on'            => date('Y-m-d H:i:s'),
                'created_by'            => $this->auth->user_name(),
            ],
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUM',
                'no_perkiraan'          => $jurnal->bank_asal,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => 0,
                'kredit'                => $jurnal->transaksi,
                'nilai_valas_debet'     => 0,
                'nilai_valas_kredit'    => $jurnal->nilai,
                'created_on'            => date('Y-m-d H:i:s'),
                'created_by'            => $this->auth->user_name(),
            ]
        ];

        // Insert batch ke jurnal
        $this->db->insert_batch(DBACC . '.jurnal', $det_Jurnaltes);

        // Update nomor BUM di tabel cabang
        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'");

        // Update jurnal1 di tr_request_mutasi_admin
        $this->db->query("UPDATE tr_request_mutasi_admin SET jurnal1 = '$Nomor_JV' WHERE kd_mutasi = '$kode_mutasi'");

        return ($this->db->affected_rows() > 0);
    }

    public function save_jurnal_BUK($kode_mutasi)
    {
        $jurnal         = $this->db->query("SELECT * FROM tr_request_mutasi_admin WHERE kd_mutasi='$kode_mutasi'")->row();
        if (!$jurnal) return;

        $Nomor_JV       = $this->Jurnal_model->get_Nomor_Jurnal_BUK2('101', $jurnal->tgl_request);
        $nokir_debet    = ($jurnal->jenis_transaksi == 'bank') ? '1112-01-01' : $jurnal->bank_asal;

        // Insert ke tabel header jurnal
        $this->db->insert(DBACC . '.japh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $jurnal->tgl_request,
            'jml'           => $jurnal->transaksi,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUK',
            'no_reff'       => $kode_mutasi,
            'bayar_kepada'  => $jurnal->bank_tujuan,
            'jenis_ap'      => 'BUK',
            'note'          => $jurnal->keterangan,
            'batal'         => '0',
            'user_id'       => $this->auth->user_id(),
        ]);

        // Data jurnal debit dan kredit
        $det_Jurnaltes = [
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUK',
                'no_perkiraan'          => $nokir_debet,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => $jurnal->transaksi,
                'kredit'                => 0,
                'nilai_valas_debet'     => 0,
                'nilai_valas_kredit'    => $jurnal->nilai,
            ],
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUK',
                'no_perkiraan'          => $jurnal->bank_tujuan,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => 0,
                'kredit'                => $jurnal->transaksi,
                'nilai_valas_debet'     => $jurnal->nilai,
                'nilai_valas_kredit'    => 0,
            ]
        ];

        // Insert batch ke jurnal
        $this->db->insert_batch(DBACC . '.jurnal', $det_Jurnaltes);

        // Update nomor BUK di tabel cabang dan jurnal2 di mutasi admin
        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk = nobuk + 1 WHERE nocab = '101'");
        $this->db->query("UPDATE tr_request_mutasi_admin SET jurnal2 = '$Nomor_JV' WHERE kd_mutasi = '$kode_mutasi'");

        return ($this->db->affected_rows() > 0);
    }


    // fungsi fungsi yang dipake di View
    function terbilang()
    {
        $nilai = $_GET['nilai'];
        $kode = $_GET['matauang'];
        $terbilang = ynz_terbilang($nilai);
        $terbilang_mata_uang = $this->getMataUangByKode($kode);
        $mata_uang = strtoupper($terbilang_mata_uang);

        echo "$terbilang $mata_uang";
    }

    function getMataUangByKode($kode)
    {
        if (empty($kode)) {
            return "Kode mata uang tidak boleh kosong!";
        }

        $query = $this->db->select('mata_uang')
            ->from('mata_uang')
            ->where('kode', $kode)
            ->get();

        if ($query->num_rows() > 0) {
            return isset($query->row()->mata_uang) ? $query->row()->mata_uang : "Tidak ditemukan";
        } else {
            return "Tidak ditemukan";
        }
    }
}
