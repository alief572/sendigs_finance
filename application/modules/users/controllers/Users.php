<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends Front_Controller
{

    protected $id_user;
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
        if ($this->auth->is_login()) {
            history("Login");
            redirect('/');
        }

        //$identitas = $this->identitas_model->find(1); => ERROR variable nama_program not define krn ga ada fieldnya di tabel identitas
        $identitas = $this->identitas_model->find_by(array('ididentitas' => 1)); // By Muhaemin => Di Form Login

        if (isset($_POST['login'])) {
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
        if (!empty($this->id_user)) {
            history("Logout");
        }
        $this->auth->logout();
    }

    public function is_2fa_enabled()
    {
        $user = $this->users_model->find($this->auth->user_id());
        return !empty($user->ga_secret);
    }

    public function setup_2fa()
    {
        if (!$this->input->post()) {
            $user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
            if ($user->ga_secret) {
                $this->template->set_message('2FA sudah diaktifkan. Silakan reset 2FA terlebih dahulu jika ingin mengatur ulang.', 'danger');
                redirect('users/profile');
            }
        }

        // Cek apakah user sudah login
        if (!$this->auth->is_login()) {
            $this->template->set_message('Anda harus login terlebih dahulu untuk mengatur 2FA.', 'danger');
            redirect('users/login');
        }

        $ga = new PHPGangsta_GoogleAuthenticator();

        $secret = $ga->createSecret();

        // Simpan secret ke DB user
        $this->db->update('users', ['ga_secret' => $secret], ['id_user' => $this->auth->user_id()]);
        $user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->nm_lengkap . '@SENDIGS-ERP', $secret);

        $data['secret'] = $secret;
        $data['qrCodeUrl'] = $qrCodeUrl;

        $identitas = $this->identitas_model->find_by(array('ididentitas' => 1)); // By Muhaemin => Di Form Login
        $this->template->set('idt', $identitas);
        // $this->template->set_theme('bracket');
        // $this->template->set_layout('login');
        $this->template->title('Login');
        $this->template->render('setup_2fa', $data);
    }

    public function verify_2fa()
    {
        $identitas = $this->identitas_model->find_by(array('ididentitas' => 1)); // By Muhaemin => Di Form Login
        $user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
        $secret = $user->ga_secret;

        if (!$secret) {
            $this->session->set_flashdata('error', '2FA belum diaktifkan. Silakan aktifkan terlebih dahulu.');
            redirect('users/setup_2fa');
        }

        $this->template->set('idt', $identitas);
        // $this->template->set_theme('bracket');
        // $this->template->set_layout('login');
        $this->template->title('Login');
        $this->template->render('verify_2fa');
    }

    public function confirm_reset_2fa()
    {
        if ($this->input->post()) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            if (!$username || !$password) {
                $this->session->set_flashdata('error', 'Username dan Password tidak boleh kosong.');
                $this->template->render('users/confirm_reset_2fa');
            }
            // Verifikasi username dan password
            $user = $this->db->get_where('users', ['username' => $username])->row();
            if (!$user) {
                $this->session->set_flashdata('error', 'Username tidak ditemukan.');
                $this->template->render('users/confirm_reset_2fa');
            }
            if (!password_verify($password, $user->password)) {
                $this->session->set_flashdata('error', 'Password salah.');
                $this->template->render('users/confirm_reset_2fa');
            }

            $this->template->render('users/reset_2fa');
            return;
        }


        $user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
        $data = [
            'user' => $user,
        ];
        // $this->template->set_theme('bracket');
        // $this->template->set_layout('login');
        // $this->template->set_layout('index');
        $this->template->render('confirm_reset_2fa', $data);
    }

    public function reset_2fa()
    {
        $user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
        if (!$user) {
            $this->session->set_flashdata('error', 'User tidak ditemukan.');
            redirect('users/confirm_reset_2fa');
        }

        // Hapus secret 2FA dari database
        $this->db->update('users', ['ga_secret' => null], ['id_user' => $user->id_user]);
        $this->session->set_userdata('2fa_verified', false);
        $this->template->set_message('2FA telah berhasil direset. Silakan atur ulang 2FA Anda.', 'success');
        redirect('users/profile');
    }

    public function confirm_setup_2fa()
    {
        if ($this->input->post()) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            if (!$username || !$password) {
                $this->session->set_flashdata('error', 'Username dan Password tidak boleh kosong.');
                redirect('users/confirm_setup_2fa');
            }
            // Verifikasi username dan password
            $user = $this->db->get_where('users', ['username' => $username])->row();
            if (!$user) {
                $this->session->set_flashdata('error', 'Username tidak ditemukan.');
                redirect('users/confirm_setup_2fa');
            }
            if (!password_verify($password, $user->password)) {
                $this->session->set_flashdata('error', 'Password salah.');
                redirect('users/confirm_setup_2fa');
            }

            redirect('users/setup_2fa');
            return;
        }


        $user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
        $data = [
            'user' => $user,
        ];
        // $this->template->set_theme('bracket');
        // $this->template->set_layout('login');
        $this->template->render('confirm_setup_2fa', $data);
    }

    public function check_otp()
    {
        $ga = new PHPGangsta_GoogleAuthenticator();

        $otp    = $this->input->post('otp');
        $user   = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
        $secret = $user->ga_secret;

        if (!$secret) {
            $this->session->set_flashdata('error', '2FA belum diaktifkan. Silakan aktifkan terlebih dahulu.');
            redirect('users/setup_2fa');
        }

        if (!$otp) {
            $this->session->set_flashdata('error', 'Kode OTP tidak boleh kosong.');
            $this->template->render('users/verify_2fa');
        }

        // Verifikasi kode OTP
        $checkResult = $ga->verifyCode($secret, $otp, 2); // toleransi waktu 2x30 detik

        if ($checkResult) {
            $this->session->set_userdata('2fa_verified', true);
            redirect('dashboard');
        } else {
            // Jika verifikasi gagal, tampilkan pesan error
            $this->session->set_userdata('2fa_verified', false);
            $this->session->set_flashdata('error', 'Kode OTP salah');
            $this->template->render('users/verify_2fa');
        }
    }
}
