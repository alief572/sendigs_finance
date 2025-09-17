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
            <td rowspan="2" style="width: 100px;">
                <?= $results['logo'] ?>
            </td>
            <td style="vertical-align: middle; border-bottom: 1px solid #ccc;">
                <h3 style="font-style: italic;font-weight: bold;">Outstanding Reliant Innovative</h3>
            </td>
            <td class="text-center" style="width:200px; border-bottom: 1px solid #ccc;">
                <img src="<?= base_url('assets/images/ISO.png'); ?>" width="100" alt="">
            </td>
            <!-- <td class="text-right" style="vertical-align: top;">
                Jl. Pembangunan 2 No. 34 <br>
                Kec. Batuceper, Kota Tanggerang, Banten 15121 <br>
                <span style="font-weight:bold;">Hotline Service :</span> (+62) 21 557 66 153 <span style="font-weight:bold;">WhatsApp :</span> (+62) 858 9138 3212
            </td> -->
        </tr>
        <tr>
            <td style="height: 60px;">
                PT ORIGA MULIA FP
            </td>
            <td class="text-right">
                Jl. Pembangunan II
                Kel. Batusari,
                Kec. Batuceper,
                Kota Tangerang Postal
                Code 15122
                Indonesia
            </td>
        </tr>
    </table>

    Jakarta, <?= date('d M Y') ?>
    <table style="width: 500px !important;">
        <tr>
            <td style="vertical-align: top;">No Ref.</td>
            <td style="text-align:center; width:50px !important; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $results['data_penawaran']->no_penawaran ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Attn</td>
            <td style="text-align:center; width:50px !important; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $results['data_penawaran']->nm_customer ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Address</td>
            <td style="text-align:center; width:50px !important; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $results['data_penawaran']->alamat ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Email</td>
            <td style="text-align:center; width:50px !important; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $results['data_penawaran']->email_customer ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Phone</td>
            <td style="text-align:center; width:50px !important; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $results['data_penawaran']->telpon ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Subject</td>
            <td style="text-align:center; width:50px !important; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $results['data_penawaran']->project ?></td>
        </tr>
    </table>

    <div style="padding-top: 25px;">
        <span style="margin-top: 25px;">
            Dear Mr / Mrs <br>
            Thank you for inviting us to quote for the above-mentioned project. We are pleased to submit here <br>
            with our quotation as follow :
        </span>

        <table class="table table-bordered" border="1">
            <thead>
                <tr>
                    <th class="text-center">NO</th>
                    <th class="text-center" style="width:250px;">ITEM DESCRIPTION</th>
                    <th class="text-center">CODE</th>
                    <th class="text-center">QTY</th>
                    <th class="text-center">UNIT</th>
                    <th class="text-center">PRICE</th>
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
                        <td class="text-center">' . number_format($penawaran_detail->qty) . ' Pcs</td>
                        <td class="text-center">SET</td>
                        <td class="text-right">IDR ' . number_format(($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100))) . '</td>
                        <td class="text-right">IDR ' . number_format((($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100)) * $penawaran_detail->qty)) . '</td>
                    </tr>
                ';

                    $harga_seb_diskon += ($penawaran_detail->harga_satuan * $penawaran_detail->qty);
                    $harga_ses_diskon += (($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100)) * $penawaran_detail->qty);
                    $ttl_diskon += (($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100) * $penawaran_detail->qty);
                    $no++;
                }

                if ($harga_seb_diskon > 0 && $harga_ses_diskon > 0) {
                    $ttl_persen_diskon = (($harga_seb_diskon - $harga_ses_diskon) / $harga_seb_diskon);
                }
                ?>
            </tbody>
            <tbody>
                <tr>
                    <th class="text-right" colspan="6">TOTAL NEET</th>
                    <th class="text-right">IDR <?= number_format($harga_seb_diskon) ?></th>
                </tr>
                <tr>
                    <th class="text-right" colspan="6">VAT <?= number_format($results['data_penawaran']->ppn) ?>%</th>
                    <th class="text-right">IDR <?= number_format(($harga_ses_diskon * $results['data_penawaran']->ppn / 100)) ?></th>
                </tr>
                <tr>
                    <th class="text-right" colspan="6">GRAND TOTAL NETT</th>
                    <th class="text-right">IDR <?= number_format($harga_ses_diskon + ($harga_ses_diskon * $results['data_penawaran']->ppn / 100)) ?></th>
                </tr>
            </tbody>
        </table>
    </div>
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