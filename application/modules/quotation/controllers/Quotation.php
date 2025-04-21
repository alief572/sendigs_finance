<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quotation extends Admin_Controller
{

	//Permission

	protected $viewPermission   = "Quotation.View";
	protected $addPermission    = "Quotation.Add";
	protected $managePermission = "Quotation.Manage";
	protected $deletePermission = "Quotation.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model('Quotation/quotation_model');
		$this->template->title('Quotation');
		$this->template->page_icon('fa fa-building-o');
		date_default_timezone_set('Asia/Bangkok');
	}


	public function index()
	{
		$this->template->page_icon('fa fa-list');
		$data = $this->quotation_model->get_data_quotation();
		$get_curr = $this->db->get_where('mata_uang', ['deleted' => null])->result();
		$this->template->set('results', $data);
		$this->template->set('list_curr', $get_curr);
		$this->template->title('Indeks Of Quotation');
		$this->template->render('list_quotation');
	}

	public function print_quotation($no_penawaran, $show_disc = null)
	{
		$this->template->page_icon('fa fa-list');

		$get_penawaran = $this->db->query('SELECT a.*, b.nm_customer, b.alamat, b.telpon, c.name as nama_top FROM tr_penawaran a LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN list_help c ON c.id = a.top WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
		$get_penawaran_detail = $this->db->query('
			SELECT 
				a.*, 
				e.kode as code, 
				c.code as unit_packing, 
				d.code as unit_measure, 
				e.variant_product, 
				e.color, 
				e.surface 
			FROM 
				tr_penawaran_detail a 
				LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_category3 
				LEFT JOIN ms_satuan c ON c.id = b.id_unit_packing 
				LEFT JOIN ms_satuan d ON d.id = b.id_unit 
				LEFT JOIN bom_header e ON e.no_bom = a.no_bom 
			WHERE 
				a.no_penawaran = "' . $no_penawaran . '" 
			GROUP BY a.id_penawaran_detail
			ORDER BY a.id_penawaran_detail ASC
		')->result();

		if ($get_penawaran->quote_by == "ORINDO") {
			$logo = '<img src="' . base_url('assets/images/orindo_logo.png') . '" width="200" alt="" srcset="" style="padding-top: 40px;">';
			$pt_name = 'PT Orindo Eratec';
		} else {
			$logo = '<img src="' . base_url('assets/images/ori_logo2.png') . '" width="95" alt="" srcset="">';
			$pt_name = 'PT Origa Mulia FRP';
		}

		$get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

		$get_other_item = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

		if ($show_disc !== null) {
			$data = [
				'data_penawaran' => $get_penawaran,
				'data_penawaran_detail' => $get_penawaran_detail,
				'logo' => $logo,
				'list_other_cost' => $get_other_cost,
				'show_disc' => $show_disc,
				'pt_name' => $pt_name,
				'list_other_item' => $get_other_item
			];
		} else {
			$data = [
				'data_penawaran' => $get_penawaran,
				'data_penawaran_detail' => $get_penawaran_detail,
				'logo' => $logo,
				'list_other_cost' => $get_other_cost,
				'pt_name' => $pt_name,
				'list_other_item' => $get_other_item
			];
		}
		// $this->template->set('results', $data);
		// $this->template->title('Print Quotation');
		$this->load->view('print_quotation', ['results' => $data]);
	}

	public function print_quotation_non_ppn($no_penawaran, $show_disc = null)
	{
		$this->template->page_icon('fa fa-list');

		$get_penawaran = $this->db->query('SELECT a.*, b.nm_customer, b.alamat, b.telpon, c.name as nama_top FROM tr_penawaran a LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN list_help c ON c.id = a.top WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
		$get_penawaran_detail = $this->db->query('SELECT a.*, e.kode as code, c.code as unit_packing, d.code as unit_measure, e.variant_product, e.color, e.surface FROM tr_penawaran_detail a LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_category3 LEFT JOIN ms_satuan c ON c.id = b.id_unit_packing LEFT JOIN ms_satuan d ON d.id = b.id_unit LEFT JOIN bom_header e ON e.no_bom = a.no_bom WHERE a.no_penawaran = "' . $no_penawaran . '" GROUP BY a.id_penawaran_detail ORDER BY a.id_penawaran_detail ASC')->result();

		if ($get_penawaran->quote_by == "ORINDO") {
			$logo = '<img src="' . base_url('assets/images/orindo_logo.png') . '" width="200" alt="" srcset="" style="padding-top: 40px;">';
			$pt_name = 'PT Orindo Eratec';
		} else {
			$logo = '<img src="' . base_url('assets/images/ori_logo2.png') . '" width="95" alt="" srcset="">';
			$pt_name = 'PT Origa Mulia FRP';
		}


		$get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();

		$get_other_item = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

		if ($show_disc !== null) {
			$data = [
				'data_penawaran' => $get_penawaran,
				'data_penawaran_detail' => $get_penawaran_detail,
				'logo' => $logo,
				'list_other_cost' => $get_other_cost,
				'show_disc' => $show_disc,
				'pt_name' => $pt_name,
				'list_other_item' => $get_other_item
			];
		} else {
			$data = [
				'data_penawaran' => $get_penawaran,
				'data_penawaran_detail' => $get_penawaran_detail,
				'logo' => $logo,
				'list_other_cost' => $get_other_cost,
				'pt_name' => $pt_name,
				'list_other_item' => $get_other_item
			];
		}
		// $this->template->set('results', $data);
		// $this->template->title('Print Quotation');
		$this->load->view('print_quotation_non_ppn', ['results' => $data]);
	}

	public function server_side_inv()
	{
		$this->quotation_model->get_data_json_inv();
	}
	public function create_penerimaan()
	{
		$this->invoicing_model->list_top();
	}

	public function server_side_payment()
	{
		$this->quotation_model->get_data_json_payment();
	}
	public function server_side_top()
	{
		$this->invoicing_model->get_data_json_top();
	}

	public function modal_detail_invoice($no_penawaran = null)
	{
		$this->quotation_model->modal_detail_invoice($no_penawaran);
	}

	public function modal_add_invoice($curr)
	{
		$this->quotation_model->modal_add_invoice($curr);
	}

	public function approval_quotation($no_penawaran)
	{
		$this->quotation_model->approval_quotation($no_penawaran);
	}

	public function view_quotation($no_penawaran)
	{
		$this->quotation_model->view_quotation($no_penawaran);
	}

	public function modal_detail_invoice_np()
	{
		$this->quotation_model->modal_detail_invoice_np($this->uri->segment(3));
	}

	public function view_penerimaan()
	{
		$kd_bayar = $this->uri->segment(3);
		$bank1			 = $this->Jurnal_model->get_Coa_Bank_Aja('101');
		$data = array(
			'datbank' => $bank1,
			'kodebayar' => $kd_bayar,
		);
		$this->load->view('view_penerimaan', $data);
	}

	public function save_penerimaan()
	{

		// print_r($this->input->post());
		// exit;
		$session = $this->session->userdata('app_session');

		$post = $this->input->post();

		$no_surat = $post['no_surat'];


		$this->db->trans_begin();

		if ($no_surat == '' || $no_surat == $this->auth->user_id()) {
			$no_penawaran = $this->quotation_model->generate_no_penawaran();

			$get_ttl_detail = $this->db->query("SELECT SUM(a.harga_satuan * a.qty) AS ttl_harga, SUM(a.total_harga) AS ttl_harga_after_disc FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $session['id_user'] . "'")->row();

			$get_ttl_other_cost = $this->db->select('SUM(a.total_nilai) AS ttl_other_cost')->get_where('tr_penawaran_other_cost a', ['a.id_penawaran' => $session['id_user']])->row();
			$get_ttl_other_item = $this->db->select('SUM(a.total) AS ttl_other_item')->get_where('tr_penawaran_other_item a', ['a.id_penawaran' => $session['id_user']])->row();

			$persen_ppn = 11;
			if ($post['ppn_check'] !== '11') {
				$persen_ppn = 0;
			}

			$nilai_ppn = (($get_ttl_detail->ttl_harga_after_disc + $get_ttl_other_cost->ttl_other_cost + $get_ttl_other_item->ttl_other_item) * $persen_ppn / 100);

			$this->db->insert('tr_penawaran', [
				'no_penawaran' => $no_penawaran,
				'quote_by' => $post['quote_by'],
				'tgl_penawaran' => $post['tanggal'],
				'id_customer' => $post['id_customer'],
				'pic_customer' => $post['pic_customer'],
				'top' => $post['term_of_payment'],
				'notes' => $post['notes'],
				'project' => $post['project'],
				'email_customer' => $post['email_customer'],
				'id_sales' => $session['id_user'],
				'nama_sales' => $session['nm_lengkap'],
				'nilai_ppn' => $nilai_ppn,
				'ppn' => $persen_ppn,
				'nilai_penawaran' => $get_ttl_detail->ttl_harga,
				'created_by' => $session['id_user'],
				'created_on' => date('Y-m-d H:i:s'),
				'subject' => $post['subject'],
				'time_delivery' => $post['time_delivery'],
				'offer_period' => $post['offer_period'],
				'delivery_term' => $post['delivery_term'],
				'warranty' => $post['warranty'],
				'currency' => $post['curr']
			]);

			$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result_array();
			foreach ($get_penawaran_detail as $penawaran_detail) :
				$this->db->update('tr_penawaran_detail', ['ukuran_potongan' => $post['ukuran_potong_' . $penawaran_detail['id_penawaran_detail']]], ['id_penawaran_detail' => $penawaran_detail['id_penawaran_detail']]);
			endforeach;

			$this->db->update('tr_penawaran_detail', [
				'no_penawaran' => $no_penawaran,
			], [
				'no_penawaran' => $session['id_user']
			]);

			$this->db->update('tr_penawaran_other_cost', [
				'id_penawaran' => $no_penawaran
			], [
				'id_penawaran' => $session['id_user']
			]);

			$this->db->update('tr_penawaran_other_item', [
				'id_penawaran' => $no_penawaran
			], [
				'id_penawaran' => $session['id_user']
			]);

			// print_r($session);
		} else {

			$get_ttl_detail = $this->db->query("SELECT SUM(a.harga_satuan * a.qty) AS ttl_harga, SUM(a.total_harga) AS ttl_harga_after_disc FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $no_surat . "'")->row();

			$get_ttl_other_cost = $this->db->select('SUM(a.total_nilai) AS ttl_other_cost')->get_where('tr_penawaran_other_cost a', ['a.id_penawaran' => $no_surat])->row();
			$get_ttl_other_item = $this->db->select('SUM(a.total) AS ttl_other_item')->get_where('tr_penawaran_other_item a', ['a.id_penawaran' => $no_surat])->row();

			$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $no_surat])->row();

			$persen_ppn = 11;
			if ($post['ppn_check'] !== '11') {
				$persen_ppn = 0;
			}

			$nilai_ppn = (($get_ttl_detail->ttl_harga_after_disc + $get_ttl_other_cost->ttl_other_cost + $get_ttl_other_item->ttl_other_item) * $persen_ppn / 100);

			if (isset($post['select_action'])) {
				if ($get_penawaran->status == '2') {
					$sts = 1;
				} else {
					$sts = ($get_penawaran->status + 1);
				}
				$this->db->update('tr_penawaran', [
					'quote_by' => $post['quote_by'],
					'tgl_penawaran' => $post['tanggal'],
					'id_customer' => $post['id_customer'],
					'pic_customer' => $post['pic_customer'],
					'top' => $post['term_of_payment'],
					'notes' => $post['notes'],
					'project' => $post['project'],
					'email_customer' => $post['email_customer'],
					'id_sales' => $session['id_user'],
					'nama_sales' => $session['nm_lengkap'],
					'nilai_ppn' => $nilai_ppn,
					'ppn' => $persen_ppn,
					'nilai_penawaran' => $get_ttl_detail->ttl_harga,
					'status' => $sts,
					'no_revisi' => ($get_penawaran->no_revisi + 1),
					'approved_by' => $session['id_user'],
					'approved_on' => date('Y-m-d H:i:s'),
					'subject' => $post['subject'],
					'time_delivery' => $post['time_delivery'],
					'offer_period' => $post['offer_period'],
					'delivery_term' => $post['delivery_term'],
					'warranty' => $post['warranty']
				], [
					'no_penawaran' => $no_surat
				]);
			} else {
				if ($get_penawaran->req_app1 !== null || $get_penawaran->req_app2 !== null || $get_penawaran->req_app3 !== null) {

					$get_ttl_detail = $this->db->query("SELECT SUM(a.harga_satuan * a.qty) AS ttl_harga, SUM(a.total_harga) AS ttl_harga_after_disc FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $no_surat . "'")->row();

					$get_ttl_other_cost = $this->db->select('SUM(a.total_nilai) AS ttl_other_cost')->get_where('tr_penawaran_other_cost a', ['a.id_penawaran' => $no_surat])->row();
					$get_ttl_other_item = $this->db->select('SUM(a.total) AS ttl_other_item')->get_where('tr_penawaran_other_item a', ['a.id_penawaran' => $no_surat])->row();

					$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $no_surat])->row();

					if ($get_penawaran->status == '2') {
						$sts = 1;
					} else {
						$sts = ($get_penawaran->status + 1);
					}

					$persen_ppn = 11;
					if ($post['ppn_check'] !== '11') {
						$persen_ppn = 0;
					}

					$nilai_ppn = (($get_ttl_detail->ttl_harga_after_disc + $get_ttl_other_cost->ttl_other_cost + $get_ttl_other_item->ttl_other_item) * $persen_ppn / 100);


					$no_revisi = ($get_penawaran->no_revisi + 1);
					if ($get_penawaran->status == '2') {
						$update_quote = $this->db->update('tr_penawaran', [
							'quote_by' => $post['quote_by'],
							'tgl_penawaran' => $post['tanggal'],
							'id_customer' => $post['id_customer'],
							'pic_customer' => $post['pic_customer'],
							'top' => $post['term_of_payment'],
							'notes' => $post['notes'],
							'project' => $post['project'],
							'email_customer' => $post['email_customer'],
							'id_sales' => $session['id_user'],
							'nama_sales' => $session['nm_lengkap'],
							'nilai_ppn' => $nilai_ppn,
							'ppn' => $persen_ppn,
							'nilai_penawaran' => $get_ttl_detail->ttl_harga,
							'modified_by' => $session['id_user'],
							'modified_on' => date('Y-m-d H:i:s'),
							'no_revisi' => $no_revisi,
							'status' => 0,
							'req_app1' => null,
							'req_app2' => null,
							'req_app3' => null,
							'app_1' => null,
							'app_2' => null,
							'app_3' => null,
							'keterangan_app1' => null,
							'keterangan_app2' => null,
							'keterangan_app3' => null,
							'subject' => $post['subject'],
							'time_delivery' => $post['time_delivery'],
							'offer_period' => $post['offer_period'],
							'delivery_term' => $post['delivery_term'],
							'warranty' => $post['warranty']
						], [
							'no_penawaran' => $no_surat
						]);
						if (!$update_quote) {
							print_r($this->db->error($update_quote));
							exit;
						}
						// print_r($update_quote);
						// exit;
						// if(!$update_quote){
						// }
					} else {
						$update_quote = $this->db->update('tr_penawaran', [
							'quote_by' => $post['quote_by'],
							'tgl_penawaran' => $post['tanggal'],
							'id_customer' => $post['id_customer'],
							'pic_customer' => $post['pic_customer'],
							'top' => $post['term_of_payment'],
							'notes' => $post['notes'],
							'project' => $post['project'],
							'email_customer' => $post['email_customer'],
							'id_sales' => $session['id_user'],
							'nama_sales' => $session['nm_lengkap'],
							'nilai_ppn' => $nilai_ppn,
							'ppn' => $persen_ppn,
							'nilai_penawaran' => $get_ttl_detail->ttl_harga,
							'modified_by' => $session['id_user'],
							'modified_on' => date('Y-m-d H:i:s'),
							'no_revisi' => $no_revisi,
							'subject' => $post['subject'],
							'time_delivery' => $post['time_delivery'],
							'offer_period' => $post['offer_period'],
							'delivery_term' => $post['delivery_term'],
							'warranty' => $post['warranty']
						], [
							'no_penawaran' => $no_surat
						]);

						if (!$update_quote) {
							print_r($this->db->error($update_quote));
							exit;
						}
					}

					$id_history_penawaran = $this->quotation_model->generate_id_history();

					$insert_history = $this->db->insert('tr_history_penawaran', [
						'id_history_penawaran' => $id_history_penawaran,
						'no_penawaran' => $post['no_surat'],
						'quote_by' => $post['quote_by'],
						'tgl_penawaran' => $post['tanggal'],
						'id_customer' => $post['id_customer'],
						'pic_customer' => $post['pic_customer'],
						'email_customer' => $post['email_customer'],
						'top' => $post['term_payment'],
						'notes' => $post['notes'],
						'nilai_penawaran' => $get_ttl_detail->ttl_harga,
						'id_sales' => $get_penawaran->id_sales,
						'nama_sales' => $get_penawaran->nama_sales,
						'revisi' => $no_revisi,
						'created_by' => $get_penawaran->created_by,
						'created_on' => $get_penawaran->created_on,
						'modified_by' => $session['id_user'],
						'modified_on' => date('Y-m-d H:i:s'),
						'revisi_by' => $session['id_user'],
						'revisi_on' => date('Y-m-d H:i:s'),
						'ppn' => str_replace(',', '', str_replace('%', '', $post['persen_ppn'])),
						'nilai_ppn' => str_replace(',', '', $post['nilai_ppn']),
						'project' => $post['project'],
						'req_app1' => $get_penawaran->req_app1,
						'req_app2' => $get_penawaran->req_app2,
						'req_app3' => $get_penawaran->req_app3,
						'app_1' => $get_penawaran->app_1,
						'app_2' => $get_penawaran->app_2,
						'app_3' => $get_penawaran->app_3,
						'keterangan_app1' => $get_penawaran->keterangan_app1,
						'keterangan_app2' => $get_penawaran->keterangan_app2,
						'keterangan_app3' => $get_penawaran->keterangan_app3,
						'subject' => $post['subject'],
						'time_delivery' => $post['time_delivery'],
						'offer_period' => $post['offer_period'],
						'delivery_term' => $post['delivery_term'],
						'warranty' => $post['warranty'],
						'curr' => $post['curr'],
						'notes' => $post['notes']
					]);

					if (!$insert_history) {
						print_r($this->db->error($insert_history));
						exit;
					}



					$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_surat])->result();
					foreach ($get_penawaran_detail as $penawaran_detail) {
						// print_r($penawaran_detail->id_penawaran_detail);
						// exit;

						$ukuran_potong = $this->input->post('ukuran_potong_' . $penawaran_detail->id_penawaran_detail);

						$update_ukuran = $this->db->update('tr_penawaran_detail', [
							'ukuran_potongan' => $ukuran_potong
						], [
							'id_penawaran_detail' => $penawaran_detail->id_penawaran_detail
						]);
						if (!$update_ukuran) {
							print_r($this->db->error($update_ukuran));
							exit;
						}

						// print_r($update_product);
						// exit;

						$insert_history_detail = $this->db->insert('tr_history_penawaran_detail', [
							'id_history_penawaran' => $id_history_penawaran,
							'no_penawaran' => $penawaran_detail->no_penawaran,
							'id_category3' => $penawaran_detail->id_category3,
							'nama_produk' => $penawaran_detail->nama_produk,
							'qty' => $penawaran_detail->qty,
							'harga_satuan' => $penawaran_detail->harga_satuan,
							'stok_tersedia' => $penawaran_detail->stok_tersedia,
							'diskon_persen' => $penawaran_detail->diskon_persen,
							'diskon_nilai' => $penawaran_detail->diskon_nilai,
							'total_harga' => $penawaran_detail->total_harga,
							'keterangan' => $penawaran_detail->keterangan,
							'revisi' => $no_revisi,
							'created_by' => $penawaran_detail->created_by,
							'created_on' => $penawaran_detail->created_on,
							'modified_by' => $session['id_user'],
							'modified_on' => date("Y-m-d H:i:s"),
							'nilai_diskon' => $penawaran_detail->nilai_diskon,
							'free_stock' => $penawaran_detail->free_stock,
							'curr' => $penawaran_detail->curr,
							'ukuran_potongan' => $penawaran_detail->ukuran_potongan,
							'cutting_fee' => $penawaran_detail->cutting_fee,
							'delivery_fee' => $penawaran_detail->delivery_fee
						]);
						if (!$insert_history_detail) {
							print_r($this->db->error($insert_history_detail));
							exit;
						}
					}

					$get_penawaran_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_surat])->result();
					foreach ($get_penawaran_other_cost as $other_cost) {
						$insert_hisotry_other_cost = $this->db->insert('tr_history_penawaran_other_cost', [
							'id_history_penawaran' => $id_history_penawaran,
							'id_penawaran' => $other_cost->id_penawaran,
							'curr' => $other_cost->curr,
							'nilai' => $other_cost->nilai,
							'keterangan' => $other_cost->keterangan,
							'dibuat_oleh' => $this->auth->user_id(),
							'dibuat_tgl' => date('Y-m-d H:i:s')
						]);

						if (!$insert_hisotry_other_cost) {
							print_r($this->db->error($insert_hisotry_other_cost));
							exit;
						}
					}
					// print_r($post);
				} else {

					$get_ttl_detail = $this->db->query("SELECT SUM(a.harga_satuan * a.qty) AS ttl_harga, SUM(a.total_harga) AS ttl_harga_after_disc FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $no_surat . "'")->row();

					$get_ttl_other_cost = $this->db->select('SUM(a.total_nilai) AS ttl_other_cost')->get_where('tr_penawaran_other_cost a', ['a.id_penawaran' => $no_surat])->row();
					$get_ttl_other_item = $this->db->select('SUM(a.total) AS ttl_other_item')->get_where('tr_penawaran_other_item a', ['a.id_penawaran' => $no_surat])->row();

					$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $no_surat])->row();

					if ($get_penawaran->status == '2') {
						$sts = 1;
					} else {
						$sts = ($get_penawaran->status + 1);
					}

					$persen_ppn = 11;
					if ($post['ppn_check'] !== '11') {
						$persen_ppn = 0;
					}

					$nilai_ppn = (($get_ttl_detail->ttl_harga_after_disc + $get_ttl_other_cost->ttl_other_cost + $get_ttl_other_item->ttl_other_item) * $persen_ppn / 100);

					$update_penawaran = $this->db->update('tr_penawaran', [
						'quote_by' => $post['quote_by'],
						'tgl_penawaran' => $post['tanggal'],
						'id_customer' => $post['id_customer'],
						'pic_customer' => $post['pic_customer'],
						'top' => $post['term_of_payment'],
						'notes' => $post['notes'],
						'project' => $post['project'],
						'email_customer' => $post['email_customer'],
						'id_sales' => $session['id_user'],
						'nama_sales' => $session['nm_lengkap'],
						'nilai_ppn' => $nilai_ppn,
						'ppn' => $persen_ppn,
						'nilai_penawaran' => $get_ttl_detail->ttl_harga,
						'modified_by' => $session['id_user'],
						'modified_on' => date('Y-m-d H:i:s'),
						'subject' => $post['subject'],
						'time_delivery' => $post['time_delivery'],
						'offer_period' => $post['offer_period'],
						'delivery_term' => $post['delivery_term'],
						'warranty' => $post['warranty']
					], [
						'no_penawaran' => $no_surat
					]);
					if (!$update_penawaran) {
						print_r($this->db->error($update_penawaran));
						exit;
					}
				}
			}
		}


		// print_r($post);

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

		$nomordoc   = html_escape($data_bayar->id_customer);
		$gethd = $this->db->query("SELECT * FROM ms_customers WHERE id_customer='$nomordoc'")->row();
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
			$persentase  = number_format($val->persentase, 2);
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
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$invoice = $this->db->query("SELECT * FROM tr_invoice WHERE id_customer ='$customer' AND sisa_invoice_idr >'0'")->result();
		$data = [
			'detail' => $customer
		];
		$this->template->set('results', $data);
		$this->template->title('List Invoice');
		$this->template->render('invoice');
	}

	public function TambahInvoice_np()
	{
		$customer = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$invoice = $this->db->query("SELECT * FROM tr_invoice_np_header WHERE id_customer ='$customer' AND sisa_invoice_idr >'0'")->result();
		$data = [
			'detail' => $customer
		];
		$this->template->set('results', $data);
		$this->template->title('List Invoice');
		$this->template->render('invoice_np');
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
		$data = $this->quotation_model->get_data_pn_jurnal();
		$this->template->set('results', $data);
		$this->template->title('Jurnal Penerimaan');
		$this->template->render('index_jurnal_penerimaan');
	}


	public function save_penerimaan_np()
	{

		// print_r($this->input->post());
		// exit;
		$session = $this->session->userdata('app_session');
		$Tgl_Invoice        = $this->input->post('tgl_bayar');

		$data_session 	    = $this->session->userdata;
		$kd_bayar 			= $this->quotation_model->generate_nopn_np($Tgl_Invoice);

		if (!empty($this->input->post('bank'))) {
			// $bank = explode('|',$this->input->post('bank'));
			// $kd_bank = $bank[0];
			// $nmbank = $bank[1];

			$kd_bank  = $this->input->post('bank');
		}
		// print_r($kd_bank);
		// exit;
		$kurs = $this->input->post('kurs');
		$jumlah_total_idr = str_replace(",", "", $this->input->post('total_bank')) * $kurs;

		$unlocated =  str_replace(",", "", $this->input->post('total_bank'));
		$id_unlocated = $this->input->post('id_unlocated');

		$lebihbayar =  str_replace(",", "", $this->input->post('pakai_lebih_bayar'));
		$id_lebihbayar = $this->input->post('id_lebihbayar');

		$idcustomer = $this->input->post('customer');

		$customer =  $this->db->query("SELECT * FROM master_customers WHERE id_customer = '$idcustomer'")->row();

		$idcs   = $customer->id_customer;
		$nmcs	= html_escape($customer->name_customer);



		$data = array(
			'no_invoice' => $this->input->post('no_invoice'),
			'kd_pembayaran' => $kd_bayar,
			'jenis_reff' => '-',
			'no_reff' => '-',
			'tgl_pembayaran' => $this->input->post('tgl_bayar'),
			'kurs_bayar' => $this->input->post('kurs'),
			'jumlah_piutang' => str_replace(",", "", $this->input->post('total_invoice')),
			'jumlah_piutang_idr' => str_replace(",", "", $this->input->post('total_invoice')) * $kurs,
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
			'keterangan' => $this->input->post('ket_bayar'),
			'nm_customer' => $nmcs,
			'lebih_bayar' => str_replace(",", "", $this->input->post('pakai_lebih_bayar')),
			'tambah_lebih_bayar' => str_replace(",", "", $this->input->post('tambah_lebih_bayar')),
		);



		$this->db->insert('tr_invoice_np_payment', $data);


		for ($i = 0; $i < count($this->input->post('kode_produk')); $i++) {
			$datadetail = array(
				'kd_pembayaran'     => $kd_bayar,
				'no_invoice'        => $this->input->post('kode_produk')[$i],
				'nm_customer'       => $this->input->post('nm_customer2')[$i],
				'total_invoice_idr'    => str_replace(",", "", $this->input->post('sisa_invoice')[$i]),
				'total_bayar_idr'     => str_replace(",", "", $this->input->post('jml_bayar')[$i]),
				'sisa_invoice_idr'    => str_replace(",", "", $this->input->post('sisa_invoice')[$i]) - str_replace(",", "", $this->input->post('jml_bayar')[$i]),
				'total_pph_idr'     => str_replace(",", "", $this->input->post('pph')[$i]),
				'created_on'    => date('Y-m-d H:i:s'),
				'created_by'    => $session['id_user']
			);
			$this->db->insert('tr_invoice_np_payment_detail', $datadetail);
			//Update QTY_AVL
			$invoice = $this->input->post('kode_produk')[$i];
			$jmlbyr  = str_replace(",", "", $this->input->post('jml_bayar')[$i]);
			$Qry_Update	 = "UPDATE tr_invoice_np_header SET total_bayar_idr=total_bayar_idr + $jmlbyr, sisa_invoice_idr=sisa_invoice_idr - $jmlbyr WHERE id_invoice='$invoice'";
			$this->db->query($Qry_Update);


			$so  = $this->db->query("SELECT * FROM tr_invoice_np_header WHERE id_invoice='$invoice'")->row();
			// $no_so = $so->no_so;

			// $Qry_Update_so	 = "UPDATE tr_sales_order SET total_bayar_so=total_bayar_so + $jmlbyr WHERE no_so='$no_so'";
			// $this->db->query($Qry_Update_so);


		}
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
	public function jurnal_bum_np()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-list');
		$data = $this->quotation_model->get_data_pn_jurnal_np();
		$this->template->set('results', $data);
		$this->template->title('Jurnal Penerimaan');
		$this->template->render('index_jurnal_penerimaan_np');
	}

	public function print_penerimaan()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$kd_bayar = $this->uri->segment(3);
		$data = array(
			'kodebayar' => $kd_bayar,
		);
		$this->load->view('print_penerimaan', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Penerimaan.pdf', 'I');
	}

	public function add_item_modal()
	{
		$this->template->set('results', [
			'no_surat' => $this->input->post('no_surat')
		]);
		$this->template->title('Jurnal Penerimaan');
		$this->template->render('list_product_price');
	}

	public function data_side_product_price()
	{
		$data = $this->quotation_model->get_json_product_price();
		// print_r($data);
		// exit;

		return $data;
	}

	public function add_product_price()
	{
		$session = $this->session->userdata('app_session');

		$id_product_list = $this->input->post('id');
		$id_ukuran_jadi = $this->input->post('id_ukuran_jadi');
		$curr = $this->input->post('curr');

		$no_surat_product_list = $this->input->post('no_surat_product_list');
		if ($no_surat_product_list == '') {
			$no_surat_product_list = $session['id_user'];
		}

		$get_data = $this->db->query('SELECT a.* FROM product_price a LEFT JOIN new_inventory_4 b ON b.code_lv4	= a.code_lv4 WHERE a.id = "' . $id_product_list . '"')->row();

		$get_ukuran_jadi = $this->db->get_where('product_price_ukuran_jadi', ['id' => $id_ukuran_jadi])->row();

		$nm_product = $get_data->product_master;

		$get_stock_tersedia = $this->db->query('SELECT IF(SUM(a.actual_stock) > 0, SUM(a.actual_stock), 0) AS sum_actual_stock, IF(SUM(a.booking_stock) > 0, SUM(a.booking_stock), 0) AS sum_booking_stock FROM stock_product a WHERE a.code_lv4 = "' . $get_data->code_lv4 . '" AND a.no_bom = "' . $get_data->no_bom . '"')->row();

		$harga_produk = $get_ukuran_jadi->price_unit;
		if ($curr == 'USD') {
			if ($get_ukuran_jadi->price_unit <= 0 || $get_data->kurs <= 0) {
				$harga_produk = 0;
			} else {
				$harga_produk = ($get_ukuran_jadi->price_unit / $get_data->kurs);
			}
		}

		if ($harga_produk <= 0) {
			if ($curr == 'USD') {
				$harga_produk = $get_data->price_list;
			} else {
				$harga_produk = $get_data->price_list_idr;
			}
		}

		$this->db->trans_begin();

		$this->db->insert('tr_penawaran_detail', [
			'no_penawaran' => $no_surat_product_list,
			'id_category3' => $get_data->code_lv4,
			'nama_produk' => $nm_product,
			'harga_satuan' => $harga_produk,
			'stok_tersedia' => ($get_stock_tersedia->sum_actual_stock - $get_stock_tersedia->sum_booking_stock),
			'id_product_price' => $id_product_list,
			'no_bom' => $get_data->no_bom,
			'curr' => $curr
		]);

		if ($this->db->trans_status() === FALSE) {
			$valid = 0;
			$msg = 'Maaf, produk gagal diinput !';

			$this->db->trans_rollback();
		} else {
			$valid = 1;
			$msg = 'Selamat, produk telah berhasil diinput !';

			$this->db->trans_commit();
		}

		echo json_encode([
			'status' => $valid,
			'msg' => $msg
		]);
	}

	public function cek_detail_penawaran()
	{
		$session = $this->session->userdata('app_session');

		$id = $this->input->post('id');
		if ($id == '') {
			$id = $session['id_user'];
		}


		$post = $this->input->post();

		$curr = $post['curr'];
		$persen_ppn = 11;
		if ($post['ppn'] !== '11') {
			$persen_ppn = 0;
		}
		// $nilai_ppn = $this->input->post('nilai_ppn');


		$hasil = '';

		$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $id, 'curr' => $curr])->result();
		foreach ($get_penawaran_detail as $penawaran_detail) {

			$harga_x_qty = ($penawaran_detail->harga_satuan * $penawaran_detail->qty);
			$price_after_disc = ($penawaran_detail->harga_satuan + $penawaran_detail->cutting_fee + $penawaran_detail->delivery_fee - $penawaran_detail->diskon_nilai);
			$total_harga = ($penawaran_detail->total_harga);

			$harga_standar = 0;
			if ($curr == 'IDR') {
				$get_harga_standar = $this->db->select('price_list_idr')->get_where('product_price', ['id' => $penawaran_detail->id_product_price])->row();
				if (!empty($get_harga_standar)) {
					$harga_standar = round($get_harga_standar->price_list_idr);
				}
			} else {
				$get_harga_standar = $this->db->select('price_list')->get_where('product_price', ['id' => $penawaran_detail->id_product_price])->row();
				if (!empty($get_harga_standar)) {
					$harga_standar = round($get_harga_standar->price_list);
				}
			}

			$hasil = $hasil . '
				<tr>
					<td>
						<span>' . $penawaran_detail->nama_produk . '</span> <br><br>
						<table class="table">
							<tr>
								<td>Cut Size</td>
								<td width="2" class="text-center">:</td>
								<td>
									<input type="text" name="ukuran_potong_' . $penawaran_detail->id_penawaran_detail . '" id="" class="form-control form-control-sm ukuran_potong ukuran_potong_' . $penawaran_detail->id_penawaran_detail . '" value="' . $penawaran_detail->ukuran_potongan . '" placeholder="- Cut Size -" data-id="' . $penawaran_detail->id_penawaran_detail . '">
								</td>
							</tr>
						</table>
					</td>
					<td>
						<input type="number" name="qty_' . $penawaran_detail->id_penawaran_detail . '" value="' . $penawaran_detail->qty . '" class="form-control text-right qty qty_' . $penawaran_detail->id_penawaran_detail . '" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')">
					</td>
					<td class="text-right">
						<table class="w-100" border="0">
							<tr>
								<td class="text-center" style="vertical-align: top;">
									(' . $post['curr'] . ') 
								</td>
								<td></td>
								<td>
									<input type="text" name="input_harga" id="" class="form-control form-control-sm text-right input_harga auto_num" value="' . $penawaran_detail->harga_satuan . '" data-harga_standar="' . $harga_standar . '" data-id="' . $penawaran_detail->id_penawaran_detail . '">
								</td>
							</tr>
						</table>
						<table class="w-100" border="0">
							<tr>
								<td class="text-center" style="vertical-align: top;">Cutting Fee</td>
								<td class="text-center" style="vertical-align: top;">:</td>
								<td class="text-center" style="vertical-align: top;">
									<input type="text" name="cutting_fee_' . $penawaran_detail->id_penawaran_detail . '" id="" class="form-control cutting_fee_' . $penawaran_detail->id_penawaran_detail . ' input_cutting_fee auto_num" value="' . $penawaran_detail->cutting_fee . '" style="margin-top: 0.5vh; text-align: right" data-id="' . $penawaran_detail->id_penawaran_detail . '">
								</td>
							</tr>
							<tr>
								<td class="text-center" style="vertical-align: top;">Delivery Fee</td>
								<td class="text-center" style="vertical-align: top;">:</td>
								<td class="text-center" style="vertical-align: top;">
									<input type="text" name="delivery_fee_' . $penawaran_detail->id_penawaran_detail . '" id="" class="form-control delivery_fee_' . $penawaran_detail->id_penawaran_detail . ' input_delivery_fee auto_num" value="' . $penawaran_detail->delivery_fee . '" style="margin-top: 0.5vh; text-align: right" data-id="' . $penawaran_detail->id_penawaran_detail . '">
								</td>
							</tr>
						</table>
					</td>
					<td>
						<table class="w-100">
							<tr>
								<td>(%)</td>
								<td>
									<input type="text" name="diskon_persen_' . $penawaran_detail->id_penawaran_detail . '" id="" class="form-control diskon_persen_' . $penawaran_detail->id_penawaran_detail . '" placeholder="Input (%)" value="' . $penawaran_detail->diskon_persen . '%" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')">
								</td>
							</tr>
							<tr>
								<td>(' . $post['curr'] . ')</td>
								<td>
									<input type="text" class="form-control diskon_nilai diskon_nilai_' . $penawaran_detail->id_penawaran_detail . '" name="diskon_nilai_' . $penawaran_detail->id_penawaran_detail . '" id="" value="' . ($penawaran_detail->diskon_nilai) . '" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')">
								</td>
							</tr>
						</table>
					</td>
					<td class="text-right">
					(' . $post['curr'] . ') ' . number_format($price_after_disc, 2) . '
					</td>
					<td class="text-right">
					(' . $post['curr'] . ') ' . number_format($total_harga, 2) . '
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-danger del_product_price_' . $penawaran_detail->id_penawaran_detail . '" onclick="del_product_price(' . $penawaran_detail->id_penawaran_detail . ')"><i class="fa fa-trash"></i></button>
					</td>
				</tr>
			';
		}

		$nilai_ppn = 0;
		$total_price_before_discount = 0;
		$ttl_after_disc = 0;
		$total_nilai_discount = 0;
		$ttl_persen_discount = 0;

		$get_ttl_detail = $this->db->query("SELECT SUM(a.total_harga) AS ttl_harga, SUM(a.harga_satuan * a.qty) AS ttl_price_bef_disc, SUM(a.total_harga) AS ttl_after_disc, SUM(a.diskon_nilai * a.qty) AS ttl_nilai_diskon FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $id . "'")->row();


		$total_price_before_discount = ($get_ttl_detail->ttl_price_bef_disc);
		$ttl_after_disc = $get_ttl_detail->ttl_after_disc;
		$total_nilai_discount = $get_ttl_detail->ttl_nilai_diskon;


		if ($total_price_before_discount > 0 && $ttl_after_disc > 0) {
			$ttl_persen_discount = (($total_price_before_discount - $ttl_after_disc) / $total_price_before_discount * 100);
		}

		$total_other_cost = 0;
		$get_total_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $id, 'curr' => $curr])->result();
		foreach ($get_total_other_cost as $other_cost) {
			$total_other_cost += $other_cost->total_nilai;
		}



		$ttl_other_item = 0;
		$get_total_other_item = $this->db->select('SUM(a.total) AS ttl_other_item')->get_where('tr_penawaran_other_item a', ['a.id_penawaran' => $id])->row();
		if (!empty($get_total_other_item)) {
			$ttl_other_item = $get_total_other_item->ttl_other_item;
		}

		if ($get_ttl_detail->ttl_harga > 0 && $persen_ppn > 0) {
			$nilai_ppn = ((($get_ttl_detail->ttl_harga + $total_other_cost + $ttl_other_item) * $persen_ppn / 100));
		}

		echo json_encode([
			'hasil' => $hasil,
			'total' => $get_ttl_detail->ttl_harga,
			'total_other_cost' => $total_other_cost,
			'grand_total_other_item' => $ttl_other_item,
			'nilai_ppn' => $nilai_ppn,
			'grand_total' => ($get_ttl_detail->ttl_harga + $nilai_ppn + $total_other_cost + $ttl_other_item),
			'total_price_before_discount' => $total_price_before_discount,
			'total_nilai_discount' => $total_nilai_discount,
			'ttl_persen_discount' => $ttl_persen_discount
		]);
	}

	public function hitung_all()
	{
		$id = $this->input->post('id');
		$no_surat = $this->input->post('no_surat');
		$qty = $this->input->post('qty');
		$diskon_persen = $this->input->post('diskon_persen');
		$diskon_nilai = $this->input->post('diskon_nilai');
		$persen_ppn = $this->input->post('persen_ppn');
		$nilai_ppn = $this->input->post('nilai_ppn');
		$cutting_fee = $this->input->post('cutting_fee');
		$delivery_fee = $this->input->post('delivery_fee');


		$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['id_penawaran_detail' => $id])->row();

		$price_list = $get_penawaran_detail->harga_satuan;

		if ($diskon_persen != $get_penawaran_detail->diskon_persen) {
			$diskon_nilai = (($price_list) * $diskon_persen / 100);
		} else {
			if ($diskon_nilai != $get_penawaran_detail->diskon_nilai) {
				$diskon_persen = (($diskon_nilai / ($price_list)) * 100);
			}
		}

		if ($qty != $get_penawaran_detail->qty) {
			$diskon_persen = (($diskon_nilai / ($price_list)) * 100);
		}
		// if ($diskon_nilai !== $get_penawaran_detail->diskon_nilai) {
		// }


		$price_after_disc = ((($price_list + $cutting_fee + $delivery_fee) * $qty) - (($price_list * $qty) * $diskon_persen / 100));

		$this->db->trans_begin();

		$this->db->update('tr_penawaran_detail', [
			'qty' => $qty,
			'diskon_persen' => $diskon_persen,
			'diskon_nilai' => $diskon_nilai,
			'total_harga' => $price_after_disc
		], [
			'id_penawaran_detail' => $id
		]);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}


	public function del_product_price()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();

		$this->db->delete('tr_penawaran_detail', ['id_penawaran_detail' => $id]);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function ubah_persen_ppn()
	{
		$session = $this->session->userdata('app_session');

		$post = $this->input->post();
		$ppn = $post['ppn'];
		$id = $post['id'];
		if ($id == '') {
			$id = $session['id_user'];
		}

		$ppn_persen = 11;
		if ($ppn !== '11') {
			$ppn_persen = 0;
		}

		$get_ttl_detail = $this->db->query("SELECT SUM(a.total_harga) AS ttl_harga FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $id . "'")->row();

		$nilai_ppn = ($get_ttl_detail->ttl_harga * $ppn_persen / 100);

		echo json_encode([
			'hasil' => $nilai_ppn
		]);
	}

	public function ubah_nilai_ppn()
	{
		$session = $this->session->userdata('app_session');

		$post = $this->input->post();
		$nilai_ppn = $post['nilai_ppn'];
		$id = $post['id'];
		if ($id == '') {
			$id = $session['id_user'];
		}

		$get_ttl_detail = $this->db->query("SELECT SUM(a.total_harga) AS ttl_harga FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $id . "'")->row();

		$persen_ppn = ($nilai_ppn / $get_ttl_detail->ttl_harga * 100);

		echo json_encode([
			'hasil' => $persen_ppn
		]);
	}

	public function hitung_total()
	{
		$session = $this->session->userdata('app_session');

		$id = $this->input->post('id');
		if ($id == '') {
			$id = $session['id_user'];
		}


		$persen_ppn = 11;
		if ($this->input->post('ppn') !== '11') {
			$persen_ppn = 0;
		}

		$get_ttl_detail = $this->db->query("SELECT SUM(a.total_harga) AS ttl_harga FROM tr_penawaran_detail a WHERE a.no_penawaran = '" . $id . "'")->row();

		$get_ttl_other_cost = $this->db->select('SUM(a.total_nilai) as ttl_other_cost')->get_where('tr_penawaran_other_cost a', ['a.id_penawaran' => $id])->row();

		$get_ttl_other_item = $this->db->select('SUM(a.total) as ttl_other_item')->get_where('tr_penawaran_other_item a', ['a.id_penawaran' => $id])->row();

		$nilai_ppn = (($get_ttl_detail->ttl_harga + $get_ttl_other_cost->ttl_other_cost + $get_ttl_other_item->ttl_other_item) * $persen_ppn / 100);

		$grand_total = (($get_ttl_detail->ttl_harga + $get_ttl_other_cost->ttl_other_cost + $get_ttl_other_item->ttl_other_item) + $nilai_ppn);

		echo $grand_total;
	}

	public function update_status()
	{
		$id = $this->input->post('id');

		$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $id])->row();
		$updated_status = ($get_penawaran->status + 1);

		$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $id])->result();

		$harga_before_disc = 0;
		$harga_after_disc = 0;
		foreach ($get_penawaran_detail as $penawaran_detail) :
			$harga_before_disc += ($penawaran_detail->harga_satuan * $penawaran_detail->qty);
			$harga_after_disc += $penawaran_detail->total_harga;
		endforeach;


		$ttl_disc = (($harga_before_disc - $harga_after_disc) / $harga_before_disc * 100);
		// print_r($ttl_disc);
		// exit;

		$check_disc_penawaran = $this->db->query('SELECT MAX(diskon_persen) AS max_disc_persen FROM tr_penawaran_detail WHERE no_penawaran = "' . $id . '"')->row();

		$get_disc = $this->db->query('SELECT * FROM ms_diskon ORDER BY diskon_awal DESC')->result();

		$tingkatan = '';
		foreach ($get_disc as $list_disc) {
			if ($tingkatan == '') {
				if ($check_disc_penawaran->max_disc_persen >= $list_disc->diskon_awal && $check_disc_penawaran->max_disc_persen <= $list_disc->diskon_akhir) {
					$tingkatan = $list_disc->tingkatan;
				} else {
					if ($check_disc_penawaran->max_disc_persen >= $list_disc->diskon_awal) {
						$tingkatan = $list_disc->tingkatan;
					}
				}
			}
		}




		$this->db->trans_begin();


		// if ($tingkatan == 'Tingkat 1') {
		// 	$this->db->update('tr_penawaran', [
		// 		'status' => $updated_status,
		// 		'req_app1' => 1
		// 	], [
		// 		'no_penawaran' => $id
		// 	]);
		// }
		if ($tingkatan == 'Tingkat 2') {
			// $this->db->update('tr_penawaran', [
			// 	'status' => $updated_status,
			// 	'req_app1' => 1,
			// 	'req_app2' => 1
			// ], [
			// 	'no_penawaran' => $id
			// ]);
			$this->db->update('tr_penawaran', [
				'status' => $updated_status,
				'req_app1' => 1
			], [
				'no_penawaran' => $id
			]);
		}
		if ($tingkatan == 'Tingkat 3') {
			$this->db->update('tr_penawaran', [
				'status' => $updated_status,
				'req_app1' => 1,
				'req_app2' => 1
			], [
				'no_penawaran' => $id
			]);
		}
		if ($tingkatan == 'Tingkat 4') {
			$this->db->update('tr_penawaran', [
				'status' => $updated_status,
				'req_app1' => 1,
				'req_app2' => 1,
				'req_app3' => 1
			], [
				'no_penawaran' => $id
			]);
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid,
			'updated_sts' => $updated_status
		]);
	}

	public function approve_penawaran()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();

		$this->db->update('tr_penawaran', [
			'req_app1' => 1,
			'app_1' => 1,
			'status' => 2,
		], [
			'no_penawaran' => $id
		]);

		if ($this->db->trans_status() === FALSE) {
			$valid = 0;
			$msg = 'Maaf, penawaran gagal di Approve !';

			$this->db->trans_rollback();
		} else {
			$valid = 1;
			$msg = 'Selamat, penawaran berhasil di Approve !';

			$this->db->trans_commit();
		}

		echo json_encode([
			'status' => $valid,
			'pesan' => $msg
		]);
	}

	public function loss_penawaran()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();

		$this->db->update('tr_penawaran', [
			'status' => 4,
		], [
			'no_penawaran' => $id
		]);

		if ($this->db->trans_status() === FALSE) {
			$valid = 0;
			$msg = 'Maaf, penawaran gagal di Loss !';

			$this->db->trans_rollback();
		} else {
			$valid = 1;
			$msg = 'Selamat, penawaran berhasil di Loss !';

			$this->db->trans_commit();
		}

		echo json_encode([
			'status' => $valid,
			'pesan' => $msg
		]);
	}

	public function get_data_customer()
	{
		$id_customer = $this->input->post('id_customer');

		// Ambil semua PIC yang sesuai dengan id_customer
		$get_data_pic = $this->db->query('SELECT a.name_pic, a.id_pic, a.email_pic FROM child_customer_pic a WHERE a.id_customer = "' . $id_customer . '"')->result();

		// Pastikan list kosong jika tidak ada data
		$list_pic = '<option value="">-- Choose Customer PIC --</option>';
		$email_list = [];
		$email_pic = '';

		foreach ($get_data_pic as $pic) {
			$list_pic .= '<option value="' . $pic->id_pic . '">' . $pic->name_pic . '</option>';
			$email_list[$pic->id_pic] = $pic->email_pic;
		}

		// Ambil email dari PIC pertama jika ada
		if (!empty($get_data_pic)) {
			$email_pic = $get_data_pic[0]->email_pic;
		}

		echo json_encode([
			'list_pic' => $list_pic,
			'email_pic' => $email_pic,
			'email_list' => $email_list // Kirim semua email PIC berdasarkan id_pic
		]);
	}


	public function createunlocated()
	{
		if ($this->input->post('no_surat') !== null) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			// print_r($data);
			// exit;
			$session 	= $this->session->userdata('app_session');
			$Ym			= date('y');
			$id    				= $data['id'];
			$no_ipp    			= $data['no_ipp'];
			$id_customer    	= $data['id_customer'];
			$project    		= $data['project'];
			$referensi    		= $data['referensi'];
			$id_top    			= $data['id_top'];
			$keterangan    		= $data['keterangan'];
			$delivery_type    	= $data['delivery_type'];
			$id_country    		= $data['id_country'];
			$delivery_category	= $data['delivery_category'];
			$area_destinasi    	= $data['area_destinasi'];
			$delivery_address   = $data['delivery_address'];
			$shipping_method    = $data['shipping_method'];
			$packing    		= $data['packing'];
			$guarantee    		= $data['guarantee'];
			$delivery_date    	= (!empty($data['delivery_date'])) ? date('Y-m-d', strtotime($data['delivery_date'])) : NULL;
			$instalasi_option   = $data['instalasi_option'];

			$created_by   = 'updated_by';
			$created_date = 'updated_date';
			$tanda        = 'Insert ';

			if (empty($id)) {
				//pengurutan kode
				$srcMtr			= "SELECT MAX(no_ipp) as maxP FROM ipp WHERE no_ipp LIKE 'IPP" . $Ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 5, 4);
				$urutan2++;
				$urut2			= sprintf('%04s', $urutan2);
				$no_ipp	      	= "IPP" . $Ym . $urut2;

				$created_by   = 'created_by';
				$created_date = 'created_date';
				$tanda        = 'Update ';

				$rev = 0;
			} else {
				$header   	= $this->db->get_where('ipp', array('id' => $id))->result();
				$rev		= $header[0]->rev + 1;
			}

			$ArrHeader		= array(
				'no_ipp'			=> $no_ipp,
				'id_customer'		=> $id_customer,
				'project'			=> $project,
				'rev'				=> $rev,
				'request_new_product' => 1,
				$created_by	    	=> $session['id_user'],
				$created_date	  	=> date('Y-m-d H:i:s')
			);


			$ArrDetail	= array();
			$ArrDetailProduct	= array();
			$ArrDetailAcc	= array();
			$ArrDetailJadi	= array();
			$ArrDetailSheet	= array();
			$ArrDetailEnd	= array();
			if (!empty($data['Detail'])) {
				$nomor = 0;
				foreach ($data['Detail'] as $val => $valx) {
					$nomor++;
					$ArrDetail[$val]['no_ipp'] 			= $no_ipp;
					$ArrDetail[$val]['no_ipp_code'] 	= $no_ipp . '-' . $nomor;
					$ArrDetail[$val]['platform'] 		= (!empty($valx['platform'])) ? $valx['platform'] : 'N';
					$ArrDetail[$val]['cover_drainage'] 	= (!empty($valx['cover_drainage'])) ? $valx['cover_drainage'] : 'N';
					$ArrDetail[$val]['facade'] 			= (!empty($valx['facade'])) ? $valx['facade'] : 'N';
					$ArrDetail[$val]['ceilling'] 		= (!empty($valx['ceilling'])) ? $valx['ceilling'] : 'N';
					$ArrDetail[$val]['partition'] 		= (!empty($valx['partition'])) ? $valx['partition'] : 'N';
					$ArrDetail[$val]['fence'] 			= (!empty($valx['fence'])) ? $valx['fence'] : 'N';
					$ArrDetail[$val]['max_load'] 		= str_replace(',', '', $valx['max_load']);
					$ArrDetail[$val]['min_load'] 		= str_replace(',', '', $valx['min_load']);
					$ArrDetail[$val]['app_indoor'] 		= (!empty($valx['app_indoor'])) ? $valx['app_indoor'] : 'N';
					$ArrDetail[$val]['app_outdoor'] 	= (!empty($valx['app_outdoor'])) ? $valx['app_outdoor'] : 'N';
					$ArrDetail[$val]['type_product'] 		= $valx['type_product'];
					$ArrDetail[$val]['color'] 				= $valx['color'];
					$ArrDetail[$val]['food_grade'] 			= (!empty($valx['food_grade'])) ? $valx['food_grade'] : 'N';
					$ArrDetail[$val]['uv'] 					= (!empty($valx['uv'])) ? $valx['uv'] : 'N';
					$ArrDetail[$val]['fire_reterdant_1'] 	= (!empty($valx['fire_reterdant_1'])) ? $valx['fire_reterdant_1'] : 'N';
					$ArrDetail[$val]['fire_reterdant_2'] 	= (!empty($valx['fire_reterdant_2'])) ? $valx['fire_reterdant_2'] : 'N';
					$ArrDetail[$val]['fire_reterdant_3'] 	= (!empty($valx['fire_reterdant_3'])) ? $valx['fire_reterdant_3'] : 'N';
					$ArrDetail[$val]['standard_astm'] 		= (!empty($valx['standard_astm'])) ? $valx['standard_astm'] : 'N';
					$ArrDetail[$val]['standard_bs'] 		= (!empty($valx['standard_bs'])) ? $valx['standard_bs'] : 'N';
					$ArrDetail[$val]['standard_dnv'] 		= (!empty($valx['standard_dnv'])) ? $valx['standard_dnv'] : 'N';
					$ArrDetail[$val]['file_pendukung_1'] 	= $valx['file_pendukung_1'];
					$ArrDetail[$val]['file_pendukung_2'] 	= $valx['file_pendukung_2'];
					$ArrDetail[$val]['other_test'] 			= $valx['other_test'];
					$ArrDetail[$val]['surface_concave'] 	= (!empty($valx['surface_concave'])) ? $valx['surface_concave'] : 'N';
					$ArrDetail[$val]['surface_flat'] 		= (!empty($valx['surface_flat'])) ? $valx['surface_flat'] : 'N';
					$ArrDetail[$val]['id_bom_topping'] 		= $valx['id_bom_topping'];

					if (!empty($_FILES['photo_' . $val]["tmp_name"])) {
						$target_dir     = "assets/files/";
						$target_dir_u   = get_root3() . "/assets/files/";
						$name_file      = 'ipp-' . $val . "-" . $no_ipp . '-' . $nomor . '-' . date('Ymdhis');
						$target_file    = $target_dir . basename($_FILES['photo_' . $val]["name"]);
						$name_file_ori  = basename($_FILES['photo_' . $val]["name"]);
						$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
						$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;

						// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){

						$terupload = move_uploaded_file($_FILES['photo_' . $val]["tmp_name"], $nama_upload);
						$link_url    	= $target_dir . $name_file . "." . $imageFileType;

						$ArrDetail[$val]['file_dokumen'] 		= $link_url;
						// }
					}

					if (!empty($valx['product_master'])) {
						foreach ($valx['product_master'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailProduct[$UNIQ]['category'] = 'product';
							$ArrDetailProduct[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailProduct[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailProduct[$UNIQ]['code_lv4'] = $value['code_lv4'];
							$ArrDetailProduct[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['accessories'])) {
						foreach ($valx['accessories'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailAcc[$UNIQ]['category'] = 'accessories';
							$ArrDetailAcc[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailAcc[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailAcc[$UNIQ]['code_lv4'] = $value['code_lv4'];
							$ArrDetailAcc[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['ukuran_jadi'])) {
						foreach ($valx['ukuran_jadi'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailJadi[$UNIQ]['category'] = 'ukuran jadi';
							$ArrDetailJadi[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailJadi[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailJadi[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailJadi[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailJadi[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['flat_sheet'])) {
						foreach ($valx['flat_sheet'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailSheet[$UNIQ]['category'] = 'flat sheet';
							$ArrDetailSheet[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailSheet[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailSheet[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailSheet[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailSheet[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['end_plate'])) {
						foreach ($valx['end_plate'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailEnd[$UNIQ]['category'] = 'end plate';
							$ArrDetailEnd[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailEnd[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailEnd[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailEnd[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailEnd[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}
				}
			}


			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('ipp', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('ipp', $ArrHeader);
			}

			$this->db->where('no_ipp', $no_ipp);
			$this->db->delete('ipp_detail');

			$this->db->where('no_ipp', $no_ipp);
			$this->db->delete('ipp_detail_lainnya');

			if (!empty($ArrDetail)) {
				$this->db->insert_batch('ipp_detail', $ArrDetail);
			}
			if (!empty($ArrDetailProduct)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailProduct);
			}
			if (!empty($ArrDetailAcc)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailAcc);
			}
			if (!empty($ArrDetailJadi)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailJadi);
			}
			if (!empty($ArrDetailSheet)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailSheet);
			}
			if (!empty($ArrDetailEnd)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailEnd);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=> 'Save gagal disimpan ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Save berhasil disimpan. Thanks ...',
					'status'	=> 1
				);
				history($tanda . " supplier " . $no_ipp);
			}

			echo json_encode($Arr_Data);
		} else {
			$id 			= $this->uri->segment(3);
			$header   		= $this->db->get_where('ipp', array('id' => $id))->result();
			$detail = [];
			if (!empty($header)) {
				$no_ipp 		= (!empty($header[0]->no_ipp)) ? $header[0]->no_ipp : 0;
				$detail   		= $this->db->get_where('ipp_detail', array('no_ipp' => $no_ipp))->result_array();
			}
			$customer   	= $this->db->order_by('nm_customer', 'asc')->get_where('customer', array('deleted_date' => NULL))->result_array();
			$deliv_category = $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'category'))->result_array();
			$top			= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'ipp', 'category' => 'top'))->result_array();
			$shipping		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'method'))->result_array();
			$packing		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'ipp', 'category' => 'packing type'))->result_array();
			$country 		= $this->db->order_by('a.name', 'asc')->get('country_all a')->result_array();

			$list_bom_topping = $this->db
				->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
				->order_by('a.id_product', 'asc')
				->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
				->join('new_inventory_3 c', 'a.id_product=c.code_lv3', 'left')
				->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result_array();
			// print_r($detail);
			// exit;
			$data = [
				'header' => $header,
				'detail' => $detail,
				'customer' => $customer,
				'top' => $top,
				'country' => $country,
				'deliv_category' => $deliv_category,
				'shipping' => $shipping,
				'packing_list' => $packing,
				'list_bom_topping' => $list_bom_topping,
				'product_lv1' => get_list_inventory_lv1('product'),
				'id_customer' => $this->input->post('id_customer'),
				'project' => $this->input->post('project')
			];
			$this->template->title('Add IPP');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('request_new_product', $data);
		}
	}

	public function get_add()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$product_lv1 = get_list_inventory_lv1('product');
		$list_bom_topping = $this->db
			->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
			->order_by('a.id_product', 'asc')
			->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
			->join('new_inventory_3 c', 'a.id_product=c.code_lv3', 'left')
			->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result_array();

		$d_Header = "";
		$d_Header .= "<div id='header_" . $id . "'>";
		$d_Header .= "<h4 class='text-bold text-primary'>Permintaan " . $id . "&nbsp;&nbsp;<span class='text-red text-bold delPart' data-id='" . $id . "' style='cursor:pointer;' title='Delete Part'>Delete</span></h4>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<label>Aplikasi Kebutuhan</label>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][platform]' value='Y'>Platform</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][cover_drainage]' value='Y'>Cover Drainage</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][facade]' value='Y'>Facade</label></div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][ceilling]' value='Y'>Ceilling</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][partition]' value='Y'>Partition</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][fence]' value='Y'>Fence</label></div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'><label>Aplikasi Pemasangan</label>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][app_indoor]' value='Y'>Indoor</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][app_outdoor]' value='Y'>Outdoor</label></div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'><label>Max Load</label>";
		$d_Header .= "<input type='text' name='Detail[" . $id . "][max_load]' class='form-control input-md autoNumeric0' placeholder='Max Load' value=''>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'><label>Min Load</label>";
		$d_Header .= "<input type='text' name='Detail[" . $id . "][min_load]' class='form-control input-md autoNumeric0' placeholder='Min Load' value=''>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";

		$d_Header .= "<hr>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Type Product</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "	<select name='Detail[" . $id . "][type_product]' id='type_product_" . $id . "' class='form-control chosen-select'>";
		$d_Header .= "		<option value='0'>All Type Product</option>";
		foreach ($product_lv1 as $val => $valx) {
			$d_Header .= "<option value='" . $valx['code_lv1'] . "'>" . strtoupper($valx['nama']) . "</option>";
		}
		$d_Header .= 	"</select>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Product Name</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-8'>";
		$d_Header .= "	<input type='text' class='form-control' name='Detail[" . $id . "][product_name]' placeholder='- Product Name -'>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Additional Spesification</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Additional</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][food_grade]' value='Y'>Food Grade</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][uv]' value='Y'>UV</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Fire Retardant</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][fire_reterdant_1]' value='Y'>Level 1</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][fire_reterdant_2]' value='Y'>Level 2</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][fire_reterdant_3]' value='Y'>Level 3</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Standard Spec</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][standard_astm]' value='Y'>ASTM</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][standard_bs]' value='Y'>BS</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][standard_dnv]' value='Y'>GNV-GL</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "		<div class='form-group'><label>Dokumen Pendukung</label>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[" . $id . "][file_pendukung_1]' placeholder='Dokumen Pendukung 1' style='margin-bottom:5px;'>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[" . $id . "][file_pendukung_2]' placeholder='Dokumen Pendukung 2' style='margin-bottom:5px;'>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label></label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Color</label>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[" . $id . "][color]' placeholder='Color'>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "		<div class='form-group'><label>Other Testing Requirement</label>";
		$d_Header .= "		<textarea class='form-control' name='Detail[" . $id . "][other_test]' rows='2' placeholder='Other Testing Requirement'></textarea>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Surface</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_concave]' value='Y'>Concave</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_flat]' value='Y'>Flat</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_chequered_flat]' value='Y'>Chequered Plate</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $id . "][surface_anti_skid]' value='Y'>Anti Skid</label></div>";

		$d_Header .= "		<div ><label><textarea class='form-control' name='Detail[" . $id . "][surface_custom]' id=' cols='30' rows='10'></textarea></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Drawing Customer</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "	<input type='file' class='form-control' name='drawing_customer_" . $id . "'>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Topping</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "	<input type='text' class='form-control' name='Detail[" . $id . "][id_bom_topping]'>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";



		//ukuran jadi
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2 mt-15'>";
		$d_Header .= "		<label>Ukuran Jadi</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-5'>";
		$d_Header .= "	<table class='table table-striped table-bordered table-hover table-condensed'>";
		$d_Header .= "		<tr class='bg-blue'>";
		$d_Header .= "			<th class='text-center' width='30%'>Length</th>";
		$d_Header .= "			<th class='text-center' width='30%'>Width</th>";
		$d_Header .= "			<th class='text-center' width='30%'>Qty</th>";
		$d_Header .= "			<th class='text-center' width='10%'>#</th>";
		$d_Header .= "		</tr>";
		$new_number = 0;
		$d_Header .= "		<tr id='addjadi_" . $id . "_" . $new_number . "'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPartUkj' title='Add Ukuran Jadi'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";
		$d_Header .= "	</table>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		//flat sheet


		//end plate


		//penutup div delete
		$d_Header .= "<hr>";
		$d_Header .= "</div>";
		//add part
		$d_Header .= "<div id='add_" . $id . "'><button type='button' class='btn btn-sm btn-primary addPart' title='Add Permintaan'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Permintaan</button></td></div>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_ukuran()
	{
		$post 			= $this->input->post();

		$id_head 		= $this->uri->segment(3);
		$id 			= $this->uri->segment(4);
		$NameSave 		= $post['NameSave'];
		$LabelAdd 		= $post['LabelAdd'];
		$LabelClass 	= $post['LabelClass'];
		$idClass 		= $post['idClass'];

		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr id='header" . $idClass . "_" . $id_head . "_" . $id . "'>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][" . $NameSave . "][" . $id . "][length]' class='form-control input-md text-center autoNumeric4'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][" . $NameSave . "][" . $id . "][width]' class='form-control input-md text-center autoNumeric4'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id_head . "][" . $NameSave . "][" . $id . "][order]' class='form-control input-md text-center autoNumeric0'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='center'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart" . $LabelClass . "' title='Delete'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "		<tr id='add" . $idClass . "_" . $id_head . "_" . $id . "'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPart" . $LabelClass . "' title='Add " . $LabelAdd . "'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add " . $LabelAdd . "</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function add()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			// print_r($data);
			// exit;
			$session 	= $this->session->userdata('app_session');
			$Ym			= date('y');
			$id    				= $data['id'];
			$no_ipp    			= $data['no_ipp'];
			$id_customer    	= $data['id_customer'];
			$project    		= $data['project'];
			$referensi    		= $data['referensi'];
			$id_top    			= $data['id_top'];
			$keterangan    		= $data['keterangan'];
			$delivery_type    	= $data['delivery_type'];
			$id_country    		= $data['id_country'];
			$delivery_category	= $data['delivery_category'];
			$area_destinasi    	= $data['area_destinasi'];
			$delivery_address   = $data['delivery_address'];
			$shipping_method    = $data['shipping_method'];
			$packing    		= $data['packing'];
			$guarantee    		= $data['guarantee'];
			$delivery_date    	= (!empty($data['delivery_date'])) ? date('Y-m-d', strtotime($data['delivery_date'])) : NULL;
			$instalasi_option   = $data['instalasi_option'];

			$created_by   = 'updated_by';
			$created_date = 'updated_date';
			$tanda        = 'Insert ';

			if (empty($id)) {
				//pengurutan kode
				$srcMtr			= "SELECT MAX(no_ipp) as maxP FROM ipp WHERE no_ipp LIKE 'IPP" . $Ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 5, 4);
				$urutan2++;
				$urut2			= sprintf('%04s', $urutan2);
				$no_ipp	      	= "IPP" . $Ym . $urut2;

				$created_by   = 'created_by';
				$created_date = 'created_date';
				$tanda        = 'Update ';

				$rev = 0;
			} else {
				$header   	= $this->db->get_where('ipp', array('id' => $id))->result();
				$rev		= $header[0]->rev + 1;
			}

			$ArrHeader		= array(
				'no_ipp'			=> $no_ipp,
				'id_customer'		=> $id_customer,
				'project'			=> $project,
				'rev'				=> $rev,
				'request_new_product' => 1,
				$created_by	    	=> $session['id_user'],
				$created_date	  	=> date('Y-m-d H:i:s')
			);


			$ArrDetail	= array();
			$ArrDetailProduct	= array();
			$ArrDetailAcc	= array();
			$ArrDetailJadi	= array();
			$ArrDetailSheet	= array();
			$ArrDetailEnd	= array();

			$valid = 1;
			if (!empty($data['Detail'])) {
				$nomor = 0;
				foreach ($data['Detail'] as $val => $valx) {
					$nomor++;
					$ArrDetail[$val]['no_ipp'] 			= $no_ipp;
					$ArrDetail[$val]['no_ipp_code'] 	= $no_ipp . '-' . $nomor;
					$ArrDetail[$val]['platform'] 		= (!empty($valx['platform'])) ? $valx['platform'] : 'N';
					$ArrDetail[$val]['cover_drainage'] 	= (!empty($valx['cover_drainage'])) ? $valx['cover_drainage'] : 'N';
					$ArrDetail[$val]['facade'] 			= (!empty($valx['facade'])) ? $valx['facade'] : 'N';
					$ArrDetail[$val]['ceilling'] 		= (!empty($valx['ceilling'])) ? $valx['ceilling'] : 'N';
					$ArrDetail[$val]['partition'] 		= (!empty($valx['partition'])) ? $valx['partition'] : 'N';
					$ArrDetail[$val]['fence'] 			= (!empty($valx['fence'])) ? $valx['fence'] : 'N';
					$ArrDetail[$val]['max_load'] 		= str_replace(',', '', $valx['max_load']);
					$ArrDetail[$val]['min_load'] 		= str_replace(',', '', $valx['min_load']);
					$ArrDetail[$val]['app_indoor'] 		= (!empty($valx['app_indoor'])) ? $valx['app_indoor'] : 'N';
					$ArrDetail[$val]['app_outdoor'] 	= (!empty($valx['app_outdoor'])) ? $valx['app_outdoor'] : 'N';
					$ArrDetail[$val]['type_product'] 		= $valx['type_product'];
					$ArrDetail[$val]['color'] 				= $valx['color'];
					$ArrDetail[$val]['food_grade'] 			= (!empty($valx['food_grade'])) ? $valx['food_grade'] : 'N';
					$ArrDetail[$val]['uv'] 					= (!empty($valx['uv'])) ? $valx['uv'] : 'N';
					$ArrDetail[$val]['fire_reterdant_1'] 	= (!empty($valx['fire_reterdant_1'])) ? $valx['fire_reterdant_1'] : 'N';
					$ArrDetail[$val]['fire_reterdant_2'] 	= (!empty($valx['fire_reterdant_2'])) ? $valx['fire_reterdant_2'] : 'N';
					$ArrDetail[$val]['fire_reterdant_3'] 	= (!empty($valx['fire_reterdant_3'])) ? $valx['fire_reterdant_3'] : 'N';
					$ArrDetail[$val]['standard_astm'] 		= (!empty($valx['standard_astm'])) ? $valx['standard_astm'] : 'N';
					$ArrDetail[$val]['standard_bs'] 		= (!empty($valx['standard_bs'])) ? $valx['standard_bs'] : 'N';
					$ArrDetail[$val]['standard_dnv'] 		= (!empty($valx['standard_dnv'])) ? $valx['standard_dnv'] : 'N';
					$ArrDetail[$val]['file_pendukung_1'] 	= $valx['file_pendukung_1'];
					$ArrDetail[$val]['file_pendukung_2'] 	= $valx['file_pendukung_2'];
					$ArrDetail[$val]['other_test'] 			= $valx['other_test'];
					$ArrDetail[$val]['surface_concave'] 	= (!empty($valx['surface_concave'])) ? $valx['surface_concave'] : 'N';
					$ArrDetail[$val]['surface_flat'] 		= (!empty($valx['surface_flat'])) ? $valx['surface_flat'] : 'N';
					$ArrDetail[$val]['surface_chequered_flat'] 		= (!empty($valx['surface_chequered_flat'])) ? $valx['surface_chequered_flat'] : 'N';
					$ArrDetail[$val]['surface_anti_skid'] 		= (!empty($valx['surface_anti_skid'])) ? $valx['surface_anti_skid'] : 'N';
					$ArrDetail[$val]['surface_custom'] 		= (!empty($valx['surface_custom'])) ? $valx['surface_custom'] : 'N';
					$ArrDetail[$val]['nm_bom_topping'] 		= $valx['id_bom_topping'];
					$ArrDetail[$val]['product_name'] 		= $valx['product_name'];

					if (!empty($_FILES['photo_' . $val]["tmp_name"])) {
						$target_dir     = "assets/files/";
						$target_dir_u   = get_root3() . "/assets/files/";
						$name_file      = 'ipp-' . $val . "-" . $no_ipp . '-' . $nomor . '-' . date('Ymdhis');
						$target_file    = $target_dir . basename($_FILES['photo_' . $val]["name"]);
						$name_file_ori  = basename($_FILES['photo_' . $val]["name"]);
						$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
						$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;

						// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){

						$terupload = move_uploaded_file($_FILES['photo_' . $val]["tmp_name"], $nama_upload);
						$link_url    	= $target_dir . $name_file . "." . $imageFileType;

						$ArrDetail[$val]['file_dokumen'] 		= $link_url;
						// }
					}

					if (!empty($_FILES['drawing_customer_' . $val]["tmp_name"])) {
						$target_dir     = "assets/files/";
						$target_dir_u   = get_root3() . "/assets/files/";
						$name_file      = 'ipp-' . $val . "-" . $no_ipp . '-' . $nomor . '-' . date('Ymdhis');
						$target_file    = $target_dir . basename($_FILES['drawing_customer_' . $val]["name"]);
						$name_file_ori  = basename($_FILES['drawing_customer_' . $val]["name"]);
						$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
						$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;

						// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){

						$terupload = move_uploaded_file($_FILES['drawing_customer_' . $val]["tmp_name"], $nama_upload);
						$link_url    	= $target_dir . $name_file . "." . $imageFileType;

						$ArrDetail[$val]['drawing_customer'] 		= $link_url;
						// }
					}

					if (!empty($valx['product_master'])) {
						foreach ($valx['product_master'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailProduct[$UNIQ]['category'] = 'product';
							$ArrDetailProduct[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailProduct[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailProduct[$UNIQ]['code_lv4'] = $value['code_lv4'];
							$ArrDetailProduct[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['ukuran_jadi'])) {
						foreach ($valx['ukuran_jadi'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailJadi[$UNIQ]['category'] = 'ukuran jadi';
							$ArrDetailJadi[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailJadi[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailJadi[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailJadi[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailJadi[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}
				}
			} else {
				$valid = 0;
			}


			$this->db->trans_start();
			if ($valid > 0) {
				if (empty($id)) {
					$this->db->insert('ipp', $ArrHeader);
				}
				if (!empty($id)) {
					$this->db->where('id', $id);
					$this->db->update('ipp', $ArrHeader);
				}

				$this->db->where('no_ipp', $no_ipp);
				$this->db->delete('ipp_detail');

				$this->db->where('no_ipp', $no_ipp);
				$this->db->delete('ipp_detail_lainnya');

				if (!empty($ArrDetail)) {
					$this->db->insert_batch('ipp_detail', $ArrDetail);
				}
				if (!empty($ArrDetailProduct)) {
					$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailProduct);
				}
				if (!empty($ArrDetailAcc)) {
					$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailAcc);
				}
				if (!empty($ArrDetailJadi)) {
					$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailJadi);
				}
				if (!empty($ArrDetailSheet)) {
					$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailSheet);
				}
				if (!empty($ArrDetailEnd)) {
					$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailEnd);
				}
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE || $valid < 1) {
				$this->db->trans_rollback();
				$msg = 'Save gagal disimpan ...';
				if ($valid < 1) {
					$msg = 'Maaf, tambah dulu pemintaan nya sebelum melakukan save !';
				}
				$Arr_Data	= array(
					'pesan'		=> $msg,
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Save berhasil disimpan. Thanks ...',
					'status'	=> 1
				);
				history($tanda . " supplier " . $no_ipp);
			}

			echo json_encode($Arr_Data);
		} else {
			$id 			= $this->uri->segment(3);
			$header   		= $this->db->get_where('ipp', array('id' => $id))->result();
			$detail = [];
			if (!empty($header)) {
				$no_ipp 		= (!empty($header[0]->no_ipp)) ? $header[0]->no_ipp : 0;
				$detail   		= $this->db->get_where('ipp_detail', array('no_ipp' => $no_ipp))->result_array();
			}
			$customer   	= $this->db->order_by('nm_customer', 'asc')->get_where('customer', array('deleted_date' => NULL))->result_array();
			$deliv_category = $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'category'))->result_array();
			$top			= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'ipp', 'category' => 'top'))->result_array();
			$shipping		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'method'))->result_array();
			$packing		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'ipp', 'category' => 'packing type'))->result_array();
			$country 		= $this->db->order_by('a.name', 'asc')->get('country_all a')->result_array();

			$list_bom_topping = $this->db
				->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
				->order_by('a.id_product', 'asc')
				->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
				->join('new_inventory_3 c', 'a.id_product=c.code_lv3', 'left')
				->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result_array();
			// print_r($detail);
			// exit;
			$data = [
				'header' => $header,
				'detail' => $detail,
				'customer' => $customer,
				'top' => $top,
				'country' => $country,
				'deliv_category' => $deliv_category,
				'shipping' => $shipping,
				'packing_list' => $packing,
				'list_bom_topping' => $list_bom_topping,
				'product_lv1' => get_list_inventory_lv1('product'),
			];
			$this->template->title('Add IPP');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add', $data);
		}
	}

	public function request_new_product()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		//   $this->template->page_icon('fa fa-users');
		$this->template->title('List Request New Product');
		$this->template->render('list_request_new_product');
	}

	public function get_json_ipp()
	{
		$this->quotation_model->get_json_ipp();
	}

	public function view_request_new_product()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			// print_r($data);
			// exit;
			$session 	= $this->session->userdata('app_session');
			$Ym			= date('y');
			$id    				= $data['id'];
			$no_ipp    			= $data['no_ipp'];
			$id_customer    	= $data['id_customer'];
			$project    		= $data['project'];
			$referensi    		= $data['referensi'];
			$id_top    			= $data['id_top'];
			$keterangan    		= $data['keterangan'];
			$delivery_type    	= $data['delivery_type'];
			$id_country    		= $data['id_country'];
			$delivery_category	= $data['delivery_category'];
			$area_destinasi    	= $data['area_destinasi'];
			$delivery_address   = $data['delivery_address'];
			$shipping_method    = $data['shipping_method'];
			$packing    		= $data['packing'];
			$guarantee    		= $data['guarantee'];
			$delivery_date    	= (!empty($data['delivery_date'])) ? date('Y-m-d', strtotime($data['delivery_date'])) : NULL;
			$instalasi_option   = $data['instalasi_option'];

			$created_by   = 'updated_by';
			$created_date = 'updated_date';
			$tanda        = 'Insert ';

			if (empty($id)) {
				//pengurutan kode
				$srcMtr			= "SELECT MAX(no_ipp) as maxP FROM ipp WHERE no_ipp LIKE 'IPP" . $Ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 5, 4);
				$urutan2++;
				$urut2			= sprintf('%04s', $urutan2);
				$no_ipp	      	= "IPP" . $Ym . $urut2;

				$created_by   = 'created_by';
				$created_date = 'created_date';
				$tanda        = 'Update ';

				$rev = 0;
			} else {
				$header   	= $this->db->get_where('ipp', array('id' => $id))->result();
				$rev		= $header[0]->rev + 1;
			}

			$ArrHeader		= array(
				'no_ipp'			=> $no_ipp,
				'id_customer'		=> $id_customer,
				'project'			=> $project,
				'referensi'			=> $referensi,
				'id_top'			=> $id_top,
				'keterangan'		=> $keterangan,
				'delivery_type'		=> $delivery_type,
				'id_country'		=> $id_country,
				'delivery_category'	=> $delivery_category,
				'area_destinasi'	=> $area_destinasi,
				'delivery_address'	=> $delivery_address,
				'shipping_method'	=> $shipping_method,
				'packing'			=> $packing,
				'guarantee'			=> $guarantee,
				'delivery_date'		=> $delivery_date,
				'instalasi_option'	=> $instalasi_option,
				'rev'				=> $rev,
				$created_by	    	=> $session['id_user'],
				$created_date	  	=> date('Y-m-d H:i:s')
			);


			$ArrDetail	= array();
			$ArrDetailProduct	= array();
			$ArrDetailAcc	= array();
			$ArrDetailJadi	= array();
			$ArrDetailSheet	= array();
			$ArrDetailEnd	= array();
			if (!empty($data['Detail'])) {
				$nomor = 0;
				foreach ($data['Detail'] as $val => $valx) {
					$nomor++;
					$ArrDetail[$val]['no_ipp'] 			= $no_ipp;
					$ArrDetail[$val]['no_ipp_code'] 	= $no_ipp . '-' . $nomor;
					$ArrDetail[$val]['platform'] 		= (!empty($valx['platform'])) ? $valx['platform'] : 'N';
					$ArrDetail[$val]['cover_drainage'] 	= (!empty($valx['cover_drainage'])) ? $valx['cover_drainage'] : 'N';
					$ArrDetail[$val]['facade'] 			= (!empty($valx['facade'])) ? $valx['facade'] : 'N';
					$ArrDetail[$val]['ceilling'] 		= (!empty($valx['ceilling'])) ? $valx['ceilling'] : 'N';
					$ArrDetail[$val]['partition'] 		= (!empty($valx['partition'])) ? $valx['partition'] : 'N';
					$ArrDetail[$val]['fence'] 			= (!empty($valx['fence'])) ? $valx['fence'] : 'N';
					$ArrDetail[$val]['max_load'] 		= str_replace(',', '', $valx['max_load']);
					$ArrDetail[$val]['min_load'] 		= str_replace(',', '', $valx['min_load']);
					$ArrDetail[$val]['app_indoor'] 		= (!empty($valx['app_indoor'])) ? $valx['app_indoor'] : 'N';
					$ArrDetail[$val]['app_outdoor'] 	= (!empty($valx['app_outdoor'])) ? $valx['app_outdoor'] : 'N';
					$ArrDetail[$val]['type_product'] 		= $valx['type_product'];
					$ArrDetail[$val]['color'] 				= $valx['color'];
					$ArrDetail[$val]['food_grade'] 			= (!empty($valx['food_grade'])) ? $valx['food_grade'] : 'N';
					$ArrDetail[$val]['uv'] 					= (!empty($valx['uv'])) ? $valx['uv'] : 'N';
					$ArrDetail[$val]['fire_reterdant_1'] 	= (!empty($valx['fire_reterdant_1'])) ? $valx['fire_reterdant_1'] : 'N';
					$ArrDetail[$val]['fire_reterdant_2'] 	= (!empty($valx['fire_reterdant_2'])) ? $valx['fire_reterdant_2'] : 'N';
					$ArrDetail[$val]['fire_reterdant_3'] 	= (!empty($valx['fire_reterdant_3'])) ? $valx['fire_reterdant_3'] : 'N';
					$ArrDetail[$val]['standard_astm'] 		= (!empty($valx['standard_astm'])) ? $valx['standard_astm'] : 'N';
					$ArrDetail[$val]['standard_bs'] 		= (!empty($valx['standard_bs'])) ? $valx['standard_bs'] : 'N';
					$ArrDetail[$val]['standard_dnv'] 		= (!empty($valx['standard_dnv'])) ? $valx['standard_dnv'] : 'N';
					$ArrDetail[$val]['file_pendukung_1'] 	= $valx['file_pendukung_1'];
					$ArrDetail[$val]['file_pendukung_2'] 	= $valx['file_pendukung_2'];
					$ArrDetail[$val]['other_test'] 			= $valx['other_test'];
					$ArrDetail[$val]['surface_concave'] 	= (!empty($valx['surface_concave'])) ? $valx['surface_concave'] : 'N';
					$ArrDetail[$val]['surface_flat'] 		= (!empty($valx['surface_flat'])) ? $valx['surface_flat'] : 'N';
					$ArrDetail[$val]['id_bom_topping'] 		= $valx['id_bom_topping'];

					if (!empty($_FILES['photo_' . $val]["tmp_name"])) {
						$target_dir     = "assets/files/";
						$target_dir_u   = get_root3() . "/assets/files/";
						$name_file      = 'ipp-' . $val . "-" . $no_ipp . '-' . $nomor . '-' . date('Ymdhis');
						$target_file    = $target_dir . basename($_FILES['photo_' . $val]["name"]);
						$name_file_ori  = basename($_FILES['photo_' . $val]["name"]);
						$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
						$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;

						// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){

						$terupload = move_uploaded_file($_FILES['photo_' . $val]["tmp_name"], $nama_upload);
						$link_url    	= $target_dir . $name_file . "." . $imageFileType;

						$ArrDetail[$val]['file_dokumen'] 		= $link_url;
						// }
					}

					if (!empty($valx['product_master'])) {
						foreach ($valx['product_master'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailProduct[$UNIQ]['category'] = 'product';
							$ArrDetailProduct[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailProduct[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailProduct[$UNIQ]['code_lv4'] = $value['code_lv4'];
							$ArrDetailProduct[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['accessories'])) {
						foreach ($valx['accessories'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailAcc[$UNIQ]['category'] = 'accessories';
							$ArrDetailAcc[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailAcc[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailAcc[$UNIQ]['code_lv4'] = $value['code_lv4'];
							$ArrDetailAcc[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['ukuran_jadi'])) {
						foreach ($valx['ukuran_jadi'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailJadi[$UNIQ]['category'] = 'ukuran jadi';
							$ArrDetailJadi[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailJadi[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailJadi[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailJadi[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailJadi[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['flat_sheet'])) {
						foreach ($valx['flat_sheet'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailSheet[$UNIQ]['category'] = 'flat sheet';
							$ArrDetailSheet[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailSheet[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailSheet[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailSheet[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailSheet[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}

					if (!empty($valx['end_plate'])) {
						foreach ($valx['end_plate'] as $key => $value) {
							$UNIQ = $val . '-' . $key;
							$ArrDetailEnd[$UNIQ]['category'] = 'end plate';
							$ArrDetailEnd[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailEnd[$UNIQ]['no_ipp_code'] = $no_ipp . '-' . $nomor;
							$ArrDetailEnd[$UNIQ]['length'] 	= str_replace(',', '', $value['length']);
							$ArrDetailEnd[$UNIQ]['width'] 	= str_replace(',', '', $value['width']);
							$ArrDetailEnd[$UNIQ]['order'] 	= str_replace(',', '', $value['order']);
						}
					}
				}
			}


			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('ipp', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('ipp', $ArrHeader);
			}

			$this->db->where('no_ipp', $no_ipp);
			$this->db->delete('ipp_detail');

			$this->db->where('no_ipp', $no_ipp);
			$this->db->delete('ipp_detail_lainnya');

			if (!empty($ArrDetail)) {
				$this->db->insert_batch('ipp_detail', $ArrDetail);
			}
			if (!empty($ArrDetailProduct)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailProduct);
			}
			if (!empty($ArrDetailAcc)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailAcc);
			}
			if (!empty($ArrDetailJadi)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailJadi);
			}
			if (!empty($ArrDetailSheet)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailSheet);
			}
			if (!empty($ArrDetailEnd)) {
				$this->db->insert_batch('ipp_detail_lainnya', $ArrDetailEnd);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=> 'Save gagal disimpan ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Save berhasil disimpan. Thanks ...',
					'status'	=> 1
				);
				history($tanda . " supplier " . $no_ipp);
			}

			echo json_encode($Arr_Data);
		} else {
			$id 			= $this->uri->segment(3);
			$header   		= $this->db->get_where('ipp', array('id' => $id))->result();
			$detail = [];
			if (!empty($header)) {
				$no_ipp 		= (!empty($header[0]->no_ipp)) ? $header[0]->no_ipp : 0;
				$detail   		= $this->db->get_where('ipp_detail', array('no_ipp' => $no_ipp))->result_array();
			}
			$customer   	= $this->db->order_by('nm_customer', 'asc')->get_where('customer', array('deleted_date' => NULL))->result_array();
			$deliv_category = $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'category'))->result_array();
			$top			= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'ipp', 'category' => 'top'))->result_array();
			$shipping		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'delivery rate', 'category' => 'method'))->result_array();
			$packing		= $this->db->order_by('urut', 'asc')->get_where('list', array('menu' => 'ipp', 'category' => 'packing type'))->result_array();
			$country 		= $this->db->order_by('a.name', 'asc')->get('country_all a')->result_array();

			$list_bom_topping = $this->db
				->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
				->order_by('a.id_product', 'asc')
				->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
				->join('new_inventory_3 c', 'a.id_product=c.code_lv3', 'left')
				->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result_array();
			// print_r($detail);
			// exit;
			$data = [
				'header' => $header,
				'detail' => $detail,
				'customer' => $customer,
				'top' => $top,
				'country' => $country,
				'deliv_category' => $deliv_category,
				'shipping' => $shipping,
				'packing_list' => $packing,
				'list_bom_topping' => $list_bom_topping,
				'product_lv1' => get_list_inventory_lv1('product'),
			];
			$this->template->title('View IPP');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('view_request_new_product', $data);
		}
	}

	public function save_other_cost()
	{
		$post = $this->input->post();

		$no_surat = $post['no_surat'];
		if ($no_surat == '') {
			$no_surat = $this->auth->user_id();
		}
		$inc_exc_pph = $post['inc_exc_pph'];
		$curr = $post['curr'];
		$keterangan = $post['keterangan'];
		$nilai = $post['nilai'];
		$nilai_pph = $post['nilai_pph'];
		$total_nilai = $post['total_nilai'];

		$this->db->trans_begin();

		$this->db->insert('tr_penawaran_other_cost', [
			'id' => $this->quotation_model->generate_no_other_cost(),
			'id_penawaran' => $no_surat,
			'curr' => $curr,
			'keterangan' => $keterangan,
			'inc_exc_pph' => $inc_exc_pph,
			'nilai' => $nilai,
			'nilai_pph' => $nilai_pph,
			'total_nilai' => $total_nilai,
			'dibuat_oleh' => $this->auth->user_id(),
			'dibuat_tgl' => date('Y-m-d H:i:s')
		]);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();

			$valid = 0;
			$msg = 'Maaf, input other cost gagal !';
		} else {
			$this->db->trans_commit();

			$valid = 1;
			$msg = 'Selamat, input other cost berhasil !';
		}

		echo json_encode([
			'status' => $valid,
			'pesan' => $msg
		]);
	}

	public function refresh_other_cost()
	{
		$post = $this->input->post();

		$no_surat = $post['no_surat'];
		if ($no_surat == '') {
			$no_surat = $this->auth->user_id();
		}
		if (isset($post['curr']) && $post['curr'] !== '') {
			$curr = $post['curr'];
		} else {
			$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $no_surat])->row();
			$curr = $get_penawaran->currency;
		}

		$get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_surat, 'curr' => $curr])->result();

		$hasil = '';

		$no = 1;
		foreach ($get_other_cost as $other_cost) :
			$inc_exc_pph = ($other_cost->inc_exc_pph == '1') ? 'Include' : 'Exclude';
			$hasil = $hasil . '
				<tr>
					<td class="text-left">' . $other_cost->keterangan . '</td>
					<td class="text-right">
						<input type="hidden" class="nilai_other_cost" value="' . $other_cost->nilai . '">
						<span>(' . $curr . ') ' . number_format($other_cost->nilai, 2) . '</span>
					</td>
					<td class="text-center">
						' . $inc_exc_pph . '
					</td>
					<td class="text-right">
						<input type="hidden" class="nilai_pph23_other_cost" value="' . $other_cost->nilai_pph . '">
						<span>(' . $other_cost->curr . ') ' . number_format($other_cost->nilai_pph, 2) . '</span>
					</td>
					<td class="text-right">
						<input type="hidden" class="total_nilai_other_cost" value="' . $other_cost->total_nilai . '">
						<span>(' . $other_cost->curr . ') ' . number_format($other_cost->total_nilai, 2) . '</span>
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-danger del_other_cost" data-id="' . $other_cost->id . '">
							<i class="fa fa-trash"></i>
						</button>
					</td>
				</tr>
			';

			$no++;
		endforeach;

		echo $hasil;
	}

	public function del_other_cost()
	{
		$post = $this->input->post();

		$no_surat = $post['no_surat'];
		if ($no_surat == '') {
			$no_surat = $this->auth->user_id();
		}
		$curr = $post['curr'];
		$id = $post['id'];

		$this->db->trans_begin();

		$this->db->delete('tr_penawaran_other_cost', ['id' => $id]);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();

			$valid = 0;
			$msg = 'Maaf, hapus Other Cost gagal ! !';
		} else {
			$this->db->trans_commit();

			$valid = 1;
			$msg = 'Selamat, hapus Other Cost berhasil !';
		}

		echo json_encode([
			'status' => $valid,
			'pesan' => $msg
		]);
	}

	public function input_ukuran_potong()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$this->db->update('tr_penawaran_detail', ['ukuran_potongan' => $post['ukuran_potong']], ['id_penawaran_detail' => $post['id']]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function input_cutting_fee()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$this->db->update('tr_penawaran_detail', [
			'cutting_fee' => $post['nilai']
		], [
			'id_penawaran_detail' => $post['id']
		]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function input_delivery_fee()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$this->db->update('tr_penawaran_detail', [
			'delivery_fee' => $post['nilai']
		], [
			'id_penawaran_detail' => $post['id']
		]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function update_harga_barang_quotation()
	{
		$id = $this->input->post('id');
		$harga_now = $this->input->post('harga_now');

		$get_data_detail = $this->db->get_where('tr_penawaran_detail', ['id_penawaran_detail' => $id])->row();
		$diskon_nilai = ($harga_now * $get_data_detail->diskon_persen / 100);
		$total_harga = (($harga_now - $diskon_nilai) * $get_data_detail->qty);

		$this->db->trans_start();

		$this->db->update('tr_penawaran_detail', [
			'harga_satuan' => $harga_now,
			'diskon_nilai' => $diskon_nilai,
			'total_harga' => $total_harga
		], [
			'id_penawaran_detail' => $id
		]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function get_price_other_item()
	{
		$id_product = $this->input->post('id_product');

		$get_other_price_ref = $this->db->query("
			SELECT
				a.price_ref_use,
				a.price_ref_use_usd
			FROM
				new_inventory_4 a
			WHERE
				a.code_lv4 = '" . $id_product . "'

			UNION ALL

			SELECT
				a.price_ref_use,
				a.price_ref_use_usd
			FROM
				accessories a
			WHERE
				a.id = '" . $id_product . "'
		")->row();

		echo json_encode([
			'price_ref_use' => round($get_other_price_ref->price_ref_use, 2),
			'price_ref_use_usd' => round($get_other_price_ref->price_ref_use_usd, 2)
		]);
	}

	public function add_other_item()
	{
		$post = $this->input->post();

		$nm_other = '';
		$get_nm_other = $this->db->query("
			SELECT
				a.nama as nm_other
			FROM
				new_inventory_4 a
			WHERE
				a.code_lv4 = '" . $post['id_product'] . "'

			UNION ALL

			SELECT
				a.stock_name as nm_other
			FROM
				accessories a
			WHERE
				a.id = '" . $post['id_product'] . "'
		")->row();
		if (!empty($get_nm_other)) {
			$nm_other = $get_nm_other->nm_other;
		}

		$this->db->trans_start();

		$data_insert = [
			'id_penawaran' => $post['no_surat'],
			'id_other' => $post['id_product'],
			'nm_other' => $nm_other,
			'harga' => $post['price'],
			'qty' => $post['qty'],
			'total' => ($post['price'] * $post['qty']),
			'created_by' => $this->auth->user_id(),
			'created_on' => date('Y-m-d H:i:s')
		];

		$insert_other_item = $this->db->insert('tr_penawaran_other_item', $data_insert);
		if (!$insert_other_item) {
			print_r($this->db->error($insert_other_item));
			exit;
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

	public function refresh_list_other_item()
	{
		$no_surat = $this->input->post('no_surat');

		$hasil = '';

		$get_other_item = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_surat])->result();

		$grand_total = 0;
		foreach ($get_other_item as $other_item) {

			$get_list_other_item = $this->db->query("
				SELECT
					a.code_lv4 as id_product,
					a.nama as nm_product,
					a.code as product_code
				FROM
					new_inventory_4 a
				WHERE
					a.category = 'material' AND
					a.deleted_by IS NULL
				
				UNION ALL

				SELECT
					a.id as id_product,
					a.stock_name as nm_product,
					a.id_stock as product_code
				FROM
					accessories a 
				WHERE
					a.deleted_by IS NULL
			")->result();

			$hasil .= '<tr>';

			$hasil .= '<td>';
			$hasil .= $other_item->nm_other;
			$hasil .= '</td>';

			$hasil .= '<td class="text-right">';
			$hasil .= number_format($other_item->harga, 2);
			$hasil .= '</td>';

			$hasil .= '<td class="text-right">';
			$hasil .= number_format($other_item->qty, 2);
			$hasil .= '</td>';

			$hasil .= '<td class="text-right">';
			$hasil .= number_format($other_item->total, 2);
			$hasil .= '</td>';

			$hasil .= '<td class="text-center">';
			$hasil .= '<button type="button" class="btn btn-sm btn-danger del_other_item" data-id="' . $other_item->id . '"><i class="fa fa-trash"></i></button>';
			$hasil .= '</td>';

			$hasil .= '</tr>';

			$grand_total += $other_item->total;
		}

		echo json_encode([
			'hasil' => $hasil,
			'grand_total' => $grand_total
		]);
	}

	public function del_other_item()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();

		$del_other_item = $this->db->delete('tr_penawaran_other_item', ['id' => $id]);
		if (!$del_other_item) {
			print_r($del_other_item);
			exit;
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
}
