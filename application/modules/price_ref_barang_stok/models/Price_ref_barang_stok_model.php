<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Model for Price Reference Barang Stok (Approval)
 */
class Price_ref_barang_stok_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_header($no_doc)
    {
        $this->db->select('h.*, u.nm_lengkap as pembuat, u_app.nm_lengkap as approver, u_rej.nm_lengkap as rejector');
        $this->db->from('tr_price_sup_barang_stok_header h');
        $this->db->join('users u', 'u.id_user = h.created_by', 'left');
        $this->db->join('users u_app', 'u_app.id_user = h.approved_by', 'left');
        $this->db->join('users u_rej', 'u_rej.id_user = h.rejected_by', 'left');
        $this->db->where('h.no_doc', $no_doc);
        return $this->db->get()->row();
    }

    public function get_details($no_doc)
    {
        $this->db->select('d.*, a.id_stock, a.stock_name, a.spec, c.nm_category, s.code as nm_satuan');
        $this->db->from('tr_price_sup_barang_stok_detail d');
        $this->db->join('accessories a', 'a.id = d.id_barang', 'left');
        $this->db->join('accessories_category c', 'c.id = d.id_category', 'left');
        $this->db->join('ms_satuan s', 's.id = a.id_unit', 'left');
        $this->db->where('d.no_doc', $no_doc);
        $this->db->order_by('c.nm_category', 'ASC');
        $this->db->order_by('a.stock_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_files($no_doc)
    {
        return $this->db->get_where('tr_price_sup_barang_stok_files', ['no_doc' => $no_doc])->result();
    }

    public function get_data_approval()
    {
        $draw   = $this->input->post('draw');
        $length = $this->input->post('length') ? intval($this->input->post('length')) : 10;
        $start  = $this->input->post('start') ? intval($this->input->post('start')) : 0;
        $search = $this->input->post('search');

        // Total count
        $total_records = $this->db->count_all_results('tr_price_sup_barang_stok_header');

        // Filter count
        $this->db->from('tr_price_sup_barang_stok_header h');
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('h.no_doc', $search['value']);
            $this->db->or_like('h.tanggal_doc', $search['value']);
            $this->db->or_like('h.note', $search['value']);
            $this->db->group_end();
        }
        $total_filtered = $this->db->count_all_results();

        // Data query
        $this->db->select('h.*, COUNT(d.id) as total_item, u.nm_lengkap as pembuat');
        $this->db->from('tr_price_sup_barang_stok_header h');
        $this->db->join('tr_price_sup_barang_stok_detail d', 'd.no_doc = h.no_doc', 'left');
        $this->db->join('users u', 'u.id_user = h.created_by', 'left');
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('h.no_doc', $search['value']);
            $this->db->or_like('h.tanggal_doc', $search['value']);
            $this->db->or_like('h.note', $search['value']);
            $this->db->group_end();
        }
        $this->db->group_by('h.no_doc');
        // Order: Waiting approval first (status=0), then by id DESC
        $this->db->order_by("FIELD(h.status, '0', '1', '2')", "ASC", false);
        $this->db->order_by('h.id', 'DESC');
        $this->db->limit($length, $start);
        $query = $this->db->get();

        $data = [];
        $no = $start;
        foreach ($query->result() as $row) {
            $no++;

            // Status badge
            if ($row->status == '1') {
                $status_badge = '<span class="badge bg-green">Approved</span>';
            } elseif ($row->status == '2') {
                $status_badge = '<span class="badge bg-red">Rejected</span>';
            } else {
                $status_badge = '<span class="badge bg-yellow">Waiting Approval</span>';
            }

            // Files
            $files = $this->get_files($row->no_doc);
            $file_links = '';
            if (!empty($files)) {
                foreach ($files as $f) {
                    $file_links .= '<a href="' . base_url($f->file_path) . '" target="_blank" class="btn btn-xs btn-default" style="margin-bottom:2px;" title="' . htmlspecialchars($f->file_name) . '"><i class="fa fa-paperclip"></i> ' . (strlen($f->file_name) > 15 ? substr($f->file_name, 0, 12) . '...' : $f->file_name) . '</a> ';
                }
            } else {
                $file_links = '<span class="text-muted">-</span>';
            }

            // Actions
            $action = '<button type="button" class="btn btn-sm btn-info view_doc" data-no_doc="' . $row->no_doc . '" title="View Detail"><i class="fa fa-eye"></i></button> ';
            
            if ($row->status == '0' && has_permission('Price_Ref_Barang_Stok.Manage')) {
                $action .= '<a class="btn btn-sm btn-success" href="' . base_url('price_ref_barang_stok/approval/' . $row->no_doc) . '" title="Proses Approval"><i class="fa fa-check-square-o"></i> Approval</a> ';
            }

            $data[] = [
                'no'           => $no,
                'no_doc'       => '<b>' . $row->no_doc . '</b>',
                'tanggal_doc'  => date('d-M-Y', strtotime($row->tanggal_doc)),
                'kurs'         => number_format($row->kurs, 2),
                'total_item'   => '<span class="badge bg-blue">' . $row->total_item . ' Item</span>',
                'pembuat'      => $row->pembuat ? $row->pembuat : '-',
                'status'       => $status_badge,
                'files'        => $file_links,
                'note'         => htmlspecialchars($row->note ?? ''),
                'action'       => $action
            ];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total_records,
            'recordsFiltered' => $total_filtered,
            'data'            => $data
        ];
    }

    public function process_approval($no_doc, $action, $reason = '', $items_custom = [])
    {
        $header = $this->get_header($no_doc);
        if (!$header) {
            return ['status' => 0, 'pesan' => 'Dokumen tidak ditemukan!'];
        }

        if ($header->status != '0') {
            return ['status' => 0, 'pesan' => 'Dokumen ini sudah pernah diproses approval!'];
        }

        $details = $this->get_details($no_doc);
        $user_id = $this->auth->user_id();
        $now = date('Y-m-d H:i:s');

        $this->db->trans_begin();

        if ($action == 'approve') {
            // Update Header
            $this->db->where('no_doc', $no_doc)->update('tr_price_sup_barang_stok_header', [
                'status'          => '1',
                'approved_by'     => $user_id,
                'approved_date'   => $now,
                'rejected_by'     => null,
                'rejected_date'   => null,
                'rejected_reason' => null
            ]);

            // Update Details & Master accessories
            foreach ($details as $d) {
                $id_barang = $d->id_barang;

                // Price reference use: defaults to new higher price unless customized
                $use_idr = $d->price_ref_high_new;
                $use_usd = $d->price_ref_high_new_usd;
                $use_exp = $d->expired;

                if (!empty($items_custom[$id_barang])) {
                    if (isset($items_custom[$id_barang]['price_ref_use_after'])) {
                        $use_idr = floatval(str_replace(',', '', $items_custom[$id_barang]['price_ref_use_after']));
                    }
                    if (isset($items_custom[$id_barang]['price_ref_use_after_usd'])) {
                        $use_usd = floatval(str_replace(',', '', $items_custom[$id_barang]['price_ref_use_after_usd']));
                    }
                    if (isset($items_custom[$id_barang]['price_ref_expired_use_after'])) {
                        $use_exp = intval($items_custom[$id_barang]['price_ref_expired_use_after']);
                    }
                }

                // Update detail row
                $this->db->where('id', $d->id)->update('tr_price_sup_barang_stok_detail', [
                    'status'                      => '1',
                    'price_ref_use_after'         => $use_idr,
                    'price_ref_use_after_usd'     => $use_usd,
                    'price_ref_expired_use_after' => $use_exp
                ]);

                // Update master table accessories
                $update_acc = [
                    'price_ref'             => $d->price_ref_new,
                    'price_ref_high'        => $d->price_ref_high_new,
                    'price_ref_usd'         => $d->price_ref_new_usd,
                    'price_ref_high_usd'    => $d->price_ref_high_new_usd,
                    'price_ref_date'        => $header->tanggal_doc,
                    'price_ref_expired'     => $d->expired,
                    'price_ref_use'         => $use_idr,
                    'price_ref_use_usd'     => $use_usd,
                    'price_ref_date_use'    => date('Y-m-d'),
                    'price_ref_expired_use' => $use_exp,
                    'kurs'                  => $header->kurs,
                    'status_app'            => 'N',
                    'app_by'                => $user_id,
                    'app_date'              => $now
                ];

                $this->db->where('id', $id_barang);
                $this->db->update('accessories', $update_acc);
            }

            $log_msg = "Approve pengajuan price supplier barang stok: " . $no_doc;
        } else {
            // Reject Header
            $this->db->where('no_doc', $no_doc)->update('tr_price_sup_barang_stok_header', [
                'status'          => '2',
                'rejected_by'     => $user_id,
                'rejected_date'   => $now,
                'rejected_reason' => $reason
            ]);

            // Update Details
            $this->db->where('no_doc', $no_doc)->update('tr_price_sup_barang_stok_detail', [
                'status' => '2'
            ]);

            $log_msg = "Reject pengajuan price supplier barang stok: " . $no_doc . " (Alasan: " . $reason . ")";
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => 0, 'pesan' => 'Gagal memproses approval data!'];
        } else {
            $this->db->trans_commit();
            history($log_msg);
            return [
                'status' => 1,
                'pesan'  => 'Dokumen pengajuan ' . $no_doc . ' berhasil di-' . ($action == 'approve' ? 'Approve' : 'Reject') . '.'
            ];
        }
    }
}

