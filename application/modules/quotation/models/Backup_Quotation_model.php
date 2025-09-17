<?php
class Quotation_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function modal_detail_invoice($no_penawaran = null)
	{
		$session = $this->session->userdata('app_session');

		$Cust = $this->db->query("SELECT a.* FROM customer a")->result();
		$User = $this->db->query("SELECT a.* FROM users a")->result();
		$pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

		$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

		$get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

		if ($no_penawaran !== null) {
			$get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
			$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

			$get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();
			
			$get_other_item = $this->db->query("
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

		$get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

			$this->template->set('results', [
				'customers' => $Cust,
				'user' => $User,
				'pic_cust' => $pic_cust,
				'nm_sales' => $session['nm_lengkap'],
				'data_penawaran' => $get_penawaran,
				'data_penawaran_detail' => $get_penawaran_detail,
				'list_top' => $get_top,
				'curr' => $get_penawaran->currency,
				'list_other_cost' => $get_other_cost,
				'list_other_item' => $get_other_item,
				'list_another_item' => $get_list_item_others
			]);
		} else {
			$this->template->set('results', [
				'customers' => $Cust,
				'user' => $User,
				'pic_cust' => $pic_cust,
				'list_penawaran_detail' => $get_penawaran_detail,
				'nm_sales' => $session['nm_lengkap'],
				'list_top' => $get_top
			]);
		}
		$this->template->render('create_quotation');
	}

	public function modal_add_invoice($curr)
	{
		$session = $this->session->userdata('app_session');

		$Cust = $this->db->query("SELECT a.* FROM customer a")->result();
		$User = $this->db->query("SELECT a.* FROM users a")->result();
		$pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

		$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user'], 'curr' => $curr])->result();

		$get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $session['id_user'], 'curr' => $curr])->result();

		$get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

		$get_other_item = $this->db->query("
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

		$get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $this->auth->user_id()])->result();

		$this->template->set('results', [
			'customers' => $Cust,
			'user' => $User,
			'pic_cust' => $pic_cust,
			'list_penawaran_detail' => $get_penawaran_detail,
			'nm_sales' => $session['nm_lengkap'],
			'list_top' => $get_top,
			'curr' => $curr,
			'list_other_cost' => $get_other_cost,
			'list_other_item' => $get_other_item,
			'list_another_item' => $get_list_item_others
		]);
		$this->template->render('create_quotation_wcurr');
	}

	public function approval_quotation($no_penawaran)
	{
		$session = $this->session->userdata('app_session');

		$Cust = $this->db->query("SELECT a.* FROM customer a")->result();
		$User = $this->db->query("SELECT a.* FROM users a")->result();
		$pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

		$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

		if ($no_penawaran !== null) {
			$get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
			$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

			$this->template->set('results', [
				'customers' => $Cust,
				'user' => $User,
				'pic_cust' => $pic_cust,
				'nm_sales' => $session['nm_lengkap'],
				'data_penawaran' => $get_penawaran,
				'data_penawaran_detail' => $get_penawaran_detail
			]);
		} else {
			$this->template->set('results', [
				'customers' => $Cust,
				'user' => $User,
				'pic_cust' => $pic_cust,
				'list_penawaran_detail' => $get_penawaran_detail,
				'nm_sales' => $session['nm_lengkap']
			]);
		}
		$this->template->render('approval_quotation');
	}

	public function view_quotation($no_penawaran)
	{
		$session = $this->session->userdata('app_session');

		$Cust = $this->db->query("SELECT a.* FROM customer a")->result();
		$User = $this->db->query("SELECT a.* FROM users a")->result();
		$pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

		$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();
		$get_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

		if ($no_penawaran !== null) {
			$get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
			$get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

			$get_other_cost = $this->db->get_where('tr_penawaran_other_cost', ['id_penawaran' => $no_penawaran])->result();
			
			$get_other_item = $this->db->query("
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

		$get_list_item_others = $this->db->get_where('tr_penawaran_other_item', ['id_penawaran' => $no_penawaran])->result();

			$this->template->set('results', [
				'customers' => $Cust,
				'user' => $User,
				'pic_cust' => $pic_cust,
				'nm_sales' => $session['nm_lengkap'],
				'data_penawaran' => $get_penawaran,
				'data_penawaran_detail' => $get_penawaran_detail,
				'list_top' => $get_top,
				'curr' => $get_penawaran->currency,
				'list_other_cost' => $get_other_cost,
				'list_other_item' => $get_other_item,
				'list_another_item' => $get_list_item_others
			]);
		} else {
			$this->template->set('results', [
				'customers' => $Cust,
				'user' => $User,
				'pic_cust' => $pic_cust,
				'list_penawaran_detail' => $get_penawaran_detail,
				'nm_sales' => $session['nm_lengkap'],
				'list_top' => $get_top
			]);
		}
		$this->template->render('view_quotation');
	}


	public function modal_detail_invoice_np($id)
	{

		// $id    = $this->uri->segment(3);
		$getInv = $this->db->query("SELECT * FROM tr_invoice_np_header WHERE no_invoice='$id'")->row();

		$Cust = $this->db->query("SELECT a.id_customer,b.name_customer as nm_customer  FROM tr_invoice_np_header a
											INNER JOIN master_customers b on a.id_customer=b.id_customer GROUP BY a.id_customer")->result();

		$bank1			 = $this->Jurnal_model->get_Coa_Bank_Aja('101');
		$pphpenjualan  	 = $this->Acc_model->combo_pph_penjualan();
		$datacoa  	     = $this->Acc_model->GetCoaCombo();
		$template  	     = $this->Acc_model->GetTemplate();

		// print_r($pphpenjualan);
		// exit;

		// $data = array(
		// 'results' => $getInv,
		// 'no_inv'  => $id,
		// 'datbank' => $bank1,
		// 'pphpenjualan'=> $pphpenjualan,
		// 'template'=> $template

		// );

		// $this->load->view('create_penerimaan', $data);

		$list_top = $this->db->get_where('list_help', ['group_by' => 'top invoice'])->result();

		$this->template->set([
			'results' => $getInv,
			'no_inv'  => $id,
			'datbank' => $bank1,
			'pphpenjualan' => $pphpenjualan,
			'template' => $template,
			'customer' => $Cust,
			'list_top' => $list_top
		]);
		$this->template->render('create_penerimaan_np');
	}

	//SERVER SIDE
	public function get_data_json_inv()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_inv(
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

			$mixedStr = $row['no_ipp'];
			$searchStr = 'NP';
			$searchStr2 = 'OT';

			if (strpos($mixedStr, $searchStr)) {
				$class = 'print1';
			} else if (strpos($mixedStr, $searchStr2)) {
				$class = 'print2';
			} else {
				$class = 'print';
			}

			$edit = 'edit';

			$jenis_invoice = $row['jenis_invoice'];

			if ($jenis_invoice == 'TR-01') {
				$jenis = 'UANG MUKA';
			} elseif ($jenis_invoice == 'TR-02') {
				$jenis = 'PROGRESS';
			}

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['tgl_invoice'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['no_ipp'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['no_invoice'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['so_number'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $jenis . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['nm_customer'] . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['total_invoice'], 2) . "</div>";
			$priX	= "";
			$updX	= "";
			$ApprvX	= "";
			$Edit	= "";
			$Print	= "";
			$Hist	= "";
			$ApprvX2Edit = "";

			if ($row['proses_print'] == '1') {
				$Terima	= "<button class='btn btn-sm btn-success terima' title='Create Penerimaan' data-inv='" . $row['no_invoice'] . "'><i class='fa fa-list'></i></button>";
			}
			$nestedData[]	= "<div align='center'>
									" . $priX . "
									" . $updX . "
									" . $viewX . "
									" . $ApprvX . "
									" . $Hist . "
									" . $ApprvX2Edit . "
									" . $Edit . "
									" . $Print . "
									" . $Jurnal . "
									" . $Terima . "
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

	public function query_data_inv($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "
			SELECT
				a.*
				
			FROM
				tr_invoice a
		    WHERE 1=1
                AND a.proses_print='1'
				AND (
				a.no_invoice LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			
			
			GROUP BY a.nm_customer
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_ipp',
			2 => 'nm_customer'
		);

		$sql .= " ORDER BY a.created_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";
		$data['query'] = $this->db->query($sql);
		return $data;
	}

	//SERVER SIDE 
	public function get_data_json_payment()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_payment(
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
			$mixedStr = $row['kd_pembayaran'];
			$searchStr = 'NP';
			$searchStr2 = 'OT';

			if (strpos($mixedStr, $searchStr)) {
				$class = 'print1';
			} else if (strpos($mixedStr, $searchStr2)) {
				$class = 'print2';
			} else {
				$class = 'print';
			}
			$edit = 'edit';
			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['tgl_pembayaran'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['kd_pembayaran'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['nm_customer'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['keterangan'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['invoiced'] . "</div>";
			$nestedData[]	= "<div align='left'>" . number_format($row['totalinvoiced'], 2) . "</div>";
			$nestedData[]	= "<div align='left'>" . number_format($row['biaya_pph_idr'], 2) . "</div>";
			$nestedData[]	= "<div align='left'>" . number_format($row['biaya_admin_idr'], 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['jumlah_pembayaran_idr'], 2) . "</div>";
			$priX	= "";
			$updX	= "";
			$ApprvX	= "";
			$Edit	= "";
			$Print	= "";
			$Hist	= "";
			$Buktip = "";
			$ApprvX2Edit = "";

			$viewX	= "<button class='btn btn-sm btn-warning detail' title='View' data-id_bq='" . $row['kd_pembayaran'] . "'><i class='fa fa-eye'></i></button>";

			// print_r($row['status_jurnal']);
			// exit;

			$Jurnal	= "";

			if ($row['status_jurnal'] == 0) {
				$Jurnal	= "<button class='btn btn-sm btn-primary jurnal'  title='Approval Jurnal Penerimaan' data-inv='" . $row['kd_pembayaran'] . "'><i class='fa fa-check'></i></button>";
			}


			if ($row['biaya_pph_idr'] > 0 && $row['bukti_potong'] == '') {
				$Buktip = " <button class='btn btn-sm btn-success buktip'  title='Penerimaan Bukti Potong' data-kd_pembayaran='" . $row['kd_pembayaran'] . "'><i class='fa fa-cloud-upload'></i></button>";
			}


			//$Print	= "&nbsp;<a href='".base_url('print_invoice/'.$row['no_invoice'])."' target='_blank' class='btn btn-sm btn-info print' onClick='print()' title='Print Invoice' ><i class='fa fa-print'></i></a>";
			// <button class='btn btn-sm btn-primary' id='detailBQ'  title='Detail BQ' data-id_bq='".$row['id_bq']."'><i class='fa fa-eye'></i></button>
			$nestedData[]	= "<div align='center'>
									" . $priX . "
									" . $updX . "
									" . $viewX . "
									" . $ApprvX . "
									" . $Hist . "
									" . $ApprvX2Edit . "
									" . $Edit . "
									" . $Print . "
									" . $Jurnal . "
									" . $Jurnal1 . "
									" . $Terima . "
									" . $Buktip . "
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

	public function query_data_payment($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$sql = "
			SELECT a.*, c.invoiced, c.totalinvoiced FROM tr_invoice_payment a			
			
			left outer join (
				SELECT kd_pembayaran,
				GROUP_CONCAT(no_invoice SEPARATOR ',') as invoiced,
				sum(total_bayar_idr) as totalinvoiced
				FROM tr_invoice_payment_detail
				GROUP BY kd_pembayaran
			) c on a.kd_pembayaran=c.kd_pembayaran
		    WHERE 1=1
               	AND (
				c.invoiced LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.kd_pembayaran LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
	        )
		";
		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'tgl_pembayaran',
			1 => 'kd_pembayaran',
			2 => 'nm_customer'
		);
		$sql .= " ORDER BY a.created_on DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";
		$data['query'] = $this->db->query($sql);
		return $data;
	}

	function generate_nopn($tgl)
	{
		$arr_tgl = array(
			1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F',
			7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L'
		);
		$bln_now = date('m', strtotime($tgl));
		$kode_bln = '';
		foreach ($arr_tgl as $k => $v) {
			if ($k == $bln_now) {
				$kode_bln = $v;
			}
		}
		$cek = 'PN-' . date('y') . $kode_bln;
		/*$query_cek = $this->db->query("SELECT MAX(no_so) as max_id FROM trans_so_header
      WHERE no_so LIKE '%$cek%'")->num_rows();*/
		$this->db->select("MAX(kd_pembayaran) as max_id");
		$this->db->like('kd_pembayaran', $cek);
		$this->db->from('tr_invoice_payment');
		$query_cek = $this->db->count_all_results();

		if ($query_cek == 0) {
			$kode = 1;
			$next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
			$fin = 'PN-' . date('y') . $kode_bln . $next_kode;
		} else {
			$query = "SELECT MAX(kd_pembayaran) as max_id
        FROM
        tr_invoice_payment WHERE kd_pembayaran LIKE '%$cek%'";
			$q = $this->db->query($query);
			$r = $q->row();


			$query = $this->db->query("SELECT MAX(kd_pembayaran) as max_id
        FROM
        tr_invoice_payment WHERE kd_pembayaran LIKE '%$cek%'");
			$row = $query->row_array();
			$thn = date('T');
			$max_id = $row['max_id'];
			$max_id1 = (int) substr($max_id, -5);
			$kode = $max_id1 + 1;

			$next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
			$fin = 'PN-' . date('y') . $kode_bln . $next_kode;
		}
		return $fin;
	}

	public function get_data($kunci, $tabel)
	{
		if ($kunci != '') {
			$this->db->where($kunci);
			$query = $this->db->get($tabel);
		} else {
			$query = $this->db->get($tabel);
		}
		return $query->result();
	}

	public function get_data_quotation()
	{

		$query =  $this->db->query("SELECT a.*, b.nm_customer FROM tr_penawaran a LEFT JOIN customer b ON b.id_customer = a.id_customer ORDER BY a.created_on DESC");

		return $query->result();
	}

	public function get_data_pn_jurnal()
	{

		$query =  $this->db->query("SELECT a.*, c.invoiced, c.totalinvoiced FROM tr_invoice_payment a	        
        left outer join (
            SELECT kd_pembayaran,
            GROUP_CONCAT(no_surat SEPARATOR ',') as invoiced,
            sum(total_bayar_idr) as totalinvoiced
            FROM view_tr_invoice_payment
            GROUP BY kd_pembayaran
        ) c on a.kd_pembayaran=c.kd_pembayaran WHERE a.status_jurnal='0'   
        ");

		return $query->result();
	}

	public function get_data_pn_np()
	{

		$query =  $this->db->query("SELECT a.* FROM tr_invoice_np_payment a");

		return $query->result();
	}

	public function get_data_pn_jurnal_np()
	{

		$query =  $this->db->query("SELECT a.* FROM tr_invoice_np_payment a WHERE a.status_jurnal='0'");

		return $query->result();
	}

	function generate_nopn_np($tgl)
	{
		$arr_tgl = array(
			1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F',
			7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L'
		);
		$bln_now = date('m', strtotime($tgl));
		$kode_bln = '';
		foreach ($arr_tgl as $k => $v) {
			if ($k == $bln_now) {
				$kode_bln = $v;
			}
		}
		$cek = 'NP-' . date('y') . $kode_bln;
		/*$query_cek = $this->db->query("SELECT MAX(no_so) as max_id FROM trans_so_header
      WHERE no_so LIKE '%$cek%'")->num_rows();*/
		$this->db->select("MAX(kd_pembayaran) as max_id");
		$this->db->like('kd_pembayaran', $cek);
		$this->db->from('tr_invoice_np_payment');
		$query_cek = $this->db->count_all_results();

		if ($query_cek == 0) {
			$kode = 1;
			$next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
			$fin = 'NP-' . date('y') . $kode_bln . $next_kode;
		} else {
			$query = "SELECT MAX(kd_pembayaran) as max_id
        FROM
        tr_invoice_np_payment WHERE kd_pembayaran LIKE '%$cek%'";
			$q = $this->db->query($query);
			$r = $q->row();


			$query = $this->db->query("SELECT MAX(kd_pembayaran) as max_id
        FROM
        tr_invoice_np_payment WHERE kd_pembayaran LIKE '%$cek%'");
			$row = $query->row_array();
			$thn = date('T');
			$max_id = $row['max_id'];
			$max_id1 = (int) substr($max_id, -5);
			$kode = $max_id1 + 1;

			$next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
			$fin = 'NP-' . date('y') . $kode_bln . $next_kode;
		}
		return $fin;
	}

	public function get_query_json_product_price($status = NULL, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$WHERE = "";
		// if ($status != '0') {
		// 	$WHERE = "AND a.status = '" . $status . "'";
		// }
		$sql = "SELECT
					a.*,
					a.product_master AS nama_level4,
					b.code as product_code,
					d.variant_product,
					d.color as color,
					d.surface as surface,
					c.nama AS nama_level1,
					0 as price_list,
					0 as price_list_idr,
					e.price_unit,
					e.id as id_ukuran_jadi,
					f.width as width,
					f.length as length
				FROM
					product_price a 
					JOIN product_price_ukuran_jadi e ON e.kode = a.kode 
					LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
					LEFT JOIN new_inventory_1 c ON b.code_lv1=c.code_lv1
					LEFT JOIN bom_header d ON a.no_bom = d.no_bom
					LEFT JOIN custom_ipp_detail_lainnya f ON f.id = e.id_ukuran
				WHERE 1=1 AND e.deleted_by IS NULL " . $WHERE . " AND
					(
						b.code LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.no_bom LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR d.variant_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
				
				UNION ALL

				SELECT
					a.*,
					a.product_master AS nama_level4,
					b.code as product_code,
					d.variant_product,
					d.color as color,
					d.surface as surface,
					c.nama AS nama_level1,
					a.price_list as price_list,
					a.price_list_idr as price_list_idr,
					0 as price_unit,
					'' as id_ukuran_jadi,
					0 as width,
					0 as length
				FROM
					product_price a 
					LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
					LEFT JOIN new_inventory_1 c ON b.code_lv1=c.code_lv1
					LEFT JOIN bom_header d ON a.no_bom = d.no_bom
				WHERE 1=1 AND a.deleted_by IS NULL " . $WHERE . " AND
					(
						b.code LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.no_bom LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR d.variant_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
		";

		
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor'
		);

		// $sql .= " ORDER BY a.no_bom DESC";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		// print_r($data);
		// exit;
		return $data;
	}

	public function get_json_product_price()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_product_price(
			$requestData['status'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData			= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query					= $fetch['query'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		// print_r($query);
		// exit;
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

			if(!isset($row['price_unit']) || $row['price_unit'] <= 0){
				$idr_price = $row['price_list_idr'];
				$usd_price = $row['price_list'];
			}else{
				$idr_price = $row['price_unit'];
				$usd_price = 0;
				if($row['kurs'] > 0) {
					$usd_price = ($row['price_unit'] / $row['kurs']);
				}
			}

			$nestedData 	= array();
			$nestedData[]	= "<td align='center'>" . $nomor . "</td>";
			$nestedData[]	= "<td align='left'>" . strtoupper(strtolower($row['product_code'])) . "</td>";
			$nestedData[]	= "<td align='left'>" . strtoupper(strtolower($row['nama_level4'])) . "</td>";
			$nestedData[]	= "<td align='left' style='min-width: 15% !important;'>" . number_format($row['width'], 2) . " x ".number_format($row['length'], 2)."</td>";
			$nestedData[]	= "<td align='left'>" . strtoupper(strtolower($row['variant_product'])) . "</td>";
			$nestedData[]	= "<td align='left'>" . strtoupper(strtolower($row['color'])) . "</td>";
			$nestedData[]	= "<td align='left'>" . strtoupper(strtolower($row['surface'])) . "</td>";
			// $nestedData[]	= "<td align='right'>".number_format($row['price_man_power'],2)."</td>";
			// $nestedData[]	= "<td align='right'>" . number_format($row['price_total'], 2) . "</td>";
			$nestedData[]	= "<td align='right'>" . number_format($idr_price, 2) . "</td>";
			$nestedData[]	= "<td align='right'>" . number_format($usd_price, 2) . "</td>";

			$status = 'Waiting Submission';
			$warna = 'blue';
			if ($row['status'] == 'WA') {
				$status = 'Waiting Approval';
				$warna = 'purple';
			}
			if ($row['status'] == 'A') {
				$status = 'Approved';
				$warna = 'green';
			}
			if ($row['status'] == 'R') {
				$status = 'Rejected';
				$warna = 'red';
			}

			// $nestedData[]	= "<div align='left'><span class='badge bg-".$warna."'>".$status."</span></div>";


			$view	= '<button type="button" class="btn btn-sm btn-success select_product_price_' . $row['id'] . '_'.$row['id_ukuran_jadi'].'" onclick="add_product_price(' . $row['id'] . ', ' . $row['id_ukuran_jadi'] . ')"><i class="fa fa-plus"></i>Select</button>';
			$edit	= "";

			// $view	= "<a href='" . site_url($this->uri->segment(1)) . '/detail_costing/' . $row['no_bom'] . "' class='btn btn-sm btn-warning' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></a>";
			// if($row['status'] == 'WA'){
			// 	$edit	= "<a href='".site_url($this->uri->segment(1)).'/pengajuan_costing/'.$row['no_bom']."' class='btn btn-sm btn-success' title='Approval Price List' data-role='qtip'><i class='fa fa-check'></i></a>";
			// }
			$nestedData[]	= "	<div align='center'>
								" . $view . "
								" . $edit . "
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

	function generate_no_penawaran($kode = '')
	{
		$generate_id = $this->db->query("SELECT MAX(no_penawaran) AS max_id FROM tr_penawaran WHERE no_penawaran LIKE '%PNR1-" . date('m-y') . "%'")->row();
		$kodeBarang = $generate_id->max_id;
		$urutan = (int) substr($kodeBarang, 11, 5);
		$urutan++;
		$tahun = date('m-y');
		$huruf = "PNR1-";
		$kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

		return $kodecollect;
	}
	function generate_id_history($kode = '')
	{
		$generate_id = $this->db->query("SELECT MAX(id_history_penawaran) AS max_id FROM tr_history_penawaran WHERE id_history_penawaran LIKE '%HPNR1-" . date('m-y') . "%'")->row();
		$kodeBarang = $generate_id->max_id;
		$urutan = (int) substr($kodeBarang, 12, 5);
		$urutan++;
		$tahun = date('m-y');
		$huruf = "HPNR1-";
		$kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

		return $kodecollect;
	}
	function generate_no_other_cost($kode = '')
	{
		$generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM tr_penawaran_other_cost WHERE id LIKE '%PNROC1-" . date('m-y') . "%'")->row();
		$kodeBarang = $generate_id->max_id;
		$urutan = (int) substr($kodeBarang, 13, 5);
		$urutan++;
		$tahun = date('m-y');
		$huruf = "PNROC1-";
		$kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

		return $kodecollect;
	}

	public function get_json_ipp()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_ipp(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData			= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query					= $fetch['query'];

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

			$status = '<div class="badge badge-warning">Draft</div>';
			if ($row['sts'] == '1') {
				$status = '<div class="badge badge-success">Approved</div>';
			}
			if ($row['sts'] == '2') {
				$status = '<div class="badge badge-danger">Rejected</div>';
			}

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper(strtolower($row['no_ipp'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper(strtolower($row['nm_customer'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper(strtolower($row['project'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper(strtolower($row['nama'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper(strtolower($row['product_name'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . $status . "</div>";
			$nestedData[]	= "<div align='center'>
				<a href='" . base_url('quotation/view_request_new_product/' . $row['id']) . "' class='btn btn-sm btn-info'>View</a>
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

	public function get_query_json_ipp($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "SELECT
					a.*,
					b.nm_customer AS nm_customer,
					c.nm_bom_topping,
					c.product_name,
					d.nama
				FROM
					ipp a
					LEFT JOIN customer b ON a.id_customer=b.id_customer
					LEFT JOIN ipp_detail c ON c.no_ipp = a.no_ipp
					LEFT JOIN new_inventory_1 d ON d.code_lv1 = c.type_product
				WHERE 1=1 AND a.request_new_product = 1 AND a.deleted_date IS NULL AND 
					(
						b.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.no_ipp LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR d.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR c.product_name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
		";
		// print_r($sql);
		// exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_ipp',
			2 => 'b.name_customer',
			3 => 'project',
			4 => 'rev'
		);

		$sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
}
