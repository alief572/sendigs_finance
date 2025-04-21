
<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete='off'> 
<div class="box-body"> 
	<br>
    <input type="hidden" name='no_po' id='no_po' value='<?= $no_po;?>'>
    <input type="hidden" name='id_dept' id='id_dept' value='<?= $id_dept;?>'>
    <input type="hidden" name='id_costcenter' id='id_costcenter' value='<?= $id_costcenter;?>'>
    <input type="hidden" name='tanggal' id='tanggal' value='<?= $tanggal_trans;?>'>
	<input type="hidden" name='pic' id='pic' value='<?= $pic;?>'>
	<input type="hidden" name='note' id='note' value='<?= $note;?>'>
    <input type="hidden" name='adjustment' id='adjustment' value='IN'>
    <div class='form-group row'>
        <label class='label-control col-sm-2'><b>Upload Document</b></label>
        <div class='col-sm-4'>
            <input type='file' name='upload_doc' class='form-control input-sm text-left'>
        </div>
    </div>	
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='3%'>#</th>
                <th class="text-center" style='vertical-align:middle;' width='10%'>Departemen</th>
				<th class="text-center" style='vertical-align:middle;'>Nama Barang/Jasa</th>
				<th class="text-center" style='vertical-align:middle;' width='5%'>Qty</th>
				<th class="text-center" style='vertical-align:middle;' width='5%'>Satuan</th>
                <th class="text-center" style='vertical-align:middle;' width='7%'>Status</th> 
				<th class="text-center" style='vertical-align:middle;' width='12%'>Catatan</th> 
				<th class="text-center" style='vertical-align:middle;' width='12%'>Pemeriksa</th> 
				<!-- <th class="text-center" style='vertical-align:middle;' width='15%'>Upload Doc</th>  -->
			</tr>
		</thead>
		<tbody>
			<?php
			if(!empty($result)){
                $Total1 = 0;
                $Total2 = 0;
                $No=0;
                foreach($result AS $val => $valx){
                    $No++;

                    $cek_type = substr($no_po,0,3);

                    
                    if($cek_type == 'NPO'){
                        $qty = $valx['qty_rev'];
                        $satuan = 'UNIT';
                        $dept = get_name('department','nm_dept','id',$valx['id_dept']);
                        $no_pox = $valx['no_non_po'];
                    }
                    if($cek_type == 'POX'){
                        $qty = $valx['qty_po'];
                        $satuan = 'UNIT';
                        $dept = '';
                        $no_pox = $valx['no_po'];
                    }
                    echo "<tr>";
                        echo "<td align='center'>".$No."
                            <input type='hidden' name='addInMat[$No][no_po]' value='".$no_pox."'>
                            <input type='hidden' name='addInMat[$No][nm_barang]' value='".$valx['nm_barang']."'>
                            <input type='hidden' name='addInMat[$No][spec]' value='".$valx['id_barang']."'>
                            <input type='hidden' name='addInMat[$No][id]' value='".$valx['id']."'>
                            <input type='hidden' name='addInMat[$No][qty_rev]' value='".$qty."'>
                        </td>";
                        echo "<td>".strtoupper($dept)."</td>";
                        echo "<td>".strtoupper($valx['nm_barang'])."</td>";
                        echo "<td align='center'>".number_format($qty)."</td>";
                        echo "<td align='center'>".strtoupper($satuan)."</td>";
                    echo "	<td>
                                <select name='addInMat[$No][status]' class='form-control input-md chosen_select'>
                                    <option value='1'>YES</option>
                                    <option value='2'>NO</option>
                                </select>
                            </td>";
                    echo "<td align='center'><input type='text' name='addInMat[$No][keterangan]' data-no='$No' class='form-control input-sm text-left'></td>";
                    echo "<td align='center'><input type='text' name='addInMat[$No][pemeriksa]' data-no='$No' class='form-control input-sm text-left'></td>";
                    // echo "<td align='center'><input type='file' name='upload_".$No."' data-no='$No' class='form-control input-sm text-left'></td>";
                    echo "</tr>";
                }
			}
			else{
				echo "<tr>";
					echo "<td colspan='3'>Data aktual belum di update, silahkan update data terlebih dahulu.</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
    <?php
		echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Save','content'=>'Save','id'=>'saveINMaterial'));
	?>
</div>
</form>
<style>
	.tanggal{
		cursor: pointer;
	}
</style> 
<script>
	$(document).ready(function(){
        swal.close();
    });
</script>