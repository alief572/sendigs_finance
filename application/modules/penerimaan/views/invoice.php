 <div class="box box-primary">
 	<div class="box-body">
 		<form id="data-form" method="post">
 			<div class="form-group row table-responsive">
 				<table class="table table-bordered" width="100%" id="list_invoice">
 					<thead>
 						<tr>
 							<!-- <th width="30%">Tgl</th>
 							<th width="30%">No IPP</th>
 							<th width="30%">No Invoice</th>
 							<th width="30%">Mata Uang</th>
 							<th width="30%">Nama Customer</th>
 							<th width="30%">Total Invoice USD</th>
 							<th width="30%">Total Invoice</th>
 							<th width="30%">Sisa Invoice IDR</th>
 							<th width="30%">Sisa Invoice USD</th>
 							<th width="30%">Sisa Retensi IDR</th>
 							<th width="30%">Sisa Retensi USD</th>
 							<th width="2%" class="text-center">Aksi</th> -->

 							<th class="text-center">No</th>
 							<th class="text-center">Tgl</th>
 							<th class="text-center">No. Invoice</th>
 							<th class="text-center">Mata Uang</th>
 							<th class="text-center">Nama Customer</th>
 							<th class="text-center">Total Invoice</th>
 							<th class="text-center">Action</th>
 						</tr>
 					</thead>
 					<tbody>
 						<?php

							$cust = $results;

							// $invoice = $this->db->query("SELECT a.*, b.nm_customer as nm_customer FROM tr_invoice_header a
							// 				  INNER JOIN customer b ON a.id_customer=b.id_customer WHERE a.id_customer ='$cust' AND ((a.base_cur='IDR' AND a.sisa_invoice_idr >='1')  OR (a.base_cur='USD' AND a.sisa_invoice >'0')) ")->result();

							$this->db->select('a.* , DATE_FORMAT(a.created_on, "%d %M %Y") as tanggal, c.nm_customer, d.currency');
							$this->db->from('tr_invoice_sales a');
							$this->db->join('tr_sales_order b', 'b.no_so = a.id_so');
							$this->db->join('customer c', 'c.id_customer = b.id_customer', 'left');
							$this->db->join('tr_penawaran d', 'd.no_penawaran = b.no_penawaran', 'left');
							$this->db->where('c.id_customer', $cust);
							$this->db->where('a.sts', 1);
							$this->db->where('a.total_bayar < a.nilai_invoice');
							$this->db->group_by('a.id_invoice');
							$invoice = $this->db->get()->result();

							if ($invoice) {
								$no = 1;
								foreach ($invoice as $ks => $vs) {
							?>
 								<tr>
 									<td class="text-center"><?= $no ?></td>
 									<td class="text-center"><?= $vs->tanggal ?></td>
									<td class="text-center"><?= $vs->id_invoice ?></td>
									<td class="text-center"><?= $vs->currency ?></td>
									<td class="text-center"><?= $vs->nm_customer ?></td>
									<td class="text-right"><?= number_format($vs->nilai_invoice, 2) ?></td>
 									<td>
 										<center>
 											<!-- <button id="btn-<?php echo $vs->id_invoice ?>" class="btn btn-warning btn-sm" type="button" onclick="startmutasi('<?php echo $vs->id_invoice ?>', '<?php echo $vs->no_invoice ?>', '<?php echo $vs->no_ipp ?>','<?php echo addslashes($vs->nm_customer) ?>','<?php echo $vs->total_invoice_idr ?>','<?php echo $vs->sisa_invoice_idr ?>','<?php echo $vs->sisa_invoice_retensi2_idr ?>','<?php echo $vs->kurs_jual ?>')">
 												Pilih
 											</button> -->

											<button type="button" class="btn btn-sm btn-success add_invoice add_invoice_<?= $no ?>" data-id_invoice="<?= $vs->id_invoice ?>" data-no="<?= $no ?>"><i class="fa fa-plus"></i> Add</button>
 										</center>
 									</td>
 								</tr>
 						<?php
									$no++;
								}
							}
							?>
 					</tbody>
 				</table>
 			</div>
 	</div>
 </div>








 </script>