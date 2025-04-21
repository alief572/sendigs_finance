<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author harboens
 * @copyright Copyright (c) 2019, Harboens
 *
 * This is controller for PO
 */

$datppn=array();
$datcombodata=array();
$dattipe_pr=array();
class Po_aset extends Admin_Controller {

    protected $viewPermission   = "PR_Asset.View";
    protected $addPermission    = "PR_Asset.Add";
    protected $managePermission = "PR_Asset.Manage";
    protected $deletePermission = "PR_Asset.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Po_aset/Po_aset_model','jurnal_nomor/Acc_model','Jurnal_nomor/Jurnal_model'));
        $this->template->title('Manage Data PR Aset');
        $this->template->page_icon('fa fa-table');
		$this->datppn=array('0'=>'Non PPN','10'=>'PPN');
        date_default_timezone_set("Asia/Bangkok");
		$this->datcombodata=array('No'=>'No','Asli'=>'Asli','Copy'=>'Copy');
		$this->dattipe_pr=array(''=>'Pilih','PO'=>'PO','PP'=>'PP');
    }

    public function index() {
        $this->auth->restrict($this->viewPermission);
        $data = $this->Po_aset_model->GetPrAset();
        $this->template->set('results', $data);
        $this->template->title('PR Aset');
        $this->template->render('list');
    }

    public function list_approve_pr() {
        $this->auth->restrict($this->viewPermission);
        $data = $this->Po_aset_model->GetPrAset('1');
        $this->template->set('results', $data);
        $this->template->title('Approval PR Aset');
        $this->template->render('list_approve_pr');
    }

    public function create() {
        $this->auth->restrict($this->addPermission);
        $dataaset = $this->Po_aset_model->aset_combo(date("Y"),date("n"));
		$datvendor	= $this->Acc_model->vendor_combo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$tipe_bayar=$this->Acc_model->tipe_bayar();
		$this->template->set('tipe_bayar',$tipe_bayar);
        $this->template->set('datppn',$this->datppn);
        $this->template->set('datdivisi',$datdivisi);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
		$this->template->title('Input PR Aset');
        $this->template->render('pr_form');
    }

	public function approve_pr($id) {
        $this->auth->restrict($this->managePermission);
        $data  = $this->Po_aset_model->find_by(array('id' => $id));
        if(!$data) {
            $this->template->set_message("Invalid PR", 'error');
            redirect('po_aset/list');
			die();
        }
        $datauser = $this->Acc_model->GetInfoUser($data->created_by);
		$data->username=$datauser->nm_lengkap;
		$tahun=date("Y",strtotime($data->tgl_pr));
		$bulan=date("n",strtotime($data->tgl_pr));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
		$datvendor	= $this->Acc_model->vendor_combo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$tipe_bayar=$this->Acc_model->tipe_bayar();
		$this->template->set('tipe_bayar',$tipe_bayar);
        $this->template->set('datppn',$this->datppn);
        $this->template->set('datdivisi',$datdivisi);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
        $this->template->set('data',$data);
        $this->template->title('Approval PR Aset');
        $this->template->render('pr_form_approval');
	}

	public function reject_approval_pr(){
		$no_pr  	= $this->input->post("no_pr");
		$reject_reason  	= $this->input->post("reject_reason");
        $id         = $this->input->post("id");
		$result = FALSE;
		if($no_pr != "") {
			$data = array(
						array(
							'id'=>$id,
							'reject_reason'=>$reject_reason,
							'status'=>'10',
						)
					);
			$result = $this->Po_aset_model->update_batch($data,'id');
			$keterangan     = "SUKSES, Reject PR Nonstok ".$id.", atas id : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$keterangan     = "GAGAL, Reject data PR Nonstok ".$id.", atas ID : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
		die();

	}

	public function edit($id) {
        $this->auth->restrict($this->managePermission);
        $data  = $this->Po_aset_model->find_by(array('id' => $id));
        if(!$data) {
            $this->template->set_message("Invalid PR", 'error');
            redirect('po_aset/list');
			die();
        }
        $datauser = $this->Acc_model->GetInfoUser($data->created_by);
		$data->username=$datauser->nm_lengkap;
		$tahun=date("Y",strtotime($data->tgl_pr));
		$bulan=date("n",strtotime($data->tgl_pr));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
		$datvendor	= $this->Acc_model->vendor_combo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$tipe_bayar=$this->Acc_model->tipe_bayar();
		$this->template->set('tipe_bayar',$tipe_bayar);
        $this->template->set('datppn',$this->datppn);
        $this->template->set('datdivisi',$datdivisi);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
        $this->template->set('data',$data);
		$this->template->title('View PR Aset');
        $this->template->render('pr_form');
    }

	public function save_approval_pr(){
        $type           = $this->input->post("type");
        $id             = $this->input->post("id");
        $no_pr          = $this->input->post("no_pr");
		$tgl_pr  		= $this->input->post("tgl_pr");
        $id_aset       	= $this->input->post("id_aset");
        $divisi			= $this->input->post("divisi");
        $budget			= $this->input->post("budget");
        $budget_sisa    = $this->input->post('budget_sisa');
        $description    = $this->input->post('description');
        $nilai_pr       = $this->input->post('nilai_pr');
        $tipe_pr       	= $this->input->post('tipe_pr');

        $nilai_ppn		= $this->input->post('nilai_ppn');
        $ppn	       	= $this->input->post('ppn');

		$result=false;
        if($type=="approve") {
			if($id!="")
            {
                $data = array(
                            array(
								'id'=>$id,
								'modified_by'=> $this->auth->user_id(),
								'modified_on'=>date("Y-m-d h:i:s"),
                                'status'=>'0',
                            )
                        );
                $result = $this->Po_aset_model->update_batch($data,'id');
                $keterangan     = "SUKSES, Approval data PR Aset ".$id.", atas id : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();

				$data =  array(
							'no_pr'=>$no_pr,
							'tgl_pr'=>$tgl_pr,
							'nilai_pr'=>$nilai_pr,
						);
				if($tipe_pr=='KASBON') $this->create_kasbon($data);
				if($tipe_pr=='PP') $this->create_pp($data);
				if($tipe_pr=='PO') $this->create_po($data);
            } else {
                $result = FALSE;
                $keterangan     = "GAGAL, Approval data PR Nonstok ".$id.", atas ID : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
	}

    public function save_data(){
        $type           = $this->input->post("type");
        $id             = $this->input->post("id");
		$tgl_pr  		= $this->input->post("tgl_pr");
        $id_aset       	= $this->input->post("id_aset");
        $divisi			= $this->input->post("divisi");
		$qty			= $this->input->post("qty");
        $budget			= $this->input->post("budget");
        $budget_sisa    = $this->input->post('budget_sisa');
        $description    = $this->input->post('description');
        $nilai_pr       = $this->input->post('nilai_pr');
        $tipe_pr       	= $this->input->post('tipe_pr');
		$tgl_dibutuhkan	= $this->input->post('tgl_dibutuhkan');

        $nilai_ppn		= $this->input->post('nilai_ppn');
        $ppn	       	= $this->input->post('ppn');
		$budgetpr       	= $this->input->post('budgetpr') - $nilai_pr;
		$budgetpo       	= $this->input->post('budgetpo');

		$vendor_po1		= $this->input->post("vendor_po1");
		$vendor_po2		= $this->input->post("vendor_po2");
		$supplier_text	= $this->input->post("vendor_kasbon");
		$nilai_pengajuan= $nilai_pr;
		$notes			= $description;
		
        $quality_inspect         = $this->input->post("quality_inspect");
        $qty_inspect             = $this->input->post("qty_inspect");
        $note_release            = $this->input->post("note_release");
        $tipe_bayar            = $this->input->post("tipe_bayar");

		if($type=="edit") {

            if($id!="")
            {
                $data = array(
                            array(
								'id'=>$id,
								'tgl_pr'=>$tgl_pr,
								'id_aset'=>$id_aset,
								'tipe_pr'=>$tipe_pr,
								'budget'=>($budget),
								'budget_sisa'=>($budget_sisa),
								'description'=>$description,
								'nilai_pr'=>$nilai_pr,
								'tipe_pr'=>$tipe_pr,
								'modified_by'=> $this->auth->user_id(),
								'modified_on'=>date("Y-m-d h:i:s"),
								'ppn'=>$ppn,
								'nilai_ppn'=>$nilai_ppn,
								'supplier_text'=>$supplier_text,
								'notes'=>$notes,
								'nilai_pengajuan'=>$nilai_pengajuan,
								'status'=>'1',
								'alt_supplier_1'=>$vendor_po1,
								'alt_supplier_2'=>$vendor_po2,
								'notes'=>$notes,
								'reject_reason'=>'',
								'quality_inspect'=>$quality_inspect,
								'qty_inspect'=>$qty_inspect,
								'qty'=>$qty,
								'note_release'=>$note_release,
								'tipe_bayar'=>$tipe_bayar,
								'divisi'=>$divisi,
								'tgl_dibutuhkan'=>$tgl_dibutuhkan,
							)
                        );
				$data_old  = $this->Po_aset_model->find_by(array('id' => $id));
                $result = $this->Po_aset_model->update_batch($data,'id');
                $keterangan     = "SUKSES, Edit data PR Aset ".$id.", atas id : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();
				if($data_old) {
					$this->Po_aset_model->Update_budget($data_old->id_aset,($data_old->nilai_pr*-1));
				}
				$this->Po_aset_model->Update_budget($id_aset,$nilai_pr);
            } else {
                $result = FALSE;
                $keterangan     = "GAGAL, Edit data PR Aset ".$id.", atas ID : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();
            }
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {

			$datapr = $this->identitas_model->GentableSelect('no_pr_aset');
			$nopr=explode(';',$datapr[0]->info);
			$nomorpr='';
			if($nopr[0]==date("Y")){
				$nomorpr='PRA-'.$nopr[0].'-'.sprintf('%04d', $nopr[1]);
				$updnopr=$nopr[0].';'.($nopr[1]+1);
			}else{
				$nomorpr='PRA-'.($nopr[0]+1).'-'.sprintf('%04d', 1);
				$updnopr=date("Y").';2';
			}
            $data =  array(
						'no_pr'=>$nomorpr,
						'tgl_pr'=>$tgl_pr,
						'id_aset'=>$id_aset,
						'tipe_pr'=>$tipe_pr,
						'budget'=>($budget),
						'budget_sisa'=>($budget_sisa),
						'description'=>$description,
						'nilai_pr'=>$nilai_pr,
						'tipe_pr'=>$tipe_pr,
						'created_by'=> $this->auth->user_id(),
						'created_on'=>date("Y-m-d h:i:s"),
						'ppn'=>$ppn,
						'nilai_ppn'=>$nilai_ppn,
						'supplier_text'=>$supplier_text,
						'notes'=>$notes,
						'nilai_pengajuan'=>$nilai_pengajuan,
						'status'=>'1',
						'alt_supplier_1'=>$vendor_po1,
						'alt_supplier_2'=>$vendor_po2,
						'notes'=>$notes,
						'reject_reason'=>'',
						'quality_inspect'=>$quality_inspect,
						'qty_inspect'=>$qty_inspect,
						'qty'=>$qty,
						'note_release'=>$note_release,
						'tipe_bayar'=>$tipe_bayar,
						'budgetpr'=>$budgetpr,
						'budgetpo'=>$budgetpo,
						'divisi'=>$divisi,
						'tgl_dibutuhkan'=>$tgl_dibutuhkan,
					);
            $id = $this->Po_aset_model->insert($data);
			$this->Po_aset_model->Update_budget_aset($id_aset,$budgetpr);
            if(is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data PR Aset ".$id.", atas ID : ".$id;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result         = TRUE;
				$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnopr),array('tipe'=>'no_pr_aset'));
				$this->Po_aset_model->Update_budget($id_aset,$nilai_pr);
//				if($tipe_pr=='KASBON') $this->create_kasbon($data);
//				if($tipe_pr=='PP') $this->create_pp($data);
//				if($tipe_pr=='PO') $this->create_po($data);
            } else {
                $keterangan     = "GAGAL, tambah data PR Aset ".$id.", atas ID : ".$id;
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

    public function list_pp_aset() {

        $data = $this->Po_aset_model->GetPpAset();
        $this->template->set('results', $data);
        $this->template->title('List PP Aset');
        $this->template->render('list_pp_aset');
    }

	function list_approve_payment_po_ap(){
        $data = $this->Po_aset_model->GetPoPaymentAsetList(array('status'=>2,'ap_cek'=>0));
        $this->template->set('results', $data);
        $this->template->title('Approve Payment PO Aset AP');
        $this->template->render('list_approve_po_payment_ap');
	}

	function create_po($data){
			$datanomor = $this->identitas_model->GentableSelect('no_po_aset');
			$nodoc=explode(';',$datanomor[0]->info);
			$nomordoc='';
			if($nodoc[0]==date("Y")){
				$nomordoc='POA-'.$nodoc[0].'-'.sprintf('%04d', $nodoc[1]);
				$updnodoc=$nodoc[0].';'.($nodoc[1]+1);
			}else{
				$nomordoc='POA-'.($nodoc[0]+1).'-'.sprintf('%04d', 1);
				$updnodoc=date("Y").';2';
			}
			$ppn		= $this->input->post("ppn");
			$nilai_ppn		= $this->input->post("nilai_ppn");
			$vendor_po1		= $this->input->post("vendor_po1");
			$vendor_po2		= $this->input->post("vendor_po2");
			$vendor_po3		= $this->input->post("vendor_po3");
            $datainsert =  array(
						'no_po'=>$nomordoc,
						'no_pr'=>$data['no_pr'],
						'tgl_po'=>$data['tgl_pr'],
						'vendor1_id'=>$vendor_po1,
						'vendor2_id'=>$vendor_po2,
						'vendortext_id'=>$vendor_po3,
						'created_by'=> $this->auth->user_id(),
						'created_on'=>date("Y-m-d h:i:s"),
						'ppn'=>$ppn,
						'nilai_ppn'=>$nilai_ppn,
					);
            $id = $this->identitas_model->DataSave('tr_po_aset',$datainsert);
			$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnodoc),array('tipe'=>'no_po_aset'));
	}

	function create_pp($data){
			$datanomor = $this->identitas_model->GentableSelect('no_pp_aset');
			$nodoc=explode(';',$datanomor[0]->info);
			$nomordoc='';
			if($nodoc[0]==date("Y")){
				$nomordoc='PPAR-'.$nodoc[0].'-'.sprintf('%04d', $nodoc[1]);
				$updnodoc=$nodoc[0].';'.($nodoc[1]+1);
			}else{
				$nomordoc='PPAR-'.($nodoc[0]+1).'-'.sprintf('%04d', 1);
				$updnodoc=date("Y").';2';
			}
			$vendor_pp		= $this->input->post("vendor_pp");
			$nilai_pp		= $this->input->post("nilai_pp");
			$note_pp		= $this->input->post("note_pp");
			$ppn			= $this->input->post("ppn");
			$nilai_ppn		= ($ppn*$nilai_pp/100);
			$quality_inspect= $this->input->post("quality_inspect");
			$qty_inspect   	= $this->input->post('qty_inspect');
			$note_release	= $this->input->post('note_release');
			$tipe_bayar	= $this->input->post('tipe_bayar');
			$approve=$this->identitas_model->get_maxapproval($nilai_pp,'PP');
			
			$coa   = $this->input->post("id_aset");
			$asset  = $this->db->query("SELECT * FROM ms_coa_aset WHERE id='$coa'")->row();
			$coa_aset = $asset->coa;
		
            $datainsert =  array(
						'no_pp'=>$nomordoc,
						'no_pr'=>$data['no_pr'],
						'tgl_pp'=>$data['tgl_pr'],
						'vendor_id'=>$vendor_pp,
						'notes'=>$note_pp,
						'request_payment'=>$nilai_pp,
						'approve'=>$approve,
						'status'=>'1',
						'created_by'=> $this->auth->user_id(),
						'created_on'=>date("Y-m-d h:i:s"),
						'ppn'=>$ppn,
						'nilai_ppn'=>$nilai_ppn,
						'quality_inspect'=>$quality_inspect,
						'qty_inspect'=>$qty_inspect,
						'note_release'=>$note_release,
						'tipe_bayar'=>$tipe_bayar,
						'sisa_nilai_pp'=>$nilai_pp,
						'requestplusppn'=>$nilai_pp+$nilai_ppn,
						'requestnonppn'=>$nilai_pp,
					);
            $this->identitas_model->DataSave('tr_pp_aset',$datainsert);
			$this->identitas_model->DataUpdate('tr_pr_aset',array('terbayar'=>$nilai_pp),array('no_pr'=>$data['no_pr']));
			$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnodoc),array('tipe'=>'no_pp_aset'));
			
			$tgl_pp = $data['tgl_pr'];

			if ($qty_inspect > 0){
				$kodejurnal1	 ='JV024';
				$Keterangan_INV1		    = 'TERIMA BARANG PP ASET U/ '.$nomordoc.' TGL PP. '.$tgl_pp;

				#AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

				$datajurnal1  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal1);

						foreach($datajurnal1 AS $rec){

						$tabel1  = $rec->menu;
						$posisi1 = $rec->posisi;
						$field1  = $rec->field;
						$param1  = 'no_pp';
						$value_param1  = $nomordoc;
						$val1 = $this->Acc_model->GetData($tabel1,$field1,$param1,$value_param1);
						$nilaibayar1 = $val1[0]->$field1;

                        if ($field1 == 'request_payment'){ //full
						$nokir1 =$coa_aset;
						}
						else {
						$nokir1  = $rec->no_perkiraan;
						}



						if ($posisi1=='D'){
						$det_Jurnaltes1[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_pp,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir1,
      					  'keterangan'    => $Keterangan_INV1,
      					  'no_reff'       => $nomordoc,
						  'debet'         => $nilaibayar1,
						  'kredit'        => 0,
						  'jenis_jurnal'  => 'penerimaan'
					     );
						}
						elseif ($posisi1=='K'){
						$det_Jurnaltes1[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_pp,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir1,
      					  'keterangan'    => $Keterangan_INV1,
      					  'no_reff'       => $nomordoc,
						  'debet'         => 0,
						  'kredit'        => $nilaibayar1,
						  'jenis_jurnal'  => 'penerimaan'
					     );
						}

						}

						$this->db->insert_batch('jurnal',$det_Jurnaltes1);
						
						$status_tr	 = "UPDATE tr_pp_aset SET sts_trm=1 WHERE no_pp  = '$nomordoc' ";
		                $this->db->query($status_tr);

		#END JURNAL TEMPORARY
		}
	}

