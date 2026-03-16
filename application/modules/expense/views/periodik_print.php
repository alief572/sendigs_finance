<html>

<head>
	<title> PRINT PENGAJUAN PERIODIK  </title>
</head>

<body>
	<style>
		body {
			font-family: sans-serif;
		}

		table.garis {
			border-collapse: collapse;
			font-size: 0.9em;
			font-family: sans-serif;
		}

		@media print {
			.pagebreak {
				page-break-before: always;
			}

			/* page-break-after works, as well */
		}
	</style>
	<table cellpadding=2 cellspacing=0 border=0 width=650>
		<tr>
			<th colspan=6>PERIODIK<br /><br /><br /></th>
		</tr>
		<tr>
			<td nowrap colspan=2>No Dokumen : <?= $data->no_doc ?></td>
			<td nowrap colspan=2>Jumlah Nilai : <?= number_format($data->jumlah_kasbon) ?></td>
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

        if (!empty($data->approved_on) && $data->approved_on != '0000-00-00') {
        $tglMengetahui = date('d F Y', strtotime($data->approved_on));
        } else {
        $tglMengetahui = date('d F Y');
        }
		?>
		<tr height=120>
			<td colspan=2 align=center nowrap valign="bottom">
				<u>&nbsp; &nbsp; <?= (($nmuser) ? $nmuser : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u><br><?= date('d F Y', strtotime($data->created_on)); ?>
			</td>
			<td colspan=2 align=center nowrap valign="bottom">
                <u>&nbsp; &nbsp; <?= $mengetahui->name ?> &nbsp; &nbsp; </u>
                <br><?= $tglMengetahui; ?>
            </td>
		</tr>
	</table><br /><br />
	
</body>

</html>