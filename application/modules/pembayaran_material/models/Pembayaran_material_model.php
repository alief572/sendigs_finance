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

	public function generate_id_payment_paid($kode_bank = null, $tanggal)
	{
		$generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM tr_payment_paid WHERE id LIKE '%BK-" . $kode_bank . "-" . date('my-', strtotime($tanggal)) . "%'")->row();
		$kodeBarang = $generate_id->max_id;
		$urutan = (int) substr($kodeBarang, 16, 4);
		if ($kode_bank == null) {
			$urutan = (int) substr($kodeBarang, 9, 4);
		}
		$urutan++;
		$tahun = date('my-', strtotime($tanggal));
		$huruf = "BK-" . $kode_bank . "-";
		$kodecollect = $huruf . $tahun . sprintf("%04s", $urutan);

		return $kodecollect;
	}

	public function get_list_req_payment()
	{
		$post = $this->input->post();

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];
		$jenis_payment = $post['jenis_payment'];

		$hasil = [];

		if ($jenis_payment == 1) {
			$this->db->select('a.id, a.created_on, a.no_doc, a.currency, a.jumlah, a.keperluan, b.created_by as requestor');
			$this->db->from('payment_approve a');
			$this->db->join('tr_expense b', 'b.no_doc = a.no_doc');
			$this->db->where('a.status <>', 2);
			$this->db->where('b.exp_inv_po', 1);
			if (!empty($search['value'])) {
				$this->db->group_start();
				$this->db->like('a.no_doc', $search['value'], 'both');
				$this->db->or_like('a.created_on', $search['value'], 'both');
				$this->db->or_like('a.keperluan', $search['value'], 'both');
				$this->db->or_like('a.currency', $search['value'], 'both');
				$this->db->or_like('a.jumlah', $search['value'], 'both');
				$this->db->group_end();
			}
			$this->db->order_by('a.created_on', 'desc');
			$this->db->group_by('a.id');

			$db_clone = clone $this->db;
			$count_all = $db_clone->count_all_results();

			$this->db->limit($length, $start);
			$get_data = $this->db->get()->result();

			$hasil = [];

			$no = (0 + $start);
			foreach ($get_data as $item) {
				$no_incoming = [];
				$no_po = [];
				$nm_supplier = [];

				if (!empty($get_rec_invoice)) {
					if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
						$arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
						$get_no_po = $this->db
							->select('a.no_ipp')
							->from('tr_incoming_check a')
							->where_in('a.kode_trans', explode(',', $arr_no_incoming))
							->get()
							->result();

						$arr_no_po = [];
						foreach ($get_no_po as $item_no_po) {
							$arr_no_po[] = $item_no_po->no_ipp;
						}

						$arr_no_po = implode(',', $arr_no_po);
						$arr_no_po = str_replace(', ', ',', $arr_no_po);

						$get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
						foreach ($get_no_surat as $item_no_surat) {
							$no_po[] = $item_no_surat->no_surat;
						}
					} else {
						$no_po[] = $get_rec_invoice->no_po;
					}
				}

				if (!empty($no_po)) {
					$get_nm_supplier = $this->db
						->select('b.nama as nm_supplier')
						->from('tr_purchase_order a')
						->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
						->where_in('a.no_surat', $no_po)
						->group_by('b.nama')
						->get()
						->result();
					foreach ($get_nm_supplier as $item_supplier) {
						$nm_supplier[] = $item_supplier->nm_supplier;
					}
				}

				$nm_supplier = implode(', ', $nm_supplier);

				$get_choosed_payment = $this->db->get_where('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $item->id])->result();
				$checked = (count($get_choosed_payment) > 0) ? 'checked' : null;

				$option = '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . $checked . '>';

				$hasil[] = [
					'no' => $no,
					'no_dokumen' => $item->no_doc,
					'tgl' => date('d F Y', strtotime($item->created_on)),
					'keperluan' => $item->keperluan,
					'currency' => $item->currency,
					'total_invoice' => number_format($item->jumlah),
					'requestor' => $item->requestor,
					'option' => $option
				];
			}
		} else {
			$this->db->select('a.id, a.created_on, a.no_doc, a.currency, a.jumlah, a.keperluan, a.tipe');
			$this->db->from('payment_approve a');
			$this->db->join('tr_expense b', 'b.no_doc = a.no_doc', 'left');
			$this->db->where('a.status <>', 2);
			$this->db->group_start();
			$this->db->where('b.exp_inv_po <>', 1);
			$this->db->or_where('b.exp_inv_po', null);
			$this->db->group_end();
			if (!empty($search['value'])) {
				$this->db->group_start();
				$this->db->like('a.no_doc', $search['value'], 'both');
				$this->db->or_like('a.created_on', $search['value'], 'both');
				$this->db->or_like('a.keperluan', $search['value'], 'both');
				$this->db->or_like('a.currency', $search['value'], 'both');
				$this->db->or_like('a.jumlah', $search['value'], 'both');
				$this->db->group_end();
			}
			$this->db->order_by('a.created_on', 'desc');
			$this->db->group_by('a.id');

			$db_clone = clone $this->db;
			$count_all = $db_clone->count_all_results();

			$this->db->limit($length, $start);
			$get_data = $this->db->get()->result();

			$hasil = [];

			$no = (0 + $start);
			foreach ($get_data as $item) {
				$no++;

				$get_choosed_payment = $this->db->get_where('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $item->id])->result();

				$checked = (count($get_choosed_payment) > 0) ? 'checked' : null;

				$option = '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . $checked . '>';

				$requestor = '';
				if ($item->tipe == 'kasbon') {
					$get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_kasbon)) ? $get_kasbon->nama : '';
				}
				if ($item->tipe == 'expense') {
					$get_expense = $this->db->get_where('tr_expense', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_expense)) ? $get_expense->nama : '';
				}
				if ($item->tipe == 'transport' || $item->tipe == 'transportasi') {
					$get_transport_req = $this->db->get_where('tr_transport_req', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_transport_req)) ? $get_transport_req->nama : '';
				}

				$hasil[] = [
					'no' => $no,
					'no_dokumen' => $item->no_doc,
					'tgl' => date('d F Y', strtotime($item->created_on)),
					'keperluan' => $item->keperluan,
					'currency' => $item->currency,
					'total_invoice' => number_format($item->jumlah),
					'requestor' => $requestor,
					'option' => $option
				];
			}
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}
}
