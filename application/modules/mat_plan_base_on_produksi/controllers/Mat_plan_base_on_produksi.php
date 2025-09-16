<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mat_plan_base_on_produksi extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Material_Planning_Base_On_Produksi.View';
    protected $addPermission  	= 'Material_Planning_Base_On_Produksi.Add';
    protected $managePermission = 'Material_Planning_Base_On_Produksi.Manage';
    protected $deletePermission = 'Material_Planning_Base_On_Produksi.Delete';

   public function __construct(){
        parent::__construct();

        $this->load->model(array('Mat_plan_base_on_produksi/mat_plan_base_on_produksi_model'));
        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View index material planning base on production");
      $this->template->title('Material Planning / Based On Production');
      $this->template->render('index');
    }

    public function data_side_material_planning(){
  		$this->mat_plan_base_on_produksi_model->data_side_material_planning();
  	}

    public function detail(){
      $so_number 	= $this->input->post('so_number');
      $detail 	= $this->db->get_where('material_planning_base_on_produksi_detail',array('so_number' => $so_number))->result_array();

      $data = [
        'so_number' => $so_number,
        'detail' => $detail,
        'GET_LEVEL4' 	=> get_inventory_lv4()
      ];
      $this->template->set('results', $data);
      $this->template->render('detail', $data);
    }

    public function material_planning($so_number=null){
      if($this->input->post()){
        $data 			  = $this->input->post();
    		$session      = $this->session->userdata('app_session');

    		$so_number		    = $data['so_number'];
        $tgl_dibutuhkan	  = (!empty($data['tgl_dibutuhkan']))?date('Y-m-d',strtotime($data['tgl_dibutuhkan'])):NULL;
        $detail		        = $data['detail'];

      
        $ArrPlanningDetail = [];
        $SUM_USE = 0;
        $SUM_PROPOSE = 0;
        $ArrStock = [];
        if(!empty($detail)){
          foreach ($detail as $key => $value) {
            //Planning
            $use_stock = str_replace(',','',$value['use_stock']);
            $propose = str_replace(',','',$value['propose']);

            $ArrPlanningDetail[$key]['id'] = $value['id'];
            $ArrPlanningDetail[$key]['stock_free'] = $value['stock_free'];
            $ArrPlanningDetail[$key]['min_stock'] = $value['min_stok'];
            $ArrPlanningDetail[$key]['max_stock'] = $value['max_stok'];
            $ArrPlanningDetail[$key]['use_stock'] = $use_stock;
            $ArrPlanningDetail[$key]['propose_purchase'] = $propose;
            $ArrPlanningDetail[$key]['note'] = $value['note'];

            $ArrStock[$key]['id'] = $value['code_material'];
            $ArrStock[$key]['qty'] = $use_stock;

            $SUM_USE += $use_stock;
            $SUM_PROPOSE += $propose;
          }
        }

        $ArrHeader = array(
          'tgl_dibutuhkan'  => $tgl_dibutuhkan,
          'qty_use_stok'  => $SUM_USE,
          'qty_propose'  => $SUM_PROPOSE,
          'updated_by'	    => $this->id_user,
          'updated_date'	  => $this->datetime
        );

        // print_r($ArrBOMDetail);
        // exit;

        $this->db->trans_start();
            $this->db->where('so_number', $so_number);
            $this->db->update('material_planning_base_on_produksi', $ArrHeader);

            if(!empty($ArrPlanningDetail)){
              $this->db->update_batch('material_planning_base_on_produksi_detail', $ArrPlanningDetail, 'id'); 
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
          // booking_warehouse($ArrStock, 1, 1, $so_number, null);
          history("Create material planning  : ".$so_number);
        }
        echo json_encode($Arr_Data);
      }
      else{
        $header 	  = $this->db
                            ->select('a.*, b.due_date, c.nm_customer')
                            ->join('so_internal b','a.so_number=b.so_number','left')
                            ->join('customer c','a.id_customer=c.id_customer','left')
                            ->get_where('material_planning_base_on_produksi a',
                              array
                                (
                                  'a.so_number' => $so_number
                                )
                              )
                            ->result_array();
        $detail 	  = $this->db
                            ->select('a.*, b.max_stok, b.min_stok')
                            ->join('new_inventory_4 b','a.id_material=b.code_lv4','left')
                            ->get_where('material_planning_base_on_produksi_detail a',
                              array
                                (
                                  'a.so_number' => $so_number
                                )
                              )
                            ->result_array();
  
        $data = [
          'so_number' => $so_number,
          'header' => $header,
          'detail' => $detail,
          'GET_LEVEL4' 	=> get_inventory_lv4(),
          'GET_STOK_PUSAT' => getStokMaterial(1)
        ];
        
        $this->template->title('Set Material Planning');
        $this->template->render('material_planning', $data);
      }
  	}

    public function process_booking(){
        $data 			= $this->input->post();
    		$so_number	= $data['so_number'];

        $ArrHeader = array(
          'booking_by'	    => $this->id_user,
          'booking_date'	  => $this->datetime
        );

        $detail = $this->db->get_where('material_planning_base_on_produksi_detail',array('so_number'=>$so_number))->result_array();

        $ArrStock = [];
        if(!empty($detail)){
          foreach ($detail as $key => $value) {
            $ArrStock[$key]['id'] = $value['id_material'];
            $ArrStock[$key]['qty'] = $value['use_stock'];
          }
        }
        // print_r($ArrBOMDetail);
        // exit;

        $this->db->trans_start();
            $this->db->where('so_number', $so_number);
            $this->db->update('material_planning_base_on_produksi', $ArrHeader);
        $this->db->trans_complete();

        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $Arr_Data	= array(
            'pesan'		=>'Process Failed !',
            'status'	=> 0
          );
        }
        else{
          $this->db->trans_commit();
          $Arr_Data	= array(
            'pesan'		=>'Process Success !',
            'status'	=> 1
          );
          
          if(!empty($ArrStock)){
            booking_warehouse($ArrStock, 1, 1, $so_number, null);
          }
          history("booking material planning  : ".$so_number);
        }
        echo json_encode($Arr_Data);
  	}

}

?>
