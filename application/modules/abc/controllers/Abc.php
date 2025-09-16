<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Abc extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Abc.View';
    protected $addPermission  	= 'Abc.Add';
    protected $managePermission = 'Abc.Manage';
    protected $deletePermission = 'Abc.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->template->title('Sample Abc');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');

      $this->template->title('Abc');
      $this->template->render('index');
    }

}

?>
