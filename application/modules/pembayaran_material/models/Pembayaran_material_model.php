<?php
class Pembayaran_material_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}
	public function get_data_json_request_payment_header($sqlwhere = '')
	{
		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment_header a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.id desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_request_payment($sqlwhere = '')
	{

		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.id desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_request_payment_nm($sqlwhere = '')
	{

		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment_nm a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.no_po desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_jurnal($sqlwhere = '')
	{

		$sql = "SELECT nomor,tanggal,no_reff,stspos FROM jurnaltras a WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " group by nomor,tanggal,no_reff,stspos order by no_reff desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}

	public function generate_id_payment_paid($kode_bank = null, $tanggal) {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM tr_payment_paid WHERE id LIKE '%BK-".$kode_bank."-" . date('my-', strtotime($tanggal)) . "%'")->row();
        $kodeBarang = $generate_id->max_id;
        $urutan = (int) substr($kodeBarang, 11, 4);
        $urutan++;
        $tahun = date('my-', strtotime($tanggal));
        $huruf = "BK-".$kode_bank."-";
        $kodecollect = $huruf . $tahun . sprintf("%04s", $urutan);

        return $kodecollect;   
    }
}
