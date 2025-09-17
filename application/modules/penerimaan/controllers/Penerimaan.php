<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penerimaan extends Admin_Controller
{

	protected $viewPermission   = 'Penerimaan_Uang.View';
	protected $addPermission    = 'Penerimaan_Uang.Add';
	protected $managePermission = 'Penerimaan_Uang.Manage';
	protected $deletePermission = 'Penerimaan_Uang.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			'Penerimaan/master_model',
			'Penerimaan/penerimaan_model',
			'Penerimaan/All_model',
			'Penerimaan/Jurnal_model',
			'Penerimaan/Acc_model'
		));

		date_default_timezone_set('Asia/Bangkok');

		$this->id_user  = $this->auth->user_id();
		$this->datetime = date('Y-m-d H:i:s');
	}

	public function index()
	{
		$so = $this->penerimaan_model->get_data_pn();
		$data = array(
			'title'			=> 'Penerimaan',
			'action'		=> 'index',
			'results'		=> $so,
		);
		$this->template->set($data);
		$this->template->render('list_payment');
	}

	public function index_draf()
	{

		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$so = $this->penerimaan_model->get_data_pro();
		$data = array(
			'title'			=> 'Penerimaan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'results'			=> $so,
		);
		history('View Revenue');
		$this->load->view('Penerimaan/list_payment_temp', $data);
	}

	public function penerimaan_buktipotong($kd_bayar)
	{
		$noinvoice = $this->db->query("SELECT no_invoice FROM tr_invoice_payment_detail WHERE kd_pembayaran = '$kd_bayar' ")->result();
		$buktipotong = $this->db->query("SELECT * FROM tr_invoice_bukti_potong WHERE kd_pembayaran = '$kd_bayar' ")->result();
		$data = array(
			'kodebayar' => $kd_bayar,
			'noinvoice' => $noinvoice,
			'buktipotong' => $buktipotong
		);
		$this->load->view('form_buktipotong', $data);
	}

	public function save_buktipotong()
	{
		$data = array(
			'no_invoice' => $this->input->post('no_invoice'),
			'tgl_terima' => $this->input->post('tgl_terima'),
			'kd_pembayaran' => $this->input->post('kd_pembayaran'),
			'no_bukti_potong' => $this->input->post('no_bukti_potong'),
			'created_by' => $this->auth->user_id(),
			'created_date' => date('Y-m-d H:i:s'),
		);
		$this->db->insert('tr_invoice_bukti_potong', $data);
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Return		= array(
				'status'		=> 2,
				'pesan'			=> 'Save Process Failed. Please Try Again...'
			);
		} else {
			$this->db->trans_commit();
			$Arr_Return		= array(
				'status'		=> 1,
				'pesan'			=> 'Save Process Success. '
			);
		}
		echo json_encode($Arr_Return);
	}

	public function create_new()
	{
		$this->auth->restrict($this->viewPermission);

		$this->template->page_icon('fa fa-list');
		$data = '0';
		$this->template->set('results', $data);
		$this->template->title('Indeks Of Invoice');
		$this->template->render('invoice_siap_terima');
	}

	public function server_side_inv()
	{
		$this->penerimaan_model->get_data_json_inv();
	}
	public function create_penerimaan()
	{
		$this->invoicing_model->list_top();
	}

	public function server_side_payment()
	{
		$this->penerimaan_model->get_data_json_payment();
	}
	public function server_side_top()
	{
		$this->invoicing_model->get_data_json_top();
	}

	public function modal_detail_invoice()
	{
		$this->penerimaan_model->modal_detail_invoice($this->uri->segment(3));
	}

	public function modal_detail_invoice_draf()
	{
		$this->penerimaan_model->modal_detail_invoice_draf($this->uri->segment(3));
	}

	public function view_penerimaan()
	{
		$kd_bayar = $this->uri->segment(3);
		$data = array(
			'kodebayar' => $kd_bayar,
		);
		$this->template->set($data);
		$this->template->render('view_penerimaan');
	}

	public function view_penerimaan_draf()
	{
		$kd_bayar = $this->uri->segment(3);
		$data = array(
			'kodebayar' => $kd_bayar,
		);
		$this->load->view('Penerimaan/view_penerimaan_draf', $data);
	}

	public function save_penerimaan()
	{

		// print_r($this->input->post());
		// exit;
		$session = $this->session->userdata('app_session');
		$Tgl_Invoice        = $this->input->post('tgl_bayar');

		$data_session 	    = $this->session->userdata;
		$kd_bayar 			= $this->penerimaan_model->generate_nopn($Tgl_Invoice);

		$this->db->trans_begin();

		if (!empty($this->input->post('bank'))) {
			// $bank = explode('|',$this->input->post('bank'));
			// $kd_bank = $bank[0];
			// $nmbank = $bank[1];

			$kd_bank  = $this->input->post('bank');
		}
		// print_r($kd_bank);
		// exit;
		$matauang = $this->input->post('matauang');
		if ($matauang == 'usd') {
			$kurs = str_replace(",", "", str_replace(',', '',  $this->input->post('kurs')));
		} else if ($matauang == 'idr') {
			$kurs = 1;
		}
		$jumlah_total_idr = number_format(str_replace(",", "", $this->input->post('total_bank')) * $kurs);

		$unlocated =  str_replace(",", "", $this->input->post('total_bank'));
		$id_unlocated = $this->input->post('id_unlocated');

		$lebihbayar =  str_replace(",", "", $this->input->post('pakai_lebih_bayar'));
		$id_lebihbayar = $this->input->post('id_lebihbayar');

		$idcustomer = $this->input->post('customer');

		$customer =  $this->db->query("SELECT * FROM customer WHERE id_customer = '$idcustomer'")->row();

		$idcs   = $customer->id_customer;
		$nmcs	= html_escape($customer->nm_customer);

		$arr_id_invoice = [];
		foreach ($this->input->post('detail') as $item) {
			$arr_id_invoice[] = $item['id_invoice'];
		}

		$arr_id_invoice = implode(',', $arr_id_invoice);


		$data = array(
			'no_invoice' => $arr_id_invoice,
			'kd_pembayaran' => $kd_bayar,
			'jenis_reff' => '-',
			'no_reff' => '-',
			'tgl_pembayaran' => $this->input->post('tgl_bayar'),
			'kurs_bayar' => str_replace(',', '',  $this->input->post('kurs')),
			'jumlah_piutang' => str_replace(",", "", $this->input->post('total_invoice')),
			'jumlah_piutang_idr' => '-',
			'jumlah_bank' => str_replace(",", "", $this->input->post('total_bank')),
			'jumlah_bank_idr' => str_replace(",", "", $this->input->post('total_bank')) * $kurs,
			'jumlah_pembayaran' => str_replace(",", "", $this->input->post('total_terima')),
			'jumlah_pembayaran_idr' => str_replace(",", "", $this->input->post('total_terima')) * $kurs,
			'kd_bank' => $kd_bank,
			'biaya_admin' => str_replace(",", "", $this->input->post('biaya_adm')),
			'biaya_admin_idr' => str_replace(",", "", $this->input->post('biaya_adm')) * $kurs,
			'biaya_pph' => str_replace(",", "", $this->input->post('biaya_pph')),
			'biaya_pph_idr' => str_replace(",", "", $this->input->post('biaya_pph')) * $kurs,
			'created_by'    => $session['id_user'],
			'created_on' => date('Y-m-d H:i:s'),
			'jenis_pph' => $this->input->post('jenis_pph'),
			'no_account' => '-',
			'selisih' => '-',
			'selisih_idr' => '-',
			'keterangan' => $this->input->post('ket_bayar'),
			'id_customer' => $idcs,
			'nm_customer' => $nmcs,
			'lebih_bayar' => str_replace(",", "", $this->input->post('pakai_lebih_bayar')),
			'tambah_lebih_bayar' => str_replace(",", "", $this->input->post('tambah_lebih_bayar')),
		);
		$insert_payment = $this->db->insert('tr_invoice_payment', $data);
		if (!$insert_payment) {
			print_r($this->db->error($insert_payment));
			exit;
		}



		$selisih    = 0;
		$selisihidr = 0;
		$piutangidr = 0;

		foreach ($this->input->post('detail') as $item) {
			$kurs_jual = 1;

			$total_bayar = $item['total_bayar'];
			$pph = $item['pph'];
			if ($pph !== '') {
				$pph = str_replace(',', '', $item['pph']);
			} else {
				$pph = 0;
			}

			$nilai_bayar = round(str_replace(",", "", $total_bayar) * $kurs);
			$nilai_jual  = round($kurs_jual * str_replace(",", "", $total_bayar));
			$pphidr      = round($kurs_jual * str_replace(",", "", $pph));

			$selisih     = $nilai_bayar - $nilai_jual;

			$selisihidr  += $selisih;

			$piutangidr  += $nilai_jual;

			$datadetail = array(
				'kd_pembayaran'     => $kd_bayar,
				'no_invoice'        => $item['id_invoice'],
				'no_ipp'        => $item['id_so'],
				'nm_customer'       => $item['nm_customer'],
				'total_invoice_idr'    => str_replace(",", "", $item['sisa_invoice']),
				'total_bayar'         => str_replace(",", "", $item['total_bayar']),
				'total_bayar_idr'     => round(str_replace(",", "", $item['total_bayar']) * $kurs),
				'sisa_invoice_idr'    => str_replace(",", "", $item['sisa_invoice']) - str_replace(",", "", $item['total_bayar']),
				'jenis_pph'           => $item['jenis_pph'],
				'total_pph'           => $pph,
				'total_pph_idr'       => $pphidr,
				'kurs_jual'				=> $kurs_jual,
				'kurs_bayar'			=> $kurs,
				'total_jual_idr'	    => $nilai_jual,
				'selisih_idr'	        => $selisih,
				'created_on'    => date('Y-m-d H:i:s'),
				'created_by'    => $session['id_user'],
				'sisa_retensi_idr'    => 0,
				'tipe_bayar'           => $item['tipe_bayar']
			);
			$insert_payment_detail = $this->db->insert('tr_invoice_payment_detail', $datadetail);
			if (!$insert_payment_detail) {
				print_r($this->db->error($insert_payment_detail));
				exit;
			}
			//Update QTY_AVL
			$invoice = $item['id_invoice'];
			$jmlbyr  = str_replace(",", "", $item['total_bayar']);
			$jmlbyridr  = round(str_replace(",", "", $item['total_bayar']) * $kurs);

			if ($matauang == 'usd') {
				$Qry_Update	 = "UPDATE tr_invoice_header SET total_bayar=total_bayar + $jmlbyr,total_bayar_idr=total_bayar_idr + $jmlbyridr, sisa_invoice=sisa_invoice - $jmlbyr, sisa_invoice_idr=sisa_invoice_idr - $jmlbyridr, proses_print='2' WHERE no_invoice='$invoice'";
				$update_invoice_header = $this->db->query($Qry_Update);
				if (!$update_invoice_header) {
					print_r($this->db->error($update_invoice_header));
					exit;
				}
			} else if ($matauang == 'idr') {
				$Qry_Update	 = "UPDATE tr_invoice_header SET total_bayar=total_bayar + $jmlbyr,total_bayar_idr=total_bayar_idr + $jmlbyr, sisa_invoice_idr=sisa_invoice_idr - $jmlbyr, sisa_invoice=sisa_invoice - $jmlbyridr, proses_print='2' WHERE no_invoice='$invoice'";
				$update_invoice_header = $this->db->query($Qry_Update);

				if (!$update_invoice_header) {
					print_r($this->db->error($update_invoice_header));
					exit;
				}
			}

			$so  = $this->db->query("SELECT * FROM tr_invoice_payment WHERE no_invoice='$invoice'")->row();
			$no_so = $item['id_so'];

			// $Qry_Update_so	 = "UPDATE so_bf_header SET total_bayar_so=total_bayar_so + $jmlbyr WHERE so_number='$no_so'";
			// $update_qry_so = $this->db->query($Qry_Update_so);

			$update_invoice = $this->db->query("UPDATE tr_invoice_sales SET total_bayar = (total_bayar + " . $jmlbyr . ") WHERE id_invoice = '" . $item['id_invoice'] . "'");
			if (!$update_invoice) {
				print_r($this->db->error($update_invoice));
				exit;
			}

			// $Qry_Update_py	 = "UPDATE tr_invoice_payment SET selisih_idr = selisih_idr + $selisih WHERE kd_pembayaran='$kd_bayar'";
			// $this->db->query($Qry_Update_py);
		}

		// for ($i = 0; $i < count($this->input->post('kode_produk')); $i++) {

		// 	// if ($matauang == 'usd') {
		// 	// 	$kurs_jual = str_replace(",", "", $this->input->post('kurs_jual')[$i]);
		// 	// } else if ($matauang == 'idr') {
		// 	// 	$kurs_jual = 1;
		// 	// }

		// 	$kurs_jual = 1;


		// 	$nilai_bayar = round(str_replace(",", "", $this->input->post('jml_bayar')[$i]) * $kurs);
		// 	$nilai_jual  = round($kurs_jual * str_replace(",", "", $this->input->post('jml_bayar')[$i]));
		// 	$pphidr      = round($kurs_jual * str_replace(",", "", $this->input->post('pph')[$i]));

		// 	$selisih     = $nilai_bayar - $nilai_jual;

		// 	$selisihidr  += $selisih;

		// 	$piutangidr  += $nilai_jual;

		// 	$datadetail = array(
		// 		'kd_pembayaran'     => $kd_bayar,
		// 		'no_invoice'        => $this->input->post('kode_produk')[$i],
		// 		'no_ipp'        => $this->input->post('no_surat')[$i],
		// 		'nm_customer'       => $this->input->post('nm_customer2')[$i],
		// 		'total_invoice_idr'    => str_replace(",", "", $this->input->post('sisa_invoice')[$i]),
		// 		'total_bayar'         => str_replace(",", "", $this->input->post('jml_bayar')[$i]),
		// 		'total_bayar_idr'     => round(str_replace(",", "", $this->input->post('jml_bayar')[$i]) * $kurs),
		// 		'sisa_invoice_idr'    => str_replace(",", "", $this->input->post('sisa_invoice')[$i]) - str_replace(",", "", $this->input->post('jml_bayar')[$i]),
		// 		'jenis_pph'           => str_replace(",", "", $this->input->post('jenis_pph2')[$i]),
		// 		'total_pph'           => str_replace(",", "", $this->input->post('pph')[$i]),
		// 		'total_pph_idr'       => $pphidr,
		// 		'kurs_jual'				=> $kurs_jual,
		// 		'kurs_bayar'			=> $kurs,
		// 		'total_jual_idr'	    => $nilai_jual,
		// 		'selisih_idr'	        => $selisih,
		// 		'created_on'    => date('Y-m-d H:i:s'),
		// 		'created_by'    => $session['id_user'],
		// 		'sisa_retensi_idr'    => str_replace(",", "", $this->input->post('sisa_retensi')[$i]),
		// 		'tipe_bayar'           => $this->input->post('tipe_bayar')[$i]
		// 	);
		// 	$this->db->insert('tr_invoice_payment_detail', $datadetail);
		// 	//Update QTY_AVL
		// 	$invoice = $this->input->post('kode_produk')[$i];
		// 	$jmlbyr  = str_replace(",", "", $this->input->post('jml_bayar')[$i]);
		// 	$jmlbyridr  = round(str_replace(",", "", $this->input->post('jml_bayar')[$i]) * $kurs);

		// 	if ($matauang == 'usd') {
		// 		$Qry_Update	 = "UPDATE tr_invoice_header SET total_bayar=total_bayar + $jmlbyr,total_bayar_idr=total_bayar_idr + $jmlbyridr, sisa_invoice=sisa_invoice - $jmlbyr, sisa_invoice_idr=sisa_invoice_idr - $jmlbyridr, proses_print='2' WHERE no_invoice='$invoice'";
		// 		$this->db->query($Qry_Update);
		// 	} else if ($matauang == 'idr') {
		// 		$Qry_Update	 = "UPDATE tr_invoice_header SET total_bayar=total_bayar + $jmlbyr,total_bayar_idr=total_bayar_idr + $jmlbyr, sisa_invoice_idr=sisa_invoice_idr - $jmlbyr, sisa_invoice=sisa_invoice - $jmlbyridr, proses_print='2' WHERE no_invoice='$invoice'";
		// 		$this->db->query($Qry_Update);
		// 	}

		// 	$so  = $this->db->query("SELECT * FROM tr_invoice_payment WHERE no_invoice='$invoice'")->row();
		// 	$no_so = $this->input->post('no_surat')[$i];

		// 	$Qry_Update_so	 = "UPDATE so_bf_header SET total_bayar_so=total_bayar_so + $jmlbyr WHERE so_number='$no_so'";
		// 	$this->db->query($Qry_Update_so);

		// 	// $Qry_Update_py	 = "UPDATE tr_invoice_payment SET selisih_idr = selisih_idr + $selisih WHERE kd_pembayaran='$kd_bayar'";
		// 	// $this->db->query($Qry_Update_py);


		// }
		$tambah_lebih_bayar = $this->input->post('tambah_lebih_bayar');


		if ($tambah_lebih_bayar != 0) {



			$data_lebih_bayar[]			= array(
				'tgl'                => $this->input->post('tgl_bayar'),
				'keterangan'         => $nmcs,
				'totalpenerimaan'    => str_replace(",", "", $this->input->post('tambah_lebih_bayar')),
				'saldo'              => str_replace(",", "", $this->input->post('tambah_lebih_bayar')),
				'created_on'         => date('Y-m-d H:i:s'),
				'created_by'         => $session['id_user'],
				'bank'         	  => $this->input->post('bank')

			);


			$this->db->insert_batch('tr_unlocated_bank', $data_lebih_bayar);

			$Nomor_BUM				= $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $Tgl_Invoice);

			// $Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $Tgl_Invoice);
			$Keterangan_INV1 = 'LEBIH BAYAR ' . $nmcs;
			$Jml_Ttl  = str_replace(",", "", $this->input->post('tambah_lebih_bayar'));
			$Bln = substr($Tgl_Invoice, 5, 2);
			$Thn = substr($Tgl_Invoice, 0, 4);

			// $dataJVhead = array(
			// 'nomor' => $Nomor_JV, 
			// 'tgl' => $Tgl_Invoice,
			// 'jml' => $Jml_Ttl, 
			// 'koreksi_no' => '-', 
			// 'kdcab' => '101', 
			// 'jenis' => 'JV', 
			// 'keterangan' => $Keterangan_INV1, 
			// 'bulan' => $Bln, 
			// 'tahun' => $Thn, 
			// 'user_id' => $session['id_user'], 
			// 'memo' => '', 
			// 'tgl_jvkoreksi' => $Tgl_Invoice, 
			// 'ho_valid' => ''
			// );

			$dataJARH2 = array(
				'nomor' 	    	=> $Nomor_BUM,
				'kd_pembayaran'    	=> $kd_bayar,
				'tgl'	         	=> $Tgl_Invoice,
				'jml'	            => $Jml_Ttl,
				'kdcab'				=> '101',
				'jenis_reff'		=> $kd_bayar,
				'no_reff'		    => $kd_bayar,
				'customer'		    => $nmcs,
				'terima_dari'		=> '-',
				'jenis_ar'		    => 'V',
				'note'				=> $Keterangan_INV1,
				'valid'				=> $session['id_user'],
				'tgl_valid'			=> $Tgl_Invoice,
				'user_id'			=> $session['id_user'],
				'tgl_invoice'	    => $Tgl_Invoice,
				'ho_valid'			=> '',
				'batal'			    => '0'
			);

			$det_Jurnal_lebih  = array();
			$det_Jurnal_lebih[] = array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Invoice,
				'tipe'          => 'JV',
				'no_perkiraan'  => $kd_bank,
				'keterangan'    => $Keterangan_INV1,
				'no_reff'       => $kd_bayar,
				'debet'         => $Jml_Ttl,
				'kredit'        => 0
			);


			$det_Jurnal_lebih[] = array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Invoice,
				'tipe'          => 'JV',
				'no_perkiraan'  => '2109-02-01',
				'keterangan'    => $Keterangan_INV1,
				'no_reff'       => $kd_bayar,
				'debet'         => 0,
				'kredit'        => $Jml_Ttl
			);



			// $this->db->insert(DBACC.'.JARH',$dataJARH2);
			// $this->db->insert_batch(DBACC.'.jurnal',$det_Jurnal_lebih);

			//$this->db->insert(DBACC.'.JARH',$dataJARH2);
			//$this->db->insert_batch(DBACC.'.jurnal',$det_Jurnal_lebih);

			//$Qry_Update_Cabang_acc	 = "UPDATE ".DBACC.".pastibisa_tb_cabang SET nobum=nobum + 1 WHERE nocab='101'";
			//$this->db->query($Qry_Update_Cabang_acc); 

			// $Qry_Update_Cabang_acc = "UPDATE ".DBACC.".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
			// $this->db->query($Qry_Update_Cabang_acc);

		}

		if ($id_unlocated != '') {
			$Qry_Update2	 = "UPDATE tr_unlocated_bank SET saldo=saldo - $unlocated WHERE id='$id_unlocated'";
			$this->db->query($Qry_Update2);
		}

		// elseif($id_lebihbayar !=''){			
		// $Qry_Update3	 = "UPDATE tr_lebihbayar_bank SET saldo=saldo - $lebihbayar WHERE id='$id_lebihbayar'";
		// 	 $this->db->query($Qry_Update3); 
		// } 




		if ($matauang == 'usd') {

			$saldo  = $this->db->query("SELECT * FROM tr_saldo_bank WHERE kd_bank='$kd_bank'")->row();

			if (!empty($saldo)) {
				$Qry_Update_saldo	 = "UPDATE tr_saldo_bank SET kurs_baru=$kurs, saldo_baru = $unlocated,  saldo_baru_idr = $unlocated*$kurs, saldo_akhir=saldo_lama+$unlocated, saldo_akhir_idr=saldo_lama+($unlocated*$kurs) WHERE kd_bank='$kd_bank'";
				$update_saldo = $this->db->query($Qry_Update_saldo);

				if (!$update_saldo) {
					print_r($this->db->error($update_saldo));
					exit;
				}
			} else {
				$datasaldo = array(
					'kd_pembayaran' => $kd_bayar,
					'tgl_transaksi' => $this->input->post('tgl_bayar'),
					'kurs_lama' => str_replace(',', '',  $this->input->post('kurs')),
					'saldo_lama' => str_replace(",", "", $this->input->post('total_bank')),
					'saldo_lama_idr' => str_replace(",", "", $this->input->post('total_bank')) * $kurs,
					'kurs_baru' => str_replace(',', '',  $this->input->post('kurs')),
					'saldo_baru' => str_replace(",", "", $this->input->post('total_bank')),
					'saldo_baru_idr' => str_replace(",", "", $this->input->post('total_bank')) * $kurs,
					'saldo_akhir' => str_replace(",", "", $this->input->post('total_bank')),
					'saldo_akhir_idr' => str_replace(",", "", $this->input->post('total_bank')) * $kurs,
					'kd_bank' => $kd_bank,
					'created_by'    => $session['id_user'],
					'created_on' => date('Y-m-d H:i:s'),
				);

				$insert_saldo_bank = $this->db->insert('tr_saldo_bank', $datasaldo);
				if (!$insert_saldo_bank) {
					print_r($this->db->error($insert_saldo_bank));
					exit;
				}
			}
		}

		$updatepro	 = "UPDATE tr_invoice_payment SET jumlah_piutang_idr='$piutangidr', selisih_idr='$selisihidr' WHERE kd_pembayaran='$kd_bayar'";
		$update_invoice_payment = $this->db->query($updatepro);
		if (!$update_invoice_payment) {
			print_r($this->db->error($update_invoice_payment));
			exit;
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Return		= array(
				'status'		=> 2,
				'pesan'			=> 'Save Process Failed. Please Try Again...'
			);
		} else {
			$this->db->trans_commit();
			$this->save_jurnal_BUM();
			$Arr_Return		= array(
				'status'		=> 1,
				'nomor'		    => $kd_bayar,
				'pesan'			=> 'Save Process Success. '
			);
		}
		echo json_encode($Arr_Return);
	}



	public function save_jurnal_BUM()
	{


		$kodejurnal = 'BUM01';
		$nomor      = $this->db->query("SELECT max(id) as id from tr_invoice_payment limit 1")->row();
		$id			= $nomor->id;


		$tr      = $this->db->query("SELECT * from tr_invoice_payment where id='$id'")->row();
		$idcust  = $tr->id_customer;
		$tgl_inv = $tr->tgl_pembayaran;
		$total	 = $tr->jumlah_pembayaran_idr;
		$totalkurs	 = $tr->jumlah_bank;
		$totalkurspiutang	 = $tr->jumlah_piutang;
		$kurs	 = $tr->kurs_bayar;

		$nama      =  $tr->nm_customer;
		$nomoripp  =  $tr->kd_pembayaran;

		$selisih   = $tr->selisih_idr;

		$pph    = $tr->biaya_pph_idr;
		$coaPPH = $tr->jenis_pph;

		$lebihbayar = $tr->tambah_lebih_bayar;

		$biaya_admin     = $tr->biaya_admin_idr;

		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $tgl_inv);
		$Keterangan_INV		    = 'Penerimaan Invoice ' . ($nama) . ' No Penerimaan' . ($nomoripp);


		$Bln 			= substr($tgl_inv, 5, 2);
		$Thn 			= substr($tgl_inv, 0, 4);




		$dataJVhead = array(
			'nomor' 	    	=> $Nomor_JV,
			'tgl'	         	=> $tgl_inv,
			'kd_pembayaran'     => $nomoripp,
			'jml'	            => $total,
			'kdcab'				=> '101',
			'jenis_reff'	    => 'BUM',
			'no_reff' 		    => $nomoripp,
			'terima_dari'	    => $nama,
			'jenis_ar'			=> 'BUM',
			'note'				=> $Keterangan_INV,
			'batal'				=> '0'
		);

		$Tgl_Invoice = $tgl_inv;
		$no_request = $id;
		$tgl_voucher = $Tgl_Invoice;

		#AMBIL TEMPLATE JURNAL DAN SIMPAN KE JURNAL TRAS
		$kd_bank         = $tr->kd_bank;
		$jenispph		 = $tr->jenis_pph;

		// $datajurnal  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal);
		// foreach ($datajurnal as $record) {
		// 	//$nokir  = $record->no_perkiraan;
		// 	$tabel  = $record->menu;
		// 	$posisi = $record->posisi;
		// 	$field  = $record->field;
		// 	if ($field == 'jumlah_bank_idr') {
		// 		$nokir = $kd_bank;
		// 	} elseif ($field == 'biaya_pph_idr') {
		// 		$nokir = $jenispph;
		// 	} else {
		// 		$nokir  = $record->no_perkiraan;
		// 	}
		// 	$no_voucher = $id;
		// 	$param  = 'id';
		// 	$value_param  = $id;
		// 	$val = $this->Acc_model->GetData($tabel, $field, $param, $value_param);
		// 	$nilaibayar = $val[0]->$field;

		// 	if ($posisi == 'D') {
		// 		$det_Jurnaltes[]  = array(
		// 			'nomor'         => $Nomor_JV,
		// 			'tanggal'       => $tgl_voucher,
		// 			'tipe'          => 'BUM',
		// 			'no_perkiraan'  => $nokir,
		// 			'keterangan'    => $Keterangan_INV,
		// 			'no_reff'       => $nomoripp,
		// 			'debet'         => $nilaibayar,
		// 			'kredit'        => 0,
		// 			'nilai_valas_debet'         => $totalkurs,
		// 			'nilai_valas_kredit'        => 0,

		// 		);
		// 	}
		// }

		// if ($selisih < 0) {
		// 	$det_Jurnaltes[]  = array(
		// 		'nomor'         => $Nomor_JV,
		// 		'tanggal'       => $tgl_voucher,
		// 		'tipe'          => 'BUM',
		// 		'no_perkiraan'  => '7101-01-02',
		// 		'keterangan'    => $Keterangan_INV,
		// 		'no_reff'       => $nomoripp,
		// 		'debet'         => $selisih * -1,
		// 		'kredit'        => 0,
		// 		'nilai_valas_debet'         => 0,
		// 		'nilai_valas_kredit'        => 0,

		// 	);
		// } elseif ($selisih > 0) {
		// 	$det_Jurnaltes[]  = array(
		// 		'nomor'         => $Nomor_JV,
		// 		'tanggal'       => $tgl_voucher,
		// 		'tipe'          => 'BUM',
		// 		'no_perkiraan'  => '7101-01-02',
		// 		'keterangan'    => $Keterangan_INV,
		// 		'no_reff'       => $nomoripp,
		// 		'debet'         => 0,
		// 		'kredit'        => $selisih,
		// 		'nilai_valas_debet'         => 0,
		// 		'nilai_valas_kredit'        => 0,

		// 	);
		// }

		// if ($biaya_admin != 0) {
		// 	$det_Jurnaltes[]			= array(
		// 		'nomor'         => $Nomor_JV,
		// 		'tanggal'       => $tgl_voucher,
		// 		'tipe'          => 'BUM',
		// 		'no_perkiraan'  => '7201-01-03',
		// 		'keterangan'    => $Keterangan_INV,
		// 		'no_reff'       => $nomoripp,
		// 		'debet'         => $biaya_admin,
		// 		'kredit'        => 0,
		// 		'nilai_valas_debet'         => 0,
		// 		'nilai_valas_kredit'        => 0,

		// 	);
		// }

		// if ($lebihbayar > 0) {
		// 	$det_Jurnaltes[]  = array(
		// 		'nomor'         => $Nomor_JV,
		// 		'tanggal'       => $tgl_voucher,
		// 		'tipe'          => 'BUM',
		// 		'no_perkiraan'  => '7201-01-03',
		// 		'keterangan'    => $Keterangan_INV,
		// 		'no_reff'       => $nomoripp,
		// 		'debet'         => 0,
		// 		'kredit'        => $lebihbayar,
		// 		'nilai_valas_debet'         => 0,
		// 		'nilai_valas_kredit'        => 0,

		// 	);
		// }


		// $data_jurnal = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran = '$nomoripp' ")->result();

		// foreach ($data_jurnal as $jr) {
		// 	$jmlbayarusd   = $jr->total_bayar;
		// 	$jmlbayar   = $jr->total_bayar_idr;
		// 	$invoice2    = $jr->no_invoice;
		// 	$jenis_pphdt = $jr->jenis_pph;
		// 	$jumlahpph   = $jr->total_pph_idr;

		// 	$tipe        = $jr->tipe_bayar;

		// 	$selisih = $jr->selisih_idr;

		// 	if ($selisih < 0) {
		// 		$selisihidr = $selisih * (-1);
		// 	} else {
		// 		$selisihidr = $selisih;
		// 	}

		// 	if ($jumlahpph != 0) {
		// 		$det_Jurnaltes[] = array(
		// 			'nomor'         => $Nomor_JV,
		// 			'tanggal'       => $tgl_voucher,
		// 			'tipe'          => 'BUM',
		// 			'no_perkiraan'  => $jenis_pphdt,
		// 			'keterangan'    => $Keterangan_INV,
		// 			'no_reff'       => $nomoripp,
		// 			'debet'         => $jumlahpph,
		// 			'kredit'        => 0,
		// 			'nilai_valas_debet'         => 0,
		// 			'nilai_valas_kredit'        => 0,
		// 		);
		// 	}


		// 	if ($kurs > 1) {

		// 		if ($tipe == 'PROGRESS') {

		// 			$det_Jurnaltes[] = array(
		// 				'nomor'         => $Nomor_JV,
		// 				'tanggal'       => $tgl_voucher,
		// 				'tipe'          => 'BUM',
		// 				'no_perkiraan'  => '1102-01-02',
		// 				'keterangan'    => $Keterangan_INV,
		// 				'no_reff'       => $invoice2,
		// 				'debet'         => 0,
		// 				'kredit'        => $jmlbayar + $selisihidr,
		// 				'nilai_valas_debet'         => 0,
		// 				'nilai_valas_kredit'        => $jmlbayarusd
		// 			);
		// 		} elseif ($tipe == 'RETENSI') {

		// 			$det_Jurnaltes[] = array(
		// 				'nomor'         => $Nomor_JV,
		// 				'tanggal'       => $tgl_voucher,
		// 				'tipe'          => 'BUM',
		// 				'no_perkiraan'  => '1102-01-04',
		// 				'keterangan'    => $Keterangan_INV,
		// 				'no_reff'       => $invoice2,
		// 				'debet'         => 0,
		// 				'kredit'        => $jmlbayar + $selisihidr,
		// 				'nilai_valas_debet'         => 0,
		// 				'nilai_valas_kredit'        => $jmlbayarusd
		// 			);
		// 		}
		// 	} else {

		// 		if ($tipe == 'PROGRESS') {

		// 			$det_Jurnaltes[] = array(
		// 				'nomor'         => $Nomor_JV,
		// 				'tanggal'       => $tgl_voucher,
		// 				'tipe'          => 'BUM',
		// 				'no_perkiraan'  => '1102-01-01',
		// 				'keterangan'    => $Keterangan_INV,
		// 				'no_reff'       => $invoice2,
		// 				'debet'         => 0,
		// 				'kredit'        => $jmlbayar,
		// 				'nilai_valas_debet'         => 0,
		// 				'nilai_valas_kredit'        => 0,
		// 			);
		// 		} elseif ($tipe == 'RETENSI') {

		// 			$det_Jurnaltes[] = array(
		// 				'nomor'         => $Nomor_JV,
		// 				'tanggal'       => $tgl_voucher,
		// 				'tipe'          => 'BUM',
		// 				'no_perkiraan'  => '1102-01-03',
		// 				'keterangan'    => $Keterangan_INV,
		// 				'no_reff'       => $invoice2,
		// 				'debet'         => 0,
		// 				'kredit'        => $jmlbayar,
		// 				'nilai_valas_debet'         => 0,
		// 				'nilai_valas_kredit'        => 0,
		// 			);
		// 		}
		// 	}
		// }

		// $this->db->insert_batch(DBACC . '.jurnal', $det_Jurnaltes);

		// $this->db->insert(DBACC . '.jarh', $dataJVhead);

		// $Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobum=nobum + 1 WHERE nocab='101'";
		// $this->db->query($Qry_Update_Cabang_acc);

		$dt      = $this->db->query("SELECT * from tr_invoice_payment_detail where kd_pembayaran='$nomoripp'")->result();

		foreach ($dt as $val) {
			$invoice = $val->no_invoice;
			$nilai   = $val->total_bayar_idr;
			$nilai_usd = $val->total_bayar;
			$tipe2     = $val->tipe_bayar;


			if ($kurs > 1) {

				if ($tipe2 == 'PROGRESS') {
					$noperkiraan = '1102-01-02';
				} elseif ($tipe2 == 'RETENSI') {
					$noperkiraan = '1102-01-04';
				}
			} else {

				if ($tipe2 == 'PROGRESS') {

					$noperkiraan = '1102-01-01';
				} elseif ($tipe2 == 'RETENSI') {

					$noperkiraan = '1102-01-03';
				}
			}


			$datapiutang = array(
				'tipe'       	 => 'BUM',
				'nomor'       	 => $Nomor_JV,
				'tanggal'        => $tgl_voucher,
				'no_perkiraan'   => $noperkiraan,
				'keterangan'     => $Keterangan_INV,
				'no_reff'        => $invoice,
				'debet'          => 0,
				'kredit'         => round($nilai),
				'debet_usd'          => 0,
				'kredit_usd'         => $nilai_usd,
				'id_supplier'     => $idcust,
				'nama_supplier'   => $nama,

			);
			$this->db->insert('tr_kartu_piutang', $datapiutang);
		}
	}


	function appr_jurnal()
	{




		$kd_bayar   = $this->uri->segment(3);
		$session = $this->session->userdata('app_session');

		$data_bayar =  $this->db->query("SELECT * FROM tr_invoice_payment WHERE kd_pembayaran = '$kd_bayar' ")->row();

		$tgl_byr 	= $data_bayar->tgl_pembayaran;
		$kd_invoice    	= $data_bayar->no_invoice;
		$kd_bank 	= $data_bayar->kd_bank;
		$jenis_pph 	= $data_bayar->jenis_pph;
		$nama	= html_escape($data_bayar->nm_customer);
		$jmlpph   = $data_bayar->total_pph_idr;

		$id_cust =  $this->db->query("SELECT * FROM master_customer WHERE name_customer = '$nama'")->row();
		$idcust  = $id_cust->id_customer;



		$No_Inv  = $kd_bayar;
		$Tgl_Inv = $tgl_byr;
		$Bln 			= substr($Tgl_Inv, 6, 2);
		$Thn 			= substr($Tgl_Inv, 0, 4);
		$bulan_bayar = date("n", strtotime($Tgl_Inv));
		$tahun_bayar = date("Y", strtotime($Tgl_Inv));
		$keterangan_byr  = $data_bayar->keterangan;
		$jumlah_total    = $data_bayar->jumlah_pembayaran_idr;
		$jumlah_terima   = $data_bayar->jumlah_bank_idr;
		$biaya_admin     = $data_bayar->biaya_admin_idr;
		$biaya_lain     = $data_bayar->biaya_pph_idr;
		$deposit         = $data_bayar->lebih_bayar;
		$jenis_reff      = $kd_bayar;
		$no_reff         = $kd_bayar;
		## NOMOR JV ##
		$Nomor_BUM				= $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $Tgl_Inv);

		//print_r($Nomor_BUM);
		//exit;


		//$Keterangan_INV		    = 'PENERIMAAN MULTI INVOICE A/N '.$nama.' INV NO. '.$No_Inv.
		//' Keterangan :'.$ket_invoice.', Catatan :'.$notes.', No Reff:'.$noreff.', No Pembayaran:'.$kd_pn;

		$Keterangan_INV		    = 'PENERIMAAN MULTI INVOICE A/N ' . $nama . ' INV NO. ' . $No_Inv . ' Keterangan :' . $keterangan_byr;

		$dataJARH = array(
			'nomor' 	    	=> $Nomor_BUM,
			'kd_pembayaran'    	=> $kd_pembayaran,
			'tgl'	         	=> $Tgl_Inv,
			'jml'	            => $jumlah_total,
			'kdcab'				=> '101',
			'jenis_reff'		=> $jenis_reff,
			'no_reff'		    => $no_reff,
			'customer'		    => $nama,
			'terima_dari'		=> '-',
			'jenis_ar'		    => 'V',
			'note'				=> $Keterangan_INV,
			'valid'				=> $session['id_user'],
			'tgl_valid'			=> $Tgl_Inv,
			'user_id'			=> $session['id_user'],
			'tgl_invoice'	    => $Tgl_Inv,
			'ho_valid'			=> '',
			'batal'			    => '0'
		);

		$det_Jurnal				= array();
		$det_Jurnal[]			= array(
			'nomor'         => $Nomor_BUM,
			'tanggal'       => $Tgl_Inv,
			'tipe'          => 'BUM',
			'no_perkiraan'  => $kd_bank,
			'keterangan'    => $Keterangan_INV,
			'no_reff'       => $No_Inv,
			'debet'         => $jumlah_terima,
			'kredit'        => 0

		);

		if ($biaya_admin != 0) {
			$det_Jurnal[]			= array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Inv,
				'tipe'          => 'BUM',
				'no_perkiraan'  => '7205-01-01',
				'keterangan'    => $Keterangan_INV,
				'no_reff'       => $No_Inv,
				'debet'         => $biaya_admin,
				'kredit'        => 0

			);
		}

		if ($deposit != 0) {
			$det_Jurnal[]			= array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Inv,
				'tipe'          => 'BUM',
				'no_perkiraan'  => '2109-02-01',
				'keterangan'    => $Keterangan_INV,
				'no_reff'       => $No_Inv,
				'debet'         => $deposit,
				'kredit'        => 0

			);
		}




		// if ($jumlah_piutang2 > $pembayaran){

		// $det_Jurnal[]			  = array(
		// 'nomor'         => $Nomor_BUM,
		// 'tanggal'       => $Tgl_Inv,
		// 'tipe'          => 'BUM',
		// 'no_perkiraan'  => $no_account,
		// 'keterangan'    => $Keterangan_INV,
		// 'no_reff'       => $No_Inv,
		// 'debet'         => $selisih,
		// 'kredit'        => 0
		// );

		// }
		// else if ($jumlah_piutang2 < $pembayaran){
		// $det_Jurnal[]			  = array(
		// 'nomor'         => $Nomor_BUM,
		// 'tanggal'       => $Tgl_Inv,
		// 'tipe'          => 'BUM',
		// 'no_perkiraan'  => $no_account,
		// 'keterangan'    => $Keterangan_INV,
		// 'no_reff'       => $No_Inv,
		// 'debet'         => 0,
		// 'kredit'        => $selisih
		// );

		// }



		$data_jurnal = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran = '$kd_bayar' ")->result();

		foreach ($data_jurnal as $jr) {
			$jmlbayar   = $jr->total_bayar_idr;
			$invoice2    = $jr->no_invoice;


			if ($biaya_lain != 0) {
				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_BUM,
					'tanggal'       => $Tgl_Inv,
					'tipe'          => 'BUM',
					'no_perkiraan'  => $jenis_pph,
					'keterangan'    => $Keterangan_INV,
					'no_reff'       => $No_Inv,
					'debet'         => $jmlpph,
					'kredit'        => 0
				);
			}

			$det_Jurnal[]			  = array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Inv,
				'tipe'          => 'BUM',
				'no_perkiraan'  => '1102-01-01',
				'keterangan'    => $Keterangan_INV,
				'no_reff'       => $invoice2,
				'debet'         => 0,
				'kredit'        => $jmlbayar,
			);
		}


		## INSERT JURNAL ##
		$this->db->insert(DBACC . '.JARH', $dataJARH);
		$this->db->insert_batch(DBACC . '.jurnal', $det_Jurnal);

		## UPDATE AR ##
		$Query_AR	= "UPDATE " . DBACC . ".ar SET kredit=kredit + " . $jumlah_total . ", saldo_akhir=saldo_akhir - " . $jumlah_total . " WHERE  no_invoice='" . $No_Inv . "' AND thn='$tahun_bayar' AND bln='$bulan_bayar'";
		$this->db->query($Query_AR);

		$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobum=nobum + 1 WHERE nocab='101'";
		$this->db->query($Qry_Update_Cabang_acc);

		//PROSES JURNAL

		$data_jr = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran = '$kd_bayar' ")->result();

		foreach ($data_jr as $val) {
			$jml   = $val->total_bayar_idr;
			$inv   = $val->no_invoice;

			$Ket_INV		    = 'PENERIMAAN MULTI INVOICE A/N ' . $nama . ' INV NO. ' . $inv . ' Keterangan :' . $keterangan_byr;


			$datapiutang = array(
				'tipe'       	 => 'BUM',
				'nomor'       	 => $Nomor_BUM,
				'tanggal'        => $Tgl_Inv,
				'no_perkiraan'  => '1103-01-01',
				'keterangan'    => $Ket_INV,
				'no_reff'       => $inv,
				'debet'         => 0,
				'kredit'         => $jml,
				'id_supplier'     => $idcust,
				'nama_supplier'   => $nama,

			);



			$idso = $this->db->insert('tr_kartu_piutang', $datapiutang);
		}

		$Qry  = "UPDATE tr_invoice_payment SET status_jurnal='1' WHERE kd_pembayaran='$kd_bayar'";
		$this->db->query($Qry);


		$this->print_penerimaan_fix();
	}



	public function save_penerimaan_proforma()
	{

		// print_r($this->input->post());
		// exit;
		$session = $this->session->userdata('app_session');
		$Tgl_Invoice        = $this->input->post('tgl_bayar');

		$data_session 	    = $this->session->userdata;
		$kd_bayar 			= $this->penerimaan_model->generate_nopro($Tgl_Invoice);

		$pro    = '-PRO';
		$nomor  = $kd_bayar . $pro;

		$this->db->trans_begin();

		if (!empty($this->input->post('bank'))) {
			// $bank = explode('|',$this->input->post('bank'));
			// $kd_bank = $bank[0];
			// $nmbank = $bank[1];

			$kd_bank  = $this->input->post('bank');
		}
		// print_r($kd_bank);
		// exit;
		$matauang = $this->input->post('matauang');
		if ($matauang == 'usd') {
			$kurs = str_replace(",", "", str_replace(',', '',  $this->input->post('kurs')));
		} else if ($matauang == 'idr') {
			$kurs = 1;
		}
		$jumlah_total_idr = number_format(str_replace(",", "", $this->input->post('total_bank')) * $kurs);

		$unlocated =  str_replace(",", "", $this->input->post('total_bank'));
		$id_unlocated = $this->input->post('id_unlocated');

		$lebihbayar =  str_replace(",", "", $this->input->post('pakai_lebih_bayar'));
		$id_lebihbayar = $this->input->post('id_lebihbayar');

		$idcustomer = $this->input->post('customer');

		$customer =  $this->db->query("SELECT * FROM customer WHERE id_customer = '$idcustomer'")->row();

		$idcs   = $customer->id_customer;
		$nmcs	= html_escape($customer->nm_customer);


		$data = array(
			'no_invoice' => $this->input->post('no_invoice'),
			'kd_pembayaran' => $nomor,
			'jenis_reff' => '-',
			'no_reff' => '-',
			'tgl_pembayaran' => $this->input->post('tgl_bayar'),
			'kurs_bayar' => str_replace(',', '',  $this->input->post('kurs')),
			'jumlah_piutang' => str_replace(",", "", $this->input->post('total_invoice')),
			'jumlah_piutang_idr' => '-',
			'jumlah_bank' => str_replace(",", "", $this->input->post('total_bank')),
			'jumlah_bank_idr' => str_replace(",", "", $this->input->post('total_bank')) * $kurs,
			'jumlah_pembayaran' => str_replace(",", "", $this->input->post('total_terima')),
			'jumlah_pembayaran_idr' => str_replace(",", "", $this->input->post('total_terima')) * $kurs,
			'kd_bank' => $kd_bank,
			'biaya_admin' => str_replace(",", "", $this->input->post('biaya_adm')),
			'biaya_admin_idr' => str_replace(",", "", $this->input->post('biaya_adm')) * $kurs,
			'biaya_pph' => str_replace(",", "", $this->input->post('biaya_pph')),
			'biaya_pph_idr' => str_replace(",", "", $this->input->post('biaya_pph')) * $kurs,
			'created_by'    => $session['id_user'],
			'created_on' => date('Y-m-d H:i:s'),
			'jenis_pph' => $this->input->post('jenis_pph'),
			'no_account' => '-',
			'selisih' => '-',
			'selisih_idr' => '-',
			'keterangan' => $this->input->post('ket_bayar'),
			'id_customer' => $idcs,
			'nm_customer' => $nmcs,
			'lebih_bayar' => str_replace(",", "", $this->input->post('pakai_lebih_bayar')),
			'tambah_lebih_bayar' => str_replace(",", "", $this->input->post('tambah_lebih_bayar')),

		);



		$this->db->insert('tr_invoice_payment_temp', $data);



		$selisih    = 0;
		$selisihidr = 0;
		$piutangidr = 0;

		for ($i = 0; $i < count($this->input->post('kode_produk')); $i++) {

			if ($matauang == 'usd') {
				$kurs_jual = str_replace(",", "", $this->input->post('kurs_jual')[$i]);
			} else if ($matauang == 'idr') {
				$kurs_jual = 1;
			}


			$nilai_bayar = round(str_replace(",", "", $this->input->post('jml_bayar')[$i]) * $kurs);
			$nilai_jual  = round($kurs_jual * str_replace(",", "", $this->input->post('jml_bayar')[$i]));
			$pphidr      = round($kurs_jual * str_replace(",", "", $this->input->post('pph')[$i]));

			$selisih     = $nilai_bayar - $nilai_jual;

			$selisihidr  += $selisih;

			$piutangidr  += $nilai_jual;



			$datadetail = array(
				'kd_pembayaran'     => $nomor,
				'no_invoice'        => $this->input->post('kode_produk')[$i],
				'no_ipp'        => $this->input->post('no_surat')[$i],
				'nm_customer'       => $this->input->post('nm_customer2')[$i],
				'total_invoice_idr'    => str_replace(",", "", $this->input->post('sisa_invoice')[$i]),
				'total_bayar'         => str_replace(",", "", $this->input->post('jml_bayar')[$i]),
				'total_bayar_idr'     => round(str_replace(",", "", $this->input->post('jml_bayar')[$i]) * $kurs),
				'sisa_invoice_idr'    => str_replace(",", "", $this->input->post('sisa_invoice')[$i]) - str_replace(",", "", $this->input->post('jml_bayar')[$i]),
				'jenis_pph'           => str_replace(",", "", $this->input->post('jenis_pph2')[$i]),
				'total_pph'           => str_replace(",", "", $this->input->post('pph')[$i]),
				'total_pph_idr'       => $pphidr,
				'kurs_jual'				=> $kurs_jual,
				'kurs_bayar'			=> $kurs,
				'total_jual_idr'	    => $nilai_jual,
				'selisih_idr'	        => $selisih,
				'created_on'    => date('Y-m-d H:i:s'),
				'created_by'    => $session['id_user']
			);
			$this->db->insert('tr_invoice_payment_detail_temp', $datadetail);

			// $this->printout_draft($nomor); 






		}

		$updatepro	 = "UPDATE tr_invoice_payment_temp SET jumlah_piutang_idr='$piutangidr', selisih_idr='$selisihidr' WHERE kd_pembayaran='$nomor'";
		$this->db->query($updatepro);


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Return		= array(
				'status'		=> 2,
				'pesan'			=> 'Save Process Failed. Please Try Again...'
			);
		} else {
			$this->db->trans_commit();
			$Arr_Return		= array(
				'status'		=> 1,
				'pesan'			=> 'Save Process Success.',
				'nomor'		    => $nomor
			);
		}
		echo json_encode($Arr_Return);
	}


	function print_penerimaan_fix()
	{
		// $sroot 		= $_SERVER['DOCUMENT_ROOT'];
		// include $sroot."/application/libraries/MPDF57/mpdf.php";
		$data_session = $this->session->userdata;
		$session      = $this->session->userdata('app_session');

		// print_r($session);
		// exit;

		$mpdf = new mPDF('utf-8', 'A5-L');
		$mpdf->SetImportUse();

		$kd_bayar   = $this->uri->segment(3);
		$data_bayar =  $this->db->query("SELECT * FROM tr_invoice_payment WHERE kd_pembayaran = '$kd_bayar' ")->row();
		$coabank    =  $data_bayar->kd_bank;
		$coa        =  $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE no_perkiraan = '$coabank' ")->row();

		$nomordoc   = html_escape($data_bayar->nm_customer);
		$gethd = $this->db->query("SELECT * FROM master_customer WHERE name_customer='$nomordoc'")->row();
		$tgl       = $gethd->tgl_invoice;
		$Jml_Ttl   = $gethd->total_invoice;
		$Id_klien     = $gethd->id_customer;
		$Nama_klien   = html_escape($gethd->nm_customer);
		$Bln 			= substr($tgl, 5, 2);
		$Thn 			= substr($tgl, 0, 4);

		$data_header = $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice ='$nomordoc'")->row();
		$alamat_cust =  $this->db->query("SELECT * FROM master_customer WHERE id_customer = '$gethd->id_customer'")->row();
		$mso =  $this->db->query("SELECT * FROM mso_proses_header WHERE id_quotation = '$gethd->no_ipp'")->row();

		$quot =  $this->db->query("SELECT * FROM quotation_process WHERE id = '$gethd->no_ipp'")->row();

		$count = $this->db->query("SELECT COUNT(no_invoice) as total FROM tr_invoice_detail WHERE no_invoice ='$nomordoc'")->row();
		$count1 = $count->total;


		$total  = $this->invoicing_model->GetInvoiceHeader($nomordoc);
		$detail  = $this->invoicing_model->GetInvoiceDetail($nomordoc);

		$data['inv'] = $data_header;
		$data['quot'] = $quot;
		$data['total'] = $this->invoicing_model->GetInvoiceHeader($nomordoc);
		$data['results']  = $this->invoicing_model->GetInvoiceDetail($nomordoc);
		$data['user']  = $session['username'];
		$data['kodebayar'] = $kd_bayar;


		$show = $this->load->view('penerimaan/print_penerimaan', $data, TRUE);




		$tglprint = date("d-m-Y H:i:s");
		$tglprint2 = date("d-m-Y");

		foreach ($total as $val) {
			$date = tgl_indo($val->tgl_invoice); //date('d-m-Y');
			$invoice  = $val->no_invoice;
			$so  = $val->so_number;
			$total2  = $val->total_invoice;
			$customer  = $val->nm_customer;
			$tagih  = $val->jenis_invoice;
			$persentase  = number_format($val->persentase);
			$persen      = '%';

			if ($tagih == 'TR-01') {
				$jenis_invoice1 = 'DOWN PAYMENT OF ';
				$jenis_invoice = $jenis_invoice1 . $persentase . $persen;
			} elseif ($tagih == 'TR-02') {
				$jenis_invoice1 = 'PAYMENT ';
				$jenis_invoice = $jenis_invoice1 . $persentase . $persen;
			} else {
				$jenis_invoice = 'RETENSI';
			}
		}


		$header = '
          <br>

        	<table width="100%" border="0"  style="font-size:7.5pt !important;max-height:100px;border-spacing:-1px">
			<tr>
  	      		<td width="8%" style="text-align: center;">
  	      			<img src="assets/images/logo.png" style="height: 40px;width: auto;">
  	      		</td>
  	      	</tr>
			</table>
			<br>
			<table width="100%" border="0"  style="font-size:7.5pt !important;max-height:100px;border-spacing:-1px">
			<tr>
  	      		<td style="text-align: center; font-weight: bold; font-size:12pt">
  	      			BUKTI UANG MASUK
  	      		</td>
  	      	</tr>
  	      	</table>
		  <br>
		  <br>
          <table border="0" width="100%">
            <tr><b>
                  <td width="15%" style="font-size:8pt !important;vertical-align:top"><b>Kode Penerimaan</b></td>
				 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$kd_bayar . '</b></td>
				  <td width="15%" style="font-size:8pt !important;vertical-align:top"><b>Customer</b></td>
				 <td width="3%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @html_escape($gethd->name_customer) . '</b></td>
		 </b> </tr>
		 <tr><b>
                 <<td width="10%"style="font-size:8pt !important;vertical-align:top"><b>Tgl Terima</b></td>
                 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @tgl_indo($data_bayar->tgl_pembayaran) . '</b></td>
				 <td width="10%" style="font-size:8pt !important;vertical-align:top"><b></b></td> 
                 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b></b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$alamat_cust->address_office . '</b></td>
				 
		 </b> </tr>
		  <tr><b> 
		         <td width="10%" style="font-size:8pt !important;vertical-align:top"><b>Bank</b></td>
				 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$coa->nama . '</b></td>
				 <td width="10%" style="font-size:8pt !important;vertical-align:top"><b></b></td>
				 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b></b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b></b></td>
                
                 
		 </b> </tr> 
		    <tr><b>
                 <td width="10%" style="font-size:8pt !important;vertical-align:top"><b>Keterangan</b></td> 
                 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$data_bayar->keterangan . '</b></td>
				 <td width="10%" style="font-size:8pt !important;vertical-align:top"><b></b></td>
                 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b></b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b></b></td>
				 
		 </b> </tr>
		 </table>
		    <br>
			
		  <hr> 
		  ';

		$this->mpdf->SetHTMLHeader($header, '0', true);


		$this->mpdf->SetHTMLFooter('
        <hr>        
       	<div id="footer">
        <table>
            <tr><td>PT IDEFAB CIPTA - Printed By ' . ucwords($session['username']) . ' On ' . $tglprint . ' </td></tr>
        </table>
        </div>
        ');


		$this->mpdf->AddPageByArray([
			'orientation' => 'L',
			'margin-top' => 60,
			'margin-bottom' => 15,
			'margin-left' => 5,
			'margin-right' => 10,
			'margin-header' => 0,
			'margin-footer' => 0,
		]);
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output();
	}


	public function unlocated()
	{

		$bank1			 = $this->Jurnal_model->get_Coa_Bank_Cabang('101');
		$pphpenjualan  	 = $this->Acc_model->combo_pph_penjualan();
		$datacoa  	     = $this->Acc_model->GetCoaCombo();
		$template  	     = $this->Acc_model->GetTemplate();
		$this->template->title('Penerimaan Unlocated');


		$this->template->set([
			'no_inv'  => $id,
			'datbank' => $bank1,
			'pphpenjualan' => $pphpenjualan,
			'template' => $template
		]);
		$this->template->render('create_unlocated');
	}
	public function lebihbayar()
	{

		$bank1			 = $this->Jurnal_model->get_Coa_Bank_Cabang('101');
		$pphpenjualan  	 = $this->Acc_model->combo_pph_penjualan();
		$datacoa  	     = $this->Acc_model->GetCoaCombo();
		$template  	     = $this->Acc_model->GetTemplate();
		$this->template->title('Penerimaan Lebih Bayar');


		$this->template->set([
			'no_inv'  => $id,
			'datbank' => $bank1,
			'pphpenjualan' => $pphpenjualan,
			'template' => $template
		]);
		$this->template->render('create_lebihbayar');
	}

	public function createunlocated()
	{

		$bank1			 = $this->Jurnal_model->get_Coa_Bank_Cabang('101');
		$pphpenjualan  	 = $this->Acc_model->combo_pph_penjualan();
		$datacoa  	     = $this->Acc_model->GetCoaCombo();
		$template  	     = $this->Acc_model->GetTemplate();
		$this->template->title('Penerimaan Unlocated');


		$this->template->set([
			//'no_inv'  => $id,
			'datbank' => $bank1,
			'pphpenjualan' => $pphpenjualan,
			'template' => $template
		]);
		$this->template->render('create_unlocated');
	}

	public function save_unlocated()
	{

		// print_r($this->input->post());
		// exit;
		$session = $this->session->userdata('app_session');
		$data_session 	    = $this->session->userdata;


		if (!empty($this->input->post('bank'))) {
			$bank = explode('|', $this->input->post('bank'));
			$kd_bank = $bank[0];
			$nmbank = $bank[1];
		}


		for ($i = 0; $i < count($this->input->post('keterangan')); $i++) {
			$datadetail = array(
				'tgl'               =>  $this->input->post('tanggal'),
				'keterangan'        => $this->input->post('keterangan')[$i],
				'bank'              => $this->input->post('bank'),
				'totalpenerimaan'   => $this->input->post('totalpenerimaan')[$i],
				'saldo'             => $this->input->post('totalpenerimaan')[$i],
				'created_on'    => date('Y-m-d H:i:s'),
				'created_by'    => $session['id_user']
			);
			$this->db->insert('tr_unlocated_bank', $datadetail);



			$No_Inv  = $kd_bayar;
			$Tgl_Inv = $this->input->post('tanggal');
			$Bln 			= substr($Tgl_Inv, 6, 2);
			$Thn 			= substr($Tgl_Inv, 0, 4);
			$bulan_bayar = date("n", strtotime($Tgl_Inv));
			$tahun_bayar = date("Y", strtotime($Tgl_Inv));
			$keterangan_byr  = $this->input->post('keterangan')[$i];
			$jumlah_total    = $this->input->post('totalpenerimaan')[$i];

			$jenis_reff      = 'Deposit';
			$no_reff         = 'Deposit';
			## NOMOR JV ##
			$Nomor_BUM				= $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $Tgl_Inv);

			$Keterangan_INV		    = 'DEPOSIT CUSTOMER' . $keterangan_byr;

			$dataJARH = array(
				'nomor' 	    	=> $Nomor_BUM,
				'kd_pembayaran'    	=> $kd_pembayaran,
				'tgl'	         	=> $Tgl_Inv,
				'jml'	            => $jumlah_total,
				'kdcab'				=> '101',
				'jenis_reff'		=> $jenis_reff,
				'no_reff'		    => $no_reff,
				'customer'		    => 'DEPOSIT CUSTOMER',
				'terima_dari'		=> '-',
				'jenis_ar'		    => 'V',
				'note'				=> $Keterangan_INV,
				'valid'				=> $session['id_user'],
				'tgl_valid'			=> $Tgl_Inv,
				'user_id'			=> $session['id_user'],
				'tgl_invoice'	    => $Tgl_Inv,
				'ho_valid'			=> '',
				'batal'			    => '0'
			);




			$det_Jurnal[]			  = array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Inv,
				'tipe'          => 'BUM',
				'no_perkiraan'  => $kd_bank,
				'keterangan'    => $Keterangan_INV,
				'no_reff'       => 'DEPOSIT CUSTOMER',
				'debet'         => $jumlah_total,
				'kredit'        => 0
			);


			$det_Jurnal[]			  = array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Inv,
				'tipe'          => 'BUM',
				'no_perkiraan'  => '2101-08-01',
				'keterangan'    => $Keterangan_INV,
				'no_reff'       => 'DEPOSIT CUSTOMER',
				'debet'         => 0,
				'kredit'        => $jumlah_total,
			);




			## INSERT JURNAL ##
			$this->db->insert(DBACC . '.jarh', $dataJARH);
			$this->db->insert_batch(DBACC . '.jurnal', $det_Jurnal);

			$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobum=nobum + 1 WHERE nocab='101'";
			$this->db->query($Qry_Update_Cabang_acc);
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Return		= array(
				'status'		=> 2,
				'pesan'			=> 'Save Process Failed. Please Try Again...'
			);
		} else {
			$this->db->trans_commit();
			$Arr_Return		= array(
				'status'		=> 1,
				'pesan'			=> 'Save Process Success. '
			);
		}
		echo json_encode($Arr_Return);
	}

	public function TambahInvoice()
	{
		$customer = $this->uri->segment(3);
		$data = array(
			'results' => $customer,
		);

		$this->template->set($data);
		$this->template->render('invoice');
		// $this->load->view('Penerimaan/invoice', $data);
	}

	public function TambahLebihBayar()
	{
		$customer = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$invoice = $this->db->query("SELECT * FROM tr_lebihbayar_bank WHERE saldo !=0 AND id_customer ='$customer'")->result();
		$data = [
			'detail' => $customer
		];
		$this->template->set('results', $data);
		$this->template->title('List Invoice');
		$this->template->render('lebihbayar');
	}

	public function save_lebihbayar()
	{

		// print_r($this->input->post());
		// exit;
		$session = $this->session->userdata('app_session');
		$data_session 	    = $this->session->userdata;


		// if(!empty($this->input->post('bank'))){
		// $bank = explode('|',$this->input->post('bank'));
		// $kd_bank = $bank[0];
		// $nmbank = $bank[1];
		// }


		for ($i = 0; $i < count($this->input->post('tanggal')); $i++) {
			$datadetail = array(
				'tgl'               =>  $this->input->post('tanggal'),
				'keterangan'        => $this->input->post('keterangan'),
				'bank'              => $this->input->post('bank'),
				'totalpenerimaan'   => $this->input->post('totalpenerimaan'),
				'saldo'             => $this->input->post('totalpenerimaan'),
				'created_on'    => date('Y-m-d H:i:s'),
				'created_by'    => $session['id_user']
			);
			$this->db->insert('tr_lebihbayar_bank', $datadetail);
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Return		= array(
				'status'		=> 2,
				'pesan'			=> 'Save Process Failed. Please Try Again...'
			);
		} else {
			$this->db->trans_commit();
			$Arr_Return		= array(
				'status'		=> 1,
				'pesan'			=> 'Save Process Success. '
			);
		}
		echo json_encode($Arr_Return);
	}

	public function jurnal_bum()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-list');
		$data = $this->penerimaan_model->get_data_pn_jurnal();
		$this->template->set('results', $data);
		$this->template->title('Jurnal Penerimaan');
		$this->template->render('index_jurnal_penerimaan');
	}

	public function index_akhir_bulan()
	{

		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$so = $this->penerimaan_model->get_data_invoice();
		$data = array(
			'title'			=> 'Penerimaan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'results'			=> $so,
		);
		history('View Akhir Bulan');
		$this->load->view('Penerimaan/list_akhir_bulan', $data);
	}



	public function update_invoice_akhir_bulan()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$so                 = $this->penerimaan_model->get_data_pn();
		$data = array(
			'title'			=> 'Update Invoice Akhir Bulan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'results'		=> $so,
		);
		history('Update Invoice Akhir Bulan');
		$this->load->view('Penerimaan/index_update_invoice_akhir_bulan', $data);
	}

	function update_invoice()
	{

		//UPDATE KURS PIUTANG USD
		$session = $this->session->userdata('app_session');
		$post    = $this->input->post();
		$kurs    = $post['kurs'];
		$tanggal = $post['tgl_update'];
		$bulan 	 = date("m", strtotime($tanggal));
		$thn 	 = date("Y", strtotime($tanggal));


		$this->db->query("INSERT INTO tr_invoice_tutup_bulan_history (
						id_invoice,
						id_penagihan,
						id_bq,
						no_ipp,
						so_number,
						no_invoice,
						tgl_invoice,
						nm_customer,
						jenis_invoice,
						kurs_jual,
						persentase,
						kurs_bayar,
						total_invoice,
						total_invoice_idr,
						total_bayar,
						total_bayar_idr,
						created_by,
						created_date,
						modified_date,
						modified_by,
						id_top,
						base_cur,
						sisa_invoice_idr,
						sisa_invoice,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal,
						sisa_invoice_retensi2_idr,
						sisa_invoice_retensi2,
						selisih_debit_retensi,
						selisih_kredit_retensi
						) 
						(SELECT 
						id_invoice,
						id_penagihan,
						id_bq,
						no_ipp,
						so_number,
						no_invoice,
						tgl_invoice,
						nm_customer,
						jenis_invoice,
						kurs_jual,
						persentase,
						kurs_bayar,
						total_invoice,
						total_invoice_idr,
						total_bayar,
						total_bayar_idr,
						created_by,
						created_date,
						modified_date,
						modified_by,
						id_top,
						base_cur,
						sisa_invoice_idr,
						sisa_invoice,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal,
						sisa_invoice_retensi2_idr,
						sisa_invoice_retensi2,
						selisih_debit_retensi,
						selisih_kredit_retensi
						FROM tr_invoice_tutup_bulan)");


		$this->db->query("DELETE FROM tr_invoice_tutup_bulan");


		$invoice = $this->db->query("SELECT * FROM tr_invoice_header WHERE Year(tgl_invoice)='$thn' AND month(tgl_invoice)='$bulan' AND base_cur='USD' AND sisa_invoice > 0")->result();

		foreach ($invoice as $val) {

			$nilailama 	   = $val->kurs_jual * $val->sisa_invoice;
			$nilaibaru 	   = $kurs * $val->sisa_invoice;
			$nilai		   = $nilaibaru;
			$selisih       = $nilailama - $nilaibaru;
			if ($selisih > 0) {
				$selisihdebet  = $selisih;
				$selisihkredit = 0;
			} elseif ($selisih < 0) {
				$selisihdebet  = 0;
				$selisihkredit = $selisih * -1;
			}


			$datainvoice = array(

				'id_invoice'       	=> $val->id_invoice,
				'id_penagihan'      => $val->id_penagihan,
				'id_bq'       	 	=> $val->id_bq,
				'no_ipp'        	=> $val->no_ipp,
				'so_number'  		=> $val->so_number,
				'no_invoice'        => $val->no_invoice,
				'tgl_invoice'       => $val->tgl_invoice,
				'nm_customer'       => $val->nm_customer,
				'jenis_invoice'     => $val->jenis_invoice,
				'kurs_jual'     	=> $val->kurs_jual,
				'persentase'   		=> $val->persentase,
				'kurs_bayar'       	=> $val->kurs_bayar,
				'total_invoice'     => $val->total_invoice,
				'total_invoice_idr' => $val->total_invoice_idr,
				'total_bayar'  		=> $val->total_bayar,
				'total_bayar_idr'   => $val->total_bayar_idr,
				'created_by'       	=> $val->created_by,
				'created_date'      => $val->created_date,
				'modified_date'   	=> date('Y-m-d H:i:s'),
				'modified_by'    	=> $session['id_user'],
				'id_top'   			=> $val->id_top,
				'base_cur'       	=> $val->base_cur,
				'sisa_invoice_idr'  => $val->sisa_invoice_idr,
				'sisa_invoice'      => $val->sisa_invoice,
				'kurs_baru'  		=> $kurs,
				'nilai_invoice_baru' => $nilai,
				'selisih_debit'     => $selisihdebet,
				'selisih_kredit'    => $selisihkredit,
				'tanggal'     		=> $tanggal,



			);



			$idso = $this->db->insert('tr_invoice_tutup_bulan', $datainvoice);
		}




		//UPDATE KURS UNINVOICING USD


		$this->db->query("INSERT INTO tr_invoice_retensi_tutup_bulan_history (
						id_invoice,
						id_penagihan,
						id_bq,
						no_ipp,
						so_number,
						no_invoice,
						tgl_invoice,
						nm_customer,
						jenis_invoice,
						kurs_jual,
						persentase,
						kurs_bayar,
						total_invoice,
						total_invoice_idr,
						total_bayar,
						total_bayar_idr,
						created_by,
						created_date,
						modified_date,
						modified_by,
						id_top,
						base_cur,
						sisa_invoice_idr,
						sisa_invoice,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal,
						sisa_invoice_retensi2_idr,
						sisa_invoice_retensi2,
						nilai_retensi_baru,
						selisih_debit_retensi,
						selisih_kredit_retensi
						) 
						(SELECT 
						id_invoice,
						id_penagihan,
						id_bq,
						no_ipp,
						so_number,
						no_invoice,
						tgl_invoice,
						nm_customer,
						jenis_invoice,
						kurs_jual,
						persentase,
						kurs_bayar,
						total_invoice,
						total_invoice_idr,
						total_bayar,
						total_bayar_idr,
						created_by,
						created_date,
						modified_date,
						modified_by,
						id_top,
						base_cur,
						sisa_invoice_idr,
						sisa_invoice,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal,
						sisa_invoice_retensi2_idr,
						sisa_invoice_retensi2,
						nilai_retensi_baru,
						selisih_debit_retensi,
						selisih_kredit_retensi
						FROM tr_invoice_retensi_tutup_bulan)");


		$this->db->query("DELETE FROM tr_invoice_retensi_tutup_bulan");


		$invoice2 = $this->db->query("SELECT * FROM tr_invoice_header WHERE Year(tgl_invoice)='$thn' AND month(tgl_invoice)='$bulan' AND base_cur='USD' AND sisa_invoice_retensi2 > 0")->result();

		foreach ($invoice2 as $val2) {

			$nilailama2 	    = $val2->kurs_jual * $val2->sisa_invoice_retensi2;
			$nilaibaru2 	    = $kurs2 * $val2->sisa_invoice_retensi2;
			$nilai2		   		= $nilaibaru2;
			$selisih2       	= $nilailama2 - $nilaibaru2;
			if ($selisih2 > 0) {
				$selisihdebet2  	= $selisih2;
				$selisihkredit2 	= 0;
			} elseif ($selisih2 < 0) {
				$selisihdebet2  	= 0;
				$selisihkredit2 	= $selisih * -1;
			}


			$datainvoice2 = array(

				'id_invoice'       	=> $val2->id_invoice,
				'id_penagihan'      => $val2->id_penagihan,
				'id_bq'       	 	=> $val2->id_bq,
				'no_ipp'        	=> $val2->no_ipp,
				'so_number'  		=> $val2->so_number,
				'no_invoice'        => $val2->no_invoice,
				'tgl_invoice'       => $val2->tgl_invoice,
				'nm_customer'       => $val2->nm_customer,
				'jenis_invoice'     => $val2->jenis_invoice,
				'kurs_jual'     	=> $val2->kurs_jual,
				'persentase'   		=> $val2->persentase,
				'kurs_bayar'       	=> $val2->kurs_bayar,
				'total_invoice'     => $val2->total_invoice,
				'total_invoice_idr' => $val2->total_invoice_idr,
				'total_bayar'  		=> $val2->total_bayar,
				'total_bayar_idr'   => $val2->total_bayar_idr,
				'created_by'       	=> $val2->created_by,
				'created_date'      => $val2->created_date,
				'modified_date'   	=> date('Y-m-d H:i:s'),
				'modified_by'    	=> $session['id_user'],
				'id_top'   			=> $val2->id_top,
				'base_cur'       	=> $val2->base_cur,
				'sisa_invoice_idr'  => $val2->sisa_invoice_idr,
				'sisa_invoice'      => $val2->sisa_invoice,
				'kurs_baru'  		=> $kurs,
				'nilai_invoice_baru' => $nilai2,
				'tanggal'     		=> $tanggal,
				'selisih_debit_retensi'     => $selisihdebet2,
				'selisih_debit_retensi'    => $selisihkredit2,



			);



			$idso = $this->db->insert('tr_invoice_retensi_tutup_bulan', $datainvoice2);
		}





		//UPDATE KURS UANG MUKA CUSTOMER USD


		$this->db->query("INSERT INTO tr_invoice_retensi_tutup_bulan_history (
						id_invoice,
						id_penagihan,
						id_bq,
						no_ipp,
						so_number,
						no_invoice,
						tgl_invoice,
						nm_customer,
						jenis_invoice,
						kurs_jual,
						persentase,
						kurs_bayar,
						total_invoice,
						total_invoice_idr,
						total_bayar,
						total_bayar_idr,
						created_by,
						created_date,
						modified_date,
						modified_by,
						id_top,
						base_cur,
						sisa_invoice_idr,
						sisa_invoice,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal,
						sisa_invoice_retensi2_idr,
						sisa_invoice_retensi2,
						selisih_debit_retensi,
						selisih_kredit_retensi
						) 
						(SELECT 
						id_invoice,
						id_penagihan,
						id_bq,
						no_ipp,
						so_number,
						no_invoice,
						tgl_invoice,
						nm_customer,
						jenis_invoice,
						kurs_jual,
						persentase,
						kurs_bayar,
						total_invoice,
						total_invoice_idr,
						total_bayar,
						total_bayar_idr,
						created_by,
						created_date,
						modified_date,
						modified_by,
						id_top,
						base_cur,
						sisa_invoice_idr,
						sisa_invoice,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal,
						sisa_invoice_retensi2_idr,
						sisa_invoice_retensi2,
						selisih_debit_retensi,
						selisih_kredit_retensi
						FROM tr_invoice_retensi_tutup_bulan)");


		$this->db->query("DELETE FROM tr_invoice_retensi_tutup_bulan");


		$invoice2 = $this->db->query("SELECT * FROM tr_invoice_header WHERE Year(tgl_invoice)='$thn' AND month(tgl_invoice)='$bulan' AND base_cur='USD' AND sisa_invoice_retensi2 > 0")->result();

		foreach ($invoice2 as $val2) {

			$nilailama2 	    = $val2->kurs_jual * $val2->sisa_invoice_retensi2;
			$nilaibaru2 	    = $kurs2 * $val2->sisa_invoice_retensi2;
			$nilai2		   		= $nilaibaru2;
			$selisih2       	= $nilailama2 - $nilaibaru2;
			if ($selisih2 > 0) {
				$selisihdebet2  	= $selisih2;
				$selisihkredit2 	= 0;
			} elseif ($selisih2 < 0) {
				$selisihdebet2  	= 0;
				$selisihkredit2 	= $selisih * -1;
			}


			$datainvoice2 = array(

				'id_invoice'       	=> $val2->id_invoice,
				'id_penagihan'      => $val2->id_penagihan,
				'id_bq'       	 	=> $val2->id_bq,
				'no_ipp'        	=> $val2->no_ipp,
				'so_number'  		=> $val2->so_number,
				'no_invoice'        => $val2->no_invoice,
				'tgl_invoice'       => $val2->tgl_invoice,
				'nm_customer'       => $val2->nm_customer,
				'jenis_invoice'     => $val2->jenis_invoice,
				'kurs_jual'     	=> $val2->kurs_jual,
				'persentase'   		=> $val2->persentase,
				'kurs_bayar'       	=> $val2->kurs_bayar,
				'total_invoice'     => $val2->total_invoice,
				'total_invoice_idr' => $val2->total_invoice_idr,
				'total_bayar'  		=> $val2->total_bayar,
				'total_bayar_idr'   => $val2->total_bayar_idr,
				'created_by'       	=> $val2->created_by,
				'created_date'      => $val2->created_date,
				'modified_date'   	=> date('Y-m-d H:i:s'),
				'modified_by'    	=> $session['id_user'],
				'id_top'   			=> $val2->id_top,
				'base_cur'       	=> $val2->base_cur,
				'sisa_invoice_idr'  => $val2->sisa_invoice_idr,
				'sisa_invoice'      => $val2->sisa_invoice,
				'kurs_baru'  		=> $kurs,
				'nilai_invoice_baru' => $nilai2,
				'tanggal'     		=> $tanggal,
				'selisih_debit_retensi'     => $selisihdebet2,
				'selisih_kredit_retensi'    => $selisihkredit2,



			);



			$idso = $this->db->insert('tr_invoice_retensi_tutup_bulan', $datainvoice2);
		}
	}

	function create_jurnal_akhir_bulan()
	{


		$data_jurnal = $this->db->query("SELECT * FROM tr_invoice_tutup_bulan")->result();

		foreach ($data_jurnal as $jr) {

			$tanggal  = $jr->tanggal;
			$invoice  = $jr->no_invoice;

			$Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tanggal);

			$Bln 			= substr($tanggal, 5, 2);
			$Thn 			= substr($tanggal, 0, 4);



			$totaldebet     = $jr->selisih_debit;
			$totalkredit    = $jr->selisih_kredit;

			$kurs		= $jr->kurs_baru;
			$Id_Inv		= $jr->id_invoice;

			$Keterangan    = 'Selisih Kurs Piutang USD $kurs periode $Bln-$Thn' . ($invoice);

			if ($totaldebet != 0) {
				$totalselisih = $jr->selisih_debit;
			} else {
				$totalselisih = $jr->selisih_kredit;
			}

			$dataJVhead[] = array(
				'nomor' => $Nomor_JV,
				'tgl' => $tanggal,
				'jml' => $totalselisih,
				'koreksi_no' => '-',
				'kdcab' => '101',
				'jenis' => 'JV',
				'keterangan' => $Keterangan,
				'bulan' => $Bln,
				'tahun' => $Thn,
				'user_id' => '',
				'memo' => $invoice,
				'tgl_jvkoreksi' => $tanggal,
				'ho_valid' => ''
			);




			if ($totaldebet != 0) {
				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '7101-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => $totaldebet,
					'kredit'        => 0
				);


				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '1102-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => 0,
					'kredit'        => $totaldebet
				);
			} else {

				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '1102-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => $totalkredit,
					'kredit'        => 0
				);


				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '7101-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => 0,
					'kredit'        => $totalkredit
				);
			}


			// ## UPDATE AR ##
			$Query_AR	= "UPDATE tr_invoice_header SET kurs_bayar=$kurs WHERE  id_invoice=$Id_Inv";
			$this->db->query($Query_AR);
		}

		## INSERT JURNAL ##
		// $this->db->insert_batch(DBACC.'.javh',$dataJVhead);
		// $this->db->insert_batch(DBACC.'.jurnal',$det_Jurnal);
		// $Qry_Update_Cabang_acc	 = "UPDATE ".DBACC.".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
		// $this->db->query($Qry_Update_Cabang_acc);



		$this->create_jurnal_akhir_bulan_retensi();
	}


	function create_jurnal_akhir_bulan_retensi()
	{


		$data_jurnal = $this->db->query("SELECT * FROM tr_invoice_retensi_tutup_bulan")->result();

		foreach ($data_jurnal as $jr) {

			$tanggal  = $jr->tanggal;
			$invoice  = $jr->no_invoice;

			$Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tanggal);

			$Bln 			= substr($tanggal, 5, 2);
			$Thn 			= substr($tanggal, 0, 4);



			$totaldebet     = $jr->selisih_debit;
			$totalkredit    = $jr->selisih_kredit;

			$kurs		= $jr->kurs_baru;
			$Id_Inv		= $jr->id_invoice;


			$Keterangan    = 'Selisih Kurs UN INVOICING USD $kurs periode $Bln-$Thn' . ($invoice);

			if ($totaldebet != 0) {
				$totalselisih = $jr->selisih_debit;
			} else {
				$totalselisih = $jr->selisih_kredit;
			}

			$dataJVhead[] = array(
				'nomor' => $Nomor_JV,
				'tgl' => $tanggal,
				'jml' => $totalselisih,
				'koreksi_no' => '-',
				'kdcab' => '101',
				'jenis' => 'JV',
				'keterangan' => $Keterangan,
				'bulan' => $Bln,
				'tahun' => $Thn,
				'user_id' => '',
				'memo' => $invoice,
				'tgl_jvkoreksi' => $tanggal,
				'ho_valid' => ''
			);




			if ($totaldebet != 0) {
				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '7101-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => $totaldebet,
					'kredit'        => 0
				);


				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '1102-01-04',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => 0,
					'kredit'        => $totaldebet
				);
			} else {

				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '1102-01-04',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => $totalkredit,
					'kredit'        => 0
				);


				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '7101-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $invoice,
					'debet'         => 0,
					'kredit'        => $totalkredit
				);
			}


			// ## UPDATE AR ##
			$Query_AR	= "UPDATE tr_invoice_header SET kurs_bayar=$kurs WHERE  id_invoice=$Id_Inv";
			$this->db->query($Query_AR);
		}

		## INSERT JURNAL ##
		// $this->db->insert_batch(DBACC.'.javh',$dataJVhead);
		// $this->db->insert_batch(DBACC.'.jurnal',$det_Jurnal);
		// $Qry_Update_Cabang_acc	 = "UPDATE ".DBACC.".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
		// $this->db->query($Qry_Update_Cabang_acc);



		$this->index_akhir_bulan();
	}


	public function index_bank_akhir_bulan()
	{

		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$so = $this->penerimaan_model->get_data_bank();
		$data = array(
			'title'			=> 'Penerimaan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'results'			=> $so,
		);
		history('View Akhir Bulan');
		$this->load->view('Penerimaan/list_bank_akhir_bulan', $data);
	}

	function update_bank()
	{

		//PROSES JURNAL
		$session = $this->session->userdata('app_session');
		$post    = $this->input->post();
		$kurs    = $post['kurs'];
		$tanggal = $post['tgl_update'];
		$bulan = date("m", strtotime($tanggal));
		$thn = date("Y", strtotime($tanggal));

		$data_jurnal = $this->db->query("SELECT * FROM tr_saldo_bank")->result();

		foreach ($data_jurnal as $jr) {

			$bank  = $jr->kd_bank;

			$Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tanggal);

			$Bln 			= substr($tanggal, 5, 2);
			$Thn 			= substr($tanggal, 0, 4);

			$Keterangan    = 'Selisih Kurs Bank' . ($bank);

			$akhir_usd     = $jr->saldo_akhir;
			$akhir_idr     = $jr->saldo_akhir_idr;

			$nilai_tutup    = $akhir_usd * $kurs;
			$selisih		= $akhir_idr - $nilai_tutup;

			$dataJVhead[] = array(
				'nomor' => $Nomor_JV,
				'tgl' => $tanggal,
				'jml' => $selisih,
				'koreksi_no' => '-',
				'kdcab' => '101',
				'jenis' => 'JV',
				'keterangan' => $Keterangan,
				'bulan' => $Bln,
				'tahun' => $Thn,
				'user_id' => '',
				'memo' => $bank,
				'tgl_jvkoreksi' => $tanggal,
				'ho_valid' => ''
			);




			if ($selisih < 0) {
				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '7101-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $bank,
					'debet'         => $selisih * -1,
					'kredit'        => 0
				);


				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => $bank,
					'keterangan'    => $Keterangan,
					'no_reff'       => $bank,
					'debet'         => 0,
					'kredit'        => $selisih * -1,
				);
			} else {

				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => $bank,
					'keterangan'    => $Keterangan,
					'no_reff'       => $bank,
					'debet'         => $selisih,
					'kredit'        => 0
				);


				$det_Jurnal[]			  = array(
					'nomor'         => $Nomor_JV,
					'tanggal'       => $tanggal,
					'tipe'          => 'JV',
					'no_perkiraan'  => '7101-01-02',
					'keterangan'    => $Keterangan,
					'no_reff'       => $bank,
					'debet'         => 0,
					'kredit'        => $selisih
				);
			}


			## UPDATE AR ##
			$Query_AR	= "UPDATE tr_invoice_header SET kurs_bayar=$kurs WHERE  id_invoice=$Id_Inv";
			$this->db->query($Query_AR);
		}

		// ## INSERT JURNAL ##
		// $this->db->insert_batch(DBACC.'.javh',$dataJVhead);
		// $this->db->insert_batch(DBACC.'.jurnal',$det_Jurnal);
		// $Qry_Update_Cabang_acc	 = "UPDATE ".DBACC.".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
		// $this->db->query($Qry_Update_Cabang_acc);



		$this->index_bank_akhir_bulan();
	}
	function printout($kd_bayar)
	{
		$data = array(
			'kodebayar' => $kd_bayar,
		);
		$this->load->view('print_penerimaan', $data);
	}

	function printout_pn()
	{
		$kd_bayar = $this->uri->segment('3');
		$data = array(
			'kodebayar' => $kd_bayar,
		);
		$this->load->view('Penerimaan/print_penerimaan', $data);
	}

	function printout_draft()
	{
		$kd_bayar = $this->uri->segment('3');
		$data = array(
			'kodebayar' => $kd_bayar,
		);
		$this->load->view('Penerimaan/print_penerimaandraft', $data);
	}



	public function update_hutang_akhir_bulan()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$so                 = $this->penerimaan_model->get_data_pn();
		$data = array(
			'title'			=> 'Update Kurs Hutang USD Akhir Bulan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'results'		=> $so,
		);
		history('Update Invoice Akhir Bulan');
		$data['proses'] = 0;
		$this->load->view('Penerimaan/index_update_hutang_akhir_bulan', $data);
	}

	function update_kartu_hutang()
	{

		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$so                 = $this->penerimaan_model->get_data_pn();

		$session = $this->session->userdata('app_session');
		$post    = $this->input->post();
		$kurs    = $post['kurs'];
		$tanggal = $post['tgl_update'];
		$bulan 	 = date("m", strtotime($tanggal));
		$thn 	 = date("Y", strtotime($tanggal));

		$this->db->query("INSERT INTO tran_material_po_header_tutup_bulan_history (
						no_po,
						id_supplier,
						nm_supplier,
						total_material,
						total_price,
						status,
						incoterms,
						request_date,
						tax,
						top,
						remarks,
						created_by,
						created_date,
						deleted,
						deleted_by,
						deleted_date,
						updated_by,
						updated_date,
						buyer,
						mata_uang,
						status1,
						approval1_by,
						approval1_date,
						reason1,
						status2,
						approval2_by,
						approval2_date,
						reason2,
						total_po,
						discount,
						net_price,
						net_plus_tax,
						delivery_cost,
						tgl_dibutuhkan,
						npwp,
						phone,
						repeat_po,
						valid_date,
						terima_barang_kurs,
						terima_barang_idr,
						nilai_ppn,
						uang_muka,
						proses_uang_muka,
						nilai_total,
						nilai_plus_ppn,
						nilai_total_rupiah,
						nilai_plus_ppn_rupiah,
						total_bayar,
						total_bayar_rupiah,
						nilai_dp,
						sisa_dp,
						total_terima_barang_idr,
						status_po,
						nilai_terima_barang_kurs,
						nilai_dp_kurs,
						status_id,
						uang_muka_persen,
						amount_words,
						retur,
						hutang_kurs,
						hutang_idr,
						bayar_kurs,
						bayar_idr,
						selisih_kurs,
						kurs_terima,
						sisa_hutang_kurs,
						sisa_hutang_idr,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal
						) 
						(SELECT 
						no_po,
						id_supplier,
						nm_supplier,
						total_material,
						total_price,
						status,
						incoterms,
						request_date,
						tax,
						top,
						remarks,
						created_by,
						created_date,
						deleted,
						deleted_by,
						deleted_date,
						updated_by,
						updated_date,
						buyer,
						mata_uang,
						status1,
						approval1_by,
						approval1_date,
						reason1,
						status2,
						approval2_by,
						approval2_date,
						reason2,
						total_po,
						discount,
						net_price,
						net_plus_tax,
						delivery_cost,
						tgl_dibutuhkan,
						npwp,
						phone,
						repeat_po,
						valid_date,
						terima_barang_kurs,
						terima_barang_idr,
						nilai_ppn,
						uang_muka,
						proses_uang_muka,
						nilai_total,
						nilai_plus_ppn,
						nilai_total_rupiah,
						nilai_plus_ppn_rupiah,
						total_bayar,
						total_bayar_rupiah,
						nilai_dp,
						sisa_dp,
						total_terima_barang_idr,
						status_po,
						nilai_terima_barang_kurs,
						nilai_dp_kurs,
						status_id,
						uang_muka_persen,
						amount_words,
						retur,
						hutang_kurs,
						hutang_idr,
						bayar_kurs,
						bayar_idr,
						selisih_kurs,
						kurs_terima,
						sisa_hutang_kurs,
						sisa_hutang_idr,
						kurs_baru,
						nilai_invoice_baru,
						selisih_debit,
						selisih_kredit,
						tanggal
						FROM tran_material_po_header_tutup_bulan)");


		$this->db->query("DELETE FROM tran_material_po_header_tutup_bulan");



		$hutang = $this->db->query("SELECT * FROM tran_material_po_header WHERE Year(created_date)='$thn' AND month(created_date)='$bulan' AND mata_uang='USD' AND sisa_hutang_kurs > 0")->result();

		foreach ($hutang as $val2) {

			$nilailama2 	    = $val2->kurs_terima * $val2->sisa_hutang_kurs;
			$nilaibaru2 	    = $kurs * $val2->sisa_hutang_kurs;
			$nilai2		   		= $nilaibaru2;
			$selisih2       	= $nilailama2 - $nilaibaru2;
			if ($selisih2 > 0) {
				$selisihdebet2  	= $selisih2;
				$selisihkredit2 	= 0;
			} elseif ($selisih2 < 0) {
				$selisihdebet2  	= 0;
				$selisihkredit2 	= $selisih2 * -1;
			} elseif ($selisih2 == 0) {
				$selisihdebet2  	= 0;
				$selisihkredit2 	= 0;
			}




			$datahutang = array(
				'no_po' 		=> $val2->no_po,
				'id_supplier'  	=> $val2->id_supplier,
				'nm_supplier'	=> $val2->nm_supplier,
				'total_material' => $val2->total_material,
				'total_price' 	=> $val2->total_price,
				'status'		=> $val2->status,
				'incoterms'		=> $val2->incoterms,
				'request_date'	=> $val2->request_date,
				'tax'			=> $val2->tax,
				'top'			=> $val2->top,
				'remarks'		=> $val2->remarks,
				'created_by'	=> $val2->created_by,
				'created_date'	=> $val2->created_date,
				'deleted'		=> $val2->deleted,
				'deleted_by'	=> $val2->deleted_by,
				'deleted_date'	=> $val2->deleted_date,
				'updated_by'	=> $val2->updated_by,
				'updated_date'	=> $val2->updated_date,
				'buyer'			=> $val2->buyer,
				'mata_uang'		=> $val2->mata_uang,
				'status1'		=> $val2->status1,
				'approval1_by'	=> $val2->approval1_by,
				'approval1_date' => $val2->approval1_date,
				'reason1'		=> $val2->reason1,
				'status2'		=> $val2->status2,
				'approval2_by'	=> $val2->approval2_by,
				'approval2_date' => $val2->approval2_date,
				'reason2'		=> $val2->reason2,
				'total_po'		=> $val2->total_po,
				'discount'		=> $val2->discount,
				'net_price'		=> $val2->net_price,
				'net_plus_tax'	=> $val2->net_plus_tax,
				'delivery_cost' => $val2->delivery_cost,
				'tgl_dibutuhkan' => $val2->tgl_dibutuhkan,
				'npwp'			=> $val2->npwp,
				'phone'			=> $val2->phone,
				'repeat_po'		=> $val2->repeat_po,
				'valid_date'	=> $val2->valid_date,
				'terima_barang_kurs' => $val2->terima_barang_kurs,
				'terima_barang_idr' => $val2->terima_barang_idr,
				'nilai_ppn'		=> $val2->nilai_ppn,
				'uang_muka'		=> $val2->uang_muka,
				'proses_uang_muka' => $val2->proses_uang_muka,
				'nilai_total' 	=> $val2->nilai_total,
				'nilai_plus_ppn' => $val2->nilai_plus_ppn,
				'nilai_total_rupiah' => $val2->nilai_total_rupiah,
				'nilai_plus_ppn_rupiah' => $val2->nilai_plus_ppn_rupiah,
				'total_bayar' => $val2->total_bayar,
				'total_bayar_rupiah' => $val2->total_bayar_rupiah,
				'nilai_dp' => $val2->nilai_dp,
				'sisa_dp' => $val2->sisa_dp,
				'total_terima_barang_idr' => $val2->total_terima_barang_idr,
				'status_po' => $val2->status_po,
				'nilai_terima_barang_kurs' => $val2->nilai_terima_barang_kurs,
				'nilai_dp_kurs' => $val2->nilai_dp_kurs,
				'status_id' => $val2->status_id,
				'uang_muka_persen' => $val2->uang_muka_persen,
				'amount_words' => $val2->amount_words,
				'retur' => $val2->retur,
				'hutang_kurs' => $val2->hutang_kurs,
				'hutang_idr' => $val2->hutang_idr,
				'bayar_kurs' => $val2->bayar_kurs,
				'bayar_idr' => $val2->bayar_idr,
				'selisih_kurs' => $val2->selisih_kurs,
				'kurs_terima' => $val2->kurs_terima,
				'sisa_hutang_kurs' => $val2->sisa_hutang_kurs,
				'sisa_hutang_idr' => $val2->sisa_hutang_idr,
				'kurs_baru'  		=> $kurs,
				'nilai_invoice_baru' => $nilai2,
				'tanggal'     		=> $tanggal,
				'selisih_debit'     => $selisihdebet2,
				'selisih_kredit'    => $selisihkredit2,
			);
			$idso = $this->db->insert('tran_material_po_header_tutup_bulan', $datahutang);
		}


		$data_jurnal = $this->db->query("SELECT * FROM tran_material_po_header_tutup_bulan")->result();

		foreach ($data_jurnal as $jr) {
			$no_po = $jr->no_po;

			## UPDATE AP ##
			$Query_AP	= "UPDATE tran_material_po_header SET kurs_terima=$kurs, sisa_hutang_idr=sisa_hutang_kurs*$kurs WHERE  no_po='$no_po'";
			$this->db->query($Query_AP);
		}

		$data = array(
			'title'			=> 'Update Kurs Hutang USD Akhir Bulan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'results'		=> $so,
		);
		history('Proses Update Invoice Akhir Bulan');

		$data['proses'] = 1;
		$this->load->view('Penerimaan/index_update_hutang_akhir_bulan', $data);
	}

	public function add_invoice()
	{
		$id_invoice = $this->input->post('id_invoice');
		$no = $this->input->post('no');

		$valid = 1;
		// $get_invoice = $this->db->get_where('tr_invoice_sales', array('id_invoice' => $id_invoice))->row();


		$this->db->select('a.*, c.nm_customer');
		$this->db->from('tr_invoice_sales a');
		$this->db->join('tr_sales_order b', 'b.no_so = a.id_so', 'left');
		$this->db->join('customer c', 'c.id_customer = b.id_customer', 'left');
		$this->db->where('a.id_invoice', $id_invoice);
		$this->db->group_by('a.id_invoice');
		$get_invoice = $this->db->get()->row();
		if (!$get_invoice) {
			$valid = 0;
		}

		$sisa_invoice = 0;
		if (!empty($get_invoice)) {
			$sisa_invoice = ($get_invoice->nilai_invoice - $get_invoice->total_bayar);
		}

		echo json_encode([
			'status' => $valid,
			'sisa_invoice' => $sisa_invoice,
			'data_invoice' => $get_invoice
		]);
	}
}
