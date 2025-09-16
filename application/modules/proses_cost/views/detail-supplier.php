<?php
    $ENABLE_ADD     = has_permission('Master_supplier.Add');
    $ENABLE_MANAGE  = has_permission('Master_supplier.Manage');
    $ENABLE_VIEW    = has_permission('Master_supplier.View');
    $ENABLE_DELETE  = has_permission('Master_supplier.Delete');
?>

        <div class="box-body">	
			<div class="row">
			  <div class="col-md-12">
				<legend><h3 style="margin:0px !Important"><label><?= $result['nm_supplier']?></label></h3></legend>
				<table class="table-responsive table-condensed" width="100%" style="line-height:10px">
					<tbody>
						<tr>
							<td width='15%'><label>Supplier ID</label></td>
							<td width='35%'>: <?= $result['id_supplier']?></td>
							<td width='15%'><label>Status</label></td>
							<td width='35%'>: <label class="label label-success"><?= strtoupper($result['activation'])?></label></td>
						</tr>
						<tr>
							<td><label>Telephone</label></td>
							<td>: <?= $result['telephone']?></td>
							<td><label>Country</label></td>
							<td>: <?= $result['id_country']?></td>
						</tr>
						<tr>
							<td><label>Fax.</label></td>
							<td>: <?= $result['fax']?></td>
							<td><label>Currency</label></td>
							<td>: <?= $result['id_currency']?></td>
							
						</tr>
						<tr>
							<td><label>E-mail</label></td>
							<td>: <a href="mailto:<?= $result['email']?>"><?= $result['email']?><sup><i class="fa fa-envelope-o"></i></sup></a></td>
							<td><label>Persion</label></td>
							<td>: <?= $result['persion']?></td>
						</tr>
						<tr>
							<td><label>Website</label></td>
							<td>: <a href="http://<?= $result['website']?>"> <?= $result['website']?> <sup><i class="fa fa-external-link"></i></sup></a></td>
							<td><label>Priduct</label></td>
							<td>: <?= $result['product_category']?></td>
						</tr>
					</tbody>
				</table>
				<br>
				
				<table class="table">
					<tr>
						<td width="40%"><label>Office Address </label></td>
						<td width="20%"><label>NPWP Number</label></td>
						<td ><label>NPWP Address</label></td>
					</tr>
					<tr>
						<td><?= $result['address_office']?></td>
						<td><?= $result['npwp'] == '' ? '-':$result['npwp'];?></td>
						<td><?= $result['npwp_address'] == '' ? '-':$result['npwp_address'];?></td>
					</tr>
				</table>
				<br>
				<legend><label>Note</label></legend>
				<table>
					<tr>
						<td><?= $result['note'] == '' ? '-':$result['note'];?></td>
					</tr>
				</table>
			  <div>
			</div>
		</div>


