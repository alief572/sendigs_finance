<?php
$this->load->view('include/side_menu');	
?>
<style>
	.myDiv {
		background-color: #d3eefa;
		font-family: verdana;
	}

	.warnaTombol {
		background-color: #286090;
		color: white;
	}

	.warnaTombolExcel {
		background-color: #02723B;
		color: white;
	}

	.warnaTombolPdf {
		background-color: #DE0B0B;
		color: white;
	}
</style>
<section class="content-header">
	<h1>
		<?= $title ?>
	</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li class="active"><?= $title ?></li>
	</ol>
</section>

<section class="content-header">
	<div class="box box-primary">
		<div class="myDiv">
			<form method="post" action="<?= base_url() ?>index.php/penerimaan/update_invoice" autocomplete="off">
				<div class="row">
					<div class="col-sm-10">
						<div class="col-sm-2">
							<div class="form-group">
								<br>
								<label>Tanggal</label>
								 <input type="date" name="tgl_update" id="tgl_update" class="form-control input-sm" value="<?php echo date('Y-m-d') ?>" >
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<br>
								<label>Kurs</label>
								 <input type="text" name="kurs" id="kurs" class="form-control input-sm divide">
							</div>
						</div>
						<div class="col-sm-5">
							<div class="form-group">
								<br>
								<label> &nbsp;</label><br>
								<input type="submit" name="tampilkan" value="Update Kurs Akhir Bulan" onclick="return check()" class="btn warnaTombol pull-center"> &nbsp;
								</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</section>
<?php $this->load->view('include/footer'); ?>

<link rel="stylesheet" href="<?= base_url() ?>plugins/datepicker/datepicker3.css">
<script src="<?= base_url() ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="<?= base_url() ?>dist/moment.min.js"></script>
<script>
	$(document).ready(function(){
		$(".divide").divide();
	});
	function check() {
		if ($("#bulan_labarugi").val() == "0") {
			alert("Silahkan Pilih Bulan");
			return false;
		} else if ($("#tahun_labarugi").val() == "0") {
			alert("Silahkan Pilih Tahun");
			return false;
		} else if ($("#level").val() == "") {
			alert("Silahkan Pilih Level");
			return false;
		}
	}
</script>