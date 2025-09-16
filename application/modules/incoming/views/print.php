<?php
date_default_timezone_set("Asia/Bangkok");
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>
    @font-face { font-family: kitfont; src: url('1979 Dot Matrix Regular.TTF'); }
      html
        {
            margin:0;
            padding:0;
            font-style: kitfont; 
            font-family:Arial;
            font-size:9pt;
			font-weignt:bold;
            color:#000;
        }
        body
        {
            width:100%;
            font-family:Arial;
            font-style: kitfont;
            font-size:9pt;
			font-weight:bold;
            margin:0;
            padding:0;
        }

        p
        {
            margin:0;
            padding:0;
        }

        .page
        {
            width: 210mm;
            height: 145mm;
            page-break-after:always;
        }

        #header-tabel tr {
            padding: 0px;
        }
        #tabel-laporan {
            border-spacing: -1px;
            padding: 0px !important;
        }

        #tabel-laporan th{
            /*
            border-top: solid 1px #000;
            border-bottom: solid 1px #000;
            */
           border : solid 1px #000;
            margin: 0px;
            height: auto;
        }

        #tabel-laporan td{
            border : solid 1px #000;
            margin: 0px;
            height: auto;
        }
        #tabel-laporan {
          border-bottom:1px solid #000 !important;
        }

        .isi td{
          border-top:0px !important;
          border-bottom:0px !important;
        }
		
		 #grey
        {
             background:#eee;
        }

        #footer
        {
            /*width:180mm;*/
            margin:0 15mm;
            padding-bottom:3mm;
        }
        #footer table
        {
            width:100%;
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;

            background:#eee;

            border-spacing:0;
            border-collapse: collapse;
        }
        #footer table td
        {
            width:25%;
            text-align:center;
            font-size:9pt;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }

        img.resize {
          max-width:12%;
          max-height:12%;
        }
		.pagebreak 
		{
		width:100% ;
		page-break-after: always;
		margin-bottom:10px;
		}

        table.gridtable {
            font-family: arial,sans-serif;
            font-size:12px;
            color:#333333;
            border: 1px solid #808080;
            border-collapse: collapse;
        }
        table.gridtable th {
            padding: 6px;
            background-color: #f7f7f7;
            color: black;
            border-color: #808080;
            border-style: solid;
            border-width: 1px;
        }
        table.gridtable th.head {
            padding: 6px; 
            background-color: #f7f7f7;
            color: black;
            border-color: #808080;
            border-style: solid;
            border-width: 1px;
        }
        table.gridtable td {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: #808080;
        }
        table.gridtable td.cols {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: #808080;
        }
    </style>
</head>
<body>
<?php
	foreach($head as $header){
	}
?>

        <table class='gridtable'  cellpadding='0' cellspacing='0' width='100%' style='width:100% !important;'>
			<thead style='vertical-align:middle;'>
			<tr class='bg-blue'>
			<th width='20%'>No PO</th>
			<th width='20%'>Produk</th>
			<th width='20%'>Kode Barang</th>
			<th width='7%'>Qty Order</th>
			<th width='7%'>Qty Receive</th>
			</tr>
			</thead>
			<tbody id="data_request" style='vertical-align:top;'>
			<?php
		       $loop=0;
			   foreach ($detail as $material){
				     $no_po	= substr($material->id_dt_po,0,8);
					$mt     = $this->db->query("SELECT * FROM tr_purchase_order WHERE no_po = '".$no_po."'  ")->row();
					$no_surat = $mt->no_surat;
				   
				$loop++;
				echo "
				<tr id='trmaterial_$loop'>
				<td	align='left'>".$no_surat."</td>
				<td	align='left'>".$material->nama_material."</td>
				<td	align='left'>".$material->kode_barang."</td>
				<td	align='center'>".number_format($material->qty_order)."</td>
				<td	align='center'>".number_format($material->qty_recive)."</td>
				</tr>
				";
				}
			?>
			</tbody>
			</table>