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

    protected $viewPermission   = "PembelianAset.View";
    protected $addPermission    = "PembelianAset.Add";
    protected $managePermission = "PembelianAset.Manage";
    protected $deletePermission = "PembelianAset.Delete";

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

    public function create() {
        $this->auth->restrict($this->addPermission);
        $dataaset = $this->Po_aset_model->aset_combo(date("Y"),date("n"));
		$datvendor	= $this->Acc_model->vendor_combo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
        $this->template->set('datdivisi',$datdivisi);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
		$this->template->title('Input PR Aset');
        $this->template->render('pr_form');
    }

    public function edit($id) {
        $this->auth->restrict($this->managePermission);
        $data  = $this->Po_aset_model->find_by(array('id' => $id));
        if(!$data) {
            $this->template->set_message("Invalid PR", 'error');
            redirect('po_aset/list');
			die();
        }
		$tahun=date("Y",strtotime($data->tgl_pr));
		$bulan=date("n",strtotime($data->tgl_pr));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
		$datvendor	= $this->Acc_model->vendor_combo();
        $datdivisi  = $this->Acc_model->GetDivisiCombo();
        $this->template->set('datdivisi',$datdivisi);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('datvendor',$datvendor);
        $this->template->set('dattipe_pr',$this->dattipe_pr);
        $this->template->set('data',$data);
		$this->template->title('View PR Aset');
        $this->template->render('pr_form');
    }

    public function save_data(){
        $type           = $this->input->post("type");
        $id             = $this->input->post("id");
		$tgl_pr  		= $this->input->post("tgl_pr");
        $id_aset       		= $this->input->post("id_aset");
        $divisi			= $this->input->post("divisi");
        $budget		= $this->input->post("budget");
        $budget_sisa       	= $this->input->post('budget_sisa');
        $description       	= $this->input->post('description');
        $nilai_pr       	= $this->input->post('nilai_pr');
        $tipe_pr       	= $this->input->post('tipe_pr');
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
                            )
                        );
                $result = $this->Po_aset_model->update_batch($data,'id');
                $keterangan     = "SUKSES, Edit data PR Aset ".$id.", atas id : ".$id;
                $status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
                $sql            = $this->db->last_query();

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
					);
            $id = $this->Po_aset_model->insert($data);
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
				if($tipe_pr=='PP') $this->create_pp($data);
				if($tipe_pr=='PO') $this->create_po($data);
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
            $this->identitas_model->DataSave('tr_pp_aset',$datainsert);
			$this->identitas_model->DataUpdate('tr_pr_aset',array('terbayar'=>$nilai_pp),array('no_pr'=>$data['no_pr']));
			$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnodoc),array('tipe'=>'no_pp_aset'));	
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
				'status'=>$status,  'approve_now'=>$app,
				'modified_by'=> $this->auth->user_id(),
				'modified_on'=>date("Y-m-d h:i:s"),
			);
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
		$edit_t='';
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
		$tahun=date("Y",strtotime($data->tgl_po));
		$bulan=date("n",strtotime($data->tgl_po));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $data->no_pr));
		$datvendor	= $this->Acc_model->vendor_combo();
		$this->template->set('datppn',$this->datppn);        
        $this->template->set('datvendor',$datvendor);
        $this->template->set('datapr',$datapr);
        $this->template->set('dataaset',$dataaset);
        $this->template->set('data',$data);
        $this->template->title('Pemerikasaan Barang / Jasa');
        $this->template->render('po_periksa_form');
    }

	public function save_release_po(){
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
		$coa   = $this->input->post("id_aset");

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
				);

				$approve=$this->identitas_model->get_maxapproval($request_payment,'PO');
				if($harga_total<=($request_payment+$terbayar)) $status=3;
				$datasave['no_request']=$nomordoc;
				$datasave['approve']=$approve;
				$datasave['status']=1;
				$this->identitas_model->DataSave('tr_po_aset_request_payment',$datasave);
				$this->identitas_model->DataUpdate('ms_generate',array('info'=>$updnodoc),array('tipe'=>'no_po_pp_aset'));	
				$this->identitas_model->DataUpdate('tr_po_aset',array('terbayar'=>($request_payment+$terbayar),'status'=>$status),array('id'=>$id));	
			}else{
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
				);
				$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$datasave,array('id'=>$id));
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
			
			
			
		//PROSES JURNAL
		                
						$po = $this->Po_aset_model->GetDataNoCoa($coa);
						// print_r($po);
						// exit;
						$NoCoa = $po[0]->coa;
						
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
        					  'debet'         => $nilai_bayar,
        					  'kredit'        => 0

        				);
						
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
						
        				$det_Jurnal[]			  = array(
      					  'nomor'         => $Nomor_JV,
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => '2102-01-01',
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

    					//PROSES JURNAL
		
		$this->db->trans_complete();
        $param = array(
                'save' => $this->db->trans_status(),
                );
        echo json_encode($param);
	}

	public function list_approve_payment_po(){
        $data = $this->Po_aset_model->GetPoPaymentAset(array(1));
        $this->template->set('results', $data);
        $this->template->title('Approve Payment PO Aset');
        $this->template->render('list_approve_po_payment');
	}

    public function approve_payment_po($id) {
        $datarpo  = $this->Po_aset_model->EditPoPayment($id);
        if(!$datarpo) {
            $this->template->set_message("Invalid PO", 'error');
            redirect('po_aset/list_approve_payment_po');
        }
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $datarpo->no_pr));
		$datvendor	= $this->Acc_model->vendor_combo();
        $data     = $this->Po_aset_model->InfoPo($datarpo->no_po);
		$tahun=date("Y",strtotime($data->tgl_po));
		$bulan=date("n",strtotime($data->tgl_po));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
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
		$datvendor	= $this->Acc_model->vendor_combo();
        $data     	= $this->Po_aset_model->InfoPo($datarpo->no_po);
		$datbayar	= $this->identitas_model->GentableCombo('reff_bayar');
