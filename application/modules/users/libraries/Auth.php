<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This library for authentication user
 */
class Auth
{
    protected $ci;
    protected $user;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->library('session');
        $this->ci->lang->load('users/users');
        $this->ci->load->model(array(
            'users/users_model',
            'users/user_groups_model'
        ));

        $this->user = $this->ci->session->userdata('app_session');
    }

    public function is_login()
    {
        return ($this->user) ? TRUE : FALSE;
    }

    public function user_id()
    {
        return isset($this->user['id_user']) ? $this->user['id_user'] : '';
    }

    public function user_name()
    {
        return $this->user['username'];
    }

    public function user_cab()
    {
        return $this->user['kdcab'];
    }

    public function nama()
    {
        return $this->user['nm_lengkap'];
    }

    public function userdata()
    {
        $userdata =  $this->ci->users_model->select(array("users.*"))
            ->find($this->user_id());
        $user_groups = "";

        if ($this->is_admin()) {
            $user_groups = "Administrator";
        } else {
            $user_groups = $this->get_user_groups();
        }

        $userdata->groups = $user_groups;

        return $userdata;
    }

    public function login($username = "", $password = "")
    {
        if ($this->is_login()) {
            redirect('/');
            // redirect('https://sentral.dutastudy.com/metalsindo_dev/');
        }

        $user     = $this->ci->users_model->find_by(array('username' => $username));

        if (!$user) {
            $this->ci->template->set_message(lang('users_login_fail'), 'danger');
            return FALSE;
        }

        if ($user->deleted == 1) {
            $this->ci->template->set_message(lang('users_already_deleted'), 'danger');
            return FALSE;
        }

        if ($user->st_aktif == 0) {
            $this->ci->template->set_message(lang('users_not_active'), 'danger');
            return FALSE;
        }

        if (password_verify($password, $user->password)) {
            //Buat Session
            $array = array();
            foreach ($user as $key => $usr) {
                $array[$key] = $usr;
            }

            $this->ci->session->set_userdata('app_session', $array);
            //Set User Data
            $this->user = $this->ci->session->userdata('app_session');

            //Update Login Terakhir
            $ip_address = ($this->ci->input->ip_address()) == "::1" ? "127.0.0.1" : $this->ci->input->ip_address();
            $check_last_login = $this->ci->users_model->find($this->user_id());
            $msg = "Halo, Selamat datang <strong>$check_last_login->nm_lengkap</strong>, Ini pertama kalinya kamu login di CSJ ERP sistem. <br>" . '<i>"' . $this->quot_msg($check_last_login) . '"</i>';
            if ($check_last_login->login_terakhir) {
                $msg = "Hai <strong>$check_last_login->nm_lengkap</strong>, Selamat datang kembali. <br>" . '<i>"' . $this->quot_msg($check_last_login) . '"</i>';
            }

            $this->ci->template->set_message($msg, 'info');
            $this->ci->users_model->update($this->user_id(), array('login_terakhir' => date('Y-m-d H:i:s'), 'ip' => $ip_address));

            $requested_page = $this->ci->session->userdata('requested_page');
            if ($requested_page != '') {
                //redirect($requested_page);
                redirect("/");
            }

            redirect("/");
        }

        $this->ci->template->set_message(lang('users_wrong_password'), 'danger');
        $this->ci->template->message();
        return FALSE;
    }

    public function logout()
    {
        $this->ci->session->sess_destroy();
        redirect('login');
    }

    public function is_admin()
    {
        $id = $this->user_id();

        $data = $this->ci->users_model->join('user_groups', 'users.id_user = user_groups.id_user')
            ->find_by(array('users.id_user' => $id, 'id_group' => 1));

        if ($data) {
            return TRUE;
        }

        return FALSE;
    }

    public function get_user_groups()
    {
        $id = $this->user_id();

        $groups = $this->ci->user_groups_model->select("user_groups.id_group, groups.nm_group")
            ->join('groups', 'user_groups.id_group = groups.id_group')
            ->order_by('nm_group', 'ASC')
            ->find_all_by(array('id_user' => $id));

        $return = "";
        $arr    = array();
        if ($groups) {
            foreach ($groups as $key => $gr) {
                $arr[] = ucwords($gr->nm_group);
            }

            $return = implode(", ", $arr);
        }

        return $return;
    }

    public function has_permission($nm_permission = "")
    {
        if ($nm_permission == "") {
            return FALSE;
        }

        if ($this->is_admin()) {
            return TRUE;
        }

        $id = $this->user_id();

        $group_permissions = $this->ci->users_model->join('user_groups', 'users.id_user = user_groups.id_user')
            ->join('group_permissions', 'user_groups.id_group = group_permissions.id_group')
            ->join('permissions', 'group_permissions.id_permission = permissions.id_permission')
            ->find_by(array('nm_permission' => $nm_permission, 'users.id_user' => $id));
        if ($group_permissions) {
            return TRUE;
        }

        $user_permissions = $this->ci->users_model->join('user_permissions', 'users.id_user = user_permissions.id_user')
            ->join('permissions', 'user_permissions.id_permission = permissions.id_permission')
            ->find_by(array('nm_permission' => $nm_permission, 'users.id_user' => $id));
        if ($user_permissions) {
            return TRUE;
        }

        return FALSE;
    }

    public function restrict($permission = null, $uri = null) // This function copied from bonfire with modification
    {
        // If user isn't logged in, redirect to the login page.
        if ($this->is_login() === false) {
            $this->ci->template->set_message(lang('users_must_login'), 'error');
            redirect('login');
        }

        // Check whether the user has the proper permissions.
        if (empty($permission) || $this->has_permission($permission)) {
            return true;
        }

        // If the user is logged in, but does not have permission...
        // If $uri is not set, get the previous page from the session.
        if (! $uri) {
            $uri      = $this->ci->session->userdata('previous_page');
            $req_page = $this->ci->session->userdata('requested_page');

            // If previous page and current page are the same, but the user no longer
            // has permission, redirect to site URL to prevent an infinite loop.
            if ($uri == $req_page) {
                $uri = site_url();
            }
        }

        // Inform the user of the lack of permission and redirect.
        $this->ci->template->set_message(lang('users_no_permission'), 'error');
        redirect($uri);
    }

    function quot_msg($data)
    {
        $ArrMsg = [
            "Majulah dan lakukan yang terbaik hari ini, Semangat!!",
            "Sebuah tindakan adalah dasar untuk mencapai kesuksesan. Kerja, kerja, kerja, hingga sukses ada di depan mata.",
            "Kerja keras, kerja cerdas, dan kerja ikhlas untuk mencapai kesuksesan. Selamat bekerja, Kawan.",
            "Happy working! Semangat terus kerjanya! Semoga pekerjaanmu dilancarkan, diberi rejeki yang melimpah dan berkah. Keep spirit!",
            "Selamat pagi semesta. Terima apa yang sudah terjadi, ikhlaskan apa yang tidak bisa diubah, bentuklah apa yang masih bisa diperbaiki. Happy working ",
            "Selamat pagi dunia. Aku selalu siap menerima tantangan yang Kamu berikan di hari yang cerah ini. Selamat beraktivitas!",
            "Selamat pagi all. Mari jemput rezeki dengan sepenuh hati. Jangan lupa ngopi biar lupa rasanya sakit hati. Ahay, semangat kerja!",
            "Jangan buang waktumu dengan melakukan hal yang kurang perlu. Fokuslah pada hal yang mampu membuatmu bertumbuh jadi lebih baik. Selamat bekerja! Fighting!",
            "Jangan remehkan kekuatan doa. Seperti putaran kayuh sepeda, doa akan mengantarkanmu ke tujuan. Selamat pagi, selamat beraktivitas!",
            "Selamat pagi, selamat bekerja, tetap semangat, jangan lupa bersyukur!",
            "Kita dapat sukses apabila kita belajar dari kesalahan. Kerja keras dan berdoalah.",
            "Jangan takut melangkah, ikuti tujuanmu dan kamu pasti akan bisa mencapainya. Selamat bekerja.",
            "Formula dari sebuah kesuksesan adalah kerja keras dan tidak pernah menyerah.",
            "Sukses tidak diukur menggunakan kekayaan, sukses adalah sebuah pencapaian yang kita inginkan. Selamat bekerja.",
            "Untuk mendapatkan kesuksesan, keberanianmu harus lebih besar daripada ketakutanmu. Semangat dan kerja keras dengan tujuanmu.",
            "Kesuksesan selalu disertai dengan kegagalan. Tumbuh dan berkembang untuk pekerjaanmu",

        ];
        $m = rand(0, 15);
        return $ArrMsg[$m];
    }
}
