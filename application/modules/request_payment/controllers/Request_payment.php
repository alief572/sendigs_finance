<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is controller for Request Payment
 */

$status = array();
class Request_payment extends Admin_Controller
{

	//Permission
	protected $viewPermission   = "Request_Payment.View";
	protected $addPermission    = "Request_Payment.Add";
	protected $managePermission = "Request_Payment.Manage";
	protected $deletePermission = "Request_Payment.Delete";

	protected $status;

	protected $consultant;

	public function __construct()
	{
		parent::__construct();
		$this->consultant = $this->load->database('consultant', true);
		$this->load->model(array('Request_payment/Request_payment_model', 'All/All_model', 'Jurnal_nomor/Jurnal_model'));
		$this->template->title('Manage Request Payment');
		$this->template->page_icon('fa fa-table');
		$this->status = array("0" => "Baru", "1" => "Disetujui", "2" => "Selesai");
		date_default_timezone_set("Asia/Bangkok");
	}

	public function index()
	{
		$this->template->title('Request Payment');
		// Load companies for filter dropdown
		$companies = $this->Request_payment_model->get_companies_list();
		$this->template->set('companies', $companies);
		$this->template->render('index');
	}

	public function payment_list()
	{
		$data = $this->Request_payment_model->GetListDataPaymentList();
		$list_tgl_pengajuan_pembayaran = $this->Request_payment_model->get_payment_paid();

		$this->template->set('data', $data);
		$this->template->set('list_tgl_pengajuan_pembayaran', $list_tgl_pengajuan_pembayaran);
		$this->template->title('Payment List');
		$this->template->render('payment_list');
	}

	public function save_request()
	{
		$status	= $this->input->post("status");
		$this->db->trans_begin();
		if (!empty($status)) {
			foreach ($status as $val) {
				// print_r($this->input->post("tanggal_" . $val));
				// exit;

				$config['upload_path'] = './assets/expense/';
				$config['allowed_types'] = '*';
				$config['remove_spaces'] = TRUE;
				$config['encrypt_name'] = TRUE;

				$filenames = '';
				$this->upload->initialize($config);
				if ($this->upload->do_upload('upload_doc_' . $val)) {
					$uploadData = $this->upload->data();
					$filenames = $uploadData['file_name'];
				}

				$tipe = $this->input->post("tipe_" . $val);
				$no_doc = $this->input->post("no_doc_" . $val);
				$data =  array(
					'no_doc' => $no_doc,
					'nama' => $this->input->post("nama_" . $val),
					'tgl_doc' => $this->input->post("tgl_doc_" . $val),
					'tanggal' => date('Y-m-d', strtotime($this->input->post("tanggal_" . $val))),
					'keperluan' => $this->input->post("keperluan_" . $val),
					'tipe' => $tipe,
					'jumlah' => $this->input->post("jumlah_" . $val),
					'ids' => $this->input->post("ids_" . $val),
					'status' => 0,
					'bank_id' => $this->input->post("bank_id_" . $val),
					'accnumber' => $this->input->post("accnumber_" . $val),
					'accname' => $this->input->post("accname_" . $val),
					'created_by' => $this->auth->user_name(),
					'created_on' => date("Y-m-d h:i:s"),
					'currency' => $this->input->post('currency_' . $val),
					'bank_name' => $this->input->post('bank_' . $val),
					'admin_bank' => str_replace(',', '', $this->input->post('admin_charge_' . $val)),
					'tipe_pph' => $this->input->post('tipe_pph_' . $val),
					'total_pph' => str_replace(',', '', $this->input->post('nilai_pph_' . $val)),
					'link_doc' => $filenames
				);
				$idreq = $this->All_model->dataSave('request_payment', $data);
				if ($tipe == 'transportasi') {
					$this->All_model->dataUpdate('tr_transport_req', array('status' => 2), array('no_doc' => $no_doc));
				}
				if ($tipe == 'kasbon') {
					$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $no_doc])->row();
					if ($get_kasbon->status = 4) {
						$this->All_model->dataUpdate('tr_kasbon', array('status' => 2), array('no_doc' => $no_doc));
					} else {
						$this->All_model->dataUpdate('tr_kasbon', array('status' => 2), array('no_doc' => $no_doc));
					}
				}
				if ($tipe == 'expense') {
					$this->All_model->dataUpdate('tr_expense', array('status' => 2), array('no_doc' => $no_doc));
				}
				if ($tipe == 'nonpo') {
					$this->All_model->dataUpdate('tr_non_po_header', array('status' => 4), array('no_doc' => $no_doc));
				}
				if ($tipe == 'periodik') {
					$this->All_model->dataUpdate('tr_pengajuan_rutin_detail', array('id_payment' => $idreq), array('no_doc' => $no_doc, 'id' => $this->input->post("ids_" . $val)));
				}
				if ($tipe == 'direct_payment') {
					$this->db->update('tr_direct_payment', ['sts' => 2], ['no_doc' => $this->input->post('no_doc_' . $val)]);
				}
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
		} else {
			$this->db->trans_commit();
			$result = true;
		}
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	public function list_approve()
	{
		$data = $this->Request_payment_model->GetListDataApproval('status <> 2');

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from('tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$this->template->set('data', $data);
		$this->template->set('list_no_invoice', $list_no_invoice);
		$this->template->title('Request Payment Approval');
		$this->template->render('list_approve');
	}

	public function list_approve_checker()
	{
		$data = $this->Request_payment_model->GetListDataApproval('a.status <> 2 AND a.app_checker IS NULL');

		$data_kasbon = $this->Request_payment_model->GetListDataApproval('1 = 1');
		$data_expense = $this->Request_payment_model->GetListDataApproval('1 = 1');

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from('tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$this->template->set('tingkat_approval', 1);
		$this->template->set('data', $data);
		$this->template->set('data_kasbon', $data_kasbon);
		$this->template->set('data_expense', $data_expense);
		$this->template->set('list_no_invoice', $list_no_invoice);
		$this->template->title('Request Payment Approval Checker');
		$this->template->render('list_approve_checker');
	}

	public function list_approve_management()
	{
		$data = $this->Request_payment_model->GetListDataApproval('a.status <> 2 AND a.app_checker = 1');
		// echo '<pre>';
		// print_r($data);
		// echo '</pre>';
		// die();

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from('tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$this->template->set('tingkat_approval', 2);
		$this->template->set('data', $data);
		$this->template->set('list_no_invoice', $list_no_invoice);
		$this->template->title('Request Payment Approval Management');
		$this->template->render('list_approve_management');
	}

	/* 
	##########
	# Updated by Hikmat A.R 15-08-2022
	##########
	*/

	public function approval_payment($type = null, $id = null)
	{
		$type 		= $_GET['type'];
		$id_exp 	= $_GET['id'];

		$get_id = $this->db->get_where('request_payment', ['id' => $id_exp])->row();

		$id = $get_id->ids;

		$this->template->title('Approval Payment');

		/* Expense */
		if (isset($type) && $type == 'expense') {
			$data 			= $this->db->get_where('tr_expense', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_expense_detail', ['no_doc' => $data->no_doc, 'req_payment' => 1])->result();
		}

		/* Kasbon */
		$kasbon_pr = 0;
		$data_detail_pr_kasbon = '';
		if (isset($type) && $type == 'kasbon') {
			$data 			= $this->db->get_where('tr_kasbon', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_kasbon', ['id' => $id])->result();
			if (!empty($data->id_pr)) {
				$kasbon_pr = 1;
				$data_detail_pr_kasbon = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $data->no_doc])->result();
			}
		}

		/* Transportasi */
		if (isset($type) && $type == 'transportasi') {
			$data 			= $this->db->get_where('tr_transport_req', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_transport', ['no_req' => $data->no_doc])->result();
		}

		/* NON PO */
		if (isset($type) && $type == 'nonpo') {
			$data 			= $this->db->get_where('tr_non_po_header', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_non_po_detail', ['no_doc' => $data->no_doc])->result();
		}

		/* Periodik/Rutin */
		if (isset($type) && $type == 'periodik') {
			$data 			= $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $id])->result();
		}

		// Direct Payment
		if (isset($type) && $type == 'direct_payment') {
			$data 			= $this->db->get_where('tr_direct_payment', ['no_doc' => $get_id->no_doc])->row();
			$data_detail	= $this->db->get_where('tr_direct_payment', ['no_doc' => $get_id->no_doc])->result();
		}

		// $data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		// $data_pc 		= $this->All_model->GetPettyCashCombo();

		// $this->template->set('data_pc', $data_pc);
		// $this->template->set('data_budget', $data_budget);
		// $this->template->set('data_detail', $data_detail);
		// $this->template->set('status', $this->status);
		// $this->template->set('data', $data);
		// $this->template->set('stsview', 'view');

		$get_req_payment = $this->db->get_where('request_payment', ['id' => $id_exp])->row_array();

		$list_coa = [];
		$get_coa = $this->db->get(DBACC . '.coa_master')->result();
		foreach ($get_coa as $item_coa) {
			$list_coa[$item_coa->no_perkiraan] = $item_coa->nama;
		}

		$this->template->set([
			'type'		 => $type,
			'header'	 => $data,
			'details' 	=> $data_detail,
			'kasbon_pr' => $kasbon_pr,
			'data_detail_pr_kasbon' => $data_detail_pr_kasbon,
			'data_req_payment' => $get_req_payment,
			'list_coa' => $list_coa
		]);
		$this->template->render('detail_approve');
	}

	public function approval_payment_checker($type = null, $id = null)
	{
		$type 		= $_GET['type'];
		$id_exp 	= $_GET['id'];

		$get_id = $this->db->get_where('request_payment', ['id' => $id_exp])->row();

		$id = $get_id->ids;

		$this->template->title('Approval Payment');

		/* Expense */
		if (isset($type) && $type == 'expense') {
			$data 			= $this->db->get_where('tr_expense', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_expense_detail', ['no_doc' => $data->no_doc, 'id_kasbon' => null])->result();
		}

		/* Kasbon */
		$kasbon_pr = 0;
		$data_detail_pr_kasbon = '';
		if (isset($type) && $type == 'kasbon') {
			$data 			= $this->db->get_where('tr_kasbon', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_kasbon', ['id' => $id])->result();
			if (!empty($data->id_pr)) {
				$kasbon_pr = 1;
				$data_detail_pr_kasbon = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $data->no_doc])->result();
			}
		}

		/* Transportasi */
		if (isset($type) && $type == 'transportasi') {
			$data 			= $this->db->get_where('tr_transport_req', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_transport', ['no_req' => $data->no_doc])->result();
		}

		/* NON PO */
		if (isset($type) && $type == 'nonpo') {
			$data 			= $this->db->get_where('tr_non_po_header', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_non_po_detail', ['no_doc' => $data->no_doc])->result();
		}

		/* Periodik/Rutin */
		if (isset($type) && $type == 'periodik') {
			$data 			= $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $id])->row();
			$data_detail	= $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $id])->result();
		}

		// Direct Payment
		if (isset($type) && $type == 'direct_payment') {
			$data 			= $this->db->get_where('tr_direct_payment', ['no_doc' => $get_id->no_doc])->row();
			$data_detail	= $this->db->get_where('tr_direct_payment', ['no_doc' => $get_id->no_doc])->result();
		}

		// $data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		// $data_pc 		= $this->All_model->GetPettyCashCombo();

		// $this->template->set('data_pc', $data_pc);
		// $this->template->set('data_budget', $data_budget);
		// $this->template->set('data_detail', $data_detail);
		// $this->template->set('status', $this->status);
		// $this->template->set('data', $data);
		// $this->template->set('stsview', 'view');

		$get_req_payment = $this->db->get_where('request_payment', ['id' => $id_exp])->row_array();

		$list_coa = [];
		$get_coa = $this->db->get(DBACC . '.coa_master')->result();
		foreach ($get_coa as $item_coa) {
			$list_coa[$item_coa->no_perkiraan] = $item_coa->nama;
		}

		$this->template->set([
			'type'		 			=> $type,
			'header'	 			=> $data,
			'details' 				=> $data_detail,
			'kasbon_pr' 			=> $kasbon_pr,
			'data_detail_pr_kasbon' => $data_detail_pr_kasbon,
			'data_req_payment' 		=> $get_req_payment,
			'list_coa' 				=> $list_coa
		]);
		$this->template->render('detail_approve_checker');
	}


	/* public function save_approval()
	{
		$status	= $this->input->post("status");
		$this->db->trans_begin();
		if (!empty($status)) {
			foreach ($status as $val) {
				$data =  array(
					'status' => 1,
					'approved_by' => $this->auth->user_name(),
					'approved_on' => date("Y-m-d h:i:s"),
				);
				$this->All_model->dataUpdate('request_payment', $data, array('id' => $val));
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
		} else {
			$this->db->trans_commit();
			$result = true;
		}
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	} */


	/* 
	Update by Hikmat A.R (16/08)
	*/
	private function _getIdPayment($date)
	{
		$count 		= 1;
		//		$m 			= date_format(date_create($date), 'm');
		$y 			= date_format(date_create($date), 'Y');

		$sql 		= "SELECT count(id) as max_id FROM payment_approve where YEAR(tgl_doc) = '$y'";
		$max_id 	= $this->db->query($sql)->row()->max_id;

		if ($max_id > 0) {
			$max_id = (int)$max_id;
			$count 	= $max_id + 1;
		}
		$new_id  	= 'PAY' . $y . str_pad($count, 5, '0', STR_PAD_LEFT);
		return  $new_id;
	}


	private function _getIdDetail($payment_id)
	{
		$count 		= 1;
		$sql 		= "SELECT MAX(RIGHT(id,2)) as max_id FROM payment_approve_details where payment_id = '$payment_id'";
		$max_id 	= $this->db->query($sql)->row()->max_id;

		if ($max_id > 0) {
			$count 	= $max_id + 1;
		}

		// $new_id  	= 'PAY' . date('ym') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
		return  $count;
	}

	public function save_approval_cons()
	{
		$id = $this->input->post('id');
		$no_doc_sendigs = $this->input->post('no_doc_sendigs');
		$id_expense = $this->input->post('id_expense');

		$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $no_doc_sendigs))->row();

		$get_user = $this->db->get_where('users', array('id_user' => $this->auth->user_id()))->row();

