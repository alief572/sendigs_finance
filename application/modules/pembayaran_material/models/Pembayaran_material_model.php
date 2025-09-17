<?php
class Pembayaran_material_model extends BF_Model
{

	protected $consultant;
	protected $accounting;
	protected $hris;

	public function __construct()
	{
		parent::__construct();

		$this->consultant = $this->load->database('consultant', true);
		$this->accounting = $this->load->database('accounting', true);
		$this->hris = $this->load->database('hris', true);
	}
	public function get_data_json_request_payment_header($sqlwhere = '')
	{
		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment_header a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.id desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_request_payment($sqlwhere = '')
	{

		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.id desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_request_payment_nm($sqlwhere = '')
	{

		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment_nm a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.no_po desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_jurnal($sqlwhere = '')
	{

		$sql = "SELECT nomor,tanggal,no_reff,stspos FROM jurnaltras a WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " group by nomor,tanggal,no_reff,stspos order by no_reff desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}

	public function generate_id_payment_paid($kode_bank = null, $tanggal)
	{
		$generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM tr_payment_paid WHERE id LIKE '%BK-" . $kode_bank . "-" . date('my-', strtotime($tanggal)) . "%'")->row();
		$kodeBarang = $generate_id->max_id;
		$urutan = (int) substr($kodeBarang, 16, 4);
		if ($kode_bank == null) {
			$urutan = (int) substr($kodeBarang, 9, 4);
		}
		$urutan++;
		$tahun = date('my-', strtotime($tanggal));
		$huruf = "BK-" . $kode_bank . "-";
		$kodecollect = $huruf . $tahun . sprintf("%04s", $urutan);

		return $kodecollect;
	}

	public function get_list_req_payment()
	{
		$post = $this->input->post();

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];
		$jenis_payment = $post['jenis_payment'];

		$hasil = [];

		if ($jenis_payment == 1) {
			$this->db->select('a.id, a.created_on, a.no_doc, a.currency, a.jumlah, a.keperluan, b.created_by as requestor');
			$this->db->from('payment_approve a');
			$this->db->join('tr_expense b', 'b.no_doc = a.no_doc');
			$this->db->where('a.status <>', 2);
			$this->db->where('b.exp_inv_po', 1);
			if (!empty($search['value'])) {
				$this->db->group_start();
				$this->db->like('a.no_doc', $search['value'], 'both');
				$this->db->or_like('a.created_on', $search['value'], 'both');
				$this->db->or_like('a.keperluan', $search['value'], 'both');
				$this->db->or_like('a.currency', $search['value'], 'both');
				$this->db->or_like('a.jumlah', $search['value'], 'both');
				$this->db->group_end();
			}
			$this->db->order_by('a.created_on', 'desc');
			$this->db->group_by('a.id');

			$db_clone = clone $this->db;
			$count_all = $db_clone->count_all_results();

			$this->db->limit($length, $start);
			$get_data = $this->db->get()->result();

			$hasil = [];

			$no = (0 + $start);
			foreach ($get_data as $item) {
				$no_incoming = [];
				$no_po = [];
				$nm_supplier = [];

				if (!empty($get_rec_invoice)) {
					if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
						$arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
						$get_no_po = $this->db
							->select('a.no_ipp')
							->from('tr_incoming_check a')
							->where_in('a.kode_trans', explode(',', $arr_no_incoming))
							->get()
							->result();

						$arr_no_po = [];
						foreach ($get_no_po as $item_no_po) {
							$arr_no_po[] = $item_no_po->no_ipp;
						}

						$arr_no_po = implode(',', $arr_no_po);
						$arr_no_po = str_replace(', ', ',', $arr_no_po);

						$get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
						foreach ($get_no_surat as $item_no_surat) {
							$no_po[] = $item_no_surat->no_surat;
						}
					} else {
						$no_po[] = $get_rec_invoice->no_po;
					}
				}

				if (!empty($no_po)) {
					$get_nm_supplier = $this->db
						->select('b.nama as nm_supplier')
						->from('tr_purchase_order a')
						->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
						->where_in('a.no_surat', $no_po)
						->group_by('b.nama')
						->get()
						->result();
					foreach ($get_nm_supplier as $item_supplier) {
						$nm_supplier[] = $item_supplier->nm_supplier;
					}
				}

				$nm_supplier = implode(', ', $nm_supplier);

				$get_choosed_payment = $this->db->get_where('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $item->id])->result();
				$checked = (count($get_choosed_payment) > 0) ? 'checked' : null;

				$option = '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . $checked . '>';

				$hasil[] = [
					'no' => $no,
					'no_dokumen' => $item->no_doc,
					'tgl' => date('d F Y', strtotime($item->created_on)),
					'keperluan' => $item->keperluan,
					'currency' => $item->currency,
					'total_invoice' => number_format($item->jumlah),
					'requestor' => $item->requestor,
					'option' => $option
				];
			}
		} else {
			$this->db->select('a.id, a.created_on, a.no_doc, a.currency, a.jumlah, a.keperluan, a.tipe');
			$this->db->from('payment_approve a');
			$this->db->join('tr_expense b', 'b.no_doc = a.no_doc', 'left');
			$this->db->where('a.status <>', 2);
			$this->db->group_start();
			$this->db->where('b.exp_inv_po <>', 1);
			$this->db->or_where('b.exp_inv_po', null);
			$this->db->group_end();
			if (!empty($search['value'])) {
				$this->db->group_start();
				$this->db->like('a.no_doc', $search['value'], 'both');
				$this->db->or_like('a.created_on', $search['value'], 'both');
				$this->db->or_like('a.keperluan', $search['value'], 'both');
				$this->db->or_like('a.currency', $search['value'], 'both');
				$this->db->or_like('a.jumlah', $search['value'], 'both');
				$this->db->group_end();
			}
			$this->db->order_by('a.created_on', 'desc');
			$this->db->group_by('a.id');

			$db_clone = clone $this->db;
			$count_all = $db_clone->count_all_results();

			$this->db->limit($length, $start);
			$get_data = $this->db->get()->result();

			$hasil = [];

			$no = (0 + $start);
			foreach ($get_data as $item) {
				$no++;

				$get_choosed_payment = $this->db->get_where('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $item->id])->result();

				$checked = (count($get_choosed_payment) > 0) ? 'checked' : null;

				$option = '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . $checked . '>';

				$requestor = '';
				if ($item->tipe == 'kasbon') {
					$get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_kasbon)) ? $get_kasbon->nama : '';
				}
				if ($item->tipe == 'expense') {
					$get_expense = $this->db->get_where('tr_expense', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_expense)) ? $get_expense->nama : '';
				}
				if ($item->tipe == 'transport' || $item->tipe == 'transportasi') {
					$get_transport_req = $this->db->get_where('tr_transport_req', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_transport_req)) ? $get_transport_req->nama : '';
				}

				$hasil[] = [
					'no' => $no,
					'no_dokumen' => $item->no_doc,
					'tgl' => date('d F Y', strtotime($item->created_on)),
					'keperluan' => $item->keperluan,
					'currency' => $item->currency,
					'total_invoice' => number_format($item->jumlah),
					'requestor' => $requestor,
					'option' => $option
				];
			}
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function set_jurnal()
	{
		$post = $this->input->post();

		$id_payment = $post['id_payment'];
		$payment_bank = str_replace(',', '', $post['payment_bank']);
		$bank_charge = str_replace(',', '', $post['bank_charge']);
		$bank = $post['bank'];

		$hasil_jurnal = '';
		$ttl_debit = 0;
		$ttl_kredit = 0;

		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->where_in('a.id', explode(',', $id_payment));
		$get_payment = $this->db->get()->result();

		foreach ($get_payment as $item_payment) :
			if ($item_payment->tipe == 'kasbon') :
				$coa_bank = '';
				if (!empty($bank)) {
					$get_coa_bank = $this->db->get_where('ms_bank', ['id' => $bank])->row();

					$coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank->coa_bank : '';
				}

				$arr_coa_jurnal = ['1030-29-9', '7010-20-5'];
				if (!empty($bank)) {
					$get_bank = $this->db->get_where('ms_bank', ['id' => $bank])->row();

					array_push($arr_coa_jurnal, $get_bank->coa_bank);
				}

				$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
				$this->accounting->from('coa_master a');
				$this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
				$get_coa_jurnal = $this->accounting->get()->result();

				$no_jurnal = 1;
				foreach ($get_coa_jurnal as $item_coa) :
					$debit = 0;
					$kredit = 0;

					$this->db->select('b.title_id');
					$this->db->from('tr_kasbon a');
					$this->db->join('users b', 'b.nm_lengkap = a.created_by');
					$this->db->where('a.no_doc', $item_payment->no_doc);
					$get_kasbon_user_title = $this->db->get()->row();

					$id_divisi = '';
					$nm_divisi = '';
					if (!empty($get_kasbon_user_title)) {
						$this->hris->select('a.id as id_title, a.name as nm_title');
						$this->hris->from('titles a');
						$this->hris->where('a.id', $get_kasbon_user_title->title_id);
						$get_titles = $this->hris->get()->row();

						$id_divisi = (!empty($get_titles)) ? $get_titles->id_title : '';
						$nm_divisi = (!empty($get_titles)) ? $get_titles->nm_title : '';
					}

					if ($item_coa->no_coa == '1030-29-9') {
						$id_company = '';
						$nm_company = '';

						$debit = $item_payment->jumlah;

						$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();

						$id_kasbon_consultant = (!empty($get_kasbon->no_kasbon_consultant)) ? $get_kasbon->no_kasbon_consultant : '';

						if (!empty($id_kasbon_consultant)) {
							$this->consultant->select('a.id as id_company, a.nm_company');
							$this->consultant->from('kons_tr_company a');
							$this->consultant->join('kons_tr_penawaran b', 'b.company = a.id', 'left');
							$this->consultant->join('kons_tr_kasbon_project_header c', 'c.id_penawaran = b.id_quotation', 'left');
							$this->consultant->where('c.id', $id_kasbon_consultant);
							$get_company = $this->consultant->get()->row();

							$id_company = (!empty($get_company)) ? $get_company->id_company : '';
							$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
						}

						$hasil_jurnal .= '<tr>';

						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= date('d F Y');
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $nm_company;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $nm_divisi;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_divisi]" value="' . $id_divisi . '">';
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $item_coa->no_coa;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $item_coa->no_coa . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $item_coa->nm_coa;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $item_coa->nm_coa . ' - ' . $item_payment->no_doc;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $item_coa->nm_coa . ' - ' . $item_payment->no_doc . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '<td class="text-right">';
						$hasil_jurnal .= number_format($debit);
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '<td class="text-right">';
						$hasil_jurnal .= number_format($kredit);
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
						$hasil_jurnal .= '</td>';

						$hasil_jurnal .= '</tr>';

						$ttl_debit += $debit;
						$ttl_kredit += $kredit;
						$no_jurnal++;
					} else {
						if ($item_coa->no_coa == '7010-20-5') {
							$id_company = '';
							$nm_company = '';

							$id_divisi = '';
							$nm_divisi = '';


							$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();
							$get_users = $this->db->get_where('users', ['nm_lengkap' => $get_kasbon->created_by])->row();

							if (!empty($get_users)) {
								$this->hris->select('a.id as id_divisi, a.name as nm_divisi');
								$this->hris->from('titles a');
								$this->hris->where('a.id', $get_users->title_id);
								$get_titles = $this->hris->get()->row();

								$id_divisi = (!empty($get_titles)) ? $get_titles->id_divisi : '';
								$nm_divisi = (!empty($get_titles)) ? $get_titles->nm_divisi : '';
							}

							$id_kasbon_consultant = (!empty($get_kasbon->no_kasbon_consultant)) ? $get_kasbon->no_kasbon_consultant : '';

							if (!empty($id_kasbon_consultant)) {
								$this->consultant->select('a.id as id_company, a.nm_company');
								$this->consultant->from('kons_tr_company a');
								$this->consultant->join('kons_tr_penawaran b', 'b.company = a.id', 'left');
								$this->consultant->join('kons_tr_kasbon_project_header c', 'c.id_penawaran = b.id_quotation', 'left');
								$this->consultant->where('c.id', $id_kasbon_consultant);
								$get_company = $this->consultant->get()->row();

								$id_company = (!empty($get_company)) ? $get_company->id_company : '';
								$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
							}

							if ($bank_charge > 0) {
								$kredit = $bank_charge;
								$hasil_jurnal .= '<tr>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= date('d F Y');
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_company;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_divisi;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_divisi]" value="' . $id_divisi . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $item_coa->no_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $item_coa->no_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $item_coa->nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $item_coa->nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $item_coa->nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-right">';
								$hasil_jurnal .= number_format($debit);
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-right">';
								$hasil_jurnal .= number_format($kredit);
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '</tr>';

								$ttl_debit += $debit;
								$ttl_kredit += $kredit;

								$no_jurnal++;
							}
						} else {
							if (!empty($coa_bank) && $coa_bank == $item_coa->no_coa) {
								$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();
								$get_users = $this->db->get_where('users', ['nm_lengkap' => $get_kasbon->created_by])->row();

								$kredit = $payment_bank;
								if ($payment_bank > $get_kasbon->jumlah_kasbon) {
									$kredit = $get_kasbon->jumlah_kasbon;
								}

								$id_company = '';
								$nm_company = '';

								$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();
								$get_users = $this->db->get_where('users', ['nm_lengkap' => $get_kasbon->created_by])->row();

								$id_kasbon_consultant = (!empty($get_kasbon)) ? $get_kasbon->no_kasbon_consultant : '';

								if (!empty($get_users)) {
									$this->hris->select('a.id as id_divisi, a.name as nm_divisi');
									$this->hris->from('titles a');
									$this->hris->where('a.id', $get_users->title_id);
									$get_titles = $this->hris->get()->row();

									$id_divisi = (!empty($get_titles)) ? $get_titles->id_divisi : '';
									$nm_divisi = (!empty($get_titles)) ? $get_titles->nm_divisi : '';
								}

								$this->consultant->select('a.id as id_company, a.nm_company');
								$this->consultant->from('kons_tr_company a');
								$this->consultant->join('kons_tr_penawaran b', 'b.company = a.id', 'left');
								$this->consultant->join('kons_tr_kasbon_project_header c', 'c.id_penawaran = b.id_quotation', 'left');
								$this->consultant->where('c.id', $id_kasbon_consultant);
								$get_company = $this->consultant->get()->row();

								$id_company = (!empty($get_company)) ? $get_company->id_company : '';
								$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';

								$this->db->select('a.rekening, a.nama, b.nama_bank');
								$this->db->from('ms_bank a');
								$this->db->join('list_bank b', 'b.id = a.bank', 'left');
								$this->db->where('a.id', $bank);
								$get_bank = $this->db->get()->row();

								$nm_bank = (!empty($get_bank)) ? $get_bank->rekening . ' - ' . $get_bank->nama_bank . ' - ' . $get_bank->nama : '';

								$hasil_jurnal .= '<tr>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= date('d F Y');
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_company;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_divisi;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_divisi]" value="' . $id_divisi . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $item_coa->no_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $item_coa->no_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $item_coa->nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $item_coa->nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $item_coa->nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-right">';
								$hasil_jurnal .= number_format($debit);
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-right">';
								$hasil_jurnal .= number_format($kredit);
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '</tr>';

								$ttl_debit += $debit;
								$ttl_kredit += $kredit;
								$no_jurnal++;
							}
						}
					}

				endforeach;
			endif;

			if ($item_payment->tipe == 'transport' || $item_payment->tipe == 'transportasi') {
				$coa_bank = '';
				if (!empty($bank)) {
					$get_coa_bank = $this->db->get_where('ms_bank', ['id' => $bank])->row();

					$coa_bank = (!empty($get_coa_bank)) ? $get_coa_bank->coa_bank : '';
				}

				$arr_coa_jurnal = ['5010-30-1', '1010-10-2'];

				$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
				$this->accounting->from('coa_master a');
				$this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
				$get_coa_jurnal = $this->accounting->get()->result();

				$no_jurnal = 1;
				foreach ($get_coa_jurnal as $item_coa) {
					$debit = 0;
					$kredit = 0;
					if ($item_coa->no_coa == '5010-30-1') {
						$debit = $item_payment->jumlah;
					}
					if ($item_coa->no_coa == '1010-10-2') {
						$kredit = $item_payment->jumlah;
					}

					$this->db->select('a.title_id');
					$this->db->from('users a');
					$this->db->join('tr_transport_req b', 'b.created_by = a.nm_lengkap');
					$this->db->where('b.no_doc', $item_payment->no_doc);
					$get_transport_title = $this->db->get()->row();

					$id_company = '';
					$nm_company = '';
					if ($get_transport_title->title_id == 'TIT009') {
						$get_company = $this->consultant->get_where('kons_tr_company', ['id' => 4])->row();

						$id_company = (!empty($get_company)) ? $get_company->id : '';
						$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
					}

					$id_divisi = '';
					$nm_divisi = '';
					if (!empty($get_transport_title)) {
						$get_title = $this->hris->get_where('titles', ['id' => $get_transport_title->title_id])->row();

						$id_divisi = (!empty($get_title)) ? $get_title->id : '';
						$nm_divisi = (!empty($get_title)) ? $get_title->name : '';
					}


					$hasil_jurnal .= '<tr>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= date('d F Y');
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_company;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_divisi;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_divisi]" value="' . $id_divisi . '">';
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $item_coa->no_coa;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $item_coa->no_coa . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $item_coa->nm_coa;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $item_coa->nm_coa . ' - ' . $item_payment->no_doc;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $item_coa->nm_coa . ' - ' . $item_payment->no_doc . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-right">';
					$hasil_jurnal .= number_format($debit);
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-right">';
					$hasil_jurnal .= number_format($kredit);
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '</tr>';

					$ttl_debit += $debit;
					$ttl_kredit += $kredit;
					$no_jurnal++;
				};
			}
		endforeach;

		$response = [
			'hasil_jurnal' => $hasil_jurnal,
			'ttl_debit' => $ttl_debit,
			'ttl_kredit' => $ttl_kredit
		];

		echo json_encode($response);
	}

	public function generate_id_invoice_jurnal($nomor)
	{
		$Ym             = date('ym');
		$srcMtr            = "SELECT MAX(id) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
		$resultMtr        = $this->db->query($srcMtr)->result_array();
		$angkaUrut2        = $resultMtr[0]['maxP'];
		$urutan2        = (int)substr($angkaUrut2, 0, 5);
		$urutan2 = $urutan2 + $nomor;
		$urut2            = sprintf('%05s', $urutan2);
		$kode_trans        = $urut2 . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');

		return $kode_trans;
	}

	public function check_transport_payment($id_payment)
	{
		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->join('tr_transport_req b', 'b.no_doc = a.no_doc');
		$this->db->where_in('a.id', $id_payment);
		$get_transport = $this->db->get()->result();

		$result = (!empty($get_transport)) ? 1 : 0;

		return $result;
	}

	public function jurnal_refill_petty_cash($id_payment, $id_bank = null)
	{
		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->join('tr_transport_req b', 'b.no_doc = a.no_doc');
		$this->db->join('users c', 'c.nm_lengkap = a.created_by');
		$this->db->where_in('a.id', $id_payment);
		$this->db->group_by('a.id');
		$get_transport_val = $this->db->get()->result();

		return $get_transport_val;
	}

	public function set_jurnal_refill()
	{
		$post = $this->input->post();

		$id_payment = $post['id_payment'];
		$bank = $post['bank'];

		$hasil = '';

		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->where_in('a.id', explode(',', $id_payment));
		$get_payment = $this->db->get()->result();

		$ttl_debit = 0;
		$ttl_kredit = 0;

		foreach ($get_payment as $item_payment) {
			if ($item_payment->tipe == 'transportasi' || $item_payment->tipe == 'transport') {
				$this->db->select('b.title_id');
				$this->db->from('tr_transport_req a');
				$this->db->join('users b', 'b.nm_lengkap = a.created_by');
				$this->db->where('a.no_doc', $item_payment->no_doc);
				$get_check_transport_title_user = $this->db->get()->row();

				$id_divisi = '';
				$nm_divisi = '';

				if ($get_check_transport_title_user->title_id == 'TIT009') {
					$arr_coa_jurnal_refill = ['1010-10-2'];

					$this->hris->select('a.id as id_title, a.name as nm_title');
					$this->hris->from('titles a');
					$this->hris->where('a.id', $get_check_transport_title_user->title_id);
					$get_titles = $this->hris->get()->row();

					$id_divisi = (!empty($get_titles)) ? $get_titles->id_title : '';
					$nm_divisi = (!empty($get_titles)) ? $get_titles->nm_title : '';

					$nm_bank = '';

					if (!empty($bank)) {
						$this->db->select('a.rekening, a.nama, a.coa_bank, b.nama_bank as nm_bank');
						$this->db->from('ms_bank a');
						$this->db->join('list_bank b', 'b.id = a.bank', 'left');
						$this->db->where('a.id', $bank);
						$get_bank = $this->db->get()->row();

						$nm_bank = $get_bank->rekening . ' a/n ' . $get_bank->nm_bank;

						$arr_coa_jurnal_refill[] = $get_bank->coa_bank;
					}

					$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
					$this->accounting->from('coa_master a');
					$this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal_refill);
					$get_coa_jurnal_refill = $this->accounting->get()->result();

					$no_jurnal = 0;
					foreach ($get_coa_jurnal_refill as $item_coa) {
						$no_jurnal++;

						$debit = 0;
						$kredit = 0;

						$keterangan = 'Refill Pettycash - ' . $item_payment->no_doc;
						if ($item_coa->no_coa == '1010-10-2') {
							$debit = $item_payment->jumlah;
						} else {
							$kredit = $item_payment->jumlah;
							$keterangan = $nm_bank . ' - ' . $item_payment->no_doc;
						}

						$this->consultant->select('a.id, a.nm_company');
						$this->consultant->from('kons_tr_company a');
						$this->consultant->where('a.id', 4);
						$get_company = $this->consultant->get()->row();

						$id_company = (!empty($get_company)) ? $get_company->id : '';
						$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';

						$hasil .= '<tr>';

						$hasil .= '<td class="text-center">';
						$hasil .= date('d F Y');
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
						$hasil .= '</td>';

						$hasil .= '<td class="text-center">';
						$hasil .= $nm_company;
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
						$hasil .= '</td>';

						$hasil .= '<td class="text-center">';
						$hasil .= $nm_divisi;
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][id_divisi]" value="' . $id_divisi . '">';
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
						$hasil .= '</td>';

						$hasil .= '<td class="text-center">';
						$hasil .= $item_coa->no_coa;
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][no_coa]" value="' . $item_coa->no_coa . '">';
						$hasil .= '</td>';

						$hasil .= '<td class="text-center">';
						$hasil .= $item_coa->nm_coa;
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
						$hasil .= '</td>';

						$hasil .= '<td class="text-center">';
						$hasil .= $keterangan;
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
						$hasil .= '</td>';

						$hasil .= '<td class="text-right">';
						$hasil .= number_format($debit);
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][debit]" value="' . $debit . '">';
						$hasil .= '</td>';

						$hasil .= '<td class="text-right">';
						$hasil .= number_format($kredit);
						$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
						$hasil .= '</td>';

						$hasil .= '</tr>';

						$ttl_debit += $debit;
						$ttl_kredit += $kredit;
					}
				}
			}
		}

		$response = [
			'hasil' => $hasil,
			'ttl_debit' => $ttl_debit,
			'ttl_kredit' => $ttl_kredit
		];

		echo json_encode($response);
	}
}
