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

  /* Card Container */
  .login-card {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 440px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 20px;
    box-shadow: 
      0 25px 50px -12px rgba(0, 0, 0, 0.4),
      0 0 0 1px rgba(255, 255, 255, 0.3);
    padding: 38px 34px 34px 34px;
    animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    text-align: center;
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
    margin-bottom: 20px;
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

  .auth-instruction {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 12.5px;
    color: #0369a1;
    line-height: 1.45;
    margin-bottom: 20px;
    text-align: center;
  }

  .auth-instruction a {
    color: #0284c7;
    font-weight: 600;
    text-decoration: underline;
  }

  /* QR Code Box */
  .qrcode-container {
    background: #ffffff;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 18px;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px auto;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.04);
  }

  .qrcode-container img {
    border-radius: 8px;
    max-width: 180px;
    height: auto;
    display: block;
  }

  .secret-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 12px;
    color: #64748b;
    margin-top: 10px;
    word-break: break-all;
  }

  .secret-box code {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-weight: 700;
    color: #0f172a;
    font-size: 13px;
    letter-spacing: 1px;
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
    text-decoration: none;
  }

  .btn-submit-login:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1.5px);
    box-shadow: 0 8px 22px -3px rgba(37, 99, 235, 0.5);
    color: #ffffff;
    text-decoration: none;
  }

  .btn-submit-login:active {
    transform: translateY(0);
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.35);
    color: #ffffff;
  }

  .login-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 18px;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s ease;
  }

  .login-back-link:hover {
    color: #2563eb;
    text-decoration: none;
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

<div class="login-wrapper">
  <div class="login-card">
    <div class="login-header">
      <div class="login-brand-badge">
        <i class="fa fa-qrcode"></i>
      </div>
      <h1 class="login-title">Setup QR Code 2FA</h1>
      <p class="login-subtitle"><?= !empty($idt->nm_perusahaan) ? htmlspecialchars($idt->nm_perusahaan) : 'SENDIGS SS' ?></p>
    </div>

    <div class="auth-instruction">
      Buka aplikasi <strong><a target="_blank" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=id">Google Authenticator</a></strong> pada ponsel Anda, lalu pindai (scan) kode QR berikut:
    </div>

    <div class="qrcode-container">
      <img src="<?= $qrCodeUrl ?>" alt="QR Code Google Authenticator">
      <?php if (!empty($secret)): ?>
        <div class="secret-box">
          <div>Kode Rahasia Manual:</div>
          <code><?= htmlspecialchars($secret) ?></code>
        </div>
      <?php endif; ?>
    </div>

    <a href="<?= base_url('users/verify_2fa'); ?>" class="btn-submit-login">
      <span>Lanjut Verifikasi OTP</span>
      <i class="fa fa-arrow-right"></i>
    </a>

    <div style="text-align: center;">
      <a href="<?= base_url('logout'); ?>" class="login-back-link">
        <i class="fa fa-arrow-left"></i>
        <span>Kembali ke Halaman Login</span>
      </a>
    </div>
  </div>

  <footer class="login-footer">
    <p>Copyright &copy; <?= !empty($idt->nm_perusahaan) ? htmlspecialchars($idt->nm_perusahaan) : 'SENDIGS SS' ?> <?= date('Y'); ?>. All rights reserved.</p>
  </footer>
</div>