<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Pr_asset extends Admin_Controller
{

	protected $viewPermission 	= 'Master_Materials.View';
	protected $addPermission  	= 'Master_Materials.Add';
	protected $managePermission = 'Master_Materials.Manage';
	protected $deletePermission = 'Master_Materials.Delete';

	protected $viewPermission_head 	= 'Approval_PR_Asset_Head.View';
	protected $addPermission_head  	= 'Approval_PR_Asset_Head.Add';
	protected $managePermission_head = 'Approval_PR_Asset_Head.Manage';
	protected $deletePermission_head = 'Approval_PR_Asset_Head.Delete';

	protected $viewPermission_cost_control 	= 'Approval_PR_Asset_Cost_Control.View';
	protected $addPermission_cost_control  	= 'Approval_PR_Asset_Cost_Control.Add';
	protected $managePermission_cost_control = 'Approval_PR_Asset_Cost_Control.Manage';
	protected $deletePermission_cost_control = 'Approval_PR_Asset_Cost_Control.Delete';

	protected $viewPermission_management 	= 'Approval_PR_Asset_Management.View';
	protected $addPermission_management  	= 'Approval_PR_Asset_Management.Add';
	protected $managePermission_management = 'Approval_PR_Asset_Management.Manage';
	protected $deletePermission_management = 'Approval_PR_Asset_Management.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Pr_asset_model');
		$this->load->model('master_model');
	}

	public function index()
	{
		// $SQL_LAST = $this->db->select('MAX(MONTH(bulan)) AS bulan, MAX(YEAR(tahun)) AS tahun')->get_where('asset_generatex',array('flag'=>'N'))->result();

		$data = array(
			'title'			=> 'Indeks Of Assets',
			'action'		=> 'asset',
			'kategori' 		=> $this->Pr_asset_model->getList('asset_category'),
			// 'bulan' 		=> date('F',strtotime($SQL_LAST[0]->bulan)),
			// 'tahun' 		=> date('Y',strtotime($SQL_LAST[0]->tahun)),
		);
		history("View index asset");
		$this->load->view('Asset/index', $data);
	}

	public function data_side()
	{
		$this->Pr_asset_model->getDataJSON();
	}

	public function modal_view()
	{
		$id = $this->uri->segment(3);
		$qData	= "SELECT a.*, b.nm_costcenter FROM asset a LEFT JOIN costcenter b ON a.id_costcenter=b.id_costcenter WHERE a.id='" . $this->uri->segment(3) . "'";
		$dataD	= $this->db->query($qData)->result_array();

		$data = array(
			'title'			=> 'Indeks Of Assets',
			'action'		=> 'asset',
			'dataD'			=> $dataD,
			'list_cab' 		=> $this->Pr_asset_model->getList('asset_branch'),
			'list_pajak'	=> $this->Pr_asset_model->getList('asset_category_pajak'),
			'list_dept' 	=> $this->Pr_asset_model->getList('department'),
			'list_catg' 	=> $this->Pr_asset_model->getList('asset_category'),
			'list_coa' 		=> $this->Pr_asset_model->getList('asset_coa')
		);
		history("View index asset");
		$this->load->view('Asset/modal_view', $data);
	}

	public function add()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$db2 			= $this->load->database('instalasi', TRUE);
			$id				= $data['id'];
			$kd_asset		= $data['kd_asset'];


			$nmCategory		= $this->Pr_asset_model->getWhere('asset_category', 'id', $data['category']);

			$id_coa			= $data['id_coa'];
			$category		= $data['category'];
			$penyusutan		= $data['penyusutan'];
			$category_pajak	= $data['category_pajak'];
			$KdCategory		= sprintf('%02s', $category);
			$KdCategoryPjk	= sprintf('%02s', $category_pajak);
			$Ym				= date('ym');
			$tgl_oleh		= date('Y-m-d');
			$tgl_perolehan	= date('Y-m-d');

			$branch		= $data['branch'];

			if (!empty($data['tanggal'])) {
				$tgl_oleh		= date('Y-m-d', strtotime($data['tanggal']));
				$Year			= date('y', strtotime($data['tanggal']));
				$Month			= date('m', strtotime($data['tanggal']));
				$Ym				= $Year . $Month;
			}

			if (!empty($data['tanggal_oleh'])) {
				$tgl_perolehan		= date('Y-m-d', strtotime($data['tanggal_oleh']));
				$Year_perolehan		= date('y', strtotime($data['tanggal_oleh']));
				$Month_perolehan	= date('m', strtotime($data['tanggal_oleh']));
				$Ym_perolehan		= $Year_perolehan . $Month_perolehan;
			}

			$qQuery			= "SELECT max(kd_asset) as maxP FROM asset WHERE kd_asset LIKE '" . $branch . "-" . $Ym_perolehan . $KdCategory . $KdCategoryPjk . "-%' "; //category='".$category."' AND 
			$restQuery		= $this->db->query($qQuery)->result_array();

			$angkaUrut2		= $restQuery[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 13, 3);
			$urutan2++;
			$urut2			= sprintf('%03s', $urutan2);

			$kode_assets	= $branch . "-" . $Ym_perolehan . $KdCategory . $KdCategoryPjk . "-" . $urut2;

			//kode group
			$q_group		= "SELECT max(code_group) as maxP FROM asset WHERE code_group LIKE 'AS%' ";
			$rest_group		= $this->db->query($q_group)->result_array();

			$angka_group	= $rest_group[0]['maxP'];
			$urut_g			= (int)substr($angka_group, 2, 5);
			$urut_g++;
			$urut			= sprintf('%05s', $urut_g);
			$kode_group		= "AS" . $urut;

			//insert to instalasi
			$ArrHeaderInstalasi = array(
				'code_group' 	=> $kode_group,
				'category' 		=> 'asset ' . strtolower($nmCategory[0]['nm_category']),
				'spec' 			=> strtolower($data['nm_asset']),
				'created_by' 	=> $this->session->userdata['ORI_User']['username'],
				'created_date' 	=> date('Y-m-d h:i:s')
			);

			$num_cty = $db2->query("SELECT * FROM vehicle_tool_category WHERE category='asset " . strtolower($nmCategory[0]['nm_category']) . "' ")->num_rows();

			$ArrCategory = array(
				'category' 		=> 'asset ' . strtolower($nmCategory[0]['nm_category']),
				'created_by' 	=> 'asset',
				'created_date' 	=> date('Y-m-d h:i:s')
			);

			$region = $db2->query("SELECT * FROM region ORDER BY urut ASC")->result_array();
			$ArrPrice = array();
			foreach ($region as $key => $value) {
				$ArrPrice[$key]['category'] 		= 'vehicle tool';
				$ArrPrice[$key]['code_group'] 		= $kode_group;
				$ArrPrice[$key]['unit_material'] 	= 'month';
				$ArrPrice[$key]['kurs'] 			= 'IDR';
				$ArrPrice[$key]['region'] 			= $value['region'];
				$ArrPrice[$key]['rate'] 			= str_replace(',', '', $data['value']);
				$ArrPrice[$key]['updated_by'] 		= $this->session->userdata['ORI_User']['username'];
				$ArrPrice[$key]['updated_date'] 	= date('Y-m-d h:i:s');
			}

			$config = array(
				'upload_path' 		=> './assets/foto/',
				'allowed_types' 		=> 'gif|jpg|png|jpeg|JPG|PNG',
				'file_name' 			=> $kode_assets,
				'file_ext_tolower' 	=> TRUE,
				'overwrite' 			=> TRUE,
				'max_size' 			=> 2000048,
				'remove_spaces' 		=> TRUE
			);

			$tmp 		= explode(".", $_FILES['foto']['name']);
			$ext 		= end($tmp);
			$pic 		= $kode_assets . "." . strtolower($ext);

			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('foto')) {
				$result = $this->upload->display_errors();
			} else {
				$paths 		= $_SERVER['DOCUMENT_ROOT'] . '/assets/foto/' . $pic;
				if (file_exists($paths)) {
					unlink($paths);
				}
				$data_foto  = array('upload_data' => $this->upload->data('foto'));
			}

			$detailDataDash	= array();
			// echo $kode_assets; exit;

			$lopp 	= 0;
			$lopp2 	= 0;
			for ($no = 1; $no <= $data['qty']; $no++) {
				$Nomor	= sprintf('%03s', $no);
				$lopp++;
				$detailData[$lopp]['kd_asset'] 		= $kode_assets . $Nomor;
				$detailData[$lopp]['code_group'] 	= $kode_group;
				$detailData[$lopp]['nm_asset'] 		= $data['nm_asset'];
				$detailData[$lopp]['tgl_perolehan'] = $tgl_perolehan;
				$detailData[$lopp]['id_coa'] 		= $id_coa;
				$detailData[$lopp]['category'] 		= $data['category'];
				$detailData[$lopp]['category_pajak'] = $data['category_pajak'];
				$detailData[$lopp]['nm_category'] 	= strtoupper($nmCategory[0]['nm_category']);
				$detailData[$lopp]['nilai_asset'] 	= str_replace(',', '', $data['nilai_asset']);
				$detailData[$lopp]['qty'] 			= $data['qty'];
				$detailData[$lopp]['asset_ke'] 		= $no;
				$detailData[$lopp]['depresiasi'] 	= $data['depresiasi'];
				$detailData[$lopp]['value'] 		= str_replace(',', '', $data['value']);
				$detailData[$lopp]['kdcab'] 		= $branch;
				$detailData[$lopp]['foto'] 			= $pic;
				$detailData[$lopp]['penyusutan'] 	= $penyusutan;
				$detailData[$lopp]['id_dept'] 		= $data['lokasi_asset'];
				$detailData[$lopp]['department'] 	= get_name('department', 'nm_dept', 'id', $data['lokasi_asset']);
				$detailData[$lopp]['id_costcenter'] = $data['cost_center'];
				$detailData[$lopp]['nama_user'] 	= $data['nama_user'];
				$detailData[$lopp]['cost_center'] 	= get_name('costcenter', 'nm_costcenter', 'id_costcenter', $data['cost_center']);
				$detailData[$lopp]['created_by'] 	= $this->session->userdata['ORI_User']['username'];
				$detailData[$lopp]['created_date'] 	= date('Y-m-d h:i:s');
				$detailData[$lopp]['tgl_depresiasi'] = $tgl_oleh;

				$jmlx   	= $data['depresiasi'] * 12;
				$date_now 	= date('Y-m-d');
				$date_now_real 	= date('Y-m-d');

				if (!empty($data['tanggal'])) {
					$date_now 	= date('Y-m-d', strtotime($data['tanggal']));
				}

				for ($x = 1; $x <= $jmlx; $x++) {
					$lopp2 += $x;

					//bulan depat mulai menyusut
					// $Tanggal 	= date('Y-m', mktime(0,0,0,substr($date_now,5,2)+ $x,1,substr($date_now,0,4)));
					//bulan sekarang langsung disusutkan
					$TglNow		= date('Y-m', strtotime($date_now_real));
					$Tanggal 	= date('Y-m', mktime(0, 0, 0, substr($date_now, 5, 2) + $x, 0, substr($date_now, 0, 4)));
					$flagx		= 'X';
					if ($penyusutan == 'Y') {
						$flagx		= 'N';
						if ($Tanggal < $TglNow) {
							$flagx	= 'Y';
						}
					}

					$detailDataDash[$lopp2]['kd_asset'] 	= $kode_assets . $Nomor;
					$detailDataDash[$lopp2]['nm_asset'] 	= $data['nm_asset'];
					$detailDataDash[$lopp2]['category'] 	= $data['category'];
					$detailDataDash[$lopp2]['category_pajak'] 	= $data['category_pajak'];
					$detailDataDash[$lopp2]['nm_category'] 	= strtoupper($nmCategory[0]['nm_category']);
					$detailDataDash[$lopp2]['bulan'] 		= substr($Tanggal, 5, 2);
					$detailDataDash[$lopp2]['tahun'] 		= substr($Tanggal, 0, 4);
					$detailDataDash[$lopp2]['lokasi_asset'] = $data['lokasi_asset'];
					$detailDataDash[$lopp2]['cost_center'] 	= $data['cost_center'];
					$detailDataDash[$lopp2]['nilai_susut'] 	= str_replace(',', '', $data['value']);
					$detailDataDash[$lopp2]['kdcab'] 		= $branch;
					$detailDataDash[$lopp2]['flag'] 		= $flagx;
				}
			}

			$tanda = "Insert ";
			$tanda2 = $kode_assets;


			// print_r($ArrHeaderInstalasi);
			// print_r($ArrPrice);
			// echo $num_cty;
			// exit;

			$this->db->trans_start();
			$this->db->insert_batch('asset', $detailData);
			$this->db->insert_batch('asset_generate', $detailDataDash);

			$db2->insert('vehicle_tool_new', $ArrHeaderInstalasi);
			$db2->insert_batch('price_ref', $ArrPrice);
			if ($num_cty < 1) {
				$db2->insert('vehicle_tool_category', $ArrCategory);
			}

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
				history($tanda . "asset " . $tanda2);
			}

			echo json_encode($Arr_Data);
		} else {
			$id = $this->uri->segment(3);
			$header = $this->Pr_asset_model->getWhere('asset', 'id', $id);
			$data = array(
				'title'			=> 'Add Asset',
				'action'		=> 'add',
				'data' 			=> $header,
				'list_cab' 		=> $this->Pr_asset_model->getList('asset_branch'),
				'list_coa' 		=> $this->Pr_asset_model->getList('asset_coa'),
				'list_pajak'	=> $this->Pr_asset_model->getList('asset_category_pajak'),
				'list_dept' => $this->Pr_asset_model->getList('department'),
				'list_catg' => $this->Pr_asset_model->getList('asset_category')
			);
			$this->load->view('Asset/add', $data);
		}
	}


	public function edit()
	{

		$id = $this->uri->segment(3);

		$get_pr_detail = $this->db->get_where('tran_pr_detail', ['id' => $id])->row();
		$get_pr = $this->db->get_where('tran_pr_header', ['no_pr' => $get_pr_detail->no_pr])->row();
		$get_asset = $this->db->get_where('asset_planning', ['no_pr' => $get_pr->no_pr])->result();

		$list_department = $this->db->get_where('ms_department', ['deleted_by' => null])->result_array();
		$list_costcenter = $this->db->get_where('ms_costcenter', ['deleted_by' => null])->result_array();
		$datacoa = $this->db->like('no_perkiraan', '13', 'after')->get_where(DBACC . '.coa_master', array('level' => '5', 'no_perkiraan not like ' => '1309%'))->result_array();
		$penyusutan = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE `level`='5' AND (nama LIKE 'DEPRECIATION%') ORDER BY no_perkiraan ASC")->result_array();

		$data = [
			'title' => 'Edit Asset Planning',
			'id' => $id,
			'data_pr' => $get_pr,
			'data_asset' => $get_asset,
			'approve' => '',
			'list_department' => $list_department,
			'list_costcenter' => $list_costcenter,
			'datacoa'		=> $datacoa,
			'penyusutan'	=> $penyusutan
		];

		$this->template->set($data);
		$this->template->render('edit');
	}


	//move asset
	public function edited()
	{

		$Arr_Kembali	= array();
		$data			= $this->input->post();

		$branch				= $data['branch'];
		$kd_asset			= $data['kd_asset'];
		$lokasi_asset_new	= $data['lokasi_asset_new'];
		$cost_center_new	= $data['cost_center_new'];

		$ArrUpHeader = array(
			'id_coa'		=> $data['id_coa'],
			'category'		=> $data['category'],
			'modified_by' 	=> $this->session->userdata['ORI_User']['username'],
			'modified_date' => date('Y-m-d h:i:s')
		);



		$this->db->trans_start();
		$this->db->where('kd_asset', $kd_asset);
		$this->db->update('asset', $ArrUpHeader);

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
			history('Move asset to asset');
		}

		echo json_encode($Arr_Data);
	}


	//move asset
	public function move_asset()
	{

		$Arr_Kembali	= array();
		$data			= $this->input->post();

		$branch				= $data['branch'];
		$kd_asset			= $data['kd_asset'];
		$lokasi_asset_new	= $data['lokasi_asset_new'];
		$cost_center_new	= $data['cost_center_new'];

		$ArrUpHeader = array(
			'kdcab' 	=> $branch,
			'id_dept' 	=> $lokasi_asset_new,
			'department' 	=> get_name('department', 'nm_dept', 'id', $lokasi_asset_new),
			'id_costcenter'	=> $cost_center_new,
			'cost_center' 	=> get_name('costcenter', 'nm_costcenter', 'id_costcenter', $cost_center_new),
			'modified_by' 	=> $this->session->userdata['ORI_User']['username'],
			'modified_date' => date('Y-m-d h:i:s')
		);

		$ArrUpGen = array(
			'kdcab' 	=> $branch,
			'lokasi_asset' 	=> $lokasi_asset_new,
			'cost_center'	=> $cost_center_new
		);

		// echo $cost_center_new; exit;



		// print_r($detailData);
		// print_r($detailDataDash);
		// exit;

		$this->db->trans_start();
		$this->db->where('kd_asset', $kd_asset);
		$this->db->update('asset', $ArrUpHeader);

		$this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'N'));
		$this->db->update('asset_generate', $ArrUpGen);
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
			history('Move asset to asset');
		}

		echo json_encode($Arr_Data);
	}

	//delete asset
	public function delete_asset()
	{

		$Arr_Kembali	= array();
		$data			= $this->input->post();

		$kd_asset		= $this->uri->segment(3);

		$ArrUpHeader = array(
			'deleted_by' 	=> $this->session->userdata['ORI_User']['username'],
			'deleted_date' => date('Y-m-d h:i:s')
		);

		$ArrUpGen = array(
			'flag' 	=> 'L'
		);

		$this->db->trans_start();
		$this->db->where('kd_asset', $kd_asset);
		$this->db->update('asset', $ArrUpHeader);

		$this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'N'));
		$this->db->update('asset_generate', $ArrUpGen);

		$this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'X'));
		$this->db->update('asset_generate', $ArrUpGen);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal dihapus ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil dihapus. Thanks ...',
				'status'	=> 1
			);
			history('Delete asset ' . $kd_asset);
		}

		echo json_encode($Arr_Data);
	}

	public function list_center()
	{
		$id = $this->uri->segment(3);
		$cs = $this->uri->segment(4);
		$query	 	= "SELECT * FROM costcenter WHERE id_dept='" . $id . "' AND deleted='N' ORDER BY nm_costcenter ASC";
		$Q_result	= $this->db->query($query)->result();
		if (!empty($Q_result)) {
			$option 	= "<option value='0'>Select Costcenter</option>";
			foreach ($Q_result as $row) {
				$selx = ($row->id_costcenter == $cs) ? 'selected' : '';
				$option .= "<option value='" . $row->id_costcenter . "' " . $selx . ">" . strtoupper($row->nm_costcenter) . "</option>";
			}
		} else {
			$option 	= "<option value='0'>List Empty</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function get_jangka_waktu()
	{
		$id = $this->uri->segment(3);
		$query	 	= "SELECT * FROM asset_category_pajak WHERE id='" . $id . "' ";
		$Q_result	= $this->db->query($query)->result();
		$data 	 	= $Q_result[0]->jangka_waktu;
		echo json_encode(array(
			'jangka_waktu' => $data
		));
	}

	//======================================================================================================================
	//===================================================CATEGORY============================================================
	//======================================================================================================================

	public function category()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data = array(
			'title'			=> 'Indeks Of Asset Category',
			'action'		=> 'category',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses
		);
		history('View Data Master Asset category');
		$this->load->view('Asset/category', $data);
	}

	public function data_side_category()
	{
		$this->Pr_asset_model->get_json_category();
	}

	public function add_category()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$dateTime		= date('Y-m-d H:i:s');

			//header
			$id 		    = $data['id'];
			$nm_category	= strtoupper($data['nm_category']);
			$status			= $data['status'];

			if (empty($id)) {
				$ArrHeader = array(
					'nm_category'   => $nm_category,
					'status' 		=> $status,
					'created_by' 	=> $data_session['ORI_User']['username'],
					'created_date' 	=> $dateTime
				);
				$TandaI = "Insert";
			}

			if (!empty($id)) {
				$ArrHeader = array(
					'nm_category'   => $nm_category,
					'status' 		=> $status,
					'updated_by' 	=> $data_session['ORI_User']['username'],
					'updated_date' 	=> $dateTime
				);
				$TandaI = "Update";
			}

			// print_r($ArrHeader);
			// exit;

			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('asset_category', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('asset_category', $ArrHeader);
			}
			$this->db->trans_complete();


			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> $TandaI . ' data failed. Please try again later ...',
					'status'	=> 2
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> $TandaI . ' data success. Thanks ...',
					'status'	=> 1
				);
				history($TandaI . ' Category Asset ' . $id . ' / ' . $nm_category);
			}

			echo json_encode($Arr_Kembali);
		} else {
			$controller			= ucfirst(strtolower($this->uri->segment(1)));
			$Arr_Akses			= getAcccesmenu($controller);
			if ($Arr_Akses['create'] != '1') {
				$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
				redirect(site_url('users'));
			}

			$id = $this->uri->segment(3);
			$query = "SELECT * FROM asset_category WHERE id ='" . $id . "' LIMIT 1 ";
			$result = $this->db->query($query)->result();

			$data = array(
				'title'		=> 'Add Category Asset',
				'action'	=> 'add',
				'data'      => $result
			);
			$this->load->view('Asset/add_category', $data);
		}
	}

	public function hapus_category()
	{
		$id = $this->uri->segment(3);
		$data_session	= $this->session->userdata;

		$ArrPlant		= array(
			'deleted' 		=> 'Y',
			'deleted_by' 	=> $data_session['ORI_User']['username'],
			'deleted_date' 	=> date('Y-m-d H:i:s')
		);


		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('asset_category', $ArrPlant);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data success. Thanks ...',
				'status'	=> 1
			);
			history('Delete Category Asset Data : ' . $id);
		}
		echo json_encode($Arr_Data);
	}

	//======================================================================================================================
	//===================================================PR ASSET============================================================
	//======================================================================================================================

	public function pr()
	{
		$this->Pr_asset_model->pr();
	}

	public function approval_head()
	{
		$this->auth->restrict($this->viewPermission_head);
		$this->Pr_asset_model->approval_head();
	}

	public function approval_cost_control()
	{
		$this->auth->restrict($this->viewPermission_cost_control);
		$this->Pr_asset_model->approval_cost_control();
	}

	public function approval_management()
	{
		$this->auth->restrict($this->viewPermission_management);
		$this->Pr_asset_model->approval_management();
	}

	public function server_side_pr_asset()
	{
		$this->Pr_asset_model->get_data_json_pr_asset();
	}

	public function add_pr()
	{
		$this->Pr_asset_model->add_pr();
	}

	public function server_side_add_pr_asset()
	{
		$this->Pr_asset_model->get_data_json_add_pr_asset();
	}

	public function approve_pr()
	{
		$this->Pr_asset_model->approve_pr();
	}

	public function print_pr_asset()
	{
		ob_clean();
		ob_start();

		$no_pr 	= $this->uri->segment(3);
		$sql	= "	SELECT
						a.*
					FROM
						tran_pr_detail a LEFT JOIN tran_pr_header b ON a.no_pr = b.no_pr
					WHERE  1=1 AND a.category='asset' AND a.no_pr = '" . $no_pr . "'";
		$result = $this->db->query($sql)->result_array();

		$result_header = $this->db->get_where('tran_pr_header', ['no_pr' => $no_pr])->row_array();

		$data = array(
			'no_pr'		=> $no_pr,
			'result'		=> $result,
			'result_header' => $result_header
		);

		$this->load->view('print_pr_asset', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html = ob_get_contents();

		$html2pdf->WriteHTML($html);

		ob_end_clean();
		$html2pdf->Output('PR Asset.pdf', 'I');


		// $this->template->set($data);
		// $this->template->render('print_pr_asset');
	}

	//generate asset manual
	public function generate_asset_manual()
	{
		// $get_asset = $this->db->get_where('asset',array('depresiasi >'=>0,'id'=>'4'))->result_array();
		$get_asset = $this->db->order_by('kd_asset', 'asc')->get_where('asset', array('depresiasi >' => 0))->result_array();
		$ArrDetailAsset = [];
		$nomor = 0;
		foreach ($get_asset as $key => $value) {
			if ($value['kd_asset'] != 'ORI-22000000-000001' and $value['kd_asset'] != 'ORI-22000000-000002' and $value['kd_asset'] != 'ORI-22000000-000419') {
				// if($value['kd_asset'] != 'ORI-22000000-000001' AND $value['kd_asset'] != 'ORI-22000000-000002'){
				$key++;
				$TOTAL_BULAN 	= $value['depresiasi'] * 12;
				$TGL_AWAL 		= $value['tgl_perolehan'];
				$TGL_NOW		= 202205;
				for ($i = 0; $i < $TOTAL_BULAN; $i++) {
					$nomor++;
					$BULAN = date('m', strtotime('+' . $i . ' month', strtotime($TGL_AWAL)));
					$TAHUN = date('Y', strtotime('+' . $i . ' month', strtotime($TGL_AWAL)));
					$TGLYM = date('Ym', strtotime('+' . $i . ' month', strtotime($TGL_AWAL)));
					$FLAG = 'Y';
					if ($TGLYM >= $TGL_NOW) {
						$FLAG = 'N';
					}
					$ArrDetailAsset[$nomor]['kd_asset'] 		= $value['kd_asset'];
					$ArrDetailAsset[$nomor]['nm_asset'] 		= $value['nm_asset'];
					$ArrDetailAsset[$nomor]['category'] 		= $value['category'];
					$ArrDetailAsset[$nomor]['category_pajak'] 	= $value['category_pajak'];
					$ArrDetailAsset[$nomor]['nm_category'] 		= $value['nm_category'];
					$ArrDetailAsset[$nomor]['bulan'] 			= $BULAN;
					$ArrDetailAsset[$nomor]['tahun'] 			= $TAHUN;
					$ArrDetailAsset[$nomor]['nilai_susut'] 		= $value['value'];
					$ArrDetailAsset[$nomor]['lokasi_asset'] 	= $value['id_dept'];
					$ArrDetailAsset[$nomor]['cost_center'] 		= $value['id_costcenter'];
					$ArrDetailAsset[$nomor]['kdcab'] 			= $value['kdcab'];
					$ArrDetailAsset[$nomor]['flag'] 			= $FLAG;
				}
			}
			# code...
		}
		// echo "<pre>";
		// print_r($ArrDetailAsset);
		$whereNotIN = array('ORI-22000000-000001', 'ORI-22000000-000002', 'ORI-22000000-000419');
		// $whereNotIN = array('ORI-22000000-000001', 'ORI-22000000-000002');
		$this->db->where_not_in('kd_asset', $whereNotIN);
		$this->db->delete('asset_generate');

		$this->db->insert_batch('asset_generate', $ArrDetailAsset);
		echo 'Success Insert !';
	}


	//JURNAL
	public function modal_jurnal()
	{
		$this->load->view('Asset/modal_jurnal');
	}

	public function saved_jurnal()
	{
		$session 		= $this->session->userdata('app_session');
		$ArrDel = $this->db->query("SELECT nomor FROM jurnaltras WHERE jenis_trans = 'asset jurnal' AND SUBSTRING_INDEX(tanggal, '-', 2) = '" . date('Y-m') . "' GROUP BY nomor ")->result_array();

		$dtListArray = array();
		foreach ($ArrDel as $val => $valx) {
			$dtListArray[$val] = $valx['nomor'];
		}

		$dtImplode	= "('" . implode("','", $dtListArray) . "')";

		$date_now	= date('Y-m-d');
		$bln		= ltrim(date('m'), 0);
		$thn		= date('Y');
		$bulanx		= date('m');

		if (!empty($this->input->post('tgl_jurnal'))) {
			$date_now	= $this->input->post('tgl_jurnal') . "-01";
			$DtExpl		= explode('-', $date_now);
			$bln		= ltrim($DtExpl[1], 0);
			$thn		= $DtExpl[0];
			$bulanx		= $DtExpl[1];
		}
		// print_r($dtImplode);
		// exit;

		$ArrJurnal_D = $this->Asset_model->getList('asset_jurnal');
		$ArrDebit = array();
		$ArrKredit = array();
		$ArrJavh = array();
		$Loop = 0;
		foreach ($ArrJurnal_D as $val => $valx) {
			$Loop++;

			if ($valx['category'] == 1) {
				$coaD 	= "6831-02-01";
				$ketD	= "BIAYA PENYUSUTAN KENDARAAN";
				$coaK 	= "1309-05-01";
				$ketK	= "AKUMULASI PENYUSUTAN KENDARAAN";
			}
			if ($valx['category'] == 2) {
				$coaD 	= "6831-06-01";
				$ketD	= "BIAYA PENYUSUTAN HARTA LAINNYA";
				$coaK 	= "1309-08-01";
				$ketK	= "AKUMULASI PENYUSUTAN HARTA LAINNYA";
			}
			if ($valx['category'] == 3) {
				$coaD 	= "6831-01-01";
				$ketD	= "BIAYA PENYUSUTAN BANGUNAN";
				$coaK 	= "1309-07-01";
				$ketK	= "AKUMULASI PENYUSUTAN BANGUNAN";
			}

			$ArrDebit[$Loop]['tipe'] 			= "JV";
			$ArrDebit[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrDebit[$Loop]['tanggal'] 		= $date_now;
			$ArrDebit[$Loop]['no_perkiraan'] 	= $coaD;
			$ArrDebit[$Loop]['keterangan'] 		= $ketD;
			$ArrDebit[$Loop]['no_reff'] 		= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrDebit[$Loop]['debet'] 			= $valx['sisa_nilai'];
			$ArrDebit[$Loop]['kredit'] 			= 0;
			$ArrDebit[$Loop]['jenis_trans'] 	= 'asset jurnal';

			$ArrKredit[$Loop]['tipe'] 			= "JV";
			$ArrKredit[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrKredit[$Loop]['tanggal'] 		= $date_now;
			$ArrKredit[$Loop]['no_perkiraan'] 	= $coaK;
			$ArrKredit[$Loop]['keterangan'] 	= $ketK;
			$ArrKredit[$Loop]['no_reff'] 		= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrKredit[$Loop]['debet'] 			= 0;
			$ArrKredit[$Loop]['kredit'] 		= $valx['sisa_nilai'];
			$ArrKredit[$Loop]['jenis_trans'] 	= 'asset jurnal';

			$ArrJavh[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrJavh[$Loop]['tgl'] 				= $date_now;
			$ArrJavh[$Loop]['jml'] 				= $valx['sisa_nilai'];
			$ArrJavh[$Loop]['kdcab'] 			= $valx['kdcab'];
			$ArrJavh[$Loop]['jenis'] 			= "V";
			$ArrJavh[$Loop]['keterangan'] 		= "PENYUSUTAN ASSET";
			$ArrJavh[$Loop]['bulan'] 			= $bln;
			$ArrJavh[$Loop]['tahun'] 			= $thn;
			$ArrJavh[$Loop]['user_id'] 			= "System";
			$ArrJavh[$Loop]['tgl_jvkoreksi'] 	= $date_now;

			$this->Jurnal_model->update_Nomor_Jurnal($valx['kdcab'], 'JM');
		}

		// echo "<pre>";
		// print_r($ArrDebit);
		// print_r($ArrKredit);
		// print_r($ArrJavh);
		// exit;

		$this->db->trans_start();
		$this->db->query("DELETE FROM jurnaltras WHERE nomor IN " . $dtImplode . " ");
		$this->db->query("DELETE FROM javh WHERE nomor IN " . $dtImplode . " ");
		$this->db->insert_batch('jurnaltras', $ArrDebit);
		$this->db->insert_batch('jurnaltras', $ArrKredit);
		$this->db->insert_batch('javh', $ArrJavh);
		$this->db->query("UPDATE asset_generate SET flag='Y' WHERE bulan='" . $bulanx . "' AND tahun='" . $thn . "' ");
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$this->db->query("INSERT INTO asset_jurnal_log (tanggal, ket, jurnal_by, bulan, tahun, kdcab) VALUES ('" . date('Y-m-d H:i:s') . "', 'FAILED', '" . $this->session->userdata['app_session']['username'] . "', '" . $bulanx . "', '" . $thn . "', '" . $session['kdcab'] . "')");
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$this->db->query("INSERT INTO asset_jurnal_log (tanggal, ket, jurnal_by, bulan, tahun, kdcab) VALUES ('" . date('Y-m-d H:i:s') . "', 'SUCCESS', '" . $this->session->userdata['app_session']['username'] . "', '" . $bulanx . "', '" . $thn . "', '" . $session['kdcab'] . "')");
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil disimpan. Terimakasih ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

	public function download_excel($category = null, $bulan = null, $tahun = null)
	{
		//membuat objek PHPExcel
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		// $this->load->library("PHPExcel/Writer/Excel2007");
		$objPHPExcel	= new PHPExcel();

		$style_header = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$style_header2 = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleArray = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray3 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray4 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray1 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);
		$styleArray2 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$Arr_Bulan	= array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet 		= $objPHPExcel->getActiveSheet();

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(12);
		$sheet->setCellValue('A' . $Row, 'DATA ASSETS DEPRESIASI PER ' . $bulan . '-' . $tahun);
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($style_header2);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setWidth(10);

		$sheet->setCellValue('B' . $NewRow, 'CODE');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setWidth(20);

		$sheet->setCellValue('C' . $NewRow, 'ASSET NAME');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D' . $NewRow, 'TGL PEROLEHAN');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'CATEGORY');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setWidth(10);

		$sheet->setCellValue('F' . $NewRow, 'KELOMPOK PENYUSUTAN');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setWidth(10);

		$sheet->setCellValue('G' . $NewRow, 'COSTCENTER');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
		$sheet->getColumnDimension('G')->setWidth(10);

		$sheet->setCellValue('H' . $NewRow, 'DEPRESIASI (YEAR)');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
		$sheet->getColumnDimension('H')->setWidth(10);

		$sheet->setCellValue('I' . $NewRow, 'NILAI PEROLEHAN');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
		$sheet->getColumnDimension('I')->setWidth(10);

		$sheet->setCellValue('J' . $NewRow, 'DEPRESIASI');
		$sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);
		$sheet->getColumnDimension('J')->setWidth(10);

		$sheet->setCellValue('K' . $NewRow, 'AKUMULASI DEPRESIASI');
		$sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);
		$sheet->getColumnDimension('K')->setWidth(10);

		$sheet->setCellValue('L' . $NewRow, 'ASSET VALUE');
		$sheet->getStyle('L' . $NewRow . ':L' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('L' . $NewRow . ':L' . $NextRow);
		$sheet->getColumnDimension('L')->setWidth(10);

		$where_kategori = "";
		if ($category != '0') {
			$where_kategori = " AND a.category = '" . $category . "' ";
		}

		$WHERE_PERIODE = "AND (b.flag='N' OR b.flag='X')";
		$WHERE_PERIODE2 = "AND (b.flag='Y')";
		if ($bulan != '0' and $tahun != '0') {
			//			$WHERE_PERIODE = "AND CONCAT(b.tahun,'-',b.bulan,'-01') > '".$tahun."-".$bulan."-01' OR a.penyusutan = 'N'";
			$WHERE_PERIODE2 = "AND CONCAT(b.tahun,'-',b.bulan,'-01') <= '" . $tahun . "-" . $bulan . "-01'";
		}

		$SQL = "
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
			" . $where_kategori . "
		GROUP BY a.kd_asset
		ORDER BY a.id
		";

		$result = $this->db->query($SQL)->result_array();
		$GET_DEPRESIASI = get_valueDepresiasi();
		if ($result) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($result as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$UNIQ = $row_Cek['kd_asset'] . '-' . $bulan . $tahun;

				$awal_col++;
				$kd_asset	= strtoupper($row_Cek['kd_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $kd_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_asset	= strtoupper($row_Cek['nm_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$tgl_perolehan	= $row_Cek['tgl_perolehan'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $tgl_perolehan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_category	= strtoupper($row_Cek['nm_category']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_category);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$KEL_PENYUSUTAN = (!empty($row_Cek['no_perkiraan'])) ? strtoupper($row_Cek['no_perkiraan'] . ' | ' . $row_Cek['ket_coa']) : '';
				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $KEL_PENYUSUTAN);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$cost_center		= strtoupper($row_Cek['cost_center']);
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $cost_center);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$depresiasi		= $row_Cek['depresiasi'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $depresiasi);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$SISA_NILAI 	= ($row_Cek['penyusutan'] == 'N') ? $row_Cek['nilai_asset'] : $row_Cek['sisa_nilai'];
				// $DEPRESIASI 	= ($SISA_NILAI > 0 AND $SISA_NILAI != $row_Cek['nilai_asset'])?$row_Cek['value'] : 0;
				if (intval($bulan) >= 4  and intval($tahun) >= 2022 and $row_Cek['kd_asset'] == 'ORI-22000000-000122') {
					//					$SISA_NILAI = $SISA_NILAI + 29887;
				}

				$TGL_PEROLEHAN 	= date('Y-m-01', strtotime($row_Cek['tgl_perolehan']));
				$DEPRESIASI_BLN = $row_Cek['depresiasi'] * 12;
				$TGL_LAST_DEPT	= date('Ym', strtotime('+' . $DEPRESIASI_BLN . ' month', strtotime($TGL_PEROLEHAN)));
				$TGL_NOW 		= $tahun . $bulan;
				$TGL_NOW_DATE 	= date('Y-m-01', strtotime($tahun . '-' . $bulan . '-01'));
				$DEPRESIASI = 0;
				if ($TGL_LAST_DEPT > $TGL_NOW and $TGL_PEROLEHAN <= $TGL_NOW_DATE) {
					// $DEPRESIASI = $row_Cek['value'];
					$DEPRESIASI = (!empty($GET_DEPRESIASI[$UNIQ])) ? $GET_DEPRESIASI[$UNIQ] : 0;
				}

				$awal_col++;
				$nilai_asset		= $row_Cek['nilai_asset'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nilai_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $DEPRESIASI);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $row_Cek['total_depresiasi']);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $SISA_NILAI);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);
			}
		}


		$sheet->setTitle('ASSETS');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="data-assets.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function download_excel_all($category = null)
	{
		//membuat objek PHPExcel
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		// $this->load->library("PHPExcel/Writer/Excel2007");
		$objPHPExcel	= new PHPExcel();

		$style_header = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$style_header2 = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleArray = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray3 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray4 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray1 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);
		$styleArray2 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$Arr_Bulan	= array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet 		= $objPHPExcel->getActiveSheet();

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(12);
		$sheet->setCellValue('A' . $Row, 'DATA ASSETS');
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($style_header2);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setWidth(10);

		$sheet->setCellValue('B' . $NewRow, 'CODE');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setWidth(20);

		$sheet->setCellValue('C' . $NewRow, 'ASSET NAME');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D' . $NewRow, 'TGL PEROLEHAN');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'CATEGORY');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setWidth(10);

		$sheet->setCellValue('F' . $NewRow, 'KELOMPOK PENYUSUTAN');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setWidth(10);

		$sheet->setCellValue('G' . $NewRow, 'COSTCENTER');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
		$sheet->getColumnDimension('G')->setWidth(10);

		$sheet->setCellValue('H' . $NewRow, 'DEPRESIASI (YEAR)');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
		$sheet->getColumnDimension('H')->setWidth(10);

		$sheet->setCellValue('I' . $NewRow, 'NILAI PEROLEHAN');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
		$sheet->getColumnDimension('I')->setWidth(10);

		$sheet->setCellValue('J' . $NewRow, 'DEPRESIASI');
		$sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);
		$sheet->getColumnDimension('J')->setWidth(10);

		$sheet->setCellValue('K' . $NewRow, 'AKUMULASI DEPRESIASI');
		$sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);
		$sheet->getColumnDimension('K')->setWidth(10);

		$sheet->setCellValue('L' . $NewRow, 'ASSET VALUE');
		$sheet->getStyle('L' . $NewRow . ':L' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('L' . $NewRow . ':L' . $NextRow);
		$sheet->getColumnDimension('L')->setWidth(10);

		$where_kategori = "";
		if ($category != '0') {
			$where_kategori = " AND a.category = '" . $category . "' ";
		}

		$SQL = "
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
			" . $where_kategori . "
		";

		$result = $this->db->query($SQL)->result_array();

		$tahun = date('Y');
		$bulan = date('m');
		$GET_DEPRESIASI = get_valueDepresiasi();
		if ($result) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($result as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$UNIQ = $row_Cek['kd_asset'] . '-' . $bulan . $tahun;

				$awal_col++;
				$kd_asset	= strtoupper($row_Cek['kd_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $kd_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_asset	= strtoupper($row_Cek['nm_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$tgl_perolehan	= $row_Cek['tgl_perolehan'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $tgl_perolehan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_category	= strtoupper($row_Cek['nm_category']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_category);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$KEL_PENYUSUTAN = (!empty($row_Cek['no_perkiraan'])) ? strtoupper($row_Cek['no_perkiraan'] . ' | ' . $row_Cek['ket_coa']) : '';
				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $KEL_PENYUSUTAN);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$cost_center		= strtoupper($row_Cek['cost_center']);
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $cost_center);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$depresiasi		= $row_Cek['depresiasi'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $depresiasi);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$SISA_NILAI 	= ($row_Cek['penyusutan'] == 'N') ? $row_Cek['nilai_asset'] : $row_Cek['sisa_nilai'];
				// $DEPRESIASI 	= ($SISA_NILAI > 0 AND $SISA_NILAI != $row_Cek['nilai_asset'])?$row_Cek['value'] : 0;

				$TGL_PEROLEHAN 	= date('Y-m-01', strtotime($row_Cek['tgl_perolehan']));
				$DEPRESIASI_BLN = $row_Cek['depresiasi'] * 12;
				$TGL_LAST_DEPT	= date('Ym', strtotime('+' . $DEPRESIASI_BLN . ' month', strtotime($TGL_PEROLEHAN)));
				$TGL_NOW 		= $tahun . $bulan;
				$TGL_NOW_DATE 	= date('Y-m-01', strtotime($tahun . '-' . $bulan . '-01'));
				$DEPRESIASI = 0;
				if ($TGL_LAST_DEPT > $TGL_NOW and $TGL_PEROLEHAN <= $TGL_NOW_DATE) {
					// $DEPRESIASI = $row_Cek['value'];
					$DEPRESIASI = (!empty($GET_DEPRESIASI[$UNIQ])) ? $GET_DEPRESIASI[$UNIQ] : 0;
				}

				$awal_col++;
				$nilai_asset		= $row_Cek['nilai_asset'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nilai_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $DEPRESIASI);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $row_Cek['nilai_asset'] - $SISA_NILAI);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $SISA_NILAI);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);
			}
		}


		$sheet->setTitle('ASSETS');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="data-assets-depresiasi-all.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function download_excel_all_default($category = null)
	{
		//membuat objek PHPExcel
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		// $this->load->library("PHPExcel/Writer/Excel2007");
		$objPHPExcel	= new PHPExcel();

		$style_header = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$style_header2 = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleArray = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray3 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray4 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray1 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);
		$styleArray2 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$Arr_Bulan	= array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet 		= $objPHPExcel->getActiveSheet();

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(9);
		$sheet->setCellValue('A' . $Row, 'DATA ASSETS');
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($style_header2);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setWidth(10);

		$sheet->setCellValue('B' . $NewRow, 'CODE');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setWidth(20);

		$sheet->setCellValue('C' . $NewRow, 'ASSET NAME');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D' . $NewRow, 'TGL PEROLEHAN');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'CATEGORY');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setWidth(10);

		$sheet->setCellValue('F' . $NewRow, 'KELOMPOK PENYUSUTAN');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setWidth(10);

		$sheet->setCellValue('G' . $NewRow, 'COSTCENTER');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
		$sheet->getColumnDimension('G')->setWidth(10);

		$sheet->setCellValue('H' . $NewRow, 'DEPRESIASI (YEAR)');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
		$sheet->getColumnDimension('H')->setWidth(10);

		$sheet->setCellValue('I' . $NewRow, 'NILAI PEROLEHAN');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
		$sheet->getColumnDimension('I')->setWidth(10);

		$where_kategori = "";
		if ($category != '0') {
			$where_kategori = " AND a.category = '" . $category . "' ";
		}

		$SQL = "
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
			ORDER BY a.id
		";

		$result = $this->db->query($SQL)->result_array();

		if ($result) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($result as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);



				$awal_col++;
				$kd_asset	= strtoupper($row_Cek['kd_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $kd_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_asset	= strtoupper($row_Cek['nm_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$tgl_perolehan	= $row_Cek['tgl_perolehan'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $tgl_perolehan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_category	= strtoupper($row_Cek['nm_category']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_category);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$KEL_PENYUSUTAN = (!empty($row_Cek['no_perkiraan'])) ? strtoupper($row_Cek['no_perkiraan'] . ' | ' . $row_Cek['ket_coa']) : '';
				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $KEL_PENYUSUTAN);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$cost_center		= strtoupper($row_Cek['cost_center']);
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $cost_center);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$depresiasi		= $row_Cek['depresiasi'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $depresiasi);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$nilai_asset		= $row_Cek['nilai_asset'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nilai_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);
			}
		}


		$sheet->setTitle('ASSETS');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="data-assets-all.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function saved_jurnal_erp()
	{
		$data_session	= $this->session->userdata;
		$username = $data_session['ORI_User']['username'];
		$datetime = date('Y-m-d H:i:s');

		$get_jurnal = $this->db->get_where('asset_jurnal_temp', array('created_by' => $username, 'kredit' => 0))->result_array();
		$ArrJurnal = [];
		foreach ($get_jurnal as $key => $value) {
			$ArrJurnal[$key]['category'] 		= 'assets';
			$ArrJurnal[$key]['tanggal'] 		= $value['tanggal'];
			$ArrJurnal[$key]['id_detail'] 		= $value['id_category'];
			$ArrJurnal[$key]['product'] 		= $value['category'];
			$ArrJurnal[$key]['id_material'] 	= $value['no_perkiraan'];
			$ArrJurnal[$key]['nm_material'] 	= $value['keterangan'];
			$ArrJurnal[$key]['total_nilai'] 	= $value['debet'];
			$ArrJurnal[$key]['created_by'] 		= $username;
			$ArrJurnal[$key]['created_date'] 	= $datetime;
		}


		// echo "<pre>";
		// print_r($ArrDebit);
		// print_r($ArrKredit);
		// print_r($ArrJurnal);
		// exit;
		$ArrDelete = [
			'tanggal' => $value['tanggal'],
			'category' => 'assets'
		];

		$this->db->trans_start();
		$this->db->delete('jurnal', $ArrDelete);
		$this->db->insert_batch('jurnal', $ArrJurnal);
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
				'pesan'		=> 'Asset berhasil disimpan. Terimakasih ...',
				'status'	=> 1
			);
			history('Insert jurnal erp ' . $value['tanggal']);
		}

		echo json_encode($Arr_Data);
	}


	//======================================================================================================================
	//===================================================PR ASSET============================================================
	//======================================================================================================================
	public function depreciation()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		// $SQL_LAST = $this->db->select('MAX(MONTH(bulan)) AS bulan, MAX(YEAR(tahun)) AS tahun')->get_where('asset_generatex',array('flag'=>'N'))->result();

		$data = array(
			'title'			=> 'Indeks Of Depreciation Assets',
			'action'		=> 'asset',
			'akses_menu'	=> $Arr_Akses,
			'bulan_'		=> date('m'),
			'kategori' 		=> $this->Pr_asset_model->getList('asset_category')
		);
		history("View index asset depreciation");
		$this->load->view('Asset/depreciation', $data);
	}

	public function data_side_depreciation()
	{
		$this->Pr_asset_model->data_side_depreciation();
	}

	//======================================================================================================================
	//===================================================ASSET COA============================================================
	//======================================================================================================================

	public function asset_coa()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data = array(
			'title'			=> 'Indeks Of Asset COA',
			'action'		=> 'category',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses
		);
		history('View Data Asset COA');
		$this->load->view('Asset/asset_coa', $data);
	}

	public function data_side_asset_coa()
	{
		$this->Pr_asset_model->get_json_asset_coa();
	}

	public function add_asset_coa()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$dateTime		= date('Y-m-d H:i:s');

			//header
			$id 		    = $data['id'];
			$keterangan		= strtoupper($data['keterangan']);
			$coa			= $data['coa'];
			$coa_kredit		= $data['coa_kredit'];
			$status			= $data['status'];

			if (empty($id)) {
				$ArrHeader = array(
					'keterangan' => $keterangan,
					'coa' 		=> $coa,
					'coa_kredit' => $coa_kredit,
					'status'	=> $status,
				);
				$TandaI = "Insert";
			}

			if (!empty($id)) {
				$ArrHeader = array(
					'keterangan'   => $keterangan,
					'coa' 		=> $coa,
					'coa_kredit' => $coa_kredit,
					'status'	=> $status,
				);
				$TandaI = "Update";
			}

			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('asset_coa', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('asset_coa', $ArrHeader);
			}
			$this->db->trans_complete();


			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> $TandaI . ' data failed. Please try again later ...',
					'status'	=> 2
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> $TandaI . ' data success. Thanks ...',
					'status'	=> 1
				);
				history($TandaI . ' Asset COA ' . $id . ' / ' . $keterangan);
			}

			echo json_encode($Arr_Kembali);
		} else {
			$this->load->model('All_model');
			$controller			= ucfirst(strtolower($this->uri->segment(1)));
			$Arr_Akses			= getAcccesmenu($controller);
			if ($Arr_Akses['create'] != '1') {
				$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
				redirect(site_url('users'));
			}

			$id = $this->uri->segment(3);
			$query = "SELECT * FROM asset_coa WHERE id ='" . $id . "' LIMIT 1 ";
			$result = $this->db->query($query)->result();
			$data_coa = $this->All_model->GetCoaCombo();
			$data = array(
				'title'		=> 'Add Asset COA',
				'action'	=> 'add',
				'data'      => $result,
				'coalist'      => $data_coa
			);
			$this->load->view('Asset/add_asset_coa', $data);
		}
	}

	public function hapus_asset_coa()
	{
		$id = $this->uri->segment(3);
		$data_session	= $this->session->userdata;

		$ArrPlant		= array(
			'status' 		=> 'N',
		);


		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('asset_coa', $ArrPlant);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data success. Thanks ...',
				'status'	=> 1
			);
			history('Delete Asset COA : ' . $id);
		}
		echo json_encode($Arr_Data);
	}

	public function edit_asset() {
		$post = $this->input->post();

		$this->db->trans_start();

		$data_update = [
			'coa' => $post['coa'],
			'coa_akum' => $post['coa_akum'],
			'nama_asset' => $post['nama_asset'],
			'id_dept' => $post['id_dept'],
			'id_costcenter' => $post['id_costcenter'],
			'tahun' => $post['tahun'],
			'bulan' => $post['bulan'],
			'budget' => str_replace(',', '', $post['budget']),
			'budget_pr' => str_replace(',', '', $post['budget']),
			'budget_po' => str_replace(',', '', $post['budget']),
			'qty' => str_replace(',', '', $post['qty']),
			'keterangan' => $post['keterangan']
		];

		$update_asset = $this->db->update('asset_planning', $data_update, ['no_pr' => $post['no_pr']]);
		if(!$update_asset) {
			print_r($this->db->error($update_asset));
			exit;
		}

		$data_update2 = [
			'nm_barang' => $post['nama_asset'],
			'qty' => str_replace(',', '', $post['qty']),
			'nilai_pr' => str_replace(',', '', $post['budget'])
		];
		$update_pr_detail = $this->db->update('tran_pr_detail', $data_update2, ['no_pr' => $post['no_pr']]);
		if(!$update_pr_detail) {
			print_r($this->db->error($update_pr_detail));
			exit;
		}

		if($this->db->trans_status() === false) {
			$this->db->trans_rollback();

			$valid = 0;
		}else{
			$this->db->trans_commit();

			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}
}