// tidak dipakai kasbon
	function create_kasbon($data){
			$datanomor = $this->identitas_model->GentableSelect('no_kasbon_aset');
			$nodoc=explode(';',$datanomor[0]->info);
			$nomordoc='';
			if($nodoc[0]==date("Y")){
				$nomordoc='CAA-'.$nodoc[0].'-'.sprintf('%04d', $nodoc[1]);
				$updnodoc=$nodoc[0].';'.($nodoc[1]+1);
			}else{
				$nomordoc='CAA-'.($nodoc[0]+1).'-'.sprintf('%04d', 1);
				$updnodoc=date("Y").';2';
			}
			$vendor_kasbon		= $this->input->post("vendor_kasbon");
			$approve=$this->identitas_model->get_maxapproval($data['nilai_pr'],'KASBON');

            $datainsert =  array(
						'no_kasbon'=>$nomordoc,
						'no_pr'=>$data['no_pr'],
						'tgl_kasbon'=>$data['tgl_pr'],
						'nilai_kasbon'=>$data['nilai_pr'],
						'approve'=>$approve,
						'status'=>'1',
						'created_by'=> $this->auth->user_id(),
						'created_on'=>date("Y-m-d h:i:s"),
					);
            $id = $this->identitas_model->DataSave('tr_pr_aset_kasbon',$datainsert);
			$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnodoc),array('tipe'=>'no_kasbon_aset'));
	}

    public function list_approve_kasbon() {
        $data = $this->Po_aset_model->GetKasbonAset(array(1));
        $this->template->set('results', $data);
        $this->template->title('Approve Cash Advance Aset');
        $this->template->render('list_approve_kasbon');
    }

    public function edit_kasbon($id) {
        $data  = $this->Po_aset_model->EditKasbon($id);
        if(!$data) {
            $this->template->set_message("Invalid Cash Advance", 'error');
            redirect('po_aset/list_approve_kasbon');
        }
		$tahun=date("Y",strtotime($data->tgl_kasbon));
		$bulan=date("n",strtotime($data->tgl_kasbon));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $data->no_pr));

        $this->template->set('data',$data);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
        $this->template->set('datapr',$datapr);
        $this->template->title('Edit Cash Advance Aset');
        $this->template->render('kasbon_form');
    }

	public function save_data_kasbon(){
        $id             = $this->input->post("id");
		$tgl_kasbon  	= $this->input->post("tgl_kasbon");
        $nilai_kasbon  	= $this->input->post('nilai_kasbon');
        $type		  	= $this->input->post('type');
        $no_dok         = $this->input->post("no_kasbon");
		$edit_t=$this->input->post('edit_t');
		$edit_status=0;
		$olddata  = $this->Po_aset_model->EditKasbon($id);
		if($olddata->edit_status=='0') {
			if($edit_t=='1') $edit_status=1;
		}else{
			$edit_status=1;
		}
		$datasave=array(
			'tgl_kasbon'=>$tgl_kasbon,
			'nilai_kasbon'=>$nilai_kasbon,
			'modified_by'=> $this->auth->user_id(),
			'modified_on'=>date("Y-m-d h:i:s"),
			'edit_status'=>$edit_status,
		);
		$this->db->trans_start();
			$this->identitas_model->DataUpdate('tr_pr_aset_kasbon',$datasave,array('id'=>$id));
			if($type=='approve') {
				$this->save_approval($id,$no_dok,'KASBON','tr_pr_aset_kasbon');
				$edit_t = $this->input->post('edit_t');
				if($edit_t==1){
					$content='
					nilai_kasbon1:'.$olddata->nilai_kasbon.',
					nilai_kasbon2:'.$nilai_kasbon.'';
					$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'KASBONASETPAYMENT','content'=>$content));
				}
			}
		$this->db->trans_complete();
        $param = array(
                'save' => $this->db->trans_status(),
                );
        echo json_encode($param);
	}

    public function list_payment_kasbon() {

        $data = $this->Po_aset_model->GetKasbonAset(array(2,3,5));
        $this->template->set('results', $data);
        $this->template->title('Pembayaran Cash Advance Aset');
        $this->template->render('list_payment_kasbon');
    }

	function payment_kasbon($id){
        $data  = $this->Po_aset_model->EditKasbon($id);
        if(!$data) {
            $this->template->set_message("Invalid Cash Advance", 'error');
            redirect('po_aset/list_payment_kasbon');
        }
		$tahun=date("Y",strtotime($data->tgl_kasbon));
		$bulan=date("n",strtotime($data->tgl_kasbon));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $data->no_pr));
		$datsumber	=$this->identitas_model->GentableCombo('sumber_dana');
        $this->template->set('datsumber',$datsumber);
        $this->template->set('data',$data);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
        $this->template->set('datapr',$datapr);
        $this->template->title('Payment Cash Advance Aset');
        $this->template->render('kasbon_payment_form');
	}

	function save_payment_kasbon(){
        $status         = $this->input->post("status");
        $id             = $this->input->post("id");
		if($status==2){
			$tgl_cair  		= $this->input->post("tgl_cair");
			$sumber_dana    = $this->input->post("sumber_dana");
			$setstatus=3;
			$data = array(
					'status'=>$setstatus, 'sumber_dana'=>$sumber_dana, 'tgl_cair'=>$tgl_cair,
					'modified_by'=> $this->auth->user_id(),
					'modified_on'=>date("Y-m-d h:i:s"),
				);
		}
		if($status==3){
			$tgl_expense	= $this->input->post("tgl_expense");
			$nilai_aktual	= $this->input->post("nilai_aktual");
			$setstatus=5;
			$data = array(
					'status'=>$setstatus, 'nilai_aktual'=>$nilai_aktual, 'tgl_expense'=>$tgl_expense,
					'modified_by'=> $this->auth->user_id(),
					'modified_on'=>date("Y-m-d h:i:s"),
				);
		}
		$this->db->trans_start();
			$this->identitas_model->DataUpdate('tr_pr_aset_kasbon',$data,array('id'=>$id));
			if($status==3){
				$id_aset = $this->input->post("id_aset");
				$tgl_pr = $this->input->post("tgl_pr");
				$nilai_pr = $this->input->post("nilai_pr");
				$this->Po_aset_model->Update_budget($id,$nilai_aktual,$nilai_pr);
			}
		$this->db->trans_complete();
        $param = array('save' => $this->db->trans_status());
        echo json_encode($param);
	}

	function print_pu($id){
		ob_start();
        $datas  = $this->Po_aset_model->EditKasbon($id);
        if(!$datas) {
            $this->template->set_message("Invalid Cash Advance", 'error');
            redirect('po_aset/list_payment_kasbon');
        }
        $data['infopr'] = $this->Po_aset_model->find_by(array('no_pr' => $datas->no_pr));
		$data['kasbon']=$datas;
		$this->load->view('print_pu',$data);
		$html = ob_get_contents();
		ob_end_clean();
		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$pdf = new HTML2PDF('L','A5','en');
		$pdf->WriteHTML($html);
		$pdf->Output();
	}

    public function list_po() {
        $data = $this->Po_aset_model->GetPoAset(array(0));
        $this->template->set('results', $data);
        $this->template->title('Buat PO Aset');
        $this->template->render('list_po');
    }

	function save_approval($id,$no_dok,$tipe,$table,$data_change=''){
		$datauser=$this->identitas_model->DataGetOne('users','id_user='.$this->auth->user_id());
		$datatrans=$this->identitas_model->DataGetOne($table,'id='.$id);
		$app=$datauser->id_jabatan;
		if($datatrans->approve == $app){
			$status='2';
		}else{
			$status='1';
		}
		$data = array(
				'status'=>$status, 'approve_now'=>$app,
				'modified_by'=> $this->auth->user_id(),
				'modified_on'=>date("Y-m-d h:i:s"),
			);
		if ($status=='2'){
//			if($table=='tr_po_aset_request_payment') $data['ap_cek']=1;
		}

		$this->identitas_model->DataUpdate($table,$data,array('id'=>$id));
		$dataapp=array(
			'tipe'=>$tipe, 'no_dokumen'=>$no_dok, 'approve'=>$app, 'data_change'=>$data_change,
			'created_by'=> $this->auth->user_id(),
			'created_on'=>date("Y-m-d h:i:s"),
		);
		$this->identitas_model->DataSave('tr_aset_approval',$dataapp);
	}

    public function edit_po($id) {
        $data  = $this->Po_aset_model->EditPo($id);
        if(!$data) {
            $this->template->set_message("Invalid PO", 'error');
            redirect('po_aset/list_po');
        }
		$tahun=date("Y",strtotime($data->tgl_po));
		$bulan=date("n",strtotime($data->tgl_po));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $data->no_pr));
        $datauser = $this->Acc_model->GetInfoUser($datapr->created_by);
		$datapr->username=$datauser->nm_lengkap;

        $datauser = $this->Acc_model->GetInfoUser($datapr->created_by);
		$datapr->username=$datauser->nm_lengkap;
		$datsumber	=$this->identitas_model->GentableCombo('sumber_dana');
		$datvendor	= $this->Acc_model->vendor_combo();
		$this->template->set('datppn',$this->datppn);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('datsumber',$datsumber);
        $this->template->set('data',$data);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
        $this->template->set('datapr',$datapr);
        $this->template->title('Edit PO Aset');
        $this->template->render('po_form');
    }

	public function save_data_po(){
        $id             = $this->input->post("id");
		$tgl_po  		= $this->input->post("tgl_po");
        $vendor1_id		= $this->input->post("vendor1_id");
        $vendor2_id		= $this->input->post("vendor2_id");
        $vendortext_id	= $this->input->post("vendortext_id");
        $vendor_id     	= $this->input->post('vendor_id');
        $vendor_reason	= $this->input->post('vendor_reason');
        $info_desc     	= $this->input->post('info_desc');
        $notes     		= $this->input->post('notes');
        $qty	       	= $this->input->post('qty');
        $harga_satuan  	= $this->input->post('harga_satuan');
        $ppn    	   	= $this->input->post('ppn');
        $nilai_ppn     	= $this->input->post('nilai_ppn');
        $total_nilai_po	= $this->input->post('total_nilai_po');
        $status    	   	= $this->input->post('status');
        $edit_status 	= $this->input->post('edit_status');
		if($edit_status==0){
			$edit_t = $this->input->post('edit_t');
			if($edit_t==1) $edit_status=1;
		}

		$datasave=array(
			'status'=>$status,
			'tgl_po'=>$tgl_po,
			'harga_total'=>($qty*$harga_satuan),
			'vendor1_id'=>$vendor1_id,
			'vendor2_id'=>$vendor2_id,
			'vendortext_id'=>$vendortext_id,
			'vendor_id'=>($vendor_id),
			'vendor_reason'=>($vendor_reason),
			'info_desc'=>$info_desc,
			'notes'=>$notes,
			'qty'=>$qty,
			'harga_satuan'=>$harga_satuan,
			'ppn'=>$ppn,
			'edit_status'=>$edit_status,
			'nilai_ppn'=>$nilai_ppn,
			'total_nilai_po'=>$total_nilai_po,
			'modified_by'=> $this->auth->user_id(),
			'modified_on'=>date("Y-m-d h:i:s"),
		);
		$this->db->trans_start();
			if($status==0){
				$approve=$this->identitas_model->get_maxapproval($total_nilai_po,'PO');
				$status=1;
				$datasave['approve']=$approve;
				$datasave['status']=$status;
				$this->identitas_model->DataUpdate('tr_po_aset',$datasave,array('id'=>$id));
			}else{
				$olddata = $this->Po_aset_model->EditPo($id);
				$this->identitas_model->DataUpdate('tr_po_aset',$datasave,array('id'=>$id));
				$no_dok = $this->input->post('no_po');
				$this->save_approval($id,$no_dok,'PO','tr_po_aset');
				if($edit_t==1){
					$content='
					note1:'.$olddata->notes.',
					note2:'.$notes.',
					qty1:'.$olddata->qty.',
					qty2:'.$qty.',
					ppn1:'.$olddata->ppn.',
					ppn2:'.$ppn.',
					nilai_ppn1:'.$olddata->nilai_ppn.',
					nilai_ppn2:'.$nilai_ppn.',
					total_nilai_po1:'.$total_nilai_po.',
					total_nilai_po2:'.$olddata->total_nilai_po.'';
					$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'POASET','content'=>$content));
				}
			}
		$this->db->trans_complete();
        $param = array(
                'save' => $this->db->trans_status(),
                );
        echo json_encode($param);
	}

    public function list_approve_po() {

        $data = $this->Po_aset_model->GetPoAset(array(1));
        $this->template->set('results', $data);
        $this->template->title('Approval PO Aset');
        $this->template->render('list_po');
    }

    public function list_po_release() {
        $data = $this->Po_aset_model->GetPoAset(array(2));
        $this->template->set('results', $data);
        $this->template->title('PO Aset');
        $this->template->render('list_po_release');
    }

    public function release_po($id) {
        $data  = $this->Po_aset_model->EditPo($id);
        if(!$data) {
            $this->template->set_message("Invalid PO", 'error');
            redirect('po_aset/list_po_release');
        }
		$datarpo = $this->identitas_model->DataGetOne('tr_po_aset_request_payment',array('no_po'=>$data->no_po,'status'=>'10'));
		if($datarpo){
			$this->template->set('datarpo',$datarpo);
		}
		$tahun=date("Y",strtotime($data->tgl_po));
		$bulan=date("n",strtotime($data->tgl_po));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $data->no_pr));
        $datauser = $this->Acc_model->GetInfoUser($datapr->created_by);
		$datapr->username=$datauser->nm_lengkap;
		$datvendor	= $this->Acc_model->vendor_combo();
		$tipe_bayar=$this->Acc_model->tipe_bayar();
		$this->template->set('tipe_bayar',$tipe_bayar);
		$this->template->set('datppn',$this->datppn);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('datapr',$datapr);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('data',$data);
        $this->template->title('Pemerikasaan Barang / Jasa');
        $this->template->render('po_periksa_form');
    }

	function reject_approval_po_payment(){
		$no_po  		= $this->input->post("no_pr");
		$reject_reason	= $this->input->post("reject_reason");
        $id				= $this->input->post("id");
		$result = FALSE;
		if($no_po != "") {
			$data = array(
						'reject_reason'=>$reject_reason,
						'status'=>'10',
						'approve_now'=>'0',
					);
			$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$data,array('id'=>$id));
			$this->identitas_model->DataUpdate('tr_po_aset',array('status'=>2),array('no_po'=>$no_po));
			$result = true;
			$keterangan     = "SUKSES, Reject Payment PO Aset ".$id.", atas id : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$keterangan     = "GAGAL, Reject Payment PO Aset ".$id.", atas ID : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
		die();
	}

	public function save_release_po(){
		// print_r($this->input->post());
		// exit;

		$db2 = $this->load->database('accounting', TRUE);
        $id             = $this->input->post("id");
		$tgl_periksa  	= $this->input->post("tgl_periksa");
        $request_payment= $this->input->post("request_payment");
        $request_note	= $this->input->post("request_note");
        $quality_inspect= $this->input->post("quality_inspect");
        $qty_inspect   	= $this->input->post('qty_inspect');
        $note_release	= $this->input->post('note_release');
        $status    	   	= $this->input->post('status');
        $no_po    	   	= $this->input->post('no_po');
        $no_pr    	   	= $this->input->post('no_pr');
        $vendor_id    	= $this->input->post('vendor_id');
        $terbayar    	= $this->input->post('terbayar');
		$harga_total 	= $this->input->post('harga_total');
		$nilai_bayar 	= $this->input->post('nilai_bayar');
		$dpp	= $this->input->post("dpp");
		$pph	= $this->input->post("pph");
		$ppn	= $this->input->post("ppn");
		$bank	= $this->input->post("bank");
		$nilai_ppn   = $this->input->post("nilai_ppn");
		
		$tipe_bayar  	= $this->input->post("tipe_bayar");

		$c_inv   = $this->input->post("c_inv");
		$t_inv   = $this->input->post("t_inv");
		$t_inv_tgl   = $this->input->post("t_inv_tgl");
		$c_kontrak   = $this->input->post("c_kontrak");
		$c_surat_jalan   = $this->input->post("c_surat_jalan");
		$t_kontrak   = $this->input->post("t_kontrak");
		$t_surat_jalan   = $this->input->post("t_surat_jalan");
		$c_faktur   = $this->input->post("c_faktur");
		$t_faktur   = $this->input->post("t_faktur");
		$t_faktur_tgl   = $this->input->post("t_faktur_tgl");

		$total_nilai_po	= $this->input->post('total_nilai_po');
		
		if($nilai_ppn > 0) {
		$ppn_request = 10*$request_payment/100;
        $requestplusppn = $request_payment+$ppn_request;
		}
		else{
		$ppn_request = 0;
        $requestplusppn = $request_payment;
		}
		
        $coa   = $this->input->post("id_aset");
		$asset  = $this->db->query("SELECT * FROM ms_coa_aset WHERE id='$coa'")->row();
		$coa_aset = $asset->coa;
		
		$this->db->trans_start();
			if($status==2){
				$datanomor = $this->identitas_model->GentableSelect('no_po_pp_aset');
				$nodoc=explode(';',$datanomor[0]->info);
				$nomordoc='';
				if($nodoc[0]==date("Y")){
					$nomordoc='POPPA-'.$nodoc[0].'-'.sprintf('%04d', $nodoc[1]);
					$updnodoc=$nodoc[0].';'.($nodoc[1]+1);
				}else{
					$nomordoc='POPPA-'.($nodoc[0]+1).'-'.sprintf('%04d', 1);
					$updnodoc=date("Y").';2';
				}
				$datasave=array(
					'tgl_periksa'=>$tgl_periksa,
					'request_payment'=>$request_payment,
					'request_note'=>$request_note,
					'quality_inspect'=>$quality_inspect,
					'qty_inspect'=>($qty_inspect),
					'note_release'=>($note_release),
					'no_po'=>$no_po,
					'no_pr'=>$no_pr,
					'vendor_id'=>$vendor_id,
					'created_by'=> $this->auth->user_id(),
					'created_on'=>date("Y-m-d h:i:s"),
					'tipe_bayar'=>$tipe_bayar,
					'ppn_request'=>$ppn_request,
					'requestplusppn'=>$requestplusppn,
					'ppn'=>$ppn,
					'nilai_ppn'=>$nilai_ppn,
				);

				$approve=$this->identitas_model->get_maxapproval($request_payment,'PO');
				if($harga_total<=($request_payment+$terbayar)) $status=3;
				$datasave['no_request']=$nomordoc;
				$datasave['approve']=$approve;

// hanya terima baran saja
				$hanya_terima_barang='';
				if(($qty_inspect!='' || $quality_inspect!='' ) && ($request_payment==0 || $request_payment=='')) {
					$hanya_terima_barang='ok';
					$datasave['status']=5;
					$datasave['ap_cek']=5;
					
				}else{
					$datasave['status']=1;
				}

				$this->identitas_model->DataSave('tr_po_aset_request_payment',$datasave);
				$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnodoc),array('tipe'=>'no_po_pp_aset'));
				//$this->identitas_model->DataUpdate('tr_po_aset',array('terbayar'=>($request_payment+$terbayar),'status'=>$status),array('id'=>$id));
			    
				$po = $this->Po_aset_model->GetDataNoPo($no_po);
			    $jurnal = $po[0]->jurnal;
				$tgl_po   = $po[0]->tgl_po;
				$nilai_po = $po[0]->harga_total;
				$nilai_ppn = $po[0]->nilai_ppn;
				$po_total = $po[0]->total_nilai_po;
				$vendor_id = $po[0]->vendor_id;
				$terbayar1 = $po[0]->terbayar;


				//SYAM UPDATE nilai_sisa_po dan terima_barang KARENA ADA JURNAL PENERIMAAN
				$datapotosave1=array('nilai_sisa_po'=>($nilai_po-$terbayar1));

				$this->identitas_model->DataUpdate('tr_po_aset',$datapotosave1,array('no_po'=>$no_po));
				
				
				 if ($qty_inspect > 0){
				$kodejurnal1	 ='JV017';
				$Keterangan_INV1		    = 'TERIMA ASET U/ '.$no_po.' TGL PO. '.$tgl_po;

				#AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

				$datajurnal1  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal1);

						foreach($datajurnal1 AS $rec){

						$tabel1  = $rec->menu;
						$posisi1 = $rec->posisi;
						$field1  = $rec->field;
						$param1  = 'no_po';
						$value_param1  = $no_po;
						$val1 = $this->Acc_model->GetData($tabel1,$field1,$param1,$value_param1);
						$nilaibayar1 = $val1[0]->$field1;

                        if ($field1 == 'harga_total'){ //full
						$nokir1 =$coa_aset;
						}
						else {
						$nokir1  = $rec->no_perkiraan;
						}



						if ($posisi1=='D'){
						$det_Jurnaltes1[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir1,
      					  'keterangan'    => $Keterangan_INV1,
      					  'no_reff'       => $no_po,
						  'debet'         => $nilaibayar1,
						  'kredit'        => 0,
						  'jenis_jurnal'  => 'penerimaan'
					     );
						}
						elseif ($posisi1=='K'){
						$det_Jurnaltes1[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir1,
      					  'keterangan'    => $Keterangan_INV1,
      					  'no_reff'       => $no_po,
						  'debet'         => 0,
						  'kredit'        => $nilaibayar1,
						  'jenis_jurnal'  => 'penerimaan'
					     );
						}

						}

						$this->db->insert_batch('jurnal',$det_Jurnaltes1);
						
						$status_tr	 = "UPDATE tr_po_aset_request_payment SET sts_trm=1 WHERE no_request  = '$nomordoc' ";
		                $this->db->query($status_tr);
						
						
						//SYAM UPDATE nilai_sisa_po dan terima_barang KARENA ADA JURNAL PENERIMAAN
				$dataterima=array('terima_barang'=>1);

				$this->identitas_model->DataUpdate('tr_po_aset',$dataterima,array('no_po'=>$no_po));

		#END JURNAL TEMPORARY
		}

			
			
			
			
			
			}else{


				$olddata  = $this->Po_aset_model->EditPoPayment($id);

//agus update
				if($status==1){
					$no_dok = $this->input->post('no_request');
					$datasave=array(
						'reject_reason'=>'',
						'modified_by'=> $this->auth->user_id(),
						'modified_on'=>date("Y-m-d h:i:s"),
					);

					$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$datasave,array('id'=>$id));
					$this->save_approval($id,$no_dok,'POPPA','tr_po_aset_request_payment');
				}


				if($olddata){
					if($olddata->status=='10'){
						$datasave=array(
							'tgl_periksa'=>$tgl_periksa,
							'request_payment'=>$request_payment,
							'request_note'=>$request_note,
							'quality_inspect'=>$quality_inspect,
							'qty_inspect'=>($qty_inspect),
							'note_release'=>($note_release),
							'status'=>1,
							'vendor_id'=>$vendor_id,
							'reject_reason'=>'',
							'modified_by'=> $this->auth->user_id(),
							'modified_on'=>date("Y-m-d h:i:s"),
							'tipe_bayar'=>$tipe_bayar,
							'ppn'=>$ppn,
							'nilai_ppn'=>$nilai_ppn,
						);
						$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$datasave,array('id'=>$id));
					}else{
					  if($olddata->status=='2'){

			// ini saat approval ap selesai
					// update tabel PO : terima barang , status , terbayar
					         //SYAM HAPUS UPDATE TERBAYAR KARENA HARUSNYA KE UPDATE SETELAH SAVE PEMBAYARAN KARENA BARU REQUEST
							$datapotosave=array('nilai_sisa_po'=>($total_nilai_po-$terbayar));
							if($qty_inspect!='' || $quality_inspect!='' ) $datapotosave['terima_barang']="1";
							if($harga_total<=($request_payment+$terbayar)) $datapotosave['status']=3;
							$this->identitas_model->DataUpdate('tr_po_aset',$datapotosave,array('no_po'=>$no_po));
					// update tabel request payment po : status ap cek
							$data = array(
									'ap_cek'=>'1',
									'modified_by'=> $this->auth->user_id(),
									'modified_on'=>date("Y-m-d h:i:s"),
									'c_surat_jalan'=>$c_surat_jalan, 'c_kontrak'=>$c_kontrak,'t_inv_tgl'=>$t_inv_tgl, 't_inv'=>$t_inv,'c_inv'=>$c_inv,'c_faktur'=>$c_faktur,'t_kontrak'=>$t_kontrak,'t_surat_jalan'=>$t_surat_jalan,
									't_faktur'=>$t_faktur, 't_faktur_tgl'=>$t_faktur_tgl
								);
							$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$data,array('id'=>$id));
							$no_dok = $this->input->post('no_request');
							$dataapp=array(
								'tipe'=>'POAPP', 'no_dokumen'=>$no_dok, 'approve'=> 1, 'data_change'=>'AP CHECK',
								'created_by'=> $this->auth->user_id(),
								'created_on'=>date("Y-m-d h:i:s"),
							);
							$this->identitas_model->DataSave('tr_aset_approval',$dataapp);
							
							
							
							//JURNAL Approval

	            $tgl_periksa  	= $this->input->post("tgl_periksa");
				$request_payment= $this->input->post("request_payment");
				$request_note	= $this->input->post("request_note");
				$quality_inspect= $this->input->post("quality_inspect");
				$qty_inspect   	= $this->input->post('qty_inspect');
				$note_release	= $this->input->post('note_release');
				$status    	   	= $this->input->post('status');
				$no_po    	   	= $this->input->post('no_po');
				$no_pr    	   	= $this->input->post('no_pr');
				$vendor_id    	= $this->input->post('vendor_id');
				$terbayar    	= $this->input->post('terbayar');
				$nilai_ppn   	= $this->input->post('nilai_ppn');
				$harga_total 	= $this->input->post('harga_total');
				//$coa			= $this->input->post('no_coa');
				$total_nilai_po			= $this->input->post('total_nilai_po');
				$tipe_bayar			= $this->input->post('tipe_bayar');
				$no_request = $this->input->post('no_request');

				$tgl_po         = $tgl_periksa;

                $coa   = $this->input->post("id_aset");
				$asset  = $this->db->query("SELECT * FROM ms_coa_aset WHERE id='$coa'")->row();
				$coa_aset = $asset->nama_aset;

		// JURNAL REQUEST

		if ($request_payment > 0){

			if ($tipe_bayar == 0){ //full
			$kodejurnal ='JV018';
			}
			elseif ($tipe_bayar == 1){//dp
			$kodejurnal ='JV016';
			}
			elseif ($tipe_bayar == 2){//pelunasan
			$kodejurnal ='JV018';
			}


		$Keterangan_INV		    = 'PEMBELIAN ASET U/ '.$coa_aset.','.$request_note.' No PO. '.$no_po.' TGL PO. '.$tgl_po;


		#AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

		$datajurnal  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal);

					foreach($datajurnal AS $record){
						$nokir  = $record->no_perkiraan;
						$tabel  = $record->menu;
						$posisi = $record->posisi;
						$field  = $record->field;
						$param  = 'no_request';
						$value_param  = $no_request;
						$val = $this->Acc_model->GetData($tabel,$field,$param,$value_param);
						$nilaibayar = $val[0]->$field;




						if ($posisi=='D'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_po,
						  'debet'         => $nilaibayar,
						  'kredit'        => 0,
						  'jenis_jurnal'  => 'approval',
						  'no_request'    => $no_request
					     );
						}
						elseif ($posisi=='K'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_po,
						  'debet'         => 0,
						  'kredit'        => $nilaibayar,
						  'jenis_jurnal'  => 'approval',
						  'no_request'    => $no_request
					     );
						}

					}		

						$this->db->insert_batch('jurnal',$det_Jurnaltes);
						
						$status_tr	 = "UPDATE tr_po_aset_request_payment SET sts_apr=1 WHERE no_request  = '$no_request' ";
		                $this->db->query($status_tr);

		#END JURNAL TEMPORARY
		         
		}
		
		
		

						}else{
							$no_dok = $this->input->post('no_request');
							$this->save_approval($id,$no_dok,'POAPP','tr_po_aset_request_payment');
						}

					}

				}
			}
		$this->db->trans_complete();
        $param = array(
                'save' => $this->db->trans_status(),
                );

        echo json_encode($param);
