<?php

class Pr_asset_model extends BF_model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($table)
	{
		$queryList = $this->db->where('status', 'Y')->get($table)->result_array();
		return $queryList;
	}

	public function getWhere($table, $flied, $value)
	{
		$queryList = $this->db->get_where($table, array($flied => $value))->result_array();
		return $queryList;
	}

	public function saveData($table, $dataArr)
	{

		$this->db->trans_start();
		$this->db->insert($table, $dataArr);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		return $Arr_Data;
	}

	public function getDataJSON()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		$requestData	= $_REQUEST;
		$fetch			= $this->queryDataJSON(
			$requestData['kategori'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];
		$totalAset		= $fetch['totalAset'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		$sumx	= 0;
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

			$KEL_PENYUSUTAN = (!empty($row['no_perkiraan'])) ? strtoupper($row['no_perkiraan'] . ' | ' . $row['ket_coa']) : '';

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['kd_asset'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nm_asset'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['tgl_perolehan'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nm_category'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . $KEL_PENYUSUTAN . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['cost_center'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['depresiasi'] . " Year</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['nilai_asset']) . "</div>";

			$edit 	= "";
			$edit2 	= "";
			$delete = "";

			if ($Arr_Akses['update'] == '1') {
				$edit	= "&nbsp;<a href='" . site_url('asset/add/' . $row['id']) . "' class='btn btn-sm btn-primary edit' data-code='" . $row['id'] . "' title='Pindah Lokasi' data-role='qtip'><i class='fa fa-exchange'></i></a>";
				$edit2	= "&nbsp;<a href='" . site_url('asset/edit/' . $row['id']) . "' class='btn btn-sm btn-primary edit' data-code='" . $row['id'] . "' title='Edit Coa' data-role='qtip'><i class='fa fa-edit'></i></a>";
			}
			if ($Arr_Akses['delete'] == '1') {
				$delete = "&nbsp;<button type='button' class='btn btn-sm btn-danger delete_asset' title='Hapus Asset' data-id='" . $row['kd_asset'] . "' data-role='qtip'><i class='fa fa-trash'></i></button>";
			}

			$nestedData[]	= "<div align='center'>
									<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-id='" . $row['id'] . "' data-role='qtip'><i class='fa fa-eye'></i></button>
									" . $edit . "
									" . $edit2 . "
									" . $delete . "
									</div>";
			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data,
			"recordsAset"		=> intval($totalAset)
		);

		echo json_encode($json_data);
	}

	public function queryDataJSON($kategori, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where_kategori = "";
		if ($kategori != '0') {
			$where_kategori = " AND a.category = '" . $kategori . "' ";
		}

		$sql = "
				SELECT
					a.id,
					a.kd_asset,
					a.nm_asset,
					a.category,
					a.penyusutan,
					c.nm_category,
					a.nilai_asset,
					a.depresiasi,
					a.`value`,
					a.department,
					a.kdcab,
					a.cost_center,
					a.tgl_perolehan,
					d.coa AS no_perkiraan,
					d.keterangan AS ket_coa
				FROM
					asset a 
					LEFT JOIN asset_category c ON a.category = c.id
					LEFT JOIN asset_coa d ON a.id_coa = d.id
				WHERE 1=1
					AND a.deleted_date IS NULL
					" . $where_kategori . "
					AND (
						a.nm_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.kd_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
				GROUP BY a.kd_asset
			";

		$Query_Sum	= "	SELECT
								SUM(a.nilai_asset) AS total_aset
							FROM
								asset a
							WHERE 1=1
								AND a.deleted_date IS NULL
								" . $where_kategori . "
								AND (
									a.nm_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
									OR a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
									OR a.kd_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
								)
							";

		// echo $Query_Sum; exit;

		$Total_Aset = 0;
		$Hasil_SUM		   	= $this->db->query($Query_Sum)->result_array();
		if ($Hasil_SUM) {
			$Total_Aset		= $Hasil_SUM[0]['total_aset'];
		}
		$data['totalData'] 		= $this->db->query($sql)->num_rows();
		$data['totalAset'] 		= $Total_Aset;
		$data['totalFiltered'] 	= $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'id',
			1 => 'kd_asset',
			2 => 'nm_asset',
			3 => 'tgl_perolehan',
			4 => 'nm_category',
			5 => 'd.coa',
			6 => 'depresiasi',
			7 => 'nilai_asset'

		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	//CATEGORY
	public function get_json_category()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		$requestData	= $_REQUEST;
		$fetch			= $this->get_query_json_category(
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
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nm_category'])) . "</div>";
			$value = "Active";
			$color = "bg-green";
			if ($row['status'] == 'N') {
				$value = "Not Active";
				$color = "bg-red";
			}
			// $nestedData[]	= "<div align='center'><span class='badge ".$color." '>".$value."</span></div>";

			$last_create = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
			$nestedData[]	= "<div align='center'>" . strtolower($last_create) . "</div>";

			$last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];
			$nestedData[]	= "<div align='center'>" . date('d-m-Y', strtotime($last_date)) . "</div>";

			$detail		= "";
			$edit		= "";
			$delete		= "";

			if ($Arr_Akses['delete'] == '1') {
				$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete data' data-code='" . $row['id'] . "'><i class='fa fa-trash'></i></button>";
			}

			if ($Arr_Akses['update'] == '1') {
				$edit	= "&nbsp;<button type='button' class='btn btn-sm btn-primary edit' data-code='" . $row['id'] . "' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></button>";
			}
			$nestedData[]	= "<div align='left'>
                                    " . $edit . "
                                    " . $delete . "
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

	public function get_query_json_category($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "
            SELECT
                (@row:=@row+1) AS nomor,
				a.*
			FROM
                asset_category a,
                (SELECT @row:=0) r 
            WHERE 
                1=1 AND a.deleted='N' AND (
                a.nm_category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.created_by LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.updated_by LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData']  = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'nm_category'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	// PR ASSET
	public function pr()
	{

		$tanda = $this->uri->segment(3);

		$where = '';
		if (!empty($tanda)) {
			if ($tanda == 'approval_head') {
				$where = " AND (b.app_status_1 = '' OR b.app_status_1 IS NULL)";
			}
			if ($tanda == 'approval_cost_control') {
				$where = " AND b.app_status_1 = 'Y' AND (b.app_status_2 = '' OR b.app_status_2 IS NULL)";
			}
			if ($tanda == 'approval_management') {
				$where = " AND b.app_status_1 = 'Y' AND b.app_status_2 = 'Y' AND (b.app_status_3 = '' OR b.app_status_3 IS NULL)";
			}
		}

		$data = array(
			'title'			=> 'Indeks Of PR Assets',
			'action'		=> 'asset',
			'tanda'			=> ''
		);
		history("View index PR asset");
		$this->template->set($data);
		$this->template->render('pr');
	}

	public function approval_head()
	{
		$data = array(
			'title'			=> 'Approval PR Asset by Head',
			'action'		=> 'asset',
			'tanda'			=> 'approval_head'
		);
		history('Approval PR Asset by Head');
		$this->template->set($data);
		$this->template->render('approval_pr_head');
	}

	public function approval_cost_control()
	{
		$data = array(
			'title'			=> 'Approval PR Asset by Cost Control',
			'action'		=> 'asset',
			'tanda'			=> 'approval_cost_control'
		);
		history('Approval PR Asset by Cost Control');
		$this->template->set($data);
		$this->template->render('approval_pr_cost_control');
	}

	public function approval_management()
	{
		$data = array(
			'title'			=> 'Approval PR Asset by Management',
			'action'		=> 'asset',
			'tanda'			=> 'approval_management'
		);
		history('Approval PR Asset by Management');
		$this->template->set($data);
		$this->template->render('approval_pr_management');
	}

	public function add_pr()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$code_plan 	= $data['code_plan'];
			$qty_rev 	= $data['qty_rev'];
			$nil_pr 	= $data['nil_pr'];
			$tgl_butuh 	= $data['tgl_butuh'];

			$Ym = date('y');
			$qIPP			= "SELECT MAX(no_pr) as maxP FROM tran_pr_header WHERE no_pr LIKE 'PRA" . $Ym . "%' ";
			$numrowIPP		= $this->db->query($qIPP)->num_rows();
			$resultIPP		= $this->db->query($qIPP)->result_array();
			$angkaUrut2		= $resultIPP[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 5, 4);
			$urutan2++;
			$urut2			= sprintf('%04s', $urutan2);
			$no_pr			= "PRA" . $Ym . $urut2;

			$get_asset = $this->db->query("SELECT * FROM asset_planning WHERE code_plan='" . $code_plan . "' LIMIT 1")->result();

			$ArrHeader = array(
				'no_pr' => $no_pr,
				'category' => 'asset',
				'tgl_pr'	=> date('Y-m-d'),
				'created_by' => $this->auth->user_id(),
				'created_date' => date('Y-m-d H:i:s')
			);

			$ArrDetail = array(
				'no_pr' => $no_pr,
				'category' => 'asset',
				'tgl_pr'	=> date('Y-m-d'),
				'id_barang' => $code_plan,
				'nm_barang' => $get_asset[0]->nama_asset,
				'qty' => $qty_rev,
				'satuan' => '21',
				'nilai_pr' => $nil_pr,
				'tgl_dibutuhkan' => $tgl_butuh,
				'created_by' => $this->auth->user_id(),
				'created_date' => date('Y-m-d H:i:s')
			);

			$ArrUpdate = array(
				'no_pr' => $no_pr
			);
			// print_r($ArrHeader);
			// exit;
			$this->db->trans_start();
			$this->db->insert('tran_pr_header', $ArrHeader);
			$this->db->insert('tran_pr_detail', $ArrDetail);

			$this->db->where('code_plan', $code_plan);
			$this->db->update('asset_planning', $ArrUpdate);
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
				history('Create PR asset ' . $no_pr);
			}
			echo json_encode($Arr_Data);
		} else {
			$data = array(
				'title'			=> 'Add PR Assets',
				'action'		=> 'asset',
			);
			$this->template->set($data);
			$this->template->render('add_pr');
		}
	}

	public function get_data_json_pr_asset()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_pr_asset(
			$requestData['tanda'],
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
			$nestedData[]	= "<div class='prt_" . $nomor . "' align='center'>" . $nomor . "</div><script type='text/javascript'>$('.prt_" . $nomor . "').parent().parent().attr('id','" . $nomor . "');</script>";
			$nestedData[]	= "<div align='center'>" . strtoupper($row['no_pr']) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d M Y', strtotime($row['tgl_pr'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_barang']) . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['dibuat_oleh'] . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d M Y', strtotime($row['created_date'])) . "</div>";

			$approve_stat = 0;

			if (
				($row['app_status_1'] == '' || $row['app_status_1'] == null) &&
				($row['app_status_2'] == '' || $row['app_status_2'] == null) &&
				($row['app_status_3'] == '' || $row['app_status_3'] == null)
			) {
				$status = 'Waiting Approval Head';
				$color = 'blue';
			}
			if (
				$row['app_status_1'] == 'D'
			) {
				$status = 'Rejected by Head';
				$color = 'red';
			}

			if (
				($row['app_status_1'] == 'Y') &&
				($row['app_status_2'] == '' || $row['app_status_2'] == null) &&
				($row['app_status_3'] == '' || $row['app_status_3'] == null)
			) {
				$status = 'Waiting Approval Cost Control';
				$color = 'blue';
			}
			if (
				$row['app_status_2'] == 'D'
			) {
				$status = 'Rejected by Cost Control';
				$color = 'red';
			}

			if (
				($row['app_status_1'] == 'Y') &&
				($row['app_status_2'] == 'Y') &&
				($row['app_status_3'] == '' || $row['app_status_3'] == null)
			) {
				$status = 'Waiting Approval Management';
				$color = 'blue';
			}
			if (
				$row['app_status_3'] == 'D'
			) {
				$status = 'Rejected by Management';
				$color = 'red';
			}

			if (
				$row['app_status_1'] == 'Y' &&
				$row['app_status_2'] == 'Y' &&
				$row['app_status_3'] == 'Y'
			) {
				$status = 'Approved';
				$color = 'green';

				$approve_stat = 1;
			}
			$nestedData[]	= "<div align='left'><span class='badge bg-" . $color . "'>" . strtoupper($status) . "</span></div>";
			$view = '';
			if($requestData['tanda'] !== '' && $requestData['tanda'] !== null) {
				$view = "<button type='button' class='btn btn-sm btn-primary look_hide' title='Look and Hide' data-id='" . $nomor . "' data-role='qtip'><i class='fa fa-check'></i></button>";
			}
			$print			= "&nbsp;<button type='button'class='btn btn-sm btn-info print_pr' title='Print PR' data-no_pr='" . $row['no_pr'] . "'><i class='fa fa-print'></i></button>";

			$edit = "&nbsp;<a href='" . base_url('pr_asset/edit/' . $row['id']) . "' class='btn btn-sm btn-warning'><i class='fa fa-pencil'></i></a>";
			if ($approve_stat == '1') {
				$edit = '';
			}

			$nestedData[]	= "<div align='center'>" . $view . "" . $print . "" . $edit . "</div>";
			$data[] = $nestedData;

			$tipe_approve = '';
			if ($requestData['tanda'] == 'approval_head') {
				$tipe_approve = 1;
			}
			if ($requestData['tanda'] == 'approval_cost_control') {
				$tipe_approve = 2;
			}
			if ($requestData['tanda'] == 'approval_management') {
				$tipe_approve = 3;
			}

			//detail
			if($requestData['tanda'] !== '' && $requestData['tanda'] !== null) {
				$nestedData2 	= array();
				$nestedData2[]	= "<div class='prtCh_" . $nomor . "' align='center'></div><script type='text/javascript'>$('.prtCh_" . $nomor . "').parent().parent().attr('class','child-" . $nomor . "');$('.child-" . $nomor . "').hide()</script>"; //$('.prtCh_".$nomor."').parent().parent().attr('height','200px');
				$nestedData2[]	= "<div align='left'></div>";
				$nestedData2[]	= "<div align='right'><b>QTY BARANG</b><br>" . number_format($row['qty']) . "</div>";
				$nestedData2[]	= "<div align='right'><b>NILAI PR</b></br>" . number_format($row['nilai_pr']) . "</div>";
				$nestedData2[]	= "<div align='right'><b>TGL DIBUTUHKAN</b></br>" . date('d F Y', strtotime($row['tgl_dibutuhkan'])) . "</div>";
				$approve = "<button type='button' class='btn btn-sm btn-success approve' title='Approve' data-id='" . $nomor . "' data-tipe_approve='" . $tipe_approve . "' data-role='qtip'><i class='fa fa-check'></i></button>";
	
				$app_by = '';
				$app_date = '';
				if ($row['app_reason_1'] !== '' || $row['app_reason_1'] !== null) {
					$app_reason = $row['app_reason_1'];
					$app_date = date('d-M-Y H:i:s', strtotime($row['app_date_1']));
	
					$get_create_by = $this->db->get_where('users', ['id_user' => $row['app_by_1']])->row();
					if (!empty($get_create_by)) {
						$app_by = $get_create_by->nm_lengkap;
					}
				} else if ($row['app_reason_2'] !== '' || $row['app_reason_2'] !== null) {
					$app_reason = $row['app_reason_2'];
					$app_date = date('d-M-Y H:i:s', strtotime($row['app_date_2']));
	
					$get_create_by = $this->db->get_where('users', ['id_user' => $row['app_by_2']])->row();
					if (!empty($get_create_by)) {
						$app_by = $get_create_by->nm_lengkap;
					}
				} else if ($row['app_reason_3'] !== '' || $row['app_reason_3'] !== null) {
					$app_reason = $row['app_reason_3'];
					$app_date = date('d-M-Y H:i:s', strtotime($row['app_date_3']));
	
					$get_create_by = $this->db->get_where('users', ['id_user' => $row['app_by_3']])->row();
					if (!empty($get_create_by)) {
						$app_by = $get_create_by->nm_lengkap;
					}
				} else {
					$app_reason = 'tidak ada';
				}
				$sts_app = "";
				$sts_by = "";
				if ($row['app_status_1'] !== '' || $row['app_status_2'] !== '' || $row['app_status_3'] !== '') {
					$sts_app = "<b>" . $status . " REASON</b><br>" . ucfirst($app_reason);
					$sts_by = "<b>" . $status . " BY</b><br>" . ucfirst($app_by . " (" . $app_date . ")");
				}
				if (!empty($tanda)) {
					$nestedData2[]	= "<div align='right'><b>ACTION</b></br>
										<select id='action_" . $nomor . "' class='form-control input-sm chosen-select' style='width:100%;'>
											<option value='Y'>APPROVE</option>
											<option value='D'>REJECT</option>
										</select>
										</div>";
					$nestedData2[]	= "<div align='right'><b>REASON REJECT</b></br>
										<input type='input' id='reason_" . $nomor . "' class='form-control input-sm text-left' style='width:100%;' placeholder='Reason'>
										<input type='hidden' id='no_pr_" . $nomor . "' class='form-control input-sm' value='" . $row['no_pr'] . "'>
										</div>";
					$nestedData2[]	= "<div align='center'><br>" . $approve . "</div>";
				} else {
					$nestedData2[]	= "<div align='right'>" . $sts_by . "</div>";
					$nestedData2[]	= "<div align='right'>" . $sts_app . "</div>";
					$nestedData2[]	= "<div align='right'></div>";
				}
				$data[] = $nestedData2;
			}


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

	public function query_data_json_pr_asset($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = '';
		if (!empty($tanda)) {
			if ($tanda == 'approval_head') {
				$where = " AND (b.app_status_1 = '' OR b.app_status_1 IS NULL)";
			}
			if ($tanda == 'approval_cost_control') {
				$where = " AND b.app_status_1 = 'Y' AND (b.app_status_2 = '' OR b.app_status_2 IS NULL)";
			}
			if ($tanda == 'approval_management') {
				$where = " AND b.app_status_1 = 'Y' AND b.app_status_2 = 'Y' AND (b.app_status_3 = '' OR b.app_status_3 IS NULL)";
			}
		}

		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.app_status_1,
				b.app_status_2,
				b.app_status_3,
				c.nm_lengkap as dibuat_oleh
			FROM
				tran_pr_detail a 
				LEFT JOIN tran_pr_header b ON a.no_pr = b.no_pr
				LEFT JOIN users c ON c.id_user = b.created_by,
				(SELECT @row:=0) r
		    WHERE  1=1 " . $where . " AND a.category = 'asset' AND(
				a.id LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.nm_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR c.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'id'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function get_data_json_add_pr_asset()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_add_pr_asset(
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
			$nestedData[]	= "<div class='prt_" . $nomor . "' align='center'>" . $nomor . "</div><script type='text/javascript'>$('.prt_" . $nomor . "').parent().parent().attr('id','" . $nomor . "');</script>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nama_asset']) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_dept']) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_costcenter']) . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['qty'] . "</div>";
			$nestedData[]	= "<div align='center'>" . strtolower($row['app_by']) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d M Y', strtotime($row['app_date'])) . "</div>";

			$view = "<button type='button' class='btn btn-sm btn-primary look_hide' title='Look and Hide' data-id='" . $nomor . "' data-role='qtip'><i class='fa fa-plus'></i></button>";

			$nestedData[]	= "<div align='center'>" . $view . "</div>";

			$data[] = $nestedData;


			//detail
			$nestedData2 	= array();
			$nestedData2[]	= "<div class='prtCh_" . $nomor . "' align='center'></div><script type='text/javascript'>$('.prtCh_" . $nomor . "').parent().parent().attr('class','child-" . $nomor . "');$('.child-" . $nomor . "').hide()</script>"; //$('.prtCh_".$nomor."').parent().parent().attr('height','200px');
			$nestedData2[]	= "<div align='left'></div>";
			$nestedData2[]	= "<div align='right'><b>BUDGET</b><br>" . number_format($row['budget']) . "<br><b>SISA BUDGET PO</b><br>" . number_format($row['budget_po']) . "<br><b>SISA BUDGET PR</b></br>" . number_format($row['budget_pr']) . "</div>";
			$nestedData2[]	= "<div align='right'><b>RENCANA BELI</b><br>" . date('F Y', strtotime($row['tahun'] . '-' . $row['bulan'] . '-01')) . "<br><b>KETERANGAN</b><br>" . strtoupper($row['keterangan']) . "</div>";
			$nestedData2[]	= "<div align='right'><b>QTY</b><input type='text' id='qty_rev_" . $nomor . "' class='form-control input-sm text-center maskM' placeholder='Qty Rev' value='" . number_format($row['qty']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></div>";
			$nestedData2[]	= "<div align='right'><b>NILAI PR</b><input type='text' id='nil_pr_" . $nomor . "' class='form-control input-sm text-right maskM' placeholder='Nilai PR' value='" . number_format($row['budget']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></div>";
			$nestedData2[]	= "<div align='right'><b>TGL DIBUTUHKAN</b>
								<input type='date' class='form-control input-sm text-center' id='tgl_butuh_" . $nomor . "'>
								
								<input type='hidden' id='code_plan_" . $nomor . "' class='form-control input-sm' value='" . $row['code_plan'] . "'>
								</div>
								<style>.datepicker{cursor:pointer;}</style>
								";
			$app = "<button type='button' class='btn btn-sm btn-success add_pr' title='Tambahkan PR' data-id='" . $nomor . "' data-role='qtip'><i class='fa fa-check'></i></button>";

			$nestedData2[]	= "<div align='center' style='vertical-align:middle;'><br>" . $app . "</div>";

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

	public function query_data_json_add_pr_asset($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.nama as nm_dept,
				c.nama_costcenter as nm_costcenter
			FROM
				asset_planning a
				LEFT JOIN ms_department b ON a.id_dept = b.id
				LEFT JOIN ms_costcenter c ON a.id_costcenter = c.id,
				(SELECT @row:=0) r
		    WHERE  a.deleted='N' AND a.status='Y' AND a.no_pr IS NULL AND (
				a.id LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'nama_asset',
			2 => 'nm_dept',
			3 => 'nm_costcenter'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function approve_pr()
	{
		$data = $this->input->post();
		$no_pr 		= $data['no_pr'];
		$action 	= $data['action'];
		$tipe_approve = $data['tipe_approve'];
		$reason 	= strtolower($data['reason']);


		$ArrUpdate = array(
			'app_status_' . $tipe_approve => $action,
			'app_reason_' . $tipe_approve => $reason,
			'app_by_' . $tipe_approve => $this->auth->user_id(),
			'app_date_' . $tipe_approve => date('Y-m-d H:i:s')
		);
		// print_r($ArrUpdate);
		// exit;
		$this->db->trans_start();
		$this->db->where('no_pr', $no_pr);
		$this->db->update('tran_pr_header', $ArrUpdate);

		$this->db->where('no_pr', $no_pr);
		$this->db->update('tran_pr_detail', $ArrUpdate);
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
			history('Approve PR asset ' . $no_pr);
		}
		echo json_encode($Arr_Data);
	}

	//DEPRESIASI
	public function data_side_depreciation()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		$requestData	= $_REQUEST;
		$fetch			= $this->query_depreciation(
			$requestData['kdcab'],
			$requestData['tgl'],
			$requestData['kategori'],
			$requestData['bulan'],
			$requestData['tahun'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];
		$query2			= $fetch['query2'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		$sumx	= 0;
		$GET_DEPRESIASI = get_valueDepresiasi();
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
			$UNIQ = $row['kd_asset'] . '-' . $requestData['bulan'] . $requestData['tahun'];
			$KEL_PENYUSUTAN = (!empty($row['no_perkiraan'])) ? strtoupper($row['no_perkiraan'] . ' | ' . $row['ket_coa']) : '';

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['kd_asset'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nm_asset'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($row['tgl_perolehan'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nm_category'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . $KEL_PENYUSUTAN . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['cost_center'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['depresiasi'] . " Year</div>";
			$SISA_NILAI 	= ($row['penyusutan'] == 'N') ? $row['nilai_asset'] : $row['sisa_nilai'];

			$TGL_PEROLEHAN 	= date('Y-m-01', strtotime($row['tgl_perolehan']));
			$DEPRESIASI_BLN = $row['depresiasi'] * 12;
			$TGL_LAST_DEPT	= date('Ym', strtotime('+' . $DEPRESIASI_BLN . ' month', strtotime($TGL_PEROLEHAN)));
			$TGL_NOW 		= $requestData['tahun'] . $requestData['bulan'];
			$TGL_NOW_DATE 	= date('Y-m-01', strtotime($requestData['tahun'] . '-' . $requestData['bulan'] . '-01'));
			$DEPRESIASI = 0;
			if ($TGL_LAST_DEPT > $TGL_NOW and $TGL_PEROLEHAN <= $TGL_NOW_DATE) {
				$DEPRESIASI = (!empty($GET_DEPRESIASI[$UNIQ])) ? $GET_DEPRESIASI[$UNIQ] : 0;
			}

			$nestedData[]	= number_format($row['nilai_asset']);
			$nestedData[]	= number_format($DEPRESIASI);
			// $nestedData[]	= number_format($row['nilai_asset'] - $SISA_NILAI);
			$nestedData[]	= number_format($row['total_depresiasi']);
			$nestedData[]	= number_format($SISA_NILAI);

			$nestedData[]	= "<div align='center'>
									<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-id='" . $row['id'] . "' data-role='qtip'><i class='fa fa-eye'></i></button>
								</div>";
			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$totalAset = 0;
		$totalSusut = 0;
		$totalSisa = 0;
		$totalSusutnew = 0;
		foreach ($query2->result_array() as $row) {

			$UNIQ = $row['kd_asset'] . '-' . $requestData['bulan'] . $requestData['tahun'];

			$SISA_NILAI 	= ($row['penyusutan'] == 'N') ? $row['nilai_asset'] : $row['sisa_nilai'];
			if (intval($requestData['bulan']) >= 4  and intval($requestData['tahun']) >= 2022 and $row['kd_asset'] == 'ORI-22000000-000122') {
				//$SISA_NILAI = $SISA_NILAI + 29887;
			}

			$TGL_PEROLEHAN 	= date('Y-m-01', strtotime($row['tgl_perolehan']));
			$DEPRESIASI_BLN = $row['depresiasi'] * 12;
			$TGL_LAST_DEPT	= date('Ym', strtotime('+' . $DEPRESIASI_BLN . ' month', strtotime($TGL_PEROLEHAN)));
			$TGL_NOW 		= $requestData['tahun'] . $requestData['bulan'];
			$TGL_NOW_DATE 	= date('Y-m-01', strtotime($requestData['tahun'] . '-' . $requestData['bulan'] . '-01'));
			$DEPRESIASI = 0;
			if ($TGL_LAST_DEPT > $TGL_NOW and $TGL_PEROLEHAN <= $TGL_NOW_DATE) {
				// $DEPRESIASI = $row['value'];
				$DEPRESIASI = (!empty($GET_DEPRESIASI[$UNIQ])) ? $GET_DEPRESIASI[$UNIQ] : 0;
			}

			$NilaiAsset = $row['nilai_asset'];
			$NilaiDeps 	= $DEPRESIASI;
			$TotalDeps 	= $row['total_depresiasi'];
			$SisaNilai 	= $SISA_NILAI;

			$totalAset += $NilaiAsset;
			$totalSusut += $NilaiDeps;
			$totalSisa += $SisaNilai;
			$totalSusutnew += $TotalDeps;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data,
			"recordsAset"		=> intval($totalAset),
			"recordsSusut"		=> intval($totalSusut),
			//			"recordsSusutAk"	=> intval($totalAset-$totalSisa),
			"recordsSusutAk"	=> intval($totalSusutnew),
			"recordsSisa"		=> intval($totalSisa)
		);

		echo json_encode($json_data);
	}

	public function query_depreciation($kdcab, $tgl, $kategori, $bulan, $tahun, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where_kdcab = "";
		if (!empty($kdcab)) {
			$where_kdcab = " AND a.kdcab = '" . $kdcab . "' ";
		}

		$where_kategori = "";
		$where_kategori2 = "";
		if ($kategori != '0') {
			$where_kategori = " AND a.category = '" . $kategori . "' ";
			$where_kategori2 = " AND c.category = '" . $kategori . "' ";
		}

		$WHERE_PERIODE = "AND (b.flag='N' OR b.flag='X')";
		$WHERE_PERIODE2 = "AND (b.flag='N' OR b.flag='X')";
		if ($bulan != '0' and $tahun != '0') {
			//			$WHERE_PERIODE = "AND CONCAT(b.tahun,'-',b.bulan,'-01') > '".$tahun."-".$bulan."-01'";
			$WHERE_PERIODE2 = "AND CONCAT(b.tahun,'-',b.bulan,'-01') <= '" . $tahun . "-" . $bulan . "-01'";
		}

		if ($bulan != '0' and $tahun != '0') {
			$sql = "
					SELECT
						a.id,
						a.kd_asset,
						a.nm_asset,
						a.category,
						a.penyusutan,
						c.nm_category,
						a.nilai_asset,
						a.depresiasi,
						a.`value`,
						(SELECT SUM(b.nilai_susut) FROM asset_generate b WHERE a.kd_asset = b.kd_asset AND a.deleted = 'N' " . $WHERE_PERIODE . ") as sisa_nilai,
						(SELECT SUM(b.nilai_susut) FROM asset_generate b WHERE a.kd_asset = b.kd_asset AND a.deleted = 'N' AND b.flag='Y' " . $WHERE_PERIODE2 . ") as total_depresiasi,
						a.department,
						a.kdcab,
						a.cost_center,
						a.tgl_perolehan,
						d.coa AS no_perkiraan,
						d.keterangan AS ket_coa
					FROM
						asset a 
						LEFT JOIN asset_category c ON a.category = c.id
						LEFT JOIN asset_coa d ON a.id_coa = d.id
					WHERE 1=1
						AND a.deleted_date IS NULL
						" . $where_kdcab . "
						" . $where_kategori . "
						AND (
							a.nm_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
							OR a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
							OR a.kd_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						)
					GROUP BY a.kd_asset
				";
		} else {
			$sql = "
					SELECT
						a.id,
						a.kd_asset,
						a.nm_asset,
						a.category,
						a.penyusutan,
						c.nm_category,
						a.nilai_asset,
						a.depresiasi,
						a.`value`,
						b.sisa_nilai as sisa_nilai,
						(a.nilai_asset - b.sisa_nilai) as total_depresiasi,
						a.department,
						a.kdcab,
						a.cost_center,
						a.tgl_perolehan,
						d.coa AS no_perkiraan,
						d.keterangan AS ket_coa
					FROM
						asset a 
						LEFT JOIN asset_nilai b ON a.kd_asset = b.kd_asset
						LEFT JOIN asset_category c ON a.category = c.id
						LEFT JOIN asset_coa d ON a.id_coa = d.id
					WHERE 1=1
						AND a.deleted_date IS NULL
						" . $where_kdcab . "
						" . $where_kategori . "
						AND (
							a.nm_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
							OR a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
							OR a.kd_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						)
				";
		}

		$data['query2'] = $this->db->query($sql);
		$data['totalData'] 	= $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'id',
			1 => 'kd_asset',
			2 => 'nm_asset',
			3 => 'tgl_perolehan',
			4 => 'nm_category',
			5 => 'd.coa',
			6 => 'depresiasi',
			7 => 'nilai_asset',
			8 => 'value',
			9 => 'sisa_nilai'

		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	//CATEGORY
	public function get_json_asset_coa()
	{
		$controller		= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses		= getAcccesmenu($controller);
		$requestData	= $_REQUEST;
		$fetch			= $this->get_query_json_asset_coa(
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
			$status = ($row['status'] == "Y" ? "" : "bg-danger");

			$nestedData 	= array();
			$nestedData[]	= "<div align='center' class='" . $status . "'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left' class='" . $status . "'>" . strtoupper(strtolower($row['keterangan'])) . "</div>";
			$nestedData[]	= "<div align='left' class='" . $status . "'>" . (($row['coa'])) . "</div>";
			$nestedData[]	= "<div align='left' class='" . $status . "'>" . (($row['coa_kredit'])) . "</div>";

			$edit	= "&nbsp;<button type='button' class='btn btn-sm btn-primary edit' data-code='" . $row['id'] . "' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></button>";
			$nestedData[]	= "<div align='left'>
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

	public function get_query_json_asset_coa($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "
            SELECT
                (@row:=@row+1) AS nomor,
				a.*
			FROM
                asset_coa a,
                (SELECT @row:=0) r 
            WHERE 
                1=1 AND (
                a.coa LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.keterangan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.coa_kredit LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData']  = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'keterangan'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
}
