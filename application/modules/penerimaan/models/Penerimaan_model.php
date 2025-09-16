<?php
class Penerimaan_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}



	public function modal_detail_invoice($id)
	{

		// $id    = $this->uri->segment(3);
		$getInv = $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice= '$id'")->row();

		// $Cust = $this->db->query("SELECT a.id_customer,b.nm_customer FROM tr_invoice_header a INNER JOIN customer b on a.id_customer=b.id_customer GROUP BY a.id_customer")->result();
		// $Cust = $this->db->get_where('customer', array('deleted_by' => null))->result();

		$this->db->select();
		$this->db->from('tr_invoice_sales a');
		$this->db->join('tr_sales_order b', 'b.no_so = a.id_so', 'left');
		$this->db->join('customer c', 'c.id_customer = b.id_customer', 'left');
		$this->db->group_by('c.id_customer');
		$this->db->where('c.deleted_by', null);
		$this->db->where('a.sts', 1);
		$Cust = $this->db->get()->result();

		$bank1			 = $this->Jurnal_model->get_Coa_Bank_Aja('101');
		$pphpenjualan  	 = $this->Acc_model->combo_pph_penjualan();
		$datacoa  	     = $this->Acc_model->GetCoaCombo();
		$template  	     = $this->Acc_model->GetTemplate();
		$data_coa_bank = $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
		// print_r($pphpenjualan);
		// exit;

		$data = array(
			'title'			=> 'Penerimaan',
			'action'		=> 'add',
			'results' => $getInv,
			'no_inv'  => $id,
			'datbank' => $data_coa_bank,
			'pphpenjualan' => $pphpenjualan,
			'template' => $template,
			'customer' => $Cust

		);

		$this->template->set($data);
		$this->template->render('create_penerimaan_new');
		// $this->load->view('Penerimaan/create_penerimaan_new', $data);

		// $this->template->set([
		// 'results' => $getInv,
		// 'no_inv'  => $id,
		// 'datbank' => $bank1,
		// 'pphpenjualan'=> $pphpenjualan,
		// 'template'=> $template,
		// 'customer'=>$Cust
		// ]);
		// $this->template->render('create_penerimaan_new');
	}

	public function modal_detail_invoice_draf($id)
	{

		// $id    = $this->uri->segment(3);
		$getInv = $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice='$id'")->row();

		$Cust = $this->db->query("SELECT a.id_customer,b.nm_customer FROM tr_invoice_header a
											INNER JOIN customer b on a.id_customer=b.id_customer GROUP BY a.id_customer")->result();

		$header = $this->db->query("SELECT * FROM tr_invoice_payment_temp WHERE kd_pembayaran='$id'")->row();
		$detail = $this->db->query("SELECT * FROM tr_invoice_payment_detail_temp WHERE kd_pembayaran='$id'")->result();
		$bank1			 = $this->Jurnal_model->get_Coa_Bank_Aja('101');
		$pphpenjualan  	 = $this->Acc_model->combo_pph_penjualan();
		$datacoa  	     = $this->Acc_model->GetCoaCombo();
		$template  	     = $this->Acc_model->GetTemplate();
		$data_coa_bank = $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");

		$coa = $this->db->query("SELECT a.no_perkiraan,a.nama FROM gl_ori_dummy.coa_master a
											WHERE a.no_perkiraan like '1101%' AND level='5'")->result();
		// print_r($pphpenjualan);
		// exit;

		$data = array(
			'title'			=> 'Penerimaan',
			'action'		=> 'add',
			'results' => $getInv,
			'no_inv'  => $id,
			'coa' => $coa,
			'pphpenjualan' => $pphpenjualan,
			'template' => $template,
			'customer' => $Cust,
			'header' => $header,
			'detail' => $detail

		);

		$this->load->view('Penerimaan/create_penerimaan_draf', $data);

		// $this->template->set([
		// 'results' => $getInv,
		// 'no_inv'  => $id,
		// 'datbank' => $bank1,
		// 'pphpenjualan'=> $pphpenjualan,
		// 'template'=> $template,
		// 'customer'=>$Cust
		// ]);
		// $this->template->render('create_penerimaan_new');
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
			$nestedData[]	= "<div align='left'>" . number_format($row['totalinvoiced']) . "</div>";
			$nestedData[]	= "<div align='left'>" . number_format($row['biaya_pph_idr']) . "</div>";
			$nestedData[]	= "<div align='left'>" . number_format($row['biaya_admin_idr']) . "</div>";
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
			1 => 'A',
			2 => 'B',
			3 => 'C',
			4 => 'D',
			5 => 'E',
			6 => 'F',
			7 => 'G',
			8 => 'H',
			9 => 'I',
			10 => 'J',
			11 => 'K',
			12 => 'L'
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

	function generate_nopro($tgl)
	{
		$arr_tgl = array(
			1 => 'A',
			2 => 'B',
			3 => 'C',
			4 => 'D',
			5 => 'E',
			6 => 'F',
			7 => 'G',
			8 => 'H',
			9 => 'I',
			10 => 'J',
			11 => 'K',
			12 => 'L'
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
		$this->db->from('tr_invoice_payment_temp');
		$query_cek = $this->db->count_all_results();



		if ($query_cek == 0) {
			$kode = 1;
			$next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
			$fin = 'PN-' . date('y') . $kode_bln . $next_kode;
		} else {
			$query = "SELECT MAX(kd_pembayaran) as max_id
        FROM
        tr_invoice_payment_temp WHERE kd_pembayaran LIKE '%$cek%'";
			$q = $this->db->query($query);
			$r = $q->row();


			$query = $this->db->query("SELECT MAX(kd_pembayaran) as max_id
        FROM
        tr_invoice_payment_temp WHERE kd_pembayaran LIKE '%$cek%'");
			$row = $query->row_array();
			$thn = date('T');
			$max_id = $row['max_id'];
			$max_id1 = (int) substr($max_id, -6, 2);
			$kode = $max_id1 + 1;

			$next_kode = str_pad($kode, 5, "0", STR_PAD_LEFT);
			$fin = 'PN-' . date('y') . $kode_bln . $next_kode;
		}

		// print_r($max_id1);
		// exit;
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

	public function get_data_pn()
	{

		$query =  $this->db->query("SELECT a.*, c.invoiced, c.totalinvoiced FROM tr_invoice_payment a	        
        left outer join (
            SELECT kd_pembayaran,
            GROUP_CONCAT(no_surat SEPARATOR ',') as invoiced,
            sum(total_bayar_idr) as totalinvoiced
            FROM view_tr_invoice_payment
            GROUP BY kd_pembayaran
        ) c on a.kd_pembayaran=c.kd_pembayaran       
        ORDER BY a.id DESC");
		if(!$query){
			print_r($this->db->error($query));
			exit;
		}

		return $query->result();
	}

	public function get_data_pro()
	{

		$query =  $this->db->query("SELECT a.*, c.invoiced, c.totalinvoiced FROM tr_invoice_payment_temp a	        
        left outer join (
            SELECT kd_pembayaran,
            GROUP_CONCAT(no_surat SEPARATOR ',') as invoiced,
            sum(total_bayar_idr) as totalinvoiced
            FROM view_tr_invoice_payment_temp
            GROUP BY kd_pembayaran
        ) c on a.kd_pembayaran=c.kd_pembayaran       
        ORDER BY a.id DESC");

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


	public function get_data_invoice()
	{

		$query =  $this->db->query("SELECT a.* FROM tr_invoice_tutup_bulan a");

		return $query->result();
	}

	public function get_data_bank()
	{

		$query =  $this->db->query("SELECT a.* FROM tr_saldo_bank a");

		return $query->result();
	}
}
