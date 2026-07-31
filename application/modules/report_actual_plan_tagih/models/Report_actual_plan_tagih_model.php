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
        $this->db->select("a.id_spk_penawaran, a.id_customer, a.nm_customer, a.nm_konsultan_1, a.nm_konsultan_2, a.nm_sales, a.nilai_kontrak, a.id_company, a.nm_company, a.nm_paket, a.sts_spk, a.nominal_invoice, a.nominal_uninvoice, a.macet, MAX(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.tahun_data ELSE a.tahun_data END) AS tahun_data", FALSE);

        $list_bulan = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        foreach ($list_bulan as $bln) {
            $this->db->select("SUM(CASE WHEN a.tahun_data = " . $this->db->escape($tahun) . " THEN a.`$bln` ELSE 0 END) AS `$bln`", FALSE);
        }

        $this->db->from('view_rekap_actual_plan_tagih_dev a');

        $this->db->group_start();
        $this->db->where('a.tahun_data', $tahun);
        $this->db->or_where('a.macet >', 0);
        $this->db->group_end();

        $this->db->where('a.tahun_data >=', 2000);

        if (!empty($client))  $this->db->where('a.id_customer', $client);
        if (!empty($company)) $this->db->where('a.id_company', $company);

        $this->db->group_by('a.id_spk_penawaran');
        $this->db->order_by('a.id_spk_penawaran', 'DESC');

        $get_data = $this->db->get()->result();

        return $get_data;
    }
}
