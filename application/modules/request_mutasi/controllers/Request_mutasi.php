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

    // Multi Accounting Target - Whitelist & Mapping
    private static $VALID_TARGET_ACCOUNTING = [
        'accounting_stm',
        'accounting_vuca',
        'accounting_sustain'
    ];

    private static $TARGET_LABELS = [
        'accounting_stm'     => 'STM',
        'accounting_vuca'    => 'VUCA',
        'accounting_sustain' => 'SUSTAIN',
    ];

    private static $TARGET_DB_MAP = [
        'accounting_stm'     => DBACC_STM,
        'accounting_vuca'    => DBACC_VUCA,
        'accounting_sustain' => DBACC_SUSTAIN,
    ];

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

        $this->template->title('Form Request Mutasi');
        $this->template->render('form', $data);
    }

    public function save()
    {
        $post   = $this->input->post();

        $tgl    = $post['tgl_request'];
        $dari   = $post['dari'];
        $ke     = $post['ke'];
        $target_accounting = isset($post['target_accounting']) ? $post['target_accounting'] : '';

        // Validasi target_accounting terhadap whitelist
        $target = $this->_resolve_target_db($target_accounting);
        if (!$target) {
            echo json_encode(['status' => 2, 'pesan' => 'Target Accounting tidak valid!']);
            return;
        }

        $kode_mutasi   = $this->Request_mutasi_model->generate_nopn($tgl);

        // Query COA validation dari target database
        $target_db = $target['connection'];
        $db_name   = $target['db_name'];

        $q_bank_asal    = $target_db->query("SELECT * FROM coa_master WHERE no_perkiraan=?", [$dari]);
        $q_bank_tujuan  = $target_db->query("SELECT * FROM coa_master WHERE no_perkiraan=?", [$ke]);

        if (!$q_bank_asal || !$q_bank_tujuan) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Database ' . $db_name . ' tidak dapat diakses.'
            ]);
            return;
        }

        $bank_asal    = $q_bank_asal->row();
        $bank_tujuan  = $q_bank_tujuan->row();

        if (!$bank_asal) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$dari' tidak ditemukan di database $db_name."]);
            return;
        }
        if (!$bank_tujuan) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$ke' tidak ditemukan di database $db_name."]);
            return;
        }

        $this->db->trans_begin();
        $data = array(
            'kd_mutasi'         => $kode_mutasi,
            'tgl_request'       => date('Y-m-d', strtotime($post['tgl_request'])),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'mata_uang'         => $post['matauang'],
            'target_accounting' => $target_accounting,
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

    /**
     * Simpan realisasi/aktual mutasi dari form add.php
     * Dipanggil via AJAX POST dari view add.php
     * URL: request_mutasi/save_mutasi
     */
    public function save_mutasi()
    {
        $post = $this->input->post();

        $no_request  = $post['no_request'];
        $dari        = $post['dari'];
        $ke          = $post['ke'];
        $keterangan  = $post['keterangan'];
        $kurs        = str_replace(',', '', $post['kurs'] ?? '1');
        $tgl         = $post['tgl'];                                    // tanggal aktual
        $nilai_req   = str_replace(',', '', $post['nilai']);            // nilai request (readonly)
        $rupiah      = str_replace(',', '', $post['rupiah']);           // nilai aktual yang diisi user

        // Ambil data request asal
        $request = $this->db->get_where('tr_request_mutasi', ['kd_mutasi' => $no_request])->row();
        if (!$request) {
            echo json_encode(['status' => 2, 'pesan' => 'Data request mutasi tidak ditemukan.']);
            return;
        }

        // Validasi target_accounting dari parent record
        if (empty($request->target_accounting)) {
            echo json_encode(['status' => 2, 'pesan' => 'Request asal belum memiliki Target Accounting.']);
            return;
        }

        // Generate kode mutasi aktual
        $kode_aktual = $this->_generate_kode_aktual($tgl);

        $this->db->trans_begin();

        // 1. Insert ke tabel aktual
        $data_aktual = [
            'kd_mutasi_aktual'  => $kode_aktual,
            'kd_mutasi_request' => $no_request,
            'tgl_request'       => $request->tgl_request,
            'tgl_mutasi'        => date('Y-m-d', strtotime($tgl)),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'nama_bank_asal'    => $request->nama_bank_asal,
            'nama_bank_tujuan'  => $request->nama_bank_tujuan,
            'mata_uang'         => $request->mata_uang,
            'target_accounting' => $request->target_accounting,
            'kurs'              => $kurs,
            'nilai_request'     => $nilai_req,
            'nilai_aktual'      => $rupiah,
            'keterangan'        => $keterangan,
            'created_by'        => $this->auth->user_name(),
            'created_on'        => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tr_request_mutasi_aktual', $data_aktual);

        // 2. Update status request menjadi selesai (status = 1)
        $this->db->update('tr_request_mutasi', ['status' => '1'], ['kd_mutasi' => $no_request]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 2, 'pesan' => 'Gagal menyimpan data mutasi.']);
            return;
        }

        // 3. Buat jurnal BUM (Bank Uang Masuk) untuk mutasi antar bank
        $jurnal_ok = $this->_save_jurnal_mutasi_aktual($kode_aktual, $dari, $ke, $rupiah, $keterangan, $tgl, $request->mata_uang, $nilai_req, $request->target_accounting);

        if (!$jurnal_ok) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 2, 'pesan' => 'Gagal membuat jurnal mutasi.']);
            return;
        }

        $this->db->trans_commit();
        echo json_encode([
            'status' => 1,
            'nomor'  => $kode_aktual,
            'pesan'  => 'Mutasi berhasil disimpan.'
        ]);
    }

    /**
     * Generate kode untuk mutasi aktual: MTR-YYMM + 5 digit urut
     */
    private function _generate_kode_aktual($tgl)
    {
        $prefix = 'MTR-' . date('ym', strtotime($tgl));

        $row = $this->db->query(
            "SELECT MAX(kd_mutasi_aktual) AS max_id FROM tr_request_mutasi_aktual WHERE kd_mutasi_aktual LIKE ?",
            [$prefix . '%']
        )->row_array();

        $next = $row && $row['max_id'] ? ((int) substr($row['max_id'], -5) + 1) : 1;
        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Resolve target accounting value ke database info
     * @param string $target_accounting - CI connection group name
     * @return array|false ['db_name' => string, 'connection' => CI_DB, 'group' => string]
     */
    private function _resolve_target_db($target_accounting)
    {
        if (empty($target_accounting) || !in_array($target_accounting, self::$VALID_TARGET_ACCOUNTING)) {
            return false;
        }

        $db_name = self::$TARGET_DB_MAP[$target_accounting];
        $connection = $this->load->database($target_accounting, TRUE);

        if (!$connection) {
            return false;
        }

        return [
            'db_name'    => $db_name,
            'connection' => $connection,
            'group'      => $target_accounting,
        ];
    }

    /**
     * AJAX endpoint: Return COA bank list dari database target
     * URL: request_mutasi/get_coa_by_target
     * Method: POST
     * Input: target_accounting (string)
     * Output: JSON {status: 1|2, data: [{no_perkiraan, nama}], pesan: string}
     */
    public function get_coa_by_target()
    {
        $target_accounting = $this->input->post('target_accounting');

        // Validate & resolve target database
        $target = $this->_resolve_target_db($target_accounting);
        if (!$target) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Target Accounting tidak valid.'
            ]);
            return;
        }

        // Query COA bank list from target database
        $db = $target['connection'];
        $query = $db->query(
            "SELECT no_perkiraan, nama FROM coa_master WHERE no_perkiraan LIKE '1101%' ORDER BY no_perkiraan ASC"
        );

        if (!$query) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Database ' . $target['db_name'] . ' tidak dapat diakses.'
            ]);
            return;
        }

        $data = $query->result_array();

        echo json_encode([
            'status' => 1,
            'data'   => $data
        ]);
    }

    /**
     * Buat jurnal untuk realisasi mutasi aktual (debit bank tujuan, kredit bank asal)
     * Jurnal ditulis ke database target sesuai target_accounting
     */
    private function _save_jurnal_mutasi_aktual($kode_aktual, $bank_asal, $bank_tujuan, $nilai_idr, $keterangan, $tgl, $mata_uang, $nilai_valas, $target_accounting)
    {
        // Resolve target database
        $target = $this->_resolve_target_db($target_accounting);
        if (!$target) {
            return false;
        }
        $db_name = $target['db_name'];

        $tgl_jurnal = date('Y-m-d', strtotime($tgl));
        $Nomor_JV   = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $tgl_jurnal, $db_name);

        // Header jurnal
        $this->db->insert($db_name . '.jarh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $tgl_jurnal,
            'jml'           => $nilai_idr,
            'kd_pembayaran' => $kode_aktual,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUM',
            'no_reff'       => $kode_aktual,
            'terima_dari'   => $keterangan,
            'jenis_ar'      => 'BUM',
            'note'          => $keterangan,
            'batal'         => '0',
        ]);

        // Detail jurnal: debit bank tujuan, kredit bank asal
        $this->db->insert_batch($db_name . '.jurnal', [
            [
                'nomor'              => $Nomor_JV,
                'tanggal'            => $tgl_jurnal,
                'tipe'               => 'BUM',
                'no_perkiraan'       => $bank_tujuan,
                'keterangan'         => $keterangan,
                'no_reff'            => $kode_aktual,
                'debet'              => $nilai_idr,
                'kredit'             => 0,
                'nilai_valas_debet'  => $nilai_valas,
                'nilai_valas_kredit' => 0,
                'created_on'         => date('Y-m-d H:i:s'),
                'created_by'         => $this->auth->user_name(),
            ],
            [
                'nomor'              => $Nomor_JV,
                'tanggal'            => $tgl_jurnal,
                'tipe'               => 'BUM',
                'no_perkiraan'       => $bank_asal,
                'keterangan'         => $keterangan,
                'no_reff'            => $kode_aktual,
                'debet'              => 0,
                'kredit'             => $nilai_idr,
                'nilai_valas_debet'  => 0,
                'nilai_valas_kredit' => $nilai_valas,
                'created_on'         => date('Y-m-d H:i:s'),
                'created_by'         => $this->auth->user_name(),
            ],
        ]);

        // Update counter BUM di database target dan simpan nomor jurnal ke aktual
        $this->db->query("UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'");
        $this->db->update('tr_request_mutasi_aktual', ['jurnal' => $Nomor_JV], ['kd_mutasi_aktual' => $kode_aktual]);

        return ($this->db->affected_rows() >= 0);
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

        // Ambil dan validasi target_accounting dari POST data
        $target_accounting = isset($post['target_accounting']) ? $post['target_accounting'] : '';
        $target = $this->_resolve_target_db($target_accounting);

        if (!$target) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Target Accounting tidak valid!'
            ]);
            return;
        }

        if ($jenis == 'keluar') {
            $kode_mutasi    = $this->Request_mutasi_model->generate_notr($tgl);
        } else {
            $kode_mutasi    = $this->Request_mutasi_model->generate_nokm($tgl);
        }

        // Query COA validation dari target database
        $target_db = $target['connection'];
        $q_bank_asal2   = $target_db->query("SELECT * FROM coa_master WHERE no_perkiraan=?", [$dari]);
        $q_bank_tujuan2 = $target_db->query("SELECT * FROM coa_master WHERE no_perkiraan=?", [$ke]);

        if (!$q_bank_asal2 || !$q_bank_tujuan2) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Database ' . $target['db_name'] . ' tidak dapat diakses.'
            ]);
            return;
        }

        $bank_asal   = $q_bank_asal2->row();
        $bank_tujuan = $q_bank_tujuan2->row();

        if (!$bank_asal) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$dari' tidak ditemukan di database " . $target['db_name'] . "."]);
            return;
        }
        if (!$bank_tujuan) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$ke' tidak ditemukan di database " . $target['db_name'] . "."]);
            return;
        }

        $this->db->trans_begin();
        $data = array(
            'kd_mutasi'         => $kode_mutasi,
            'tgl_request'       => date('Y-m-d', strtotime($post['tgl_request'])),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'mata_uang'         => $post['matauang'],
            'target_accounting' => $target_accounting,
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
            } else {
                $jurnal_status = true; // jenis lain tidak butuh jurnal
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
        if (!$jurnal) return false;

        // Resolve target database dari record
        $target = $this->_resolve_target_db($jurnal->target_accounting);
        if (!$target) {
            return false;
        }
        $db_name = $target['db_name'];

        $Nomor_JV   = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $jurnal->tgl_request, $db_name);

        // Insert ke tabel header jurnal (target database)
        $this->db->insert($db_name . '.jarh', [
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

        // Insert batch ke jurnal (target database)
        $this->db->insert_batch($db_name . '.jurnal', $det_Jurnaltes);

        // Update nomor BUM di tabel cabang (target database)
        $this->db->query("UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'");

        // Update jurnal1 di tr_request_mutasi_admin
        $this->db->query("UPDATE tr_request_mutasi_admin SET jurnal1 = '$Nomor_JV' WHERE kd_mutasi = '$kode_mutasi'");

        return ($this->db->affected_rows() > 0);
    }

    public function save_jurnal_BUK($kode_mutasi)
    {
        $jurnal         = $this->db->query("SELECT * FROM tr_request_mutasi_admin WHERE kd_mutasi='$kode_mutasi'")->row();
        if (!$jurnal) return false;

        // Resolve target accounting database
        $target = $this->_resolve_target_db($jurnal->target_accounting);
        if (!$target) {
            return false;
        }
        $db_name = $target['db_name'];

        $Nomor_JV       = $this->Jurnal_model->get_Nomor_Jurnal_BUK2('101', $jurnal->tgl_request, $db_name);
        $nokir_debet    = ($jurnal->jenis_transaksi == 'bank') ? '1112-01-01' : $jurnal->bank_asal;

        // Insert ke tabel header jurnal (target database)
        $this->db->insert($db_name . '.japh', [
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

        // Insert batch ke jurnal (target database)
        $this->db->insert_batch($db_name . '.jurnal', $det_Jurnaltes);

        // Update nomor BUK di tabel cabang (target database) dan jurnal2 di mutasi admin
        $this->db->query("UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobuk = nobuk + 1 WHERE nocab = '101'");
        $this->db->query("UPDATE tr_request_mutasi_admin SET jurnal2 = '$Nomor_JV' WHERE kd_mutasi = '$kode_mutasi'");

        return ($this->db->affected_rows() > 0);
    }


    /**
     * Printout untuk request mutasi (sebelum realisasi)
     * URL: request_mutasi/printout/{kd_mutasi}
     */
    public function printout($kd_mutasi)
    {
        $data = $this->db->get_where('tr_request_mutasi', ['kd_mutasi' => $kd_mutasi])->row();
        if (!$data) show_404();

        $this->load->view('printout', ['data' => $data]);
    }

    /**
     * Printout untuk realisasi/aktual mutasi
     * URL: request_mutasi/printout_mutasi/{kd_mutasi_aktual}
     */
    public function printout_mutasi($kd_mutasi_aktual)
    {
        $data = $this->db->get_where('tr_request_mutasi_aktual', ['kd_mutasi_aktual' => $kd_mutasi_aktual])->row();
        if (!$data) show_404();

        // Ambil data request asal untuk info tambahan
        $request = $this->db->get_where('tr_request_mutasi', ['kd_mutasi' => $data->kd_mutasi_request])->row();

        $this->load->view('print_mutasi', [
            'data'    => $data,
            'request' => $request,
        ]);
    }

    /**
     * Printout untuk transaksi bank (keluar/masuk)
     * URL: request_mutasi/printout_transaksi/{kd_mutasi}
     */
    public function printout_transaksi($kd_mutasi)
    {
        $data = $this->db->get_where('tr_request_mutasi_admin', ['kd_mutasi' => $kd_mutasi])->row();
        if (!$data) show_404();

        $this->load->view('print_transaksi', ['data' => $data]);
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
