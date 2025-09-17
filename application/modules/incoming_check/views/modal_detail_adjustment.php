<div class="box-body">
	<table border="0" width="100%">
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No PO</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $no_surat; ?></td>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No PR</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $no_pr; ?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $result_header['kode_trans']; ?></td>
				<td colspan="3"></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Penerimaan</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= date('d F Y', strtotime($result_header['created_date'])); ?></td>
				<td colspan="3"></td>
			</tr>
		</thead>
	</table><br>
	<?php
	$exp_no_ipp = explode(',', $result_header['no_ipp']);
	foreach ($exp_no_ipp as $no_ipp) {

		$this->db->select('a.*, b.no_surat, c.konversi, d.code as satuan, e.code as packing');
        $this->db->from('tr_incoming_check_detail a');
        $this->db->join('tr_purchase_order b', 'b.no_po LIKE CONCAT("%",a.no_ipp,"%")', 'left');
		$this->db->join('dt_trans_po f', 'f.id = a.id_po_detail', 'left');
        $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left');
        $this->db->join('ms_satuan d', 'd.id = c.id_unit', 'left');
        $this->db->join('ms_satuan e', 'e.id = c.id_unit_packing', 'left');
        $this->db->where('a.kode_trans', $kode_trans);
		$this->db->where('f.no_po', $no_ipp);	
        $result = $this->db->get()->result_array();

		$get_no_surat = $this->db->select('no_surat')->get_where('tr_purchase_order', ['no_po' => $no_ipp])->row();
	?>
		<b>No. PO : <?= $get_no_surat->no_surat ?></b>
		<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
			<thead id='head_table'>
				<tr class='bg-blue'>
					<th class="text-center">No.</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">Material</th>
					<th class="text-center">Incoming</th>
					<th class="text-center">Unit</th>
					<th class="text-center">Konversi</th>
					<th class="text-center">Packing</th>
					<th class="text-center">Qty Pack</th>
					<th class="text-center">Qty NG</th>
					<th class="text-center">Qty Oke</th>
					<th class="text-center">Qty Pack</th>
					<th class="text-center">Expired Date</th>
					<th class="text-center">Document</th>
					<th class="text-center">Lot Description</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$No = 0;
				foreach ($result as $val => $valx) {
					$No++;

					$packing = $valx['qty_order'];
					if ($valx['konversi'] > 0) {
						$packing = ($valx['qty_order'] / $valx['konversi']);
					}

					// echo '<tr>';
					// echo '<td class="text-center">' . $No . '</td>';
					// echo '<td class="text-center">' . $valx['no_surat'] . '</td>';
					// echo '<td class="text-center">' . $valx['nm_material'] . '</td>';
					// echo '<td class="text-center">' . number_format($valx['qty_incoming'], 2) . '</td>';
					// echo '<td class="text-center">' . strtoupper($valx['unit']) . '</td>';
					// echo '<td class="text-center">' . number_format($valx['konversi'], 2) . '</td>';
					// echo '<td class="text-center">' . number_format($valx['qty_incoming'] / $valx['konversi'], 2) . '</td>';
					// echo '<td class="text-center">' . strtoupper($valx['packing']) . '</td>';
					// echo '<td class="text-center">' . number_format($valx['qty_ng'], 2) . '</td>';
					// echo '<td class="text-center">' . number_format($valx['qty_oke'], 2) . '</td>';
					// echo '<td class="text-center">' . number_format(($valx['qty_incoming'] - $valx['qty_ng']) / $valx['konversi'], 2) . '</td>';
					// echo '<td class="text-center">' . date('d F Y', strtotime($valx['expired_date'])) . '</td>';
					// echo '</tr>';

					echo '<tr>';
					echo '<td class="text-center">' . $No . '</td>';
					echo '<td class="text-center">' . $get_no_surat->no_surat . '</td>';
					echo '<td class="text-center">' . $valx['nm_material'] . '</td>';
					echo '<td class="text-center">' . number_format($valx['qty_order'], 2) . '</td>';
					echo '<td class="text-center">' . strtoupper($valx['satuan']) . '</td>';
					echo '<td class="text-center">' . number_format($valx['konversi'], 2) . '</td>';
					echo '<td class="text-center">' . number_format($packing, 2) . '</td>';
					echo '<td class="text-center">' . $valx['packing'] . '</td>';
					echo '<td class="text-center">-</td>';
					echo '<td class="text-center">-</td>';
					echo '<td class="text-center">-</td>';
					echo '<td class="text-center">-</td>';
					echo '<td class="text-center">-</td>';
					echo '<td class="text-center">-</td>';
					echo '</tr>';

					$get_checked_incoming = $this->db->get_where('tr_checked_incoming_detail', ['kode_trans' => $valx['kode_trans'], 'id_detail' => $valx['id'], 'id_material' => $valx['id_material']])->result_array();
					foreach ($get_checked_incoming as $checked_incoming) :



						echo '<tr>';
						echo '<td colspan="8"></td>';
						echo '<td class="text-center">' . number_format($checked_incoming['qty_ng'], 2) . '</td>';
						echo '<td class="text-center">' . number_format($checked_incoming['qty_oke'], 2) . '</td>';
						echo '<td class="text-center">' . number_format($checked_incoming['qty_pack'], 2) . '</td>';
						echo '<td class="text-center">' . date('d F Y', strtotime($checked_incoming['expired_date'])) . '</td>';
						echo '<td class="text-center">';
						if (file_exists($checked_incoming['uploaded_file'])) {
							echo '<a href="' . base_url($checked_incoming['uploaded_file']) . '" class="btn btn-sm btn-primary" target="_blank">Download File</a>';
						}
						echo '</td>';
							echo '<td>' . $checked_incoming['lot_description'] . '</td>';
							echo '</tr>';
					endforeach;
				}
				?>
			</tbody>
		</table>

	<?php
	}
	?>


	<div class="row">
		<div class="col-md-4" style="margin-top: 1vh;">
			<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
				<thead>
					<tr>
						<th class="text-center">No.</th>
						<th class="text-center">Material</th>
						<th class="text-center">Qty Incoming</th>
						<th class="text-center">Qty NG</th>
						<th class="text-center">Qty OK</th>
					</tr>
				</thead>
				<tbody class="list_summary_material">
					<?php
					$no = 1;
					$stok_tidak_masuk = 0;
					$stok_masuk = 0;
					foreach ($summary_incoming as $summ) :
						echo '<tr>';
						echo '<td class="text-center">' . $no . '</td>';
						echo '<td class="text-center">' . $summ['nm_material'] . '</td>';
						echo '<td class="text-center">' . number_format($summ['qty_order']) . '</td>';
						echo '<td class="text-center">' . number_format($summ['ttl_qty_ng']) . '</td>';
						echo '<td class="text-center">' . number_format($summ['ttl_qty_oke']) . '</td>';
						echo '</tr>';
						$no++;

						$stok_tidak_masuk += $summ['ttl_qty_ng'];
						$stok_masuk += $summ['ttl_qty_oke'];
					endforeach;
					?>
				</tbody>
				<tbody class="list_masuk_stock">
					<tr>
						<td colspan="3" class="text-right">Masuk ke Stock</td>
						<td class="text-center">
							<span style="color: red;"><?= number_format($stok_tidak_masuk) ?></span>
						</td>
						<td class="text-center">
							<span style="color: green;"><?= number_format($stok_masuk) ?></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<?php
	// echo ($file_incoming_material);
	if ($result_header['file_incoming_material'] !== '' && $result_header['file_incoming_material'] !== null) {
		echo '<span>Incoming Material File</span> <br>';
		$exp_file_incoming_material = explode('|', $result_header['file_incoming_material']);
		foreach ($exp_file_incoming_material as $exp_material) {
			if (file_exists($exp_material)) {
				echo '<a href="' . base_url($exp_material) . '" class="" style="margin-top: 2vh;margin-left: 1vh;"><i class="fa fa-download"></i> ' . $exp_material . '</a> <br>';
			}
		}
	}
	if ($result_header['file_incoming_check'] !== '' && $result_header['file_incoming_check'] !== null) {
		echo '<span>Incoming Check File</span> <br>';
		$exp_file_incoming_check = explode('|', $result_header['file_incoming_check']);
		foreach ($exp_file_incoming_check as $exp_incoming) {
			if (file_exists($exp_incoming)) {
				echo '<a href="' . base_url($exp_incoming) . '" class="" style="margin-top: 2vh;margin-left: 1vh;"><i class="fa fa-download"></i> ' . $exp_incoming . '</a> <br>';
			}
		}
	}
	?>
</div>
<script>
	swal.close();
</script>