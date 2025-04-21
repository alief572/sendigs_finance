<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Delivery_rate extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Delivery_Rate.View';
    protected $addPermission  	= 'Delivery_Rate.Add';
    protected $managePermission = 'Delivery_Rate.Manage';
    protected $deletePermission = 'Delivery_Rate.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
          'Delivery_rate/Delivery_rate_model'
        ));
        $this->template->title('Manage Product Jenis');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');

    		$this->template->page_icon('fa fa-users');
    		
        $where = [
          'deleted_date' => NULL,
        ];
        $listData = $this->Delivery_rate_model->get_data($where);

        $data = [
          'result' =>  $listData,
          'country' =>  get_list_country()
        ];
        
        history("View index delivery rate");
        $this->template->set($data);
        $this->template->title('Rate Delivery');
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

        $id               = $post['id'];
        $type             = $post['type'];
        $id_country       = $post['id_country'];
        $category         = $post['category'];
        $shipping_method  = $post['shipping_method'];
        $transport_type   = $post['transport_type'];
        $area_tujuan      = $post['area_tujuan'];
        $price            = str_replace(',','',$post['price']);

        $last_by    = (!empty($id))?'updated_by':'created_by';
        $last_date  = (!empty($id))?'updated_date':'created_date';
        $label      = (!empty($id))?'Edit':'Add';

        $dataProcess = [
          'type'  => $type,
          'id_country'  => $id_country,
          'category'  => $category,
          'shipping_method'  => $shipping_method,
          'transport_type'  => $transport_type,
          'area_tujuan'		  => $area_tujuan,
          'price'  => $price,
          $last_by	  => $this->id_user,
          $last_date	=> $this->datetime
        ];

        // print_r($dataProcess);
        // exit;

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('rate_delivery',$dataProcess);
          }
          else{
            $this->db->where('id',$id);
            $this->db->update('rate_delivery',$dataProcess);
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
          history($label." rate delivery: ".$id);
        }
        echo json_encode($status);
      }
      else{
        $listData   = $this->db->get_where('rate_delivery',array('id' => $id))->result();
        $country 		= $this->db->order_by('country_name','asc')->get('country')->result_array();
        $category_lokal 		= $this->db->order_by('urut','asc')->get_where('list',array('menu'=>'delivery rate','category'=>'category'))->result_array();
        $shipping_method 		= $this->db->order_by('urut','asc')->get_where('list',array('menu'=>'delivery rate','category'=>'method'))->result_array();

        $data = [
          'listData' => $listData,
          'country' => $country,
          'category_lokal' => $category_lokal,
          'shipping_method_' => $shipping_method,
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
      $this->db->where('id',$id)->update("rate_delivery",$data);

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
        history("Delete rate delivery : ".$id);
      }
      echo json_encode($status);
    }

    public function add_country(){	
      $this->auth->restrict($this->addPermission);
      if($this->input->post()){
        $post = $this->input->post();

        $get_country = get_list_country_all();
        $country_code     = $post['country_code'];
        $nama_negara = (!empty($get_country[$country_code]['nama']))?$get_country[$country_code]['nama']:'';

        $dataProcess = [
          'country_code'  => $country_code,
          'country_name'  => $nama_negara,
        ];

        // print_r($dataProcess);
        // exit;

        $this->db->trans_start();
          $this->db->insert('country',$dataProcess);
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
        }
        echo json_encode($status);
      }
      else{
        $country 		= $this->db->order_by('a.name','asc')->join('country b','a.iso3=b.country_code','left')->get_where('country_all a',array('b.country_code'=>NULL))->result_array();

        $data = [
          'country' => $country
        ];
        $this->template->set($data);
        $this->template->render('add_country');
      }
    }

    public function update_kurs(){
      $data 		= $this->input->post();
      $session 	= $this->session->userdata('app_session');
      $id  		  = $data['id'];
      $header   	= $this->db->order_by('id','desc')->limit(1)->get('rate_delivery',array('id'=>$id))->result();
      $kurs   	= $this->db->order_by('id','desc')->limit(1)->get_where('master_kurs',array('deleted_date'=>NULL))->result();
      $price_usd = ($header[0]->price > 0 AND $kurs[0]->kurs > 0)?$header[0]->price/$kurs[0]->kurs : 0;
      $ArrHeader = array(
        'price_usd'		=> $price_usd,
        'id_kurs'		=> $kurs[0]->id,
        'kurs'	=> $kurs[0]->kurs,
        'kurs_tanggal'=> $kurs[0]->tanggal,
        'kurs_by'	  	=> $session['id_user'],
        'kurs_date'	=> date('Y-m-d H:i:s')
      );

      $this->db->trans_start();
          $this->db->where('id', $id);
          $this->db->update('rate_delivery', $ArrHeader);
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
          'pesan'		=>'Kurs berhasil di update.',
          'status'	=> 1
        );
        history("Update Kurs di delivery rate, ".$id);
      }

      echo json_encode($Arr_Data);

  }

}
