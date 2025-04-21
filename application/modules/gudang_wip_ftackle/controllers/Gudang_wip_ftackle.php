<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gudang_wip_ftackle extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Gudang_WIP_F-Tackle.View';
    protected $addPermission  	= 'Gudang_WIP_F-Tackle.Add';
    protected $managePermission = 'Gudang_WIP_F-Tackle.Manage';
    protected $deletePermission = 'Gudang_WIP_F-Tackle.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Gudang_wip_ftackle/gudang_wip_ftackle_model'
                                ));
        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      $listSO = $this->db->get_where('so_internal',array('deleted_date'=>NULL))->result_array();
      $data = [
        'listSO' => $listSO
      ];

      history("View data gudang wip ftackle");
      $this->template->title('Gudang WIP / F-Tackle');
      $this->template->render('index',$data);
    }

    public function data_side_gudang_wip(){
  		$this->gudang_wip_ftackle_model->data_side_gudang_wip();
  	}

    public function show_history_in_out_wip_detail(){
      $data       = $this->input->post();
      // print_r($data);
      // echo $data['tanda'];
      // exit;
      $tanda  	    = $data['tanda'];
      $sales_order  = $data['sales_order'];
      $code_lv4  	  = $data['code_lv4'];
      $tgl_awal     = date('Y-m-d',strtotime($data['tgl_awal']));
      $tgl_akhir    = date('Y-m-d',strtotime($data['tgl_akhir']));
  
      if($tanda == 'out'){
        $transaksi	= $this->db
                ->select('COUNT(b.id) AS qty, 
                          a.code_lv4,
                          b.no_spk,
                          a.nama_product,
                          a.so_number,
                          b.qc_by AS created_by,
                          b.qc_date AS created_date')
                ->from('so_internal_product b')
                ->join('so_internal_spk z','b.id_key_spk=z.id','left')
                ->join('so_internal a','a.id=z.id_so AND z.status_id = "1"','left')
                ->join('new_inventory_4 y','a.code_lv4=y.code_lv4','left')
                ->where('a.deleted_date',NULL)
                ->where('y.code_lv1','P123000009')
                ->where('DATE(b.qc_date) >=',$tgl_awal)
                ->where('DATE(b.qc_date) <=',$tgl_akhir)
                ->where('a.code_lv4',$code_lv4)
                ->group_by('b.id_key_spk')
                ->get()
                ->result_array();
      }
      else{
        $transaksi	= $this->db
                ->select('COUNT(b.id) AS qty, 
                          a.code_lv4,
                          b.no_spk,
                          a.nama_product,
                          a.so_number,
                          b.close_by AS created_by,
                          b.close_date AS created_date')
                ->from('so_internal_product b')
                ->join('so_internal_spk z','b.id_key_spk=z.id','left')
                ->join('so_internal a','a.id=z.id_so AND z.status_id = "1"','left')
                ->join('new_inventory_4 y','a.code_lv4=y.code_lv4','left')
                ->where('a.deleted_date',NULL)
                ->where('y.code_lv1','P123000009')
                ->where('DATE(b.close_date) >=',$tgl_awal)
                ->where('DATE(b.close_date) <=',$tgl_akhir)
                ->where('a.code_lv4',$code_lv4)
                ->group_by('b.id_key_spk')
                ->get()
                ->result_array();
      }
      // echo $this->db->last_query();
      // print_r($transaksi);
      // exit;
      $ArrTrans_IN = [];
      foreach ($transaksi as $key => $value) {
        $ArrTrans_IN[$value['code_lv4']][] = $value;
      }
      $dataArr = [
        'get_in_trans' 	=> $ArrTrans_IN,
        'code_lv4' 		  => $code_lv4,
        'GET_USER'      => get_list_user()
      ];
  
      $data_html = $this->load->view('history_in_out_wip_detail', $dataArr, TRUE);
      // print_r($ArrTrans_IN);
      // echo $data_html;
      // exit;
      $Arr_Kembali	= array(
        'status'	=> 1,
        'data_html'	=> $data_html
      );
      echo json_encode($Arr_Kembali);
    }
}

?>
