<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Metode_pembelian extends Admin_Controller
{
	protected $viewPermission     = 'Metode_Pembelian.View';
	protected $addPermission      = 'Metode_Pembelian.Add';
	protected $managePermission = 'Metode_Pembelian.Manage';
	protected $deletePermission = 'Metode_Pembelian.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'Metode_pembelian/master_model',
			'Metode_pembelian/Metode_pembelian_model',
			'Metode_pembelian/Jurnal_model',
			'Metode_pembelian/All_model'
		));
	}

	//==================================================================================================================
	//=============================================PURCHASE REQUEST=====================================================
	//==================================================================================================================

	public function pr()
	{
		$this->auth->restrict($this->viewPermission);
		$this->Metode_pembelian_model->index_pr();
	}

	public function server_side_progress_pr()
	{
		// $this->Metode_pembelian_model->get_data_json_progress_pr();
		$this->Metode_pembelian_model->get_data_json_progress_pr_new();
	}

	public function modal_detail_pr()
	{
		$no_pr_group    = $this->input->post('no_pr_group');
		$tipe_pr = $this->input->post('tipe_pr');
		$result			= $this->db->get_where('tran_pr_detail', array('no_pr_group' => $no_pr_group))->result_array();

		if ($tipe_pr == 'Department') {
			$this->db->select('a.nm_barang as nama_material, a.qty as qty, a.tanggal as tgl_dibutuhkan');
			$this->db->from('rutin_non_planning_detail a');
			$this->db->where('a.no_pr', $no_pr_group);
			$result = $this->db->get()->result_array();
		} else {
			$this->db->select('IF(b.nama IS NULL, c.stock_name, b.nama) AS nama_material, a.propose_purchase as qty, d.tgl_dibutuhkan as tgl_dibutuhkan');
			$this->db->from('material_planning_base_on_produksi_detail a');
			$this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_material', 'left');
			$this->db->join('accessories c', 'c.id = a.id_material', 'left');
			$this->db->join('material_planning_base_on_produksi d', 'd.so_number = a.so_number', 'left');
			$this->db->where('d.no_pr', $no_pr_group);
			$result = $this->db->get()->result_array();
		}

		// print_r($no_pr_group.' - '.$tipe_pr);
		// exit;

		$data = array(
			'result' 	=> $result
		);

		$this->template->set($data);
		$this->template->render('modal_detail_pr');
	}

	//==================================================================================================================
	//=============================================REQUEST FOR QUOTATION================================================
	//==================================================================================================================

	public function rfq()
	{
		$this->Metode_pembelian_model->index_rfq();
	}

	public function server_side_rfq()
	{
		$this->Metode_pembelian_model->get_data_json_rfq();
	}

	public function add_rfq()
	{
		$this->Metode_pembelian_model->add_rfq();
	}

	public function server_side_list_pr()
	{
		$this->Metode_pembelian_model->get_data_json_list_pr();
	}

	public function save_rfq()
	{
		$this->Metode_pembelian_model->save_rfq();
	}

	public function modal_detail_rfq()
	{
		$this->Metode_pembelian_model->modal_detail_rfq();
	}

	public function modal_edit_rfq()
	{
		$this->Metode_pembelian_model->modal_edit_rfq();
	}

	public function modal_edit_rfq_print()
	{
		$this->Metode_pembelian_model->modal_edit_rfq_print();
	}

	public function update_rfq()
	{
		$this->Metode_pembelian_model->update_rfq();
	}

	public function cancel_sebagian_rfq()
	{
		$this->Metode_pembelian_model->cancel_sebagian_rfq();
	}

	public function print_rfq()
	{
		$this->Metode_pembelian_model->print_rfq();
	}

	public function hapus_rfq()
	{
		$this->Metode_pembelian_model->hapus_rfq();
	}

	public function save_checked_rfq()
	{
		$data = $this->input->post();

		$id 		= $data['id'];
		$flag 		= $data['flag'];

		$checklist = NULL;
		$Hist = 'Uncheck ';
		if ($flag == '1') {
			$checklist = 1;
			$Hist = 'Check ';
		}

		$ArrUpdate = [
			'checklist' => $checklist,
			'checklist_by' => $this->session->userdata['ORI_User']['username']
		];

		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('tran_pr_detail', $ArrUpdate);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1
			);
			history($Hist . ' PR for RFQ : ' . $id);
		}
		echo json_encode($Arr_Data);
	}

	public function changeSpec()
	{
		$data 		= $this->input->post();

		$no_pr 		= $data['no_pr'];
		$no_rfq 	= $data['no_rfq'];
		$spec 		= $data['spec'];

		$ArrUpdate = [
			'spec' => $spec
		];

		$this->db->trans_start();
		$this->db->where('no_pr', $no_pr);
		$this->db->update('tran_pr_detail', $ArrUpdate);

		$this->db->where('no_pr', $no_pr);
		$this->db->where('no_rfq', $no_rfq);
		$this->db->update('tran_rfq_detail', $ArrUpdate);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'status'	=> 1
			);
			history('Change spec : ' . $no_pr . '/' . $no_rfq);
		}
		echo json_encode($Arr_Data);
	}

	//==================================================================================================================
	//======================================================PERBANDINGAN================================================
	//==================================================================================================================

	public function perbandingan()
	{
		$this->Metode_pembelian_model->index_perbandingan();
	}

	public function server_side_perbandingan()
	{
		$this->Metode_pembelian_model->get_data_json_perbandingan();
	}

	public function add_perbandingan()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$no_rfq 		= $data['no_rfq'];
			$header 		= $data['Header'];
			$detail 		= $data['Detail'];
			// print_r($data);

			$ArrHeader = array();
			$ArrDetail = array();
			foreach ($header as $val => $valx) {
				$ArrHeader[$val]['id'] 				= $valx['id'];
				$ArrHeader[$val]['lokasi'] 			= $valx['lokasi'];
				$ArrHeader[$val]['alamat_supplier'] = $valx['alamat'];
				$ArrHeader[$val]['currency'] 		= $valx['currency'];
				$ArrHeader[$val]['keterangan'] 		= $valx['keterangan'];
				$ArrHeader[$val]['kurs'] 			= str_replace(',', '', $valx['kurs']);
				$ArrHeader[$val]['sts_ajuan'] 		= 'PRS';
				$ArrHeader[$val]['sts_process'] 	= 'Y';
				$ArrHeader[$val]['updated_by'] 		= $data_session['ORI_User']['username'];
				$ArrHeader[$val]['updated_date'] 	= date('Y-m-d H:i:s');
			}

			foreach ($detail as $val => $valx) {
				foreach ($valx['detail'] as $val2 => $valx2) {
					$ArrDetail[$val . $val2]['id'] 				= $valx2['id'];
					$ArrDetail[$val . $val2]['price_ref_sup'] 	= str_replace(',', '', $valx2['price_ref_sup']);
					$ArrDetail[$val . $val2]['qty'] 				= str_replace(',', '', $valx2['qty']);
					$ArrDetail[$val . $val2]['moq'] 				= str_replace(',', '', $valx2['moq']);
					$ArrDetail[$val . $val2]['lead_time'] 		= str_replace(',', '', $valx2['lead_time']);
					$ArrDetail[$val . $val2]['price_ref'] 		= str_replace(',', '', $valx2['price_ref']);
					$ArrDetail[$val . $val2]['harga_idr'] 		= str_replace(',', '', $valx2['harga_idr']);
					$ArrDetail[$val . $val2]['total_harga'] 		= str_replace(',', '', $valx2['total_harga']);
					$ArrDetail[$val . $val2]['tgl_dibutuhkan'] 	= $valx2['tgl_dibutuhkan'];
					$ArrDetail[$val . $val2]['top'] 				= strtolower($valx2['top']);
					$ArrDetail[$val . $val2]['keterangan'] 		= strtolower($valx2['keterangan']);
				}
			}
			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();
			$this->db->update_batch('tran_rfq_header', $ArrHeader, 'id');
			$this->db->update_batch('tran_rfq_detail', $ArrDetail, 'id');

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Insert data failed. Please try again later ...',
					'status'	=> 2
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Insert data success. Thanks ...',
					'status'	=> 1
				);
				history('Create Table Perbandingan ' . $no_rfq);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$no_rfq = $this->uri->segment(3);
			$result = $this->db->order_by('id', 'ASC')->get_where('tran_rfq_header', array('no_rfq' => $no_rfq))->result_array();
			$currency = $this->db->get_where('currency', array('flag' => 1))->result_array();
			$data = array(
				'title'			=> 'Add Table Perbandingan',
				'action'		=> 'index',
				'currency' 		=> $currency,
				'result' 		=> $result
			);
			$this->load->view('Metode_pembelian/add_perbandingan', $data);
		}
	}

	public function modal_detail_perbandingan()
	{
		//		$this->Metode_pembelian_model->modal_detail_perbandingan();
		$this->Metode_pembelian_model->modal_detail_perbandingan_new();
	}
	public function pengajuan_rfq()
	{
		$this->Metode_pembelian_model->pengajuan_rfq();
	}

	//==================================================================================================================
	//======================================================PENGAJUAN ==================================================
	//==================================================================================================================

	public function pengajuan()
	{
		$this->Metode_pembelian_model->index_pengajuan();
	}

	public function server_side_pengajuan()
	{
		$this->Metode_pembelian_model->get_data_json_pengajuan();
	}

	public function modal_detail_pengajuan()
	{
		$this->Metode_pembelian_model->modal_detail_pengajuan();
	}

	public function modal_pemilihan()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$no_rfq 		= $data['no_rfq'];
			$alasan_pemilihan 	= $data['alasan_pemilihan'];
			$team_seleksi 		= $data['team_seleksi'];
			// print_r($data);

			if (!empty($data['check'])) {
				$detail 		= $data['check'];
			}
			$username 		= $data_session['ORI_User']['username'];
			$datetime 		= date('Y-m-d H:i:s');
			// print_r($data);

			$ArrDetail = array();
			if (!empty($data['check'])) {
				foreach ($detail as $val) {
					$ArrDetail[$val]['id'] 			= $val;
					$ArrDetail[$val]['status'] 		= 'SETUJU';
					$ArrDetail[$val]['setuju_by'] 	= $username;
					$ArrDetail[$val]['setuju_date'] = $datetime;
				}
			}

			$ArrHeader = array(
				'sts_ajuan' => 'APV',
				'alasan_pemilihan' => $alasan_pemilihan,
				'team_seleksi' => $team_seleksi
			);

			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();
			$this->db->update_batch('tran_rfq_detail', $ArrDetail, 'id');

			$this->db->where(array('no_rfq' => $no_rfq));
			$this->db->update('tran_rfq_header', $ArrHeader);
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Insert purchase order data failed. Please try again later ...',
					'status'	=> 2
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Insert purchase order data success. Thanks ...',
					'status'	=> 1
				);
				history('Create Pemilihan Supplier ' . $no_rfq);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$no_rfq 	= $this->uri->segment(3);

			$result		= $this->db
				->select('a.*, b.currency')
				->order_by('a.nm_supplier, a.id', 'ASC')
				->join('tran_rfq_header b', 'a.hub_rfq=b.hub_rfq', 'left')
				->get_where('tran_rfq_detail a', array(
					'a.no_rfq' => $no_rfq,
					'a.deleted' => 'N'
				))
				->result_array();

			$data = array(
				'result' 	=> $result,
				'no_rfq' 	=> $no_rfq
			);

			$this->load->view('Metode_pembelian/modal_pemilihan', $data);
		}
	}

	public function modal_hasil_pengajuan()
	{
		$no_rfq 	= $this->uri->segment(3);

		$result		= $this->db
			->select('a.*, b.currency')
			->order_by('a.nm_supplier, a.id', 'ASC')
			->join('tran_rfq_header b', 'a.hub_rfq=b.hub_rfq', 'left')
			->get_where('tran_rfq_detail a', array(
				'a.no_rfq' => $no_rfq,
				'a.deleted' => 'N'
			))
			->result_array();

		$header		= $this->db->get_where('tran_rfq_header', array('no_rfq' => $no_rfq))->result_array();

		$data = array(
			'result' 	=> $result,
			'header' 	=> $header,
			'no_rfq' 	=> $no_rfq
		);

		$this->load->view('Metode_pembelian/modal_hasil_pengajuan', $data);
	}

	public function print_hasil_pemilihan()
	{
		$this->Metode_pembelian_model->print_hasil_pemilihan();
	}

	//==================================================================================================================
	//======================================================APPROVAL ===================================================
	//==================================================================================================================

	public function approval()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)) . '/' . strtolower($this->uri->segment(2)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data_gudang		= $this->db->query("SELECT * FROM warehouse WHERE `status`='Y' ")->result_array();
		$data = array(
			'title'			=> 'Approval RFQ',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'data_gudang'	=> $data_gudang
		);
		history('View approval pemilihan supplier final non material');
		$this->load->view('Metode_pembelian/approval', $data);
	}

	public function server_side_approval()
	{
		$this->Metode_pembelian_model->get_data_json_approval();
	}

	public function modal_detail_approve()
	{
		$this->Metode_pembelian_model->modal_detail_approve();
	}

	public function modal_approve()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$no_rfq 		= $data['no_rfq'];
			$category 		= $data['category'];

			if (!empty($data['check'])) {
				$detail 		= $data['check'];
			}
			$username 		= $data_session['ORI_User']['username'];
			$datetime 		= date('Y-m-d H:i:s');

			$ArrDetail = array();
			if (!empty($data['check'])) {
				foreach ($detail as $val) {
					$ArrDetail[$val]['id'] 			= $val;
					$ArrDetail[$val]['status_apv'] 		= 'SETUJU';
					$ArrDetail[$val]['close_by'] 	= $username;
					$ArrDetail[$val]['close_date'] = $datetime;
				}
			}

			$ArrUpd2 = array(
				'sts_ajuan' => 'CLS'
			);


			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();

			$this->db->update_batch('tran_rfq_detail', $ArrDetail, 'id');
			$this->db->where(array('no_rfq' => $no_rfq));
			$this->db->update('tran_rfq_header', $ArrUpd2);
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Insert data failed. Please try again later ...',
					'status'	=> 2
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Insert data success. Thanks ...',
					'status'	=> 1
				);
				history('Close RFQ ' . $no_rfq);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$no_rfq 	= $this->uri->segment(3);

			$result		= $this->db
				->select('a.*, b.currency')
				->order_by('a.nm_supplier, a.id', 'ASC')
				->join('tran_rfq_header b', 'a.hub_rfq=b.hub_rfq', 'left')
				->get_where('tran_rfq_detail a', array(
					'a.no_rfq' => $no_rfq,
					'a.deleted' => 'N'
				))
				->result_array();

			$header		= $this->db->limit(1)->get_where('tran_rfq_header', array('no_rfq' => $no_rfq))->result();

			$data = array(
				'result' 	=> $result,
				'header' 	=> $header,
				'no_rfq' 	=> $no_rfq,
				'category' 	=> $header[0]->category
			);

			$this->load->view('Metode_pembelian/modal_approve', $data);
		}
	}

	//==================================================================================================================
	//==================================================PURCHASE ORDER==================================================
	//==================================================================================================================

	public function purchase_order()
	{
		$this->Metode_pembelian_model->index_purchase_order();
	}

	public function server_side_purchase_order()
	{
		$this->Metode_pembelian_model->get_data_json_purchase_order();
	}

	public function modal_detail_purchase_order()
	{
		$no_po 	= $this->uri->segment(3);

		$get_status = $this->db->select('status')->get_where('tran_po_header', array('no_po' => $no_po))->result();

		$WHERE = [
			'a.no_po' => $no_po
		];
		if ($get_status[0]->status != 'DELETED') {
			$WHERE = [
				'a.no_po' => $no_po,
				'a.deleted' => 'N'
			];
		}

		$result		= $this->db
			->select('a.*, b.nm_supplier, b.mata_uang AS currency')
			->join('tran_po_header b', 'a.no_po=b.no_po', 'left')
			->get_where('tran_po_detail a', $WHERE)
			->result_array();

		$data = array(
			'result' => $result
		);

		$this->load->view('Metode_pembelian/modal_detail_purchase_order', $data);
	}

	public function modal_edit_purchase_order()
	{
		if ($this->input->post()) {
			$data_session	= $this->session->userdata;
			$data		= $this->input->post();
			// print_r($data);
			$no_po 		= $data['no_po'];
			$category 	= $data['category'];
			$detail 	= $data['detail'];
			if (!empty($data['detail_po'])) {
				$detail_po 	= $data['detail_po'];
			}

			$userName = $data_session['ORI_User']['username'];
			$dateTime = date('Y-m-d H:i:s');

			$REQ_DATE = (!empty($data['request_date'])) ? date('Y-m-d', strtotime($data['request_date'])) : NULL;

			$ArrHeader = array(
				'incoterms' 	=> strtolower($data['incoterms']),
				'tgl_dibutuhkan' 	=> $REQ_DATE,
				'tax' 			=> str_replace(',', '', $data['tax']),
				'pph' 			=> str_replace(',', '', $data['pph']),
				'remarks' 		=> strtolower($data['remarks']),
				'buyer' 		=> strtolower($data['buyer']),
				'top' 			=> $data['top'],
				'mata_uang' 	=> $data['current'],
				'amount_words' 	=> $data['amount_words'],
				'updated_by' 	=> $userName,
				'updated_date' 	=> $dateTime
			);

			$ArrEdit = array();
			foreach ($detail as $val => $valx) {
				$ArrEdit[$val]['id'] = $valx['id'];
				$ArrEdit[$val]['nm_barang'] = $valx['nm_barang'];
				//				$ArrEdit[$val]['qty_po'] = $valx['qty'];
			}

			$ArrEditPO = array();
			$no = 0;
			if (!empty($data['detail_po'])) {
				foreach ($detail_po as $val => $valx) {
					$no++;
					if (!empty($valx['progress'])) {
						$ArrEditPO[$val]['no_po'] 		= $no_po;
						$ArrEditPO[$val]['category'] 	= $category;
						$ArrEditPO[$val]['term'] 		= $no;
						$ArrEditPO[$val]['group_top'] 	= $valx['group_top'];
						$ArrEditPO[$val]['progress'] 	= str_replace(',', '', $valx['progress']);
						$ArrEditPO[$val]['value_usd'] 	= str_replace(',', '', $valx['value_usd']);
						$ArrEditPO[$val]['value_idr'] 	= str_replace(',', '', $valx['value_idr']);
						$ArrEditPO[$val]['keterangan'] 	= strtolower($valx['keterangan']);
						$ArrEditPO[$val]['jatuh_tempo'] = $valx['jatuh_tempo'];
						$ArrEditPO[$val]['syarat'] 		= strtolower($valx['syarat']);
						$ArrEditPO[$val]['created_by'] 	= $userName;
						$ArrEditPO[$val]['created_date'] = $dateTime;
					}
				}
			}

			$hist_top 		= $this->db->get_where('billing_top', array('no_po' => $no_po))->result_array();
			$ArrEditPOHist 	= array();
			if (!empty($hist_top)) {
				foreach ($hist_top as $val => $valx) {
					$ArrEditPOHist[$val]['no_po'] 		= $valx['no_po'];
					$ArrEditPOHist[$val]['category'] 	= $valx['category'];
					$ArrEditPOHist[$val]['term'] 		= $valx['term'];
					$ArrEditPOHist[$val]['progress'] 	= $valx['progress'];
					$ArrEditPOHist[$val]['value_usd'] 	= $valx['value_usd'];
					$ArrEditPOHist[$val]['value_idr'] 	= $valx['value_idr'];
					$ArrEditPOHist[$val]['keterangan'] 	= $valx['keterangan'];
					$ArrEditPOHist[$val]['jatuh_tempo'] = $valx['jatuh_tempo'];
					$ArrEditPOHist[$val]['syarat'] 		= $valx['syarat'];
					$ArrEditPOHist[$val]['created_by'] 	= $valx['created_by'];
					$ArrEditPOHist[$val]['created_date'] = $valx['created_date'];
					$ArrEditPOHist[$val]['hist_by'] 	= $userName;
					$ArrEditPOHist[$val]['hist_date']	= $dateTime;
				}
			}

			// print_r($ArrHeader);
			// print_r($ArrEdit);
			// print_r($ArrEditPO);
			// print_r($ArrEditPOHist);
			// exit;

			$this->db->trans_start();
			$this->db->where('no_po', $data['no_po']);
			$this->db->update('tran_po_header', $ArrHeader);

			$this->db->update_batch('tran_po_detail', $ArrEdit, 'id');

			$this->db->where('no_po', $data['no_po']);
			$this->db->delete('billing_top');

			if (!empty($ArrEditPO)) {
				$this->db->insert_batch('billing_top', $ArrEditPO);
			}

			if (!empty($ArrEditPOHist)) {
				$this->db->insert_batch('hist_billing_top', $ArrEditPOHist);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=> 'Save data failed. Please try again later ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Save data success. Thanks ...',
					'status'	=> 1
				);
				history('Edit po custom and insert top : ' . $data['no_po']);
			}
			echo json_encode($Arr_Data);
		} else {
			$no_po 	= $this->uri->segment(3);

			$result	= $this->db->get_where('tran_po_header', array('no_po' => $no_po))->result();

			$sql_detail = "SELECT a.*, b.nm_supplier FROM tran_po_detail a LEFT JOIN tran_po_header b ON a.no_po=b.no_po WHERE a.no_po='" . $no_po . "'";

			if ($result[0]->status != 'DELETED') {
				$sql_detail = "SELECT a.*, b.nm_supplier FROM tran_po_detail a LEFT JOIN tran_po_header b ON a.no_po=b.no_po WHERE a.no_po='" . $no_po . "' AND a.deleted='N'";
			}


			$result_det		= $this->db->query($sql_detail)->result_array();

			$data_top 		= $this->db->query("SELECT * FROM billing_top WHERE no_po='" . $no_po . "'")->result_array();
			$data_kurs 		= $this->db->query("SELECT * FROM kurs WHERE kode_dari='USD' LIMIT 1")->result();

			$payment = $this->db->get_where('list_help', array('group_by' => 'top'))->result_array();

			$data = array(
				'data' => $result,
				'data_top' => $data_top,
				'data_kurs' => $data_kurs,
				'result' => $result_det,
				'payment' => $payment
			);

			$this->load->view('Metode_pembelian/modal_edit_purchase_order', $data);
		}
	}

	public function print_po()
	{
		$this->Metode_pembelian_model->print_po();
	}

	public function print_po2xxx()
	{
		$this->Metode_pembelian_model->print_po2();
	}

	public function print_po2()
	{
		$no_po		= $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $data_session['ORI_User']['username'];

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$data = array(
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'no_po' => $no_po
		);
		history('Print Purchase Order ' . $no_po);
		$this->load->view('Print/print_po_dotmatrik_non_material', $data);
	}

	public function get_add()
	{
		$this->Metode_pembelian_model->get_add();
	}

	public function get_kurs()
	{
		$this->Metode_pembelian_model->get_kurs();
	}

	public function modal_po()
	{
		$this->Metode_pembelian_model->modal_po();
	}

	public function server_side_list_rfq()
	{
		$this->Metode_pembelian_model->get_data_json_list_rfq();
	}

	public function edit_po_qty()
	{
		if ($this->input->post()) {
			$data_session	= $this->session->userdata;
			$Username 		= $this->session->userdata['ORI_User']['username'];
			$dateTime		= date('Y-m-d H:i:s');
			$data	= $this->input->post();

			$detail = $data['detail'];
			$no_po = $data['no_po'];
			$tgl_dibutuhkan	= date('Y-m-d', strtotime($data['tanggal_dibutuhkan']));
			$total_po		= str_replace(',', '', $data['total_po']);
			$discount		= str_replace(',', '', $data['discount']);
			$net_price		= str_replace(',', '', $data['net_price']);
			$tax			= str_replace(',', '', $data['tax']);
			$net_plus_tax	= str_replace(',', '', $data['net_plus_tax']);
			$delivery_cost	= str_replace(',', '', $data['delivery_cost']);
			$grand_total	= str_replace(',', '', $data['grand_total']);

			$ArrEdit = [];
			$SUM_MAT = 0;
			foreach ($detail as $val => $valx) {
				$qty_po = str_replace(',', '', $valx['qty_purchase']);
				$SUM_MAT += $qty_po;

				$ArrEdit[$val]['id'] 			= $valx['id'];
				$ArrEdit[$val]['total_price'] 	= $valx['total_price'];
				$ArrEdit[$val]['nm_barang'] 	= $valx['nm_barang'];
				$ArrEdit[$val]['qty_purchase'] 	=  $qty_po;
				$ArrEdit[$val]['qty_po'] 		=  $qty_po;
				$ArrEdit[$val]['created_by'] 	= $Username;
				$ArrEdit[$val]['created_date'] 	= $dateTime;
			}

			$ArrHeader['no_po'] 			= $no_po;
			$ArrHeader['total_material'] 	= $SUM_MAT;
			$ArrHeader['total_price'] 		= $grand_total;
			$ArrHeader['tax'] 				= $tax;
			$ArrHeader['total_po'] 			= $total_po;
			$ArrHeader['discount'] 			= $discount;
			$ArrHeader['net_price'] 		= $net_price;
			$ArrHeader['net_plus_tax'] 		= $net_plus_tax;
			$ArrHeader['delivery_cost'] 	= $delivery_cost;
			$ArrHeader['tgl_dibutuhkan'] 	= $tgl_dibutuhkan;
			$ArrHeader['updated_by'] 		= $Username;
			$ArrHeader['updated_date'] 		= $dateTime;

			// print_r($ArrEdit);
			// exit;
			$this->db->trans_start();
			$this->db->update_batch('tran_po_detail', $ArrEdit, 'id');

			$this->db->where('no_po', $no_po);
			$this->db->update('tran_po_header', $ArrHeader);
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=> 'Save data failed. Please try again later ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Save data success. Thanks ...',
					'status'	=> 1
				);
				history('Edit qty PO : ' . $no_po);
			}
			echo json_encode($Arr_Data);
		} else {
			$no_po 	= $this->uri->segment(3);
			$get_status = $this->db->select('*')->get_where('tran_po_header', array('no_po' => $no_po))->result_array();
			$WHERE = [
				'a.no_po' => $no_po
			];
			if ($get_status[0]['status'] != 'DELETED') {
				$WHERE = [
					'a.no_po' => $no_po,
					'a.deleted' => 'N'
				];
			}

			$result		= $this->db
				->select('a.*, b.nm_supplier, b.mata_uang AS currency')
				->join('tran_po_header b', 'a.no_po=b.no_po', 'left')
				->get_where('tran_po_detail a', $WHERE)
				->result_array();

			$data = array(
				'result' => $result,
				'header' => $get_status
			);

			$this->load->view('Metode_pembelian/edit_po_qty', $data);
		}
	}

	public function delete_sebagian_po()
	{
		$this->Metode_pembelian_model->delete_sebagian_po();
	}

	public function delete_sebagian_po_new()
	{
		$this->Metode_pembelian_model->delete_sebagian_po_new();
	}

	public function delete_sebagian_po_new_repeat()
	{
		$data_session	= $this->session->userdata;
		$data			= $this->input->post();

		// $detail_ch 	= $data['checked'];
		$id 		= $data['id'];
		$no_po 		= $data['no_po'];
		$detail 	= $this->db->select('*')->from('tran_po_detail')->where('id', $id)->where('no_po', $no_po)->where('deleted', 'N')->get()->result_array();

		$ArrEdit = [];
		if (!empty($detail)) {
			foreach ($detail as $val => $valx) {
				$ArrEdit[$val]['id'] 			= $valx['id'];
				$ArrEdit[$val]['deleted'] 		= 'Y';
				$ArrEdit[$val]['deleted_by'] 	= $data_session['ORI_User']['username'];
				$ArrEdit[$val]['deleted_date'] 	= date('Y-m-d H:i:s');
			}
		}
		// print_r($ArrEdit);
		// exit;
		$this->db->trans_start();
		if (!empty($ArrEdit)) {
			$this->db->update_batch('tran_po_detail', $ArrEdit, 'id');
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save data failed. Please try again later ...',
				'status'	=> 0,
				'no_po' 	=> $no_po
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save data success. Thanks ...',
				'status'	=> 1,
				'no_po' 	=> $no_po
			);
			history('Delete sebagian PO Repeat : ' . $no_po);
		}
		echo json_encode($Arr_Data);
	}

	public function delete_semua_po()
	{
		$this->Metode_pembelian_model->delete_semua_po();
	}
	public function po_top($id_po)
	{
		$this->Metode_pembelian_model->po_top();
	}
	public function save_po_top()
	{
		$this->Metode_pembelian_model->save_po_top();
	}
	public function invoice_receive($id)
	{
		$controller			= 'purchase/purchase_order';
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}
		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$info_payterm 	= $this->db->query("select * from billing_top where id='" . $id . "'")->row();
		if ($info_payterm->invoice_no != "") {
			$dt_incoming = $this->db->query("select * from warehouse_adjustment where id_invoice='" . $id . "' and no_ipp='" . $info_payterm->no_po . "'")->result();
		} else {
			$dt_incoming = $this->db->query("select * from warehouse_adjustment where no_ipp='" . $info_payterm->no_po . "' and (id_invoice is null or id_invoice = '')")->result();
		}
		$data = array(
			'title'			=> 'Receive Invoice',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'results'		=> $info_payterm,
			'dt_incoming'	=> $dt_incoming,
			'akses_menu'	=> $Arr_Akses,
			'id'			=> $id
		);
		history('View receive invoice ' . $id);
		$this->load->view('Metode_pembelian/form_receive_invoice', $data);
	}

	function receive_invoice_save()
	{
		$data = $this->input->post();
		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');
		$id				= $data['id_top'];
		$total			= $data['invoice_total'];
		$ArrUpdate = [
			'invoice_no' => $data['invoice_no'],
			'nilai_ppn' => $data['nilai_ppn'],
			'invoice_total' => $data['invoice_total'],
			'potong_um' => $data['potong_um'],
			'faktur_pajak' => $data['faktur_pajak'],
			'surat_jalan' => $data['surat_jalan'],
			'lainnya' => $data['lainnya'],
			'tgl_terima' => $data['tgl_terima'],
			'created_date_invoice' => $dateTime,
			//			'invoice_dokumen' => $data['invoice_dokumen'],
			'created_by_invoice' => $Username,
		];

		$this->db->trans_start();

		$kode_trans		= $this->input->post("kode_trans");
		if (!empty($kode_trans)) {
			foreach ($kode_trans as $keys => $val) {
				$datawarehouse = array(
					'id_invoice' => $id,
				);
				$this->All_model->dataUpdate('warehouse_adjustment', $datawarehouse, array('kode_trans' => $val));
			}
		}


		$no_po = $data['no_po'];

		$datapo = $this->db->query("select * from tran_po_header where no_po='" . $no_po . "'")->row();
		//		if($data->nilai_terima_barang_kurs>0){
		if ($total > 0) {
			if ($data['group_top'] == 'uang muka') {
				$jenis_jurnal = 'JV053';
			} else {
				$jenis_jurnal = 'JV041';
			}
			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' order by parameter_no")->result();
			$nomor_jurnal = $jenis_jurnal . $no_po . rand(100, 999);
			$payment_date = $data['tgl_terima']; //date("Y-m-d");
			$det_Jurnaltes1 = array();
			//			$total=($data->nilai_terima_barang_kurs);

			if ($total != 0) {
				foreach ($datajurnal1 as $rec) {
					if ($rec->parameter_no == "1") {

						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $datapo->no_po,
							'no_reff' => $data['invoice_no'],
							'debet' => $data['invoice_total'] + $data['potong_um'] - $data['nilai_ppn'],
							'kredit' => 0,
							'no_request' => $datapo->no_po,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "2") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $datapo->no_po,
							'no_reff' => $data['invoice_no'],
							'debet' => 0,
							'kredit' => $data['invoice_total'],
							'no_request' => $datapo->no_po,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "3") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PPN PO ' . $datapo->no_po,
							'no_reff' => $data['invoice_no'],
							'debet' => $data['nilai_ppn'],
							'kredit' => 0,
							'no_request' => $datapo->no_po,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "4") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'Potongan DP ' . $datapo->no_po,
							'no_reff' => $data['invoice_no'],
							'debet' => 0,
							'kredit' => $data['potong_um'],
							'no_request' => $datapo->no_po,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$this->db->insert_batch('jurnaltras', $det_Jurnaltes1);

			//auto jurnal

			$tanggal = $data['tgl_terima'];
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
				$this->db->insert(DBACC . '.jurnal', $datadetail);
			}
			$keterangan		= 'Receive Invoice ' . $data['invoice_no'];
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
			$this->db->insert(DBACC . '.javh', $dataJVhead);

			//end auto jurnal

		}

		$this->db->where('id', $id);
		$this->db->update('billing_top', $ArrUpdate);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data failed. Please try again later ...',
				'status'	=> 2,
				'id'		=> $id
			);
		} else {
			$this->db->trans_commit();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data success. Thanks ...',
				'status'	=> 1,
				'id'		=> $id
			);
			history('Reeceive Invoice ' . $id);
		}
		echo json_encode($Arr_Kembali);
	}

	public function invoice_receive_top($no_po)
	{
		$controller			= 'purchase/purchase_order';
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}
		$dt_incoming = $this->db->query("select * from warehouse_adjustment where no_ipp='" . $no_po . "' and (id_invoice is null or id_invoice = '')")->result();

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data_po 	= $this->db->query("select * from tran_po_header where no_po='" . $no_po . "'")->row();
		$data = array(
			'title'			=> 'Receive Invoice',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'results'		=> $data_po,
			'akses_menu'	=> $Arr_Akses,
			'dt_incoming'	=> $dt_incoming,
			'id'			=> $no_po
		);
		history('View receive invoice ' . $no_po);
		$this->load->view('Metode_pembelian/form_receive_invoice_top', $data);
	}

	function receive_invoice_top_save()
	{
		$data = $this->input->post();
		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');
		$no_po			= $data['no_po'];
		$data_term 		= $this->db->query("select max(term) as terms from billing_top where no_po='" . $no_po . "'")->row();
		$total			= $data['invoice_total'];
		$ArrUpdate = [
			'invoice_no' => $data['invoice_no'],
			'no_po' => $data['no_po'],
			'category' => $data['category'],
			'potong_um' => $data['potong_um'],
			'term' => ($data_term->terms + 1),
			'group_top' => 'progress',
			'progress' => '0',
			'value_usd' => '0',
			'value_idr' => ($data['invoice_total'] - $data['nilai_ppn']),
			'nilai_ppn' => $data['nilai_ppn'],
			'invoice_total' => $data['invoice_total'],
			'faktur_pajak' => $data['faktur_pajak'],
			'surat_jalan' => $data['surat_jalan'],
			'lainnya' => $data['lainnya'],
			'tgl_terima' => $data['tgl_terima'],
			'created_by' => $Username,
			'created_date' => $dateTime,
			'created_date_invoice' => $dateTime,
			'created_by_invoice' => $Username,
		];
		$this->db->trans_start();

		$datapo = $this->db->query("select * from tran_po_header where no_po='" . $no_po . "'")->row();
		//		if($data->nilai_terima_barang_kurs>0){
		if ($total > 0) {
			$jenis_jurnal = 'JV041';
			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' order by parameter_no")->result();
			$nomor_jurnal = $jenis_jurnal . $no_po . rand(100, 999);
			$payment_date = $data['tgl_terima']; //date("Y-m-d");
			$det_Jurnaltes1 = array();
			//			$total=($data->nilai_terima_barang_kurs);			
			if ($total != 0) {
				foreach ($datajurnal1 as $rec) {
					if ($rec->parameter_no == "1") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $datapo->no_po,
							'no_reff' => $data['invoice_no'],
							'debet' => $data['invoice_total'] + $data['potong_um'] - $data['nilai_ppn'],
							'kredit' => 0,
							'no_request' => $datapo->no_po,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "2") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PO ' . $datapo->no_po,
							'no_reff' => $data['invoice_no'],
							'debet' => 0,
							'kredit' => $data['invoice_total'],
							'no_request' => $datapo->no_po,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "3") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'PPN PO ' . $datapo->no_po,
							'no_reff' => $data['invoice_no'],
							'debet' => $data['nilai_ppn'],
							'kredit' => 0,
							'no_request' => $datapo->no_po,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
					if ($rec->parameter_no == "4") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'JV',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => 'Potongan DP ' . $datapo->no_po,
							'no_request' => $datapo->no_po,
							'debet' => 0,
							'kredit' => $data['potong_um'],
							'no_reff' => $data['invoice_no'],
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $datapo->id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$this->db->insert_batch('jurnaltras', $det_Jurnaltes1);

			//auto jurnal

			$tanggal = $data['tgl_terima'];
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$total	= 0;
			$Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tanggal);
			$det_Jurnaltes1 = array();
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
				$this->db->insert(DBACC . '.jurnal', $datadetail);
			}
			$keterangan		= 'Receive Invoice ' . $data['invoice_no'];
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
			$this->db->insert(DBACC . '.javh', $dataJVhead);

			//end auto jurnal

		}

		$idbill = $this->db->insert('billing_top', $ArrUpdate);
		$kode_trans		= $this->input->post("kode_trans");
		if (!empty($kode_trans)) {
			foreach ($kode_trans as $keys => $val) {
				$datawarehouse = array(
					'id_invoice' => $idbill,
				);
				$this->All_model->dataUpdate('warehouse_adjustment', $datawarehouse, array('kode_trans' => $val));
			}
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data failed. Please try again later ...',
				'status'	=> 2,
				'id'		=> $no_po
			);
		} else {
			$this->db->trans_commit();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data success. Thanks ...',
				'status'	=> 1,
				'id'		=> $no_po
			);
			history('Reeceive Invoice ' . $no_po);
		}
		echo json_encode($Arr_Kembali);
	}
	//===Payment===
	public function request_payment($id_top)
	{
		$info_payterm 	= $this->db->query("select * from billing_top where id='" . $id_top . "'")->row();
		$datapoh = $this->db->query("select a.*,b.data_bank from tran_po_header a left join supplier b on a.id_supplier=b.id_supplier where a.no_po='" . $info_payterm->no_po . "'")->row();
		$no_po = $datapoh->no_po;
		$curency  = $this->db->limit(1)->get_where('kurs', array('kode_dari' => 'USD'))->result();
		$payterm  = $this->db->query("select data2,name from list_help where group_by='top' and name='" . $info_payterm->group_top . "'")->row();
		$datapod = array();
		$data_payterm = array();
		if (!empty($datapoh)) {
			$datapod = $this->db->query("SELECT a.*, b.nm_supplier FROM tran_po_detail a LEFT JOIN tran_po_header b ON a.no_po=b.no_po WHERE a.no_po='" . $no_po . "' AND a.deleted='N'")->result();
			$data_payterm 	= $this->db->query("select * from billing_top where no_po='" . $info_payterm->no_po . "'")->result();
			$def_ppn = (object)array('info' => $datapoh->tax);
		} else {
			$def_ppn = $this->All_model->getppn();
		}
		$def_pph = $this->All_model->getpph();
		$controller			= "";
		$Arr_Akses			= getAcccesmenu($controller);
		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$combo_coa_pph = $this->All_model->Getcomboparamcoa('pph_pembelian');
		$data = array(
			'title'			=> 'Request Payment',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'def_ppn' 		=> $def_ppn,
			'def_pph' 		=> $def_pph,
			'datapoh' 		=> $datapoh,
			'info_payterm' 	=> $info_payterm,
			'payterm'		=> $payterm,
			'data_payterm'	=> $data_payterm,
			'datapod'		=> $datapod,
			'data_ppn'		=> $this->data_ppn,
			'curency'		=> $curency,
			'datapo'		=> $datapoh,
			'combo_coa_pph'	=> $combo_coa_pph,
		);
		$this->load->view('Metode_pembelian/form_request', $data);
	}
	public function request_payment_save()
	{
		$id_req = $this->input->post("id_req");
		$request_date = $this->input->post("request_date");
		$no_po = $this->input->post("no_po");
		$id_supplier = $this->input->post("id_supplier");
		$nilai_ppn = $this->input->post("nilai_ppn");
		$curs_header = $this->input->post("curs_header");
		$nilai_total = $this->input->post("nilai_total");
		$total_bayar = $this->input->post("total_bayar");
		$po_belum_dibayar = $this->input->post("po_belum_dibayar");
		$sisa_dp = $this->input->post("sisa_dp");
		$tipe = $this->input->post("tipe");
		$no_request = $this->input->post("no_request");
		$no_invoice = $this->input->post("no_invoice");
		$nilai_invoice = $this->input->post("nilai_invoice");
		$keterangan = $this->input->post("keterangan");
		$potongan_dp = $this->input->post("potongan_dp");
		$potongan_claim = $this->input->post("potongan_claim");
		$keterangan_potongan = $this->input->post("keterangan_potongan");
		$request_payment = $this->input->post("request_payment");
		$invoice_ppn = $this->input->post("invoice_ppn");
		$payfor = $this->input->post("payfor");
		$nilai_po_invoice = $this->input->post("nilai_po_invoice");
		$nilai_pph_invoice = $this->input->post("nilai_pph_invoice");
		$id_top = $this->input->post("id_top");
		$nilai_po = $this->input->post("nilai_po");
		$coa_pph = $this->input->post("coa_pph");
		$bank_transfer = $this->input->post("bank_transfer");

		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');

		$this->db->trans_begin();
		if ($id_req == '') {
			$no_request = $this->All_model->GenerateAutoNumber_YM('request_payment');
			$id_po = $no_po;
			$dataheader =  array(
				'id_top' => $id_top,
				'no_request' => $no_request,
				'request_date' => $request_date,
				'no_po' => $no_po,
				'id_supplier' => $id_supplier,
				'nilai_po' => $nilai_po,
				'nilai_ppn' => $nilai_ppn,
				'tipe' => $tipe,
				'status' => '0',
				'curs_header' => $curs_header,
				'nilai_total' => $nilai_total,
				'total_bayar' => $total_bayar,
				'po_belum_dibayar' => $po_belum_dibayar,
				'sisa_dp' => $sisa_dp,
				'nilai_po_invoice' => $nilai_po_invoice,
				'nilai_pph_invoice' => $nilai_pph_invoice,
				'no_invoice' => $no_invoice,
				'nilai_invoice' => $nilai_invoice,
				'invoice_ppn' => $invoice_ppn,
				'keterangan' => $keterangan,
				'potongan_dp' => $potongan_dp,
				'potongan_claim' => $potongan_claim,
				'keterangan_potongan' => $keterangan_potongan,
				'request_payment' => $request_payment,
				'coa_pph' => $coa_pph,
				'bank_transfer' => $bank_transfer,
				'created_on' => date('Y-m-d H:i:s'),
				'created_by' => $Username
			);
			$idreq = $this->All_model->DataSave('purchase_order_request_payment_nm', $dataheader);
			if ($id_top != '') $this->All_model->DataUpdate('billing_top', array('proses_inv' => '1', 'id_penagihan' => $idreq), array('id' => $id_top));
			if (!empty($payfor)) {
				foreach ($payfor as $val) {
					if ($val != "") $this->All_model->DataUpdate('tran_po_detail', array('status_pay' => $no_request), array('id' => $val));
				}
			}
		} else {
			$dataheader =  array(
				'id_top' => $id_top,
				'request_date' => $request_date,
				'curs_header' => $curs_header,
				'no_invoice' => $no_invoice,
				'nilai_invoice' => $nilai_invoice,
				'keterangan' => $keterangan,
				'potongan_dp' => $potongan_dp,
				'potongan_claim' => $potongan_claim,
				'keterangan_potongan' => $keterangan_potongan,
				'invoice_ppn' => $invoice_ppn,
				'tipe' => $tipe,
				'nilai_po_invoice' => $nilai_po_invoice,
				'nilai_pph_invoice' => $nilai_pph_invoice,
				'nilai_po' => $nilai_po,
				'request_payment' => $request_payment,
				'coa_pph' => $coa_pph,
				'bank_transfer' => $bank_transfer,
				'modified_on' => date('Y-m-d H:i:s'),
				'modified_by' => $Username
			);
			$this->All_model->DataUpdate('purchase_order_request_payment_nm', $dataheader, array('id' => $id_req));
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save data failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save data success. Thanks ...',
				'status'	=> 1,
				'id_request' => $no_request
			);
			history('Add Request payment PO : ' . $no_po);
		}
		echo json_encode($Arr_Data);
	}
	function print_request($id_request)
	{
		$datapo = $this->db->query("select * from purchase_order_request_payment_nm where id_top='" . $id_request . "'")->row();
		$data = array(
			'datapo' 		=> $datapo,
		);
		$this->load->view('Metode_pembelian/print_request', $data);
	}
	function close_po($no_po)
	{

		$datapo = $this->db->query("select * from tran_po_header where no_po='" . $no_po . "'")->row();
		$curency  = $this->db->limit(1)->get_where('kurs', array('kode_dari' => 'USD'))->result();
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data = array(
			'title'			=> 'Close PO',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'datapo' 		=> $datapo,
			'curency'		=> $curency,
		);
		$this->load->view('Metode_pembelian/close_po', $data);
	}
	function save_close_po()
	{
		$no_po = $this->input->post("no_po");
		$this->db->trans_begin();
		/*
		$data = $this->db->query("select * from tran_po_header where no_po='".$no_po."'")->row();
		if($data->total_terima_barang_idr!=$data->total_bayar_rupiah){
			$jenis_jurnal='JV034';
			$datajurnal1 = $this->db->query("select * from ".DBACC.".master_oto_jurnal_detail where kode_master_jurnal='".$jenis_jurnal."' order by parameter_no")->result();
			$nomor_jurnal=$jenis_jurnal.$no_po.rand(100,999);
			$payment_date=date("Y-m-d");
			$det_Jurnaltes1=array();
			$selisih=($data->total_terima_barang_idr-$data->total_bayar_rupiah);
			if($selisih!=0) {
			  foreach ($datajurnal1 as $rec) {
				if($rec->parameter_no=="1"){
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'JV', 'no_perkiraan' => $rec->no_perkiraan, 'keterangan' => 'Inventory '.$data->no_po, 'no_request' => $data->no_po, 'debet' => (($selisih>0)?abs($selisih):0), 'kredit' => (($selisih>0)?0:abs($selisih)), 'no_reff' => $data->no_po, 'jenis_jurnal'=>$jenis_jurnal, 'nocust'=>$data->id_supplier
					);
				}
				if($rec->parameter_no=="2"){
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'JV', 'no_perkiraan' => $rec->no_perkiraan, 'keterangan' => 'Inventory '.$data->no_po, 'no_request' => $data->no_po, 'debet' => (($selisih>0)?0:abs($selisih)), 'kredit' => (($selisih>0)?abs($selisih):0), 'no_reff' => $data->no_po, 'jenis_jurnal'=>$jenis_jurnal, 'nocust'=>$data->id_supplier
					);
				}
			  }
			}
			$this->db->insert_batch('jurnal', $det_Jurnaltes1);
		}
*/
		$this->db->query("update tran_po_header set status_po='CLS',status='COMPLETE' where no_po='" . $no_po . "'");
		$this->db->trans_complete();
		if ($this->db->trans_status()) {
			$this->db->trans_commit();
			$result         = TRUE;
			history('Close PO : ' . $no_po);
		} else {
			$this->db->trans_rollback();
			$result = FALSE;
		}
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
	//==================================================================================================================
	//==============================================NON PURCHASE ORDER==================================================
	//==================================================================================================================

	public function approval_non_po()
	{
		$this->Metode_pembelian_model->approval_non_po();
	}

	public function server_side_app_non_po()
	{
		$this->Metode_pembelian_model->get_data_json_app_non_po();
	}

	public function app_non_po()
	{
		$this->Metode_pembelian_model->app_non_po();
	}

	public function get_add_non_po()
	{
		$this->Metode_pembelian_model->get_add_non_po();
	}

	public function expense_non_po()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$dateTime		= date('Y-m-d H:i:s');

			$code_plan  	= $data['id'];
			$coa_lain       = $data['coa_lain'];
			$coa_bank       = $data['coa_bank'];
			$biaya_lain     = str_replace(',', '', $data['biaya_lain']);
			$ket_lain		= strtolower($data['keterangan_lain']);
			$selisih     	= str_replace(',', '', $data['selisih']);
			$total_real     = str_replace(',', '', $data['total_real']);

			$detail 		= $data['detail'];

			$ArrDetail = array();
			if (!empty($detail)) {
				foreach ($detail as $val => $valx) {
					$harga_satuan 	= str_replace(',', '', $valx['price_unit_real']);
					$harga 			= str_replace(',', '', $valx['total_harga_real']);
					$ArrDetail[$val]['id'] 				= $valx['id'];
					$ArrDetail[$val]['price_pay_unit'] 	= $harga_satuan;
					$ArrDetail[$val]['price_pay_total'] = $harga;
					$ArrDetail[$val]['keterangan_real'] = strtolower($valx['keterangan_real']);
				}
			}

			//header edit
			$ArrHeader		= array(
				'coa_lain' 	=> $coa_lain,
				'coa_bank' 	=> $coa_bank,
				'biaya_lain' 	=> $biaya_lain,
				'keterangan_lain' 	=> $ket_lain,
				'nilai_selisih' 	=> $selisih,
				'nilai_real' 	=> $total_real,
				'expense_by'	=> $data_session['ORI_User']['username'],
				'expense_date'	=> $dateTime
			);

			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();
			$this->db->where(array('no_non_po' => $code_plan));
			$this->db->update('tran_non_po_header', $ArrHeader);

			$this->db->update_batch('tran_non_po_detail', $ArrDetail, 'id');
			$this->db->trans_complete();


			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data failed. Please try again later ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data success. Thanks ...',
					'status'	=> 1
				);
				history('Expense report po non rutin ' . $code_plan);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$controller			= ucfirst(strtolower($this->uri->segment(1))) . '/approval_non_po';
			$Arr_Akses			= getAcccesmenu($controller);
			if ($Arr_Akses['read'] != '1') {
				$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
				redirect(site_url('dashboard'));
			}

			$data_Group	= $this->master_model->getArray('groups', array(), 'id', 'name');
			$id 		= $this->uri->segment(3);
			$approve 	= $this->uri->segment(4);
			$non_po 	= $this->uri->segment(5);
			$header 	= $this->db->query("SELECT * FROM tran_non_po_header WHERE no_non_po='" . $id . "' ")->result();
			$detail 	= $this->db->query("SELECT * FROM tran_non_po_detail WHERE no_non_po='" . $id . "' ")->result_array();
			$datacoa 	= $this->db->query("SELECT * FROM coa_category WHERE tipe='NONRUTIN' ")->result_array();
			$satuan		= $this->db->get_where('raw_pieces', array('delete' => 'N'))->result_array();
			$tanda 		= (!empty($header)) ? 'Edit' : 'Add';
			if (!empty($approve)) {
				$tanda 		= ($approve == 'view') ? 'View' : 'Approve';
			}
			$datcoa	= $this->All_model->GetCoaCombo();
			$data = array(
				'title'				=> $tanda . ' Non PO',
				'action'		=> strtolower($tanda),
				'akses_menu'	=> $Arr_Akses,
				'header'		=> $header,
				'detail'		=> $detail,
				'datacoa'		=> $datacoa,
				'datcoa'		=> $datcoa,
				'satuan'		=> $satuan,
				'approve'		=> $approve,
				'non_po'		=> $non_po,
				'id'			=> $id
			);

			$this->load->view('Metode_pembelian/expense_non_po', $data);
		}
	}

	// APPROVAL PO
	//Approval PO
	public function approval_po($id = null)
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/approval_po/" . $id;
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data = array(
			'title'			=> 'Metode_pembelian Non-Material & Jasa >> PO >> Approval PO',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'id'			=> $id
		);
		history('View approval po rutin ' . $id);
		$this->load->view('Metode_pembelian/approval_po', $data);
	}

	public function server_side_purchase_order_approve()
	{
		$this->Metode_pembelian_model->get_data_json_purchase_order_approve();
	}

	public function modal_approval_po()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$data_session	= $this->session->userdata;
			$Username 		= $this->session->userdata['ORI_User']['username'];
			$dateTime		= date('Y-m-d H:i:s');

			$id				= $data['id'];
			$no_po			= $data['no_po'];
			$status 		= $data['status'];
			$approve_reason = $data['approve_reason'];
			$nilai_po 		= $data['nilai_po'] * 14000;

			$created 	= ($id == '1') ? 'approval1_by' : 'approval2_by';
			$dated 		= ($id == '1') ? 'approval1_date' : 'approval2_date';
			$reason 	= ($id == '1') ? 'reason1' : 'reason2';
			$statusx 	= ($id == '1') ? 'status1' : 'status2';

			// $ArrUpdate = [
			// 	$statusx => $status,
			// 	$reason => $approve_reason,
			// 	$created => $Username,
			// 	$dated => $dateTime
			// ];

			// if($nilai_po <= 50000000){
			$ArrUpdate = [
				'status1' => $status,
				'reason1' => $approve_reason,
				'approval1_by' => $Username,
				'approval1_date' => $dateTime,
				'status2' => $status,
				'reason2' => $approve_reason,
				'approval2_by' => $Username,
				'approval2_date' => $dateTime
			];
			// }

			$this->db->trans_start();
			$this->db->where('no_po', $no_po);
			$this->db->update('tran_po_header', $ArrUpdate);
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data failed. Please try again later ...',
					'status'	=> 2,
					'id'		=> $id
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data success. Thanks ...',
					'status'	=> 1,
					'id'		=> $id
				);
				history('Approval ' . $id . ' / ' . $no_po . ' / ' . $status);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$id 	= $this->uri->segment(4);
			$no_po 	= $this->uri->segment(3);

			$result	= $this->db
				->select('a.*, b.nm_supplier, b.total_price AS total_price2, b.net_plus_tax, b.delivery_cost, b.net_price AS net_price2, b.tax, b.total_po, b.discount, b.tgl_dibutuhkan AS tgl_butuh')
				->from('tran_po_detail a')
				->join('tran_po_header b', 'ON a.no_po=b.no_po', 'left')
				->where('a.no_po', $no_po)
				->get()
				->result_array();

			$data = array(
				'result' 	=> $result,
				'id' 		=> $id,
				'no_po' 	=> $no_po,
				'nilai_po'	=> $result[0]['total_price2']
			);

			$this->load->view('Metode_pembelian/modal_approval_po', $data);
		}
	}

	//REPEAT PO
	public function repeat_po()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)) . '/' . strtolower($this->uri->segment(2)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data = array(
			'title'			=> 'Metode_pembelian Non-Material & Jasa >> PO >> Repeat PO',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses
		);
		history('View data repeat po non-material');
		$this->load->view('Metode_pembelian/repeat_po', $data);
	}

	public function server_side_repeat_po()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/repeat_po";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_repeat_po(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		foreach ($query->result_array() as $row) {
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			}
			if ($asc_desc == 'desc') {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_po_header WHERE no_po='" . $row['no_po'] . "'")->result_array();
			$arr_sup = array();
			foreach ($list_supplier as $val => $valx) {
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);

			if ($row['status'] != 'DELETED') {
				$list_material	= $this->db->query("SELECT nm_barang, qty_purchase, price_ref, price_ref_sup, net_price FROM tran_po_detail WHERE no_po='" . $row['no_po'] . "' AND deleted='N' GROUP BY id_barang")->result_array();
			} else {
				$list_material		= $this->db->query("SELECT nm_barang, qty_purchase, price_ref, price_ref_sup, net_price FROM tran_po_detail WHERE no_po='" . $row['no_po'] . "' GROUP BY id_barang")->result_array();
			}
			$arr_mat = array();
			foreach ($list_material as $val => $valx) {
				$arr_mat[$val] = strtoupper($valx['nm_barang']);
			}
			$dt_mat	= implode("<br>", $arr_mat);

			$arr_qty = array();
			foreach ($list_material as $val => $valx) {
				$arr_qty[$val] = number_format($valx['qty_purchase']);
			}
			$dt_qty	= implode("<br>", $arr_qty);

			$arr_pur = array();
			foreach ($list_material as $val => $valx) {
				$arr_pur[$val] = number_format($valx['net_price'], 2);
			}
			$dt_pur	= implode("<br>", $arr_pur);

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['no_po'] . "</div>";
			if ($row['category'] == 'asset') {
				$warna = '#a9179e';
			} elseif ($row['category'] == 'rutin') {
				$warna = '#a19012';
			} else {
				$warna = '#1bb885';
			}
			$category = $row['category'];
			if ($category == 'rutin') {
				$category = 'stok';
			}

			if ($category == 'non rutin') {
				$category = 'departemen';
			}
			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . strtoupper($category) . "</span></div>";
			$nestedData[]	= "<div align='left'>" . $dt_sup . "</div>";
			$nestedData[]	= "<div align='left'>" . $dt_mat . "</div>";
			$nestedData[]	= "<div align='right'>" . $dt_qty . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['total_price'], 2) . "</div>";
			$nestedData[]	= "<div align='left'>" . get_name('users', 'nm_lengkap', 'username', $row['created_by']) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['created_date'])) . "</div>";
			if ($row['status'] == 'COMPLETE') {
				$warna = 'bg-green';
				$status = $row['status'];
			} else if ($row['status'] == 'WAITING IN') {
				$warna = 'bg-blue';
				$status = $row['status'];
			} else if ($row['status'] == 'IN PARSIAL') {
				$warna = 'bg-purple';
				$status = $row['status'];
			} else {
				$warna = 'bg-red';
				$status = $row['status'];
			}

			$span_bg = "<span class='badge " . $warna . "'>" . $status . "</span>";

			if (($row['status1'] == 'N' or $row['status2'] == 'N') and $row['deleted'] == 'N' and $row['status'] == 'WAITING IN') {
				if ($row['status1'] == 'N') {
					$warna = 'bg-yellow';
					$status = 'Waiting Approval';
				} else {
					$warna = 'bg-green';
					$status = 'Approved 1';
				}

				if ($row['status2'] == 'N') {
					$warna2 = 'bg-yellow';
					$status2 = 'Waiting Approval 2';
				} else {
					$warna2 = 'bg-green';
					$status2 = 'Approved 2';
				}
				// $span_bg = "<span class='badge ".$warna."'>".$status."</span><br><span class='badge ".$warna2."'>".$status2."</span>";
				$span_bg = "<span class='badge " . $warna . "'>" . $status . "</span>";
			}

			$nestedData[]	= "<div align='left'>" . $span_bg . "</div>";
			$edit_print = "";
			$edit_po = "";
			$print_po = "";
			$delete_po = "";
			if ($row['status'] == 'WAITING IN' and $row['status1'] == 'Y' and $row['status2'] == 'Y') {
				$edit_print	= "&nbsp;<button type='button' class='btn btn-sm btn-warning edit_po' title='Edit Print PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-pencil'></i></button>";
				$print_po	= "&nbsp;<a href='" . base_url('purchase/print_po/' . $row['no_po']) . "' target='_blank' class='btn btn-sm btn-info' title='Print PO' data-role='qtip'><i class='fa fa-print'></i></a>";
			}
			if ($row['status'] == 'WAITING IN' and $row['status1'] == 'N' and $row['status2'] == 'N') {
				$edit_po	= "&nbsp;<button type='button' class='btn btn-sm btn-success edit_po_qty' title='Edit PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-edit'></i></button>";
				// $delete_po	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete_po' title='Delete PO' data-no_po='".$row['no_po']."'><i class='fa fa-trash'></i></button>";
			}

			$nestedData[]	= "	<div align='left'>
                                    <button type='button' class='btn btn-sm btn-primary detailMat' title='Detail PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-eye'></i></button>
									" . $edit_po . "
									" . $edit_print . "
									" . $print_po . "
									" . $delete_po . "
								</div>";
			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function query_data_json_repeat_po($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*
			FROM
				tran_po_header a,
				(SELECT @row:=0) r
			WHERE 1=1 AND a.repeat_po IS NOT NULL
			AND (
				a.no_po LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_po',
			2 => 'nm_supplier'
		);

		$sql .= " ORDER BY a.updated_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function repeat_po_process()
	{
		$Arr_Kembali	= array();
		$data			= $this->input->post();
		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');

		$po_repeat 		= $data['no_po'];

		$Ym = date('ym');
		//pengurutan kode
		$srcMtr			= "SELECT MAX(no_po) as maxP FROM tran_po_header WHERE no_po LIKE 'POX" . $Ym . "%' ";
		$resultMtr		= $this->db->query($srcMtr)->result_array();
		$angkaUrut2		= $resultMtr[0]['maxP'];
		$urutan2		= (int)substr($angkaUrut2, 7, 4);
		$urutan2++;
		$urut2			= sprintf('%04s', $urutan2);
		$no_po			= "POX" . $Ym . $urut2;

		$getHeader = $this->db->get_where('tran_po_header', array('no_po' => $po_repeat))->result();
		$getDetail = $this->db->get_where('tran_po_detail', array('no_po' => $po_repeat))->result_array();

		$ArrHeader['no_po'] 			= $no_po;
		$ArrHeader['id_supplier'] 		= $getHeader[0]->id_supplier;
		$ArrHeader['category'] 			= $getHeader[0]->category;
		$ArrHeader['nm_supplier'] 		= $getHeader[0]->nm_supplier;
		$ArrHeader['total_material'] 	= $getHeader[0]->total_material;
		$ArrHeader['total_price'] 		= $getHeader[0]->total_price;
		$ArrHeader['tax'] 				= $getHeader[0]->tax;
		$ArrHeader['total_po'] 			= $getHeader[0]->total_po;
		$ArrHeader['discount'] 			= $getHeader[0]->discount;
		$ArrHeader['net_price'] 		= $getHeader[0]->net_price;
		$ArrHeader['net_plus_tax'] 		= $getHeader[0]->net_plus_tax;
		$ArrHeader['delivery_cost'] 	= $getHeader[0]->delivery_cost;
		$ArrHeader['tgl_dibutuhkan'] 	= $getHeader[0]->tgl_dibutuhkan;
		$ArrHeader['mata_uang'] 		= $getHeader[0]->mata_uang;
		$ArrHeader['repeat_po'] 		= $po_repeat;
		$ArrHeader['npwp'] 				= '01.081.598.3-431.000';
		$ArrHeader['phone'] 			= '021-8972193';
		$ArrHeader['created_by'] 		= $Username;
		$ArrHeader['created_date'] 		= $dateTime;
		$ArrHeader['updated_by'] 		= $Username;
		$ArrHeader['updated_date'] 		= $dateTime;

		$ArrDetail = [];
		foreach ($getDetail as $key => $value) {
			$ArrDetail[$key]['no_po'] 			= $no_po;
			$ArrDetail[$key]['id_header'] 		= $value['id'];
			$ArrDetail[$key]['id_barang'] 		= $value['id_barang'];
			$ArrDetail[$key]['nm_barang'] 		= $value['nm_barang'];
			$ArrDetail[$key]['qty_purchase'] 	= $value['qty_purchase'];
			$ArrDetail[$key]['price_ref'] 		= $value['price_ref'];
			$ArrDetail[$key]['price_ref_sup'] 	= $value['price_ref_sup'];
			$ArrDetail[$key]['moq'] 			= $value['moq'];
			$ArrDetail[$key]['satuan'] 			= $value['satuan'];
			$ArrDetail[$key]['tgl_dibutuhkan'] 	= $value['tgl_dibutuhkan'];
			$ArrDetail[$key]['lead_time'] 		= $value['lead_time'];
			$ArrDetail[$key]['net_price'] 		= $value['net_price'];
			$ArrDetail[$key]['total_price'] 	= $value['total_price'];
			$ArrDetail[$key]['created_by'] 		= $Username;
			$ArrDetail[$key]['created_date'] 	= $dateTime;
		}

		// print_r($ArrHeader);
		// print_r($ArrDetail);
		// exit();
		$this->db->trans_start();
		if (!empty($ArrDetail)) {
			$this->db->insert('tran_po_header', $ArrHeader);
			$this->db->insert_batch('tran_po_detail', $ArrDetail);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data failed. Please try again later ...',
				'status'	=> 2
			);
		} else {
			$this->db->trans_commit();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data success. Thanks ...',
				'status'	=> 1
			);
			history('Repeat PO Non-material ' . $po_repeat . ', new po number ' . $no_po);
		}
		echo json_encode($Arr_Kembali);
	}
}
