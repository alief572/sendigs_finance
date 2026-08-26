<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Model for Price Supplier Barang Stok
 */
class Price_sup_barang_stok_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_categories()
    {
        $this->db->where('deleted_date IS NULL');
        $this->db->order_by('nm_category', 'ASC');
        return $this->db->get('accessories_category')->result();
    }

    public function get_items_by_category($id_category)
    {
        $this->db->select('a.*, c.code as nm_satuan');
        $this->db->from('accessories a');
        $this->db->join('ms_satuan c', 'c.id = a.id_unit', 'left');
        $this->db->where('a.id_category', $id_category);
        $this->db->where('a.deleted_date IS NULL');
        $this->db->where('a.status', '1');
        $this->db->order_by('a.stock_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_all_categories_with_items()
    {
        $categories = $this->get_categories();
        $result = [];
        foreach ($categories as $cat) {
            $items = $this->get_items_by_category($cat->id);
            $result[] = [
                'category' => $cat,
                'items'    => $items
            ];
        }
        return $result;
    }

    public function get_latest_kurs()
    {
        $kurs = $this->db->order_by('id', 'DESC')->limit(1)->get_where('master_kurs', ['deleted_date' => NULL])->row();
        return (!empty($kurs)) ? $kurs->kurs : 1;
    }

    public function get_header($no_doc)
    {
        return $this->db->get_where('tr_price_sup_barang_stok_header', ['no_doc' => $no_doc])->row();
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

    public function get_data_dokumen()
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
        $this->db->select('h.*, COUNT(d.id) as total_item, u.nm_lengkap as pembuat, u_app.nm_lengkap as approver');
        $this->db->from('tr_price_sup_barang_stok_header h');
        $this->db->join('tr_price_sup_barang_stok_detail d', 'd.no_doc = h.no_doc', 'left');
        $this->db->join('users u', 'u.id_user = h.created_by', 'left');
        $this->db->join('users u_app', 'u_app.id_user = h.approved_by', 'left');
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('h.no_doc', $search['value']);
            $this->db->or_like('h.tanggal_doc', $search['value']);
            $this->db->or_like('h.note', $search['value']);
            $this->db->group_end();
        }
        $this->db->group_by('h.no_doc');
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

            // Files count & quick download link
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
            
            if ($row->status == '0') {
                if (has_permission('Price_Supplier_Barang_Stok.Manage')) {
                    $action .= '<a class="btn btn-sm btn-primary" href="' . base_url('price_sup_barang_stok/edit/' . $row->no_doc) . '" title="Edit"><i class="fa fa-edit"></i></a> ';
                }
                if (has_permission('Price_Supplier_Barang_Stok.Delete')) {
                    $action .= '<button type="button" class="btn btn-sm btn-danger delete_doc" data-no_doc="' . $row->no_doc . '" title="Delete"><i class="fa fa-trash"></i></button>';
                }
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

    public function get_price_history($id_barang = null, $id_category = null)
    {
        $this->db->select('
            d.*,
            h.tanggal_doc,
            h.kurs as kurs_doc,
            h.approved_date,
            h.rejected_date,
            h.rejected_reason,
            a.id_stock,
            a.stock_name,
            a.spec,
            c.nm_category,
            s.code as nm_satuan,
            u_cr.nm_lengkap as creator_name,
            u_app.nm_lengkap as approver_name
        ');
        $this->db->from('tr_price_sup_barang_stok_detail d');
        $this->db->join('tr_price_sup_barang_stok_header h', 'h.no_doc = d.no_doc', 'left');
        $this->db->join('accessories a', 'a.id = d.id_barang', 'left');
        $this->db->join('accessories_category c', 'c.id = d.id_category', 'left');
        $this->db->join('ms_satuan s', 's.id = a.id_unit', 'left');
        $this->db->join('users u_cr', 'u_cr.id_user = h.created_by', 'left');
        $this->db->join('users u_app', 'u_app.id_user = h.approved_by', 'left');

        if (!empty($id_barang)) {
            $this->db->where('d.id_barang', $id_barang);
        }
        if (!empty($id_category)) {
            $this->db->where('d.id_category', $id_category);
        }

        $this->db->order_by('h.tanggal_doc', 'DESC');
        $this->db->order_by('d.id', 'DESC');
        return $this->db->get()->result();
    }
}
