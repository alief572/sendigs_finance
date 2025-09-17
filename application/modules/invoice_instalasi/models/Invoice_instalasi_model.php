<?php
class Invoice_instalasi_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	function generate_id()
	{
		$query = $this->db->query("SELECT MAX(id) as max_id FROM tr_billing_plan WHERE id LIKE '%BILLING-" . date('Ymd') . "%'");
		$row = $query->row_array();
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 17, 5);
		$counter = $max_id1 + 1;
		$counter = sprintf('%05s', $counter);
		$idcust = "BILLING-" . date('Ymd') . "-" . $counter;
		return $idcust;
	}

	function  generate_id_invoice()
	{
		$query = $this->db->query("SELECT MAX(id_invoice) as max_id FROM tr_invoice_sales WHERE id_invoice LIKE '%INV-OM-" . date('y') . "-" . date('m') . "%'");
		$row = $query->row_array();
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 13, 3);
		$counter = $max_id1 + 1;
		$counter = sprintf('%03s', $counter);
		$idcust = "INV-OM-" . date('y') . '-' . date('m') . '-' . $counter;
		return $idcust;
	}
}
