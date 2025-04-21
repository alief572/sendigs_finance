<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Warehouse_material extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Warehouse_Material.View';
    protected $addPermission  	= 'Warehouse_Material.Add';
    protected $managePermission = 'Warehouse_Material.Manage';
    protected $deletePermission = 'Warehouse_Material.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Warehouse_material/warehouse_material_model'
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
      history("View index masterial stock");
      $this->template->title('Materials Stock');
      $this->template->render('stock');
    }

    public function data_side_stock(){
  		$this->warehouse_material_model->get_json_stock();
  	}

    public function modal_history(){
  		$this->warehouse_material_model->modal_history();
  	}

    //==========================================================================================================
    //============================================STOCK PRO=========================================================
    //==========================================================================================================

    public function stock_pro(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index masterial stock produksi");
      $this->template->title('Materials Stock Produksi');
      $this->template->render('stock_pro');
    }

    public function data_side_stock_pro(){
  		$this->warehouse_material_model->get_json_stock_pro();
  	}

    //==========================================================================================================
    //============================================WIP=========================================================
    //==========================================================================================================

    public function wip(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index masterial wip");
      $this->template->title('Material Request');
      $this->template->render('wip');
    }

    public function data_side_wip(){
  		$this->warehouse_material_model->get_json_wip();
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

        $q_header = "SELECT
                      	b.*
                      FROM
                      	produksi_planning b
                      WHERE
                      	b.no_plan = '".$data['plandate']."'";
        $resultHP = $this->db->query($q_header)->result();

        $ArrHeader = array(
          'no_wip'        => $no_wip,
          'costcenter'    => 'CC2000012',
          'date_awal'     => $resultHP[0]->date_awal,
          'date_akhir'    => $resultHP[0]->date_akhir,
          'no_plan'       => $resultHP[0]->no_plan,
          'created_by'	  => $session['username'],
          'created_date'	=> date('Y-m-d H:i:s')
        );

        $ArrDetail	= array();
        $ArrUpdateMat	= array();
        $ArrUpdatePro	= array();
        $ArrHist	= array();
        $SUM_QTY = 0;
        $SUM_PACK = 0;
        foreach($detail AS $val => $valx){

          $qty_packing  = str_replace(',','',$valx['qty_packing']);
          $qty_aktual   = str_replace(',','',$valx['qty_aktual']);

          $SUM_QTY  += $qty_aktual;
          $SUM_PACK += $qty_packing;

          $ArrDetail[$val]['no_wip']        = $no_wip;
          $ArrDetail[$val]['material']      = $valx['material'];
          $ArrDetail[$val]['qty_material']  = $valx['qty_material'];
          $ArrDetail[$val]['unit']          = $valx['unit'];
          $ArrDetail[$val]['qty_packing']   = $qty_packing;
          $ArrDetail[$val]['unit_packing']  = $valx['unit_packing'];
          $ArrDetail[$val]['qty_aktual'] 	  = $qty_aktual;
          $ArrDetail[$val]['unit_aktual']   = $valx['unit_aktual'];

          $sqlWhDetail	   = "SELECT b.* FROM warehouse_stock b WHERE b.id_material = '".$valx['material']."' AND b.kd_gudang = 'PRO'";
  				$restWhDetail	   = $this->db->query($sqlWhDetail)->result();

          $sqlWhDetailMat	  = "SELECT b.* FROM warehouse_stock b WHERE 	b.id_material = '".$valx['material']."' AND b.kd_gudang = 'OPC'";
  				$restWhDetailMat	= $this->db->query($sqlWhDetailMat)->result();

          //update warehouse material
  				$ArrUpdateMat[$val]['id_material']        = $valx['material'];
  				$ArrUpdateMat[$val]['kd_gudang'] 	        = 'OPC';
          $ArrUpdateMat[$val]['outgoing'] 	        = $qty_aktual;
  				$ArrUpdateMat[$val]['qty_stock'] 	        = $restWhDetailMat[0]->qty_stock - $qty_aktual;
          $ArrUpdateMat[$val]['outgoing_packing'] 	= $qty_packing;
          $ArrUpdateMat[$val]['qty_stock_packing'] 	= $restWhDetailMat[0]->qty_stock_packing - $qty_packing;
  				$ArrUpdateMat[$val]['update_by'] 	        = $session['username'];
  				$ArrUpdateMat[$val]['update_date']        = date('Y-m-d H:i:s');

          //update warehouse produksi
  				$ArrUpdatePro[$val]['id_material']        = $valx['material'];
  				$ArrUpdatePro[$val]['kd_gudang'] 	        = 'PRO';
          $ArrUpdatePro[$val]['incoming'] 	        = $qty_aktual;
  				$ArrUpdatePro[$val]['qty_stock'] 	        = $restWhDetail[0]->qty_stock + $qty_aktual;
          $ArrUpdatePro[$val]['incoming_packing'] 	= $qty_packing;
          $ArrUpdatePro[$val]['qty_stock_packing'] 	= $restWhDetail[0]->qty_stock_packing + $qty_packing;
  				$ArrUpdatePro[$val]['update_by'] 	        = $session['username'];
  				$ArrUpdatePro[$val]['update_date']        = date('Y-m-d H:i:s');

          //insert history
  				$ArrHist[$val]['id_material'] 		  = $restWhDetail[0]->id_material;
  				$ArrHist[$val]['idmaterial'] 		    = $restWhDetail[0]->idmaterial;
  				$ArrHist[$val]['nm_material'] 		  = $restWhDetail[0]->nm_material;
  				$ArrHist[$val]['kd_gudang_dari'] 	  = "OPC";
  				$ArrHist[$val]['kd_gudang_ke'] 		  = "PRO";
          $ArrHist[$val]['incoming_awal'] 	  = $restWhDetail[0]->incoming;
  				$ArrHist[$val]['incoming_akhir'] 	  = $restWhDetail[0]->incoming + $qty_aktual;
  				$ArrHist[$val]['qty_stock_awal'] 	  = $restWhDetail[0]->qty_stock;
  				$ArrHist[$val]['qty_stock_akhir'] 	= $restWhDetail[0]->qty_stock + $qty_aktual;
  				$ArrHist[$val]['qty_booking_awal'] 	= $restWhDetail[0]->qty_booking;
  				$ArrHist[$val]['qty_booking_akhir'] = $restWhDetail[0]->qty_booking;
  				$ArrHist[$val]['qty_rusak_awal'] 	  = $restWhDetail[0]->qty_rusak;
  				$ArrHist[$val]['qty_rusak_akhir'] 	= $restWhDetail[0]->qty_rusak;
  				$ArrHist[$val]['no_trans'] 			    = $no_wip;
  				$ArrHist[$val]['jumlah_mat'] 		    = $qty_aktual;
  				$ArrHist[$val]['update_by'] 		    = $session['username'];
  				$ArrHist[$val]['update_date'] 		  = date('Y-m-d H:i:s');
          $ArrHist[$val]['incoming_awal_packing'] 	  = $restWhDetail[0]->incoming_packing;
  				$ArrHist[$val]['incoming_akhir_packing'] 	  = $restWhDetail[0]->incoming_packing + $qty_packing;
          $ArrHist[$val]['qty_stock_awal_packing'] 	  = $restWhDetail[0]->qty_stock_packing;
  				$ArrHist[$val]['qty_stock_akhir_packing'] 	= $restWhDetail[0]->qty_stock_packing + $qty_packing;
  				$ArrHist[$val]['qty_booking_awal_packing'] 	= $restWhDetail[0]->qty_booking_packing;
  				$ArrHist[$val]['qty_booking_akhir_packing'] = $restWhDetail[0]->qty_booking_packing;
  				$ArrHist[$val]['qty_rusak_awal_packing'] 	  = $restWhDetail[0]->qty_rusak_packing;
  				$ArrHist[$val]['qty_rusak_akhir_packing'] 	= $restWhDetail[0]->qty_rusak;
          $ArrHist[$val]['jumlah_mat_packing'] 		    = $qty_packing;

        }

        //insert adjustment
        $ArrInsertH = array(
  				'no_ipp'             => $no_wip,
  				'jumlah_mat'         => $SUM_QTY,
          'jumlah_mat_packing' => $SUM_PACK,
  				'kd_gudang_dari'     => 'OPC',
  				'kd_gudang_ke'       => 'PRO',
  				// 'note' => $note,
          // 'tanda_terima' => $tanda_terima,
  				'created_by'    => $session['username'],
  				'created_date'  => date('Y-m-d H:i:s')
  			);

        $q_update = "SELECT
                  	a.id,
                  	a.sts_wip
                  FROM
                  	produksi_planning_data a
                  	LEFT JOIN produksi_planning b ON a.no_plan = b.no_plan
                  WHERE
                  	b.costcenter = 'CC2000012'
                    AND a.no_plan = '".$data['plandate']."'
                  	AND a.sts_wip = 'N'";
        $result_update = $this->db->query($q_update)->result_array();
        $ArrUpdate	= array();
        foreach($result_update AS $val => $valx){
          $ArrUpdate[$val]['id']          = $valx['id'];
          $ArrUpdate[$val]['sts_wip']     = 'Y';
          $ArrUpdate[$val]['updated_by']  = $session['username'];
          $ArrUpdate[$val]['updated_date']= date('Y-m-d H:i:s');
          $ArrUpdate[$val]['no_wip']      = $no_wip;
        }

        $ArrHeadPlan = array(
          'sts_plan' => 'Y'
        );

        $sales_header    = $this->db->query("SELECT * FROM sales_order_header WHERE delivery_date BETWEEN '".$resultHP[0]->date_awal."' AND '".$resultHP[0]->date_akhir."' ")->result_array();
        $ArrUpdSales	= array();
        foreach($sales_header AS $val => $valx){
          $ArrUpdSales[$val]['no_so']      = $valx['no_so'];
          $ArrUpdSales[$val]['sts_plan']   = 'Y';
        }

        $sales_detail    = $this->db->query("SELECT *, SUM(qty_order) AS order_plan FROM sales_order_detail WHERE delivery_date BETWEEN '".$resultHP[0]->date_awal."' AND '".$resultHP[0]->date_akhir."' GROUP BY product ")->result_array();
        $ArrUpdStockProduct	= array();
        $ArrHistProduct = array();
        foreach($sales_detail AS $val => $valx){
          $ArrUpdStockProduct[$val]['id_product']  = $valx['product'];
          $ArrUpdStockProduct[$val]['qty_order']   = $valx['order_plan'];
          $ArrUpdStockProduct[$val]['update_by']  = $session['username'];
          $ArrUpdStockProduct[$val]['update_date']= date('Y-m-d H:i:s');

          $sqlhistPro	   = "SELECT * FROM warehouse_product WHERE id_product = '".$valx['product']."' AND category = 'product'";
  				$resthistPro	 = $this->db->query($sqlhistPro)->result();

          //insert history
  				$ArrHistProduct[$val]['category'] 		     = 'product';
  				$ArrHistProduct[$val]['id_product'] 		 = $valx['product'];
  				$ArrHistProduct[$val]['qty_order_awal']  = $resthistPro[0]->qty_order;
  				$ArrHistProduct[$val]['qty_order_akhir'] = $resthistPro[0]->qty_order + $valx['order_plan'];
          $ArrHistProduct[$val]['no_trans'] 		   = $no_wip;
          $ArrHistProduct[$val]['update_by'] 	   = $session['username'];
  				$ArrHistProduct[$val]['update_date'] 	 = date('Y-m-d H:i:s');
        }


        //HISTORT STOCK

        // echo $q_update;
        // print_r($ArrHeader);
        // print_r($ArrDetail);
        // print_r($ArrUpdate);
        // print_r($ArrUpdateMat);
        // print_r($ArrUpdatePro);
        // print_r($ArrHist);
        // print_r($ArrInsertH);

        // print_r($ArrUpdStockProduct);
        // print_r($ArrHistProduct);
        // exit;

        $this->db->trans_start();
          $this->db->where('no_plan', $data['plandate']);
          $this->db->update('produksi_planning', $ArrHeadPlan);

          $this->db->insert('material_wip', $ArrHeader);
          $this->db->insert_batch('material_wip_detail', $ArrDetail);
          $this->db->update_batch('produksi_planning_data', $ArrUpdate, 'id');

          $this->db->where('kd_gudang','OPC');
          $this->db->update_batch('warehouse_stock', $ArrUpdateMat, 'id_material');

          $this->db->where('kd_gudang','PRO');
          $this->db->update_batch('warehouse_stock', $ArrUpdatePro, 'id_material');

          $this->db->insert_batch('warehouse_history', $ArrHist);
          $this->db->insert('warehouse_adjustment', $ArrInsertH);

          $this->db->update_batch('sales_order_header', $ArrUpdSales, 'no_so');

          $this->db->where('category','product');
          $this->db->update_batch('warehouse_product', $ArrUpdStockProduct, 'id_product');
          $this->db->insert_batch('warehouse_product_history', $ArrHistProduct);
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
      $no_plan = $this->uri->segment(3);
      $query = "SELECT
                  a.*,
                  b.nm_material,
                  b.unit,
                  b.konversi,
                  b.satuan_packing
                FROM
                  get_material_wip a
                  LEFT JOIN ms_material b ON a.code_material=b.code_material
                WHERE a.no_plan='".$no_plan."'
                  AND a.code_material <> '0' AND a.code_material <> '' AND a.code_material IS NOT NULL
                  ";
      $result = $this->db->query($query)->result_array();
      $num_r = $this->db->query($query)->num_rows();
      $d_Header = "<div class='box box-primary'>";
        	$d_Header .= "<div class='box-body'>";
          $d_Header .= "<table class='table table-bordered table-striped'>";
          $d_Header .= "<thead>";
          $d_Header .= "<tr>";
            $d_Header .= "<th class='text-center' width='5%' style='vertical-align:middle;'>#</th>";
            $d_Header .= "<th class='text-center' width='30%' style='vertical-align:middle;'>Material Name</th>";
            $d_Header .= "<th class='text-center' width='13%' style='vertical-align:middle;'>Qty Packing</th>";
            $d_Header .= "<th class='text-center' width='13%' style='vertical-align:middle;'>Qty Material</th>";
            $d_Header .= "<th class='text-center' width='13%' style='vertical-align:middle;'>Actual Qty Packing</th>";
            $d_Header .= "<th class='text-center' width='13%' style='vertical-align:middle;'>Unit Packing</th>";
            $d_Header .= "<th class='text-center' width='13%' style='vertical-align:middle;'>Actual Qty Material (Kg)</th>";
          $d_Header .= "</tr>";
          $d_Header .= "</thead>";
          $d_Header .= "<tbody>";
          foreach ($result as $key => $value) { $key++;

            if($value['weight'] <= 0 OR empty($value['konversi']) OR $value['konversi'] <= 0){
              $qty_pack = 0;
            }else{
              $qty_pack = $value['weight']/$value['konversi'];
            }
            $d_Header .= "<tr>";
              $d_Header .= "<td class='text-center'>".$key."</td>";
              $d_Header .= "<td>".strtoupper($value['nm_material'])."</td>";
              $d_Header .= "<td class='text-right'>".number_format($qty_pack,2)." ".ucfirst($value['satuan_packing'])."</td>";
              $d_Header .= "<td class='text-right'>".number_format($value['weight'],2)." ".ucfirst($value['unit'])."</td>";
              $d_Header .= "<td>
                            <input type='text' name='detail[".$key."][qty_packing]' id='pack_".$key."' data-konversi='".$value['konversi']."' class='form-control input-md text-right maskM inputPacking'>
                            </td>";
              $d_Header .= "<td class='text-center'>".ucfirst($value['satuan_packing'])."</td>";
              $d_Header .= "<td>
                            <input type='text' name='detail[".$key."][qty_aktual]' id='qty_".$key."' class='form-control input-md text-right' readonly='readonly'>
                            <input type='hidden' name='detail[".$key."][material]' class='form-control input-md' value='".$value['code_material']."'>
                            <input type='hidden' name='detail[".$key."][qty_material]' class='form-control input-md' value='".$value['weight']."'>
                            <input type='hidden' name='detail[".$key."][unit]' class='form-control input-md' value='".$value['unit']."'>
                            <input type='hidden' name='detail[".$key."][unit_packing]' class='form-control input-md' value='".$value['satuan_packing']."'>
                            <input type='hidden' name='detail[".$key."][unit_aktual]' class='form-control input-md' value='".$value['unit']."'>
                            </td>";
            $d_Header .= "</tr>";
          }
          if($num_r < 1){
            $d_Header .= "<tr>";
            $d_Header .= "<td colspan='6'>Data not found ...</td>";
            $d_Header .= "</tr>";
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
    //============================================INCOMING=========================================================
    //==========================================================================================================

    public function incoming(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');

      $query = "SELECT no_po FROM tran_material_purchase_header WHERE sts_ajuan = 'OPN' ORDER BY no_po ASC ";
      // echo $query;
  		$restQuery = $this->db->query($query)->result_array();
  		$data = array(
  			'no_po' => $restQuery
  		);

      $this->template->set('results', $data);
      history("View index masterial incoming");
      $this->template->title('Materials Incoming');
      $this->template->render('incoming');
    }

    public function data_side_incoming(){
      $this->warehouse_material_model->get_json_incoming();
    }

    public function adjustment(){
      $no_po = $this->uri->segment(3);
      $gudang = $this->uri->segment(4);

      $qBQdetailHeader 	= " SELECT
                              a.*,
                              SUM(a.qty) AS qty,
                              SUM(a.qty_in) AS qty_in,
                              b.satuan_packing,
                              b.konversi,
                              b.unit
                            FROM tran_material_purchase_detail a LEFT JOIN ms_material b ON a.id_material=b.code_material
                            WHERE
                              a.no_po='".$no_po."'
                              AND complete = 'N'
                            GROUP BY id_material";
      $qBQdetailRest		= $this->db->query($qBQdetailHeader)->result_array();
      $qBQdetailNum		= $this->db->query($qBQdetailHeader)->num_rows();
      // echo $qBQdetailHeader;
      // exit;

      $data = array(
        'qBQdetailHeader' => $qBQdetailHeader,
        'qBQdetailRest' => $qBQdetailRest,
        'qBQdetailNum' => $qBQdetailNum,
        'no_po' => $no_po,
        'gudang' => $gudang
      );

      $this->template->set('results', $data);
      $this->template->title('Materials In');
      $this->template->render('adjustment');
  	}

    public function in_material(){
  		$data 			= $this->input->post();
  		$session  = $this->session->userdata('app_session');
  		$no_po			= $data['no_po'];
  		$gudang			= $data['gudang'];
  		// $note			= strtolower($data['note']);
      // $tanda_terima			= strtoupper($data['tanda_terima']);
  		$addInMat		= $data['addInMat'];
  		$adjustment 	= $data['adjustment'];

      $Ym 			      = date('ym');

  		//pengurutan kode
  		$srcMtr			  = "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRS".$Ym."%' ";
  		$numrowMtr		= $this->db->query($srcMtr)->num_rows();
  		$resultMtr		= $this->db->query($srcMtr)->result_array();
  		$angkaUrut2		= $resultMtr[0]['maxP'];
  		$urutan2		  = (int)substr($angkaUrut2, 7, 4);
  		$urutan2++;
  		$urut2			   = sprintf('%04s',$urutan2);
  		$kode_trans		= "TRS".$Ym.$urut2;

  		// echo $no_po;
  		// print_r($addInMat);
  		// exit;

  		if($adjustment == 'IN'){
  			$histHlp = "Material Adjustment In Purchase To ".$gudang." / ".$no_po;
  		}

  		if($adjustment == 'IN'){
  			$ArrUpdate		 = array();
  			$ArrInList		 = array();
  			$ArrDeatil		 = array();
  			$ArrHist		 = array();

        $ArrUpdateMat		 = array();

  			$SumMat = 0;
  			$SumRisk = 0;

        $SumMatPack = 0;
  			$SumRiskPack = 0;
  			foreach($addInMat AS $val => $valx){
  				$SumMat += $valx['qty_in'];
  				$SumRisk += $valx['qty_rusak'];

          $SumMatPack += $valx['qty_in_pack'];
  				$SumRiskPack += $valx['qty_rusak_pack'];

  				$sqlWhDetail	= "	SELECT
  									a.*,
  									b.id AS id2,
  									b.*
  								FROM
  									tran_material_purchase_detail a
  									LEFT JOIN warehouse_stock b
  										ON a.id_material=b.id_material
  								WHERE
  									a.id = '".$valx['id']."' AND b.kd_gudang = 'OPC'
  								";
  				$restWhDetail	= $this->db->query($sqlWhDetail)->result();

          // echo $sqlWhDetail;
  				//update detail purchase
  				$ArrUpdate[$val]['id'] 			= $valx['id'];
  				$ArrUpdate[$val]['qty_in'] 		= $restWhDetail[0]->qty_in + $valx['qty_in'];
  				$ArrUpdate[$val]['complete'] 	= (!empty($valx['complete']))?$valx['complete']:'N';
          $ArrUpdate[$val]['qty_in_packing'] 		= $restWhDetail[0]->qty_in_packing + $valx['qty_in_pack'];

  				$ArrInList[$val]['complete'] 	= (!empty($valx['complete']))?$valx['complete']:'N';

          //Update_mat


  				//update stock
  				$ArrDeatil[$val]['id'] 			    = $restWhDetail[0]->id2;
  				// $ArrDeatil[$val]['id_material'] = $restWhDetail[0]->id_material;
  				// $ArrDeatil[$val]['kd_gudang'] 	= $restWhDetail[0]->kd_gudang;
          $ArrDeatil[$val]['incoming'] 	  = $valx['qty_in'];
  				$ArrDeatil[$val]['qty_stock'] 	= $restWhDetail[0]->qty_stock + $valx['qty_in'];
  				$ArrDeatil[$val]['qty_rusak'] 	= $restWhDetail[0]->qty_rusak + $valx['qty_rusak'];
  				$ArrDeatil[$val]['update_by'] 	= $session['username'];
  				$ArrDeatil[$val]['update_date'] = date('Y-m-d H:i:s');

  				//insert history
  				$ArrHist[$val]['id_material'] 		= $restWhDetail[0]->id_material;
  				$ArrHist[$val]['idmaterial'] 		  = $restWhDetail[0]->idmaterial;
  				$ArrHist[$val]['nm_material'] 		= $restWhDetail[0]->nm_material;
  				$ArrHist[$val]['kd_gudang_dari'] 	= "PURCHASE";
          $ArrHist[$val]['id_gudang'] 		= "1";
  				$ArrHist[$val]['kd_gudang'] 		= "OPC";
          $ArrHist[$val]['id_gudang_ke'] 		= "";
          $ArrHist[$val]['kd_gudang_ke'] 		= "PURCHASE";
          $ArrHist[$val]['id_gudang_dari'] 		= "1";
  				$ArrHist[$val]['kd_gudang_dari'] 		= "OPC";
          $ArrHist[$val]['incoming_awal'] 	= $restWhDetail[0]->incoming;
  				$ArrHist[$val]['incoming_akhir'] 	= $restWhDetail[0]->incoming + $valx['qty_in'];
  				$ArrHist[$val]['qty_stock_awal'] 	= $restWhDetail[0]->qty_stock;
  				$ArrHist[$val]['qty_stock_akhir'] 	= $restWhDetail[0]->qty_stock + $valx['qty_in'];
  				$ArrHist[$val]['qty_booking_awal'] 	= $restWhDetail[0]->qty_booking;
  				$ArrHist[$val]['qty_booking_akhir'] = $restWhDetail[0]->qty_booking;
  				$ArrHist[$val]['qty_rusak_awal'] 	= $restWhDetail[0]->qty_rusak;
  				$ArrHist[$val]['qty_rusak_akhir'] 	= $restWhDetail[0]->qty_rusak + $valx['qty_rusak'];
  				$ArrHist[$val]['no_ipp'] 			    = $kode_trans."/".$no_po;
  				$ArrHist[$val]['jumlah_mat'] 		  = $valx['qty_in'] + $valx['qty_rusak'];
          $ArrHist[$val]['ket'] 		        = "incoming material";
  				$ArrHist[$val]['update_by'] 		  = $session['username'];
  				$ArrHist[$val]['update_date'] 		= date('Y-m-d H:i:s');

          $ArrHist[$val]['tanda_terima'] 		= $valx['tanda_terima'];
          $ArrHist[$val]['surat_jalan'] 		= $valx['surat_jalan'];
  			}

  			$ArrInsertH = array(
          'kode_trans' => $kode_trans,
  				'no_ipp' => $no_po,
  				'jumlah_mat' => $SumMat + $SumRisk,
          'jumlah_mat_packing' => $SumMatPack + $SumRiskPack,
          'id_gudang_dari' => '',
  				'kd_gudang_dari' => 'PURCHASE',
  				'id_gudang_ke' => '1',
          'kd_gudang_ke' => 'OPC',
  				// 'note' => $note,
          'category' => 'incoming material',
  				'created_by' => $session['username'],
  				'created_date' => date('Y-m-d H:i:s')
  			);

  			$ArrHeader = array(
  				'sts_process' => 'Y',
  			);

  			$ArrHeader2 = array(
  				'sts_ajuan' => 'CLS',
  			);


  			// print_r($ArrUpdate);
  			// print_r($ArrHist);
  			// print_r($ArrInsertH);
  			// exit;
  			$this->db->trans_start();
  				$this->db->update_batch('warehouse_stock', $ArrDeatil, 'id');
  				$this->db->update_batch('tran_material_purchase_detail', $ArrUpdate, 'id');

  				$this->db->insert_batch('warehouse_history', $ArrHist);
  				$this->db->insert('warehouse_adjustment', $ArrInsertH);

  				$this->db->where('no_po', $no_po);
  				$this->db->update('tran_material_purchase_header', $ArrHeader);

  				$qCheck = "SELECT * FROM tran_material_purchase_detail WHERE no_po='".$no_po."' AND qty_in < qty ";
  				$NumChk = $this->db->query($qCheck)->num_rows();
  				if($NumChk < 1){
  					$this->db->where('no_po', $no_po);
  					$this->db->update('tran_material_purchase_header', $ArrHeader2);
  				}
  			$this->db->trans_complete();
  		}


  		if($this->db->trans_status() === FALSE){
  			$this->db->trans_rollback();
  			$Arr_Data	= array(
  				'pesan'		=>'Save process failed. Please try again later ...',
  				'status'	=> 0
  			);
  		}
  		else{
  			$this->db->trans_commit();
  			$Arr_Data	= array(
  				'pesan'		=>'Save process success. Thanks ...',
  				'status'	=> 1
  			);
  			history($histHlp);
  		}
  		echo json_encode($Arr_Data);
  	}

    public function detail_adjustment(){
      $no_ipp     = $this->uri->segment(3);
      $created    = str_replace('sp4si',' ',$this->uri->segment(4));
      $dated      = str_replace('sp4si',' ',$this->uri->segment(5));

      $qBQdetailHeader 	= "SELECT * FROM warehouse_history WHERE no_ipp LIKE '%".$no_ipp."%' AND update_by='".$created."' AND update_date='".$dated."' ";
      $qBQdetailRest		= $this->db->query($qBQdetailHeader)->result_array();
      $qBQdetailNum		= $this->db->query($qBQdetailHeader)->num_rows();

      $data = array(
        'qBQdetailHeader' => $qBQdetailHeader,
        'qBQdetailRest'   => $qBQdetailRest,
        'qBQdetailNum'    => $qBQdetailNum
      );

      $this->template->set('results', $data);
      history("View index detail adjustment ".$no_ipp);
      $this->template->title('Materials Adjustment In');
      $this->template->render('detail_adjustment');
    }

    //==========================================================================================================
    //============================================ADJUSTMENT MATERIAL===========================================
    //==========================================================================================================

    public function adjustment_material(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index adjustment material");
      $this->template->title('Adjustment');
      $this->template->render('adjustment_material');
    }

    public function data_side_adjustment(){
  		$this->warehouse_material_model->get_json_adjustment();
  	}

    public function add_adjustment(){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');
        $kd_gudang		= $data['kd_gudang'];
        $material	    = $data['material'];
        $adjustment		= $data['adjustment'];
        $surat_jalan	= $data['surat_jalan'];
        $qty			    = $data['qty'];
        $stock_awal	  = $data['stock_awal'];
        $stock_akhir	= $data['stock_akhir'];
        $reason	      = strtolower($data['reason']);
        $unit_	      = $data['unit'];
        $IMP          = explode('_', $unit_);
        $ajust_by     = $IMP[1];
        $unit         = $IMP[0];

        $Ym           = date('ym');
        $srcMtr			  = "SELECT MAX(kd_adjustment) as maxP FROM warehouse_material_adjustment WHERE kd_adjustment LIKE 'AM".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 6, 6);
        $urutan2++;
        $urut2			  = sprintf('%06s',$urutan2);
        $kd_adjustment	= "AM".$Ym.$urut2;

        if($ajust_by == 'packing'){
          $awal     = $stock_awal * get_konversi($material);
          $akhir     = $stock_akhir * get_konversi($material);
          $qty_unit     = $qty * get_konversi($material);
        }
        if($ajust_by == 'unit'){
          $awal     = $stock_awal;
          $akhir     = $stock_akhir;
          $qty_unit     = $qty;
        }

        $ArrHeader = array(
          'kd_adjustment'   => $kd_adjustment,
          'kd_gudang'       => $kd_gudang,
          'material'        => $material,
          'adjustment'      => $adjustment,
          'qty'             => $qty_unit,
          'stock_awal'      => $awal,
          'surat_jalan'      => $surat_jalan,
          'stock_akhir'     => $akhir,
          'reason'          => $reason,
          'unit'            => $unit,
          'ajust_by'        => $ajust_by,
          'created_by'	    => $session['username'],
          'created_date'	  => date('Y-m-d H:i:s')
        );

        // print_r($ArrHeader); exit;

        $ArrHist	  = array();
        $ArrUpdate3	= array();

        //history
        $sqlWhDetail	= "	SELECT
                            b.*
                          FROM
                            warehouse_stock b
                          WHERE
                            b.id_material = '".$material."' AND b.id_gudang = '".$kd_gudang."'
                          LIMIT 1";
        $restWhDetail	= $this->db->query($sqlWhDetail)->result();
        // echo $sqlWhDetail; exit;
        if(!empty($restWhDetail)){
          $incoming_akhir = $restWhDetail[0]->incoming - $qty_unit;
          $qty_stock_akhir = $restWhDetail[0]->qty_stock - $qty_unit;
          if($adjustment == 'plus'){
            $incoming_akhir = $restWhDetail[0]->incoming + $qty_unit;
            $qty_stock_akhir = $restWhDetail[0]->qty_stock + $qty_unit;
          }

          $ArrUpdate3['qty_stock']          = $akhir;
          $ArrUpdate3['incoming']           = $qty_unit;
          $ArrUpdate3['update_by'] 	        = $session['username'];
  				$ArrUpdate3['update_date'] 	      = date('Y-m-d H:i:s');

          $ArrHist['id_material'] 		= $restWhDetail[0]->id_material;
          $ArrHist['idmaterial'] 		  = $restWhDetail[0]->idmaterial;
          $ArrHist['nm_material'] 		= $restWhDetail[0]->nm_material;
          $ArrHist['id_gudang'] 		  = $restWhDetail[0]->id_gudang;
          $ArrHist['kd_gudang'] 		  = $restWhDetail[0]->kd_gudang;
          $ArrHist['id_gudang_dari'] 	= "";
          $ArrHist['kd_gudang_dari'] 	= "ADJUSTMENT";
          $ArrHist['id_gudang_ke'] 		= $restWhDetail[0]->id_gudang;
          $ArrHist['kd_gudang_ke'] 		= $restWhDetail[0]->kd_gudang;
          $ArrHist['incoming_awal'] 	= $restWhDetail[0]->incoming;
          $ArrHist['incoming_akhir'] 	= $incoming_akhir;
          $ArrHist['qty_stock_awal'] 	= $restWhDetail[0]->qty_stock;
          $ArrHist['qty_stock_akhir'] 	= $qty_stock_akhir;
          $ArrHist['qty_booking_awal'] 	= $restWhDetail[0]->qty_booking;
          $ArrHist['qty_booking_akhir'] = $restWhDetail[0]->qty_booking;
          $ArrHist['qty_rusak_awal'] 	  = $restWhDetail[0]->qty_rusak;
          $ArrHist['qty_rusak_akhir'] 	= $restWhDetail[0]->qty_rusak;
          $ArrHist['no_ipp'] 			    = $kd_adjustment;
          $ArrHist['jumlah_mat'] 		    = $qty_unit;
          $ArrHist['ket'] 		        = "adjustment material ".$adjustment;
          $ArrHist['update_by'] 		    = $session['username'];
          $ArrHist['update_date'] 		  = date('Y-m-d H:i:s');
        }

        $ArrStockSupIns = array();
        $ArrHistSubIns = array();
        if(empty($restWhDetail)){
					$sql_mat	= "	SELECT a.* FROM ms_material a WHERE a.code_material = '".$material."' LIMIT 1";
					$rest_mat	= $this->db->query($sql_mat)->result();
					//update stock sub gudang
					$ArrStockSupIns['id_material'] 	= $rest_mat[0]->code_material;
					$ArrStockSupIns['idmaterial'] 	= $rest_mat[0]->code_company;
					$ArrStockSupIns['nm_material'] 	= $rest_mat[0]->nm_material;
					$ArrStockSupIns['id_gudang'] 		= $kd_gudang;
					$ArrStockSupIns['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', $kd_gudang);
					$ArrStockSupIns['qty_stock'] 		= $req_stock;
					$ArrStockSupIns['update_by'] 		= $session['username'];
					$ArrStockSupIns['update_date'] 	= date('Y-m-d H:i:s');

					$ArrHistSubIns['id_material'] 	= $rest_mat[0]->code_material;
					$ArrHistSubIns['idmaterial'] 		= $rest_mat[0]->code_company;
					$ArrHistSubIns['nm_material'] 	= $rest_mat[0]->nm_material;
					$ArrHistSubIns['id_gudang'] 		= $kd_gudang;
					$ArrHistSubIns['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', $kd_gudang);
          $ArrHistSubIns['id_gudang_dari'] 	= "";
          $ArrHistSubIns['kd_gudang_dari'] 	= "ADJUSTMENT";
					$ArrHistSubIns['id_gudang_ke'] 	= $kd_gudang;
					$ArrHistSubIns['kd_gudang_ke'] 	= get_name('warehouse', 'kd_gudang', 'id', $kd_gudang);
					$ArrHistSubIns['qty_stock_awal'] 	= 0;
					$ArrHistSubIns['qty_stock_akhir'] 	= $req_stock;
					$ArrHistSubIns['qty_booking_awal'] 	= 0;
					$ArrHistSubIns['qty_booking_akhir'] 	= 0;
					$ArrHistSubIns['qty_rusak_awal'] 		= 0;
					$ArrHistSubIns['qty_rusak_akhir'] 	= 0;
					$ArrHistSubIns['no_ipp'] 				= $kd_adjustment;
					$ArrHistSubIns['jumlah_mat'] 			= $qty_unit;
					$ArrHistSubIns['ket'] 				= 'penambahan gudang by adjustment (insert new)';
					$ArrHistSubIns['update_by'] 			= $session['username'];
					$ArrHistSubIns['update_date'] 		= date('Y-m-d H:i:s');
				}

        // print_r($ArrHeader);
        // print_r($ArrUpdate3);
        // print_r($ArrHist);
        // print_r($ArrStockSupIns);
        // print_r($ArrHistSubIns);
        // exit;

        $this->db->trans_start();
            $this->db->insert('warehouse_material_adjustment', $ArrHeader);

            if(!empty($restWhDetail)){
              $this->db->where('id_material', $material);
              $this->db->where('id_gudang', $kd_gudang);
              $this->db->update('warehouse_stock', $ArrUpdate3);
              $this->db->insert('warehouse_history', $ArrHist);
            }
            if(empty($restWhDetail)){
      				$this->db->insert('warehouse_stock', $ArrStockSupIns);
      				$this->db->insert('warehouse_history', $ArrHistSubIns);
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
          history("Insert adjustment material ".$kd_adjustment);
        }
        echo json_encode($Arr_Data);
      }
      else{
        $this->template->title('Add Adjustment');
        $this->template->render('add_adjustment');
      }
  	}

    public function get_stock(){
      $material = $this->uri->segment(3);
      $gudang    = $this->uri->segment(4);
      $unit    = $this->uri->segment(5);

      $IMP = explode('_', $unit);
      // echo $IMP[1];

      if($gudang == '1'){
        if(empty($unit)){
          $stock = get_stock_material_packing($material, $gudang);
        }
        if(!empty($unit)){
          if($IMP[1] == 'unit'){
            $stock = get_stock_material($material, $gudang);
          }
          if($IMP[1] == 'packing'){
            $stock = get_stock_material_packing($material, $gudang);
          }

        }

        $sqlSup		= "SELECT satuan_packing, unit FROM ms_material WHERE code_material ='".$material."' ";
    		$restSup	= $this->db->query($sqlSup)->result();

    		$option	= "<option value='".$restSup[0]->satuan_packing."_packing'>".strtoupper($restSup[0]->satuan_packing)." (Packing)</option>";
        $option	.= "<option value='".$restSup[0]->unit."_unit'>".strtoupper($restSup[0]->unit)." (Unit)</option>";

        $tanda = "packing";
      }

      if($gudang != '1'){
        $stock = get_stock_material($material, $gudang);

        $sqlSup		= "SELECT unit FROM ms_material WHERE code_material ='".$material."' ";
    		$restSup	= $this->db->query($sqlSup)->result();

        $option	= "<option value='".$restSup[0]->unit."_unit'>".strtoupper($restSup[0]->unit)." (Unit)</option>";

        $tanda = "unit";
      }

  		 echo json_encode(array(
  				'stock'			=> floatval($stock),
          'option'		=> $option,
          'tanda'			=> $tanda
  		 ));
  	}

    //==========================================================================================================
    //============================================STOCK=========================================================
    //==========================================================================================================

    public function history(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index masterial History");
      $this->template->title('Materials History');
      $this->template->render('history');
    }

    public function data_side_history(){
  		$this->warehouse_material_model->get_json_history();
  	}

    public function excel_history(){
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

        $material   = $this->uri->segment(3);
        $kd_gudang  = $this->uri->segment(4);
        $tgl_awal   = $this->uri->segment(5);
        $tgl_akhir  = $this->uri->segment(6);

        $material_where = "";
        if($material != '0'){
        $material_where = " AND a.id_material = '".$material."'";
        }

        $kd_gudang_where = "";
        if($kd_gudang != '0'){
        $kd_gudang_where = " AND a.id_gudang = '".$kd_gudang."'";
        }

        $kd_date_where = "";
        if($tgl_awal != '0'){
        $kd_date_where = " AND a.update_date BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' ";
        }

    		$sql = "
      			SELECT
      				a.*
      			FROM
      			   warehouse_history a
      		   WHERE DATE(update_date) >= '2020-12-01' ".$material_where." ".$kd_gudang_where." ".$kd_date_where." ";
    		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(11);
    		$sheet->setCellValue('A'.$Row, 'HISTORY MATERIAL');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Material');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'Tanggal');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D'.$NewRow, 'Gudang');
    		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->setCellValue('E'.$NewRow, 'Weight');
    		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
    		$sheet->getColumnDimension('E')->setAutoSize(true);

        $sheet->setCellValue('F'.$NewRow, 'Gudang Dari');
    		$sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
    		$sheet->getColumnDimension('F')->setAutoSize(true);

        $sheet->setCellValue('G'.$NewRow, 'Gudang Ke');
    		$sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
    		$sheet->getColumnDimension('G')->setAutoSize(true);

        $sheet->setCellValue('H'.$NewRow, 'Stock Awal');
    		$sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('H'.$NewRow.':H'.$NextRow);
    		$sheet->getColumnDimension('H')->setAutoSize(true);

        $sheet->setCellValue('I'.$NewRow, 'Stock Akhir');
    		$sheet->getStyle('I'.$NewRow.':I'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('I'.$NewRow.':I'.$NextRow);
    		$sheet->getColumnDimension('I')->setAutoSize(true);

        $sheet->setCellValue('J'.$NewRow, 'Update By');
    		$sheet->getStyle('J'.$NewRow.':J'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('J'.$NewRow.':J'.$NextRow);
    		$sheet->getColumnDimension('J')->setAutoSize(true);

        $sheet->setCellValue('K'.$NewRow, 'Update Date');
    		$sheet->getStyle('K'.$NewRow.':K'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('K'.$NewRow.':K'.$NextRow);
    		$sheet->getColumnDimension('K')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$id_produksi	= strtoupper(strtoupper($row['nm_material']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= strtoupper(date('d-M-Y', strtotime($row['update_date'])));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $awal_col++;
          $status_date	= strtoupper(get_name('warehouse','nm_gudang','id',$row['id_gudang']));
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= $row['jumlah_mat'];
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $gudang_dari = (!empty($row['id_gudang_dari']))?get_name('warehouse','nm_gudang','id',$row['id_gudang_dari']):$row['kd_gudang_dari'];
          $gudang_ke = (!empty($row['id_gudang_ke']))?get_name('warehouse','nm_gudang','id',$row['id_gudang_ke']):$row['kd_gudang_ke'];

          $awal_col++;
          $status_date	= strtoupper($gudang_dari);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= strtoupper($gudang_ke);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $qty_stock_awal	= $row['qty_stock_awal'];
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $qty_stock_awal);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $awal_col++;
          $qty_stock_akhir	= $row['qty_stock_akhir'];
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $qty_stock_akhir);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $awal_col++;
          $update_by	= strtoupper($row['update_by']);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $update_by);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $update_date	= date('d-M-Y H:i:s', strtotime($row['update_date']));
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $update_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

  			}
  		}


  		$sheet->setTitle('History Material');
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
  		header('Content-Disposition: attachment;filename="history_material_'.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

}

?>
