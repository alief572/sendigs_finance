<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Syamsudin
 * @copyright Copyright (c) 2022, Syamsudin
 *
 * This is controller for Purchase Order Payment
 */

class Purchase_order_payment extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Purchase_Order.View';
	protected $addPermission  	= 'Purchase_Order.Add';
	protected $managePermission = 'Purchase_Order.Manage';
	protected $deletePermission = 'Purchase_Order.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Purchase_order_payment/Pr_model',
			'Purchase_order_payment/Jurnal_model',
		));
		$this->template->title('Receive Invoice');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');

		$this->db->select('a.*, b.nm_lengkap');
		$this->db->from('tr_purchase_order a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->where('a.status', '2');
		$get_list_po = $this->db->get()->result_array();

		$this->template->set('list_po', $get_list_po);
		$this->template->title('Receive Invoice');
		$this->template->render('index_incoming');
	}

	public function checkbx()
	{
		$post = $this->input->post();

		if ($post['checkbx'] == 'dp') {
			$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, d.id as id_invoice, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
			$this->db->from('tr_purchase_order a');
			$this->db->join('users b', 'b.id_user = a.created_by', 'left');
			$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
			$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
			$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
			$this->db->where('e.group_top', 76);
			$this->db->where('a.status', '2');
			$this->db->group_by('e.id');
			$this->db->order_by('a.created_on', 'desc');
			$get_list_po = $this->db->get()->result_array();



			$get_supplier = $this->db->get('new_supplier')->result();

			$this->template->set('list_po', $get_list_po);
			$this->template->set('list_supplier', $get_supplier);
			$this->template->render('list_dp');
		} else if ($post['checkbx'] == 'pro') {
			$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, d.id as id_invoice, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
			$this->db->from('tr_purchase_order a');
			$this->db->join('users b', 'b.id_user = a.created_by', 'left');
			$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
			$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
			$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
			$this->db->where('e.group_top', 77);
			$this->db->where('a.status', '2');
			$this->db->group_by('e.id');
			$this->db->order_by('a.created_on', 'desc');
			$get_list_po = $this->db->get()->result_array();

			$get_supplier = $this->db->get('new_supplier')->result();

			$this->template->set('list_po', $get_list_po);
			$this->template->set('list_supplier', $get_supplier);
			$this->template->render('list_pro');
		} else if ($post['checkbx'] == 'ret') {
			$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, d.id as id_invoice , IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
			$this->db->from('tr_purchase_order a');
			$this->db->join('users b', 'b.id_user = a.created_by', 'left');
			$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
			$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
			$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
			$this->db->where('e.group_top', 78);
			$this->db->where('a.status', '2');
			$this->db->group_by('e.id');
			$this->db->order_by('a.created_on', 'desc');
			$get_list_po = $this->db->get()->result_array();

			$get_supplier = $this->db->get('new_supplier')->result();

			$this->template->set('list_po', $get_list_po);
			$this->template->set('list_supplier', $get_supplier);
			$this->template->render('list_ret');
		} else {
			$this->db->select('a.*');
			$this->db->from('tr_invoice_po a');
			$this->db->like('a.no_po', 'TR');
			$this->db->order_by('a.created_date', 'desc');
			$get_list_inc = $this->db->get()->result_array();
			// print_r($this->db->last_query());
			// exit;

			$get_supplier = $this->db->get('new_supplier')->result();

			// print_r($no_po);
			// exit;

			$this->template->set('list_inc', $get_list_inc);
			$this->template->set('list_supplier', $get_supplier);
			$this->template->render('list_inc');
		}
	}

	public function req_app()
	{
		$no_surat = $this->input->post('no_po');
		$id_top = $this->input->post('id_top');
		$tipe = $this->input->post('tipe');


		$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $no_surat])->row_array();
		$get_currency = $this->db->get('mata_uang')->result_array();
		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po['id_suplier']])->row_array();

		$get_total_po = $this->db->select('hargatotal as ttl_po')->get_where('tr_purchase_order', ['no_po' => $get_po['no_po']])->row_array();

		$get_top = $this->db->get_where('tr_top_po', ['id' => $id_top])->row();

		$progress = $get_top->progress;
		$nilai_disc = ($get_po['nilai_disc']);
		$nilai_ppn = (($get_po['total_ppn']) * $progress / 100);

		$this->template->set('data_po', $get_po);
		$this->template->set('list_currency', $get_currency);
		$this->template->set('get_total_po', $get_total_po);
		$this->template->set('get_supplier', $get_supplier);
		$this->template->set('get_top', $get_top);
		$this->template->set('id_top', $id_top);
		$this->template->set('nilai_ppn', $nilai_ppn);
		$this->template->set('nilai_disc', $nilai_disc);
		$this->template->set('progress', $progress);

		if ($tipe == 'dp') {
			$this->template->render('add');
		}
		if ($tipe == 'pro') {
			$this->template->render('add_pro');
		}
		if ($tipe == 'ret') {
			$this->template->render('add_ret');
		}
	}

	public function req_inc_app()
	{
		$no_surat = $this->input->post('no_po');
		$tipe_incoming = $this->input->post('tipe_incoming');


		$get_currency = $this->db->get('mata_uang')->result_array();

		if ($tipe_incoming == 'incoming material') {
			$get_inc = $this->db->get_where('tr_incoming_check', ['kode_trans' => $no_surat])->row_array();
			$get_total_po = $this->db->query('
				SELECT SUM(a.jumlahharga) as ttl_po
				FROM
					dt_trans_po a 
				WHERE
					a.id IN (SELECT aa.id_po_detail FROM tr_incoming_check_detail aa WHERE aa.kode_trans = "' . $no_surat . '")
			')->row_array();

			$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $get_inc['no_ipp']])->row();

			$this->db->select('a.nama as nm_supplier');
			$this->db->from('new_supplier a');
			$this->db->join('tr_purchase_order b', 'b.id_suplier = a.kode_supplier', 'left');
			$this->db->where('b.no_po', $get_inc['no_ipp']);
			$get_supplier = $this->db->get()->row();

			// $get_list_inc = $this->db->get_where('tr_incoming_check_detail', ['kode_trans' => $no_surat])->result_array();

			$this->db->select('a.*, b.hargasatuan, b.qty as qty_po, c.no_surat, (d.qty_ng + d.qty_oke) as qty_incoming');
			$this->db->from('tr_incoming_check_detail a');
			$this->db->join('dt_trans_po b', 'b.id = a.id_po_detail', 'left');
			$this->db->join('tr_purchase_order c', 'c.no_po = b.no_po', 'left');
			$this->db->join('tr_checked_incoming_detail d', 'd.id_detail = a.id', 'left');
			$this->db->where('a.kode_trans', $no_surat);
			// $this->db->group_by('a.id');
			$get_list_inc = $this->db->get()->result_array();

			$no_surat = $get_list_inc[0]['no_surat'];

			$get_invoice = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();

			$total_dp = 0;
			$get_total_dp = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();
			if (!empty($get_total_dp)) {
				$total_dp = $get_total_dp['value_dp'];
			}

			$total_incoming = 0;
			foreach ($get_list_inc as $item) {
				$total_incoming += ($item['qty_incoming'] * $item['hargasatuan']);
			}
		} else {
			$get_inc = $this->db->get_where('warehouse_adjustment', ['kode_trans' => $no_surat])->row_array();
			$get_total_po = $this->db->query('
				SELECT SUM(a.jumlahharga) as ttl_po
				FROM
					dt_trans_po a 
				WHERE
					a.id IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans = "' . $no_surat . '")
			')->row_array();

			// $get_list_inc = $this->db->get_where('tr_incoming_check_detail', ['kode_trans' => $no_surat])->result_array();

			$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $get_inc['no_ipp']])->row();

			$this->db->select('a.nama as nm_supplier');
			$this->db->from('new_supplier a');
			$this->db->join('tr_purchase_order b', 'b.id_suplier = a.kode_supplier', 'left');
			$this->db->where('b.no_po', $get_inc['no_ipp']);
			$get_supplier = $this->db->get()->row();

			$get_list_inc = $this->db->query("
				SELECT
					a.*, b.hargasatuan, b.qty as qty_po, c.no_surat, (a.qty_oke + a.qty_rusak) as qty_incoming
				FROM
					warehouse_adjustment_detail a
					LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
					LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
				WHERE
					a.kode_trans = '" . $no_surat . "'
			")->result_array();

			$no_surat = $get_list_inc[0]['no_surat'];

			$get_invoice = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();

			$total_dp = 0;
			$get_total_dp = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();
			if (!empty($get_total_dp)) {
				$total_dp = $get_total_dp['value_dp'];
			}

			$total_incoming = 0;
			foreach ($get_list_inc as $item) {
				$total_incoming += ($item['qty_incoming'] * $item['hargasatuan']);
			}
		}

		$this->template->set('data_inc', $get_inc);
		$this->template->set('list_currency', $get_currency);
		$this->template->set('get_total_po', $get_total_po);
		$this->template->set('list_inc', $get_list_inc);
		$this->template->set('total_dp', $total_dp);
		$this->template->set('total_incoming', $total_incoming);
		$this->template->set('tipe_incoming', $tipe_incoming);
		$this->template->set('get_supplier', $get_supplier);
		$this->template->set('data_po', $get_po);
		$this->template->render('add_inc');
	}

	public function view()
	{
		$id = $this->input->post('id');
		$tipe = $this->input->post('tipe');

		$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $id])->row_array();
		$id_top = $get_invoice['id_top'];

		$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $get_invoice['no_po']])->row();
		$get_top = $this->db->get_where('tr_top_po', ['id' => $id_top])->row();

		$this->template->set('data_invoice', $get_invoice);
		$this->template->set('nilai_ppn', $get_invoice['nilai_ppn']);
		$this->template->set('nilai_disc', $get_invoice['nilai_disc']);
		$this->template->set('nilai_top', $get_top->nilai);
		if ($tipe == 'dp') {
			$this->template->render('view');
		}
		if ($tipe == 'pro') {
			$this->template->render('view_pro');
		}
		if ($tipe == 'ret') {
			$this->template->render('view_ret');
		}
	}

	public function view_inc()
	{
		$id = $this->input->post('id');

		$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $id])->row_array();
		$id_po = str_replace(', ', ',', $get_invoice['no_po']);
		$no_incoming = explode(',', $id_po);

		// print_r("SELECT
		// 		a.nm_material as nm_material,
		// 		b.hargasatuan as hargasatuan,
		// 		b.qty as qty_po,
		// 		c.no_surat as no_surat,
		// 		(d.qty_ng + d.qty_oke) as qty_incoming
		// 	FROM
		// 		tr_incoming_check_detail a
		// 		LEFT JOIN dt_trans_po b ON b.id = a.id_po_detail
		// 		LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
		// 		LEFT JOIN tr_checked_incoming_detail d ON d.id_detail = a.id
		// 	WHERE
		// 		a.kode_trans IN ('" . str_replace(",", "','", $id_po) . "')

		// 	UNION ALL

		// 	SELECT
		// 		a.nm_material as nm_material,
		// 		b.hargasatuan as hargasatuan,
		// 		b.qty as qty_po,
		// 		c.no_surat as no_surat,
		// 		(a.qty_oke + a.qty_rusak) as qty_incoming
		// 	FROM
		// 		warehouse_adjustment_detail a
		// 		LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
		// 		LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
		// 	WHERE
		// 		a.kode_trans IN ('" . str_replace(",", "','", $id_po) . "')");
		// 		exit;

		// $get_detail_incoming = $this->db->query("
		// 	SELECT
		// 		a.nm_material as nm_material,
		// 		b.hargasatuan as hargasatuan,
		// 		b.qty as qty_po,
		// 		c.no_surat as no_surat,
		// 		(d.qty_ng + d.qty_oke) as qty_incoming
		// 	FROM
		// 		tr_incoming_check_detail a
		// 		LEFT JOIN dt_trans_po b ON b.id = a.id_po_detail
		// 		LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
		// 		LEFT JOIN tr_checked_incoming_detail d ON d.id_detail = a.id
		// 	WHERE
		// 		a.kode_trans IN ('" . str_replace(",", "','", $id_po) . "')

		// 	UNION ALL

		// 	SELECT
		// 		a.nm_material as nm_material,
		// 		b.hargasatuan as hargasatuan,
		// 		b.qty as qty_po,
		// 		c.no_surat as no_surat,
		// 		(a.qty_oke + a.qty_rusak) as qty_incoming
		// 	FROM
		// 		warehouse_adjustment_detail a
		// 		LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
		// 		LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
		// 	WHERE
		// 		a.kode_trans IN ('" . str_replace(",", "','", $id_po) . "')
		// ")->result_array();

		$this->template->set('data_invoice', $get_invoice);
		$this->template->set('no_incoming', $no_incoming);
		$this->template->render('view_inc');
	}

	public function save_invoice()
	{
		$post = $this->input->post();

		$config['upload_path'] = './uploads/invoice'; //path folder
		$config['allowed_types'] = '*'; //type yang dapat diakses bisa anda sesuaikan
		$config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
		$config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
		$config['remove_spaces'] = FALSE; // Remove spaces from the file name.

		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		$this->db->trans_begin();

		$link_doc = '';
		if ($this->upload->do_upload('upload_invoice')) {
			$data_upload_po = $this->upload->data();
			$link_doc = 'uploads/invoice/' . $data_upload_po['file_name'];
		}

		$no_po = $post['no_po'];
		$no_po1 = $post['nomor_po'];

		// print_r($no_po);
		// exit;

		$no_invoice = $this->Pr_model->generate_no_invoice();

		if ($post['tipe_req'] == 'dp') {
			$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $post['nomor_po']])->row();
			$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po->id_suplier])->row();

			$insert_invoice = $this->db->insert('tr_invoice_po', [
				'id' => $no_invoice,
				'no_po' => $post['nomor_po'],
				'curr' => $post['currency'],
				'invoice_date' => $post['invoice_date'],
				'value_dp' => str_replace(',', '', $post['value_dp']),
				'invoice_no' => $post['nomor_invoice'],
				'total_pembelian' => str_replace(',', '', $post['total_pembelian']),
				'no_faktur_pajak' => $post['nomor_faktur_pajak'],
				'persen_dp' => $post['persen_dp'],
				'link_doc' => $link_doc,
				'invoice_date_real' => $post['invoice_date_real'],
				'tanggal_faktur_pajak' => $post['tanggal_faktur_pajak'],
				'id_supplier' => $get_supplier->kode_supplier,
				'nm_supplier' => $get_supplier->nama,
				'id_top' => $post['id_top'],
				'bank' => $post['bank'],
				'no_bank' => $post['no_bank'],
				'nm_acc_bank' => $post['nm_acc_bank'],
				'nilai_disc' => str_replace(',', '', $post['nilai_disc']),
				'nilai_ppn' => str_replace(',', '', $post['nilai_ppn']),
				'total_invoice' => str_replace(',', '', $post['nilai_ppn']) + str_replace(',', '', $post['total_pembelian']),
				'kurs' => str_replace(',', '', $post['kurs']),
				'created_by' => $this->auth->user_id(),
				'created_date' => date('Y-m-d H:i:s')
			]);
			if (!$insert_invoice) {
				print_r($this->db->error($insert_invoice));
			}
		} else {
			$arr_id_suplier = [];
			$get_id_suplier = $this->db->query("SELECT a.id_suplier FROM tr_purchase_order a WHERE a.no_surat IN ('" . str_replace(",", "','", $post['nomor_po']) . "') GROUP BY a.id_suplier")->result();
			foreach ($get_id_suplier as $item_id_suplier) {
				$arr_id_suplier[] = $item_id_suplier->id_suplier;
			}

			$arr_nm_supplier = [];
			$get_nm_supplier = $this->db->query("SELECT a.nama FROM new_supplier a WHERE a.kode_supplier IN ('" . str_replace(",", "','", implode(',', $arr_id_suplier)) . "')")->result();
			foreach ($arr_nm_supplier as $item_nm_supplier) {
				$arr_nm_supplier[] = $item_nm_supplier->nama;
			}

			$insert_invoice = $this->db->insert('tr_invoice_po', [
				'id' => $no_invoice,
				'no_po' => $post['nomor_po'],
				'curr' => $post['currency'],
				'invoice_date' => $post['invoice_date'],
				'value_dp' => str_replace(',', '', $post['value_dp']),
				'invoice_no' => $post['nomor_invoice'],
				'total_pembelian' => str_replace(',', '', $post['total_pembelian']),
				'no_faktur_pajak' => $post['nomor_faktur_pajak'],
				'link_doc' => $link_doc,
				'req_payment_po' => str_replace(',', '', $post['req_payment_po']),
				'total_invoice' => str_replace(',', '', $post['total_invoice']),
				'notes' => $post['notes'],
				'invoice_date_real' => $post['invoice_date_real'],
				'tanggal_faktur_pajak' => $post['tanggal_faktur_pajak'],
				'id_supplier' => $post['kode_supplier'],
				'nm_supplier' => $post['nama_supplier'],
				'nilai_ppn' => str_replace(',', '', $post['nilai_ppn']),
				'nilai_disc' => str_replace(',', '', $post['nilai_disc']),
				'bank' => $post['bank'],
				'no_bank' => $post['no_bank'],
				'nm_acc_bank' => $post['nm_acc_bank'],
				'kurs' => str_replace(',', '', $post['kurs']),
				'created_by' => $this->auth->user_id(),
				'created_date' => date('Y-m-d H:i:s')
			]);
			if (!$insert_invoice) {
				print_r($this->db->error($insert_invoice));
			}
		}

		$get_users = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();

		if ($post['tipe_req'] == 'dp') {
			$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $post['nomor_po']])->row();
			$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po->id_suplier])->row();

			$get_top = $this->db->get_where('tr_top_po', ['id' => $post['id_top']])->row();
			if ($get_top->group_top == 76) {
				$insert_expense = $this->db->insert('tr_expense', [
					'no_doc' => $no_invoice,
					'tgl_doc' => $post['invoice_date'],
					'nama' => $get_users['nm_lengkap'],
					'approval' => $get_users['nm_lengkap'],
					'status' => 1,
					'created_by' => $get_users['nm_lengkap'],
					'created_on' => date('Y-m-d H:i:s'),
					'approved_by' => $get_users['nm_lengkap'],
					'approved_on' => date('Y-m-d H:i:s'),
					'jumlah' => str_replace(',', '', $post['value_dp']),
					'informasi' => 'Pembayaran DP : ' . $no_po . ' (' . $get_supplier->nama . ')',
					'exp_inv_po' => 1,
					'bank_id' => $post['bank'],
					'accnumber' => $post['no_bank'],
					'accname' => $post['nm_acc_bank'],
					'id_po' => $post['nomor_po']
				]);
				if (!$insert_expense) {
					print_r($this->db->error($insert_expense));
					exit;
				}

				$insert_expense_detail = $this->db->insert('tr_expense_detail', [
					'tanggal' => $post['invoice_date'],
					'no_doc' => $no_invoice,
					'deskripsi' => 'Pembayaran DP : ' . $no_po . ' (' . $get_supplier->nama . ')',
					'qty' => 1,
					'harga' => str_replace(',', '', $post['value_dp']),
					'total_harga' => str_replace(',', '', $post['value_dp']),
					'status' => 0,
					'keterangan' => 'Pembayaran DP : ' . $no_po . ' (' . $get_supplier->nama . ')',
					'expense' => str_replace(',', '', $post['value_dp']),
					'created_by' => $get_users['nm_lengkap'],
					'created_on' => date('Y-m-d H:i:s')
				]);
				if (!$insert_expense_detail) {
					print_r($this->db->error($insert_expense_detail));
					exit;
				}

				if ($post['currency'] == 'IDR') {
					$kurs  = 1;
				} else {
					$kurs  = str_replace(',', '', $post['kurs']);
				}

				$dpp_dp_idr = (str_replace(',', '', $post['total_pembelian']) * $kurs);
				$dpp_dp = (str_replace(',', '', $post['total_pembelian']));

				$update_uang_muka = $this->db->update('tr_purchase_order', ['uang_muka_idr' => $dpp_dp_idr], ['no_surat' => $no_po1]);
				$update_uang_muka1 = $this->db->update('tr_purchase_order', ['uang_muka' => $dpp_dp], ['no_surat' => $no_po1]);
				$update_kurs       = $this->db->update('tr_purchase_order', ['kurs_terima_invoice' => $kurs], ['no_surat' => $no_po1]);
			}
			if ($get_top->group_top == 77) {
				$insert_expense = $this->db->insert('tr_expense', [
					'no_doc' => $no_invoice,
					'tgl_doc' => $post['invoice_date'],
					'nama' => $get_users['nm_lengkap'],
					'approval' => $get_users['nm_lengkap'],
					'status' => 1,
					'created_by' => $get_users['nm_lengkap'],
					'created_on' => date('Y-m-d H:i:s'),
					'approved_by' => $get_users['nm_lengkap'],
					'approved_on' => date('Y-m-d H:i:s'),
					'jumlah' => str_replace(',', '', $post['value_dp']),
					'informasi' => 'Pembayaran Progress : ' . $no_po . ' (' . $get_supplier->nama . ')',
					'exp_inv_po' => 1,
					'bank_id' => $post['bank'],
					'accnumber' => $post['no_bank'],
					'accname' => $post['nm_acc_bank']
				]);
				if (!$insert_expense) {
					print_r($this->db->error($insert_expense));
					exit;
				}

				$insert_expense_detail = $this->db->insert('tr_expense_detail', [
					'tanggal' => $post['invoice_date'],
					'no_doc' => $no_invoice,
					'deskripsi' => 'Pembayaran Progress : ' . $no_po . ' (' . $get_supplier->nama . ')',
					'qty' => 1,
					'harga' => str_replace(',', '', $post['value_dp']),
					'total_harga' => str_replace(',', '', $post['value_dp']),
					'status' => 0,
					'keterangan' => 'Pembayaran Progress : ' . $no_po . ' (' . $get_supplier->nama . ')',
					'expense' => str_replace(',', '', $post['value_dp']),
					'created_by' => $get_users['nm_lengkap'],
					'created_on' => date('Y-m-d H:i:s')
				]);
				if (!$insert_expense_detail) {
					print_r($this->db->error($insert_expense_detail));
					exit;
				}

				if ($post['currency'] == 'IDR') {
					$kurs  = 1;
				} else {
					$kurs  = str_replace(',', '', $post['kurs']);
				}

				$dpp_dp_idr = (str_replace(',', '', $post['total_pembelian']) * $kurs);
				$dpp_dp = (str_replace(',', '', $post['total_pembelian']));

				$update_uang_muka = $this->db->update('tr_purchase_order', ['uang_muka_idr' => $dpp_dp_idr], ['no_surat' => $no_po1]);
				$update_uang_muka1 = $this->db->update('tr_purchase_order', ['uang_muka' => $dpp_dp], ['no_surat' => $no_po1]);
				$update_kurs       = $this->db->update('tr_purchase_order', ['kurs_terima_invoice' => $kurs], ['no_surat' => $no_po1]);
			}
			if ($get_top->group_top == 78) {
				$insert_expense = $this->db->insert('tr_expense', [
					'no_doc' => $no_invoice,
					'tgl_doc' => $post['invoice_date'],
					'nama' => $get_users['nm_lengkap'],
					'approval' => $get_users['nm_lengkap'],
					'status' => 1,
					'created_by' => $get_users['nm_lengkap'],
					'created_on' => date('Y-m-d H:i:s'),
					'approved_by' => $get_users['nm_lengkap'],
					'approved_on' => date('Y-m-d H:i:s'),
					'jumlah' => str_replace(',', '', $post['value_dp']),
					'informasi' => 'Pembayaran Retensi : ' . $no_po1 . ' (' . $get_supplier->nama . ')',
					'exp_inv_po' => 1,
					'bank_id' => $post['bank'],
					'accnumber' => $post['no_bank'],
					'accname' => $post['nm_acc_bank']
				]);
				if (!$insert_expense) {
					print_r($this->db->error($insert_expense));
					exit;
				}

				$insert_expense_detail = $this->db->insert('tr_expense_detail', [
					'tanggal' => $post['invoice_date'],
					'no_doc' => $no_invoice,
					'deskripsi' => 'Pembayaran Retensi : ' . $no_po1 . ' (' . $get_supplier->nama . ')',
					'qty' => 1,
					'harga' => str_replace(',', '', $post['value_dp']),
					'total_harga' => str_replace(',', '', $post['value_dp']),
					'status' => 0,
					'keterangan' => 'Pembayaran Retensi : ' . $no_po1 . ' (' . $get_supplier->nama . ')',
					'expense' => str_replace(',', '', $post['value_dp']),
					'created_by' => $get_users['nm_lengkap'],
					'created_on' => date('Y-m-d H:i:s')
				]);
				if (!$insert_expense) {
					print_r($this->db->error($insert_expense));
					exit;
				}
			}
		} else {
			$arr_id_suplier = [];
			$get_id_suplier = $this->db->query("SELECT a.id_suplier FROM tr_purchase_order a WHERE a.no_surat IN ('" . str_replace(",", "','", $post['no_po']) . "') GROUP BY a.id_suplier")->result();
			foreach ($get_id_suplier as $item_id_suplier) {
				$arr_id_suplier[] = $item_id_suplier->id_suplier;
			}

			// print_r(str_replace(",", "','", $post['nomor_po']));
			// exit;

			$arr_nm_supplier = [];
			if (!empty($arr_id_suplier)) {
				$get_nm_supplier = $this->db->select('nama')->from('new_supplier')->where_in('kode_supplier', $arr_id_suplier)->get()->result();
				foreach ($get_nm_supplier as $item_nm_supplier) {
					$arr_nm_supplier[] = $item_nm_supplier->nama;
				}
			}

			$check_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $no_po])->result();
			if (count($check_po) < 1) {
				$update_kurs       = $this->db->update('rutin_non_planning_header', ['kurs_terima_invoice_progress' => $kurs], ['no_pr' => $no_po]);
			} else {
				$update_kurs       = $this->db->update('tr_purchase_order', ['kurs_terima_invoice_progress' => $kurs], ['no_surat' => $no_po]);
			}


			$insert_expense = $this->db->insert('tr_expense', [
				'no_doc' => $no_invoice,
				'tgl_doc' => $post['invoice_date'],
				'nama' => $get_users['nm_lengkap'],
				'approval' => $get_users['nm_lengkap'],
				'status' => 1,
				'created_by' => $get_users['nm_lengkap'],
				'created_on' => date('Y-m-d H:i:s'),
				'approved_by' => $get_users['nm_lengkap'],
				'approved_on' => date('Y-m-d H:i:s'),
				'jumlah' => str_replace(',', '', $post['req_payment_po']),
				'informasi' => 'Pembayaran PO : ' . $no_po . ' (' . implode(', ', $arr_nm_supplier) . ')',
				'bank_id' => $post['bank'],
				'accnumber' => $post['no_bank'],
				'accname' => $post['nm_acc_bank'],
				'id_po' => $post['no_po'],
				'exp_inv_po' => 1
			]);
			if (!$insert_expense) {
				print_r($this->db->error($insert_expense));
				exit;
			}

			$insert_expense_detail = $this->db->insert('tr_expense_detail', [
				'tanggal' => $post['invoice_date'],
				'no_doc' => $no_invoice,
				'deskripsi' => 'Pembayaran PO : ' . $no_po . ' (' . implode(', ', $arr_nm_supplier) . ')',
				'qty' => 1,
				'harga' => str_replace(',', '', $post['req_payment_po']),
				'total_harga' => str_replace(',', '', $post['req_payment_po']),
				'status' => 0,
				'keterangan' => 'Pembayaran PO : ' . $no_po . ' (' . implode(', ', $arr_nm_supplier) . ')',
				'expense' => str_replace(',', '', $post['req_payment_po']),
				'created_by' => $get_users['nm_lengkap'],
				'created_on' => date('Y-m-d H:i:s')
			]);
			if (!$insert_expense_detail) {
				print_r($this->db->error($insert_expense_detail));
				exit;
			}
		}

		if ($post['tipe_req'] == 'dp') {
			$update_po = $this->db->update('tr_purchase_order', ['po_inv_create' => 1], ['no_surat' => $post['nomor_po']]);
			if (!$update_po) {
				print_r($this->db->error($update_po));
				exit;
			}
		} else {
			$clean_no_po = str_replace(', ', ',', $post['nomor_po']);
			// if ($post['tipe_incoming'] == 'incoming material') {
			// 	$this->db->update('tr_incoming_check', ['inc_inv_create' => 1], ['kode_trans' => $post['nomor_po']]);
			// } else {
			// 	$this->db->update('warehouse_adjustment', ['inc_inv_create' => 1], ['kode_trans' => $post['nomor_po']]);
			// }
			$update_incoming = $this->db->where_in('kode_trans', explode(',', $clean_no_po));
			$update_incoming = $this->db->update('tr_incoming_check', ['inc_inv_create' => 1]);
			if (!$update_incoming) {
				print_r($this->db->error($update_incoming));
				exit;
			}

			$update_warehouse = $this->db->where_in('kode_trans', explode(',', $clean_no_po));
			$update_warehouse = $this->db->update('warehouse_adjustment', ['inc_inv_create' => 1]);
			if (!$update_warehouse) {
				print_r($this->db->error($update_warehouse));
				exit;
			}

			$update_invoice = $this->db->where_in('kode_trans', explode(',', $clean_no_po));
			$update_invoice = $this->db->delete('tr_check_invoice');
			if (!$update_invoice) {
				print_r($this->db->error($update_invoice));
				exit;
			}
		}

		//tambahan syam 16/07/2024

		$totalunbill = 0;
		$totalap = 0;
		$coaunbill = '';
		$coaap = '';


		if ($post['tipe_req'] == 'dp') {
			$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po->id_suplier])->row();
			if ($post['currency'] == 'IDR') {
				$kurs  = 1;
				$jenis_jurnal = 'JV001';
			} else {
				$kurs  = str_replace(',', '', $post['kurs']);
				$jenis_jurnal = 'JV004';
			}

			$nilai_invoice = str_replace(',', '', $post['total_pembelian']) * $kurs;
			$nilai_ppn = str_replace(',', '', $post['nilai_ppn']) * $kurs;
			$kode_supplier = $get_supplier->kode_supplier;
			$nama = $get_supplier->nama;
		} else {

			if ($post['currency'] == 'IDR') {
				$kurs  = 1;
				$jenis_jurnal = 'JV003';
			} else {
				$kurs  = str_replace(',', '', $post['kurs']);
				$jenis_jurnal = 'JV006';
			}

			$nilai_invoice = str_replace(',', '', $post['total_invoice']) * $kurs;
			$nilai_ppn = str_replace(',', '', $post['nilai_ppn']) * $kurs;
			$kode_supplier = implode(', ', $arr_id_suplier);
			$nama = implode(', ', $arr_nm_supplier);
		}

		// print_r($jenis_jurnal);
		// exit;

		$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' order by parameter_no")->result();
		$data_po     = $this->db->query("select * from tr_purchase_order WHERE no_surat='$no_po'")->row();

		// print_r($data_po);
		// exit;

		$unbill      = $data_po->hutang_idr;
		$kurs_unbill = $data_po->kurs_terima_barang;
		$kurs_um     = $data_po->kurs_terima_invoice;
		$um          = $data_po->uang_muka;
		$umidr       = $data_po->uang_muka_idr;
		if ($data_po->matauang == 'IDR') {
			$kurs_unbill = 1;
			$kurs_um = 1;
		}

		$selisih_um  = (($nilai_invoice) - ($unbill - $umidr));

		if ($selisih_um < 0) {
			$selisihdebet  = 0;
			$selisihkredit = $selisih_um * (-1);
		} elseif ($selisih_um > 0) {
			$selisihdebet  = $selisih_um;
			$selisihkredit = 0;
		}

		$hutangimport = $nilai_invoice;

		$nomor_jurnal = $jenis_jurnal . $no_po . rand(100, 999);
		$payment_date = $post['invoice_date']; //date("Y-m-d");
		$det_Jurnaltes1 = array();
		//			$total=($data->nilai_terima_barang_kurs);
		if ($post['tipe_req'] == 'dp') {
			if ($nilai_invoice > 0) {
				foreach ($datajurnal1 as $rec) {
					if ($rec->parameter_no == "1") {

						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => $nilai_invoice,
							'kredit' => 0,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
						$totalunbill = $nilai_invoice;
						$coaunbill = $rec->no_perkiraan;
					}
					if ($rec->parameter_no == "2") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => 0,
							'kredit' => $nilai_invoice + $nilai_ppn,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
						$totalap = $nilai_invoice + $nilai_ppn;
						$coaap = $rec->no_perkiraan;
					}
					if ($rec->parameter_no == "3") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => $nilai_ppn,
							'kredit' => 0,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
					}
				}
			}
		} else {
			if ($nilai_invoice > 0) {
				foreach ($datajurnal1 as $rec) {
					if ($rec->parameter_no == "1") {

						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => $unbill,
							'kredit' => 0,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
						$totalunbill = $unbill;
						$coaunbill = $rec->no_perkiraan;
					}
					if ($rec->parameter_no == "2") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => 0,
							'kredit' => $hutangimport + $nilai_ppn,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
						$totalap = $hutangimport;
						$coaap = $rec->no_perkiraan;
					}
					if ($rec->parameter_no == "3") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => $nilai_ppn,
							'kredit' => 0,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "4") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => 0,
							'kredit' => $umidr,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "5") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
							'no_reff' => $post['nomor_invoice'],
							'debet' => $selisihdebet,
							'kredit' => $selisihkredit,
							'no_request' => $post['nomor_po'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $kode_supplier,
							'stspos' => '1'
						);
					}
				}
			}
		}
		$insert_jurnaltras = $this->db->insert_batch('jurnaltras', $det_Jurnaltes1);
		if (!$insert_jurnaltras) {
			print_r($this->db->error($insert_jurnaltras));
			exit;
		}

		//auto jurnal

		$tanggal = $post['invoice_date_real'];
		$Bln	= substr($tanggal, 5, 2);
		$Thn	= substr($tanggal, 0, 4);
		$total	= 0;
		$Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tanggal);
		foreach ($det_Jurnaltes1 as $vals) {
			$datadetail = array(
				'tipe'			=> 'JV',
				'nomor'			=> $Nomor_JV,
				'tanggal'		=> $tanggal,
				'no_perkiraan'	=> $vals['no_perkiraan'],
				'keterangan'	=> $vals['keterangan'],
				'no_reff'		=> $vals['no_reff'],
				'debet'			=> $vals['debet'],
				'kredit'		=> $vals['kredit'],
			);
			$total = ($total + $vals['debet']);
			$insert_jurnal = $this->db->insert(DBACC . '.jurnal', $datadetail);
			if (!$insert_jurnal) {
				print_r($this->db->error($insert_jurnal));
				exit;
			}
		}
		$keterangan		= 'Receive Invoice ' . $no_invoice;
		$dataJVhead = array(
			'nomor' 	    	=> $Nomor_JV,
			'tgl'	         	=> $tanggal,
			'jml'	            => $total,
			'bulan'	            => $Bln,
			'tahun'	            => $Thn,
			'kdcab'				=> '101',
			'jenis'			    => 'JV',
			'keterangan'		=> $keterangan,
			'user_id'			=> $Username,
			'ho_valid'			=> '',
		);
		$insert_javh = $this->db->insert(DBACC . '.javh', $dataJVhead);
		if (!$insert_javh) {
			print_r($this->db->error($insert_javh));
			exit;
		}
		$datahutang = array(
			'tipe'       	 => 'JV',
			'nomor'       	 => $Nomor_JV,
			'tanggal'        => $tanggal,
			'no_perkiraan'   => $coaunbill,
			'keterangan'     => $keterangan,
			'no_reff'     	 => $post['nomor_po'],
			'kredit'      	 => 0,
			'debet'          => $totalunbill,
			'id_supplier'    => $kode_supplier,
			'nama_supplier'  => $nama,
			'no_request'     => $post['nomor_invoice'],
		);
		$insert_kartu_hutang = $this->db->insert('tr_kartu_hutang', $datahutang);
		if (!$insert_kartu_hutang) {
			print_r($this->db->error($insert_kartu_hutang));
			exit;
		}
		$datahutang = array(
			'tipe'       	 => 'JV',
			'nomor'       	 => $Nomor_JV,
			'tanggal'        => $tanggal,
			'no_perkiraan'   => $coaap,
			'keterangan'     => $keterangan,
			'no_reff'     	 => $post['nomor_po'],
			'kredit'      	 => $totalap,
			'debet'          => 0,
			'id_supplier'    => $kode_supplier,
			'nama_supplier'  => $nama,
			'no_request'     => $post['nomor_invoice'],
		);
		$insert_kartu_hutang = $this->db->insert('tr_kartu_hutang', $datahutang);
		if (!$insert_kartu_hutang) {
			print_r($this->db->error($insert_kartu_hutang));
			exit;
		}
		//end auto jurnal



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

	public function search_inc()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $kode_supplier])->row();

		$this->db->select('a.*');
		$this->db->from('tr_invoice_po a');
		$this->db->like('a.no_po', 'TR');
		$get_list_inc = $this->db->get()->result_array();

		$hasil = '
			<table class="table table-bordered table_req_pay_inc">
            <thead class="bg-blue">
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">Tanggal Invoice</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center">Status</th>
					<th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
			';

		$no_po = [];
		foreach ($get_list_inc as $item) {
			$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
			if (!empty($get_no_po)) {
				$list_no_po = [];
				foreach ($get_no_po as $item_no_po) {
					$list_no_po[] = $item_no_po->no_surat;
				}

				if (!empty($list_no_po)) {
					$list_no_po = implode(', ', $list_no_po);

					$no_po[$item['kode_trans']] = $list_no_po;
				} else {
					$no_po[$item['kode_trans']] = '';
				}
			} else {
				$no_po[$item['kode_trans']] = '';
			}
		}

		$total_invoice = [];
		foreach ($get_list_inc as $item) {
			$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
			if (!empty($get_total_invoice)) {
				$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
			} else {
				$total_invoice[$item['kode_trans']] = 0;
			}
		}

		$no = 1;
		foreach ($get_list_inc as $item) {

			$exp_no_po = explode(',', $item['no_po']);

			$nm_supplier = [];

			$no_ipp = [];
			$this->db->select('a.no_ipp');
			$this->db->from('tr_incoming_check a');
			$this->db->where_in('a.kode_trans', $exp_no_po);
			$get_no_ipp = $this->db->get()->result();
			foreach ($get_no_ipp as $item_ipp) {
				$no_ipp[] = $item_ipp->no_ipp;
			}

			$this->db->select('a.no_ipp');
			$this->db->from('warehouse_adjustment a');
			$this->db->where_in('a.kode_trans', $exp_no_po);
			$get_no_ipp_ware = $this->db->get()->result();
			foreach ($get_no_ipp_ware as $item_ipp_ware) {
				$no_ipp[] = $item_ipp_ware->no_ipp;
			}
			if (count($no_ipp) > 0) {
				$no_ipp = implode(',', $no_ipp);
			} else {
				$no_ipp = '';
			}

			$this->db->select('b.nama as nm_supplier');
			$this->db->from('tr_purchase_order a');
			$this->db->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left');
			$this->db->where_in('a.no_po', explode(',', $no_ipp));
			$this->db->group_by('b.nama');
			$get_nm_supplier = $this->db->get()->result();
			foreach ($get_nm_supplier as $item_nm_supplier) {
				$nm_supplier[] = $item_nm_supplier->nm_supplier;
			}

			if (count($nm_supplier) > 0) {
				$nm_supplier = implode(', ', $nm_supplier);
			} else {
				$nm_supplier = '';
			}

			$status = '<div class="badge bg-yellow">Waiting</div>';
			// if($id_rec_invoice !== ''){
			$get_invoice_payment = $this->db->get_where('payment_approve', ['no_doc' => $item['id'], 'status' => 2])->result();
			if (count($get_invoice_payment) > 0) {
				$complete = 1;
				$status = '<div class="badge bg-green">Complete</div>';
			}
			// }

			$view = '<button type="button" class="btn btn-sm btn-info view_inc" data-id="' . $item['id'] . '"><i class="fa fa-eye"></i></button>';
			if ($kode_supplier !== '') {
				if (strpos($nm_supplier, $get_supplier->nama) !== false) {
					$hasil .= '<tr>';
					$hasil .= '<td style="text-align: center;">' . $no . '</td>';
					$hasil .= '<td style="text-align: center;">' . $item['id'] . '</td>';
					$hasil .= '<td style="text-align: center;">' . date('d F Y', strtotime($item['invoice_date'])) . '</td>';
					$hasil .= '<td>' . $nm_supplier . '</td>';
					$hasil .= '<td style="text-align: center;">' . $status . '</td>';
					$hasil .= '<td style="text-align: center;">' . $view . '</td>';
					$hasil .= '</tr>';
					$no++;
				}
			} else {
				$hasil .= '<tr>';
				$hasil .= '<td style="text-align: center;">' . $no . '</td>';
				$hasil .= '<td style="text-align: center;">' . $item['id'] . '</td>';
				$hasil .= '<td style="text-align: center;">' . date('d F Y', strtotime($item['invoice_date'])) . '</td>';
				$hasil .= '<td>' . $nm_supplier . '</td>';
				$hasil .= '<td style="text-align: center;">' . $status . '</td>';
				$hasil .= '<td style="text-align: center;">' . $view . '</td>';
				$hasil .= '</tr>';
				$no++;
			}
		}
		$hasil .= '
            </tbody>
        </table>
		';

		echo $hasil;
	}

	public function search_dp()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
		$this->db->from('tr_purchase_order a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
		$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
		$this->db->where('e.group_top', 76);
		$this->db->where('a.status', '2');
		$this->db->order_by('a.created_on', 'desc');
		if ($kode_supplier !== '') {
			$this->db->where('a.id_suplier', $kode_supplier);
		}
		$this->db->group_by('e.id');
		$get_list_po = $this->db->get()->result_array();

		$hasil = '
			<table class="table table-bordered table_req_pay_dp">
            <thead class="bg-green">
                <tr>
					<th class="text-center">No</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. Purchase Invoice</th>
					<th class="text-center">No. Invoice</th>
					<th class="text-center">Nama Supplier</th>
					<th class="text-center">Tanggal PO</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Created By</th>
					<th class="text-center">Status</th>
					<th class="text-center">Action</th>
				</tr>
            </thead>
            <tbody>
			';

		// $no_po = [];
		// foreach ($get_list_po as $item) {
		// 	$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
		// 	if (!empty($get_no_po)) {
		// 		$list_no_po = [];
		// 		foreach ($get_no_po as $item_no_po) {
		// 			$list_no_po[] = $item_no_po->no_surat;
		// 		}

		// 		if (!empty($list_no_po)) {
		// 			$list_no_po = implode(', ', $list_no_po);

		// 			$no_po[$item['kode_trans']] = $list_no_po;
		// 		} else {
		// 			$no_po[$item['kode_trans']] = '';
		// 		}
		// 	} else {
		// 		$no_po[$item['kode_trans']] = '';
		// 	}
		// }

		// $total_invoice = [];
		// foreach ($get_list_po as $item) {
		// 	$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
		// 	if (!empty($get_total_invoice)) {
		// 		$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
		// 	} else {
		// 		$total_invoice[$item['kode_trans']] = 0;
		// 	}
		// }

		$no = 1;
		foreach ($get_list_po as $item) {

			$sts = '<div class="badge bg-blue">Waiting</div>';
			$close = 0;
			if ($item['ttl_persen_dp'] == $item['progress']) {
				$sts = '<div class="badge bg-green">Complete</div>';
				$close = 1;
			} else {
				if ($item['ttl_persen_dp'] > 0 && $item['ttl_persen_dp'] < 100) {
					$sts = '<div class="badge bg-yellow">Partial</div>';
				}
			}

			$get_incoming = $this->db->get_where('tr_incoming_check', ['no_ipp' => $item['no_po']])->result();
			$arr_id_incoming = [];

			foreach ($get_incoming as $item_incoming) {
				$arr_id_incoming[] = $item_incoming->kode_trans;
			}

			if (!empty($arr_id_incoming)) {
				$this->db->select('count(a.no_po) as num_po');
				$this->db->from('tr_invoice_po a');
				$this->db->where_in('a.no_po', $arr_id_incoming);
				$num_invoice = $this->db->get()->row();

				if ($num_invoice->num_po > 0) {
					$sts = '<div class="badge bg-green">Complete</div>';
					$close = 1;
				}
			}



			$view_btn = '';
			$req_pay_btn = '<button type="button" class="btn btn-sm btn-primary req_app" style="margin-left: 0.5rem" title="Request Payment" data-no_po="' . $item['no_surat'] . '" data-id_top="' . $item['id_top'] . '" data-tipe="dp"><i class="fa fa-arrow-up"></i></button>';
			if ($close == 1) {
				$get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['no_surat'], 'id_top' => $item['id_top']])->row_array();

				$view_btn = '<button type="button" class="btn btn-sm btn-info view" data-id="' . $get_invoice['id'] . '" data-id_top="' . $get_invoice['id_top'] . '" data-tipe="dp" title="view"><i class="fa fa-eye"></i></button>';
				$req_pay_btn = '';
			}

			$list_dp_btn = '';
			// if($item['ttl_persen_dp'] > 0) {
			//     $list_dp_btn = '<button type="button" class="btn btn-sm btn-warning list_dp" data-no_po="'.$item['no_po'].'" style="margin-left: 0.5rem"><i class="fa fa-list"></i></button>';
			// }

			$no_purchase_invoice = [];
			$no_invoice = [];

			$get_invoice = $this->db->select('a.*')
				->from('tr_invoice_po a')
				->where('a.id_top', $item['id_top'])
				->like('a.no_po', $item['no_surat'])
				->get()
				->result();

			foreach ($get_invoice as $item_invoice) {
				$no_purchase_invoice[] = str_replace(',', '', $item_invoice->id);
				$no_invoice[] = str_replace(',', '', $item_invoice->invoice_no);
			}

			if (!empty($no_purchase_invoice)) {
				$no_purchase_invoice = implode(', ', $no_purchase_invoice);
			} else {
				$no_purchase_invoice = '';
			}

			if (!empty($no_invoice)) {
				$no_invoice = implode(', ', $no_invoice);
			} else {
				$no_invoice = '';
			}

			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $no . '</td>';
			$hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
			$hasil .= '<td class="text-center">' . $no_purchase_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $no_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_supplier'] . '</td>';
			$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
			$hasil .= '<td class="text-center">' . $item['keterangan_top'] . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_lengkap'] . '</td>';
			$hasil .= '<td class="text-center">' . $sts . '</td>';
			$hasil .= '<td style="text-align: center;">' . $view_btn . $req_pay_btn . $list_dp_btn . '</td>';
			$hasil .= '</tr>';

			$no++;
		}
		$hasil .= '
            </tbody>
        </table>
		';

		echo $hasil;
	}

	public function search_pro()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
		$this->db->from('tr_purchase_order a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
		$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
		$this->db->where('e.group_top', 77);
		$this->db->where('a.status', '2');
		$this->db->order_by('a.created_on', 'desc');
		if ($kode_supplier !== '') {
			$this->db->where('a.id_suplier', $kode_supplier);
		}
		$this->db->group_by('e.id');
		$get_list_po = $this->db->get()->result_array();

		$hasil = '
			<table class="table table-bordered table_req_pay_pro">
            <thead class="bg-yellow">
                <tr>
					<th class="text-center">No</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. Purchase Invoice</th>
					<th class="text-center">No. Invoice</th>
					<th class="text-center">Nama Supplier</th>
					<th class="text-center">Tanggal PO</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Created By</th>
					<th class="text-center">Status</th>
					<th class="text-center">Action</th>
				</tr>
            </thead>
            <tbody>
			';

		// $no_po = [];
		// foreach ($get_list_po as $item) {
		// 	$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
		// 	if (!empty($get_no_po)) {
		// 		$list_no_po = [];
		// 		foreach ($get_no_po as $item_no_po) {
		// 			$list_no_po[] = $item_no_po->no_surat;
		// 		}

		// 		if (!empty($list_no_po)) {
		// 			$list_no_po = implode(', ', $list_no_po);

		// 			$no_po[$item['kode_trans']] = $list_no_po;
		// 		} else {
		// 			$no_po[$item['kode_trans']] = '';
		// 		}
		// 	} else {
		// 		$no_po[$item['kode_trans']] = '';
		// 	}
		// }

		// $total_invoice = [];
		// foreach ($get_list_po as $item) {
		// 	$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
		// 	if (!empty($get_total_invoice)) {
		// 		$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
		// 	} else {
		// 		$total_invoice[$item['kode_trans']] = 0;
		// 	}
		// }

		$no = 1;
		foreach ($get_list_po as $item) {

			$sts = '<div class="badge bg-blue">Waiting</div>';
			$close = 0;
			if ($item['ttl_persen_dp'] == $item['progress']) {
				$sts = '<div class="badge bg-green">Complete</div>';
				$close = 1;
			} else {
				if ($item['ttl_persen_dp'] > 0 && $item['ttl_persen_dp'] < 100) {
					$sts = '<div class="badge bg-yellow">Partial</div>';
				}
			}

			$get_incoming = $this->db->get_where('tr_incoming_check', ['no_ipp' => $item['no_po']])->result();
			$arr_id_incoming = [];

			foreach ($get_incoming as $item_incoming) {
				$arr_id_incoming[] = $item_incoming->kode_trans;
			}

			if (!empty($arr_id_incoming)) {
				$this->db->select('count(a.no_po) as num_po');
				$this->db->from('tr_invoice_po a');
				$this->db->where_in('a.no_po', $arr_id_incoming);
				$num_invoice = $this->db->get()->row();

				if ($num_invoice->num_po > 0) {
					$sts = '<div class="badge bg-green">Complete</div>';
					$close = 1;
				}
			}



			$view_btn = '';
			$req_pay_btn = '<button type="button" class="btn btn-sm btn-primary req_app" style="margin-left: 0.5rem" title="Request Payment" data-no_po="' . $item['no_surat'] . '" data-id_top="' . $item['id_top'] . '" data-tipe="dp"><i class="fa fa-arrow-up"></i></button>';
			if ($close == 1) {
				$get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['no_surat'], 'id_top' => $item['id_top']])->row_array();

				$view_btn = '<button type="button" class="btn btn-sm btn-info view" data-id="' . $get_invoice['id'] . '" data-id_top="' . $get_invoice['id_top'] . '" data-tipe="dp" title="view"><i class="fa fa-eye"></i></button>';
				$req_pay_btn = '';
			}

			$list_dp_btn = '';
			// if($item['ttl_persen_dp'] > 0) {
			//     $list_dp_btn = '<button type="button" class="btn btn-sm btn-warning list_dp" data-no_po="'.$item['no_po'].'" style="margin-left: 0.5rem"><i class="fa fa-list"></i></button>';
			// }
			$no_purchase_invoice = [];
			$no_invoice = [];

			$get_invoice = $this->db->select('a.*')
				->from('tr_invoice_po a')
				->where('a.id_top', $item['id_top'])
				->like('a.no_po', $item['no_surat'])
				->get()
				->result();

			foreach ($get_invoice as $item_invoice) {
				$no_purchase_invoice[] = str_replace(',', '', $item_invoice->id);
				$no_invoice[] = str_replace(',', '', $item_invoice->invoice_no);
			}

			if (!empty($no_purchase_invoice)) {
				$no_purchase_invoice = implode(', ', $no_purchase_invoice);
			} else {
				$no_purchase_invoice = '';
			}

			if (!empty($no_invoice)) {
				$no_invoice = implode(', ', $no_invoice);
			} else {
				$no_invoice = '';
			}

			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $no . '</td>';
			$hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
			$hasil .= '<td class="text-center">' . $no_purchase_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $no_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_supplier'] . '</td>';
			$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
			$hasil .= '<td class="text-center">' . $item['keterangan_top'] . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_lengkap'] . '</td>';
			$hasil .= '<td class="text-center">' . $sts . '</td>';
			$hasil .= '<td style="text-align: center;">' . $view_btn . $req_pay_btn . $list_dp_btn . '</td>';
			$hasil .= '</tr>';

			$no++;
		}
		$hasil .= '
            </tbody>
        </table>
		';

		echo $hasil;
	}

	public function search_ret()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
		$this->db->from('tr_purchase_order a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
		$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
		$this->db->where('e.group_top', 78);
		$this->db->where('a.status', '2');
		$this->db->order_by('a.created_on', 'desc');
		if ($kode_supplier !== '') {
			$this->db->where('a.id_suplier', $kode_supplier);
		}
		$this->db->group_by('e.id');
		$get_list_po = $this->db->get()->result_array();

		$hasil = '
			<table class="table table-bordered table_req_pay_ret">
            <thead class="bg-red">
                <tr>
					<th class="text-center">No</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. Purchase Invoice</th>
					<th class="text-center">No. Invoice</th>
					<th class="text-center">Nama Supplier</th>
					<th class="text-center">Tanggal PO</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Created By</th>
					<th class="text-center">Status</th>
					<th class="text-center">Action</th>
				</tr>
            </thead>
            <tbody>
			';

		// $no_po = [];
		// foreach ($get_list_po as $item) {
		// 	$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
		// 	if (!empty($get_no_po)) {
		// 		$list_no_po = [];
		// 		foreach ($get_no_po as $item_no_po) {
		// 			$list_no_po[] = $item_no_po->no_surat;
		// 		}

		// 		if (!empty($list_no_po)) {
		// 			$list_no_po = implode(', ', $list_no_po);

		// 			$no_po[$item['kode_trans']] = $list_no_po;
		// 		} else {
		// 			$no_po[$item['kode_trans']] = '';
		// 		}
		// 	} else {
		// 		$no_po[$item['kode_trans']] = '';
		// 	}
		// }

		// $total_invoice = [];
		// foreach ($get_list_po as $item) {
		// 	$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
		// 	if (!empty($get_total_invoice)) {
		// 		$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
		// 	} else {
		// 		$total_invoice[$item['kode_trans']] = 0;
		// 	}
		// }

		$no = 1;
		foreach ($get_list_po as $item) {

			$sts = '<div class="badge bg-blue">Waiting</div>';
			$close = 0;
			if ($item['ttl_persen_dp'] == $item['progress']) {
				$sts = '<div class="badge bg-green">Complete</div>';
				$close = 1;
			} else {
				if ($item['ttl_persen_dp'] > 0 && $item['ttl_persen_dp'] < 100) {
					$sts = '<div class="badge bg-yellow">Partial</div>';
				}
			}

			$get_incoming = $this->db->get_where('tr_incoming_check', ['no_ipp' => $item['no_po']])->result();
			$arr_id_incoming = [];

			foreach ($get_incoming as $item_incoming) {
				$arr_id_incoming[] = $item_incoming->kode_trans;
			}

			if (!empty($arr_id_incoming)) {
				$this->db->select('count(a.no_po) as num_po');
				$this->db->from('tr_invoice_po a');
				$this->db->where_in('a.no_po', $arr_id_incoming);
				$num_invoice = $this->db->get()->row();

				if ($num_invoice->num_po > 0) {
					$sts = '<div class="badge bg-green">Complete</div>';
					$close = 1;
				}
			}



			$view_btn = '';
			$req_pay_btn = '<button type="button" class="btn btn-sm btn-primary req_app" style="margin-left: 0.5rem" title="Request Payment" data-no_po="' . $item['no_surat'] . '" data-id_top="' . $item['id_top'] . '" data-tipe="dp"><i class="fa fa-arrow-up"></i></button>';
			if ($close == 1) {
				$get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['no_surat'], 'id_top' => $item['id_top']])->row_array();

				$view_btn = '<button type="button" class="btn btn-sm btn-info view" data-id="' . $get_invoice['id'] . '" data-id_top="' . $get_invoice['id_top'] . '" data-tipe="dp" title="view"><i class="fa fa-eye"></i></button>';
				$req_pay_btn = '';
			}

			$list_dp_btn = '';
			// if($item['ttl_persen_dp'] > 0) {
			//     $list_dp_btn = '<button type="button" class="btn btn-sm btn-warning list_dp" data-no_po="'.$item['no_po'].'" style="margin-left: 0.5rem"><i class="fa fa-list"></i></button>';
			// }
			$no_purchase_invoice = [];
			$no_invoice = [];

			$get_invoice = $this->db->select('a.*')
				->from('tr_invoice_po a')
				->where('a.id_top', $item['id_top'])
				->like('a.no_po', $item['no_surat'])
				->get()
				->result();

			foreach ($get_invoice as $item_invoice) {
				$no_purchase_invoice[] = str_replace(',', '', $item_invoice->id);
				$no_invoice[] = str_replace(',', '', $item_invoice->invoice_no);
			}

			if (!empty($no_purchase_invoice)) {
				$no_purchase_invoice = implode(', ', $no_purchase_invoice);
			} else {
				$no_purchase_invoice = '';
			}

			if (!empty($no_invoice)) {
				$no_invoice = implode(', ', $no_invoice);
			} else {
				$no_invoice = '';
			}

			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $no . '</td>';
			$hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
			$hasil .= '<td class="text-center">' . $no_purchase_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $no_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_supplier'] . '</td>';
			$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
			$hasil .= '<td class="text-center">' . $item['keterangan_top'] . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_lengkap'] . '</td>';
			$hasil .= '<td class="text-center">' . $sts . '</td>';
			$hasil .= '<td style="text-align: center;">' . $view_btn . $req_pay_btn . $list_dp_btn . '</td>';
			$hasil .= '</tr>';

			$no++;
		}
		$hasil .= '
            </tbody>
        </table>
		';

		echo $hasil;
	}

	public function check_list_inc()
	{
		$get_list_inc = $this->db->query('
				SELECT
					a.kode_trans as kode_trans,
					a.no_ipp as no_ipp,
					a.inc_inv_create as inc_inv_create,
					a.tanggal as tanggal,
					"incoming material" as tipe_incoming
				FROM
					tr_incoming_check a
				WHERE
					a.checked = "Y"

				UNION ALL

				SELECT 
					a.kode_trans as kode_trans,
					a.no_ipp as no_ipp,
					a.inc_inv_create as inc_inv_create,
					a.tanggal as tanggal,
					a.category as tipe_incoming
				FROM
					warehouse_adjustment a
				WHERE
					a.category = "incoming stok" OR a.category = "incoming non rutin" OR a.category = "incoming asset"
			')->result_array();

		$no_po = [];
		foreach ($get_list_inc as $item) {
			$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "') OR a.no_surat IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
			if (!empty($get_no_po)) {
				$list_no_po = [];
				foreach ($get_no_po as $item_no_po) {
					$list_no_po[] = $item_no_po->no_surat;
				}

				if (!empty($list_no_po)) {
					$list_no_po = implode(', ', $list_no_po);

					$no_po[$item['kode_trans']] = $list_no_po;
				} else {
					$no_po[$item['kode_trans']] = '';
				}
			} else {
				$no_po[$item['kode_trans']] = '';
			}
		}

		$total_invoice = [];
		foreach ($get_list_inc as $item) {
			$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
			if (!empty($get_total_invoice)) {
				$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
			} else {
				if ($item['category'] == 'incoming non rutin') {
					$this->db->select('SUM(a.total_harga) as ttl_harga');
					$this->db->from('tr_pr_detail_kasbon a');
					$this->db->where('a.id_kasbon', $item['no_ipp']);
					$get_total = $this->db->get()->row();

					$total_invoice[$item['kode_trans']] = $get_total->ttl_harga;
				} else if ($item['category'] == 'incoming asset') {
					$this->db->select('SUM(a.harga_total) as ttl_harga');
					$this->db->from('dt_trans_po a');
					$this->db->join('tr_purchase_order b', 'b.no_po = a.no_po');
					$this->db->where('b.no_surat', $item['no_ipp']);
					$get_total = $this->db->get()->row();

					$total_invoice[$item['kode_trans']] = $get_total->ttl_harga;
				} else {
					$total_invoice[$item['kode_trans']] = 0;
				}
			}
		}

		$get_supplier = $this->db->get('new_supplier')->result();

		$this->template->set('list_inc', $get_list_inc);
		$this->template->set('no_po', $no_po);
		$this->template->set('total_invoice', $total_invoice);
		$this->template->set('list_supplier', $get_supplier);
		$this->template->render('check_list_inc');
	}

	public function check_invoice()
	{
		$kode_trans = $this->input->post('kode_trans');
		$tipe_incoming = $this->input->post('tipe_incoming');
		$tipe = $this->input->post('tipe');

		$this->db->trans_start();
		if ($tipe == 1) {
			$checked_invoice = $this->db->get_where('tr_check_invoice', ['kode_trans' => $kode_trans, 'id_user' => $this->auth->user_id()])->result();
			if (count($checked_invoice) < 1) {
				$insert_check_invoice = $this->db->insert('tr_check_invoice', [
					'kode_trans' => $kode_trans,
					'id_user' => $this->auth->user_id()
				]);
				if (!$insert_check_invoice) {
					print_r($this->db->error($insert_check_invoice));
					exit;
				}
			}
		} else {
			$this->db->delete('tr_check_invoice', ['kode_trans' => $kode_trans, 'id_user' => $this->auth->user_id()]);
		}

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

	public function clear_checked_invoice()
	{
		$this->db->trans_start();

		$this->db->delete('tr_check_invoice', ['id_user' => $this->auth->user_id()]);
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

	public function checkCheckedInv()
	{
		$get_checked_invoice = $this->db->get_where('tr_check_invoice', ['id_user' => $this->auth->user_id()])->result();

		echo count($get_checked_invoice);
	}

	public function rec_invoice_btn()
	{
		$get_checked_invoice = $this->db->select('kode_trans')->get_where('tr_check_invoice', ['id_user' => $this->auth->user_id()])->result();
		$no_incoming = [];
		foreach ($get_checked_invoice as $item) {
			$no_incoming[] = $item->kode_trans;
		}

		if (!empty($no_incoming)) {
			$incoming_no = implode(', ', $no_incoming);
		} else {
			$incoming_no = '';
		}

		$arr_no_po = [];
		$ppn_asli = 0;
		$get_no_po = $this->db->query("
			SELECT
				b.no_surat as surat_no,
				b.total_ppn as total_ppn,
				b.uang_muka,
				b.uang_muka_idr,
				b.kurs_terima_barang,
				b.matauang

			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
			WHERE
				a.id IN (SELECT aa.id_po_detail FROM tr_incoming_check_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY b.no_surat

			UNION ALL

			SELECT
				b.no_surat as surat_no,
				b.total_ppn as total_ppn,
				b.uang_muka,
				b.uang_muka_idr,
				b.kurs_terima_barang,
				b.matauang
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
			WHERE
				a.id IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY b.no_surat

			UNION ALL

			SELECT
				a.no_pr as surat_no,
				0 as total_ppn,
				b.uang_muka,
				b.uang_muka_idr,
				b.kurs_terima_barang,
				'IDR' as matauang
			FROM
				rutin_non_planning_detail a
				JOIN rutin_non_planning_header b ON b.no_pengajuan = a.no_pengajuan
			WHERE
				a.id IN (SELECT aa.id_po_detail FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY a.no_pr

			UNION ALL

			SELECT
				b.no_surat as surat_no,
				b.total_ppn as total_ppn,
				b.uang_muka,
				b.uang_muka_idr,
				b.kurs_terima_barang,
				b.matauang
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
			WHERE
				b.no_surat IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "')) AND b.tipe = 'pr asset'
			GROUP BY b.no_surat
		")->result();

		foreach ($get_no_po as $item_no_po) {
			$arr_no_po[] = $item_no_po->surat_no;
			$ppn_asli += $item_no_po->total_ppn;
			$uang_muka = $item_no_po->uang_muka;
			$uang_muka_idr = $item_no_po->uang_muka_idr;
			$kurs_terima_barang = $item_no_po->kurs_terima_barang;
			if ($item_no_po->matauang == 'IDR') {
				$kurs_terima_barang = 1;
			}
		}

		$arrNmSupplier = [];
		$arrKdSupplier = [];
		$get_nm_supplier = $this->db->query("
			SELECT
				c.nama as nm_supplier,
				c.kode_supplier as kode_supplier
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
				LEFT JOIN new_supplier c ON c.kode_supplier = b.id_suplier
			WHERE
				a.id IN (SELECT aa.id_po_detail FROM tr_incoming_check_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
				GROUP BY c.nama

			UNION ALL

			SELECT
				c.nama as nm_supplier,
				c.kode_supplier as kode_supplier
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
				LEFT JOIN new_supplier c ON c.kode_supplier = b.id_suplier
			WHERE
				a.id IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY c.nama

			UNION ALL

			SELECT
				c.nama as nm_supplier,
				c.kode_supplier as kode_supplier
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
				LEFT JOIN new_supplier c ON c.kode_supplier = b.id_suplier
			WHERE
				b.no_surat IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY c.nama
		")->result();
		foreach ($get_nm_supplier as $item_supplier) {
			$arrNmSupplier[] = $item_supplier->nm_supplier;
			$arrKdSupplier[] = $item_supplier->kode_supplier;
		}

		if (!empty($arrNmSupplier)) {
			$nm_supplier = implode(', ', $arrNmSupplier);
			$kode_supplier = implode(',', $arrKdSupplier);
		} else {
			$nm_supplier = '';
			$kode_supplier = '';
		}

		$arrCurrency = [];
		$get_currency = $this->db->query("
			SELECT
				b.matauang as currency
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
			WHERE
				a.id IN (SELECT aa.id_po_detail FROM tr_incoming_check_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY b.matauang

			UNION ALL

			SELECT
				b.matauang as currency
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
			WHERE
				a.id IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY b.matauang

			UNION ALL

			SELECT
				b.matauang as currency
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
			WHERE
				b.no_surat IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY b.matauang
		")->result();
		foreach ($get_currency as $item_currency) {
			if ($item_currency->currency !== '') {
				$arrCurrency[] = $item_currency->currency;
			}
		}

		if (!empty($arrCurrency)) {
			$currency = implode(', ', $arrCurrency);
		} else {
			$currency = '';
		}

		$value_dp = 0;
		$get_value_dp = $this->db->query("
			SELECT
				c.nilai as nilai_dp_material,
				0 as nilai_dp_stok
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
				LEFT JOIN tr_top_po c ON c.no_po = b.no_po
			WHERE
				a.id IN (SELECT aa.id_po_detail FROM tr_incoming_check_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY c.id

			UNION ALL

			SELECT
				0 as nilai_dp_material,
				c.nilai as nilai_dp_stok
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
				LEFT JOIN tr_top_po c ON c.no_po = b.no_po
			WHERE
				a.id IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY c.id

			UNION ALL

			SELECT
				0 as nilai_dp_material,
				c.nilai as nilai_dp_stok
			FROM
				dt_trans_po a
				JOIN tr_purchase_order b ON b.no_po = a.no_po
				LEFT JOIN tr_top_po c ON c.no_po = b.no_po
			WHERE
				b.no_surat IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "'))
			GROUP BY c.id
		")->result();
		foreach ($get_value_dp as $item_dp) {
			$value_dp += ($item_dp->nilai_dp_material + $item_dp->nilai_dp_stok);
		}

		$total_invoice = 0;
		$get_ttl_invoice = $this->db->query("
			SELECT
				c.qty_oke as qty_oke,
				b.hargasatuan as hargasatuan
			FROM
				tr_incoming_check_detail a
				JOIN dt_trans_po b ON b.id = a.id_po_detail
				JOIN tr_checked_incoming_detail c ON c.kode_trans = a.kode_trans AND c.id_material = a.id_material
			WHERE
				a.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "')
			
			UNION ALL

			SELECT
				a.qty_oke as qty_oke,
				b.hargasatuan as hargasatuan
			FROM
				warehouse_adjustment_detail a
				JOIN dt_trans_po b ON b.id = a.no_ipp
				LEFT JOIN warehouse_adjustment c ON c.kode_trans = a.kode_trans
			WHERE
				a.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "')  AND
				c.category = 'incoming stok'

			UNION ALL

			SELECT
				a.qty_oke as qty_oke,
				b.harga as hargasatuan
			FROM
				warehouse_adjustment_detail a
				JOIN tr_pr_detail_kasbon b ON b.id_detail = a.id_po_detail AND b.id_kasbon = a.no_ipp
				LEFT JOIN warehouse_adjustment c ON c.kode_trans = a.kode_trans
			WHERE
				a.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "') AND 
				c.category = 'incoming non rutin'

			UNION ALL

			SELECT
				a.qty_oke as qty_oke,
				d.hargasatuan as hargasatuan
			FROM
				warehouse_adjustment_detail a
				JOIN tr_purchase_order b ON b.no_surat = a.no_ipp
				JOIN dt_trans_po d ON d.no_po = b.no_po AND a.nm_material = d.namamaterial
				LEFT JOIN warehouse_adjustment c ON c.kode_trans = a.kode_trans
			WHERE
				a.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "')  AND
				c.category = 'incoming asset'
		")->result();

		// print_r($this->db->error($get_ttl_invoice));
		// exit;
		foreach ($get_ttl_invoice as $item_ttl_invoice) {
			$total_invoice += ($item_ttl_invoice->hargasatuan * $item_ttl_invoice->qty_oke);
		}

		$nilai_disc = 0;
		$get_nilai_disc = $this->db->query("
			SELECT
				d.qty_oke,
				b.hargasatuan,
				b.persen_disc as persen_disc_item,
				c.persen_disc as persen_disc_po
			FROM
				tr_incoming_check_detail a
				JOIN dt_trans_po b ON b.id = a.id_po_detail
				JOIN tr_purchase_order c ON c.no_po = b.no_po
				JOIN tr_checked_incoming_detail d ON d.kode_trans = a.kode_trans AND d.id_material = a.id_material
			WHERE
				a.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "')
		")->result();
		foreach ($get_nilai_disc as $item_nilai_disc) {
			if ($item_nilai_disc->persen_disc_item > 0) {
				$persen_disc = $item_nilai_disc->persen_disc_item;
			} else {
				$persen_disc = $item_nilai_disc->persen_disc_po;
			}

			$nilai_awal = ($item_nilai_disc->hargasatuan * $item_nilai_disc->qty_oke);
			$nilai_disc += (($nilai_awal) * $persen_disc / 100);
		}

		$get_nilai_disc_stok = $this->db->query("
			SELECT
				a.qty_oke,
				b.hargasatuan,
				b.persen_disc as persen_disc_item,
				c.persen_disc as persen_disc_po
			FROM
				warehouse_adjustment_detail a
				JOIN dt_trans_po b ON b.id = a.no_ipp
				JOIN tr_purchase_order c ON c.no_po = b.no_po
				JOIN tr_checked_incoming_detail d ON d.kode_trans = a.kode_trans AND d.id_material = a.id_material
			WHERE
				a.kode_trans IN ('" . str_replace(",", "','", implode(',', $no_incoming)) . "')
		")->result();
		foreach ($get_nilai_disc_stok as $item_nilai_disc) {
			if ($item_nilai_disc->persen_disc_item > 0) {
				$persen_disc = $item_nilai_disc->persen_disc_item;
			} else {
				$persen_disc = $item_nilai_disc->persen_disc_po;
			}

			$nilai_awal = ($item_nilai_disc->hargasatuan * $item_nilai_disc->qty_oke);
			$nilai_disc += (($nilai_awal) * $persen_disc / 100);
		}

		if ($nilai_disc <= 0) {
			$this->db->select('a.qty_oke, c.hargasatuan, c.persen_disc as persen_disc_item, b.persen_disc as persen_disc_po');
			$this->db->from('warehouse_adjustment_detail a');
			$this->db->join('tr_purchase_order b', 'b.no_surat = a.no_ipp');
			$this->db->join('dt_trans_po c', 'c.no_po = b.no_po AND c.namamaterial = a.nm_material', 'left');
			$this->db->where_in('a.kode_trans', $no_incoming);
			$get_nilai_disc_asset = $this->db->get()->result();

			// print_r($this->db->last_query());
			// exit;

			foreach ($get_nilai_disc_asset as $item_nilai_disc) {
				if ($item_nilai_disc->persen_disc_item > 0) {
					$persen_disc = $item_nilai_disc->persen_disc_item;
				} else {
					$persen_disc = $item_nilai_disc->persen_disc_po;
				}

				$nilai_awal = ($item_nilai_disc->hargasatuan * $item_nilai_disc->qty_oke);
				$nilai_disc += (($nilai_awal) * $persen_disc / 100);
			}
		}

		$nilai_ppn = ((($total_invoice * $kurs_terima_barang) - $uang_muka_idr) * 11 / 100);
		if ($ppn_asli <= 0) {
			$nilai_ppn = 0;
		}
		$nilai_req_payment = (($total_invoice * $kurs_terima_barang) + $nilai_ppn - $nilai_disc - $value_dp);

		$data = [
			'no_incoming' => $no_incoming,
			'incoming_no' => $incoming_no,
			'nm_supplier' => $nm_supplier,
			'kode_supplier' => $kode_supplier,
			'currency' => $currency,
			'value_dp' => $uang_muka_idr,
			'total_invoice' => ($total_invoice * $kurs_terima_barang),
			'nilai_disc' => $nilai_disc,
			'nilai_ppn' => $nilai_ppn,
			'nilai_req_payment' => $nilai_req_payment,
			'no_po' => $arr_no_po
		];

		$this->template->set('results', $data);
		$this->template->render('add_inc');
	}

	// public function list_dp(){
	// 	$no_po = $this->input->post('no_po');

	// 	$get_po_dp = $this->db->get_where()
	// }


}
