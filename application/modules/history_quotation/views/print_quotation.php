<?php
$ENABLE_ADD     = has_permission('Quotation.Add');
$ENABLE_MANAGE  = has_permission('Quotation.Manage');
$ENABLE_VIEW    = has_permission('Quotation.View');
$ENABLE_DELETE  = has_permission('Quotation.Delete');

?>
<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box" id="printed_area">
    <!-- <div class="box-body"> -->
    <table class="table w-100">
        <tr>
            <td>
                <img src="<?= base_url() ?>assets/images/orindo_logo.png" width="500" alt="" srcset="">
            </td>
            <td class="text-right" style="vertical-align: middle;">
                Jl. Pembangunan 2 No. 34 <br>
                Kec. Batuceper, Kota Tanggerang, Banten 15121 <br>
                <span style="font-weight:bold;">Hotline Service :</span> (+62) 21 557 66 153 <span style="font-weight:bold;">WhatsApp :</span> (+62) 858 9138 3212
            </td>
        </tr>
    </table>

    Jakarta, <?= date('d M Y') ?>
    <table style="width: 500px !important;">
        <tr>
            <td>No Ref.</td>
            <td style="text-align:center; width:50px !important;">:</td>
            <td><?= $results['data_penawaran']->no_penawaran ?></td>
        </tr>
        <tr>
            <td>Attn</td>
            <td style="text-align:center; width:50px !important;">:</td>
            <td><?= $results['data_penawaran']->nm_customer ?></td>
        </tr>
        <tr>
            <td>Address</td>
            <td style="text-align:center; width:50px !important;">:</td>
            <td><?= $results['data_penawaran']->alamat ?></td>
        </tr>
        <tr>
            <td>Email</td>
            <td style="text-align:center; width:50px !important;">:</td>
            <td><?= $results['data_penawaran']->email_customer ?></td>
        </tr>
        <tr>
            <td>Telp</td>
            <td style="text-align:center; width:50px !important;">:</td>
            <td><?= $results['data_penawaran']->telpon ?></td>
        </tr>
        <tr>
            <td>Subject</td>
            <td style="text-align:center; width:50px !important;">:</td>
            <td><?= $results['data_penawaran']->project ?></td>
        </tr>
    </table>

    <p style="font-weight: bold;">Dear Mr / Mrs</p>
    <p style="font-weight: bold;">Thank You for inviting us to quote for the above-mentioned project, we are pleased to submit here with out quotation as</p>
    <p style="font-weight: bold;">A. Product Types and Pricing</p>

    <table class="table table-bordered" border="1">
        <thead>
            <tr>
                <th class="text-center">NO</th>
                <th class="text-center" style="width:250px;">PRODUCT</th>
                <th class="text-center">ITEM CODE</th>
                <th class="text-center">QUANTITY</th>
                <th class="text-center">UNIT PRICE</th>
                <th class="text-center">TOTAL PRICE</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            $harga_seb_diskon = 0;
            $harga_ses_diskon = 0;

            $ttl_diskon = 0;
            $ttl_persen_diskon = 0;
            foreach ($results['data_penawaran_detail'] as $penawaran_detail) {
                echo '
                    <tr>
                        <td class="text-center">' . $no . '</td>
                        <td class="">' . $penawaran_detail->nama_produk . '</td>
                        <td class="text-center">' . $penawaran_detail->code . '</td>
                        <td class="text-center">' . number_format($penawaran_detail->qty, 2) . ' Pcs</td>
                        <td class="text-right">IDR ' . number_format(($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100)), 2) . '</td>
                        <td class="text-right">IDR ' . number_format((($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100)) * $penawaran_detail->qty), 2) . '</td>
                    </tr>
                ';

                $harga_seb_diskon += ($penawaran_detail->harga_satuan * $penawaran_detail->qty);
                $harga_ses_diskon += (($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100)) * $penawaran_detail->qty);
                $ttl_diskon += (($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100) * $penawaran_detail->qty);
                $no++;
            }

            $ttl_persen_diskon = (($harga_seb_diskon - $harga_ses_diskon) / $harga_seb_diskon);
            ?>
        </tbody>
        <tbody>
            <tr>
                <th class="text-right" colspan="5">TOTAL NEET</th>
                <th class="text-right">IDR <?= number_format($harga_seb_diskon, 2) ?></th>
            </tr>
            <tr>
                <th class="text-right" colspan="5">VAT <?= number_format($results['data_penawaran']->ppn, 2) ?>%</th>
                <th class="text-right">IDR <?= number_format(($harga_ses_diskon * $results['data_penawaran']->ppn / 100), 2) ?></th>
            </tr>
            <tr>
                <th class="text-right" colspan="5">GRAND TOTAL NETT</th>
                <th class="text-right">IDR <?= number_format($harga_ses_diskon + ($harga_ses_diskon * $results['data_penawaran']->ppn / 100), 2) ?></th>
            </tr>
        </tbody>
    </table>
    <!-- </div> -->
    <!-- /.box-body -->
</div>

<div class="text-right">
    <button type="button" class="btn btn-sm btn-info" onclick="printDiv('printed_area')">Print</button>
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->

<!-- /.modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script>
    function printDiv(divId) {
        var printContents = document.getElementById(divId).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    }
</script>