<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_instalasi extends Admin_Controller
{

	//Permission

	protected $viewPermission   = "Invoice_instalasi.View";
	protected $addPermission    = "Invoice_instalasi.Add";
	protected $managePermission = "Invoice_instalasi.Manage";
	protected $deletePermission = "Invoice_instalasi.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model('Invoice_instalasi/Invoice_instalasi_model');
		$this->template->title('Invoice Instalasi');
		$this->template->page_icon('fa fa-building-o');
		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);

		$get_list_invoice_dp = $this->db->get_where('tr_billing_plan', ['tipe_billing_plan' => 1, 'tipe_so' => 2])->result();

		$data = [
			'list_invoice_dp' => $get_list_invoice_dp
		];
		$this->template->set($data);
		$this->template->render('index');
	}

	public function create_invoice_modal()
	{
		$no_so = $this->input->post('no_so');
		$id = $this->input->post('id');
		$tipe_billing = $this->input->post('tipe_billing');

		// if ($tipe_billing == 'dp') {
		// 	$get_so_details = $this->db->query("
		// 		SELECT
		// 			a.nama_produk as nama_produk,
		// 			a.qty as qty,
		// 			b.harga_satuan as harga_satuan,
		// 			b.diskon_persen as diskon_persen,
		// 			b.diskon_nilai as diskon_nilai,
		// 			b.total_harga as total_harga,
		// 			d.code as uom
		// 		FROM
		// 			tr_sales_order_detail a
		// 			LEFT JOIN tr_penawaran_detail b ON b.id_penawaran_detail = a.id_penawaran_detail
		// 			LEFT JOIN new_inventory_4 c ON c.code_lv4 = b.id_category3
		// 			LEFT JOIN ms_satuan d ON d.id = c.id_unit
		// 		WHERE
		// 			a.no_so = '" . $no_so . "'

		// 		UNION ALL

		// 		SELECT
		// 			a.nm_other as nama_produk,
		// 			a.qty as qty,
		// 			a.harga as harga_satuan,
		// 			0 as diskon_persen,
		// 			0 as diskon_nilai,
		// 			a.total as total_harga,
		// 			d.code as uom
		// 		FROM
		// 			tr_penawaran_other_item a
		// 			LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
		// 			LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_other
		// 			LEFT JOIN ms_satuan d ON d.id = c.id_unit
		// 		WHERE
		// 			b.no_so = '" . $no_so . "'
		// 	")->result();
		// 	// print_r($this->db->error($get_so_details));
		// 	// exit;

		// 	$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();
		// 	$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $get_so->no_penawaran])->row();
		// 	$get_billing_details = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

		// 	$data = [
		// 		'detail' => $get_so_details,
		// 		'tipe_billing' => $tipe_billing,
		// 		'id_billing' => $id,
		// 		'no_so' => $no_so,
		// 		'billing_details' => $get_billing_details,
		// 		'data_so' => $get_so,
		// 		'data_penawaran' => $get_penawaran
		// 	];
		// 	$this->template->set('results', $data);
		// 	$this->template->render('modal_billing_plan');
		// }

		// if ($tipe_billing == 'progress') {
		// 	$get_so_details = $this->db->query("
		// 		SELECT
		// 			a.nama_produk as nama_produk,
		// 			a.qty as qty,
		// 			b.harga_satuan as harga_satuan,
		// 			b.diskon_persen as diskon_persen,
		// 			b.diskon_nilai as diskon_nilai,
		// 			b.total_harga as total_harga,
		// 			d.code as uom
		// 		FROM
		// 			tr_sales_order_detail a
		// 			LEFT JOIN tr_penawaran_detail b ON b.id_penawaran_detail = a.id_penawaran_detail
		// 			LEFT JOIN new_inventory_4 c ON c.code_lv4 = b.id_category3
		// 			LEFT JOIN ms_satuan d ON d.id = c.id_unit
		// 		WHERE
		// 			a.no_so = '" . $no_so . "'

		// 		UNION ALL

		// 		SELECT
		// 			a.nm_other as nama_produk,
		// 			a.qty as qty,
		// 			a.harga as harga_satuan,
		// 			0 as diskon_persen,
		// 			0 as diskon_nilai,
		// 			a.total as total_harga,
		// 			d.code as uom
		// 		FROM
		// 			tr_penawaran_other_item a
		// 			LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
		// 			LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_other
		// 			LEFT JOIN ms_satuan d ON d.id = c.id_unit
		// 		WHERE
		// 			b.no_so = '" . $no_so . "'
		// 	")->result();
		// 	// print_r($this->db->error($get_so_details));
		// 	// exit;

		// 	$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();
		// 	$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $get_so->no_penawaran])->row();
		// 	$get_billing_details = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

		// 	$data = [
		// 		'detail' => $get_so_details,
		// 		'tipe_billing' => $tipe_billing,
		// 		'id_billing' => $id,
		// 		'no_so' => $no_so,
		// 		'billing_details' => $get_billing_details,
		// 		'data_so' => $get_so,
		// 		'data_penawaran' => $get_penawaran
		// 	];
		// 	$this->template->set('results', $data);
		// 	$this->template->render('modal_billing_plan');
		// }

		// if ($tipe_billing == 'retensi') {
		// 	$get_so_details = $this->db->query("
		// 		SELECT
		// 			a.nama_produk as nama_produk,
		// 			a.qty as qty,
		// 			b.harga_satuan as harga_satuan,
		// 			b.diskon_persen as diskon_persen,
		// 			b.diskon_nilai as diskon_nilai,
		// 			b.total_harga as total_harga,
		// 			d.code as uom
		// 		FROM
		// 			tr_sales_order_detail a
		// 			LEFT JOIN tr_penawaran_detail b ON b.id_penawaran_detail = a.id_penawaran_detail
		// 			LEFT JOIN new_inventory_4 c ON c.code_lv4 = b.id_category3
		// 			LEFT JOIN ms_satuan d ON d.id = c.id_unit
		// 		WHERE
		// 			a.no_so = '" . $no_so . "'

		// 		UNION ALL

		// 		SELECT
		// 			a.nm_other as nama_produk,
		// 			a.qty as qty,
		// 			a.harga as harga_satuan,
		// 			0 as diskon_persen,
		// 			0 as diskon_nilai,
		// 			a.total as total_harga,
		// 			d.code as uom
		// 		FROM
		// 			tr_penawaran_other_item a
		// 			LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
		// 			LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_other
		// 			LEFT JOIN ms_satuan d ON d.id = c.id_unit
		// 		WHERE
		// 			b.no_so = '" . $no_so . "'
		// 	")->result();
		// 	// print_r($this->db->error($get_so_details));
		// 	// exit;

		// 	$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();
		// 	$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $get_so->no_penawaran])->row();
		// 	$get_billing_details = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

		// 	$data = [
		// 		'detail' => $get_so_details,
		// 		'tipe_billing' => $tipe_billing,
		// 		'id_billing' => $id,
		// 		'no_so' => $no_so,
		// 		'billing_details' => $get_billing_details,
		// 		'data_so' => $get_so,
		// 		'data_penawaran' => $get_penawaran
		// 	];
		// 	$this->template->set('results', $data);
		// 	$this->template->render('modal_billing_plan');
		// }

		// if ($tipe_billing == 'jaminan') {
		// 	$get_spk_delivery = $this->db->get_where('spk_delivery', ['no_so' => $no_so])->result();

		// 	$get_billing_plan = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

		// 	$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();

		// 	$get_dp = $this->db->get_where('tr_invoice_sales', ['id_so' => $no_so, 'tipe_billing' => 'dp'])->row();

		// 	$get_penawaran = $this->db
		// 		->select('b.*')
		// 		->from('tr_sales_order a')
		// 		->join('tr_penawaran b', 'b.no_penawaran = a.no_penawaran', 'left')
		// 		->where('a.no_so', $no_so)
		// 		->get()
		// 		->row();

		// 	$data = [
		// 		'list_spk_delivery' => $get_spk_delivery,
		// 		'billing_plan' => $get_billing_plan,
		// 		'no_so' => $no_so,
		// 		'id_billing' => $id,
		// 		'tipe_billing' => $tipe_billing,
		// 		'data_so' => $get_so,
		// 		'data_penawaran' => $get_penawaran,
		// 		'data_dp' => $get_dp
		// 	];
		// 	$this->template->set('results', $data);
		// 	$this->template->render('modal_billing_jaminan');
		// }

		$get_so_details = $this->db->query("
				SELECT
					a.nama_produk as nama_produk,
					a.qty as qty,
					b.harga_satuan as harga_satuan,
					b.diskon_persen as diskon_persen,
					b.diskon_nilai as diskon_nilai,
					b.total_harga as total_harga,
					d.code as uom
				FROM
					tr_sales_order_detail a
					LEFT JOIN tr_penawaran_detail b ON b.id_penawaran_detail = a.id_penawaran_detail
					LEFT JOIN new_inventory_4 c ON c.code_lv4 = b.id_category3
					LEFT JOIN ms_satuan d ON d.id = c.id_unit
				WHERE
					a.no_so = '" . $no_so . "'

				UNION ALL

				SELECT
					a.nm_other as nama_produk,
					a.qty as qty,
					a.harga as harga_satuan,
					0 as diskon_persen,
					0 as diskon_nilai,
					a.total as total_harga,
					d.code as uom
				FROM
					tr_penawaran_other_item a
					LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
					LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_other
					LEFT JOIN ms_satuan d ON d.id = c.id_unit
				WHERE
					b.no_so = '" . $no_so . "'

				UNION ALL

				SELECT
					a.keterangan as nama_produk,
					1 as qty,
					a.total_nilai as harga_satuan,
					0 as diskon_persen,
					0 as diskon_nilai,
					a.total_nilai as total_harga,
					'Pcs' as uom
				FROM
					tr_penawaran_other_cost a
					LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
				WHERE
					b.no_so = '" . $no_so . "'

			")->result();
		// print_r($this->db->error($get_so_details));
		// exit;

		$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();
		$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $get_so->no_penawaran])->row();
		$get_billing_details = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

		$data = [
			'detail' => $get_so_details,
			'tipe_billing' => $tipe_billing,
			'id_billing' => $id,
			'no_so' => $no_so,
			'billing_details' => $get_billing_details,
			'data_so' => $get_so,
			'data_penawaran' => $get_penawaran
		];
		$this->template->set('results', $data);
		$this->template->render('modal_billing_plan');
	}

	public function view_invoice_modal()
	{
		$no_so = $this->input->post('no_so');
		$id = $this->input->post('id');
		$tipe_billing = $this->input->post('tipe_billing');
		$id_invoice = $this->input->post('id_invoice');


		if ($tipe_billing == 'dp') {
			$get_so_details = $this->db->query("
				SELECT
					a.nama_produk as nama_produk,
					a.qty as qty,
					b.harga_satuan as harga_satuan,
					b.diskon_persen as diskon_persen,
					b.diskon_nilai as diskon_nilai,
					b.total_harga as total_harga,
					d.code as uom
				FROM
					tr_sales_order_detail a
					LEFT JOIN tr_penawaran_detail b ON b.id_penawaran_detail = a.id_penawaran_detail
					LEFT JOIN new_inventory_4 c ON c.code_lv4 = b.id_category3
					LEFT JOIN ms_satuan d ON d.id = c.id_unit
				WHERE
					a.no_so = '" . $no_so . "'

				UNION ALL

				SELECT
					a.nm_other as nama_produk,
					a.qty as qty,
					a.harga as harga_satuan,
					0 as diskon_persen,
					0 as diskon_nilai,
					a.total as total_harga,
					d.code as uom
				FROM
					tr_penawaran_other_item a
					LEFT JOIN tr_sales_order b ON b.no_penawaran = a.id_penawaran
					LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_other
					LEFT JOIN ms_satuan d ON d.id = c.id_unit
				WHERE
					b.no_so = '" . $no_so . "'
			")->result();
			// print_r($this->db->error($get_so_details));
			// exit;

			$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();
			$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $get_so->no_penawaran])->row();
			$get_billing_details = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

			$data = [
				'detail' => $get_so_details,
				'tipe_billing' => $tipe_billing,
				'id_billing' => $id,
				'no_so' => $no_so,
				'billing_details' => $get_billing_details,
				'data_so' => $get_so,
				'data_penawaran' => $get_penawaran
			];
			$this->template->set('results', $data);
			$this->template->render('modal_billing_plan');
		}

		if ($tipe_billing == 'delivery') {
			if ($id_invoice !== '') {
				$get_other_cost = $this->db
					->select('b.*')
					->from('tr_sales_order a')
					->join('tr_penawaran_other_cost b', 'b.id_penawaran = a.no_penawaran', 'left')
					->join('tr_used_invoice_sales_other_cost c', 'c.id_other_cost = b.id')
					->where('a.no_so', $no_so)
					->where('c.id_invoice', $id_invoice)
					->get()
					->result();
			} else {
				$get_other_cost = $this->db
					->select('b.*')
					->from('tr_sales_order a')
					->join('tr_penawaran_other_cost b', 'b.id_penawaran = a.no_penawaran', 'left')
					->where('a.no_so', $no_so)
					->get()
					->result();
			}

			$get_so_detail = $this->db
				->query("
					SELECT
						b.nama_produk,
						b.qty,
						a.qty_delivery,
						b.harga_satuan,
						b.diskon_persen,
						b.diskon_nilai,
						b.nama_produk
					FROM
						spk_delivery_detail a
						LEFT JOIN tr_sales_order_detail b ON b.no_so = a.no_so AND b.id_category3 = a.code_lv4
					WHERE
						a.no_so = '" . $no_so . "' AND
						a.no_delivery = '" . $id . "'
					GROUP BY a.id
				")
				->result();

			$persen_dp = 0;
			$get_persen_dp = $this->db->select('a.persen_billing_plan')->get_where('tr_billing_plan a', ['a.no_so' => $no_so, 'a.tipe_billing_plan' => 1])->row();
			if (!empty($get_persen_dp)) {
				$persen_dp = $get_persen_dp->persen_billing_plan;
			}

			$persen_retensi = 0;
			$get_persen_retensi = $this->db->select('a.persen_billing_plan')->get_where('tr_billing_plan a', ['a.no_so' => $no_so, 'a.tipe_billing_plan' => 2])->row();
			if (!empty($get_persen_retensi)) {
				$persen_retensi = $get_persen_retensi->persen_billing_plan;
			}

			$persen_jaminan = 0;
			$get_persen_jaminan = $this->db->select('a.persen_billing_plan')->get_where('tr_billing_plan a', ['a.no_so' => $no_so, 'a.tipe_billing_plan' => 3])->row();
			if (!empty($get_persen_jaminan)) {
				$persen_jaminan = $get_persen_jaminan->persen_billing_plan;
			}

			$get_penawaran = $this->db
				->select('b.*')
				->from('tr_sales_order a')
				->join('tr_penawaran b', 'b.no_penawaran = a.no_penawaran', 'left')
				->where('a.no_so', $no_so)
				->get()
				->row();

			$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();
			// $get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $get_so->no_penawaran])->row();

			$data = [
				'tipe_billing' => $tipe_billing,
				'id_billing' => $id,
				'no_so' => $no_so,
				'list_other_cost' => $get_other_cost,
				'list_so_detail' => $get_so_detail,
				'persen_dp' => $persen_dp,
				'persen_retensi' => $persen_retensi,
				'persen_jaminan' => $persen_jaminan,
				'currency' => $get_penawaran->currency,
				'persen_ppn' => $get_penawaran->ppn,
				'data_so' => $get_so,
				'data_penawaran' => $get_penawaran,
				'view' => 1
			];

			$this->template->set('results', $data);
			$this->template->render('modal_billing_delivery');
		}

		if ($tipe_billing == 'retensi') {
			$get_spk_delivery = $this->db->get_where('spk_delivery', ['no_so' => $no_so])->result();

			$get_billing_plan = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

			$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();

			$get_penawaran = $this->db
				->select('b.*')
				->from('tr_sales_order a')
				->join('tr_penawaran b', 'b.no_penawaran = a.no_penawaran', 'left')
				->where('a.no_so', $no_so)
				->get()
				->row();

			$data = [
				'list_spk_delivery' => $get_spk_delivery,
				'billing_plan' => $get_billing_plan,
				'no_so' => $no_so,
				'id_billing' => $id,
				'tipe_billing' => $tipe_billing,
				'data_so' => $get_so,
				'data_penawaran' => $get_penawaran
			];
			$this->template->set('results', $data);
			$this->template->render('modal_billing_retensi');
		}

		if ($tipe_billing == 'jaminan') {
			$get_spk_delivery = $this->db->get_where('spk_delivery', ['no_so' => $no_so])->result();

			$get_billing_plan = $this->db->get_where('tr_billing_plan', ['id' => $id])->row();

			$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $no_so])->row();

			$get_dp = $this->db->get_where('tr_invoice_sales', ['id_so' => $no_so, 'tipe_billing' => 'dp'])->row();

			$get_penawaran = $this->db
				->select('b.*')
				->from('tr_sales_order a')
				->join('tr_penawaran b', 'b.no_penawaran = a.no_penawaran', 'left')
				->where('a.no_so', $no_so)
				->get()
				->row();

			$data = [
				'list_spk_delivery' => $get_spk_delivery,
				'billing_plan' => $get_billing_plan,
				'no_so' => $no_so,
				'id_billing' => $id,
				'tipe_billing' => $tipe_billing,
				'data_so' => $get_so,
				'data_penawaran' => $get_penawaran,
				'data_dp' => $get_dp
			];
			$this->template->set('results', $data);
			$this->template->render('modal_billing_jaminan');
		}
	}

	public function create_invoice()
	{
		$post = $this->input->post();

		$id_invoice = $this->Invoice_instalasi_model->generate_id_invoice();

		$this->db->trans_begin();

		// if ($post['tipe_billing'] == 'dp') {
		// 	$data_insert = [
		// 		'id_invoice' => $id_invoice,
		// 		'id_so' => $post['no_so'],
		// 		'tipe_so' => $post['tipe_so'],
		// 		'id_penawaran' => $post['id_penawaran'],
		// 		'id_billing' => $post['id_billing'],
		// 		'tipe_billing' => $post['tipe_billing'],
		// 		'nilai_dpp' => $post['nilai_dpp'],
		// 		'nilai_asli' => $post['nilai_asli'],
		// 		'nilai_invoice' => $post['nilai_invoice'],
		// 		'persen_invoice' => $post['persen_invoice'],
		// 		'ppn' => $post['ppn'],
		// 		'nilai_ppn' => $post['nilai_ppn'],
		// 		'grand_total' => $post['grand_total'],
		// 		'created_by' => $this->auth->user_id(),
		// 		'created_on' => date('Y-m-d H:i:s')
		// 	];

		// 	$insert_invoice = $this->db->insert('tr_invoice_sales', $data_insert);

		// 	$data_insert_detail = [];
		// 	$get_so_detail = $this->db->query("
		// 		SELECT
		// 			a.id_category3 as id_produk,
		// 			a.nama_produk as nama_produk,
		// 			a.harga_satuan as harga_satuan,
		// 			a.qty as qty,
		// 			a.diskon_nilai as diskon_nilai,
		// 			a.total_harga as total_harga,
		// 			b.tipe_so as tipe_so,
		// 			d.code as uom
		// 		FROM
		// 			tr_sales_order_detail a
		// 			LEFT JOIN tr_sales_order b ON b.no_so = a.no_so
		// 			LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_category3
		// 			LEFT JOIN ms_satuan d ON d.id = c.id_unit
		// 		WHERE
		// 			a.no_so = '" . $post['no_so'] . "'
		// 	")->result();
		// 	foreach ($get_so_detail as $item_detail) {
		// 		$data_insert_detail[] = [
		// 			'id_invoice' => $id_invoice,
		// 			'id_so' => $post['no_so'],
		// 			'tipe_so' => $post['tipe_so'],
		// 			'id_penawaran' => $post['id_penawaran'],
		// 			'id_produk' => $item_detail->id_produk,
		// 			'nm_produk' => $item_detail->nama_produk,
		// 			'qty' => $item_detail->qty,
		// 			'uom' => $item_detail->uom,
		// 			'harga' => $item_detail->harga_satuan,
		// 			'disc' => $item_detail->diskon_nilai,
		// 			'subtotal' => $item_detail->total_harga,
		// 			'created_by' => $this->auth->user_id(),
		// 			'created_on' => date('Y-m-d H:i:s')
		// 		];
		// 	}

		// 	$insert_invoice_details = $this->db->insert_batch('tr_invoice_sales_detail', $data_insert_detail);
		// }

		// if ($post['tipe_billing'] == 'delivery') {

		// }

		// if ($post['tipe_billing'] == 'retensi') {
		// 	$data_insert = [
		// 		'id_invoice' => $id_invoice,
		// 		'id_so' => $post['no_so'],
		// 		'tipe_so' => $post['tipe_so'],
		// 		'id_penawaran' => $post['id_penawaran'],
		// 		'id_billing' => $post['id_billing'],
		// 		'tipe_billing' => $post['tipe_billing'],
		// 		'nilai_dpp' => $post['nilai_dpp'],
		// 		'nilai_asli' => $post['nilai_asli'],
		// 		'nilai_invoice' => $post['nilai_invoice'],
		// 		'ppn' => $post['ppn'],
		// 		'nilai_ppn' => $post['nilai_ppn'],
		// 		'grand_total' => $post['grand_total'],
		// 		'created_by' => $this->auth->user_id(),
		// 		'created_on' => date('Y-m-d H:i:s')
		// 	];
		// 	$insert_invoice = $this->db->insert('tr_invoice_sales', $data_insert);

		// 	$data_insert_detail_invoice = [];
		// 	$get_spk_delivery_details = $this->db
		// 		->select('a.code_lv4, a.qty_delivery, b.nama_produk, b.harga_satuan, b.diskon_persen, d.code as uom')
		// 		->from('spk_delivery_detail a')
		// 		->join('tr_sales_order_detail b', 'b.no_so = a.no_so AND b.id_category3 = a.code_lv4', 'left')
		// 		->join('new_inventory_4 c', 'c.code_lv4 = a.code_lv4', 'left')
		// 		->join('ms_satuan d', 'd.id = c.id_unit', 'left')
		// 		->where('a.no_so', $post['no_so'])
		// 		->group_by('a.id')
		// 		->get()
		// 		->result();

		// 	// print_r($this->db->last_query());
		// 	// exit;

		// 	foreach ($get_spk_delivery_details as $item_details) {
		// 		$nilai_disc = (float) ($item_details->harga_satuan * $item_details->diskon_persen / 100);
		// 		$subtotal = (float) (($item_details->harga_satuan - $nilai_disc) * $item_details->qty_delivery);

		// 		$data_insert_detail_invoice[] = [
		// 			'id_invoice' => $id_invoice,
		// 			'id_so' => $post['no_so'],
		// 			'tipe_so' => $post['tipe_so'],
		// 			'id_penawaran' => $post['id_penawaran'],
		// 			'id_produk' => $item_details->code_lv4,
		// 			'nm_produk' => $item_details->nama_produk,
		// 			'qty' => $item_details->qty_delivery,
		// 			'uom' => $item_details->uom,
		// 			'harga' => $item_details->harga_satuan,
		// 			'disc' => $nilai_disc,
		// 			'subtotal' => $subtotal,
		// 			'created_by' => $this->auth->user_id(),
		// 			'created_on' => date('Y-m-d H:i:s')
		// 		];
		// 	}

		// 	$insert_invoice_details = $this->db->insert_batch('tr_invoice_sales_detail', $data_insert_detail_invoice);
		// }

		// if ($post['tipe_billing'] == 'jaminan') {
		// 	$data_insert = [
		// 		'id_invoice' => $id_invoice,
		// 		'id_so' => $post['no_so'],
		// 		'tipe_so' => $post['tipe_so'],
		// 		'id_penawaran' => $post['id_penawaran'],
		// 		'id_billing' => $post['id_billing'],
		// 		'tipe_billing' => $post['tipe_billing'],
		// 		'nilai_dpp' => $post['nilai_dpp'],
		// 		'nilai_asli' => $post['nilai_asli'],
		// 		'nilai_invoice' => $post['nilai_invoice'],
		// 		'ppn' => $post['ppn'],
		// 		'nilai_ppn' => $post['nilai_ppn'],
		// 		'grand_total' => $post['grand_total'],
		// 		'created_by' => $this->auth->user_id(),
		// 		'created_on' => date('Y-m-d H:i:s')
		// 	];
		// 	$insert_invoice = $this->db->insert('tr_invoice_sales', $data_insert);

		// 	$data_insert_detail_invoice = [];
		// 	$get_spk_delivery_details = $this->db
		// 		->select('a.code_lv4, a.qty_delivery, b.nama_produk, b.harga_satuan, b.diskon_persen, d.code as uom')
		// 		->from('spk_delivery_detail a')
		// 		->join('tr_sales_order_detail b', 'b.no_so = a.no_so AND b.id_category3 = a.code_lv4', 'left')
		// 		->join('new_inventory_4 c', 'c.code_lv4 = a.code_lv4', 'left')
		// 		->join('ms_satuan d', 'd.id = c.id_unit', 'left')
		// 		->where('a.no_so', $post['no_so'])
		// 		->group_by('a.id')
		// 		->get()
		// 		->result();

		// 	// print_r($this->db->last_query());
		// 	// exit;

		// 	foreach ($get_spk_delivery_details as $item_details) {
		// 		$nilai_disc = (float) ($item_details->harga_satuan * $item_details->diskon_persen / 100);
		// 		$subtotal = (float) (($item_details->harga_satuan - $nilai_disc) * $item_details->qty_delivery);

		// 		$data_insert_detail_invoice[] = [
		// 			'id_invoice' => $id_invoice,
		// 			'id_so' => $post['no_so'],
		// 			'tipe_so' => $post['tipe_so'],
		// 			'id_penawaran' => $post['id_penawaran'],
		// 			'id_produk' => $item_details->code_lv4,
		// 			'nm_produk' => $item_details->nama_produk,
		// 			'qty' => $item_details->qty_delivery,
		// 			'uom' => $item_details->uom,
		// 			'harga' => $item_details->harga_satuan,
		// 			'disc' => $nilai_disc,
		// 			'subtotal' => $subtotal,
		// 			'created_by' => $this->auth->user_id(),
		// 			'created_on' => date('Y-m-d H:i:s')
		// 		];
		// 	}

		// 	$insert_invoice_details = $this->db->insert_batch('tr_invoice_sales_detail', $data_insert_detail_invoice);
		// }

		$data_insert = [
			'id_invoice' => $id_invoice,
			'id_so' => $post['no_so'],
			'tipe_so' => $post['tipe_so'],
			'id_penawaran' => $post['id_penawaran'],
			'id_billing' => $post['id_billing'],
			'tipe_billing' => $post['tipe_billing'],
			'nilai_dpp' => $post['nilai_dpp'],
			'nilai_asli' => $post['nilai_asli'],
			'nilai_invoice' => $post['nilai_invoice'],
			'persen_invoice' => $post['persen_invoice'],
			'ppn' => $post['ppn'],
			'nilai_ppn' => $post['nilai_ppn'],
			'grand_total' => $post['grand_total'],
			'created_by' => $this->auth->user_id(),
			'created_on' => date('Y-m-d H:i:s')
		];

		$insert_invoice = $this->db->insert('tr_invoice_sales', $data_insert);

		$data_insert_detail = [];
		$get_so_detail = $this->db->query("
			SELECT
				a.id_category3 as id_produk,
				a.nama_produk as nama_produk,
				a.harga_satuan as harga_satuan,
				a.qty as qty,
				a.diskon_nilai as diskon_nilai,
				a.total_harga as total_harga,
				b.tipe_so as tipe_so,
				d.code as uom
			FROM
				tr_sales_order_detail a
				LEFT JOIN tr_sales_order b ON b.no_so = a.no_so
				LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_category3
				LEFT JOIN ms_satuan d ON d.id = c.id_unit
			WHERE
				a.no_so = '" . $post['no_so'] . "'
		")->result();
		foreach ($get_so_detail as $item_detail) {
			$data_insert_detail[] = [
				'id_invoice' => $id_invoice,
				'id_so' => $post['no_so'],
				'tipe_so' => $post['tipe_so'],
				'id_penawaran' => $post['id_penawaran'],
				'id_produk' => $item_detail->id_produk,
				'nm_produk' => $item_detail->nama_produk,
				'qty' => $item_detail->qty,
				'uom' => $item_detail->uom,
				'harga' => $item_detail->harga_satuan,
				'disc' => $item_detail->diskon_nilai,
				'subtotal' => $item_detail->total_harga,
				'created_by' => $this->auth->user_id(),
				'created_on' => date('Y-m-d H:i:s')
			];
		}

		$insert_invoice_details = $this->db->insert_batch('tr_invoice_sales_detail', $data_insert_detail);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}

	public function change_tab()
	{
		$tipe = $this->input->post('tipe');

		$hasil = '<table class="table table-bordered datatable">';

		if ($tipe == 'dp') {
			$hasil .= '
				<thead>
					<tr>
						<th class="text-center">No. SO</th>
						<th class="text-center">No. Invoice</th>
						<th class="text-center">Remarks</th>
						<th class="text-center">Customer Name</th>
						<th class="text-center">SO</th>
						<th class="text-center">Invoiced</th>
						<th class="text-center">Outstanding Invoice</th>
						<th class="text-center">Billing Plan Date</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
			';

			$get_list_invoice_dp = $this->db->get_where('tr_billing_plan', ['tipe_billing_plan' => 1, 'tipe_so' => 2])->result();
			foreach ($get_list_invoice_dp as $item) {

				$invoiced_value = 0;
				$get_invoiced_value = $this->db->select('IF(a.nilai_invoice IS NULL, 0,  SUM(a.nilai_invoice)) AS invoiced_value')->get_where('tr_invoice_sales a', ['id_so' => $item->no_so])->row();
				if (!empty($get_invoiced_value)) {
					$invoiced_value = $get_invoiced_value->invoiced_value;
				}

				$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $item->no_so])->row();

				$other_so = 0;
				$get_other_cost = $this->db->select('SUM(total_nilai) AS ttl_other_cost')->get_where('tr_penawaran_other_cost', ['id_penawaran' => $get_so->no_penawaran])->row();
				if (!empty($get_other_cost)) {
					$other_so += $get_other_cost->ttl_other_cost;
				}
				$get_other_item = $this->db->select('SUM(total) as ttl_other_item')->get_where('tr_penawaran_other_item', ['id_penawaran' => $get_so->no_penawaran])->row();
				if (!empty($get_other_item)) {
					$other_so += $get_other_item->ttl_other_item;
				}

				$hasil .= '<tr>';
				$hasil .= '<td class="text-center">' . $item->no_so . '</td>';
				$hasil .= '<td class="text-center">' . $item->id . '</td>';
				$hasil .= '<td class="text-left">' . $item->keterangan . '</td>';
				$hasil .= '<td class="text-center">' . $item->nm_customer . '</td>';
				$hasil .= '<td class="text-right">' . number_format(($item->total_so + $other_so), 2) . '</td>';
				$hasil .= '<td class="text-right">' . number_format($invoiced_value, 2) . '</td>';
				$hasil .= '<td class="text-right">' . number_format(($item->total_so + $other_so) - $invoiced_value, 2) . '</td>';
				$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item->billing_plan_date)) . '</td>';

				$edit = '<button type="button" class="btn btn-sm btn-success create_invoice_modal" data-no_so="' . $item->no_so . '" data-id="' . $item->id . '" data-tipe_billing="dp" title="Create"><i class="fa fa-check"></i></button>';

				$view = '<button type="button" class="btn btn-sm btn-info view_invoice_modal" data-no_so="' . $item->no_so . '" data-id="' . $item->id . '" data-tipe_billing="dp"><i class="fa fa-eye"></i></button>';

				$id_invoice = '';
				$get_id_invoice = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item->id])->row();
				if (!empty($get_id_invoice)) {
					$id_invoice = $get_id_invoice->id_invoice;
				}

				$print = '<a href="invoice_instalasi/print_invoice_dp/' . $id_invoice . '" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-print"></i></a>';

				$check_invoice_dp = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item->id, 'tipe_billing' => 'dp'])->num_rows();
				if ($check_invoice_dp > 0) {
					$button = $view . ' ' . $print;
				} else {
					$button = $edit;
				}

				$hasil .=	 '<td class="text-center">
								' . $button . '
							</td>';
				$hasil .=	 '</tr>';
			}

			$hasil .= '</tbody>';
		}

		if ($tipe == 'progress') {
			$hasil .= '
				<thead>
					<tr>
						<th class="text-center">No. SO</th>
						<th class="text-center">No. Invoice</th>
						<th class="text-center">Remarks</th>
						<th class="text-center">Customer Name</th>
						<th class="text-center">Invoice</th>
						<th class="text-center">Billing Plan Date</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
			';

			$get_list_invoice_dp = $this->db->get_where('tr_billing_plan', ['tipe_billing_plan' => 2, 'tipe_so' => 2])->result();
			foreach ($get_list_invoice_dp as $item) {

				$invoiced_value = 0;
				$get_invoiced_value = $this->db->select('IF(a.nilai_invoice IS NULL, 0,  SUM(a.nilai_invoice)) AS invoiced_value')->get_where('tr_invoice_sales a', ['id_so' => $item->no_so])->row();
				if (!empty($get_invoiced_value)) {
					$invoiced_value = $get_invoiced_value->invoiced_value;
				}

				$hasil .= '<tr>';
				$hasil .= '<td class="text-center">' . $item->no_so . '</td>';
				$hasil .= '<td class="text-center">' . $item->id . '</td>';
				$hasil .= '<td class="text-left">' . $item->keterangan . '</td>';
				$hasil .= '<td class="text-center">' . $item->nm_customer . '</td>';
				$hasil .= '<td class="text-right">' . number_format($item->value_billing_plan, 2) . '</td>';
				$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item->billing_plan_date)) . '</td>';

				$edit = '<button type="button" class="btn btn-sm btn-success create_invoice_modal" data-no_so="' . $item->no_so . '" data-id="' . $item->id . '" data-tipe_billing="progress" title="Create"><i class="fa fa-check"></i></button>';

				$view = '<button type="button" class="btn btn-sm btn-info view_invoice_modal" data-no_so="' . $item->no_so . '" data-id="' . $item->id . '" data-tipe_billing="progress"><i class="fa fa-eye"></i></button>';

				$id_invoice = '';
				$get_id_invoice = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item->id])->row();
				if (!empty($get_id_invoice)) {
					$id_invoice = $get_id_invoice->id_invoice;
				}

				$print = '<a href="invoice_instalasi/print_invoice_progress/' . $id_invoice . '" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-print"></i></a>';

				$check_invoice_dp = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item->id, 'tipe_billing' => 'progress'])->num_rows();
				if ($check_invoice_dp > 0) {
					$button = $view . ' ' . $print;
				} else {
					$button = $edit;
				}

				$hasil .=	 '<td class="text-center">
								' . $button . '
							</td>';
				$hasil .=	 '</tr>';
			}

			$hasil .= '</tbody>';
		}

		if ($tipe == 'retensi') {
			$hasil .= '
				<thead>
					<tr>
						<th class="text-center">No. SO</th>
						<th class="text-center">No. Invoice</th>
						<th class="text-center">Remarks</th>
						<th class="text-center">Nama Customer</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
			';

			$get_retensi = $this->db
				->select('a.*')
				->from('tr_billing_plan a')
				->where('a.tipe_billing_plan', 3)
				->where('a.tipe_so', 2)
				->get()
				->result();

			foreach ($get_retensi as $item_retensi) {

				$check_invoice_retensi = $this->db->get_where('tr_invoice_sales', ['id_so' => $item_retensi->no_so, 'id_billing' => $item_retensi->id])->num_rows();

				$edit = '<button type="button" class="btn btn-sm btn-success create_invoice_modal" data-no_so="' . $item_retensi->no_so . '" data-id="' . $item_retensi->id . '" data-tipe_billing="retensi" title="Create"><i class="fa fa-check"></i></button>';

				$view = '<button type="button" class="btn btn-sm btn-info view_invoice_modal" data-no_so="' . $item_retensi->no_so . '" data-id="' . $item_retensi->id . '" data-tipe_billing="retensi"><i class="fa fa-eye"></i></button>';

				$id_invoice = '';
				$get_id_invoice = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item_retensi->id])->row();
				if (!empty($get_id_invoice)) {
					$id_invoice = $get_id_invoice->id_invoice;
				}

				$print = '<a href="invoice_instalasi/print_invoice_retensi/' . $id_invoice . '" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-print"></i></a>';

				if ($check_invoice_retensi > 0) {
					$button = $view . ' ' . $print;
				} else {
					$button = $edit;
				}

				$hasil .= '<tr>
					<td class="text-center">' . $item_retensi->no_so . '</td>
					<td class="text-center">' . $item_retensi->id . '</td>
					<td class="text-left">' . $item_retensi->keterangan . '</td>
					<td class="text-left">' . $item_retensi->nm_customer . '</td>
					<td class="text-center">' . $button . '</td>
				</tr>';
			}
			$hasil .= '</tbody>';
		}

		if ($tipe == 'jaminan') {
			$hasil .= '
				<thead>
					<tr>
						<th class="text-center">No. SO</th>
						<th class="text-center">No. Invoice</th>
						<th class="text-left">Remarks</th>
						<th class="text-center">Nama Customer</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
			';

			$get_jaminan = $this->db
				->select('a.*')
				->from('tr_billing_plan a')
				->where('a.tipe_billing_plan', 4)
				->where('a.tipe_so', 2)
				->get()
				->result();

			foreach ($get_jaminan as $item_jaminan) {

				$check_invoice_jaminan = $this->db->get_where('tr_invoice_sales', ['id_so' => $item_jaminan->no_so, 'id_billing' => $item_jaminan->id])->num_rows();

				$edit = '<button type="button" class="btn btn-sm btn-success create_invoice_modal" data-no_so="' . $item_jaminan->no_so . '" data-id="' . $item_jaminan->id . '" data-tipe_billing="jaminan" title="Create"><i class="fa fa-check"></i></button>';

				$view = '<button type="button" class="btn btn-sm btn-info view_invoice_modal" data-no_so="' . $item_jaminan->no_so . '" data-id="' . $item_jaminan->id . '" data-tipe_billing="jaminan"><i class="fa fa-eye"></i></button>';

				$id_invoice = '';
				$get_id_invoice = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item_jaminan->id])->row();
				if (!empty($get_id_invoice)) {
					$id_invoice = $get_id_invoice->id_invoice;
				}

				$print = '<a href="invoice_instalasi/print_invoice_jaminan/' . $id_invoice . '" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-print"></i></a>';

				if ($check_invoice_jaminan > 0) {
					$button = $view . ' ' . $print;
				} else {
					$button = $edit;
				}

				$hasil .= '<tr>
						<td class="text-center">' . $item_jaminan->no_so . '</td>
						<td class="text-center">' . $item_jaminan->id . '</td>
						<td class="text-left">' . $item_jaminan->keterangan . '</td>
						<td class="text-left">' . $item_jaminan->nm_customer . '</td>
						<td class="text-center">' . $button . '</td>
					</tr>';
			}
			$hasil .= '</tbody>';
		}

		$hasil .= '</table>';

		echo json_encode([
			'hasil' => $hasil
		]);
	}

	public function hitung_delivery_w_other_cost()
	{
		$post = $this->input->post();
		$id_billing = $post['id_billing'];
		$no_so = $post['no_so'];
		$tipe_billing = $post['tipe_billing'];
		$nilai_other_cost = $post['nilai_other_cost'];

		$get_other_cost = $this->db
			->select('b.*')
			->from('tr_sales_order a')
			->join('tr_penawaran_other_cost b', 'b.id_penawaran = a.no_penawaran', 'left')
			->where('a.no_so', $no_so)
			->get()
			->result();

		$get_so_detail = $this->db
			->query("
					SELECT
						b.nama_produk,
						b.qty,
						a.qty_delivery,
						b.harga_satuan,
						b.diskon_persen,
						b.diskon_nilai,
						b.nama_produk
					FROM
						spk_delivery_detail a
						LEFT JOIN tr_sales_order_detail b ON b.no_so = a.no_so AND b.id_category3 = a.code_lv4
					WHERE
						a.no_so = '" . $no_so . "' AND
						a.no_delivery = '" . $id_billing . "'
					GROUP BY a.id
				")
			->result();

		$persen_dp = 0;
		$get_persen_dp = $this->db->select('a.persen_billing_plan')->get_where('tr_billing_plan a', ['a.no_so' => $no_so, 'a.tipe_billing_plan' => 1])->row();
		if (!empty($get_persen_dp)) {
			$persen_dp = $get_persen_dp->persen_billing_plan;
		}

		$persen_retensi = 0;
		$get_persen_retensi = $this->db->select('a.persen_billing_plan')->get_where('tr_billing_plan a', ['a.no_so' => $no_so, 'a.tipe_billing_plan' => 2])->row();
		if (!empty($get_persen_retensi)) {
			$persen_retensi = $get_persen_retensi->persen_billing_plan;
		}

		$persen_jaminan = 0;
		$get_persen_jaminan = $this->db->select('a.persen_billing_plan')->get_where('tr_billing_plan a', ['a.no_so' => $no_so, 'a.tipe_billing_plan' => 3])->row();
		if (!empty($get_persen_jaminan)) {
			$persen_jaminan = $get_persen_jaminan->persen_billing_plan;
		}

		$get_penawaran = $this->db
			->select('b.*')
			->from('tr_sales_order a')
			->join('tr_penawaran b', 'b.no_penawaran = a.no_penawaran', 'left')
			->where('a.no_so', $no_so)
			->get()
			->row();
		$persen_ppn = $get_penawaran->ppn;

		$subtotal = 0;
		foreach ($get_so_detail as $item_detail) {
			$nilai_disc = (float) ($item_detail->harga_satuan * $item_detail->disc_persen / 100);
			$total_harga = (($item_detail->harga_satuan - $nilai_disc) * $item_detail->qty_delivery);

			$subtotal += $total_harga;
		}

		$dp_proporsional = ($subtotal * $persen_dp / 100);
		$retensi_proporsional = ($subtotal * $persen_retensi / 100);
		$jaminan_proporsional = ($subtotal * $persen_jaminan / 100);

		$dpp = ($subtotal - $dp_proporsional - $retensi_proporsional);
		$nilai_ppn = (($dpp + $nilai_other_cost) * $persen_ppn / 100);
		$total_all = ($dpp + $nilai_ppn);
		$total_tagihan = ($total_all + $jaminan_proporsional);

		$nilai_invoice = ($dpp + $nilai_other_cost);

		$hasil = '
			<tr>
				<td class="text-right" colspan="6">Total</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($subtotal, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">DP Proporsional (' . number_format($persen_dp, 2) . '%)</td>
				<td class="text-right">(' . $get_penawaran->currency . ')  ' . number_format($dp_proporsional, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">Retensi Proporsional (' . number_format($persen_retensi, 2) . '%)</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($retensi_proporsional, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">DPP</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($dpp, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">Other Cost</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($nilai_other_cost, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">PPn (' . number_format($persen_ppn, 2) . '%)</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($nilai_ppn, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">Total</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($total_all, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">Jaminan</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($jaminan_proporsional, 2) . '</td>
			</tr>
			<tr>
				<td class="text-right" colspan="6">Total Tagihan</td>
				<td class="text-right">(' . $get_penawaran->currency . ') ' . number_format($total_tagihan, 2) . '</td>
			</tr>
		';

		echo json_encode([
			'hasil' => $hasil,
			'total_tagihan' => $total_tagihan,
			'nilai_ppn' => $nilai_ppn,
			'nilai_invoice' => $nilai_invoice
		]);
	}

	public function print_invoice_dp()
	{
		ob_clean();
		ob_start();

		$id_invoice = $this->uri->segment(3);

		$get_invoice = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $id_invoice])->row();
		// $get_invoice_detail = $this->db->get_where('tr_invoice_sales_detail', ['id_invoice' => $id_invoice])->result();
		$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $get_invoice->id_so])->row();
		$get_customer = $this->db->get_where('customer', ['id_customer' => $get_so->id_customer])->row();
		$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $get_so->no_penawaran])->row();

		$this->db->select('
			a.id_category3 as id_product,
			a.nama_produk as nm_produk,
			a.qty as qty,
			a.harga_satuan as harga,
			a.diskon_nilai as disc,
			a.total_harga as subtotal
		');
		$this->db->from('tr_sales_order_detail a');
		$this->db->where('a.no_so', $get_so->no_so);
		$this->db->group_by('a.id_so_detail');
		$get_invoice_detail1 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.keterangan as nm_produk,
			1 as qty,
			a.total_nilai as harga,
			0 as disc,
			a.total_nilai as subtotal
		');
		$this->db->from('tr_penawaran_other_cost a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail2 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.nm_other as nm_produk,
			a.qty as qty,
			a.harga as harga,
			0 as disc,
			a.total as subtotal
		');
		$this->db->from('tr_penawaran_other_item a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail3 = $this->db->get_compiled_select();

		$query_detail_invoice = $get_invoice_detail1 . ' UNION ALL ' . $get_invoice_detail2 . ' UNION ALL ' . $get_invoice_detail3;
		$get_invoice_detail = $this->db->query($query_detail_invoice)->result();

		$data = [
			'id_invoice' => $id_invoice,
			'data_invoice' => $get_invoice,
			'data_invoice_detail' => $get_invoice_detail,
			'data_so' => $get_so,
			'data_customer' => $get_customer,
			'data_penawaran' => $get_penawaran
		];

		$this->load->view('print_invoice_dp', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html = ob_get_contents();

		$html2pdf->WriteHTML($html);

		ob_end_clean();
		$html2pdf->Output('Invoice DP.pdf', 'I');
	}

	public function print_invoice_progress()
	{
		ob_clean();
		ob_start();

		$id_invoice = $this->uri->segment(3);

		$get_invoice = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $id_invoice])->row();
		// $get_invoice_detail = $this->db->get_where('tr_invoice_sales_detail', ['id_invoice' => $id_invoice])->result();
		$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $get_invoice->id_so])->row();
		$get_customer = $this->db->get_where('customer', ['id_customer' => $get_so->id_customer])->row();

		$this->db->select('
			a.id_category3 as id_product,
			a.nama_produk as nm_produk,
			a.qty as qty,
			a.harga_satuan as harga,
			a.diskon_nilai as disc,
			a.total_harga as subtotal
		');
		$this->db->from('tr_sales_order_detail a');
		$this->db->where('a.no_so', $get_so->no_so);
		$this->db->group_by('a.id_so_detail');
		$get_invoice_detail1 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.keterangan as nm_produk,
			1 as qty,
			a.total_nilai as harga,
			0 as disc,
			a.total_nilai as subtotal
		');
		$this->db->from('tr_penawaran_other_cost a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail2 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.nm_other as nm_produk,
			a.qty as qty,
			a.harga as harga,
			0 as disc,
			a.total as subtotal
		');
		$this->db->from('tr_penawaran_other_item a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail3 = $this->db->get_compiled_select();

		$query_detail_invoice = $get_invoice_detail1 . ' UNION ALL ' . $get_invoice_detail2 . ' UNION ALL ' . $get_invoice_detail3;
		$get_invoice_detail = $this->db->query($query_detail_invoice)->result();

		$data = [
			'id_invoice' => $id_invoice,
			'data_invoice' => $get_invoice,
			'data_invoice_detail' => $get_invoice_detail,
			'data_so' => $get_so,
			'data_customer' => $get_customer
		];

		$this->load->view('print_invoice_progress', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html = ob_get_contents();

		$html2pdf->WriteHTML($html);

		ob_end_clean();
		$html2pdf->Output('Invoice DP.pdf', 'I');
	}

	public function print_invoice_retensi()
	{
		ob_clean();
		ob_start();

		$id_invoice = $this->uri->segment(3);

		$get_invoice = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $id_invoice])->row();
		// $get_invoice_detail = $this->db->get_where('tr_invoice_sales_detail', ['id_invoice' => $id_invoice])->result();
		$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $get_invoice->id_so])->row();
		$get_customer = $this->db->get_where('customer', ['id_customer' => $get_so->id_customer])->row();

		$this->db->select('
			a.id_category3 as id_product,
			a.nama_produk as nm_produk,
			a.qty as qty,
			a.harga_satuan as harga,
			a.diskon_nilai as disc,
			a.total_harga as subtotal
		');
		$this->db->from('tr_sales_order_detail a');
		$this->db->where('a.no_so', $get_so->no_so);
		$this->db->group_by('a.id_so_detail');
		$get_invoice_detail1 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.keterangan as nm_produk,
			1 as qty,
			a.total_nilai as harga,
			0 as disc,
			a.total_nilai as subtotal
		');
		$this->db->from('tr_penawaran_other_cost a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail2 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.nm_other as nm_produk,
			a.qty as qty,
			a.harga as harga,
			0 as disc,
			a.total as subtotal
		');
		$this->db->from('tr_penawaran_other_item a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail3 = $this->db->get_compiled_select();

		$query_detail_invoice = $get_invoice_detail1 . ' UNION ALL ' . $get_invoice_detail2 . ' UNION ALL ' . $get_invoice_detail3;
		$get_invoice_detail = $this->db->query($query_detail_invoice)->result();


		$data = [
			'id_invoice' => $id_invoice,
			'data_invoice' => $get_invoice,
			'data_invoice_detail' => $get_invoice_detail,
			'data_so' => $get_so,
			'data_customer' => $get_customer
		];

		$this->load->view('print_invoice_retensi', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html = ob_get_contents();

		$html2pdf->WriteHTML($html);

		ob_end_clean();
		$html2pdf->Output('Invoice Retensi.pdf', 'I');
	}

	public function print_invoice_jaminan()
	{
		ob_clean();
		ob_start();

		$id_invoice = $this->uri->segment(3);

		$get_invoice = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $id_invoice])->row();
		// $get_invoice_detail = $this->db->get_where('tr_invoice_sales_detail', ['id_invoice' => $id_invoice])->result();
		$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $get_invoice->id_so])->row();
		$get_customer = $this->db->get_where('customer', ['id_customer' => $get_so->id_customer])->row();

		$this->db->select('
			a.id_category3 as id_product,
			a.nama_produk as nm_produk,
			a.qty as qty,
			a.harga_satuan as harga,
			a.diskon_nilai as disc,
			a.total_harga as subtotal
		');
		$this->db->from('tr_sales_order_detail a');
		$this->db->where('a.no_so', $get_so->no_so);
		$this->db->group_by('a.id_so_detail');
		$get_invoice_detail1 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.keterangan as nm_produk,
			1 as qty,
			a.total_nilai as harga,
			0 as disc,
			a.total_nilai as subtotal
		');
		$this->db->from('tr_penawaran_other_cost a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail2 = $this->db->get_compiled_select();

		$this->db->reset_query();
		$this->db->select('
			"" as id_product,
			a.nm_other as nm_produk,
			a.qty as qty,
			a.harga as harga,
			0 as disc,
			a.total as subtotal
		');
		$this->db->from('tr_penawaran_other_item a');
		$this->db->where('a.id_penawaran', $get_so->no_penawaran);
		$this->db->group_by('a.id');
		$get_invoice_detail3 = $this->db->get_compiled_select();

		$query_detail_invoice = $get_invoice_detail1 . ' UNION ALL ' . $get_invoice_detail2 . ' UNION ALL ' . $get_invoice_detail3;
		$get_invoice_detail = $this->db->query($query_detail_invoice)->result();

		$data = [
			'id_invoice' => $id_invoice,
			'data_invoice' => $get_invoice,
			'data_invoice_detail' => $get_invoice_detail,
			'data_so' => $get_so,
			'data_customer' => $get_customer
		];

		$this->load->view('print_invoice_jaminan', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html = ob_get_contents();

		$html2pdf->WriteHTML($html);

		ob_end_clean();
		$html2pdf->Output('Invoice Jaminan.pdf', 'I');
	}
}
