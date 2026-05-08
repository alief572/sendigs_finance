<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Laporan Kunjungan Konsultan Model
 *
 * Handles database operations for the Laporan Kunjungan module.
 * Uses 'consultant' connection group for db_consultant_new database.
 * All queries to existing SPK tables are SELECT only (read-only access).
 */
class Laporan_kunjungan_model extends BF_Model
{
    protected $table_name = 'lk_visit_header';
    protected $key        = 'id';
    protected $db_con     = 'consultant';

    protected $set_created  = false;
    protected $set_modified = false;
    protected $soft_deletes = false;

    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // READ-ONLY methods for existing SPK tables
    // =========================================================================

    /**
     * Get list of SPK projects assigned to a konsultan.
     * SELECT only from existing tables.
     *
     * @param string $konsultan_id ID konsultan from session
     * @return array|false
     */
    public function get_spk_list($konsultan_id)
    {
        $this->db->select('
            a.id_spk_budgeting,
            a.id_spk_penawaran,
            a.nm_customer,
            a.nm_project_leader,
            a.nm_project,
            a.id_project,
            a.nm_konsultan_1 as nama_konsultan,
            b.nm_sales,
            b.waktu_from,
            b.waktu_to
        ');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->group_start();
        $this->db->where('a.id_konsultan_1', $konsultan_id);
        $this->db->or_where('a.id_konsultan_2', $konsultan_id);
        $this->db->group_end();
        $this->db->where('a.sts', 1);
        $this->db->order_by('a.id_spk_budgeting', 'desc');

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    /**
     * Get detail of a specific SPK project with all joins.
     * SELECT only from existing tables.
     *
     * @param string $id_spk ID SPK budgeting
     * @return object|false
     */
    public function get_spk_detail($id_spk)
    {
        $this->db->select('
            a.id_spk_budgeting,
            a.id_spk_penawaran,
            a.nm_customer,
            a.nm_project_leader,
            a.nm_project,
            a.id_project,
            a.id_konsultan_1,
            a.nm_konsultan_1 as nama_konsultan,
            b.nm_sales,
            b.waktu_from,
            b.waktu_to,
            d.nm_paket
        ');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join('kons_master_konsultasi_header d', 'd.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk);

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            return $query->row();
        }

        return false;
    }

    /**
     * Get kegiatan/activities from SPK aktifitas table.
     * SELECT only from existing tables.
     *
     * @param string $id_spk ID SPK budgeting
     * @return array|false
     */
    public function get_kegiatan_spk($id_spk)
    {
        $this->db->select('
            id,
            id_spk_budgeting,
            id_aktifitas,
            nm_aktifitas,
            mandays_subcont_final
        ');
        $this->db->from('kons_tr_spk_budgeting_aktifitas');
        $this->db->where('id_spk_budgeting', $id_spk);
        $this->db->order_by('id', 'asc');

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    /**
     * Get total mandays allocated for a project from SPK aktifitas.
     * SELECT only from existing tables.
     *
     * @param string $id_spk ID SPK budgeting
     * @return float Total mandays allocated
     */
    public function get_mandays_allocated($id_spk)
    {
        $this->db->select_sum('mandays_subcont_final', 'total_mandays');
        $this->db->from('kons_tr_spk_budgeting_aktifitas');
        $this->db->where('id_spk_budgeting', $id_spk);

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            return (float) ($row->total_mandays ?? 0);
        }

        return 0;
    }

    // =========================================================================
    // CRUD methods for lk_visit_header, lk_visit_kegiatan, lk_visit_action_plan
    // =========================================================================

    /**
     * Create a new visit record with transaction safety.
     *
     * @param array $data Associative array of visit header fields
     * @return int|false The inserted visit ID on success, false on failure
     */
    public function create_visit($data)
    {
        $this->db->trans_begin();

        $this->db->insert('lk_visit_header', $data);
        $insert_id = $this->db->insert_id();

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        return $insert_id;
    }

    /**
     * Update an existing visit header record.
     *
     * @param int   $id   Visit header ID
     * @param array $data Associative array of fields to update
     * @return bool True on success, false on failure
     */
    public function update_visit($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('lk_visit_header', $data);
    }

    /**
     * Batch insert kegiatan entries for a visit.
     * Deletes existing kegiatan for the visit first (replace strategy),
     * then inserts new entries within a transaction.
     *
     * @param int   $visit_id      Visit header ID
     * @param array $kegiatan_data Array of kegiatan records, each containing:
     *                             - id_aktifitas (string|null)
     *                             - nama_kegiatan (string)
     *                             - is_custom (int 0|1)
     *                             - sort_order (int)
     * @return array|false Array of inserted kegiatan IDs on success, false on failure
     */
    public function save_kegiatan($visit_id, $kegiatan_data)
    {
        $this->db->trans_begin();

        // Remove existing kegiatan for this visit (cascade will remove action plans)
        $this->db->where('visit_id', $visit_id);
        $this->db->delete('lk_visit_kegiatan');

        $inserted_ids = [];

        foreach ($kegiatan_data as $index => $kegiatan) {
            $insert_data = [
                'visit_id'      => $visit_id,
                'id_aktifitas'  => isset($kegiatan['id_aktifitas']) ? $kegiatan['id_aktifitas'] : null,
                'nama_kegiatan' => $kegiatan['nama_kegiatan'],
                'is_custom'     => isset($kegiatan['is_custom']) ? (int) $kegiatan['is_custom'] : 0,
                'sort_order'    => isset($kegiatan['sort_order']) ? (int) $kegiatan['sort_order'] : $index,
            ];

            $this->db->insert('lk_visit_kegiatan', $insert_data);
            $inserted_ids[] = $this->db->insert_id();
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        return $inserted_ids;
    }

    /**
     * Batch insert action plans for a kegiatan.
     *
     * @param int   $kegiatan_id Kegiatan ID
     * @param array $plans       Array of action plan records, each containing:
     *                           - visit_id (int)
     *                           - description (string)
     *                           - pic (string)
     *                           - due_date (string Y-m-d)
     *                           - status (string 'Progress'|'Done', default 'Progress')
     * @return bool True on success, false on failure
     */
    public function save_action_plans($kegiatan_id, $plans)
    {
        if (empty($plans)) {
            return true;
        }

        $this->db->trans_begin();

        $now = date('Y-m-d H:i:s');

        foreach ($plans as $plan) {
            $insert_data = [
                'kegiatan_id' => $kegiatan_id,
                'visit_id'    => $plan['visit_id'],
                'description' => $plan['description'],
                'pic'         => $plan['pic'],
                'due_date'    => $plan['due_date'],
                'status'      => isset($plan['status']) ? $plan['status'] : 'Progress',
                'created_at'  => $now,
            ];

            $this->db->insert('lk_visit_action_plan', $insert_data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        return true;
    }

    /**
     * Get a visit record with all related kegiatan and action plans.
     *
     * @param int $id Visit header ID
     * @return object|false Visit object with kegiatan and action_plans arrays, or false
     */
    public function get_visit($id)
    {
        // Get visit header
        $this->db->where('id', $id);
        $query = $this->db->get('lk_visit_header');

        if (!$query || $query->num_rows() == 0) {
            return false;
        }

        $visit = $query->row();

        // Get kegiatan for this visit
        $this->db->where('visit_id', $id);
        $this->db->order_by('sort_order', 'asc');
        $kegiatan_query = $this->db->get('lk_visit_kegiatan');

        $visit->kegiatan = [];

        if ($kegiatan_query && $kegiatan_query->num_rows() > 0) {
            $visit->kegiatan = $kegiatan_query->result();

            // Get action plans for each kegiatan
            foreach ($visit->kegiatan as &$keg) {
                $this->db->where('kegiatan_id', $keg->id);
                $this->db->order_by('id', 'asc');
                $plans_query = $this->db->get('lk_visit_action_plan');

                $keg->action_plans = [];
                if ($plans_query && $plans_query->num_rows() > 0) {
                    $keg->action_plans = $plans_query->result();
                }
            }
        }

        return $visit;
    }

    /**
     * Get all finalized visits for a project, sorted by visit_date descending.
     *
     * @param string $id_spk ID SPK budgeting
     * @return array|false Array of visit objects with kegiatan and action plans, or false
     */
    public function get_visits_by_project($id_spk)
    {
        $this->db->where('id_spk_budgeting', $id_spk);
        $this->db->where('status', 'final');
        $this->db->order_by('visit_date', 'desc');
        $query = $this->db->get('lk_visit_header');

        if (!$query || $query->num_rows() == 0) {
            return false;
        }

        $visits = $query->result();

        // Load kegiatan and action plans for each visit
        foreach ($visits as &$visit) {
            $this->db->where('visit_id', $visit->id);
            $this->db->order_by('sort_order', 'asc');
            $kegiatan_query = $this->db->get('lk_visit_kegiatan');

            $visit->kegiatan = [];

            if ($kegiatan_query && $kegiatan_query->num_rows() > 0) {
                $visit->kegiatan = $kegiatan_query->result();

                foreach ($visit->kegiatan as &$keg) {
                    $this->db->where('kegiatan_id', $keg->id);
                    $this->db->order_by('id', 'asc');
                    $plans_query = $this->db->get('lk_visit_action_plan');

                    $keg->action_plans = [];
                    if ($plans_query && $plans_query->num_rows() > 0) {
                        $keg->action_plans = $plans_query->result();
                    }
                }
            }
        }

        return $visits;
    }

    /**
     * Get total mandays used for a project (sum from all visits).
     *
     * @param string $id_spk ID SPK budgeting
     * @return float Total mandays used
     */
    public function get_mandays_used($id_spk)
    {
        $this->db->select_sum('mandays_used', 'total_used');
        $this->db->from('lk_visit_header');
        $this->db->where('id_spk_budgeting', $id_spk);
        $this->db->where('status', 'final');

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            return (float) ($row->total_used ?? 0);
        }

        return 0;
    }

    /**
     * Get action plans from previous visits for a project.
     * Used to display follow-up items during a new visit.
     *
     * @param string $id_spk ID SPK budgeting
     * @return array|false Array of action plan objects with kegiatan info, or false
     */
    public function get_previous_action_plans($id_spk)
    {
        $this->db->select('
            ap.id,
            ap.kegiatan_id,
            ap.visit_id,
            ap.description,
            ap.pic,
            ap.due_date,
            ap.status,
            ap.created_at,
            ap.updated_at,
            k.nama_kegiatan,
            vh.visit_date
        ');
        $this->db->from('lk_visit_action_plan ap');
        $this->db->join('lk_visit_kegiatan k', 'k.id = ap.kegiatan_id', 'left');
        $this->db->join('lk_visit_header vh', 'vh.id = ap.visit_id', 'left');
        $this->db->where('vh.id_spk_budgeting', $id_spk);
        $this->db->where('vh.status', 'final');
        $this->db->order_by('vh.visit_date', 'desc');
        $this->db->order_by('ap.id', 'asc');

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    /**
     * Update the status of an action plan entry.
     *
     * @param int    $id     Action plan ID
     * @param string $status New status ('Progress' or 'Done')
     * @return bool True on success, false on failure
     */
    public function update_action_plan_status($id, $status)
    {
        $this->db->where('id', $id);
        return $this->db->update('lk_visit_action_plan', [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get client email from SPK data.
     * SELECT only from existing tables.
     *
     * @param string $id_spk ID SPK budgeting
     * @return string|false Client email address, or false if not found
     */
    public function get_client_email($id_spk)
    {
        $this->db->select('b.email');
        $this->db->from('kons_tr_spk_budgeting a');
        $this->db->join('kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk);

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            return !empty($row->email) ? $row->email : false;
        }

        return false;
    }
}
