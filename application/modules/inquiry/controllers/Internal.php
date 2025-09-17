<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Salesorder
 */

class Internal extends Admin_Controller {

    //Permission

    protected $viewPermission   = "Mutasi.View";
    protected $addPermission    = "Mutasi.Add";
    protected $managePermission = "Mutasi.Manage";
    protected $deletePermission = "Mutasi.Delete";

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload','Image_lib'));


        $this->load->model(array('Internal/Internalpo_model',
                                 'Internal/Detailinternalpo_model',
                                 'Cabang/Cabang_model',
                                 'Aktifitas/aktifitas_model',
								 'Customer/Customer_model',
								 'Jurnal_nomor/Jurnal_model',
								 'Salesorder/Salesorder_model',
								 'Trans_stock/Trans_stock_model',
								 'Salesorder/Trans_avl_model'
                                ));

        $this->template->title('SO Internal');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

  public function po()
      {

    		$session 			= $this->session->userdata('app_session');
    		$Cabang_User		= $session['kdcab'];
    		$det_Cabang			= $this->db->get('pastibisa_tb_cabang')->result();
    		$Arr_Cabang			= array();
    		if($det_Cabang){
    			foreach($det_Cabang as $key=>$vals){
    				$kode_cab				= $vals->nocab;
    				$Arr_Cabang[$kode_cab]	= $kode_cab;
    			}
    		}
    		$this->template->set('Arr_Cabang', $Arr_Cabang);
            $this->template->set('cabs_user', $Cabang_User);
            $this->template->title('Data PO Internal');
            $this->template->render('list_po');
      }

	public function bayarmutasi()
      {

		$session 			= $this->session->userdata('app_session');
    	$Cabang_User		= $session['kdcab'];

        $no_mutasi = $this->uri->segment(3);
        $header = $this->Internalpo_model->find_data('trans_internalpo_header',$no_mutasi,'no_mutasi');

		    $det_Cabang			= $this->db->get('pastibisa_tb_cabang')->result();
    		$Arr_Cabang			= array();
    		if($det_Cabang){
    			foreach($det_Cabang as $key=>$vals){
    				$kode_cab				= $vals->nocab;
    				$Arr_Cabang[$kode_cab]	= $kode_cab;
    			}
    		}
    		$this->template->set('Arr_Cabang', $Arr_Cabang);
            $this->template->set('cabs_user', $Cabang_User);
			$this->template->set('header', $header);
			$this->template->title('Data PO Internal');
            $this->template->render('list_hutang');
      }

	function display_data_json(){
		$session     = $this->session->userdata('app_session');
        $kdcabtujuan = $session['kdcab'];

		include APPPATH.'libraries/ssp.class.php';
		include APPPATH.'helpers/extend_helper.php';
		$session 			= $this->session->userdata('app_session');
		$Cabang_User		= $session['kdcab'];
		$det_Akses			= akses_server_side();
		$table 				= 'trans_internalpo_header';
		$primaryKey 		= 'no_mutasi';
		$WHERE				="";
		if($Cabang_User !='100'){
			$WHERE				= "(kdcab_asal='$Cabang_User' OR kdcab_tujuan='$Cabang_User')";
		}
		$columns = array(
			array( 'db' => 'no_mutasi', 'dt' => 'no_mutasi'),
			array( 'db' => 'no_do', 'dt' => 'no_do'),
			array( 'db' => 'no_so', 'dt' => 'no_so'),
			array( 'db' => 'kdcab_tujuan', 'dt' => 'kdcab_tujuan'),
			array( 'db' => 'cabang_tujuan', 'dt' => 'cabang_tujuan'),
			array('db' => 'kdcab_asal','dt' => 'kdcab_asal'),
			array( 'db' => 'cabang_asal', 'dt' => 'cabang_asal'),
			array( 'db' => 'id_supir', 'dt' => 'id_supir'),
			array( 'db' => 'nm_supir', 'dt' => 'nm_supir'),
			array( 'db' => 'id_kendaraan', 'dt' => 'id_kendaraan'),
			array( 'db' => 'ket_kendaraan', 'dt' => 'ket_kendaraan'),
			array( 'db' => 'status_mutasi', 'dt' => 'status_mutasi'),
			array( 'db' => 'status_receiving', 'dt' => 'status_receiving'),
			array(
				'db' => 'tgl_mutasi',
				'dt'=> 'tgl_mutasi',
				'formatter' => function($d,$row){
					return date('d F Y',strtotime($d));
				}
			),
			array(
				'db' => 'no_mutasi',
				'dt'=> 'action',
				'formatter' => function($d,$row){
					return '';
				}
			)
		);
		$sql_details = array(
			'user' => 'importa',
			'pass' => 'cent656',
			'db'   => 'importa',
			'host' => 'localhost'
		);

		echo json_encode(
			SSP::complex ( $_POST, $sql_details, $table, $primaryKey, $columns, null, $WHERE )
		);
	}


	function display_data_json_mutasi(){
		$session     = $this->session->userdata('app_session');
        $kdcabtujuan = $session['kdcab'];

		include APPPATH.'libraries/ssp.class.php';
		include APPPATH.'helpers/extend_helper.php';
		$session 			= $this->session->userdata('app_session');
		$Cabang_User		= $session['kdcab'];
		$det_Akses			= akses_server_side();
		$table 				= 'trans_internalpo_header';
		$primaryKey 		= 'no_mutasi';
		$WHERE				="";
		if($Cabang_User !='100'){
			$WHERE				= "(kdcab_asal='$Cabang_User' AND status_receiving='CLOSE')";
		}


		$columns = array(
			array( 'db' => 'no_mutasi', 'dt' => 'no_mutasi'),
			array( 'db' => 'kdcab_tujuan', 'dt' => 'kdcab_tujuan'),
			array( 'db' => 'cabang_tujuan', 'dt' => 'cabang_tujuan'),
			array('db' => 'kdcab_asal','dt' => 'kdcab_asal'),
			array( 'db' => 'cabang_asal', 'dt' => 'cabang_asal'),
			array( 'db' => 'id_supir', 'dt' => 'id_supir'),
			array( 'db' => 'nm_supir', 'dt' => 'nm_supir'),
			array( 'db' => 'id_kendaraan', 'dt' => 'id_kendaraan'),
			array( 'db' => 'ket_kendaraan', 'dt' => 'ket_kendaraan'),
			array( 'db' => 'status_mutasi', 'dt' => 'status_mutasi'),
			array( 'db' => 'status_receiving', 'dt' => 'status_receiving'),
			array( 'db' => 'total_hutang', 'dt' => 'total_hutang'),
			array( 'db' => 'status_hutang', 'dt' => 'status_hutang'),
			array(
				'db' => 'tgl_mutasi',
				'dt'=> 'tgl_mutasi',
				'formatter' => function($d,$row){
					return date('d F Y',strtotime($d));
				}
			),
			array(
				'db' => 'no_mutasi',
				'dt'=> 'action',
				'formatter' => function($d,$row){
					return '';
				}
			)
		);
    $sql_details = array(
			'user' => 'importa',
			'pass' => 'cent656',
			'db'   => 'importa',
			'host' => 'localhost'
		);



		echo json_encode(
			SSP::complex ( $_POST, $sql_details, $table, $primaryKey, $columns, null, $WHERE )
		);
	}


