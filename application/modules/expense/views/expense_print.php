<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title><?= (!empty($data->id_kasbon) ? 'EXPENSE REPORT' : 'EXPENSE') . ' - ' . $data->no_doc ?></title>
	<style>
		body {
			font-family: sans-serif;
			margin: 20px;
			color: #000;
		}

		table.garis {
			border-collapse: collapse;
			font-size: 0.9em;
			font-family: sans-serif;
		}

		.pdf-page-box {
			margin-bottom: 15px;
			page-break-inside: avoid;
			break-inside: avoid;
		}

		.pdf-page-canvas {
			max-width: 650px;
			width: 100%;
			height: auto;
			display: block;
			margin: 0 auto;
			border: 1px solid #e2e8f0;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}

		.attachment-img {
			max-width: 650px;
			width: auto;
			height: auto;
			max-height: 900px;
			display: block;
			margin: 5px 0;
			border: 1px solid #e2e8f0;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			page-break-inside: avoid;
			break-inside: avoid;
		}

		.attachment-item {
			page-break-inside: avoid;
			break-inside: avoid;
			margin-bottom: 25px;
		}

		@media print {
			body {
				margin: 0;
				padding: 0;
			}

			.no-print {
				display: none !important;
			}

			.pagebreak {
				page-break-before: always;
				break-before: page;
			}

			.pdf-page-box {
				page-break-inside: avoid;
				break-inside: avoid;
				margin-bottom: 15px;
			}

			.pdf-page-canvas {
				max-width: 100% !important;
				height: auto !important;
				border: none !important;
				box-shadow: none !important;
			}

			.attachment-img {
				max-width: 100% !important;
				height: auto !important;
				border: none !important;
				box-shadow: none !important;
			}
		}
	</style>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
</head>

<body>
	<!-- Tombol Cetak & Info (Hanya di layar, tersembunyi saat cetak) -->
	<div class="no-print" style="max-width: 650px; margin-bottom: 15px; padding: 10px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
		<span style="font-size: 13px; color: #334155;">
			Dokumen: <strong><?= htmlspecialchars($data->no_doc) ?></strong> &bull; Dialog cetak akan otomatis terbuka setelah berkas selesai dimuat.
		</span>
		<button type="button" onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 6px 14px; border-radius: 4px; font-size: 13px; font-weight: bold; cursor: pointer;">
			Cetak
		</button>
	</div>

	<table cellpadding=2 cellspacing=0 border=0 width=650>
		<tr>
			<th colspan=8>Form Permintaan Pembelian Barang dan Jasa</th>
		</tr>
		<tr>
			<td colspan=8>
				<table cellpadding=2 cellspacing=0 border=1 width=650 class="garis">
					<tr>
						<th nowrap>No</th>
						<th nowrap>Tgl Pengajuan</th>
						<th nowrap>Nama Barang</th>
						<th nowrap>Spesifikasi</th>
						<th nowrap>Jml</th>
						<th nowrap>Tgl Dibutuhkan</th>
						<th nowrap>Perkiraan Biaya<br />Satuan</th>
						<th nowrap>Total Biaya</th>
					</tr>
					<?php $total_expense = 0;
					$total_tol = 0;
					$total_parkir = 0;
					$total_kasbon = 0;
					$idd = 1;
					$total_km = 0;
					$grand_total = 0;
					$i = 0;
					if (!empty($data_detail)) {
						foreach ($data_detail as $record) {
							$i++; ?>
							<tr>
								<td><?= $i; ?></td>
								<td><?= date('d F Y', strtotime($data->created_on)); ?></td>
								<td><?= $record->deskripsi; ?></td>
								<td><?= $record->keterangan; ?></td>
								<td align="right"><?= number_format($record->qty); ?></td>
								<td><?= date('d F Y', strtotime($record->tanggal)); ?></td>
								<td align="right"><?= number_format($record->harga); ?></td>
								<td align="right"><?= number_format($record->expense); ?></td>
							</tr>
					<?php

							$total_expense = ($total_expense + ($record->expense));
							$idd++;
						}
					}
					$grand_total = ($total_expense);
					for ($x = 0; $x < (5 - $i); $x++) {
						echo '
		<tr>
			<td>&nbsp;</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	';
					}
					?>
					<tr>
						<td colspan=7 align=right><strong>Total Expense</strong></td>
						<td align="right"><strong><?= number_format($total_expense); ?></strong></td>
					</tr>
					<?php if (!empty($data->total_kasbon) && $data->total_kasbon > 0): ?>
					<tr>
						<td colspan=7 align=right><strong>Kasbon</strong></td>
						<td align="right"><?= number_format($data->total_kasbon); ?></td>
					</tr>
					<?php endif; ?>
					<?php if (!empty($data->lebih_bayar) && $data->lebih_bayar > 0): ?>
					<tr>
						<td colspan=7 align=right><strong>Lebih Bayar (Pengembalian Kasbon)</strong></td>
						<td align="right"><strong><?= number_format($data->lebih_bayar); ?></strong></td>
					</tr>
					<?php endif; ?>
					<?php if (!empty($data->kurang_bayar) && $data->kurang_bayar > 0): ?>
					<tr>
						<td colspan=7 align=right><strong>Kurang Bayar (Reimburse Kantor)</strong></td>
						<td align="right"><strong><?= number_format($data->kurang_bayar); ?></strong></td>
					</tr>
					<?php endif; ?>
				</table>
			</td>
		</tr>

		<?php
		$pelapor = $this->db->query("SELECT b.nm_karyawan as name FROM users a left join employee b on a.employee_id=b.id WHERE a.username='" . $data->created_by . "'")->row();
		$mengetahui = $this->db->query("SELECT b.nm_karyawan as name FROM users a left join employee b on a.employee_id=b.id WHERE a.username='" . $data->approved_by . "'")->row();
		?>

		<tr>
			<td colspan=2 align=center>Mengajukan</td>
			<td></td>
			<td align=center colspan=3>Mengetahui</td>
			<td></td>
			<td align=center>Menyetujui</td>
		</tr>
		<tr>
			<td colspan=8>&nbsp;</td>
		</tr>
		<tr height=120>
			<td colspan=2 align=center nowrap valign="bottom" width=100><?php
																		echo '<br><br><br>';
																		?>

			</td>
			<td width=25>&nbsp;</td>
			<td colspan=3 align=center nowrap valign="bottom" width=120><?php
																		if (!empty($mengetahui)) {
																			echo '<br><br><br>';
																		}
																		?>

			</td>
			<td>&nbsp;</td>
			<td align=center nowrap valign="bottom"><u>&nbsp; &nbsp; </u><br /></td>
		</tr>
	</table>
	<em>STM/FR02/09/01/00</em>

	<?php
	// Kumpulkan semua berkas bukti/lampiran pengeluaran
	$all_files = [];
	if (!empty($data_detail)) {
		$row_num = 0;
		foreach ($data_detail as $record) {
			$row_num++;
			$item_label = 'Item #' . $row_num . ' (' . ($record->deskripsi ?: 'Biaya') . ')';

			// 1. Dari tr_expense_detail_file (multiple upload)
			if (isset($detail_files[$record->id]) && is_array($detail_files[$record->id])) {
				foreach ($detail_files[$record->id] as $df) {
					if (!empty($df->doc_file)) {
						$all_files[] = [
							'file'  => $df->doc_file,
							'label' => $item_label,
						];
					}
				}
			}

			// 2. Dari tr_expense_detail.doc_file (data legacy single upload)
			if (!empty($record->doc_file)) {
				$exists_in_list = false;
				foreach ($all_files as $af) {
					if ($af['file'] === $record->doc_file) {
						$exists_in_list = true;
						break;
					}
				}
				if (!$exists_in_list) {
					$all_files[] = [
						'file'  => $record->doc_file,
						'label' => $item_label,
					];
				}
			}

			// 3. Dari tr_expense_detail.doc_file_2
			if (!empty($record->doc_file_2)) {
				$all_files[] = [
					'file'  => $record->doc_file_2,
					'label' => $item_label,
				];
			}
		}
	}

	// 4. Lampiran Header (jika ada)
	if (!empty($data->bon_bukti)) {
		$all_files[] = ['file' => $data->bon_bukti, 'label' => 'Bon Bukti Header'];
	}
	if (!empty($data->bukti_pengembalian)) {
		$all_files[] = ['file' => $data->bukti_pengembalian, 'label' => 'Bukti Pengembalian Kasbon'];
	}
	if (!empty($data->transfer_file)) {
		$all_files[] = ['file' => $data->transfer_file, 'label' => 'Bukti Transfer Bank'];
	}

	// Filter berkas yang benar-benar ada di storage server
	$valid_files = [];
	foreach ($all_files as $f) {
		$fpath_expense = 'assets/expense/' . $f['file'];
		$fpath_bukti   = 'assets/bukti/' . $f['file'];
		if (file_exists($fpath_expense)) {
			$f['url'] = base_url($fpath_expense);
			$valid_files[] = $f;
		} elseif (file_exists($fpath_bukti)) {
			$f['url'] = base_url($fpath_bukti);
			$valid_files[] = $f;
		}
	}
	?>

	<?php if (!empty($valid_files)): ?>
		<div class="pagebreak" style="margin-top: 30px;">
			<h3 style="font-family: sans-serif; font-size: 13pt; margin: 15px 0 12px 0; border-bottom: 2px solid #333; padding-bottom: 5px; max-width: 650px;">
				Lampiran Bukti / Bon Pengeluaran
			</h3>
			<?php foreach ($valid_files as $fidx => $vf): 
				$is_pdf = (stripos($vf['file'], '.pdf') !== false);
			?>
				<div class="attachment-item">
					<div style="font-weight: bold; font-size: 11pt; margin-bottom: 6px; color: #222; max-width: 650px;">
						<?= ($fidx + 1) . '. ' . htmlspecialchars($vf['label']) ?>
						<span class="no-print" style="font-weight: normal; font-size: 9pt; color: #64748b; margin-left: 8px;">
							(<?= htmlspecialchars($vf['file']) ?>)
						</span>
					</div>
					<?php if ($is_pdf): ?>
						<div class="pdf-render-container" data-pdf-url="<?= $vf['url'] ?>" data-file-name="<?= htmlspecialchars($vf['file']) ?>">
							<div class="pdf-loading no-print" style="padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; font-size: 11px; color: #64748b; max-width: 650px;">
								Memuat seluruh halaman PDF (<?= htmlspecialchars($vf['file']) ?>)...
							</div>
						</div>
					<?php else: ?>
						<div class="img-render-container">
							<img src="<?= $vf['url'] ?>" class="attachment-img" alt="<?= htmlspecialchars($vf['label']) ?>">
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			// Konfigurasi worker PDF.js
			if (typeof pdfjsLib !== 'undefined') {
				pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
			}

			// 1. Tunggu seluruh file gambar bukti selesai dimuat
			var images = Array.from(document.querySelectorAll('img.attachment-img'));
			var imgPromises = images.map(function(img) {
				if (img.complete && img.naturalHeight !== 0) {
					return Promise.resolve();
				}
				return new Promise(function(resolve) {
					img.onload = resolve;
					img.onerror = resolve; // Tetap lanjut jika salah satu gambar gagal
				});
			});

			// 2. Render seluruh halaman dari setiap file PDF
			var containers = Array.from(document.querySelectorAll('.pdf-render-container'));
			var pdfPromises = containers.map(function(container) {
				var url = container.getAttribute('data-pdf-url');
				if (!url || typeof pdfjsLib === 'undefined') {
					return Promise.resolve();
				}

				return pdfjsLib.getDocument(url).promise.then(function(pdf) {
					container.innerHTML = ''; // Bersihkan loader

					// Render halaman secara berurutan agar urutan (Hal 1, 2, dst) selalu konsisten
					var renderSequential = function(pageNum) {
						if (pageNum > pdf.numPages) {
							return Promise.resolve();
						}

						return pdf.getPage(pageNum).then(function(page) {
							var scale = 1.5;
							var viewport = page.getViewport({ scale: scale });

							var pageBox = document.createElement('div');
							pageBox.className = 'pdf-page-box';

							if (pdf.numPages > 1) {
								var badge = document.createElement('div');
								badge.className = 'pdf-page-badge no-print';
								badge.style.cssText = 'font-size:10px; color:#64748b; margin-bottom:3px; text-align:right; max-width:650px;';
								badge.textContent = 'Halaman ' + pageNum + ' dari ' + pdf.numPages;
								pageBox.appendChild(badge);
							}

							var canvas = document.createElement('canvas');
							canvas.className = 'pdf-page-canvas';
							var context = canvas.getContext('2d');
							canvas.height = viewport.height;
							canvas.width = viewport.width;

							pageBox.appendChild(canvas);
							container.appendChild(pageBox);

							var renderContext = {
								canvasContext: context,
								viewport: viewport
							};

							return page.render(renderContext).promise.then(function() {
								return renderSequential(pageNum + 1);
							});
						});
					};

					return renderSequential(1);
				}).catch(function(err) {
					console.error("Gagal merender PDF:", url, err);
					container.innerHTML = '<div class="no-print" style="color:#ef4444; font-size:11px; padding:6px; background:#fef2f2; border:1px solid #fecaca; border-radius:4px; max-width:650px;">Gagal memuat preview PDF. <a href="' + url + '" target="_blank">Buka file PDF langsung</a></div>';
				});
			});

			// 3. Setelah semua gambar dan seluruh halaman PDF selesai dirender, otomatis buka dialog print
			Promise.all(imgPromises.concat(pdfPromises)).then(function() {
				setTimeout(function() {
					window.print();
				}, 500);
			});
		});
	</script>
</body>

</html>