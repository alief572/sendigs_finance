<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Quality_control extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Quality_Control.View';
    protected $addPermission  	= 'Quality_Control.Add';
    protected $managePermission = 'Quality_Control.Manage';
    protected $deletePermission = 'Quality_Control.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Quality_control/quality_control_model'
                                ));
        $this->template->title('Quality Control');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    //==========================================================================================================
    //============================================QC=========================================================
    //==========================================================================================================

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index quality control");
      $this->template->title('Quality Control');
      $this->template->render('index');
    }

    public function data_side_qc(){
  		$this->quality_control_model->get_json_qc();
  	}

    public function good_bad_action(){
    	$Arr_Kembali	= array();
  		$id			      = $this->uri->segment(3);
      $tanda			  = $this->uri->segment(4);
      $hasil        = ($tanda == 'oke')?'good':'bad';
      // print_r($data);
      // exit;
  		$session 		   = $this->session->userdata('app_session');

      $ArrHeader		  = array(
        'sts_daycode'  => 'N',
        'ket'			     => $hasil,
        'ket_by'	     => $session['id_user'],
        'ket_date'	   => date('Y-m-d H:i:s')
      );

      $ArrHeader2		  = array(
        'sts_daycode'			  => 'Y'
      );

      if($tanda == 'oke'){
        $data_header    = $this->db->query("SELECT a.id_product, b.id_costcenter, a.id_produksi, a.code FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi WHERE a.id='".$id."' AND a.id_produksi_h=b.id_produksi_h LIMIT 1 ")->result();

        $sql_last_pro	   = "SELECT * FROM warehouse_product WHERE id_product = '".$data_header[0]->id_product."' AND costcenter = '".$data_header[0]->id_costcenter."' AND category = 'order' LIMIT 1";
        $rest_last_pro	 = $this->db->query($sql_last_pro)->result();
        $qty_stock       = (!empty($rest_last_pro[0]->qty_stock))?$rest_last_pro[0]->qty_stock:0;

        $ArrInsertStock	= array(
          'category'    => 'order',
          'id_product'  => $data_header[0]->id_product,
          'costcenter'  => $data_header[0]->id_costcenter,
          'qty_stock'   => $qty_stock + 1,
          'update_by'   => $session['id_user'],
          'update_date' => date('Y-m-d H:i:s')
        );

        $ArrHistProduct = array(
          'category'    => 'order',
          'id_product'  => $data_header[0]->id_product,
          'costcenter'  => $data_header[0]->id_costcenter,
          'qty_stock_awal'   => $qty_stock,
          'qty_stock_akhir'  => $qty_stock + 1,
          'no_trans'    => $id.", ".$data_header[0]->id_produksi,
          'update_by'   => $session['id_user'],
          'update_date' => date('Y-m-d H:i:s')
        );

        //update sebelumnya
        $antrial  = get_before_costcenter_warehouse($data_header[0]->id_product, $data_header[0]->id_costcenter);

        $sql_last_pro2	   = "SELECT * FROM warehouse_product WHERE id_product = '".$data_header[0]->id_product."' AND costcenter = '".$antrial."' AND category = 'order' LIMIT 1";
        $rest_last_pro2	 = $this->db->query($sql_last_pro2)->result();
        $qty_stock2       = (!empty($rest_last_pro2[0]->qty_stock))?$rest_last_pro2[0]->qty_stock:0;
        $qty_stock3 = $qty_stock2 - 1;

        $ArrInsertStock23	= array(
          'category'    => 'order',
          'id_product'  => $data_header[0]->id_product,
          'costcenter'  => $antrial,
          'qty_stock'   => $qty_stock3,
          'update_by'   => $session['id_user'],
          'update_date' => date('Y-m-d H:i:s')
        );

        $ArrHistProduct23 = array(
          'category'    => 'order',
          'id_product'  => $data_header[0]->id_product,
          'costcenter'  => $antrial,
          'qty_stock_awal'   => $qty_stock2,
          'qty_stock_akhir'  => $qty_stock3,
          'no_trans'    => $id.", ".$data_header[0]->id_produksi,
          'update_by'   => $session['id_user'],
          'update_date' => date('Y-m-d H:i:s')
        );

        if($data_header[0]->id_costcenter == get_last_costcenter_warehouse($data_header[0]->id_product)){
            $sql_last_pro2	   = "SELECT * FROM warehouse_product WHERE id_product = '".$data_header[0]->id_product."' AND category = 'product' LIMIT 1";
            $rest_last_pro2	 = $this->db->query($sql_last_pro2)->result();
            $qty_stock2       = (!empty($rest_last_pro2[0]->qty_stock))?$rest_last_pro2[0]->qty_stock:0;

            $ArrInsertStock2	= array(
              'category'    => 'product',
              'qty_stock'   => $qty_stock2 + 1,
              'update_by'   => $session['id_user'],
              'update_date' => date('Y-m-d H:i:s')
            );

            $ArrHistProduct2 = array(
              'category'    => 'product',
              'id_product'  => $data_header[0]->id_product,
              'qty_stock_awal'   => $qty_stock2,
              'qty_stock_akhir'  => $qty_stock2 + 1,
              'no_trans'    => $id.", ".$data_header[0]->id_produksi,
              'update_by'   => $session['id_user'],
              'update_date' => date('Y-m-d H:i:s')
            );
        }
      }

      // print_r($ArrHeader); CC2000001
      // print_r($ArrInsertStock);
      // print_r($ArrInsertStock23);
      // print_r($ArrHistProduct);
      // print_r($ArrHistProduct23);
      // print_r($ArrHistProduct2);
      // echo $urut."-".$data_header[0]->id_costcenter."-".$antrial;
      // echo $antrial;
      // exit;

  		$this->db->trans_start();
        $this->db->where(array('code'=>$data_header[0]->code, 'id_product'=>$data_header[0]->id_product, 'ket'=>'good'));
        $this->db->update('report_produksi_daily_detail', $ArrHeader2);

        $this->db->where('id', $id);
  			$this->db->update('report_produksi_daily_detail', $ArrHeader);

        if($tanda == 'oke'){
          $this->db->delete('warehouse_product', array('category' => 'order', 'id_product' => $data_header[0]->id_product, 'costcenter' => $data_header[0]->id_costcenter));
          $this->db->insert('warehouse_product', $ArrInsertStock);

          $this->db->insert('warehouse_product_history', $ArrHistProduct);

          if($antrial != '0'){
            $this->db->delete('warehouse_product', array('category' => 'order', 'id_product' => $data_header[0]->id_product, 'costcenter' => $antrial));
            $this->db->insert('warehouse_product', $ArrInsertStock23);

            $this->db->insert('warehouse_product_history', $ArrHistProduct23);
          }

          if($data_header[0]->id_costcenter == get_last_costcenter_warehouse($data_header[0]->id_product)){
            $this->db->where(array('category' => 'product', 'id_product' => $data_header[0]->id_product));
            $this->db->update('warehouse_product', $ArrInsertStock2);

            $this->db->insert('warehouse_product_history', $ArrHistProduct2);
          }
        }
  		$this->db->trans_complete();

  		if($this->db->trans_status() === FALSE){
  			$this->db->trans_rollback();
  			$Arr_Data	= array(
  				'pesan'		=>'Delete gagal disimpan ...',
  				'status'	=> 0
  			);
  		}
  		else{
  			$this->db->trans_commit();
  			$Arr_Data	= array(
  				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
  				'status'	=> 1
  			);
        history("Quality control process ".$id." / ".$tanda);
  		}

  		echo json_encode($Arr_Data);
  	}

  public function good_bad_action_check(){
    	$Arr_Kembali	= array();
		$data     		= $this->input->post();
		$session 		= $this->session->userdata('app_session');
  		$ket			= $this->uri->segment(3);
		$detail   		= $data['check'];
		$costcenter_h   = $data['costcenter'];
      if(!empty($detail)){
        $ArrHeader	= array();
        foreach($detail AS $val => $valx){
          $ArrHeader[$val]['id'] = $valx;
          $ArrHeader[$val]['ket'] = $ket;
          $ArrHeader[$val]['ket_by'] = $session['id_user'];
          $ArrHeader[$val]['ket_date'] = date('Y-m-d H:i:s');
        }

        $dtListArray = array();
  			foreach($detail AS $val => $valx){
  				$dtListArray[$val] = $valx;
  			}
  			$dtImplode	= "('".implode("','", $dtListArray)."')";

        // echo $costcenter_h; exit;

        if($ket == 'good'){
          $sqlH = "SELECT a.id, a.id_product, b.id_costcenter, a.code, a.id_produksi, COUNT(DISTINCT(a.code)) AS qty FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi WHERE a.id IN ".$dtImplode." AND a.id_produksi_h=b.id_produksi_h GROUP BY a.id_product ";
          // echo $sqlH;
          // exit;
          $data_header    = $this->db->query($sqlH)->result_array();
          $ArrInsertStock	= array();
          $ArrHistProduct = array();
          $ArrInsertStock23 = array();
          $ArrHistProduct23 = array();
          $ArrInsertStock2 = array();
          $ArrHistProduct2 = array();

          foreach($data_header AS $val => $valx){
            $sql_last_pro	   	= "SELECT * FROM warehouse_product WHERE id_product = '".$valx['id_product']."' AND costcenter = '".$valx['id_costcenter']."' AND category = 'order' LIMIT 1";
            $rest_last_pro	 	= $this->db->query($sql_last_pro)->result();
            $qty_stock       	= (!empty($rest_last_pro[0]->qty_stock))?$rest_last_pro[0]->qty_stock:0;

            $ArrInsertStock[$val]['category']    = 'order';
            $ArrInsertStock[$val]['id_product']  = $valx['id_product'];
            $ArrInsertStock[$val]['costcenter']  = $valx['id_costcenter'];
            $ArrInsertStock[$val]['qty_stock']   = $qty_stock + $valx['qty'];
            $ArrInsertStock[$val]['update_by']   = $session['id_user'];
            $ArrInsertStock[$val]['update_date'] = date('Y-m-d H:i:s');

            $ArrHistProduct[$val]['category']         = 'order';
            $ArrHistProduct[$val]['id_product']       = $valx['id_product'];
            $ArrHistProduct[$val]['costcenter']       = $valx['id_costcenter'];
            $ArrHistProduct[$val]['qty_stock_awal']   = $qty_stock;
            $ArrHistProduct[$val]['qty_stock_akhir']  = $qty_stock + $valx['qty'];
            $ArrHistProduct[$val]['no_trans']         = $valx['id'].", ".$valx['id_produksi']."-".$valx['code']."-".$valx['id_product'];
            $ArrHistProduct[$val]['update_by']        = $session['id_user'];
            $ArrHistProduct[$val]['update_date']      = date('Y-m-d H:i:s');

            //update sebelumnya
      			if($valx['id_costcenter'] != 'CC2000012'){
      				$antrial  			= get_before_costcenter_warehouse($valx['id_product'], $valx['id_costcenter']);

      				$sql_last_pro2	  	= "SELECT * FROM warehouse_product WHERE id_product = '".$valx['id_product']."' AND costcenter = '".$antrial."' AND category = 'order' LIMIT 1";
      				$rest_last_pro2		= $this->db->query($sql_last_pro2)->result();
      				$qty_stock2       	= (!empty($rest_last_pro2[0]->qty_stock))?$rest_last_pro2[0]->qty_stock:0;
      				$qty_stock3 		= $qty_stock2 - $valx['qty'];

      				// echo $valx['id_product'].'<br>';
      				// echo $valx['id_costcenter'].'<br>';
      				// echo $antrial.'<br>';

      				$ArrInsertStock23['category']       = 'order';
      				$ArrInsertStock23['id_product']     = $valx['id_product'];
      				$ArrInsertStock23['costcenter']     = $antrial;
      				$ArrInsertStock23['qty_stock']      = $qty_stock3;
      				$ArrInsertStock23['update_by']      = $session['id_user'];
      				$ArrInsertStock23['update_date']    = date('Y-m-d H:i:s');

      				$ArrHistProduct23[$val]['category']       = 'order';
      				$ArrHistProduct23[$val]['id_product']     = $valx['id_product'];
      				$ArrHistProduct23[$val]['costcenter']     = $antrial;
      				$ArrHistProduct23[$val]['qty_stock_awal'] = $qty_stock2;
      				$ArrHistProduct23[$val]['qty_stock_akhir']= $qty_stock3;
      				$ArrHistProduct23[$val]['no_trans']       = $valx['id'].", ".$valx['id_produksi']."-".$valx['code']."-".$valx['id_product'];
      				$ArrHistProduct23[$val]['update_by']      = $session['id_user'];
      				$ArrHistProduct23[$val]['update_date']    = date('Y-m-d H:i:s');

      				//terpaksa taruh sini
      				$this->db->where('category', 'order');
      				$this->db->where('id_product', $valx['id_product']);
      				$this->db->where('costcenter', $antrial);
      				$this->db->update('warehouse_product', $ArrInsertStock23);
      			}

            if($valx['id_costcenter'] == get_last_costcenter_warehouse($valx['id_product'])){
                $sql_last_pro2	   = "SELECT * FROM warehouse_product WHERE id_product = '".$valx['id_product']."' AND category = 'product' LIMIT 1";
                $rest_last_pro2	 = $this->db->query($sql_last_pro2)->result();
                $qty_stock2       = (!empty($rest_last_pro2[0]->qty_stock))?$rest_last_pro2[0]->qty_stock:0;

                $ArrInsertStock2[$val]['category']        = 'product';
                $ArrInsertStock2[$val]['id_product']      = $valx['id_product'];
                $ArrInsertStock2[$val]['qty_stock']       = $qty_stock2 + $valx['qty'];
                $ArrInsertStock2[$val]['update_by']       = $session['id_user'];
                $ArrInsertStock2[$val]['update_date']     = date('Y-m-d H:i:s');

                $ArrHistProduct2[$val]['category']        = 'product';
                $ArrHistProduct2[$val]['id_product']      = $valx['id_product'];
                $ArrHistProduct2[$val]['qty_stock_awal']  = $qty_stock2;
                $ArrHistProduct2[$val]['qty_stock_akhir'] = $qty_stock2 + $valx['qty'];
                $ArrHistProduct2[$val]['no_trans']        = $valx['id'].", ".$valx['id_produksi']."-".$valx['code']."-".$valx['id_product'];
                $ArrHistProduct2[$val]['update_by']       = $session['id_user'];
                $ArrHistProduct2[$val]['update_date']     = date('Y-m-d H:i:s');

                //mengurangi stock material
                $sql_min  = "SELECT a.code_material, a.weight FROM bom_detail a LEFT JOIN bom_header b ON a.no_bom=b.no_bom WHERE b.id_product = '".$valx['id_product']."' AND a.code_material <> '' AND a.code_material IS NOT NULL";
                // echo $sql_min; exit;
                $rest_min	= $this->db->query($sql_min)->result_array();
                $ArrStockSup = array();
                $ArrHistSub = array();
                $ArrStockSupIns = array();
                $ArrHistSubIns = array();
                foreach($rest_min AS $vmin => $vminx){
                  $gud_subgudang	= "SELECT a.* FROM  warehouse_stock a WHERE a.id_material = '".$vminx['code_material']."' AND a.id_gudang='3'";
                  $rest_subgudang	= $this->db->query($gud_subgudang)->result();
                  $berat = (!empty($vminx['weight']))?$vminx['weight']:0;
                  if(!empty($rest_subgudang)){
          					//update stock sub gudang
          					$ArrStockSup[$val]['id'] 			     = $rest_subgudang[0]->id;
          					$ArrStockSup[$val]['qty_stock'] 	 = $rest_subgudang[0]->qty_stock - ($vminx['weight'] * $valx['qty']);
          					$ArrStockSup[$val]['update_by'] 	  = $session['username'];
          					$ArrStockSup[$val]['update_date'] 	= date('Y-m-d H:i:s');

          					$ArrHistSub[$val]['id_material'] 		= $rest_subgudang[0]->id_material;
          					$ArrHistSub[$val]['idmaterial'] 		= $rest_subgudang[0]->idmaterial;
          					$ArrHistSub[$val]['nm_material'] 		= $rest_subgudang[0]->nm_material;
          					$ArrHistSub[$val]['id_gudang'] 			= '3';
          					$ArrHistSub[$val]['kd_gudang'] 			= get_name('warehouse', 'kd_gudang', 'id', '3');
          					$ArrHistSub[$val]['id_gudang_dari'] 	= '3';
          					$ArrHistSub[$val]['kd_gudang_dari'] 	= get_name('warehouse', 'kd_gudang', 'id', '3');
          					$ArrHistSub[$val]['id_gudang_ke'] 		= '';
          					$ArrHistSub[$val]['kd_gudang_ke'] 		= 'PEMAKAIAN';
          					$ArrHistSub[$val]['qty_stock_awal'] 	= $rest_subgudang[0]->qty_stock;
          					$ArrHistSub[$val]['qty_stock_akhir'] 	= $rest_subgudang[0]->qty_stock - ($berat * $valx['qty']);
          					$ArrHistSub[$val]['qty_booking_awal'] 	= $rest_subgudang[0]->qty_booking;
          					$ArrHistSub[$val]['qty_booking_akhir'] 	= $rest_subgudang[0]->qty_booking;
          					$ArrHistSub[$val]['qty_rusak_awal'] 	= $rest_subgudang[0]->qty_rusak;
          					$ArrHistSub[$val]['qty_rusak_akhir'] 	= $rest_subgudang[0]->qty_rusak;
          					$ArrHistSub[$val]['no_ipp'] 			  = $valx['id'].", ".$valx['id_produksi']."-".$valx['code']."-".$valx['id_product'];
          					$ArrHistSub[$val]['jumlah_mat'] 		= $berat * $valx['qty'];
          					$ArrHistSub[$val]['ket'] 				    = 'pengurangan pemakaian material';
          					$ArrHistSub[$val]['update_by'] 			= $session['username'];
          					$ArrHistSub[$val]['update_date'] 		= date('Y-m-d H:i:s');

                    $this->db->update_batch('warehouse_stock', $ArrStockSup, 'id');
            				$this->db->insert_batch('warehouse_history', $ArrHistSub);
          				}

                  if(empty($rest_subgudang)){
          					$sql_mat	= "	SELECT a.* FROM ms_material a WHERE a.code_material = '".$vminx['code_material']."' LIMIT 1";
          					$rest_mat	= $this->db->query($sql_mat)->result();
                    if(!empty($rest_mat)){
                      // echo $sql_mat; exit;
            					//update stock sub gudang
            					$ArrStockSupIns[$val]['id_material'] 	= $rest_mat[0]->code_material;
            					$ArrStockSupIns[$val]['idmaterial'] 	= $rest_mat[0]->code_company;
            					$ArrStockSupIns[$val]['nm_material'] 	= $rest_mat[0]->nm_material;
            					$ArrStockSupIns[$val]['id_gudang'] 		= '3';
            					$ArrStockSupIns[$val]['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', '3');
            					$ArrStockSupIns[$val]['qty_stock'] 		= 0 - ($berat * $valx['qty']);
            					$ArrStockSupIns[$val]['update_by'] 		= $session['username'];
            					$ArrStockSupIns[$val]['update_date'] 	= date('Y-m-d H:i:s');

            					$ArrHistSubIns[$val]['id_material'] 	= $rest_mat[0]->code_material;
            					$ArrHistSubIns[$val]['idmaterial'] 		= $rest_mat[0]->code_company;
            					$ArrHistSubIns[$val]['nm_material'] 	= $rest_mat[0]->nm_material;
                      $ArrHistSubIns[$val]['id_gudang'] 			= '3';
                      $ArrHistSubIns[$val]['kd_gudang'] 			= get_name('warehouse', 'kd_gudang', 'id', '3');
                      $ArrHistSubIns[$val]['id_gudang_dari'] 	= '3';
                      $ArrHistSubIns[$val]['kd_gudang_dari'] 	= get_name('warehouse', 'kd_gudang', 'id', '3');
                      $ArrHistSubIns[$val]['id_gudang_ke'] 		= '';
                      $ArrHistSubIns[$val]['kd_gudang_ke'] 		= 'PEMAKAIAN';
            					$ArrHistSubIns[$val]['qty_stock_awal'] 	= 0;
            					$ArrHistSubIns[$val]['qty_stock_akhir'] 	= 0 - ($berat * $valx['qty']);
            					$ArrHistSubIns[$val]['qty_booking_awal'] 	= 0;
            					$ArrHistSubIns[$val]['qty_booking_akhir'] = 0;
            					$ArrHistSubIns[$val]['qty_rusak_awal'] 		= 0;
            					$ArrHistSubIns[$val]['qty_rusak_akhir'] 	= 0;
            					$ArrHistSubIns[$val]['no_ipp'] 				= $valx['id'].", ".$valx['id_produksi']."-".$valx['code']."-".$valx['id_product'];
            					$ArrHistSubIns[$val]['jumlah_mat'] 			= $berat * $valx['qty'];
            					$ArrHistSubIns[$val]['ket'] 				= '(insert new) pengurangan pemakaian material';
            					$ArrHistSubIns[$val]['update_by'] 			= $session['username'];
            					$ArrHistSubIns[$val]['update_date'] 		= date('Y-m-d H:i:s');

                      $this->db->insert_batch('warehouse_stock', $ArrStockSupIns);
                      $this->db->insert_batch('warehouse_history', $ArrHistSubIns);
                    }
          				}


                }

            }

          }
        }
		// exit;
        // print_r($ArrHeader);
        // print_r($ArrInsertStock);
        // print_r($ArrHistProduct);
        // print_r($ArrInsertStock23);
        // print_r($ArrHistProduct23);
        // print_r($ArrInsertStock2);
        // print_r($ArrHistProduct2);
        // exit;

    		$this->db->trans_start();
    			$this->db->update_batch('report_produksi_daily_detail', $ArrHeader, 'id');

          if($ket == 'good'){
			      $this->db->where('category', 'order');
			      $this->db->where('costcenter', $costcenter_h);
            $this->db->update_batch('warehouse_product', $ArrInsertStock, 'id_product');
            $this->db->insert_batch('warehouse_product_history', $ArrHistProduct);

            if(!empty($ArrHistProduct23)){
              $this->db->insert_batch('warehouse_product_history', $ArrHistProduct23);
            }

            // foreach($data_header AS $val => $valx){
            //   if($valx['id_costcenter'] == get_last_costcenter_warehouse($valx['id_product'])){
                if(!empty($ArrInsertStock2)){
                  $this->db->where('category','product');
                  $this->db->update_batch('warehouse_product', $ArrInsertStock2, 'id_product');
                }
                if(!empty($ArrHistProduct2)){
                  $this->db->insert_batch('warehouse_product_history', $ArrHistProduct2);
                }

            //   }
            // }
          }
    		$this->db->trans_complete();

    		if($this->db->trans_status() === FALSE){
    			$this->db->trans_rollback();
    			$Arr_Data	= array(
    				'pesan'		=>'Delete gagal disimpan ...',
    				'status'	=> 0
    			);
    		}
    		else{
    			$this->db->trans_commit();
    			$Arr_Data	= array(
    				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
    				'status'	=> 1
    			);
          history("Quality control process ".$dtImplode." / ".$ket);
    		}
      }
      else{
        $Arr_Data	= array(
          'pesan'		=>'No data is processed ...',
          'status'	=> 0
        );
      }

  		echo json_encode($Arr_Data);
  	}

    public function good_bad_action_check_before(){
    	$Arr_Kembali	= array();
      $data     = $this->input->post();
      $session 	= $this->session->userdata('app_session');
  		$ket			= $this->uri->segment(3);
      $detail   = $data['check'];
      if(!empty($detail)){
        $ArrHeader	= array();
        foreach($detail AS $val => $valx){
          $ArrHeader[$val]['id'] = $valx;
          $ArrHeader[$val]['ket'] = $ket;
          $ArrHeader[$val]['ket_by'] = $session['id_user'];
          $ArrHeader[$val]['ket_date'] = date('Y-m-d H:i:s');
        }

        $dtListArray = array();
  			foreach($detail AS $val => $valx){
  				$dtListArray[$val] = $valx;
  			}
  			$dtImplode	= "('".implode("','", $dtListArray)."')";

        // echo $dtImplode; exit;

        if($ket == 'good'){
          $sqlH = "SELECT a.id, a.id_product, b.id_costcenter, a.id_produksi, COUNT(a.id_product) AS qty FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi WHERE a.id IN ".$dtImplode." AND a.id_produksi_h=b.id_produksi_h GROUP BY a.id_product ";
          // echo $sqlH;
          // exit;
          $data_header    = $this->db->query($sqlH)->result_array();
          $ArrInsertStock	= array();
          $ArrHistProduct = array();
          $ArrInsertStock23 = array();
          $ArrHistProduct23 = array();
          $ArrInsertStock2 = array();
          $ArrHistProduct2 = array();

          foreach($data_header AS $val => $valx){
            $sql_last_pro	   = "SELECT * FROM warehouse_product WHERE id_product = '".$valx['id_product']."' AND costcenter = '".$valx['id_costcenter']."' AND category = 'order' LIMIT 1";
            $rest_last_pro	 = $this->db->query($sql_last_pro)->result();
            $qty_stock       = (!empty($rest_last_pro[0]->qty_stock))?$rest_last_pro[0]->qty_stock:0;

            $ArrInsertStock[$val]['category']    = 'order';
            $ArrInsertStock[$val]['id_product']  = $valx['id_product'];
            $ArrInsertStock[$val]['costcenter']  = $valx['id_costcenter'];
            $ArrInsertStock[$val]['qty_stock']   = $qty_stock + $valx['qty'];
            $ArrInsertStock[$val]['update_by']   = $session['id_user'];
            $ArrInsertStock[$val]['update_date'] = date('Y-m-d H:i:s');

            $ArrHistProduct[$val]['category']         = 'order';
            $ArrHistProduct[$val]['id_product']       = $valx['id_product'];
            $ArrHistProduct[$val]['costcenter']       = $valx['id_costcenter'];
            $ArrHistProduct[$val]['qty_stock_awal']   = $qty_stock;
            $ArrHistProduct[$val]['qty_stock_akhir']  = $qty_stock + $valx['qty'];
            $ArrHistProduct[$val]['no_trans']         = $valx['id'].", ".$valx['id_produksi'];
            $ArrHistProduct[$val]['update_by']        = $session['id_user'];
            $ArrHistProduct[$val]['update_date']      = date('Y-m-d H:i:s');

            //update sebelumnya
            $antrial  = get_before_costcenter_warehouse($valx['id_product'], $valx['id_costcenter']);

            $sql_last_pro2	   = "SELECT * FROM warehouse_product WHERE id_product = '".$valx['id_product']."' AND costcenter = '".$antrial."' AND category = 'order' LIMIT 1";
            $rest_last_pro2	 = $this->db->query($sql_last_pro2)->result();
            $qty_stock2       = (!empty($rest_last_pro2[0]->qty_stock))?$rest_last_pro2[0]->qty_stock:0;
            $qty_stock3 = $qty_stock2 - $valx['qty'];

            $ArrInsertStock23[$val]['category']       = 'order';
            $ArrInsertStock23[$val]['id_product']     = $valx['id_product'];
            $ArrInsertStock23[$val]['costcenter']     = $antrial;
            $ArrInsertStock23[$val]['qty_stock']      = $qty_stock3;
            $ArrInsertStock23[$val]['update_by']      = $session['id_user'];
            $ArrInsertStock23[$val]['update_date']    = date('Y-m-d H:i:s');

            $ArrHistProduct23[$val]['category']       = 'order';
            $ArrHistProduct23[$val]['id_product']     = $valx['id_product'];
            $ArrHistProduct23[$val]['costcenter']     = $antrial;
            $ArrHistProduct23[$val]['qty_stock_awal'] = $qty_stock2;
            $ArrHistProduct23[$val]['qty_stock_akhir']= $qty_stock3;
            $ArrHistProduct23[$val]['no_trans']       = $dtListArray.", ".$valx['id_produksi'];
            $ArrHistProduct23[$val]['update_by']      = $session['id_user'];
            $ArrHistProduct23[$val]['update_date']    = date('Y-m-d H:i:s');

            if($valx['id_costcenter'] == get_last_costcenter_warehouse($valx['id_product'])){
                $sql_last_pro2	   = "SELECT * FROM warehouse_product WHERE id_product = '".$valx['id_product']."' AND category = 'product' LIMIT 1";
                $rest_last_pro2	 = $this->db->query($sql_last_pro2)->result();
                $qty_stock2       = (!empty($rest_last_pro2[0]->qty_stock))?$rest_last_pro2[0]->qty_stock:0;

                $ArrInsertStock2[$val]['category']        = 'product';
                $ArrInsertStock2[$val]['id_product']      = $valx['id_product'];
                $ArrInsertStock2[$val]['qty_stock']       = $qty_stock2 + $valx['qty'];
                $ArrInsertStock2[$val]['update_by']       = $session['id_user'];
                $ArrInsertStock2[$val]['update_date']     = date('Y-m-d H:i:s');

                $ArrHistProduct2[$val]['category']        = 'product';
                $ArrHistProduct2[$val]['id_product']      = $valx['id_product'];
                $ArrHistProduct2[$val]['qty_stock_awal']  = $qty_stock2;
                $ArrHistProduct2[$val]['qty_stock_akhir'] = $qty_stock2 + $valx['qty'];
                $ArrHistProduct2[$val]['no_trans']        = $dtListArray.", ".$valx['id_produksi'];
                $ArrHistProduct2[$val]['update_by']       = $session['id_user'];
                $ArrHistProduct2[$val]['update_date']     = date('Y-m-d H:i:s');
            }

          }
        }

        // print_r($ArrHeader);
        // print_r($ArrInsertStock);
        // print_r($ArrHistProduct);
        // print_r($ArrInsertStock23);
        // print_r($ArrHistProduct23);
        // print_r($ArrInsertStock2);
        // print_r($ArrHistProduct2);
        // exit;

    		$this->db->trans_start();
    			$this->db->update_batch('report_produksi_daily_detail', $ArrHeader, 'id');

          if($ket == 'good'){
            foreach($data_header AS $val => $valx){
              $this->db->delete('warehouse_product', array('category' => 'order', 'id_product' => $valx['id_product'], 'costcenter' => $valx['id_costcenter']));
            }
            $this->db->insert_batch('warehouse_product', $ArrInsertStock);
            $this->db->insert_batch('warehouse_product_history', $ArrHistProduct);

            if($antrial != '0'){
              foreach($data_header AS $val => $valx){
                $antrial  = get_before_costcenter_warehouse($valx['id_product'], $valx['id_costcenter']);
                $this->db->delete('warehouse_product', array('category' => 'order', 'id_product' => $valx['id_product'], 'costcenter' => $antrial));
              }
              $this->db->insert_batch('warehouse_product', $ArrInsertStock23);
              $this->db->insert_batch('warehouse_product_history', $ArrHistProduct23);
            }

            foreach($data_header AS $val => $valx){
              if($valx['id_costcenter'] == get_last_costcenter_warehouse($valx['id_product'])){
                $this->db->where('category','product');
                $this->db->update_batch('warehouse_product', $ArrInsertStock2, 'id_product');
                $this->db->insert_batch('warehouse_product_history', $ArrHistProduct2);
              }
            }
          }
    		$this->db->trans_complete();

    		if($this->db->trans_status() === FALSE){
    			$this->db->trans_rollback();
    			$Arr_Data	= array(
    				'pesan'		=>'Delete gagal disimpan ...',
    				'status'	=> 0
    			);
    		}
    		else{
    			$this->db->trans_commit();
    			$Arr_Data	= array(
    				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
    				'status'	=> 1
    			);
          history("Quality control process ".$dtImplode." / ".$ket);
    		}
      }
      else{
        $Arr_Data	= array(
          'pesan'		=>'No data is processed ...',
          'status'	=> 0
        );
      }

  		echo json_encode($Arr_Data);
  	}

    //==========================================================================================================
    //============================================QC FINAL=========================================================
    //==========================================================================================================

    public function final_(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index quality control final");
      $this->template->title('Quality Control Final');
      $this->template->render('final');
    }

    public function data_side_qc_final(){
  		$this->quality_control_model->get_json_qc_final();
  	}

}

?>
