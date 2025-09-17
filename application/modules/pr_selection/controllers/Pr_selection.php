<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2021, Harboens
 *
 * This is controller for PR Selestion
 */

class Pr_selection extends Admin_Controller {

    //Permission 
    protected $viewPermission   = "Pr_selection.View";
    protected $addPermission    = "Pr_selection.Add";
    protected $managePermission = "Pr_selection.Manage";
    protected $deletePermission = "Pr_selection.Delete";
	protected $marketingPermission = "Pr_selection.Marketing";

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload','Image_lib'));
        $this->load->model(array('Purchase_order/Purchase_order_model',
		                         'Po_aset/Po_aset_model',
                                 'Pr_selection/Pr_selection_model','All/All_model' ));
        $this->template->title('Manage Data Quotation');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
//        $this->auth->restrict($this->viewPermission);
        // $data = $this->Pra_invoice_model->GetListSPK();
        // $this->template->set('results', $data);
        $this->template->title('Tabel Perbandingan');
        $this->template->render('index.php');
    } 	
	
	 public function list_rfq()
    {
//        $this->auth->restrict($this->viewPermission);
        // $data = $this->Pra_invoice_model->GetListSPK();
        // $this->template->set('results', $data);
        $this->template->title('List RFQ');
        $this->template->render('index_listrfq.php');
    } 	
	
	  public function po_aset()
    {
//        $this->auth->restrict($this->viewPermission);
        $data = $this->Po_aset_model->GetPrAsetSelection();
		$supplier  = $this->Purchase_order_model->get_data('ori_dev.supplier');
		
		$this->template->set('supplier', $supplier);
        $this->template->set('results', $data);
        $this->template->title('PO Aset');
        $this->template->render('po_aset.php');
    } 	
	
	public function list_po_aset() {
        $data = $this->Purchase_order_model->query_data_json_po('ASET','CLS');
        $this->template->set('results', $data);
        $this->template->title('List PO Aset');
        $this->template->render('list_po_aset');
    }

	public function list_perbandingan_rutin() {
        $data = $this->Purchase_order_model->query_data_json_po('RUTIN',"OPN','PRS");
        $this->template->set('results', $data);
        $this->template->title('List Perbandingan Rutin');
        $this->template->render('list_po_rutin');
    }

	public function list_perbandingan_nonrutin() {
        $data = $this->Purchase_order_model->query_data_json_po('NONRUTIN',"OPN','PRS");
        $this->template->set('results', $data);
        $this->template->title('List Perbandingan Non Rutin');
        $this->template->render('list_po_rutin');
    }

	public function list_po_rutin() {
        $data = $this->Purchase_order_model->query_data_json_po('RUTIN','CLS');
        $this->template->set('results', $data);
        $this->template->title('List PO Rutin');
        $this->template->render('list_po_rutin');
    }
	
	public function list_rfq_aset() {
        $data = $this->Purchase_order_model->query_data_json_po('ASET','CLS');
        $this->template->set('results', $data);
        $this->template->title('List PO Aset');
        $this->template->render('list_rfq_aset');
    }

	public function list_rfq_nonrutin() {
        $data = $this->Purchase_order_model->query_data_json_po('NONRUTIN','OPN');
        $this->template->set('results', $data);
        $this->template->title('List PO Non Rutin');
        $this->template->render('list_rfq_nonrutin');
    }
	
	public function list_rfq_rutin() {
        $data = $this->Purchase_order_model->query_data_json_po('RUTIN','OPN');
        $this->template->set('results', $data);
        $this->template->title('List PO Rutin');
        $this->template->render('list_rfq_rutin');
    }

	public function add_perbandingan(){
		
		$norfq = $this->uri->segment(3);
		$data = $this->Pr_selection_model->GetSupplier($norfq);

        $this->template->set('results', $data);
        $this->template->title('Add Perbandingan');
        $this->template->render('add_perbandingan');
	}
	
	public function save_perbandingan(){
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$no_rfq 		= $data['no_rfq'];
			$header 		= $data['Header'];
			$detail 		= $data['Detail'];
			// print_r($data);
			// exit;
			
			$ArrHeader = array();
			$ArrDetail = array();
			foreach($header AS $val => $valx){
				$ArrHeader[$val]['id'] 				= $valx['id'];
				$ArrHeader[$val]['lokasi'] 			= $valx['lokasi'];
				$ArrHeader[$val]['alamat_supplier'] = $valx['alamat'];
				$ArrHeader[$val]['sts_ajuan'] 		= 'PRS';
				$ArrHeader[$val]['sts_process'] 	= 'Y';
				$ArrHeader[$val]['updated_by'] 		= $this->session->userdata['app_session']['username'];
				$ArrHeader[$val]['updated_date'] 	= date('Y-m-d H:i:s');
			}
			
			foreach($detail AS $val => $valx){
				foreach($valx['detail'] AS $val2 => $valx2){
					$ArrDetail[$val.$val2]['id'] 				= $valx2['id'];
					$ArrDetail[$val.$val2]['price_ref_sup'] 	= str_replace(',','',$valx2['price_ref_sup']);
					$ArrDetail[$val.$val2]['tgl_dibutuhkan'] 	= $valx2['tgl_dibutuhkan'];
					$ArrDetail[$val.$val2]['lead_time'] 	= $valx2['leadtime'];
					$ArrDetail[$val.$val2]['keterangan'] 	= $valx2['keterangan'];
				}
			}
			$this->db->trans_start();
				$this->db->update_batch('tran_material_rfq_header', $ArrHeader, 'id');
				$this->db->update_batch('tran_material_rfq_detail', $ArrDetail, 'id');
				
			$this->db->trans_complete();

			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert data failed. Please try again later ...',
					'status'	=> 2
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert data success. Thanks ...',
					'status'	=> 1
				);
				//history('Create Table Perbandingan '.$no_rfq);
			}
			echo json_encode($Arr_Kembali);
	}
	
	public function pengajuan(){

			$no_rfq 	= $this->uri->segment(3);

			$sql 		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' GROUP BY a.id_material ORDER BY a.id ASC";
			$result		= $this->db->query($sql)->result_array();
			
			$sql_sup 		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' GROUP BY a.id_supplier ORDER BY a.id_supplier ASC";
			$supplier		= $this->db->query($sql_sup)->result_array();
			
			$data = array(
				'result' 	=> $result,
				'supplier' 	=> $supplier,
				'no_rfq' 	=> $no_rfq
			);

			$this->template->title('Pengajuan');
            $this->template->render('pengajuan', $data);

	}
	
	public function save_pengajuan(){ 
		$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$no_rfq 		= $data['no_rfq'];
			$detail 		= $data['detail'];

			$ArrDetail = array();
			foreach($detail AS $val => $valx){
				$sql_d 		= "SELECT id FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' AND a.id_material='".$valx['id_material']."' AND a.id_supplier='".$valx['id_supplier']."'";
				$dataT		= $this->db->query($sql_d)->result();
				$ArrDetail[$val]['id'] 			= $dataT[0]->id;		
				$ArrDetail[$val]['status'] 		= 'SETUJU';
				$ArrDetail[$val]['setuju_by'] 	= $this->session->userdata['app_session']['username'];
				$ArrDetail[$val]['setuju_date'] = date('Y-m-d H:i:s');
			}

			$ArrHeader = array(
				'sts_ajuan' => 'APV'
			);

			$this->db->trans_start();
				$this->db->update_batch('tran_material_rfq_detail', $ArrDetail, 'id');
				
				$this->db->where(array('no_rfq'=>$no_rfq));
				$this->db->update('tran_material_rfq_header', $ArrHeader);
			$this->db->trans_complete();

			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert purchase order data failed. Please try again later ...',
					'status'	=> 2
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert purchase order data success. Thanks ...',
					'status'	=> 1
				);
			}
			echo json_encode($Arr_Kembali);
	}

	public function index_approval()
    {
//        $this->auth->restrict($this->viewPermission);
        $this->template->title('List RFQ');
        $this->template->render('index_approval');
    }

	public function approval(){

			$no_rfq 	= $this->uri->segment(3);

			$sql 		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' GROUP BY a.id_material ORDER BY a.id ASC";
			$result		= $this->db->query($sql)->result_array();

			$sql_sup 		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' GROUP BY a.id_supplier ORDER BY a.id_supplier ASC";
			$supplier		= $this->db->query($sql_sup)->result_array();

			$data = array(
				'result' 	=> $result,
				'supplier' 	=> $supplier,
				'no_rfq' 	=> $no_rfq
			);

			$this->template->title('Approval');
            $this->template->render('approval', $data);
	}

	public function save_approval(){
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$kategori 		= $data['kategori'];
			$no_rfq 		= $data['no_rfq'];
			$detail 		= $data['detail'];			
			$byGroup = group_by("id_supplier", $detail);

			$Ym = date('ym');
			//pengurutan kode
			$srcMtr			= "SELECT MAX(no_po) as maxP FROM tran_material_po_header WHERE no_po LIKE 'PO".$Ym."%' ";
			$numrowMtr		= $this->db->query($srcMtr)->num_rows();
			$resultMtr		= $this->db->query($srcMtr)->result_array();
			$angkaUrut2		= $resultMtr[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 6, 4);
			$urutan2++;
			$no_po			= "PO".$Ym;

			$ArrHeader = array();
			$ArrDetail = array();
			$ArrUpdate = array();
			$no = 0;
			$this->db->trans_start();
			foreach($byGroup AS $val => $valx){ $no++;
				$urut2			= sprintf('%04s',$urutan2);
				foreach($valx AS $val2 => $valx2){
					$datasupplier=$this->All_model->GetOneData('master_supplier',array('id_supplier'=>$valx2['id_supplier']));

					$ArrHeader['no_po'] 			= $no_po.$urut2;
					$ArrHeader['id_supplier'] 	= $valx2['id_supplier'];
					$ArrHeader['nm_supplier'] 	= $datasupplier->name_supplier;
					$ArrHeader['total_material'] = 0;
					$ArrHeader['created_by'] 	= $this->session->userdata['app_session']['username'];
					$ArrHeader['created_date'] 	= date('Y-m-d H:i:s');
					$ArrHeader['kategori'] 		= $kategori;
				}
				$this->All_model->dataSave('tran_material_po_header', $ArrHeader);

				foreach($valx AS $val2 => $valx2){
					$sql_d 		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' AND a.id_material='".$valx2['id_material']."' AND a.id_supplier='".$valx2['id_supplier']."'";
					$dataT		= $this->db->query($sql_d)->result();					
					$harga     = $dataT[0]->price_ref_sup;					

					$ArrDetail['no_po'] 			= $no_po.$urut2;
					$ArrDetail['id_material'] 	= $valx2['id_material'];
					$ArrDetail['idmaterial'] 	= $dataT[0]->id_material;
					$ArrDetail['nm_material'] 	= $dataT[0]->nm_material;
					$ArrDetail['qty_purchase'] 	= $dataT[0]->qty;
					$ArrDetail['price_ref'] 		= $dataT[0]->price_ref;
					$ArrDetail['price_ref_sup'] 	= $dataT[0]->price_ref_sup;
					$ArrDetail['moq'] 			= $dataT[0]->moq;
					$ArrDetail['tgl_dibutuhkan'] = $dataT[0]->tgl_dibutuhkan;
					$ArrDetail['lead_time'] 		= $dataT[0]->lead_time;
					$ArrDetail['created_by'] 	= $this->session->userdata['app_session']['username'];
					$ArrDetail['created_date'] 	= date('Y-m-d H:i:s');
					$ArrDetail['kategori'] 		= $dataT[0]->kategori;

//					$ArrUpdate['id'] 			= $dataT[0]->id;
					$ArrUpdate['no_po'] 			= $no_po.$urut2;
					$ArrUpdate['status_apv'] 	= 'SETUJU';
					$ArrUpdate['close_by'] 		= $this->session->userdata['app_session']['username'];
					$ArrUpdate['close_date'] 	= date('Y-m-d H:i:s');
					$this->All_model->dataSave('tran_material_po_detail', $ArrDetail);
					$this->All_model->dataUpdate('tran_material_rfq_detail', $ArrUpdate, array('id'=>$dataT[0]->id));

/*
					$update			= "UPDATE tran_material_rfq_header SET sts_ajuan='CLS'
									  WHERE no_rfq='".$no_rfq."' and no_rfq IN (
									  SELECT IF(count(no_rfq)=0,'".$no_rfq."','') AS NILAI from tran_material_rfq_detail where status = 'BELUM SETUJU' and no_rfq='".$no_rfq."' )";
					$this->db->query($update);
*/

				}
				$ArrUpd2 = array('sts_ajuan' => 'CLS');
				$this->db->where(array('no_rfq'=>$no_rfq));
				$this->db->update('tran_material_rfq_header', $ArrUpd2);
				$urutan2++;				
			}			
			$this->db->trans_complete();

			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert data failed. Please try again later ...',
					'status'	=> 2
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert data success. Thanks ...',
					'status'	=> 1
				);
				
			}
			echo json_encode($Arr_Kembali);			
	      }

		public function list_po_aset_approval() {
			$data = $this->Purchase_order_model->query_data_json_po('ASET','OPN');
			$this->template->set('results', $data);
			$this->template->title('List PO Aset');
			$this->template->render('list_po_aset_approval');
		}

		public function list_po_nonrutin_approval() {
			$data = $this->Purchase_order_model->query_data_json_po('NONRUTIN','APV');
			$this->template->set('results', $data);
			$this->template->title('List PO Non Rutin');
			$this->template->render('list_po_nonrutin_approval');
		}

		public function list_po_rutin_approval() {
			$data = $this->Purchase_order_model->query_data_json_po('RUTIN','APV');
			$this->template->set('results', $data);
			$this->template->title('List PO Rutin');
			$this->template->render('list_po_rutin_approval');
		}

		public function print_rfq(){
		$no_rfq		= $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $this->session->userdata['app_session']['username'];
		$koneksi		= akses_server_side($this->db);
		include "application/modules/pr_selection/views/print_rfq.php";

		$data_url		= base_url();
		$Split_Beda		= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];
		print_rfq($Nama_Beda, $no_rfq, $koneksi, $printby);
	}

	public function purchase_order()
    {
//        $this->auth->restrict($this->viewPermission);
        $data = $this->Pr_selection_model->get_data_json_purchase_order();
        $this->template->set('results', $data);
        $this->template->title('List PO');
        $this->template->render('index_purchase_order.php');
    }
	public function view_po(){
		
		$nopo = $this->uri->segment(3);
		$data = $this->Pr_selection_model->GetSupplierPo($nopo);		
		$this->template->set('results', $data);
        $this->template->title('View Po');
        $this->template->render('view_po');
	}
	
	public function terima_aset()
    {
//        $this->auth->restrict($this->viewPermission);
        $data = $this->Pr_selection_model->get_data_json_purchase_order();
        $this->template->set('results', $data);
        $this->template->title('Terima Aset');
        $this->template->render('index_terima_aset.php');

    }
	
	public function edit_po(){
		
		$nopo = $this->uri->segment(3);
		$data = $this->Pr_selection_model->GetSupplierPo($nopo);		
		$this->template->set('results', $data);
        $this->template->title('Edit Po');
        $this->template->render('edit_po');
	}

	public function save_edit_po(){
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$no_po 		    = $data['no_po'];
			$header 		= $data['Header'];
			$detail 		= $data['Detail'];

			$ArrHeader = array();
			$ArrDetail = array();
			foreach($header AS $val => $valx){
				$ArrHeader[$val]['no_po'] 			= $no_po;
				$ArrHeader[$val]['incoterms'] 		= $valx['incoterms'];
				$ArrHeader[$val]['request_date'] 	= $valx['request_date'];
				$ArrHeader[$val]['tax'] 			= $valx['tax'];
				$ArrHeader[$val]['remarks'] 		= $valx['remarks'];
				$ArrHeader[$val]['updated_by'] 		= $this->session->userdata['app_session']['username'];
				$ArrHeader[$val]['updated_date'] 	= date('Y-m-d H:i:s');
			}

			foreach($detail AS $val => $valx){
				foreach($valx['detail'] AS $val2 => $valx2){
					$ArrDetail[$val.$val2]['id'] 			= $valx2['id'];
					$ArrDetail[$val.$val2]['qty_purchase'] 	= $valx2['qty_purchase'];
				}
			}

			$ArrTop = array();

			$term=$this->input->post('detail_top_term');
			$progress=$this->input->post('detail_top_progress');
			$value=$this->input->post('detail_top_value');
			$syarat=$this->input->post('detail_top_syarat');
			$this->db->trans_start();
			$this->All_model->dataDelete('billing_top',array('no_ipp'=>$no_po));			
			for ($i=0;$i<count($term);$i++){
				$ArrTop =  array(
						'no_ipp'=>$no_po,
						'category'=>$this->input->post("kategori"),
						'term'=>$term[$i],
						'progress'=>$progress[$i],
						'value'=>$value[$i],
						'syarat'=>$syarat[$i],
						'created_by'=>$this->session->userdata['app_session']['username'],
						'created_date'=>date('Y-m-d H:i:s'),
					);
				$this->All_model->dataSave('billing_top',$ArrTop);
			}

			$this->db->update_batch('tran_material_po_header', $ArrHeader, 'no_po');
			$this->db->update_batch('tran_material_po_detail', $ArrDetail, 'id');
			$this->db->trans_complete();
			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert data failed. Please try again later ...',
					'status'	=> 2
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=>'Insert data success. Thanks ...',
					'status'	=> 1
				);
			}
			echo json_encode($Arr_Kembali);

	}

	public function print_po(){
		$no_po		= $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $this->session->userdata['app_session']['username'];
		$koneksi		= akses_server_side($this->db);
		include "application/modules/pr_selection/views/print_po.php";

		$data_url		= base_url();
		$Split_Beda		= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];
		print_po($Nama_Beda, $no_po, $koneksi, $printby);
	}

}
?>
