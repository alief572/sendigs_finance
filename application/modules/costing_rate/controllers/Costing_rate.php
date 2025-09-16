<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Costing_rate extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Costing_rate.View';
    protected $addPermission      = 'Costing_rate.Add';
    protected $managePermission = 'Costing_rate.Manage';
    protected $deletePermission = 'Costing_rate.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->template->title('Costing Rate');
        $this->template->page_icon('fa fa-building-o');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');

        $data['dataList'] = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

        history("View data costing rate");
        $this->template->set('results', $data);
        $this->template->title('Costing Rate');
        $this->template->render('index');
    }

    public function saveCostingRate()
    {
        $this->auth->restrict($this->addPermission);
        $session = $this->session->userdata('app_session');
        $username     = $session['id_user'];
        $datetime     = date('Y-m-d H:i:s');

        $detail = $_POST['detail'];


        $ArrayInsert = [];
        foreach ($detail as $val => $value) {
            $ArrayInsert[$val]['code']              = $value['code'];
            $ArrayInsert[$val]['judul']             = $value['judul'];
            $ArrayInsert[$val]['element_costing']   = $value['element_costing'];
            $ArrayInsert[$val]['keterangan']        = $value['keterangan'];
            $ArrayInsert[$val]['rate']              = str_replace(',', '', $value['rate']);
            $ArrayInsert[$val]['element_coa']       = $value['element_coa'];
            $ArrayInsert[$val]['urutan']            = $value['urut'];
            $ArrayInsert[$val]['updated_by']         = $username;
            $ArrayInsert[$val]['updated_date']       = $datetime;
        }

        $this->db->trans_start();
        $this->db->where('deleted_date', NULL);
        $this->db->update('costing_rate', array('deleted_by' => $username, 'deleted_date' => $datetime));

        $this->db->insert_batch('costing_rate', $ArrayInsert);
        $this->db->trans_complete();


        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status    = array(
                'pesan'        => 'Gagal Save Item. Thanks ...',
                'status'    => 0
            );
        } else {
            $this->db->trans_commit();
            $status    = array(
                'pesan'        => 'Success Save Item. invenThanks ...',
                'status'    => 1
            );
            history("Update costing rate");
        }

        echo json_encode($status);
    }
}
