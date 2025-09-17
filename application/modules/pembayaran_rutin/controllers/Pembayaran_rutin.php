<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Pembayaran Rutin
 */

$status=array();
$waktu=array();
class Pembayaran_rutin extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Pembayaran_Periodik.View';
    protected $addPermission  	= 'Pembayaran_Periodik.Add';
    protected $managePermission = 'Pembayaran_Periodik.Manage';
    protected $deletePermission = 'Pembayaran_Periodik.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('All/All_model','Pembayaran_rutin/Pembayaran_rutin_model','Budget_coa/Budget_coa_model'));
        $this->template->title('Master Pembayaran Rutin');
        $this->template->page_icon('fa fa-cubes');
        date_default_timezone_set('Asia/Bangkok');
		$this->waktu=array("bulan"=>"bulan","tahun"=>"tahun");
    }

    public function index() {
//        $this->auth->restrict($this->viewPermission);
        $data = $this->Pembayaran_rutin_model->GetPengajuanRutin();
		$status=array("0"=>"Baru","1"=>"Disetujui","2"=>"Selesai","3"=>"Menunggu Pembayaran","4"=>"Realisasi");
        $datdept  = $this->All_model->GetDeptCombo();
        $data_detail = $this->Pembayaran_rutin_model->GetDataPengajuanRutinAll();
		$this->template->set('datdept',$datdept);
        $this->template->set('status', $status);
        $this->template->set('results', $data);
        $this->template->set('data_detail', $data_detail);
        $this->template->title('Pembayaran Rutin');
        $this->template->render('list');
    }

    public function edit($id) {
        $data	= $this->Pembayaran_rutin_model->GetDataPengajuanRutin($id);
        if(!$data) {
            $this->template->set_message("Invalid Data", 'error');
            redirect('pembayaran_rutin');
        }
		$datacoapayment=$this->All_model->get_coa_payment('bayarrutin',$data->no_doc);
        $datdept  = $this->All_model->GetDeptCombo($data->departement);
		$data_detail=$this->Pembayaran_rutin_model->GetDataPengajuanRutinDetail($data->no_doc);
        $datcoa	= $this->Budget_coa_model->GetCoa();
        $this->template->set('datacoapayment',$datacoapayment);
        $this->template->set('datcoa',$datcoa);
		$this->template->set('type','edit');
        $this->template->set('datdept',$datdept);
        $this->template->set('data',$data);
        $this->template->set('data_detail',$data_detail);
        $this->template->title('Edit Pembayaran Rutin');
        $this->template->render('input_form');
    }

    public function view($id) {
        $data	= $this->Pembayaran_rutin_model->GetDataPengajuanRutin($id);
        if(!$data) {
            $this->template->set_message("Invalid Data", 'error');
            redirect('pembayaran_rutin');
        }
		$datacoapayment=$this->All_model->get_coa_payment('bayarrutin',$data->no_doc);
        $datdept  = $this->All_model->GetDeptCombo($data->departement);
		$data_detail=$this->Pembayaran_rutin_model->GetDataPengajuanRutinDetail($data->no_doc);
        $datcoa	= $this->Budget_coa_model->GetCoa();
        $this->template->set('datacoapayment',$datacoapayment);
        $this->template->set('datcoa',$datcoa);
		$this->template->set('type','view');
        $this->template->set('datdept',$datdept);
        $this->template->set('data',$data);
        $this->template->set('data_detail',$data_detail);
        $this->template->title('View Pembayaran Rutin');
        $this->template->render('input_form');
    }

    public function get_data() {
		$allbudget		= $this->input->post("allbudget");
        $dept       	= $this->input->post("dept");
        $tanggal           = $this->input->post("tanggal");
		$data=$this->Pembayaran_rutin_model->GetDataBudgetRutin($dept,$tanggal,$allbudget);
		$param = array(
				'save' =>1,
				'data'=>$data,
				);
		echo json_encode($param);
	}

    public function save_data(){
        $departement	= $this->input->post("departement");
        $id				= $this->input->post("id");
		$no_doc			= $this->input->post("no_doc");
		$tanggal_doc	= $this->input->post("tanggal_doc");
        $nilai_total	= $this->input->post("nilai_total");
        $coa_bank		= $this->input->post("coa_bank");
        $coa_ppn		= $this->input->post("coa_ppn");
        $nilai_ppn		= $this->input->post("nilai_ppn");

		$detail_id		= $this->input->post("detail_id");
		$id_budget		= $this->input->post("id_budget");
        $coa       		= $this->input->post("coa");
        $nama           = $this->input->post("nama");
		$tanggal		= $this->input->post("tanggal");
		$tipe  			= 'rutin';
        $nilai_bayar	= $this->input->post("nilai_bayar");
        $keterangan		= $this->input->post("keterangan");
        $ppn			= $this->input->post("ppn");

        $modul			= $this->input->post("modul");
        $detail_id_coa	= $this->input->post("detail_id_coa");
        $detail_coa		= $this->input->post("detail_coa");
        $kredit			= $this->input->post("kredit");
        $debit			= $this->input->post("debit");
        $keterangancoa	= $this->input->post("keterangancoa");


			$this->db->trans_begin();
			$dataheader =  array(
				array(
						'id'=>$id,
						'coa_bank'=>$coa_bank,
						'nilai_total'=>$nilai_total,
						'coa_ppn'=>$coa_ppn,
						'nilai_ppn'=>$nilai_ppn,
						'status'=>'2',
					)
				);
			$this->Pembayaran_rutin_model->update_batch($dataheader,'id');

			for ($x = 0; $x < count($detail_id); $x++) {
					$data = array(
						'nilai_bayar'=>$nilai_bayar[$x],
						'ppn'=>$ppn[$x],
					);
				
				$this->All_model->dataUpdate('tr_pengajuan_rutin_detail',$data,array('id'=>$detail_id[$x]));
			}

			for ($x = 0; $x < count($detail_id_coa); $x++) {
				$datadtlcoa = array(
							'modul'=>$modul,
							'no_doc'=>$no_doc,
							'keterangan'=>$keterangancoa[$x],
							'coa'=>$detail_coa[$x],
							'kredit'=>$kredit[$x],
							'debit'=>$debit[$x],
					);
				$this->All_model->dataSave('tr_coa_payment',$datadtlcoa);
			}

            if($this->db->trans_status()) {
                $keterangan     = "SUKSES, tambah data ";
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = $x;
                $sql            = $this->db->last_query();
                $result         = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data ";
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = $x;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
			$this->db->trans_complete();
			$param = array(
					'save' => $result
					);
			echo json_encode($param);
    }

}
