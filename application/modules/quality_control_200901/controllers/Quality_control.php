<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Quality_control extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Cycletime.View';
    protected $addPermission  	= 'Cycletime.Add';
    protected $managePermission = 'Cycletime.Manage';
    protected $deletePermission = 'Cycletime.Delete';

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
        'ket'			  => $hasil,
        'ket_by'	  => $session['id_user'],
        'ket_date'	=> date('Y-m-d H:i:s')
      );

      if($tanda == 'oke'){
        $data_header    = $this->db->query("SELECT a.id_product, b.id_costcenter, a.id_produksi FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi WHERE a.id='".$id."' LIMIT 1 ")->result();

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
          $sqlH = "SELECT a.id, a.id_product, b.id_costcenter, a.id_produksi, COUNT(a.id_product) AS qty FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi WHERE a.id IN ".$dtImplode." AND a.id_produksi_h=b.id_produksi_h  GROUP BY a.id_product ";
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
              if($data_header[0]->id_costcenter == get_last_costcenter_warehouse($valx['id_product'])){
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

    public function final(){
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
