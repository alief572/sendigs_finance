<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends Front_Controller {

    public function __construct()
    {
    	parent::__construct();
    	$this->load->model(array('identitas_model'));
    	$this->load->library('users/auth');

        $this->id_user  = $this->auth->user_id();
    }

    public function index()
    {
        redirect('users/setting');
    }

    public function login()
    {
        if($this->auth->is_login())
        {
            history("Login");
            redirect('/');
        }

    	//$identitas = $this->identitas_model->find(1); => ERROR variable nama_program not define krn ga ada fieldnya di tabel identitas
        $identitas = $this->identitas_model->find_by(array('ididentitas'=>1));// By Muhaemin => Di Form Login

    	if(isset($_POST['login']))
    	{
    		$username = $this->input->post('username');
    		$password = $this->input->post('password');

    		$this->auth->login($username, $password);
    	}

    	$this->template->set('idt', $identitas);
        $this->template->set_theme('default');
        $this->template->set_layout('login');
        $this->template->title('Login');
    	$this->template->render('login_animate');
    }

    public function logout()
    {   
        if(!empty($this->id_user)){
            history("Logout");
        }
    	$this->auth->logout();
    }
}
