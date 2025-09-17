
<div class="box-body"> 
	<br>
	<input type='hidden' id='no_rfq' name='no_rfq' value='<?=$no_rfq;?>'>
	<input type='hidden' id='category' name='category' value='<?=$category;?>'>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center mid">SUPPLIER NAME</th>
				<th class="text-center mid">MATERIAL NAME</th>
				<th class="text-center mid" width='10%'>PRICE REF</th>
				<th class="text-right mid" width='10%'>NET PRICE</th>
				<th class="text-center mid" width='10%'>MOQ</th>
				<th class="text-center mid" width='10%'>LEAD TIME</th>
				<th class="text-center mid" width='10%'>QTY</th>
				<th class="text-center mid" width='10%'>TOTAL HARGA</th>
				<th class="text-center mid" width='10%'>KOMITE</th>
				<th class="text-center mid" width='5%'>CHECK</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$no=0;
            foreach($result AS $val => $valx){ $no++;
				$nm_material = $valx['nm_barang'];
                echo "<tr>";
					echo "<td class='mid' >".strtoupper($valx['nm_supplier'])."</td>";
					echo "<td class='mid' >".strtoupper($nm_material)."</td>";
					echo "<td class='text-center mid'>".number_format($valx['price_ref'],2)."</td>";
					echo "<td class='text-right mid'>".number_format($valx['harga_idr'],2)." <span class='text-primary text-bold'>".strtoupper($valx['currency'])."</span></td>";
					echo "<td class='text-center mid'>".number_format($valx['moq'],2)."</td>";
					echo "<td class='text-center mid'>".number_format($valx['lead_time'],2)."</td>";
					echo "<td class='text-center mid'>".number_format($valx['qty'],2)."</td>";
					echo "<td class='text-right mid'>".number_format($valx['harga_idr'] * $valx['qty'],2)."</td>";
					echo "<td class='text-center mid'><b>".strtoupper($valx['status'])."</b></td>";
					echo "<td class='text-center mid'><input type='checkbox' name='check[".$valx['id']."]' class='chk_personal' value='".$valx['id']."'></td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<br>
    <div class='form-group row'>              
        <div class='col-sm-2'><label>Alasan Pemilihan</label></div>
        <div class='col-sm-5'>
            <textarea class='form-control input-md' name='alasan_pemilihan' rows='2' placeholder='Alasan Pemilihan'><?= $header[0]->alasan_pemilihan;?></textarea>
        </div>
    </div>
    <div class='form-group row'>              
        <div class='col-sm-2'><label>Team Seleksi</label></div>
        <div class='col-sm-5'>
            <textarea class='form-control input-md' name='team_seleksi' rows='2' placeholder='Team Seleksi'><?= $header[0]->team_seleksi;?></textarea>
        </div>
    </div>
	<?php
		echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 15px 0px 0px 0px;','value'=>'approve','content'=>'Approve','id'=>'saveAju')).' ';
	?>
</div>
<style>
	.mid{
		vertical-align: middle !important;
	}
	.chosen-container{
		width: 100% !important;
		text-align : left !important;
	}
</style>
<script>
	$(document).ready(function(){
		swal.close();
		$('.chosen-select').chosen();
	});
	
	
</script>