<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Report_actual_plan_tagih_model extends Admin_Controller
{

    public function list_customer()
    {
        $this->db->select('a.id_customer, a.nm_customer');
        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        $this->db->where('a.sts_spk', '1');
        $this->db->group_by('a.id_customer');
        $this->db->order_by('a.nm_customer', 'asc');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function list_company()
    {
        $this->db->select('b.company as id_company, c.nm_company');
        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_company c', 'c.id = b.company');
        $this->db->group_by('b.company');
        $this->db->order_by('b.company', 'asc');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function list_report_filterable($client = null, $company = null, $tahun)
    {
        $this->db->select('a.*');
        $this->db->from('view_rekap_actual_plan_tagih a');
        $this->db->where('a.tahun_data', $tahun);
        if (!empty($client)) {
            $this->db->where('a.id_customer', $client);
        }
        if (!empty($company)) {
            $this->db->where('a.id_company', $company);
        }
        $get_data = $this->db->get()->result();

        return $get_data;

        // $this->db->select('a.id_spk_penawaran, a.id_customer, a.nm_customer, a.nilai_kontrak, c.id as id_company, c.nm_company, d.nm_paket');
        // $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        // $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        // $this->db->join(DBCNL . '.kons_tr_company c', 'c.id = b.company', 'left');
        // $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = a.id_project', 'left');
        // $this->db->where('a.sts_spk', '1');
        // if (!empty($client)) {
        //     $this->db->where('a.id_customer', $client);
        // }
        // if (!empty($company)) {
        //     $this->db->where('b.company', $company);
        // }
        // $this->db->group_by('a.id_spk_penawaran');

        // $get_data = $this->db->get()->result();

        // return $get_data;
    }

    public function get_report_actual_plan_tagih()
    {
        $this->db->select('a.id_spk_penawaran, a.id_customer, a.nm_customer, a.nilai_kontrak, c.id as id_company, c.nm_company, d.nm_paket');
        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_company c', 'c.id = b.company', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.sts_spk', '1');
        $this->db->group_by('a.id_spk_penawaran');

        $db_clone = clone $this->db;
        $count_filter = $db_clone->count_all_results();

        // $count_filter = $this->db->count_all_results();

        $get_data_all = $this->db->get()->result();

        return $get_data_all;
    }
}
