<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alokasi extends Admin_Controller
{
    protected $viewPermission = 'Alokasi.View';
    protected $managePermission = 'Alokasi.Manage';
    protected $addPermission = 'Alokasi.Add';
    protected $deletePermission = 'Alokasi.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Alokasi/Alokasi_model'));
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->db->select('a.*, b.nama_bank');
        $this->db->from('ms_bank a');
        $this->db->join('list_bank b', 'b.id = a.bank');
        $this->db->where('a.deleted', '0');
        $get_bank = $this->db->get()->result_array();

        $data['data_bank'] = $get_bank;
        $this->template->title('Alokasi');
        $this->template->render('index', $data);
    }

    public function upload_rekening_koran()
    {
        $post = $this->input->post();
        $this->load->helper('file');

        if (!empty($_FILES['upload_csv'])) {
            $config['upload_path']   = './uploads/rekening_koran';
            $config['allowed_types'] = '*';
            $config['remove_spaces'] = TRUE;
            $config['encrypt_name'] = TRUE;

            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            if ($this->upload->do_upload('upload_csv')) {
                $fileData = $this->upload->data();
                $filePath = $fileData['full_path'];

                $csvData = array_map("str_getcsv", file($filePath));
                // $header = array_shift($csvData); // remove and store header

                $id_header = $this->Alokasi_model->generate_id();

                $account_no = '';
                $periode_from = '';
                $periode_to = '';
                $saldo_awal = 0;
                $total_debit = 0;
                $total_kredit = 0;
                $total_akhir = 0;

                $arr_detail = [];

                $no = 0;
                foreach ($csvData as $row => $item) {

                    // $insertData = array_combine($header, $item);

                    if ($post['jenis_bank'] == '7') {
                        $no++;
                        if (isset($item[0]) && strpos($item[0], 'No. rekening : ') !== false && $account_no == '') {
                            $account_no = str_replace('No. rekening : ', '', $item[0]);
                        }
                        if (isset($item[0]) && strpos($item[0], 'Periode : ') !== false && $periode_from == '' && $periode_to == '') {
                            $periode_arr = str_replace('Periode : ', '', $item[0]);
                            $split_periode = explode(' - ', $periode_arr);

                            $periode_from = date('Y-m-d', strtotime(str_replace('/', '-', $split_periode[0])));
                            $periode_to = date('Y-m-d', strtotime(str_replace('/', '-', $split_periode[1])));
                        }
                        if (isset($item[0]) && strpos($item[0], 'Saldo Awal : ') !== false && $saldo_awal == '') {
                            $saldo_awal = str_replace('Saldo Awal : ', '', $item[0]);
                            $saldo_awal = str_replace(',', '', $saldo_awal);
                        }
                        if (isset($item[0]) && strpos($item[0], 'Mutasi Debet : ') !== false && $total_debit == '') {
                            $total_debit = str_replace('Mutasi Debet : ', '', $item[0]);
                            $total_debit = str_replace(',', '', $total_debit);
                        }
                        if (isset($item[0]) && strpos($item[0], 'Mutasi Kredit : ') !== false && $total_kredit == '') {
                            $total_kredit = str_replace('Mutasi Kredit : ', '', $item[0]);
                            $total_kredit = str_replace(',', '', $total_kredit);
                        }
                        if (isset($item[0]) && strpos($item[0], 'Saldo Akhir : ') !== false && $total_akhir == '') {
                            $total_akhir = str_replace('Saldo Akhir : ', '', $item[0]);
                            $total_akhir = str_replace(',', '', $total_akhir);
                        }

                        if ($no >= 8) {
                            if (strtotime(str_replace('/', '-', $item[0])) === false) {
                                $tanggal_transaksi = $item[0];
                            } else {
                                $tanggal_transaksi = date('Y-m-d', strtotime(str_replace('/', '-', $item[0])));
                            }

                            $debit = 0;
                            $kredit = 0;

                            if (isset($item[3]) && strpos($item[3], 'CR') !== false) {
                                $debit = str_replace('CR', '', $item[3]);
                                $debit = str_replace(',', '', $item[3]);
                                $debit = floatval($debit);
                            } else {
                                if (isset($item[3])) {
                                    $kredit = str_replace('DB', '', $item[3]);
                                    $kredit = str_replace(',', '', $item[3]);
                                    $kredit = floatval($kredit);
                                }
                            }

                            $saldo = 0;

                            if (isset($item[4])) {
                                $saldo = str_replace(',', '', $item[4]);
                                $saldo = floatval($saldo);
                            }

                            if ($debit > 0 || $kredit > 0) {
                                $arr_detail[] = [
                                    'id_header' => $id_header,
                                    'tipe_bank' => $post['bank'],
                                    'jenis_bank' => $post['jenis_bank'],
                                    'tanggal_transaksi' => $tanggal_transaksi,
                                    'keterangan' => $item[1],
                                    'cabang' => $item[2],
                                    'nominal_debit' => $debit,
                                    'nominal_kredit' => $kredit,
                                    'saldo' => $saldo,
                                    'reference_no' => '',
                                    'cheque_no' => '',
                                    'status_approval' => 'pending',
                                    'created_by' => $this->auth->user_id(),
                                    'created_date' => date('Y-m-d H:i:s')
                                ];
                            }
                        }
                        // print_r($account_no);
                    }

                    if ($post['jenis_bank'] == '22') {
                        $no++;

                        // print_r($no . '<br>');
                        if ($no == '2') {
                            $saldo_awal = str_replace(',', '', $item[7]);
                            $saldo_awal = floatval($saldo_awal);
                        }
                        if (strpos($item[0], 'PERIOD :') !== false && $periode_from == '') {
                            $split_periode = str_replace('/', '-', $item[1]);
                            $split_periode = explode(' - ', $split_periode);

                            $periode_from = date('Y-m-d', strtotime($split_periode[0]));
                            $periode_to = date('Y-m-d', strtotime($split_periode[1]));
                        }
                        if (strpos($item[0], 'ACCOUNT NO :') !== false && $account_no == '') {
                            $account_no = $item[1];
                        }
                        if (strpos($item[0], 'TOTAL DEBIT :') !== false && $total_kredit == 0) {
                            $total_kredit = str_replace(',', '', $item[2]);
                            $total_kredit = floatval($total_kredit);
                        }
                        if (strpos($item[0], 'TOTAL CREDIT :') !== false && $total_debit == 0) {
                            $total_debit = str_replace(',', '', $item[2]);
                            $total_debit = floatval($total_debit);
                        }
                        if (isset($item[6]) && strpos($item[6], 'CLOSING BALANCE :') !== false && $total_akhir == 0) {
                            $total_akhir = str_replace(',', '', $item[7]);
                            $total_akhir = floatval($total_akhir);
                        }

                        if ($no >= 11) {
                            $tanggal_transaksi = str_replace('/', '-', $item[0]);
                            $tanggal_transaksi = date('Y-m-d', strtotime($tanggal_transaksi));

                            $arr_detail[] = [
                                'id_header' => $id_header,
                                'tipe_bank' => $post['bank'],
                                'jenis_bank' => $post['jenis_bank'],
                                'tanggal_transaksi' => $tanggal_transaksi,
                                'keterangan' => $item[4],
                                'cabang' => '',
                                'nominal_debit' => str_replace(',', '', $item[6]),
                                'nominal_kredit' => str_replace(',', '', $item[5]),
                                'saldo' => str_replace(',', '', $item[7]),
                                'reference_no' => str_replace("'", "", $item[2]),
                                'cheque_no' => str_replace('', '', $item[3]),
                                'status_approval' => 'pending',
                                'created_by' => $this->auth->user_id(),
                                'created_date' => date('Y-m-d H:i:s')
                            ];
                        }
                    }

                    // Example insert (adjust for your table and field names)
                    // $this->db->insert('your_table', $insertData);
                }

                // exit;

                $arr_header = [
                    'id' => $id_header,
                    'tipe_bank' => $post['bank'],
                    'jenis_bank' => $post['jenis_bank'],
                    'account_no' => $account_no,
                    'tanggal_transaksi_from' => $periode_from,
                    'tanggal_transaksi_to' => $periode_to,
                    'saldo_awal' => $saldo_awal,
                    'total_debit' => $total_debit,
                    'total_credit' => $total_kredit,
                    'saldo_akhir' => $total_akhir,
                    'status_alokasi' => 1,
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];

                $this->db->trans_begin();

                $insert_header = $this->db->insert('tr_alokasi', $arr_header);
                if (!$insert_header) {
                    $this->db->trans_rollback();

                    print_r($this->db->error($insert_header));
                    exit;
                }

                foreach ($arr_detail as $detail) {
                    $insert_det = $this->db->insert('tr_alokasi_detail', $detail);
                    if (!$insert_det) {
                        $this->db->trans_rollback();
                        print_r($this->db->error($insert_det));
                        exit;
                    }
                    $detail_id = $this->db->insert_id();

                    $log_desc = 'Upload rekening koran: ' . $detail['keterangan'] . 
                                ' (Debit: Rp. ' . number_format($detail['nominal_debit'], 2) . 
                                ', Kredit: Rp. ' . number_format($detail['nominal_kredit'], 2) . ')';
                    $log_data = [
                        'id_alokasi_detail' => $detail_id,
                        'action' => 'UPLOAD_REKENING',
                        'deskripsi_log' => $log_desc,
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                    $insert_log = $this->db->insert('log_alokasi_history', $log_data);
                    if (!$insert_log) {
                        $this->db->trans_rollback();
                        print_r($this->db->error($insert_log));
                        exit;
                    }
                }

                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();

                    $valid = 0;
                    $msg = 'Upload rekening koran error !';
                } else {
                    $this->db->trans_commit();

                    $valid = 1;
                    $msg = 'Upload rekening koran berhasil !';
                }

                echo json_encode([
                    'status' => $valid,
                    'msg' => $msg,
                    'id_header' => $id_header
                ]);
            } else {
                print_r($this->upload->display_errors());
                exit;
            }
        }
    }

    public function get_bank_sesuai_alokasi()
    {
        $bank = $this->input->post('bank');

        $query = '
            SELECT 
                sub.*
            FROM
                (
                    SELECT 
                        aa.*
                    FROM tr_alokasi_detail aa
                    WHERE
                        aa.tipe_bank = "' . $bank . '"
                    ORDER BY aa.id DESC
                    LIMIT 3
                ) AS sub
            ORDER BY sub.id ASC
        ';

        $sql = $this->db->query($query)->result_array();

        $hasil = '';

        foreach ($sql as $item) {
            $hasil .= '<tr>';

            $tanggal_transaksi = date('d F Y', strtotime($item['tanggal_transaksi']));
            if ($item['tanggal_transaksi'] == '0000-00-00') {
                $tanggal_transaksi = 'PEND';
            }

            $hasil .= '<td class="text-center">' . $tanggal_transaksi . '</td>';
            $hasil .= '<td class="text-center">' . $item['reference_no'] . '</td>';
            $hasil .= '<td class="text-left">' . $item['keterangan'] . '</td>';
            $hasil .= '<td class="text-right">Rp. ' . number_format($item['nominal_kredit'], 2) . '</td>';
            $hasil .= '<td class="text-right">Rp. ' . number_format($item['nominal_debit'], 2) . '</td>';
            $hasil .= '<td class="text-right">Rp. ' . number_format($item['saldo'], 2) . '</td>';

            $hasil .= '</tr>';
        }

        echo $hasil;
    }

    public function get_alokasi_detail()
    {
        $post = $this->input->post();

        $id = $post['id'];

        $this->db->select('a.*');
        $this->db->from('tr_alokasi_detail a');
        $this->db->where('a.id', $id);
        $this->db->order_by('a.id', 'asc');
        $get_data = $this->db->get()->result_array();

        $hasil = [];

        foreach ($get_data as $item) {

            $selected1 = '';
            $selected2 = '';
            $selected3 = '';
            $selected4 = '';
            $selected5 = '';
            $selected6 = '';
            $selected7 = '';

            if ($item['sts'] == '1') {
                $selected1 = 'selected';
            }
            if ($item['sts'] == '2') {
                $selected2 = 'selected';
            }
            if ($item['sts'] == '3') {
                $selected3 = 'selected';
            }
            if ($item['sts'] == '4') {
                $selected4 = 'selected';
            }
            if ($item['sts'] == '5') {
                $selected5 = 'selected';
            }
            if ($item['sts'] == '6') {
                $selected6 = 'selected';
            }
            if ($item['sts'] == '7') {
                $selected7 = 'selected';
            }

            $disabled = '';
            if ($selected1 !== '' || $selected2 !== '' || $selected3 !== '' || $selected4 !== '' || $selected5 !== '' || $selected6 !== '' || $selected7 !== '') {
                $disabled = 'readonly';
            }

            $action = '<select class="form-control form-control-sm" name="action_' . $item['id'] . '" ' . $disabled . '>';
            if ($selected1 !== '') {
                $action .= '<option value="1" ' . $selected1 . '>Penerimaan Piutang</option>';
            } else if ($selected2 !== '') {
                $action .= '<option value="2" ' . $selected2 . '>Unlocated Penerimaan</option>';
            } else if ($selected3 !== '') {
                $action .= '<option value="3" ' . $selected3 . '>Pengembalian Kasbon</option>';
            } else if ($selected4 !== '') {
                $action .= '<option value="4" ' . $selected4 . '>Mutasi</option>';
            } else if ($selected5 !== '') {
                $action .= '<option value="5" ' . $selected5 . '>Transaksi Bank</option>';
            } else if ($selected6) {
                $action .= '<option value="6" ' . $selected6 . '>Pembayaran</option>';
            } else if ($selected7) {
                $action .= '<option value="7" ' . $selected7 . '>Alokasi Kalibrasi</option>';
            } else {
                $action .= '<option value="">- Select Option -</option>';
                $action .= '<option value="1" ' . $selected1 . '>Penerimaan Piutang</option>';
                $action .= '<option value="2" ' . $selected2 . '>Unlocated Penerimaan</option>';
                $action .= '<option value="3" ' . $selected3 . '>Pengembalian Kasbon</option>';
                $action .= '<option value="4" ' . $selected4 . '>Mutasi</option>';
                $action .= '<option value="5" ' . $selected5 . '>Transaksi Bank</option>';
                $action .= '<option value="6" ' . $selected6 . '>Pembayaran</option>';
                $action .= '<option value="7" ' . $selected7 . '>Alokasi Kalibrasi</option>';
            }

            $action .= '</select>';

            $hasil[] = [
                'tanggal_transaksi' => date('d F Y', strtotime($item['tanggal_transaksi'])),
                'reference_no' => $item['reference_no'],
                'description' => $item['keterangan'],
                'credit' => number_format($item['nominal_kredit'], 2),
                'debit' => number_format($item['nominal_debit'], 2),
                'balance' => number_format($item['saldo'], 2),
                'action' => $action
            ];
        }

        echo json_encode([
            'id' => $id,
            'data' => $hasil
        ]);
    }

    public function save_alokasi()
    {
        $post = $this->input->post();

        $this->db->select('a.*');
        $this->db->from('tr_alokasi_detail a');
        $this->db->where('a.id', $post['id']);
        $get_alokasi_detail = $this->db->get()->result_array();

        $arr_detail = [];

        foreach ($get_alokasi_detail as $item) {

            $sts = 0;
            if (isset($post['action_' . $item['id']])) {
                $sts = $post['action_' . $item['id']];
            }

            if ($sts !== '' && $sts !== '' && $sts !== '0') {
                $arr_detail[] = [
                    'id' => $item['id'],
                    'sts' => $sts
                ];
            }
        }

        $this->db->update_batch('tr_alokasi_detail', $arr_detail, 'id');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $json = [
                'status' => 0,
                'msg' => 'Save Failed !'
            ];
        } else {
            $this->db->trans_commit();

            $this->Alokasi_model->update_alokasi_header($post['id']);
            $json = [
                'status' => 1,
                'msg' => 'Save Success !'
            ];
        }

        echo json_encode($json);
    }

    public function get_jenis_bank()
    {
        $bank = $this->input->post('bank');

        $get_bank = $this->db->get_where('ms_bank', ['id' => $bank])->row_array();

        $response = [
            'jenis_bank' => $get_bank['bank']
        ];

        echo json_encode($response);
    }

    public function get_alokasi()
    {
        $this->Alokasi_model->get_alokasi();
    }

    public function get_alokasi_split_detail()
    {
        $id = $this->input->post('id');

        $data = $this->Alokasi_model->get_transaction_for_split($id);

        if (!$data) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Data transaksi tidak ditemukan'
            ]);
            return;
        }

        $tanggal_transaksi = date('d F Y', strtotime($data['tanggal_transaksi']));
        if ($data['tanggal_transaksi'] == '0000-00-00') {
            $tanggal_transaksi = 'PEND';
        }

        echo json_encode([
            'status' => 1,
            'id' => $data['id'],
            'tanggal_transaksi' => $tanggal_transaksi,
            'keterangan' => $data['keterangan'],
            'nominal_kredit' => floatval($data['nominal_kredit']),
            'nominal_debit' => floatval($data['nominal_debit']),
            'saldo' => floatval($data['saldo']),
            'reference_no' => $data['reference_no']
        ]);
    }

    public function save_split_alokasi()
    {
        $id = $this->input->post('id');
        $splits = $this->input->post('splits');

        // Validate id not empty
        if (empty($id)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'ID transaksi tidak boleh kosong'
            ]);
            return;
        }

        // Validate splits not empty
        if (empty($splits) || !is_array($splits)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Data alokasi tidak boleh kosong'
            ]);
            return;
        }

        // Validate each split row
        foreach ($splits as $split) {
            $jenis = isset($split['jenis_alokasi']) ? intval($split['jenis_alokasi']) : 0;
            $nominal = isset($split['nominal']) ? floatval($split['nominal']) : 0;

            if ($jenis < 1 || $jenis > 7) {
                echo json_encode([
                    'status' => 0,
                    'msg' => 'Semua baris harus memiliki jenis alokasi'
                ]);
                return;
            }

            if ($nominal <= 0) {
                echo json_encode([
                    'status' => 0,
                    'msg' => 'Nominal harus lebih besar dari 0'
                ]);
                return;
            }
        }

        // Get transaction to validate sum
        $transaction = $this->Alokasi_model->get_transaction_for_split($id);

        if (!$transaction) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Data transaksi tidak ditemukan'
            ]);
            return;
        }

        // Determine transaction nominal (kredit or debit)
        $nominal_transaksi = ($transaction['nominal_kredit'] > 0)
            ? floatval($transaction['nominal_kredit'])
            : floatval($transaction['nominal_debit']);

        // Validate sum of splits equals transaction total
        $total_split = 0;
        foreach ($splits as $split) {
            $total_split += floatval($split['nominal']);
        }

        if (abs($total_split - $nominal_transaksi) > 0.01) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Total nominal harus sama dengan total transaksi'
            ]);
            return;
        }

        // Call model to save
        $user_id = $this->auth->user_id();
        $result = $this->Alokasi_model->save_split_alokasi($id, $splits, $user_id);

        if ($result) {
            // Double-check: ensure sts is set to 8
            $this->db->query("UPDATE tr_alokasi_detail SET sts = '8' WHERE id = ?", array($id));

            echo json_encode([
                'status' => 1,
                'msg' => 'Save Success !'
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'msg' => 'Penyimpanan gagal, silakan coba lagi'
            ]);
        }
    }

    public function get_view_split_alokasi()
    {
        $id = $this->input->post('id');

        if (empty($id)) {
            echo json_encode(['status' => 0, 'msg' => 'ID tidak valid']);
            return;
        }

        // Get transaction detail
        $this->db->select('id, tanggal_transaksi, keterangan, nominal_kredit, nominal_debit, saldo, reference_no');
        $this->db->from('tr_alokasi_detail');
        $this->db->where('id', $id);
        $transaction = $this->db->get()->row_array();

        if (!$transaction) {
            echo json_encode(['status' => 0, 'msg' => 'Data transaksi tidak ditemukan']);
            return;
        }

        // Get split records
        $split_data = $this->db->get_where('tr_alokasi_split', ['id_alokasi_detail' => $id])->result_array();

        if (empty($split_data)) {
            echo json_encode(['status' => 0, 'msg' => 'Data split tidak ditemukan']);
            return;
        }

        $jenis_labels = [
            '1' => 'Penerimaan Piutang',
            '2' => 'Unlocated Penerimaan',
            '3' => 'Pengembalian Kasbon',
            '4' => 'Mutasi',
            '5' => 'Transaksi Bank',
            '6' => 'Pembayaran',
            '7' => 'Alokasi Kalibrasi'
        ];

        $splits = [];
        foreach ($split_data as $split) {
            $splits[] = [
                'jenis_alokasi' => isset($jenis_labels[$split['jenis_alokasi']]) ? $jenis_labels[$split['jenis_alokasi']] : '-',
                'nominal' => floatval($split['nominal']),
                'created_date' => $split['created_date']
            ];
        }

        $tanggal_transaksi = date('d F Y', strtotime($transaction['tanggal_transaksi']));
        if ($transaction['tanggal_transaksi'] == '0000-00-00') {
            $tanggal_transaksi = 'PEND';
        }

        echo json_encode([
            'status' => 1,
            'tanggal_transaksi' => $tanggal_transaksi,
            'keterangan' => $transaction['keterangan'],
            'nominal_kredit' => floatval($transaction['nominal_kredit']),
            'nominal_debit' => floatval($transaction['nominal_debit']),
            'saldo' => floatval($transaction['saldo']),
            'reference_no' => $transaction['reference_no'],
            'splits' => $splits
        ]);
    }

    public function review_upload($id_header)
    {
        $id_header = str_replace('-O-', '/', $id_header);
        $this->auth->restrict($this->viewPermission);

        $data['id_header'] = $id_header;
        $this->template->title('Review Upload Alokasi');
        $this->template->render('review_upload', $data);
    }

    public function get_pending_alokasi()
    {
        $id_header = $this->input->post('id_header');
        
        $this->db->select('a.id, a.keterangan, a.tanggal_transaksi, a.nominal_debit, a.nominal_kredit, a.saldo, a.reference_no, b.nama_bank, c.rekening, c.nama');
        $this->db->from('tr_alokasi_detail a');
        $this->db->join('list_bank b', 'b.id = a.jenis_bank', 'left');
        $this->db->join('ms_bank c', 'c.id = a.tipe_bank', 'left');
        $this->db->where('a.id_header', $id_header);
        $this->db->where('a.status_approval', 'pending');
        
        $get_data = $this->db->get()->result_array();

        $hasil = [];
        $no = 0;
        foreach ($get_data as $item) {
            $no++;
            $tanggal_transaksi = date('d-F-Y', strtotime($item['tanggal_transaksi']));
            if ($item['tanggal_transaksi'] == '0000-00-00') {
                $tanggal_transaksi = 'PEND';
            }

            $hasil[] = [
                'id' => $item['id'],
                'no' => $no,
                'tanggal_transaksi' => $tanggal_transaksi,
                'bank' => $item['nama_bank'] . ' - ' . $item['rekening'] . ' - ' . $item['nama'],
                'keterangan' => $item['keterangan'],
                'reference_no' => $item['reference_no'],
                'debit' => number_format($item['nominal_debit'], 2),
                'kredit' => number_format($item['nominal_kredit'], 2),
                'saldo' => number_format($item['saldo'], 2)
            ];
        }

        echo json_encode(['data' => $hasil]);
    }

    public function approve_data()
    {
        $ids = $this->input->post('ids');
        $id_header = $this->input->post('id_header');

        if (!empty($ids) && is_array($ids)) {
            $this->db->where_in('id', $ids);
            $this->db->update('tr_alokasi_detail', ['status_approval' => 'approved']);
        } else if (!empty($id_header)) {
            $this->db->where('id_header', $id_header);
            $this->db->where('status_approval', 'pending');
            $this->db->update('tr_alokasi_detail', ['status_approval' => 'approved']);
        }

        echo json_encode(['status' => 1, 'msg' => 'Data berhasil diapprove!']);
    }

    public function delete_pending_data()
    {
        $ids = $this->input->post('ids');
        $id_header = $this->input->post('id_header');

        if (!empty($ids) && is_array($ids)) {
            $this->db->where_in('id', $ids);
            $this->db->delete('tr_alokasi_detail');
        } else if (!empty($id_header)) {
            $this->db->where('id_header', $id_header);
            $this->db->where('status_approval', 'pending');
            $this->db->delete('tr_alokasi_detail');
            
            // Delete header if all details are deleted
            $this->db->where('id_header', $id_header);
            $count = $this->db->count_all_results('tr_alokasi_detail');
            if ($count == 0) {
                $this->db->where('id', $id_header);
                $this->db->delete('tr_alokasi');
            }
        }

        echo json_encode(['status' => 1, 'msg' => 'Data berhasil dihapus!']);
    }

    public function update_pending_data()
    {
        $id = $this->input->post('id');
        $keterangan = $this->input->post('keterangan');
        $nominal_debit = $this->input->post('nominal_debit');
        $nominal_kredit = $this->input->post('nominal_kredit');
        $reference_no = $this->input->post('reference_no');

        $data = [
            'keterangan' => $keterangan,
            'nominal_debit' => str_replace(',', '', $nominal_debit),
            'nominal_kredit' => str_replace(',', '', $nominal_kredit),
            'reference_no' => $reference_no
        ];

        $this->db->where('id', $id);
        $this->db->update('tr_alokasi_detail', $data);

        echo json_encode(['status' => 1, 'msg' => 'Data berhasil direvisi!']);
    }
}
