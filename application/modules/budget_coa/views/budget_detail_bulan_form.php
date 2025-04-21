<?php
?>
<style>
.tableFixHead          { overflow: auto; height: 500px; }
.tableFixHead thead th { position: sticky; top: 0; z-index: 1; background-color:#dadada; }
</style>
<div class="nav-tabs-area">
    <!-- /.tab-content -->
    <div class="tab-content">
            <div class="box box-primary">
                <div class="box-body">
				<div class="row table-responsive">
					<div class="col-md-12 tableFixHead">
					<table class="table table-bordered table-condensed">
					<thead>
					<tr>
						<th>COA</th>
						<th>No Perkiraan</th>
						<th>Budget Bulan <?=date('F', strtotime('2020-'.$bulan.'-01'))?></th>
						<th>Year To Date <?=date('F', strtotime('2020-'.$bulan.'-01'))?> <?=$tahun?></th>
						<th>Budget Tahun <?=$tahun?></th>
					</tr>
					</thead>
					<tbody>
					<?php $i=0;
					foreach($data as $record) {
						$i++;?>
						<tr>
							<td><?=$record->no_perkiraan.'</td><td>'.$record->nama_perkiraan; ?></td>
							<td align=right><?= number_format($record->{"bulan_".$bulan});?></td>
							<?php
							$ytd=0;
							for($bln=1;$bln<=$bulan;$bln++){
								$ytd=($ytd+$record->{"bulan_".$bln});
							}
							?>
							<td align=right><?php echo number_format($ytd); ?></td>
							<td align=right><?=number_format($record->total); ?></td>
					<?php } ?>
					</tbody>
					</table>
					</div>
				</div>
				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
						<a class="btn btn-default" href="<?=base_url("budget_coa")?>"><i class="fa fa-undo"> </i>Kembali</a>
						</div>
					</div>
				</div>
    </div>
</div>