/*


				$edit_t=$this->input->post('edit_t');
				$edit_status=0;
				$olddata  = $this->Po_aset_model->EditPoPayment($id);
				if($olddata->edit_status=='0') {
					if($edit_t=='1') $edit_status=1;
				}else{
					$edit_status=1;
				}
				$datasave=array(
					'tgl_periksa'=>$tgl_periksa,
					'request_payment'=>$request_payment,
					'request_note'=>$request_note,
					'quality_inspect'=>$quality_inspect,
					'qty_inspect'=>($qty_inspect),
					'note_release'=>($note_release),
					'vendor_id'=>$vendor_id,
					'modified_by'=> $this->auth->user_id(),
					'modified_on'=>date("Y-m-d h:i:s"),
					'edit_status'=>$edit_status,
					'reject_reason'=>'',
					'tipe_bayar'=>$tipe_bayar,
				);
				$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$datasave,array('id'=>$id));
				if($status=='10'){
					if($harga_total<=($request_payment+$terbayar)) {
						$status=3;
//						$oldreq=$olddata->request_payment;
						$this->identitas_model->DataUpdate('tr_po_aset',array('status'=>$status),array('id'=>$id));
					}
					$this->identitas_model->DataUpdate('tr_po_aset_request_payment',array('status'=>'1'),array('id'=>$id));
				}else{
					$no_dok = $this->input->post('no_request');
					$this->save_approval($id,$no_dok,'POPP','tr_po_aset_request_payment');
					if($edit_t==1){
						$content='
						request_note1:'.$olddata->request_note.',
						request_note2:'.$request_note.',
						request_payment1:'.$olddata->request_payment.',
						request_payment2:'.$request_payment.'';
						$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'POASETPAYMENT','content'=>$content));
					}
				}

				$po = $this->Po_aset_model->GetDataNoPo($no_po);
			    $jurnal = $po[0]->jurnal;
				$tgl_po   = $po[0]->tgl_po;
				$nilai_po = $po[0]->harga_total;
				$nilai_ppn = $po[0]->nilai_ppn;
				$po_total = $po[0]->total_nilai_po;
				$vendor_id = $po[0]->vendor_id;

/*
			    if ($jurnal =='0'){

				$Tgl_Inv	    = $tgl_periksa;
					$Bln 			= substr($Tgl_Inv,5,2);
		            $Thn 			= substr($Tgl_Inv,0,4);




			        ## ACCOUNT RECEIVABLE ##
			            $Bulan_Invoice  = date('n',strtotime($Tgl_Inv));
          				$Tahun_Invoice  = date('Y',strtotime($Tgl_Inv));
        				$Total_Inv      = $request_payment;
        				$Bulan_Sekarang = date('n');
        				$Tahun_Sekarang = date('Y');
        				$Beda_Bulan     = (($Tahun_Sekarang - $Tahun_Invoice) * 12) + ($Bulan_Sekarang - $Bulan_Invoice);
        				if($Beda_Bulan < 1){
        					$Beda_Bulan   = 0;
        				}
        				$dataAR					= array();
        				$intL				   	= 0;
        				$Saldo_Awal			    = 0;
        				$Kredit					= 0;
        				$Debet					= $Total_Inv;
        				$Saldo_Akhir		    = $Total_Inv;
        				for($x=0;$x<=$Beda_Bulan;$x++){
        					$intL++;
        					$Bulan_Proses		= date('n',mktime(0,0,0,$Bulan_Invoice + $x,1,$Tahun_Invoice));
        					$Tahun_Proses		= date('Y',mktime(0,0,0,$Bulan_Invoice + $x,1,$Tahun_Invoice));
        					if($intL > 1){
        						$Debet			= 0;
        						$Saldo_Awal		= $Total_Inv;
        					}
        					$dataAR[$x] 	= array(
        						'no_invoice' 		=> $no_po,
        						'tgl_invoice'		=> $Tgl_Inv,
								'no_po'		        => '-',
								'tgl_terima_invoice'=> $Tgl_Inv,
								'tgl_jatuh_tempo'   => '-',
        						'id_klien'	        => $vendor_id,
        						'nama_klien' 		=> '-',
        						'bln'			    => $Bulan_Proses,
        						'thn'				=> $Tahun_Invoice,
        						'saldo_awal' 		=> $Saldo_Awal, //nilai invoice
        						'debet'				=> $Debet,
        						'kredit'			=> $Kredit,
        						'saldo_akhir'		=> $Saldo_Akhir, //nilai invoice
        						'created_on'		=> date('Y-m-d H:i:s')
          					);
        				}

		//PROSES JURNAL

						$po = $this->Po_aset_model->GetDataNoCoa($coa);
						// print_r($po);
						// exit;
						$NoCoa = $po[0]->coa;

						// if ($NoCoa1=='1302-01-01'){ //Bangunan
						// $NoCoa	= '1105-02-01';
						// }
						// else if ($NoCoa1=='1302-02-01'){ //Kendaraan
						// $NoCoa	= '1105-02-02';
						// }
						// else if ($NoCoa1=='1302-03-01'){ //Konstruksi
						// $NoCoa	= '1105-02-03';
						// }
						// else if ($NoCoa1=='1302-04-01'){ //Peralatan
						// $NoCoa	= '1105-02-04';
						// }
						// else if ($NoCoa1=='1302-05-01'){ //Tanah
						// $NoCoa	= '1105-02-05';
						// }

                        $nilai_po       =	$request_payment;
						$tgl_po			= $tgl_periksa;
		                $Bln 			= substr($tgl_po,5,2);
		                $Thn 			= substr($tgl_po,0,4);
        				## NOMOR JV ##
        				$Nomor_JV		= $this->Jurnal_model->get_Nomor_Jurnal_Sales('101',$tgl_po);

						$no_po          = $no_pr;

						//print_r($Nomor_JV);
						//exit;


        				$Keterangan_INV		    = 'PEMBELIAN ASSET U/ '.$no_po.' TGL PO. '.$tgl_po;

        				$dataJVhead = array(
          					'nomor' 	    	=> $Nomor_JV,
          					'tgl'	         	=> $tgl_po,
          					'jml'	            => $po_total,
          					'koreksi_no'		=> '-',
          					'kdcab'				=> '101',
          					'jenis'			    => 'JV',
          					'keterangan' 		=> $Keterangan_INV,
        					'bulan'				=> $Bln,
          					'tahun'				=> $Thn,
          					'user_id'			=> $this->auth->user_id(),
          					'memo'			    => '',
          					'tgl_jvkoreksi'	    => $tgl_po,
          					'ho_valid'			=> ''
          				);

        				$det_Jurnal				= array();
        				$det_Jurnal[]			= array(
        					  'nomor'         => $Nomor_JV,
        					  'tanggal'       => $tgl_po,
        					  'tipe'          => 'JV',
        					  'no_perkiraan'  => $NoCoa,
        					  'keterangan'    => $Keterangan_INV,
        					  'no_reff'       => $no_po,
        					  'debet'         => $nilai_po,
        					  'kredit'        => 0

        				);

						if($nilai_ppn!=0)
						{

        				$det_Jurnal[]			= array(
        					  'nomor'         => $Nomor_JV,
        					  'tanggal'       => $tgl_po,
        					  'tipe'          => 'JV',
        					  'no_perkiraan'  => '2105-01-01',
        					  'keterangan'    => $Keterangan_INV,
        					  'no_reff'       => $no_po,
        					  'debet'         => $nilai_ppn,
        					  'kredit'        => 0

        				);
						}

        				$det_Jurnal[]			  = array(
      					  'nomor'         => $Nomor_JV,
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => '2108-01-01',
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_po,
      					  'debet'         => 0,
      					  'kredit'        => $po_total
      				    );



        				## INSERT JURNAL ##
        				$db2->insert('javh',$dataJVhead);
        				$db2->insert_batch('jurnal',$det_Jurnal);


        				## INSERT ACCOUNT RECEIVABLE  ##
        				//$db2->insert_batch('ap',$dataAR);

        				$Qry_Update_Cabang_acc	 = "UPDATE pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        				$db2->query($Qry_Update_Cabang_acc);

						 $Qry_Update_po_header	 = "UPDATE tr_po_aset SET jurnal=1 WHERE no_po='$no_po'";
        				$this->db->query($Qry_Update_po_header);

    					//PROSES JURNAL
			}

			}


		$this->db->trans_complete();
        $param = array(
                'save' => $this->db->trans_status(),
                );
        echo json_encode($param);
*/

	}

	public function list_approve_payment_po(){
        $data = $this->Po_aset_model->GetPoPaymentAset(array(1));
        $this->template->set('results', $data);
        $this->template->title('Approve Payment PO Aset');
        $this->template->render('list_approve_po_payment');
	}

	public function approve_payment_po_report(){
        $get_data = $this->Po_aset_model->GetPoPaymentAset(array(1));
		$data['results'] = $get_data;
		$this->load->library(array('Mpdf'));
		$mpdf=new mPDF('','','','','','','','','','');
		$mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('approval_po_report',$data);
		$this->mpdf->AddPage('L','A4','en');
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output();


	}

	public function approve_payment_po($id) {
        $datarpo  = $this->Po_aset_model->EditPoPayment($id);
        if(!$datarpo) {
            $this->template->set_message("Invalid PO", 'error');
            redirect('po_aset/list_approve_payment_po');
        }
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $datarpo->no_pr));
        $datauser = $this->Acc_model->GetInfoUser($datapr->created_by);
		$datapr->username=$datauser->nm_lengkap;
		$datvendor	= $this->Acc_model->vendor_combo();
        $data     = $this->Po_aset_model->InfoPo($datarpo->no_po);
		$tahun=date("Y",strtotime($data->tgl_po));
		$bulan=date("n",strtotime($data->tgl_po));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
		$tipe_bayar=$this->Acc_model->tipe_bayar();
		$this->template->set('datcombodata',$this->datcombodata);
		$this->template->set('tipe_bayar',$tipe_bayar);
        $this->template->set('datarpo',$datarpo);
		$this->template->set('datppn',$this->datppn);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('datapr',$datapr);
        $this->template->set('data',$data);
        $this->template->set('dataaset',$dataaset);
        $this->template->title('Approve Payment PO');
        $this->template->render('po_periksa_form');
    }

	public function po_payment($id){
        $datarpo  = $this->Po_aset_model->EditPoPayment($id);
        if(!$datarpo) {
            $this->template->set_message("Invalid PO", 'error');
            redirect('po_aset/list_approve_payment_po');
        }
		$checkpayment	= $this->identitas_model->DataGetOne('tr_po_aset_payment', array('status'=>'10', 'no_po'=>$datarpo->no_po));
		if($checkpayment) {
			$this->po_payment_finance($checkpayment->id);
		}else{
			$datvendor	= $this->Acc_model->vendor_combo();
			$data     	= $this->Po_aset_model->InfoPo($datarpo->no_po);
			$datbayar	= $this->identitas_model->GentableCombo('reff_bayar');
	//		$datbank  	 = $this->Acc_model->combo_bank();
			$datbank			= $this->Jurnal_model->get_Coa_Bank_Cabang('101');
			$tahun=date("Y",strtotime($data->tgl_po));
			$bulan=date("n",strtotime($data->tgl_po));
			$dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
			$datapr  = $this->Po_aset_model->find_by(array('no_pr' => $datarpo->no_pr));
			$datauser = $this->Acc_model->GetInfoUser($datapr->created_by);
			$datapr->username=$datauser->nm_lengkap;
			$datvendor	= $this->Acc_model->vendor_combo();
			$pphpembelian  	 = $this->Acc_model->combo_pph_pembelian();

			$datcoa     = $this->Acc_model->GetCoaCombo();
			$this->template->set('datcoa',$datcoa);
			$this->template->set('pphpembelian', $pphpembelian);
			$this->template->set('datbank',$datbank);
			$this->template->set('datbayar',$datbayar);
			$this->template->set('datarpo',$datarpo);
			$this->template->set('datppn',$this->datppn);
			$this->template->set('datvendor',$datvendor);
			$this->template->set('datapr',$datapr);
			$this->template->set('dataaset',$dataaset);
			$this->template->set('data',$data);
			$this->template->set('datcombodata',$this->datcombodata);
			$this->template->title('Pembayaran PO');
			$this->template->render('po_payment_form');
		}
	}

	public function list_payment_po(){

        $data = $this->Po_aset_model->GetPoPaymentAset(array(2),false,array('ap_cek'=>1));
        $this->template->set('results', $data);
        $this->template->title('Pembayaran PO Aset');
        $this->template->render('list_payment_po');
	}

	function save_payment_po(){
		$db2 = $this->load->database('accounting', TRUE);
		$jenis_pph	= $this->input->post("jenis_pph");
		$potongan   = $this->input->post("potongan");
		$potongan_note   = $this->input->post("potongan_note");
		$id = $this->input->post("id");
		$tgl_terima_invoice = $this->input->post("tgl_terima_invoice");
		$dpp	= $this->input->post("dpp");
		$pph	= $this->input->post("pph");
		$ppn	= $this->input->post("ppn");
		$bank	= $this->input->post("bank");
		$nilai_ppn   = $this->input->post("nilai_ppn");
		// print_r($nilai_ppn);
		// exit;
		$t_biaya_lain   = $this->input->post("t_biaya_lain");
		$biaya_lain_note   = $this->input->post("biaya_lain_note");
		$nilai_bayar   = $this->input->post("nilai_bayar");
		$top   = $this->input->post("top");
		$jenis_pembayaran   = $this->input->post("jenis_pembayaran");
		$notes   = $this->input->post("notes");
		$bank_id   = $this->input->post("bank_id");
		$nama_rekening   = $this->input->post("nama_rekening");
		$nomor_rekening   = $this->input->post("nomor_rekening");
		$giro_atas_nama   = $this->input->post("giro_atas_nama");
		$no_voucher   = $this->input->post("no_voucher");
		$tgl_voucher   = $this->input->post("tgl_voucher");
		$no_po   = $this->input->post("no_po");
		$c_inv   = $this->input->post("c_inv");
		$t_inv   = $this->input->post("t_inv");
		$t_inv_tgl   = $this->input->post("t_inv_tgl");
		$c_kontrak   = $this->input->post("c_kontrak");
		$c_surat_jalan   = $this->input->post("c_surat_jalan");
		$c_faktur   = $this->input->post("c_faktur");
		$t_faktur   = $this->input->post("t_faktur");
		$t_faktur_tgl   = $this->input->post("t_faktur_tgl");
		$request_payment   = $this->input->post("request_payment");
		$terbayar   = $this->input->post("terbayar");
		$no_pp   = $this->input->post("no_pp");
		$no_request   = $this->input->post("no_request");
		$vendor_id   = $this->input->post("vendor_id");
		$vendor      = $this->input->post("vendor_id");
		$coa         = $this->input->post("id_aset");
		$desc   = $this->input->post("description");
		
		$dppppn      =$dpp+$nilai_ppn;
		$totalminpphpot =$nilai_bayar+$t_biaya_lain+$nilai_ppn-$pph-$potongan;

		if($request_payment<=($dpp+$terbayar)){
			$status_poreq=5;
		}else{
			$status_poreq=2;
		}
			$datapr = $this->identitas_model->GentableSelect('no_po_aset_payment');
			$nopr=explode(';',$datapr[0]->info);
			$nomorpr='';
			if($nopr[0]==date("Y")){
				$nomorpr='POPYA-'.$nopr[0].'-'.sprintf('%04d', $nopr[1]);
				$updnopr=$nopr[0].';'.($nopr[1]+1);
			}else{
				$nomorpr='POPYA-'.($nopr[0]+1).'-'.sprintf('%04d', 1);
				$updnopr=date("Y").';2';
			}

		$data = array(
				'no_payment'=>$nomorpr, 'jenis_pph'=>$jenis_pph,
				'no_po'=>$no_po,'jenis_pembayaran'=>$jenis_pembayaran,'top'=>$top,'giro_atas_nama'=>$giro_atas_nama,'status'=>'1',
				'bank_id'=>$bank_id,'nama_rekening'=>$nama_rekening,'nomor_rekening'=>$nomor_rekening,
				'tgl_terima_invoice'=>$tgl_terima_invoice, 'dpp'=>$dpp, 'pph'=>$pph, 'potongan'=>$potongan, 'potongan_note'=>$potongan_note,
				'nilai_ppn'=>$nilai_ppn, 't_biaya_lain'=>$t_biaya_lain,'ppn'=>$ppn, 'c_surat_jalan'=>$c_surat_jalan, 'c_kontrak'=>$c_kontrak,'t_inv_tgl'=>$t_inv_tgl, 't_inv'=>$t_inv,'c_inv'=>$c_inv,'c_faktur'=>$c_faktur,
				'nilai_bayar'=>$nilai_bayar, 'no_voucher'=>$nomorpr, 'tgl_voucher'=>$tgl_voucher,
				'created_by'=> $this->auth->user_id(), 't_faktur'=>$t_faktur, 't_faktur_tgl'=>$t_faktur_tgl,'notes'=>$notes,
				'created_on'=>date("Y-m-d h:i:s"), 'biaya_lain_note'=>$biaya_lain_note,'no_request'=>$no_request,
				'vendor_id'=>$vendor_id,'nilai_request'=>$request_payment,'bank'=>$bank,'dppplusppn'=>$dppppn,'totalminuspphpotongan'=>$totalminpphpot,
			);
		$this->identitas_model->DataSave('tr_po_aset_payment',$data);


		if(is_numeric($id)) {
			$keterangan     = "SUKSES, update pembayaran po aset";
			$status         = "1";
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result         = TRUE;
/*
			$data_po_request=array('status'=>$status_poreq,'terbayar'=>($dpp+$terbayar));
			$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$data_po_request,array('id'=>$id));
*/
			$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnopr),array('tipe'=>'no_po_aset_payment'));
		} else {
			$keterangan     = "GAGAL, update pembayaran po aset";
			$status         = 0;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result = FALSE;
		}
		//Save Log
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'save' => $result
                );
		echo json_encode($param);
	}

	public function list_payment_po_cashier(){

        $data = $this->Po_aset_model->GetPoPaymentAsetFinance(array('status'=>1));
        $this->template->set('results', $data);
        $this->template->title('Pembayaran PO Aset Kasir');
        $this->template->render('list_payment_po_finance');
	}

	public function list_payment_po_finance1(){
        $data = $this->Po_aset_model->GetPoPaymentAsetFinance(array('status'=>2));
        $this->template->set('results', $data);
        $this->template->title('Pembayaran PO Aset Finance 1');
        $this->template->render('list_payment_po_finance');
	}

	public function list_payment_po_finance2(){
        $data = $this->Po_aset_model->GetPoPaymentAsetFinance(array('status'=>3));
        $this->template->set('results', $data);
        $this->template->title('Pembayaran PO Aset Finance 2');
        $this->template->render('list_payment_po_finance');
	}

	function reject_approval_po_payment_finance() {
		$no_pr  		= $this->input->post("no_pr");
		$reject_reason	= $this->input->post("reject_reason");
        $id				= $this->input->post("id");
		$result = FALSE;
		if($no_pr != "") {
			$data = array(
						'reject_reason'=>$reject_reason,
						'status'=>'10',
					);
			$this->identitas_model->DataUpdate('tr_po_aset_payment',$data,array('id'=>$id));
			$result = true;
			$keterangan     = "SUKSES, Reject Payment PO Aset ".$id.", atas id : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$keterangan     = "GAGAL, Reject Payment PO Aset ".$id.", atas ID : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
		die();
	}

	function po_payment_finance($id){
        $datreq  = $this->Po_aset_model->EditPoPaymentAsetFinance($id);
        if(!$datreq) {
            $this->template->set_message("Invalid PO Payment", 'error');
            redirect('po_aset/list_approve_payment_po');
        }
		$datarpo	= $this->identitas_model->DataGetOne('tr_po_aset_request_payment',array('no_request'=>$datreq->no_request));
        $datcoa     = $this->Acc_model->GetCoaCombo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$datvendor	= $this->Acc_model->vendor_combo();
        $data     	= $this->Po_aset_model->InfoPo($datarpo->no_po);
        $datapr     = $this->Po_aset_model->InfoPp($datarpo->no_pr);
		$datbayar	= $this->identitas_model->GentableCombo('reff_bayar');
		$datbank			= $this->Jurnal_model->get_Coa_Bank_Cabang('101');
		$pphpembelian  	 = $this->Acc_model->combo_pph_pembelian();
		$tahun=date("Y",strtotime($datapr->tgl_pr));
		$bulan=date("n",strtotime($datapr->tgl_pr));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);

        $this->template->set('dataaset',$dataaset);
        $this->template->set('datreq', $datreq);
        $this->template->set('pphpembelian', $pphpembelian);
        $this->template->set('datbank',$datbank);
        $this->template->set('datbayar',$datbayar);
        $this->template->set('datarpo',$datarpo);
		$this->template->set('datppn',$this->datppn);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('datapr',$datapr);
        $this->template->set('datdivisi',$datdivisi);
        $this->template->set('data',$data);
        $this->template->set('datcoa',$datcoa);
        $this->template->set('datcombodata',$this->datcombodata);
        $this->template->title('Pembayaran PO');
        $this->template->render('po_payment_form_finance');
	}

	function save_payment_po_finance(){
		$no_payment   = $this->input->post("no_payment");
		$status   = $this->input->post("status");
		$potongan   = $this->input->post("potongan");
		$potongan_note   = $this->input->post("potongan_note");
		$id = $this->input->post("id");
		$tgl_terima_invoice = $this->input->post("tgl_terima_invoice");
		$dpp	= $this->input->post("dpp");
		$jenis_pph = $this->input->post("jenis_pph");
		$pph	= $this->input->post("pph");
		$ppn	= $this->input->post("ppn");
		$bank	= $this->input->post("bank");
		$nilai_ppn   = $this->input->post("nilai_ppn");
		$t_biaya_lain   = $this->input->post("t_biaya_lain");
		$biaya_lain_note   = $this->input->post("biaya_lain_note");
		$nilai_bayar   = $this->input->post("nilai_bayar");
		$top   = $this->input->post("top");
		$jenis_pembayaran   = $this->input->post("jenis_pembayaran");
		$notes   = $this->input->post("notes");
		$bank_id   = $this->input->post("bank_id");
		$nama_rekening   = $this->input->post("nama_rekening");
		$nomor_rekening   = $this->input->post("nomor_rekening");
		$giro_atas_nama   = $this->input->post("giro_atas_nama");
		$no_voucher   = $this->input->post("no_voucher");
		$tgl_voucher   = $this->input->post("tgl_voucher");
		$no_po   = $this->input->post("no_po");
		$c_inv   = $this->input->post("c_inv");
		$t_inv   = $this->input->post("t_inv");
		$t_inv_tgl   = $this->input->post("t_inv_tgl");
		$c_kontrak   = $this->input->post("c_kontrak");
		$c_surat_jalan   = $this->input->post("c_surat_jalan");
		$c_faktur   = $this->input->post("c_faktur");
		$t_faktur   = $this->input->post("t_faktur");
		$t_faktur_tgl   = $this->input->post("t_faktur_tgl");
		$request_payment   = $this->input->post("request_payment");
		$terbayar   = $this->input->post("terbayar");
		$no_pp   = $this->input->post("no_pp");

		$coa_lain		 = $this->input->post("coa_lain");
		$coa_potongan		 = $this->input->post("coa_potongan");

		$no_request   = $this->input->post("no_request");
		$vendor_id   = $this->input->post("vendor_id");
		$vendor		 = $vendor_id;
		
		$qty   = $this->input->post("qty");
		$harga_satuan   = $this->input->post("harga_satuan");
		$total_nilai_po  = $qty * $harga_satuan;
		
		        $coa   = $this->input->post("id_aset");
				$asset  = $this->db->query("SELECT * FROM ms_coa_aset WHERE id='$coa'")->row();
				$coa_aset = $asset->nama_aset;
		
if($status==10) $status=0;
if($status<2) {
		$data = array(
				'coa_potongan'=>$coa_potongan, 'coa_lain'=>$coa_lain,'status'=>($status+1),'jenis_pph'=>$jenis_pph,
				'no_po'=>$no_po,'jenis_pembayaran'=>$jenis_pembayaran,'top'=>$top,'giro_atas_nama'=>$giro_atas_nama,
				'bank_id'=>$bank_id,'nama_rekening'=>$nama_rekening,'nomor_rekening'=>$nomor_rekening,
				'tgl_terima_invoice'=>$tgl_terima_invoice, 'dpp'=>$dpp, 'pph'=>$pph, 'potongan'=>$potongan, 'potongan_note'=>$potongan_note,
				'nilai_ppn'=>$nilai_ppn, 't_biaya_lain'=>$t_biaya_lain,'ppn'=>$ppn, 'c_surat_jalan'=>$c_surat_jalan, 'c_kontrak'=>$c_kontrak,'t_inv_tgl'=>$t_inv_tgl, 't_inv'=>$t_inv,'c_inv'=>$c_inv,'c_faktur'=>$c_faktur,
				'nilai_bayar'=>$nilai_bayar, 'no_voucher'=>$no_voucher, 'tgl_voucher'=>$tgl_voucher,
				'modified_by'=> $this->auth->user_id(), 't_faktur'=>$t_faktur, 't_faktur_tgl'=>$t_faktur_tgl,'notes'=>$notes,
				'modified_on'=>date("Y-m-d h:i:s"), 'biaya_lain_note'=>$biaya_lain_note,'no_request'=>$no_request,
				'vendor_id'=>$vendor_id,'nilai_request'=>$request_payment,'bank'=>$bank,'reject_reason'=>'',
			);
		$this->identitas_model->DataUpdate('tr_po_aset_payment',$data,array('id'=>$id));
					$content='
					coa_potongan='.$coa_potongan.'<br>coa_lain='.$coa_lain.'<br>status='.$status.'<br>jenis_pph='.$jenis_pph.'<br>
					no_po='.$no_po.'<br>jenis_pembayaran='.$jenis_pembayaran.'<br>top='.$top.'<br>giro_atas_nama='.$giro_atas_nama.'<br>
					bank_id='.$bank_id.'<br>nama_rekening='.$nama_rekening.'<br>nomor_rekening='.$nomor_rekening.'<br>
					tgl_terima_invoice='.$tgl_terima_invoice.'<br> dpp='.$dpp.'<br> pph='.$pph.'<br> potongan='.$potongan.'<br> potongan_note='.$potongan_note.'<br>
					nilai_ppn='.$nilai_ppn.'<br> t_biaya_lain='.$t_biaya_lain.'<br>ppn='.$ppn.'<br> c_surat_jalan='.$c_surat_jalan.'<br> c_kontrak='.$c_kontrak.'<br>t_inv_tgl='.$t_inv_tgl.'<br> t_inv='.$t_inv.'<br>c_inv='.$c_inv.'<br>c_faktur='.$c_faktur.'<br>
					nilai_bayar='.$nilai_bayar.'<br> no_voucher='.$no_voucher.'<br> tgl_voucher='.$tgl_voucher.'<br>
					modified_by='. $this->auth->user_id().'<br> t_faktur='.$t_faktur.'<br> t_faktur_tgl='.$t_faktur_tgl.'<br>notes='.$notes.'<br>
					modified_on='.date("Y-m-d h:i:s").'<br> biaya_lain_note='.$biaya_lain_note.'<br>no_request='.$no_request.'<br>
					vendor_id='.$vendor_id.'<br>nilai_request='.$request_payment.'<br>bank='.$bank;
					$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'PAYMENTPOASET','content'=>$content));

 

}else{
		$data = array(
				'status'=>($status+1),
				'modified_by'=> $this->auth->user_id(),
				'modified_on'=>date("Y-m-d h:i:s"),
			);
		$this->identitas_model->DataUpdate('tr_po_aset_payment',$data,array('id'=>$id));

		$dataapp=array(
			'tipe'=>'POPYA', 'no_dokumen'=>$no_request, 'approve'=> $status, 'data_change'=>'FINANCE CHECK',
			'created_by'=> $this->auth->user_id(),
			'created_on'=>date("Y-m-d h:i:s"),
		);
		$this->identitas_model->DataSave('tr_aset_approval',$dataapp);
}
		if(is_numeric($id)) {
			$keterangan     = "SUKSES, update pembayaran po aset";
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result         = TRUE;

// update saat finance 2 approval
			if($status=='3') {
				if($request_payment<=($dpp+$terbayar)){
					$status_poreq=5;
				}else{
					$status_poreq=2;
				}

				$data_po_request=array('terbayar'=>($dpp+$terbayar),'status'=>$status_poreq);
				$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$data_po_request,array('no_request'=>$no_request));
			$sql            = $this->db->last_query();
			
			
			// jurnal setelah approval finance 2
//JURNAL PEMBAYARAN

			$kodejurnal ='BUK010';



		$Keterangan_INV		    = 'PEMBAYARAN ASET U/'.$coa_aset.','.$notes.' No. PO'.$no_po.' TGL VOUCHER. '.$tgl_voucher;


		#AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

		$datajurnal  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal);

						foreach($datajurnal AS $record){
						$nokir1  = $record->no_perkiraan;
						$tabel  = $record->menu;
						$posisi = $record->posisi;
						$field  = $record->field;

						if ($field == 't_biaya_lain'){
						$nokir  = $coa_lain;
						}
						elseif ($field == 'potongan'){
						$nokir  = $coa_potongan;
						}
						elseif ($field == 'pph'){
						$nokir  = $jenis_pph;
						}
						elseif ($field == 'nilai_bayar'){
						$nokir = $bank;
						}
						else{
						$nokir  = $record->no_perkiraan;
						}



						$param  = 'no_payment';
						$value_param  = $no_voucher;
						$val = $this->Acc_model->GetData($tabel,$field,$param,$value_param);
						$nilaibayar = $val[0]->$field;




						if ($posisi=='D'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_voucher,
      					  'tipe'          => 'BUK',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_po,
						  'debet'         => $nilaibayar,
						  'kredit'        => 0,
						  'jenis_jurnal'  => 'pembayaran',
						  'no_request'    => $no_request
					     );
						}
						elseif ($posisi=='K'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_voucher,
      					  'tipe'          => 'BUK',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_po,
						  'debet'         => 0,
						  'kredit'        => $nilaibayar,
						  'jenis_jurnal'  => 'pembayaran',
						  'no_request'    => $no_request
					     );
						}

						}

						$this->db->insert_batch('jurnal',$det_Jurnaltes);
						
						
						$status_tr	 = "UPDATE tr_po_aset_request_payment SET sts_buk=1 WHERE no_request  = '$no_request' ";
		                $this->db->query($status_tr);
						
						
						$po = $this->Po_aset_model->GetDataNoPo($no_po);
						$jurnal = $po[0]->jurnal;
						$tgl_po   = $po[0]->tgl_po;
						$nilai_po = $po[0]->harga_total;
						$nilai_ppn = $po[0]->nilai_ppn;
						$po_total = $po[0]->total_nilai_po;
						$vendor_id = $po[0]->vendor_id;
						$terbayar1 = $po[0]->terbayar;
						
						  //SYAM 18/08/2020
						$datapotosave=array('terbayar'=>($request_payment+$terbayar1),'nilai_sisa_po'=>($total_nilai_po-($terbayar1+$dpp)));
						$this->identitas_model->DataUpdate('tr_po_aset',$datapotosave,array('no_po'=>$no_po));

		#END JURNAL TEMPORARY


			

			}
