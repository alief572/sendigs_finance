<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Misc_model extends BF_Model
{

    protected $consultant;

    public function __construct()
    {
        $this->consultant = $this->load->database('consultant', true);
    }
    public function get_plan_tagih($id_spk_penawaran)
    {
        $this->db->select('a.*');
        $this->db->from('kons_tr_plan_tagih_header a');
        $this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_plan_tagih_detail($id_spk_penawaran, $term_payment)
    {
        $this->db->select('a.*');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
        $this->db->where('a.term_payment', $term_payment);
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_spk_penawaran($id_spk_penawaran)
    {
        $this->consultant->select('a.*, b.nm_paket');
        $this->consultant->from('kons_tr_spk_penawaran a');
        $this->consultant->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
        $this->consultant->where('a.id_spk_penawaran', $id_spk_penawaran);
        $get_data = $this->consultant->get()->row();

        return $get_data;
    }

    public function get_top_by_spk($id_spk_penawaran, $term_payment)
    {
        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_spk_penawaran_payment a');
        $this->consultant->where('a.id_spk_penawaran', $id_spk_penawaran);
        $this->consultant->where('a.term_payment', $term_payment);
        $get_data = $this->consultant->get()->row();

        return $get_data;
    }

    public function get_actual_by_spk($id_spk_penawaran, $term_payment)
    {
        $this->db->select('a.*');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
        $this->db->where('a.term_payment', $term_payment);
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit(1);
        $get_data = $this->db->get()->row();

        return $get_data;
    }

    public function get_actual_plan_tagih_last($id_spk_penawaran, $term_payment)
    {
        $this->db->select('a.*');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->like('a.id_spk_penawaran', $id_spk_penawaran, 'both');
        $this->db->like('a.term_payment', $term_payment, 'both');
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit(1);
        $get_data = $this->db->get()->row();

        return $get_data;
    }
}
