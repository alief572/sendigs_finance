<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Warehouse_product extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Warehouse_Product.View';
    protected $addPermission  	= 'Warehouse_Product.Add';
    protected $managePermission = 'Warehouse_Product.Manage';
    protected $deletePermission = 'Warehouse_Product.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Warehouse_product/warehouse_product_model'
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    //==========================================================================================================
    //============================================STOCK=========================================================
    //==========================================================================================================

    public function stock(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index finish good product");
      $this->template->title('Product Finish Good');
      $this->template->render('stock');
    }

    public function data_side_stock(){
  		$this->warehouse_product_model->get_json_stock();
  	}

    //==========================================================================================================
    //============================================WIP=========================================================
    //==========================================================================================================

    public function wip(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index product wip");
      $this->template->title('Product WIP');
      $this->template->render('wip');
    }

    public function data_side_wip(){
  		$this->warehouse_product_model->get_json_wip();
  	}

    public function add_wip(){
      if($this->input->post()){
        $data 			= $this->input->post();
    		$session  = $this->session->userdata('app_session');
    		$detail			= $data['detail'];
        $Ym = date('ym');
        $srcMtr			  = "SELECT MAX(no_wip) as maxP FROM material_wip WHERE no_wip LIKE 'WIP".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 7, 3);
        $urutan2++;
        $urut2			  = sprintf('%03s',$urutan2);
        $no_wip	      = "WIP".$Ym.$urut2;

        $ArrHeader = array(
          'no_wip'        => $no_wip,
          'costcenter'    => 'CC2000012',
          'created_by'	  => $session['id_user'],
          'created_date'	=> date('Y-m-d H:i:s')
        );

        $ArrDetail	= array();
        foreach($detail AS $val => $valx){
          $ArrDetail[$val]['no_wip'] = $no_wip;
          $ArrDetail[$val]['material'] = $valx['material'];
          $ArrDetail[$val]['qty_material'] = $valx['qty_material'];
          $ArrDetail[$val]['unit'] = $valx['unit'];
          $ArrDetail[$val]['qty_packing'] = $valx['qty_packing'];
          $ArrDetail[$val]['unit_packing'] = $valx['unit_packing'];
          $ArrDetail[$val]['qty_aktual'] 	      = str_replace(',','',$valx['qty_aktual']);
          $ArrDetail[$val]['unit_aktual'] = $valx['unit_aktual'];
        }

        $q_update = "SELECT
                  	a.id,
                  	a.sts_wip
                  FROM
                  	produksi_planning_data a
                  	LEFT JOIN produksi_planning b ON a.no_plan = b.no_plan
                  WHERE
                  	b.costcenter = 'CC2000012'
                  	AND a.sts_wip = 'N'";
        $result_update = $this->db->query($q_update)->result_array();
        $ArrUpdate	= array();
        foreach($result_update AS $val => $valx){
          $ArrUpdate[$val]['id']          = $valx['id'];
          $ArrUpdate[$val]['sts_wip']     = 'Y';
          $ArrUpdate[$val]['updated_by']  = $session['id_user'];
          $ArrUpdate[$val]['updated_date']= date('Y-m-d H:i:s');
          $ArrUpdate[$val]['no_wip']      = $no_wip;
        }

        // print_r($ArrHeader);
        // print_r($ArrDetail);
        // print_r($ArrUpdate);
        //
        // exit;
        $this->db->trans_start();
          $this->db->insert('material_wip', $ArrHeader);
          $this->db->insert_batch('material_wip_detail', $ArrDetail);
          $this->db->update_batch('produksi_planning_data', $ArrUpdate, 'id');
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
          history("Insert Material WIP ".$no_wip);
        }
        echo json_encode($Arr_Data);
      }
      else{
        $this->template->title('Materials WIP');
        $this->template->render('add_wip');
      }
  	}

    public function get_wip_produksi(){

      $query = "SELECT
                  a.*,
                  b.nm_material,
                  b.unit,
                  b.konversi,
                  b.satuan_packing
                FROM
                  get_material_wip a LEFT JOIN ms_material b ON a.code_material=b.code_material";
      $result = $this->db->query($query)->result_array();
      $num_r = $this->db->query($query)->num_rows();
      $d_Header = "<div class='box box-primary'>";
        	$d_Header .= "<div class='box-body'>";
          $d_Header .= "<table class='table table-bordered table-striped'>";
          $d_Header .= "<thead>";
          $d_Header .= "<tr>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;'>#</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;'>Material Name</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;'>Qty Material</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;'>Qty Packing</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;'>Actual Qty Material</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;'>Unit</th>";
          $d_Header .= "</tr>";
          $d_Header .= "</thead>";
          $d_Header .= "<tbody>";
          foreach ($result as $key => $value) { $key++;
            $d_Header .= "<tr>";
              $d_Header .= "<td class='text-center'>".$key."</td>";
              $d_Header .= "<td>".strtoupper($value['nm_material'])."</td>";
              $d_Header .= "<td class='text-right'>".number_format($value['weight'],2)." ".ucfirst($value['unit'])."</td>";
              $d_Header .= "<td class='text-right'>".number_format($value['weight']/$value['konversi'],2)." ".ucfirst($value['satuan_packing'])."</td>";
              $d_Header .= "<td>
                            <input type='text' name='detail[".$key."][qty_aktual]' class='form-control input-md text-right maskM'>
                            <input type='hidden' name='detail[".$key."][material]' class='form-control input-md' value='".$value['code_material']."'>
                            <input type='hidden' name='detail[".$key."][qty_material]' class='form-control input-md' value='".$value['weight']."'>
                            <input type='hidden' name='detail[".$key."][unit]' class='form-control input-md' value='".$value['unit']."'>
                            <input type='hidden' name='detail[".$key."][qty_packing]' class='form-control input-md' value='".$value['weight']/$value['konversi']."'>
                            <input type='hidden' name='detail[".$key."][unit_packing]' class='form-control input-md' value='".$value['satuan_packing']."'>
                            <input type='hidden' name='detail[".$key."][unit_aktual]' class='form-control input-md' value='".$value['unit']."'>
                            </td>";
              $d_Header .= "<td class='text-center'>".ucfirst($value['unit'])."</td>";
            $d_Header .= "</tr>";
          }
          if($num_r < 1){
            $d_Header .= "<tr>";
            $d_Header .= "<td colspan='6'>Data not found ...</td>";
            $d_Header .= "/tr>";
          }
          $d_Header .= "</tbody>";
          $d_Header .= "</table>";
          $d_Header .= "</div>";
      $d_Header .= "</div>";


  		 echo json_encode(array(
  				'header'			=> $d_Header,
          'num' => $num_r
  		 ));
  	}

    public function detail_wip(){
      $no_wip     = $this->uri->segment(3);
      $created    = str_replace('sp4si',' ',$this->uri->segment(4));
      $dated      = str_replace('sp4si',' ',$this->uri->segment(5));

      $query 	= "SELECT a.*, b.nm_material FROM material_wip_detail a LEFT JOIN ms_material b ON a.material=b.code_material WHERE no_wip='".$no_wip."'";
      $result		= $this->db->query($query)->result_array();

      $data = array(
        'data' => $result
      );

      $this->template->set('results', $data);
      history("View detail wip ".$no_wip);
      $this->template->title('Materials WIP');
      $this->template->render('detail_wip');
    }

    //==========================================================================================================
    //============================================DELIVERY======================================================
    //==========================================================================================================

    public function delivery(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index delivery product");
      $this->template->title('Product Delivery');
      $this->template->render('delivery');
    }

    public function data_side_delivery(){
  		$this->warehouse_product_model->get_json_delivery();
  	}

    public function add_delivery(){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');
    		$detail			  = $data['detail'];
        $no_so			  = $data['delivery'];

        $Ym           = date('y');
        $srcMtr			  = "SELECT MAX(no_delivery) as maxP FROM delivery_header WHERE no_delivery LIKE 'DY".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 5, 4);
        $urutan2++;
        $urut2			  = sprintf('%04s',$urutan2);
        $no_delivery	= "DY".$Ym.$urut2;

        $ArrHeader = array(
          'no_delivery'   => $no_delivery,
          'no_so'         => $no_so,
          'code_cust'     => get_name_field('sales_order_header', 'code_cust', 'no_so', $no_so),
          'delivery_date' => get_name_field('sales_order_header', 'delivery_date', 'no_so', $no_so),
          'delivery_real' => get_name_field('sales_order_header', 'delivery_date', 'no_so', $no_so),
          'shipping'      => get_name_field('sales_order_header', 'shipping', 'no_so', $no_so),
          'created_by'	  => $session['id_user'],
          'created_date'	=> date('Y-m-d H:i:s')
        );

        $ArrDetail	= array();
        $ArrUpdate	= array();
        $ArrUpdate2	= array();
        $ArrHist2	= array();
        $ArrUpdate3	= array();
        $ArrHist3	= array();
        $ArrUpdatePro	= array();
        $ArrHist	= array();

        $ArrUpdateDC	= array();
        foreach($detail AS $val => $valx){
          if($valx['qty_delivery'] <> ''){
            $ArrDetail[$val]['no_delivery']   = $no_delivery;
            $ArrDetail[$val]['id_so_detail']  = $valx['id_so_detail'];
            $ArrDetail[$val]['product']       = $valx['product'];
            $ArrDetail[$val]['qty_order']     = $valx['qty_order'];
            $ArrDetail[$val]['qty_delivery']  = $valx['qty_delivery'];
            $ArrDetail[$val]['qty_kurang']    = $valx['qty_kurang'];
            $ArrDetail[$val]['remarks']       = strtolower($valx['remarks']);
            $ArrDetail[$val]['tanggal']       = $valx['tanggal'];

            $restLast = $this->db->query("SELECT qty_delivery FROM sales_order_detail WHERE id='".$valx['id_so_detail']."' LIMIT 1")->result();

            $ArrUpdate[$val]['id']            = $valx['id_so_detail'];
            $ArrUpdate[$val]['qty_delivery']  = $valx['qty_delivery'] + $restLast[0]->qty_delivery;


            $sql_last_pro	   = "SELECT * FROM warehouse_product WHERE id_product = '".$valx['product']."' AND costcenter = 'CC2000001' AND category = 'order' LIMIT 1";
            $rest_last_pro	 = $this->db->query($sql_last_pro)->result();
            $qty_stock       = (!empty($rest_last_pro[0]->qty_stock))?$rest_last_pro[0]->qty_stock:0;

            //insert batch
            // $ArrUpdate3[$val]['id_product'] = $valx['product'];
            // $ArrUpdate3[$val]['costcenter'] = 'CC2000001';
            // $ArrUpdate3[$val]['qty_stock']       = $qty_stock - $valx['qty_delivery'];
            // $ArrUpdate3[$val]['update_by'] 	     = $session['id_user'];
    				// $ArrUpdate3[$val]['update_date'] 	   = date('Y-m-d H:i:s');

            $ArrUpdate3 = array(
              'qty_stock'       => $qty_stock - $valx['qty_delivery'],
              'update_by' 	    => $session['id_user'],
    				  'update_date' 	  => date('Y-m-d H:i:s')
            );

            $last_cost = get_last_costcenter_warehouse($valx['product']);
            $this->db->where('category', 'order');
            $this->db->where('costcenter', $last_cost);
            $this->db->where('id_product', $valx['product']);
            $this->db->update('warehouse_product', $ArrUpdate3, 'id_product');

            $ArrHist3[$val]['category'] 		   = 'order';
    				$ArrHist3[$val]['id_product'] 		 = $valx['product'];
            $ArrHist3[$val]['qty_stock_awal']  = $qty_stock;
    				$ArrHist3[$val]['qty_stock_akhir'] = $qty_stock - $valx['qty_delivery'];
            $ArrHist3[$val]['no_trans'] 		   = $no_delivery;
            $ArrHist3[$val]['update_by'] 	     = $session['id_user'];
    				$ArrHist3[$val]['update_date'] 	   = date('Y-m-d H:i:s');


            $sql_last_pro2	   = "SELECT * FROM warehouse_product WHERE id_product = '".$valx['product']."' AND category = 'product' LIMIT 1";
            $rest_last_pro2	 = $this->db->query($sql_last_pro2)->result();
            $qty_stock2       = (!empty($rest_last_pro2[0]->qty_stock))?$rest_last_pro2[0]->qty_stock:0;
            $qty_order2       = (!empty($rest_last_pro2[0]->qty_order))?$rest_last_pro2[0]->qty_order:0;

            $ArrUpdate2[$val]['id_product'] = $valx['product'];
            $ArrUpdate2[$val]['qty_stock']  = $qty_stock2 - $valx['qty_delivery'];
            $ArrUpdate2[$val]['qty_order']  = $qty_order2 - $valx['qty_delivery'];

            $ArrHist2[$val]['category'] 		   = 'product';
    				$ArrHist2[$val]['id_product'] 		 = $valx['product'];
    				$ArrHist2[$val]['qty_order_awal']  = $qty_order2;
    				$ArrHist2[$val]['qty_order_akhir'] = $qty_order2 - $valx['qty_delivery'];
            $ArrHist2[$val]['qty_stock_awal']  = $qty_stock2;
    				$ArrHist2[$val]['qty_stock_akhir'] = $qty_stock2 - $valx['qty_delivery'];
            $ArrHist2[$val]['no_trans'] 		   = $no_delivery;
            $ArrHist2[$val]['update_by'] 	     = $session['id_user'];
    				$ArrHist2[$val]['update_date'] 	   = date('Y-m-d H:i:s');

            //mengurangi material
            $sql_material = "SELECT a.id_product, b.code_material, b.weight FROM bom_header a LEFT JOIN bom_detail b ON a.no_bom=b.no_bom WHERE a.id_product= '".$valx['product']."'";
            $rest_mat = $this->db->query($sql_material)->result_array();
            foreach($rest_mat AS $val2 => $valx2){
              $sqlWhDetail	   = "SELECT b.* FROM warehouse_stock b WHERE b.id_material = '".$valx2['code_material']."' AND b.kd_gudang = 'PRO'";
      				$restWhDetail	   = $this->db->query($sqlWhDetail)->result();

              $ArrUpdatePro[$val2]['id_material']       = $valx2['code_material'];
      				$ArrUpdatePro[$val2]['kd_gudang'] 	      = 'PRO';
              $ArrUpdatePro[$val2]['outgoing'] 	        = $valx2['weight'] * $valx['qty_delivery'];
      				$ArrUpdatePro[$val2]['qty_stock'] 	      = $restWhDetail[0]->qty_stock - ($valx2['weight'] * $valx['qty_delivery']);
      				$ArrUpdatePro[$val2]['update_by'] 	      = $session['id_user'];
      				$ArrUpdatePro[$val2]['update_date']       = date('Y-m-d H:i:s');

              //insert history
      				$ArrHist[$val2]['id_material'] 		  = $restWhDetail[0]->id_material;
      				$ArrHist[$val2]['idmaterial'] 		    = $restWhDetail[0]->idmaterial;
      				$ArrHist[$val2]['nm_material'] 		  = $restWhDetail[0]->nm_material;
      				$ArrHist[$val2]['kd_gudang_dari'] 	  = "PRO";
      				$ArrHist[$val2]['kd_gudang_ke'] 		  = "DFY";
      				$ArrHist[$val2]['qty_stock_awal'] 	  = $restWhDetail[0]->qty_stock;
      				$ArrHist[$val2]['qty_stock_akhir'] 	= $restWhDetail[0]->qty_stock - ($valx2['weight'] * $valx['qty_delivery']);

      				$ArrHist[$val2]['no_ipp'] 			    = $no_delivery;
      				$ArrHist[$val2]['jumlah_mat'] 		    = $qty_aktual;
      				$ArrHist[$val2]['update_by'] 		    = $session['id_user'];
      				$ArrHist[$val2]['update_date'] 		  = date('Y-m-d H:i:s');
            }

            //update daycode_$a
            if(count($valx['daycode']) == $valx['qty_delivery']){

              $dtListArray = array();
      				foreach($valx['daycode'] AS $val2 => $valx2){
      					$dtListArray[$val2] = $valx2;
      				}
      				$dtImplode	= "('".implode("','", $dtListArray)."')";

              $ArrUpdateDC[$val]['product']         = $valx['product'];
              $ArrUpdateDC[$val]['daycode']         = $dtImplode;
              $ArrUpdateDC[$val]['delivery_code']   = $no_delivery ." ".$dtImplode;
              $ArrUpdateDC[$val]['sts_daycode']     = 'Y';
              $ArrUpdateDC[$val]['delivery_by']     = $session['id_user'];
              $date_now   = date('Y-m-d H:i:s');

              $sql_update = " UPDATE report_produksi_daily_detail
                                SET
                                delivery_code = '".$no_delivery ."',
                                sts_daycode = 'Y',
                                delivery_by = '".$session['id_user']."',
                                delivery_date = '".$date_now."'
                              WHERE
                                id_product = '".$valx['product']."'
                                AND ket = 'good'
                                AND code IN ".$dtImplode."
                            ";
                // echo $sql_update."<br>";
              $this->db->query($sql_update);
            }
            else{
              $Arr_Data	= array(
                'pesan'		=> "Product ".$valx['product']." jumlah qty deliver dengan jumlah daycode berbeda. ",
                'status'	=> 0
              );
              echo json_encode($Arr_Data);

            }
          }
        }

        // print_r($ArrUpdateDC);

        // print_r($ArrHeader);
        // print_r($ArrUpdate3);
        // print_r($ArrHist3);
        // print_r($ArrHist);
        // print_r($ArrUpdatePro);
        // exit;

        $this->db->trans_start();
          if(!empty($ArrDetail)){
            $this->db->insert('delivery_header', $ArrHeader);
            $this->db->insert_batch('delivery_detail', $ArrDetail);
            $this->db->update_batch('sales_order_detail', $ArrUpdate, 'id');

            $this->db->where('category', 'product');
            $this->db->update_batch('warehouse_product', $ArrUpdate2, 'id_product');
            $this->db->insert_batch('warehouse_product_history', $ArrHist2);


            // $this->db->where('category', 'order');
            // $this->db->where('costcenter', 'CC2000001');
            // $this->db->update_batch('warehouse_product', $ArrUpdate3, 'id_product');

            // $this->db->insert_batch('warehouse_product_history', $ArrHist3);
            //
            // $this->db->where('id_gudang','3');
            // $this->db->update_batch('warehouse_stock', $ArrUpdatePro, 'id_material');
            //
            // $this->db->insert_batch('warehouse_history', $ArrHist);
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
          history("Insert Delivery Product ".$no_delivery);
        }
        echo json_encode($Arr_Data);
      }
      else{
        $this->template->title('Add Delivery');
        $this->template->render('add_delivery');
      }
  	}

    public function get_delivery(){
      $no_so = $this->uri->segment(3);
      $query = "SELECT
                  a.*,
                  b.nama,
                  b.id_category2
                FROM
                  sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2
                WHERE a.no_so='".$no_so."' ORDER BY a.product ASC";
      $result = $this->db->query($query)->result_array();
      $num_r = $this->db->query($query)->num_rows();
      $d_Header = "<div class='box box-primary'>";
        	$d_Header .= "<div class='box-body'>";
          $d_Header .= "<table class='table table-bordered table-striped'>";
          $d_Header .= "<thead>";
          $d_Header .= "<tr>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='5%'>#</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;'>Product Name</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='10%'>Qty Packing List</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='10%'>Available Finish Good</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='10%'>Qty Delivery</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='20%'>Daycode</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='10%'>Qty Balance</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='10%'>Remarks</th>";
            $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='10%'>Tgl. Delivery</th>";
          $d_Header .= "</tr>";
          $d_Header .= "</thead>";
          $d_Header .= "<tbody>";
          foreach ($result as $key => $value) { $key++;
            $qty_order = $value['qty_order'] - $value['qty_delivery'];
            $costcenter_last = get_last_costcenter_warehouse($value['id_category2']);
            $daycode = $this->db->query(" SELECT
                                            a.code
                                          FROM
                                            report_produksi_daily_detail a
                                            LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi
                                          WHERE
                                            b.id_costcenter='".$costcenter_last."'
                                            AND a.id_produksi_h=b.id_produksi_h
                                            AND a.id_product='".$value['id_category2']."'
                                            AND a.ket='good'
                                            AND a.sts_daycode='N'
                                          GROUP BY a.code ")->result_array();


            $d_Header .= "<tr>";
              $d_Header .= "<td class='text-center'>".$key."</td>";
              $d_Header .= "<td>".strtoupper($value['nama'])."</td>";
              $d_Header .= "<td class='text-center'>".$value['qty_order']."</td>";
              $d_Header .= "<td class='text-right'><input type='text' name='detail[".$key."][qty_available]' id='qty_stock_".$key."' data-no='".$key."' class='form-control input-md text-center maskM' value='".get_stock($value['product'])."' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
              $d_Header .= "<td class='text-right'><input type='text' name='detail[".$key."][qty_delivery]' readonly id='deliv_".$key."' data-no='".$key."' class='form-control input-md text-center qty_delivery' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
              $d_Header .= "<td class='text-right'>
                            <select name='detail[".$key."][daycode][]' id='daycod_".$key."' data-no='".$key."' class='chosen_select form-control inline-block selMax' multiple>";
                            foreach($daycode AS $vd => $vcode){
                              $d_Header .= "<option value='".$vcode['code']."'>".$vcode['code']."</option>";
                            }
              $d_Header .= "</select></td>";
              $d_Header .= "<td>
                            <input type='hidden' name='detail[".$key."][id_so_detail]' class='form-control input-md' value='".$value['id']."'>
                            <input type='hidden' name='detail[".$key."][product]' class='form-control input-md' value='".$value['product']."'>
                            <input type='hidden' name='detail[".$key."][qty_order]' id='qty_order_".$key."' class='form-control input-md' value='".$value['qty_order']."'>
                            <input type='hidden' name='detail[".$key."][qty_kurang2]' id='qty_kurang2_".$key."' class='form-control input-md text-center' readonly value='".$qty_order."'>
                            <input type='text' name='detail[".$key."][qty_kurang]' id='qty_kurang_".$key."' class='form-control input-md text-center' readonly value='".$qty_order."'>
                            </td>";
              $d_Header .= "<td class='text-right'><input type='text' name='detail[".$key."][remarks]' id='remarks_".$key."' data-no='".$key."' class='form-control input-md text-center'></td>";
              $d_Header .= "<td class='text-right'><input type='text' name='detail[".$key."][tanggal]' id='tanggal_".$key."' data-no='".$key."' class='form-control input-md text-center datepicker' readonly value='".get_name_field('sales_order_header', 'delivery_date', 'no_so', $no_so)."'></td>";
            $d_Header .= "</tr>";
          }
          if($num_r < 1){
            $d_Header .= "<tr>";
            $d_Header .= "<td colspan='6'>Data not found ...</td>";
            $d_Header .= "/tr>";
          }
          $d_Header .= "</tbody>";
          $d_Header .= "</table>";
          $d_Header .= "</div>";
      $d_Header .= "</div>";


  		 echo json_encode(array(
  				'header'			=> $d_Header,
          'num' => $num_r,
          'plan_delivery' => strtoupper(get_name_field('sales_order_header', 'shipping', 'no_so', $no_so))
  		 ));
  	}

    public function detail_delivery(){
      // $this->auth->restrict($this->viewPermission);
      $no_delivery 	= $this->input->post('no_delivery');

      $detail = $this->db->query("SELECT * FROM delivery_detail WHERE no_delivery='".$no_delivery."'")->result_array();
      $header = $this->db->query("SELECT * FROM delivery_header WHERE no_delivery='".$no_delivery."'")->result();

      // print_r($header);
      $data = [
        'detail' => $detail,
        'header'=> $header
      ];
      $this->template->set('results', $data);
      $this->template->render('detail_delivery', $data);
    }

    public function excel_report_delivery(){
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');
  		$no_delivery		= $this->uri->segment(3);

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

      // $product    = $this->db->query("SELECT a.* FROM sales_order_detail a WHERE a.no_so = '".$no_so."' ORDER BY a.product")->result_array();
      $detail = $this->db->query("SELECT * FROM delivery_detail WHERE no_delivery='".$no_delivery."'")->result_array();

  		$Row		= 1;
  		$NewRow		= $Row+1;
  		$Col_Akhir	= $Cols	= getColsChar(8);
  		$sheet->setCellValue('A'.$Row, 'DELIVERY '.$no_delivery);
  		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
  		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

  		$NewRow	= $NewRow +2;
  		$NextRow= $NewRow +1;

  		$sheet->setCellValue('A'.$NewRow, 'No');
  		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
  		$sheet->getColumnDimension('A')->setAutoSize(true);

  		$sheet->setCellValue('B'.$NewRow, 'Product Name');
  		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
  		$sheet->getColumnDimension('B')->setAutoSize(true);

  		$sheet->setCellValue('C'.$NewRow, 'Qty Order');
  		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
  		$sheet->getColumnDimension('C')->setAutoSize(true);

  		$sheet->setCellValue('D'.$NewRow, 'Qty Propose');
  		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
  		$sheet->getColumnDimension('D')->setAutoSize(true);

  		$sheet->setCellValue('E'.$NewRow, 'Qty Delivery');
  		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
  		$sheet->getColumnDimension('E')->setAutoSize(true);

      $sheet->setCellValue('F'.$NewRow, 'Qty Balance');
  		$sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
  		$sheet->getColumnDimension('F')->setAutoSize(true);

      $sheet->setCellValue('G'.$NewRow, 'Remarks');
  		$sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
  		$sheet->getColumnDimension('G')->setAutoSize(true);

      $sheet->setCellValue('H'.$NewRow, 'Daycode');
  		$sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('H'.$NewRow.':H'.$NextRow);
  		$sheet->getColumnDimension('H')->setAutoSize(true);




      if($detail){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($detail as $key => $value){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

          $list_daycode = $this->db->query("SELECT * FROM cek_daycode_delivery WHERE delivery_code='".$value['no_delivery']."' AND id_product='".$value['product']."'")->result_array();
          $dtListArray = array();
          foreach($list_daycode AS $val => $valx){
            $dtListArray[$val] = $valx['daycode'];
          }
          $dtImplode	= implode(", ", $dtListArray);

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$est_material	= strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= number_format($value['qty_propose']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= number_format($value['qty_order']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= number_format($value['qty_delivery']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $bal = ($value['qty_propose'] - $value['qty_delivery']);
          $balance = ($bal < 0)?0:$bal;

          $awal_col++;
  				$est_material	= $balance;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $remaks = (!empty($value['remarks']))?$value['remarks']:'-';

          $awal_col++;
  				$est_material	= $remaks;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= $dtImplode;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  			}
  		}





  		$sheet->setTitle('Excel Report '.$no_delivery);
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
  		header('Content-Disposition: attachment;filename="Delivery '.$no_delivery.' '.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function excel_report_delivery_all(){
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

      $detail = $this->db->query("SELECT a.*, b.* FROM delivery_detail a LEFT JOIN delivery_header b ON a.no_delivery=b.no_delivery ")->result_array();

  		$Row		= 1;
  		$NewRow		= $Row+1;
  		$Col_Akhir	= $Cols	= getColsChar(12);
  		$sheet->setCellValue('A'.$Row, 'DELIVERY');
  		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
  		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

  		$NewRow	= $NewRow +2;
  		$NextRow= $NewRow +1;

  		$sheet->setCellValue('A'.$NewRow, 'No');
  		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
  		$sheet->getColumnDimension('A')->setAutoSize(true);

  		$sheet->setCellValue('B'.$NewRow, 'Code Delivery');
  		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
  		$sheet->getColumnDimension('B')->setAutoSize(true);

  		$sheet->setCellValue('C'.$NewRow, 'Customer Name');
  		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
  		$sheet->getColumnDimension('C')->setAutoSize(true);

  		$sheet->setCellValue('D'.$NewRow, 'Delivery Date');
  		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
  		$sheet->getColumnDimension('D')->setAutoSize(true);

  		$sheet->setCellValue('E'.$NewRow, 'Shipping By');
  		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
  		$sheet->getColumnDimension('E')->setAutoSize(true);

      $sheet->setCellValue('F'.$NewRow, 'Product Name');
  		$sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
  		$sheet->getColumnDimension('F')->setAutoSize(true);

      $sheet->setCellValue('G'.$NewRow, 'Qty Order');
  		$sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
  		$sheet->getColumnDimension('G')->setAutoSize(true);

      $sheet->setCellValue('H'.$NewRow, 'Qty Propose');
  		$sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('H'.$NewRow.':H'.$NextRow);
  		$sheet->getColumnDimension('H')->setAutoSize(true);

      $sheet->setCellValue('I'.$NewRow, 'Qty Delivery');
  		$sheet->getStyle('I'.$NewRow.':I'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('I'.$NewRow.':I'.$NextRow);
  		$sheet->getColumnDimension('I')->setAutoSize(true);

      $sheet->setCellValue('J'.$NewRow, 'Qty Balance');
  		$sheet->getStyle('J'.$NewRow.':J'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('J'.$NewRow.':J'.$NextRow);
  		$sheet->getColumnDimension('J')->setAutoSize(true);

      $sheet->setCellValue('K'.$NewRow, 'Remarks');
  		$sheet->getStyle('K'.$NewRow.':K'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('K'.$NewRow.':K'.$NextRow);
  		$sheet->getColumnDimension('K')->setAutoSize(true);

      $sheet->setCellValue('L'.$NewRow, 'Daycode');
  		$sheet->getStyle('L'.$NewRow.':L'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('L'.$NewRow.':L'.$NextRow);
  		$sheet->getColumnDimension('L')->setAutoSize(true);




      if($detail){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($detail as $key => $value){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

          $list_daycode = $this->db->query("SELECT * FROM cek_daycode_delivery WHERE delivery_code='".$value['no_delivery']."' AND id_product='".$value['product']."'")->result_array();
          $dtListArray = array();
          foreach($list_daycode AS $val => $valx){
            $dtListArray[$val] = $valx['daycode'];
          }
          $dtImplode	= implode(", ", $dtListArray);

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= $value['no_delivery'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= strtoupper(get_name('master_customer','name_customer','id_customer',$value['code_cust']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= $value['delivery_real'];;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= strtoupper($value['shipping']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$est_material	= strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= number_format($value['qty_propose']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= number_format($value['qty_order']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= number_format($value['qty_delivery']);
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $bal = ($value['qty_propose'] - $value['qty_delivery']);
          $balance = ($bal < 0)?0:$bal;

          $awal_col++;
  				$est_material	= $balance;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $remaks = (!empty($value['remarks']))?$value['remarks']:'-';

          $awal_col++;
  				$est_material	= $remaks;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$est_material	= $dtImplode;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_material);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  			}
  		}





  		$sheet->setTitle('Excel Report Delivery');
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
  		header('Content-Disposition: attachment;filename="Delivery '.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    //==========================================================================================================
    //============================================ADJUSTMENT====================================================
    //==========================================================================================================

    public function adjustment(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index adjustment");
      $this->template->title('Adjustment');
      $this->template->render('adjustment');
    }

    public function data_side_adjustment(){
  		$this->warehouse_product_model->get_json_adjustment();
  	}

    public function add_adjustment(){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');
    		$detail			  = $data['daycode'];
        $costcenter		= $data['costcenter'];
        $id_product	  = $data['id_product'];
        $adjustment		= $data['adjustment'];
        $qty			    = $data['qty'];
        $stock_awal	  = $data['stock_awal'];
        $stock_akhir	= $data['stock_akhir'];
        $reason	      = strtolower($data['reason']);

        $Ym           = date('ym');
        $srcMtr			  = "SELECT MAX(kd_adjustment) as maxP FROM warehouse_product_adjustment WHERE kd_adjustment LIKE 'AD".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 6, 6);
        $urutan2++;
        $urut2			  = sprintf('%06s',$urutan2);
        $kd_adjustment	= "AD".$Ym.$urut2;

        $ArrHeader = array(
          'kd_adjustment'   => $kd_adjustment,
          'id_product'      => $id_product,
          'costcenter'      => $costcenter,
          'adjustment'      => $adjustment,
          'qty'             => $qty,
          'stock_awal'      => $stock_awal,
          'stock_akhir'     => $stock_akhir,
          'reason'          => $reason,
          'created_by'	    => $session['id_user'],
          'created_date'	  => date('Y-m-d H:i:s')
        );

        $ArrInpPro	= array();
        $ArrDetail	= array();
        $ArrHist3	  = array();
        $ArrUpdate3	= array();
        if(!empty($detail)){
          foreach($detail AS $val => $valx){
              $ArrDetail[$val]['kd_adjustment'] = $kd_adjustment;
              $ArrDetail[$val]['id_product']    = $id_product;
              $ArrDetail[$val]['costcenter']    = $costcenter;
              $ArrDetail[$val]['product_ke']    = $val;
              $ArrDetail[$val]['daycode']       = $valx['daycode'];

              if($adjustment == 'minus'){
                $get_inp = $this->db->query(" SELECT
                                                a.*
                                              FROM
                                                report_produksi_daily_detail a
                                                LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                                              WHERE a.id_product='".$id_product."'
                                                AND a.code='".$valx['daycode']."'
                                                AND b.id_costcenter='".$costcenter."'
                                              LIMIT 1
                                                ")->result();
                $ArrInpPro[$val]['id']             = $get_inp[0]->id;
                $ArrInpPro[$val]['sts_daycode']    = 'Y';
                $ArrInpPro[$val]['delivery_by']    = $session['id_user'];
                $ArrInpPro[$val]['delivery_date']  = date('Y-m-d H:i:s');
                $ArrInpPro[$val]['delivery_code']  = $kd_adjustment;
              }

              $val++;
          }
        }

        $ArrUpdate3['qty_stock']     = $stock_akhir;
        $ArrUpdate3['update_by'] 	   = $session['id_user'];
				$ArrUpdate3['update_date'] 	 = date('Y-m-d H:i:s');

        $ArrHist3['category'] 		   = 'order';
				$ArrHist3['id_product'] 		 = $id_product;
        $ArrHist3['costcenter'] 		 = $costcenter;
        $ArrHist3['qty_stock_awal']  = $stock_awal;
				$ArrHist3['qty_stock_akhir'] = $stock_akhir;
        $ArrHist3['no_trans'] 		   = $kd_adjustment;
        $ArrHist3['update_by'] 	     = $session['id_user'];
				$ArrHist3['update_date'] 	   = date('Y-m-d H:i:s');

        // print_r($ArrHeader);
        // print_r($ArrUpdate3);
        // print_r($ArrHist3);
        // print_r($ArrDetail);
        // exit;

        $this->db->trans_start();
            $this->db->insert('warehouse_product_adjustment', $ArrHeader);
            if(!empty($ArrDetail)){
              $this->db->insert_batch('warehouse_product_adjustment_daycode', $ArrDetail);
            }

            if(!empty($ArrInpPro)){
              $this->db->update_batch('report_produksi_daily_detail', $ArrInpPro, 'id');
            }

            $this->db->where('category', 'order');
            $this->db->where('costcenter', $costcenter);
            $this->db->where('id_product', $id_product);
            $this->db->update('warehouse_product', $ArrUpdate3);

            if(get_last_costcenter_warehouse($id_product) == $costcenter){
              $this->db->where('category', 'product');
              $this->db->where('id_product', $id_product);
              $this->db->update('warehouse_product', $ArrUpdate3);
            }

            $this->db->insert('warehouse_product_history', $ArrHist3);
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
          history("Insert adjustment product ".$kd_adjustment);
        }
        echo json_encode($Arr_Data);
      }
      else{
        $this->template->title('Add Adjustment');
        $this->template->render('add_adjustment');
      }
  	}

    public function add_adjustment2(){
      if($this->input->post()){
        $data 			  = $this->input->post();
    	$session      = $this->session->userdata('app_session');
    	$daycode			= $data['daycode'];
        $costcenter		= $data['costcenter'];
        $id_product	  = $data['id_product'];
        $adjustment		= 'minus';
        $qty			    = '1';
		$get_stock 		= $this->db->get_where('warehouse_product', array('id_product'=>$id_product,'costcenter'=>$costcenter,'category'=>'order'))->result();
        
		$stock_awal	  	= (!empty($get_stock))?$get_stock[0]->qty_stock:0;
        $stock_akhir	= $stock_awal - 0;
        $reason	      = strtolower($data['reason']);

        $Ym           = date('ym');
        $srcMtr			  = "SELECT MAX(kd_adjustment) as maxP FROM warehouse_product_adjustment WHERE kd_adjustment LIKE 'AD".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 6, 6);
        $urutan2++;
        $urut2			  = sprintf('%06s',$urutan2);
        $kd_adjustment	= "AD".$Ym.$urut2;

        //header adjustment
        $ArrHeader = array(
          'kd_adjustment'   => $kd_adjustment,
          'id_product'      => $id_product,
          'costcenter'      => $costcenter,
          'adjustment'      => $adjustment,
          'qty'             => $qty,
          'stock_awal'      => $stock_awal,
          'stock_akhir'     => $stock_akhir,
          'reason'          => $reason,
          'created_by'	    => $session['username'],
          'created_date'	  => date('Y-m-d H:i:s')
        );
        //detail adjustment
        $ArrDetail = array(
          'kd_adjustment'   => $kd_adjustment,
          'id_product'      => $id_product,
          'costcenter'      => $costcenter,
          'product_ke'      => '1',
          'daycode'         => $daycode
        );

        $get_inp = $this->db->query("SELECT a.* FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                                      WHERE a.id_product='".$id_product."' AND a.code='".$daycode."' AND b.id_costcenter='".$costcenter."' LIMIT 1")->result();
        $id_detail = $get_inp[0]->id;
        //update report detail
        $ArrInpPro = array(
          'sts_daycode'   => 'Y',
          'delivery_by'      => $session['username'],
          'delivery_date'      => date('Y-m-d H:i:s'),
          'delivery_code'      => $kd_adjustment
        );

        //update stock
        $ArrUpdate3 = array(
          'qty_stock'       => $stock_akhir,
          'update_by'       => $session['username'],
          'update_date'     => date('Y-m-d H:i:s')
        );

        //history stock
        $ArrHist3['category'] 		  	= 'order';
		$ArrHist3['id_product'] 		= $id_product;
        $ArrHist3['costcenter'] 		= $costcenter;
        $ArrHist3['qty_stock_awal']  	= $stock_awal;
		$ArrHist3['qty_stock_akhir'] 	= $stock_akhir;
        $ArrHist3['no_trans'] 		   	= $kd_adjustment;
        $ArrHist3['update_by'] 	     	= $session['username'];
		$ArrHist3['update_date'] 	   	= date('Y-m-d H:i:s');

        // print_r($ArrHeader);
        // print_r($ArrDetail);
        // print_r($ArrInpPro);
        // print_r($ArrUpdate3);
        // print_r($ArrHist3);
        // exit;

        $this->db->trans_start();
            $this->db->insert('warehouse_product_adjustment', $ArrHeader);
            if(!empty($ArrDetail)){
              $this->db->insert('warehouse_product_adjustment_daycode', $ArrDetail);
            }

            if(!empty($ArrInpPro)){
              $this->db->where('code', $daycode);
              $this->db->where('id_product', $id_product);
              $this->db->update('report_produksi_daily_detail', $ArrInpPro);
            }

            $this->db->where('category', 'order');
            $this->db->where('costcenter', $costcenter);
            $this->db->where('id_product', $id_product);
            $this->db->update('warehouse_product', $ArrUpdate3);

            if(get_last_costcenter_warehouse($id_product) == $costcenter){
                $this->db->where('category', 'product');
                $this->db->where('id_product', $id_product);
                $this->db->update('warehouse_product', $ArrUpdate3);
            }

            $this->db->insert('warehouse_product_history', $ArrHist3);
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
          history("Insert adjustment product ".$kd_adjustment);
        }
        echo json_encode($Arr_Data);
      }
      else{
        $this->template->title('Add Adjustment');
        $this->template->render('add_adjustment2');
      }
  	}

    public function get_stock(){
      $costcenter = $this->uri->segment(3);
      $product    = $this->uri->segment(4);

      $stock = get_stock_wip($product, $costcenter);
      if(get_last_costcenter_warehouse($product) == $costcenter){
        $stock = get_stock($product);
      }
  		 echo json_encode(array(
  				'stock'			=> $stock
  		 ));
  	}

    public function get_daycode(){
      $jumlah = $this->uri->segment(3);
      $costcenter = $this->uri->segment(4);

      $daycode_ = "SELECT * FROM filter_daycode GROUP BY code ORDER BY code ASC";
      $rest_day = $this->db->query($daycode_)->result_array();
      $dtListArray = array();
      foreach($rest_day AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= "('".implode("','", $dtListArray)."')";

      if($costcenter == 'CC2000012'){
        $list_daycode = $this->db->query("SELECT * FROM daycode WHERE code NOT IN ".$dtImplode." ORDER BY code ASC ")->result_array();
      }

      if($costcenter <> 'CC2000012'){
        $list_daycode = $this->db->query("SELECT * FROM daycode WHERE code IN ".$dtImplode." ORDER BY code ASC ")->result_array();
      }

      $label = ($jumlah <> '0' AND $jumlah <> '')?'':'';
      $dHeader = "";
      for($a=1; $a<=$jumlah; $a++){
        $dHeader .= "<div class='form-group row'>";
          $dHeader .= "<div class='col-md-1'>";
          $dHeader .= "<label for='customer'>Product ".$a." <span class='text-red'>*</span></label>";
          $dHeader .= "</div>";
          $dHeader .= "<div class='col-md-4'>";
          $dHeader .= "<select name='daycode[$a][daycode]' id='daycode_$a'  class='form-control input-md chosen-select'>";
          $dHeader .= "<option value='0'>Select Daycode</option>";
          foreach($list_daycode AS $val => $valx){
            $dHeader .= "<option value='".$valx['code']."'>".$valx['code']."</option>";
          }
          $dHeader .= "<select>";
          $dHeader .= "</div>";

          $dHeader .= "<div class='col-md-7'>";
          $dHeader .= "</div>";
        $dHeader .= "</div>";
      }
  		 echo json_encode(array(
  				'label'			=> $label,
          'dHeader'			=> $dHeader
  		 ));
  	}

    public function get_daycode_delete(){
      $jumlah     = $this->uri->segment(3);
      $id_product = $this->uri->segment(4);
      $costcenter = $this->uri->segment(5);

      $list_daycode = $this->db->query("SELECT * FROM filter_daycode_delete WHERE id_product='".$id_product."' AND id_costcenter='".$costcenter."'")->result_array();


      $label = ($jumlah <> '0' AND $jumlah <> '')?'':'';
      $dHeader = "";
      for($a=1; $a<=$jumlah; $a++){
        $dHeader .= "<div class='form-group row'>";
          $dHeader .= "<div class='col-md-1'>";
          $dHeader .= "<label for='customer'>Product ".$a." <span class='text-red'>*</span></label>";
          $dHeader .= "</div>";
          $dHeader .= "<div class='col-md-4'>";
          $dHeader .= "<select name='daycode[$a][daycode]' id='daycode_$a'  class='form-control input-md chosen-select'>";
          $dHeader .= "<option value='0'>Select Daycode</option>";
          foreach($list_daycode AS $val => $valx){
            $dHeader .= "<option value='".$valx['code']."'>".$valx['code']."</option>";
          }
          $dHeader .= "<select>";
          $dHeader .= "</div>";

          $dHeader .= "<div class='col-md-7'>";
          $dHeader .= "</div>";
        $dHeader .= "</div>";
      }
  		 echo json_encode(array(
  				'label'			=> $label,
          'dHeader'			=> $dHeader
  		 ));
  	}

    public function get_costcenter_adjust(){
      $product = $this->uri->segment(3);

  		$sqlSup		= "SELECT b.costcenter FROM cycletime_header a LEFT JOIN cycletime_detail_header b ON a.id_time=b.id_time WHERE a.id_product ='".$product."' ";
  		$restSup	= $this->db->query($sqlSup)->result_array();

  		$option	= "<option value='0'>Select An Costcenter</option>";
  		foreach($restSup AS $val => $valx){
  			$option .= "<option value='".$valx['costcenter']."'>".strtoupper(get_name_field('ms_costcenter', 'nama_costcenter', 'id_costcenter', $valx['costcenter']))."</option>";
  		}

  		$ArrJson	= array(
  			'option' => $option
  		);
  		echo json_encode($ArrJson);
  	}

    public function get_daycode_adjust(){
      $product = $this->uri->segment(3);

      $list_daycode = "SELECT * FROM filter_daycode_delete WHERE id_product='".$product."' GROUP BY `code`";
      $restSup	= $this->db->query($list_daycode)->result_array();

  		$option	= "<option value='0'>Select An Daycode</option>";
  		foreach($restSup AS $val => $valx){
  			$option .= "<option value='".$valx['code']."'>".$valx['code']."</option>";
  		}

  		$ArrJson	= array(
  			'option' => $option
  		);
  		echo json_encode($ArrJson);
  	}

    public function get_costcenteradjust(){
      $product = $this->uri->segment(3);
      $daycode = $this->input->post('daycode');

      $list_daycode = "SELECT * FROM filter_daycode_delete WHERE id_product='".$product."' AND code = '".$daycode."' ";
      $restSup	= $this->db->query($list_daycode)->result_array();

  		$option	= "<option value='0'>Select An Costcenter</option>";
  		foreach($restSup AS $val => $valx){
  			$option .= "<option value='".$valx['id_costcenter']."'>".strtoupper(get_name_field('ms_costcenter', 'nama_costcenter', 'id_costcenter', $valx['id_costcenter']))."</option>";
  		}

  		$ArrJson	= array(
  			'option' => $option
  		);
  		echo json_encode($ArrJson);
  	}

    //=================================================================================================================
    //============================================PACKING LIST=========================================================
    //=================================================================================================================

    public function packing(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $sql_beet = $this->db->query("SELECT a.no_so, a.delivery_date FROM sales_order_header a WHERE a.delivery_date >= DATE(NOW())")->result_array();
      history("View index Packinglist");
      $data = array(
        'sales_order' => $sql_beet
      );
      $this->template->title('Packing List');
      $this->template->render('packing', $data);
    }

    public function get_packing(){
      $sales_order = $this->input->post('no_so');

      $projectX = "('".str_replace("-","','",$sales_order)."')";
      // echo $projectX; exit;
      $data_detail  = $this->db->query("SELECT a.*, SUM(a.qty_order) AS propose, b.length, b.high, b.wide, b.per_box, b.group_packing, b.weight_per_product FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so IN ".$projectX." GROUP BY a.product ORDER BY a.product ASC")->result_array();


      $d_Header = "<div class='tableFixHead' style='height:500px;'>";
      $d_Header .= "<table class='table table-bordered table-striped'>";
      $d_Header .= "<thead class='thead'>";
      $d_Header .= "<tr class='bg-blue'>";
      $d_Header .= "<th class='text-center th headcol' style='vertical-align:middle; z-index: 99999;' width='15%'>Project</th>";
      $d_Header .= "<th class='text-center th headcol' style='vertical-align:middle; z-index: 99999'>Product</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Qty Shipping</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='10%'>Carton Box Size</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Content Per Carton Box</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Carton Box Quantity</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Paper Label No</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Weight Per Product Piece</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Netto</th>";
      $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Gross</th>";
      $d_Header .= "</thead>";
      $d_Header .= "<tbody>";
      $SUM_BOX = 0;
      foreach($data_detail AS $val => $valx){

        $box_qty = 0;
        if($valx['propose'] != 0 AND $valx['per_box'] != 0){
          $box_qty = number_format($valx['propose'] / $valx['per_box'],2);
        }

        $netto = $valx['propose'] * $valx['weight_per_product'] / 1000;
        $gross = (($valx['propose'] * $valx['weight_per_product']) + 250) / 1000;
        $SUM_BOX += ceil($box_qty);

        $d_Header .= "<tr>";
          $d_Header .= "<td class='headcol'>".strtoupper(get_project_name($valx['product']))."</td>";
          $d_Header .= "<td class='headcol'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$valx['product']))."</td>";
          $d_Header .= "<td align='center'>".number_format($valx['propose'])."</td>";
          $d_Header .= "<td align='center'>".get_dimensi($valx['product'])."</td>";
          $d_Header .= "<td align='center'>".number_format($valx['per_box'])."</td>";
          $d_Header .= "<td align='center'>".ceil($box_qty)."</td>";
          $d_Header .= "<td align='center'>-</td>";
          $d_Header .= "<td align='center'>".number_format($valx['weight_per_product'],2)."</td>";
          $d_Header .= "<td align='center'>".number_format($netto,2)."</td>";
          $d_Header .= "<td align='center'>".number_format($gross,2)."</td>";
        $d_Header .= "</tr>";
      }
      $d_Header .= "</tbody>";
      $d_Header .= "</table>";
      $d_Header .= "</div>";


      $d_Header2 = "<div class='tableFixHead' style='height:500px;'>";
      $d_Header2 .= "<table class='table table-bordered table-striped'>";
      $d_Header2 .= "<thead class='thead'>";
      $d_Header2 .= "<tr class='bg-blue'>";
      $d_Header2 .= "<th class='text-center th headcol' style='vertical-align:middle; z-index: 99999;' width='15%'>Project</th>";
      $d_Header2 .= "<th class='text-center th headcol' style='vertical-align:middle; z-index: 99999'>Product</th>";
      $d_Header2 .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Length</th>";
      $d_Header2 .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Wide</th>";
      $d_Header2 .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>High</th>";
      $d_Header2 .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>CUB</th>";
      $d_Header2 .= "<th class='text-center th' style='vertical-align:middle;' width='8%'>Total CUB</th>";
      $d_Header2 .= "</thead>";
      $d_Header2 .= "<tbody>";
      $SUM_CUB = 0;
      foreach($data_detail AS $val => $valx){
        $box_qty = 0;
        if($valx['propose'] != 0 AND $valx['per_box'] != 0){
          $box_qty = number_format($valx['propose'] / $valx['per_box'],2);
        }

        $car_qty = ceil($box_qty);

        $cub = ($valx['length'] * $valx['wide'] * $valx['high']) / 1000000000;

        $SUM_CUB += $cub * $car_qty;
        $d_Header2 .= "<tr>";
          $d_Header2 .= "<td class='headcol'>".strtoupper(get_project_name($valx['product']))."</td>";
          $d_Header2 .= "<td class='headcol'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$valx['product']))."</td>";

          $d_Header2 .= "<td align='center'>".number_format($valx['length'])."</td>";
          $d_Header2 .= "<td align='center'>".number_format($valx['wide'])."</td>";
          $d_Header2 .= "<td align='center'>".number_format($valx['high'])."</td>";
          $d_Header2 .= "<td align='center'>".number_format($cub, 6)."</td>";
          $d_Header2 .= "<td align='center'>".number_format($cub * $car_qty, 3)."</td>";
        $d_Header2 .= "</tr>";
      }
      $d_Header2 .= "</tbody>";
      $d_Header2 .= "</table>";
      $d_Header2 .= "</div>";


       echo json_encode(array(
          'header'			=> $d_Header,
          'header2'			=> $d_Header2,
          'total_cub'   => number_format($SUM_CUB,3),
          'total_box'   => $SUM_BOX
       ));
    }

    public function print_packing_list(){
  		$container	= $this->uri->segment(3);
      $no_so	  	= $this->uri->segment(4);

  		$session 		= $this->session->userdata('app_session');
  		$printby		= $session['username'];

  		$data_url		= base_url();
  		$Split_Beda	= explode('/',$data_url);
  		$Jum_Beda		= count($Split_Beda);
  		$Nama_Beda	= $Split_Beda[$Jum_Beda - 2];

  		history('Print packing list '.$no_so.'/'.$container);
      $data = array(
        'nama_beda' => $Nama_Beda,
        'username'  => $printby,
        'no_so'     => $no_so,
        'container' => $container
      );
      // $this->template->render('print_packing_list', $data);
  		$this->load->view('print_packing_list', $data);
  	}

    public function modal_temp_packing_list(){
        $this->auth->restrict($this->viewPermission);
        $session  = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');
        $sql_beet = $this->db->query("SELECT * FROM temp_print WHERE category='packing list'")->result();
        $data = array(
          'data' => $sql_beet
        );
        $this->load->view('modal_temp_packing_list', $data);
    }

    public function save_temp_packing_list(){
        $data	= $this->input->post();
        $session  = $this->session->userdata('app_session');
        $id = $data['id'];

        $ArrHeader = array(
  				'prepared_by' 	  => strtolower($data['prepared_by']),
  				'checked_by' 			=> strtolower($data['checked_by']),
  				'acknowleged_by' 	=> strtolower($data['acknowleged_by']),
          'city' 	          => strtolower($data['city']),
  				'ket1' 			      => $data['ket1'],
  				'ket2' 			      => $data['ket2'],
          'ket3' 			      => $data['ket3'],
  				'created_by' 	    => $session['username'],
  				'created_date' 	  => date('Y-m-d H:i:s')
  			);

        // print_r($ArrHeader); exit;
        $this->db->trans_start();
  				$this->db->where('id', $data['id']);
  				$this->db->update('temp_print', $ArrHeader);
  			$this->db->trans_complete();

  			if($this->db->trans_status() === FALSE){
  				$this->db->trans_rollback();
  				$Arr_Data	= array(
  					'pesan'		=>'Save data failed. Please try again later ...',
  					'status'	=> 0
  				);
  			}
  			else{
  				$this->db->trans_commit();
  				$Arr_Data	= array(
  					'pesan'		=>'Save data success. Thanks ...',
  					'status'	=> 1
  				);
  				history('Edit temp print packing list : '.$data['id']);
  			}
  			echo json_encode($Arr_Data);
    }

}

?>
