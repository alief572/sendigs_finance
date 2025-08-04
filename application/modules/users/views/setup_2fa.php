<style>
	body {
		font-family: "Open Sans", sans-serif;
		height: 100vh;
		background: url("<?php echo base_url(); ?>assets/img/wallpaper.jpg") 50% fixed;
		background-size: cover;
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
		color: white;
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

<div class="wrapper">
	<div class="d-flex align-items-center justify-content-center ht-100v">
		<img src="<?= base_url('assets/images/logo_sentral-removebg-preview.png'); ?>" class="wd-100p ht-100p object-fit-cover" alt="">
		<div class="overlay-body bg-black-4 flex-column d-flex align-items-center justify-content-center">

			<?= Template::message(); ?>
			<div class="login-wrapper wd-300 wd-xs-350 pd-25 pd-xs-40 rounded-20 bg-black-5" style="backdrop-filter: blur(5px);">
				<?php if ($this->session->flashdata('error')): ?>
					<p style="color:red"><?= $this->session->flashdata('error') ?></p>
				<?php endif; ?>
				<div class="signin-logo tx-center tx-28 tx-bold tx-white d-flex flex-column flex-center justify-content-center align-items-center">
					<div class="text-normal" style="color: white;">
						2FA QR Code
					</div>
				</div>
				<div class="tx-white-5 tx-center mg-b-20">Scan this qrcode for 2FA verification</div>
				<div class="text-center">
					<img src="<?= $qrCodeUrl ?>" alt="QR Code">
					<br><br>
					<div class="mt-3">
						<a href="<?= base_url('login'); ?>" class="btn btn-success"><i class="fa fa-key" aria-hidden="true"></i> Get OTP Code</a>
					</div>

				</div>
				<footer class="mt-3">
					<div class="text-gray text-center">
						<p>Copyright &copy; <?php echo $idt->nm_perusahaan . "&nbsp;" . date('Y') ?></p>
					</div>
				</footer>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		// Initialize the QR code
		var qrcode = new QRCode(document.getElementById("qrcode"), {
			text: "<?= $qrCodeUrl ?>",
			width: 128,
			height: 128,
			colorDark: "#000000",
			colorLight: "#ffffff",
			correctLevel: QRCode.CorrectLevel.H
		});
	});
</script>