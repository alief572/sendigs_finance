<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Invoice
 */

class Efaktur extends Admin_Controller {

    //Permission

    protected $viewPermission   = "Efaktur.View";
    protected $addPermission    = "Efaktur.Add";
    protected $managePermission = "Efaktur.Manage";
    protected $deletePermission = "Efaktur.Delete";

    public function __construct()
    {
        parent::__construct();
		$this->load->model('Efaktur/Efaktur_model');
		$this->load->database();
        $this->template->title('Efaktur');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index(){
        //$this->auth->restrict($this->viewPermission);

		if($this->input->post()){
			$tgl_awal		= $this->input->post('tgl_awal');
			$tgl_akhir		= $this->input->post('tgl_akhir');
		}else{
			$tgl_awal		= date('Y-m-d',mktime(0,0,0,date('m')-1,1,date('Y')));
			$tgl_akhir		= date('Y-m-d');
		}
		$Qry_Data			= "SELECT * FROM view_export_efaktur WHERE (date_export BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."')";
        $data 				= $this->db->query($Qry_Data)->result_array();
        $this->template->set('results', $data);
		$this->template->set('tgl_awal', $tgl_awal);
		$this->template->set('tgl_akhir', $tgl_akhir);
        $this->template->title('Efaktur');
        $this->template->render('list');
    }

	 public function list_outstanding(){

        $data 				= $this->Efaktur_model->getArray('view_outstanding_export_efaktur');
        $this->template->set('results', $data);
        $this->template->title('E-faktur');
        $this->template->render('list_out');
    }


	public function proses(){
		if($this->input->post()){
			$getparam 		= $this->input->post('set_choose_invoice');
			$Arr_Data		= array();
			$this->db->where_in('no_invoice',$getparam);
			$Arr_Data		= $this->db->get('view_outstanding_export_efaktur')->result_array();
			$this->template->set('records', $Arr_Data);
			$this->template->title('Export E-faktur');
			$this->template->render('proses');
		}else{
			 $this->template->render('list_out');
		}

    }

    public function add(){
       if($this->input->post()){
			//echo"<pre>";print_r($this->input->post());exit;
			$detail_do			= $this->input->post('det_do');
			$Nomor				= date('ymdHi');
			$Tanggal			= date('Y-m-d');
			$Jam				= date('H:i:s');
			$OK					= 1;
			$Kode_Proses		= implode("','",$detail_do);
			$Arr_Detail			= array();
			if($detail_do){
				$intI			=0;
				foreach($detail_do as $key=>$vals){
					$intI++;
					$Arr_Ins			= array(
						'id_export'			=> $Nomor,
						'date_export'		=> $Tanggal,
						'time_export'		=> $Jam,
						'invoice_no'		=> $vals
					);
					$Arr_Detail[$intI]	= $Arr_Ins;
				}
				unset($detail_do);
			}

			$Qry_Update_Inv			= "UPDATE trans_invoice_header SET sts_faktur='Y' WHERE no_invoice IN ('".$Kode_Proses."')";

			$this->db->trans_begin();
			$this->db->query($Qry_Update_Inv);
			$this->db->insert_batch('faktur_e_logs',$Arr_Detail);

			if($this->db->trans_status() === FALSE){
				 $this->db->trans_rollback();
				 $Arr_Return		= array(
						'status'		=> 2,
						'pesan'			=> 'Save Process Failed. Please Try Again...'
				   );
			}else{
				 $this->db->trans_commit();
				 $Arr_Return		= array(
					'status'		=> 1,
					'pesan'			=> 'Save Process Success. Thank You & Have A Nice Day...'
			   );
			}

	   }else{
		   $Arr_Return		= array(
				'status'		=> 3,
				'pesan'			=> 'No Record Was Found To Process. Please Try Again...'
		   );
	   }
	   echo json_encode($Arr_Return);
    }

	function export_csv($kode=''){
		$output = 'FK;KD_JENIS_TRANSAKSI;FG_PENGGANTI;NOMOR_FAKTUR;MASA_PAJAK;TAHUN_PAJAK;TANGGAL_FAKTUR;NPWP;NAMA;ALAMAT_LENGKAP;JUMLAH_DPP;JUMLAH_PPN;JUMLAH_PPNBM;ID_KETERANGAN_TAMBAHAN;FG_UANG_MUKA;UANG_MUKA_DPP;UANG_MUKA_PPN;UANG_MUKA_PPNBM;REFERENSI';
		$output .="\n";
		$output .="LT;NPWP;NAMA;JALAN;BLOK;NOMOR;RT;RW;KECAMATAN;KELURAHAN,KABUPATEN;PROPINSI,KODE_POS;NOMOR_TELEPON";
		$output .="\n";
		$output .="OF;KODE_OBJEK;NAMA;HARGA_SATUAN;JUMLAH_BARANG;HARGA_TOTAL;DISKON;DPP;PPN;TARIF_PPNBM;PPNBM";
		$output .="\n";
		$Data_Header	= $this->Efaktur_model->getArray('view_export_efaktur_detail',array('id_export'=>$kode));
		$det_Proses		= array();
		$intP			= 0;
		foreach($Data_Header as $key=>$values){
			$intP++;
			$cons_Header		= array();
			$Data_Customer		= $this->Efaktur_model->getArray('customer',array('id_customer'=>$values['id_customer']));
			$kd_trans 			= substr($values['nofakturpajak'],0,2);
			$mp 				= substr($values['tanggal_invoice'],5,2);
			$tp 				= substr($values['tanggal_invoice'],0,4);
			$hp 				= substr($values['tanggal_invoice'],8,2);
			$tglv 				= $hp."/".$mp."/".$tp;
			$nofaktur1 			= substr($values['nofakturpajak'],2,1);
			$nofaktur2 			= substr($values['nofakturpajak'],4,3);
			$nofaktur3 			= substr($values['nofakturpajak'],8,2);
			$nofaktur4 			= substr($values['nofakturpajak'],11,8);
			$nofaktur 			= $nofaktur2.$nofaktur3.$nofaktur4;
			if(!empty($Data_Customer[0]['npwp'])){
				$nonpwp1 	= substr($Data_Customer[0]['npwp'],0,2);
				$nonpwp2 	= substr($Data_Customer[0]['npwp'],3,3);
				$nonpwp3 	= substr($Data_Customer[0]['npwp'],7,3);
				$nonpwp4 	= substr($Data_Customer[0]['npwp'],11,1);
				$nonpwp5 	= substr($Data_Customer[0]['npwp'],13,3);
				$nonpwp6 	= substr($Data_Customer[0]['npwp'],17,3);
				$nonpwp 	= $nonpwp1.$nonpwp2.$nonpwp3.$nonpwp4.$nonpwp5.$nonpwp6;
			}else{
				$nonpwp		= '000000000000000';
			}
			$cust		= trim($Data_Customer[0]['nm_customer']);

			$addr 		= (isset($Data_Customer[0]['alamat_npwp']) &&$Data_Customer[0]['alamat_npwp'])?$Data_Customer[0]['alamat_npwp']:$values['alamatcustomer'];
			$noinv 		= $values['no_invoice'];

			$jmldpp 	= round($values['dpp']);
			$jmlppn 	= floor($values['ppn']);
			$Kode_Unik	= 0;
			if ($kd_trans=="07") {
				$Kode_Unik	= 1;
			}
			$cons_Header		= array(
				'kd_trans'			=> $kd_trans,
				'faktur'			=> $nofaktur,
				'bulan'				=> $mp,
				'tahun'				=> $tp,
				'tgl_inv'			=> $tglv,
				'npwp'				=> $nonpwp,
				'customer'			=> $cust,
				'alamat'			=> $addr,
				'dpp'				=> $jmldpp,
				'ppn'				=> $jmlppn,
				'no_invoice'		=> $noinv,
				'kode_unik'			=> $Kode_Unik
			);
			
			$Total_Alat			= $Total_DPP = $Loop	= 0;
			$cons_Detail		= array();
			$Data_Detail		= $this->Efaktur_model->getArray('trans_invoice_detail',array('no_invoice'=>$values['no_invoice']));
			foreach($Data_Detail as $keyD=>$valD){
				$Loop++;
				$Qty			= $valD['jumlah'];
				$Harga_Jual		= round($valD['hargajual'] / 1.1);
				$Harga_Nett		= round($valD['harga_nett'] / 1.1);
				$Total_Jual		= $Harga_Jual * $Qty;
				$Total_Nett		= $Harga_Nett * $Qty;
				$disc			= $Total_Jual - $Total_Nett;
				
				$Total_Alat		+=$Qty;
				$Total_DPP		+=$Total_Nett;
				
				$cons_Detail[$Loop]	= array(
					'nama_barang'		=> $valD['nm_barang'],
					'harga'				=> $Harga_Jual,
					'qty'				=> $Qty,
					'total'				=> $Total_Jual,
					'disc'				=> $disc,
					'total_nett'		=> $Total_Nett
				);
			}
			$cons_Header['total_detail']	= $Total_DPP;
			$cons_Header['total_qty']		= $Total_Alat;
			
			$det_Proses[$intP]['header']	= $cons_Header;
			$det_Proses[$intP]['detail']	= $cons_Detail;
		}
		//echo"<pre>";print_r($det_Proses);exit;
		foreach($det_Proses as $keyH=>$valH){
			$DPP		= $valH['header']['dpp'];
			$Detail_DPP	= $valH['header']['total_detail'];
			$Selisih	= $Detail_DPP - $DPP;
			$Pengurang_Disc	= 0;
			if($Selisih !== 0){
				$Pengurang_Disc	= round($Selisih / $valH['header']['total_qty']);
			}
			
			$output .= 'FK;'.$valH['header']['kd_trans'].';0;'.$valH['header']['faktur'].';'.$valH['header']['bulan'].';'.$valH['header']['tahun'].';'.$valH['header']['tgl_inv'].';'.$valH['header']['npwp'].';'.$valH['header']['customer'].';'.$valH['header']['alamat'].';'.$valH['header']['dpp'].';'.$valH['header']['ppn'].';0;'.$valH['header']['kode_unik'].';0;0;0;0;'.$valH['header']['no_invoice'];
			$output .="\n";
			
			$Mulai	= 0;
			$Tot_Banding	= 0;
			$Jum_Det		= count($valH['detail']);
			foreach($valH['detail'] as $keyI=>$valI){
				$Mulai++;
				$Name_Item		= $valI['nama_barang'];
				$Harga_Item		= $valI['harga'];
				$Jum_Item		= $valI['qty'];
				$Total_Item		= $valI['total'];
				$Disc_Item		= $valI['disc'];
				$Tambah			= 0;
				
				if($Mulai == $Jum_Det){
					$Tambah		= $Selisih - $Tot_Banding;
				}else{
					$Tambah		= $Jum_Item * $Pengurang_Disc;
				}
				$Disc_Item		+=$Tambah;
				$Net_Item		= $Total_Item - $Disc_Item;
				$PPN_Item		= floor($Net_Item * 0.1);
				if($valH['header']['kd_trans'] === '04'){
					$Net_Item		= 0;
					$PPN_Item		= 0;
					
				}
				$Tot_Banding  	+= $Tambah;
				
				$output .='OF;;'.$Name_Item.';'.$Harga_Item.';'.$Jum_Item.';'.$Total_Item.';'.$Disc_Item.';'.$Net_Item.';'.$PPN_Item.';0;0;0;;;;;;;';
				$output .="\n";
				
			}
		}
		$filename = "efaktur-".date("ymdHis").".csv";
		header('Content-type: application/csv');
		header('Content-Disposition: attachment; filename='.$filename);

		echo $output;

		exit;
	}

    public function view($kode){
		$header				= $this->Efaktur_model->getArray('view_export_efaktur',array('id_export'=>$kode));
		$details			= $this->Efaktur_model->getArray('view_export_efaktur_detail',array('id_export'=>$kode));
		//echo"<pre>";print_r($details);
		$this->template->set('row_header', $header);
        //$this->template->set('customer', $customer);
        $this->template->set('row_detail', $details);

        $this->template->render('view');


    }


}

?>
