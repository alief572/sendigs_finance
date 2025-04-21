<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_employee extends Admin_Controller
{
    //Permission
    protected $viewPermission   = "Master_Karyawan.View";
    protected $addPermission    = "Master_Karyawan.Add";
    protected $managePermission = "Master_Karyawan.Manage";
    protected $deletePermission = "Master_Karyawan.Delete";

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Master_employee/Employee_model'
        ));
        $this->template->title('Master Employee');

        date_default_timezone_set("Asia/Bangkok");

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $where = [
            'deleted_date' => NULL
          ];
          $listData = $this->Employee_model->get_data($where);
  
          $data = [
            'result' =>  $listData
          ];
          
          history("View data employee");
          $this->template->set($data);
          $this->template->title('Master Employee');
          $this->template->render('index');
    }

    public function add($id=null,$tanda=null) {
        if(empty($id)){
            $this->auth->restrict($this->addPermission);
          }
          else{
            $this->auth->restrict($this->managePermission);
          }		
		if($this->input->post()){
			$Arr_Kembali			= array();
			$data					= $this->input->post();
			$data_session			= $this->session->userdata;
			
			$id					= $data['id'];
			$nik				= $data['nik'];
			$nm_karyawan		= strtolower($data['nm_karyawan']);
			$no_ktp				= strtolower($data['no_ktp']);
			$tmp_lahir			= strtolower($data['tmp_lahir']);
			$tgl_lahir			= (!empty($data['tgl_lahir']))?date('Y-m-d', strtotime($data['tgl_lahir'])):NULL;
			$gender				= $data['gender'];
			$agama				= $data['agama'];
			$department			= $data['department'];
			$no_ponsel			= strtolower($data['no_ponsel']);
			$email				= strtolower($data['email']);
			$pendidikan			= $data['pendidikan'];
			$ktp_kode_pos		= $data['ktp_kode_pos'];
			$domisili_kode_pos	= $data['domisili_kode_pos'];
			$ktp_alamat			= $data['ktp_alamat'];
			$domisili_alamat	= $data['domisili_alamat'];
			$npwp				= $data['npwp'];
			$bpjs				= $data['bpjs'];
			$tgl_join			= (!empty($data['tgl_join']))?date('Y-m-d', strtotime($data['tgl_join'])):NULL;
			$tgl_end			= (!empty($data['tgl_end']))?date('Y-m-d', strtotime($data['tgl_end'])):NULL;
			$rek_number			= $data['rek_number'];
			$bank_account		= $data['bank_account'];
			$sts_karyawan		= $data['sts_karyawan'];
			$status				= $data['status'];
			
			$created_by 		= 'updated_by';
			$created_date 		= 'updated_date';
			$tandax 			= 'Update';

			if(empty($id)){
				$Y = date('y');
				$created_by 		= 'created_by';
				$created_date 		= 'created_date';
				$tandax 				= 'Insert';
				//kode group
				$q_group		= "SELECT max(nik) as maxP FROM employee WHERE nik LIKE 'ID".$Y."%' ";
				$rest_group		= $this->db->query($q_group)->result_array();
				$angka_group	= $rest_group[0]['maxP'];
				$urut_g			= (int)substr($angka_group, 4, 5);
				$urut_g++;
				$urut			= sprintf('%05s',$urut_g);
				$nik			= "ID".$Y.$urut;
			}

			$ArrHeader1 = array(
				'nik' 	=> $nik,
				'nm_karyawan' => $nm_karyawan,
				'tmp_lahir' => $tmp_lahir,
				'tgl_lahir' => $tgl_lahir,
				'department' => $department,
				'gender' => $gender,
				'agama' => $agama,
				'pendidikan' => $pendidikan,
				'ktp_kode_pos' => $ktp_kode_pos,
				'ktp_alamat' => $ktp_alamat,
				'domisili_kode_pos' => $domisili_kode_pos,
				'domisili_alamat' => $domisili_alamat,
				'no_ponsel' => $no_ponsel,
				'email' => $email,
				'npwp' => $npwp,
				'bpjs' => $bpjs,
				'no_ktp' => $no_ktp,
				'tgl_join' => $tgl_join,
				'tgl_end' => $tgl_end,
				'sts_karyawan' => $sts_karyawan,
				'rek_number' => $rek_number,
				'bank_account' => $bank_account,
				'status' => $status,
				$created_by => $this->id_user,
				$created_date => $this->datetime
			);

			//UPLOAD DOCUMENT
			$target_dir     = "assets/files/";
			$target_dir_u   = get_root3()."/assets/files/";
			$name_file      = 'ttd-'.uniqid()."-".date('Ymdhis');
			$target_file    = $target_dir . basename($_FILES['tanda_tangan']["name"]);
			$name_file_ori  = basename($_FILES['tanda_tangan']["name"]);
			$imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); 
			$nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
			$ArrHeader2 = [];
			if($imageFileType == 'jpeg' OR $imageFileType == 'jpg' OR $imageFileType == 'png'){
				$terupload = move_uploaded_file($_FILES['tanda_tangan']["tmp_name"], $nama_upload);
				$link_url    	= $target_dir.$name_file.".".$imageFileType;

				$ArrHeader2	= array('tanda_tangan' => $link_url);
			}

			$ArrHeader = array_merge($ArrHeader1,$ArrHeader2);
			
			$this->db->trans_start();
				if(empty($id)){
					$this->db->insert('employee', $ArrHeader);
				}
				if(!empty($id)){
					$this->db->where('id', $id);
					$this->db->update('employee', $ArrHeader);
				}
			$this->db->trans_complete();
			
			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=>'Process data failed. Please try again later ...',
					'status'	=> 2
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=>'Process data Success. Thank you & have a nice day ...',
					'status'	=> 1
				);
				history($tandax.' employee '.$nik);
			}
			echo json_encode($Arr_Kembali);
		}
		else{
			
			$restHeader 	= $this->db->get_where('employee',array('id'=>$id))->result(); 
			$bank 			= $this->db->get('bank')->result_array();
			$department		= $this->db->order_by('nama')->get_where('ms_department',array('status'=>'1','deleted_date'=>NULL))->result_array(); 
			$pendidikan		= $this->db->order_by('id')->get_where('list_help',array('group_by'=>'pendidikan','sts'=>'Y'))->result_array(); 
			$agama			= $this->db->order_by('id')->get_where('list_help',array('group_by'=>'agama','sts'=>'Y'))->result_array(); 
			$gender			= $this->db->order_by('id')->get_where('list_help',array('group_by'=>'gender','sts'=>'Y'))->result_array(); 
			$sts_karyawan	= $this->db->order_by('id')->get_where('list_help',array('group_by'=>'status karyawan','sts'=>'Y'))->result_array(); 
			$status			= $this->db->order_by('id')->get_where('list_help',array('group_by'=>'status aktif','sts'=>'Y'))->result_array(); 

            $data = [
                'departmentx'	=> $department,
				'pendidikanx'	=> $pendidikan,
				'bankx'			=> $bank,
				'agamax'		=> $agama,
				'genderx'		=> $gender,
				'sts_karyawanx'	=> $sts_karyawan,
				'statusx'		=> $status,
				'header' 		=> $restHeader,
                'tanda' 		=> $tanda,
            ];
            
            $this->template->set($data);
            $this->template->title('Add Employee');
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
        $this->db->where('id',$id)->update("employee",$data);

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
          history("Delete employee master : ".$id);
        }
        echo json_encode($status);
      }

    
}
