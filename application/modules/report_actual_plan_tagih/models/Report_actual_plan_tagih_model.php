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
        $this->db->from('view_rekap_actual_plan_tagih_dev a');

        // Logic sama persis dengan get_data_report_apt di controller
        $this->db->group_start();
        $this->db->where('a.tahun_data', $tahun);
        $this->db->or_where('a.macet >', 0);
        $this->db->group_end();

        // Safety filter untuk data tahun yang valid
        $this->db->where('a.tahun_data >=', 2000);
        $this->db->where('a.tahun_data', $tahun);

        if (!empty($client))  $this->db->where('a.id_customer', $client);
        if (!empty($company)) $this->db->where('a.id_company', $company);

        $this->db->group_by('a.id_spk_penawaran');
        $this->db->order_by('a.id_spk_penawaran', 'DESC');

        $get_data = $this->db->get()->result();

        return $get_data;
    }
}
