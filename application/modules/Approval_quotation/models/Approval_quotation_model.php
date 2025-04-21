<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Approval_quotation_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        // $this->code       = 'code_lv1';
    }

    // public function approval_quotation($no_penawaran)
    // {
    //     echo 'awdawd';
    //     // $session = $this->session->userdata('app_session');

    //     // $Cust = $this->db->query("SELECT a.* FROM customer a")->result();
    //     // $User = $this->db->query("SELECT a.* FROM users a")->result();
    //     // $pic_cust = $this->db->query("SELECT a.* FROM customer_pic a WHERE a.nm_pic <> ''")->result();

    //     // $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $session['id_user']])->result();

    //     // if ($no_penawaran !== null) {
    //     //     $get_penawaran = $this->db->query('SELECT a.*, b.nm_lengkap FROM tr_penawaran a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_penawaran = "' . $no_penawaran . '"')->row();
    //     //     $get_penawaran_detail = $this->db->get_where('tr_penawaran_detail', ['no_penawaran' => $no_penawaran])->result();

    //     //     $this->template->set('results', [
    //     //         'customers' => $Cust,
    //     //         'user' => $User,
    //     //         'pic_cust' => $pic_cust,
    //     //         'nm_sales' => $session['nm_lengkap'],
    //     //         'data_penawaran' => $get_penawaran,
    //     //         'data_penawaran_detail' => $get_penawaran_detail
    //     //     ]);
    //     // } else {
    //     //     $this->template->set('results', [
    //     //         'customers' => $Cust,
    //     //         'user' => $User,
    //     //         'pic_cust' => $pic_cust,
    //     //         'list_penawaran_detail' => $get_penawaran_detail,
    //     //         'nm_sales' => $session['nm_lengkap']
    //     //     ]);
    //     // }
    //     // $this->template->render('approval_quotation');
    // }
}
