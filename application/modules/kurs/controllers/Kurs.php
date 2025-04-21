<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Kurs extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Master_Kurs.View';
    protected $addPermission  	= 'Master_Kurs.Add';
    protected $managePermission = 'Master_Kurs.Manage';
    protected $deletePermission = 'Master_Kurs.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Kurs/Kurs_model'
        ));
        $this->template->title('Manage Material Category');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');

    		$this->template->page_icon('fa fa-users');
    		
        $listData = $this->db->order_by('id','desc')->get_where('master_kurs',array('deleted_date'=>NULL))->result();

        $data = [
          'result' =>  $listData,
          'GET_USERNAME' =>  get_list_user(),
        ];
        
        history("View index master kurs");
        $this->template->set($data);
        $this->template->title('Master Kurs');
        $this->template->render('index');
    }

    public function add($id=null){	
      if(empty($id)){
        $this->auth->restrict($this->addPermission);
      }
      else{
        $this->auth->restrict($this->managePermission);
      }		
      if($this->input->post()){
        $post = $this->input->post();

        $id             = $post['id'];
        $kode_currency  = $post['kode_currency'];
        $to_currency  = $post['to_currency'];
        $tanggal        = (!empty($post['tanggal']))?date('Y-m-d',strtotime($post['tanggal'])):NULL;
        $kurs           = str_replace(',','',$post['kurs']);

        $last_by    = (!empty($id))?'updated_by':'created_by';
        $last_date  = (!empty($id))?'updated_date':'created_date';
        $label      = (!empty($id))?'Edit':'Add';

        $dataProcess = [
          'kode_currency'  => $kode_currency,
          'to_currency'  => $to_currency,
          'tanggal'  => $tanggal,
          'kurs'		  => $kurs,
          $last_by	  => $this->id_user,
          $last_date	=> $this->datetime
        ];

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('master_kurs',$dataProcess);
          }
          else{
            $this->db->where('id',$id);
            $this->db->update('master_kurs',$dataProcess);
          }
        $this->db->trans_complete();	

        if($this->db->trans_status() === FALSE){
          $this->db->trans_rollback();
          $status	= array(
            'pesan'		=>'Failed process data!',
            'status'	=> 0
          );
        } else {
          $this->db->trans_commit();
          $status	= array(
            'pesan'		=>'Success process data!',
            'status'	=> 1
          );
          history($label." master kurs: ".$id.' - '.$kode_currency.' - '.$tanggal.' - '.$kurs);
        }
        echo json_encode($status);
      }
      else{
        $listData = $this->db->get_where('master_kurs',array('id' => $id))->result();

        $data = [
          'listData' => $listData,
          'currency' => $this->db->order_by('kode','asc')->get('master_currency')->result_array()
        ];
        $this->template->set($data);
        $this->template->render('add');
      }
    }

	  public function delete(){
      $this->auth->restrict($this->deletePermission);
      
      $id = $this->input->post('id');
      $data = [
        'deleted_by' 	  => $this->id_user,
        'deleted_date' 	=> $this->datetime
      ];

      $this->db->trans_begin();
      $this->db->where('id',$id)->update("master_kurs",$data);

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $status	= array(
          'pesan'		=>'Failed process data!',
          'status'	=> 0
        );
      } else {
        $this->db->trans_commit();
        $status	= array(
          'pesan'		=>'Success process data!',
          'status'	=> 1
        );
        history("Delete master kurs : ".$id);
      }
      echo json_encode($status);
    }

    public function update_kurs_to_all(){
      $this->auth->restrict($this->managePermission);
      $id = $this->input->post('id');
      
      $data = [
        'price_by' 	  => $this->id_user,
        'price_date' 	=> $this->datetime
      ];


      $dataKurs = $this->db->get_where('master_kurs',array('id'=>$id))->result();
      $id_kurs  = $dataKurs[0]->id;
      $kurs     = $dataKurs[0]->kurs;
      $kurs_date= $dataKurs[0]->tanggal;

      //Update Price RawMaterial & STok
      $dataRawStok = $this->db->get_where('new_inventory_4',array('deleted_date'=>NULL))->result_array();
      
      $ArrUpdateRawStok = [];
      foreach ($dataRawStok as $key => $value) {
        $ArrUpdateRawStok[$key]['id']                      = $value['id'];
        $ArrUpdateRawStok[$key]['price_ref_usd']           = (!empty($value['price_ref']) AND $value['price_ref'] > 0)?$value['price_ref']/$kurs:null;
        $ArrUpdateRawStok[$key]['price_ref_high_usd']      = (!empty($value['price_ref_high']) AND $value['price_ref_high'] > 0)?$value['price_ref_high']/$kurs:null;
        $ArrUpdateRawStok[$key]['price_ref_new_usd']       = (!empty($value['price_ref_new']) AND $value['price_ref_new'] > 0)?$value['price_ref_new']/$kurs:null;
        $ArrUpdateRawStok[$key]['price_ref_high_new_usd']  = (!empty($value['price_ref_high_new']) AND $value['price_ref_high_new'] > 0)?$value['price_ref_high_new']/$kurs:null;
        $ArrUpdateRawStok[$key]['price_ref_use_usd']       = (!empty($value['price_ref_use']) AND $value['price_ref_use'] > 0)?$value['price_ref_use']/$kurs:null;
        $ArrUpdateRawStok[$key]['id_kurs']                 = $id_kurs;
        $ArrUpdateRawStok[$key]['kurs']                    = $kurs;
      }

      //Update Man Power
      $dataLastMP = $this->db->order_by('id','DESC')->limit(1)->get('rate_man_power')->result();
      $dataLastMPDetail = $this->db->get_where('rate_man_power_detail',array('kode'=>$dataLastMP[0]->kode))->result_array();

      $Ym				    = date('ym');
      $srcMtr			  = "SELECT MAX(kode) as maxP FROM rate_man_power WHERE kode LIKE 'MPR".$Ym."%' ";
      $numrowMtr		= $this->db->query($srcMtr)->num_rows();
      $resultMtr		= $this->db->query($srcMtr)->result_array();
      $angkaUrut2		= $resultMtr[0]['maxP'];
      $urutan2		  = (int)substr($angkaUrut2, 7, 3);
      $urutan2++;
      $urut2			  = sprintf('%03s',$urutan2);
      $kode	      = "MPR".$Ym.$urut2;

      $ArrUpdateMP = array(
        'kode'				      => $kode,
        'tanggal'			      => date('Y-m-d'),
        'total_direct'	    => $dataLastMP[0]->total_direct,
        'total_bpjs'	    	=> $dataLastMP[0]->total_bpjs,
        'total_biaya_lain'	=> $dataLastMP[0]->total_biaya_lain,
        
        'upah_per_bulan_dollar'	=> (!empty($dataLastMP[0]->upah_per_bulan) AND $dataLastMP[0]->upah_per_bulan > 0)?$dataLastMP[0]->upah_per_bulan/$kurs:null,
        'upah_per_jam_dollar'	  => (!empty($dataLastMP[0]->upah_per_jam) AND $dataLastMP[0]->upah_per_jam > 0)?$dataLastMP[0]->upah_per_jam/$kurs:null,
        'upah_per_bulan'	      => $dataLastMP[0]->upah_per_bulan,
        'upah_per_jam'	    	  => $dataLastMP[0]->upah_per_jam,

        'id_kurs'	    	  => $id_kurs,
        'rate_dollar'	    => $kurs,
        'kurs_tanggal'	  => $kurs_date,
        'kurs_by'	    	  => $this->id_user,
        'kurs_date'	    	=> $this->datetime,
        'created_by'	    => $this->id_user,
        'created_date'	  => $this->datetime
      );

      $ArrUpdateMPDetail = [];
      foreach($dataLastMPDetail AS $val => $valx){
        $ArrUpdateMPDetail[$val]['kode'] 			    = $kode;
        $ArrUpdateMPDetail[$val]['category'] 		  = $valx['category'];
        $ArrUpdateMPDetail[$val]['nama'] 			    = $valx['nama'];
        $ArrUpdateMPDetail[$val]['nilai'] 	 		  = $valx['nilai'];
        $ArrUpdateMPDetail[$val]['keterangan'] 	  = $valx['keterangan'];
        $ArrUpdateMPDetail[$val]['harga_per_pcs'] = $valx['harga_per_pcs'];
      }

      //Update Delivery
      $dataDelivery = $this->db->get_where('rate_delivery',array('deleted_date'=>NULL))->result_array();
      
      $ArrUpdateDelivery = [];
      foreach ($dataDelivery as $key => $value) {
        $ArrUpdateDelivery[$key]['id']            = $value['id'];
        $ArrUpdateDelivery[$key]['price_usd']     = (!empty($value['price']) AND $value['price'] > 0)?$value['price']/$kurs:null;
        $ArrUpdateDelivery[$key]['id_kurs']       = $id_kurs;
        $ArrUpdateDelivery[$key]['kurs']          = $kurs;
        $ArrUpdateDelivery[$key]['kurs_tanggal']  = $kurs_date;
        $ArrUpdateDelivery[$key]['kurs_by']       = $this->id_user;
        $ArrUpdateDelivery[$key]['kurs_date']     = $this->datetime;
      }

      //Update Machine
      $dataMachine = $this->db->get_where('rate_machine',array('deleted_date'=>NULL))->result_array();
      
      $ArrUpdateMachine = [];
      foreach ($dataMachine as $key => $value) {
        $harga_usd    = (!empty($value['harga_mesin']) AND $value['harga_mesin'] > 0)?$value['harga_mesin']/$kurs:null;
        $depresiasi   = ($harga_usd > 0 AND $value['est_manfaat'] > 0)?$harga_usd/($value['est_manfaat']*12):null;
        $biaya_mesin  = ($depresiasi > 0 AND $value['used_hour_month'] > 0)?$depresiasi/$value['used_hour_month']:null;

        $ArrUpdateMachine[$key]['id']               = $value['id'];
        $ArrUpdateMachine[$key]['harga_mesin_usd']  = $harga_usd;
        $ArrUpdateMachine[$key]['depresiasi_bulan'] = $depresiasi;
        $ArrUpdateMachine[$key]['biaya_mesin']      = $biaya_mesin;
        $ArrUpdateMachine[$key]['id_kurs']       = $id_kurs;
        $ArrUpdateMachine[$key]['kurs']          = $kurs;
        $ArrUpdateMachine[$key]['kurs_tanggal']  = $kurs_date;
        $ArrUpdateMachine[$key]['kurs_by']       = $this->id_user;
        $ArrUpdateMachine[$key]['kurs_date']     = $this->datetime;
      }

      //Update Mold
      $dataMold = $this->db->get_where('rate_mold',array('deleted_date'=>NULL))->result_array();
      
      $ArrUpdateMold = [];
      foreach ($dataMold as $key => $value) {
        $harga_usd    = (!empty($value['harga_mesin']) AND $value['harga_mesin'] > 0)?$value['harga_mesin']/$kurs:null;
        $depresiasi   = ($harga_usd > 0 AND $value['est_manfaat'] > 0)?$harga_usd/($value['est_manfaat']*12):null;
        $biaya_mesin  = ($depresiasi > 0 AND $value['used_hour_month'] > 0)?$depresiasi/$value['used_hour_month']:null;

        $ArrUpdateMold[$key]['id']               = $value['id'];
        $ArrUpdateMold[$key]['harga_mesin_usd']  = $harga_usd;
        $ArrUpdateMold[$key]['depresiasi_bulan'] = $depresiasi;
        $ArrUpdateMold[$key]['biaya_mesin']      = $biaya_mesin;
        $ArrUpdateMold[$key]['id_kurs']       = $id_kurs;
        $ArrUpdateMold[$key]['kurs']          = $kurs;
        $ArrUpdateMold[$key]['kurs_tanggal']  = $kurs_date;
        $ArrUpdateMold[$key]['kurs_by']       = $this->id_user;
        $ArrUpdateMold[$key]['kurs_date']     = $this->datetime;
      }

      $this->db->trans_begin();
        $this->db->where('id',$id)->update("master_kurs",$data);
        //rawstok
        if(!empty($ArrUpdateRawStok)){
          $this->db->update_batch('new_inventory_4',$ArrUpdateRawStok,'id');
        }
        //man power
        $this->db->insert('rate_man_power',$ArrUpdateMP);
        if(!empty($ArrUpdateMPDetail)){
          $this->db->insert_batch('rate_man_power_detail',$ArrUpdateMPDetail);
        }
        //delivery
        if(!empty($ArrUpdateDelivery)){
          $this->db->update_batch('rate_delivery',$ArrUpdateDelivery,'id');
        }
        //machine
        if(!empty($ArrUpdateMachine)){
          $this->db->update_batch('rate_machine',$ArrUpdateMachine,'id');
        }
        //mold
        if(!empty($ArrUpdateMold)){
          $this->db->update_batch('rate_mold',$ArrUpdateMold,'id');
        }
      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $status	= array(
          'pesan'		=>'Failed process data!',
          'status'	=> 0
        );
      } else {
        $this->db->trans_commit();
        $status	= array(
          'pesan'		=>'Success process data!',
          'status'	=> 1
        );
        history("Update kurs price to all : ".$id);
      }
      echo json_encode($status);
    }

}
