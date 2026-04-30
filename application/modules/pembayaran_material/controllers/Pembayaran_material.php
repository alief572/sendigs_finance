<?php
defined('BASEPATH') or exit('No direct script access allowed');
$data_status = array();
class Pembayaran_material extends Admin_Controller
{
	protected $viewPermission   = 'Incoming_Stok.View';
	protected $addPermission    = 'Incoming_Stok.Add';
	protected $managePermission = 'Incoming_Stok.Manage';
	protected $deletePermission = 'Incoming_Stok.Delete';

	protected $data_status;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'pembayaran_material/master_model',
			'pembayaran_material/Pembayaran_material_model',
			'all/All_model',
			'pembayaran_material/Jurnal_model'
		));
		$this->data_status = array('0' => 'Pengajuan', '1' => 'Approve', '2' => 'Selesai');
	}
	//==================================================================================================================
	//==================================================REQUEST PEMBAYARAN==============================================
	//==================================================================================================================
	function index()
	{
		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data_list 			= $this->pembayaran_material_model->get_data_json_request_payment();
		$data_listnm 			= $this->pembayaran_material_model->get_data_json_request_payment_nm();
		$data = array(
			'title'			=> 'Indeks Of Request Payment',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'results'		=> $data_list,
			'resultsnm'		=> $data_listnm,
			'data_status'	=> $this->data_status
		);
		history('View Request Payment');
		$this->load->view('Pembayaran_material/index_request_payment', $data);
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
		$no_request = $this->input->post("no_request");
		$no_invoice = $this->input->post("no_invoice");
		$nilai_invoice = $this->input->post("nilai_invoice");
		$keterangan = $this->input->post("keterangan");
		$potongan_dp = $this->input->post("potongan_dp");
		$potongan_claim = $this->input->post("potongan_claim");
		$keterangan_potongan = $this->input->post("keterangan_potongan");
		$request_payment = $this->input->post("request_payment");
		$invoice_ppn = $this->input->post("invoice_ppn");
		$nilai_pph_invoice = $this->input->post("nilai_pph_invoice");
		$nilai_po_invoice = $this->input->post("nilai_po_invoice");
		$tipe = $this->input->post("tipe");
		$tipetrans = $this->input->post("tipetrans");
		$payfor = $this->input->post("payfor");
		$coa_pph = $this->input->post("coa_pph");
		$bank_transfer = $this->input->post("bank_transfer");

		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');

		$this->db->trans_begin();
		$dataheader =  array(
			'nilai_po_invoice' => $nilai_po_invoice,
			'nilai_pph_invoice' => $nilai_pph_invoice,
			'request_payment' => $request_payment,
			'request_date' => $request_date,
			'no_invoice' => $no_invoice,
			'nilai_invoice' => $nilai_invoice,
			'keterangan' => $keterangan,
			'tipe' => $tipe,
			'invoice_ppn' => $invoice_ppn,
			'potongan_dp' => $potongan_dp,
			'potongan_claim' => $potongan_claim,
			'keterangan_potongan' => $keterangan_potongan,
			'coa_pph' => $coa_pph,
			'bank_transfer' => $bank_transfer,
			'modified_on' => date('Y-m-d H:i:s'),
			'modified_by' => $Username
		);
		if ($tipetrans == "2") {
			$this->All_model->DataUpdate('purchase_order_request_payment_nm', $dataheader, array('id' => $id_req));
			$this->All_model->DataUpdate('tran_po_detail', array('status_pay' => ''), array('status_pay' => $no_request));
			if (!empty($payfor)) {
				foreach ($payfor as $val) {
					if ($val != "") $this->All_model->DataUpdate('tran_po_detail', array('status_pay' => $no_request), array('id' => $val));
				}
			}
		} else {
			$this->All_model->DataUpdate('purchase_order_request_payment', $dataheader, array('id' => $id_req));
			$this->All_model->DataUpdate('tran_material_po_detail', array('status_pay' => ''), array('status_pay' => $no_request));
			if (!empty($payfor)) {
				foreach ($payfor as $val) {
					if ($val != "") $this->All_model->DataUpdate('tran_material_po_detail', array('status_pay' => $no_request), array('id' => $val));
				}
			}
		}
		$this->db->trans_complete();
		if ($this->db->trans_status()) {
			$this->db->trans_commit();
			$keterangan     = "SUKSES, simpan data ";
			$result         = TRUE;
		} else {
			$this->db->trans_rollback();
			$keterangan     = "GAGAL, simpan data ";
			$result = FALSE;
			history('Save Edit Request Payment, No ' . $id_req);
		}
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}



	//==================================================================================================================
	//===============================================PEMBAYARAN=========================================================
	//==================================================================================================================
	public function payment_list()
	{
		// $results = $this->pembayaran_material_model->get_data_json_request_payment_header("status>0 and tipe='material'");
		// $results = $this->db->get_where('payment_approve', ['status' => 2])->result();
		$results = $this->db
			->select('a.*')
			->from('payment_approve a')
			->join('tr_expense b', 'b.no_doc = a.no_doc')
			->where('a.status', 2)
			->where('b.exp_inv_po', 1)
			->where('a.id_payment <>', null)
			->where('a.id_payment <>', '')
			->group_by('a.id_payment')
			->order_by('a.created_on', 'DESC')
			->get()
			->result();

		$results2 = $this->db->query("SELECT a.* FROM payment_approve a LEFT JOIN tr_expense b ON b.no_doc = a.no_doc WHERE a.status = 2 AND a.no_doc NOT LIKE '%INV-%' AND a.no_doc NOT LIKE '%PI-%' AND (a.id_payment IS NOT NULL AND a.id_payment <> '') GROUP BY a.id_payment ORDER BY a.created_on DESC")->result();
		// ->select('a.*')
		// ->from('payment_approve a')
		// ->join('tr_expense b', 'b.no_doc = a.no_doc', 'left')
		// ->where('a.status', 2)
		// ->where('(SELECT COUNT(aa.id) FROM tr_expense aa WHERE aa.no_doc = a.no_doc AND (aa.exp_inv_po IS NULL OR aa.exp_inv_po = "")) <=', 0)
		// ->group_by('a.id')
		// ->order_by('a.created_on', 'DESC')
		// ->get()
		// ->result();


		// $results2 = $this->pembayaran_material_model->get_data_json_request_payment_header("status>0 and tipe='nonmaterial'");
		// $data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data = array(
			'title'			=> 'Payment List',
			'action'		=> 'index',
			'data_status'	=> $this->data_status,
			'results'		=> $results,
			'results2' => $results2
		);
		// history('List Payment');
		$this->template->set($data);
		$this->template->render('index_payment_new');
	}


	public function form_payment_new()
	{

		$id_payment = explode(';', $_GET['id_payment']);

		// $dataid = implode("','", $request_id);
		// $results = $this->pembayaran_material_model->get_data_json_request_payment("id in ('" . $dataid . "')");
		// $data_Group	= $this->master_model->getArray('groups', array(), 'id', 'name');
		// $datacoa	= $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
		// $data = array(
		// 	'title'			=> 'Form Payment',
		// 	'action'		=> 'index',
		// 	'datacoa'		=> $datacoa,
		// 	'row_group'		=> $data_Group,
		// 	'akses_menu'	=> $Arr_Akses,
		// 	'results'		=> $results,
		// );
		// history('Form Payment');
		// $this->load->view('Pembayaran_material/form_payment_new.php', $data);

		$check_transpoty_driver = $this->Pembayaran_material_model->check_transport_payment($id_payment);

		$jurnal_refill_petty_cash = '';
		if ($check_transpoty_driver > 0) {
			$jurnal_refill_petty_cash = $this->Pembayaran_material_model->jurnal_refill_petty_cash($id_payment);
		}

		$get_payment = $this->db
			->select('a.*')
			->from('payment_approve a')
			->where_in('a.id', $id_payment)
			->get()
			->result();
		$get_supplier = $this->db->get('new_supplier')->result();
		// $get_bank = $this->db->get_where(DBACC . '.coa_master', ['kode_bank <>' => '', 'kode_bank <>' => null])->result();
		$get_mata_uang = $this->db->get_where('mata_uang', ['deleted_by' => 0, 'activation' => 'active'])->result();

		$this->db->select('a.id, a.rekening, a.nama, a.coa_bank, b.nama_bank, c.nama as nm_coa');
		$this->db->from('ms_bank a');
		$this->db->join('list_bank b', 'b.id = a.bank', 'left');
		$this->db->join(DBACC . '.coa_master c', 'c.no_perkiraan = a.coa_bank', 'left');
		$this->db->where('a.deleted', '0');
		$get_bank = $this->db->get()->result();

		$data = [
			'id_payment' => implode(',', $id_payment),
			'result_payment' => $get_payment,
			'list_supplier' => $get_supplier,
			'list_bank' => $get_bank,
			'list_mata_uang' => $get_mata_uang,
			'jurnal_refill_petty_cash' => $jurnal_refill_petty_cash
		];
		$this->template->set('results', $data);
		$this->template->render('form_payment_new');
	}

	public function save_payment_new()
	{
		$id_req = $this->input->post("id_req");
		$payment_date = $this->input->post("payment_date");
		$bank_coa = $this->input->post("bank_coa");
		$bank_nilai = $this->input->post("bank_nilai");
		$curs = $this->input->post("curs");
		$id_supplier = $this->input->post("id_supplier");
		$curs_header = $this->input->post("curs_header");

		$biaya_admin_forex = $this->input->post("biaya_admin_forex");
		$biaya_admin = $this->input->post("biaya_admin");
		$curs_admin = $this->input->post("curs_admin");

		$biaya_admin_forex2 = $this->input->post("biaya_admin_forex2");
		$biaya_admin2 = $this->input->post("biaya_admin2");
		$curs_admin2 = $this->input->post("curs_admin2");
		$bank_coa_admin = $this->input->post("bank_coa_admin");

		$nilai_bayar_bank = $this->input->post("nilai_bayar_bank");

		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');
		$alokasi_dp = $this->input->post("alokasi_dp");
		$alokasi_hutang = $this->input->post("alokasi_hutang");
		$tipetrans = $this->input->post("tipetrans");
		$selisih_kurs1 = 0;
		$this->db->trans_begin();
		$jenis_jurnal = 'BUK20';
		if ($curs_header != 'IDR') $jenis_jurnal = 'BUK21';
		try {

			$no_payment = $this->All_model->GetAutoGenerate('format_payment');
			$nomor_jurnal = $jenis_jurnal . $no_payment . rand(100, 999);

			$dataheader =  array(
				'no_payment' => $no_payment,
				'id_supplier' => $id_supplier,
				'curs_header' => $curs_header,
				'payment_date' => $payment_date,
				'bank_coa' => $bank_coa,
				'nilai_bayar_bank' => $nilai_bayar_bank,
				'curs' => $curs,
				'bank_nilai' => $bank_nilai,
				'modul' => 'PO',
				'biaya_admin_forex' => $biaya_admin_forex,
				'biaya_admin' => $biaya_admin,
				'curs_admin' => $curs_admin,
				'biaya_admin_forex2' => $biaya_admin_forex2,
				'biaya_admin2' => $biaya_admin2,
				'curs_admin2' => $curs_admin2,
				'bank_coa_admin' => $bank_coa_admin,
				'status' => '1',
				'created_on' => date('Y-m-d H:i:s'),
				'created_by' => $Username
			);

			$this->All_model->dataSave('purchase_order_request_payment_header', $dataheader);
			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('1','4','8') order by parameter_no")->result();
			$det_Jurnaltes1 = array();
			$total = 0;
			foreach ($datajurnal1 as $rec) {
				// CASH BANK
				if ($rec->parameter_no == "1") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => ($bank_nilai),
						'debet' => 0,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => $nilai_bayar_bank,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK EXPENSE
				if ($rec->parameter_no == "4") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin2,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK
				if ($rec->parameter_no == "8") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin,
						'debet' => 0,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin2,
						'debet' => 0,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
			}
			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);
			foreach ($id_req as $keys) {
				$this->All_model->DataUpdate('purchase_order_request_payment', array('status' => '2', 'payment_date' => $payment_date, 'no_payment' => $no_payment), array('id' => $keys));
				$data = $this->db->query("select * from purchase_order_request_payment where id='" . $keys . "'")->row();
				$selisih_kurs = 0;
				$nilai_terima_barang_idr = 0;
				$datapoheader = $this->db->query("select * from tran_material_po_header where no_po='" . $data->no_po . "'")->row();
				if ($datapoheader->terima_barang_idr != 0) {
					$kurs_hutang = $datapoheader->kurs_terima;
					$selisih_kurs = (($data->nilai_po_invoice * $curs) - $data->nilai_po_invoice * $datapoheader->kurs_terima);

					if ($selisih_kurs < 0) {
						$selisih_kurs1 = $selisih_kurs * (-1);
					} else {
						$selisih_kurs1 = $selisih_kurs;
					}
				} else {

					$kurs_hutang = $data->kurs_receive_invoice;

					$selisih_kurs = (($data->nilai_po_invoice * $curs) - $data->nilai_po_invoice * $data->kurs_receive_invoice);

					if ($selisih_kurs < 0) {
						$selisih_kurs1 = $selisih_kurs * (-1);
					} else {
						$selisih_kurs1 = $selisih_kurs;
					}
				}


				// update PO
				$nilai_dp_kurs = 0;
				$addsql = "";
				if ($data->tipe == 'TR-01') {
					$nilai_dp_kurs = ($data->nilai_po_invoice * $curs);
					$addsql = ", nilai_dp_kurs=" . $nilai_dp_kurs . "";
				}

				if ($data->tipe == 'TR-01') {
					$this->db->query("update tran_material_po_header set terima_barang_kurs=0, terima_barang_idr=0
				" . $addsql . ", total_bayar=(total_bayar+" . ($data->nilai_po_invoice) . "),
				total_bayar_rupiah=(total_bayar_rupiah+" . ($data->nilai_po_invoice * $curs) . "),
				bayar_kurs=(bayar_kurs+" . ($data->nilai_po_invoice) . "),
				bayar_idr=(bayar_idr+" . ($data->nilai_po_invoice * $curs) . ")
				" .
						($data->tipe == 'TR-01' ?
							",nilai_dp=(nilai_dp+" . $data->nilai_po_invoice . "), sisa_dp=(sisa_dp+" . $data->nilai_po_invoice . ")" :
							", nilai_dp=(nilai_dp-" . $data->potongan_dp . "), sisa_dp=(sisa_dp-" . $data->potongan_dp . ")") .
						" where no_po='" . $data->no_po . "'");
				}

				if ($data->tipe == 'TR-02') {
					$this->db->query("update tran_material_po_header set terima_barang_kurs=0, terima_barang_idr=0
				" . $addsql . ", total_bayar=(total_bayar+" . ($data->nilai_po_invoice) . "),
				total_bayar_rupiah=(total_bayar_rupiah+" . ($data->nilai_po_invoice * $curs) . "),
				bayar_kurs=(bayar_kurs+" . ($data->nilai_po_invoice) . "),
				bayar_idr=(bayar_idr+" . ($data->nilai_po_invoice * $curs) . "),
				sisa_hutang_kurs=(sisa_hutang_kurs-" . ($data->nilai_po_invoice) . "),
				sisa_hutang_idr=(sisa_hutang_idr-" . ($data->nilai_po_invoice * $curs) . ")				
				" .
						($data->tipe == 'TR-01' ?
							",nilai_dp=(nilai_dp+" . $data->nilai_po_invoice . "), sisa_dp=(sisa_dp+" . $data->nilai_po_invoice . ")" :
							", nilai_dp=(nilai_dp-" . $data->potongan_dp . "), sisa_dp=(sisa_dp-" . $data->potongan_dp . ")") .
						" where no_po='" . $data->no_po . "'");
				}

				$keterangan		= 'Pembayaran ' . $no_payment;
				$data_coa 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='3'")->row();
				$data_supplier 	= $this->db->query("select * from supplier where id_supplier='" . $data->id_supplier . "'")->row();
				$data_coa2 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='2'")->row();

				if ($data->curs_header == 'IDR') {
					$coahutang = '2101-01-01';
				} else {
					$coahutang = '2101-01-04';
				}

				if ($data->tipe == 'TR-01') {
					$datahutang = array(
						'tipe'       	 => 'BUK',
						'nomor'       	 => $Nomor_JV,
						'tanggal'        => $tanggal,
						'no_perkiraan'   => $coahutang,
						'keterangan'     => $keterangan,
						'no_reff'     	 => $data->no_po,
						'debet'      	 => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
						'kredit'         => 0,
						'id_supplier'    => $data->id_supplier,
						'nama_supplier'  => $data_supplier->nm_supplier,
						'no_request'     => $no_payment,
						'debet_usd'		 => (($curs_header != 'IDR') ? ($data->nilai_po_invoice + $data->invoice_ppn) : 0),
						'kredit_usd'	=> 0,

					);
					$this->db->insert('tr_kartu_hutang', $datahutang);
				}


				if ($data->tipe == 'TR-02') {
					$datahutang = array(
						'tipe'       	 => 'BUK',
						'nomor'       	 => $Nomor_JV,
						'tanggal'        => $tanggal,
						'no_perkiraan'   => $coahutang,
						'keterangan'     => $keterangan,
						'no_reff'     	 => $data->no_po,
						'debet'      	 => (($data->nilai_po_invoice + $data->invoice_ppn) * $kurs_hutang),
						'kredit'         => 0,
						'id_supplier'    => $data->id_supplier,
						'nama_supplier'  => $data_supplier->nm_supplier,
						'no_request'     => $no_payment,
						'debet_usd'		 => (($curs_header != 'IDR') ? ($data->nilai_po_invoice + $data->invoice_ppn) : 0),
						'kredit_usd'	=> 0,

					);
					$this->db->insert('tr_kartu_hutang', $datahutang);
				}

				$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('2','3','5','6','7','9') order by parameter_no")->result();
				foreach ($datajurnal1 as $rec) {
					if ($data->modul == 'PO') {
						// UANG MUKA
						if ($rec->parameter_no == "2") {
							if ($data->tipe == 'TR-01') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => round(($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
									'nilai_valas_debet' => ($data->nilai_po_invoice + $data->invoice_ppn),
									'nilai_valas_kredit' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								if ($data->potongan_dp > 0) {
									$det_Jurnaltes1[] = array(
										'nomor' => $nomor_jurnal,
										'tanggal' => $payment_date,
										'tipe' => 'BUK',
										'no_perkiraan' => $rec->no_perkiraan,
										'keterangan' => $data->keterangan,
										'no_request' => $data->no_po,
										'debet' => 0,
										'kredit' => 0,
										'nilai_valas_debet' => 0,
										'nilai_valas_kredit' => 0,
										'no_reff' => $no_payment,
										'jenis_jurnal' => $jenis_jurnal,
										'nocust' => $data->id_supplier,
										'stspos' => '1'
									);
								}
							}
						}
						// HUTANG
						if ($rec->parameter_no == "3") {
							if ($data->tipe == 'TR-02') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => round((($data->nilai_po_invoice + $data->invoice_ppn) * $kurs_hutang)),
									'nilai_valas_debet' => ($data->nilai_po_invoice + $data->invoice_ppn),
									'nilai_valas_kredit' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => 0,
									'nilai_valas_debet' => 0,
									'nilai_valas_kredit' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							}
						}
					}

					if ($data->modul == 'FORWARDER') {
						// HUTANG FORWARDER
						if ($rec->parameter_no == "5") {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => 'FORWARDER ',
								'no_request' => $data->no_po,
								'kredit' => 0,
								'debet' => round(($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
								'nilai_valas_debet' => 0,
								'nilai_valas_kredit' => 0,
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// PPN
					if ($rec->parameter_no == "6") {
						/*
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUK', 'no_perkiraan' => $rec->no_perkiraan, 'keterangan' => $data->keterangan, 'no_request' => $data->no_po, 'kredit' => 0, 'debet' => ($data->invoice_ppn*$curs), 'no_reff' => $no_payment, 'jenis_jurnal'=>$jenis_jurnal, 'nocust'=>$data->id_supplier
					);
*/
					}
					// PPH
					if ($rec->parameter_no == "7") {
						if ($data->nilai_pph_invoice <> 0) {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $data->coa_pph,
								'keterangan' => $data->keterangan,
								'no_request' => $data->no_po,
								'kredit' => round($data->nilai_pph_invoice * $curs),
								'debet' => 0,
								'nilai_valas_debet' => 0,
								'nilai_valas_kredit' => 0,
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// SELISIH KURS
					if ($rec->parameter_no == "9") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => $data->keterangan,
							'no_request' => $data->no_po,
							'kredit' => round($selisih_kurs < 0 ? ($selisih_kurs * -1) : 0),
							'debet' => round($selisih_kurs >= 0 ? $selisih_kurs : 0),
							'nilai_valas_debet' => 0,
							'nilai_valas_kredit' => 0,
							'no_reff' => $no_payment,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $data->id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$this->db->insert_batch('jurnaltras', $det_Jurnaltes1);

			//auto jurnal

			foreach ($det_Jurnaltes1 as $vals) {
				$datadetail = array(
					'tipe'			=> 'BUK',
					'nomor'			=> $Nomor_JV,
					'tanggal'		=> $tanggal,
					'no_perkiraan'	=> $vals['no_perkiraan'],
					'keterangan'	=> $vals['keterangan'],
					'no_reff'		=> $vals['no_reff'],
					'debet'			=> $vals['debet'],
					'kredit'		=> $vals['kredit'],
					'nilai_valas_debet'			=> $vals['nilai_valas_debet'],
					'nilai_valas_kredit'		=> $vals['nilai_valas_kredit'],
				);
				$total = ($total + $vals['debet']);
				$this->db->insert(DBACC . '.jurnal', $datadetail);
			}

			$keterangan		= 'Pembayaran ' . $no_payment;
			$dataJVhead = array(
				'nomor' 	    	=> $Nomor_JV,
				'tgl'	         	=> $tanggal,
				'jml'	            => $total,
				'jenis_ap'	        => 'V',
				'bayar_kepada'		=> $data_supplier->nm_supplier,
				'kdcab'				=> '101',
				'jenis_reff' 		=> 'BUK',
				'no_reff' 			=> $no_payment,
				'note'				=> $keterangan,
				'user_id'			=> $Username,
				'ho_valid'			=> '',
			);

			$this->db->insert(DBACC . '.japh', $dataJVhead);
			$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
			$this->db->query($Qry_Update_Cabang_acc);


			//end auto jurnal

			$this->db->trans_complete();
			if ($this->db->trans_status()) {
				$this->db->trans_commit();
				$result         = TRUE;
				history('Save Payment');
			} else {
				$this->db->trans_rollback();
				$result = FALSE;
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$result = FALSE;
		}

		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
	public function view_payment_new($id)
	{
		$list_id_payment = [];
		$get_id_payment = $this->db->select('a.id')->get_where('payment_approve a', ['a.id_payment' => $id])->result();
		foreach ($get_id_payment as $item_id_payment) {
			$list_id_payment[] = $item_id_payment->id;
		}
		$list_id_payment = implode(';', $list_id_payment);

		$id_payment = explode(';', $list_id_payment);

		// $dataid = implode("','", $request_id);
		// $results = $this->pembayaran_material_model->get_data_json_request_payment("id in ('" . $dataid . "')");
		// $data_Group	= $this->master_model->getArray('groups', array(), 'id', 'name');
		// $datacoa	= $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
		// $data = array(
		// 	'title'			=> 'Form Payment',
		// 	'action'		=> 'index',
		// 	'datacoa'		=> $datacoa,
		// 	'row_group'		=> $data_Group,
		// 	'akses_menu'	=> $Arr_Akses,
		// 	'results'		=> $results,
		// );
		// history('Form Payment');
		// $this->load->view('Pembayaran_material/form_payment_new.php', $data);

		$get_payment = $this->db
			->select('a.*')
			->from('payment_approve a')
			->where_in('a.id', $id_payment)
			->get()
			->result();
		$get_supplier = $this->db->get('new_supplier')->result();
		$get_bank = $this->db->get_where(DBACC . '.coa_master', ['kode_bank <>' => '', 'kode_bank <>' => null])->result();
		$get_mata_uang = $this->db->get_where('mata_uang', ['deleted_by' => 0, 'activation' => 'active'])->result();

		$get_payment_header = $this->db
			->select('a.*')
			->from('payment_approve a')
			->where_in('a.id', $id_payment)
			->group_by('a.id_payment')
			->get()
			->row();

		$bank_charge = 0;
		$get_bank_charge = $this->db->get_where('tr_payment_paid a', ['a.id' => $id])->row();
		if (!empty($get_bank_charge)) {
			$bank_charge = $get_bank_charge->bank_charge;
		}

		$data = [
			'id_payment' => implode(',', $id_payment),
			'result_header' => $get_payment_header,
			'result_payment' => $get_payment,
			'list_supplier' => $get_supplier,
			'list_bank' => $get_bank,
			'list_mata_uang' => $get_mata_uang,
			'bank_charge' => $bank_charge
		];
		$this->template->set('results', $data);
		$this->template->render('view_payment_new');
	}



	//==================================================================================================================
	//================================================== PAYMENT JURNAL ================================================
	//==================================================================================================================



	public function save_payment_new_nonmaterial()
	{
		$id_req = $this->input->post("id_req");
		$payment_date = $this->input->post("payment_date");
		$bank_coa = $this->input->post("bank_coa");
		$bank_nilai = $this->input->post("bank_nilai");
		$curs = $this->input->post("curs");
		$id_supplier = $this->input->post("id_supplier");
		$curs_header = $this->input->post("curs_header");

		$biaya_admin_forex = $this->input->post("biaya_admin_forex");
		$biaya_admin = $this->input->post("biaya_admin");
		$curs_admin = $this->input->post("curs_admin");

		$biaya_admin_forex2 = $this->input->post("biaya_admin_forex2");
		$biaya_admin2 = $this->input->post("biaya_admin2");
		$curs_admin2 = $this->input->post("curs_admin2");
		$bank_coa_admin = $this->input->post("bank_coa_admin");

		$nilai_bayar_bank = $this->input->post("nilai_bayar_bank");

		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');
		$alokasi_dp = $this->input->post("alokasi_dp");
		$alokasi_hutang = $this->input->post("alokasi_hutang");
		$tipetrans = $this->input->post("tipetrans");
		$this->db->trans_begin();
		$jenis_jurnal = 'BUK20';
		if ($curs_header != 'IDR') $jenis_jurnal = 'BUK21';
		try {

			$no_payment = $this->All_model->GetAutoGenerate('format_payment');
			$nomor_jurnal = $jenis_jurnal . $no_payment . rand(100, 999);

			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);

			$dataheader =  array(
				'no_payment' => $no_payment,
				'id_supplier' => $id_supplier,
				'curs_header' => $curs_header,
				'payment_date' => $payment_date,
				'bank_coa' => $bank_coa,
				'nilai_bayar_bank' => $nilai_bayar_bank,
				'curs' => $curs,
				'bank_nilai' => $bank_nilai,
				'modul' => 'PO',
				'tipe' => 'nonmaterial',
				'biaya_admin_forex' => $biaya_admin_forex,
				'biaya_admin' => $biaya_admin,
				'curs_admin' => $curs_admin,
				'biaya_admin_forex2' => $biaya_admin_forex2,
				'biaya_admin2' => $biaya_admin2,
				'curs_admin2' => $curs_admin2,
				'bank_coa_admin' => $bank_coa_admin,
				'status' => '1',
				'created_on' => date('Y-m-d H:i:s'),
				'created_by' => $Username
			);

			$this->All_model->dataSave('purchase_order_request_payment_header', $dataheader);
			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('1','4','8') order by parameter_no")->result();
			$det_Jurnaltes1 = array();
			foreach ($datajurnal1 as $rec) {
				// CASH BANK
				if ($rec->parameter_no == "1") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => ($bank_nilai),
						'debet' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK EXPENSE
				if ($rec->parameter_no == "4") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin2,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK
				if ($rec->parameter_no == "8") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin,
						'debet' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin2,
						'debet' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
			}
			foreach ($id_req as $keys) {
				$this->All_model->DataUpdate('purchase_order_request_payment_nm', array('status' => '2', 'payment_date' => $payment_date, 'no_payment' => $no_payment), array('id' => $keys));
				$data = $this->db->query("select * from purchase_order_request_payment_nm where id='" . $keys . "'")->row();
				$selisih_kurs = 0;
				$nilai_terima_barang_idr = 0;
				$datapoheader = $this->db->query("select * from tran_po_header where no_po='" . $data->no_po . "'")->row();
				if ($datapoheader->terima_barang_idr != 0) {
					$selisih_kurs = (($data->nilai_po_invoice * $curs) - $datapoheader->terima_barang_idr);
				}
				// update PO
				$nilai_dp_kurs = 0;
				$addsql = "";
				if ($data->tipe == 'TR-01') {
					$nilai_dp_kurs = ($data->nilai_po_invoice * $curs);
					$addsql = ", nilai_dp_kurs=" . $nilai_dp_kurs . "";
				}
				$this->db->query("update tran_po_header set terima_barang_kurs=0, terima_barang_idr=0
				" . $addsql . ", total_bayar=(total_bayar+" . ($data->nilai_po_invoice) . "),
				total_bayar_rupiah=(total_bayar_rupiah+" . ($data->nilai_po_invoice * $curs) . ")
				" .
					($data->tipe == 'TR-01' ?
						",nilai_dp=(nilai_dp+" . $data->nilai_po_invoice . "), sisa_dp=(sisa_dp+" . $data->nilai_po_invoice . ")" :
						", nilai_dp=(nilai_dp-" . $data->potongan_dp . "), sisa_dp=(sisa_dp-" . $data->potongan_dp . ")") .
					" where no_po='" . $data->no_po . "'");

				$keterangan		= 'Pembayaran ' . $no_payment;
				$data_coa 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='3'")->row();
				$data_supplier 	= $this->db->query("select * from supplier where id_supplier='" . $data->id_supplier . "'")->row();
				$datahutang = array(
					'tipe'       	 => 'BUK',
					'nomor'       	 => $Nomor_JV,
					'tanggal'        => $tanggal,
					'no_perkiraan'   => $data_coa->no_perkiraan,
					'keterangan'     => $keterangan,
					'no_reff'     	 => $data->no_po,
					'debet'      	 => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
					'kredit'         => 0,
					'id_supplier'    => $data->id_supplier,
					'nama_supplier'  => $data_supplier->nm_supplier,
					'no_request'     => $no_payment,
					'debet_usd'		 => (($curs_header != 'IDR') ? ($data->nilai_po_invoice + $data->invoice_ppn) : 0),
					'kredit_usd'	=> 0,
				);
				$this->db->insert('tr_kartu_hutang', $datahutang);


				if ($data->curs_header == 'IDR') {
					$coahutang = '2101-01-01';
				} else {
					$coahutang = '2101-01-04';
				}


				$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('2','3','5','6','7','9') order by parameter_no")->result();
				foreach ($datajurnal1 as $rec) {
					if ($data->modul == 'PO') {
						// UANG MUKA
						if ($rec->parameter_no == "2") {
							if ($data->tipe == 'TR-01') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								if ($data->potongan_dp > 0) {
									$det_Jurnaltes1[] = array(
										'nomor' => $nomor_jurnal,
										'tanggal' => $payment_date,
										'tipe' => 'BUK',
										'no_perkiraan' => $coahutang,
										'keterangan' => $data->keterangan,
										'no_request' => $data->no_po,
										'debet' => 0,
										'kredit' => 0,
										'no_reff' => $no_payment,
										'jenis_jurnal' => $jenis_jurnal,
										'nocust' => $data->id_supplier,
										'stspos' => '1'
									);
								}
							}
						}
						// HUTANG
						if ($rec->parameter_no == "3") {
							if ($data->tipe == 'TR-02') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $rec->no_perkiraan,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $rec->no_perkiraan,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							}
						}
					}

					if ($data->modul == 'FORWARDER') {
						// HUTANG FORWARDER
						if ($rec->parameter_no == "5") {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => 'FORWARDER ',
								'no_request' => $data->no_po,
								'kredit' => 0,
								'debet' => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// PPN
					if ($rec->parameter_no == "6") {
						/*
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUK', 'no_perkiraan' => $rec->no_perkiraan, 'keterangan' => $data->keterangan, 'no_request' => $data->no_po, 'kredit' => 0, 'debet' => ($data->invoice_ppn*$curs), 'no_reff' => $no_payment, 'jenis_jurnal'=>$jenis_jurnal, 'nocust'=>$data->id_supplier
					);
*/
					}
					// PPH
					if ($rec->parameter_no == "7") {
						if ($data->nilai_pph_invoice <> 0) {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => $data->keterangan,
								'no_request' => $data->no_po,
								'kredit' => ($data->nilai_pph_invoice * $curs),
								'debet' => 0,
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// SELISIH KURS
					if ($rec->parameter_no == "9") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => $data->keterangan,
							'no_request' => $data->no_po,
							'kredit' => ($selisih_kurs < 0 ? ($selisih_kurs * -1) : 0),
							'debet' => ($selisih_kurs >= 0 ? $selisih_kurs : 0),
							'no_reff' => $no_payment,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $data->id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$this->db->insert_batch('jurnaltras', $det_Jurnaltes1);


			//auto jurnal
			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);
			$total = 0;
			foreach ($det_Jurnaltes1 as $vals) {
				$datadetail = array(
					'tipe'			=> 'BUK',
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

			$dataJVhead = array(
				'nomor' 	    	=> $Nomor_JV,
				'tgl'	         	=> $tanggal,
				'jml'	            => $total,
				'jenis_ap'	        => 'V',
				'bayar_kepada'		=> $data_supplier->nm_supplier,
				'kdcab'				=> '101',
				'jenis_reff' 		=> 'BUK',
				'no_reff' 			=> $no_payment,
				'note'				=> $keterangan,
				'user_id'			=> $Username,
				'ho_valid'			=> '',
			);

			$this->db->insert(DBACC . '.japh', $dataJVhead);
			$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
			$this->db->query($Qry_Update_Cabang_acc);

			//end auto jurnal

			$this->db->trans_complete();
			if ($this->db->trans_status()) {
				$this->db->trans_commit();
				$result         = TRUE;
				history('Save Payment');
			} else {
				$this->db->trans_rollback();
				$result = FALSE;
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$result = FALSE;
		}

		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	public function list_request_payment($jenis_payment)
	{
		$this->template->set('jenis_payment', $jenis_payment);
		$this->template->title('List Request Payment');
		$this->template->render('list_request_payment');
	}

	public function check_payment()
	{
		$id = $this->input->post('id');
		$checked = $this->input->post('checked');

		$this->db->trans_start();

		if ($checked == 1) {
			$this->db->insert('tr_choosed_payment', [
				'id_user' => $this->auth->user_id(),
				'id_payment' => $id
			]);
		} else {
			$this->db->delete('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $id]);
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function clear_choosed_payment()
	{
		$id_user = $this->auth->user_id();

		$this->db->trans_start();

		$this->db->delete('tr_choosed_payment', ['id_user' => $id_user]);

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

	public function proses_payment()
	{
		$id_user = $this->auth->user_id();

		$arr_choosed_payment = [];
		$get_choosed_payment = $this->db->query("SELECT * FROM tr_choosed_payment WHERE id_user = '" . $id_user . "'")->result();
		foreach ($get_choosed_payment as $item) {
			$arr_choosed_payment[] = $item->id_payment;
		}

		echo json_encode([
			'count_choosed_payment' => count($get_choosed_payment),
			'arr_choosed_payment' => implode(';', $arr_choosed_payment)
		]);
	}

	public function save_payment()
	{
		$post = $this->input->post();

		try {
			$get_coa_bank = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $post['bank']])->row();
			$nm_coa_bank = '';
			$kode_bank = '';
			if (!empty($get_coa_bank)) {
				$nm_coa_bank = $get_coa_bank->nama;
				$kode_bank = $get_coa_bank->kode_bank;
			}

			$id_payment_paid = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $post['tgl_bayar']);

			$config['upload_path'] = 'assets/expense/';
			$config['allowed_types'] = '*';
			$config['remove_spaces'] = TRUE;
			$config['encrypt_name'] = TRUE;
			$filenames = '';

			if (!empty($_FILES['upload_doc']['name'])) {
				$_FILES['file']['name'] = $_FILES['upload_doc']['name'];
				$_FILES['file']['type'] = $_FILES['upload_doc']['type'];
				$_FILES['file']['tmp_name'] = $_FILES['upload_doc']['tmp_name'];
				$_FILES['file']['error'] = $_FILES['upload_doc']['error'];
				$_FILES['file']['size'] = $_FILES['upload_doc']['size'];
				// $this->load->library('upload', $config);
				$this->upload->initialize($config);
				if ($this->upload->do_upload('file')) {
					$uploadData = $this->upload->data();
					$filenames = $uploadData['file_name'];
				}
			}

			$insert_payment_paid = $this->db->insert('tr_payment_paid', [
				'id' => $id_payment_paid,
				'bank_charge' => str_replace(',', '', $post['bank_charge']),
				'created_by' => $this->auth->user_id(),
				'created_on' => date('Y-m-d H:i:s')
			]);
			if (!$insert_payment_paid) {
				throw new Exception($this->db->error($insert_payment_paid));
			}

			$this->db->where_in('id', explode(',', $post['id_payment']));
			$update_payment1 = $this->db->update('payment_approve', [
				'id_payment' => $id_payment_paid,
				'tgl_bayar' => $post['tgl_bayar'],
				'supplier' => $post['supplier_input'],
				'keterangan_pembayaran' => $post['keterangan_pembayaran'],
				'coa_bank' => $post['bank'],
				'nm_coa_bank' => $nm_coa_bank,
				'mata_uang' => $post['mata_uang'],
				'payment_bank' => str_replace(',', '', $post['payment_bank']),
				'total_payment' => $post['total_payment'],
				'selisih' => ($post['total_payment'] - str_replace(',', '', $post['payment_bank'])),
				'status' => 2,
				'link_doc' => $filenames,
				'id_supplier' => $post['supplier_input'],
				'nm_supplier' => $post['nm_supplier_input'],
				'kurs_payment' => str_replace(',', '', $post['kurs_payment'])
			]);
			if (!$update_payment1) {
				throw new Exception($this->db->error($update_payment1));
			}

			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$tipe_pph = ($detail['tipe_pph'] == 1) ? 'PPH 23' : 'PPH 22';

					$this->db->where('id', $detail['id_payment']);
					$update_payment_detail = $this->db->update('payment_approve', [
						'total_ppn' => str_replace(',', '', $detail['nilai_ppn']),
						'total_pph' => str_replace(',', '', $detail['nilai_pph']),
						'tipe_pph' => $tipe_pph
					]);

					$kurs_invoice = $detail['kurs_invoice'];
					if (!$update_payment_detail) {
						throw new Exception($this->db->error($update_payment_detail));
						// print_r($this->db->error($update_payment_detail));
						// exit;
					}
				}
			}

			$arr_jurnal = [];
			$no_jurnal = 1;
			if (isset($post['jurnal_ls'])) {
				// print_r($post['jurnal_ls']);
				// exit;
				foreach ($post['jurnal_ls'] as $item_jurnal) {
					// if (isset($item_jurnal['tanggal_jurnal'])) {
					$id_jurnal = $this->Pembayaran_material_model->generate_id_invoice_jurnal($no_jurnal);

					$arr_jurnal[] = [
						'no_jurnal' => $id_jurnal,
						'tgl_jurnal' => date('Y-m-d'),
						'coa' => $item_jurnal['coa'],
						'id_company' => $item_jurnal['id_company'],
						'nm_company' => $item_jurnal['nm_company'],
						'nm_coa' => $item_jurnal['nm_coa'],
						'debit' => $item_jurnal['debit'],
						'kredit' => $item_jurnal['kredit'],
						'keterangan' => $item_jurnal['keterangan'],
						'no_transaksi' => $id_payment_paid,
						'jenis_transaksi' => 'Transport',
						'id_divisi' => $item_jurnal['id_divisi'],
						'nm_divisi' => $item_jurnal['nm_divisi'],
						'created_by' => $this->auth->user_id(),
						'created_date' => date('Y-m-d')
					];

					$no_jurnal++;
					// }
				}
			}
			// else {
			// 	throw new Exception('Data jurnal tidak terdeteksi !');
			// }

			// if (!empty($arr_jurnal)) {
			$insert_jurnal = $this->db->insert_batch('tr_jurnal', $arr_jurnal);
			if (!$insert_jurnal) {
				throw new Exception('Data jurnal gagal dibuat !');
			}

			$no_payment = $post['id_payment'];

			if ($post['mata_uang'] == 'IDR') {
				$jenis_jurnal = 'BUK001';
				$kurs         = 1;
				$selisih      = 0;
				$hutang       = (str_replace(',', '', $post['total_payment']) * $kurs) + (str_replace(',', '', $detail['nilai_ppn']) * $kurs);
			} else {
				$jenis_jurnal = 'BUK004';
				$kurs         = str_replace(',', '', $post['kurs_payment']);
				$selisih      = $kurs - $kurs_invoice;
				$hutang       = (str_replace(',', '', $post['total_payment']) * $kurs_invoice) + (str_replace(',', '', $detail['nilai_ppn']) * $kurs_invoice);
			}

			$bank_coa     = $post['bank'];
			$no_request   = $post['id_payment'];
			$keterangan   = $post['keterangan_pembayaran'];
			$bankcharge   = (str_replace(',', '', $post['bank_charge'])) * $kurs;
			$bank_nilai   = (str_replace(',', '', $post['payment_bank'])) * $kurs;
			$ap           = str_replace(',', '', $post['total_payment']);
			$selisihkurs  = $selisih * $ap;

			if ($selisihkurs < 0) {
				$selisihdebet  = 0;
				$selisihkredit = $selisihkurs * (-1);
			} elseif ($selisihkurs > 0) {
				$selisihdebet  = $selisihkurs;
				$selisihkredit = 0;
			}

			$nomor_jurnal = $nomor_jurnal = $jenis_jurnal . $no_payment . rand(100, 999);
			$payment_date = $post['tgl_bayar'];
			$id_supplier = $post['supplier_input'];
			$nm_supplier = $post['nm_supplier_input'];
			$no_reff     = $post['id_payment'];
			$Username    = $this->auth->user_id();

			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' order by parameter_no")->result();
			$det_Jurnaltes1 = array();
			foreach ($datajurnal1 as $rec) {
				if ($rec->parameter_no == "1") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $no_request . '. ' . $keterangan,
						'no_request' => $id_payment_paid,
						'kredit' => ($bank_nilai),
						'debet' => 0,
						'no_reff' => $no_request,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				if ($rec->parameter_no == "2") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $no_request . '. ' . $keterangan,
						'no_request' => $id_payment_paid,
						'kredit' => 0,
						'debet' => $hutang,
						'no_reff' => $no_request,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}

				if ($rec->parameter_no == "4") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $no_request . '. ' . $keterangan,
						'no_request' => $id_payment_paid,
						'kredit' => 0,
						'debet' => $bankcharge,
						'no_reff' => $no_request,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}

				if ($jenis_jurnal = 'BUK004') {
					if ($rec->parameter_no == "5") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => $no_request . '. ' . $keterangan,
							'no_request' => $id_payment_paid,
							'kredit' => $selisihkredit,
							'debet' => $selisihdebet,
							'no_reff' => $no_request,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$insert_jurnal_tras = $this->db->insert_batch('jurnaltras', $det_Jurnaltes1);
			if (!$insert_jurnal_tras) {
				throw new Exception('Input JurnalTras gagal !');
			}

			//auto jurnal
			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);
			$total = 0;
			foreach ($det_Jurnaltes1 as $vals) {
				$datadetail = array(
					'tipe'			=> 'BUK',
					'nomor'			=> $Nomor_JV,
					'tanggal'		=> $tanggal,
					'no_perkiraan'	=> $vals['no_perkiraan'],
					'keterangan'	=> $vals['keterangan'],
					'no_reff'		=> $vals['no_reff'],
					'debet'			=> $vals['debet'],
					'kredit'		=> $vals['kredit'],
				);
				$total = ($total + $vals['debet']);
				$insert_jurnal_det = $this->db->insert(DBACC . '.jurnal', $datadetail);
				if (!$insert_jurnal_det) {
					throw new Exception('Input Jurnal Detail gagal !');
				}
			}

			$keterangan		= 'Pembayaran ' . $no_reff;
			$dataJVhead = array(
				'nomor' 	    	=> $Nomor_JV,
				'tgl'	         	=> $tanggal,
				'jml'	            => $total,
				'jenis_ap'	        => 'V',
				'bayar_kepada'		=> $nm_supplier,
				'kdcab'				=> '101',
				'jenis_reff' 		=> 'BUK',
				'no_reff' 			=> $no_reff,
				'note'				=> $keterangan,
				'user_id'			=> $Username,
				'ho_valid'			=> '',
			);

			$insert_japh = $this->db->insert(DBACC . '.japh', $dataJVhead);
			if (!$insert_japh) {
				throw new Exception('Insert JAPH gagak !');
			}
			$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
			$this->db->query($Qry_Update_Cabang_acc);

			$data_coa 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='3'")->row();
			$datahutang = array(
				'tipe'       	 => 'BUK',
				'nomor'       	 => $Nomor_JV,
				'tanggal'        => $tanggal,
				'no_perkiraan'   => $data_coa->no_perkiraan,
				'keterangan'     => $keterangan,
				'no_reff'     	 => $no_reff,
				'debet'      	 => $hutang,
				'kredit'         => 0,
				'id_supplier'    => $id_supplier,
				'nama_supplier'  => $nm_supplier,
				'no_request'     => $no_request,
			);
			$insert_kartu_hutang = $this->db->insert('tr_kartu_hutang', $datahutang);
			if (!$insert_kartu_hutang) {
				throw new Exception('Insert Kartu Hutang gagal !');
			}

			//end auto jurnal

			// }

			$valid = 1;
			$pesan = 'Selamat, data telah berhasil dibayar !';
			// }
			$this->db->trans_commit();
			echo json_encode([
				'status' => $valid,
				'pesan' => $pesan
			]);
		} catch (Exception $e) {
			$this->db->trans_rollback();

			$response = [
				'status' => 0,
				'pesan' => $e->getMessage()
			];

			echo json_encode($response);
		}
	}

	public function used_choosed_payment()
	{
		$this->db->trans_start();

		$this->db->delete('tr_choosed_payment', ['id_user' => $this->auth->user_id()]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function get_list_req_payment()
	{
		$this->Pembayaran_material_model->get_list_req_payment();
	}

	public function set_jurnal()
	{
		$this->Pembayaran_material_model->set_jurnal();
	}


	public function set_jurnal_refill()
	{
		$this->Pembayaran_material_model->set_jurnal_refill();
	}
}
