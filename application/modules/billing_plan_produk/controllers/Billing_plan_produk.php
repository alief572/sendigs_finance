<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Billing_plan_produk extends Admin_Controller
{

	//Permission

	protected $viewPermission   = "Billing_Plan_Produk.View";
	protected $addPermission    = "Billing_Plan_Produk.Add";
	protected $managePermission = "Billing_Plan_Produk.Manage";
	protected $deletePermission = "Billing_Plan_Produk.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model('Billing_plan_produk/Billing_plan_produk_model');
		$this->template->title('Billing Plan Produk');
		$this->template->page_icon('fa fa-building-o');
		date_default_timezone_set('Asia/Bangkok');
	}


	public function index()
	{
		$get_result = $this->db->select('a.*, b.nm_customer, d.currency')
			->from('tr_sales_order a')
			->join('customer b', 'b.id_customer = a.id_customer', 'left')
			->join('tr_sales_order_detail c', 'c.no_so = a.no_so', 'left')
			->join('tr_penawaran d', 'd.no_penawaran = a.no_penawaran', 'left')
			->where('a.tipe_so', 1)
			->group_by('a.no_so')
			->order_by('a.no_so', 'desc')
			->get()
			->result();

		$this->template->set('results', $get_result);
		$this->template->title('Billing Plan Produk');
		$this->template->render('index');
	}

	public function modal_billing_plan()
	{
		$no_so = $this->input->post('no_so');
		$currency = $this->input->post('currency');

		$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();
		$get_so_detail = $this->db->select('a.*, b.kode as kode_matbom, d.code as uom')
			->from('tr_sales_order_detail a')
			->join('bom_header b', 'b.no_bom = a.no_bom', 'left')
			->join('new_inventory_4 c', 'c.code_lv4 = a.id_category3', 'left')
			->join('ms_satuan d', 'd.id = c.id_unit', 'left')
			->where('a.no_so', $no_so)
			->get()
			->result();

		$get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $get_so->no_penawaran])->result();

		$get_other_item = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $get_so->no_penawaran])->result();


		$get_nm_customer = $this->db->get_where('customer', ['id_customer' => $get_so->id_customer])->row();
		$nm_customer = '';
		if (!empty($get_nm_customer)) {
			$nm_customer = $get_nm_customer->nm_customer;
		}

		$ttl_total_harga = 0;
		$get_ttl_harga = $this->db->select('SUM(a.total_harga) as ttl_harga')
			->from('tr_sales_order_detail a')
			->where('a.no_so', $no_so)
			->get()
			->row();
		if (!empty($get_ttl_harga)) {
			$ttl_total_harga += $get_ttl_harga->ttl_harga;
		}

		$this->db->select('*');
		$this->db->from('spk_delivery');
		$this->db->where('no_so', $no_so);
		$get_spk_delivery = $this->db->get()->result();

		$no_do = array();
		$tanggal_do = array();
		foreach($get_spk_delivery as $item_delivery) {
			$no_do[] = $item_delivery->no_delivery;
			$tanggal_do[] = date('d-m-Y', strtotime($item_delivery->delivery_date));
		}

		$data = [
			'results' => $get_so,
			'results_detail' => $get_so_detail,
			'currency' => $currency,
			'nm_customer' => $nm_customer,
			'ttl_harga' => $ttl_total_harga,
			'detail_other_cost' => $get_other_cost,
			'detail_other_item' => $get_other_item,
			'no_do' => implode(', ', $no_do),
			'tanggal_do' => implode(', ', $tanggal_do)
		];

		$this->template->set($data);
		$this->template->render('modal_billing_plan');
	}

	public function save_billing_plan()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$valid_spk_delivery = 1;

		$arrInvoice = [];

		$no = $post['no'];
		for($x = 1; $x <= $no; $x++) {
			if(isset($post['new_invoice_type_' . $x])) {
				$arrInvoice[] = [
					'id' => $this->Billing_plan_produk_model->generate_id($x),
					'no_so' => $post['no_so'],
					'id_customer' => $post['id_customer'],
					'nm_customer' => $post['nm_customer'],
					'total_so' => $post['ttl_harga'],
					'tipe_billing_plan' => $post['new_invoice_type_' . $x],
					'persen_billing_plan' => str_replace(',', '', $post['new_invoice_persen_' . $x]),
					'value_billing_plan' => str_replace(',', '', $post['new_invoice_value_' . $x]),
					'billing_plan_date' => $post['new_billing_plan_date_' . $x],
					'tipe_so' => $post['tipe_so'],
					'created_by' => $this->auth->user_id(),
					'created_date' => date('Y-m-d H:i:s')
				];
			}
		}

		if(!empty($arrInvoice)) {
			$insert_billing_plan = $this->db->insert_batch('tr_billing_plan', $arrInvoice);
			if(!$insert_billing_plan) {
				print_r($this->db->error($insert_billing_plan));
				$this->db->trans_rollback();
				exit;
			}
		}

		// print_r($valid_spk_delivery);
		// exit;

		if ($this->db->trans_status() === false || $valid_spk_delivery <= 0) {
			$this->db->trans_rollback();

			if ($valid_spk_delivery <= 0) {
				$valid = 2;
			} else {
				$valid = 0;
			}
		} else {
			$this->db->trans_commit();

			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}
}
