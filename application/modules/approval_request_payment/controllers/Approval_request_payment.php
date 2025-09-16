<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// require_once 'mpdf/src/autoload.php'; // Load dependencies
// require_once 'mpdf/src/Mpdf.php';         // Load mPDF manually

require_once 'vendor/autoload.php';

// foreach (glob('mpdf/src/*.php') as $file) {
// 	require_once $file;
// }
/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is controller for Request Payment
 */

use Mpdf\Mpdf;

$status = array();
class Approval_request_payment extends Admin_Controller
{

	//Permission
	protected $viewPermission   = "Request_Payment.View";
	protected $addPermission    = "Request_Payment.Add";
	protected $managePermission = "Request_Payment.Manage";
	protected $deletePermission = "Request_Payment.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Approval_request_payment/Approval_request_payment_model', 'All/All_model', 'Jurnal_nomor/Jurnal_model'));
		$this->template->title('Manage Request Payment');
		$this->template->page_icon('fa fa-table');
		// $this->status = array("0" => "Baru", "1" => "Disetujui", "2" => "Selesai");
		date_default_timezone_set("Asia/Bangkok");
	}

	public function index()
	{
		$data = $this->Approval_request_payment_model->GetListDataRequestNew();
		$list_mata_uang = $this->db->get('mata_uang')->result_array();
		// $list_coa = $this->db->get_where(DBACC.'.coa_master', ['no_perkirran'])->result_array();

		$this->db->select('a.*');
		$this->db->from(DBCNL . '.coa_master a')
			->where('a.no_perkiraan LIKE', '%1101-02%')
			->where('a.kode_bank <>', '')
			->where('a.kode_bank <>', '');
		$list_coa = $this->db->get()->result_array();

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from(DBCNL . '.tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$get_vendor = $this->db->get_where(DBCNL . '.new_supplier', ['deleted_by' => null])->result();

		$this->template->set('data', $data);
		$this->template->set('list_curr', $list_mata_uang);
		$this->template->set('list_coa', $list_coa);
		$this->template->set('list_no_invoice', $list_no_invoice);
		$this->template->set('list_vendor', $get_vendor);
		$this->template->title('Request Payment');
		$this->template->render('index');
	}
	public function payment_list()
	{

		$list_tgl_pengajuan_pembayaran = [];
		$get_payment_approve = $this->db->select('no_doc, created_by, pay_by, DATE_FORMAT(created_on, "%d %M %Y") as tgl_pengajuan, IF(pay_on IS NULL, "", DATE_FORMAT(pay_on, "%d %M %Y")) as tgl_pembayaran')->get('payment_approve')->result();
		foreach ($get_payment_approve as $item_payment) {
			$list_tgl_pengajuan_pembayaran[$item_payment->no_doc] = [
				'diajukan_oleh' => $item_payment->created_by,
				'dibayar_oleh' => $item_payment->pay_by,
				'tgl_pengajuan' => $item_payment->tgl_pengajuan,
				'tgl_pembayaran' => $item_payment->tgl_pembayaran
			];
		}

		$data_bank = $this->db->select('no_perkiraan, nama')->get_where(DBCNL . '.coa_master', ['nama LIKE' => '%bank%'])->result();
		$this->template->set('data_bank', $data_bank);
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
				if ($tipe == 'kasbon') {
					$this->All_model->dataUpdate(DBCNL . '.kons_tr_kasbon_project_header', array('sts_req_payment' => 1), array('id' => $no_doc));
				}
				if ($tipe == 'expense') {
					$this->db->update(DBCNL . '.kons_tr_expense_report_project_header', array('sts_req_payment' => 1), array('id' => $no_doc));
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
		$data = $this->Approval_request_payment_model->GetListDataApproval('status <> 2');

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from(DBCNL . '.tr_invoice_po');
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
		$data = $this->Approval_request_payment_model->GetListDataApproval('a.status <> 2 AND a.app_checker IS NULL');

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from(DBCNL . '.tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$this->template->set('tingkat_approval', 1);
		$this->template->set('data', $data);
		// $this->template->set('list_no_invoice', $list_no_invoice);
		$this->template->title('Request Payment Approval Finance');
		$this->template->render('list_approve_checker');
	}

	public function list_approve_management()
	{
		$data = $this->Approval_request_payment_model->GetListDataApproval('a.status <> 2 AND a.app_checker = 1');

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from(DBCNL . '.tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$this->template->set('tingkat_approval', 2);
		$this->template->set('data', $data);
		$this->template->set('list_no_invoice', $list_no_invoice);
		$this->template->title('Request Payment Approval Direktur');
		$this->template->render('list_approve_management');
	}

	/* 
	##########
	# Updated by Hikmat A.R 15-08-2022
	##########
	*/

	public function approval_payment()
	{
		$id = '';
		if (isset($_GET['id_cons'])) {
			$id = urldecode($this->input->get('id_cons'));
			$id = str_replace('|', '/', $id);
		}
		if (isset($_GET['id_exp_consultant'])) {
			$id = urldecode($this->input->get('id_exp_consultant'));
			$id = str_replace('|', '/', $id);
		}

		$id_expense = (isset($_GET['id_expense'])) ? $this->input->get('id_expense') : '';

		$id_sendigs = '';
		if (isset($_GET['id_sendigs'])) {
			$id_sendigs = urldecode($_GET['id_sendigs']);
			$id_sendigs = str_replace('|', '/', $id_sendigs);
		}

		$get_kasbon_header = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $id))->row();

		if (!empty($get_kasbon_header)) {
			$type = 'kasbon';
			$id_spk_penawaran = $get_kasbon_header->id_spk_penawaran;

			$get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->db->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
			$this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->db->get()->row();

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

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_subcont = $this->db->get()->result();

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_akomodasi = $this->db->get()->result();

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_others = $this->db->get()->result();

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'data_kasbon_header' => $get_kasbon_header,
				'data_kasbon_subcont' => $get_kasbon_subcont,
				'data_kasbon_akomodasi' => $get_kasbon_akomodasi,
				'data_kasbon_others' => $get_kasbon_others,
				'tipe' => $tipe
			];
		} else {
			$type = 'expense';

			$this->db->select('a.*, b.id_spk_penawaran');
			$this->db->from(DBCNL . '.kons_tr_expense_report_project_header a');
			$this->db->join(DBCNL . '.kons_tr_kasbon_project_header b', 'b.id = a.id_header');
			$this->db->where('a.id', $id);
			$get_expense = $this->db->get()->row();

			$id_spk_penawaran = $get_expense->id_spk_penawaran;

			$get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->db->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
			$this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->db->get()->row();

			$tipe = 'Expense';

			$get_expense_detail = $this->db->get_where(DBCNL . '.kons_tr_expense_report_project_detail', array('id_header_expense' => $id))->result();

			$list_detail_expense_detail = [];
			foreach ($get_expense_detail as $item_expense_detail) :
				if ($item_expense_detail->tipe == '1') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_aktifitas', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_subcont', array('id_spk_budgeting' => $item_expense_detail->id_spk_budgeting, 'id_aktifitas' => $get_spk_budgeting->id_aktifitas))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_aktifitas,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '2') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_akomodasi', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_akomodasi', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_akomodasi' => $item_expense_detail->id_akomodasi))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_item,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '3') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_others', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_others', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_others' => $item_expense_detail->id_others))->row();

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

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
			$this->db->join(DBCNL . '.kons_tr_expense_report_project_header b', 'b.id_header = a.id');
			$this->db->where('b.id', $id);
			$get_kasbon = $this->db->get()->row();

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


		if (!empty($get_kasbon_header)) {
			$get_kasbon = $this->db->get_where('tr_kasbon', array('no_kasbon_consultant' => $id))->row();
			$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $get_kasbon->no_doc))->row();
		} else {
			$get_expense = $this->db->get_where('tr_expense', ['no_expense_consultant' => $id])->row();
			$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $get_expense->no_doc))->row();
		}

		$no_doc_sendigs = $id;

		$tgl_approve_direktur = (!empty($get_request_payment)) ? $get_request_payment->created_on : '';


		$this->template->title('Approval Request Payment Direktur');
		$this->template->set($data);
		$this->template->set('no_doc_sendigs', $no_doc_sendigs);
		$this->template->set('id_sendigs', $id_sendigs);
		$this->template->set('id_expense', $id_expense);
		$this->template->set('tgl_approve_direktur', $tgl_approve_direktur);

		$this->template->render('detail_approve');
	}

	public function approval_payment_checker()
	{
		$id = urldecode($this->input->get('id_exp_consultant'));
		$id = str_replace('|', '/', $id);

		$id_expense = (isset($_GET['id_expense'])) ? $this->input->get('id_expense') : '';
		$id_kasbon = (isset($_GET['id_kasbon'])) ? $this->input->get('id_kasbon') : '';

		$get_kasbon_header = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $id))->row();

		if (!empty($get_kasbon_header)) {
			$id_spk_penawaran = $get_kasbon_header->id_spk_penawaran;

			$get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->db->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
			$this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->db->get()->row();

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

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_subcont = $this->db->get()->result();

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_akomodasi = $this->db->get()->result();

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_others = $this->db->get()->result();

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'data_kasbon_header' => $get_kasbon_header,
				'data_kasbon_subcont' => $get_kasbon_subcont,
				'data_kasbon_akomodasi' => $get_kasbon_akomodasi,
				'data_kasbon_others' => $get_kasbon_others,
				'tipe' => $tipe
			];
		} else {
			$this->db->select('a.*, b.id_spk_penawaran');
			$this->db->from(DBCNL . '.kons_tr_expense_report_project_header a');
			$this->db->join(DBCNL . '.kons_tr_kasbon_project_header b', 'b.id = a.id_header');
			$this->db->where('a.id', $id);
			$get_expense = $this->db->get()->row();

			$id_spk_penawaran = $get_expense->id_spk_penawaran;

			$get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->db->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
			$this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->db->get()->row();

			$tipe = 'Expense';

			$get_expense_detail = $this->db->get_where(DBCNL . '.kons_tr_expense_report_project_detail', array('id_header_expense' => $id))->result();

			$list_detail_expense_detail = [];
			foreach ($get_expense_detail as $item_expense_detail) :
				if ($item_expense_detail->tipe == '1') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_aktifitas', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_subcont', array('id_spk_budgeting' => $item_expense_detail->id_spk_budgeting, 'id_aktifitas' => $get_spk_budgeting->id_aktifitas))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_aktifitas,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '2') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_akomodasi', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_akomodasi', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_akomodasi' => $item_expense_detail->id_akomodasi))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_item,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '3') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_others', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_others', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_others' => $item_expense_detail->id_others))->row();

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

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
			$this->db->join(DBCNL . '.kons_tr_expense_report_project_header b', 'b.id_header = a.id');
			$this->db->where('b.id', $id);
			$get_kasbon = $this->db->get()->row();

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
		$no_doc_sendigs = $id;

		if (!empty($get_kasbon_header)) {
			$get_kasbon = $this->db->get_where('tr_kasbon', array('no_kasbon_consultant' => $id))->row();
			$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $get_kasbon->no_doc))->row();
		} else {
			$get_expense = $this->db->get_where('tr_expense', ['no_expense_consultant' => $id])->row();
			$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $get_expense->no_doc))->row();
		}

		$tgl_approve_direktur = (!empty($get_request_payment)) ? $get_request_payment->created_on : '';


		$this->template->title('Approval Request Payment Finance');
		$this->template->set($data);
		$this->template->set('no_doc_sendigs', $no_doc_sendigs);
		$this->template->set('tgl_approve_direktur', $tgl_approve_direktur);
		$this->template->set('id_expense', $id_expense);
		$this->template->set('id_kasbon', $id_kasbon);
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

	public function save_approval()
	{

		$id = $this->input->post('id');

		$header 	= $this->db->get_where('request_payment', ['no_doc' => $id])->row_array();
		// $Id 		= $this->_getIdPayment(str_replace('/', '-', $Data['date']));

		$no_coa_bank = explode(' - ', $header['bank_name']);
		$no_coa_bank = $no_coa_bank[0];

		$kode_bank = '';
		$get_kode_bank = $this->db->get_where(DBCNL . '.coa_master', ['no_perkiraan' => $no_coa_bank])->row();
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
			$dtl 				= $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', ['id' => $id])->row();

			$ArrDetail[] 		= [
				'id' 			=> $id_detail,
				'payment_id' 	=> $Id,
				'no_doc' 		=> $dtl->id,
				'tgl_doc' 		=> $dtl->tgl,
				'deskripsi' 	=> $dtl->deskripsi,
				'qty' 			=> '1',
				'total' 		=> $dtl->grand_total,
				'harga' 		=> $dtl->grand_total,
				'keterangan' 	=> $dtl->deskripsi,
				'doc_file' 		=> $dtl->dokument_link,
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
			$exist_data = $this->db->get_where(DBCNL . '.payment_approve', ['id' => $id, 'tipe' => $tipe])->num_rows();
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

	public function save_approval_checker()
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
		}
		if ($tipe == "kasbon") {
			$this->db->update(DBCNL . '.kons_tr_kasbon_project_header a', array('sts_reject' => null, 'sts_reject_manage' => null, 'reject_reason' => null), array('id' => $post['id']));
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

	public function list_payment()
	{
		$data_coa = $this->All_model->GetCoaCombo(5, 'a.no_perkiraan LIKE "%1101-02%" AND (a.kode_bank IS NOT NULL AND a.kode_bank <> "")');
		$results = $this->Approval_request_payment_model->GetListDataPayment('status IS NOT NULL');
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
						// $rec = $this->db->get_where(DBCNL.'.payment_approve_details', ['payment_id' => $val])->result();
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
		$results = $this->Approval_request_payment_model->GetListDataJurnal();
		$this->template->set('results', $results);
		$this->template->title('Payment Jurnal');
		$this->template->render('list_jurnal');
	}
	public function view_jurnal($id)
	{
		$data = $this->db->query("select * from jurnal where nomor='" . $id . "' order by kredit,debet,no_perkiraan")->result();
		$data_coa = $this->All_model->GetCoaCombo();
		$results = $this->Approval_request_payment_model->GetListDataPayment('status=1');
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
		$results = $this->Approval_request_payment_model->GetListDataPayment('status=1');
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
		// $this->load->view('Approval_request_payment/list_return', $data);

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

		$get_req_payment = $this->db->get_where('request_payment', ['no_doc' => $post['id']])->row_array();
		if ($post['tingkat_approval'] == '1') {
			if ($get_req_payment['tipe'] == 'kasbon') {
				$this->db->update(DBCNL . '.kons_tr_kasbon_project_header', array('sts_req_payment' => null, 'sts_reject' => 1, 'sts_reject_manage' => null, 'reject_reason' => $post['reject_reason'], 'sts' => null), array('id' => $post['id']));

				$this->db->update(DBCNL . '.kons_tr_kasbon_project_subcont', array('sts' => null), array('id_header' => $post['id']));
				$this->db->update(DBCNL . '.kons_tr_kasbon_project_akomodasi', array('sts' => null), array('id_header' => $post['id']));
				$this->db->update(DBCNL . '.kons_tr_kasbon_project_others', array('sts' => null), array('id_header' => $post['id']));
			}
			if ($get_req_payment['tipe'] == 'expense') {
				$this->db->update('tr_expense', ['status' => 1, 'sts_reject' => 1, 'sts_reject_manage' => 0, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['id']]);

				$this->db->update('tr_expense_detail', ['req_payment' => 0], ['no_doc' => $post['id']]);
			}
		} else {
			if ($get_req_payment['tipe'] == 'kasbon') {
				$this->db->update('tr_kasbon', ['status' => 1, 'sts_reject_manage' => 1, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);
			}
			if ($get_req_payment['tipe'] == 'expense') {
				$this->db->update('tr_expense', ['status' => 1, 'sts_reject_manage' => 1, 'reject_reason' => $post['reject_reason']], ['no_doc' => $post['no_doc']]);

				$this->db->update('tr_expense_detail', ['req_payment' => 0], ['no_doc' => $post['no_doc']]);
			}
		}

		$this->db->delete('request_payment', ['id' => $get_req_payment['id']]);

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

		$this->Approval_request_payment_model->search_payment_list($tgl_from, $tgl_to, $bank);
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
			$get_nm_vendor = $this->db->get_where(DBCNL . '.new_supplier', ['kode_supplier' => $vendor])->row();
			$nm_vendor = $get_nm_vendor->nama;
		}

		$data = $this->Approval_request_payment_model->GetListDataRequest($actived_tab, $from_date, $to_date);

		$list_curr = $this->db->get('mata_uang')->result();

		$this->db->select('a.*');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->where('a.no_perkiraan LIKE', '%1101%');
		$list_coa = $this->db->get()->result();

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from(DBCNL . '.tr_invoice_po');
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
				$get_expense = $this->db->get_where(DBCNL . '.tr_expense', ['no_doc' => $record->no_doc])->row_array();
				if ($get_expense['exp_inv_po'] == '1') {
					$tipe = 'Pembayaran PO';

					$get_inv = $this->db->get_where(DBCNL . '.tr_invoice_po', ['id' => $record->no_doc])->row_array();
					$currency = $get_inv['curr'];
				}
			}

			$nm_supplier = '';

			$get_ros = $this->db->select('a.nm_supplier')->get_where(DBCNL . '.tr_ros a', ['a.id' => $record->no_doc])->row();
			if (!empty($get_ros)) {
				$nm_supplier = $get_ros->nm_supplier;
			}

			$get_invoice = $this->db->select('a.no_po')
				->from(DBCNL . '.tr_invoice_po a')
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
					$get_expense = $this->db->get_where(DBCNL . '.tr_expense', ['id' => $record->ids])->row_array();
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
								<option value="1">PPh 23</option>
								<option value="2">PPh 22</option>
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
		$data = $this->Approval_request_payment_model->GetListDataRequest($tab);

		$list_curr = $this->db->get_where(DBCNL . '.mata_uang', ['deleted' => null])->result();
		$list_coa = $this->db->get_where(DBCNL . '.coa_master', ['kode_bank <>' => null])->result();

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from(DBCNL . '.tr_invoice_po');
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

			$no_invoice = (isset($list_no_invoice[$record->id])) ? $list_no_invoice[$record->id] : '';

			$tipe = $record->tipe;

			$currency = 'IDR';
			// if ($record->tipe == 'expense') {
			// 	$get_expense = $this->db->get_where(DBCNL.'.tr_expense', ['no_doc' => $record->no_doc])->row_array();
			// 	if ($get_expense['exp_inv_po'] == '1') {
			// 		$tipe = 'Pembayaran PO';

			// 		$get_inv = $this->db->get_where(DBCNL.'.tr_invoice_po', ['id' => $record->no_doc])->row_array();
			// 		$currency = $get_inv['curr'];
			// 	}
			// }

			$nm_supplier = '';

			// $get_ros = $this->db->select('a.nm_supplier')->get_where(DBCNL.'.tr_ros a', ['a.id' => $record->no_doc])->row();
			// if (!empty($get_ros)) {
			// 	$nm_supplier = $get_ros->nm_supplier;
			// }


			// if ($tab == 'expense') {
			// 	if (strpos($record->no_doc, 'ER-') !== false || strpos($record->no_doc, 'ROS') !== false) {
			// 		$valid = 1;
			// 	} else {
			// 		$valid = 0;
			// 	}
			// } else {
			// 	$valid = 1;
			// }

			$valid = 1;

			if ($valid == 1) {
				$hasil .= '<tr>';
				$hasil .= '<td class="exclass">';
				if ($ENABLE_MANAGE) {
					$hasil .= '<input type="hidden" name="no_doc_' . $numb . '" id="no_doc_' . $numb . '" value="' . $record->id . '">';
					$hasil .= '<input type="hidden" name="nama_' . $numb . '" id="nama_' . $numb . '" value="' . $record->nama . '">';
					$hasil .= '<input type="hidden" name="tgl_doc_' . $numb . '" id="tgl_doc_' . $numb . '" value="' . $record->tgl . '">';
					$hasil .= '<input type="hidden" name="keperluan_' . $numb . '" id="keperluan_' . $numb . '" value="' . $record->deskripsi . '">';
					$hasil .= '<input type="hidden" name="tipe_' . $numb . '" id="tipe_' . $numb . '" value="' . $record->tipe . '">';
					$hasil .= '<input type="hidden" name="jumlah_' . $numb . '" id="jumlah_' . $numb . '" value="' . $record->grand_total . '">';
					$hasil .= '<input type="hidden" name="bank_id_' . $numb . '" id="bank_id_' . $numb . '" value="' . $record->bank . '">';
					$hasil .= '<input type="hidden" name="accnumber_' . $numb . '" id="accnumber_' . $numb . '" value="' . $record->bank_number . '">';
					$hasil .= '<input type="hidden" name="accname_' . $numb . '" id="accname_' . $numb . '" value="' . $record->bank_account . '">';
					$hasil .= '<input type="hidden" name="ids_' . $numb . '" id="ids_' . $numb . '" value="' . $record->id . '">';
					$hasil .= '<input type="checkbox" name="status[]" id="status_' . $numb . '" value="' . $numb . '" class="dtlloop" onclick="cektotal()">';
				}
				if ($record->tipe == 'kasbon') {
					$link_kasbon_view = '';
					if ($record->tipee == '1') {
						$link_kasbon_view = base_url('kasbon_project/view_kasbon_subcont/' . urlencode(str_replace('/', '|', $record->id)));
					}
					if ($record->tipee == '2') {
						$link_kasbon_view = base_url('kasbon_project/view_kasbon_akomodasi/' . urlencode(str_replace('/', '|', $record->id)));
					}
					if ($record->tipee == '3') {
						$link_kasbon_view = base_url('kasbon_project/view_kasbon_others/' . urlencode(str_replace('/', '|', $record->id)));
					}
					$hasil .= '<a href="' . $link_kasbon_view . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'transportasi') {
					$hasil .= '<a href="' . base_url('expense/transport_req_view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'expense') {
					$hasil .= '<a href="' . base_url('expense_report_project/view_expense_subcont/' . urlencode(str_replace('/', '|', $record->id))) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'nonpo') {
					$hasil .= '<a href="' . base_url('purchase_order/non_po/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}
				if ($record->tipe == 'periodiks') {
					$hasil .= '<a href="' . base_url('pembayaran_rutin/view/' . $record->ids) . '" target="_blank"><i class="fa fa-search pull-right"></i></a>';
				}

				$curr = 'IDR';
				// $get_curr = $this->db->get_where(DBCNL.'.tr_invoice_po', ['id' => $record->no_doc])->row();
				// if (!empty($get_curr)) {
				// 	$curr = $get_curr->curr;
				// }

				$hasil .= '</td>';
				$hasil .= '<td class="">' . $numb . '</td>';
				$hasil .= '<td>' . $record->id . '</td>';
				$hasil .= '<td>' . date('d F Y', strtotime($record->tgl)) . '</td>';
				$hasil .= '<td>' . $record->deskripsi . '</td>';
				$hasil .= '<td>' . number_format($record->grand_total) . '</td>';
				$hasil .= '<td>' . $sts . '</td>';
				$hasil .= '<td>';
				$hasil .= '
				<input type="hidden" name="currency_' . $numb . '" value="IDR" readonly>
				<table class="w-100" border="0" style="border: 0px !important;">
					<tr>
						<td>Nilai Pengajuan</td>
						<td class="text-center">:</td>
						<td>
							<input type="text" name="" id="" class="form-control form-control-sm text-right nilai_pengajuan_' . $numb . '" value="' . number_format($record->grand_total) . '" readonly>
						</td>
					</tr>
					<tr>
						<td>
							<select name="tipe_pph_' . $numb . '" id="" class="form-control form-control-sm select_pph_' . $numb . '">
								<option value="">- Select PPh -</option>
								<option value="1">PPh 23</option>
								<option value="2">PPh 22</option>
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

		$this->Approval_request_payment_model->excel_payment_list($tgl_from, $tgl_to, $bank);
	}

	public function view_receive_invoice()
	{
		$id_invoice = $this->input->post('id_invoice');

		$get_invoice = $this->db->get_where(DBCNL . '.tr_invoice_po', ['id' => $id_invoice])->row_array();
		if ($get_invoice['id_top'] !== null) {
			$get_top_invoice = $this->db->get_where(DBCNL . '.tr_top_po', ['id' => $get_invoice['id_top']])->row_array();

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

	public function get_payment_list()
	{
		$this->Approval_request_payment_model->get_payment_list();
	}

	public function export_excel_kasbon_checker()
	{
		$tingkat = $this->input->get('tingkat');

		if ($tingkat !== '1') {
			$this->db->select('a.*');
			$this->db->from('request_payment a');
			$this->db->join('tr_kasbon b', 'b.no_doc = a.no_doc');
			$this->db->where('a.status <>', 2);
			$this->db->where('a.app_checker', 1);
			$this->db->where('b.no_kasbon_consultant <>', null);
			$this->db->order_by('a.tanggal', 'asc');
			$this->db->order_by('a.tipe', 'desc');
			$this->db->order_by('a.id', 'desc');
			$this->db->group_by('a.no_doc');
			$data = $this->db->get()->result();
		} else {
			$this->db->select('a.*');
			$this->db->from('request_payment a');
			$this->db->join('tr_kasbon b', 'b.no_doc = a.no_doc');
			$this->db->where('a.status <>', 2);
			$this->db->where('a.app_checker IS NULL');
			$this->db->where('b.no_kasbon_consultant <>', null);
			$this->db->order_by('a.tanggal', 'asc');
			$this->db->order_by('a.tipe', 'desc');
			$this->db->order_by('a.id', 'desc');
			$this->db->group_by('a.no_doc');
			$data = $this->db->get()->result();
		}

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from('tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$this->load->view('excel_kasbon_app', array('data' => $data, 'tingkat' => $tingkat));
	}

	public function export_excel_expense_checker()
	{
		$tingkat = $this->input->get('tingkat');

		if ($tingkat !== '1') {
			$data = $this->Approval_request_payment_model->GetListDataApproval('a.status <> 2 AND a.app_checker = 1');
		} else {
			$data = $this->Approval_request_payment_model->GetListDataApproval('a.status <> 2 AND a.app_checker IS NULL');
		}

		$list_no_invoice = [];
		$this->db->select('id, invoice_no');
		$this->db->from(DBCNL . '.tr_invoice_po');
		$get_invoice_no = $this->db->get()->result();
		foreach ($get_invoice_no as $item_no_invoice) {
			$list_no_invoice[$item_no_invoice->id] = $item_no_invoice->invoice_no;
		}

		$this->load->view('excel_expense_app', array('data' => $data, 'tingkat' => $tingkat));
	}

	public function print_kasbon($id)
	{
		$id = urldecode($id);
		$id = str_replace('|', '/', $id);


		$get_kasbon_header = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $id))->row();

		if (!empty($get_kasbon_header)) {
			$id_spk_penawaran = $get_kasbon_header->id_spk_penawaran;

			$get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->db->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
			$this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->db->get()->row();

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

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_subcont = $this->db->get()->result();

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_akomodasi = $this->db->get()->result();

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
			$this->db->where('a.id_header', $id);
			$get_kasbon_others = $this->db->get()->result();

			$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $id))->row();

			$tgl_approve_direktur = (!empty($get_request_payment)) ? $get_request_payment->created_on : '';

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'data_kasbon_header' => $get_kasbon_header,
				'data_kasbon_subcont' => $get_kasbon_subcont,
				'data_kasbon_akomodasi' => $get_kasbon_akomodasi,
				'data_kasbon_others' => $get_kasbon_others,
				'tipe' => $tipe,
				'tgl_approve_direktur' => $tgl_approve_direktur
			];
		} else {
			$this->db->select('a.*, b.id_spk_penawaran');
			$this->db->from(DBCNL . '.kons_tr_expense_report_project_header a');
			$this->db->join(DBCNL . '.kons_tr_kasbon_project_header b', 'b.id = a.id_header');
			$this->db->where('a.id', $id);
			$get_expense = $this->db->get()->row();

			$id_spk_penawaran = $get_expense->id_spk_penawaran;

			$get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

			$this->db->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
			$this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
			$this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
			$this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
			$get_spk_penawaran = $this->db->get()->row();

			$tipe = 'Expense';

			$get_expense_detail = $this->db->get_where(DBCNL . '.kons_tr_expense_report_project_detail', array('id_header_expense' => $id))->result();

			$list_detail_expense_detail = [];
			foreach ($get_expense_detail as $item_expense_detail) :
				if ($item_expense_detail->tipe == '1') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_aktifitas', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_subcont', array('id_spk_budgeting' => $item_expense_detail->id_spk_budgeting, 'id_aktifitas' => $get_spk_budgeting->id_aktifitas))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_aktifitas,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '2') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_akomodasi', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_akomodasi', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_akomodasi' => $item_expense_detail->id_akomodasi))->row();

					$list_detail_expense_detail[$item_expense_detail->id] = [
						'nama_expense' => $get_spk_budgeting->nm_item,
						'qty_kasbon' => $get_kasbon->qty_pengajuan,
						'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
						'qty_expense' => $item_expense_detail->qty_expense,
						'nominal_expense' => $item_expense_detail->nominal_expense
					];
				}
				if ($item_expense_detail->tipe == '3') {
					$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_others', array('id' => $item_expense_detail->id_detail_kasbon))->row();
					$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_others', array('id_spk_budgeting' => $get_spk_budgeting->id_spk_budgeting, 'id_others' => $item_expense_detail->id_others))->row();

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

			$this->db->select('a.*');
			$this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
			$this->db->join(DBCNL . '.kons_tr_expense_report_project_header b', 'b.id_header = a.id');
			$this->db->where('b.id', $id);
			$get_kasbon = $this->db->get()->row();

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

		$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $id))->row();

		$today = date('l, d F Y [H:i:s]');

		// $this->load->library(array('Mpdf'));
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4',
			'margin_top' => 10,
			'margin_bottom' => 10,
			'margin_left' => 15,
			'margin_right' => 15
		]);
		// $mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('print_kasbon', $data);
		// $mpdf->AddPage();
		$footer = 'Printed by : ' . ucfirst(strtolower($this->auth->user_name())) . ', ' . $today . ' / ' . $id . '';
		// $mpdf->SetWatermarkText('ORI Group');
		$mpdf->showWatermarkText = true;
		$mpdf->SetTitle($id . "/" . date('ymdhis'));
		$mpdf->AddPage();
		$mpdf->SetFooter($footer);
		$mpdf->WriteHTML($show);
		$mpdf->Output(' ' . $id . '/' . date('ymdhis') . '.pdf', \Mpdf\Output\Destination::INLINE);
	}

	public function print_expense($id)
	{
		$id = urldecode($id);
		$id = str_replace('|', '/', $id);

		$this->db->select('a.*, b.id_spk_penawaran');
		$this->db->from(DBCNL . '.kons_tr_expense_report_project_header a');
		$this->db->join(DBCNL . '.kons_tr_kasbon_project_header b', 'b.id = a.id_header');
		$this->db->where('a.id', $id);
		$get_expense = $this->db->get()->row();

		$id_spk_penawaran = $get_expense->id_spk_penawaran;

		$get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', array('id_spk_penawaran' => $id_spk_penawaran))->row();

		$this->db->select('a.id_spk_penawaran, a.nm_project_leader, a.nm_sales, a.nm_customer, a.waktu_from, a.waktu_to, a.address as alamat, b.nm_paket');
		$this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
		$this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
		$this->db->where('a.id_spk_penawaran', $id_spk_penawaran);
		$get_spk_penawaran = $this->db->get()->row();

		$tipe = 'Expense';

		$get_expense_detail = $this->db->get_where(DBCNL . '.kons_tr_expense_report_project_detail', array('id_header_expense' => $id))->result();

		$list_detail_expense_detail = [];
		foreach ($get_expense_detail as $item_expense_detail) :
			if ($item_expense_detail->tipe == '1') {
				$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_aktifitas', array('id' => $item_expense_detail->id_detail_kasbon))->row();
				$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_subcont', array('id_header' => $item_expense_detail->id_header_kasbon, 'id_aktifitas' => $get_spk_budgeting->id_aktifitas))->row();

				$list_detail_expense_detail[$item_expense_detail->id] = [
					'nama_expense' => $get_spk_budgeting->nm_aktifitas,
					'qty_kasbon' => $get_kasbon->qty_pengajuan,
					'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
					'qty_expense' => $item_expense_detail->qty_expense,
					'nominal_expense' => $item_expense_detail->nominal_expense
				];
			}
			if ($item_expense_detail->tipe == '2') {
				$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_akomodasi', array('id' => $item_expense_detail->id_detail_kasbon))->row();
				$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_akomodasi', array('id_header' => $item_expense_detail->id_header_kasbon, 'id_akomodasi' => $item_expense_detail->id_akomodasi))->row();

				$list_detail_expense_detail[$item_expense_detail->id] = [
					'nama_expense' => $get_spk_budgeting->nm_item,
					'qty_kasbon' => $get_kasbon->qty_pengajuan,
					'nominal_kasbon' => $get_kasbon->nominal_pengajuan,
					'qty_expense' => $item_expense_detail->qty_expense,
					'nominal_expense' => $item_expense_detail->nominal_expense
				];
			}
			if ($item_expense_detail->tipe == '3') {
				$get_spk_budgeting = $this->db->get_where(DBCNL . '.kons_tr_spk_budgeting_others', array('id' => $item_expense_detail->id_detail_kasbon))->row();
				$get_kasbon = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_others', array('id_header' => $item_expense_detail->id_header_kasbon, 'id_others' => $get_spk_budgeting->id_others))->row();

				$list_detail_expense_detail[$item_expense_detail->id] = [
					'nama_expense' => $get_kasbon->nm_item,
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

		$this->db->select('a.*');
		$this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
		$this->db->join(DBCNL . '.kons_tr_expense_report_project_header b', 'b.id_header = a.id');
		$this->db->where('b.id', $id);
		$get_kasbon = $this->db->get()->row();

		$get_request_payment = $this->db->get_where('request_payment', array('no_doc' => $id))->row();


		if (empty($get_request_payment)) {
			$get_bukti_penggunaan = $this->db->get_where(DBCNL . '.kons_tr_bukti_penggunaan_expense', array('id_header_expense' => $id))->result();

			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'list_expense_detail' => $get_expense_detail,
				'data_kasbon_header' => $get_kasbon,
				'tipe' => $tipe,
				'title_expense' => $title_expense,
				'list_detail_expense_detail' => $list_detail_expense_detail,
				'list_bukti_penggunaan' => $get_bukti_penggunaan
			];
		} else {
			$data = [
				'id' => $id,
				'id_spk_penawaran' => $id_spk_penawaran,
				'data_spk_penawaran' => $get_spk_penawaran,
				'list_expense_detail' => $get_expense_detail,
				'data_kasbon_header' => $get_kasbon,
				'tipe' => $tipe,
				'title_expense' => $title_expense,
				'list_detail_expense_detail' => $list_detail_expense_detail,
				'tgl_approve_direktur' => $get_request_payment->created_on
			];
			$this->template->set('tgl_approve_direktur', $get_request_payment->created_on);
		}

		$today = date('l, d F Y [H:i:s]');

		// $this->load->library(array('Mpdf'));
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4',
			'margin_top' => 10,
			'margin_bottom' => 10,
			'margin_left' => 15,
			'margin_right' => 15
		]);
		// $mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		// $show = $this->load->view('print_expense', $data);
		$show = $this->template->load_view('print_expense', $data);
		// $mpdf->AddPage('L', 'A4', 'en');
		$footer = 'Printed by : ' . ucfirst(strtolower($this->auth->user_name())) . ', ' . $today . ' / ' . $id . '';
		// $mpdf->SetWatermarkText('ORI Group');
		$mpdf->showWatermarkText = true;
		$mpdf->SetTitle($id . "/" . date('ymdhis'));
		$mpdf->AddPage();
		$mpdf->SetFooter($footer);
		$mpdf->WriteHTML($show);
		$mpdf->Output(' ' . $id . '/' . date('ymdhis') . '.pdf', \Mpdf\Output\Destination::INLINE);
	}
}
