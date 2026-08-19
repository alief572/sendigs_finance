<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_jurnal_payment_nomor_model extends CI_Model
{
    protected $db2;

    public function __construct()
    {
        parent::__construct();
        $this->db2 = $this->load->database('accounting', true);
    }
}