//
		} else {
			$keterangan     = "GAGAL, update pembayaran po aset";
			$status         = 0;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result = FALSE;
		}
		//Save Log
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'save' => $result
                );
		echo json_encode($param);
	}


    public function list_pp() {
        $data = $this->Po_aset_model->GetPpAset();
        $this->template->set('results', $data);
        $this->template->title('Permintaan Pembelian Aset');
        $this->template->render('list_pp');
    }

    public function list_approve_pp() {
        $data = $this->Po_aset_model->GetPpAset(array(1));
        $this->template->set('results', $data);
        $this->template->title('Approve Permintaan Pembelian Aset');
        $this->template->render('list_approve_pp');
    }

	public function reject_approval_pp(){
		$no_pp  	= $this->input->post("no_pp");
		$reject_reason  	= $this->input->post("reject_reason");
        $id         = $this->input->post("id");
		$result = FALSE;
		if($no_pp != "") {
			$data = array(
						'id'=>$id,
						'reject_reason'=>$reject_reason,
						'status'=>'10',
						'approve_now'=>'0',
					);
			$this->identitas_model->DataUpdate('tr_pp_aset',$data,array('id'=>$id));
			$result = true;
			$keterangan     = "SUKSES, Reject PP ASet ".$id.", atas id : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$keterangan     = "GAGAL, Reject data PP Aset ".$id.", atas ID : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
		die();

	}

    public function approve_pp_report() {
        $get_data = $this->Po_aset_model->GetPpAset(array(1));
		$data['results'] = $get_data;
		$this->load->library(array('Mpdf'));
		$mpdf=new mPDF('','','','','','','','','','');
		$mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('approval_pp_report',$data);
		$this->mpdf->AddPage('L','A4','en');
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output();
    }

    public function list_payment_pp_cashier() {

        $data = $this->Po_aset_model->GetPpAsetPayment(array('status'=>1));
        $this->template->set('results', $data);
        $this->template->title('Pembayaran Permintaan PP Pembayaran Aset Kasir');
        $this->template->render('list_payment_pp_finance');
    }

    public function list_payment_pp_finance1() {

        $data = $this->Po_aset_model->GetPpAsetPayment(array('status'=>2));
        $this->template->set('results', $data);
        $this->template->title('Approval Pembayaran PP Aset Finance 1');
        $this->template->render('list_payment_pp_finance');
    }

    public function list_payment_pp_finance2() {

        $data = $this->Po_aset_model->GetPpAsetPayment(array('status'=>3));
        $this->template->set('results', $data);
        $this->template->title('Approval Pembayaran PP Aset Finance 2');
        $this->template->render('list_payment_pp_finance');
    }

    public function pp_payment_finance($id) {

        $data  = $this->Po_aset_model->EditPpPayment($id);
        if(!$data) {
            $this->template->set_message("Invalid Pembayaran", 'error');
            redirect('list_payment_pp');
        }
        $datcoa     = $this->Acc_model->GetCoaCombo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
		$datarq	= $this->identitas_model->DataGetOne('tr_pp_aset',array('no_pp'=>$data->no_pp));
		$pphpembelian  	 = $this->Acc_model->combo_pph_pembelian();
		$datbank	= $this->Jurnal_model->get_Coa_Bank_Cabang('101');
        $datapr     = $this->Po_aset_model->InfoPp($data->no_pr);
		$tahun=date("Y",strtotime($datapr->tgl_pr));
		$bulan=date("n",strtotime($datapr->tgl_pr));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
		$datbayar	= $this->identitas_model->GentableCombo('reff_bayar');

		$this->template->set('datbayar',$datbayar);
		$this->template->set('dataaset', $dataaset);
		$this->template->set('pphpembelian', $pphpembelian);
        $this->template->set('datarq',$datarq);
        $this->template->set('datbank',$datbank);
		$this->template->set('datppn',$this->datppn);
        $this->template->set('datapr',$datapr);
        $this->template->set('datdivisi',$datdivisi);
        $this->template->set('data',$data);
        $this->template->set('datcoa',$datcoa);
        $this->template->title('Edit Pembayaran PP Aset');
        $this->template->render('pp_payment_form_finance');
    }

	function reject_approval_pp_payment_finance() {
		$no_pr  		= $this->input->post("no_pr");
		$reject_reason	= $this->input->post("reject_reason");
        $id				= $this->input->post("id");
		$result = FALSE;
		if($no_pr != "") {
			$data = array(
						'reject_reason'=>$reject_reason,
						'status'=>'10',
					);
			$this->identitas_model->DataUpdate('tr_pp_aset_payment',$data,array('id'=>$id));
			$result = true;
			$keterangan     = "SUKSES, Reject Payment PP Nonstok ".$id.", atas id : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$keterangan     = "GAGAL, Reject Payment PP Nonstok ".$id.", atas ID : ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        $param = array(
                'save' => $result
                );
        echo json_encode($param);
		die();
	}

	function save_payment_pp_finance(){
		$db2            = $this->load->database('accounting', TRUE);
        $id             = $this->input->post("id");
		$tgl_bayar  	= $this->input->post("tgl_bayar");
		$nilai_bayar    = $this->input->post("nilai_bayar");
        $no_pr          = $this->input->post("no_pr");
        $no_pp          = $this->input->post("no_pp");
        $bank           = $this->input->post("bank");
		$potongan   = $this->input->post("potongan");
		$potongan_note   = $this->input->post("potongan_note");

        $ppn            = $this->input->post("ppn");
        $nilai_ppn          = $this->input->post("nilai_ppn");

		$t_biaya_lain   = $this->input->post("t_biaya_lain");
		$biaya_lain_note   = $this->input->post("biaya_lain_note");
		$dpp	= $this->input->post("dpp");
		$jenis_pph = $this->input->post("jenis_pph");
		$pph	= $this->input->post("pph");
		$coa_lain		 = $this->input->post("coa_lain");
		$coa_potongan		 = $this->input->post("coa_potongan");

		$no_voucher   = $this->input->post("no_voucher");
		$tgl_voucher   = $this->input->post("tgl_voucher");
		$status   = $this->input->post("status");

		$jenis_pembayaran   = $this->input->post("jenis_pembayaran");
		$notes   = $this->input->post("notes");
		$bank_id   = $this->input->post("bank_id");
		$nama_rekening   = $this->input->post("nama_rekening");
		$nomor_rekening   = $this->input->post("nomor_rekening");
		$giro_atas_nama   = $this->input->post("giro_atas_nama");
		
                $coa   = $this->input->post("id_aset");
				$asset  = $this->db->query("SELECT * FROM ms_coa_aset WHERE id='$coa'")->row();
				$coa_aset = $asset->nama_aset;		

		$this->db->trans_start();
if($status==10) $status=0;
if($status<2) {
		$data = array(
				'no_pr'=>$no_pr, 'no_pp'=>$no_pp,
				'tgl_bayar'=>$tgl_bayar, 'nilai_bayar'=>$nilai_bayar, 'bank'=>$bank, 'potongan'=>$potongan, 'potongan_note'=>$potongan_note, 'ppn'=>$ppn, 'nilai_ppn'=>$nilai_ppn,
				'created_by'=> $this->auth->user_id(),'coa_potongan'=>$coa_potongan, 'coa_lain'=>$coa_lain, 't_biaya_lain'=>$t_biaya_lain, 'no_voucher'=>$no_voucher, 'tgl_voucher'=>$tgl_voucher,
				'created_on'=>date("Y-m-d h:i:s"),'reject_reason'=>'','jenis_pph'=>$jenis_pph,'dpp'=>$dpp, 'pph'=>$pph, 'potongan'=>$potongan, 'biaya_lain_note'=>$biaya_lain_note,'status'=>($status+1),'dppplusppn'=>$nilai_ppn+$dpp,
				'jenis_pembayaran'=>$jenis_pembayaran,'giro_atas_nama'=>$giro_atas_nama,
				'bank_id'=>$bank_id,'nama_rekening'=>$nama_rekening,'nomor_rekening'=>$nomor_rekening,
			);
			$this->identitas_model->DataUpdate('tr_pp_aset_payment',$data, array('id'=>$id));
			$content=implode("<br >",$data);
			$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'PAYMENTPPASET','content'=>$content));
}else{
		$data = array(
				'status'=>($status+1),
				'modified_by'=> $this->auth->user_id(),
				'modified_on'=>date("Y-m-d h:i:s"),
			);
		$this->identitas_model->DataUpdate('tr_pp_aset_payment',$data,array('id'=>$id));

		$dataapp=array(
			'tipe'=>'PPAPY', 'no_dokumen'=>$no_pp, 'approve'=> $status, 'data_change'=>'FINANCE CHECK',
			'created_by'=> $this->auth->user_id(),
			'created_on'=>date("Y-m-d h:i:s"),
		);
		$this->identitas_model->DataSave('tr_aset_approval',$dataapp);
}
if($status=='3') {

			$terbayar    	= $this->input->post("dpp");
			$request_payment= $this->input->post("request_payment");
			if($request_payment<=($nilai_bayar+$terbayar)){
				$status=5;
			}else{
				$status=2;
			}
			$dataterbayar = ($nilai_bayar+$terbayar);
			$datapp = array(
					'terbayar'=>$dataterbayar, 'status'=>$status, 'bank'=>$bank,
					'modified_by'=> $this->auth->user_id(),
					'modified_on'=>date("Y-m-d h:i:s"),
				);
			$this->identitas_model->DataUpdate('tr_pp_aset',$datapp,array('no_pp'=>$no_pp));
			
			// update saat financial 2
//JURNAL PEMBAYARAN

		$kodejurnal ='BUK015';



		$Keterangan_INV		    = 'PEMBAYARAN ASET U/'.$coa_aset.','.$notes.'No PP'.$no_pp.' TGL VOUCHER. '.$tgl_bayar;


		#AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

		$datajurnal  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal);

						foreach($datajurnal AS $record){
						$nokir1  = $record->no_perkiraan;
						$tabel  = $record->menu;
						$posisi = $record->posisi;
						$field  = $record->field;

						if ($field == 't_biaya_lain'){
						$nokir  = $coa_lain;
						}
						elseif ($field == 'potongan'){
						$nokir  = $coa_potongan;
						}
						elseif ($field == 'pph'){
						$nokir  = $jenis_pph;
						}
						elseif ($field == 'nilai_bayar'){
						$nokir = $bank;
						}
						else{
						$nokir  = $record->no_perkiraan;
						}



						$param  = 'no_pp';
						$value_param  = $no_pp;
						$val = $this->Acc_model->GetData($tabel,$field,$param,$value_param);
						$nilaibayar = $val[0]->$field;




						if ($posisi=='D'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_bayar,
      					  'tipe'          => 'BUK',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_pp,
						  'debet'         => $nilaibayar,
						  'kredit'        => 0,
						  'jenis_jurnal'  => 'pembayaran',
						  'no_request'    => $no_pp
					     );
						}
						elseif ($posisi=='K'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_bayar,
      					  'tipe'          => 'BUK',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_pp,
						  'debet'         => 0,
						  'kredit'        => $nilaibayar,
						  'jenis_jurnal'  => 'pembayaran',
						  'no_request'    => $no_pp 
					     );
						}

						}

						$this->db->insert_batch('jurnal',$det_Jurnaltes);
						$status_tr	 = "UPDATE tr_pp_aset SET sts_buk=1 WHERE no_pp  = '$no_pp' ";
		                $this->db->query($status_tr);

		#END JURNAL TEMPORARY


}
		$this->db->trans_complete();
        $param = array('save' => $this->db->trans_status());
        echo json_encode($param);
	}




    public function list_payment_pp() {
        $data = $this->Po_aset_model->GetPpAset(array(2),false,'1');
        $this->template->set('results', $data);
        $this->template->title('Pembayaran Permintaan Pembayaran Aset');
        $this->template->render('list_payment_pp');
    }

	function payment_pp($id){
        $data  = $this->Po_aset_model->EditPp($id);
        if(!$data) {
            $this->template->set_message("Invalid Permintaan Pembayaran", 'error');
            redirect('po_aset/list_payment_pp');
        }
		$checkpayment	= $this->identitas_model->DataGetOne('tr_pp_aset_payment', array('status'=>'10', 'no_pp'=>$data->no_pp));
		if($checkpayment) {
			$this->pp_payment_finance($checkpayment->id);
		}else{
			$datapr     = $this->Po_aset_model->InfoPp($data->no_pr);
			$tahun=date("Y",strtotime($datapr->tgl_pr));
			$bulan=date("n",strtotime($datapr->tgl_pr));
			$dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
	//		$datbank  	 = $this->Acc_model->combo_bank();
			$datbank			= $this->Jurnal_model->get_Coa_Bank_Cabang('101');
			$pphpembelian  	 = $this->Acc_model->combo_pph_pembelian();
			$this->template->set('pphpembelian', $pphpembelian);
			$datcoa     = $this->Acc_model->GetCoaCombo();
			$datvendor	= $this->Acc_model->vendor_combo();
			$this->template->set('datcoa',$datcoa);
			$this->template->set('datppn',$this->datppn);
			$this->template->set('datapr',$datapr);
			$this->template->set('datbank',$datbank);
			$this->template->set('data',$data);
			$this->template->set('dataaset',$dataaset);
			$this->template->set('datcombodata',$this->datcombodata);
			$this->template->set('datvendor', $datvendor);
			$this->template->title('Payment Permintaan Pembayaran Aset');
			$this->template->render('pp_payment_form');
		}
	}

	function save_payment_pp(){
		$db2 = $this->load->database('accounting', TRUE);
        $id             = $this->input->post("id");
		$tgl_bayar  	= $this->input->post("tgl_bayar");
		$nilai_bayar    = $this->input->post("nilai_bayar");
        $no_pr          = $this->input->post("no_pr");
        $no_pp          = $this->input->post("no_pp");
        $bank           = $this->input->post("bank");
		$potongan   = $this->input->post("potongan");
		$potongan_note   = $this->input->post("potongan_note");

        $ppn            = $this->input->post("ppn");
        $nilai_ppn          = $this->input->post("nilai_ppn");

		$t_biaya_lain   = $this->input->post("t_biaya_lain");
		$biaya_lain_note   = $this->input->post("biaya_lain_note");
		$dpp	= $this->input->post("dpp");
		$jenis_pph = $this->input->post("jenis_pph");
		$pph	= $this->input->post("pph");
		$coa_lain		 = $this->input->post("coa_lain");
		$coa_potongan		 = $this->input->post("coa_potongan");

		$jenis_pembayaran   = $this->input->post("jenis_pembayaran");
		$notes   = $this->input->post("notes");
		$bank_id   = $this->input->post("bank_id");
		$nama_rekening   = $this->input->post("nama_rekening");
		$nomor_rekening   = $this->input->post("nomor_rekening");
		$giro_atas_nama   = $this->input->post("giro_atas_nama");
		$no_voucher   = $this->input->post("no_voucher");
		$tgl_voucher   = $this->input->post("tgl_voucher");

		$data = array(
				'no_pr'=>$no_pr, 'no_pp'=>$no_pp,
				'tgl_bayar'=>$tgl_bayar, 'nilai_bayar'=>$nilai_bayar, 'bank'=>$bank, 'potongan'=>$potongan, 'potongan_note'=>$potongan_note, 'ppn'=>$ppn, 'nilai_ppn'=>$nilai_ppn,
				'created_by'=> $this->auth->user_id(),'coa_potongan'=>$coa_potongan, 'coa_lain'=>$coa_lain, 't_biaya_lain'=>$t_biaya_lain, 'no_voucher'=>$no_pp, 'tgl_voucher'=>$tgl_bayar,
				'created_on'=>date("Y-m-d h:i:s"),'reject_reason'=>'','jenis_pph'=>$jenis_pph,'dpp'=>$dpp, 'pph'=>$pph, 'potongan'=>$potongan, 'biaya_lain_note'=>$biaya_lain_note,'status'=>1,'dppplusppn'=>$nilai_ppn+$dpp,
				'jenis_pembayaran'=>$jenis_pembayaran,'giro_atas_nama'=>$giro_atas_nama,
				'bank_id'=>$bank_id,'nama_rekening'=>$nama_rekening,'nomor_rekening'=>$nomor_rekening,

			);
		$this->db->trans_start();
			$this->identitas_model->DataSave('tr_pp_aset_payment',$data);
		$this->db->trans_complete();
        $param = array('save' => $this->db->trans_status());
        echo json_encode($param);
/*
	        $nilai_ppn =0;
		    $Cabang_Pusat	= '101';
    		$Jumlah_Bayar	= $nilai_bayar-$nilai_ppn;
    		$Jenis_Bayar	= 'BANK';
    		$Tgl_Jurnal		= $tgl_bayar;

    			$Jenis_Pay	= 'BANK';
    			$Tipe_Bayar	= 'Transfer';
    			$No_COA		= $this->input->post('bank');



  			$Keterangan_BUK	= 'Pembayaran PP ASSET#'.$no_pp.'#'.$desc;

		$Nomor_BUK		= $this->Jurnal_model->get_no_buk($Cabang_Pusat);
		$Header_BUK		= array(
			'nomor'			=> $Nomor_BUK,
			'tgl'		    => $Tgl_Jurnal,
			'jml'			=> $Jumlah_Bayar,
			'kdcab'			=> $Cabang_Pusat,
			'jenis_reff'    => $Tipe_Bayar,
			'no_reff'		=> $no_pp,
			'bayar_kepada'  => '-',
			'jenis_ap'		=> 'V'
		);

		$Detail_BUK			= array();
		$Detail_BUK[0]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => '2108-01-01',
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $no_pp,
			  'debet'         => $Jumlah_Bayar,
			  'kredit'        => 0

		);
		/*$Detail_BUK[1]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => $jenis_pph,
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $no_pp,
			  'debet'         => $pph,
			  'kredit'        => 0

		);
		$Detail_BUK[2]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => '2105-01-01',
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $no_pp,
			  'debet'         => $nilai_ppn,
			  'kredit'        => 0

		);

		$Detail_BUK[3]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => '5101-08-01',
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $no_pp,
			  'debet'         => $t_biaya_lain,
			  'kredit'        => 0

		); */

/*
		$Detail_BUK[4]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => $No_COA,
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $no_pp,
			  'debet'         => 0,
			  'kredit'        => $Jumlah_Bayar

		);

		$db2->insert('japh',$Header_BUK);
		$db2->insert_batch('jurnal',$Detail_BUK);

		$ambilnobuk = substr($Nomor_BUK, 8, 4);
		$data3 = array('nobuk' => $ambilnobuk);
		$db2->where("nocab", $Cabang_Pusat);
		$db2->update("pastibisa_tb_cabang", $data3);


		## END JURNAL BUK ##

		$this->db->trans_complete();
        $param = array('save' => $this->db->trans_status());
        echo json_encode($param);
*/
	}

    public function edit_pp($id) {
        $data  = $this->Po_aset_model->EditPp($id);
        if(!$data) {
            $this->template->set_message("Invalid Permintaan Pembayaran", 'error');
            redirect('po_aset/list_pp');
        }
        $datapr     = $this->Po_aset_model->InfoPp($data->no_pr);
		$tahun=date("Y",strtotime($datapr->tgl_pr));
		$bulan=date("n",strtotime($datapr->tgl_pr));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
		$tipe_bayar=$this->Acc_model->tipe_bayar();
		$datvendor	= $this->Acc_model->vendor_combo();
 		$this->template->set('datcombodata',$this->datcombodata);
        $this->template->set('datvendor',$datvendor);
		$this->template->set('tipe_bayar',$tipe_bayar);
		$this->template->set('datppn',$this->datppn);
        $this->template->set('datapr',$datapr);
        $this->template->set('data',$data);
        $this->template->set('dataaset',$dataaset);
        $this->template->title('Edit Permintaan Pembayaran Aset');
        $this->template->render('pp_form');
    }

	public function save_data_pp(){
		$db2 = $this->load->database('accounting', TRUE);
        $id             = $this->input->post("id");
		$tgl_pp  		= $this->input->post("tgl_pp");
        $vendor_id     	= $this->input->post('vendor_id');
        $request_payment= $this->input->post('request_payment');
        $notes     		= $this->input->post('notes');
		$type			= $this->input->post('type');
		$no_dok			= $this->input->post('no_pp');
		$coa    		= $this->input->post("id_aset");
		$no_pr    		= $this->input->post("no_pr");
		$ppn    		= $this->input->post("ppn");
		$nilai_ppn    		= $this->input->post("nilai_ppn");
        $quality_inspect         = $this->input->post("quality_inspect");
        $qty_inspect             = $this->input->post("qty_inspect");
        $note_release            = $this->input->post("note_release");

		$tipe_bayar              = $this->input->post("tipe_bayar");

		$c_inv   = $this->input->post("c_inv");
		$t_inv   = $this->input->post("t_inv");
		$t_inv_tgl   = $this->input->post("t_inv_tgl");
		$c_kontrak   = $this->input->post("c_kontrak");
		$c_surat_jalan   = $this->input->post("c_surat_jalan");
		$t_kontrak   = $this->input->post("t_kontrak");
		$t_surat_jalan   = $this->input->post("t_surat_jalan");
		$c_faktur   = $this->input->post("c_faktur");
		$t_faktur   = $this->input->post("t_faktur");
		$t_faktur_tgl   = $this->input->post("t_faktur_tgl");
		
		$coa   = $this->input->post("id_aset");
				$asset  = $this->db->query("SELECT * FROM ms_coa_aset WHERE id='$coa'")->row();
				$coa_aset = $asset->nama_aset;

		// print_r($coa);
		// exit;

		$datasave=array(
			'tgl_pp'=>$tgl_pp,
			'vendor_id'=>($vendor_id),
			'request_payment'=>$request_payment,
			'notes'=>$notes,
			'ppn'=>$ppn,
			'nilai_ppn'=>$nilai_ppn,
			'modified_by'=> $this->auth->user_id(),
			'modified_on'=>date("Y-m-d h:i:s"),
			'reject_reason'=>'',
			'quality_inspect'=>$quality_inspect,
			'qty_inspect'=>$qty_inspect,
			'note_release'=>$note_release,
			'reject_reason'=>'',
			'c_surat_jalan'=>$c_surat_jalan, 'c_kontrak'=>$c_kontrak,'t_inv_tgl'=>$t_inv_tgl, 't_inv'=>$t_inv,'c_inv'=>$c_inv,'c_faktur'=>$c_faktur,'t_kontrak'=>$t_kontrak,'t_surat_jalan'=>$t_surat_jalan,
			't_faktur'=>$t_faktur, 't_faktur_tgl'=>$t_faktur_tgl,'requestplusppn'=>$request_payment+$nilai_ppn,
		);


		$this->db->trans_start();
			$olddata  = $this->Po_aset_model->EditPp($id);
			$this->identitas_model->DataUpdate('tr_pp_aset',$datasave,array('id'=>$id));
			if($type=='approve') {
				$this->save_approval($id,$no_dok,'PP','tr_pp_aset');
				$edit_t = $this->input->post('edit_t');
				if($edit_t==1){
					$content='
					notes1:'.$olddata->notes.',
					notes2:'.$notes.',
					ppn1:'.$olddata->ppn.',
					ppn2:'.$ppn.',
					request_payment1:'.$olddata->request_payment.',
					request_payment2:'.$request_payment.'';
					$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'PPASETPAYMENT','content'=>$content));
				}

			}else{
				if($type=='approve_ap'){
					$datasave=array(
						'modified_by'=> $this->auth->user_id(),
						'modified_on'=>date("Y-m-d h:i:s"),
						'ap_cek'=>'1',
						'c_surat_jalan'=>$c_surat_jalan, 'c_kontrak'=>$c_kontrak,'t_inv_tgl'=>$t_inv_tgl, 't_inv'=>$t_inv,'c_inv'=>$c_inv,
						'c_faktur'=>$c_faktur,'t_kontrak'=>$t_kontrak,'t_surat_jalan'=>$t_surat_jalan,
						't_faktur'=>$t_faktur, 't_faktur_tgl'=>$t_faktur_tgl
					);
					$this->identitas_model->DataUpdate('tr_pp_aset',$datasave,array('id'=>$id));
					$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'PPASETPAYMENT','content'=>'AP APPROVE'));
				// JURNAL REQUEST



		if ($tipe_bayar == 0){ //full
		$kodejurnal ='JV025';
        }
		elseif ($tipe_bayar == 1){//dp
		$kodejurnal ='JV026';
		}
		elseif ($tipe_bayar == 2){//pelunasan
		$kodejurnal ='JV025';
		}


		$Keterangan_INV		    = 'PEMBELIAN ASET PP PP U/ '.$coa_aset.','.$notes.','.$note_release.','.$no_dok.' TGL PP. '.$tgl_pp;


		#AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

		$datajurnal  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal);

						foreach($datajurnal AS $record){
						$nokir  = $record->no_perkiraan;
						$tabel  = $record->menu;
						$posisi = $record->posisi;
						$field  = $record->field;
						$param  = 'no_pp';
						$value_param  = $no_dok;
						$val = $this->Acc_model->GetData($tabel,$field,$param,$value_param);
						$nilaibayar = $val[0]->$field;




						if ($posisi=='D'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_pp,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_dok,
						  'debet'         => $nilaibayar,
						  'kredit'        => 0,
						  'jenis_jurnal'  => 'approval',
						  'no_request'    => $no_dok
						  
					     );
						}
						elseif ($posisi=='K'){
						$det_Jurnaltes[]  = array(
      					  'nomor'         => '',
      					  'tanggal'       => $tgl_pp,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => $nokir,
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_dok,
						  'debet'         => 0,
						  'kredit'        => $nilaibayar,
						  'jenis_jurnal'  => 'approval',
						  'no_request'    => $no_dok
					     );
						}

						}

						$this->db->insert_batch('jurnal',$det_Jurnaltes);
						
						$status_tr	 = "UPDATE tr_pp_aset SET sts_apr=1 WHERE no_pp  = '$no_dok' ";
						$this->db->query($status_tr);

		#END JURNAL TEMPORARY
				
				}
				if($type=='edit'){
					$datasave['status']=1;
					$this->identitas_model->DataUpdate('tr_pp_aset',$datasave,array('id'=>$id));
				}
			}


       	$this->db->trans_complete();

