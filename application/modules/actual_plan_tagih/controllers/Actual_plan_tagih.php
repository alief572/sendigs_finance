<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Actual_plan_tagih extends Admin_Controller
{
    protected $viewPermission     = 'Actual_Plan_Tagih.View';
    protected $addPermission      = 'Actual_Plan_Tagih.Add';
    protected $managePermission = 'Actual_Plan_Tagih.Manage';
    protected $deletePermission = 'Actual_Plan_Tagih.Delete';

    protected $consultant;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Actual_plan_tagih/Actual_plan_tagih_model'
        ));
        $this->template->title('Actual_plan_tagih');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Actual Plan Tagih');
        $this->template->render('index');
    }

    public function add_plan_tagih($id_spk)
    {
        $id_spk = urldecode($id_spk);
        $id_spk = str_replace('|', '/', $id_spk);

        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_spk_penawaran a');
        $this->consultant->where('a.id_spk_penawaran', $id_spk);
        $get_spk_penawaran = $this->consultant->get()->row();

        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_spk_penawaran_payment a');
        $this->consultant->where('a.id_spk_penawaran', $id_spk);
        $this->consultant->order_by('a.dibuat_tgl', 'asc');
        $get_top_spk_penawaran = $this->consultant->get()->result();

        $data = [
            'data_spk_penawaran' => $get_spk_penawaran,
            'data_top_spk_penawaran' => $get_top_spk_penawaran
        ];

        $this->template->set($data);
        $this->template->title('Add Plan Tagih');
        $this->template->render('add_plan_tagih');
    }

    public function aktual_tagihan_get()
    {
        $id = $this->input->post('id');

        $get_plan_tagih_detail  = $this->db->get_where('kons_tr_plan_tagih_detail', array('id' => $id))->row();

        $macet = '';
        $tgl_aktual_tagih_last = '';
        $get_actual_plan_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', array('id_detail_plan_tagih' => $id))->result();
        if (!empty($get_actual_plan_tagih) && $get_actual_plan_tagih[0]->tagih_mundur == '3') {
            $macet = '1';
        }

        $this->db->select('tanggal_actual_plan_tagih');
        $this->db->from('kons_tr_actual_plan_tagih');
        $this->db->where('id_detail_plan_tagih', $id);
        $this->db->order_by('created_date', 'desc');
        $this->db->limit(1);
        $get_last_actual = $this->db->get()->row();

        $tgl_actual_plan_tagih_last = (!empty($get_last_actual->tanggal_actual_plan_tagih)) ? $get_last_actual->tanggal_actual_plan_tagih : '';

        $this->template->set('data_plan_tagih_detail', $get_plan_tagih_detail);
        $this->template->set('tgl_actual_tagih_last', $tgl_actual_plan_tagih_last);
        $this->template->set('macet', $macet);
        $this->template->render('form_actual_plan_tagih');
    }

    public function aktual_tagihan_macet_get()
    {
        $id = $this->input->post('id');

        $get_plan_tagih_detail  = $this->db->get_where('kons_tr_plan_tagih_detail', array('id' => $id))->row();

        $macet = '';


        $this->template->set('data_plan_tagih_detail', $get_plan_tagih_detail);
        $this->template->set('macet', $macet);
        $this->template->render('form_actual_plan_tagih_macet');
    }

    public function save_actual_plan_tagih()
    {
        $post = $this->input->post();

        $file_surat_mundur = '';
        if (!empty($_FILES['upload_surat_mundur'])) {
            $config['upload_path']   = './uploads/surat_mundur';
            $config['allowed_types'] = '*';
            $config['max_size']      = 999999999999; // In KB
            $config['encrypt_name']  = TRUE; // Optional: encrypt the filename
            $config['remove_spaces']  = TRUE; // Optional: encrypt the filename

            $this->load->library('upload', $config);

            $this->upload->initialize($config);
            if ($this->upload->do_upload('upload_surat_mundur')) {
                $uploadData = $this->upload->data();
                $file_surat_mundur = 'uploads/surat_mundur/' . $uploadData['file_name'];
            }
            // else {
            //     print_r('surat_mundur - ' . $this->upload->display_errors());
            //     exit;
            // }
        }

        $file_laporan_progress = '';
        if (!empty($_FILES['upload_laporan_progress']['filename'])) {
            $config2['upload_path']   = './uploads/laporan_progress';
            $config2['allowed_types'] = '*';
            $config2['max_size']      = 999999999999; // In KB
            $config2['encrypt_name']  = TRUE; // Optional: encrypt the filename 
            $config2['remove_spaces']  = TRUE; // Optional: encrypt the filename 

            $this->load->library('upload', $config2);

            $this->upload->initialize($config2);
            if ($this->upload->do_upload('upload_laporan_progress')) {
                $uploadData2 = $this->upload->data();
                $file_laporan_progress = 'uploads/laporan_progress/' . $uploadData2['file_name'];
            }
            // else {
            //     print_r('laporan progress - ' . $this->upload->display_errors());
            //     exit;
            // }
        }

        $this->db->trans_begin();

        try {
            if ($post['macet'] == '1') {
                $arr_update = [
                    'tgl_actual_plan_tagih' => $post['tgl_plan_tagih'],
                    'tagih_mundur' => $post['tagih_mundur'],
                    'alasan_mundur' => '',
                    'file_surat_mundur' => '',
                    'file_laporan_progress' => $file_laporan_progress,
                    'macet' => 1
                ];
                $update_actual_plan_tagih = $this->db->update('kons_tr_actual_plan_tagih', $arr_update, array('id_detail_plan_tagih' => $post['id_detail_plan_tagih']));

                $arr_update_plan_tagih = [
                    'tgl_aktual_plan_tagih' => $post['tgl_plan_tagih'],
                    'status_terakhir' => $post['tagih_mundur']
                ];
                $update_plan_tagih_detail = $this->db->update('kons_tr_plan_tagih_detail', $arr_update_plan_tagih, ['id' => $post['id_detail_plan_tagih']]);
            } else {
                $id = $this->Actual_plan_tagih_model->generate_id();

                if ($post['tagih_mundur'] == '1') {
                    $tanggal_actual = $post['tanggal_actual'];
                } else {
                    if (empty($post['tanggal_actual'])) {
                        throw new Exception('Mohon pilih tanggal rencana penagihan untuk status "Mundur"!');
                    }
                    $tanggal_actual = $post['tanggal_actual'];
                }

                $arr_insert = [
                    'id' => $id,
                    'id_detail_plan_tagih' => $post['id_detail_plan_tagih'],
                    'id_top' => $post['id_top'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'term_payment' => $post['term_payment'],
                    'persen_payment' => $post['persen_payment'],
                    'nominal_payment' => $post['nominal_payment'],
                    'desc_payment' => $post['desc_payment'],
                    'tgl_plan_tagih' => $post['tgl_plan_tagih'],
                    'urutan' => $post['urutan'],
                    'tanggal_actual_plan_tagih' => $tanggal_actual,
                    'tagih_mundur' => $post['tagih_mundur'],
                    'alasan_mundur' => $post['alasan_mundur'],
                    'file_surat_mundur' => $file_surat_mundur,
                    'file_laporan_progress' => $file_laporan_progress,
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];

                $insert_actual_plan = $this->db->insert('kons_tr_actual_plan_tagih', $arr_insert);
                if (!$insert_actual_plan) {
                    throw new Exception('Maaf, data gagal di proses !');
                }

                $arr_update_plan_tagih = [
                    'tgl_aktual_plan_tagih' => $tanggal_actual,
                    'status_terakhir' => $post['tagih_mundur']
                ];
                $update_plan_tagih_detail = $this->db->update('kons_tr_plan_tagih_detail', $arr_update_plan_tagih, ['id' => $post['id_detail_plan_tagih']]);

                // print_r($this->db->last_query());
                // exit;
            }

            $this->db->trans_commit();

            $this->output->set_status_header(200);
            $response = [
                'status' => '1',
                'msg' => 'Data saved successfully !'
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->output->set_status_header(500);

            echo json_encode([
                'msg' => $e->getMessage()
            ]);
        }

        // if ($this->db->trans_status() === false) {
        //     $this->db->trans_rollback();

        //     $valid = 0;
        //     $msg = 'Please try again later !';
        // } else {
        //     $this->db->trans_commit();

        //     $valid = 1;
        //     $msg = 'Data saved succesfully !';
        // }

        // echo json_encode([
        //     'status' => $valid,
        //     'msg' => $msg
        // ]);
    }

    public function save_actual_plan_tagih_macet()
    {
        $post = $this->input->post();

        $file_laporan_progress = '';
        if (!empty($_FILES['upload_laporan_progress'])) {
            $config2['upload_path']   = './uploads/laporan_progress';
            $config2['allowed_types'] = '*';
            $config2['max_size']      = 999999999999; // In KB
            $config2['encrypt_name']  = TRUE; // Optional: encrypt the filename 
            $config2['remove_spaces']  = TRUE; // Optional: encrypt the filename 

            $this->load->library('upload', $config2);

            $this->upload->initialize($config2);
            if ($this->upload->do_upload('upload_laporan_progress')) {
                $uploadData2 = $this->upload->data();
                $file_laporan_progress = 'uploads/laporan_progress/' . $uploadData2['file_name'];
            } else {
                print_r('laporan progress - ' . $this->upload->display_errors());
                exit;
            }
        }

        $this->db->trans_begin();

        $arr_update = [
            'tanggal_actual_plan_tagih' => $post['tanggal_actual'],
            'tagih_mundur' => $post['tagih_mundur'],
            'alasan_mundur' => '',
            'file_surat_mundur' => '',
            'file_laporan_progress' => $file_laporan_progress
        ];
        $update_actual_plan_tagih = $this->db->update('kons_tr_actual_plan_tagih', $arr_update, array('id_detail_plan_tagih' => $post['id_detail_plan_tagih']));
        if (!$update_actual_plan_tagih) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'Data saved succesfully !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function download_excel($tahun = null, $status = null)
    {
        $data = [
            'list_data' => $this->Actual_plan_tagih_model->dataDownloadExcel($tahun, $status),
            'tahun' => $tahun
        ];

        $this->load->view('download_excel', $data);
    }

    public function update_actual_plan_tagih()
    {
        $this->db->select('a.*');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->where('a.status_terakhir', '1');
        $get_plan_tagih_detail = $this->db->get()->result();

        $arr_update_actual = [];

        foreach ($get_plan_tagih_detail as $item_detail) {
            $this->db->select('COUNT(a.id) as jumlah_mundur');
            $this->db->from('kons_tr_actual_plan_tagih a');
            $this->db->where('a.id_detail_plan_tagih', $item_detail->id);
            $this->db->where('a.tagih_mundur', '2');
            $get_jumlah_mundur = $this->db->get()->row();

            if ($get_jumlah_mundur->jumlah_mundur > 0) {
                $this->db->select('a.*');
                $this->db->from('kons_tr_actual_plan_tagih a');
                $this->db->where('a.id_detail_plan_tagih', $item_detail->id);
                $this->db->order_by('a.created_date', 'desc');
                $this->db->limit(1);
                $get_actual_plan_tagih_last = $this->db->get()->row();

                $this->db->select('a.*');
                $this->db->from('kons_tr_actual_plan_tagih a');
                $this->db->where('a.id_detail_plan_tagih', $item_detail->id);
                $this->db->order_by('a.created_date', 'desc');
                $this->db->limit(1, 1);
                $get_actual_plan_tagih_before_last = $this->db->get()->row();

                if ($get_actual_plan_tagih_before_last->tagih_mundur == '2' && $get_actual_plan_tagih_before_last->tanggal_actual_plan_tagih > $get_actual_plan_tagih_last->tanggal_actual_plan_tagih) {
                    $arr_update_actual[] = [
                        'id' => $item_detail->id,
                        'tanggal_actual_plan_tagih' => $get_actual_plan_tagih_before_last->tanggal_actual_plan_tagih
                    ];
                }
            }
        }

        if (!empty($arr_update_actual)) {
            $this->db->update_batch('kons_tr_plan_tagih_detail', $arr_update_actual, 'id');
        }
    }

    public function get_actual_plan_tagih()
    {
        $this->Actual_plan_tagih_model->get_actual_plan_tagih();
    }

    /**
     * Batch process all qualifying "Waiting" records to "Tagih" status
     * with automatic invoice and journal creation.
     *
     * @return void Outputs JSON response
     */
    public function batch_process_tagih()
    {
        $start_time = microtime(true);

        // Permission check for AJAX request — return 403 JSON if denied
        if (!$this->auth->has_permission($this->managePermission)) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => 'error',
                'message' => 'Access denied. Actual_Plan_Tagih.Manage permission required.'
            ]));
            return;
        }

        try {

            // Load Invoicing model from the invoicing module
            $this->load->model('invoicing/Invoicing_model', 'Invoicing_model');

            // Retrieve qualifying records
            $records = $this->Actual_plan_tagih_model->get_batch_records(2019, 2025);

            // If no records found, return early with appropriate message
            if (empty($records)) {
                $this->output->set_content_type('application/json')->set_output(json_encode([
                    'status' => 'success',
                    'total_found' => 0,
                    'total_success' => 0,
                    'total_failed' => 0,
                    'failed_records' => [],
                    'duration_seconds' => round(microtime(true) - $start_time, 2),
                    'message' => 'No qualifying records found for batch processing.'
                ]));
                return;
            }

            // Log batch start
            log_message('info', sprintf(
                'Batch Process Tagih started: timestamp=%s, user_id=%s, year_range=2019-2025, total_records=%d',
                date('Y-m-d H:i:s'),
                $this->auth->user_id(),
                count($records)
            ));

            // Initialize counters
            $total_found = count($records);
            $success_count = 0;
            $failed_count = 0;
            $failed_records = [];
            $macet_excluded = 0;
            $offset = 0;

            // Per-record processing loop
            foreach ($records as $record) {
                $this->db->trans_begin();

                try {
                    // Re-check record status (race condition guard)
                    $fresh_record = $this->Actual_plan_tagih_model->get_record_for_processing($record->id);

                    if ($fresh_record === null) {
                        $this->db->trans_rollback();
                        $macet_excluded++;
                        continue;
                    }

                    // Generate actual_plan_tagih ID with incrementing offset
                    $offset++;
                    $id = $this->Actual_plan_tagih_model->generate_id($offset);

                    // Insert into kons_tr_actual_plan_tagih
                    $arr_insert = [
                        'id' => $id,
                        'id_detail_plan_tagih' => $record->id,
                        'id_top' => $record->id_top,
                        'id_spk_penawaran' => $record->id_spk_penawaran,
                        'id_penawaran' => $record->id_penawaran,
                        'term_payment' => $record->term_payment,
                        'persen_payment' => $record->persen_payment,
                        'nominal_payment' => $record->nominal_payment,
                        'desc_payment' => $record->desc_payment,
                        'tgl_plan_tagih' => $record->tgl_plan_tagih,
                        'urutan' => $record->urutan,
                        'tanggal_actual_plan_tagih' => date('Y-m-d'),
                        'tagih_mundur' => '1',
                        'alasan_mundur' => '',
                        'file_surat_mundur' => '',
                        'file_laporan_progress' => '',
                        'sts_invoice' => 0,
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];

                    $insert_result = $this->db->insert('kons_tr_actual_plan_tagih', $arr_insert);
                    if (!$insert_result) {
                        throw new Exception('Database insert failed for actual_plan_tagih');
                    }

                    // Update kons_tr_plan_tagih_detail status
                    $arr_update = [
                        'status_terakhir' => '1',
                        'tgl_aktual_plan_tagih' => date('Y-m-d')
                    ];
                    $update_result = $this->db->update('kons_tr_plan_tagih_detail', $arr_update, ['id' => $record->id]);
                    if (!$update_result) {
                        throw new Exception('Database update failed for plan_tagih_detail');
                    }

                    // Invoice and journal creation
                    if ($arr_insert['sts_invoice'] != 1) {
                        // Generate invoice ID
                        $invoice_id = $this->Invoicing_model->generate_id();

                        // Calculate financial values
                        $nominal = $record->nominal_payment;
                        $dpp = $nominal * 11 / 12;
                        $ppn = $dpp * 12 / 100;
                        $pph = $nominal * 0.5 / 100;
                        $total_akhir = $nominal + $ppn - $pph;

                        // Insert into tr_invoicing
                        $arr_invoice = [
                            'id' => $invoice_id,
                            'id_actual_plan_tagih' => $id,
                            'id_detail_plan_tagih' => $record->id,
                            'id_penawaran' => $record->id_penawaran,
                            'id_spk_penawaran' => $record->id_spk_penawaran,
                            'id_customer' => $record->id_customer,
                            'nm_customer' => $record->nm_customer,
                            'address' => $record->address,
                            'id_project' => $record->id_project,
                            'nm_project' => $record->nm_project,
                            'id_project_leader' => $record->id_project_leader,
                            'nm_project_leader' => $record->nm_project_leader,
                            'id_sales' => $record->id_sales,
                            'nm_sales' => $record->nm_sales,
                            'tanggal_invoice' => date('Y-m-d'),
                            'no_invoice' => '',
                            'total_nominal' => $nominal,
                            'dpp_nilai_lain' => $dpp,
                            'ppn_jurnal' => $ppn,
                            'pph_jurnal' => $pph,
                            'total_akhir_jurnal' => $total_akhir,
                            'saldo_piutang' => $total_akhir,
                            'created_by' => $this->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        ];

                        $invoice_result = $this->db->insert('tr_invoicing', $arr_invoice);
                        if (!$invoice_result) {
                            throw new Exception('Invoice creation failed');
                        }

                        // Generate 4 journal entry IDs and insert journal entries
                        $journal_entries = [];

                        // Journal 1: COA 1102-01-01 (Piutang Usaha) - Debit = Total Akhir
                        $journal_entries[] = [
                            'no_jurnal' => $this->Invoicing_model->generate_id_invoice_jurnal(1),
                            'tgl_jurnal' => date('Y-m-d'),
                            'coa' => '1102-01-01',
                            'id_company' => '',
                            'nm_company' => $record->nm_company,
                            'nm_coa' => 'Piutang Usaha',
                            'debit' => $total_akhir,
                            'kredit' => 0,
                            'keterangan' => '',
                            'sts' => 0,
                            'no_transaksi' => $invoice_id,
                            'jenis_transaksi' => 'Invoicing',
                            'created_by' => $this->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        ];

                        // Journal 2: COA 2104-01-07 (PPN Keluaran) - Kredit = PPN
                        $journal_entries[] = [
                            'no_jurnal' => $this->Invoicing_model->generate_id_invoice_jurnal(2),
                            'tgl_jurnal' => date('Y-m-d'),
                            'coa' => '2104-01-07',
                            'id_company' => '',
                            'nm_company' => $record->nm_company,
                            'nm_coa' => 'PPN Keluaran',
                            'debit' => 0,
                            'kredit' => $ppn,
                            'keterangan' => '',
                            'sts' => 0,
                            'no_transaksi' => $invoice_id,
                            'jenis_transaksi' => 'Invoicing',
                            'created_by' => $this->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        ];

                        // Journal 3: COA 1106-01-02 (PPh 23 Dibayar Dimuka) - Debit = PPh
                        $journal_entries[] = [
                            'no_jurnal' => $this->Invoicing_model->generate_id_invoice_jurnal(3),
                            'tgl_jurnal' => date('Y-m-d'),
                            'coa' => '1106-01-02',
                            'id_company' => '',
                            'nm_company' => $record->nm_company,
                            'nm_coa' => 'PPh 23 Dibayar Dimuka',
                            'debit' => $pph,
                            'kredit' => 0,
                            'keterangan' => '',
                            'sts' => 0,
                            'no_transaksi' => $invoice_id,
                            'jenis_transaksi' => 'Invoicing',
                            'created_by' => $this->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        ];

                        // Journal 4: COA 4101-01-01 (Pendapatan Jasa Konsultansi) - Kredit = nominal
                        $journal_entries[] = [
                            'no_jurnal' => $this->Invoicing_model->generate_id_invoice_jurnal(4),
                            'tgl_jurnal' => date('Y-m-d'),
                            'coa' => '4101-01-01',
                            'id_company' => '',
                            'nm_company' => $record->nm_company,
                            'nm_coa' => 'Pendapatan Jasa Konsultansi',
                            'debit' => 0,
                            'kredit' => $nominal,
                            'keterangan' => '',
                            'sts' => 0,
                            'no_transaksi' => $invoice_id,
                            'jenis_transaksi' => 'Invoicing',
                            'created_by' => $this->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        ];

                        // Insert all 4 journal entries at once
                        $journal_result = $this->db->insert_batch('tr_jurnal', $journal_entries);
                        if (!$journal_result) {
                            throw new Exception('Journal entries creation failed');
                        }

                        // Update sts_invoice to 1
                        $this->db->update('kons_tr_actual_plan_tagih', ['sts_invoice' => 1], ['id' => $id]);
                    }

                    if ($this->db->trans_status() === false) {
                        throw new Exception('Transaction failed');
                    }

                    $this->db->trans_commit();
                    $success_count++;
                } catch (Exception $e) {
                    $this->db->trans_rollback();
                    $failed_records[] = ['id' => $record->id, 'error' => $e->getMessage()];
                    $failed_count++;
                }
            }

            // Log batch completion
            log_message('info', sprintf(
                'Batch Process Tagih completed: total_found=%d, success=%d, failed=%d, macet_excluded=%d, duration=%.2fs',
                $total_found,
                $success_count,
                $failed_count,
                $macet_excluded,
                microtime(true) - $start_time
            ));

            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => 'success',
                'total_found' => $total_found,
                'total_success' => $success_count,
                'total_failed' => $failed_count,
                'failed_records' => $failed_records,
                'duration_seconds' => round(microtime(true) - $start_time, 2)
            ]));
        } catch (Exception $e) {
            log_message('error', 'Batch Process Tagih system error: ' . $e->getMessage());
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => 'error',
                'message' => 'System error: ' . $e->getMessage()
            ]));
        }
    }
}
