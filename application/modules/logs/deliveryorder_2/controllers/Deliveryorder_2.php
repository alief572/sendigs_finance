<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Salesorder
 */

class Deliveryorder_2 extends Admin_Controller {
    
    //Permission
    /*
    protected $viewPermission   = "Deliveryorder.View";
    protected $addPermission    = "Deliveryorder.Add";
    protected $managePermission = "Deliveryorder.Manage";
    protected $deletePermission = "Deliveryorder.Delete";
    */
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload','Image_lib'));
        
        $this->load->model(array('Deliveryorder_2/Deliveryorder_model',
                                 'Deliveryorder_2/Detaildeliveryorder_model',
                                 'Salesorder/Salesorder_model',
                                 'Salesorder/Detailsalesorder_model',
                                 'Customer/Customer_model',
                                 'Aktifitas/aktifitas_model'
                                ));
        
        $this->template->title('Delivery Order');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        //$this->auth->restrict($this->viewPermission);

        $data = $this->Deliveryorder_model->order_by('no_do','ASC')->find_all();
        $this->template->set('results', $data);
        $this->template->title('Delivery Order');
        $this->template->render('list');
    }

    //Create New Delivery Order
    public function create()
    {
        //$this->auth->restrict($this->addPermission);
        /*
        $session = $this->session->userdata('app_session');
        $nodo = $this->Deliveryorder_model->generate_nodo($session['kdcab']);
        $customer = $this->Customer_model->find_all();
        $marketing = $this->Deliveryorder_model->pilih_marketing()->result();
        $getitemdo = $this->Detaildeliveryorder_model->find_all_by(array('no_do'=>$nodo));
        $this->template->set('customer',$customer);
        $this->template->set('marketing',$marketing);
        $this->template->set('detaildo',$getitemdo);
        */
        $data = $this->Salesorder_model->order_by('no_so','ASC')->find_all();
        $this->template->set('results', $data);
        
        $this->template->title('Input Delivery Order');
        $this->template->render('list_so');
    }

    //Get detail Customer
    function get_customer(){
        $idcus = $_GET['idcus'];
        $customer = $this->Salesorder_model->get_customer($idcus)->row();

        echo json_encode($customer);
    }

    //Get detail Sales
    function get_salesman(){
        $idsales = $_GET['idsales'];
        $salesman = $this->Salesorder_model->get_marketing($idsales)->row();

        echo json_encode($salesman);
    }

    public function get_itemsobycus(){
        $idcustomer = $this->input->post('idcus');
        $getso = $this->Salesorder_model->find_all_by(array('id_customer'=>$idcustomer,'stsorder'=>0));
        //$getitemso = $this->Detailsalesorder_model->find_all_by(array('no_so'=>$getso->no_so));
        $data['so'] = $getso;
        $data['customer'] = $this->Customer_model->find_by(array('id_customer'=>$idcustomer));;
        //$data['itemso'] = $getitemso;
        $this->load->view('ajax/get_itemsobycus',$data);
    }

    public function set_itemdo(){
        $session = $this->session->userdata('app_session');
        $noso = $this->input->post('NOSO');
        $idbrg = $this->input->post('IDBRG');
        $cus = $this->input->post('CUS');
        $by = $this->input->post('BY');
        $key = array(
            'no_so' => $noso,
            'id_barang' => $idbrg,
            'createdby' => $by
            );
        $getitemso = $this->Detailsalesorder_model->find_by($key); 

        $dataitem_do = array(
            'no_do' => $this->Deliveryorder_model->generate_nodo($session['kdcab']),
            'id_barang' => $getitemso->id_barang,
            'nm_barang' => $getitemso->nm_barang,
            'satuan' => $getitemso->satuan,
            'qty_order' => $getitemso->qty_order,
            'qty_supply' => $getitemso->qty_supply
            );
        $this->db->trans_start();
        $this->db->insert('trans_do_detail',$dataitem_do);
        //$this->db->where($key);
        //$this->db->update('trans_so_detail',array('proses_do'=>1));
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE){
            $result['type'] = "error";
            $result['pesan'] = "Data gagal disimpan !";
        }else{
            $result['type'] = "success";
            $result['pesan'] = "Data sukses disimpan.";
        }
        echo json_encode($result);
    }

    public function hapus_item_do(){
        $id=$this->input->post('ID');
        if(!empty($id)){
           $result = $this->Detaildeliveryorder_model->delete_where(array('id'=>$id));
           $param['delete'] = 1; 
        }else{
            $param['delete'] = 0;
        }
        echo json_encode($param);
    }

    function hapus_header_do(){
        $nodo = $this->input->post('NO_DO');
        if(!empty($nodo)){
           $result = $this->Deliveryorder_model->delete($noso);
           $param['delete'] = 1; 
        }else{
            $param['delete'] = 0;
        }
        echo json_encode($param);
    }

    function saveheaderdo(){
        $session = $this->session->userdata('app_session');
        $nodo = $this->Deliveryorder_model->generate_nodo($session['kdcab']);
        $idcustomer = $this->input->post('idcustomer');
        $nmcustomer = $this->input->post('nmcustomer');
        $idsalesman = $this->input->post('idsalesman');
        $nmsalesman = $this->input->post('nmsalesman');
        $tgldo = $this->input->post('tgldo');
        $tipekirim = $this->input->post('tipekirim');
        $nmsupir = $this->input->post('supir_do');
        $kendaraan = $this->input->post('kendaraan_do');

        $dataheaderdo = array(
            'no_do' => $nodo,
            'id_customer' => $idcustomer,
            'nm_customer' => $nmcustomer,
            'tgl_do' => $tgldo,
            'id_salesman' => $idsalesman,
            'nm_salesman' => $nmsalesman,
            'tipe_pengiriman' => $tipekirim,
            'nm_supir' => $nmsupir,
            'id_kendaraan' => $kendaraan,
            );

        $this->db->trans_begin();
        //Update counter NO_DO
        $count = $this->Deliveryorder_model->cek_data(array('kdcab'=>$session['kdcab']),'cabang');
        $this->db->where(array('kdcab'=>$session['kdcab']));
        $this->db->update('cabang',array('no_suratjalan'=>$count->no_suratjalan+1));
        //Update counter NO_DO
        $this->db->insert('trans_do_header',$dataheaderdo);
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

    function print_request($nodo){
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();
        
        $do_data = $this->Deliveryorder_model->find_data('trans_do_header',$nodo,'no_do');
        $customer = $this->Deliveryorder_model->cek_data(array('id_customer'=>$do_data->id_customer),'customer');
        $detail = $this->Detaildeliveryorder_model->find_all_by(array('no_do' => $nodo));

        $this->template->set('do_data', $do_data);
        $this->template->set('customer', $customer);
        $this->template->set('detail', $detail);
        
        $show = $this->template->load_view('print_data',$data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);        
        $this->mpdf->Output();
    }

}

?>
