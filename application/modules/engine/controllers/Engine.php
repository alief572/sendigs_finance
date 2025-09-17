<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Engine extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Engineering.View';
    protected $addPermission  	= 'Engineering.Add';
    protected $managePermission = 'Engineering.Manage';
    protected $deletePermission = 'Engineering.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array('Mpdf'));
        $this->load->model(array('Engine/engine_model'
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    //========================================================BOM

    public function bom(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->engine_model->get_data('bom_header','deleted','N');
      history("View index BOM");
      $this->template->set('results', $data);
      $this->template->title('Bill Of Materials');
      $this->template->render('bom');
    }

    public function data_side_bom(){
      $this->engine_model->get_json_bom();
    }

    public function add_bom(){
      if($this->input->post()){
        $Arr_Kembali	= array();
    		$data			= $this->input->post();
        // print_r($data);
        // exit;
    		$session 		  = $this->session->userdata('app_session');
        $Detail 	    = $data['Detail'];
        $Ym					  = date('ym');
        $no_bom        = $data['no_bom'];
        $no_bomx        = $data['no_bom'];
        $check_p			  = "SELECT * FROM bom_header WHERE id_product ='".$data['id_product']."' ";
        $num_p		= $this->db->query($check_p)->num_rows();
        // if($num_p < 1){
          $created_by   = 'updated_by';
          $created_date = 'updated_date';
          $tanda        = 'Insert ';
          if(empty($no_bomx)){
            //pengurutan kode
            $srcMtr			  = "SELECT MAX(no_bom) as maxP FROM bom_header WHERE no_bom LIKE 'BOM".$Ym."%' ";
            $numrowMtr		= $this->db->query($srcMtr)->num_rows();
            $resultMtr		= $this->db->query($srcMtr)->result_array();
            $angkaUrut2		= $resultMtr[0]['maxP'];
            $urutan2		  = (int)substr($angkaUrut2, 7, 3);
            $urutan2++;
            $urut2			  = sprintf('%03s',$urutan2);
            $no_bom	      = "BOM".$Ym.$urut2;

            $created_by   = 'created_by';
            $created_date = 'created_date';
            $tanda        = 'Update ';
          }

          $ArrHeader		= array(
            'no_bom'			    => $no_bom,
            'id_product'	    => $data['id_product'],
			'waste_product'	    => str_replace(',','',$data['waste_product']),
			'waste_setting'	    => str_replace(',','',$data['waste_setting']),
            $created_by	    => $session['id_user'],
            $created_date	  => date('Y-m-d H:i:s')
          );

          $ArrDetail	= array();
          $ArrDetail2	= array();
          foreach($Detail AS $val => $valx){
            $urut				= sprintf('%03s',$val);
            $ArrDetail[$val]['no_bom'] 			 = $no_bom;
            $ArrDetail[$val]['no_bom_detail'] = $no_bom."-".$urut;
            $ArrDetail[$val]['code_material'] 		 = $valx['code_material'];
            $ArrDetail[$val]['weight'] 	 = str_replace(',','',$valx['weight']);
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

          if(!empty($ArrDetail)){
            $this->db->delete('bom_detail', array('no_bom' => $no_bom));
      			$this->db->insert_batch('bom_detail', $ArrDetail);
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
        // }
        // else{
        //   $Arr_Data	= array(
        //     'pesan'		=>'Product sudah digunakan .',
        //     'status'	=> 0
        //   );
        // }

    		echo json_encode($Arr_Data);
      }
      else{
      	$session  = $this->session->userdata('app_session');
        $no_bom 	  = $this->uri->segment(3);
    		$header   = $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
        $detail   = $this->db->get_where('bom_detail',array('no_bom' => $no_bom))->result_array();
  			$product    = $this->engine_model->get_data('ms_inventory_category2');
        $material    = $this->engine_model->get_data('ms_material','deleted','N');

        // print_r($header);
        // exit;
  			$data = [
          'header' => $header,
          'detail' => $detail,
          'product' => $product,
          'material' => $material
  			];
  			$this->template->set('results', $data);
        $this->template->title('Add Bill Of Materials');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add_bom',$data);
      }
    }

    public function detail_bom(){
      // $this->auth->restrict($this->viewPermission);
      $no_bom 	= $this->input->post('no_bom');
      $header = $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
      $detail = $this->db->get_where('bom_detail',array('no_bom' => $no_bom))->result_array();
      $product    = $this->engine_model->get_data('ms_inventory_category2');
      // print_r($header);
      $data = [
        'header' => $header,
        'detail' => $detail,
        'product' => $product
      ];
      $this->template->set('results', $data);
      $this->template->render('detail_bom', $data);
    }

    public function get_add(){
  		$id 	= $this->uri->segment(3);
  		$no 	= 0;

      $material    = $this->engine_model->get_data('ms_material','deleted','N');
  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='header_".$id."'>";
  				$d_Header .= "<td align='center'>".$id."</td>";
  				$d_Header .= "<td align='left'>";
          $d_Header .= "<select name='Detail[".$id."][code_material]' class='chosen_select form-control input-sm inline-blockd material'>";
          $d_Header .= "<option value='0'>Select Material Name</option>";
          foreach($material AS $valx){
            $d_Header .= "<option value='".$valx->code_material."'>".strtoupper($valx->nm_material)."</option>";
          }
          $d_Header .= 		"</select>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][weight]' class='form-control input-md maskM qty' placeholder='Weight'>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
  				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
  				$d_Header .= "</td>";
  			$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

    public function hapus(){
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
          history("Delete data BOM ".$no_bom);
        }

        echo json_encode($Arr_Data);

    }

    //========================================================MATERIAL PLANNING

    public function material_planning(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      history("View index Material Planning");
      $this->template->title('Material Planning');
      $this->template->render('material_planning');
    }

    public function data_side_plan(){
      $this->engine_model->get_json_plan();
    }

    public function detail_material_planning(){
      // $this->auth->restrict($this->viewPermission);
      $no_plan 	= $this->input->post('no_plan');

      $data_num = $this->db->query("SELECT * FROM material_planning_data WHERE no_plan='".$no_plan."' GROUP BY material ORDER BY material")->num_rows();
      $data = $this->db->query("SELECT * FROM material_planning_data WHERE no_plan='".$no_plan."' GROUP BY material ORDER BY material")->result_array();

      $product = $this->db->query("SELECT * FROM material_planning_data WHERE no_plan='".$no_plan."' GROUP BY product ORDER BY product")->result_array();

      // print_r($header);
      $data = [
        'data_num' => $data_num,
        'data' => $data,
        'product' => $product
      ];
      $this->template->set('results', $data);
      $this->template->render('detail_material_planning', $data);
    }

    public function add_material_planning(){
      if($this->input->post()){
        $Arr_Kembali	= array();
    		$data			= $this->input->post();

    		$session 		  = $this->session->userdata('app_session');
        $detail 	    = $data['detail'];
        $footer 	    = $data['footer'];
        $Ym					  = date('ym');
        $no_plan      = $data['no_plan'];
        $no_planx     = $data['no_plan'];
        $plan_date    = $data['plan_date'];
        $project      = $data['project'];
        $date         = explode('-', $plan_date);

        $created_by   = 'updated_by';
        $created_date = 'updated_date';
        $tanda        = 'Update ';
        if(empty($no_planx)){

          $Y = substr($date[0],2,2);
          $m = sprintf('%02s',$date[1]);

          //pengurutan kode
      		$srcMtr			= "SELECT MAX(no_plan) as maxP FROM material_planning WHERE no_plan LIKE 'PLM".$Ym."%' ";
      		$numrowMtr		= $this->db->query($srcMtr)->num_rows();
      		$resultMtr		= $this->db->query($srcMtr)->result_array();
      		$angkaUrut2		= $resultMtr[0]['maxP'];
      		$urutan2	 	  = (int)substr($angkaUrut2, 7, 3);
      		$urutan2++;
      		$urut2			= sprintf('%03s',$urutan2);
      		$no_plan			= "PLM".$Ym.$urut2;

          $created_by   = 'created_by';
          $created_date = 'created_date';
          $tanda        = 'Insert ';
        }

        $ArrHeader		= array(
          'no_plan'		=> $no_plan,
          'bulan'	    => $date[1],
          'tahun'	    => $date[0],
          'project'		=> $project,
          $created_by	    => $session['id_user'],
          $created_date	  => date('Y-m-d H:i:s')
        );

        // print_r($data);
        // exit;

        $ArrDetail	= array();
        $ArrDetail2	= array();
        foreach($detail AS $val => $valx){
          $urut				= sprintf('%03s',$val);
          foreach($valx['data'] AS $val2 => $valx2){
            $ArrDetail[$val.$val2]['no_plan'] 	      = $no_plan;
            $ArrDetail[$val.$val2]['no_plan_detail']  = $no_plan."-".$urut;
            $ArrDetail[$val.$val2]['material']        = $valx['material'];
            $ArrDetail[$val.$val2]['weight']       = $valx2['weight'];
            $ArrDetail[$val.$val2]['product']      = (!empty($valx2['product']))?$valx2['product']:'';
            $ArrDetail[$val.$val2]['qty_order']    = (!empty($valx2['qty_order']))?$valx2['qty_order']:'0';
          }
        }

        // print_r($ArrHeader);
    		// print_r($ArrDetail);
    		// exit;

        foreach($footer AS $val => $valx){
          $urut				= sprintf('%03s',$val);
          foreach($valx['sum'] AS $val2 => $valx2){
            $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
            $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
            $ArrDetail2[$val.$val2]['material']       = $valx['material'];
            $ArrDetail2[$val.$val2]['category']       = $valx2['name'];
            $ArrDetail2[$val.$val2]['weight']          = $valx2['value'];
          }
          // foreach($valx['stock'] AS $val3 => $valx3){ $val2++;
          //   $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          //   $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          //   $ArrDetail2[$val.$val2]['material']       = $valx['material'];
          //   $ArrDetail2[$val.$val2]['category']       = $valx3['name'];
          //   $ArrDetail2[$val.$val2]['weight']          = $valx3['value'];
          // }
          // foreach($valx['order'] AS $val4 => $valx4){ $val2++;
          //   $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          //   $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          //   $ArrDetail2[$val.$val2]['material']       = $valx['material'];
          //   $ArrDetail2[$val.$val2]['category']       = $valx4['name'];
          //   $ArrDetail2[$val.$val2]['weight']          = $valx4['value'];
          // }
          // foreach($valx['suggest'] AS $val5 => $valx5){ $val2++;
          //   $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          //   $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          //   $ArrDetail2[$val.$val2]['material']       = $valx['material'];
          //   $ArrDetail2[$val.$val2]['category']       = $valx5['name'];
          //   $ArrDetail2[$val.$val2]['weight']          = $valx5['value'];
          // }
          // foreach($valx['request'] AS $val6 => $valx6){ $val2++;
          //   $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          //   $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          //   $ArrDetail2[$val.$val2]['material']       = $valx['material'];
          //   $ArrDetail2[$val.$val2]['category']       = $valx6['name'];
          //   $ArrDetail2[$val.$val2]['weight']         = str_replace(',','',$valx6['value']);
          // }
        }

        // print_r($ArrHeader);
    		// print_r($ArrDetail);
        // print_r($ArrDetail2);
    		// exit;

    		$this->db->trans_start();
          if(empty($no_planx)){
            $this->db->delete('material_planning', array('no_plan' => $no_plan));
            $this->db->insert('material_planning', $ArrHeader);
          }
          if(!empty($no_planx)){
            $this->db->where('no_plan', $no_plan);
            $this->db->update('material_planning', $ArrHeader);
          }

          if(!empty($ArrDetail)){
            $this->db->delete('material_planning_data', array('no_plan' => $no_plan));
      			$this->db->insert_batch('material_planning_data', $ArrDetail);
          }
          if(!empty($ArrDetail2)){
            $this->db->delete('material_planning_footer', array('no_plan' => $no_plan));
      			$this->db->insert_batch('material_planning_footer', $ArrDetail2);
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
          history($tanda." Material Planning ".$no_plan);
    		}

    		echo json_encode($Arr_Data);
      }
      else{
      	$session  = $this->session->userdata('app_session');
        $no_plan 	  = $this->uri->segment(3);
    		$header   = $this->db->get_where('material_planning',array('no_plan' => $no_plan))->result();
        $plan    = $this->engine_model->get_data('list_date_planning');
        // print_r($header);
        // exit;
  			$data = [
          'header' => $header,
          'plan' => $plan
  			];
  			$this->template->set('results', $data);
        $this->template->title('Add Material Planning');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add_material_planning',$data);
      }
    }

    public function get_planning(){
  		$id 	     = $this->uri->segment(3);
      $kelompok 	  = $this->uri->segment(4);
      $no_plan 	  = $this->uri->segment(5);
  		$no 	= 0;
      $explode = explode("-", $id);
      $tahun = $explode[0];
      $bulan = $explode[1];
      // echo "id: ".$id."<br>";
      // echo "no_plan: ".$no_plan."<br>";
      // echo "kelompok: ".$kelompok."<br>";
      // exit;
      $product = $this->db->query("SELECT a.*, b.nama FROM get_plan_product a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE b.id_category1='".$kelompok."' AND a.tahun='".$tahun."' AND a.bulan='".$bulan."' ")->result_array();
      $dtListArray = array();
			foreach($product AS $val => $valx){
				$dtListArray[$val] = $valx['product'];
			}
			$dtImplode	= "('".implode("','", $dtListArray)."')";
      // $list_mat = "SELECT a.* FROM view_get_plan_header a LEFT JOIN ms_material b ON a.code_material=b.code_material WHERE b.kelompok = '".$kelompok."' AND a.tahun='".$tahun."' AND a.bulan='".$bulan."' AND a.code_material IS NOT NULL AND a.code_material <> '' ";
      // $list_mat = "SELECT a.* FROM view_get_plan_header a WHERE a.tahun='".$tahun."' AND a.bulan='".$bulan."' AND a.code_material IS NOT NULL AND a.code_material <> '' ";
      $list_mat = "SELECT b.code_material FROM bom_header a LEFT JOIN bom_detail b ON a.no_bom=b.no_bom WHERE a.id_product IN ".$dtImplode." AND b.code_material IS NOT NULL AND b.code_material <> '' GROUP BY b.code_material";

      $header = $this->db->query($list_mat)->result_array();
      $header_num = $this->db->query($list_mat)->num_rows();
      // echo $header_num."<br>";
      // echo COUNT($product);
      // exit;
      if($header_num == '0'){
        echo json_encode(array(
   				'pesan'			=> "Material Tidak ditemukan",
          'status'    => 0
   		 ));
       return false;
      }
      // echo $list_mat;
      // print_r($header); exit;
      $d_Header = "<div class='box box-primary'>";
        	$d_Header .= "<div class='box-body'>";
          $d_Header .= "<div class='tableFixHead' style='height:600px;'>";
          $d_Header .= "<table class='table table-bordered table-striped'>";
          $d_Header .= "<thead>";
          $d_Header .= "<tr class='bg-blue thead'>";
            $d_Header .= "<th class='text-center th' style='vertical-align:middle; min-width:300px;'>Product</th>";
            $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='7%'>Total Order</th>";
            $siz = 74/$header_num;
            foreach ($header as $key => $value) { $key++;
                $d_Header .= "<th class='text-left th' style='font-size: 12px; vertical-align:top; min-width:100px;'>".strtoupper(get_name('ms_material','nm_material','code_material',$value['code_material']))."
                              <input type='hidden' name='detail[".$key."][material]' class='form-control text-right input-md' value='".$value['code_material']."'>
                              <input type='hidden' name='footer[".$key."][material]' class='form-control text-right input-md' value='".$value['code_material']."'>
                              </th>";
            }
          $d_Header .= "</tr>";
          $d_Header .= "</thead>";
          $d_Header .= "<tbody>";
            foreach ($product as $key => $value) { $key++;
                $d_Header .= "<tr>";
                $d_Header .= "<td>(".$key.')  '.strtoupper($value['nama'])."</td>";
                $d_Header .= "<td class='text-center'>".$value['qty_propose']."</td>";
                foreach ($header as $key2 => $value2) { $key2++;
                    $q_weight = "SELECT b.weight FROM bom_header a LEFT JOIN bom_detail b ON a.no_bom=b.no_bom WHERE a.id_product ='".$value['product']."' AND b.code_material ='".$value2['code_material']."' LIMIT 1 ";
                    $weight = $this->db->query($q_weight)->result();
                    $nil = (!empty($weight))?$weight[0]->weight:0;
                    $d_Header .= "<td class='text-right'>".number_format($nil * $value['qty_propose'],2)."
                                  <input type='hidden' name='detail[".$key2."][data][".$key."][weight]' class='form-control text-right input-md material_".$key2." maskM' value='".$nil * $value['qty_propose']."'>
                                  <input type='hidden' name='detail[".$key2."][data][".$key."][product]' class='form-control text-right input-md' value='".$value['product']."'>
                                  <input type='hidden' name='detail[".$key2."][data][".$key."][qty_propose]' class='form-control text-right input-md' value='".$value['qty_propose']."'>
                                  </td>";
                }
                $d_Header .= "</tr>";
            }
            $d_Header .= "<tr>";
              $d_Header .= "<td><b>TOTAL KEBUTUHAN</b></td>";
              $d_Header .= "<td></td>";
              foreach ($header as $key2 => $value2) { $key2++;
                $d_Header .= "<td class='text-right'><b><span class='sum_".$key2."'></span></b>
                              <input type='hidden' id='sum_".$key2."' name='footer[".$key2."][sum][".$key2."][value]' class='form-control text-right input-md sum_".$key2." maskM'>
                              <input type='hidden' name='footer[".$key2."][sum][".$key2."][name]' class='form-control text-right input-md' value='sum'>
                              </td>";
              }
            $d_Header .= "</tr>";
            // $d_Header .= "<tr>";
            //   $d_Header .= "<td colspan='2'><b>STOCK</b></td>";
            //   foreach ($header as $key2 => $value2) { $key2++;
            //     $stock = $this->db->query("SELECT begin_balance FROM ms_material WHERE code_material='".$value2['code_material']."' LIMIT 1 ")->result();
            //     $stval = (!empty($stock[0]->begin_balance))?$stock[0]->begin_balance:0;
            //     $d_Header .= "<td class='text-right'><b><span class='stock_".$key2."'>".number_format($stval,2)."</span></b>
            //                   <input type='hidden' id='stock_".$key2."' name='footer[".$key2."][stock][".$key2."][value]' class='form-control text-right input-md stock_".$key2." maskM' value='".$stval."'>
            //                   <input type='hidden' name='footer[".$key2."][stock][".$key2."][name]' class='form-control text-right input-md' value='stock'>
            //                   </td>";
            //   }
            // $d_Header .= "</tr>";
            // $d_Header .= "<tr class='hide_now'>";
            //   $d_Header .= "<td colspan='2'><b>ORDER (3 MONTH)</b></td>";
            //   foreach ($header as $key2 => $value2) { $key2++;
            //     $d_Header .= "<td class='text-right'><b><span class='order_".$key2."'></span></b>
            //                   <input type='hidden' name='footer[".$key2."][order][".$key2."][value]' class='form-control input-md text-right maskM order_".$key2."'>
            //                   <input type='hidden' name='footer[".$key2."][order][".$key2."][name]' class='form-control text-right input-md' value='order'>
            //                   </td>";
            //   }
            // $d_Header .= "</tr>";
            // $d_Header .= "<tr class='hide_now'>";
            //   $d_Header .= "<td colspan='2'><b>SUGGEST REQUEST</b></td>";
            //   foreach ($header as $key2 => $value2) { $key2++;
            //     $d_Header .= "<td class='text-right'><b><span class='suggest_".$key2."'></span></b>
            //                   <input type='hidden' name='footer[".$key2."][suggest][".$key2."][value]' class='form-control input-md text-right maskM suggest_".$key2."'>
            //                   <input type='hidden' name='footer[".$key2."][suggest][".$key2."][name]' class='form-control text-right input-md' value='suggest'>
            //                   </td>";
            //   }
            // $d_Header .= "</tr>";
            // $d_Header .= "<tr class='hide_now'>";
            //   $d_Header .= "<td colspan='2'><b>REQUEST</b><input type='text' readonly style='width: 300px; !important;border-color: transparent; background-color: transparent;'></td>";
            //   foreach ($header as $key2 => $value2) { $key2++;
            //     $query  = "SELECT * FROM material_planning_footer WHERE no_plan='".$no_plan."' AND material='".$value2['code_material']."' AND category='request' LIMIT 1";
            //     $rest_d = $this->db->query($query)->result();
            //     $weight    = (!empty($rest_d[0]->weight))?number_format($rest_d[0]->weight):'';
            //     $d_Header .= "<td class='text-right'>
            //                   <input type='text' name='footer[".$key2."][request][".$key2."][value]' style='min-width: 70px !important;' class='form-control input-md text-right maskM' value='".$weight."'>
            //                   <input type='hidden' name='footer[".$key2."][request][".$key2."][name]' class='form-control text-right input-md' value='request'>
            //                   </td>";
            //   }
            // $d_Header .= "</tr>";
          $d_Header .= "</tbody>";
          $d_Header .= "</table>";
          $d_Header .= "</div>";
          $d_Header .= "</div>";
      $d_Header .= "</div>";


  		 echo json_encode(array(
  				'header'		=> $d_Header,
          'total'     => $header_num,
          'status'    => 1
  		 ));
  	}

    public function print_material_planning(){
  		$id_bq	= $this->uri->segment(3);
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$koneksi		= akses_server();

  		include 'plusPrint.php';
  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

  		history('Print Hasil Sales Order '.$id_bq);

  		PrintMaterialPlanning($Nama_Beda, $koneksi, $session['id_user'], $id_bq);
  	}


    //BOM HEAD TO HEAD
    public function bom_head_to_head(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->engine_model->get_data('bom_header','deleted','N');
      history("View index BOM Head To Head");
      $this->template->set('results', $data);
      $this->template->title('BOM Head To Head');
      $this->template->render('bom_head_to_head');
    }

    public function data_side_bom_hth(){
      $this->engine_model->get_json_bom_hth();
    }

    public function add_bom_head_to_head(){
    	$session = $this->session->userdata('app_session');

      $no_bom = $this->uri->segment(3);
      $header = $this->db->get_where('bom_hth_header',array('kode_bom_hth' => $no_bom))->result();
      $detail_header = $this->db->get_where('bom_hth_detail_header',array('kode_bom_hth' => $no_bom, 'company' => 'f-tackle'))->result_array();
      $detail_header2 = $this->db->get_where('bom_hth_detail_header',array('kode_bom_hth' => $no_bom, 'company' => 'origa'))->result_array();

      $group_material	= $this->db->query("SELECT * FROM group_material WHERE deleted='N' ORDER BY group_material ASC ")->result_array();

      $data = [
        'product' => (!empty($header))?$header[0]->id_product:'',
        'detail_header' => $detail_header,
        'detail_header2' => $detail_header2,
        'no_bom' => $no_bom,
        'group_material' => $group_material
      ];

      $this->template->page_icon('fa fa-edit');
      $this->template->title('Add BOM Head To Head');
      $this->template->render('add_bom_head_to_head', $data);
    }

    public function detail_bom_hth(){
      // $this->auth->restrict($this->viewPermission);
      $no_bom = $this->uri->segment(3);
      $header = $this->db->get_where('bom_hth_header',array('kode_bom_hth' => $no_bom))->result();
      $detail_header = $this->db->get_where('bom_hth_detail_header',array('kode_bom_hth' => $no_bom, 'company' => 'f-tackle'))->result_array();
      $detail_header2 = $this->db->get_where('bom_hth_detail_header',array('kode_bom_hth' => $no_bom, 'company' => 'origa'))->result_array();

      $group_material	= $this->db->query("SELECT * FROM group_material WHERE deleted='N' ORDER BY group_material ASC ")->result_array();

      $data = [
        'product' => (!empty($header))?$header[0]->id_product:'',
        'detail_header' => $detail_header,
        'detail_header2' => $detail_header2,
        'no_bom' => $no_bom,
        'group_material' => $group_material
      ];
      $this->template->set('results', $data);
      $this->template->render('detail_bom_hth', $data);
    }

    public function get_add_bom_selected(){
  		$id 	= $this->uri->segment(3);
      $code 	= $this->uri->segment(4);

  		$group_material	= $this->db->query("SELECT * FROM group_material WHERE deleted='N' ORDER BY group_material ASC ")->result_array();
      // echo $qListResin; exit;
  		$option = "";
          $option .= "<select name='Detail[".$id."][group_material]' data-no='".$id."' id='group_".$id."' class='chosen_select form-control input-sm inline-blockd group_material'>";
          $option .= "<option value='0'>Select Group Material</option>";
          foreach($group_material AS $val => $valx){
            $selx = ($valx['id_group_material'] == $code)?'selected':'';
            $option .= "<option value='".$valx['id_group_material']."' ".$selx.">".strtoupper($valx['group_material'])."</option>";
          }
          $option .= "</select>";

          $option2 = "";
              $option2 .= "<select name='Detail2[".$id."][group_material]' data-no='".$id."' id='group_".$id."' class='chosen_select form-control input-sm inline-blockd group_material'>";
              $option2 .= "<option value='0'>Select Group Material</option>";
              foreach($group_material AS $val => $valx){
                $selx = ($valx['id_group_material'] == $code)?'selected':'';
                $option2 .= "<option value='".$valx['id_group_material']."' ".$selx.">".strtoupper($valx['group_material'])."</option>";
              }
              $option2 .= "</select>";

  		 echo json_encode(array(
  				'option'			=> $option,
          'option2'			=> $option2
  		 ));
  	}

    public function get_add_bom(){
  		$id 	= $this->uri->segment(3);
  		$no 	= 0;

  		$group_material	= $this->db->query("SELECT * FROM group_material WHERE deleted='N' ORDER BY group_material ASC ")->result_array();
      // echo $qListResin; exit;
  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='header_".$id."'>";
  				$d_Header .= "<td align='center' style='vertical-align:middle;'>".$id."</td>";
  				$d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<select name='Detail[".$id."][group_material]' data-no='".$id."' id='group_".$id."' class='chosen_select form-control input-sm inline-blockd group_material'>";
          $d_Header .= "<option value='0'>Select Group Material</option>";
          foreach($group_material AS $val => $valx){
            $d_Header .= "<option value='".$valx['id_group_material']."'>".strtoupper($valx['group_material'])."</option>";
          }
          $d_Header .= "</select>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
  				$d_Header .= "</td>";
  				$d_Header .= "<td align='left'></td>";
  				$d_Header .= "<td align='center' style='vertical-align:middle;'>";
  				$d_Header .= "<button type='button' class='btn btn-sm btn-danger delPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
  				$d_Header .= "</td>";
  			$d_Header .= "</tr>";

  		//add nya
  		$d_Header .= "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addSubPart' data-no='".$id."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail[".$id."][footer][".$no."][total_qty]' class='form-control text-right input-md total_qty_".$id." total_qty' placeholder='Total Qty' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail[".$id."][footer][".$no."][total_total]' class='form-control text-right input-md total_total_".$id." total_total' placeholder='Sub Total' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' data-no='".$id."' title='Add Group'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Group Process</button></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='total_qty' id='sub_qty' class='form-control text-right  input-md' placeholder='Total Qty' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='total_total' id='sub_total' class='form-control text-right  input-md' placeholder='Sub Total' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

    public function get_add_sub_bom(){
  		$id 	= $this->uri->segment(3);
      $no 	= $this->uri->segment(4);
      $product 	= $this->uri->segment(5);

  		$material	= $this->db->query("SELECT
                                      b.code_material,
                                      b.weight,
                                      c.nm_material
                                    FROM
                                      bom_header a
                                      LEFT JOIN bom_detail b ON a.no_bom=b.no_bom
                                      LEFT JOIN ms_material c ON b.code_material=c.code_material
                                    WHERE
                                      a.id_product='".$product."'
                                    ")->result_array();
  		// echo $qListResin; exit;
  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='header_".$id."'>";
  				$d_Header .= "<td align='center'></td>";
  				$d_Header .= "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
          // $d_Header .= "<select name='Detail[".$id."][detail][".$no."][material]' class='chosen_select form-control input-sm inline-blockd material'>";
          // $d_Header .= "<option value='0'>Select Material Name</option>";
          // foreach($material AS $val => $valx){
          //   $d_Header .= "<option value='".$valx['code_material']."'>".strtoupper($valx['nm_material'])."</option>";
          // }
          // $d_Header .= 		"</select>";
          $d_Header .= "<input type='text' name='Detail[".$id."][detail][".$no."][material]' id='material_".$id."_".$no."' class='form-control text-left input-md ' placeholder='Material Name'>";

  				$d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][detail][".$no."][qty]' id='qty_".$id."_".$no."' class='form-control text-right input-md maskM qty_".$id." getTotal' placeholder='Qty'>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][detail][".$no."][price]' id='price_".$id."_".$no."' class='form-control text-right  input-md maskM2 getTotal' placeholder='Price'>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<input type='text' name='Detail[".$id."][detail][".$no."][total]' id='total_".$id."_".$no."' class='form-control text-right  input-md total_".$id."' placeholder='Total' readonly>";
          $d_Header .= "</td>";
  				$d_Header .= "<td align='center' style='vertical-align:middle;'>";
  				$d_Header .= "<button type='button' class='btn btn-sm btn-danger delSubPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
  				$d_Header .= "</td>";
  			$d_Header .= "</tr>";

  		//add nya
  		$d_Header .= "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addSubPart' data-no='".$id."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail[".$id."][footer][".$no."][total_qty]' class='form-control text-right  input-md total_qty_".$id." total_qty' placeholder='Total Qty' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail[".$id."][footer][".$no."][total_total]' class='form-control text-right  input-md total_total_".$id." total_total' placeholder='Sub Total' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}


    public function get_add_bom2(){
  		$id 	= $this->uri->segment(3);
  		$no 	= 0;

  		$group_material	= $this->db->query("SELECT * FROM group_material WHERE deleted='N' ORDER BY group_material ASC ")->result_array();
      // echo $qListResin; exit;
  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='header2_".$id."'>";
  				$d_Header .= "<td align='center' style='vertical-align:middle;'>".$id."</td>";
  				$d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<select name='Detail2[".$id."][group_material]' data-no='".$id."' id='group2_".$id."' class='chosen_select form-control input-sm inline-blockd group_material'>";
          $d_Header .= "<option value='0'>Select Group Material</option>";
          foreach($group_material AS $val => $valx){
            $d_Header .= "<option value='".$valx['id_group_material']."'>".strtoupper($valx['group_material'])."</option>";
          }
          $d_Header .= "</select>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left'>";
  				$d_Header .= "</td>";
  				$d_Header .= "<td align='left'></td>";
  				$d_Header .= "<td align='center' style='vertical-align:middle;'>";
  				$d_Header .= "<button type='button' class='btn btn-sm btn-danger delPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
  				$d_Header .= "</td>";
  			$d_Header .= "</tr>";

  		//add nya
  		$d_Header .= "<tr id='add2_".$id."_".$no."' class='header2_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addSubPart2' data-no='".$id."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail2[".$id."][footer][".$no."][total_qty]' class='form-control text-right input-md total_qty2_".$id." total_qty2' placeholder='Total Qty' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail2[".$id."][footer][".$no."][total_total]' class='form-control text-right  input-md total_total2_".$id." total_total2' placeholder='Sub Total' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add2_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart2' data-no='".$id."' title='Add Group'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Group Process</button></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='total_qty2' id='sub_qty2' class='form-control text-right  input-md' placeholder='Total Qty' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='total_total2' id='sub_total2' class='form-control text-right  input-md' placeholder='Sub Total' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

    public function get_add_sub_bom2(){
  		$id 	= $this->uri->segment(3);
      $no 	= $this->uri->segment(4);
      $product 	= $this->uri->segment(5);

  		$material	= $this->db->query("SELECT
                                      b.code_material,
                                      b.weight,
                                      c.nm_material
                                    FROM
                                      bom_header a
                                      LEFT JOIN bom_detail b ON a.no_bom=b.no_bom
                                      LEFT JOIN ms_material c ON b.code_material=c.code_material
                                    WHERE
                                      a.id_product='".$product."'
                                    ")->result_array();
  		// echo $qListResin; exit;
  		$d_Header = "";
  		// $d_Header .= "<tr>";
  			$d_Header .= "<tr class='header2_".$id."'>";
  				$d_Header .= "<td align='center'></td>";
  				$d_Header .= "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
          $d_Header .= "<select name='Detail2[".$id."][detail][".$no."][material]' data-no1='".$id."' data-no2='".$no."' class='chosen_select form-control input-sm inline-blockd material process2'>";
          $d_Header .= "<option value='0'>Select Material Name</option>";
          foreach($material AS $val => $valx){
            $d_Header .= "<option value='".$valx['code_material']."'>".strtoupper($valx['nm_material'])."</option>";
          }
          $d_Header .= 		"</select>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<input type='text' name='Detail2[".$id."][detail][".$no."][qty]' id='qty2_".$id."_".$no."' class='form-control text-right input-md maskM qty2_".$id."' placeholder='Qty' readonly>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<input type='text' name='Detail2[".$id."][detail][".$no."][price]' id='price2_".$id."_".$no."' class='form-control text-right  input-md maskM' placeholder='Price' readonly>";
  				$d_Header .= "</td>";
          $d_Header .= "<td align='left' style='vertical-align:middle;'>";
          $d_Header .= "<input type='text' name='Detail2[".$id."][detail][".$no."][total]' id='total2_".$id."_".$no."' class='form-control text-right  input-md total2_".$id."' placeholder='Total' readonly>";
          $d_Header .= "</td>";
  				$d_Header .= "<td align='center' style='vertical-align:middle;'>";
  				$d_Header .= "<button type='button' class='btn btn-sm btn-danger delSubPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
  				$d_Header .= "</td>";
  			$d_Header .= "</tr>";

  		//add nya
  		$d_Header .= "<tr id='add2_".$id."_".$no."' class='header2_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addSubPart2' data-no='".$id."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail2[".$id."][footer][".$no."][total_qty]' class='form-control text-right  input-md total_qty2_".$id." total_qty2' placeholder='Total Qty' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left' style='vertical-align:middle;'>";
        $d_Header .= "<input type='text' name='Detail2[".$id."][footer][".$no."][total_total]' class='form-control text-right  input-md total_total2_".$id." total_total2' placeholder='Sub Total' readonly>";
        $d_Header .= "</td>";
  			$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
  	}

    public function get_data_berat_price(){
  		$product 	= $this->uri->segment(3);
      $material 	= $this->uri->segment(4);

      $weight	= $this->db->query("SELECT
                                      b.weight
                                    FROM
                                      bom_header a
                                      LEFT JOIN bom_detail b ON a.no_bom=b.no_bom
                                    WHERE
                                      a.id_product='".$product."'
                                      AND b.code_material='".$material."'
                                    LIMIT 1
                                    ")->result();
      $get_price	= $this->db->query("SELECT
                                  b.rate
                                FROM
                                  price_ref b
                                WHERE
                                  b.code='".$material."'
                                  AND category = 'material'
                                LIMIT 1
                                ")->result();

      $qty = (!empty($weight[0]->weight))?$weight[0]->weight:0;
      $price = (!empty($get_price[0]->rate))?$get_price[0]->rate:0;

  		 echo json_encode(array(
  				'qty'			=> number_format($qty,5),
          'price'			=> number_format($price,2),
          'total'			=> number_format($qty * $price,5)
  		 ));
  	}

    public function save_bom_hth(){

    	$Arr_Kembali	= array();
  		$data			= $this->input->post();
      // print_r($data);
      // exit;
  		$session 		= $this->session->userdata('app_session');
      $Detail 	  = $data['Detail'];
      $Detail2 	  = $data['Detail2'];
      $Ym					= date('ym');
      $id_material  = $data['no_bom_hth'];
      $tanda        = $data['tanda'];

      $sql_check	= "SELECT * FROM bom_hth_header WHERE id_product = '".$data['produk']."' AND deleted='N'";
      $rest_check = $this->db->query($sql_check)->num_rows();
      // echo $id_material; exit;
      if($rest_check > 0 AND empty($id_material)){
          $Arr_Data	= array(
            'pesan'		=>'Product sudah ada, periksa kembali!',
            'status'	=> 0
          );
          // print_r($Arr_Data); exit;
    		  echo json_encode($Arr_Data);
          return false;
      }else{
        //pengurutan kode
        // echo "Masuk sini";
        if(empty($id_material)){
          $srcMtr			  = "SELECT MAX(kode_bom_hth) as maxP FROM bom_hth_header WHERE kode_bom_hth LIKE 'HTH".$Ym."%' ";
          $numrowMtr		= $this->db->query($srcMtr)->num_rows();
          $resultMtr		= $this->db->query($srcMtr)->result_array();
          $angkaUrut2		= $resultMtr[0]['maxP'];
          $urutan2		  = (int)substr($angkaUrut2, 7, 3);
          $urutan2++;
          $urut2			  = sprintf('%03s',$urutan2);
          $id_material	= "HTH".$Ym.$urut2;
        }
        $ArrHeader		= array(
          'kode_bom_hth'	 => $id_material,
          'id_product'	   => $data['produk'],
          'qty'	           => str_replace(',','',$data['total_qty']),
          'total_price'	   => str_replace(',','',$data['total_total']),
          'qty2'	         => str_replace(',','',$data['total_qty2']),
          'total_price2'	 => str_replace(',','',$data['total_total2']),
          'created_by'	   => $session['id_user'],
          'created_date'   => date('Y-m-d H:i:s')
        );



        $ArrDetail	= array();
        $ArrDetail2	= array();
        $ArrFooter	= array();
        foreach($Detail AS $val => $valx){
          $urut				= sprintf('%03s',$val);
          $ArrDetail[$val]['kode_bom_hth'] 			    = $id_material;
          $ArrDetail[$val]['kode_bom_hth_detail']   = $id_material."-".$urut;
          $ArrDetail[$val]['company'] 		          = 'f-tackle';
          $ArrDetail[$val]['id_group_material'] 		= $valx['group_material'];
          $ArrDetail[$val]['group_material'] 				= get_name('group_material', 'group_material', 'id_group_material', $valx['group_material']);
          if(!empty($valx['detail'])){
            foreach($valx['detail'] AS $val2 => $valx2){
              $ArrDetail2[$val2.$val]['kode_bom_hth'] 			 = $id_material;
              $ArrDetail2[$val2.$val]['kode_bom_hth_detail'] = $id_material."-".$urut;
              $ArrDetail2[$val2.$val]['company'] 	           = 'f-tackle';
              $ArrDetail2[$val2.$val]['id_group_material'] 	 = $valx['group_material'];
              $ArrDetail2[$val2.$val]['id_material'] 	       = $valx2['material'];
              $ArrDetail2[$val2.$val]['qty'] 			           = str_replace(',','',$valx2['qty']);
              $ArrDetail2[$val2.$val]['price'] 				       = str_replace(',','',$valx2['price']);
              $ArrDetail2[$val2.$val]['total'] 				       = str_replace(',','',$valx2['total']);
            }
          }
            foreach($valx['footer'] AS $val2F => $valx2F){
              $ArrFooter[$val2F.$val]['kode_bom_hth'] 			  = $id_material;
              $ArrFooter[$val2F.$val]['kode_bom_hth_detail'] = $id_material."-".$urut;
              $ArrFooter[$val2F.$val]['company'] 	          = 'f-tackle';
              $ArrFooter[$val2F.$val]['id_group_material'] 	= $valx['group_material'];
              $ArrFooter[$val2F.$val]['qty'] 			          = str_replace(',','',$valx2F['total_qty']);
              $ArrFooter[$val2F.$val]['total_price'] 				= str_replace(',','',$valx2F['total_total']);
            }

        }

        $ArrFooter[$val]['kode_bom_hth'] 			    = $id_material;
        $ArrFooter[$val]['kode_bom_hth_detail']   = $id_material;
        $ArrFooter[$val]['company'] 		          = 'f-tackle';
        $ArrFooter[$val]['id_group_material'] 		= 0;
        $ArrFooter[$val]['qty'] 		              = str_replace(',','',$data['total_qty']);
        $ArrFooter[$val]['total_price'] 		      = str_replace(',','',$data['total_total']);

        //ORIGA

        $ArrDetail2x	= array();
        $ArrDetail22	= array();
        $ArrFooter2	= array();
        foreach($Detail2 AS $val => $valx){
          $urut				= sprintf('%03s',$val);
          $ArrDetail2x[$val]['kode_bom_hth'] 			  = $id_material;
          $ArrDetail2x[$val]['kode_bom_hth_detail']  = $id_material."-".$urut;
          $ArrDetail2x[$val]['company'] 		          = 'origa';
          $ArrDetail2x[$val]['id_group_material'] 		= $valx['group_material'];
          $ArrDetail2x[$val]['group_material'] 			= get_name('group_material', 'group_material', 'id_group_material', $valx['group_material']);
          if(!empty($valx['detail'])){
            foreach($valx['detail'] AS $val2 => $valx2){
              $ArrDetail22[$val2.$val]['kode_bom_hth'] 			   = $id_material;
              $ArrDetail22[$val2.$val]['kode_bom_hth_detail']  = $id_material."-".$urut;
              $ArrDetail22[$val2.$val]['company'] 	           = 'origa';
              $ArrDetail22[$val2.$val]['id_group_material'] 	 = $valx['group_material'];
              $ArrDetail22[$val2.$val]['id_material'] 	       = $valx2['material'];
              $ArrDetail22[$val2.$val]['qty'] 			           = str_replace(',','',$valx2['qty']);
              $ArrDetail22[$val2.$val]['price'] 				       = str_replace(',','',$valx2['price']);
              $ArrDetail22[$val2.$val]['total'] 				       = str_replace(',','',$valx2['total']);
            }
          }
            foreach($valx['footer'] AS $val2F => $valx2F){
              $ArrFooter2[$val2F.$val]['kode_bom_hth'] 			  = $id_material;
              $ArrFooter2[$val2F.$val]['kode_bom_hth_detail'] = $id_material."-".$urut;
              $ArrFooter2[$val2F.$val]['company'] 	          = 'origa';
              $ArrFooter2[$val2F.$val]['id_group_material'] 	= $valx['group_material'];
              $ArrFooter2[$val2F.$val]['qty'] 			          = str_replace(',','',$valx2F['total_qty']);
              $ArrFooter2[$val2F.$val]['total_price'] 				= str_replace(',','',$valx2F['total_total']);
            }
        }

        $ArrFooter2[$val]['kode_bom_hth'] 			  = $id_material;
        $ArrFooter2[$val]['kode_bom_hth_detail']  = $id_material;
        $ArrFooter2[$val]['company'] 		          = 'origa';
        $ArrFooter2[$val]['id_group_material'] 		= 0;
        $ArrFooter2[$val]['qty'] 		              = str_replace(',','',$data['total_qty2']);
        $ArrFooter2[$val]['total_price'] 		      = str_replace(',','',$data['total_total2']);


        // print_r($ArrHeader);
    		// print_r($ArrDetail);
    		// print_r($ArrDetail2);
        // print_r($ArrFooter);
        //
        // print_r($ArrDetail2x);
        // print_r($ArrDetail22);
        // print_r($ArrFooter2);
    		// exit;

    		$this->db->trans_start();
          $this->db->delete('bom_hth_header', array('kode_bom_hth' => $id_material));
          $this->db->delete('bom_hth_detail_header', array('kode_bom_hth' => $id_material));
          $this->db->delete('bom_hth_detail_detail', array('kode_bom_hth' => $id_material));
          $this->db->delete('bom_hth_detail_footer', array('kode_bom_hth' => $id_material));

      		$this->db->insert('bom_hth_header', $ArrHeader);
          if(!empty($ArrDetail)){
      			$this->db->insert_batch('bom_hth_detail_header', $ArrDetail);
          }
          if(!empty($ArrDetail2)){
            $this->db->insert_batch('bom_hth_detail_detail', $ArrDetail2);
          }
          if(!empty($ArrFooter)){
            $this->db->insert_batch('bom_hth_detail_footer', $ArrFooter);
          }

          if(!empty($ArrDetail2x)){
      			$this->db->insert_batch('bom_hth_detail_header', $ArrDetail2x);
          }
          if(!empty($ArrDetail22)){
            $this->db->insert_batch('bom_hth_detail_detail', $ArrDetail22);
          }
          if(!empty($ArrFooter2)){
            $this->db->insert_batch('bom_hth_detail_footer', $ArrFooter2);
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
          history($tanda." BOM Head To Head ".$id_material);
    		}
        echo json_encode($Arr_Data);
      }
  	}

    public function hapus_bom_hth(){
        $data       = $this->input->post();
        $session 		= $this->session->userdata('app_session');
        $no_bom     = $data['id'];

        $ArrHeader		= array(
          'deleted'			  => "Y",
          'deleted_by'	  => $session['id_user'],
          'deleted_date'	=> date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
            $this->db->where('kode_bom_hth', $no_bom);
            $this->db->update('bom_hth_header', $ArrHeader);
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
          history("Delete data BOM HTH ".$no_bom);
        }

        echo json_encode($Arr_Data);

    }

    public function print_bom_hth(){
  		$bom_hth	= $this->uri->segment(3);
  		$data_session	= $this->session->userdata;
  		$session 		   = $this->session->userdata('app_session');
  		$printby		= $session['id_user'];

  		$data_url		= base_url();
  		$Split_Beda		= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

      $header       = $this->db->query("SELECT * FROM bom_hth_header WHERE kode_bom_hth='".$bom_hth."' LIMIT 1")->result();
      $detail_head  = $this->db->query("SELECT * FROM bom_hth_detail_header WHERE kode_bom_hth='".$bom_hth."' AND company='origa'")->result_array();

      $data = array(
  			'Nama_Beda' => $Nama_Beda,
  			'printby' => $printby,
  			'bom_hth' => $bom_hth,
        'detail_head' => $detail_head,
        'header' => $header
  		);

  		history('Print BOM head To Head '.$bom_hth);
  		$this->load->view('print_bom_hth', $data);
  	}

    public function excel_report_all_bom_hth(){
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$this->load->library("PHPExcel");
  		// $this->load->library("PHPExcel/Writer/Excel2007");
  		$objPHPExcel	= new PHPExcel();

  		$style_header = array(
  			'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			),
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
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
  				'color' => array('rgb'=>'e0e0e0'),
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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

    		$Arr_Bulan	= array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    		$sheet 		= $objPHPExcel->getActiveSheet();

  		$sql = "
  			SELECT
  				a.*
  			FROM
  				bom_hth_header a
  		    WHERE a.deleted = 'N'
  			ORDER BY
  				a.id_product ASC
  		";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(5);
    		$sheet->setCellValue('A'.$Row, 'BOM HEAD TO HEAD');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Project');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'Product');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D'.$NewRow, 'Price ORIGA');
    		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->setCellValue('E'.$NewRow, 'Price F-TACKLE');
    		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
    		$sheet->getColumnDimension('E')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$id_produksi	= strtoupper(get_name('ms_inventory_category1','nama','id_category1',get_name('ms_inventory_category2','id_category1','id_category2', $row_Cek['id_product'])));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= strtoupper(get_name('ms_inventory_category2','nama','id_category2', $row_Cek['id_product']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= number_format($row_Cek['total_price2'],5);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $awal_col++;
          $status_date	= number_format($row_Cek['total_price'],5);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

  			}
  		}


  		$sheet->setTitle('List Bom Head To Head');
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
  		header('Content-Disposition: attachment;filename="BOM_HTH_'.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function excel_report_satuan_bom_hth(){
      $bom_hth	= $this->uri->segment(3);
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$this->load->library("PHPExcel");
  		// $this->load->library("PHPExcel/Writer/Excel2007");
  		$objPHPExcel	= new PHPExcel();

  		$style_header = array(
  			'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			),
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
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
  				'color' => array('rgb'=>'e0e0e0'),
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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

    		$Arr_Bulan	= array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    		$sheet 		= $objPHPExcel->getActiveSheet();

        $header       = $this->db->query("SELECT * FROM bom_hth_header WHERE kode_bom_hth='".$bom_hth."' LIMIT 1")->result();

  		$sql = "SELECT * FROM bom_hth_detail_header WHERE kode_bom_hth='".$bom_hth."' AND company='origa'";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(10);
    		$sheet->setCellValue('A'.$Row, 'BOM HEAD TO HEAD '.$bom_hth);
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

        $NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'CODE');
    		$sheet->getStyle('A'.$NewRow.':B'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('A'.$NewRow.':B'.$NewRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

        $sheet->setCellValue('C'.$NewRow, $bom_hth);
    		$sheet->getStyle('C'.$NewRow.':C'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NewRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $NewRow	= $NewRow +1;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'PROJECT');
    		$sheet->getStyle('A'.$NewRow.':B'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('A'.$NewRow.':B'.$NewRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

        $sheet->setCellValue('C'.$NewRow, strtoupper(get_name('ms_inventory_category1','nama','id_category1',get_name('ms_inventory_category2','id_category1','id_category2', $header[0]->id_product))));
    		$sheet->getStyle('C'.$NewRow.':C'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NewRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $NewRow	= $NewRow +1;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'PRICE ORIGA');
    		$sheet->getStyle('A'.$NewRow.':B'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('A'.$NewRow.':B'.$NewRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

        $sheet->setCellValue('C'.$NewRow, number_format($header[0]->total_price2,5));
    		$sheet->getStyle('C'.$NewRow.':C'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NewRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $NewRow	= $NewRow +1;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'PRICE F-TACKLE');
    		$sheet->getStyle('A'.$NewRow.':B'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('A'.$NewRow.':B'.$NewRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

        $sheet->setCellValue('C'.$NewRow, number_format($header[0]->total_price,5));
    		$sheet->getStyle('C'.$NewRow.':C'.$NewRow)->applyFromArray($styleArray3);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NewRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'NO');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'GROUP PROCESS');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'ORIGA');
    		$sheet->getStyle('C'.$NewRow.':F'.$NewRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':F'.$NewRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('G'.$NewRow, 'F-TACKLE');
        $sheet->getStyle('G'.$NewRow.':J'.$NewRow)->applyFromArray($style_header);
        $sheet->mergeCells('G'.$NewRow.':J'.$NewRow);
        $sheet->getColumnDimension('G')->setAutoSize(true);

        $NewRow	= $NewRow +1;
    		$NextRow= $NewRow +1;

  		  $sheet->setCellValue('C'.$NewRow, 'MATERIAL NAME');
    		$sheet->getStyle('C'.$NewRow.':C'.$NewRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NewRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D'.$NewRow, 'QTY');
    		$sheet->getStyle('D'.$NewRow.':D'.$NewRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NewRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->setCellValue('E'.$NewRow, 'PRICE');
    		$sheet->getStyle('E'.$NewRow.':E'.$NewRow)->applyFromArray($style_header);
    		$sheet->mergeCells('E'.$NewRow.':E'.$NewRow);
    		$sheet->getColumnDimension('E')->setAutoSize(true);

        $sheet->setCellValue('F'.$NewRow, 'TOTAL');
        $sheet->getStyle('F'.$NewRow.':F'.$NewRow)->applyFromArray($style_header);
        $sheet->mergeCells('F'.$NewRow.':F'.$NewRow);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $sheet->setCellValue('G'.$NewRow, 'MATERIAL NAME');
        $sheet->getStyle('G'.$NewRow.':G'.$NewRow)->applyFromArray($style_header);
        $sheet->mergeCells('G'.$NewRow.':G'.$NewRow);
        $sheet->getColumnDimension('G')->setAutoSize(true);

        $sheet->setCellValue('H'.$NewRow, 'QTY');
        $sheet->getStyle('H'.$NewRow.':H'.$NewRow)->applyFromArray($style_header);
        $sheet->mergeCells('H'.$NewRow.':H'.$NewRow);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $sheet->setCellValue('I'.$NewRow, 'PRICE');
        $sheet->getStyle('I'.$NewRow.':I'.$NewRow)->applyFromArray($style_header);
        $sheet->mergeCells('I'.$NewRow.':I'.$NewRow);
        $sheet->getColumnDimension('I')->setAutoSize(true);

        $sheet->setCellValue('J'.$NewRow, 'TOTAL');
        $sheet->getStyle('J'.$NewRow.':J'.$NewRow)->applyFromArray($style_header);
        $sheet->mergeCells('J'.$NewRow.':J'.$NewRow);
        $sheet->getColumnDimension('J')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NewRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

          $detail_origa = $this->db->query("
                                                SELECT
                                                  a.id AS id,
                                                  a.material AS material,
                                                  a.qty AS qty,
                                                  a.price AS price,
                                                  a.total AS total
                                                FROM
                                                  view_bom_hth_origa a
                                                WHERE
                                                  a.kode_bom_hth_detail = '".$row_Cek['kode_bom_hth_detail']."'")->result_array();
          $detail_ftackle = $this->db->query("
                                                SELECT
                                                  a.id AS id,
                                                  a.material AS material,
                                                  a.qty AS qty,
                                                  a.price AS price,
                                                  a.total AS total
                                                FROM
                                                  view_bom_hth_ftackle a
                                                WHERE
                                                  a.kode_bom_hth_detail = '".$row_Cek['kode_bom_hth_detail']."'")->result_array();

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$id_produksi	= strtoupper($row_Cek['group_material']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_origa) < 2){
            $koma = "";
          }
          foreach($detail_origa AS $val2 => $val2x){
            $LAB .= strtoupper(get_name('ms_material','nm_material','code_material', $val2x['material'])).$koma;
            // $LAB .= $LAB->setWrapText(true);
          }

  				$awal_col++;
  				$status_date	= $LAB;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_origa) < 2){
            $koma = "";
          }
          foreach($detail_origa AS $val2 => $val2x){
            $LAB .= number_format($val2x['qty'],5).$koma;
          }

          $awal_col++;
          $status_date	= $LAB;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_origa) < 2){
            $koma = "";
          }
          foreach($detail_origa AS $val2 => $val2x){
            $LAB .= number_format($val2x['price'],2).$koma;
          }

          $awal_col++;
          $status_date	= $LAB;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_origa) < 2){
            $koma = "";
          }
          foreach($detail_origa AS $val2 => $val2x){
            $SUM += $val2x['total'];
            $LAB .= number_format($val2x['total'],5).$koma;
          }

          $awal_col++;
          $status_date	= $LAB;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_ftackle) < 2){
            $koma = "";
          }
          foreach($detail_ftackle AS $val2 => $val2x){
            $LAB .= strtoupper($val2x['material']).$koma;
          }

          $awal_col++;
          $status_date	= $LAB;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_ftackle) < 2){
            $koma = "";
          }
          foreach($detail_ftackle AS $val2 => $val2x){
            $LAB .= number_format($val2x['qty'],5).$koma;
          }

          $awal_col++;
          $status_date	= $LAB;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_ftackle) < 2){
            $koma = "";
          }
          foreach($detail_ftackle AS $val2 => $val2x){
            $LAB .= number_format($val2x['price'],2).$koma;
          }

          $awal_col++;
          $status_date	= $LAB;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $LAB = "";
          $koma = ",  ";
          if(COUNT($detail_ftackle) < 2){
            $koma = "";
          }
          foreach($detail_ftackle AS $val2 => $val2x){
            $SUM2 += $val2x['total'];
            $LAB .= number_format($val2x['total'],5).$koma;
          }

          $awal_col++;
          $status_date	= $LAB;
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

  			}
  		}


  		$sheet->setTitle('List Bom Head To Head');
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
  		header('Content-Disposition: attachment;filename="BOM_'.$bom_hth.'_'.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function excel_report_all_bom(){
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$this->load->library("PHPExcel");
  		// $this->load->library("PHPExcel/Writer/Excel2007");
  		$objPHPExcel	= new PHPExcel();

  		$style_header = array(
  			'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			),
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
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
  				'color' => array('rgb'=>'e0e0e0'),
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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

    		$Arr_Bulan	= array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    		$sheet 		= $objPHPExcel->getActiveSheet();

  		$sql = "
  			SELECT
  				a.*
  			FROM
  				bom_header a
  		    WHERE a.deleted = 'N'
  			ORDER BY
  				a.id_product ASC
  		";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(4);
    		$sheet->setCellValue('A'.$Row, 'BOM HEAD TO HEAD');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Project');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'Product');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D'.$NewRow, 'Total Weight');
    		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$id_produksi	= strtoupper(get_name('ms_inventory_category1','nama','id_category1',get_name('ms_inventory_category2','id_category1','id_category2', $row_Cek['id_product'])));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= strtoupper(get_name('ms_inventory_category2','nama','id_category2', $row_Cek['id_product']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $SUM_WEIGHT = $this->db->query("SELECT SUM(weight) AS berat FROM bom_detail WHERE no_bom = '".$row_Cek['no_bom']."' ")->result();

          $awal_col++;
          $status_date	= number_format($SUM_WEIGHT[0]->berat,5);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

  			}
  		}


  		$sheet->setTitle('List BOM');
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
  		header('Content-Disposition: attachment;filename="BOM_'.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function excel_report_all_bom_detail(){
      $kode_bom = $this->uri->segment(3);
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$this->load->library("PHPExcel");
  		// $this->load->library("PHPExcel/Writer/Excel2007");
  		$objPHPExcel	= new PHPExcel();

  		$style_header = array(
  			'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			),
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
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
  				'color' => array('rgb'=>'e0e0e0'),
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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
  					  'color' => array('rgb'=>'000000')
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

    		$Arr_Bulan	= array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    		$sheet 		= $objPHPExcel->getActiveSheet();

  		$sql = "
  			SELECT
  				a.id_product,
          b.code_material,
          b.weight
  			FROM
  				bom_header a LEFT JOIN bom_detail b ON a.no_bom = b.no_bom
  		    WHERE a.no_bom = '".$kode_bom."' AND b.no_bom = '".$kode_bom."'
  			ORDER BY
  				b.code_material ASC
  		";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(5);
    		$sheet->setCellValue('A'.$Row, 'BOM HEAD TO HEAD DETAIL');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Project');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'Product');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D'.$NewRow, 'Material Name');
    		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->setCellValue('E'.$NewRow, 'Total Weight');
    		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
    		$sheet->getColumnDimension('E')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$id_produksi	= strtoupper(get_name('ms_inventory_category1','nama','id_category1',get_name('ms_inventory_category2','id_category1','id_category2', $row_Cek['id_product'])));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= strtoupper(get_name('ms_inventory_category2','nama','id_category2', $row_Cek['id_product']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= strtoupper(get_name('ms_material','nm_material','code_material', $row_Cek['code_material']));
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= number_format($row_Cek['weight'],5);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

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
  		header('Content-Disposition: attachment;filename="BOM_DETAIL_'.strtoupper(get_name('ms_inventory_category2','nama','id_category2', $product[0]['id_product'])).'_'.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

}

?>
