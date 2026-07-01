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

		if ($urutan == '') {
			$urutan = 0;
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
		$jenis_payment = $post['jenis_payment'];
		$search = $post['search']['value'];

		$this->db->from('v_list_payment');
		$this->db->where('status <>', 2);

		// Logika Filter Jenis Payment
		if ($jenis_payment == 1) {
			$this->db->group_start()
				->where('is_po_payment', 1)
				->or_where('tipe', 'Cash')
				->group_end();
		} else {
			$this->db->where('is_po_payment <>', 1);
		}

		// Global Search
		if (!empty($search)) {
			$this->db->group_start()
				->like('no_doc', $search)
				->or_like('requestor', $search)
				->or_like('keperluan', $search)
				->group_end();
		}

		// Clone untuk count total
		$count_all = $this->db->count_all_results('', FALSE);

		// Order & Limit
		$this->db->order_by('created_on', 'DESC');
		$this->db->limit($post['length'], $post['start']);
		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $post['start']);
		foreach ($get_data as $item) {
			$no++;
			// Logika checkbox tetep di sini karena butuh session user_id
			$is_checked = $this->db->get_where('tr_choosed_payment', [
				'id_user' => $this->auth->user_id(),
				'id_payment' => $item->id
			])->num_rows() > 0;

			$hasil[] = [
				'no' => $no,
				'no_dokumen' => $item->no_doc,
				'tgl' => date('d F Y', strtotime($item->created_on)),
				'keperluan' => $item->keperluan,
				'total_invoice' => number_format($item->jumlah),
				'requestor' => $item->requestor,
				'currency' => !empty($item->currency) ? $item->currency : 'IDR',
				'option' => '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . ($is_checked ? 'checked' : '') . '>'
			];
		}

		echo json_encode([
			'draw' => intval($post['draw']),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		]);
	}

	public function set_jurnal()
	{
		$post = $this->input->post();

		$id_payment = $post['id_payment'];
		$payment_bank = str_replace(',', '', $post['payment_bank'] ?? '');
		$bank_charge = str_replace(',', '', $post['bank_charge'] ?? '');
		$bank = $post['bank'] ?? '';
		$total_payment = str_replace(',', '', $post['total_payment'] ?? '');

		// Determine admin charge bearer (company or recipient)
		$admin_charge_bearer = isset($post['admin_charge_bearer']) ? $post['admin_charge_bearer'] : '';
		if (!in_array($admin_charge_bearer, ['company', 'recipient'])) {
			$admin_charge_bearer = 'company'; // Default for backward compatibility
		}

		// Ambil tgl_bayar dari input user, fallback ke hari ini jika kosong
		$tgl_bayar = !empty($post['tgl_bayar']) ? $post['tgl_bayar'] : date('Y-m-d');
		$tgl_bayar_display = date('d F Y', strtotime($tgl_bayar));
		$tgl_bayar_value = date('Y-m-d', strtotime($tgl_bayar));

		$hasil_jurnal = '';
		$hasil_jurnal_refill = '';
		$ttl_debit = 0;
		$ttl_kredit = 0;
		$ttl_debit_refill = 0;
		$ttl_kredit_refill = 0;

		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->where_in('a.id', explode(',', $id_payment));
		$get_payment = $this->db->get()->result();

		// Cek apakah semua pembayaran bertipe petty_cash_hutang
		$is_all_petty_cash_hutang = true;
		foreach ($get_payment as $_check_item) {
			if ($_check_item->tipe != 'petty_cash_hutang') {
				$is_all_petty_cash_hutang = false;
				break;
			}
		}

		$nilai_pph = $this->input->post('nilai_pph', true);
		$nilai_ppn = $this->input->post('nilai_ppn', true);

		// Cache arrays to reduce duplicate database lookups
		$company_cache = [];
		$titles_cache = [];
		$coa_cache = [];

		$coa_bank = '';
		$nm_bank = '';
		if (!empty($bank)) {
			$this->db->select('a.rekening, a.nama, a.coa_bank, b.nama_bank');
			$this->db->from('ms_bank a');
			$this->db->join('list_bank b', 'b.id = a.bank', 'left');
			$this->db->where('a.id', $bank);
			$get_bank_detail = $this->db->get()->row();
			if (!empty($get_bank_detail)) {
				$coa_bank = $get_bank_detail->coa_bank;
				$nm_bank = $get_bank_detail->rekening . ' - ' . $get_bank_detail->nama_bank . ' - ' . $get_bank_detail->nama;
			}
		}

		// Helper to load multiple COAs into coa_cache
		$load_coas = function ($arr_coas) use (&$coa_cache) {
			$missing = [];
			foreach ($arr_coas as $coa) {
				if (!empty($coa) && !isset($coa_cache[$coa])) {
					$missing[] = $coa;
				}
			}
			if (!empty($missing)) {
				$coas_data = $this->accounting->select('no_perkiraan as no_coa, nama as nm_coa')
					->from('coa_master')
					->where_in('no_perkiraan', $missing)
					->get()->result();
				foreach ($coas_data as $row) {
					$coa_cache[$row->no_coa] = $row->nm_coa;
				}
				foreach ($missing as $coa) {
					if (!isset($coa_cache[$coa])) {
						$coa_cache[$coa] = '';
					}
				}
			}
		};

		// Helper to get COA list ordered by the input array
		$get_coa_list = function ($arr_coas) use (&$coa_cache, $load_coas) {
			$load_coas($arr_coas);
			$list = [];
			foreach ($arr_coas as $coa) {
				if (!empty($coa) && isset($coa_cache[$coa]) && $coa_cache[$coa] !== '') {
					$list[] = (object)[
						'no_coa' => $coa,
						'nm_coa' => $coa_cache[$coa]
					];
				}
			}
			return $list;
		};

		// Helper closure to generate HTML rows
		$generate_tr = function ($no_jurnal, $id_payment_ref, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $coa, $nm_coa, $keterangan, $debit, $kredit, $div_key = 'id_divisi', $coa_key = 'coa') use (&$ttl_debit, &$ttl_kredit) {
			$tr = '<tr>';
			$tr .= '<td class="text-center">';
			$tr .= $tgl_bayar_display;
			if ($id_payment_ref !== null) {
				$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $id_payment_ref . '">';
			}
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $nm_company;
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $nm_divisi;
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][' . $div_key . ']" value="' . $id_divisi . '">';
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $coa;
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][' . $coa_key . ']" value="' . $coa . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $nm_coa;
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $keterangan;
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-right">';
			$tr .= number_format($debit);
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-right">';
			$tr .= number_format($kredit);
			$tr .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
			$tr .= '</td>';
			$tr .= '</tr>';

			$ttl_debit += $debit;
			$ttl_kredit += $kredit;
			return $tr;
		};

		$generate_tr_refill = function ($no_jurnal, $id_payment_ref, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $coa, $nm_coa, $keterangan, $debit, $kredit, $div_key = 'id_divisi', $coa_key = 'coa') {
			$tr = '<tr>';
			$tr .= '<td class="text-center">';
			$tr .= $tgl_bayar_display;
			if ($id_payment_ref !== null) {
				$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][id_payment_ref]" value="' . $id_payment_ref . '">';
			}
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $nm_company;
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $nm_divisi;
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][' . $div_key . ']" value="' . $id_divisi . '">';
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $coa;
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][' . $coa_key . ']" value="' . $coa . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $nm_coa;
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_account]" value="' . $nm_coa . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-center">';
			$tr .= $keterangan;
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][deskripsi]" value="' . $keterangan . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-right">';
			$tr .= number_format($debit);
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][debit]" value="' . $debit . '">';
			$tr .= '</td>';

			$tr .= '<td class="text-right">';
			$tr .= number_format($kredit);
			$tr .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
			$tr .= '</td>';

			$tr .= '</tr>';
			return $tr;
		};

		$hasil_jurnal = '';
		$hasil_jurnal_refill = '';
		$no = 1;
		foreach ($get_payment as $item_payment) :

			if ($item_payment->tipe == 'kasbon') {
				$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();

				$id_divisi = '';
				$nm_divisi = '';

				$get_kasbon_user_title = null;
				if (!empty($get_kasbon)) {
					$this->db->select('b.title_id');
					$this->db->from('tr_kasbon a');
					$this->db->join('users b', 'b.nm_lengkap = a.created_by');
					$this->db->where('a.no_doc', $item_payment->no_doc);
					$get_kasbon_user_title = $this->db->get()->row();
				}

				if (!empty($get_kasbon_user_title)) {
					$title_id = $get_kasbon_user_title->title_id;
					if (!isset($titles_cache[$title_id])) {
						$this->hris->select('a.id as id_title, a.name as nm_title');
						$this->hris->from('titles a');
						$this->hris->where('a.id', $title_id);
						$titles_cache[$title_id] = $this->hris->get()->row();
					}
					$get_titles = $titles_cache[$title_id];
					$id_divisi = (!empty($get_titles)) ? $get_titles->id_title : '';
					$nm_divisi = (!empty($get_titles)) ? $get_titles->nm_title : '';
				}

				if (!empty($get_kasbon->no_kasbon_consultant)) {
					$this->consultant->select('a.created_by, b.employee_id');
					$this->consultant->from('kons_tr_kasbon_project_header a');
					$this->consultant->join('users b', 'b.id_user = a.created_by', 'left');
					$this->consultant->where('a.id', $get_kasbon->no_kasbon_consultant);
					$get_pengajuan_konsultan = $this->consultant->get()->row();

					if (!empty($get_pengajuan_konsultan)) {
						$this->hris->select('a.*, b.id as id_department, b.name as nm_department');
						$this->hris->from('employees a');
						$this->hris->join('departments b', 'b.id = a.department_id', 'left');
						$this->hris->where('a.id', $get_pengajuan_konsultan->employee_id);
						$get_department = $this->hris->get()->row();

						$id_divisi = $get_department->id_department ?? '';
						$nm_divisi = $get_department->nm_department ?? '';
					}
				} else {
					if (!empty($get_kasbon)) {
						$this->db->select('a.department_id');
						$this->db->from('users a');
						$this->db->where('a.username', $get_kasbon->created_by);
						$get_user = $this->db->get()->row();

						if (!empty($get_user)) {
							$this->hris->select('a.id as id_department, a.name as nm_department');
							$this->hris->from('departments a');
							$this->hris->where('a.id', $get_user->department_id);
							$get_department = $this->hris->get()->row();

							$id_divisi = $get_department->id_department ?? '';
							$nm_divisi = $get_department->nm_department ?? '';
						}
					}
				}

				$id_company = '';
				$nm_company = '';
				$id_kasbon_consultant = (!empty($get_kasbon->no_kasbon_consultant)) ? $get_kasbon->no_kasbon_consultant : '';
				if (!empty($id_kasbon_consultant)) {
					if (!isset($company_cache[$id_kasbon_consultant])) {
						$this->consultant->select('a.id as id_company, a.nm_company');
						$this->consultant->from('kons_tr_company a');
						$this->consultant->join('kons_tr_penawaran b', 'b.company = a.id', 'left');
						$this->consultant->join('kons_tr_kasbon_project_header c', 'c.id_penawaran = b.id_quotation', 'left');
						$this->consultant->where('c.id', $id_kasbon_consultant);
						$company_cache[$id_kasbon_consultant] = $this->consultant->get()->row();
					}
					$get_company = $company_cache[$id_kasbon_consultant];
					$id_company = (!empty($get_company)) ? $get_company->id_company : '';
					$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
				}

				$pph_data = $this->input->post('pph_data');
				$row_tipe_pph = isset($pph_data[$item_payment->id]) ? $pph_data[$item_payment->id] : '';
				$coa_pph = ($row_tipe_pph == '23') ? '2104-01-03' : '2104-01-02';

				$arr_coa_jurnal = ['1103-01-14', '7201-01-04', '1106-01-06', $coa_pph];
				if (!empty($coa_bank)) {
					array_push($arr_coa_jurnal, $coa_bank);
				}

				$get_coa_jurnal = $get_coa_list($arr_coa_jurnal);

				$no_jurnal = 1;
				foreach ($get_coa_jurnal as $item_coa) {
					$debit = 0;
					$kredit = 0;
					$keterangan = $item_coa->nm_coa . ' - ' . $item_payment->no_doc;

					if ($item_coa->no_coa == '1103-01-14') {
						if (!empty($id_kasbon_consultant)) {
							$get_kasbon_consultant = $this->consultant->select('a.*')
								->from('kons_tr_kasbon_project_header a')
								->where('a.id', $id_kasbon_consultant)
								->get()
								->row();

							if ($get_kasbon_consultant->tipe == '2') {
								$get_kasbon_detail = $this->consultant->select('a.*, b.no_coa, b.nm_coa, COALESCE(d.id, f.id) as company_id, COALESCE(d.nm_company, f.nm_company) as company_name')
									->from('kons_tr_kasbon_project_akomodasi a')
									->join('kons_master_biaya b', 'b.id = a.id_item', 'left')
									->join('kons_tr_penawaran c', 'c.id_quotation = a.id_penawaran', 'left')
									->join('kons_tr_company d', 'd.id = c.company', 'left')
									->join('kons_tr_spk_penawaran e', 'e.id_spk_penawaran = a.id_spk_penawaran', 'left')
									->join('kons_tr_company f', 'f.id = e.id_company', 'left')
									->where('a.id_header', $id_kasbon_consultant)
									->get()
									->result();

								foreach ($get_kasbon_detail as $item_kasbon) :
									$debit = $item_kasbon->total_pengajuan;
									$kredit = 0;

									$keterangan = $item_kasbon->nm_item;

									$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $item_kasbon->company_id, $item_kasbon->company_name, $id_divisi, $nm_divisi, $item_kasbon->no_coa, $item_kasbon->nm_coa, $keterangan, $debit, $kredit);
								endforeach;
							} else if ($get_kasbon_consultant->tipe == '3') {
								$get_kasbon_detail = $this->consultant->select('a.*, b.no_coa, b.nm_coa, COALESCE(d.id, f.id) as company_id, COALESCE(d.nm_company, f.nm_company) as company_name')
									->from('kons_tr_kasbon_project_others a')
									->join('kons_master_biaya b', 'b.id = a.id_item', 'left')
									->join('kons_tr_penawaran c', 'c.id_quotation = a.id_penawaran', 'left')
									->join('kons_tr_company d', 'd.id = c.company', 'left')
									->join('kons_tr_spk_penawaran e', 'e.id_spk_penawaran = a.id_spk_penawaran', 'left')
									->join('kons_tr_company f', 'f.id = e.id_company', 'left')
									->where('a.id_header', $id_kasbon_consultant)
									->get()
									->result();

								// print_r(get_kasbon_detail);
								// exit;

								foreach ($get_kasbon_detail as $item_kasbon) :
									$debit = $item_kasbon->total_pengajuan;
									$kredit = 0;

									$keterangan = $item_kasbon->nm_item;

									$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $item_kasbon->company_id, $item_kasbon->company_name, $id_divisi, $nm_divisi, $item_kasbon->no_coa, $item_kasbon->nm_coa, $keterangan, $debit, $kredit);
								endforeach;
							} else if ($get_kasbon_consultant->tipe == '4') {
								$get_kasbon_detail = $this->consultant->select('a.*, b.no_coa, b.nm_coa, COALESCE(d.id, f.id) as company_id, COALESCE(d.nm_company, f.nm_company) as company_name')
									->from('kons_tr_kasbon_project_lab a')
									->join('kons_master_lab b', 'b.id = a.id_item', 'left')
									->join('kons_tr_penawaran c', 'c.id_quotation = a.id_penawaran', 'left')
									->join('kons_tr_company d', 'd.id = c.company', 'left')
									->join('kons_tr_spk_penawaran e', 'e.id_spk_penawaran = a.id_spk_penawaran', 'left')
									->join('kons_tr_company f', 'f.id = e.id_company', 'left')
									->where('a.id_header', $id_kasbon_consultant)
									->get()
									->result();

								foreach ($get_kasbon_detail as $item_kasbon) :
									$debit = $item_kasbon->total_pengajuan;
									$kredit = 0;

									$keterangan = $item_kasbon->nm_item;

									$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $item_kasbon->company_id, $item_kasbon->company_name, $id_divisi, $nm_divisi, $item_kasbon->no_coa, $item_kasbon->nm_coa, $keterangan, $debit, $kredit);
								endforeach;
							} else if ($get_kasbon_consultant->tipe == '5') {
								$get_kasbon_detail = $this->consultant->select('a.*, b.no_coa, b.nm_coa, COALESCE(d.id, f.id) as company_id, COALESCE(d.nm_company, f.nm_company) as company_name')
									->from('kons_tr_kasbon_project_subcont_tenaga_ahli a')
									->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left')
									->join('kons_tr_penawaran c', 'c.id_quotation = a.id_penawaran', 'left')
									->join('kons_tr_company d', 'd.id = c.company', 'left')
									->join('kons_tr_spk_penawaran e', 'e.id_spk_penawaran = a.id_spk_penawaran', 'left')
									->join('kons_tr_company f', 'f.id = e.id_company', 'left')
									->where('a.id_header', $get_kasbon_consultant->id)
									->get()
									->result();

								foreach ($get_kasbon_detail as $item_kasbon) :
									$debit = $item_kasbon->total_pengajuan;
									$kredit = 0;

									$keterangan = $item_kasbon->nm_item;

									$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $item_kasbon->company_id, $item_kasbon->company_name, $id_divisi, $nm_divisi, $item_kasbon->no_coa, $item_kasbon->nm_coa, $keterangan, $debit, $kredit);
								endforeach;
							} else {
								$get_kasbon_detail = $this->consultant->select('a.*, b.no_coa, b.nm_coa, COALESCE(d.id, f.id) as company_id, COALESCE(d.nm_company, f.nm_company) as company_name')
									->from('kons_tr_kasbon_project_subcont_perusahaan a')
									->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left')
									->join('kons_tr_penawaran c', 'c.id_quotation = a.id_penawaran', 'left')
									->join('kons_tr_company d', 'd.id = c.company', 'left')
									->join('kons_tr_spk_penawaran e', 'e.id_spk_penawaran = a.id_spk_penawaran', 'left')
									->join('kons_tr_company f', 'f.id = e.id_company', 'left')
									->where('a.id_header', $id_kasbon_consultant)
									->get()
									->result();

								foreach ($get_kasbon_detail as $item_kasbon) :
									$debit = $item_kasbon->total_pengajuan;
									$kredit = 0;

									$keterangan = $item_kasbon->nm_item;

									$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $item_kasbon->company_id, $item_kasbon->company_name, $id_divisi, $nm_divisi, $item_kasbon->no_coa, $item_kasbon->nm_coa, $keterangan, $debit, $kredit);
								endforeach;
							}
						} else {
							$debit = $item_payment->jumlah;
							$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $item_coa->no_coa, $item_coa->nm_coa, $keterangan, $debit, $kredit);
						}
					} elseif ($item_coa->no_coa == '7201-01-04') {
						$debit = ($admin_charge_bearer === 'recipient') ? 0 : $bank_charge;
						$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $item_coa->no_coa, $item_coa->nm_coa, 'Admin Charge', $debit, $kredit);
					} elseif ($item_coa->no_coa == '1106-01-06') {
						$debit = $nilai_ppn;
						$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $item_coa->no_coa, $item_coa->nm_coa, 'PPN', $debit, $kredit);
					} elseif ($item_coa->no_coa == '2104-01-02' || $item_coa->no_coa == '2104-01-03') {
						$kredit = $nilai_pph;
						$keterangan = ($item_coa->no_coa == '2104-01-02') ? 'PPh 21' : 'PPh 23';
						$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $item_coa->no_coa, $item_coa->nm_coa, $keterangan, $debit, $kredit);
					} elseif (!empty($coa_bank) && $coa_bank == $item_coa->no_coa) {
						$kredit = ($admin_charge_bearer === 'recipient') ? ($total_payment - $bank_charge) : $total_payment;
						$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $item_coa->no_coa, $item_coa->nm_coa, $item_coa->nm_coa, $debit, $kredit);

						if ($bank_charge > 0) {
							$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $item_coa->no_coa, $item_coa->nm_coa, $item_coa->nm_coa, 0, $bank_charge);
						}
					}
				}
			} else if ($item_payment->tipe == 'transport' || $item_payment->tipe == 'transportasi') {
				$this->db->select('a.no_coa, a.nm_coa, a.created_by');
				$this->db->from('tr_transport a');
				$this->db->join('tr_transport_req b', 'b.no_doc = a.no_req');
				$this->db->where('b.no_doc', $item_payment->no_doc);
				$get_coa_transport = $this->db->get()->row();

				$coa_transport = (!empty($get_coa_transport->no_coa)) ? $get_coa_transport->no_coa : '';
				$nm_coa_transport = (!empty($get_coa_transport->nm_coa)) ? $get_coa_transport->nm_coa : '';

				$get_users = null;
				if (!empty($get_coa_transport)) {
					$get_users = $this->db->get_where('users', ['username' => $get_coa_transport->created_by])->row();
				}

				$get_department = null;
				if (!empty($get_users)) {
					$get_department = $this->hris->get_where('departments', ['id' => $get_users->department_id])->row();
				}

				$id_department = $get_department->id ?? '';
				$nm_department = $get_department->name ?? '';
				$idd_company = (!empty($get_department->company_id)) ? $get_department->company_id : '';

				$arr_coa_jurnal = [$coa_transport, '1106-01-06', '7201-01-04', '1106-01-01'];
				if (!empty($coa_bank)) {
					$arr_coa_jurnal[] = $coa_bank;
				}

				$get_coa_jurnal = $get_coa_list($arr_coa_jurnal);

				$get_transport_title = null;
				if (!empty($item_payment->no_doc)) {
					$this->db->select('a.title_id');
					$this->db->from('users a');
					$this->db->join('tr_transport_req b', 'b.created_by = a.nm_lengkap');
					$this->db->where('b.no_doc', $item_payment->no_doc);
					$get_transport_title = $this->db->get()->row();
				}

				$id_company = '';
				$nm_company = '';
				$target_comp_id = '';
				if ($idd_company == 'COM003') {
					$target_comp_id = '7';
				} else if ($idd_company == 'COM006') {
					$target_comp_id = '3';
				} else if ($idd_company == 'COM012') {
					$target_comp_id = '4';
				}

				if ($target_comp_id !== '') {
					if (!isset($company_cache[$target_comp_id])) {
						$company_cache[$target_comp_id] = $this->consultant->get_where('kons_tr_company', ['id' => $target_comp_id])->row();
					}
					$get_company = $company_cache[$target_comp_id];
					$id_company = (!empty($get_company)) ? $get_company->id : '';
					$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
				}

				$id_divisi = '';
				$nm_divisi = '';
				if (!empty($get_transport_title)) {
					$title_id = $get_transport_title->title_id;
					if (!isset($titles_cache[$title_id])) {
						$titles_cache[$title_id] = $this->hris->get_where('titles', ['id' => $title_id])->row();
					}
					$get_title = $titles_cache[$title_id];
					$id_divisi = (!empty($get_title)) ? $get_title->id : '';
					$nm_divisi = (!empty($get_title)) ? $get_title->name : '';
				}

				$no_jurnal = 1;
				foreach ($get_coa_jurnal as $item_coa) {
					$debit = 0;
					$kredit = 0;
					$keterangan = $item_coa->nm_coa . ' - ' . $item_payment->no_doc;

					if ($item_coa->no_coa == $coa_transport) {
						$debit = $item_payment->jumlah;
					} elseif (!empty($coa_bank) && $item_coa->no_coa == $coa_bank) {
						$kredit = $total_payment;
					} elseif ($item_coa->no_coa == '1106-01-06') {
						$debit = $nilai_ppn;
						$keterangan = 'PPN';
					} elseif ($item_coa->no_coa == '7201-01-04') {
						$debit = $bank_charge;
						$keterangan = 'Admin Charge';
					} elseif ($item_coa->no_coa == '2104-01-02' || $item_coa->no_coa == '2104-01-03') {
						$kredit = $nilai_pph;
						$keterangan = ($item_coa->no_coa == '2104-01-02') ? 'PPh 21' : 'PPh 23';
					} elseif ($item_coa->no_coa == '1106-01-01') {
						$kredit = $nilai_pph;
					}

					if ($debit == '') $debit = 0;
					if ($kredit == '') $kredit = 0;

					$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_department, $nm_department, $item_coa->no_coa, $item_coa->nm_coa, $keterangan, $debit, $kredit);
				}

				if ($bank_charge > 0 && !empty($coa_bank)) {
					$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_department, $nm_department, $coa_bank, $nm_bank, $nm_bank, 0, $bank_charge);
				}
			} else if ($item_payment->tipe == 'expense') {
				$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_payment->no_doc])->row();

				if (!empty($get_expense->exp_inv_po)) {

					$get_inv_po = $this->db->get_where('tr_invoice_po', ['id' => $get_expense->no_doc])->row();
					$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $get_inv_po->no_po])->row();
					$get_top_po = $this->db->get_where('tr_top_po', ['id' => $get_inv_po->id_top])->row();

					$id_company = '';
					$nm_company = '';
					$id_div = '';
					$nm_div = '';

					if ($get_po->tipe == 'pr depart') {
						$get_detail_po = $this->db->get_where('dt_trans_po', ['no_po' => $get_po->no_po])->row();

						$this->db->select('a.*');
						$this->db->from('rutin_non_planning_header a');
						$this->db->join('rutin_non_planning_detail b', 'b.no_pengajuan = a.no_pengajuan');
						$this->db->where('b.id', $get_detail_po->idpr);
						$get_pr_header = $this->db->get()->row();

						if (!empty($get_pr_header)) {
							$this->hris->select('a.id as id_comp, a.name as nm_comp');
							$this->hris->from('companies a');
							$this->hris->join('departments b', 'b.company_id = a.id');
							$this->hris->where('b.id', $get_pr_header->id_dept);
							$get_comp = $this->hris->get()->row();

							$this->hris->select('a.id as id_div, a.name as nm_div');
							$this->hris->from('divisions a');
							$this->hris->join('departments b', 'b.division_id = a.id');
							$this->hris->where('b.id', $get_pr_header->id_dept);
							$get_div_obj = $this->hris->get()->row();

							$id_div = (!empty($get_div_obj)) ? $get_div_obj->id_div : '';
							$nm_div = (!empty($get_div_obj)) ? $get_div_obj->nm_div : '';

							if (!empty($get_comp) && in_array($get_comp->id_comp, ['COM003', 'COM012', 'COM006'])) {
								if (!isset($company_cache['4'])) {
									$company_cache['4'] = $this->consultant->get_where('kons_tr_company', ['id' => '4'])->row();
								}
								$get_company = $company_cache['4'];

								$id_company = (!empty($get_company)) ? $get_company->id : '';
								$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
							}
						}
					}

					if ($get_top_po->group_top == '75' || $get_top_po->group_top == '76') {
						$arr_coa_jurnal = ['2010-10-0', '7010-20-5'];
						if (!empty($coa_bank)) {
							$arr_coa_jurnal[] = $coa_bank;
						}

						$get_coa_jurnal = $get_coa_list($arr_coa_jurnal);

						$no_jurnal = 0;
						foreach ($get_coa_jurnal as $item_coa) {
							$id_coa = $item_coa->no_coa;
							$nm_coa = $item_coa->nm_coa;
							$debit = 0;
							$kredit = 0;
							$keterangan = $nm_coa . ' - ' . $item_payment->id;

							if ($item_coa->no_coa == '2010-10-0') {
								$no_jurnal++;
								$debit = $item_payment->jumlah;
								$hasil_jurnal .= $generate_tr($no_jurnal, null, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_div, $nm_div, $id_coa, $nm_coa, $keterangan, $debit, $kredit, 'id_div', 'id_coa');
							} elseif ($item_coa->no_coa == '7010-20-5' && $bank_charge > 0) {
								$no_jurnal++;
								$debit = $bank_charge;
								$hasil_jurnal .= $generate_tr($no_jurnal, null, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_div, $nm_div, $id_coa, $nm_coa, $keterangan, $debit, $kredit, 'id_div', 'id_coa');
							} elseif ($item_coa->no_coa == $coa_bank && $bank_charge > 0) {
								$no_jurnal++;
								$kredit = $bank_charge;
								$hasil_jurnal .= $generate_tr($no_jurnal, null, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_div, $nm_div, $id_coa, $nm_coa, $keterangan, $debit, $kredit, 'id_div', 'id_coa');
							} elseif ($item_coa->no_coa == $coa_bank && $payment_bank > 0) {
								$no_jurnal++;
								$kredit = $total_payment;
								$hasil_jurnal .= $generate_tr($no_jurnal, null, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_div, $nm_div, $id_coa, $nm_coa, $keterangan, $debit, $kredit, 'id_div', 'id_coa');
							}
						}
					}
				} else {
					if (!empty($get_expense->no_expense_consultant)) {
						$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_header', ['id' => $get_expense->id_kasbon])->row();
						$get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_kasbon->id_penawaran])->row();
						$get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_kasbon->id_spk_penawaran])->row();

						$get_company = (!empty($get_penawaran->company)) ?
							$this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row() :
							$this->consultant->get_where('kons_tr_company', ['id' => $get_spk_penawaran->id_company])->row();

						$id_company = $get_company->id ?? '';
						$nm_company = $get_company->nm_company ?? '';

						$get_department = $this->hris->select('a.id as id_depart, a.name as nm_depart')
							->from('divisions a')
							->where('a.id', $get_spk_penawaran->id_divisi)
							->get()->row();

						$id_department = $get_department->id_depart ?? '';
						$nm_department = $get_department->nm_depart ?? '';

						$arr_coa_jurnal = ['9999-99-99', '7201-01-04', '1106-01-06', $coa_pph];
						if (!empty($coa_bank)) $arr_coa_jurnal[] = $coa_bank;

						$get_coa_jurnal = $get_coa_list($arr_coa_jurnal);

						$no_jurnal = 0;
						foreach ($get_coa_jurnal as $item_coa) {
							$no_jurnal++;
							$debit = 0;
							$kredit = 0;
							$keterangan = $item_coa->nm_coa . ' - ' . $item_payment->no_doc;

							if ($item_coa->no_coa == '9999-99-99') {
								$debit = $item_payment->jumlah;
							} elseif ($item_coa->no_coa == '7201-01-04') {
								$debit = $bank_charge;
								$keterangan = 'Admin Charge';
							} elseif ($item_coa->no_coa == '1106-01-06') {
								$debit = $nilai_ppn;
								$keterangan = 'PPN';
							} elseif ($item_coa->no_coa == '2104-01-02' || $item_coa->no_coa == '2104-01-03') {
								$kredit = $nilai_pph;
								$keterangan = ($item_coa->no_coa == '2104-01-02') ? 'PPh 21' : 'PPh 23';
							} elseif ($item_coa->no_coa == $coa_bank) {
								$kredit = $total_payment;
							}

							$hasil_jurnal .= $generate_tr($no_jurnal, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_department, $nm_department, $item_coa->no_coa, $item_coa->nm_coa, $keterangan, $debit, $kredit);
						}

						if ($bank_charge > 0 && !empty($coa_bank)) {
							$no_jurnal++;
							$hasil_jurnal .= $generate_tr($no_jurnal, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_department, $nm_department, $coa_bank, $nm_bank, $nm_bank, 0, $bank_charge);
						}
					}
				}
			} else if ($item_payment->tipe == 'direct_payment') {
				$get_direct_payment = $this->db->get_where('tr_direct_payment', ['no_doc' => $item_payment->no_doc])->row();
				$get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_direct_payment->id_penawaran])->row();
				$get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_direct_payment->id_spk_penawaran])->row();

				$id_company = '';
				$nm_company = '';

				if (!empty($get_penawaran->id_company)) {
					$get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran->id_company])->row();
					$id_company = $get_company->id ?? '';
					$nm_company = $get_company->nm_company ?? '';
				} else {
					$get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_spk_penawaran->id_company])->row();
					$id_company = $get_company->id ?? '';
					$nm_company = $get_company->nm_company ?? '';
				}

				$get_divisi = $this->hris->get_where('divisions', ['id' => $get_penawaran->id_divisi])->row();
				$id_divisi = $get_divisi->id ?? '';
				$nm_divisi = $get_divisi->name ?? '';

				$get_kasbon_cons = $this->consultant->get_where('kons_tr_kasbon_project_header', ['id' => $get_direct_payment->ids])->row();

				$get_kasbon_cons_detail = [];
				if ($get_kasbon_cons) {
					if ($get_kasbon_cons->tipe == '1') {
						$get_kasbon_cons_detail = $this->consultant->select('a.*, a.nm_aktifitas as nm_biaya')->from('kons_tr_kasbon_project_subcont a')->where('a.id_header', $get_direct_payment->ids)->get()->result();
					} else if ($get_kasbon_cons->tipe == '2') {
						$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')->from('kons_tr_kasbon_project_akomodasi a')->join('kons_master_biaya b', 'b.id = a.id_item', 'left')->where('a.id_header', $get_direct_payment->ids)->get()->result();
					} else if ($get_kasbon_cons->tipe == '3') {
						$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')->from('kons_tr_kasbon_project_others a')->join('kons_master_biaya b', 'b.id = a.id_item', 'left')->where('a.id_header', $get_direct_payment->ids)->get()->result();
					} else if ($get_kasbon_cons->tipe == '4') {
						$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')->from('kons_tr_kasbon_project_lab a')->join('kons_master_lab b', 'b.id = a.id_item', 'left')->where('a.id_header', $get_direct_payment->ids)->get()->result();
					} else if ($get_kasbon_cons->tipe == '5') {
						$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')->from('kons_tr_kasbon_project_subcont_tenaga_ahli a')->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left')->where('a.id_header', $get_direct_payment->ids)->get()->result();
					} else {
						$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')->from('kons_tr_kasbon_project_subcont_perusahaan a')->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left')->where('a.id_header', $get_direct_payment->ids)->get()->result();
					}
				}

				$no_jurnal = 1;
				foreach ($get_kasbon_cons_detail as $item_detail) :
					$debit = $item_detail->total_pengajuan ?? 0;
					$kredit = 0;
					$no_coa = $item_detail->no_coa ?? '5101-01-03';
					$nm_coa = $item_detail->nm_coa ?? 'Biaya Pengeluaran Lainnya';
					$nm_biaya = $item_detail->nm_biaya ?? '';

					$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $no_coa, $nm_coa, $nm_biaya, $debit, $kredit);
				endforeach;

				$arr_coa_jurnal = [$coa_pph, '7201-01-04', '1106-01-06'];
				if (!empty($coa_bank)) $arr_coa_jurnal[] = $coa_bank;

				$get_coa_jurnal = $get_coa_list($arr_coa_jurnal);

				foreach ($get_coa_jurnal as $item_coa) :
					$no_coa = $item_coa->no_coa;
					$nm_coa = $item_coa->nm_coa;
					$debit = 0;
					$kredit = 0;
					$keterangan = $item_coa->nm_coa . ' - ' . $item_payment->no_doc;

					if ($item_coa->no_coa == '2104-01-02' || $item_coa->no_coa == '2104-01-03') {
						$kredit = $nilai_pph;
						$keterangan = ($item_coa->no_coa == '2104-01-02') ? 'PPh 21' : 'PPh 23';
					} elseif ($item_coa->no_coa == '7201-01-04') {
						$kredit = $bank_charge;
						$keterangan = 'Admin Charge';
					} elseif ($item_coa->no_coa == '1106-01-06') {
						$kredit = $nilai_ppn;
						$keterangan = 'PPN';
					} elseif ($item_coa->no_coa == $coa_bank) {
						$kredit = $total_payment;
					}

					$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $no_coa, $nm_coa, $keterangan, $debit, $kredit);
				endforeach;

				if ($bank_charge > 0 && !empty($coa_bank)) {
					$hasil_jurnal .= $generate_tr($no_jurnal++, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $coa_bank, $nm_bank, $nm_bank, 0, $bank_charge);
				}
			} else if ($item_payment->tipe == 'petty_cash_hutang') {
				$get_petty_cash = $this->db->get_where('tr_petty_cash_vuca_sustain', ['no_payment_hutang' => $item_payment->no_doc])->row();

				if (!empty($get_petty_cash)) {
					$id_company = '';
					$nm_company = $get_petty_cash->company;
					$id_divisi = '';
					$nm_divisi = '';

					$get_company = $this->hris->get_where('companies', ['name' => $nm_company])->row();
					if (!empty($get_company)) {
						$id_company = $get_company->id;
					}

					$get_stm = $this->hris->get_where('companies', ['name' => 'STM'])->row();
					$id_company_stm = !empty($get_stm) ? $get_stm->id : '';

					$no_jurnal = 0;
					$jumlah = $item_payment->jumlah;

					// Ambil detail expenses dari pencatatan petty cash
					$this->db->select('d.coa_code, d.pengeluaran, d.total, c.nama as coa_nama');
					$this->db->from('tr_pelaporan_petty_cash_detail pd');
					$this->db->join('tr_expense_petty_cash_detail d', 'd.pencatatan_id = pd.pencatatan_id');
					$this->db->join(DBACC . '.coa_master c', 'c.no_perkiraan = d.coa_code', 'left');
					$this->db->where('pd.pelaporan_id', $get_petty_cash->pelaporan_id);
					$expense_details = $this->db->get()->result();

					// 1. Jurnal Expense (Debit)
					foreach ($expense_details as $detail) {
						$no_jurnal++;
						$hasil_jurnal .= $generate_tr($no_jurnal, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, $detail->coa_code, $detail->coa_nama, $detail->pengeluaran, $detail->total, 0);
					}

					// 2. Jurnal Kas Kecil (Kredit)
					// Kas Kecil COA is 1101-01-02
					$no_jurnal++;
					$hasil_jurnal .= $generate_tr($no_jurnal, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, '1101-01-02', 'Kas Kecil', 'Kas Kecil', 0, $jumlah);

					// Note: Bank Charge and PPN/PPh usually don't apply to petty cash expense journals, but we leave the logic if they are entered in the UI.
					if ($bank_charge > 0) {
						$no_jurnal++;
						$hasil_jurnal .= $generate_tr($no_jurnal, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, '7201-01-04', 'Admin Charge', 'Admin Charge', $bank_charge, 0);
					}
					if ($nilai_ppn > 0) {
						$no_jurnal++;
						$hasil_jurnal .= $generate_tr($no_jurnal, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, '1106-01-06', 'PPN', 'PPN', $nilai_ppn, 0);
					}
					if ($nilai_pph > 0) {
						$no_jurnal++;
						$hasil_jurnal .= $generate_tr($no_jurnal, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company, $nm_company, $id_divisi, $nm_divisi, '2104-01-02', 'PPh', 'PPh', 0, $nilai_pph);
					}

					// Since the refill part generates the Bank credit in a separate table, we don't need to credit the Bank in this table.
					// But if coa_bank is somehow processed, we shouldn't credit it here to avoid double crediting (it's credited in Jurnal Refill).
					// Actually, wait, the Jurnal table requires balancing. 
					// Expense (Debit) = 500k
					// Kas Kecil (Kredit) = 500k. It's balanced!

					// 3. Refill STM (Jurnal Refill Petty Cash)
					// Company for Refill is always STM
					$get_stm = $this->hris->get_where('companies', ['name' => 'STM'])->row();
					$id_company_stm = !empty($get_stm) ? $get_stm->id : '';

					$no_jurnal_refill = 0;
					$no_jurnal_refill++;
					$hasil_jurnal_refill .= $generate_tr_refill($no_jurnal_refill, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company_stm, 'STM', $id_divisi, $nm_divisi, '1101-01-02', 'Kas Kecil', 'Refill Kas Kecil', $jumlah, 0);
					$ttl_debit_refill += $jumlah;
					$no_jurnal_refill++;
					$hasil_jurnal_refill .= $generate_tr_refill($no_jurnal_refill, $item_payment->id, $tgl_bayar_display, $tgl_bayar_value, $id_company_stm, 'STM', $id_divisi, $nm_divisi, (!empty($coa_bank) ? $coa_bank : '1101-02-09'), (!empty($nm_bank) ? $nm_bank : 'Bank STM'), (!empty($nm_bank) ? $nm_bank : 'Bank STM'), 0, $jumlah);
					$ttl_kredit_refill += $jumlah;
				}
			} else {
				$get_non_po = $this->db->get_where('tr_pr_non_po', ['no_non_po' => $item_payment->no_doc])->row();

				if (!empty($get_non_po)) {
					$arr_coa_jurnal = ['1103-01-04', '7201-01-04'];
					if (!empty($coa_bank)) {
						array_push($arr_coa_jurnal, $coa_bank);
					}
					// Note: the original script does not loop and generate HTML for this case. We leave it empty as original.
				}
			}

			$no++;
		endforeach;

		$response = [
			'hasil_jurnal' => $hasil_jurnal,
			'hasil_jurnal_refill' => $hasil_jurnal_refill,
			'ttl_debit' => $ttl_debit,
			'ttl_kredit' => $ttl_kredit,
			'ttl_debit_refill' => $ttl_debit_refill,
			'ttl_kredit_refill' => $ttl_kredit_refill,
			'is_all_petty_cash_hutang' => $is_all_petty_cash_hutang
		];

		echo json_encode($response);
	}

	public function generate_id_invoice_jurnal($nomor)
	{
		$Ym             = date('ym');
		$srcMtr            = "SELECT MAX(no_jurnal) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
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
// 