	   public function pembayaranmutasi(){
		   $session 				= $this->session->userdata('app_session');
		   $Cabang_Bayar			= $session['kdcab'];



		    $no_mutasi = $this->uri->segment(3);
            $header = $this->Internalpo_model->find_data('trans_internalpo_header',$no_mutasi,'no_mutasi');
			$cabang_Pengirim = $header->kdcab_tujuan;

			$coa_Bayar              = $this->Internalpo_model->get_Coa_Kas_Bank($cabang_Pengirim);
  		    $det_Coa				= $this->Internalpo_model->get_Coa_Kas_Bank($Cabang_Bayar);


			$det_Detail				= $this->db->get('ar_cabang')->result();
  			//echo"<pre>";print_r($det_Detail);exit;
  			$this->template->set('rows_data', $det_Detail);
			$this->template->set('rows_coa', $det_Coa);
			$this->template->set('rows_coakirim', $coa_Bayar);
  			$this->template->set('rows_cabang', $det_Cabang);
  			$this->template->set('records', $Arr_Data);
			$this->template->set('header', $header);
  			$this->template->title('Bayar Hutang');
  			$this->template->render('bayarmutasi_form');


    }

  public function create()
    {
  		$session 			= $this->session->userdata('app_session');
  		$Cabang_User		= $session['kdcab'];
  		$det_Cabang			= $this->db->get_where('pastibisa_tb_cabang',array('nocab !='=>'100'))->result();
  		$Arr_Cabang			= array();
  		if($det_Cabang){
  			foreach($det_Cabang as $key=>$vals){
  				$kode_cab				= $vals->nocab;
  				$Arr_Cabang[$kode_cab]	= $vals->cabang;
  			}
  		}
  		$stok_cabang	= array();
  		if($Cabang_User !='100'){
  			$stok_cabang 	= $this->Internalpo_model->get_data(array('kdcab'=>$Cabang_User, 'kategori'=>'set'),'barang_stock');
  		}
      		$this->template->set('Arr_Cabang', $Arr_Cabang);
          $this->template->set('cabs_user', $Cabang_User);
          $this->template->set('stok_cabang',$stok_cabang);
          $this->template->title('Create PO Internal');
          $this->template->render('po_form');
    }

	public function so()
    {

		$session = $this->session->userdata('app_session');
        $kdcabtujuan = $session['kdcab'];
        //$kdcabtujuan = '102';
        $data = $this->Internalpo_model->where(array('kdcab_tujuan'=>$kdcabtujuan))->order_by('no_mutasi','ASC')->find_all();

        $this->template->set('results', $data);
        $this->template->title('Buat SO Internal');
        $this->template->render('list_so');
    }


    public function receive($no_mutasi)
      {
        $session = $this->session->userdata('app_session');

        $header = $this->Internalpo_model->find_data('trans_internalpo_header',$no_mutasi,'no_mutasi');
    		$detail = $this->Detailinternalpo_model->find_all_by(array('no_mutasi' => $no_mutasi));
        //$detail = $this->db->where(array('keterangan'=>$no_mutasi,'MID(trans_so_header_internal.no_so,5,3)'=>'SOI'))->join('trans_so_detail_internal','trans_so_detail_internal.no_so = trans_so_header_internal.no_so', 'left')->get('trans_so_header_internal')->row();
    		$detail2 = $this->Detailinternalpo_model->find_all_by(array('no_mutasi' => $no_mutasi));
    		$customer = $this->Customer_model->find_all_by(array('deleted'=>0,'kdcab'=>$session['kdcab']));

            //print_r($detail);
    		$this->template->set('customer',$customer);
            $this->template->set('header', $header);
            $this->template->set('detail', $detail);

          $this->template->set('results', $data);
          $this->template->title('Receive');
          $this->template->render('list_receive');

      }

	public function getdetailmutasi(){

		$session = $this->session->userdata('app_session');
        $kdcabtujuan = $session['kdcab'];

        $no_mutasi = $this->uri->segment(3);
        $header = $this->Internalpo_model->find_data('trans_internalpo_header',$no_mutasi,'no_mutasi');
		$detail = $this->Detailinternalpo_model->getDetailPO($no_mutasi,$kdcabtujuan);
		$detail2 = $this->Detailinternalpo_model->find_all_by(array('no_mutasi' => $no_mutasi));
		$customer = $this->Customer_model->find_all_by(array('deleted'=>0,'kdcab'=>$session['kdcab']));

        //print_r($detail);
		$this->template->set('customer',$customer);
        $this->template->set('header', $header);
        $this->template->set('detail', $detail);
        $this->template->render('getdetailpo');
        //$this->template->load_view('getdetailmutasi',$data);
  }


	  function update_avl(){

	   $barang = $_GET['barang'];
	   $qty    = $_GET['qty'];
	   $session = $this->session->userdata('app_session');
       $kdcabtujuan = $session['kdcab'];

       $data=  $this->Internalpo_model->update_stok_avl($barang,$kdcabtujuan,$qty);

	  }

