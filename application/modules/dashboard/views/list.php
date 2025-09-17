<?php
$Waktu = date('H:i');
?>
<!--
<div class="box box-primary box-solid">
	<div class="box-header">
		<h3 class="box-title">Aging Piutang Customer Jam <?php echo $Waktu;?></h3>
	</div>
	<div class="box-body ">
		<div class='form-group row'>
			<div class='col-md-3'>
				<div class="info-box bg-green">
					<span class="info-box-icon">
						<i class="fa fa-calculator"></i>
					</span>
					<div class="info-box-content">
						<span class="info-box-text">0 - 15 Hari</span>
						<span class="info-box-number">
						<?php
							$link	="<a href='#' onClick='return view_piutang(\"1\");' style='color:white !important' id='ar_aging_15'>0 Juta</a>";
							echo $link;

						?>
						</span>
					</div>
				</div>
			</div>
			<div class='col-md-3'>
				<div class="info-box bg-blue">
					<span class="info-box-icon">
						<i class="fa fa-money"></i>
					</span>
					<div class="info-box-content">
						<span class="info-box-text">16 - 30 Hari</span>
						<span class="info-box-number">
						<?php
							$link	="<a href='#' onClick='return view_piutang(\"2\");' style='color:white !important' id='ar_aging_30'>0 Juta</a>";
							echo $link;

						?>
						</span>
					</div>
				</div>
			</div>
			<div class='col-md-3'>
				<div class="info-box bg-orange">
					<span class="info-box-icon">
						<i class="fa fa-calendar"></i>
					</span>
					<div class="info-box-content">
						<span class="info-box-text">31 - 60 Hari</span>
						<span class="info-box-number">
						<?php
							$link	="<a href='#' onClick='return view_piutang(\"3\");' style='color:white !important' id='ar_aging_60'>0 Juta</a>";
							echo $link;

						?>
						</span>
					</div>
				</div>
			</div>
			<div class='col-md-3'>
				<div class="info-box bg-maroon">
					<span class="info-box-icon">
						<i class="fa fa-bell"></i>
					</span>
					<div class="info-box-content">
						<span class="info-box-text">61 - 90 Hari</span>
						<span class="info-box-number">
						<?php
							$link	="<a href='#' onClick='return view_piutang(\"4\");' style='color:white !important' id='ar_aging_90'>0 Juta</a>";
							echo $link;

						?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class='form-group row'>
			<div class='col-md-3'>
				<div class="info-box bg-red">
					<span class="info-box-icon">
						<i class="fa fa-bullhorn"></i>
					</span>
					<div class="info-box-content">
						<span class="info-box-text">> 90 Hari</span>
						<span class="info-box-number">
						<?php
							$link	="<a href='#' onClick='return view_piutang(\"5\");' style='color:white !important' id='ar_aging_91'>0 Juta</a>";
							echo $link;

						?>
						</span>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
-->
<div class="modal fade" id="MyDasboardModal">
  <div class="modal-dialog" style="width:85% !important">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="judul"></h4>
      </div>
      <div class="modal-body" id="isi">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div class="modal fade" id="ModalView">
  <div class="modal-dialog"  style='width:55%; '>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="head_title"></h4>
			</div>
			<div class="modal-body" id="view">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
			</div>
			</div>
	</div>
</div>
<script type="text/javascript">
	var base_url			= siteurl;
	var active_controller	= 'dashboard';



	$(function(){
		ambil_data_dashboard();
		//setInterval(ambil_data_dashboard,60000);

	});
	function ambil_data_dashboard(){
		var baseurl=base_url+active_controller+'/json_dashboard';
		$.ajax({
			'url'		: baseurl,
			'type'		: 'get',
			'success'	: function(data){
				var datas				= $.parseJSON(data);
				var piutang_15			= parseInt(datas.ar_umur_15);
				var piutang_30			= parseInt(datas.ar_umur_30);
				var piutang_60			= parseInt(datas.ar_umur_60);
				var piutang_90			= parseInt(datas.ar_umur_90);
				var piutang_91			= parseInt(datas.ar_umur_91);

				$('#ar_aging_15').text(piutang_15.format(0,3,',')+' Juta');
				$('#ar_aging_30').text(piutang_30.format(0,3,',')+' Juta');
				$('#ar_aging_60').text(piutang_60.format(0,3,',')+' Juta');
				$('#ar_aging_90').text(piutang_90.format(0,3,',')+' Juta');
				$('#ar_aging_91').text(piutang_91.format(0,3,',')+' Juta');


			}
		});
	}
	function view_piutang(kode){
		$('#isi').empty();
		$('#judul').text('');
		if(kode==1){
			var ket	= 'List Aging Piutang Customer 0-15 Hari';
		}else if(kode==2){
			var ket	= 'List Aging Piutang Customer 16-30 Hari';
		}else if(kode==2){
			var ket	= 'List Aging Piutang Customer 31-60 Hari';
		}else if(kode==4){
			var ket	= 'List Aging Piutang Customer 61-90 Hari';
		}else if(kode==5){
			var ket	= 'List Aging Piutang Customer > 90 Hari';
		}

		var baseurl=base_url+active_controller+'/get_piutang_dashboard/'+kode;
		$.ajax({
			'url'		: baseurl,
			'type'		: 'get',
			'success'	: function(data){

				$('#judul').text(ket);
				$('#isi').html(data);
				$('#MyDasboardModal').modal('show');
			}
		});
	}

	Number.prototype.format = function(n, x, s, c) {
		var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
			num = this.toFixed(Math.max(0, ~~n));

		return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
	};
</script>
