<?php
?>
<style>
.tableFixHead          { overflow: auto; height: 500px; }
.tableFixHead thead th { position: sticky; top: 0; z-index: 1; background-color:#dadada; }
</style>
<div class="nav-tabs-area">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group ">
							<label class="col-sm-2 control-label">Tahun<font size="4" color="red"><B>*</B></font></label>
							<div class="col-sm-10">
								<div class="input-group">
									<input type="text" class="form-control" id="tahunform" name="tahun" value="" placeholder="tahun" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row table-responsive">
					<div class="col-md-12 tableFixHead">
					<table class="table table-bordered table-condensed">
					<thead>
					<tr>
						<th>COA</th>
						<th>Penanggung Jawab</th>
						<th>Kategori</th>
						<th>Budget Tahun</th>
						<?php
						for($bln=1;$bln<=12;$bln++){
							echo '<th nowrap>Bulan '.$bln.'</th>';
						}
						?>
					</tr>
					</thead>
					<tbody>
					<?php $i=0; $tahun=date("Y");
					foreach($data as $record) {
						$i++;
						if (isset($record->tahun)) $tahun=$record->tahun;?>
						<tr>
							<td>
								<?=$record->no_perkiraan.'<br />'.$record->nama_perkiraan; ?>
							</td>
							<td><?php echo $record->nm_dept;?></td>
							<td><?php echo $record->kategori; ?></td>
							<td align=right>
								<?=(isset($record->total)?number_format($record->total):0); ?>
							</td>
							<?php
							for($bln=1;$bln<=12;$bln++){
								echo '<td  align=right nowrap>'.(isset($record->{"bulan_".$bln})?number_format($record->{"bulan_".$bln}):0).'</td>';
							}
							?>
					<?php } ?>
					</tbody>
					</table>
					</div>
				</div>
				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
						<a class="btn btn-default" data-toggle="modal" onclick="cancel()"><i class="fa fa-undo">&nbsp;</i>Kembali</a>
						</div>
					</div>
				</div>
            <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
		$(".divide").divide();
		$("#tahunform").val('<?=$tahun?>');
		$('#frm_data :input').prop('disabled', true); 
    });

    function cancel(){
        $(".box").show();
        $("#form-data").hide();
    }
</script>