	 function saveso(){
		  // $data				= $this->input->post();
		  // $dataXX			= $data['data'];
		  // echo"<pre>";
		 // print_r($data);
         // exit;
		 //


        // $tglso              = date('Y-m-d');
		$tglso              = $this->input->post('tgl_mutasi');
        $session            = $this->session->userdata('app_session');
        $noso               = $this->Internalpo_model->generate_noso($session['kdcab'],$tglso);
		$no_pickinglist     = $this->Internalpo_model->generate_no_pl($session['kdcab'],$noso);
        $idcustomer         = $this->input->post('idcustomer');
        $nomutasi           = $this->input->post('no_mutasi');
        $idsalesman         = $this->input->post('idsalesman');
		$datcustomer        = $this->Internalpo_model->get_customer($idcustomer);
		foreach($datcustomer as $keyD=>$valD)
		{
		$nmcustomer 		= $valD->nm_customer;
		}
        $waktu              = date('Y-m-d H:i:s');
        $statusso           = 'OPEN';
		$ongkoskirim        = str_replace(',','',$this->input->post('biayakirim'));
        $dppso              = str_replace(',','',$this->input->post('grandtotal'));
        $totalso            = str_replace(',','',$this->input->post('grandtotal'));


        $dataheaderso = array(
            'no_so'               => $noso,
            'no_picking_list'     => $no_pickinglist,
            'id_customer'         => $idcustomer,
            'nm_customer'         => $nmcustomer,
            'tanggal'             => $tglso,
            'waktu'               => $waktu,
            'dpp'                 => $dppso,
            'stsorder'            => $statusso,
		    'biaya_kirim'         => $ongkoskirim,
            'total'               => $totalso,
            'keterangan'          => $nomutasi,
            'created_on'          => date("Y-m-d H:i:s"),
            'created_by'          => $session['id_user']
            );





		// print_r($_POST['data'] );
		//


        $this->db->trans_begin();

        $this->db->insert('trans_so_header_internal',$dataheaderso);



		foreach($_POST['data'] as $d){


		$idbarang          = $d[id_barang_rec_mutasi];
		$nmbarang          = $d[nm_barang];
		$satuan      	   = $d[satuan];
		$jenis            = $d[jenis];
		$qty              = $d[qty_kirim];
		$hargatotal       = $d[hargatotal];
		$harga            = $d[harga];
		$hpp              = $d[hpp];


		 $this->auth->restrict($this->addPermission);


			$dataitem = array(
              'no_so'               => $noso,
              'id_barang'           => $idbarang,
              'nm_barang'           => $nmbarang,
              'satuan'              => $satuan,
              'jenis'               => $jenis,
              'qty_order'           => $qty,
			  'qty_booked'          => $qty,
              'stok_avl'            => 0,
			  'harga_normal'        => $harga,
              'harga'               => $harga,
              'subtotal'            => $harga*$qty,
              'tgl_order'           =>date("Y-m-d"),
              'created_by'          =>$session['id_user']
            );

            $this->db->insert('trans_so_detail_internal',$dataitem);


			  //Update QTY_AVL
            $keycek = array('kdcab'=>$session['kdcab'],'id_barang'=>$idbarang);
            $stok_avl = $this->Salesorder_model->cek_data($keycek,'barang_stock');
            $this->db->where($keycek);
            $this->db->update('barang_stock',array('qty_avl'=>$stok_avl->qty_avl-$qty));


			 $get_data_barang = $this->Salesorder_model->get_item_barang($idbarang,$session['kdcab'])->row();
  			 $data_avl = $get_data_barang->qty_avl; //GET DATA AVAILABLE, AGAR TIDAK BENTROK DG USER LAIN
            //Update QTY_AVL

			         $id_st 			= $this->Salesorder_model->gen_st($this->auth->user_cab()).$i;
  					$data_adj_trans 	= array(
  						'id_st'				    => $id_st.$idbarang,
  						'tipe'				    => 'OUT',
  						'jenis_trans'		  => 'OUT_SO',
  						'noreff'		  	  => $noso,
  						'id_barang'			  => $idbarang,
  						'nm_barang'			  => $get_data_barang->nm_barang,
  						'kdcab'			  	  => $this->auth->user_cab(),
  						'date_stock'		  => date('Y-m-d H:i:s'),
  						'qty'			      => $qty,
  						'nilai_barang'	      => $harga,
  						'notes'			  	  => 'SO INTERNAL',
  						'qty_stock_awal'	=> $get_data_barang->qty_stock,
  						'qty_avl_awal' 		=> $data_avl,
  						'qty_stock_akhir'	=> $get_data_barang->qty_stock,
  						'qty_avl_akhir' 	=> $data_avl-$qty
  					);
  					$this->Trans_avl_model->insert($data_adj_trans);

        }


		 $this->db->where('no_mutasi',$nomutasi);
		 $this->db->update('trans_internalpo_header',array('status_mutasi' =>'CLOSE', 'no_so'=>$noso));

		  $this->db->where('no_mutasi',$nomutasi);
		 $this->db->update('trans_internalpo_detail',array('no_so'=>$noso));

	   if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            $param = array(
            'save' => 0,
            'msg' => "GAGAL, simpan data..!!!"
            );
        }
        else
        {
            $this->db->trans_commit();
            $param = array(
            'save' => 1,
            'msg' => "SUKSES, simpan data..!!!"
            );
        }
        echo json_encode($param);
    }


	function get_stock_item(){
		$Cabang			= $this->input->post('cabang');
		$stok_cabang 	= $this->Internalpo_model->get_data(array('kdcab'=>$Cabang),'barang_stock');
		$data			= array(
			'rows_data'		=> $stok_cabang
		);

		echo"<pre>";print_r($Cabang	);
		exit;

		$this->template->set('rows_data', $stok_cabang);
		$this->template->render('list_stock');
	}


	function get_Driver($cabang=''){
		$driver 	= $this->Internalpo_model->pilih_driver($cabang)->result();
		$Arr_Driver	= array();
		if($driver){
			foreach($driver as $keyD=>$valD){
				$Kode_Driver		= $valD->id_karyawan;
				$Name_Driver		= $valD->nama_karyawan;
				$Arr_Driver[$Kode_Driver]	= $Name_Driver;
			}
			unset($driver);
		}
		echo json_encode($Arr_Driver);
	}


	function get_Kendaraan($cabang=''){
		$Arr_Kendaraan 	= array();
		$kendaraan 		= $this->Internalpo_model->pilih_kendaraan($cabang)->result();
		if($kendaraan){
			foreach($kendaraan as $keyK=>$valK){
				$Id_kendaraan        			= $valK->id_kendaraan;
				$Nama_kendaraan        			= $valK->nm_kendaraan;
				$Arr_Kendaraan[$Id_kendaraan]   = $Nama_kendaraan;
			}
			unset($kendaraan);
		}
		echo json_encode($Arr_Kendaraan);
	}


  public function save_po(){
        $session = $this->session->userdata('app_session');
        $cabang_asal = explode('|',$this->input->post('cabang_asal'));
        $cabang_tujuan = explode('|',$this->input->post('cabang_tujuan'));

        $dataheader = array(
            'no_mutasi'         => $this->Internalpo_model->generate_no_mutasi($session['kdcab']),
            // 'tgl_mutasi'        => date('Y-m-d'),
			 'tgl_mutasi'       => $this->input->post('tanggal_po'),
            'kdcab_asal'        => $cabang_asal[0],
            'cabang_asal'       => $cabang_asal[1],
            'kdcab_tujuan'      => $cabang_tujuan[0],
            'cabang_tujuan'     => $cabang_tujuan[1],
            'id_supir'          => $Kode_Driver,
            'nm_supir'          => $Name_Driver,
            'id_kendaraan'      => $Kode_Kendaraan,
            'ket_kendaraan'     => $Nama_Kendaraan,
            //'nm_helper'         => $this->input->post('helper_do'),
            'status_mutasi'     => 'OPEN',
            'created_on'        => date('Y-m-d H:i:s'),
            'created_by'        => $session['id_user']
            );
        $this->db->trans_begin();
        for($i=0;$i < count($this->input->post('kode_produk'));$i++){
            $datadetail = array(
                'no_mutasi'     => $this->Internalpo_model->generate_no_mutasi($session['kdcab']),
                'id_barang'     => $this->input->post('kode_produk')[$i],
                'nm_barang'     => $this->input->post('nama_produk')[$i],
                'qty_mutasi'    => $this->input->post('qty_mutasi')[$i],
                'created_on'    => date('Y-m-d H:i:s'),
                'created_by'    => $session['id_user']
                );
             $this->db->insert('trans_internalpo_detail',$datadetail);
             //Update QTY_AVL
             // $keycek = array('kdcab'=>$session['kdcab'],'id_barang'=>$this->input->post('kode_produk')[$i]);
             // $stok_avl = $this->Internalpo_model->cek_data($keycek,'barang_stock');
             // $this->db->where($keycek);
             // $this->db->update('barang_stock',array('qty_avl'=>$stok_avl->qty_avl-$this->input->post('qty_mutasi')[$i]));
             //Update QTY_AVL
        }
        //Update counter NO_MUTASI
        $count = $this->Internalpo_model->cek_data(array('kdcab'=>$session['kdcab']),'cabang');
        $this->db->where(array('kdcab'=>$session['kdcab']));
        $this->db->update('cabang',array('no_mutasi'=>$count->no_mutasi+1));
        //Update counter NO_MUTASI
        $this->db->insert('trans_internalpo_header',$dataheader);
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            $param = array(
            'save' => 0,
            'msg' => "GAGAL, simpan data..!!!"
            );
        }
        else
        {
            $this->db->trans_commit();
            $param = array(
            'save' => 1,
            'msg' => "SUKSES, simpan data..!!!"
            );
        }
        echo json_encode($param);
    }


	function view_data($kode=''){
		$header = $this->Internalpo_model->find_data('trans_internalpo_header',$kode,'no_mutasi');
		$detail = $this->DetailInternalpo_model->find_all_by(array('no_mutasi' => $kode));

		$this->template->set('header', $header);
		$this->template->set('detail', $detail);
		$this->template->title('View Mutasi Produk');
		$this->template->render('view_detail');
	}


  function print_request($mutasi){
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $header = $this->Internalpo_model->find_data('trans_internalpo_header',$mutasi,'no_mutasi');
        $detail = $this->DetailInternalpo_model->find_all_by(array('no_mutasi' => $mutasi));

        $this->template->set('header', $header);
        $this->template->set('detail', $detail);

        $show = $this->template->load_view('print_data',$data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }


	function getCustomer(){
        $idcustomer = $_GET['idcustomer'];
        $datcustomer = $this->Internalpo_model->get_customer($idcustomer)->row();

        echo json_encode($datcustomer);
  }


  function savereceive(){

	$datapost = $this->input->post();

	 // print_r($datapost);
	 //

	$no_mutasi = $this->input->post('no_mutasi');
	$penerima  = $this->input->post('cabang_tujuan');
	$pengirim  = $this->input->post('cabang_asal');
	$kendaraan = $this->input->post('kendaraan');
	$tglreceive = $this->input->post('tgl_receive');

    $i=0;
    $this->db->trans_begin();
    foreach($_POST['data'] as $d){


  		$idbarang         = $d[id_barang_rec_mutasi];
  		$nmbarang         = $d[nm_barang];
  		$satuan      	    = $d[satuan];
  		$jenis            = $d[jenis];
  		$qty              = $d[qty_receive];

      $ambil_so = $this->db->where(array('keterangan'=>$no_mutasi,'MID(trans_so_header_internal.no_so,5,3)'=>'SOI','id_barang'=>$idbarang))->join('trans_so_detail_internal','trans_so_detail_internal.no_so = trans_so_header_internal.no_so', 'left')->get('trans_so_header_internal')->row();
      $ambil_barang = $this->db->where(array('id_barang'=>$idbarang,'kdcab'=>$this->auth->user_cab()))->get('barang_stock')->row();

      $qty_stock_sebelum = $ambil_barang->qty_stock;
      $qty_avl_sebelum = $ambil_barang->qty_avl;
      $qty_stock_sesudah = $ambil_barang->qty_stock+$qty;
      $qty_avl_sesudah = $ambil_barang->qty_avl+$qty;
      $landed_cost_sebelum = $ambil_barang->landed_cost;
      $landed_cost_sesudah = (($qty_stock_sebelum*$landed_cost_sebelum)+($qty*$ambil_so->harga))/($qty_stock_sebelum+$qty);

  			$dataheader = array(

                'tgl_receive'         => $tglreceive,
				'id_kendaraan'        => $kendaraan,
                'id_penerima'         =>$this->auth->user_id()
              );


			$dataitem = array(
                'qty_received'        => $qty,
                'received_on'           =>date("Y-m-d H:i:s"),
                'received_by'          =>$this->auth->user_id()
              );




		$this->db->where(array('no_mutasi'=>$no_mutasi))->update('trans_internalpo_header',$dataheader);
		$this->db->where(array('no_mutasi'=>$no_mutasi,'id_barang'=>$idbarang))->update('trans_internalpo_detail',$dataitem);
        $this->db->where(array('id_barang'=>$idbarang,'kdcab'=>$this->auth->user_cab()))->update('barang_stock',array('qty_stock'=>$qty_stock_sesudah,'qty_avl'=>$qty_avl_sesudah,'landed_cost'=>$landed_cost_sesudah));

        $id_st 			= $this->Trans_stock_model->gen_st($this->auth->user_cab()).$i;
  			$data_adj_trans 	= array(
  				'id_st'				=> $id_st,
  				'tipe'				=> 'IN',
  				'jenis_trans'		=> 'IN MUTASI',
  				'noreff'			=> $no_mutasi,
  				'id_barang'			=> $idbarang,
  				'nm_barang'			=> $nmbarang,
  				'kdcab'				=> $this->auth->user_cab(),
  				'date_stock'		=> date('Y-m-d H:i:s'),
  				'qty'				=> $qty,
  				'nilai_barang'		=> $ambil_so->harga,
  				'notes'				=> 'MUTASI SO SO INTERNAL',
  				'qty_stock_awal'	=> $qty_stock_sebelum,
  				'qty_avl_awal' 		=> $qty_avl_sebelum,
  				'qty_stock_akhir'	=> $qty_stock_sesudah,
  				'qty_avl_akhir' 	=> $qty_avl_sesudah
  			);
  			$this->Trans_stock_model->insert($data_adj_trans);


        $i++;



    }


	$Tgl_Jurnal = $this->input->post('tgl_receive');
	$total		= $ambil_so->total;
	$totalsemua = $ambil_so->total + $ambil_so->biaya_kirim;
	$biayakirim = $ambil_so->biaya_kirim;

	$this->db->where(array('no_mutasi' => $no_mutasi));
    $this->db->update('trans_internalpo_header',array('status_receiving'=>'CLOSE','status_hutang'=>'OPEN','total_hutang'=>$total ));


	## JURNAL PERSEDIAAN ##
	    ## JOGJA-JKT ## HUTANG JKT -JOGJA
  		if($pengirim =='101' && $penerima =='102' ){
	    #JURNAL PENERIMA
		$session 				= $this->session->userdata('app_session');

    		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($penerima,$Tgl_Jurnal);
    		$Keterangan_JV			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead = array(
    			'nomor' 	    	=> $Nomor_JV,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $penerima,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal				= array();
    		$det_Jurnal[0]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[1]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[2]			= array(
    				  'nomor'         => $Nomor_JV,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-03',
    				  'keterangan'    => $Keterangan_JV,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $totalsemua

    			);

			$this->db->insert('javh',$dataJVhead);
    		$this->db->insert_batch('jurnal',$det_Jurnal);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($penerima,'JP');
    		## END JURNAL  PENERIMA##
			
			
			##JURNAL ELIMINASI##
			$Nomor_JV_EL				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian('110',$Tgl_Jurnal);
    		$Keterangan_JV_EL			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead_EL = array(
    			'nomor' 	    	=> $Nomor_JV_EL,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> '110',
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_EL,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal_EL				= array();
			
			$det_Jurnal_EL[0]			= array(
    				  'nomor'         => $Nomor_JV_EL,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-03',
    				  'keterangan'    => $Keterangan_JV_EL,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => $totalsemua,
    				  'kredit'        => 0

    			);
    		$det_Jurnal_EL[1]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal_EL[2]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		

			$this->db->insert('javh',$dataJVhead_EL);
    		$this->db->insert_batch('jurnal',$det_Jurnal_EL);
			$Update_JV_EL = $this->Jurnal_model->update_Nomor_Jurnal('110','JP');




		}

		## JURNAL PERSEDIAAN ##
	    ## JOGJA-SURABAYA ## HUTANG SBY -JOGJA
  		if($pengirim =='101' && $penerima =='103' ){
	    #JURNAL PENERIMA
		$session 				= $this->session->userdata('app_session');

    		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($penerima,$Tgl_Jurnal);
    		$Keterangan_JV			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead = array(
    			'nomor' 	    	=> $Nomor_JV,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $penerima,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal				= array();
    		$det_Jurnal[0]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[1]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[2]			= array(
    				  'nomor'         => $Nomor_JV,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-06',
    				  'keterangan'    => $Keterangan_JV,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $totalsemua

    			);

			$this->db->insert('javh',$dataJVhead);
    		$this->db->insert_batch('jurnal',$det_Jurnal);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($penerima,'JP');
    		## END JURNAL  PENERIMA##

           ##JURNAL ELIMINASI##
			$Nomor_JV_EL				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian('110',$Tgl_Jurnal);
    		$Keterangan_JV_EL			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead_EL = array(
    			'nomor' 	    	=> $Nomor_JV_EL,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> '110',
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_EL,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal_EL				= array();
			
			$det_Jurnal_EL[0]			= array(
    				  'nomor'         => $Nomor_JV_EL,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-06',
    				  'keterangan'    => $Keterangan_JV_EL,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => $totalsemua,
    				  'kredit'        => 0

    			);
    		$det_Jurnal_EL[1]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal_EL[2]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		

			$this->db->insert('javh',$dataJVhead_EL);
    		$this->db->insert_batch('jurnal',$det_Jurnal_EL);
			$Update_JV_EL = $this->Jurnal_model->update_Nomor_Jurnal('110','JP');

		}


		## JURNAL PERSEDIAAN ##
		#PENERIMA#
	    ## JKT-JOGJA ## HUTANG JOGJA -JKT
  		if($pengirim =='102' && $penerima =='101' )
		 {
  			## JURNAL PENERIMA ##
    		$session 				= $this->session->userdata('app_session');

    		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($penerima,$Tgl_Jurnal);
    		$Keterangan_JV			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead = array(
    			'nomor' 	    	=> $Nomor_JV,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $penerima,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

			// persediaan
			// biaya kirim
				//hutang antar cabang

    		$det_Jurnal				= array();
    		$det_Jurnal[0]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[1]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[2]			= array(
    				  'nomor'         => $Nomor_JV,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-01',
    				  'keterangan'    => $Keterangan_JV,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $totalsemua

    			);

			$this->db->insert('javh',$dataJVhead);
    		$this->db->insert_batch('jurnal',$det_Jurnal);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($penerima,'JP');
    		## END JURNAL  PENERIMA##

            ##JURNAL ELIMINASI##
			$Nomor_JV_EL				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian('110',$Tgl_Jurnal);
    		$Keterangan_JV_EL			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead_EL = array(
    			'nomor' 	    	=> $Nomor_JV_EL,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> '110',
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_EL,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal_EL				= array();
			
			$det_Jurnal_EL[0]			= array(
    				  'nomor'         => $Nomor_JV_EL,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-01',
    				  'keterangan'    => $Keterangan_JV_EL,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => $totalsemua,
    				  'kredit'        => 0

    			);
    		$det_Jurnal_EL[1]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal_EL[2]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		

			$this->db->insert('javh',$dataJVhead_EL);
    		$this->db->insert_batch('jurnal',$det_Jurnal_EL);
			$Update_JV_EL = $this->Jurnal_model->update_Nomor_Jurnal('110','JP');


	    }

		## JURNAL PERSEDIAAN ##
		#PENERIMA#
	    ## JKT-SURABAYA ##  HUTANG SBY -JKT
  		if($pengirim =='102' && $penerima =='103' )
		 {
  			## JURNAL PENERIMA ##
    		$session 				= $this->session->userdata('app_session');

    		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($penerima,$Tgl_Jurnal);
    		$Keterangan_JV			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead = array(
    			'nomor' 	    	=> $Nomor_JV,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $penerima,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

			// persediaan
			// biaya kirim
				//hutang antar cabang

    		$det_Jurnal				= array();
    		$det_Jurnal[0]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[1]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[2]			= array(
    				  'nomor'         => $Nomor_JV,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-05',
    				  'keterangan'    => $Keterangan_JV,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $totalsemua

    			);

			$this->db->insert('javh',$dataJVhead);
    		$this->db->insert_batch('jurnal',$det_Jurnal);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($penerima,'JP');
    		## END JURNAL  PENERIMA##
			
			
			##JURNAL ELIMINASI##
			$Nomor_JV_EL				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian('110',$Tgl_Jurnal);
    		$Keterangan_JV_EL			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead_EL = array(
    			'nomor' 	    	=> $Nomor_JV_EL,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> '110',
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_EL,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal_EL				= array();
			
			$det_Jurnal_EL[0]			= array(
    				  'nomor'         => $Nomor_JV_EL,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-05',
    				  'keterangan'    => $Keterangan_JV_EL,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => $totalsemua,
    				  'kredit'        => 0

    			);
    		$det_Jurnal_EL[1]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal_EL[2]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		

			$this->db->insert('javh',$dataJVhead_EL);
    		$this->db->insert_batch('jurnal',$det_Jurnal_EL);
			$Update_JV_EL = $this->Jurnal_model->update_Nomor_Jurnal('110','JP');




	    }

	## JURNAL PERSEDIAAN ##
		#PENERIMA#
	    ## SURABAYA-JOGJA ##  HUTANG JOGJA -SBY
  		if($pengirim =='103' && $penerima =='101' )
		 {
  			## JURNAL PENERIMA ##
    		$session 				= $this->session->userdata('app_session');

    		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($penerima,$Tgl_Jurnal);
    		$Keterangan_JV			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead = array(
    			'nomor' 	    	=> $Nomor_JV,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $penerima,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

			// persediaan
			// biaya kirim
				//hutang antar cabang

    		$det_Jurnal				= array();
    		$det_Jurnal[0]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[1]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[2]			= array(
    				  'nomor'         => $Nomor_JV,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-02',
    				  'keterangan'    => $Keterangan_JV,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $totalsemua

    			);

			$this->db->insert('javh',$dataJVhead);
    		$this->db->insert_batch('jurnal',$det_Jurnal);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($penerima,'JP');
    		## END JURNAL  PENERIMA##
			
			##JURNAL ELIMINASI##
			$Nomor_JV_EL				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian('110',$Tgl_Jurnal);
    		$Keterangan_JV_EL			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead_EL = array(
    			'nomor' 	    	=> $Nomor_JV_EL,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> '110',
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_EL,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal_EL				= array();
			
			$det_Jurnal_EL[0]			= array(
    				  'nomor'         => $Nomor_JV_EL,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-02',
    				  'keterangan'    => $Keterangan_JV_EL,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => $totalsemua,
    				  'kredit'        => 0

    			);
    		$det_Jurnal_EL[1]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal_EL[2]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		

			$this->db->insert('javh',$dataJVhead_EL);
    		$this->db->insert_batch('jurnal',$det_Jurnal_EL);
			$Update_JV_EL = $this->Jurnal_model->update_Nomor_Jurnal('110','JP');

			//
			//


	    }


	    ## JURNAL PERSEDIAAN ##
		#PENERIMA#
	    ## SURABAYA-JKT ##     HUTANG JKT -SBY
  		if($pengirim =='103' && $penerima =='102' )
		 {
  			## JURNAL PENERIMA ##
    		$session 				= $this->session->userdata('app_session');

    		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($penerima,$Tgl_Jurnal);
    		$Keterangan_JV			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead = array(
    			'nomor' 	    	=> $Nomor_JV,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $penerima,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

			// persediaan
			// biaya kirim
				//hutang antar cabang

    		$det_Jurnal				= array();
    		$det_Jurnal[0]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[1]			= array(
    			  'nomor'         => $Nomor_JV,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		$det_Jurnal[2]			= array(
    				  'nomor'         => $Nomor_JV,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-04',
    				  'keterangan'    => $Keterangan_JV,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $totalsemua

    			);

			$this->db->insert('javh',$dataJVhead);
    		$this->db->insert_batch('jurnal',$det_Jurnal);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($penerima,'JP');
    		## END JURNAL  PENERIMA##
			
			##JURNAL ELIMINASI##
			$Nomor_JV_EL				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian('110',$Tgl_Jurnal);
    		$Keterangan_JV_EL			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVhead_EL = array(
    			'nomor' 	    	=> $Nomor_JV_EL,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> '110',
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_EL,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_Jurnal_EL				= array();
			
			$det_Jurnal_EL[0]			= array(
    				  'nomor'         => $Nomor_JV_EL,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '2105-01-04',
    				  'keterangan'    => $Keterangan_JV_EL,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => $totalsemua,
    				  'kredit'        => 0

    			);
    		$det_Jurnal_EL[1]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1105-01-01',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $total,
    			  'kredit'        => 0

    		);
    		$det_Jurnal_EL[2]			= array(
    			  'nomor'         => $Nomor_JV_EL,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6201-01-15',
    			  'keterangan'    => $Keterangan_JV_EL,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $biayakirim,
    			  'kredit'        => 0

    		);
    		

			$this->db->insert('javh',$dataJVhead_EL);
    		$this->db->insert_batch('jurnal',$det_Jurnal_EL);
			$Update_JV_EL = $this->Jurnal_model->update_Nomor_Jurnal('110','JP');

			//
			//


	    }

		## ALI 2019-03-09 ##
  		## Piutang Cabang ##
  		//$Tgl_Jurnal			= $this->input->post('tglreceive');

		$header = $this->Internalpo_model->find_data('trans_internalpo_header',$no_mutasi,'no_mutasi');
		$no_do = $header->no_do;
		$no_so = $header->no_so;
		$kode_Pengirim = $header->kdcab_tujuan;
		$kode_Penerima = $header->kdcab_asal;
		$cabang_Pengirim = $header->cabang_tujuan;
		$cabang_Penerima = $header->cabang_asal;

  		$Periode_Receive	= date('Y-m',strtotime($Tgl_Jurnal));
  		$Bulan_Rec			= date('n',strtotime($Tgl_Jurnal));
  		$Tahun_Rec			= date('Y',strtotime($Tgl_Jurnal));

  		$Periode_Sekarang	= date('Y-m');
  		$Tgl_Sekarang		= date('Y-m-d');
  		$Bulan_Now			= date('n');
  		$Tahun_Now			= date('Y');
  		$Beda_Bulan			= (($Tahun_Now - $Tahun_Rec) * 12 ) + ($Bulan_Now - $Bulan_Rec);
  		//echo"Hasil = (".$Tahun_Now." - ".$Tahun_Rec.") * 12) + (".$Bulan_Now." - ".$Bulan_Rec.")";exit;
  		$AR_Cabang			= array();
  		$Saldo_Awal			= $Kredit	= $Saldo_Akhir	= 0;
  		$Debet				= $totalsemua;

  		for($x=0;$x<($Beda_Bulan + 1);$x++){
  			$Tgl_Baru		= date('Y-m-d',mktime(0,0,0,$Bulan_Rec + $x,1,$Tahun_Rec));
  			if($x > 0){
  				$Saldo_Awal	= $Saldo_Akhir;
  				$Debet		= $Kredit	= 0;
  			}
  			$Saldo_Akhir	= $Saldo_Awal + $Debet - $Kredit;
  			$AR_Cabang[$x]		= array(
  				'no_po'			=> $no_mutasi,
  				'tgl_receive'	=> $Tgl_Jurnal,
  				'no_do'			=> $no_do,
  				'id_supplier'	=> $kode_Pengirim,
  				'nm_supplier'	=> $cabang_Pengirim,
				'kdcab_pengirim'=> $kode_Pengirim,
				'kdcab_penerima'=> $kode_Penerima,
				'pengirim'      => $cabang_Pengirim,
				'penerima'      => $cabang_Penerima,
  				'bln'			=> date('n',strtotime($Tgl_Baru)),
  				'thn'			=> date('Y',strtotime($Tgl_Baru)),
  				'saldo_awal'	=> $Saldo_Awal,
  				'debet'			=> $Debet,
  				'kredit'		=> $Kredit,
  				'saldo_akhir'	=> $Saldo_Akhir,
  				'kdcab'			=> $kode_Pengirim
  			);
  		}

  		$this->db->insert_batch('ar_mutasi',$AR_Cabang);


    if ($this->db->trans_status() === FALSE)
    {
        $this->db->trans_rollback();
        $param = array(
        'save' => 0,
        'msg' => "GAGAL, simpan data..!!!"
        );
    }
    else
    {

	$this->db->trans_commit();
	$param = array(
        'save' => 1,
        'msg' => "SUKSES, simpan data..!!!"
        );
    }
    echo json_encode($param);
  }


  function save_jurnal(){

	  $datapost = $this->input->post();

	   // print_r ($datapost);
	   // exit;


		$Arr_Return	= array();
		if($this->input->post()){
			//echo"<pre>";print_r($this->input->post());exit;
			$session 				= $this->session->userdata('app_session');
			//echo"<pre>";print_r($session);exit;
			$Bayar_Ke				= $this->input->post('bayar_kepada');
			$No_Mutasi				= $this->input->post('nomutasi');
			$No_COA					= $this->input->post('no_perkiraan');
			$No_COA2				= $this->input->post('no_perkiraanpengirim');
			$Total_bayar			= round($this->input->post('total_bayar'));
			$Keterangan				= strtoupper($this->input->post('descr'));
			$dataDet				= $this->input->post('dataDet');
			//$Grand_Total			= str_replace(',','',$this->input->post('grand_tot'));
			// $Tgl_Jurnal				= date('Y-m-d');
			$Tgl_Jurnal				= $this->input->post('tgl_bayar');
			$Cabang_Bayar			= $session['kdcab'];
			$Cabang_Dibayar			= $this->input->post('cabangdibayar');
			$this->db->trans_begin();
			## JURNAL BUK ##
			$Tipe_Bayar		= 'Transfer';
			if(substr($No_COA,0,4)=='1101'){
				$Jenis_Pay	= 'KAS';
			}else{
				$Jenis_Pay	= 'BANK';
			}
			$Nomor_BUK		= $this->Jurnal_model->get_Nomor_Jurnal_BUK($Cabang_Bayar,$Tgl_Jurnal,$Jenis_Pay);
			// echo"Cabang : ".$Cabang_Bayar." Jenis_Pay : ".$Jenis_Pay." Nomor_BUK : ".$Nomor_BUK;exit;
			$Update_BUK 	= $this->Jurnal_model->update_Nomor_Jurnal_BUK($Cabang_Bayar,$Jenis_Pay);
			$Nomor_BUM		= $this->Jurnal_model->get_Nomor_Jurnal_BUM($Cabang_Dibayar,$Tgl_Jurnal);
			$Update_BUM 	= $this->Jurnal_model->update_Nomor_Jurnal($Cabang_Dibayar,'BUM');

			## COA ##
			$Coa_Piutang	= $this->Jurnal_model->get_COA_Piutang($Cabang_Bayar);

			$pengirim       = $this->input->post('cabangdibayar');
			$penerima       = $this->input->post('cabangbayar');

			#JOGJA-JKT  HUTANG = JKT-JOGJA
			if($pengirim =='101' && $penerima =='102' ){
			$Coa_Hutang		= '2105-01-03';
			$Coa_Piutang	= '1104-01-01';
			}
			#JOGJA-SBY  HUTANG = SBY-JOGJA
			if($pengirim =='101' && $penerima =='103' ){
			$Coa_Hutang		= '2105-01-06';
			$Coa_Piutang	= '1104-01-02';
			}
			#JKT-JOGJA   HUTANG = JOGJA-JKT
			if($pengirim =='102' && $penerima =='101' ){
			$Coa_Hutang		= '2105-01-01';
			$Coa_Piutang	= '1104-01-01';
			}
			#JKT-SBY     HUTANG = SBY-JKT
			if($pengirim =='102' && $penerima =='103' ){
			$Coa_Hutang		= '2105-01-05';
			$Coa_Piutang	= '1104-01-02';
			}
			#SBY-JOGJA   HUTANG = JOGJA-SBY
			if($pengirim =='103' && $penerima =='101' ){
			$Coa_Hutang		= '2105-01-02';
			$Coa_Piutang	= '1104-01-02';
			}
			#SBY-JKT    HUTANG = JKT-SBY
			if($pengirim =='103' && $penerima =='102' ){
			$Coa_Hutang		= '2105-01-04';
			$Coa_Piutang	= '1104-01-01';
			}



			$det_Cabang		= $this->db->get_where('pastibisa_tb_cabang',array('nocab'=>$Cabang_Bayar))->result();

			$Header_Payment	= array(
				'jurnalid'		=> $Nomor_BUK,
				'datet'			=> $Tgl_Jurnal,
				'kdcab'			=> $Cabang_Bayar,
				'no_perkiraan'	=> $No_COA,
				'no_perkiraanpengirim'	=> $No_COA2,
				'total'			=> $Total_bayar,
				'descr'			=> $Keterangan,
				'bum_pengirim'		=> $Nomor_BUM,
				'created_date'	=> date('Y-m-d H:i:s'),
				'created_by'	=> $session['id_user']
			);



			$Header_BUK		= array(
				'nomor'			=> $Nomor_BUK,
				'tgl'			=> $Tgl_Jurnal,
				'jml'			=> $Total_bayar,
				'kdcab'			=> $Cabang_Bayar,
				'jenis_reff'	    => $Tipe_Bayar,
				'no_reff'		=> '-',
				'bayar_kepada'	=> $Bayar_Ke,
				'jenis_ap'		=> 'V'
			);
			$Header_BUM = array(
				'nomor'         => $Nomor_BUM,
				'kd_pembayaran' => $Nomor_BUK,
				'tgl'           => $Tgl_Jurnal,
				'jml'           => $Total_bayar,
				'kdcab'         => $Cabang_Dibayar,
				'jenis_reff'    => 'TRANSFER',
				'no_reff'       => '-',
				'terima_dari'   => $det_Cabang[0]->cabang,
				'valid'         => 1,
				'tgl_valid'     => $Tgl_Jurnal,
				'user_id'       => $session['id_user']
			);
			$Detail_BUK			= array();
			$Detail_BUM			= array();
			$Detail_BUM[0] 		= array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Jurnal,
				'tipe'          => 'BUM',
				'no_perkiraan'  => $No_COA2,
				'keterangan'    => 'Pembayaran Hutang Cabang#'.$det_Cabang[0]->cabang,
				'no_reff'       => '-',
				'debet'         => $Total_bayar,
				'kredit'        => 0
			);
			$Detail_BUM[1] 		= array(
				'nomor'         => $Nomor_BUM,
				'tanggal'       => $Tgl_Jurnal,
				'tipe'          => 'BUM',
				'no_perkiraan'  => $Coa_Piutang,
				'keterangan'    => 'Pembayaran Hutang Cabang#'.$det_Cabang[0]->cabang,
				'no_reff'       => '-',
				'debet'         => 0,
				'kredit'        => $Total_bayar
			);


					$Cek_Data			= $this->db->get_where('ar_mutasi',array('no_po'=>$No_Mutasi))->result();
					if($Cek_Data){
						$Saldo_Awal		= $Cek_Data[0]->saldo_awal;
						$Debet			= $Cek_Data[0]->debet;
						$Kredit			= $Cek_Data[0]->kredit + $Total_bayar;
						$Saldo_Akhir	= $Saldo_Awal + $Debet - $Kredit;
						$this->db->update('ar_mutasi',array('kredit'=>$Kredit,'saldo_akhir'=>$Saldo_Akhir),array('no_po'=>$No_Mutasi));
					}



			$Detail_BUK[0]		= array(
				  'nomor'         => $Nomor_BUK,
				  'tanggal'       => $Tgl_Jurnal,
				  'tipe'          => 'BUK',
				  'no_perkiraan'  => $Coa_Hutang,
				  'keterangan'    => $Keterangan,
				  'no_reff'       => '-',
				  'debet'         => $Total_bayar,
				  'kredit'        => 0

			);

			$Detail_BUK[1]		= array(
				  'nomor'         => $Nomor_BUK,
				  'tanggal'       => $Tgl_Jurnal,
				  'tipe'          => 'BUK',
				  'no_perkiraan'  => $No_COA,
				  'keterangan'    => $Keterangan,
				  'no_reff'       => '-',
				  'debet'         => 0,
				  'kredit'        => $Total_bayar

			);


			## BUK  ##
			$this->db->insert('japh',$Header_BUK);
			$this->db->insert_batch('jurnal',$Detail_BUK);
			## BUM ##
			$this->db->insert('jarh',$Header_BUM);
			$this->db->insert_batch('jurnal',$Detail_BUM);




			## PAYMENT
			$this->db->insert('pembayaran_mutasi',$Header_Payment);

			$this->db->where(array('no_mutasi' => $No_Mutasi));
            $this->db->update('trans_internalpo_header',array('status_hutang'=>'LUNAS'));


			$this->db->trans_complete();
			if ($this->db->trans_status() === false) {
				$this->db->trans_rollback();
				$Arr_Return		= array(
					'status'		=> 2,
					'pesan'			=> 'Save Process Failed. Please Try Again...'
			   );
			} else {
				$this->db->trans_commit();
				$Arr_Return		= array(
					'status'		=> 1,
					'pesan'			=> 'Save Process Success. Thank You & Have A Nice Day....'
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

	function viewDet(){
		$this->load->view('internal/viewDet');
	}

	public function printRecv(){
		$no_mutasi		= $this->uri->segment(3);
		$cabang			= $this->uri->segment(4);

		$data_url		= base_url();
		$Split_Beda		= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$data	= array(
			'no_mutasi' => $no_mutasi,
			'cabang'	=> $cabang,
			'nama_beda'	=> $Nama_Beda
		);

		$this->load->view('internal/print_recv', $data);
	}
	
	function set_cancel_so(){
        $noso = $this->uri->segment(3);
        if(!empty($noso)){
            $kdcab = substr($noso,0,3);
            $session = $this->session->userdata('app_session');
           $this->db->trans_begin();
           $getitemso = $this->Salesorder_model->get_data(array('no_so'=>$noso),'trans_so_detail_internal');
		   
		   $i=0;		   
           foreach($getitemso as $k=>$v){
                //Update QTY_AVL
                $keycek = array('kdcab'=>$kdcab,'id_barang'=>$v->id_barang);
                $stok_avl = $this->Salesorder_model->cek_data($keycek,'barang_stock');
                $this->db->where($keycek);
                $this->db->update('barang_stock',array('qty_avl'=>$stok_avl->qty_avl+$v->qty_booked));
                //Update QTY_AVL
				
		        
            $tipe           = 'IN';
            $jenis_trans    = 'CANCEL SO';
            $qty_avl_new    = $stok_avl->qty_avl + $v->qty_booked;
			
					$id_st 			    = $this->Salesorder_model->gen_st($this->auth->user_cab()).$i;
  					$data_adj_trans 	= array(
  						'id_st'				    => $id_st,
  						'tipe'				    => 'IN',
  						'jenis_trans'		  => 'CANCEL SO INTERNAL',
  						'noreff'		  	  => $noso,
  						'id_barang'			  => $v->id_barang,
  						'nm_barang'			  => $v->nm_barang,
  						'kdcab'			  	  => $this->auth->user_cab(),
  						'date_stock'		  => date('Y-m-d H:i:s'),
  						'qty'			      => $v->qty_booked,
  						'nilai_barang'	      => $v->harga,
  						'notes'			  	  => 'CANCEL SO',
  						'qty_stock_awal'	=> $stok_avl->qty_stock,
  						'qty_avl_awal' 		=> $stok_avl->qty_avl,
  						'qty_stock_akhir'	=> $stok_avl->qty_stock,
  						'qty_avl_akhir' 	=> $qty_avl_new
  					);
  					$this->Trans_avl_model->insert($data_adj_trans);				
           
		   $i++;
		   }
		   
		   
           $today = date('Y-m-d H:i:s');
           $this->db->where(array('no_so'=>$noso));
           $this->db->update('trans_internalpo_header', array('status_mutasi'=>'CANCEL', 'modified_on'=>$today));
		   
		   $this->db->where(array('no_so'=>$noso));
           $this->db->update('trans_so_header_internal', array('stsorder'=>'CANCEL', 'modified_on'=>$today));
            if ($this->db->trans_status() === FALSE){
                $this->db->trans_rollback();
                $param['cancel'] = 0;
            }else{
                $this->db->trans_commit();
                $param['cancel'] = 1;
            }
        }
        redirect('internal/po');
    }

}

?>
