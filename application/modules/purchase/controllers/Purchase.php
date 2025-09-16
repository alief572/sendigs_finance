<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Purchase extends Admin_Controller
{
    protected $viewPermission 	= 'Cycletime.View';
    protected $addPermission  	= 'Cycletime.Add';
    protected $managePermission = 'Cycletime.Manage';
    protected $deletePermission = 'Cycletime.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array('Mpdf'));
        $this->load->model(array('Purchase/purchase_model'
                                ));
        $this->template->title('Purchasing');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    //========================================================BOM

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->purchase_model->get_data('tran_material_purchase_header','deleted','N');
      history("View index Purchasing");
      $this->template->set('results', $data);
      $this->template->title('Purchasing Order');
      $this->template->render('index');
    }

    public function data_side_purchase(){
      $this->purchase_model->get_json_purchase();
    }

    public function data_side_request(){
      $this->purchase_model->get_json_request();
    }

    public function add_po(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
  		$query = "SELECT id_supplier, name_supplier FROM master_supplier WHERE activation='active' ORDER BY name_supplier ASC ";
      // echo $query;
  		$restQuery = $this->db->query($query)->result_array();
  		$data = array(
  			'supList' => $restQuery
  		);
      $this->template->set('results', $data);
      $this->template->title('Purchasing Request');
      $this->template->render('add_po', $data);
  	}

    public function detail_purchase(){
      $no_po = $this->uri->segment(3);
      $status = $this->uri->segment(4);

      $qBQdetailHeader 	= "SELECT a.*, SUM(a.qty) AS qty, b.satuan_packing, b.konversi, b.unit FROM tran_material_purchase_detail a LEFT JOIN ms_material b ON a.id_material=b.code_material WHERE a.no_po='".$no_po."' AND a.deleted='N' GROUP BY a.id_material";

      if($status == 'CNC'){
      	$qBQdetailHeader 	= "SELECT a.*, SUM(a.qty) AS qty, b.satuan_packing, b.konversi, b.unit FROM tran_material_purchase_detail a LEFT JOIN ms_material b ON a.id_material=b.code_material WHERE a.no_po='".$no_po."' GROUP BY a.id_material";
      }
      $qBQdetailRest		= $this->db->query($qBQdetailHeader)->result_array();
      $qBQdetailNum		= $this->db->query($qBQdetailHeader)->num_rows();

      $data = array(
  			'qBQdetailRest' => $qBQdetailRest,
        'qBQdetailNum' => $qBQdetailNum
  		);
      $this->template->set('results', $data);
      $this->template->render('detail_purchase', $data);
  	}

    public function edit_purchase(){
      $no_po = $this->uri->segment(3);

      $qBQdetailHeader 	= "SELECT a.*, b.satuan_packing, b.konversi, b.unit FROM tran_material_purchase_detail a LEFT JOIN ms_material b ON a.id_material=b.code_material WHERE a.no_po='".$no_po."' AND a.deleted='N'";
      $qBQdetailRest		= $this->db->query($qBQdetailHeader)->result_array();
      $qBQdetailNum		  = $this->db->query($qBQdetailHeader)->num_rows();
      // echo $qBQdetailHeader;
      // exit;
      $data = array(
  			'qBQdetailRest' => $qBQdetailRest,
        'qBQdetailNum' => $qBQdetailNum
  		);
      $this->template->set('results', $data);
      $this->template->render('edit_purchase', $data);
  	}

    public function createPO(){
      $Arr_Kembali	= array();
      $data			= $this->input->post();
      $session 	= $this->session->userdata('app_session');
      $Ym				= date('ym');
      //pengurutan kode
      $srcMtr			= "SELECT MAX(no_po) as maxP FROM tran_material_purchase_header WHERE no_po LIKE 'PO".$Ym."%' ";
      $numrowMtr		= $this->db->query($srcMtr)->num_rows();
      $resultMtr		= $this->db->query($srcMtr)->result_array();
      $angkaUrut2		= $resultMtr[0]['maxP'];
      $urutan2		= (int)substr($angkaUrut2, 6, 3);
      $urutan2++;
      $urut2			= sprintf('%03s',$urutan2);
      $no_po			= "PO".$Ym.$urut2;

      $id_supplier	= $data['id_supplier'];
      $check			 = $data['check'];
      $ArrList 		 = array();
      foreach($check AS $vaxl){
        $ArrList[$vaxl] = $vaxl;
      }
      $dtImplode		= "('".implode("','", $ArrList)."')";

      //nm supplier
      $qSupplier			= "SELECT * FROM master_supplier WHERE id_supplier ='".$id_supplier."' LIMIT 1 ";
      $restSupplier		= $this->db->query($qSupplier)->result();

      $qList 		= "SELECT * FROM material_request_detail WHERE id IN ".$dtImplode."  ";
      $restList 	= $this->db->query($qList)->result_array();

      $qListG 	= "SELECT id, material, weight AS purchase FROM material_request_detail WHERE id IN ".$dtImplode." ";
      $restListG 	= $this->db->query($qListG)->result_array();

      //insert detail
      $ArrDetail = array();
      $SUM_MAT = 0;
      foreach($restListG AS $val => $valx){
        $SUM_MAT += $valx['purchase'];
        $ArrDetail[$val]['no_po'] 		= $no_po;
        $ArrDetail[$val]['id_material'] = $valx['material'];
        $ArrDetail[$val]['idmaterial'] 	= get_name('ms_material', 'code_company', 'code_material',$valx['material']);
        $ArrDetail[$val]['nm_material'] = get_name('ms_material', 'nm_material', 'code_material',$valx['material']);
        $ArrDetail[$val]['id_supplier'] = $id_supplier;
        $ArrDetail[$val]['nm_supplier'] = $restSupplier[0]->name_supplier;
        $ArrDetail[$val]['qty'] 		    = str_replace(',','',$data["qty_".$valx['id']]);
        $ArrDetail[$val]['qty_packing'] = str_replace(',','',$data["packing_".$valx['id']]);
        // $ArrDetail[$val]['price_ref'] 	= $data['ref_'.$valx['id']];
      }

      //insert header
      $ArrHeader = array(
        'no_po' 		=> $no_po,
        'id_supplier' 	=> $id_supplier,
        'nm_supplier' 	=> $restSupplier[0]->name_supplier,
        'total_material' 	=> $SUM_MAT,
        'created_by' 	=> $session['id_user'],
        'created_date' 	=> date('Y-m-d H:i:s')
      );

      //update detail
      $ArrDetailUpdate = array();
      foreach($restList AS $val => $valx){
        $ArrDetailUpdate[$val]['id'] 	= $valx['id'];
        $ArrDetailUpdate[$val]['no_po'] = $no_po;
      }

      // print_r($ArrHeader);
      // print_r($ArrDetail);
      // print_r($ArrDetailUpdate);
      // exit;

      $this->db->trans_start();
        $this->db->insert('tran_material_purchase_header', $ArrHeader);
        $this->db->insert_batch('tran_material_purchase_detail', $ArrDetail);
        $this->db->update_batch('material_request_detail', $ArrDetailUpdate, 'id');
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Kembali	= array(
          'pesan'		=>'Insert purchase order data failed. Please try again later ...',
          'status'	=> 2
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Kembali	= array(
          'pesan'		=>'Insert purchase order data success. Thanks ...',
          'status'	=> 1
        );
        history('Create Purchase Order '.$no_po);
      }
      echo json_encode($Arr_Kembali);
    }

    public function updatePur(){
      $data 			= $this->input->post();
      $session 		  = $this->session->userdata('app_session');
      $no_po			= $data['no_po'];
      $ListPur 		= $data['ListPur'];


      $ArrUpdate		 = array();
      $SumMat = 0;
      foreach($ListPur AS $val => $valx){
        $SumMat += $valx['qty'];

        $ArrUpdate[$val]['id'] 			= $valx['id'];
        $ArrUpdate[$val]['qty'] 		= $valx['qty'];
        $ArrUpdate[$val]['qty_packing'] 		= $valx['qty_packing'];
      }

      $ArrUpdateH = array(
        'total_material'  => $SumMat,
        'updated_by'     => $session['id_user'],
        'updated_date'   => date('Y-m-d H:i:s')
      );

      // print_r($ArrUpdate);
      // exit;
      $this->db->trans_start();
        $this->db->update_batch('tran_material_purchase_detail', $ArrUpdate, 'id');

        $this->db->where('no_po', $no_po);
        $this->db->update('tran_material_purchase_header', $ArrUpdateH);
      $this->db->trans_complete();


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
        history('Update Material Purchasing '.$no_po);
      }
      echo json_encode($Arr_Data);
    }

    //cancel PO sebagian material
  	public function cancel_mat_sebagian(){
  		$session 		= $this->session->userdata('app_session');
  		$id				  = $this->uri->segment(3);
  		$no_po			= $this->uri->segment(4);
  		$id_material= $this->uri->segment(5);

  		$ArrUpdateDetail = array(
  			'deleted' => 'Y',
  			'deleted_by' => $session['id_user'],
  			'deleted_date' => date('Y-m-d H:i:s')
  		);

  		$ArrUpdateD = array(
  			'no_po' => NULL
  		);

  		// print_r($ArrUpdate);
  		// exit;
  		$this->db->trans_start();
  			$this->db->where('id', $id);
  			$this->db->update('tran_material_purchase_detail', $ArrUpdateDetail);

  			$this->db->where('no_po', $no_po);
  			$this->db->where('material', $id_material);
  			$this->db->update('material_planning_footer', $ArrUpdateD);
  		$this->db->trans_complete();


  		if($this->db->trans_status() === FALSE){
  			$this->db->trans_rollback();
  			$Arr_Data	= array(
  				'pesan'		=>'Save process failed. Please try again later ...',
  				'status'	=> 0,
  				'no_po'		=> $no_po
  			);
  		}
  		else{
  			$this->db->trans_commit();
  			$Arr_Data	= array(
  				'pesan'		=>'Save process success. Thanks ...',
  				'status'	=> 1,
  				'no_po'		=> $no_po
  			);
  			history('Cancel Sebagian Material Purchasing Order '.$no_po.'/'.$id_material);
  		}
  		echo json_encode($Arr_Data);
  	}

    //cancel PO
  	public function cancelPO(){
  		$session 		= $this->session->userdata('app_session');
  		$no_po			= $this->uri->segment(3);

  		$ArrUpdateH = array(
  			'sts_ajuan' => 'CNC',
  			'cancel_by' => $session['id_user'],
  			'cancel_date' => date('Y-m-d H:i:s')
  		);
  		$ArrUpdateDetail = array(
  			'deleted'    => 'Y',
  			'deleted_by' => $session['id_user'],
  			'deleted_date' => date('Y-m-d H:i:s')
  		);

  		$ArrUpdateD = array(
  			'no_po' => NULL
  		);

  		// print_r($ArrUpdate);
  		// exit;
  		$this->db->trans_start();
  			$this->db->where('no_po', $no_po);
  			$this->db->update('tran_material_purchase_header', $ArrUpdateH);

  			$this->db->where('no_po', $no_po);
  			$this->db->update('tran_material_purchase_detail', $ArrUpdateDetail);

  			$this->db->where('no_po', $no_po);
  			$this->db->update('material_planning_footer', $ArrUpdateD);
  		$this->db->trans_complete();


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
  			history('Cancel Purchasing Order '.$no_po);
  		}
  		echo json_encode($Arr_Data);
  	}






    public function add(){
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
  			$product    = $this->purchase_model->get_data('ms_inventory_category2');
        $material    = $this->purchase_model->get_data('ms_material','deleted','N');

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

    public function detail(){
      // $this->auth->restrict($this->viewPermission);
      $no_bom 	= $this->input->post('no_bom');
      $header = $this->db->get_where('bom_header',array('no_bom' => $no_bom))->result();
      $detail = $this->db->get_where('bom_detail',array('no_bom' => $no_bom))->result_array();
      $product    = $this->purchase_model->get_data('ms_inventory_category2');
      // print_r($header);
      $data = [
        'header' => $header,
        'detail' => $detail,
        'product' => $product
      ];
      $this->template->set('results', $data);
      $this->template->render('detail_bom', $data);
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

    //===============================================================================================
    //=======================================MATERIAL PLANNING=======================================
    //===============================================================================================
    public function material_planning(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->purchase_model->get_data('tran_material_purchase_header','deleted','N');
      history("View index Material Planning Purchase");
      $this->template->set('results', $data);
      $this->template->title('Purchasing Material');
      $this->template->render('material_planning');
    }

    public function data_side_matplan(){
      $this->purchase_model->get_json_matplan();
    }

    public function add_material_planning(){
      if($this->input->post()){
          $Arr_Kembali	= array();
      		$data			= $this->input->post();
          // print_r($data);
          // exit;
      		$session 		  = $this->session->userdata('app_session');
          $Detail 	    = $data['detail'];
          $Ym					  = date('ym');
          $no_req        = $data['no_req'];
          $no_reqx        = $data['no_req'];

          $bulan_awal        = $data['bulan_awal'];
          $bulan_akhir        = $data['bulan_akhir'];

          $created_by   = 'updated_by';
          $created_date = 'updated_date';
          $tanda        = 'Update ';
          if(empty($no_reqx)){
            //pengurutan kode
            $srcMtr			  = "SELECT MAX(no_req) as maxP FROM material_request WHERE no_req LIKE 'REQ".$Ym."%' ";
            $numrowMtr		= $this->db->query($srcMtr)->num_rows();
            $resultMtr		= $this->db->query($srcMtr)->result_array();
            $angkaUrut2		= $resultMtr[0]['maxP'];
            $urutan2		  = (int)substr($angkaUrut2, 7, 4);
            $urutan2++;
            $urut2			  = sprintf('%03s',$urutan2);
            $no_req	      = "REQ".$Ym.$urut2;

            $created_by   = 'created_by';
            $created_date = 'created_date';
            $tanda        = 'Insert ';
          }

          $ArrDetail	= array();
          $SUM = 0;
          foreach($Detail AS $val => $valx){
            $urut				= sprintf('%03s',$val);
            $SUM += str_replace(',','',$valx['qty_order']);
            $ArrDetail[$val]['no_req']        = $no_req;
            $ArrDetail[$val]['no_req_det']    = $no_req."-".$urut;
            $ArrDetail[$val]['material']      = $valx['code_material'];
            $ArrDetail[$val]['weight'] 	      = str_replace(',','',$valx['qty_order']);
          }

          $ArrHeader		= array(
            'no_req'			  => $no_req,
            'bulan_awal'		=> $bulan_awal,
            'bulan_akhir'	  => $bulan_akhir,
            'sum_mat'	  => $SUM,
            $created_by	    => $session['username'],
            $created_date	  => date('Y-m-d H:i:s')
          );

      		// print_r($ArrHeader);
          // print_r($ArrDetail);
      		// exit;

      		$this->db->trans_start();

          if(empty($no_reqx)){
            $this->db->insert('material_request', $ArrHeader);
          }
          if(!empty($no_reqx)){
            $this->db->where('no_req', $no_req);
            $this->db->update('material_request', $ArrHeader);
          }

          if(!empty($ArrDetail)){
            $this->db->delete('material_request_detail', array('no_req' => $no_req));
      			$this->db->insert_batch('material_request_detail', $ArrDetail);
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
            history($tanda." Material Merge Planning ".$no_req);
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
        $no_req = $this->uri->segment(3);
        $tanda = $this->uri->segment(4);
        $sql_hed  = " SELECT
                        a.*
                      FROM
                        material_request a
                      WHERE
                        a.no_req = '".$no_req."'";
        $header   = $this->db->query($sql_hed)->result();
        $today     = date('Y-m-d');
        if(!empty($header)){
          $today     = $header[0]->bulan_awal;
        }

        $todayAkhir    = date('Y-m-d', strtotime('+2 month', strtotime($today)));
        $today1    = date('Y');
        $nextM1    = date('Y', strtotime('+1 month', strtotime($today)));
        $nextN1    = date('Y', strtotime('+2 month', strtotime($today)));
        $nextO1    = date('Y', strtotime('+3 month', strtotime($today)));

        $today2    = date('m');
        $nextM2    = date('m', strtotime('+1 month', strtotime($today)));
        $nextN2    = date('m', strtotime('+2 month', strtotime($today)));
        $nextO2    = date('m', strtotime('+3 month', strtotime($today)));

        $today3    = date('F Y');
        $nextM3    = date('F Y', strtotime('+1 month', strtotime($today)));
        $nextN3    = date('F Y', strtotime('+2 month', strtotime($today)));
        $nextO3    = date('F Y', strtotime('+3 month', strtotime($today)));

        $sql_det  = " SELECT
                        a.*
                      FROM
                        material_planning_footer a
                        LEFT JOIN material_planning b ON a.no_plan=b.no_plan
                      WHERE a.material <> '0' AND
                        (b.tahun='".$today1."' AND b.bulan='".ltrim($today2, '0')."')
                        OR (b.tahun='".$nextM1."' AND b.bulan='".ltrim($nextM2, '0')."')
                        OR (b.tahun='".$nextN1."' AND b.bulan='".ltrim($nextN2, '0')."')
                        OR (b.tahun='".$nextO1."' AND b.bulan='".ltrim($nextO2, '0')."')
                      GROUP BY
                        a.material
                      ORDER BY
                        a.material";
        // echo $sql_det;exit;
        $detail   = $this->db->query($sql_det)->result_array();

        // print_r($header);
        // exit;
  			$data = [
          'detail' => $detail,
          'today3' => $today3,
          'nextM3' => $nextM3,
          'nextN3' => $nextN3,
          'nextO3' => $nextO3,
          'today1'  => ltrim($today1, '0'),
          'nextM1'  => ltrim($nextM1, '0'),
          'nextN1'  => ltrim($nextN1, '0'),
          'nextO1'  => ltrim($nextO1, '0'),
          'today2'  => ltrim($today2, '0'),
          'nextM2'  => ltrim($nextM2, '0'),
          'nextN2'  => ltrim($nextN2, '0'),
          'nextO2'  => ltrim($nextO2, '0'),
          'tgl_awal' => $today,
          'tgl_akhir' => $todayAkhir,
          'no_req'  => $no_req,
          'tanda' => $tanda
  			];

        $this->template->title('Add Material Planning');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add_material_planning',$data);
      }
    }

    public function print_material_planning(){
      $no_req = $this->uri->segment(3);
      $sql_hed  = " SELECT
                      a.*
                    FROM
                      material_request a
                    WHERE
                      a.no_req = '".$no_req."'";
      $header   = $this->db->query($sql_hed)->result();
      $today     = date('Y-m-d');
      if(!empty($header)){
        $today     = $header[0]->bulan_awal;
      }

      $todayAkhir    = date('Y-m-d', strtotime('+2 month', strtotime($today)));
      $today1    = date('Y');
      $nextM1    = date('Y', strtotime('+1 month', strtotime($today)));
      $nextN1    = date('Y', strtotime('+2 month', strtotime($today)));
      $nextO1    = date('Y', strtotime('+3 month', strtotime($today)));

      $today2    = date('m');
      $nextM2    = date('m', strtotime('+1 month', strtotime($today)));
      $nextN2    = date('m', strtotime('+2 month', strtotime($today)));
      $nextO2    = date('m', strtotime('+3 month', strtotime($today)));

      $today3    = date('F Y');
      $nextM3    = date('F Y', strtotime('+1 month', strtotime($today)));
      $nextN3    = date('F Y', strtotime('+2 month', strtotime($today)));
      $nextO3    = date('F Y', strtotime('+3 month', strtotime($today)));

      $sql_det  = " SELECT
                      a.*
                    FROM
                      material_planning_footer a
                      LEFT JOIN material_planning b ON a.no_plan=b.no_plan
                    WHERE
                    (b.tahun='".$today1."' AND b.bulan='".ltrim($today2, '0')."')
                    OR (b.tahun='".$nextM1."' AND b.bulan='".ltrim($nextM2, '0')."')
                    OR (b.tahun='".$nextN1."' AND b.bulan='".ltrim($nextN2, '0')."')
                    OR (b.tahun='".$nextO1."' AND b.bulan='".ltrim($nextO2, '0')."')
                    GROUP BY
                      a.material
                    ORDER BY
                      a.material";
      // echo $sql_det;exit;
      $detail   = $this->db->query($sql_det)->result_array();
      $temp_print = $this->db->query("SELECT * FROM temp_print WHERE category='purchase request'")->result();
      // print_r($detail);
      // exit;
      $data = [
        'detail' => $detail,
        'today3' => $today3,
        'nextM3' => $nextM3,
        'nextN3' => $nextN3,
        'nextO3' => $nextO3,
        'today1'  => ltrim($today1, '0'),
        'nextM1'  => ltrim($nextM1, '0'),
        'nextN1'  => ltrim($nextN1, '0'),
        'nextO1'  => ltrim($nextO1, '0'),
        'today2'  => ltrim($today2, '0'),
        'nextM2'  => ltrim($nextM2, '0'),
        'nextN2'  => ltrim($nextN2, '0'),
        'nextO2'  => ltrim($nextO2, '0'),
        'tgl_awal' => $today,
        'tgl_akhir' => $todayAkhir,
        'no_req'  => $no_req,
        'temp_print' => $temp_print
      ];

      $this->load->view('print_material_planning',$data);
    }


}

?>
