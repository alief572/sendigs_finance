<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Rekon_penerimaan extends Admin_Controller
{
    protected $viewPermission     = 'Rekon_Penerimaan.View';
    protected $addPermission      = 'Rekon_Penerimaan.Add';
    protected $managePermission = 'Rekon_Penerimaan.Manage';
    protected $deletePermission = 'Rekon_Penerimaan.Delete';

    protected $accounting;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Rekon_penerimaan/Rekon_penerimaan_model',
        ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->accounting = $this->load->database('accounting', true);
    }

    public function index()
    {
        $this->accounting->select('a.no_perkiraan, a.nama');
        $this->accounting->from('coa_master a');
        $this->accounting->where('a.kode_bank IS NOT NULL');
        $get_coa_bank = $this->accounting->get()->result();

        $data = [
            'list_coa_bank' => $get_coa_bank
        ];

        $this->template->title('Rekon Penerimaan');
        $this->template->render('index');
    }
}
