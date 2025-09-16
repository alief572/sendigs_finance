<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author harboens
 * @copyright Copyright (c) 2019, Harboens
 *
 * This is controller for Budget
 */

class Budget extends Admin_Controller {

    protected $viewPermission   = "Budget.View";
    protected $addPermission    = "Budget.Add";
    protected $managePermission = "Budget.Manage";
    protected $deletePermission = "Budget.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Budget/Budget_model','All/All_model'
                                ));
        $this->template->title('Manage Data Budget');
        $this->template->page_icon('fa fa-table');
        date_default_timezone_set("Asia/Bangkok");
    }

    public function index() {
        $this->auth->restrict($this->viewPermission);
        $data = $this->Budget_model->GetBudget();
        $this->template->set('results', $data);
        $this->template->title('Budget List');
        $this->template->render('list');
    }

    public function create() {
        $this->auth->restrict($this->addPermission);
        $datcoa     = $this->All_model->GetCoaComboCategory();
        $datdivisi  = $this->All_model->GetDivisiCombo();
		$this->template->set('datdivisi',$datdivisi);
        $this->template->set('datcoa',$datcoa);
		$this->template->title('Input Budget');
        $this->template->render('budget_form');
    }

    public function edit($id) {
        $this->auth->restrict($this->managePermission);
        $data  = $this->Budget_model->find_by(array('id' => $id));
        if(!$data) {
            $this->template->set_message("Invalid Budget", 'error');
            redirect('budget');
        }
        $datcoa     = $this->All_model->GetCoaComboCategory();
        $datdivisi  = $this->All_model->GetDivisiCombo();
		$this->template->set('datdivisi',$datdivisi);
        $this->template->set('datcoa',$datcoa);
        $this->template->set('data',$data);
        $this->template->title('Edit Budget');
        $this->template->render('budget_form');
    }

    public function save_data(){
        $type           = $this->input->post("type");
        $id             = $this->input->post("id");
		$tahun  		= $this->input->post("tahun");
        $coa       		= $this->input->post("coa");
        $total			= $this->input->post("total");
        $terpakai		= $this->input->post("terpakai");
        $sisa       	= $this->input->post('sisa');
        $info     		= $this->input->post('info');
        $divisi     	= $this->input->post('divisi');
        $tipe_pr       	= $this->input->post('tipe_pr');
		if(is_array($tipe_pr)){
			$tipe_pr = implode(",",$tipe_pr);
		}
		for($i=1;$i<=12;$i++){
			${"bulan_".$i} = $this->input->post('bulan_'.$i);
		}
        if($type=="edit") {
            $this->auth->restrict($this->managePermission);
            if($id!="")
            {
                $data = array(
                            array(
								'id'=>$id,'info'=>$info,
								'tahun'=>$tahun,
//                                'coa'=>$coa,
								'total'=>$total,
								'divisi'=>$divisi,
                                'tipe_pr'=>$tipe_pr,
                                'sisa'=>($total-$terpakai),
								'bulan_1'=>$bulan_1,'bulan_2'=>$bulan_2,'bulan_3'=>$bulan_3,'bulan_4'=>$bulan_4,'bulan_5'=>$bulan_5,'bulan_6'=>$bulan_6,
								'bulan_7'=>$bulan_7,'bulan_8'=>$bulan_8,'bulan_9'=>$bulan_9,'bulan_10'=>$bulan_10,'bulan_11'=>$bulan_11,'bulan_12'=>$bulan_12,
                            )
                        );
                $result = $this->Budget_model->update_batch($data,'id');
                $keterangan     = "SUKSES, Edit data Budget ".$id.", atas Nama : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();

            } else {
                $result = FALSE;
                $keterangan     = "GAGAL, Edit data Budget ".$id.", atas ID : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
            $this->auth->restrict($this->addPermission);
            $data =  array(
						'tahun'=>$tahun,'info'=>$info,
						'coa'=>$coa,
						'total'=>$total,
						'terpakai'=>$terpakai,
						'tipe_pr'=>$tipe_pr,
						'sisa'=>$total,
						'divisi'=>$divisi,
						'bulan_1'=>$bulan_1,'bulan_2'=>$bulan_2,'bulan_3'=>$bulan_3,'bulan_4'=>$bulan_4,'bulan_5'=>$bulan_5,'bulan_6'=>$bulan_6,
						'bulan_7'=>$bulan_7,'bulan_8'=>$bulan_8,'bulan_9'=>$bulan_9,'bulan_10'=>$bulan_10,'bulan_11'=>$bulan_11,'bulan_12'=>$bulan_12,
					);
            $id = $this->Budget_model->insert($data);
            if(is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data Budget ".$id.", atas ID : ".$id;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result         = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data Budget ".$id.", atas ID : ".$id;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
    }

    function hapus_data($id)
    {
        $this->auth->restrict($this->deletePermission);
        if($id!=''){
            $result = $this->Budget_model->delete($id);
            $keterangan     = "SUKSES, Delete data Budget ".$id;
            $status         = 1; $nm_hak_akses   = $this->deletePermission; $kode_universal = $id; $jumlah = 1;
            $sql            = $this->db->last_query();

        } else {
            $result = 0;
            $keterangan     = "GAGAL, Delete data Budget ".$id;
            $status         = 0; $nm_hak_akses   = $this->deletePermission; $kode_universal = $id; $jumlah = 1;
            $sql            = $this->db->last_query();
        }
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'delete' => $result,
                'idx'=>$id
                );
        echo json_encode($param);
    }

    public function search_old($coa,$tgl,$dtl='') {
		$tahun = date("Y",strtotime($tgl));
        $data = $this->Budget_model->SearchBudget($coa,$tahun);
		$param=array();
		if($data!==false){
			if($dtl==''){
				$bulan=date("n",strtotime($tgl));
				$budget=$data->{"bulan_".$bulan};
				$sisa=($data->{"bulan_".$bulan}-$data->{"terpakai_bulan_".$bulan});
				$param = array(
						'budget' => $budget,
						'sisa'=>$sisa,
						'tipe'=>$data->tipe_pr,
						);
			}else{
				$param=$data;
			}
		}else{
			if($dtl==''){
				$param = array(
						'budget' =>0,
						'sisa'=>0,
						'tipe'=>'',
						);
			}
		}
		echo json_encode($param);
   }

    public function search($coa,$tgl,$dtl='') {
		$tahun = date("Y",strtotime($tgl));
        $data = $this->Budget_model->SearchBudget($coa,$tahun);
		$param=array();
		if($data!==false){
			if($dtl==''){
				$bulannow=date("n");
				$bulan=date("n",strtotime($tgl));
				$budget=0;
				$terpakai=0;
				for($i=1;$i<=$bulannow;$i++){
					$budget=($budget+$data->{"bulan_".$i});
					$terpakai=($terpakai+$data->{"terpakai_bulan_".$i});
				}
				$sisa=($budget-$terpakai);
				$param = array(
						'budget' => $budget,
						'terpakai' => $terpakai,
						'sisa'=>$sisa,
						'tipe'=>$data->tipe_pr,
						);
			}else{
				$param=$data;
			}
		}else{
			if($dtl==''){
				$param = array(
						'budget' =>0,
						'terpakai' =>0,
						'sisa'=>0,
						'tipe'=>'',
						);
			}
		}
		echo json_encode($param);
   }

}
