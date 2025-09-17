<?php
class Billing_plan_instalasi_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	function generate_id($nox = 0)
	{
		$query = $this->db->query("SELECT MAX(id) as max_id FROM tr_billing_plan WHERE id LIKE '%BILLING-".date('Ymd')."%'");
		$row = $query->row_array();
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 17, 5);
		$counter = $max_id1 + ($nox + 1);
		$counter = sprintf('%05s', $counter);
		$idcust = "BILLING-".date('Ymd')."-".$counter;
		return $idcust;
	}
}
