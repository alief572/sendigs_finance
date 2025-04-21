<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Incoming_asset extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('master_model');
		$this->load->library('upload');
	}

	public function departemen()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)) . '/' . strtolower($this->uri->segment(2)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$pusat				= $this->db->query("SELECT * FROM warehouse WHERE category='indirect' ORDER BY urut ASC")->result_array();
		$inventory 			= $this->db->query("SELECT * FROM con_nonmat_category_awal WHERE `delete`='N' ORDER BY category ASC")->result_array();
		$no_po				= $this->db->query("(SELECT a.no_po, 'PO' AS ket, a.nm_supplier AS nm_supplier FROM tran_po_header a WHERE a.category='non rutin' AND (a.status='WAITING IN' OR a.status='IN PARSIAL') AND a.status_id='1' AND a.deleted='N' ORDER BY a.no_po ASC)
												UNION
												(SELECT b.no_non_po AS no_po, 'NON-PO' AS ket, '' AS nm_supplier FROM tran_non_po_header b WHERE b.category='non rutin' AND (b.status !='COMPLETE') AND b.app_status = 'Y' ORDER BY b.no_non_po ASC)")->result_array();
		$list_po	= $this->db->group_by('no_ipp')->get_where('warehouse_adjustment', array('category' => 'incoming non rutin'))->result_array();
		$data_gudang = $this->db->group_by('id_gudang_ke')->get_where('warehouse_adjustment', array('category' => 'incoming non rutin'))->result_array();

		$data = array(
			'title'			=> 'Pembelian Non-Material & Jasa >> Incoming Departemen',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'pusat'			=> $pusat,
			'inventory'			=> $inventory,
			'list_po'		=> $list_po,
			'data_gudang'		=> $data_gudang,
			'no_po'			=> $no_po
		);
		history('View incoming departemen');
		$this->load->view('Incoming/departemen', $data);
	}

	public function server_side_incoming()
	{
		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_incoming(
			$requestData['no_po'],
			$requestData['gudang'],
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
		$GET_USERNAME = get_detail_user();
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

			$link = '';
			if (!empty($row['doc'])) {
				$link = "<a href='" . base_url($row['doc']) . "' target='_blank' title='Download' data-role='qtip'>Download</a>";
			}

			$print	= "&nbsp;<a href='" . base_url('incoming_asset/print_incoming_assets/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a>";

			$TANGGAL = (!empty($row['tanggal'])) ? $row['tanggal'] : $row['created_date'];
			$NM_LENGKAP = (!empty($GET_USERNAME[strtolower($row['created_by'])]['nm_lengkap'])) ? $GET_USERNAME[strtolower($row['created_by'])]['nm_lengkap'] : $row['created_by'];

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div>" . $row['kode_trans'] . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($TANGGAL)) . "</div>";
			$nestedData[]	= "<div>" . $row['no_ipp'] . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['jumlah_mat'] . "</div>";
			"</div>";
			$nestedData[]	= "<div>" . strtoupper($row['pic']) . "</div>";
			$nestedData[]	= "<div>" . strtoupper($NM_LENGKAP) . "</div>";
			$nestedData[]	= "<div>" . strtoupper($row['nm_supplier']) . "</div>";
			$nestedData[]	= "<div align='center'>
								<button type='button' class='btn btn-sm btn-primary detailAjust' title='View Incoming' data-kode_trans='" . $row['kode_trans'] . "' ><i class='fa fa-eye'></i></button>
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

	public function query_data_json_incoming($no_po, $gudang, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where_no_po = '';
		if (!empty($no_po)) {
			$where_no_po = " AND a.no_ipp = '" . $no_po . "' ";
		}

		$where_gudang = '';
		if (!empty($gudang)) {
			$where_gudang = " AND a.id_gudang_ke = '" . $gudang . "' ";
		}

		$sql = "
			SELECT
				a.*,b.nm_supplier
			FROM
				warehouse_adjustment a
				left join tran_po_header b on a.no_ipp=b.no_po
		    WHERE 1=1 AND a.category = 'incoming non rutin' AND a.status_id = '1'
				" . $where_no_po . "
				" . $where_gudang . "
			AND(
				a.no_ipp LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.id_gudang_ke LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.nm_supplier LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.jumlah_mat LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'kode_trans',
			2 => 'no_ipp'
		);

		$sql .= " ORDER BY created_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function modal_incoming()
	{
		$data = $this->input->post();

		$inventory 	    = $data['inventory'];
		$no_po 			= $data['no_po'];
		$id_dept 		= $data['id_dept'];
		$id_costcenter 	= $data['id_costcenter'];
		$tanggal_trans 	= $data['tanggal_trans'];
		$pic 			= strtolower($data['pic']);
		$note 			= strtolower($data['note']);

		$cek_type = substr($no_po, 0, 3);
		if ($cek_type == 'POX') {
			$result	= $this->db->get_where('tran_po_detail', array('no_po' => $no_po))->result_array();
		} else {
			$result	= $this->db->get_where('tran_non_po_detail', array('no_non_po' => $no_po))->result_array();
		}

		$data = array(
			'no_po' => $no_po,
			'inventory' => $inventory,
			'id_dept' => $id_dept,
			'id_costcenter' => $id_costcenter,
			'tanggal_trans' => $tanggal_trans,
			'pic' 	=> $pic,
			'note' 	=> $note,
			'result' => $result
		);

		$this->load->view('Incoming/modal_incoming', $data);
	}

	public function process_incoming()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;
		$no_po			= $data['no_po'];
		$inventory		= $data['inventory'];
		$id_dept		= $data['id_dept'];
		$id_costcenter	= $data['id_costcenter'];
		$pic			= $data['pic'];
		$note			= $data['note'];
		$tanggal		= $data['tanggal'];
		$addInMat		= $data['addInMat'];
		$adjustment 	= $data['adjustment'];
		$Ym 			= date('ym');
		$dateTime		= date('Y-m-d H:i:s');
		$UserName		= $data_session['ORI_User']['username'];
		$cek_type 		= substr($no_po, 0, 3);
		$totalprice = 0;

		if ($adjustment == 'IN') {
			$histHlp = "Adjustment incoming departemen / " . $no_po;

			//pengurutan kode
			$srcMtr			= "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRS" . $Ym . "%' ";
			$numrowMtr		= $this->db->query($srcMtr)->num_rows();
			$resultMtr		= $this->db->query($srcMtr)->result_array();
			$angkaUrut2		= $resultMtr[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 7, 4);
			$urutan2++;
			$urut2			= sprintf('%04s', $urutan2);
			$kode_trans		= "TRS" . $Ym . $urut2;

			$ArrUpdate		= array();
			$ArrDeatilAdj	= array();
			$ArrDeatilChk	= array();
			$ArrJurnal		= array();
			$SumMat = 0;
			foreach ($addInMat as $val => $valx) {
				$qtyOrder 	= str_replace(',', '', $valx['qty_rev']);
				$qtyIN 		= str_replace(',', '', $valx['qty_in']);

				$SumMat 	+= $qtyIN;

				//update detail purchase
				if ($cek_type == 'POX') {
					$result_det	= $this->db->get_where('tran_po_detail', array('id' => $valx['id']))->result_array();
				} else {
					$result_det	= $this->db->get_where('tran_non_po_detail', array('id' => $valx['id']))->result_array();
				}

				$ArrUpdate[$val]['id'] 			= $valx['id'];
				$ArrUpdate[$val]['qty_in'] 		= $result_det[0]['qty_in'] + $qtyIN;

				//detail adjustmeny
				$ArrDeatilAdj[$val]['no_ipp'] 			= $no_po;
				$ArrDeatilAdj[$val]['kode_trans'] 		= $kode_trans;
				$ArrDeatilAdj[$val]['id_po_detail']     = $valx['id'];
				$ArrDeatilAdj[$val]['id_material'] 		= ($valx['id']);
				$ArrDeatilAdj[$val]['nm_material'] 		= strtolower($valx['nm_barang']);
				$ArrDeatilAdj[$val]['nm_category'] 		= strtolower($valx['spec']);
				$ArrDeatilAdj[$val]['qty_order'] 		= $qtyOrder;
				$ArrDeatilAdj[$val]['qty_oke'] 			= $qtyIN;
				$ArrDeatilAdj[$val]['keterangan'] 		= strtolower($valx['keterangan']);
				$ArrDeatilAdj[$val]['no_ba'] 		    = null;
				$ArrDeatilAdj[$val]['ket_req_po'] 		= strtolower($valx['pemeriksa']);
				$ArrDeatilAdj[$val]['update_by'] 		= $UserName;
				$ArrDeatilAdj[$val]['update_date'] 		= $dateTime;
				$ArrDeatilAdj[$val]['check_qty_oke'] 	= $qtyIN;
				$ArrDeatilAdj[$val]['check_keterangan'] = strtolower($valx['keterangan']);

				//detail adjustmeny
				$ArrDeatilChk[$val]['no_ipp'] 		= $no_po;
				$ArrDeatilChk[$val]['id_detail'] 	= NULL;
				$ArrDeatilChk[$val]['kode_trans'] 	= $kode_trans;
				$ArrDeatilChk[$val]['nm_material'] 	= strtolower($valx['nm_barang']);
				$ArrDeatilChk[$val]['nm_category'] 	= strtolower($valx['spec']);
				$ArrDeatilChk[$val]['qty_order'] 	= $qtyOrder;
				$ArrDeatilChk[$val]['qty_oke'] 		= $qtyIN;
				$ArrDeatilChk[$val]['keterangan'] 	= strtolower($valx['keterangan']);
				$ArrDeatilChk[$val]['update_by'] 	= $UserName;
				$ArrDeatilChk[$val]['update_date'] 	= $dateTime;

				//detail jurnal
				if ($cek_type == 'NPO') {
					$GET_UNITPRICE = $this->db->select('price_unit_rev AS unit_price, id_barang')->get_where('tran_non_po_detail', array('id' => $valx['id']))->result();
				} else {
					$GET_UNITPRICE = $this->db->select('net_price AS unit_price, id_barang')->get_where('tran_po_detail', array('id' => $valx['id']))->result();
				}
				$totalprice = ($totalprice + ($qtyIN * (!empty($GET_UNITPRICE[0]->unit_price)) ? $GET_UNITPRICE[0]->unit_price : 0));

				$ArrJurnal[$val]['unit_price'] 		= (!empty($GET_UNITPRICE[0]->unit_price)) ? $GET_UNITPRICE[0]->unit_price : 0;
				$ArrJurnal[$val]['qty'] 			= $qtyIN;
				$ArrJurnal[$val]['no_po'] 			= $no_po;
				$ArrJurnal[$val]['nm_barang'] 		= strtolower($valx['nm_barang'] . ' - ' . $valx['spec']);
				$ArrJurnal[$val]['id_barang'] 		= (!empty($GET_UNITPRICE[0]->id_barang)) ? $GET_UNITPRICE[0]->id_barang : NULL;
			}

			$file_name = NULL;
			if (!empty($_FILES["upload_doc"]["name"])) {
				$target_dir     = "assets/file/produksi/";
				$target_dir_u   = $_SERVER['DOCUMENT_ROOT'] . "/assets/file/produksi/";
				$name_file      = 'doc_incoming_' . date('Ymdhis');
				$target_file    = $target_dir . basename($_FILES["upload_doc"]["name"]);
				$name_file_ori  = basename($_FILES["upload_doc"]["name"]);
				$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
				$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;
				$file_name    	= $target_dir . $name_file . "." . $imageFileType;

				if (!empty($_FILES["upload_doc"]["tmp_name"])) {
					$terupload = move_uploaded_file($_FILES["upload_doc"]["tmp_name"], $nama_upload);
				}
			}

			$ArrInsertH = array(
				'kode_trans' 		=> $kode_trans,
				'no_ipp' 			=> $no_po,
				'tanggal' 			=> $tanggal,
				'category' 			=> 'incoming non rutin',
				'jumlah_mat' 		=> $SumMat,
				'adjustment_type' 	=> $inventory,
				'kd_gudang_dari' 	=> 'PURCHASE',
				'id_gudang_ke' 		=> $id_dept,
				'kd_gudang_ke' 		=> $id_costcenter,
				'pic' 				=> $pic,
				'note' 				=> $note,
				'created_by' 		=> $UserName,
				'created_date' 		=> $dateTime,
				'checked' 			=> 'Y',
				'jumlah_mat_check' 	=> $SumMat,
				'doc' 				=> $file_name,
				'checked_by' 		=> $UserName,
				'checked_date' 		=> $dateTime
			);

			$ArrHeader2 = array(
				'status' => 'COMPLETE',
			);

			$ArrHeader3 = array(
				'status' => 'IN PARSIAL'
			);

			// print_r($ArrUpdate);
			// print_r($ArrDeatilAdj);
			// print_r($ArrDeatilChk);
			// print_r($ArrInsertH);
			// exit;
			$this->db->trans_start();
			if ($cek_type == 'NPO') {
				$this->db->update_batch('tran_non_po_detail', $ArrUpdate, 'id');

				$qCheck = "SELECT * FROM tran_non_po_detail WHERE no_non_po='" . $no_po . "' AND qty_in < qty_rev ";
				$NumChk = $this->db->query($qCheck)->num_rows();
				if ($NumChk < 1) {
					$this->db->where('no_non_po', $no_po);
					$this->db->update('tran_non_po_header', $ArrHeader2);
				}
				if ($NumChk > 0) {
					$this->db->where('no_non_po', $no_po);
					$this->db->update('tran_non_po_header', $ArrHeader3);
				}

				// $this->db->where('no_non_po', $no_po);
				// $this->db->update('tran_non_po_header', $ArrHeader2);
			} else {
				$this->db->update_batch('tran_po_detail', $ArrUpdate, 'id');

				$qCheck = "SELECT * FROM tran_po_detail WHERE no_po='" . $no_po . "' AND qty_in < qty_po ";
				$NumChk = $this->db->query($qCheck)->num_rows();
				if ($NumChk < 1) {
					$this->db->where('no_po', $no_po);
					$this->db->update('tran_po_header', $ArrHeader2);
				}
				if ($NumChk > 0) {
					$this->db->where('no_po', $no_po);
					$this->db->update('tran_po_header', $ArrHeader3);
				}

				// $this->db->where('no_po', $no_po);
				// $this->db->update('tran_po_header', $ArrHeader2);
			}

			$this->db->insert('warehouse_adjustment', $ArrInsertH);
			$this->db->insert_batch('warehouse_adjustment_detail', $ArrDeatilAdj);
			$this->db->insert_batch('warehouse_adjustment_check', $ArrDeatilChk);
			$this->db->trans_complete();
		}


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
			insert_jurnal_department($ArrJurnal, NULL, NULL, $kode_trans, 'incoming department', 'incoming department', 'incoming department');
			history($histHlp);
		}
		echo json_encode($Arr_Data);
	}

	public function modal_detail()
	{
		$kode_trans     = $this->uri->segment(3);
		$tanda     = $this->uri->segment(4);

		$sql 		= "SELECT * FROM warehouse_adjustment_detail WHERE kode_trans='" . $kode_trans . "' ";
		$result		= $this->db->query($sql)->result_array();

		$sql_header 		= "SELECT * FROM warehouse_adjustment WHERE kode_trans='" . $kode_trans . "' ";
		$result_header		= $this->db->query($sql_header)->result();

		$data = array(
			'result' 	=> $result,
			'tanda' 	=> $tanda,
			'checked' 	=> $result_header[0]->checked,
			'kode_trans' => $result_header[0]->kode_trans,
			'no_po' 	=> $result_header[0]->no_ipp,
			'dated' 	=> date('ymdhis', strtotime($result_header[0]->created_date)),
			'resv' 		=> (!empty($result_header[0]->tanggal)) ? date('d F Y', strtotime($result_header[0]->tanggal)) : date('d F Y', strtotime($result_header[0]->created_date))

		);
		$this->template->set($data);
		$this->template->render('modal_detail');
	}

	//INCOMING ASSETS
	public function asset()
	{
		$this->db->select('"PO" AS ket, a.no_surat as no_po, b.nama as nm_supplier');
		$this->db->from('tr_purchase_order a');
		$this->db->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left');
		$this->db->where('a.tipe', 'pr asset');
		$this->db->where('(SELECT IF(SUM(aa.qty_in) IS NULL, 0, SUM(aa.qty_in)) FROM dt_trans_po aa WHERE aa.no_po = a.no_po) <=', 0);
		$no_po = $this->db->get()->result();


		$list_po	= $this->db->group_by('no_ipp')->get_where('warehouse_adjustment', array('category' => 'incoming asset'))->result_array();
		$data_gudang = $this->db->group_by('id_gudang_ke')->get_where('warehouse_adjustment', array('category' => 'incoming asset'))->result_array();

		$this->db->select('a.*');
		$this->db->from('ms_department a');
		$this->db->where('a.deleted_by', null);
		$this->db->order_by('a.nama', 'asc');
		$get_department = $this->db->get()->result();

		$this->db->select('a.*');
		$this->db->from('ms_costcenter a');
		$this->db->where('a.deleted_by', null);
		$this->db->order_by('a.nama_costcenter', 'asc');
		$get_costcenter = $this->db->get()->result();

		$data = array(
			'title'			=> 'Pembelian Non-Material & Jasa >> Incoming Asset',
			'action'		=> 'index',
			'list_po'		=> $list_po,
			'no_po'		=> $no_po,
			'data_gudang'		=> $data_gudang,
			'no_po'			=> $no_po,
			'list_department' => $get_department,
			'list_costcenter' => $get_costcenter
		);
		$this->template->set($data);
		$this->template->render('asset');
		// $this->load->view('Incoming/asset', $data);
	}

	public function server_side_incoming_asset()
	{
		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_incoming_asset(
			$requestData['no_po'],
			$requestData['gudang'],
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
		$GET_USERNAME = get_detail_user();
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

			$link = '';
			if (!empty($row['doc'])) {
				$link = "<a href='" . base_url('uploads/produksi/' . $row['doc']) . "' target='_blank' title='Download' data-role='qtip'>Download</a>";
			}

			$TANGGAL = (!empty($row['tanggal'])) ? $row['tanggal'] : $row['created_date'];
			$NM_LENGKAP = (!empty($GET_USERNAME[strtolower($row['created_by'])]['nm_lengkap'])) ? $GET_USERNAME[strtolower($row['created_by'])]['nm_lengkap'] : $row['created_by'];
			$print	= "&nbsp;<a href='" . base_url('incoming_asset/print_incoming_assets/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a>";

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['kode_trans'] . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y', strtotime($TANGGAL)) . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['no_ipp'] . "</div>";
			// $nestedData[]	= "<div align='left'>".$row['kd_gudang_dari']."</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(get_name('ms_department', 'nama', 'id', $row['id_gudang_ke'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper($row['pic']) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['note']) . "</div>";
			$nestedData[]	= "<div align='center'>" . strtoupper($NM_LENGKAP) . "</div>";
			$nestedData[]	= "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>";
			$nestedData[]	= "<div align='center'>" . $link . "</div>";
			$nestedData[]	= "<div align='center'>
									<button type='button' class='btn btn-sm btn-primary detailAjust' title='View Incoming' data-kode_trans='" . $row['kode_trans'] . "' ><i class='fa fa-eye'></i></button>
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

	public function query_data_json_incoming_asset($no_po, $gudang, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where_no_po = '';
		if (!empty($no_po)) {
			$where_no_po = " AND a.no_ipp = '" . $no_po . "' ";
		}

		$where_gudang = '';
		if (!empty($gudang)) {
			$where_gudang = " AND a.id_gudang_ke = '" . $gudang . "' ";
		}

		$sql = "
			SELECT
				a.*
			FROM
				warehouse_adjustment a
		    WHERE 1=1 AND a.category = 'incoming asset' 
			AND(
				a.no_ipp LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				or a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.id_gudang_ke LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'kode_trans',
			2 => 'no_ipp'
		);

		$sql .= " ORDER BY created_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function modal_incoming_asset()
	{
		$data = $this->input->post();

		$no_po 			= $data['no_po'];
		$id_dept 		= $data['id_dept'];
		$id_costcenter 	= $data['id_costcenter'];
		$tanggal_trans 	= $data['tanggal_trans'];
		$pic 			= strtolower($data['pic']);
		$note 			= strtolower($data['note']);

		// $cek_type = substr($no_po, 0, 3);
		// if ($cek_type == 'POX') {
		// 	$result	= $this->db->where('qty_in < qty_po')->get_where('tran_po_detail', array('no_po' => $no_po))->result_array();
		// } else {
		// 	$result	= $this->db->where('qty_in < qty_rev')->get_where('tran_non_po_detail', array('no_non_po' => $no_po))->result_array();
		// }

		// $result = $this->db->get_where('dt_trans_po', ['no_po' => $get_po['no_po']])->result_array();

		$this->db->select('a.*, b.id_dept');
		$this->db->from('dt_trans_po a');
		$this->db->join('tr_purchase_order b', 'b.no_po = a.no_po');
		$this->db->where('b.no_surat', $no_po);
		$result = $this->db->get()->result_array();

		$data = array(
			'no_po' => $no_po,
			'id_dept' => $id_dept,
			'id_costcenter' => $id_costcenter,
			'tanggal_trans' => $tanggal_trans,
			'pic' 	=> $pic,
			'note' 	=> $note,
			'result' => $result
		);

		$this->template->set($data);
		$this->template->render('modal_incoming_asset');
	}

	public function process_incoming_asset()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;
		$no_po			= $data['no_po'];
		$id_dept		= $data['id_dept'];
		$id_costcenter	= $data['id_costcenter'];
		$pic			= $data['pic'];
		$note			= $data['note'];
		$tanggal		= $data['tanggal'];
		$addInMat		= $data['addInMat'];
		$adjustment 	= $data['adjustment'];
		$Ym 			= date('ym');
		$dateTime		= date('Y-m-d H:i:s');
		$UserName		= $this->auth->user_name();
		$cek_type 		= substr($no_po, 0, 3);

		if ($adjustment == 'IN') {
			$histHlp = "Adjustment incoming asset / " . $no_po;

			//pengurutan kode
			$srcMtr			= "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRS" . $Ym . "%' ";
			$numrowMtr		= $this->db->query($srcMtr)->num_rows();
			$resultMtr		= $this->db->query($srcMtr)->result_array();
			$angkaUrut2		= $resultMtr[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 7, 4);
			$urutan2++;
			$urut2			= sprintf('%04s', $urutan2);
			$kode_trans		= "TRS" . $Ym . $urut2;

			$ArrUpdate		= array();
			$ArrDeatilAdj	= array();
			$ArrDeatilChk	= array();
			$ArrJurnal		= array();
			$SumMat = 0;
			foreach ($addInMat as $val => $valx) {
				$qtyIN 		= str_replace(',', '', $valx['qty_rev']);

				$SumMat 	+= $qtyIN;

				//update detail purchase
				$ArrUpdate[$val]['id'] 			= $valx['id'];
				$ArrUpdate[$val]['qty_in'] 		= $qtyIN;

				//detail adjustmeny
				$ArrDeatilAdj[$val]['no_ipp'] 			= $no_po;
				$ArrDeatilAdj[$val]['kode_trans'] 		= $kode_trans;
				$ArrDeatilAdj[$val]['id_po_detail'] 	= $valx['id'];
				$ArrDeatilAdj[$val]['nm_material'] 		= strtolower($valx['nm_barang']);
				$ArrDeatilAdj[$val]['nm_category'] 		= strtolower($valx['spec']);
				$ArrDeatilAdj[$val]['qty_order'] 		= $qtyIN;
				$ArrDeatilAdj[$val]['qty_oke'] 			= $qtyIN;
				$ArrDeatilAdj[$val]['keterangan'] 		= strtolower($valx['keterangan']);
				$ArrDeatilAdj[$val]['no_ba'] 		    = strtolower($valx['status']);
				$ArrDeatilAdj[$val]['ket_req_po'] 		= strtolower($valx['pemeriksa']);
				$ArrDeatilAdj[$val]['update_by'] 		= $UserName;
				$ArrDeatilAdj[$val]['update_date'] 		= $dateTime;
				$ArrDeatilAdj[$val]['check_qty_oke'] 	= $qtyIN;
				$ArrDeatilAdj[$val]['check_keterangan'] = strtolower($valx['keterangan']);

				//detail adjustmeny
				$ArrDeatilChk[$val]['no_ipp'] 		= $no_po;
				$ArrDeatilChk[$val]['id_detail'] 	= NULL;
				$ArrDeatilChk[$val]['kode_trans'] 	= $kode_trans;
				$ArrDeatilChk[$val]['nm_material'] 	= strtolower($valx['nm_barang']);
				$ArrDeatilChk[$val]['nm_category'] 	= strtolower($valx['spec']);
				$ArrDeatilChk[$val]['qty_order'] 	= $qtyIN;
				$ArrDeatilChk[$val]['qty_oke'] 		= $qtyIN;
				$ArrDeatilChk[$val]['keterangan'] 	= strtolower($valx['keterangan']);
				$ArrDeatilChk[$val]['update_by'] 	= $UserName;
				$ArrDeatilChk[$val]['update_date'] 	= $dateTime;

				//detail jurnal
				// if ($cek_type == 'NPO') {
				// 	$GET_UNITPRICE = $this->db->select('price_unit_rev AS unit_price, id_barang')->get_where('tran_non_po_detail', array('id' => $valx['id']))->result();
				// } else {
				// 	$GET_UNITPRICE = $this->db->select('net_price AS unit_price, id_barang')->get_where('tran_po_detail', array('id' => $valx['id']))->result();
				// }

				$GET_UNITPRICE = $this->db->select('hargasatuan as unit_price, id as id_barang')->get_where('dt_trans_po', ['id' => $valx['id']])->result();

				$ArrJurnal[$val]['unit_price'] 		= (!empty($GET_UNITPRICE[0]->unit_price)) ? $GET_UNITPRICE[0]->unit_price : 0;
				$ArrJurnal[$val]['qty'] 			= $qtyIN;
				$ArrJurnal[$val]['no_po'] 			= $no_po;
				$ArrJurnal[$val]['nm_barang'] 		= strtolower($valx['nm_barang']);
				$ArrJurnal[$val]['id_barang'] 		= (!empty($GET_UNITPRICE[0]->id_barang)) ? $GET_UNITPRICE[0]->id_barang : NULL;

				$assetNew = array(
					'kode_trans' 		=> $kode_trans,
					'id_barang' 		=> (!empty($GET_UNITPRICE[0]->id_barang)) ? $GET_UNITPRICE[0]->id_barang : NULL,
					'nama' 		        => strtolower($valx['nm_barang']),
					'nilai_aset' 		=> (!empty($GET_UNITPRICE[0]->unit_price)) ? $GET_UNITPRICE[0]->unit_price : 0,
					'tanggal' 			=> $tanggal,
					'created_by' 		=> $UserName,
					'created_date' 		=> $dateTime,

				);

				$this->db->insert('asset_new', $assetNew);
			}

			$file_name = NULL;
			if (!empty($_FILES["upload_doc"]["tmp_name"])) {
				$config['upload_path'] = 'uploads/produksi/';
				$config['allowed_types'] = '*';
				$config['remove_spaces'] = TRUE;
				$config['encrypt_name'] = TRUE;

				$this->load->library('upload', $config);
				$this->upload->initialize($config);
				if ($this->upload->do_upload('upload_doc')) {
					$uploadData = $this->upload->data();
					$file_name = $uploadData['file_name'];
				}
				// $terupload = move_uploaded_file($_FILES["upload_doc"]["tmp_name"], $nama_upload);
			}

			$ArrInsertH = array(
				'kode_trans' 		=> $kode_trans,
				'no_ipp' 			=> $no_po,
				'tanggal' 			=> $tanggal,
				'category' 			=> 'incoming asset',
				'jumlah_mat' 		=> $SumMat,
				'kd_gudang_dari' 	=> 'PURCHASE',
				'id_gudang_ke' 		=> $id_dept,
				'kd_gudang_ke' 		=> $id_costcenter,
				'pic' 				=> $pic,
				'note' 				=> $note,
				'created_by' 		=> $UserName,
				'created_date' 		=> $dateTime,
				'checked' 			=> 'Y',
				'jumlah_mat_check' 	=> $SumMat,
				'doc' 				=> $file_name,
				'checked_by' 		=> $UserName,
				'checked_date' 		=> $dateTime
			);




			$ArrHeader2 = array(
				'status' => 'COMPLETE',
			);
			// print_r($ArrUpdate);
			// print_r($ArrDeatilAdj);
			// print_r($ArrDeatilChk);
			// print_r($ArrInsertH);
			// exit;
			$this->db->trans_start();
			// if ($cek_type == 'NPO') {
			// 	$this->db->update_batch('tran_non_po_detail', $ArrUpdate, 'id');
			// 	$this->db->where('no_non_po', $no_po);
			// 	$this->db->update('tran_non_po_header', $ArrHeader2);
			// } else {
			// 	$this->db->update_batch('tran_po_detail', $ArrUpdate, 'id');
			// 	$this->db->where('no_po', $no_po);
			// 	$this->db->update('tran_po_header', $ArrHeader2);
			// }

			$update_po_detail = $this->db->update_batch('dt_trans_po', $ArrUpdate, 'id');
			if (!$update_po_detail) {
				print_r($this->db->error($update_po_detail));
				$this->db->trans_rollback();
				exit;
			}

			$insert_warehouse = $this->db->insert('warehouse_adjustment', $ArrInsertH);
			if (!$insert_warehouse) {
				print_r($this->db->error($insert_warehouse));
				$this->db->trans_rollback();
				exit;
			}
			$insert_warehouse_detail = $this->db->insert_batch('warehouse_adjustment_detail', $ArrDeatilAdj);
			if (!$insert_warehouse_detail) {
				print_r($this->db->error($insert_warehouse_detail));
				$this->db->trans_rollback();
				exit;
			}
			$insert_warehouse_check = $this->db->insert_batch('warehouse_adjustment_check', $ArrDeatilChk);
			if (!$insert_warehouse_check) {
				print_r($this->db->error($insert_warehouse_check));
				$this->db->trans_rollback();
				exit;
			}

			$this->db->trans_complete();
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0
			);
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1
			);
			insert_jurnal_department($ArrJurnal, NULL, NULL, $kode_trans, 'incoming asset', 'incoming asset', 'incoming asset');
			history($histHlp);

			$valid = 1;
		}
		echo $valid;
	}

	public function print_incoming_dept()
	{
		$kode_trans     = $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $data_session['ORI_User']['username'];

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$data = array(
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'kode_trans' => $kode_trans
		);

		history('Print Incoming Department ' . $kode_trans);
		$this->load->view('Print/print_incoming_dept', $data);
	}

	public function print_incoming_assets()
	{
		$kode_trans     = $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $this->auth->user_name();

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$data = array(
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'kode_trans' => $kode_trans
		);

		history('Print Incoming Assets ' . $kode_trans);

		$today = date('l, d F Y [H:i:s]');

		$this->load->library(array('Mpdf'));
		$mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
		$mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('print_incoming_assets', $data);
		$this->mpdf->AddPage('L', 'A4', 'en');
		$footer = 'Printed by : ' . ucfirst(strtolower($this->auth->user_name())) . ', ' . $today . ' / ' . $kode_trans . '';

		$mpdf->showWatermarkText = true;
		$mpdf->SetTitle($kode_trans . "/" . date('ymdhis'));
		$mpdf->AddPage();
		$mpdf->SetFooter($footer);
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output('tanda-terima-pembelian-asset-' . $kode_trans . '.pdf', 'I');


		// $this->template->set($data);
		// $this->template->render('print_incoming_assets');
	}
}
