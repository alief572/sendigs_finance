<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author harboens
 * @copyright Copyright (c) 2020, harboens
 *
 * This is controller for ms_coa_category
 */

class Coa_category extends Admin_Controller {

    //Permission
    protected $viewPermission   = "COACategory.View";
    protected $addPermission    = "COACategory.Add";
    protected $managePermission = "COACategory.Manage";
    protected $deletePermission = "COACategory.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Coa_category/Coa_category_model','jurnal_nomor/Acc_model'
                                ));
        $this->template->title('Manage Data Kategori COA');
        $this->template->page_icon('fa fa-table');
		$this->datatipe=array('ASET'=>'ASET','NONSTOK'=>'NONSTOK','STOK'=>'STOK','RUTIN'=>'RUTIN','NONRUTIN'=>'NONRUTIN');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $data = $this->Coa_category_model->ListCoaCategory();
        $datcoa = $this->Acc_model->GetCoaCombo();
        $this->template->set('datatipe', $this->datatipe);
        $this->template->set('datcoa', $datcoa);
        $this->template->set('results', $data);
        $this->template->title('Kategori COA');
        $this->template->render('list');
    }

    //Save data ajax
    public function save_data(){
        $id = $this->input->post("id");
        $nama = $this->input->post("nama");
        $coa = $this->input->post("coa");
        $tipe = $this->input->post("tipe");
        if($id!="") {
//            $this->auth->restrict($this->managePermission);
			$data = array(
						array(
							'id'=>$id,
							'nama'=>$nama,
							'coa'=>$coa,
							'tipe'=>$tipe,
							'modified_by'=> $this->auth->user_id(),
							'modified_on'=>date("Y-m-d h:i:s"),
						)
					);
			//Update data
			$result = $this->Coa_category_model->update_batch($data,'id');
			$keterangan     = "SUKSES, Edit data coa category ".$id.", atas Nama : ".$nama;
			$status         = 1;
			$nm_hak_akses   = $this->addPermission;
			$id_universal 	= $id;
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $id_universal, $keterangan, $jumlah, $sql, $status);
        } else {
            $this->auth->restrict($this->addPermission);
            $data = array(
						'nama'=>$nama,
						'coa'=>$coa,
						'tipe'=>$tipe,
						'created_by'=> $this->auth->user_id(),
						'created_on'=>date("Y-m-d h:i:s"),
					);
            //Add Data
            $id = $this->Coa_category_model->insert($data);
            if(is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data coa kcategory ".$id.", atas Nama : ".$nama;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $id_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result         = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data coa category ".$id.", atas Nama : ".$nama;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $id_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $id_universal, $keterangan, $jumlah, $sql, $status);

        }
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
    }

    function hapus_data()
    {
        $this->auth->restrict($this->deletePermission);
        $id = $this->uri->segment(3);

        if($id!=''){

            $result = $this->Coa_category_model->delete($id);

            $keterangan     = "SUKSES, Delete data coa category".$id;
            $status         = 1;
            $nm_hak_akses   = $this->addPermission;
            $id_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();

        }
        else
        {
            $result = 0;
            $keterangan     = "GAGAL, Delete data coa category ".$id;
            $status         = 0;
            $nm_hak_akses   = $this->addPermission;
            $id_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
        }
        //Save Log
            simpan_aktifitas($nm_hak_akses, $id_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
                'delete' => $result,
                'idx'=>$id
                );
        echo json_encode($param);
    }

}
?>
