<?php

	$ArrJurnal = $this->db->get('asset_jurnal')->result_array();
	
	$ArrDebit = array();
	$ArrKredit = array();
	$ArrJavh = array();
	$Loop = 0;
	foreach($ArrJurnal AS $val => $valx){
		$Loop++;
		
		if($valx['category'] == 1){
			$coaD 	= "6831-02-01";
			$ketD	= "BIAYA PENYUSUTAN KENDARAAN";
			$coaK 	= "1309-05-01";
			$ketK	= "AKUMULASI PENYUSUTAN KENDARAAN";
		}
		if($valx['category'] == 2){
			$coaD 	= "6831-06-01";
			$ketD	= "BIAYA PENYUSUTAN HARTA LAINNYA";
			$coaK 	= "1309-08-01";
			$ketK	= "AKUMULASI PENYUSUTAN HARTA LAINNYA";
		}
		if($valx['category'] == 3){
			$coaD 	= "6831-01-01";
			$ketD	= "BIAYA PENYUSUTAN BANGUNAN";
			$coaK 	= "1309-07-01";
			$ketK	= "AKUMULASI PENYUSUTAN BANGUNAN";
		}
		
		$ArrDebit[$Loop]['category'] 		= $valx['nm_category'];
		$ArrDebit[$Loop]['tipe'] 			= "JV";
		$ArrDebit[$Loop]['nomor'] 			= $Loop;
		$ArrDebit[$Loop]['tanggal'] 		= date('Y-m-d');
		$ArrDebit[$Loop]['no_perkiraan'] 	= $coaD;
		$ArrDebit[$Loop]['keterangan'] 		= $ketD;
		$ArrDebit[$Loop]['kdcab'] 			= $valx['kdcab'];
		$ArrDebit[$Loop]['debet'] 			= $valx['sisa_nilai'];
		$ArrDebit[$Loop]['kredit'] 			= 0;
		
		$ArrKredit[$Loop]['category'] 		= $valx['nm_category'];
		$ArrKredit[$Loop]['tipe'] 			= "JV";
		$ArrKredit[$Loop]['nomor'] 			= $Loop;
		$ArrKredit[$Loop]['tanggal'] 		= date('Y-m-d');
		$ArrKredit[$Loop]['no_perkiraan'] 	= $coaK;
		$ArrKredit[$Loop]['keterangan'] 	= $ketK;
		$ArrKredit[$Loop]['kdcab'] 			= $valx['kdcab'];
		$ArrKredit[$Loop]['debet'] 			= 0;
		$ArrKredit[$Loop]['kredit'] 		= $valx['sisa_nilai'];
	}
	
	$this->db->trans_start();
		$this->db->truncate('asset_jurnal_temp');
		$this->db->insert_batch('asset_jurnal_temp', $ArrDebit);
		$this->db->insert_batch('asset_jurnal_temp', $ArrKredit);
	$this->db->trans_complete();
	
	$ArrTemp = $this->db->query('SELECT a.*, b.namacabang FROM asset_jurnal_temp a LEFT JOIN cabang b ON a.kdcab=b.kdcab ORDER BY a.kdcab ASC, a.nomor ASC, a.id ASC')->result_array();

