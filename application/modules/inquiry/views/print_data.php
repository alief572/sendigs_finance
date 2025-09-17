<?php
date_default_timezone_set("Asia/Bangkok");
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>

        {
            margin:0;
            padding:0;
            font-family:Arial;
            font-size:10pt;
            color:#000;
        }
        body
        {
            width:100%;
            font-family:Arial;
            font-size:10pt;
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
            /*height:297mm;
            width:210mm;*/
            width: 297mm;
            height: 210mm;
            page-break-after:always;
        }

        #header-tabel tr {
            padding: 0px;
        }

        #tabel-laporan {
            border-spacing: -1px;
        }

        #tabel-laporan th{
            border-top: solid 1px #000;
            border-bottom: solid 1px #000;
            margin: 0px;
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
    </style>
</head>
<body>
<div id="wrapper">
    <table width="100%" border="0" id="header-tabel">
        <tr>
            <th colspan="3" width="20%" style="text-align: left;">PT IMPORTA JAYA ABADI<br>YOGYAKARTA</th>
            <th style="border-right: none;">SALES ORDER (SO)<br><?php echo 'NO. : '.@$so_data->no_so?></th>
            <th colspan="3" style="border-left: none;"></th>
        </tr>
        <tr>
            <td width="10%">SALES</td>
            <td width="1%">:</td>
            <td colspan="2"><?php echo strtoupper(@$so_data->nm_salesman)?></td>
            <td width="15%">TGL SO</td>
            <td width="1%">:</td>
            <td><?php echo date('d-M-Y',strtotime(@$so_data->tanggal))?></td>
        </tr>
        <tr>
            <td width="10%">CUSTOMER</td>
            <td width="1%">:</td>
            <td colspan="2"><?php echo strtoupper(@$so_data->nm_customer)?></td>
            <td width="10%">TGL KIRIM</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
        <tr>
            <td width="10%">ALAMAT</td>
            <td width="1%">:</td>
            <td colspan="2"><?php echo @$customer->alamat?></td>
            <td width="10%">TOP (Hari)</td>
            <td width="1%">:</td>
            <td><?php echo @$so_data->top?></td>
        </tr>
        <tr>
            <td width="10%">KETERANGAN</td>
            <td width="1%">:</td>
            <td colspan="2"><?php echo @$so_data->keterangan?></td>
            <td width="10%"></td>
            <td width="1%"></td>
            <td></td>
        </tr>
        <tr>
            <td width="10%"></td>
            <td width="1%"></td>
            <td></td>
        </tr>
    </table>
    <table width="100%" id="tabel-laporan">
        <tr>
            <th width="1%">NO</th>
            <th width="40%">NAMA BARANG</th>
            <th width="20%">COLLY</th>
            <th width="7%">QTY COLLY</th>
            <th width="7%">TOTAL COLLY</th>
            <th width="8%">SATUAN</th>
            <th width="5%">QTY</th>
            <th width="15%">HARGA</th>
            <th width="15%">JUMLAH</th>

        </tr>
        <?php
        $n=1;
        $total = 0;
        foreach(@$detail as $ks=>$vs){
        $no = $n++;
        $total += $vs->harga*$vs->qty_order;
        $colly = $this->Salesorder_model->get_data(array('id_barang' => $vs->id_barang),'barang_koli');
        ?>
        <tr>
            <td style="vertical-align: top;"><center><?php echo $no?></center></td>
            <td style="vertical-align: top;"><?php echo $vs->nm_barang?></td>
            <td>
                 <?php
                $sn = 1;
                foreach($colly as $kc=>$vc){
                    echo $no.'.'.$sn++.' -'.$vc->nm_koli.'<br>';
                }
                ?>
            </td>
            <td style="vertical-align: top;">
                <center>
                <?php
                $sn = 1;
                foreach($colly as $kc=>$vc){
                    echo $vc->qty.'<br>';
                }
                ?>
                </center>
            </td>
            <td style="vertical-align: top;">
                <center>
                <?php
                $tc = 0;
                foreach($colly as $kc){
                    $tc = count($colly);
                    echo $tc.'<br>';
                }
                ?>
                </center>
            </td>
            <td style="vertical-align: top;"><center><?php echo $vs->satuan?></center></td>
            <td style="vertical-align: top;"><center><?php echo $vs->qty_order?></center></td>
            <td style="text-align: right;vertical-align: top;"><?php echo formatnomor($vs->harga)?></td>
            <td style="text-align: right;vertical-align: top;"><?php echo formatnomor($vs->harga*$vs->qty_order)?></td>

        </tr>
        <?php }
          $total = $total - @$so_data->persen_diskon_toko*$total/100 - @$so_data->diskon_cash + @$so_data->ppn
         ?>
    </table>
