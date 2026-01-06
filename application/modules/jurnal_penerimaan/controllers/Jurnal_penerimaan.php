<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Jurnal_penerimaan extends Admin_Controller
{
	protected $viewPermission     = 'Jurnal_Penerimaan.View';
	protected $addPermission      = 'Jurnal_Penerimaan.Add';
	protected $managePermission = 'Jurnal_Penerimaan.Manage';
	protected $deletePermission = 'Jurnal_Penerimaan.Delete';

	protected $consultant;
	protected $accounting_vuca;
	protected $accounting_sustain;
	protected $accounting_stm;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'Jurnal_penerimaan/Jurnal_penerimaan_model',
			'Jurnal_penerimaan/Jurnal_penerimaan_nomor_model'
		));
		$this->template->title('Jurnal');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');

		$this->consultant = $this->load->database('consultant', true);
		$this->accounting_vuca = $this->load->database('accounting_vuca', true);
		$this->accounting_sustain = $this->load->database('accounting_sustain', true);
		$this->accounting_stm = $this->load->database('accounting_stm', true);
	}

	public function index()
	{
		$this->template->title('Jurnal Penerimaan');
		$this->template->render('index');
	}

	public function add_jurnal()
	{
		$id = $this->input->post('id');

		$get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $id])->row();

		$this->db->select('a.*');
		$this->db->from('tr_jurnal a');
		$this->db->where('a.no_transaksi', $get_jurnal->no_transaksi);
		$this->db->where('a.jenis_transaksi', $get_jurnal->jenis_transaksi);
		$this->db->where('a.sts <>', '1');
		$get_all_jurnal = $this->db->get()->result();

		$hasil = '<input type="hidden" name="id" value="' . $id . '">';

		$hasil .= '<table class="table table-striped">';
		$hasil .= '<thead>';
		$hasil .= '<tr>';
		$hasil .= '<td class="text-center">Tanggal</td>';
		$hasil .= '<td class="text-center">Tipe</td>';
		$hasil .= '<td class="text-center">No. COA</td>';
		$hasil .= '<td class="text-center">Keterangan</td>';
		$hasil .= '<td class="text-center">No. Reff</td>';
		$hasil .= '<td class="text-center">Debit</td>';
		$hasil .= '<td class="text-center">Kredit</td>';
		$hasil .= '</tr>';
		$hasil .= '</thead>';
		$hasil .= '<tbody>';

		$no = 0;
		$ttl_debit = 0;
		$ttl_kredit = 0;
		foreach ($get_all_jurnal as $item) {
			$no++;

			$hasil .= '<tr>';

			$hasil .= '<td class="text-center">';
			$hasil .= date('d F Y', strtotime($item->tgl_jurnal));
			$hasil .= '<input type="hidden" name="jurnal[' . $no . '][id]" value="' . $item->id . '">';
			$hasil .= '</td>';
			$hasil .= '<td class="text-center">' . $item->jenis_transaksi . '</td>';
			$hasil .= '<td class="text-center">' . $item->coa . '</td>';
			$hasil .= '<td class="text-center">';
			$hasil .= '<textarea class="form-control form-control-sm" name="jurnal[' . $no . '][keterangan]">' . $item->keterangan . '</textarea>';
			$hasil .= '</td>';
			$hasil .= '<td class="text-center">';
			$hasil .= $item->no_transaksi;
			$hasil .= '<input type="hidden" name="jurnal[' . $no . '][no_transaksi]">';
			$hasil .= '</td>';
			$hasil .= '<td class="text-center">';
			$hasil .= '<input type="input" class="form-control form-control-sm text-right" name="jurnal[' . $no . '][debit]" value="' . number_format($item->debit) . '" readonly>';
			$hasil .= '</td>';
			$hasil .= '<td class="text-center">';
			$hasil .= '<input type="input" class="form-control form-control-sm text-right" name="jurnal[' . $no . '][kredit]" value="' . number_format($item->kredit) . '" readonly>';
			$hasil .= '</td>';

			$hasil .= '</tr>';

			$ttl_debit += $item->debit;
			$ttl_kredit += $item->kredit;
		}

		$hasil .= '</tbody>';
		$hasil .= '<tfoot>';
		$hasil .= '<tr>';
		$hasil .= '<th class="text-right" colspan="6	">Total</th>';
		$hasil .= '<td class="text-right">';
		$hasil .= '<input type="text" class="form-control form-control-sm text-right" name="ttl_debit" value="' . number_format($ttl_debit) . '" readonly>';
		$hasil .= '</td>';
		$hasil .= '<td class="text-right">';
		$hasil .= '<input type="text" class="form-control form-control-sm text-right" name="ttl_kredit" value="' . number_format($ttl_kredit) . '" readonly>';
		$hasil .= '</td>';
		$hasil .= '</tr>';
		$hasil .= '</tfoot>';
		$hasil .= '</table>';

		echo $hasil;
	}

	public function modal_posting_jurnal()
	{
		$id = $this->input->post('id');

		$get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $id])->row();
		$get_jurnal_detail = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_jurnal->no_transaksi, 'jenis_transaksi' => $get_jurnal->jenis_transaksi])->result();

		$data = [
			'jurnal_header' => $get_jurnal_detail,
			'id' => $id
		];

		$this->load->view('posting_jurnal', $data);
	}

	public function save_posting_jurnal()
	{
		$post        = $this->input->post();
		$session = $this->session->userdata('app_session');
		$data_session    = $this->session->userdata;

		$get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $post['id']])->row();
		$get_jurnal_detail = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_jurnal->no_transaksi, 'jenis_transaksi' => $get_jurnal->jenis_transaksi])->result();

		$this->db->trans_begin();

		try {
			$get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $get_jurnal->no_transaksi])->row();
			$id_company = (!empty($get_jurnal->id_company)) ? $get_jurnal->id_company : '';

			$Nomor_BUM = $this->Jurnal_penerimaan_nomor_model->get_Nomor_Jurnal_BUM('101', $get_invoicing->tanggal_invoice, $id_company);

			$nilai = (!empty($get_jurnal->debit) && $get_jurnal->debit > 0) ? $get_jurnal->debit : $get_jurnal->kredit;

			$arr_jarh = [
				'nomor' => $Nomor_BUM,
				'tgl' => $get_jurnal->tgl_jurnal,
				'jml' => $nilai,
				'kdcab' => '101',
				'jenis_reff' => 'BUM',
				'no_reff' => $get_invoicing->id,
				'customer' => $get_invoicing->nm_customer,
				'terima_dari' => $this->auth->user_name(),
				'jenis_ar' => 'BUM',
				'note' => $get_jurnal->keterangan,
				'user_id' => $this->auth->user_id(),
				'tgl_invoice' => $get_invoicing->tanggal_invoice
			];
			if ($get_jurnal->id_company == '1' || $get_jurnal->id_company == '6') {
				$insert_jarh = $this->accounting_stm->insert('jarh', $arr_jarh);
			} else if ($get_jurnal->id_company == '4' || $get_jurnal->id_company == '5') {
				$insert_jarh = $this->accounting_vuca->insert('jarh', $arr_jarh);
			} else {
				$insert_jarh = $this->accounting_sustain->insert('jarh', $arr_jarh);
			}


			if ($get_jurnal->jenis_transaksi == 'Penerimaan Piutang') {

				$arr_jurnal = [];

				foreach ($get_jurnal_detail as $item) {
					$arr_jurnal[] = [
						'tipe' => 'BUM',
						'nomor' => $Nomor_BUM,
						'tanggal' => $item->tgl_jurnal,
						'no_perkiraan' => $item->coa,
						'keterangan' => $item->keterangan,
						'no_reff' => $get_invoicing->id,
						'debet' => $item->debit,
						'kredit' => $item->kredit,
						'id_perusahaan' => $item->id_company,
						'nm_perusahaan' => $item->nm_company
					];
				}

				if ($get_jurnal->id_company == '1' || $get_jurnal->id_company == '6') {
					$insert_jurnal = $this->accounting_stm->insert_batch('jurnal', $arr_jurnal);
				} else if ($get_jurnal->id_company == '4' || $get_jurnal->id_company == '5') {
					$insert_jurnal = $this->accounting_vuca->insert_batch('jurnal', $arr_jurnal);
				} else {
					$insert_jurnal = $this->accounting_sustain->insert_batch('jurnal', $arr_jurnal);
				}

				$update_jurnal_sts = $this->db->update('tr_jurnal', ['sts' => '1'], ['no_transaksi' => $get_jurnal->no_transaksi, 'jenis_transaksi' => $get_jurnal->jenis_transaksi]);
				if ($get_jurnal->id_company == '1' || $get_jurnal->id_company == '6') {
					$update_cabang_acc = $this->accounting_stm->query('UPDATE pastibisa_tb_cabang SET nobum = nobum+1 WHERE nocab = "101"');
				} else if ($get_jurnal->id_company == '4' || $get_jurnal->id_company == '5') {
					$update_cabang_acc = $this->accounting_vuca->query('UPDATE pastibisa_tb_cabang SET nobum = nobum+1 WHERE nocab = "101"');
				} else {
					$update_cabang_acc = $this->accounting_sustain->query('UPDATE pastibisa_tb_cabang SET nobum = nobum+1 WHERE nocab = "101"');
				}

				$this->db->trans_commit();

				$msg = "Posting jurnal ke Tras Sukses !";

				$response = [
					'status' => 1,
					'msg' => $msg
				];

				echo json_encode($response);
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();

			$param = array(
				'save' => 0,
				'msg' => $e->getMessage()
			);

			echo json_encode($param);
		}
	}

	public function fix_company()
	{
		$get_jurnal = $this->db->get_where('tr_jurnal', ['jenis_transaksi' => 'Penerimaan Piutang'])->result();

		$arr_update_jurnal = [];
		foreach ($get_jurnal as $item_jurnal) {
			$get_invoicing = $this->db->get_where('tr_invoicing', ['id' => $item_jurnal->no_transaksi])->row();

			$get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_invoicing->id_penawaran])->row();
			$get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row();

			$id_company = (!empty($get_company)) ? $get_company->id : '';
			$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';


			$arr_update_jurnal[] = [
				'id' => $item_jurnal->id,
				'id_company' => $id_company,
				'nm_company' => $nm_company
			];
		}

		$this->db->trans_begin();

		$update_jurnal = $this->db->update_batch('tr_jurnal', $arr_update_jurnal, 'id');
		if (!$update_jurnal) {
			$this->db->trans_rollback();

			print_r($this->db->last_query());
			exit;
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();

			echo json_encode([
				'status' => 1,
				'msg' => 'Data has not been updated !'
			]);
		} else {
			$this->db->trans_commit();

			echo json_encode([
				'status' => 1,
				'msg' => 'Data has been updated !'
			]);
		}
	}

	public function download_excel()
	{
		$this->db->select('a.id, a.tgl_jurnal, a.no_transaksi, a.coa, a.nm_coa, a.debit, a.kredit, a.sts, b.nm_customer, b.nm_project, b.no_invoice, b.id_spk_penawaran, d.id as id_company, a.nm_company, e.name as nm_divisi');
		$this->db->from('tr_jurnal a');
		$this->db->join('tr_invoicing b', 'b.id = a.no_transaksi');
		$this->db->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran');
		$this->db->join(DBCNL . '.kons_tr_company d', 'd.id = c.company');
		$this->db->join(DBHRIS . '.divisions e', 'e.id = c.id_divisi');
		$this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
		// $this->db->group_start();
		// $this->db->where('a.debit >', 0);
		// $this->db->or_where('a.kredit >', 0);
		// $this->db->group_end();
		// $this->db->group_by('a.no_transaksi');	

		$get_data_jurnal = $this->db->get()->result();

		$this->load->view('download_excel', ['list_jurnal' => $get_data_jurnal]);
	}

	public function update_sts_revisi_jurnal()
	{
		$this->Jurnal_penerimaan_model->update_sts_revisi_jurnal();
	}

	public function get_data_jurnal_invoicing()
	{
		$this->Jurnal_penerimaan_model->get_data_jurnal_invoicing();
	}
}
