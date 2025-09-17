<div class="box-body">
	<input type="hidden" name='kode_trans' id='kode_trans' value='<?= $getData[0]['kode_trans']; ?>'>
	<table class="table" width="100%" border='0'>
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;' width='33%'><?= $getData[0]['kode_trans']; ?></td>
				<td class="text-left" style='vertical-align:middle;' width='15%'></td>
				<td class="text-left" style='vertical-align:middle;' width='2%'></td>
				<td class="text-left" style='vertical-align:middle;' width='33%'></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Nomor PO</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= strtoupper($no_po); ?></td>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Incoming</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= tgl_indo($getData[0]['tanggal']); ?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Gudang</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $getData[0]['id_gudang_ke'])); ?></td>
				<td class="text-left" style='vertical-align:middle;'>PIC</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $getData[0]['pic']; ?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Note</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $getData[0]['note']; ?></td>
				<td class="text-left" style='vertical-align:middle;'></td>
				<td class="text-left" style='vertical-align:middle;'></td>
				<td class="text-left" style='vertical-align:middle;'></td>
			</tr>
		</thead>
	</table><br>
	<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead>
			<tr>
				<th class='text-center' width='5%'>#</th>
				<th class='text-left'>Stok Name</th>
				<th class='text-center' width='9%'>Qty Pack</th>
				<th class='text-center' width='9%'>Unit Pack</th>
				<th class='text-center' width='9%'>Qty</th>
				<th class='text-center' width='12%'>Unit Measurement</th>
				<th class='text-center' width='20%'>Keterangan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (!empty($getDataDetail)) {
				$No = 0;
				foreach ($getDataDetail as $key => $value) {
					$No++;
					$id_material 	= $value['id_material'];
					$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama'])) ? $GET_MATERIAL[$id_material]['nama'] : 0;
					$id_packing     = (!empty($GET_MATERIAL[$id_material]['id_packing'])) ? $GET_MATERIAL[$id_material]['id_packing'] : 0;
					$konversi       = (!empty($GET_MATERIAL[$id_material]['konversi'])) ? $GET_MATERIAL[$id_material]['konversi'] : 1;
					$packing        = (!empty($GET_SATUAN[$id_packing]['code'])) ? $GET_SATUAN[$id_packing]['code'] : '';

					$this->db->select('a.*, b.code as satuan, c.code as satuan_packing');
					$this->db->from('accessories a');
					$this->db->join('ms_satuan b', 'b.id = a.id_unit', 'left');
					$this->db->join('ms_satuan c', 'c.id = a.id_unit_gudang', 'left');
					$this->db->where('a.id', $id_material);
					$get_stock = $this->db->get()->row();

					$qty_in = $value['qty_oke'];
					echo "<tr>";
					echo "<td align='center'>" . $No . "</td>";
					echo "<td>" . $nm_material . "</td>";
					echo "<td align='center'>" . number_format($qty_in, 2) . "</td>";
					echo "<td align='center'>" . strtoupper($packing) . "</td>";
					echo "<td align='center'>" . number_format($qty_in * $konversi, 2) . "</td>";
					echo "<td align='center'>" . strtoupper($get_stock->satuan) . "</td>";
					echo "<td>" . $value['keterangan'] . "</td>";
					echo "</tr>";
				}
			?>
			<?php
			} else {
				echo "<tr>";
				echo "<td colspan='5'><b>Tidak ada data yang ditampilkan !</b></td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<style>
	.tanggal {
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function() {
		swal.close();
		$('.chosen-select').select2({
			'width': '200px'
		});
		$('.autoNumeric2').autoNumeric('init', {
			mDec: '2',
			aPad: false
		});
		$('.tanggal').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: 0
		});
	});
</script>