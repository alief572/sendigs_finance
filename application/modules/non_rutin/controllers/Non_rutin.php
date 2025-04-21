<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Non_rutin extends Admin_Controller
{

	protected $viewPermission 	= 'PR_Departemen.View';
	protected $addPermission  	= 'PR_Departemen.Add';
	protected $managePermission = 'PR_Departemen.Manage';
	protected $deletePermission = 'PR_Departemen.Delete';

	protected $viewPermission1 	= 'Approval_PR_Depart_Head.View';
	protected $addPermission1  	= 'Approval_PR_Depart_Head.Add';
	protected $managePermission1 = 'Approval_PR_Depart_Head.Manage';
	protected $deletePermission1 = 'Approval_PR_Depart_Head.Delete';

	protected $viewPermission2 	= 'Approval_PR_Depart_Cost_Control.View';
	protected $addPermission2  	= 'Approval_PR_Depart_Cost_Control.Add';
	protected $managePermission2 = 'Approval_PR_Depart_Cost_Control.Manage';
	protected $deletePermission2 = 'Approval_PR_Depart_Cost_Control.Delete';

	protected $viewPermission3 	= 'Approval_PR_Depart_Management.View';
	protected $addPermission3  	= 'Approval_PR_Depart_Management.Add';
	protected $managePermission3 = 'Approval_PR_Depart_Management.Manage';
	protected $deletePermission3 = 'Approval_PR_Depart_Management.Delete';


	public function __construct()
	{
		parent::__construct();
		$this->load->model('master_model');
		$this->load->model('non_rutin_model');

		// $this->load->library(array('Mpdf'));
	}

	//===============================================================================================================================
	//=============================================RUTIN=============================================================================
	//===============================================================================================================================

	public function index()
	{


		$data_Group			= $this->db->get('groups')->result();
		$tanda				= $this->uri->segment(2);
		$get_department = $this->db->get_where('ms_department', ['deleted_by' => null])->result();

		$get_list_data = $this->db->select('a.*, b.nama')
			->from('rutin_non_planning_detail z')
			->join('rutin_non_planning_header a', 'z.no_pengajuan = a.no_pengajuan', 'left')
			->join('ms_department b', 'b.id = a.id_dept', 'left')
			->where('a.status_id', 1)
			->where('a.close_pr', null)
			->group_by('z.no_pengajuan')
			->get()
			->result();

		$data = array(
			'title'			=> 'PR Departemen',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'tanda'			=> $tanda,
			'list_department' => $get_department,
			'result' => $get_list_data
		);
		history('View data pengajuan pr non-rutin (departemen)');
		$this->template->set($data);
		$this->template->render('index');
	}

	public function server_side_non_rutin()
	{
		$this->non_rutin_model->get_data_json_non_rutin();
	}
	public function server_side_non_rutin_approval_head()
	{
		$this->non_rutin_model->get_data_json_non_rutin_approval_head();
	}
	public function server_side_non_rutin_approval_cost_control()
	{
		$this->non_rutin_model->get_data_json_non_rutin_approval_cost_control();
	}
	public function server_side_non_rutin_approval_management()
	{
		$this->non_rutin_model->get_data_json_non_rutin_approval_management();
	}

	public function add()
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
			$tingkat_approval = $data['tingkat_approval'];
			$coa = $data['coa'];
			$code_planx  	= $data['id'];
			if (empty($code_planx)) :
				$code_planx = '';
			endif;
			$no_so        	= (!empty($data['no_so'])) ? $data['no_so'] : NULL;
			$project_name   = (!empty($data['project_name'])) ? $data['project_name'] : NULL;
			$id_dept 		= (!empty($data['id_dept'])) ? $data['id_dept'] : NULL;
			// $id_costcenter 	= (!empty($data['id_costcenter'])) ? $data['id_costcenter'] : NULL;
			// $coa 			= (!empty($data['coa'])) ? $data['coa'] : NULL;
			// $budget 		= str_replace(',', '', $data['budget']);
			// $sisa_budget 	= str_replace(',', '', $data['sisa_budget']);

			$detail 		= $data['detail'];

			//approve
			$sts_app        = (!empty($data['sts_app'])) ? $data['sts_app'] : '';
			$reason        	= (!empty($data['reason'])) ? $data['reason'] : '';

			$ym = date('ym');


			// if ($tingkat_approval == '3') :
			if (empty($code_plan)) {
				$srcMtr			= "SELECT MAX(no_pengajuan) as maxP FROM rutin_non_planning_header WHERE no_pengajuan LIKE 'PLN" . $ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 7, 3);
				$urutan2++;
				$urut2			= sprintf('%03s', $urutan2);
				$code_plan		= "PLN" . $ym . $urut2;
			}
			// endif;


			$SUM_QTY = 0;
			$SUM_HARGA = 0;
			if (empty($approve)) {
				$ArrDetail = array();
				if (!empty($detail)) {
					foreach ($detail as $val => $valx) {
						$qty 	= str_replace(',', '', $valx['qty']);
						$harga 	= str_replace(',', '', $valx['harga']);

						$SUM_QTY 	+= $qty;
						$SUM_HARGA 	+= $harga * $qty;

						$ArrDetail[$val]['no_pengajuan'] 	= $code_plan;
						$ArrDetail[$val]['nm_barang'] 		= strtolower($valx['nm_barang']);
						$ArrDetail[$val]['spec'] 			= strtolower($valx['spec']);
						$ArrDetail[$val]['satuan'] 			= $valx['satuan'];
						$ArrDetail[$val]['qty'] 			= $qty;
						$ArrDetail[$val]['harga'] 			= $harga;
						$ArrDetail[$val]['keterangan'] 		= strtolower($valx['keterangan']);
						$ArrDetail[$val]['tanggal'] 		= $valx['tanggal'];
						$ArrDetail[$val]['created_by'] 		= $this->auth->user_id();
						$ArrDetail[$val]['created_date'] 	= $dateTime;
					}
				}
			}

			//UPLOAD DOCUMENT
			$file_name = NULL;
			if (!empty($_FILES["upload_spk"]["name"])) {

				$config['upload_path'] = './assets/pr/';
				$config['allowed_types'] = '*';
				$config['remove_spaces'] = TRUE;
				$config['encrypt_name'] = TRUE;
				$file_name = '';
				if (!empty($_FILES['upload_spk']['name'])) {
					$_FILES['file']['name'] = $_FILES['upload_spk']['name'];
					$_FILES['file']['type'] = $_FILES['upload_spk']['type'];
					$_FILES['file']['tmp_name'] = $_FILES['upload_spk']['tmp_name'];
					$_FILES['file']['error'] = $_FILES['upload_spk']['error'];
					$_FILES['file']['size'] = $_FILES['upload_spk']['size'];
					$this->load->library('upload', $config);
					$this->upload->initialize($config);
					if ($this->upload->do_upload('file')) {
						$uploadData = $this->upload->data();
						$file_name = $uploadData['file_name'];
					} else {
						print_r($this->upload->display_errors());
						exit;
					}
				}
				// $target_dir     = $_SERVER['DOCUMENT_ROOT'] . "origa_dev/uploads/PR/";
				// $target_dir_u   = $_SERVER['DOCUMENT_ROOT'] . "origa_dev/uploads/PR/";
				// $name_file      = 'lampiran_pr_dept_' . date('Ymdhis');
				// $target_file    = $target_dir . basename($_FILES["upload_spk"]["name"]);
				// $name_file_ori  = basename($_FILES["upload_spk"]["name"]);
				// $imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
				// $nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;
				// $file_name    	= $name_file . "." . $imageFileType;

				// if (!empty($_FILES["upload_spk"]["tmp_name"])) {
				// 	move_uploaded_file($_FILES["upload_spk"]["tmp_name"], $nama_upload);
				// }
			}

			//header edit
			// $ArrHeader		= array(
			// 	'id_dept' 		=> $id_dept,
			// 	'id_costcenter' => $id_costcenter,
			// 	'coa' 			=> $coa,
			// 	'budget' 		=> $budget,
			// 	'no_so' 		=> $no_so,
			// 	'project_name'	=> $project_name,
			// 	'sisa_budget' 	=> $sisa_budget,
			// 	'qty' 			=> $SUM_QTY,
			// 	'harga' 		=> $SUM_HARGA,
			// 	'document' 		=> $file_name,
			// 	'updated_by'	=> $this->auth->user_id(),
			// 	'updated_date'	=> $dateTime
			// );



			//header approve
			if (!empty($approve)) {
				$ArrDetail = array();
				$ArrDetailPR = array();

				$no_pr = '';
				$no_pr_group = '';
				if ($tingkat_approval == '3') :
					$Ym = date('ym');
					$qIPP			= "SELECT MAX(no_pr) as maxP FROM rutin_non_planning_header WHERE no_pr LIKE 'PRN" . $Ym . "%' ";
					$numrowIPP		= $this->db->query($qIPP)->num_rows();
					$resultIPP		= $this->db->query($qIPP)->result_array();
					$angkaUrut2		= $resultIPP[0]['maxP'];
					$urutan2		= (int)substr($angkaUrut2, 7, 4);
					$urutan2++;
					$urut2			= sprintf('%04s', $urutan2);
					$no_pr			= "PRN" . $Ym . $urut2;

				// $Ym = date('ym');
				// $qIPPX			= "SELECT MAX(no_pr_group) as maxP FROM rutin_non_planning_header WHERE no_pr_group LIKE 'PR" . $Ym . "%' ";
				// $numrowIPPX		= $this->db->query($qIPPX)->num_rows();
				// $resultIPPX		= $this->db->query($qIPPX)->result_array();
				// $angkaUrut2X	= $resultIPPX[0]['maxP'];
				// $urutan2X		= (int)substr($angkaUrut2X, 6, 4);
				// $urutan2X++;
				// $urut2X			= sprintf('%04s', $urutan2X);
				// $no_pr_group	= "PR" . $Ym . $urut2X;
				endif;

				$ArrHeaderPR = array(
					'no_pr' => $no_pr,
					'no_pr_group' => $no_pr_group,
					'category' => 'non rutin',
					'tgl_pr'	=> date('Y-m-d'),
					'created_by' => $this->auth->user_id(),
					'created_date' => date('Y-m-d H:i:s')
				);

				$SUM_QTY = 0;
				$SUM_HARGA = 0;
				if (!empty($detail)) {
					foreach ($detail as $val => $valx) {
						$qty 	= str_replace(',', '', $valx['qty']);
						$harga 	= str_replace(',', '', $valx['harga']);

						$SUM_QTY 	+= $qty;
						$SUM_HARGA 	+= $harga * $qty;

						$ArrDetail[$val]['id'] 			= $valx['id'];
						$ArrDetail[$val]['no_pr'] 		= $no_pr;
						$ArrDetail[$val]['qty_rev'] 	= $qty;
						$ArrDetail[$val]['harga_rev'] 	= $harga;
						$ArrDetail[$val]['sts_app'] 	= $sts_app;
						$ArrDetail[$val]['sts_app_by'] 	= $this->auth->user_id();
						$ArrDetail[$val]['sts_app_date'] = $dateTime;


						$ArrDetailPR[$val]['no_pr'] 		= $no_pr;
						$ArrDetailPR[$val]['no_pr_group'] 	= $no_pr_group;
						$ArrDetailPR[$val]['category'] 		= 'non rutin';
						$ArrDetailPR[$val]['tgl_pr'] 		= date('Y-m-d');
						$ArrDetailPR[$val]['id_barang'] 	= $valx['id'];
						$ArrDetailPR[$val]['nm_barang'] 	= strtolower($valx['nm_barang'] . ' - ' . $valx['spec']);
						$ArrDetailPR[$val]['qty'] 			= $qty;
						$ArrDetailPR[$val]['nilai_pr'] 		= $harga;
						$ArrDetailPR[$val]['tgl_dibutuhkan'] = $valx['tanggal'];
						$ArrDetailPR[$val]['satuan']		= $valx['satuan'];
						$ArrDetailPR[$val]['app_status'] 	= 'Y';
						$ArrDetailPR[$val]['app_reason']	= strtolower($valx['keterangan']);
						$ArrDetailPR[$val]['app_by'] = $this->auth->user_id();
						$ArrDetailPR[$val]['app_date'] = $dateTime;
						$ArrDetailPR[$val]['created_by'] 	= $this->auth->user_id();
						$ArrDetailPR[$val]['created_date'] 	= $dateTime;
					}
				}

				if ($tingkat_approval !== '') {
					if ($tingkat_approval == '1' || $tingkat_approval == '2') :
						if ($sts_app == 'Y') :
							if ($tingkat_approval == '2') :
								$ArrHeader		= array(
									'qty_rev' 		=> $SUM_QTY,
									'harga_rev' 	=> $SUM_HARGA,
									'app_2' => '1',
									'sts_reject2' => null,
									'reason' 		=> $reason,
									'app_2_by'	=> $this->auth->user_id(),
									'app_2_date'	=> $dateTime,
									'keterangan_1' => $data['keterangan_1'],
									'keterangan_2' => $data['keterangan_2'],
									'keterangan_3' => $data['keterangan_3'],
									'app_post' => '3'
								);
							else :
								$ArrHeader		= array(
									'qty_rev' 		=> $SUM_QTY,
									'harga_rev' 	=> $SUM_HARGA,
									'app_1' => '1',
									'sts_reject1' => null,
									'reason' 		=> $reason,
									'app_1_by'	=> $this->auth->user_id(),
									'app_1_date'	=> $dateTime,
									'keterangan_1' => $data['keterangan_1'],
									'keterangan_2' => $data['keterangan_2'],
									'keterangan_3' => $data['keterangan_3'],
									'app_post' => '2'
								);
							endif;
						else :
							$ArrHeader		= array(
								'qty_rev' 		=> $SUM_QTY,
								'harga_rev' 	=> $SUM_HARGA,
								'sts_reject' . $tingkat_approval => '1',
								'reject_reason' . $tingkat_approval => $reason,
								'sts_reject' . $tingkat_approval . '_by' => $this->auth->user_id(),
								'sts_reject' . $tingkat_approval . '_date' => date('Y-m-d H:i:s'),
								'no_pr' => null,
								'sts_app' => 0,
								'keterangan_1' => $data['keterangan_1'],
								'keterangan_2' => $data['keterangan_2'],
								'keterangan_3' => $data['keterangan_3'],
								'app_post' => null,
								'rejected' => 1
							);
						endif;
					else :
						if ($sts_app == 'Y') :
							$ArrHeader		= array(
								'qty_rev' 		=> $SUM_QTY,
								'harga_rev' 	=> $SUM_HARGA,
								'no_pr' 		=> $no_pr,
								'sts_app' 		=> $sts_app,
								'reason' 		=> $reason,
								'app_3' 		=> 1,
								'sts_reject3' 		=> null,
								'app_3_by'	=> $this->auth->user_id(),
								'app_3_date'	=> $dateTime,
								'keterangan_1' => $data['keterangan_1'],
								'keterangan_2' => $data['keterangan_2'],
								'keterangan_3' => $data['keterangan_3'],
								'app_post' => 4
							);
						else :
							$ArrHeader		= array(
								'qty_rev' 		=> $SUM_QTY,
								'harga_rev' 	=> $SUM_HARGA,
								'sts_reject' . $tingkat_approval => '1',
								'reject_reason' . $tingkat_approval => $reason,
								'sts_reject3_by' => $this->auth->user_id(),
								'sts_reject3_date' => date('Y-m-d H:i:s'),
								'no_pr' => null,
								'sts_app' => 0,
								'keterangan_1' => $data['keterangan_1'],
								'keterangan_2' => $data['keterangan_2'],
								'keterangan_3' => $data['keterangan_3'],
								'reject_reason' . $tingkat_approval => $reason,
								'app_post' => null,
								'rejected' => 1
							);
						endif;
					endif;
				}


				// print_r($ArrHeaderPR);
				// print_r($ArrDetailPR);
			} else {
				if (empty($code_planx)) {
					$ArrHeader		= array(
						'id_dept' 		=> $id_dept,
						'no_pengajuan' 	=> $code_plan,
						'project_name'	=> $project_name,
						'qty' 			=> $SUM_QTY,
						'harga' 		=> $SUM_HARGA,
						'document' 		=> $file_name,
						'coa' 		=> $coa,
						'tingkat_pr' => $data['tingkat_pr'],
						'created_by'	=> $this->auth->user_id(),
						'created_date'	=> $dateTime
					);
				} else {
					$ArrHeader		= [
						'id_dept' 		=> $id_dept,
						'project_name'	=> $project_name,
						'qty' 			=> $SUM_QTY,
						'harga' 		=> $SUM_HARGA,
						'document' 		=> $file_name,
						'coa' 		=> $coa,
						'app_1' => null,
						'app_2' => null,
						'app_3' => null,
						'sts_reject1' => null,
						'sts_reject2' => null,
						'sts_reject3' => null,
						'app_1_by' => null,
						'app_1_date' => null,
						'app_2_by' => null,
						'app_2_date' => null,
						'app_3_by' => null,
						'app_3_date' => null,
						'sts_reject1_by' => null,
						'sts_reject1_date' => null,
						'sts_reject2_by' => null,
						'sts_reject2_date' => null,
						'sts_reject3_by' => null,
						'sts_reject3_date' => null,
						'rejected' => null,
						'app_post' => null,
						'keterangan_1' 		=> $data['keterangan_1'],
						'keterangan_2' 		=> $data['keterangan_2'],
						'keterangan_3' 		=> $data['keterangan_3'],
						'updated_by'	=> $this->auth->user_id(),
						'updated_date'	=> $dateTime,
						'tingkat_pr' => $data['tingkat_pr']
					];
				}
			}

			// print_r($ArrDetail);
			// print_r($ArrHeader);
			// exit;

			// if ($tingkat_approval == '3') :
			// 	$this->db->insert('rutin_non_planning_header', $ArrHeaderPR);
			// 	$this->db->insert_batch('tran_pr_detail', $ArrDetailPR);
			// endif;

			$link_approval = (!empty($approve)) ? 'approval' : '';



			$this->db->trans_start();
			if (empty($approve)) {
				if (!empty($code_planx)) {
					$this->db->where(array('no_pengajuan' => $code_plan));
					$this->db->update('rutin_non_planning_header', $ArrHeader);

					$this->db->where(array('no_pengajuan' => $code_plan));
					$this->db->delete('rutin_non_planning_detail');
					$this->db->insert_batch('rutin_non_planning_detail', $ArrDetail);
				} else {
					// print_r($ArrHeader);
					// exit;
					$this->db->insert('rutin_non_planning_header', $ArrHeader);

					$this->db->delete('rutin_non_planning_detail', ['no_pengajuan' => $code_plan]);
					$this->db->insert_batch('rutin_non_planning_detail', $ArrDetail);
				}
			}
			if (!empty($approve)) {
				// foreach ($ArrHeader as $column => $value) {
				// 	$this->db->set($column, $value);
				// }
				if ($code_planx == '') :
					$this->db->update('rutin_non_planning_header', $ArrHeader, ['no_pengajuan' => $code_plan]);
				else :
					$this->db->update('rutin_non_planning_header', $ArrHeader, ['no_pengajuan' => $code_planx]);
				endif;
				$this->db->update_batch('rutin_non_planning_detail', $ArrDetail, 'id');

				// if ($tingkat_approval == '3') :
				// 	$this->db->insert('rutin_non_planning_header', $ArrHeaderPR);
				// 	$this->db->insert_batch('tran_pr_detail', $ArrDetailPR);
				// endif;
			}
			$this->db->trans_complete();


			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data failed. Please try again later ...',
					'status'	=> 0,
					'approve'	=> $link_approval
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data success. Thanks ...',
					'status'	=> 1,
					'approve'	=> $link_approval
				);
				history($tanda . ' pengajuan budget non rutin ' . $code_plan);
			}
			echo json_encode($Arr_Kembali);
		} else {

			$data_Group	= $this->db->get('groups')->result();
			$id 		= $this->uri->segment(3);
			$approve 	= $this->uri->segment(4);
			$tingkat_approval = $this->uri->segment(5);
			$header 	= $this->db->query("SELECT * FROM rutin_non_planning_header WHERE no_pengajuan='" . $id . "' ")->result();
			$detail 	= $this->db->query("SELECT * FROM rutin_non_planning_detail WHERE no_pengajuan='" . $id . "' ")->result_array();
			$datacoa 	= $this->db->query("SELECT a.coa,b.nama FROM coa_category a join " . DBACC . ".coa_master b on a.coa=b.no_perkiraan WHERE a.tipe='NONRUTIN' order by a.coa")->result_array();
			$satuan		= $this->db->get_where('ms_satuan', array('deleted' => 'N'))->result_array();
			$tanda 		= (!empty($header)) ? 'Edit' : 'Add';
			if (!empty($approve)) {
				$tanda 		= ($approve == 'view') ? 'View' : 'Approve';
			}

			$title_tingkat = '';
			if ($tingkat_approval == '1') :
				$title_tingkat = 'Head Department';
			elseif ($tingkat_approval == '2') :
				$title_tingkat = 'Cost Control';
			elseif ($tingkat_approval == '3') :
				$title_tingkat = 'Management';
			endif;

			$get_list_coa = $this->db->get(DBACC . '.coa_master')->result_array();

			$get_departement = $this->db->get_where('ms_department', ['deleted_by' => null])->result();
			$data = array(
				'title'				=> $tanda . ' PR Departemen ' . $title_tingkat,
				'action'		=> strtolower($tanda),
				'header'		=> $header,
				'detail'		=> $detail,
				'datacoa'		=> $datacoa,
				'satuan'		=> $satuan,
				'approve'		=> $approve,
				'id'			=> $id,
				'list_departement' => $get_departement,
				'tingkat_approval'			=> $tingkat_approval,
				'list_coa' => $get_list_coa
			);

			$this->template->set($data);
			$this->template->render('add');
		}
	}

	public function get_add()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;
		$satuan		= $this->db->get_where('ms_satuan', array('deleted' => 'N', 'category' => 'packing'))->result_array();

		$d_Header = "";
		$d_Header .= "<tr class='header_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'><input type='text' name='detail[" . $id . "][nm_barang]' class='form-control input-md'></td>";
		$d_Header .= "<td align='left'><input type='text' name='detail[" . $id . "][spec]' class='form-control input-md'></td>";
		$d_Header .= "<td align='left'><input type='text' id='qty_" . $id . "' name='detail[" . $id . "][qty]' class='form-control input-md text-center autoNumeric2 sum_tot'></td>";
		$d_Header .= "<td align='left'><select name='detail[" . $id . "][satuan]' class='form-control chosen_select wajib' required>";
		$d_Header .= "<option value='0'>Pilih</option>";
		foreach ($satuan as $key => $value) {
			$d_Header .= "<option value='" . $value['id'] . "'>" . $value['code'] . "</option>";
		}
		$d_Header .= "	</select></td>";
		$d_Header .= "<td align='left'><input type='text' id='harga_" . $id . "' name='detail[" . $id . "][harga]' class='form-control input-md text-right maskM sum_tot' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
		$d_Header .= "<td align='left'><input type='text' id='total_harga_" . $id . "' name='detail[" . $id . "][total_harga]' class='form-control input-md text-right maskM jumlah_all' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
		$d_Header .= "<td align='left'><input type='text' name='detail[" . $id . "][tanggal]' class='form-control input-md text-center datepicker tgl_dibutuhkan' readonly></td>";
		$d_Header .= "<td align='left'><input type='text' name='detail[" . $id . "][keterangan]' class='form-control input-md'></td>";
		$d_Header .= "<td align='center'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";


		//add part
		$d_Header .= "<tr id='add_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Barang'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Barang</button></td>";
		$d_Header .= "<td align='center' colspan='7'></td>";
		$d_Header .= "</tr><script>$('.autoNumeric2').autoNumeric('init', {mDec: '2', aPad: false});</script>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function approval_head()
	{
		$this->auth->restrict($this->viewPermission1);
		$data_Group			= $this->db->get('groups')->result();
		$tanda				= $this->uri->segment(2);
		$data = array(
			'title'			=> 'Approval PR Departemen - Head Department',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'tanda'			=> $tanda
		);
		$this->template->set($data);
		$this->template->render('approval_head');
	}

	public function approval_cost_control()
	{
		$this->auth->restrict($this->viewPermission2);
		$data_Group			= $this->db->get('groups')->result();
		$tanda				= $this->uri->segment(2);
		$data = array(
			'title'			=> 'Approval PR Departemen - Cost Control',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'tanda'			=> $tanda
		);
		$this->template->set($data);
		$this->template->render('approval_cost_control');
	}

	public function approval_management()
	{
		$this->auth->restrict($this->viewPermission3);
		$data_Group			= $this->db->get('groups')->result();
		$tanda				= $this->uri->segment(2);
		$data = array(
			'title'			=> 'Approval PR Departemen - Management',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'tanda'			=> $tanda
		);
		$this->template->set($data);
		$this->template->render('approval_management');
	}

	public function print_pengajuan_non_rutin()
	{



		ob_clean();
		ob_start();

		$kode_trans     = $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $this->auth->user_id();

		$header 	= $this->db->query("SELECT a.*, b.nama as nm_dept, c.nm_lengkap as nm_user, CONCAT(d.no_perkiraan,' - ',d.nama) as nm_coa FROM rutin_non_planning_header a LEFT JOIN ms_department b ON b.id = a.id_dept LEFT JOIN users c ON c.id_user = a.created_by LEFT JOIN " . DBACC . ".coa_master d ON d.no_perkiraan = a.coa WHERE a.no_pengajuan='" . $kode_trans . "' ")->result();
		$detail 	= $this->db->query("SELECT * FROM rutin_non_planning_detail WHERE no_pengajuan='" . $kode_trans . "' ")->result_array();
		$datacoa 	= $this->db->query("SELECT * FROM coa_category WHERE tipe='NONRUTIN' ")->result_array();

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$data = array(
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'kode_trans' => $kode_trans,
			'header' => $header,
			'detail' => $detail,
			'datacoa' => $datacoa
		);

		$today = date('l, d F Y [H:i:s]');

		history('Print pengajuan non rutin ' . $kode_trans);
		$this->load->library(array('Mpdf'));
		$mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
		$mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('print_pengajuan_non_rutin', $data);
		$this->mpdf->AddPage('L', 'A4', 'en');
		$footer = 'Printed by : ' . ucfirst(strtolower($this->auth->user_name())) . ', ' . $today . ' / ' . $kode_trans . '';
		// $mpdf->SetWatermarkText('ORI Group');
		$mpdf->showWatermarkText = true;
		$mpdf->SetTitle($kode_trans . "/" . date('ymdhis'));
		$mpdf->AddPage();
		$mpdf->SetFooter($footer);
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output('tanda terima rutin ' . $kode_trans . '/' . date('ymdhis') . '.pdf', 'D');

		// $this->load->view('print_pengajuan_non_rutin', $data);
		// $html = ob_get_contents();

		// print_r($data['header']);
		// exit;

		// require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		// $html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		// $html2pdf->pdf->SetDisplayMode('fullpage');
		// $html2pdf->WriteHTML($html);
		// ob_end_clean();
		// $html2pdf->Output('PR Departement.pdf', 'I');
	}

	public function edit_detail()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$ArrUpdate = [
			'nm_barang' => $post['nm_barang'],
			'spec' => $post['spec'],
			'qty' => $post['qty'],
			'satuan' => $post['satuan'],
			'harga' => $post['harga'],
			'keterangan' => $post['keterangan']
		];
		$this->db->update('rutin_non_planning_detail', $ArrUpdate, ['id' => $post['id']]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}

	public function search_by_depart()
	{
		$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');

		$depart = $this->input->post('depart');

		if ($depart !== '') {
			$get_list_data = $this->db->select('a.*, b.nama')
				->from('rutin_non_planning_detail z')
				->join('rutin_non_planning_header a', 'z.no_pengajuan = a.no_pengajuan', 'left')
				->join('ms_department b', 'b.id = a.id_dept', 'left')
				->where('a.status_id', 1)
				->where('a.id_dept', $depart)
				->group_by('z.no_pengajuan')
				->get()
				->result();
		} else {
			$get_list_data = $this->db->select('a.*, b.nama')
				->from('rutin_non_planning_detail z')
				->join('rutin_non_planning_header a', 'z.no_pengajuan = a.no_pengajuan', 'left')
				->join('ms_department b', 'b.id = a.id_dept', 'left')
				->where('a.status_id', 1)
				->group_by('z.no_pengajuan')
				->get()
				->result();
		}

		$hasil = '
			<table class="table table-bordered table-striped" id="my-grid" width="100%">
					<thead>
						<tr class="bg-blue">
							<th class="text-center">#</th>
							<th class="text-center">No PR</th>
							<th class="text-center">Departemen</th>
							<th class="text-center no-sort">Nama Barang/Jasa</th>
							<th class="text-center no-sort">Spec / Requirement</th>
							<th class="text-center no-sort" width="7%">Qty</th>
							<th class="text-center no-sort">Dibutuhkan</th>
							<th class="text-center no-sort">Keterangan</th>
							<th class="text-center no-sort">Status</th>
							<th class="text-center no-sort" width="13%">Option</th>
						</tr>
					</thead>
					<tbody>
		';

		$no = 1;
		foreach ($get_list_data as $item) {
			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $no . '</td>';
			if (!empty($item->no_pr)) {
				$hasil .= '<td>' . $item->no_pr . '</td>';
			} else {
				$hasil .= '<td><span class="text-red">' . $item->no_pengajuan . '</span></td>';
			}
			$hasil .= '<td>' . strtoupper($item->nama) . '</td>';

			$list_barang    = $this->db->get_where('rutin_non_planning_detail', array('no_pengajuan' => $item->no_pengajuan))->result_array();
			$arr_nmbarang = array();
			$arr_spec = array();
			$arr_qty = array();
			$arr_tanggal = array();
			$arr_ket = array();
			foreach ($list_barang as $val => $valx) {
				$get_satuan = $this->db->get_where('ms_satuan', array('id' => $valx['satuan']))->result();
				$nm_satuan = (!empty($get_satuan)) ? strtolower($get_satuan[0]->code) : '';
				$arr_nmbarang[$val] = "&bull; " . strtoupper($valx['nm_barang']);
				$arr_spec[$val] = "&bull; " . strtoupper($valx['spec']);
				$arr_qty[$val] = "&bull; " . floatval($valx['qty']) . ' ' . $nm_satuan;
				$tgl_dibutuhkan = ($valx['tanggal'] <> '0000-00-00' and $valx['tanggal'] != NULL) ? date('d-M-Y', strtotime($valx['tanggal'])) : 'not set';
				$arr_tanggal[$val] = "&bull; " . $tgl_dibutuhkan;
				$arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
			}
			$dt_nama_barang    = implode("<br>", $arr_nmbarang);
			$dt_spec    = implode("<br>", $arr_spec);
			$dt_qty    = implode("<br>", $arr_qty);
			$dt_tanggal    = implode("<br>", $arr_tanggal);
			$dt_ket    = implode("<br>", $arr_ket);

			$hasil .= '<td>' . $dt_nama_barang . '</td>';
			$hasil .= '<td>' . $dt_spec . '</td>';
			$hasil .= '<td>' . $dt_qty . '</td>';
			$hasil .= '<td>' . $dt_tanggal . '</td>';
			$hasil .= '<td>' . $dt_ket . '</td>';

			$last_by     = (!empty($item->updated_by)) ? $item->updated_by : $item->created_by;
			$last_date = (!empty($item->updated_date)) ? $item->updated_date : $item->created_date;

			if ($item->sts_app == 'N') {
				$warna     = 'blue';
				$sts     = 'WAITING APPROVAL';
			} elseif ($item->sts_app == 'Y') {
				$warna     = 'green';
				$sts     = 'APPROVED';
			} else {
				$warna     = 'red';
				$sts     = 'REJECTED';
			}

			if (($item->sts_reject1 !== null || $item->sts_reject2 !== null || $item->sts_reject3 !== null) && $item->rejected == 1) {
				if ($item->sts_reject1 == "1") :
					$warna = "red";
					$sts = "Rejected By Head Department";
				elseif ($item->sts_reject2 == "1") :
					$warna = "red";
					$sts = "Rejected By Cost Control";
				elseif ($item->sts_reject3 == "1") :
					$warna = "red";
					$sts = "Rejected By Management";
				endif;
			} else {
				if ($item->app_1 == null && $item->app_2 == null && $item->app_3 == null) :
					$warna = "blue";
					$sts = "Waiting Approval Head Department";
				elseif ($item->app_1 !== null && $item->app_2 == null && $item->app_3 == null) :
					$warna = "blue";
					$sts = "Waiting Approval Cost Control";
				elseif ($item->app_1 !== null && $item->app_2 !== null && $item->app_3 == null) :
					$warna = "blue";
					$sts = "Waiting Approval Management";
				else :
					if ($item->sts_app == "Y") :
						$warna = "green";
						$sts = "Approved";
					else :
						$warna = "blue";
						$sts = "Waiting Approval Head Department";
					endif;
				endif;
			}

			$hasil .= '<td><span class="badge" style="background-color: ' . $warna . '">' . $sts . '</span></td>';

			$view        = "<a href='" . base_url('non_rutin/add/' . $item->no_pengajuan . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
			$edit        = "";
			$approve    = "";
			$cancel        = "";
			$print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $item->no_pengajuan) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";

			if ($item->sts_app == 'N' || $item->sts_app == '') {
				$edit    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $item->no_pengajuan) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
			}

			$close = '';
			if ($ENABLE_DELETE) {
				$close = '<button type="button" class="btn btn-sm btn-danger close_pr_modal" data-no_pengajuan="' . $item->no_pengajuan . '" title="Close PR"><i class="fa fa-close"></i></button>';
			}

			$hasil .= '<td>' . $view . ' ' . $edit . ' ' . $approve . ' ' . $cancel . ' ' . $print . ' ' . $close . '</td>';

			$hasil .= '</tr>';

			$no++;
		}

		$hasil .= '</tbody>';
		$hasil .= '</table>';

		echo $hasil;
	}

	public function close_pr_modal()
	{
		$no_pengajuan = $this->input->post('no_pengajuan');

		$get_no_pr = $this->db->get_where('rutin_non_planning_header', ['no_pengajuan' => $no_pengajuan])->row();

		$this->template->set('no_pr', $get_no_pr->no_pr);
		$this->template->set('no_pengajuan', $no_pengajuan);
		$this->template->render('close_pr_modal');
	}

	public function close_pr()
	{
		$no_pengajuan = $this->input->post('no_pengajuan');
		$close_pr_reason = $this->input->post('close_pr_reason');

		$this->db->trans_start();

		$update_close_pr = $this->db->update('rutin_non_planning_header', ['close_pr' => 1, 'close_pr_desc' => $close_pr_reason], ['no_pengajuan' => $no_pengajuan]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();

			$valid = 0;
		} else {
			$this->db->trans_commit();

			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}
}
