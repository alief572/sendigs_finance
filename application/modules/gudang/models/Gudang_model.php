<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Gudang_model extends BF_Model{

    public function __construct(){
        parent::__construct();
    }

    //==========================================================================================================================
	//===================================================REQUEST SUBGUDANG======================================================
	//==========================================================================================================================

	public function index(){
		$pusat				  = $this->db->query("SELECT * FROM warehouse")->result_array();
	  $uri_tanda			= $this->uri->segment(3);
		$data = array(
			'title'			=> 'Indeks Of Mutasi Material',
			'action'		=> 'index',
			'pusat'			=> $pusat,
			'uri_tanda'		=> $uri_tanda
		);
		history('View Mutasi Material');
    $this->template->render('index', $data);
	}

  public function modal_mutasi(){
		$gudang_before 	= $this->uri->segment(3);
		$gudang_after 	= $this->uri->segment(4);
    $session 		  = $this->session->userdata('app_session');

    $this->db->where('created_by', $session['username']);
    $this->db->delete('temp_server_side');

		$sql 	= "SELECT b.* FROM warehouse_stock b WHERE b.id_gudang = '".$gudang_before."' ";
		$result	= $this->db->query($sql)->result_array();

		$data = array(
			'gudang_before' => $gudang_before,
			'gudang_after' 	=> $gudang_after,
			'result' 		    => $result
		);
		$this->load->view('modal_mutasi', $data);
	}

  public function get_gudang_tujuan(){
    $id 	= $this->input->post('id');
		$sqlDist	= "SELECT * FROM warehouse WHERE id <> '".$id."'";
		$restDist	= $this->db->query($sqlDist)->result_array();

		$option	= "<option value='0'>Select Warehouse</option>";
		foreach($restDist AS $val => $valx){
			$option .= "<option value='".$valx['id']."'>".$valx['nm_gudang']."</option>";
		}

		$ArrJson	= array(
			'option' => $option
		);
		echo json_encode($ArrJson);
	}

  public function modal_detail_adjustment(){
		$kode_trans     = $this->uri->segment(3);
		$tanda     = $this->uri->segment(4);

		$sql 		= "SELECT * FROM warehouse_adjustment_detail WHERE kode_trans='".$kode_trans."' ";
		$result		= $this->db->query($sql)->result_array();

		$sql_header 		= "SELECT * FROM warehouse_adjustment WHERE kode_trans='".$kode_trans."' ";
		$result_header		= $this->db->query($sql_header)->result();

		$data = array(
			'result' 	=> $result,
			'tanda' 	=> $tanda,
			'checked' 	=> $result_header[0]->checked,
			'kode_trans'=> $result_header[0]->kode_trans,
			'no_po' 	=> $result_header[0]->no_ipp,
			'dated' 	=> date('ymdhis', strtotime($result_header[0]->created_date)),
			'resv' 		=> date('d F Y', strtotime($result_header[0]->created_date))

		);

		$this->load->view('modal_detail', $data);
	}


  public function print_request(){
		$kode_trans     = $this->uri->segment(3);
		$check     		= $this->uri->segment(4);

		$session 		  = $this->session->userdata('app_session');
		$printby		= $session['username'];
		$koneksi		= akses_server();

		$sroot 		= $_SERVER['DOCUMENT_ROOT'];
		include 'plusPurchaseOrder.php';

		$data_url		= base_url();
		$Split_Beda		= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];
		// $okeH  			= $this->session->userdata("ses_username");

		history('Print Request Material '.$kode_trans);

		print_request_material($Nama_Beda, $kode_trans, $koneksi, $printby, $check);
	}

  public function get_data_json_mutasi_material(){

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_mutasi_material(
			$requestData['tanda'],
			$requestData['uri_tanda'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		$urut1  = 1;
        $urut2  = 0;
		$uri_tanda = $requestData['uri_tanda'];
		foreach($query->result_array() as $row)
		{
			$total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if($asc_desc == 'desc')
            {
                $nomor = $urut1 + $start_dari;
            }
            if($asc_desc == 'asc')
            {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$nestedData[]	= "<div align='center'>".$row['kode_trans']."</div>";
			$nestedData[]	= "<div align='left'>".get_name('warehouse', 'nm_gudang', 'id', $row['id_gudang_dari'])."</div>";
			$nestedData[]	= "<div align='left'>".get_name('warehouse', 'nm_gudang', 'id', $row['id_gudang_ke'])."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['jumlah_mat'],2)."</div>";
			$nestedData[]	= "<div align='left'>".$row['created_by']."</div>";
			$nestedData[]	= "<div align='left'>".date('d-M-Y H:i:s', strtotime($row['created_date']))."</div>";
				$plus	= "";

				$print	= "&nbsp;<a href='".base_url('gudang/print_request/'.$row['kode_trans'])."' target='_blank' class='btn btn-sm btn-warning' title='Print Permintaan'><i class='fa fa-print'></i></a>";
				if(!empty($uri_tanda)){
					if($row['checked'] == 'N'){
						$plus	= "&nbsp;<button type='button' class='btn btn-sm btn-info check' title='Konfirmasi Permintaan' data-kode_trans='".$row['kode_trans']."'><i class='fa fa-check'></i></button>";
					}
				}

			$nestedData[]	= "<div align='left'>
									<button type='button' class='btn btn-sm btn-primary detailAjust' data-tanda='request' title='View Permintaan' data-kode_trans='".$row['kode_trans']."'><i class='fa fa-eye'></i></button>
                                    ".$print."
									".$plus."
									</div>";
			$data[] = $nestedData;
            $urut1++;
            $urut2++;
		}

		$json_data = array(
			"draw"            	=> intval( $requestData['draw'] ),
			"recordsTotal"    	=> intval( $totalData ),
			"recordsFiltered" 	=> intval( $totalFiltered ),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function query_data_json_mutasi_material($tanda, $uri_tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$where_tanda ='';
		if(!empty($tanda)){
			$where_tanda = " AND a.category = '".$tanda."' ";
		}

		$where_tanda2 ='';
		if(!empty($uri_tanda)){
			$where_tanda2 = " AND a.checked = 'N' ";
		}

		$sql = "
			SELECT
        (@row:=@row+1) AS nomor,
				a.*
			FROM
				warehouse_adjustment a,
        (SELECT @row:=0) r
		    WHERE 1=1
				".$where_tanda."
				".$where_tanda2."
			AND(
				a.no_ipp LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.kd_gudang_ke LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.kd_gudang_dari LIKE '%".$this->db->escape_like_str($like_value)."%'
        OR a.kode_trans LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'kode_trans',
      2 => 'id_gudang_dari',
      3 => 'id_gudang_ke',
      4 => 'jumlah_mat',
      5 => 'created_by',
      6 => 'created_date'
		);

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  //MODAL Mutasi
  public function process_mutasi(){
    $data 			    = $this->input->post();
		$session 		    = $this->session->userdata('app_session');

		// $detail			    = $data['detail'];
    $detail         = $this->db->query("SELECT * FROM temp_server_side WHERE category='mutasi material' AND created_by='".$session['username']."'")->result_array();
		$gudang_before	= $data['gudang_before'];
		$gudang_after	  = $data['gudang_after'];
		$Ym 			      = date('ym');

		//pengurutan kode
		$srcMtr			  = "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRS".$Ym."%' ";
		$numrowMtr		= $this->db->query($srcMtr)->num_rows();
		$resultMtr		= $this->db->query($srcMtr)->result_array();
		$angkaUrut2		= $resultMtr[0]['maxP'];
		$urutan2		  = (int)substr($angkaUrut2, 7, 4);
		$urutan2++;
		$urut2			   = sprintf('%04s',$urutan2);
		$kode_trans		= "TRS".$Ym.$urut2;

    $ArrInsertH = array();
    $ArrDeatilAdj = array();
    $ArrStock = array();
    $ArrHist = array();
    $ArrStockSup = array();
    $ArrHistSub = array();
    $ArrStockSupIns = array();
    $ArrHistSubIns = array();

		$SUM_MAT = 0;
		foreach($detail AS $val => $valx){
			$req_stock 	= str_replace(',','',$valx['stock']);
			if($req_stock > 0){
				$SUM_MAT += $req_stock;
				$gud_pusat	= "	SELECT b.* FROM warehouse_stock b WHERE b.id = '".$valx['id_mat']."' AND b.id_gudang='".$gudang_before."'";
				$rest_pusat	= $this->db->query($gud_pusat)->result();

				//detail adjustmeny
				$ArrDeatilAdj[$val]['kode_trans'] 		= $kode_trans;
				$ArrDeatilAdj[$val]['id_material'] 		= $rest_pusat[0]->id_material;
				$ArrDeatilAdj[$val]['nm_material'] 		= $rest_pusat[0]->nm_material;
				$ArrDeatilAdj[$val]['qty_order'] 		 = $req_stock;
				$ArrDeatilAdj[$val]['qty_oke'] 			  = $req_stock;
				$ArrDeatilAdj[$val]['keterangan'] 		= strtolower($valx['ket']);
				$ArrDeatilAdj[$val]['update_by'] 		 = $session['username'];
				$ArrDeatilAdj[$val]['update_date'] 		= date('Y-m-d H:i:s');

        //update Stock
        $ArrStock[$val]['id'] 			     = $rest_pusat[0]->id;
				$ArrStock[$val]['qty_stock'] 	   = $rest_pusat[0]->qty_stock - $req_stock;
				$ArrStock[$val]['update_by'] 	   = $session['username'];
				$ArrStock[$val]['update_date'] 	 = date('Y-m-d H:i:s');

        $ArrHist[$val]['id_material'] 		= $rest_pusat[0]->id_material;
				$ArrHist[$val]['idmaterial'] 		  = $rest_pusat[0]->idmaterial;
				$ArrHist[$val]['nm_material'] 		= $rest_pusat[0]->nm_material;
				$ArrHist[$val]['id_gudang'] 		= $gudang_before;
				$ArrHist[$val]['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', $gudang_before);
				$ArrHist[$val]['id_gudang_dari'] 	= $gudang_before;
				$ArrHist[$val]['kd_gudang_dari'] 	= get_name('warehouse', 'kd_gudang', 'id', $gudang_before);
				$ArrHist[$val]['id_gudang_ke'] 		= $gudang_after;
				$ArrHist[$val]['kd_gudang_ke'] 		= get_name('warehouse', 'kd_gudang', 'id', $gudang_after);
				$ArrHist[$val]['qty_stock_awal'] 	= $rest_pusat[0]->qty_stock;
				$ArrHist[$val]['qty_stock_akhir'] 	= $rest_pusat[0]->qty_stock - $req_stock;
				$ArrHist[$val]['qty_booking_awal'] 	= $rest_pusat[0]->qty_booking;
				$ArrHist[$val]['qty_booking_akhir'] = $rest_pusat[0]->qty_booking;
				$ArrHist[$val]['qty_rusak_awal'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$val]['qty_rusak_akhir'] 	= $rest_pusat[0]->qty_rusak;
				$ArrHist[$val]['no_ipp'] 			= $kode_trans;
				$ArrHist[$val]['jumlah_mat'] 		= $req_stock;
				$ArrHist[$val]['ket'] 				= 'pengurangan gudang';
				$ArrHist[$val]['update_by'] 		= $session['username'];
				$ArrHist[$val]['update_date'] 		= date('Y-m-d H:i:s');

        $gud_subgudang	= "SELECT a.* FROM  warehouse_stock a WHERE a.id_material = '".$rest_pusat[0]->id_material."' AND a.id_gudang='".$gudang_after."'";
        $rest_subgudang	= $this->db->query($gud_subgudang)->result();
				if(!empty($rest_subgudang)){
					//update stock sub gudang
					$ArrStockSup[$val]['id'] 			     = $rest_subgudang[0]->id;
					$ArrStockSup[$val]['qty_stock'] 	 = $rest_subgudang[0]->qty_stock + $req_stock;
					$ArrStockSup[$val]['update_by'] 	  = $session['username'];
					$ArrStockSup[$val]['update_date'] 	= date('Y-m-d H:i:s');

					$ArrHistSub[$val]['id_material'] 		= $rest_subgudang[0]->id_material;
					$ArrHistSub[$val]['idmaterial'] 		= $rest_subgudang[0]->idmaterial;
					$ArrHistSub[$val]['nm_material'] 		= $rest_subgudang[0]->nm_material;
					$ArrHistSub[$val]['id_gudang'] 			= $gudang_after;
					$ArrHistSub[$val]['kd_gudang'] 			= get_name('warehouse', 'kd_gudang', 'id', $gudang_after);
					$ArrHistSub[$val]['id_gudang_dari'] 	= $gudang_before;
					$ArrHistSub[$val]['kd_gudang_dari'] 	= get_name('warehouse', 'kd_gudang', 'id', $gudang_before);
					$ArrHistSub[$val]['id_gudang_ke'] 		= $gudang_after;
					$ArrHistSub[$val]['kd_gudang_ke'] 		= get_name('warehouse', 'kd_gudang', 'id', $gudang_after);
					$ArrHistSub[$val]['qty_stock_awal'] 	= $rest_subgudang[0]->qty_stock;
					$ArrHistSub[$val]['qty_stock_akhir'] 	= $rest_subgudang[0]->qty_stock + $req_stock;
					$ArrHistSub[$val]['qty_booking_awal'] 	= $rest_subgudang[0]->qty_booking;
					$ArrHistSub[$val]['qty_booking_akhir'] 	= $rest_subgudang[0]->qty_booking;
					$ArrHistSub[$val]['qty_rusak_awal'] 	= $rest_subgudang[0]->qty_rusak;
					$ArrHistSub[$val]['qty_rusak_akhir'] 	= $rest_subgudang[0]->qty_rusak;
					$ArrHistSub[$val]['no_ipp'] 			  = $kode_trans;
					$ArrHistSub[$val]['jumlah_mat'] 		= $req_stock;
					$ArrHistSub[$val]['ket'] 				    = 'penambahan gudang';
					$ArrHistSub[$val]['update_by'] 			= $session['username'];
					$ArrHistSub[$val]['update_date'] 		= date('Y-m-d H:i:s');
				}

        if(empty($rest_subgudang)){
					$sql_mat	= "	SELECT a.* FROM ms_material a WHERE a.code_material = '".$rest_pusat[0]->id_material."' LIMIT 1";
					// echo $sql_mat; exit;
					$rest_mat	= $this->db->query($sql_mat)->result();
					//update stock sub gudang
					$ArrStockSupIns[$val]['id_material'] 	= $rest_mat[0]->code_material;
					$ArrStockSupIns[$val]['idmaterial'] 	= $rest_mat[0]->code_company;
					$ArrStockSupIns[$val]['nm_material'] 	= $rest_mat[0]->nm_material;
					$ArrStockSupIns[$val]['id_gudang'] 		= $gudang_after;
					$ArrStockSupIns[$val]['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', $gudang_after);
					$ArrStockSupIns[$val]['qty_stock'] 		= $req_stock;
					$ArrStockSupIns[$val]['update_by'] 		= $session['username'];
					$ArrStockSupIns[$val]['update_date'] 	= date('Y-m-d H:i:s');

					$ArrHistSubIns[$val]['id_material'] 	= $rest_mat[0]->code_material;
					$ArrHistSubIns[$val]['idmaterial'] 		= $rest_mat[0]->code_company;
					$ArrHistSubIns[$val]['nm_material'] 	= $rest_mat[0]->nm_material;
					$ArrHistSubIns[$val]['id_gudang'] 		= $gudang_after;
					$ArrHistSubIns[$val]['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', $gudang_after);
					$ArrHistSubIns[$val]['id_gudang_dari'] 	= $gudang_before;
					$ArrHistSubIns[$val]['kd_gudang_dari'] 	= get_name('warehouse', 'kd_gudang', 'id', $gudang_before);
					$ArrHistSubIns[$val]['id_gudang_ke'] 	= $gudang_after;
					$ArrHistSubIns[$val]['kd_gudang_ke'] 	= get_name('warehouse', 'kd_gudang', 'id', $gudang_after);
					$ArrHistSubIns[$val]['qty_stock_awal'] 	= 0;
					$ArrHistSubIns[$val]['qty_stock_akhir'] 	= $req_stock;
					$ArrHistSubIns[$val]['qty_booking_awal'] 	= 0;
					$ArrHistSubIns[$val]['qty_booking_akhir'] 	= 0;
					$ArrHistSubIns[$val]['qty_rusak_awal'] 		= 0;
					$ArrHistSubIns[$val]['qty_rusak_akhir'] 	= 0;
					$ArrHistSubIns[$val]['no_ipp'] 				= $kode_trans;
					$ArrHistSubIns[$val]['jumlah_mat'] 			= $req_stock;
					$ArrHistSubIns[$val]['ket'] 				= 'penambahan gudang (insert new)';
					$ArrHistSubIns[$val]['update_by'] 			= $session['username'];
					$ArrHistSubIns[$val]['update_date'] 		= date('Y-m-d H:i:s');
				}

			}
		}

		$ArrInsertH = array(
			'kode_trans' 		=> $kode_trans,
			'category' 			=> 'mutasi material',
			'jumlah_mat' 		=> $SUM_MAT,
			'id_gudang_dari' 	=> $gudang_before,
			'kd_gudang_dari' 	=> get_name('warehouse', 'kd_gudang', 'id', $gudang_before),
			'id_gudang_ke' 		=> $gudang_after,
			'kd_gudang_ke' 		=> get_name('warehouse', 'kd_gudang', 'id', $gudang_after),
			'created_by' 		=> $session['username'],
			'created_date' 		=> date('Y-m-d H:i:s')
		);

		// print_r($ArrInsertH);
		// print_r($ArrDeatilAdj);
    // print_r($ArrStock);
		// print_r($ArrHist);
    // print_r($ArrStockSup);
		// print_r($ArrHistSub);
    // print_r($ArrStockSupIns);
		// print_r($ArrHistSubIns);
		// exit;
		$this->db->trans_start();
			$this->db->insert('warehouse_adjustment', $ArrInsertH);
			$this->db->insert_batch('warehouse_adjustment_detail', $ArrDeatilAdj);

      $this->db->update_batch('warehouse_stock', $ArrStock, 'id');
			$this->db->insert_batch('warehouse_history', $ArrHist);

      if(!empty($ArrStockSup)){
				$this->db->update_batch('warehouse_stock', $ArrStockSup, 'id');
				$this->db->insert_batch('warehouse_history', $ArrHistSub);
			}
			if(!empty($ArrStockSupIns)){
				$this->db->insert_batch('warehouse_stock', $ArrStockSupIns);
				$this->db->insert_batch('warehouse_history', $ArrHistSubIns);
			}
		$this->db->trans_complete();


		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Save process failed. Please try again later ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Save process success. Thanks ...',
				'status'	=> 1
			);
			history("Mutasi material : ".$kode_trans);
		}
		echo json_encode($Arr_Data);
	}

  public function save_temp_mutasi(){
    $data 			    = $this->input->post();
		$session 		    = $this->session->userdata('app_session');

		$id			      = $data['id'];
		$req_pack	    = $data['req_pack'];
		$req_stock	  = $data['req_stock'];
    $ket_request	 = $data['ket_request'];

		$ArrInsertH = array(
			'category' 	=> 'mutasi material',
			'id_mat' 		=> $id,
			'pack'   	  => $req_pack,
			'stock' 	  => $req_stock,
			'ket' 		  => $ket_request,
			'created_by' 		=> $session['username'],
			'created_date' 	=> date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
      $this->db->where('id_mat', $id);
      $this->db->where('created_by', $session['username']);
      $this->db->delete('temp_server_side');

      $this->db->insert('temp_server_side', $ArrInsertH);
		$this->db->trans_complete();

	}

  public function get_data_json_modal_mutasi(){

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_modal_mutasi(
			$requestData['gudang_before'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];
    $session 		    = $this->session->userdata('app_session');
		$data	= array();
		$urut1  = 1;
    $urut2  = 0;
		foreach($query->result_array() as $row)
		{
			$total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if($asc_desc == 'asc')
            {
                $nomor = $urut1 + $start_dari;
            }
            if($asc_desc == 'desc')
            {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

			$nestedData 	= array();
      $konversi = get_konversi($row['id_material']);
      $pack = 0;
      if(!empty($row['qty_stock']) AND $row['qty_stock'] > 0 AND $konversi > 0){
        $pack = $row['qty_stock'] / $konversi;
      }

      $get_temp = $this->db->query("SELECT * FROM temp_server_side WHERE id_mat = '".$row['id']."' AND created_by = '".$session['username']."' ")->result();
      $pack2   = (!empty($get_temp))?$get_temp[0]->pack:'';
      $stock  = (!empty($get_temp))?$get_temp[0]->stock:'';
      $ket    = (!empty($get_temp))?$get_temp[0]->ket:'';

			$nestedData[]	= "<div align='center'>".$nomor."<input type='hidden' name='detail[$nomor][id]' value='".$row['id']."' id='id_".$nomor."'></div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_material'])."</div>";
			$nestedData[]	= "<div align='right'>".number_format($pack,2)."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(get_name('ms_material', 'satuan_packing', 'code_material', $row['id_material']))."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['qty_stock'],2)."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(get_name('ms_material', 'unit', 'code_material', $row['id_material']))."</div>";
			$nestedData[]	= "<div align='left'>
                          <input type='hidden' name='detail[$nomor][konversi]' id='konversi_".$nomor."' value='".$konversi."'>
                          <input type='hidden' name='detail[$nomor][pack]' id='pack_".$nomor."' value='".$pack."'>
                          <input type='hidden' name='detail[$nomor][stock]' id='stock_".$nomor."' value='".$row['qty_stock']."'>
                          <input type='text' style='width:100%' name='detail[$nomor][req_pack]' data-no='$nomor' id='req_pack_".$nomor."' class='form-control input-sm text-right maskM pack' value='".$pack2."'>
                        </div>";
      $nestedData[]	= "<div align='left'><input type='text' style='width:100%' name='detail[$nomor][req_stock]' data-no='$nomor' id='req_stock_".$nomor."' class='form-control input-sm text-right maskM stock' value='".$stock."'></div>";
      $nestedData[]	= "<div align='left'><input type='text' style='width:100%' name='detail[$nomor][ket_request]' data-no='$nomor' id='ket_request_".$nomor."' class='form-control input-sm text-left ket' value='".$ket."'></div><script type='text/javascript'>$('.maskM').maskMoney();</script>";

			$data[] = $nestedData;
            $urut1++;
            $urut2++;
		}

		$json_data = array(
			"draw"            	=> intval( $requestData['draw'] ),
			"recordsTotal"    	=> intval( $totalData ),
			"recordsFiltered" 	=> intval( $totalFiltered ),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function query_data_json_modal_mutasi($gudang_before, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$where_gudang_before ='';
		if(!empty($gudang_before)){
			$where_gudang_before = " AND a.id_gudang = '".$gudang_before."' ";
		}

		$sql = "
			SELECT
        (@row:=@row+1) AS nomor,
				a.*
			FROM
				warehouse_stock a,
				(SELECT @row:=0) r
		    WHERE 1=1
				".$where_gudang_before."
			AND(
				a.idmaterial LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.nm_material LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.id_material LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'nm_material'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}


}
