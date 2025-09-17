<?php
	$data_session	= $this->session->userdata;
	$username = $data_session['ORI_User']['username'];
	$datetime = date('Y-m-d H:i:s');
	$get_max = $this->db->select('MIN(tahun) AS tahun')->get_where('asset_generate',array('flag'=>'N','category <>'=>'9'))->result();
	$TAHUN = $get_max[0]->tahun;
	$get_max2 = $this->db->select('MIN(bulan) AS bulan')->get_where('asset_generate',array('flag'=>'N','category <>'=>'9','tahun'=>$TAHUN))->result();
	$BULAN = $get_max2[0]->bulan;

	// echo $BULAN;
	$errors='';
	$SQL = "SELECT
				category AS category,
				nm_category AS nm_category,
				sum( nilai_susut ) AS sisa_nilai,
				kdcab AS kdcab
			FROM
				asset_generate
			WHERE
				(
					bulan = '".$BULAN."' 
					AND tahun = '".$TAHUN."' 
					and category <> '9'
				) 
			GROUP BY 
				category, 
				kdcab";
	// echo $SQL;
        $SQL = "SELECT
                    a.category AS category,
                    a.nm_category AS nm_category,
                    sum( a.nilai_susut ) AS sisa_nilai,
                    a.kdcab AS kdcab,
					c.coa,
					c.coa_kredit
                FROM
                    asset_generate a
					LEFT JOIN asset b ON a.kd_asset=b.kd_asset
					LEFT JOIN asset_coa c ON b.id_coa=c.id
                WHERE
                    (a.bulan = '$BULAN' AND a.tahun = '$TAHUN' and a.flag='N') AND b.deleted_date IS NULL
                GROUP BY 
                    a.category, 
                    a.kdcab,
					c.coa,
					c.coa_kredit";	
	$ArrJurnal = $this->db->query($SQL)->result_array();
	
	$ArrDebit = array();
	$ArrKredit = array();
	$ArrJavh = array();
	$Loop = 0;
	$TANGGAL = $TAHUN.'-'.$BULAN.'-25';
