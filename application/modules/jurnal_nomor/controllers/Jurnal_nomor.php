<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2018, Harboens
 *
 * This is controller for Purchase Order
 */

class Jurnal_nomor extends CI_Controller {

    
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Jurnal_nomor/Acc_model',
                                 'Jurnal_nomor/Jurnal_model'));
        date_default_timezone_set("Asia/Bangkok");
		$this->datppn=array('0'=>'Non PPN','10'=>'PPN');
		$this->datcombodata=array('No'=>'No','Asli'=>'Asli','Copy'=>'Copy');		
    }

    public function index()
    {        
        // $data = $this->Purchase_order_model->GetListPR('BIAYA');
        // $this->template->set('results', $data);
        // $this->template->title('Purchase Request Operational Titik (Existing)');
        // $this->template->render('list');
    }
	
	function view_jurnal_jv() {
        // $data = $this->Purchase_order_model->GetListPR('BIAYA');
        // $this->template->set('results', $data);
        // $this->template->title('Purchase Request Operational Titik (Existing)');
        // $this->template->render('list');

		//JURNAL JV PINDAH GUDANG

		$id		= $this->uri->segment(3);
	   	$kodejurnal = $this->uri->segment(4);
		$ket = $this->uri->segment(5);

		if($ket =='jurnalrevenue'){
        $noso = $this->db->query("select * FROM tr_revenue WHERE id='$id'")->row();
		
		$nomorSO = $noso->no_surat;
		$so = $noso->no_so;
		
		$invoice = $this->db->query("select no_surat FROM tr_invoice WHERE no_so='$so'")->result();
		$separator =',';
		$allinv = array();
		foreach($invoice as $inv){
		$allinv[] = $inv->no_surat;		
		}
		
		}
		
		$invc =  implode($separator, $allinv);

        $Tgl_Invoice = date('Y-m-d');

		$no_request = $id;
		$tgl_voucher =$Tgl_Invoice;

		$Keterangan_INV		    = 'Pengakuan Penjualan'.' Nomor SO '.$nomorSO.' Nomor Inv '.$invc ;//.rawurldecode($ket);

		#AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

		$datajurnal  	 = $this->Acc_model->GetTemplateJurnal($kodejurnal);
		foreach($datajurnal AS $record){
			$nokir1  = $record->no_perkiraan;
			$tabel  = $record->menu;
			$posisi = $record->posisi;
			$field  = $record->field;
			if ($field == 'jumlah_bank'){
				$nokir = $kd_bank;
			} else{
				$nokir  = $record->no_perkiraan;
			}
			$no_voucher = $id;
			$param  = 'id';
			$value_param  = $id;
			$val = $this->Acc_model->GetData($tabel,$field,$param,$value_param);
			$nilaibayar = $val[0]->$field;
			// print_r($nilaibayar);
			// exit;
			if ($posisi=='D'){
				$det_Jurnaltes[]  = array(
				  'nomor'         => '',
				  'tanggal'       => $tgl_voucher,
				  'tipe'          => 'JV',
				  'no_perkiraan'  => $nokir,
				  'keterangan'    => $Keterangan_INV,
				  'no_reff'       => $id,
				  'debet'         => $nilaibayar,
				  'kredit'        => 0,
				  'jenis_jurnal'  => $ket,
				  'no_request'    => $no_request
				 );
			} elseif ($posisi=='K'){
				$det_Jurnaltes[]  = array(
				  'nomor'         => '',
				  'tanggal'       => $tgl_voucher,
				  'tipe'          => 'JV',
				  'no_perkiraan'  => $nokir,
				  'keterangan'    => $Keterangan_INV,
				  'no_reff'       => $id,
				  'debet'         => 0,
				  'kredit'        => $nilaibayar,
				  'jenis_jurnal'  => $ket,
				   'no_request'    => $no_request
				 );
			}
		}
		$this->db->where('no_reff', $id);
		$this->db->where('jenis_jurnal', $ket);
		$this->db->delete('jurnal');
		$this->db->insert_batch('jurnal',$det_Jurnaltes);
		$noreff     = $id;
		$tipe		= 'jv';
		$jenisjurnal = $ket;
		$data['list_data'] 	    = $this->Jurnal_model->get_detail_jurnal($noreff,$tipe,$jenisjurnal);
		$data['data_perkiraan']	= $this->Acc_model->get_noperkiraan();
		$data['jenis']	        = 'JV';
		$data['akses']	        = 'jurnal';
		$data['jenis_jurnal']	= $jenisjurnal;
		$data['po_no']	        = $id;
		$data['total_po']		= $nilaibayar;
		$data['id_vendor']		= '';
		$data['nama_vendor']	= '';
		$data['no_surat']		= '';
		$this->load->view("v_detail_jurnal", $data);
	}
	
	public function save_jurnal_tras(){
		
		
		
        $session = $this->session->userdata('app_session');
		$data_session	= $this->session->userdata;
		
		$tgl_po  =$this->input->post('tgl_jurnal[0]');
		$keterangan  =$this->input->post('keterangan[0]');
		$type        =$this->input->post('type[0]');
		$reff        =$this->input->post('reff[0]');
		$no_req      =$this->input->post('no_request[0]');
		$total       =$this->input->post('total');
		$jenis       =$this->input->post('jenis');
		$tipe_jurnal       =$this->input->post('tipe');
		$jenis_jurnal       =$this->input->post('jenis_jurnal');
		
		$total_po           =$this->input->post('total_po');
		$id_vendor          =$this->input->post('vendor_id');
		$nama_vendor        =$this->input->post('vendor_nm');
		
		
		
		
		// print_r($jenis);
		// print_r ($jenis_jurnal);
		// print_r ($reff);
		// exit;
		
		
		
		
		
		$this->db->trans_begin();
		
		$Nomor_JV				= $this->Jurnal_model->get_no_buk('101');
       
       
				$Bln 			= substr($tgl_po,5,2);
				$Thn 			= substr($tgl_po,0,4);
				// ## NOMOR JV ##
				// $Nomor_JV				= $this->Jurnal_model->get_no_buk('101');
				

        			    
        				
        				$dataJVhead = array(
          					'nomor' 	    	=> $Nomor_JV,
          					'tgl'	         	=> $tgl_po,
          					'jml'	            => $total,
          					'kdcab'				=> '101',
          					'jenis_reff'	    => 'BUK',
          					'no_reff' 		    => $reff,
							'customer' 		    => $nama_vendor,
							'bayar_kepada'      => $nama_vendor,
							'jenis_ap'			=> 'V',
							'note'				=> $keterangan,
        					'user_id'			=> $session['username'],
          					'ho_valid'			=> '',
							'batal'			    => '0'
          				);
				$this->db->insert(DBACC.'.japh',$dataJVhead);
				
				
		
        for($i=0;$i < count($this->input->post('type'));$i++){
			$tipe =$this->input->post('type')[$i];
			$perkiraan =$this->input->post('no_coa')[$i];
			$noreff =$this->input->post('reff')[$i];
			$jenisjurnal =$this->input->post('jenisjurnal')[$i];
						
            $datadetail = array(
                'tipe'        => $this->input->post('type')[$i],
                'nomor'       => $Nomor_JV,
                'tanggal'     => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'     	  => $this->input->post('reff')[$i],
				'debet'      	  => $this->input->post('debet')[$i],
				'kredit'          => $this->input->post('kredit')[$i]
                );
            $this->db->insert(DBACC.'.jurnal',$datadetail);
			 
			$jurnal_posting	 = "UPDATE jurnal SET stspos=1 WHERE tipe = '$tipe'
			AND  jenis_jurnal = '$jenisjurnal' AND no_reff  = '$noreff' ";
            $this->db->query($jurnal_posting); 
             
        }
		
		
		$Qry_Update_Cabang_acc	 = "UPDATE ".DBACC.".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);
		
		$jurnal_po	 = "UPDATE purchase_order_payment SET status_jurnal='1' WHERE kd_pembayaran = '$reff' ";
        $this->db->query($jurnal_po);
		
		
             
		
		
		
		
		$datahutang = array(
                'tipe'       	 => $type,
                'nomor'       	 => $Nomor_JV,
                'tanggal'        => $tgl_po,
                'no_perkiraan'    => $this->input->post('no_coa[0]'),
                'keterangan'      => $keterangan,
                'no_reff'     	  => $reff,
				'debet'      	  => $total_po,
				'kredit'          => 0,
				'id_supplier'     => $id_vendor,
				'nama_supplier'   => $nama_vendor,
				'no_request'      => $no_req,
				
                );
				
        $this->db->insert('tr_kartu_hutang',$datahutang);
		
				
        
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
			
            $param = array(
            'save' => 0,
            'msg' => "GAGAL, simpan data..!!!",
			
            );
        }
        else
        {
            $this->db->trans_commit();
			
            $param = array(
            'save' => 1,
            'msg' => "SUKSES, simpan data..!!!",
		
            );
        }
        echo json_encode($param);
    }
	
	
}

