<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Kiro
 * @copyright Copyright (c) 2024
 *
 * Model class for Master Petty Cash (header-detail structure)
 */

class Petty_cash_master_model extends BF_Model
{
    /**
     * @var string Table name for petty cash master header
     */
    protected $table_name = 'ms_petty_cash';
    protected $key        = 'id';

    /**
     * @var string Field name for created time column
     */
    protected $created_field = 'created_on';

    /**
     * @var string Field name for modified time column
     */
    protected $modified_field = 'modified_on';

    /**
     * @var bool Auto-fill created time on insert
     */
    protected $set_created = true;

    /**
     * @var bool Auto-fill modified time on update
     */
    protected $set_modified = true;

    /**
     * @var string Date/time field type
     */
    protected $date_format = 'datetime';

    /**
     * @var bool Log user id in created_by and modified_by
     */
    protected $log_user = true;

    /**
     * @var string Detail table name
     */
    protected $detail_table = 'ms_petty_cash_detail';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get server-side DataTables data with search and pagination
     *
     * @param array $dt_params [draw, start, length, search, order]
     * @return array Formatted DataTables JSON structure
     */
    public function get_server_side_data($dt_params)
    {
        $draw   = isset($dt_params['draw']) ? intval($dt_params['draw']) : 1;
        $start  = isset($dt_params['start']) ? intval($dt_params['start']) : 0;
        $length = isset($dt_params['length']) ? intval($dt_params['length']) : 10;
        $search = isset($dt_params['search']['value']) ? $dt_params['search']['value'] : '';
        $order_col = isset($dt_params['order'][0]['column']) ? intval($dt_params['order'][0]['column']) : 0;
        $order_dir = isset($dt_params['order'][0]['dir']) ? $dt_params['order'][0]['dir'] : 'asc';

        // Define sortable columns
        $columns = ['a.id', 'a.nama', 'a.keterangan', 'a.total_budget'];

        $order_by = isset($columns[$order_col]) ? $columns[$order_col] : 'a.id';
        $order_dir = ($order_dir === 'desc') ? 'desc' : 'asc';

        // Count total records
        $this->db->from($this->table_name . ' a');
        $records_total = $this->db->count_all_results();

        // Count filtered records
        $this->db->from($this->table_name . ' a');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.nama', $search);
            $this->db->or_like('a.keterangan', $search);
            $this->db->group_end();
        }
        $records_filtered = $this->db->count_all_results();

        // Get paginated data
        $this->db->select('a.id, a.nama, a.keterangan, a.total_budget');
        $this->db->from($this->table_name . ' a');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.nama', $search);
            $this->db->or_like('a.keterangan', $search);
            $this->db->group_end();
        }
        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $results = $query->result();

        // Format data
        $data = [];
        $no = $start + 1;
        foreach ($results as $row) {
            $data[] = [
                'no'           => $no++,
                'nama'         => $row->nama,
                'keterangan'   => $row->keterangan,
                'total_budget' => number_format($row->total_budget, 0, ',', '.'),
                'id'           => $row->id
            ];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
            'data'            => $data
        ];
    }

    /**
     * Get master header with approver name joined from users table
     *
     * @param int $id
     * @return object|false
     */
    public function get_header($id)
    {
        $this->db->select('a.*, b.nm_lengkap as approver_name');
        $this->db->from($this->table_name . ' a');
        $this->db->join('users b', 'b.id_user = a.approver', 'left');
        $this->db->where('a.id', $id);
        $query = $this->db->get();

        if ($query->num_rows() != 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    /**
     * Get all detail rows for a master
     *
     * @param int $petty_cash_id
     * @return array
     */
    public function get_details($petty_cash_id)
    {
        $this->db->select('a.*');
        $this->db->from($this->detail_table . ' a');
        $this->db->where('a.petty_cash_id', $petty_cash_id);
        $this->db->order_by('a.id', 'asc');
        $query = $this->db->get();

        return $query->result();
    }

    /**
     * Save header + details in a transaction
     * Handles insert (new) or update (existing) based on presence of $id
     *
     * @param array $header   [nama, keterangan, approver]
     * @param array $details  [[coa_code, jenis_pengeluaran, nominal], ...]
     * @param int|null $id    Existing ID for update, null for insert
     * @return array [status => 0|1, msg => string, id => int]
     */
    public function save_with_details($header, $details, $id = null)
    {
        // Calculate total_budget from details
        $total_budget = 0;
        foreach ($details as $detail) {
            $total_budget += intval($detail['nominal']);
        }
        $header['total_budget'] = $total_budget;

        $this->db->trans_begin();

        if (empty($id)) {
            // INSERT new header
            $header['created_on'] = date('Y-m-d H:i:s');
            $header['created_by'] = $this->auth->user_id();
            $this->db->insert($this->table_name, $header);
            $id = $this->db->insert_id();
        } else {
            // UPDATE existing header
            $header['modified_on'] = date('Y-m-d H:i:s');
            $header['modified_by'] = $this->auth->user_id();
            $this->db->where('id', $id);
            $this->db->update($this->table_name, $header);

            // DELETE existing details (will re-insert all)
            $this->db->where('petty_cash_id', $id);
            $this->db->delete($this->detail_table);
        }

        // INSERT all detail rows
        foreach ($details as $detail) {
            $detail_data = [
                'petty_cash_id'     => $id,
                'coa_code'          => $detail['coa_code'],
                'jenis_pengeluaran' => $detail['jenis_pengeluaran'],
                'nominal'           => intval($detail['nominal'])
            ];
            $this->db->insert($this->detail_table, $detail_data);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => 0, 'msg' => 'Gagal menyimpan data. Silakan coba lagi.'];
        }

        $this->db->trans_commit();
        return ['status' => 1, 'msg' => 'Data berhasil disimpan', 'id' => $id];
    }

    /**
     * Delete master header + all detail rows in a transaction
     *
     * @param int $id
     * @return array [status => 0|1, msg => string]
     */
    public function delete_with_details($id)
    {
        $this->db->trans_begin();

        // DELETE details first (child rows)
        $this->db->where('petty_cash_id', $id);
        $this->db->delete($this->detail_table);

        // DELETE header
        $this->db->where('id', $id);
        $this->db->delete($this->table_name);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => 0, 'msg' => 'Gagal menghapus data. Silakan coba lagi.'];
        }

        $this->db->trans_commit();
        return ['status' => 1, 'msg' => 'Data berhasil dihapus'];
    }

    /**
     * Get active users for approver dropdown
     *
     * @return array Result objects with id_user, nm_lengkap
     */
    public function get_active_users()
    {
        $this->db->select('a.id_user, a.nm_lengkap');
        $this->db->from('users a');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.st_aktif', 1);
        $this->db->order_by('a.nm_lengkap', 'asc');
        $query = $this->db->get();

        return $query->result();
    }

    /**
     * Get COA list from accounting DB
     * Uses DBACC constant to query coa_master from the accounting database
     *
     * @return array Associative [no_perkiraan => 'no_perkiraan - nama']
     */
    public function get_coa_list()
    {
        $aMenu = [];
        $this->db->select('a.no_perkiraan, a.nama');
        $this->db->from(DBACC . '.coa_master a');
        $this->db->order_by('a.no_perkiraan', 'asc');
        $query = $this->db->get();
        $results = $query->result_array();

        if ($results) {
            foreach ($results as $vals) {
                $aMenu[$vals['no_perkiraan']] = $vals['no_perkiraan'] . ' - ' . $vals['nama'];
            }
        }

        return $aMenu;
    }
}
