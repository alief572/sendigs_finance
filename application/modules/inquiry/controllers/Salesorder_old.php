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

        $this->template->set('results', $data);
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
        $itembarang    = $this->Salesorder_model
        ->pilih_item($session['kdcab'])
        ->result();
        //$diskontoko = $this->Salesorder_model->get_data(array('deleted'=>'0'),'customer');
       // $listitembarang = $this->Detailsalesordertmp_model->find_all();
        $listitembarang = $this->Detailsalesordertmp_model->find_all_by(array('createdby'=>$session['id_user']));
        if(!@$listitembarang){
            $this->session->unset_userdata('header_so');
        }
        //$customer = $this->Salesorder_model->get_data(array('deleted'=>'0'),'customer');
        $customer = $this->Customer_model->find_all_by(array('deleted'=>0));
        $marketing = $this->Salesorder_model->pilih_marketing()->result();

        $this->template->set('itembarang',$itembarang);
        $this->template->set('listitembarang',$listitembarang);
        $this->template->set('customer',$customer);
        $this->template->set('marketing',$marketing);
        $this->template->title('Input Sales Order');
        $this->template->render('salesorder_form');
    }

    //Edit Sales Order
    public function edit()
    {
        //$this->auth->restrict($this->addPermission);
        $session = $this->session->userdata('app_session');
        $noso= $this->uri->segment(3);
        $header  = $this->Salesorder_model->find_by(array('no_so' => $noso));
        //$detail  = $this->Detailsalesorder_model->find_all(array('no_so' => $noso));

        $itembarang    = $this->Salesorder_model->pilih_item($session['kdcab'])->result();
        $listitembarang = $this->Detailsalesorder_model->find_all_by(array('no_so' => $noso));
        $customer = $this->Customer_model->find_all();
        $marketing = $this->Salesorder_model->pilih_marketing()->result();
        $pic = $this->Salesorder_model->get_pic_customer($header->id_customer)->result();

        $this->template->set('itembarang',$itembarang);
        $this->template->set('data',$header);
        $this->template->set('listitembarang',$listitembarang);
        $this->template->set('customer',$customer);
        $this->template->set('marketing',$marketing);
        $this->template->set('pic',$pic);
        $this->template->title('Edit Sales Order');
        $this->template->render('salesorder_form_edit');
    }

    function get_detail_so(){
        $noso = $this->input->post('NO_SO');
        $id = $this->input->post('ID');
        $key = array('no_so'=>$noso,'id_barang'=>$id);
        if(!empty($noso) && !empty($id)){
            $detail  = $this->Detailsalesorder_model->find_by($key);
        }
        echo json_encode($detail);
    }

    //Get detail item barang
    function get_item_barang(){
        $idbarang = $_GET['idbarang'];
        $datbarang = $this->Salesorder_model->get_item_barang($idbarang)->row();

        echo json_encode($datbarang);
    }

    //Get detail Customer
    function get_customer(){
        $idcus = $_GET['idcus'];
        $customer = $this->Salesorder_model->get_customer($idcus)->row();

        echo json_encode($customer);
    }

    //Get PIC Customer
    function get_pic_customer(){
        $idcus = $_GET['idcus'];
        $customer = $this->Salesorder_model->get_pic_customer($idcus)->result();
        $pichtml = '';
        if($customer){
            //$pichtml = '<select name="pic" id="pic" class="form-control input-sm select2">';
            foreach($customer as $k=>$v){
                if($v->divisi != "" && $v->jabatan != ""){
                    $pichtml .= '<option value="'.$v->id_pic.'">'.$v->nm_pic.' - '.$v->divisi.' ('.$v->jabatan.')</option>';
                }else{
                    $pichtml .= '<option value="'.$v->id_pic.'">'.$v->nm_pic.'</option>';
                }
            }
            //$pichtml .= '</select>';
        }else{
            $pichtml = '';
        }

        echo $pichtml;
    }

	function get_pic_customer_new(){
		$arr_Data		= array();
		$kode_cust		= $_GET['company'];
		$nam_cust		= $_GET['term'];
		if($kode_cust !=''){
			$Query	  ="SELECT * FROM customer_pic WHERE id_customer='".$kode_cust."' AND nm_pic LIKE '%".$nam_cust."%'";
			 $customer = $this->db->query($Query)->result_array();
			 if($customer){
				 $loop	=0;
				 foreach($customer as $key=>$vals){
					 $loop++;
					 $kode_Pic	= $vals['id_pic'];
					 $arr_Data[$Key]	= array(
						'label'				=> $vals['nm_pic'],
						'value'				=> $vals['nm_pic'],
						'id'				=> $kode_Pic
					 );

				 }
				 unset($customer);
			 }
		}
       echo json_encode($arr_Data);
    }

    //Get detail Sales
    function get_salesman(){
        $idsales = $_GET['idsales'];
        $salesman = $this->Salesorder_model->get_marketing($idsales)->row();
        echo json_encode($salesman);
    }

    function saveitemso(){
        $dataheader = array(
            'idcustomer' => $this->input->post('idcustomer'),
            'nmcustomer' => $this->input->post('nmcustomer'),
            'pic' => $this->input->post('pic'),
            'idsalesman' => $this->input->post('idsalesman'),
            'nmsalesman' => $this->input->post('nmsalesman'),
            'tglso' => $this->input->post('tglso'),
            'dppso' => $this->input->post('dppso'),
            'totalso' => $this->input->post('totalso'),
            'ppnso' => $this->input->post('ppnso'),
            'nilaippn' => $this->input->post('nilaippn'),
            'persen_diskon_toko' => $this->input->post('persen_diskon_toko'),
            'diskon_toko' => $this->input->post('diskon_toko'),
            'diskoncash' => $this->input->post('diskoncash'),
            'top' => $this->input->post('top'),
            'keterangan' => $this->input->post('keterangan')
            );
        $this->session->set_userdata('header_so',$dataheader);

        $session = $this->session->userdata('app_session');
        $noso = $this->Salesorder_model->generate_noso($session['kdcab']);
        $idbarang = $this->input->post('item_brg_so');
        $nmbarang = $this->input->post('nama_barang');
        $satuan = $this->input->post('satuan');
        $jenis = $this->input->post('jenis');
        $qtyorder = $this->input->post('qty_order');
        $qtyavl = $this->input->post('qty_avl');
        $qtysupply = $this->input->post('qty_supply');//==> qty confirm
        $qtypending = $this->input->post('qty_pending');
        $qtycancel = $this->input->post('qty_cancel');
        $diskon_persen = $this->input->post('diskon_standar_persen');
        $diskon_standar = $this->input->post('diskon_standar_persen') * $this->input->post('qty_supply') * $this->input->post('harga_normal') / 100;
        $diskon_promo_rp = $this->input->post('diskon_promo_rp');
        $diskon_promo_persen = $this->input->post('diskon_promo_persen');
        $qty_bonus = $this->input->post('qty_bonus');
        $harga = $this->input->post('harga');
        $harga_normal = $this->input->post('harga_normal');
        $diskon = $diskon_standar + $diskon_promo_rp + ($diskon_promo_persen * $harga_normal * $qtysupply / 100);

        $total = $this->input->post('total');
        //$this->auth->restrict($this->addPermission);


        /*
        if($qtyavl <= $qtyorder){
            $qtybooked = $qtyavl;
        }else{
            $qtybooked = $qtysupply;
        }
        */

        $dataso = array(
            'no_so' => $noso,
            'id_barang' => $idbarang,
            'nm_barang' => $nmbarang,
            'satuan' => $satuan,
            'jenis' => $jenis,
            'qty_order' => $qtyorder,
            //'qty_supply' => $qtysupply,//qty_supply default nol
            'qty_pending' => $qtypending,
            'qty_cancel' => $qtycancel,
            'qty_booked' => $qtysupply,//qty_confirm
            'stok_avl' => $qtyavl,
            'ukuran' => '',
            'harga' => $harga,
            'harga_normal' => $harga_normal,
            'diskon' => $diskon,
            'diskon_persen' => $diskon_persen,
            'diskon_standar' => $diskon_standar,
            'diskon_promo_rp' => $diskon_promo_rp,
            'diskon_promo_persen' => $diskon_promo_persen,
            'qty_bonus' => $qty_bonus,
            'subtotal' => $total,
            'createdby' => $session['id_user']
            );

        //print_r($dataso);die();

        $this->db->trans_begin();
        $this->db->insert('trans_so_detail_tmp',$dataso);
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            $param = array(
            'save' => 0,
            'msg' => "GAGAL, tambah item barang..!!!"
            );
        }
        else
        {
            $this->db->trans_commit();
            $param = array(
            'save' => 1,
            'msg' => "SUKSES, tambah item barang..!!!",
            'header' => $dataheader
            );
        }
        echo json_encode($param);
    }

    function ajaxdetailso(){
        $this->load->view('salesorder/ajax/ajaxdetailsossss');
    }

    function saveheaderso(){
        $session = $this->session->userdata('app_session');
        $noso = $this->Salesorder_model->generate_noso($session['kdcab']);
        $noso_pending = $this->Salesorder_model->generate_no_pending($noso);
        $no_pickinglist = $this->Salesorder_model->generate_no_pl($session['kdcab']);
        $idcustomer = $this->input->post('idcustomer');
        $nmcustomer = $this->input->post('nmcustomer');
        $tglso = $this->input->post('tglso');
        $idsalesman = $this->input->post('idsalesman');
        $nmsalesman = $this->input->post('nmsalesman');
        $picso = $this->input->post('pic');
        $waktu = date('Y-m-d H:i:s');
        $statusso = '';
        $dppso = $this->input->post('dppso');
        $ppnso = $this->input->post('ppnso');
        $flagppn = $this->input->post('nilaippn');
        $totalso = $this->input->post('totalso');
        $diskon_toko = $this->input->post('diskon_toko');
        $diskon_cash = $this->input->post('diskon_cash');
        $keterangan = $this->input->post('keterangan');
        $top = $this->input->post('top');

        $dataheaderso = array(
            'no_so' => $noso,
            'no_picking_list' => $no_pickinglist,
            'id_customer' => $idcustomer,
            'nm_customer' => $nmcustomer,
            'tanggal' => $tglso,
            'id_salesman' => $idsalesman,
            'nm_salesman' => $nmsalesman,
            'pic' => $picso,
            'waktu' => $waktu,
            'dpp' => $dppso,
            'ppn' => $ppnso,
            'flag_ppn' => $flagppn,
            'total' => $totalso,
            'diskon_toko' => $diskon_toko,
            'diskon_cash' => $diskon_cash,
            'top' => $top,
            'keterangan' => $keterangan
            );
        $this->db->trans_begin();
        $data_tmp = $this->Detailsalesordertmp_model->find_all_by(array('createdby'=>$session['id_user']));
        foreach($data_tmp as $key=>$val){
            $dataitem = array(
                'no_so' => $val->no_so,
                'id_barang' => $val->id_barang,
                'nm_barang' => $val->nm_barang,
                'satuan' => $val->satuan,
                'jenis' => '',
                'qty_order' => $val->qty_booked,
                'qty_supply' => $val->qty_supply,
                'qty_booked' => $val->qty_booked,
                'qty_cancel' => $val->qty_cancel,
                'qty_pending' => $val->qty_pending,
                'stok_avl' => $val->stok_avl,
                'ukuran' => '',
                'harga' => $val->harga,
                'harga_normal' => $val->harga_normal,
                'diskon' => $val->diskon,
                'diskon_persen' => $val->diskon_persen,
                'diskon_standar' => $val->diskon_standar,
                'diskon_promo_rp' => $val->diskon_promo_rp,
                'diskon_promo_persen' => $val->diskon_promo_persen,
                'qty_bonus' => $val->qty_bonus,
                'subtotal' => $val->subtotal
            );
            $qty_booking = $val->qty_booked;
            $qty_pending = $val->qty_pending;
            $qty_order = $val->qty_order;
            $qty_cancel = $val->qty_cancel;
            if ($val->qty_booked < $val->qty_order) {
              $qty_supply_pending = '1';
              $dataitem_pending = array(
                'no_so_pending' => $noso_pending,
                'no_so' => $val->no_so,
                'id_barang' => $val->id_barang,
                'nm_barang' => $val->nm_barang,
                'satuan' => $val->satuan,
                'qty_order' => $val->qty_booked,
                'qty_booked' => $val->qty_order-$val->qty_pending-$val->qty_booked,
                'qty_pending' => $val->qty_pending,
                'qty_cancel' => $val->qty_cancel,
                'qty_supply' => $val->qty_supply,
                'harga' => $val->harga,
                'harga_normal' => $val->harga_normal,
                'subtotal' => $val->subtotal
              );

              $this->db->insert('trans_so_pending_detail',$dataitem_pending);
              if ($val->qty_booked != 0) {

                $this->db->insert('trans_so_detail',$dataitem);
              }
            }
            else {

              $this->db->insert('trans_so_detail',$dataitem);
            }
            //Update QTY_AVL
            $keycek = array('kdcab'=>$session['kdcab'],'id_barang'=>$val->id_barang);
            $stok_avl = $this->Salesorder_model->cek_data($keycek,'barang_stock');
            $this->db->where($keycek);
            $this->db->update('barang_stock',array('qty_avl'=>$stok_avl->qty_avl-$val->qty_booked));
            //Update QTY_AVL
        }

        $this->db->insert('trans_so_header',$dataheaderso);

        //Update counter NO_SO
        $counter = $this->Salesorder_model->cek_data(array('kdcab'=>$session['kdcab']),'cabang');
        $pl = 1;
        if(date('y') == $counter->th_picking_list){
            $pl = $counter->no_picking_list+1;
        }
        $data_update = array(
            'no_so'=>$counter->no_so+1,
            'th_picking_list' => date('y'),
            'no_picking_list' => $pl
            );
        $this->db->where(array('kdcab'=>$session['kdcab']));
        $this->db->update('cabang',$data_update);
        //Update counter NO_SO
        if (isset($qty_supply_pending)) {
          $dataheadersopending = array(
              'no_so_pending' => $noso_pending,
              'no_so' => $noso,
              //'no_picking_list' => $no_pickinglist,
              'id_customer' => $idcustomer,
              'nm_customer' => $nmcustomer,
              'tanggal' => $tglso,
              'id_salesman' => $idsalesman,
              'nm_salesman' => $nmsalesman,
              'pic' => $picso,
              'waktu' => $waktu,
              'dpp' => $dppso,
              'ppn' => $ppnso,
              'flag_ppn' => $flagppn,
              'total' => $totalso,
              'create_by' => $session['id_user'],
              'create_on' => date('Y-m-d H:i:s')
              );
              $this->db->insert('trans_so_pending_header',$dataheadersopending);
        }
        $this->db->delete('trans_so_detail_tmp', array('createdby' => $session['id_user']));
        //$this->db->truncate('trans_so_detail_tmp');

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            $param = array(
            'save' => 0,
            'msg' => "GAGAL, tambah item barang..!!!"
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

    function hapus_item_so(){
        $session = $this->session->userdata('app_session');
        $noso = $this->input->post('NO_SO');
        $id = $this->input->post('ID');
        $key = array('no_so'=>$noso,'id_barang'=>$id,'createdby'=>$session['id_user']);
        if(!empty($noso) && !empty($id)){
           $result = $this->Detailsalesordertmp_model->delete_where($key);
           $param['delete'] = 1;
        }else{
            $param['delete'] = 0;
        }
        echo json_encode($param);
    }

    function hapus_header_so(){
        $noso = $this->input->post('NO_SO');
        if(!empty($noso)){
           $result = $this->Salesorder_model->delete($noso);
           $param['delete'] = 1;
        }else{
            $param['delete'] = 0;
        }
        echo json_encode($param);
    }

    function set_cancel_so(){
        $noso = $this->input->post('NO_SO');
        if(!empty($noso)){
            $kdcab = substr($noso,0,3);
            $session = $this->session->userdata('app_session');
           $this->db->trans_begin();
           $getitemso = $this->Salesorder_model->get_data(array('no_so'=>$noso),'trans_so_detail');
           foreach($getitemso as $k=>$v){
                //Update QTY_AVL
                $keycek = array('kdcab'=>$kdcab,'id_barang'=>$v->id_barang);
                $stok_avl = $this->Salesorder_model->cek_data($keycek,'barang_stock');
                $this->db->where($keycek);
                $this->db->update('barang_stock',array('qty_avl'=>$stok_avl->qty_avl+$v->qty_booked));
                //Update QTY_AVL
           }
           $this->db->where(array('no_so'=>$noso));
           $this->db->update('trans_so_header',array('stsorder'=>'CANCEL'));
            if ($this->db->trans_status() === FALSE){
                $this->db->trans_rollback();
                $param['cancel'] = 0;
            }else{
                $this->db->trans_commit();
                $param['cancel'] = 1;
            }
        }
        echo json_encode($param);
    }

    function print_request($noso){
        $no_so = $noso;
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $so_data = $this->Salesorder_model->find_data('trans_so_header',$no_so,'no_so');
        $customer = $this->Salesorder_model->cek_data(array('id_customer'=>$so_data->id_customer),'customer');
        $detail = $this->Detailsalesorder_model->find_all_by(array('no_so' => $no_so));

        $this->template->set('so_data', $so_data);
        $this->template->set('customer', $customer);
        $this->template->set('detail', $detail);
        $show = $this->template->load_view('print_data',$data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    function print_picking_list($noso){
        $no_so = $noso;
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $so_data = $this->Salesorder_model->find_data('trans_so_header',$no_so,'no_so');
        $customer = $this->Salesorder_model->cek_data(array('id_customer'=>$so_data->id_customer),'customer');
        $detail = $this->Detailsalesorder_model->find_all_by(array('no_so' => $no_so));

        $this->template->set('so_data', $so_data);
        $this->template->set('customer', $customer);
        $this->template->set('detail', $detail);
        $show = $this->template->load_view('print_picking_list',$data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

}

?>