/*
	foreach($ArrJurnal AS $val => $valx){
		$coa_category = $this->db->query("select * from asset_category where id='".$valx['category']."' and deleted='N'")->result();
		$totalall=0;
		if($coa_category){
			foreach($coa_category as $rec){
				$array_debit=explode(";",$rec->coa_debit);
				foreach ($array_debit as $coa_rec){
					$Loop++;
					$array_coa=explode("/",$coa_rec);
					$totalrow=floor($valx['sisa_nilai']*$array_coa[1]/100);
					$coa_data=$this->db->query("select * from ".DBACC.".coa_master where no_perkiraan='".$array_coa[0]."'")->row();
					$ArrDebit[$Loop]['id_category'] 	= $valx['category'];
					$ArrDebit[$Loop]['category'] 		= $valx['nm_category'];
					$ArrDebit[$Loop]['tipe'] 			= "JV";
					$ArrDebit[$Loop]['nomor'] 			= $Loop;
					$ArrDebit[$Loop]['tanggal'] 		= $TANGGAL;
					$ArrDebit[$Loop]['no_perkiraan'] 	= $coa_data->no_perkiraan;
					$ArrDebit[$Loop]['keterangan'] 		= $coa_data->nama;
					$ArrDebit[$Loop]['kdcab'] 			= $valx['kdcab'];
					$ArrDebit[$Loop]['debet'] 			= $totalrow;
					$ArrDebit[$Loop]['kredit'] 			= 0;
					$ArrDebit[$Loop]['created_by'] 		= $username;
					$ArrDebit[$Loop]['created_date'] 	= $datetime;
					$totalall=($totalall+$totalrow);
				}
			}
		}else{
			$Loop++;
			$ArrDebit[$Loop]['id_category'] 	= $valx['category'];
			$ArrDebit[$Loop]['category'] 		= $valx['nm_category'];
			$ArrDebit[$Loop]['tipe'] 			= "JV";
			$ArrDebit[$Loop]['nomor'] 			= $Loop;
			$ArrDebit[$Loop]['tanggal'] 		= $TANGGAL;
			$ArrDebit[$Loop]['no_perkiraan'] 	= '0000-00-00';
			$ArrDebit[$Loop]['keterangan'] 		= "BELUM DI SETTING";
			$ArrDebit[$Loop]['kdcab'] 			= $valx['kdcab'];
			$ArrDebit[$Loop]['debet'] 			= $valx['sisa_nilai'];
			$ArrDebit[$Loop]['kredit'] 			= 0;
			$ArrDebit[$Loop]['created_by'] 		= $username;
			$ArrDebit[$Loop]['created_date'] 	= $datetime;
			$errors='error';
		}
		if($coa_category){
			foreach($coa_category as $rec){
				$array_kredit=explode(";",$rec->coa_kredit);
				foreach ($array_kredit as $coa_rec){
					$Loop++;
					$array_coa=explode("/",$coa_rec);
					$coa_data=$this->db->query("select * from ".DBACC.".coa_master where no_perkiraan='".$array_coa[0]."'")->row();
					$ArrKredit[$Loop]['id_category'] 	= $valx['category'];
					$ArrKredit[$Loop]['category'] 		= $valx['nm_category'];
					$ArrKredit[$Loop]['tipe'] 			= "JV";
					$ArrKredit[$Loop]['nomor'] 			= $Loop;
					$ArrKredit[$Loop]['tanggal'] 		= $TANGGAL;
					$ArrKredit[$Loop]['no_perkiraan'] 	= $coa_data->no_perkiraan;
					$ArrKredit[$Loop]['keterangan'] 	= $coa_data->nama;
					$ArrKredit[$Loop]['kdcab'] 			= $valx['kdcab'];
					$ArrKredit[$Loop]['debet'] 			= 0;
					$ArrKredit[$Loop]['kredit'] 		= floor($totalall*$array_coa[1]/100);
					$ArrKredit[$Loop]['created_by'] 	= $username;
					$ArrKredit[$Loop]['created_date'] 	= $datetime;
				}
			}
		}else{
			$Loop++;
			$ArrKredit[$Loop]['id_category'] 	= $valx['category'];
			$ArrKredit[$Loop]['category'] 		= $valx['nm_category'];
			$ArrKredit[$Loop]['tipe'] 			= "JV";
			$ArrKredit[$Loop]['nomor'] 			= $Loop;
			$ArrKredit[$Loop]['tanggal'] 		= $TANGGAL;
			$ArrKredit[$Loop]['no_perkiraan'] 	= '0000-00-00';
			$ArrKredit[$Loop]['keterangan']		= "BELUM DI SETTING";
			$ArrKredit[$Loop]['kdcab'] 			= $valx['kdcab'];
			$ArrKredit[$Loop]['debet'] 			= 0;
			$ArrKredit[$Loop]['kredit'] 		= $valx['sisa_nilai'];
			$ArrKredit[$Loop]['created_by'] 	= $username;
			$ArrKredit[$Loop]['created_date'] 	= $datetime;
			$errors='error';
		}
	}
*/
if(!empty($ArrJurnal)) {
	foreach($ArrJurnal AS $val => $valx){
		$Loop++;
		$ArrDebit[$Loop]['id_category'] 	= $valx['category'];
		$ArrDebit[$Loop]['category'] 		= $valx['nm_category'];
		$ArrDebit[$Loop]['tipe'] 			= "JV";
		$ArrDebit[$Loop]['nomor'] 			= $Loop;
		$ArrDebit[$Loop]['tanggal'] 		= $TANGGAL;
		$ArrDebit[$Loop]['no_perkiraan'] 	= $valx['coa'];
		$ArrDebit[$Loop]['keterangan'] 		= $valx['nm_category'];
		$ArrDebit[$Loop]['kdcab'] 			= $valx['kdcab'];
		$ArrDebit[$Loop]['debet'] 			= $valx['sisa_nilai'];
		$ArrDebit[$Loop]['kredit'] 			= 0;
		$ArrDebit[$Loop]['created_by'] 		= $username;
		$ArrDebit[$Loop]['created_date'] 	= $datetime;

		$ArrKredit[$Loop]['id_category'] 	= $valx['category'];
		$ArrKredit[$Loop]['category'] 		= $valx['nm_category'];
		$ArrKredit[$Loop]['tipe'] 			= "JV";
		$ArrKredit[$Loop]['nomor'] 			= $Loop;
		$ArrKredit[$Loop]['tanggal'] 		= $TANGGAL;
		$ArrKredit[$Loop]['no_perkiraan'] 	= $valx['coa_kredit'];
		$ArrKredit[$Loop]['keterangan'] 	= $valx['nm_category'];
		$ArrKredit[$Loop]['kdcab'] 			= $valx['kdcab'];
		$ArrKredit[$Loop]['debet'] 			= 0;
		$ArrKredit[$Loop]['kredit'] 		= $valx['sisa_nilai'];
		$ArrKredit[$Loop]['created_by'] 	= $username;
		$ArrKredit[$Loop]['created_date'] 	= $datetime;
	}
	$this->db->trans_start();
		$this->db->delete('asset_jurnal_temp',array('created_by'=>$username));
		$this->db->insert_batch('asset_jurnal_temp', $ArrDebit);
		$this->db->insert_batch('asset_jurnal_temp', $ArrKredit);
	$this->db->trans_complete();
}
	$ArrTemp = $this->db->query("SELECT a.*, b.nm_branch FROM asset_jurnal_temp a LEFT JOIN asset_branch b ON a.kdcab=b.id_branch WHERE created_by = '$username' ORDER BY a.kdcab ASC, a.category, a.nomor ASC, a.id ASC")->result_array();

?>

	<div class="box-body">
		<?php
			// echo "<b>Set Pembuatan Jurnal : </b>";
			// echo form_input(array('type'=>'text','id'=>'tgl_jurnal','name'=>'tgl_jurnal','class'=>'form-control input-sm', 'style'=>'width:150px;','autocomplete'=>'off','placeholder'=>'Tanggal', 'readonly'=>'readonly'));											
			// echo "<br>";
		?>
		<h4><b>Next Depresiasi Jurnal Bulan <?=date('F',strtotime('2000-'.$BULAN.'-01'));?> <?=$TAHUN;?> </b></h4>
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
							echo "<td align='left'>".$valx['nm_branch']."</td>";
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
		<?php
		//	if ($errors=='') echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'save','content'=>'Save','id'=>'simpanjurnal','style'=>'width:100px; float:right;')).' ';
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
	
	</script>