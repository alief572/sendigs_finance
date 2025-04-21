<?php
    $ENABLE_ADD     = has_permission('ApprovalPembayaran.Add');
    $ENABLE_MANAGE  = has_permission('ApprovalPembayaran.Manage');
    $ENABLE_VIEW    = has_permission('ApprovalPembayaran.View');
    $ENABLE_DELETE     = has_permission('ApprovalPembayaran.Delete');
	$ENABLE_MARKETING  = has_permission('ApprovalPembayaran.Marketing');
?>
<html>
<head>
    <title>Ringkasan Approval Pembayaran</title>
  <style>
  body { font-family: Calibri,Arial; font-size:10px; }
        table { border-collapse:collapse;      
				table-layout:fixed;}    
	.border-all{border: 1px solid black; }
	.border-rl{border-left: 1px solid;border-right: 1px solid;}
	.border-rlb{border-left: 1px solid;border-right: 1px solid;border-bottom: 1px solid;}
    
  </style>	
</head>
<body>
<h3>Ringkasan Approval Pembayaran Non PO Aset</h3>
	<table width="900" border="1" style="border-style:solid;border-width:5px;" cellpadding="2" cellspacing="0">
		<thead>
		<tr>
			<th>No</th>
			<th>Nama Supplier</th>
			<th>No. PP Prisma</th>
			<th>Item Pembayaran</th>
			<th>Nilai Request</th>
		</tr>
		</thead>
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0;
			foreach($results AS $record){ $numb++; 
			$tgl_tempo=date('Y-m-d', strtotime($record->tgl_po. ' + '.$record->top.' days'));
			?> 
		<tr>
			<td><?= $numb ?></td>
			<td><?= $record->vendor_id ?></td>
			<td><?= ($record->no_pp) ?></td>
			<td><?= ($record->notes) ?></td>
			<td><?= number_format($record->request_payment) ?></td>
		</tr>
		<?php }
		}  ?>
		</tbody>
	</table>
</body>
</html>