<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Salesorder
 */

class Salesorder extends Admin_Controller {

    //Permission
    protected $viewPermission   = "Salesorder.View";
    protected $addPermission    = "Salesorder.Add";
    protected $managePermission = "Salesorder.Manage";
    protected $deletePermission = "Salesorder.Delete";

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload','Image_lib'));
        $this->load->model(array('Salesorder/Salesorder_model',
                                 'Salesorder/Detailsalesorder_model',
                                 'Salesorder/Detailsalesordertmp_model',
                                 'Customer/Customer_model',
                                 'Aktifitas/aktifitas_model'
                                ));
        $this->template->title('Sales Order');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = $this->Salesorder_model->order_by('no_so','DESC')->find_all_by(array('total !='=>0));
        $disc_cash = $this->Salesorder_model->get_data(array('diskon'=>'CASH'),'diskon');
        //$this->Salesorder_model->find_data('trans_so_header',$no_so,'no_so');
        $this->template->set('results', $data);
        $this->template->set('disc_cash', $disc_cash);
        $this->template->title('Sales Order');
        $this->template->render('list');
    }

    public function getitemsotemp(){
        $this->template->render('getitemsotemp');
    }

    //Create New Sales Order
    public function create()
    {
        $this->auth->restrict($this->addPermission);

        $session = $this->session->userdata('app_session');
        // $itembarang    = $this->Salesorder_model
        // ->pilih_item($session['kdcab'])
        // ->result();
        //$diskontoko = $this->Salesorder_model->get_data(array('deleted'=>'0'),'customer');
       // $listitembarang = $this->Detailsalesordertmp_model->find_all();
        // $listitembarang = $this->Detailsalesordertmp_model->find_all_by(array('createdby'=>$session['id_user']));
        // if(!@$listitembarang){
            // $this->session->unset_userdata('header_so');
        // }
        //$customer = $this->Salesorder_model->get_data(array('deleted'=>'0'),'customer');
        // $customer = $this->Customer_model->find_all_by(array('deleted'=>0));
        // $marketing = $this->Salesorder_model->pilih_marketing()->result();

        // $this->template->set('itembarang',$itembarang);
        // $this->template->set('listitembarang',$listitembarang);
        // $this->template->set('customer',$customer);
        // $this->template->set('marketing',$marketing);
        $this->template->title('Input Sales Order');
        $this->template->render('salesorder_form');
    }


}

?>
