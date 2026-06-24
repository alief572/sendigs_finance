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
				'currency' => $item->currency,
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
		$payment_bank = str_replace(',', '', $post['payment_bank']);
		$bank_charge = str_replace(',', '', $post['bank_charge']);
		$bank = $post['bank'];

		// Ambil tgl_bayar dari input user, fallback ke hari ini jika kosong
		$tgl_bayar = !empty($post['tgl_bayar']) ? $post['tgl_bayar'] : date('Y-m-d');
		$tgl_bayar_display = date('d F Y', strtotime($tgl_bayar));
		$tgl_bayar_value = date('Y-m-d', strtotime($tgl_bayar));

		$hasil_jurnal = '';
		$ttl_debit = 0;
		$ttl_kredit = 0;

		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->where_in('a.id', explode(',', $id_payment));
		$get_payment = $this->db->get()->result();

		// Cek apakah baris kredit bank charge perlu ditampilkan di jurnal (dari toggle user)
		$include_bank_charge_jurnal = !empty($post['include_bank_charge_jurnal']) && $post['include_bank_charge_jurnal'] == '1';

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

		$no = 1;
		foreach ($get_payment as $item_payment) :

			if ($item_payment->tipe == 'kasbon') {
				$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();

				$id_divisi = '';
				$nm_divisi = '';
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

					$this->hris->select('a.*, b.id as id_department, b.name as nm_department');
					$this->hris->from('employees a');
					$this->hris->join('departments b', 'b.id = a.department_id', 'left');
					$this->hris->where('a.id', $get_pengajuan_konsultan->employee_id);
					$get_department = $this->hris->get()->row();

					$id_divisi = $get_department->id_department ?? '';
					$nm_divisi = $get_department->nm_department ?? '';
				} else {

					$this->db->select('a.department_id');
					$this->db->from('users a');
					$this->db->where('a.username', $get_kasbon->created_by);
					$get_user = $this->db->get()->row();

					$this->hris->select('a.id as id_department, a.name as nm_department');
					$this->hris->from('departments a');
					$this->hris->where('a.id', $get_user->department_id);
					$get_department = $this->hris->get()->row();

					$id_divisi = $get_department->id_department ?? '';
					$nm_divisi = $get_department->nm_department ?? '';
				}

				// Optimization: get user title outside the inner loop
				$get_kasbon_user_title = null;
				if (!empty($get_kasbon)) {
					$this->db->select('b.title_id');
					$this->db->from('tr_kasbon a');
					$this->db->join('users b', 'b.nm_lengkap = a.created_by');
					$this->db->where('a.no_doc', $item_payment->no_doc);
					$get_kasbon_user_title = $this->db->get()->row();
				}

				// Optimization: get company details outside the inner loop
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

				$arr_coa_jurnal = ['1103-01-14', '7201-01-04', '1106-01-06', '2104-01-02'];
				if (!empty($coa_bank)) {
					array_push($arr_coa_jurnal, $coa_bank);
				}

				$get_coa_jurnal = $get_coa_list($arr_coa_jurnal);

				$no_jurnal = 1;
				foreach ($get_coa_jurnal as $item_coa) :
					$debit = 0;
					$kredit = 0;

					if ($item_coa->no_coa == '1103-01-14') {
						$debit = $item_payment->jumlah;

						$hasil_jurnal .= '<tr>';
						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $tgl_bayar_display;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
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
					}

					if ($item_coa->no_coa == '7201-01-04') {
						$debit = $bank_charge;

						$hasil_jurnal .= '<tr>';
						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $tgl_bayar_display;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
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
					}

					if ($item_coa->no_coa == '1106-01-06') {
						$debit = $nilai_ppn;

						$hasil_jurnal .= '<tr>';
						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $tgl_bayar_display;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
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
					}

					if ($item_coa->no_coa == '2104-01-02') {
						$kredit = $nilai_pph;

						$hasil_jurnal .= '<tr>';
						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $tgl_bayar_display;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
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
					}

					if (!empty($coa_bank) && $coa_bank == $item_coa->no_coa) {
						$kredit = $payment_bank;
						// if ($get_kasbon && $payment_bank > $get_kasbon->jumlah_kasbon) {
						// 	$kredit = $get_kasbon->jumlah_kasbon;
						// }

						$hasil_jurnal .= '<tr>';
						$hasil_jurnal .= '<td class="text-center">';
						$hasil_jurnal .= $tgl_bayar_display;
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
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

						// Baris tambahan Bank untuk Bank Charge (kredit)
						if ($bank_charge > 0 && $include_bank_charge_jurnal) {
							$hasil_jurnal .= '<tr>';
							$hasil_jurnal .= '<td class="text-center">';
							$hasil_jurnal .= $tgl_bayar_display;
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
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
							$hasil_jurnal .= number_format(0);
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
							$hasil_jurnal .= '</td>';
							$hasil_jurnal .= '<td class="text-right">';
							$hasil_jurnal .= number_format($bank_charge);
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $bank_charge . '">';
							$hasil_jurnal .= '</td>';
							$hasil_jurnal .= '</tr>';

							$ttl_kredit += $bank_charge;
							$no_jurnal++;
						}
					}

				endforeach;
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

				// Optimization: Get transport title outside inner loop
				$get_transport_title = null;
				if (!empty($item_payment->no_doc)) {
					$this->db->select('a.title_id');
					$this->db->from('users a');
					$this->db->join('tr_transport_req b', 'b.created_by = a.nm_lengkap');
					$this->db->where('b.no_doc', $item_payment->no_doc);
					$get_transport_title = $this->db->get()->row();
				}

				// Optimization: Get company details outside inner loop
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

				// Optimization: Get titles/divisi outside inner loop (cached)
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

					if ($item_coa->no_coa == $coa_transport) {
						$debit = $item_payment->jumlah;
						$kredit = 0;
					}
					if (!empty($coa_bank) && $item_coa->no_coa == $coa_bank) {
						$kredit = $payment_bank;
						$debit = 0;
					}
					if ($item_coa->no_coa == '1106-01-06') {
						$debit = $nilai_ppn;
						$kredit = 0;
					}
					if ($item_coa->no_coa == '7201-01-04') {
						$debit = $bank_charge;
						$kredit = 0;
					}
					if ($item_coa->no_coa == '1106-01-01') {
						$kredit = $nilai_pph;
						$debit = 0;
					}

					if ($debit == '') {
						$debit = 0;
					}
					if ($kredit == '') {
						$kredit = 0;
					}

					$hasil_jurnal .= '<tr>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $tgl_bayar_display;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_company;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
					$hasil_jurnal .= '</td>';

					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_department;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_divisi]" value="' . $id_department . '">';
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_department . '">';
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
				}
			} else if ($item_payment->tipe == 'expense') {
				$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_payment->no_doc])->row();

				if (!empty($get_expense->exp_inv_po)) {

					$get_inv_po = $this->db->get_where('tr_invoice_po', ['id' => $get_expense->no_doc])->row();
					$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $get_inv_po->no_po])->row();
					$get_top_po = $this->db->get_where('tr_top_po', ['id' => $get_inv_po->id_top])->row();

					if ($get_po->tipe == 'pr depart') {
						$get_detail_po = $this->db->get_where('dt_trans_po', ['no_po' => $get_po->no_po])->row();

						$this->db->select('a.*');
						$this->db->from('rutin_non_planning_header a');
						$this->db->join('rutin_non_planning_detail b', 'b.no_pengajuan = a.no_pengajuan');
						$this->db->where('b.id', $get_detail_po->idpr);
						$get_pr_header = $this->db->get()->row();

						$this->hris->select('a.id as id_comp, a.name as nm_comp');
						$this->hris->from('companies a');
						$this->hris->join('departments b', 'b.company_id = a.id');
						$this->hris->where('b.id', $get_pr_header->id_dept);
						$get_comp = $this->hris->get()->row();

						$this->hris->select('a.id as id_div, a.name as nm_div');
						$this->hris->from('divisions a');
						$this->hris->join('departments b', 'b.division_id = a.id');
						$this->hris->where('b.id', $get_pr_header->id_dept);
						$get_div = $this->hris->get()->row();

						$id_div = (!empty($get_div)) ? $get_div->id_div : '';
						$nm_div = (!empty($get_div)) ? $get_div->nm_div : '';

						if ($get_comp->id_comp == 'COM003' || $get_comp->id_comp == 'COM012') {
							if (!isset($company_cache['4'])) {
								$company_cache['4'] = $this->consultant->get_where('kons_tr_company', ['id' => '4'])->row();
							}
							$get_company = $company_cache['4'];

							$id_company = (!empty($get_company)) ? $get_company->id : '';
							$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
						}
						if ($get_comp->id_comp == 'COM006') {
							if (!isset($company_cache['4'])) {
								$company_cache['4'] = $this->consultant->get_where('kons_tr_company', ['id' => '4'])->row();
							}
							$get_company = $company_cache['4'];

							$id_company = (!empty($get_company)) ? $get_company->id : '';
							$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
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
							if ($item_coa->no_coa == '2010-10-0') {
								$no_jurnal++;
								$debit = $item_payment->jumlah;
								$kredit = 0;

								$keterangan = $nm_coa . ' - ' . $item_payment->id;

								$hasil_jurnal .= '<tr>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $tgl_bayar_display;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_company;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_div;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_div]" value="' . $id_div . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_div]" value="' . $nm_div . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $id_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_coa]" value="' . $id_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $keterangan;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
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
							}
							if ($item_coa->no_coa == '7010-20-5' && $bank_charge > 0) {
								$no_jurnal++;
								$kredit = 0;
								$debit = $bank_charge;

								$keterangan = $nm_coa . ' - ' . $item_payment->id;

								$hasil_jurnal .= '<tr>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $tgl_bayar_display;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_company;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_div;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_div]" value="' . $id_div . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_div]" value="' . $nm_div . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $id_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_coa]" value="' . $id_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $keterangan;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
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
							}
							if ($item_coa->no_coa == $coa_bank && $bank_charge > 0) {
								$no_jurnal++;
								$kredit = $bank_charge;
								$debit = 0;

								$keterangan = $nm_coa . ' - ' . $item_payment->id;

								$hasil_jurnal .= '<tr>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $tgl_bayar_display;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_company;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_div;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_div]" value="' . $id_div . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_div]" value="' . $nm_div . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $id_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_coa]" value="' . $id_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $keterangan;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
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
							}
							if ($item_coa->no_coa == $coa_bank && $payment_bank > 0) {
								$no_jurnal++;
								$kredit = ($payment_bank + $bank_charge);
								$debit = 0;

								$keterangan = $nm_coa . ' - ' . $item_payment->id;

								$hasil_jurnal .= '<tr>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $tgl_bayar_display;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_company;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_div;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_div]" value="' . $id_div . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_div]" value="' . $nm_div . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $id_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_coa]" value="' . $id_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
								$hasil_jurnal .= '</td>';

								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $keterangan;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
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
							}
						}
					}
				} else {
					if (!empty($get_expense->no_expense_consultant)) {

						$get_kasbon = $this->consultant->get_where('kons_tr_kasbon_project_header', ['id' => $get_expense->id_kasbon])->row();


						$get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $get_kasbon->id_penawaran])->row();

						$get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $get_kasbon->id_spk_penawaran])->row();

						if (!empty($get_penawaran->company)) {
							$get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row();
						} else {
							$get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_spk_penawaran->id_company])->row();
						}

						$id_company = $get_company->id ?? '';
						$nm_company = $get_company->nm_company ?? '';

						$get_department = $this->hris->select('a.id as id_depart, a.name as nm_depart')
							->from('divisions a')
							->where('a.id', $get_spk_penawaran->id_divisi)
							->get()
							->row();

						$id_department = $get_department->id_depart ?? '';
						$nm_department = $get_department->nm_depart ?? '';

						$arr_coa = ['9999-99-99', '7201-01-04', '1106-01-06', '2104-01-02', $coa_bank];

						$get_coa_jurnal = $this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa')
							->from('coa_master a')
							->where_in('a.no_perkiraan', $arr_coa)
							->get()
							->result();

						$no_jurnal = 0;
						foreach ($get_coa_jurnal as $item_coa) {
							$no_jurnal++;

							if ($item_coa->no_coa == '9999-99-99') {
								$debit = $item_payment->jumlah;
								$kredit = 0;
							} elseif ($item_coa->no_coa == '7201-01-04') {
								$debit = $bank_charge;
								$kredit = 0;
							} elseif ($item_coa->no_coa == '1106-01-06') {
								$debit = $nilai_ppn;
								$kredit = 0;
							} elseif ($item_coa->no_coa == '2104-01-02') {
								$debit = 0;
								$kredit = $nilai_pph;
							} elseif ($item_coa->no_coa == $coa_bank) {
								$kredit = $item_payment->jumlah;
								$debit = 0;
							} else {
								$debit = 0;
								$kredit = 0;
							}

							$hasil_jurnal .= '<tr>';

							$hasil_jurnal .= '<td class="text-center">';
							$hasil_jurnal .= $tgl_bayar_display;
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
							$hasil_jurnal .= '</td>';

							$hasil_jurnal .= '<td class="text-center">';
							$hasil_jurnal .= $nm_company;
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
							$hasil_jurnal .= '</td>';

							$hasil_jurnal .= '<td class="text-center">';
							$hasil_jurnal .= $nm_department;
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_divisi]" value="' . $id_department . '">';
							$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_department . '">';
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
						}

						// Baris tambahan Bank untuk Bank Charge (kredit) - expense
						if ($bank_charge > 0 && !empty($coa_bank) && $include_bank_charge_jurnal) {
							$get_coa_bank_info = $this->accounting->select('no_perkiraan as no_coa, nama as nm_coa')
								->from('coa_master')
								->where('no_perkiraan', $coa_bank)
								->get()
								->row();

							if (!empty($get_coa_bank_info)) {
								$hasil_jurnal .= '<tr>';
								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $tgl_bayar_display;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_company;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $nm_department;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_divisi]" value="' . $id_department . '">';
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_divisi]" value="' . $nm_department . '">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $get_coa_bank_info->no_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $get_coa_bank_info->no_coa . '">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $get_coa_bank_info->nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $get_coa_bank_info->nm_coa . '">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '<td class="text-center">';
								$hasil_jurnal .= $get_coa_bank_info->nm_coa;
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $get_coa_bank_info->nm_coa . '">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '<td class="text-right">';
								$hasil_jurnal .= number_format(0);
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '<td class="text-right">';
								$hasil_jurnal .= number_format($bank_charge);
								$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $bank_charge . '">';
								$hasil_jurnal .= '</td>';
								$hasil_jurnal .= '</tr>';

								$ttl_kredit += $bank_charge;
								$no_jurnal++;
							}
						}

						if (!empty($get_kasbon->tipe)) {



							// if ($get_kasbon->tipe == '1') {
							// 	$get_kasbon_detail = $this->consultant->get_where('kons_tr_kasbon_project_subcont', ['id_header' => $get_kasbon->id])->result();
							// } elseif ($get_kasbon->tipe == '2') {
							// 	$get_kasbon_detail = $this->consultant->get_where('kons_tr_kasbon_project_akomodasi', ['id_header' => $get_kasbon->id])->result();
							// } elseif ($get_kasbon->tipe == '3') {
							// 	$get_kasbon_detail = $this->consultant->select('a.*')
							// 		->from('kons_tr_kasbon_project_others a')
							// 		->join('kons_master_biaya b', 'b.id = a.id_item', 'left')
							// 		->where('a.id', $get_expense->id_kasbon)
							// 		->get()
							// 		->result();

							// 	foreach ($get_kasbon_detail as $item_detail) {

							// 	}
							// } elseif ($get_kasbon->tipe == '4') {
							// 	$get_kasbon_detail = $this->consultant->get_where('kons_tr_kasbon_project_subcont', ['id_header' => $get_kasbon->id])->result();
							// } elseif ($get_kasbon->tipe == '5') {
							// } else {
							// }
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

				// $hasil_jurnal = '';

				$ttl_debit = 0;
				$ttl_kredit = 0;

				$get_kasbon_cons = $this->consultant->get_where('kons_tr_kasbon_project_header', ['id' => $get_direct_payment->ids])->row();

				$no_jurnal = 0;



				if ($get_kasbon_cons->tipe == '1') {
					$get_kasbon_cons_detail = $this->consultant->select('a.*, a.nm_aktifitas as nm_biaya')
						->from('kons_tr_kasbon_project_subcont a')
						->where('a.id_header', $get_direct_payment->ids)
						->get()
						->result();
				} else if ($get_kasbon_cons->tipe == '2') {
					$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')
						->from('kons_tr_kasbon_project_akomodasi a')
						->join('kons_master_biaya b', 'b.id = a.id_item', 'left')
						->where('a.id_header', $get_direct_payment->ids)
						->get()
						->result();
				} else if ($get_kasbon_cons->tipe == '3') {
					$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')
						->from('kons_tr_kasbon_project_others a')
						->join('kons_master_biaya b', 'b.id = a.id_item', 'left')
						->where('a.id_header', $get_direct_payment->ids)
						->get()
						->result();
				} else if ($get_kasbon_cons->tipe == '4') {
					$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')
						->from('kons_tr_kasbon_project_lab a')
						->join('kons_master_lab b', 'b.id = a.id_item', 'left')
						->where('a.id_header', $get_direct_payment->ids)
						->get()
						->result();
				} else if ($get_kasbon_cons->tipe == '5') {
					$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')
						->from('kons_tr_kasbon_project_subcont_tenaga_ahli a')
						->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left')
						->where('a.id_header', $get_direct_payment->ids)
						->get()
						->result();
				} else {
					$get_kasbon_cons_detail = $this->consultant->select('a.*, b.nm_biaya as nm_biaya, b.no_coa, b.nm_coa')
						->from('kons_tr_kasbon_project_subcont_perusahaan a')
						->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left')
						->where('a.id_header', $get_direct_payment->ids)
						->get()
						->result();
				}



				$no_jurnal = 1;
				foreach ($get_kasbon_cons_detail as $item_detail) :

					$debit = $item_detail->total_pengajuan ?? 0;
					$kredit = 0;

					$no_coa = $item_detail->no_coa ?? '5101-01-03';
					$nm_coa = $item_detail->nm_coa ?? 'Biaya Pengeluaran Lainnya';

					$nm_biaya = $item_detail->nm_biaya ?? '';

					$hasil_jurnal .= '<tr>';
					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $tgl_bayar_display;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
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
					$hasil_jurnal .= $no_coa;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $no_coa . '">';
					$hasil_jurnal .= '</td>';
					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_coa;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
					$hasil_jurnal .= '</td>';
					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_biaya;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $nm_biaya . '">';
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
				endforeach;

				$arr_coa = ['2104-01-02', '7201-01-04', '1106-01-06', $coa_bank];

				$get_coa = $this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa')
					->from('coa_master a')
					->where_in('a.no_perkiraan', $arr_coa)
					->get()
					->result();

				foreach ($get_coa as $item_coa) :
					$no_jurnal++;

					$no_coa = $item_coa->no_coa;
					$nm_coa = $item_coa->nm_coa;

					$debit = 0;
					$kredit = 0;
					if ($item_coa->no_coa == '2104-01-02') :
						$kredit = $nilai_pph;
						$debit = 0;
					elseif ($item_coa->no_coa == '7201-01-04') :
						$kredit = $bank_charge;
						$debit = 0;
					elseif ($item_coa->no_coa == '1106-01-06') :
						$debit = $nilai_ppn;
						$kredit = 0;
					elseif ($item_coa->no_coa == $coa_bank) :
						$kredit = $payment_bank;
						$debit = 0;
					endif;

					$hasil_jurnal .= '<tr>';
					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $tgl_bayar_display;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tgl_bayar_value . '">';
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][id_payment_ref]" value="' . $item_payment->id . '">';
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
					$hasil_jurnal .= $no_coa;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $no_coa . '">';
					$hasil_jurnal .= '</td>';
					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_coa;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
					$hasil_jurnal .= '</td>';
					$hasil_jurnal .= '<td class="text-center">';
					$hasil_jurnal .= $nm_coa;
					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $nm_coa . '">';
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
				endforeach;
			} else {
				$get_non_po = $this->db->get_where('tr_pr_non_po', ['no_non_po' => $item_payment->no_doc])->row();

				if (!empty($get_non_po)) {
					$arr_coa_jurnal = ['1103-01-04', '7201-01-04'];
					if (!empty($coa_bank)) {
						array_push($arr_coa_jurnal, $coa_bank);
					}
				}
			}

			$no++;
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
