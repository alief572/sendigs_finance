<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Price_sup_barang_stok extends Admin_Controller
{
    // Permission
    protected $viewPermission   = 'Price_Supplier_Barang_Stok.View';
    protected $addPermission    = 'Price_Supplier_Barang_Stok.Add';
    protected $managePermission = 'Price_Supplier_Barang_Stok.Manage';
    protected $deletePermission = 'Price_Supplier_Barang_Stok.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Price_sup_barang_stok/Price_sup_barang_stok_model',
            'All/All_model'
        ));
        $this->template->title('Price From Supplier >> Barang Stok');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        history("View index price from supplier barang stok (batch)");
        $this->template->title('Price From Supplier >> Barang Stok');
        $this->template->render('index');
    }

    public function get_data()
    {
        $data = $this->Price_sup_barang_stok_model->get_data_dokumen();
        echo json_encode($data);
    }

    public function add()
    {
        $this->auth->restrict($this->addPermission);

        $no_doc = $this->All_model->GetAutoGenerate('format_price_sup_stok');
        if (empty($no_doc)) {
            $no_doc = 'PRC-STK-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
        }

        $kurs = $this->Price_sup_barang_stok_model->get_latest_kurs();
        $categories_items = $this->Price_sup_barang_stok_model->get_all_categories_with_items();

        $data = [
            'type'             => 'add',
            'no_doc'           => $no_doc,
            'tanggal_doc'      => date('Y-m-d'),
            'kurs'             => $kurs,
            'note'             => '',
            'header'           => null,
            'categories_items' => $categories_items,
            'existing_details' => [],
            'existing_files'   => []
        ];

        $this->template->set($data);
        $this->template->title('Add Pengajuan Price Supplier >> Barang Stok');
        $this->template->render('add');
    }

    public function edit($no_doc = null)
    {
        $this->auth->restrict($this->managePermission);

        $header = $this->Price_sup_barang_stok_model->get_header($no_doc);
        if (!$header) {
            $this->template->set_message("Dokumen pengajuan tidak ditemukan.", 'error');
            redirect('price_sup_barang_stok');
        }

        if ($header->status == '1') {
            $this->template->set_message("Dokumen sudah disetujui (Approved) dan tidak dapat diedit.", 'warning');
            redirect('price_sup_barang_stok');
        }

        $details = $this->Price_sup_barang_stok_model->get_details($no_doc);
        $files   = $this->Price_sup_barang_stok_model->get_files($no_doc);

        $existing_details = [];
        foreach ($details as $d) {
            $existing_details[$d->id_barang] = $d;
        }

        $categories_items = $this->Price_sup_barang_stok_model->get_all_categories_with_items();

        $data = [
            'type'             => 'edit',
            'no_doc'           => $header->no_doc,
            'tanggal_doc'      => $header->tanggal_doc,
            'kurs'             => $header->kurs,
            'note'             => $header->note,
            'header'           => $header,
            'categories_items' => $categories_items,
            'existing_details' => $existing_details,
            'existing_files'   => $files
        ];

        $this->template->set($data);
        $this->template->title('Edit Pengajuan Price Supplier >> Barang Stok');
        $this->template->render('add');
    }

    public function view($no_doc = null)
    {
        $header  = $this->Price_sup_barang_stok_model->get_header($no_doc);
        $details = $this->Price_sup_barang_stok_model->get_details($no_doc);
        $files   = $this->Price_sup_barang_stok_model->get_files($no_doc);

        // Group details by category
        $details_by_cat = [];
        foreach ($details as $d) {
            $cat_name = !empty($d->nm_category) ? $d->nm_category : 'Lainnya';
            $details_by_cat[$cat_name][] = $d;
        }

        $data = [
            'header'         => $header,
            'details'        => $details,
            'details_by_cat' => $details_by_cat,
            'files'          => $files
        ];

        $this->load->view('view', $data);
    }

    public function save_data()
    {
        $post = $this->input->post();

        $type        = $post['type'] ?? 'add';
        $no_doc      = $post['no_doc'];
        $tanggal_doc = !empty($post['tanggal_doc']) ? date('Y-m-d', strtotime($post['tanggal_doc'])) : date('Y-m-d');
        $kurs        = !empty($post['kurs']) ? floatval(str_replace(',', '', $post['kurs'])) : 1;
        $note        = $post['note'] ?? '';

        $items = $post['items'] ?? [];

        if (empty($items)) {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Belum ada data barang yang diinput harga baru!'
            ]);
            return;
        }

        // Filter only items that have new price entered
        $valid_items = [];
        foreach ($items as $item) {
            $price_new = floatval(str_replace(',', '', $item['price_ref_new'] ?? 0));
            $price_high_new = floatval(str_replace(',', '', $item['price_ref_high_new'] ?? 0));
            $price_new_usd = floatval(str_replace(',', '', $item['price_ref_new_usd'] ?? 0));
            $price_high_new_usd = floatval(str_replace(',', '', $item['price_ref_high_new_usd'] ?? 0));

            if ($price_new > 0 || $price_high_new > 0 || $price_new_usd > 0 || $price_high_new_usd > 0) {
                $valid_items[] = [
                    'no_doc'                  => $no_doc,
                    'id_category'             => intval($item['id_category']),
                    'id_barang'               => intval($item['id_barang']),
                    'price_ref_before'        => floatval(str_replace(',', '', $item['price_ref_before'] ?? 0)),
                    'price_ref_high_before'   => floatval(str_replace(',', '', $item['price_ref_high_before'] ?? 0)),
                    'price_ref_usd_before'    => floatval(str_replace(',', '', $item['price_ref_usd_before'] ?? 0)),
                    'price_ref_high_usd_before' => floatval(str_replace(',', '', $item['price_ref_high_usd_before'] ?? 0)),
                    'price_ref_new'           => $price_new,
                    'price_ref_high_new'      => $price_high_new,
                    'price_ref_new_usd'       => $price_new_usd,
                    'price_ref_high_new_usd'  => $price_high_new_usd,
                    'expired'                 => intval($item['expired'] ?? 1),
                    'note'                    => $item['note'] ?? '',
                    'status'                  => '0'
                ];
            }
        }

        if (empty($valid_items)) {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Silakan masukkan minimal 1 harga baru barang yang diajukan!'
            ]);
            return;
        }

        $this->db->trans_begin();

        if ($type == 'edit') {
            $dataHeader = [
                'tanggal_doc'     => $tanggal_doc,
                'kurs'            => $kurs,
                'note'            => $note,
                'status'          => '0',
                'updated_by'      => $this->id_user,
                'updated_date'    => $this->datetime,
                'rejected_by'     => null,
                'rejected_date'   => null,
                'rejected_reason' => null
            ];
            $this->db->where('no_doc', $no_doc);
            $this->db->update('tr_price_sup_barang_stok_header', $dataHeader);

            // Delete old details to re-insert fresh
            $this->db->where('no_doc', $no_doc);
            $this->db->delete('tr_price_sup_barang_stok_detail');
        } else {
            $dataHeader = [
                'no_doc'       => $no_doc,
                'tanggal_doc'  => $tanggal_doc,
                'kurs'         => $kurs,
                'note'         => $note,
                'status'       => '0',
                'created_by'   => $this->id_user,
                'created_date' => $this->datetime
            ];
            $this->db->insert('tr_price_sup_barang_stok_header', $dataHeader);
        }

        // Insert Detail Items
        $this->db->insert_batch('tr_price_sup_barang_stok_detail', $valid_items);

        // Handle Multiple File Upload
        if (!empty($_FILES['evidence_files']['name'][0])) {
            $upload_dir = 'assets/files/evidence_price_sup/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_count = count($_FILES['evidence_files']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                if (!empty($_FILES['evidence_files']['tmp_name'][$i])) {
                    $orig_name = $_FILES['evidence_files']['name'][$i];
                    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                    $safe_doc = preg_replace('/[^A-Za-z0-9_\-]/', '_', $no_doc);
                    $new_file_name = 'evd_' . $safe_doc . '_' . date('YmdHis') . '_' . $i . '.' . $ext;
                    $target_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($_FILES['evidence_files']['tmp_name'][$i], $target_path)) {
                        $this->db->insert('tr_price_sup_barang_stok_files', [
                            'no_doc'       => $no_doc,
                            'file_name'    => $orig_name,
                            'file_path'    => $target_path,
                            'file_type'    => $ext,
                            'file_size'    => $_FILES['evidence_files']['size'][$i] ?? 0,
                            'created_by'   => $this->id_user,
                            'created_date' => $this->datetime
                        ]);
                    }
                }
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal menyimpan data pengajuan!'
            ]);
        } else {
            $this->db->trans_commit();
            history("Pengajuan Price Supplier Barang Stok: " . $no_doc);
            echo json_encode([
                'status' => 1,
                'pesan'  => 'Pengajuan harga supplier berhasil disimpan (No. ' . $no_doc . ')',
                'no_doc' => $no_doc
            ]);
        }
    }

    public function delete_data()
    {
        $this->auth->restrict($this->deletePermission);
        $no_doc = $this->input->post('no_doc');

        $header = $this->Price_sup_barang_stok_model->get_header($no_doc);
        if (!$header || $header->status != '0') {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Data tidak dapat dihapus atau sudah diproses approval!'
            ]);
            return;
        }

        $this->db->trans_begin();

        // Delete files
        $files = $this->Price_sup_barang_stok_model->get_files($no_doc);
        foreach ($files as $f) {
            if (file_exists($f->file_path)) {
                @unlink($f->file_path);
            }
        }
        $this->db->where('no_doc', $no_doc)->delete('tr_price_sup_barang_stok_files');
        $this->db->where('no_doc', $no_doc)->delete('tr_price_sup_barang_stok_detail');
        $this->db->where('no_doc', $no_doc)->delete('tr_price_sup_barang_stok_header');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus data!']);
        } else {
            $this->db->trans_commit();
            history("Hapus pengajuan price supplier: " . $no_doc);
            echo json_encode(['status' => 1, 'pesan' => 'Dokumen pengajuan berhasil dihapus.']);
        }
    }

    public function history()
    {
        $categories = $this->Price_sup_barang_stok_model->get_categories();
        $this->db->where('deleted_date IS NULL');
        $this->db->order_by('stock_name', 'ASC');
        $items = $this->db->get('accessories')->result();

        $data = [
            'categories' => $categories,
            'items'      => $items
        ];
        $this->load->view('history', $data);
    }

    public function get_history_data()
    {
        $id_barang   = $this->input->post('id_barang');
        $id_category = $this->input->post('id_category');
        $history     = $this->Price_sup_barang_stok_model->get_price_history($id_barang, $id_category);
        echo json_encode($history);
    }

    public function get_kurs()
    {
        $kurs = $this->Price_sup_barang_stok_model->get_latest_kurs();
        echo json_encode(['status' => 1, 'kurs' => $kurs]);
    }
}

