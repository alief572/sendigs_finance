<?php defined('BASEPATH') || exit('No direct script access allowed');

class Base_Controller extends MX_Controller
{
    protected $previous_page;
    protected $requested_page;

    private $ignore_pages = array(
        '/dms/login',
        'login',
        '/dms/logout',
        'logout',
        '/images',
    );

    public $autoload = array(
        'libraries' => array(),
        'helpers'   => array('application'),
        'models'    => array(),
    );

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('form_validation','session'));
        $this->form_validation->CI =& $this;

        $this->saveRequestPage();
    }

    protected function saveRequestPage()
    {
        $uri = empty($_SERVER['REQUEST_URI']) ? null : $_SERVER['REQUEST_URI'];
        if (empty($uri)) {
            // Try to get the current URL through PATH INFO.
            $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
            if (trim($path, '/') != '' && $path != '/' . SELF) {
                $uri = $path;
            }
        }

        if (empty($uri)) {
            // Finally, try the query string.
            $path =  isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
            if (trim($path, '/') != '') {
                $uri = $path;
            }
        }

        $base_sub_folder = str_replace(prep_url($_SERVER['HTTP_HOST']), '', base_url());
        $uri = str_replace($base_sub_folder,'', $uri);

        if(!in_array($uri, $this->ignore_pages))
        {
            $this->session->set_userdata('requested_page', $uri);
        }

        /*Previous Page*/

        $prev_url = isset($_SERVER['HTTP_REFERER']) ? str_replace(base_url(), '', $_SERVER['HTTP_REFERER']) : '';
        if($prev_url == '')
        {
            $prev_url = str_replace(base_url(), '', current_url());
        }

        $this->session->set_userdata('previous_page', $prev_url);
    }

}
/*End of Base Controller*/