<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_ppic extends Admin_Controller
{
	/*
 * @author Yunaz
 * @copyright Copyright (c) 2016, Yunaz
 *
 * This is controller for Penerimaan
 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('dashboard_ppic/dashboard_ppic_model');

		$this->template->page_icon('fa fa-dashboard');
	}

    public function index(){

        $get_data = $this->db->select('a.nm_material, a.qty_oke, a.expired_date')
        ->from('tr_checked_incoming_detail a')
        ->where('a.qty_used < a.qty_oke')
        ->where('a.expired_date <>', '0000-00-00')
        ->get()
        ->result();

        $this->template->title('Monitoring PPIC');
        $this->template->set('result', $get_data);
        $this->template->render('index');
    }


}