		if (!empty($get_request_payment)) {
			$get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $no_doc_sendigs))->row();

			$no_coa_bank = explode(' - ', $get_request_payment->bank_name);
			$no_coa_bank = $no_coa_bank[0];

			$kode_bank = '';
			$get_kode_bank = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $no_coa_bank])->row();
			if (!empty($get_kode_bank)) {
				$kode_bank = $get_kode_bank->kode_bank;
			}

			$Id = $this->Request_payment_model->generate_id_payment($kode_bank);

			$header = [
				'id' => $Id,
				'no_doc' => $no_doc_sendigs,
				'nama' => $get_user->nm_lengkap,
				'tgl_doc' => $get_kasbon->tgl_doc,
				'keperluan' => $get_kasbon->keperluan,
				'tipe' => 'kasbon',
				'jumlah' => $get_kasbon->jumlah_kasbon,
				'status' => '1',
				'tanggal' => date('Y-m-d'),
				'created_by' => $get_user->nm_lengkap,
				'created_on' => date('Y-m-d H:i:s'),
				'bank_id' => $get_kasbon->bank_id,
				'accnumber' => $get_kasbon->accnumber,
				'accname' => $get_kasbon->accname,
				'ids' => $get_kasbon->id,
				'currency' => $get_request_payment->currency,
				'bank_name' => $get_request_payment->bank_name,
				'link_doc' => $get_request_payment->link_doc
			];

			$id_detail = $this->Request_payment_model->generate_id_detail(1);

			$detail = [
				'id' => $id_detail,
				'payment_id' => $Id,
				'no_doc' => $no_doc_sendigs,
				'tgl_doc' => $get_kasbon->tgl_doc,
				'deskripsi' => $get_kasbon->keterangan,
				'qty' => 1,
				'harga' => $get_kasbon->jumlah_kasbon,
				'total' => $get_kasbon->jumlah_kasbon,
				'keterangan' => $get_kasbon->keterangan,
				'created_by' => $get_user->nm_lengkap,
				'created_on' => date('Y-m-d H:i:s')
			];
		} else {
			$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $id_expense))->row();

			$get_expense = $this->db->get_where('tr_expense', array('no_doc' => $id_expense))->row();

			$no_coa_bank = explode(' - ', $get_request_payment->bank_name);
			$no_coa_bank = $no_coa_bank[0];

			$kode_bank = '';
			$get_kode_bank = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $no_coa_bank])->row();
			if (!empty($get_kode_bank)) {
				$kode_bank = $get_kode_bank->kode_bank;
			}

			$Id = $this->Request_payment_model->generate_id_payment($kode_bank);

			$header = [
				'id' => $Id,
				'no_doc' => $id_expense,
				'nama' => $get_user->nm_lengkap,
				'tgl_doc' => $get_expense->tgl_doc,
				'keperluan' => $get_expense->informasi,
				'tipe' => 'expense',
				'jumlah' => $get_expense->jumlah,
				'status' => '1',
				'tanggal' => date('Y-m-d'),
				'created_by' => $get_user->nm_lengkap,
				'created_on' => date('Y-m-d H:i:s'),
				'bank_id' => $get_expense->bank_id,
				'accnumber' => $get_expense->accnumber,
				'accname' => $get_expense->accname,
				'ids' => $get_expense->id,
				'currency' => $get_request_payment->currency,
				'bank_name' => $get_request_payment->bank_name,
				'link_doc' => $get_request_payment->link_doc
			];

			$id_detail = $this->Request_payment_model->generate_id_detail(1);

			$detail = [
				'id' => $id_detail,
				'payment_id' => $Id,
				'no_doc' => $id_expense,
				'tgl_doc' => $get_expense->tgl_doc,
				'deskripsi' => $get_expense->informasi,
				'qty' => 1,
				'harga' => $get_expense->jumlah,
				'total' => $get_expense->jumlah,
				'keterangan' => $get_expense->informasi,
				'created_by' => $get_user->nm_lengkap,
				'created_on' => date('Y-m-d H:i:s')
			];
		}

		$this->db->trans_begin();

		$insert_payment = $this->db->insert('payment_approve', $header);
		if (!$insert_payment) {
			$this->db->trans_rollback();

			print_r($this->db->last_query());
			exit;
		}

		$insert_payment_detail = $this->db->insert('payment_approve_details', $detail);
		if (!$insert_payment_detail) {
			$this->db->trans_rollback();

			print_r($this->db->last_query());
			exit;
		}

		if ($get_request_payment->tipe == 'kasbon') {
			$arr_update_kasbon = [
				'status' => 3
			];

			$update_kasbon = $this->db->update('tr_kasbon', $arr_update_kasbon, array('no_doc' => $no_doc_sendigs));
		} else {
			$arr_update_expense = [
				'status' => 3
			];

			$update_expense = $this->db->update('tr_expense', $arr_update_expense, array('no_doc' => $id_expense));
		}

		$update_request_payment = $this->db->update('request_payment', ['status' => 2], ['no_doc' => $no_doc_sendigs]);
		$update_request_payment = $this->db->update('request_payment', ['status' => 2], ['no_doc' => $id_expense]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();

			$valid = 0;
			$msg = 'Please, try again later !';
		} else {
			$this->db->trans_commit();

			$valid = 1;
			$msg = 'Data has been approved !';
		}

		$hasil = [
			'status' => $valid,
			'msg' => $msg
		];

		echo json_encode($hasil);
	}

	public function save_approval()
	{
		$Data		= $this->input->post();
		$header 	= $this->db->get_where('request_payment', ['no_doc' => $Data['no_doc'], 'tipe' => $Data['tipe'], 'ids' => $Data['id']])->row_array();
		if ($Data['tipe'] == 'direct_payment') {
			$header 	= $this->db->get_where('request_payment', ['no_doc' => $Data['no_doc'], 'tipe' => $Data['tipe']])->row_array();
		}
		// $Id 		= $this->_getIdPayment(str_replace('/', '-', $Data['date']));

		$no_coa_bank = explode(' - ', $header['bank_name']);
		$no_coa_bank = $no_coa_bank[0];

		$kode_bank = '';
		$get_kode_bank = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $no_coa_bank])->row();
		if (!empty($get_kode_bank)) {
			$kode_bank = $get_kode_bank->kode_bank;
		}

		$Id = $this->Request_payment_model->generate_id_payment($kode_bank);

		// $detail = 
		$ArrDetail 			= [];
		// $idDetail 			= $this->_getIdDetail($Id);

		// print_r($this->Request_payment_model->generate_id_detail());
		// exit;

		$n = 0;
		foreach ($Data['item'] as $detail) {
			$n++;
			// $idDetail++;
			$id_detail = $this->Request_payment_model->generate_id_detail($n);

			if ($Data['tipe'] == 'expense') {
				$dtl 				= $this->db->get_where('tr_expense_detail', ['id' => $detail['id']])->row();
				$expense			= $this->db->get_where('tr_expense', ['id' => $Data['id']])->row();

				if ($expense->id_kasbon != null) {
					$harga = $expense->kurang_bayar;
					$total = $expense->kurang_bayar;
				} else {
					$harga = $dtl->harga;
					$total = $dtl->total_harga;
					if ($dtl->kasbon > 0) {
						$harga = ($dtl->kasbon * -1);
						$total = ($dtl->kasbon * -1);
					}
				}

				$ArrDetail[] 		= [
					'id' 			=> $id_detail,
					'payment_id' 	=> $Id,
					'no_doc' 		=> $dtl->no_doc,
					'tgl_doc' 		=> $dtl->tanggal,
					'deskripsi' 	=> $dtl->deskripsi,
					'qty' 			=> $dtl->qty,
					'harga' 		=> $harga,
					'total' 		=> $total,
					'keterangan' 	=> $dtl->keterangan,
					'doc_file' 		=> $dtl->doc_file,
					'coa' 			=> $dtl->coa,
					'created_by' 	=> $this->auth->user_name(),
					'created_on' 	=> date("Y-m-d h:i:s"),
				];

				$updateDetail[] = [
					'id' 			=> $dtl->id,
					'status' 		=> '2',
					'modified_by' 	=> $this->auth->user_name(),
					'modified_on' 	=> date("Y-m-d h:i:s"),
				];

				$updateExpense[] = [
					'id' 			=> $expense->id,
					'status' 		=> '3',
					'modified_by' 	=> $this->auth->user_name(),
					'modified_on' 	=> date("Y-m-d h:i:s"),
				];

				if ($expense->id_kasbon != null) {
					$Harga[]			= $expense->kurang_bayar;
				} else {
					if ($dtl->id_kasbon == '') {
						$Harga[] 		= ($dtl->harga * $dtl->qty);
					} else {
						$Harga[] 		= ($dtl->kasbon * -1);
					}
				}
			}

			if ($Data['tipe'] == 'kasbon') {
				$dtl 				= $this->db->get_where('tr_kasbon', ['id' => $detail['id']])->row();

				if ($dtl->kurang_bayar != null) {
					$nilai = $dtl->kurang_bayar;
				} else {
					$nilai = $dtl->jumlah_kasbon;
				}

				$ArrDetail[] 		= [
					'id' 			=> $id_detail,
					'payment_id' 	=> $Id,
					'no_doc' 		=> $dtl->no_doc,
					'tgl_doc' 		=> $dtl->tgl_doc,
					'deskripsi' 	=> $dtl->keperluan,
					'qty' 			=> '1',
					'harga' 		=> $nilai,
					'total' 		=> $nilai,
					'keterangan' 	=> $dtl->keperluan,
					'doc_file' 		=> $dtl->doc_file,
					'coa' 			=> $dtl->coa,
					'created_by' 	=> $this->auth->user_name(),
					'created_on' 	=> date("Y-m-d h:i:s"),
				];
				$updateDetail[] = [
					'id' 			=> $dtl->id,
					'status' 		=> '3',
					'modified_by' 	=> $this->auth->user_name(),
					'modified_on' 	=> date("Y-m-d h:i:s"),
				];
				$Harga[] 		= $nilai;
			}

			if ($Data['tipe'] == 'transportasi') {
				$dtl 				= $this->db->get_where('tr_transport', ['id' => $detail['id']])->row();
				$ArrDetail[] 		= [
					'id' 			=> $id_detail,
					'payment_id' 	=> $Id,
					'no_doc' 		=> $dtl->no_req,
					'tgl_doc' 		=> $dtl->tgl_doc,
					'deskripsi' 	=> $dtl->keperluan,
					'qty' 			=> '1',
					'harga' 		=> $dtl->jumlah_kasbon,
					'total' 		=> $dtl->jumlah_kasbon,
					'keterangan' 	=> $dtl->keperluan,
					'doc_file' 		=> $dtl->doc_file,
					'coa' 			=> null,
					'created_by' 	=> $this->auth->user_name(),
					'created_on' 	=> date("Y-m-d h:i:s"),
				];
				$updateDetail[] = [
					'id' 			=> $dtl->id,
					'status' 		=> '2',
					'modified_by' 	=> $this->auth->user_name(),
					'modified_on' 	=> date("Y-m-d h:i:s"),
				];
				$Harga[] 		= $dtl->jumlah_kasbon;
			}

			if ($Data['tipe'] == 'nonpo') {
				$dtl 				= $this->db->get_where('tr_non_po_detail', ['id' => $detail['id']])->row();

				$ArrDetail[] 		= [
					'id' 			=> $id_detail,
					'payment_id' 	=> $Id,
					'no_doc' 		=> $dtl->no_doc,
					'tgl_doc' 		=> $dtl->tgl_pr,
					'deskripsi' 	=> $dtl->deskripsi,
					'qty' 			=> '1',
					'harga' 		=> $dtl->nilai_satuan_request,
					'total' 		=> $dtl->total_request,
					'keterangan' 	=> $dtl->keterangan,
					// 'doc_file' 		=> $dtl->doc_file,
					'coa' 			=> null,
					'created_by' 	=> $this->auth->user_name(),
					'created_on' 	=> date("Y-m-d h:i:s"),
				];

				$updateDetail[] = [
					'id' 			=> $dtl->id,
					'status' 		=> '1',
					'modified_by' 	=> $this->auth->user_name(),
					'modified_on' 	=> date("Y-m-d h:i:s"),
				];
				$Harga[] 		= $dtl->total_request;
			}

			if ($Data['tipe'] == 'periodik') {
				$dtl 				= $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $detail['id']])->row();

				$ArrDetail[] 		= [
					'id' 			=> $id_detail,
					'payment_id' 	=> $Id,
					'no_doc' 		=> $dtl->no_doc,
					'tgl_doc' 		=> $dtl->tanggal,
					'deskripsi' 	=> $dtl->keterangan,
					'qty' 			=> '1',
					'harga' 		=> $dtl->nilai,
					'total' 		=> $dtl->nilai,
					'keterangan' 	=> $dtl->keterangan,
					'doc_file' 		=> $dtl->doc_file,
					'coa' 			=> $dtl->coa,
					'created_by' 	=> $this->auth->user_name(),
					'created_on' 	=> date("Y-m-d h:i:s"),
				];

				$updateDetail[] = [
					'id' 			=> $dtl->id,
					'status' 		=> '1',
					'modified_by' 	=> $this->auth->user_name(),
					'modified_on' 	=> date("Y-m-d h:i:s"),
				];
				$Harga[] 		= $dtl->nilai;
			}

			if ($Data['tipe'] == 'direct_payment') {
				$dtl = $this->db->get_where('tr_direct_payment', ['id' => $detail['id']])->row();
				$data_request_payment = $this->db->get_where('request_payment', ['no_doc' => $dtl->no_doc])->row();

				$nilai = $dtl->grand_total;

				$ArrDetail[] 		= [
					'id' 			=> $id_detail,
					'payment_id' 	=> $Id,
					'no_doc' 		=> $dtl->no_doc,
					'tgl_doc' 		=> $dtl->tgl_doc,
					'deskripsi' 	=> $dtl->deskripsi,
					'qty' 			=> '1',
					'harga' 		=> $nilai,
					'total' 		=> $nilai,
					'keterangan' 	=> $dtl->deskripsi,
					'doc_file' 		=> $data_request_payment->link_doc,
					'coa' 			=> '',
					'created_by' 	=> $this->auth->user_name(),
					'created_on' 	=> date("Y-m-d h:i:s"),
				];
				$updateDetail[] = [
					'id' 			=> $dtl->id,
					'sts' 		=> '3'
				];
				$Harga[] 		= $nilai;
			}

			if ($Data['tipe'] == 'petty_cash_hutang') {
				$dtl = $this->db->get_where('request_payment', ['no_doc' => $detail['no_doc'], 'tipe' => 'petty_cash_hutang'])->row();

				$nilai = $dtl->jumlah;

				$ArrDetail[] 		= [
					'id' 			=> $id_detail,
					'payment_id' 	=> $Id,
					'no_doc' 		=> $dtl->no_doc,
					'tgl_doc' 		=> $dtl->tgl_doc,
					'deskripsi' 	=> $dtl->keperluan,
					'qty' 			=> '1',
					'harga' 		=> $nilai,
					'total' 		=> $nilai,
					'keterangan' 	=> $dtl->keperluan,
					'doc_file' 		=> '',
					'coa' 			=> '',
					'created_by' 	=> $this->auth->user_name(),
					'created_on' 	=> date("Y-m-d h:i:s"),
				];
				$Harga[] 		= $nilai;
			}

			$id_detail++;
		}

		$header['jumlah'] 	= array_sum($Harga);
		$header['status'] 	= '1';

		$this->db->trans_rollback();
		$this->db->trans_begin();

		if (($header)) {
			$header['id'] = $Id;
			$header['approved_by'] = $this->auth->user_name();
			$header['approved_on'] = date("Y-m-d h:i:s");
			$exist_data = $this->db->get_where('payment_approve', ['id' => $Data['id'], 'tipe' => $Data['tipe']])->num_rows();

			if ($exist_data == '0') {
				$insert_payment_approve = $this->db->insert('payment_approve', $header);
				if (!$insert_payment_approve) {
					print_r($this->db->error()['message']);
					exit;
				}
				// print_r($this->db->last_query());
				// exit;
			}
		}

		/* Details */
		if ($ArrDetail) {

			// print_r($ArrDetail);
			// exit;

			if ($Data['tipe'] == 'expense') {

				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				// print_r($this->db->last_query());
				// exit;
				$this->db->update_batch('tr_expense', $updateExpense, 'id');
				$this->db->update_batch('tr_expense_detail', $updateDetail, 'id');

				// Update request_payment
				$no_doc = '';
				$get_no_doc = $this->db->select('no_doc')->get_where('tr_expense', ['id' => $Data['id']])->row_array();
				$no_doc = $get_no_doc['no_doc'];

				$countData 		= $this->db->get_where('tr_expense_detail', ['no_doc' => $Data['no_doc']])->num_rows();
				$actualPayment 	= $this->db->get_where('tr_expense_detail', ['no_doc' => $Data['no_doc'], 'status >=' => '1'])->num_rows();

				// $get_expense_detail = $this->db->get_where('tr_expense_detail', ['id' => $Data['id']])->row_array();

				// $data_request_payment = $this->db->select('id')->get_where('request_payment', ['no_doc' => $get_expense_detail['no_doc']])->row_array();

				// if ($countData > $actualPayment) {
				// 	$this->db->update('request_payment', ['status' => '1'], ['no_doc' => $get_expense_detail['no_doc']]);
				// } elseif (($countData == $actualPayment)) {

				// print_r($no_doc);
				// exit;
				$this->db->update('request_payment', ['status' => '2'], ['no_doc' => $no_doc]);
				// }

			}


			if ($Data['tipe'] == 'kasbon') {
				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				$this->db->update_batch('tr_kasbon', $updateDetail, 'id');

				// Update request_payment
				$countData 		= $this->db->get_where('tr_kasbon', ['id' => $Data['id']])->num_rows();
				$actualPayment 	= $this->db->get_where('tr_kasbon', ['id' => $Data['id'], 'status >=' => '3'])->num_rows();

				$get_kasbon = $this->db->get_where('tr_kasbon', ['id' => $Data['id']])->row_array();

				$data_request_payment = $this->db->select('id')->get_where('request_payment', ['no_doc' => $get_kasbon['no_doc'], 'status' => 0])->row_array();

				if ($countData > $actualPayment) {
					$this->db->update('request_payment', ['status' => '1'], ['id' => $data_request_payment['id']]);
				} elseif (($countData == $actualPayment)) {
					$this->db->update('request_payment', ['status' => '2'], ['id' => $data_request_payment['id']]);
				}
			}

			if ($Data['tipe'] == 'transportasi') {
				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				$this->db->update_batch('tr_transport', $updateDetail, 'id');

				// Update request_payment
				$countData 		= $this->db->get_where('tr_transport', ['id' => $Data['id']])->num_rows();
				$actualPayment 	= $this->db->get_where('tr_transport', ['id' => $Data['id'], 'status >=' => '2'])->num_rows();

				$get_transport = $this->db->get_where('tr_transport_req', ['id' => $Data['id']])->row_array();

				$data_request_payment = $this->db->select('id')->get_where('request_payment', ['no_doc' => $get_transport['no_doc']])->row_array();

				if ($countData > $actualPayment) {
					$this->db->update('request_payment', ['status' => '1'], ['id' => $data_request_payment['id']]);
				} elseif (($countData == $actualPayment)) {
					$this->db->update('request_payment', ['status' => '2'], ['id' => $data_request_payment['id']]);
				}
			}

			if ($Data['tipe'] == 'nonpo') {
				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				$this->db->update_batch('tr_non_po_detail', $updateDetail, 'id');

				// Update request_payment
				$countData 		= $this->db->get_where('tr_non_po_detail', ['id' => $Data['id']])->num_rows();
				$actualPayment 	= $this->db->get_where('tr_non_po_detail', ['id' => $Data['id'], 'status >=' => '1'])->num_rows();

				$get_nonpo = $this->db->get_where('tr_non_po_detail', ['id' => $Data['id']])->row_array();

				$data_request_payment = $this->db->select('id')->get_where('request_payment', ['no_doc' => $get_nonpo['no_doc']])->row_array();

				if ($countData > $actualPayment) {
					$this->db->update('request_payment', ['status' => '1'], ['id' => $data_request_payment['id']]);
				} elseif (($countData == $actualPayment)) {
					$this->db->update('request_payment', ['status' => '2'], ['id' => $data_request_payment['id']]);
				}
			}

			if ($Data['tipe'] == 'periodik') {
				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				$this->db->update_batch('tr_pengajuan_rutin_detail', $updateDetail, 'id');

				// Update request_payment
				$countData 		= $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $Data['id']])->num_rows();
				$actualPayment 	= $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $Data['id'], 'status >=' => '1'])->num_rows();

				$get_nonpo = $this->db->get_where('tr_pengajuan_rutin_detail', ['id' => $Data['id']])->row_array();

				$data_request_payment = $this->db->select('id')->get_where('request_payment', ['no_doc' => $get_nonpo['no_doc']])->row_array();

				// if ($countData > $actualPayment) {
				// 	$this->db->update('request_payment', ['status' => '1'], ['id' => $data_request_payment['id']]);
				// } elseif (($countData == $actualPayment)) {
				// 	$this->db->update('request_payment', ['status' => '2'], ['id' => $data_request_payment['id']]);
				// }
				$update_request_payment = $this->db->update('request_payment', ['status' => '2'], ['no_doc' => $get_nonpo['no_doc'], 'ids' => $get_nonpo['id']]);
				// if(!$update_request_payment){
				// 	print_r($this->db->error()['message']);
				// 	exit;
				// }
			}

			if ($Data['tipe'] == 'direct_payment') {
				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				$this->db->update_batch('tr_direct_payment', $updateDetail, 'id');

				// Update request_payment
				$get_kasbon = $this->db->get_where('tr_direct_payment', ['id' => $Data['id']])->row_array();

				$data_request_payment = $this->db->select('id')->get_where('request_payment', ['no_doc' => $get_kasbon['no_doc']])->row_array();

				$this->db->update('request_payment', ['status' => '2'], ['id' => $data_request_payment['id']]);
			}

			if ($Data['tipe'] == 'petty_cash_hutang') {
				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				$this->db->update('request_payment', ['status' => '2'], ['no_doc' => $Data['no_doc'], 'tipe' => 'petty_cash_hutang']);
			}
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
		} else {
			$this->db->trans_commit();
			$result = true;
		}
		$param = array(
			'save' => $result
		);

		echo json_encode($param);
	}

	public function save_approval_checker()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		foreach ($post['item'] as $item) :
			if (isset($item['id'])) {
				if ($post['tipe'] == "periodik") {
					$this->db->update('request_payment', [
						'app_checker' => 1,
						'app_checker_by' => $this->auth->user_id(),
						'app_checker_date' => date('Y-m-d H:i:s')
					], [
						'no_doc' => $post['no_doc'],
						'ids' => $item['id']
					]);

					$this->db->update('tr_pengajuan_rutin_detail', ['sts_reject' => 0, 'sts_reject_manage' => 0], ['no_doc' => $post['no_doc'], 'id' => $item['id']]);
				} else {
					$this->db->update('request_payment', [
						'app_checker' => 1,
						'app_checker_by' => $this->auth->user_id(),
						'app_checker_date' => date('Y-m-d H:i:s')
					], [
						'no_doc' => $post['no_doc'],
						'app_checker' => null
					]);

					if ($post['tipe'] == "transportasi") {
						$this->db->update('tr_transport_req', ['sts_reject' => 0, 'sts_reject_manage' => 0], ['no_doc' => $post['no_doc']]);
						$this->db->update('tr_transport', ['req_payment' => 1], ['id' => $item['id']]);
					}
					if ($post['tipe'] == "expense") {
						$this->db->update('tr_expense', ['sts_reject' => 0, 'sts_reject_manage' => 0], ['no_doc' => $post['no_doc']]);
						$this->db->update('tr_expense_detail', ['req_payment' => 1], ['id' => $item['id']]);
					}
					if ($post['tipe'] == "kasbon") {
						$this->db->update('tr_kasbon', ['sts_reject' => 0, 'sts_reject_manage' => 0], ['no_doc' => $post['no_doc']]);
					}
				}
			}
		endforeach;

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
		} else {
			$this->db->trans_commit();
			$result = true;
		}
		$param = array(
			'save' => $result
		);

		echo json_encode($param);
	}

	public function list_payment()
	{
		$data_coa = $this->All_model->GetCoaCombo(5, 'a.no_perkiraan LIKE "%1101-02%" AND (a.kode_bank IS NOT NULL AND a.kode_bank <> "")');
		$results = $this->Request_payment_model->GetListDataPayment('status IS NOT NULL');
		$this->template->set('data_coa', $data_coa);
		$this->template->set('results', $results);
		$this->template->title('Payment');
		$this->template->render('list_payment');
	}

	public function save_payment()
	{
		$bank_coa		= $this->input->post("bank_coa");
		$keterangan		= $this->input->post("keterangan");
		$bank_nilai		= $this->input->post("bank_nilai");
		$bank_admin		= $this->input->post("bank_admin");
		$status			= $this->input->post("status");
		$no_doc			= $this->input->post("no_doc");
		$keperluan		= $this->input->post("keperluan");
		$tipe			= $this->input->post("tipe");
		$nama			= $this->input->post("nama");
		$ids			= $this->input->post("ids");
		$this->db->trans_begin();
		$jenis_jurnal = 'BUK030';
		$payment_date = date("Y-m-d");
		$det_Jurnaltes1 = array();
		$ix = 0;
		$config['upload_path'] = './assets/expense/';
		$config['allowed_types'] = '*';
		$config['remove_spaces'] = TRUE;
		$config['encrypt_name'] = TRUE;



		if (!empty($status)) {
			foreach ($status as $keys => $val) {
				if ($bank_nilai[$keys] <> 0) {
					$filenames = "";
					if (!empty($_FILES['doc_file_' . $val]['name'])) {
						$_FILES['file']['name'] = $_FILES['doc_file_' . $val]['name'];
						$_FILES['file']['type'] = $_FILES['doc_file_' . $val]['type'];
						$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $val]['tmp_name'];
						$_FILES['file']['error'] = $_FILES['doc_file_' . $val]['error'];
						$_FILES['file']['size'] = $_FILES['doc_file_' . $val]['size'];
						$this->load->library('upload', $config);
						$this->upload->initialize($config);
						if ($this->upload->do_upload('file')) {
							$uploadData = $this->upload->data();
							$filenames = $uploadData['file_name'];
						}
					}

					$ix++;
					$nomor_jurnal = $jenis_jurnal . date("ymd") . rand(1000, 9999) . $ix;
					$data =  array(
						'keterangan' => $keterangan[$keys],
						'bank_nilai' => $bank_nilai[$keys],
						'bank_admin' => $bank_admin[$keys],
						'bank_coa' => $bank_coa,
						'doc_file' => $filenames,
						'status' => 2,
						'pay_by' => $this->auth->user_name(),
						'pay_on' => date("Y-m-d h:i:s")
					);

					$this->All_model->dataUpdate('payment_approve', $data, array('id' => $val));

					if ($tipe[$keys] == 'transportasi') {
						$coa = '';
						$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='" . $tipe[$keys] . "'")->row();
						if (!empty($rec)) {
							$coa = $rec->no_perkiraan;
						}
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $coa,
							'keterangan' => $keterangan[$keys],
							'no_request' => $no_doc[$keys],
							'debet' => $bank_nilai[$keys],
							'kredit' => 0,
							'no_reff' =>  $no_doc[$keys],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $nama[$keys]
						);
						if ($bank_admin[$keys] > 0) {
							$coa = '';
							$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='admin'")->row();
							if (!empty($rec)) {
								$coa = $rec->no_perkiraan;
							}
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => $keterangan[$keys],
								'no_request' => $no_doc[$keys],
								'debet' =>  $bank_admin[$keys],
								'kredit' => 0,
								'no_reff' =>  $no_doc[$keys],
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $nama[$keys]
							);
						}
					}
					if ($tipe[$keys] == 'kasbon') {
						$coa = '';
						$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='" . $tipe[$keys] . "'")->row();
						if (!empty($rec)) {
							$coa = $rec->no_perkiraan;
						}
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $coa,
							'keterangan' => $keterangan[$keys],
							'no_request' => $no_doc[$keys],
							'debet' => $bank_nilai[$keys],
							'kredit' => 0,
							'no_reff' =>  $no_doc[$keys],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $nama[$keys]
						);
						if ($bank_admin[$keys] > 0) {
							$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='admin'")->row();
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => $keterangan[$keys],
								'no_request' => $no_doc[$keys],
								'debet' =>  $bank_admin[$keys],
								'kredit' => 0,
								'no_reff' =>  $no_doc[$keys],
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $nama[$keys]
							);
						}
					}

					if ($tipe[$keys] == 'expense') {
						$rec = $this->db->query("select * from tr_expense_detail where no_doc='" . $no_doc[$keys] . "' and status = '1'")->result();
						// $rec = $this->db->get_where('payment_approve_details', ['payment_id' => $val])->result();
						$this->db->update('tr_expense_detail', ['status' => '2'], ['no_doc' => $no_doc[$keys], 'status' => '1']);
						foreach ($rec as $record) {
							$coa = $record->coa;
							if ($record->id_kasbon != '') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $record->coa,
									'keterangan' => $keterangan[$keys],
									'no_request' => $no_doc[$keys],
									'debet' => 0,
									'kredit' => $record->kasbon,
									'no_reff' =>  $no_doc[$keys],
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $nama[$keys]
								);
							} else {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $record->coa,
									'keterangan' => $keterangan[$keys],
									'no_request' => $no_doc[$keys],
									'debet' => $record->expense,
									'kredit' => 0,
									'no_reff' =>  $no_doc[$keys],
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $nama[$keys]
								);
							}
						}
						if ($bank_admin[$keys] > 0) {
							$coa = '';
							$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='admin'")->row();
							if (!empty($rec)) {
								$coa = $rec->no_perkiraan;
							}
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $coa,
								'keterangan' => $keterangan[$keys],
								'no_request' => $no_doc[$keys],
								'debet' =>  $bank_admin[$keys],
								'kredit' => 0,
								'no_reff' =>  $no_doc[$keys],
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $nama[$keys]
							);
						}
					}

					if ($tipe[$keys] == 'nonpo') {
						$coa = '';
						$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='" . $tipe[$keys] . "'")->row();
						if (!empty($rec)) {
							$coa = $rec->no_perkiraan;
						}
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $coa,
							'keterangan' => $keterangan[$keys],
							'no_request' => $no_doc[$keys],
							'debet' => $bank_nilai[$keys],
							'kredit' => 0,
							'no_reff' =>  $no_doc[$keys],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $nama[$keys]
						);
						if ($bank_admin[$keys] > 0) {
							$coa = '';
							$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='admin'")->row();
							if (!empty($rec)) {
								$coa = $rec->no_perkiraan;
							}
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $coa,
								'keterangan' => $keterangan[$keys],
								'no_request' => $no_doc[$keys],
								'debet' =>  $bank_admin[$keys],
								'kredit' => 0,
								'no_reff' =>  $no_doc[$keys],
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $nama[$keys]
							);
						}
					}

					if ($tipe[$keys] == 'periodik') {
						$coa = '';
						$rec = $this->db->query("select coa from tr_pengajuan_rutin_detail where id='" . $ids[$keys] . "' and no_doc='" . $no_doc[$keys] . "'")->row();
						if (!empty($rec)) {
							$coa = $rec->coa;
						}
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $rec->coa,
							'keterangan' => $keterangan[$keys],
							'no_request' => $no_doc[$keys],
							'debet' => $bank_nilai[$keys],
							'kredit' => 0,
							'no_reff' =>  $no_doc[$keys],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $nama[$keys]
						);
						if ($bank_admin[$keys] > 0) {
							$coa = '';
							$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='admin'")->row();
							if (!empty($rec)) {
								$coa = $rec->no_perkiraan;
							}
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $coa,
								'keterangan' => $keterangan[$keys],
								'no_request' => $no_doc[$keys],
								'debet' =>  $bank_admin[$keys],
								'kredit' => 0,
								'no_reff' =>  $no_doc[$keys],
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $nama[$keys]
							);
						}
					}


					//bank coa
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $keterangan[$keys],
						'no_request' => $no_doc[$keys],
						'debet' => ($bank_nilai[$keys] < 0 ? ($bank_nilai[$keys] * -1) : 0),
						'kredit' => ($bank_nilai[$keys] >= 0 ? $bank_nilai[$keys] : 0),
						'no_reff' =>  $no_doc[$keys],
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $nama[$keys]
					);
					if ($bank_admin[$keys] > 0) {
						$rec = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and menu='admin'")->row();
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $bank_coa,
							'keterangan' => $keterangan[$keys],
							'no_request' => $no_doc[$keys],
							'debet' => 0,
							'kredit' => $bank_admin[$keys],
							'no_reff' =>  $no_doc[$keys],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $nama[$keys]
						);
					}
				}
			}

			// print_r($det_Jurnaltes1);
			$this->db->insert_batch('jurnal', $det_Jurnaltes1);
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = 0;
		} else {
			$this->db->trans_commit();
			$result = 1;
		}
		$param = array(
			'hasil' => $result
		);
		echo json_encode($param);
	}

	public function payment_jurnal_list()
	{
		$results = $this->Request_payment_model->GetListDataJurnal();
		$this->template->set('results', $results);
		$this->template->title('Payment Jurnal');
		$this->template->render('list_jurnal');
	}
	public function view_jurnal($id)
	{
		$data = $this->db->query("select * from jurnal where nomor='" . $id . "' order by kredit,debet,no_perkiraan")->result();
		$data_coa = $this->All_model->GetCoaCombo();
		$results = $this->Request_payment_model->GetListDataPayment('status=1');
		$this->template->set('data', $data);
		$this->template->set('datacoa', $data_coa);
		$this->template->set('results', $results);
		$this->template->set('status', 'view');
		$this->template->title('Payment Jurnal');
		$this->template->render('form_jurnal');
	}
	public function edit_jurnal($id)
	{
		$data = $this->db->query("select * from jurnal where nomor='" . $id . "' order by kredit,debet,no_perkiraan")->result();
		$data_coa = $this->All_model->GetCoaCombo();
		$results = $this->Request_payment_model->GetListDataPayment('status=1');
		$this->template->set('data', $data);
		$this->template->set('datacoa', $data_coa);
		$this->template->set('status', 'edit');
		$this->template->title('Payment Jurnal');
		$this->template->render('form_jurnal');
	}
	public function jurnal_save()
	{
		$id = $this->input->post("id");
		$no_perkiraan = $this->input->post("no_perkiraan");
		$keterangan = $this->input->post("keterangan");
		$debet = $this->input->post("debet");
		$kredit = $this->input->post("kredit");

		$tanggal		= $this->input->post('tanggal');
		$tipe			= $this->input->post('tipe');
		$no_reff        = $this->input->post('no_reff');
		$no_request		= $this->input->post('no_request');
		$jenis_jurnal	= $this->input->post('jenis_jurnal');
		$nocust         = $this->input->post('nocust');
		$total			= 0;
		$total_po		= $this->input->post('total_po');
		$Bln 			= substr($tanggal, 5, 2);
		$Thn 			= substr($tanggal, 0, 4);
		$Nomor_JV = $this->Jurnal_model->get_no_buk('101');
		$session = $this->session->userdata('app_session');
		$data_session	= $this->session->userdata;

		$this->db->trans_begin();
		for ($i = 0; $i < count($id); $i++) {
			$dataheader =  array(
				'stspos' => "1",
				'no_perkiraan' => $no_perkiraan[$i],
				'keterangan' => $keterangan[$i],
				'debet' => $debet[$i],
				'kredit' => $kredit[$i]
			);
			$total = ($total + $debet[$i]);
			$this->All_model->DataUpdate('jurnal', $dataheader, array('id' => $id[$i]));

			$datadetail = array(
				'tipe'        	=> $tipe,
				'nomor'       	=> $Nomor_JV,
				'tanggal'     	=> $tanggal,
				'no_reff'     	=> $no_reff,
				'no_perkiraan'	=> $no_perkiraan[$i],
				'keterangan' 	=> $keterangan[$i],
				'debet' 		=> $debet[$i],
				'kredit' 		=> $kredit[$i]
			);
			$this->db->insert(DBACC . '.jurnal', $datadetail);
		}

		$keterangan	= 'Payment';
		$dataJVhead = array(
			'nomor' 	    	=> $Nomor_JV,
			'tgl'	         	=> $tanggal,
			'jml'	            => $total,
			'kdcab'				=> '101',
			'jenis_reff'	    => 'BUK',
			'no_reff' 		    => $no_reff,
			'jenis_ap'			=> 'V',
			'note'				=> $keterangan,
			'user_id'			=> $this->auth->user_name(),
			'ho_valid'			=> '',
			'batal'			    => '0'
		);
		$this->db->insert(DBACC . '.japh', $dataJVhead);
		$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
		$this->db->query($Qry_Update_Cabang_acc);

		$this->db->trans_complete();
		if ($this->db->trans_status()) {
			$this->db->trans_commit();
			$result         = TRUE;
		} else {
			$this->db->trans_rollback();
			$result = FALSE;
		}
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	public function list_return()
	{
		// $controller			= 'request_payment/index';
		// $Arr_Akses			= getAcccesmenu($controller);
		// if ($Arr_Akses['read'] != '1') {
		// 	$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
		// 	redirect(site_url('dashboard'));
		// }
		$get_Data			= $this->db->query("SELECT a.id as ids,a.no_doc,a.created_by,c.nm_lengkap as nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, bank_id, accnumber, accname FROM tr_expense a left join " . DBACC . ".coa_master as b on a.coa=b.no_perkiraan
		left join users c on a.nama=c.nm_lengkap WHERE a.status=1 and a.jumlah <> 0 AND a.exp_pib IS NULL AND a.exp_inv_po IS NULL AND (a.tipe_penggantian = '2' OR a.tipe_penggantian IS NULL) AND (a.tipe_pengembalian = '2' OR a.tipe_pengembalian IS NULL)")->result();
		// $menu_akses			= $this->master_model->getMenu();
		$data = array(
			'title'			=> 'Pengembalian Expense',
			// 'action'		=> 'index',
			'row'			=> $get_Data
			// 'data_menu'		=> $menu_akses
			// 'akses_menu'	=> $Arr_Akses
		);
		// history('View Pengembalian Expense');
		// $this->load->view('Request_payment/list_return', $data);

		$this->template->set($data);
		$this->template->title('Pengembalian Expense');
		$this->template->render('list_return');
	}

	public function list_return_approval()
	{
		$data_pengembalian_expense = $this->db->query('SELECT * FROM tr_pengembalian_expense WHERE status IS null OR status = 2')->result();

		$this->template->set('data_pengembalian', $data_pengembalian_expense);
		$this->template->title('Approval Pengembalian Expense');
		$this->template->render('list_return_approval');
	}

	public function reject_approval()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$get_req_payment = $this->db->get_where('request_payment', ['no_doc' => $post['no_doc']])->row_array();
		if ($post['tingkat_approval'] == '1') {
			if ($get_req_payment['tipe'] == 'transportasi') {
				$this->db->update('tr_transport_req', ['status' => 1, 'sts_reject' => 1, 'sts_reject_manage' => 0, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);

				$this->db->update('tr_transport', ['req_payment' => 0], ['no_req' => $post['no_doc']]);
			}
			if ($get_req_payment['tipe'] == 'kasbon') {
				$this->db->update('tr_kasbon', ['status' => 1, 'sts_reject' => 1, 'sts_reject_manage' => 0, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);
			}
			if ($get_req_payment['tipe'] == 'expense') {
				$this->db->update('tr_expense', ['status' => 1, 'sts_reject' => 1, 'sts_reject_manage' => 0, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);

				$this->db->update('tr_expense_detail', ['req_payment' => 0], ['no_doc' => $post['no_doc']]);
			}
			if ($get_req_payment['tipe'] == 'periodik') {
				foreach ($post['item'] as $item) {
					$this->db->update('tr_pengajuan_rutin_detail', ['id_payment' => null, 'sts_reject' => 1, 'sts_reject_manage' => 0, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc'], 'id' => $item['id']]);
				}
			}
		} else {
			if ($get_req_payment['tipe'] == 'transportasi') {
				$this->db->update('tr_transport_req', ['status' => 1, 'sts_reject_manage' => 1, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);

				$this->db->update('tr_transport', ['req_payment' => 0], ['no_req' => $post['no_doc']]);
			}
			if ($get_req_payment['tipe'] == 'kasbon') {
				$this->db->update('tr_kasbon', ['status' => 1, 'sts_reject_manage' => 1, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);
			}
			if ($get_req_payment['tipe'] == 'expense') {
				$this->db->update('tr_expense', ['status' => 1, 'sts_reject_manage' => 1, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);

				$this->db->update('tr_expense_detail', ['req_payment' => 0], ['no_doc' => $post['no_doc']]);
			}
			if ($get_req_payment['tipe'] == 'periodik') {
				foreach ($post['item'] as $item) {
					$this->db->update('tr_pengajuan_rutin_detail', ['id_payment' => null, 'sts_reject' => 1, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc'], 'id' => $item['id']]);
				}
			}
		}

		// $this->db->update('request_payment', ['status' => '9'], ['no_doc' => $post['no_doc']]);
		if ($post['tipe'] == "periodik") {
			foreach ($post['item'] as $item) {
				$this->db->delete('request_payment', ['no_doc' => $post['no_doc'], 'ids' => $item['id']]);
			}
		} else {
			$this->db->delete('request_payment', ['no_doc' => $post['no_doc']]);
		}

		if ($this->db->trans_status() == FALSE) {
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode([
			'save' => $valid
		]);
	}

	public function approve_pengembalian_expense()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();

		$this->db->update('tr_pengembalian_expense', ['status' => 1, 'app_by' => $this->auth->user_id(), 'app_date' => date('Y-m-d H:i:s')], ['id' => $id]);

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

	public function reject_pengembalian_expense()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();

		$this->db->update('tr_pengembalian_expense', ['status' => 2, 'reject_by' => $this->auth->user_id(), 'reject_date' => date('Y-m-d H:i:s')], ['id' => $id]);

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

	public function search_payment_list()
	{
		$tgl_from = $this->input->post('tgl_from');
		$tgl_to = $this->input->post('tgl_to');
		$bank = $this->input->post('bank');

		$this->Request_payment_model->search_payment_list($tgl_from, $tgl_to, $bank);
	}

	public function search_req_payment()
	{
		$ENABLE_ADD     = has_permission('Request_Payment.Add');
		$ENABLE_MANAGE  = has_permission('Request_Payment.Manage');
		$ENABLE_DELETE  = has_permission('Request_Payment.Delete');
		$ENABLE_VIEW    = has_permission('Request_Payment.View');

		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');
		$vendor = $this->input->post('vendor');
		$actived_tab = $this->input->post('actived_tab');

		$nm_vendor = '';
		if ($vendor !== '') {
			$get_nm_vendor = $this->db->get_where('new_supplier', ['kode_supplier' => $vendor])->row();
			$nm_vendor = $get_nm_vendor->nama;
		}

		$data = $this->Request_payment_model->GetListDataRequest($actived_tab, $from_date, $to_date);

		$list_curr = $this->db->get('mata_uang')->result();

		$this->db->select('a.*');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->where('a.no_perkiraan LIKE', '%1101%');
		$list_coa = $this->db->get()->result();

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from('tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$hasil = '';

		$numb = 1;
		foreach ($data as $record) {

			$sts = '<div class="badge bg-blue">Open</div>';
			if ($record->sts_reject == '1') {
				$sts = '<div class="badge bg-red">Rejected by Checker</div>';
			}
			if ($record->sts_reject_manage == '1') {
				$sts = '<div class="badge bg-red">Rejected by Management</div>';
			}

			$reject_reason = '';
			if ($record->sts_reject == '1' || $record->sts_reject_manage == '1') {
				$reject_reason = $record->reject_reason;
			}

			$no_invoice = (isset($list_no_invoice[$record->no_doc])) ? $list_no_invoice[$record->no_doc] : '';

			$tipe = $record->tipe;

			$currency = '';
			if ($record->tipe == 'expense') {
				$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $record->no_doc])->row_array();
				if ($get_expense['exp_inv_po'] == '1') {
					$tipe = 'Pembayaran PO';

					$get_inv = $this->db->get_where('tr_invoice_po', ['id' => $record->no_doc])->row_array();
					$currency = $get_inv['curr'];
				}
			}

			$nm_supplier = '';

			$get_ros = $this->db->select('a.nm_supplier')->get_where('tr_ros a', ['a.id' => $record->no_doc])->row();
			if (!empty($get_ros)) {
				$nm_supplier = $get_ros->nm_supplier;
			}

			$get_invoice = $this->db->select('a.no_po')
				->from('tr_invoice_po a')
				->where('a.id', $record->no_doc)
				->get()
				->row();
			if ($nm_supplier == '' && !empty($get_invoice)) {
				$nm_supplier = [];
				$no_po = str_replace(', ', ',', $get_invoice->no_po);

				if (strpos($no_po, 'TR') !== false) {
					$get_supplier = $this->db->query("
						SELECT
							c.nama as nm_supplier
						FROM
							tr_incoming_check a 
							LEFT JOIN tr_purchase_order b ON b.no_po = a.no_ipp
							LEFT JOIN new_supplier c ON c.kode_supplier = b.id_suplier
						WHERE
							a.kode_trans IN ('" . str_replace(",", "','", $no_po) . "')
						GROUP BY c.nama
						
						UNION ALL

						SELECT
							c.nama as nm_supplier
						FROM
							warehouse_adjustment a
							LEFT JOIN tr_purchase_order b ON b.no_po = a.no_ipp
							LEFT JOIN new_supplier c ON c.kode_supplier = b.id_suplier
						WHERE
							a.kode_trans IN ('" . str_replace(",", "','", $no_po) . "')
						GROUP BY c.nama
					")->result();
					foreach ($get_supplier as $item_supplier) {
						$nm_supplier[] = $item_supplier->nm_supplier;
					}
				} else {
					$get_supplier = $this->db->query("
						SELECT
							b.nama as nm_supplier
						FROM
							tr_purchase_order a
							LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier
						WHERE
							a.no_surat IN ('" . str_replace(",", "','", $no_po) . "')
						GROUP BY b.nama
					")->result();
					foreach ($get_supplier as $item_supplier) {
						$nm_supplier[] = $item_supplier->nm_supplier;
					}
				}
				$nm_supplier = implode(',', $nm_supplier);
			}

			if ($actived_tab == 'pembayaran_po') {
				if ($tipe == 'Pembayaran PO') {
					$valid = 1;
				} else {
					$valid = 0;
				}
			} else if ($actived_tab == 'expense') {
				if (strpos($record->no_doc, 'ER-') !== false) {
					$valid = 1;
				} else {
					$valid = 0;
				}
			} else {
				$valid = 1;
			}

			if ($vendor !== '') {
				if ($nm_supplier !== $nm_vendor) {
					$valid = 0;
				} else {
					$valid = 1;
				}
			}

			if ($valid == 1) {
				$hasil .= '<tr>';
				$hasil .= '<td class="exclass">';
				if ($ENABLE_MANAGE) {
					$hasil .= '<input type="hidden" name="no_doc_' . $numb . '" id="no_doc_' . $numb . '" value="' . $record->no_doc . '">';
					$hasil .= '<input type="hidden" name="nama_' . $numb . '" id="nama_' . $numb . '" value="' . $record->nama . '">';
					$hasil .= '<input type="hidden" name="tgl_doc_' . $numb . '" id="tgl_doc_' . $numb . '" value="' . $record->tgl_doc . '">';
					$hasil .= '<input type="hidden" name="keperluan_' . $numb . '" id="keperluan_' . $numb . '" value="' . $record->keperluan . '">';
					$hasil .= '<input type="hidden" name="tipe_' . $numb . '" id="tipe_' . $numb . '" value="' . $record->tipe . '">';
					$hasil .= '<input type="hidden" name="jumlah_' . $numb . '" id="jumlah_' . $numb . '" value="' . $record->jumlah . '">';
					$hasil .= '<input type="hidden" name="bank_id_' . $numb . '" id="bank_id_' . $numb . '" value="' . $record->bank_id . '">';
					$hasil .= '<input type="hidden" name="accnumber_' . $numb . '" id="accnumber_' . $numb . '" value="' . $record->accnumber . '">';
					$hasil .= '<input type="hidden" name="accname_' . $numb . '" id="accname_' . $numb . '" value="' . $record->accname . '">';
					$hasil .= '<input type="hidden" name="ids_' . $numb . '" id="ids_' . $numb . '" value="' . $record->ids . '">';
					$hasil .= '<input type="checkbox" name="status[]" id="status_' . $numb . '" value="' . $numb . '" class="dtlloop" onclick="cektotal()">';
				}
				if ($record->tipe == 'kasbon') {
					$hasil .= '<a href="' . base_url("expense/kasbon_view/" . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'transportasi') {
					$hasil .= '<a href="' . base_url('expense/transport_req_view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'expense') {
					$get_expense = $this->db->get_where('tr_expense', ['id' => $record->ids])->row_array();
					if ($get_expense['exp_pib'] == '1') {
						$hasil .= '<a href="' . base_url('ros/view/' . $record->no_doc) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
					} else if ($get_expense['exp_inv_po'] == '1') {
						$hasil .= '';
					} else {
						$hasil .= '<a href="' . base_url('expense/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
					}
				}
				if ($record->tipe == 'nonpo') {
					$hasil .= '<a href="' . base_url('purchase_order/non_po/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'periodiks') {
					$hasil .= '<a href="' . base_url('pembayaran_rutin/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				$hasil .= '</td>';
				$hasil .= '<td class="">' . $numb . '</td>';
				if ($actived_tab == 'pembayaran_po') {
					$hasil .= '<td>' . $no_invoice . '</td>';
				} else {
					$hasil .= '<td>' . $record->no_doc . '</td>';
				}
				$hasil .= '<td>' . $nm_supplier . '</td>';
				$hasil .= '<td>' . $record->tgl_doc . '</td>';
				$hasil .= '<td>' . $record->keperluan . '</td>';
				$hasil .= '<td>';
				$hasil .= '<select name="currency_' . $numb . '" id="" class="form-control form-control-sm select2">';
				$hasil .= '<option value="">- Currency -</option>';
				foreach ($list_curr as $item_curr) {
					$hasil .= '<option value="' . $item_curr->kode . '">' . $item_curr->kode . '</option>';
				}
				$hasil .= '</select>';
				$hasil .= '</td>';
				$hasil .= '<td>' . number_format($record->jumlah) . '</td>';
				$hasil .= '<td>' . $sts . '</td>';
				$hasil .= '<td>';
				$hasil .= '
				<table class="w-100" border="0" style="border: 0 !important;">
					<tr>
						<td>Nilai Pengajuan</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="" id="" class="form-control form-control-sm text-right nilai_pengajuan_' . $numb . '" value="' . number_format($record->jumlah) . '" readonly>
						</td>
					</tr>
					<tr>
						<td>
							<select name="tipe_pph_' . $numb . '" id="" class="form-control form-control-sm select_pph_' . $numb . '">
								<option value="">- Select PPh -</option>
								<option value="1">PPh 21</option>
							</select>
						</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="nilai_pph_' . $numb . '" id="" class="form-control form-control-sm text-right divide nilai_pph_' . $numb . '">
						</td>
					</tr>
					<tr>
						<td>Admin Charge</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="admin_charge_' . $numb . '" id="" class="form-control form-control-sm text-right admin_charge_' . $numb . ' divide" onchange="hitung_net_payment(' . $numb . ')">
						</td>
					</tr>
					<tr>
						<td>Net Payment</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="" id="" class="form-control form-control-sm text-right net_payment_' . $numb . '" onchange="hitung_net_payment(' . $numb . ')" readonly>
						</td>
					</tr>
				
					<tr>
						<td>Bank Pengirim</td>
						<td>:</td>
						<td>
							<select name="bank_' . $numb . '" id="" class="form-control form-control-sm select2">
								<option value="">- Bank -</option>
							';

				foreach ($list_coa as $item_coa) {
					$hasil .= '<option value="' . $item_coa->no_perkiraan . ' - ' . $item_coa->nama . '">' . $item_coa->no_perkiraan . ' - ' . $item_coa->nama . '</option>';
				}

				$hasil .= '
							</select>
						</td>
					</tr>
					<tr>
						<td>Tanggal Rencana Pembayaran</td>
						<td>:</td>
						<td>
							<input type="text" class="form-control tanggal" id="tanggal_' . $numb . '" name="tanggal_' . $numb . '" value="" placeholder="Tanggal">
						</td>
					</tr>
					<tr>
						<td>Upload Dokumen</td>
						<td>:</td>
						<td>
							<input type="file" name="upload_doc_' . $numb . '" id="" class="form-control form-control-sm">
						</td>
					</tr>
				</table>
				';
				$hasil .= '</td>';
				$hasil .= '</tr>';

				$numb++;
			}
		}

		echo json_encode([
			'hasil' => $hasil
		]);
	}

	public function change_tab()
	{
		$ENABLE_ADD     = has_permission('Request_Payment.Add');
		$ENABLE_MANAGE  = has_permission('Request_Payment.Manage');
		$ENABLE_DELETE  = has_permission('Request_Payment.Delete');
		$ENABLE_VIEW    = has_permission('Request_Payment.View');

		$tab = $this->input->post('tab');
		$data = $this->Request_payment_model->GetListDataRequest($tab);

		$list_curr = $this->db->get_where('mata_uang', ['deleted' => null])->result();
		$list_coa = $this->db->get_where(DBACC . '.coa_master', ['kode_bank <>' => null])->result();

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from('tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$numb = 1;

		$hasil = '';
		foreach ($data as $record) {

			$sts = '<div class="badge bg-blue">Open</div>';
			if ($record->sts_reject == '1') {
				$sts = '<div class="badge bg-red">Rejected by Checker</div>';
			}
			if ($record->sts_reject_manage == '1') {
				$sts = '<div class="badge bg-red">Rejected by Management</div>';
			}

			$reject_reason = '';
			if ($record->sts_reject == '1' || $record->sts_reject_manage == '1') {
				$reject_reason = $record->reject_reason;
			}

			$no_invoice = (isset($list_no_invoice[$record->no_doc])) ? $list_no_invoice[$record->no_doc] : '';

			$tipe = $record->tipe;

			$currency = '';
			if ($record->tipe == 'expense') {
				$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $record->no_doc])->row_array();
				if ($get_expense['exp_inv_po'] == '1') {
					$tipe = 'Pembayaran PO';

					$get_inv = $this->db->get_where('tr_invoice_po', ['id' => $record->no_doc])->row_array();
					$currency = $get_inv['curr'];
				}
			}

			$nm_supplier = '';

			$get_ros = $this->db->select('a.nm_supplier')->get_where('tr_ros a', ['a.id' => $record->no_doc])->row();
			if (!empty($get_ros)) {
				$nm_supplier = $get_ros->nm_supplier;
			}

			$get_invoice = $this->db->select('a.no_po')
				->from('tr_invoice_po a')
				->where('a.id', $record->no_doc)
				->get()
				->row();
			if ($nm_supplier == '' && !empty($get_invoice)) {
				$nm_supplier = [];
				$no_po = str_replace(', ', ',', $get_invoice->no_po);

				if (strpos($no_po, 'TR') !== false) {
					$get_supplier = $this->db->query("
						SELECT
							c.nama as nm_supplier
						FROM
							tr_incoming_check a 
							LEFT JOIN tr_purchase_order b ON b.no_po = a.no_ipp
							LEFT JOIN new_supplier c ON c.kode_supplier = b.id_suplier
						WHERE
							a.kode_trans IN ('" . str_replace(",", "','", $no_po) . "')
						GROUP BY c.nama
						
						UNION ALL

						SELECT
							c.nama as nm_supplier
						FROM
							warehouse_adjustment a
							LEFT JOIN tr_purchase_order b ON b.no_po = a.no_ipp
							LEFT JOIN new_supplier c ON c.kode_supplier = b.id_suplier
						WHERE
							a.kode_trans IN ('" . str_replace(",", "','", $no_po) . "')
						GROUP BY c.nama
					")->result();
					foreach ($get_supplier as $item_supplier) {
						$nm_supplier[] = $item_supplier->nm_supplier;
					}
				} else {
					$get_supplier = $this->db->query("
						SELECT
							b.nama as nm_supplier
						FROM
							tr_purchase_order a
							LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier
						WHERE
							a.no_surat IN ('" . str_replace(",", "','", $no_po) . "')
						GROUP BY b.nama
					")->result();
					foreach ($get_supplier as $item_supplier) {
						$nm_supplier[] = $item_supplier->nm_supplier;
					}
				}
				$nm_supplier = implode(',', $nm_supplier);
			}

			if ($tab == 'pembayaran_po') {
				if ($tipe == 'Pembayaran PO') {
					$valid = 1;
				} else {
					$valid = 0;
				}
			} else if ($tab == 'expense') {
				if (strpos($record->no_doc, 'ER-') !== false || strpos($record->no_doc, 'ROS') !== false) {
					$valid = 1;
				} else {
					$valid = 0;
				}
			} else {
				$valid = 1;
			}

			if ($valid == 1) {
				$hasil .= '<tr>';
				$hasil .= '<td class="exclass">';
				if ($ENABLE_MANAGE) {
					$hasil .= '<input type="hidden" name="no_doc_' . $numb . '" id="no_doc_' . $numb . '" value="' . $record->no_doc . '">';
					$hasil .= '<input type="hidden" name="nama_' . $numb . '" id="nama_' . $numb . '" value="' . $record->nama . '">';
					$hasil .= '<input type="hidden" name="tgl_doc_' . $numb . '" id="tgl_doc_' . $numb . '" value="' . $record->tgl_doc . '">';
					$hasil .= '<input type="hidden" name="keperluan_' . $numb . '" id="keperluan_' . $numb . '" value="' . $record->keperluan . '">';
					$hasil .= '<input type="hidden" name="tipe_' . $numb . '" id="tipe_' . $numb . '" value="' . $record->tipe . '">';
					$hasil .= '<input type="hidden" name="jumlah_' . $numb . '" id="jumlah_' . $numb . '" value="' . (($record->tipe == 'expense' and $record->id_kasbon != null and $record->kurang_bayar > 0) ? $record->kurang_bayar : $record->jumlah) . '">';
					$hasil .= '<input type="hidden" name="bank_id_' . $numb . '" id="bank_id_' . $numb . '" value="' . $record->bank_id . '">';
					$hasil .= '<input type="hidden" name="accnumber_' . $numb . '" id="accnumber_' . $numb . '" value="' . $record->accnumber . '">';
					$hasil .= '<input type="hidden" name="accname_' . $numb . '" id="accname_' . $numb . '" value="' . $record->accname . '">';
					$hasil .= '<input type="hidden" name="ids_' . $numb . '" id="ids_' . $numb . '" value="' . $record->ids . '">';
					$hasil .= '<input type="checkbox" name="status[]" id="status_' . $numb . '" value="' . $numb . '" class="dtlloop" onclick="cektotal()">';
				}
				if ($record->tipe == 'kasbon') {
					$hasil .= '<a href="' . base_url("expense/kasbon_view/" . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'transportasi') {
					$hasil .= '<a href="' . base_url('expense/transport_req_view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'expense') {
					$get_expense = $this->db->get_where('tr_expense', ['id' => $record->ids])->row_array();
					if ($get_expense['exp_pib'] == '1') {
						$hasil .= '<a href="' . base_url('ros/view/' . $record->no_doc) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
					} else if ($get_expense['exp_inv_po'] == '1') {
						$hasil .= '';
					} else {
						$hasil .= '<a href="' . base_url('expense/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
					}
				}
				if ($record->tipe == 'nonpo') {
					$hasil .= '<a href="' . base_url('purchase_order/non_po/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'periodiks') {
					$hasil .= '<a href="' . base_url('pembayaran_rutin/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}

				$curr = '';
				$get_curr = $this->db->get_where('tr_invoice_po', ['id' => $record->no_doc])->row();
				if (!empty($get_curr)) {
					$curr = $get_curr->curr;
				}

				$hasil .= '</td>';
				$hasil .= '<td class="">' . $numb . '</td>';
				if ($tab == 'pembayaran_po') {
					$hasil .= '<td>' . $no_invoice . '</td>';
				} else {
					$hasil .= '<td>' . $record->no_doc . '</td>';
				}
				$hasil .= '<td>' . $nm_supplier . '</td>';
				$hasil .= '<td>' . $record->tgl_doc . '</td>';
				$hasil .= '<td>' . $record->keperluan . '</td>';
				$hasil .= '<td>';
				$hasil .= '<select name="currency_' . $numb . '" id="" class="form-control form-control-sm select2">';
				$hasil .= '<option value="IDR"> IDR </option>';
				foreach ($list_curr as $item_curr) {
					$selected = '';
					if ($item_curr->kode == $curr) {
						$selected = 'selected';
					}
					$hasil .= '<option value="' . $item_curr->kode . '" ' . $selected . '>' . $item_curr->kode . '</option>';
				}
				$hasil .= '</select>';
				$hasil .= '</td>';
				$hasil .= '<td>' . (($record->tipe == 'expense' and $record->kurang_bayar > 0) ? number_format($record->kurang_bayar) : number_format($record->jumlah))  . '</td>';
				$hasil .= '<td>' . $sts . '</td>';
				$hasil .= '<td>';
				$hasil .= '
				<table class="w-100" border="0" style="border: 0px !important;">
					<tr>
						<td>Nilai Pengajuan</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="" id="" class="form-control form-control-sm text-right nilai_pengajuan_' . $numb . '" value="' . (($record->tipe == 'expense' and $record->kurang_bayar > 0) ? number_format($record->kurang_bayar) : number_format($record->jumlah)) . '" readonly>
						</td>
					</tr>
					<tr>
						<td>
							<select name="tipe_pph_' . $numb . '" id="" class="form-control form-control-sm select_pph_' . $numb . '">
								<option value="">- Select PPh -</option>
								<option value="1">PPh 21</option>
							</select>
						</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="nilai_pph_' . $numb . '" id="" class="form-control form-control-sm text-right divide nilai_pph_' . $numb . '">
						</td>
					</tr>
					<tr>
						<td>Admin Charge</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="admin_charge_' . $numb . '" id="" class="form-control form-control-sm text-right admin_charge_' . $numb . ' divide" onchange="hitung_net_payment(' . $numb . ')">
						</td>
					</tr>
					<tr>
						<td>Net Payment</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="" id="" class="form-control form-control-sm text-right net_payment_' . $numb . '" onchange="hitung_net_payment(' . $numb . ')" readonly>
						</td>
					</tr>
				
					<tr>
						<td>Bank Pengirim</td>
						<td>:</td>
						<td>
							<select name="bank_' . $numb . '" id="" class="form-control form-control-sm select2">
								<option value="">- Bank -</option>
							';

				foreach ($list_coa as $item_coa) {
					$hasil .= '<option value="' . $item_coa->no_perkiraan . ' - ' . $item_coa->nama . '">' . $item_coa->no_perkiraan . ' - ' . $item_coa->nama . '</option>';
				}

				$hasil .= '
							</select>
						</td>
					</tr>
					<tr>
						<td>Tanggal Rencana Pembayaran</td>
						<td>:</td>
						<td>
							<input type="text" class="form-control tanggal" id="tanggal_' . $numb . '" name="tanggal_' . $numb . '" value="" placeholder="Tanggal">
						</td>
					</tr>
					<tr>
						<td>Upload Dokumen</td>
						<td>:</td>
						<td>
							<input type="file" name="upload_doc_' . $numb . '" id="" class="form-control form-control-sm">
						</td>
					</tr>
				</table>
				';
				$hasil .= '</td>';
				$hasil .= '</tr>';

				$numb++;
			}
		}

		echo $hasil;
	}

	public function excel_payment_list()
	{
		$tgl_from = $this->uri->segment(3);
		$tgl_to = $this->uri->segment(4);
		$bank = $this->uri->segment(5);

		$this->Request_payment_model->excel_payment_list($tgl_from, $tgl_to, $bank);
	}

	public function view_receive_invoice()
	{
		$id_invoice = $this->input->post('id_invoice');

		$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $id_invoice])->row_array();
		if ($get_invoice['id_top'] !== null) {
			$get_top_invoice = $this->db->get_where('tr_top_po', ['id' => $get_invoice['id_top']])->row_array();

			$this->template->set('data_invoice', $get_invoice);
			$this->template->set('nilai_ppn', $get_invoice['nilai_ppn']);
			$this->template->set('nilai_disc', $get_invoice['nilai_disc']);
			$this->template->set('nilai_top', $get_top_invoice['nilai']);
			if ($get_top_invoice['group_top'] == '76') {
				$this->template->render('view');
			}
			if ($get_top_invoice['group_top'] == '77') {
				$this->template->render('view_pro');
			}
			if ($get_top_invoice['group_top'] == '78') {
				$this->template->render('view_ret');
			}
		} else {
			$id_po = str_replace(', ', ',', $get_invoice['no_po']);
			$no_incoming = explode(',', $id_po);

			$this->template->set('data_invoice', $get_invoice);
			$this->template->set('no_incoming', $no_incoming);
			$this->template->render('view_inc');
		}
	}

	public function save_approval_checker_consultant()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$this->db->update('request_payment', [
			'app_checker' => 1,
			'app_checker_by' => $this->auth->user_id(),
			'app_checker_date' => date('Y-m-d H:i:s')
		], [
			'no_doc' => $post['id'],
			'app_checker' => null
		]);

		$check_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $post['id']))->num_rows();

		if ($check_kasbon > 0) {
			$tipe = 'kasbon';
		} else {
			$tipe = 'expense';
		}

		if ($tipe == "expense") {
			$this->db->update('tr_expense', ['sts_reject' => 0, 'sts_reject_manage' => 0], ['no_doc' => $post['id']]);
			$this->db->update('tr_expense_detail', ['req_payment' => 1], ['id' => $post['id']]);

			$this->db->update('request_payment', [
				'app_checker' => 1,
				'app_checker_by' => $this->auth->user_id(),
				'app_checker_date' => date('Y-m-d H:i:s')
			], [
				'no_doc' => $post['id_expense']
			]);
		}
		if ($tipe == "kasbon") {
			$get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $post['id_kasbon']))->row();

			$this->db->update(DBCNL . '.kons_tr_kasbon_project_header a', array('sts_reject' => null, 'sts_reject_manage' => null, 'reject_reason' => null), array('id' => $post['id']));

			$this->db->update('request_payment', [
				'app_checker' => 1,
				'app_checker_by' => $this->auth->user_id(),
				'app_checker_date' => date('Y-m-d H:i:s')
			], [
				'no_doc' => $post['id_kasbon']
			]);

			$this->db->update('tr_kasbon', ['sts_reject' => 0, 'sts_reject_manage' => 0], ['no_doc' => $post['id_kasbon']]);
			// if ($post['tipe'] == "kasbon") {
			// }
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
		} else {
			$this->db->trans_commit();
			$result = true;
		}
		$param = array(
			'save' => $result
		);

		echo json_encode($param);
	}

	public function save_approval_consultant()
	{

		$id = $this->input->post('id');

		$header 	= $this->db->get_where('request_payment', ['no_doc' => $id])->row_array();
		// $Id 		= $this->_getIdPayment(str_replace('/', '-', $Data['date']));

		$no_coa_bank = explode(' - ', $header['bank_name']);
		$no_coa_bank = $no_coa_bank[0];

		$kode_bank = '';
		$get_kode_bank = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $no_coa_bank])->row();
		if (count($get_kode_bank) > 0) {
			$kode_bank = $get_kode_bank->kode_bank;
		}

		$Id = $this->Approval_request_payment_model->generate_id_payment($kode_bank);

		$ArrDetail 			= [];

		$check_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $id))->num_rows();

		if ($check_kasbon > 0) {
			$tipe = 'kasbon';
		} else {
			$tipe = 'expense';
		}


		if ($tipe == 'expense') {
			$id_detail = $this->Approval_request_payment_model->generate_id_detail(1);
			$dtl = $this->db->get_where(DBCNL . '.kons_tr_expense_report_project_header', ['id' => $id])->row();

			$harga = $dtl->selisih;
			$total = $dtl->selisih;

			$ArrDetail[] 		= [
				'id' 			=> $id_detail,
				'payment_id' 	=> $Id,
				'no_doc' 		=> $dtl->id,
				'tgl_doc' 		=> date('Y-m-d', strtotime($dtl->created_date)),
				'deskripsi' 	=> $header['keperluan'],
				'qty' 			=> 1,
				'harga' 		=> $harga,
				'total' 		=> $total,
				'keterangan' 	=> $header['keperluan'],
				'doc_file' 		=> $header['link_doc'],
				'coa' 			=> '',
				'created_by' 	=> $this->auth->user_name(),
				'created_on' 	=> date("Y-m-d h:i:s"),
			];
			// $updateExpense[] = [
			// 	'id' 			=> $dtl->id,
			// 	'status' 		=> '1',
			// 	'modified_by' 	=> $this->auth->user_name(),
			// 	'modified_on' 	=> date("Y-m-d h:i:s"),
			// ];
			$Harga[] = ($dtl->selisih);
		}

		if ($tipe == 'kasbon') {
			$id_detail = $this->Approval_request_payment_model->generate_id_detail(1);
			$dtl = $this->db->get_where('tr_kasbon', array('no_doc_consultant' => $id));
			$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $dtl->id))->row();
			// $dtl 				= $this->db->get_where(DBCNL.'.kons_tr_kasbon_project_header', ['id' => $get_kasbon->id])->row();

			$ArrDetail[] 		= [
				'id' 			=> $id_detail,
				'payment_id' 	=> $Id,
				'no_doc' 		=> $dtl->no_doc,
				'tgl_doc' 		=> $dtl->tgl_doc,
				'deskripsi' 	=> $dtl->deskripsi_keperluan,
				'qty' 			=> '1',
				'total' 		=> $dtl->jumlah_kasbon,
				'harga' 		=> $dtl->jumlah_kasbon,
				'keterangan' 	=> $dtl->keperluan,
				'doc_file' 		=> $dtl->link_doc,
				'coa' 			=> '',
				'created_by' 	=> $this->auth->user_name(),
				'created_on' 	=> date("Y-m-d h:i:s"),
			];
			// $updateDetail[] = [
			// 	'id' 			=> $dtl->id,
			// 	'status' 		=> '3',
			// 	'modified_by' 	=> $this->auth->user_name(),
			// 	'modified_on' 	=> date("Y-m-d h:i:s"),
			// ];
			$Harga[] 		= $dtl->grand_total;
		}



		$header['jumlah'] 	= array_sum($Harga);
		$header['status'] 	= '1';

		$this->db->trans_begin();

		if (($header)) {
			$header['id'] = $Id;
			$header['approved_by'] = $this->auth->user_name();
			$header['approved_on'] = date("Y-m-d h:i:s");
			$exist_data = $this->db->get_where('payment_approve', ['id' => $id, 'tipe' => $tipe])->num_rows();
			if ($exist_data == '0') {
				$insert_payment_approve = $this->db->insert('payment_approve', $header);
				if (!$insert_payment_approve) {
					print_r($this->db->error()['message']);
					exit;
				}
				// print_r($this->db->last_query());
				// exit;
			}
		}

		/* Details */
		if ($ArrDetail) {

			// print_r($ArrDetail);
			// exit;

			if ($tipe == 'expense') {

				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				$this->db->update('request_payment', ['status' => '2'], ['no_doc' => $dtl->id]);
				// }

			}


			if ($tipe == 'kasbon') {
				$this->db->insert_batch('payment_approve_details', $ArrDetail);
				// $this->db->update_batch('tr_kasbon', $updateDetail, 'id');

				// Update request_payment
				$countData 		= $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', ['id' => $id])->num_rows();
				$actualPayment 	= $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', ['id' => $id])->num_rows();

				$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', ['id' => $id])->row_array();

				$data_request_payment = $this->db->select('id')->get_where('request_payment', ['no_doc' => $get_kasbon['id']])->row_array();

				if ($countData > $actualPayment) {
					$this->db->update('request_payment', ['status' => '1'], ['id' => $data_request_payment['id']]);
				} elseif (($countData == $actualPayment)) {
					$this->db->update('request_payment', ['status' => '2'], ['id' => $data_request_payment['id']]);
				}

				// print_r($countData.' - '.$actualPayment);
				// exit;
			}
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
		} else {
			$this->db->trans_commit();
			$result = true;
		}
		$param = array(
			'save' => $result
		);

		echo json_encode($param);
	}

	public function get_data_req_payment()
	{
		$company_id = $this->input->post('company_id');
		$date_from  = $this->input->post('date_from');
		$date_to    = $this->input->post('date_to');
		$kategori   = $this->input->post('kategori');
		$tab        = $this->input->post('tab') ?: 'belum_dibayar';

		$this->Request_payment_model->get_data_req_payment($company_id, $date_from, $date_to, $kategori, $tab);
	}

	public function get_summary_cards()
	{
		$filters = [
			'company_id' => $this->input->post('company_id'),
			'date_from'  => $this->input->post('date_from'),
			'date_to'    => $this->input->post('date_to'),
			'kategori'   => $this->input->post('kategori'),
		];
		$result = $this->Request_payment_model->get_summary_cards($filters);
		echo json_encode($result);
	}

	public function added_pilih_data()
	{
		$post = $this->input->post();

		$id = $post['id'];
		$kategori = $post['kategori'];
		$wdo = $post['wdo'];

		$this->db->trans_begin();
		if ($wdo == 1) {
			$arr_insert = [
				'no_doc' => $id,
				'tipe' => $kategori,
				'created_by' => $this->auth->user_name(),
				'created_date' => date('Y-m-d H:i:s')
			];
			$this->db->insert('tr_added_req_payment', $arr_insert);
		} else {
			$this->db->delete('tr_added_req_payment', ['no_doc' => $id]);
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function save_request_payment()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$arr_insert = [];

		$get_added = $this->db->get('tr_added_req_payment')->result();

		if (!empty($get_added)) {
			foreach ($get_added as $item) {
				$tanggal_pembayaran = isset($post['tanggal_pembayaran_' . $item->no_doc]) ? $post['tanggal_pembayaran_' . $item->no_doc] : '';
				if (is_array($tanggal_pembayaran)) {
					$tanggal_pembayaran = !empty($tanggal_pembayaran) ? reset($tanggal_pembayaran) : '';
				}
				if (!empty($tanggal_pembayaran) && strpos($tanggal_pembayaran, '/') !== false) {
					$parts = explode('/', $tanggal_pembayaran);
					if (count($parts) == 3) {
						$tanggal_pembayaran = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
					}
				}

				$kategori = isset($post['kategori_' . $item->no_doc]) ? $post['kategori_' . $item->no_doc] : $item->tipe;
				if (is_array($kategori)) {
					$kategori = !empty($kategori) ? reset($kategori) : $item->tipe;
				}

				$nilai_pengajuan = isset($post['nilai_pengajuan_' . $item->no_doc]) ? $post['nilai_pengajuan_' . $item->no_doc] : 0;
				if (is_array($nilai_pengajuan)) {
					$nilai_pengajuan = !empty($nilai_pengajuan) ? reset($nilai_pengajuan) : 0;
				}
				$nilai_pengajuan = (float) str_replace(['.', ','], '', (string)$nilai_pengajuan);

				$tipe_lower = strtolower($item->tipe);

				if ($tipe_lower == 'kasbon') {
					$this->db->select('a.created_by, a.tgl_doc, a.keperluan, a.jumlah_kasbon, a.bank_id, a.accnumber, a.accname, a.id');
					$this->db->from('tr_kasbon a');
					$this->db->where('a.no_doc', $item->no_doc);
					$get_kasbon = $this->db->get()->row();

					if ($get_kasbon) {
						$arr_insert[] = [
							'no_doc'     => (string)$item->no_doc,
							'nama'       => (string)$get_kasbon->created_by,
							'tgl_doc'    => (string)$get_kasbon->tgl_doc,
							'keperluan'  => (string)$get_kasbon->keperluan,
							'tipe'       => 'kasbon',
							'jumlah'     => (float)$get_kasbon->jumlah_kasbon,
							'status'     => 0,
							'tanggal'    => (string)$tanggal_pembayaran,
							'created_by' => (string)$this->auth->user_name(),
							'created_on' => date('Y-m-d H:i:s'),
							'bank_id'    => $get_kasbon->bank_id,
							'accnumber'  => $get_kasbon->accnumber,
							'accname'    => $get_kasbon->accname,
							'ids'        => $get_kasbon->id,
							'currency'   => 'IDR',
							'admin_bank' => 0,
							'total_pph'  => 0
						];

						$this->db->update('tr_kasbon', ['status' => 2], ['no_doc' => $item->no_doc]);
					}
				}

				if ($tipe_lower == 'expense') {
					$this->db->select('a.no_doc, a.tgl_doc, a.nama, a.bank_id, a.accnumber, a.accname, a.id, a.informasi');
					$this->db->from('tr_expense a');
					$this->db->where('a.no_doc', $item->no_doc);
					$get_expense = $this->db->get()->row();

					if ($get_expense) {
						$arr_insert[] = [
							'no_doc'     => (string)$item->no_doc,
							'nama'       => (string)$get_expense->nama,
							'tgl_doc'    => (string)$get_expense->tgl_doc,
							'keperluan'  => (string)$get_expense->informasi,
							'tipe'       => 'expense',
							'jumlah'     => (float)$nilai_pengajuan,
							'status'     => 0,
							'tanggal'    => (string)$tanggal_pembayaran,
							'created_by' => (string)$this->auth->user_name(),
							'created_on' => date('Y-m-d H:i:s'),
							'bank_id'    => $get_expense->bank_id,
							'accnumber'  => $get_expense->accnumber,
							'accname'    => $get_expense->accname,
							'ids'        => $get_expense->id,
							'currency'   => 'IDR',
							'admin_bank' => 0,
							'total_pph'  => 0
						];

						$this->db->update('tr_expense', ['status' => 2], ['no_doc' => $item->no_doc]);
					}
				}

				if ($tipe_lower == 'transport') {
					$this->db->select('a.no_doc, a.tgl_doc, a.nama, a.jumlah_kasbon, a.keterangan, b.bank_id, b.accnumber, b.accname, b.id, b.jumlah_expense');
					$this->db->from('tr_transport a');
					$this->db->join('tr_transport_req b', 'b.no_doc = a.no_req', 'left');
					$this->db->where('a.no_req', $item->no_doc);
					$get_transport = $this->db->get()->row();

					if ($get_transport) {
						$arr_insert[] = [
							'no_doc'     => (string)$item->no_doc,
							'nama'       => (string)$get_transport->nama,
							'tgl_doc'    => (string)$get_transport->tgl_doc,
							'keperluan'  => (string)$get_transport->keterangan,
							'tipe'       => 'transport',
							'jumlah'     => (float)$get_transport->jumlah_expense,
							'status'     => 0,
							'tanggal'    => (string)$tanggal_pembayaran,
							'created_by' => (string)$this->auth->user_name(),
							'created_on' => date('Y-m-d H:i:s'),
							'bank_id'    => $get_transport->bank_id,
							'accnumber'  => $get_transport->accnumber,
							'accname'    => $get_transport->accname,
							'ids'        => $get_transport->id,
							'currency'   => 'IDR',
							'admin_bank' => 0,
							'total_pph'  => 0
						];

						$this->db->update('tr_transport_req', ['status' => 2], ['no_doc' => $item->no_doc]);
					}
				}

				if ($tipe_lower == 'periodik') {
					$this->db->select('a.*, SUM(b.nilai) as nilai_pengajuan, b.bank_id, b.accnumber, b.accname, c.nm_lengkap as nama');
					$this->db->from('tr_pengajuan_rutin a');
					$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc');
					$this->db->join('users c', 'c.id_user = a.created_by');
					$this->db->where('a.no_doc', $item->no_doc);
					$this->db->group_by('a.no_doc');
					$get_periodik = $this->db->get()->row();

					if ($get_periodik) {
						$arr_insert[] = [
							'no_doc'     => (string)$item->no_doc,
							'nama'       => (string)$get_periodik->nama,
							'tgl_doc'    => (string)$get_periodik->tanggal_doc,
							'keperluan'  => (string)$get_periodik->keterangan,
							'tipe'       => 'periodik',
							'jumlah'     => (float)$nilai_pengajuan,
							'status'     => 0,
							'tanggal'    => (string)$tanggal_pembayaran,
							'created_by' => (string)$this->auth->user_name(),
							'created_on' => date('Y-m-d H:i:s'),
							'bank_id'    => $get_periodik->bank_id,
							'accnumber'  => $get_periodik->accnumber,
							'accname'    => $get_periodik->accname,
							'ids'        => $get_periodik->id,
							'currency'   => 'IDR',
							'admin_bank' => 0,
							'total_pph'  => 0
						];

						$this->db->update('tr_pengajuan_rutin', ['status' => 2], ['no_doc' => $item->no_doc]);
					}
				}

				if ($tipe_lower == 'cash') {
					$this->db->select('a.*');
					$this->db->from('tr_pr_non_po a');
					$this->db->where('a.no_non_po', $item->no_doc);
					$get_data_non_po = $this->db->get()->row();

					if ($get_data_non_po) {
						$arr_insert[] = [
							'no_doc'     => (string)$item->no_doc,
							'nama'       => (string)$get_data_non_po->nm_pic,
							'tgl_doc'    => date('Y-m-d', strtotime($get_data_non_po->created_date)),
							'keperluan'  => 'PR Cash - ' . $get_data_non_po->no_pr . ' - ' . ucfirst($get_data_non_po->jenis_pr),
							'tipe'       => 'Cash',
							'jumlah'     => (float)$get_data_non_po->total_pr,
							'status'     => 0,
							'tanggal'    => (string)$tanggal_pembayaran,
							'created_by' => (string)$this->auth->user_name(),
							'created_on' => date('Y-m-d H:i:s'),
							'bank_id'    => isset($get_data_non_po->bank_id) ? $get_data_non_po->bank_id : null,
							'accnumber'  => isset($get_data_non_po->accnumber) ? $get_data_non_po->accnumber : null,
							'accname'    => isset($get_data_non_po->accname) ? $get_data_non_po->accname : null,
							'ids'        => $get_data_non_po->id,
							'currency'   => 'IDR',
							'admin_bank' => 0,
							'total_pph'  => 0
						];

						$this->db->update('tr_pr_non_po', ['sts' => '2'], ['no_non_po' => $item->no_doc]);
					}
				}

				if ($tipe_lower == 'direct payment' || $tipe_lower == 'direct_payment') {
					$this->db->select('a.ids, a.no_doc, a.tgl_doc, a.deskripsi, a.grand_total, a.bank, a.bank_number, a.bank_account, b.nm_lengkap as nama');
					$this->db->from('tr_direct_payment a');
					$this->db->join('users b', 'b.id_user = a.created_by', 'left');
					$this->db->where('a.no_doc', $item->no_doc);
					$get_direct_payment = $this->db->get()->row();

					if ($get_direct_payment) {
						$arr_insert[] = [
							'no_doc'     => (string)$item->no_doc,
							'nama'       => (string)$get_direct_payment->nama,
							'tgl_doc'    => (string)$get_direct_payment->tgl_doc,
							'keperluan'  => (string)$get_direct_payment->deskripsi,
							'tipe'       => 'direct_payment',
							'jumlah'     => (float)$get_direct_payment->grand_total,
							'status'     => 0,
							'tanggal'    => (string)$tanggal_pembayaran,
							'created_by' => (string)$this->auth->user_name(),
							'created_on' => date('Y-m-d H:i:s'),
							'bank_id'    => $get_direct_payment->bank,
							'accnumber'  => $get_direct_payment->bank_number,
							'accname'    => $get_direct_payment->bank_account,
							'ids'        => $get_direct_payment->ids,
							'currency'   => 'IDR',
							'admin_bank' => 0,
							'total_pph'  => 0
						];

						$this->db->update('tr_direct_payment', ['sts' => 2], ['no_doc' => $item->no_doc]);
					}
				}

				// Data yang sudah ada di request_payment, update status saja
				if ($tipe_lower == 'petty cash hutang' || $tipe_lower == 'petty_cash_hutang' || $tipe_lower == 'petty cash' || $tipe_lower == 'petty_cash' || $tipe_lower == 'refill pettycash' || $tipe_lower == 'refill_pettycash') {
					$tipe_update = $item->tipe;
					if ($tipe_lower == 'petty cash hutang' || $tipe_lower == 'petty_cash_hutang') {
						$tipe_update = 'petty_cash_hutang';
					} else if ($tipe_lower == 'petty cash' || $tipe_lower == 'petty_cash') {
						$tipe_update = 'petty_cash';
					} else if ($tipe_lower == 'refill pettycash' || $tipe_lower == 'refill_pettycash') {
						$tipe_update = 'refill_pettycash';
					}

					// Ensure we also update the tipe in request_payment to be consistent
					$this->db->update('request_payment', ['status' => 2, 'tipe' => $tipe_update], ['no_doc' => $item->no_doc]);
				}
			}
		}

		if (!empty($arr_insert)) {
			$insert_req_payment = $this->db->insert_batch('request_payment', $arr_insert);
			if (!$insert_req_payment) {
				$this->db->trans_rollback();

				$db_error = $this->db->error();
				echo json_encode([
					'status' => 0,
					'msg'    => 'Gagal simpan data batch: ' . (!empty($db_error['message']) ? $db_error['message'] : 'Database error')
				]);
				exit;
			}
		}

		// Selalu clear tr_added_req_payment setelah proses
		$this->db->from('tr_added_req_payment');
		$this->db->where('no_doc IS NOT NULL');
		$this->db->delete();

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();

			$valid = 0;
			$msg = 'Please try again later !';
		} else {
			$this->db->trans_commit();

			$this->Request_payment_model->copy_to_payment();

			$valid = 1;
			$msg = 'Data has been processed !';
		}

		echo json_encode([
			'status' => $valid,
			'msg' => $msg
		]);
	}

	public function reset_choosed_req_payment()
	{
		$this->db->trans_begin();

		$this->db->from('tr_added_req_payment');
		$this->db->where('no_doc IS NOT NULL');
		$this->db->delete();

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function copy_to_payment()
	{
		$this->Request_payment_model->copy_to_payment();
	}

	public function reject_req_payment()
	{
		$list_added_req_payment = $this->Request_payment_model->list_added_req_payment();

		$reject_reason = $this->input->post('reject_reason');

		$this->db->trans_begin();

		if (count($list_added_req_payment) > 0) {
			foreach ($list_added_req_payment as $item) {
				if ($item->tipe == 'Kasbon') {
					$data_reject = [
						'status' => '9',
						'st_reject' => $reject_reason
					];

					$update_reject_kasbon = $this->db->update('tr_kasbon', $data_reject, ['no_doc' => $item->no_doc]);
					if (!$update_reject_kasbon) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				}

				if ($item->tipe == 'Transport') {
					$data_reject = [
						'status' => '9',
						'st_reject' => $reject_reason
					];

					$update_reject_transport = $this->db->update('tr_transport_req', $data_reject, ['no_doc' => $item->no_doc]);
					if (!$update_reject_transport) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				}

				if ($item->tipe == 'Expense') {
					$data_reject = [
						'status' => '9',
						'st_reject' => $reject_reason
					];

					$update_reject_expense = $this->db->update('tr_expense', $data_reject, ['no_doc' => $item->no_doc]);
					if (!$update_reject_expense) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				}

				if ($item->tipe == 'Periodik') {
					$data_reject = [
						'status' => '9',
						'sts_reject' => '1',
						'reject_ket' => $reject_reason
					];

					$update_reject_periodik = $this->db->update('tr_pengajuan_rutin', $data_reject, ['no_doc' => $item->no_doc]);
					if (!$update_reject_periodik) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				}

				if ($item->tipe == 'Direct Payment') {
					$data_reject = [
						'sts' => '9',
						'sts_reject' => '1',
						'reject_reason' => $reject_reason
					];

					$update_reject_direct_payment = $this->db->update('tr_direct_payment', $data_reject, ['no_doc' => $item->no_doc]);
					if (!$update_reject_direct_payment) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				}

				if ($this->db->trans_status() === false) {
					$this->db->trans_rollback();

					$valid = 0;
					$msg = 'Please try again later !';
				} else {
					$this->db->trans_commit();

					$valid = 1;
					$msg = 'Data has been rejected !';
				}

				$response = [
					'status' => $valid,
					'msg' => $msg
				];

				echo json_encode($response);
			}
		} else {
			$valid = 0;
			$msg = 'Belum ada data request payment yang dipilih !';

			$response = [
				'status' => $valid,
				'msg' => $msg
			];

			echo json_encode($response);
		}
	}

	public function download_excel_request_payment()
	{
		set_time_limit(0);
		ini_set('memory_limit', '512M');

		$filters = [
			'company_id' => $this->input->get('company_id'),
			'date_from'  => $this->input->get('date_from'),
			'date_to'    => $this->input->get('date_to'),
			'kategori'   => $this->input->get('kategori'),
		];

		$list_all_request_payment = $this->Request_payment_model->list_all_request_payment($filters);

		// Build company names lookup (same logic as DataTable)
		$company_map = ['COM003' => 7, 'COM006' => 3, 'COM012' => 4];
		$company_names = [];
		$company_query = $this->db->query("SELECT id, nm_company as nama FROM " . DBCNL . ".kons_tr_company WHERE id IN ('3','4','7')");
		if ($company_query) {
			foreach ($company_query->result() as $comp) {
				$company_names[$comp->id] = $comp->nama;
			}
		}

		$this->load->library('PHPExcel');

		$objPHPExcel = new PHPExcel();
		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->setTitle('Request Payment');

		// Header style
		$headerStyle = [
			'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
			'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
			'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
			'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
		];

		$bodyStyle = [
			'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
		];

		// Column headers
		$headers = ['#', 'No. Dokumen', 'Request By', 'Company', 'Tanggal Pengajuan', 'Keperluan', 'Kategori', 'Nilai Pengajuan', 'Tanggal di Approve'];
		$cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

		$row = 1;
		foreach ($headers as $i => $header) {
			$sheet->setCellValue($cols[$i] . $row, $header);
		}
		$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

		// Column widths
		$sheet->getColumnDimension('A')->setWidth(5);
		$sheet->getColumnDimension('B')->setWidth(25);
		$sheet->getColumnDimension('C')->setWidth(25);
		$sheet->getColumnDimension('D')->setWidth(25);
		$sheet->getColumnDimension('E')->setWidth(18);
		$sheet->getColumnDimension('F')->setWidth(40);
		$sheet->getColumnDimension('G')->setWidth(18);
		$sheet->getColumnDimension('H')->setWidth(20);
		$sheet->getColumnDimension('I')->setWidth(20);

		// Data rows
		$row = 2;
		$no = 0;

		if (!empty($list_all_request_payment)) {
			foreach ($list_all_request_payment as $item) {
				$no++;

				// Request By (with Kasbon special logic)
				$nmuser = $item->request_by;
				if ($item->kategori == 'Kasbon') {
					$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item->no_dokumen])->row();
					if ($get_kasbon) {
						$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item->no_dokumen])->result();
						if (count($check_detail)) {
							if ($get_kasbon->tipe_pr == 'pr departemen') {
								$this->db->select('b.nm_lengkap');
								$this->db->from('rutin_non_planning_header a');
								$this->db->join('users b', 'b.id_user = a.created_by');
								$this->db->where('a.no_pr', $get_kasbon->id_pr);
								$get_single_detail = $this->db->get()->row();
								if ($get_single_detail) $nmuser = $get_single_detail->nm_lengkap;
							}
							if ($get_kasbon->tipe_pr == 'pr stok') {
								$this->db->select('b.nm_lengkap');
								$this->db->from('material_planning_base_on_produksi a');
								$this->db->join('users b', 'b.id_user = a.created_by');
								$this->db->where('a.no_pr', $get_kasbon->id_pr);
								$get_single_detail = $this->db->get()->row();
								if ($get_single_detail) $nmuser = $get_single_detail->nm_lengkap;
							}
						}
					}
				}

				// Company display - derive from hris_companies.id via mapping
				$company_display = '';
				if (!empty($item->id_company) && isset($company_map[$item->id_company])) {
					$mapped_id = $company_map[$item->id_company];
					if (isset($company_names[$mapped_id])) {
						$company_display = $company_names[$mapped_id];
					}
				}

				// Fallback untuk Petty Cash Hutang dan Petty Cash biasa
				if (empty($company_display) && ($item->kategori == 'Petty Cash Hutang' || $item->kategori == 'Petty Cash' || strpos($item->no_dokumen, 'RPC-') === 0)) {
					$get_petty_cash = $this->db->select('company')->get_where('tr_petty_cash_vuca_sustain', ['no_payment_hutang' => $item->no_dokumen])->row();
					if (!empty($get_petty_cash)) {
						$company_display = $get_petty_cash->company;
					}

					if (empty($company_display) && strpos($item->no_dokumen, 'RPC-') === 0) {
						$get_rpc = $this->db->select('company')->get_where('tr_pelaporan_petty_cash', ['no_pelaporan' => $item->no_dokumen])->row();
						if (!empty($get_rpc) && !empty($get_rpc->company)) {
							$company_display = $get_rpc->company;
						} else {
							$company_display = 'STM';
						}
					}
				}

				// Tanggal Pengajuan
				$tanggal_pengajuan = (!empty($item->tanggal) && strtotime($item->tanggal) !== false) ? date('d-M-Y', strtotime($item->tanggal)) : '';

				// Tanggal di Approve
				$tgl_approve = $this->_get_tanggal_approval($item);
				$tgl_approve_formatted = (!empty($tgl_approve) && strtotime($tgl_approve) !== false) ? date('d-M-Y', strtotime($tgl_approve)) : '';

				// Nilai Pengajuan
				$nilai = (!empty($item->nilai_pengajuan)) ? (float) $item->nilai_pengajuan : 0;

				// Keperluan
				$keperluan = (!empty($item->keperluan)) ? $item->keperluan : '';

				// Write data
				$sheet->setCellValue('A' . $row, $no);
				$sheet->setCellValue('B' . $row, $item->no_dokumen);
				$sheet->setCellValue('C' . $row, $nmuser);
				$sheet->setCellValue('D' . $row, $company_display);
				$sheet->setCellValue('E' . $row, $tanggal_pengajuan);
				$sheet->setCellValue('F' . $row, $keperluan);
				$sheet->setCellValue('G' . $row, $item->kategori);
				$sheet->setCellValue('H' . $row, $nilai);
				$sheet->setCellValue('I' . $row, $tgl_approve_formatted);

				// Apply body style
				$sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($bodyStyle);

				// Center alignment for specific columns
				$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				// Number format for nilai pengajuan
				$sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
				$sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$row++;
			}
		}

		// Output file
		$filename = 'Request_Payment_' . date('d-m-Y') . '.xls';

		if (ob_get_level()) {
			ob_end_clean();
		}

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * Helper: Get tanggal approval dari tabel tagihan berdasarkan kategori
	 */
	private function _get_tanggal_approval($item)
	{
		$tgl_approve = '';
		switch ($item->kategori) {
			case 'Kasbon':
				$row = $this->db->select('approved_on')->get_where('tr_kasbon', ['no_doc' => $item->no_dokumen])->row();
				if ($row && !empty($row->approved_on)) {
					$tgl_approve = $row->approved_on;
				}
				break;
			case 'Transport':
				$row = $this->db->select('approved_on')->get_where('tr_transport_req', ['no_doc' => $item->no_dokumen])->row();
				if ($row && !empty($row->approved_on)) {
					$tgl_approve = $row->approved_on;
				}
				break;
			case 'Cash':
			case 'Non-PO':
				$row = $this->db->select('created_date')->get_where('tr_pr_non_po', ['id' => $item->id])->row();
				if ($row && !empty($row->created_date)) {
					$tgl_approve = $row->created_date;
				}
				break;
			case 'Expense':
				$row = $this->db->select('approved_on')->get_where('tr_expense', ['no_doc' => $item->no_dokumen])->row();
				if ($row && !empty($row->approved_on)) {
					$tgl_approve = $row->approved_on;
				}
				break;
			case 'Periodik':
				$row = $this->db->select('approved_date')->get_where('tr_pengajuan_rutin', ['no_doc' => $item->no_dokumen])->row();
				if ($row && !empty($row->approved_date)) {
					$tgl_approve = $row->approved_date;
				}
				break;
			case 'Direct Payment':
				$row = $this->db->select('created_date as approved_on')->get_where('tr_direct_payment', ['no_doc' => $item->no_dokumen])->row();
				if ($row && !empty($row->approved_on)) {
					$tgl_approve = $row->approved_on;
				}
				break;
			default:
				$tgl_approve = '';
				break;
		}
		return $tgl_approve;
	}

	public function print_cash($id)
	{
		// Validate ID and fetch Direct_Payment_Record
		if (empty($id)) {
			show_404();
		}

		$get_data_cash = $this->db->get_where('tr_pr_non_po', ['no_non_po' => $id])->row();

		if (empty($get_data_cash)) {
			show_404();
		}

		if ($get_data_cash->jenis_pr == 'pr departemen') {
			// Fetch PR_Header from rutin_non_planning_header
			$pr_header = $this->db->get_where('rutin_non_planning_header', ['no_pr' => $get_data_cash->no_pr])->row();

			// Fetch PR_Detail rows from rutin_non_planning_detail
			$pr_details = $this->db->get_where('rutin_non_planning_detail', ['no_pr' => $get_data_cash->no_pr])->result_array();
			if (empty($pr_details)) {
				$pr_details = [];
			}

			// Resolve dept_name from HRIS departments
			$dept_name = '';
			if (!empty($pr_header->id_dept)) {
				$hris = $this->load->database('hris', true);
				$dept_row = $hris->get_where('departments', ['id' => $pr_header->id_dept])->row();
				if (!empty($dept_row)) {
					$dept_name = $dept_row->name;
				}
			}

			// Resolve coa_display from DBACC.coa_master
			$coa_display = '';
			if (!empty($pr_header->coa)) {
				$coa_row = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $pr_header->coa])->row();
				if (!empty($coa_row)) {
					$coa_display = $coa_row->no_perkiraan . ' ' . $coa_row->nama;
				} else {
					$coa_display = $pr_header->coa;
				}
			}

			// Resolve request_by from users table
			$request_by = '';
			if (!empty($pr_header->created_by)) {
				$user_row = $this->db->get_where('users', ['id_user' => $pr_header->created_by])->row();
				if (!empty($user_row)) {
					$request_by = $user_row->nm_lengkap;
				}
			}

			$data = [
				'title' => 'Pengajuan Direct Payment',
				'data_pr' => $get_data_cash,
				'pr_header' => $pr_header,
				'pr_details' => $pr_details,
				'dept_name' => $dept_name,
				'coa_display' => $coa_display,
				'request_by' => $request_by,
				'bank_name' => !empty($pr_header->bank_name) ? $pr_header->bank_name : '',
				'bank_account_no' => !empty($pr_header->bank_account_no) ? $pr_header->bank_account_no : '',
				'bank_account_name' => !empty($pr_header->bank_account_name) ? $pr_header->bank_account_name : ''
			];

			$this->load->view('print_cash', $data);
		} else {
			// Preserve existing non-PR-departemen rendering path
			$get_v_req_payment = $this->db->get_where('v_request_payment', ['no_dokumen' => $id])->row();

			$this->db->select('CONCAT("assets/pr/", a.dokument_pendukung) as doc_file, a.no_pr as no_doc');
			$this->db->from('tran_pr_header a');
			$this->db->where('a.no_pr', $get_data_cash->no_pr);
			$get_doc_pr = $this->db->get()->row();

			$data = [
				'data_pr' => $get_data_cash,
				'v_req_payment' => $get_v_req_payment,
				'doc_pr' => $get_doc_pr
			];

			$this->load->view('print_cash_non_pr', $data);
		}
	}

	public function print_direct_payment($id)
	{
		$id = urldecode($id);
		$id = str_replace('|', '/', $id);


		$get_kasbon_header = $this->consultant->get_where('kons_tr_kasbon_project_header', array('id' => $id))->row();

		if (!empty($get_kasbon_header)) {
			$id_spk_penawaran = $get_kasbon_header->id_spk_penawaran;

			$get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->consultant->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->consultant->from('kons_tr_spk_penawaran a');
			$this->consultant->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->consultant->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->consultant->get()->row();

			$tipe = '';
			if ($get_kasbon_header->tipe == '1') {
				$tipe = 'Direct Payment Subcont';
			}
			if ($get_kasbon_header->tipe == '2') {
				$tipe = 'Direct Payment Akomodasi';
			}
			if ($get_kasbon_header->tipe == '3') {
				$tipe = 'Direct Payment Others';
			}
			if ($get_kasbon_header->tipe == '4') {
				$tipe = 'Direct Payment Lab';
			}
			if ($get_kasbon_header->tipe == '5') {
				$tipe = 'Direct Payment Subcont Tenaga Ahli';
			}
			if ($get_kasbon_header->tipe == '6') {
				$tipe = 'Direct Payment Subcont Perusahaan';
			}

			// ============================================================
			// FIX: Real-time Sisa Qty & Sisa Budget Calculation
			// Issue: Snapshot (aktual_terpakai, sisa_budget) gives wrong values
			// when multiple kasbons exist for the same item in same SPK
			// Solution: Calculate real-time by querying all other kasbons
			// ============================================================

			// [START] KASBON SUBCONT - Real-time calculation
			$this->consultant->select('a.*,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_subcont b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_aktifitas = a.id_aktifitas
					AND b.id_spk_budgeting = a.id_spk_budgeting
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_subcont b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_aktifitas = a.id_aktifitas
					AND b.id_spk_budgeting = a.id_spk_budgeting
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_subcont a');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_subcont = $this->consultant->get()->result();
			// [END] KASBON SUBCONT

			// [START] KASBON AKOMODASI - Real-time calculation (with qty_budget_tambahan & budget_tambahan)
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi + COALESCE(a.qty_budget_tambahan, 0) - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_akomodasi b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_akomodasi = a.id_akomodasi
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi + COALESCE(a.budget_tambahan, 0) - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_akomodasi b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_akomodasi = a.id_akomodasi
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_akomodasi a');
			$this->consultant->join('kons_tr_penawaran_akomodasi b', 'b.id = a.id_akomodasi', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_akomodasi = $this->consultant->get()->result();
			// [END] KASBON AKOMODASI

			// [START] KASBON OTHERS - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_others b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_others = a.id_others
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_others b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_others = a.id_others
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_others a');
			$this->consultant->join('kons_tr_penawaran_others b', 'b.id = a.id_others', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_others = $this->consultant->get()->result();
			// [END] KASBON OTHERS

			// [START] KASBON LAB - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_lab b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_lab = a.id_lab
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_lab b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_lab = a.id_lab
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_lab a');
			$this->consultant->join('kons_tr_penawaran_lab b', 'b.id = a.id_lab', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_lab = $this->consultant->get()->result();
			// [END] KASBON LAB

			// [START] KASBON SUBCONT TENAGA AHLI - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_subcont_tenaga_ahli b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_subcont_tenaga_ahli b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
			$this->consultant->join('kons_tr_penawaran_subcont_tenaga_ahli b', 'b.id = a.id_subcont', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_subcont_tenaga_ahli = $this->consultant->get()->result();
			// [END] KASBON SUBCONT TENAGA AHLI

			// [START] KASBON SUBCONT PERUSAHAAN - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_subcont_perusahaan b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_subcont_perusahaan b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_subcont_perusahaan a');
			$this->consultant->join('kons_tr_penawaran_subcont_perusahaan b', 'b.id = a.id_subcont', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_subcont_perusahaan = $this->consultant->get()->result();
			// [END] KASBON SUBCONT PERUSAHAAN

			$get_request_payment = $this->consultant->get_where('request_payment', array('no_doc' => $id))->row();

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'data_kasbon_header' => $get_kasbon_header,
				'data_kasbon_subcont' => $get_kasbon_subcont,
				'data_kasbon_akomodasi' => $get_kasbon_akomodasi,
				'data_kasbon_others' => $get_kasbon_others,
				'data_kasbon_lab' => $get_kasbon_lab,
				'data_kasbon_subcont_tenaga_ahli' => $get_kasbon_subcont_tenaga_ahli,
				'data_kasbon_subcont_perusahaan' => $get_kasbon_subcont_perusahaan,
				'tipe' => $tipe,
				'tgl_approve_direktur' => $get_request_payment->created_on
			];
		} else {
			$this->consultant->select('a.*, b.id_spk_penawaran');
			$this->consultant->from('kons_tr_expense_report_project_header a');
			$this->consultant->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
			$this->consultant->where('a.id', $id);
			$get_expense = $this->consultant->get()->row();

			$id_spk_penawaran = $get_expense->id_spk_penawaran;

			$get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->consultant->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->consultant->from('kons_tr_spk_penawaran a');
			$this->consultant->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->consultant->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->consultant->get()->row();

			$tipe = 'Expense';

			$get_expense_detail = $this->consultant->get_where('kons_tr_expense_report_project_detail', array('id_header_expense' => $id))->result();

			$list_detail_expense_detail = [];
			foreach ($get_expense_detail as $item_expense_detail) :
				if ($item_expense_detail->tipe == '1') {
					$get_spk_budgeting = $this->consultant->get_where('kons_tr_spk_budgeting_aktifitas', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_subcont', array('id_spk_budgeting' => $item_expense_detail->id_spk_budgeting, 'id_aktifitas' => $get_spk_budgeting->id_aktifitas))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_aktifitas,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '2') {
					$get_spk_budgeting = $this->consultant->get_where('kons_tr_spk_budgeting_akomodasi', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_akomodasi', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_akomodasi' => $item_expense_detail->id_akomodasi))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_item,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '3') {
					$get_spk_budgeting = $this->consultant->get_where('kons_tr_spk_budgeting_others', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_others', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_others' => $item_expense_detail->id_others))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_item,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
			endforeach;


			$title_expense = '';
			if ($get_expense->tipe == '1') {
				$title_expense = 'Expense Subcont';
			}
			if ($get_expense->tipe == '2') {
				$title_expense = 'Expense Akomodasi';
			}
			if ($get_expense->tipe == '3') {
				$title_expense = 'Expense Others';
			}
			if ($get_expense->tipe == '4') {
				$title_expense = 'Expense Lab';
			}

			$this->consultant->select('a.*');
			$this->consultant->from('kons_tr_kasbon_project_header a');
			$this->consultant->join('kons_tr_expense_report_project_header b', 'b.id_header = a.id');
			$this->consultant->where('b.id', $id);
			$get_kasbon = $this->consultant->get()->row();

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'list_expense_detail' => $get_expense_detail,
				'data_kasbon_header' => $get_kasbon,
				'tipe' => $tipe,
				'title_expense' => $title_expense,
				'list_detail_expense_detail' => $list_detail_expense_detail
			];
		}

		$get_request_payment = $this->consultant->get_where('request_payment', array('no_doc' => $id))->row();

		$today = date('l, d F Y [H:i:s]');

		// $this->load->library(array('Mpdf'));
		$mpdf = new Mpdf();
		// $mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('print_direct_payment', $data);

		$footer = 'Printed by : ' . ucfirst(strtolower($this->auth->user_name())) . ', ' . $today . ' / ' . $id . '';
		// $mpdf->SetWatermarkText('ORI Group');
		$mpdf->showWatermarkText = true;
		$mpdf->SetTitle($id . "/" . date('ymdhis'));
		$mpdf->AddPage();
		$mpdf->SetFooter($footer);
		$mpdf->WriteHTML($show);
		$mpdf->Output(' ' . $id . '/' . date('ymdhis') . '.pdf', 'D');
	}

	public function print_kasbon($id)
	{
		$id = urldecode($id);
		$id = str_replace('|', '/', $id);


		$get_kasbon_header = $this->consultant->get_where('kons_tr_kasbon_project_header', array('id' => $id))->row();

		if (!empty($get_kasbon_header)) {
			$id_spk_penawaran = $get_kasbon_header->id_spk_penawaran;

			$get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->consultant->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->consultant->from('kons_tr_spk_penawaran a');
			$this->consultant->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->consultant->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->consultant->get()->row();

			$tipe = '';
			if ($get_kasbon_header->tipe == '1') {
				$tipe = 'Kasbon Subcont';
			}
			if ($get_kasbon_header->tipe == '2') {
				$tipe = 'Kasbon Akomodasi';
			}
			if ($get_kasbon_header->tipe == '3') {
				$tipe = 'Kasbon Others';
			}
			if ($get_kasbon_header->tipe == '4') {
				$tipe = 'Kasbon Lab';
			}
			if ($get_kasbon_header->tipe == '5') {
				$tipe = 'Kasbon Subcont Tenaga Ahli';
			}
			if ($get_kasbon_header->tipe == '6') {
				$tipe = 'Kasbon Subcont Perusahaan';
			}

			// ============================================================
			// FIX: Real-time Sisa Qty & Sisa Budget Calculation
			// Issue: Snapshot (aktual_terpakai, sisa_budget) gives wrong values
			// when multiple kasbons exist for the same item in same SPK
			// Solution: Calculate real-time by querying all other kasbons
			// ============================================================

			// [START] KASBON SUBCONT - Real-time calculation
			$this->consultant->select('a.*,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_subcont b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_aktifitas = a.id_aktifitas
					AND b.id_spk_budgeting = a.id_spk_budgeting
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_subcont b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_aktifitas = a.id_aktifitas
					AND b.id_spk_budgeting = a.id_spk_budgeting
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_subcont a');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_subcont = $this->consultant->get()->result();
			// [END] KASBON SUBCONT

			// [START] KASBON AKOMODASI - Real-time calculation (with qty_budget_tambahan & budget_tambahan)
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi + COALESCE(a.qty_budget_tambahan, 0) - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_akomodasi b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_akomodasi = a.id_akomodasi
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi + COALESCE(a.budget_tambahan, 0) - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_akomodasi b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_akomodasi = a.id_akomodasi
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_akomodasi a');
			$this->consultant->join('kons_tr_penawaran_akomodasi b', 'b.id = a.id_akomodasi', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_akomodasi = $this->consultant->get()->result();
			// [END] KASBON AKOMODASI

			// [START] KASBON OTHERS - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_others b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_others = a.id_others
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_others b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_others = a.id_others
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_others a');
			$this->consultant->join('kons_tr_penawaran_others b', 'b.id = a.id_others', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_others = $this->consultant->get()->result();
			// [END] KASBON OTHERS

			// [START] KASBON LAB - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_lab b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_lab = a.id_lab
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_lab b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_lab = a.id_lab
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_lab a');
			$this->consultant->join('kons_tr_penawaran_lab b', 'b.id = a.id_lab', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_lab = $this->consultant->get()->result();
			// [END] KASBON LAB

			// [START] KASBON SUBCONT TENAGA AHLI - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_subcont_tenaga_ahli b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_subcont_tenaga_ahli b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
			$this->consultant->join('kons_tr_penawaran_subcont_tenaga_ahli b', 'b.id = a.id_subcont', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_subcont_tenaga_ahli = $this->consultant->get()->result();
			// [END] KASBON SUBCONT TENAGA AHLI

			// [START] KASBON SUBCONT PERUSAHAAN - Real-time calculation
			$this->consultant->select('a.*, b.keterangan,
				(a.qty_estimasi - COALESCE(
					(SELECT SUM(b.qty_pengajuan)
					FROM kons_tr_kasbon_project_subcont_perusahaan b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.qty_pengajuan) as sisa_qty_realtime,
				(a.total_budget_estimasi - COALESCE(
					(SELECT SUM(b.total_pengajuan)
					FROM kons_tr_kasbon_project_subcont_perusahaan b
					JOIN kons_tr_kasbon_project_header h ON h.id = b.id_header
					WHERE b.id_subcont = a.id_subcont
					AND h.id_spk_penawaran = "' . $id_spk_penawaran . '"
					AND b.id_header != a.id_header
					), 0) - a.total_pengajuan) as sisa_budget_realtime
			');
			$this->consultant->from('kons_tr_kasbon_project_subcont_perusahaan a');
			$this->consultant->join('kons_tr_penawaran_subcont_perusahaan b', 'b.id = a.id_subcont', 'left');
			$this->consultant->where('a.id_header', $id);
			$get_kasbon_subcont_perusahaan = $this->consultant->get()->result();
			// [END] KASBON SUBCONT PERUSAHAAN

			$get_request_payment = $this->consultant->get_where('request_payment', array('no_doc' => $id))->row();

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'data_kasbon_header' => $get_kasbon_header,
				'data_kasbon_subcont' => $get_kasbon_subcont,
				'data_kasbon_akomodasi' => $get_kasbon_akomodasi,
				'data_kasbon_others' => $get_kasbon_others,
				'data_kasbon_lab' => $get_kasbon_lab,
				'data_kasbon_subcont_tenaga_ahli' => $get_kasbon_subcont_tenaga_ahli,
				'data_kasbon_subcont_perusahaan' => $get_kasbon_subcont_perusahaan,
				'tipe' => $tipe,
				'tgl_approve_direktur' => $get_request_payment->created_on
			];
		} else {
			$this->consultant->select('a.*, b.id_spk_penawaran');
			$this->consultant->from('kons_tr_expense_report_project_header a');
			$this->consultant->join('kons_tr_kasbon_project_header b', 'b.id = a.id_header');
			$this->consultant->where('a.id', $id);
			$get_expense = $this->consultant->get()->row();

			$id_spk_penawaran = $get_expense->id_spk_penawaran;

			$get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->consultant->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->consultant->from('kons_tr_spk_penawaran a');
			$this->consultant->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->consultant->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->consultant->get()->row();

			$tipe = 'Expense';

			$get_expense_detail = $this->consultant->get_where('kons_tr_expense_report_project_detail', array('id_header_expense' => $id))->result();

			$list_detail_expense_detail = [];
			foreach ($get_expense_detail as $item_expense_detail) :
				if ($item_expense_detail->tipe == '1') {
					$get_spk_budgeting = $this->consultant->get_where('kons_tr_spk_budgeting_aktifitas', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_subcont', array('id_spk_budgeting' => $item_expense_detail->id_spk_budgeting, 'id_aktifitas' => $get_spk_budgeting->id_aktifitas))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_aktifitas,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '2') {
					$get_spk_budgeting = $this->consultant->get_where('kons_tr_spk_budgeting_akomodasi', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_akomodasi', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_akomodasi' => $item_expense_detail->id_akomodasi))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_item,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '3') {
					$get_spk_budgeting = $this->consultant->get_where('kons_tr_spk_budgeting_others', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_others', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_others' => $item_expense_detail->id_others))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_item,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
			endforeach;


			$title_expense = '';
			if ($get_expense->tipe == '1') {
				$title_expense = 'Expense Subcont';
			}
			if ($get_expense->tipe == '2') {
				$title_expense = 'Expense Akomodasi';
			}
			if ($get_expense->tipe == '3') {
				$title_expense = 'Expense Others';
			}
			if ($get_expense->tipe == '4') {
				$title_expense = 'Expense Lab';
			}

			$this->consultant->select('a.*');
			$this->consultant->from('kons_tr_kasbon_project_header a');
			$this->consultant->join('kons_tr_expense_report_project_header b', 'b.id_header = a.id');
			$this->consultant->where('b.id', $id);
			$get_kasbon = $this->consultant->get()->row();

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'list_expense_detail' => $get_expense_detail,
				'data_kasbon_header' => $get_kasbon,
				'tipe' => $tipe,
				'title_expense' => $title_expense,
				'list_detail_expense_detail' => $list_detail_expense_detail
			];
		}

		$get_request_payment = $this->consultant->get_where('request_payment', array('no_doc' => $id))->row();

		$today = date('l, d F Y [H:i:s]');

		// $this->load->library(array('Mpdf'));
		$mpdf = new Mpdf();
		// $mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('print_kasbon', $data);

		$footer = 'Printed by : ' . ucfirst(strtolower($this->auth->user_name())) . ', ' . $today . ' / ' . $id . '';
		// $mpdf->SetWatermarkText('ORI Group');
		$mpdf->showWatermarkText = true;
		$mpdf->SetTitle($id . "/" . date('ymdhis'));
		$mpdf->AddPage();
		$mpdf->SetFooter($footer);
		$mpdf->WriteHTML($show);
		$mpdf->Output(' ' . $id . '/' . date('ymdhis') . '.pdf', 'D');
	}
}
