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
                 'Trans_stock/Trans_stock_model'
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
			'user' => 'root',
			'pass' => 'Annabell2018',
			'db'   => 'imperial',
			'host' => 'localhost'
		);



		echo json_encode(
			SSP::complex ( $_POST, $sql_details, $table, $primaryKey, $columns, null, $WHERE )
		);
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
  			$stok_cabang 	= $this->Internalpo_model->get_data(array('kdcab'=>$Cabang_User,'qty_avl >'=>0),'barang_stock');
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
        //$detail = $this->db->where(array('keterangan'=>$no_mutasi,'MID(trans_so_header.no_so,5,3)'=>'SOI'))->join('trans_so_detail','trans_so_detail.no_so = trans_so_header.no_so', 'left')->get('trans_so_header')->row();
    		$detail2 = $this->Detailinternalpo_model->find_all_by(array('no_mutasi' => $no_mutasi));
    		$customer = $this->Customer_model->find_all_by(array('deleted'=>0,'kdcab'=>$session['kdcab']));

            //print_r($detail); exit;
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

        //print_r($detail); exit;
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
		 //print_r($data);

		 //exit;


        $tglso              = date('Y-m-d');
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
		$ongkoskirim        = $this->input->post('biayakirim');
        $dppso              = $this->input->post('grandtotal');
        $totalso            = $this->input->post('grandtotal');


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
		// exit;


        $this->db->trans_begin();

        $this->db->insert('trans_so_header',$dataheaderso);



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

            $this->db->insert('trans_so_detail',$dataitem);

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
		$stok_cabang 	= $this->Internalpo_model->get_data(array('kdcab'=>$Cabang,'qty_avl >'=>0),'barang_stock');
		$data			= array(
			'rows_data'		=> $stok_cabang
		);
		//echo"<pre>";print_r($stok_cabang);exit;
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
            'tgl_mutasi'        => date('Y-m-d'),
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
             $keycek = array('kdcab'=>$session['kdcab'],'id_barang'=>$this->input->post('kode_produk')[$i]);
             $stok_avl = $this->Internalpo_model->cek_data($keycek,'barang_stock');
             $this->db->where($keycek);
             $this->db->update('barang_stock',array('qty_avl'=>$stok_avl->qty_avl-$this->input->post('qty_mutasi')[$i]));
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
	// exit;
	
    $no_mutasi = $this->input->post('no_mutasi');
	$pengirim  = $this->input->post('cabang_asal');
	$pemesan   = $this->input->post('cabang_tujuan');
	
    $i=0;
    $this->db->trans_begin();
    foreach($_POST['data'] as $d){


  		$idbarang         = $d[id_barang_rec_mutasi];
  		$nmbarang         = $d[nm_barang];
  		$satuan      	    = $d[satuan];
  		$jenis            = $d[jenis];
  		$qty              = $d[qty_receive];

      $ambil_so = $this->db->where(array('keterangan'=>$no_mutasi,'MID(trans_so_header.no_so,5,3)'=>'SOI','id_barang'=>$idbarang))->join('trans_so_detail','trans_so_detail.no_so = trans_so_header.no_so', 'left')->get('trans_so_header')->row();
      $ambil_barang = $this->db->where(array('id_barang'=>$idbarang,'kdcab'=>$this->auth->user_cab()))->get('barang_stock')->row();

      $qty_stock_sebelum = $ambil_barang->qty_stock;
      $qty_avl_sebelum = $ambil_barang->qty_avl;
      $qty_stock_sesudah = $ambil_barang->qty_stock+$qty;
      $qty_avl_sesudah = $ambil_barang->qty_avl+$qty;
      $landed_cost_sebelum = $ambil_barang->landed_cost;
      $landed_cost_sesudah = (($qty_stock_sebelum*$landed_cost_sebelum)+($qty*$ambil_so->harga))/($qty_stock_sebelum+$qty);

  			$dataitem = array(
                'qty_received'        => $qty,
                'received_on'           =>date("Y-m-d H:i:s"),
                'received_by'          =>$this->auth->user_id()
              );

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
  				'notes'				=> 'MUTASI',
  				'qty_stock_awal'	=> $qty_stock_sebelum,
  				'qty_avl_awal' 		=> $qty_avl_sebelum,
  				'qty_stock_akhir'	=> $qty_stock_sesudah,
  				'qty_avl_akhir' 	=> $qty_avl_sesudah
  			);
  			$this->Trans_stock_model->insert($data_adj_trans);


        $i++;



    }
	
	
	
	
	
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
	
	$this->db->where(array('no_so' => $nomor_so));
    $this->db->update('trans_internalpo_header',array('status_receiving'=>'CLOSE'));
	  
	  
	$Tgl_Jurnal = $this->input->post('tgl_receive');
	$total		= $ambil_so->total;
	$totalsemua = $ambil_so->total + $ambil_so->biaya_kirim;
	$biayakirim = $ambil_so->biaya_kirim;
	
	
	## JURNAL PERSEDIAAN ##
		#PENERIMA#
	    ## JOGJA-JKT ##		
  		if($pengirim =='101' && $penerima =='102' ){
  			## JURNAL CABANG ##
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
    			  'no_perkiraan'  => '6101-01-15',
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
			
			// print_r($Update_JV);
			// exit;
			## JURNAL PENGIRIM ##
    		
    		$Nomor_JV_Pengirim				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($pengirim,$Tgl_Jurnal);
    		$Keterangan_JV_Pengirim			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVheadPengirim = array(
    			'nomor' 	    	=> $Nomor_JV_Pengirim,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $pengirim,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_Pengirim,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

    		$det_JurnalPengirim				= array();
    		$det_JurnalPengirim[0]			= array(
    			  'nomor'         => $Nomor_JV_Pengirim,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1104-07-01',
    			  'keterangan'    => $Keterangan_JV_Pengirim,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $totalsemua,
    			  'kredit'        => 0

    		);
    		$det_JurnalPengirim[1]			= array(
    			  'nomor'         => $Nomor_JV_Pengirim,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6101-01-15',
    			  'keterangan'    => $Keterangan_JV_Pengirim,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => 0,
    			  'kredit'        => $biayakirim

    		);
    		$det_JurnalPengirim[2]			= array(
    				  'nomor'         => $Nomor_JV_Pengirim,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '4201-01-01',
    				  'keterangan'    => $Keterangan_JV_Pengirim,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $total

    			);
    		
			$this->db->insert('javh',$dataJVheadPengirim);
    		$this->db->insert_batch('jurnal',$det_JurnalPengirim);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($pengirim,'JP');
    		## END JURNAL  PENGIRIM##
	    }
	
		
		## JURNAL PERSEDIAAN ##
		#PENERIMA#
	    ## JOGJA-JKT ##		
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
    			  'no_perkiraan'  => '6101-01-15',
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
			
			print_r($Update_JV);
			exit;
			## JURNAL PENGIRIM ##
    		
    		$Nomor_JV_Pengirim				= $this->Jurnal_model->get_Nomor_Jurnal_Pembelian($pengirim,$Tgl_Jurnal);
    		$Keterangan_JV_Pengirim			= 'Receive#SOI'.$no_mutasi.'#'.$this->input->post('cabang_pemesan');

    		$dataJVheadPengirim = array(
    			'nomor' 	    	=> $Nomor_JV_Pengirim,
    			'tgl'	         	=> $Tgl_Jurnal,
    			'jml'	          	=> $totalsemua,
    			'koreksi_no'		=> '',
    			'kdcab'				=> $pengirim,
    			'jenis'			    => 'V',
    			'keterangan' 		=> $Keterangan_JV_Pengirim,
    			'bulan'				=> date('n'),
    			'tahun'				=> date('Y'),
    			'user_id'			=> $session['id_user'],
    			'memo'			    => '',
    			'tgl_jvkoreksi'		=> $Tgl_Jurnal,
    			'ho_valid'			=> ''
    		);

			//piutang antar cabang
				//biaya kirim
				//pendapatan kotor non ppn
    		$det_JurnalPengirim				= array();
    		$det_JurnalPengirim[0]			= array(
    			  'nomor'         => $Nomor_JV_Pengirim,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '1104-07-01',
    			  'keterangan'    => $Keterangan_JV_Pengirim,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => $totalsemua,
    			  'kredit'        => 0

    		);
    		$det_JurnalPengirim[1]			= array(
    			  'nomor'         => $Nomor_JV_Pengirim,
    			  'tanggal'       => $Tgl_Jurnal,
    			  'tipe'          => 'JV',
    			  'no_perkiraan'  => '6101-01-15',
    			  'keterangan'    => $Keterangan_JV_Pengirim,
    			  'no_reff'       => $no_mutasi,
    			  'debet'         => 0,
    			  'kredit'        => $biayakirim

    		);
    		$det_JurnalPengirim[2]			= array(
    				  'nomor'         => $Nomor_JV_Pengirim,
    				  'tanggal'       => $Tgl_Jurnal,
    				  'tipe'          => 'JV',
    				  'no_perkiraan'  => '4201-01-01',
    				  'keterangan'    => $Keterangan_JV_Pengirim,
    				  'no_reff'       => $no_mutasi,
    				  'debet'         => 0,
    				  'kredit'        => $total

    			);
    		
			$this->db->insert('javh',$dataJVheadPengirim);
    		$this->db->insert_batch('jurnal',$det_JurnalPengirim);
			$Update_JV = $this->Jurnal_model->update_Nomor_Jurnal($pengirim,'JP');
    		## END JURNAL  PENGIRIM##
	    }
	
        $param = array(
        'save' => 1,
        'msg' => "SUKSES, simpan data..!!!"
        );
    }
    echo json_encode($param);
  }




}

?>