</div>
<?php $tglprint = date("d-m-Y H:i:s");?>
<htmlpagefooter name="footer">
    <hr>
    <table width="100%" border="0">
        <tr>
            <td width="20%"><center>Dibuat Oleh,</center></td>
            <td width="20%"><center>Mengetahui,</center></td>
            <td width="20%"><center>Disetujui,</center></td>
            <td width="10%">JUMLAH NOMINAL</td>
            <td width="1%">:</td>
            <td width="10%" style="text-align: right;"><?php echo  formatnomor(@$so_data->dpp)?></td>
            <td width="8%" style="text-align: right;">Blm JTT</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%">
              <?php
                if (@$so_data->diskon_toko != 0) {
                  echo "1. Diskon Toko";
                }else {
                  echo "1. Disc - %";
                }
              ?>
            </td>
            <td width="1%">:</td>
            <td width="10%" style="text-align: right;">
              <?php
              $disc_toko = @$so_data->persen_diskon_toko*@$so_data->dpp/100;
              echo formatnomor($disc_toko) ?>
            </td>
            <td width="8%" style="text-align: right;">01-30</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%">
              <?php
                if (@$so_data->diskon_cash != 0) {
                  echo "2. Diskon Cash";
                }else {
                  echo "2. Disc - %";
                }
              ?>
            </td>
            <td width="1%">:</td>
            <td width="10%" style="text-align: right;">
              <?php echo formatnomor(@$so_data->persen_diskon_cash*@$so_data->dpp/100) ?>
            </td>
            <td width="8%" style="text-align: right;">31-60</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%">
              <?php
                if (@$so_data->diskon_toko != 0) {
                  echo "3. PPN";
                }else {
                  echo "3. PPN - %";
                }
              ?>
            </td>
            <td width="1%">:</td>
            <td width="10%" style="text-align: right;">
              <?php echo formatnomor(@$so_data->ppn) ?>
            </td>
            <td width="8%" style="text-align: right;">61-90</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
        <tr>
            <td width="15%"><center>( Adm Sales & Stok )</center></td>
            <td width="15%"><center>( Spv. ACC & Tax )</center></td>
            <td width="15%"><center>( BM )</center></td>
            <td width="15%">ONGKOS KIRIM</td>
            <td width="1%">:</td>
            <td></td>
            <td width="8%" style="text-align: right;">> 90</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"><b>GRAND TOTAL</b></td>
            <td width="1%">:</td>
            <td style="border:solid 1px #000;text-align: right;margin-right: 10px;"><b><?php echo  formatnomor(@$so_data->total)?></b></td>
            <td width="8%" style="text-align: right;">TOTAL</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
    </table>
    <hr />
    <div id="footer">
    <table>
        <tr><td>PT IMPORTA JAYA ABADI - Printed By <?php echo ucwords($userData->nm_lengkap) ." On ". $tglprint; ?></td></tr>
    </table>
    </div>
</htmlpagefooter>
<sethtmlpagefooter name="footer" value="on" />
</body>
</html>
