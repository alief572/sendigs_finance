<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    min-height: 100vh;
    background: #0b192c;
    color: #1e293b;
    overflow-x: hidden;
  }

  .login-wrapper {
    min-height: 100vh;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 20px;
    position: relative;
    background: 
      radial-gradient(circle at 15% 15%, rgba(37, 99, 235, 0.4) 0%, transparent 45%),
      radial-gradient(circle at 85% 85%, rgba(14, 165, 233, 0.3) 0%, transparent 45%),
      radial-gradient(circle at 50% 50%, rgba(30, 58, 138, 0.25) 0%, transparent 60%),
      linear-gradient(135deg, #091424 0%, #0f233a 50%, #08111e 100%);
  }

  /* Ambient light overlay decoration */
  .login-wrapper::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.015'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
  }

  /* Login Card Container */
  .login-card {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 20px;
    box-shadow: 
      0 25px 50px -12px rgba(0, 0, 0, 0.4),
      0 0 0 1px rgba(255, 255, 255, 0.3);
    padding: 40px 36px 36px 36px;
    animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1);
  }

  @keyframes cardAppear {
    from {
      opacity: 0;
      transform: translateY(20px) scale(0.98);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  /* Header Branding */
  .login-header {
    text-align: center;
    margin-bottom: 28px;
  }

  .login-brand-badge {
    width: 60px;
    height: 60px;
    margin: 0 auto 16px auto;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 26px;
    box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.45);
    transition: transform 0.3s ease;
  }

  .login-brand-badge:hover {
    transform: scale(1.05) rotate(3deg);
  }

  .login-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin-bottom: 4px;
  }

  .login-subtitle {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
  }

  /* Form Elements */
  .form-group-custom {
    margin-bottom: 18px;
    text-align: left;
  }

  .form-label-custom {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
  }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-icon-left {
    position: absolute;
    left: 14px;
    color: #94a3b8;
    font-size: 15px;
    pointer-events: none;
    transition: color 0.2s ease;
    z-index: 2;
  }

  .form-input-custom {
    width: 100%;
    height: 46px;
    padding: 0 16px 0 42px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: #0f172a;
    font-weight: 500;
    transition: all 0.25s ease;
  }

  .form-input-custom::placeholder {
    color: #94a3b8;
    font-weight: 400;
  }

  .form-input-custom:hover {
    border-color: #cbd5e1;
    background: #ffffff;
  }

  .form-input-custom:focus {
    outline: none;
    background: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
  }

  .form-input-custom:focus ~ .input-icon-left {
    color: #2563eb;
  }

  /* Password Toggle Button */
  .password-toggle-btn {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 6px;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
    z-index: 3;
  }

  .password-toggle-btn:hover {
    color: #334155;
  }

  .password-toggle-btn:focus {
    outline: none;
  }

  .form-input-custom.has-toggle {
    padding-right: 44px;
  }

  /* Submit Button */
  .btn-submit-login {
    width: 100%;
    height: 48px;
    margin-top: 10px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 14.5px;
    font-family: inherit;
    font-weight: 600;
    letter-spacing: 0.3px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 6px 16px -2px rgba(37, 99, 235, 0.4);
    transition: all 0.25s ease;
  }

  .btn-submit-login:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1.5px);
    box-shadow: 0 8px 22px -3px rgba(37, 99, 235, 0.5);
  }

  .btn-submit-login:active {
    transform: translateY(0);
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.35);
  }

  .btn-submit-login:disabled {
    opacity: 0.75;
    cursor: not-allowed;
    transform: none;
  }

  /* Shake Animation on Error */
  @keyframes shakeCard {
    0%, 100% { transform: translateX(0); }
    15%, 45%, 75% { transform: translateX(-7px); }
    30%, 60%, 90% { transform: translateX(7px); }
  }

  .login-card.has-error {
    animation: shakeCard 0.5s ease;
  }

  /* Alert Card (Error notification) */
  .login-error-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-left: 4px solid #dc2626;
    border-radius: 10px;
    padding: 12px 14px;
    margin-top: 18px;
    color: #991b1b;
    font-size: 13px;
    line-height: 1.4;
    animation: fadeInDown 0.3s ease;
  }

  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-8px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .login-error-icon {
    font-size: 16px;
    color: #dc2626;
    margin-top: 2px;
    flex-shrink: 0;
  }

  .login-error-content strong {
    display: block;
    font-weight: 700;
    color: #991b1b;
    margin-bottom: 2px;
  }

  /* Footer Copyright */
  .login-footer {
    position: relative;
    z-index: 10;
    margin-top: 24px;
    text-align: center;
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
    letter-spacing: 0.2px;
  }

  @media (max-width: 480px) {
    .login-card {
      padding: 30px 24px 28px 24px;
    }
  }
</style>

<link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert2.min.css') ?>">
<script src="<?= base_url('assets/dist/sweetalert2.min.js') ?>"></script>

