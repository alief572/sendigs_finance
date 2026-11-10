<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Plan_tagih extends Admin_Controller
{
    protected $viewPermission     = 'Plan_Tagih.View';
    protected $addPermission      = 'Plan_Tagih.Add';
    protected $managePermission = 'Plan_Tagih.Manage';
    protected $deletePermission = 'Plan_Tagih.Delete';

    protected $consultant;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Plan_tagih/Plan_tagih_model'
        ));
        $this->template->title('Plan_tagih');
        $this->template->page_icon('fa fa-building-o');

        $this->consultant = $this->load->database('consultant', true);
    }

    // =========================================================================
    // AUDIT TRAIL
    // =========================================================================

    private function beginAudit($method, $data = array(), $keys = array())
    {
        if (isset($this->load) && method_exists($this->load, 'library')) {
            $this->load->library('Module_audit_recorder');
        }
        if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'begin')) {
            return $this->module_audit_recorder->begin('plan_tagih', $method,
                array('data' => $this->auditSafeData($data)),
                array_unique(array_merge(array_keys($data), $keys, array('reason', 'outcome'))));
        }
        return false;
    }

    private function completeAudit($message, $status, $data = array())
    {
        if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'complete')) {
            return $this->module_audit_recorder->complete($message, $status,
                array('data' => $this->auditSafeData($data), 'sql' => null));
        }
        return false;
    }

    // Hanya scalar; ID dokumen/SPK dibuang bila bentuknya tidak wajar.
    private function auditSafeData($data)
    {
        $safe = array();
        foreach ($data as $key => $value) {
            // ID asli berformat 0001/PLN-TGH/XI/26, jadi '/' harus lolos.
            if (in_array($key, array('id', 'id_header', 'id_spk_penawaran'), true) && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9\/_.-]{1,100}$/', (string) $value))) { continue; }
            if (is_scalar($value) || $value === null) {
                $safe[$key] = is_string($value) ? mb_substr($value, 0, 512, 'UTF-8') : $value;
            }
        }
        return $safe;
    }

    // Catat penolakan akses sebelum restrict() mengalihkan/menghentikan request.
    private function auditRestrict($permission)
    {
        if (!has_permission($permission)) {
            $this->completeAudit('Akses plan tagih ditolak', 0, array('reason' => 'permission_denied', 'outcome' => 'denied'));
        }
        $this->auth->restrict($permission);
    }

    public function index()
    {
        $this->beginAudit(__FUNCTION__, array(), array('outcome'));
        $this->auditRestrict($this->viewPermission);

        $this->template->title('Plan Tagih');
        $this->template->render('index');
        $this->completeAudit('Membuka daftar plan tagih', 1, array('outcome' => 'rendered'));
    }

    public function add_plan_tagih($id_spk)
    {
        $this->beginAudit(__FUNCTION__, array(), array('id_spk_penawaran', 'outcome'));
        $id_spk = urldecode($id_spk);
        $id_spk = str_replace('|', '/', $id_spk);

        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_spk_penawaran a');
        $this->consultant->where('a.id_spk_penawaran', $id_spk);
        $get_spk_penawaran = $this->consultant->get()->row();

        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_spk_penawaran_payment a');
        $this->consultant->where('a.id_spk_penawaran', $id_spk);
        $this->consultant->order_by('a.id', 'asc');
        $get_top_spk_penawaran = $this->consultant->get()->result();

        $data = [
            'data_spk_penawaran' => $get_spk_penawaran,
            'data_top_spk_penawaran' => $get_top_spk_penawaran
        ];

        $this->template->set($data);
        $this->template->title('Add Plan Tagih');
        $this->template->render('add_plan_tagih');
        $this->completeAudit('Membuka form tambah plan tagih', empty($get_spk_penawaran) ? 0 : 1, array('id_spk_penawaran' => $id_spk, 'outcome' => empty($get_spk_penawaran) ? 'not_found' : 'rendered'));
    }

    public function view_plan_tagih($id_spk)
    {
        $this->beginAudit(__FUNCTION__, array(), array('id_spk_penawaran', 'outcome'));
        $id_spk = urldecode($id_spk);
        $id_spk = str_replace('|', '/', $id_spk);

        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_spk_penawaran a');
        $this->consultant->where('a.id_spk_penawaran', $id_spk);
        $get_spk_penawaran = $this->consultant->get()->row();

        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_spk_penawaran_payment a');
        $this->consultant->where('a.id_spk_penawaran', $id_spk);
        $this->consultant->order_by('a.id', 'asc');
        $get_top_spk_penawaran = $this->consultant->get()->result();

        $this->db->select('a.keterangan_penagihan');
        $this->db->from('kons_tr_plan_tagih_header a ');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_header = $this->db->get()->row();

        $arr_plan_tagih = [];

        $this->db->select('a.tgl_plan_tagih, a.id_top');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_detail = $this->db->get()->result();

        foreach ($get_plan_tagih_detail as $item) {
            $arr_plan_tagih[$item->id_top] = [
                'tgl_plan_tagih' => $item->tgl_plan_tagih
            ];
        }

        $data = [
            'data_spk_penawaran' => $get_spk_penawaran,
            'data_top_spk_penawaran' => $get_top_spk_penawaran,
            'data_plan_tagih_header' => $get_plan_tagih_header,
            'arr_plan_tagih' => $arr_plan_tagih
        ];

        $this->template->set($data);
        $this->template->title('View Plan Tagih');
        $this->template->render('view_plan_tagih');
        $this->completeAudit('Melihat plan tagih', empty($get_spk_penawaran) ? 0 : 1, array('id_spk_penawaran' => $id_spk, 'outcome' => empty($get_spk_penawaran) ? 'not_found' : 'rendered'));
    }

    public function edit_plan_tagih($id_spk)
    {
        $this->beginAudit(__FUNCTION__, array(), array('id_spk_penawaran', 'outcome'));
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

        $this->db->select('a.id, a.keterangan_penagihan');
        $this->db->from('kons_tr_plan_tagih_header a ');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_header = $this->db->get()->row();

        $arr_plan_tagih = [];

        $this->db->select('a.tgl_plan_tagih, a.id, a.id_top');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_detail = $this->db->get()->result();

        foreach ($get_plan_tagih_detail as $item) {
            $arr_plan_tagih[$item->id_top] = [
                'id_plan_tagih' => $item->id,
                'tgl_plan_tagih' => $item->tgl_plan_tagih
            ];
        }

        $data = [
            'data_spk_penawaran' => $get_spk_penawaran,
            'data_top_spk_penawaran' => $get_top_spk_penawaran,
            'data_plan_tagih_header' => $get_plan_tagih_header,
            'arr_plan_tagih' => $arr_plan_tagih
        ];

        $this->template->set($data);
        $this->template->title('Revisi Plan Tagih');
        $this->template->render('revisi_plan_tagih');
        $this->completeAudit('Membuka form revisi plan tagih', empty($get_spk_penawaran) ? 0 : 1, array('id_spk_penawaran' => $id_spk, 'outcome' => empty($get_spk_penawaran) ? 'not_found' : 'rendered'));
    }

    public function save_plan_tagih()
    {
        $this->beginAudit(__FUNCTION__, array(), array('id', 'id_spk_penawaran', 'detail_count', 'outcome'));
        $post = $this->input->post();

        // Validasi: cek apakah SPK sudah punya plan tagih (mencegah duplikasi)
        $existing_header = $this->db->get_where('kons_tr_plan_tagih_header', ['id_spk_penawaran' => $post['id_spk_penawaran']])->row();
        if (!empty($existing_header)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Plan Tagih untuk SPK ini sudah ada! Gunakan fitur Revisi jika ingin mengubah.'
            ]);
            $this->completeAudit('Simpan plan tagih ditolak: sudah ada', 0, array('id_spk_penawaran' => $post['id_spk_penawaran'], 'reason' => 'duplicate', 'outcome' => 'duplicate'));
            return;
        }

        $id = $this->Plan_tagih_model->generate_id();

        $arr_header = [
            'id' => $id,
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'id_customer' => $post['id_customer'],
            'nm_customer' => $post['nm_customer'],
            'id_project' => $post['id_project'],
            'nm_project' => $post['nm_project'],
            'id_project_leader' => $post['id_project_leader'],
            'nm_project_leader' => $post['nm_project_leader'],
            'nilai_bersih_project' => $post['nilai_bersih_project'],
            'keterangan_penagihan' => $post['keterangan_penagihan'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $arr_detail = [];
        $no_detail = 0;
        foreach ($post['dt'] as $item) {
            $no_detail++;
            $data_top = $this->consultant->get_where('kons_tr_spk_penawaran_payment', array('id' => $item['id']))->row();

            if (empty($data_top)) {
                echo json_encode([
                    'status' => 0,
                    'msg' => 'Data Term of Payment tidak ditemukan untuk item ke-' . $no_detail
                ]);
                $this->completeAudit('Simpan plan tagih gagal: TOP tidak ditemukan', 0, array('id_spk_penawaran' => $post['id_spk_penawaran'], 'reason' => 'top_not_found', 'outcome' => 'top_not_found'));
                return;
            }

            $arr_detail[] = [
                'id_spk_penawaran' => $post['id_spk_penawaran'],
                'id_header' => $id,
                'id_penawaran' => $post['id_penawaran'],
                'id_top' => $item['id'],
                'term_payment' => $data_top->term_payment,
                'persen_payment' => $data_top->persen_payment,
                'nominal_payment' => $data_top->nominal_payment,
                'desc_payment' => $data_top->desc_payment,
                'tgl_plan_tagih' => $item['tgl_plan_tagih'],
                'urutan' => $no_detail,
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
        }

        $this->db->trans_begin();
        $auditReason = 'failed';

        try {
            $insert_header = $this->db->insert('kons_tr_plan_tagih_header', $arr_header);
            if (!$insert_header) {
                // throw new Exception('Gagal menyimpan header plan tagih.');

                // Perilaku lama (print_r + exit) dipertahankan; hasil dicatat sebelum keluar.
                $this->completeAudit('Menyimpan plan tagih baru gagal', 0, array('id_spk_penawaran' => $post['id_spk_penawaran'], 'reason' => 'header_failed', 'outcome' => 'failed'));
                print_r($this->db->error($insert_header));
                exit;
            }

            $insert_detail = $this->db->insert_batch('kons_tr_plan_tagih_detail', $arr_detail);
            if (!$insert_detail) {
                $auditReason = 'detail_failed';
                throw new Exception('Gagal menyimpan detail plan tagih.');
            }

            if ($this->db->trans_status() === false) {
                $auditReason = 'transaction_failed';
                throw new Exception('Transaksi gagal, silakan coba lagi.');
            }

            // Sukses ditentukan dari nilai balik commit (CI3), bukan trans_status().
            if ($this->db->trans_commit() === false) {
                $auditReason = 'commit_failed';
                throw new Exception('Transaksi gagal, silakan coba lagi.');
            }

            echo json_encode([
                'status' => 1,
                'msg' => 'New Plan Tagih has been saved !'
            ]);
            $this->completeAudit('Menyimpan plan tagih baru', 1, array('id' => $id, 'id_spk_penawaran' => $post['id_spk_penawaran'], 'detail_count' => count($arr_detail), 'outcome' => 'saved'));
        } catch (Exception $e) {
            $this->db->trans_rollback();

            echo json_encode([
                'status' => 0,
                'msg' => $e->getMessage()
            ]);
            $this->completeAudit('Menyimpan plan tagih baru gagal', 0, array('id_spk_penawaran' => $post['id_spk_penawaran'], 'reason' => $auditReason, 'outcome' => 'failed'));
        }
    }

    public function revisi_plan_tagih()
    {
        $this->beginAudit(__FUNCTION__, array(), array('id_header', 'detail_count', 'outcome'));
        $post = $this->input->post();

        $id_header = $post['id_header'];

        $arr_update_header = [
            'keterangan_penagihan' => $post['keterangan_penagihan']
        ];

        $arr_update_detail = [];
        $no_detail = 0;
        foreach ($post['dt'] as $item) {
            $no_detail++;
            $data_top = $this->consultant->get_where('kons_tr_spk_penawaran_payment', array('id' => $item['id']))->row();

            $arr_update_detail[] = [
                'id' => $item['id_plan_tagih'],
                'tgl_plan_tagih' => $item['tgl_plan_tagih']
            ];
        }

        $this->db->trans_begin();

        $update_header = $this->db->update('kons_tr_plan_tagih_header', $arr_update_header, array('id' => $id_header));
        if (!$update_header) {
            $this->db->trans_rollback();

            // Perilaku lama (print_r + exit) dipertahankan; hasil dicatat sebelum keluar.
            $this->completeAudit('Merevisi plan tagih gagal', 0, array('id_header' => $id_header, 'reason' => 'header_failed', 'outcome' => 'failed'));
            print_r($this->db->last_query());
            exit;
        }

        $update_detail = $this->db->update_batch('kons_tr_plan_tagih_detail', $arr_update_detail, 'id');
        if (!$update_detail) {
            $this->db->trans_rollback();

            $this->completeAudit('Merevisi plan tagih gagal', 0, array('id_header' => $id_header, 'reason' => 'detail_failed', 'outcome' => 'failed'));
            print_r($this->db->last_query());
            exit;
        }

        $auditReason = null;
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $auditReason = 'transaction_failed';
            $valid = 0;
            $msg = 'Please, try again later !';
        } elseif ($this->db->trans_commit() === false) {
            // Sukses ditentukan dari nilai balik commit (CI3), bukan trans_status().
            $this->db->trans_rollback();

            $auditReason = 'commit_failed';
            $valid = 0;
            $msg = 'Please, try again later !';
        } else {
            $valid = 1;
            $msg = 'New Plan Tagih has been updated !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
        $this->completeAudit($valid ? 'Merevisi plan tagih' : 'Merevisi plan tagih gagal', $valid,
            $valid ? array('id_header' => $id_header, 'detail_count' => count($arr_update_detail), 'outcome' => 'revised')
                   : array('id_header' => $id_header, 'reason' => $auditReason, 'outcome' => 'failed'));
    }

    public function updateee()
    {
        $this->beginAudit(__FUNCTION__, array(), array('processed_count', 'outcome'));
        $this->db->trans_begin();

        try {
            $get_data_detail = $this->Plan_tagih_model->get_all_plan_tagih_detail();
            $arr_update = [];

            foreach ($get_data_detail as $item) {
                $get_actual_last = $this->Plan_tagih_model->get_data_last_aktual($item->id);
                if (!empty($get_actual_last)) {
                    $data_invoicing = $this->Plan_tagih_model->get_invoicing($item->id);

                    $tgl_invoice = (!empty($data_invoicing->tanggal_invoice)) ? $data_invoicing->tanggal_invoice : null;

                    $arr_update[] = [
                        'id' => $item->id,
                        'tgl_aktual_plan_tagih' => $get_actual_last->tanggal_actual_plan_tagih,
                        'status_terakhir' => '' . $get_actual_last->tagih_mundur . '',
                        'sts_invoice' => $get_actual_last->sts_invoice,
                        'tgl_invoice' => $tgl_invoice
                    ];
                }
            }

            // update_batch dengan array kosong mengembalikan FALSE di CI3; tidak ada yang disinkronkan bukan kegagalan.
            if (!empty($arr_update) && $this->db->update_batch('kons_tr_plan_tagih_detail', $arr_update, 'id') === false) {
                throw new RuntimeException('update_failed');
            }

            // Sukses ditentukan dari nilai balik commit (CI3), bukan trans_status().
            if ($this->db->trans_commit() === false) {
                throw new RuntimeException('commit_failed');
            }
            http_response_code(200);
            $this->completeAudit('Sinkron status plan tagih', 1, array('processed_count' => count($arr_update), 'outcome' => 'synced'));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            http_response_code(500);
            $reason = in_array($th->getMessage(), array('update_failed', 'commit_failed'), true) ? $th->getMessage() : 'exception';
            $this->completeAudit('Sinkron status plan tagih gagal', 0, array('reason' => $reason, 'outcome' => 'failed'));
        }
    }

    public function get_data_spk()
    {
        $this->beginAudit(__FUNCTION__, array(), array('outcome'));
        $this->Plan_tagih_model->get_data_spk();
        $this->completeAudit('Data SPK plan tagih diambil', 1, array('outcome' => 'rendered'));
    }
}
