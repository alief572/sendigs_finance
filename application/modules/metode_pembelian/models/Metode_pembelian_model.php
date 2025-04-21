<?php
class Metode_pembelian_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	//==================================================================================================================
	//=============================================PURCHASE REQUEST=====================================================
	//==================================================================================================================

	public function index_pr()
	{
		$data_Group			=  $this->db->get('groups');
		$data = array(
			'title'			=> 'Pembelian Non-Material & Jasa >> Metode Pembelian',
			'action'			=> 'index',
			'row_group'		=> $data_Group
		);
		history('View Progress PR Asset, Rutin, Non Rutin');
		// $this->load->view('Pembelian/pr',$data);
		$this->template->set($data);
		$this->template->render('pr_new');
	}

	public function get_data_json_progress_pr()
	{
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_progress_pr(
			$requestData['category'],
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
				$nomor = ($total_data - $start_dari) - $urut2;
			}
			if ($asc_desc == 'desc') {

				$nomor = $urut1 + $start_dari;
			}

			$nestedData 	= array();

			$nestedData[]	= "<div class='prt_" . $nomor . "' align='center'>" . $nomor . "</div><script type='text/javascript'>$('.prt_" . $nomor . "').parent().parent().attr('id','" . $nomor . "');</script></div>";
			$nestedData[]	= "<div align='center'>" . $row['no_pr_group'] . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['tgl_pr'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_dept']) . "</div>";
			if ($row['category'] == 'asset') {
				$warna = '#a9179e';
			} elseif ($row['category'] == 'rutin') {
				$warna = '#ff1ab3';
			} else {
				$warna = '#1bb885';
			}

			$sts2 = "";

			$category = $row['category'];
			if ($category == 'rutin') {
				$category = 'stok';
			}

			if ($category == 'non rutin') {
				$category = 'departemen';
			}

			if (!empty($row['jenis_pembelian'])) {
				$sts2 = "<br><span class='badge' style='background-color: " . $warna . ";'>PURCHASING " . strtoupper($row['jenis_pembelian']) . "</span>";
			}

			$jns_pm = "";
			$sts = "WAITING PROCESS";
			$warna2 = 'blue';
			if (!empty($row['no_rfq'])) {
				if ($row['jenis_pembelian'] == 'po') {
					$jns_pm = "<br><b>NO RFQ</b><br>" . strtoupper($row['no_rfq']);

					$get_sts = $this->db->query("SELECT sts_ajuan FROM tran_rfq_header WHERE no_rfq='" . $row['no_rfq'] . "' LIMIT 1")->result();

					$sts 	= color_status_purchase($get_sts[0]->sts_ajuan)['status'];
					$warna2 = color_status_purchase($get_sts[0]->sts_ajuan)['color'];
				}
				if ($row['jenis_pembelian'] == 'non po') {
					$jns_pm = "<br><b>NO NON-PO</b><br>" . strtoupper($row['no_rfq']);

					$get_sts = $this->db->query("SELECT app_status FROM tran_non_po_detail WHERE no_non_po='" . $row['no_rfq'] . "' LIMIT 1")->result();
					$stsx = "APV";
					if ($get_sts[0]->app_status == 'Y') {
						$stsx = "CLS";
					}
					$sts 	= color_status_purchase($stsx)['status'];
					$warna2 = color_status_purchase($stsx)['color'];
				}
			}

			$nestedData[]	= "<div align='center'><span class='badge' style='background-color: " . $warna . ";'>" . strtoupper($category) . "</span></div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_barang']) . "</div>";
			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: " . $warna2 . "'>" . $sts . "</span>" . $sts2 . "</div>";
			$nestedData[]	= "<div align='center'>
								<button type='button' class='btn btn-sm btn-primary look_hide' title='Look and Hide' data-id='" . $nomor . "'><i class='fa fa-eye'></i></button>
								</div>";
			$data[] = $nestedData;

			$app_reason = (!empty($row['app_reason'])) ? $row['app_reason'] : 'tidak ada';
			$sts_app = "<b>REASON</b><br>" . ucfirst($app_reason);
			$sts_by = "<b>CREATED BY</b><br>" . ucfirst($row['app_by'] . " (" . date('d-M-Y H:i:s', strtotime($row['app_date'])) . ")");


			$nestedData2 	= array();
			$nestedData2[]	= "<div class='prtCh_" . $nomor . "' align='center'></div><script type='text/javascript'>$('.prtCh_" . $nomor . "').parent().parent().attr('class','child-" . $nomor . "');$('.child-" . $nomor . "').hide()</script>"; //$('.prtCh_".$nomor."').parent().parent().attr('height','200px');
			$nestedData2[]	= "<div align='left'></div>";
			$nestedData2[]	= "<div align='left'></div>";
			$nestedData2[]	= "<div align='right'><b>QTY BARANG</b><br>" . number_format($row['qty']) . "</div>";
			$nestedData2[]	= "<div align='right'><b>NILAI PR</b></br>" . number_format($row['nilai_pr']) . "</div>";
			$nestedData2[]	= "<div align='left'><b>TGL DIBUTUHKAN</b></br>" . date('d F Y', strtotime($row['tgl_dibutuhkan'])) . $jns_pm . "</div>";
			$nestedData2[]	= "<div align='left'>" . $sts_by . "<br>" . $sts_app . "</div>";
			$nestedData2[]	= "<div align='left'></div>";
			$data[] = $nestedData2;

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

	public function query_data_json_progress_pr($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($category <> '0') {
			$where = " AND a.category='" . $category . "' ";
		}

		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				d.nm_dept
			FROM
				tran_pr_detail a
				LEFT JOIN rutin_non_planning_detail c ON a.id_barang=c.id
				LEFT JOIN rutin_non_planning_header e ON c.no_pengajuan=e.no_pengajuan
				LEFT JOIN department d ON e.id_dept=d.id,
				(SELECT @row:=0) r
		    WHERE 1=1 AND a.app_status = 'Y' AND a.no_rfq IS NULL " . $where . " 
				AND (
				a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.id_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_pr',
			2 => 'tgl_pr',
			3 => 'category',
			4 => 'nm_barang'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function get_data_json_progress_pr_new()
	{
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_progress_pr_new(
			$requestData['category'],
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
				$nomor = ($total_data - $start_dari) - $urut2;
			}
			if ($asc_desc == 'desc') {

				$nomor = $urut1 + $start_dari;
			}

			$nestedData 	= array();

			$nestedData[]	= "<div class='prt_" . $nomor . "' align='center'>" . $nomor . "</div><script type='text/javascript'>$('.prt_" . $nomor . "').parent().parent().attr('id','" . $nomor . "');</script></div>";
			$nestedData[]	= "<div align='center'>" . $row['no_pr'] . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['tgl_pr'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['departemen']) . "</div>";
			if ($row['category'] == 'pr material') {
				$warna = '#a9179e';
			} elseif ($row['category'] == 'pr stok') {
				$warna = '#ff1ab3';
			} else {
				$warna = '#1bb885';
			}

			$sts2 = "";

			$category = $row['category'];
			if ($category == 'stok') {
				$category = 'stok';
			}

			if ($category == 'departemen') {
				$category = 'departemen';
			}

			$nestedData[]	= "<div align='center'><span class='badge' style='background-color: " . $warna . ";'>" . strtoupper($category) . "</span></div>";
			$nestedData[]	= "<div align='center'>" . strtoupper($row['by_name']) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['tgl_buat'])) . "</div>";
			$nestedData[]	= "<div align='center'>
								<button type='button' class='btn btn-sm btn-primary detail_pr' title='Detail' data-no_pr_group='" . $row['no_pr'] . "' data-tipe_pr='" . $row['category'] . "'><i class='fa fa-eye'></i></button>
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

	public function query_data_json_progress_pr_new($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($category <> '0') {
			$where = " AND a.category='" . $category . "' ";
		}

		// $sql = "
		// 	SELECT
		// 		(@row:=@row+1) AS nomor,
		// 		a.*,
		// 		d.nm_dept
		// 	FROM
		// 		tran_pr_detail a
		// 		LEFT JOIN rutin_non_planning_detail c ON a.id_barang=c.id
		// 		LEFT JOIN rutin_non_planning_header e ON c.no_pengajuan=e.no_pengajuan
		// 		LEFT JOIN department d ON e.id_dept=d.id,
		// 		(SELECT @row:=0) r
		//     WHERE 1=1 AND a.app_status = 'Y' " . $where . " 
		// 		AND (
		// 		a.no_pr_group LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		// 		OR a.id_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		// 		OR a.nm_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		// 		OR a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		//     )
		// 	GROUP BY a.no_pr_group
		// ";

		if ($category <> '0') {
			if ($category == 'departemen') {
				$sql = "
					SELECT
						a.no_pr as no_pr,
						a.created_date as tgl_pr,
						b.nama as departemen,
						'Department' as category,
						c.nm_lengkap as by_name,
						a.created_date as tgl_buat
					FROM
						rutin_non_planning_header a
						LEFT JOIN ms_department b ON b.id = a.id_dept
						LEFT JOIN users c ON c.id_user = a.created_by
					WHERE
						a.sts_app = 'Y' AND 
						a.metode_pembelian IS NULL AND (
							a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							c.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						)
				";
			} else if ($category == 'stok') {
				$sql = "
					SELECT
						a.no_pr as no_pr,
						a.tgl_so as tgl_pr,
						'PPIC' as departemen,
						a.category as category,
						b.nm_lengkap as by_name,
						a.created_date as tgl_buat
					FROM
						material_planning_base_on_produksi a
						LEFT JOIN users b ON b.id_user = a.created_by
						JOIN material_planning_base_on_produksi_detail c ON c.so_number = a.so_number AND c.status_app = 'Y'
					WHERE
						a.category IN ('pr stok') AND
						a.metode_pembelian IS NULL AND (
							a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							a.tgl_so LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							b.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						)
					GROUP BY a.no_pr
				";
			} else {
				$sql = "
					SELECT
						a.no_pr as no_pr,
						a.tgl_so as tgl_pr,
						'PPIC' as departemen,
						a.category as category,
						b.nm_lengkap as by_name,
						a.created_date as tgl_buat
					FROM
						material_planning_base_on_produksi a
						LEFT JOIN users b ON b.id_user = a.created_by
						JOIN material_planning_base_on_produksi_detail c ON c.so_number = a.so_number AND c.status_app = 'Y'
					WHERE
						a.category IN ('pr material') AND
						a.metode_pembelian IS NULL AND (
							a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							a.tgl_so LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							b.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
							a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						)
					GROUP BY a.no_pr
				";
			}
		} else {
			$sql = "
				SELECT
					a.no_pr as no_pr,
					a.tgl_so as tgl_pr,
					'PPIC' as departemen,
					a.category as category,
					b.nm_lengkap as by_name,
					a.created_date as tgl_buat
				FROM
					material_planning_base_on_produksi a
					LEFT JOIN users b ON b.id_user = a.created_by
					JOIN material_planning_base_on_produksi_detail c ON c.so_number = a.so_number AND c.status_app = 'Y'
				WHERE
					a.category IN ('pr material', 'pr stok') AND
					a.metode_pembelian IS NULL AND
					(
						a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
						a.tgl_so LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
						a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
						b.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
						a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
				GROUP BY a.no_pr

				UNION ALL

				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nama as departemen,
					'Department' as category,
					c.nm_lengkap as by_name,
					a.created_date as tgl_buat
				FROM
					rutin_non_planning_header a
					LEFT JOIN ms_department b ON b.id = a.id_dept
					LEFT JOIN users c ON c.id_user = a.created_by
				WHERE
					a.sts_app = 'Y' AND 
					a.metode_pembelian IS NULL AND (
						a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
						a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
						b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
						c.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
				GROUP BY a.no_pr
			";
		}

		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_pr',
			2 => 'tgl_pr',
			3 => 'category'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	//==================================================================================================================
	//=============================================REQUEST FOR QUOTATION================================================
	//==================================================================================================================

	public function index_rfq()
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
			'title'			=> 'Pembelian Non-Material & Jasa >> PO >> Request For Quotation',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'data_gudang'	=> $data_gudang
		);
		history('View RFQ Asset, Rutin, Non Rutin');
		$this->load->view('Pembelian/rfq', $data);
	}

	public function get_data_json_rfq()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/rfq";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_rfq(
			$requestData['category'],
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

			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_rfq_header WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N'")->result_array();
			$arr_sup = array();
			foreach ($list_supplier as $val => $valx) {
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);

			$list_material		= $this->db->query("SELECT nm_barang, qty, tgl_dibutuhkan FROM tran_rfq_detail WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N' GROUP BY id_barang")->result_array();
			$arr_mat = array();
			$arr_tgl = array();
			$arr_qty = array();
			foreach ($list_material as $val => $valx) {
				$arr_mat[$val] = $valx['nm_barang'];
				$arr_qty[$val] = number_format($valx['qty']);
				$arr_tgl[$val] = date('d-M-Y', strtotime($valx['tgl_dibutuhkan']));
			}
			$dt_mat	= implode("<br>", $arr_mat);
			$dt_qty	= implode("<br>", $arr_qty);
			$dt_tgl	= implode("<br>", array_unique($arr_tgl));

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['no_rfq'] . "</div>";
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
			$nestedData[]	= "<div align='left'>" . strtoupper($dt_mat) . "</div>";
			$nestedData[]	= "<div align='center'>" . $dt_qty . "</div>";
			$nestedData[]	= "<div align='center'>" . $dt_tgl . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['updated_by'] . "</div>";
			$nestedData[]	= "<div align='right'>" . date('d-M-Y H:i:s', strtotime($row['updated_date'])) . "</div>";

			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: " . color_status_purchase($row['sts_ajuan'])['color'] . "'>" . color_status_purchase($row['sts_ajuan'])['status'] . "</span></div>";
			$create	= "";
			$edit	= "";
			$edit_rfq	= "";
			$hapus	= "";
			$booking	= "";
			$spk_ambil_mat	= "";
			if ($row['sts_ajuan'] == 'OPN' and $row['sts_process'] == 'N') {
				$edit_rfq	= "<button type='button' class='btn btn-sm btn-primary edit_po' title='Edit RFQ' data-no_rfq='" . $row['no_rfq'] . "'><i class='fa fa-edit'></i></button>";
				$spk_ambil_mat	= "<a href='" . base_url('pembelian/print_rfq/' . $row['no_rfq']) . "' target='_blank' class='btn btn-sm btn-info' title='Print RFQ' data-role='qtip'><i class='fa fa-print'></i></a>";
				if ($Arr_Akses['update'] == '1') {
					$edit			= "<button type='button' class='btn btn-sm btn-success editMat' title='Edit Material Purchase' data-no_rfq='" . $row['no_rfq'] . "'><i class='fa fa-edit'></i></button>";
				}
				$hapus			= "<button type='button' class='btn btn-sm btn-danger delete_rfq' title='Delete RFQ' data-no_rfq='" . $row['no_rfq'] . "'><i class='fa fa-trash'></i></button>";
			}
			$nestedData[]	= "<div align='left'>
                                    <button type='button' class='btn btn-sm btn-warning detailMat' title='Total Material Purchase' data-no_rfq='" . $row['no_rfq'] . "' data-status='" . $row['sts_ajuan'] . "'><i class='fa fa-eye'></i></button>
                                    " . $create . "
									" . $edit . "
									" . $booking . "
									" . $spk_ambil_mat . "
									" . $edit_rfq . "
									" . $hapus . "
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

	public function query_data_json_rfq($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($category <> '0') {
			$where = " AND a.category='" . $category . "' ";
		}

		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*
			FROM
				tran_rfq_header a,
				(SELECT @row:=0) r
		    WHERE 1=1 " . $where . " AND a.deleted = 'N' AND  (
				a.no_rfq LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY a.no_rfq
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_rfq',
			2 => 'category'
		);

		$sql .= " ORDER BY a.updated_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function add_rfq()
	{

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$query = "SELECT kode_supplier AS id_supplier, nama AS nm_supplier FROM new_supplier ORDER BY nm_supplier ASC ";
		$restQuery = $this->db->query($query)->result_array();
		$data = array(
			'title'			=> 'Add Group PR',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'supList'		=> $restQuery,
		);
		$this->template->set($data);
		$this->template->render('add_rfq');
	}

	public function get_data_json_list_pr()
	{
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_list_pr(
			$requestData['category'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		// print_r($query);
		// exit;

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

			$username = $this->auth->user_name();

			// $CHECK = ($username == $row['checklist_by'] and $row['checklist'] == '1') ? 'checked' : '';

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'><center><input type='checkbox' name='check[]' class='chk_personal' data-nomor='" . $nomor . "' value='" . $row['no_pr'] . "' ></center><input type='hidden' name='category_" . $row['no_pr'] . "' value='" . $row['category'] . "'></div>";
			$nestedData[]	= "<div align='center'>" . $row['no_pr'] . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['tgl_pr'])) . "</div>";

			$list_barang = '';
			if ($row['category'] == 'material' || $row['category'] == 'stok') {
				if ($row['category'] == 'stok') {
					$this->db->select('b.stock_name as nm_barang, a.propose_purchase as qty');
					$this->db->from('material_planning_base_on_produksi_detail a');
					$this->db->join('accessories b', 'b.id = a.id_material', 'left');
					$this->db->where('a.status_app', 'Y');
					$this->db->where('a.so_number', $row['so_number']);
					$get_list_barang = $this->db->get()->result_array();
				} else {
					$this->db->select('b.nama as nm_barang, a.propose_purchase as qty');
					$this->db->from('material_planning_base_on_produksi_detail a');
					$this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_material', 'left');
					$this->db->where('a.status_app', 'Y');
					$this->db->where('a.so_number', $row['so_number']);
					$get_list_barang = $this->db->get()->result_array();
				}
			} else {
				if ($row['category'] == 'asset') {
					$this->db->select('a.nama_asset as nm_barang, 1 as qty');
					$this->db->from('asset_planning a');
					$this->db->where('a.status', 'Y');
					$this->db->where('a.no_pr', $row['no_pr']);
					$get_list_barang = $this->db->get()->result_array();
				} else {
					$this->db->select('a.nm_barang as nm_barang, a.qty as qty');
					$this->db->from('rutin_non_planning_detail a');
					$this->db->where('a.sts_app', 'Y');
					$this->db->where('a.no_pr', $row['no_pr']);
					$get_list_barang = $this->db->get()->result_array();
				}
			}

			foreach ($get_list_barang as $barang) :
				$list_barang .= $barang['nm_barang'] . ' x <span style="font-weight: bold;">' . number_format($barang['qty']) . '</span><br>';
			endforeach;

			$nestedData[]	= "<div align='left'>" . $list_barang . "</div>";
			if ($row['category'] == 'material') {
				$warna = '#a9179e';
			} elseif ($row['category'] == 'stok') {
				$warna = '#a19012';
			} elseif ($row['category'] == 'asset') {
				$warna = '#66ccff';
			} else {
				$warna = '#1bb885';
			}

			$category = $row['category'];
			if ($category == 'stok') {
				$category = 'stok';
			}

			if ($category == 'departemen') {
				$category = 'departemen';
			}

			$nestedData[]	= "<div align='center'><span class='badge' style='background-color: " . $warna . ";'>" . strtoupper($category) . "</span></div>";
			$nestedData[]	= "<div align='center'>" . date('d F Y', strtotime($row['tgl_pr'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper($row['request_by']) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['request_date'])) . "</div>";
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

	public function query_data_json_list_pr($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($category <> '0') {
			$where = " AND category = '" . $category . "' ";
		}

		// $sql = "
		// 	SELECT
		// 		(@row:=@row+1) AS nomor,
		// 		a.*
		// 	FROM
		// 		tran_pr_detail a,
		// 		(SELECT @row:=0) r
		//     WHERE 1=1 AND a.app_status = 'Y' AND no_rfq IS NULL " . $where . "
		// 		AND (
		// 		a.no_pr_group LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		// 		OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		// 		OR a.id_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		// 		OR a.nm_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		// 		OR a.created_date LIKE '%" . $this->db->escape_like_str($like_value) . "%'
		//     )
		// ";


		if ($category <> '0') {
			if ($category == 'departemen') {
				$sql = 'SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"departemen" as category,
					"" as so_number
				FROM
					rutin_non_planning_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
						a.metode_pembelian IS NULL AND
						a.close_pr IS NULL
						AND (
							a.no_pr LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
							a.created_date LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
							b.nm_lengkap LIKE "%' . $this->db->escape_like_str($like_value) . '%"
						)
				';
			} else if ($category == 'asset') {
				$sql = 'SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"asset" as category,
					"" as so_number
				FROM
					tran_pr_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
						a.metode_pembelian IS NULL AND
						a.app_status_3 = "Y" AND 
						a.close_pr IS NULL
						AND (
							a.no_pr LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
							a.created_date LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
							b.nm_lengkap LIKE "%' . $this->db->escape_like_str($like_value) . '%"
						)
				';
			} else {
				if ($category == 'stok') {
					$sql = '
						SELECT
							a.no_pr as no_pr,
							a.tgl_so as tgl_pr,
							b.nm_lengkap as request_by,
							a.created_date as request_date,
							IF(a.category = "pr stok", "stok", "material") as category,
							a.so_number as so_number
						FROM
							material_planning_base_on_produksi a
							LEFT JOIN users b ON b.id_user = a.created_by
						WHERE
							a.category IN ("pr stok") AND
							a.metode_pembelian IS NULL AND
							a.close_pr IS NULL
							AND (
								a.no_pr LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
								a.tgl_so LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
								b.nm_lengkap LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
								a.created_date LIKE "%' . $this->db->escape_like_str($like_value) . '%"
							)
					';
				} else {
					$sql = '
						SELECT
							a.no_pr as no_pr,
							a.tgl_so as tgl_pr,
							b.nm_lengkap as request_by,
							a.created_date as request_date,
							IF(a.category = "pr stok", "stok", "material") as category,
							a.so_number as so_number
						FROM
							material_planning_base_on_produksi a
							LEFT JOIN users b ON b.id_user = a.created_by
						WHERE
							a.category IN ("pr material") AND
							a.metode_pembelian IS NULL AND
							a.close_pr IS NULL
							AND (
								a.no_pr LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
								a.tgl_so LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
								b.nm_lengkap LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
								a.created_date LIKE "%' . $this->db->escape_like_str($like_value) . '%"
							)
					';
				}
			}
		} else {
			$sql = '
				SELECT
					a.no_pr as no_pr,
					a.tgl_so as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					IF(a.category = "pr stok", "stok", "material") as category,
					a.so_number as so_number
				FROM
					material_planning_base_on_produksi a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.category IN ("pr material", "pr stok") AND
					a.close_pr IS NULL AND
					a.metode_pembelian IS NULL AND (
						a.no_pr LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
						a.tgl_so LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
						b.nm_lengkap LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
						a.created_date LIKE "%' . $this->db->escape_like_str($like_value) . '%"
					)
				
				UNION ALL
	
				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"departemen" as category,
					"" as so_number
				FROM
					rutin_non_planning_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
						a.metode_pembelian IS NULL AND
						a.close_pr IS NULL AND
						(
							a.no_pr LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
							a.created_date LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
							b.nm_lengkap LIKE "%' . $this->db->escape_like_str($like_value) . '%"
						)
				
				UNION ALL

				SELECT
					a.no_pr as no_pr,
					a.created_date as tgl_pr,
					b.nm_lengkap as request_by,
					a.created_date as request_date,
					"asset" as category,
					"" as so_number
				FROM
					tran_pr_header a
					LEFT JOIN users b ON b.id_user = a.created_by
				WHERE
					a.metode_pembelian IS NULL AND
					a.close_pr IS NULL AND
					(
						a.no_pr LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
						a.created_date LIKE "%' . $this->db->escape_like_str($like_value) . '%" OR
						b.nm_lengkap LIKE "%' . $this->db->escape_like_str($like_value) . '%"
					)


			';

			// print_r($sql);
			// exit;
		}

		// echo $sql; exit;	

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'no_pr'
		);

		$sql .= " ORDER BY no_pr DESC";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function save_rfq()
	{
		$Arr_Kembali	= array();
		$data			= $this->input->post();
		$data_session	= $this->session->userdata;
		$dateTime		= date('Y-m-d H:i:s');
		$UserName		= $this->auth->user_name();
		$Ym				= date('ym');
		$jenis_pembelian = $data['jenis_pembelian'];
		$category		= $data['category'];

		if ($jenis_pembelian == 'po') {
			$kode_jenis_pembelian = '1';
		} else {
			$kode_jenis_pembelian = '2';
		}

		$check			= $this->db->select('id')->get_where('tran_pr_detail', array('checklist' => '1', 'checklist_by' => $UserName, 'no_rfq' => NULL))->result_array();
		$ArrList 		= array();
		foreach ($check as $val => $vaxl) {
			$ArrList[] = $vaxl['id'];
		}
		$dtImplode		= "('" . implode("','", $ArrList) . "')";

		// print_r($check);
		// echo $dtImplode;
		// exit;

		// if ($jenis_pembelian == 'po') {
		// 	//pengurutan kode
		// 	$srcMtr			= "SELECT MAX(no_rfq) as maxP FROM tran_rfq_header WHERE no_rfq LIKE 'RFQX" . $Ym . "%' ";
		// 	$numrowMtr		= $this->db->query($srcMtr)->num_rows();
		// 	$resultMtr		= $this->db->query($srcMtr)->result_array();
		// 	$angkaUrut2		= $resultMtr[0]['maxP'];
		// 	$urutan2		= (int)substr($angkaUrut2, 8, 4);
		// 	$urutan2++;
		// 	$urut2			= sprintf('%04s', $urutan2);
		// 	$no_rfq			= "RFQX" . $Ym . $urut2;

		// 	$id_supplier	= $data['id_supplier'];
		// 	// $check			= $data['check'];
		// 	// $ArrList 		= array();
		// 	// foreach($check AS $vaxl){
		// 	// 	$ArrList[$vaxl] = $vaxl;
		// 	// }
		// 	// $dtImplode		= "('".implode("','", $ArrList)."')";

		// 	$qListPRD 		= "SELECT * FROM tran_pr_detail WHERE id IN " . $dtImplode . "  ";
		// 	$restListPRD 	= $this->db->query($qListPRD)->result_array();

		// 	$ArrUpdate = array();
		// 	foreach ($restListPRD as $val => $valx) {
		// 		$ArrUpdate[$val]['id'] 	= $valx['id'];
		// 		$ArrUpdate[$val]['no_rfq'] = $no_rfq;
		// 		$ArrUpdate[$val]['jenis_pembelian'] = $jenis_pembelian;
		// 	}

		// 	$qListG 	= "SELECT id, id_barang, nm_barang, nilai_pr, SUM(qty) AS purchase, tgl_dibutuhkan, satuan, no_pr, spec, info FROM tran_pr_detail WHERE id IN " . $dtImplode . " GROUP BY id_barang";
		// 	$restListG 	= $this->db->query($qListG)->result_array();

		// 	$ArrDetail = array();
		// 	$ArrHeader = array();
		// 	$no = 0;
		// 	foreach ($id_supplier as $sup => $supx) {
		// 		$qSupplier			= "SELECT * FROM supplier WHERE id_supplier ='" . $supx . "' LIMIT 1 ";
		// 		$restSupplier		= $this->db->query($qSupplier)->result();
		// 		$SUM_MAT = 0;

		// 		$no++;
		// 		$num = sprintf('%03s', $no);
		// 		foreach ($restListG as $val => $valx) {

		// 			$SUM_MAT += $valx['purchase'];
		// 			$ArrDetail[$sup . $val]['no_rfq'] 		= $no_rfq;
		// 			$ArrDetail[$sup . $val]['hub_rfq'] 		= $no_rfq . '-' . $num;
		// 			$ArrDetail[$sup . $val]['id_barang'] 		= $valx['id_barang'];
		// 			$ArrDetail[$sup . $val]['nm_barang'] 		= $valx['nm_barang'];
		// 			$ArrDetail[$sup . $val]['spec'] 			= $valx['spec'];
		// 			$ArrDetail[$sup . $val]['info'] 			= $valx['info'];
		// 			$ArrDetail[$sup . $val]['id_supplier']	= $supx;
		// 			$ArrDetail[$sup . $val]['nm_supplier']	= $restSupplier[0]->nm_supplier;
		// 			$ArrDetail[$sup . $val]['qty'] 			= $valx['purchase'];
		// 			$ArrDetail[$sup . $val]['satuan'] 		= $valx['satuan'];
		// 			$ArrDetail[$sup . $val]['price_ref'] 		= $valx['nilai_pr'];
		// 			$ArrDetail[$sup . $val]['no_pr'] 		 	= $valx['no_pr'];
		// 			$ArrDetail[$sup . $val]['tgl_dibutuhkan'] = $valx['tgl_dibutuhkan'];
		// 			$ArrDetail[$sup . $val]['created_by'] 	= $UserName;
		// 			$ArrDetail[$sup . $val]['created_date'] 	= $dateTime;
		// 		}

		// 		$ArrHeader[$sup]['no_rfq'] 			= $no_rfq;
		// 		$ArrHeader[$sup]['hub_rfq'] 		= $no_rfq . '-' . $num;
		// 		$ArrHeader[$sup]['category'] 		= $category;
		// 		$ArrHeader[$sup]['id_supplier'] 	= $supx;
		// 		$ArrHeader[$sup]['nm_supplier'] 	= $restSupplier[0]->nm_supplier;
		// 		$ArrHeader[$sup]['total_request'] 	= $SUM_MAT;
		// 		$ArrHeader[$sup]['created_by'] 		= $UserName;
		// 		$ArrHeader[$sup]['created_date'] 	= $dateTime;
		// 		$ArrHeader[$sup]['updated_by'] 		= $UserName;
		// 		$ArrHeader[$sup]['updated_date'] 	= $dateTime;
		// 	}

		// 	// print_r($ArrHeader);
		// 	// print_r($ArrDetail);
		// 	// print_r($ArrUpdate);
		// 	// exit;

		// 	$this->db->trans_start();
		// 	$this->db->insert_batch('tran_rfq_header', $ArrHeader);
		// 	$this->db->insert_batch('tran_rfq_detail', $ArrDetail);
		// 	$this->db->update_batch('tran_pr_detail', $ArrUpdate, 'id');
		// 	$this->db->trans_complete();

		// 	if ($this->db->trans_status() === FALSE) {
		// 		$this->db->trans_rollback();
		// 		$Arr_Kembali	= array(
		// 			'pesan'		=> 'Insert purchase order data failed. Please try again later ...',
		// 			'status'	=> 2
		// 		);
		// 	} else {
		// 		$this->db->trans_commit();
		// 		$Arr_Kembali	= array(
		// 			'pesan'		=> 'Insert purchase order data success. Thanks ...',
		// 			'status'	=> 1
		// 		);
		// 		history('Create RFQ ' . $no_rfq . ', ' . $category . '/' . $jenis_pembelian);
		// 	}
		// 	echo json_encode($Arr_Kembali);
		// }
		// if ($jenis_pembelian == 'non po') {
		// 	$pic			= strtolower($data['pic']);
		// 	$keterangan		= strtolower($data['keterangan']);
		// 	//pengurutan kode
		// 	$srcMtr			= "SELECT MAX(no_non_po) as maxP FROM tran_non_po_header WHERE no_non_po LIKE 'NPO" . $Ym . "%' ";
		// 	$numrowMtr		= $this->db->query($srcMtr)->num_rows();
		// 	$resultMtr		= $this->db->query($srcMtr)->result_array();
		// 	$angkaUrut2		= $resultMtr[0]['maxP'];
		// 	$urutan2		= (int)substr($angkaUrut2, 7, 4);
		// 	$urutan2++;
		// 	$urut2			= sprintf('%04s', $urutan2);
		// 	$no_rfq			= "NPO" . $Ym . $urut2;

		// 	// $check			= $data['check'];
		// 	// $ArrList 		= array();
		// 	// foreach($check AS $vaxl){
		// 	// 	$ArrList[$vaxl] = $vaxl;
		// 	// }
		// 	// $dtImplode		= "('".implode("','", $ArrList)."')";

		// 	$qListPRD 		= "SELECT * FROM tran_pr_detail WHERE id IN " . $dtImplode . "  ";
		// 	$restListPRD 	= $this->db->query($qListPRD)->result_array();

		// 	$ArrUpdate = array();
		// 	foreach ($restListPRD as $val => $valx) {
		// 		$ArrUpdate[$val]['id'] 	= $valx['id'];
		// 		$ArrUpdate[$val]['no_rfq'] = $no_rfq;
		// 		$ArrUpdate[$val]['jenis_pembelian'] = $jenis_pembelian;
		// 	}

		// 	$qListG 	= "SELECT no_pr, id, id_barang, nm_barang, nilai_pr, SUM(qty) AS purchase, tgl_dibutuhkan, satuan FROM tran_pr_detail WHERE id IN " . $dtImplode . " GROUP BY id_barang";
		// 	$restListG 	= $this->db->query($qListG)->result_array();

		// 	$ArrDetail = array();
		// 	$ArrHeader = array();
		// 	$SUM_MAT = 0;
		// 	$SUM_QTY = 0;
		// 	foreach ($restListG as $val => $valx) {

		// 		$SUM_MAT += $valx['nilai_pr'];
		// 		$SUM_QTY += $valx['purchase'];
		// 		$nomor_PR = get_name('rutin_non_planning_detail', 'no_pengajuan', 'no_pr', $valx['no_pr']);
		// 		$ArrDetail[$val]['no_non_po'] 	= $no_rfq;
		// 		$ArrDetail[$val]['id_barang'] 	= $valx['id_barang'];
		// 		$ArrDetail[$val]['nm_barang'] 	= $valx['nm_barang'];
		// 		$ArrDetail[$val]['qty'] 		= $valx['purchase'];
		// 		$ArrDetail[$val]['satuan'] 		= $valx['satuan'];
		// 		$ArrDetail[$val]['no_pr'] 		= $valx['no_pr'];
		// 		$ArrDetail[$val]['id_dept'] 		= get_name('rutin_non_planning_header', 'id_dept', 'no_pengajuan', $nomor_PR);
		// 		$ArrDetail[$val]['id_costcenter'] 	= get_name('rutin_non_planning_header', 'id_costcenter', 'no_pengajuan', $nomor_PR);
		// 		$ArrDetail[$val]['price_unit'] 		= $valx['nilai_pr'];
		// 		$ArrDetail[$val]['tgl_dibutuhkan'] 	= $valx['tgl_dibutuhkan'];
		// 		$ArrDetail[$val]['created_by'] 		= $data_session['ORI_User']['username'];
		// 		$ArrDetail[$val]['created_date'] 	= date('Y-m-d H:i:s');
		// 	}

		// 	$ArrHeader['no_non_po'] 	= $no_rfq;
		// 	$ArrHeader['tanggal_non_po'] = date('Y-m-d');
		// 	$ArrHeader['pic'] 			= $pic;
		// 	$ArrHeader['category'] 		= $category;
		// 	$ArrHeader['qty'] 			= $SUM_QTY;
		// 	$ArrHeader['nilai_request'] = $SUM_MAT;
		// 	$ArrHeader['keterangan'] 	= $keterangan;
		// 	$ArrHeader['created_by'] 	= $data_session['ORI_User']['username'];
		// 	$ArrHeader['created_date'] 	= date('Y-m-d H:i:s');



		// 	// print_r($ArrHeader);
		// 	// print_r($ArrDetail);
		// 	// print_r($ArrUpdate);
		// 	// exit;

		// 	$this->db->trans_start();
		// 	$this->db->insert('tran_non_po_header', $ArrHeader);
		// 	$this->db->insert_batch('tran_non_po_detail', $ArrDetail);
		// 	$this->db->update_batch('tran_pr_detail', $ArrUpdate, 'id');
		// 	$this->db->trans_complete();

		// 	if ($this->db->trans_status() === FALSE) {
		// 		$this->db->trans_rollback();
		// 		$Arr_Kembali	= array(
		// 			'pesan'		=> 'Insert purchase order data failed. Please try again later ...',
		// 			'status'	=> 2
		// 		);
		// 	} else {
		// 		$this->db->trans_commit();
		// 		$Arr_Kembali	= array(
		// 			'pesan'		=> 'Insert purchase order data success. Thanks ...',
		// 			'status'	=> 1
		// 		);
		// 		history('Create NON PO ' . $no_rfq . ', ' . $category . '/' . $jenis_pembelian);
		// 	}
		// 	echo json_encode($Arr_Kembali);
		// }

		// if ($jenis_pembelian == 'po') {

		// }
		// if ($jenis_pembelian == 'non po') {
		// }

		foreach ($data['check'] as $valx) :
			$category_pr = $data['category_' . $valx];
			if ($category_pr == 'departemen') {
				$this->db->update('rutin_non_planning_header', [
					'metode_pembelian' => $kode_jenis_pembelian
				], [
					'no_pr' => $valx
				]);
			} elseif ($category_pr == 'asset') {
				$this->db->update('tran_pr_header', [
					'metode_pembelian' => $kode_jenis_pembelian
				], [
					'no_pr' => $valx
				]);
			} else {
				$this->db->update('material_planning_base_on_produksi', [
					'metode_pembelian' => $kode_jenis_pembelian
				], [
					'no_pr' => $valx
				]);
			}
		endforeach;

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			if ($kode_jenis_pembelian == '1') {
				$pesan = 'PO PR Grouping failed, please try again later !';
			} else {
				$pesan = 'Non PO PR Grouping failed, please try again later !';
			}
			$Arr_Kembali	= array(
				'pesan'		=> $pesan,
				'status'	=> 2
			);
		} else {
			$this->db->trans_commit();
			if ($kode_jenis_pembelian == '1') {
				$pesan = 'PO PR Grouping Success !';
			} else {
				$pesan = 'Non PO PR Grouping Success !';
			}
			$Arr_Kembali	= array(
				'pesan'		=> $pesan,
				'status'	=> 1
			);
			// history('Create NON PO ' . $no_rfq . ', ' . $category . '/' . $jenis_pembelian);
		}

		echo json_encode($Arr_Kembali);
	}

	public function modal_detail_rfq()
	{
		$no_rfq 	= $this->uri->segment(3);

		$sql 		= "SELECT a.* FROM tran_rfq_detail a WHERE a.no_rfq='" . $no_rfq . "' AND a.deleted='N'";
		$result		= $this->db->query($sql)->result_array();

		$sql2 		= "SELECT a.* FROM tran_rfq_detail a WHERE a.no_rfq='" . $no_rfq . "' AND a.deleted='N' GROUP BY id_supplier";
		$num_rows	= $this->db->query($sql2)->num_rows();

		$data = array(
			'result' => $result,
			'num_rows' => $num_rows
		);

		$this->load->view('Pembelian/modal_detail_rfq', $data);
	}

	public function modal_edit_rfq_print()
	{
		if ($this->input->post()) {
			$data_session	= $this->session->userdata;
			$data	= $this->input->post();

			$ArrHeader = array(
				'incoterms' 	=> strtolower($data['incoterms']),
				'top' 			=> strtolower($data['top']),
				'remarks' 		=> strtolower($data['remarks']),
				'updated_print_by' 	=> $data_session['ORI_User']['username'],
				'updated_print_date' 	=> date('Y-m-d H:i:s')
			);

			// print_r($ArrHeader);
			// exit;

			$this->db->trans_start();
			$this->db->where('no_rfq', $data['no_rfq']);
			$this->db->update('tran_rfq_header', $ArrHeader);
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
				history('Edit RFQ print custom : ' . $data['no_rfq']);
			}
			echo json_encode($Arr_Data);
		} else {
			$no_rfq 	= $this->uri->segment(3);

			$sql 		= "SELECT a.* FROM tran_rfq_header a WHERE a.no_rfq='" . $no_rfq . "'";

			$result		= $this->db->query($sql)->result();

			$data = array(
				'data' => $result
			);

			$this->load->view('Pembelian/modal_edit_rfq_print', $data);
		}
	}

	public function modal_edit_rfq()
	{
		$no_rfq = $this->uri->segment(3);
		$result		= $this->db->select('
									a.no_rfq,
									a.id_barang,
									a.qty,
									a.nm_barang,
									UPPER(a.spec) AS spec,
									b.category,
									b.tgl_dibutuhkan,
									b.no_pr,
									b.created_date AS tgl_pr
									')
			->from('tran_rfq_detail a')
			->join('tran_pr_detail b', 'a.no_rfq=b.no_rfq', 'left')
			->where('a.id_barang=b.id_barang')
			->where('a.no_rfq', $no_rfq)
			->where('a.deleted', 'N')
			->group_by('a.id_barang')
			->get()
			->result_array();
		// print_r($result); exit;
		$RestSupplierList 		= $this->db->select('id_supplier, nm_supplier')->order_by('nm_supplier', 'asc')->get_where('supplier', array('sts_aktif' => 'aktif'))->result_array();
		$RestCheckedSupplier 	= $this->db->select('id_supplier')->get_where('tran_rfq_header', array('no_rfq' => $no_rfq, 'deleted' => 'N'))->result_array();

		$ArrSupChecked = '';
		if (!empty($RestCheckedSupplier)) {
			$ArrData1 = array();
			foreach ($RestCheckedSupplier as $vaS => $vaA) {
				$ArrData1[] = $vaA['id_supplier'];
			}
			$ArrData1 		= implode(",", $ArrData1);
			$ArrSupChecked 	= explode(",", $ArrData1);
		}

		$data = array(
			'result' 			=> $result,
			'supplierList' 		=> $RestSupplierList,
			'supplierChecked' 	=> $ArrSupChecked,
			'no_rfq' 			=> $no_rfq
		);

		$this->load->view('Pembelian/modal_edit_rfq', $data);
	}

	public function update_rfq()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;
		$no_rfq			= $data['no_rfq'];
		$category		= $data['category'];
		$id_supplier	= $data['id_supplier'];
		$dateTime		= date('Y-m-d H:i:s');
		$UserName		= $data_session['ORI_User']['username'];

		$check			= $data['check'];
		$ArrList 		= array();
		foreach ($check as $vaxl) {
			$ArrList[$vaxl] = $vaxl;
		}
		$dtImplode		= "('" . implode("','", $ArrList) . "')";

		$qListG 	= "SELECT id, id_barang, nm_barang, SUM(qty) AS purchase, tgl_dibutuhkan, spec, info FROM tran_pr_detail WHERE no_pr IN " . $dtImplode . " GROUP BY id_barang";
		$restListG 	= $this->db->query($qListG)->result_array();

		$ArrDetail = array();
		$ArrHeader = array();
		$no = 0;
		foreach ($id_supplier as $sup => $supx) {
			$restSupplier	= $this->db->limit(1)->get_where('supplier', array('id_supplier' => $supx))->result();
			$SUM_MAT 		= 0;

			$no++;
			$num = sprintf('%03s', $no);
			foreach ($restListG as $val => $valx) {
				$SUM_MAT += $valx['purchase'];

				$ArrDetail[$sup . $val]['no_rfq'] 		= $no_rfq;
				$ArrDetail[$sup . $val]['hub_rfq'] 		= $no_rfq . '-' . $num;
				$ArrDetail[$sup . $val]['id_barang'] 		= $valx['id_barang'];
				$ArrDetail[$sup . $val]['nm_barang'] 		= $valx['nm_barang'];
				$ArrDetail[$sup . $val]['spec'] 			= $valx['spec'];
				$ArrDetail[$sup . $val]['info'] 			= $valx['info'];
				$ArrDetail[$sup . $val]['id_supplier'] 	= $supx;
				$ArrDetail[$sup . $val]['nm_supplier'] 	= $restSupplier[0]->nm_supplier;
				$ArrDetail[$sup . $val]['qty'] 		 	= $valx['purchase'];
				$ArrDetail[$sup . $val]['tgl_dibutuhkan'] = $valx['tgl_dibutuhkan'];
				$ArrDetail[$sup . $val]['created_by'] 	= $UserName;
				$ArrDetail[$sup . $val]['created_date'] 	= $dateTime;
			}

			$ArrHeader[$sup]['no_rfq'] 			= $no_rfq;
			$ArrHeader[$sup]['hub_rfq'] 		= $no_rfq . '-' . $num;
			$ArrHeader[$sup]['category'] 		= $category;
			$ArrHeader[$sup]['id_supplier'] 	= $supx;
			$ArrHeader[$sup]['nm_supplier'] 	= $restSupplier[0]->nm_supplier;
			$ArrHeader[$sup]['total_request'] 	= $SUM_MAT;
			$ArrHeader[$sup]['created_by'] 		= $UserName;
			$ArrHeader[$sup]['created_date'] 	= $dateTime;
			$ArrHeader[$sup]['updated_by'] 		= $UserName;
			$ArrHeader[$sup]['updated_date'] 	= $dateTime;
		}

		// print_r($ArrHeader);
		// print_r($ArrDetail);
		// exit;
		$this->db->trans_start();
		$this->db->delete('tran_rfq_header', array('no_rfq' => $no_rfq));
		$this->db->delete('tran_rfq_detail', array('no_rfq' => $no_rfq));
		$this->db->insert_batch('tran_rfq_header', $ArrHeader);
		$this->db->insert_batch('tran_rfq_detail', $ArrDetail);
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
			history('Update RFQ ' . $no_rfq);
		}
		echo json_encode($Arr_Data);
	}

	public function cancel_sebagian_rfq()
	{
		$data_session	= $this->session->userdata;
		$no_pr			= $this->uri->segment(3);
		$id_barang	= $this->uri->segment(4);
		$no_rfq			= $this->uri->segment(5);

		// echo $id."<br>";
		// echo $no_po."<br>";
		// echo $id_material."<br>";
		// exit;

		$ArrUpdateDetail = array(
			'deleted' => 'Y',
			'deleted_by' => $data_session['ORI_User']['username'],
			'deleted_date' => date('Y-m-d H:i:s')
		);

		$ArrUpdateD = array(
			'no_rfq' => NULL,
			'jenis_pembelian' => NULL
		);

		// print_r($ArrUpdateD);
		// exit;
		$this->db->trans_start();
		$this->db->where('no_rfq', $no_rfq);
		$this->db->where('id_barang', $id_barang);
		$this->db->update('tran_rfq_detail', $ArrUpdateDetail);

		$this->db->where('no_rfq', $no_rfq);
		$this->db->where('no_pr', $no_pr);
		$this->db->update('tran_pr_detail', $ArrUpdateD);

		$this->db->trans_complete();


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0,
				'no_po'		=> $no_rfq
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1,
				'no_po'		=> $no_rfq
			);
			history('Cancel Sebagian RFQ ' . $no_rfq . '/' . $id_barang . '/' . $no_pr);
		}
		echo json_encode($Arr_Data);
	}

	public function print_rfq()
	{
		$no_rfq		= $this->uri->segment(3);
		// echo $id_bq; exit;
		$data_session	= $this->session->userdata;
		$printby		= $data_session['ORI_User']['username'];
		$koneksi		= akses_server_side();

		$sroot 		= $_SERVER['DOCUMENT_ROOT'];
		include $sroot . "/application/views/Print/print_rfq_ast_rtn.php";

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];
		// $okeH  			= $this->session->userdata("ses_username");

		history('Print Request From Quotation ' . $no_rfq);

		print_rfq($Nama_Beda, $no_rfq, $koneksi, $printby);
	}

	public function hapus_rfq()
	{
		$data_session	= $this->session->userdata;
		$no_rfq			= $this->uri->segment(3);

		$ArrUpdateDetail = array(
			'deleted' => 'Y',
			'deleted_by' => $data_session['ORI_User']['username'],
			'deleted_date' => date('Y-m-d H:i:s')
		);

		$ArrUpdateD = array(
			'no_rfq' => NULL,
			'jenis_pembelian' => NULL,
			'checklist' => NULL,
			'checklist_by' => NULL
		);

		$this->db->trans_start();
		$this->db->where('no_rfq', $no_rfq);
		$this->db->update('tran_pr_detail', $ArrUpdateD);

		$this->db->where('no_rfq', $no_rfq);
		$this->db->update('tran_rfq_header', $ArrUpdateDetail);
		$this->db->trans_complete();


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0,
				'no_po'		=> $no_rfq
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1,
				'no_po'		=> $no_rfq
			);
			history('Delete RFQ ' . $no_rfq);
		}
		echo json_encode($Arr_Data);
	}


	//==================================================================================================================
	//=============================================REQUEST FOR QUOTATION================================================
	//==================================================================================================================


	public function index_perbandingan()
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
			'title'			=> 'Pembelian Non-Material & Jasa >> PO >> Table Perbandingan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'data_gudang'	=> $data_gudang
		);
		history('View Purchase Order Table Perbandingan');
		$this->load->view('Pembelian/perbandingan', $data);
	}

	public function get_data_json_perbandingan()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/perbandingan";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_perbandingan(
			$requestData['category'],
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

			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_rfq_header WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N'")->result_array();
			$arr_sup = array();
			foreach ($list_supplier as $val => $valx) {
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);

			$list_material		= $this->db->query("SELECT no_pr, nm_barang, qty, price_ref, price_ref_sup, tgl_dibutuhkan FROM tran_rfq_detail WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N' GROUP BY id_barang")->result_array();

			$arr_mat = array();
			$arr_qty = array();
			$arr_price = array();
			$arr_tgl = array();
			$arr_pr = array();
			foreach ($list_material as $val => $valx) {
				$arr_mat[$val] = $valx['nm_barang'];
				$arr_pr[$val] = $valx['no_pr'];
				$arr_qty[$val] = number_format($valx['qty'], 2);
				$arr_price[$val] = number_format($valx['price_ref']);
				$arr_tgl[$val] = date('d-M-Y', strtotime($valx['tgl_dibutuhkan']));
			}
			$dt_mat	= implode("<br>", $arr_mat);
			$dt_qty	= implode("<br>", $arr_qty);
			$dt_price	= implode("<br>", $arr_price);
			$dt_tgl	= implode("<br>", array_unique($arr_tgl));
			$dt_pr	= implode("<br>", array_unique($arr_pr));

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['no_rfq'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $dt_pr . "</div>";
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
			$nestedData[]	= "<div align='right'>" . $dt_price . "</div>";
			$nestedData[]	= "<div align='right'>" . $dt_qty . "</div>";
			$nestedData[]	= "<div align='center'>" . $dt_tgl . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['updated_by'] . "</div>";
			$nestedData[]	= "<div align='right'>" . date('d-M-Y H:i:s', strtotime($row['updated_date'])) . "</div>";

			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: " . color_status_purchase($row['sts_ajuan'])['color'] . "'>" . color_status_purchase($row['sts_ajuan'])['status'] . "</span></div>";
			$create	= "";
			$edit	= "";
			$booking	= "";
			$spk_ambil_mat	= "";
			$ajukan	= "";
			if ($row['sts_ajuan'] == 'OPN' and $row['sts_process'] == 'N') {
				$create	= "&nbsp;<a href='" . base_url('pembelian/add_perbandingan/' . $row['no_rfq']) . "' target='_blank' class='btn btn-sm btn-info' title='Add Perbandingan' data-role='qtip'><i class='fa fa-plus'></i></a>";
			}
			if ($row['sts_ajuan'] == 'PRS' and $row['sts_process'] == 'Y') {
				if ($Arr_Akses['update'] == '1') {
					$edit = "&nbsp;<a href='" . base_url('pembelian/add_perbandingan/' . $row['no_rfq']) . "' class='btn btn-sm btn-success editMat' title='Edit Material Purchase' data-no_rfq='" . $row['no_rfq'] . "'><i class='fa fa-edit'></i></a>";
				}
				if ($Arr_Akses['approve'] == '1') {
					$ajukan	= "&nbsp;<button type='button' class='btn btn-sm btn-primary ajukan' title='Ajukan Perbandingan' data-no_rfq='" . $row['no_rfq'] . "'><i class='fa fa-check'></i></button>";
				}
			}
			$nestedData[]	= "<div align='left'>
                                    <button type='button' class='btn btn-sm btn-warning detailMat' title='Detail' data-no_rfq='" . $row['no_rfq'] . "' data-status='" . $row['sts_ajuan'] . "'><i class='fa fa-eye'></i></button>
                                    " . $create . "
									" . $edit . "
									" . $booking . "
									" . $spk_ambil_mat . "
									" . $ajukan . "
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

	public function query_data_json_perbandingan($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($category <> '0') {
			$where = " AND a.category='" . $category . "' ";
		}

		$sql = "
			SELECT
				a.*
			FROM
				tran_rfq_header a
				LEFT JOIN tran_rfq_detail b ON a.no_rfq = b.no_rfq
		    WHERE 1=1 " . $where . " AND a.deleted = 'N' AND (
				a.no_rfq LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY a.no_rfq
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_rfq',
			2 => 'nm_supplier'
		);

		$sql .= " ORDER BY a.updated_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function modal_detail_perbandingan()
	{
		$no_rfq 	= $this->uri->segment(3);

		$sql 		= "	SELECT 
							a.*,
							b.alamat_supplier,
							b.lokasi,
							b.currency,
							b.kurs
						FROM 
							tran_rfq_detail a 
							LEFT JOIN tran_rfq_header b ON a.no_rfq=b.no_rfq
						WHERE 
							a.no_rfq='" . $no_rfq . "'
							AND a.hub_rfq=b.hub_rfq
							AND a.deleted='N' ORDER BY a.hub_rfq ASC
						";

		$result		= $this->db->query($sql)->result_array();

		$sql2 		= "	SELECT 
							a.*
						FROM 
							tran_rfq_detail a 
						WHERE 
							a.no_rfq='" . $no_rfq . "' 
							AND a.deleted='N' GROUP BY id_supplier
						";

		$num_rows		= $this->db->query($sql2)->num_rows();

		$data = array(
			'result' => $result,
			'num_rows' => $num_rows
		);

		$this->load->view('Pembelian/modal_detail_perbandingan', $data);
	}

	public function modal_detail_perbandingan_new()
	{
		$no_rfq 	= $this->uri->segment(3);

		$sql 		= "	SELECT 
							a.*,
							b.alamat_supplier,
							b.lokasi,
							b.currency,
							b.kurs
						FROM 
							tran_rfq_detail a 
							LEFT JOIN tran_rfq_header b ON a.no_rfq=b.no_rfq
						WHERE 
							a.no_rfq='" . $no_rfq . "'
							AND a.hub_rfq=b.hub_rfq
							AND a.deleted='N' ORDER BY a.id_barang,id_supplier ASC
						";

		$result	= $this->db->query($sql)->result();

		$sql2	= "	SELECT a.id_supplier,nm_supplier FROM tran_rfq_detail a WHERE a.no_rfq='" . $no_rfq . "' AND a.deleted='N' GROUP BY id_supplier,nm_supplier";

		$dt_supplier	= $this->db->query($sql2)->result();

		$data = array(
			'result' => $result,
			'dt_supplier' => $dt_supplier
		);

		$this->load->view('Pembelian/modal_detail_perbandingan_by_material', $data);
	}

	public function pengajuan_rfq()
	{
		$data_session	= $this->session->userdata;
		$no_rfq			= $this->uri->segment(3);
		// echo $no_po;
		// exit;

		$ArrUpdateH = array(
			'sts_ajuan' 	=> 'AJU',
			'updated_by' 	=> $data_session['ORI_User']['username'],
			'updated_date' 	=> date('Y-m-d H:i:s')
		);

		// print_r($ArrUpdate);
		// exit;
		$this->db->trans_start();
		$this->db->where('no_rfq', $no_rfq);
		$this->db->update('tran_rfq_header', $ArrUpdateH);
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
			history('Pengajuan Purchasing RFQ ' . $no_rfq);
		}
		echo json_encode($Arr_Data);
	}

	//==================================================================================================================
	//======================================================PENGAJUAN ==================================================
	//==================================================================================================================

	public function index_pengajuan()
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
			'title'			=> 'Table Pengajuan',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'data_gudang'	=> $data_gudang
		);
		history('View Table Pengajuan');
		$this->load->view('Pembelian/pengajuan', $data);
	}

	public function get_data_json_pengajuan()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/pengajuan";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_pengajuan(
			$requestData['category'],
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

			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_rfq_header WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N'")->result_array();
			$arr_sup = array();
			foreach ($list_supplier as $val => $valx) {
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);

			$list_material		= $this->db->query("SELECT nm_barang, qty, price_ref, price_ref_sup FROM tran_rfq_detail WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N' GROUP BY id_barang")->result_array();
			$arr_mat = array();
			foreach ($list_material as $val => $valx) {
				$arr_mat[$val] = strtoupper($valx['nm_barang']);
			}
			$dt_mat	= implode("<br>", $arr_mat);

			$arr_qty = array();
			foreach ($list_material as $val => $valx) {
				$arr_qty[$val] = number_format($valx['qty']);
			}
			$dt_qty	= implode("<br>", $arr_qty);

			$arr_price = array();
			foreach ($list_material as $val => $valx) {
				$arr_price[$val] = number_format($valx['price_ref']);
			}
			$dt_price	= implode("<br>", $arr_price);

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['no_rfq'] . "</div>";
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
			$nestedData[]	= "<div align='right'>" . $dt_price . "</div>";
			$nestedData[]	= "<div align='right'>" . $dt_qty . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['created_by'] . "</div>";
			$nestedData[]	= "<div align='right'>" . date('d F Y', strtotime($row['created_date'])) . "</div>";

			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: " . color_status_purchase($row['sts_ajuan'])['color'] . "'>" . color_status_purchase($row['sts_ajuan'])['status'] . "</span></div>";
			$ajukan	= "";
			$print	= "";
			$hasil_ajukan	= "";
			if ($row['sts_ajuan'] == 'AJU' and $row['sts_process'] == 'Y') {

				if ($Arr_Akses['approve'] == '1') {
					$ajukan	= "&nbsp;<button type='button' class='btn btn-sm btn-info ajukan' title='Pemilihan Supplier' data-no_rfq='" . $row['no_rfq'] . "' data-status='" . $row['sts_ajuan'] . "'><i class='fa fa-check'></i></button>";
				}
			}
			if (($row['sts_ajuan'] == 'APV' or $row['sts_ajuan'] == 'CLS') and $row['sts_process'] == 'Y') {
				$ajukan	= "&nbsp;<button type='button' class='btn btn-sm btn-success hasil_ajukan' title='Hasil Perbandingan' data-no_rfq='" . $row['no_rfq'] . "'><i class='fa fa-eye'></i></button>";
				$print	= "&nbsp;<a href='" . base_url('pembelian/print_hasil_pemilihan/' . $row['no_rfq']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print Hasil Perbandingan'><i class='fa fa-print'></i></a>";
			}
			$nestedData[]	= "<div align='left'>
                                    <button type='button' class='btn btn-sm btn-primary detailMat' title='Total Material Purchase' data-no_rfq='" . $row['no_rfq'] . "' data-status='" . $row['sts_ajuan'] . "'><i class='fa fa-eye'></i></button>
                                   " . $ajukan . "
								   " . $hasil_ajukan . "
								   " . $print . "
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

	public function query_data_json_pengajuan($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($category <> '0') {
			$where = " AND a.category='" . $category . "' ";
		}

		$sql = "
			SELECT
				a.*
			FROM
				tran_rfq_header a
		    WHERE  1=1 " . $where . " AND
				(a.sts_ajuan='AJU' OR a.sts_ajuan='CLS' OR a.sts_ajuan='APV') 
			AND (
				a.no_rfq LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY a.no_rfq
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_rfq',
			2 => 'nm_supplier'
		);

		$sql .= " ORDER BY a.updated_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function modal_detail_pengajuan()
	{
		$no_rfq 	= $this->uri->segment(3);

		$sql 		= "	SELECT 
							a.*,
							b.alamat_supplier,
							b.lokasi
						FROM 
							tran_rfq_detail a 
							LEFT JOIN tran_rfq_header b ON a.no_rfq=b.no_rfq
						WHERE 
							a.no_rfq='" . $no_rfq . "'
							AND a.hub_rfq=b.hub_rfq
							AND a.deleted='N' ORDER BY a.hub_rfq ASC
						";

		$result		= $this->db->query($sql)->result_array();

		$sql2 		= "	SELECT 
							a.*
						FROM 
							tran_rfq_detail a 
						WHERE 
							a.no_rfq='" . $no_rfq . "' 
							AND a.deleted='N' GROUP BY id_supplier
						";

		$num_rows		= $this->db->query($sql2)->num_rows();

		$data = array(
			'result' => $result,
			'num_rows' => $num_rows
		);

		$this->load->view('Pembelian/modal_detail_pengajuan', $data);
	}

	public function print_hasil_pemilihan()
	{
		$no_rfq			= $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $data_session['ORI_User']['username'];

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$data = array(
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'no_rfq' => $no_rfq
		);
		history('Print Hasil Pemilihan RFQ ' . $no_rfq);
		$this->load->view('Print/print_pemilihan_non_material', $data);
	}

	//==================================================================================================================
	//======================================================APPROVAL ==================================================
	//==================================================================================================================

	public function get_data_json_approval()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/approval";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_approval(
			$requestData['category'],
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

			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_rfq_header WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N'")->result_array();
			$arr_sup = array();
			foreach ($list_supplier as $val => $valx) {
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);

			$list_material		= $this->db->query("SELECT nm_barang, qty, price_ref, price_ref_sup FROM tran_rfq_detail WHERE no_rfq='" . $row['no_rfq'] . "' AND deleted='N' GROUP BY id_barang")->result_array();
			$arr_mat = array();
			foreach ($list_material as $val => $valx) {
				$arr_mat[$val] = strtoupper($valx['nm_barang']);
			}
			$dt_mat	= implode("<br>", $arr_mat);

			$arr_qty = array();
			foreach ($list_material as $val => $valx) {
				$arr_qty[$val] = number_format($valx['qty']);
			}
			$dt_qty	= implode("<br>", $arr_qty);

			$arr_price = array();
			foreach ($list_material as $val => $valx) {
				$arr_price[$val] = number_format($valx['price_ref']);
			}
			$dt_price	= implode("<br>", $arr_price);

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['no_rfq'] . "</div>";
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
			$nestedData[]	= "<div align='right'>" . $dt_price . "</div>";
			$nestedData[]	= "<div align='right'>" . $dt_qty . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['created_by'] . "</div>";
			$nestedData[]	= "<div align='right'>" . date('d F Y', strtotime($row['created_date'])) . "</div>";

			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: " . color_status_purchase($row['sts_ajuan'])['color'] . "'>" . color_status_purchase($row['sts_ajuan'])['status'] . "</span></div>";
			$ajukan	= "";
			if ($row['sts_ajuan'] == 'APV' and $row['sts_process'] == 'Y') {

				if ($Arr_Akses['approve'] == '1') {
					$ajukan	= "&nbsp;<button type='button' class='btn btn-sm btn-info approved' title='Approve' data-no_rfq='" . $row['no_rfq'] . "' data-status='" . $row['sts_ajuan'] . "'><i class='fa fa-check'></i></button>";
				}
			}
			$nestedData[]	= "<div align='left'>
                                    <button type='button' class='btn btn-sm btn-primary detailMat' title='Detail' data-no_rfq='" . $row['no_rfq'] . "' data-status='" . $row['sts_ajuan'] . "'><i class='fa fa-eye'></i></button>
                                   " . $ajukan . "
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

	public function query_data_json_approval($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($category <> '0') {
			$where = " AND a.category='" . $category . "' ";
		}

		$sql = "
			SELECT
				a.*
			FROM
				tran_rfq_header a
		    WHERE  1=1 " . $where . " AND  
				(a.sts_ajuan='CLS' OR a.sts_ajuan='APV') 
			AND (
				a.no_rfq LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY a.no_rfq
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_rfq',
			2 => 'nm_supplier'
		);

		$sql .= " ORDER BY a.updated_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function modal_detail_approve()
	{
		$no_rfq 	= $this->uri->segment(3);

		$sql 		= "	SELECT 
							a.*,
							b.alamat_supplier,
							b.lokasi
						FROM 
							tran_rfq_detail a 
							LEFT JOIN tran_rfq_header b ON a.no_rfq=b.no_rfq
						WHERE 
							a.no_rfq='" . $no_rfq . "'
							AND a.hub_rfq=b.hub_rfq
							AND (a.status='SETUJU' OR a.status='CLOSE')
							AND (b.sts_ajuan = 'APV' OR b.sts_ajuan = 'CLS')
							AND a.deleted='N' ORDER BY a.hub_rfq ASC
						";

		$result		= $this->db->query($sql)->result_array();

		$sql2 		= "	SELECT 
							a.*
						FROM 
							tran_rfq_detail a 
							LEFT JOIN tran_rfq_header b ON a.no_rfq=b.no_rfq
						WHERE 
							a.no_rfq='" . $no_rfq . "' 
							AND a.hub_rfq=b.hub_rfq
							AND (b.sts_ajuan = 'APV' OR b.sts_ajuan = 'CLS')
							AND (a.status='SETUJU' OR a.status='CLOSE')
							AND a.deleted='N' GROUP BY id_supplier
						";

		$num_rows		= $this->db->query($sql2)->num_rows();

		$data = array(
			'result' => $result,
			'num_rows' => $num_rows
		);

		$this->load->view('Pembelian/modal_detail_approve', $data);
	}

	//==================================================================================================================
	//==================================================PURCHASE ORDER==================================================
	//==================================================================================================================

	public function index_purchase_order()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)) . '/' . strtolower($this->uri->segment(2)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data = array(
			'title'			=> 'Pembelian Non-Material & Jasa >> PO >> Monitoring Purchase Order',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses
		);
		history('View purchase order non material');
		$this->load->view('Pembelian/purchase_order', $data);
	}

	public function get_data_json_purchase_order()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/purchase_order";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_purchase_order(
			$requestData['category'],
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

			//NO PR
			$list_pr		= $this->db->select('no_pr')->get_where('tran_rfq_detail', array('no_po' => $row['no_po']))->result_array();
			$arr_pr = array();
			foreach ($list_pr as $val => $valx) {
				$arr_pr[$val] = $valx['no_pr'];
			}
			$arr_pr = array_unique($arr_pr);
			$dt_pr	= implode("<br>", $arr_pr);

			$list_material		= $this->db->query("SELECT nm_barang, qty_purchase, price_ref, price_ref_sup, net_price FROM tran_po_detail WHERE no_po='" . $row['no_po'] . "' GROUP BY id_barang")->result_array();
			if ($row['status'] != 'DELETED') {
				$list_material	= $this->db->query("SELECT nm_barang, qty_purchase, price_ref, price_ref_sup, net_price FROM tran_po_detail WHERE no_po='" . $row['no_po'] . "' AND deleted='N' GROUP BY id_barang")->result_array();
			}

			$arr_mat = array();
			foreach ($list_material as $val => $valx) {
				$arr_mat[$val] = strtoupper($valx['nm_barang']);
			}
			$dt_mat	= implode("<br>", $arr_mat);

			$arr_qty = array();
			foreach ($list_material as $val => $valx) {
				$arr_qty[$val] = number_format($valx['qty_purchase'], 2);
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
			$nestedData[]	= "<div align='center'>" . $dt_pr . "</div>";
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
			$status_po = $row['status_po'];
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
			if ($row['status_po'] == 'CLS') {
				$warna = 'bg-red';
				$status = 'CLOSE';
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
			$repeat_po = "";
			$request_payment = "";
			$close_po = "";
			$request_payment = "&nbsp;<button type='button' class='btn btn-sm btn-primary request_payment' title='Request Payment' data-no_po='" . $row['no_po'] . "'><i class='fa fa-money'></i></button>";
			if ($row['status'] == 'IN PARSIAL') {
				$close_po = "&nbsp;<button type='button' class='btn btn-sm btn-danger close_po' title='Close PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-check'></i></button>";
			}
			if ($row['status'] == 'WAITING IN' and $row['status1'] == 'Y' and $row['status2'] == 'Y') {
				$edit_print	= "&nbsp;<button type='button' class='btn btn-sm btn-warning edit_po' title='Edit Print PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-pencil'></i></button>";
				$print_po	= "&nbsp;<a href='" . base_url('pembelian/print_po2/' . $row['no_po']) . "' target='_blank' class='btn btn-sm btn-info' title='Print PO' data-role='qtip'><i class='fa fa-print'></i></a>";
				$close_po = "&nbsp;<button type='button' class='btn btn-sm btn-danger close_po' title='Close PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-check'></i></button>";
				// $edit_po	= "&nbsp;<button type='button' class='btn btn-sm btn-success edit_po_qty' title='Edit PO' data-no_po='".$row['no_po']."'><i class='fa fa-edit'></i></button>";
				// $delete_po	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete_po' title='Delete PO' data-no_po='".$row['no_po']."'><i class='fa fa-trash'></i></button>";
			}
			if ($row['status'] == 'WAITING IN' and $row['status1'] == 'N' and $row['status2'] == 'N') {
				$request_payment = "";
				$edit_print	= "&nbsp;<button type='button' class='btn btn-sm btn-warning edit_po' title='Edit Print PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-pencil'></i></button>";
				$print_po	= "&nbsp;<a href='" . base_url('pembelian/print_po2/' . $row['no_po']) . "' target='_blank' class='btn btn-sm btn-info' title='Print PO' data-role='qtip'><i class='fa fa-print'></i></a>";
				$edit_po	= "&nbsp;<button type='button' class='btn btn-sm btn-success edit_po_qty' title='Edit PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-edit'></i></button>";
				$delete_po	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete_po' title='Delete PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-trash'></i></button>";
			}

			if (!empty($row['valid_date']) and $row['valid_date'] >= date('Y-m-d')) {
				$repeat_po	= "&nbsp;<button type='button' class='btn btn-sm bg-purple repeat_po' title='Repeat PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-retweet'></i></button>";
				$print_po	= "&nbsp;<a href='" . base_url('pembelian/print_po2/' . $row['no_po']) . "' target='_blank' class='btn btn-sm btn-info' title='Print PO' data-role='qtip'><i class='fa fa-print'></i></a>";
			}


			$nestedData[]	= "	<div align='left'>
                                    <button type='button' class='btn btn-sm btn-default detailMat' title='Detail PO' data-no_po='" . $row['no_po'] . "'><i class='fa fa-eye'></i></button>
									" . $edit_po . "
									" . $edit_print . "
									" . $print_po . "
									" . $delete_po . "
									" . $request_payment . "
									" . $close_po . "
									" . $repeat_po . "
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

	public function query_data_json_purchase_order($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$where = "";
		if ($category <> '0') {
			$where = " AND a.category='" . $category . "' ";
		}

		$sql = "
			SELECT
			(@row:=@row+1) AS nomor,
				a.*
			FROM
				tran_po_detail b
				LEFT JOIN tran_po_header a ON a.no_po=b.no_po,
				(SELECT @row:=0) r
			WHERE 1=1 AND a.repeat_po IS NULL AND a.status_id = '1' " . $where . "
			AND (
				a.no_po LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.created_by LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY b.no_po
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_po',
			2 => 'category',
			3 => 'nm_supplier'
		);

		$sql .= " ORDER BY a.updated_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function print_po()
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
		$this->load->view('Print/print_po_non_material', $data);
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
		$this->load->view('Print/print_po_non_material_new', $data);
	}

	public function get_add()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$payment = $this->db->get_where('list_help', array('group_by' => 'top'))->result_array();

		$d_Header = "";
		$d_Header .= "<tr class='header_" . $id . "'>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='detail_po[" . $id . "][group_top]' class='form-control text-left chosen_select' value='" . $id . "'>";
		$d_Header .= "<option value='0'>Select Group TOP</option>";
		foreach ($payment as $val => $valx) {
			$d_Header .= "<option value='" . $valx['name'] . "'>" . strtoupper($valx['name']) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "<input type='hidden' name='detail_po[" . $id . "][term]' class='form-control text-center input-md' value='" . $id . "'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'><input type='text' id='progress_" . $id . "' name='detail_po[" . $id . "][progress]' class='form-control input-md text-center maskM progress_term' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
		$d_Header .= "<td align='left' class='hidden'><input type='text' id='usd_" . $id . "' name='detail_po[" . $id . "][value_usd]' class='form-control input-md text-right maskM sum_tot_usd' tabindex='-1' readonly></td>";
		$d_Header .= "<td align='left'><input type='text' id='idr_" . $id . "' name='detail_po[" . $id . "][value_idr]' class='form-control input-md text-right maskM sum_tot_idr' ></td>";
		$d_Header .= "<td align='left'><input type='text' id='total_harga_" . $id . "' name='detail_po[" . $id . "][keterangan]' class='form-control input-md text-left'></td>";
		$d_Header .= "<td align='left'><input type='text' name='detail_po[" . $id . "][jatuh_tempo]' class='form-control input-md text-center datepicker' readonly></td>";
		$d_Header .= "<td align='left'><input type='text' name='detail_po[" . $id . "][syarat]' class='form-control input-md'></td>";
		$d_Header .= "<td align='center'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";


		//add part
		$d_Header .= "<tr id='add_" . $id . "'>";
		$d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' title='Add TOP'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add TOP</button></td>";
		$d_Header .= "<td align='center' colspan='7'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_kurs()
	{
		$id 	= $this->uri->segment(3);
		$query	= $this->db->query("SELECT a.kurs FROM kurs a WHERE a.kode_dari='" . $id . "'")->result();

		echo json_encode(array(
			'kurs'	=> 1, //$query[0]->kurs,
		));
	}

	public function delete_sebagian_po()
	{
		$data_session	= $this->session->userdata;
		$data		= $this->input->post();

		$detail_ch 	= $data['checked'];
		$no_po 		= $data['no_po'];

		$header_po 	= $this->db->get_where('tran_po_header', array('no_po' => $no_po))->result();
		$detail 	= $this->db->select('*')->from('tran_po_detail')->where_in('id', $detail_ch)->where('no_po', $no_po)->where('deleted', 'N')->get()->result_array();

		//Urutab PR
		$Ym = date('ym');
		$qIPP			= "SELECT MAX(no_pr) as maxP FROM tran_pr_header WHERE no_pr LIKE 'PR" . $Ym . "%' ";
		$numrowIPP		= $this->db->query($qIPP)->num_rows();
		$resultIPP		= $this->db->query($qIPP)->result_array();
		$angkaUrut2		= $resultIPP[0]['maxP'];
		$urutan2PR		= (int)substr($angkaUrut2, 6, 4);

		//Urutab PR GROUP
		$Ym = date('ym');
		$qIPPX			= "SELECT MAX(no_pr_group) as maxP FROM tran_pr_header WHERE no_pr_group LIKE 'PR" . $Ym . "%' ";
		$numrowIPPX		= $this->db->query($qIPPX)->num_rows();
		$resultIPPX		= $this->db->query($qIPPX)->result_array();
		$angkaUrut2X	= $resultIPPX[0]['maxP'];
		$urutan2X		= (int)substr($angkaUrut2X, 6, 4);
		$urutan2X++;
		$urut2X			= sprintf('%04s', $urutan2X);
		$no_pr_group	= "PR" . $Ym . $urut2X;

		//Urutab RFQ
		$srcMtr			= "SELECT MAX(no_rfq) as maxP FROM tran_rfq_header WHERE no_rfq LIKE 'RFQX" . $Ym . "%' ";
		$numrowMtr		= $this->db->query($srcMtr)->num_rows();
		$resultMtr		= $this->db->query($srcMtr)->result_array();
		$angkaUrut2		= $resultMtr[0]['maxP'];
		$urutan2		= (int)substr($angkaUrut2, 8, 4);
		$urutan2++;
		$urut2			= sprintf('%04s', $urutan2);
		$no_rfq			= "RFQX" . $Ym . $urut2;

		$ArrEdit = [];
		$ArrAddPRHeader = [];
		$ArrAddPR = [];
		$ArrAddRFQ = [];

		$SUM_MAT = 0;
		if (!empty($detail)) {
			foreach ($detail as $val => $valx) {
				$urutan2PR++;
				$ArrEdit[$val]['id'] 			= $valx['id'];
				$ArrEdit[$val]['deleted'] 		= 'Y';
				$ArrEdit[$val]['deleted_by'] 	= $data_session['ORI_User']['username'];
				$ArrEdit[$val]['deleted_date'] = date('Y-m-d H:i:s');

				$SUM_MAT += $valx['qty_purchase'];

				$urut2X			= sprintf('%04s', $urutan2PR);

				$ArrAddPR[$val]['no_pr'] 			= "PR" . $Ym . $urut2X;
				$ArrAddPR[$val]['no_pr_group'] 		= $no_pr_group;
				$ArrAddPR[$val]['tgl_pr'] 			= date('Y-m-d');
				$ArrAddPR[$val]['category'] 		= $header_po[0]->category;
				$ArrAddPR[$val]['jenis_pembelian'] 	= 'po';
				$ArrAddPR[$val]['no_rfq'] 			= $no_rfq;
				$ArrAddPR[$val]['id_barang'] 		= $valx['id_barang'];
				$ArrAddPR[$val]['nm_barang'] 		= $valx['nm_barang'];
				$ArrAddPR[$val]['qty'] 				= $valx['qty_purchase'];
				$ArrAddPR[$val]['tgl_dibutuhkan'] 	= $valx['tgl_dibutuhkan'];
				$ArrAddPR[$val]['created_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPR[$val]['created_date'] 	= date('Y-m-d H:i:s');

				$ArrAddPRHeader[$val]['no_pr'] 			= "PR" . $Ym . $urut2X;
				$ArrAddPRHeader[$val]['no_pr_group'] 	= $no_pr_group;
				$ArrAddPRHeader[$val]['tgl_pr'] 		= date('Y-m-d');
				$ArrAddPRHeader[$val]['category'] 		= $header_po[0]->category;
				$ArrAddPRHeader[$val]['created_by'] 	= $data_session['ORI_User']['username'];
				$ArrAddPRHeader[$val]['created_date'] 	= date('Y-m-d H:i:s');
				$ArrAddPRHeader[$val]['app_status'] 	= 'Y';
				$ArrAddPRHeader[$val]['app_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPRHeader[$val]['app_date'] 		= date('Y-m-d H:i:s');

				$ArrAddRFQ[$val]['no_rfq'] 		= $no_rfq;
				$ArrAddRFQ[$val]['hub_rfq'] 	= $no_rfq . "-001";
				$ArrAddRFQ[$val]['id_barang'] 	= $valx['id_barang'];
				$ArrAddRFQ[$val]['nm_barang'] 	= $valx['nm_barang'];
				$ArrAddRFQ[$val]['id_supplier'] = $header_po[0]->id_supplier;
				$ArrAddRFQ[$val]['nm_supplier'] = $header_po[0]->nm_supplier;
				$ArrAddRFQ[$val]['qty'] 		= $valx['qty_purchase'];
				$ArrAddRFQ[$val]['moq'] 			= $valx['moq'];
				$ArrAddRFQ[$val]['tgl_dibutuhkan'] 	= $valx['tgl_dibutuhkan'];
				$ArrAddRFQ[$val]['created_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddRFQ[$val]['created_date'] 	= date('Y-m-d H:i:s');
			}
		}


		$ArrAddRFQHeader = [
			'no_rfq' => $no_rfq,
			'hub_rfq' => $no_rfq . "-001",
			'category' => $header_po[0]->category,
			'id_supplier' => $header_po[0]->id_supplier,
			'nm_supplier' => $header_po[0]->nm_supplier,
			'total_request' => $SUM_MAT,
			'created_by' => $data_session['ORI_User']['username'],
			'created_date' => date('Y-m-d H:i:s'),
			'updated_by' => $data_session['ORI_User']['username'],
			'updated_date' => date('Y-m-d H:i:s')
		];

		// $ArrEditHeader = [
		// 'status' => 'DELETED',
		// 'deleted' => 'Y',
		// 'deleted_by' => $data_session['ORI_User']['username'],
		// 'deleted_date' => date('Y-m-d H:i:s')
		// ];

		// print_r($ArrAddPRHeader);
		// print_r($ArrAddPR);

		// print_r($ArrAddRFQHeader);
		// print_r($ArrAddRFQ);

		// print_r($ArrEdit);
		// exit;
		$this->db->trans_start();
		if (!empty($ArrEdit)) {
			$this->db->update_batch('tran_po_detail', $ArrEdit, 'id');
		}

		if (!empty($ArrAddPR)) {
			$this->db->insert_batch('tran_pr_detail', $ArrAddPR);
			$this->db->insert_batch('tran_pr_header', $ArrAddPRHeader);
		}

		if (!empty($ArrAddRFQ)) {
			$this->db->insert_batch('tran_rfq_detail', $ArrAddRFQ);
			$this->db->insert('tran_rfq_header', $ArrAddRFQHeader);
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
			history('Delete sebagian PO : ' . $no_po);
		}
		echo json_encode($Arr_Data);
	}

	public function delete_sebagian_po_new()
	{
		$data_session	= $this->session->userdata;
		$data		= $this->input->post();

		// $detail_ch 	= $data['checked'];
		$id 		= $data['id'];
		$no_po 		= $data['no_po'];

		$header_po 	= $this->db->get_where('tran_po_header', array('no_po' => $no_po))->result();
		$detail 	= $this->db->select('*')->from('tran_po_detail')->where('id', $id)->where('no_po', $no_po)->where('deleted', 'N')->get()->result_array();

		//Urutab PR
		$Ym = date('ym');
		$qIPP			= "SELECT MAX(no_pr) as maxP FROM tran_pr_header WHERE no_pr LIKE 'PR" . $Ym . "%' ";
		$numrowIPP		= $this->db->query($qIPP)->num_rows();
		$resultIPP		= $this->db->query($qIPP)->result_array();
		$angkaUrut2		= $resultIPP[0]['maxP'];
		$urutan2PR		= (int)substr($angkaUrut2, 6, 4);

		//Urutab PR GROUP
		$Ym = date('ym');
		$qIPPX			= "SELECT MAX(no_pr_group) as maxP FROM tran_pr_header WHERE no_pr_group LIKE 'PR" . $Ym . "%' ";
		$numrowIPPX		= $this->db->query($qIPPX)->num_rows();
		$resultIPPX		= $this->db->query($qIPPX)->result_array();
		$angkaUrut2X	= $resultIPPX[0]['maxP'];
		$urutan2X		= (int)substr($angkaUrut2X, 6, 4);
		$urutan2X++;
		$urut2X			= sprintf('%04s', $urutan2X);
		$no_pr_group			= "PR" . $Ym . $urut2X;

		//Urutab RFQ
		// $srcMtr			= "SELECT MAX(no_rfq) as maxP FROM tran_rfq_header WHERE no_rfq LIKE 'RFQX".$Ym."%' ";
		// $numrowMtr		= $this->db->query($srcMtr)->num_rows();
		// $resultMtr		= $this->db->query($srcMtr)->result_array();
		// $angkaUrut2		= $resultMtr[0]['maxP'];
		// $urutan2		= (int)substr($angkaUrut2, 8, 4);
		// $urutan2++;
		// $urut2			= sprintf('%04s',$urutan2);
		// $no_rfq			= "RFQX".$Ym.$urut2;

		$ArrEdit = [];
		$ArrAddPRHeader = [];
		$ArrAddPR = [];
		$ArrAddRFQ = [];

		$SUM_MAT = 0;
		if (!empty($detail)) {
			foreach ($detail as $val => $valx) {
				$urutan2PR++;
				$ArrEdit[$val]['id'] 			= $valx['id'];
				$ArrEdit[$val]['deleted'] 		= 'Y';
				$ArrEdit[$val]['deleted_by'] 	= $data_session['ORI_User']['username'];
				$ArrEdit[$val]['deleted_date'] = date('Y-m-d H:i:s');

				$SUM_MAT += $valx['qty_purchase'];

				$urut2X			= sprintf('%04s', $urutan2PR);

				$ArrAddPR[$val]['no_pr'] 			= "PR" . $Ym . $urut2X;
				$ArrAddPR[$val]['no_pr_group'] 		= $no_pr_group;
				$ArrAddPR[$val]['tgl_pr'] 			= date('Y-m-d');
				$ArrAddPR[$val]['category'] 		= $header_po[0]->category;
				// $ArrAddPR[$val]['jenis_pembelian'] 	= 'po';
				// $ArrAddPR[$val]['no_rfq'] 			= $no_rfq;
				$ArrAddPR[$val]['id_barang'] 		= $valx['id_barang'];
				$ArrAddPR[$val]['nm_barang'] 		= $valx['nm_barang'];
				$ArrAddPR[$val]['qty'] 				= $valx['qty_purchase'];
				$ArrAddPR[$val]['tgl_dibutuhkan'] 	= $valx['tgl_dibutuhkan'];
				$ArrAddPR[$val]['created_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPR[$val]['created_date'] 	= date('Y-m-d H:i:s');
				$ArrAddPR[$val]['app_status'] 	= 'Y';
				$ArrAddPR[$val]['app_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPR[$val]['app_date'] 		= date('Y-m-d H:i:s');

				$ArrAddPRHeader[$val]['no_pr'] 			= "PR" . $Ym . $urut2X;
				$ArrAddPRHeader[$val]['no_pr_group'] 	= $no_pr_group;
				$ArrAddPRHeader[$val]['tgl_pr'] 		= date('Y-m-d');
				$ArrAddPRHeader[$val]['category'] 		= $header_po[0]->category;
				$ArrAddPRHeader[$val]['created_by'] 	= $data_session['ORI_User']['username'];
				$ArrAddPRHeader[$val]['created_date'] 	= date('Y-m-d H:i:s');
				$ArrAddPRHeader[$val]['app_status'] 	= 'Y';
				$ArrAddPRHeader[$val]['app_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPRHeader[$val]['app_date'] 		= date('Y-m-d H:i:s');

				// $ArrAddRFQ[$val]['no_rfq'] 		= $no_rfq;
				// $ArrAddRFQ[$val]['hub_rfq'] 	= $no_rfq."-001";
				// $ArrAddRFQ[$val]['id_barang'] 	= $valx['id_barang'];
				// $ArrAddRFQ[$val]['nm_barang'] 	= $valx['nm_barang'];
				// $ArrAddRFQ[$val]['id_supplier'] = $header_po[0]->id_supplier;
				// $ArrAddRFQ[$val]['nm_supplier'] = $header_po[0]->nm_supplier;
				// $ArrAddRFQ[$val]['qty'] 		= $valx['qty_purchase'];
				// $ArrAddRFQ[$val]['moq'] 			= $valx['moq'];
				// $ArrAddRFQ[$val]['tgl_dibutuhkan'] 	= $valx['tgl_dibutuhkan'];
				// $ArrAddRFQ[$val]['created_by'] 		= $data_session['ORI_User']['username'];
				// $ArrAddRFQ[$val]['created_date'] 	= date('Y-m-d H:i:s');
			}
		}


		// $ArrAddRFQHeader = [
		// 'no_rfq' => $no_rfq,
		// 'hub_rfq' => $no_rfq."-001",
		// 'category' => $header_po[0]->category,
		// 'id_supplier' => $header_po[0]->id_supplier,
		// 'nm_supplier' => $header_po[0]->nm_supplier,
		// 'total_request' => $SUM_MAT,
		// 'created_by' => $data_session['ORI_User']['username'],
		// 'created_date' => date('Y-m-d H:i:s'),
		// 'updated_by' => $data_session['ORI_User']['username'],
		// 'updated_date' => date('Y-m-d H:i:s')
		// ];

		// $ArrEditHeader = [
		// 'status' => 'DELETED',
		// 'deleted' => 'Y',
		// 'deleted_by' => $data_session['ORI_User']['username'],
		// 'deleted_date' => date('Y-m-d H:i:s')
		// ];

		// print_r($ArrAddPRHeader);
		// print_r($ArrAddPR);

		// print_r($ArrAddRFQHeader);
		// print_r($ArrAddRFQ);

		// print_r($ArrEdit);
		// exit;
		$this->db->trans_start();
		if (!empty($ArrEdit)) {
			$this->db->update_batch('tran_po_detail', $ArrEdit, 'id');
		}

		if (!empty($ArrAddPR)) {
			$this->db->insert_batch('tran_pr_detail', $ArrAddPR);
			$this->db->insert_batch('tran_pr_header', $ArrAddPRHeader);
		}

		// if(!empty($ArrAddRFQ)){
		// $this->db->insert_batch('tran_rfq_detail', $ArrAddRFQ);
		// $this->db->insert('tran_rfq_header', $ArrAddRFQHeader);
		// }
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
			history('Delete sebagian PO : ' . $no_po);
		}
		echo json_encode($Arr_Data);
	}


	public function delete_semua_po()
	{
		$data_session	= $this->session->userdata;
		$data	= $this->input->post();

		$no_po = $data['no_po'];

		$header_po 	= $this->db->get_where('tran_po_header', array('no_po' => $no_po))->result();
		$detail 	= $this->db->get_where('tran_po_detail', array('no_po' => $no_po, 'deleted' => 'N'))->result_array();

		//Urutab PR
		$Ym = date('ym');
		$qIPP			= "SELECT MAX(no_pr) as maxP FROM tran_pr_header WHERE no_pr LIKE 'PR" . $Ym . "%' ";
		$numrowIPP		= $this->db->query($qIPP)->num_rows();
		$resultIPP		= $this->db->query($qIPP)->result_array();
		$angkaUrut2		= $resultIPP[0]['maxP'];
		$urutan2PR		= (int)substr($angkaUrut2, 6, 4);

		//Urutab PR GROUP
		$Ym = date('ym');
		$qIPPX			= "SELECT MAX(no_pr_group) as maxP FROM tran_pr_header WHERE no_pr_group LIKE 'PR" . $Ym . "%' ";
		$numrowIPPX		= $this->db->query($qIPPX)->num_rows();
		$resultIPPX		= $this->db->query($qIPPX)->result_array();
		$angkaUrut2X	= $resultIPPX[0]['maxP'];
		$urutan2X		= (int)substr($angkaUrut2X, 6, 4);
		$urutan2X++;
		$urut2X			= sprintf('%04s', $urutan2X);
		$no_pr_group			= "PR" . $Ym . $urut2X;

		//Urutab RFQ
		// $srcMtr			= "SELECT MAX(no_rfq) as maxP FROM tran_rfq_header WHERE no_rfq LIKE 'RFQX".$Ym."%' ";
		// $numrowMtr		= $this->db->query($srcMtr)->num_rows();
		// $resultMtr		= $this->db->query($srcMtr)->result_array();
		// $angkaUrut2		= $resultMtr[0]['maxP'];
		// $urutan2		= (int)substr($angkaUrut2, 8, 4);
		// $urutan2++;
		// $urut2			= sprintf('%04s',$urutan2);
		// $no_rfq			= "RFQX".$Ym.$urut2;

		$ArrEdit = [];
		$ArrAddPRHeader = [];
		$ArrAddPR = [];
		$ArrAddRFQ = [];

		$SUM_MAT = 0;
		if (!empty($detail)) {
			foreach ($detail as $val => $valx) {
				$urutan2PR++;
				$ArrEdit[$val]['id'] 			= $valx['id'];
				$ArrEdit[$val]['deleted'] 		= 'Y';
				$ArrEdit[$val]['deleted_by'] 	= $data_session['ORI_User']['username'];
				$ArrEdit[$val]['deleted_date'] = date('Y-m-d H:i:s');

				$SUM_MAT += $valx['qty_purchase'];

				$urut2X			= sprintf('%04s', $urutan2PR);

				$ArrAddPR[$val]['no_pr'] 			= "PR" . $Ym . $urut2X;
				$ArrAddPR[$val]['no_pr_group'] 		= $no_pr_group;
				$ArrAddPR[$val]['tgl_pr'] 			= date('Y-m-d');
				$ArrAddPR[$val]['category'] 		= $header_po[0]->category;
				// $ArrAddPR[$val]['jenis_pembelian'] 	= 'po';
				// $ArrAddPR[$val]['no_rfq'] 			= $no_rfq;
				$ArrAddPR[$val]['id_barang'] 		= $valx['id_barang'];
				$ArrAddPR[$val]['nm_barang'] 		= $valx['nm_barang'];
				$ArrAddPR[$val]['qty'] 				= $valx['qty_purchase'];
				$ArrAddPR[$val]['tgl_dibutuhkan'] 	= $valx['tgl_dibutuhkan'];
				$ArrAddPR[$val]['created_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPR[$val]['created_date'] 	= date('Y-m-d H:i:s');
				$ArrAddPR[$val]['app_status'] 	= 'Y';
				$ArrAddPR[$val]['app_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPR[$val]['app_date'] 		= date('Y-m-d H:i:s');

				$ArrAddPRHeader[$val]['no_pr'] 			= "PR" . $Ym . $urut2X;
				$ArrAddPRHeader[$val]['no_pr_group'] 	= $no_pr_group;
				$ArrAddPRHeader[$val]['tgl_pr'] 		= date('Y-m-d');
				$ArrAddPRHeader[$val]['category'] 		= $header_po[0]->category;
				$ArrAddPRHeader[$val]['created_by'] 	= $data_session['ORI_User']['username'];
				$ArrAddPRHeader[$val]['created_date'] 	= date('Y-m-d H:i:s');
				$ArrAddPRHeader[$val]['app_status'] 	= 'Y';
				$ArrAddPRHeader[$val]['app_by'] 		= $data_session['ORI_User']['username'];
				$ArrAddPRHeader[$val]['app_date'] 		= date('Y-m-d H:i:s');

				// $ArrAddRFQ[$val]['no_rfq'] 		= $no_rfq;
				// $ArrAddRFQ[$val]['hub_rfq'] 	= $no_rfq."-001";
				// $ArrAddRFQ[$val]['id_barang'] 	= $valx['id_barang'];
				// $ArrAddRFQ[$val]['nm_barang'] 	= $valx['nm_barang'];
				// $ArrAddRFQ[$val]['id_supplier'] = $header_po[0]->id_supplier;
				// $ArrAddRFQ[$val]['nm_supplier'] = $header_po[0]->nm_supplier;
				// $ArrAddRFQ[$val]['qty'] 		= $valx['qty_purchase'];
				// $ArrAddRFQ[$val]['moq'] 			= $valx['moq'];
				// $ArrAddRFQ[$val]['tgl_dibutuhkan'] 	= $valx['tgl_dibutuhkan'];
				// $ArrAddRFQ[$val]['created_by'] 		= $data_session['ORI_User']['username'];
				// $ArrAddRFQ[$val]['created_date'] 	= date('Y-m-d H:i:s');
			}
		}


		// $ArrAddRFQHeader = [
		// 'no_rfq' => $no_rfq,
		// 'hub_rfq' => $no_rfq."-001",
		// 'category' => $header_po[0]->category,
		// 'id_supplier' => $header_po[0]->id_supplier,
		// 'nm_supplier' => $header_po[0]->nm_supplier,
		// 'total_request' => $SUM_MAT,
		// 'created_by' => $data_session['ORI_User']['username'],
		// 'created_date' => date('Y-m-d H:i:s'),
		// 'updated_by' => $data_session['ORI_User']['username'],
		// 'updated_date' => date('Y-m-d H:i:s')
		// ];

		$ArrEditHeader = [
			'status' => 'DELETED',
			'deleted' => 'Y',
			'deleted_by' => $data_session['ORI_User']['username'],
			'deleted_date' => date('Y-m-d H:i:s')
		];

		// print_r($ArrAddPRHeader);
		// print_r($ArrAddPR);

		// print_r($ArrAddRFQHeader);
		// print_r($ArrAddRFQ);

		// print_r($ArrEditHeader);
		// print_r($ArrEdit);
		// exit;
		$this->db->trans_start();
		$this->db->where('no_po', $no_po);
		$this->db->update('tran_po_header', $ArrEditHeader);

		if (!empty($ArrEdit)) {
			$this->db->update_batch('tran_po_detail', $ArrEdit, 'id');
		}

		if (!empty($ArrAddPR)) {
			$this->db->insert_batch('tran_pr_detail', $ArrAddPR);
			$this->db->insert_batch('tran_pr_header', $ArrAddPRHeader);
		}

		// if(!empty($ArrAddRFQ)){
		// $this->db->insert_batch('tran_rfq_detail', $ArrAddRFQ);
		// $this->db->insert('tran_rfq_header', $ArrAddRFQHeader);
		// }
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
			history('Delete semua PO : ' . $no_po . ' / ' . $no_pr_group);
		}
		echo json_encode($Arr_Data);
	}


	//==================================================================================================================
	//==============================================NON PURCHASE ORDER==================================================
	//==================================================================================================================

	public function approval_non_po()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . '/approval_non_po';
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$tanda				= $this->uri->segment(3);
		if ($tanda == 'approval') {
			$tandax = 'Approval';
		} elseif ($tanda == 'non_po') {
			$tandax = 'List';
		} else {
			$tandax = 'Pengajuan';
		}
		$data = array(
			'title'			=> 'Pembelian Non-Material & Jasa >> Non-PO >> ' . $tandax . ' Non PO',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'tanda'			=> $tanda
		);
		history('View Data Approval Pengajuan Non PO');
		$this->load->view('Pembelian/approval_non_po', $data);
	}

	public function get_data_json_app_non_po()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/approval_non_po";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_app_non_po(
			$requestData['tanda'],
			$requestData['category'],
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

			$tanda = $requestData['tanda'];

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
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
			$nestedData[]	= "<div align='center'>" . strtoupper($row['no_non_po']) . "</div>";
			$nestedData[]	= "<div align='center'><span class='badge' style='background-color: " . $warna . ";'>" . strtoupper($category) . "</span></div>";

			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_dept']) . "</div>";

			$list_barang	= $this->db->get_where('tran_non_po_detail', array('no_non_po' => $row['no_non_po']))->result_array();
			$arr_nmbarang = array();
			$arr_qty = array();
			$arr_tanggal = array();
			foreach ($list_barang as $val => $valx) {
				$get_satuan = $this->db->get_where('raw_pieces', array('id_satuan' => $valx['satuan']))->result();
				$nm_satuan = (!empty($get_satuan)) ? strtolower($get_satuan[0]->kode_satuan) : '';
				$arr_nmbarang[$val] = "&bull; " . strtoupper($valx['nm_barang']);
				$arr_qty[$val] = "&bull; " . number_format($valx['qty']) . ' ' . $nm_satuan;
				$tgl_dibutuhkan = ($valx['tgl_dibutuhkan'] <> '0000-00-00') ? date('d-M-Y', strtotime($valx['tgl_dibutuhkan'])) : 'not set';
				$arr_tanggal[$val] = "&bull; " . $tgl_dibutuhkan;
			}
			$dt_nama_barang	= implode("<br>", $arr_nmbarang);
			$dt_qty	= implode("<br>", $arr_qty);
			$dt_tanggal	= implode("<br>", $arr_tanggal);

			$nestedData[]	= "<div align='left'>" . $dt_nama_barang . "</div>";
			$nestedData[]	= "<div align='left'>" . $dt_qty . "</div>";
			$nestedData[]	= "<div align='left'>" . $dt_tanggal . "</div>";



			// $last_by 	= (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
			// $last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];

			// $nestedData[]	= "<div align='center'>".$last_by."</div>";
			// $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

			if ($row['app_status'] == 'N') {
				$warna 	= 'blue';
				$sts 	= 'WAITING APPROVAL';
			} elseif ($row['app_status'] == 'Y') {
				$warna 	= 'green';
				$sts 	= 'WAITING EXPENSE REPORT';
			} else {
				$warna 	= 'red';
				$sts 	= 'REJECTED';
			}

			if (!empty($row['expense_date'])) {
				$warna 	= 'blue';
				$sts 	= 'EXPENSE REPORT';
			}

			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
			$view		= "<a href='" . base_url('pembelian/app_non_po/' . $row['no_non_po'] . '/view') . "'  class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
			$edit		= "";
			$approve	= "";
			$cancel		= "";
			$expense		= "";

			if ($tanda <> 'approval') {
				if ($Arr_Akses['update'] == '1') {
					if ($row['app_status'] == 'N') {
						$edit		= "<a href='" . base_url('pembelian/app_non_po/' . $row['no_non_po']) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
					}
					if ($row['app_status'] == 'Y') {
						$expense	= "<a href='" . base_url('pembelian/expense_non_po/' . $row['no_non_po']) . "' class='btn btn-sm btn-success' title='Expense Report' data-role='qtip'><i class='fa fa-credit-card'></i></a>";
					}
				}
			}

			if ($tanda == 'approval') {
				$view		= "";
				if ($Arr_Akses['approve'] == '1') {
					if ($row['app_status'] == 'N') {
						$approve	= "<a href='" . base_url('pembelian/app_non_po/' . $row['no_non_po'] . '/approve') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
					}
				}
			}
			$nestedData[]	= "<div align='left'>
									" . $view . "
                                    " . $edit . "
									" . $approve . "
									" . $cancel . "
									" . $expense . "
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

	public function query_data_json_app_non_po($tanda, $category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = "";
		if ($tanda == 'approval') {
			$where = "AND a.app_status = 'N' ";
		}
		if ($tanda == 'non_po') {
			$where = "AND a.app_status = 'Y' ";
		}

		$where2 = "";
		if ($category <> '0') {
			$where2 = " AND a.category='" . $category . "' ";
		}
		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				d.nm_dept
			FROM
				tran_non_po_header a
				LEFT JOIN tran_pr_detail b ON a.no_non_po=b.no_rfq
				LEFT JOIN rutin_non_planning_detail c ON b.id_barang=c.id
				LEFT JOIN rutin_non_planning_header e ON c.no_pengajuan=e.no_pengajuan
				LEFT JOIN department d ON e.id_dept=d.id,
				(SELECT @row:=0) r
		    WHERE 1=1 " . $where . " " . $where2 . " AND (
				a.no_non_po LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.pic LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY a.no_non_po
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_non_po'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function app_non_po()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$dateTime		= date('Y-m-d H:i:s');
			// print_r($data); exit;
			$code_plan  	= $data['id'];
			$tanda        	= $data['tanda'];
			$approve        = $data['approve'];

			$pic        	= strtolower($data['pic']);
			$keterangan     = strtolower($data['keterangan']);

			$detail 		= $data['detail'];

			//approve
			$sts_app        = (!empty($data['sts_app'])) ? $data['sts_app'] : '';
			$reason        	= (!empty($data['reason'])) ? $data['reason'] : '';

			$ym = date('ym');


			$SUM_QTY = 0;
			$SUM_HARGA = 0;
			if (empty($approve)) {
				$ArrDetail = array();
				if (!empty($detail)) {
					foreach ($detail as $val => $valx) {
						$qty 	= str_replace(',', '', $valx['qty']);
						$harga 	= str_replace(',', '', $valx['price_unit']);

						$SUM_QTY 	+= $qty;
						$SUM_HARGA 	+= $harga * $qty;

						$ArrDetail[$val]['id'] 				= $valx['id'];
						$ArrDetail[$val]['nm_barang'] 		= strtolower($valx['nm_barang']);
						$ArrDetail[$val]['qty'] 			= $qty;
						$ArrDetail[$val]['price_unit'] 		= $harga;
						$ArrDetail[$val]['keterangan'] 		= strtolower($valx['keterangan']);
						$ArrDetail[$val]['tgl_dibutuhkan'] 		= $valx['tanggal'];
						$ArrDetail[$val]['created_by'] 		= $data_session['ORI_User']['username'];
						$ArrDetail[$val]['created_date'] 	= $dateTime;
					}
				}
			}

			//header edit
			$ArrHeader		= array(
				'pic' 			=> $pic,
				'keterangan' 	=> $keterangan,
				'qty' 			=> $SUM_QTY,
				'nilai_request' => $SUM_HARGA,
				'updated_by'	=> $data_session['ORI_User']['username'],
				'updated_date'	=> $dateTime
			);


			//header approve
			if (!empty($approve)) {
				$ArrDetail = array();
				if (!empty($detail)) {
					foreach ($detail as $val => $valx) {
						$qty 	= str_replace(',', '', $valx['qty']);
						$harga 	= str_replace(',', '', $valx['price_unit']);

						$SUM_QTY 	+= $qty;
						$SUM_HARGA 	+= $harga * $qty;

						$ArrDetail[$val]['id'] 				= $valx['id'];
						$ArrDetail[$val]['nm_barang'] 		= strtolower($valx['nm_barang']);
						$ArrDetail[$val]['qty_rev'] 		= $qty;
						$ArrDetail[$val]['price_unit_rev'] 	= $harga;
						$ArrDetail[$val]['keterangan'] 		= strtolower($valx['keterangan']);
						$ArrDetail[$val]['tgl_dibutuhkan'] 	= $valx['tanggal'];
						$ArrDetail[$val]['satuan'] 			= $valx['satuan'];
						$ArrDetail[$val]['created_by'] 		= $data_session['ORI_User']['username'];
						$ArrDetail[$val]['created_date'] 	= $dateTime;
						$ArrDetail[$val]['app_reason'] 		= strtolower($reason);
						$ArrDetail[$val]['app_status'] 		= $sts_app;
						$ArrDetail[$val]['app_by'] 			= $data_session['ORI_User']['username'];
						$ArrDetail[$val]['app_date'] 		= $dateTime;
					}
				}

				$ArrHeader		= array(
					'pic' 			=> $pic,
					'keterangan' 	=> $keterangan,
					'qty_rev' 		=> $SUM_QTY,
					'nilai_rev' 	=> $SUM_HARGA,
					'updated_by'	=> $data_session['ORI_User']['username'],
					'updated_date'	=> $dateTime,
					'app_status' 	=> $sts_app,
					'app_by'		=> $data_session['ORI_User']['username'],
					'app_date'		=> $dateTime,
					'app_reason' 	=> strtolower($reason),
				);
			}


			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();
			if (empty($approve)) {
				$this->db->where(array('no_non_po' => $code_plan));
				$this->db->update('tran_non_po_header', $ArrHeader);

				$this->db->update_batch('tran_non_po_detail', $ArrDetail, 'id');
			}
			if (!empty($approve)) {
				$this->db->where(array('no_non_po' => $code_plan));
				$this->db->update('tran_non_po_header', $ArrHeader);

				$this->db->update_batch('tran_non_po_detail', $ArrDetail, 'id');
			}
			$this->db->trans_complete();


			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data failed. Please try again later ...',
					'status'	=> 0,
					'approve'	=> $approve
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data success. Thanks ...',
					'status'	=> 1,
					'approve'	=> $approve
				);
				history($tanda . ' pengajuan budget non rutin ' . $code_plan);
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
			$data = array(
				'title'				=> $tanda . ' Non PO',
				'action'		=> strtolower($tanda),
				'akses_menu'	=> $Arr_Akses,
				'header'		=> $header,
				'detail'		=> $detail,
				'datacoa'		=> $datacoa,
				'satuan'		=> $satuan,
				'approve'		=> $approve,
				'non_po'		=> $non_po,
				'id'			=> $id
			);

			$this->load->view('Pembelian/app_non_po', $data);
		}
	}

	public function get_add_non_po()
	{
		$nomor 	= $this->uri->segment(3);
		$no 	= 0;

		$d_Header = "";
		$d_Header .= "<tr class='header_" . $nomor . "'>";
		$d_Header .= "<td align='center'>" . $nomor . "</td>";
		$d_Header .= "<td align='left'><input type='text' name='detail[" . $nomor . "][nm_barang]' class='form-control input-md'></td>";
		$d_Header .= "<td align='left'><input type='text' id='qty_" . $nomor . "' name='detail[" . $nomor . "][qty]' class='form-control input-md text-center maskM sum_tot' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
		$d_Header .= "<td align='left'><input type='text' id='harga_" . $nomor . "' name='detail[" . $nomor . "][price_unit]' class='form-control input-md text-right maskM sum_tot' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
		$d_Header .= "<td align='left'><input type='text' id='total_harga_" . $nomor . "' name='detail[" . $nomor . "][total_harga]' class='form-control input-md text-right maskM jumlah_all' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
		$d_Header .= "<td align='left'><input type='text' name='detail[" . $nomor . "][tanggal]' class='form-control input-md datepicker' readonly></td>";
		$d_Header .= "<td align='left'><input type='text' name='detail[" . $nomor . "][keterangan]' class='form-control input-md'></td>";
		if (empty($approve)) {
			$d_Header .= "<td align='center'><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td>";
		}
		$d_Header .= "</tr>";


		//add part
		$d_Header .= "<tr id='add_" . $nomor . "'>";
		$d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' title='Add TOP'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add TOP</button></td>";
		$d_Header .= "<td align='center' colspan='7'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	//NEW
	public function modal_po()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$Username 		= $this->session->userdata['ORI_User']['username'];
			$dateTime		= date('Y-m-d H:i:s');
			$category 		= $data['category2'];

			$Ym = date('ym');
			//pengurutan kode
			$srcMtr			= "SELECT MAX(no_po) as maxP FROM tran_po_header WHERE no_po LIKE 'POX" . $Ym . "%' ";
			$numrowMtr		= $this->db->query($srcMtr)->num_rows();
			$resultMtr		= $this->db->query($srcMtr)->result_array();
			$angkaUrut2		= $resultMtr[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 7, 4);
			$urutan2++;
			$urut2			= sprintf('%04s', $urutan2);
			$no_po			= "POX" . $Ym . $urut2;
			// echo $no_po; exit;

			$check			= $data['check'];
			$tgl_dibutuhkan	= date('Y-m-d', strtotime($data['tanggal_dibutuhkan']));
			$valid_date		= (!empty($data['valid_date'])) ? date('Y-m-d', strtotime($data['valid_date'])) : NULL;
			$total_po		= str_replace(',', '', $data['total_po']);
			$discount		= str_replace(',', '', $data['discount']);
			$net_price		= str_replace(',', '', $data['net_price']);
			$tax			= str_replace(',', '', $data['tax']);
			$net_plus_tax	= str_replace(',', '', $data['net_plus_tax']);
			$delivery_cost	= str_replace(',', '', $data['delivery_cost']);
			$grand_total	= str_replace(',', '', $data['grand_total']);

			$ArrList 		= array();
			foreach ($check as $vaxl) {
				$ArrList[$vaxl] = $vaxl;
			}
			$dtImplode		= "('" . implode("','", $ArrList) . "')";

			$qListPRD 	= "SELECT * FROM tran_rfq_detail WHERE id IN " . $dtImplode . "  ";
			$detail 	= $this->db->query($qListPRD)->result_array();
			$hub_rfq 	= $detail[0]['hub_rfq'];
			$headerRFQ	= $this->db->get_where('tran_rfq_header', array('hub_rfq' => $hub_rfq))->result();

			$ArrHeader = array();
			$ArrDetail = array();
			$ArrUpdate = array();

			$SUM_MAT = 0;
			foreach ($detail as $val2 => $valx22) {
				$qty_po = str_replace(',', '', $data['purchase_' . $valx22['id']]);
				$net_pricedtl = str_replace(',', '', $data['harga_idr_' . $valx22['id']]);
				$total_price = str_replace(',', '', $data['total_harga_' . $valx22['id']]);

				$SUM_MAT += $qty_po;

				$ArrDetail[$val2]['no_po'] 			= $no_po;
				$ArrDetail[$val2]['id_barang'] 		= $valx22['id_barang'];
				$ArrDetail[$val2]['nm_barang'] 		= $valx22['nm_barang'];
				$ArrDetail[$val2]['qty_purchase'] 	= $qty_po;
				$ArrDetail[$val2]['qty_po'] 		= $qty_po;
				$ArrDetail[$val2]['net_price'] 		= $net_pricedtl;
				$ArrDetail[$val2]['total_price'] 	= $total_price;
				$ArrDetail[$val2]['price_ref'] 		= $valx22['price_ref'];
				$ArrDetail[$val2]['price_ref_sup'] 	= $valx22['price_ref_sup'];
				$ArrDetail[$val2]['moq'] 			= $valx22['moq'];
				$ArrDetail[$val2]['tgl_dibutuhkan'] = $valx22['tgl_dibutuhkan'];
				$ArrDetail[$val2]['lead_time'] 		= $valx22['lead_time'];
				$ArrDetail[$val2]['top'] 			= $valx22['top'];
				$ArrDetail[$val2]['satuan'] 		= (($valx22['satuan'] == "") ? '21' : $valx22['satuan']);
				$ArrDetail[$val2]['created_by'] 	= $Username;
				$ArrDetail[$val2]['created_date'] 	= $dateTime;

				$ArrUpdate[$val2]['id'] 			= $valx22['id'];
				$ArrUpdate[$val2]['no_po'] 			= $no_po;
				$ArrUpdate[$val2]['qty_po'] 		= $valx22['qty_po'] + $qty_po;
			}

			$ArrHeader['no_po'] 			= $no_po;
			$ArrHeader['category'] 			= $category;
			$ArrHeader['id_supplier'] 		= $valx22['id_supplier'];
			$ArrHeader['nm_supplier'] 		= get_name('supplier', 'nm_supplier', 'id_supplier', $valx22['id_supplier']);
			$ArrHeader['total_material'] 	= $SUM_MAT;
			$ArrHeader['total_price'] 		= $grand_total;
			$ArrHeader['tax'] 				= $tax;
			$ArrHeader['total_po'] 			= $total_po;
			$ArrHeader['discount'] 			= $discount;
			$ArrHeader['net_price'] 		= $net_price;
			$ArrHeader['net_plus_tax'] 		= $net_plus_tax;
			$ArrHeader['delivery_cost'] 	= $delivery_cost;
			$ArrHeader['tgl_dibutuhkan'] 	= $tgl_dibutuhkan;
			$ArrHeader['mata_uang'] 		= (!empty($headerRFQ[0]->currency)) ? $headerRFQ[0]->currency : NULL;
			$ArrHeader['valid_date'] 		= $valid_date;
			$ArrHeader['npwp'] 		= '01.081.598.3-431.000';
			$ArrHeader['phone'] 	= '021-8972193';
			$ArrHeader['created_by'] 		= $Username;
			$ArrHeader['created_date'] 		= $dateTime;
			$ArrHeader['updated_by'] 		= $Username;
			$ArrHeader['updated_date'] 		= $dateTime;

			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// print_r($ArrUpdate);
			// exit;

			$this->db->trans_start();
			$this->db->insert('tran_po_header', $ArrHeader);
			$this->db->insert_batch('tran_po_detail', $ArrDetail);

			$this->db->update_batch('tran_rfq_detail', $ArrUpdate, 'id');
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
				history('Create PO ' . $no_po . '/' . $valx22['hub_rfq'] . '/' . $valx22['id']);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$query 		= "	SELECT 
								a.id_supplier, 
								b.nm_supplier 
							FROM 
								tran_rfq_detail a
								LEFT JOIN supplier b ON a.id_supplier = b.id_supplier
							WHERE 
								a.status_apv = 'SETUJU' 
								AND a.qty_po < a.qty 
							GROUP BY 
								a.id_supplier 
							ORDER BY
								b.nm_supplier ASC ";
			$restQuery = $this->db->query($query)->result_array();
			$data = array(
				'supList' => $restQuery
			);
			$this->load->view('Pembelian/modal_po', $data);
		}
	}

	public function get_data_json_list_rfq()
	{
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_list_rfq(
			$requestData['id_supplier'],
			$requestData['category'],
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

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'><input type='checkbox' name='check[" . $row['id'] . "]' class='chk_personal check_pr' data-nomor='" . $row['id'] . "' value='" . $row['id'] . "'></div>";
			$nestedData[]	= "<div align='center'>" . $row['no_rfq'] . "
									<input type='hidden' name='harga_idr_" . $row['id'] . "' value='" . $row['harga_idr'] . "' class='harga_idr_val'>
									<input type='hidden' name='total_harga_" . $row['id'] . "' value='" . $row['total_harga'] . "' class='total_harga_val'></div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_barang']) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['qty'], 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['qty_po'], 2) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['tgl_dibutuhkan'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['created_by'] . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['created_date'])) . "</div>";
			$nestedData[]	= "<div align='right' class='harga_idr'>" . number_format($row['harga_idr'], 2) . "</div>";
			$nestedData[]	= "<div align='left'><span class='text-primary text-bold'>" . $row['currency'] . "</span></div>";
			$nestedData[]	= "<div align='center'>
									<input type='text' name='purchase_" . $row['id'] . "' id='purchase_" . $row['id'] . "' value='" . $row['qty'] . "' class='form-control input-md text-right maskM qty_po' style='width:100%;'>
								</div><script type='text/javascript'>$('.maskM').autoNumeric('init', {mDec: '2', aPad: false});</script>";
			$nestedData[]	= "<div align='right' class='total_harga'>" . number_format($row['total_harga'], 2) . "</div>";
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

	public function query_data_json_list_rfq($id_supplier, $category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$where = "";
		if ($id_supplier <> '0') {
			$where = " AND a.id_supplier = '" . $id_supplier . "'";
		}

		$where2 = "";
		if ($category <> '0') {
			$where2 = " AND b.category='" . $category . "' ";
		}
		$sql = "
			SELECT
			(@row:=@row+1) AS nomor,
				a.*,
				b.category,
				b.currency
			FROM
				tran_rfq_detail a 
				LEFT JOIN tran_rfq_header b ON a.hub_rfq=b.hub_rfq,
				(SELECT @row:=0) r
		    WHERE 1=1 " . $where . " " . $where2 . "
				AND a.status_apv = 'SETUJU'
				AND a.qty_po < a.qty
			AND (
				a.no_rfq LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_rfq',
			2 => 'close_date',
			3 => 'nm_barang'
		);

		$sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	//approve
	public function get_data_json_purchase_order_approve()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/approval_po";
		$Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_purchase_order_apporve(
			$requestData['id'],
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

		$ID = $requestData['id'];
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

			$list_material		= $this->db->query("SELECT nm_barang, qty_purchase, price_ref, price_ref_sup, net_price, total_price FROM tran_po_detail WHERE no_po='" . $row['no_po'] . "' GROUP BY id_barang")->result_array();
			if ($row['status'] != 'DELETED') {
				$list_material	= $this->db->query("SELECT nm_barang, qty_purchase, price_ref, price_ref_sup, net_price, total_price FROM tran_po_detail WHERE no_po='" . $row['no_po'] . "' AND deleted='N' GROUP BY id_barang")->result_array();
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
				$arr_pur[$val] = number_format($valx['net_price']);
			}
			$dt_pur	= implode("<br>", $arr_pur);

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['no_po'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $dt_sup . "</div>";
			$nestedData[]	= "<div align='left'>" . $dt_mat . "</div>";
			$nestedData[]	= "<div align='right'>" . $dt_qty . "</div>";
			$nestedData[]	= "<div align='right'>" . $dt_pur . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['total_price'], 2) . "</div>";

			$app1 = "<button type='button' class='btn btn-sm btn-success approved' title='Approval' data-id='" . $ID . "' data-no_po='" . $row['no_po'] . "'><i class='fa fa-check'></i></button>";

			$nestedData[]	= "	<div align='center'>
                                    " . $app1 . "
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

	public function query_data_json_purchase_order_apporve($id, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$WHERE = "";
		if ($id == '2') {
			$WHERE = "AND (a.total_price * 14000) > 50000000";
		}

		$sql = "SELECT
					a.*
				FROM
					tran_po_header a
				WHERE 1=1
					AND status$id = 'N'
					" . $WHERE . "
					AND a.deleted = 'N'
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

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
	function po_top()
	{
		$no_po 		= $this->uri->segment(3);
		$result		= $this->db->get_where('tran_po_header', array('no_po' => $no_po))->result();
		$data_kurs 	= $this->db->limit(1)->get_where('kurs', array('kode_dari' => 'USD'))->result();
		$get_RFQ = get_name('tran_rfq_detail', 'no_rfq', 'no_po', $no_po);
		$result_RFQ	= $this->db->get_where('tran_rfq_header', array('no_rfq' => $get_RFQ))->result();

		$sql_detail = "SELECT a.*, b.nm_supplier FROM tran_po_detail a LEFT JOIN tran_po_header b ON a.no_po=b.no_po WHERE a.no_po='" . $no_po . "'";

		if ($result[0]->status != 'DELETED') {
			$sql_detail = "SELECT a.*, b.nm_supplier FROM tran_po_detail a LEFT JOIN tran_po_header b ON a.no_po=b.no_po WHERE a.no_po='" . $no_po . "' AND a.deleted='N'";
		}
		$result_det		= $this->db->query($sql_detail)->result_array();

		$data_top		= $this->db->get_where('billing_top', array('no_po' => $no_po))->result_array();

		$payment = $this->db->get_where('list_help', array('group_by' => 'top'))->result_array();

		$data = array(
			'data' 		=> $result,
			'data_rfq' 	=> $result_RFQ,
			'data_kurs' => $data_kurs,
			'data_top' => $data_top,
			'payment' => $payment,
			'result' => $result_det
		);

		$this->load->view('Pembelian/form_top', $data);
	}
	function save_po_top()
	{
		$ArrEditPO = array();
		$data = $this->input->post();
		$no_po = $data['no_po'];
		$detail_po = $data['detail_po'];
		$data_session	= $this->session->userdata;
		$no = 0;
		if (!empty($data['detail_po'])) {
			foreach ($detail_po as $val => $valx) {
				$no++;
				if (!empty($valx['progress'])) {
					$ArrEditPO[$val]['no_po'] 		= $no_po;
					$ArrEditPO[$val]['category'] 	= 'pembelian material';
					$ArrEditPO[$val]['term'] 		= $no;
					$ArrEditPO[$val]['group_top'] 	= $valx['group_top'];
					$ArrEditPO[$val]['progress'] 	= str_replace(',', '', $valx['progress']);
					$ArrEditPO[$val]['value_usd'] 	= str_replace(',', '', $valx['value_usd']);
					$ArrEditPO[$val]['value_idr'] 	= str_replace(',', '', $valx['value_idr']);
					$ArrEditPO[$val]['keterangan'] 	= strtolower($valx['keterangan']);
					$ArrEditPO[$val]['jatuh_tempo'] = $valx['jatuh_tempo'];
					$ArrEditPO[$val]['syarat'] 		= strtolower($valx['syarat']);
					$ArrEditPO[$val]['created_by'] 	= $data_session['ORI_User']['username'];
					$ArrEditPO[$val]['created_date'] = date('Y-m-d H:i:s');
				}
			}
		}

		$hist_top 		= $this->db->query("SELECT * FROM billing_top WHERE no_po='" . $no_po . "'")->result_array();
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
				$ArrEditPOHist[$val]['hist_by'] 	= $data_session['ORI_User']['username'];
				$ArrEditPOHist[$val]['hist_date']	= date('Y-m-d H:i:s');
			}
		}
		$this->db->trans_start();
		$this->db->where('no_po', $data['no_po']);
		$this->db->where('proses_inv', '0');
		$this->db->where('invoice_total', '0');
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
			history('Edit PO custom TOP : ' . $data['no_po']);
		}
		echo json_encode($Arr_Data);
	}
}
