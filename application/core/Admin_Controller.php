<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_Controller extends Base_Controller
{
    protected $pager;
    protected $limit;
    protected $user_data;

    public function __construct()
    {
        $this->autoload['helpers'][]   = 'form';
        $this->autoload['libraries'][] = 'Template';
        $this->autoload['libraries'][] = 'users/auth';

        parent::__construct();

        $this->load->model('identitas_model');

        /*Check If user has logged in*/
        if (!$this->auth->is_login())
        {
            redirect('login');
        }

        $idt         = $this->identitas_model->find(1);

        $this->user_data = $this->auth->userdata();

        $this->form_validation->set_error_delimiters('', '');

        // Pagination config
        $this->pager = array(
            'full_tag_open'     => '<ul class="pagination pull-right" style="margin: 0 0 0;">',
            'full_tag_close'    => '</ul>',
            'next_link'         => '&rarr;',
            'prev_link'         => '&larr;',
            'next_tag_open'     => '<li>',
            'next_tag_close'    => '</li>',
            'prev_tag_open'     => '<li>',
            'prev_tag_close'    => '</li>',
            'first_tag_open'    => '<li>',
            'first_tag_close'   => '</li>',
            'last_tag_open'     => '<li>',
            'last_tag_close'    => '</li>',
            'cur_tag_open'      => '<li class="active"><a href="#">',
            'cur_tag_close'     => '</a></li>',
            'num_tag_open'      => '<li>',
            'num_tag_close'     => '</li>',
        );

        // Basic setup
        $this->template->set('userData', $this->user_data);
        $this->template->set('idt', $idt);
        $this->template->set_theme('admin');
        $this->template->set_layout('index');
        //Overwrite if the request is ajax
        if($this->input->is_ajax_request())
        {
            $this->template->set_layout('ajax');
        }
        
        $this->form_validation->set_error_delimiters('<p>','</p>');
    }
}
/* End of file Admin_Controller.php */