<?php
// Ambil raw message sebelum dikonsumsi Template::message()
$raw = Template::get_message_raw();
// Bersihkan session via Template::message()
Template::message();

$has_error = false;
$error_title = "Login Gagal";
$error_msg = "";

if (!empty($raw) && in_array($raw['type'], array('danger', 'error', 'warning'))) {
  $has_error = true;
  $raw_msg = $raw['message'];
  if (stripos($raw_msg, 'username not found') !== false || stripos($raw_msg, 'login_fail') !== false) {
    $error_title = "Username Tidak Ditemukan";
    $error_msg = "Username yang Anda masukkan tidak terdaftar. Mohon periksa kembali.";
  } elseif (stripos($raw_msg, 'wrong password') !== false) {
    $error_title = "Password Salah";
    $error_msg = "Password yang Anda masukkan salah. Pastikan penulisan huruf besar/kecil sudah benar.";
  } elseif (stripos($raw_msg, 'not active') !== false) {
    $error_title = "Akun Non-Aktif";
    $error_msg = "Akun Anda saat ini berstatus non-aktif. Silakan hubungi Administrator.";
  } elseif (stripos($raw_msg, 'removed') !== false || stripos($raw_msg, 'deleted') !== false) {
    $error_title = "Akun Telah Dinonaktifkan";
    $error_msg = "Akun Anda telah dinonaktifkan dari sistem. Silakan hubungi Administrator.";
  } else {
    $error_title = "Autentikasi Gagal";
    $error_msg = $raw_msg;
  }
}
?>

<div class="login-wrapper">
  <div class="login-card <?= $has_error ? 'has-error' : '' ?>">
    <div class="login-header">
      <div class="login-brand-badge">
        <i class="fa fa-cubes"></i>
      </div>
      <h1 class="login-title"><?= !empty($idt->nm_perusahaan) ? htmlspecialchars($idt->nm_perusahaan) : 'SENDIGS SS' ?></h1>
      <p class="login-subtitle">Finance & Accounting Management System</p>
    </div>

    <?= form_open($this->uri->uri_string(), array('id' => 'frm_login', 'name' => 'frm_login', 'class' => 'login-form', 'autocomplete' => 'off')) ?>
      <input type="hidden" name="login" value="1">
      
      <div class="form-group-custom">
        <label class="form-label-custom" for="username">Username</label>
        <div class="input-wrapper">
          <input 
            type="text" 
            name="username" 
            id="username" 
            class="form-input-custom" 
            placeholder="Masukkan username anda" 
            value="<?= set_value('username') ?>" 
            required 
            autofocus
          >
          <i class="fa fa-user input-icon-left"></i>
        </div>
      </div>

      <div class="form-group-custom">
        <label class="form-label-custom" for="password">Password</label>
        <div class="input-wrapper">
          <input 
            type="password" 
            name="password" 
            id="password" 
            class="form-input-custom has-toggle" 
            placeholder="Masukkan password anda" 
            required
          >
          <i class="fa fa-lock input-icon-left"></i>
          <button type="button" class="password-toggle-btn" id="btnTogglePassword" tabindex="-1" title="Lihat/Sembunyikan Password">
            <i class="fa fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit-login" id="btnSubmitLogin" name="login" value="1">
        <span>Masuk ke Sistem</span>
        <i class="fa fa-arrow-right"></i>
      </button>

    </form>

    <?php if ($has_error): ?>
      <div class="login-error-alert">
        <div class="login-error-icon">
          <i class="fa fa-exclamation-circle"></i>
        </div>
        <div class="login-error-content">
          <strong><?= htmlspecialchars($error_title) ?></strong>
          <span><?= htmlspecialchars($error_msg) ?></span>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <footer class="login-footer">
    <p>Copyright &copy; <?= !empty($idt->nm_perusahaan) ? htmlspecialchars($idt->nm_perusahaan) : 'SENDIGS SS' ?> <?= date('Y'); ?>. All rights reserved.</p>
  </footer>
</div>

<script>
  $(document).ready(function() {
    <?php if ($has_error): ?>
      // SweetAlert2 notification popup
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: '<?= addslashes($error_title) ?>',
          text: '<?= addslashes($error_msg) ?>',
          confirmButtonText: 'Coba Lagi',
          confirmButtonColor: '#2563eb'
        });
      }
    <?php endif; ?>

    // Show/Hide Password Interactive Toggle
    $('#btnTogglePassword').on('click', function(e) {
      e.preventDefault();
      var passwordInput = $('#password');
      var icon = $('#toggleIcon');
      
      if (passwordInput.attr('type') === 'password') {
        passwordInput.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
      } else {
        passwordInput.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
      }
    });

    // Handle button visual loading state on submit without disabling element prematurely
    $('#frm_login').on('submit', function() {
      var btn = $('#btnSubmitLogin');
      btn.css({'pointer-events': 'none', 'opacity': '0.85'}).html('<i class="fa fa-spinner fa-spin"></i> <span>Memvalidasi...</span>');
    });
  });
</script>