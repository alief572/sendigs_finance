<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bom_hi_grid_custom extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'BOM_HI-Grid_Custom.View';
	protected $addPermission  	= 'BOM_HI-Grid_Custom.Add';
	protected $managePermission = 'BOM_HI-Grid_Custom.Manage';
	protected $deletePermission = 'BOM_HI-Grid_Custom.Delete';

	public function __construct()
	{
		parent::__construct();

		// $this->load->library(array('Mpdf'));
		$this->load->model(array(
			'Bom_hi_grid_custom/bom_hi_grid_custom_model'
		));

		date_default_timezone_set('Asia/Bangkok');
	}

	//========================================================BOM

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$data = $this->bom_hi_grid_custom_model->get_data('bom_header', 'deleted', 'N');
		history("View index BOM Assembly");
		$this->template->set('results', $data);
		$this->template->title('BOM Assembly');
		$this->template->render('index');
	}

	public function data_side_bom()
	{
		$this->bom_hi_grid_custom_model->get_json_bom();
	}

	public function add()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			// print_r($data);
			// exit;
			$session 		 = $this->session->userdata('app_session');
			$Ym					  = date('ym');
			$no_bom        = $data['no_bom'];
			$no_bomx        = $data['no_bom'];

			if (!empty($data['Detail'])) {
				$Detail	= $data['Detail'];
			}

			if (!empty($data['DetailAdt'])) {
				$DetailAdt	= $data['DetailAdt'];
			}

			if (!empty($data['DetailTop'])) {
				$DetailTop	= $data['DetailTop'];
			}

			if (!empty($data['DetailHiGrid'])) {
				$DetailHiGrid	= $data['DetailHiGrid'];
			}

			if (!empty($data['DetailAcc'])) {
				$DetailAcc	= $data['DetailAcc'];
			}

			if (!empty($data['DetailMatJoint'])) {
				$DetailMatJoint	= $data['DetailMatJoint'];
			}

			if (!empty($data['DetailFlat'])) {
				$DetailFlat	= $data['DetailFlat'];
			}

			if (!empty($data['DetailEnd'])) {
				$DetailEnd	= $data['DetailEnd'];
			}

			if (!empty($data['DetailJadi'])) {
				$DetailJadi	= $data['DetailJadi'];
			}

			if (!empty($data['DetailOthers'])) {
				$DetailOthers	= $data['DetailOthers'];
			}

			$created_by   = 'updated_by';
			$created_date = 'updated_date';
			$tanda        = 'Insert ';
			if (empty($no_bomx)) {
				//pengurutan kode
				$srcMtr			  = "SELECT MAX(no_bom) as maxP FROM bom_header WHERE no_bom LIKE 'BOC" . $Ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		  = (int)substr($angkaUrut2, 7, 3);
				$urutan2++;
				$urut2			  = sprintf('%03s', $urutan2);
				$no_bom	      = "BOC" . $Ym . $urut2;

				$created_by   = 'created_by';
				$created_date = 'created_date';
				$tanda        = 'Update ';
			}

			$ArrHeader2		= array(
				'no_bom'			=> $no_bom,
				'category' 			=> 'grid custom',
				'id_product'	    => $data['id_product'],
				'variant_product'	=> $data['variant_product'],
				// 'fire_retardant'	=> $data['fire_retardant'],
				// 'anti_uv'			=> $data['anti_uv'],
				// 'tixotropic'		=> $data['tixotropic'],
				// 'food_grade'		=> $data['food_grade'],
				// 'wax'				=> $data['wax'],
				// 'corrosion'			=> $data['corrosion'],
				// 'waste_product'	    => str_replace(',','',$data['waste_product']),
				// 'waste_setting'	    => str_replace(',','',$data['waste_setting']),
				$created_by	    => $session['id_user'],
				$created_date	  => date('Y-m-d H:i:s')
			);

			//UPLOAD DOCUMENT
			$dataProcess2 = [];
			if (!empty($_FILES['photo']["tmp_name"])) {
				$target_dir     = "assets/files/";
				$target_dir_u   = get_root3() . "/assets/files/";
				$name_file      = 'bom-ukuran-jadi-' . $no_bom . "-" . date('Ymdhis');
				$target_file    = $target_dir . basename($_FILES['photo']["name"]);
				$name_file_ori  = basename($_FILES['photo']["name"]);
				$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
				$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;

				// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){

				$terupload = move_uploaded_file($_FILES['photo']["tmp_name"], $nama_upload);
				$link_url    	= $target_dir . $name_file . "." . $imageFileType;

				$dataProcess2	= array('file_upload' => $link_url);
				// }
			}

			$ArrHeader = array_merge($ArrHeader2, $dataProcess2);

			$ArrDetail	= array();
			$ArrDetail2	= array();
			$ArrDetail21	= array();
			$ArrDetail3	= array();
			$ArrDetail31	= array();
			$ArrDetail4	= array();
			$ArrDetail4MatLayer	= array();
			$ArrDetail5	= array();
			$ArrDetail51	= array();
			$ArrDetail6	= array();
			$ArrDetail61	= array();
			$ArrDetail7	= array();
			$ArrDetail71	= array();
			$ArrDetail8	= array();
			$ArrDetail9	= array();
			$ArrDetail91 = array();
			$ArrDetail81	= array();
			$ArrDetail82	= array();
			$ArrDetail83	= array();
			if (!empty($data['Detail'])) {
				foreach ($Detail as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail[$val]['no_bom'] 			= $no_bom;
					$ArrDetail[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail[$val]['code_material'] 	= $valx['code_material'];
					$ArrDetail[$val]['weight'] 	 		= str_replace(',', '', $valx['weight']);
				}
			}

			if (!empty($data['DetailAdt'])) {
				$GET_ADDITIVE = get_persen_additive();
				foreach ($DetailAdt as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail2[$val]['category'] 		= 'additive';
					$ArrDetail2[$val]['no_bom'] 		= $no_bom;
					$ArrDetail2[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail2[$val]['code_material'] 	= $valx['code_material'];

					if (!empty($valx['detail'])) {
						foreach ($valx['detail'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$key2 = $valx['code_material'] . '-' . $valx2['code_material'];
							$persen = (!empty($GET_ADDITIVE[$key2]['persen'])) ? $GET_ADDITIVE[$key2]['persen'] : 0;

							$ArrDetail21[$key]['category'] 		= 'additive';
							$ArrDetail21[$key]['no_bom'] 		= $no_bom;
							$ArrDetail21[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail21[$key]['code_material'] = $valx2['code_material'];
							$ArrDetail21[$key]['nm_material'] 	= $valx2['nm_material'];
							$ArrDetail21[$key]['weight'] 		= str_replace(',', '', $valx2['berat']);
							$ArrDetail21[$key]['persen'] 		= $persen;
						}
					}
				}
			}

			if (!empty($data['DetailHiGrid'])) {
				foreach ($DetailHiGrid as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail8[$val]['category'] 		= 'hi grid std';
					$ArrDetail8[$val]['no_bom'] 		= $no_bom;
					$ArrDetail8[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail8[$val]['code_material'] 	= $valx['code_material'];
					$ArrDetail8[$val]['ket'] 			= $valx['ket'];
					$ArrDetail8[$val]['qty'] 			= str_replace(',', '', $valx['qty']);
					$ArrDetail8[$val]['unit'] 			= $valx['unit'];

					if (!empty($valx['detail'])) {
						foreach ($valx['detail'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$ArrDetail81[$key]['category'] 		= 'hi grid std';
							$ArrDetail81[$key]['no_bom'] 		= $no_bom;
							$ArrDetail81[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail81[$key]['code_material'] = $valx2['code_material'];
							$ArrDetail81[$key]['nm_material'] 	= $valx2['nm_material'];
							$ArrDetail81[$key]['weight'] 		= str_replace(',', '', $valx2['berat']);
						}
					}
					if (!empty($valx['ukuran_jadi'])) {
						foreach ($valx['ukuran_jadi'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$ArrDetail82[$key]['category'] 		= 'ukuran jadi';
							$ArrDetail82[$key]['no_bom'] 		= $no_bom;
							$ArrDetail82[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail82[$key]['length'] 		= str_replace(',', '', $valx2['length']);
							$ArrDetail82[$key]['width'] 		= str_replace(',', '', $valx2['width']);
							$ArrDetail82[$key]['qty'] 			= str_replace(',', '', $valx2['qty']);
							$ArrDetail82[$key]['lari'] 			= str_replace(',', '', $valx2['lari']);
						}
					}
					if (!empty($valx['cutting'])) {
						foreach ($valx['cutting'] as $val2 => $valx2) {
							$key = $val . '-' . $val2 . '-9999';
							$ArrDetail83[$key]['category'] 		= 'material cutting';
							$ArrDetail83[$key]['no_bom'] 		= $no_bom;
							$ArrDetail83[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail83[$key]['code_material']	= $valx2['id_material'];
							$ArrDetail83[$key]['weight'] 		= str_replace(',', '', $valx2['weight']);
						}
					}
				}
			}

			if (!empty($data['DetailTop'])) {
				foreach ($DetailTop as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail3[$val]['category'] 		= 'topping';
					$ArrDetail3[$val]['no_bom'] 		= $no_bom;
					$ArrDetail3[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail3[$val]['code_material'] 	= $valx['code_material'];
					$ArrDetail3[$val]['ket'] 			= $valx['ket'];
					$ArrDetail3[$val]['qty'] 			= str_replace(',', '', $valx['qty']);
					$ArrDetail3[$val]['unit'] 			= $valx['unit'];

					if (!empty($valx['detail'])) {
						foreach ($valx['detail'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$ArrDetail31[$key]['category'] 		= 'topping';
							$ArrDetail31[$key]['no_bom'] 		= $no_bom;
							$ArrDetail31[$key]['no_bom_detail'] 	= $no_bom . "-" . $urut;
							$ArrDetail31[$key]['code_material'] 	= $valx2['code_material'];
							$ArrDetail31[$key]['nm_material'] 	= $valx2['nm_material'];
							$ArrDetail31[$key]['weight'] 		= str_replace(',', '', $valx2['berat']);
						}
					}
				}
			}

			if (!empty($data['DetailAcc'])) {
				foreach ($DetailAcc as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail4[$val]['category'] 		= 'accessories';
					$ArrDetail4[$val]['no_bom'] 		= $no_bom;
					$ArrDetail4[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail4[$val]['code_material'] 	= $valx['code_material'];
					$ArrDetail4[$val]['ket'] 			= $valx['ket'];
					$ArrDetail4[$val]['weight'] 	 	= str_replace(',', '', $valx['weight']);
				}
			}

			if (!empty($data['DetailMatJoint'])) {
				foreach ($DetailMatJoint as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail4MatLayer[$val]['category'] 		= 'mat joint';
					$ArrDetail4MatLayer[$val]['no_bom'] 		= $no_bom;
					$ArrDetail4MatLayer[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail4MatLayer[$val]['code_material'] 	= $valx['code_material'];
					$ArrDetail4MatLayer[$val]['ket'] 			= $valx['ket'];
					$ArrDetail4MatLayer[$val]['layer'] 			= $valx['layer'];
					$ArrDetail4MatLayer[$val]['weight'] 	 	= str_replace(',', '', $valx['weight']);
				}
			}

			if (!empty($data['DetailFlat'])) {
				foreach ($DetailFlat as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail5[$val]['category'] 		= 'flat sheet';
					$ArrDetail5[$val]['no_bom'] 		= $no_bom;
					$ArrDetail5[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail5[$val]['length'] 	 	= str_replace(',', '', $valx['length']);
					$ArrDetail5[$val]['width'] 	 		= str_replace(',', '', $valx['width']);
					$ArrDetail5[$val]['qty'] 	 		= str_replace(',', '', $valx['qty']);
					$ArrDetail5[$val]['m2'] 	 		= str_replace(',', '', $valx['m2']);

					if (!empty($valx['material'])) {
						foreach ($valx['material'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$ArrDetail51[$key]['category'] 		= 'material flat sheet';
							$ArrDetail51[$key]['no_bom'] 		= $no_bom;
							$ArrDetail51[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail51[$key]['code_material']	= $valx2['id_material'];
							$ArrDetail51[$key]['weight'] 		= str_replace(',', '', $valx2['weight']);
						}
					}
				}
			}

			if (!empty($data['DetailEnd'])) {
				foreach ($DetailEnd as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail6[$val]['category'] 		= 'end plate';
					$ArrDetail6[$val]['no_bom'] 		= $no_bom;
					$ArrDetail6[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail6[$val]['length'] 	 	= str_replace(',', '', $valx['length']);
					$ArrDetail6[$val]['width'] 	 		= str_replace(',', '', $valx['width']);
					$ArrDetail6[$val]['qty'] 	 		= str_replace(',', '', $valx['qty']);
					$ArrDetail6[$val]['m2'] 	 		= str_replace(',', '', $valx['m2']);

					if (!empty($valx['material'])) {
						foreach ($valx['material'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$ArrDetail61[$key]['category'] 		= 'material end plate';
							$ArrDetail61[$key]['no_bom'] 		= $no_bom;
							$ArrDetail61[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail61[$key]['code_material']	= $valx2['id_material'];
							$ArrDetail61[$key]['weight'] 		= str_replace(',', '', $valx2['weight']);
						}
					}
				}
			}

			if (!empty($data['DetailJadi'])) {
				foreach ($DetailJadi as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail7[$val]['category'] 		= 'ukuran jadi';
					$ArrDetail7[$val]['no_bom'] 		= $no_bom;
					$ArrDetail7[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail7[$val]['length'] 	 	= str_replace(',', '', $valx['length']);
					$ArrDetail7[$val]['width'] 	 		= str_replace(',', '', $valx['width']);
					$ArrDetail7[$val]['qty'] 	 		= str_replace(',', '', $valx['qty']);
					$ArrDetail7[$val]['m2'] 	 		= str_replace(',', '', $valx['m2']);

					if (!empty($valx['material'])) {
						foreach ($valx['material'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$ArrDetail71[$key]['category'] 		= 'material ukuran jadi';
							$ArrDetail71[$key]['no_bom'] 		= $no_bom;
							$ArrDetail71[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail71[$key]['code_material']	= $valx2['id_material'];
							$ArrDetail71[$key]['weight'] 		= str_replace(',', '', $valx2['weight']);
						}
					}
				}
			}

			if (!empty($data['DetailOthers'])) {
				foreach ($DetailOthers as $val => $valx) {
					$urut = sprintf('%03s', $val);
					$ArrDetail9[$val]['category'] 		= 'others';
					$ArrDetail9[$val]['no_bom'] 		= $no_bom;
					$ArrDetail9[$val]['no_bom_detail'] 	= $no_bom . "-" . $urut;
					$ArrDetail9[$val]['length'] 	 	= str_replace(',', '', $valx['length']);
					$ArrDetail9[$val]['width'] 	 		= str_replace(',', '', $valx['width']);
					$ArrDetail9[$val]['qty'] 	 		= str_replace(',', '', $valx['qty']);
					$ArrDetail9[$val]['m2'] 	 		= str_replace(',', '', $valx['m2']);

					if (!empty($valx['material'])) {
						foreach ($valx['material'] as $val2 => $valx2) {
							$key = $val . '-' . $val2;
							$ArrDetail91[$key]['category'] 		= 'material others';
							$ArrDetail91[$key]['no_bom'] 		= $no_bom;
							$ArrDetail91[$key]['no_bom_detail'] = $no_bom . "-" . $urut;
							$ArrDetail91[$key]['code_material']	= $valx2['id_material'];
							$ArrDetail91[$key]['weight'] 		= str_replace(',', '', $valx2['weight']);
						}
					}
				}
			}

			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();
			if (empty($no_bomx)) {
				$this->db->insert('bom_header', $ArrHeader);
			}
			if (!empty($no_bomx)) {
				$this->db->where('no_bom', $no_bom);
				$this->db->update('bom_header', $ArrHeader);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'));
			if (!empty($ArrDetail)) {
				$this->db->insert_batch('bom_detail', $ArrDetail);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'));
			if (!empty($ArrDetail2)) {
				$this->db->insert_batch('bom_detail', $ArrDetail2);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'additive'));
			if (!empty($ArrDetail21)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail21);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'));
			if (!empty($ArrDetail3)) {
				$this->db->insert_batch('bom_detail', $ArrDetail3);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'topping'));
			if (!empty($ArrDetail31)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail31);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'hi grid std'));
			if (!empty($ArrDetail8)) {
				$this->db->insert_batch('bom_detail', $ArrDetail8);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'hi grid std'));
			if (!empty($ArrDetail81)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail81);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'));
			if (!empty($ArrDetail82)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail82);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material cutting'));
			if (!empty($ArrDetail83)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail83);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'));
			if (!empty($ArrDetail4)) {
				$this->db->insert_batch('bom_detail', $ArrDetail4);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'));
			if (!empty($ArrDetail4MatLayer)) {
				$this->db->insert_batch('bom_detail', $ArrDetail4MatLayer);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'));
			if (!empty($ArrDetail5)) {
				$this->db->insert_batch('bom_detail', $ArrDetail5);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material flat sheet'));
			if (!empty($ArrDetail51)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail51);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'));
			if (!empty($ArrDetail6)) {
				$this->db->insert_batch('bom_detail', $ArrDetail6);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material end plate'));
			if (!empty($ArrDetail61)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail61);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'));
			if (!empty($ArrDetail7)) {
				$this->db->insert_batch('bom_detail', $ArrDetail7);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material ukuran jadi'));
			if (!empty($ArrDetail71)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail71);
			}

			$this->db->delete('bom_detail', array('no_bom' => $no_bom, 'category' => 'others'));
			if (!empty($ArrDetail9)) {
				$this->db->insert_batch('bom_detail', $ArrDetail9);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom, 'category' => 'material others'));
			if (!empty($ArrDetail91)) {
				$this->db->insert_batch('bom_detail_custom', $ArrDetail91);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=> 'Save gagal disimpan ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Save berhasil disimpan. Thanks ...',
					'status'	=> 1
				);
				history($tanda . " BOM " . $no_bom);
			}
			echo json_encode($Arr_Data);
		} else {
			$session  = $this->session->userdata('app_session');
			$no_bom 	  		= $this->uri->segment(3);
			$header   			= $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
			$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
			$detail_hi_grid   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'hi grid std'))->result_array();
			$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
			$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
			$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
			$detail_mat_joint 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'))->result_array();
			$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
			$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
			$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
			$detail_others 		= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'others'))->result_array();
			$product			= $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));
			$material			= $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'material'));
			$accessories		= $this->bom_hi_grid_custom_model->get_data_where_array('accessories', array('deleted_date' => NULL));
			$bom_additive    	= $this->bom_hi_grid_custom_model->get_data_where_array('bom_header', array('deleted_date' => NULL, 'category' => 'additive'));
			$bom_topping    	= $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_3 b', 'a.id_product=b.code_lv3', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'topping'))->result();
			$bom_higridstd1    	= $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'grid standard'))->result();
			$bom_higridstd2    	= $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'standard'))->result();
			$bom_higridstd 		= array_merge($bom_higridstd1, $bom_higridstd2);
			$satuan				= $this->bom_hi_grid_custom_model->get_data_where_array('ms_satuan', array('deleted_date' => NULL, 'category' => 'unit'));

			// print_r($header);
			// exit;
			$data = [
				'header' => $header,
				'detail' => $detail,
				'satuan' => $satuan,
				'detail_hi_grid' => $detail_hi_grid,
				'detail_additive' => $detail_additive,
				'detail_topping' => $detail_topping,
				'detail_accessories' => $detail_accessories,
				'detail_mat_joint' => $detail_mat_joint,
				'detail_flat_sheet' => $detail_flat_sheet,
				'detail_end_plate' => $detail_end_plate,
				'detail_ukuran_jadi' => $detail_ukuran_jadi,
				'detail_others' => $detail_others,
				'product' => $product,
				'material' => $material,
				'accessories' => $accessories,
				'bom_additive' => $bom_additive,
				'bom_topping' => $bom_topping,
				'bom_higridstd' => $bom_higridstd,
				'GET_LEVEL4' => get_inventory_lv4(),
			];

			$this->template->set('results', $data);
			$this->template->title('Add BOM Assembly');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add', $data);
		}
	}

	public function detail()
	{
		// $this->auth->restrict($this->viewPermission);
		$no_bom 	= $this->input->post('no_bom');
		$header = $this->db->get_where('bom_header', array('no_bom' => $no_bom))->result();
		$detail   			= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'default'))->result_array();
		$detail_hi_grid   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'hi grid std'))->result_array();
		$detail_additive   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'additive'))->result_array();
		$detail_topping   	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'topping'))->result_array();
		$detail_accessories = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'accessories'))->result_array();
		$detail_mat_joint 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'mat joint'))->result_array();
		$detail_flat_sheet 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'flat sheet'))->result_array();
		$detail_end_plate 	= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'end plate'))->result_array();
		$detail_ukuran_jadi = $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'ukuran jadi'))->result_array();
		$detail_others 		= $this->db->get_where('bom_detail', array('no_bom' => $no_bom, 'category' => 'others'))->result_array();
		$product    = $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'product'));
		// print_r($header);
		$data = [
			'header' => $header,
			'detail' => $detail,
			'detail_hi_grid' => $detail_hi_grid,
			'detail_additive' => $detail_additive,
			'detail_topping' => $detail_topping,
			'detail_accessories' => $detail_accessories,
			'detail_mat_joint' => $detail_mat_joint,
			'detail_flat_sheet' => $detail_flat_sheet,
			'detail_end_plate' => $detail_end_plate,
			'detail_ukuran_jadi' => $detail_ukuran_jadi,
			'detail_others' => $detail_others,
			'product' => $product,
			'GET_LEVEL4' => get_inventory_lv4(),
			'GET_ACC' => get_accessories()
		];
		$this->template->set('results', $data);
		$this->template->render('detail', $data);
	}

	public function get_add()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$material    = $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4', array('deleted_date' => NULL, 'category' => 'material'));
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='header_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='Detail[" . $id . "][code_material]' class='chosen_select form-control input-sm inline-blockd material'>";
		$d_Header .= "<option value='0'>Select Material Name</option>";
		foreach ($material as $valx) {
			$d_Header .= "<option value='" . $valx->code_lv4 . "'>" . strtoupper($valx->nama) . "</option>";
		}
		$d_Header .= 		"</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='Detail[" . $id . "][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Weight'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='add_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addPart' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function hapus()
	{
		$data = $this->input->post();
		$session 		= $this->session->userdata('app_session');
		$no_bom  = $data['id'];

		$ArrHeader		= array(
			'deleted'			  => "Y",
			'deleted_by'	  => $session['id_user'],
			'deleted_date'	=> date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
		$this->db->where('no_bom', $no_bom);
		$this->db->update('bom_header', $ArrHeader);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
			history("Delete data BOM " . $no_bom);
		}

		echo json_encode($Arr_Data);
	}

	public function excel_report_all_bom()
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		$objPHPExcel	= new PHPExcel();

		$tableHeader 	= tableHeader();
		$mainTitle 		= mainTitle();
		$tableBodyCenter = tableBodyCenter();
		$tableBodyLeft 	= tableBodyLeft();
		$tableBodyRight = tableBodyRight();

		$sheet 		= $objPHPExcel->getActiveSheet();

		$product    = $this->db
			->select('a.*, b.nama AS nm_product')
			->order_by('a.no_bom', 'desc')
			->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
			->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'grid custom'))
			->result_array();

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(12);
		$sheet->setCellValue('A' . $Row, 'BOM HI GRID STANDARD');
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow + 1;

		$sheet->setCellValue('A' . $NewRow, 'No');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->setCellValue('B' . $NewRow, 'Product Name');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->setCellValue('C' . $NewRow, 'Variant');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D' . $NewRow, 'Total Weight');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'Waste Product (%)');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setAutoSize(true);

		$sheet->setCellValue('F' . $NewRow, 'Waste Setting/Cleaning (%)');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setAutoSize(true);

		$sheet->setCellValue('G' . $NewRow, 'Fire Reterdant');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
		$sheet->getColumnDimension('G')->setAutoSize(true);

		$sheet->setCellValue('H' . $NewRow, 'Anti UV');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
		$sheet->getColumnDimension('H')->setAutoSize(true);

		$sheet->setCellValue('I' . $NewRow, 'Tixotropic');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
		$sheet->getColumnDimension('I')->setAutoSize(true);

		$sheet->setCellValue('J' . $NewRow, 'Food Grade');
		$sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);
		$sheet->getColumnDimension('J')->setAutoSize(true);

		$sheet->setCellValue('K' . $NewRow, 'Wax');
		$sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);
		$sheet->getColumnDimension('K')->setAutoSize(true);

		$sheet->setCellValue('L' . $NewRow, 'Corrosion');
		$sheet->getStyle('L' . $NewRow . ':L' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('L' . $NewRow . ':L' . $NextRow);
		$sheet->getColumnDimension('L')->setAutoSize(true);

		if ($product) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($product as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$nomor	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nomor);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nm_product	= $row_Cek['nm_product'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_product);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$variant_product	= $row_Cek['variant_product'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $variant_product);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$SUM_WEIGHT = $this->db->query("SELECT SUM(weight) AS berat FROM bom_detail WHERE no_bom = '" . $row_Cek['no_bom'] . "' ")->result();
				$awal_col++;
				$status_date	= number_format($SUM_WEIGHT[0]->berat, 4);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $status_date);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				$awal_col++;
				$waste_product	= $row_Cek['waste_product'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $waste_product);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$waste_setting	= $row_Cek['waste_setting'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $waste_setting);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$fire_retardant	= ($row_Cek['fire_retardant'] == 1) ? 'Yes' : 'No';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $fire_retardant);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$anti_uv	= ($row_Cek['anti_uv'] == 1) ? 'Yes' : 'No';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $anti_uv);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$tixotropic	= ($row_Cek['tixotropic'] == 1) ? 'Yes' : 'No';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $tixotropic);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$food_grade	= ($row_Cek['food_grade'] == 1) ? 'Yes' : 'No';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $food_grade);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$wax	= ($row_Cek['wax'] == 1) ? 'Yes' : 'No';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $wax);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$corrosion	= ($row_Cek['corrosion'] != 0 or $row_Cek['corrosion'] != NULL) ? $row_Cek['corrosion'] : '-';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $corrosion);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}

		$sheet->setTitle('BOM');
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
		header('Content-Disposition: attachment;filename="bom-hi-grid-custom.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function excel_report_all_bom_detail()
	{
		$kode_bom = $this->uri->segment(3);
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		$objPHPExcel	= new PHPExcel();

		$tableHeader 	= tableHeader();
		$mainTitle 		= mainTitle();
		$tableBodyCenter = tableBodyCenter();
		$tableBodyLeft 	= tableBodyLeft();
		$tableBodyRight = tableBodyRight();

		$sheet 		= $objPHPExcel->getActiveSheet();

		$sql = "
  			SELECT
  				a.id_product,
				a.variant_product,
          		b.code_material,
          		b.weight,
				c.nama AS nm_product
  			FROM
  				bom_header a 
				LEFT JOIN bom_detail b ON a.no_bom = b.no_bom
				LEFT JOIN new_inventory_4 c ON a.id_product = c.code_lv4
  		    WHERE 
				a.no_bom = '" . $kode_bom . "' 
				AND b.no_bom = '" . $kode_bom . "'
				AND a.category = 'grid custom'
  			ORDER BY
  				b.id ASC
  		";
		$product    = $this->db->query($sql)->result_array();

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(3);
		$sheet->setCellValue('A' . $Row, 'BOM HI GRID STANDARD DETAIL');
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;

		$sheet->setCellValue('A' . $NewRow, $product[0]['nm_product']);
		$sheet->getStyle('A' . $NewRow . ':C' . $NewRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('A' . $NewRow . ':C' . $NewRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$NewRow	 = $NewRow + 1;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, $product[0]['variant_product']);
		$sheet->getStyle('A' . $NewRow . ':C' . $NewRow)->applyFromArray($tableBodyLeft);
		$sheet->mergeCells('A' . $NewRow . ':C' . $NewRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$NewRow	 = $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, 'No');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setAutoSize(true);

		$sheet->setCellValue('B' . $NewRow, 'Material Name');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setAutoSize(true);

		$sheet->setCellValue('C' . $NewRow, 'Total Weight');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		if ($product) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($product as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $no);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$status_date	= strtoupper(get_name('new_inventory_4', 'nama', 'code_lv4', $row_Cek['code_material']));
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $status_date);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$status_date	= number_format($row_Cek['weight'], 4);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $status_date);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
			}
		}


		$sheet->setTitle('List BOM DETAIL');
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
		header('Content-Disposition: attachment;filename="bom-hi-grid-custom-detail-' . $kode_bom . '.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function get_add_additive()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$material    = $this->bom_hi_grid_custom_model->get_data_where_array('bom_header', array('deleted_date' => NULL, 'category' => 'additive'));
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headeradditive_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailAdt[" . $id . "][code_material]' data-id='" . $id . "' class='chosen_select form-control input-sm inline-blockd changeFungsiAdditive'>";
		$d_Header .= "<option value='0'>Select Additive</option>";
		foreach ($material as $valx) {
			$d_Header .= "<option value='" . $valx->no_bom . "'>" . strtoupper($valx->additive_name) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<table class='table table-bordered additiveMat" . $id . "'></table>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='addadditive_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-info addPartAdditive' title='Add Additive'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Additive</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_additive_breakdown()
	{
		$id 		= $this->uri->segment(3);
		$id_row 	= $this->uri->segment(4);


		$material    = $this->bom_hi_grid_custom_model->get_data_where_array('bom_detail', array('no_bom' => $id));
		$GET_LEVEL4 = get_inventory_lv4();

		$d_Header = "";
		$nomor = 0;
		foreach ($material as $valx) {
			$nomor++;
			$nm_material = (!empty($GET_LEVEL4[$valx->code_material]['nama'])) ? $GET_LEVEL4[$valx->code_material]['nama'] : '';
			$datetime 	= $id_row . '-' . $nomor;

			$d_Header .= "<tr>";
			$d_Header .= "<td width='70%'>";
			$d_Header .= "<input type='hidden' name='DetailAdt[" . $id_row . "][detail][" . $datetime . "][code_material]' class='form-control input-md' value='" . $valx->code_material . "'>";
			$d_Header .= "<input type='text' name='DetailAdt[" . $id_row . "][detail][" . $datetime . "][nm_material]' class='form-control input-md' value='" . $nm_material . "' readonly>";
			$d_Header .= "</td>";
			$d_Header .= "<td>";
			$d_Header .= "<input type='text' name='DetailAdt[" . $id_row . "][detail][" . $datetime . "][berat]' class='form-control input-md autoNumeric4 qty' placeholder='Weight'>";
			$d_Header .= "</td>";
			$d_Header .= "</tr>";
		}

		echo json_encode(array(
			'material'	=> $d_Header
		));
	}

	public function get_add_topping()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$GET_LEVEL3 = get_inventory_lv3();

		$material    = $this->bom_hi_grid_custom_model->get_data_where_array('bom_header', array('deleted_date' => NULL, 'category' => 'topping'));
		$satuan		= $this->bom_hi_grid_custom_model->get_data_where_array('ms_satuan', array('deleted_date' => NULL, 'category' => 'unit'));
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headertopping_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailTop[" . $id . "][code_material]' data-id='" . $id . "' class='chosen_select form-control input-sm inline-blockd changeFungsiTopping'>";
		$d_Header .= "<option value='0'>Select Topping</option>";
		foreach ($material as $valx) {
			$nm_jenis = (!empty($GET_LEVEL3[$valx->id_product]['nama'])) ? $GET_LEVEL3[$valx->id_product]['nama'] : '';
			$d_Header .= "<option value='" . $valx->no_bom . "'>" . strtoupper($nm_jenis . ' | ' . $valx->variant_product) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<table class='table table-bordered toppingMat" . $id . "'></table>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailTop[" . $id . "][qty]' class='form-control input-md autoNumeric0 text-center' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailTop[" . $id . "][unit]' data-id='" . $id . "' class='chosen_select form-control input-sm inline-blockd'>";
		$d_Header .= "<option value='0'>Select Unit</option>";
		foreach ($satuan as $valx) {
			$d_Header .= "<option value='" . $valx->id . "'>" . strtoupper($valx->code) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailTop[" . $id . "][ket]' class='form-control input-md' placeholder='Keterangan'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='addtopping_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPartTopping' title='Add Topping'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Topping</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_topping_breakdown()
	{
		$id 		= $this->uri->segment(3);
		$id_row 	= $this->uri->segment(4);


		$material    = $this->bom_hi_grid_custom_model->get_data_where_array('bom_detail', array('no_bom' => $id));
		$GET_LEVEL4 = get_inventory_lv4();

		$d_Header = "";
		$nomor = 0;
		foreach ($material as $valx) {
			$nomor++;
			$nm_material = (!empty($GET_LEVEL4[$valx->code_material]['nama'])) ? $GET_LEVEL4[$valx->code_material]['nama'] : '';
			$datetime 	= $id_row . '-' . $nomor;

			$d_Header .= "<tr>";
			$d_Header .= "<td width='70%'>";
			$d_Header .= "<input type='hidden' name='DetailTop[" . $id_row . "][detail][" . $datetime . "][code_material]' class='form-control input-md' value='" . $valx->code_material . "'>";
			$d_Header .= "<input type='text' name='DetailTop[" . $id_row . "][detail][" . $datetime . "][nm_material]' class='form-control input-md' value='" . $nm_material . "' readonly>";
			$d_Header .= "</td>";
			$d_Header .= "<td>";
			$d_Header .= "<input type='text' name='DetailTop[" . $id_row . "][detail][" . $datetime . "][berat]' class='form-control input-md autoNumeric4 qty' placeholder='Weight'  readonly value='" . $valx->weight . "'>";
			$d_Header .= "</td>";
			$d_Header .= "</tr>";
		}

		echo json_encode(array(
			'material'	=> $d_Header
		));
	}

	public function get_add_accessories()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$accessories    = $this->bom_hi_grid_custom_model->get_data_where_array('accessories', array('deleted_date' => NULL));
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headeraccessories_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailAcc[" . $id . "][code_material]' class='chosen_select form-control input-sm inline-blockd material'>";
		$d_Header .= "<option value='0'>Select Accessories</option>";
		foreach ($accessories as $valx) {
			$d_Header .= "<option value='" . $valx->id . "'>" . strtoupper($valx->stock_name . ' ' . $valx->brand . ' ' . $valx->spec) . "</option>";
		}
		$d_Header .= 		"</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailAcc[" . $id . "][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailAcc[" . $id . "][ket]' class='form-control input-md' placeholder='Keterangan'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='addaccessories_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPartAcc' title='Add Accessories'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Accessories</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_mat_joint()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$material    = get_list_inventory_lv4('material');
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headermatjoint_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailMatJoint[" . $id . "][layer]' class='form-control input-md' placeholder='Layer'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailMatJoint[" . $id . "][code_material]' class='chosen_select form-control input-sm inline-blockd material'>";
		$d_Header .= "<option value='0'>Select Material</option>";
		foreach ($material as $valx => $value) {
			$d_Header .= "<option value='" . $value['code_lv4'] . "'>" . strtoupper($value['nama']) . "</option>";
		}
		$d_Header .= 		"</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailMatJoint[" . $id . "][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailMatJoint[" . $id . "][ket]' class='form-control input-md' placeholder='Keterangan'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='addmatjoint_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPartMatJoint' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material Joint</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_flat_sheet()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headerflatsheet_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailFlat[" . $id . "][length]' class='form-control input-md text-center autoNumeric4 length changeFlat' placeholder='Length'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailFlat[" . $id . "][width]' class='form-control input-md text-center autoNumeric4 width changeFlat' placeholder='Width'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailFlat[" . $id . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailFlat[" . $id . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part

		$d_Header .= "<tr id='headerflatsheet_" . $id . "_" . $no . "' class='headerflatsheet_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerflatsheet' data-label_name='DetailFlat' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		$d_Header .= "<tr id='addflatsheet_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-danger addPartFlat' title='Add Flat Sheet'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Flat Sheet</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_end_plate()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headerendplate_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailEnd[" . $id . "][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailEnd[" . $id . "][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Height'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailEnd[" . $id . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailEnd[" . $id . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part

		$d_Header .= "<tr id='headerendplate_" . $id . "_" . $no . "' class='headerendplate_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerendplate' data-label_name='DetailEnd' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		$d_Header .= "<tr id='addendplate_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addPartEnd' title='Add End Plate'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add End Plate</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_ukuran_jadi()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headerukuranjadi_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailJadi[" . $id . "][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailJadi[" . $id . "][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Width'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailJadi[" . $id . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailJadi[" . $id . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part

		$d_Header .= "<tr id='headerukuranjadi_" . $id . "_" . $no . "' class='headerukuranjadi_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerukuranjadi' data-label_name='DetailJadi' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		$d_Header .= "<tr id='addukuranjadi_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPartJadi' title='Add Ukuran Jadi'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Chequered Plate</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_others()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headerothers_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailOthers[" . $id . "][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailOthers[" . $id . "][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Width'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailOthers[" . $id . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailOthers[" . $id . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add part

		$d_Header .= "<tr id='headerothers_" . $id . "_" . $no . "' class='headerothers_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerothers' data-label_name='DetailOthers' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		$d_Header .= "<tr id='addothers_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPartOthers' title='Add Others'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Others</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_hi_grid_std()
	{
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$GET_LEVEL3 = get_inventory_lv4();

		$material1    	= $this->bom_hi_grid_custom_model->get_data_where_array('bom_header', array('deleted_date' => NULL, 'category' => 'grid standard'));
		$material2    	= $this->bom_hi_grid_custom_model->get_data_where_array('bom_header', array('deleted_date' => NULL, 'category' => 'standard'));
		$satuan			= $this->bom_hi_grid_custom_model->get_data_where_array('ms_satuan', array('deleted_date' => NULL, 'category' => 'unit'));

		$material = array_merge($material1, $material2);
		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headerhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'>" . $id . "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailHiGrid[" . $id . "][code_material]' data-id='" . $id . "' class='chosen_select form-control input-sm inline-blockd changeFungsiHiGrid'>";
		$d_Header .= "<option value='0'>Select BOM Standard</option>";
		foreach ($material as $valx) {
			$nm_jenis = (!empty($GET_LEVEL3[$valx->id_product]['nama'])) ? $GET_LEVEL3[$valx->id_product]['nama'] : '';
			$d_Header .= "<option value='" . $valx->no_bom . "'>" . strtoupper($nm_jenis . ' | ' . $valx->variant_product) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left' hidden>";
		$d_Header .= "<table class='table table-bordered higridMat" . $id . "'></table>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailHiGrid[" . $id . "][qty]' class='form-control input-md autoNumeric0 text-center' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailHiGrid[" . $id . "][unit]' data-id='" . $id . "' class='chosen_select form-control input-sm inline-blockd'>";
		$d_Header .= "<option value='0'>Select Unit</option>";
		foreach ($satuan as $valx) {
			$d_Header .= "<option value='" . $valx->id . "'>" . strtoupper($valx->code) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailHiGrid[" . $id . "][ket]' class='form-control input-md' placeholder='Keterangan'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add nya
		$d_Header .= "<tr id='addhigrid_" . $id . "_" . $no . "' class='headerhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
		$d_Header .= "<td align='center' hidden></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		$d_Header .= "<tr id='addhigridcutting_" . $id . "_" . $no . "' class='headerhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMatCut' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material Cutting</button></td>";
		$d_Header .= "<td align='center' hidden></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='addhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addPartHiGrid' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Standard & HI GRID Standard</button></td>";
		$d_Header .= "<td align='center' hidden></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_sub_ukuran_jadi()
	{
		$id 	= $this->uri->segment(3);
		$no 	= $this->uri->segment(4);

		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headerhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<div class='input-group'>";
		$d_Header .= "<span class='input-group-addon' style='background: bisque;'>Length :</span>";
		$d_Header .= "<input type='text' name='DetailHiGrid[" . $id . "][ukuran_jadi][" . $no . "][length]' class='form-control input-md autoNumeric'>";
		$d_Header .= "<span class='input-group-addon' style='background: bisque;'>Width :</span>";
		$d_Header .= "<input type='text' name='DetailHiGrid[" . $id . "][ukuran_jadi][" . $no . "][width]' class='form-control input-md autoNumeric'>";
		$d_Header .= "<span class='input-group-addon' style='background: bisque;'>Qty :</span>";
		$d_Header .= "<input type='text' name='DetailHiGrid[" . $id . "][ukuran_jadi][" . $no . "][qty]' class='form-control input-md autoNumeric'>";
		$d_Header .= "<span class='input-group-addon' style='background: bisque;'>Meter Lari :</span>";
		$d_Header .= "<input type='text' name='DetailHiGrid[" . $id . "][ukuran_jadi][" . $no . "][lari]' class='form-control input-md autoNumeric'>";
		$d_Header .= "</div>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left' hidden></td>";
		$d_Header .= "<td align='left'></td>";
		$d_Header .= "<td align='left'></td>";
		$d_Header .= "<td align='left'></td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add nya
		$d_Header .= "<tr id='addhigrid_" . $id . "_" . $no . "' class='headerhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
		$d_Header .= "<td align='center' hidden></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_sub_cutting_material()
	{
		$id 	= $this->uri->segment(3);
		$no 	= $this->uri->segment(4);

		$material    = get_list_inventory_lv4('material');

		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='headerhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select name='DetailHiGrid[" . $id . "][cutting][" . $no . "][id_material]' data-id='" . $id . "' class='chosen-select form-control input-sm inline-blockd'>";
		$d_Header .= "<option value='0'>Select Material</option>";
		foreach ($material as $valx => $value) {
			$d_Header .= "<option value='" . $value['code_lv4'] . "'>" . strtoupper($value['nama']) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left' hidden></td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='DetailHiGrid[" . $id . "][cutting][" . $no . "][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'></td>";
		$d_Header .= "<td align='left'></td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add nya
		$d_Header .= "<tr id='addhigridcutting_" . $id . "_" . $no . "' class='headerhigrid_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMatCut' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material Cutting</button></td>";
		$d_Header .= "<td align='center' hidden></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add_hi_grid_std_breakdown()
	{
		$id 		= $this->uri->segment(3);
		$id_row 	= $this->uri->segment(4);


		$material    = $this->bom_hi_grid_custom_model->get_data_where_array('bom_detail', array('no_bom' => $id));
		$GET_LEVEL4 = get_inventory_lv4();

		$d_Header = "";
		$nomor = 0;
		foreach ($material as $valx) {
			$nomor++;
			$nm_material = (!empty($GET_LEVEL4[$valx->code_material]['nama'])) ? $GET_LEVEL4[$valx->code_material]['nama'] : '';
			$datetime 	= $id_row . '-' . $nomor;

			$d_Header .= "<tr>";
			$d_Header .= "<td width='70%'>";
			$d_Header .= "<input type='hidden' name='DetailHiGrid[" . $id_row . "][detail][" . $datetime . "][code_material]' class='form-control input-md' value='" . $valx->code_material . "'>";
			$d_Header .= "<input type='text' name='DetailHiGrid[" . $id_row . "][detail][" . $datetime . "][nm_material]' class='form-control input-md' value='" . $nm_material . "' readonly>";
			$d_Header .= "</td>";
			$d_Header .= "<td>";
			$d_Header .= "<input type='text' name='DetailHiGrid[" . $id_row . "][detail][" . $datetime . "][berat]' class='form-control input-md autoNumeric4 qty' placeholder='Weight'  readonly value='" . $valx->weight . "'>";
			$d_Header .= "</td>";
			$d_Header .= "</tr>";
		}

		echo json_encode(array(
			'material'	=> $d_Header
		));
	}

	public function get_add_sub_material()
	{
		$data = $this->input->post();

		$id 		= $data['id'];
		$no 		= $data['no'];
		$labelName 	= $data['label_name'];
		$labelClass = $data['label_class'];

		$material    = get_list_inventory_lv4('material');

		$d_Header = "";
		// $d_Header .= "<tr>";
		$d_Header .= "<tr class='" . $labelClass . "_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left' colspan='2'>";
		$d_Header .= "<select name='" . $labelName . "[" . $id . "][material][" . $no . "][id_material]' data-id='" . $id . "' class='chosen-select form-control input-sm inline-blockd'>";
		$d_Header .= "<option value='0'>Select Material</option>";
		foreach ($material as $valx => $value) {
			$d_Header .= "<option value='" . $value['code_lv4'] . "'>" . strtoupper($value['nama']) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<input type='text' name='" . $labelName . "[" . $id . "][material][" . $no . "][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty'>";
		$d_Header .= "</td>";
		$d_Header .= "<td align='left'></td>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
		$d_Header .= "</td>";
		$d_Header .= "</tr>";

		//add nya
		$d_Header .= "<tr id='" . $labelClass . "_" . $id . "_" . $no . "' class='" . $labelClass . "_" . $id . "'>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='" . $labelClass . "' data-label_name='" . $labelName . "' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}
}