?>

	<div class="box-body">
		<?php
			// echo "<b>Set Pembuatan Jurnal : </b>";
			// echo form_input(array('type'=>'text','id'=>'tgl_jurnal','name'=>'tgl_jurnal','class'=>'form-control input-sm', 'style'=>'width:150px;','autocomplete'=>'off','placeholder'=>'Tanggal', 'readonly'=>'readonly'));											
			// echo "<br>";
		?>
		<table id="example1" class="table table-bordered table-striped" width='100%'>
			<thead>
				<tr class='bg-blue' >
				<th class="text-center">Cabang</th>
					<th class="text-center">Kategori</th>
					<th class="text-center">Tanggal</th>
					<th class="text-center">Tipe</th>
					<th class="text-center">No Perkiraan</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Debet</th>
					<th class="text-center">Kredit</th>
					
				</tr>
			</thead>
			<tbody>
				<?php
					$totD	= 0;
					$totK	= 0;
					foreach($ArrTemp AS $val => $valx){
						if($valx['kdcab'] == '100'){
							$colorBack = '#c8ffc5';
						}
						elseif($valx['kdcab'] == '101'){
							$colorBack = '#ffc5c5';
						}
						elseif($valx['kdcab'] == '102'){
							$colorBack = '#dfdbff';
						}
						else{
							$colorBack = '#ebc5ff';
						}
						
						$totD += $valx['debet'];
						$totK += $valx['kredit'];
						
						echo "<tr style='background-color:".$colorBack."'>";
							echo "<td align='left'>".$valx['namacabang']."</td>";
							echo "<td align='left'>".$valx['category']."</td>";
							echo "<td align='center'>".$valx['tanggal']."</td>";
							echo "<td align='center'>".$valx['tipe']."</td>";
							echo "<td align='center'>".$valx['no_perkiraan']."</td>";
							echo "<td>".$valx['keterangan']."</td>";
							echo "<td align='right'>".number_format($valx['debet'])."</td>";
							echo "<td align='right'>".number_format($valx['kredit'])."</td>";
						echo "</tr>";
					}
					echo "<tr style='background-color:#36cdfb'>";
						echo "<td colspan='6'><b>TOTAL PENYUSUTAN</b></td>";
						echo "<td align='right'><b>".number_format($totD)."</b></td>";
						echo "<td align='right'><b>".number_format($totK)."</b></td>";
					echo "</tr>";
				?>
			</tbody>
		</table>
		<br> 
		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'save','content'=>'Save','id'=>'simpan-jurnal','style'=>'width:100px; float:right;')).' ';
		?>
	</div>
	
	<script>
	
	$("#tgl_jurnal").datepicker( {
		format: 'yyyy-mm',
		// dateFormat: 'dd, mm, yy',
		viewMode: "months",
		minViewMode: "months",
		autoClose: true
		// defaultDate: new Date()
	});
	
	
	
	$('#simpan-jurnal').click(function(e){
		e.preventDefault();
		swal({
			  title: "Apakah anda yakin ?",
			  text: "Data akan diproses ke jurnal!",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "Ya, Lanjutkan!",
			  cancelButtonText: "Tidak, Batalkan!",
			  closeOnConfirm: false,
			  closeOnCancel: false
			},
			function(isConfirm) {
			  if (isConfirm) {
					// loading_spinner();
					var formData  	= new FormData($('#form_proses_bro')[0]);
					var baseurl		= siteurl +'asset/saved_jurnal';
					$.ajax({
						url			: baseurl,
						type		: "POST",
						data		: formData,
						cache		: false,
						dataType	: 'json',
						processData	: false, 
						contentType	: false,				
						success		: function(data){								
							if(data.status == 1){											
								swal({
									  title	: "Berhasil Tersimpan !",
									  text	: data.pesan,
									  type	: "success",
									  timer	: 7000
									});
								window.location.href = siteurl + 'asset';
							}
							else{ 
								if(data.status == 0){
									swal({
									  title	: "Gagal Tersimpan !",
									  text	: data.pesan,
									  type	: "warning",
									  timer	: 7000
									});
								}
								else{
									swal({
									  title	: "Terjadi kesalahan saat proses simpan data!",
									  text	: data.pesan,
									  type	: "warning",
									  timer	: 7000,
									  showCancelButton	: false,
									  showConfirmButton	: false,
									  allowOutsideClick	: false
									});
								}
								$('#simpan-jurnal').prop('disabled',false);
							}
						},
						error: function() {
							swal({
							  title				: "Error Message !",
							  text				: 'Terjadi kesalahan saat proses simpan data!',						
							  type				: "warning",								  
							  timer				: 7000,
							  showCancelButton	: false,
							  showConfirmButton	: false,
							  allowOutsideClick	: false
							});
							$('#simpan-jurnal').prop('disabled',false);
						}
					});
			  } else {
				swal("Dibatalkan", "Data dapat diproses kembali", "error");
				$('#simpan-jurnal').prop('disabled',false);
				return false;
			  }
		});
	});
	
	</script>