<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Jurnal_payment_model extends BF_Model
{
    protected $viewPermission     = 'Jurnal_Payment.View';
    protected $addPermission      = 'Jurnal_Payment.Add';
    protected $managePermission = 'Jurnal_Payment.Manage';
    protected $deletePermission = 'Jurnal_Payment.Delete';

    protected $accounting;
    protected $accounting_vuca;
    protected $accounting_sustain;
    protected $consultant;

    public function __construct()
    {
        $this->accounting = $this->load->database('accounting', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
        $this->consultant = $this->load->database('consultant', true);
    }

    public function get_data_jurnal()
    {
        $post = $this->input->post();

        $draw   = isset($post['draw']) ? $post['draw'] : 0;
        $length = isset($post['length']) ? $post['length'] : 10;
        $start  = isset($post['start']) ? $post['start'] : 0;
        $search = isset($post['search']) ? $post['search'] : ['value' => ''];
        $order  = isset($post['order']) ? $post['order'] : [];

        $tgl_jurnal = isset($post['tgl_jurnal']) ? $post['tgl_jurnal'] : '';
        $tgl_from = '';
        $tgl_to = '';
        if (!empty($tgl_jurnal)) {
            $exp_tgl_jurnal = explode(' to ', $tgl_jurnal);
            $tgl_from = $exp_tgl_jurnal[0];
            $tgl_to = $exp_tgl_jurnal[1];
        }

        $no_transaksi = isset($post['no_transaksi']) ? $post['no_transaksi'] : '';
        $company = isset($post['company']) ? $post['company'] : '';

        $filter = [
            'tgl_from' => $tgl_from,
            'tgl_to' => $tgl_to,
            'no_transaksi' => $no_transaksi,
            'company' => $company
        ];

        // Define sortable columns mapping
        $sort_columns = [
            1 => 'a.no_transaksi',
            3 => 'a.jenis_transaksi',
            4 => 'a.tgl_jurnal',
            5 => 'a.nm_company'
        ];

        $arr_jenis_transaksi = ['Payment', 'Transport', 'Transportasi', 'Kasbon', 'Expense', 'Expense Report'];

        // Base filter criteria
        $this->db->from('tr_jurnal a');
        $this->db->join('payment_approve b', 'a.no_transaksi = b.id OR FIND_IN_SET(b.id, REPLACE(a.no_transaksi, \' \', \'\')) > 0', 'left');
        $this->db->join('tr_kasbon k', 'k.no_doc = b.no_doc', 'left');
        $this->db->join('tr_expense e', 'e.no_doc = b.no_doc', 'left');
        $this->db->where('a.sts <>', '1');
        $this->db->where_in('a.jenis_transaksi', $arr_jenis_transaksi);
        $this->db->where('a.nm_company <>', '');

        if (!empty($tgl_from) && !empty($tgl_to)) {
            $this->db->where('a.tgl_jurnal >=', $filter['tgl_from']);
            $this->db->where('a.tgl_jurnal <=', $filter['tgl_to']);
        }

        if (!empty($no_transaksi)) {
            $this->db->where('a.no_transaksi', $filter['no_transaksi']);
        }

        if (!empty($company)) {
            $this->db->where('a.id_company', $filter['company']);
        }

        $this->db->group_by(['a.no_transaksi', 'a.jenis_transaksi']);

        // Calculate recordsTotal (Total before search)
        $temp_db = clone $this->db;
        $query_total = $temp_db->get();
        $recordsTotal = $query_total->num_rows();

        // Apply Search
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.no_transaksi', $search['value'], 'both');
            $this->db->or_like('a.jenis_transaksi', $search['value'], 'both');
            $this->db->or_like('a.nm_company', $search['value'], 'both');
            $this->db->or_like('b.no_doc', $search['value'], 'both');
            $this->db->or_like('k.no_kasbon_consultant', $search['value'], 'both');
            $this->db->or_like('e.no_expense_consultant', $search['value'], 'both');
            $this->db->group_end();
        }

        // Calculate recordsFiltered (Total after search)
        $temp_db_filtered = clone $this->db;
        $query_filtered = $temp_db_filtered->get();
        $recordsFiltered = $query_filtered->num_rows();

        // Apply Ordering
        if (!empty($order) && isset($sort_columns[$order[0]['column']])) {
            $this->db->order_by($sort_columns[$order[0]['column']], $order[0]['dir']);
        } else {
            $this->db->order_by('a.tgl_jurnal', 'desc');
            $this->db->order_by('a.id', 'desc');
        }

        // Apply Select and Limit
        $this->db->select('a.id, a.no_jurnal, a.tgl_jurnal, a.coa, a.id_company, a.nm_company, a.nm_coa, a.debit, a.kredit, a.keterangan, a.sts, a.no_transaksi, a.jenis_transaksi');
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        $get_data = $this->db->get()->result();

        $hasil = [];
        $no = $start;
        foreach ($get_data as $item) {
            $no++;

            $arr_no_transaksi = array_map('trim', explode(',', $item->no_transaksi));
            $get_payment_info = $this->db->select('b.id, b.no_doc, b.tipe, k.no_kasbon_consultant, e.no_expense_consultant')
                ->from('payment_approve b')
                ->join('tr_kasbon k', 'k.no_doc = b.no_doc', 'left')
                ->join('tr_expense e', 'e.no_doc = b.no_doc', 'left')
                ->where_in('b.id', $arr_no_transaksi)
                ->get()
                ->result_array();

            $arr_no_pengajuan = [];
            $arr_tipe_payment = [];
            foreach ($get_payment_info as $row_pa) {
                if (!empty($row_pa['tipe'])) {
                    $arr_tipe_payment[] = $row_pa['tipe'];
                }

                $no_doc = $row_pa['no_doc'];
                if ($row_pa['tipe'] == 'kasbon' && !empty($row_pa['no_kasbon_consultant'])) {
                    $no_doc = $row_pa['no_kasbon_consultant'];
                } elseif ($row_pa['tipe'] == 'expense' && !empty($row_pa['no_expense_consultant'])) {
                    $no_doc = $row_pa['no_expense_consultant'];
                }

                if (!empty($no_doc)) {
                    $arr_no_pengajuan[] = $no_doc;
                }
            }

            if ($item->jenis_transaksi == 'Expense Report') {
                $no_pengajuan = $item->no_transaksi;
                $raw_tipe_payment = 'Expense Report';
            } else {
                $no_pengajuan = (!empty($arr_no_pengajuan)) ? implode(', ', array_unique($arr_no_pengajuan)) : '-';
                $raw_tipe_payment = (!empty($arr_tipe_payment)) ? implode(', ', array_unique($arr_tipe_payment)) : $item->jenis_transaksi;
            }

            // Format kategori: replace '_' menjadi spasi dan jadikan setiap kata berawalan huruf kapital
            $clean_kategori = ucwords(str_replace('_', ' ', strtolower(trim($raw_tipe_payment))));
            $tipe_payment_lower = strtolower($raw_tipe_payment);

            // Badge styling untuk kategori transaksi dengan warna senada
            if (strpos($tipe_payment_lower, 'kasbon') !== false) {
                $badge_kategori = '<span class="label label-warning" style="font-size:11px; padding:4px 8px; border-radius:4px; font-weight:600;"><i class="fa fa-money"></i> ' . htmlspecialchars($clean_kategori) . '</span>';
            } elseif (strpos($tipe_payment_lower, 'expense') !== false) {
                $badge_kategori = '<span class="label label-primary" style="font-size:11px; padding:4px 8px; border-radius:4px; font-weight:600;"><i class="fa fa-file-text-o"></i> ' . htmlspecialchars($clean_kategori) . '</span>';
            } elseif (strpos($tipe_payment_lower, 'transport') !== false) {
                $badge_kategori = '<span class="label label-info" style="font-size:11px; padding:4px 8px; border-radius:4px; font-weight:600;"><i class="fa fa-car"></i> ' . htmlspecialchars($clean_kategori) . '</span>';
            } else {
                $badge_kategori = '<span class="label label-success" style="font-size:11px; padding:4px 8px; border-radius:4px; font-weight:600;"><i class="fa fa-check-circle"></i> ' . htmlspecialchars($clean_kategori) . '</span>';
            }

            $badge_company = '<span class="label" style="background-color: #3c8dbc; color: #fff; font-size:11px; padding:4px 8px; border-radius:4px; font-weight:600;">' . htmlspecialchars($item->nm_company) . '</span>';

            $action_btn = '<button type="button" class="btn btn-sm btn-primary" onclick="add_jurnal(' . $item->id . ')" title="Posting Jurnal" style="border-radius: 6px; padding: 4px 10px; font-weight: 600;"><i class="fa fa-plus"></i> Post</button>';

            $hasil[] = [
                'no'               => '<span class="text-muted" style="font-weight:600;">' . $no . '</span>',
                'no_transaksi'     => '<span style="font-weight: 700; color: #0073b7;">' . htmlspecialchars($item->no_transaksi) . '</span>',
                'no_pengajuan'     => '<span style="font-weight: 600; color: #444;">' . htmlspecialchars($no_pengajuan) . '</span>',
                'kategori_payment' => $badge_kategori,
                'tanggal_jurnal'   => date('d F Y', strtotime($item->tgl_jurnal)),
                'company'          => $badge_company,
                'action'           => $action_btn
            ];
        }

        echo json_encode([
            'draw'            => intval($draw),
            'recordsTotal'    => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data'            => $hasil
        ]);
    }

    public function get_list_jurnal($filter = null)
    {
        $arr_jenis_transaksi = ['Payment', 'Transport', 'Transportasi', 'Kasbon', 'Expense', 'Expense Report'];

        $this->db->select('a.*');
        $this->db->from('tr_jurnal a');
        $this->db->where('a.sts <>', '1');
        $this->db->where_in('a.jenis_transaksi', $arr_jenis_transaksi);
        $this->db->where('a.nm_company <>', '');

        if (!empty($filter['tgl_from']) && !empty($filter['tgl_to'])) {
            $this->db->where('a.tgl_jurnal >=', $filter['tgl_from']);
            $this->db->where('a.tgl_jurnal <=', $filter['tgl_to']);
        }

        if (!empty($filter['no_transaksi'])) {
            $this->db->where('a.no_transaksi', $filter['no_transaksi']);
        }

        if (!empty($filter['company'])) {
            $this->db->where('a.id_company', $filter['company']);
        }

        $this->db->group_by(['a.no_transaksi', 'a.jenis_transaksi']);
        $this->db->order_by('a.id', 'desc');
        $get_data = $this->db->get()->result_array();

        $hasil = [];
        foreach ($get_data as $item) {
            $arr_no_transaksi = array_map('trim', explode(',', $item['no_transaksi']));
            $get_payment_info = $this->db->select('b.id, b.no_doc, b.tipe, k.no_kasbon_consultant, e.no_expense_consultant')
                ->from('payment_approve b')
                ->join('tr_kasbon k', 'k.no_doc = b.no_doc', 'left')
                ->join('tr_expense e', 'e.no_doc = b.no_doc', 'left')
                ->where_in('b.id', $arr_no_transaksi)
                ->get()
                ->result_array();

            $arr_no_pengajuan = [];
            $arr_tipe_payment = [];
            foreach ($get_payment_info as $row_pa) {
                if (!empty($row_pa['tipe'])) {
                    $arr_tipe_payment[] = $row_pa['tipe'];
                }

                $no_doc = $row_pa['no_doc'];
                if ($row_pa['tipe'] == 'kasbon' && !empty($row_pa['no_kasbon_consultant'])) {
                    $no_doc = $row_pa['no_kasbon_consultant'];
                } elseif ($row_pa['tipe'] == 'expense' && !empty($row_pa['no_expense_consultant'])) {
                    $no_doc = $row_pa['no_expense_consultant'];
                }

                if (!empty($no_doc)) {
                    $arr_no_pengajuan[] = $no_doc;
                }
            }

            if ($item['jenis_transaksi'] == 'Expense Report') {
                $no_pengajuan = $item['no_transaksi'];
                $raw_tipe_payment = 'Expense Report';
            } else {
                $no_pengajuan = (!empty($arr_no_pengajuan)) ? implode(', ', array_unique($arr_no_pengajuan)) : '-';
                $raw_tipe_payment = (!empty($arr_tipe_payment)) ? implode(', ', array_unique($arr_tipe_payment)) : $item['jenis_transaksi'];
            }
            $clean_kategori = ucwords(str_replace('_', ' ', strtolower(trim($raw_tipe_payment))));

            $item['no_pengajuan'] = $no_pengajuan;
            $item['kategori_payment'] = $clean_kategori;

            $hasil[] = $item;
        }

        return $hasil;
    }

    public function get_no_payment_jurnal()
    {
        $arr_jenis_transaksi = ['Payment', 'Transport', 'Transportasi', 'Kasbon', 'Expense', 'Expense Report'];

        $get_no_payment_jurnal = $this->db->select('a.no_transaksi')
            ->from('tr_jurnal a')
            ->where('a.sts <>', '1')
            ->where_in('a.jenis_transaksi', $arr_jenis_transaksi)
            ->where('a.nm_company <>', '')
            ->group_by(['a.no_transaksi', 'a.jenis_transaksi'])
            ->order_by('a.created_date', 'desc')
            ->get()
            ->result_array();

        return $get_no_payment_jurnal;
    }

    public function get_company()
    {
        $get_company = $this->consultant->select('a.id as id_company, a.nm_company')
            ->from('kons_tr_company a')
            ->get()
            ->result_array();

        return $get_company;
    }
}
