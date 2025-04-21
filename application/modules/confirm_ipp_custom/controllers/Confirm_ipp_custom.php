<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Confirm_ipp_custom extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Confirm_IPP_Custom.View';
    protected $addPermission  	= 'Confirm_IPP_Custom.Add';
    protected $managePermission = 'Confirm_IPP_Custom.Manage';
    protected $deletePermission = 'Confirm_IPP_Custom.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array('Mpdf'));
        $this->load->model(array(
			'Confirm_ipp_custom/confirm_ipp_custom_model',
			'Bom_hi_grid_custom/bom_hi_grid_custom_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    //========================================================BOM

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
    //   $this->template->page_icon('fa fa-users');

      history("View index ipp custom");

      $this->template->title('IPP Custom/Assembly');
      $this->template->render('index');
    }

    public function get_json_ipp(){
      $this->confirm_ipp_custom_model->get_json_ipp();
    }

    public function add(){
		if($this->input->post()){
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			// print_r($data);
			// exit;
			$session 	= $this->session->userdata('app_session');
			$Ym			= date('y');
			$id    				= $data['id'];
			$no_ipp    			= $data['no_ipp'];
			$id_customer    	= $data['id_customer'];
			$project    		= $data['project'];
			$referensi    		= $data['referensi'];
			$id_top    			= $data['id_top'];
			$keterangan    		= $data['keterangan'];
			$delivery_type    	= $data['delivery_type'];
			$id_country    		= $data['id_country'];
			$delivery_category	= $data['delivery_category'];
			$area_destinasi    	= $data['area_destinasi'];
			$delivery_address   = $data['delivery_address'];
			$shipping_method    = $data['shipping_method'];
			$packing    		= $data['packing'];
			$guarantee    		= $data['guarantee'];
			$delivery_date    	= (!empty($data['delivery_date']))?date('Y-m-d',strtotime($data['delivery_date'])):NULL;
			$instalasi_option   = $data['instalasi_option'];

			$created_by   = 'updated_by';
			$created_date = 'updated_date';
			$tanda        = 'Insert ';

			if(empty($id)){
				//pengurutan kode
				$srcMtr			= "SELECT MAX(no_ipp) as maxP FROM custom_ipp WHERE no_ipp LIKE 'IPP_CA".$Ym."%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 11, 4);
				$urutan2++;
				$urut2			= sprintf('%04s',$urutan2);
				$no_ipp	      	= "IPP_CA".$Ym.$urut2;

				$created_by   = 'created_by';
				$created_date = 'created_date';
				$tanda        = 'Update ';

				$rev = 0;
			}
			else{
				$header   	= $this->db->get_where('custom_ipp',array('id' => $id))->result();
				$rev		= $header[0]->rev + 1;
			}

			$ArrHeader		= array(
				'no_ipp'			=> $no_ipp,
				'id_customer'		=> $id_customer,
				'project'			=> $project,
				'referensi'			=> $referensi,
				'id_top'			=> $id_top,
				'keterangan'		=> $keterangan,
				'delivery_type'		=> $delivery_type,
				'id_country'		=> $id_country,
				'delivery_category'	=> $delivery_category,
				'area_destinasi'	=> $area_destinasi,
				'delivery_address'	=> $delivery_address,
				'shipping_method'	=> $shipping_method,
				'packing'			=> $packing,
				'guarantee'			=> $guarantee,
				'delivery_date'		=> $delivery_date,
				'instalasi_option'	=> $instalasi_option,
				'rev'				=> $rev,
				$created_by	    	=> $session['id_user'],
				$created_date	  	=> date('Y-m-d H:i:s')
			);

			
			$ArrDetail	= array();
			$ArrDetailProduct	= array();
			$ArrDetailAcc	= array();
			$ArrDetailJadi	= array();
			$ArrDetailSheet	= array();
			$ArrDetailEnd	= array();
			if(!empty($data['Detail'])){
				$nomor = 0;
				foreach($data['Detail'] AS $val => $valx){ $nomor++;
					$ArrDetail[$val]['no_ipp'] 			= $no_ipp;
					$ArrDetail[$val]['no_ipp_code'] 	= $no_ipp.'-'.$nomor;
					$ArrDetail[$val]['platform'] 		= (!empty($valx['platform']))?$valx['platform']:'N';
					$ArrDetail[$val]['cover_drainage'] 	= (!empty($valx['cover_drainage']))?$valx['cover_drainage']:'N';
					$ArrDetail[$val]['facade'] 			= (!empty($valx['facade']))?$valx['facade']:'N';
					$ArrDetail[$val]['ceilling'] 		= (!empty($valx['ceilling']))?$valx['ceilling']:'N';
					$ArrDetail[$val]['partition'] 		= (!empty($valx['partition']))?$valx['partition']:'N';
					$ArrDetail[$val]['fence'] 			= (!empty($valx['fence']))?$valx['fence']:'N';
					$ArrDetail[$val]['app_others'] 		= $valx['app_others'];


					$ArrDetail[$val]['color_dark_green'] 	= (!empty($valx['color_dark_green']))?$valx['color_dark_green']:'N';
					$ArrDetail[$val]['color_dark_grey'] 	= (!empty($valx['color_dark_grey']))?$valx['color_dark_grey']:'N';
					$ArrDetail[$val]['color_light_grey'] 	= (!empty($valx['color_light_grey']))?$valx['color_light_grey']:'N';
					$ArrDetail[$val]['color_yellow'] 		= (!empty($valx['color_yellow']))?$valx['color_yellow']:'N';
					$ArrDetail[$val]['color'] 				= $valx['color'];

					$ArrDetail[$val]['food_grade'] 			= (!empty($valx['food_grade']))?$valx['food_grade']:'N';
					$ArrDetail[$val]['uv'] 					= (!empty($valx['uv']))?$valx['uv']:'N';
					$ArrDetail[$val]['fire_reterdant'] 		= (!empty($valx['fire_reterdant']))?$valx['fire_reterdant']:'N';
					$ArrDetail[$val]['industrial_type'] 	= (!empty($valx['industrial_type']))?$valx['industrial_type']:'N';
					$ArrDetail[$val]['commercial_type'] 	= (!empty($valx['commercial_type']))?$valx['commercial_type']:'N';
					$ArrDetail[$val]['superior_type'] 		= (!empty($valx['superior_type']))?$valx['superior_type']:'N';

					$ArrDetail[$val]['standard_astm'] 		= (!empty($valx['standard_astm']))?$valx['standard_astm']:'N';
					$ArrDetail[$val]['standard_bs'] 		= (!empty($valx['standard_bs']))?$valx['standard_bs']:'N';
					$ArrDetail[$val]['standard_dnv'] 		= (!empty($valx['standard_dnv']))?$valx['standard_dnv']:'N';
					$ArrDetail[$val]['file_pendukung_1'] 	= $valx['file_pendukung_1'];
					$ArrDetail[$val]['file_pendukung_2'] 	= $valx['file_pendukung_2'];

					$ArrDetail[$val]['surface_concave'] 		= (!empty($valx['surface_concave']))?$valx['surface_concave']:'N';
					$ArrDetail[$val]['surface_flat'] 			= (!empty($valx['surface_flat']))?$valx['surface_flat']:'N';
					$ArrDetail[$val]['surface_chequered_plate'] = (!empty($valx['surface_chequered_plate']))?$valx['surface_chequered_plate']:'N';
					$ArrDetail[$val]['surface_anti_skid'] 		= (!empty($valx['surface_anti_skid']))?$valx['surface_anti_skid']:'N';
					// $ArrDetail[$val]['surface_custom'] 			= $valx['surface_custom'];

					$ArrDetail[$val]['mesh_open'] 				= (!empty($valx['mesh_open']))?$valx['mesh_open']:'N';
					$ArrDetail[$val]['mesh_closed'] 			= (!empty($valx['mesh_closed']))?$valx['mesh_closed']:'N';

					$ArrDetail[$val]['type_product'] 	= $valx['type_product'];
					$ArrDetail[$val]['product_name'] 	= $valx['product_name'];
					$ArrDetail[$val]['accessories'] 	= $valx['accessories'];

					if(!empty($_FILES['photo_'.$val]["tmp_name"])){
						$target_dir     = "assets/files/";
						$target_dir_u   = get_root3()."/assets/files/";
						$name_file      = 'ipp-'.$val."-".$no_ipp.'-'.$nomor.'-'.date('Ymdhis');
						$target_file    = $target_dir . basename($_FILES['photo_'.$val]["name"]);
						$name_file_ori  = basename($_FILES['photo_'.$val]["name"]);
						$imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); 
						$nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
						
						// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){
						
						  $terupload = move_uploaded_file($_FILES['photo_'.$val]["tmp_name"], $nama_upload);
						  $link_url    	= $target_dir.$name_file.".".$imageFileType;
						
						  $ArrDetail[$val]['file_dokumen'] 		= $link_url;
						// }
					}

					if(!empty($valx['ukuran_jadi'])){
						foreach ($valx['ukuran_jadi'] as $key => $value) {
							$UNIQ = $val.'-'.$key;
							$ArrDetailJadi[$UNIQ]['category'] = 'ukuran jadi';
							$ArrDetailJadi[$UNIQ]['no_ipp'] = $no_ipp;
							$ArrDetailJadi[$UNIQ]['no_ipp_code'] = $no_ipp.'-'.$nomor;
							$ArrDetailJadi[$UNIQ]['length'] 	= str_replace(',','',$value['length']);
							$ArrDetailJadi[$UNIQ]['width'] 	= str_replace(',','',$value['width']);
							$ArrDetailJadi[$UNIQ]['order'] 	= str_replace(',','',$value['order']);
						}
					}
				}
			}

			// print_r($ArrHeader);
			// print_r($ArrDetail);
			// exit;

			$this->db->trans_start();
				if(empty($id)){
					$this->db->insert('custom_ipp', $ArrHeader);
				}
				if(!empty($id)){
					$this->db->where('id', $id);
					$this->db->update('custom_ipp', $ArrHeader);
				}

				$this->db->where('no_ipp', $no_ipp);
				$this->db->delete('custom_ipp_detail');

				$this->db->where('no_ipp', $no_ipp);
				$this->db->delete('custom_ipp_detail_lainnya');

				if(!empty($ArrDetail)){
					$this->db->insert_batch('custom_ipp_detail', $ArrDetail);
				}

				if(!empty($ArrDetailJadi)){
					$this->db->insert_batch('custom_ipp_detail_lainnya', $ArrDetailJadi);
				}
			$this->db->trans_complete();

			if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
				$Arr_Data	= array(
				'pesan'		=>'Save gagal disimpan ...',
				'status'	=> 0
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Data	= array(
				'pesan'		=>'Save berhasil disimpan. Thanks ...',
				'status'	=> 1
				);
				history($tanda." supplier ".$no_ipp);
			}

			echo json_encode($Arr_Data);
		}
		else{
			$id 			= $this->uri->segment(3);
			$tanda 			= $this->uri->segment(4);
			$header   		= $this->db->get_where('custom_ipp',array('id' => $id))->result();
			$detail = [];
			if(!empty($header)){
				$no_ipp 		= (!empty($header[0]->no_ipp))?$header[0]->no_ipp:0;
				$detail   		= $this->db->get_where('custom_ipp_detail',array('no_ipp' => $no_ipp))->result_array();
			}
			$customer   	= $this->db->order_by('nm_customer','asc')->get_where('customer',array('deleted_date'=>NULL))->result_array();
			$deliv_category = $this->db->order_by('urut','asc')->get_where('list',array('menu'=>'delivery rate','category'=>'category'))->result_array();
			$top			= $this->db->order_by('id','asc')->get_where('list_help',array('group_by'=>'top invoice'))->result_array();
			$shipping		= $this->db->order_by('urut','asc')->get_where('list',array('menu'=>'delivery rate','category'=>'method'))->result_array();
			$packing		= $this->db->order_by('urut','asc')->get_where('list',array('menu'=>'ipp','category'=>'packing type'))->result_array();
			$country 		= $this->db->order_by('a.name','asc')->get('country_all a')->result_array();

			$list_bom_topping = $this->db
							->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
							->order_by('a.id_product','asc')
							->join('new_inventory_4 b','a.id_product=b.code_lv4','left')
							->join('new_inventory_3 c','a.id_product=c.code_lv3','left')
							->get_where('bom_header a',array('a.deleted_date'=>NULL,'a.category'=>'topping'))->result_array();
			// print_r($detail);
			// exit;
				$data = [
					'header' => $header,
					'detail' => $detail,
					'customer' => $customer,
					'top' => $top,
					'country' => $country,
					'deliv_category' => $deliv_category,
					'shipping' => $shipping,
					'packing_list' => $packing,
					'list_bom_topping' => $list_bom_topping,
					'tanda' => $tanda,
					'product_lv1' => get_list_inventory_lv1('product'),
				];

				$explodeURL = explode('/',base_url());

			$this->template->title('Add IPP Custom/Assembly');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add',$data);
		}
    }

	public function get_add(){
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$product_lv1 = get_list_inventory_lv1('product');
		$list_bom_topping = $this->db
							->select('a.*, b.nama AS nama_lv4, c.nama AS nama_lv3')
							->order_by('a.id_product','asc')
							->join('new_inventory_4 b','a.id_product=b.code_lv4','left')
							->join('new_inventory_3 c','a.id_product=c.code_lv3','left')
							->get_where('bom_header a',array('a.deleted_date'=>NULL,'a.category'=>'topping'))->result_array();

		$d_Header = "";
		$d_Header .= "<div id='header_".$id."'>";
		$d_Header .= "<h4 class='text-bold text-primary'>Permintaan ".$id."&nbsp;&nbsp;<span class='text-red text-bold delPart' data-id='".$id."' style='cursor:pointer;' title='Delete Part'>Delete</span></h4>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<label>Aplikasi Kebutuhan</label>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][platform]' value='Y'>Platform</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][cover_drainage]' value='Y'>Cover Drainage</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][facade]' value='Y'>Facade</label></div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][ceilling]' value='Y'>Ceilling</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][partition]' value='Y'>Partition</label></div>";
		$d_Header .= "<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][fence]' value='Y'>Fence</label></div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='col-md-2'>";
		$d_Header .= "<div class='form-group'><label>Other</label>";
		$d_Header .= "<input type='text' name='Detail[".$id."][app_others]' class='form-control input-md' placeholder='Other' value=''>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		$d_Header .= "</div>";
		
		$d_Header .= "<hr>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Type Product</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-3'>";
		$d_Header .= "		<select name='Detail[".$id."][type_product]' class='form-control'>";
							foreach ($product_lv1 as $key => $value) {
								$d_Header .= "<option value='".$value['code_lv1']."'>".$value['nama']."</option>";
							}
		$d_Header .= "		</select>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Product Name</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-6'>";
		$d_Header .= "		<input type='text' name='Detail[".$id."][product_name]' class='form-control input-md' placeholder='Product Name' value=''>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		$d_Header .= "<hr>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Additional Spesification</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Additional</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][food_grade]' value='Y'>Food Grade</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][uv]' value='Y'>UV</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][fire_reterdant]' value='Y'>Fire Reterdant</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label></label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][industrial_type]' value='Y'>Industrial Type</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][commercial_type]' value='Y'>Commercial Type</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][superior_type]' value='Y'>Superior Type</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Standard Spec</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][standard_astm]' value='Y'>ASTM</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][standard_bs]' value='Y'>BS</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][standard_dnv]' value='Y'>GNV-GL</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-4'>";
		$d_Header .= "		<div class='form-group'><label>Dokumen Pendukung</label>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[".$id."][file_pendukung_1]' placeholder='Dokumen Pendukung 1' style='margin-bottom:5px;'>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[".$id."][file_pendukung_2]' placeholder='Dokumen Pendukung 2' style='margin-bottom:5px;'>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label></label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Color</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][color_dark_green]' value='Y'>Dark Green</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][color_dark_grey]' value='Y'>Dark Grey</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label></label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][color_light_grey]' value='Y'>Light Grey</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][color_yellow]' value='Y'>Yellow</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Color Other</label>";
		$d_Header .= "		<input type='text' class='form-control' name='Detail[".$id."][color]' placeholder='Color Other'>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		$d_Header .= "<div class='form-group row' hidden>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label></label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label>Surface</label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][surface_concave]' value='Y'>Concave</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][surface_flat]' value='Y'>Flat</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'><label></label>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][surface_anti_skid]' value='Y'>Anti Skid</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][surface_chequered_plate]' value='Y'>Chequered Plate</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";
		
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Drawing Customer</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-5'><input type='file' name='photo_".$id."' id='photo_".$id."' ></div>";
		$d_Header .= "</div>";

		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Accessories</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-6'>";
		$d_Header .= "		<input type='text' name='Detail[".$id."][accessories]' class='form-control input-md' placeholder='Accessories' value=''>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		$d_Header .= "<div class='form-group row' hidden>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Mesh</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<div class='form-group'>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][mesh_open]' value='Y'>Open Mesh</label></div>";
		$d_Header .= "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$id."][mesh_closed]' value='Y'>Closed Mesh</label></div>";
		$d_Header .= "		</div>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		//ukuran jadi
		$d_Header .= "<div class='form-group row'>";
		$d_Header .= "	<div class='col-md-2'>";
		$d_Header .= "		<label>Ukuran Jadi</label>";
		$d_Header .= "	</div>";
		$d_Header .= "	<div class='col-md-5'>";
		$d_Header .= "	<table class='table table-striped table-bordered table-hover table-condensed'>";
		$d_Header .= "		<tr class='bg-blue'>";
		$d_Header .= "			<th class='text-center' width='30%'>Length</th>";
		$d_Header .= "			<th class='text-center' width='30%'>Width</th>";
		$d_Header .= "			<th class='text-center' width='30%'>Qty</th>";
		$d_Header .= "			<th class='text-center' width='10%'>#</th>";
		$d_Header .= "		</tr>";
		$new_number = 0;
		$d_Header .= "		<tr id='addjadi_".$id."_".$new_number."'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPartUkj' title='Add Ukuran Jadi'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";
		$d_Header .= "	</table>";
		$d_Header .= "	</div>";
		$d_Header .= "</div>";

		//penutup div delete
		$d_Header .= "<hr>";
		$d_Header .= "</div>";
		//add part
		$d_Header .= "<div id='add_".$id."'><button type='button' class='btn btn-sm btn-primary addPart' title='Add Permintaan'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Permintaan</button></td></div>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}

	public function get_add_ukuran(){
		$post 			= $this->input->post();

		$id_head 		= $this->uri->segment(3);
		$id 			= $this->uri->segment(4);
		$NameSave 		= $post['NameSave'];
		$LabelAdd 		= $post['LabelAdd'];
		$LabelClass 	= $post['LabelClass'];
		$idClass 		= $post['idClass'];

		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr id='header".$idClass."_".$id_head."_".$id."'>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "<input type='text' name='Detail[".$id_head."][".$NameSave."][".$id."][length]' class='form-control input-md text-center autoNumeric4'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "<input type='text' name='Detail[".$id_head."][".$NameSave."][".$id."][width]' class='form-control input-md text-center autoNumeric4'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='left'>";
					$d_Header .= "<input type='text' name='Detail[".$id_head."][".$NameSave."][".$id."][order]' class='form-control input-md text-center autoNumeric0'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='center'>";
					$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart".$LabelClass."' title='Delete'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

		//add part
		$d_Header .= "		<tr id='add".$idClass."_".$id_head."_".$id."'>";
		$d_Header .= "			<td><button type='button' class='btn btn-sm btn-warning addPart".$LabelClass."' title='Add ".$LabelAdd."'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add ".$LabelAdd."</button></td>";
		$d_Header .= "			<td></td>";
		$d_Header .= "		</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}

	public function confirm(){
        $data 		= $this->input->post();
        $session 	= $this->session->userdata('app_session');
        $id  				= $data['id'];
        $ajukan_sts  		= $data['ajukan_sts'];
        $ajukan_sts_reason  = $data['ajukan_sts_reason'];

        $ArrHeader		= array(
          'ajukan_sts'	  	=> $ajukan_sts,
          'ajukan_sts_reason'	  	=> $ajukan_sts_reason,
          'ajukan_sts_by'	=> $session['id_user'],
          'ajukan_sts_date'	=> date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
            $this->db->where('id', $id);
            $this->db->update('custom_ipp', $ArrHeader);
        $this->db->trans_complete();

        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $Arr_Data	= array(
            'pesan'		=>'Save gagal diproses !',
            'status'	=> 0
          );
        }
        else{
          $this->db->trans_commit();
          $Arr_Data	= array(
            'pesan'		=>'Save berhasil diproses !',
            'status'	=> 1
          );
          history("Konfirmasi custom ipp: ".$id);
        }

        echo json_encode($Arr_Data);

    }

	//bom
	public function add_bom(){
		if($this->input->post()){
		  $Arr_Kembali	= array();
			$data			= $this->input->post();
		  // print_r($data);
		  // exit;
		  $session 		 = $this->session->userdata('app_session');
		  $Ym					  = date('ym');
		  $id        = $data['id'];
		  $no_bom        = $data['no_bom'];
		  $id_ipp        = $data['id_ipp'];
		  $no_bomx        = $data['no_bom'];

		  if(!empty($data['Detail'])){
			  $Detail	= $data['Detail'];
		  }

		  if(!empty($data['DetailAdt'])){
			  $DetailAdt	= $data['DetailAdt'];
		  }

		  if(!empty($data['DetailTop'])){
			  $DetailTop	= $data['DetailTop'];
		  }

		  if(!empty($data['DetailHiGrid'])){
			  $DetailHiGrid	= $data['DetailHiGrid'];
		  }

		  if(!empty($data['DetailAcc'])){
			  $DetailAcc	= $data['DetailAcc'];
		  }

		  if(!empty($data['DetailMatJoint'])){
			  $DetailMatJoint	= $data['DetailMatJoint'];
		  }

		  if(!empty($data['DetailFlat'])){
			  $DetailFlat	= $data['DetailFlat'];
		  }

		  if(!empty($data['DetailEnd'])){
			  $DetailEnd	= $data['DetailEnd'];
		  }

		  if(!empty($data['DetailJadi'])){
			  $DetailJadi	= $data['DetailJadi'];
		  }

		  if(!empty($data['DetailOthers'])){
			$DetailOthers	= $data['DetailOthers'];
		}

		  $created_by   = 'updated_by';
		  $created_date = 'updated_date';
		  $tanda        = 'Insert ';
			if(empty($no_bomx)){
			  //pengurutan kode
			  $srcMtr			  = "SELECT MAX(no_bom) as maxP FROM bom_header WHERE no_bom LIKE 'BOC".$Ym."%' ";
			  $numrowMtr		= $this->db->query($srcMtr)->num_rows();
			  $resultMtr		= $this->db->query($srcMtr)->result_array();
			  $angkaUrut2		= $resultMtr[0]['maxP'];
			  $urutan2		  = (int)substr($angkaUrut2, 7, 3);
			  $urutan2++;
			  $urut2			  = sprintf('%03s',$urutan2);
			  $no_bom	      = "BOC".$Ym.$urut2;

			  $created_by   = 'created_by';
			  $created_date = 'created_date';
			  $tanda        = 'Update ';
			}

		  $ArrHeader2		= array(
			  'no_bom'			=> $no_bom,
			  'id_ipp'			=> $id_ipp,
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
		  if(!empty($_FILES['photo']["tmp_name"])){
			$target_dir     = "assets/files/";
			$target_dir_u   = get_root3()."/assets/files/";
			$name_file      = 'bom-ukuran-jadi-'.$no_bom."-".date('Ymdhis');
			$target_file    = $target_dir . basename($_FILES['photo']["name"]);
			$name_file_ori  = basename($_FILES['photo']["name"]);
			$imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); 
			$nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
			
			// if($imageFileType == 'pdf' OR $imageFileType == 'jpeg' OR $imageFileType == 'jpg'){
			
			  $terupload = move_uploaded_file($_FILES['photo']["tmp_name"], $nama_upload);
			  $link_url    	= $target_dir.$name_file.".".$imageFileType;
  
			  $dataProcess2	= array('file_upload' => $link_url);
			// }
		  }

		  $ArrHeader = array_merge($ArrHeader2,$dataProcess2);

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
		  $ArrDetail81	= array();
		  $ArrDetail82	= array();
		  $ArrDetail83	= array();
		  $ArrDetail9	= array();
			$ArrDetail91 = array();
		  if(!empty($data['Detail'])){
			  foreach($Detail AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail[$val]['no_bom'] 			= $no_bom;
				  $ArrDetail[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail[$val]['code_material'] 	= $valx['code_material'];
				  $ArrDetail[$val]['weight'] 	 		= str_replace(',','',$valx['weight']);
			  }
		  }

		  if(!empty($data['DetailAdt'])){
			  $GET_ADDITIVE = get_persen_additive();
			  foreach($DetailAdt AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail2[$val]['category'] 		= 'additive';
				  $ArrDetail2[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail2[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail2[$val]['code_material'] 	= $valx['code_material'];

				  if(!empty($valx['detail'])){
					  foreach ($valx['detail'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2;
						  $key2 = $valx['code_material'].'-'.$valx2['code_material'];
						  $persen = (!empty($GET_ADDITIVE[$key2]['persen']))?$GET_ADDITIVE[$key2]['persen']:0;

						  $ArrDetail21[$key]['category'] 		= 'additive';
						  $ArrDetail21[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail21[$key]['no_bom_detail'] = $no_bom."-".$urut;
						  $ArrDetail21[$key]['code_material'] = $valx2['code_material'];
						  $ArrDetail21[$key]['nm_material'] 	= $valx2['nm_material'];
						  $ArrDetail21[$key]['weight'] 		= str_replace(',','',$valx2['berat']);
						  $ArrDetail21[$key]['persen'] 		= $persen;
					  }
				  }

			  }
		  }

		  if(!empty($data['DetailHiGrid'])){
			  foreach($DetailHiGrid AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail8[$val]['category'] 		= 'hi grid std';
				  $ArrDetail8[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail8[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail8[$val]['code_material'] 	= $valx['code_material'];
				  $ArrDetail8[$val]['ket'] 			= $valx['ket'];
				  $ArrDetail8[$val]['qty'] 			= str_replace(',','',$valx['qty']);
				  $ArrDetail8[$val]['unit'] 			= $valx['unit'];

				  if(!empty($valx['detail'])){
					  foreach ($valx['detail'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2;
						  $ArrDetail81[$key]['category'] 		= 'hi grid std';
						  $ArrDetail81[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail81[$key]['no_bom_detail'] = $no_bom."-".$urut;
						  $ArrDetail81[$key]['code_material'] = $valx2['code_material'];
						  $ArrDetail81[$key]['nm_material'] 	= $valx2['nm_material'];
						  $ArrDetail81[$key]['weight'] 		= str_replace(',','',$valx2['berat']);
					  }
				  }
				  if(!empty($valx['ukuran_jadi'])){
					  foreach ($valx['ukuran_jadi'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2;
						  $ArrDetail82[$key]['category'] 		= 'ukuran jadi';
						  $ArrDetail82[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail82[$key]['no_bom_detail'] = $no_bom."-".$urut;
						  $ArrDetail82[$key]['length'] 		= str_replace(',','',$valx2['length']);
						  $ArrDetail82[$key]['width'] 		= str_replace(',','',$valx2['width']);
						  $ArrDetail82[$key]['qty'] 			= str_replace(',','',$valx2['qty']);
					  }
				  }
				  if(!empty($valx['cutting'])){
					  foreach ($valx['cutting'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2.'-9999';
						  $ArrDetail83[$key]['category'] 		= 'material cutting';
						  $ArrDetail83[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail83[$key]['no_bom_detail'] = $no_bom."-".$urut;
						  $ArrDetail83[$key]['code_material']	= $valx2['id_material'];
						  $ArrDetail83[$key]['weight'] 		= str_replace(',','',$valx2['weight']);
					  }
				  }

			  }
		  }

		  if(!empty($data['DetailTop'])){
			  foreach($DetailTop AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail3[$val]['category'] 		= 'topping';
				  $ArrDetail3[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail3[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail3[$val]['code_material'] 	= $valx['code_material'];
				  $ArrDetail3[$val]['ket'] 			= $valx['ket'];
				  $ArrDetail3[$val]['qty'] 			= str_replace(',','',$valx['qty']);
				  $ArrDetail3[$val]['unit'] 			= $valx['unit'];

				  if(!empty($valx['detail'])){
					  foreach ($valx['detail'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2;
						  $ArrDetail31[$key]['category'] 		= 'topping';
						  $ArrDetail31[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail31[$key]['no_bom_detail'] 	= $no_bom."-".$urut;
						  $ArrDetail31[$key]['code_material'] 	= $valx2['code_material'];
						  $ArrDetail31[$key]['nm_material'] 	= $valx2['nm_material'];
						  $ArrDetail31[$key]['weight'] 		= str_replace(',','',$valx2['berat']);
					  }
				  }

			  }
		  }

		  if(!empty($data['DetailAcc'])){
			  foreach($DetailAcc AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail4[$val]['category'] 		= 'accessories';
				  $ArrDetail4[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail4[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail4[$val]['code_material'] 	= $valx['code_material'];
				  $ArrDetail4[$val]['ket'] 			= $valx['ket'];
				  $ArrDetail4[$val]['weight'] 	 	= str_replace(',','',$valx['weight']);
			  }
		  }

		  if(!empty($data['DetailMatJoint'])){
			  foreach($DetailMatJoint AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail4MatLayer[$val]['category'] 		= 'mat joint';
				  $ArrDetail4MatLayer[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail4MatLayer[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail4MatLayer[$val]['code_material'] 	= $valx['code_material'];
				  $ArrDetail4MatLayer[$val]['ket'] 			= $valx['ket'];
				  $ArrDetail4MatLayer[$val]['layer'] 			= $valx['layer'];
				  $ArrDetail4MatLayer[$val]['weight'] 	 	= str_replace(',','',$valx['weight']);
			  }
		  }

		  if(!empty($data['DetailFlat'])){
			  foreach($DetailFlat AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail5[$val]['category'] 		= 'flat sheet';
				  $ArrDetail5[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail5[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail5[$val]['length'] 	 	= str_replace(',','',$valx['length']);
				  $ArrDetail5[$val]['width'] 	 		= str_replace(',','',$valx['width']);
				  $ArrDetail5[$val]['qty'] 	 		= str_replace(',','',$valx['qty']);
				  $ArrDetail5[$val]['m2'] 	 		= str_replace(',','',$valx['m2']);

				  if(!empty($valx['material'])){
					  foreach ($valx['material'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2;
						  $ArrDetail51[$key]['category'] 		= 'material flat sheet';
						  $ArrDetail51[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail51[$key]['no_bom_detail'] = $no_bom."-".$urut;
						  $ArrDetail51[$key]['code_material']	= $valx2['id_material'];
						  $ArrDetail51[$key]['weight'] 		= str_replace(',','',$valx2['weight']);
					  }
				  }
			  }
		  }

		  if(!empty($data['DetailEnd'])){
			  foreach($DetailEnd AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail6[$val]['category'] 		= 'end plate';
				  $ArrDetail6[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail6[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail6[$val]['length'] 	 	= str_replace(',','',$valx['length']);
				  $ArrDetail6[$val]['width'] 	 		= str_replace(',','',$valx['width']);
				  $ArrDetail6[$val]['qty'] 	 		= str_replace(',','',$valx['qty']);
				  $ArrDetail6[$val]['m2'] 	 		= str_replace(',','',$valx['m2']);

				  if(!empty($valx['material'])){
					  foreach ($valx['material'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2;
						  $ArrDetail61[$key]['category'] 		= 'material end plate';
						  $ArrDetail61[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail61[$key]['no_bom_detail'] = $no_bom."-".$urut;
						  $ArrDetail61[$key]['code_material']	= $valx2['id_material'];
						  $ArrDetail61[$key]['weight'] 		= str_replace(',','',$valx2['weight']);
					  }
				  }
			  }
		  }

		  if(!empty($data['DetailJadi'])){
			  foreach($DetailJadi AS $val => $valx){
				  $urut = sprintf('%03s',$val);
				  $ArrDetail7[$val]['category'] 		= 'ukuran jadi';
				  $ArrDetail7[$val]['no_bom'] 		= $no_bom;
				  $ArrDetail7[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				  $ArrDetail7[$val]['length'] 	 	= str_replace(',','',$valx['length']);
				  $ArrDetail7[$val]['width'] 	 		= str_replace(',','',$valx['width']);
				  $ArrDetail7[$val]['qty'] 	 		= str_replace(',','',$valx['qty']);
				  $ArrDetail7[$val]['m2'] 	 		= str_replace(',','',$valx['m2']);

				  if(!empty($valx['material'])){
					  foreach ($valx['material'] as $val2 => $valx2) {
						  $key = $val.'-'.$val2;
						  $ArrDetail71[$key]['category'] 		= 'material ukuran jadi';
						  $ArrDetail71[$key]['no_bom'] 		= $no_bom;
						  $ArrDetail71[$key]['no_bom_detail'] = $no_bom."-".$urut;
						  $ArrDetail71[$key]['code_material']	= $valx2['id_material'];
						  $ArrDetail71[$key]['weight'] 		= str_replace(',','',$valx2['weight']);
					  }
				  }
			  }
		  }

		  if(!empty($data['DetailOthers'])){
			foreach($DetailOthers AS $val => $valx){
				$urut = sprintf('%03s',$val);
				$ArrDetail9[$val]['category'] 		= 'others';
				$ArrDetail9[$val]['no_bom'] 		= $no_bom;
				$ArrDetail9[$val]['no_bom_detail'] 	= $no_bom."-".$urut;
				$ArrDetail9[$val]['length'] 	 	= str_replace(',','',$valx['length']);
				$ArrDetail9[$val]['width'] 	 		= str_replace(',','',$valx['width']);
				$ArrDetail9[$val]['qty'] 	 		= str_replace(',','',$valx['qty']);
				$ArrDetail9[$val]['m2'] 	 		= str_replace(',','',$valx['m2']);

				if(!empty($valx['material'])){
					foreach ($valx['material'] as $val2 => $valx2) {
						$key = $val.'-'.$val2;
						$ArrDetail91[$key]['category'] 		= 'material others';
						$ArrDetail91[$key]['no_bom'] 		= $no_bom;
						$ArrDetail91[$key]['no_bom_detail'] = $no_bom."-".$urut;
						$ArrDetail91[$key]['code_material']	= $valx2['id_material'];
						$ArrDetail91[$key]['weight'] 		= str_replace(',','',$valx2['weight']);
					}
				}
			}
		}

		  // print_r($ArrHeader);
		  // print_r($ArrDetail);
		  // exit;

			$this->db->trans_start();
		  if(empty($no_bomx)){
			  $this->db->insert('bom_header', $ArrHeader);
		  }
		  if(!empty($no_bomx)){
			  $this->db->where('no_bom', $no_bom);
			  $this->db->update('bom_header', $ArrHeader);
		  }

		  $this->db->where('id', $id);
			$this->db->update('custom_ipp', array('no_bom'=>$no_bom));

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'default'));
		  if(!empty($ArrDetail)){
			  $this->db->insert_batch('bom_detail', $ArrDetail);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'additive'));
		  if(!empty($ArrDetail2)){
			  $this->db->insert_batch('bom_detail', $ArrDetail2);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'additive'));
		  if(!empty($ArrDetail21)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail21);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'topping'));
		  if(!empty($ArrDetail3)){
			  $this->db->insert_batch('bom_detail', $ArrDetail3);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'topping'));
		  if(!empty($ArrDetail31)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail31);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'hi grid std'));
		  if(!empty($ArrDetail8)){
			  $this->db->insert_batch('bom_detail', $ArrDetail8);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'hi grid std'));
		  if(!empty($ArrDetail81)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail81);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'ukuran jadi'));
		  if(!empty($ArrDetail82)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail82);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'material cutting'));
		  if(!empty($ArrDetail83)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail83);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'accessories'));
		  if(!empty($ArrDetail4)){
			  $this->db->insert_batch('bom_detail', $ArrDetail4);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'mat joint'));
		  if(!empty($ArrDetail4MatLayer)){
			  $this->db->insert_batch('bom_detail', $ArrDetail4MatLayer);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'flat sheet'));
		  if(!empty($ArrDetail5)){
			  $this->db->insert_batch('bom_detail', $ArrDetail5);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'material flat sheet'));
		  if(!empty($ArrDetail51)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail51);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'end plate'));
		  if(!empty($ArrDetail6)){
			  $this->db->insert_batch('bom_detail', $ArrDetail6);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'material end plate'));
		  if(!empty($ArrDetail61)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail61);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'ukuran jadi'));
		  if(!empty($ArrDetail7)){
			  $this->db->insert_batch('bom_detail', $ArrDetail7);
		  }

		  $this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'material ukuran jadi'));
		  if(!empty($ArrDetail71)){
			  $this->db->insert_batch('bom_detail_custom', $ArrDetail71);
		  }

		  $this->db->delete('bom_detail', array('no_bom' => $no_bom,'category' => 'others'));
			if(!empty($ArrDetail9)){
				$this->db->insert_batch('bom_detail', $ArrDetail9);
			}

			$this->db->delete('bom_detail_custom', array('no_bom' => $no_bom,'category' => 'material others'));
			if(!empty($ArrDetail91)){
				$this->db->insert_batch('bom_detail_custom', $ArrDetail91);
			}

			$this->db->trans_complete();

			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=>'Save gagal disimpan ...',
					'status'	=> 0
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=>'Save berhasil disimpan. Thanks ...',
					'status'	=> 1
				);
			  history($tanda." BOM ".$no_bom);
			}
		  echo json_encode($Arr_Data);
		}
		else{
		  $session  = $this->session->userdata('app_session');
		  $id_ipp 	  			= $this->uri->segment(3);
			$getIppCustom = $this->db->get_where('custom_ipp',array('id'=>$id_ipp))->result();

		  $no_bom 	  			= $this->uri->segment(4);
		  $header   			= $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
		  $detail   			= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'default'))->result_array();
		  $detail_hi_grid   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'hi grid std'))->result_array();
		  $detail_additive   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'additive'))->result_array();
		  $detail_topping   	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'topping'))->result_array();
		  $detail_accessories 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'accessories'))->result_array();
		  $detail_mat_joint 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'mat joint'))->result_array();
		  $detail_flat_sheet 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'flat sheet'))->result_array();
		  $detail_end_plate 	= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'end plate'))->result_array();
		  $detail_ukuran_jadi = $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'ukuran jadi'))->result_array();
			$detail_others 		= $this->db->get_where('bom_detail',array('no_bom' => $no_bom, 'category'=>'others'))->result_array();
			$product			= $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'product'));
		  $material			= $this->bom_hi_grid_custom_model->get_data_where_array('new_inventory_4',array('deleted_date'=>NULL,'category'=>'material'));
		  $accessories		= $this->bom_hi_grid_custom_model->get_data_where_array('accessories',array('deleted_date'=>NULL));
		  $bom_additive    	= $this->bom_hi_grid_custom_model->get_data_where_array('bom_header',array('deleted_date'=>NULL,'category'=>'additive'));
		  $bom_topping    	= $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_3 b','a.id_product=b.code_lv3','left')->get_where('bom_header a',array('a.deleted_date'=>NULL,'a.category'=>'topping'))->result();
		  $bom_higridstd1    	= $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b','a.id_product=b.code_lv4','left')->get_where('bom_header a',array('a.deleted_date'=>NULL,'a.category'=>'grid standard'))->result();
		  $bom_higridstd2    	= $this->db->select('a.*, b.nama AS nm_jenis')->join('new_inventory_4 b','a.id_product=b.code_lv4','left')->get_where('bom_header a',array('a.deleted_date'=>NULL,'a.category'=>'standard'))->result();
		  $bom_higridstd 		= array_merge($bom_higridstd1,$bom_higridstd2);
		  $satuan				= $this->bom_hi_grid_custom_model->get_data_where_array('ms_satuan',array('deleted_date'=>NULL,'category'=>'unit'));

		  // print_r($header);
		  // exit;
			$data = [
			  'headerIPP' => $getIppCustom,
			  'id_ipp' => $id_ipp,
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
		  $this->template->render('add_bom',$data);
	  }
  }

}

?>
