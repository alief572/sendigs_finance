<html>

<head>
	<title> EXPENSES REPORT BENSIN & TOL </title>
	<style>
		body {
			font-family: sans-serif;
		}

		table.garis {
			border-collapse: collapse;
			font-size: 0.9em;
			font-family: sans-serif;
		}

		.pdf-page-canvas {
			max-width: 100%;
			height: auto;
			display: block;
			margin: 10px 0;
			border: 1px solid #ddd;
		}

		@media print {
			.pagebreak {
				page-break-before: always;
			}

			.pdf-page-canvas {
				max-width: 100% !important;
				height: auto !important;
				page-break-inside: avoid;
				border: none;
			}

			/* page-break-after works, as well */
		}
	</style>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
</head>

<body>
	<table cellpadding=2 cellspacing=0 border=0 width=650>
		<tr>
			<th colspan=6>KASBON<br /><br /><br /></th>
		</tr>
		<tr>
			<td nowrap colspan=2>No Dokumen : <?= $data->no_doc ?></td>
			<td nowrap colspan=2>Jumlah Kasbon : <?= number_format($data->jumlah_kasbon) ?></td>
			<td nowrap colspan=2>Tanggal : <?= date('d F Y', strtotime($data->tgl_doc)) ?></td>
		</tr>
		<tr>
			<th colspan=6><br /></th>
		</tr>
		<tr>
			<td valign=top width=100>Keperluan</td>
			<td valign=top colspan=5>: <?= $data->keperluan ?></td>
		</tr>
		<tr>
			<td valign=top width=100>Project</td>
			<td valign=top colspan=5>: <?= ($data->project); ?></td>
		</tr>
		<tr>
			<td height=60 colspan=6></td>
		</tr>
		<tr>
			<td colspan=2 align=center>Mengajukan</td>
			<td colspan=2 rowspan=3></td>
			<td colspan=2 align=center>Mengetahui</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<?php
		$mengajukan = $this->db->query("SELECT a.nm_lengkap as name FROM users a WHERE a.username='" . $data->created_by . "'")->row();
		$mengetahui = $this->db->query("SELECT a.nm_lengkap as name FROM users a WHERE a.username='" . $data->approved_by . "'")->row();
				if (empty($mengetahui)) {
			$mengetahui = new stdClass();
			$mengetahui->name = "FINANCE";
		}
		?>
		<tr height=120>
			<td colspan=2 align=center nowrap valign="bottom">
				<u>&nbsp; &nbsp; <?= (($nmuser) ? $nmuser : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u><br><?= date('d F Y', strtotime($data->created_on)); ?>
			</td>
			<td colspan=2 align=center nowrap valign="bottom">
				<u>&nbsp; &nbsp; <?= (($mengetahui) ? $mengetahui->name : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u><br><?= date('d F Y', strtotime($data->approved_on)); ?>
			</td>
		</tr>
	</table><br /><br />
	<?php
	if (isset($data)) {
		//	echo '<div class="pagebreak"> </div>';
		if ($data->doc_file != '') {
			$is_pdf = (stripos($data->doc_file, '.pdf') !== false);
			if ($is_pdf) {
				$pdf_url = base_url('assets/expense/' . $data->doc_file);
				echo '<div class="col-md-12" style="margin-bottom:20px;">
					<div class="pdf-render-container" data-pdf-url="' . $pdf_url . '">
						<iframe src="' . $pdf_url . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
							<a href="' . $pdf_url . '">Download PDF</a>
						</iframe>
					</div>
					<br />' . $data->no_doc . '</div>';
			} else {
				echo '<div class="col-md-12"><a href="' . base_url('assets/expense/' . $data->doc_file) . '" target="_blank"><img src="' . base_url('assets/expense/' . $data->doc_file) . '" class="img-responsive"></a><br />' . $data->no_doc . '</div>';
			}
		}
		if ($data->doc_file_2 != '') {
			$is_pdf2 = (stripos($data->doc_file_2, '.pdf') !== false);
			if ($is_pdf2) {
				$pdf_url2 = base_url('assets/expense/' . $data->doc_file_2);
				echo '<div class="col-md-12" style="margin-bottom:20px;">
					<div class="pdf-render-container" data-pdf-url="' . $pdf_url2 . '">
						<iframe src="' . $pdf_url2 . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
							<a href="' . $pdf_url2 . '">Download PDF</a>
						</iframe>
					</div>
					<br />' . $data->no_doc . '</div>';
			} else {
				echo '<div class="col-md-12"><a href="' . base_url('assets/expense/' . $data->doc_file_2) . '" target="_blank"><img src="' . base_url('assets/expense/' . $data->doc_file_2) . '" class="img-responsive"></a><br />' . $data->no_doc . '</div>';
			}
		}
	}
	?>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			if (typeof pdfjsLib !== 'undefined') {
				pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

				var containers = document.querySelectorAll('.pdf-render-container');
				containers.forEach(function(container) {
					var url = container.getAttribute('data-pdf-url');
					if (!url) return;

					pdfjsLib.getDocument(url).promise.then(function(pdf) {
						container.innerHTML = ''; // Hapus iframe fallback
						
						var renderPage = function(num) {
							pdf.getPage(num).then(function(page) {
								var scale = 1.5;
								var viewport = page.getViewport({ scale: scale });
								var canvas = document.createElement('canvas');
								canvas.className = 'pdf-page-canvas';
								var context = canvas.getContext('2d');
								canvas.height = viewport.height;
								canvas.width = viewport.width;

								container.appendChild(canvas);

								var renderContext = {
									canvasContext: context,
									viewport: viewport
								};
								page.render(renderContext);

								if (num < pdf.numPages) {
									renderPage(num + 1);
								}
							});
						};
						renderPage(1);
					}).catch(function(err) {
						console.error("PDF.js render error:", err);
					});
				});
			}
		});
	</script>
</body>

</html>