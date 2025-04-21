<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Pengajuan Rutin
 */

$status=array();
class Payment extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Payment.View';
    protected $addPermission  	= 'Payment.Add';
    protected $managePermission = 'Payment.Manage';
    protected $deletePermission = 'Payment.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('jurnal_nomor/Acc_model','Payment/Payment_model'));
        $this->template->title('Pembayaran PO');
        $this->template->page_icon('fa fa-cubes');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index() {
        $this->auth->restrict($this->viewPermission);
        $data = $this->Payment_model->ListPembayaran();
        $this->template->set('results', $data);
        $this->template->title('List Pembayaran');
        $this->template->render('list');
    }

    public function create() {
        $this->auth->restrict($this->addPermission);
		$this->template->title('Input Pembayaran');
		$this->template->set('type','add');
        $this->template->render('input_form');
    }

    public function edit($id) {
        $data	= $this->Payment_model->GetDataPayment($id);
        if(!$data) {
            $this->template->set_message("Invalid Data", 'error');
            redirect('payment');
        }
		$data_detail=$this->Payment_model->GetDataPaymentDetail($data->no_doc);
		$this->template->set('type','edit');
        $this->template->set('data',$data);
        $this->template->set('data_detail',$data_detail);
        $this->template->title('Edit Pembayaran');
        $this->template->render('input_form');
    }

    public function view($id) {
        $data	= $this->Payment_model->GetDataPayment($id);
        if(!$data) {
            $this->template->set_message("Invalid Data", 'error');
            redirect('Payment');
        }
		$data_detail=$this->Payment_model->GetDataPaymentDetail($data->no_doc);
		$this->template->set('type','view');
        $this->template->set('data',$data);
        $this->template->set('data_detail',$data_detail);
        $this->template->title('View Pembayaran');
        $this->template->render('input_form');
    }

    public function get_data() {
		$allbill		= $this->input->post("allbill");
        $tanggal           = $this->input->post("tanggal");
		$data=$this->Payment_model->GetDataBilling($tanggal,$allbill);
		$param = array(
				'save' =>1,
				'data'=>$data,
				);
		echo json_encode($param);
	}

    public function save_data(){

        $id				= $this->input->post("id");
		$no_doc			= $this->input->post("no_doc");
		$tanggal_doc	= $this->input->post("tanggal_doc");
        $dana_tersedia  = $this->input->post("dana_tersedia");
        $total_payment  = $this->input->post("total_payment");

		$detail_id		= $this->input->post("detail_id");
		$id_vendor		= $this->input->post("id_vendor");
		$category		= $this->input->post("category");
		$id_bill		= $this->input->post("id_bill");
		$no_po			= $this->input->post("no_po");
        $nama           = $this->input->post("nama");
		$nilai_invoice	= $this->input->post("nilai_invoice");
		$sisa_bayar		= $this->input->post("sisa_bayar");
		$nilai_bayar	= $this->input->post("nilai_bayar");
		$tanggal_top	= $this->input->post("tanggal_top");
		$progress		= $this->input->post("progress");
        $no_invoice		= $this->input->post("no_invoice");
        $keterangan		= $this->input->post("keterangan");

			$this->db->trans_begin();
			if($no_doc=='') {
				$no_doc=$this->identitas_model->GenerateAutoNumber('no_payment');
				$dataheader =  array(
							'no_doc'=>$no_doc,
							'tanggal_doc'=>$tanggal_doc,
							'dana_tersedia'=>$dana_tersedia,
							'total_payment'=>$total_payment,
						);
				$this->Payment_model->insert($dataheader);

			}else{
				$dataheader =  array(
					array(
							'id'=>$id,
							'tanggal_doc'=>$tanggal_doc,
							'dana_tersedia'=>$dana_tersedia,
							'total_payment'=>$total_payment,
						)
					);
				$this->Payment_model->update_batch($dataheader,'id');
			}
			if (is_array($detail_id)) {
				$delid=implode("','",$detail_id);
				$this->identitas_model->DataDelete('tr_payment_detail'," id not in ('".$delid."') and no_doc='".$no_doc."'");
			}
			for ($x = 0; $x < count($detail_id); $x++) {
				if($detail_id[$x]!='') {
					$data = array(
								'id_vendor'=>$id_vendor[$x],
								'category'=>$category[$x],
								'nama'=>$nama[$x],
								'id_bill'=>$id_bill[$x],
								'no_po'=>$no_po[$x],
								'nilai_invoice'=>$nilai_invoice[$x],
								'sisa_bayar'=>$sisa_bayar[$x],
								'nilai_bayar'=>$nilai_bayar[$x],
								'tanggal_top'=>$tanggal_top[$x],
								'progress'=>$progress[$x],
								'no_invoice'=>$no_invoice[$x],
								'keterangan'=>$keterangan[$x],
						);
					$this->identitas_model->DataUpdate('tr_payment_detail',$data,array('id'=>$detail_id[$x]));
				}else{
					$data =  array(
								'no_doc'=>$no_doc,
								'id_vendor'=>$id_vendor[$x],
								'category'=>$category[$x],
								'nama'=>$nama[$x],
								'id_bill'=>$id_bill[$x],
								'no_po'=>$no_po[$x],
								'nilai_invoice'=>$nilai_invoice[$x],
								'sisa_bayar'=>$sisa_bayar[$x],
								'nilai_bayar'=>$nilai_bayar[$x],
								'tanggal_top'=>$tanggal_top[$x],
								'progress'=>$progress[$x],
								'no_invoice'=>$no_invoice[$x],
								'keterangan'=>$keterangan[$x],
							);
					$this->identitas_model->DataSave('tr_payment_detail',$data);
				}
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

    function hapus_data($id)
    {
        $this->auth->restrict($this->deletePermission);
        if($id!=''){
			$data	= $this->Payment_model->GetDataPayment($id);
			if(!$data) {
				$this->template->set_message("Invalid Data", 'error');
				redirect('payment');
			}
			$this->db->trans_begin();
            $this->identitas_model->DataDelete('tr_payment_header',array('id'=>$id));
			$this->identitas_model->DataDelete('tr_payment_detail',array('no_doc'=>$data->no_doc));
            $result = $this->db->trans_status();
			$this->db->trans_complete();
            $keterangan     = "SUKSES, Delete data  ";
            $status         = 1; $nm_hak_akses   = $this->deletePermission; $kode_universal = $id; $jumlah = 1;
            $sql            = $this->db->last_query();
        } else {
            $result = 0;
            $keterangan     = "GAGAL, Delete data  ";
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

	// approve
	public function approve($id=''){
		$result=false;
        if($id!="") {
			$data = array(
						array(
							'id'=>$id,
							'status'=>1,
						)
					);
			$result = $this->Payment_model->update_batch($data,'id');
			$keterangan     = "SUKSES, Approve data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result, 'id'=>$id
                );
        echo json_encode($param);
	}
}