/*
			$po = $this->Po_aset_model->GetDataNoPp($no_dok);
			    $jurnal = $po[0]->jurnal;
				$tgl_po   = $po[0]->tgl_pp;
				$nilai_pp = $po[0]->request_payment;
				$nilai_ppn =0;
			    if ($jurnal =='0'){

			//PROSES JURNAL

						$po = $this->Po_aset_model->GetDataNoCoa($coa);
						// print_r($po);
						// exit;
						$NoCoa = $po[0]->coa;

                        $nilai_po       =	$request_payment;
						$tgl_po			= $tgl_pp;
		                $Bln 			= substr($tgl_po,5,2);
		                $Thn 			= substr($tgl_po,0,4);
        				## NOMOR JV ##
        				$Nomor_JV		= $this->Jurnal_model->get_Nomor_Jurnal_Sales('101',$tgl_po);

						$no_po          = $no_pr;

						//print_r($Nomor_JV);
						//exit;


        				$Keterangan_INV		    = 'PEMBELIAN ASSET U/ PP '.$no_po.' TGL PO. '.$tgl_po;

        				$dataJVhead = array(
          					'nomor' 	    	=> $Nomor_JV,
          					'tgl'	         	=> $tgl_po,
          					'jml'	            => $nilai_po,
          					'koreksi_no'		=> '-',
          					'kdcab'				=> '101',
          					'jenis'			    => 'JV',
          					'keterangan' 		=> $Keterangan_INV,
        					'bulan'				=> $Bln,
          					'tahun'				=> $Thn,
          					'user_id'			=> $this->auth->user_id(),
          					'memo'			    => '',
          					'tgl_jvkoreksi'	    => $tgl_po,
          					'ho_valid'			=> ''
          				);

        				$det_Jurnal				= array();
        				$det_Jurnal[]			= array(
        					  'nomor'         => $Nomor_JV,
        					  'tanggal'       => $tgl_po,
        					  'tipe'          => 'JV',
        					  'no_perkiraan'  => $NoCoa,
        					  'keterangan'    => $Keterangan_INV,
        					  'no_reff'       => $no_po,
        					  'debet'         => $nilai_po,
        					  'kredit'        => 0

        				);

						if($nilai_ppn!=0)
						{
        				$det_Jurnal[]			= array(
        					  'nomor'         => $Nomor_JV,
        					  'tanggal'       => $tgl_po,
        					  'tipe'          => 'JV',
        					  'no_perkiraan'  => '2105-01-01',
        					  'keterangan'    => $Keterangan_INV,
        					  'no_reff'       => $no_po,
        					  'debet'         => $nilai_ppn,
        					  'kredit'        => 0

        				);
						}

        				$det_Jurnal[]			  = array(
      					  'nomor'         => $Nomor_JV,
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => '2108-01-01',
      					  'keterangan'    => $Keterangan_INV,
      					  'no_reff'       => $no_po,
      					  'debet'         => 0,
      					  'kredit'        => $nilai_po
      				    );



        				## INSERT JURNAL ##
        				$db2->insert('javh',$dataJVhead);
        				$db2->insert_batch('jurnal',$det_Jurnal);

        				## INSERT ACCOUNT RECEIVABLE  ##
        				//$db2->insert_batch('ar',$dataAR);

        				$Qry_Update_Cabang_acc	 = "UPDATE pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        				$db2->query($Qry_Update_Cabang_acc);


						 $Qry_Update_po_header	 = "UPDATE tr_pp_aset SET jurnal=1 WHERE no_pp='$no_dok'";
        				$this->db->query($Qry_Update_po_header);

    					//PROSES JURNAL

				}

*/
        $param = array( 'save' => $this->db->trans_status(), );
        echo json_encode($param);
	}

    public function list_approve_pp_ap() {

        $data = $this->Po_aset_model->GetPpAsetAp(array('status'=>'2','ap_cek'=>'0'));
        $this->template->set('results', $data);
        $this->template->title('Approve PP Aset AP');
        $this->template->render('list_approve_pp_ap');
    }


	function create_new_pp(){
		$nomorpr=$this->input->post("nomor_pr");
		$nilai_pr=$this->input->post("nilai_pr");
		$data =  array(
					'no_pr'=>$nomorpr,
					'tgl_pr'=>date("Y-m-d"),
					'nilai_pr'=>$nilai_pr,
				);
		$datanomor = $this->identitas_model->GentableSelect('no_pp_aset');
		$nodoc=explode(';',$datanomor[0]->info);
		$nomordoc='';
		if($nodoc[0]==date("Y")){
			$nomordoc='PPAR-'.$nodoc[0].'-'.sprintf('%04d', $nodoc[1]);
			$updnodoc=$nodoc[0].';'.($nodoc[1]+1);
		}else{
			$nomordoc='PPAR-'.($nodoc[0]+1).'-'.sprintf('%04d', 1);
			$updnodoc=date("Y").';2';
		}
		$vendor_pp		= $this->input->post("vendor_pp");
		$nilai_pp		= $this->input->post("nilai_pp");
		$note_pp		= $this->input->post("note_pp");
		$terbayar 		= $this->input->post("terbayar");
		$nilai_terbayar = ($terbayar+$nilai_pp);
		$approve=$this->identitas_model->get_maxapproval($nilai_pp,'PP');
		$datainsert =  array(
					'no_pp'=>$nomordoc,
					'no_pr'=>$data['no_pr'],
					'tgl_pp'=>$data['tgl_pr'],
					'vendor_id'=>$vendor_pp,
					'notes'=>$note_pp,
					'request_payment'=>$nilai_pp,
					'approve'=>$approve,
					'status'=>'1',
					'created_by'=> $this->auth->user_id(),
					'created_on'=>date("Y-m-d h:i:s"),
				);
		$this->db->trans_start();
			$this->identitas_model->DataSave('tr_pp_aset',$datainsert);
			$this->identitas_model->DataUpdate('tr_pr_aset',array('terbayar'=>$nilai_terbayar),array('no_pr'=>$data['no_pr']));
			$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnodoc),array('tipe'=>'no_pp_aset'));
		$this->db->trans_complete();
        $param = array('save' => $this->db->trans_status());
        echo json_encode($param);
	}

	function print_po($id){
		ob_start();
		$get_data = $this->identitas_model->DataGetOne('tr_po_aset',array('id'=>$id));
		$user_created=$this->identitas_model->DataGetOne('users',array('id_user'=>$get_data->created_by));
		$user_approved=$this->Po_aset_model->user_approved('PO',$get_data->no_po);
		$data['results'] = $get_data;
		$data['sign_user'] = $user_created;
		$data['sign_approved'] = $user_approved;
		$data['vendor'] = $this->identitas_model->DataGetOne('ms_vendor',array('id_vendor'=>$get_data->vendor_id));

		$show=$this->template->load_view('print_po',$data);

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
        $this->load->library(array('Mpdf'));
		$mpdf=new mPDF('','','','','','','','','','');
		$mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$this->mpdf->AddPage('P','A4','en');
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output();
	}

}
?>