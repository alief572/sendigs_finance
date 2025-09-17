
<div class="box-body">
	<!-- <table id="my-grid" class="table" width="100%">
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No PO</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$no_po;?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$kode_trans;?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Penerimaan</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$resv;?></td>
			</tr>
		</thead>
	</table><br> -->
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='5%'>#</th>
				<th class="text-center" style='vertical-align:middle;'>Name Barang</th>
				<th class="text-center" style='vertical-align:middle;'>Qty</th>
				<th class="text-center" style='vertical-align:middle;'>Tgl Dibutuhkan</th>
				<!-- <th class="text-center" style='vertical-align:middle;'>Jenis Pembelian</th>
				<th class="text-center" style='vertical-align:middle;'>Status</th> -->
			</tr>
		</thead>
		<tbody>
			<?php
            $No=0;
			foreach($result AS $val => $valx){
                $No++;
				
                // if($valx['category'] == 'asset'){
                //     $warna = '#a9179e';
                // }
                // elseif($valx['category'] == 'rutin'){
                //     $warna = '#ff1ab3';
                // }
                // else{
                //     $warna = '#1bb885';
                // }
                
                // $sts2 = "";
                // if(!empty($valx['jenis_pembelian'])){
                //     $sts2 = "<span class='badge' style='background-color: ".$warna.";'>PURCHASING ".strtoupper($valx['jenis_pembelian'])."</span>";
                // }

                // $jns_pm = "";
                // $sts = "NOT DONE";
                // $warna2 = 'blue';
                // if(!empty($valx['no_rfq'])){
                //     if($valx['jenis_pembelian'] == 'po'){
                //         $jns_pm = "<br><b>NO RFQ</b><br>".strtoupper($valx['no_rfq']);
                        
                //         $get_sts = $this->db->query("SELECT sts_ajuan FROM tran_rfq_header WHERE no_rfq='".$valx['no_rfq']."' LIMIT 1")->result();
                        
                //         $sts 	= color_status_purchase($get_sts[0]->sts_ajuan)['status'];
                //         $warna2 = color_status_purchase($get_sts[0]->sts_ajuan)['color'];
                //     }
                //     if($valx['jenis_pembelian'] == 'non po'){
                //         $jns_pm = "<br><b>NO NON-PO</b><br>".strtoupper($valx['no_rfq']);
                        
                //         $get_sts = $this->db->query("SELECT app_status FROM tran_non_po_detail WHERE no_non_po='".$valx['no_rfq']."' LIMIT 1")->result();
                //         $stsx = "APV";
                //         if($get_sts[0]->app_status == 'Y'){
                //             $stsx = "CLS";
                //         }
                //         $sts 	= color_status_purchase($stsx )['status'];
                //         $warna2 = color_status_purchase($stsx )['color'];
                //     }
                // }

				echo "<tr>";
					echo "<td align='center'>".$No."</td>";
					echo "<td>".strtoupper($valx['nama_material'])."</td>";
                    echo "<td align='center'>".number_format($valx['qty'])."</td>";
					echo "<td align='center'>".date('d-M-Y',strtotime($valx['tgl_dibutuhkan']))."</td>";
					// echo "<td align='center'>".$sts2."</td>";
					// echo "<td align='center'><span class='badge' style='background-color: ".$warna2."'>".$sts."</span></td>";
					// echo "<td></td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<script>
	swal.close();
</script>