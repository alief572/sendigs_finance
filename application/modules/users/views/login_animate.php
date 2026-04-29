<!--<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title><? php // echo $idt->nm_perusahaan; 
          ?></title>  -->
<style>
  /* NOTE: The styles were added inline because Prefixfree needs access to your styles and they must be inlined if they are on local disk! */
  body {
    font-family: "Open Sans", sans-serif;
    height: 100vh;
    background: url("<?php echo base_url(); ?>assets/img/wallpaper.jpg") 50% fixed;
    background-size: cover;
  }

  @keyframes spinner {
    0% {
      transform: rotateZ(0deg);
    }

    100% {
      transform: rotateZ(359deg);
    }
  }

  * {
    box-sizing: border-box;
  }

  .wrapper {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    width: 100%;
    min-height: 100%;
    padding: 20px;
    background: rgba(4, 40, 68, 0.85);
  }

  .login {
    border-radius: 2px 2px 5px 5px;
    padding: 10px 20px 20px 20px;
    width: 90%;
    max-width: 320px;
    background: #ffffff;
    position: relative;
    padding-bottom: 80px;
    box-shadow: 0px 1px 5px rgba(0, 0, 0, 0.3);
  }

  .login.loading button {
    max-height: 100%;
    padding-top: 50px;
  }

  .login.loading button .spinner {
    opacity: 1;
    top: 40%;
  }

  .login.ok button {
    background-color: #8bc34a;
  }

  .login.ok button .spinner {
    border-radius: 0;
    border-top-color: transparent;
    border-right-color: transparent;
    height: 20px;
    animation: none;
    transform: rotateZ(-45deg);
  }

  .login input {
    display: block;
    padding: 15px 10px;
    margin-bottom: 10px;
    width: 100%;
    border: 1px solid #ddd;
    transition: border-width 0.2s ease;
    border-radius: 2px;
    color: #ccc;
  }

  .login input+i.fa {
    color: #fff;
    font-size: 1em;
    position: absolute;
    margin-top: -47px;
    opacity: 0;
    left: 0;
    transition: all 0.1s ease-in;
  }

  .login input:focus {
    outline: none;
    color: #444;
    border-color: #2196F3;
    border-left-width: 35px;
  }

  .login input:focus+i.fa {
    opacity: 1;
    left: 30px;
    transition: all 0.25s ease-out;
  }

  .login a {
    font-size: 0.8em;
    color: #2196F3;
    text-decoration: none;
  }

  .login .title {
    color: #444;
    font-size: 1.2em;
    font-weight: bold;
    margin: 10px 0 30px 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
  }

  .login button {
    width: 100%;
    height: 100%;
    padding: 10px 10px;
    background: #2196F3;
    color: #fff;
    display: block;
    border: none;
    margin-top: 20px;
    position: absolute;
    left: 0;
    bottom: 0;
    max-height: 60px;
    border: 0px solid rgba(0, 0, 0, 0.1);
    border-radius: 0 0 2px 2px;
    transform: rotateZ(0deg);
    transition: all 0.1s ease-out;
    border-bottom-width: 7px;
  }

  .login button .spinner {
    display: block;
    width: 40px;
    height: 40px;
    position: absolute;
    border: 4px solid #ffffff;
    border-top-color: rgba(255, 255, 255, 0.3);
    border-radius: 100%;
    left: 50%;
    top: 0;
    opacity: 0;
    margin-left: -20px;
    margin-top: -20px;
    animation: spinner 0.6s infinite linear;
    transition: top 0.3s 0.3s ease, opacity 0.3s 0.3s ease, border-radius 0.3s ease;
    box-shadow: 0px 1px 0px rgba(0, 0, 0, 0.2);
  }

  .login:not(.loading) button:hover {
    box-shadow: 0px 1px 3px #2196F3;
  }

  .login:not(.loading) button:focus {
    border-bottom-width: 4px;
  }

  /* Login Alert Card */
  .login-alert {
    display: flex;
    align-items: flex-start;
    width: 90%;
    max-width: 320px;
    margin-top: 16px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-left: 5px solid #e65100;
    border-radius: 4px;
    padding: 14px 16px;
    box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.25);
    animation: fadeInDown 0.3s ease;
  }

  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .login-alert-icon {
    font-size: 1.4em;
    color: #e65100;
    margin-right: 12px;
    padding-top: 2px;
    flex-shrink: 0;
  }

  .login-alert-body {
    color: #5d4037;
    font-size: 0.88em;
    line-height: 1.4;
  }

  .login-alert-body strong {
    display: block;
    font-size: 1em;
    margin-bottom: 4px;
    color: #bf360c;
  }

  .login-alert-body p {
    margin: 0;
  }

  footer {
    display: block;
    padding-top: 50px;
    text-align: center;
    color: #ddd;
    font-weight: normal;
    text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.2);
    font-size: 0.8em;
  }

  footer a,
  footer a:link {
    color: #fff;
    text-decoration: none;
  }
</style>
<!--</head>

<body>-->

<div class="wrapper">
  <?= form_open($this->uri->uri_string(), array('id' => 'frm_login', 'name' => 'frm_login', 'class' => 'login', 'autocomplete' => 'off')) ?>
  <p class="title">Log in</p>
  <input type="text" name="username" placeholder="Username" value="<?= set_value('username') ?>" required autofocus>
  <i class="fa fa-user"></i>
  <input type="password" name="password" placeholder="Password" value="" required>
  <i class="fa fa-key"></i>
  <button type="submit" class="btn" name="login">Sign In</button>
  </form>

  <?php
  // Ambil raw message sebelum dikonsumsi Template::message()
  $raw = Template::get_message_raw();
  // Bersihkan session via Template::message() (tidak ditampilkan)
  Template::message();
  if (!empty($raw) && in_array($raw['type'], array('danger', 'error', 'warning'))): ?>
    <div class="login-alert">
      <div class="login-alert-icon">
        <i class="fa fa-exclamation-triangle"></i>
      </div>
      <div class="login-alert-body">
        <strong>Login Gagal</strong>
        <p><?= htmlspecialchars($raw['message'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>
    </div>
  <?php endif; ?>

  <footer>
    <font color="white">
      <p>Copyright &copy; <?php echo $idt->nm_perusahaan; ?> <?= date('Y'); ?></p>
    </font>
  </footer>
</div>
<script src="<?php echo base_url(); ?>assets/login/js/index.js"></script>
</body>

</html>