//		$datbank  	 = $this->Acc_model->combo_bank();
		$datbank			= $this->Jurnal_model->get_Coa_Bank_Cabang('101');
		$tahun=date("Y",strtotime($data->tgl_po));
		$bulan=date("n",strtotime($data->tgl_po));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
        $datapr  = $this->Po_aset_model->find_by(array('no_pr' => $datarpo->no_pr));
		$datvendor	= $this->Acc_model->vendor_combo();
		$pphpembelian  	 = $this->Acc_model->combo_pph_pembelian();

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

	public function list_payment_po(){
        
        $data = $this->Po_aset_model->GetPoPaymentAset(array(2));
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
		$coa   = $this->input->post("id_aset");
		
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
				'no_po'=>$no_po,'jenis_pembayaran'=>$jenis_pembayaran,'top'=>$top,'giro_atas_nama'=>$giro_atas_nama,
				'bank_id'=>$bank_id,'nama_rekening'=>$nama_rekening,'nomor_rekening'=>$nomor_rekening,
				'tgl_terima_invoice'=>$tgl_terima_invoice, 'dpp'=>$dpp, 'pph'=>$pph, 'potongan'=>$potongan, 'potongan_note'=>$potongan_note,
				'nilai_ppn'=>$nilai_ppn, 't_biaya_lain'=>$t_biaya_lain,'ppn'=>$ppn, 'c_surat_jalan'=>$c_surat_jalan, 'c_kontrak'=>$c_kontrak,'t_inv_tgl'=>$t_inv_tgl, 't_inv'=>$t_inv,'c_inv'=>$c_inv,'c_faktur'=>$c_faktur,
				'nilai_bayar'=>$nilai_bayar, 'no_voucher'=>$no_voucher, 'tgl_voucher'=>$tgl_voucher,
				'created_by'=> $this->auth->user_id(), 't_faktur'=>$t_faktur, 't_faktur_tgl'=>$t_faktur_tgl,'notes'=>$notes, 
				'created_on'=>date("Y-m-d h:i:s"), 'biaya_lain_note'=>$biaya_lain_note,'no_request'=>$no_request,
				'vendor_id'=>$vendor_id,'nilai_request'=>$request_payment,'bank'=>$bank,
			);
		
		
		## JURNAL BUK ##
		
		$po = $this->Po_nonstock_model->GetDataNoPo($no_po);
		$no_pr = $po[0]->no_pr;  
		
			
		$pr = $this->Po_nonstock_model->GetDataNoPr($no_pr);
		//$tipe = $pr[0]->tipe;  
		$coa  = $pr[0]->no_coa;
		
		// print_r($po);
		// exit; 
		
		
		
		    $Cabang_Pusat	= '101';
    		$Jumlah_Bayar	= $nilai_bayar;
    		$Jenis_Bayar	= 'BANK';
    		$Tgl_Jurnal		= $tgl_voucher;
			
    			$Jenis_Pay	= 'BANK';
    			$Tipe_Bayar	= 'Transfer';
    			$No_COA		= $this->input->post('bank');
    		
		
		    
  			$Keterangan_BUK	= 'Pembayaran PO NONSTOK#'.$no_po.'#';
		
		$Nomor_BUK		= $this->Jurnal_model->get_no_buk($Cabang_Pusat);
		$Header_BUK		= array(
			'nomor'			=> $Nomor_BUK,
			'tgl'		    => $Tgl_Jurnal,
			'jml'			=> $Jumlah_Bayar,
			'kdcab'			=> $Cabang_Pusat,
			'jenis_reff'    => $Tipe_Bayar,
			'no_reff'		=> $no_po,
			'bayar_kepada'  => $vendor,
			'jenis_ap'		=> 'V'
		);

		$Detail_BUK			= array();
		$Detail_BUK[0]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => $coa,
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $nomorpr,
			  'debet'         => $dpp,
			  'kredit'        => 0

		);
		$Detail_BUK[1]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => $jenis_pph,
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $nomorpr,
			  'debet'         => $pph,
			  'kredit'        => 0

		);
		$Detail_BUK[2]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => '2105-01-01',
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $nomorpr,
			  'debet'         => $nilai_ppn,
			  'kredit'        => 0

		);
		
		$Detail_BUK[3]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => '5101-08-01',
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $nomorpr,
			  'debet'         => $t_biaya_lain,
			  'kredit'        => 0

		);


		$Detail_BUK[4]		= array(
			  'nomor'         => $Nomor_BUK,
			  'tanggal'       => $Tgl_Jurnal,
			  'tipe'          => 'BUK',
			  'no_perkiraan'  => $No_COA,
			  'keterangan'    => $Keterangan_BUK,
			  'no_reff'       => $nomorpr,
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
		
		$this->identitas_model->DataSave('tr_po_aset_payment',$data);
			

		if(is_numeric($id)) {
			$keterangan     = "SUKSES, update pembayaran po aset";
			$status         = 1;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();			
			$result         = TRUE;
			$data_po_request=array('status'=>$status_poreq,'terbayar'=>($dpp+$terbayar));
			$this->identitas_model->DataUpdate('tr_po_aset_request_payment',$data_po_request,array('id'=>$id));
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

    public function list_approve_pp() {        
        $data = $this->Po_aset_model->GetPpAset(array(1));
        $this->template->set('results', $data);
        $this->template->title('Approve Permintaan Pembelian Aset');
        $this->template->render('list_approve_pp');
    }

    public function list_payment_pp() {
        $data = $this->Po_aset_model->GetPpAset(array(2));
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
        $datapr     = $this->Po_aset_model->InfoPp($data->no_pr);
		$tahun=date("Y",strtotime($datapr->tgl_pr));
		$bulan=date("n",strtotime($datapr->tgl_pr));
        $dataaset = $this->Po_aset_model->aset_combo($tahun,$bulan);
//		$datbank  	 = $this->Acc_model->combo_bank();
		$datbank			= $this->Jurnal_model->get_Coa_Bank_Cabang('101');
        $this->template->set('datapr',$datapr);
        $this->template->set('datbank',$datbank);
        $this->template->set('data',$data);
        $this->template->set('dataaset',$dataaset);
        $this->template->title('Payment Permintaan Pembayaran Aset');
        $this->template->render('pp_payment_form');
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

		$data = array(
				'no_pr'=>$no_pr, 'no_pp'=>$no_pp,
				'tgl_bayar'=>$tgl_bayar, 'nilai_bayar'=>$nilai_bayar, 'bank'=>$bank, 'potongan'=>$potongan, 'potongan_note'=>$potongan_note,
				'created_by'=> $this->auth->user_id(),
				'created_on'=>date("Y-m-d h:i:s"),
			);
		$this->db->trans_start();
			$this->identitas_model->DataSave('tr_pp_aset_payment',$data);
			$terbayar    	= $this->input->post("terbayar");
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
			$this->identitas_model->DataUpdate('tr_pp_aset',$datapp,array('id'=>$id));
			
			## JURNAL BUK ##
		
					
		$pr = $this->Po_nonstock_model->GetDataNoPr($no_pr);
		//$tipe = $pr[0]->tipe;  
		$coa  = $pr[0]->no_coa;
		
		// print_r($po);
		// exit; 
		
		
		
		    $Cabang_Pusat	= '101';
    		$Jumlah_Bayar	= $nilai_bayar;
    		$Jenis_Bayar	= 'BANK';
    		$Tgl_Jurnal		= $tgl_bayar;
			
    			$Jenis_Pay	= 'BANK';
    			$Tipe_Bayar	= 'Transfer';
    			$No_COA		= $this->input->post('bank');
    		
		
		    
  			$Keterangan_BUK	= 'Pembayaran PO NONSTOK#'.$no_pp.'#';
		
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
			  'no_perkiraan'  => $coa,
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
		
		// print_r($coa);
		// exit;
		
		$datasave=array(
			'tgl_pp'=>$tgl_pp,
			'vendor_id'=>($vendor_id),
			'request_payment'=>$request_payment,
			'notes'=>$notes,
			'modified_by'=> $this->auth->user_id(),
			'modified_on'=>date("Y-m-d h:i:s"),
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
					request_payment1:'.$olddata->request_payment.',
					request_payment2:'.$request_payment.'';
					$this->identitas_model->DataSave('tr_log_data',array('idrow'=>$id,'tipe'=>'PPASETPAYMENT','content'=>$content));
				}
			}
			
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

        			    
        				$Keterangan_INV		    = 'PEMBELIAN ASSET U/ '.$no_po.' TGL PO. '.$tgl_po;
        				
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
						
        				/*$det_Jurnal[]			= array(
        					  'nomor'         => $Nomor_JV,
        					  'tanggal'       => $tgl_po,
        					  'tipe'          => 'JV',
        					  'no_perkiraan'  => '2105-01-01',
        					  'keterangan'    => $Keterangan_INV,
        					  'no_reff'       => $no_po,
        					  'debet'         => $nilai_ppn,
        					  'kredit'        => 0

        				);*/
						
        				$det_Jurnal[]			  = array(
      					  'nomor'         => $Nomor_JV,
      					  'tanggal'       => $tgl_po,
      					  'tipe'          => 'JV',
      					  'no_perkiraan'  => '2102-01-01',
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

    					//PROSES JURNAL
			

		$this->db->trans_complete();
        $param = array( 'save' => $this->db->trans_status(), );
        echo json_encode($param);
	}

	function create_new_pp(){
		$nomorpr=$this->input->post("nomor_pr");
		$nilai_pr=$this->input->post("nilai_pr");
		$pic=$this->input->post("pic");
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