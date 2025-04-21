<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author harboens
 * @copyright Copyright (c) 2019, Harboens
 *
 * This is controller for Aset
 */

class Aset extends Admin_Controller {

    protected $viewPermission   = "Assets.View";
    protected $addPermission    = "Assets.Add";
    protected $managePermission = "Assets.Manage";
    protected $deletePermission = "Assets.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Aset/Aset_model','jurnal_nomor/Acc_model'
                                ));
        $this->template->title('Manage Data Aset');
        $this->template->page_icon('fa fa-table');
        date_default_timezone_set("Asia/Bangkok");
    }

    public function index() {
        $this->auth->restrict($this->viewPermission);
        $data = $this->Aset_model->GetAset();
        $this->template->set('results', $data);
        $this->template->title('Rencana Pembelian Aset');
        $this->template->render('list');
    }

    public function create() {
        $this->auth->restrict($this->addPermission);
        $datcoa     = $this->Acc_model->GetCoaComboCategory('ASET','',$name='coa');
		$penyusutan = $this->Acc_model->GetCoaComboCategory('PENYUSUTAN','',$name='coa');
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$datcostcenter  = $this->Acc_model->GetCostcenterCombo();
        $this->template->set('datcoa',$datcoa);
		$this->template->set('penyusutan',$penyusutan);
		$this->template->set('datdivisi',$datdivisi);
		$this->template->set('datcostcenter',$datcostcenter);
		$this->template->title('Input Aset');
        $this->template->render('aset_form');
    }

    public function edit($id) {
        $this->auth->restrict($this->managePermission);
        $data  = $this->Aset_model->find_by(array('id' => $id));
        if(!$data) {
            $this->template->set_message("Invalid Aset", 'error');
            redirect('Aset');
        }
        $datcoa     = $this->Acc_model->GetCoaCombo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$penyusutan = $this->Acc_model->GetCoaComboCategory('PENYUSUTAN','',$name='coa');
		$datcostcenter  = $this->Acc_model->GetCostcenterCombo();
		
		$this->template->set('datcostcenter',$datcostcenter);
		$this->template->set('penyusutan',$penyusutan);
		$this->template->set('datdivisi',$datdivisi);
        $this->template->set('datcoa',$datcoa);
        $this->template->set('data',$data);
        $this->template->title('Edit Aset');
        $this->template->render('aset_form');
    }

    public function save_data(){
        $type       = $this->input->post("type");
        $id         = $this->input->post("id");
		$coa    	= $this->input->post("coa");
		$coa_akum   = $this->input->post("penyusutan");
        $nama_aset  = $this->input->post("nama_aset");
        $divisi		= $this->input->post("divisi");
		$costcenter	= $this->input->post("costcenter");
        $qty		= $this->input->post("qty");
		$budget		= $this->input->post("budget");
		$budgetpr		= $this->input->post("budgetpr");
		$budgetpo		= $this->input->post("budgetpo");
		$deskripsi   = $this->input->post('description');
        
        $tahun      = $this->input->post('tahun');
        $bulan      = $this->input->post('bulan');
        if($type=="edit") {
            $this->auth->restrict($this->managePermission);
            if($id!="")
            {
                $data = array(
                            array(
                                'id'=>$id,
								'coa'=>$coa,
                                'nama_aset'=>$nama_aset,
								'divisi'=>$divisi,
                                'budget'=>$budget,
                                'tahun'=>$tahun,
                                'bulan'=>$bulan,
								'budgetpr'=>$budgetpr,
								'budgetpo'=>$budgetpo,
								'qty'=>$qty,
								'status_appr'=>0,
								'deskripsi'=>$deskripsi,
								'costcenter'=>$costcenter,
								'coa_akum'=>$coa_akum,
                            )
                        );
                $result = $this->Aset_model->update_batch($data,'id');
                $keterangan     = "SUKSES, Edit data Aset ".$id.", atas Nama : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();

            } else {
                $result = FALSE;
                $keterangan     = "GAGAL, Edit data Aset ".$id.", atas ID : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
            $this->auth->restrict($this->addPermission);
            $data =  array(
						'coa'=>$coa,
						'nama_aset'=>$nama_aset,
						'divisi'=>$divisi,
						'budget'=>$budget,
						'tahun'=>$tahun,
						'bulan'=>$bulan,
						'budgetpr'=>$budgetpr,
					    'budgetpo'=>$budgetpo,
						'qty'=>$qty,
						'status_appr'=>0,
						'deskripsi'=>$deskripsi,
						'costcenter'=>$costcenter,
						'coa_akum'=>$coa_akum,
					);
            $id = $this->Aset_model->insert($data);
            if(is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data Aset ".$id.", atas ID : ".$id;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result         = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data Aset ".$id.", atas ID : ".$id;
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
            $result = $this->Aset_model->delete($id);
            $keterangan     = "SUKSES, Delete data Aset ".$id;
            $status         = 1; $nm_hak_akses   = $this->deletePermission; $kode_universal = $id; $jumlah = 1;
            $sql            = $this->db->last_query();

        } else {
            $result = 0;
            $keterangan     = "GAGAL, Delete data Aset ".$id;
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

    public function get_aset($tgl) {
		$tahun = date("Y",strtotime($tgl));
		$bulan = date("n",strtotime($tgl));
        $data = $this->Aset_model->SearchBudget($tahun,$bulan);
		$param=array();
		if($data!==false){
			$param=$data;
		}else{
			$param = array();
		}
		echo json_encode($param);
   }

    public function search($tgl,$id='',$dtl='') {
		$tahun = date("Y",strtotime($tgl));
		$bulan = date("n",strtotime($tgl));
        $data = $this->Aset_model->SearchBudget($tahun,$bulan,$id);
		$param=array();
		if($data!==false){
			if($dtl==''){
				$bulan=date("n",strtotime($tgl));
				$budget=$data->budget;
				$qty=$data->qty;
				$budgetpr=$data->budgetpr;
				$budgetpo=$data->budgetpo;
				$deskripsi=$data->nama_aset;
				$sisa=($data->budget-$data->terpakai);
				$param = array(
						'budget' => $budget,
						'sisa'=>$sisa,
						'qty'=>$qty,
						'deskripsi'=>$deskripsi,
						'budgetpr'=>$budgetpr,
						'budgetpo'=>$budgetpo,
						);
			}else{
				$param=$data;
			}
		}else{
			$param = array(
					'budget' =>0,
					'sisa'=>0,
					'qty'=>0,
					'deskripsi'=>'',
					'budgetpr'=>0,
					'budgetpo'=>0,
					);
		}
		echo json_encode($param);
   }
   
    public function approval() {
        $this->auth->restrict($this->viewPermission);
        $data = $this->Aset_model->GetAset();
        $this->template->set('results', $data);
        $this->template->title('Rencana Pembelian Aset');
        $this->template->render('list_approval');
    }
	
	public function proses_approve($id) {
        $this->auth->restrict($this->managePermission);
        $data  = $this->Aset_model->find_by(array('id' => $id));
        if(!$data) {
            $this->template->set_message("Invalid Aset", 'error');
            redirect('Aset');
        }
        
		$datcoa     = $this->Acc_model->GetCoaCombo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$penyusutan = $this->Acc_model->GetCoaComboCategory('PENYUSUTAN','',$name='coa');
		$datcostcenter  = $this->Acc_model->GetCostcenterCombo();
		
		$this->template->set('datcostcenter',$datcostcenter);
		$this->template->set('penyusutan',$penyusutan);
		$this->template->set('datdivisi',$datdivisi);
        $this->template->set('datcoa',$datcoa);
        $this->template->set('data',$data);
        $this->template->title('Approve Aset');
        $this->template->render('aset_form_approve');
    }
	
	public function reject_approve(){
        $type       = $this->input->post("type");
        $id         = $this->input->post("id");
		$coa    	= $this->input->post("coa");
        $nama_aset  = $this->input->post("nama_aset");
        $divisi		= $this->input->post("divisi");
        $qty		= $this->input->post("qty");
		$budget		= $this->input->post("budget");
		$budgetpr		= $this->input->post("budgetpr");
		$budgetpo		= $this->input->post("budgetpo");
        $tahun      = $this->input->post('tahun');
        $bulan      = $this->input->post('bulan');
		$alasan      = $this->input->post('alasan');
        
		
            $this->auth->restrict($this->managePermission);
            if($id!="")
            {
                $data = array(
                            array(
                                'id'=>$id,
								'coa'=>$coa,
                                'nama_aset'=>$nama_aset,
								'divisi'=>$divisi,
                                'budget'=>$budget,
                                'tahun'=>$tahun,
                                'bulan'=>$bulan,
								'budgetpr'=>$budgetpr,
								'budgetpo'=>$budgetpo,
								'qty'=>$qty,
								'status_appr'=>2,
								'alasan'=>$alasan,
                            )
                        );
                $result = $this->Aset_model->update_batch($data,'id');
                $keterangan     = "SUKSES, Edit data Aset ".$id.", atas Nama : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();

            } else {
                $result = FALSE;
                $keterangan     = "GAGAL, Edit data Aset ".$id.", atas ID : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
       
           
        
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
    }
	
	public function save_approve(){
        $type       = $this->input->post("type");
        $id         = $this->input->post("id");
		$coa    	= $this->input->post("coa");
        $nama_aset  = $this->input->post("nama_aset");
        $divisi		= $this->input->post("divisi");
        $qty		= $this->input->post("qty");
		$budget		= $this->input->post("budget");
		$budgetpr		= $this->input->post("budgetpr");
		$budgetpo		= $this->input->post("budgetpo");
        $tahun      = $this->input->post('tahun');
        $bulan      = $this->input->post('bulan');
		$alasan      = $this->input->post('alasan');
		$deskripsi   = $this->input->post('description');
        
		
            $this->auth->restrict($this->managePermission);
            if($id!="")
            {
                $data = array(
                            array(
                                'id'=>$id,
								'coa'=>$coa,
                                'nama_aset'=>$nama_aset,
								'divisi'=>$divisi,
                                'budget'=>$budget,
                                'tahun'=>$tahun,
                                'bulan'=>$bulan,
								'budgetpr'=>$budgetpr,
								'budgetpo'=>$budgetpo,
								'qty'=>$qty,
								'status_appr'=>1,
								'alasan'=>$alasan,
								'deskripsi'=>$deskripsi,
                            )
                        );
                $result = $this->Aset_model->update_batch($data,'id');
                $keterangan     = "SUKSES, Edit data Aset ".$id.", atas Nama : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();

            } else {
                $result = FALSE;
                $keterangan     = "GAGAL, Edit data Aset ".$id.", atas ID : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
       
           
        
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
    }
	
	function get_aset_divisi()
    {
        $divisi = $this->input->post('divisi');
		$tgl    = $this->input->post('tgl');
		$tahun = date("Y",strtotime($tgl));
		$bulan = date("n",strtotime($tgl));
		// print_r($divisi);
		// exit;
		//$divisi=$_GET['divisi'];
        $data=$this->Aset_model->get_aset_divisi($divisi,$tahun);
		
        // print_r($data);
        // exit();
        echo "<select id='id_aset' name='id_aset' class='form-control input-sm select2'>";
        echo "<option value=''>--Pilih Aset--</option>";
                foreach ($data as $key => $st) :
				      echo "<option value='$st->id' set_select('id_aset', $st->id, isset($data->id) && $data->id == $st->id)>$st->coa | $st->nama_aset
                    </option>";
                endforeach;
        echo "</select>";
    }

}
?>